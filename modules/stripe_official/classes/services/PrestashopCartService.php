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

use PrestaShop\PrestaShop\Core\Domain\Order\Exception\OrderException;
use StripeOfficial\Classes\dtos\CartDiscountDetailsDto;
use StripeOfficial\Classes\StripeProcessLogger;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PrestashopCartService
{
    public function checkDiscountCouponForCart($cart): CartDiscountDetailsDto
    {
        $cartRules = $cart->getCartRules();
        $discountDetails = new CartDiscountDetailsDto();

        foreach ($cartRules as $cartRule) {
            // free shipping
            if ($cartRule['free_shipping']) {
                $discountDetails->free_shipping = true;
            }
            // percentage discount
            if ($cartRule['reduction_percent']) {
                $discountDetails->percentage_discount += $cartRule['reduction_percent'];
            }
            // fixed discount
            if ($cartRule['reduction_amount']) {
                $discountDetails->fixed_discount += $cartRule['reduction_amount'];
            }
        }

        // total discount
        $discountDetails->total_discount = $discountDetails->percentage_discount + $discountDetails->fixed_discount;

        StripeProcessLogger::logInfo('Discount details => ' . json_encode($discountDetails), 'PrestashopCartService', $cart->id);

        return $discountDetails;
    }

    public static function createPrestashopCart($psCustomer)
    {
        $context = Context::getContext();

        $psCart = new Cart();

        // Guest or logged-in customer
        if ($psCustomer instanceof Customer && Validate::isLoadedObject($psCustomer) && (int) $psCustomer->id > 0) {
            $psCart->id_customer = (int) $psCustomer->id;
            $psCart->secure_key = (string) $psCustomer->secure_key;
        } else {
            $psCart->id_customer = 0;

            // secure_key is required later by validateOrder checks
            // Prefer cookie secure_key if present, otherwise generate a stable random one
            $psCart->secure_key = !empty($context->cookie->secure_key)
                ? (string) $context->cookie->secure_key
                : bin2hex(random_bytes(16));
        }

        // Keep existing behavior, but guard missing cookie fields
        $psCart->id_guest = (int) ($context->cookie->id_guest ?? 0);
        $psCart->id_currency = (int) ($context->cookie->id_currency ?? (int) $context->currency->id ?? (int) Configuration::get('PS_CURRENCY_DEFAULT'));
        $psCart->id_shop_group = (int) ($context->shop->id_shop_group ?? 0);
        $psCart->id_shop = (int) ($context->shop->id ?? 0);
        $psCart->id_lang = (int) ($context->cookie->id_lang ?? (int) $context->language->id ?? (int) Configuration::get('PS_LANG_DEFAULT'));

        $psCart->recyclable = false;
        $psCart->gift = false;

        if (!$psCart->add()) {
            throw new PrestaShopException('Unable to create cart');
        }

        // Ensure PS uses this cart in the same session
        $context->cart = $psCart;
        $context->cookie->id_cart = (int) $psCart->id;

        return $psCart;
    }

    public function createPrestashopCartProduct($expressParams, $cart, $idProductAttribute)
    {
        $customizationId = Customization::getOrderedCustomizations((int) $cart->id);

        $sql = new DbQuery();
        $sql->select('count(*)');
        $sql->from('cart_product', 'cp');
        $sql->where('id_cart = ' . (int) $cart->id);
        $result = Db::getInstance()->getValue($sql);

        if ($result == 0) {
            $result = $cart->updateQty(
                (int) $expressParams['productQuantity'] ?? '',
                (int) $expressParams['productId'],
                (int) $idProductAttribute,
                $customizationId,
                'up',
                $cart->id_address_delivery,
                new Shop($cart->id_shop),
                true,
                true
            );
            if (!$result) {
                throw new OrderException(sprintf('Product with id "%s" is out of stock.', $expressParams['productId']));
            }

            StripeProcessLogger::logInfo('Create Prestashop Cart Product => ' . json_encode($result), 'PrestashopCartService', $cart->id);
        }
    }

    public function updatePrestashopCartProduct($addressDeliveryId, $cartId)
    {
        $sql = 'UPDATE `' . _DB_PREFIX_ . 'cart_product`
        SET `id_address_delivery` = ' . (int) $addressDeliveryId . '
        WHERE  `id_cart` = ' . (int) $cartId;

        StripeProcessLogger::logInfo('Update Prestashop Cart Product => ' . $sql, 'createIntent', $cartId);
        Db::getInstance()->execute($sql);
    }

    public function updatePrestashopCart($delivery_option, $cartId)
    {
        $sql = 'UPDATE ' . _DB_PREFIX_ . 'cart
        SET delivery_option = \'' . $delivery_option . '\'
        WHERE id_cart = ' . (int) $cartId;

        Db::getInstance()->execute($sql);
    }

    public static function getCartUniqueKey($cartId)
    {
        $key = self::getOriginalKeyFromCartId($cartId);

        return hash('sha256', $key);
    }

    public static function validateCartUniqueKey($cartId, $key)
    {
        $originalKey = self::getOriginalKeyFromCartId($cartId);

        return hash('sha256', $originalKey) === $key;
    }

    protected static function getOriginalKeyFromCartId($cartId)
    {
        $idempotencyKey = StripeIdempotencyKey::getOrCreateIdempotencyKey($cartId);
        if (!$idempotencyKey->idempotency_key) {
            $cart = new Cart($cartId);

            return $cart->secure_key;
        }

        return $idempotencyKey->idempotency_key;
    }

    public function refreshCart(Cart $cart): void
    {
        $context = Context::getContext();
        $context->cart = $cart;

        // clear invalid auto rules, then add newly valid ones
        CartRule::autoRemoveFromCart($context);
        CartRule::autoAddToCart($context);

        // refresh internal state / totals
        $cart->getCartRules();
        $cart->update();
    }

    public function simulateDiscountForCarrier($cart, $idCarrier): CartDiscountDetailsDto
    {
        // clone current cart
        $tmpCart = clone $cart;

        // update carrier
        $tmpCart->id_carrier = $idCarrier;
        $tmpCart->update();
        $this->refreshCart($tmpCart);

        if (method_exists($tmpCart, 'resetStaticCache')) {
            $tmpCart->resetStaticCache();
        }

        // calculate discount details for the cart with the new carrier
        $discount = new CartDiscountDetailsDto();
        try {
            $discount = $this->checkDiscountCouponForCart($tmpCart);
        } finally {
            // revert to original
            $cart->update();
            $this->refreshCart($cart);
        }

        return $discount;
    }
}
