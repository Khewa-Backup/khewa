<?php
/**
 *
 * NOTICE OF LICENSE
 *
 *  @author    IntelliPresta <tehran.alishov@gmail.com>
 *  @copyright 2020 IntelliPresta
 *  @license   Commercial License
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class OrdersExportSalesReportPro extends Module
{

    protected $config_form = false;
    public $time_start;

    public function __construct()
    {
        $this->name = 'ordersexportsalesreportpro';
        $this->tab = 'export';
        $this->version = '4.1.7';
        $this->author = 'IntelliPresta';
        $this->need_instance = 1;
        $this->ps_versions_compliancy = array('min' => '1.5.6.3', 'max' => _PS_VERSION_);
        $this->bootstrap = true;
        $this->module_key = '01ba84734e29e58c71ae4ca819b8a4d0';
        $this->controller = 'AdminOrdersExportSalesReportPro';

        parent::__construct();

        $this->confirmUninstall = $this->l('All the saved settings will be lost. Are you sure to uninstall Best Sales Reports & Accounting Exports module?');

        $this->displayName = $this->l('Advanced Orders Exports & Reports');
        $this->description = $this->l('With this module you will be able to get reports of your sales in Excel, CSV, HTML & PDF files, and also send them to emails and FTP addresses.');
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update.
     */
    public function install()
    {

        $langs    = Language::getLanguages();
        $tabvalue = array(
            array(
                'class_name' => 'AdminReportExport',
                'id_parent'  => (int)Db::getInstance()->getValue(
                    'SELECT `id_tab`
    FROM `'._DB_PREFIX_.'tab`
    WHERE `class_name` = \'SELL\''
                ),
                'module'     => 'ordersexportsalesreportpro',
                'name'       => 'Export on Click',
            ),
        );
        foreach ( $tabvalue as $tab ) {
            $newtab             = new Tab();
            $newtab->class_name = $tab['class_name'];
            $newtab->module     = $tab['module'];
            $newtab->id_parent  = $tab['id_parent'];
            foreach ( $langs as $l ) {
                $newtab->name[ $l['id_lang'] ] = $this->l( $tab['name'] );
            }
//            $newtab->add( true, false );
            $newtab->save();
        }



        include dirname(__FILE__) . '/sql/install.php';
        $tab = new Tab();
        $tab->module = $this->name;
        $langs = Language::getLanguages();
        $tab->name = array();
        foreach ($langs as $lang) {
            $tab->name[$lang['id_lang']] = $this->l('Advanced Orders Exports & Reports');
        }
        $tab->id_parent = $tab->getIdFromClassName('DEFAULT');
        $tab->class_name = "AdminOrdersExportSalesReportPro";
        $tab->icon = 'trending_up';
        $tab->active = 0;
        return parent::install() &&
            $tab->save() &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('actionOrderHistoryAddAfter');
    }

    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }

        include dirname(__FILE__) . '/sql/uninstall.php';

        // Retrieve Tab ID
        $id_tab = (int) Tab::getIdFromClassName('AdminOrdersExportSalesReportPro');

        // Load tab
        $tab = new Tab((int) $id_tab);
        // Delete it
        return $tab->delete();
    }

    public function getConfig()
    {
        $confs = DB::getInstance()->executeS('SELECT 
                                                    `id_orders_export_srpro`, 
                                                    `name`,
                                                    `configuration`,
                                                    `datatables`,
                                                    "' . $this->l('Delete') . '" `title`
                                                FROM `' . _DB_PREFIX_ . 'orders_export_srpro`
                                                WHERE `name` <> "orders_default"
                                                ORDER BY `id_orders_export_srpro` DESC;');
        foreach ($confs as &$conf) {
            $conf['configuration'] = str_replace("'", '&apos;', $conf['configuration']);
        }
        return $confs;
    }

    /**
     * Load the configuration form.
     */
    public function getContent()
    {



        //
//
//        error_reporting(E_ERROR | E_PARSE);
//        ini_set('max_execution_time', 0);
        
        /*
         * If values have been submitted in the form, process.
         */




        if($this->context->employee->id_profile == '4'){
            if(!isset($_GET['auto_export'])){

                $AdminReportExportController = Context::getContext()->link->getAdminLink('AdminDashboard', true);
                Tools::redirectAdmin($AdminReportExportController);
            }
        }


        if(isset($_GET['auto_export'])){

            if($_GET['auto_export'] == 'true'){

                $do_export =  $this->doExport();

                exit;
            }
        }
        if ((bool) Tools::isSubmit('orders_export_as')) {

            $this->doExport();
            exit;
        }

        $lang_id = $this->context->language->id;

//        $catIDs = DB::getInstance()->getValue('SELECT GROUP_CONCAT(id_category) cats FROM ' . _DB_PREFIX_ . 'category;');

        $tree = new HelperTreeCategories(
            'data_export_orders_categories_tree',
            $this->l('Filter by Category'),
            (int) Category::getRootCategory()->id,
            $lang_id,
            false
        );

        $tree->setUseCheckBox(true)
            ->setUseSearch(true)
            ->setInputName('products_categories')
            ->setFullTree(true)
            ->setUseShopRestriction(false);
//            ->setSelectedCategories(explode(',', $catIDs));

//        if (Tools::substr($admin_link = $this->context->link->getAdminLink('AdminOrdersExportSalesReportPro', false), 0, 4) === "http") {
//            $schedule_url = $admin_link . '&schedule=' . md5(Configuration::get('OXSRP_SECURE_KEY'));
//        } else {
//            $schedule_url = _PS_BASE_URL_ . __PS_BASE_URI__ . basename(_PS_ADMIN_DIR_) . '/' . $admin_link . '&schedule=' . md5(Configuration::get('OXSRP_SECURE_KEY'));
//        }
        
        $schedule_url = $this->context->link->getModuleLink('ordersexportsalesreportpro', 'export', array('schedule' => md5(Configuration::get('OXSRP_SECURE_KEY'))));
        
        $this->context->smarty->assign(array(
            'show_id_tax_rule_group' => version_compare(_PS_VERSION_, '1.6.1.0'),
            'show_orijinal_wholesale_price' => version_compare(_PS_VERSION_, '1.6.1.3'),
            'show_isbn' => version_compare(_PS_VERSION_, '1.7.0.0'),
            'lang_id' => $lang_id,
            'lang_iso_code' => $this->context->language->iso_code,
            'languages' => $this->context->controller->getLanguages(),
            'image_types' => ImageType::getImagesTypes(),
            'categories_tree' => $tree->render(),
            'module_dir' => $this->_path,
            'configs' => $this->getConfig(),
            'controller_link' => str_replace('index.php', 'ajax-tab.php', $this->context->link->getAdminLink('AdminOrdersExportSalesReportPro')),
            'autoexport_enabled' => Configuration::get('OXSRP_AEXP_ENABLE'),
            'autoexport_on_what' => explode(';', Configuration::get('OXSRP_AEXP_ON_WHAT')),
            'autoexport_email_enabled' => Configuration::get('OXSRP_AEXP_USE_EMAIL'),
            'autoexport_ftp_enabled' => Configuration::get('OXSRP_AEXP_USE_FTP'),
            'autoexport_dont_send_empty' => Configuration::get('OXSRP_AUTOEXP_DNSEM'),
            'schedule_enabled' => Configuration::get('OXSRP_SCHDL_ENABLE'),
            'schedule_email_enabled' => Configuration::get('OXSRP_SCHDL_USE_EMAIL'),
            'schedule_ftp_enabled' => Configuration::get('OXSRP_SCHDL_USE_FTP'),
            'schedule_dont_send_empty' => Configuration::get('OXSRP_SCHDL_DNSEM'),
            'order_states' => OrderState::getOrderStates($lang_id),
            'target_action_ftp_url' => filter_input(INPUT_SERVER, 'SERVER_NAME'),
            'schedule_url' => $schedule_url
        ));

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/export.tpl');

        return $output;
    }

    public function doExport()
    {
        require_once dirname(__FILE__) . '/classes/SalesExportHelper.php';
        require_once dirname(__FILE__) . '/classes/ExportSales.php';
        $orders = new ExportSales($this);
        $orders->run();
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {

        $this->context->controller->addCSS($this->_path . 'views/css/menu_tab_icon.css', 'all');
        if (Tools::getValue('configure') == $this->name) {
            if (version_compare(_PS_VERSION_, '1.6.0.5') === -1) {
                $this->context->controller->addJS($this->_path . 'views/js/bootstrap.min.js');
                $this->context->controller->addJQueryUI('ui.datepicker');
                $this->context->controller->addCSS($this->_path . 'views/css/bootstrap.min.css', 'all');
                $this->context->controller->addCSS($this->_path . 'views/css/font.css', 'all');
                $this->context->controller->addCSS($this->_path . 'views/css/1.5.css', 'all');
            }
            $this->context->controller->addJquery();
            $this->context->controller->addJS($this->_path . 'views/js/datatables.min.js');
            $this->context->controller->addJS($this->_path . 'views/js/dataTables.checkboxes.min.js');
            $this->context->controller->addJS($this->_path . 'views/js/orders_export.js');
            $this->context->controller->addJQueryUI('ui.sortable');
//            $this->context->controller->addCSS($this->_path.'views/css/font-awesome.min.css', 'all');
            $this->context->controller->addCSS($this->_path . 'views/css/datatables.min.css', 'all');
            $this->context->controller->addCSS($this->_path . 'views/css/dataTables.checkboxes.css', 'all');
//            $this->context->controller->addCSS($this->_path.'views/css/awesome-bootstrap-checkbox.css', 'all');
            $this->context->controller->addCSS($this->_path . 'views/css/orders_export.css', 'all');
            $this->context->controller->addJqueryPlugin('tagify');
        }
    }

    public function hookActionOrderHistoryAddAfter($params)
    {
        error_reporting(E_ERROR | E_PARSE);
        ini_set('max_execution_time', 0);
        
        $state_count = (int) Db::getInstance()->getValue('SELECT COUNT(id_order_history) c FROM ' . _DB_PREFIX_ . 'order_history
            WHERE id_order = ' . (int) $params['order_history']->id_order);
        
        if (!$params['order_history']->id_employee && $state_count <= 1 && Configuration::get('OXSRP_AEXP_ENABLE') == '1' &&
            in_array('0', explode(';', Configuration::get('OXSRP_AEXP_ON_WHAT'))) &&
            (Configuration::get('OXSRP_AEXP_USE_EMAIL') == '1' || Configuration::get('OXSRP_AEXP_USE_FTP') == '1')) {
            require_once dirname(__FILE__) . '/classes/SalesExportHelper.php';
            require_once dirname(__FILE__) . '/classes/ExportSales.php';
            $orders = new ExportSales($this, $this->_path);
            $orders->run($params['order_history']->id_order, 0);
        } elseif ($params['order_history']->id_employee && $state_count > 1 && Configuration::get('OXSRP_AEXP_ENABLE') == '1' &&
            in_array($params['order_history']->id_order_state, explode(';', Configuration::get('OXSRP_AEXP_ON_WHAT'))) &&
            (Configuration::get('OXSRP_AEXP_USE_EMAIL') == '1' || Configuration::get('OXSRP_AEXP_USE_FTP') == '1')) {
            require_once dirname(__FILE__) . '/classes/SalesExportHelper.php';
            require_once dirname(__FILE__) . '/classes/ExportSales.php';
            $orders = new ExportSales($this, $this->_path);
            $orders->run($params['order_history']->id_order, $params['order_history']->id_order_state);
        }
    }
}
