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

class ReportSale extends Module
{
    private $viewbasic;
    private $viewtaxonly;
    private $viewprofit;
    private $viewfull;
    private $viewproduct;
    private $viewbrand;
    private $viewsupplier;
    private $viewcategory;
    private $viewcustomer;
    private $viewstorecredit;
    public $export_without_cs = false;
    public $pname_with_breakline = true;

    public function __construct()
    {
        $this->name = "reportsale";
        $this->tab = "analytics_stats";
        $this->version = "1.0.27";
        $this->author = "buy-addons";
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->module_key = '7ffe178f88fcee22d8f22732f14d9b60';

        parent::__construct();

        $this->displayName = $this->l('Report Sale, Tax, Profit');
        $this->description = $this->l('Author: buy-addons');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        require_once(_PS_MODULE_DIR_ . $this->name . '/classes/reportsaledata.php');
        require_once(_PS_MODULE_DIR_ . $this->name . '/classes/baviewbasic.php');
        require_once(_PS_MODULE_DIR_ . $this->name . '/classes/baviewtaxonly.php');
        require_once(_PS_MODULE_DIR_ . $this->name . '/classes/baviewprofit.php');
        require_once(_PS_MODULE_DIR_ . $this->name . '/classes/baviewfull.php');
        require_once(_PS_MODULE_DIR_ . $this->name . '/classes/baviewproduct.php');
        require_once(_PS_MODULE_DIR_ . $this->name . '/classes/baviewbrand.php');
        require_once(_PS_MODULE_DIR_ . $this->name . '/classes/baviewsupplier.php');
        require_once(_PS_MODULE_DIR_ . $this->name . '/classes/baviewcategory.php');
        require_once(_PS_MODULE_DIR_ . $this->name . '/classes/baviewcustomer.php');
        require_once(_PS_MODULE_DIR_ . $this->name . '/classes/baviewstocredit.php');
    }

