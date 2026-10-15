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

use StripeOfficial\Classes\StripeProcessLogger;

if (!defined('_PS_VERSION_')) {
    exit;
}

class stripe_officialRetryPaymentModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $orderId = (int) Tools::getValue('id_order');
        $token = (string) Tools::getValue('retry_token');
        $service = new StripePaymentRetryService();

        try {
            if (!$orderId || $token === '') {
                throw new Exception('Missing order ID or retry token');
            }
            $order = new Order($orderId);
            if (!Validate::isLoadedObject($order)) {
                throw new Exception('Order ' . $orderId . ' not found');
            }
            if ($order->module !== $this->module->name) {
                throw new Exception('Order ' . $orderId . ' does not belong to this module (got: ' . $order->module . ')');
            }
            $currentCustomer = $this->context->customer;
            $oldCart = new Cart((int) $order->id_cart);
            $guestId = (int) ($this->context->cookie->id_guest ?? 0);
            if (!$service->validateGuestContext($order, $oldCart, $currentCustomer, $guestId)) {
                throw new Exception('Guest context mismatch for order ' . $orderId . ' [is_guest=' . (int) $currentCustomer->is_guest . ' customer_id=' . (int) $currentCustomer->id . ' order_customer=' . (int) $order->id_customer . ' cart_guest=' . (int) $oldCart->id_guest . ' cookie_guest=' . $guestId . ']');
            }
            $payment = new StripePayment();
            $payment->getStripePaymentByCart((int) $order->id_cart);
            $paymentContextId = (string) $payment->id_payment_intent;
            $intent = new StripePaymentIntent();
            $intent->findByIdPaymentIntent($paymentContextId);
            if (!Validate::isLoadedObject($intent)) {
                throw new Exception('Payment intent not found for payment_intent_id=' . $paymentContextId . ' cart=' . (int) $order->id_cart);
            }
            if (!$service->validateToken($order, $paymentContextId, $token)) {
                throw new Exception('Token mismatch for order ' . $orderId);
            }
            $currentState = (int) $order->getCurrentState();
            $errorState = (int) Configuration::get('PS_OS_ERROR');
            if ($currentState !== $errorState) {
                throw new Exception('Order ' . $orderId . ' state is ' . $currentState . ', expected error state ' . $errorState);
            }
            if (!in_array((string) $intent->status, [
                StripePaymentIntent::STATUS_FAIL,
                StripePaymentIntent::STATUS_UNCONFIRMED,
                StripePaymentIntent::STATUS_IN_PROGRESS,
                StripePaymentIntent::STATUS_CANCEL,
            ], true)) {
                throw new Exception('Intent status "' . $intent->status . '" is not retryable for order ' . $orderId);
            }

            $service->restoreCart($order);
            Tools::redirect($this->context->link->getPageLink('order', true));
        } catch (Exception $exception) {
            StripeProcessLogger::logWarning(
                'Payment retry rejected for order ' . $orderId . ': ' . $exception->getMessage(),
                'RetryPaymentController',
                $orderId
            );
            Tools::redirect($this->context->link->getPageLink('history', true));
        }
    }
}
