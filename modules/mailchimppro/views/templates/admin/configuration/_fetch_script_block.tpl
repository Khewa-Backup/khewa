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
{*
 * Shared partial: fetch-script form group + CSP warning + last-fetched status panel.
 * Included from _advanced.tpl and _pixel.tpl so both tabs render the same controls
 * with the same Vue-reactive data (single source of truth in main.js):
 *   - listId, storeSynced, cspStatus, cspChecking, scriptCachedDate
 *   - syncStoresScript() and checkPixelCspNow() Vue methods
 *
 * IMPORTANT: this partial must NOT use any HTML id="..." attributes — when the
 * file is {include}'d into two parents on the same rendered page, duplicated ids
 * would violate HTML uniqueness. Class selectors only.
 *}
<hr v-if="listId && storeSynced">
<div v-if="listId && storeSynced && cspStatus && cspStatus.checked === false && cspStatus.reason && cspStatus.reason !== 'no_script_cached' && cspStatus.reason !== 'curl_unavailable' && cspStatus.reason !== 'no_fo_url'"
     class="alert alert-warning script-csp-unreachable"
     style="margin-top: 15px;">
    <p><strong>{l s='We couldn\'t reach your storefront to check its Content-Security-Policy.' mod='mailchimppro'}</strong></p>
    <p>
        {l s='Reason:' mod='mailchimppro'} <code v-text="cspStatus.reason"></code><span v-if="cspStatus.curl_errno"> (cURL errno <span v-text="cspStatus.curl_errno"></span>)</span>
    </p>
    <p>{l s='This is usually a transient network or self-loopback issue. The pixel itself is unaffected — this probe only diagnoses CSP misconfiguration. Click below to retry.' mod='mailchimppro'}</p>
    <p style="margin-bottom: 0;">
        <button type="button"
                class="btn btn-default btn-sm script-csp-recheck-btn"
                v-on:click="checkPixelCspNow"
                v-bind:disabled="cspChecking">
            <span v-if="!cspChecking">{l s='Re-check now' mod='mailchimppro'}</span>
            <span v-else>{l s='Checking…' mod='mailchimppro'}</span>
        </button>
    </p>
</div>
<div v-if="listId && storeSynced && cspStatus && cspStatus.csp_present === true && cspStatus.compatible === false"
     class="alert alert-warning script-csp-warning"
     style="margin-top: 15px;">
    <p><strong>{l s='Your storefront\'s Content-Security-Policy may block the Mailchimp pixel.' mod='mailchimppro'}</strong></p>
    <p>
        {l s='The following CSP directives are missing chimpstatic.com:' mod='mailchimppro'}
        <b><span v-for="(d, i) in cspStatus.missing_directives" v-bind:key="d">{{ i ? ', ' : '' }}{{ d }}</span></b>
    </p>
    <p>{l s='Add chimpstatic.com to those directives in your web server config (Apache .htaccess, nginx, or your CSP-managing module). Without this fix the pixel script will load, but events may be silently blocked by the browser.' mod='mailchimppro'}</p>
    <p style="margin-bottom: 0;">
        <button type="button"
                class="btn btn-default btn-sm script-csp-recheck-btn"
                v-on:click="checkPixelCspNow"
                v-bind:disabled="cspChecking">
            <span v-if="!cspChecking">{l s='Re-check now' mod='mailchimppro'}</span>
            <span v-else>{l s='Checking…' mod='mailchimppro'}</span>
        </button>
    </p>
