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

class stripe_officialCalculateShippingModuleFrontController extends ModuleFrontController
{
    /**
     * @var PrestashopCartService
     */
    private $prestashopCartService;

    private $stripeAnonymize;

    public function __construct()
    {
        parent::__construct();
        $this->prestashopCartService = new PrestashopCartService();
        $this->stripeAnonymize = new StripeAnonymize();
    }

    /**
     * @throws OrderException
     * @throws PrestaShopException
     */
    public function postProcess()
    {
        header('Content-Type: application/json; charset=utf-8');

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($contentType, 'application/json') === false) {
            http_response_code(400);
            echo json_encode([
                'error' => true,
                'message' => 'Invalid Content-Type. application/json required',
            ]);
            exit;
        }

        $values = Tools::file_get_contents('php://input');
        $content = json_decode($values, true);

        if (!is_array($content)) {
            http_response_code(400);
            echo json_encode([
                'error' => true,
                'message' => 'Invalid JSON payload',
            ]);
            exit;
        }

        $contentAnonymized = $this->stripeAnonymize->anonymize($content);

        // 2) Basic required fields
        $shippingAddress = $content['shippingAddress'] ?? null;
        $idProductAttribute = isset($content['idProductAttribute']) ? (int) $content['idProductAttribute'] : 0;

        if (!is_array($shippingAddress) || empty($shippingAddress['country'])) {
            http_response_code(400);
            echo json_encode(['error' => true, 'message' => 'Missing country from shipping address']);
            exit;
        }

        $expressCheckoutType = $content['expressCheckoutType'] ?? 'cart';
        if (!in_array($expressCheckoutType, ['product', 'cart'], true)) {
            http_response_code(400);
            echo json_encode(['error' => true, 'message' => 'Invalid expressCheckoutType']);
            exit;
        }

        $countryId = (int) Country::getByIso($shippingAddress['country'], true);
        if (!$countryId) {
            StripeProcessLogger::logError(
                'CalculateShipping - Shipping country unavailable - content => ' . json_encode($contentAnonymized),
                'calculateShipping',
                Context::getContext()->cart->id
            );
            http_response_code(400);
            echo json_encode(['error' => true, 'message' => 'Shipping country unavailable']);
            exit;
        }

        $this->context->country = new Country($countryId); // set the contry in context. Using this prestashop will canculate the corect taxes automatically

        // Guest or customer
        $customer = ($this->context->customer && Validate::isLoadedObject($this->context->customer))
            ? $this->context->customer
            : null;

        $cart = null;

        if ($expressCheckoutType === 'cart') {
            if (!Validate::isLoadedObject($this->context->cart) || !(int) $this->context->cart->id) {
                echo json_encode(['error' => true, 'message' => 'Invalid cart, please refresh and try again']);
                exit;
            }
            $cart = new Cart((int) $this->context->cart->id);
        } else {
            $cart = $this->prestashopCartService->createPrestashopCart($customer);

            $idProductAttribute = null;
            if (isset($content['idProductAttribute'])) {
                if (!is_numeric($content['idProductAttribute'])) {
                    StripeProcessLogger::logError(
                        'CalculateShipping - Invalid product attribute id - content => ' . json_encode($contentAnonymized),
                        'calculateShipping',
                        (int) $cart->id
                    );
                    http_response_code(400);
                    echo json_encode(['error' => true, 'message' => 'Invalid product attribute']);
                    exit;
                }
                $idProductAttribute = (int) $content['idProductAttribute'];
                if ($idProductAttribute <= 0) {
                    $idProductAttribute = null;
                }
            }

            $this->prestashopCartService->createPrestashopCartProduct($content, $cart, $idProductAttribute);
        }

        if (!$cart || !Validate::isLoadedObject($cart)) {
            StripeProcessLogger::logError(
                'CalculateShipping - Invalid cart - content => ' . json_encode($contentAnonymized),
                'calculateShipping',
                (int) ($this->context->cart->id ?? 0)
            );
            echo json_encode(['error' => true, 'message' => 'Invalid cart, please refresh and try again']);
            exit;
        }

        if (!isset($customer->id) || !$customer->id) {
            $addressId = $this->createAddressFromShipping($shippingAddress, $customer);
        } else {
            AddressModel::deleteCalcShipAddresses($customer->id);
            $addressId = $this->checkCustomerExpressShippingAddress($shippingAddress, $customer, $countryId);
        }

        StripeProcessLogger::logInfo("Address ID used for shipping calculation: $addressId", 'calculateShipping', (int) $cart->id);

