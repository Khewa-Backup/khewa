<?php
/**
 * PrestaChamps
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Commercial License
 * you can't distribute, modify or sell this code
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file
 * If you need help please contact leo@prestachamps.com
 *
 * @author    Mailchimp
 * @copyright Mailchimp
 * @license   commercial
 */

namespace PrestaChamps\MailchimpPro\Helpers;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Picks which Address row to use when more than one is associated with a
 * Customer. PrestaShop core has NO `id_default_address` column on Customer,
 * so any "default address" notion has to be derived.
 *
 * Strategy (first non-null wins):
 *
 *   1. MOST RECENTLY UPDATED ADDRESS (date_upd DESC, active + non-deleted).
 *      Used by pixel identify, audience sync (ListMemberFormatter), and
 *      ecommerce address sync (AddressFormatter). All three must pick the
 *      SAME row — Mailchimp's PHONE_SHA256 matching compares the pixel
 *      hash against the hash of the stored merge_fields.PHONE; any drift
 *      between surfaces breaks the match.
 *
 *   2. CART DELIVERY ADDRESS — last-resort fallback for the narrow case
 *      where the cart references an address the customer is not otherwise
 *      linked to (e.g. guest mid-OPC checkout, or a custom theme injecting
 *      an address bound to a different id_customer).
 *
 *   3. NULL — caller must handle (skip phone identify, skip PHONE merge).
 *
 * Why NOT `Customer::getAddresses() + reset()` (PS core's obvious shortcut):
 * that returns id_address ASC (oldest first), giving the customer's first-
 * ever-registered address — goes stale whenever they move house, switch
 * carriers, or register a fresh business address.
 */
class CustomerAddressSelector
{
    /**
     * @param mixed $customer \Customer-like object (must expose ->id)
     * @param mixed|null $cart \Cart for the path-2 fallback (optional)
     * @param int|null $idLang defaults to PS_LANG_DEFAULT
     *
     * @return \Address|null
     */
    public static function pickPrimary($customer, $cart = null, $idLang = null)
    {
        if (!is_object($customer) || empty($customer->id)) {
            return null;
        }
        $customerId = (int) $customer->id;
        if (null === $idLang) {
            $idLang = (int) \Configuration::get('PS_LANG_DEFAULT');
        }

        // Per-request cache mirrors Address::getFirstCustomerAddressId in PS
        // core — pixel-identify + audience-sync + ecommerce-sync can all
        // hit this for the same customer in one request.
        // _PS_USE_SQL_SLAVE_ routes to a read replica when master/slave DB
        // is configured (no-op false on single-DB installs).
        // NOTE: no trailing `LIMIT 1` — Db::getRow() auto-appends one and a
        // second LIMIT is a MariaDB syntax error.
        $cacheId = 'PrestaChamps_MailchimpPro_pickPrimary_recentAddressId_' . $customerId;
        if (\Cache::isStored($cacheId)) {
            $addressId = (int) \Cache::retrieve($cacheId);
        } else {
            $sql = 'SELECT id_address
                    FROM `' . _DB_PREFIX_ . 'address`
                    WHERE id_customer = ' . $customerId . '
                      AND active = 1
                      AND deleted = 0
                    ORDER BY date_upd DESC, id_address DESC';
            $row = \Db::getInstance(\_PS_USE_SQL_SLAVE_)->getRow($sql);
            $addressId = !empty($row['id_address']) ? (int) $row['id_address'] : 0;
            \Cache::store($cacheId, $addressId);
        }

        if ($addressId > 0) {
            $address = new \Address($addressId, $idLang);
            if (\Validate::isLoadedObject($address)) {
                return $address;
            }
        }

        // Path 2 — re-verify that the cart's delivery address still belongs
        // to this customer and isn't deleted before accepting it (guards
        // against tampered cart state or stale id_address_delivery).
        if (is_object($cart) && !empty($cart->id_address_delivery)) {
            $addressId = (int) $cart->id_address_delivery;
            if ($addressId > 0) {
                $address = new \Address($addressId, $idLang);
                if (\Validate::isLoadedObject($address)
                    && (int) $address->id_customer === $customerId
                    && !$address->deleted
                ) {
                    return $address;
                }
            }
        }

        return null;
    }
}
