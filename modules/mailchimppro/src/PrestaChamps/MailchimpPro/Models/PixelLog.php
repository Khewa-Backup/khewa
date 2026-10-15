<?php
/**
 * MailChimp
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Commercial License
 * you can't distribute, modify or sell this code
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file
 * If you need help please contact leo@prestachamps.com
 *
 * @author    Mailchimp
 * @copyright Mailchimp
 * @license   commercial
 */

namespace PrestaChamps\MailchimpPro\Models;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Ring-buffered log for Mailchimp Pixel events + admin actions. Storage:
 * ps_mailchimppro_pixel_log (created in installDb()). Capped via the
 * two-pass trim in trimRingBuffer().
 *
 * Sources: 'bo' for server-side admin actions, 'fo' for client-side
 * telemetry POSTed via controllers/front/pixeltelemetry.php. Severities:
 * 1 = info, 2 = warning, 3 = error.
 *
 * Hard column caps (MAX_*_LEN) are enforced before insert as defense
 * against hostile FO POSTs that pass FO-controller validation but try
 * to inflate row size downstream.
 */
class PixelLog
{
    public const SOURCE_BO = 'bo';
    public const SOURCE_FO = 'fo';
    public const SOURCE_CRON = 'cron';
    public const SOURCE_HOOK = 'hook';

    public const SEVERITY_INFO = 1;
    public const SEVERITY_WARNING = 2;
    public const SEVERITY_ERROR = 3;

    public const MAX_EVENT_TYPE_LEN = 50;
    /** Column is TEXT; cap defensively at 4000 — PURCHASED with several line items can reach ~2000 chars. */
    public const MAX_MESSAGE_LEN = 4000;
    /** IPv4-mapped IPv6 is 45 chars. */
    public const MAX_IP_LEN = 45;

    public const TABLE = 'mailchimppro_pixel_log';

    /**
     * Allowed event_type values for FO telemetry. Other values are rejected
     * by the front controller. BO callers are trusted but should pick from
     * this list for filterability.
     *
     * @var string[]
     */
    public static $foAllowedEventTypes = [
        // Track events (Mailchimp 3P Pixel SDK)
        'PAGE_VIEWED',
        'PRODUCT_VIEWED',
        'PRODUCT_ADDED_TO_CART',
        'CART_VIEWED',
        'CHECKOUT_STARTED',
        'PURCHASED',
        'IDENTIFY',
        // SDK lifecycle / availability diagnostics
        'SDK_LOAD_FAILED',
        'SDK_TIMEOUT',
        'PIXEL_AVAILABLE',
        'PIXEL_NOT_AVAILABLE',
        // Bridge-side diagnostic events surfaced for support visibility
        'PIXEL_HOOK_WIRED',           // verbose-only: template hook IS in active theme
        'PIXEL_HOOK_MISSING',         // sev=2 warn: hook is NOT in active theme (custom theme / PS 1.7.0.6 classic)
        'QUICKVIEW_DATA_INCOMPLETE',  // sev=1 info: quickview opened but DOM scrape missed the product
        'PIXEL_DATA_INCOMPLETE',      // sev=2 warn: fell through every cascade layer; track skipped to avoid invalid payload
    ];

    /**
     * Problem-indicator events that ALWAYS log regardless of the configured
     * level — bypass the errors_only severity-3-only cut. These are sev=1/2
     * events that nonetheless signal something is wrong: a misconfigured
     * theme, an SDK that never appeared, a fallback cascade that dropped a
     * track payload, etc. Merchants running on `errors_only` should still
     * see these so problems don't go unnoticed.
     *
     * SDK_LOAD_FAILED (sev=3) is already covered by the severity check;
     * listed below for documentation / future-proofing only.
     *
     * @var string[]
     */
    public static $alwaysLogEventTypes = [
        'SDK_LOAD_FAILED',
        'SDK_TIMEOUT',
        'PIXEL_HOOK_MISSING',
        'PIXEL_DATA_INCOMPLETE',
        'PIXEL_NOT_AVAILABLE',
        'QUICKVIEW_DATA_INCOMPLETE',
    ];

    /**
     * Events that only land at `verbose` level. Currently just the
     * "everything is wired" success diagnostic — pure verbose-mode signal
     * with no problem implied. The 6 track events used to be here but are
     * now allowed at default with a compact one-liner emitted by the
     * bridge (`Track fired`) so merchants get tracking-confirmation
     * visibility without payload verbosity.
     *
     * @var string[]
     */
    public static $verboseOnlyEventTypes = [
        'PIXEL_HOOK_WIRED',
    ];

