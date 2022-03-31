<?php
/**
 * 2007-2018 PrestaShop
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2018 PrestaShop SA
 * @license   http://addons.prestashop.com/en/content/12-terms-and-conditions-of-use
 * International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_3_1_1($module)
{
	// Registration order status
    if (!$module->createOS()) {
        return false;
    }
	
    if (!Db::getInstance()->Execute("ALTER TABLE `"._DB_PREFIX_."stripejs_transaction` CHANGE `status` `status` ENUM('paid','unpaid','uncaptured','failed','canceled')  NOT NULL")) {
        return false;
    }
    
    return true;
}
