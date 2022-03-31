<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 * We offer the best and most useful modules PrestaShop and modifications for your online store.
 *
 * @author    knowband.com <support@knowband.com>
 * @copyright 2017 Knowband
 * @license   see file: LICENSE.txt
 * @category  PrestaShop Module
 */

//Include Etsy Module Class to inherit some common functions and callbacks
require_once(_PS_MODULE_DIR_ . 'kbetsy/classes/EtsyModule.php');


class AdminEtsySalesReportController extends ModuleAdminController
{

    //Class Constructor
    public function __construct()
    {
        $this->context = Context::getContext();
        $this->bootstrap = true;

        parent::__construct();
        $this->toolbar_title = $this->module->l('Etsy Sales Report', 'AdminEtsySalesReportController');
        $this->display = 'view';
    }

    //Set JS and CSS
    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $this->addJS($this->getModuleDirUrl() . $this->module->name . '/views/js/script.js');
        $this->addCSS($this->getModuleDirUrl() . $this->module->name . '/views/css/style.css');
        $this->addJS($this->getModuleDirUrl() . $this->module->name . '/views/js/velovalidation.js');
        $this->addJS($this->getModuleDirUrl() . $this->module->name . '/views/js/validate_admin.js');
        $this->addCSS($this->getModuleDirUrl() . $this->module->name . '/views/css/custom.css');

