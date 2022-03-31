<?php
/**
 * @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
 * @copyright (c) 2022, Jamoliddin Nasriddinov
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_1_3($module)
{
    unset($module);
    $sql = array();

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

    foreach ($sql as $query) {
        if (Db::getInstance()->execute($query) == false) {
            throw new Exception(Db::getInstance()->getMsgError());
        }
    }

    return true;
}
