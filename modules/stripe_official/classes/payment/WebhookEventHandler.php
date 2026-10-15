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

use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\Webhook;
use Stripe\WebhookSignature;
use StripeOfficial\Classes\StripeProcessLogger;

if (!defined('_PS_VERSION_')) {
    exit;
}

class WebhookEventHandler
{
    public const SUPPORTED_EVENTS = [
        Event::CHARGE_REFUNDED,
        Event::CHARGE_CAPTURED,
        Event::CHARGE_SUCCEEDED,
        Event::CHARGE_FAILED,
        Event::CHARGE_EXPIRED,
        Event::CHARGE_DISPUTE_CREATED,
        Event::PAYMENT_INTENT_CANCELED,
        Event::PAYMENT_INTENT_SUCCEEDED,
        Event::PAYMENT_INTENT_PAYMENT_FAILED,
    ];

    private $context;
    /**
     * @var Stripe_official
     */
    private $module;

    private $psStatuses;
    /**
     * @var PrestashopOrderService
     */
    private $prestashopOrderService;

    /**
     * @var StripePaymentIntentService
     */
    private $stripePaymentIntentService;

    /**
     * @var PrestashopBuildOrderService
     */
    private $prestashopBuildOrderService;

    /**
     * @var StripeCheckoutSessionService
     */
    private $stripeCheckoutSessionService;

    private $stripeAnonymize;

    public function __construct($context, $module)
    {
        $this->context = $context;
        $this->module = $module;

        $this->psStatuses = [
            Event::CHARGE_EXPIRED => Configuration::get('PS_OS_CANCELED'),
            Event::CHARGE_FAILED => Configuration::get('PS_OS_ERROR'),
            Event::CHARGE_SUCCEEDED => Configuration::get('PS_OS_PAYMENT'),
            Event::CHARGE_CAPTURED => Configuration::get('PS_OS_PAYMENT'),
            Event::PAYMENT_INTENT_SUCCEEDED => Configuration::get('PS_OS_PAYMENT'),
            Event::PAYMENT_INTENT_CANCELED => Configuration::get('PS_OS_CANCELED'),
            Event::PAYMENT_INTENT_PAYMENT_FAILED => Configuration::get('PS_OS_ERROR'),
            Event::CHARGE_REFUNDED => Configuration::get('PS_OS_REFUND'),
            Event::CHARGE_DISPUTE_CREATED => Configuration::get(Stripe_official::SEPA_DISPUTE),
        ];

        $secretKey = Stripe_official::getSecretKey();
        $this->stripePaymentIntentService = new StripePaymentIntentService($secretKey);
        $this->prestashopOrderService = new PrestashopOrderService($this->context, $this->module, $secretKey);
        $this->prestashopBuildOrderService = new PrestashopBuildOrderService($this->context, $this->module, $secretKey);
        $this->stripeAnonymize = new StripeAnonymize();
        $this->stripeCheckoutSessionService = new StripeCheckoutSessionService($secretKey);
    }

    public function handleRequest($content, $signatureHeader, $webhookSecret)
    {
        $this->processEvent($content, $signatureHeader, $webhookSecret);
    }

