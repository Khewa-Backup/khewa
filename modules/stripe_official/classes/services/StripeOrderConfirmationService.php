<?php

/**
 * Copyright (c) since 2010 Stripe, Inc. (https://stripe.com)
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Stripe <https://support.stripe.com/contact/email>
 * @copyright Since 2010 Stripe, Inc.
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

use StripeOfficial\Classes\exceptions\StripeOrderAlreadyConfirmedException;
use StripeOfficial\Classes\StripeProcessLogger;

if (!defined('_PS_VERSION_')) {
    exit;
}

class StripeOrderConfirmationService
{
    private $context;
    /**
     * @var StripePaymentIntentService
     */
    private $stripePaymentIntentService;

    /**
     * @var PrestashopOrderService
     */
    private $prestashopOrderService;

    /**
     * @var StripeCheckoutSessionService
     */
    private $stripeCheckoutSessionService;

    public function __construct($context, $module, $secretKey = null)
    {
        $this->context = $context;
        $secretKey = $secretKey ?: Stripe_official::getSecretKey();
        $this->stripePaymentIntentService = new StripePaymentIntentService($secretKey);
        $this->prestashopOrderService = new PrestashopOrderService($this->context, $module, $secretKey);
        $this->stripeCheckoutSessionService = new StripeCheckoutSessionService($secretKey);
    }

    public function orderConfirmationNew(?int $cartId = null)
    {
        $failUrl = $redirectUrl = $this->context->link->getModuleLink('stripe_official', 'orderFailure', [
            'cartId' => $cartId,
            'key' => PrestashopCartService::getCartUniqueKey($cartId),
        ], true);
        $intent = null;
        StripeProcessLogger::logInfo('Order Confirmation Return init: ', 'StripeOrderConfirmationService', $cartId);
        try {
            if (!$cartId) {
                StripeProcessLogger::logWarning('Empty $cartId', 'StripeOrderConfirmationService', $cartId);

                return $failUrl;
            }
            $this->prestashopOrderService->removeProductsFromDuplicateCart(new Cart($cartId));

            $stripeIdempotencyKey = StripeIdempotencyKey::getOrCreateIdempotencyKey($cartId);
            if (!$stripeIdempotencyKey->id_payment_intent) {
                StripeProcessLogger::logWarning('Empty $stripeIdempotencyKey', 'StripeOrderConfirmationService', $cartId);

                return $failUrl;
            }
            $intent = $this->prestashopOrderService->findStripePaymentIntent($stripeIdempotencyKey->id_payment_intent);
            if (!$intent) {
                StripeProcessLogger::logWarning('Empty $intent: ' . $stripeIdempotencyKey->id_payment_intent, 'StripeOrderConfirmationService', $cartId);

                return $failUrl;
            }

            $psStripePaymentIntent = new StripePaymentIntent();
            $psStripePaymentIntent->findByIdPaymentIntent($stripeIdempotencyKey->id_payment_intent);
            if (empty($psStripePaymentIntent->id_payment_intent)) {
                // If it is empty, then checkout session id is in DB, but idempotency key is already payment intent
                // Because here the idempotency key was not refreshed, it mens the webhook already updated it, but did not reach to the point to update the payment intent too
                // We wait 1 second, until webhook updates the payment intent table too, then retry
                sleep(1);
                $psStripePaymentIntent->findByIdPaymentIntent($stripeIdempotencyKey->id_payment_intent);
            }

            if (empty($psStripePaymentIntent->id_payment_intent) && Configuration::get('STRIPE_ENABLE_PAYMENT_ELEMENTS') == 0) {
                $session = $this->stripeCheckoutSessionService->getStripeCheckoutSessionByPaymentIntentId($intent->id);
                $psStripePaymentIntent->findByIdPaymentIntent($session->id);
            }

            // When a checkout session is used the webhook may change the stripe_payment_intent
            // record's id_payment_intent from cs_... to pi_... before this confirmation path
            // runs.  If all lookups by checkout-session ID failed, fall back to the actual
            // PaymentIntent ID so the PI record is always found.
            if (empty($psStripePaymentIntent->id_payment_intent) && isset($intent->id) && $intent->id !== $stripeIdempotencyKey->id_payment_intent) {
                StripeProcessLogger::debugLog('orderConfirmationNew - PI not found by cs id, retrying by intent id: ' . $intent->id, $cartId);
                $psStripePaymentIntent->findByIdPaymentIntent($intent->id);
            }

            // Fail-fast: if the payment has an error (e.g. declined, expired), resolve the
            // decline status and bail out to the failure URL — identical to the legacy flow.
            $status = null;
            $lastPaymentError = $intent->last_payment_error ?? null;
            if ($lastPaymentError) {
                $chargeDeclineCode = $lastPaymentError->decline_code ?? $lastPaymentError->code ?? null;
                $status = $psStripePaymentIntent->getStatusFromStripeDeclineCode($chargeDeclineCode);
                StripeProcessLogger::logInfo('Last Payment Error: ' . json_encode($lastPaymentError), 'StripeOrderConfirmationService', $cartId, $intent->id);
            }
            $status = $status ?? $psStripePaymentIntent->getStatusFromStripePaymentIntentStatus($intent->status);
            StripeProcessLogger::debugLog('orderConfirmationNew - status after checking last payment error: ' . $status, $cartId);

            if ($intent->status == Stripe\PaymentIntent::STATUS_REQUIRES_PAYMENT_METHOD) {
                StripeProcessLogger::logWarning('Payment status not confirmed: ' . $intent->status, 'StripeOrderConfirmationService', $cartId, $intent->id);

                return $failUrl;
            }

            StripeOrderStateSync::sync(
                $intent,
                $cartId,
                $psStripePaymentIntent->id_payment_intent,
                null,
                $status
            );

            $orderId = Order::getIdByCartId($cartId);
            $order = new Order($orderId);
            if (!Validate::isLoadedObject($order)) {
                StripeProcessLogger::logWarning('orderConfirmationNew - order not found for cart', 'StripeOrderConfirmationService', $cartId);

                return $failUrl;
            }

            $orderStatus = $psStripePaymentIntent->getPsStatus();
            $orderReference = $this->context->shop->name . ' / Reference: ' . $order->reference . ' / Order: ' . $orderId;
            $this->stripePaymentIntentService->updateStripePaymentIntent($intent, $orderReference);

            $chargeId = $this->prestashopOrderService->getChargeIdFromIntent($intent);

            $this->prestashopOrderService->updateTransactionIdForPSOrder($cartId, $intent, $chargeId);
            $this->prestashopOrderService->updatePsStripePayment($intent, $cartId);
            $this->prestashopOrderService->saveStripeCapture($orderId, $intent, $orderStatus);
            $this->prestashopOrderService->updatePsOrders($intent, $orderId);
            $redirectUrl = $this->prestashopOrderService->getOrderConfirmationLink($order) ?? $failUrl;

            StripeProcessLogger::logInfo('Order Confirmation Return URL: ' . json_encode($redirectUrl), 'StripeOrderConfirmationService', $cartId);
        } catch (Exception $e) {
            StripeProcessLogger::logError('Order Confirmation Error => ' . $e->getMessage() . ' - ' . $e->getTraceAsString(), 'StripeOrderConfirmationService', $cartId, $intent ? $intent->id : '');
        }

        return $redirectUrl;
    }

    public function orderConfirmationLegacy(?int $cartId = null)
    {
        $failUrl = $redirectUrl = $this->context->link->getModuleLink('stripe_official', 'orderFailure', [
            'cartId' => $cartId,
            'key' => PrestashopCartService::getCartUniqueKey($cartId),
        ], true);

        $intent = null;
        try {
            if (!$cartId) {
                StripeProcessLogger::logWarning('Empty $cartId', 'StripeOrderConfirmationService', $cartId);

                return $failUrl;
            }

            StripeProcessLogger::logInfo('Order Confirmation Legacy start ', 'StripeOrderConfirmationService', $cartId);
            $stripeIdempotencyKey = StripeIdempotencyKey::getOrCreateIdempotencyKey($cartId);
            if (!$stripeIdempotencyKey->id_payment_intent) {
                StripeProcessLogger::logWarning('Empty $stripeIdempotencyKey', 'StripeOrderConfirmationService', $cartId);

                return $failUrl;
            }
            $intent = $this->prestashopOrderService->findStripePaymentIntent($stripeIdempotencyKey->id_payment_intent);
            if (!$intent) {
                StripeProcessLogger::logWarning('Empty $intent: ' . $stripeIdempotencyKey->id_payment_intent, 'StripeOrderConfirmationService', $cartId);

                return $failUrl;
            }

            $psStripePaymentIntent = new StripePaymentIntent();
            $psStripePaymentIntent->findByIdPaymentIntent($stripeIdempotencyKey->id_payment_intent);
            if (empty($psStripePaymentIntent->id_payment_intent)) {
                sleep(1);
                $psStripePaymentIntent->findByIdPaymentIntent($stripeIdempotencyKey->id_payment_intent);
            }

            // Same checkout-session-ID-to-PI-ID race as in orderConfirmationNew: if the
            // webhook already updated the record to pi_..., fall back to the actual intent id.
            if (empty($psStripePaymentIntent->id_payment_intent) && isset($intent->id) && $intent->id !== $stripeIdempotencyKey->id_payment_intent) {
                StripeProcessLogger::debugLog('orderConfirmationLegacy - PI not found by cs id, retrying by intent id: ' . $intent->id, $cartId);
                $psStripePaymentIntent->findByIdPaymentIntent($intent->id);
            }

            $status = null;
            $lastPaymentError = $intent->last_payment_error ?? null;
            if ($lastPaymentError) {
                $chargeDeclineCode = $lastPaymentError->decline_code ?? $lastPaymentError->code ?? null;
                $status = $psStripePaymentIntent->getStatusFromStripeDeclineCode($chargeDeclineCode);
                StripeProcessLogger::logInfo('Last Payment Error: ' . json_encode($lastPaymentError), 'StripeOrderConfirmationService', $cartId, $intent->id);
            }
            $status = $status ?? $psStripePaymentIntent->getStatusFromStripePaymentIntentStatus($intent->status);
            StripeProcessLogger::debugLog('orderConfirmationLegacy - status after checking last payment error: ' . $status, $cartId);

            if ($intent->status == Stripe\PaymentIntent::STATUS_REQUIRES_PAYMENT_METHOD) {
                StripeProcessLogger::logWarning('Payment status not confirmed: ' . $intent->status, 'StripeOrderConfirmationService', $cartId, $intent->id);

                return $failUrl;
            }

            StripeOrderStateSync::sync(
                $intent,
                $cartId,
                $stripeIdempotencyKey->id_payment_intent,
                null,
                $status
            );
            $psStripePaymentIntent = new StripePaymentIntent($psStripePaymentIntent->id); // Refresh after sync

            $stripeIdempotencyKeyObject = new StripeIdempotencyKey();
            $stripeIdempotencyKeyObject->updateIdempotencyKey($cartId, $intent);
            $psCart = new Cart($cartId);

            $orderModel = $this->prestashopOrderService->buildOrderModel($psStripePaymentIntent, $intent, $psCart);

            $chargeId = $this->prestashopOrderService->getChargeIdFromIntent($intent);

            // Try to hydrate an order created by webhook
            if (!$orderModel->order) {
                $orderModel->order = $this->prestashopOrderService->findOrderByIntentOrCharge($intent->id, $chargeId ?? null);
                $orderModel = $this->hydrateOrderModelWithOrder($orderModel, $orderModel->order);
            }

            // 2) If still nothing, create it ONCE under a lock, with a re-check inside the lock
            if (!$orderModel->order) {
                $this->acquirePiLock($intent->id, 5); // MySQL advisory lock, 5s timeout
                try {
                    // re-check inside lock (webhook might have just finished)
                    $orderModel->order = $this->prestashopOrderService->findOrderByIntentOrCharge($intent->id, $chargeId ?? null);
                    $orderModel = $this->hydrateOrderModelWithOrder($orderModel, $orderModel->order);

                    if (!$orderModel->order) {
                        $orderModel = $this->prestashopOrderService->createPsOrder($orderModel);
                        StripeProcessLogger::debugLog('createPsOrder executing order creation inside the lock');
                    }
                } finally {
                    $this->releasePiLock($intent->id);
                }
            }

            // Final fallback: if another request/path created the order but orderModel is still empty
            $orderId = Order::getIdByCartId($cartId);
            if (!$orderModel->order && $orderId) {
                $orderModel = $this->hydrateOrderModelWithOrder($orderModel, new Order($orderId));
            }

            StripeProcessLogger::logInfo('PrestaShop Order before redirect to URL: ' . json_encode($orderModel->order), 'StripeOrderConfirmationService', $cartId, $intent->id);

            $orderId = Order::getIdByCartId($cartId);
            $this->prestashopOrderService->updateTransactionIdForPSOrder($cartId, $intent, $chargeId);
            $this->stripePaymentIntentService->updateStripePaymentIntent($intent, $orderModel->orderReference);
            $this->prestashopOrderService->saveStripeCapture($orderId, $intent, $orderModel->status);
            $this->prestashopOrderService->updatePsOrders($intent, $orderId);
            $this->prestashopOrderService->createPsStripePayment($intent, $orderModel);

            StripeProcessLogger::debugLog('orderConfirmationLegacy - status after creating order: ' . $orderModel->status, $cartId);

            $redirectUrl = $orderModel->order ? $this->prestashopOrderService->getOrderConfirmationLink($orderModel->order) : $failUrl;

            StripeProcessLogger::logInfo('Order Confirmation Return Legacy URL: ' . json_encode($redirectUrl), 'StripeOrderConfirmationService', $cartId);
        } catch (Exception $e) {
            StripeProcessLogger::logError('Order Confirmation Error => ' . $e->getMessage() . ' - ' . $e->getTraceAsString(), 'StripeOrderConfirmationService', $cartId, $intent ? $intent->id : '');
        }

        return $redirectUrl;
    }

    public function checkOrderAlreadyConfirmed(?int $cartId = null)
    {
        $failUrl = $this->context->link->getModuleLink('stripe_official', 'orderFailure', [
            'cartId' => $cartId,
            'key' => PrestashopCartService::getCartUniqueKey($cartId),
        ], true);
        if (!$cartId) {
            StripeProcessLogger::logWarning('Empty $cartId in checkOrderAlreadyConfirmed', 'StripeOrderConfirmationService', $cartId);
            throw new StripeOrderAlreadyConfirmedException($failUrl);
        }
        $payment_id = StripePayment::getIdByCartId($cartId);
        if ($payment_id) {
            $payment = new StripePayment($payment_id);
            if (!empty($payment->id_stripe) && !empty($payment->id_payment_intent)) {
                $orderId = Order::getIdByCartId($cartId);
                $order = new Order($orderId);
                if (!Validate::isLoadedObject($order)) {
                    StripeProcessLogger::logWarning('checkOrderAlreadyConfirmed - order not found for cart', 'StripeOrderConfirmationService', $cartId);

                    return;
                }

                $returnUrl = $this->prestashopOrderService->getOrderConfirmationLink($order) ?: $failUrl;
                StripeProcessLogger::logWarning('Order already confirmed OrderId: ' . $orderId, 'StripeOrderConfirmationService', $cartId);
                throw new StripeOrderAlreadyConfirmedException($returnUrl);
            }
        }
    }

    private function hydrateOrderModelWithOrder(OrderModel $orderModel, ?Order $order = null): OrderModel
    {
        if (!$order || !Validate::isLoadedObject($order)) {
            return $orderModel;
        }

        $orderModel->order = $order;
        $orderModel->orderId = (int) $order->id;
        $orderModel->orderReference = $this->context->shop->name . ' / Reference: ' . $order->reference . ' / Order: ' . (int) $order->id;

        return $orderModel;
    }

    private function acquirePiLock($paymentIntentId, $timeoutSeconds = 5)
    {
        $key = 'stripe_pi_' . pSQL($paymentIntentId);
        Db::getInstance()->execute('SELECT GET_LOCK("' . $key . '", ' . (int) $timeoutSeconds . ')');
    }

    private function releasePiLock($paymentIntentId)
    {
        $key = 'stripe_pi_' . pSQL($paymentIntentId);
        Db::getInstance()->execute('SELECT RELEASE_LOCK("' . $key . '")');
    }
}