        $cart->id_address_delivery = $addressId;
        $cart->id_address_invoice = $addressId;
        $cart->update();
        $this->prestashopCartService->refreshCart($cart);

        // IMPORTANT: ensure cart lines also have the address, otherwise restrictions are not applied
        $this->prestashopCartService->updatePrestashopCartProduct($addressId, (int) $cart->id);

        // Reload cart to avoid stale cached shipping computations
        $cart = new Cart((int) $cart->id);
        $this->prestashopCartService->refreshCart($cart);

        StripeProcessLogger::logInfo(
            'CalculateShipping - content => ' . json_encode($contentAnonymized),
            'calculateShipping',
            (int) $cart->id
        );

        // 6) Currency precision + cart amount (without shipping) for Elements
        $precision = (int) ($this->context->currency->precision ?? 2);
        $cartAmountWithoutShippingMinor = (int) round(
            (float) $cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING) * pow(10, $precision)
        );

        // 9) Optional: discount details (keep if you already use it elsewhere)
        // NOTE: do not rely on Carrier::getCarriersForOrder() here for selection/price.
        $discountDetails = $this->prestashopCartService->checkDiscountCouponForCart($cart);
        StripeProcessLogger::logInfo(
            'CalculateShipping - discountDetails=' . json_encode($discountDetails->toArray()),
            'calculateShipping',
            (int) $cart->id
        );

        // 7) Build shipping options using deliveryOptionList (source of truth: restrictions, country, carrier rules)
        $shippingOptions = $this->buildShippingOptionsFromDeliveryOptionList($cart, $precision, $discountDetails);

        // If empty: no shipping possible for that address/cart -> block express at JS level
        if (empty($shippingOptions)) {
            echo json_encode([
                'carriers' => [],
                'cartId' => (int) $cart->id,
                'precision' => $precision,
                'updatedCartAmount' => Tools::ps_round($cartAmountWithoutShippingMinor, 0), // already minor units
                'expressAllowed' => false,
                'expressBlockReason' => 'no_shipping_available',
            ]);
            exit;
        }

        // 8) Default carrier id (informational only; don't force $cart->id_carrier here)
        $defaultCarrierId = (int) Configuration::get('PS_CARRIER_DEFAULT');

        echo json_encode([
            'carriers' => $shippingOptions,
            'cartId' => (int) $cart->id,
            'discountDetails' => $discountDetails->toArray(),
            'updatedCartAmount' => Tools::ps_round($cartAmountWithoutShippingMinor, 0),
            'precision' => $precision,
            'defaultCarrierId' => $defaultCarrierId,
            'expressAllowed' => true,
            'productAttributeId' => $idProductAttribute ?? null,
        ]);

        exit;
    }

    /**
     * Build shipping options from Cart::getDeliveryOptionList() (PS source of truth).
     *
     * Returns:
     *   - option_key (string)   : delivery option key (e.g. "3," or "3,2,")
     *   - price (float)         : tax incl, major units (e.g. 5.00)
     *   - amount (int)          : minor units (e.g. 500) ready for Stripe Elements.update
     *   - name (string)         : carrier name (combined if multiple carriers)
     *   - id_carrier (int|array): carrier id (or array of ids for combo)
     *   - is_free (bool)
     */
    private function buildShippingOptionsFromDeliveryOptionList(Cart $cart, int $precision, CartDiscountDetailsDto $discountDetails): array
    {
        $idAddress = (int) $cart->id_address_delivery;
        if ($idAddress <= 0) {
            return [];
        }

        $deliveryOptionList = $cart->getDeliveryOptionList(null, true); // tax incl
        $optionsForAddress = $deliveryOptionList[$idAddress] ?? [];

        StripeProcessLogger::logInfo(
            'CalculateShipping - deliveryOptionList keys=' . json_encode(array_map('array_keys', $deliveryOptionList)),
            'calculateShipping',
            (int) $cart->id
        );

        if (empty($optionsForAddress)) {
            return [];
        }

        $mult = (int) pow(10, $precision);
        $shippingOptions = [];

        foreach ($optionsForAddress as $optionKey => $opt) {
            // optionKey can be "3," or "3,2," etc.
            $carrierIds = array_values(array_filter(explode(',', rtrim((string) $optionKey, ',')), 'strlen'));
            $carrierIds = array_map('intval', $carrierIds);

            foreach ($carrierIds as $carrierId) {
                $discount = $this->prestashopCartService->simulateDiscountForCarrier($cart, $carrierId);
                if ($discount->free_shipping) {
                    $opt['is_free'] = true;
                }
            }

            // Price with tax (major units)
            $priceWithTax = 0.0;
            if (isset($opt['total_price_with_tax'])) {
                $priceWithTax = (float) $opt['total_price_with_tax'];
            } elseif (isset($opt['price_with_tax'])) {
                $priceWithTax = (float) $opt['price_with_tax'];
            } elseif (isset($opt['price'])) {
                $priceWithTax = (float) $opt['price'];
            }

            // Use PS is_free if present; also consider free-shipping rule
            $isFree = !empty($opt['is_free']) || $priceWithTax <= 0.0 || $discountDetails->free_shipping;

            if ($isFree) {
                $priceWithTax = 0.0;
            }

            // Build name (combined if multiple carriers)
            $names = [];
            foreach ($carrierIds as $cid) {
                if (isset($opt['carrier_list'][$cid][0]['instance']) && is_object($opt['carrier_list'][$cid][0]['instance'])) {
                    $names[] = (string) $opt['carrier_list'][$cid][0]['instance']->name;
                } else {
                    $c = new Carrier($cid, (int) $this->context->language->id);
                    if (Validate::isLoadedObject($c)) {
                        $names[] = $c->name;
                    }
                }
            }
            $name = !empty($names) ? implode(' + ', array_unique($names)) : 'Shipping';

            $priceRounded = (float) Tools::ps_round($priceWithTax, $precision);
            $amountMinor = (int) round($priceRounded * $mult);

            $shippingOptions[] = [
                'option_key' => (string) $optionKey,
                'price' => $priceRounded,        // major units
                'amount' => $amountMinor,         // minor units for JS/Stripe
                'name' => $name,
                'id_carrier' => (count($carrierIds) === 1) ? (int) $carrierIds[0] : $carrierIds,
                'is_free' => (bool) $isFree,
            ];
        }

        usort($shippingOptions, function ($a, $b) {
            if ($a['price'] === $b['price']) {
                return strcmp($a['name'], $b['name']);
            }

            return ($a['price'] < $b['price']) ? -1 : 1;
        });

        return $shippingOptions;
    }

    /**
     * @throws PrestaShopException
     */
    public function checkCustomerExpressShippingAddress($shippingAddress, $customer, $countryId): int
    {
        $postcode = (string) ($shippingAddress['postal_code'] ?? '');
        $city = trim((string) ($shippingAddress['city'] ?? ''));
        if ($city === '') {
            $city = 'N/A';
        }

        $address = AddressModel::getExistingAddress((int) $customer->id, [
            'id_country' => (int) $countryId,
            'postcode' => $postcode,
            'city' => $city,
        ]);

        if ($address->id > 0) {
            return $address->id;
        }

        return $this->createAddressFromShipping($shippingAddress, $customer);
    }

    /**
     * Create a PrestaShop Address from wallet shipping data.
     * Works for guests (no customer) and logged-in customers.
     *
     * @param array $shippingAddress
     * @param Customer|null $customer
     *
     * @return int id_address
     *
     * @throws PrestaShopException
     */
    private function createAddressFromShipping(array $shippingAddress, ?Customer $customer = null): int
    {
        $customerId = ($customer && Validate::isLoadedObject($customer)) ? (int) $customer->id : 0;
        $city = (string) ($shippingAddress['city'] ?? '');
        $city = trim($city) === '' ? 'N/A' : $city;
        $stateValue = $shippingAddress['state'] ?? '';

        $address = new Address();
        $address->id_customer = $customerId;
        $address->id_country = (int) Country::getByIso($shippingAddress['country'], true);
        $address->id_state = Country::containsStates($address->id_country) ? (State::getIdByIso($stateValue) ?: State::getIdByName($stateValue) ?: 0) : 0;
        $address->firstname = pSQL('Customer');
        $address->lastname = pSQL('Guest');
        $address->address1 = pSQL('N/A');
        $address->postcode = pSQL($shippingAddress['postal_code'] ?? '');
        $address->city = pSQL($city);

        $alias = AddressModel::CALC_SHIP_ADDRESS_PREFIX . ' ' . substr(sha1(uniqid('', true)), 0, 10);
        $address->alias = pSQL(Tools::substr($alias, 0, 32));

        $errors = $address->validateController();
        if (!empty($errors)) {
            throw new PrestaShopException('Invalid address from wallet: ' . implode(' | ', $errors));
        }

        if (!$address->add()) {
            throw new PrestaShopException('Failed to create address from wallet shipping address');
        }

        return (int) $address->id;
    }
}
