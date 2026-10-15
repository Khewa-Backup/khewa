/**
 * PrestaChamps
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
 * @var axios
 * @var mailchimp
 */

const {createApp} = Vue

window.app = createApp({
    methods: {
        saveSettings(reason, showResponseMessage, callbackOnSuccess, callbackOnError) {
            if (!this.preventSave) {
                this.isSaving = true;
                axios
                    .post(
                        window.configurationUrl + '&action=saveSettings',
                        {
                            action: 'saveSettings',
                            selectedPreset: this.selectedPreset,
                            multiInstanceMode: this.multiInstanceMode,
                            cronjobBasedSync: this.cronjobBasedSync,
                            syncProducts: this.syncProducts,
                            syncCustomers: this.syncCustomers,
                            syncCartRules: this.syncCartRules,
                            syncOrders: this.syncOrders,
                            syncCarts: this.syncCarts,
                            syncCartsPassw: this.syncCartsPassw,
                            syncNewsletterSubscribers: this.syncNewsletterSubscribers,
                            statusForPending: this.statusForPending,
                            statusForRefunded: this.statusForRefunded,
                            statusForCancelled: this.statusForCancelled,
                            statusForShipped: this.statusForShipped,
                            statusForPaid: this.statusForPaid,
                            productDescriptionField: this.productDescriptionField,
                            existingOrderSyncStrategy: this.existingOrderSyncStrategy,
                            productSyncFilterActive: this.productSyncFilterActive,
                            productSyncFilterVisibility: this.productSyncFilterVisibility,
                            customerSyncFilterEnabled: this.customerSyncFilterEnabled,
                            customerSyncFilterNewsletter: this.customerSyncFilterNewsletter,
                            customerSyncFilterPeriod: this.customerSyncFilterPeriod,
                            customerSyncTagDefaultGroup: this.customerSyncTagDefaultGroup,
                            customerSyncTagGender: this.customerSyncTagGender,
                            customerSyncTagLanguage: this.customerSyncTagLanguage,
                            customerSyncCompanyFromAddress: this.customerSyncCompanyFromAddress,
                            cartRuleSyncFilterStatus: this.cartRuleSyncFilterStatus,
                            cartRuleSyncFilterExpiration: this.cartRuleSyncFilterExpiration,
                            newsletterSubscriberSyncFilterPeriod: this.newsletterSubscriberSyncFilterPeriod,
                            productImageSize: this.productImageSize,
                            listId: this.listId,
                            storeSynced: this.storeSynced,
                            logQueue: this.logQueue,
                            queueStep: this.queueStep,
                            queueAttempt: this.queueAttempt,
                            logCronjob: this.logCronjob,
                            showDashboardStats: this.showDashboardStats,
                            promoOverridesEnabled: this.promoOverridesEnabled,
                            autotrackEnabled: this.autotrackEnabled,
                            identifyFormat: this.identifyFormat,
                            identifyAjaxMode: this.identifyAjaxMode,
                            pixelLogEnabled: this.pixelLogEnabled,
                            pixelLogLevel: this.pixelLogLevel,
                            pixelLogMaxRows: this.pixelLogMaxRows,
                            pixelLogRateLimitPerMin: this.pixelLogRateLimitPerMin,
                            // Tags input in BO; Multiselect emits an array of
                            // unique strings (dedup is built-in via mode='tags').
                            // Server-side converts to comma-separated for storage.
                            pixelCheckoutControllers: Array.isArray(this.pixelCheckoutControllers)
                                ? this.pixelCheckoutControllers.join(',')
                                : (this.pixelCheckoutControllers || ''),
                            pixelOrderConfirmationControllers: Array.isArray(this.pixelOrderConfirmationControllers)
                                ? this.pixelOrderConfirmationControllers.join(',')
                                : (this.pixelOrderConfirmationControllers || ''),
                            pixelRequireConsent: this.pixelRequireConsent,
                            sdkSettleDelayMs: this.sdkSettleDelayMs,
                            identifyOnForms: this.identifyOnForms,
                            // v-model contains ONLY admin's custom additions
                            // (the hardcoded core defaults live in PHP +
                            // appear only as disabled dropdown entries, never
                            // in v-model). Comma-joined for storage — same
                            // shape as pixelCheckoutControllers.
                            identifyNewsletterSelectorsCustom: Array.isArray(this.identifyNewsletterSelectorsCustom)
                                ? this.identifyNewsletterSelectorsCustom.join(',')
                                : '',
                            identifyRestockSelectorsCustom: Array.isArray(this.identifyRestockSelectorsCustom)
                                ? this.identifyRestockSelectorsCustom.join(',')
                                : '',
                            identifyContactSelectorsCustom: Array.isArray(this.identifyContactSelectorsCustom)
                                ? this.identifyContactSelectorsCustom.join(',')
                                : '',
                            identifyForgotPasswordSelectorsCustom: Array.isArray(this.identifyForgotPasswordSelectorsCustom)
                                ? this.identifyForgotPasswordSelectorsCustom.join(',')
                                : '',
                        }
                    )
                    .then((response) => {
                            this.isSaving = false;
                            this.getEntityCount();
                            if (showResponseMessage !== false) {
                                this.showSuccess('Update successful!');
                            }
                            if (reason == 'multiInstanceMode') {
                                setTimeout(function () {
                                    location.reload();
                                }, 1500);
                            }
                            if (callbackOnSuccess && typeof callbackOnSuccess == 'function') {
                                callbackOnSuccess();
                            } else if (callbackOnSuccess && typeof this[callbackOnSuccess] == 'function') {
                                this[callbackOnSuccess](...callbackOnSuccessParams);
                            }
                        }
                    );
            }
        },
        markReadJsonJobs(reason, showResponseMessage) {
            this.isSaving = true;
            axios
                .post(
                    window.configurationUrl + '&action=markReadJsonJobs',
                    {
                        action: 'markReadJsonJobs',                        
                    }
                )
                .then((response) => {
                        this.isSaving = false;
                        this.getEntityCount();

                        if (showResponseMessage !== false) {
                            this.showSuccess('Update successful!');
                        }

                        setTimeout(function () {
                            location.reload();
                        }, 100);                        
                    }
                )
        },
        markReadAutoList(reason, showResponseMessage) {
            this.isSaving = true;
            axios
                .post(
                    window.configurationUrl + '&action=markReadAutoList',
                    {
                        action: 'markReadAutoList',                        
                    }
                )
                .then((response) => {
                        this.isSaving = false;
                        this.getEntityCount();

                        if (showResponseMessage !== false) {
                            this.showSuccess('Update successful!');
                            $(".alert-auto-audience-sync").hide();
                        }

                        // setTimeout(function () {
                        //     location.reload();
                        // }, 100);                        
                    }
                )
        },
        maybeCheckPixelCsp() {
            // Auto-check CSP at most once per 24h, and only when the pixel is actually injecting.
            // Skip when there's no script cached (no script = nothing for CSP to block).
            if (!this.scriptCachedDate) {
                return;
            }
            var STALE_AFTER = 86400; // seconds = 24h
            var nowTs = Math.floor(Date.now() / 1000);
            if (!this.cspCheckedAt || (nowTs - this.cspCheckedAt) > STALE_AFTER) {
                this.checkPixelCspNow();
            }
        },
        checkPixelCspNow() {
            // Force a fresh CSP probe, ignoring the 24h cache. Used by the
            // background staleness trigger AND the "Re-check now" link in
            // the warning banner.
            if (this.cspChecking) {
                return;
            }
            this.cspChecking = true;
            var self = this;
            axios
                .post(
                    window.configurationUrl + '&action=checkPixelCspNow',
                    { action: 'ajaxProcessCheckPixelCspNow' }
                )
                .then(function (response) {
                    self.cspChecking = false;
                    if (response && response.data && response.data.hasError === false) {
                        self.cspStatus = response.data.cspStatus;
                        self.cspCheckedAt = response.data.cspCheckedAt;
                    }
                })
                .catch(function () {
                    self.cspChecking = false;
                });
        },
        createCompanyField() {
            if (this.companyFieldWorking) {
                return;
            }
            this.companyFieldWorking = true;
            this.showLoader = true;
            var self = this;
            axios
                .post(
                    window.configurationUrl + '&action=createCompanyMergeField',
                    { action: 'ajaxProcessCreateCompanyMergeField' }
                )
                .then(function (response) {
                    self.companyFieldWorking = false;
                    self.showLoader = false;
                    if (response && response.data) {
                        self.companyFieldStatus = response.data.companyFieldStatus;
                        self.companyFieldError = response.data.hasError ? (response.data.error || 'unknown') : '';
                        if (response.data.hasError) {
                            self.showError(
                                response.data.error === 'merge-field-cap-reached'
                                    ? 'Your audience already has 30 merge fields (Mailchimp limit). Remove one in Mailchimp, then try again.'
                                    : 'Could not create the COMPANY field: ' + (response.data.error || 'unknown error')
                            );
                        } else {
                            self.showSuccess('COMPANY merge field is ready in your audience.');
                        }
                    }
                })
                .catch(function () {
                    self.companyFieldWorking = false;
                    self.showLoader = false;
                    self.showError('Could not reach the server while creating the COMPANY field. Please try again.');
                });
        },
        // pixelLogEnabled is stored as a string in `data` (radio v-model
        // strict-equality compatibility) but the underlying truth is a bool.
        // This helper hides the type acrobatics — templates use it as
        // v-if="isPixelLogEnabled()" without caring about how the radio's
        // raw value is shaped. (Method, not computed: it's cheap and we
        // already keep the rest of the conditional logic in methods.)
        isPixelLogEnabled() {
            return this.pixelLogEnabled === 1
                || this.pixelLogEnabled === '1'
                || this.pixelLogEnabled === true;
        },
        // Decide which Bootstrap alert variant the pixel-status-container
        // should use. Matrix:
        //   pixelAvailable null              → info  (or warning if script issues)
        //   pixel disabled                   → danger (regardless of script state)
        //   pixel enabled, marginal reports  → warning ("checking" — partial signal)
        //   pixel enabled, no issues         → success
        //   pixel enabled, script issues     → warning
        // Script issues = MC.js not loading on storefront, or CSP blocking.
        // Marginal = some recent visitors reported PIXEL_NOT_AVAILABLE but
        // not yet enough distinct IPs to meet quorum and flip the state.
        pixelStatusAlertClass() {
            var hasScriptIssues =
                !(this.listId && this.storeSynced && this.scriptCachedDate)
                || (this.cspStatus
                    && this.cspStatus.csp_present === true
                    && this.cspStatus.compatible === false);
            if (!this.pixelAvailable) {
                return hasScriptIssues ? 'alert-warning' : 'alert-info';
            }
            if (this.pixelAvailable.enabled !== true) {
                return 'alert-danger';
            }
            if (Number(this.pixelAvailable.recent_negative_count) > 0) {
                return 'alert-warning';
            }
            return hasScriptIssues ? 'alert-warning' : 'alert-success';
        },
        // Decide the Bootstrap alert variant for the Advanced settings
        // script-fetched-status-container. Only renders when listId &&
        // storeSynced are truthy (parent v-if guards that), so the only
        // failure modes here are missing scriptCachedDate (danger) or
        // a CSP block (warning).
        scriptStatusAlertClass() {
            if (!(this.listId && this.storeSynced && this.scriptCachedDate)) {
                return 'alert-danger';
            }
            var cspProblem = this.cspStatus
                && this.cspStatus.csp_present === true
                && this.cspStatus.compatible === false;
            return cspProblem ? 'alert-warning' : 'alert-success';
        },
        // Map a technical event_type code to the merchant-friendly label
        // shown in the filter dropdown + log table. Falls back to the raw
        // code if the event isn't in the map (defensive — older bridge
        // versions might emit events not yet listed here).
        pixelEventLabel(eventType) {
            for (var g = 0; g < this.pixelEventGroups.length; g++) {
                var items = this.pixelEventGroups[g].items;
                for (var i = 0; i < items.length; i++) {
                    if (items[i].value === eventType) return items[i].label;
                }
            }
            return eventType;
        },
        // Parse a track-telemetry message (e.g., "api.track() fired |
        // source=initial | product=42/variant=7 | price=29.99 EUR | sku=X |
        // "Title" | payload={...}") into structured parts the template can
        // render as chips + collapsible JSON instead of one long line.
        //
        // Returns null when the message isn't in this format (e.g., it's a
        // verdict-transition or plain text). Returns:
        //   {
        //     prefix: "api.track() fired",
        //     tokens: [{key:'source', value:'initial'}, …, {key:null, value:'"Title"'}],
        //     payload: <parsed JSON object | null>
        //   }
        // — orphan tokens (quoted titles etc.) get key=null and render as
        // muted italic.
        parseTrackTelemetryMessage(message) {
            if (!message || typeof message !== 'string') return null;
            if (message.indexOf(' | ') < 0) return null;
            var parts = message.split(' | ');
            var prefix = parts.shift();
            var tokens = [];
            var payloadJson = null;
            var title = '';
            for (var i = 0; i < parts.length; i++) {
                var p = parts[i];
                var m = p.match(/^([a-zA-Z_][a-zA-Z0-9_]*)=([\s\S]*)$/);
                if (m && m[1] === 'payload') {
                    try { payloadJson = JSON.parse(m[2]); }
                    catch (e) { tokens.push({ key: 'payload', value: '(unparseable JSON)' }); }
                } else if (m) {
                    tokens.push({ key: m[1], value: m[2] });
                } else if (!title && /^".*"$/.test(p)) {
                    // Bridge's formatTrackTelemetry pushes the quoted title as
                    // the only orphan token (key=null) — pull it into a
                    // dedicated field so the template can render it as a
                    // distinct top-line element instead of a generic chip.
                    title = p.replace(/^"|"$/g, '');
                } else {
                    tokens.push({ key: null, value: p });
                }
            }
            return { prefix: prefix, title: title, tokens: tokens, payload: payloadJson };
        },
        // Pretty-print a JSON payload object for the collapsible <details>
        // section. Wraps so the messageColumn doesn't blow out horizontally.
        prettyPrintPayload(obj) {
            try { return JSON.stringify(obj, null, 2); }
            catch (e) { return '(unable to stringify)'; }
        },
        // Parse the optional "[from:STATE] " transition prefix the
        // pixeltelemetry controller prepends to verdict-transition messages.
        // Returns { from: 'PIXEL_AVAILABLE'|'PIXEL_NOT_AVAILABLE'|'UNKNOWN'|null,
        //           body: <descriptive bridge text> }. Bridge messages never
        // legitimately start with "[from:" so the marker is collision-free.
        parsePixelLogMessage(message) {
            if (typeof message !== 'string' || !message) {
                return { from: null, body: message || '' };
            }
            var m = message.match(/^\[from:([A-Z_]+)\]\s?([\s\S]*)$/);
            if (!m) {
                return { from: null, body: message };
            }
            return { from: m[1], body: m[2] };
        },
        // Map a pixel state name to a CSS class for the colored pill.
        // Semantic colors: AVAILABLE = good (green), NOT_AVAILABLE = bad (red),
        // UNKNOWN = neutral (grey). Used by both the "from" and "to" pills.
        pixelStateClass(state) {
            if (state === 'PIXEL_AVAILABLE') return 'pixel-state-pill--available';
            if (state === 'PIXEL_NOT_AVAILABLE') return 'pixel-state-pill--unavailable';
            return 'pixel-state-pill--unknown';
        },
        // Dropdown-style enter animation for the v-show'd pixel-log sections.
        // Pairs with v-on:enter on a <transition v-bind:css="false">. We animate
        // height from 0 to scrollHeight (the actual rendered height of the
        // already-laid-out content) so the section unfolds like an accordion
        // panel. opacity rides along for a softer feel. Cleanup clears the
        // inline styles so the element returns to "natural" sizing — important
        // because the content can change height afterward (e.g. log table
        // gains rows on refresh).
        //
        // Why JS-driven instead of pure CSS: height: auto isn't animatable in
        // CSS. max-height transitions force layout-recalc every frame on tall
        // content (a previous attempt with max-height: 4000px on the log
        // viewer contributed to a Windows-level memory exhaustion + freeze).
        // Reading scrollHeight once and animating to a fixed pixel target lets
        // the browser do simple compositor-level work for the duration.
        pixelSectionEnter(el, done) {
            el.style.height = '0px';
            el.style.opacity = '0';
            el.style.overflow = 'hidden';
            void el.offsetHeight; // force reflow so the start state commits
            var target = el.scrollHeight;
            el.style.transition = 'height 220ms ease, opacity 200ms ease';
            el.style.height = target + 'px';
            el.style.opacity = '1';
            var handler = function (e) {
                if (e.propertyName !== 'height') return;
                el.removeEventListener('transitionend', handler);
                el.style.height = '';
                el.style.opacity = '';
                el.style.overflow = '';
                el.style.transition = '';
                done();
            };
            el.addEventListener('transitionend', handler);
        },
        // Mirror of pixelSectionEnter for the leave direction. Snapshots the
        // current height so the animation has a definite starting point, then
        // collapses to 0.
        pixelSectionLeave(el, done) {
            el.style.height = el.scrollHeight + 'px';
            el.style.opacity = '1';
            el.style.overflow = 'hidden';
            void el.offsetHeight;
            el.style.transition = 'height 220ms ease, opacity 200ms ease';
            el.style.height = '0px';
            el.style.opacity = '0';
            var handler = function (e) {
                if (e.propertyName !== 'height') return;
                el.removeEventListener('transitionend', handler);
                el.style.height = '';
                el.style.opacity = '';
                el.style.overflow = '';
                el.style.transition = '';
                done();
            };
            el.addEventListener('transitionend', handler);
        },
        // Clear every pixel-log filter (both rows) and re-run the query.
        // Bound to the "Reset filters" button in the log viewer.
        resetPixelLogFilters() {
            this.pixelLogFilters.severity = '';
            this.pixelLogFilters.source = '';
            this.pixelLogFilters.event_type = '';
            this.pixelLogFilters.message = '';
            this.pixelLogFilters.ip = '';
            this.pixelLogFilters.from = '';
            this.pixelLogFilters.to = '';
            this.pixelLogOffset = 0;
            this.loadPixelLogs();
        },
        loadPixelLogs() {
            // Fetch a page of pixel log rows matching the current filters.
            // Called on tab visit, refresh-button click, filter change, and pagination.
            if (this.pixelLogLoading) {
                return;
            }
            this.pixelLogLoading = true;
            var self = this;
            axios
                .post(
                    window.configurationUrl + '&action=getPixelLogs',
                    {
                        action: 'ajaxProcessGetPixelLogs',
                        limit: this.pixelLogLimit,
                        offset: this.pixelLogOffset,
                        severity: this.pixelLogFilters.severity,
                        source: this.pixelLogFilters.source,
                        event_type: this.pixelLogFilters.event_type,
                        message: this.pixelLogFilters.message,
                        ip: this.pixelLogFilters.ip,
                        from: this.pixelLogFilters.from,
                        to: this.pixelLogFilters.to
                    }
                )
                .then(function (response) {
                    self.pixelLogLoading = false;
                    if (response && response.data && response.data.hasError === false) {
                        var rows = response.data.rows || [];
                        // PRE-COMPUTE per-row derived fields ONCE here, instead
                        // of calling pixelEventLabel() / parsePixelLogMessage()
                        // from the template (which would re-run them on every
                        // Vue render — for ~50 rows that's 200+ method calls
                        // per re-render, with the message parser doing a regex
                        // and the label resolver doing a nested loop). Doing
                        // it once on load is O(n); the template just reads
                        // the cached values. Critical perf fix — we suspect
                        // the per-render thrash contributed to a system-level
                        // freeze when the Pixel BO tab was first opened.
                        for (var i = 0; i < rows.length; i++) {
                            rows[i]._eventLabel = self.pixelEventLabel(rows[i].event_type);
                            rows[i]._parsed = self.parsePixelLogMessage(rows[i].message);
                            rows[i]._fromPillClass = rows[i]._parsed.from
                                ? self.pixelStateClass(rows[i]._parsed.from) : '';
                            rows[i]._toPillClass = self.pixelStateClass(rows[i].event_type);
                            // Only precompute the track-token structure when the
                            // row is NOT a verdict-transition row (transition
                            // rows have the [from:STATE] prefix and use the
                            // verdict pills instead of token chips).
                            rows[i]._track = rows[i]._parsed.from
                                ? null
                                : self.parseTrackTelemetryMessage(rows[i].message);
                        }
                        self.pixelLogRows = rows;
                        self.pixelLogTotal = response.data.total || 0;
                    } else if (response && response.data && response.data.errorMessage) {
                        self.showError(response.data.errorMessage);
                    }
                })
                .catch(function () {
                    self.pixelLogLoading = false;
                });
        },
        clearPixelLog() {
            if (this.pixelLogClearing) {
                return;
            }
            if (!confirm(
                (window.mailchimp.translations && window.mailchimp.translations.clearPixelLogConfirm)
                || 'Clear all pixel log entries?'
            )) {
                return;
            }
            this.pixelLogClearing = true;
            var self = this;
            axios
                .post(
                    window.configurationUrl + '&action=clearPixelLog',
                    { action: 'ajaxProcessClearPixelLog' }
                )
                .then(function (response) {
                    self.pixelLogClearing = false;
                    if (response && response.data && response.data.hasError === false) {
                        self.showSuccess(response.data.successMessage);
                        self.pixelLogOffset = 0;
                        self.loadPixelLogs();
                    } else if (response && response.data && response.data.errorMessage) {
                        self.showError(response.data.errorMessage);
                    }
                })
                .catch(function () {
                    self.pixelLogClearing = false;
                });
        },
        pixelLogPrevPage() {
            this.pixelLogOffset = Math.max(0, this.pixelLogOffset - this.pixelLogLimit);
            this.loadPixelLogs();
        },
        pixelLogNextPage() {
            if ((this.pixelLogOffset + this.pixelLogLimit) < this.pixelLogTotal) {
                this.pixelLogOffset += this.pixelLogLimit;
                this.loadPixelLogs();
            }
        },
        // Refetch the Mailchimp pixel script from the API, cache it locally,
        // and refresh the reactive state in the BO (scriptCachedDate +
        // pixelAvailable + scriptVerified). Returns the axios promise so
        // callers can chain (e.g. togglePixel).
        syncStoresScript() {
            this.showLoader = true;
            return axios
                .post(
                    window.configurationUrl + '&action=syncStoresScript',
                    {
                        action: 'ajaxProcessSyncStoresScript'
                    }
                )
                .then((response) => {
                        this.showLoader = false;
                        if (response.data.hasError === false) {
                            if (response.data.scriptCachedDate) {
                                this.scriptCachedDate = response.data.scriptCachedDate;
                            }
                            if (response.data.pixelAvailable !== undefined) {
                                this.pixelAvailable = response.data.pixelAvailable;
                            }
                            if (response.data.scriptVerified !== undefined) {
                                this.scriptVerified = response.data.scriptVerified;
                            }
                            this.showSuccess(response.data.successMessage);
                        }
                        else {
                            this.showError(response.data.errorMessage);
                            throw new Error(response.data.errorMessage || 'syncStoresScript failed');
                        }
                    }
                )
        },
        // Merge connection-health fields from an ajax response into the Vue
        // app's reactive state. Any handler whose backend counterpart returns
        // getHealthStatePayload() should call this on both success and error
        // branches so the banner reflects reality without a page refresh.
        //
        // Any confirmed non-empty state from a fresh ajax response un-dismisses
        // the banner. Rationale: dismiss = "acknowledged, hide for now"; a fresh
        // action that hit the broken system deserves reinforced feedback even
        // if the state hasn't changed. Server-side initial render doesn't go
        // through this path — only ajax responses do — so page-load dismisses
        // aren't affected.
        updateHealthStateFromResponse(response) {
            const data = response && response.data;
            if (!data) return;
            if ('foCdnBroken' in data) {
                this.foCdnBroken = !!data.foCdnBroken;
            }
            if ('foCdnBrokenUntil' in data) {
                this.foCdnBrokenUntil = data.foCdnBrokenUntil || 0;
            }
            if ('healthStateFlag' in data) {
                const newFlag = data.healthStateFlag || '';
                if (newFlag) {
                    this.healthBannerDismissedThisSession = false;
                }
                this.healthStateFlag = newFlag;
                this.healthStateSetAt = data.healthStateSetAt || '';
                this.healthStateSource = data.healthStateSource || '';
            }
        },
        // Master Pixel-enable/disable toggle. Two-step:
        //   1. POST enable-pixel / disable-pixel — flips Mailchimp's flag.
        //   2. syncStoresScript() refetches the now-updated bundle and
        //      refreshes pixelAvailable / scriptVerified / scriptCachedDate.
        //   3. Flip the local pixelEnabled toggle once both succeed.
        // No extra debounce flag — the loader overlay covers the tab and the
        // underlying API endpoints are idempotent, so duplicate clicks are safe.
        togglePixel(target) {
            if (this.pixelEnabled === target) return;
            this.showLoader = true;
            const action = target ? 'enablePixel' : 'disablePixel';
            axios
                .post(window.configurationUrl + '&action=' + action)
                .then((response) => {
                    this.showLoader = false;
                    if (response.data.hasError) {
                        this.showError(response.data.errorMessage || 'Pixel toggle failed');
                        throw new Error('toggle action failed');
                    } else {
                        this.showSuccess(response.data.successMessage);
                    }
                    // The enable/disable POST succeeded server-side. Now sync
                    // the local script + state in a single chained refresh.
                    return this.syncStoresScript();
                })
                .then(() => {
                    // Both calls succeeded — align the toggle UI to match.
                    this.pixelEnabled = target;
                })
                .catch(() => {
                    // Inner .then branches already surfaced the error via
                    // showError() — no double-toast here. Leave pixelEnabled
                    // unchanged so the UI honestly reflects "state not
                    // confirmed". Re-clicking is safe (Mailchimp endpoints
                    // are idempotent).
                    this.showLoader = false;
                });
        },
        syncStore() {
            this.showLoader = true;
            axios
                .post(
                    window.configurationUrl + '&action=syncStores', 
                    {
                        action: 'ajaxProcessSyncStores'
                    }
                )
                .then((response) => {
                        this.showLoader = false;
                        if (response.data.hasError === false) {
                            if (response.data.scriptCachedDate) {
                                this.scriptCachedDate = response.data.scriptCachedDate;
                            }
                            this.showSuccess(response.data.successMessage);
                            this.storeSynced = true;
                            $("#content > .bootstrap .alert-info").addClass('hidden');
                        }
                        else {
                            this.showError(response.data.errorMessage);
                            this.storeSynced = false;
                        }
                    }
                )
        },
        getEntityCount() {
            axios
                .post(
                    window.configurationUrl + '&action=getEntityCount',
                    {
                        action: 'getEntityCount',
                    }
                )
                .then((response) => {
                        this.numberOfCartRulesToSync = response.data.cartRules;
                        this.numberOfCustomersToSync = response.data.customers;
                        this.numberOfProductsToSync = response.data.products;
                        this.numberOfOrdersToSync = response.data.orders;
                        this.numberOfNewsletterSubscribersToSync = response.data.newsletterSubscribers;
                    }
                )
        },
        updateStaticContent() {
            axios
                .post(
                    window.configurationUrl + '&action=updateStaticContent',
                    {
                        action: 'ajaxProcessUpdateStaticContent',
                    }
                )
                .then((response) => {
                        this.cronjobLogContent = response.data.cronjobLogContent;
                        this.lastSyncedProductId = response.data.lastSyncedProductId;
                        this.lastSyncedCustomerId = response.data.lastSyncedCustomerId;
                        this.lastSyncedCartRuleId = response.data.lastSyncedCartRuleId;
                        this.lastSyncedOrderId = response.data.lastSyncedOrderId;
                        this.lastSyncedCartId = response.data.lastSyncedCartId;
                        this.lastSyncedNewsletterSubscriberId = response.data.lastSyncedNewsletterSubscriberId;
                        this.lastCronjob = response.data.lastCronjob;
                        this.lastCronjobExecutionTime = response.data.lastCronjobExecutionTime;
                        this.totalJobs = response.data.totalJobs;
                    }
                )
        },
        disconnect() {
            if (confirm("Do you really want to log out?")) {
                axios
                    .post(
                        window.configurationUrl + '&action=disconnect',
                        {
                            action: 'disconnect',
                        }
                    )
                    .then((response) => {
                            this.accountInfo = response.data.accountInfo;
                            this.validApiKey = response.data.validApiKey;
                            
                            $('#content > .bootstrap .alert-info').addClass('hidden');
                        }
                    )
            }
        },
        oauthStart() {
            let width = 500;
            let height = 900;
            let left = (screen.width / 2) - (width / 2);
            let top = (screen.height / 2) - (height / 2);

            window.open(
                window.middlewareUrlUpgraded,
                "McAuthMiddleware",
                'width=' + width + ', height=' + height + ', top=' + top + ', left=' + left + ",resizable=yes,scrollbars=yes,toolbar=yes,menubar=no,location=no,directories=no, status=yes"
            );
        },
        /**
         * Route the merchant from a connection-health banner to the right
         * BO surface. State-aware:
         *   - SITE_MISSING / STORE_MISSING / SITE_AND_STORE_MISSING /
         *     LIST_MISSING → Advanced settings tab, scroll to the
         *     "Delete e-commerce data" button + brief pulse-highlight so
         *     the merchant can't miss it. Delete flips MAILCHIMP_STORE_SYNCED
         *     to false — makes the Wizard's "Initialize connection" flow
         *     treat the module as a fresh install (recreates connected_site
         *     + ecommerce/store + list mapping).
         *   - ACCOUNT_INVALID → Account info tab (where the Log out button
         *     lives). Merchant logs out + re-OAuths + re-inits.
         *   - MAILCHIMP_DOWN → no button rendered; nothing to do here.
         */
        healthRecovery(state) {
            if (state === 'ACCOUNT_INVALID') {
                this.setCurrentPage('accountInfo');
                return;
            }
            // All other broken states: navigate to Advanced settings and
            // pulse-highlight the delete-ecommerce button so the merchant
            // spots it under the (correctly alarming, but here-legitimate)
            // "no going back" warning.
            this.setCurrentPage('advanced-settings');
            this.$nextTick(() => {
                const el = document.querySelector('.delete-ecommerce-data');
                if (!el) return;
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                el.classList.add('mc-health-pulse');
                setTimeout(() => el.classList.remove('mc-health-pulse'), 3000);
            });
        },

        /**
         * Hide the health banner for the rest of THIS BO session (per-tab).
         * Reappears on next full page load — banner surfaces a persistent
         * state, not a dismissable-forever notification. Merchant just gets
         * to breathe if they're mid-task in another tab.
         */
        dismissHealthBanner() {
            this.healthBannerDismissedThisSession = true;
        },

        resetFoCdnHealthCheck() {
            this.showLoader = true;
            axios
                .post(
                    window.configurationUrl + '&action=resetFoCdnHealth',
                    { action: 'ajaxProcessResetFoCdnHealth' }
                )
                .then((response) => {
                    if (response.data.hasError === false) {
                        this.showSuccess(response.data.successMessage);
                    } else {
                        this.showError(response.data.errorMessage);
                    }
                })
                .catch(() => {
                    this.showError('Failed to reset CDN health check');
                })
                .finally(() => {
                    this.showLoader = false;
                });
        },

        setCurrentPage(currentPage, scrollToId) {
            this.currentPage = currentPage;
            const url = new URL(window.location);
            url.hash = '#' + currentPage;
            window.history.pushState({}, '', url);

            this.updateStaticContent();

            // Optional anchor scroll for cross-tab deep-links (e.g. clicking
            // "Pixel tab" inside the FAQ should land at the consent toggle,
            // not at the top of the Pixel tab). nextTick lets Vue finish the
            // tab visibility swap first. scrollIntoView honours the target's
            // scroll-margin-top — driven by --bo-fixed-header-offset,
            // measured at mount / on resize by updateBOHeaderOffset().
            //
            // If the scroll target is an FAQ accordion header (closed by
            // default), we auto-expand its body first AND wait for the
            // Bootstrap collapse animation to settle (`shown.bs.collapse`)
            // before scrolling — otherwise scrollIntoView computes the
            // target rect mid-animation and lands at the wrong position,
            // with the error magnitude depending on which OTHER items are
            // currently open (different page layout → different absolute
            // position for our target).
            if (scrollToId) {
                this.$nextTick(() => {
                    const el = document.getElementById(scrollToId);
                    if (!el) return;
                    // Explicit scrollTo with our measured BO-header offset.
                    // More reliable than scrollIntoView+scroll-margin-top —
                    // the latter has known smooth-scroll quirks where the
                    // CSS margin isn't honoured mid-animation. Re-measures
                    // the offset right before scrolling in case the BO
                    // chrome changed since mount.
                    const doScroll = () => {
                        this.updateBOHeaderOffset();
                        const cssOffset = getComputedStyle(document.documentElement)
                            .getPropertyValue('--bo-fixed-header-offset');
                        const offset = parseInt(cssOffset, 10) || 96;
                        const targetY = el.getBoundingClientRect().top + window.scrollY - offset;
                        window.scrollTo({ top: Math.max(0, targetY), behavior: 'smooth' });
                    };
                    if (scrollToId.indexOf('accordion-heading') !== 0) {
                        doScroll();
                        return;
                    }
                    const bodyId = scrollToId.replace('accordion-heading', 'accordion-body-container');
                    const body = document.getElementById(bodyId);
                    if (!body) {
                        doScroll();
                        return;
                    }
                    // Already open → no animation to wait for, scroll now.
                    if (body.classList.contains('in')) {
                        doScroll();
                        return;
                    }
                    const $ = window.jQuery || window.$;
                    if ($ && typeof $.fn.collapse === 'function') {
                        // Wait for Bootstrap-3 collapse plugin's "shown" event
                        // (fires after CSS transition finishes, ~350ms). .one()
                        // auto-unbinds after first trigger.
                        $(body).one('shown.bs.collapse', doScroll).collapse('show');
                    } else {
                        // Vanilla fallback — Bootstrap CSS transition is still
                        // applied via the .in class swap, so delay scroll by
                        // the typical 400ms (350ms animation + buffer).
                        body.classList.add('in');
                        el.classList.remove('collapsed');
                        setTimeout(doScroll, 400);
                    }
                    el.setAttribute('aria-expanded', 'true');
                    // At least one item is now open → flip the FAQ-heading
                    // button mutex so "Collapse all" shows (no longer
                    // "Expand all"), giving the merchant a one-click way
                    // to close what we just opened.
                    this.allFaqsExpanded = true;
                });
            }
        },
        /**
         * Measure the BO's fixed top-bar height and publish it to the
         * --bo-fixed-header-offset CSS variable. Used by scroll-margin-top
         * on cross-tab deep-link targets so they land BELOW the fixed header
         * instead of being clipped behind it.
         *
         * Iterates known PS BO top-bar selectors (varies across 1.7 / 8 / 9),
         * returns the first measurable fixed/sticky one at top:0. Adds 16px
         * of breathing room. Falls back to 80px if nothing matches — close
         * enough to the typical BO chrome that the scroll target is at
         * worst slightly crowded against the bar.
         */
        /**
         * Bulk open all FAQ accordion items. Prefers Bootstrap's collapse
         * plugin via jQuery (available in PS BO) for the proper aria-expanded
         * toggling + transitions; falls back to direct class manipulation if
         * jQuery isn't there.
         */
        expandAllFaqs() {
            const $ = window.jQuery || window.$;
            if ($ && typeof $.fn.collapse === 'function') {
                $('#faq-accordions-container .accordion-collapse').collapse('show');
                this.allFaqsExpanded = true;
                return;
            }
            // Vanilla fallback below also sets allFaqsExpanded=true at end.
            // Vanilla fallback — mirror the Bootstrap-3 collapse plugin's
            // class management (.in on the body, .collapsed removed from
            // the header trigger, aria-expanded flipped).
            document.querySelectorAll('#faq-accordions-container .accordion-collapse').forEach(el => {
                el.classList.add('in');
            });
            document.querySelectorAll('#faq-accordions-container [data-toggle="collapse"]').forEach(h => {
                h.classList.remove('collapsed');
                h.setAttribute('aria-expanded', 'true');
            });
            this.allFaqsExpanded = true;
        },
        /**
         * Bulk close all FAQ accordion items. Symmetric to expandAllFaqs.
         */
        collapseAllFaqs() {
            const $ = window.jQuery || window.$;
            if ($ && typeof $.fn.collapse === 'function') {
                $('#faq-accordions-container .accordion-collapse').collapse('hide');
                this.allFaqsExpanded = false;
                return;
            }
            // Vanilla fallback below also sets allFaqsExpanded=false at end.
            document.querySelectorAll('#faq-accordions-container .accordion-collapse').forEach(el => {
                el.classList.remove('in');
            });
            document.querySelectorAll('#faq-accordions-container [data-toggle="collapse"]').forEach(h => {
                h.classList.add('collapsed');
                h.setAttribute('aria-expanded', 'false');
            });
            this.allFaqsExpanded = false;
        },
        updateBOHeaderOffset() {
            // Walk a curated list of known PS BO top-chrome selectors.
            // For each match that is position:fixed|sticky and anchored
            // near the top (rect.top within 0–200px), keep the deepest
            // rect.bottom as the offset. Using rect.bottom (not rect.height)
            // captures any siblings stacked above the matched element,
            // even if those siblings don't match our selectors directly.
            const selectors = [
                '.page-head',           // PS 9.x — breadcrumb + page-title bar
                'nav.main-header',      // PS 8.x AdminLTE
                'header.main-header',
                '#header_infos',        // PS 1.7.x
                'header.header',        // generic catch-all
            ];
            let bottomY = 0;
            selectors.forEach(sel => {
                document.querySelectorAll(sel).forEach(el => {
                    try {
                        const pos = window.getComputedStyle(el).position;
                        if (pos !== 'fixed' && pos !== 'sticky') return;
                        const rect = el.getBoundingClientRect();
                        if (rect.height < 20) return;
                        if (rect.top > 200) return; // sanity: must be in top region
                        if (rect.bottom > bottomY) bottomY = rect.bottom;
                    } catch (e) { /* getComputedStyle on weird nodes — ignore */ }
                });
            });
            const offset = (bottomY > 0 ? bottomY : 80) + 16;
            document.documentElement.style.setProperty('--bo-fixed-header-offset', offset + 'px');
        },
        getCurrentPage(defaultValue) {
            let validApiKey = window.mailchimp.validApiKey;
            let listId = window.mailchimp.listId;
            let storeSynced = window.mailchimp.storeSynced;
            let selectedPreset = window.mailchimp.selectedPreset;
            let lastCronjob = window.mailchimp.lastCronjob;
            if (!defaultValue) {
                validApiKey = this.validApiKey;
                listId = this.listId;
                storeSynced = this.storeSynced;
                selectedPreset = this.selectedPreset;
                lastCronjob = this.lastCronjob;
            }
            let currentPage = window.location.hash.slice(1);
            if (validApiKey && (!selectedPreset || (!currentPage && !(listId && storeSynced && selectedPreset && lastCronjob)))) {
                window.location.hash = 'presets';
                return 'presets';
            }
            if (!currentPage) {
                currentPage = 'presets';
            }
            return currentPage;
        },
        isPanelStepAvailable(step) {
            switch(step) {
                case 'sync-store':
                    return true;
                case 'select-preset':
                    return this.selectedPreset || (this.validApiKey && this.listId && this.storeSynced && !this.selectedPreset);
                case 'setup-cronjob':
                    return this.validApiKey && this.listId && this.storeSynced && this.selectedPreset && this.cronjobBasedSync;
                default:
                    return false;
            }
        },
        isPanelStepActive(step) {
            switch(step) {
                case 'sync-store':
                    return !(this.validApiKey && this.listId && this.storeSynced);
                case 'select-preset':
                    return (this.validApiKey && this.listId && this.storeSynced && (!this.selectedPreset || (this.selectedPreset && !this.cronjobBasedSync))) || (this.validApiKey && this.listId && this.storeSynced && this.lastCronjob);
                case 'setup-cronjob':
                    return this.validApiKey && this.listId && this.storeSynced && this.selectedPreset && this.cronjobBasedSync && !this.lastCronjob;
                default:
                    return false;
            }
        },
        isPanelStepDone(step) {
            switch(step) {
                case 'sync-store':
                    return this.validApiKey && this.listId && this.storeSynced;
                case 'select-preset':
                    return this.validApiKey && this.selectedPreset;
                case 'setup-cronjob':
                    return this.validApiKey && this.listId && this.storeSynced && this.selectedPreset && this.lastCronjob;
                default:
                    return false;
            }
        },
        selectPanelStep(event) {
            if (event.target.classList.contains('panel-step') || event.target.closest('.panel-step')) {
                let currentStep = event.target.classList.contains('panel-step') ? event.target : event.target.closest('.panel-step');
                let currentStepContent = currentStep.dataset.stepTarget;
                // if (!currentStep) {
                //     currentStep = event.target.closest('.panel-step').dataset.stepTarget;
                // }
                if ($(currentStep).length && $('.' + currentStepContent).length) {
                    $(currentStep).addClass('active').siblings('.panel-step').removeClass('active');
                    $('.' + currentStepContent).addClass('active').siblings('.step-content').removeClass('active');
                }
            }
        },
        selectPreset(event) {
            if (event.target.closest('.panel') && event.target.closest('.panel').dataset.preset) {
                let currentPreset = event.target.closest('.panel').dataset.preset;
                let selectedPresetDefaults = this.definedPresets[currentPreset]['config-values'];

                // check if all keys in the selectedPresetDefaults object are present in the current data (this) object object:
                if (selectedPresetDefaults && Object.keys(selectedPresetDefaults).every(key => key in this)) {
                    // prevent running save function for every changed option
                    this.preventSave = true;
                    // update current data values:
                    Object.keys(selectedPresetDefaults).forEach(key => {
                        // if (key in this) {
                            this[key] = selectedPresetDefaults[key];
                        // }
                    });
                    // update selected preset value:
                    this.selectedPreset = currentPreset;
                    // wait for watchers to finish
                    setTimeout(function (_this) {
                        _this.preventSave = false;
                        if (_this.selectedPreset == 'custom') {
                            // when the selected preset is custom, only delete existing jobs, they would have to add them manually
                            _this.saveSettings('selectPreset', true, () => {_this.clearJobs('selectPreset', true)});
                        } else {
                            // else delete existing jobs, and add them based on the selected preset automatically
                            _this.saveSettings('selectPreset', true, () => {_this.clearJobs('selectPreset', true, () => {_this.pushSetupJobsToQueue('selectPreset', true)})});
                        }
                    }, 200, this);
                } else {
                    this.showError('Incorrect preset-defaults configuration!');
                }
            }
        },
        // set the current preset based on the current configuration data
        manageSelectedPreset() {
            let determinedPreset = this.determinePreset();
            if (this.selectedPreset && this.selectedPreset != determinedPreset) {
                // prevent running save function when changing the preset by configuration data
                this.preventSave = true;
                this.selectedPreset = determinedPreset;
                setTimeout(function (_this) {
                    _this.preventSave = false;
                    // update selected preset value:
                    _this.saveSettings('selectedPreset', false);
                }, 100, this);
            }
        },
        determinePreset() {
            const definedPresets = this.definedPresets;

            for (const key in definedPresets) {
                const configValues = definedPresets[key]['config-values'];
                const isMatch = Object.keys(configValues).every((configKey) => {
                    const dataValue = this[configKey];
                    const configValue = configValues[configKey];

                    if (Array.isArray(configValue)) {
                        // Check if both arrays are deeply equal
                        return Array.isArray(dataValue) && JSON.stringify(dataValue) === JSON.stringify(configValue);
                    }

                    // Check if primitive values match
                    return dataValue === configValue;
                });

                if (isMatch) {
                    // console.log(key);
                    if (key == 'free' && this.selectedPreset != 'free' && !this.isFreePresetAvailable()) {
                        return 'custom';
                    }
                    return key; // Return the matching main key
                }
            }
            // console.log('custom');

            return 'custom'; // Return 'custom' if no match is found
        },
        isFreePresetAvailable() {
            return this.accountInfo && this.accountInfo.total_subscribers && this.accountInfo.total_subscribers <= this.definedPresets['free']['list-member-limit'];
        },
        dropDownToggle(event) {
            let dropdownItem = event.target.closest('[aria-expanded]');
            if (dropdownItem) {
                dropdownItem.setAttribute("aria-expanded", !(dropdownItem.getAttribute("aria-expanded") == 'true'));
            }
        },
        getPromoData() {
            return {
                promoId: this.currentPromoId,
                promoName: $("#PROMO_NAME").val().trim(),
                promoPrefix: $("#PROMO_PREFIX").val().trim(),
                promoSuffix: $("#PROMO_SUFFIX").val().trim(),
                promoExpiration: $("#PROMO_EXPIRATION").val().trim(),
                // promoCampaign: $("#PROMO_CAMPAIN").val().trim(),
                promoReduction: this.validatePromoReduction("PROMO_REDUCTION"),
                promoStatus: +$("[name='PROMO_STATUS']:checked").val(),
                promoReductionType: +$("[name='PROMO_REDUCTION_TYPE']:checked").val(),
            };
        },
        async addNewPromo() {
            const promoData = this.getPromoData();

            const validationErrors = this.validatePromoData(promoData);
            if (validationErrors.length) {
                this.showError(validationErrors.join(' '));
                return;
            }

            await axios
                .post(
                    window.configurationUrl + '&action=addNewPromo',
                    {
                        action: 'addNewPromo',
                        data: promoData
                    }
                )
                .then(async ({ data }) => {
                    if (data.success) {
                        this.currentPromoId = data.promo.id;
                        localStorage.setItem('currentPromoId', this.currentPromoId);
                        this.showSuccess(data.message);
                        if (data.promo.status == 1) {
                            this.showGeneratePromoElements();
                            await this.generateCodes(this.currentPromoId);
                            if (!$('#promoTable tbody tr[data-id=' + this.currentPromoId + ']').length) {
                                window.location.reload();
                            }
                        }
                    } else {
                        this.showError(data.message);
                    }
                })
                .catch((err) => {
                    this.showError('An error occurred while processing your request.' + err.response.data.detail);
                });
        },
        validatePromoReduction(promoReductionId) {
            let promoReductionValue = $("#" + promoReductionId).val().trim();
            let fixedValue = (isNaN(parseFloat(promoReductionValue)) ? '' : Math.abs(parseFloat(promoReductionValue)));
            if (promoReductionValue !== fixedValue) {
                $("#" + promoReductionId).val(fixedValue);
            }
            return fixedValue;
        },
        validatePromoData(data) {
            const errors = [];
            let target = false;
            if (data.target && data.target.id) {
                target = '#' + data.target.id;
            }
            if (!data || target) {
                data = this.getPromoData();
            }
            if (data) {
                if (!data.promoName && (!target || (target && target == '#PROMO_NAME'))) {
                    errors.push('Please enter a name for the new promo code!');
                    $("#PROMO_NAME").closest('.form-group').removeClass('has-success').addClass('has-error');
                } else {
                    if (data.promoName) {
                        $("#PROMO_NAME").closest('.form-group').removeClass('has-error');
                    }
                }
                if (!data.promoPrefix && (!target || (target && target == '#PROMO_PREFIX'))) {
                    errors.push('Please enter a prefix for the new promo code!');
                    $("#PROMO_PREFIX").closest('.form-group').removeClass('has-success').addClass('has-error');
                } else {
                    if (data.promoPrefix) {
                        $("#PROMO_PREFIX").closest('.form-group').removeClass('has-error');
                    }
                }
                if (!data.promoExpiration && (!target || (target && target == '#PROMO_EXPIRATION'))) {
                    errors.push('Please enter an expiration date for the new promo code!');
                    $("#PROMO_EXPIRATION").closest('.form-group').removeClass('has-success').addClass('has-error');
                } else {
                    if (data.promoExpiration) {
                        $("#PROMO_EXPIRATION").closest('.form-group').removeClass('has-error');
                    }
                }
                if (!(data.promoReduction && isFinite(data.promoReduction)) && (!target || (target && target == '#PROMO_REDUCTION'))) {
                    errors.push('Please enter a valid reduction value!');
                    $("#PROMO_REDUCTION").closest('.form-group').removeClass('has-success').addClass('has-error');
                } else {
                    if (data.promoReduction && isFinite(data.promoReduction)) {
                        $("#PROMO_REDUCTION").closest('.form-group').removeClass('has-error');
                    }
                }
                // $("#PROMO_SUFFIX").closest('.form-group').addClass('has-success').removeClass('has-error');
                // $("[name='PROMO_REDUCTION_TYPE']").closest('.form-group').addClass('has-success').removeClass('has-error');
                // $("[name='PROMO_STATUS']").closest('.form-group').addClass('has-success').removeClass('has-error');
            }
            return errors;
        },
        editPromo(promoId) {
            // Save the promoId to localStorage
            localStorage.setItem('currentPromoId', promoId);

            axios
                .post(window.configurationUrl + '&action=getPromoDetails', { id: promoId })
                .then(({ data }) => {
                    if (data.success) {
                        const promo = data.data;
                        const count = data.count;
                        const progress = Math.round(((count.totalAll - count.remain) / count.totalAll) * 100);
                        this.populatePromoForm(promo);
                        $('#progressBar').css('width', progress + '%').attr('aria-valuenow', progress).html(progress + '%');
                        $('#totalCount').html(count.total);
                        this.setCurrentPage('addNewPromo');

                        if (promo.status == 1) {
                            this.showGeneratePromoElements();
                        }
                        if (progress > 0 && progress < 100) {
                            $('.col-generate-codes').show();
                        }
                    } else {
                        this.showError(data.message);
                    }
                })
                .catch(() => {
                    this.showError('An error occurred while fetching promo stats.');
                });
        },
        viewPromo(promoId) {
            axios
                .post(window.configurationUrl + '&action=getPromoCodes', { id: promoId })
                .then(({ data }) => {
                    if (data.success) {
                        this.promoCodes = data.codes;
                        this.setCurrentPage('promocodes');
                        const table = $('#promocodesTable');
                        if ($.fn.dataTable.isDataTable(table)) {
                            table.DataTable().destroy(); // Destroy the existing DataTable
                        }

                        setTimeout(function () {
                            // Reinitialize the DataTable after a short delay for previous DataTable destroy
                            table.DataTable({
                                "paging": true,
                                "searching": true,
                                "ordering": true,
                                "order": [[0, 'desc']],
                                "columnDefs": [
                                    { "orderable": false, "targets": [0, 5] }
                                ]
                            });
                        }, 500);
                    } else {
                        this.showError(data.message);
                    }
                })
                .catch(() => {
                    this.showError('An error occurred while fetching promo codes.');
                });
        },
        deletePromo(promoId) {
            if (confirm("Do you really want to delete? This will also delete the Merge Field from Mailchimp.")) {
            axios
                .post(window.configurationUrl + '&action=deletePromo', { id: promoId })
                .then(({ data }) => {
                    if (data.success) {
                        this.showSuccess(data.message);
                        this.setCurrentPage('promo');
                    } else {
                        this.showError(data.message);
                    }
                    setTimeout(function () {
                        location.reload();
                    }, 1500);
                })
                .catch(() => {
                    this.showError('An error occurred while fetching promo.');
                });
            }
        },
        deletePromocode(promocode) {
            if (confirm("Do you really want to delete?")) {
            axios
                .post(window.configurationUrl + '&action=deletePromocode', { id: promocode })
                .then(({ data }) => {
                    if (data.success) {
                        this.showSuccess(data.message);
                        this.setCurrentPage('promo');
                    } else {
                        this.showError(data.message);
                    }
                    setTimeout(function () {
                        location.reload();
                    }, 1500);
                })
                .catch(() => {
                    this.showError('An error occurred while fetching promo.');
                });
            }
        },
        deleteSelectedPromocodes() {
            if (
                this.selectedPromoCodes.length > 0 &&
                confirm("Do you really want to delete the selected promocodes?")
            ) {
                axios
                    .post(window.configurationUrl + '&action=deleteBulkPromocodes', { ids: this.selectedPromoCodes })
                    .then(({ data }) => {
                        if (data.success) {
                            this.showSuccess(data.message);
                            this.setCurrentPage('promo');
                        } else {
                            this.showError(data.message);
                        }
                        setTimeout(function () {
                            location.reload();
                        }, 1500);
                    })
                    .catch(() => {
                        this.showError('An error occurred while deleting promocodes.');
                    });
            }
        },
        toggleSelectAll() {
            if (this.selectAll) {
                this.selectedPromoCodes = this.promoCodes.map((code) => code.id);
            } else {
                this.selectedPromoCodes = [];
            }
        },
        updatePromo() {
            const promoData = this.getPromoData();

            const validationErrors = this.validatePromoData(promoData);
            if (validationErrors.length) {
                this.showError(validationErrors.join(' '));
                return;
            }

            axios
                .post(window.configurationUrl + '&action=updatePromo', { data: promoData })
                .then(async ({ data }) => {
                    if (data.success) {
                        this.currentPromoId = data.promo.id;
                        localStorage.setItem('currentPromoId', this.currentPromoId);
                        this.showSuccess(data.message);
                        // this.setCurrentPage('promo');
                        // window.location.reload();
                        if (promoData.promoStatus == 1) {
                            this.showGeneratePromoElements();
                            await this.generateCodes(this.currentPromoId);
                        }
                        //if (!$('#promoTable tbody tr[data-id=' + this.currentPromoId + ']').length) {
                            window.location.reload();
                        //}
                    } else {
                        this.showError(data.message);
                    }
                })
                .catch(() => {
                    this.showError('An error occurred while updating promo.');
                });
        },
        populatePromoForm(promo) {
            if (promo === 'new') {
                localStorage.removeItem('currentPromoId');
                this.hideGeneratePromoElements();
            }
            if (!promo || promo === 'new') {
                promo = {
                    id: 0,
                    name: '',
                    prefix: '',
                    suffix: '',
                    end_date: '',
                    campaign_id: '',
                    reduction: '',
                    status: 0,
                    reduction_type: 0
                }
                $('#progressBar').css('width', '0%').attr('aria-valuenow', '0').html('0%');
                $('#totalCount').html(0);
            }
            $("#PROMO_NAME").val(promo.name);
            $("#PROMO_PREFIX").val(promo.prefix);
            $("#PROMO_SUFFIX").val(promo.suffix);
            $("#PROMO_EXPIRATION").val(promo.end_date);
            // $("#PROMO_CAMPAIN").val(promo.campaign_id);
            $("#PROMO_REDUCTION").val(promo.reduction);
            $(`[name='PROMO_STATUS'][value="${promo.status}"]`).prop('checked', true);
            $(`[name='PROMO_REDUCTION_TYPE'][value="${promo.reduction_type}"]`).prop('checked', true);
            this.currentPromoId = promo.id;
            if (promo.status == 0) {
                this.hideGeneratePromoElements();
            }
        },
        showGeneratePromoElements() {
            $('.generate-promo-codes-hr, .generate-promo-codes').show();
        },
        hideGeneratePromoElements() {
            $('.generate-promo-codes-hr, .generate-promo-codes').hide();
        },
        async generateCodes(promoId) {
            let offset = 0;
            const progressBar = document.getElementById('progressBar');
            const totalCountDisplay = document.getElementById('totalCount');

            if (!promoId) {
                await this.addNewPromo();
                promoId = this.currentPromoId;
            }

            const updateProgressBar = (progress) => {
                progressBar.style.width = `${progress}%`;
                progressBar.setAttribute('aria-valuenow', progress);
                progressBar.innerText = `${progress}%`;
            };

            let totalEmails = 0;

            while (true) {
                try {
                    const response = await axios.post(window.configurationUrl + '&action=generateCodes', {
                        id: promoId,
                        offset: offset,
                    });

                    const { success, message, generated_count, remaining, total, next_offset } = response.data;

                    if (!success) {
                        this.showError(`Error: ${message}`);
                        break;
                    }

                    if (!totalEmails) {
                        totalEmails = total;
                    }

                    offset = next_offset;

                    // Update progress bar
                    const progress = Math.min(100, Math.round(((totalEmails - remaining) / totalEmails) * 100));
                    updateProgressBar(progress);

                    if (remaining <= 0) {
                        this.showSuccess(message);
                        updateProgressBar(100);
                        break;
                    }
                    if (!totalEmails) {
                        totalCountDisplay.innerText = totalEmails;
                    }

                    this.showSuccess(`${message}. ${generated_count} codes generated in this batch. Remaining: ${remaining}`);
                } catch (error) {
                    console.error('Error:', error);
                    this.showError('A critical error occurred during code generation.');
                    break;
                }
            }
        },
        syncPromo(promoId) {

            // if(!promoId) {
            //     await this.addNewPromo();
            //     promoId = this.currentPromoId;
            // }

            axios
                .post(
                    window.configurationUrl + '&action=syncPromo', 
                    {
                        action: 'ajaxProcessSyncPromo',
                        promoId: promoId,
                    }
                )
                .then((response) => {
                        // this.showLoader = false;
                        if (response.data.success === true) {
                            this.showSuccess(response.data.message);
                            // this.storeSynced = true;
                            // $("#content > .bootstrap .alert-info").addClass('hidden');
                        }
                        else {
                            this.showError(response.data.message);
                            // this.storeSynced = false;
                        }
                    }
                )
        },
        arrayEquals(a, b) {
            if (a.length !== b.length) return false;
            const uniqueValues = new Set([...a, ...b]);
            for (const v of uniqueValues) {
                const aCount = a.filter(e => e === v).length;
                const bCount = b.filter(e => e === v).length;
                if (aCount !== bCount) return false;
            }
            return true;
        },
        getUniqueConfigKeys(data) {
            const keys = new Set();
            // Iterate through each top-level key
            Object.values(data).forEach(section => {
                if (section['config-values']) {
                    // Add the keys from 'config-values' to the set
                    Object.keys(section['config-values']).forEach(key => keys.add(key));
                }
            });

            return Array.from(keys); // Convert the Set to an Array
        },
        pushSetupJobsToQueue(reason, showResponseMessage) {
			this.isSaving = true;
            let requests = [];
            // stores
            // requests.push(axios.post(window.configurationUrl + '&action=syncStores', {action: 'ajaxProcessSyncStores'}))
            let jobsAddedToQueue = 0;
            if (this.syncProducts) {
                requests.push(axios.post(window.configurationUrl + '&action=addProductsToQueue', {action: 'ajaxProcessAddProductsToQueue'}));
                
                // filling the specific price table with initial values
                requests.push(axios.post(window.configurationUrl + '&action=initializeSpecificPrices', {action: 'ajaxProcessSpecificPrices'}));
                jobsAddedToQueue += this.numberOfProductsToSync;
            }
            if (this.syncCustomers) {
                requests.push(axios.post(window.configurationUrl + '&action=addCustomersToQueue', {action: 'ajaxProcessAddCustomersToQueue'}));
                jobsAddedToQueue += this.numberOfCustomersToSync;
            }
            if (this.syncOrders) {
                requests.push(axios.post(window.configurationUrl + '&action=addOrdersToQueue', {action: 'ajaxProcessAddOrdersToQueue'}));
                jobsAddedToQueue += this.numberOfOrdersToSync;
            }
            if (this.syncCartRules) {
                requests.push(axios.post(window.configurationUrl + '&action=addCartRulesToQueue', {action: 'ajaxProcessAddCartRulesToQueue'}));
                jobsAddedToQueue += this.numberOfCartRulesToSync;
            }
            if (this.syncNewsletterSubscribers) {
                requests.push(axios.post(window.configurationUrl + '&action=addNewsletterSubscribersToQueue', {action: 'ajaxProcessAddNewsletterSubscribersToQueue'}));
                jobsAddedToQueue += this.numberOfNewsletterSubscribersToSync;
            }

            axios
                .all(requests)
                .then(
                    axios.spread((...responses) => {
                        console.log(responses);
						this.jobsAddedToQueue = true;
						this.isSaving = false;
                        if (this.syncProducts) this.queuedTypes.products = true;
                        if (this.syncCustomers) this.queuedTypes.customers = true;
                        if (this.syncCartRules) this.queuedTypes.cartRules = true;
                        if (this.syncOrders) this.queuedTypes.orders = true;
                        if (this.syncNewsletterSubscribers) this.queuedTypes.newsletterSubscribers = true;
                        if (showResponseMessage) {
                            if (jobsAddedToQueue) {
                                this.showSuccess('Jobs successfully added to queue!');
                                this.totalJobs = jobsAddedToQueue;
                            } else {
                                console.log('No jobs have been added to queue!');
                            }
                        }
                    })
                )
                .catch(errors => {
                    // react on errors.
                    console.error(errors);
					this.isSaving = false;
                    if (showResponseMessage) {
                        this.showSuccess('Something went wrong adding jobs to queue!');
                    }
                });
        },
        pushSingleTypeToQueue(type) {
            const typeConfig = {
                products: {
                    loadingKey: 'queueLoadingProducts',
                    countKey: 'numberOfProductsToSync',
                    actions: [
                        { action: 'addProductsToQueue', ajaxAction: 'ajaxProcessAddProductsToQueue' },
                        { action: 'initializeSpecificPrices', ajaxAction: 'ajaxProcessSpecificPrices' }
                    ],
                    label: 'Product'
                },
                customers: {
                    loadingKey: 'queueLoadingCustomers',
                    countKey: 'numberOfCustomersToSync',
                    actions: [
                        { action: 'addCustomersToQueue', ajaxAction: 'ajaxProcessAddCustomersToQueue' }
                    ],
                    label: 'Customer'
                },
                cartRules: {
                    loadingKey: 'queueLoadingCartRules',
                    countKey: 'numberOfCartRulesToSync',
                    actions: [
                        { action: 'addCartRulesToQueue', ajaxAction: 'ajaxProcessAddCartRulesToQueue' }
                    ],
                    label: 'Cart rule'
                },
                orders: {
                    loadingKey: 'queueLoadingOrders',
                    countKey: 'numberOfOrdersToSync',
                    actions: [
                        { action: 'addOrdersToQueue', ajaxAction: 'ajaxProcessAddOrdersToQueue' }
                    ],
                    label: 'Order'
                },
                newsletterSubscribers: {
                    loadingKey: 'queueLoadingNewsletterSubscribers',
                    countKey: 'numberOfNewsletterSubscribersToSync',
                    actions: [
                        { action: 'addNewsletterSubscribersToQueue', ajaxAction: 'ajaxProcessAddNewsletterSubscribersToQueue' }
                    ],
                    label: 'Newsletter subscriber'
                },
            };

            const config = typeConfig[type];
            if (!config) return;

            this[config.loadingKey] = true;

            let requests = config.actions.map(a =>
                axios.post(window.configurationUrl + '&action=' + a.action, { action: a.ajaxAction })
            );

            axios.all(requests)
                .then(axios.spread((...responses) => {
                    this[config.loadingKey] = false;
                    this.queuedTypes[type] = true;
                    this.jobsAddedToQueue = true;
                    this.totalJobs = (this.totalJobs || 0) + this[config.countKey];
                    this.showSuccess(config.label + ' jobs added to queue!');
                }))
                .catch(errors => {
                    console.error(errors);
                    this[config.loadingKey] = false;
                    this.showError('Failed to add ' + config.label.toLowerCase() + ' jobs to queue.');
                });
        },
        clearJobs(reason, showResponseMessage, callbackOnSuccess, callbackOnError) {
            if (this.totalJobs && this.totalJobs > 0) {
                this.isSaving = true;
                axios
                    .post(
                        window.queueUrl + '&action=clearJobs'
                    )
                    .catch((error) => {
                        this.isSaving = false;
                        if (error.response) {
                            this.showError(error.response.data)
                        } else if (error.request) {
                            this.showError(error.response.data)
                        } else {
                            this.showError(error.message)
                        }
                        if (showResponseMessage) {
                            this.showSuccess('Something went wrong clearing previous jobs!');
                        }
                    })
                    .then(response => {
                        this.isSaving = false;
                        if (showResponseMessage) {
                            if (reason == 'selectPreset') {
                                this.showSuccess('Previous jobs deleted successfully!');
                            } else {
                                this.showSuccess('Jobs deleted successfully!');
                            }
                        }
                        if (callbackOnSuccess && typeof callbackOnSuccess == 'function') {
                            callbackOnSuccess();
                        } else if (callbackOnSuccess && typeof this[callbackOnSuccess] == 'function') {
                            this[callbackOnSuccess](...callbackOnSuccessParams);
                        }
                        this.totalJobs = (response && response.data && response.data.numberOfJobsAvailable) ? response.data.numberOfJobsAvailable : 0;
                    })
            } else {
                console.log('Total jobs: ' + this.totalJobs + ' - no deletion needed.');
                // if (showResponseMessage) {
                //     this.showSuccess('No previous jobs have been deleted!');
                // }
                if (callbackOnSuccess && typeof callbackOnSuccess == 'function') {
                    callbackOnSuccess();
                } else if (callbackOnSuccess && typeof this[callbackOnSuccess] == 'function') {
                    this[callbackOnSuccess](...callbackOnSuccessParams);
                }
            }
        },
        deleteMailchimpEcommerceData() {
            if (confirm("Do you really want to delete?")) {
                this.showLoader = true;
                axios
                    .post(
                        window.configurationUrl + '&action=deleteEcommerceData',
                        {
                            action: 'ajaxProcessDeleteEcommerceData',
                        }
                    )
                    .then((response) => {
                            this.showLoader = false;
                            if (response.data.hasError === false) {
                                this.showSuccess(response.data.successMessage);
                            }
                            else {
                                this.showError(response.data.errorMessage);
                            }
                            setTimeout(function () {
                                location.reload();
                            }, 1500);
                        }
                    )
            }
        },
        executeCronjob(e) {
            e.preventDefault();
            this.showLoader = true;
            axios
                .post(
                    window.configurationUrl + '&action=executeCronjob&secure=' + window.cronjobSecureToken,
                    {
                        action: 'ajaxProcessExecuteCronjob',
                    }
                )
                .then((response) => {
                        if (response.data.errors) {
                            this.showError(response.data.errors);
                        }
                        else {
                            if (response.data.result) {
                                this.showSuccess(response.data.result);
                            }
                        }

                        this.updateStaticContent();
                    }
                )
                .catch((error) => {
                    var msg = (error && error.response && typeof error.response.data === 'string' && error.response.data)
                        || (error && error.message)
                        || 'Cronjob execution failed';
                    this.showError(msg);
                })
                .finally(() => {
                    this.showLoader = false;
                });
        },
        clearCronjobLog() {
            if (confirm("Do you really want to clear the cronjob log?")) {
                this.showLoader = true;
                axios
                    .post(
                        window.configurationUrl + '&action=clearCronjobLog',
                        {
                            action: 'ajaxProcessClearCronjobLog',
                        }
                    )
                    .then((response) => {
                            this.showLoader = false;
                            if (response.data.hasError === false) {
                                this.showSuccess(response.data.successMessage);
                            }
                            else {
                                this.showError(response.data.errorMessage);
                            }

                            this.updateStaticContent();
                        }
                    )
            }
        },
        refreshReports() {
            this.showLoader = true;
            axios
                .post(
                    window.configurationUrl + '&action=refreshReports',
                    {
                        action: 'refreshReports',
                    }
                )
                .then((response) => {
                        this.showLoader = false;
                        $('.panel-statistics #mailchimp-reports').replaceWith(response.data.statistics);
                    }
                )
        },
        showError(message) {
            Toastify({
                text: message,
                // Errors get a longer visibility window (8s) so verbose
                // messages — connection-broken guidance, "store missing on
                // Mailchimp" details — can be read before auto-dismissing.
                duration: 8000,
                close: true,
                gravity: "top",
                position: 'center',
                style: {
                    background: "#ff0000",
                },
                stopOnFocus: false,
            }).showToast();
        },
		showSuccess(message) {
            Toastify({
                text: message,
                duration: 2000,
                close: true,
                gravity: "top",
                position: 'center',
                style: {
                    background: "#1a8f35",
                },
                stopOnFocus: false,
            }).showToast();
        },
        viewPromoStats(idPromo) {
            if (idPromo) {
                localStorage.setItem('currentPromoStatId', idPromo);
            }
            this.currentPromoId = idPromo;

            axios
                .post(window.statsUrl + '&action=getStats', { idPromo: idPromo })
                .then(({ data }) => {
                    if (data.success) {
                        console.log(data);

                        $('#statsStart').datepicker("option", 'minDate', data.campaign.start_date);
                        $('#statsStart').datepicker("option", 'maxDate', data.campaign.end_date);

                        $('#statsEnd').datepicker("option", 'minDate', data.campaign.start_date);
                        $('#statsEnd').datepicker("option", 'maxDate', data.campaign.end_date);

                        this.setCurrentPage('promoStats');

                        this.statsName = data.campaign.name;

                        this.statAllCodes = data.stats.all_codes;
                        this.statUsedCodes = data.stats.used_codes;
                        //this.statUnUsedCodes = data.stats.unused_codes;
                        this.statConversion = data.stats.conversion;
                        this.statFrequency = data.stats.frequency;

                        var pieOptions = {
                            series: data.pieData.series,
                            chart: {
                                height: 315,
                                type: 'pie',
                            },
                            labels: data.pieData.labels,
                            fill: {
                                colors: data.pieData.colors
                            },
                            responsive: [{
                                breakpoint: 480,
                                options: {
                                    chart: {
                                        // width: 200
                                    },
                                    legend: {
                                        position: 'bottom'
                                    }
                                }
                            }]
                        };

                        var barOptions = {
                            series: [{
                                name: data.barData.seriesLabel,
                                data: data.barData.series ?? [0],
                            }],
                            chart: {
                                height: 350,
                                type: 'bar',
                            },
                            plotOptions: {
                                bar: {
                                    borderRadius: 0,
                                    dataLabels: {
                                        position: 'top', // top, center, bottom
                                    },
                                }
                            },
                            colors: ['#241c15'],
                            dataLabels: {
                                enabled: true,
                                // formatter: function (val) {
                                //   return val + "%";
                                // },
                                offsetY: -20,
                                style: {
                                    fontSize: '12px',
                                    colors: ["#304758"]
                                }
                            },

                            xaxis: {
                                categories: data.barData.labels ?? [new Date().toLocaleDateString()],
                                position: 'bottom',
                                axisBorder: {
                                    show: false
                                },
                                axisTicks: {
                                    show: false
                                },
                                crosshairs: {
                                    fill: {
                                        type: 'gradient',
                                        gradient: {
                                            colorFrom: '#D8E3F0',
                                            colorTo: '#BED1E6',
                                            stops: [0, 100],
                                            opacityFrom: 0.4,
                                            opacityTo: 0.5,
                                        }
                                    }
                                },
                                tooltip: {
                                    enabled: true,
                                }
                            },
                            yaxis: {
                                axisBorder: {
                                    show: false
                                },
                                axisTicks: {
                                    show: false,
                                },
                                labels: {
                                    show: false,
                                    //   formatter: function (val) {
                                    //     return val + "%";
                                    //   }
                                }

                            },
                            title: {
                                text: data.barData.mainLabel,
                                position: 'top',
                                align: 'center',
                                margin: 10,      
                                offsetX: 0,      
                                offsetY: 0,    
                                floating: false,
                                style: {
                                  fontSize: '16px',
                                  fontWeight: 'bold',
                                  color: '#444'
                                }
                            }
                        };

                        var conversionOptions = {
                            series: [
                                {
                                    name: data.conversionData.seriesLabel,
                                    data: data.conversionData.seriesOrderCart,
                                },
                            ],
                            chart: {
                                height: 350,
                                type: 'line',
                                dropShadow: {
                                    enabled: true,
                                    color: '#000',
                                    top: 18,
                                    left: 7,
                                    blur: 10,
                                    opacity: 0.5
                                },
                                zoom: {
                                    enabled: false
                                },
                                toolbar: {
                                    show: false
                                }
                            },
                            colors: ['#ffe01b', '#545454'],
                            dataLabels: {
                                enabled: true,
                                formatter: function (val) {
                                    return val + "%";
                                }
                            },
                            stroke: {
                                curve: 'smooth'
                            },
                            grid: {
                                borderColor: '#e7e7e7',
                                row: {
                                    colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
                                    opacity: 0.5
                                },
                            },
                            markers: {
                                size: 1
                            },
                            xaxis: {
                                categories: data.conversionData.labels,
                                position: 'bottom',
                            },
                            title: {
                                position: 'top',
                                text: data.conversionData.mainLabel,
                                align: 'center',
                                margin: 10,      
                                offsetX: 0,      
                                offsetY: 0,    
                                floating: false,
                                style: {
                                  fontSize: '16px',
                                  fontWeight: 'bold',
                                  color: '#444'
                                }
                            },
                            legend: {
                                position: 'top',
                                horizontalAlign: 'right',
                                floating: true,
                                offsetY: -25,
                                offsetX: -5
                            },
                            tooltip: {
                                y: {
                                  formatter: function(value) {
                                    return value + "%";
                                  }
                                }
                              }
                        };


                        if (typeof this.pieChart != 'undefined') {
                            this.pieChart.destroy();
                        }
                        this.pieChart = new ApexCharts(document.querySelector("#pieChart"), pieOptions);

                        if (typeof this.barChart != 'undefined') {
                            this.barChart.destroy();
                        }
                        this.barChart = new ApexCharts(document.querySelector("#barChart"), barOptions);
                        if (data.barData.series && data.barData.labels) {
                            document.querySelector("#barChart").style.display = 'block';
                        } else {
                            document.querySelector("#barChart").style.display = 'none';
                        }
                        
                        if (typeof this.conversionChart != 'undefined') {
                            this.conversionChart.destroy();
                        }
                        this.conversionChart = new ApexCharts(document.querySelector("#conversionChart"), conversionOptions);
                        

                        setTimeout(() => {
                            this.pieChart.render();
                            this.barChart.render();
                            this.conversionChart.render();
                        }, 300);

                    } else {
                        this.showError(data.message);
                    }
                })
                .catch((e) => {
                    console.log(e);
                    this.showError('An error occurred while fetching promo details.');
                });
        },
        getStats()
        {
            var request = {
                idPromo: this.currentPromoId,
                dateStart: $('#statsStart').val(),
                dateEnd: $('#statsEnd').val(),
            }

            axios
            .post(window.statsUrl + '&action=getStatsByDate', request)
            .then(({ data }) => {
                if (data.success == false) {
                    this.showError(data.message);
                    return;
                }

                console.log(data);
                this.barChart.updateOptions({
                    series: [{
                        data: data.barData.series,
                    }],
                    xaxis: {
                        categories: data.barData.labels
                    }
                });
                if (data.barData.series && data.barData.labels) {
                    document.querySelector("#barChart").style.display = 'block';
                } else {
                    document.querySelector("#barChart").style.display = 'none';
                }

                this.conversionChart.updateOptions({
                    series: [{
                        data: data.conversionData.seriesOrderCart,
                    }],
                    xaxis: {
                        categories: data.conversionData.labels
                    }
                });
                

            });
        },
        
        /**
         * Toggle promo overrides and handle installation if needed
         * 
         * @param {boolean} enabled - Whether overrides should be enabled
         */
        togglePromoOverrides(enabled) {
            this.showLoader = true;
            
            // If disabling and auto-installed, use the remove method
            if (!enabled && this.promoOverridesAutoInstalled) {
                axios
                    .post(
                        window.configurationUrl + '&action=removePromoOverrides',
                        {
                            action: 'ajaxProcessRemovePromoOverrides'
                        }
                    )
                    .then((response) => {
                        this.showLoader = false;
                        
                        if (response.data.success) {
                            this.showSuccess(response.data.message);
                            this.promoOverridesAutoInstalled = false;
                        } else {
                            this.showError(response.data.message || 'An error occurred');
                            // Reset the toggle if there was an error
                            this.promoOverridesEnabled = true;
                        }
                    })
                    .catch((error) => {
                        this.showLoader = false;
                        this.showError('Error during override removal: ' + (error.response?.data?.message || error.message));
                        // Reset the toggle if there was an error
                        this.promoOverridesEnabled = true;
                        console.error('Error removing promo overrides:', error);
                    });
            } else {
                // Existing code for enabling/disabling
                axios
                    .post(
                        window.configurationUrl + '&action=togglePromoOverrides',
                        {
                            action: 'ajaxProcessTogglePromoOverrides',
                            enabled: enabled
                        }
                    )
                    .then((response) => {
                        this.showLoader = false;
                        
                        if (response.data.success) {
                            switch (response.data.overrideStatus) {
                                case 'conflicts':
                                    this.showError(response.data.message);
                                    this.overrideConflicts = response.data.conflicts || [];
                                    
                                    // Create a more detailed conflict message
                                    let conflictDetails = '';
                                    this.overrideConflicts.forEach(conflict => {
                                        conflictDetails += `<strong>File: ${conflict.file}</strong><br>`;
                                        if (conflict.methods && conflict.methods.length > 0) {
                                            conflictDetails += 'Conflicting methods:<br><ul>';
                                            conflict.methods.forEach(method => {
                                                conflictDetails += `<li>${method}</li>`;
                                            });
                                            conflictDetails += '</ul>';
                                        }
                                    });
                                    
                                    // Store the detailed conflict message
                                    this.conflictDetailsHtml = conflictDetails;
                                    this.showManualInstallationDialog = true;
                                    
                                    // Reset the toggle if installation failed
                                    this.promoOverridesEnabled = false;
                                    break;
                                    
                                case 'installed':
                                    this.showSuccess(response.data.message);
                                    this.promoOverridesAutoInstalled = true;
                                    break;
                                    
                                case 'auto_installed':
                                    this.showSuccess(response.data.message);
                                    this.promoOverridesAutoInstalled = true;
                                    break;
                                    
                                case 'failed':
                                    this.showError(response.data.message);
                                    // Reset the toggle if installation failed
                                    this.promoOverridesEnabled = false;
                                    break;
                                    
                                case 'error':
                                    this.showError(response.data.message);
                                    // Reset the toggle if installation failed
                                    this.promoOverridesEnabled = false;
                                    break;
                                    
                                case 'disabled':
                                    this.showSuccess(response.data.message);
                                    break;
                                    
                                default:
                                    // Unknown status
                                    console.warn('Unknown override status:', response.data.overrideStatus);
                            }
                        } else {
                            this.showError(response.data.message || 'An error occurred');
                            
                            if (response.data.requiresManualInstallation) {
                                this.overrideConflicts = response.data.conflicts || [];
                                
                                // Create a more detailed conflict message
                                let conflictDetails = '';
                                this.overrideConflicts.forEach(conflict => {
                                    conflictDetails += `<strong>File: ${conflict.file}</strong><br>`;
                                    if (conflict.methods && conflict.methods.length > 0) {
                                        conflictDetails += 'Conflicting methods:<br><ul>';
                                        conflict.methods.forEach(method => {
                                            conflictDetails += `<li>${method}</li>`;
                                        });
                                        conflictDetails += '</ul>';
                                    }
                                });
                                
                                // Store the detailed conflict message
                                this.conflictDetailsHtml = conflictDetails;
                                this.showManualInstallationDialog = true;
                            }
                            
                            // Reset the toggle if there was an error
                            this.promoOverridesEnabled = false;
                        }
                    })
                    .catch((error) => {
                        this.showLoader = false;
                        this.showError('Error during override toggle: ' + (error.response?.data?.message || error.message));
                        // Reset the toggle if there was an error
                        this.promoOverridesEnabled = false;
                        console.error('Error toggling promo overrides:', error);
                    });
            }
        },
        
        /**
         * Mark overrides as manually installed
         */
        markOverridesManuallyInstalled() {
            this.showLoader = true;
            this.preventToggleOverrides = true; // Set flag to prevent watcher from triggering
            
            axios
                .post(
                    window.configurationUrl + '&action=markOverridesInstalled',
                    {
                        action: 'ajaxProcessMarkOverridesInstalled'
                    }
                )
                .then((response) => {
                    this.showLoader = false;
                    
                    if (response.data.success) {
                        this.showSuccess(response.data.message);
                        this.promoOverridesEnabled = true;
                        this.showManualInstallationDialog = false;
                        this.manuallyInstalledOverrides = true; // Set flag for manually installed overrides
                    } else {
                        this.showError(response.data.message || 'An error occurred');
                    }
                    
                    // Reset the flag after a short delay to ensure the watcher doesn't trigger
                    setTimeout(() => {
                        this.preventToggleOverrides = false;
                    }, 100);
                })
                .catch((error) => {
                    this.showLoader = false;
                    this.preventToggleOverrides = false; // Reset flag in case of error
                    this.showError('Error: ' + (error.response?.data?.message || error.message));
                    console.error('Error marking overrides as installed:', error);
                });
        },
        
        /**
         * Check the current status of overrides
         */
        checkOverrideStatus() {
            // Use the promoOverridesConflicts variable directly instead of making an AJAX call
            if (!this.manuallyInstalledOverrides && this.promoOverridesConflicts && this.promoOverridesConflicts.length > 0) {
                this.overrideConflicts = this.promoOverridesConflicts;
                
                // Create a more detailed conflict message
                let conflictDetails = '';
                this.overrideConflicts.forEach(conflict => {
                    conflictDetails += `<strong>File: ${conflict.file}</strong><br>`;
                    if (conflict.methods && conflict.methods.length > 0) {
                        conflictDetails += 'Conflicting methods:<br><ul>';
                        conflict.methods.forEach(method => {
                            conflictDetails += `<li>${method}</li>`;
                        });
                        conflictDetails += '</ul>';
                    }
                });
                
                // Store the detailed conflict message
                this.conflictDetailsHtml = conflictDetails;
                this.showManualInstallationDialog = true;
                
                // Set preventToggleOverrides flag to prevent AJAX call
                this.preventToggleOverrides = true;
                
                // Reset the toggle if installation failed
                this.promoOverridesEnabled = false;
                
                // Reset the flag after a short delay to ensure the watcher doesn't trigger
                setTimeout(() => {
                    this.preventToggleOverrides = false;
                }, 100);
            }
        },
        // Pixel-tab numeric-input keypress filter. Blocks every character that
        // isn't a digit 0-9, plus "0" as the first character when the input's
        // minimum allowed value is > 0 (so the rate-limit field whose min=0
        // still accepts a bare "0" meaning "disable"). The HTML maxlength
        // attribute on each input handles the upper bound separately.
        onDigitOnlyKeypress: function (event, minValue) {
            const charCode = event.charCode || event.which;
            if (charCode < 48 || charCode > 57) {
                event.preventDefault();
                return;
            }
            // Block "0" inserted at the start of the field (position 0) when
            // the input's min is > 0 — no leading zeros allowed. Covers empty
            // field + type "0" AND cursor-at-start of non-empty field + type "0".
            if (charCode === 48 && minValue > 0) {
                const input = event.target;
                const start = input.selectionStart !== null ? input.selectionStart : 0;
                if (start === 0) {
                    event.preventDefault();
                }
            }
        },
        // Paste counterpart of the keypress filter — clipboard payloads could
        // otherwise bypass the per-character block. Strips non-digits, then
        // splices the cleaned string into the current selection / cursor
        // position and re-dispatches an `input` event so Vue's v-model sees it.
        onDigitOnlyPaste: function (event) {
            event.preventDefault();
            const text = (event.clipboardData || window.clipboardData).getData('text');
            const digits = String(text).replace(/\D/g, '');
            if (digits === '') {
                return;
            }
            const input = event.target;
            const start = input.selectionStart;
            const end = input.selectionEnd;
            input.value = input.value.slice(0, start) + digits + input.value.slice(end);
            input.dispatchEvent(new Event('input'));
        },
        // Shared watcher body for every numeric text input. Auto-strips leading
        // zeros (returns immediately so the watcher re-fires on the stripped
        // value), then schedules a debounced (~600ms) range-error toast. Save
        // does NOT happen here — it's blur's job. `errMsg` is captured by the
        // closure so each setTimeout fires the field-specific message.
        handleNumericWatcher: function (rawKey, timerKey, min, max, errMsg) {
            if (/^0\d/.test(String(this[rawKey]))) {
                this[rawKey] = String(this[rawKey]).replace(/^0+/, '');
                return;
            }
            clearTimeout(this[timerKey]);
            this[timerKey] = setTimeout(() => {
                const n = parseInt(this[rawKey], 10);
                if (isNaN(n) || n < min || n > max) {
                    this.showError(errMsg);
                }
            }, 600);
        },
        // Shared blur body. Cancels the pending toast, restores last-saved
        // canonical on NaN/empty (user mid-edit cleared the field — don't
        // change committed state), snaps below-min / above-max to the
        // nearest bound, commits Raw → canonical, and saves only when the
        // canonical value actually changed.
        handleNumericBlur: function (rawKey, canonicalKey, timerKey, min, max) {
            clearTimeout(this[timerKey]);
            const n = parseInt(this[rawKey], 10);
            if (isNaN(n)) {
                this[rawKey] = this[canonicalKey];
                return;
            }
            let valid = n;
            if (n < min) {
                valid = min;
            } else if (n > max) {
                valid = max;
            }
            this[rawKey] = valid;
            if (this[canonicalKey] !== valid) {
                this[canonicalKey] = valid;
                this.saveSettings();
            }
        },
        // Blur handlers — cancel the pending debounced toast, snap the Raw
        // value to the nearest valid bound (or the PHP-injected default for
        // SDK delay NaN), commit Raw → canonical, and save only if the
        // canonical value actually changed.
        onSdkSettleDelayBlur: function () {
            this.handleNumericBlur('sdkSettleDelayMsRaw', 'sdkSettleDelayMs', 'sdkSettleDelayMsToastTimer', this.sdkSettleDelayMsMin, this.sdkSettleDelayMsMax);
        },
        onPixelLogMaxRowsBlur: function () {
            this.handleNumericBlur('pixelLogMaxRowsRaw', 'pixelLogMaxRows', 'pixelLogMaxRowsToastTimer', 100, 100000);
        },
        onPixelLogRateLimitPerMinBlur: function () {
            this.handleNumericBlur('pixelLogRateLimitPerMinRaw', 'pixelLogRateLimitPerMin', 'pixelLogRateLimitPerMinToastTimer', 0, 10000);
        },
        onQueueStepBlur: function () {
            this.handleNumericBlur('queueStepRaw', 'queueStep', 'queueStepToastTimer', 1, 9999);
        },
        onQueueAttemptBlur: function () {
            this.handleNumericBlur('queueAttemptRaw', 'queueAttempt', 'queueAttemptToastTimer', 1, 99);
        }
    },
    computed: {
        /**
         * Grouped option list for the newsletter form-identify Multiselect.
         * Three groups:
         *   1. Built-in defaults — disabled (visible for transparency only;
         *      admin can't click them; always active server-side).
         *   2. Common theme-specific patterns — click-to-add suggestions.
         *   3. Your custom selectors — appears ONLY when admin has typed
         *      or added values that aren't built-in / suggested. Provides
         *      a clear view of what THIS admin has added on top.
         *
         * Reactive on identifyNewsletterSelectorsCustom so a freshly-typed
         * value appears in group 3 immediately.
         */
        identifyNewsletterAllOptions: function () {
            var core = this.identifyNewsletterCoreDefaults || [];
            var suggested = this.identifyNewsletterSuggestedOptions || [];
            var suggestedValues = suggested.map(function (o) { return o.value; });
            var v = Array.isArray(this.identifyNewsletterSelectorsCustom)
                ? this.identifyNewsletterSelectorsCustom : [];
            var customValues = v.filter(function (s) {
                return core.indexOf(s) === -1 && suggestedValues.indexOf(s) === -1;
            });

            var groups = [
                {
                    label: 'Built-in defaults (always active)',
                    options: core.map(function (s) {
                        return { value: s, label: '✓ ' + s, disabled: true };
                    }),
                },
                {
                    label: 'Common theme-specific patterns (click to add)',
                    options: suggested,
                },
            ];
            if (customValues.length > 0) {
                groups.push({
                    label: 'Your custom selectors',
                    options: customValues.map(function (s) {
                        return { value: s, label: s };
                    }),
                });
            }
            return groups;
        },

        /**
         * Lookup set of values that came from the "Common theme-specific
         * patterns" suggestion list. Used by the tag slot to apply the
         * `tag-suggested` CSS class — visually distinct from admin's
         * freely-typed custom selectors (which get `tag-custom`).
         */
        identifyNewsletterSuggestedValuesSet: function () {
            var out = {};
            (this.identifyNewsletterSuggestedOptions || []).forEach(function (o) {
                out[o.value] = true;
            });
            return out;
        },

        /**
         * Same shape + ordering as identifyNewsletterAllOptions, sourced from
         * the restock-specific data fields. Built-in defaults are rendered
         * as disabled tags; the admin's custom selectors appear in their
         * own group when present.
         */
        identifyRestockAllOptions: function () {
            var core = this.identifyRestockCoreDefaults || [];
            var suggested = this.identifyRestockSuggestedOptions || [];
            var suggestedValues = suggested.map(function (o) { return o.value; });
            var v = Array.isArray(this.identifyRestockSelectorsCustom)
                ? this.identifyRestockSelectorsCustom : [];
            var customValues = v.filter(function (s) {
                return core.indexOf(s) === -1 && suggestedValues.indexOf(s) === -1;
            });

            var groups = [
                {
                    label: 'Built-in defaults (always active)',
                    options: core.map(function (s) {
                        return { value: s, label: '✓ ' + s, disabled: true };
                    }),
                },
                {
                    label: 'Common theme-specific patterns (click to add)',
                    options: suggested,
                },
            ];
            if (customValues.length > 0) {
                groups.push({
                    label: 'Your custom selectors',
                    options: customValues.map(function (s) {
                        return { value: s, label: s };
                    }),
                });
            }
            return groups;
        },

        identifyRestockSuggestedValuesSet: function () {
            var out = {};
            (this.identifyRestockSuggestedOptions || []).forEach(function (o) {
                out[o.value] = true;
            });
            return out;
        },

        /** Same shape + ordering as identifyNewsletterAllOptions, contact data. */
        identifyContactAllOptions: function () {
            var core = this.identifyContactCoreDefaults || [];
            var suggested = this.identifyContactSuggestedOptions || [];
            var suggestedValues = suggested.map(function (o) { return o.value; });
            var v = Array.isArray(this.identifyContactSelectorsCustom)
                ? this.identifyContactSelectorsCustom : [];
            var customValues = v.filter(function (s) {
                return core.indexOf(s) === -1 && suggestedValues.indexOf(s) === -1;
            });

            var groups = [
                {
                    label: 'Built-in defaults (always active)',
                    options: core.map(function (s) {
                        return { value: s, label: '✓ ' + s, disabled: true };
                    }),
                },
                {
                    label: 'Common theme-specific patterns (click to add)',
                    options: suggested,
                },
            ];
            if (customValues.length > 0) {
                groups.push({
                    label: 'Your custom selectors',
                    options: customValues.map(function (s) {
                        return { value: s, label: s };
                    }),
                });
            }
            return groups;
        },

        identifyContactSuggestedValuesSet: function () {
            var out = {};
            (this.identifyContactSuggestedOptions || []).forEach(function (o) {
                out[o.value] = true;
            });
            return out;
        },

        /** Same shape + ordering as identifyNewsletterAllOptions, forgot-password data. */
        identifyForgotPasswordAllOptions: function () {
            var core = this.identifyForgotPasswordCoreDefaults || [];
            var suggested = this.identifyForgotPasswordSuggestedOptions || [];
            var suggestedValues = suggested.map(function (o) { return o.value; });
            var v = Array.isArray(this.identifyForgotPasswordSelectorsCustom)
                ? this.identifyForgotPasswordSelectorsCustom : [];
            var customValues = v.filter(function (s) {
                return core.indexOf(s) === -1 && suggestedValues.indexOf(s) === -1;
            });

            var groups = [
                {
                    label: 'Built-in defaults (always active)',
                    options: core.map(function (s) {
                        return { value: s, label: '✓ ' + s, disabled: true };
                    }),
                },
                {
                    label: 'Common theme-specific patterns (click to add)',
                    options: suggested,
                },
            ];
            if (customValues.length > 0) {
                groups.push({
                    label: 'Your custom selectors',
                    options: customValues.map(function (s) {
                        return { value: s, label: s };
                    }),
                });
            }
            return groups;
        },

        identifyForgotPasswordSuggestedValuesSet: function () {
            var out = {};
            (this.identifyForgotPasswordSuggestedOptions || []).forEach(function (o) {
                out[o.value] = true;
            });
            return out;
        },

        /**
         * Shows the CDN-propagation banner in the Pixel tab for 5
         * minutes after the Mailchimp pixel script was (re-)cached.
         *
         * Every actual CDN-propagation trigger updates scriptCachedDate
         * via cacheConnectedSiteScript():
         *   - Master pixel toggle ON/OFF (togglePixel → chains syncStoresScript)
         *   - Manual "Fetch script" button click (syncStoresScript)
         *   - Fresh install completing first store-sync (ajaxProcessSyncStores)
         *   - Audience swap (re-cache after new connected_site)
         *
         * scriptCachedDate is PHP's date('Y-m-d H:i:s') in PS_TIMEZONE.
         * We parse with Date constructor (local-TZ) and compare against
         * Date.now() (also local-TZ on the admin's browser). Server +
         * admin in the same TZ → exact match. Different TZ → result is
         * "banner shows or doesn't show" — neither breaks anything.
         *
         * After 5 minutes the 30s setInterval bumps `nowTick`, the
         * computed re-evaluates to false, and the banner reactively
         * disappears.
         */
        showCdnPropagationBanner: function () {
            if (!this.scriptCachedDate) return false;
            try {
                // Replace space with 'T' for ISO 8601 form so older
                // browsers (Safari ≤ 14, IE-era polyfills) parse correctly.
                var cachedMs = new Date(String(this.scriptCachedDate).replace(' ', 'T')).getTime();
                if (isNaN(cachedMs)) return false;
                var ageMs = this.nowTick - cachedMs;
                return ageMs >= 0 && ageMs < 5 * 60 * 1000;
            } catch (e) {
                return false;
            }
        },
    },

    watch: {
        listId: function () {
            if (this.listId) {
                this.storeSynced = false;
            }
            this.saveSettings();
        },
        multiInstanceMode: function () {
            if (this.storeInstanceMode != this.multiInstanceMode) {
                this.storeSynced = false;
                this.listId = false;
                this.saveSettings("multiInstanceMode", false);
            }
            else {
                this.saveSettings("multiInstanceMode", true);
            }
        },
        selectedPreset: function (newValue, oldValue) {
            // if (oldValue || newValue == 'custom') {
            //     this.saveSettings();
            // } else {
            //     // automatically add jobs to queue on first preset selection
            //     // this.saveSettings('saveSettings', true, 'pushSetupJobsToQueue');
            //     this.saveSettings('saveSettings', true, () => {this.pushSetupJobsToQueue()});
            // }
            this.saveSettings();
        },
        cronjobBasedSync: function () {
            this.saveSettings();
        },
        syncProducts: function () {
            this.saveSettings();
        },
        syncCustomers: function () {
            this.saveSettings();
        },
        syncCartRules: function () {
            this.saveSettings();
        },
        syncOrders: function () {
            this.saveSettings();
        },
        syncCarts: function () {
            this.saveSettings();
        },
        syncCartsPassw: function () {
            this.saveSettings();
        },
        syncNewsletterSubscribers: function () {
            this.saveSettings();
        },
        statusForPending: function () {
            this.saveSettings();
        },
        statusForRefunded: function () {
            this.saveSettings();
        },
        statusForCancelled: function () {
            this.saveSettings();
        },
        statusForShipped: function () {
            this.saveSettings();
        },
        statusForPaid: function () {
            this.saveSettings();
        },
        // CHECKOUT_STARTED custom checkout controllers — fires on add /
        // remove / suggestion-click. The Multiselect emits a fresh array
        // reference each time so the watcher triggers reliably. Vue uses
        // deep:false by default; not needed here because we only mutate
        // the reference, never modify-in-place.
        pixelCheckoutControllers: function () {
            this.saveSettings();
        },
        // PURCHASED custom order-confirmation controllers — same watcher
        // pattern as pixelCheckoutControllers. Saves on add / remove /
        // suggestion-click. Default empty list — most setups never need
        // entries here.
        pixelOrderConfirmationControllers: function () {
            this.saveSettings();
        },
        productDescriptionField: function () {
            this.saveSettings();
        },
        existingOrderSyncStrategy: function () {
            this.saveSettings();
        },
        productSyncFilterActive: function () {
            this.saveSettings();
        },
        productSyncFilterVisibility: function () {
            this.saveSettings();
        },
        customerSyncFilterEnabled: function () {
            this.saveSettings();
        },
        customerSyncFilterNewsletter: function () {
            this.saveSettings();
        },
        customerSyncFilterPeriod: function () {
            this.saveSettings();
        },
        customerSyncTagDefaultGroup: function () {
            this.saveSettings();
        },
        customerSyncTagGender: function () {
            this.saveSettings();
        },
        customerSyncTagLanguage: function () {
            this.saveSettings();
        },
        customerSyncCompanyFromAddress: function () {
            this.saveSettings();
        },
        cartRuleSyncFilterStatus: function () {
            this.saveSettings();
        },
        newsletterSubscriberSyncFilterPeriod: function () {
            this.saveSettings();
        },
        cartRuleSyncFilterExpiration: function () {
            this.saveSettings();
        },
        productImageSize: function () {
            this.saveSettings();
        },
		logQueue: function () {
            this.saveSettings();
        },
        showDashboardStats: function () {
            this.saveSettings();
        },
        promoOverridesEnabled: function (newValue) {
            // Only call togglePromoOverrides if preventToggleOverrides is false
            if (!this.preventToggleOverrides) {
                this.togglePromoOverrides(newValue);
            }
        },
        queueStepRaw: function () {
            this.handleNumericWatcher('queueStepRaw', 'queueStepToastTimer', 1, 9999, 'Invalid queue step! Must be 1-9999.');
        },
        queueAttemptRaw: function () {
            this.handleNumericWatcher('queueAttemptRaw', 'queueAttemptToastTimer', 1, 99, 'Invalid queue max-trying time! Must be 1-99.');
        },
        logCronjob: function () {
            this.saveSettings();
        },
        autotrackEnabled: function () {
            this.saveSettings();
        },
        pixelRequireConsent: function () {
            this.saveSettings();
        },
        identifyFormat: function () {
            this.saveSettings();
        },
        identifyAjaxMode: function () {
            this.saveSettings();
        },
        identifyOnForms: function () {
            this.saveSettings();
        },
        // Multiselect tags emits a fresh array on add / remove / clear.
        // Saves immediately on change — same pattern as pixelCheckoutControllers.
        // `deep: true` because Vue 3 reactivity on array contents needs it.
        identifyNewsletterSelectorsCustom: {
            handler: function () {
                this.saveSettings();
            },
            deep: true
        },
        identifyRestockSelectorsCustom: {
            handler: function () {
                this.saveSettings();
            },
            deep: true
        },
        identifyContactSelectorsCustom: {
            handler: function () {
                this.saveSettings();
            },
            deep: true
        },
        identifyForgotPasswordSelectorsCustom: {
            handler: function () {
                this.saveSettings();
            },
            deep: true
        },
        pixelLogLevel: function () {
            this.saveSettings();
        },
        pixelLogEnabled: function () {
            this.saveSettings();
        },
        sdkSettleDelayMsRaw: function () {
            this.handleNumericWatcher('sdkSettleDelayMsRaw', 'sdkSettleDelayMsToastTimer', this.sdkSettleDelayMsMin, this.sdkSettleDelayMsMax, 'Invalid SDK settle delay! Must be ' + this.sdkSettleDelayMsMin + '-' + this.sdkSettleDelayMsMax + ' ms.');
        },
        pixelLogMaxRowsRaw: function () {
            this.handleNumericWatcher('pixelLogMaxRowsRaw', 'pixelLogMaxRowsToastTimer', 100, 100000, 'Pixel log size cap must be between 100 and 100,000.');
        },
        pixelLogRateLimitPerMinRaw: function () {
            this.handleNumericWatcher('pixelLogRateLimitPerMinRaw', 'pixelLogRateLimitPerMinToastTimer', 0, 10000, 'Telemetry rate limit must be between 0 and 10,000.');
        }
    },
    mounted() {
        // Global axios response interceptor: any ajax response the module's
        // controllers emit is routed through ajaxDie, which auto-injects the
        // connection-health payload. This one-liner then feeds every response
        // (success or error) into updateHealthStateFromResponse so the banner
        // stays in sync without per-handler wiring.
        const vm = this;
        axios.interceptors.response.use(
            function (response) {
                vm.updateHealthStateFromResponse(response);
                return response;
            },
            function (error) {
                if (error && error.response) {
                    vm.updateHealthStateFromResponse(error.response);
                }
                return Promise.reject(error);
            }
        );
        this.timer = setInterval(() => {
            if (this.getCurrentPage() !== this.currentPage) {
                this.currentPage = this.getCurrentPage();
            }
        }, 100);
        // Measure the BO's fixed top-bar height and feed it to the
        // --bo-fixed-header-offset CSS variable so [id^="accordion-heading"]
        // and #pixel-require-consent-section scroll-margin-top adjusts to the
        // actual BO chrome (varies across PS 1.7 / 8 / 9 themes). Re-measured
        // on window resize so changes to the BO toolbar (e.g. responsive
        // collapse) don't leave a stale offset.
        this.updateBOHeaderOffset();
        window.addEventListener('resize', this.updateBOHeaderOffset);
        // Live clock for the CDN-propagation banner. 30s cadence is
        // a good compromise: banner disappears within 30s of the 5-min
        // mark (responsive without busy-polling). No need to clearInterval
        // on unmount — the timer is bound to the page lifecycle and Vue's
        // root instance lives for the full page session.
        this.cdnBannerTick = setInterval(() => {
            this.nowTick = Date.now();
        }, 30000);
        // Background CSP staleness check (>24h) — silent, only fires when pixel is injecting.
        this.maybeCheckPixelCsp();
        // Pre-load pixel log so the tab is responsive on first visit.
        this.loadPixelLogs();
        const savedPromoId = localStorage.getItem('currentPromoId');

        if (savedPromoId && window.location.hash === '#addNewPromo') {
            this.editPromo(savedPromoId); // Call editPromo with the saved ID
        }
        const currentPromoStatId = localStorage.getItem('currentPromoStatId');
        if (currentPromoStatId && window.location.hash === '#promoStats') {
            this.viewPromoStats(currentPromoStatId); // Call editPromo with the saved ID
        }

        window.addEventListener(
            "message",
            (event) => {
                if (event.origin !== window.middlewareUrl) {
                    return false;
                }
                if (event.data.hasOwnProperty('token') && event.data.hasOwnProperty('user')) {
                    const token = event.data.token + "-" + event.data.user.dc;
                    axios
                        .post(
                            window.configurationUrl + '&action=connect',
                            {
                                action: 'connect',
                                token: token
                            }
                        )
                        .then((response) => {
                                /* this.accountInfo = response.data.accountInfo;
                                this.validApiKey = response.data.validApiKey; */
                                
                                //setTimeout(function () {
                                    location.reload();
                                //}, 500);
                            }
                        )
                }
            },
            true
        );


        // Add dynamic watch to all the data which affect the current preset:
        // Get unique preset affecting config keys
        const uniquePresetConfigKeys = this.getUniqueConfigKeys(this.definedPresets);
        // Add watchers dynamically for each unique key
        uniquePresetConfigKeys.forEach(key => {
            if (this[key] !== undefined) {
                this.$watch(
                    key,
                    (newValue, oldValue) => {
                        // console.log(`Key '${key}' changed:`, oldValue, '->', newValue);
                        // manage selected preset only when the value of the watched key is changed manually and not by triggered from selectPreset()
                        if (!this.preventSave) {
        this.manageSelectedPreset();
        
        // Check override status on page load
        this.checkOverrideStatus();
                        }
                    },
                    { immediate: false } // Optional: Run the watcher immediately on initialization
                );
            }
        });

        this.checkOverrideStatus();
        this.manageSelectedPreset();
    },
        data() {
            return {
            isSaving: false,
            preventSave: false,
            showLoader: false,
			jobsAddedToQueue: false,
            queueLoadingProducts: false,
            queueLoadingCustomers: false,
            queueLoadingCartRules: false,
            queueLoadingOrders: false,
            queueLoadingNewsletterSubscribers: false,
            queuedTypes: {
                products: false,
                customers: false,
                cartRules: false,
                orders: false,
                newsletterSubscribers: false,
            },
            definedPresets: window.mailchimp.definedPresets,
            selectedPreset: window.mailchimp.selectedPreset,
            currentPage: this.getCurrentPage(true),
            // Mutex state for the FAQ "Expand all" / "Collapse all" buttons —
            // only one is rendered at a time. Starts at false because every
            // accordion ships closed by default (no `in` class). Flipped by
            // expandAllFaqs / collapseAllFaqs + by setCurrentPage's auto-
            // expand path when arriving via a cross-tab deep-link.
            allFaqsExpanded: false,
            currentPromoId: 0,
            promoCodes: [],
            selectedPromoCodes: [],
            selectAll: false,
            preventToggleOverrides: false,
            manuallyInstalledOverrides: window.mailchimp.manuallyInstalledOverrides,
            listId: window.mailchimp.listId,
            lists: window.mailchimp.lists,
            storeAlreadySynced: window.storeAlreadySynced ?? false,
            storeSynced: window.mailchimp.storeSynced,
            scriptCachedDate: window.mailchimp.scriptCachedDate,
            cspStatus: window.mailchimp.cspStatus || null,
            cspCheckedAt: window.mailchimp.cspCheckedAt || 0,
            cspChecking: false,
            // Connection-health state — Phase 3 banner reads healthStateFlag
            // to decide whether to render (empty = healthy = no banner) and
            // which state-specific copy + action button to show. foCdnBroken
            // is a separate signal: when true, MC.js emission is suppressed
            // on the storefront (piece 4 gate) — surfaced in the same panel
            // so the merchant sees BOTH BO and FO breakage at a glance.
            healthStateFlag: window.mailchimp.healthStateFlag || '',
            healthStateSetAt: window.mailchimp.healthStateSetAt || '',
            healthStateSource: window.mailchimp.healthStateSource || '',
            foCdnBroken: window.mailchimp.foCdnBroken || false,
            foCdnBrokenUntil: window.mailchimp.foCdnBrokenUntil || 0,
            healthBannerDismissedThisSession: false,
            // JSON cache of verify-script-installation result.
            // Shape: {verified, last_checked_at, last_attempt_at, last_error}.
            // Null when never verified or no JSON cache yet.
            scriptVerified: window.mailchimp.scriptVerified || null,
            // Pixel master enable/disable state. Derived from
            // pixelAvailable.enabled at boot; flipped via togglePixel() which
            // calls the Mailchimp enable-pixel / disable-pixel endpoints +
            // refreshes the cached script. The showLoader overlay covers the
            // whole tab during the AJAX chain, so we don't need a separate
            // saving/target flag for visual feedback or click debouncing.
            pixelEnabled: !!(window.mailchimp.pixelAvailable && window.mailchimp.pixelAvailable.enabled === true),
            // Pixel tab state. Strings ('1' / '0') for radio v-model strict-equality
            // compatibility — matches the 'logQueue' / 'logCronjob' pattern.
            autotrackEnabled: window.mailchimp.autotrackEnabled !== undefined && window.mailchimp.autotrackEnabled !== ''
                ? String(window.mailchimp.autotrackEnabled) : '1',
            // Live tick incremented every 30s in mounted() — used by the
            // computed showCdnPropagationBanner so the banner reactively
            // self-hides after the 5-minute propagation window expires.
            nowTick: Date.now(),
            pixelRequireConsent: window.mailchimp.pixelRequireConsent !== undefined && window.mailchimp.pixelRequireConsent !== ''
                ? String(window.mailchimp.pixelRequireConsent) : '1',
            // SDK settle delay — milliseconds the bridge waits after the
            // SDK reports installed===true before firing the first track.
            // PHP-side constants on MailchimpProConfig are the single
            // source of truth; the BO JsDef injects them as
            // sdkSettleDelayMsMin/Max/Default. Input's :min/:max bind to
            // the latter; v-model.number keeps the field numeric; server
            // silent-clamps on save. No JS-side magic-number fallbacks —
            // if PHP injection fails, the input renders as NaN which is
            // a loud signal that something's wrong at the PHP-to-JS
            // boundary (vs silently guessing a value).
            sdkSettleDelayMs: Number.isFinite(Number(window.mailchimp.sdkSettleDelayMs))
                ? Number(window.mailchimp.sdkSettleDelayMs)
                : Number(window.mailchimp.sdkSettleDelayMsDefault),
            sdkSettleDelayMsRaw: Number.isFinite(Number(window.mailchimp.sdkSettleDelayMs))
                ? Number(window.mailchimp.sdkSettleDelayMs)
                : Number(window.mailchimp.sdkSettleDelayMsDefault),
            sdkSettleDelayMsMin: Number(window.mailchimp.sdkSettleDelayMsMin),
            sdkSettleDelayMsMax: Number(window.mailchimp.sdkSettleDelayMsMax),
            sdkSettleDelayMsDefault: Number(window.mailchimp.sdkSettleDelayMsDefault),
            sdkSettleDelayMsToastTimer: null,
            pixelLogMaxRowsToastTimer: null,
            pixelLogRateLimitPerMinToastTimer: null,
            identifyFormat: window.mailchimp.identifyFormat || 'EMAIL_SHA256',
            identifyAjaxMode: window.mailchimp.identifyAjaxMode !== undefined && window.mailchimp.identifyAjaxMode !== ''
                ? String(window.mailchimp.identifyAjaxMode) : '0',
            // Form-identify master toggle — gates the JS-side watcher for
            // newsletter / restock / contact / forgot-password forms.
            identifyOnForms: window.mailchimp.identifyOnForms !== undefined && window.mailchimp.identifyOnForms !== ''
                ? String(window.mailchimp.identifyOnForms) : '1',
            // Hardcoded core defaults shipped from PHP (single source of
            // truth: MailchimpProConfig::$pixelFormCoreDefaults). Always
            // active when master toggle is on. Surfaced in the BO ONLY as
            // DISABLED dropdown entries (non-clickable, greyed out) so
            // admins SEE what's matching by default — they aren't rendered
            // as tags in the input area. The bridge UNIONs these with the
            // admin's custom selectors at runtime.
            identifyNewsletterCoreDefaults: (window.mailchimp.pixelFormCoreDefaults
                && Array.isArray(window.mailchimp.pixelFormCoreDefaults.newsletter))
                ? window.mailchimp.pixelFormCoreDefaults.newsletter.slice()
                : [],
            // v-model for the Multiselect — admin's custom additions ONLY.
            // Stored server-side as a comma-separated string; Multiselect
            // wants an array, so we split here and rejoin on save.
            identifyNewsletterSelectorsCustom: (function () {
                var customStr = (typeof window.mailchimp.identifyNewsletterSelectorsCustom === 'string')
                    ? window.mailchimp.identifyNewsletterSelectorsCustom.trim()
                    : '';
                return customStr !== ''
                    ? customStr.split(',').map(function (s) { return s.trim(); }).filter(Boolean)
                    : [];
            })(),
            // Common theme/module-specific newsletter form selectors offered
            // as one-click ADDITIONS in the Multiselect dropdown. These are
            // patterns seen on 3rd-party themes / marketplace modules that
            // diverge from the stock module template the core defaults cover.
            identifyNewsletterSelectorSuggestions: [
                { value: '.footer-subscribe form', label: '.footer-subscribe form — theme variant (footer subscribe block)' },
                { value: 'form.newsletter-form', label: 'form.newsletter-form — common class-based fallback' },
                { value: 'form[action*="newsletter"]', label: 'form[action*="newsletter"] — match by form action URL' },
                { value: '#newsletter-popup form', label: '#newsletter-popup form — popup-style subscription forms' },
                { value: '.ps_emailsubscription form', label: '.ps_emailsubscription form — explicit module class' },
            ],
            // Static reference lists for the Multiselect — the actual
            // grouped :options is built reactively in the `computed:`
            // section below (so admin-typed custom values can be surfaced
            // as a 3rd group in the dropdown for visibility).
            identifyNewsletterSuggestedOptions: [
                { value: '.footer-subscribe form', label: '.footer-subscribe form — theme variant (footer subscribe block)' },
                { value: 'form.newsletter-form', label: 'form.newsletter-form — common class-based fallback' },
                { value: 'form[action*="newsletter"]', label: 'form[action*="newsletter"] — match by form action URL' },
                { value: '#newsletter-popup form', label: '#newsletter-popup form — popup-style subscription forms' },
                { value: '.ps_emailsubscription form', label: '.ps_emailsubscription form — explicit module class' },
            ],
            // ──── Restock-alert form (ps_emailalerts) ────────────────────
            // Same shape and lifecycle as the newsletter block above; see
            // the comments there for the underlying contract. The Multiselect
            // is a click-target picker (NOT a <form> selector) since restock
            // is JS-driven AJAX with no real form element.
            identifyRestockCoreDefaults: (window.mailchimp.pixelFormCoreDefaults
                && Array.isArray(window.mailchimp.pixelFormCoreDefaults.restock))
                ? window.mailchimp.pixelFormCoreDefaults.restock.slice()
                : [],
            identifyRestockSelectorsCustom: (function () {
                var customStr = (typeof window.mailchimp.identifyRestockSelectorsCustom === 'string')
                    ? window.mailchimp.identifyRestockSelectorsCustom.trim()
                    : '';
                return customStr !== ''
                    ? customStr.split(',').map(function (s) { return s.trim(); }).filter(Boolean)
                    : [];
            })(),
            // Common 3rd-party / theme-variant restock button selectors as
            // click-to-add suggestions. The core defaults already cover stock
            // ps_emailalerts (both classic + hummingbird) — these are escape
            // hatches for custom restock implementations or themes that ship
            // their own wrapper class.
            identifyRestockSuggestedOptions: [
                { value: '.product-availability button[data-action="notify-me"]', label: '.product-availability button[data-action="notify-me"] — theme attribute' },
                { value: 'button.notify-me', label: 'button.notify-me — common class-based fallback' },
                { value: '[data-restock-notify]', label: '[data-restock-notify] — generic data-attribute hook' },
                { value: '.btn-notify-when-available', label: '.btn-notify-when-available — common 3rd-party class' },
            ],
            // ──── Contact form (contactform module) ──────────────────────
            // Same shape and lifecycle as the newsletter / restock blocks
            // above. Built-in selectors anchor on PS-specific identifiers
            // (select[name="id_contact"]) plus the theme convention
            // (section.contact-form form). The bridge ALSO appends a
            // runtime selector from window.prestashop.urls.pages.contact
            // at boot, so multi-lingual contact URLs (French /contactez-nous,
            // German /kontaktieren-sie-uns, etc.) work without admin
            // having to paste the translated slug.
            identifyContactCoreDefaults: (window.mailchimp.pixelFormCoreDefaults
                && Array.isArray(window.mailchimp.pixelFormCoreDefaults.contact))
                ? window.mailchimp.pixelFormCoreDefaults.contact.slice()
                : [],
            identifyContactSelectorsCustom: (function () {
                var customStr = (typeof window.mailchimp.identifyContactSelectorsCustom === 'string')
                    ? window.mailchimp.identifyContactSelectorsCustom.trim()
                    : '';
                return customStr !== ''
                    ? customStr.split(',').map(function (s) { return s.trim(); }).filter(Boolean)
                    : [];
            })(),
            // Common 3rd-party / theme-variant contact form selectors as
            // click-to-add suggestions. Built-ins already cover stock PS
            // contactform; these are escape hatches for popular addon
            // contact forms with their own DOM.
            identifyContactSuggestedOptions: [
                { value: 'form#ets-contactus-form', label: 'form#ets-contactus-form — ETS Contact Us module' },
                { value: 'form.pscontactform-pro', label: 'form.pscontactform-pro — PS Contact Form Pro (paid)' },
                { value: 'form#mhsuperformbuilder-contact', label: 'form#mhsuperformbuilder-contact — Mister House Super Form Builder' },
                { value: 'form[data-form-type="contact"]', label: 'form[data-form-type="contact"] — generic data-attribute hook' },
            ],
            // ──── Forgot-password form (PS core AuthController) ──────────
            // PS core feature (not a module). Built-ins anchor on the
            // stable `forgotten-password` class shipped by both stock
            // theme overrides. Runtime selector appended by the bridge
            // from window.prestashop.urls.pages.password handles multi-
            // lingual recovery URLs.
            identifyForgotPasswordCoreDefaults: (window.mailchimp.pixelFormCoreDefaults
                && Array.isArray(window.mailchimp.pixelFormCoreDefaults.forgotPassword))
                ? window.mailchimp.pixelFormCoreDefaults.forgotPassword.slice()
                : [],
            identifyForgotPasswordSelectorsCustom: (function () {
                var customStr = (typeof window.mailchimp.identifyForgotPasswordSelectorsCustom === 'string')
                    ? window.mailchimp.identifyForgotPasswordSelectorsCustom.trim()
                    : '';
                return customStr !== ''
                    ? customStr.split(',').map(function (s) { return s.trim(); }).filter(Boolean)
                    : [];
            })(),
            // Suggestion list for forgot-password is intentionally small —
            // 3rd-party password-reset form modules are extremely rare in
            // PS (it's a core auth flow). The built-ins cover stock + both
            // themes, the runtime URL selector handles i18n.
            identifyForgotPasswordSuggestedOptions: [
                { value: 'form#forgot-password', label: 'form#forgot-password — generic ID' },
                { value: 'form[data-form-type="password-reset"]', label: 'form[data-form-type="password-reset"] — generic data-attribute hook' },
                { value: 'form[action*="forgot" i]', label: 'form[action*="forgot" i] — action-URL match' },
            ],
            pixelLogLevel: window.mailchimp.pixelLogLevel || 'default',
            pixelLogLevels: window.mailchimp.pixelLogLevels || {},
            pixelLogEnabled: window.mailchimp.pixelLogEnabled !== undefined && window.mailchimp.pixelLogEnabled !== ''
                ? String(window.mailchimp.pixelLogEnabled) : '1',
            pixelLogMaxRows: window.mailchimp.pixelLogMaxRows ? parseInt(window.mailchimp.pixelLogMaxRows, 10) : 1000,
            pixelLogMaxRowsRaw: window.mailchimp.pixelLogMaxRows ? parseInt(window.mailchimp.pixelLogMaxRows, 10) : 1000,
            pixelLogRateLimitPerMin: window.mailchimp.pixelLogRateLimitPerMin ? parseInt(window.mailchimp.pixelLogRateLimitPerMin, 10) : 60,
            pixelLogRateLimitPerMinRaw: window.mailchimp.pixelLogRateLimitPerMin ? parseInt(window.mailchimp.pixelLogRateLimitPerMin, 10) : 60,
            // Custom checkout controllers — array of php_self strings the
            // CHECKOUT_STARTED bundle should also load on (beyond stock
            // 'order' + page_name='checkout' auto-detection). Stored
            // server-side as a comma-separated string; converted to array
            // here for the Multiselect tags input. Watcher (in `watch:`
            // below) calls saveSettings on change. Serializes back to
            // comma-string in saveSettings's payload.
            pixelCheckoutControllers: (typeof window.mailchimp.pixelCheckoutControllers === 'string'
                && window.mailchimp.pixelCheckoutControllers.trim() !== '')
                ? window.mailchimp.pixelCheckoutControllers.split(',').map(function (s) { return s.trim(); }).filter(Boolean)
                : ['opc'],
            // Common 3rd-party checkout module controllers — surfaced as
            // suggestions in the dropdown so admin can click to add a
            // known value without typing. `create-option` on the
            // Multiselect still allows free-text entries for modules not
            // in this list. Object shape matches what @vueform/multiselect
            // expects + parallels orderStates' structure.
            pixelCheckoutControllerSuggestions: [
                { value: 'opc', label: 'opc — OnePageCheckoutPS (default php_self)' },
                { value: 'module-onepagecheckoutps-opc', label: 'module-onepagecheckoutps-opc — OnePageCheckoutPS (module front controller)' },
                { value: 'module-supercheckout-supercheckout', label: 'module-supercheckout-supercheckout — SuperCheckout' },
                { value: 'module-quickcheckout-quickcheckout', label: 'module-quickcheckout-quickcheckout — QuickCheckout' },
                { value: 'module-cartfusion-cartfusion', label: 'module-cartfusion-cartfusion — CartFusion' }
            ],
            // Custom order-confirmation controllers — parallel of
            // pixelCheckoutControllers for the PURCHASED event. Default empty
            // since most 3rd-party modules extend OrderConfirmationController
            // and fire the displayOrderConfirmation hook naturally; the BO
            // list is the escape hatch for the minority that break both
            // conventions (php_self !== 'order-confirmation' AND
            // page_name !== 'order-confirmation').
            pixelOrderConfirmationControllers: (typeof window.mailchimp.pixelOrderConfirmationControllers === 'string'
                && window.mailchimp.pixelOrderConfirmationControllers.trim() !== '')
                ? window.mailchimp.pixelOrderConfirmationControllers.split(',').map(function (s) { return s.trim(); }).filter(Boolean)
                : [],
            // Known 3rd-party module order-confirmation controllers. Most
            // OPCs fire the displayOrderConfirmation hook properly (via
            // parent::initContent()) so users rarely need to add anything.
            // These are the published php_self values for modules that
            // override the standard pattern — listing as suggestions
            // so admins can click rather than typing.
            pixelOrderConfirmationControllerSuggestions: [
                { value: 'module-onepagecheckoutps-orderconfirmation', label: 'module-onepagecheckoutps-orderconfirmation — OnePageCheckoutPS confirmation' },
                { value: 'module-supercheckout-confirmation', label: 'module-supercheckout-confirmation — SuperCheckout confirmation' },
                { value: 'module-quickcheckout-confirmation', label: 'module-quickcheckout-confirmation — QuickCheckout confirmation' }
            ],
            pixelAvailable: window.mailchimp.pixelAvailable || null,
            pixelLogRows: [],
            pixelLogTotal: 0,
            pixelLogLimit: 50,
            pixelLogOffset: 0,
            pixelLogLoading: false,
            pixelLogClearing: false,
            pixelLogFilters: {
                severity: '', source: '', event_type: '',
                // New free-text + date-range filters. message + ip search are
                // substring/prefix matches (server-side via LIKE); from/to are
                // ISO date strings (yyyy-mm-dd) from the <input type="date">.
                message: '', ip: '', from: '', to: ''
            },
            // Single source of truth for the merchant-friendly labels shown
            // in the Pixel-log filter dropdown + the table cell. Drives both
            // the optgroup hierarchy and the eventTypeLabel() lookup. Keeping
            // it in `data` rather than as a static map means any future
            // event types only need adding here — both surfaces pick them up.
            pixelEventGroups: [
                { label: (window.mailchimp.translations && window.mailchimp.translations.evGroupAvailability) || 'Pixel availability', items: [
                    { value: 'PIXEL_AVAILABLE',     label: (window.mailchimp.translations && window.mailchimp.translations.evPixelAvailable)     || 'Pixel available' },
                    { value: 'PIXEL_NOT_AVAILABLE', label: (window.mailchimp.translations && window.mailchimp.translations.evPixelNotAvailable)  || 'Pixel not available' },
                    { value: 'SDK_LOAD_FAILED',     label: (window.mailchimp.translations && window.mailchimp.translations.evSdkLoadFailed)      || 'SDK load failed' },
                    { value: 'SDK_TIMEOUT',         label: (window.mailchimp.translations && window.mailchimp.translations.evSdkTimeout)         || 'SDK timeout' }
                ]},
                { label: (window.mailchimp.translations && window.mailchimp.translations.evGroupIdentify) || 'Customer identification', items: [
                    { value: 'IDENTIFY',            label: (window.mailchimp.translations && window.mailchimp.translations.evIdentify)           || 'Customer identified (login / logout / switch)' }
                ]},
                { label: (window.mailchimp.translations && window.mailchimp.translations.evGroupStorefront) || 'Storefront events (compact at default, full at verbose)', items: [
                    { value: 'PAGE_VIEWED',            label: (window.mailchimp.translations && window.mailchimp.translations.evPageViewed)         || 'Page viewed' },
                    { value: 'PRODUCT_VIEWED',         label: (window.mailchimp.translations && window.mailchimp.translations.evProductViewed)      || 'Product viewed' },
                    { value: 'PRODUCT_ADDED_TO_CART',  label: (window.mailchimp.translations && window.mailchimp.translations.evProductAddedToCart) || 'Product added to cart' },
                    { value: 'CART_VIEWED',            label: (window.mailchimp.translations && window.mailchimp.translations.evCartViewed)         || 'Cart viewed' },
                    { value: 'CHECKOUT_STARTED',       label: (window.mailchimp.translations && window.mailchimp.translations.evCheckoutStarted)    || 'Checkout started' },
                    { value: 'PURCHASED',              label: (window.mailchimp.translations && window.mailchimp.translations.evPurchased)          || 'Order placed' }
                ]},
                { label: (window.mailchimp.translations && window.mailchimp.translations.evGroupAdmin) || 'Admin actions', items: [
                    { value: 'SCRIPT_CACHED',          label: (window.mailchimp.translations && window.mailchimp.translations.evScriptCached)       || 'Script fetched & cached' },
                    { value: 'SCRIPT_CLEARED',         label: (window.mailchimp.translations && window.mailchimp.translations.evScriptCleared)      || 'Script cleared' },
                    { value: 'SCRIPT_VERIFIED',        label: (window.mailchimp.translations && window.mailchimp.translations.evScriptVerified)     || 'Script installation verified' },
                    { value: 'PIXEL_STATE_REFRESH',    label: (window.mailchimp.translations && window.mailchimp.translations.evPixelStateRefresh)  || 'Pixel feature state synced' },
                    { value: 'PIXEL_ENABLED',          label: (window.mailchimp.translations && window.mailchimp.translations.evPixelEnabled)       || 'Pixel enabled' },
                    { value: 'PIXEL_DISABLED',         label: (window.mailchimp.translations && window.mailchimp.translations.evPixelDisabled)      || 'Pixel disabled' },
                    { value: 'PIXEL_AUTO_ENABLED',     label: (window.mailchimp.translations && window.mailchimp.translations.evPixelAutoEnabled)   || 'Pixel auto-enabled on setup' },
                    { value: 'PIXEL_SETTING_CHANGED',  label: (window.mailchimp.translations && window.mailchimp.translations.evPixelSettingChanged) || 'Pixel setting changed' },
                    { value: 'LOG_CLEARED',            label: (window.mailchimp.translations && window.mailchimp.translations.evLogCleared)         || 'Log cleared' },
                    { value: 'CSP_PRESENT',            label: (window.mailchimp.translations && window.mailchimp.translations.evCspPresent)         || 'CSP — pixel allowed' },
                    { value: 'CSP_MISSING',            label: (window.mailchimp.translations && window.mailchimp.translations.evCspMissing)         || 'CSP — pixel blocked' },
                    { value: 'CSP_NO_CSP',             label: (window.mailchimp.translations && window.mailchimp.translations.evCspNoCsp)           || 'CSP — none configured' }
                ]},
                // Theme + storefront-bridge integration health. Surfaced for
                // support: PIXEL_HOOK_MISSING tells a merchant their theme
                // dropped the template hook (custom theme or PS 1.7.0.6
                // classic); PIXEL_HOOK_WIRED confirms the theme is properly
                // integrated; QUICKVIEW_DATA_INCOMPLETE flags rare cases
                // where the quickview modal didn't expose enough data for
                // the bridge to fire a meaningful event.
                { label: (window.mailchimp.translations && window.mailchimp.translations.evGroupIntegration) || 'Theme & integration health', items: [
                    { value: 'PIXEL_HOOK_WIRED',         label: (window.mailchimp.translations && window.mailchimp.translations.evPixelHookWired)       || 'Theme hook detected' },
                    { value: 'PIXEL_HOOK_MISSING',       label: (window.mailchimp.translations && window.mailchimp.translations.evPixelHookMissing)     || 'Theme hook missing' },
                    { value: 'QUICKVIEW_DATA_INCOMPLETE', label: (window.mailchimp.translations && window.mailchimp.translations.evQuickviewIncomplete) || 'Quickview data incomplete' }
                ]}
            ],
            orderStates: window.mailchimp.orderStates,
            storeInstanceMode: window.mailchimp.multiInstanceMode,
            multiInstanceMode: window.mailchimp.multiInstanceMode,
            cronjobBasedSync: window.mailchimp.cronjobBasedSync,
            syncProducts: window.mailchimp.syncProducts,
            syncCustomers: window.mailchimp.syncCustomers,
            syncCartRules: window.mailchimp.syncCartRules,
            syncOrders: window.mailchimp.syncOrders,
            syncCarts: window.mailchimp.syncCarts,
            syncCartsPassw: window.mailchimp.syncCartsPassw,
            syncNewsletterSubscribers: window.mailchimp.syncNewsletterSubscribers,
            statusForPending: (window.mailchimp.statusForPending),
            statusForRefunded: (window.mailchimp.statusForRefunded),
            statusForCancelled: (window.mailchimp.statusForCancelled),
            statusForShipped: (window.mailchimp.statusForShipped),
            statusForPaid: (window.mailchimp.statusForPaid),
            productDescriptionField: window.mailchimp.productDescriptionField,
            existingOrderSyncStrategy: window.mailchimp.existingOrderSyncStrategy,
            productSyncFilterActive: (window.mailchimp.productSyncFilterActive),
            productSyncFilterVisibility: (window.mailchimp.productSyncFilterVisibility),
            customerSyncFilterEnabled: (window.mailchimp.customerSyncFilterEnabled),
            customerSyncFilterNewsletter: (window.mailchimp.customerSyncFilterNewsletter),
            customerSyncFilterPeriod: window.mailchimp.customerSyncFilterPeriod,
            customerSyncTagDefaultGroup: window.mailchimp.customerSyncTagDefaultGroup,
            customerSyncTagGender: window.mailchimp.customerSyncTagGender,
            customerSyncTagLanguage: window.mailchimp.customerSyncTagLanguage,
            customerSyncCompanyFromAddress: window.mailchimp.customerSyncCompanyFromAddress,
            b2bModeEnabled: window.mailchimp.b2bModeEnabled,
            companyFieldStatus: window.mailchimp.companyFieldStatus || 'unknown',
            companyFieldWorking: false,
            // Last create attempt's failure reason — drives the cap-specific
            // hint. Session-only: a fresh page load starts with a clean slate.
            companyFieldError: '',
            cartRuleSyncFilterStatus: (window.mailchimp.cartRuleSyncFilterStatus),
            cartRuleSyncFilterExpiration: (window.mailchimp.cartRuleSyncFilterExpiration),
            newsletterSubscriberSyncFilterPeriod: window.mailchimp.newsletterSubscriberSyncFilterPeriod,
            productImageSize: window.mailchimp.productImageSize,
            token: window.mailchimp.token,
            validApiKey: window.mailchimp.validApiKey,
            accountInfo: window.mailchimp.accountInfo,
            numberOfCartRulesToSync: window.mailchimp.numberOfCartRulesToSync,
            numberOfCustomersToSync: window.mailchimp.numberOfCustomersToSync,
            numberOfProductsToSync: window.mailchimp.numberOfProductsToSync,
            numberOfOrdersToSync: window.mailchimp.numberOfOrdersToSync,
            numberOfNewsletterSubscribersToSync: window.mailchimp.numberOfNewsletterSubscribersToSync,
            logQueue: window.mailchimp.logQueue,
            queueStep: window.mailchimp.queueStep,
            queueStepRaw: window.mailchimp.queueStep,
            queueStepToastTimer: null,
            queueAttempt: window.mailchimp.queueAttempt,
            queueAttemptRaw: window.mailchimp.queueAttempt,
            queueAttemptToastTimer: null,
			logCronjob: window.mailchimp.logCronjob,
			cronjobLogContent: window.mailchimp.cronjobLogContent,
			lastSyncedProductId: window.mailchimp.lastSyncedProductId,
			lastSyncedCustomerId: window.mailchimp.lastSyncedCustomerId,
            lastSyncedCartRuleId: window.mailchimp.lastSyncedCartRuleId,
			lastSyncedOrderId: window.mailchimp.lastSyncedOrderId,
            lastSyncedCartId: window.mailchimp.lastSyncedCartId,
			lastSyncedNewsletterSubscriberId: window.mailchimp.lastSyncedNewsletterSubscriberId,
			lastCronjob: window.mailchimp.lastCronjob,
			lastCronjobExecutionTime: window.mailchimp.lastCronjobExecutionTime,
			totalJobs: window.mailchimp.totalJobs,
            showDashboardStats: window.mailchimp.showDashboardStats,
            statAllCodes: 0,
            statUsedCodes: 0,
            statConversion: 0,
            statFrequency: 0,
            promoOverridesEnabled: window.mailchimp.promoOverridesEnabled,
            promoOverridesAutoInstalled: window.mailchimp.promoOverridesAutoInstalled || false,
            promoOverridesConflicts : window.mailchimp.promoOverridesConflicts || false,
            overrideConflicts: [],
            showManualInstallationDialog: false,
        }
    }
});
window.app.component('multiselect', window.VueformMultiselect)
window.app.mount('#app')
