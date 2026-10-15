{*
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
 *}
 <div v-cloak v-if="validApiKey" class="panel panel-pixel" v-show="currentPage === 'pixel'">
    <h3 class="panel-heading">
        <span class="panel-heading-icon-container">
            <i class="las la-user-secret la-2x"></i>
            {l s='Mailchimp Tracking Pixel' mod='mailchimppro'}
        </span>
        <div class="panel-heading-action">
            <button v-on:click="saveSettings"
                    v-if="validApiKey"
                    title="{l s='Save settings' mod='mailchimppro'}"
                    class="list-toolbar-btn btn btn-success">
                <i class="las la-save la-2x"></i>
                <span>{l s='Save' mod='mailchimppro'}</span>
            </button>
        </div>
    </h3>
    <div class="panel-body">
        <h2>{l s='Pixel tracking' mod='mailchimppro'}</h2>

        {* Master Yes/No toggle — mirrors the Sync-products tab pattern.
           Calls togglePixel(true|false) which:
             1. POSTs enable-pixel or disable-pixel to Mailchimp
             2. Re-fetches the connected_site so the cached script fragment
                matches the new state
             3. Updates local pixelAvailable + scriptCachedDate
           Quick double-clicks are debounced via pixelToggleSaving flag. *}
        <div class="form-group pixel-master-toggle deep-link-target" id="pixel-master-toggle-section">
            <div class="clearfix"></div>
            <div class="btn-group large" role="group">
                <button type="button" class="btn"
                        v-on:click="togglePixel(true)"
                        :class="pixelEnabled === true ? 'btn-primary' : 'btn-default'">
                    {l s='Yes' mod='mailchimppro'}
                </button>
                <button type="button" class="btn"
                        v-on:click="togglePixel(false)"
                        :class="pixelEnabled === false ? 'btn-primary' : 'btn-default'">
                    {l s='No' mod='mailchimppro'}
                </button>
            </div>
            {* Mailchimp legal notice — mirrors the disclaimer shown next to
               the equivalent "Turn Pixel On" control in Mailchimp's own
               integration management UIs (e.g. their Wix integration). By
               turning the toggle to Yes the merchant consents to Mailchimp's
               Additional Terms governing the Pixel feature. *}
            <p class="help-block" style="margin-top: 8px; color: inherit;">
                {{l s='By turning on site tracking, you agree to Mailchimp\'s [link]additional terms[/link].'
                    sprintf=[
                        '[link]' => '<a href="https://mailchimp.com/legal/additional-terms/" target="_blank" rel="noreferrer noopener" class="link-mc fw-600 text-decoration-underline">',
                        '[/link]' => '</a>'
                    ]
                    mod='mailchimppro'}|unescape:'html'}
            </p>

            <p class="help-block" style="margin-top: 4px;">{l s='When set to Yes, pixel events are sent to Mailchimp. When set to No, tracking events stop firing; the script still loads so Mailchimp popups and signup forms keep working.' mod='mailchimppro'}</p>

            <div v-if="showCdnPropagationBanner" class="alert alert-info pixel-cdn-propagation-banner">
                <p><strong>⏳ {l s='Pixel script was just (re-)fetched.' mod='mailchimppro'}</strong></p>
                <p>{l s='Mailchimp\'s CDN may take up to 5 minutes to propagate any Mailchimp-side pixel state changes (e.g., the master pixel toggle being flipped, an audience swap, or a fresh-install setup). If you only re-fetched the same script without changing pixel state, you can ignore this notice. Otherwise: wait a few minutes then hard-reload (Ctrl+F5) the storefront if track events aren\'t firing yet.' mod='mailchimppro'}</p>
                <p style="margin-bottom: 0;"><em>{l s='This notice auto-hides after the 5-minute propagation window.' mod='mailchimppro'}</em></p>
            </div>

            <div class="alert alert-info">
                <p>{l s='The Mailchimp Pixel is a small JavaScript snippet (loaded from chimpstatic.com) that captures shopper behavior on your storefront and sends it to Mailchimp. This complements the existing server-side ecommerce sync — events that the server-side sync cannot capture (page views, browse abandonment) are what the pixel adds.' mod='mailchimppro'}</p>
                <hr>
                <p style="margin-bottom: 0.5rem"><strong>{l s='You decide whether and how the Mailchimp pixel runs on your store.' mod='mailchimppro'}</strong></p>
                <p style="margin-bottom: 0">{{l s='The pixel is [b]off[/b] by default — flip [masterlink][b]Master toggle[/b][/masterlink] to enable it. [consentlink][b]Strict consent[/b][/consentlink] is also off by default; in EU / UK / Switzerland / Brazil and other prior-consent jurisdictions, turn it [b]on[/b] before enabling the pixel in production and wire a consent banner per the [faqlink][b]consent-banner FAQ entry[/b][/faqlink]. You are the data controller for your store; we surface the choice — the legal posture for your jurisdiction is yours to set.'
                    sprintf=[
                        '[b]' => '<strong>', '[/b]' => '</strong>',
                        '[masterlink]' => '<a href="#pixel-master-toggle-section" class="link-mc fw-600 text-decoration-underline" @click.prevent="setCurrentPage(\'pixel\', \'pixel-master-toggle-section\')">',
                        '[/masterlink]' => '</a>',
                        '[consentlink]' => '<a href="#pixel-require-consent-section" class="link-mc fw-600 text-decoration-underline" @click.prevent="setCurrentPage(\'pixel\', \'pixel-require-consent-section\')">',
                        '[/consentlink]' => '</a>',
                        '[faqlink]' => '<a href="#faq" class="link-mc fw-600 text-decoration-underline" @click.prevent="setCurrentPage(\'faq\', \'accordion-heading18\')">',
                        '[/faqlink]' => '</a>'
                    ]
                    mod='mailchimppro'}|unescape:'html'}</p>
            </div>
        </div>

        <hr>
        
        {* Pixel health status — single-glance diagnostic. Placed above the Fetch-script
           block so admins see the current state at the top of the tab. Same data
           sources as the advanced-settings section, intentionally duplicated so
           admins on this tab see the full picture. *}
        <div class="form-group pixel-status-container deep-link-target" id="pixel-health-panel-section">
            <div class="alert" :class="pixelStatusAlertClass()">
                {* State icon inlined into the heading + full sentence body
                   beneath. Three states (enabled / disabled / not-determined)
                   each carry their own emoji + message; the surrounding alert
                   class (success / danger / info / warning) is driven by
                   pixelStatusAlertClass() in main.js. *}
                <h4>
                    {l s='Pixel status' mod='mailchimppro'}
                    <span v-if="!pixelAvailable">❓</span>
                    <span v-else-if="pixelAvailable.enabled === true && pixelAvailable.recent_negative_count > 0">⚠️</span>
                    <span v-else-if="pixelAvailable.enabled === true">✔️</span>
                    <span v-else>❌</span>
                </h4>
                <p style="font-size: 16px;">
                    <span v-if="!pixelAvailable">{l s='Pixel state not yet determined — sync your store to detect.' mod='mailchimppro'}</span>
                    <span v-else-if="pixelAvailable.enabled === true && pixelAvailable.recent_negative_count > 0">
                        {l s='Pixel is enabled, but some visitors are reporting issues.' mod='mailchimppro'}
                        ({{ pixelAvailable.recent_negative_count }}/{{ pixelAvailable.negative_quorum }}
                        {l s='distinct visitors in the last 15 minutes reported the pixel SDK not loading correctly. The status will flip to "not enabled" once the quorum is reached. Check CSP / ad-blocker / connected-site state. See FAQ for details.' mod='mailchimppro'})
                    </span>
                    <span v-else-if="pixelAvailable.enabled === true">{l s='Pixel is enabled for this connected site!' mod='mailchimppro'}</span>
                    <span v-else>{l s='Pixel is not enabled for this connected site!' mod='mailchimppro'}</span>
                </p>
                {* Last-verified timestamp + source. Passive informational line —
                   absent on fresh-install state, shown once the verdict probe
                   or admin Sync has populated MAILCHIMP_PIXEL_AVAILABLE. Source
                   tag (api / fo_bridge) tells the merchant where the value came
                   from, useful in support tickets. *}
                <p class="help-block" v-if="pixelAvailable && pixelAvailable.last_checked_at" style="margin-top: 0px; color: inherit;">
                    {l s='Last verified:' mod='mailchimppro'} {{ pixelAvailable.last_checked_at }}
                    <span v-if="pixelAvailable.source === 'fo_bridge'">({l s='from storefront visitors' mod='mailchimppro'})</span>
                    <span v-else-if="pixelAvailable.source === 'api'">({l s='from Mailchimp API sync' mod='mailchimppro'})</span>
                </p>
                {* Conditional health indicators — only shown when something
                   needs attention. Healthy state stays silent so the panel
                   doesn't clutter with green checkmarks. *}
                <ul>
                    <li v-if="!scriptCachedDate">
                        {l s='Script cached:' mod='mailchimppro'}
                        <b style="color: #d9534f;">{l s='No' mod='mailchimppro'}</b>
                    </li>
                    <li v-if="!(listId && storeSynced && scriptCachedDate)">
                        {l s='Mailchimp integration script loaded on this storefront:' mod='mailchimppro'}
                        <b style="color: #d9534f;">{l s='No' mod='mailchimppro'}</b>
                    </li>
                    <li v-if="cspStatus && cspStatus.csp_present === true && cspStatus.compatible === false">
                        {l s='CSP status:' mod='mailchimppro'}
                        <b style="color: #d9534f;">{l s='WARNING: missing chimpstatic.com in' mod='mailchimppro'}
                            <span v-for="(d, i) in cspStatus.missing_directives" v-bind:key="d">{{ i ? ', ' : '' }}{{ d }}</span>
                        </b>
                    </li>
                </ul>
            </div>
        </div>

        <hr>

        <div class="form-group pixel-information">
            <h3 class="modal-title text-info">{l s='What gets tracked' mod='mailchimppro'}</h3>
            <p>{l s='The pixel sends the following events:' mod='mailchimppro'}</p>
            <ul class="pixel-event-list">
                <li><b>{l s='Page viewed' mod='mailchimppro'}</b> <code class="pixel-event-tech">PAGE_VIEWED</code> — {l s='every storefront page load (controlled by the Automatic page-view tracking toggle below).' mod='mailchimppro'}</li>
                <li><b>{l s='Product viewed' mod='mailchimppro'}</b> <code class="pixel-event-tech">PRODUCT_VIEWED</code> — {l s='product detail page load.' mod='mailchimppro'}</li>
                <li><b>{l s='Product added to cart' mod='mailchimppro'}</b> <code class="pixel-event-tech">PRODUCT_ADDED_TO_CART</code> — {l s='when a visitor adds an item to their cart.' mod='mailchimppro'}</li>
                <li><b>{l s='Cart viewed' mod='mailchimppro'}</b> <code class="pixel-event-tech">CART_VIEWED</code> — {l s='cart page load.' mod='mailchimppro'}</li>
                <li><b>{l s='Checkout started' mod='mailchimppro'}</b> <code class="pixel-event-tech">CHECKOUT_STARTED</code> — {l s='first step of checkout.' mod='mailchimppro'}</li>
                <li><b>{l s='Order placed' mod='mailchimppro'}</b> <code class="pixel-event-tech">PURCHASED</code> — {l s='order confirmation page.' mod='mailchimppro'}</li>
                <li><b>{l s='Customer identified' mod='mailchimppro'}</b> <code class="pixel-event-tech">identify()</code> — {l s='whenever a logged-in customer is detected.' mod='mailchimppro'}</li>
            </ul>
            <div class="pixel-notes-container alert alert-warning">
                <h4>{l s='Things to keep in mind:' mod='mailchimppro'}</h4>
                <ul>
                    <li>{l s='You control pixel tracking with the Yes/No toggle at the top of this tab. While disabled, the script still loads (so Mailchimp popups and signup forms keep working) but no tracking events are sent.' mod='mailchimppro'}</li>
                    <li>{l s='Ad-blockers and tracking-protection extensions block chimpstatic.com for ~25-40% of visitors. Pixel is supplementary; the server-side ecommerce sync remains your authoritative data source.' mod='mailchimppro'}</li>
                    <li>{l s='If your storefront enforces a Content-Security-Policy, chimpstatic.com must be allowed in both script-src and connect-src.' mod='mailchimppro'}</li>
                </ul>
            </div>
        </div>

        <hr>

        <div :class="pixelEnabled === true ? 'pixel-settings-container' : 'pixel-settings-container pixel-disabled'">
        {* Everything from here through the closing wrapper before `<div class="pixel-logs-container">`
           gets dimmed when pixel is disabled. Includes intro text + status panel +
           settings form + fetch-script panel. The log viewer outside stays
           interactive even when disabled — admins still need to read past events. *}

        {* CSP warning — independent of the pixel-availability state because a
           blocking CSP will silently kill events even when Mailchimp has the
           pixel enabled for the account. Same Vue data (cspStatus, cspChecking)
           and same checkPixelCspNow() method as the Advanced settings version,
           so any check from either tab updates both. *}
        <div v-if="listId && storeSynced && cspStatus && cspStatus.checked === false && cspStatus.reason && cspStatus.reason !== 'no_script_cached' && cspStatus.reason !== 'curl_unavailable' && cspStatus.reason !== 'no_fo_url'"
             class="alert alert-warning pixel-csp-unreachable"
             style="margin-bottom: 20px;">
            <p><strong>{l s='We couldn\'t reach your storefront to check its Content-Security-Policy.' mod='mailchimppro'}</strong></p>
            <p>
                {l s='Reason:' mod='mailchimppro'} <code v-text="cspStatus.reason"></code><span v-if="cspStatus.curl_errno"> (cURL errno <span v-text="cspStatus.curl_errno"></span>)</span>
            </p>
            <p>{l s='This is usually a transient network or self-loopback issue. The pixel itself is unaffected — this probe only diagnoses CSP misconfiguration. Click below to retry.' mod='mailchimppro'}</p>
            <button type="button"
                    class="btn btn-default btn-sm script-csp-recheck-btn"
                    v-on:click="checkPixelCspNow"
                    v-bind:disabled="cspChecking">
                <span v-if="!cspChecking"><i class="icon-refresh"></i> {l s='Re-check now' mod='mailchimppro'}</span>
                <span v-else>{l s='Checking…' mod='mailchimppro'}</span>
            </button>
        </div>

        <div v-if="listId && storeSynced && cspStatus && cspStatus.csp_present === true && cspStatus.compatible === false"
             class="alert alert-warning pixel-csp-warning"
             style="margin-bottom: 20px;">
            <p><strong>{l s='Your storefront\'s Content-Security-Policy may block the Mailchimp pixel.' mod='mailchimppro'}</strong></p>
            <p>
                {l s='The following CSP directives are missing chimpstatic.com:' mod='mailchimppro'}
                <b><span v-for="(d, i) in cspStatus.missing_directives" v-bind:key="d">{{ i ? ', ' : '' }}{{ d }}</span></b>
            </p>
            <p>{l s='Add chimpstatic.com to those directives in your web server config (Apache .htaccess, nginx, or your CSP-managing module). Without this fix the pixel script will load, but events may be silently blocked by the browser.' mod='mailchimppro'}</p>
            <button type="button"
                    class="btn btn-default btn-sm script-csp-recheck-btn"
                    v-on:click="checkPixelCspNow"
                    v-bind:disabled="cspChecking">
                <span v-if="!cspChecking"><i class="icon-refresh"></i> {l s='Re-check now' mod='mailchimppro'}</span>
                <span v-else>{l s='Checking…' mod='mailchimppro'}</span>
            </button>
        </div>

        

        

        {* The Fetch-script panel + CSP-warning block live exclusively on the
           Advanced settings tab now (still rendered there via _fetch_script_block.tpl).
           This Pixel tab keeps just the inline "Re-fetch script" button inside the
           top-of-tab state alerts above — same Vue method (syncStoresScript), same
           reactive scriptCachedDate / cspStatus state — so any fetch from either tab
           updates both surfaces. Removing the duplicate panel here keeps this tab
           focused on "what's the state?" instead of repeating the operational copy. *}


        {* Pixel configuration form — autotrack, identify format, log toggle, log max rows, rate limit.
           Uses the same form-horizontal grid + control-label col-lg-2 + ps-switch / input-group + addon
           pattern as the Cronjob tab so the two tabs share visual identity. *}
        <div class="form-horizontal pixel-settings-form">
            <div class="panel-heading">{l s='Pixel settings' mod='mailchimppro'}</div>

            <div class="form-group pixel-autotrack">
                <label class="control-label col-lg-2">
                    <span class="label-tooltip" data-toggle="tooltip" data-html="true" title="" data-original-title="{l s='Disabling this only stops the automatic page-view event. The other 5 events (Product viewed, Product added to cart, Cart viewed, Checkout started, Order placed) still fire because the module calls pixel.api.track() explicitly. Technical event names: PAGE_VIEWED, PRODUCT_VIEWED, PRODUCT_ADDED_TO_CART, CART_VIEWED, CHECKOUT_STARTED, PURCHASED.' mod='mailchimppro'}">{l s='Automatic page-view tracking' mod='mailchimppro'}</span>
                </label>
                <div class="col-lg-8">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" name="autotrackEnabled" id="autotrackEnabled_on" value="1" v-model="autotrackEnabled">
                        <label for="autotrackEnabled_on">{l s='Yes' mod='mailchimppro'}</label>
                        <input type="radio" name="autotrackEnabled" id="autotrackEnabled_off" value="0" v-model="autotrackEnabled">
                        <label for="autotrackEnabled_off">{l s='No' mod='mailchimppro'}</label>
                        <a class="slide-button btn"></a>
                    </span>
                    <p class="help-block">{l s='When enabled (default), the Mailchimp SDK records a "page viewed" event automatically on every storefront page load. When disabled, no PAGE_VIEWED events are sent. The other pixel events (product view, add to cart, cart view, checkout, purchase) are not affected by this toggle.' mod='mailchimppro'}</p>
                </div>
            </div>

            <div class="form-group pixel-require-consent deep-link-target" id="pixel-require-consent-section">
                <label class="control-label col-lg-2">
                    <span class="label-tooltip" data-toggle="tooltip" data-html="true" title="" data-original-title="{l s='Strict-mode toggle for privacy compliance. Default OFF — admin enables based on the jurisdiction the store operates in. When ON, the module writes mc_user_optin=false BEFORE MC.js loads, so the SDK defaults to opted-out; your CMP (Consent Management Platform — your cookie banner / GDPR module) must call window.$mcSite.enableOptIn() (or write mc_user_optin=true) on accept. When OFF, the SDK follows the opt-out model — tracking proceeds unless the visitor explicitly declined. See the alert below for guidance + the consent-banner FAQ entry for CMP wiring snippets. Consult legal counsel for your specific jurisdiction.' mod='mailchimppro'}">{l s='Require explicit consent before tracking' mod='mailchimppro'}</span>
                </label>
                <div class="col-lg-8">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" name="pixelRequireConsent" id="pixelRequireConsent_on" value="1" v-model="pixelRequireConsent">
                        <label for="pixelRequireConsent_on">{l s='Yes' mod='mailchimppro'}</label>
                        <input type="radio" name="pixelRequireConsent" id="pixelRequireConsent_off" value="0" v-model="pixelRequireConsent">
                        <label for="pixelRequireConsent_off">{l s='No' mod='mailchimppro'}</label>
                        <a class="slide-button btn"></a>
                    </span>
                    <p class="help-block">{l s='OFF (default): opt-out model — pixel tracks by default; CMP only needs to set mc_user_optin=false on decline. ON: pixel does NOT track until the visitor accepts via your consent banner — the module pre-sets mc_user_optin=false before MC.js loads and your banner must flip it on accept. See the alert below for jurisdiction guidance and the consent-banner FAQ entry for CMP wiring snippets.' mod='mailchimppro'}</p>

                    <div class="alert alert-warning" style="margin-top: 0.75rem; margin-bottom: 0">
                        <strong>{l s='Pick the right mode by your local consent regime, NOT by whether you have a CMP installed' mod='mailchimppro'}:</strong>
                        <ul style="margin-top: 0.5rem; margin-bottom: 0">
                            <li>{{l s='[b]Prior-consent regime[/b] (your local law requires explicit visitor consent BEFORE any tracking; commonly called "opt-in" or "GDPR-style", e.g. EU / UK / Switzerland / Brazil) — switch this [b]ON[/b]. You must install and wire a consent banner module. Do [b]NOT[/b] leave it OFF as a workaround for "no events firing" — the correct fix is to install a CMP (see the [link][b]consent-banner FAQ entry[/b][/link] for wiring snippets covering Cookiebot, OneTrust, Iubenda, CookieYes, Klaro, DIY banners — and [b]gdprpro 2.5.0+, which auto-wires with no manual setup needed[/b]).'
                                sprintf=[
                                    '[b]' => '<strong>', '[/b]' => '</strong>',
                                    '[link]' => '<a href="#faq" class="link-mc fw-600 text-decoration-underline" @click.prevent="setCurrentPage(\'faq\', \'accordion-heading18\')">',
                                    '[/link]' => '</a>'
                                ]
                                mod='mailchimppro'}|unescape:'html'}</li>
                            <li>{{l s='[b]Opt-out regime[/b] (your local law permits tracking by default unless the visitor declines, e.g. most U.S. states) — leave OFF. Pixel will track by default; your CMP (if you have one) only needs to set [code]mc_user_optin=false[/code] on decline.'
                                sprintf=['[b]' => '<strong>', '[/b]' => '</strong>', '[code]' => '<code>', '[/code]' => '</code>']
                                mod='mailchimppro'}|unescape:'html'}</li>
                            <li>{{l s='[b]If unsure[/b] — switch ON and consult legal counsel for the jurisdictions you operate in. Privacy regulations vary by country and U.S. state and evolve over time; the module does not track regulatory changes for you.'
                                sprintf=['[b]' => '<strong>', '[/b]' => '</strong>']
                                mod='mailchimppro'}|unescape:'html'}</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="form-group pixel-sdk-settle-delay">
                <label class="control-label col-lg-2">
                    <span class="label-tooltip" data-toggle="tooltip" data-html="true" title="" data-original-title="{l s='Milliseconds the bridge waits after the Mailchimp Pixel SDK reports it is loaded, before firing the first track or identify call. Covers the brief window where the SDK\'s public installed flag has flipped but its internal initialization is not quite finished. Bump higher (750 or 1000) if you see &quot;Pixel SDK ERROR Pixel not initialized&quot; in the browser console — slower CPUs, cold caches, and heavy pages can need more. Values outside the allowed range are silently clamped at save time.' mod='mailchimppro'}">{l s='SDK settle delay' mod='mailchimppro'}</span>
                </label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text"
                               name="sdkSettleDelayMs"
                               id="sdkSettleDelayMs"
                               required="required"
                               v-bind:minlength="String(sdkSettleDelayMsMin).length"
                               v-bind:maxlength="String(sdkSettleDelayMsMax).length"
                               v-model="sdkSettleDelayMsRaw"
                               @keypress="onDigitOnlyKeypress($event, sdkSettleDelayMsMin)"
                               @paste="onDigitOnlyPaste"
                               @blur="onSdkSettleDelayBlur">
                        <span class="input-group-addon">{l s='ms' mod='mailchimppro'}</span>
                    </div>
                    <p class="help-block">
                        {l s='Default:' mod='mailchimppro'} <strong>{{ sdkSettleDelayMsDefault }}</strong>.
                        {l s='Range:' mod='mailchimppro'} <strong>{{ sdkSettleDelayMsMin }}</strong> {l s='to' mod='mailchimppro'} <strong>{{ sdkSettleDelayMsMax }}</strong>.
                        {l s='Increase if "Pixel not initialized" console errors persist on your storefront (try 750 or 1000). See the FAQ for guidance on tuning.' mod='mailchimppro'}
                    </p>
                </div>
            </div>

            <hr>
            <h4 class="text-info">{l s='Customer identify' mod='mailchimppro'}</h4>

            <div class="form-group pixel-identify-format deep-link-target" id="pixel-identify-format-section">
                <label class="control-label col-lg-2">
                    <span class="label-tooltip" data-toggle="tooltip" data-html="true" title="" data-original-title="{l s='SHA256 hashing prevents the customer\'s plain email from appearing in the rendered page source, Network tab, browser extensions, or persisting in the visitor\'s localStorage. Mailchimp matches both formats against your audience contacts equivalently — choose EMAIL_SHA256 unless you specifically need plain-text consistency with the server-side ecommerce sync.' mod='mailchimppro'}">{l s='Customer identify() format' mod='mailchimppro'}</span>
                </label>
                <div class="col-lg-8">
                    <div class="radio">
                        <label>
                            <input type="radio" name="identifyFormat" value="EMAIL_SHA256" v-model="identifyFormat">
                            <b>EMAIL_SHA256</b> — {l s='hash the email before sending (privacy-first, recommended).' mod='mailchimppro'}
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio" name="identifyFormat" value="EMAIL" v-model="identifyFormat">
                            <b>EMAIL</b> — {l s='send the plain email (consistent with the existing server-side ecommerce sync, easier to debug). Note: the plain email also persists in the visitor\'s localStorage between sessions for identify-transition detection.' mod='mailchimppro'}
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-group pixel-identify-ajax-mode">
                <label class="control-label col-lg-2">
                    <span class="label-tooltip" data-toggle="tooltip" data-html="true" title="" data-original-title="{l s='Defensive mode for aggressive cache configurations. By default (off), the customer\'s identify value is embedded inline in the page — fast, zero extra HTTP requests, safe under PrestaShop\'s native cache + LiteSpeed Cache + login-aware Varnish. ENABLE this only if you run a cache-everything setup (Varnish/Cloudflare page rules that cache all HTML regardless of login state) — when enabled, the bridge fetches the identify value via a separate POST request that no cache will store, eliminating any possibility of one customer\'s identify value being served from cache to another visitor. Cost when on: one extra HTTP request per page load.' mod='mailchimppro'}">{l s='Cache-safe identify mode' mod='mailchimppro'}</span>
                </label>
                <div class="col-lg-8">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" name="identifyAjaxMode" id="identifyAjaxMode_on" value="1" v-model="identifyAjaxMode">
                        <label for="identifyAjaxMode_on">{l s='Yes' mod='mailchimppro'}</label>
                        <input type="radio" name="identifyAjaxMode" id="identifyAjaxMode_off" value="0" v-model="identifyAjaxMode">
                        <label for="identifyAjaxMode_off">{l s='No' mod='mailchimppro'}</label>
                        <a class="slide-button btn"></a>
                    </span>
                    <p class="help-block">{l s='Default: No (off). Only enable if your storefront sits behind a cache-everything proxy (aggressive Varnish, Cloudflare "Cache Everything" rule) — see the FAQ.' mod='mailchimppro'}</p>
                </div>
            </div>

            <div class="form-group pixel-identify-on-forms deep-link-target" id="pixel-form-identify-section">
                <label class="control-label col-lg-2">
                    <span class="label-tooltip" data-toggle="tooltip" data-html="true" title="" data-original-title="{l s='When enabled, the pixel fires identify() once a visitor submits an email on the newsletter signup, restock-alert, contact, or forgot-password form. Behavioral events on the rest of that browser session then attribute to that customer in Mailchimp\'s reports. Skipped silently when the visitor is already identified (logged-in customer or guest mid-checkout) — stronger identities take priority. Shared-browser trade-off: once a visitor identifies on a public terminal, subsequent visitors using the same browser would have their events attributed to the first visitor until the browser data is cleared. See the FAQ for details.' mod='mailchimppro'}">{l s='Fire identify on form submissions' mod='mailchimppro'}</span>
                </label>
                <div class="col-lg-8">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" name="identifyOnForms" id="identifyOnForms_on" value="1" v-model="identifyOnForms">
                        <label for="identifyOnForms_on">{l s='Yes' mod='mailchimppro'}</label>
                        <input type="radio" name="identifyOnForms" id="identifyOnForms_off" value="0" v-model="identifyOnForms">
                        <label for="identifyOnForms_off">{l s='No' mod='mailchimppro'}</label>
                        <a class="slide-button btn"></a>
                    </span>
                    <p class="help-block">{l s='Default: Yes (on). Identifies visitors on newsletter signup, restock alert, contact form, and password reset — only when no stronger identity (login or checkout) is already active.' mod='mailchimppro'}</p>
                </div>
            </div>

            <div class="form-group pixel-identify-form-selectors-custom pixel-identify-newsletter-selectors-custom"
                 :class="{ 'is-master-disabled': identifyOnForms !== '1' }">
                <label class="control-label col-lg-2" for="identifyNewsletterSelectorsCustom">
                    <span class="label-tooltip" data-toggle="tooltip" data-html="true" title="" data-original-title="{l s='The built-in default selectors are always active (visible as greyed-out entries when you open the dropdown) — they can\'t be added or removed individually; only the master toggle disables them. Use this field only if a custom theme\'s newsletter form isn\'t caught by the built-ins: type your own CSS selector or click a suggestion. Standard CSS selector syntax. Sanitized server-side — only alphanumerics and CSS selector punctuation accepted; 500-char total cap.' mod='mailchimppro'}">{l s='Newsletter form — extra selectors' mod='mailchimppro'}</span>
                </label>
                <div class="col-lg-8">
                    {literal}
                    <Multiselect
                        v-model="identifyNewsletterSelectorsCustom"
                        id="identifyNewsletterSelectorsCustom"
                        :options="identifyNewsletterAllOptions"
                        :groups="true"
                        :group-select="false"
                        mode="tags"
                        :searchable="true"
                        :create-option="true"
                        :close-on-select="false"
                        :hide-selected="false"
                        :can-clear="false"
                        placeholder="Type a CSS selector and press Enter to add"
                    >
                        <template #tag="{ option, handleTagRemove, disabled }">
                            <div
                                class="multiselect-tag is-user"
                                :class="identifyNewsletterSuggestedValuesSet[option.value] ? 'tag-suggested' : 'tag-custom'"
                            >
                                {{ option.value }}
                                <span
                                    v-if="!disabled"
                                    class="multiselect-tag-remove"
                                    @click="(e) => handleTagRemove(option, e)"
                                    @mousedown.prevent
                                >
                                    <span class="multiselect-tag-remove-icon"></span>
                                </span>
                            </div>
                        </template>
                        <template #option="{ option }">
                            <span
                                v-if="option.disabled"
                                class="builtin-default-pill"
                            >{{ option.label }}</span>
                            <span
                                v-else-if="!identifyNewsletterSuggestedValuesSet[option.value]"
                                class="custom-selector-pill"
                            >{{ option.label }}</span>
                            <span
                                v-else
                                class="suggested-selector-pill"
                            >{{ option.label }}</span>
                        </template>
                    </Multiselect>
                    {/literal}
                    <p class="help-block">{l s='Open the dropdown to see the built-in default selectors — they\'re always active and can\'t be added/removed (use the master toggle above to disable form-identify entirely). Add custom selectors below only if a theme\'s newsletter form isn\'t caught by the built-ins.' mod='mailchimppro'}</p>
                </div>
            </div>

            <div class="form-group pixel-identify-form-selectors-custom pixel-identify-restock-selectors-custom"
                 :class="{ 'is-master-disabled': identifyOnForms !== '1' }">
                <label class="control-label col-lg-2" for="identifyRestockSelectorsCustom">
                    <span class="label-tooltip" data-toggle="tooltip" data-html="true" title="" data-original-title="{l s='Click-event watcher for the restock-alert (ps_emailalerts) button. The built-in defaults catch ps_emailalerts on both classic and Hummingbird themes (button selectors, not form selectors — restock is JS-driven AJAX, not a real form). Add custom selectors only if a 3rd-party restock module or custom theme override isn\'t caught by the built-ins. Sanitized server-side; 500-char total cap.' mod='mailchimppro'}">{l s='Restock-alert button — extra selectors' mod='mailchimppro'}</span>
                </label>
                <div class="col-lg-8">
                    {literal}
                    <Multiselect
                        v-model="identifyRestockSelectorsCustom"
                        id="identifyRestockSelectorsCustom"
                        :options="identifyRestockAllOptions"
                        :groups="true"
                        :group-select="false"
                        mode="tags"
                        :searchable="true"
                        :create-option="true"
                        :close-on-select="false"
                        :hide-selected="false"
                        :can-clear="false"
                        placeholder="Type a CSS selector and press Enter to add"
                    >
                        <template #tag="{ option, handleTagRemove, disabled }">
                            <div
                                class="multiselect-tag is-user"
                                :class="identifyRestockSuggestedValuesSet[option.value] ? 'tag-suggested' : 'tag-custom'"
                            >
                                {{ option.value }}
                                <span
                                    v-if="!disabled"
                                    class="multiselect-tag-remove"
                                    @click="(e) => handleTagRemove(option, e)"
                                    @mousedown.prevent
                                >
                                    <span class="multiselect-tag-remove-icon"></span>
                                </span>
                            </div>
                        </template>
                        <template #option="{ option }">
                            <span
                                v-if="option.disabled"
                                class="builtin-default-pill"
                            >{{ option.label }}</span>
                            <span
                                v-else-if="!identifyRestockSuggestedValuesSet[option.value]"
                                class="custom-selector-pill"
                            >{{ option.label }}</span>
                            <span
                                v-else
                                class="suggested-selector-pill"
                            >{{ option.label }}</span>
                        </template>
                    </Multiselect>
                    {/literal}
                    <p class="help-block">{l s='Identify fires on click of the restock button — only for guests, only when the visitor has typed a valid email. Logged-in visitors (already identified) are skipped. The watcher only attaches when ps_emailalerts is installed and enabled.' mod='mailchimppro'}</p>
                </div>
            </div>

            <div class="form-group pixel-identify-form-selectors-custom pixel-identify-contact-selectors-custom"
                 :class="{ 'is-master-disabled': identifyOnForms !== '1' }">
                <label class="control-label col-lg-2" for="identifyContactSelectorsCustom">
                    <span class="label-tooltip" data-toggle="tooltip" data-html="true" title="" data-original-title="{l s='Submit-event watcher for the contactform module. The built-in defaults catch the stock PS contact form on /contact-us across all themes (anchored on PS-specific identifiers, not URL strings). The bridge also auto-handles multi-lingual URLs at runtime via window.prestashop.urls.pages.contact — no need to paste the translated slug. Add custom selectors only if a 3rd-party contact form module isn\'t caught by the built-ins. Sanitized server-side; 500-char total cap.' mod='mailchimppro'}">{l s='Contact form — extra selectors' mod='mailchimppro'}</span>
                </label>
                <div class="col-lg-8">
                    {literal}
                    <Multiselect
                        v-model="identifyContactSelectorsCustom"
                        id="identifyContactSelectorsCustom"
                        :options="identifyContactAllOptions"
                        :groups="true"
                        :group-select="false"
                        mode="tags"
                        :searchable="true"
                        :create-option="true"
                        :close-on-select="false"
                        :hide-selected="false"
                        :can-clear="false"
                        placeholder="Type a CSS selector and press Enter to add"
                    >
                        <template #tag="{ option, handleTagRemove, disabled }">
                            <div
                                class="multiselect-tag is-user"
                                :class="identifyContactSuggestedValuesSet[option.value] ? 'tag-suggested' : 'tag-custom'"
                            >
                                {{ option.value }}
                                <span
                                    v-if="!disabled"
                                    class="multiselect-tag-remove"
                                    @click="(e) => handleTagRemove(option, e)"
                                    @mousedown.prevent
                                >
                                    <span class="multiselect-tag-remove-icon"></span>
                                </span>
                            </div>
                        </template>
                        <template #option="{ option }">
                            <span
                                v-if="option.disabled"
                                class="builtin-default-pill"
                            >{{ option.label }}</span>
                            <span
                                v-else-if="!identifyContactSuggestedValuesSet[option.value]"
                                class="custom-selector-pill"
                            >{{ option.label }}</span>
                            <span
                                v-else
                                class="suggested-selector-pill"
                            >{{ option.label }}</span>
                        </template>
                    </Multiselect>
                    {/literal}
                    <p class="help-block">{l s='Identify fires on contact form submission — only for guests, only when the visitor has typed a valid email. Logged-in visitors (already identified) are skipped. The watcher only attaches when the contactform module is installed and enabled.' mod='mailchimppro'}</p>
                </div>
            </div>

            <div class="form-group pixel-identify-form-selectors-custom pixel-identify-forgot-password-selectors-custom"
                 :class="{ 'is-master-disabled': identifyOnForms !== '1' }">
                <label class="control-label col-lg-2" for="identifyForgotPasswordSelectorsCustom">
                    <span class="label-tooltip" data-toggle="tooltip" data-html="true" title="" data-original-title="{l s='Submit-event watcher for the PrestaShop forgot-password form (core feature, AuthController). The built-in defaults catch the stock form across all themes (form.forgotten-password + button#send-reset-link). The bridge also auto-handles multi-lingual URLs at runtime via window.prestashop.urls.pages.password — no need to paste the translated slug. Custom selectors only needed for non-stock auth flows. Sanitized server-side; 500-char total cap.' mod='mailchimppro'}">{l s='Forgot-password form — extra selectors' mod='mailchimppro'}</span>
                </label>
                <div class="col-lg-8">
                    {literal}
                    <Multiselect
                        v-model="identifyForgotPasswordSelectorsCustom"
                        id="identifyForgotPasswordSelectorsCustom"
                        :options="identifyForgotPasswordAllOptions"
                        :groups="true"
                        :group-select="false"
                        mode="tags"
                        :searchable="true"
                        :create-option="true"
                        :close-on-select="false"
                        :hide-selected="false"
                        :can-clear="false"
                        placeholder="Type a CSS selector and press Enter to add"
                    >
                        <template #tag="{ option, handleTagRemove, disabled }">
                            <div
                                class="multiselect-tag is-user"
                                :class="identifyForgotPasswordSuggestedValuesSet[option.value] ? 'tag-suggested' : 'tag-custom'"
                            >
                                {{ option.value }}
                                <span
                                    v-if="!disabled"
                                    class="multiselect-tag-remove"
                                    @click="(e) => handleTagRemove(option, e)"
                                    @mousedown.prevent
                                >
                                    <span class="multiselect-tag-remove-icon"></span>
                                </span>
                            </div>
                        </template>
                        <template #option="{ option }">
                            <span
                                v-if="option.disabled"
                                class="builtin-default-pill"
                            >{{ option.label }}</span>
                            <span
                                v-else-if="!identifyForgotPasswordSuggestedValuesSet[option.value]"
                                class="custom-selector-pill"
                            >{{ option.label }}</span>
                            <span
                                v-else
                                class="suggested-selector-pill"
                            >{{ option.label }}</span>
                        </template>
                    </Multiselect>
                    {/literal}
                    <p class="help-block">{l s='Identify fires on forgot-password form submission — only for guests, only when the visitor has typed a valid email. Forgot-password is a PrestaShop core feature (not a module), so the watcher is always active when the master toggle is on.' mod='mailchimppro'}</p>
                </div>
            </div>

            <hr>
            <h4 class="text-info">{l s='3rd-party checkout modules' mod='mailchimppro'}</h4>

            <div class="form-group pixel-checkout-controllers deep-link-target" id="pixel-checkout-controllers-section">
                <label class="control-label col-lg-2" for="pixelCheckoutControllers">
                    <span class="label-tooltip" data-toggle="tooltip" data-html="true" title="" data-original-title="{l s='3rd-party checkout modules (OnePageCheckoutPS, SuperCheckout, etc.) use their own controllers that don\'t match stock PrestaShop\'s. The bundle auto-loads when: (1) it IS the stock OrderController OR (2) the page_name is \'checkout\' (theme convention preserved by most modules) — so most setups need NOTHING here. Pick a known module from the dropdown OR type your own controller php_self. Find it via View Source on the checkout page — look for the body\'s id or class.' mod='mailchimppro'}">{l s='Custom checkout controllers' mod='mailchimppro'}</span>
                </label>
                <div class="col-lg-8">
                    {literal}
                    <Multiselect
                        v-model="pixelCheckoutControllers"
                        id="pixelCheckoutControllers"
                        :options="pixelCheckoutControllerSuggestions"
                        mode="tags"
                        :searchable="true"
                        :create-option="true"
                        :hide-selected="true"
                        :close-on-select="false"
                        :placeholder="'{/literal}{l s='Pick a known module or type a controller php_self and press Enter' mod='mailchimppro'}{literal}'"
                        :no-options-text="'{/literal}{l s='Type to add a custom controller name' mod='mailchimppro'}{literal}'"
                    >
                        <template v-slot:tag="{ option, handleTagRemove }">
                            <div class="multiselect-tag is-user">
                                {{ option.value }}
                                <span class="multiselect-tag-remove" @click="handleTagRemove(option, $event)">
                                    <span class="multiselect-tag-remove-icon"></span>
                                </span>
                            </div>
                        </template>
                        <template v-slot:option="{ option }">
                            <span class="badge badge-pill badge-primary">
                                {{ option.label }}
                            </span>
                        </template>
                    </Multiselect>
                    {/literal}
                    <p class="help-block">{l s='Default "opc" (OnePageCheckoutPS) is pre-selected. Pick more from the dropdown for known modules, or type a custom controller php_self and press Enter — duplicates are auto-filtered. Changes save automatically.' mod='mailchimppro'}</p>
                </div>
            </div>

            <div class="form-group pixel-order-confirmation-controllers deep-link-target" id="pixel-order-confirmation-controllers-section">
                <label class="control-label col-lg-2" for="pixelOrderConfirmationControllers">
                    <span class="label-tooltip" data-toggle="tooltip" data-html="true" title="" data-original-title="{l s='3rd-party order-confirmation controllers for the PURCHASED event. Most modules call hookDisplayOrderConfirmation naturally so the event fires server-side — nothing needed here. Only add entries if your checkout module uses a custom confirmation controller that doesn\'t extend OrderConfirmationController AND breaks the page_name=\'order-confirmation\' theme convention. Find it via View Source on the order-confirmation page — look for the body\'s id or class.' mod='mailchimppro'}">{l s='Custom order-confirmation controllers' mod='mailchimppro'}</span>
                </label>
                <div class="col-lg-8">
                    {literal}
                    <Multiselect
                        v-model="pixelOrderConfirmationControllers"
                        id="pixelOrderConfirmationControllers"
                        :options="pixelOrderConfirmationControllerSuggestions"
                        mode="tags"
                        :searchable="true"
                        :create-option="true"
                        :hide-selected="true"
                        :close-on-select="false"
                        :placeholder="'{/literal}{l s='Pick a known module or type a controller php_self and press Enter' mod='mailchimppro'}{literal}'"
                        :no-options-text="'{/literal}{l s='Type to add a custom controller name' mod='mailchimppro'}{literal}'"
                    >
                        <template v-slot:tag="{ option, handleTagRemove }">
                            <div class="multiselect-tag is-user">
                                {{ option.value }}
                                <span class="multiselect-tag-remove" @click="handleTagRemove(option, $event)">
                                    <span class="multiselect-tag-remove-icon"></span>
                                </span>
                            </div>
                        </template>
                        <template v-slot:option="{ option }">
                            <span class="badge badge-pill badge-primary">
                                {{ option.label }}
                            </span>
                        </template>
                    </Multiselect>
                    {/literal}
                    <p class="help-block">{l s='Default empty — most 3rd-party checkout modules fire the displayOrderConfirmation hook naturally and don\'t need anything here. Only add entries if PURCHASED events stop firing for your specific module. Changes save automatically.' mod='mailchimppro'}</p>
                </div>
            </div>

            <hr>
            <h4 class="text-info deep-link-target" id="pixel-log-section">{l s='Pixel event log' mod='mailchimppro'}</h4>

            <div class="form-group pixel-log-enabled">
                <label class="control-label col-lg-2">
                    <span class="label-tooltip" data-toggle="tooltip" data-html="true" title="" data-original-title="{l s='Controls writes to the pixel log table only. Disabling stops both BO admin-action entries AND public FO telemetry rows. Pixel tracking itself keeps working — to fully disable tracking, use the master Pixel toggle at the top of this tab or the Auto-track toggle below.' mod='mailchimppro'}">{l s='Pixel event log' mod='mailchimppro'}</span>
                </label>
                <div class="col-lg-8">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" name="pixelLogEnabled" id="pixelLogEnabled_on" value="1" v-model="pixelLogEnabled">
                        <label for="pixelLogEnabled_on">{l s='Yes' mod='mailchimppro'}</label>
                        <input type="radio" name="pixelLogEnabled" id="pixelLogEnabled_off" value="0" v-model="pixelLogEnabled">
                        <label for="pixelLogEnabled_off">{l s='No' mod='mailchimppro'}</label>
                        <a class="slide-button btn"></a>
                    </span>
                    <p class="help-block">{l s='When enabled, the module records pixel-related events (admin actions + storefront telemetry) into a ring-buffered log table. Disabling stops log writes only — pixel tracking, identify, and the bridge\'s AJAX fallback for 3rd-party checkouts all keep working.' mod='mailchimppro'}</p>
                </div>
            </div>

            <transition name="pixel-section" v-bind:css="false" v-on:enter="pixelSectionEnter" v-on:leave="pixelSectionLeave">
                <div v-show="!isPixelLogEnabled()" class="alert alert-info pixel-log-disabled-notice">
                    {l s='The pixel log is currently disabled — log level, size cap, rate limit, and the log viewer below are hidden until you enable it.' mod='mailchimppro'}
                </div>
            </transition>

            <transition name="pixel-section" v-bind:css="false" v-on:enter="pixelSectionEnter" v-on:leave="pixelSectionLeave">
            <div v-show="isPixelLogEnabled()" class="form-group pixel-log-level">
                <label class="control-label col-lg-2">
                    <span class="label-tooltip" data-toggle="tooltip" data-html="true" title="" data-original-title="{l s='Choose how much detail goes into the pixel log. \"Errors only\" — set-and-forget production; logs failures and problem indicators only. \"Default\" — see the pipeline working (verdict probe + identify transitions + admin actions + errors + a compact one-liner per track event). \"Verbose\" — flip on temporarily for full payload details on every track() event, then switch back.' mod='mailchimppro'}">{l s='Pixel log level' mod='mailchimppro'}</span>
                </label>
                <div class="col-lg-5">
                    <select v-model="pixelLogLevel" class="form-control">
                        <option v-for="(meta, key) in pixelLogLevels" v-bind:key="key" v-bind:value="key" v-bind:title="meta.description">
                            {{ meta.label }}
                        </option>
                    </select>
                    <p class="help-block">
                        <strong>{l s='Errors only:' mod='mailchimppro'}</strong>
                        {l s='clean log for set-and-forget production. Logs SDK failures plus problem indicators (SDK timeouts, missing theme hooks, incomplete payloads, audience setup gaps) — merchants still see real problems even when their severity is not error-grade.' mod='mailchimppro'}
                        <br>
                        <strong>{l s='Default:' mod='mailchimppro'}</strong>
                        {l s='infrastructure events (pixel availability, identify transitions, admin actions) + all problems from the Errors-only level + a compact one-liner per track event so you can see tracking firing without payload verbosity. Recommended for production.' mod='mailchimppro'}
                        <br>
                        <strong>{l s='Verbose:' mod='mailchimppro'}</strong>
                        {l s='everything with full detail — track events include source, via, and the full payload. Turn on temporarily when troubleshooting / verifying the bridge is firing the right data, then switch back to Default.' mod='mailchimppro'}
                    </p>
                </div>
            </div>
            </transition>

            <transition name="pixel-section" v-bind:css="false" v-on:enter="pixelSectionEnter" v-on:leave="pixelSectionLeave">
            <div v-show="isPixelLogEnabled()" class="form-group pixel-log-max-rows">
                <label class="control-label col-lg-2">
                    <span class="label-tooltip" data-toggle="tooltip" data-html="true" title="" data-original-title="{l s='Maximum rows kept in the pixel log table. After every insert, the oldest rows beyond this cap are auto-deleted (ring-buffer). Higher = more history retained but more disk usage. Default 10,000 keeps months of \"Default\" log-level activity, or roughly a day of \"Verbose\" activity on a busy store. Storage cost at default is ~6-8 MB.' mod='mailchimppro'}">{l s='Pixel log size cap' mod='mailchimppro'}</span>
                </label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" name="pixelLogMaxRows" id="pixelLogMaxRows" class="" required="required" minlength="3" maxlength="6" v-model="pixelLogMaxRowsRaw" @keypress="onDigitOnlyKeypress($event, 100)" @paste="onDigitOnlyPaste" @blur="onPixelLogMaxRowsBlur">
                        <span class="input-group-addon">{l s='row(s)' mod='mailchimppro'}</span>
                    </div>
                    <p class="help-block">{l s='Default: 10,000. Range: 100 to 100,000. The ring-buffer enforces this cap on every insert so the table never exceeds it. If you turn on Verbose log level on a high-traffic store and need more capture window, bump this to 50,000 or higher.' mod='mailchimppro'}</p>
                </div>
            </div>
            </transition>

            <transition name="pixel-section" v-bind:css="false" v-on:enter="pixelSectionEnter" v-on:leave="pixelSectionLeave">
            <div v-show="isPixelLogEnabled()" class="form-group pixel-log-rate-limit">
                <label class="control-label col-lg-2">
                    <span class="label-tooltip" data-toggle="tooltip" data-html="true" title="" data-original-title="{l s='Per-IP per-minute cap on the public FO telemetry endpoint (/module/mailchimppro/pixeltelemetry). Defends against malicious traffic flooding the log table. Visitors exceeding the cap are silently rejected (the endpoint still returns 200 OK to avoid leaking the rate-limit state to attackers).' mod='mailchimppro'}">{l s='Telemetry rate limit (per IP, per minute)' mod='mailchimppro'}</span>
                </label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" name="pixelLogRateLimitPerMin" id="pixelLogRateLimitPerMin" class="" required="required" minlength="1" maxlength="5" v-model="pixelLogRateLimitPerMinRaw" @keypress="onDigitOnlyKeypress($event, 0)" @paste="onDigitOnlyPaste" @blur="onPixelLogRateLimitPerMinBlur">
                        <span class="input-group-addon">{l s='request(s)' mod='mailchimppro'}</span>
                    </div>
                    <p class="help-block">{l s='Default: 60. Set to 0 to disable rate-limiting entirely (the ring-buffer cap above is the backup defense).' mod='mailchimppro'}</p>
                </div>
            </div>
            </transition>
        </div>

        </div> {* /pixel-settings-container — close the dimmable wrapper *}

        <hr>

        {* Pixel log viewer — outside the .pixel-settings-container wrapper so it
           stays interactive even when pixel is disabled. Admins still need to read
           past events to diagnose issues. Filterable + paginated table backed by
           ps_mailchimppro_pixel_log; hidden entirely when the master kill-switch
           (Pixel event log = No) is off. *}
        <div class="pixel-logs-container">
        <transition name="pixel-section" v-bind:css="false" v-on:enter="pixelSectionEnter" v-on:leave="pixelSectionLeave">
        <div v-show="isPixelLogEnabled()" class="form-horizontal pixel-log-table-wrapper">
            <div class="panel-heading">{l s='Pixel log' mod='mailchimppro'}</div>
            <div class="form-group pixel-log-filters">
                <div class="row">
                    <div class="col-md-3">
                        <label>{l s='Severity' mod='mailchimppro'}</label>
                        <select v-model="pixelLogFilters.severity" v-on:change="loadPixelLogs" class="form-control">
                            <option value="">{l s='All' mod='mailchimppro'}</option>
                            <option value="1">{l s='Info' mod='mailchimppro'}</option>
                            <option value="2">{l s='Warning' mod='mailchimppro'}</option>
                            <option value="3">{l s='Error' mod='mailchimppro'}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>{l s='Source' mod='mailchimppro'}</label>
                        <select v-model="pixelLogFilters.source" v-on:change="loadPixelLogs" class="form-control">
                            <option value="">{l s='All' mod='mailchimppro'}</option>
                            <option value="bo">{l s='BO (admin actions)' mod='mailchimppro'}</option>
                            <option value="fo">{l s='FO (storefront telemetry)' mod='mailchimppro'}</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>{l s='Event type' mod='mailchimppro'}</label>
                        <select v-model="pixelLogFilters.event_type" v-on:change="loadPixelLogs" class="form-control">
                            <option value="">{l s='All' mod='mailchimppro'}</option>
                            <optgroup v-for="group in pixelEventGroups" v-bind:key="group.label" v-bind:label="group.label">
                                <option v-for="item in group.items" v-bind:key="item.value" v-bind:value="item.value" v-bind:title="item.value">
                                    {{ item.label }}
                                </option>
                            </optgroup>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>&nbsp;</label>
                        <button type="button" class="btn btn-default pixel-log-refresh-btn" v-on:click="loadPixelLogs" v-bind:disabled="pixelLogLoading">
                            <i class="las la-sync"></i>
                            {l s='Refresh' mod='mailchimppro'}
                        </button>
                    </div>
                </div>
                {* Row 2 — free-text + date-range filters. Apply on Enter or
                   blur to avoid an AJAX hit per keystroke. The reset button
                   blanks all four (plus the row-1 filters too) for a quick
                   "start over" UX. *}
                <div class="row" style="margin-top: 10px;">
                    <div class="col-md-3">
                        <label>{l s='Message contains' mod='mailchimppro'}</label>
                        <input type="text" class="form-control"
                               v-model="pixelLogFilters.message"
                               v-on:keyup.enter="loadPixelLogs"
                               v-on:change="loadPixelLogs"
                               placeholder="{l s='e.g. PRODUCT_VIEWED' mod='mailchimppro'}">
                    </div>
                    <div class="col-md-2">
                        <label>{l s='IP starts with' mod='mailchimppro'}</label>
                        <input type="text" class="form-control"
                               v-model="pixelLogFilters.ip"
                               v-on:keyup.enter="loadPixelLogs"
                               v-on:change="loadPixelLogs"
                               placeholder="{l s='e.g. 82.77.' mod='mailchimppro'}">
                    </div>
                    <div class="col-md-2">
                        <label>{l s='From date' mod='mailchimppro'}</label>
                        <input type="date" class="form-control"
                               v-model="pixelLogFilters.from"
                               v-on:change="loadPixelLogs">
                    </div>
                    <div class="col-md-2">
                        <label>{l s='To date' mod='mailchimppro'}</label>
                        <input type="date" class="form-control"
                               v-model="pixelLogFilters.to"
                               v-on:change="loadPixelLogs">
                    </div>
                    <div class="col-md-3">
                        <label>&nbsp;</label>
                        <div>
                            <button type="button" class="btn btn-default" v-on:click="resetPixelLogFilters" v-bind:disabled="pixelLogLoading">
                                <i class="las la-times"></i>
                                {l s='Reset filters' mod='mailchimppro'}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="pixelLogLoading" class="text-center" style="padding: 20px;">
                <i class="las la-spinner la-2x" style="animation: spin 1s linear infinite;"></i>
                {l s='Loading…' mod='mailchimppro'}
            </div>

            <div v-else-if="!pixelLogRows || pixelLogRows.length === 0" class="alert alert-info">
                {l s='No log entries match the current filters.' mod='mailchimppro'}
            </div>

            <table v-else class="table table-striped table-hover pixel-log-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th style="width: 80px;">{l s='Severity' mod='mailchimppro'}</th>
                        <th style="width: 60px;">{l s='Src' mod='mailchimppro'}</th>
                        <th>{l s='Event' mod='mailchimppro'}</th>
                        <th>{l s='Message' mod='mailchimppro'}</th>
                        <th style="width: 120px;">{l s='IP' mod='mailchimppro'}</th>
                        <th style="width: 160px;">{l s='When' mod='mailchimppro'}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in pixelLogRows" v-bind:key="row.id_log" v-bind:class="['pixel-log-row-sev-' + row.severity, 'pixel-log-row-src-' + row.source]">
                        <td>{{ row.id_log }}</td>
                        <td>
                            <span v-if="row.severity == 1" class="badge badge-info">info</span>
                            <span v-else-if="row.severity == 2" class="badge badge-warning">warn</span>
                            <span v-else class="badge badge-danger">error</span>
                        </td>
                        <td>
                            <span class="badge" v-bind:class="row.source === 'fo' ? 'badge-primary' : 'badge-secondary'">{{ row.source }}</span>
                        </td>
                        <td><span v-bind:title="row.event_type" class="pixel-event-friendly">{{ row._eventLabel }}</span></td>
                        <td>
                            <template v-if="row._parsed.from">
                                <div class="pixel-transition">
                                    <span class="pixel-state-pill" v-bind:class="row._fromPillClass">{{ row._parsed.from }}</span>
                                    <span class="pixel-transition-arrow" aria-hidden="true">→</span>
                                    <span class="pixel-state-pill" v-bind:class="row._toPillClass">{{ row.event_type }}</span>
                                </div>
                                <div class="pixel-transition-body">{{ row._parsed.body }}</div>
                            </template>
                            <template v-else-if="row._track">
                                <div v-if="row._track.title" class="track-msg-title">{{ row._track.title }}</div>
                                <div class="track-msg-prefix">{{ row._track.prefix }}</div>
                                <div v-if="row._track.tokens.length" class="track-msg-tokens">
                                    <span v-for="(t, ti) in row._track.tokens"
                                          v-bind:key="ti"
                                          class="track-msg-token"
                                          v-bind:class="{ 'track-msg-token--orphan': !t.key }"
                                          v-bind:title="t.key ? (t.key + ': ' + t.value) : t.value">
                                        <span v-if="t.key" class="track-msg-key">{{ t.key }}</span><span class="track-msg-value">{{ t.value }}</span>
                                    </span>
                                </div>
                                <details v-if="row._track.payload" class="track-msg-payload-details">
                                    <summary class="track-msg-payload-toggle">
                                        <span class="track-msg-payload-icon" aria-hidden="true">▸</span>
                                        <span class="track-msg-payload-label">{l s='Show full payload' mod='mailchimppro'}</span>
                                    </summary>
                                    <pre class="track-msg-payload">{{ prettyPrintPayload(row._track.payload) }}</pre>
                                </details>
                            </template>
                            <template v-else>
                                <span v-bind:title="row.message">{{ row.message }}</span>
                            </template>
                        </td>
                        <td>{{ row.ip || '–' }}</td>
                        <td>{{ row.date_add }}</td>
                    </tr>
                </tbody>
            </table>

            <div v-if="pixelLogTotal > pixelLogLimit" class="pixel-log-pagination">
                <button type="button" class="btn btn-default btn-sm" v-on:click="pixelLogPrevPage" v-bind:disabled="pixelLogOffset === 0 || pixelLogLoading">
                    {l s='← Previous' mod='mailchimppro'}
                </button>
                <span style="padding: 0 15px;">
                    {l s='Showing' mod='mailchimppro'} {{ pixelLogOffset + 1 }}–{{ Math.min(pixelLogOffset + pixelLogLimit, pixelLogTotal) }} {l s='of' mod='mailchimppro'} {{ pixelLogTotal }}
                </span>
                <button type="button" class="btn btn-default btn-sm" v-on:click="pixelLogNextPage" v-bind:disabled="(pixelLogOffset + pixelLogLimit) >= pixelLogTotal || pixelLogLoading">
                    {l s='Next →' mod='mailchimppro'}
                </button>
            </div>

            <div style="margin-top: 15px;">
                <button class="clear-pixel-log btn btn-default" name="clear-pixel-log" type="button" v-on:click="clearPixelLog" v-bind:disabled="pixelLogClearing">
                    <i class="icon-trash"></i>
                    <span v-if="!pixelLogClearing">{l s='Clear pixel log' mod='mailchimppro'}</span>
                    <span v-else>{l s='Clearing…' mod='mailchimppro'}</span>
                </button>
            </div>
        </div>
        </transition>
        </div> {* /pixel-logs-container *}
    </div>
</div>
