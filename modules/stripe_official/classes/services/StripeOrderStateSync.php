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

        // Persist PI row if allowed
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
            $psStripePaymentIntent->save();

            StripeProcessLogger::debugLog('StripeOrderStateSync - sync - updating stripe payment intent status to: ' . $effectiveStatus . ' amount: ' . $amountInStoreCurrency, $cartId);
        } else {
            StripeProcessLogger::debugLog('StripeOrderStateSync - sync - Skipping PI status update: current=' . $psStripePaymentIntent->getPsStatus() . ', new=' . $effectiveStatus, $cartId);
        }

        // Only attempt to set order state if we have a valid Order instance
        if (!empty($orders)) {
            foreach ($orders as $o) {
                StripeOrderStateSync::setOrderStateSafe($o, $targetPsState ?: $psStripePaymentIntent->getPsStatus(), (int) $cartId, $intent->id ?? null);
            }
        } else {
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

        try {
            // Get the latest row from order history to compare with the order status. Sometimes, history is not updated correctly
            $lastOrderState = Db::getInstance()->getValue(
                'SELECT id_order_state FROM ' . _DB_PREFIX_ . 'order_history WHERE id_order = ' . (int) $order->id . ' ORDER BY id_order_history DESC',
                false
            );

            // If (very rarely) history still didn’t record, force a backfill
            if ((int) $order->current_state !== $stateId) {
                StripeProcessLogger::debugLog('Update the history order with state: ' . $stateId, $cartId);
                $oh = new OrderHistory();
                $oh->id_order = (int) $order->id;
                $oh->changeIdOrderState($stateId, $order);
            }

            if ((int) $lastOrderState !== $stateId) {
                // Already in target, but history missing → backfill once
                StripeProcessLogger::debugLog('Order already in state: ' . $order->current_state . ' but history in other state: ' . $lastOrderState, $cartId);
                $oh = new OrderHistory();
                $oh->id_order = (int) $order->id;
                $oh->id_order_state = (int) $stateId;
                $oh->id_employee = 0;
                $oh->date_add = date('Y-m-d H:i:s'); // avoid 0000-00-00 00:00:00
                $oh->addWithemail(false);
            }

            return true;
        } catch (PrestaShopException $e) {
            StripeProcessLogger::logError('Order state change failed: ' . $e->getMessage(), 'StripeOrderStateSync', $cartId, $paymentIntentId);

            return false;
        }
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
