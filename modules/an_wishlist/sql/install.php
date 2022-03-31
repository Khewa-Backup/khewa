<?php
/**
 * 2021 Anvanto
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses. 
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 *  @author Anvanto <anvantoco@gmail.com>
 *  @copyright  2021 Anvanto
 *  @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of Anvanto
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'an_wishlist` (
            `id_wishlist` int(10) NOT NULL AUTO_INCREMENT,
            `id_customer` int(10) unsigned NOT NULL,
            `is_guest` int(10) unsigned NOT NULL DEFAULT 0,
            `id_shop` int(10) unsigned NOT NULL,
            `date_upd` datetime NOT NULL,
            PRIMARY KEY  (`id_wishlist`)
            ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET = utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'an_wishlist_products` (
			  `id_wishlist_products` int(10) NOT NULL AUTO_INCREMENT,
			  `id_wishlist` int(11) NOT NULL,
			  `id_product` int(10) UNSIGNED NOT NULL,
			  `id_product_attribute` int(10) UNSIGNED NOT NULL,
			  `date_add` datetime NOT NULL,
            PRIMARY KEY  (`id_wishlist_products`)
            ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET = utf8';

return $sql;