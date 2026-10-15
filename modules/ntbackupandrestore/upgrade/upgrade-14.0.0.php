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

function upgrade_module_14_0_0($module)
{
    $req_1 = Db::getInstance()->execute('
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ntbr_googlecloud` (
            `id_ntbr_googlecloud`   int(10)         unsigned    NOT NULL    auto_increment,
            `id_ntbr_config`        int(10)         unsigned    NOT NULL,
            `active`           		tinyint(1)                  NOT NULL    DEFAULT "0",
            `name`                  varchar(255)                NOT NULL,
            `config_nb_backup`      int(10)         unsigned    NOT NULL    DEFAULT "0",
            `bucket`                text                        NOT NULL,
            `directory`             text                        NOT NULL,
            `token`                 text                		NOT NULL,
            `date_add`              datetime,
            `date_upd`              datetime,
            PRIMARY KEY (`id_ntbr_googlecloud`)
        ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8;
    ');

    if (!$req_1) {
        PrestaShopLogger::addLog('Could not add Google Cloud Storage table ' . Db::getInstance()->getMsgError(), 3);

        return false;
    }

    $req_2 = Db::getInstance()->execute('
        ALTER TABLE `' . _DB_PREFIX_ . 'ntbr_config`
        ADD `crypt_backup` TINYINT(1) NOT NULL DEFAULT "0" AFTER `create_on_distant`,
        ADD `sodium_key` TEXT AFTER `crypt_backup`,
        ADD `only_origin_img` TINYINT(1) NOT NULL DEFAULT "0" AFTER `ignore_product_image`;
    ');

    if (!$req_2) {
        PrestaShopLogger::addLog('Could not upgrade config table ' . Db::getInstance()->getMsgError(), 3);

        return false;
    }

    $req_3 = Db::getInstance()->execute('
        UPDATE `' . _DB_PREFIX_ . 'ntbr_config` SET `sodium_key` = "";
    ');

    if (!$req_3) {
        PrestaShopLogger::addLog('Could not initialize sodium key ' . Db::getInstance()->getMsgError(), 3);

        return false;
    }

    return $module;
}
