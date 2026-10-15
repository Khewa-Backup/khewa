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

function upgrade_module_11_2_10($module)
{
    if (file_exists(_PS_ROOT_DIR_ . '/modules/ntbackupandrestore/classes/Aws.php')) {
        unlink(_PS_ROOT_DIR_ . '/modules/ntbackupandrestore/classes/Aws.php');
    }

    if (file_exists(_PS_ROOT_DIR_ . '/modules/ntbackupandrestore/classes/Dropbox.php')) {
        unlink(_PS_ROOT_DIR_ . '/modules/ntbackupandrestore/classes/Dropbox.php');
    }

    if (file_exists(_PS_ROOT_DIR_ . '/modules/ntbackupandrestore/classes/Ftp.php')) {
        unlink(_PS_ROOT_DIR_ . '/modules/ntbackupandrestore/classes/Ftp.php');
    }

    if (file_exists(_PS_ROOT_DIR_ . '/modules/ntbackupandrestore/classes/Googledrive.php')) {
        unlink(_PS_ROOT_DIR_ . '/modules/ntbackupandrestore/classes/Googledrive.php');
    }

    if (file_exists(_PS_ROOT_DIR_ . '/modules/ntbackupandrestore/classes/Hubic.php')) {
        unlink(_PS_ROOT_DIR_ . '/modules/ntbackupandrestore/classes/Hubic.php');
    }

    if (file_exists(_PS_ROOT_DIR_ . '/modules/ntbackupandrestore/classes/Onedrive.php')) {
        unlink(_PS_ROOT_DIR_ . '/modules/ntbackupandrestore/classes/Onedrive.php');
    }

    if (file_exists(_PS_ROOT_DIR_ . '/modules/ntbackupandrestore/classes/Owncloud.php')) {
        unlink(_PS_ROOT_DIR_ . '/modules/ntbackupandrestore/classes/Owncloud.php');
    }

    if (file_exists(_PS_ROOT_DIR_ . '/modules/ntbackupandrestore/classes/Sugarsync.php')) {
        unlink(_PS_ROOT_DIR_ . '/modules/ntbackupandrestore/classes/Sugarsync.php');
    }

    if (file_exists(_PS_ROOT_DIR_ . '/modules/ntbackupandrestore/classes/Webdav.php')) {
        unlink(_PS_ROOT_DIR_ . '/modules/ntbackupandrestore/classes/Webdav.php');
    }

    return $module;
}
