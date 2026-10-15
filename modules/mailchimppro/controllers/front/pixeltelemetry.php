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
if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaChamps\MailchimpPro\Models\PixelLog;

/**
 * Public FO endpoint that accepts pixel-event telemetry POSTed from the
 * storefront's Vue/JS pixel bridge. Reachable as:
 *
 *   POST /module/mailchimppro/pixeltelemetry
 *
 * Expected request body (JSON or form-urlencoded):
 *   {
 *     "event_type": "PRODUCT_VIEWED",   // must be in PixelLog::$foAllowedEventTypes
 *     "severity":   1,                   // optional, 1|2|3 (defaults to 1)
 *     "message":    "...short detail..." // optional, capped at 500 chars
 *   }
 *
 * Always returns HTTP 200 + {ok:true, logged:bool}. Never tells the caller
 * WHY a write was skipped (rate limit, kill-switch, invalid event_type) so an
 * attacker probing the endpoint can't distinguish failure modes.
 *
 * Defenses against abuse on a public endpoint:
 *   1. Master kill-switch via MAILCHIMP_PIXEL_LOG_ENABLED config (= silent no-op)
 *   2. event_type whitelist (rejects anything not in PixelLog::$foAllowedEventTypes)
 *   3. Hard length caps on event_type (50) and message (500) — defensive, even
 *      though the model truncates again
 *   4. Per-IP per-minute rate-limit
 *      (MAILCHIMP_PIXEL_LOG_RATE_LIMIT_PER_MIN config, default 60)
 *   5. Ring-buffer in PixelLog::insert() — even if all defenses above fail,
 *      total table rows stay capped at MAILCHIMP_PIXEL_LOG_MAX_ROWS (default 1000)
 */
class MailchimpproPixeltelemetryModuleFrontController extends ModuleFrontController
{
    /** Hard cap on request-body bytes accepted by readPayload(). */
    public const MAX_PAYLOAD_BYTES = 16384;

    /**
     * Quorum: how many DISTINCT visitor IPs must report PIXEL_NOT_AVAILABLE
     * within {@see self::PIXEL_NOT_AVAILABLE_WINDOW_MIN} minutes before the
     * BO Pixel-status panel flips from "enabled" to "not enabled". One
     * visitor reporting a negative is inconclusive — could be a partial
     * chimpstatic block / browser extension nullifying pixel.api / etc.
     */
    public const PIXEL_NOT_AVAILABLE_QUORUM = 3;
    public const PIXEL_NOT_AVAILABLE_WINDOW_MIN = 15;

    /**
     * Side-channel: set by updatePixelAvailability() when a request lands as
     * marginal (under quorum). handlePost() reads it to build the
     * "[marginal N/Q] " log prefix.
     *
     * @var int
     */
    private $lastMarginalCount = 0;

    /** @var bool no auth required — public FO endpoint by design */
    public $auth = false;
    /** @var bool */
    public $ajax = true;
    /** @var bool */
    public $ssl = true;

    /**
     * PS dispatch routes here when Controller::isAjax() returns true — i.e.
     * the caller sets `?ajax=1`, posts `ajax=1`, or sends `Accept:
     * application/json`. Without this method, PS short-circuits to an empty
     * response (display() is NOT called as a fallback when ajax=true).
     */
    public function displayAjax()
    {
        $logged = $this->handlePost();
        $this->respond(['ok' => true, 'logged' => $logged]);
    }

    /**
     * PS dispatch routes here when Controller::isAjax() returns false — the
     * common case for sendBeacon and keepalive-fetch posts (neither sets the
     * Accept header). Same logic as displayAjax — both paths must produce
     * identical output.
     */
    public function display()
    {
        $logged = $this->handlePost();
        $this->respond(['ok' => true, 'logged' => $logged]);
    }

