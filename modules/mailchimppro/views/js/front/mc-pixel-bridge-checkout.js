/**
 * @author    Mailchimp
 * @copyright Mailchimp
 * @license   Proprietary
 */

/**
 * Mailchimp Pixel bridge — CHECKOUT_STARTED wiring (checkout-page-only).
 *
 * Registered by mailchimppro.php ONLY when the active FO controller is
 * the checkout/order page (php_self === 'order'). Runs AFTER the core
 * bridge (priority 200 < 210). The IIFE auto-fires on load — the
 * conditional registration is the gate, no JS-side page_name check.
 *
 * Mailchimp 3P spec — note the `checkout` wrapper (NOT `cart` like
 * CART_VIEWED) with TWO required IDs:
 *
 *   { checkout: {
 *       id:        'chk_9',         // REQUIRED — checkout identifier
 *       cartId:    '9',             // REQUIRED — associated PS cart id
 *       lineItems: [{item, quantity}, ...],
 *       subtotalPrice, totalTax, totalShipping, totalPrice,  // optional
 *       currency, customerId,                                  // optional
 *       discounts: [{code, amount, type}, ...]                // optional
 *   } }
 *
 * checkout.id derivation:
 *   PS has no native checkout entity — the cart persists through the
 *   entire multi-step checkout flow under the same id_cart, only
 *   becoming id_order at the final "Confirm order" submit. So we
 *   derive checkout.id = 'chk_' + cartId. This means:
 *     - Stable across page reloads + step navigation (cart_id is)
 *     - Distinct string from cart.id (per Mailchimp spec)
 *     - fireOnce dedupes step navigation by this fingerprint —
 *       visitor walking through PS's 4 checkout steps fires ONE event
 *
 * Cascade (mirrors CART_VIEWED):
 *   1. LAYER 1: window.prestashop.cart (zero AJAX). Build lineItems[]
 *      via core's buildCartLineItems + extract subtotals/totals/
 *      discounts/customerId from PS native data.
 *   2. LAYER 2: AJAX /pixelcartinfo?with_items=1&with_checkout=1 —
 *      server runs Cart::getProducts + Cart::getOrderTotal + enrichment.
 *   3. Both fail → PIXEL_DATA_INCOMPLETE.
 *
 * Reuses ALL existing core helpers — only the checkout-specific extras
 * extraction is checkout-bundle-local.
 */