    /**
     * Available log levels — single source of truth. BO select widget,
     * server-side validation in insert(), and FAQ descriptions all read
     * from here. To add a level: append a key, then add one branch in
     * insert()'s level-filter switch.
     *
     * @var array<string, array{label: string, description: string}>
     */
    public static $logLevels = [
        'errors_only' => [
            'label' => 'Errors only',
            'description' => 'Log only problems that need attention — SDK failures (sev=3), SDK timeouts, missing theme hooks, incomplete payloads, audience setup gaps. Minimal noise for production monitoring; merchants still see a record when something genuinely breaks even if its severity is not error-grade.',
        ],
        'default' => [
            'label' => 'Default',
            'description' => 'Log infrastructure events (pixel availability, identify transitions, BO admin actions), all problems from the Errors-only level, AND a compact one-liner per track event so you can see tracking firing without payload verbosity. Recommended for production.',
        ],
        'verbose' => [
            'label' => 'Verbose',
            'description' => 'Log everything with full detail — track events include source, via, and the full payload. Use temporarily when troubleshooting or verifying the bridge is firing the right data, then switch back to default. Produces the most data per pageview.',
        ],
    ];

    public static function getTableName()
    {
        return _DB_PREFIX_ . self::TABLE;
    }

    /**
     * Insert a log entry. Caller-supplied strings are length-capped to match
     * column widths.
     *
     * Render-safety: $message may carry FO-supplied content (source='fo').
     * Any BO viewer of this column MUST render via auto-escaping
     * interpolation (Vue `{{ }}`, Smarty `escape:'htmlall'`) — never
     * `v-html` / `nofilter` / direct DOM-write.
     *
     * @param int $severity one of the SEVERITY_* constants
     * @param string $source one of the SOURCE_* constants
     * @param string $eventType short identifier (e.g. 'PRODUCT_VIEWED', 'AUTO_FETCH')
     * @param string $message free-form detail (truncated to MAX_MESSAGE_LEN)
     * @param string|null $ip visitor IP for FO source (used by rate-limit); null for BO
     * @param int|null $idShop shop context (defaults to current)
     *
     * @return bool true on insert success
     */
    public static function insert($severity, $source, $eventType, $message = null, $ip = null, $idShop = null)
    {
        // Master kill-switch — also guards against an attacker bypassing the
        // FO controller and reaching insert() directly.
        if (!\Configuration::get(\MailchimpProConfig::MAILCHIMP_PIXEL_LOG_ENABLED)) {
            return false;
        }

        $severityInt = (int) $severity;
        if ($severityInt < 1 || $severityInt > 3) {
            $severityInt = self::SEVERITY_INFO;
        }
        $sourceStr = self::normalizeSource($source);
        $eventStr = self::truncate((string) $eventType, self::MAX_EVENT_TYPE_LEN);
        if ('' === $eventStr) {
            return false;
        }

        // Apply log-level filter. Unknown / unset / legacy values fall back
        // to 'default' (matches the upgrade-hook seed).
        $level = (string) \Configuration::get(\MailchimpProConfig::MAILCHIMP_PIXEL_LOG_LEVEL);
        if (!isset(self::$logLevels[$level])) {
            $level = 'default';
        }
        if ('errors_only' === $level) {
            // Sev-3 OR explicit problem-indicator allowlist — see
            // $alwaysLogEventTypes for the rationale (catches sev=1/2
            // events that still signal something is wrong).
            $passes = $severityInt >= self::SEVERITY_ERROR
                   || in_array($eventStr, self::$alwaysLogEventTypes, true);
            if (!$passes) {
                return false;
            }
        } elseif ('default' === $level) {
            // Drop verbose-only diagnostics (currently just PIXEL_HOOK_WIRED).
            // Track events (PAGE_VIEWED, PRODUCT_VIEWED, etc.) are NOT in
            // the verbose-only list anymore — they pass through at default
            // with a compact `Track fired` message emitted by the bridge.
            if (in_array($eventStr, self::$verboseOnlyEventTypes, true)) {
                return false;
            }
        }
        $messageStr = (null === $message || '' === $message) ? null : self::truncate((string) $message, self::MAX_MESSAGE_LEN);
        $ipStr = (null === $ip || '' === $ip) ? null : self::truncate((string) $ip, self::MAX_IP_LEN);
        $idShopInt = (null === $idShop) ? null : (int) $idShop;

        $row = [
            'severity' => $severityInt,
            'source' => pSQL($sourceStr),
            'event_type' => pSQL($eventStr),
            'message' => (null === $messageStr) ? null : pSQL($messageStr),
            'id_shop' => $idShopInt,
            'ip' => (null === $ipStr) ? null : pSQL($ipStr),
            'date_add' => date('Y-m-d H:i:s'),
        ];

        $ok = (bool) \Db::getInstance()->insert(self::TABLE, $row);

        if ($ok) {
            // Best-effort trim — non-fatal if cleanup fails so the insert still counts.
            self::trimRingBuffer();
        }

        return $ok;
    }

