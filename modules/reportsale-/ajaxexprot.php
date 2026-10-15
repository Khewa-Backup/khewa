<?php
/**
 * 2007-2021 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@buy-addons.com so we can send you a copy.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@buy-addons.com>
 *  @copyright 2007-2021 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

include_once('../../config/config.inc.php');
// if maintaince mode enable
$remote_ip = Tools::getRemoteAddr();
if (!(int)Configuration::get('PS_SHOP_ENABLE')) {
    if (!in_array($remote_ip, explode(',', Configuration::get('PS_MAINTENANCE_IP')))) {
        if (!Configuration::get('PS_MAINTENANCE_IP')) {
            Configuration::updateValue('PS_MAINTENANCE_IP', $remote_ip);
        } else {
            Configuration::updateValue('PS_MAINTENANCE_IP', Configuration::get('PS_MAINTENANCE_IP') . ',' . $remote_ip);
        }
    }
}
include_once('../../init.php');
include_once(_PS_MODULE_DIR_ . 'reportsale/reportsale.php');
$reportsale = new ReportSale();
set_time_limit(0);
$ba_str = Tools::getValue('ba_str_data');
$prefix = Tools::getValue('prefix_ba');
$arr=trim($ba_str, ",");
$id_arr = explode(',', $arr);
$reportsale->addnewExportData($id_arr, $prefix);
