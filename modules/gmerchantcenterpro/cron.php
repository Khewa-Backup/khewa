<?php
/**
 * Google Merchant Center Pro
 *
 * @author    BusinessTech.fr - https://www.businesstech.fr
 * @copyright Business Tech 2020 - https://www.businesstech.fr
 * @license   Commercial
 *
 *           ____    _______
 *          |  _ \  |__   __|
 *          | |_) |    | |
 *          |  _ <     | |
 *          | |_) |    | |
 *          |____/     |_|
 */

require_once(dirname(__FILE__) . '/../../config/config.inc.php');
require_once(dirname(__FILE__) . '/../../init.php');
require_once(dirname(__FILE__) . '/gmerchantcenterpro.php');


// get the token
$sToken = Tools::getValue('token');

/* instantiate the main class */
$oModule = new GMerchantCenterPro();

if ($sToken == GMerchantCenterPro::$conf['GMCP_FEED_TOKEN']) {
    /* use case - handle to generate XML files */
    $_POST['sAction'] = Tools::getIsset('sAction') ? Tools::getValue('sAction') : 'generate';
    $_POST['sType'] = Tools::getIsset('sType') ? Tools::getValue('sType') : 'cron';

    echo $oModule->getContent();
} else {
    echo 'Invalid security token';
}
