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

use Stripe\PaymentIntent;
use Stripe_officialClasslib\Actions\ActionsHandler;
use StripeOfficial\Classes\StripeProcessLogger;

if (!defined('_PS_VERSION_')) {
    exit;
}

class stripe_officialOrderSuccessModuleFrontController extends ModuleFrontController
{
    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();

        $intent = $this->retrievePaymentIntent();

        if ($this->checkAndSaveStripeEvent($intent)) {
            $this->handleWebhookActions($intent);
        }

        $this->displayOrderConfirmation($intent);
    }

    private function retrievePaymentIntent()
    {
        $intent = $payment_intent = null;
        try {
            $payment_intent = Tools::getValue('payment_intent');
            $intent = PaymentIntent::retrieve($payment_intent);
        } catch (Exception $e) {
            StripeProcessLogger::logError(StripeProcessLogger::getFormattedMessageLogs('Retrieve Payment Intent Error => ' . $e->getMessage() . ' - ' . $e->getTraceAsString(), null, $payment_intent), 'orderSuccess - retrievePaymentIntent');
        }
        StripeProcessLogger::logInfo(StripeProcessLogger::getFormattedMessageLogs('Retrieve Payment Intent Ending => ' . json_encode($intent), $intent->metadata->id_cart, $payment_intent), 'orderSuccess - retrievePaymentIntent');

        return $intent;
    }

    private function checkEventStatus($paymentIntent)
    {
        if (!$paymentIntent) {
            return false;
        }

        $eventCharge = isset($paymentIntent->charges->data[0]) ? $paymentIntent->charges->data[0] : $paymentIntent;
        $chargeStatus = isset($eventCharge->status) ? $eventCharge->status : null;

        $stripeEventStatus = StripeEvent::getStatusAssociatedToChargeType($chargeStatus);
        StripeProcessLogger::logInfo(StripeProcessLogger::getFormattedMessageLogs('Check Event Status => ' . json_encode($stripeEventStatus), $paymentIntent->metadata->id_cart, $paymentIntent->id), 'orderSuccess - checkEventStatus');
        if (!$stripeEventStatus) {
            StripeProcessLogger::logInfo(StripeProcessLogger::getFormattedMessageLogs('Charge event does not need to be processed => ' . $eventCharge->status, $paymentIntent->metadata->id_cart, $paymentIntent->id), 'orderSuccess - checkEventStatus');

            return false;
        }

        $lastRegisteredEvent = new StripeEvent();
        $lastRegisteredEvent = $lastRegisteredEvent->getLastRegisteredEventByPaymentIntent($paymentIntent->id);

        StripeProcessLogger::logInfo(StripeProcessLogger::getFormattedMessageLogs('Last registered event => ID: ' . $lastRegisteredEvent->id, $paymentIntent->metadata->id_cart, $paymentIntent->id), 'orderSuccess - checkEventStatus');

        if (!StripeEvent::validateTransitionStatus($lastRegisteredEvent->status, $stripeEventStatus) && StripeEvent::REFUNDED_STATUS !== $stripeEventStatus) {
            StripeProcessLogger::logInfo(StripeProcessLogger::getFormattedMessageLogs(
                'This Stripe module event "' . $stripeEventStatus . '" cannot be processed because [Last event status: ' . $lastRegisteredEvent->status . ' | Processed : ' . ($lastRegisteredEvent->isProcessed() ? 'Yes' : 'No') . '].',
                $paymentIntent->metadata->id_cart,
                $paymentIntent->id),
                'orderSuccess - checkEventStatus'
            );

            return false;
        }

        return $stripeEventStatus;
    }

    /**
     * @throws Exception
     */
    private function checkAndSaveStripeEvent($paymentIntent)
    {
        if (!$paymentIntent) {
            return false;
        }

        $eventCharge = isset($paymentIntent->charges->data[0]) ? $paymentIntent->charges->data[0] : $paymentIntent;

        $stripeEventStatus = $this->checkEventStatus($paymentIntent);

        if (!$stripeEventStatus) {
            return false;
        }
        StripeProcessLogger::logInfo(StripeProcessLogger::getFormattedMessageLogs('Check And Save Stripe Event => ' . json_encode($stripeEventStatus), $paymentIntent->metadata->id_cart, $paymentIntent->id), 'orderSuccess - checkAndSaveStripeEvent');

        return StripeEvent::registerStripeEvent($paymentIntent, $eventCharge, $stripeEventStatus);
    }

    private function handleWebhookActions($intent)
    {
        $payment_method = Tools::getValue('payment_method');
        $conveyorModel = new ConveyorModel();
        $conveyorModel->setModule($this->module);
        $conveyorModel->setContext($this->context);
        $conveyorModel->setPaymentIntentId($intent->id);

        if ('oxxo' === $payment_method) {
            $conveyorModel->setVoucherUrl($intent->next_action->oxxo_display_details->hosted_voucher_url);
            $conveyorModel->setVoucherExpire($intent->next_action->oxxo_display_details->expires_after);
        }

        $handler = new ActionsHandler();
        $handler->setConveyor($conveyorModel);
        StripeProcessLogger::logInfo(StripeProcessLogger::getFormattedMessageLogs('Handle Webhook Actions Beginning ', $intent->metadata->id_cart, $intent->id), 'orderSuccess - handleWebhookActions');
        $handler->addActions(
            'preparePaymentFlowActions',
            'updatePaymentIntent',
            'updateOrder',
            'addTentative'
        );

        if (!$handler->process('ValidationOrderActions')) {
            StripeProcessLogger::logError(StripeProcessLogger::getFormattedMessageLogs('Handle Webhook Actions Error => ' . json_encode($handler), $intent->metadata->id_cart, $intent->id), 'orderSuccess - handleWebhookActions');
        }
    }

    private function displayOrderConfirmation($intent)
    {
        StripeProcessLogger::logInfo(StripeProcessLogger::getFormattedMessageLogs('Display Order Confirmation Beginning ', $intent->metadata->id_cart, $intent->id), 'orderSuccess - displayOrderConfirmation');

        $orderId = null;
        $cartId = $intent->metadata->id_cart;
        if (!$cartId) {
            $stripeIdempotencyKey = new StripeIdempotencyKey();
            $stripeIdempotencyKey->getByIdPaymentIntent($intent->id);

            $cartId = $stripeIdempotencyKey->id_cart;
        }

        if ($cartId) {
            $orderId = Order::getIdByCartId($cartId);
        }

        if (!$orderId) {
            $url = Context::getContext()->link->getModuleLink(
                'stripe_official',
                'orderFailure',
                [],
                true
            );

            StripeProcessLogger::logInfo(StripeProcessLogger::getFormattedMessageLogs('Failed Order Url => ' . $url, $intent->metadata->id_cart, $intent->id), 'orderSuccess - displayOrderConfirmation');
        } else {
            $secure_key = isset($this->context->customer->secure_key) ? $this->context->customer->secure_key : false;
            $url = Context::getContext()->link->getPageLink(
                'order-confirmation',
                true,
                null,
                [
                    'id_cart' => isset($cartId) ? $cartId : 0,
                    'id_module' => (int) $this->module->id,
                    'id_order' => $orderId,
                    'key' => $secure_key,
                ]
            );
            StripeProcessLogger::logInfo(StripeProcessLogger::getFormattedMessageLogs('Display Order Confirmation Ending => ' . $url, $intent->metadata->id_cart, $intent->id), 'orderSuccess - displayOrderConfirmation');
        }

        Tools::redirect($url);
    }
}
