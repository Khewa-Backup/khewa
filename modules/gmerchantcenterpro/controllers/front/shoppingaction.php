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
        $aParams = $_POST;
        $sResponse = '';

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
