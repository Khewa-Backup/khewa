/**
 * @author    Mailchimp
 * @copyright Mailchimp
 * @license   Proprietary
 */

/**
 * Mailchimp Pixel bridge — PURCHASED wiring (order-confirmation page only).
 *
 * Registered by mailchimppro.php via the 3-prong detection in
 * hookActionFrontControllerSetMedia (any of: php_self='order-confirmation'
 * | page_name='order-confirmation' | php_self in MAILCHIMP_PIXEL_ORDER_
 * CONFIRMATION_CONTROLLERS BO list). Runs AFTER the core bridge
 * (priority 200 < 210). The IIFE auto-fires on load.
 *
 * Mailchimp 3P spec — note the `order` wrapper:
 *
 *   { order: {
 *       id:        '<order id>',                    // REQUIRED
 *       lineItems: [{ item, quantity, price }, ...], // REQUIRED
 *       subtotalPrice, totalTax, totalShipping, totalPrice,  // optional
 *       currency, customerId, discounts                       // optional
 *   } }
 *
 * Cascade (2 layers — no JsDef Layer 0 because the SAME hook payload-
 * builder produces the payload server-side anyway; PURCHASED is a
 * one-shot terminal event, not a presenter-driven update like
 * PRODUCT_VIEWED):
 *
 *   LAYER 1: displayOrderConfirmation hook → MC_PIXEL_EVENTS_QUEUE push
 *     (handled by core bridge's installEventQueueInterceptor; this file
 *     doesn't need to "do" anything for Layer 1 — the queue interceptor
 *     drains the payload PHP already pushed inline). Hits whenever the
 *     active OrderConfirmation controller fires the hook (stock + most
 *     3rd-party modules that extend OrderConfirmationController).
 *
 *   LAYER 2: AJAX /pixelorderinfo?id_order=X (or id_cart=Y&key=K for
 *     guests) — fires when the queue didn't get a PURCHASED push within
 *     a small window (typical case: 3rd-party module with a custom
 *     confirmation controller that doesn't fire the hook). URL context
 *     comes from setMedia's MC_PIXEL_ORDER_CONTEXT JsDef.
 *
 * fireOnce dedupes by `order.id` so even if BOTH layers happen to
 * produce a payload (well-behaved 3rd-party module that fires the hook
 * AND matches setMedia's 3-prong), Mailchimp gets ONE PURCHASED per
 * order render.
 *
 * If neither layer succeeds (queue empty + AJAX failed/no-context),
 * log PIXEL_DATA_INCOMPLETE at warn severity and skip.
 */
