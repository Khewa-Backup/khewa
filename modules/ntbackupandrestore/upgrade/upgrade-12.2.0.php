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

function upgrade_module_12_2_0($module)
{
    // We initialize the configuration for all shops
    $shops = Shop::getShops();

    foreach ($shops as $shop) {
        if (!Configuration::updateValue('NTBR_ONGOING_REFRESH', 0, false, $shop['id_shop_group'], $shop['id_shop'])) {
            PrestaShopLogger::addLog('The configuration cannot be created: Check ongoing refresh config cannot be created.', 3);

            return false;
        }
    }

    return $module;
}
