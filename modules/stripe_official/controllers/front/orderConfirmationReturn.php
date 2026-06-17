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

class stripe_officialOrderConfirmationReturnModuleFrontController extends ModuleFrontController
{
    /**
     * @var StripeOrderConfirmationService
     */
    private $stripeOrderConfirmationService;
    /**
     * @var StripePaymentIntentService
     */
    private $stripePaymentIntentService;
    /**
     * @var StripeCheckoutSessionService
     */
    private $stripeCheckoutSessionService;

    public function __construct($secretKey = null)
    {
        parent::__construct();
        $this->ssl = true;
        $this->ajax = true;
        $this->json = true;
        $secretKey = $secretKey ?: Stripe_official::getSecretKey();
        $this->stripeOrderConfirmationService = new StripeOrderConfirmationService($this->context, $this->module, $secretKey);
        $this->stripePaymentIntentService = new StripePaymentIntentService($secretKey);
        $this->stripeCheckoutSessionService = new StripeCheckoutSessionService($secretKey);
    }

    /**
     * @throws Exception
     *
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();

        $cartIdFromUrl = (int) Tools::getValue('cartId');
        $paymentIntentId = (string) Tools::getValue('payment_intent');
        $paymentIntent = (string) Tools::getValue('paymentIntentId');
        if (empty($paymentIntentId) && !empty($paymentIntent)) {
            $paymentIntentId = $paymentIntent;
        }
        $sessionId = (string) Tools::getValue('session_id');
        StripeProcessLogger::logInfo('Stripe session ' . $sessionId, 'orderConfirmationReturn', $cartIdFromUrl);
        StripeProcessLogger::logInfo('Stripe  payment_intent ' . $paymentIntentId, 'orderConfirmationReturn', $cartIdFromUrl);
        $cartId = 0;
        try {
            if (!empty($sessionId)) {
                $session = $this->stripeCheckoutSessionService->getStripeCheckoutSession($sessionId);

                if (empty($session) || empty($session->payment_intent)) {
                    StripeProcessLogger::logInfo('Stripe session has no payment_intent', 'orderConfirmationReturn', $cartIdFromUrl);

                    return Tools::redirect($this->context->link->getPageLink('cart', true));
                }

                if (is_string($session->payment_intent)) {
                    $paymentIntentId = $session->payment_intent;
                }

                $cartId = isset($session->metadata['id_cart']) ? (int) $session->metadata['id_cart'] : 0;

                if ($cartId <= 0) {
                    StripeProcessLogger::logInfo('Missing ps_cart_id in PI metadata from session', 'orderConfirmationReturn', $cartIdFromUrl);

                    return Tools::redirect($this->context->link->getPageLink('cart', true));
                }
            }

            if ($cartId <= 0 && !empty($paymentIntentId)) {
                $cartId = $this->stripePaymentIntentService->getCartIdByPaymentIntentId($paymentIntentId);
                StripeProcessLogger::logInfo('getCartIdByPaymentIntentId: ' . $cartId, 'orderConfirmationReturn', $cartIdFromUrl);
                if ($cartId <= 0) {
                    StripeProcessLogger::logInfo('Missing ps_cart_id in PI metadata', 'orderConfirmationReturn', $cartIdFromUrl);

                    return Tools::redirect($this->context->link->getPageLink('cart', true));
                }
            }
        } catch (Exception $e) {
            StripeProcessLogger::logInfo('Stripe retrieve failed: ' . $e->getMessage(), 'orderConfirmationReturn', $cartIdFromUrl);

            return Tools::redirect($this->context->link->getPageLink('cart', true));
        }

        if ($cartId <= 0) {
            StripeProcessLogger::logInfo('Missing session_id/payment_intent on return URL', 'orderConfirmationReturn', $cartIdFromUrl);

            return Tools::redirect($this->context->link->getPageLink('cart', true));
        }

        // Verify cart exists and key is valid
        $cart = new Cart($cartId);
        if (!Validate::isLoadedObject($cart) || !PrestashopCartService::validateCartUniqueKey($cartId, Tools::getValue('key'))) {
            StripeProcessLogger::logError('Order Confirmation Return: Cart not found with id ' . $cartId . ' or cart unique key is not matching.', 'orderConfirmationReturn', $cartId);

            return Tools::redirect(
                $this->context->link->getPageLink('history', true)
            );
        }

        try {
            $this->stripeOrderConfirmationService->checkOrderAlreadyConfirmed($cartId);
        } catch (StripeOrderAlreadyConfirmedException $e) {
            return Tools::redirect($e->getReturnUrl());
        }

        $newOrderFlow = !(int) Configuration::get(Stripe_official::ORDER_FLOW);

        $redirectUrl = $newOrderFlow ?
            $this->stripeOrderConfirmationService->orderConfirmationNew($cartId) :
            $this->stripeOrderConfirmationService->orderConfirmationLegacy($cartId)
        ;

        Tools::redirect($redirectUrl);
    }
}
