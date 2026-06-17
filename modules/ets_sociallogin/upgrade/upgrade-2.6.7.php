<?php
/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_6_7($object)
{
    $migrate = array(
        'ETS_SOLO_WINDOWSLIVE_ENABLED'   => 'ETS_SOLO_MICROSOFT_ENABLED',
        'ETS_SOLO_WINDOWSLIVE_APP_ID'     => 'ETS_SOLO_MICROSOFT_APP_ID',
        'ETS_SOLO_WINDOWSLIVE_APP_SECRET' => 'ETS_SOLO_MICROSOFT_APP_SECRET',
        'ETS_SOLO_WINDOWSLIVE_CALLBACK'   => 'ETS_SOLO_MICROSOFT_CALLBACK',
        'ETS_SOLO_WINDOWSLIVE_TITLE'      => 'ETS_SOLO_MICROSOFT_TITLE',
    );
    foreach ($migrate as $oldKey => $newKey) {
        $value = Configuration::get($oldKey);
        if ($value !== false) {
            Configuration::updateValue($newKey, $value, true);
            Configuration::deleteByName($oldKey);
        }
    }
    if (method_exists('Tools', 'clearSmartyCache')) {
        Tools::clearSmartyCache();
    }
    return true;
}
