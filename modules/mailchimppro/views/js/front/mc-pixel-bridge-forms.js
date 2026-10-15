/**
 * @author    Mailchimp
 * @copyright Mailchimp
 * @license   Proprietary
 */

/**
 * MailchimpPro — pixel form-identify watcher.
 *
 * Conditionally loaded only when MAILCHIMP_PIXEL_IDENTIFY_ON_FORMS is on
 * (server-side gate in mailchimppro::hookActionFrontControllerSetMedia).
 * When loaded, it listens for `submit` events on the document (newsletter,
 * contact, forgot-password — Steps 1/3/4) AND for `click` events on the
 * restock-alert button (Step 2 — ps_emailalerts is JS-driven AJAX with no
 * real <form>). On a match it fires api.identify(...) with the visitor's
 * email — but only for guests, never overwriting a stronger identity.
 *
 * Lifecycle on a typical visit:
 *   1. Bridge boots → checks for a pending-form-identify localStorage
 *      flag (from a prior form-POST + reload). If found and recent,
 *      fires identify and clears the flag.
 *   2. Document-level submit listener (capture phase) — newsletter / contact /
 *      forgot-password form submissions.
 *   3. Document-level click listener (capture phase) — restock-alert button
 *      clicks (no submit event because there's no real <form>).
 *   4. Each handler:
 *        - DUAL GATE: skip if visitor is logged-in (explicit
 *          window.prestashop.customer.is_logged check — race-free) OR if
 *          mc_pixel_last_identify is already set (covers guest mid-checkout
 *          + prior identify). See bridge file for rationale.
 *        - Read email from the relevant DOM container, validate basic shape.
 *        - Hash via crypto.subtle when format=EMAIL_SHA256, else use plain.
 *        - Write a pending-identify flag to localStorage (defense against
 *          the form-POST race where the browser navigates BEFORE the SDK
 *          persists the identify).
 *        - Call window.MC_PIXEL_BRIDGE.identify(...) — synchronous queue
 *          insert into the SDK (the SDK dedupes against its own stored
 *          anonymousId binding).
 *        - Schedule a 500ms setTimeout to clear the pending flag. The
 *          timeout is the AJAX-vs-form-POST detector: if the page stays
 *          alive (AJAX), the timeout fires + clears the flag (no
 *          duplicate recovery on subsequent refresh). If the page
 *          navigates (form-POST), the timeout is cancelled by tear-down
 *          and the flag survives to drive recovery on the next page.
 *
 * Cross-contamination caveat documented in the FAQ: on shared browsers,
 * a guest who identifies via a form binds the pixel anonymousId to their
 * email until their browser data is cleared. Admins on multi-user
 * terminals can disable form-identify via the master toggle in the BO.
 */
