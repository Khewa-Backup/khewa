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
 <div v-cloak v-if="validApiKey" class="panel panel-faq" v-show="currentPage === 'faq'">
    <h3 class="panel-heading">
        <span class="panel-heading-icon-container">
            <i class="las la-question-circle la-2x"></i>
            {l s='FAQs' mod='mailchimppro'}
        </span>
        <div class="panel-heading-action">
            <button type="button"
                    v-if="!allFaqsExpanded"
                    v-on:click="expandAllFaqs"
                    title="{l s='Open every FAQ entry' mod='mailchimppro'}"
                    class="list-toolbar-btn btn btn-default">
                <i class="las la-angle-double-down la-2x"></i>
                <span>{l s='Expand all' mod='mailchimppro'}</span>
            </button>
            <button type="button"
                    v-else
                    v-on:click="collapseAllFaqs"
                    title="{l s='Close every FAQ entry' mod='mailchimppro'}"
                    class="list-toolbar-btn btn btn-default">
                <i class="las la-angle-double-up la-2x"></i>
                <span>{l s='Collapse all' mod='mailchimppro'}</span>
            </button>
        </div>
    </h3>
    <div class="panel-body">
		<div class="accordion" id="faq-accordions-container">
            <div class="accordion-item">
                <div class="accordion-header collapsed" id="accordion-heading1" data-toggle="collapse" data-target="#accordion-body-container1" aria-expanded="false" aria-controls="accordion-body-container1">
                    <div class="accordion-title h3">
                        <span>
                            <i class="material-icons icon-arrow-forward">&#xe5c8;</i>
                            {l s='Should I Synchronize every item in my store?' mod='mailchimppro'}
                        </span>
                        <div>
							<i class="material-icons icon-down">&#xe313;</i>
						</div>
                    </div>
                </div>
                <div id="accordion-body-container1" class="accordion-collapse collapse" aria-labelledby="accordion-heading1">
                    <div class="accordion-body">
                        <p>{l s='Mailchimp uses customer data as the primary payment determinant. You will be charged per client email account. Even while it is not required, we encourage that you do so in order to maximize Mailchimp features such as Customer Journey and sales-based segmentation.'  mod='mailchimppro'}</p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="accordion-item">
                <div class="accordion-header collapsed" id="accordion-heading2" data-toggle="collapse" data-target="#accordion-body-container2" aria-expanded="false" aria-controls="accordion-body-container2">
                    <div class="accordion-title h3">
                        <span>
                            <i class="material-icons icon-arrow-forward">&#xe5c8;</i>
                            {l s='What does Hook Based mean within Advanced Settings?' mod='mailchimppro'}
                        </span>
                        <div>
							<i class="material-icons icon-down">&#xe313;</i>
						</div>
                    </div>
                </div>
                <div id="accordion-body-container2" class="accordion-collapse collapse" aria-labelledby="accordion-heading2">
                    <div class="accordion-body">
                        <p>{l s='Hook-based synchronization will utilize the Prestashop modular hook system to synchronize an instance\'s data; however, in the case of larger stores, this may result in checkout lag. This option is recommended for customers with enough servers or limited data.' mod='mailchimppro'}</p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="accordion-item">
                <div class="accordion-header collapsed" id="accordion-heading3" data-toggle="collapse" data-target="#accordion-body-container3" aria-expanded="false" aria-controls="accordion-body-container3">
                    <div class="accordion-title h3">
                        <span>
                            <i class="material-icons icon-arrow-forward">&#xe5c8;</i>
                            {l s='How can I put up the required cronjob?' mod='mailchimppro'}
                        </span>
                        <div>
                            <i class="material-icons icon-down">&#xe313;</i>
                        </div>
                    </div>
                </div>
                <div id="accordion-body-container3" class="accordion-collapse collapse" aria-labelledby="accordion-heading3">
                    <div class="accordion-body">
                        <p>{{l s='WE developed the module in the same manner as any other official Prestashop module that employs cronjobs, such as Search or Faceted Search. Follow the description under the [link]"Cron Job" Tab[/link] if you are unfamiliar with the cronjob settings and provide all information to the server maintenance or technical staff.'
                            sprintf=[
                                '[link]' => '<a href="#cronjob" class="link-mc fw-600 text-decoration-underline" @click.prevent="setCurrentPage(\'cronjob\')">',
                                '[/link]' => '</a>'
                            ]
                            mod='mailchimppro'}|unescape:'html'}</p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="accordion-item">
                <div class="accordion-header collapsed" id="accordion-heading4" data-toggle="collapse" data-target="#accordion-body-container4" aria-expanded="false" aria-controls="accordion-body-container4">
                    <div class="accordion-title h3">
                        <span>
                            <i class="material-icons icon-arrow-forward">&#xe5c8;</i>
                            {l s='What does Multi-instance mode entail?' mod='mailchimppro'}
                        </span>
                        <div>
                            <i class="material-icons icon-down">&#xe313;</i>
                        </div>
                    </div>
                </div>
                <div id="accordion-body-container4" class="accordion-collapse collapse" aria-labelledby="accordion-heading4">
                    <div class="accordion-body">
                        <p>{l s='As suggested by its name, it should not be confused with Prestashop Multistore Option. The module\'s main functionality includes both the core Prestashop Multistore as well as the Multilanguage option. Multi-instance mode refers to two distinct Prestashop instances that operate independently and are synchronized to the same Mailchimp account.' mod='mailchimppro'}</p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="accordion-item">
                <div class="accordion-header collapsed" id="accordion-heading5" data-toggle="collapse" data-target="#accordion-body-container5" aria-expanded="false" aria-controls="accordion-body-container5">
                    <div class="accordion-title h3">
                        <span>
                            <i class="material-icons icon-arrow-forward">&#xe5c8;</i>
                            {l s='Will Mailchimp\'s Unsubscribed accounts be linked with Prestashop?' mod='mailchimppro'}
                        </span>
                        <div>
                            <i class="material-icons icon-down">&#xe313;</i>
                        </div>
                    </div>
                </div>
                <div id="accordion-body-container5" class="accordion-collapse collapse" aria-labelledby="accordion-heading5">
                    <div class="accordion-body">
                        <p>{l s='Not at present. Due to the fact that Prestashop allows the opportunity to place an order using a Guest account, each order can and will have a unique subscription choice, putting the Mailchimp Unsubscribe option at risk. We suggest manually adding unsubscribed accounts, or using Mailchimp for all marketing emails.' mod='mailchimppro'}</p>
                    </div>
                </div>
            </div>
            <hr>
            {* ----- Pixel-related FAQs ----- *}
            <div class="accordion-item">
                <div class="accordion-header collapsed" id="accordion-heading6" data-toggle="collapse" data-target="#accordion-body-container6" aria-expanded="false" aria-controls="accordion-body-container6">
                    <div class="accordion-title h3">
                        <span>
                            <i class="material-icons icon-arrow-forward">&#xe5c8;</i>
                            {l s='What is the Mailchimp Pixel?' mod='mailchimppro'}
                        </span>
                        <div><i class="material-icons icon-down">&#xe313;</i></div>
                    </div>
                </div>
                <div id="accordion-body-container6" class="accordion-collapse collapse" aria-labelledby="accordion-heading6">
                    <div class="accordion-body">
                        <p>{l s='The Mailchimp Pixel is a small JavaScript SDK loaded from chimpstatic.com that captures shopper behavior on your storefront — page views, browse abandonment, cart actions, checkout, purchases — and sends those events to Mailchimp. It complements the existing server-side ecommerce sync (which handles entity sync like products, orders, customers). The pixel adds the behavioral layer the server-side sync cannot capture.' mod='mailchimppro'}</p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="accordion-item">
                <div class="accordion-header collapsed" id="accordion-heading7" data-toggle="collapse" data-target="#accordion-body-container7" aria-expanded="false" aria-controls="accordion-body-container7">
                    <div class="accordion-title h3">
                        <span>
                            <i class="material-icons icon-arrow-forward">&#xe5c8;</i>
                            {l s='How do I enable or disable pixel tracking?' mod='mailchimppro'}
                        </span>
                        <div><i class="material-icons icon-down">&#xe313;</i></div>
                    </div>
                </div>
                <div id="accordion-body-container7" class="accordion-collapse collapse" aria-labelledby="accordion-heading7">
                    <div class="accordion-body">
                        <p>{{l s='The Yes/No toggle at the top of the [link]Pixel tab[/link] controls whether your connected site sends tracking events to Mailchimp. The toggle calls Mailchimp\'s server-side enable-pixel / disable-pixel actions directly — no support contact required.'
                            sprintf=[
                                '[link]' => '<a href="#pixel" class="link-mc fw-600 text-decoration-underline" @click.prevent="setCurrentPage(\'pixel\', \'pixel-master-toggle-section\')">',
                                '[/link]' => '</a>'
                            ]
                            mod='mailchimppro'}|unescape:'html'}</p>
                        <p>{l s='When disabled, the script still loads on your storefront (so Mailchimp popups and signup forms keep working) but no tracking events are sent — no page views, product views, cart actions, checkout or purchase events reach Mailchimp until you re-enable.' mod='mailchimppro'}</p>
                        <p>{l s='The pixel is OFF by default. Flip the toggle when you want to start sending events to Mailchimp. Review the strict-consent setting below the toggle first — in prior-consent jurisdictions (EU, UK, Switzerland, Brazil, etc.), turn strict consent ON and wire a CMP before enabling the pixel in production.' mod='mailchimppro'}</p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="accordion-item">
                <div class="accordion-header collapsed" id="accordion-heading8" data-toggle="collapse" data-target="#accordion-body-container8" aria-expanded="false" aria-controls="accordion-body-container8">
                    <div class="accordion-title h3">
                        <span>
                            <i class="material-icons icon-arrow-forward">&#xe5c8;</i>
                            {l s='Why are my pixel events not appearing in Mailchimp?' mod='mailchimppro'}
                        </span>
                        <div><i class="material-icons icon-down">&#xe313;</i></div>
                    </div>
                </div>
                <div id="accordion-body-container8" class="accordion-collapse collapse" aria-labelledby="accordion-heading8">
                    <div class="accordion-body">
                        <p>{{l s='Check the [link]Pixel tab health panel[/link] for these conditions:'
                            sprintf=[
                                '[link]' => '<a href="#pixel" class="link-mc fw-600 text-decoration-underline" @click.prevent="setCurrentPage(\'pixel\', \'pixel-health-panel-section\')">',
                                '[/link]' => '</a>'
                            ]
                            mod='mailchimppro'}|unescape:'html'}</p>
                        <ul>
                            <li>{{l s='Script cached (yes/no): if no, click [link]Fetch script in Advanced Settings[/link].'
                                sprintf=[
                                    '[link]' => '<a href="#advanced-settings" class="link-mc fw-600 text-decoration-underline" @click.prevent="setCurrentPage(\'advanced-settings\', \'advanced-fetch-script-section\')">',
                                    '[/link]' => '</a>'
                                ]
                                mod='mailchimppro'}|unescape:'html'}</li>
                            <li>{l s='Currently injecting on storefront: if no, your store is not yet fully synced — check API key + audience + preset selection.' mod='mailchimppro'}</li>
                            <li>{l s='CSP status: if WARNING, your web server is sending a Content-Security-Policy header that blocks chimpstatic.com. Add chimpstatic.com to script-src and connect-src.' mod='mailchimppro'}</li>
                            <li>{l s='Pixel feature enabled: if NO, flip the Yes/No toggle at the top of the Pixel tab to Yes. The module calls Mailchimp\'s enable-pixel endpoint and refreshes the cached script automatically.' mod='mailchimppro'}</li>
                            <li>{l s='Ad blockers: ~25-40% of visitors block chimpstatic.com via uBlock Origin / EasyPrivacy / Brave shields. The pixel never breaks your storefront, but events from those visitors are silently dropped.' mod='mailchimppro'}</li>
                        </ul>
                        <p>{l s='Pixel status "Checking" (yellow): one or more recent visitors reported the pixel SDK loaded but the pixel.api accessor was missing. The status only flips to "not enabled" once 3 distinct visitors report this within 15 minutes — this prevents one visitor\'s browser extension or partial chimpstatic block from incorrectly disabling the panel. If the count keeps rising, check your CSP and confirm your Mailchimp account has pixel access enabled.' mod='mailchimppro'}</p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="accordion-item">
                <div class="accordion-header collapsed" id="accordion-heading9" data-toggle="collapse" data-target="#accordion-body-container9" aria-expanded="false" aria-controls="accordion-body-container9">
                    <div class="accordion-title h3">
                        <span>
                            <i class="material-icons icon-arrow-forward">&#xe5c8;</i>
                            {l s='Does the auto-track toggle control all pixel events?' mod='mailchimppro'}
                        </span>
                        <div><i class="material-icons icon-down">&#xe313;</i></div>
                    </div>
                </div>
                <div id="accordion-body-container9" class="accordion-collapse collapse" aria-labelledby="accordion-heading9">
                    <div class="accordion-body">
                        <p>{l s='No. The auto-track toggle only controls the automatic PAGE_VIEWED event that the Mailchimp SDK fires on every page load. The other 5 events (PRODUCT_VIEWED, PRODUCT_ADDED_TO_CART, CART_VIEWED, CHECKOUT_STARTED, PURCHASED) are emitted by this module via explicit pixel.api.track() calls regardless of the auto-track setting. The identify() call for known customers (logged-in customers, or guests after they submit the personal-info form on checkout) also runs regardless of auto-track.' mod='mailchimppro'}</p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="accordion-item">
                <div class="accordion-header collapsed" id="accordion-heading10" data-toggle="collapse" data-target="#accordion-body-container10" aria-expanded="false" aria-controls="accordion-body-container10">
                    <div class="accordion-title h3">
                        <span>
                            <i class="material-icons icon-arrow-forward">&#xe5c8;</i>
                            {l s='How do I verify the pixel is actually firing?' mod='mailchimppro'}
                        </span>
                        <div><i class="material-icons icon-down">&#xe313;</i></div>
                    </div>
                </div>
                <div id="accordion-body-container10" class="accordion-collapse collapse" aria-labelledby="accordion-heading10">
                    <div class="accordion-body">
                        <ol>
                            <li>{l s='Open your storefront in a browser and press F12 to open DevTools.' mod='mailchimppro'}</li>
                            <li>{l s='Go to the Network tab.' mod='mailchimppro'}</li>
                            <li>{l s='Filter for chimpstatic.com.' mod='mailchimppro'}</li>
                            <li>{l s='Reload the page. You should see the MC.js loader request with status 200.' mod='mailchimppro'}</li>
                            <li>{l s='Browse a product, add to cart, view the cart, etc. Each pixel event fires a POST to chimpstatic.com/pixel/v1/track. If those POSTs appear with 200 OK, the pixel is firing.' mod='mailchimppro'}</li>
                            <li>{l s='Cross-check Mailchimp\'s audience activity dashboard for the events.' mod='mailchimppro'}</li>
                        </ol>
                        <p>{l s='You can also enable the pixel log in this tab to see server-side records of admin actions and storefront telemetry POSTs.' mod='mailchimppro'}</p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="accordion-item">
                <div class="accordion-header collapsed" id="accordion-heading11" data-toggle="collapse" data-target="#accordion-body-container11" aria-expanded="false" aria-controls="accordion-body-container11">
                    <div class="accordion-title h3">
                        <span>
                            <i class="material-icons icon-arrow-forward">&#xe5c8;</i>
                            {l s='Is the pixel GDPR / privacy-friendly?' mod='mailchimppro'}
                        </span>
                        <div><i class="material-icons icon-down">&#xe313;</i></div>
                    </div>
                </div>
                <div id="accordion-body-container11" class="accordion-collapse collapse" aria-labelledby="accordion-heading11">
                    <div class="accordion-body">
                        <p>{{l s='By default the pixel uses the EMAIL_SHA256 identify format — the customer\'s email is hashed before being sent, so the plain email never appears in the rendered page source, browser Network tab, or visitor\'s localStorage. You can switch to plain EMAIL in the [link]Pixel tab[/link] if you prefer consistency with the existing server-side sync.'
                            sprintf=[
                                '[link]' => '<a href="#pixel" class="link-mc fw-600 text-decoration-underline" @click.prevent="setCurrentPage(\'pixel\', \'pixel-identify-format-section\')">',
                                '[/link]' => '</a>'
                            ]
                            mod='mailchimppro'}|unescape:'html'}</p>
                        <p>{l s='Consent: per Mailchimp\'s integration guide, consent is the integrator\'s responsibility. If you have a GDPR consent module installed (such as psgdpr), a future module update will gate the pixel injection on the consent state. For now, when the pixel is enabled, the script loads on every page where the visitor reaches your storefront — make sure your privacy policy and cookie banner reflect this.' mod='mailchimppro'}</p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="accordion-item">
                <div class="accordion-header collapsed" id="accordion-heading12" data-toggle="collapse" data-target="#accordion-body-container12" aria-expanded="false" aria-controls="accordion-body-container12">
                    <div class="accordion-title h3">
                        <span>
                            <i class="material-icons icon-arrow-forward">&#xe5c8;</i>
                            {l s='Does the pixel slow down my storefront?' mod='mailchimppro'}
                        </span>
                        <div><i class="material-icons icon-down">&#xe313;</i></div>
                    </div>
                </div>
                <div id="accordion-body-container12" class="accordion-collapse collapse" aria-labelledby="accordion-heading12">
                    <div class="accordion-body">
                        <p>{l s='Negligibly. The MC.js loader is loaded asynchronously (m.async=1) so it does not block page render. The pixel telemetry POSTs to the BO log endpoint use navigator.sendBeacon which is fire-and-forget — they never delay any page interaction. If chimpstatic.com is unreachable (network down, ad blocker, CSP blocking), the pixel fails silently without affecting the storefront in any way; the server-side ecommerce sync continues to work as normal.' mod='mailchimppro'}</p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="accordion-item">
                <div class="accordion-header collapsed" id="accordion-heading13" data-toggle="collapse" data-target="#accordion-body-container13" aria-expanded="false" aria-controls="accordion-body-container13">
                    <div class="accordion-title h3">
                        <span>
                            <i class="material-icons icon-arrow-forward">&#xe5c8;</i>
                            {l s='When should I enable "Cache-safe identify mode"?' mod='mailchimppro'}
                        </span>
                        <div><i class="material-icons icon-down">&#xe313;</i></div>
                    </div>
                </div>
                <div id="accordion-body-container13" class="accordion-collapse collapse" aria-labelledby="accordion-heading13">
                    <div class="accordion-body">
                        <p>{l s='Almost never — leave it OFF unless you know your cache layer is aggressive.' mod='mailchimppro'}</p>
                        <p><strong>{l s='What it does:' mod='mailchimppro'}</strong>
                            {l s='By default (off), the pixel bridge embeds the known customer\'s identify value (hashed email by default) inline in the page HTML. "Known" means either a logged-in customer, or a guest who has submitted the personal-info form on checkout — both states populate the PrestaShop session with the customer\'s email and produce the same identify behavior. This is fast — zero extra HTTP requests, zero added latency — and safe under PrestaShop\'s native cache, LiteSpeed Cache, and any Varnish setup that bypasses cache when a login session cookie is present (the dominant configurations).' mod='mailchimppro'}</p>
                        <p><strong>{l s='When to turn it ON:' mod='mailchimppro'}</strong>
                            {l s='Enable this only if your storefront sits behind a "cache everything" proxy that ignores Cache-Control headers and caches HTML regardless of login state. Examples:' mod='mailchimppro'}</p>
                        <ul>
                            <li>{l s='Cloudflare with a "Cache Everything" page rule applied to all storefront URLs (and no login-aware bypass)' mod='mailchimppro'}</li>
                            <li>{l s='Aggressive Varnish VCL configured for full-page caching without varying on the login session cookie' mod='mailchimppro'}</li>
                            <li>{l s='Any reverse-proxy that caches HTML for all visitors with a single shared cache key' mod='mailchimppro'}</li>
                        </ul>
                        <p>{l s='Without this option enabled in those setups, one customer\'s identify value could be served from cache to another visitor — Mailchimp would then attribute the second visitor\'s browse activity to the first customer.' mod='mailchimppro'}</p>
                        <p><strong>{l s='Cost when enabled:' mod='mailchimppro'}</strong>
                            {l s='One extra HTTP POST request per page load (to /module/mailchimppro/customerinfo). The response carries Cache-Control: no-store and the POST verb prevents intermediate caches from storing it. The actual identify() call fires on every page load while a customer is known — this matches the convention used by GA4, Klaviyo, and Segment; the SDK dedupes server-side on repeated calls with the same value, and re-asserting on every page protects against the SDK\'s local identity state being lost between pages. State-transition telemetry (login / logout / customer switch) is still logged only on actual transitions to keep the BO log readable.' mod='mailchimppro'}</p>
                        <p><strong>{l s='How to verify your cache config:' mod='mailchimppro'}</strong>
                            {l s='In your browser DevTools Network tab, log in as a test customer and load a regular storefront page. Then log out, open a fresh incognito window, and load the same URL. If the response shows "(from disk cache)" or your cache layer\'s hit indicator AND the inline MC_PIXEL JsDef in the source contains a value field — your cache is shared across login states and you should turn this option on.' mod='mailchimppro'}</p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="accordion-item">
                <div class="accordion-header collapsed" id="accordion-heading14" data-toggle="collapse" data-target="#accordion-body-container14" aria-expanded="false" aria-controls="accordion-body-container14">
                    <div class="accordion-title h3">
                        <span>
                            <i class="material-icons icon-arrow-forward">&#xe5c8;</i>
                            {l s='I use a custom theme that logs customers in via AJAX without a page reload — does identify() still work?' mod='mailchimppro'}
                        </span>
                        <div><i class="material-icons icon-down">&#xe313;</i></div>
                    </div>
                </div>
                <div id="accordion-body-container14" class="accordion-collapse collapse" aria-labelledby="accordion-heading14">
                    <div class="accordion-body">
                        <p><strong>{l s='Short answer:' mod='mailchimppro'}</strong>
                            {l s='Yes, but with one caveat for AJAX-login-only themes — see below.' mod='mailchimppro'}</p>
                        <p><strong>{l s='Stock PrestaShop themes (classic + hummingbird) — fully supported:' mod='mailchimppro'}</strong>
                            {l s='Both default themes use the standard form-submit + server-redirect login flow, which triggers a full page reload after login (and after logout). On the next page render the pixel bridge picks up the new state automatically and fires identify() once on the transition. This works correctly across all supported PrestaShop versions (1.7.0.6 through 9.1) on both classic v1/v2/v3 and hummingbird v1/v2.' mod='mailchimppro'}</p>
                        <p><strong>{l s='Custom themes that AJAX-ify the login form:' mod='mailchimppro'}</strong>
                            {l s='Some marketplace or custom themes intercept the login form and submit it via XHR/fetch, updating the page DOM in place without a navigation. PrestaShop core does NOT emit a JavaScript event when this happens (no "customerLogin" / "customerLogout" event exists in any version), so the pixel bridge has no way to detect the transition until the customer navigates to another page.' mod='mailchimppro'}</p>
                        <p>{l s='What this means in practice: on an AJAX-login custom theme, identify() will fire on the FIRST page navigation AFTER the silent AJAX login (e.g. when the customer clicks a category link or adds to cart) — not at the exact moment the login completes. For most stores this is fine: any subsequent track() event still binds to the right customer because the bridge updates identify before the event fires.' mod='mailchimppro'}</p>
                        <p>{l s='If your custom theme does AJAX login AND your customers regularly trigger pixel events on the immediate login response page (very rare), please contact us — we can add an explicit hook for your theme.' mod='mailchimppro'}</p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="accordion-item">
                <div class="accordion-header collapsed" id="accordion-heading19" data-toggle="collapse" data-target="#accordion-body-container19" aria-expanded="false" aria-controls="accordion-body-container19">
                    <div class="accordion-title h3">
                        <span>
                            <i class="material-icons icon-arrow-forward">&#xe5c8;</i>
                            {l s='Does the pixel track guest customers (no account, checkout-only)?' mod='mailchimppro'}
                        </span>
                        <div><i class="material-icons icon-down">&#xe313;</i></div>
                    </div>
                </div>
                <div id="accordion-body-container19" class="accordion-collapse collapse" aria-labelledby="accordion-heading19">
                    <div class="accordion-body">
                        <p><strong>{l s='Short answer:' mod='mailchimppro'}</strong>
                            {l s='Yes — guests are tracked anonymously while they browse, then identified (with their email, SHA-256 hashed by default) the moment they submit the personal-info form on checkout. From that point through the order-confirmation page their PRODUCT_ADDED_TO_CART, CART_VIEWED, CHECKOUT_STARTED, and PURCHASED events all carry the customer identifier, so Mailchimp\'s reporting correlates the full guest funnel.' mod='mailchimppro'}</p>
                        <p><strong>{l s='How the lifecycle works:' mod='mailchimppro'}</strong>
                            {l s='PrestaShop populates the session Customer object with the guest\'s email after the personal-info step is submitted, even though isLogged() stays false. The pixel bridge picks up that email on the next page render and fires identify() once. PrestaShop then clears that session data the moment the order-confirmation page is rendered (PS core calls Customer::mylogout() for guests on the OrderConfirmation controller), so any subsequent browsing on the same browser is automatically un-identified — the next visitor on a shared browser cannot inherit the previous guest\'s identity.' mod='mailchimppro'}</p>
                        <p><strong>{l s='3rd-party One-Page-Checkout modules:' mod='mailchimppro'}</strong>
                            {l s='Modules like OnePageCheckoutPS or SuperCheckout don\'t submit each step as a separate form, so the personal-info gate doesn\'t apply. The bridge has a separate JavaScript watcher that polls the email field on those checkouts and fires identify() once the visitor enters a valid email — no page reload required.' mod='mailchimppro'}</p>
                        <p><strong>{l s='Privacy:' mod='mailchimppro'}</strong>
                            {{l s='By default the identify value is the SHA-256 hash of the email, so the raw email never travels to Mailchimp via the pixel. You can switch to plain EMAIL in the [link]Pixel tab[/link] if you prefer consistency with the server-side sync — the same trade-off applies as for logged-in customers.'
                                sprintf=[
                                    '[link]' => '<a href="#pixel" class="link-mc fw-600 text-decoration-underline" @click.prevent="setCurrentPage(\'pixel\', \'pixel-identify-format-section\')">',
                                    '[/link]' => '</a>'
                                ]
                                mod='mailchimppro'}|unescape:'html'}</p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="accordion-item">
                <div class="accordion-header collapsed" id="accordion-heading20" data-toggle="collapse" data-target="#accordion-body-container20" aria-expanded="false" aria-controls="accordion-body-container20">
                    <div class="accordion-title h3">
                        <span>
                            <i class="material-icons icon-arrow-forward">&#xe5c8;</i>
                            {l s='Does the pixel identify visitors via form submissions?' mod='mailchimppro'}
                        </span>
                        <div><i class="material-icons icon-down">&#xe313;</i></div>
                    </div>
                </div>
                <div id="accordion-body-container20" class="accordion-collapse collapse" aria-labelledby="accordion-heading20">
                    <div class="accordion-body">
                        <p><strong>{l s='Short answer:' mod='mailchimppro'}</strong>
                            {l s='Yes. Four stock PrestaShop forms are covered: newsletter signup, restock alert, contact form, and forgot-password. When a guest submits one of these with a valid email, the pixel fires identify() (SHA-256 hashed by default) so any subsequent track events on that browser bind to the same customer in Mailchimp\'s reporting.' mod='mailchimppro'}</p>

                        <p><strong>{l s='When it fires / when it doesn\'t:' mod='mailchimppro'}</strong>
                            {l s='Only for guests with no prior identify. Two skip gates run before each fire: an explicit window.prestashop.customer.is_logged check (logged-in visitors are already identified server-side) AND a mc_pixel_last_identify localStorage check (covers guest mid-checkout where PrestaShop has already populated the session email). Stronger identities — login, checkout — always win.' mod='mailchimppro'}</p>

                        <p><strong>{l s='How to disable:' mod='mailchimppro'}</strong>
                            {{l s='Use the master "Fire identify on form submissions" toggle in the [link]Pixel tab[/link]. When OFF, the form-watcher JavaScript isn\'t loaded on the storefront at all — zero overhead. Default: ON.'
                                sprintf=[
                                    '[link]' => '<a href="#pixel" class="link-mc fw-600 text-decoration-underline" @click.prevent="setCurrentPage(\'pixel\', \'pixel-form-identify-section\')">',
                                    '[/link]' => '</a>'
                                ]
                                mod='mailchimppro'}|unescape:'html'}</p>

                        <p><strong>{l s='Per-form customization (custom themes / 3rd-party modules):' mod='mailchimppro'}</strong>
                            {l s='Each of the four forms has an "extra selectors" Multiselect in the Pixel tab. The built-in defaults are always active when the master toggle is on — they catch stock PrestaShop on both classic and Hummingbird themes. The extra-selectors field is purely additive: paste theme-specific or 3rd-party-module selectors to catch forms the built-ins miss. Sanitized server-side (alphanumerics + standard CSS selector punctuation only, 500-char cap).' mod='mailchimppro'}</p>

                        <p><strong>{l s='Per-form disable workaround:' mod='mailchimppro'}</strong>
                            {l s='If you want to disable just one watcher (e.g., newsletter) without affecting the other three, uninstall or disable the underlying module: ps_emailsubscription (newsletter), ps_emailalerts (restock), or contactform (contact). The bridge auto-detects module-enabled state and skips listener registration when the module is disabled. Forgot-password is a PrestaShop core feature (not a module) — only the master toggle disables it.' mod='mailchimppro'}</p>

                        <p><strong>{l s='Privacy:' mod='mailchimppro'}</strong>
                            {l s='Same as for logged-in identify — SHA-256 hashing is the default ("Identify format" setting in the Pixel tab). The raw email never travels to Mailchimp\'s pixel infrastructure in EMAIL_SHA256 mode. The pre-hash email value briefly enters the browser\'s in-memory crypto.subtle API but is not stored anywhere on disk.' mod='mailchimppro'}</p>

                        <p><strong>{l s='⚠️ Shared-browser caveat (kiosks, family computers, multi-user terminals):' mod='mailchimppro'}</strong>
                            {l s='On a shared browser, a guest who identifies via a form binds the pixel\'s anonymous visitor ID to their email address until that browser\'s data is cleared (cookies + localStorage). Subsequent guests on the same browser will get attributed to the first identifier in Mailchimp\'s reporting until they either clear browser data OR log in (login rebinds the visitor ID). If you run a multi-user terminal where this matters for your compliance posture, disable form-identify via the master toggle.' mod='mailchimppro'}</p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="accordion-item">
                <div class="accordion-header collapsed" id="accordion-heading21" data-toggle="collapse" data-target="#accordion-body-container21" aria-expanded="false" aria-controls="accordion-body-container21">
                    <div class="accordion-title h3">
                        <span>
                            <i class="material-icons icon-arrow-forward">&#xe5c8;</i>
                            {l s='I disabled the pixel from BO then re-enabled it — why aren\'t track events firing on the storefront yet?' mod='mailchimppro'}
                        </span>
                        <div><i class="material-icons icon-down">&#xe313;</i></div>
                    </div>
                </div>
                <div id="accordion-body-container21" class="accordion-collapse collapse" aria-labelledby="accordion-heading21">
                    <div class="accordion-body">
                        <p><strong>{l s='Short answer:' mod='mailchimppro'}</strong>
                            {l s='Mailchimp\'s CDN can serve a stale "pixel-not-loaded" bundle for up to 5 minutes after you toggle the pixel state. Wait a few minutes, then hard-reload your storefront (Ctrl+F5) to bypass browser cache.' mod='mailchimppro'}</p>

                        <p><strong>{l s='Why it happens:' mod='mailchimppro'}</strong>
                            {l s='The Mailchimp pixel script lives on chimpstatic.com and is served with Cache-Control: max-age=300 (5 minutes). When you toggle the master Pixel tracking switch off then back on (or when the module re-caches the script after an audience swap / fresh install), the bundle the CDN serves during the propagation window may not reflect the new state yet. The pixel script loads in the storefront\'s page source, but window.$mcSite.pixel.api ends up missing or unwired — so no events fire.' mod='mailchimppro'}</p>

                        <p><strong>{l s='How to confirm it\'s the CDN gap (not a code issue):' mod='mailchimppro'}</strong>
                            {l s='Open the storefront in an incognito window after the 5-minute wait. If events fire there, browser cache was the issue. If still nothing fires, check the Pixel-availability and Last-fetched indicators in the Pixel tab — both should be green. Otherwise, this is not the propagation gap and something else needs investigation.' mod='mailchimppro'}</p>

                        <p><strong>{l s='Visible notice in the BO:' mod='mailchimppro'}</strong>
                            {l s='The module shows a "Pixel script was just (re-)fetched" notice directly under the master Pixel tracking toggle for 5 minutes after any script-cache event — toggling the master switch, clicking "Fetch script", an audience swap, or a fresh install\'s first store-sync. The notice self-hides once the propagation window has elapsed. It\'s informational, not a warning: if you only re-fetched the same script without changing pixel state on Mailchimp\'s side, the storefront keeps working unchanged. The reminder matters only when an actual state change happened.' mod='mailchimppro'}</p>

                        <p><strong>{l s='Not a bug in this module:' mod='mailchimppro'}</strong>
                            {l s='This is how Mailchimp\'s CDN propagation works for the per-account pixel bundle. The module has no control over the CDN cache. The 5-minute window is bounded — it always resolves on its own.' mod='mailchimppro'}</p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="accordion-item">
                <div class="accordion-header collapsed" id="accordion-heading22" data-toggle="collapse" data-target="#accordion-body-container22" aria-expanded="false" aria-controls="accordion-body-container22">
                    <div class="accordion-title h3">
                        <span>
                            <i class="material-icons icon-arrow-forward">&#xe5c8;</i>
                            {l s='Does the pixel send the customer\'s phone number to Mailchimp?' mod='mailchimppro'}
                        </span>
                        <div><i class="material-icons icon-down">&#xe313;</i></div>
                    </div>
                </div>
                <div id="accordion-body-container22" class="accordion-collapse collapse" aria-labelledby="accordion-heading22">
                    <div class="accordion-body">
                        <p><strong>{l s='Short answer:' mod='mailchimppro'}</strong>
                            {l s='Yes — alongside the email identify, the pixel fires a second identify() call carrying the customer\'s phone number (hashed by default) when one is available on their most-recently-updated address. Mailchimp uses both identifiers to match incoming pixel events back to the right subscriber on their side.' mod='mailchimppro'}</p>

                        <p><strong>{l s='When the phone is sent / when it isn\'t:' mod='mailchimppro'}</strong>
                            {l s='Sent when the customer\'s most-recently-updated address has a non-empty phone — covers logged-in customers and guests mid-checkout (once they\'ve submitted the address-step form, PrestaShop creates an address record carrying the phone they just typed). Skipped silently when that address has no phone, or when there\'s no address at all yet (e.g., a guest who hasn\'t reached the address step).' mod='mailchimppro'}</p>

                        <p><strong>{l s='Which address is used (single-address customer):' mod='mailchimppro'}</strong>
                            {l s='Their one and only address. Module simply uses what\'s stored.' mod='mailchimppro'}</p>

                        <p><strong>{l s='Which address is used (multi-address customer):' mod='mailchimppro'}</strong>
                            {l s='The most-recently-updated active address (ORDER BY date_upd DESC). PrestaShop has no explicit "default address" or "primary address" field on the Customer entity, so this is the closest heuristic to "the address they currently care about" — a customer who recently edited their phone on an address is signalling current contact info. For business customers with separate invoice + delivery addresses, whichever was last touched wins. This same address is used by audience sync (ListMemberFormatter → merge_fields.PHONE) so the pixel hash and the subscriber phone always match.' mod='mailchimppro'}</p>

                        <p><strong>{l s='What if the most-recently-updated address has NO phone, but an older address does?' mod='mailchimppro'}</strong>
                            {l s='No phone identify fires — we deliberately do NOT fall back to an older address. Reasoning: if the customer left the phone field empty on a freshly edited or freshly created address, that may be an intentional choice (e.g., a privacy-conscious customer removing their phone, or a business address that doesn\'t need one). Resurrecting an older phone would override that intent and potentially conflict with GDPR. The customer can re-add a phone on their next address edit; until then, the audience subscriber\'s PHONE merge field also gets cleared on the next audience sync, propagating the customer\'s choice consistently.' mod='mailchimppro'}</p>

                        <p><strong>{l s='Mobile vs landline:' mod='mailchimppro'}</strong>
                            {l s='If the customer has both phone_mobile and phone (landline) populated, mobile wins. Matches the existing behavior of the audience PHONE merge field — both pipelines now use the same priority via a shared helper, so the value Mailchimp receives via the pixel\'s PHONE_SHA256 matches the PHONE merge field on the subscriber record.' mod='mailchimppro'}</p>

                        <p><strong>{l s='Normalization (current):' mod='mailchimppro'}</strong>
                            {l s='Just trim() — whitespace stripping only, applied identically on the pixel side and on the audience-sync / ecommerce-sync sides. Mailchimp hashes the stored PHONE merge field value when matching the incoming PHONE_SHA256 from pixel; since both sides start from the same trimmed string, the hashes match.' mod='mailchimppro'}</p>

                        <p><strong>{l s='Normalization (NOT done yet — E.164):' mod='mailchimppro'}</strong>
                            {l s='Industry-standard E.164 format (e.g., +40723456789 with no spaces / dashes / parentheses) is NOT applied to the phone value before hashing. The single PhoneNormalizer helper (used by all three outbound surfaces) is the choke point if/when Mailchimp confirms they expect E.164 — at that point a single method-body change extends normalization across pixel, audience sync, and ecommerce sync without separate call-site edits. Until then, the trim()-only approach keeps the three pipelines string-identical.' mod='mailchimppro'}</p>

                        <p><strong>{l s='Privacy + format toggle:' mod='mailchimppro'}</strong>
                            {l s='Same "Identify format" setting in the Pixel tab controls both email and phone. EMAIL_SHA256 (default) → both email and phone are SHA-256 hashed before they leave the server. EMAIL (plain) → both are sent unhashed, consistent with the existing server-side ecommerce sync. The pre-hash value briefly enters PHP\'s hash() function but is not stored anywhere on disk.' mod='mailchimppro'}</p>

                        <p><strong>{l s='Strict-consent mode:' mod='mailchimppro'}</strong>
                            {l s='When the "Require explicit consent before tracking" toggle is ON, the entire SDK is initialized opted-out via the mc_user_optin cookie — including the phone identify. The phone is never sent to Mailchimp until the visitor explicitly grants consent through your CMP / cookie banner. Strict-consent is the recommended mode for merchants under a prior-consent regime — phone numbers are typically treated as more sensitive PII than emails, and the active-consent gate aligns with that.' mod='mailchimppro'}</p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="accordion-item">
                <div class="accordion-header collapsed" id="accordion-heading23" data-toggle="collapse" data-target="#accordion-body-container23" aria-expanded="false" aria-controls="accordion-body-container23">
                    <div class="accordion-title h3">
                        <span>
                            <i class="material-icons icon-arrow-forward">&#xe5c8;</i>
                            {l s='Why does the phone on my customer\'s subscriber profile in Mailchimp differ from the phone on their orders?' mod='mailchimppro'}
                        </span>
                        <div><i class="material-icons icon-down">&#xe313;</i></div>
                    </div>
                </div>
                <div id="accordion-body-container23" class="accordion-collapse collapse" aria-labelledby="accordion-heading23">
                    <div class="accordion-body">
                        <p><strong>{l s='Short answer:' mod='mailchimppro'}</strong>
                            {l s='By design. PrestaShop stores phone numbers on address records, not on the customer record itself — so an order\'s shipping/billing phone reflects the specific address used for THAT order, while the subscriber\'s PHONE merge field reflects the customer\'s most-recently-updated address (their current contact info). These can diverge when a customer maintains multiple addresses with different phones.' mod='mailchimppro'}</p>

                        <p><strong>{l s='Three Mailchimp fields, three different sources:' mod='mailchimppro'}</strong></p>
                        <ul>
                            <li>
                                <b>{l s='Subscriber merge_fields.PHONE' mod='mailchimppro'}</b> —
                                {l s='the customer\'s primary contact phone (most-recently-updated address). Updated by every audience sync. One phone per subscriber.' mod='mailchimppro'}
                            </li>
                            <li>
                                <b>{l s='Pixel PHONE_SHA256 identifier' mod='mailchimppro'}</b> —
                                {l s='same source as the merge field. Used by Mailchimp to match incoming pixel events back to the subscriber record. Pixel hash always matches the subscriber\'s stored PHONE.' mod='mailchimppro'}
                            </li>
                            <li>
                                <b>{l s='Order shipping_address.phone / billing_address.phone' mod='mailchimppro'}</b> —
                                {l s='the phone on the SPECIFIC address used for delivery / invoice of that order. Per-order context. Carrier and accountant care about these — they\'re not customer-level identifiers.' mod='mailchimppro'}
                            </li>
                        </ul>

                        <p><strong>{l s='Real-world example:' mod='mailchimppro'}</strong>
                            {l s='A customer\'s primary mobile is 0700-111-111 (used for marketing) but they shipped one order to their vacation home where the local landline is 0700-222-222. Mailchimp records both — same customer, different phones for different purposes. The subscriber profile shows 0700-111-111 (mobile, marketing). The vacation-home order shows 0700-222-222 (vacation home, delivery confirmation).' mod='mailchimppro'}</p>

                        <p><strong>{l s='Does this break Mailchimp\'s pixel matching?' mod='mailchimppro'}</strong>
                            {l s='No. Mailchimp uses PHONE_SHA256 from the pixel to match against the SUBSCRIBER\'s merge_fields.PHONE — not against per-order phones. Both pixel and subscriber phone come from the same source (most-recently-updated address) so they always agree. The per-order phones are stored as order metadata only, never used for subscriber identification.' mod='mailchimppro'}</p>

                        <p><strong>{l s='Can I force all three to match?' mod='mailchimppro'}</strong>
                            {l s='Not without architectural trade-offs that create new problems. Forcing the subscriber phone to track the cart\'s current delivery address would mean re-syncing the audience every time the customer changes carts — expensive and noisy. Forcing the order phone to use the customer\'s "primary" phone (instead of the order address phone) would tell the carrier the wrong number for THIS delivery. The current architecture respects each field\'s purpose.' mod='mailchimppro'}</p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="accordion-item">
                <div class="accordion-header collapsed" id="accordion-heading24" data-toggle="collapse" data-target="#accordion-body-container24" aria-expanded="false" aria-controls="accordion-body-container24">
                    <div class="accordion-title h3">
                        <span>
                            <i class="material-icons icon-arrow-forward">&#xe5c8;</i>
                            {l s='What customer identifiers does the pixel send to Mailchimp?' mod='mailchimppro'}
                        </span>
                        <div><i class="material-icons icon-down">&#xe313;</i></div>
                    </div>
                </div>
                <div id="accordion-body-container24" class="accordion-collapse collapse" aria-labelledby="accordion-heading24">
                    <div class="accordion-body">
                        <p>{l s='Each tracking event the pixel emits carries up to three identifier signals that let Mailchimp tie anonymous browsing to a known subscriber on their side:' mod='mailchimppro'}</p>

                        <ul>
                            <li>
                                <strong>{l s='Pixel visitor ID' mod='mailchimppro'}</strong> —
                                {l s='a random anonymous ID generated by the Mailchimp SDK on first page load and stored in the visitor\'s browser. Always sent on every event. Persists across navigations within the same browser; cleared when the visitor clears their cookies/localStorage.' mod='mailchimppro'}
                            </li>
                            <li>
                                <strong>{l s='Email (hashed by default)' mod='mailchimppro'}</strong> —
                                {l s='SHA-256 of the visitor\'s email when EMAIL_SHA256 mode is on (the default in MAILCHIMP_PIXEL_IDENTIFY_FORMAT). Plain email never leaves the browser in that mode. Sent whenever a customer identify event fires — see "When does identify fire?" below. Switch to EMAIL mode in the Pixel tab if you prefer consistency with the server-side ecommerce sync; the plain email then appears in inline page source and the BO pixel log.' mod='mailchimppro'}
                            </li>
                            <li>
                                <strong>{l s='Phone (hashed by default)' mod='mailchimppro'}</strong> —
                                {{l s='SHA-256 of the customer\'s phone, sent when the customer has a non-empty phone on their most-recently-updated address. Follows the SAME format toggle as email (one switch controls both). Currently uses trim-only normalization, NOT E.164 — see the [link]phone-identify FAQ entry[/link] for the full design rationale and the future-extension path if Mailchimp later confirms E.164 is required.'
                                    sprintf=[
                                        '[link]' => '<a href="#faq" class="link-mc fw-600 text-decoration-underline" @click.prevent="setCurrentPage(\'faq\', \'accordion-heading22\')">',
                                        '[/link]' => '</a>'
                                    ]
                                    mod='mailchimppro'}|unescape:'html'}
                            </li>
                        </ul>

                        <p>{l s='Email is Mailchimp\'s primary subscriber key (unique per email + per shop). Phone is a secondary enrichment signal — not a unique key in either PrestaShop or Mailchimp. The pixel visitor ID alone never identifies a customer; the email and/or phone are what tie the anonymous visitor to a known subscriber record.' mod='mailchimppro'}</p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="accordion-item">
                <div class="accordion-header collapsed" id="accordion-heading25" data-toggle="collapse" data-target="#accordion-body-container25" aria-expanded="false" aria-controls="accordion-body-container25">
                    <div class="accordion-title h3">
                        <span>
                            <i class="material-icons icon-arrow-forward">&#xe5c8;</i>
                            {l s='When does the pixel identify the customer?' mod='mailchimppro'}
                        </span>
                        <div><i class="material-icons icon-down">&#xe313;</i></div>
                    </div>
                </div>
                <div id="accordion-body-container25" class="accordion-collapse collapse" aria-labelledby="accordion-heading25">
                    <div class="accordion-body">
                        <p>{l s='The pixel ties anonymous browsing to a known subscriber every time it sees the customer\'s identity. That happens at these trigger points:' mod='mailchimppro'}</p>

                        <ul>
                            <li>
                                <strong>{l s='Logged-in customer page-load' mod='mailchimppro'}</strong> —
                                {l s='re-asserted on every navigation. The SDK dedupes repeat identifies server-side, so re-firing is cheap; gain is robustness against lost SDK identity state (cleared cookies, third-party cookie blocking).' mod='mailchimppro'}
                            </li>
                            <li>
                                <strong>{l s='New account creation' mod='mailchimppro'}</strong> —
                                {l s='via PrestaShop\'s actionCustomerAccountAdd / actionCustomerAccountUpdate hooks. Fires the moment a new customer record is created — including during checkout, before the order is placed.' mod='mailchimppro'}
                            </li>
                            <li>
                                <strong>{l s='Guest mid-checkout' mod='mailchimppro'}</strong> —
                                {{l s='email at the personal-info step (PrestaShop populates the session Customer object with the guest\'s email when they submit step 1), phone at the address-step (PS creates an Address record with the typed phone when they submit step 2). Two separate BO log entries — one for email transition, one for phone transition. See the [guest]guest-customer pixel FAQ entry[/guest] + the [phone]phone-identify FAQ entry[/phone].'
                                    sprintf=[
                                        '[guest]' => '<a href="#faq" class="link-mc fw-600 text-decoration-underline" @click.prevent="setCurrentPage(\'faq\', \'accordion-heading19\')">',
                                        '[/guest]' => '</a>',
                                        '[phone]' => '<a href="#faq" class="link-mc fw-600 text-decoration-underline" @click.prevent="setCurrentPage(\'faq\', \'accordion-heading22\')">',
                                        '[/phone]' => '</a>'
                                    ]
                                    mod='mailchimppro'}|unescape:'html'}
                            </li>
                            <li>
                                <strong>{l s='Stock PrestaShop form submissions' mod='mailchimppro'}</strong> —
                                {{l s='newsletter signup (ps_emailsubscription), restock alert (ps_emailalerts), contact form (contactform), forgot-password (PS core). Only for guests who haven\'t already been identified; logged-in visitors skip these watchers since their identity is already known. See the [link]form-submission identify FAQ entry[/link] for the full list, configurable selectors, and per-form disable workaround.'
                                    sprintf=[
                                        '[link]' => '<a href="#faq" class="link-mc fw-600 text-decoration-underline" @click.prevent="setCurrentPage(\'faq\', \'accordion-heading20\')">',
                                        '[/link]' => '</a>'
                                    ]
                                    mod='mailchimppro'}|unescape:'html'}
                            </li>
                            <li>
                                <strong>{l s='3rd-party One-Page-Checkout modules' mod='mailchimppro'}</strong> —
                                {{l s='OnePageCheckoutPS, SuperCheckout, etc. — the bridge polls the cart\'s server-side customer email via /pixelcartinfo AJAX and fires identify (email + phone if available) once the visitor reaches the customer-association step inside their OPC flow. No page reload required. See the [link]guest-customer pixel FAQ entry[/link].'
                                    sprintf=[
                                        '[link]' => '<a href="#faq" class="link-mc fw-600 text-decoration-underline" @click.prevent="setCurrentPage(\'faq\', \'accordion-heading19\')">',
                                        '[/link]' => '</a>'
                                    ]
                                    mod='mailchimppro'}|unescape:'html'}
                            </li>
                        </ul>

                        <p><strong>{l s='Strict-consent mode:' mod='mailchimppro'}</strong>
                            {l s='When MAILCHIMP_PIXEL_REQUIRE_CONSENT is ON, the Mailchimp SDK is initialized opted-out by default — no identify or track call fires until the visitor\'s consent banner explicitly opts them in (via the mc_user_optin cookie or window.$mcSite.enableOptIn()). All five trigger points above are gated by this consent state on the SDK side; nothing reaches Mailchimp before consent is granted.' mod='mailchimppro'}</p>

                        <p><strong>{l s='Already-identified gate:' mod='mailchimppro'}</strong>
                            {l s='Form-identify and OPC-checkout watchers run their own skip-if-already-identified check (window.prestashop.customer.is_logged + mc_pixel_last_identify localStorage flag) to avoid re-firing identify when a stronger identity (login or checkout) is already active. This keeps the BO log focused on actual transitions instead of every pageload.' mod='mailchimppro'}</p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="accordion-item">
                <div class="accordion-header collapsed" id="accordion-heading15" data-toggle="collapse" data-target="#accordion-body-container15" aria-expanded="false" aria-controls="accordion-body-container15">
                    <div class="accordion-title h3">
                        <span>
                            <i class="material-icons icon-arrow-forward">&#xe5c8;</i>
                            {l s='What does the "Pixel log level" setting do, and which one should I use?' mod='mailchimppro'}
                        </span>
                        <div><i class="material-icons icon-down">&#xe313;</i></div>
                    </div>
                </div>
                <div id="accordion-body-container15" class="accordion-collapse collapse" aria-labelledby="accordion-heading15">
                    <div class="accordion-body">
                        <p>{l s='The Pixel log level controls how much detail the module records into the in-database log table. Three options, picked for distinct use cases:' mod='mailchimppro'}</p>

                        <h4 class="text-info">{l s='1. Errors only' mod='mailchimppro'}</h4>
                        <p><strong>{l s='When to use:' mod='mailchimppro'}</strong>
                            {l s='You don\'t actively monitor the log and just want a record when something actually breaks. Set-and-forget production.' mod='mailchimppro'}</p>
                        <p><strong>{l s='What gets logged:' mod='mailchimppro'}</strong>
                            {l s='Severity-3 errors (SDK exceptions, load failures) PLUS problem indicators that are sev=1 or sev=2 but still mean something is wrong: SDK_TIMEOUT, PIXEL_HOOK_MISSING, PIXEL_DATA_INCOMPLETE, PIXEL_NOT_AVAILABLE, QUICKVIEW_DATA_INCOMPLETE. Merchants still see real problems even when their categorisation isn\'t error-grade.' mod='mailchimppro'}</p>

                        <h4 class="text-info">{l s='2. Default (recommended)' mod='mailchimppro'}</h4>
                        <p><strong>{l s='When to use:' mod='mailchimppro'}</strong>
                            {l s='Standard production. You want to confirm the pixel pipeline is working — with a one-liner-per-track-event confirmation but no payload firehose.' mod='mailchimppro'}</p>
                        <p><strong>{l s='What gets logged:' mod='mailchimppro'}</strong>
                            {l s='Infrastructure events (pixel availability, identify transitions, BO admin actions), all problems from the Errors-only level, AND a compact one-liner per track event ("Track fired") for PAGE_VIEWED / PRODUCT_VIEWED / PRODUCT_ADDED_TO_CART / CART_VIEWED / CHECKOUT_STARTED / PURCHASED. Enough to confirm tracking is firing without the full payload.' mod='mailchimppro'}</p>

                        <h4 class="text-info">{l s='3. Verbose' mod='mailchimppro'}</h4>
                        <p><strong>{l s='When to use:' mod='mailchimppro'}</strong>
                            {l s='Troubleshooting. A merchant or support engineer needs to inspect the actual payload (source, via, props) of each track() event. Switch on temporarily, observe the log fill with full PRODUCT_VIEWED / CART_VIEWED / PURCHASED entries with their payload context, switch back to Default once verified.' mod='mailchimppro'}</p>
                        <p><strong>{l s='What gets logged:' mod='mailchimppro'}</strong>
                            {l s='Everything in Default — but track-event rows carry the full payload (source + via + props) instead of just the compact "Track fired" one-liner. Also logs the PIXEL_HOOK_WIRED success-diagnostic which is suppressed at default/errors_only.' mod='mailchimppro'}</p>
                        <p><strong>{l s='Why temporarily:' mod='mailchimppro'}</strong>
                            {l s='On a busy storefront the log can fill quickly in Verbose mode. The ring-buffer cap (default 5,000 rows) is enforced, so older entries are auto-deleted — but errors get pushed out faster too. Switch back to Default once you\'ve confirmed what you need.' mod='mailchimppro'}</p>

                        <h4 class="text-info">{l s='Performance impact' mod='mailchimppro'}</h4>
                        <p>{l s='None on page render. Telemetry POSTs use navigator.sendBeacon (fire-and-forget, browser-queued, runs during idle time, survives navigation). Even on Verbose mode, page load times are unaffected.' mod='mailchimppro'}</p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="accordion-item">
                <div class="accordion-header collapsed" id="accordion-heading24" data-toggle="collapse" data-target="#accordion-body-container24" aria-expanded="false" aria-controls="accordion-body-container24">
                    <div class="accordion-title h3">
                        <span>
                            <i class="material-icons icon-arrow-forward">&#xe5c8;</i>
                            {l s='When should I change the "SDK settle delay" setting?' mod='mailchimppro'}
                        </span>
                        <div><i class="material-icons icon-down">&#xe313;</i></div>
                    </div>
                </div>
                <div id="accordion-body-container24" class="accordion-collapse collapse" aria-labelledby="accordion-heading24">
                    <div class="accordion-body">
                        <p>{l s='The "SDK settle delay" controls how many milliseconds the bridge waits after the Mailchimp Pixel SDK reports it has loaded, before firing the first track or identify call. The default 500 ms covers most environments — leave it alone unless you see a specific symptom.' mod='mailchimppro'}</p>

                        <p><strong>{l s='Why does this exist?' mod='mailchimppro'}</strong>
                            {l s='Mailchimp\'s SDK has two readiness signals: a public flag ($mcSite.pixel.installed===true) which flips when the script-level API is mounted, and an internal flag (c.initialized) which flips when init() has finished its async services. In some builds the public flag flips ONE MICROTASK before the internal flag — calling api.track() in that window throws "Pixel not initialized" inside the SDK, which prints a console error BEFORE rejecting the returned promise. The bridge retries on rejection so events still land, but the visible console error remains. The settle delay closes that microsecond window by waiting before the first event of the page.' mod='mailchimppro'}</p>

                        <h4 class="text-info">{l s='Increase to 750 or 1000 ms if:' mod='mailchimppro'}</h4>
                        <ul>
                            <li>{l s='You see "Pixel SDK ERROR Pixel not initialized" messages in the browser console on the storefront.' mod='mailchimppro'}</li>
                            <li>{l s='Your storefront runs on slow / shared hosting and the SDK\'s init takes longer than usual.' mod='mailchimppro'}</li>
                            <li>{l s='Heavy pages (order confirmation, product pages with many combinations) reliably trigger the console error.' mod='mailchimppro'}</li>
                            <li>{l s='Your visitor base skews toward older mobile devices with slower CPUs.' mod='mailchimppro'}</li>
                        </ul>

                        <h4 class="text-info">{l s='Lower to 200-300 ms if:' mod='mailchimppro'}</h4>
                        <ul>
                            <li>{l s='You\'re running a heavily-cached / fast setup and want first events to fire sooner.' mod='mailchimppro'}</li>
                            <li>{l s='You\'ve verified zero console errors over a representative test window.' mod='mailchimppro'}</li>
                        </ul>

                        <h4 class="text-info">{l s='What if I set it too low or too high?' mod='mailchimppro'}</h4>
                        <p>{l s='Out-of-range values are silently clamped to the bounds on save. Below the minimum, you risk reintroducing the "Pixel not initialized" console error. Above the maximum, the first event of every storefront pageview would be unnecessarily delayed, hurting analytics responsiveness without any benefit.' mod='mailchimppro'}</p>

                        <p><strong>{l s='Where it does NOT help:' mod='mailchimppro'}</strong>
                            {l s='If events fail to land entirely (not just slow / console-error noise), the issue is elsewhere — CSP blocking, ad-blocker interference, audience not enabled in Mailchimp, or strict-consent gate keeping the SDK fully opted-out. The retry mechanism inside the bridge will keep trying for ~4 seconds regardless of this setting; if events still don\'t land, the settle delay isn\'t the cause.' mod='mailchimppro'}</p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="accordion-item">
                <div class="accordion-header collapsed" id="accordion-heading16" data-toggle="collapse" data-target="#accordion-body-container16" aria-expanded="false" aria-controls="accordion-body-container16">
                    <div class="accordion-title h3">
                        <span>
                            <i class="material-icons icon-arrow-forward">&#xe5c8;</i>
                            {l s='I use a 3rd-party checkout module (OnePageCheckoutPS, SuperCheckout, etc.) — will CHECKOUT_STARTED still fire?' mod='mailchimppro'}
                        </span>
                        <div><i class="material-icons icon-down">&#xe313;</i></div>
                    </div>
                </div>
                <div id="accordion-body-container16" class="accordion-collapse collapse" aria-labelledby="accordion-heading16">
                    <div class="accordion-body">
                        <p>{{l s='Short answer: yes, in most cases automatically — and for the rest there is a one-click setting on the [link]Pixel tab[/link].'
                            sprintf=[
                                '[link]' => '<a href="#pixel" class="link-mc fw-600 text-decoration-underline" @click.prevent="setCurrentPage(\'pixel\', \'pixel-checkout-controllers-section\')">',
                                '[/link]' => '</a>'
                            ]
                            mod='mailchimppro'}|unescape:'html'}</p>
                        <p>{l s='The CHECKOUT_STARTED event fires whenever this module decides the visitor is on a checkout page. That decision uses three "any-of" prongs:' mod='mailchimppro'}</p>
                        <ol>
                            <li>{l s='Stock PrestaShop OrderController (php_self = "order") — always covered.' mod='mailchimppro'}</li>
                            <li>{l s='Any controller whose page_name is "checkout" — most 3rd-party checkout modules preserve this theme convention even when their php_self differs, so they get covered automatically.' mod='mailchimppro'}</li>
                            <li>{l s='Any controller whose php_self appears in the "Custom checkout controllers" list on the Pixel tab — the escape-hatch for modules that break the page_name convention.' mod='mailchimppro'}</li>
                        </ol>
                        <p>{l s='OnePageCheckoutPS\'s default controller php_self is "opc", which is pre-seeded into the Custom checkout controllers list out of the box — so OPC works with zero configuration on a fresh install.' mod='mailchimppro'}</p>
                        <h4 class="text-info">{l s='How to add a custom checkout module' mod='mailchimppro'}</h4>
                        <ol>
                            <li>{l s='Open your storefront\'s checkout page in a browser.' mod='mailchimppro'}</li>
                            <li>{l s='Right-click → View Page Source.' mod='mailchimppro'}</li>
                            <li>{{l s='Look at the opening body tag. PrestaShop themes typically render the controller name in one of three places on the body element: the [code]id[/code] attribute, the [code]class[/code] attribute (often as [code]page-CONTROLLERNAME[/code]), or a [code]data-controller[/code] attribute. For example, a body tag with [code]id="supercheckout" class="page-supercheckout"[/code] means the value to add is [code]supercheckout[/code].'
                                sprintf=[
                                    '[code]' => '<code>',
                                    '[/code]' => '</code>'
                                ]
                                mod='mailchimppro'
                            }|unescape:'html'}</li>
                            <li>{l s='Add that value to the "Custom checkout controllers" field on the Pixel tab and click outside the field — the setting saves automatically.' mod='mailchimppro'}</li>
                            <li>{l s='Reload the checkout page. CHECKOUT_STARTED should now fire (verify via DevTools Network tab → chimpstatic.com requests, or via the pixel log on the Pixel tab with log level = Verbose).' mod='mailchimppro'}</li>
                        </ol>
                        <p>{l s='If you\'re not sure of the php_self value, set the Pixel tab\'s log level to Verbose, browse to your checkout page once, then check the pixel log — entries with source=BO and event_type=CHECKOUT_STARTED tell you the bridge loaded; their absence tells you which prong is missing.' mod='mailchimppro'}</p>
                        <h4 class="text-info">{l s='Common 3rd-party modules pre-listed in the dropdown' mod='mailchimppro'}</h4>
                        <ul>
                            <li><code>opc</code> — {l s='OnePageCheckoutPS (default php_self), pre-selected on install.' mod='mailchimppro'}</li>
                            <li><code>module-onepagecheckoutps-opc</code> — {l s='OnePageCheckoutPS module front controller variant.' mod='mailchimppro'}</li>
                            <li><code>module-supercheckout-supercheckout</code> — {l s='SuperCheckout.' mod='mailchimppro'}</li>
                            <li><code>module-quickcheckout-quickcheckout</code> — {l s='QuickCheckout.' mod='mailchimppro'}</li>
                            <li><code>module-cartfusion-cartfusion</code> — {l s='CartFusion.' mod='mailchimppro'}</li>
                        </ul>
                        <p>{l s='Other modules: type the controller php_self in the field and press Enter — duplicates are auto-filtered. You can list as many as you need.' mod='mailchimppro'}</p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="accordion-item">
                <div class="accordion-header collapsed" id="accordion-heading17" data-toggle="collapse" data-target="#accordion-body-container17" aria-expanded="false" aria-controls="accordion-body-container17">
                    <div class="accordion-title h3">
                        <span>
                            <i class="material-icons icon-arrow-forward">&#xe5c8;</i>
                            {l s='My PURCHASED events are not firing on a custom checkout module' mod='mailchimppro'}
                        </span>
                        <div><i class="material-icons icon-down">&#xe313;</i></div>
                    </div>
                </div>
                <div id="accordion-body-container17" class="accordion-collapse collapse" aria-labelledby="accordion-heading17">
                    <div class="accordion-body">
                        <p>{{l s='Short answer: PURCHASED uses a 2-layer cascade — the displayOrderConfirmation PHP hook (Layer 1) and an AJAX fallback to /pixelorderinfo (Layer 2). If your checkout module fires the hook (most do, via parent::initContent() in their custom controller), Layer 1 wins automatically. If it doesn\'t, Layer 2 needs the bridge JS loaded — which is what the "Custom order-confirmation controllers" setting on the [link]Pixel tab[/link] does.'
                            sprintf=[
                                '[link]' => '<a href="#pixel" class="link-mc fw-600 text-decoration-underline" @click.prevent="setCurrentPage(\'pixel\', \'pixel-order-confirmation-controllers-section\')">',
                                '[/link]' => '</a>'
                            ]
                            mod='mailchimppro'}|unescape:'html'}</p>
                        <h4 class="text-info">{l s='Diagnostic flow' mod='mailchimppro'}</h4>
                        <ol>
                            <li>{l s='Place a test order through your checkout module.' mod='mailchimppro'}</li>
                            <li>{{l s='Open the [link]Pixel tab → Pixel event log table[/link]. Check for a recent "api.track() fired" entry with event_type=PURCHASED. If present → events are firing, look at Mailchimp\'s side.'
                                sprintf=[
                                    '[link]' => '<a href="#pixel" class="link-mc fw-600 text-decoration-underline" @click.prevent="setCurrentPage(\'pixel\', \'pixel-log-section\')">',
                                    '[/link]' => '</a>'
                                ]
                                mod='mailchimppro'}|unescape:'html'}</li>
                            <li>{{l s='If no PURCHASED entry exists, open the order-confirmation page in a browser and check View Source for a span element with class [code]mc-pixel-order-confirmation-marker[/code]. If absent → the hook didn\'t fire; the custom controller doesn\'t dispatch displayOrderConfirmation.'
                                sprintf=[
                                    '[code]' => '<code>',
                                    '[/code]' => '</code>'
                                ]
                                mod='mailchimppro'
                            }|unescape:'html'}</li>
                            <li>{{l s='Note the opening body tag\'s [code]id[/code] or [code]class[/code] attribute (e.g., [code]id="module-checkoutpro-orderconfirmation"[/code]). That\'s the php_self of the custom controller.'
                                sprintf=[
                                    '[code]' => '<code>',
                                    '[/code]' => '</code>'
                                ]
                                mod='mailchimppro'
                            }|unescape:'html'}</li>
                            <li>{l s='Add that value to the "Custom order-confirmation controllers" Multiselect on the Pixel tab. The bridge will then load on that page and use its AJAX fallback to fetch the order payload server-side.' mod='mailchimppro'}</li>
                            <li>{l s='Place another test order to verify a PURCHASED entry appears in the log.' mod='mailchimppro'}</li>
                        </ol>
                        <h4 class="text-info">{l s='Why default empty' mod='mailchimppro'}</h4>
                        <p>{l s='Unlike CHECKOUT_STARTED (where 3rd-party modules often break the page_name=\'checkout\' convention), nearly all order-confirmation flows preserve page_name=\'order-confirmation\' AND fire the displayOrderConfirmation hook from their custom controller. The escape-hatch list is only needed for the minority of modules that break BOTH conventions simultaneously.' mod='mailchimppro'}</p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="accordion-item">
                <div class="accordion-header collapsed" id="accordion-heading18" data-toggle="collapse" data-target="#accordion-body-container18" aria-expanded="false" aria-controls="accordion-body-container18">
                    <div class="accordion-title h3">
                        <span>
                            <i class="material-icons icon-arrow-forward">&#xe5c8;</i>
                            {l s='How do I make the Pixel respect my cookie consent banner (GDPR / ePrivacy)?' mod='mailchimppro'}
                        </span>
                        <div><i class="material-icons icon-down">&#xe313;</i></div>
                    </div>
                </div>
                <div id="accordion-body-container18" class="accordion-collapse collapse" aria-labelledby="accordion-heading18">
                    <div class="accordion-body">
                        <h4 class="text-info">{l s='How consent works' mod='mailchimppro'}</h4>
                        <p>{l s='The Mailchimp SDK reads a cookie named ' mod='mailchimppro'}<code>mc_user_optin</code>{l s=' to decide whether to transmit any tracking data. The cookie value is the STRING ' mod='mailchimppro'}<code>"true"</code>{l s=' (opted in) or ' mod='mailchimppro'}<code>"false"</code>{l s=' (opted out) — NOT a number. Anything that is not exactly the string ' mod='mailchimppro'}<code>"true"</code>{l s=' is treated as opted out.' mod='mailchimppro'}</p>
                        <p>{l s='MC.js itself always loads regardless of consent — it powers Mailchimp popups, signup forms and other non-tracking features unrelated to the Pixel. Tracking transmission is the consent-gated layer.' mod='mailchimppro'}</p>

                        <h4 class="text-info">{l s='Two modes — pick the one that fits your jurisdiction' mod='mailchimppro'}</h4>
                        <p>{l s='The "Require explicit consent" toggle on the Pixel tab is OFF by default — admin enables based on the jurisdiction the store operates in. The two modes:' mod='mailchimppro'}</p>
                        <p><b>{l s='Strict mode ("Require explicit consent" = ON on the Pixel tab)' mod='mailchimppro'}</b></p>
                        <ul>
                            <li>{l s='Required where your local law operates a prior-consent regime — explicit visitor consent must be obtained BEFORE any tracking fires (commonly called "opt-in" or "GDPR-style" rules). See the regime-aware guidance under "Require explicit consent before tracking" on the Pixel tab + the legal-counsel note at the bottom of this entry.' mod='mailchimppro'}</li>
                            <li>{l s='The module emits an inline ' mod='mailchimppro'}<code>&lt;script&gt;</code>{l s=' BEFORE MC.js that writes ' mod='mailchimppro'}<code>mc_user_optin=false</code>{l s=' (only if the cookie is not already present — preserves a visitor\'s earlier consent choice). The module-written cookie uses a 6-month TTL, following common GDPR / ePrivacy guidance that consent records should refresh within ~6–13 months.' mod='mailchimppro'}</li>
                            <li>{l s='Events do NOT fire until your CMP explicitly flips the cookie to ' mod='mailchimppro'}<code>"true"</code>{l s=' on accept.' mod='mailchimppro'}</li>
                            <li>{l s='When you LATER switch this setting OFF, the module automatically clears any stale ' mod='mailchimppro'}<code>mc_user_optin=false</code>{l s=' cookie on the next page load — so visitors who were opted-out under strict mode start tracking immediately, without having to wait for the cookie\'s TTL to expire. ' mod='mailchimppro'}<code>mc_user_optin=true</code>{l s=' is never touched (legitimate consent record).' mod='mailchimppro'}</li>
                        </ul>

                        <div class="alert alert-warning" style="margin-top: 0.75rem">
                            <strong>{l s='Heads-up if strict mode is ON and no tracking is firing:' mod='mailchimppro'}</strong> {l s='Without a CMP (Consent Management Platform — your cookie banner / GDPR module) wired up to call ' mod='mailchimppro'}<code>window.$mcSite.enableOptIn()</code>{l s=' (or set ' mod='mailchimppro'}<code>mc_user_optin=true</code>{l s=' cookie) on accept, no events will fire — visitors stay opted-out. The right fix depends on your jurisdiction:' mod='mailchimppro'}
                            <ul style="margin-top: 0.5rem; margin-bottom: 0">
                                <li>{{l s='[b]Prior-consent regime[/b] (your local law requires explicit visitor consent BEFORE any tracking; commonly called "opt-in" or "GDPR-style", e.g. EU / UK / Switzerland / Brazil) — install a consent banner module and wire it per the snippets below. [b]Do NOT switch strict-consent OFF as a workaround[/b] — pre-consent tracking is not permitted under these rules.'
                                    sprintf=['[b]' => '<strong>', '[/b]' => '</strong>']
                                    mod='mailchimppro'}|unescape:'html'}</li>
                                <li>{{l s='[b]Opt-out regime[/b] (your local law permits tracking by default unless the visitor declines, e.g. most U.S. states) — switch "Require explicit consent" to OFF on the [link]Pixel tab[/link] if you don\'t want to deploy a CMP. Pixel will track by default; your CMP (if you have one) only needs to set [code]mc_user_optin=false[/code] on decline.'
                                    sprintf=[
                                        '[b]' => '<strong>', '[/b]' => '</strong>',
                                        '[code]' => '<code>', '[/code]' => '</code>',
                                        '[link]' => '<a href="#pixel" class="link-mc fw-600 text-decoration-underline" @click.prevent="setCurrentPage(\'pixel\', \'pixel-require-consent-section\')">',
                                        '[/link]' => '</a>'
                                    ]
                                    mod='mailchimppro'}|unescape:'html'}</li>
                                <li>{{l s='[b]If unsure[/b] which regime applies to your store — switch strict mode ON and consult legal counsel. Privacy regulations vary by country and U.S. state and evolve over time; this module does not track regulatory changes for you.'
                                    sprintf=['[b]' => '<strong>', '[/b]' => '</strong>']
                                    mod='mailchimppro'}|unescape:'html'}</li>
                            </ul>
                        </div>

                        <p><b>{l s='Opt-out mode (default — "Require explicit consent" = OFF on the Pixel tab)' mod='mailchimppro'}</b></p>
                        <ul>
                            <li>{l s='When ' mod='mailchimppro'}<code>mc_user_optin</code> {l s='is NOT set: events transmit. The SDK\'s ' mod='mailchimppro'}<code>hasOptedIn()</code>{l s=' returns true by default — same opt-out model as Meta Pixel, GA4, TikTok Pixel.' mod='mailchimppro'}</li>
                            <li>{l s='When ' mod='mailchimppro'}<code>mc_user_optin="false"</code>: {l s='events suppressed.' mod='mailchimppro'}</li>
                            <li>{l s='When ' mod='mailchimppro'}<code>mc_user_optin="true"</code>: {l s='events transmit.' mod='mailchimppro'}</li>
                            <li>{l s='Your CMP only needs to set the cookie to ' mod='mailchimppro'}<code>"false"</code>{l s=' on decline (or write nothing at all if visitor doesn\'t interact).' mod='mailchimppro'}</li>
                        </ul>

                        <h4 class="text-info">{l s='Setting consent — two APIs (pick one)' mod='mailchimppro'}</h4>
                        <p>{l s='You have two ways to flip the consent state in your CMP handlers. They produce the same cookie name and value, but with a difference in flags worth knowing — see the note below.' mod='mailchimppro'}</p>
                        <p><b>{l s='Option 1: SDK-native API (works after MC.js loads)' mod='mailchimppro'}</b></p>
                        <pre><code>// On accept:
window.$mcSite.enableOptIn();
// On decline:
window.$mcSite.createCookie('mc_user_optin', 'false', 365);</code></pre>
                        <p><b>{l s='Option 2: vanilla document.cookie (works ANY time, even before MC.js loads) — recommended' mod='mailchimppro'}</b></p>
                        <pre><code>// On accept:
document.cookie = 'mc_user_optin=true; path=/; max-age=31536000; SameSite=Lax' + (location.protocol === 'https:' ? '; Secure' : '');
// On decline:
document.cookie = 'mc_user_optin=false; path=/; max-age=31536000; SameSite=Lax' + (location.protocol === 'https:' ? '; Secure' : '');</code></pre>

                        <p>{{l s='[b]Why Option 2 is recommended over Option 1 on HTTPS sites:[/b] the vanilla snippet sets the [code]Secure[/code] cookie flag (only sent over HTTPS, OWASP best practice) via the protocol-aware suffix. The SDK-native [code]$mcSite.enableOptIn()[/code] in Option 1 does not currently set [code]Secure[/code]. Functionally both produce a working opt-in state, but a cookie carrying consent / privacy preferences on an HTTPS store should always carry the [code]Secure[/code] flag. The [code]location.protocol === \'https:\' ? \'; Secure\' : \'\'[/code] suffix gracefully skips the flag on HTTP-only dev / staging environments (browsers reject [code]Secure[/code] cookies set from HTTP origins).'
                            sprintf=[
                                '[b]' => '<strong>', '[/b]' => '</strong>',
                                '[code]' => '<code>', '[/code]' => '</code>'
                            ]
                            mod='mailchimppro'}|unescape:'html'}</p>

                        <h4 class="text-info">{l s='Cookie lifetime — what the 365 / 31536000 numbers mean' mod='mailchimppro'}</h4>
                        <p>{{l s='The snippets above persist the consent cookie for [b]1 year[/b]: [code]365[/code] is the days argument to Mailchimp\'s SDK helper, and [code]31536000[/code] seconds is the same year expressed for [code]document.cookie[/code] ([code]60 * 60 * 24 * 365[/code]). If you omit the lifetime entirely, browsers fall back to a [b]session cookie[/b] — wiped when the visitor closes the browser, so visitors would see your consent banner on every new session. The 365-day value matches [code]$mcSite.enableOptIn()[/code]\'s own internal TTL (hardcoded in the Mailchimp SDK bundle) — so whichever path a CMP uses, the visitor ends up with the same consent record lifetime.'
                            sprintf=[
                                '[code]' => '<code>',
                                '[/code]' => '</code>',
                                '[b]' => '<strong>',
                                '[/b]' => '</strong>'
                            ]
                            mod='mailchimppro'
                        }|unescape:'html'}</p>
                        <p>{{l s='[b]Note: the module\'s strict-mode pre-script uses a shorter TTL by design.[/b] The [code]mc_user_optin=false[/code] cookie the module writes BEFORE MC.js loads (when "Require explicit consent" is ON) uses a [b]6-month[/b] TTL — that\'s the "no decision yet" default-deny record, where regulator guidance generally prefers a shorter re-prompt cadence for non-decision states. Once the visitor takes an active decision via your CMP (accept OR decline), the snippets above write a 12-month record — respecting the explicit choice longer and matching what [code]$mcSite.enableOptIn()[/code] does internally. The asymmetric pair (6mo default-deny / 12mo explicit-decision) is intentional.'
                            sprintf=[
                                '[code]' => '<code>',
                                '[/code]' => '</code>',
                                '[b]' => '<strong>',
                                '[/b]' => '</strong>'
                            ]
                            mod='mailchimppro'
                        }|unescape:'html'}</p>
                        <p>{{l s='[b]Adjusting to match your CMP:[/b] keep the [code]mc_user_optin[/code] active-consent TTL aligned with the consent-record TTL configured in your cookie banner / CMP. Common CMP defaults sit at 6–12 months; if your CMP re-asks every 6 months but [code]mc_user_optin[/code] persists for 12, the visitor could see the banner reappear while Mailchimp still considers them opted-in (or vice versa). Pick one number across both surfaces — typically whatever your CMP uses — and apply it to the snippets above by changing [code]365[/code] (Option 1) and [code]31536000[/code] (Option 2, value in seconds: [code]60 * 60 * 24 * days[/code]).'
                            sprintf=[
                                '[code]' => '<code>',
                                '[/code]' => '</code>',
                                '[b]' => '<strong>',
                                '[/b]' => '</strong>'
                            ]
                            mod='mailchimppro'
                        }|unescape:'html'}</p>

                        <h4 class="text-info">{l s='Wiring it up — common cookie banners' mod='mailchimppro'}</h4>
                        <p>{l s='All examples below use the vanilla cookie set (works regardless of MC.js load timing). Replace with ' mod='mailchimppro'}<code>$mcSite.enableOptIn()</code>{l s=' if your banner fires after MC.js is loaded.' mod='mailchimppro'}</p>

                        <div class="alert alert-success" style="margin-top: 0.5rem">
                            <p style="margin-bottom: 0.5rem"><strong>{l s='GDPR Compliance Pro (gdprpro) — auto-wired starting from gdprpro 2.5.0; no manual setup required.' mod='mailchimppro'}</strong></p>
                            <p style="margin-bottom: 0.5rem">{l s='If you have gdprpro 2.5.0 or later installed alongside this module, the integration is automatic:' mod='mailchimppro'}</p>
                            <ul style="margin-bottom: 0.5rem">
                                <li>{l s='gdprpro auto-detects this module and seeds a "Mailchimp Tracking Pixel" entry into its services list under the Marketing category.' mod='mailchimppro'}</li>
                                <li>{{l s='On visitor consent (Accept / Reject) and on every returning-visitor page load, gdprpro writes [code]mc_user_optin[/code] to match the visitor\'s marketing-category decision — with the same [code]path=/[/code], [code]SameSite=Lax[/code], conditional [code]Secure[/code], and 12-month TTL the snippets below use.'
                                    sprintf=['[code]' => '<code>', '[/code]' => '</code>']
                                    mod='mailchimppro'}|unescape:'html'}</li>
                                <li>{l s='You do not need to copy any of the snippets below when using gdprpro 2.5.0+.' mod='mailchimppro'}</li>
                            </ul>
                            <p style="margin-bottom: 0">{{l s='[b]One thing to confirm:[/b] keep "Require explicit consent before tracking" ON (the default). gdprpro only auto-integrates when strict mode is enabled. If you switch strict mode OFF while gdprpro is installed, gdprpro shows a BO warning explaining that pre-consent tracking can fire and recommends turning strict mode back on.'
                                sprintf=['[b]' => '<strong>', '[/b]' => '</strong>']
                                mod='mailchimppro'}|unescape:'html'}</p>
                        </div>

                        <p><b>Cookiebot</b> — {l s='listen for the accept event:' mod='mailchimppro'}</p>
                        <pre><code>window.addEventListener('CookiebotOnAccept', function () {
    if (window.Cookiebot &amp;&amp; Cookiebot.consent &amp;&amp; Cookiebot.consent.marketing) {
        document.cookie = 'mc_user_optin=true; path=/; max-age=31536000; SameSite=Lax' + (location.protocol === 'https:' ? '; Secure' : '');
    }
});
window.addEventListener('CookiebotOnDecline', function () {
    document.cookie = 'mc_user_optin=false; path=/; max-age=31536000; SameSite=Lax' + (location.protocol === 'https:' ? '; Secure' : '');
});</code></pre>

                        <p><b>OneTrust</b> — {l s='hook into the wrapper callback (replace C0004 with your Targeting/Marketing group ID):' mod='mailchimppro'}</p>
                        <pre><code>function OptanonWrapper() {
    var groups = window.OnetrustActiveGroups || '';
    if (groups.indexOf('C0004') !== -1) {
        document.cookie = 'mc_user_optin=true; path=/; max-age=31536000; SameSite=Lax' + (location.protocol === 'https:' ? '; Secure' : '');
    } else {
        document.cookie = 'mc_user_optin=false; path=/; max-age=31536000; SameSite=Lax' + (location.protocol === 'https:' ? '; Secure' : '');
    }
}</code></pre>

                        <p><b>Iubenda</b> — {l s='use the onConsentGiven callback in your csConfiguration:' mod='mailchimppro'}</p>
                        <pre><code>window._iub = window._iub || {};