    /**
     * @return bool true if a row was inserted, false otherwise (rate-limited,
     *              disabled, invalid input — the caller doesn't get told which)
     */
    private function handlePost()
    {
        // Master kill-switch — silent
        if (!Configuration::get(MailchimpProConfig::MAILCHIMP_PIXEL_LOG_ENABLED)) {
            return false;
        }

        // Read raw JSON body first; fall back to form-encoded params
        $payload = $this->readPayload();
        if (!is_array($payload)) {
            return false;
        }

        // event_type — required, must be in whitelist
        $eventType = isset($payload['event_type']) ? (string) $payload['event_type'] : '';
        $eventType = $this->cap($eventType, PixelLog::MAX_EVENT_TYPE_LEN);
        if ('' === $eventType || !in_array($eventType, PixelLog::$foAllowedEventTypes, true)) {
            return false;
        }

        // severity — optional, must be 1|2|3
        $severity = isset($payload['severity']) ? (int) $payload['severity'] : PixelLog::SEVERITY_INFO;
        if ($severity < 1 || $severity > 3) {
            $severity = PixelLog::SEVERITY_INFO;
        }

        // message — optional, capped
        $message = isset($payload['message']) ? $this->cap((string) $payload['message'], PixelLog::MAX_MESSAGE_LEN) : null;

        // Rate limit by IP
        $ip = $this->getIp();
        if (null !== $ip) {
            $rateLimit = (int) Configuration::get(MailchimpProConfig::MAILCHIMP_PIXEL_LOG_RATE_LIMIT_PER_MIN);
            if ($rateLimit > 0) {
                $recentCount = PixelLog::countByIpInLastMinute($ip);
                if ($recentCount >= $rateLimit) {
                    return false;
                }
            }
        }

        // ===== Authoritative verdicts (PIXEL_AVAILABLE / PIXEL_NOT_AVAILABLE)
        //
        // Two outcomes per probe:
        //   (a) Steady state — the incoming verdict matches what's already in
        //       MAILCHIMP_PIXEL_AVAILABLE. Touch `last_checked_at` (so the BO
        //       panel shows "reconfirmed N seconds ago") but DO NOT insert a
        //       log row. Logging the same verdict over and over wastes ring-
        //       buffer rows on a busy store (1 row per unique-IP visitor per
        //       5 min — quickly fills the buffer with zero new information).
        //   (b) Transition — verdict differs from stored. Update Configuration
        //       AND insert a single log entry with the bridge's descriptive
        //       message preserved verbatim (e.g. "$mcSite loaded but pixel.api
        //       not exposed — account likely not pixel-enabled"). The state-
        //       machine codes are already in the `event_type` column; what
        //       the message column should carry is the *diagnostic context*
        //       that tells a merchant or support engineer WHY this happened.
        //
        // SDK_LOAD_FAILED is NOT a state-with-current-truth (its causes vary
        // per visitor: CSP, ad blocker, network blip), so transition logic
        // doesn't apply — per-IP 5min dedupe handles it below.
        if ('PIXEL_AVAILABLE' === $eventType || 'PIXEL_NOT_AVAILABLE' === $eventType) {
            $previousVerdict = $this->updatePixelAvailability($eventType, $ip);
            if (null === $previousVerdict) {
                // Steady state — Configuration was touched, no log row.
                return false;
            }
            // Transition / marginal — prepend a self-contained marker so the
            // BO log viewer can render context WITHOUT looking at adjacent
            // rows, and a CSV/raw-DB export still shows the meaning. Bridge
            // messages never legitimately start with "[" so the markers are
            // collision-free. Length stays under MAX_MESSAGE_LEN (500).
            //   [marginal N/Q] — PIXEL_NOT_AVAILABLE under quorum, no flip
            //   [from:STATE]   — genuine transition, state was flipped
            if ('MARGINAL' === $previousVerdict) {
                $prefix = '[marginal ' . $this->lastMarginalCount
                    . '/' . self::PIXEL_NOT_AVAILABLE_QUORUM . '] ';
            } else {
                $prefix = '[from:' . $previousVerdict . '] ';
            }
            $remaining = PixelLog::MAX_MESSAGE_LEN - strlen($prefix);
            $body = is_string($message) ? substr($message, 0, $remaining) : '';
            $message = $prefix . $body;
        }

        // Per-IP dedupe for SDK_LOAD_FAILED — legitimately varies per visitor
        // (CSP, ad blocker, network), so per-IP makes sense; same IP twice
        // within 5 min is either a refresh or a stable client-side block,
        // neither needs two rows.
        if ('SDK_LOAD_FAILED' === $eventType && null !== $ip
            && $this->wasVerdictRecentlyLogged($eventType, $ip)) {
            return false;
        }

        $idShop = (int) $this->context->shop->id;

        return PixelLog::insert(
            $severity,
            PixelLog::SOURCE_FO,
            $eventType,
            $message,
            $ip,
            $idShop > 0 ? $idShop : null
        );
    }

