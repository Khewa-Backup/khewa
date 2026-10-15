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

class StripePaymentRetryService
{
    public const GENERIC_ERROR = 'Unable to restore this payment. Please return to checkout and try again.';

    public function createToken(Order $order, $paymentContextId)
    {
        return hash_hmac('sha256', $this->getPayload($order, $paymentContextId), _COOKIE_KEY_);
    }

    public function validateToken(Order $order, $paymentContextId, $providedToken)
    {
        if (!is_string($providedToken) || $providedToken === '') {
            return false;
        }

        return hash_equals($this->createToken($order, $paymentContextId), $providedToken);
    }

    public function validateGuestContext(Order $order, Cart $cart, Customer $customer, $guestId)
    {
        return Validate::isLoadedObject($order)
            && Validate::isLoadedObject($cart)
            && Validate::isLoadedObject($customer)
            && (bool) $customer->is_guest
            && (int) $customer->id === (int) $order->id_customer
            && (int) $cart->id_guest === (int) $guestId;
    }

    public function restoreCart(Order $order)
    {
        $paymentContextId = '';
        try {
            $oldCart = new Cart((int) $order->id_cart);
            $customer = new Customer((int) $order->id_customer);
            if (!Validate::isLoadedObject($oldCart)) {
                throw new Exception('Old cart ' . (int) $order->id_cart . ' not found');
            }
            if (!Validate::isLoadedObject($customer)) {
                throw new Exception('Customer ' . (int) $order->id_customer . ' not found');
            }

            $this->validateAddresses($oldCart, $customer, $order);
            $newCart = $this->copyCart($oldCart, $customer);
            $this->setContext($newCart, $customer);

            return $newCart;
        } catch (Exception $exception) {
            StripeProcessLogger::logError(
                'Payment retry initialization failed: ' . $exception->getMessage(),
                'StripePaymentRetryService',
                (int) $order->id_cart,
                (string) $paymentContextId
            );
            throw $exception;
        }
    }

    private function getPayload(Order $order, $paymentContextId)
    {
        return implode('|', [
            (int) $order->id,
            (int) $order->id_cart,
            (int) $order->id_customer,
            (string) $order->reference,
            (string) $paymentContextId,
        ]);
    }

    private function validateAddresses(Cart $cart, Customer $customer, Order $order)
    {
        foreach ([(int) $cart->id_address_delivery, (int) $cart->id_address_invoice] as $addressId) {
            if (!$addressId) {
                continue;
            }
            $address = new Address($addressId);
            if (!Validate::isLoadedObject($address)) {
                throw new Exception('Address ' . $addressId . ' not found');
            }
            if ((int) $address->id_customer !== (int) $customer->id) {
                throw new Exception('Address ' . $addressId . ' belongs to customer ' . (int) $address->id_customer . ', expected ' . (int) $customer->id);
            }
        }

        if ((int) $customer->id !== (int) $order->id_customer) {
            throw new Exception('Customer mismatch: context=' . (int) $customer->id . ' order=' . (int) $order->id_customer);
        }
    }

    private function copyCart(Cart $oldCart, Customer $customer)
    {
        $context = Context::getContext();
        $cartRules = $oldCart->getCartRules(CartRule::FILTER_ACTION_ALL, false) ?: [];
        $newCart = new Cart();
        foreach (['id_shop_group', 'id_shop', 'id_lang', 'id_currency', 'id_customer', 'id_address_delivery', 'id_address_invoice', 'id_carrier', 'recyclable', 'gift', 'gift_message', 'delivery_option'] as $field) {
            if (property_exists($oldCart, $field)) {
                $newCart->$field = $oldCart->$field;
            }
        }
        $newCart->id_customer = (int) $customer->id;
        $newCart->id_guest = (int) ($context->cookie->id_guest ?? $oldCart->id_guest);
        $newCart->secure_key = (string) $customer->secure_key;

        if (!$newCart->add()) {
            throw new Exception('Failed to save new cart to database');
        }

        try {
            $added = 0;
            $products = $oldCart->getProducts(true);
            foreach ($products as $line) {
                if (!empty($line['is_gift'])) {
                    continue;
                }
                $product = new Product((int) $line['id_product'], false, (int) $oldCart->id_lang, (int) $oldCart->id_shop);
                if (!Validate::isLoadedObject($product)) {
                    throw new Exception('Product ' . (int) $line['id_product'] . ' not found');
                }
                if (!(bool) $product->active) {
                    throw new Exception('Product ' . (int) $product->id . ' is no longer active');
                }
                $attributeId = (int) ($line['id_product_attribute'] ?? 0);
                if ($attributeId && !(int) Db::getInstance()->getValue(
                    'SELECT id_product_attribute FROM ' . _DB_PREFIX_ . 'product_attribute WHERE id_product_attribute = ' . $attributeId . ' AND id_product = ' . (int) $product->id
                )) {
                    throw new Exception('Attribute ' . $attributeId . ' not found for product ' . (int) $product->id);
                }
                if (!empty($line['id_customization'])) {
                    throw new Exception('Product ' . (int) $product->id . ' has customization which cannot be retried');
                }
                $result = $newCart->updateQty(
                    (int) $line['quantity'],
                    (int) $product->id,
                    $attributeId,
                    false,
                    'up',
                    (int) $newCart->id_address_delivery,
                    new Shop((int) $newCart->id_shop),
                    true,
                    false
                );
                if ($result === false || (int) $result < (int) $line['quantity']) {
                    throw new Exception('Could not add product ' . (int) $product->id . ' qty ' . (int) $line['quantity'] . ' to new cart (result=' . var_export($result, true) . ')');
                }
                ++$added;
            }

            if (!$added) {
                throw new Exception('No products could be added to the retry cart (original cart ' . (int) $oldCart->id . ' has ' . count($products) . ' lines)');
            }

            $context->cart = $newCart;
            foreach ($cartRules as $cartRule) {
                $cartRuleObject = new CartRule((int) $cartRule['id_cart_rule'], (int) $newCart->id_lang);
                if (!Validate::isLoadedObject($cartRuleObject)
                    || !$cartRuleObject->checkValidity($context, false, false)
                    || !$newCart->addCartRule((int) $cartRule['id_cart_rule'])
                ) {
                    StripeProcessLogger::logWarning(
                        'Cart rule ' . (int) $cartRule['id_cart_rule'] . ' could not be restored on retry cart',
                        'StripePaymentRetryService'
                    );
                    continue;
                }
            }

            $newCart->update();
        } catch (Exception $exception) {
            $newCart->delete();
            throw $exception;
        }

        return $newCart;
    }

    private function setContext(Cart $cart, Customer $customer)
    {
        $context = Context::getContext();
        $context->customer = $customer;
        $context->cart = $cart;
        $context->cookie->id_customer = (int) $customer->id;
        $context->cookie->is_guest = 1;
        $context->cookie->id_cart = (int) $cart->id;
        $context->cookie->secure_key = (string) $customer->secure_key;
        if (method_exists($context->cookie, 'write')) {
            $context->cookie->write();
        }
    }
}
