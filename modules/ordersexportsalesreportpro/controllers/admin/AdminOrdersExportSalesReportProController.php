<?php
/**
 *
 * NOTICE OF LICENSE
 *
 *  @author    IntelliPresta <tehran.alishov@gmail.com>
 *  @copyright 2020 IntelliPresta
 *  @license   Commercial License
 */

class AdminOrdersExportSalesReportProController extends ModuleAdminController
{

    public function init()
    {
//        parent::init();



        error_reporting(E_ERROR | E_PARSE);
        ini_set('max_execution_time', 0);
//        ini_set('memory_limit', '-1');

        /*
         * If values have been submitted in the form, process.
         */

        if ((bool) Tools::isSubmit('ajax_action')) {
            $action = Tools::getValue('ajax_action');
            $type = Tools::getValue('type');
            if ($type === 'dml') {
                require_once dirname(__FILE__) . '/../../classes/DataSaver.php';
                $saver = new DataSaver($this->module);
                if (method_exists($saver, $action)) {
                    $saver->{$action}();
                }
            } else {
                require_once dirname(__FILE__) . '/../../classes/DataFilter.php';
                if (method_exists('DataFilter', $action)) {
                    DataFilter::$action($this->module);
                }
            }
        } elseif ((bool) Tools::isSubmit('orders_export_as')) {
            $this->doExport();
            exit;
        } else {
            $actions_list = array(
                'getFile' => 'getFile',
                'getDoc' => 'getDoc'
            );
            $action = Tools::getValue('action');
            if (isset($actions_list[$action])) {
                $this->{$actions_list[$action]}();
            } else {
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules') . '&configure=' . $this->module->name . '&tab_module=' . $this->module->tab . '&module_name=' . $this->module->name);
            }
        }
    }

    public function doExport($param = null)
    {
        require_once dirname(__FILE__) . '/../../classes/SalesExportHelper.php';
        require_once dirname(__FILE__) . '/../../classes/ExportSales.php';
        $orders = new ExportSales($this->module);
        $orders->run($param);
    }

    private function getFile()
    {
        $id = pSQL(Tools::getValue('id'));
        $type = pSQL(Tools::getValue('type'));
        $name = pSQL(Tools::getValue('name'));
        $filePath = realpath(dirname(__FILE__) . '/../../output/' . $id . '.' . ($type === 'excel' ? 'xlsx' : $type));
        $size = filesize($filePath);


//        var_dump($size);
//        die("okman");
        if (file_exists($filePath)) {
            if ($type === 'excel') {
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="' . $name . '.xlsx"');
            } elseif ($type === 'csv') {
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="' . $name . '.csv"');
            } elseif ($type === 'pdf') {
                header('Content-Type: application/pdf');
                header('Content-Disposition: inline; filename="' . $name . '.pdf"');
            }
            header('Content-Length: ' . $size);

            header('Cache-Control: max-age=0');
            // If you're serving to IE 9, then the following may be needed
//            header('Cache-Control: max-age=1');
            // If you're serving to IE over SSL, then the following may be needed
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
            header('Cache-Control: cache, must-revalidate'); // HTTP/1.1

            header('Pragma: public'); // HTTP/1.0
            readfile($filePath);
//            echo file_get_contents($filePath);
            unlink($filePath);
            die;
        } else {
            die($this->l("File doesn't exist."));
        }
    }

    private function getDoc()
    {
        $lang = pSQL(Tools::getValue('lang'));
        $file_path = realpath(dirname(__FILE__) . '/../../doc/readme_' . $lang . '.pdf');
        if (file_exists($file_path)) {
            header('Content-type:application/pdf');
            header('Content-Disposition:inline;filename="Orders Export Pro"');
            readfile($file_path);
            die;
        } else {
            die($this->l("File doesn't exist."));
        }
    }
}
