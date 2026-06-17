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

function upgrade_module_11_1_0($module)
{
    $new_ignored_dir = 'upload';

    $ignore_directories = Db::getInstance()->executeS('
        SELECT `id_ntbr_config`, `ignore_directories`
        FROM `' . _DB_PREFIX_ . 'ntbr_config`;
    ');

    foreach ($ignore_directories as $config) {
        if (strpos($config['ignore_directories'], $new_ignored_dir) === false) {
            if (trim($config['ignore_directories']) != '') {
                $config['ignore_directories'] .= ',';
            }

            $config['ignore_directories'] .= $new_ignored_dir;

            $update_ignore_directories = Db::getInstance()->execute('
                UPDATE `' . _DB_PREFIX_ . 'ntbr_config`
                SET `ignore_directories` = "' . pSQL($config['ignore_directories']) . '"
                WHERE `id_ntbr_config` = ' . (int) $config['id_ntbr_config'] . ';
            ');

            if (!$update_ignore_directories) {
                return false;
            }
        }
    }

    return $module;
}
