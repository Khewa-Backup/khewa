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

namespace PrestaChamps\MailchimpPro;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaChamps\MailchimpPro\Models\PixelLog;

/**
 * Connection-health state flag helper — central place to set / read / clear
 * the health-state flag that BO banners + cronjob circuit-breaker read.
 *
 * Design: event-driven (not polling). Flag is set by MailChimpAPI::makeRequest
 * when a failed API response matches a known broken-state pattern. Read on BO
 * page load to render the state-specific warning banner + recovery choices.
 *
 * The setFlag() call is idempotent (no-op if already at the same state).
 *
 * Note: there is no "intentional disconnect" surface here — after the Phase 4
 * Sites/Stores-tab delete lockdown, the only merchant-initiated disconnect path
 * is the Log out button, which clears the API key and stops all API traffic,
 * so no reactive flag-set can happen while disconnected. External Mailchimp-side
 * deletions ALWAYS surface the banner (that's the whole point of this feature).
 */
class HealthState
{
    public const STATE_HEALTHY = '';
    public const STATE_SITE_MISSING = 'SITE_MISSING';
    public const STATE_STORE_MISSING = 'STORE_MISSING';
    public const STATE_SITE_AND_STORE_MISSING = 'SITE_AND_STORE_MISSING';
    public const STATE_LIST_MISSING = 'LIST_MISSING';
    public const STATE_ACCOUNT_INVALID = 'ACCOUNT_INVALID';
    public const STATE_MAILCHIMP_DOWN = 'MAILCHIMP_DOWN';

    /**
     * Request-scoped source label. Entry points (cron controller, hook methods,
     * BO ajax handlers, FO telemetry receiver) call setSourceContext() before
     * their work runs. setFlag() picks it up so the recorded source reflects
     * WHY the API call fired, not just "the API client noticed a failure".
     *
     * Default 'api-client' keeps setFlag() self-descriptive if no entry point
     * has claimed the request yet (e.g. one-off scripts, tests).
     */
    private static $sourceContext = 'api-client';

    /**
     * Set the request-scoped source label. Call this ONCE at each entry point
     * before any work runs. PHP's per-request lifecycle scopes this naturally —
     * the next request starts fresh.
     */
    public static function setSourceContext($label)
    {
        self::$sourceContext = (string) $label;
    }

    /**
     * @return string the current request-scoped source label
     */
    public static function getSourceContext()
    {
        return self::$sourceContext;
    }

    /**
     * States that halt automated work. Excludes MAILCHIMP_DOWN (transient)
     * and SITE_MISSING (only affects FO pixel — BO sync targets store + list
     * endpoints, not the connected-site).
     *
     * @return string[]
     */
    public static function getBreakerStates()
    {
        return [
            self::STATE_STORE_MISSING,
            self::STATE_SITE_AND_STORE_MISSING,
            self::STATE_LIST_MISSING,
            self::STATE_ACCOUNT_INVALID,
        ];
    }

    /**
     * States where retry is the right response — merchant recovery isn't needed
     * because Mailchimp will (likely) come back on its own. Symmetric counterpart
     * to getBreakerStates().
     *
     * @return string[]
     */
    public static function getRecoverableStates()
    {
        return [
            self::STATE_MAILCHIMP_DOWN,
        ];
    }

    /**
     * @return bool whether the current flag is in a state that should halt automated work
     */
    public static function isBreakerActive()
    {
        $flag = self::getFlag();

        return self::STATE_HEALTHY !== $flag && in_array($flag, self::getBreakerStates(), true);
    }

    /**
     * Set the health-state flag. Idempotent on same state.
     *
     * Source resolution priority (highest first):
     *   1. Explicit $source argument — from tests, banner UI, healthcheck-manual
     *   2. Request-scoped context set by an entry point (cron / BO ajax handler)
     *   3. Backtrace auto-detection — if the call chain contains a hookXxxYyy
     *      method, derive 'hook-xxxYyy' from that. Single source of truth (the
     *      method name), zero boilerplate at hook sites, auto-labels any new hook.
     *   4. Fallback to the default 'api-client' context value.
     *
     * @param string $state one of the STATE_* constants
     * @param string|null $source explicit override; null = auto-resolve per above
     */
    public static function setFlag($state, $source = null)
    {
        $current = self::getFlag();
        if ($current === $state) {
            return;
        }
        // Compound-state hold — a single-resource 404 doesn't prove the other resource recovered.
        if (self::STATE_SITE_AND_STORE_MISSING === $current
            && (self::STATE_SITE_MISSING === $state || self::STATE_STORE_MISSING === $state)) {
            return;
        }
        // Escalation SITE ↔ STORE → SITE_AND_STORE_MISSING.
        if ((self::STATE_SITE_MISSING === $current && self::STATE_STORE_MISSING === $state)
            || (self::STATE_STORE_MISSING === $current && self::STATE_SITE_MISSING === $state)) {
            $state = self::STATE_SITE_AND_STORE_MISSING;
        }
        if (null === $source) {
            $source = self::$sourceContext;
            if ('api-client' === $source) {
                $detected = self::detectHookFromBacktrace();
                if (null !== $detected) {
                    $source = $detected;
                }
            }
        }
        $effectiveSource = (string) $source;
        \Configuration::updateValue(\MailchimpProConfig::MAILCHIMP_HEALTH_STATE_FLAG, $state, true);
        \Configuration::updateValue(\MailchimpProConfig::MAILCHIMP_HEALTH_STATE_SET_AT, date('Y-m-d H:i:s'));
        \Configuration::updateValue(\MailchimpProConfig::MAILCHIMP_HEALTH_STATE_SOURCE, $effectiveSource);
        if (class_exists(PixelLog::class)) {
            PixelLog::insert(
                PixelLog::SEVERITY_WARNING,
                self::mapSourceToPixelLog($effectiveSource),
                'HEALTH_FLAG_SET',
                "state={$state} source={$effectiveSource} (was={$current})"
            );
        }
    }