    public function processEvent($content, $signatureHeader, $webhookSecret): void
    {
        $data = json_decode($content, true);
        $contentAnonymized = $this->stripeAnonymize->anonymize($data);
        $eventType = $data['type'] ?? null;
        if (!$this->isSupportedEvent($eventType)) {
            return;
        }

        if (!$webhookSecret) {
            StripeProcessLogger::logError('Webhook secret not set. Please set webhook secret before using webhooks!', 'WebhookEventHandler', null);

            return;
        }

        $event = $this->getStripeEvent($content, $signatureHeader, $webhookSecret);
        if (!$event) {
            StripeProcessLogger::logError('Not a valid stripe event => ' . json_encode($contentAnonymized), 'WebhookEventHandler', null);

            return;
        }

        $cart = $this->getPsCart($event);
        if ($cart && (int) $cart->id_shop > 0) {
            $this->switchToCartShopContext((int) $cart->id_shop);
        }
        if (!$this->isCartValid($cart)) {
            StripeProcessLogger::logError('Not a valid cart => ' . json_encode($cart), 'WebhookEventHandler', null);

            return;
        }

        $eventAnonymized = $this->stripeAnonymize->anonymize($event);
        StripeProcessLogger::logInfo('Process Webhook event => ' . json_encode($eventAnonymized), 'WebhookEventHandler', $cart->id);

        switch ($event->type) {
            case Event::CHARGE_REFUNDED:
                $this->refundedEvent($event, $cart);
                break;
            case Event::CHARGE_CAPTURED:
                $this->capturedEvent($event, $cart);
                break;
            case Event::CHARGE_SUCCEEDED:
            case Event::PAYMENT_INTENT_SUCCEEDED:
                $this->paymentSucceededEvent($event, $cart);
                break;
            case Event::CHARGE_EXPIRED:
            case Event::PAYMENT_INTENT_CANCELED:
                $this->cancelEvent($event, $cart);
                break;
            case Event::CHARGE_FAILED:
            case Event::PAYMENT_INTENT_PAYMENT_FAILED:
                $this->paymentFailedEvent($event, $cart);
                break;
            case Event::CHARGE_DISPUTE_CREATED:
                $this->disputeEvent($event, $cart);
                break;
        }
    }

    private function switchToCartShopContext(int $cartShopId): void
    {
        if ((int) Stripe_official::getShopIdContext() === $cartShopId) {
            return;
        }
        $cartShop = new Shop($cartShopId);
        if (!Validate::isLoadedObject($cartShop)) {
            StripeProcessLogger::debugLog('switchToCartShopContext - shop not found for id: ' . $cartShopId);

            return;
        }
        StripeProcessLogger::debugLog(
            'switchToCartShopContext - switching context from shop ' . Stripe_official::getShopIdContext() . ' to cart shop ' . $cartShopId
        );
        Context::getContext()->shop = $cartShop;
        Shop::setContext(Shop::CONTEXT_SHOP, $cartShopId);
        $this->context = Context::getContext();
        $secretKey = Stripe_official::getSecretKey();
        $this->stripePaymentIntentService = new StripePaymentIntentService($secretKey);
        $this->prestashopOrderService = new PrestashopOrderService($this->context, $this->module, $secretKey);
        $this->prestashopBuildOrderService = new PrestashopBuildOrderService($this->context, $this->module, $secretKey);
        $this->stripeCheckoutSessionService = new StripeCheckoutSessionService($secretKey);
    }

    public function isSupportedEvent($eventType)
    {
        return in_array($eventType, self::SUPPORTED_EVENTS);
    }

    protected function getStripeEvent($content, $signatureHeader, $webhookSecret)
    {
        return $this->constructEvent($content, $signatureHeader, $webhookSecret);
    }

    protected function constructEvent($content, $signatureHeader, $secret)
    {
        $event = null;
        try {
            $event = Webhook::constructEvent($content, $signatureHeader, $secret);
        } catch (SignatureVerificationException $e) {
            // Invalid signature
            StripeProcessLogger::logError('Invalid signature => ' . $e->getMessage() . ' - ' . $e->getTraceAsString(), 'WebhookEventHandler');
        }

        return $event;
    }

