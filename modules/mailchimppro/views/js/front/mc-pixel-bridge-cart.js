/**
 * @author    Mailchimp
 * @copyright Mailchimp
 * @license   Proprietary
 */

/**
 * Mailchimp Pixel bridge — CART_VIEWED wiring (cart-page-only).
 *
 * This file is registered by mailchimppro.php ONLY when the active FO
 * controller is the cart page (php_self === 'cart') AND the request is
 * NOT an AJAX refresh. It runs AFTER the core bridge — both are `defer`,
 * core has a lower priority number → executes first.
 *
 * Dependencies (exposed by core bridge via window.MC_PIXEL_BRIDGE):
 *   - bridge.fireOnce(eventType, payload, fingerprint, source, via)
 *   - bridge.sendTelemetry(eventType, severity, message)
 *   - bridge.hasRequiredPixelFields(item)
 *   - bridge.resolveCurrency()
 *   - bridge.resolveCartId(cb)
 *   - bridge.fetchCartInfo(withItems, cb)   (Layer 2 server-built recovery)
 *   - bridge.buildCartLineProductItem(line, idProduct, idProductAttribute)
 *   - bridge.cfg                            (= window.MC_PIXEL)
 *
 * Cascade:
 *   1. Layer 1 — read window.prestashop.cart (PS native, no AJAX). Build
 *      lineItems[] from cart.products[] + extract totalPrice from
 *      cart.totals.total.amount.
 *   2. Layer 2 — AJAX /pixelcartinfo?with_items=1 when frontend data is
 *      missing/incomplete (custom theme stripping JsDef, page-cache bug,
 *      etc.). Server runs Cart::getProducts() once + enriches each line
 *      with productUrl + categories before returning.
 *   3. Both fail → PIXEL_DATA_INCOMPLETE telemetry, mcTrack NOT called.
 *
 * NOT handled here:
 *   - PRODUCT_ADDED_TO_CART (fires from anywhere → core bridge)
 *   - Updates while viewing cart (qty change, item remove) — those go
 *     through the `updateCart` event handled by core. Mailchimp's 3P spec
 *     doesn't have a CART_UPDATED event, so we don't re-fire CART_VIEWED.
 */