        $dir = '/views/js/';
//        if (_PS_VERSION_ < '1.6.0') {
//            $this->addJS($this->getModuleDirUrl() . $this->module->name . $dir . 'flot/jquery.flot121.js');
//        } else {
//            $this->context->controller->addJqueryPlugin('flot');
//        }

//        $this->addJS($this->getModuleDirUrl() . $this->module->name . $dir . 'flot/jquery.flot.pie.min.js');
//        $this->addJS($this->getModuleDirUrl() . $this->module->name . $dir . 'flot/jquery.flot.axislabels.js');
//        $this->addJS($this->getModuleDirUrl() . $this->module->name . $dir . 'flot/jquery.flot.orderBars.js');
//        $this->addJS($this->getModuleDirUrl() . $this->module->name . $dir . 'flot/jquery.flot.tooltip_0.5.js');
//        $this->addCSS($this->getModuleDirUrl() . $this->module->name . '/views/css/jquery.easy-pie-chart.css');
//        if (preg_match('/(?i)msie 8.0/', $_SERVER['HTTP_USER_AGENT'])) {
//            $this->addJS($this->getModuleDirUrl() . $this->module->name . $dir . 'flot/excanvas.js');
//        }
        $this->addJS($this->getModuleDirUrl() . $this->module->name . $dir . 'morries/morris.min.js');
        $this->addCSS($this->getModuleDirUrl() . $this->module->name . '/views/css/morris.css');
        $this->addJS($this->getModuleDirUrl() . $this->module->name . '/views/js/raphael.min.js');
    }

    public function initPageHeaderToolbar()
    {
        $this->page_header_toolbar_title = $this->module->l('Etsy Sales Report', 'AdminEtsySalesReportController');
        parent::initPageHeaderToolbar();
    }

    public function renderView()
    {
        $this->context->smarty->assign(array(
            'module_path' => $this->context->link->getAdminLink('AdminEtsySalesReport', true),
            'loader' => $this->getModuleDirUrl().$this->module->name.'/views/img/spinner.gif',
        ));
        $tpl =  $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name.'/views/templates/admin/velovalidation.tpl');
        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name.'/views/templates/admin/sale_report.tpl').$tpl;
    }

    public function postProcess()
    {
        if (Tools::isSubmit('ajax')) {
            if (Tools::isSubmit('getChart')) {
                $start_date = Tools::getValue('start');
                $end_date = Tools::getValue('end');
                $groupby = Tools::getValue('groupby');
                $json = $this->displayChartData($start_date, $end_date, $groupby);
                $this->context->smarty->assign(array(
                    'data' => $json['data_table'],
                    'is_report_display' => true,
                    'module_path' => $this->context->link->getAdminLink('AdminEtsySalesReport', true),
                     'loader' => $this->getModuleDirUrl().$this->module->name.'/views/img/spinner.gif',
                ));
                $tpl = $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name.'/views/templates/admin/sale_report.tpl');
                $data = array();
                $data['table'] = $tpl;
                $data['graph'] = $json['data_graph'];
                header('Content-Type: application/json', true);
                echo Tools::jsonEncode($data);
                die;
            }
            die;
        }
        return parent::postProcess();
    }

    public function displayChartData($from, $to, $groupby)
    {
        $orderDataArray = array();
        $range = '';
        if ($groupby == 'days') {
            $range = 'day';
        } elseif ($groupby == 'years') {
            $range = 'year';
        } elseif ($groupby == 'months') {
            $range = 'month';
        }
        $filter_string = '';
        switch ($range) {
            case 'day':
                $filter_string = ' and date(date_added) >="' . pSQL($from) . '"
					and date(date_added) <="' . pSQL($to) . '" group by YEAR(date_added), MONTH(date_added), DAY(date_added)';
                $total_orders = $this->getCartsBasedOnFilters($filter_string);
                $ordersArray = array();
                $found = false;
                $date_diff = abs(strtotime($to) - strtotime($from));
                $days = (int) floor($date_diff / (60 * 60 * 24));
                for ($i = $days; $i >= 0; $i--) {
                    $date = date("Y-m-d", strtotime("- " . $i . " days", strtotime($to)));
                    foreach ($total_orders as $orders) {
                        if ($date == date("Y-m-d", strtotime($orders['date_added']))) {
                            $ordersArray['total'] = $orders['order_total'];
                            $ordersArray['count'] = $orders['order_count'];
                            $ordersArray['walmart_order_date'] = $orders['date_added'];
                            $ordersArray['product_total'] = $orders['total_product'];
                            $ordersArray['label'] = date("d-M-Y", strtotime($date));
                            $orderDataArray[$date] = $ordersArray;
                            $found = true;
                        }
                    }
                    if ($found == false) {
                        $orderDataArray[$date] = array("total" => 0, "count" => 0, 'label' => date("d-M-Y", strtotime($date)));
                    }
                }
                $data = $orderDataArray;
                break;

            case 'month':
                $filter_string = 'and date(date_added) >="' . pSQL($from) . '" AND date(date_added) <= "' .
                        pSQL($to) . '" group by YEAR(date_added),MONTH(date_added)';
                $total_orders = $this->getCartsBasedOnFilters($filter_string);
                $ordersArray = array();
                $found = false;
                $start_date_month = (int) date("m", strtotime($from));
                $end_date_month = (int) date("m", strtotime($to));
                $start_date_year = (int) date("Y", strtotime($from));
                $end_date_year = (int) date("Y", strtotime($to));

                $date_arr = array();
                for ($i = $start_date_year; $i <= $end_date_year; $i++) {
                    if ($i == $start_date_year) {
                        $k = $start_date_month;
                    } else {
                        $k = 1;
                    }

                    if ($i == $end_date_year) {
                        $l = $end_date_month;
                    } else {
                        $l = 12;
                    }

                    for ($j = $k; $j <= $l; $j++) {
                        $date_arr[] = date("Y-m", strtotime($i . "-" . $j));
                    }
                }
                foreach ($date_arr as $date) {
                    $found = false;
                    foreach ($total_orders as $orders) {
                        if ($date == date("Y-m", strtotime($orders['date_added']))) {
//                            $orderObj = new Order($orders['id_order']);
                            $ordersArray['total'] = $orders['order_total'];
                            $ordersArray['count'] = $orders['order_count'];
                            $ordersArray['walmart_order_date'] = $orders['date_added'];
                            $ordersArray['product_total'] = $orders['total_product'];
//                            $date = date("Y-m-d", strtotime($orders['date_added']));
                            $ordersArray['label'] = date("M-Y", strtotime($date));
                            $orderDataArray[$date] = $ordersArray;
                            $found = true;
                        }
                    }
                    if ($found == false) {
                        $orderDataArray[$date] = array("total" => 0, "count" => 0, 'label' => date("M-Y", strtotime($date)));
                    }
                    $data = $orderDataArray;
                }
//                }
                break;
            case 'year':
                $filter_string = 'and  date(date_added) >= "' . pSQL($from) . '"
					and date(date_added) <="' . pSQL($to) . '" group by YEAR(date_added)';
                $total_orders = $this->getCartsBasedOnFilters($filter_string);
                $start_date_year = (int) date("Y", strtotime($from));
                $end_date_year = (int) date("Y", strtotime($to));

                $date_arr = array();
                for ($i = $start_date_year; $i <= $end_date_year; $i++) {
                    $date_arr[] = (string) $i;
                }

                $ordersArray = array();
                foreach ($date_arr as $date) {
                    $found = false;
                    foreach ($total_orders as $orders) {
                        if ($date == date("Y", strtotime($orders['date_added']))) {
//                            $orderObj = new Order($orders['id_order']);
                             $ordersArray['total'] = $orders['order_total'];
                            $ordersArray['count'] = $orders['order_count'];
                            $ordersArray['walmart_order_date'] = $orders['date_added'];
                            $ordersArray['product_total'] = $orders['total_product'];
//                            $date = date("Y-m-d", strtotime($orders['date_added']));
                            $ordersArray['label'] = $date;
                            $orderDataArray[$date] = $ordersArray;
                            $found = true;
                        }
                    }
                    if ($found == false) {
                        $orderDataArray[$date] = array("total" => 0, "count" => 0, 'label' => $date);
                    }
                }
                $data = $orderDataArray;
                break;
        }

        $orderTableArray = array();

        $filter_string = ' and date(date_added) >="' . pSQL($from) . '"
					and date(date_added) <="' . pSQL($to) . '" group by YEAR(date_added), MONTH(date_added), DAY(date_added)';
        $total_orders = $this->getCartsBasedOnFilters($filter_string);
        $ordersArray = array();
        $found = false;
        $date_diff = abs(strtotime($to) - strtotime($from));
        $days = (int) floor($date_diff / (60 * 60 * 24));
        for ($i = $days; $i >= 0; $i--) {
            $date = date("Y-m-d", strtotime("- " . $i . " days", strtotime($to)));
            foreach ($total_orders as $orders) {
                if ($date == date("Y-m-d", strtotime($orders['date_added']))) {
                    $ordersArray['total'] = $orders['order_total'];
                    $ordersArray['count'] = $orders['order_count'];
                    $ordersArray['walmart_order_date'] = $orders['date_added'];
                    $ordersArray['product_total'] = $orders['total_product'];
                    $ordersArray['label'] = date("d-M-Y", strtotime($date));
                    $orderTableArray[$date] = $ordersArray;
                    $found = true;
                }
            }
            if ($found == false) {
                $orderTableArray[$date] = array("total" => 0, "count" => 0, 'label' => date("d-M-Y", strtotime($date)));
            }
        }
        $data = $orderDataArray;
        $item = array();
        $item['data_graph'] = $orderDataArray;
        $item['data_table'] = $orderTableArray;
        return $item;
    }

    public function getCartsBasedOnFilters($filters_string = '')
    {
        $carts_query = 'SELECT sum(o.total_paid_tax_incl) as order_total, count(l.id_order) as order_count, date_added, count(od.product_id) as total_product FROM '._DB_PREFIX_.'etsy_orders_list l INNER JOIN '._DB_PREFIX_.'orders o on (o.id_order=l.id_order) LEFT JOIN '._DB_PREFIX_.'order_detail od on (o.id_order=od.id_order AND od.id_shop='.(int)$this->context->shop->id.') where 1 '.$filters_string;
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($carts_query);
    }

    private function getModuleDirUrl()
    {
        $module_dir = '';
        if ($this->checkSecureUrl()) {
            $module_dir = _PS_BASE_URL_SSL_ . __PS_BASE_URI__ . str_replace(_PS_ROOT_DIR_ . '/', '', _PS_MODULE_DIR_);
        } else {
            $module_dir = _PS_BASE_URL_ . __PS_BASE_URI__ . str_replace(_PS_ROOT_DIR_ . '/', '', _PS_MODULE_DIR_);
        }
        return $module_dir;
    }

    private function checkSecureUrl()
    {
        $custom_ssl_var = 0;

        if (isset($_SERVER['HTTPS'])) {
            if ($_SERVER['HTTPS'] == 'on') {
                $custom_ssl_var = 1;
            }
        } else if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            $custom_ssl_var = 1;
        }



        if ((bool) Configuration::get('PS_SSL_ENABLED') && $custom_ssl_var == 1) {
            return true;
        } else {
            return false;
        }
    }
}