    public function refundedEvent(Event $event, ?Cart $cart = null): void
    {
        if (!$this->validateEventForStatusChange($event)) {
            StripeProcessLogger::logWarning('refundedEvent - validateEventForStatusChange returned false: ' . $event->type, 'WebhookEventHandler', $cart->id ?? null);

            return;
        }
        $psPaymentIntent = $this->getPsPaymentIntentFromEvent($event);
        if ($psPaymentIntent) {
            $refundedAmount = $event->data->object->amount_refunded;
            $capturedAmount = $event->data->object->amount_captured;
            $idStripe = $event->data->object->id;
            $currency = $event->data->object->currency;
            $stripePaymentIntent = $this->prestashopOrderService->findStripePaymentIntent($psPaymentIntent->id_payment_intent);

            if ((int) $refundedAmount == (int) $capturedAmount) {
                $this->updatePsPaymentIntentStatus($psPaymentIntent, StripePaymentIntent::STATUS_REFUNDED);
                $this->updateStripePaymentAmount((int) $capturedAmount, $idStripe, $currency);
                $this->syncStatusWithPs($psPaymentIntent, $cart);
                StripeOrderStateSync::sync(
                    $stripePaymentIntent,
                    (int) $cart->id,
                    $psPaymentIntent->id_payment_intent,
                    null,
                    $psPaymentIntent->getPsStatus()
                );
            } elseif ($psPaymentIntent->getPsStatus() !== StripePaymentIntent::STATUS_REFUNDED) {
                $this->updateStripePaymentAmount((int) $refundedAmount, $idStripe, $currency);
                $this->updatePsPaymentIntentStatus($psPaymentIntent, StripePaymentIntent::STATUS_PARTIALLY_REFUNDED);
                $this->syncStatusWithPs($psPaymentIntent, $cart);
                StripeOrderStateSync::sync(
                    $stripePaymentIntent,
                    (int) $cart->id,
                    $psPaymentIntent->id_payment_intent,
                    null,
                    $psPaymentIntent->getPsStatus()
                );
            }
        } else {
            StripeProcessLogger::logWarning('refundedEvent - psPaymentIntent not found', 'WebhookEventHandler', $cart->id ?? null);
        }
    }

    public function cancelEvent($event, $cart = null): void
    {
        $psPaymentIntent = $this->getPsPaymentIntentFromEvent($event);
        $stripePaymentIntent = $this->prestashopOrderService->findStripePaymentIntent($psPaymentIntent->id_payment_intent);
        if ($psPaymentIntent && ($psPaymentIntent->validateStatusChange(StripePaymentIntent::STATUS_CANCEL) || $psPaymentIntent->getStatus() === StripePaymentIntent::STATUS_CANCEL)) {
            if ($psPaymentIntent->validateStatusChange(StripePaymentIntent::STATUS_CANCEL)) {
                $this->updatePsPaymentIntentStatus($psPaymentIntent, StripePaymentIntent::STATUS_CANCEL);
            } else {
                StripeProcessLogger::debugLog('cancelEvent - psPaymentIntent already at STATUS_CANCEL, skipping status update', $cart->id ?? null);
            }
            $this->syncStatusWithPs($psPaymentIntent, $cart);
            StripeOrderStateSync::sync(
                $stripePaymentIntent,
                (int) $cart->id,
                $psPaymentIntent->id_payment_intent,
                null,
                StripePaymentIntent::STATUS_CANCEL
            );
        } elseif (!$psPaymentIntent->id) {
            StripeProcessLogger::logWarning('cancelEvent - psPaymentIntent not found', 'WebhookEventHandler', $cart->id ?? null);
        }
    }

    public function capturedEvent($event, $cart = null): void
    {
        $psPaymentIntent = $this->getPsPaymentIntentFromEvent($event);
        $stripePaymentIntent = $this->prestashopOrderService->findStripePaymentIntent($psPaymentIntent->id_payment_intent);

        if ($psPaymentIntent && ($psPaymentIntent->validateStatusChange(StripePaymentIntent::STATUS_SUCCESS) || $psPaymentIntent->getStatus() === StripePaymentIntent::STATUS_SUCCESS)) {
            if ($psPaymentIntent->validateStatusChange(StripePaymentIntent::STATUS_SUCCESS)) {
                $this->updatePsPaymentIntentStatus($psPaymentIntent, StripePaymentIntent::STATUS_SUCCESS);
            } else {
                StripeProcessLogger::debugLog('capturedEvent - psPaymentIntent already at STATUS_SUCCESS, skipping status update', $cart->id ?? null);
            }
            $this->syncStatusWithPs($psPaymentIntent, $cart);
            StripeOrderStateSync::sync(
                $stripePaymentIntent,
                (int) $cart->id,
                $psPaymentIntent->id_payment_intent,
                null,
                $psPaymentIntent->getPsStatus()
            );
        } elseif (!$psPaymentIntent->id) {
            StripeProcessLogger::logWarning('capturedEvent - psPaymentIntent not found', 'WebhookEventHandler', $cart->id ?? null);
        }
    }

