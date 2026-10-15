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

function upgrade_module_1_1_3($object)
{
    Configuration::updateValue('GIFTCARD_AMOUNT_CUSTOM_PITCH', '1');

    $languages = Language::getLanguages();
    $path = _PS_MODULE_DIR_ . $object->name . '/mails/en';
    foreach ($languages as $language) {
        $dest = _PS_MODULE_DIR_ . $object->name . '/mails/' . $language['iso_code'];
        if (file_exists($dest)) {
            if (file_exists($dest . '/giftcard_print.txt') && file_exists($dest . '/giftcard_friend.txt')) {
                continue;
            }

            $files = ['giftcard_print.txt', 'giftcard_friend.txt', 'index.php'];
        } else {
            if (!mkdir($dest, 0777, true)) {
                continue;
            }

            $files = scandir($path);
        }

        foreach ($files as $file) {
            if ('.' == $file || '..' == $file) {
                continue;
            }

            copy($path . '/' . $file, $dest . '/' . $file);
        }
    }

    return true;
}
