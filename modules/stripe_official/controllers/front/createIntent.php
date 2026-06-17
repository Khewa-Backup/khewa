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
use StripeOfficial\Classes\StripeProcessLogger;

if (!defined('_PS_VERSION_')) {
    exit;
}

class stripe_officialCreateIntentModuleFrontController extends ModuleFrontController
{
    /**
     * @var StripePaymentIntentService
     */
    private $stripePaymentIntentService;
    /**
     * @var PrestashopBuildOrderService
     */
    private $prestashopBuildOrderService;

    /**
     * @var PrestashopOrderService
     */
    private $prestashopOrderService;

    /**
     * @var PrestashopCartService
     */
    private $prestashopCartService;

    private $stripeAnonymize;

    /**
     * @param string|null $secretKey
     */
    public function __construct($secretKey = null)
    {
        parent::__construct();
        $secretKey = $secretKey ?: Stripe_official::getSecretKey();
        $this->stripePaymentIntentService = new StripePaymentIntentService($secretKey);
        $this->prestashopBuildOrderService = new PrestashopBuildOrderService($this->context, $this->module, $secretKey);
        $this->prestashopOrderService = new PrestashopOrderService($this->context, $this->module, $secretKey);
        $this->prestashopCartService = new PrestashopCartService();
        $this->stripeAnonymize = new StripeAnonymize();
    }