    public function paymentSucceededEvent($event, $cart = null): void
    {
        if (!$this->validateEventForStatusChange($event)) {
            StripeProcessLogger::debugLog('paymentSucceededEvent validateEventForStatusChange failed', $cart->id ?? null);

            return;
        }
        $psPaymentIntent = $this->getPsPaymentIntentFromEvent($event);
        $cartId = isset($event->data->object->metadata->id_cart) ? $event->data->object->metadata->id_cart : null;
        if (!$cartId && $cart) {
            $cartId = (int) $cart->id;
            StripeProcessLogger::debugLog('paymentSucceededEvent metadata.id_cart missing, fallback to resolved cart id: ' . $cartId, $cartId);
        }
        if (empty($psPaymentIntent->id_payment_intent) && $cartId) {
            StripeProcessLogger::debugLog('paymentSucceededEvent no payment intent - getting from cartId');
            $stripeIdempotencyKey = new StripeIdempotencyKey();
            $stripeIdempotencyKey->getByIdCart($cartId);
            if ($stripeIdempotencyKey->id_payment_intent) {
                $psPaymentIntent->findByIdPaymentIntent($stripeIdempotencyKey->id_payment_intent);
            }
        }
        if (empty($psPaymentIntent->id_payment_intent) && Configuration::get('STRIPE_ENABLE_PAYMENT_ELEMENTS') == 0) {
            StripeProcessLogger::debugLog('paymentSucceededEvent no payment intent - no payment elements - getting again');
            $paymentIntentId = $this->getStripePaymentIntentIdFromEvent($event);
            $session = $this->stripeCheckoutSessionService->getStripeCheckoutSessionByPaymentIntentId($paymentIntentId);
            $psPaymentIntent->findByIdPaymentIntent($session->id);
        }
        // Advisory lock prevents charge.succeeded and payment_intent.succeeded
        // webhooks from both passing validateStatusChange when they read the PI
        // status concurrently before either writes the new value.
        $successLockKey = 'stripe_wh_success_' . pSQL($psPaymentIntent->id_payment_intent);
        $successLockHeld = ((int) Db::getInstance()->getValue(
            'SELECT GET_LOCK("' . $successLockKey . '", 5)',
            false
        )) === 1;
        try {
            // Re-read PI status inside the lock to get the authoritative value.
            if ($successLockHeld) {
                $psPaymentIntent = $this->getPsPaymentIntentFromEvent($event);
            }
            if ($psPaymentIntent && $psPaymentIntent->validateStatusChange(StripePaymentIntent::STATUS_SUCCESS)) {
                $this->updatePsPaymentIntentStatus($psPaymentIntent, StripePaymentIntent::STATUS_SUCCESS);
                $this->syncStatusWithPs($psPaymentIntent, $cart, $this->getChargeIdFromEvent($event));
                $stripePaymentIntent = $this->prestashopOrderService->findStripePaymentIntent($psPaymentIntent->id_payment_intent);

                StripeOrderStateSync::sync(
                    $stripePaymentIntent,
                    (int) $cartId,
                    $psPaymentIntent->id_payment_intent,
                    null,
                    $psPaymentIntent->getPsStatus()
                );
            } elseif (!$psPaymentIntent->id) {
                StripeProcessLogger::logWarning('paymentSucceededEvent - psPaymentIntent not found', 'WebhookEventHandler', $cart->id ?? null);
            } else {
                StripeProcessLogger::debugLog('paymentSucceededEvent - PI status transition skipped, backfilling transaction_id', $cart->id ?? null);
                $order = $cart ? $this->getPsOrder($cart) : null;
                if ($order && $this->isOrderValid($order)) {
                    $chargeId = $this->getChargeIdFromEvent($event);
                    $stripePaymentIntent = null;
                    if ($chargeId && !empty($psPaymentIntent->id_payment_intent)) {
                        $stripePaymentIntent = PaymentIntent::constructFrom([
                            'id' => $psPaymentIntent->id_payment_intent,
                            'latest_charge' => $chargeId,
                        ]);
                    }
                    if (!$stripePaymentIntent) {
                        $stripePaymentIntent = $this->prestashopOrderService->findStripePaymentIntent($psPaymentIntent->id_payment_intent);
                    }
                    if ($stripePaymentIntent) {
                        $this->prestashopOrderService->updatePsOrders($stripePaymentIntent, $order->id);
                    }
                }
            }
        } finally {
            if ($successLockHeld) {
                Db::getInstance()->execute('SELECT RELEASE_LOCK("' . $successLockKey . '")');
            }
        }
    }

