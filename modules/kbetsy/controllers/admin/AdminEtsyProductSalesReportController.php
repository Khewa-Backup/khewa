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


class AdminEtsyProductSalesReportController extends ModuleAdminController
{

    //Class Constructor
    public function __construct()
    {
        $this->context = Context::getContext();
        $this->bootstrap = true;

        parent::__construct();
        $this->toolbar_title = $this->module->l('Product Sales Report', 'AdminEtsyProductSalesReportController');
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
    }

    public function initPageHeaderToolbar()
    {
        $this->page_header_toolbar_title = $this->module->l('Product Sales Report', 'AdminEtsyProductSalesReportController');
        parent::initPageHeaderToolbar();
    }

    public function renderView()
    {
        $lang =  Configuration::get('etsy_default_lang') != '' ? Configuration::get('etsy_default_lang') : Context::getContext()->language->id;
        $productDetails = Db::getInstance()->executeS('SELECT l.*, pl.name FROM '._DB_PREFIX_.'etsy_products_list l JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (l.`id_product` = pl.`id_product`) AND id_lang = ' . (int) $lang.' WHERE listing_id !=""');
        $this->context->smarty->assign(array(
            'module_path' => $this->context->link->getAdminLink('AdminEtsyProductSalesReport', true),
            'productDetails' => $productDetails,
            'loader' => $this->getModuleDirUrl().$this->module->name.'/views/img/spinner.gif',
        ));
        $tpl =  $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name.'/views/templates/admin/velovalidation.tpl');
        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name.'/views/templates/admin/product_sale_report.tpl').$tpl;
    }
    
    public function postProcess()
    {
        if (Tools::isSubmit('ajax')) {
            if (Tools::isSubmit('getProductSaleReport')) {
                $skus = Tools::getValue('skus');
                $start_date = Tools::getValue('start');
                $end_date = Tools::getValue('end');
                $groupby = Tools::getValue('groupby');
                $json = $this->displayProductSaleReportData($skus, $start_date, $end_date, $groupby);
                $data = array();
                $data['table'] = $json;
                header('Content-Type: application/json', true);
                echo Tools::jsonEncode($data);
                die;
            }
        }
    }
    
    public function displayProductSaleReportData($skus, $from, $to, $groupby)
    {
        $orderSkuData = array();
        if (isset($from) && isset($to) && isset($groupby)) {
            $sku_arr = array();
            if (!empty($skus)) {
                $sku_arr = explode(',', $skus);
            }
            $orderDataArray = array();
            $filter_string = '';
            if ($groupby == 'days') {
                $filter_string = ' and date(date_added) >="' . pSQL($from) . '" 
					and date(date_added) <="' . pSQL($to) . '" group by l.id_order,YEAR(date_added), MONTH(date_added), DAY(date_added) ,p.reference,date_added';
                $total_orders = $this->getSaleBasedOnFilters($filter_string);
                $date_diff = abs(strtotime($to) - strtotime($from));
                $days = (int) floor($date_diff / (60 * 60 * 24));
                for ($i = $days; $i >= 0; $i--) {
                    $date = date("Y-m-d", strtotime("- " . $i . " days", strtotime($to)));
                    $found = false;
                    foreach ($total_orders as $order) {
                        if ($date == date("Y-m-d", strtotime($order['date_added'])) && in_array($order['reference'], $sku_arr)) {
                            $orderDataArray[$date] = array("sku" => $order['reference'],"product_name" => $order['product_name'],"total_product" => $order['total_product'], "total" => $order['order_total'], "count" => $order['order_count'], 'walmart_order_date' => $order['date_added'], 'label' => date("d-M-Y", strtotime($date)));
                            $found = true;
                            break;
                        }
                    }
                    if ($found == false) {
                        $orderDataArray[$date] = array("total" => 0, "count" => 0, 'label' => date("d-M-Y", strtotime($date)));
                    }
                }
            } else if ($groupby == 'months') {
                $filter_string = ' and date(date_added) >="' . pSQL($from) . '" 
					and date(date_added) <="' . pSQL($to) . '" group by l.id_order,YEAR(date_added), MONTH(date_added) ,p.reference,date_added';
                $total_orders = $this->getSaleBasedOnFilters($filter_string);

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
                    foreach ($total_orders as $order) {
                        if ($date == date("Y-m", strtotime($order['date_added'])) && in_array($order['reference'], $sku_arr)) {
                            $orderDataArray[$date] = array("sku" => $order['reference'],"product_name" => $order['product_name'], "total_product" => $order['total_product'], "total" => $order['order_total'], "count" => $order['order_count'], 'walmart_order_date' => $order['date_added'], 'label' => date("M-Y", strtotime($date)));
                            $found = true;
                            break;
                        }
                    }
                    if ($found == false) {
                        $orderDataArray[$date] = array("total" => 0, "count" => 0, 'label' => date("M-Y", strtotime($date)));
                    }
                }
            } else if ($groupby == 'years') {
                 $filter_string = ' and date(date_added) >="' . pSQL($from) . '" 
					and date(date_added) <="' . pSQL($to) . '" group by l.id_order,YEAR(date_added),p.reference,date_added';
                $total_orders = $this->getSaleBasedOnFilters($filter_string);

                $start_date_year = (int) date("Y", strtotime($from));
                $end_date_year = (int) date("Y", strtotime($to));

                $date_arr = array();
                for ($i = $start_date_year; $i <= $end_date_year; $i++) {
                    $date_arr[] = (string) $i;
                }

                foreach ($date_arr as $date) {
                    $found = false;
                    foreach ($total_orders as $order) {
                        if ($date == date("Y", strtotime($order['date_added'])) && in_array($order['reference'], $sku_arr)) {
                            $orderDataArray[$date] = array("sku" => $order['reference'],"product_name" => $order['product_name'], "total_product" => $order['total_product'], "total" => $order['order_total'], "count" => $order['order_count'], 'walmart_order_date' => $order['date_added'], 'label' => $date);
                            $found = true;
                            break;
                        }
                    }
                    if ($found == false) {
                        $orderDataArray[$date] = array("total" => 0, "count" => 0, 'label' => $date);
                    }
                }
            }
            
            
            foreach ($sku_arr as $ordersku) {
                $found = false;
                foreach ($orderDataArray as $order) {
                    if (isset($order['sku']) && $order['sku'] == $ordersku) {
                        $found = true;
                        $orderSkuData[] = array("sku" => $order['product_name'].'('.$order['sku'].')', "count" => $order['count'], 'total' => $order['total'],'total_product' => $order['total_product']);
                        break;
                    }
                }
            }
        }
        return $orderSkuData;
    }
    
    private function getSaleBasedOnFilters($filters_string = '')
    {
        
        $carts_query = 'SELECT p.reference ,pl.name as product_name ,sum(o.total_paid_tax_incl) as order_total, count(l.id_order) as order_count, date_added, count(od.product_id) as total_product '
            . 'FROM '._DB_PREFIX_.'etsy_orders_list l INNER JOIN '._DB_PREFIX_.'orders o on (o.id_order=l.id_order) '
            . 'LEFT JOIN '._DB_PREFIX_.'order_detail od on (o.id_order=od.id_order AND od.id_shop='.(int)$this->context->shop->id.') '
            . 'LEFT JOIN `'._DB_PREFIX_.'product` p ON (p.id_product = od.product_id) '
            . 'LEFT JOIN '._DB_PREFIX_.'product_lang pl on (p.id_product = pl.id_product AND pl.id_shop= od.id_shop AND pl.id_lang = '.(int)Context::getContext()->language->id.') '
            . 'LEFT JOIN `'._DB_PREFIX_.'product_shop` ps ON (ps.id_product = p.id_product AND ps.id_shop = od.id_shop) where 1 '.$filters_string;
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
