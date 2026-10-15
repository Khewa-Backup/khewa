<?php
/**
 * MailChimp
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
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Public FO endpoint that returns the SERVER-BUILT Mailchimp Pixel PURCHASED
 * payload for a given order. Reachable as:
 *
 *   GET /module/mailchimppro/pixelorderinfo?id_order=X
 *   GET /module/mailchimppro/pixelorderinfo?id_cart=Y&key=K   (guest path)
 *
 * Purpose: Layer 2 fallback for the PURCHASED event cascade. The primary
 * trigger is the displayOrderConfirmation PHP hook (handler in
 * mailchimppro.php pushes the payload onto MC_PIXEL_EVENTS_QUEUE via inline
 * <script>). When that hook doesn't fire — typically a 3rd-party checkout
 * module with a custom order-confirmation controller that doesn't extend
 * stock OrderConfirmationController — the bridge falls back to this AJAX.
 *
 * URL parameters carried from setMedia's MC_PIXEL_ORDER_CONTEXT JsDef:
 *   - id_order: logged-in path; PS sets this in the OrderConfirmation URL
 *               when the visitor placed a checkout while authenticated
 *   - id_cart + key: guest path; PS uses secure_key for opaque auth on
 *                    guest order-confirmation URLs (no id_order in URL)
 *
 * Security model — both paths require authorization the visitor can prove:
 *   - Logged-in path: $order->id_customer must match the current customer
 *   - Guest path: provided `key` must match $order->secure_key (the same
 *     mechanism stock OrderConfirmationController uses to gate guest
 *     order display)
 * Without these, anyone could probe id_order ranges to exfiltrate order
 * data. The opacity policy from pixelproductinfo applies — failures
 * return {ok:false} with no detail to defeat enumeration attacks.
 *
 * Defenses on this public endpoint:
 *   1. Strict (int) cast on id_order / id_cart before DB use
 *   2. Auth check (id_customer OR secure_key) before payload build
 *   3. Early-reject anonymous probes (no logged-in customer AND no key)
 *      to close the order-id timing oracle on `new Order(N)`
 *   4. Cache-prevention headers (per-visitor data)
 *
 * Always returns HTTP 200 + JSON. Failure modes are silent by design.
 */
class MailchimpproPixelorderinfoModuleFrontController extends ModuleFrontController
{
    /** @var bool no auth required — guest checkouts need to use the key path */
    public $auth = false;
    /** @var bool */
    public $ajax = true;
    /** @var bool */
    public $ssl = true;

    /**
     * PS dispatch routes here when Controller::isAjax() returns true.
     */
    public function displayAjax()
    {
        $this->respond($this->buildResponse());
    }

    /**
     * PS dispatch routes here when Controller::isAjax() returns false.
     * Same logic as displayAjax — both paths must produce identical output.
     */
    public function display()
    {
        $this->respond($this->buildResponse());
    }

    /**
     * Resolves the PURCHASED payload for the requested order, or returns
     * {ok:false} on any failure (invalid params, auth fail, order load
     * fail, builder threw). Silent on purpose — see class docblock.
     *
     * @return array
     */
    private function buildResponse()
    {
        try {
            // Resolve the Order via either path. id_order wins if provided
            // (logged-in path); otherwise fall back to id_cart + key
            // (guest path). Strict (int) cast on both ids.
            $idOrder = (int) Tools::getValue('id_order');
            $idCart = (int) Tools::getValue('id_cart');
            $providedKey = (string) Tools::getValue('key');

            // Early-reject anonymous probes — closes the timing oracle
            // between "order N exists, auth failed" and "order N doesn't
            // exist" by skipping the `new Order()` DB hit entirely.
            $hasAuthChance = (isset($this->context->customer)
                    && (int) $this->context->customer->id > 0)
                || '' !== $providedKey;
            if (!$hasAuthChance) {
                return ['ok' => false];
            }

            $order = null;
            if ($idOrder > 0) {
                $order = new Order($idOrder);
            } elseif ($idCart > 0) {
                // Cart→Order id lookup. Three different method names across
                // the supported PS versions (verified empirically across the
                // docker stack PS 1.7.0.6 → 9.1):
                //   - PS 1.7.0.6: `Order::getOrderByCartId($id_cart)` →
                //     returns the id_order column value (string|int) or
                //     false. The PS docblock claiming "array Order details"
                //     is misleading — implementation returns the bare id.
                //   - PS 1.7.7 / 8.x / 9.x: `Order::getIdByCartId($id_cart)`
                //     → returns int (this is the canonical method)
                //   - PS 1.7.7 / 8.x: also has `Order::getByCartId` →
                //     returns Order|null (NOT castable to int — would
                //     trigger "Object of class Order could not be converted
                //     to int" warning)
                // Prefer getIdByCartId; fall back to legacy getOrderByCartId.
                // The legacy branch uses call_user_func to hide the method
                // name from PHPStan — getOrderByCartId is no longer in the
                // PS 8/9 API surface so direct calls trip the validator.
                $orderId = 0;
                if (method_exists('Order', 'getIdByCartId')) {
                    $orderId = (int) Order::getIdByCartId($idCart);
                } elseif (method_exists('Order', 'getOrderByCartId')) {
                    $orderId = (int) call_user_func(['Order', 'getOrderByCartId'], $idCart);
                }
                if ($orderId > 0) {
                    $order = new Order($orderId);
                }
            }
            if (!$order || !Validate::isLoadedObject($order)) {
                return ['ok' => false];
            }

            // Auth check — visitor must prove they own this order. Two paths:
            //   1. Logged-in visitor IS the order's customer (id match)
            //   2. Guest visitor provides matching secure_key
            $isAuthed = false;
            if (isset($this->context->customer)
                && (int) $this->context->customer->id > 0
                && (int) $this->context->customer->id === (int) $order->id_customer) {
                $isAuthed = true;
            } elseif ('' !== $providedKey
                && hash_equals((string) $order->secure_key, $providedKey)) {
                // hash_equals — timing-safe comparison (PHP 5.6+, universal
                // on our 7.1.33 floor) prevents secure_key character-by-
                // character oracle attacks.
                $isAuthed = true;
            }
            if (!$isAuthed) {
                return ['ok' => false];
            }

            // Build the payload via the canonical builder in the module
            // class. Same shape the displayOrderConfirmation hook emits.
            // $this->module is set by ModuleFrontController's constructor; we
            // wouldn't be inside the controller if the module weren't loaded.
            /** @var Mailchimppro $module */
            $module = $this->module;
            $payload = $module->buildPixelOrderPayload($order);
            if (empty($payload['id'])) {
                return ['ok' => false];
            }

            return ['ok' => true, 'order' => $payload];
        } catch (Exception $e) {
            return ['ok' => false];
        }
    }

    /**
     * JSON response with cache-prevention headers — order data is per-
     * visitor and per-order; intermediaries must NOT pin one visitor's
     * order data and serve it to others.
     *
     * @param array $body
     */
    private function respond($body)
    {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            header('Expires: 0');
        }
        echo json_encode($body, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
        exit;
    }
}