    public function paymentFailedEvent($event, $cart = null): void
    {
        $psPaymentIntent = $this->getPsPaymentIntentFromEvent($event);
        $stripePaymentIntent = $this->prestashopOrderService->findStripePaymentIntent($psPaymentIntent->id_payment_intent);

        if ($psPaymentIntent && $psPaymentIntent->validateStatusChange(StripePaymentIntent::STATUS_FAIL)) {
            // Guard: Do not degrade an order that is already in a paid state.
            // This prevents abandoned/expired intents from overriding a successful payment
            // made through a second attempt (another Stripe APM or third-party module).
            $order = $this->getPsOrder($cart);
            if ($order && $this->isOrderPaid($order)) {
                StripeProcessLogger::logInfo(
                    'paymentFailedEvent - Skipping status change: order is already in paid state',
                    'WebhookEventHandler',
                    $cart->id ?? null,
                    $psPaymentIntent->id_payment_intent
                );

                return;
            }

            $this->updatePsPaymentIntentStatus($psPaymentIntent, StripePaymentIntent::STATUS_FAIL);
            $this->syncStatusWithPs($psPaymentIntent, $cart);
            StripeOrderStateSync::sync(
                $stripePaymentIntent,
                (int) $cart->id,
                $psPaymentIntent->id_payment_intent,
                null,
                $psPaymentIntent->getPsStatus()
            );
        } elseif (!$psPaymentIntent->id) {
            StripeProcessLogger::logWarning('paymentFailedEvent - psPaymentIntent not found', 'WebhookEventHandler', $cart->id ?? null);
        }
    }

    public function disputeEvent($event, $cart = null): void
    {
        $psPaymentIntent = $this->getPsPaymentIntentFromEvent($event);
        $stripePaymentIntent = $this->prestashopOrderService->findStripePaymentIntent($psPaymentIntent->id_payment_intent);

        if ($psPaymentIntent && ($psPaymentIntent->validateStatusChange(StripePaymentIntent::STATUS_DISPUTE) || $psPaymentIntent->getStatus() === StripePaymentIntent::STATUS_DISPUTE)) {
            if ($psPaymentIntent->validateStatusChange(StripePaymentIntent::STATUS_DISPUTE)) {
                $this->updatePsPaymentIntentStatus($psPaymentIntent, StripePaymentIntent::STATUS_DISPUTE);
            } else {
                StripeProcessLogger::debugLog('disputeEvent - psPaymentIntent already at STATUS_DISPUTE, skipping status update', $cart->id ?? null);
            }
            $this->syncStatusWithPs($psPaymentIntent, $cart);
            StripeOrderStateSync::sync(
                $stripePaymentIntent,
                (int) $cart->id,
                $psPaymentIntent->id_payment_intent,
                null,
                $psPaymentIntent->getPsStatus()
            );
        } elseif (!$psPaymentIntent->id) {
            StripeProcessLogger::logWarning('disputeEvent - psPaymentIntent not found', 'WebhookEventHandler', $cart->id ?? null);
        }
    }

    public function getStripePaymentIntentIdFromEvent(Event $event)
    {
        return isset($event->data->object->payment_intent) ?
            $event->data->object->payment_intent :
            (
                isset($event->data->object->id) ?
                    $event->data->object->id :
                    null
            );
    }

    public function getChargeIdFromEvent(Event $event)
    {
        $eventObject = $event->data->object ?? null;
        if (!$eventObject) {
            return null;
        }

        // charge.succeeded / charge.captured / charge.refunded etc.
        if (strpos($event->type, 'charge.') === 0 && isset($eventObject->id)) {
            return $eventObject->id;
        }

        // payment_intent.succeeded
        if (isset($eventObject->latest_charge)) {
            return $eventObject->latest_charge;
        }
        if (isset($eventObject->charges->data[0]->id)) {
            return $eventObject->charges->data[0]->id;
        }

        return null;
    }

