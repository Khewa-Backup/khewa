/**
 * @author    Mailchimp
 * @copyright Mailchimp
 * @license   Proprietary
 */

/**
 * Mailchimp Pixel bridge — PRODUCT_VIEWED wiring (product-page-only).
 *
 * This file is registered by mailchimppro.php ONLY when the active FO
 * controller is the product page (php_self === 'product') and the
 * request is NOT a quickview AJAX call. It runs AFTER the core bridge
 * (mc-pixel-bridge.js) — both are `defer`, and the core file is
 * registered with a lower priority number → executes first.
 *
 * Dependencies (exposed by core bridge via window.MC_PIXEL_BRIDGE):
 *   - bridge.fireOnce(eventType, payload, fingerprint)
 *   - bridge.sendTelemetry(eventType, severity, message)
 *   - bridge.cfg (= window.MC_PIXEL — same reference)
 *
 * Responsibilities:
 *   1. Initial-page fire from cfg.initialProduct (server-emitted JsDef).
 *      Uses fireOnce so when the displayProductAdditionalInfo hook also
 *      pushes to MC_PIXEL_EVENTS_QUEUE with the same product id, only
 *      one mcTrack reaches the SDK.
 *   2. Marker presence detection — logs PIXEL_HOOK_WIRED at verbose,
 *      PIXEL_HOOK_MISSING at warn so support can diagnose custom themes
 *      that strip the displayProductAdditionalInfo template hook.
 *   3. JS fallback for combination change — subscribes to
 *      prestashop.on('updatedProduct'). Universal across PS 1.7.0.6 →
 *      9.1 × classic + hummingbird (the event exists everywhere; we
 *      verified via the JS-compat skill). This is the ONLY path that
 *      covers PS 1.7.0.6 combination change (its displayAjaxRefresh
 *      response doesn't include the product_additional_info fragment,
 *      so the hook script can't re-execute on that version).
 *
 * Quickview is NOT handled here — it fires from category/listing pages
 * too, so its listener lives in the core bridge file alongside the
 * shared queue + fireOnce + sendTelemetry helpers.
 */
