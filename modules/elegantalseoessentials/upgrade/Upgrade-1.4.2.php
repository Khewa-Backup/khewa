<?php
/**
 * @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
 * @copyright (c) 2022, Jamoliddin Nasriddinov
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_4_2($module)
{
    unset($module);
    $sql = array();
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

    foreach ($sql as $query) {
        if (Db::getInstance()->execute($query) == false) {
            //throw new Exception(Db::getInstance()->getMsgError());
            return false;
        }
    }

    return true;
}