    public function getPsPaymentIntentFromEvent(Event $event)
    {
        $psStripePaymentIntent = null;
        $paymentIntentId = $this->getStripePaymentIntentIdFromEvent($event);
        if ($paymentIntentId) {
            $psStripePaymentIntent = new StripePaymentIntent();
            $psStripePaymentIntent->findByIdPaymentIntent($paymentIntentId);
            $eventObject = isset($event->data->object) ? $event->data->object : null;
            if (!$psStripePaymentIntent->id && $eventObject) {
                StripeProcessLogger::logWarning('getPsPaymentIntentFromEvent - psStripePaymentIntent not found, creating from event data', 'WebhookEventHandler');
                $psStripePaymentIntent = StripePaymentIntent::createPaymentIntent((object) [
                    'id' => $paymentIntentId,
                    'currency' => isset($eventObject->currency) ? $eventObject->currency : '',
                    'created' => isset($eventObject->created) ? (int) $eventObject->created : time(),
                ]);
            }
        }

        return $psStripePaymentIntent;
    }

    protected function updatePsPaymentIntentStatus(StripePaymentIntent $psPaymentIntent, string $status): void
    {
        $psPaymentIntent->setStatus($status);
        $psPaymentIntent->save();
    }

    public function syncStatusWithPs(StripePaymentIntent $psPaymentIntent, Cart $cart, ?string $explicitChargeId = null): void
    {
        $intentToUseAfterOrderUpdated = $stripePaymentIntent = null;
        $order = $this->getPsOrder($cart);
        if (!$order) {
            StripeProcessLogger::debugLog('syncStatusWithPs order was not found', $cart->id);
            $psOrderStatus = $psPaymentIntent->getPsStatusForOrderCreation();
            if ($psOrderStatus) {
                $lockKey = 'stripe_pi_' . $psPaymentIntent->id_payment_intent;
                $lockHeld = false;
                $skipLockBlock = false;

                $rawLock = Db::getInstance()->getValue('SELECT GET_LOCK("' . pSQL($lockKey) . '", 5)', false);

                if ($rawLock === null || $rawLock === false) {
                    // DB / lock-infrastructure error: fall back to executing without lock
                    // (matches StripePdoLockService backward-compat pattern).
                    StripeProcessLogger::logWarning(
                        'Could not acquire order creation lock - executing without lock',
                        'WebhookEventHandler',
                        $cart->id,
                        $psPaymentIntent->id_payment_intent
                    );
                // $lockHeld stays false; finally skips RELEASE_LOCK
                } elseif ((int) $rawLock !== 1) {
                    // Timeout: another process holds the lock. Final re-check —
                    // the concurrent process may have already created the order.
                    $order = $this->getPsOrder($cart);
                    if ($order) {
                        StripeProcessLogger::debugLog(
                            'syncStatusWithPs lock timeout but order now exists - proceeding to update',
                            $cart->id
                        );
                        $intentToUseAfterOrderUpdated = $this->prestashopOrderService->findStripePaymentIntent($psPaymentIntent->id_payment_intent);
                        $this->prestashopOrderService->updatePsStripePayment($intentToUseAfterOrderUpdated, $cart->id);
                        $psPaymentIntent->setAmount($intentToUseAfterOrderUpdated->amount);
                        $psPaymentIntent->setIdPaymentIntent($intentToUseAfterOrderUpdated->id);
                        $psPaymentIntent->save();
                        $skipLockBlock = true;
                    } else {
                        // Order still absent after timeout: the lock holder may have failed
                        // or stalled. Fall back to executing the order-creation path without
                        // the lock — matches the project's backward-compat pattern and ensures
                        // a paid order is never silently dropped when this webhook is the only
                        // remaining creation opportunity (Stripe already received 200 OK).
                        // The re-check inside !$skipLockBlock still guards against a concurrent
                        // creation completing between here and the inner try block.
                        StripeProcessLogger::logWarning(
                            'syncStatusWithPs lock timeout and order still absent - falling back to create without lock',
                            'WebhookEventHandler',
                            $cart->id,
                            $psPaymentIntent->id_payment_intent
                        );
                        // $lockHeld stays false (finally skips RELEASE_LOCK);
                        // $skipLockBlock stays false so the order-creation block runs.
                    }
                } else {
                    $lockHeld = true;
                }

                if (!$skipLockBlock) {
                    try {
                        // Re-check inside the advisory lock: another concurrent event may have
                        // just finished creating the order while we were waiting.
                        $order = $this->getPsOrder($cart);
                        if (!$order) {
                            $stripePaymentIntent = $this->prestashopOrderService->findStripePaymentIntent($psPaymentIntent->id_payment_intent);

                            if (!$stripePaymentIntent) {
                                StripeProcessLogger::logError('Stripe PaymentIntent not found', 'WebhookEventHandler', $cart->id, $psPaymentIntent->id_payment_intent);

                                return;
                            }

                            $orderModel = $this->prestashopBuildOrderService->buildAndCreatePrestashopOrder($psPaymentIntent, $stripePaymentIntent, null, $cart);

                            StripeProcessLogger::logInfo('Create PrestaShop Order from Webhook: ' . json_encode($orderModel), 'WebhookEventHandler', $cart->id, $stripePaymentIntent->id);
                            $this->stripePaymentIntentService->updateStripePaymentIntent($stripePaymentIntent, $orderModel->orderReference);
                            if (isset($stripePaymentIntent->currency_conversion) && isset($stripePaymentIntent->currency_conversion->amount_total)) {
                                $amountInStoreCurrency = $stripePaymentIntent->currency_conversion->amount_total;
                            } else {
                                $amountInStoreCurrency = $stripePaymentIntent->amount;
                            }
                            $psPaymentIntent->setAmount($amountInStoreCurrency);
                            $psPaymentIntent->setIdPaymentIntent($stripePaymentIntent->id);
                            $psPaymentIntent->save();
                            $this->prestashopOrderService->createPsStripePayment($stripePaymentIntent, $orderModel);
                            $intentToUseAfterOrderUpdated = $stripePaymentIntent;
                            $order = $orderModel->order;
                        } else {
                            StripeProcessLogger::debugLog('syncStatusWithPs re-check found existing order inside lock: ' . $order->id, $cart->id);
                        }
                    } finally {
                        if ($lockHeld) {
                            Db::getInstance()->execute('SELECT RELEASE_LOCK("' . pSQL($lockKey) . '")');
                        }
                    }
                }
            }
        } else {
            StripeProcessLogger::debugLog("syncStatusWithPs order was found with id: $order->id", $cart->id);
            $intentToUseAfterOrderUpdated = $this->prestashopOrderService->findStripePaymentIntent($psPaymentIntent->id_payment_intent);
            $this->prestashopOrderService->updatePsStripePayment($intentToUseAfterOrderUpdated, $cart->id);
            $psPaymentIntent->setAmount($intentToUseAfterOrderUpdated->amount);
            $psPaymentIntent->setIdPaymentIntent($intentToUseAfterOrderUpdated->id);
            $psPaymentIntent->save();
        }
        if (!$this->isOrderValid($order)) {
            return;
        }
        $order = new Order($order->id);
        if ($intentToUseAfterOrderUpdated) {
            $this->prestashopOrderService->updatePsOrders($intentToUseAfterOrderUpdated, $order->id);
        }
        $this->prestashopOrderService->updateTransactionIdForPSOrder($cart->id, $intentToUseAfterOrderUpdated, $explicitChargeId);
    }

