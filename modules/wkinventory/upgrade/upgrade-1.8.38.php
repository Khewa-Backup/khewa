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

function upgrade_module_1_8_38($module)
{
    Configuration::updateValue('WKINVENTORY_PDFREPORT_MODE', 'normal');
    // Uninstall tabs
    $module->uninstallTabs();
    // Create new tabs
    $module->installTabs();

    $module->registerHook('actionProductDelete');
    $module->registerHook('actionAttributeCombinationDelete');
    $module->registerHook('actionObjectDeleteAfter');

    $sqlFile = dirname(__FILE__).'/sql/upgrade-1.8.38.sql';
    if (!$module->loadSQLFile($sqlFile)) {
        return false;
    }

    // All went well!
    return true;
}
