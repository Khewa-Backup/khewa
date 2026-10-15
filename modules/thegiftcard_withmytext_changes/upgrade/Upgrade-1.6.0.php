<?php
/**
* 2017 - Keyrnel SARL
*
* NOTICE OF LICENSE
*
* The source code of this module is under a commercial license.
* Each license is unique and can be installed and used on only one shop.
* Any reproduction or representation total or partial of the module, one or more of its components,
* by any means whatsoever, without express permission from us is prohibited.
* If you have not received this module from us, thank you for contacting us.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to newer
* versions in the future.
*
* @author    Keyrnel
* @copyright 2017 - Keyrnel SARL
* @license   commercial
* International Registered Trademark & Property of Keyrnel SARL
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_6_0($object)
{
    $id_default_currency = (int)Configuration::get('PS_CURRENCY_DEFAULT');
    $shops = Shop::getShops(true, null, true);

    foreach ($shops as $shop) {
        $id_product = Configuration::get('GIFTCARD_PROD', null, Shop::getGroupFromShop($shop), $shop);
        if ($id_product) {
            break;
        }
    }

    $product = new Product((int)$id_product);
    if (!Validate::isLoadedObject($product)) {
        return false;
    }

    $id_default_shop = $product->id_shop_default;
    $id_default_shop_group = (int)Shop::getGroupFromShop($id_default_shop);

    $customization_ids = array();
    foreach (GiftCardModel::$customizations as $customization) {
        $customization_ids[] = Configuration::get('GIFTCARD_CUST_'.Tools::strtoupper($customization['name']), null, $id_default_shop_group, $id_default_shop);
    }

    $res = Db::getInstance()->getRow(
        'SELECT cfl.`id_customization_field`, cfl.`id_lang`, cfl.`id_shop`, cfl.`name`
        FROM `'._DB_PREFIX_.'customization_field_lang` cfl
        LEFT JOIN `'._DB_PREFIX_.'customization_field` cf ON cf.`id_customization_field` = cfl.`id_customization_field`
        WHERE cfl.`id_shop` = '.(int)$id_default_shop.'
        AND cfl.`id_customization_field` IN ('.implode(',', array_map('intval', $customization_ids)).')'
    );

    if (!$res) {
        $sql = 'INSERT IGNORE INTO `'._DB_PREFIX_.'customization_field_lang` (`id_customization_field`, `id_lang`, `name`, `id_shop`)
                (SELECT cfl.`id_customization_field`, cfl.`id_lang`, cfl.`name`, '.(int)$id_default_shop.'
                FROM '._DB_PREFIX_.'customization_field_lang cfl
                LEFT JOIN `'._DB_PREFIX_.'customization_field` cf ON cf.`id_customization_field` = cfl.`id_customization_field`
                WHERE cfl.`id_shop` = 1
                AND cfl.`id_customization_field` IN ('.implode(',', array_map('intval', $customization_ids)).'))';

        Db::getInstance()->execute($sql);
    }

    Configuration::updateGlobalValue('GIFTCARD_EXPIRATION_TIME', Configuration::get('GIFTCARD_EXPIRATION_TIME', null, $id_default_shop_group, $id_default_shop));
    Configuration::updateGlobalValue('GIFTCARD_EXPIRATION_DATE', Configuration::get('GIFTCARD_EXPIRATION_DATE', null, $id_default_shop_group, $id_default_shop));
    Configuration::updateGlobalValue('GIFTCARD_DISPLAY_TOPMENU', Configuration::get('GIFTCARD_DISPLAY_TOPMENU', null, $id_default_shop_group, $id_default_shop));
    Configuration::updateGlobalValue('GIFTCARD_EMAIL_IMG_WIDTH', Configuration::get('GIFTCARD_EMAIL_IMG_WIDTH', null, $id_default_shop_group, $id_default_shop));
    Configuration::updateGlobalValue('GIFTCARD_EMAIL_IMG_HEIGHT', Configuration::get('GIFTCARD_EMAIL_IMG_HEIGHT', null, $id_default_shop_group, $id_default_shop));
    Configuration::updateGlobalValue('GIFTCARD_CART_RULE', Configuration::get('GIFTCARD_CART_RULE', null, $id_default_shop_group, $id_default_shop));
    Configuration::updateGlobalValue('GIFTCARD_USE_CART_RULE', Configuration::get('GIFTCARD_USE_CART_RULE', null, $id_default_shop_group, $id_default_shop));
    Configuration::updateGlobalValue('GIFTCARD_USE_CACHE', Configuration::get('GIFTCARD_USE_CACHE', null, $id_default_shop_group, $id_default_shop));
    Configuration::updateGlobalValue('GIFTCARD_PROD_'.(int)$id_default_currency, (int)$product->id);
    Configuration::updateGlobalValue('GIFTCARD_CAT', Configuration::get('GIFTCARD_CAT', null, $id_default_shop_group, $id_default_shop));
    Configuration::updateGlobalValue('GIFTCARD_AMOUNT_CUSTOM_FROM_'.(int)$product->id, Configuration::get('GIFTCARD_AMOUNT_CUSTOM_FROM', null, $id_default_shop_group, $id_default_shop));
    Configuration::updateGlobalValue('GIFTCARD_AMOUNT_CUSTOM_TO_'.(int)$product->id, Configuration::get('GIFTCARD_AMOUNT_CUSTOM_TO', null, $id_default_shop_group, $id_default_shop));
    Configuration::updateGlobalValue('GIFTCARD_AMOUNT_FIXED_'.(int)$product->id, Configuration::get('GIFTCARD_AMOUNT_FIXED', null, $id_default_shop_group, $id_default_shop));
    Configuration::updateGlobalValue('GIFTCARD_AMOUNT_DEFAULT_'.(int)$product->id, Configuration::get('GIFTCARD_AMOUNT_DEFAULT', null, $id_default_shop_group, $id_default_shop));
    Configuration::updateGlobalValue('GIFTCARD_AMOUNT_CUSTOM_PITCH_'.(int)$product->id, Configuration::get('GIFTCARD_AMOUNT_CUSTOM_PITCH', null, $id_default_shop_group, $id_default_shop));
    foreach (GiftCardModel::$attributes_goup as $attribute_group) {
        Configuration::updateGlobalValue('GIFTCARD_ATTRGROUP_'.Tools::strtoupper($attribute_group['name']), Configuration::get('GIFTCARD_ATTRGROUP_'.Tools::strtoupper($attribute_group['name']), null, $id_default_shop_group, $id_default_shop));
    }
    foreach (GiftCardModel::$customizations as $customization) {
        Configuration::updateGlobalValue('GIFTCARD_CUST_'.Tools::strtoupper($customization['name']).'_'.(int)$product->id, Configuration::get('GIFTCARD_CUST_'.Tools::strtoupper($customization['name']), null, $id_default_shop_group, $id_default_shop));
    }

    Configuration::deleteByName('GIFTCARD_PROD');
    Configuration::deleteByName('GIFTCARD_AMOUNT_CUSTOM_PITCH');
    Configuration::deleteByName('GIFTCARD_AMOUNT_CUSTOM_FROM');
    Configuration::deleteByName('GIFTCARD_AMOUNT_CUSTOM_TO');
    Configuration::deleteByName('GIFTCARD_AMOUNT_FIXED');
    Configuration::deleteByName('GIFTCARD_AMOUNT_DEFAULT');
    foreach (GiftCardModel::$customizations as $customization) {
        Configuration::deleteByName('GIFTCARD_CUST_'.Tools::strtoupper($customization['name']));
    }

    $currency_installed = array();
    foreach ($shops as $id_shop) {
        $currencies = Currency::getCurrenciesByIdShop((int)$id_shop);
        foreach ($currencies as $currency) {
            if (in_array($currency['id_currency'], $currency_installed) || $currency['deleted'] || !$currency['active']) {
                continue;
            }

            $shops = GiftCardModel::getShopsByIdCurrency((int)$currency['id_currency']);
            $object->duplicateGiftCard((int)$currency['id_currency'], $shops);
            $currency_installed[] = (int)$currency['id_currency'];
        }
    }
    return true;
}
