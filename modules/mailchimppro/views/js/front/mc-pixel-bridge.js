/**
 * @author    Mailchimp
 * @copyright Mailchimp
 * @license   Proprietary
 */

/**
 * Mailchimp Pixel bridge — storefront-side glue between PrestaShop FO events
 * and the Mailchimp Pixel SDK (window.$mcSite.pixel.api).
 *
 * Responsibilities (this file is the FOUNDATION; the actual event wiring lands
 * in the next sub-commit):
 *   1. Wait for window.$mcSite.pixel.api to become available (the chimpstatic
 *      MC.js loader is async — our bridge can only fire calls once the SDK
 *      has booted in the browser). Time out after 8 s.
 *   2. Report the runtime truth back to the BO via the FO telemetry endpoint:
 *        - PIXEL_AVAILABLE (info) when $mcSite.pixel.api is present
 *        - SDK_TIMEOUT (warning) when 8 s elapse with no SDK
 *        - SDK_LOAD_FAILED (error) reserved for explicit chimpstatic 4xx/5xx
 *      Telemetry is sent via navigator.sendBeacon (fire-and-forget, doesn't
 *      block the page) with a fetch fallback for older browsers. Calls are
 *      no-ops when MC_PIXEL.pixelLogEnabled is false (admin kill-switch).
 *   3. Expose helpers (mcTrack, mcIdentify) to the rest of the bridge code
 *      that will be added in subsequent commits.
 *
 * Defensive invariants — the bridge MUST NOT break the storefront:
 *   - Every $mcSite touch is guarded; bridge silently no-ops if SDK is absent
 *     (ad blocker, CSP block, network failure, account not enabled, etc.)
 *   - All thrown exceptions are caught locally and downgraded to telemetry POSTs
 *   - sendBeacon failures are silent — the worst case is a missed log entry
 *
 * Compatibility: written in ES5 + a couple of widely-supported ES2015 idioms
 * (const/let are fine here because PS BO/FO targets modern browsers, but no
 * arrow-callback chaining, no async/await, no optional chaining).
 */