    /**
     * @throws PrestaShopException
     * @throws PrestaShopDatabaseException
     * @throws OrderException
     * @throws Exception
     */
    public function postProcess()
    {
        $values = @Tools::file_get_contents('php://input');
        $content = json_decode($values, true);
        $contentAnonymized = $this->stripeAnonymize->anonymize($content);
        $psCustomer = $this->context->customer;

        $productId = (int) ($content['productId'] ?? 0);
        $attributeId = (int) ($content['productAttributeId'] ?? 0);

        $this->validateProductAndCombination($productId, $attributeId, (int) $this->context->language->id, $content['pageName']);

        // Always choose cart server-side for express checkout
        // (Product-page express: create a new cart each time)
        if (!empty($content['pageName']) && $content['pageName'] === 'product') {
            $cart = $this->prestashopCartService->createPrestashopCart($psCustomer);
            $this->prestashopCartService->createPrestashopCartProduct($content, $cart, $attributeId);
        } else {
            // Non-product flows: use existing session cart if valid
            $cart = $this->context->cart;

            if (!Validate::isLoadedObject($cart) || (int) $cart->id_customer !== (int) $psCustomer->id) {
                $cart = $this->prestashopCartService->createPrestashopCart($psCustomer);
            }
        }
        $cartId = (int) $cart->id;

        $this->context->cart = $cart;
        $this->context->cart->update();

        StripeProcessLogger::logInfo('Content for Express Checkout => ' . json_encode($contentAnonymized), 'createIntent', $cartId);
        $expressParams = $content['event'] ?? null;

        $countryId = (int) Country::getByIso($expressParams['shippingAddress']['address']['country'], true);
        if (!$countryId) {
            StripeProcessLogger::logInfo('Shipping country unavailable - content: ' . json_encode($contentAnonymized), 'createIntent', $cartId);
            echo json_encode(['error' => true, 'message' => 'Shipping country unavailable']);
            exit;
        }

        $customerExist = $this->checkCustomerByEmail($expressParams['billingDetails']['email']);

        if ((!isset($psCustomer->id) || !$psCustomer->id) && $customerExist) {
            $psCustomer = $customerExist;
            $this->context->customer = $customerExist;
            $this->context->cart->id_customer = $psCustomer->id;
            $this->context->cart->update();
        }

        if (!isset($psCustomer->id) || !$psCustomer->id) {
            $psCustomer = CustomerModel::createPrestashopCustomer($expressParams);
            $psAddress = AddressModel::createPrestashopAddress($expressParams, $this->context, $psCustomer->id);
            $this->context->customer = $psCustomer;
            $this->context->cart->id_customer = $psCustomer->id;
            $this->context->cart->id_address_invoice = $psAddress->id;
            $this->context->cart->id_address_delivery = $psAddress->id;
            $this->context->cart->id_guest = Context::getContext()->cookie->id_guest;
            $this->context->cart->id_currency = Context::getContext()->cookie->id_currency;
            $this->context->cart->update();

            $psGuest = new Guest(Context::getContext()->cookie->id_guest);
            $psGuest->id_customer = $psCustomer->id;
            $psGuest->update();
        } else {
            AddressModel::deleteCalcShipAddresses($psCustomer->id);
            $psAddress = $this->checkCustomerShippingAddress($expressParams, $this->context);
        }

        $this->context->cookie->id_customer = $psCustomer->id;
        $this->context->cookie->customer_lastname = $psAddress->lastname;
        $this->context->cookie->customer_firstname = $psAddress->firstname;
        $this->context->cookie->check_cgv = 1;
        $this->context->cookie->is_guest = 1;
        $this->context->cookie->id_cart = $cart->id;
        $this->context->cart->id_address_invoice = $psAddress->id;
        $this->context->cart->id_address_delivery = $psAddress->id;
        $this->context->cart->id_carrier = $expressParams['shippingRate']['id'];
        $delivery_option[$psAddress->id] = $expressParams['shippingRate']['id'] . ',';
        $delivery_option = json_encode($delivery_option);
        $this->context->cart->update();
        $cart = $this->context->cart;
        $cart->update();

        $this->prestashopCartService->updatePrestashopCart($delivery_option, $cartId);

        /** Get fresh cart data and reinitialize cart context */
        $cart = new Cart($cartId);
        $this->context->cart = $cart;
        $this->context->cart->update();

        $amount = $this->getAmount($cart, $countryId, $expressParams['shippingRate']['id']);

        $this->prestashopCartService->updatePrestashopCartProduct($psAddress->id, $cartId);
        $contextModel = ProductContextModel::getFromExpressParams($expressParams, $amount, $this->context);
        $contextModelAnonymized = $this->stripeAnonymize->anonymize($contextModel);
        StripeProcessLogger::logInfo('getFromExpressParams => ' . json_encode($contextModelAnonymized), 'createIntent', $cartId);
        $separateAuthAndCapture = Configuration::get(Stripe_official::CATCHANDAUTHORIZE);
        $stripePaymentIntent = $this->stripePaymentIntentService->createPaymentIntent($contextModel, $separateAuthAndCapture);

        $newOrderFlow = !(int) Configuration::get(Stripe_official::ORDER_FLOW);
        StripeProcessLogger::logInfo('is new order flow => ' . json_encode($newOrderFlow), 'createIntent', $cartId);
        if ($newOrderFlow) {
            StripeProcessLogger::debugLog('Entering new order flow', 'createIntent', $cartId);
            $psStripePaymentIntent = new StripePaymentIntent();
            $psStripePaymentIntent->findByIdPaymentIntent($stripePaymentIntent->id);

            StripeProcessLogger::debugLog('Payment intent is found ' . $psStripePaymentIntent->id_payment_intent, $cartId);

            $cartContextModel = CartContextModel::getFromContext($this->context);
            $cartContextModelAnonymized = $this->stripeAnonymize->anonymize($cartContextModel);
            StripeProcessLogger::logInfo('cartContextModel => ' . json_encode($cartContextModelAnonymized), 'createIntent', $cartId);
            $orderModel = $this->prestashopBuildOrderService->buildAndCreatePrestashopOrder($psStripePaymentIntent, $stripePaymentIntent, $cartContextModel);
            StripeProcessLogger::debugLog('Order model is build ' . json_encode($orderModel), $cartId);
            $this->prestashopOrderService->createPsStripePayment($stripePaymentIntent, $orderModel);
            $this->stripePaymentIntentService->updateStripePaymentIntent($stripePaymentIntent, $orderModel->orderReference);
        }

        $redirectUrl = $this->context->link->getModuleLink(
            'stripe_official',
            'orderConfirmationReturn',
            [
                'cartId' => $cartId,
                'key' => PrestashopCartService::getCartUniqueKey($cartId),
            ],
            true
        );

        echo json_encode(['intent' => $stripePaymentIntent, 'stripe_express_return_url' => $redirectUrl]);

        exit;
    }

