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

function upgrade_module_2_8_84()
{
	Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute(
		'ALTER TABLE `'._DB_PREFIX_.'wkinventory` ADD 
		`settings` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL'
	);

    return true;
}
