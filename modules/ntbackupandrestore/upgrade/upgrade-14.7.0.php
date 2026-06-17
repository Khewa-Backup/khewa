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

function upgrade_module_14_7_0($module)
{
    $googledrive_client_id = GlobConfNtbr::get('NTBR_GOOGLEDRIVE_CLIENT_ID');
    $googledrive_client_secret = GlobConfNtbr::get('NTBR_GOOGLEDRIVE_CLIENT_SECRET');

    // Check that Google Drive client ID exists
    if (!$googledrive_client_id || $googledrive_client_id == '') {
        if (!GlobConfNtbr::set('NTBR_GOOGLEDRIVE_CLIENT_ID', '')) {
            PrestaShopLogger::addLog('Google Drive client ID cannot be initialized.', 3);

            return false;
        }
    }

    // Check that Google Drive client secret exists
    if (!$googledrive_client_secret || $googledrive_client_secret == '') {
        if (!GlobConfNtbr::set('NTBR_GOOGLEDRIVE_CLIENT_SECRET', '')) {
            PrestaShopLogger::addLog('Google Drive client secret cannot be initialized.', 3);

            return false;
        }
    }

    return $module;
}
