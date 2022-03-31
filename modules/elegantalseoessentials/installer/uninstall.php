<?php
/**
 * @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
 * @copyright (c) 2022, Jamoliddin Nasriddinov
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

/**
 * This file returns array of sql queries that are required to be executed during module uninstallation.
 */
$sql = array();

// Drop tables that are created during module installation. Note: order of queries is important here.
$sql[] = "DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "elegantalseoessentials_redirects_shop`";
$sql[] = "DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "elegantalseoessentials_redirects`";
$sql[] = "DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "elegantalseoessentials_canonicals_shop`";
$sql[] = "DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "elegantalseoessentials_canonicals_lang`";
$sql[] = "DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "elegantalseoessentials_canonicals`";
$sql[] = "DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "elegantalseoessentials_auto_meta_shop`";
$sql[] = "DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "elegantalseoessentials_auto_meta_lang`";
$sql[] = "DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "elegantalseoessentials_auto_meta`";
$sql[] = "DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "elegantalseoessentials_image_alt_shop`";
$sql[] = "DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "elegantalseoessentials_image_alt_lang`";
$sql[] = "DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "elegantalseoessentials_image_alt`";
$sql[] = "DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "elegantalseoessentials_html_shop`";
$sql[] = "DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "elegantalseoessentials_html_lang`";
$sql[] = "DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "elegantalseoessentials_html`";

return $sql;
