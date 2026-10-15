<?php
/**
 * 2013-2024 2N Technologies
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@2n-tech.com so we can send you a copy immediately.
 *
 * @author    2N Technologies <contact@2n-tech.com>
 * @copyright 2013-2024 2N Technologies
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/../autoload.php';

function upgrade_module_10_1_0($module)
{
    $update_table_config = Db::getInstance()->execute('
        ALTER TABLE `' . _DB_PREFIX_ . 'ntbr_config`
        CHANGE `ignore_product_image` `ignore_product_image` int(10) unsigned NOT NULL DEFAULT "0";
    ');

    if (!Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ntbr_sugarsync`;')) {
        return false;
    }

    $create_table_sugarsync = Db::getInstance()->execute('
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ntbr_sugarsync` (
            `id_ntbr_sugarsync` int(10)         unsigned    NOT NULL    auto_increment,
            `id_ntbr_config`    int(10)         unsigned    NOT NULL,
            `active`           	tinyint(1)                  NOT NULL    DEFAULT "0",
            `name`              varchar(255)                NOT NULL,
            `config_nb_backup`  int(10)         unsigned    NOT NULL    DEFAULT "0",
            `directory_key`     varchar(255)                NOT NULL    DEFAULT "",
            `directory_path`    varchar(255)                NOT NULL    DEFAULT "",
            `token`             text                		NOT NULL,
            `login`             varchar(255)                NOT NULL,
            `date_add`          datetime,
            `date_upd`          datetime,
            PRIMARY KEY (`id_ntbr_sugarsync`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 ;
    ');

    if (!$update_table_config || !$create_table_sugarsync) {
        return false;
    }

    return $module;
}