    public function install()
    {
        if (!parent::install() || !$this->registerHook('backOfficeHeader')) {
            return false;
        }
        $datacronjob = '{"hour":"-1","day":"-1","month":"-1","day_of_week":"-1","tableex":[]}';
        $shopArrayList = Shop::getShops(false);
        foreach ($shopArrayList as $shopArray) {
            $id_shop = $shopArray['id_shop'];
            $id_shop_group = $shopArray['id_shop_group'];
            Configuration::updateValue('basettgcronj', $datacronjob, false, $id_shop_group, $id_shop);
        }
        $install = new ReportSaleData();
        $install->createTableInstall();
        // since 1.0.20
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminReportSale';
        $tab->name = array();
        $tabParentName = 'SELL';
        $tab->position = 3;
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Report Sales';
        }
        if ($tabParentName) {
            $tab->id_parent = (int) Tab::getIdFromClassName($tabParentName);
        } else {
            $tab->id_parent = 1;
        }
        $tab->module = $this->name;
        $tab->add();
        $tab->save();
        return true;
    }
    public function hookBackOfficeHeader()
    {
        $js = '<meta http-equiv="Cache-control" content="no-cache">';
        return $js;
    }
	public function getProductTaxesBreakdown($order)
    {
        $breakdown = array();
        $details = $order->getProductTaxesDetails();
        foreach ($details as $row) {
            $rate = sprintf('%.3f', $row['tax_rate']);
            if (!isset($breakdown[$rate])) {
                $breakdown[$rate] = array(
                    'total_price_tax_excl' => 0,
                    'total_amount' => 0,
                    'id_tax' => $row['id_tax'],
                    'rate' => $rate,
                );
            }

            $breakdown[$rate]['total_price_tax_excl'] += $row['total_tax_base'];
            $breakdown[$rate]['total_amount'] += $row['total_amount'];
        }

        foreach ($breakdown as $rate => $data) {
            $breakdown[$rate]['total_price_tax_excl'] = Tools::ps_round($data['total_price_tax_excl'], _PS_PRICE_COMPUTE_PRECISION_, $order->round_mode);
            $breakdown[$rate]['total_amount'] = Tools::ps_round($data['total_amount'], _PS_PRICE_COMPUTE_PRECISION_, $order->round_mode);
        }
        ksort($breakdown);
        return $breakdown;
    }
    public function getContent()
    {
		/*$install = new ReportSaleData();
        $install->deleteTableUninstall();
        $install->createTableInstall();
		die;*/
		
        $country = new Country();
        $get_coutry = $country->getCountries($this->context->language->id);
        $this->smarty->assign('get_coutry', $get_coutry);

        $token = Tools::getAdminTokenLite('AdminModules');
        $status_filtering = OrderState::getOrderStates($this->context->language->id);
        $count_filtering = count($status_filtering);

        $this->smarty->assign('status_filtering', $status_filtering);
        $this->smarty->assign('count_filtering', $count_filtering);

        /* Tab */
        $this->viewbasic = new BaViewBasic();
        $this->viewtaxonly = new BaViewTaxOnly();
        $this->viewprofit = new BaViewProfit();
        $this->viewfull = new BaViewfull();
        $this->viewproduct = new BaViewProduct();
        $this->viewbrand = new BaViewBrand();
        $this->viewsupplier = new BaViewSupplier();
        $this->viewcategory = new BaViewCategory();
        $this->viewcustomer = new BaViewCustomer();
        $this->viewstorecredit = new BaViewStoCredit();
        $tabvalue = Tools::getValue('task');
        if (Tools::getValue('task') == null) {
            $tabvalue = 'basic';
        }
        // check export csv
        $token_csv = Tools::getValue('csv');
        $token_downloadcsv = Tools::getValue('downloadcsv');
        if ($token_csv == "basic") {
            $this->basicdata();
        }
        if ($token_csv == "taxes") {
            $this->taxesdata();
        }
        if ($token_csv == "revenue") {
            $this->revenuedata();
        }
        if ($token_csv == "all") {
            $this->alldata();
        }
        if ($token_csv == "product") {
            $this->productdata();
        }
        if ($token_csv == "manufacturers") {
            $this->manufacturersdata();
        }
        if ($token_csv == "supplier") {
            $this->supplierdata();
        }
        if ($token_csv == "category") {
            $this->categorydata();
        }
        if ($token_csv == "client") {
            $this->clientdata();
        }
        if ($token_csv == "creditslips") {
            $this->creditslipsdata();
        }
        // end export
        $reportsale_module = AdminController::$currentIndex;
        $configure = $this->name;
        $ba_url = Tools::getShopProtocol() . Tools::getHttpHost() . __PS_BASE_URI__;
        $linkcronj = "0 * * * * curl \"".$ba_url."modules/".$this->name."/autoreportsale.php";
        $linkcronj .= "?batoken=".$this->cookiekeymodule()."\"";
        $this->context->smarty->assign('bachecknoticron', $this->context->cookie->bachecknoticron);
        $this->context->smarty->assign('linkcronj', $linkcronj);
        $this->smarty->assign('token', $token);
        $this->smarty->assign('taskbar', $tabvalue);
        $this->smarty->assign('report_module', $reportsale_module);
        $this->smarty->assign('configure', $configure);
        /* Add style */
        $this->context->controller->addCSS($this->_path . 'views/css/stylereport.css');

        $this->context->controller->addJS($this->_path . 'views/js/ajaxbasic.js');
        $css1='https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css';
        $this->context->controller->addCSS($css1);
        $html = $this->display(__FILE__, 'views/templates/admin/reportsaletab.tpl');
        /********This Tab Basic********* */
        if (Tools::isSubmit('reset_filter')) {
            $delete_prefix = Tools::getValue('prefixreport');
            $this->resetBaConfig($delete_prefix);
            $ba_task = Tools::getValue('task');
            $this->redirectReport($ba_task);
        }
        if ($tabvalue == 'basic') {
            $url_report = 'index.php?controller=AdminModules&configure=reportsale&task=basic&token=' . $token;
            /* assign name text input */
            $this->smarty->assign('url_report', $url_report);
            $this->assignNameTextInput('BS_');
            $this->getConfiguration('BS_');
            /* assign name text input */
            $html.=$this->display(__FILE__, 'views/templates/admin/templateoption.tpl');
            $html.=$this->viewbasic->viewbasiclist();
        }
        /* Save Configuration */
        if (Tools::isSubmit('submitResetreportsaleba_report_basic')) {
            $this->viewbasic->resetList();
            $this->redirectReport('basic');
        }
        /********End Tab Basic**********/
        /********This Tab taxes**********/
        if ($tabvalue == 'taxes') {
            $url_report = 'index.php?controller=AdminModules&configure=reportsale&task=taxes&token=' . $token;
            $this->smarty->assign('url_report', $url_report);
            $this->assignNameTextInput('TO_');
            $this->getConfiguration('TO_');
            $html.=$this->display(__FILE__, 'views/templates/admin/templateoption.tpl');
            $html.=$this->viewtaxonly->viewtaxonlylist();
        }
        if (Tools::isSubmit('TO_save_filter')) { /* Save Configuration */
            $this->saveDataConfigReport('TO_');
            $data = $this->refineIdOrder('TO_', 'ba_report_tax_only');
            $count_data = count($data);
            for ($i = 0; $i < $count_data; $i++) {
                $order_id = $data[$i]['id_order'];
                $this->viewtaxonly->insertreporttaxonly($order_id);
            }
            $this->redirectReport('taxes');
        }
        if (Tools::isSubmit('submitResetreportsaleba_report_taxonly')) {
            $this->viewtaxonly->resetList();
            $this->redirectReport('taxes');
        }
        /********End Tab taxes********* */
        /********This Tab revenue********* */
        if ($tabvalue == 'revenue') {
            $url_report = 'index.php?controller=AdminModules&configure=reportsale&task=revenue&token=' . $token;
            $this->smarty->assign('url_report', $url_report);
            $this->assignNameTextInput('PF_');
            $this->getConfiguration('PF_');
            $html.=$this->display(__FILE__, 'views/templates/admin/templateoption.tpl');
            $html.=$this->viewprofit->viewprofitlist();
        }
        if (Tools::isSubmit('submitResetreportsaleba_report_profit')) {
            $this->viewprofit->resetList();
            $this->redirectReport('revenue');
        }
        /********End Tab revenue********* */
        /********This Tab all********* */
        if ($tabvalue == 'all') {
            $url_report = 'index.php?controller=AdminModules&configure=reportsale&task=all&token=' . $token;
            $this->smarty->assign('url_report', $url_report);
            $this->assignNameTextInput('FU_');
            $this->getConfiguration('FU_');
            $html.=$this->display(__FILE__, 'views/templates/admin/templateoption.tpl');
            $html.=$this->viewfull->viewfulllist();
        }
        if (Tools::isSubmit('submitResetreportsaleba_report_full')) {
            $this->viewfull->resetList();
            $this->redirectReport('all');
        }
        /********End Tab all********* */
        /********This Tab Product********* */
        if ($tabvalue == 'product') {
            $list_category = $this->getAllCategoriesName(); //[0][1]['infos'];
            $count_cate = count($list_category);
            $this->smarty->assign('list_category', $list_category);
            $this->smarty->assign('count_cate', $count_cate);

            $Manufacturer = new Manufacturer();
            $list_manufacturers = $Manufacturer->getManufacturers(); //[0][1]['infos'];
            $count_manufacturers = count($list_manufacturers);
            $this->smarty->assign('list_manufacturers', $list_manufacturers);
            $this->smarty->assign('count_manufacturers', $count_manufacturers);

            $supplier = new Supplier();
            $list_supplier = $supplier->getSuppliers(); //[0][1]['infos'];
            $count_supplier = count($list_supplier);
            $this->smarty->assign('list_supplier', $list_supplier);
            $this->smarty->assign('count_supplier', $count_supplier);

            $this->getConfiguration('PR_', true);
            $url_report = 'index.php?controller=AdminModules&configure=reportsale&task=product&token=' . $token;
            $this->smarty->assign('url_report', $url_report);
            $html.=$this->display(__FILE__, 'views/templates/admin/templateproduct.tpl');
            $html.=$this->viewproduct->viewproductlist();
        }
        if (Tools::isSubmit('submitResetreportsaleba_report_product')) {
            $this->viewproduct->resetList();
            $this->redirectReport('product');
        }
        /********End Tab product********* */
        /********This Tab Manufacturers********* */
        if ($tabvalue == 'manufacturers') {
            $url_report = 'index.php?controller=AdminModules&configure=reportsale&task=manufacturers&token=' . $token;
            $this->smarty->assign('url_report', $url_report);
            $this->assignNameTextInput('BR_');
            $this->getConfiguration('BR_');
            $html.=$this->display(__FILE__, 'views/templates/admin/templateoption.tpl');
            $html.=$this->viewbrand->viewbrandlist();
        }

        if (Tools::isSubmit('submitResetreportsaleba_report_brand')) {
            $this->viewbrand->resetList();
            $this->redirectReport('manufacturers');
        }
        /********End Tab manufacturers********* */
        /********This Tab Sublier********* */
        if ($tabvalue == 'supplier') {
            $url_report = 'index.php?controller=AdminModules&configure=reportsale&task=supplier&token=' . $token;
            $this->smarty->assign('url_report', $url_report);
            $this->assignNameTextInput('SL_');
            $this->getConfiguration('SL_');
            $html.=$this->display(__FILE__, 'views/templates/admin/templateoption.tpl');
            $html.=$this->viewsupplier->viewsuplierlist();
        }

        if (Tools::isSubmit('submitResetreportsaleba_report_supplier')) {
            $this->viewsupplier->resetList();
            $this->redirectReport('supplier');
        }
        /********End Tab Sublier********* */
        /********This Tab Category********* */
        if ($tabvalue == 'category') {
            $url_report = 'index.php?controller=AdminModules&configure=reportsale&task=category&token=' . $token;
            $this->smarty->assign('url_report', $url_report);
            $this->assignNameTextInput('CT_');
            $this->getConfiguration('CT_');
            $html.=$this->display(__FILE__, 'views/templates/admin/templateoption.tpl');
            $html.=$this->viewcategory->viewcategorylist();
        }

        if (Tools::isSubmit('submitResetreportsaleba_report_category')) {
            $this->viewcategory->resetList();
            $this->redirectReport('category');
        }
        /********End Tab Category********* */
        /********This Tab client********* */
        if ($tabvalue == 'client') {
            $url_report = 'index.php?controller=AdminModules&configure=reportsale&task=client&token=' . $token;
            $this->smarty->assign('url_report', $url_report);
            $this->assignNameTextInput('CM_');
            $this->getConfiguration('CM_');
            $html.=$this->display(__FILE__, 'views/templates/admin/templateoption.tpl');
            $html.=$this->viewcustomer->viewcustomerlist();
        }

        if (Tools::isSubmit('submitResetreportsaleba_report_customer')) {
            $this->viewcustomer->resetList();
            $this->redirectReport('client');
        }
        /********End Tab client********* */
        /********This Tab Credit Slips********* */
        if ($tabvalue == 'creditslips') {
            $url_report = 'index.php?controller=AdminModules&configure=reportsale&task=creditslips&token=' . $token;
            $this->smarty->assign('url_report', $url_report);
            $this->assignNameTextInput('SC_');
            $this->getConfiguration('SC_');
            $html.=$this->display(__FILE__, 'views/templates/admin/templatestocerdit.tpl');
            $html.=$this->viewstorecredit->viewstocreditlist();
        }
        if ($tabvalue == 'cronjob') {
            $id_shop = $this->context->shop->id;
            $gr_shop = $this->context->shop->id_shop_group;
            $basettgcronj = Configuration::get('basettgcronj', false, $gr_shop, $id_shop);
            $this->context->smarty->assign('basettgcronj', json_decode($basettgcronj));
            if (Tools::isSubmit('submit_checkcronjob')) {
                $this->context->cookie->__set('bachecknoticron', 1);
            }
            if (Tools::isSubmit('submit_cronjob')) {
                $badatacron1 = Tools::getValue('barpcronj');
                if ($badatacron1['tableex'] == null) {
                    $badatacron1['tableex'] = array();
                }
                $badatacron = json_encode($badatacron1);
                Configuration::updateValue('basettgcronj', $badatacron, false, $gr_shop, $id_shop);
                $this->redirectReport('cronjob');
            }
            $html.=$this->display(__FILE__, 'views/templates/admin/barpcronjob.tpl');
        }
        if (Tools::isSubmit('submitResetreportsaleba_report_storecredit')) {
            $this->viewstorecredit->resetList();
            $this->redirectReport('creditslips');
        }
        if ($token_downloadcsv == "downloadcsv") {
            $html .= '<script>var ba_downloadcsv = "' . $token_downloadcsv . '";</script>';
        } else {
            $token_downloadcsv = "";
            $html .= '<script>var ba_downloadcsv = "' . $token_downloadcsv . '";</script>';
        }
        $html .= '<script>var ba_token_task = "' . $tabvalue . '";</script>';
        $html .= '<script>var ba_token = "' . $token . '";</script>';
        $folder_admin = str_ireplace(_PS_ROOT_DIR_, '', _PS_ADMIN_DIR_);
        $html .= '<script>var ba_namemodule = "' . $folder_admin . '";</script>';
        $get_host_link = Tools::getShopProtocol().Tools::getHttpHost().__PS_BASE_URI__;
        $html .= '<script>var ba_https_namemodule = "'. $get_host_link .'";</script>';

        return $html;
    }

    public function redirectReport($name)
    {
        $href = 'index.php?controller=AdminModules&configure=' . $this->name . '&token='
                . Tools::getAdminTokenLite('AdminModules') . '&task=' . $name;
        Tools::redirectAdmin($href);
        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }
        $uninstall = new ReportSaleData();
        $uninstall->deleteTableUninstall();
        // since 1.0.20
        $tab = new Tab((int) Tab::getIdFromClassName('AdminReportSale'));
        $tab->delete();
        return true;
    }
    public function assignNameTextInput($prefix)
    {
        $order_date_from = $prefix . "order_date_from";
        $order_date_to = $prefix . "order_date_to";
        $invoice_date_from = $prefix . "invoice_date_from";
        $invoice_date_to = $prefix . "invoice_date_to";
        $delivery_date_from = $prefix . "delivery_date_from";
        $delivery_date_to = $prefix . "delivery_date_to";
        $order_number_from = $prefix . "order_number_from";
        $order_number_to = $prefix . "order_number_to";
        $invoice_number_from = $prefix . "invoice_number_from";
        $invoice_number_to = $prefix . "invoice_number_to";
        $stt_filtering = $prefix . 'status_filtering_';
        $country_filtering = $prefix . "country_filtering";

        $this->smarty->assign('order_date_from', $order_date_from);
        $this->smarty->assign('order_date_to', $order_date_to);
        $this->smarty->assign('invoice_date_from', $invoice_date_from);
        $this->smarty->assign('invoice_date_to', $invoice_date_to);
        $this->smarty->assign('delivery_date_from', $delivery_date_from);
        $this->smarty->assign('delivery_date_to', $delivery_date_to);
        $this->smarty->assign('order_number_from', $order_number_from);
        $this->smarty->assign('order_number_to', $order_number_to);
        $this->smarty->assign('invoice_number_from', $invoice_number_from);
        $this->smarty->assign('invoice_number_to', $invoice_number_to);
        $this->smarty->assign('stt_filtering', $stt_filtering);
        $this->smarty->assign('country_filtering', $country_filtering);
        $this->smarty->assign('prefix_report', $prefix);

        return true;
    }

    public function saveDataConfigReport($prefix, $product = false)
    {
        $trim1=trim(Tools::getValue($prefix . 'order_date_from'));
        $trim2=trim(Tools::getValue($prefix . 'order_date_to'));
        $trim3=trim(Tools::getValue($prefix . 'invoice_date_from'));
        $trim4=trim(Tools::getValue($prefix . 'invoice_date_to'));
        $trim5=trim(Tools::getValue($prefix . 'delivery_date_from'));
        $trim6=trim(Tools::getValue($prefix . 'delivery_date_to'));
        $string7=(string) (Tools::getValue($prefix . 'order_number_from'));
        $string8=(string) (Tools::getValue($prefix . 'order_number_to'));
        $string9=(string) (Tools::getValue($prefix . 'invoice_number_from'));
        $string10=(string) (Tools::getValue($prefix . 'invoice_number_to'));
        $string11=(string) (Tools::getValue($prefix . 'credit_date_to'));
        $string12=(string) (Tools::getValue($prefix . 'credit_date_from'));
        Configuration::updateValue($prefix . 'order_date_from', $trim1);
        Configuration::updateValue($prefix . 'order_date_to', $trim2);
        Configuration::updateValue($prefix . 'invoice_date_from', $trim3);
        Configuration::updateValue($prefix . 'invoice_date_to', $trim4);
        Configuration::updateValue($prefix . 'delivery_date_from', $trim5);
        Configuration::updateValue($prefix . 'delivery_date_to', $trim6);
        Configuration::updateValue($prefix . 'order_number_from', $string7);
        Configuration::updateValue($prefix . 'order_number_to', $string8);
        Configuration::updateValue($prefix . 'invoice_number_from', $string9);
        Configuration::updateValue($prefix . 'invoice_number_to', $string10);
        Configuration::updateValue($prefix . 'credit_date_to', $string11);
        Configuration::updateValue($prefix . 'credit_date_from', $string12);

        $status_filtering = OrderState::getOrderStates($this->context->language->id);
        $count_filtering = count($status_filtering);
        for ($i = 0; $i < $count_filtering; $i++) {
            $stt=$status_filtering[$i]['id_order_state'];
            $status_filtering_value = (int) (Tools::getValue($prefix . 'status_filtering_' . $stt));
            Configuration::updateValue($prefix . 'status_filtering_' . $stt, $status_filtering_value);
        }

        $arr_country = Tools::getValue($prefix . 'country_filtering');
        $data_country='';
        if ($arr_country==null) {
            $data_country='';
        } else {
            $data_country=$this->convertArrayToString($arr_country);
        }
        Configuration::updateValue($prefix . 'country_filtering', $data_country);
        $stores_selected = json_encode(Tools::getValue($prefix . 'stores'));
        Configuration::updateValue($prefix . 'stores', $stores_selected);
        if ($product == true) {
            $categories = json_encode(Tools::getValue($prefix . 'category'));
            $manufacturers = json_encode(Tools::getValue($prefix . 'manufacturers'));
            $suppliers = json_encode(Tools::getValue($prefix . 'supplier'));
            Configuration::updateValue($prefix . 'category', $categories);
            Configuration::updateValue($prefix . 'manufacturers', $manufacturers);
            Configuration::updateValue($prefix . 'supplier', $suppliers);
        }
        return true;
    }
    public function convertArrayToString($arr)
    {
        $string="";
        if ($arr!=null) {
            for ($i=0; $i<count($arr); $i++) {
                if ($i==0) {
                    $string=$arr[$i];
                }
                if ($i<=count($arr)-1 && $i>0) {
                    $string=$string.','.$arr[$i];
                }
            }
        }
        return $string;
    }
    public function getConfiguration($prefix, $product = false)
    {
        $url = Tools::getShopProtocol() . Tools::getHttpHost() . __PS_BASE_URI__;
        $arr_configuration = array(
            'order_date_from' => Configuration::get($prefix . 'order_date_from'),
            'order_date_to' => Configuration::get($prefix . 'order_date_to'),
            'invoice_date_from' => Configuration::get($prefix . 'invoice_date_from'),
            'invoice_date_to' => Configuration::get($prefix . 'invoice_date_to'),
            'delivery_date_from' => Configuration::get($prefix . 'delivery_date_from'),
            'delivery_date_to' => Configuration::get($prefix . 'delivery_date_to'),
            'credit_date_to' => Configuration::get($prefix . 'credit_date_to'),
            'credit_date_from' => Configuration::get($prefix . 'credit_date_from'),
            'order_number_from' => (string)Configuration::get($prefix . 'order_number_from'),
            'order_number_to' => (string)Configuration::get($prefix . 'order_number_to'),
            'invoice_number_from' => (string)Configuration::get($prefix . 'invoice_number_from'),
            'invoice_number_to' => (string)Configuration::get($prefix . 'invoice_number_to')
        );

        $country_string = Configuration::get($prefix . 'country_filtering');
        $country_filtering = explode(',', $country_string);
        $count_country=count($country_filtering);

        $status_filtering = OrderState::getOrderStates($this->context->language->id);
        $count_filtering = count($status_filtering);
        $arr_status = array();
        for ($i = 0; $i < $count_filtering; $i++) {
            $name_arr = $prefix . 'status_filtering_' . $status_filtering[$i]['id_order_state'];
            $arr_status[$name_arr] = Configuration::get($name_arr);
        }
        if ($product == true) {
            $categories_selected = (array) json_decode(Configuration::get($prefix . 'category'), true);
            $manufacturers_selected = (array) json_decode(Configuration::get($prefix . 'manufacturers'), true);
            $suppliers_selected = (array) json_decode(Configuration::get($prefix . 'supplier'), true);
            $this->smarty->assign('categories_selected', $categories_selected);
            $this->smarty->assign('manufacturers_selected', $manufacturers_selected);
            $this->smarty->assign('suppliers_selected', $suppliers_selected);
            $arr_configuration['PR_category'] = Configuration::get('PR_category');
            $arr_configuration['PR_manufacturers'] = Configuration::get('PR_manufacturers');
            $arr_configuration['PR_supplier'] = Configuration::get('PR_supplier');
        }

        $stores = Shop::getShops(false);
        $stores_selected = (array) json_decode(Configuration::get($prefix . 'stores'), true);

        $this->smarty->assign('country_filtering', $country_filtering);
        $this->smarty->assign('count_country', $count_country);
        $this->smarty->assign('stores', $stores);
        $this->smarty->assign('stores_selected', $stores_selected);
        $this->smarty->assign('PREFIX_FORM', $prefix);
        $this->smarty->assign('hidden_data_ajax', null);
        $this->smarty->assign('url_bareport', $url);
        $this->smarty->assign('arr_configuration', $arr_configuration);
        $this->smarty->assign('arr_status', $arr_status);
    }

    /*******Export Report******* */
    public function refineIdOrder($prefix, $product = false, $whereclause = 'AND(', $clause2 = 'AND(')
    {
        $data_export = array(
            'order_date_from' => Configuration::get($prefix . 'order_date_from'),
            'order_date_to' => Configuration::get($prefix . 'order_date_to'),
            'invoice_date_from' => Configuration::get($prefix . 'invoice_date_from'),
            'invoice_date_to' => Configuration::get($prefix . 'invoice_date_to'),
            'delivery_date_from' => Configuration::get($prefix . 'delivery_date_from'),
            'delivery_date_to' => Configuration::get($prefix . 'delivery_date_to'),
            'order_number_from' => Configuration::get($prefix . 'order_number_from'),
            'order_number_to' => Configuration::get($prefix . 'order_number_to'),
            'invoice_number_from' => Configuration::get($prefix . 'invoice_number_from'),
            'invoice_number_to' => Configuration::get($prefix . 'invoice_number_to')
        );

        $country_string = Configuration::get($prefix . 'country_filtering');
        $stores = json_decode(Configuration::get($prefix . 'stores'), true);
        $country = explode(',', $country_string);
        $join_data='INNER JOIN '._DB_PREFIX_.'address '
                .'WHERE '._DB_PREFIX_.'address.id_address='. _DB_PREFIX_ . 'orders.id_address_invoice ';
        $join_data_string=null;
        for ($i=0; $i < count($country); $i++) {
            if (count($country)!=0 && $country_string!=0) {
                $join_data.=$clause2._DB_PREFIX_ . 'address.id_country='.pSQL($country[$i]).' ';
                $clause2='OR ';
                $join_data_string="dataok";
            }
        }
        if ($join_data_string!=null) {
            $join_data.=' ) ';
        }

        $query = 'SELECT distinct '. _DB_PREFIX_ . 'orders.id_order FROM ' . _DB_PREFIX_ . 'orders '.$join_data.' ';
        if ($data_export['order_date_from'] != null) {
            $date_from = $data_export['order_date_from'] . " 00:00:00";
            $query.='AND '._DB_PREFIX_ . 'orders.date_add >= "' . pSQL($date_from) . '" ';
        }
        if ($data_export['order_date_to'] != null) {
            $date_to = $data_export['order_date_to'] . " 23:59:59";
            $query.='AND '._DB_PREFIX_ . 'orders.date_add <= "' . pSQL($date_to) . '" ';
        }
        if ($data_export['invoice_date_from'] != null) {
            $date_from = $data_export['invoice_date_from'] . " 00:00:00";
            $query.='AND '._DB_PREFIX_ . 'orders.invoice_date >= "' . pSQL($date_from) . '" ';
        }
        if ($data_export['invoice_date_to'] != null) {
            $date_to = $data_export['invoice_date_to'] . " 23:59:59";
            $query.='AND '._DB_PREFIX_ . 'orders.invoice_date <= "' . pSQL($date_to) . '" ';
        }
        if ($data_export['delivery_date_from'] != null) {
            $date_from = $data_export['delivery_date_from'] . " 00:00:00";
            $query.='AND '._DB_PREFIX_ . 'orders.delivery_date >= "' . pSQL($date_from) . '" ';
        }
        if ($data_export['delivery_date_to'] != null) {
            $date_to = $data_export['delivery_date_to'] . " 23:59:59";
            $query.='AND '._DB_PREFIX_ . 'orders.delivery_date <= "' . pSQL($date_to) . '" ';
        }

        if ($data_export['order_number_from'] != null) { /* Order number to order ID */
            $query.='AND '._DB_PREFIX_ . 'orders.id_order >= ' . (int)$data_export['order_number_from'] . ' ';
        }
        if ($data_export['order_number_to'] != null) { /* Order number to order ID */
            $query.='AND '._DB_PREFIX_ . 'orders.id_order <= ' . (int)$data_export['order_number_to'] . ' ';
        }
        if ($data_export['invoice_number_from'] != null) { /* Order number to order ID */
            $query.='AND '._DB_PREFIX_ . 'orders.invoice_number >= ' . (int)$data_export['invoice_number_from'] . ' ';
        }
        if ($data_export['invoice_number_to'] != null) { /* Order number to order ID */
            $query.='AND '._DB_PREFIX_ . 'orders.invoice_number <= ' . (int)$data_export['invoice_number_to'] . ' ';
        }
        if (!empty($stores)) {
            $stores_str = implode(", ", $stores);
            $query.='AND '._DB_PREFIX_ . 'orders.id_shop IN (' .$stores_str. ') ';
        }
        if ($product==true) {
            $product;
        }
        /**** Product ****/
        $status_filtering = OrderState::getOrderStates($this->context->language->id);
        $count_filtering = count($status_filtering);
        $status_filtering_string=null;
        for ($i = 0; $i < $count_filtering; $i++) {
            $name_arr = $prefix . 'status_filtering_' . $status_filtering[$i]['id_order_state'];
            $data_export[$name_arr] = Configuration::get($name_arr);
            if ($data_export[$name_arr] != 0) {
                $query.=$whereclause._DB_PREFIX_ . 'orders.current_state = ' . pSQL($data_export[$name_arr]) . ' ';
                $whereclause='OR ';
                $status_filtering_string="okdata";
            }
        }
        if ($status_filtering_string !=null) {
            $query.=') ';
        }
        $query.='ORDER BY '._DB_PREFIX_ . 'orders.id_order ASC';

        $data = DB::getInstance()->executeS($query, true, false);
        $arr = array();
        for ($i = 0; $i < count($data); $i++) {
            $arr[] = $data[$i]['id_order'];
        }
        return $arr;
    }
    public function getAllCategoriesName($a = '', $b = false, $c = true, $d = '', $e = true, $f = '', $g = '', $h = '')
    {
        if ($a=='') {
            $a=null;
        }
        if ($d == '') {
            $d=null;
        }
        $root_category=$a;
        $id_lang =$b;
        $active=$c;
        $groups=$d;
        $use_shop_restriction=$e;
        $sql_filter=$f;
        $sql_sort=$g;
        $sql_limit=$h;
        if (isset($root_category) && !Validate::isInt($root_category)) {
            die(Tools::displayError());
        }

        if (!Validate::isBool($active)) {
            die(Tools::displayError());
        }
        if (isset($groups) && Group::isFeatureActive() && !is_array($groups)) {
            $groups = (array) $groups;
        }
        $str1='Category::getAllCategoriesName_';
        $cache_id = $str1 . md5((int) $root_category . (int) $id_lang . (int) $active . (int) $use_shop_restriction);
        $cache_id .= (isset($groups) && Group::isFeatureActive() ? implode('', $groups) : '');

        if (!Cache::isStored($cache_id)) {
            $query='
                SELECT c.id_category, cl.name
                FROM `' . _DB_PREFIX_ . 'category` c
                ' . ($use_shop_restriction ? Shop::addSqlAssociation('category', 'c') : '') . '
                LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl '
                .'ON c.`id_category` = cl.`id_category`' . Shop::addSqlRestrictionOnLang('cl') . '
                ' . (isset($groups) && Group::isFeatureActive() ? 'LEFT JOIN `' . _DB_PREFIX_ . 'category_group` cg '
                    .'ON c.`id_category` = cg.`id_category`' : '') . '
                ' . (isset($root_category) ? 'RIGHT JOIN `' . _DB_PREFIX_ . 'category` c2 '
                    .'ON c2.`id_category` = ' . (int) $root_category . ' '
                    .'AND c.`nleft` >= c2.`nleft` AND c.`nright` <= c2.`nright`' : '') . '
                WHERE 1 ' . $sql_filter . ' ' . ($id_lang ? 'AND `id_lang` = ' . (int) $id_lang : '') . '
                ' . ($active ? ' AND c.`active` = 1' : '') . '
                ' . (isset($groups) && Group::isFeatureActive() ? ' '
                    .'AND cg.`id_group` IN (' . implode(',', $groups) . ')' : '') . '
                ' . (!$id_lang || (isset($groups) && Group::isFeatureActive()) ? ' GROUP BY c.`id_category`' : '') . '
                ' . ($sql_sort != '' ? $sql_sort : ' ORDER BY c.`level_depth` ASC') . '
                ' . ($sql_sort == '' && $use_shop_restriction ? ', category_shop.`position` ASC' : '') . '
                ' . ($sql_limit != '' ? $sql_limit : '');
            $result = Db::getInstance()->executeS($query, true, false);

            Cache::store($cache_id, $result);
        } else {
            $result = Cache::retrieve($cache_id);
        }
        return $result;
    }

    public function addnewExportData($arrayName, $prefix)
    {
        Tools::clearSmartyCache();
        Tools::clearXMLCache();
        Media::clearCache();

        $viewbasic = new BaViewBasic();
        $viewtaxonly = new BaViewTaxOnly();
        $viewprofit = new BaViewProfit();
        $viewfull = new BaViewfull();
        $viewproduct = new BaViewProduct();
        $viewbrand = new BaViewBrand();
        $viewsupplier = new BaViewSupplier();
        $viewcategory = new BaViewCategory();
        $viewcustomer = new BaViewCustomer();
        $viewstorecredit = new BaViewStoCredit();
        $count_data = count($arrayName);
        if ($prefix == "BS_") {
            for ($i = 0; $i < $count_data; $i++) {
                $order_id = $arrayName[$i];
                $viewbasic->insertreportbasic($order_id);
            }
        }
        if ($prefix == "TO_") {
            for ($i = 0; $i < $count_data; $i++) {
                $order_id = $arrayName[$i];
                $viewtaxonly->insertreporttaxonly($order_id);
            }
        }
        if ($prefix == "PF_") {
            for ($i = 0; $i < $count_data; $i++) {
                $order_id = $arrayName[$i];
                $viewprofit->insertreportprofit($order_id);
            }
        }
        if ($prefix == "FU_") {
            for ($i = 0; $i < $count_data; $i++) {
                $order_id = $arrayName[$i];
                $viewfull->insertreportfull($order_id);
            }
        }
        if ($prefix == "PR_") {
            for ($i = 0; $i < $count_data; $i++) {
                $order_id = $arrayName[$i];
                $viewproduct->insertreportproduct($order_id);
            }
            $viewproduct->updateAllproducreport();
        }
        if ($prefix == "BR_") {
            for ($i = 0; $i < $count_data; $i++) {
                $order_id = $arrayName[$i];
                $viewbrand->insertreportbrand($order_id);
            }
        }
        if ($prefix == "SL_") {
            for ($i = 0; $i < $count_data; $i++) {
                $order_id = $arrayName[$i];
                $viewsupplier->insertreportsupplier($order_id);
            }
        }
        
        if ($prefix == "CT_") {
            for ($i = 0; $i < $count_data; $i++) {
                $order_id = $arrayName[$i];
                $viewcategory->insertreportcategory($order_id);
            }
        }
        if ($prefix == "CM_") {
            for ($i = 0; $i < $count_data; $i++) {
                $order_id = $arrayName[$i];
                $viewcustomer->insertreportcustomer($order_id);
            }
        }
        if ($prefix == "SC_") {
            for ($i = 0; $i < $count_data; $i++) {
                $order_id = $arrayName[$i];
                $viewstorecredit->insertreportcredit($order_id);
            }
        }
    }

    public function deleteDataReport($name)
    {
        $dele_data = $name;
        DB::getInstance()->delete($dele_data);
        return true;
    }

    public function basicdata()
    {
        $where = Configuration::get($this->name.'_basic_where');
        $basic_orderby = Tools::getValue("reportsaleba_report_basicOrderby");
        $basic_orderway = Tools::getValue("reportsaleba_report_basicOrderway");
        $basic = 'SELECT 
        `invoice_add_date`,
        `products_name`,
		`total_products_no_tax`,
		`total_shipping_without_tax`,
		`shipping_tax_amount`,
		`total_discounts_tax_excl`,
        `discounts_tax_amount`,
		`total_wrapping_tax_excl`,
        `wrapping_tax_amount`,
		`total_tax_5`,
        `total_tax_9975`,
        `total_tax`,
		`total_with_tax`,		
        `country`,
        `state`,
        `payment_method`,
        `refunded_amount`,
        `refunded_quantity`,
        `refunded_tax`,
		`id_currency`
         FROM `'._DB_PREFIX_.'ba_report_basic`';
        $basic .= $where;
        $basic .= ' ORDER  BY `'. pSQL($basic_orderby) .'` '. pSQL($basic_orderway);
        $result = Db::getInstance()->executeS($basic, true, false);
        foreach ($result as $key1 => $v) {
            $a = (int) $result[$key1]['id_currency'];
            $total_with_tax = $result[$key1]['total_with_tax'];
            $total_products_no_tax = $result[$key1]['total_products_no_tax'];
            $total_shipping_without_tax = $result[$key1]['total_shipping_without_tax'];
            $shipping_tax_amount = $result[$key1]['shipping_tax_amount'];
            $total_discounts_tax_excl = $result[$key1]['total_discounts_tax_excl'];
            $discounts_tax_amount = $result[$key1]['discounts_tax_amount'];
            $total_wrapping_tax_excl = $result[$key1]['total_wrapping_tax_excl'];
            $wrapping_tax_amount = $result[$key1]['wrapping_tax_amount'];
            $total_tax_5 = $result[$key1]['total_tax_5'];
            $total_tax_9975 = $result[$key1]['total_tax_9975'];
            $total_tax = $result[$key1]['total_tax'];
            $products_tax = $result[$key1]['products_tax'];
            $refunded_amount = $result[$key1]['refunded_amount'];
            $refunded_quantity = $result[$key1]['refunded_quantity'];
            $refunded_tax = $result[$key1]['refunded_tax'];

            $result[$key1]['total_with_tax'] = $this->formatPriceInCSV($total_with_tax, $a);
            $total_products_no_tax = $this->formatPriceInCSV($total_products_no_tax, $a);
            $result[$key1]['total_products_no_tax'] = $total_products_no_tax;
            $result[$key1]['products_tax'] = $this->formatPriceInCSV($products_tax, $a);
            $result[$key1]['total_shipping_without_tax'] = $this->formatPriceInCSV($total_shipping_without_tax, $a);
            $result[$key1]['shipping_tax_amount'] = $this->formatPriceInCSV($shipping_tax_amount, $a);
            $result[$key1]['total_discounts_tax_excl'] = $this->formatPriceInCSV($total_discounts_tax_excl, $a);
            $result[$key1]['discounts_tax_amount'] = $this->formatPriceInCSV($discounts_tax_amount, $a);
            $result[$key1]['total_wrapping_tax_excl'] = $this->formatPriceInCSV($total_wrapping_tax_excl, $a);
            $result[$key1]['wrapping_tax_amount'] = $this->formatPriceInCSV($wrapping_tax_amount, $a);
            $result[$key1]['total_tax_5'] = $this->formatPriceInCSV($total_tax_5, (int)$a);
            $result[$key1]['total_tax_9975'] = $this->formatPriceInCSV($total_tax_9975, (int)$a);
            $result[$key1]['total_tax'] = $this->formatPriceInCSV($total_tax, (int)$a);
            $result[$key1]['refunded_amount'] = $this->formatPriceInCSV($refunded_amount, (int)$a);
            $result[$key1]['refunded_tax'] = $this->formatPriceInCSV($refunded_tax, (int)$a);
            $result[$key1]['refunded_quantity'] = $refunded_quantity;
            if ($v['invoice_add_date'] =='0000-00-00 00:00:00') {
                $result[$key1]['invoice_add_date'] = '';
            } else {
				$result[$key1]['invoice_add_date'] = date("Y-m-d", strtotime($result[$key1]['invoice_add_date']));
			}
            // since 1.0.21
            $products_name = $result[$key1]['products_name'];
            $result[$key1]['products_name'] = $this->formatProductsName($products_name);
			unset($result[$key1]['id_currency']);
        }
        $basic_array = array(
            "Invoice Date",
            "Products",
			"Total products no tax",
			"Total shipping without tax",
			"Shipping tax amount",
			"Total discounts tax excl",
			"Discounts tax amount",
			"Total wrapping tax excl",
			"Wrapping tax amount ",
			"Total tax 5%",
            "Total tax 9.975%",
            "Total tax",			
            "Total Paid With Tax",
            "Country",
            "State",
            "Payment Method",
            "Refunded Amount",
            "Refunded Quantity",
            "Refunded Tax",
			);
        if (count($basic_array) == 0) {
            return null;
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment;filename="basic'.date('Y-m-d H:i').'.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        $df = fopen("php://output", 'w');
        fprintf($df, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($df, $basic_array);
        foreach ($result as $row) {
            fputcsv($df, $row);
        }
        exit();
    }
    public function taxesdata()
    {
        $where = Configuration::get($this->name.'_basic_where');
        $taxes_orderby = Tools::getValue("reportsaleba_report_taxonlyOrderby");
        $taxes_orderway = Tools::getValue("reportsaleba_report_taxonlyOrderway");
        $taxes = 'SELECT `id_shop`,
        `shop_name`,
        `order_id`,
        `reference`,
        `order_add_date`,
        `products_name`,
        `total_products_no_tax`,
        `product_tax`,
        `including_ecotax_tax_excl`,
        `including_ecotax_tax_amount`,
        `total_discounts_tax_excl`,
        `discounts_tax_amount`,
        `total_wrapping_tax_excl`,
        `wrapping_tax_amount`,
        `total_shipping_without_tax`,
        `shipping_tax_amount`,
        `total_tax_5`,
        `total_tax_9975`,
        `total_tax`,
        `last_name`,
        `first_name`,
        `country`,
        `id_country`,
        `state`,
        `company`,
        `iso_currency`,
        `id_currency` 
        FROM `'._DB_PREFIX_.'ba_report_tax_only`';
        $taxes .= $where;
        $taxes .= ' ORDER  BY `'. pSQL($taxes_orderby) .'`  '. pSQL($taxes_orderway) .'';
        $result = Db::getInstance()->executeS($taxes, true, false);
        foreach ($result as $key1 => $aaa) {
            $aaa;
            $a = (int)$result[$key1]['id_currency'];
            $total_products_no_tax = $result[$key1]['total_products_no_tax'];
            $including_ecotax_tax_excl = $result[$key1]['including_ecotax_tax_excl'];
            $total_shipping_without_tax = $result[$key1]['total_shipping_without_tax'];
            $shipping_tax_amount = $result[$key1]['shipping_tax_amount'];
            $total_discounts_tax_excl = $result[$key1]['total_discounts_tax_excl'];
            $discounts_tax_amount = $result[$key1]['discounts_tax_amount'];
            $total_wrapping_tax_excl = $result[$key1]['total_wrapping_tax_excl'];
            $wrapping_tax_amount = $result[$key1]['wrapping_tax_amount'];
            $total_tax = $result[$key1]['total_tax'];
            $total_tax_5 = $result[$key1]['total_tax_5'];
            $total_tax_9975 = $result[$key1]['total_tax_9975'];
            $total_tax = $result[$key1]['total_tax'];
            $product_tax = $result[$key1]['product_tax'];
            $including_ecotax_tax_amount = $result[$key1]['including_ecotax_tax_amount'];

            $result[$key1]['total_products_no_tax'] = $this->formatPriceInCSV($total_products_no_tax, $a);
            $result[$key1]['product_tax'] = $this->formatPriceInCSV($product_tax, $a);
            $result[$key1]['including_ecotax_tax_excl'] = $this->formatPriceInCSV($including_ecotax_tax_excl, $a);
            $result[$key1]['including_ecotax_tax_amount'] = $this->formatPriceInCSV($including_ecotax_tax_amount, $a);
            $result[$key1]['total_shipping_without_tax'] = $this->formatPriceInCSV($total_shipping_without_tax, $a);
            $result[$key1]['shipping_tax_amount'] = $this->formatPriceInCSV($shipping_tax_amount, $a);
            $result[$key1]['total_discounts_tax_excl'] = $this->formatPriceInCSV($total_discounts_tax_excl, $a);
            $result[$key1]['discounts_tax_amount'] = $this->formatPriceInCSV($discounts_tax_amount, $a);
            $result[$key1]['total_wrapping_tax_excl'] = $this->formatPriceInCSV($total_wrapping_tax_excl, $a);
            $result[$key1]['wrapping_tax_amount'] = $this->formatPriceInCSV($wrapping_tax_amount, $a);
            $result[$key1]['total_tax'] = $this->formatPriceInCSV($total_tax, $a);
            $result[$key1]['total_tax_9975'] = $this->formatPriceInCSV($total_tax_9975, $a);
            $result[$key1]['total_tax_5'] = $this->formatPriceInCSV($total_tax_5, $a);
            $result[$key1]['id_currency'] = '';
            // since 1.0.21
            $products_name = $result[$key1]['products_name'];
            $result[$key1]['products_name'] = $this->formatProductsName($products_name);
        }
        $taxes_array = array(
            "ID shop",
            "Shop name",
            "Order ID",
            "Order Reference",
            "Order add date",
            "Products",
            "Total Products No Tax" ,
            "Product Tax",
            "Including Ecotax Tax Excl",
            "Including Ecotax Tax Amount",
            "Total Discounts Tax Excl",
            "Discounts Tax Amount",
            "Total Wrapping Tax Excl",
            "Wrapping Tax Amount",
            "Total Shipping Without Tax",
            "Shipping Tax Amount ",
            "Total Tax 5%",
            "Total Tax 9.975%",
            "Total Tax",
            "Last Name",
            "First Name",
            "Country ",
            "ID Country",
            "State",
            "Company",
            "Currency ISO"
        );
        if (count($taxes_array) == 0) {
            return null;
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment;filename="taxes'.date('Y-m-d H:i').'.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        $df = fopen("php://output", 'w');
        fprintf($df, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($df, $taxes_array);
        foreach ($result as $row) {
            fputcsv($df, $row);
        }
        exit();
    }
    public function revenuedata()
    {
        $where = Configuration::get($this->name.'_profit_where');
        $revenue_orderby = Tools::getValue("reportsaleba_report_profitOrderby");
        $revenue_orderway = Tools::getValue("reportsaleba_report_profitOrderway");
        $revenue = 'SELECT `id_shop`,
            `shop_name`,
            `id_order`,
            `reference`,
            `order_add_date`,
            `products_name`,
            `total_discounts_tax_excl`,
            `discounts_tax_amount`,
            `total_wrapping_tax_excl`,
            `wrapping_tax_amount`,
            `total_products_no_tax`,
            `product_tax`,
            `including_ecotax_tax_excl`,
            `including_ecotax_tax_amount`,
            `total_cost`,
            `gross_profit_before_discounts`,
            `net_profit_tax_excl`,
            `gross_margin_before_discounts`,
            `net_margin_tax_excl`,
            `email`,
            `last_name`,
            `fist_name`,
            `country`,
            `id_country`,
            `state`,
            `company`,
            `iso_currency`,
            `id_currency` 
            FROM `'._DB_PREFIX_.'ba_report_profit` ';
        $revenue .= $where;
        $revenue .= ' ORDER BY `'.pSQL($revenue_orderby).'` '.pSQL($revenue_orderway).'';
        $result = Db::getInstance()->executeS($revenue, true, false);
        foreach ($result as $key1 => $aaa) {
            $aaa;
            $a = (int)$result[$key1]['id_currency'];
            $total_products_no_tax = $result[$key1]['total_products_no_tax'];
            $including_ecotax_tax_excl = $result[$key1]['including_ecotax_tax_excl'];
            $total_discounts_tax_excl = $result[$key1]['total_discounts_tax_excl'];
            $discounts_tax_amount = $result[$key1]['discounts_tax_amount'];
            $total_wrapping_tax_excl = $result[$key1]['total_wrapping_tax_excl'];
            $wrapping_tax_amount = $result[$key1]['wrapping_tax_amount'];
            $gross_profit_before_disco = $result[$key1]['gross_profit_before_discounts'];
            $net_profit_tax_excl = $result[$key1]['net_profit_tax_excl'];
            $gross_margin_before_disco = $result[$key1]['gross_margin_before_discounts'];
            $net_margin_tax_excl = $result[$key1]['net_margin_tax_excl'];
            $product_tax = $result[$key1]['product_tax'];
            $including_ecotax_tax_amount = $result[$key1]['including_ecotax_tax_amount'];
            $total_cost = $result[$key1]['total_cost'];

            $result[$key1]['including_ecotax_tax_amount'] = $this->formatPriceInCSV($including_ecotax_tax_amount, $a);
            $result[$key1]['total_products_no_tax'] = $this->formatPriceInCSV($total_products_no_tax, $a);
            $result[$key1]['product_tax'] = $this->formatPriceInCSV($product_tax, $a);
            $result[$key1]['including_ecotax_tax_excl'] = $this->formatPriceInCSV($including_ecotax_tax_excl, $a);
            $result[$key1]['total_discounts_tax_excl'] = $this->formatPriceInCSV($total_discounts_tax_excl, $a);
            $result[$key1]['discounts_tax_amount'] = $this->formatPriceInCSV($discounts_tax_amount, $a);
            $result[$key1]['total_wrapping_tax_excl'] = $this->formatPriceInCSV($total_wrapping_tax_excl, $a);
            $result[$key1]['wrapping_tax_amount'] = $this->formatPriceInCSV($wrapping_tax_amount, $a);
            $result[$key1]['total_cost'] = $this->formatPriceInCSV($total_cost, $a);
            $result[$key1]['gross_profit_before_discounts'] = $this->formatPriceInCSV($gross_profit_before_disco, $a);
            $result[$key1]['net_profit_tax_excl'] = $this->formatPriceInCSV($net_profit_tax_excl, $a);
            $result[$key1]['gross_margin_before_discounts'] = $this->formatPriceInCSV($gross_margin_before_disco, $a);
            $result[$key1]['net_margin_tax_excl'] = $this->formatPriceInCSV($net_margin_tax_excl, $a);
            $result[$key1]['id_currency'] = '';
            // since 1.0.21
            $products_name = $result[$key1]['products_name'];
            $result[$key1]['products_name'] = $this->formatProductsName($products_name);
        }
        $revenue_array = array(
            "ID shop",
            "Shop name",
            "Order ID",
            "Order Reference",
            "Order add date",
            "Products",
            "Total Discounts Tax Excl " ,
            "Discounts Tax Amount ",
            "Total Wrapping Tax Excl",
            "Wrapping Tax Amount",
            "Total Products No Tax",
            "Product Tax ",
            "Including Ecotax Tax Excl",
            "Including Ecotax Tax Amount",
            "Total Cost ",
            "Gross Profit Before Discounts",
            "Net Profit Tax Excl ",
            "Gross Margin Before Discounts ",
            "Net Margin Tax Excl",
            "Email",
            "Last Name",
            "Fist Name",
            "Country",
            "ID Country",
            "State",
            "Company",
            "Currency ISO"
        );
        if (count($revenue_array) == 0) {
            return null;
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment;filename="revenue'.date('Y-m-d H:i').'.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        $df = fopen("php://output", 'w');
        fprintf($df, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($df, $revenue_array);
        foreach ($result as $row) {
            fputcsv($df, $row);
        }
        exit();
    }
    public function alldata()
    {
        $where = Configuration::get($this->name.'_all_where');
        $all_orderby = Tools::getValue("reportsaleba_report_fullOrderby");
        $all_orderway = Tools::getValue("reportsaleba_report_fullOrderway");
        $all = 'SELECT `id_shop`,
            `shop_name`,
            `id_order`,
            `reference`,
            `order_add_date`,
            `products_name`,
            `payment_method`,
            `carrier`,
            `weight`,
            `total_paid_with_tax`,
            `total_really_paid_with_tax`,
            `total_shipping_without_tax`,
            `shipping_tax_amount`,
            `total_discounts_tax_excl`,
            `discounts_tax_amount`,
            `total_wrapping_tax_excl`,
            `wrapping_tax_amount`,
            `total_products_no_tax`,
            `products_tax`,
            `including_ecotax_tax_excl`,
            `including_ecotax_tax_amount`,
            `total_tax_5`,
            `total_tax_9975`,
            `total_tax`,
            `total_cost`,
            `gross_profit_before_discounts`,
            `net_profit_tax_excl`,
            `gross_margin_before_discounts`,
            `net_margin_tax_excl`,
            `email`,
            `birthday`,
            `last_name`,
            `first_name`,
            `customer_adding_date`,
            `customer_updating_date`,
            `company`,
            `address_1`,
            `address_2`,
            `postcode`,
            `city`,
            `country`,
            `id_country`,
            `state`,
            `phone`,
            `iso_currency`,
            `id_currency` 
            FROM `'._DB_PREFIX_.'ba_report_full` ';
        $all .= $where;
        $all .= ' ORDER BY `'. pSQL($all_orderby) .'` '. pSQL($all_orderway) .'';
        $result = Db::getInstance()->executeS($all, true, false);
        foreach ($result as $key1 => $value1) {
            $a = (int)$value1['id_currency'];
            $total_products_no_tax = $result[$key1]['total_products_no_tax'];
            $including_ecotax_tax_excl = $result[$key1]['including_ecotax_tax_excl'];
            $total_shipping_without_tax = $result[$key1]['total_shipping_without_tax'];
            $shipping_tax_amount = $result[$key1]['shipping_tax_amount'];
            $total_discounts_tax_excl = $result[$key1]['total_discounts_tax_excl'];
            $discounts_tax_amount = $result[$key1]['discounts_tax_amount'];
            $total_wrapping_tax_excl = $result[$key1]['total_wrapping_tax_excl'];
            $wrapping_tax_amount = $result[$key1]['wrapping_tax_amount'];
            $total_tax_5 = $result[$key1]['total_tax_5'];
            $total_tax_9975 = $result[$key1]['total_tax_9975'];
            $total_tax = $result[$key1]['total_tax'];
            $gross_profit_before_disco = $result[$key1]['gross_profit_before_discounts'];
            $net_profit_tax_excl = $result[$key1]['net_profit_tax_excl'];
            $gross_margin_before_disco = $result[$key1]['gross_margin_before_discounts'];
            $net_margin_tax_excl = $result[$key1]['net_margin_tax_excl'];
            $products_tax = $result[$key1]['products_tax'];
            $total_really_paid_with_tax = $result[$key1]['total_really_paid_with_tax'];
            $including_ecotax_tax_amount = $result[$key1]['including_ecotax_tax_amount'];
            $total_cost = $result[$key1]['total_cost'];
            $total_paid_with_tax = $result[$key1]['total_paid_with_tax'];

            $result[$key1]['total_paid_with_tax'] = $this->formatPriceInCSV($total_paid_with_tax, $a);
            $result[$key1]['total_products_no_tax'] = $this->formatPriceInCSV($total_products_no_tax, $a);
            $result[$key1]['products_tax'] = $this->formatPriceInCSV($products_tax, $a);
            $result[$key1]['including_ecotax_tax_excl'] = $this->formatPriceInCSV($including_ecotax_tax_excl, $a);
            $result[$key1]['total_shipping_without_tax'] = $this->formatPriceInCSV($total_shipping_without_tax, $a);
            $result[$key1]['shipping_tax_amount'] = $this->formatPriceInCSV($shipping_tax_amount, $a);
            $result[$key1]['total_discounts_tax_excl'] = $this->formatPriceInCSV($total_discounts_tax_excl, $a);
            $result[$key1]['discounts_tax_amount'] = $this->formatPriceInCSV($discounts_tax_amount, $a);
            $result[$key1]['total_wrapping_tax_excl'] = $this->formatPriceInCSV($total_wrapping_tax_excl, $a);
            $result[$key1]['wrapping_tax_amount'] = $this->formatPriceInCSV($wrapping_tax_amount, $a);
            $result[$key1]['total_tax_5'] = $this->formatPriceInCSV($total_tax_5, $a);
            $result[$key1]['total_tax_9975'] = $this->formatPriceInCSV($total_tax_9975, $a);
            $result[$key1]['total_tax'] = $this->formatPriceInCSV($total_tax, $a);
            $result[$key1]['gross_profit_before_discounts'] = $this->formatPriceInCSV($gross_profit_before_disco, $a);
            $result[$key1]['net_profit_tax_excl'] = $this->formatPriceInCSV($net_profit_tax_excl, $a);
            $result[$key1]['gross_margin_before_discounts'] = $this->formatPriceInCSV($gross_margin_before_disco, $a);
            $result[$key1]['net_margin_tax_excl'] = $this->formatPriceInCSV($net_margin_tax_excl, $a);
            $result[$key1]['total_really_paid_with_tax'] = $this->formatPriceInCSV($total_really_paid_with_tax, $a);
            $result[$key1]['including_ecotax_tax_amount'] = $this->formatPriceInCSV($including_ecotax_tax_amount, $a);
            $result[$key1]['total_cost'] = $this->formatPriceInCSV($total_cost, $a);
            $result[$key1]['id_currency'] = '';
            // since 1.0.21
            $products_name = $result[$key1]['products_name'];
            $result[$key1]['products_name'] = $this->formatProductsName($products_name);
        }
        $all_array = array(
            "ID shop",
            "Shop name",
            "Order ID",
            "Order Reference",
            "Order add date",
            "Products",
            "Payment Method " ,
            "Carrier ",
            "Weight ",
            "Total Paid With Tax ",
            "Total Really Paid With Tax",
            "Total Shipping Without Tax ",
            "Shipping Tax Amount ",
            "Total Discounts Tax Excl ",
            "Discounts Tax Amount",
            "Total Wrapping Tax Excl ",
            "Wrapping Tax Amount",
            "Total Products No Tax",
            "Products Tax ",
            "Including Ecotax Tax Excl",
            "Including Ecotax Tax Amount",
            "Total Tax 5%",
            "Total Tax 9.975%",
            "Total Tax",
			"Total Cost ",
            "Gross Profit Before Discounts",
            "Net Profit Tax Excl",
            "Gross Margin Before Discounts",
            "Net Margin Tax Excl",
            "Email","Birthday",
            "Last Name ",
            "First Name",
            "Customer Adding Date",
            "Customer Updating Date",
            "Company",
            "Address 1",
            "address 2 ",
            "Postcode",
            "City",
            "Country",
            "ID Country",
            "State",
            "Phone",
            "Currency ISO"
        );
        if (count($all_array) == 0) {
            return null;
        }
        header('Content-type: text/csv');
        header('Content-Disposition: attachment;filename="all'.date('Y-m-d H:i').'.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        $df = fopen("php://output", 'w');
        fprintf($df, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($df, $all_array);
        foreach ($result as $row) {
            fputcsv($df, $row);
        }
        exit();
    }
    public function productdata()
    {
        $where = Configuration::get($this->name.'_product_where');
        $product_orderby = Tools::getValue("reportsaleba_report_productOrderby");
        $product_orderway = Tools::getValue("reportsaleba_report_productOrderway");
        $product = 'SELECT `id_shop`,
            `shop_name`,
            `products_id`,
            `product_reference`,
            `product_name`,
            `supplier_reference`,
            `supplier_name`,
            `EAN_reference`,
            `UPC_reference`,
            `current_stock`,
            `total_quantity`,
            `AVG_unit_price`,
            `tax_rate`,
            `total_discounts_tax_excl`,
            `discounts_tax_amount`,
            `total_products_no_tax`,
            `products_tax`,
            `including_ecotax_tax_amount`,
            `including_ecotax_tax_excl`,
            `net_tax_product_reduction`,
            `total_cost`,
            `gross_profit`,
            `gross_margin`,
            `net_margin`,
            `net_profit`,
            `manufacturer_name`,
            `category_name`,
            `of_total_sales`,
            `of_total_gross_profits`,
            `of_total_net_profits`
            FROM `'._DB_PREFIX_.'ba_report_products` ';
        $product .= $where;
        $product .= ' ORDER BY `'.pSQL($product_orderby).'` '.pSQL($product_orderway).'';
        $result = Db::getInstance()->executeS($product, true, false);
        $product_array = array(
            "ID shop",
            "Shop name",
            "Products ID",
            "Product Reference",
            "Product Name" ,
            "Supplier Reference ",
            "Supplier Name ",
            "EAN Reference",
            "UPC Reference",
            "Current Stock",
            "Total Quantity",
            "AVG Unit Price",
            "Tax Rate",
            "Total Discounts Tax Excl",
            "Discounts Tax Amount",
            "Total Products No Tax",
            "Products Tax",
            "Including Ecotax Tax Amount",
            "Including Ecotax Tax Excl",
            "Net Tax Product Reduction",
            "Total Cost",
            "Gross Profit",
            "Gross Margin",
            "Net Profit",
            "Net Margin",
            "Manufacturer Name",
            "Category Name",
            "% Of Total Sales",
            "% Of Total Gross profits",
            "% Of Total Net Profits"
        );
        if (count($product_array) == 0) {
            return null;
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment;filename="product'.date('Y-m-d H:i').'.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        $df = fopen("php://output", 'w');
        fprintf($df, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($df, $product_array);
        foreach ($result as $row) {
            fputcsv($df, $row);
        }
        exit();
    }
    public function manufacturersdata()
    {
        $where = Configuration::get($this->name.'_brand_where');
        $facturers_orderby = Tools::getValue("reportsaleba_report_brandOrderby");
        $facturers_orderway = Tools::getValue("reportsaleba_report_brandOrderway");
        $manufacturers = 'SELECT `id_shop`,
        `shop_name`,
        `manufacturer_id`,
        `manufacturer_name`,
        `total_quantity`,
        `total_discounts_tax_excl`,
        `total_products_no_tax`,
        `including_ecotax_tax_excl`,
        `total_cost`,
        `gross_profit`,
        `gross_margin`,
        `net_profit`,
        `net_margin`,
        `of_total_sales`,
        `of_total_gross_profits`,
        `of_total_net_profits`
        FROM `'._DB_PREFIX_.'ba_report_brand` ';
        $manufacturers .= $where;
        $manufacturers .= ' ORDER BY `'.pSQL($facturers_orderby).'` '.pSQL($facturers_orderway);
        $result = Db::getInstance()->executeS($manufacturers, true, false);
        $manufacturers_array = array(
            "ID shop",
            "Shop name",
            "Manufacturer ID",
            "Manufacturer Name",
            "Total Quantity " ,
            "Total Discounts Tax Excl",
            "Total Products No Tax",
            "Including Ecotax Tax Excl",
            "Total Cost",
            "Gross Profit",
            "Gross Margin",
            "Net Profit",
            "Net Margin",
            "% Of Total Sales",
            "% Of Total Gross Profits",
            "% Of Total Net Profits"
        );
        if (count($manufacturers_array) == 0) {
            return null;
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment;filename="manufacturers'.date('Y-m-d H:i').'.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        $df = fopen("php://output", 'w');
        fprintf($df, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($df, $manufacturers_array);
        foreach ($result as $row) {
            fputcsv($df, $row);
        }
        exit();
    }
    public function supplierdata()
    {
        $where = Configuration::get($this->name.'_supplier_where');
        $supplier_orderby = Tools::getValue("reportsaleba_report_supplierOrderby");
        $supplier_orderway = Tools::getValue("reportsaleba_report_supplierOrderway");
        $supplier = 'SELECT `id_shop`,
        `shop_name`,
        `supplier_id`,
        `supplier_name`,
        `total_quantity`,
        `total_discounts_tax_excl`,
        `discounts_tax_amount`,
        `total_products_no_tax`,
        `including_ecotax_tax_excl`,
        `total_cost`,
        `gross_profit`,
        `gross_margin`,
        `net_profit`,
        `net_margin`,
        `of_total_sales`,
        `of_total_gross_profits`,
        `of_total_net_profits`
        FROM `'._DB_PREFIX_.'ba_report_supplier` ';
        $supplier .= $where;
        $supplier .= ' ORDER BY `'.pSQL($supplier_orderby).'` '.pSQL($supplier_orderway).'';
        $result = Db::getInstance()->executeS($supplier, true, false);
        $supplier_array = array(
            "ID shop",
            "Shop name",
            "Supplier ID",
            "Supplier Name",
            "Total Quantity " ,
            "Total Discounts Tax Excl",
            "Total Products No Tax",
            "Including Ecotax Tax Excl",
            "Discounts Tax Amount",
            "Total Products No Tax",
            "Including Ecotax Tax Excl",
            "Total Cost",
            "Gross Profit",
            "Gross Margin",
            "Net Profit",
            "Net Margin",
            "% Of Total Sales",
            "% Of Total Gross Profits",
            "% Of Total Net Profits"
        );
        if (count($supplier_array) == 0) {
            return null;
        }
        header('Content-type: text/csv');
        header('Content-Disposition: attachment;filename="supplier'.date('Y-m-d H:i').'.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        $df = fopen("php://output", 'w');
        fprintf($df, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($df, $supplier_array);
        foreach ($result as $row) {
            fputcsv($df, $row);
        }
        exit();
    }
    public function categorydata()
    {
        $where = Configuration::get($this->name.'_category_where');
        $category_orderby = Tools::getValue("reportsaleba_report_categoryOrderby");
        $category_orderway = Tools::getValue("reportsaleba_report_categoryOrderway");
        $category = 'SELECT `id_shop`,
        `shop_name`,
        `category_id`,
        `category_name`,
        `total_quantity`,
        `total_discounts_tax_excl`,
        `discounts_tax_amount`,
        `total_products_no_tax`,
        `including_ecotax_tax_excl`,
        `total_cost`,`gross_profit`,
        `gross_margin`,
        `net_profit`,
        `net_margin`,
        `of_total_sales`,
        `of_total_gross_profits`,
        `of_total_net_profits`
        FROM `'._DB_PREFIX_.'ba_report_category` ';
        $category .= $where;
        $category .= ' ORDER BY `'.pSQL($category_orderby).'` '.pSQL($category_orderway).'';
        $result = Db::getInstance()->executeS($category, true, false);
        $category_array = array(
            "ID shop",
            "Shop name",
            "Category ID",
            "Category Name",
            "Total Quantity " ,
            "Total Discounts Tax Excl",
            "Total Products No Tax",
            "Including Ecotax Tax Excl",
            "Total Cost",
            "Gross Margin",
            "Net Profit",
            "Net Margin",
            "% Of Total Sales",
            "% Of Total Gross Profits",
            "% Of Total Net Profits"
        );
        if (count($category_array) == 0) {
            return null;
        }
        header('Content-type: text/csv');
        header('Content-Disposition: attachment;filename="category'.date('Y-m-d H:i').'.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        $df = fopen("php://output", 'w');
        fprintf($df, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($df, $category_array);
        foreach ($result as $row) {
            fputcsv($df, $row);
        }
        exit();
    }
    public function clientdata()
    {
        $where = Configuration::get($this->name.'_customer_where');
        $client_orderby = Tools::getValue("reportsaleba_report_customerOrderby");
        $client_orderway = Tools::getValue("reportsaleba_report_customerOrderway");
        $client = 'SELECT `id_shop`,
        `shop_name`,
        `customer_id`,
        `last_name`,
        `first_name`,
        `email`,
        `company`,
        `address_1`,
        `address_2`,
        `postcode`,
        `city`,
        `country`,
        `id_country`,
        `phone`,
        `first_order`,
        `last_order`,
        `of_order`,
        `of_products_ordered`,
        `average_cart_all_included`,
        `products_ordered`,
        `total_products_no_tax`,
        `total_cost`,
        `total_discounts_tax_excl`,
        `gross_profit`,
        `net_profit`,
        `gross_margin`,
        `net_margin`
        FROM `'._DB_PREFIX_.'ba_report_customer` ';
        $client .= $where;
        $client .= ' ORDER BY `'. pSQL($client_orderby) .'` '. pSQL($client_orderway);
        $result = Db::getInstance()->executeS($client, true, false);
        $client_array = array(
            "ID shop",
            "Shop name",
            "Customer ID",
            "Last Name",
            "First Name",
            "Email",
            "Company",
            "address 1",
            "Address 2",
            "Postcode",
            "City",
            "Country",
            "ID Country",
            "Phone",
            "First Order",
            "Last Order",
            "Of Order",
            "Of Products Ordered",
            "Average Cart All Included",
            "Products Ordered",
            "Total Products No Tax","Total Cost",
            "Total Discounts Tax Excl",
            "Gross Profit",
            "Net Profit",
            "Gross Margin",
            "Net Margin"
        );
        if (count($client_array) == 0) {
            return null;
        }
        header('Content-type: text/csv');
        header('Content-Disposition: attachment;filename="client'.date('Y-m-d H:i').'.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        $df = fopen("php://output", 'w');
        fprintf($df, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($df, $client_array);
        foreach ($result as $row) {
            fputcsv($df, $row);
        }
        exit();
    }
    public function creditslipsdata()
    {
        $where = Configuration::get($this->name.'_credit_where');
        $credit_orderby = Tools::getValue("reportsaleba_report_storecreditOrderby");
        $credit_orderway = Tools::getValue("reportsaleba_report_storecreditOrderway");
        $creditslips = 'SELECT `shop_name`,
        `credit_slip_id`,
        `id_order`,
        `reference`,
        `first_name`,
        `last_name`,
        `credit_slip_date`,
        `payment_method`,
        `total_products_no_tax`,
        `products_tax`,
        `total_shipping_without_tax`,
        `shipping_tax_amount`,
        `total_no_tax`,
        `total_tax`,
        `total_tax_incl`,
        `iso_currency`,
        `id_currency`,
        `country`,
        `state` 
        FROM `'._DB_PREFIX_.'ba_report_store_credit` ';
        $creditslips .= $where;
        $creditslips .= ' ORDER BY `'.pSQL($credit_orderby).'` '.pSQL($credit_orderway);
        $result = Db::getInstance()->executeS($creditslips, true, false);
        foreach ($result as $key1 => $aaa) {
            $aaa;
            $a = (int)$result[$key1]['id_currency'];
            $total_products_no_tax = $result[$key1]['total_products_no_tax'];
            $total_shipping_without_tax = $result[$key1]['total_shipping_without_tax'];
            $shipping_tax_amount = $result[$key1]['shipping_tax_amount'];
            $total_tax = $result[$key1]['total_tax'];
            $products_tax = $result[$key1]['products_tax'];
            $total_no_tax = $result[$key1]['total_no_tax'];
            $total_tax_incl = $result[$key1]['total_tax_incl'];

            $result[$key1]['total_products_no_tax'] = $this->formatPriceInCSV($total_products_no_tax, (int)$a);
            $result[$key1]['products_tax'] = $this->formatPriceInCSV($products_tax, (int)$a);
            $result[$key1]['total_shipping_without_tax'] = $this->formatPriceInCSV($total_shipping_without_tax, $a);
            $result[$key1]['shipping_tax_amount'] = $this->formatPriceInCSV($shipping_tax_amount, (int)$a);
            $result[$key1]['total_no_tax'] = $this->formatPriceInCSV($total_no_tax, (int)$a);
            $result[$key1]['total_tax'] = $this->formatPriceInCSV($total_tax, (int)$a);
            $result[$key1]['total_tax_incl'] = $this->formatPriceInCSV($total_tax_incl, (int)$a);
            $result[$key1]['id_currency'] = $a;
        }
        $creditslips_array = array(
            "Shop name",
            "Credit Slip ID",
            "ID Order",
            "Order Reference",
            "First Name",
            "Last Name",
            "Credit Slip Date",
            "Payment Method",
            "Total Products No Tax",
            "Products Tax",
            "Total Shipping Without Tax",
            "Shipping Tax Amount",
            "Total No Tax",
            "Total Tax",
            "Total Tax Incl",
            "Currency ISO",
            "ID Currency",
            "Country",
            "State"
        );
        if (count($creditslips_array) == 0) {
            return null;
        }
        header('Content-type: text/csv');
        header('Content-Disposition: attachment;filename="creditslips'.date('Y-m-d H:i').'.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        $df = fopen("php://output", 'w');
        fprintf($df, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($df, $creditslips_array);
        foreach ($result as $row) {
            fputcsv($df, $row);
        }
        exit();
    }
    public function calcDiscount($product)
    {
        $response = array(
            'total_discounts_tax_excl' => 0,
            'discounts_tax_amount' => 0,
        );
        
        if ($product['reduction_amount'] >0) {
            $unit_discounts_tax_excl = $product['reduction_amount_tax_excl'];
            $response['total_discounts_tax_excl'] += $unit_discounts_tax_excl* $product['product_quantity'];
            $re = $product['reduction_amount_tax_incl'];
            $response['discounts_tax_amount'] += ($re - $unit_discounts_tax_excl) * $product['product_quantity'];
        }
        if ($product['reduction_percent'] > 0) {
            $u_price = $product['unit_price_tax_excl'];
            $original_price = $product['original_product_price'];
            $unit_price_before_discount_excl_tax = $original_price;
            $uni_dis_t_excl = $unit_price_before_discount_excl_tax - $product['unit_price_tax_excl'];
            $response['total_discounts_tax_excl'] += $uni_dis_t_excl * $product['product_quantity'];
            $rate = $product['tax_rate'];
            $response['discounts_tax_amount'] += ($rate/100)*$uni_dis_t_excl * $product['product_quantity'];
        }
        return $response;
    }
    /********* calculate discounts for an Order */
    public function calcDiscountOrder($order)
    {
        $total_discounts_tax_excl = 0;
        $discounts_tax_amount = 0;
        $cartrules = $order->getCartRules();
        
        $countcartrules = count($cartrules);
        for ($i = 0; $i < $countcartrules; $i++) {
            $tx_rules = $cartrules[$i];
            $value = $tx_rules['value'];
            $total_discounts_tax_excl = $total_discounts_tax_excl + $tx_rules['value_tax_excl'];
            $discounts_tax_amount = $discounts_tax_amount + ($value - $tx_rules['value_tax_excl']);
        }
        $response = array(
            'total_discounts_tax_excl' => $total_discounts_tax_excl,
            'discounts_tax_amount' => $discounts_tax_amount,
        );
        return $response;
    }
    public function resetBaConfig($prefix)
    {
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_);
        $sql = 'DELETE FROM '._DB_PREFIX_.'configuration WHERE name LIKE "'.pSQL($prefix).'%"';
        $db->query($sql);
        if ($prefix == 'BS_') {
            $sql = 'DELETE FROM '._DB_PREFIX_.'ba_report_basic';
            $db->query($sql);
        }
        if ($prefix == 'TO_') {
            $sql = 'DELETE FROM '._DB_PREFIX_.'ba_report_tax_only';
            $db->query($sql);
        }
        if ($prefix == 'PF_') {
            $sql = 'DELETE FROM '._DB_PREFIX_.'ba_report_profit';
            $db->query($sql);
        }
        if ($prefix == 'FU_') {
            $sql = 'DELETE FROM '._DB_PREFIX_.'ba_report_full';
            $db->query($sql);
        }
        if ($prefix == 'PR_') {
            $sql = 'DELETE FROM '._DB_PREFIX_.'ba_report_products';
            $db->query($sql);
        }
        if ($prefix == 'BR_') {
            $sql = 'DELETE FROM '._DB_PREFIX_.'ba_report_brand';
            $db->query($sql);
        }
        if ($prefix == 'SL_') {
            $sql = 'DELETE FROM '._DB_PREFIX_.'ba_report_supplier';
            $db->query($sql);
        }
        if ($prefix == 'CT_') {
            $sql = 'DELETE FROM '._DB_PREFIX_.'ba_report_category';
            $db->query($sql);
        }
        if ($prefix == 'CM_') {
            $sql = 'DELETE FROM '._DB_PREFIX_.'ba_report_customer';
            $db->query($sql);
        }
        if ($prefix == 'SC_') {
            $sql = 'DELETE FROM '._DB_PREFIX_.'ba_report_store_credit';
            $db->query($sql);
        }
    }
    public function cookiekeymodule()
    {
        $keygooglecookie = sha1(_COOKIE_KEY_ . 'reportsale');
        $md5file = md5($keygooglecookie);
        return $md5file;
    }
    public function getProductsNameOfOrder($order)
    {
        $products = $order->getproducts();
        $result = array();
        if (!empty($products)) {
            foreach ($products as $p) {
                $result[] = $p['product_quantity'].$this->l(' x ').$p['product_name'];
            }
        }
        return implode("\n", $result);
    }
    public function displayProducts($value)
    {
        return nl2br($value);
    }
    public function displayInvoiceNumber($value, $row)
    {
        if (empty($value)) {
            return $this->l('-');
        }
        $id_lang = Context::getContext()->cookie->id_lang;
        $id_shop = $row['id_shop'];
        $formatted = Configuration::get('PS_INVOICE_PREFIX', $id_lang, null, $id_shop).sprintf('%06d', $value);
        return $formatted;
    }
    public function displayFormattedDate($value)
    {
        if (empty($value) || $value=='0000-00-00 00:00:00') {
            return $this->l('-');
        }
        return Tools::displayDate($value);
    }
    public function formatPriceInCSV($price, $id_currency)
    {
        if ($this->export_without_cs == true) {
            return $price;
        }
        return Tools::displayPrice($price, $id_currency);
    }
    public function shortDisplay($file)
    {
        return $this->display(__FILE__, $file);
    }
    public function getCategoryName($id_category, $id_shop, $id_lang = null)
    {
        if ($id_lang == null) {
            $id_lang = $this->context->language->id;
        }
        $sql = "SELECT name FROM ". _DB_PREFIX_ . 'category_lang ';
        $sql .= " WHERE id_lang = ". (int) $id_lang;
        $sql .= " AND id_category = ". (int) $id_category;
        $sql .= " AND id_shop = ". (int) $id_shop;
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }
    public function getSupplierName($id_supplier)
    {
        $sql = "SELECT name FROM ". _DB_PREFIX_ . 'supplier ';
        $sql .= " WHERE ";
        $sql .= " id_supplier = ". (int) $id_supplier;
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }
    public function getShopName($id_shop)
    {
        $sql = 'SELECT name FROM ' . _DB_PREFIX_ . 'shop '
            . 'WHERE id_shop='.(int)$id_shop;
        $data = DB::getInstance()->executeS($sql, true, false);
        $value=$data[0];
        return $value['name'];
    }
    public function convertProductToDefaultCurrenct($product, $c_from)
    {
        $default_currency = (int) Configuration::get('PS_CURRENCY_DEFAULT');
        $c_to = new Currency($default_currency);
        // convert all currrency to Default currency before save
        $product['product_price'] = Tools::convertPriceFull($product['product_price'], $c_from, $c_to);
        $product['reduction_amount'] = Tools::convertPriceFull($product['reduction_amount'], $c_from, $c_to);
        $r = $product['reduction_amount_tax_incl'];
        $product['reduction_amount_tax_incl'] = Tools::convertPriceFull($r, $c_from, $c_to);
        $r = $product['reduction_amount_tax_excl'];
        $product['reduction_amount_tax_excl'] = Tools::convertPriceFull($r, $c_from, $c_to);
        $product['group_reduction'] = (double) $product['group_reduction'];
        $product['group_reduction'] = Tools::convertPriceFull($product['group_reduction'], $c_from, $c_to);
        $r = $product['product_quantity_discount'];
        $product['product_quantity_discount'] = Tools::convertPriceFull($r, $c_from, $c_to);
        $product['ecotax'] = Tools::convertPriceFull($product['ecotax'], $c_from, $c_to);
        $product['total_price_tax_incl'] = Tools::convertPriceFull($product['total_price_tax_incl'], $c_from, $c_to);
        $product['total_price_tax_excl'] = Tools::convertPriceFull($product['total_price_tax_excl'], $c_from, $c_to);
        $product['unit_price_tax_incl'] = Tools::convertPriceFull($product['unit_price_tax_incl'], $c_from, $c_to);
        $product['unit_price_tax_excl'] = Tools::convertPriceFull($product['unit_price_tax_excl'], $c_from, $c_to);
        $t = $product['total_shipping_price_tax_incl'];
        $product['total_shipping_price_tax_incl'] = Tools::convertPriceFull($t, $c_from, $c_to);
        $t = $product['total_shipping_price_tax_excl'];
        $product['total_shipping_price_tax_excl'] = Tools::convertPriceFull($t, $c_from, $c_to);
        $p = $product['purchase_supplier_price'];
        $product['purchase_supplier_price'] = Tools::convertPriceFull($p, $c_from, $c_to);
        $o = $product['original_product_price'];
        $product['original_product_price'] = Tools::convertPriceFull($o, $c_from, $c_to);
        $o = $product['original_wholesale_price'];
        $product['original_wholesale_price'] = Tools::convertPriceFull($o, $c_from, $c_to);
        $product['price'] = Tools::convertPriceFull($product['price'], $c_from, $c_to);
        // wholesale_price always saved in default_currency
        $product['wholesale_price'] = (float) $product['wholesale_price'];
        $product['unit_price_ratio'] = Tools::convertPriceFull($product['unit_price_ratio'], $c_from, $c_to);
        $a = $product['additional_shipping_cost'];
        $product['additional_shipping_cost'] = Tools::convertPriceFull($a, $c_from, $c_to);
        $product['product_price_wt'] = Tools::convertPriceFull($product['product_price_wt'], $c_from, $c_to);
        $p = $product['product_price_wt_but_ecotax'];
        $product['product_price_wt_but_ecotax'] = Tools::convertPriceFull($p, $c_from, $c_to);
        $product['total_wt'] = Tools::convertPriceFull($product['total_wt'], $c_from, $c_to);
        $product['total_price'] = Tools::convertPriceFull($product['total_price'], $c_from, $c_to);
        return $product;
    }
    // since 1.0.20
    public function formatNumber($value)
    {
        return  number_format($value);
    }
    /* since 1.0.21 */
    // calculate original_wholesale_price
    public function getWholeSalePrice($product_id, $product_attribute_id = 0)
    {
        $product = new Product($product_id);
        $wholesale_price = $product->wholesale_price;
        if (!empty($product_attribute_id)) {
            $combination = new Combination((int) $product_attribute_id);
            if ($combination && $combination->wholesale_price != '0.000000') {
                $wholesale_price = $combination->wholesale_price;
            }
        }
        return $wholesale_price;
    }
    public function displayWeight($value)
    {
        $w = number_format($value, 4).' '. Configuration::get('PS_WEIGHT_UNIT');
        return $w;
    }
    public function formatProductsName($text)
    {
        if ($this->pname_with_breakline) {
            return $text;
        } else {
            return str_replace("\n", ";", $text);
        }
    }
	public function updateTaxesBreakdown($table, $primany_column, $order)
    {
		$db = Db::getInstance(_PS_USE_SQL_SLAVE_);
		$total_tax_5 = 0;
		$total_tax_9975 = 0;
		$breakdowns = array(
            'product_tax' => $this->getProductTaxesBreakdown($order),
            'shipping_tax' => $order->getShippingTaxesBreakdown(),
			'ecotax_tax' => $order->getEcoTaxTaxesBreakdown(),
        );
		//echo '<pre>';var_dump($order);die;
		if (!empty($breakdowns['product_tax'])) {
			foreach ($breakdowns['product_tax'] as $item){
				if ($item['rate'] == 5.0) {
					$total_tax_5 += $item['total_amount'];
				}
				if ($item['rate'] >= 9.975 && $item['rate'] <= 9.98) {
					$total_tax_9975 += $item['total_amount'];
				}
			}
		}
		if (!empty($breakdowns['shipping_tax'])) {
			foreach ($breakdowns['shipping_tax'] as $item){
				if ($item['rate'] == 5.0) {
					$total_tax_5 += $item['total_amount'];
				}
				if ($item['rate'] >= 9.975 && $item['rate'] <= 9.98) {
					$total_tax_9975 += $item['total_amount'];
				}
			}
		}
		if (!empty($breakdowns['ecotax_tax'])) {
			foreach ($breakdowns['ecotax_tax'] as $item){
				if ($item['ecotax_tax_rate'] && 5.0) {
					$total_tax_5 += $item['ecotax_tax_incl'] - $item['ecotax_tax_excl'];
				}
				if ($item['ecotax_tax_rate'] >= 9.975 || $item['ecotax_tax_rate'] <= 9.98) {
					$total_tax_9975 += $item['ecotax_tax_incl'] - $item['ecotax_tax_excl'];
				}
			}
		}
		$data = array(
			'total_tax_5' => $total_tax_5,
			'total_tax_9975' => $total_tax_9975,
		);
		$address = new Address($order->id_address_invoice);
		$id_state = (int) $address->id_state; 
		//  We need the column "Total tax 5%" to remain blank for every province except of course Quebec
		if ($id_state != 90) {
			$data['total_tax_5'] = 0;
		}
		$db->update($table, $data, "$primany_column = ".$order->id);
		return true;
    }
	public function updateRefund($table, $primany_column, $order)
    {
		$id_order = $order->id;
		$db = Db::getInstance(_PS_USE_SQL_SLAVE_);
		
		$sql = "SELECT SUM(product_quantity_refunded) FROM ". _DB_PREFIX_ . 'order_detail ';
        $sql .= " WHERE id_order = ". (int) $id_order;
        $refunded_quantity = (int) $db->getValue($sql);
		
		$sql = "SELECT SUM(total_products_tax_excl + total_shipping_tax_excl) FROM ". _DB_PREFIX_ . 'order_slip ';
        $sql .= " WHERE id_order = ". (int) $id_order;
        $refunded_amount = (double) $db->getValue($sql);
		
		$sql = "SELECT SUM(total_products_tax_incl + total_shipping_tax_incl - total_products_tax_excl - total_shipping_tax_excl) AS refunded_tax FROM ". _DB_PREFIX_ . 'order_slip ';
        $sql .= " WHERE id_order = ". (int) $id_order;
        $refunded_tax = (double) $db->getValue($sql);
		
		$data = array(
			'refunded_quantity' => $refunded_quantity,
			'refunded_amount' => $refunded_amount,
			'refunded_tax' => $refunded_tax,
		);
		$db->update($table, $data, "$primany_column = ".$order->id);

		return true;
	}
}