    private function getAmount($cart, $countryId, $selectedCarrierId)
    {
        $currency = new Currency($cart->id_currency);
        $precision = 2; // default precision
        if (isset($currency->precision)) {
            $precision = $currency->precision;
        }
        $idZone = Country::getIdZone($countryId);
        $isoCode = $currency->iso_code;
        $discountDetails = $this->prestashopCartService->checkDiscountCouponForCart($cart);
        $carriers = Carrier::getCarriersForOrder($idZone, null, $cart);

        $carrierPrice = 0;
        if ($carriers) {
            foreach ($carriers as $carrier) {
                if ($selectedCarrierId == $carrier['id_carrier']) {
                    StripeProcessLogger::logInfo('Carrier found with: ' . json_encode(['id' => $carrier['id_carrier'], 'price' => $carrier['price']]), 'createIntent', $cart->id);
                    $carrierPrice = Stripe_official::isZeroDecimalCurrency($isoCode) ?
                        $carrier['price'] :
                        $carrier['price'] * 100;
                    break;
                }
            }
        }

        if ($discountDetails->free_shipping) {
            $carrierPrice = 0;
        }

        $amount = $cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING) * pow(10, $precision);

        if (in_array(Tools::strtolower($isoCode), ['ugx'])) {
            $amount = $amount * 100;
        }
        $amount = $amount + $carrierPrice;

        StripeProcessLogger::logInfo('Amount is calculated: ' . json_encode([
            'amount' => $amount,
            'carrierPrice' => $carrierPrice,
        ]), 'createIntent', $cart->id);

        return $amount;
    }

    public static function checkCustomerShippingAddress($expressParams, $context)
    {
        $countryId = (int) Country::getByIso($expressParams['shippingAddress']['address']['country'], true);
        $address = AddressModel::getExistingAddress((int) $context->customer->id, [
            'id_country' => (int) $countryId,
            'postcode' => $expressParams['shippingAddress']['address']['postal_code'],
            'city' => $expressParams['shippingAddress']['address']['city'],
            'address1' => $expressParams['shippingAddress']['address']['line1'],
        ]);
        if (empty($address->id)) {
            $address = AddressModel::createPrestashopAddress($expressParams, Context::getContext(), $context->customer->id);
        }

        return $address;
    }

    /**
     * @throws Exception
     */
    private function validateProductAndCombination(int $productId, int $productAttributeId, int $langId, string $pageName): void
    {
        // Product valid
        $product = new Product($productId, false, $langId);
        if ((!Validate::isLoadedObject($product) || !$product->active) && $pageName === 'product') {
            throw new Exception('Product not available');
        }

        if ($productAttributeId > 0) {
            // Cross-version DB check: combination belongs to product
            $sql = 'SELECT 1
                FROM ' . _DB_PREFIX_ . 'product_attribute
                WHERE id_product_attribute = ' . (int) $productAttributeId . '
                  AND id_product = ' . (int) $productId;
            if (!(int) Db::getInstance()->getValue($sql)) {
                throw new Exception('Invalid product combination');
            }
        }
    }

    /**
     * Load an existing customer by email, multistore-safe.
     * - Prefers non-guest if both guest & real customer exist.
     * - Returns null if no customer found.
     *
     * @param string $email
     *
     * @return Customer|null
     */
    private function checkCustomerByEmail(string $email): ?Customer
    {
        $email = trim(Tools::strtolower($email));

        if ($email === '') {
            return null;
        }

        // If multistore is enabled AND customer_shop exists, use it
        if (Shop::isFeatureActive()) {
            // Some installations still don't have customer_shop, so we must be defensive
            $tableExists = Db::getInstance()->getValue(
                'SHOW TABLES LIKE "' . _DB_PREFIX_ . 'customer_shop"'
            );

            if ($tableExists) {
                $row = Db::getInstance()->getRow(
                    'SELECT c.id_customer
                 FROM `' . _DB_PREFIX_ . 'customer` c
                 INNER JOIN `' . _DB_PREFIX_ . 'customer_shop` cs
                   ON cs.id_customer = c.id_customer
                 WHERE c.email = "' . pSQL($email) . '"
                   AND cs.id_shop = ' . (int) Context::getContext()->shop->id . '
                 ORDER BY c.is_guest ASC'
                );

                if (!empty($row['id_customer'])) {
                    $customer = new Customer((int) $row['id_customer']);

                    return Validate::isLoadedObject($customer) ? $customer : null;
                }
            }
        }

        // Fallback: non-multistore or customer_shop missing
        $row = Db::getInstance()->getRow(
            'SELECT id_customer
         FROM `' . _DB_PREFIX_ . 'customer`
         WHERE email = "' . pSQL($email) . '"
         ORDER BY is_guest ASC'
        );

        if (empty($row['id_customer'])) {
            return null;
        }

        $customer = new Customer((int) $row['id_customer']);

        return Validate::isLoadedObject($customer) ? $customer : null;
    }
}
