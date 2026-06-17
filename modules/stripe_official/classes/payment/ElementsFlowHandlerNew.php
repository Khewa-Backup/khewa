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

use Stripe\Exception\ApiErrorException;
use StripeOfficial\Classes\StripeProcessLogger;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ElementsFlowHandlerNew implements FlowHandlerInterface
{
    /**
     * @var Context
     */
    private $context;
    /**
     * @var Module
     */
    private $module;
    /**
     * @var StripePaymentIntentService
     */
    private $stripePaymentIntentService;
    /**
     * @var string|null
     */
    private $confirmationToken;
    /**
     * @var PrestashopBuildOrderService
     */
    private $prestashopBuildOrderService;
    /**
     * @var PrestashopOrderService
     */
    private $prestashopOrderService;

    /**
     * @var string|null
     */
    private $verificationToken;

    private $stripeAnonymize;

    /**
     * @param Context $context
     * @param Stripe_official $module
     * @param string|null $secretKey
     * @param string|null $confirmationToken
     */
    public function __construct($context, $module, $confirmationToken = null, $verificationToken = null, $secretKey = null)
    {
        $this->context = $context;
        $this->module = $module;
        $secretKey = $secretKey ?: Stripe_official::getSecretKey();
        $this->stripePaymentIntentService = new StripePaymentIntentService($secretKey);
        $this->confirmationToken = $confirmationToken;
        $this->module->setStripeAppInformation();
        $this->prestashopBuildOrderService = new PrestashopBuildOrderService($this->context, $this->module, $secretKey);
        $this->prestashopOrderService = new PrestashopOrderService($this->context, $this->module, $secretKey);
        $this->verificationToken = $verificationToken;
        $this->stripeAnonymize = new StripeAnonymize();
    }

    /**
     * @param bool $separateAuthAndCapture
     *
     * @return string|null
     *
     * @throws ApiErrorException
     */
    public function handlePayment($separateAuthAndCapture)
    {
        return $this->getPaymentElementUrl($separateAuthAndCapture);
    }

    /**
     * @param bool $separateAuthAndCapture
     *
     * @return string|null
     */
    public function getPaymentElementUrl($separateAuthAndCapture)
    {
        $cartContextModel = CartContextModel::getFromContext($this->context);

        $failUrl = $this->context->link->getModuleLink('stripe_official', 'orderFailure', [
            'cartId' => $cartContextModel->cartId,
            'key' => PrestashopCartService::getCartUniqueKey($cartContextModel->cartId),
        ], true);

        $cartId = $cartContextModel->cartId;

        if ($cartId <= 0) {
            StripeProcessLogger::logWarning('Missing cartId in cartContextModel', 'ElementsFlowHandlerNew', $cartContextModel->cartId);

            return Tools::redirect($this->context->link->getPageLink('cart', true));
        }

        $stripePaymentIntent = $this->stripePaymentIntentService->createPaymentIntent($cartContextModel, $separateAuthAndCapture);

        if (!$stripePaymentIntent) {
            $cartContextModelAnonymized = $this->stripeAnonymize->anonymize($cartContextModel);
            StripeProcessLogger::logWarning('Payment Intent does not exists. Context: ' . json_encode($cartContextModelAnonymized), 'ElementsFlowHandlerNew', $cartContextModel->cartId);

            return $failUrl;
        }

        $customer = $stripePaymentIntent->customer ?? '';
        $verificationToken = hash('sha256', $customer . $this->confirmationToken);
        if ($verificationToken !== $this->verificationToken) {
            StripeProcessLogger::logWarning('Verification token was not valid ' . $verificationToken . ' -- ' . $this->verificationToken, 'ElementsFlowHandlerNew', $cartContextModel->cartId);

            return $failUrl;
        }

        $psStripePaymentIntent = new StripePaymentIntent();
        $psStripePaymentIntent->findByIdPaymentIntent($stripePaymentIntent->id);
        $cart = new Cart($cartId);
        if (!Validate::isLoadedObject($cart)) {
            StripeProcessLogger::logWarning('Cart not found', 'ElementsFlowHandlerNew', $cartId);

            return $failUrl;
        }

        $orderModel = $this->prestashopBuildOrderService->buildAndCreatePrestashopOrder($psStripePaymentIntent, $stripePaymentIntent, $cartContextModel, $cart);

        if (!$orderModel->orderReference || !$orderModel->orderId) {
            StripeProcessLogger::logWarning('Order was not created in PrestaShop ' . json_encode($orderModel), 'ElementsFlowHandlerNew', $cartContextModel->cartId);

            return $failUrl;
        }
        if ($this->confirmationToken == null) {
            StripeProcessLogger::logWarning('The confirmation token is null' . json_encode($orderModel), $cartContextModel->cartId);

            return $failUrl;
        }

        $redirectUrl = $this->context->link->getModuleLink('stripe_official', 'orderConfirmationReturn', ['paymentIntentId' => $stripePaymentIntent->id, 'cartId' => $cartId, 'key' => PrestashopCartService::getCartUniqueKey($cartId)], true);
        $confirmedPaymentIntent = $this->stripePaymentIntentService->confirmPaymentIntent($stripePaymentIntent, $this->confirmationToken, $redirectUrl);
        $this->prestashopOrderService->createPsStripePayment($stripePaymentIntent, $orderModel);

        $returnUrl = (new StripePaymentNextActionResolver())->resolveNextAction($confirmedPaymentIntent, $cartId, $redirectUrl, $this->context);

        try {
            $status = null;

            if (!$confirmedPaymentIntent) {
                $confirmedPaymentIntent = $this->stripePaymentIntentService->getStripePaymentIntent($stripePaymentIntent->id);
                $confirmedPaymentIntentAnonymized = $this->stripeAnonymize->anonymize($confirmedPaymentIntent);
                StripeProcessLogger::logInfo('pi elements flow handler ' . json_encode($confirmedPaymentIntentAnonymized), 'ElementsFlowHandlerNew', $cartId, $confirmedPaymentIntent->id);
            }

            $lastPaymentError = $confirmedPaymentIntent->last_payment_error ?? null;

            if ($lastPaymentError) {
                $chargeDeclineCode = $lastPaymentError->decline_code ?? $lastPaymentError->code ?? null;
                $status = $psStripePaymentIntent->getStatusFromStripeDeclineCode($chargeDeclineCode);
                StripeProcessLogger::logInfo('Last Payment Error: ' . json_encode($lastPaymentError), 'ElementsFlowHandlerNew', $cartId, $confirmedPaymentIntent->id);
            }

            $status = $status ?? $psStripePaymentIntent->getStatusFromStripePaymentIntentStatus($confirmedPaymentIntent->status);
            StripeOrderStateSync::sync(
                $confirmedPaymentIntent,
                (int) $cartId,
                $stripePaymentIntent->id,
                new Order($orderModel->orderId),
                $status ?? null
            );

            $this->stripePaymentIntentService->updateStripePaymentIntent($confirmedPaymentIntent, $orderModel->orderReference);
        } catch (Exception $e) {
            StripeProcessLogger::logError('Order Confirmation Error => ' . $e->getMessage() . ' - ' . $e->getTraceAsString(), 'ElementsFlowHandlerNew', $cartId, $confirmedPaymentIntent->id);
        }

        if ($confirmedPaymentIntent->last_payment_error) {
            StripeProcessLogger::logWarning('Last Payment Error From Confirmed Payment: ' . json_encode($confirmedPaymentIntent->last_payment_error), 'ElementsFlowHandlerNew');

            return $failUrl;
        }

        StripeProcessLogger::logInfo('Return URL: ' . $returnUrl, 'ElementsFlowHandlerNew', $cartContextModel->cartId, $stripePaymentIntent->id);

        return $returnUrl;
    }
}
