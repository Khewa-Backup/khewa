<?php
/**
 * 2007-2020 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2020 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ewrelatedproductsbyprod` (
    `id_relatedproductsbyprod` int(11) NOT NULL AUTO_INCREMENT,
    `id_product` int(11) UNSIGNED NOT NULL,
    `id_feature` int(11) UNSIGNED NULL DEFAULT \'0\',
    `id_feature_value` int(11) UNSIGNED NULL DEFAULT \'0\',
    `id_attribute` int(11) UNSIGNED NULL DEFAULT \'0\',
    `id_attribute_value` int(11) UNSIGNED NULL DEFAULT \'0\',
    `reference` VARCHAR(45) NULL DEFAULT "",
    `active` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY  (`id_relatedproductsbyprod`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ewrelatedproductsbycateg` (
    `id_relatedproductsbycateg` int(11) NOT NULL AUTO_INCREMENT,
    `id_category` int(11) UNSIGNED NOT NULL,
    `id_feature` int(11) UNSIGNED NULL DEFAULT \'0\',
    `id_feature_value` int(11) UNSIGNED NULL DEFAULT \'0\',
    `id_attribute` int(11) UNSIGNED NULL DEFAULT \'0\',
    `id_attribute_value` int(11) UNSIGNED NULL DEFAULT \'0\',
    `reference` VARCHAR(45) NULL DEFAULT "",
    `active` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY  (`id_relatedproductsbycateg`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ewrelatedproductsbyprod_shop` (
    `id_relatedproductsbyprod` int(11) NOT NULL AUTO_INCREMENT,
    `id_shop` int(11) UNSIGNED NOT NULL,
    PRIMARY KEY  (`id_relatedproductsbyprod`, `id_shop`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ewrelatedproductsbycateg_shop` (
    `id_relatedproductsbycateg` int(11) NOT NULL AUTO_INCREMENT,
    `id_shop` int(11) UNSIGNED NOT NULL,
    PRIMARY KEY  (`id_relatedproductsbycateg`, `id_shop`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
