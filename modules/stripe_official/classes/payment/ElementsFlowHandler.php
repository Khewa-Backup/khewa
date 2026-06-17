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

class ElementsFlowHandler implements FlowHandlerInterface
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
     *
     * @throws ApiErrorException
     */
    public function getPaymentElementUrl($separateAuthAndCapture)
    {
        $cartContextModel = CartContextModel::getFromContext($this->context);
        $cartContextModelAnonymized = $this->stripeAnonymize->anonymize($cartContextModel);
        StripeProcessLogger::logInfo('$cartContextModelAnonymized Context: ' . json_encode($cartContextModelAnonymized), 'ElementsFlowHandler', $cartContextModel->cartId);
        if ($cartContextModel->cartId <= 0) {
            StripeProcessLogger::logWarning('Missing cartId in cartContextModel', 'ElementsFlowHandler', $cartContextModel->cartId);

            return Tools::redirect($this->context->link->getPageLink('cart', true));
        }
        $failUrl = $this->context->link->getModuleLink('stripe_official', 'orderFailure', [
            'cartId' => $cartContextModel->cartId,
            'key' => PrestashopCartService::getCartUniqueKey($cartContextModel->cartId),
        ], true);
        $stripePaymentIntent = $this->stripePaymentIntentService->createPaymentIntent($cartContextModel, $separateAuthAndCapture);
        if (!$stripePaymentIntent) {
            StripeProcessLogger::logWarning('Payment Intent does not exists. Context: ' . json_encode($cartContextModelAnonymized), 'ElementsFlowHandler', $cartContextModel->cartId);

            return $failUrl;
        }

        $successUrl = $this->context->link->getModuleLink('stripe_official', 'orderConfirmationReturn', [
            'paymentIntentId' => $stripePaymentIntent->id,
            'cartId' => $cartContextModel->cartId,
            'key' => PrestashopCartService::getCartUniqueKey($cartContextModel->cartId),
        ], true);

        if ($this->confirmationToken == null) {
            StripeProcessLogger::logWarning('The confirmation token is null', 'ElementsFlowHandler', $cartContextModel->cartId);

            return $failUrl;
        }
        $stripePaymentIntent = $this->stripePaymentIntentService->confirmPaymentIntent($stripePaymentIntent, $this->confirmationToken, $successUrl);
        if (!$stripePaymentIntent) {
            StripeProcessLogger::logWarning('Payment Intent does not exists. Context: ' . json_encode($cartContextModelAnonymized), 'ElementsFlowHandler', $cartContextModel->cartId);

            return $failUrl;
        }

        $customer = $stripePaymentIntent->customer ?? '';
        $verificationToken = hash('sha256', $customer . $this->confirmationToken);
        if ($verificationToken !== $this->verificationToken) {
            StripeProcessLogger::logWarning('Verification token was not valid: ' . $verificationToken . ' -- ' . $this->verificationToken, 'ElementsFlowHandler', $cartContextModel->cartId);

            return $failUrl;
        }

        $returnUrl = (new StripePaymentNextActionResolver())->resolveNextAction($stripePaymentIntent, $cartContextModel->cartId, $successUrl, $this->context);

        StripeProcessLogger::logInfo('Return Elements URL: ' . $returnUrl, 'ElementsFlowHandler', $cartContextModel->cartId, $stripePaymentIntent->id);

        return $returnUrl;
    }
}
