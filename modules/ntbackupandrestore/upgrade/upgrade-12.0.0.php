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

function upgrade_module_12_0_0($module)
{
    $update_aws = Db::getInstance()->execute('
        ALTER TABLE `' . _DB_PREFIX_ . 'ntbr_aws`
        ADD `host` TEXT NOT NULL AFTER `bucket`,
        ADD `type_s3` int(10) unsigned NOT NULL DEFAULT "1" AFTER `id_ntbr_config`,
        ADD `accept_unvalid_ssl` tinyint(1) NOT NULL DEFAULT "0" AFTER `active`;
    ');

    if (!$update_aws) {
        PrestaShopLogger::addLog('Could not upgrade AWS table. ' . Db::getInstance()->getMsgError(), 3);

        return false;
    }
    $update_onedrive = Db::getInstance()->execute('
        ALTER TABLE `' . _DB_PREFIX_ . 'ntbr_onedrive`
        ADD `my_site` TEXT NOT NULL AFTER `token`,
        ADD `business` tinyint(1) NOT NULL DEFAULT "0" AFTER `active`;
    ');

    if (!$update_onedrive) {
        PrestaShopLogger::addLog('Could not upgrade Onedrive table. ' . Db::getInstance()->getMsgError(), 3);

        return false;
    }

    $update_config = Db::getInstance()->execute('
        ALTER TABLE `' . _DB_PREFIX_ . 'ntbr_config`
        ADD `receive_email_version` tinyint(1) NOT NULL DEFAULT "0" AFTER `send_email`,
        ADD `mail_version` TEXT NOT NULL AFTER `mail_backup`,
        ADD `scan_files` tinyint(1) NOT NULL DEFAULT "0" AFTER `js_download`,
        ADD `not_recreate_tables` text AFTER `ignore_tables`,
        ADD `disable` tinyint(1) NOT NULL DEFAULT "0" AFTER `is_default`;
    ');

    if (!$update_config) {
        PrestaShopLogger::addLog('Could not upgrade config table. ' . Db::getInstance()->getMsgError(), 3);

        return false;
    }

    $update_config2 = Db::getInstance()->execute('
        UPDATE `' . _DB_PREFIX_ . 'ntbr_config` SET `mail_version` = "' . Configuration::get('PS_SHOP_EMAIL') . '";
    ');

    if (!$update_config2) {
        PrestaShopLogger::addLog('Could not set new config table data. ' . Db::getInstance()->getMsgError(), 3);

        return false;
    }

    $create_table_scan_size = Db::getInstance()->execute('
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ntbr_scan_size` (
            `id_ntbr_scan_size` int(10)         unsigned    NOT NULL    auto_increment,
            `file_path`         text                        NOT NULL,
            `file_size`         int(10)         unsigned    NOT NULL,
            `date_add`          datetime                    NOT NULL,
            PRIMARY KEY (`id_ntbr_scan_size`)
        ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8;
    ');

    if (!$create_table_scan_size) {
        PrestaShopLogger::addLog('Could not create scan size table. ' . Db::getInstance()->getMsgError(), 3);

        return false;
    }

    $create_table_yandex = Db::getInstance()->execute('
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ntbr_yandex` (
            `id_ntbr_yandex`   int(10)          unsigned    NOT NULL    auto_increment,
            `id_ntbr_config`    int(10)         unsigned    NOT NULL,
            `active`           	tinyint(1)                  NOT NULL    DEFAULT "0",
            `name`              varchar(255)                NOT NULL,
            `config_nb_backup`  int(10)         unsigned    NOT NULL    DEFAULT "0",
            `directory`         varchar(255)                NOT NULL    DEFAULT "",
            `token`             text                		NOT NULL,
            `date_add`          datetime,
            `date_upd`          datetime,
            PRIMARY KEY (`id_ntbr_yandex`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;
    ');

    if (!$create_table_yandex) {
        PrestaShopLogger::addLog('Could not create Yandex table. ' . Db::getInstance()->getMsgError(), 3);

        return false;
    }

    $create_table_box = Db::getInstance()->execute('
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ntbr_box` (
            `id_ntbr_box`       int(10)         unsigned    NOT NULL    auto_increment,
            `id_ntbr_config`    int(10)         unsigned    NOT NULL,
            `active`            tinyint(1)                  NOT NULL    DEFAULT "0",
            `name`              varchar(255)                NOT NULL,
            `config_nb_backup`  int(10)         unsigned    NOT NULL    DEFAULT "0",
            `directory_key`     varchar(255)                NOT NULL,
            `directory_path`    varchar(255)                NOT NULL    DEFAULT "",
            `token`             text                		NOT NULL,
            `date_add`          datetime,
            `date_upd`          datetime,
            PRIMARY KEY (`id_ntbr_box`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;
    ');

    if (!$create_table_box) {
        PrestaShopLogger::addLog('Could not create Box table. ' . Db::getInstance()->getMsgError(), 3);

        return false;
    }

    $shops = Shop::getShops();

    foreach ($shops as $shop) {
        if (!Configuration::updateValue('NTBR_SCAN_ID_CONFIG', 0, false, $shop['id_shop_group'], $shop['id_shop'])) {
            PrestaShopLogger::addLog('Could not create scan ID config.', 3);

            return false;
        }
    }

    if (version_compare(_PS_VERSION_, '1.7', '>=') === true) {
        $tab_id = Tab::getIdFromClassName('AdminNtbackupandrestore');
        $tab = new Tab($tab_id);
        $tab->icon = 'backup';
        if (!$tab->update()) {
            PrestaShopLogger::addLog('Could not add tab icon.', 3);

            return false;
        }
    }

    return $module;
}