(function () {
    'use strict';

    var bridge = window.MC_PIXEL_BRIDGE;
    if (!bridge || typeof bridge.fireOnce !== 'function') {
        // Core bridge didn't load (chimpstatic/CSP block, network failure,
        // or the bridge JS itself was cached stale on a non-bumped version).
        // Without core's fireOnce + sendTelemetry we can't safely operate.
        return;
    }

    var cfg = bridge.cfg;
    if (!cfg) return;

    /**
     * Parse the price out of the AJAX-refreshed product_prices HTML
     * fragment that `prestashop.on('updatedProduct', resp)` delivers.
     * Prefers schema.org microdata (universal across themes); falls
     * back to common price-element classnames. Returns 0 on extraction
     * failure.
     */
    function extractPriceFromFragment(html) {
        if (!html || typeof html !== 'string') return 0;
        try {
            var doc = new DOMParser().parseFromString(html, 'text/html');
            var el = doc.querySelector('[itemprop="price"]');
            if (el) {
                var content = el.getAttribute('content');
                if (content) return parseFloat(content) || 0;
                return parseFloat(String(el.textContent).replace(/[^0-9.\-]/g, '')) || 0;
            }
            // Fallback selectors used across stock + many custom themes
            el = doc.querySelector('.current-price, .product__price, .product-price');
            if (el) {
                return parseFloat(String(el.textContent).replace(/[^0-9.\-]/g, '')) || 0;
            }
        } catch (e) { /* parser failure — fall through to 0 */ }
        return 0;
    }

    /**
     * Parse the `[data-product]` JSON from PS's `product_details` fragment.
     * The presented product's `embedded_attributes` is JSON-encoded into a
     * data attribute on `<div id="product-details" data-product='...'>` by
     * `catalog/_partials/product-details.tpl` — present in EVERY PS version
     * 1.7.0.6 → 9.1 × classic + hummingbird (the class on the wrapper
     * differs across versions: `js-product-details` on 1.7.7+, no class
     * on 1.7.0.6 — but the data attribute itself is universal).
     *
     * `displayAjaxRefresh` re-renders this fragment on every combination
     * change, so the JSON is FRESH per variant — id_product_attribute,
     * price, reference, cover.bySize, etc. all reflect the new variant.
     *
     * Fields commonly present in embedded_attributes:
     *   id_product, id_product_attribute, name, reference, link_rewrite
     *   price (with-tax formatted string with currency symbol)
     *   price_tax_exc (numeric, tax-excluded)
     *   price_amount (numeric, with-tax — PS 1.7.5+, may be missing)
     *   cover.bySize.<size>.url, images[].bySize.<size>.url
     *   id_manufacturer, id_category_default (IDs only, no resolved names)
     *
     * Returns the parsed object or null on any extraction failure.
     */
    function extractEmbeddedAttributesFromFragment(html) {
        if (!html || typeof html !== 'string') return null;
        try {
            var doc = new DOMParser().parseFromString(html, 'text/html');
            var el = doc.querySelector('[data-product]');
            if (!el) return null;
            var json = el.getAttribute('data-product');
            if (!json) return null;
            return JSON.parse(json);
        } catch (e) {
            return null;
        }
    }

    /**
     * Build a Mailchimp Pixel product payload from the parsed embedded
     * attributes JSON, falling back to `cfg.initialProduct` for fields
     * NOT in the JSON (currency, vendor name, category names, productUrl).
     * The embedded JSON has id_manufacturer + id_category_default as ints
     * (not resolved names) so we can't get vendor/categories from it
     * cleanly; that's why we lean on the initial payload (built server-
     * side with the full presenter and so already has the resolved names).
     */
    function buildPayloadFromEmbeddedAttributes(emb, initial) {
        if (!emb) return null;
        initial = initial || {};

        var productId = (emb.id_product != null && emb.id_product !== '')
            ? emb.id_product
            : (emb.id != null ? emb.id : null);
        var variantId = (emb.id_product_attribute != null && emb.id_product_attribute !== '')
            ? emb.id_product_attribute
            : 0;
        var resolvedId = (variantId && String(variantId) !== '0')
            ? variantId
            : productId;

        if (productId == null || String(productId) === '') return null;

        // Price preference (with-tax, customer-facing):
        //   1. emb.price_amount (PS 1.7.5+: numeric, with-tax — best)
        //   2. emb.price (formatted string with currency symbol — strip non-numeric)
        //   3. emb.price_tax_exc (numeric, tax-excluded — last resort: better
        //      than 0 but slightly wrong if tax is applied)
        var price = null;
        if (typeof emb.price_amount === 'number') {
            price = emb.price_amount;
        } else if (typeof emb.price === 'string' && emb.price !== '') {
            var stripped = emb.price.replace(/[^0-9.,\-]/g, '').replace(/,/g, '.');
            // Some locales use thousands-separators which we'd just have nuked.
            // If multiple dots remain after the strip, keep only the last as
            // the decimal — PS-rendered prices follow the visitor's locale.
            var dotIx = stripped.lastIndexOf('.');
            if (dotIx >= 0 && stripped.indexOf('.') !== dotIx) {
                stripped = stripped.slice(0, dotIx).replace(/\./g, '') + stripped.slice(dotIx);
            }
            var parsed = parseFloat(stripped);
            if (!isNaN(parsed)) price = parsed;
        }
        if (price === null && typeof emb.price_tax_exc === 'number') {
            price = emb.price_tax_exc;
        }

        // Cover image — prefer the largest size we can find, fall back to
        // any size that's present.
        var imageUrl = '';
        if (emb.cover && typeof emb.cover === 'object') {
            var c = emb.cover;
            if (c.bySize) {
                imageUrl = (c.bySize.large_default && c.bySize.large_default.url)
                    || (c.bySize.default_xl && c.bySize.default_xl.url)
                    || (c.bySize.home_default && c.bySize.home_default.url)
                    || (c.bySize.medium_default && c.bySize.medium_default.url)
                    || '';
            }
            if (!imageUrl && c.large && c.large.url) imageUrl = c.large.url;
            if (!imageUrl && c.url) imageUrl = c.url;
        }

        return {
            id:         String(resolvedId),
            productId:  String(productId),
            title:      typeof emb.name === 'string' ? emb.name : (initial.title || ''),
            price:      (price !== null) ? price : null,
            // Currency cascade: initial.currency → window.prestashop.currency.iso_code → ''
            // (resolveCurrency lives in core bridge; uses initial when present,
            // falls back to PS-native runtime data on category/cart/checkout pages)
            currency:   (typeof bridge.resolveCurrency === 'function')
                ? bridge.resolveCurrency()
                : (initial.currency || ''),
            sku:        typeof emb.reference === 'string' ? emb.reference : (initial.sku || ''),
            productUrl: initial.productUrl
                ? rewriteProductUrlForVariant(initial.productUrl, productId, resolvedId)
                : '',
            imageUrl:   imageUrl || initial.imageUrl || '',
            vendor:     initial.vendor || '',
            categories: initial.categories || []
        };
    }

    /**
     * Parse the new cover image URL from displayAjaxRefresh's
     * `product_cover_thumbnails` fragment. PS theme variants render this
     * partial after combination change with the new combination's image
     * if one is set. Falls back to common selectors. Returns '' on
     * extraction failure — the caller decides whether to carry over the
     * previous URL or leave it empty.
     */
    function extractImageUrlFromFragment(html) {
        if (!html || typeof html !== 'string') return '';
        try {
            var doc = new DOMParser().parseFromString(html, 'text/html');
            // Schema.org first (universal across themes)
            var el = doc.querySelector('[itemprop="image"]');
            if (el) {
                var content = el.getAttribute('content') || el.getAttribute('src') || el.getAttribute('href');
                if (content) return String(content);
            }
            // Common selectors across classic + hummingbird + custom themes:
            //   classic: .product-cover img
            //   hummingbird: .product__image-cover img / .product-images__cover img
            //   generic: img with data-image-large-src or large image href
            el = doc.querySelector('.product-cover img, .product__image-cover img, .product-images__cover img, img.js-qv-product-cover');
            if (el) {
                return String(el.getAttribute('data-image-large-src')
                    || el.getAttribute('src') || '');
            }
            el = doc.querySelector('a[data-image-large-src]');
            if (el) return String(el.getAttribute('data-image-large-src') || '');
        } catch (e) { /* parser failure — fall through to '' */ }
        return '';
    }

    /**
     * Handler for `prestashop.on('updatedProduct', resp)`. The resp object
     * is the displayAjaxRefresh response — has `id_product_attribute` plus
     * the rendered HTML fragments. Builds a PRODUCT_VIEWED payload that
     * mirrors the initial fire's shape: every field from cfg.initialProduct
     * is carried over (title, currency, sku, imageUrl, vendor, categories)
     * with variant-sensitive ones (id, price, productUrl) refreshed for the
     * new combination.
     *
     * productUrl rewrite — PS encodes the variant in the URL path as
     * `/<idProduct>-<idVariant>-<slug>.html`. The initial payload's URL
     * carries the originally-active variant; we swap the variant segment
     * to match the new one. This keeps the Pixel log + the URL Mailchimp
     * receives consistent with what ProductSyncCommand's per-variant sync
     * sends to Mailchimp's REST API (ProductFormatter calls getProductLink
     * with id_product_attribute = variant id — same shape).
     */
    function onProductUpdated(resp) {
        if (!resp) return;
        if (!cfg.initialProduct) return; // can't carry over rest of payload
        // Opt-out short-circuit — see onCartUpdate in core bridge. Without
        // this, opted-out visitors emit PIXEL_DATA_INCOMPLETE telemetry
        // containing product/variant ids on combination changes when the
        // AJAX-fallback path is reached.
        if (bridge.isExplicitlyOptedOut && bridge.isExplicitlyOptedOut()) return;

        // ===== LAYER 1: PS embedded_attributes JSON ============================
        // Most accurate path. [data-product] JSON is in product_details
        // fragment in every PS 1.7.0.6 → 9.1 × classic + hummingbird.
        var emb = extractEmbeddedAttributesFromFragment(resp.product_details);
        var product = buildPayloadFromEmbeddedAttributes(emb, cfg.initialProduct);
        var via = 'prestashop.on(updatedProduct) + data-product JSON';

        // ===== LAYER 2: CSS-selector scrape ====================================
        // Falls back when product_details fragment is missing OR data-product
        // JSON failed to parse OR the embedded payload didn't validate.
        // This is the original behavior we had — kept as a defense layer.
        if (!product || !bridge.hasRequiredPixelFields(product)) {
            var newPaid = String(resp.id_product_attribute || cfg.initialProduct.productId);
            var newPrice = extractPriceFromFragment(resp.product_prices);
            var newImageUrl = extractImageUrlFromFragment(resp.product_cover_thumbnails);
            var productId = cfg.initialProduct.productId;
            var newId = newPaid && newPaid !== '0' ? newPaid : productId;
            product = {};
            for (var k in cfg.initialProduct) {
                if (Object.prototype.hasOwnProperty.call(cfg.initialProduct, k)) {
                    product[k] = cfg.initialProduct[k];
                }
            }
            product.id = String(newId);
            product.productId = String(productId);
            product.price = (newPrice && newPrice > 0) ? newPrice : cfg.initialProduct.price;
            if (newImageUrl) product.imageUrl = newImageUrl;
            if (product.productUrl) {
                product.productUrl = rewriteProductUrlForVariant(product.productUrl, productId, newId);
            }
            via = 'prestashop.on(updatedProduct) + css-scrape';
        }

        // ===== LAYER 3: AJAX fallback to /pixelproductinfo =====================
        // Triggers only when layers 1 + 2 BOTH failed to extract a complete
        // payload (rare — would need an exotic custom theme that strips
        // data-product AND lacks schema.org price markup AND doesn't expose
        // common cover-image classes). Async; doesn't block UI.
        if (!bridge.hasRequiredPixelFields(product)) {
            var ajaxProductId = (product && product.productId) || cfg.initialProduct.productId;
            var ajaxVariantId = resp.id_product_attribute || (product && product.id) || 0;
            bridge.fetchProductInfo(ajaxProductId, ajaxVariantId, function (serverProduct) {
                if (serverProduct && bridge.hasRequiredPixelFields(serverProduct)) {
                    bridge.fireOnce('PRODUCT_VIEWED', { product: serverProduct },
                        String(serverProduct.id), 'combination change',
                        'prestashop.on(updatedProduct) + ajax-fallback');
                    return;
                }
                bridge.sendTelemetry('PIXEL_DATA_INCOMPLETE', 2,
                    'combination change for product ' + ajaxProductId
                    + (ajaxVariantId ? '/' + ajaxVariantId : '')
                    + ' — required fields missing after data-product + css-scrape + AJAX fallback');
            });
            return;
        }

        bridge.fireOnce('PRODUCT_VIEWED', { product: product },
            String(product.id), 'combination change', via);
    }

    /**
     * Swap the id_product_attribute segment in a PS product URL.
     * PS default friendly URL format:
     *   `<scheme>://<host>/<idProduct>-<idVariant>-<slug>.html[#/<attrs>]`
     * Strategy:
     *   1. Try the regex slug-swap — works for stock PS friendly URLs.
     *   2. If the regex didn't match (custom URL rewrite, SEO module like
     *      SEOExpert / SEO Pretty URLs / etc., PS9 custom routes),
     *      fall back to `location.origin + location.pathname` — PS updates
     *      `location.href` via History.pushState on combination switch, so
     *      this always reflects the current variant's canonical URL.
     *      Stripping query + hash gives Mailchimp a clean tracking URL
     *      (no `?utm_*`, no `?_cb=*`, no `#/size-s/color-black`).
     *   3. Final fallback (no location available — SSR / edge cases):
     *      return the original URL untouched.
     */
    function rewriteProductUrlForVariant(url, productId, variantId) {
        if (!url || !productId || !variantId) return url;
        var pid = String(productId).replace(/[^0-9]/g, '');
        var vid = String(variantId).replace(/[^0-9]/g, '');
        if (!pid || !vid) return url;
        // Only target the path (everything up to but excluding the hash).
        var hashAt = url.indexOf('#');
        var pathPart = hashAt >= 0 ? url.slice(0, hashAt) : url;
        var hashPart = hashAt >= 0 ? url.slice(hashAt) : '';
        var rewritten = pathPart.replace(
            new RegExp('(/)' + pid + '-(\\d+)-'),
            '$1' + pid + '-' + vid + '-'
        );
        if (rewritten !== pathPart) {
            // Regex matched + rewrote — append back the original hash.
            return rewritten + hashPart;
        }
        // Regex didn't match → fall back to the live browser URL which PS
        // keeps in sync with the selected combination via History.pushState.
        if (typeof location !== 'undefined'
            && location.origin
            && location.pathname) {
            return location.origin + location.pathname;
        }
        // Last resort — preserve original URL.
        return url;
    }

    /**
     * Marker presence detection — diagnostic only. Reports whether the
     * legacy displayProductAdditionalInfo hook is rendering its inline-script
     * marker in the active theme. Only meaningful when the merchant has
     * opted into the legacy server-side handler (config flag
     * `cfg.hookDisplayProductAdditionalInfoEnabled === true`).
     *
     * When the handler is DISABLED (the default), the hook is server-side
     * no-op by design — the marker will always be missing, but that's
     * expected (not an issue) since the bridge's [data-product] JSON parser
     * covers combination change universally. We skip the diagnostic
     * entirely in that case to avoid flooding the log with false-positive
     * warnings.
     *
     * Throttled per session via sessionStorage: a missing-marker theme
     * would otherwise fire one warn-level entry PER PRODUCT PAGE LOAD per
     * visitor. Without a throttle, a busy store would fill the ring buffer
     * rapidly with the same "your theme doesn't have the hook" message.
     * sessionStorage keeps it to one entry per visitor session.
     */
    function detectHookPresence() {
        // Skip diagnostic when the handler is disabled — the hook is no-op
        // server-side, marker absence is expected, no signal to surface.
        if (!cfg.hookDisplayProductAdditionalInfoEnabled) return;
        try {
            if (sessionStorage.getItem('mc_pixel_hook_telemetry_sent')) return;
            sessionStorage.setItem('mc_pixel_hook_telemetry_sent', '1');
        } catch (e) { /* private browsing — fall through */ }
        var marker = document.querySelector('.mc-pixel-hook-marker');
        if (marker) {
            cfg._hookWired = true;
            if (cfg.pixelLogLevel === 'verbose') {
                bridge.sendTelemetry('PIXEL_HOOK_WIRED', 1,
                    'displayProductAdditionalInfo present in active theme');
            }
        } else {
            cfg._hookWired = false;
            bridge.sendTelemetry('PIXEL_HOOK_MISSING', 2,
                'displayProductAdditionalInfo handler is enabled but the '
                + 'hook is not rendering (custom theme stripping the hook?) '
                + '— bridge falls back to data-product JSON / AJAX cascade');
        }
    }

    // Wire-up: fire initial, check marker, subscribe to combination-change
    // event. cfg.initialProduct presence is guaranteed by the server-side
    // gating (this file is only loaded when on product page); guard anyway
    // for defense-in-depth.
    if (cfg.initialProduct && cfg.initialProduct.id) {
        // via='jsdef' identifies this as the actionFrontControllerSetMedia
        // → MC_PIXEL.initialProduct JsDef path (the bridge reading the
        // server-emitted payload on init). Distinguishes from `via=hook`
        // (queue push from displayProductAdditionalInfo) which would
        // otherwise carry the same source=initial tag.
        bridge.fireOnce('PRODUCT_VIEWED', { product: cfg.initialProduct },
            String(cfg.initialProduct.id), 'initial pageload',
            'jsdef (actionFrontControllerSetMedia)');
        detectHookPresence();
        if (window.prestashop && typeof window.prestashop.on === 'function') {
            try {
                window.prestashop.on('updatedProduct', onProductUpdated);
            } catch (e) { /* never break the storefront on a listener error */ }
        }
    }

    // Expose for the standalone test fixture + browser-console debug
    bridge.product = {
        onProductUpdated: onProductUpdated,
        detectHookPresence: detectHookPresence,
        extractPriceFromFragment: extractPriceFromFragment,
        extractImageUrlFromFragment: extractImageUrlFromFragment,
        extractEmbeddedAttributesFromFragment: extractEmbeddedAttributesFromFragment,
        buildPayloadFromEmbeddedAttributes: buildPayloadFromEmbeddedAttributes,
        rewriteProductUrlForVariant: rewriteProductUrlForVariant
    };
})();
