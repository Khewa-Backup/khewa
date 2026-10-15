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

namespace StripeOfficial\Classes\factories;

use Stripe\Checkout\Session;
use Stripe\PaymentIntent;
use Stripe\Stripe;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
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
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
class AdminOrderContentParamsFactory
{
    /**
     * @var \Order
     */
    protected $order;

    public function __construct(\Order $order)
    {
        $this->order = $order;
    }

    protected function validateOrder()
    {
        if (!\Validate::isLoadedObject($this->order)) {
            throw new \Exception('Order is not valid');
        }
        if ($this->order->module !== 'stripe_official') {
            throw new \Exception('Order is not a Stripe order');
        }
    }

    protected function retrieveStripePaymentIntent($paymentIntentId)
    {
        if (strpos($paymentIntentId, 'cs_') === 0) {
            $checkoutSession = Session::retrieve($paymentIntentId);
            $paymentIntentId = $checkoutSession->payment_intent;
        }
        if (empty($paymentIntentId)) {
            throw new \Exception('Payment intent not found');
        }

        return PaymentIntent::retrieve(
            $paymentIntentId
        );
    }

    public function getParams()
    {
        $this->validateOrder();

        Stripe::setApiKey(\Stripe_official::getSecretKey($this->order->id_shop_group, $this->order->id_shop));

        $stripePayment = new \StripePayment();
        $stripePayment->getStripePaymentByCart($this->order->id_cart);
        if (!\Validate::isLoadedObject($stripePayment)) {
            throw new \Exception('Stripe Payment not found for this order');
        }
        $paymentIntentId = $stripePayment->getIdPaymentIntent();

        $stripeCapture = new \StripeCapture();
        $stripeCapture->getByIdPaymentIntent($paymentIntentId);

        $dispute = false;
        if (!empty($stripePayment->getIdStripe())) {
            $stripeDispute = new \StripeDispute();
            $dispute = $stripeDispute->orderHasDispute($stripePayment->getIdStripe(), $this->order->id_shop);
        }

        $riskLevel = $riskScore = $stripeCustomerID = $paymentMethod = $intent = null;
        if ($paymentIntentId) {
            $intent = $this->retrieveStripePaymentIntent($paymentIntentId);
        }

        if ($intent && isset($intent->charges->data[0])) {
            $riskLevel = $intent->charges->data[0]->outcome->risk_level;
            $riskScore = $intent->charges->data[0]->outcome->risk_score;
            $stripeCustomerID = $intent->charges->data[0]->customer;
            $paymentMethod = $intent->charges->data[0]->payment_method_details->type;
        }

        $stripePartialRefunded = false;
        $stripeRefundedAmount = 0;
        $currency = new \Currency($this->order->id_currency);
        $orderCurrencyIsoCode = $currency->iso_code ?? null;
        if ((int) $this->order->current_state === (int) \Configuration::get(\Stripe_official::PARTIAL_REFUND)) {
            $stripePartialRefunded = true;
            $stripeRefundedAmount = $orderCurrencyIsoCode ? $stripePayment->getRefund() . ' ' . $orderCurrencyIsoCode : $stripePayment->getRefund();
        }

        return [
            'stripe_charge' => $stripePayment->getIdStripe(),
            'stripe_paymentIntent' => $paymentIntentId,
            'stripe_dashboardUrl' => $stripePayment->getDashboardUrl(),
            'stripe_paymentType' => $stripePayment->getType(),
            'stripe_paymentMethod' => $paymentMethod,
            'stripe_dateCatch' => $stripeCapture->getDateCatch(),
            'stripe_dateAuthorize' => $stripeCapture->getDateAuthorize(),
            'stripe_expired' => $stripeCapture->getExpired(),
            'stripe_dispute' => $dispute,
            'stripe_voucher_expire' => $stripePayment->getVoucherExpire(),
            'stripe_voucher_validate' => $stripePayment->getVoucherValidate(),
            'stripe_riskLevel' => $riskLevel,
            'stripe_riskScore' => $riskScore,
            'stripe_customerID' => $stripeCustomerID,
            'stripe_partialRefunded' => $stripePartialRefunded,
            'stripe_refundedAmount' => $stripeRefundedAmount,
        ];
    }
}