</div>
<div v-if="listId && storeSynced" class="form-group fetch-script-form-group deep-link-target" id="advanced-fetch-script-section">
    <label>{l s='Fetch the Mailchimp integration script for this store' mod='mailchimppro'}</label>
    <div class="alert alert-message">
        <p>{l s='Use this to manually re-fetch the pixel script from the Mailchimp API. The module also fetches it automatically every time the store is synced.' mod='mailchimppro'}</p>
        <p>{l s='You only need this button to force a refresh — for example, if Mailchimp ships a script update, or if the cached script ever gets out of sync with what Mailchimp serves. Pixel enable/disable is controlled by the toggle on the Pixel tab, not by re-fetching the script.' mod='mailchimppro'}</p>
        <p>{l s='Remember to clear both the PrestaShop and module caches after fetching the script for optimal performance.' mod='mailchimppro'}</p>
    </div>
    <div>
        <button v-on:click="syncStoresScript" class="btn btn-default sync-stores-script">
            {l s='Fetch script' mod='mailchimppro'}
        </button>
    </div>
    <div class="form-horizontal script-fetched-status-container" style="margin-top: 15px;">
        <div class="alert" :class="scriptStatusAlertClass()">
            <ul style="margin-bottom: 0; padding-left: 20px;">
                <li>
                    {l s='The script was last fetched:' mod='mailchimppro'}
                    <b v-text="scriptCachedDate ?? '–'"></b>
                    <span v-if="scriptCachedDate" title="{l s='Script has been fetched and is cached locally' mod='mailchimppro'}">✔️</span>
                    <span v-else title="{l s='Script has never been fetched — click Fetch script above' mod='mailchimppro'}">❌</span>
                </li>
                <li>
                    {l s='Mailchimp integration script loaded on this storefront:' mod='mailchimppro'}
                    <b v-if="listId && storeSynced && scriptCachedDate">{l s='Yes' mod='mailchimppro'}</b>
                    <b v-else style="color: #d9534f;">{l s='No' mod='mailchimppro'}</b>
                    <span v-if="listId && storeSynced && scriptCachedDate" title="{l s='MC.js is being injected in every storefront page <head>' mod='mailchimppro'}">✔️</span>
                    <span v-else title="{l s='Setup incomplete — check API key, audience, store sync, and fetch the script' mod='mailchimppro'}">❌</span>
                </li>
                <li>
                    {l s='CSP status:' mod='mailchimppro'}
                    <b v-if="!cspStatus || cspStatus.checked === false">{l s='not yet checked' mod='mailchimppro'}</b>
                    <b v-else-if="cspStatus.csp_present === false">{l s='no CSP detected (no risk)' mod='mailchimppro'}</b>
                    <b v-else-if="cspStatus.compatible === true">{l s='compatible ✓' mod='mailchimppro'}</b>
                    <b v-else style="color: #d9534f;">{l s='WARNING: missing chimpstatic.com in' mod='mailchimppro'}
                        <span v-for="(d, i) in cspStatus.missing_directives" v-bind:key="d">{{ i ? ', ' : '' }}{{ d }}</span>
                    </b>
                    <span v-if="!cspStatus || cspStatus.checked === false" title="{l s='CSP not yet probed — runs automatically on first script-cache event' mod='mailchimppro'}">❓</span>
                    <span v-else-if="cspStatus.csp_present === false || cspStatus.compatible === true" title="{l s='No CSP problem detected — chimpstatic.com can load' mod='mailchimppro'}">✔️</span>
                    <span v-else title="{l s='CSP blocks chimpstatic.com — add it to script-src and connect-src' mod='mailchimppro'}">❌</span>
                </li>
                <li>
                    {l s='Pixel enabled for your account:' mod='mailchimppro'}
                    <b v-if="!pixelAvailable">{l s='not yet determined' mod='mailchimppro'}</b>
                    <b v-else-if="pixelAvailable.enabled === true && pixelAvailable.recent_negative_count > 0" style="color: #f0ad4e;">{l s='Yes (checking)' mod='mailchimppro'}</b>
                    <b v-else-if="pixelAvailable.enabled === true">{l s='Yes' mod='mailchimppro'}</b>
                    <b v-else style="color: #d9534f;">{l s='No' mod='mailchimppro'}</b>
                    <span v-if="!pixelAvailable" title="{l s='Not yet determined — sync your store to detect' mod='mailchimppro'}">❓</span>
                    <span v-else-if="pixelAvailable.enabled === true && pixelAvailable.recent_negative_count > 0" :title="'{l s='Pixel is enabled but some visitors are reporting issues' mod='mailchimppro'}' + ' (' + pixelAvailable.recent_negative_count + '/' + pixelAvailable.negative_quorum + ')'">⚠️</span>
                    <span v-else-if="pixelAvailable.enabled === true" title="{l s='Pixel is enabled for this connected site' mod='mailchimppro'}">✔️</span>
                    <span v-else title="{l s='Pixel is not enabled for this connected site' mod='mailchimppro'}">❌</span>
                </li>
            </ul>
        </div>
    </div>
</div>
<br v-if="listId && storeSynced">
