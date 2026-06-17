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

function upgrade_module_1_5_0($object)
{
    $sql = [];
    include _PS_MODULE_DIR_ . $object->name . '/sql/install.php';
    foreach ($sql as $s) {
        if (!Db::getInstance()->execute($s)) {
            return false;
        }
    }

    $images = Image::getImages(Context::getContext()->language->id, (int) Configuration::get('GIFTCARD_PROD'));
    foreach ($images as $image) {
        GiftCardModel::addGiftCardImageLang($image['id_image']);
    }

    return true;
}
