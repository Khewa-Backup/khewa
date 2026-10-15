<?php
/**
 * @author    ELEGANTAL <info@elegantal.com>
 * @copyright (c) 2023, ELEGANTAL <www.elegantal.com>
 * @license   Proprietary License - It is forbidden to resell or redistribute copies of the module or modified copies of the module.
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
