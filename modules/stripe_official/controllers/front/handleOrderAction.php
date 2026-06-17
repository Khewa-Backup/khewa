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

class stripe_officialHandleOrderActionModuleFrontController extends ModuleFrontController
{
    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        $confirmationToken = Tools::getValue('confirmationToken') ?: null;
        $verificationToken = Tools::getValue('verifyToken') ?: null;

        $this->handleCartMissingFromContext();

        $cart = $this->context->cart;

        $failUrl = $cart->id ?
            $this->context->link->getModuleLink('stripe_official', 'orderFailure', [
                'cartId' => $cart->id,
                'key' => PrestashopCartService::getCartUniqueKey($cart->id),
            ], true) :
            'index.php?controller=order';
        if (!$cart->id_customer || !$cart->id_address_delivery || !$cart->id_address_invoice || !$this->module->active) {
            StripeProcessLogger::logError('Cart verification failed: ' . json_encode($cart), 'handleOrderAction', $cart->id);
            Tools::redirect($failUrl);
        }

        if ($confirmationToken && !$verificationToken) {
            StripeProcessLogger::logError('Confirmation token or verification token validation failed : ct: ' . $confirmationToken . ' token: ' . $verificationToken, 'handleOrderAction', $cart->id);
            Tools::redirect($failUrl);
        }

        try {
            $separateAuthAndCapture = Configuration::get(Stripe_official::CATCHANDAUTHORIZE);
            $paymentElements = Configuration::get(Stripe_official::ENABLE_PAYMENT_ELEMENTS);
            // ToDo check if this has to be rewritten
            $paymentFlow = $paymentElements ? Stripe_official::PM_PAYMENT_ELEMENTS : Stripe_official::PM_CHECKOUT;

            $paymentHandler = new PaymentHandler($this->context, $this->module);
            StripeProcessLogger::logInfo('Selected handler: ' . get_class($paymentHandler) . 'Using flow: ' . $paymentFlow, 'handleOrderAction', $cart->id);
            $redirectUrl = $paymentHandler->handlePayment($paymentFlow, $separateAuthAndCapture, $confirmationToken, $verificationToken);

            Tools::redirect($redirectUrl);
        } catch (Exception $e) {
            StripeProcessLogger::logError('Stripe Payment Error => ' . $e->getMessage() . ' - ' . $e->getTraceAsString(), 'handleOrderAction', $cart->id);

            Tools::redirect($failUrl);
        }
    }

    private function handleCartMissingFromContext()
    {
        $cart = $this->context->cart;
        if (Validate::isLoadedObject($cart)) {
            return;
        }

        StripeProcessLogger::logWarning('Cart could not be loaded - url called outside of context: ' . Tools::getShopDomain() . $_SERVER['REQUEST_URI'], 'handleOrderAction');

        $prestashopOrderService = new PrestashopOrderService($this->context, $this->module, Stripe_official::getSecretKey());
        $lastCartId = $prestashopOrderService->getCustomerLastCart($this->context->customer->id, null);
        if (!$lastCartId) {
            StripeProcessLogger::logError('No valid last cart found for customer id ' . $this->context->customer->id, 'handleOrderAction');

            return Tools::redirect($this->context->link->getPageLink('history', true));
        }
        $lastCart = new Cart($lastCartId);
        $hasOrder = Order::getByCartId($lastCart->id);
        if (Configuration::get('STRIPE_ENABLE_PAYMENT_ELEMENTS') == 0 && Configuration::get('STRIPE_ORDER_FLOW') == 0 && !is_null($hasOrder)) {
            // Checkout Session flow with order created before redirecting to Stripe, was called before because it has an order
            $checkoutSessionService = new StripeCheckoutSessionService(Stripe_official::getSecretKey());
            try {
                $checkoutSession = $checkoutSessionService->retrieveLastOpenCheckoutSessionForCartId($lastCart->id);
                StripeProcessLogger::logInfo('Redirecting to last open checkout session for cart id ' . $lastCart->id . ' -> ' . $checkoutSession->id, 'handleOrderAction', $lastCart->id);

                return Tools::redirect($checkoutSession->url);
            } catch (Exception $e) {
                StripeProcessLogger::logError('Error retrieving last open checkout session for cart id ' . $lastCart->id . ' -> ' . $e->getMessage(), 'handleOrderAction', $lastCart->id);

                return Tools::redirect($this->context->link->getPageLink('history', true));
            }
        }

        return Tools::redirect($this->context->link->getPageLink('history', true));
    }
}
