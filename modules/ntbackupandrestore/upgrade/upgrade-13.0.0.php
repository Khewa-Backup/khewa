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

function upgrade_module_13_0_0($module)
{
    $create_glob_conf_table = Db::getInstance()->execute('
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ntbr_glob_conf` (
            `id_ntbr_glob_conf`  int(10)         unsigned    NOT NULL    auto_increment,
            `name`              text                        NOT NULL,
            `value`             text                        NOT NULL,
            `date_add`          datetime                    NOT NULL,
            `date_upd`          datetime                    NOT NULL,
            PRIMARY KEY (`id_ntbr_glob_conf`)
        ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8;
    ');

    if (!$create_glob_conf_table) {
        PrestaShopLogger::addLog('Could not create general global table. ' . Db::getInstance()->getMsgError(), 3);

        return false;
    }

    $default_id_shop = (int) Configuration::get('PS_SHOP_DEFAULT');
    $default_id_shop_group = Shop::getGroupFromShop($default_id_shop);
    $shop_domain = Tools::getCurrentUrlProtocolPrefix() . Tools::getHttpHost();
    $shop_url = $shop_domain . __PS_BASE_URI__;

    $ntbr_admin_dir = Configuration::get('NTBR_ADMIN_DIR', null, $default_id_shop_group, $default_id_shop);
    $ntbr_automation_2nt = Configuration::get('NTBR_AUTOMATION_2NT', null, $default_id_shop_group, $default_id_shop);
    $ntbr_automation_2nt_hours = Configuration::get('NTBR_AUTOMATION_2NT_HOURS', null, $default_id_shop_group, $default_id_shop);
    $ntbr_automation_2nt_ip = Configuration::get('NTBR_AUTOMATION_2NT_IP', null, $default_id_shop_group, $default_id_shop);
    $ntbr_automation_2nt_minutes = Configuration::get('NTBR_AUTOMATION_2NT_MINUTES', null, $default_id_shop_group, $default_id_shop);
    $ntbr_big_website = Configuration::get('NTBR_BIG_WEBSITE', null, $default_id_shop_group, $default_id_shop);
    $ntbr_big_website_hide = Configuration::get('NTBR_BIG_WEBSITE_HIDE', null, $default_id_shop_group, $default_id_shop);
    $ntbr_hash = Configuration::get('NTBR_HASH', null, $default_id_shop_group, $default_id_shop);
    $ntbr_hash_temp = Configuration::get('NTBR_HASH_TEMP', null, $default_id_shop_group, $default_id_shop);
    $ntbr_multi_config = Configuration::get('NTBR_MULTI_CONFIG', null, $default_id_shop_group, $default_id_shop);
    $ntbr_ongoing = Configuration::get('NTBR_ONGOING', null, $default_id_shop_group, $default_id_shop);
    $ntbr_ongoing_id_config = Configuration::get('NTBR_ONGOING_ID_CONFIG', null, $default_id_shop_group, $default_id_shop);
    $ntbr_ongoing_refresh = Configuration::get('NTBR_ONGOING_REFRESH', null, $default_id_shop_group, $default_id_shop);
    $ntbr_scan_id_config = Configuration::get('NTBR_SCAN_ID_CONFIG', null, $default_id_shop_group, $default_id_shop);
    $ntbr_sel = Configuration::get('NTBR_SEL', null, $default_id_shop_group, $default_id_shop);
    $ntbr_sel_temp = Configuration::get('NTBR_SEL_TEMP', null, $default_id_shop_group, $default_id_shop);

    $insert_glob_config = Db::getInstance()->execute('
        INSERT INTO ' . _DB_PREFIX_ . 'ntbr_glob_conf (`name`, `value`) VALUES
            ("NTBR_ADMIN_DIR", "' . pSQL($ntbr_admin_dir) . '"),
            ("NTBR_AUTOMATION_2NT", "' . pSQL($ntbr_automation_2nt) . '"),
            ("NTBR_AUTOMATION_2NT_HOURS", "' . pSQL($ntbr_automation_2nt_hours) . '"),
            ("NTBR_AUTOMATION_2NT_IP", "' . pSQL($ntbr_automation_2nt_ip) . '"),
            ("NTBR_AUTOMATION_2NT_MINUTES", "' . pSQL($ntbr_automation_2nt_minutes) . '"),
            ("NTBR_BIG_WEBSITE", "' . pSQL($ntbr_big_website) . '"),
            ("NTBR_BIG_WEBSITE_HIDE", "' . pSQL($ntbr_big_website_hide) . '"),
            ("NTBR_HASH", "' . pSQL($ntbr_hash) . '"),
            ("NTBR_HASH_TEMP", "' . pSQL($ntbr_hash_temp) . '"),
            ("NTBR_LAST_SHOP_URL", "' . pSQL(Apparatus::encrypt($shop_url)) . '"),
            ("NTBR_MULTI_CONFIG", "' . pSQL($ntbr_multi_config) . '"),
            ("NTBR_ONGOING", "' . pSQL($ntbr_ongoing) . '"),
            ("NTBR_ONGOING_ID_CONFIG", "' . pSQL($ntbr_ongoing_id_config) . '"),
            ("NTBR_ONGOING_REFRESH", "' . pSQL($ntbr_ongoing_refresh) . '"),
            ("NTBR_SCAN_ID_CONFIG", "' . pSQL($ntbr_scan_id_config) . '"),
            ("NTBR_SEL", "' . pSQL($ntbr_sel) . '"),
            ("NTBR_SEL_TEMP", "' . pSQL($ntbr_sel_temp) . '");
    ');

    if (!$insert_glob_config) {
        PrestaShopLogger::addLog('Could not insert into global config table. ' . Db::getInstance()->getMsgError(), 3);

        return false;
    }

    return $module;
}