    /**
     * Returns true if the same event was logged from this IP in the last 5
     * minutes. Used to dedupe SDK_LOAD_FAILED (varied causes per visitor —
     * a refresh or stable client block doesn't need two rows). Uses the
     * (ip, date_add) composite index for cheap reads.
     *
     * @param string $eventType
     * @param string $ip
     *
     * @return bool
     */
    private function wasVerdictRecentlyLogged($eventType, $ip)
    {
        $tbl = _DB_PREFIX_ . PixelLog::TABLE;
        $sql = 'SELECT COUNT(*) FROM `' . $tbl . '` '
             . "WHERE `event_type` = '" . pSQL($eventType) . "' "
             . "AND `ip` = '" . pSQL($ip) . "' "
             . 'AND `date_add` > (NOW() - INTERVAL 5 MINUTE)';

        return (int) Db::getInstance()->getValue($sql) > 0;
    }

    /**
     * Authoritative verdict events (PIXEL_AVAILABLE / PIXEL_NOT_AVAILABLE) from
     * the FO bridge are the runtime truth — derived from the actual
     * `window.$mcSite.pixel.api` shape that shoppers experience — so they
     * override the server-side pixel-state refresh by writing into
     * MAILCHIMP_PIXEL_AVAILABLE. The BO Pixel-status panel reads this same key.
     *
     * Always touches `last_checked_at` (so the panel can show "reconfirmed N
     * seconds ago" even when state hasn't changed). Returns:
     *   - null                  if the incoming verdict matches the stored one
     *                           (steady state — caller skips the log insert);
     *   - 'PIXEL_AVAILABLE'     |
     *   - 'PIXEL_NOT_AVAILABLE' |
     *   - 'UNKNOWN'             on a transition — the value is the PREVIOUS
     *                           verdict (caller currently just checks non-null;
     *                           the previous-verdict string is kept available
     *                           for any future audit / diagnostic use).
     *
     * SDK_LOAD_FAILED / SDK_TIMEOUT are inconclusive (one visitor's CSP / ad
     * blocker / flaky network tells us nothing about the merchant's account
     * state) and never land here — they only go to the log table.
     *
     * @param string $eventType PIXEL_AVAILABLE | PIXEL_NOT_AVAILABLE
     *
     * @return string|null previous verdict on transition, null on steady state
     */
    private function updatePixelAvailability($eventType, $currentIp = null)
    {
        $newEnabled = ('PIXEL_AVAILABLE' === $eventType);

        $previousVerdict = null;          // null means "no prior state stored"
        $previousEnabled = null;          // bool|null — separate from the string verdict
        $isTransition = true;          // assume transition unless we prove same-state
        $current = Configuration::get(MailchimpProConfig::MAILCHIMP_PIXEL_AVAILABLE);
        if (is_string($current) && '' !== $current) {
            $decoded = json_decode($current, true);
            if (is_array($decoded) && isset($decoded['enabled'])) {
                $previousEnabled = (bool) $decoded['enabled'];
                $previousVerdict = $previousEnabled
                    ? 'PIXEL_AVAILABLE'
                    : 'PIXEL_NOT_AVAILABLE';
                if ($previousEnabled === $newEnabled) {
                    $isTransition = false;
                }
            }
        }

        // Asymmetric quorum. PIXEL_AVAILABLE is positive proof — one visitor
        // seeing pixel.api work in their real browser is authoritative, flip
        // instantly. PIXEL_NOT_AVAILABLE is inconclusive — one visitor's
        // partial chimpstatic block / browser extension nullifying pixel.api
        // / network blip would otherwise flip the BO panel from green to red
        // on a single bad report. Require N distinct IPs in last M minutes
        // to flip enabled→false. Under quorum, log as marginal but keep
        // `enabled` at its previous value; the BO panel renders a yellow
        // "checking" intermediate state when `recent_negative_count > 0`.
        $negativeCount = 0;
        $flipBlockedByQuorum = false;
        if ('PIXEL_NOT_AVAILABLE' === $eventType) {
            // Effective distinct-IP count INCLUDING the in-flight request.
            // If currentIp is already in the recent log, it doesn't add to
            // distinctness — preventing one persistent-bad-environment IP
            // from accumulating quorum on its own.
            $negativeCount = $this->countRecentNotAvailableReports($currentIp);
            if ($isTransition && $negativeCount < self::PIXEL_NOT_AVAILABLE_QUORUM) {
                $flipBlockedByQuorum = true;
                $this->lastMarginalCount = $negativeCount;
            }
        }

        // Always update Configuration — including last_checked_at — so the BO
        // panel can show "reconfirmed N seconds ago" even on steady state.
        // Same JSON shape as refreshPixelEnabledState writes from the BO side
        // (single canonical reader on the Vue side). `recent_negative_count`
        // is a fo_bridge-only field; api-source writes don't include it and
        // the Vue panel treats absent as 0.
        if ($flipBlockedByQuorum) {
            // Under-quorum negative: keep prior `enabled` if one exists; if no
            // prior cfg exists (first-ever report), default to true. The whole
            // point of the quorum gate is that ONE negative is inconclusive
            // — so it must not flip a fresh install's panel red. BO renders
            // yellow "checking" via recent_negative_count > 0.
            $effectiveEnabled = null !== $previousEnabled ? $previousEnabled : true;
        } else {
            $effectiveEnabled = $newEnabled;
        }
        $payload = [
            'enabled' => (bool) $effectiveEnabled,
            'last_checked_at' => date('Y-m-d H:i:s'),
            'source' => 'fo_bridge',
        ];
        if ('PIXEL_NOT_AVAILABLE' === $eventType) {
            $payload['recent_negative_count'] = $negativeCount;
            $payload['negative_quorum'] = self::PIXEL_NOT_AVAILABLE_QUORUM;
        } else {
            // PIXEL_AVAILABLE — positive proof clears any prior marginal count.
            // The BO panel reads absent-or-zero as "no checking state".
            $payload['recent_negative_count'] = 0;
            $payload['negative_quorum'] = self::PIXEL_NOT_AVAILABLE_QUORUM;
        }
        Configuration::updateValue(
            MailchimpProConfig::MAILCHIMP_PIXEL_AVAILABLE,
            json_encode($payload),
            true
        );

        if ($flipBlockedByQuorum) {
            return 'MARGINAL';
        }
        if (!$isTransition) {
            return null;
        }

        // First-ever verdict reads as a transition from "UNKNOWN" — useful so
        // the very first determination still gets logged.
        return null !== $previousVerdict ? $previousVerdict : 'UNKNOWN';
    }