(function () {
    'use strict';

    var bridge = window.MC_PIXEL_BRIDGE;
    if (!bridge || typeof bridge.fireOnce !== 'function') {
        // Core bridge didn't load — without its helpers we can't safely
        // operate. Silently no-op (the bridge's defense-in-depth principle).
        return;
    }
    var cfg = bridge.cfg;
    if (!cfg) return;

    /**
     * Build the lineItems[] array for CART_VIEWED from PS's native
     * window.prestashop.cart.products[]. Each entry follows Mailchimp's
     * 3P spec — { item, quantity } wrapper, same pattern PRODUCT_ADDED_TO_CART
     * uses.
     *
     * Drops lines whose required fields aren't extractable. Returns array
     * (possibly empty) — caller decides whether to fire or fall through to
     * Layer 2 AJAX recovery.
     */
    function buildCartLineItems(psProducts) {
        var out = [];
        if (!psProducts || !psProducts.length) return out;
        for (var i = 0; i < psProducts.length; i++) {
            var line = psProducts[i];
            var item = bridge.buildCartLineProductItem(line, line.id_product, line.id_product_attribute);
            if (!item || !bridge.hasRequiredPixelFields(item)) continue;
            // wrapLineItem produces { item, quantity } — same wrapper shape
            // PRODUCT_ADDED_TO_CART's `product` object uses (with price/currency
            // siblings on that event). Single source of truth for the wrapper.
            out.push(bridge.wrapLineItem(item, line.cart_quantity));
        }
        return out;
    }

    /**
     * Common firing path — shared by Layer 1 (frontend) and Layer 2 (AJAX).
     * Layer 2 caller already has cartId from /pixelcartinfo and passes it
     * as preResolvedCartId to skip a redundant resolveCartId() call.
     */
    function fireCartViewed(lineItems, totalPrice, productsCount, viaTag, preResolvedCartId) {
        var currency = bridge.resolveCurrency();
        function finishFire(cartId) {
            if (!cartId) {
                bridge.sendTelemetry('PIXEL_DATA_INCOMPLETE', 2,
                    'cart-viewed: cartId unavailable (JsDef=0 + AJAX failed)');
                return;
            }
            var fp = 'view:' + cartId + ':' + productsCount;
            var cartPayload = {
                id: String(cartId),
                lineItems: lineItems
            };
            if (typeof totalPrice === 'number' && totalPrice >= 0) {
                cartPayload.totalPrice = totalPrice;
            }
            if (currency) {
                cartPayload.currency = currency;
            }
            bridge.fireOnce('CART_VIEWED', { cart: cartPayload },
                fp, 'cart-page-load', viaTag);
        }
        if (preResolvedCartId) {
            finishFire(preResolvedCartId);
        } else {
            bridge.resolveCartId(finishFire);
        }
    }

    /**
     * Cart page init handler. The setMedia hook guarantees this file is
     * only loaded when php_self === 'cart', so we don't double-check
     * page_name here — every load IS the cart page.
     */
    function onCartViewed() {
        // Visitor opted out: skip the whole event chain. Without this guard
        // the handler runs end-to-end and may fire PIXEL_DATA_INCOMPLETE
        // containing visitor activity ("cart-viewed: cartId unavailable...")
        // — because fetchCartInfo short-circuits on opt-out, which upstream
        // code then mistakes for a failed AJAX. Same fix applied across all
        // event handlers; see core bridge onCartUpdate for the rationale.
        if (bridge.isExplicitlyOptedOut && bridge.isExplicitlyOptedOut()) return;
        // ===== LAYER 1: build from window.prestashop.cart (PS native) =====
        var psCart = window.prestashop && window.prestashop.cart;
        var lineItems = (psCart && psCart.products && psCart.products.length)
            ? buildCartLineItems(psCart.products)
            : [];
        var totalPrice = null;
        if (psCart && psCart.totals) {
            if (psCart.totals.total && typeof psCart.totals.total.amount === 'number') {
                totalPrice = psCart.totals.total.amount;
            } else if (psCart.totals.total_including_tax
                && typeof psCart.totals.total_including_tax.amount === 'number') {
                totalPrice = psCart.totals.total_including_tax.amount;
            } else if (psCart.subtotals && psCart.subtotals.products
                && typeof psCart.subtotals.products.amount === 'number') {
                totalPrice = psCart.subtotals.products.amount;
            }
        }
        var productsCount = psCart && psCart.products_count
            ? psCart.products_count
            : lineItems.length;

        // Layer 1 complete enough?
        if (lineItems.length > 0 && typeof totalPrice === 'number') {
            fireCartViewed(lineItems, totalPrice, productsCount, 'window.prestashop.cart');
            return;
        }

        // Empty cart — PS authoritative says no products. Legitimate state,
        // not an error. Silently skip.
        if (psCart && psCart.products && psCart.products.length === 0
            && (psCart.products_count === 0 || psCart.products_count === '0')) {
            return;
        }

        // Diagnostic — Layer 1 couldn't produce a usable payload but the cart
        // ISN'T legitimately empty. Most often: window.prestashop / .cart not
        // populated by the active theme (custom theme stripping
        // assignGeneralPurposeVariables, broken initContent ordering, etc.).
        // Logged before Layer 2 fires so support can diagnose "why is this
        // store always hitting the AJAX fallback" without inspecting the
        // browser. Severity 1 (info) — Layer 2 may still succeed.
        bridge.sendTelemetry('LAYER_1_MISSING', 1,
            'cart-viewed: window.prestashop.cart unusable — '
            + (!window.prestashop ? 'window.prestashop undefined' :
              !psCart ? 'window.prestashop.cart undefined' :
              !psCart.products ? 'cart.products undefined' :
              !psCart.products.length ? 'cart.products empty (but count != 0)' :
              'totals.total missing')
            + ' — falling through to /pixelcartinfo');

        // ===== LAYER 2: AJAX /pixelcartinfo?with_items=1 =====
        bridge.fetchCartInfo(true, function (info) {
            if (info && info.cartId && info.lineItems && info.lineItems.length
                && typeof info.totalPrice === 'number') {
                fireCartViewed(info.lineItems, info.totalPrice,
                    info.productsCount || info.lineItems.length,
                    'pixelcartinfo + ajax-fallback', info.cartId);
                return;
            }
            bridge.sendTelemetry('PIXEL_DATA_INCOMPLETE', 2,
                'cart-viewed: frontend data incomplete and /pixelcartinfo '
                + 'AJAX fallback also failed');
        });
    }

    // Wire up on load. The setMedia hook gating ensures this file runs
    // only on actual cart pageloads, so no further page_name check needed.
    onCartViewed();

    // Expose for the standalone test fixture + browser-console debug
    bridge.cart = {
        onCartViewed: onCartViewed,
        buildCartLineItems: buildCartLineItems,
        fireCartViewed: fireCartViewed
    };
})();
