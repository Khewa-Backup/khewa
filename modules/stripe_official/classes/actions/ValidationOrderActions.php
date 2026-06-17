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

use Stripe\Charge;
use Stripe\Event;
use Stripe\Exception\IdempotencyException;
use Stripe\PaymentIntent;
use Stripe_officialClasslib\Actions\DefaultActions;
use StripeOfficial\Classes\StripeProcessLogger;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ValidationOrderActions extends DefaultActions
{
    const SUPPORTED_PAYMENT_TYPE = [
        'afterpay_clearpay',
        'card',
        'klarna',
        'affirm',
        'link',
    ];

    protected $context;
    /**
     * @var Stripe_official
     */
    protected $module;

    public function preparePaymentFlowActions()
    {
        $this->conveyor->module->setStripeAppInformation();
        try {
            $this->context = $this->conveyor->context;
            $this->module = $this->conveyor->module;

            $intent = PaymentIntent::retrieve($this->conveyor->paymentIntentId);
            if (!$intent) {
                return false;
            }
            StripeProcessLogger::logInfo(StripeProcessLogger::getFormattedMessageLogs('Prepare Payment Flow Actions Beginning ', $this->context->cart->id, $intent->id), 'ValidationOrderActions - preparePaymentFlowActions');

            $charge = isset($intent->charges->data[0]) ? $intent->charges->data[0] : null;
            $chargeId = $charge && isset($charge->id) ? $charge->id : (isset($intent->latest_charge) ? $intent->latest_charge : null);
            $status = $charge && isset($charge->status) ? $charge->status : $intent->status;

            $stripeIdempotencyKey = new StripeIdempotencyKey();
            $stripeIdempotencyKey->getByIdPaymentIntent($intent->id);
            $this->conveyor->setCartId($stripeIdempotencyKey->id_cart);
            $this->conveyor->setCurrencyIso($intent->currency);
            $this->conveyor->setPaymentMethodId($intent->payment_method);
            $this->conveyor->setStatus($status);
            $this->conveyor->setChargeId($chargeId);

            $this->conveyor->setAmount($intent->amount);
            if (!$this->module->isZeroDecimalCurrency($intent->currency)) {
                $this->conveyor->setAmount($intent->amount / 100);
            }

            StripeProcessLogger::logInfo(StripeProcessLogger::getFormattedMessageLogs('Prepare Payment Flow Actions Ending ', $this->context->cart->id, $intent->id), 'ValidationOrderActions - preparePaymentFlowActions');
        } catch (Exception $e) {
            StripeProcessLogger::logError(StripeProcessLogger::getFormattedMessageLogs('Prepare Payment Flow Actions Error => ' . $e->getMessage() . ' - ' . $e->getTraceAsString(), $this->context->cart->id), 'ValidationOrderActions - preparePaymentFlowActions');

            return false;
        }

        return true;
    }

    /*
        Input : 'id_payment_intent', 'status'
        Output : 'paymentIntent'
     */
    public function updatePaymentIntent()
    {
        $this->conveyor->module->setStripeAppInformation();
        StripeProcessLogger::logInfo(StripeProcessLogger::getFormattedMessageLogs('Update Payment Intent Beginning ', $this->conveyor->cartId, $this->conveyor->paymentIntentId), 'ValidationOrderActions - updatePaymentIntent');
        try {
            $amount = $this->conveyor->amount;
            if (!$this->module->isZeroDecimalCurrency($this->conveyor->currencyIso)) {
                $amount = $amount * 100;
            }

            $paymentIntent = new StripePaymentIntent();
            $paymentIntent->findByIdPaymentIntent($this->conveyor->paymentIntentId);
            $paymentIntent->setAmount($amount);
            $paymentIntent->setStatus($this->conveyor->status);
            $paymentIntent->setDateUpd(date('Y-m-d H:i:s'));

            $paymentIntent->save();
            StripeProcessLogger::logInfo(StripeProcessLogger::getFormattedMessageLogs('Update Payment Intent Ending ', $this->conveyor->cartId, $this->conveyor->paymentIntentId), 'ValidationOrderActions - updatePaymentIntent');
        } catch (Exception $e) {
            StripeProcessLogger::logError(StripeProcessLogger::getFormattedMessageLogs('Update Payment Intent Error => ' . $e->getMessage() . ' - ' . $e->getTraceAsString(), $this->conveyor->cartId), 'ValidationOrderActions - updatePaymentIntent');

            return false;
        }

        return true;
    }

    public function updateOrder()
    {
        $this->conveyor->module->setStripeAppInformation();
        if (PaymentIntent::STATUS_SUCCEEDED !== $this->conveyor->status
            && Charge::STATUS_PENDING !== $this->conveyor->status
            && PaymentIntent::STATUS_REQUIRES_CAPTURE !== $this->conveyor->status
            && PaymentIntent::STATUS_REQUIRES_ACTION !== $this->conveyor->status
            && PaymentIntent::STATUS_PROCESSING !== $this->conveyor->status) {
            return false;
        }

        // retrieve payment intent
        $paymentIntent = PaymentIntent::retrieve($this->conveyor->paymentIntentId);

        $stripePaymentMethodService = new StripePaymentMethodService();
        $stripePaymentMethod = $stripePaymentMethodService->getStripePaymentMethod($paymentIntent->payment_method);
        $stripePaymentMethodType = $stripePaymentMethodService->getStripePaymentMethodTypeByPaymentIntent($paymentIntent);
        $stripePaymentMethodBillingDetails = $stripePaymentMethodService->getBillingDetailsFromStripePaymentMethod($stripePaymentMethod);

        $paymentOwnerName = isset($stripePaymentMethodBillingDetails->name) ? $stripePaymentMethodBillingDetails->name : '';
        if (!$paymentOwnerName) {
            $paymentOwnerName = isset($paymentIntent->charges->data[0]->billing_details->name) ? $paymentIntent->charges->data[0]->billing_details->name : '';
        }

        $this->conveyor->setIntentPaymentMethod($stripePaymentMethod);
        $this->conveyor->setIntentPaymentMethodType($stripePaymentMethodType);
        $this->conveyor->setIntentPaymentOwnerName($paymentOwnerName);
        $this->conveyor->setStatus($paymentIntent->status);
        $this->conveyor->setCart(new Cart((int) $paymentIntent->metadata->id_cart));

        $paid = $this->conveyor->amount;

        StripeProcessLogger::logInfo(StripeProcessLogger::getFormattedMessageLogs('Update Order Beginning ', $paymentIntent->metadata->id_cart, $this->conveyor->paymentIntentId), 'ValidationOrderActions - updateOrder');

        $orderStatus = Configuration::get('PS_OS_PAYMENT');
        $this->conveyor->setResult(1);
        /* Add transaction on Prestashop back Office (Order) */
        if (in_array($this->conveyor->intentPaymentMethodType, self::SUPPORTED_PAYMENT_TYPE)
            && Configuration::get(Stripe_official::CATCHANDAUTHORIZE)) {
            $orderStatus = Configuration::get(Stripe_official::CAPTURE_WAITING);
            $this->conveyor->setResult(2);
        } elseif ('sepa_debit' === $this->conveyor->intentPaymentMethodType
            && in_array($this->conveyor->status, [Charge::STATUS_PENDING, PaymentIntent::STATUS_PROCESSING])) {
            $orderStatus = Configuration::get(Stripe_official::SEPA_WAITING);
            $this->conveyor->setResult(3);
        }
        StripeProcessLogger::logInfo(StripeProcessLogger::getFormattedMessageLogs('Update Order Beginning of Validation ', $paymentIntent->metadata->id_cart, $this->conveyor->paymentIntentId), 'ValidationOrderActions - updateOrder');

        try {
            $addressDelivery = new Address($this->conveyor->cart->id_address_delivery);
            $this->context->country = new Country($addressDelivery->id_country);

            StripeProcessLogger::logInfo(StripeProcessLogger::getFormattedMessageLogs('Paid Amount => ' . $paid, $this->conveyor->cartId, $this->conveyor->paymentIntentId), 'ValidationOrderActions - updateOrder');

            $message = 'Stripe Transaction ID: ' . $this->conveyor->paymentIntentId;

            $this->module->validateOrder(
                $this->conveyor->cartId,
                (int) $orderStatus,
                $paid,
                sprintf(
                    $this->module->l('%s via Stripe', 'ValidationOrderActions'),
                    Tools::ucfirst($this->conveyor->intentPaymentMethodType)
                ),
                $message,
                [],
                null,
                false,
                $this->conveyor->cart->secure_key,
                $this->conveyor->context->shop
            );

            $orderId = Order::getIdByCartId((int) $this->conveyor->cartId);
            $order = new Order($orderId);

            StripeProcessLogger::logInfo(StripeProcessLogger::getFormattedMessageLogs('Prestashop Order Created => ' . $order->reference, $this->conveyor->cartId, $this->conveyor->paymentIntentId), 'ValidationOrderActions - updateOrder');

            if ('card' === $paymentIntent->payment_method_types[0]
                && StripePaymentIntentService::CAPTURE_AUTOMATIC !== $paymentIntent->capture_method
                && !Configuration::get(Stripe_official::CATCHANDAUTHORIZE)) {
                $currency = new Currency($order->id_currency, $this->context->language->id, $this->context->shop->id);
                $amount = $this->module->isZeroDecimalCurrency($currency->iso_code) ? $order->getTotalPaid() : $order->getTotalPaid() * 100;

                StripeProcessLogger::logInfo(StripeProcessLogger::getFormattedMessageLogs('Capturing Card and Amount => ' . $amount, $this->conveyor->cartId, $this->conveyor->paymentIntentId), 'ValidationOrderActions - updateOrder');
                $paid = $this->module->isZeroDecimalCurrency($currency->iso_code) ? $paid : $paid * 100;

                if ($amount !== $paid) {
                    StripeProcessLogger::logInfo(StripeProcessLogger::getFormattedMessageLogs('Fix invalid amount "' . $amount . '" to "' . $paid, $this->conveyor->cartId, $this->conveyor->paymentIntentId), 'ValidationOrderActions - updateOrder');
                    $amount = $paid;
                }

                if (!$this->module->captureFunds($amount, $this->conveyor->paymentIntentId)) {
                    return false;
                }
                StripeProcessLogger::logInfo(StripeProcessLogger::getFormattedMessageLogs('Payment Captured For Order => ' . $order->reference, $this->conveyor->cartId, $this->conveyor->paymentIntentId), 'ValidationOrderActions - updateOrder');
            }
            // END capture payment for card if no catch and authorize enabled
            StripeProcessLogger::logInfo(StripeProcessLogger::getFormattedMessageLogs('Update PM Description Beginning => ' . $order->reference, $this->conveyor->cartId, $this->conveyor->paymentIntentId), 'ValidationOrderActions - updateOrder');
            $stripeIdempotencyKey = StripeIdempotencyKey::getOrCreateIdempotencyKey($this->conveyor->cartId);
            $reference = $this->context->shop->name . ' ' . $order->reference;
            $idempotencyKeyString = $stripeIdempotencyKey->idempotency_key . uniqid() . StripePaymentIntent::PAYMENT_INTENT_UPDATE;
            try {
                PaymentIntent::update($this->conveyor->paymentIntentId, ['description' => $reference], ['idempotency_key' => $idempotencyKeyString]);
            } catch (IdempotencyException $e) {
                StripeProcessLogger::logInfo(StripeProcessLogger::getFormattedMessageLogs('Update PM Description Retry => ' . $reference, $this->conveyor->cartId, $this->conveyor->paymentIntentId), 'ValidationOrderActions - updateOrder');
                PaymentIntent::update($this->conveyor->paymentIntentId, ['description' => $reference]);
            }
            StripeProcessLogger::logInfo(StripeProcessLogger::getFormattedMessageLogs('Update PM Description Finish => ' . $reference, $this->conveyor->cartId, $this->conveyor->paymentIntentId), 'ValidationOrderActions - updateOrder');

            if (PaymentIntent::STATUS_REQUIRES_CAPTURE === $this->conveyor->status) {
                $stripeCapture = new StripeCapture();
                $stripeCapture->id_payment_intent = $this->conveyor->paymentIntentId;
                $stripeCapture->id_order = Order::getIdByCartId($this->conveyor->cart->id);
                $stripeCapture->expired = 0;
                $stripeCapture->date_catch = date('Y-m-d H:i:s');
                $stripeCapture->save();
            }

            StripeProcessLogger::logInfo(StripeProcessLogger::getFormattedMessageLogs('Update Order Ending ' . $order->reference, $this->conveyor->cartId, $this->conveyor->paymentIntentId), 'ValidationOrderActions - updateOrder');
        } catch (Exception $e) {
            StripeProcessLogger::logError(StripeProcessLogger::getFormattedMessageLogs('Update Order Error => ' . $e->getMessage() . ' - ' . $e->getTraceAsString(), $this->conveyor->cartId, $this->conveyor->paymentIntentId), 'ValidationOrderActions - updateOrder');

            return false;
        }

        return true;
    }

    /*
        Input : 'id_payment_intent', 'source', 'result'
        Output :
    */
    public function addTentative()
    {
        $this->conveyor->module->setStripeAppInformation();
        try {
            if ('American Express' == $this->conveyor->intentPaymentMethodType) {
                $this->conveyor->intentPaymentMethodType = 'amex';
            } elseif ('Diners Club' == $this->conveyor->intentPaymentMethodType) {
                $this->conveyor->intentPaymentMethodType = 'diners';
            }

            $cardType = $this->conveyor->intentPaymentMethodType;
            if (isset($this->conveyor->intentPaymentMethod->card)) {
                $cardType = $this->conveyor->intentPaymentMethod->card->brand;
            }

            $stripePayment = new StripePayment();
            $stripePayment->setIdStripe($this->conveyor->chargeId);
            $stripePayment->setIdPaymentIntent($this->conveyor->paymentIntentId);
            $stripePayment->setName($this->conveyor->intentPaymentOwnerName);
            $stripePayment->setIdCart((int) $this->conveyor->cartId);
            $stripePayment->setType(Tools::strtolower($cardType));
            $stripePayment->setAmount($this->conveyor->amount);
            $stripePayment->setRefund(0);
            $stripePayment->setCurrency(Tools::strtolower($this->context->currency->iso_code));
            $stripePayment->setResult((int) $this->conveyor->result);
            $stripePayment->setState((int) Configuration::get('STRIPE_MODE'));
            if ($this->conveyor->voucherUrl && $this->conveyor->voucherExpire) {
                $stripePayment->setVoucherUrl($this->conveyor->voucherUrl);
                $stripePayment->setVoucherExpire(date('Y-m-d H:i:s', $this->conveyor->voucherExpire));
            }
            $stripePayment->setDateAdd(date('Y-m-d H:i:s'));
            $stripePayment->save();

            if ('oxxo' !== Tools::strtolower($cardType)) {
                $orderId = Order::getIdByCartId((int) $this->conveyor->cartId);

                $order = new Order($orderId);
                $orderPaymentDatas = OrderPayment::getByOrderReference($order->reference);
                $orderPayment = isset($orderPaymentDatas[0]) ? $orderPaymentDatas[0] : null;
                if (!$orderPayment) {
                    StripeProcessLogger::logError(StripeProcessLogger::getFormattedMessageLogs('OrderPayment is not created due to a PrestaShop issue, please verify order state configuration is loggable (Consider the associated order as validated). We try to create one with charge id ' . $this->conveyor->chargeId . ' on payment.', $this->conveyor->cartId, $this->conveyor->paymentIntentId), 'ValidationOrderActions - addTentative');

                    $order = new Order($orderId);
                    if (!$order->addOrderPayment(
                        $this->conveyor->amount,
                        sprintf(
                            $this->module->l('%s via Stripe', 'ValidationOrderActions'),
                            Tools::ucfirst($this->conveyor->intentPaymentMethodType)
                        ),
                        $this->conveyor->chargeId,
                        $this->context->currency)
                    ) {
                        StripeProcessLogger::logError(StripeProcessLogger::getFormattedMessageLogs('PaymentModule::validateOrder - Cannot save Order Payment' . $order->reference, $this->conveyor->cartId, $this->conveyor->paymentIntentId), 'ValidationOrderActions - addTentative');
                    }

                    return true;
                }
                $orderPayment->transaction_id = $this->conveyor->chargeId;
                $orderPayment->save();
                StripeProcessLogger::logInfo(StripeProcessLogger::getFormattedMessageLogs('Add Tentative Ending ' . $order->reference, $this->conveyor->cartId, $this->conveyor->paymentIntentId), 'ValidationOrderActions - addTentative');
            }
        } catch (Exception $e) {
            StripeProcessLogger::logError(StripeProcessLogger::getFormattedMessageLogs('Add Tentative Error => ', $this->conveyor->cartId, $this->conveyor->paymentIntentId), 'ValidationOrderActions - addTentative');

            return false;
        }

        return true;
    }

    public function chargeWebhook()
    {
        $this->conveyor->module->setStripeAppInformation();
        $this->context = $this->conveyor->context;

        $this->conveyor->paymentIntentId =
            (isset($this->conveyor->eventJson->data->object->payment_intent))
                ? $this->conveyor->eventJson->data->object->payment_intent
                : $this->conveyor->eventJson->data->object->id;
        $id_cart = $this->conveyor->eventJson->data->object->metadata->id_cart;
        $id_order = Order::getIdByCartId($id_cart);
        $event_type = $this->conveyor->eventJson->type;

        StripeProcessLogger::logInfo(StripeProcessLogger::getFormattedMessageLogs('Charge Webhook Beginning For Order => ' . $id_order, $id_cart, $this->conveyor->paymentIntentId), 'ValidationOrderActions - chargeWebhook');

        if (!$id_order) {
            if (in_array($event_type, Stripe_official::$webhook_events)) {
                StripeProcessLogger::logInfo(StripeProcessLogger::getFormattedMessageLogs('Charge Webhook Unknown Order => ' . $event_type, $id_cart, $this->conveyor->paymentIntentId), 'ValidationOrderActions - chargeWebhook');

                http_response_code(200);

                return true;
            } else {
                StripeProcessLogger::logError(StripeProcessLogger::getFormattedMessageLogs('Charge Webhook Unknown Order - $id_order = false ', $this->conveyor->cartId, $this->conveyor->paymentIntentId), 'ValidationOrderActions - chargeWebhook');

                http_response_code(200);

                return false;
            }
        }

        $order = new Order($id_order);
        StripeProcessLogger::logInfo(StripeProcessLogger::getFormattedMessageLogs('Charge Webhook Order OK => ' . $id_order, $id_cart, $this->conveyor->paymentIntentId), 'ValidationOrderActions - chargeWebhook');

        if ('stripe_official' != $order->module) {
            StripeProcessLogger::logInfo(StripeProcessLogger::getFormattedMessageLogs('This order #' . $id_order . ' was made with ' . $order->module . ' not with Stripe => ', $id_cart, $this->conveyor->paymentIntentId), 'ValidationOrderActions - chargeWebhook');

            http_response_code(200);

            return true;
        }

        if (Event::PAYMENT_INTENT_REQUIRES_ACTION !== $event_type
            && $this->conveyor->eventState[$event_type] === $order->getCurrentState()) {
            StripeProcessLogger::logInfo(StripeProcessLogger::getFormattedMessageLogs('Order status is already the good one => ' . $order->getCurrentState(), $id_cart, $this->conveyor->paymentIntentId), 'ValidationOrderActions - chargeWebhook');

            http_response_code(200);

            return true;
        }

        StripeProcessLogger::logInfo(StripeProcessLogger::getFormattedMessageLogs('Charge Webhook - Current charge => ' . $event_type, $id_cart, $this->conveyor->paymentIntentId), 'ValidationOrderActions - chargeWebhook');
        switch ($event_type) {
            case Event::PAYMENT_INTENT_SUCCEEDED:
                if ('klarna' == $this->conveyor->eventJson->data->object->charges->data[0]->payment_method_details->type) {
                    if ($order->getCurrentState() == Configuration::get('STRIPE_CAPTURE_WAITING')) {
                        $order->setCurrentState(Configuration::get('PS_OS_PAYMENT'));
                    }
                }
                break;

            case Event::CHARGE_SUCCEEDED:
                if ($order->getCurrentState() != Configuration::get('PS_OS_OUTOFSTOCK_PAID')) {
                    $order->setCurrentState(Configuration::get('PS_OS_PAYMENT'));

                    if ('oxxo' == $this->conveyor->eventJson->data->object->payment_method_details->type) {
                        $stripePayment = new StripePayment();
                        $stripePayment->getStripePaymentByPaymentIntent($this->conveyor->paymentIntentId);
                        $stripePayment->setIdStripe($this->conveyor->eventJson->data->object->id);
                        $stripePayment->setVoucherValidate(date('Y-m-d H:i:s'));
                        $stripePayment->save();

                        StripeProcessLogger::logInfo(StripeProcessLogger::getFormattedMessageLogs('oxxo charge ID => ' . $this->conveyor->eventJson->data->object->id, $id_cart, $this->conveyor->paymentIntentId), 'ValidationOrderActions - chargeWebhook');
                    }
                }
                break;

            case Event::CHARGE_CAPTURED:
                if ($order->getCurrentState() == Configuration::get('STRIPE_CAPTURE_WAITING')) {
                    $order->setCurrentState(Configuration::get('PS_OS_PAYMENT'));
                }
                break;
            case Event::CHARGE_REFUNDED:
                if ($order->getCurrentState() === Configuration::get('PS_OS_CANCELED')) {
                    break;
                }
                if ($this->conveyor->eventJson->data->object->amount_refunded !== $this->conveyor->eventJson->data->object->amount_captured
                    || $this->conveyor->eventJson->data->object->amount_refunded !== $this->conveyor->eventJson->data->object->amount) {
                    $newOrderState = empty(Configuration::get('PS_CHECKOUT_STATE_PARTIAL_REFUND')) ? Configuration::get('PS_OS_REFUND') : Configuration::get('PS_CHECKOUT_STATE_PARTIAL_REFUND');
                    $order->setCurrentState($newOrderState);
                    StripeProcessLogger::logInfo(StripeProcessLogger::getFormattedMessageLogs('Partial Refund Of Payment => ' . $this->conveyor->eventJson->data->object->id, $id_cart, $this->conveyor->paymentIntentId), 'ValidationOrderActions - chargeWebhook');
                } else {
                    $order->setCurrentState(Configuration::get('PS_OS_REFUND'));
                    StripeProcessLogger::logInfo(StripeProcessLogger::getFormattedMessageLogs('Full Refund Of Payment => ' . $this->conveyor->eventJson->data->object->id, $id_cart, $this->conveyor->paymentIntentId), 'ValidationOrderActions - chargeWebhook');
                }
                break;

            case Event::CHARGE_FAILED:
            case Event::CHARGE_EXPIRED:
                if ($order->getCurrentState() != Configuration::get('PS_OS_PAYMENT')) {
                    $order->setCurrentState(Configuration::get('PS_OS_ERROR'));
                }
                break;

            case Event::CHARGE_DISPUTE_CREATED:
                $order->setCurrentState(Configuration::get(Stripe_official::SEPA_DISPUTE));
                break;

            case Event::PAYMENT_INTENT_CANCELED:
                $order->setCurrentState(Configuration::get('PS_OS_CANCELED'));
                break;

            default:
                return true;
        }

        $currentOrderState = $order->getCurrentOrderState()->name[(int) Configuration::get('PS_LANG_DEFAULT')];

        StripeProcessLogger::logInfo(StripeProcessLogger::getFormattedMessageLogs('Set Order State to ' . $currentOrderState . ' for ' . $event_type, $id_cart, $this->conveyor->paymentIntentId), 'ValidationOrderActions - chargeWebhook');

        return true;
    }
}
