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
 *
 * Class MailchimpProConfig
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class MailchimpProConfig
{
    public const MAILCHIMP_API_KEY = 'module-mailchimpproconfig-mailchimp-api-key';

    /**
     * Cached state of POST /connected-sites/{id}/actions/verify-script-installation.
     * JSON shape: {verified: bool, last_checked_at: string, last_attempt_at: string, last_error: string|null}
     *   - `verified`: latest known server-side status (true once Mailchimp confirms
     *      via crawl/GTM check).
     *   - `last_checked_at`: timestamp of the most recent successful POST.
     *   - `last_attempt_at`: timestamp of the most recent POST attempt (success or fail).
     *   - `last_error`: error message from the last failed attempt, or null.
     *
     * Used to skip redundant verify POSTs (30-day TTL — see scriptVerify in
     * AdminMailchimpProConfigurationController), surface verification state in
     * the BO Pixel tab health panel, and audit verify history.
     */
    public const MAILCHIMP_SCRIPT_VERIFIED = 'module-mailchimpproconfig-script-verified';
    public const MAILCHIMP_LIST_ID = 'module-mailchimpproconfig-list-id';
    public const MAILCHIMP_LIST_NAME = 'module-mailchimpproconfig-list-name';
    public const MAILCHIMP_STORE_SYNCED = 'module-mailchimpproconfig-store-synced';
    public const SELECTED_PRESET = 'module-mailchimpproconfig-selected-preset';

    /**
     * Connection-health detection state — set by MailChimpAPI::makeRequest when a
     * failed API response matches a known broken-state pattern (site missing, store
     * missing, list missing, account invalid, Mailchimp down). Read on BO page load
     * to render the state-specific warning banner + recovery choices.
     *
     * Values (see PrestaChamps\MailchimpPro\HealthState constants):
     *   '' (empty)                  — HEALTHY (no banner rendered)
     *   'SITE_MISSING'              — connected_site returned 404
     *   'STORE_MISSING'             — /ecommerce/stores/{id} returned 404 OR
     *                                 child endpoint returned 400 "store … does not exist"
     *   'SITE_AND_STORE_MISSING'    — both above (typical Mailchimp UI "Remove data" pattern)
     *   'LIST_MISSING'              — /lists/{id} returned 404
     *   'ACCOUNT_INVALID'           — any endpoint returned 401 (key revoked / account suspended)
     *   'MAILCHIMP_DOWN'            — 5xx or timeout (transient)
     */
    public const MAILCHIMP_HEALTH_STATE_FLAG = 'module-mailchimpproconfig-health-state-flag';
    public const MAILCHIMP_HEALTH_STATE_SET_AT = 'module-mailchimpproconfig-health-state-set-at';
    public const MAILCHIMP_HEALTH_STATE_SOURCE = 'module-mailchimpproconfig-health-state-source';
    public const MAILCHIMP_HEALTH_LAST_CHECK_AT = 'module-mailchimpproconfig-health-last-check-at';

    /**
     * FO CDN health signal — independent of the BO HealthState flag above.
     * Set by FoCdnHealth once ≥3 distinct visitors report 403/404/410/5xx on
     * chimpstatic.com/mcjs-connected URLs within a 24h window. Stores a UNIX
     * timestamp: "consider the CDN broken until at least this time"; a past /
     * absent value = not broken. Rolling on subsequent failures.
     *
     * Deliberately separate from HealthState because chimpstatic (CDN, 403 signal)
     * and api.mailchimp.com (API, 404/401 signals) are independent Mailchimp
     * subsystems that can fail independently — BO cronjob can 400 on
     * ecommerce/stores while FO pixel POSTs to chimpstatic still return 201.
     */
    public const MAILCHIMP_FO_CDN_BROKEN_UNTIL = 'module-mailchimpproconfig-fo-cdn-broken-until';

    /**
     * Rolling JSON log of FO CDN observations. Structure:
     *   {"reports": [{"ts": 1732454800, "status": 403, "vh": "a1b2c3d4"}, ...]}
     * Entries older than 24h are pruned on every write, and the newest
     * MAX_LOG_ENTRIES are kept — visitor hashes are deduplicated when counting,
     * not when appending, so the row would otherwise grow with traffic.
     */
    public const MAILCHIMP_FO_CDN_FAILURE_LOG = 'module-mailchimpproconfig-fo-cdn-failure-log';

    /**
     * UNIX timestamp of the most recent visitor report that MC.js executed.
     * Recent positive evidence outranks failure reports: it proves the CDN is
     * serving the loader, so the failures are client-side (blocker / CSP).
     */
    public const MAILCHIMP_FO_CDN_LAST_SUCCESS = 'module-mailchimpproconfig-fo-cdn-last-success';

    public const STATUSES_FOR_PAID = 'module-mailchimpproconfig-statuses-for-paid';
    public const STATUSES_FOR_PENDING = 'module-mailchimpproconfig-statuses-for-pending';
    public const STATUSES_FOR_REFUNDED = 'module-mailchimpproconfig-statuses-for-refunded';
    public const STATUSES_FOR_CANCELLED = 'module-mailchimpproconfig-statuses-for-cancelled';
    public const STATUSES_FOR_SHIPPED = 'module-mailchimpproconfig-statuses-for-shipped';

    public const PRODUCT_IMAGE_SIZE = 'module-mailchimpproconfig-product-image-size';
    public const PRODUCT_DESCRIPTION_FIELD = 'module-mailchimpproconfig-product-description-field';
    public const PRODUCT_SYNC_FILTER_ACTIVE = 'module-mailchimpproconfig-product-sync-filter-active';
    public const PRODUCT_SYNC_FILTER_VISIBILITY = 'module-mailchimpproconfig-product-sync-filter-visibility';

    public const CUSTOMER_SYNC_FILTER_ENABLED = 'module-mailchimpproconfig-customer-sync-filter-enabled';
    public const CUSTOMER_SYNC_FILTER_NEWSLETTER = 'module-mailchimpproconfig-customer-sync-filter-newsletter';
    public const CUSTOMER_SYNC_FILTER_PERIOD = 'module-mailchimpproconfig-customer-sync-filter-period';
    public const CUSTOMER_SYNC_TAG_DEFAULT_GROUP = 'module-mailchimpproconfig-customer-sync-tag-default-group';
    public const CUSTOMER_SYNC_TAG_GENDER = 'module-mailchimpproconfig-customer-sync-tag-gender';
    public const CUSTOMER_SYNC_TAG_LANGUAGE = 'module-mailchimpproconfig-customer-sync-tag-language';
    /**
     * Opt-in: also take the COMPANY merge field from the customer's address
     * when their own company field is empty. Off by default — a company on an
     * address is a delivery detail, not a declaration that the customer is a
     * business, and shops segmenting on COMPANY need that distinction. Shops
     * without PrestaShop's B2B mode have no customer company field at all and
     * will want this on.
     */
    public const CUSTOMER_SYNC_COMPANY_FROM_ADDRESS = 'module-mailchimpproconfig-customer-sync-company-from-address';
    // Last known COMPANY-field state ('exists'|'missing'|'unknown'). Runtime
    // cache, not seeded/defaulted — written when the module creates the field
    // or a BO object view lists the audience's fields.
    public const COMPANY_FIELD_STATUS = 'module-mailchimpproconfig-company-field-status';

    public const CART_RULE_SYNC_FILTER_STATUS = 'module-mailchimpproconfig-cart-rule-sync-filter-status';
    public const CART_RULE_SYNC_FILTER_EXPIRATION = 'module-mailchimpproconfig-cart-rule-sync-filter-expiration';

    public const EXISTING_ORDER_SYNC_STRATEGY = 'module-mailchimpproconfig-existing-order-sync-strategy';

    public const NEWSLETTER_SUBSCRIBER_SYNC_FILTER_PERIOD = 'module-mailchimpproconfig-newsletter-subscriber-sync-filter-period';

    public const MULTI_INSTANCE_MODE = 'module-mailchimpproconfig-multi-instance-mode';

    public const SYNC_PRODUCTS = 'module-mailchimpproconfig-sync-products';
    public const SYNC_CUSTOMERS = 'module-mailchimpproconfig-sync-customers';
    public const SYNC_CART_RULES = 'module-mailchimpproconfig-sync-cart-rules';
    public const SYNC_ORDERS = 'module-mailchimpproconfig-sync-orders';
    public const SYNC_CARTS = 'module-mailchimpproconfig-sync-carts';
    public const SYNC_CARTS_PASSW = 'module-mailchimpproconfig-sync-carts-passw';
    public const SYNC_NEWSLETTER_SUBSCRIBERS = 'module-mailchimpproconfig-sync-newsletter-subscribers';

    public const CRONJOB_SECURE_TOKEN = 'module-mailchimpproconfig-cronjob-secure-token';
    public const LOG_QUEUE = 'module-mailchimpproconfig-log-queue';
    public const QUEUE_STEP = 'module-mailchimpproconfig-queue-step';
    public const QUEUE_ATTEMPT = 'module-mailchimpproconfig-queue-attempt';
    public const LOG_CRONJOB = 'module-mailchimpproconfig-log-cronjob';
    public const LAST_CRONJOB = 'module-mailchimpproconfig-last-cronjob';
    public const LAST_CRONJOB_EXECUTION_TIME = 'module-mailchimpproconfig-last-cronjob-execution-time';
    public const CRONJOB_BASED_SYNC = 'module-mailchimpproconfig-cronjob-based-sync';
    public const CRONJOB_BASED_SYNC_FOR_MULTI_STORE = 'module-mailchimpproconfig-cronjob-based-sync-for-multi-store';

    public const LAST_SYNCED_PRODUCT_ID = 'module-mailchimpproconfig-last-synced-product-id';
    public const LAST_SYNCED_CUSTOMER_ID = 'module-mailchimpproconfig-last-synced-customer-id';
    public const LAST_SYNCED_PROMO_ID = 'module-mailchimpproconfig-last-synced-promo-id';
    public const LAST_SYNCED_ORDER_ID = 'module-mailchimpproconfig-last-synced-order-id';
    public const LAST_SYNCED_CART_ID = 'module-mailchimpproconfig-last-synced-cart-id';
    public const LAST_SYNCED_NEWSLETTER_SUBSCRIBER_ID = 'module-mailchimpproconfig-last-synced-newsletter-subscriber-id';

    public const DELETED_JOBS_ON_UPGRADE_COUNT = 'module-mailchimpproconfig-deleted-jobs-on-upgrade-count';
    public const DELETED_JOBS_ON_UPGRADE_DATE = 'module-mailchimpproconfig-deleted-jobs-on-upgrade-date';
    public const DELETED_JOBS_ON_UPGRADE_ACCEPTED_MESSAGE_DATE = 'module-mailchimpproconfig-deleted-jobs-on-upgrade-accepted-message-date';
    public const DELETED_JOBS_ON_UPGRADE_ACCEPTED_MESSAGE_EMPLOYEE = 'module-mailchimpproconfig-deleted-jobs-on-upgrade-accepted-message-employee';

    public const MAILCHIMP_SCRIPT_CACHED = 'module-mailchimpproconfig-script-cached';
    public const MAILCHIMP_SCRIPT_CACHED_DATE = 'module-mailchimpproconfig-script-cached-date';
    public const MAILCHIMP_PIXEL_CSP_STATUS = 'module-mailchimpproconfig-pixel-csp-status';
    public const MAILCHIMP_PIXEL_CSP_CHECKED_AT = 'module-mailchimpproconfig-pixel-csp-checked-at';
    public const MAILCHIMP_PIXEL_LOG_ENABLED = 'module-mailchimpproconfig-pixel-log-enabled';
    public const MAILCHIMP_PIXEL_LOG_MAX_ROWS = 'module-mailchimpproconfig-pixel-log-max-rows';
    public const MAILCHIMP_PIXEL_LOG_RATE_LIMIT_PER_MIN = 'module-mailchimpproconfig-pixel-log-rate-limit-per-min';
    public const MAILCHIMP_PIXEL_AUTOTRACK_ENABLED = 'module-mailchimpproconfig-pixel-autotrack-enabled';
    public const MAILCHIMP_PIXEL_IDENTIFY_FORMAT = 'module-mailchimpproconfig-pixel-identify-format';
    public const MAILCHIMP_PIXEL_IDENTIFY_AJAX_MODE = 'module-mailchimpproconfig-pixel-identify-ajax-mode';
    public const MAILCHIMP_PIXEL_LOG_LEVEL = 'module-mailchimpproconfig-pixel-log-level';
    /**
     * Master toggle for the JS-side form-identify watcher (newsletter signup,
     * restock alerts, contact form, forgot password). When enabled and a
     * visitor submits one of those forms with a valid email, the bridge fires
     * api.identify({type, value}) once — provided the visitor isn't already
     * identified via login or guest mid-checkout (those take priority).
     */
    public const MAILCHIMP_PIXEL_IDENTIFY_ON_FORMS = 'module-mailchimpproconfig-pixel-identify-on-forms';
    /**
     * Per-form admin-supplied CSS selector overrides — comma-separated lists
     * of CSS selectors that the bridge MERGES into its built-in defaults when
     * scanning for the form on each page. Useful when a custom theme or
     * 3rd-party module replaces the stock form ID / wrapper class so the
     * default selectors miss. Server-side validated against a safe-character
     * whitelist (alphanumerics + standard CSS selector punctuation) and
     * capped at 500 chars each.
     */
    public const MAILCHIMP_PIXEL_IDENTIFY_NEWSLETTER_SELECTORS_CUSTOM = 'module-mailchimpproconfig-pixel-identify-newsletter-selectors-custom';
    /**
     * Restock-alert form (ps_emailalerts) — admin-supplied selectors for the
     * SUBMIT BUTTON. Click-event delegation (not submit) because the module's
     * restock UI is JS-driven AJAX with no real <form>: the visitor enters
     * an email then clicks "Notify me when available".
     *
     * Combined at runtime with the hardcoded core defaults in
     * MailchimpProConfig::$pixelFormCoreDefaults['restock'] — the union
     * covers both ps_emailalerts' default template (classic theme) and
     * Hummingbird's data-ps-* override. See sanitizePixelFormSelectorsCustom
     * for the safe-character allowlist + length cap.
     */
    public const MAILCHIMP_PIXEL_IDENTIFY_RESTOCK_SELECTORS_CUSTOM = 'module-mailchimpproconfig-pixel-identify-restock-selectors-custom';
    /**
     * Contact form (stock contactform module) — admin-supplied selectors
     * for the FORM itself (submit-event delegation). PS contactform is
     * form-POST by default; bridge captures the email pre-navigation +
     * the pending-flag handoff recovers post-reload if the SDK didn't
     * persist in time.
     *
     * Combined at runtime with the hardcoded core defaults in
     * MailchimpProConfig::$pixelFormCoreDefaults['contact'] AND with
     * a runtime selector derived from window.prestashop.urls.pages.contact
     * (which auto-handles multi-lingual contact URLs without admin
     * having to paste anything).
     */
    public const MAILCHIMP_PIXEL_IDENTIFY_CONTACT_SELECTORS_CUSTOM = 'module-mailchimpproconfig-pixel-identify-contact-selectors-custom';
    /**
     * Forgot-password form (PS core AuthController) — admin-supplied
     * selectors for the FORM itself (submit-event delegation). Unlike
     * the other 3 form watchers, this one targets a CORE PS feature, not
     * a module — so coreDefaults are always active (no Module::isEnabled
     * gate). The form is form-POST + page reload (always — no AJAX
     * variant in stock PS), so identify lands via the two-phase
     * delivery (best-effort same-page + guaranteed post-reload via
     * pending-flag handoff).
     *
     * Combined at runtime with the hardcoded core defaults in
     * MailchimpProConfig::$pixelFormCoreDefaults['forgotPassword'] AND
     * with a runtime selector derived from window.prestashop.urls.pages.password
     * (multi-lingual / friendly-URL safe).
     */
    public const MAILCHIMP_PIXEL_IDENTIFY_FORGOT_PASSWORD_SELECTORS_CUSTOM = 'module-mailchimpproconfig-pixel-identify-forgot-password-selectors-custom';
    /**
     * Gates our PHP handler for the `displayProductAdditionalInfo` PS hook.
     * When false (default), the handler returns '' immediately — the
     * universal [data-product] JSON parser in the bridge handles
     * PRODUCT_VIEWED for every PS 1.7.0.6 → 9.1 × classic + hummingbird
     * combination, making the hook output redundant. When true, the hook
     * also emits an inline queue push (the legacy server-side path).
     * Reserved for edge cases where a custom theme strips [data-product]
     * AND the bridge cascade fails on it.
     */
    public const MAILCHIMP_PIXEL_HOOK_DISPLAYPRODUCTADDITIONALINFO_ENABLED = 'module-mailchimpproconfig-pixel-hook-displayproductadditionalinfo-enabled';
    /**
     * Custom checkout controller php_self values — escape hatch for 3rd-party
     * checkout modules (OnePageCheckoutPS, SuperCheckout, etc.) whose pages
     * don't match stock PS's `php_self === 'order'` AND don't preserve the
     * `page_name === 'checkout'` convention.
     *
     * Comma-separated list. The bundle is loaded on a page when:
     *   - php_self === 'order' (stock PS — always works), OR
     *   - page_name === 'checkout' (theme convention — most well-behaved
     *     3rd-party modules preserve this), OR
     *   - php_self IS in this list.
     *
     * Default: 'opc' covers OnePageCheckoutPS's typical override. Admin
     * extends as needed via Pixel tab → Advanced settings.
     */
    public const MAILCHIMP_PIXEL_CHECKOUT_CONTROLLERS = 'module-mailchimpproconfig-pixel-checkout-controllers';

    /**
     * 3-prong detection escape hatch for PURCHASED — comma-separated list of
     * custom order-confirmation controller php_self values for 3rd-party
     * checkout modules whose confirmation controller doesn't extend stock
     * OrderConfirmationController (and therefore won't fire displayOrderConfirmation).
     *
     * setMedia registers `mc-pixel-bridge-purchase.js` when ANY of:
     *   - php_self === 'order-confirmation' (stock OrderConfirmationController), OR
     *   - page_name === 'order-confirmation' (theme convention — most well-
     *     behaved 3rd-party modules preserve this), OR
     *   - php_self IS in this list.
     *
     * Layer 1 of the cascade is the displayOrderConfirmation hook (fires for
     * stock + any 3rd-party module that calls Hook::exec on it). Layer 2 is
     * AJAX `pixelorderinfo.php` for modules that DON'T fire the hook — the
     * bridge needs to be loaded for those, which this config enables.
     *
     * Default: empty. Most 3rd-party modules extend OrderConfirmationController
     * and fire the hook naturally, so out-of-the-box only stock prong 1 +
     * convention prong 2 are needed.
     */
    public const MAILCHIMP_PIXEL_ORDER_CONFIRMATION_CONTROLLERS = 'module-mailchimpproconfig-pixel-order-confirmation-controllers';

    /**
     * Strict-consent mode for GDPR / ePrivacy compliance.
     *
     * When OFF (default): pixel tracks by default. Mailchimp SDK's hasOptedIn()
     * returns true when `mc_user_optin` cookie is unset (industry-norm opt-out
     * model — matches Meta Pixel, GA4, TikTok Pixel). Merchant's CMP only
     * needs to set the cookie to "false" on decline.
     *
     * When ON: hookDisplayHeader emits an inline <script> BEFORE MC.js that
     * writes `mc_user_optin=false` cookie (only if not already set), so the
     * SDK defaults to opted-out. Merchant's CMP must explicitly call
     * `window.$mcSite.enableOptIn()` or write `mc_user_optin=true` on accept.
     * This is the required mode for strict-jurisdiction merchants (Germany,
     * France, Italy) where prior explicit consent is mandatory.
     */
    public const MAILCHIMP_PIXEL_REQUIRE_CONSENT = 'module-mailchimpproconfig-pixel-require-consent';
    /**
     * Power-user tunable for the SDK-init settle delay. The bridge waits this
     * many milliseconds after detecting `$mcSite.pixel.installed === true`
     * before firing the FIRST api.track() / api.identify() — the SDK's
     * internal `c.initialized` flag can lag one microtask behind the public
     * `installed` flag in some builds.
     *
     * Default {@see PIXEL_SDK_SETTLE_DELAY_MS_DEFAULT}, clamped to the
     * range [PIXEL_SDK_SETTLE_DELAY_MS_MIN, PIXEL_SDK_SETTLE_DELAY_MS_MAX]
     * on every read/write. Bump higher (750 or 1000) via the BO Pixel tab
     * if "Pixel not initialized" console errors persist on a particular
     * environment.
     */
    public const MAILCHIMP_PIXEL_SDK_SETTLE_DELAY_MS = 'module-mailchimpproconfig-pixel-sdk-settle-delay-ms';
    /**
     * Single source of truth for the SDK settle delay's default + range.
     * Used by:
     *   - $defaultValues + seedPixelDefaultsIfMissing (the seeded value)
     *   - AdminMailchimpProConfigurationController::getConfigValues (read fallback)
     *   - AdminMailchimpProConfigurationController::ajaxProcessSaveSettings (write clamp)
     *   - mailchimppro::hookActionFrontControllerSetMedia (FO JsDef exposure)
     *   - mc-pixel-bridge.js getSdkSettleDelayMs (FO clamp + fallback)
     *   - views/js/configuration/main.js Vue data init (BO fallback)
     *   - views/templates/admin/configuration/_pixel.tpl (input min/max attrs).
     *
     * Three values, three constants — no magic numbers anywhere else.
     */
    public const PIXEL_SDK_SETTLE_DELAY_MS_DEFAULT = 500;
    public const PIXEL_SDK_SETTLE_DELAY_MS_MIN = 100;
    public const PIXEL_SDK_SETTLE_DELAY_MS_MAX = 5000;
    /**
     * Local cache of connected_site.is_pixel_enabled (the canonical
     * Mailchimp-side toggle). JSON shape:
     *   {enabled: bool, last_checked_at: 'YYYY-MM-DD HH:MM:SS', source: 'api'|'fo_bridge'}.
     *
     * Refreshed by refreshPixelEnabledState() on script-cache events
     * (which calls GET /connected-sites/{id}) and by the FO bridge's
     * runtime probe via /pixeltelemetry.
     */
    public const MAILCHIMP_PIXEL_AVAILABLE = 'module-mailchimpproconfig-pixel-available';
    public const MAILCHIMP_AUTO_AUDIENCE_SYNC = 'module-mailchimpproconfig-auto-audience-sync';
    public const MAILCHIMP_AUTO_AUDIENCE_SYNC_ACCEPTED_MESSAGE_DATE = 'module-mailchimpproconfig-auto-audience-sync-accepted-message-date';
    public const MAILCHIMP_AUTO_AUDIENCE_SYNC_ACCEPTED_MESSAGE_EMPLOYEE = 'module-mailchimpproconfig-auto-audience-sync-accepted-message-employee';

    public const SHOW_DASHBOARD_STATS = 'module-mailchimpproconfig-show-dashboard-stats';
    public const MAILCHIMP_REPORTS_CACHED = 'module-mailchimpproconfig-reports-cached';
    public const MAILCHIMP_REPORTS_CACHED_DATE = 'module-mailchimpproconfig-reports-cached-date';

    public const MAILCHIMP_CART_RULE_ID = 'module-mailchimpproconfig-cart-rule-id';

    public const MAILCHIMP_ACCOUNT_ID_LOGGED_OUT = 'module-mailchimpproconfig-mailchimp-account-id-logged-out';

    public const MAILCHIMP_PROMO_OVERRIDE_ENABLED = 'module-mailchimpproconfig-mailchimp-promo-override-enabled';
    public const MAILCHIMP_PROMO_OVERRIDE_AUTO_INSTALLED = 'module-mailchimpproconfig-mailchimp-promo-override-auto-installed';
    public const MAILCHIMP_PROMO_OVERRIDE_CONFLICTS = 'module-mailchimpproconfig-mailchimp-promo-override-conflicts';

    public static $jsonValues = [
        self::STATUSES_FOR_PAID,
        self::STATUSES_FOR_PENDING,
        self::STATUSES_FOR_REFUNDED,
        self::STATUSES_FOR_CANCELLED,
        self::STATUSES_FOR_SHIPPED,
        self::PRODUCT_SYNC_FILTER_ACTIVE,
        self::PRODUCT_SYNC_FILTER_VISIBILITY,
        self::CUSTOMER_SYNC_FILTER_ENABLED,
        self::CUSTOMER_SYNC_FILTER_NEWSLETTER,
        /* self::CUSTOMER_SYNC_TAG_DEFAULT_GROUP, */
        self::CUSTOMER_SYNC_TAG_GENDER,
        self::CUSTOMER_SYNC_TAG_LANGUAGE,
        self::CART_RULE_SYNC_FILTER_STATUS,
        self::CART_RULE_SYNC_FILTER_EXPIRATION,
        self::MAILCHIMP_PROMO_OVERRIDE_CONFLICTS,
        self::MAILCHIMP_PIXEL_CSP_STATUS,
        self::MAILCHIMP_PIXEL_AVAILABLE,
        self::MAILCHIMP_SCRIPT_VERIFIED,
    ];

    public static $defaultValues = [
        self::PRODUCT_SYNC_FILTER_ACTIVE => [1],
        self::PRODUCT_SYNC_FILTER_VISIBILITY => ['both', 'catalog', 'search', 'none'],
        self::CUSTOMER_SYNC_FILTER_ENABLED => [1],
        self::CUSTOMER_SYNC_FILTER_NEWSLETTER => [1],
        self::CUSTOMER_SYNC_FILTER_PERIOD => 'all',
        self::CUSTOMER_SYNC_TAG_DEFAULT_GROUP => 'default',
        self::CUSTOMER_SYNC_TAG_GENDER => true,
        self::CUSTOMER_SYNC_TAG_LANGUAGE => false,
        self::CUSTOMER_SYNC_COMPANY_FROM_ADDRESS => false,
        self::CART_RULE_SYNC_FILTER_STATUS => [1],
        self::CART_RULE_SYNC_FILTER_EXPIRATION => [1],
        self::NEWSLETTER_SUBSCRIBER_SYNC_FILTER_PERIOD => 'all',
        self::MULTI_INSTANCE_MODE => false,
        self::CRONJOB_BASED_SYNC => true,
        self::SYNC_PRODUCTS => true,
        self::SYNC_CUSTOMERS => true,
        self::SYNC_CART_RULES => true,
        self::SYNC_ORDERS => true,
        self::SYNC_CARTS => true,
        self::SYNC_CARTS_PASSW => false,
        self::SYNC_NEWSLETTER_SUBSCRIBERS => true,
        self::LOG_QUEUE => 0,
        self::LOG_CRONJOB => 1,
        self::QUEUE_STEP => 50,
        self::QUEUE_ATTEMPT => 2,
        self::PRODUCT_DESCRIPTION_FIELD => 'description',
        self::EXISTING_ORDER_SYNC_STRATEGY => '-1 year',
        self::STATUSES_FOR_PAID => [],
        self::STATUSES_FOR_PENDING => [],
        self::STATUSES_FOR_REFUNDED => [],
        self::STATUSES_FOR_CANCELLED => [],
        self::STATUSES_FOR_SHIPPED => [],
        self::SHOW_DASHBOARD_STATS => true,
        self::MAILCHIMP_PROMO_OVERRIDE_ENABLED => false,
        self::MAILCHIMP_PIXEL_LOG_ENABLED => 1,
        self::MAILCHIMP_PIXEL_LOG_MAX_ROWS => 10000,
        self::MAILCHIMP_PIXEL_LOG_RATE_LIMIT_PER_MIN => 60,
        self::MAILCHIMP_PIXEL_AUTOTRACK_ENABLED => 1,
        self::MAILCHIMP_PIXEL_IDENTIFY_FORMAT => 'EMAIL_SHA256',
        self::MAILCHIMP_PIXEL_IDENTIFY_AJAX_MODE => 0,
        self::MAILCHIMP_PIXEL_IDENTIFY_ON_FORMS => 1,
        // Admin-supplied CSS selector overrides — comma-separated string.
        // ADDITIVE to the hardcoded core defaults in $pixelFormCoreDefaults
        // below; admin uses the BO Multiselect to add theme/module-specific
        // selectors beyond what's built in. Default empty.
        self::MAILCHIMP_PIXEL_IDENTIFY_NEWSLETTER_SELECTORS_CUSTOM => '',
        // Same shape as newsletter — admin-supplied additive selectors for
        // the restock-alert click watcher; built-in selectors live in
        // $pixelFormCoreDefaults['restock'] and are always active.
        self::MAILCHIMP_PIXEL_IDENTIFY_RESTOCK_SELECTORS_CUSTOM => '',
        // Same shape as newsletter/restock — admin-supplied additive
        // selectors for the contact form (submit watcher); built-ins live
        // in $pixelFormCoreDefaults['contact'] + a runtime URL selector
        // added by the bridge.
        self::MAILCHIMP_PIXEL_IDENTIFY_CONTACT_SELECTORS_CUSTOM => '',
        // Same shape as the other 3 form-identify selector keys —
        // additive list of admin-supplied selectors for the forgot-
        // password form. Built-ins always active via
        // $pixelFormCoreDefaults['forgotPassword'] (no module gate
        // because the form is a PS core feature, not a module).
        self::MAILCHIMP_PIXEL_IDENTIFY_FORGOT_PASSWORD_SELECTORS_CUSTOM => '',
        self::MAILCHIMP_PIXEL_LOG_LEVEL => 'default',
        self::MAILCHIMP_PIXEL_HOOK_DISPLAYPRODUCTADDITIONALINFO_ENABLED => false,
        self::MAILCHIMP_PIXEL_CHECKOUT_CONTROLLERS => 'opc',
        self::MAILCHIMP_PIXEL_ORDER_CONFIRMATION_CONTROLLERS => '',
        self::MAILCHIMP_PIXEL_REQUIRE_CONSENT => 0,
        self::MAILCHIMP_PIXEL_SDK_SETTLE_DELAY_MS => self::PIXEL_SDK_SETTLE_DELAY_MS_DEFAULT,
        self::MAILCHIMP_SCRIPT_VERIFIED => [],
    ];

    public static $keyMap = [
        'selectedPreset' => self::SELECTED_PRESET,
        'multiInstanceMode' => self::MULTI_INSTANCE_MODE,
        'cronjobBasedSync' => self::CRONJOB_BASED_SYNC,
        'syncProducts' => self::SYNC_PRODUCTS,
        'syncCustomers' => self::SYNC_CUSTOMERS,
        'syncCartRules' => self::SYNC_CART_RULES,
        'syncOrders' => self::SYNC_ORDERS,
        'syncCarts' => self::SYNC_CARTS,
        'syncCartsPassw' => self::SYNC_CARTS_PASSW,
        'syncNewsletterSubscribers' => self::SYNC_NEWSLETTER_SUBSCRIBERS,
        'statusForPending' => self::STATUSES_FOR_PENDING,
        'statusForRefunded' => self::STATUSES_FOR_REFUNDED,
        'statusForCancelled' => self::STATUSES_FOR_CANCELLED,
        'statusForShipped' => self::STATUSES_FOR_SHIPPED,
        'statusForPaid' => self::STATUSES_FOR_PAID,
        'productDescriptionField' => self::PRODUCT_DESCRIPTION_FIELD,
        'existingOrderSyncStrategy' => self::EXISTING_ORDER_SYNC_STRATEGY,
        'productSyncFilterActive' => self::PRODUCT_SYNC_FILTER_ACTIVE,
        'productSyncFilterVisibility' => self::PRODUCT_SYNC_FILTER_VISIBILITY,
        'customerSyncFilterEnabled' => self::CUSTOMER_SYNC_FILTER_ENABLED,
        'customerSyncFilterNewsletter' => self::CUSTOMER_SYNC_FILTER_NEWSLETTER,
        'customerSyncFilterPeriod' => self::CUSTOMER_SYNC_FILTER_PERIOD,
        'customerSyncTagDefaultGroup' => self::CUSTOMER_SYNC_TAG_DEFAULT_GROUP,
        'customerSyncTagGender' => self::CUSTOMER_SYNC_TAG_GENDER,
        'customerSyncTagLanguage' => self::CUSTOMER_SYNC_TAG_LANGUAGE,
        'customerSyncCompanyFromAddress' => self::CUSTOMER_SYNC_COMPANY_FROM_ADDRESS,
        'cartRuleSyncFilterStatus' => self::CART_RULE_SYNC_FILTER_STATUS,
        'cartRuleSyncFilterExpiration' => self::CART_RULE_SYNC_FILTER_EXPIRATION,
        'newsletterSubscriberSyncFilterPeriod' => self::NEWSLETTER_SUBSCRIBER_SYNC_FILTER_PERIOD,
        'productImageSize' => self::PRODUCT_IMAGE_SIZE,
        'pixelLogEnabled' => self::MAILCHIMP_PIXEL_LOG_ENABLED,
        'pixelLogMaxRows' => self::MAILCHIMP_PIXEL_LOG_MAX_ROWS,
        'pixelLogRateLimitPerMin' => self::MAILCHIMP_PIXEL_LOG_RATE_LIMIT_PER_MIN,
        'autotrackEnabled' => self::MAILCHIMP_PIXEL_AUTOTRACK_ENABLED,
        'identifyFormat' => self::MAILCHIMP_PIXEL_IDENTIFY_FORMAT,
        'identifyAjaxMode' => self::MAILCHIMP_PIXEL_IDENTIFY_AJAX_MODE,
        'identifyOnForms' => self::MAILCHIMP_PIXEL_IDENTIFY_ON_FORMS,
        'identifyNewsletterSelectorsCustom' => self::MAILCHIMP_PIXEL_IDENTIFY_NEWSLETTER_SELECTORS_CUSTOM,
        'identifyRestockSelectorsCustom' => self::MAILCHIMP_PIXEL_IDENTIFY_RESTOCK_SELECTORS_CUSTOM,
        'identifyContactSelectorsCustom' => self::MAILCHIMP_PIXEL_IDENTIFY_CONTACT_SELECTORS_CUSTOM,
        'identifyForgotPasswordSelectorsCustom' => self::MAILCHIMP_PIXEL_IDENTIFY_FORGOT_PASSWORD_SELECTORS_CUSTOM,
        'pixelLogLevel' => self::MAILCHIMP_PIXEL_LOG_LEVEL,
        'pixelCheckoutControllers' => self::MAILCHIMP_PIXEL_CHECKOUT_CONTROLLERS,
        'pixelOrderConfirmationControllers' => self::MAILCHIMP_PIXEL_ORDER_CONFIRMATION_CONTROLLERS,
        'pixelRequireConsent' => self::MAILCHIMP_PIXEL_REQUIRE_CONSENT,
        'sdkSettleDelayMs' => self::MAILCHIMP_PIXEL_SDK_SETTLE_DELAY_MS,
        'listId' => self::MAILCHIMP_LIST_ID,
        'storeSynced' => self::MAILCHIMP_STORE_SYNCED,
        'logQueue' => self::LOG_QUEUE,
        'queueStep' => self::QUEUE_STEP,
        'queueAttempt' => self::QUEUE_ATTEMPT,
        'logCronjob' => self::LOG_CRONJOB,
        'showDashboardStats' => self::SHOW_DASHBOARD_STATS,
        'promoOverridesEnabled' => self::MAILCHIMP_PROMO_OVERRIDE_ENABLED,
        'promoOverridesAutoInstalled' => self::MAILCHIMP_PROMO_OVERRIDE_AUTO_INSTALLED,
        'promoOverridesConflicts' => self::MAILCHIMP_PROMO_OVERRIDE_CONFLICTS,
    ];

    /** Required for PHP < 5.6 compatibility */
    public static $className = 'MailchimpProConfig';

    public static $multiLang = [
    ];

    /**
     * Hardcoded built-in CSS selectors per form-identify target. ALWAYS
     * active when the master "Fire identify on form submissions" toggle is
     * on — admins can't remove these (only the master toggle disables
     * them). The BO renders them as disabled tags in the Multiselect so
     * admins can SEE what's matching by default; the admin's saved value
     * (in the corresponding *_SELECTORS_CUSTOM config key) is purely
     * ADDITIVE — selectors admins paste there get UNIONed with these
     * built-ins.
     *
     * Selectors chosen for portability across stock PS 1.7.0.6 → 9.1 on
     * classic v1/v2/v3 + hummingbird. NO selector is 100% guaranteed —
     * themes can override module templates — so admins have an additive
     * field to paste theme-specific selectors when the built-ins miss.
     *
     * Newsletter (ps_emailsubscription):
     *   form:has(input[name="submitNewsletter"])
     *       MOST ROBUST. Matches the form via the module's stable submit-
     *       button name. :has() support: Chrome 105+, Safari 15.4+,
     *       Firefox 121+ (end-2023).
     *   .block_newsletter form
     *       Classic v1/v2/v3 wraps the form in <section|div class="block_newsletter">.
     *   #block_newsletter form
     *       Older classic templates used a static id="block_newsletter" wrapper.
     *   [id^="blockEmailSubscription"] form
     *       Newer PS 8+ module gives the wrapper a dynamic id of shape
     *       "blockEmailSubscription_{$hookName}" — prefix-match the stable part.
     *   form[action*="emailsubscription" i]
     *       Case-insensitive action URL fallback. Matches BOTH PS master's
     *       "#blockEmailSubscription_..." and hummingbird's "#emailsubscription_anchor_...".
     */
    /**
     * Restock-alert (ps_emailalerts):
     *   .js-mailalert-add
     *       PRIMARY — module's stable button class shipped in
     *       views/templates/hook/product.tpl. Verified unchanged across
     *       module versions v2.0.0 → master (current) on PS 1.7.0+ → 9.x.
     *       jQuery click delegation lives in modules/ps_emailalerts/js/mailalerts.js.
     *   [data-ps-action="emailalerts-subscribe"]
     *       Hummingbird (PS 9.0+) — authoritative selector from the theme's
     *       constants/selectors-map.ts. Theme ships a complete template
     *       rewrite at themes/hummingbird/modules/ps_emailalerts/views/templates/hook/product.tpl
     *       that drops the .js-mailalert class in favor of data-ps-* attrs.
     *   [data-url*="ps_emailalerts"] button
     *       3rd-party theme fallback — the module's AJAX URL (data-url on the
     *       wrapper) is stable across versions; this catches custom themes
     *       that keep the wrapper attribute but strip both stock button
     *       selectors. The bridge picks email input via
     *       closest('.js-mailalert, [data-ps-ref="emailalerts"]') walk-up.
     */
    public static $pixelFormCoreDefaults = [
        'newsletter' => [
            'form:has(input[name="submitNewsletter"])',
            '.block_newsletter form',
            '#block_newsletter form',
            '[id^="blockEmailSubscription"] form',
            'form[action*="emailsubscription" i]',
        ],
        'restock' => [
            '.js-mailalert-add',
            '[data-ps-action="emailalerts-subscribe"]',
            '[data-url*="ps_emailalerts"] button',
        ],
        /*
         * Contact form (contactform module):
         *   form:has(select[name="id_contact"])
         *       PRIMARY — the subject dropdown's `id_contact` name is unique
         *       to the contactform module across module v1+ → master + all
         *       theme overrides. NOT shared with order-messages (which uses
         *       id_product / id_order / msgText) so no false-positives on
         *       the customer order-history "add a message" form.
         *   section.contact-form form
         *       THEME CONVENTION — both classic and Hummingbird theme
         *       overrides wrap the contact form in this section class.
         *       The module's own template uses .login-form so this only
         *       fires when a theme override is active (which is most stock
         *       deployments).
         *
         * NOTE: a third runtime-only selector is appended by the bridge JS
         * from window.prestashop.urls.pages.contact — this auto-handles
         * multi-lingual / friendly-URL contact pages without admin
         * configuration. Not stored here because it's per-page-load.
         */
        'contact' => [
            'form:has(select[name="id_contact"])',
            'section.contact-form form',
        ],
        /*
         * Forgot-password form (PS core AuthController / PasswordController):
         *   form.forgotten-password
         *       PRIMARY — the `forgotten-password` class is rendered by
         *       BOTH stock theme overrides (classic + Hummingbird) in
         *       templates/customer/password-email.tpl. Stable across PS
         *       1.7+ → 9.x. Unique to this form (not reused elsewhere).
         *   form:has(button#send-reset-link)
         *       SECONDARY — submit button's stable ID. Both themes ship
         *       it; custom themes that strip the class typically keep
         *       the button as-is.
         *
         * NOTE: a third runtime-only selector is appended by the bridge
         * JS from window.prestashop.urls.pages.password — catches
         * multi-lingual password URLs (/mot-de-passe-oublie,
         * /passwort-vergessen, etc.) without admin paste.
         *
         * NO module gate — forgot-password is PS core, always present.
         */
        'forgotPassword' => [
            'form.forgotten-password',
            'form:has(button#send-reset-link)',
        ],
    ];

    /**
     * Save a config value.
     *
     * @return bool
     */
    public static function saveValue($key, $value)
    {
        return Configuration::updateValue($key, $value, true);
    }

    /**
     * Seed pixel feature defaults into ps_configuration so FO reads (raw
     * Configuration::get without sanitizeDefault) match the BO display from
     * the moment the storefront becomes pixel-eligible. Without this, fresh
     * installs have a window between install and first BO interaction where
     * FO sees missing rows = false and silently bypasses strict-consent.
     *
     * Runtime-cache keys are intentionally NOT in the list:
     *   PIXEL_AVAILABLE / CSP_STATUS / CSP_CHECKED_AT / SCRIPT_VERIFIED —
     *   written by detection probes, not merchant config.
     *
     * Self-healing on the "key exists but stored value is unusable" cases:
     * an empty-string row ('') or a literal 'null'-string row are treated
     * as missing. Both can arise from a prior Vue save where the field was
     * cleared (stored '') or from a past serialization regression where a
     * JS `null` was POSTed and ended up as the string 'null'. Re-seeding
     * those is safe — they couldn't have been an intentional admin choice.
     *
     * Genuine merchant-chosen values (including '0' for "off") have a
     * non-empty, non-'null' string representation and are preserved.
     *
     * Called from install() and upgrade/upgrade-3.1.0.php.
     *
     * @return bool true when every Configuration::updateValue we tried
     *              succeeded (and when no writes were needed because all
     *              keys already carried genuine merchant values). false
     *              when at least one write reported failure — the caller
     *              (upgrade script or install path) propagates that up so
     *              the self-heal can halt + retry on the next BO request
     *              instead of falsely bumping ps_module.version on a
     *              partial seed.
     */
    public static function seedPixelDefaultsIfMissing()
    {
        // Single source of truth lives in src/data/pixel-defaults.php so the
        // upgrade-3.1.0.php fallback path can read the same values without
        // referencing this (potentially stale-cached) class.
        $seeds = require __DIR__ . '/data/pixel-defaults.php';
        $allOk = true;
        foreach ($seeds as $key => $value) {
            if (!Configuration::hasKey($key)) {
                $allOk = Configuration::updateValue($key, $value) && $allOk;
                continue;
            }
            // `=== false` is defense-in-depth — unreachable on stable PS
            // versions (Configuration::get returns false only when the row
            // is missing, which hasKey above already covers), but cheap
            // insurance against (a) a row deletion racing between hasKey
            // and get from another process, (b) any future PS behavior
            // change. Catches the same three falsy shapes the prior
            // upgrade-3.1.0.php inline seed used.
            $current = Configuration::get($key);
            if (false === $current || '' === $current || 'null' === $current) {
                $allOk = Configuration::updateValue($key, $value) && $allOk;
            }
        }

        return $allOk;
    }

    /**
     * Get configuration keys and values.
     *
     * @return array
     */
    public static function getConfigurationValues()
    {
        try {
            $class = new ReflectionClass(static::$className);
            $values = [];
            foreach ($class->getConstants() as $constant) {
                if (is_string($constant)) {
                    if (in_array($constant, static::$multiLang, false)) {
                        static::getMultilangConfigValues($constant, $values);
                    } else {
                        $value = static::sanitizeDefault($constant, Configuration::get($constant, null, null, null, null));
                        $values[$constant] = $value;
                    }
                }
                if (in_array($constant, static::$jsonValues, false)) {
                    $jsonValue = static::sanitizeDefault($constant, Configuration::get($constant, null, null, null, null));
                    $values[$constant] = json_decode($jsonValue ?? '[]', true);
                }
            }

            return $values;
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function sanitizeDefault($key, $value)
    {
        // Treat null / empty-string / literal "null" string as "effectively missing"
        // and fall back to $defaultValues. Cross-PS behavior of Configuration::get for
        // a missing key (false vs null) plus dev-cycle artifacts (Vue posting JS null
        // which gets stored as the string "null") all converge here, so the fallback
        // path catches all of them without affecting legitimate values like "0".
        $missing = (null === $value || false === $value || '' === $value || 'null' === $value);
        if ($missing && isset(static::$defaultValues[$key])) {
            return is_scalar(static::$defaultValues[$key]) ? static::$defaultValues[$key] : json_encode(static::$defaultValues[$key]);
        }

        return $value;
    }

    /**
     * Get a multilang config key (mainly used with the HelperForm class).
     */
    protected static function getMultilangConfigValues($key, &$values)
    {
        $languages = Language::getLanguages(false, false, false);
        $values[$key] = [];
        foreach ($languages as $language) {
            $values[$key][$language['id_lang']] = Configuration::get($key, $language['id_lang']);
        }
    }

    /**
     * Decide if a config key exists in the DB or not, doesn't really care about multilang.
     *
     * @param null $configKey
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     */
    public static function configExists($configKey = null)
    {
        $query = new DbQuery();
        $query->select('count(*)');
        $query->from('configuration');
        $query->where("name = '" . pSQL((string) $configKey) . "'");

        return (int) Db::getInstance()->executeS($query) > 0;
    }

    public static function isApiKeySet()
    {
        return false;
    }

    public static function resetConfigForNewAccount()
    {
        self::saveValue(self::MAILCHIMP_AUTO_AUDIENCE_SYNC, 1);
        self::saveValue(self::MAILCHIMP_AUTO_AUDIENCE_SYNC_ACCEPTED_MESSAGE_DATE, false);
        self::saveValue(self::MAILCHIMP_AUTO_AUDIENCE_SYNC_ACCEPTED_MESSAGE_EMPLOYEE, false);

        self::saveValue(self::SELECTED_PRESET, false);
        self::saveValue(self::MAILCHIMP_STORE_SYNCED, false);

        self::saveValue(self::MAILCHIMP_SCRIPT_CACHED, false);
        self::saveValue(self::MAILCHIMP_SCRIPT_CACHED_DATE, false);

        self::saveValue(self::LAST_SYNCED_PRODUCT_ID, false);
        self::saveValue(self::LAST_SYNCED_CUSTOMER_ID, false);
        self::saveValue(self::LAST_SYNCED_PROMO_ID, false);
        self::saveValue(self::LAST_SYNCED_ORDER_ID, false);
        self::saveValue(self::LAST_SYNCED_CART_ID, false);
        self::saveValue(self::LAST_SYNCED_NEWSLETTER_SUBSCRIBER_ID, false);

        self::saveValue(self::MAILCHIMP_REPORTS_CACHED, false);
        self::saveValue(self::MAILCHIMP_REPORTS_CACHED_DATE, false);

        self::saveValue(self::LAST_CRONJOB, false);
        self::saveValue(self::LAST_CRONJOB_EXECUTION_TIME, false);

        self::saveValue(self::MULTI_INSTANCE_MODE, false);

        // Pixel keys bound to the previous Mailchimp account's connected_site —
        // each must reset so a re-key starts clean:
        //   - SCRIPT_VERIFIED: cache of the old connected_site's verify state.
        //     Without reset, the new install's scriptVerify short-circuits on
        //     the stale cache for the 30-day TTL window.
        //   - PIXEL_AVAILABLE: cache of is_pixel_enabled from the old
        //     connected_site. BO Pixel-status panel would lie about state.
        // Admin tuning preferences (autotrack, identify format, log settings,
        // consent mode, controller lists) are intentionally preserved — those
        // reflect admin choices, not per-account state.
        self::saveValue(self::MAILCHIMP_SCRIPT_VERIFIED, false);
        self::saveValue(self::MAILCHIMP_PIXEL_AVAILABLE, false);
    }
}