    /**
     * Effective COUNT(DISTINCT ip) of PIXEL_NOT_AVAILABLE FO log rows within
     * the quorum window, INCLUDING the in-flight request's contribution. If
     * $currentIp is already represented in the prior rows, the count is the
     * existing distinct count (the new request adds no new distinctness). If
     * $currentIp is new (or null), the count is prior + 1.
     *
     * Same (date_add) index the per-IP dedupe helper uses — cheap read.
     *
     * @param string|null $currentIp
     *
     * @return int
     */
    private function countRecentNotAvailableReports($currentIp = null)
    {
        $tbl = _DB_PREFIX_ . PixelLog::TABLE;
        $minutes = (int) self::PIXEL_NOT_AVAILABLE_WINDOW_MIN;
        $whereBase = "`event_type` = 'PIXEL_NOT_AVAILABLE' "
            . "AND `source` = '" . pSQL(PixelLog::SOURCE_FO) . "' "
            . 'AND `date_add` > (NOW() - INTERVAL ' . $minutes . ' MINUTE)';
        $prior = (int) Db::getInstance()->getValue(
            'SELECT COUNT(DISTINCT `ip`) FROM `' . $tbl . '` WHERE ' . $whereBase
        );
        if (null === $currentIp) {
            return $prior;
        }
        $alreadyRepresented = (int) Db::getInstance()->getValue(
            'SELECT COUNT(*) FROM `' . $tbl . '` WHERE ' . $whereBase
            . " AND `ip` = '" . pSQL($currentIp) . "'"
        );

        return $alreadyRepresented > 0 ? $prior : $prior + 1;
    }

