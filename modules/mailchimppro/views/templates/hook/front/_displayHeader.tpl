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

{if isset($mc_js_payload) && $mc_js_payload}
    {* Network-prime MC.js so DNS / TLS / fetch all happen as early as
       possible — shrinks the window between `pixel.installed === true`
       and `c.initialized` flipping inside the SDK. Less window = fewer
       "Pixel not initialized" rejections caught by the console.error hook
       in mc-pixel-bridge.js. Preconnect always emits (cheap, free even
       when the URL extract failed); preload only when the chimpstatic
       URL was extractable from the cached fragment. *}
    <link rel="preconnect" href="https://chimpstatic.com" crossorigin>
    {if isset($mc_js_preload_url) && $mc_js_preload_url}
        {* NO crossorigin — the SDK's inline loader does `script.src = url`
           without a `crossorigin` attribute, so the eventual GET is a plain
           (non-CORS) request. Adding `crossorigin` to the preload would make
           the browser issue a CORS request that chimpstatic.com refuses, the
           preload would fail (net::ERR_FAILED + CORS console error), AND
           the cached entry wouldn't match the script tag's request — so the
           browser would double-fetch. Plain preload matches the loader's
           fetch and is safely reusable. *}
        <link rel="preload" as="script" href="{$mc_js_preload_url|escape:'htmlall':'UTF-8'}">
    {/if}
    {* Strict-consent mode: write mc_user_optin=false BEFORE MC.js so the SDK
       defaults to opted-out. Only sets if absent — preserves an earlier CMP
       choice. Expiration 6 months (15552000s), inside the GDPR / ePrivacy
       refresh window.

       Off-mode cleanup (else branch): a stale mc_user_optin=false from a
       previous strict-mode visit would keep the visitor opted-out even
       after the merchant disables strict mode. Purge the blocking value
       on every page load so the flip takes effect immediately. An explicit
       `true` is preserved — that's a legitimate consent record.

       Secure-flag handling: protocol-aware. `; Secure` is appended only on
       HTTPS pages. On HTTP-only dev environments the flag is skipped (a
       browser would reject the cookie set entirely if `; Secure` were
       present on an HTTP origin). SameSite=Lax is sufficient for a
       first-party consent cookie. *}
    {* Asymmetric TTL by design:
         - Pre-script writes mc_user_optin=false with a 6-month TTL
           (15552000s). This is the "no decision yet" default-deny
           record; CNIL guidance prefers a shorter re-prompt cadence
           for non-decision states.
         - Active consent records (visitor accepts via CMP, or
           $mcSite.enableOptIn() called from JS) use a 12-month TTL —
           industry CMP norm, respects explicit choice longer. See
           the consent-banner FAQ entry wiring snippets. Do NOT
           "consistency-fix" this 15552000 to 31536000. *}
    {* Inline-by-necessity — these scripts must execute SYNCHRONOUSLY in
     * <head> BEFORE the MC.js loader below runs. MC.js reads mc_user_optin
     * on init to decide whether to enable the pixel API; setting the
     * cookie via a deferred / external .js file would write it AFTER MC.js
     * had already read its absence and defaulted to opted-out. Smarty's
     * {if} also resolves the strict-vs-off branch at render time, keeping
     * the runtime JS itself minimal. *}
    {if isset($mc_pixel_require_consent) && $mc_pixel_require_consent}
        <script>{literal}(function(){if(typeof document==='undefined')return;if(/(^|;\s*)mc_user_optin=/.test(document.cookie||''))return;var s=location.protocol==='https:'?'; Secure':'';document.cookie='mc_user_optin=false; path=/; max-age=15552000; SameSite=Lax'+s;})();{/literal}</script>
    {else}
        <script>{literal}(function(){if(typeof document==='undefined')return;if(/(^|;\s*)mc_user_optin=false(;|$)/.test(document.cookie||'')){var s=location.protocol==='https:'?'; Secure':'';document.cookie='mc_user_optin=; path=/; max-age=0; SameSite=Lax'+s;}})();{/literal}</script>
    {/if}
    {* Disable autoTrack BEFORE the MC.js loader runs — per Mailchimp docs the
       window flag must be set first. Default is autotrack ON, so we only emit
       when admin has explicitly turned it off in the BO Pixel tab. *}
    {if isset($mc_pixel_autotrack_enabled) && !$mc_pixel_autotrack_enabled}
        <script>window.disable_mailchimp_pixel_autotrack = true;</script>
    {/if}
    {* TRUST BOUNDARY — `nofilter` on $mc_js_payload below.
     * The value is the literal script-tag fragment returned by Mailchimp's
     * authenticated API (GET /ecommerce/stores → connected_site.site_script.
     * fragment), cached in MAILCHIMP_SCRIPT_CACHED. It MUST reach the browser
     * un-escaped because it IS HTML — escaping breaks the loader.
     *
     * Why this is safe:
     *   1. The value is sourced exclusively from Mailchimp's authenticated
     *      API response. Visitors, shoppers, untrusted query strings, and
     *      form submissions cannot influence what gets cached here.
     *   2. The fetch happens BO-side under admin authorization — a hostile
     *      admin could already do worse things (delete data, rotate API
     *      keys) than poison this row.
     *   3. We trust Mailchimp's API + the chimpstatic.com CDN to serve a
     *      non-malicious script fragment.
     *
     * If any of these stops holding — e.g. a merchant-supplied field starts
     * flowing into the cached value — `nofilter` becomes an XSS surface.
     * Either preserve the trust properties, or replace `nofilter` with HTML
     * escaping + strict validation at the new source. *}
    <script id="mcjs">
        {$mc_js_payload nofilter}
    </script>
{/if}