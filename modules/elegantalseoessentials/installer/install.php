<?php
/**
 * @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
 * @copyright (c) 2022, Jamoliddin Nasriddinov
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

/**
 * This file returns array of sql queries that are required to be executed during module installation.
 */
$sql = array();

$sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "elegantalseoessentials_redirects` ( 
    `id_elegantalseoessentials_redirects` int(11) unsigned NOT NULL AUTO_INCREMENT, 
    `id_product` int(11) unsigned, 
    `old_url` text NOT NULL, 
    `new_url` text NOT NULL, 
    `redirect_type` varchar(6) NOT NULL, 
    `is_active` tinyint(1) unsigned NOT NULL DEFAULT '1', 
    `created_at` DATETIME, 
    `expires_at` DATETIME, 
    PRIMARY KEY  (`id_elegantalseoessentials_redirects`) 
) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=UTF8;";

$sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "elegantalseoessentials_redirects_shop` ( 
    `id_elegantalseoessentials_redirects` int(11) unsigned NOT NULL, 
    `id_shop` int(11) unsigned NOT NULL, 
    PRIMARY KEY (`id_elegantalseoessentials_redirects`, `id_shop`), 
    FOREIGN KEY (`id_elegantalseoessentials_redirects`) REFERENCES `" . _DB_PREFIX_ . "elegantalseoessentials_redirects` (`id_elegantalseoessentials_redirects`) ON DELETE CASCADE 
) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=UTF8;";

$sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "elegantalseoessentials_canonicals` ( 
    `id_elegantalseoessentials_canonicals` int(11) unsigned NOT NULL AUTO_INCREMENT, 
    `is_active` tinyint(1) unsigned NOT NULL DEFAULT '1', 
    `created_at` DATETIME, 
    PRIMARY KEY  (`id_elegantalseoessentials_canonicals`) 
) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=UTF8;";

$sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "elegantalseoessentials_canonicals_lang` ( 
    `id_elegantalseoessentials_canonicals` int(11) unsigned NOT NULL,
    `id_lang` int(11) unsigned NOT NULL,
    `old_url` text NOT NULL, 
    `new_url` text NOT NULL, 
    PRIMARY KEY (`id_elegantalseoessentials_canonicals`, `id_lang`),
    FOREIGN KEY (`id_elegantalseoessentials_canonicals`) REFERENCES `" . _DB_PREFIX_ . "elegantalseoessentials_canonicals` (`id_elegantalseoessentials_canonicals`) ON DELETE CASCADE 
) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=UTF8;";

$sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "elegantalseoessentials_canonicals_shop` ( 
    `id_elegantalseoessentials_canonicals` int(11) unsigned NOT NULL, 
    `id_shop` int(11) unsigned NOT NULL, 
    PRIMARY KEY (`id_elegantalseoessentials_canonicals`, `id_shop`), 
    FOREIGN KEY (`id_elegantalseoessentials_canonicals`) REFERENCES `" . _DB_PREFIX_ . "elegantalseoessentials_canonicals` (`id_elegantalseoessentials_canonicals`) ON DELETE CASCADE 
) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=UTF8;";

$sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "elegantalseoessentials_auto_meta` ( 
    `id_elegantalseoessentials_auto_meta` int(11) unsigned NOT NULL AUTO_INCREMENT, 
    `name` varchar(255) NOT NULL, 
    `category_ids` text, 
    `is_active` tinyint(1) unsigned NOT NULL DEFAULT '1',  
    `created_at` DATETIME, 
    `applied_at` DATETIME, 
    PRIMARY KEY (`id_elegantalseoessentials_auto_meta`)
) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=UTF8;";

$sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "elegantalseoessentials_auto_meta_lang` (
    `id_elegantalseoessentials_auto_meta` int(11) unsigned NOT NULL,
    `id_lang` int(11) unsigned NOT NULL,
    `url_pattern` varchar(255),
    `title_pattern` varchar(255),
    `description_pattern` varchar(255),
    `keywords_pattern` varchar(255),
    PRIMARY KEY (`id_elegantalseoessentials_auto_meta`, `id_lang`),
    FOREIGN KEY (`id_elegantalseoessentials_auto_meta`) REFERENCES `" . _DB_PREFIX_ . "elegantalseoessentials_auto_meta` (`id_elegantalseoessentials_auto_meta`) ON DELETE CASCADE 
) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=UTF8;";

$sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "elegantalseoessentials_auto_meta_shop` (
    `id_elegantalseoessentials_auto_meta` int(11) unsigned NOT NULL, 
    `id_shop` int(11) unsigned NOT NULL,
    PRIMARY KEY (`id_elegantalseoessentials_auto_meta`, `id_shop`),
    FOREIGN KEY (`id_elegantalseoessentials_auto_meta`) REFERENCES `" . _DB_PREFIX_ . "elegantalseoessentials_auto_meta` (`id_elegantalseoessentials_auto_meta`) ON DELETE CASCADE 
) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=UTF8;";

$sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "elegantalseoessentials_image_alt` ( 
    `id_elegantalseoessentials_image_alt` int(11) unsigned NOT NULL AUTO_INCREMENT, 
    `name` varchar(255) NOT NULL, 
    `category_ids` text, 
    `is_active` tinyint(1) unsigned NOT NULL DEFAULT '1',  
    `created_at` DATETIME, 
    `applied_at` DATETIME, 
    PRIMARY KEY (`id_elegantalseoessentials_image_alt`)
) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=UTF8;";

$sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "elegantalseoessentials_image_alt_lang` (
    `id_elegantalseoessentials_image_alt` int(11) unsigned NOT NULL,
    `id_lang` int(11) unsigned NOT NULL,
    `pattern` varchar(255),
    PRIMARY KEY (`id_elegantalseoessentials_image_alt`, `id_lang`),
    FOREIGN KEY (`id_elegantalseoessentials_image_alt`) REFERENCES `" . _DB_PREFIX_ . "elegantalseoessentials_image_alt` (`id_elegantalseoessentials_image_alt`) ON DELETE CASCADE 
) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=UTF8;";

$sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "elegantalseoessentials_image_alt_shop` (
    `id_elegantalseoessentials_image_alt` int(11) unsigned NOT NULL, 
    `id_shop` int(11) unsigned NOT NULL,
    PRIMARY KEY (`id_elegantalseoessentials_image_alt`, `id_shop`),
    FOREIGN KEY (`id_elegantalseoessentials_image_alt`) REFERENCES `" . _DB_PREFIX_ . "elegantalseoessentials_image_alt` (`id_elegantalseoessentials_image_alt`) ON DELETE CASCADE 
) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=UTF8;";

$sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "elegantalseoessentials_html` (
    `id_elegantalseoessentials_html` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `hooks` text NOT NULL,
    `pages` text NOT NULL,
    `position` int(11) unsigned NOT NULL DEFAULT '1',
    `is_active` tinyint(1) unsigned NOT NULL DEFAULT '1',
    PRIMARY KEY  (`id_elegantalseoessentials_html`) 
) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=UTF8;";

$sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "elegantalseoessentials_html_shop` (
    `id_elegantalseoessentials_html` int(11) unsigned NOT NULL, 
    `id_shop` int(11) unsigned NOT NULL,
    PRIMARY KEY (`id_elegantalseoessentials_html`, `id_shop`),
    FOREIGN KEY (`id_elegantalseoessentials_html`) REFERENCES `" . _DB_PREFIX_ . "elegantalseoessentials_html` (`id_elegantalseoessentials_html`) ON DELETE CASCADE 
) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=UTF8;";

$sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "elegantalseoessentials_html_lang` (
    `id_elegantalseoessentials_html` int(11) unsigned NOT NULL,
    `id_lang` int(11) unsigned NOT NULL,
    `html` text NOT NULL,
    PRIMARY KEY (`id_elegantalseoessentials_html`, `id_lang`),
    FOREIGN KEY (`id_elegantalseoessentials_html`) REFERENCES `" . _DB_PREFIX_ . "elegantalseoessentials_html` (`id_elegantalseoessentials_html`) ON DELETE CASCADE 
) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=UTF8;";

return $sql;