(function () {
    'use strict';

    var bridge = window.MC_PIXEL_BRIDGE;
    if (!bridge || typeof bridge.fireOnce !== 'function') return;
    var cfg = bridge.cfg;
    if (!cfg) return;

    /**
     * Extract checkout-only payload extras from window.prestashop.cart.
     * All optional per Mailchimp spec — caller includes whichever are
     * non-null in the final payload.
     */
    function extractCheckoutExtras(psCart) {
        var extras = {};
        if (!psCart) return extras;
        // Subtotal (products only, before tax/shipping)
        if (psCart.subtotals && psCart.subtotals.products
            && typeof psCart.subtotals.products.amount === 'number') {
            extras.subtotalPrice = psCart.subtotals.products.amount;
        }
        // Shipping
        if (psCart.subtotals && psCart.subtotals.shipping
            && typeof psCart.subtotals.shipping.amount === 'number') {
            extras.totalShipping = psCart.subtotals.shipping.amount;
        }
        // Tax — read PS's `subtotals.tax` signal. The PS CartPresenter /
        // CartLazyArray (PS 1.7.7 → 9.1, theme-independent) sets:
        //   - `subtotals.tax = null`                  → tax-INCLUDED display
        //                                                mode (PS_TAX_INC).
        //                                                subtotals.products /
        //                                                totals.total already
        //                                                include tax; reporting
        //                                                a separate totalTax
        //                                                would double-count.
        //   - `subtotals.tax = {amount: 15.42, ...}`  → tax-EXCLUDED display
        //                                                mode (PS_TAX_EXC).
        //                                                Use the broken-out
        //                                                value as-is.
        //   - `subtotals.tax === undefined`           → key absent (PS 1.7.0.6
        //                                                doesn't expose
        //                                                window.prestashop.cart
        //                                                at all, so we never
        //                                                reach here; for any
        //                                                other anomaly leave
        //                                                totalTax unset and
        //                                                let Layer 2 (server)
        //                                                be authoritative).
        if (psCart.subtotals && 'tax' in psCart.subtotals) {
            if (psCart.subtotals.tax === null) {
                extras.totalTax = 0;
            } else if (psCart.subtotals.tax
                && typeof psCart.subtotals.tax.amount === 'number') {
                extras.totalTax = psCart.subtotals.tax.amount;
            }
        }
        // Order total (with tax + shipping + discounts applied). Try the
        // PS totals chain in preference order: totals.total → total_including_tax.
        if (psCart.totals && psCart.totals.total
            && typeof psCart.totals.total.amount === 'number') {
            extras.totalPrice = psCart.totals.total.amount;
        } else if (psCart.totals && psCart.totals.total_including_tax
            && typeof psCart.totals.total_including_tax.amount === 'number') {
            extras.totalPrice = psCart.totals.total_including_tax.amount;
        }
        return extras;
    }

    /**
     * Flatten PS's discounts + applied vouchers into the Mailchimp
     * discount array shape: { code, amount, type }. PS exposes two
     * source arrays (legacy + voucher), we walk both.
     */
    function extractDiscounts(psCart) {
        var out = [];
        if (!psCart) return out;
        // PS's cart.vouchers.added is an OBJECT keyed by id_cart_rule (NOT
        // an array) — e.g. { "1": {code, reduction_percent, ...} }. Iterate
        // via for/in. Verified on PS 9.1; same shape on PS 1.7+.
        if (psCart.vouchers && psCart.vouchers.added
            && typeof psCart.vouchers.added === 'object') {
            for (var key in psCart.vouchers.added) {
                if (!Object.prototype.hasOwnProperty.call(psCart.vouchers.added, key)) continue;
                var v = psCart.vouchers.added[key];
                if (!v || typeof v !== 'object') continue;
                var entry = {};
                if (v.code) entry.code = String(v.code);
                // reduction_percent + reduction_amount are STRINGS on the
                // PS-presented voucher (e.g. "10.00") — coerce to numeric
                // and pick whichever is non-zero. Percent takes precedence
                // when both are set (rare but possible).
                var pct = v.reduction_percent !== undefined ? parseFloat(v.reduction_percent) : 0;
                var amt = v.reduction_amount !== undefined ? parseFloat(v.reduction_amount) : 0;
                if (!isNaN(pct) && pct > 0) {
                    entry.amount = pct;
                    entry.type = 'percentage';
                } else if (!isNaN(amt) && amt > 0) {
                    entry.amount = amt;
                    entry.type = 'amount';
                } else if (typeof v.value === 'number' && v.value > 0) {
                    entry.amount = v.value;
                    entry.type = 'amount';
                }
                if (entry.code) out.push(entry);
            }
        }
        // Alternate path — cart.discounts[] (some PS versions / custom code).
        if (psCart.discounts && psCart.discounts.length) {
            for (var j = 0; j < psCart.discounts.length; j++) {
                var d = psCart.discounts[j];
                if (!d) continue;
                var entry2 = {};
                if (d.code) entry2.code = String(d.code);
                if (typeof d.amount === 'number') entry2.amount = d.amount;
                if (d.type) entry2.type = String(d.type);
                // Skip if we already have this code from vouchers.added
                var dup = false;
                for (var k = 0; k < out.length; k++) {
                    if (out[k].code === entry2.code) { dup = true; break; }
                }
                if (!dup && entry2.code) out.push(entry2);
            }
        }
        return out;
    }

    /**
     * customerId per Mailchimp's identify convention — when the visitor
     * is logged in, use the sha256-hashed email (the same value our
     * identify() call sends to Mailchimp for cross-event correlation).
     * Reuses MC_PIXEL.identifyValue which setMedia already emitted for
     * the bridge's identifyOrReset path.
     */
    function resolveCustomerId() {
        if (cfg.identifyValue) return String(cfg.identifyValue);
        // identifyAjaxMode: the value gets fetched async; if not yet
        // resolved at fire time we just omit customerId (optional field).
        return '';
    }

    /**
     * Common firing path — invoked once Layer 1 OR Layer 2 has lineItems.
     * Layer 2 caller pre-resolved cartId from /pixelcartinfo so we skip
     * the redundant resolveCartId() call in that branch.
     */
    function fireCheckoutStarted(lineItems, extras, viaTag, preResolvedCartId) {
        var currency = bridge.resolveCurrency();
        function finishFire(cartId) {
            if (!cartId) {
                bridge.sendTelemetry('PIXEL_DATA_INCOMPLETE', 2,
                    'checkout-started: cartId unavailable (JsDef=0 + AJAX failed)');
                return;
            }
            // Dedupe by chk_<cartId> — the same identifier we use for
            // checkout.id. Step navigation in PS's multi-step checkout
            // hits this handler multiple times; same fingerprint each
            // time → ONE mcTrack reaches Mailchimp.
            var checkoutId = 'chk_' + cartId;
            var fp = 'checkout:' + checkoutId;
            var checkoutPayload = {
                id: checkoutId,
                cartId: String(cartId),
                lineItems: lineItems
            };
            if (currency) checkoutPayload.currency = currency;
            // Merge in extras (subtotal/tax/shipping/totalPrice/discounts/customerId)
            if (extras) {
                if (typeof extras.subtotalPrice === 'number') checkoutPayload.subtotalPrice = extras.subtotalPrice;
                if (typeof extras.totalTax === 'number') checkoutPayload.totalTax = extras.totalTax;
                if (typeof extras.totalShipping === 'number') checkoutPayload.totalShipping = extras.totalShipping;
                if (typeof extras.totalPrice === 'number') checkoutPayload.totalPrice = extras.totalPrice;
                if (extras.discounts && extras.discounts.length) checkoutPayload.discounts = extras.discounts;
                if (extras.customerId) checkoutPayload.customerId = extras.customerId;
            }
            // customerId fallback to MC_PIXEL.identifyValue if extras
            // didn't carry one (Layer 1 path)
            if (!checkoutPayload.customerId) {
                var cid = resolveCustomerId();
                if (cid) checkoutPayload.customerId = cid;
            }
            bridge.fireOnce('CHECKOUT_STARTED', { checkout: checkoutPayload },
                fp, 'checkout-page-load', viaTag);
        }
        if (preResolvedCartId) {
            finishFire(preResolvedCartId);
        } else {
            bridge.resolveCartId(finishFire);
        }
    }

    function onCheckoutStarted() {
        // Opt-out short-circuit — see onCartUpdate in the core bridge for
        // the full rationale. Without this guard, opted-out visitors trigger
        // PIXEL_DATA_INCOMPLETE telemetry containing checkout-context data
        // when fetchCartInfo correctly refuses the AJAX.
        if (bridge.isExplicitlyOptedOut && bridge.isExplicitlyOptedOut()) return;
        // ===== LAYER 1: window.prestashop.cart (PS native, zero AJAX) =====
        var psCart = window.prestashop && window.prestashop.cart;
        var lineItems = [];
        if (psCart && psCart.products && psCart.products.length) {
            // Reuse the SAME helper CART_VIEWED uses for line-item building.
            // Single source of truth for raw PS line → Mailchimp item shape.
            // (buildCartLineItems would also work but lives in the cart
            // bundle which isn't loaded here — call core helpers directly.)
            for (var i = 0; i < psCart.products.length; i++) {
                var line = psCart.products[i];
                var item = bridge.buildCartLineProductItem(
                    line, line.id_product, line.id_product_attribute);
                if (!item || !bridge.hasRequiredPixelFields(item)) continue;
                lineItems.push(bridge.wrapLineItem(item, line.cart_quantity));
            }
        }
        var extras = extractCheckoutExtras(psCart);
        var discounts = extractDiscounts(psCart);
        if (discounts.length) extras.discounts = discounts;

        // Layer 1 complete enough? (≥1 line + totalPrice)
        if (lineItems.length > 0 && typeof extras.totalPrice === 'number') {
            fireCheckoutStarted(lineItems, extras, 'window.prestashop.cart');
            return;
        }

        // Empty cart on a checkout page — odd state. Mailchimp's funnel
        // can't track a checkout-of-nothing. Silently skip.
        if (psCart && psCart.products && psCart.products.length === 0
            && (psCart.products_count === 0 || psCart.products_count === '0')) {
            return;
        }

        // Diagnostic — Layer 1 couldn't produce a usable payload but cart
        // ISN'T legitimately empty. Most often: window.prestashop / .cart
        // not populated by the active theme (custom theme stripping
        // assignGeneralPurposeVariables, 3rd-party checkout module that
        // doesn't honor PS conventions, etc.). Logged before Layer 2 fires
        // so support can diagnose "why is this store always hitting the
        // AJAX fallback" without inspecting the browser. Severity 1 (info)
        // — Layer 2 may still succeed.
        bridge.sendTelemetry('LAYER_1_MISSING', 1,
            'checkout-started: window.prestashop.cart unusable — '
            + (!window.prestashop ? 'window.prestashop undefined' :
              !psCart ? 'window.prestashop.cart undefined' :
              !psCart.products ? 'cart.products undefined' :
              !psCart.products.length ? 'cart.products empty (but count != 0)' :
              lineItems.length === 0 ? 'no line items survived hasRequiredPixelFields' :
              'totalPrice missing from extras')
            + ' — falling through to /pixelcartinfo');

        // ===== LAYER 2: AJAX /pixelcartinfo?with_items=1&with_checkout=1 =====
        bridge.fetchCartInfo({ withItems: true, withCheckout: true }, function (info) {
            if (info && info.cartId && info.lineItems && info.lineItems.length
                && typeof info.totalPrice === 'number') {
                var ajaxExtras = {
                    subtotalPrice: info.subtotalPrice,
                    totalTax: info.totalTax,
                    totalShipping: info.totalShipping,
                    totalPrice: info.totalPrice,
                    discounts: info.discounts,
                    customerId: info.customerId
                };
                fireCheckoutStarted(info.lineItems, ajaxExtras,
                    'pixelcartinfo + ajax-fallback', info.cartId);
                return;
            }
            bridge.sendTelemetry('PIXEL_DATA_INCOMPLETE', 2,
                'checkout-started: frontend data incomplete and '
                + '/pixelcartinfo AJAX fallback also failed');
        });
    }

    // Auto-fire on load — setMedia gates this bundle by php_self === 'order'
    onCheckoutStarted();

    // ===== OPC identify watcher (non-stock checkouts only) ================
    // Stock PS multi-step checkout submits each step as a form → full page
    // reload → next render fires identifyOrReset on the bridge core. That
    // path already binds the visitor to their entered email. NO watcher
    // needed there.
    //
    // 3rd-party OPC modules (OnePageCheckoutPS, SuperCheckout, etc.)
    // submit the personal-info step via AJAX without a page reload, so the
    // page-load identifyOrReset never sees the guest's email until the
    // visitor navigates to the next page (which on an OPC may never
    // happen until the final submit → order-confirmation). The result:
    // anonymous events during checkout, identified PURCHASED at the end.
    // Mailchimp can still stitch journey via PIXEL_VISITOR_ID, but
    // in-checkout segmentation (abandoned-checkout, etc.) needs identify
    // to fire DURING the checkout.
    //
    // Watcher strategy:
    //   1. FAST path — read window.prestashop.customer.email. Hits when
    //      the OPC module pushes the new email into prestashop.customer
    //      after its AJAX (Hummingbird + well-behaved modules do this).
    //   2. SLOW path — if FAST returned empty AND we haven't identified
    //      yet, AJAX /pixelcartinfo?with_customer_email=1. Server reads
    //      Cart::getCustomer()->email (authoritative — bound at the
    //      personal-info step server-side regardless of what the module
    //      pushes to JS state). One AJAX per checkout session at worst
    //      (dedupes after first success).
    //   3. Wire up to PS events: updateCart (cart state changed, may
    //      now include customer association), updatedAddress (address
    //      added/changed during checkout), updatedDeliveryForm
    //      (delivery method selected — implies email entered earlier).
    if (window.MC_PIXEL_CHECKOUT_MODE === 'non-stock'
        && typeof bridge.identify === 'function') {

        var lastIdentifiedEmail = null;     // exact email seen via fast path
        var lastIdentifiedHash = null;      // server-returned identifyValue (sha256 OR plain per format config)
        var ajaxIdentifyInFlight = false;   // suppress overlapping AJAX

        function formatIdentifyValueLocal(email) {
            // Mirror the format choice the server uses for the AJAX path
            // (cfg.identifyFormat ∈ {'EMAIL', 'EMAIL_SHA256'}). Hash the
            // email client-side via the SDK's expected format. Since we
            // don't bundle a sha256 implementation in the bridge, when
            // format=EMAIL_SHA256 we ALWAYS go through the AJAX path
            // (server hashes); when format=EMAIL we use the plain string.
            if ((cfg.identifyFormat || 'EMAIL_SHA256') === 'EMAIL') {
                return email;
            }
            return null; // signals caller to use the AJAX path for hashing
        }

        function tryIdentifyFromCheckout() {
            if (ajaxIdentifyInFlight) return;
            var email = String((window.prestashop && window.prestashop.customer && window.prestashop.customer.email) || '')
                .trim().toLowerCase();

            // FAST path — JS-side check
            if (email && email !== lastIdentifiedEmail) {
                var formatted = formatIdentifyValueLocal(email);
                if (formatted !== null) {
                    // EMAIL mode — plain email, no hashing needed
                    bridge.identify(formatted);
                    lastIdentifiedEmail = email;
                    return;
                }
                // EMAIL_SHA256 mode — fall through to AJAX so server hashes
            }

            // SLOW path — AJAX server for the canonical email + format
            if (lastIdentifiedHash) return; // already identified, don't re-AJAX
            ajaxIdentifyInFlight = true;
            bridge.fetchCartInfo({ withCustomerEmail: true }, function (info) {
                ajaxIdentifyInFlight = false;
                if (info && info.identifyValue
                    && info.identifyValue !== lastIdentifiedHash) {
                    bridge.identify(info.identifyValue);
                    lastIdentifiedHash = info.identifyValue;
                    // Mark FAST path as resolved too so subsequent calls
                    // exit at the top regardless of which path won.
                    if (email) lastIdentifiedEmail = email;
                    // Phone enrichment — fire phone identify alongside email
                    // when the server returned phoneValue. Pure single-page
                    // OPC modules that never reload between address-step and
                    // PURCHASED would otherwise skip phone identify until the
                    // post-purchase navigation.
                    if (info.phoneValue && typeof bridge.identifyPhone === 'function') {
                        bridge.identifyPhone(info.phoneValue);
                    }
                }
            });
        }

        if (window.prestashop && typeof window.prestashop.on === 'function') {
            // Three PS events that reliably fire during the checkout flow
            // when relevant state changes:
            //   updateCart           — cart contents OR cart-customer
            //                          association changed (fires on PS
            //                          1.7.0.6 → 9.1 × all themes)
            //   updatedDeliveryForm  — delivery method selected (visitor
            //                          must have entered email by this step)
            //                          (fires on PS 1.7.0.6 → 9.1 × all themes)
            //   updatedAddressForm   — address form submitted/updated
            //                          (PS 9.1 ONLY — older versions don't
            //                          emit this; the subscription is a
            //                          no-op on those, no harm done)
            //
            // Earlier draft included 'updatedAddress' (no Form suffix) —
            // verified via the JS-compat bundle to NOT exist on any PS
            // version; removed to avoid misleading dead listeners.
            ['updateCart', 'updatedDeliveryForm', 'updatedAddressForm']
                .forEach(function (evt) {
                    try { window.prestashop.on(evt, tryIdentifyFromCheckout); }
                    catch (e) { /* never break checkout on a listener error */ }
                });
        }
        // Also try once on load — covers modules that emit no signal at all
        // but DO have email populated by initial page render (rare on OPC,
        // common on multi-step where step 1 = email entry → page reload).
        tryIdentifyFromCheckout();
    }

    // Expose for tests / browser-console debug
    bridge.checkout = {
        onCheckoutStarted: onCheckoutStarted,
        fireCheckoutStarted: fireCheckoutStarted,
        extractCheckoutExtras: extractCheckoutExtras,
        extractDiscounts: extractDiscounts
    };
})();
