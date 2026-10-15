<?php
/**
 * 2007-2026 PrestaChamps
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 *
 *  @author    PrestaChamps <support@prestachamps.com>
 *  @copyright 2007-2026 PrestaChamps
 *  @license   https://opensource.org/licenses/AFL-3.0  Academic Free License 3.0 (AFL-3.0)
 */

namespace PrestaChamps\MailchimpPro;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaChamps\MailchimpPro\Models\PixelLog;

/**
 * FO CDN health helper — records visitor observations of chimpstatic.com script
 * fetches, applies a distinct-visitor threshold, and exposes isBroken() for the
 * MC.js emission gate (Phase 2 piece 4).
 *
 * Design: separate from HealthState because BO api.mailchimp.com and FO
 * chimpstatic.com are independent Mailchimp subsystems. A merchant can have
 * a broken BO connection while their FO chimpstatic is happily serving 200s,
 * or vice versa (403 on the chimpstatic MC.js URL while BO API works fine).
 * MC.js carries pixel + popup + newsletter forms + subscribe widgets — so
 * gating it affects all of those, not just the pixel. This helper ONLY
 * gates MC.js emission — it does NOT feed the BO banner or cronjob breaker.
 */
class FoCdnHealth
{
    /** Threshold: this many distinct visitor hashes reporting a failure status within the window trip the flag. */
    public const THRESHOLD_DISTINCT_VISITORS = 3;

    /** Rolling window in seconds (24h). Also the TTL of the "broken until" flag once set. */
    public const WINDOW_SECONDS = 86400;

    /** 0 = the observer's "MC.js never executed" report; it cannot read a cross-origin status. */
    public const FAILURE_STATUSES = [0, 403, 404, 410, 500, 502, 503, 504];

    /** A success newer than this suppresses tripping — the loader demonstrably works. */
    public const RECENT_SUCCESS_SECONDS = 1800;

    /** Hard cap on stored observations; the threshold only ever needs a handful. */
    public const MAX_LOG_ENTRIES = 200;

    /** Minimum gap between last-success writes — each one also flushes PS's config cache. */
    public const SUCCESS_WRITE_THROTTLE_SECONDS = 60;

    /**
     * Record a failure observation from an FO visitor. Prunes stale entries,
     * dedups by visitor hash, and trips the flag if the distinct-visitor
     * threshold is reached within the rolling window.
     *
     * @param int $status HTTP status code observed by the visitor
     * @param string $visitorHash 8-hex-char short hash of visitor cookie ID + UA
     */
    public static function recordFailure($status, $visitorHash)
    {
        $status = (int) $status;
        $visitorHash = self::normalizeVisitorHash($visitorHash);
        if (!$visitorHash) {
            return;
        }
        if (!in_array($status, self::FAILURE_STATUSES, true)) {
            return;
        }

        $log = self::pruneStale(self::loadLog());
        $log[] = ['ts' => time(), 'status' => $status, 'vh' => $visitorHash];
        if (count($log) > self::MAX_LOG_ENTRIES) {
            $log = array_slice($log, -self::MAX_LOG_ENTRIES);
        }
        self::saveLog($log);

        if (self::hasRecentSuccess()) {
            // Another visitor ran MC.js moments ago, so the CDN is serving the
            // loader and this failure is client-side — a blocker, an extension
            // or a CSP rule. Without this guard a shop whose visitors block
            // scripts would trip the flag exactly like a real outage.
            return;
        }
        if (self::countDistinctFailingVisitors($log) >= self::THRESHOLD_DISTINCT_VISITORS) {
            self::setBrokenUntil(time() + self::WINDOW_SECONDS);
        }
    }

    /**
     * Record that a visitor's browser executed the shop's own MC.js loader.
     * One such report is proof the CDN is serving it, so it both clears an
     * active flag and suppresses new ones for RECENT_SUCCESS_SECONDS.
     *
     * @param string $visitorHash
     */
    public static function recordSuccess($visitorHash)
    {
        $visitorHash = self::normalizeVisitorHash($visitorHash);
        if (!$visitorHash) {
            return;
        }

        $last = (int) \Configuration::get(\MailchimpProConfig::MAILCHIMP_FO_CDN_LAST_SUCCESS);
        if (time() - $last >= self::SUCCESS_WRITE_THROTTLE_SECONDS) {
            \Configuration::updateValue(\MailchimpProConfig::MAILCHIMP_FO_CDN_LAST_SUCCESS, time());
        }

        if (self::isBroken()) {
            self::clearBroken();

            return;
        }

        $log = self::loadLog();
        if (!empty($log)) {
            self::saveLog(self::pruneStale($log));
        }
    }

