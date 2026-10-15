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

function upgrade_module_1_5_11($module)
{
    $sqlFile = dirname(__FILE__).'/sql/install-1.5.11.sql';
    if (!$module->loadSQLFile($sqlFile)) {
        return false;
    }

    // All went well!
    return true;
}
