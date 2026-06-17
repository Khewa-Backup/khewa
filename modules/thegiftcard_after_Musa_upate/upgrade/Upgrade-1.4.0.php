<?php
/**
* 2022 - Keyrnel SARL
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
* @copyright 2022 - Keyrnel SARL
* @license   commercial
* International Registered Trademark & Property of Keyrnel SARL
*/
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_4_0($object)
{
    if (!$object->registerHook('actionAfterDeleteProductInCart')
        || !$object->registerHook('actionDeleteProductInCartAfter')
        || !$object->registerHook('displayBackOfficeTop')) {
        return false;
    }

    $fixed_amounts = array_map('intval', explode(',', Configuration::get('GIFTCARD_AMOUNT_FIXED')));

    if (!Configuration::updateValue('GIFTCARD_AMOUNT_DEFAULT', $fixed_amounts[0])
        || !Configuration::updateValue('GIFTCARD_USE_CACHE', '0')
        || !Configuration::updateValue('GIFTCARD_USE_CART_RULE', '0')) {
        return false;
    }

    // Delete deprecated customization field
    $id_customization_field = (int) Configuration::get('GIFTCARD_CUST_METHOD');
    if (!Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'customization_field` WHERE `id_customization_field` = ' . (int) $id_customization_field)
        && !Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'customization_field_lang` WHERE `id_customization_field` = ' . (int) $id_customization_field)
        && !Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'customized_data` WHERE `index` = ' . (int) $id_customization_field)) {
        return false;
    }

    if (!Configuration::deleteByName('GIFTCARD_CUST_METHOD')) {
        return false;
    }

    // Delete deprecated mysql columns & update existing gift cards
    $giftcards = GiftCardModel::getGiftcards();

    if (!Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'giftcard` ADD `id_order_detail` int(10) unsigned NOT NULL DEFAULT 0 AFTER `id_giftcard` ,
        ADD `id_customization` int(10) unsigned NOT NULL DEFAULT 0 AFTER `id_image` ,
        DROP `id_order`')
    ) {
        return false;
    }

    $customization_ids = [];
    foreach ($giftcards as $giftcard) {
        $order = new Order((int) $giftcard['id_order']);
        if (!Validate::isLoadedObject($order)) {
            continue;
        }

        $cart_rule = new CartRule((int) $giftcard['id_cart_rule']);
        if (!Validate::isLoadedObject($cart_rule)) {
            continue;
        }

        $id_order_detail = 0;
        $id_customization = 0;

        $products = $order->getProductsDetail();
        foreach ($products as $product) {
            if (!($product['product_id'] == Configuration::get('GIFTCARD_PROD') && $product['unit_price_tax_incl'] == $cart_rule->reduction_amount)) {
                continue;
            }

            $id_order_detail = $product['id_order_detail'];
            $id_customization = GiftCardModel::getCustomization((int) $order->id_cart, (int) $product['product_attribute_id'], (int) $product['product_quantity'], $customization_ids);
            if (!in_array($id_customization, $customization_ids)) {
                $customization_ids[] = $id_customization;
            }
            break;
        }

        Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'giftcard`
  			SET `id_order_detail` = ' . (int) $id_order_detail . ', `id_customization` = ' . (int) $id_customization . '
  			WHERE `id_giftcard` = ' . (int) $giftcard['id_giftcard']);
    }

    return true;
}