window._iub.csConfiguration = window._iub.csConfiguration || {};
window._iub.csConfiguration.callback = window._iub.csConfiguration.callback || {};
window._iub.csConfiguration.callback.onConsentGiven = function () {
    document.cookie = 'mc_user_optin=true; path=/; max-age=31536000; SameSite=Lax' + (location.protocol === 'https:' ? '; Secure' : '');
};
window._iub.csConfiguration.callback.onConsentRejected = function () {
    document.cookie = 'mc_user_optin=false; path=/; max-age=31536000; SameSite=Lax' + (location.protocol === 'https:' ? '; Secure' : '');
};</code></pre>

                        <p><b>CookieYes / Klaro / other event-based banners</b> — {l s='listen for the banner\'s accept event and set the cookie. Most CMPs fire a custom event on accept; check your CMP\'s documentation for the exact event name (often ' mod='mailchimppro'}<code>cookieyes_consent_update</code>{l s=' or ' mod='mailchimppro'}<code>klaro:consent-given</code>{l s='). Same one-line cookie set in the handler.' mod='mailchimppro'}</p>

                        <p><b>{l s='Custom / DIY cookie banner' mod='mailchimppro'}</b> — {l s='call the cookie set directly in your accept handler:' mod='mailchimppro'}</p>
                        <pre><code>document.getElementById('my-accept-button').addEventListener('click', function () {
    document.cookie = 'mc_user_optin=true; path=/; max-age=31536000; SameSite=Lax' + (location.protocol === 'https:' ? '; Secure' : '');
});
document.getElementById('my-decline-button').addEventListener('click', function () {
    document.cookie = 'mc_user_optin=false; path=/; max-age=31536000; SameSite=Lax' + (location.protocol === 'https:' ? '; Secure' : '');
});</code></pre>

                        <h4 class="text-info">{l s='Verifying it works (Strict mode)' mod='mailchimppro'}</h4>
                        <ol>
                            <li>{{l s='Turn ON "Require explicit consent" on the [link]Pixel tab[/link] and save.'
                                sprintf=[
                                    '[link]' => '<a href="#pixel" class="link-mc fw-600 text-decoration-underline" @click.prevent="setCurrentPage(\'pixel\', \'pixel-require-consent-section\')">',
                                    '[/link]' => '</a>'
                                ]
                                mod='mailchimppro'}|unescape:'html'}</li>
                            <li>{l s='Open your storefront in an incognito window. Open DevTools → Application → Cookies.' mod='mailchimppro'}</li>
                            <li>{l s='You should immediately see ' mod='mailchimppro'}<code>mc_user_optin=false</code>{l s=' (set by the module BEFORE MC.js loads).' mod='mailchimppro'}</li>
                            <li>{l s='Browse to a product page — confirm NO PRODUCT_VIEWED entry appears in the BO Pixel log.' mod='mailchimppro'}</li>
                            <li>{l s='Accept cookies on your banner. The ' mod='mailchimppro'}<code>mc_user_optin</code>{l s=' cookie should flip to ' mod='mailchimppro'}<code>"true"</code>{l s='.' mod='mailchimppro'}</li>
                            <li>{l s='Browse to another product — a PRODUCT_VIEWED entry should now appear in the BO log within a few seconds.' mod='mailchimppro'}</li>
                        </ol>

                        <h4 class="text-info">{l s='Implementation note (for developers)' mod='mailchimppro'}</h4>
                        <p>{{l s='When [code]mc_user_optin="false"[/code], the Mailchimp SDK does NOT mount the [code]window.$mcSite.pixel.api[/code] namespace at all — [code]window.$mcSite.pixel[/code] exists but only carries [code]settings[/code], not [code]api[/code]. Once [code]mc_user_optin[/code] flips to [code]"true"[/code] and the page reloads, the SDK mounts [code]pixel.api[/code] with [code]track()[/code], [code]identify()[/code], and [code]init()[/code].'
                            sprintf=['[code]' => '<code>', '[/code]' => '</code>']
                            mod='mailchimppro'}|unescape:'html'}</p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="accordion-item">
                <div class="accordion-header collapsed" id="accordion-heading26" data-toggle="collapse" data-target="#accordion-body-container26" aria-expanded="false" aria-controls="accordion-body-container26">
                    <div class="accordion-title h3">
                        <span>
                            <i class="material-icons icon-arrow-forward">&#xe5c8;</i>
                            {l s='A banner says my Mailchimp site, store or audience is missing — what should I do?' mod='mailchimppro'}
                        </span>
                        <div>
                            <i class="material-icons icon-down">&#xe313;</i>
                        </div>
                    </div>
                </div>
                <div id="accordion-body-container26" class="accordion-collapse collapse" aria-labelledby="accordion-heading26">
                    <div class="accordion-body">
                        <p>{l s='Something the module depends on was deleted on the Mailchimp side, or the API key was revoked or regenerated. Nothing in your store is broken and nothing queued is lost — sync is paused on purpose so it does not keep firing calls that cannot succeed, and it resumes by itself once the connection works again.' mod='mailchimppro'}</p>
                        <p>{l s='The banner names what is missing and its button takes you to the fix. For a missing connected site or e-commerce store, that is Delete e-commerce data followed by Initialize connection on the Store sync tab. For an invalid API key, log out and reconnect with a current key.' mod='mailchimppro'}</p>
                        <p>{l s='If the banner reports only the connected site as missing, back-office sync keeps running normally — products, customers, orders and carts are unaffected. Only the storefront pixel, popups and signup forms stop working until the site is restored.' mod='mailchimppro'}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>