    public function validateEventForStatusChange($event): bool
    {
        switch ($event->type) {
            case Event::PAYMENT_INTENT_SUCCEEDED:
                return true;

            case Event::CHARGE_SUCCEEDED:
                return !empty($event->data->object->captured);

            case Event::CHARGE_REFUNDED:
                return true;

            default:
                return false;
        }
    }

    public function getPsCart(Event $event): ?Cart
    {
        $cart = null;
        $paymentIntentId = $this->getStripePaymentIntentIdFromEvent($event);
        if ($paymentIntentId) {
            $psIdempotencyKey = new StripeIdempotencyKey();
            $psIdempotencyKey->getByIdPaymentIntent($paymentIntentId);

            $cart = isset($psIdempotencyKey->id_cart) ? new Cart($psIdempotencyKey->id_cart) : null;
        }
        if (!$cart) {
            $cartId = isset($event->data->object->metadata->id_cart) ? $event->data->object->metadata->id_cart : null;
            $cart = $cartId ? new Cart($cartId) : null;
        }

        return $cart ?: null;
    }

    public function isCartValid($cart = null): bool
    {
        if (!$cart) {
            return false;
        }
        if ((int) $cart->id_shop_group <= 0) {
            $cart->id_shop_group = (int) Stripe_official::getShopGroupIdContext();
            StripeProcessLogger::debugLog('Cart shop group id was 0, set to: ' . $cart->id_shop_group, $cart->id);
        }

        if ((int) $cart->id_shop_group !== (int) Stripe_official::getShopGroupIdContext() || (int) $cart->id_shop !== (int) Stripe_official::getShopIdContext()) {
            StripeProcessLogger::debugLog('Cart shop group or id_shop does not match context: ' . json_encode([$cart->id_shop_group, $cart->id_shop]), $cart->id);

            return false;
        }

        return true;
    }

