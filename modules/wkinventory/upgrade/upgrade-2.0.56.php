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

function upgrade_module_2_0_56($module)
{
    $module->uninstallTabs();
    $module->installTabs();
    $module->registerHook('displayBackOfficeFooter');

    if (!$module->loadSQLFile(dirname(__FILE__).'/sql/install-logs-table.sql')) {
        return false;
    }
    // All went well!
    return true;
}