    /**
     * Parse the request body. Supports both JSON (Content-Type: application/json)
     * and standard form-encoded POST. Body size is hard-capped — see
     * {@see self::MAX_PAYLOAD_BYTES}.
     *
     * @return array|null
     */
    private function readPayload()
    {
        if (isset($_SERVER['CONTENT_LENGTH']) && (int) $_SERVER['CONTENT_LENGTH'] > self::MAX_PAYLOAD_BYTES) {
            return null;
        }

        $contentType = isset($_SERVER['CONTENT_TYPE']) ? (string) $_SERVER['CONTENT_TYPE'] : '';
        if (false !== stripos($contentType, 'application/json')) {
            $raw = $this->readCappedBody();
            if (null === $raw || '' === $raw) {
                return null;
            }
            $data = json_decode($raw, true);

            return is_array($data) ? $data : null;
        }
        // sendBeacon defaults to Blob with text/plain — accept JSON in body anyway
        if (!empty($_POST)) {
            return $_POST;
        }
        $raw = $this->readCappedBody();
        if (null !== $raw && '' !== $raw) {
            $data = json_decode($raw, true);
            if (is_array($data)) {
                return $data;
            }
        }

        return null;
    }

    /**
     * Read up to {@see self::MAX_PAYLOAD_BYTES} from php://input. Documented
     * payload is `{event_type, severity, message}` where `event_type ≤ 50` and
     * `message ≤ 500`; 16 KB is ~30x worst-case headroom. Bounds memory
     * regardless of php.ini `post_max_size`.
     *
     * @return string|null
     */
    private function readCappedBody()
    {
        $fp = @fopen('php://input', 'r');
        if (false === $fp) {
            return null;
        }
        $raw = @stream_get_contents($fp, self::MAX_PAYLOAD_BYTES);
        @fclose($fp);

        return is_string($raw) ? $raw : null;
    }

    /**
     * Best-effort visitor IP. Honors REMOTE_ADDR only — we don't trust
     * X-Forwarded-For from arbitrary upstreams. Sanitized to IPv4/IPv6 only.
     *
     * @return string|null
     */
    private function getIp()
    {
        if (!isset($_SERVER['REMOTE_ADDR'])) {
            return null;
        }
        $ip = trim((string) $_SERVER['REMOTE_ADDR']);
        if ('' === $ip) {
            return null;
        }
        // Accept only valid IPv4 / IPv6 syntax. Belt + suspenders before pSQL.
        if (false === filter_var($ip, FILTER_VALIDATE_IP)) {
            return null;
        }

        return $ip;
    }

    /**
     * Multibyte-safe truncate.
     *
     * @param string $s
     * @param int $maxLen
     *
     * @return string
     */
    private function cap($s, $maxLen)
    {
        if (function_exists('mb_substr') && function_exists('mb_strlen')) {
            return mb_strlen($s) > $maxLen ? mb_substr($s, 0, $maxLen) : $s;
        }

        return strlen($s) > $maxLen ? substr($s, 0, $maxLen) : $s;
    }

    /**
     * Emit JSON response with the right Content-Type header.
     */
    private function respond(array $data)
    {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=UTF-8');
            // Do not cache. sendBeacon respects this.
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        }
        echo json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        // ModuleFrontController::display() finishes after this — no further output
        exit;
    }
}
