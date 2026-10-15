<?php
/**
 *
 * NOTICE OF LICENSE
 *
 *  @author    IntelliPresta <tehran.alishov@gmail.com>
 *  @copyright 2020 IntelliPresta
 *  @license   Commercial License
 */

class OrdersExportSalesReportProExportModuleFrontController extends ModuleFrontController
{

    public function init()
    {
        if (Configuration::get('OXSRP_SCHDL_ENABLE') && (Configuration::get('OXSRP_SCHDL_USE_EMAIL') || Configuration::get('OXSRP_SCHDL_USE_FTP'))) {
            if (Tools::getValue('schedule') === md5(Configuration::get('OXSRP_SECURE_KEY'))) {
                error_reporting(E_ERROR | E_PARSE);
                ini_set('max_execution_time', 0);
                $this->doExport('schedule');
                die;
            } else {
                http_response_code(403);
                die($this->module->l('Secure key is incorrect.'));
            }
        } else {
            http_response_code(401);
            die($this->module->l('No schedule is enabled in the module.'));
        }
    }

    public function doExport($param = null)
    {
        require_once dirname(__FILE__) . '/../../classes/SalesExportHelper.php';
        require_once dirname(__FILE__) . '/../../classes/ExportSales.php';
        $products = new ExportSales($this->module);
        $products->run($param);
    }
}
