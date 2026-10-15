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

function upgrade_module_11_1_1($module)
{
    $get_column_config = Db::getInstance()->executeS('
        SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'ntbr_config`;
    ');

    $config_columns = [];

    foreach ($get_column_config as $conf) {
        $config_columns[] = $conf['Field'];
    }

    if (!in_array('backup_dir', $config_columns) || !in_array('create_on_distant', $config_columns)) {
        $add = '';

        if (!in_array('backup_dir', $config_columns) && !in_array('create_on_distant', $config_columns)) {
            $add = '
                ADD `backup_dir` TEXT AFTER `js_download`,
                ADD `create_on_distant` tinyint(1) NOT NULL DEFAULT "0" AFTER `delete_local_backup`
            ';
        } elseif (!in_array('backup_dir', $config_columns)) {
            $add = 'ADD `backup_dir` TEXT AFTER `js_download`';
        } else {
            $add = 'ADD `create_on_distant` tinyint(1) NOT NULL DEFAULT "0" AFTER `delete_local_backup`';
        }

        $update_table_config = Db::getInstance()->execute('
            ALTER TABLE `' . _DB_PREFIX_ . 'ntbr_config`
            ' . $add . ';
        ');

        if (!$update_table_config) {
            return false;
        }

        if (!in_array('backup_dir', $config_columns)) {
            $physic_path_modules = realpath(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'modules') . DIRECTORY_SEPARATOR;
            $backup_dir = $physic_path_modules . $module->name . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR;

            $update_backup_dir = Db::getInstance()->execute('
                UPDATE `' . _DB_PREFIX_ . 'ntbr_config`
                SET `backup_dir` = "' . pSQL($backup_dir) . '";
            ');

            if (!$update_backup_dir) {
                return false;
            }
        }
    }

    return $module;
}