(function () {
    if (!window.MC_PIXEL_BRIDGE || !window.MC_PIXEL || !window.MC_PIXEL.identifyForms) {
        return;
    }

    var bridge = window.MC_PIXEL_BRIDGE;
    var cfg = window.MC_PIXEL;
    var STORAGE_KEY_LAST_IDENTIFY = 'mc_pixel_last_identify';
    var STORAGE_KEY_PENDING_FORM = 'mc_pixel_pending_form_identify';
    var PENDING_TTL_MS = 5 * 60 * 1000; // 5 min — survives a normal form-POST + reload, expires anything stale.
    var EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    // ------------------------------------------------------------------
    // Selector resolution — UNION of server-emitted core defaults and the
    // admin's additive list. Done once at module load; selectors don't
    // change at runtime.
    // ------------------------------------------------------------------
    function resolveSelectors(formKey) {
        var formCfg = cfg.identifyForms && cfg.identifyForms[formKey];
        if (!formCfg) return [];

        var core = Array.isArray(formCfg.coreDefaults) ? formCfg.coreDefaults : [];
        var customStr = typeof formCfg.selectorsCustom === 'string' ? formCfg.selectorsCustom : '';
        var custom = customStr.trim() !== ''
            ? customStr.split(',').map(function (s) { return s.trim(); }).filter(Boolean)
            : [];

        // Union, deduped — admin entries that overlap core are dropped (no harm).
        var seen = {};
        var union = [];
        core.concat(custom).forEach(function (sel) {
            if (sel && !seen[sel]) {
                seen[sel] = true;
                union.push(sel);
            }
        });
        return union;
    }

    var newsletterSelectors = resolveSelectors('newsletter');
    var newsletterSelectorJoined = newsletterSelectors.join(',');

    var restockSelectors = resolveSelectors('restock');
    var restockSelectorJoined = restockSelectors.join(',');

    // Static wrapper + email-input selector lists used by the restock click
    // handler. Hardcoded (not admin-configurable) because they're stable
    // module + theme conventions — exposing them as another knob would be
    // confusing without adding coverage.
    //
    // Wrapper lookup walks UP from the clicked button via closest():
    //   - .js-mailalert                          (ps_emailalerts default template, classic theme)
    //   - [data-ps-ref="emailalerts"]            (hummingbird theme override, PS 9+)
    //   - [data-url*="ps_emailalerts"]           (any custom theme that keeps the module's AJAX URL attr)
    //
    // Email-input lookup runs INSIDE that wrapper:
    //   - [data-ps-ref="emailalerts-email"]      (hummingbird — authoritative selector)
    //   - input[type="email"]                    (classic + generic — module template has no name attr)
    //   - input[name="email"]                    (hummingbird has name="email"; defensive fallback)
    var RESTOCK_WRAPPER_SELECTOR = '.js-mailalert, [data-ps-ref="emailalerts"], [data-url*="ps_emailalerts"]';
    var RESTOCK_EMAIL_INPUT_SELECTOR = '[data-ps-ref="emailalerts-email"], input[type="email"], input[name="email"]';

    var contactSelectors = resolveSelectors('contact');
    // Runtime selector: build form[action="<contact URL>"] from PS's own
    // exposed URL constant. Catches multi-lingual contact URLs (French
    // /contactez-nous, German /kontaktieren-sie-uns, etc.) automatically
    // — admins on i18n shops don't need to paste the translated slug into
    // the BO selector field. The URL is set by PS core via Media::addJsDef
    // through json_encode (XSS-safe), but we still gate strictly on the
    // string having no quote characters before injecting into a selector.
    try {
        var contactUrl = (window.prestashop
            && window.prestashop.urls
            && window.prestashop.urls.pages
            && window.prestashop.urls.pages.contact) || '';
        if (typeof contactUrl === 'string'
            && contactUrl !== ''
            && contactUrl.indexOf('"') === -1
            && contactUrl.length < 500) {
            var runtimeContactSel = 'form[action="' + contactUrl + '"]';
            if (contactSelectors.indexOf(runtimeContactSel) === -1) {
                contactSelectors.push(runtimeContactSel);
            }
        }
    } catch (e) { /* prestashop global missing — skip runtime selector */ }
    var contactSelectorJoined = contactSelectors.join(',');

    var CONTACT_EMAIL_INPUT_SELECTOR = 'input[name="from"], input[type="email"], input[name="email"]';

    var forgotPasswordSelectors = resolveSelectors('forgotPassword');
    // Runtime selector for the password-recovery URL — same i18n-safe
    // pattern as contact above. PS exposes the URL via
    // window.prestashop.urls.pages.password; the bridge appends a
    // matching form[action="..."] selector so multi-lingual deployments
    // are caught without admin paste.
    try {
        var passwordUrl = (window.prestashop
            && window.prestashop.urls
            && window.prestashop.urls.pages
            && window.prestashop.urls.pages.password) || '';
        if (typeof passwordUrl === 'string'
            && passwordUrl !== ''
            && passwordUrl.indexOf('"') === -1
            && passwordUrl.length < 500) {
            var runtimePasswordSel = 'form[action="' + passwordUrl + '"]';
            if (forgotPasswordSelectors.indexOf(runtimePasswordSel) === -1) {
                forgotPasswordSelectors.push(runtimePasswordSel);
            }
        }
    } catch (e) { /* prestashop global missing — skip runtime selector */ }
    var forgotPasswordSelectorJoined = forgotPasswordSelectors.join(',');

    // ------------------------------------------------------------------
    // Email hashing — SHA-256 via crypto.subtle when format=EMAIL_SHA256,
    // else pass plain email through. crypto.subtle is universal on HTTPS
    // (which is our deployment floor); silently fail to plain on the rare
    // browser without it.
    // ------------------------------------------------------------------
    function formatIdentifyValue(email, done) {
        var normalized = String(email).trim().toLowerCase();
        if ((cfg.identifyFormat || 'EMAIL_SHA256') !== 'EMAIL_SHA256') {
            done(normalized);
            return;
        }
        try {
            var buf = new TextEncoder().encode(normalized);
            window.crypto.subtle.digest('SHA-256', buf).then(function (hash) {
                var hex = Array.prototype.map.call(new Uint8Array(hash), function (b) {
                    return b.toString(16).padStart(2, '0');
                }).join('');
                done(hex);
            }, function () { done(null); });
        } catch (e) {
            done(null);
        }
    }

    // ------------------------------------------------------------------
    // Defensive logged-in detection. window.prestashop is set by core PS
    // on every storefront page, BUT we still guard every access — custom
    // themes have been seen to defer or fully strip the global, and the
    // bridge must never throw inside a click/submit handler (PS uses
    // event-default semantics that we'd otherwise corrupt). Treat the
    // global as untrusted: any of "absent / non-object / customer absent /
    // is_logged falsy" => visitor is NOT logged in.
    //
    // We accept the boolean true OR the numeric 1 (some PS minor versions
    // serialize bool→int through json_encode-with-JSON_FORCE_OBJECT-paths;
    // belt-and-suspenders).
    // ------------------------------------------------------------------
    function isLoggedIn() {
        try {
            var ps = window.prestashop;
            if (!ps || typeof ps !== 'object') return false;
            var c = ps.customer;
            if (!c || typeof c !== 'object') return false;
            var flag = c.is_logged;
            return (flag === true || flag === 1);
        } catch (e) {
            return false;
        }
    }

    // ------------------------------------------------------------------
    // Storage helpers
    // ------------------------------------------------------------------
    function readLastIdentify() {
        try { return window.localStorage.getItem(STORAGE_KEY_LAST_IDENTIFY) || ''; }
        catch (e) { return ''; }
    }

    function writePendingFormIdentify(value, source) {
        try {
            window.localStorage.setItem(STORAGE_KEY_PENDING_FORM, JSON.stringify({
                value: value, source: source, ts: Date.now()
            }));
        } catch (e) { /* localStorage disabled — non-critical */ }
    }

    function readAndClearPendingFormIdentify() {
        try {
            var raw = window.localStorage.getItem(STORAGE_KEY_PENDING_FORM);
            if (!raw) return null;
            window.localStorage.removeItem(STORAGE_KEY_PENDING_FORM);
            var parsed = JSON.parse(raw);
            if (!parsed || !parsed.value || (Date.now() - parsed.ts) > PENDING_TTL_MS) {
                return null;
            }
            return parsed;
        } catch (e) { return null; }
    }

    // ------------------------------------------------------------------
    // Pending-identify pickup — runs once at boot. Handles the form-POST
    // page-reload race where the SDK's IndexedDB persistence may not have
    // completed before the browser navigated away.
    // ------------------------------------------------------------------
    var pending = readAndClearPendingFormIdentify();
    if (pending && !readLastIdentify()) {
        bridge.identify(pending.value);
        bridge.sendTelemetry('IDENTIFY', 1,
            humanFormLabel(pending.source) + ' identify recovered after page reload — '
            + formatLogValue(pending.value));
    }

    // ------------------------------------------------------------------
    // Shared identify routine — accepts the container (a <form> for submit
    // watchers; the ps_emailalerts wrapper for the restock click watcher)
    // plus a source label. Dual-gate is applied here so newsletter +
    // restock (+ Steps 3/4 contact + forgot-password) share the same skip
    // logic; ordering: explicit is_logged first (synchronous, no I/O),
    // then readLastIdentify (covers guest mid-checkout + prior identify).
    // ------------------------------------------------------------------
    function fireIdentifyFromContainer(container, source, emailInputSelector) {
        // Gate 1 — explicit logged-in check. Race-free, immediate;
        // doesn't depend on whether the main bridge has booted yet.
        if (isLoggedIn()) {
            bridge.sendTelemetry('IDENTIFY', 1,
                humanFormLabel(source) + ' — identify skipped (visitor is logged in)');
            return;
        }

        // Gate 2 — already-identified bookkeeping (covers guest mid-checkout
        // post personal-info, AND any prior form-identify this session).
        if (readLastIdentify()) {
            bridge.sendTelemetry('IDENTIFY', 1,
                humanFormLabel(source) + ' — identify skipped (visitor already identified)');
            return;
        }

        if (!container) return;
        var selector = emailInputSelector || 'input[type="email"], input[name="email"], input[name*="EMAIL"]';
        var emailInput = container.querySelector(selector);
        if (!emailInput || !emailInput.value) return;

        var email = String(emailInput.value).trim();
        if (!EMAIL_RE.test(email)) return;

        formatIdentifyValue(email, function (value) {
            if (!value) return;
            // Pending-flag handoff defends against the form-POST race:
            // user submits → browser navigates BEFORE the SDK persists →
            // the flag survives in localStorage → next page's bridge
            // re-fires identify. Without it, that race silently drops
            // the identify on slow connections.
            writePendingFormIdentify(value, source);
            // Same-page identify — synchronous queue insert into the SDK.
            // Covers AJAX-submitted forms (where the page never navigates).
            // For form-POST forms this still runs but may not persist
            // before navigation; the pending flag above is the recovery.
            bridge.identify(value);
            bridge.sendTelemetry('IDENTIFY', 1,
                humanFormLabel(source) + ' — identify fired (' + formatLogValue(value) + ')');
            // Optimistic LAST_IDENTIFY write — defends against the inverse
            // form-POST race where bridge.identify above DID reach the SDK
            // and the SDK persisted the event to IndexedDB before nav. On
            // the reload page, the SDK would flush the queued event AND
            // form-bridge's pending-pickup at boot would ALSO fire identify
            // with the same hash — 2 IDENTITY_ASSOCIATED events to Mailchimp
            // on a contact-form submit + reload sequence. Writing LAST_IDENTIFY
            // here makes the pending-pickup gate `!readLastIdentify()` fail on
            // the reload page, skipping the duplicate. The write is optimistic
            // (we don't know if bridge.identify actually persisted), but the
            // console.error hook + retry would catch any "Pixel not initialized"
            // failure and recover via SDK_LOAD_FAILED telemetry if all retries
            // exhaust — i.e. failure is never silently lost.
            try { window.localStorage.setItem(STORAGE_KEY_LAST_IDENTIFY, value); } catch (e) {}
            // Self-distinguishing AJAX vs form-POST. The setTimeout only
            // fires if the JS event loop is still alive — i.e. the page
            // did NOT navigate. So:
            //   AJAX (page stays alive)  → clear the flag → no duplicate
            //                              recovery on the user's next
            //                              refresh.
            //   form-POST (page navigates) → setTimeout is cancelled by
            //                                the navigation tear-down →
            //                                flag survives → next page's
            //                                bridge sees LAST_IDENTIFY set
            //                                and skips pickup (no dup).
            //
            // 500ms padding: well past typical browser nav (~50-100ms)
            // and past SDK in-memory persistence (~10-100ms).
            setTimeout(function () {
                try { window.localStorage.removeItem(STORAGE_KEY_PENDING_FORM); } catch (e) {}
            }, 500);
        });
    }

    // Human-friendly label for each form source — used in telemetry so admins
    // see "Newsletter form" instead of the internal source code "form-newsletter".
    function humanFormLabel(source) {
        switch (source) {
            case 'form-newsletter':       return 'Newsletter form';
            case 'form-restock':          return 'Restock-alert form';
            case 'form-contact':          return 'Contact form';
            case 'form-forgot-password':  return 'Forgot-password form';
            default:                      return 'Form (' + source + ')';
        }
    }

    // Format-aware log value — respects the admin's identify-format preference.
    //   EMAIL_SHA256 mode → only first 16 chars of the hash (privacy-conscious;
    //                      enough for admin to correlate visitors across log entries)
    //   EMAIL mode       → full plain email (admin already chose to ship plain
    //                      emails to Mailchimp; showing it in the log is consistent)
    function formatLogValue(storedValue) {
        // Default-case (undefined / null / empty / unknown value) falls through
        // to the hash-prefix branch — the privacy-safe default. Compare to
        // formatIdentifyValue above which uses `!== 'EMAIL_SHA256'` and needs
        // a fallback to keep the default on hash mode.
        if (cfg.identifyFormat === 'EMAIL') {
            return 'email ' + storedValue;
        }
        return 'value prefix ' + String(storedValue).substring(0, 16);
    }

    // ------------------------------------------------------------------
    // Submit watcher — newsletter today; Steps 3-4 will reuse this
    // listener for contact + forgot-password by adding selector unions.
    // Capture phase so we read the email BEFORE any onsubmit handler
    // mutates the form (AJAX) or navigates away (form-POST).
    // ------------------------------------------------------------------
    function handleSubmit(event) {
        var form = event.target;
        if (!form || form.nodeName !== 'FORM') return;

        if (newsletterSelectorJoined && form.matches && form.matches(newsletterSelectorJoined)) {
            fireIdentifyFromContainer(form, 'form-newsletter');
            return;
        }
        if (contactSelectorJoined && form.matches && form.matches(contactSelectorJoined)) {
            fireIdentifyFromContainer(form, 'form-contact', CONTACT_EMAIL_INPUT_SELECTOR);
            return;
        }
        if (forgotPasswordSelectorJoined && form.matches && form.matches(forgotPasswordSelectorJoined)) {
            // Forgot-password form has only one email input (name="email"),
            // so the default selector chain in fireIdentifyFromContainer
            // catches it cleanly — no override needed.
            fireIdentifyFromContainer(form, 'form-forgot-password');
            return;
        }
    }

    if (newsletterSelectorJoined || contactSelectorJoined || forgotPasswordSelectorJoined) {
        document.addEventListener('submit', handleSubmit, true);
    }

    // ------------------------------------------------------------------
    // Click watcher — restock-alert button (ps_emailalerts). The module
    // is JS-driven AJAX with NO real <form>: the user types an email and
    // clicks "Notify me when available". The module's own click handler
    // is jQuery $(document).on('click', ...) for classic / native delegation
    // for hummingbird — both bubble-phase. Our capture-phase listener
    // fires FIRST so we can read the email value cleanly before either
    // handler mutates the DOM (e.g., hides the input on success).
    // ------------------------------------------------------------------
    function handleClick(event) {
        var target = event.target;
        if (!target || typeof target.closest !== 'function') return;
        if (!restockSelectorJoined) return;

        var button = target.closest(restockSelectorJoined);
        if (!button) return;

        // Walk up to the ps_emailalerts wrapper — covers classic
        // (.js-mailalert) + hummingbird ([data-ps-ref="emailalerts"]) + any
        // custom theme that preserves the module's data-url attribute.
        // Fallback to the button itself's parent if no known wrapper found
        // (lets querySelector still attempt to find an email input nearby
        // without throwing).
        var wrapper = (typeof button.closest === 'function')
            ? button.closest(RESTOCK_WRAPPER_SELECTOR)
            : null;
        var container = wrapper || button.parentElement || button;

        fireIdentifyFromContainer(container, 'form-restock', RESTOCK_EMAIL_INPUT_SELECTOR);
    }

    if (restockSelectorJoined) {
        document.addEventListener('click', handleClick, true);
    }
})();