    /**
     * Per-event-type quotas — protect high-value rows (PURCHASED audits,
     * admin state changes) from being evicted by floods of low-value
     * noise (SDK_LOAD_FAILED from ad-blocker visitors, PIXEL_NOT_AVAILABLE).
     * Hardcoded (not Configuration-exposed) — implementation detail of the
     * ring buffer, not merchant-tunable. Merchants control total log
     * volume via MAILCHIMP_PIXEL_LOG_MAX_ROWS.
     *
     * @return int[] event_type => cap; '_default' covers any unlisted type
     *               (forward compat for future SDK additions)
     */
    protected static function defaultQuotas()
    {
        return [
            'PURCHASED' => 1000,
            'PIXEL_ENABLED' => 100,
            'PIXEL_DISABLED' => 100,
            'PIXEL_SETTING_CHANGED' => 200,
            'PIXEL_STATE_REFRESH' => 200,
            'PRODUCT_VIEWED' => 500,
            'PRODUCT_ADDED_TO_CART' => 500,
            'CART_VIEWED' => 500,
            'CHECKOUT_STARTED' => 500,
            'IDENTIFY' => 500,
            'PIXEL_AVAILABLE' => 500,
            'PIXEL_NOT_AVAILABLE' => 2000,  // noisy but feeds the verdict quorum window
            'SDK_LOAD_FAILED' => 2000,  // ad-blocker / CSP / network blips
            'SDK_TIMEOUT' => 500,
            'PIXEL_DATA_INCOMPLETE' => 1000,
            '_default' => 200,
        ];
    }

    /**
     * Two-pass trim:
     *   PASS 1 — per-event-type quota trim (preserves high-value rows
     *            from being evicted by noise floods).
     *   PASS 2 — global MAILCHIMP_PIXEL_LOG_MAX_ROWS backstop.
     *
     * @return int rows deleted across both passes
     */
    public static function trimRingBuffer()
    {
        $tbl = self::getTableName();
        $totalDeleted = 0;

        // ===== PASS 1 — per-event-type quotas =====
        $quotas = self::defaultQuotas();
        $defaultQuota = isset($quotas['_default']) ? (int) $quotas['_default'] : 200;
        $counts = \Db::getInstance()->executeS(
            'SELECT `event_type`, COUNT(*) AS c FROM `' . $tbl . '` GROUP BY `event_type`'
        );
        if (is_array($counts)) {
            foreach ($counts as $row) {
                $type = isset($row['event_type']) ? (string) $row['event_type'] : '';
                if ('' === $type) {
                    continue;
                }
                $current = isset($row['c']) ? (int) $row['c'] : 0;
                $cap = array_key_exists($type, $quotas) ? (int) $quotas[$type] : $defaultQuota;
                if ($cap <= 0 || $current <= $cap) {
                    continue;
                }
                $excess = $current - $cap;
                $sql = 'DELETE FROM `' . $tbl . '` '
                     . "WHERE `event_type` = '" . pSQL($type) . "' "
                     . 'ORDER BY `date_add` ASC, `id_log` ASC LIMIT ' . (int) $excess;
                if (\Db::getInstance()->execute($sql)) {
                    $totalDeleted += $excess;
                }
            }
        }

        // ===== PASS 2 — global cap backstop =====
        $maxRows = (int) \Configuration::get(\MailchimpProConfig::MAILCHIMP_PIXEL_LOG_MAX_ROWS);
        if ($maxRows <= 0) {
            return $totalDeleted; // unlimited / disabled global trim
        }
        $remaining = (int) \Db::getInstance()->getValue('SELECT COUNT(*) FROM `' . $tbl . '`');
        if ($remaining <= $maxRows) {
            return $totalDeleted;
        }
        $excess = $remaining - $maxRows;
        $sql = 'DELETE FROM `' . $tbl . '` ORDER BY `date_add` ASC, `id_log` ASC LIMIT ' . (int) $excess;
        if (\Db::getInstance()->execute($sql)) {
            $totalDeleted += $excess;
        }

        return $totalDeleted;
    }

    /**
     * Count log entries from $ip in the last 60s. Used by the FO telemetry
     * controller for per-IP per-minute rate-limit.
     */
    public static function countByIpInLastMinute($ip)
    {
        if (null === $ip || '' === $ip) {
            return 0;
        }
        $tbl = self::getTableName();
        $sql = 'SELECT COUNT(*) FROM `' . $tbl . '` '
             . "WHERE `ip` = '" . pSQL((string) $ip) . "' "
             . 'AND `date_add` > (NOW() - INTERVAL 1 MINUTE)';

        return (int) \Db::getInstance()->getValue($sql);
    }

