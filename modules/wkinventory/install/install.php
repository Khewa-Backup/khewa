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

Db::getInstance()->Execute(
    'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'wkinventory` (
		`id_inventory` int(11) NOT NULL AUTO_INCREMENT,
		`name` varchar(250) NOT NULL,
		`id_employee` int(11) NOT NULL,
		`category_ids` varchar(255) DEFAULT NULL,
		`id_supplier` int(11) NOT NULL,
		`manufacturer_ids` varchar(255) DEFAULT NULL,
		`id_warehouse` int(11) DEFAULT NULL,
		`id_shop` int(11) NOT NULL,
		`date_add` datetime NOT NULL,
		`date_upd` datetime DEFAULT NULL,
		`done` tinyint(1) NOT NULL,
		`stock_updated` tinyint(1) NOT NULL,
		`is_empty` tinyint(1) unsigned DEFAULT \'0\',
		`stock_zero` tinyint(1) unsigned DEFAULT \'0\',
		`settings` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
		PRIMARY KEY (`id_inventory`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'
);

Db::getInstance()->Execute(
    'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'wkinventory_product` (
		`id_inventory_product` int(11) NOT NULL AUTO_INCREMENT,
		`id_inventory` int(11) NOT NULL,
		`id_product` int(11) NOT NULL,
		`id_product_attribute` int(11) NOT NULL,
		`id_warehouse` int(11) DEFAULT NULL,
		`id_employee` int(11) NOT NULL,
		`date_upd` datetime DEFAULT NULL,
		`shop_quantity` int(11) NOT NULL,
		`real_quantity` int(11) NOT NULL,
		`sold_quantity` int(11) DEFAULT NULL,
		`unit_price` float NOT NULL,
		`id_currency` int(11) DEFAULT NULL,
		`stock_updated` tinyint(1) NOT NULL,
		`has_error` tinyint(1) DEFAULT NULL,
		PRIMARY KEY (`id_inventory_product`),
		KEY `id_inventory` (`id_inventory`),
		KEY `product_attribute_product` (`id_product`),
		KEY `id_product_id_product_attribute` (`id_product`,`id_product_attribute`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'
);

Db::getInstance()->Execute(
    'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'wkinventory_log` (
		`id_wkinventory_log` int(10) unsigned NOT NULL AUTO_INCREMENT,
		`severity` tinyint(1) NOT NULL,
		`error_code` int(11) DEFAULT NULL,
		`message` text NOT NULL,
		`object_type` varchar(32) DEFAULT NULL,
		`object_id` int(10) unsigned DEFAULT NULL,
		`id_employee` int(10) unsigned DEFAULT NULL,
		`date_add` datetime NOT NULL,
		`date_upd` datetime NOT NULL,
		PRIMARY KEY (`id_wkinventory_log`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'
);
