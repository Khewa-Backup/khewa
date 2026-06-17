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

function upgrade_module_1_7_0($object)
{
    $sql = [];
    include _PS_MODULE_DIR_ . $object->name . '/sql/install.php';
    foreach ($sql as $s) {
        if (!Db::getInstance()->execute($s)) {
            return false;
        }
    }

    $metas = Meta::getMetaByPage('module-' . $object->name . '-page', (int) Context::getContext()->language->id);

    if (($meta = new Meta((int) $metas['id_meta']))
        && Validate::isLoadedObject($meta)
        && version_compare(_PS_VERSION_, '1.6', '>=')
        && version_compare(_PS_VERSION_, '1.7', '<')
    ) {
        GiftCardModel::addMeta($meta->id, Theme::getThemes());
    }

    $product_ids = $object->getConfIds('GIFTCARD_PROD');
    $product_ids = explode('_', $product_ids);
    foreach ($product_ids as $product_id) {
        $product = new Product((int) $product_id);
        if (!Validate::isLoadedObject($product)) {
            continue;
        }

        $product_images = Image::getImages(Context::getContext()->language->id, $product->id);
        foreach ($product_images as $image) {
            GiftCardModel::addAmount($image['id_image'], Configuration::getGlobalValue('GIFTCARD_AMOUNT_DEFAULT_' . (int) $product->id), false, 0, 0);
        }
    }

    return true;
}
