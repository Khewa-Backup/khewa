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
$sql = [
    'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'giftcard`
	(
		`id_giftcard` int(10) unsigned NOT NULL auto_increment,
		`id_order_detail` int(10) unsigned NOT NULL,
		`id_cart_rule` int(10) unsigned NOT NULL,
		`id_image` int(10) unsigned NOT NULL,
		`id_customization` int(10) unsigned NOT NULL DEFAULT 0,
		`sent` tinyint(1) unsigned NOT NULL,
		PRIMARY KEY  (`id_giftcard`)
	) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci',

    'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'giftcard_tags`
	(
		`id_image` int(10) unsigned NOT NULL,
		`id_lang` int(10) unsigned NOT NULL,
		`tags` text,
		PRIMARY KEY  (`id_image`, `id_lang`)
	) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci',

    'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'giftcard_image_lang`
	(
		`id_image` int(10) unsigned NOT NULL,
		`id_lang` int(10) unsigned NOT NULL,
		PRIMARY KEY  (`id_image`)
	) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci',

    'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'giftcard_amounts`
	(
		`id_image` int(10) unsigned NOT NULL,
        `id_shop_group` int(11) unsigned,
        `id_shop` int(11) unsigned,
		`amount` int(10) unsigned NOT NULL,
        `auto` tinyint(1) unsigned NOT NULL DEFAULT 0,
		PRIMARY KEY  (`id_image`, `id_shop_group`, `id_shop`)
	) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci',
];