(function () {
    'use strict';

    var bridge = window.MC_PIXEL_BRIDGE;
    if (!bridge || typeof bridge.fireOnce !== 'function') return;
    var cfg = bridge.cfg;
    if (!cfg) return;

    var orderContext = window.MC_PIXEL_ORDER_CONTEXT || null;
    var orderInfoUrl = cfg.orderInfoUrl || '';

    /**
     * Layer 2 — AJAX fallback to /pixelorderinfo. Server-side resolves
     * the Order (via id_order or id_cart+key) and returns the SAME
     * payload shape the displayOrderConfirmation hook would emit.
     *
     * Two URL paths:
     *   - Logged-in: ?id_order=X (PS includes id_order in URL when
     *     visitor is authenticated during checkout)
     *   - Guest:     ?id_cart=Y&key=K (no id_order — guest uses
     *     secure_key for auth on the order-confirmation URL)
     *
     * If MC_PIXEL_ORDER_CONTEXT is missing (setMedia didn't detect the
     * page — should never happen since this file's loading IS the
     * detection's job, but defensive) OR if neither id_order nor the
     * guest pair are present (custom controller broke URL conventions),
     * skip silently with telemetry.
     */
    function fireFromAjax() {
        // Explicit opt-out short-circuit: bridge.fireOnce → mcTrack would
        // discard the event anyway, so don't bother the server with a
        // round-trip. Silent skip (no telemetry — declined consent is not
        // an error condition).
        if (bridge.isExplicitlyOptedOut && bridge.isExplicitlyOptedOut()) {
            return;
        }
        if (!orderInfoUrl) {
            bridge.sendTelemetry('PIXEL_DATA_INCOMPLETE', 2,
                'purchased: orderInfoUrl missing from MC_PIXEL config — '
                + 'AJAX fallback unavailable');
            return;
        }
        if (!orderContext) {
            bridge.sendTelemetry('PIXEL_DATA_INCOMPLETE', 2,
                'purchased: MC_PIXEL_ORDER_CONTEXT missing — '
                + 'setMedia 3-prong didn\'t fire (custom controller?)');
            return;
        }

        // Build query string from whichever context the URL had
        var qs = 'ajax=1';
        if ((orderContext.id_order | 0) > 0) {
            qs += '&id_order=' + (orderContext.id_order | 0);
        } else if ((orderContext.id_cart | 0) > 0 && orderContext.key) {
            // Guest path — encodeURIComponent the key (it's a 32-char
            // hex string normally, but defensive against future shape
            // changes / characters needing escaping)
            qs += '&id_cart=' + (orderContext.id_cart | 0)
                + '&key=' + encodeURIComponent(String(orderContext.key));
        } else {
            bridge.sendTelemetry('PIXEL_DATA_INCOMPLETE', 2,
                'purchased: order context has neither id_order nor '
                + '(id_cart + key) — cannot resolve order via AJAX');
            return;
        }

        if (typeof window.fetch !== 'function') {
            // Very old browsers — gracefully skip
            return;
        }
        var sep = orderInfoUrl.indexOf('?') >= 0 ? '&' : '?';
        try {
            window.fetch(orderInfoUrl + sep + qs, {
                method: 'GET',
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            }).then(function (r) {
                if (!r.ok) return null;
                return r.json();
            }).then(function (data) {
                if (!data || data.ok !== true || !data.order || !data.order.id) {
                    // Endpoint reachable but didn't return a usable
                    // payload — likely auth failure (id_customer mismatch,
                    // bad key, etc.) or order not found. Already covered
                    // by the hook → queue path on stock setups; this is
                    // only the fallback so we don't telemetry-spam.
                    return;
                }
                bridge.fireOnce('PURCHASED', { order: data.order },
                    String(data.order.id), 'order-confirmation',
                    'pixelorderinfo + ajax-fallback');
            }).catch(function () {
                bridge.sendTelemetry('PIXEL_DATA_INCOMPLETE', 2,
                    'purchased: /pixelorderinfo AJAX failed');
            });
        } catch (e) {
            bridge.sendTelemetry('PIXEL_DATA_INCOMPLETE', 2,
                'purchased: /pixelorderinfo throw: '
                + (e && e.message ? e.message : 'unknown'));
        }
    }

    /**
     * On bridge init, check whether Layer 1 (the hook) already fired
     * via the MC_PIXEL_EVENTS_QUEUE push interceptor. If yes, do
     * nothing — fireOnce will have processed it. If no, fall to
     * Layer 2 (AJAX) after a small grace window to let any defer'd
     * scripts run.
     *
     * Detection: check firedPixelEvents (the bridge's dedupe set)
     * for a PURCHASED entry. If absent after the grace period AND
     * we have order context, fire AJAX.
     *
     * Grace window: 800ms. Tuned so:
     *   - Stock PS where the hook fires inline (no delay) → already
     *     in firedPixelEvents before this code runs (queue processed
     *     synchronously in same JS frame) → grace barely matters
     *   - 3rd-party module with deferred render → safely past it
     *   - User doesn't perceive any delay (fire-and-forget AJAX has
     *     no UI consequence — the order-confirmation page is already
     *     rendered + interactive)
     */
    function checkAndFallback() {
        // firedPixelEvents is keyed by `eventType:fingerprint`. We need
        // to check by prefix since the fingerprint is the order.id which
        // we don't know client-side without the hook payload.
        if (bridge.firedPixelEvents) {
            for (var key in bridge.firedPixelEvents) {
                if (Object.prototype.hasOwnProperty.call(bridge.firedPixelEvents, key)
                    && key.indexOf('PURCHASED:') === 0) {
                    // Layer 1 (hook) already fired — done.
                    return;
                }
            }
        }
        // Layer 1 didn't fire → try Layer 2.
        fireFromAjax();
    }

    // Wait the grace window then check.
    setTimeout(checkAndFallback, 800);

    // Expose for tests / debug
    bridge.purchase = {
        fireFromAjax: fireFromAjax,
        checkAndFallback: checkAndFallback,
        orderContext: orderContext
    };
})();
