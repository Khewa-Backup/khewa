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
use StripeOfficial\Classes\StripeProcessLogger;

if (!defined('_PS_VERSION_')) {
    exit;
}

class StripeOrderStateSync
{
    /**
     * Sync Stripe intent status into our persistence and (optionally) the PS Order.
     *
     * @param PaymentIntent $intent
     * @param int $cartId
     * @param string $paymentIntentId
     * @param Order|null $order
     * @param string|null $effectiveStatus
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public static function sync(PaymentIntent $intent, int $cartId, string $paymentIntentId, ?Order $order = null, ?string $effectiveStatus = null): void
    {
        // ---- NEW: resolve ALL related orders (split orders) ----
        $orders = [];

        if ($order instanceof Order && Validate::isLoadedObject($order)) {
            $orders[(int) $order->id] = $order;

            // also collect siblings by reference (split orders share same reference)
            if (!empty($order->reference)) {
                $siblings = Db::getInstance()->executeS(
                    'SELECT id_order FROM ' . _DB_PREFIX_ . 'orders WHERE reference = "' . pSQL($order->reference) . '"'
                );
                foreach ($siblings as $row) {
                    $oid = (int) $row['id_order'];
                    if ($oid && !isset($orders[$oid])) {
                        $o = new Order($oid);
                        if (Validate::isLoadedObject($o)) {
                            $orders[$oid] = $o;
                        }
                    }
                }
            }
        } else {
            // Resolve by cart id (split orders are usually multiple orders with same id_cart)
            $ids = Db::getInstance()->executeS(
                'SELECT id_order FROM ' . _DB_PREFIX_ . 'orders WHERE id_cart = ' . (int) $cartId
            );

            if (empty($ids)) {
                StripeProcessLogger::logInfo('Order not found for cart', 'StripeOrderStateSync', $cartId, $intent->id ?? null);
            } else {
                foreach ($ids as $row) {
                    $oid = (int) $row['id_order'];
                    $o = new Order($oid);
                    if (Validate::isLoadedObject($o)) {
                        $orders[$oid] = $o;
                    } else {
                        StripeProcessLogger::logError('Order not loaded for id ' . $oid, 'StripeOrderStateSync', $cartId, $intent->id ?? null);
                    }
                }
            }
        }

        // Determine if this order is a backorder context (OOS but allowed to order)
        $isBackorder = self::orderHasBackorderLines($order);
        StripeProcessLogger::debugLog('StripeOrderStateSync - sync isBackorder: ' . json_encode($isBackorder), $cartId);

        // Compute the *PS order state* we want
        $OS_OOS_PAID = (int) Configuration::get('PS_OS_OUTOFSTOCK_PAID');     // "On backorder (paid)"
        $OS_OOS_UNPAID = (int) Configuration::get('PS_OS_OUTOFSTOCK_UNPAID');   // "On backorder (not paid)"
        $isPaidLike = ($intent->status === 'succeeded');
        $targetPsState = null;
        if ($isBackorder) {
            StripeProcessLogger::logInfo('Backorder is enabled: ', 'StripeOrderStateSync', $cartId, $intent->id ?? null);
            // Map INTERNAL status to OOS variants
            $effectiveStatus = $isPaidLike
                ? StripePaymentIntent::STATUS_OUTOFSTOCK_PAID
                : StripePaymentIntent::STATUS_OUTOFSTOCK_UNPAID;

            $targetPsState = $isPaidLike ? $OS_OOS_PAID : $OS_OOS_UNPAID;
        }

        $psStripePaymentIntent = new StripePaymentIntent();
        $psStripePaymentIntent->findByIdPaymentIntent($paymentIntentId);

        if (empty($psStripePaymentIntent->id_payment_intent)) {
            StripeProcessLogger::debugLog('StripeOrderStateSync - sync - Payment intent was not found with this intentId: ' . $paymentIntentId, $cartId);
            sleep(5);
            $psStripePaymentIntent->findByIdPaymentIntent($paymentIntentId);
        }

        // When a checkout session is used, the webhook may have already updated the
        // stripe_payment_intent record's id_payment_intent from cs_... to pi_... before
        // the browser confirmation path runs.  If the lookup by the checkout-session ID
        // failed, try the actual PaymentIntent ID as a fallback.
        if (empty($psStripePaymentIntent->id_payment_intent) && isset($intent->id) && $intent->id !== $paymentIntentId) {
            StripeProcessLogger::debugLog('StripeOrderStateSync - sync - Retrying PI lookup by actual intent id: ' . $intent->id, $cartId);
            $psStripePaymentIntent->findByIdPaymentIntent($intent->id);
        }

        // Prepare PI field updates but defer the save until after order state sync succeeds.
        // Saving PI before syncing order state can leave stripe_payment_intent.status updated
        // while orders.current_state is still stale when setOrderStateSafe returns false
        // (e.g. advisory-lock timeout with a concurrent process using a different target state).
        $piNeedsUpdate = false;
        if ($psStripePaymentIntent->validateStatusChange($effectiveStatus)) {
            if (isset($intent->id)) {
                $psStripePaymentIntent->setIdPaymentIntent($intent->id);
            }
            if (isset($intent->currency_conversion) && isset($intent->currency_conversion->amount_total)) {
                $amountInStoreCurrency = $intent->currency_conversion->amount_total;
            } else {
                $amountInStoreCurrency = $intent->amount;
            }
            $psStripePaymentIntent->setAmount($amountInStoreCurrency);
            $psStripePaymentIntent->setStatus($effectiveStatus);
            $piNeedsUpdate = true;

            StripeProcessLogger::debugLog('StripeOrderStateSync - sync - PI update staged for status: ' . $effectiveStatus . ' amount: ' . $amountInStoreCurrency, $cartId);
        } else {
            StripeProcessLogger::debugLog('StripeOrderStateSync - sync - Skipping PI status update: current=' . $psStripePaymentIntent->getPsStatus() . ', new=' . $effectiveStatus, $cartId);
        }

        // Sync order state, then save PI only when all state syncs succeed.
        // When no orders exist yet (e.g. legacy flow, order created later by webhook) save
        // PI immediately so subsequent webhook events can find the correct status.
        if (!empty($orders)) {
            $resolvedState = $targetPsState ?? $psStripePaymentIntent->getPsStatus();

            if ($resolvedState === null || $resolvedState === false) {
                StripeProcessLogger::logError(
                    'Could not resolve PS order state – PI status: ' . json_encode($psStripePaymentIntent->getStatus()) . ', intent status: ' . ($intent->status ?? 'unknown'),
                    'StripeOrderStateSync',
                    $cartId,
                    $intent->id ?? null
                );

                $allStateSyncsOk = false;
            } else {
                $allStateSyncsOk = true;

                foreach ($orders as $o) {
                    $synced = StripeOrderStateSync::setOrderStateSafe(
                        $o,
                        (int) $resolvedState,
                        (int) $cartId,
                        $intent->id ?? null
                    );

                    if (!$synced) {
                        $allStateSyncsOk = false;

                        StripeProcessLogger::logWarning(
                            'StripeOrderStateSync - sync - setOrderStateSafe failed for order ' . (int) $o->id . ' - PI save deferred',
                            'StripeOrderStateSync',
                            $cartId,
                            $intent->id ?? null
                        );
                    }
                }
            }

            if ($piNeedsUpdate) {
                if ($allStateSyncsOk) {
                    $psStripePaymentIntent->save();

                    StripeProcessLogger::debugLog(
                        'StripeOrderStateSync - sync - PI saved after successful state sync',
                        $cartId
                    );
                } else {
                    StripeProcessLogger::logWarning(
                        'StripeOrderStateSync - sync - PI save skipped because one or more state syncs failed',
                        'StripeOrderStateSync',
                        $cartId,
                        $intent->id ?? null
                    );
                }
            }
        } else {
            if ($piNeedsUpdate) {
                $psStripePaymentIntent->save();

                StripeProcessLogger::debugLog('StripeOrderStateSync - sync - PI saved without Order instances available', $cartId);
            }
            StripeProcessLogger::debugLog('StripeOrderStateSync - sync - no Order instances available, skipping state update', $cartId);
        }
    }

    /**
     * Safely change an order's state using OrderHistory.
     * Works on PS 1.7 / 8 / (forward-compatible with 9).
     */
    public static function setOrderStateSafe(Order $order, int $stateId, int $cartId, ?string $paymentIntentId): bool
    {
        $state = new OrderState($stateId);

        if (!Validate::isLoadedObject($state)) {
            StripeProcessLogger::logError('Invalid OrderState id ' . $stateId, 'StripeOrderStateSync', $cartId, $paymentIntentId);

            return false;
        }

        $lockKey = 'stripe_order_state_' . (int) $order->id;
        $lockHeld = false;

        $rawLock = Db::getInstance()->getValue(
            'SELECT GET_LOCK("' . pSQL($lockKey) . '", 10)',
            false
        );

        if ($rawLock === null || $rawLock === false) {
            // DB / lock-infrastructure error: fall back to executing without lock.
            // Matches the StripePdoLockService backward-compat pattern — worst case the
            // callback runs more than once, which is safer than silently dropping the sync.
            StripeProcessLogger::logWarning(
                'setOrderStateSafe advisory lock unavailable for order ' . (int) $order->id . ' - executing without lock',
                'StripeOrderStateSync',
                $cartId,
                $paymentIntentId
            );
        // $lockHeld stays false; RELEASE_LOCK is skipped in finally
        } elseif ((int) $rawLock !== 1) {
            // GET_LOCK timed out: another process holds the lock and is actively syncing
            // this order. Do NOT blindly return true — sync() already updated the module PI
            // row to the new status before calling here, so if the concurrent process
            // applied a different target state (e.g. refund vs payment), orders.current_state
            // and stripe_payment_intent.status would be left inconsistent.
            // Re-check the actual current_state from DB to confirm the target was applied.
            $currentStateInDb = (int) Db::getInstance()->getValue(
                'SELECT `current_state` FROM `' . _DB_PREFIX_ . 'orders` WHERE `id_order` = ' . (int) $order->id,
                false
            );
            if ($currentStateInDb === $stateId) {
                StripeProcessLogger::debugLog(
                    'setOrderStateSafe lock timeout - concurrent process already applied target state ' . $stateId . ' for order ' . (int) $order->id,
                    $cartId
                );

                return true;
            }
            StripeProcessLogger::logWarning(
                'setOrderStateSafe lock timeout and target state not yet applied - current=' . $currentStateInDb . ' expected=' . $stateId . ' for order ' . (int) $order->id,
                'StripeOrderStateSync',
                $cartId,
                $paymentIntentId
            );

            return false;
        } else {
            $lockHeld = true;
        }

        $isSuccessful = true;
        try {
            // Disable the PS ObjectModel entity-mapper cache for the entire state-change
            // section. PS caches loaded objects by class+id and never invalidates them on
            // raw SQL UPDATEs (e.g. updatePsOrders). With cache enabled, any internal
            // new Order($id) call inside changeIdOrderState/addWithemail/add reuses stale
            // field values and overwrites the correct payment label written by updatePsOrders.
            // Re-enabled in finally to restore the caller's environment.
            ObjectModel::disableCache();

            // Always reload inside the lock to avoid saving stale in-memory Order defaults.
            $freshOrder = new Order((int) $order->id);
            if (!Validate::isLoadedObject($freshOrder)) {
                StripeProcessLogger::logError('Order not loaded for id ' . (int) $order->id, 'StripeOrderStateSync', $cartId, $paymentIntentId);

                return false;
            }

            // Bypass the PS ObjectModel entity-mapper cache: raw SQL UPDATEs (e.g. updatePsOrders)
            // do not invalidate it, so new Order($id) can silently return a stale `payment` value.
            // Reading it directly from the DB ensures changeIdOrderState → Order::update() never
            // overwrites a correct "Ideal via Stripe" / "Link via Stripe" with the old cached label.
            $freshPayment = (string) Db::getInstance()->getValue(
                'SELECT `payment` FROM `' . _DB_PREFIX_ . 'orders` WHERE `id_order` = ' . (int) $freshOrder->id,
                false
            );
            if (!empty($freshPayment)) {
                $freshOrder->payment = $freshPayment;
            }

            $context = Context::getContext();

            if (!$context->shop || !Validate::isLoadedObject($context->shop)) {
                $context->shop = new Shop((int) $freshOrder->id_shop);
            }

            if (!$context->language || !Validate::isLoadedObject($context->language)) {
                $context->language = new Language((int) $freshOrder->id_lang);
            }

            if (!$context->currency || !Validate::isLoadedObject($context->currency)) {
                $context->currency = new Currency((int) $freshOrder->id_currency);
            }
            if (!$context->cart || !Validate::isLoadedObject($context->cart)) {
                $context->cart = new Cart((int) $freshOrder->id_cart);
            }

            if (!$context->customer || !Validate::isLoadedObject($context->customer)) {
                $context->customer = new Customer((int) $freshOrder->id_customer);
            }

            // Get the latest row from order history to compare with the order status.
            $lastOrderState = Db::getInstance()->getValue(
                'SELECT id_order_state FROM ' . _DB_PREFIX_ . 'order_history WHERE id_order = ' . (int) $freshOrder->id . ' ORDER BY id_order_history DESC',
                false
            );

            $currentState = new OrderState((int) $freshOrder->current_state);
            $targetState = new OrderState((int) $stateId);

            if (
                Validate::isLoadedObject($currentState)
                && Validate::isLoadedObject($targetState)
                && (bool) $currentState->paid
                && (bool) $targetState->paid
                && (int) $freshOrder->current_state === $stateId
                && (int) $lastOrderState === $stateId
            ) {
                StripeProcessLogger::debugLog(
                    'Skipping paid-to-paid state transition for order ' . (int) $freshOrder->id,
                    $cartId
                );

                return true;
            }

            // The two branches below are mutually exclusive.
            //
            // Branch A: the order is not yet in the target state.
            // Perform the full state transition through changeIdOrderState(), then
            // persist the corresponding history entry using addWithemail().
            //
            // Branch B: the order is already in the target state, but the latest
            // history entry is missing or outdated. Insert only the missing history
            // record and do not call changeIdOrderState() again, to avoid triggering
            // payment-related side effects on paid orders.
            $changeIdOrderStateCalled = false;

            if ((int) $freshOrder->current_state !== $stateId) {
                StripeProcessLogger::debugLog('Update the history order with state: ' . $stateId, $cartId);

                // When the target state is paid and OrderPayment rows already exist,
                // pass $use_existing_payment = true so that changeIdOrderState →
                // setInvoice() links those existing payments to the new invoice
                // rather than leaving them orphaned. This is relevant when
                // transitioning from a state without an invoice (e.g. CAPTURE_WAITING):
                // the orphan payments get linked first, getRestPaid() returns 0, and
                // the paid block inside changeIdOrderState skips payment creation.
                // For paid-to-paid transitions the invoice already has a number so
                // setInvoice is not called; duplicate prevention there relies on
                // getRestPaid() == 0 (the invoice is already fully settled).
                $useExistingPayment = false;
                if ((bool) $targetState->paid) {
                    $existingPaymentCount = (int) Db::getInstance()->getValue(
                        'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'order_payment`
                         WHERE `order_reference` = "' . pSQL($freshOrder->reference) . '"',
                        false
                    );
                    if ($existingPaymentCount > 0) {
                        $useExistingPayment = true;
                        StripeProcessLogger::debugLog(
                            'OrderPayment already exists for order ' . (int) $freshOrder->id . ' — passing use_existing_payment to link payments to invoice',
                            $cartId
                        );
                    }
                }

                $oh = new OrderHistory();
                $oh->id_order = (int) $freshOrder->id;
                $oh->changeIdOrderState($stateId, $freshOrder, $useExistingPayment);

                $changeIdOrderStateCalled = true;

                $lastOrderState = (int) Db::getInstance()->getValue(
                    'SELECT id_order_state
                 FROM ' . _DB_PREFIX_ . 'order_history
                 WHERE id_order = ' . (int) $freshOrder->id . '
                 ORDER BY id_order_history DESC'
                );

                if ($lastOrderState !== $stateId) {
                    $oh->date_add = date('Y-m-d H:i:s');
                    $oh->addWithemail(false);
                }

                // Re-read history to confirm the row was inserted.
                $lastOrderState = Db::getInstance()->getValue(
                    'SELECT id_order_state FROM ' . _DB_PREFIX_ . 'order_history WHERE id_order = ' . (int) $freshOrder->id . ' ORDER BY id_order_history DESC',
                    false
                );
            }

            if (!$changeIdOrderStateCalled && (int) $lastOrderState !== $stateId) {
                // State is already correct in ps_orders but the history row is stale.
                // Insert only the history row — do NOT call changeIdOrderState again,
                // as that would re-trigger the paid-state OrderPayment creation.
                StripeProcessLogger::debugLog('Order already in state: ' . $freshOrder->current_state . ' but history in other state: ' . $lastOrderState, $cartId);
                $oh = new OrderHistory();
                $oh->id_order = (int) $freshOrder->id;
                $oh->id_order_state = (int) $stateId;
                $oh->id_employee = 0;
                $oh->date_add = date('Y-m-d H:i:s');
                $oh->add(false);
            }
        } catch (PrestaShopException $e) {
            StripeProcessLogger::logError('Order state change failed: ' . $e->getMessage(), 'StripeOrderStateSync', $cartId, $paymentIntentId);
            $isSuccessful = false;
        } catch (Throwable $e) {
            StripeProcessLogger::logError('Order state change failed: ' . $e->getMessage(), 'StripeOrderStateSync', $cartId, $paymentIntentId);
            $isSuccessful = false;
        } finally {
            ObjectModel::enableCache();
            if ($lockHeld) {
                Db::getInstance()->execute('SELECT RELEASE_LOCK("' . pSQL($lockKey) . '")');
            }
        }

        return $isSuccessful;
    }

    /**
     * Returns true if ANY order line is out-of-stock but allowed to be ordered (backorder).
     */
    private static function orderHasBackorderLines(?Order $order): bool
    {
        if (empty($order)) {
            return false;
        }
        // Respect feature flag: if disabled, never mark as backorder
        $backorderStatusEnabled = (bool) Configuration::get(
            'PS_ENABLE_BACKORDER_STATUS',
            null,            // $id_lang
            null,            // $id_shop_group
            (int) $order->id_shop
        );

        if (!$backorderStatusEnabled) {
            return false;
        }

        $details = $order->getOrderDetailList();
        if (empty($details)) {
            return false;
        }

        foreach ($details as $d) {
            $idProduct = (int) $d['product_id'];
            $idDecl = (int) $d['product_attribute_id'];
            $qtyOrdered = (int) $d['product_quantity'];

            // 1) Historical signal at order time (very reliable)
            // Some PS versions populate product_quantity_in_stock on each order detail
            $qtyInStockAtOrder = isset($d['product_quantity_in_stock']) ? (int) $d['product_quantity_in_stock'] : null;
            if ($qtyInStockAtOrder !== null && $qtyInStockAtOrder < $qtyOrdered) {
                // confirm backorder is allowed for this product
                $policy = (int) StockAvailable::outOfStock($idProduct, (int) $order->id_shop);
                if (Product::isAvailableWhenOutOfStock($policy)) {
                    return true;
                }
            }

            // 2) Live shortage check (works even when stock is clamped at 0)
            $availableNow = (int) StockAvailable::getQuantityAvailableByProduct($idProduct, $idDecl, (int) $order->id_shop);

            // Product-level policy: 0=use global, 1=allow, 2=deny
            $policy = (int) StockAvailable::outOfStock($idProduct, (int) $order->id_shop);
            $allowBackorder = Product::isAvailableWhenOutOfStock($policy);

            // If there is a shortage and the product allows ordering when OOS → backorder line
            if ($allowBackorder && $qtyOrdered > $availableNow) {
                return true;
            }
        }

        return false;
    }
}