(function () {
    'use strict';

    // ------------------------------------------------------------------
    // Bootstrap
    // ------------------------------------------------------------------

    // The hook handler in mailchimppro.php only registers this script when
    // the pixel script is cached AND the store is synced. So in practice
    // MC_PIXEL is always defined when we get here. Defense-in-depth check
    // anyway — the bridge must never break the storefront if the server
    // forgets to emit it.
    var cfg = window.MC_PIXEL || null;
    if (!cfg) {
        return;
    }

    // Must match PixelLog::MAX_MESSAGE_LEN on the server. Server still hard-
    // caps after receiving, but doing it here too saves bandwidth and keeps
    // the human-readable summary tokens at the front of the message even on
    // very large payloads (PURCHASED with many lineItems, long product titles).
    var MAX_MESSAGE_LEN = 4000;

    // ------------------------------------------------------------------
    // SDK init-race detection via console.error pattern
    // ------------------------------------------------------------------

    /**
     * The Mailchimp Pixel SDK has a structural quirk: when api.track /
     * api.identify is called before its internal `c.initialized` flag flips,
     * the SDK throws "Pixel not initialized" inside its async method body
     * but catches the throw in its OWN internal try/catch (see logger-service
     * + error-handling-service inside chimpstatic.com's bundle). The returned
     * promise resolves with undefined instead of rejecting, so the .catch()
     * handler wired in callSdkTrack / callSdkIdentify / callSdkIdentifyPhone
     * (kept as belt-and-suspenders) never fires. The event is silently lost.
     *
     * The only observable signal is the SDK's logger calling console.error
     * with the literal "Pixel not initialized" plus a `{service: 'Pixel'}`
     * companion object. We hook console.error once here, watch for that
     * exact pattern, and trigger retry logic for the oldest pending
     * dispatch.
     *
     * FIFO matching is functionally exact, not heuristic: `c.initialized`
     * is monotonic (set once inside init(), never reset). So if any pending
     * attempt failed, it's the oldest one — any later attempt would have
     * seen `c.initialized=true` and succeeded.
     */
    var pendingSdkAttempts = [];

    (function installSdkConsoleErrorHook() {
        if (typeof console === 'undefined' || typeof console.error !== 'function') {
            return;
        }
        var originalConsoleError = console.error;
        console.error = function () {
            // Always delegate FIRST — never break other modules that may also
            // wrap console.error (Sentry, Datadog RUM, theme JS).
            originalConsoleError.apply(console, arguments);
            try {
                var hasPattern = false;
                var hasServiceTag = false;
                for (var i = 0; i < arguments.length; i++) {
                    var a = arguments[i];
                    if (typeof a === 'string' && /Pixel not initialized/i.test(a)) {
                        hasPattern = true;
                    }
                    if (a && typeof a === 'object' && a.service === 'Pixel') {
                        hasServiceTag = true;
                    }
                }
                // Both must match — narrows false-positive risk to near zero.
                if (!hasPattern || !hasServiceTag) {
                    return;
                }
                var now = Date.now();
                // Drop expired entries first (success cases that aged out).
                while (pendingSdkAttempts.length > 0 && pendingSdkAttempts[0].expiresAt < now) {
                    pendingSdkAttempts.shift();
                }
                var entry = pendingSdkAttempts.shift();
                if (entry && typeof entry.onFailed === 'function') {
                    entry.onFailed('Pixel not initialized');
                }
            } catch (e) {
                // Never let a bug in the hook break console.error for other code.
            }
        };
    })();

    // ------------------------------------------------------------------
    // sendTelemetry — POST to /module/mailchimppro/pixeltelemetry
    // ------------------------------------------------------------------

    /**
     * Fire-and-forget telemetry POST. Used by the bridge to surface runtime
     * facts (SDK availability, load failures, timeouts) into the BO Pixel-log
     * viewer. Honors the admin kill-switch — when MC_PIXEL.pixelLogEnabled is
     * false, the call is a local no-op (saves the round-trip).
     *
     * @param {string} eventType  one of PixelLog::$foAllowedEventTypes (server-side whitelist)
     * @param {number} severity   1 = info, 2 = warning, 3 = error
     * @param {string} [message]  free-form detail, capped at MAX_MESSAGE_LEN (matches server)
     */
    function sendTelemetry(eventType, severity, message) {
        if (!cfg.pixelLogEnabled) {
            return;
        }
        if (!cfg.telemetryUrl) {
            return;
        }
        var payload = {
            event_type: String(eventType),
            severity: severity || 1
        };
        if (message) {
            payload.message = String(message).slice(0, MAX_MESSAGE_LEN);
        }
        try {
            var body = JSON.stringify(payload);
            // sendBeacon is the right tool for this: queued by the browser,
            // doesn't block page lifecycle, survives navigation. Falls back
            // to fetch if not available (very old browsers).
            if (window.navigator && typeof window.navigator.sendBeacon === 'function') {
                // sendBeacon with Blob preserves Content-Type — server reads
                // application/json branch in pixeltelemetry's readPayload().
                var blob = new Blob([body], { type: 'application/json' });
                window.navigator.sendBeacon(cfg.telemetryUrl, blob);
                return;
            }
            if (typeof window.fetch === 'function') {
                window.fetch(cfg.telemetryUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: body,
                    keepalive: true,
                    credentials: 'same-origin'
                });
            }
        } catch (e) {
            // sendBeacon / fetch shouldn't throw, but if they do we don't
            // want one bad telemetry call to break the bridge for the rest
            // of the session.
        }
    }

    // ------------------------------------------------------------------
    // Required-fields validator + AJAX-fallback fetcher
    // ------------------------------------------------------------------

    /**
     * Resolve the visitor's display currency, used as a fallback when the
     * per-product payload didn't include one. Cascade:
     *   1. cfg.initialProduct.currency — server-emitted on product pages
     *      (always present when MC_PIXEL.initialProduct exists, i.e., when
     *      the visitor is currently on a product page)
     *   2. window.prestashop.currency.iso_code — PS-native page-wide currency
     *      data, exposed by assignGeneralPurposeVariables() in FrontController.
     *      Present on EVERY FO page across PS 1.7+ — covers cases where
     *      cfg.initialProduct is absent (quickview from a category page,
     *      cart page, checkout, etc.)
     *   3. '' (empty) — last resort. The required-fields validator rejects
     *      this and triggers the AJAX fallback or PIXEL_DATA_INCOMPLETE.
     */
    function resolveCurrency() {
        if (cfg.initialProduct && cfg.initialProduct.currency) {
            return String(cfg.initialProduct.currency);
        }
        if (window.prestashop && window.prestashop.currency
            && typeof window.prestashop.currency === 'object') {
            var c = window.prestashop.currency;
            if (c.iso_code) return String(c.iso_code);
            if (c.code) return String(c.code);
        }
        return '';
    }

    /**
     * Mailchimp's 3P Pixel API requires id, productId, title, price for the
     * product-shaped track payloads (PRODUCT_VIEWED, PRODUCT_ADDED_TO_CART).
     * We additionally treat currency as required — price without currency
     * is ambiguous to Mailchimp's analytics (a "9.99" can be $9.99 or €9.99).
     *
     * Accepts the product node from either shape:
     *   { product: { id, productId, title, price, currency, ... } }       (PRODUCT_VIEWED)
     *   { product: { item: { id, productId, title, ... }, quantity } }    (PRODUCT_ADDED_TO_CART)
     *
     * Returns true when all 5 required fields are present + meaningful.
     * price === 0 is accepted (genuinely free products exist); the parser
     * uses `null` to signal extraction failure so we can distinguish.
     */
    function hasRequiredPixelFields(productNode) {
        if (!productNode) return false;
        var p = productNode.item || productNode;
        return !!(
            p.id
            && p.productId
            && p.title
            && typeof p.price === 'number'
            && p.currency
        );
    }

    /**
     * Fetch the server-built pixel payload for (id_product, id_product_attribute)
     * from /module/mailchimppro/pixelproductinfo — used as a last-resort
     * fallback when:
     *   (a) the displayProductAdditionalInfo hook isn't rendered by the theme
     *   (b) DOM-parse of PS's AJAX response can't produce all 5 required fields
     * Resolves with the product payload on success; null on any failure
     * (network error, server returned ok:false, JSON parse failure). The
     * caller decides whether to log PIXEL_DATA_INCOMPLETE or skip silently.
     *
     * Fire-and-forget from the user's perspective: api.track() doesn't gate
     * any visible UI, so the ~100ms server round-trip has no UX cost.
     *
     * @param {string|number} idProduct
     * @param {string|number} idProductAttribute  0 for no-combination products
     * @param {function(object|null)} onDone
     */
    function fetchProductInfo(idProduct, idProductAttribute, onDone) {
        // Explicit opt-out: don't spend a network round-trip on data we'll
        // discard at the mcTrack stage. Same short-circuit as mcTrack itself
        // — see isExplicitlyOptedOut() docblock for why this matters.
        if (isExplicitlyOptedOut()) { onDone(null); return; }
        if (!cfg.productInfoUrl) { onDone(null); return; }
        if (typeof window.fetch !== 'function') { onDone(null); return; }
        var sep = cfg.productInfoUrl.indexOf('?') >= 0 ? '&' : '?';
        var url = cfg.productInfoUrl + sep
                + 'id_product=' + encodeURIComponent(idProduct)
                + '&id_product_attribute=' + encodeURIComponent(idProductAttribute || 0)
                + '&ajax=1';
        try {
            window.fetch(url, {
                method: 'GET',
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            }).then(function (r) {
                if (!r.ok) { onDone(null); return; }
                return r.json();
            }).then(function (data) {
                if (data && data.ok && data.product) onDone(data.product);
                else onDone(null);
            }).catch(function () { onDone(null); });
        } catch (e) {
            onDone(null);
        }
    }

    /**
     * Fetch the visitor's CURRENT cart info from /pixelcartinfo. Used as
     * fallback when MC_PIXEL.cartId (cached at pageload) is 0, AND as the
     * Layer 2 recovery path for CART_VIEWED when window.prestashop.cart
     * is missing or incomplete.
     *
     * Resolves with:
     *   - basic mode (`withItems=false`): `{ cartId, currency, productsCount }`
     *   - extended mode (`withItems=true`): also `{ lineItems: [{item,quantity}], totalPrice }`
     *   - null on any failure
     *
     * @param {boolean} withItems  pass true to request the server-built
     *                             lineItems[] + totalPrice (CART_VIEWED Layer 2)
     * @param {function(object|null)} onDone
     */
    function fetchCartInfo(opts, onDone) {
        // Back-compat for the original signatures:
        //   fetchCartInfo(cb)            → basic mode
        //   fetchCartInfo(true, cb)      → withItems=true
        //   fetchCartInfo({withItems:true, withCheckout:true}, cb)  → new form
        //   fetchCartInfo({withCustomerEmail:true}, cb)  → OPC identify path
        if (typeof opts === 'function') {
            onDone = opts;
            opts = {};
        } else if (typeof opts === 'boolean') {
            opts = { withItems: opts };
        } else if (!opts || typeof opts !== 'object') {
            opts = {};
        }
        // Explicit opt-out: don't spend a network round-trip on data we'll
        // discard at the mcTrack stage. Exception: when withCustomerEmail=1
        // (the OPC identify path), the call ALSO returns the customer's
        // hashed email which mcIdentify uses — but mcIdentify ALSO opt-out
        // guards itself, so the round-trip is still wasted. Skip it.
        if (isExplicitlyOptedOut()) { onDone(null); return; }
        if (!cfg.cartInfoUrl) { onDone(null); return; }
        if (typeof window.fetch !== 'function') { onDone(null); return; }
        var sep = cfg.cartInfoUrl.indexOf('?') >= 0 ? '&' : '?';
        var url = cfg.cartInfoUrl + sep + 'ajax=1';
        if (opts.withItems) url += '&with_items=1';
        if (opts.withCheckout) url += '&with_checkout=1';
        if (opts.withCustomerEmail) url += '&with_customer_email=1';
        try {
            // POST (not GET) — even though this is a read of per-visitor cart
            // data. Cache-everything proxy rules (misconfigured Varnish /
            // Cloudflare "Cache Everything" page rules) treat GET responses
            // as cacheable even with `Cache-Control: no-store` set. POST is
            // treated as always-fresh by every cache layer, so per-visitor
            // responses can't leak across visitors via a shared cache. Same
            // rationale customerinfo.php uses for its identify endpoint.
            window.fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            }).then(function (r) {
                if (!r.ok) { onDone(null); return; }
                return r.json();
            }).then(function (data) {
                if (data && data.ok && data.cartId) {
                    var out = {
                        cartId: data.cartId,
                        currency: data.currency || '',
                        productsCount: data.productsCount || 0
                    };
                    if (data.lineItems) out.lineItems = data.lineItems;
                    if (typeof data.totalPrice === 'number') out.totalPrice = data.totalPrice;
                    // Checkout-specific extras (only present when with_checkout=1)
                    if (typeof data.subtotalPrice === 'number') out.subtotalPrice = data.subtotalPrice;
                    if (typeof data.totalTax === 'number') out.totalTax = data.totalTax;
                    if (typeof data.totalShipping === 'number') out.totalShipping = data.totalShipping;
                    if (data.discounts) out.discounts = data.discounts;
                    if (data.customerId) out.customerId = data.customerId;
                    // Identify extra (only present when with_customer_email=1) —
                    // server-built sha256(email) or plain email per the
                    // MAILCHIMP_PIXEL_IDENTIFY_FORMAT config. Used by the
                    // OPC identify watcher to call $mcSite.pixel.api.identify
                    // when window.prestashop.customer.email isn't refreshed
                    // by the active checkout module after the personal-info
                    // AJAX step.
                    if (data.identifyValue) out.identifyValue = data.identifyValue;
                    // Phone enrichment — present only when the cart customer
                    // has a resolvable primary address with a phone. OPC
                    // checkout watcher fires phone identify alongside email.
                    if (data.phoneValue) out.phoneValue = data.phoneValue;
                    onDone(out);
                } else {
                    onDone(null);
                }
            }).catch(function () { onDone(null); });
        } catch (e) {
            onDone(null);
        }
    }

    // ------------------------------------------------------------------
    // Consent helpers — detect explicit opt-out via mc_user_optin cookie
    // ------------------------------------------------------------------

    /**
     * True ONLY when the visitor has explicitly declined consent (cookie
     * value === "false"). An unset cookie returns false here (default-opt-in
     * model — SDK's hasOptedIn behaves the same way). We use this to
     * short-circuit track/identify/verdict-probe paths so they don't:
     *   - waste 8 s polling for an SDK that won't initialize (it doesn't
     *     pass its own internal consent check when mc_user_optin=false),
     *   - emit misleading SDK_TIMEOUT / PIXEL_NOT_AVAILABLE telemetry that
     *     would make a merchant think their pixel is broken when in fact
     *     the visitor declined.
     *
     * Particularly important for the verdict probe: PIXEL_NOT_AVAILABLE
     * flips MAILCHIMP_PIXEL_AVAILABLE server-side, which means a SINGLE
     * opted-out visitor could turn the merchant's BO pixel-status panel
     * from green to red. The consent guard prevents that misattribution.
     */
    function isExplicitlyOptedOut() {
        try {
            return /(^|;\s*)mc_user_optin=false(;|$)/.test(document.cookie || '');
        } catch (e) {
            return false;
        }
    }

    // ------------------------------------------------------------------
    // whenPixelReady — poll for $mcSite.pixel.api with timeout
    // ------------------------------------------------------------------

    // Per-pageload "SDK is warm" cache. Once one event has cleared the
    // settle delay we know `c.initialized` has flipped — every subsequent
    // event in the same pageload can fire immediately.
    var sdkSettled = false;

    // Settle delay applied to the FIRST event per pageload only. The SDK's
    // surface flag `pixel.installed === true` flips one microtask BEFORE
    // its internal `c.initialized` static does. Calling api.track() in
    // that microtask window triggers the SDK's logger-service to
    // console.error "Pixel not initialized" BEFORE rejecting the promise
    // — and that console.error is impossible to intercept from our side.
    // The retry safety net would still recover the event, but the visible
    // console error would remain. A short settle delay before the FIRST
    // call lets c.initialized catch up, eliminating the console noise
    // entirely.
    //
    // Tunable via the BO Pixel tab "SDK settle delay" field, persisted as
    // MAILCHIMP_PIXEL_SDK_SETTLE_DELAY_MS. Bump higher (750–1000) on
    // environments where 500ms isn't enough (slow CPU, cold cache, heavy
    // pages). Range + default constants are owned by the PHP side and
    // injected into cfg via JsDef — bridge re-clamps here as defense in
    // depth against direct ps_configuration tampering.
    //
    // No JS-side magic-number fallbacks. If PHP injection fails (the
    // entire MailchimpProConfig::PIXEL_SDK_SETTLE_DELAY_MS_* family is
    // missing from cfg), this returns 0 — no settle delay — and the
    // retry safety net inside callSdkTrack / callSdkIdentify catches
    // any "Pixel not initialized" race that would otherwise need the
    // delay. Loud-fail better than silent-wrong-default.
    function getSdkSettleDelayMs() {
        var raw = parseInt(cfg.sdkSettleDelayMs, 10);
        var min = parseInt(cfg.sdkSettleDelayMsMin, 10);
        var max = parseInt(cfg.sdkSettleDelayMsMax, 10);
        var fallback = parseInt(cfg.sdkSettleDelayMsDefault, 10);
        if (isNaN(min) || isNaN(max) || isNaN(fallback)) {
            return 0;
        }
        if (isNaN(raw) || raw < min || raw > max) {
            return fallback;
        }
        return raw;
    }

    /**
     * Resolve when window.$mcSite.pixel.api is available, or fire timeout
     * telemetry after `timeoutMs` (default 8000). Polls every 200 ms.
     *
     * @param {function} onReady    called with the api object once available
     * @param {function} [onTimeout] optional — called when timeout is hit
     * @param {number} [timeoutMs]  default 8000
     */
    function whenPixelReady(onReady, onTimeout, timeoutMs) {
        var start = Date.now();
        var deadline = start + (timeoutMs || 8000);

        function tick() {
            // Readiness requires BOTH the api shape AND `pixel.installed === true`.
            //
            // Why both: `pixel.api` is exposed during early script load (object
            // shape only). The SDK's internal `c.initialized` static flag
            // doesn't flip until init() has resolved its async services
            // (logger/storage/window). Calling api.track() before that flip
            // throws "Pixel not initialized" inside the SDK and the event is
            // silently discarded — the SDK swallows the throw in its own
            // async catch, so our caller sees the Promise resolve normally.
            //
            // `pixel.installed === true` is the public-surface flag mirroring
            // that internal state — but in some builds it flips one microtask
            // BEFORE `c.initialized` does, so a track() call right at the
            // edge can still throw. The `sdkSettled` first-event-only settle
            // delay covers the typical case; the retry safety net inside the
            // low-level callSdkTrack / callSdkIdentify / callSdkIdentifyPhone
            // wrappers closes any residual window (all three retry on
            // "Pixel not initialized" rejections).
            //
            // Once ready, defer through `$mcSite.runIfOptedIn(fn)` — that's
            // the SDK's consent gate, which reads the `mc_user_optin` cookie
            // via hasOptedIn() and only invokes the callback if the visitor
            // hasn't explicitly opted out (default: opted in when cookie
            // unset). Fallback to firing immediately on the rare SDK build
            // that doesn't expose runIfOptedIn.
            if (window.$mcSite
                && window.$mcSite.pixel
                && window.$mcSite.pixel.installed === true
                && window.$mcSite.pixel.api) {
                var dispatch = function () {
                    sdkSettled = true;
                    if (typeof window.$mcSite.runIfOptedIn === 'function') {
                        window.$mcSite.runIfOptedIn(function () {
                            onReady(window.$mcSite.pixel.api);
                        });
                    } else {
                        onReady(window.$mcSite.pixel.api);
                    }
                };
                if (sdkSettled) {
                    dispatch();
                } else {
                    setTimeout(dispatch, getSdkSettleDelayMs());
                }
                return;
            }
            if (Date.now() >= deadline) {
                if (typeof onTimeout === 'function') {
                    onTimeout();
                }
                return;
            }
            setTimeout(tick, 200);
        }

        tick();
    }

    // ------------------------------------------------------------------
    // identifyOrReset — runs every page load, binds or clears identity
    // ------------------------------------------------------------------

    /**
     * Low-level: actually call api.identify({type, value}) or the SDK's
     * clear method. Wraps the value per Mailchimp's documented identify()
     * shape — see
     * https://mailchimp.com/help/mailchimp-site-tracking-pixel-integration-guidance/#identify
     * Bare-string form (which the SDK accepts client-side without throwing)
     * causes the SDK's batched POST to /v1/track to return 400 "Invalid
     * request data" so neither the identify nor the co-batched track
     * events land — silently breaks all subsequent tracking until next
     * SDK session.
     *
     * The SDK's "clear" convention varies by version; we try `api.reset()`
     * first (most common), then `api.identify(null)` as a fallback. Failures
     * are silent — if the SDK has neither, prior identification persists
     * until the SDK's own cookie expires (acceptable bounded leak).
     *
     * @param {object} api  the resolved $mcSite.pixel.api
     * @param {string|null|undefined} value  identify value, or falsy → reset
     */
    function callSdkIdentify(api, value, attemptNum) {
        if (typeof attemptNum !== 'number') attemptNum = 0;

        // Null/reset path — best-effort, no retry, no telemetry on failure.
        // The SDK has no documented reset API; we try api.reset() if exposed
        // (older builds). Passing a malformed argument to api.identify
        // would poison the batched-event context, so we DON'T fall back
        // to that.
        if (!value) {
            try {
                if (typeof api.reset === 'function') api.reset();
            } catch (e) { /* swallow — reset is best-effort */ }
            return;
        }

        // Opt-out re-check on every attempt — a visitor can flip consent
        // mid-retry (decline-button click on the merchant's CMP). The
        // mcIdentify wrapper's initial guard only covers attempt 0; this
        // covers retries landing through setTimeout.
        if (isExplicitlyOptedOut()) {
            return;
        }

        // Idempotency flag — see callSdkTrack for the rationale. The
        // console.error hook (top of IIFE) and the .catch() on the returned
        // Promise both feed into scheduleRetryOrFail; first signal wins.
        var attemptResolved = false;
        var scheduleRetryOrFail = function (errMsg) {
            if (attemptResolved) {
                return;
            }
            attemptResolved = true;
            if (/not initialized/i.test(errMsg) && attemptNum < TRACK_RETRY_BACKOFF_MS.length) {
                setTimeout(function () {
                    callSdkIdentify(api, value, attemptNum + 1);
                }, TRACK_RETRY_BACKOFF_MS[attemptNum]);
                return;
            }
            sendTelemetry('SDK_LOAD_FAILED', 3,
                'identify() failed after ' + (attemptNum + 1) + ' attempt(s): ' + errMsg);
        };

        // Register in the FIFO queue for the console.error hook to match.
        pendingSdkAttempts.push({
            expiresAt: Date.now() + 500,
            onFailed: scheduleRetryOrFail
        });

        try {
            if (typeof api.identify !== 'function') return;
            // Mailchimp's documented identify() signature — {type, value}.
            // Bare-string would cause /v1/track batched POST to return 400.
            var p = api.identify({
                type: cfg.identifyFormat || 'EMAIL_SHA256',
                value: value
            });
            if (p && typeof p['catch'] === 'function') {
                p['catch'](function (e) {
                    scheduleRetryOrFail(e && e.message ? String(e.message) : 'unknown');
                });
            }
        } catch (e) {
            scheduleRetryOrFail(e && e.message ? String(e.message) : 'unknown');
        }
    }

    /**
     * Phone-identity counterpart of callSdkIdentify. Fires a separate
     * api.identify({type:'PHONE_SHA256'|'PHONE', value}) so Mailchimp gets
     * BOTH identifiers on the same anonymousId and can match incoming
     * pixel events against subscribers by either field.
     *
     * Type mapping mirrors the email path:
     *   cfg.identifyFormat === 'EMAIL_SHA256' → 'PHONE_SHA256' (default, privacy-first)
     *   cfg.identifyFormat === 'EMAIL'        → 'PHONE'        (plain phone)
     *
     * Identifier type strings are UPPERCASE per Mailchimp's documented
     * api.identify() spec — see the Mailchimp Help "Identify" reference.
     *
     * Phone identify is independent of email reconciliation — we don't
     * track its own "last seen" state in localStorage. The SDK dedupes
     * repeated identify() calls with the same value server-side, so
     * re-firing on every logged-in pageload is safe + cheap.
     *
     * @param {object} api  the resolved $mcSite.pixel.api
     * @param {string|null|undefined} phoneValue  raw or hashed phone value, or falsy → no-op
     */
    function callSdkIdentifyPhone(api, phoneValue, attemptNum) {
        if (!phoneValue) return;
        if (typeof attemptNum !== 'number') attemptNum = 0;

        if (isExplicitlyOptedOut()) {
            return;
        }

        var attemptResolved = false;
        var scheduleRetryOrFail = function (errMsg) {
            if (attemptResolved) {
                return;
            }
            attemptResolved = true;
            if (/not initialized/i.test(errMsg) && attemptNum < TRACK_RETRY_BACKOFF_MS.length) {
                setTimeout(function () {
                    callSdkIdentifyPhone(api, phoneValue, attemptNum + 1);
                }, TRACK_RETRY_BACKOFF_MS[attemptNum]);
                return;
            }
            sendTelemetry('SDK_LOAD_FAILED', 3,
                'phone identify() failed after ' + (attemptNum + 1) + ' attempt(s): ' + errMsg);
        };

        // Register in the FIFO queue for the console.error hook to match.
        pendingSdkAttempts.push({
            expiresAt: Date.now() + 500,
            onFailed: scheduleRetryOrFail
        });

        try {
            if (typeof api.identify !== 'function') return;
            var emailType = cfg.identifyFormat || 'EMAIL_SHA256';
            var phoneType = (emailType === 'EMAIL_SHA256') ? 'PHONE_SHA256' : 'PHONE';
            var p = api.identify({ type: phoneType, value: phoneValue });
            if (p && typeof p['catch'] === 'function') {
                p['catch'](function (e) {
                    scheduleRetryOrFail(e && e.message ? String(e.message) : 'unknown');
                });
            }
        } catch (e) {
            scheduleRetryOrFail(e && e.message ? String(e.message) : 'unknown');
        }
    }

    // ------------------------------------------------------------------
    // Identity reconciliation — fire identify() on every logged-in page
    // ------------------------------------------------------------------

    // Strategy: re-assert identity on EVERY page load when logged in. The
    // SDK is documented as idempotent on repeated identify() calls (same
    // value → no-op server-side), and re-asserting protects against the
    // SDK's identity cookie being lost between pages (cleared, expired,
    // third-party-blocked). Matches GA4/Klaviyo/Segment conventions where
    // identify() is a per-pageload contract, not a one-shot transition.
    //
    //   - Logged-in page load   → identify({type, value})  (every time)
    //   - Login transition      → identify + log a "(login)" telemetry
    //   - A→B switch transition → identify + log a "(switch)" telemetry
    //   - Logout transition     → reset() once + log "(logout)" telemetry
    //   - Logged-in steady      → identify (silent, no log row)
    //   - Anonymous steady      → no-op
    //
    // localStorage tracks the last identified value so we know when a
    // transition happened (used only for log-line decoration + logout
    // detection — not as a "skip identify" optimization anymore).
    //
    // Note on storage privacy: the value we store is whatever was passed
    // to identify(), which for EMAIL_SHA256 mode is already a hash. For
    // EMAIL mode, it's the plain email — but the merchant chose that
    // format knowing the privacy trade-off (it's also rendered inline in
    // HTML).

    var STORAGE_KEY_LAST_IDENTIFY = 'mc_pixel_last_identify';

    function readLastIdentify() {
        try { return window.localStorage.getItem(STORAGE_KEY_LAST_IDENTIFY); }
        catch (e) { return null; }
    }
    function writeLastIdentify(v) {
        try {
            if (v) {
                window.localStorage.setItem(STORAGE_KEY_LAST_IDENTIFY, v);
            } else {
                window.localStorage.removeItem(STORAGE_KEY_LAST_IDENTIFY);
            }
        } catch (e) { /* localStorage disabled — non-critical */ }
    }

    // Phone-transition bookkeeping. The email key alone can't detect
    // "email unchanged but phone just became available" — typical guest
    // checkout where email lands at step 1 (personal info) and phone at
    // step 2 (address). Without this, the phone identify call would fire
    // with no BO log entry, leaving admins blind to when enrichment landed.
    var STORAGE_KEY_LAST_PHONE_IDENTIFY = 'mc_pixel_last_phone_identify';
    function readLastPhoneIdentify() {
        try { return window.localStorage.getItem(STORAGE_KEY_LAST_PHONE_IDENTIFY); }
        catch (e) { return null; }
    }
    function writeLastPhoneIdentify(v) {
        try {
            if (v) {
                window.localStorage.setItem(STORAGE_KEY_LAST_PHONE_IDENTIFY, v);
            } else {
                window.localStorage.removeItem(STORAGE_KEY_LAST_PHONE_IDENTIFY);
            }
        } catch (e) { /* localStorage disabled — non-critical */ }
    }

    /**
     * Reconcile current identify value (from server) with what the SDK was
     * last told. Fires identify() on every logged-in page load (SDK-side
     * dedupe handles steady state); logs telemetry only on actual login /
     * switch / logout transitions to keep the BO log readable.
     *
     * Phone enrichment: when `currentPhoneValue` is provided AND email
     * identify fires, also fire a parallel phone identify so Mailchimp's
     * subscriber matching has both signals. No independent "phone changed"
     * watch — if phone changes mid-session the SDK dedupes silently.
     *
     * @param {object} api
     * @param {string|null|undefined} currentValue
     * @param {string|null|undefined} currentPhoneValue  optional
     */
    function reconcileIdentity(api, currentValue, currentPhoneValue) {
        var current = currentValue || '';
        var currentPhone = currentPhoneValue || '';
        var last = readLastIdentify() || '';
        var lastPhone = readLastPhoneIdentify() || '';
        var isEmailTransition = (current !== last);
        var isPhoneTransition = (currentPhone !== lastPhone);

        if (current) {
            // Logged-in page — re-assert identity every time. SDK dedupes
            // server-side; gain is robustness against lost SDK identity
            // state (cookie cleared / new tab / third-party block).
            callSdkIdentify(api, current);
            // Phone identify is independent — fires in addition to email
            // when a phone is available on the customer's primary address
            // (CustomerAddressSelector picked it server-side). The SDK
            // accepts multiple identify() calls per visitor and merges the
            // identifiers against the same anonymousId.
            if (currentPhone) {
                callSdkIdentifyPhone(api, currentPhone);
            }
            writeLastIdentify(current);
            writeLastPhoneIdentify(currentPhone);

            // Telemetry routing per transition kind — covers all 4 cases
            // the guest checkout funnel hits and keeps log volume bounded:
            //
            //   email-transition (login/switch)  → ONE log row, email
            //       prefix + phone prefix if phone was also captured
            //       on the same pageload (e.g., logged-in customer with
            //       an address on file).
            //
            //   phone-only transition            → ONE log row, phone
            //       prefix only. Hit when email already matched the
            //       previous pageload's email (no email transition) but
            //       phone just became available (e.g., guest mid-
            //       checkout transitioning from step 1 personal-info
            //       to step 2 address-step — email captured at step 1,
            //       phone captured at step 2).
            //
            //   phone-only logout                → ONE log row, phone
            //       cleared (rare; phone disappearing from the
            //       customer's primary address while email stays valid).
            //
            //   no transition                    → silent. SDK still
            //       dedupes the api.identify calls server-side.
            if (isEmailTransition) {
                var kind = last ? 'switch' : 'login';
                var prefix = current.substring(0, 8);
                var msg = 'identify() fired (' + kind + ') — value prefix: ' + prefix + '…';
                if (currentPhone) {
                    msg += ', phone prefix: ' + currentPhone.substring(0, 8) + '…';
                }
                sendTelemetry('IDENTIFY', 1, msg);
            } else if (isPhoneTransition) {
                if (currentPhone) {
                    sendTelemetry('IDENTIFY', 1,
                        'phone identify() fired — phone prefix: ' + currentPhone.substring(0, 8) + '…');
                } else if (lastPhone) {
                    sendTelemetry('IDENTIFY', 1,
                        'phone identify cleared — local bookkeeping reset (SDK cannot disassociate)');
                }
            }
        } else if (last) {
            // Logout transition. Clear our bookkeeping (mc_pixel_last_identify)
            // so the next login transition correctly fires identify().
            //
            // Note on server-side attribution: the Mailchimp Pixel SDK
            // exposes no documented reset/disassociate API — only init/
            // track/identify (verified via SDK source inspection of
            // pixel-reporting-sdk/1.22.0). The SDK keeps anonymousId in
            // memory after init and re-persists to localStorage on every
            // storage interaction, so clearing intuit_pixel_sdk_user_data
            // here doesn't work — the SDK rewrites it within milliseconds
            // during the post-logout PAGE_VIEWED flush. Consequence: the
            // server-side `anonymousId → customer EMAIL_SHA256` binding
            // persists, so post-logout track events from the same browser
            // continue to attribute to the prior customer in Mailchimp's
            // reports until either a new identify() rebinds the visitor
            // ID, Mailchimp's server-side association ages out, or the
            // visitor clears their browser data.
            callSdkIdentify(api, null); // attempts api.reset() if exposed (currently not)
            writeLastIdentify(null);
            // Also clear the phone bookkeeping — without this, a future
            // login of a DIFFERENT customer would not log a phone-
            // transition entry on first pageload if their phone happens
            // to match the now-stale lastPhone value (false negative).
            writeLastPhoneIdentify(null);
            sendTelemetry('IDENTIFY', 1,
                'logout transition — local bookkeeping cleared; SDK visitor ID persists (no reset API)');
        }
        // else: anonymous + was anonymous → nothing to do.
    }

    /**
     * Top-level identity reconcile. Two paths depending on
     * `cfg.identifyAjaxMode`:
     *
     *   (A) Default — INLINE: `cfg.identifyValue` is the truth. Pure local
     *       compare against last stored — zero extra HTTP, zero added
     *       latency unless a transition actually fires.
     *
     *   (B) Cache-safe — AJAX: POST to `cfg.customerInfoUrl` to learn the
     *       current value (server-side authoritative; cached HTML can't
     *       leak a value across visitors). Used only when the merchant
     *       explicitly opted into cache-safe mode in BO Pixel tab.
     *
     * @param {object} api  the resolved $mcSite.pixel.api
     */
    function identifyOrReset(api) {
        if (!cfg.identifyAjaxMode) {
            reconcileIdentity(api, cfg.identifyValue, cfg.phoneIdentifyValue);
            return;
        }

        // AJAX path. POST verb chosen so cache-everything CDNs treat the
        // response as always-fresh (HTTP semantics: POST changes state).
        // credentials:'same-origin' carries the PS session cookie so the
        // server identifies the visitor authoritatively per-request.
        if (!cfg.customerInfoUrl) {
            return;
        }
        try {
            window.fetch(cfg.customerInfoUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: '{}',
                cache: 'no-store'
            }).then(function (res) {
                return res.ok ? res.json() : null;
            }).then(function (data) {
                reconcileIdentity(
                    api,
                    data && data.value ? data.value : null,
                    data && data.phoneValue ? data.phoneValue : null
                );
            })['catch'](function () {
                // Network failure / 5xx — don't break the bridge. SDK keeps
                // any prior identification until its own cookie expires.
                // Next page load retries.
            });
        } catch (e) {
            // fetch unavailable — silently skip in cache-safe mode.
        }
    }

    // ------------------------------------------------------------------
    // mcTrack / mcIdentify — guarded wrappers (events come in next commit)
    // ------------------------------------------------------------------

    /**
     * Compose a context-rich telemetry message line for track() calls.
     * Includes a leading status verb plus pipe-separated context tokens
     * summarizing the payload sent to api.track() — so support engineers
     * can answer "what did we send to Mailchimp?" by reading the log,
     * without needing to dump full payloads (which would risk PII).
     *
     * Per-event-type summarizers:
     *   PRODUCT_VIEWED          | source | product=pid/variant=vid | price | sku | "title"
     *   PRODUCT_ADDED_TO_CART   | source | product=… | qty | price | "title"
     *   CART_VIEWED             | source | items=N | total
     *   CHECKOUT_STARTED        | source | items=N | total | customer=<hashed-prefix>
     *   PURCHASED               | source | order=oid | items=N | total | customer=<hashed-prefix>
     *
     * Privacy guardrails:
     *   - Customer email is NEVER included (only the SHA-256 hashed prefix
     *     used for Mailchimp's identify() — 8 chars is non-reversible).
     *   - Cart line-item details are NOT enumerated; only the count.
     *   - Title strings are truncated to 30 chars.
     *
     * @param {string} prefix  status verb, e.g. "api.track() fired"
     * @param {string} [source] tag from the caller (see fireOnce)
     * @param {object} [props] event payload (per Mailchimp 3P SDK schema)
     * @return {string} composed message, safe within MAX_MESSAGE_LEN (4000)
     */
    function formatTrackTelemetry(prefix, source, via, props) {
        var parts = [prefix];
        if (source) parts.push('source=' + source);
        if (via) parts.push('via=' + via);
        if (!props) return parts.join(' | ');

        // PRODUCT_VIEWED, PRODUCT_ADDED_TO_CART (single-product events)
        if (props.product) {
            var p = props.product.item || props.product;  // PRODUCT_ADDED_TO_CART nests as product.item
            var pidPart = 'product=' + (p.productId || '?');
            if (p.id && String(p.id) !== String(p.productId)) {
                pidPart += '/variant=' + p.id;
            }
            parts.push(pidPart);
            if (typeof p.price === 'number' && p.price > 0) {
                parts.push('price=' + p.price + (p.currency ? ' ' + p.currency : ''));
            }
            // PRODUCT_ADDED_TO_CART nests quantity, line total + currency inside
            // the product object (different from PRODUCT_VIEWED's flat shape).
            if (props.product.quantity) parts.push('qty=' + props.product.quantity);
            // Show line total (price × qty) only when it differs from unit
            // price (i.e., qty > 1). Saves a redundant chip on qty=1 events.
            if (typeof props.product.price === 'number' && props.product.price > 0
                && props.product.price !== p.price) {
                parts.push('total=' + props.product.price
                    + (props.product.currency ? ' ' + props.product.currency : ''));
            }
            if (p.sku) parts.push('sku=' + p.sku);
            if (p.title) parts.push('"' + String(p.title).slice(0, 30) + '"');
        }
        // CART_VIEWED, CHECKOUT_STARTED, PURCHASED (multi-item events)
        else if (props.cart || props.checkout || props.order) {
            var c = props.cart || props.checkout || props.order;
            if (c.id) parts.push((props.order ? 'order=' : 'cart=') + c.id);
            if (c.lineItems && c.lineItems.length) parts.push('items=' + c.lineItems.length);
            if (typeof c.totalPrice === 'number') {
                parts.push('total=' + c.totalPrice + (c.currency ? ' ' + c.currency : ''));
            }
            if (c.customerId) {
                // Only the first 8 chars of the (hashed) customer id —
                // enough to correlate with identify() entries but not
                // enough to reverse-engineer.
                parts.push('customer=' + String(c.customerId).slice(0, 8));
            }
        }

        // Full payload audit at the tail. Mailchimp's api.track() is fire-
        // and-forget (no callback, no return value), so the payload we
        // generated client-side is the ONLY record of "what we sent to
        // Mailchimp." Without this, a merchant comparing our log to
        // Mailchimp's reports has no way to verify the data was correct
        // at our side. The actual api.track() call always uses the FULL
        // untruncated payload — only the log copy is bounded.
        //
        // The naive approach (slice the final JSON string at the byte cap)
        // leaves the BO viewer with unparseable JSON at the tail, which
        // breaks "pretty-print payload" and forces support to guess at
        // what was sent. We instead progressively shrink individual fields
        // (longest first: imageUrl, productUrl, title, vendor, categories)
        // until the payload JSON fits the remaining budget — so the stored
        // JSON is ALWAYS valid and parseable.
        var prefixMessage = parts.join(' | ');
        var separator = ' | payload=';
        // Reserve a small safety margin (16) for separator + any UTF-8 size
        // mismatch between string length and byte length the server cares about.
        var budget = MAX_MESSAGE_LEN - prefixMessage.length - separator.length - 16;
        if (budget > 0) {
            var jsonStr = buildBoundedPayloadJson(props, budget);
            if (jsonStr !== null) {
                return prefixMessage + separator + jsonStr;
            }
        }
        return prefixMessage;
    }

    /**
     * Stringify `payload` to JSON guaranteed valid and ≤ `budget` chars.
     * Strategy: try full JSON; if it overflows, deep-clone and progressively
     * shrink fields in priority order. The final result is always parseable
     * (or `null` if even the bare-minimum form overflows the budget — which
     * shouldn't happen at our MAX_MESSAGE_LEN, but we degrade gracefully).
     *
     * Truncation order (longest fields first; suffix `…` marks shortening):
     *   1. imageUrl     → 120 chars  (CDN URLs can be very long)
     *   2. productUrl   → 150 chars
     *   3. categories[] → 5 entries
     *   4. title        → 60 chars
     *   5. vendor       → 40 chars
     *   6. imageUrl     → dropped entirely
     *   7. productUrl   → dropped entirely
     *   8. categories[] → dropped entirely
     *
     * For cart/order shapes with many lineItems, lineItems gets truncated
     * separately (lineItems[].slice(0, 10) then [].slice(0, 5) then []).
     *
     * @param {object} payload
     * @param {number} budget   max chars for the resulting JSON string
     * @return {string|null}
     */
    function buildBoundedPayloadJson(payload, budget) {
        var full;
        try {
            full = JSON.stringify(payload);
        } catch (e) {
            return null;
        }
        if (full.length <= budget) {
            return full;
        }

        // Need to shrink. Work on a deep clone so the actual api.track() call
        // keeps the untruncated payload.
        var clone;
        try {
            clone = JSON.parse(full);
        } catch (e) {
            return null; // JSON.stringify succeeded but JSON.parse failed — give up
        }

        // Locate the truncation target (product item for single-product events,
        // cart/checkout/order container for multi-item events).
        var productTarget = null;
        if (clone.product) {
            // PRODUCT_ADDED_TO_CART wraps as { product: { item: {...}, quantity } };
            // PRODUCT_VIEWED is { product: {...} } directly.
            productTarget = clone.product.item || clone.product;
        }
        var containerTarget = clone.cart || clone.checkout || clone.order || null;

        function trimStringField(obj, field, maxLen) {
            if (!obj || typeof obj[field] !== 'string') return;
            if (obj[field].length > maxLen) {
                obj[field] = obj[field].slice(0, maxLen - 1) + '…';
            }
        }
        function trimArrayField(obj, field, maxLen) {
            if (!obj || !obj[field] || typeof obj[field].length !== 'number') return;
            if (obj[field].length > maxLen) {
                obj[field] = obj[field].slice(0, maxLen);
            }
        }
        function dropField(obj, field) {
            if (obj && field in obj) {
                delete obj[field];
            }
        }
        function tryFit() {
            try {
                var s = JSON.stringify(clone);
                return s.length <= budget ? s : null;
            } catch (e) {
                return null;
            }
        }

        var steps = [
            function () { trimStringField(productTarget, 'imageUrl', 120); },
            function () { trimStringField(productTarget, 'productUrl', 150); },
            function () { trimArrayField(productTarget, 'categories', 5); },
            function () { trimArrayField(containerTarget, 'lineItems', 10); },
            function () { trimStringField(productTarget, 'title', 60); },
            function () { trimStringField(productTarget, 'vendor', 40); },
            function () { trimArrayField(containerTarget, 'lineItems', 5); },
            function () { dropField(productTarget, 'imageUrl'); },
            function () { dropField(productTarget, 'productUrl'); },
            function () { dropField(productTarget, 'categories'); },
            function () { dropField(productTarget, 'vendor'); },
            function () { trimArrayField(containerTarget, 'lineItems', 0); }
        ];
        for (var i = 0; i < steps.length; i++) {
            steps[i]();
            var fitted = tryFit();
            if (fitted !== null) return fitted;
        }

        // Last-resort minimal form: just the IDs.
        var minimal = {};
        if (productTarget) {
            if (productTarget.id) minimal.product = { id: productTarget.id };
            if (productTarget.productId) {
                minimal.product = minimal.product || {};
                minimal.product.productId = productTarget.productId;
            }
        } else if (containerTarget) {
            var key = clone.cart ? 'cart' : (clone.checkout ? 'checkout' : 'order');
            minimal[key] = {};
            if (containerTarget.id) minimal[key].id = containerTarget.id;
        }
        try {
            var minimalJson = JSON.stringify(minimal);
            return minimalJson.length <= budget ? minimalJson : null;
        } catch (e) {
            return null;
        }
    }

    /**
     * Call $mcSite.pixel.api.track(name, props) when the SDK is ready.
     * Silently swallows failures; reports SDK_LOAD_FAILED telemetry once
     * if the SDK refused the call.
     *
     * Success-trail telemetry per log level (three modes — server-side
     * PixelLog::insert filter is authoritative either way; this JS-side
     * gating skips the HTTP send when PHP would just drop the row):
     *
     *   - 'verbose'     : emit full payload (event name + source + via +
     *                     props) so support can see exactly what fired.
     *   - 'default'     : emit a compact one-liner ('Track fired') so
     *                     merchants get tracking-confirmation visibility
     *                     without payload noise in the BO log.
     *   - 'errors_only' : skip the send entirely. PHP would drop the row
     *                     anyway (track events aren't in the always-log
     *                     allowlist) — saving the FO HTTP round-trip.
     *
     * `source` is a short tag (e.g. "initial", "combination", "quickview",
     * "hook") identifying which caller triggered the track. Surfaced in the
     * telemetry message so merchants + support can tell at a glance whether
     * an event came from an initial page load vs a combination switch.
     *
     * @param {string} eventName  e.g. PRODUCT_VIEWED, PRODUCT_ADDED_TO_CART
     * @param {object} [props]    event payload — see Mailchimp 3P guide
     * @param {string} [source]   short trigger tag — what the customer did
     *                            (initial / combination / quickview)
     * @param {string} [via]      technical code path that fired this — useful
     *                            for support to know whether the event came
     *                            from the actionFrontControllerSetMedia JsDef
     *                            (`jsdef`), the displayProductAdditionalInfo
     *                            hook (`hook`), or a prestashop.on(...) JS
     *                            listener (`js-update`, `js-quickview`).
     */
    // Retry budget for the "Pixel not initialized" race window. The
    // whenPixelReady gate (pixel.installed === true) catches the bulk of
    // it, but the SDK's internal c.initialized flag can lag by one
    // microtask in some builds. Each entry is the delay BEFORE attempt
    // n+1; total wall-clock budget ~3.75s.
    var TRACK_RETRY_BACKOFF_MS = [250, 500, 1000, 2000];

    function callSdkTrack(api, eventName, props, source, via, attemptNum) {
        if (typeof attemptNum !== 'number') attemptNum = 0;

        // Opt-out re-check on every attempt. A visitor can flip consent
        // mid-retry (decline-button click on the merchant's CMP) —
        // without this guard, scheduled retries would fire api.track()
        // after the cookie already says `false`. mcTrack's initial guard
        // only covers attempt 0; this covers retries via setTimeout.
        if (isExplicitlyOptedOut()) {
            return;
        }

        // Idempotency flag — the console.error hook (top of IIFE) and the
        // .catch() on the returned Promise can both fire scheduleRetryOrFail
        // for the same attempt. Whichever signal arrives first wins; later
        // signals are silently dropped to prevent double retry + double
        // telemetry. Also gates the deferred success telemetry below: if
        // we resolved as "failed" we must not log "Track fired".
        var attemptResolved = false;
        var scheduleRetryOrFail = function (errMsg) {
            if (attemptResolved) {
                return;
            }
            attemptResolved = true;
            if (/not initialized/i.test(errMsg) && attemptNum < TRACK_RETRY_BACKOFF_MS.length) {
                setTimeout(function () {
                    callSdkTrack(api, eventName, props, source, via, attemptNum + 1);
                }, TRACK_RETRY_BACKOFF_MS[attemptNum]);
                return;
            }
            sendTelemetry('SDK_LOAD_FAILED', 3,
                formatTrackTelemetry('track(' + eventName + ') failed after '
                    + (attemptNum + 1) + ' attempt(s): ' + errMsg, source, via, props));
        };

        // Register this dispatch in the FIFO queue so the console.error hook
        // can match a subsequent "Pixel not initialized" log line to it.
        // 500 ms expiry covers the SDK's await chain latency from track()
        // entry to logger.error — successful dispatches age out harmlessly.
        pendingSdkAttempts.push({
            expiresAt: Date.now() + 500,
            onFailed: scheduleRetryOrFail
        });

        try {
            var p = api.track(eventName, props || {});
            if (p && typeof p['catch'] === 'function') {
                p['catch'](function (e) {
                    scheduleRetryOrFail(e && e.message ? String(e.message) : 'unknown');
                });
            }
            // Track-event success telemetry — three modes:
            //   - 'verbose'     : full payload (source + via + props)
            //   - 'default'     : compact one-liner ('Track fired')
            //   - 'errors_only' : no telemetry sent (avoid FO HTTP overhead)
            // PHP-side PixelLog::insert filter is authoritative either way;
            // we skip the network call at errors_only since PHP drops the
            // row anyway (track events aren't in $alwaysLogEventTypes).
            //
            // Deferred to ~500 ms after the api.track() return so the
            // attemptResolved gate has time to flip if the SDK silently
            // catches "Pixel not initialized" (we only log success when
            // the call actually went through).
            if (cfg.pixelLogLevel === 'verbose' || cfg.pixelLogLevel === 'default') {
                setTimeout(function () {
                    if (attemptResolved) {
                        return;
                    }
                    if (cfg.pixelLogLevel === 'verbose') {
                        var msg = attemptNum > 0
                            ? 'api.track() fired (retry ' + attemptNum + ')'
                            : 'api.track() fired';
                        sendTelemetry(eventName, 1,
                            formatTrackTelemetry(msg, source, via, props));
                    } else {
                        sendTelemetry(eventName, 1,
                            attemptNum > 0 ? 'Track fired (retry ' + attemptNum + ')' : 'Track fired');
                    }
                }, 500);
            }
        } catch (e) {
            scheduleRetryOrFail(e && e.message ? String(e.message) : 'unknown');
        }
    }

    function mcTrack(eventName, props, source, via) {
        // Explicit opt-out short-circuit: skip polling AND telemetry. The
        // SDK won't init pixel.api when mc_user_optin=false, so whenPixelReady
        // would just time out and fire misleading "SDK never appeared".
        if (isExplicitlyOptedOut()) {
            return;
        }
        whenPixelReady(function (api) {
            callSdkTrack(api, eventName, props, source, via);
        }, function () {
            sendTelemetry('SDK_TIMEOUT', 2,
                formatTrackTelemetry('track(' + eventName + ') aborted — SDK never appeared', source, via, props));
        });
    }

    function mcIdentify(emailOrHash) {
        // Explicit opt-out short-circuit — see mcTrack note above.
        if (isExplicitlyOptedOut()) {
            return;
        }
        whenPixelReady(function (api) {
            callSdkIdentify(api, emailOrHash);
        }, function () {
            sendTelemetry('SDK_TIMEOUT', 2, 'identify() aborted — SDK never appeared');
        });
    }

    /**
     * Phone-identify counterpart of mcIdentify. Same SDK-ready gate, same
     * opt-out short-circuit; the type sent to api.identify({type, value})
     * follows the email format toggle (EMAIL_SHA256 → 'PHONE_SHA256';
     * EMAIL → 'PHONE'). Exposed on window.MC_PIXEL_BRIDGE so the OPC
     * checkout watcher (mc-pixel-bridge-checkout.js) can fire phone
     * identify alongside email when pixelcartinfo returns phoneValue.
     *
     * Internally delegates to callSdkIdentifyPhone — same telemetry +
     * async-rejection wiring as callSdkIdentify but with the phone
     * type mapping.
     *
     * @param {string} phoneOrHash
     */
    function mcIdentifyPhone(phoneOrHash) {
        if (isExplicitlyOptedOut()) {
            return;
        }
        whenPixelReady(function (api) {
            callSdkIdentifyPhone(api, phoneOrHash);
        }, function () {
            sendTelemetry('SDK_TIMEOUT', 2, 'phone identify() aborted — SDK never appeared');
        });
    }

    // ------------------------------------------------------------------
    // Initial verdict probe — throttled (5 min per browser via localStorage)
    // ------------------------------------------------------------------

    // The verdict probe is a self-diagnostic that updates MAILCHIMP_PIXEL_AVAILABLE
    // on the server (which the BO Pixel tab reads). It does NOT need to fire on
    // every page load — once we know the state, we just need it to refresh
    // occasionally to catch a change (Mailchimp enables/disables pixel,
    // chimpstatic outage, etc.).
    //
    // Throttle: 5 minutes per browser via localStorage. A single shopper
    // navigating multiple pages fires at most one verdict POST. An admin
    // testing in their own browser sees fresh data within 5 min after reload.
    //
    // Defense-in-depth: the FO endpoint also dedupes verdict events server-side
    // (same event_type + IP within 5 min → no log insert). So even if a browser
    // has localStorage disabled and we fail open here, the server still bounds
    // the write rate.
    //
    // Three possible verdicts on timeout, each diagnostically distinct:
    //   - PIXEL_AVAILABLE      : SDK ready, pixel.api exposed → AUTHORITATIVE YES
    //   - PIXEL_NOT_AVAILABLE  : $mcSite loaded but pixel.api absent → AUTHORITATIVE NO
    //                            (almost always means the account isn't pixel-enabled)
    //   - SDK_LOAD_FAILED      : $mcSite never appeared (chimpstatic blocked /
    //                            ad blocker / network failure) → INCONCLUSIVE
    //                            (one visitor's network issue, doesn't say
    //                            anything about merchant's account)
    // Only the first two update MAILCHIMP_PIXEL_AVAILABLE on the server. The
    // third is logged for diagnosis but doesn't move the BO panel's verdict.

    var VERDICT_THROTTLE_MS = 5 * 60 * 1000; // 5 minutes
    var VERDICT_STORAGE_KEY = 'mc_pixel_verdict_at';

    function shouldRunVerdictProbe() {
        try {
            var stored = window.localStorage.getItem(VERDICT_STORAGE_KEY);
            if (!stored) return true;
            var ts = parseInt(stored, 10);
            if (isNaN(ts)) return true;
            return (Date.now() - ts) >= VERDICT_THROTTLE_MS;
        } catch (e) {
            // localStorage disabled (private mode, security policy) — fail open.
            // Server-side dedupe in pixeltelemetry.php prevents log spam.
            return true;
        }
    }

    function markVerdictReported() {
        try {
            window.localStorage.setItem(VERDICT_STORAGE_KEY, String(Date.now()));
        } catch (e) {
            // localStorage write failed — silently ignore. Worst case: we
            // re-probe next page load.
        }
    }

    // Single polling loop that does TWO things on the same SDK-ready event:
    //   1. Reconcile identification state (every page load — never throttled)
    //   2. Fire verdict probe + telemetry (throttled to once per 5 min)
    //
    // Combining into one whenPixelReady call avoids two competing polling
    // loops both checking the same $mcSite.pixel.api condition.
    var doVerdictProbe = shouldRunVerdictProbe();
    // Explicit opt-out short-circuit: when mc_user_optin=false, the SDK
    // refuses to expose pixel.api regardless of whether the merchant's
    // account has Pixel enabled. Running the verdict probe here would
    // misattribute the visitor's consent state to an account-level
    // capability problem (one opted-out visitor would flip the BO panel
    // from green to red via the PIXEL_NOT_AVAILABLE telemetry). Skip the
    // entire probe + identifyOrReset pipeline; mark reported so we don't
    // pile up throttle attempts on every page load.
    if (isExplicitlyOptedOut()) {
        if (doVerdictProbe) {
            markVerdictReported();
        }
    } else {
        whenPixelReady(
            function (api) {
                // (1) Always: align SDK identity with current login state.
                identifyOrReset(api);

                // (2) Throttled: report PIXEL_AVAILABLE verdict.
                if (doVerdictProbe) {
                    sendTelemetry('PIXEL_AVAILABLE', 1, 'pixel.api ready on first page load');
                    markVerdictReported();
                }
            },
            function () {
                // Timeout path: SDK never became ready. identifyOrReset can't run
                // (no api), but verdict-probe diagnostics still apply.
                if (doVerdictProbe) {
                    if (!window.$mcSite) {
                        sendTelemetry('SDK_LOAD_FAILED', 3,
                            '$mcSite never appeared — chimpstatic blocked or load failed');
                    } else if (!window.$mcSite.pixel || !window.$mcSite.pixel.api) {
                        sendTelemetry('PIXEL_NOT_AVAILABLE', 2,
                            '$mcSite loaded but pixel.api not exposed — account likely not pixel-enabled');
                    } else {
                        // Theoretically unreachable — the readiness check above
                        // would have called onReady. Kept for defense.
                        sendTelemetry('SDK_TIMEOUT', 2, 'unexpected: pixel.api present but timeout fired');
                    }
                    markVerdictReported();
                }
            }
        );
    }

    // ==================================================================
    // Shared event-fire infrastructure
    //
    // Used by all event types (PRODUCT_VIEWED in mc-pixel-bridge-product.js,
    // future CART_VIEWED / CHECKOUT_STARTED / PURCHASED / PRODUCT_ADDED_TO_CART
    // in their own per-event JS files). Lives in core so every page has the
    // queue + dedupe ready regardless of which event-specific file loads.
    //
    //   Component                       | Purpose
    //   --------------------------------+-----------------------------------
    //   firedPixelEvents + fireOnce()   | Page-local Set keyed by
    //                                   | "<event_type>:<fingerprint>". Same
    //                                   | event with same fingerprint fires
    //                                   | only once per page-load — lets us
    //                                   | safely wire belt-and-suspenders
    //                                   | redundant sources for the same
    //                                   | logical event.
    //   MC_PIXEL_EVENTS_QUEUE drain     | Drains payloads PHP hooks pushed
    //   + push interceptor              | before the bridge loaded, then
    //                                   | replaces .push() with an immediate-
    //                                   | fire interceptor so late-arriving
    //                                   | inline scripts (e.g.,
    //                                   | displayProductAdditionalInfo
    //                                   | re-injected on combination change
    //                                   | via jQuery .replaceWith()) fire
    //                                   | through the same path.
    //   onQuickviewOpened               | Hummingbird quickview firing —
    //                                   | quickviews open from category /
    //                                   | search / listing pages too (not
    //                                   | just product pages), so this lives
    //                                   | in core, not in the product-page-
    //                                   | only file.
    // ==================================================================

    var firedPixelEvents = {};

    /**
     * Fire `eventType` with `payload` through mcTrack, but only if this
     * (eventType, fingerprint) pair hasn't fired yet on this page-load.
     * Repeated calls with the same fingerprint are silently dropped.
     *
     * `source` is a short trigger tag (e.g. "initial", "combination",
     * "quickview", "hook") that mcTrack surfaces in telemetry messages so
     * merchants + support can tell at a glance what caused the fire. The
     * FIRST call to fireOnce for a given fingerprint owns the source tag;
     * subsequent deduped attempts log their OWN source at verbose so the
     * full causal chain is visible if needed.
     *
     * @param {string} eventType   Mailchimp event name (e.g., 'PRODUCT_VIEWED')
     * @param {object} payload     event payload (passed verbatim to api.track)
     * @param {string} fingerprint unique key — typically the product.id
     * @param {string} [source]    trigger tag — see above
     */
    function fireOnce(eventType, payload, fingerprint, source, via) {
        var key = eventType + ':' + (fingerprint || '');
        if (firedPixelEvents[key]) {
            // Silent dedupe by design — a PIXEL_EVENT_DEDUPED telemetry here
            // produces a confusing "logged: false" ping per combination change
            // (the event isn't whitelisted server-side). The winning path's
            // log entry already shows the source; no redundant signal needed.
            return;
        }
        firedPixelEvents[key] = { ts: Date.now(), source: source || null, via: via || null };
        mcTrack(eventType, payload, source, via);
    }

    // NOTE: Session-scoped dedupe was considered for CART_VIEWED + CHECKOUT_
    // STARTED (so refresh of /cart or /order within the same tab session
    // doesn't refire the event). Deferred — Mailchimp's analytics platform
    // dedupes events server-side via their own visitor correlation, so
    // client-side dedupe was over-engineering with untested edge cases
    // (private browsing, identity transitions, post-purchase reset).

    /**
     * Queue interceptor — drains any events that PHP hooks already pushed
     * before the bridge JS loaded, then replaces the array with an object
     * whose .push() fires immediately (so late-arriving inline scripts —
     * e.g., displayProductAdditionalInfo re-injected on combination change
     * via jQuery .replaceWith() — fire through the same code path).
     *
     * Same pattern as Google Analytics' dataLayer.push interceptor.
     */
    function installEventQueueInterceptor() {
        var pre = window.MC_PIXEL_EVENTS_QUEUE;
        window.MC_PIXEL_EVENTS_QUEUE = {
            push: function (evt) { handleQueuedEvent(evt); }
        };
        if (pre && pre.length) {
            for (var i = 0; i < pre.length; i++) {
                handleQueuedEvent(pre[i]);
            }
        }
    }

    function handleQueuedEvent(evt) {
        if (!evt || typeof evt !== 'object' || !evt.event_type) return;
        var name = evt.event_type;
        // Pull the optional `_source` hint the PHP hook sets based on
        // request context (quickview AJAX vs combination-refresh AJAX vs
        // initial render). Strip it from the payload before forwarding to
        // api.track — Mailchimp wouldn't recognize it.
        var source = evt._source || 'hook';
        // Anything that reached us via the queue came from a server-side
        // hook outputting an inline <script>. Tag the `via` so support can
        // tell this apart from the cfg.initialProduct (JsDef) path.
        var via = evt._via || 'hook';
        var payload = {};
        for (var k in evt) {
            if (k !== 'event_type'
                && k !== '_source'
                && k !== '_via'
                && Object.prototype.hasOwnProperty.call(evt, k)) {
                payload[k] = evt[k];
            }
        }
        // For PRODUCT_VIEWED + PURCHASED + similar events with a wrapped
        // object (product / order), use the wrapped object's id as the
        // fingerprint. Fallback to JSON for events with no clear key.
        var fp = '';
        if (payload.product && payload.product.id) fp = String(payload.product.id);
        else if (payload.order && payload.order.id) fp = String(payload.order.id);
        else if (payload.cart && payload.cart.id) fp = String(payload.cart.id);
        else fp = JSON.stringify(payload).slice(0, 100);
        fireOnce(name, payload, fp, source, via);
    }

    /**
     * Handler for `prestashop.on('quickviewOpened')` — hummingbird's
     * quickview modal doesn't call the displayProductAdditionalInfo hook
     * (its quickview.tpl has no module hooks). DOM scraping picks up
     * id_product / id_product_attribute from the modal's data attributes
     * and price/title from schema.org microdata. Skips with telemetry if
     * any required field is missing.
     */
    // Dedupe timestamp — both `prestashop.on('quickviewOpened')` AND the
    // jQuery `shown.bs.modal` delegate route here. On hummingbird both fire
    // for the same modal open (hummingbird's quickview.ts emits
    // quickviewOpened from inside its own shown.bs.modal handler). Without
    // this guard, PRODUCT_VIEWED would track twice per quickview on
    // hummingbird. 500ms window is well above the inter-listener delay
    // (microseconds) but well below any realistic interval between two
    // genuinely separate quickview opens by the visitor.
    var lastQuickviewOpenedAt = 0;

    function onQuickviewOpened() {
        if (Date.now() - lastQuickviewOpenedAt < 500) return;
        lastQuickviewOpenedAt = Date.now();
        // Same rationale as onCartUpdate: opted-out visitors should never
        // produce visitor-activity telemetry (QUICKVIEW_DATA_INCOMPLETE
        // mentions the product context). Bail before any work.
        if (isExplicitlyOptedOut()) {
            return;
        }
        var modal = document.querySelector('.modal.show[id^="quickview-modal-"]')
                 || document.querySelector('[id^="quickview-modal-"]');
        if (!modal) {
            sendTelemetry('QUICKVIEW_DATA_INCOMPLETE', 1, 'quickview modal element not found');
            return;
        }
        var pid = modal.dataset ? modal.dataset.idProduct : null;
        var paid = modal.dataset ? (modal.dataset.idProductAttribute || '') : '';
        if (!pid) {
            // Some themes encode IDs in the modal's id attribute:
            // id="quickview-modal-{pid}-{paid}"
            var m = String(modal.id || '').match(/^quickview-modal-(\d+)(?:-(\d+))?/);
            if (m) {
                pid = pid || m[1];
                if (!paid && m[2]) paid = m[2];
            }
        }
        if (!pid) {
            sendTelemetry('QUICKVIEW_DATA_INCOMPLETE', 1, 'product id absent on quickview modal');
            return;
        }
        var titleEl = modal.querySelector('[itemprop="name"], .product__name, .product-name, h1');
        var priceEl = modal.querySelector('[itemprop="price"]');
        var currencyEl = modal.querySelector('[itemprop="priceCurrency"]');
        var skuEl = modal.querySelector('[itemprop="sku"]');
        // Quickview cover image — try schema.org first, then common selectors.
        // Hummingbird modal uses .product__cover, .product__image, .product__left img
        // (no [itemprop="image"]). Classic uses .product-cover img.
        var imageEl = modal.querySelector('[itemprop="image"]')
                   || modal.querySelector(
                        '.product-cover img, '
                      + '.product__image-cover img, '
                      + '.product-images__cover img, '
                      + 'img.js-qv-product-cover, '
                      + '.js-qv-product-images img, '
                      + '.product__cover img, '
                      + '.product__left img.img-fluid');
        // "View full product" link — quickview footer usually has one. Filter
        // by href shape matching PS's product URL pattern so we skip stray <a>s.
        var fullProductLink = null;
        var allLinks = modal.querySelectorAll('a[href]');
        for (var li = 0; li < allLinks.length; li++) {
            var href = allLinks[li].getAttribute('href') || '';
            if (/\/\d+-\d+-/.test(href) || /\/\d+-[a-z0-9][a-z0-9-]*\.html/i.test(href)) {
                fullProductLink = href;
                break;
            }
        }

        var title = titleEl ? String(titleEl.textContent).replace(/\s+/g, ' ').trim() : '';
        // price = null distinguishes "extraction failed" from "genuinely 0"
        // (free product); the validator rejects null → triggers AJAX cascade.
        var price = null;
        if (priceEl) {
            var content = priceEl.getAttribute('content');
            var pmicro = content ? parseFloat(content)
                                 : parseFloat(String(priceEl.textContent).replace(/[^0-9.\-]/g, ''));
            if (!isNaN(pmicro)) price = pmicro;
        }
        if (price === null || price <= 0) {
            // Hummingbird quickview has no [itemprop="price"] — class-based
            // markup only. Try the visible "current price" block first
            // (current price after discount); falls back to regular price.
            var fallbackPriceEl = modal.querySelector(
                '.current-price, '
              + '.product__price, '
              + '.product-price, '
              + '.product__discount-price .product__prices-inline:not(.product__regular-price), '
              + '.product__prices-inline.product__prices-inline--small-gap:not(.product__regular-price)'
            ) || modal.querySelector('.product__regular-price, .product__prices');
            if (fallbackPriceEl) {
                // Price text often looks like "Price: €22.94" or "From €22.94".
                // Strip everything that isn't a digit / dot / comma / minus,
                // then normalize locale separators (1.234,56 → 1234.56).
                var raw = String(fallbackPriceEl.textContent || '').replace(/[^0-9.,\-]/g, '');
                var dotIx = raw.lastIndexOf('.');
                var commaIx = raw.lastIndexOf(',');
                var decimalSep = dotIx > commaIx ? '.' : (commaIx > dotIx ? ',' : '.');
                if (decimalSep === ',') {
                    raw = raw.replace(/\./g, '').replace(',', '.');
                } else {
                    raw = raw.replace(/,/g, '');
                }
                var p = parseFloat(raw);
                if (!isNaN(p) && p > 0) price = p;
            }
        }
        var currency = '';
        if (currencyEl) {
            currency = (currencyEl.getAttribute('content') || String(currencyEl.textContent || '')).trim();
        }
        // Cascade through initialProduct → window.prestashop.currency.iso_code
        // (the latter is page-wide PS native data — covers quickview-from-
        // category-page where MC_PIXEL.initialProduct doesn't exist).
        if (!currency) currency = resolveCurrency();
        var sku = skuEl ? String(skuEl.textContent || '').trim() : '';
        var imageUrl = '';
        if (imageEl) {
            imageUrl = String(imageEl.getAttribute('content')
                || imageEl.getAttribute('data-image-large-src')
                || imageEl.getAttribute('src') || '');
        }

        var resolvedId = (paid && paid !== '0') ? paid : pid;
        // Capture the DOM scrape as DEFENSE — used only if AJAX fails. The
        // scrape isn't reliable across themes (hummingbird's modal markup
        // has no schema.org microdata; class-based parsing can splice the
        // regular-vs-current price texts into a Frankenstein number) — so
        // we treat it as a last-resort, not a primary source.
        var scrapedProduct = {
            id:         String(resolvedId),
            productId:  String(pid),
            title:      title,
            price:      (typeof price === 'number' && !isNaN(price)) ? price : null,
            currency:   currency,
            sku:        sku,
            productUrl: fullProductLink || '',
            imageUrl:   imageUrl,
            vendor:     '',
            categories: []
        };

        // Cascade for quickview (AJAX-FIRST):
        //   1. AJAX /pixelproductinfo with the modal's ids — server-built
        //      payload with authoritative price, sku, image, vendor,
        //      categories. The quickview modal already loaded via an AJAX
        //      call PS made; adding one more (fire-and-forget) doesn't
        //      block UI. mcTrack is async — visitor sees the modal
        //      immediately, the pixel event reaches Mailchimp ~100 ms later.
        //   2. If AJAX fails (network / server / endpoint blocked), fall
        //      back to the DOM scrape. May produce inaccurate fields but
        //      better than skipping entirely.
        //   3. If neither works, log PIXEL_DATA_INCOMPLETE and skip mcTrack.
        fetchProductInfo(pid, paid || 0, function (serverProduct) {
            if (serverProduct && hasRequiredPixelFields(serverProduct)) {
                fireOnce('PRODUCT_VIEWED', { product: serverProduct },
                    String(serverProduct.id), 'quickview',
                    'prestashop.on(quickviewOpened) + ajax');
                return;
            }
            // AJAX failed — try the scrape as defense.
            if (hasRequiredPixelFields(scrapedProduct)) {
                fireOnce('PRODUCT_VIEWED', { product: scrapedProduct },
                    String(resolvedId), 'quickview',
                    'prestashop.on(quickviewOpened) + css-scrape (ajax failed)');
                return;
            }
            sendTelemetry('PIXEL_DATA_INCOMPLETE', 2,
                'quickview product ' + pid + (paid ? '/' + paid : '')
                + ' — required fields missing after AJAX + DOM scrape fallback');
        });
    }

    // ------------------------------------------------------------------
    // PRODUCT_ADDED_TO_CART — prestashop.on('updateCart') subscription
    // ------------------------------------------------------------------

    /**
     * Match a single line in the PS-emitted cart.products[] array by id_product
     * + id_product_attribute. The `cart.products` array is what PS ships in
     * the updateCart event payload (both `e.reason.cart.products` and
     * `e.resp.cart.products` carry the same data); we always read from
     * `e.reason.cart` because that's defined at the moment the event fires.
     *
     * Returns the matching line object or null.
     */
    function findCartLineItem(cart, idProduct, idProductAttribute) {
        if (!cart || !cart.products || !cart.products.length) return null;
        var pidStr = String(idProduct);
        var paidStr = String(idProductAttribute || 0);
        for (var i = 0; i < cart.products.length; i++) {
            var p = cart.products[i];
            if (String(p.id_product) === pidStr
                && (String(p.id_product_attribute || 0) === paidStr
                    || (paidStr === '0' && !p.id_product_attribute))) {
                return p;
            }
        }
        return null;
    }

    /**
     * Build a Mailchimp Pixel `product.item` shape from a PS cart line. Field
     * mapping is stable across PS 1.7.0.6 → 9.1: every key referenced here is
     * present in the cart.products[] line items PS emits in `updateCart`.
     *   - line.id                    variant id (or product id when no variant)
     *   - line.id_product            parent product id
     *   - line.name                  product name
     *   - line.price_amount          numeric, with-tax unit price (preferred)
     *   - line.price_wt              fallback (same value, older naming)
     *   - line.reference             SKU
     *   - line.url                   variant-aware URL
     *   - line.cover.bySize.*        cover image variants
     *   - line.manufacturer_name     vendor
     *   - line.category              single category name (best-effort)
     */
    function buildCartLineProductItem(line, idProduct, idProductAttribute) {
        if (!line) return null;
        var unitPrice = null;
        if (typeof line.price_amount === 'number') unitPrice = line.price_amount;
        else if (typeof line.price_wt === 'number') unitPrice = line.price_wt;
        var imageUrl = '';
        if (line.cover && line.cover.bySize) {
            var bs = line.cover.bySize;
            imageUrl = (bs.large_default && bs.large_default.url)
                    || (bs.default_xl && bs.default_xl.url)
                    || (bs.home_default && bs.home_default.url)
                    || (bs.medium_default && bs.medium_default.url) || '';
        }
        // Categories DISABLED for PRODUCT_ADDED_TO_CART (Mailchimp spec marks
        // it optional). The cart presenter (PS Cart::getProducts) only exposes
        // `line.category` — the URL slug of the default category, lowercase
        // (e.g. "men"). That's data-inconsistent with PRODUCT_VIEWED's
        // categories which come from Product::getProductCategoriesFull and
        // are proper-case + multi (e.g. ["Home","Clothes","Men"]). Sending
        // both shapes would break Mailchimp segments built on category
        // strings (case-sensitive, exact match) — same product would match
        // a "Men" segment on view but miss the same segment on add-to-cart.
        // To re-enable cleanly we'd need either an AJAX category lookup per
        // cart line OR a server-side enrichment endpoint. For now, omit.
        // var categories = [];
        // if (line.category && typeof line.category === 'string') {
        //     categories.push(line.category);
        // }
        // productUrl — strip the `#/<attr>-<val>/...` anchor PS appends. The
        // hash is internal state for combination restoration; carrying it
        // into Mailchimp creates stale URLs (the hash reflects the variant
        // active when this line was last presented, not necessarily the
        // current selection on a multi-step refresh). Mirrors the same
        // hash-strip we do in buildPixelProductPayloadFromProduct /
        // buildPixelProductPayloadFromArray on the PHP side.
        var rawUrl = String(line.url || '');
        var urlHashAt = rawUrl.indexOf('#');
        var productUrl = urlHashAt >= 0 ? rawUrl.slice(0, urlHashAt) : rawUrl;
        return {
            id:         String(line.id || idProductAttribute || idProduct),
            productId:  String(line.id_product || idProduct),
            title:      String(line.name || ''),
            price:      (unitPrice !== null) ? unitPrice : null,
            currency:   resolveCurrency(),
            sku:        String(line.reference || ''),
            productUrl: productUrl,
            imageUrl:   imageUrl,
            vendor:     String(line.manufacturer_name || '')
            // categories: categories  // see comment above
        };
    }

    /**
     * Round to 2 decimal places, IEEE-754-noise-safe (`22.94 * 3` → `68.82`,
     * not `68.81999999...`). Used for the top-level `price` line-total field
     * = unitPrice × quantity, which Mailchimp's PRODUCT_ADDED_TO_CART event
     * spec defines as "value of THIS event" — i.e., what the visitor just
     * added, NOT the cart's running subtotal.
     */
    function round2(n) { return Math.round((n + Number.EPSILON) * 100) / 100; }

    /**
     * Wrap a Pixel-shaped product item + its quantity into Mailchimp's 3P
     * spec `{ item, quantity }` shape. This is the SAME structure both
     * cart-shaped events use:
     *   - PRODUCT_ADDED_TO_CART → `product: { item, quantity, price, currency }`
     *     (caller adds price/currency siblings after wrapping)
     *   - CART_VIEWED          → `cart.lineItems[N] = { item, quantity }`
     *
     * Centralized here so both cart-shaped events have a single source of
     * truth for the wrapper shape (and any future quantity-coercion logic).
     *
     * @param {object} item       output of buildCartLineProductItem (or pixelproductinfo)
     * @param {number|string} quantity
     * @return {object}           Mailchimp-shaped { item, quantity }
     */
    function wrapLineItem(item, quantity) {
        var q;
        if (typeof quantity === 'number' && !isNaN(quantity)) {
            q = quantity;
        } else {
            q = Number(quantity);
            if (isNaN(q) || q < 1) q = 1;
        }
        return { item: item, quantity: q };
    }

    /**
     * Hybrid cartId resolver:
     *   1. If MC_PIXEL.cartId is > 0 → use it directly (99% case: cart already
     *      existed at pageload). Zero AJAX.
     *   2. If MC_PIXEL.cartId is 0/falsy → AJAX /pixelcartinfo. Covers the
     *      first-add-of-session edge case where PS lazy-creates the cart row
     *      AFTER setMedia ran.
     *   3. Both fail → onDone(null), caller logs PIXEL_DATA_INCOMPLETE.
     */
    function resolveCartId(onDone) {
        var cached = cfg.cartId;
        if (cached && Number(cached) > 0) {
            onDone(Number(cached));
            return;
        }
        fetchCartInfo(function (info) {
            // Cache the freshly-fetched id back into cfg so subsequent
            // events in the same pageload skip the AJAX.
            if (info && info.cartId) {
                cfg.cartId = Number(info.cartId);
                onDone(Number(info.cartId));
            } else {
                onDone(null);
            }
        });
    }

    /**
     * Handler for `prestashop.on('updateCart', e)`. Fires only when
     * `e.reason.linkAction === 'add-to-cart'` — PS emits the same event
     * for remove/update-quantity actions, which map to other Mailchimp
     * events we'll wire up later (REMOVED_FROM_CART isn't in the 3P spec
     * but quantity-change MAY be tracked separately if needed).
     *
     * Cross-PS verified: `linkAction = 'add-to-cart'` exists in PS 1.7.0.6
     * core.js line 1873 and continues through PS 9.1 (theme core-prod.js).
     * `e.reason.cart.products[]` field names (id_product, id_product_
     * attribute, name, price_amount, reference, url, cover.bySize.*,
     * manufacturer_name, category) are stable across the matrix.
     */
    function onCartUpdate(e) {
        if (!e || !e.reason) return;
        if (e.reason.linkAction !== 'add-to-cart') return;
        // Visitor opted out: skip the whole event-handling chain. Without this
        // guard the handler still runs end-to-end and fires PIXEL_DATA_INCOMPLETE
        // telemetry containing visitor activity (product id, "add-to-cart")
        // — because fetchCartInfo correctly short-circuits on opt-out, which
        // upstream code then mistakes for a failed AJAX. Mirrors the top-level
        // opt-out guard already used in mcTrack, callSdkIdentify, etc.
        if (isExplicitlyOptedOut()) {
            return;
        }

        var idProduct = e.reason.idProduct;
        var idProductAttribute = e.reason.idProductAttribute || 0;
        var qty = (e.resp && e.resp.quantity) ? Number(e.resp.quantity) : 1;

        // ===== LAYER 1: build item from e.reason.cart.products[] =====
        // PS-emitted line items carry the full server-presented product data.
        // 99% path — zero AJAX, cart product line is already in the event.
        var line = findCartLineItem(e.reason.cart, idProduct, idProductAttribute);
        var item = line ? buildCartLineProductItem(line, idProduct, idProductAttribute) : null;
        var cartQuantityForFp = (line && line.cart_quantity) ? line.cart_quantity : qty;

        function fireWithItem(itemPayload, viaTag) {
            // Line value of this event = unit × just-added qty, NOT cart
            // subtotal. line.total_wt would be cart-line value including
            // previously-added quantities of the same variant.
            var lineTotal = round2(itemPayload.price * qty);
            var currency = itemPayload.currency;
            resolveCartId(function (cartId) {
                if (!cartId) {
                    sendTelemetry('PIXEL_DATA_INCOMPLETE', 2,
                        'add-to-cart: cartId unavailable (JsDef=0, AJAX returned ok:false) '
                        + 'for product ' + idProduct);
                    return;
                }
                // Fingerprint by cartId + variant + new cart_quantity. Two
                // rapid clicks for the same logical action (PS sometimes
                // emits twice) dedupe; user adding the SAME variant later
                // gets a fresh fire because cart_quantity changed.
                var fp = 'add:' + cartId + ':' + itemPayload.id
                        + ':' + cartQuantityForFp;
                // Mailchimp 3P Pixel spec for PRODUCT_ADDED_TO_CART nests
                // quantity, price, currency INSIDE the product object
                // (different from PRODUCT_VIEWED which is flat):
                //   { cartId, product: { item, quantity, price, currency } }
                // The {item, quantity} core is the same shape CART_VIEWED's
                // lineItems[] entries use — shared via wrapLineItem().
                // See https://mailchimp.com/help/mailchimp-site-tracking-pixel-integration-guidance/
                var productPayload = wrapLineItem(itemPayload, qty);
                productPayload.price = lineTotal;
                productPayload.currency = currency;
                fireOnce('PRODUCT_ADDED_TO_CART',
                    { cartId: String(cartId), product: productPayload },
                    fp, 'add-to-cart', viaTag);
            });
        }

        if (item && hasRequiredPixelFields(item)) {
            fireWithItem(item, 'prestashop.on(updateCart)');
            return;
        }

        // ===== LAYER 2: AJAX /pixelproductinfo fallback =====
        // Reached when:
        //   - the matching product wasn't in e.reason.cart.products[] (stale
        //     event payload, cache glitch, custom theme intercepting the event)
        //   - the line was found but required fields are missing (custom
        //     theme stripping standard cart-line fields)
        // We have idProduct + idProductAttribute from e.reason — server can
        // build the authoritative item payload identical to what PRODUCT_VIEWED
        // uses on Layer 3.
        fetchProductInfo(idProduct, idProductAttribute, function (serverProduct) {
            if (serverProduct && hasRequiredPixelFields(serverProduct)) {
                fireWithItem(serverProduct, 'prestashop.on(updateCart) + ajax-fallback');
                return;
            }
            // ===== Both layers failed =====
            sendTelemetry('PIXEL_DATA_INCOMPLETE', 2,
                'add-to-cart: product ' + idProduct
                + (idProductAttribute ? '/' + idProductAttribute : '')
                + ' — required fields missing after cart.products[] lookup '
                + '+ /pixelproductinfo AJAX fallback');
        });
    }

    // CART_VIEWED lives in mc-pixel-bridge-cart.js — that file is registered
    // by setMedia ONLY when php_self === 'cart', so its IIFE only runs on
    // actual cart pageloads (zero overhead on category / product / checkout
    // pages). All cart-shared helpers it needs (buildCartLineProductItem,
    // hasRequiredPixelFields, resolveCurrency, resolveCartId, fetchCartInfo,
    // fireOnce, sendTelemetry) are exposed on window.MC_PIXEL_BRIDGE below.

    // ------------------------------------------------------------------
    // Wire-up — shared infrastructure (always-on)
    // ------------------------------------------------------------------

    // (1) Queue interceptor. Must be installed before any per-event JS
    //     file runs (their inline-script hook pushes need this in place
    //     to fire-on-push). Done in core so it's ready regardless of
    //     which per-event files are loaded for this page.
    installEventQueueInterceptor();

    // (2) Quickview listeners. Quickview opens from category / search /
    //     listing pages too — NOT only from product pages. So the
    //     listener registration MUST live in core (loaded everywhere),
    //     not in mc-pixel-bridge-product.js (loaded only on product
    //     pages).
    //     Two listeners, both routing to onQuickviewOpened (which dedupes
    //     via lastQuickviewOpenedAt — 500ms window):
    //       - `prestashop.on('quickviewOpened')` — hummingbird emits this
    //         from its quickview.ts after shown.bs.modal fires.
    //       - jQuery delegate on `shown.bs.modal` — classic theme (every
    //         PS version 1.7.0.6 → 9.1.3) uses Bootstrap modals for
    //         quickview, verified by reading
    //         themes/classic/templates/catalog/_partials/quickview.tpl
    //         on all 6 docker containers; modal ID pattern
    //         `quickview-modal-{PID}-{PAID}` is identical to hummingbird's.
    if (window.prestashop && typeof window.prestashop.on === 'function') {
        try {
            window.prestashop.on('quickviewOpened', onQuickviewOpened);
        } catch (e) { /* hummingbird-only; tolerate absence on classic */ }
        try {
            // (3) Cart update listener. Fires from anywhere a customer can
            //     add to cart (product page, category, search, quickview
            //     modal). e.reason.linkAction discriminates add-to-cart
            //     from update-quantity / remove-from-cart.
            window.prestashop.on('updateCart', onCartUpdate);
        } catch (e) { /* tolerate absence */ }
    }

    // Bootstrap modal lifecycle event — covers classic theme on every PS
    // version (which doesn't emit prestashop.quickviewOpened). jQuery is
    // guaranteed on the storefront across PS 1.7+ → 9.x. Delegated to
    // document so we can listen without the modal existing yet.
    if (window.jQuery) {
        try {
            window.jQuery(document).on(
                'shown.bs.modal',
                '[id^="quickview-modal-"]',
                onQuickviewOpened
            );
        } catch (e) { /* tolerate jQuery edge cases */ }
    }

    // ------------------------------------------------------------------
    // Expose helpers for the per-event JS files (mc-pixel-bridge-product.js,
    // mc-pixel-bridge-cart.js) + browser-console debug + standalone test fixture.
    // Cart-page-only and product-page-only logic lives in those bundles —
    // see their headers for the wire-up. Core only contains:
    //   - cross-page event-bus subscriptions (quickviewOpened, updateCart)
    //   - shared helpers used by both core and the per-page bundles
    // ------------------------------------------------------------------
    window.MC_PIXEL_BRIDGE = {
        track: mcTrack,
        identify: mcIdentify,
        identifyPhone: mcIdentifyPhone,
        whenReady: whenPixelReady,
        sendTelemetry: sendTelemetry,
        fireOnce: fireOnce,
        hasRequiredPixelFields: hasRequiredPixelFields,
        fetchProductInfo: fetchProductInfo,
        fetchCartInfo: fetchCartInfo,
        resolveCurrency: resolveCurrency,
        resolveCartId: resolveCartId,
        onQuickviewOpened: onQuickviewOpened,
        onCartUpdate: onCartUpdate,
        findCartLineItem: findCartLineItem,
        buildCartLineProductItem: buildCartLineProductItem,
        wrapLineItem: wrapLineItem,
        firedPixelEvents: firedPixelEvents,
        isExplicitlyOptedOut: isExplicitlyOptedOut,
        cfg: cfg
    };
})();
