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


class gmerchantcenterproshoppingactionModuleFrontController extends ModuleFrontController
{
    /**
     * init() method init module front controller
     */
    public function init()
    {
        // exec parent
        parent::init();
        // include main module class
        require_once($this->module->getLocalPath() . 'gmerchantcenterpro.php');
        require_once(_GMCP_PATH_CONF . 'hook.conf.php');
        require_once(_GMCP_GSA_LIB . 'gsa-ctrl_class.php');
    }

    /**
     * method manage post data
     *
     * @throws Exception
     * @return bool
     */
    public function postProcess()
    {
        // Khewa PS 9.2: SECURITY. This page had no check at all: anyone could create customers, carts,
        // orders and products, or change order states, by posting type/action. It now needs the module's
        // feed token (same as the cron and feed links). Google's "Buy on Google" (Shopping Actions) has
        // been closed by Google, so nothing legit calls this page without the token.
        $sKhewaToken = (string) Configuration::get('GMCP_FEED_TOKEN');
        $sKhewaGiven = Tools::getValue('token');
        if ($sKhewaToken === '' || !is_string($sKhewaGiven) || !hash_equals($sKhewaToken, $sKhewaGiven)) {
            header('HTTP/1.1 403 Forbidden');
            die('Access denied');
        }
        $aParams = $_POST;
        $sResponse = '';
        // Khewa PS 9.2: only plain names for the class file name built from "type"
        if (!empty($aParams['type']) && !preg_match('/^[a-z]+$/', (string) $aParams['type'])) {
            die('Access denied');
        }

        // Init module object
        $oModule = new GMerchantCenterPro();
    
        if (!empty($aParams['action']) && !empty($aParams['type'])) {
            $oSyncOrdersController = new GsaCtrl($aParams['type'], $aParams['action']);
        }
        $sResponse = $oSyncOrdersController->run($aParams);

        if (!is_object($oModule)) {
            throw new Exception("can\'t load merchant center class", 101);
        }

        die(Tools::jsonEncode($sResponse));
    }
}