    /**
     * Walk the call stack looking for a PS hook frame (method matching
     * /^hook[A-Z]/). Returns 'hook-xxxYyy' derived from the first match, or
     * null. Depth cap of 15 comfortably covers the typical setFlag → makeRequest
     * → post/get → Command::run → hookXxxMethod chain (~5 frames).
     *
     * The regex requires an uppercase char after 'hook' to reject false
     * positives like 'hookable' / 'hookup' — PS convention is 'hookXxxYyy'.
     */
    private static function detectHookFromBacktrace()
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 15);
        foreach ($trace as $frame) {
            if (preg_match('/^hook[A-Z]/', $frame['function'])) {
                return 'hook-' . lcfirst(substr($frame['function'], 4));
            }
        }

        return null;
    }

    /**
     * @return string current state (empty string = HEALTHY)
     */
    public static function getFlag()
    {
        return (string) \Configuration::get(\MailchimpProConfig::MAILCHIMP_HEALTH_STATE_FLAG);
    }

    /**
     * Clear the flag (recovery action succeeded). No-op if already cleared.
     */
    public static function clearFlag()
    {
        if (!self::getFlag()) {
            return;
        }
        \Configuration::deleteByName(\MailchimpProConfig::MAILCHIMP_HEALTH_STATE_FLAG);
        \Configuration::deleteByName(\MailchimpProConfig::MAILCHIMP_HEALTH_STATE_SET_AT);
        \Configuration::deleteByName(\MailchimpProConfig::MAILCHIMP_HEALTH_STATE_SOURCE);
        if (class_exists(PixelLog::class)) {
            PixelLog::insert(
                PixelLog::SEVERITY_INFO,
                self::mapSourceToPixelLog(self::$sourceContext),
                'HEALTH_FLAG_CLEARED',
                'Recovery action succeeded'
            );
        }
    }

    /**
     * Map a HealthState source label ('cronjob', 'hook-actionValidateOrder',
     * 'bo-action-enable-pixel', 'fo-telemetry', 'api-client', …) to the
     * matching PixelLog SOURCE_* constant. Prefix-based match keeps the label
     * space open — new labels don't need this mapper updated as long as they
     * follow the {cron|hook|bo|fo}-… convention.
     */
    private static function mapSourceToPixelLog($source)
    {
        $prefix = strtolower((string) $source);
        if ('cronjob' === $prefix || 0 === strpos($prefix, 'cron')) {
            return PixelLog::SOURCE_CRON;
        }
        if (0 === strpos($prefix, 'hook')) {
            return PixelLog::SOURCE_HOOK;
        }
        if (0 === strpos($prefix, 'fo')) {
            return PixelLog::SOURCE_FO;
        }

        // 'bo-*', 'api-client', 'healthcheck-manual', 'unknown', anything else → BO
        return PixelLog::SOURCE_BO;
    }

    /**
     * Classify a failed API response into a state (or null if not a health-relevant failure).
     * Called from MailChimpAPI::makeRequest after any failed request.
     *
     * @param string $endpointPath the API path (e.g., "connected-sites/xyz" or "ecommerce/stores/xyz/products")
     * @param int $httpCode response HTTP status code
     * @param mixed $formattedResponse array (parsed JSON body) or string (raw)
     *
     * @return string|null a STATE_* constant, or null if the failure doesn't map to a health state
     */
    public static function classifyFailure($endpointPath, $httpCode, $formattedResponse, $httpVerb = null)
    {
        if ($httpCode >= 200 && $httpCode < 300) {
            return null;
        }
        // DELETE 404 = idempotent success (target already absent), not a broken state.
        if (404 === (int) $httpCode && null !== $httpVerb && 'delete' === strtolower((string) $httpVerb)) {
            return null;
        }
        if (401 === (int) $httpCode) {
            return self::STATE_ACCOUNT_INVALID;
        }
        if ((int) $httpCode >= 500) {
            return self::STATE_MAILCHIMP_DOWN;
        }
        // State-consistent skip — if the module already knows a resource isn't synced,
        // don't classify its absence as a broken state.
        if (class_exists('\\MailchimpProConfig') && class_exists('\\Configuration')) {
            if (preg_match('#^/?ecommerce/stores/#', $endpointPath)
                && !\Configuration::get(\MailchimpProConfig::MAILCHIMP_STORE_SYNCED)) {
                return null;
            }
            if (preg_match('#^/?lists/#', $endpointPath)
                && '' === (string) \Configuration::get(\MailchimpProConfig::MAILCHIMP_LIST_ID)) {
                return null;
            }
        }
        if (404 === (int) $httpCode) {
            // Site sub-endpoints have no independent lifecycle — 404 always means the parent site is gone.
            if (false !== strpos($endpointPath, 'connected-sites/')) {
                return self::STATE_SITE_MISSING;
            }
            // Store/list sub-resource 404s are ambiguous (routine "child doesn't exist yet"
            // vs parent-missing), so only classify on the direct resource endpoint.
            if (preg_match('#^/?ecommerce/stores/[^/]+$#', $endpointPath)) {
                return self::STATE_STORE_MISSING;
            }
            if (preg_match('#^/?lists/[^/]+$#', $endpointPath)) {
                return self::STATE_LIST_MISSING;
            }
        }
        if (400 === (int) $httpCode) {
            // MC empirically returns 400 with this exact body on child-endpoint calls when the parent store is gone.
            $bodyText = '';
            if (is_array($formattedResponse)) {
                $bodyText = (isset($formattedResponse['title']) ? $formattedResponse['title'] : '')
                    . ' '
                    . (isset($formattedResponse['detail']) ? $formattedResponse['detail'] : '');
            } elseif (is_string($formattedResponse)) {
                $bodyText = $formattedResponse;
            }
            if (false !== strpos($bodyText, 'store with the provided ID does not exist')) {
                return self::STATE_STORE_MISSING;
            }
        }

        // Other errors (429 rate-limit, generic 400 payload issues, etc.): not a health state.
        return null;
    }

    /**
     * Symmetric mirror of classifyFailure(): a 2xx on a resource proves any
     * matching broken-state flag is stale and should auto-heal.
     *
     * @param string $endpointPath
     * @param int $httpCode
     *
     * @return string|null a STATE_* constant to heal, or null if no auto-heal applies
     */
    public static function classifySuccess($endpointPath, $httpCode)
    {
        $code = (int) $httpCode;
        if ($code < 200 || $code >= 300) {
            return null;
        }
        if ('' === $endpointPath || '/' === $endpointPath) {
            return self::STATE_ACCOUNT_INVALID;
        }
        if (preg_match('#^/?ecommerce/stores/[^/]+(/|$)#', $endpointPath)) {
            return self::STATE_STORE_MISSING;
        }
        if (preg_match('#^/?connected-sites/[^/]+(/|$)#', $endpointPath)) {
            return self::STATE_SITE_MISSING;
        }
        if (preg_match('#^/?lists/[^/]+(/|$)#', $endpointPath)) {
            return self::STATE_LIST_MISSING;
        }

        return null;
    }

    /**
     * Transitions:
     *   - current === $evidence                                              → clearFlag()
     *   - SITE_AND_STORE_MISSING + STORE_MISSING evidence → downgrade to SITE_MISSING
     *   - SITE_AND_STORE_MISSING + SITE_MISSING evidence  → downgrade to STORE_MISSING
     *   - anything else                                                      → no-op
     *
     * Compound downgrades bypass setFlag()'s hold guard — the heal path is intentional.
     *
     * @param string $evidence a STATE_* constant returned by classifySuccess()
     */
    public static function healEvidence($evidence)
    {
        $current = self::getFlag();
        if (self::STATE_HEALTHY === $current) {
            return;
        }
        if ($current === $evidence) {
            self::clearFlag();

            return;
        }
        if (self::STATE_SITE_AND_STORE_MISSING === $current) {
            if (self::STATE_STORE_MISSING === $evidence) {
                self::writeStateBypassHold(self::STATE_SITE_MISSING);

                return;
            }
            if (self::STATE_SITE_MISSING === $evidence) {
                self::writeStateBypassHold(self::STATE_STORE_MISSING);

                return;
            }
        }
    }

    /**
     * @param string $state
     */
    private static function writeStateBypassHold($state)
    {
        $current = self::getFlag();
        if ($current === $state) {
            return;
        }
        \Configuration::updateValue(\MailchimpProConfig::MAILCHIMP_HEALTH_STATE_FLAG, $state, true);
        \Configuration::updateValue(\MailchimpProConfig::MAILCHIMP_HEALTH_STATE_SET_AT, date('Y-m-d H:i:s'));
        \Configuration::updateValue(\MailchimpProConfig::MAILCHIMP_HEALTH_STATE_SOURCE, 'heal-partial');
        if (class_exists(PixelLog::class)) {
            PixelLog::insert(
                PixelLog::SEVERITY_INFO,
                self::mapSourceToPixelLog('heal-partial'),
                'HEALTH_FLAG_HEAL',
                "state={$state} (was={$current}, partial downgrade)"
            );
        }
    }
}
