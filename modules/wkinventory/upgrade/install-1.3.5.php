<?php
/**
* NOTICE OF LICENSE
*
* This file is part of the 'WK Inventory' module feature.
* Developped by Khoufi Wissem (2017).
* You are not allowed to use it on several site
* You are not allowed to sell or redistribute this module
* This header must not be removed
*
*  @author    KHOUFI Wissem - K.W
*  @copyright Khoufi Wissem
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

function upgrade_module_1_3_5($module)
{
    Configuration::updateValue('WKINVENTORY_GEN_EAN', 1);
    Configuration::updateValue('WKINVENTORY_GEN_UPC', 1);
    Configuration::updateValue('WKINVENTORY_PREFIX_CODE', 400);

    $module->registerHook('actionProductUpdate');
    $module->registerHook('actionProductSave');

    /* Uninstall tabs */
    $module->uninstallTabs();

    // Create new tabs
    $module->installTabs();

    // All went well!
    return true;
}
