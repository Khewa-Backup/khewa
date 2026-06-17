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

function upgrade_module_11_6_7($module)
{
    $src_folder = _PS_ROOT_DIR_ . '/src';

    remove_empty_index($src_folder);

    return $module;
}

function remove_empty_index($folder)
{
    foreach (glob($folder . '/*') as $filename) {
        if (is_dir($filename)) {
            remove_empty_index($filename);
        } else {
            if (basename($filename) == 'index.php' && filesize($filename) == 0) {
                @unlink($filename);
            }
        }
    }
}