    /**
     * @return bool whether a visitor reported a working loader recently enough
     *              to outrank concurrent failure reports
     */
    private static function hasRecentSuccess()
    {
        $last = (int) \Configuration::get(\MailchimpProConfig::MAILCHIMP_FO_CDN_LAST_SUCCESS);

        return $last > 0 && $last >= time() - self::RECENT_SUCCESS_SECONDS;
    }

    /**
     * @return bool whether the BO connection-health warning should show
     */
    public static function isBroken()
    {
        return self::getBrokenUntil() > time();
    }

    /**
     * @return int UNIX timestamp; 0 if never set / already expired
     */
    public static function getBrokenUntil()
    {
        return (int) \Configuration::get(\MailchimpProConfig::MAILCHIMP_FO_CDN_BROKEN_UNTIL);
    }

    /**
     * Explicit clear — used by recovery UI (Phase 3) and by uninstall cleanup.
     */
    public static function clearBroken()
    {
        \Configuration::deleteByName(\MailchimpProConfig::MAILCHIMP_FO_CDN_BROKEN_UNTIL);
        \Configuration::deleteByName(\MailchimpProConfig::MAILCHIMP_FO_CDN_FAILURE_LOG);
        if (class_exists(PixelLog::class)) {
            PixelLog::insert(
                PixelLog::SEVERITY_INFO,
                PixelLog::SOURCE_FO,
                'FO_CDN_FLAG_CLEARED',
                'FO CDN broken flag and observation log cleared'
            );
        }
    }

    private static function setBrokenUntil($ts)
    {
        $previous = self::getBrokenUntil();
        \Configuration::updateValue(\MailchimpProConfig::MAILCHIMP_FO_CDN_BROKEN_UNTIL, (int) $ts);
        if (0 === $previous && class_exists(PixelLog::class)) {
            // Only log the first transition into broken — subsequent extensions
            // of the broken-until timestamp are just the flag renewing itself.
            PixelLog::insert(
                PixelLog::SEVERITY_WARNING,
                PixelLog::SOURCE_FO,
                'FO_CDN_FLAG_SET',
                'FO CDN broken flag set — at least ' . self::THRESHOLD_DISTINCT_VISITORS
                . ' distinct visitors reported MC.js load failures within '
                . (int) round(self::WINDOW_SECONDS / 3600) . 'h'
            );
        }
    }

    private static function loadLog()
    {
        $raw = \Configuration::get(\MailchimpProConfig::MAILCHIMP_FO_CDN_FAILURE_LOG);
        if (!$raw) {
            return [];
        }
        $decoded = json_decode((string) $raw, true);
        if (!is_array($decoded) || !isset($decoded['reports']) || !is_array($decoded['reports'])) {
            return [];
        }

        return $decoded['reports'];
    }

    private static function saveLog(array $log)
    {
        if (empty($log)) {
            \Configuration::deleteByName(\MailchimpProConfig::MAILCHIMP_FO_CDN_FAILURE_LOG);

            return;
        }
        \Configuration::updateValue(
            \MailchimpProConfig::MAILCHIMP_FO_CDN_FAILURE_LOG,
            json_encode(['reports' => array_values($log)])
        );
    }

    private static function pruneStale(array $log)
    {
        $cutoff = time() - self::WINDOW_SECONDS;
        $fresh = [];
        foreach ($log as $entry) {
            if (isset($entry['ts']) && (int) $entry['ts'] >= $cutoff) {
                $fresh[] = $entry;
            }
        }

        return $fresh;
    }

    /**
     * Count distinct visitor hashes with a failure-status entry in the log.
     */
    private static function countDistinctFailingVisitors(array $log)
    {
        $distinct = [];
        foreach ($log as $entry) {
            if (!isset($entry['vh'], $entry['status'])) {
                continue;
            }
            if (in_array((int) $entry['status'], self::FAILURE_STATUSES, true)) {
                $distinct[$entry['vh']] = true;
            }
        }

        return count($distinct);
    }

    /**
     * Reject anything that isn't the exact 8-hex-char short-hash shape.
     * Defensive against hostile FO POSTs that pass controller validation.
     */
    private static function normalizeVisitorHash($vh)
    {
        $vh = (string) $vh;
        if (preg_match('/^[a-f0-9]{8}$/', $vh)) {
            return $vh;
        }

        return '';
    }
}