    /**
     * @param int $limit capped at 500
     * @param array $filters see buildFilterWhere() for supported keys
     */
    public static function getRecent($limit = 100, array $filters = [], $offset = 0)
    {
        $limitInt = max(1, min(500, (int) $limit));
        $offsetInt = max(0, (int) $offset);

        $where = self::buildFilterWhere($filters);
        $tbl = self::getTableName();
        $sql = 'SELECT `id_log`, `severity`, `source`, `event_type`, `message`, `id_shop`, `ip`, `date_add` '
             . 'FROM `' . $tbl . '` '
             . 'WHERE ' . implode(' AND ', $where) . ' '
             . 'ORDER BY `id_log` DESC '
             . 'LIMIT ' . $offsetInt . ', ' . $limitInt;

        $rows = \Db::getInstance()->executeS($sql);

        return is_array($rows) ? $rows : [];
    }

    /**
     * Same filter shape as getRecent(). Used for pagination totals.
     */
    public static function countAll(array $filters = [])
    {
        $where = self::buildFilterWhere($filters);
        $tbl = self::getTableName();
        $sql = 'SELECT COUNT(*) FROM `' . $tbl . '` WHERE ' . implode(' AND ', $where);

        return (int) \Db::getInstance()->getValue($sql);
    }

    /**
     * Builds the WHERE clause shared by getRecent() + countAll(). Supported keys:
     *   severity    int 1-3
     *   source      'bo' | 'fo'
     *   event_type  exact match
     *   message     substring LIKE '%term%' (max 200 chars)
     *   ip          prefix LIKE 'term%'      (works for partial IPs like "192.168.")
     *   from / to   inclusive date_add bounds (date-only `to` is bumped to 23:59:59 so
     *               "from=X to=X" returns rows from that whole day)
     *   since       legacy alias for `from`
     *
     * All inputs are pSQL-escaped + length-capped.
     *
     * @return string[] WHERE fragments (always at least ['1=1'])
     */
    private static function buildFilterWhere(array $filters)
    {
        $where = ['1=1'];

        if (isset($filters['severity']) && '' !== $filters['severity']) {
            $where[] = '`severity` = ' . (int) $filters['severity'];
        }
        if (isset($filters['source']) && '' !== $filters['source']) {
            $src = self::normalizeSource($filters['source']);
            $where[] = "`source` = '" . pSQL($src) . "'";
        }
        if (isset($filters['event_type']) && '' !== $filters['event_type']) {
            $ev = self::truncate((string) $filters['event_type'], self::MAX_EVENT_TYPE_LEN);
            $where[] = "`event_type` = '" . pSQL($ev) . "'";
        }
        if (isset($filters['message']) && '' !== $filters['message']) {
            $msg = self::truncate((string) $filters['message'], 200);
            $where[] = "`message` LIKE '%" . pSQL($msg) . "%'";
        }
        if (isset($filters['ip']) && '' !== $filters['ip']) {
            $ip = self::truncate((string) $filters['ip'], 45);
            $where[] = "`ip` LIKE '" . pSQL($ip) . "%'";
        }
        $from = isset($filters['from']) ? (string) $filters['from'] : '';
        if ('' === $from && isset($filters['since'])) {
            $from = (string) $filters['since'];
        }
        if ('' !== $from) {
            $where[] = "`date_add` >= '" . pSQL($from) . "'";
        }
        if (isset($filters['to']) && '' !== $filters['to']) {
            $to = (string) $filters['to'];
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
                $to .= ' 23:59:59';
            }
            $where[] = "`date_add` <= '" . pSQL($to) . "'";
        }

        return $where;
    }

    /**
     * Wipe all log rows. Used by the BO "Clear log" button.
     */
    public static function clear()
    {
        return (bool) \Db::getInstance()->execute('TRUNCATE TABLE `' . self::getTableName() . '`');
    }

    /**
     * Normalize a caller-provided source string to one of the SOURCE_* constants.
     * Unknown values fall through to SOURCE_BO — safe fallback that used to be
     * the only valid non-FO value (kept for backwards compatibility with existing
     * insert() callers that pass unrecognized strings).
     */
    private static function normalizeSource($source)
    {
        $known = [self::SOURCE_BO, self::SOURCE_FO, self::SOURCE_CRON, self::SOURCE_HOOK];
        if (in_array($source, $known, true)) {
            return $source;
        }

        return self::SOURCE_BO;
    }

    private static function truncate($str, $maxLen)
    {
        if (function_exists('mb_substr') && function_exists('mb_strlen')) {
            if (mb_strlen($str) > $maxLen) {
                return mb_substr($str, 0, $maxLen);
            }

            return $str;
        }
        if (strlen($str) > $maxLen) {
            return substr($str, 0, $maxLen);
        }

        return $str;
    }
}
