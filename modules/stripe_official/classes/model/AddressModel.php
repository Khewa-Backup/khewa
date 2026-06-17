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

class AddressModel
{
    public $city;
    public $countryIso;
    public $street;
    public $zipCode;
    public $state;

    public const CALC_SHIP_ADDRESS_PREFIX = 'PS-Stripe';

    public function __construct($city, $countryIso, $street, $zipCode, $state)
    {
        $this->city = $city ?: null;
        $this->countryIso = $countryIso ?: null;
        $this->street = $street ?: null;
        $this->zipCode = $zipCode ?: null;
        $this->state = $state ?: null;
    }

    public static function getFromContext($context): self
    {
        $psAddress = new Address($context->cart->id_address_delivery);
        $psState = new State($psAddress->id_state);
        $psCountryIso = Country::getIsoById($psAddress->id_country);

        $city = $psAddress->city ?: null;
        $countryIso = $psCountryIso ?: null;
        $street = $psAddress->address1 ?: null;
        $zipCode = $psAddress->postcode ?: null;
        $state = $psState->iso_code ?: null;

        return new self($city, $countryIso, $street, $zipCode, $state);
    }

    public static function getFromExpressParams($expressParams)
    {
        $shippingAddress = $expressParams['shippingAddress'];
        $city = $shippingAddress['address']['city'] ?: null;
        $countryIso = $shippingAddress['address']['country'] ?: null;
        $street = $shippingAddress['address']['line1'] ?: null;
        $street2 = $shippingAddress['address']['line2'] ?: null;
        $street = $street2 ? $street . ' ' . $street2 : $street;
        $zipCode = $shippingAddress['address']['postal_code'] ?: null;
        $state = $shippingAddress['address']['state'] ?: null;

        return new self($city, $countryIso, $street, $zipCode, $state);
    }

    public function __serialize()
    {
        return [
            'city' => $this->city,
            'country' => $this->countryIso,
            'line1' => $this->street,
            'postal_code' => $this->zipCode,
            'state' => $this->state,
        ];
    }

    /**
     * Find an existing customer address by matching provided address fields.
     *
     * Example keys: id_country, postcode, city, id_state, address1.
     */
    public static function getExistingAddress($customerId, array $addressFields): Address
    {
        $customerId = (int) $customerId;
        if ($customerId <= 0 || empty($addressFields)) {
            return new Address();
        }

        $allowedFields = [
            'id_country',
            'postcode',
            'city',
            'id_state',
            'address1',
            'firstname',
            'lastname',
        ];

        $conditions = [
            'id_customer = ' . $customerId,
            'deleted = 0',
        ];

        foreach ($addressFields as $field => $value) {
            if (!in_array($field, $allowedFields, true)) {
                continue;
            }

            if (in_array($field, ['id_country', 'id_state'], true)) {
                $conditions[] = $field . ' = ' . (int) $value;
                continue;
            }

            $conditions[] = $field . ' = "' . pSQL((string) $value) . '"';
        }

        $query = 'SELECT id_address FROM ' . _DB_PREFIX_ . 'address WHERE '
            . implode(' AND ', $conditions)
            . ' ORDER BY id_address DESC';

        $addressId = Db::getInstance()->getValue($query);

        return new Address((int) $addressId);
    }

    public static function deleteCalcShipAddresses($customerId)
    {
        $query = 'UPDATE ' . _DB_PREFIX_ . 'address SET deleted=1 WHERE alias LIKE "' . self::CALC_SHIP_ADDRESS_PREFIX . '%" AND id_customer = ' . (int) $customerId;
        Db::getInstance()->execute($query);
    }

    /**
     * @throws PrestaShopException
     */
    public static function createPrestashopAddress($expressParams, $context, $customerId)
    {
        $customerModel = CustomerModel::getFromExpressParams($expressParams, $context);

        // split full name into first and last name
        $fullName = trim($customerModel->name);
        // get local part
        $localPart = strstr($fullName, '@', true) ?: $fullName;

        // replace separators with spaces
        $localPart = str_replace(['.', '_', '-'], ' ', $localPart);

        // split into name parts
        $parts = preg_split('/\s+/', trim($localPart), 2);

        $firstname = $parts[0] ?? 'Customer';
        $lastname = $parts[1] ?? 'Guest';

        $stateId = 0;
        $countryId = (int) Country::getByIso($customerModel->address->countryIso, true);

        if (Country::containsStates($countryId)) {
            $stateId = State::getIdByIso($customerModel->address->state ?? '')
                ?: State::getIdByName($customerModel->address->state ?? '') ?: 0;
        }
        if (empty($stateId)) {
            StripeProcessLogger::logInfo(
                'No state was found in Prestashop for ISO: ' . $customerModel->address->state . ' in country: ' . $customerModel->address->countryIso,
                'AddressModel'
            );
        }

        $matchingFields = [
            'lastname' => $lastname,
            'firstname' => $firstname,
            'address1' => $customerModel->address->street,
            'postcode' => $customerModel->address->zipCode,
            'city' => $customerModel->address->city,
            'id_country' => $countryId,
        ];
        if ($stateId) {
            $matchingFields['id_state'] = $stateId;
        }

        $existingAddress = self::getExistingAddress($customerId, $matchingFields);
        if ($existingAddress->id) {
            return $existingAddress;
        }

        $psAddress = new Address();
        $psAddress->id_country = $countryId;
        $psAddress->id_customer = $customerId;
        // Unique alias per customer
        $psAddress->alias = 'My Address ' . date('YmdHis');
        $psAddress->lastname = $lastname;
        $psAddress->firstname = $firstname;
        $psAddress->address1 = $customerModel->address->street;
        $psAddress->postcode = $customerModel->address->zipCode;
        $psAddress->city = $customerModel->address->city;
        $psAddress->phone = $expressParams['billingDetails']['phone'] ?? $expressParams['shippingAddress']['phone'] ?? null;
        if ($stateId) {
            $psAddress->id_state = $stateId;
        }
        $psAddress->save();

        return $psAddress;
    }
}