    public function getPsOrder(Cart $cart): ?Order
    {
        $orderId = Order::getIdByCartId($cart->id) ?: null;
        $order = $orderId ? new Order($orderId) : null;

        return $order ?: null;
    }

    public function isOrderValid($order = null): bool
    {
        if (!$order) {
            return false;
        }
        if ('stripe_official' !== $order->module) {
            return false;
        }

        return true;
    }

    public function isOrderPaid(?Order $order = null): bool
    {
        if (!$order || !Validate::isLoadedObject($order)) {
            return false;
        }

        $orderState = new OrderState((int) $order->current_state);
        if (!Validate::isLoadedObject($orderState)) {
            return false;
        }

        return (bool) $orderState->paid;
    }

    protected function updateStripePaymentAmount(int $amount, $idStripe, $currency): void
    {
        $amount = Stripe_official::isZeroDecimalCurrency(Tools::strtoupper($currency)) ? $amount : $amount / 100;
        Db::getInstance()->Execute(
            'UPDATE `' . _DB_PREFIX_ . 'stripe_payment` SET `refund` = "'
            . pSQL((string) $amount) . '" WHERE `id_stripe` = "' . pSQL($idStripe) . '"'
        );
    }

    public static function getWebhookSecret($content, $signatureHeader)
    {
        $sql = 'SELECT `id_shop`, `value` AS `webhook_secret`
                FROM `' . _DB_PREFIX_ . 'configuration`
                WHERE `name` = \'STRIPE_WEBHOOK_SIGNATURE\'
                ORDER BY `id_shop` ASC';

        $webhookSecret = Db::getInstance()->executeS($sql);

        foreach ($webhookSecret as $webhookEntry) {
            $secret = $webhookEntry['webhook_secret'];
            $shopId = $webhookEntry['id_shop'];

            if (empty($secret)) {
                continue;
            }

            try {
                WebhookSignature::verifyHeader($content, $signatureHeader, $secret);
                StripeProcessLogger::debugLog('Webhook verified with Webhook Secret: ' . $secret);

                if (empty($shopId)) {
                    StripeProcessLogger::debugLog('Matching Configuration Secret found for NULL Shop ID, setting default shop');
                    Context::getContext()->shop = Stripe_official::getShopIdContext() ? new Shop(Stripe_official::getShopIdContext()) : null;
                    Shop::setContext(Shop::CONTEXT_ALL);
                } else {
                    StripeProcessLogger::debugLog('Matching Configuration Secret found for Shop with ID: ' . $shopId);
                    $shopInstance = new Shop($shopId);
                    Context::getContext()->shop = $shopInstance;
                    Shop::setContext(Shop::CONTEXT_SHOP, $shopId);
                }

                return $secret;
            } catch (SignatureVerificationException $e) {
                StripeProcessLogger::debugLog('Webhook Secret verification failed for Webhook Secret ' . $secret . ': ' . $e->getMessage());
            }
        }

        return null;
    }
}
