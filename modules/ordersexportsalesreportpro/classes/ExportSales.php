<?php
/**
 *
 * NOTICE OF LICENSE
 *
 *  @author    IntelliPresta <tehran.alishov@gmail.com>
 *  @copyright 2020 IntelliPresta
 *  @license   Commercial License
 */

use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ExportSales
{
    public $module;
    protected $path;
    private $langId;
    private $decimalSeparator;
    private $fracPart;
    private $sql = '';
    private $mutualSql;
    private $selectedColumns;
    private $fileType;
    private $auto;
    private $ordersMerge;
    private $docName;
    private $imageType;
    private $config;
    private $datatables;
    private $orderId = false;
    private $productId = false;
    private $attributeId = false;
    private $noProduct = false;
    private $shopId = false;
    private $categoryId = false;
    private $productRewriteLink = false;
    private $categoryRewriteLink = false;
    private $manufacturerId = false;
    private $supplierId = false;
    private $currencyIsoCode = false;
    private $currencyConversionRate = false;
    private $totalProducts = false;
    private $purchaseSupplierPrice = false;
    private $productQuantity = false;
    private $totalPriceTaxExcl = false;
    private $totalDiscountsTaxExcl = false;
    private $currencySymbol = '';
    private $filteredDate = '';
    private $moneyColumns;
    private $orderMoneyColumns;
    private $curs;
    private $outputDir;
    private $displayHeader;
    private $displayFooter;
    private $displayTotals;
    private $displayMainSales;
    private $displayBestSellers;
    private $displayProductCombs;
    private $displayDailySales;
    private $displayMonthlySales;
    private $displayTopCustomers;
    private $displayPaymentMethods;
    private $displayPaymentMethods2;
    private $displaySalesByCategories;
    private $displaySalesByBrands;
    private $displaySalesBySuppliers;
    private $displaySalesByAttributes;
    private $displaySalesByFeatures;
    private $displaySalesByShops;
    private $displayExplanations;
    private $displayCurrSymbol;
    private $helperSql;
    private $tempDir;
    private $sort;
    private $sortAsc;

    public function __construct($module)
    {
        $this->module = $module;
//        $this->path = $path;
        $this->context = Context::getContext();
        $this->curs = json_decode(Tools::file_get_contents(dirname(__FILE__) . '/../assets/currencies.json'));
        $this->outputDir = dirname(__FILE__) . '/../output';
    }

    private function cleanOutputDir($str)
    {
        //If it's a file.
        if (is_file($str)) {
            //Attempt to delete it.
            return unlink($str);
        } elseif (is_dir($str)) {
            //If it's a directory.
            //Get a list of the files in this directory.
            $scan = glob(rtrim($str, '/') . '/*');
            //Loop through the list of files.
            foreach ($scan as $path) {
                //Call our recursive function.
                $this->cleanOutputDir($path);
            }
            //Remove the directory itself.
            if ($str !== $this->outputDir) {
                return @rmdir($str);
            } else {
                return 1;
            }
        }
    }

    public function getImagetype($type)
    {
        if (method_exists('ImageType', 'getFormattedName')) {
            $iType = ImageType::getFormattedName($type);
        } else {
            $iType = ImageType::getFormatedName($type);
        }
        return $iType;
    }

    public function run($auto = false, $orderState = null)
    {


        if(isset($_GET['auto_export'])){
            if($_GET['auto_export'] == 'true'){
                $predefined_json = '{"orders_selectedColumns":"{\"order\":{\"date_add\":\"Ordered At\",\"id_order\":\"Order ID\",\"total_discounts_tax_incl\":\"Total Discounts (Tax included)\",\"payment\":\"Payment Method\",\"canada_shipping_tax.amount\":\"Shipping Tax (CA 5%)\",\"quebec_shipping_tax.amount\":\"Shipping Tax (CA-QC 9.975%)\",\"rock_total_paid_tax_incl\":\"Total Refunds ROCK (Tax included)\"},\"product\":{\"order_detail_lang.product_name\":\"Product Name\",\"total_price_tax_incl\":\"Total Price (Tax included)\",\"total_price_tax_excl\":\"Total Price (Tax excluded)\",\"canada_tax.total_amount\":\"Total Amount (CA 5%)\",\"quebec_tax.total_amount\":\"Total Amount (CA-QC 9.975%)\"},\"category\":{},\"manufacturer\":{},\"supplier\":{},\"payment\":{},\"customer\":{},\"carrier\":{},\"address\":{\"delivery_country_lang.name\":\"Delivery Country\",\"delivery_state.name\":\"Delivery State\"},\"shop\":{}}","orders_export_as":"excel","orders_doc_name":"Sales","orders_general_add_ts":"1","orders_target_action":"download","target_action_to_emails":"","orders_target_action_ftp_type":"ftp","orders_target_action_ftp_mode":"active","orders_target_action_ftp_url":"khewa.com","orders_target_action_ftp_port":"","orders_target_action_ftp_username":"","orders_target_action_ftp_password":"","orders_target_action_ftp_folder":"","orders_language":"1","orders_csv_delimiter":";","orders_csv_enclosure":"quot","orders_merge_helper":"1","orders_merge":"1","orders_sort":"order.date_add","orders_sort_asc":"0","orders_date_format":"Y-m-d","orders_time_format":"no_time","orders_image_type":"","orders_display_header":"1","orders_display_footer":"1","orders_display_totals":"1","orders_display_currency_symbol":"1","orders_display_explanations":"1","orders_decimal_separator":".","orders_round":"2","orders_creation_date":"today","orders_from_date":"2021-12-13 00:00:00","orders_to_date":"2021-12-20 23:00:00","orders_invoice_date":"no_date","orders_invoice_from_date":"2021-09-01 02:00:00","orders_invoice_to_date":"2021-09-01 23:00:00","orders_delivery_date":"no_date","orders_delivery_from_date":"","orders_delivery_to_date":"","orders_payment_date":"no_date","orders_payment_from_date":"","orders_payment_to_date":"","orders_shipping_date":"no_date","orders_shipping_from_date":"","orders_shipping_to_date":"","ctrl-show-selected-shops":"all","shops_table_length":"10","orders_group_without":"1","ctrl-show-selected-groups":"all","groups_table_length":"10","orders_customer_without":"1","ctrl-show-selected-customers":"all","customers_table_length":"10","ctrl-show-selected-orders":"all","orders_table_length":"10","ctrl-show-selected-orderStates":"all","orderStates_table_length":"100","ctrl-show-selected-paymentMethods":"all","paymentMethods_table_length":"10","orders_cart_rule_without":"1","ctrl-show-selected-cartRules":"all","cartRules_table_length":"10","orders_carrier_without":"1","ctrl-show-selected-carriers":"all","carriers_table_length":"10","ctrl-show-selected-products":"all","products_table_length":"10","orders_category_whether_filter":"0","orders_category_without":"1","products_categories":["2","302","264","275","276","266","267","278","305","295","262","289","290","291","292","17","76","59","64","77","78","80","309","310","311","35","303","304","45","47","48","49","50","46","39","43","42","41","40","44","34","272","271","269","270","55","54","16","125","124","120","121","122","123","18","277","279","296","287","286","69","67","66","65","251","56","273","274","258","259","260","261","306","285","299","288","283","282","281","257","73","280","308","75","74","252","253","82","81","79","83","301"],"orders_attribute_without":"1","ctrl-show-selected-attributes":"all","attributes_table_length":"10","orders_feature_without":"1","ctrl-show-selected-features":"all","features_table_length":"10","orders_manufacturer_without":"1","ctrl-show-selected-manufacturers":"all","manufacturers_table_length":"10","orders_supplier_without":"1","ctrl-show-selected-suppliers":"all","suppliers_table_length":"10","ctrl-show-selected-countries":"all","countries_table_length":"10","ctrl-show-selected-currencies":"all","currencies_table_length":"10","orders_display_main_sales":"1","orders_display_daily_sales":"0","orders_display_monthly_sales":"0","orders_display_bestsellers":"0","orders_display_product_combs":"0","orders_display_top_customers":"0","orders_display_payment_methods":"1","orders_display_taxes":"0","orders_display_category_sales":"0","orders_display_manufacturer_sales":"0","orders_display_supplier_sales":"0","orders_display_attribute_sales":"0","orders_display_feature_sales":"0","orders_display_shop_sales":"0","orders_autoexport":"1","orders_autoexport_order_states":{"0":"0","24":"0","10":"0","13":"0","1":"0","14":"0","40":"0","41":"0","39":"0","6":"0","54":"0","5":"0","38":"0","12":"0","9":"0","25":"0","2":"0","17":"0","8":"0","27":"0","3":"0","7":"0","11":"0","4":"0","56":"0","18":"0","28":"0","37":"0","55":"0","26":"0","22":"0","23":"0","21":"0","34":"0","42":"0"},"orders_autoexport_use_email":"1","autoexportEmails_table_length":"10","orders_autoexport_use_ftp":"1","autoexportFTPs_table_length":"10","orders_autoexport_dont_send_empty":"0","orders_schedule":"1","orders_schedule_use_email":"1","scheduleEmails_table_length":"10","orders_schedule_use_ftp":"1","scheduleFTPs_table_length":"10","orders_schedule_dont_send_empty":"0","shops_type":"unselected","shops_data":"","groups_type":"unselected","groups_data":"","customers_type":"unselected","customers_data":"","orders_type":"unselected","orders_data":"","order_states_type":"unselected","order_states_data":"","cart_rules_type":"unselected","cart_rules_data":"","carriers_type":"unselected","carriers_data":"","manufacturers_type":"unselected","manufacturers_data":"","suppliers_type":"unselected","suppliers_data":"","attributes_type":"unselected","attributes_data":"","features_type":"unselected","features_data":"","payment_methods_type":"unselected","payment_methods_data":"","countries_type":"unselected","countries_data":"","currencies_type":"unselected","currencies_data":"","products_type":"unselected","products_data":"","ajax":1,"ajaxMode":1,"catIds":"","specific_categories":"","tab":"AdminModules"}';

                $predefined_json_arr = json_decode($predefined_json,true);

                foreach($predefined_json_arr as $key => $predefined_json_value){
                    $_POST[$key] = $predefined_json_value;
                }

            }
        }




        $this->cleanOutputDir($this->outputDir);

        $this->auto = $auto;
        $this->imageTypeForFile = $this->getImagetype('small');
        if (version_compare(_PS_VERSION_, '1.7') === -1) {
            $this->catImageTypeForFile = $this->getImagetype('medium');
        } else {
            $this->catImageTypeForFile = $this->getImagetype('small');
        }
        $this->catImageType = $this->getImagetype('category');



        if ($auto) {
            $this->selectedColumns = json_decode($this->config['orders_selectedColumns']);
            $this->fracPart = $this->config['orders_round'];
            $this->dateFormat = $this->config['orders_date_format'];
            $this->timeFormat = $this->config['orders_time_format'];
            $this->decimalSeparator = $this->config['orders_decimal_separator'];
            $this->langId = $this->config['orders_language'];
            $this->sortAsc = (int) $this->config['orders_sort_asc'];
            $this->sort = pSQL($this->config['orders_sort']);
        }

        if (!$auto) {


            $this->selectedColumns = json_decode(Tools::getValue('orders_selectedColumns'));
            $this->fracPart = (int) Tools::getValue('orders_round');
            $this->dateFormat = pSQL(Tools::getValue('orders_date_format'));
            $this->timeFormat = pSQL(Tools::getValue('orders_time_format'));
            $this->decimalSeparator = pSQL(Tools::getValue('orders_decimal_separator'));
            $this->langId = (int) Tools::getValue('orders_language');
            $this->sortAsc = (int) Tools::getValue('orders_sort_asc');
            $this->sort = pSQL(Tools::getValue('orders_sort'));

            $this->fileType = $fileType = Tools::getValue('orders_export_as');
            $this->ordersMerge = Tools::getValue('orders_merge');
            $this->docName = Tools::getValue('orders_doc_name') ?: $this->module->l('Sales', 'ExportSales');
            $this->imageType = Tools::getValue('orders_image_type');
            $this->displayHeader = Tools::getValue('orders_display_header');
            $this->displayFooter = Tools::getValue('orders_display_footer');
            $this->displayTotals = Tools::getValue('orders_display_totals');
            $this->displayMainSales = Tools::getValue('orders_display_main_sales');
            $this->displayBestSellers = Tools::getValue('orders_display_bestsellers');
            $this->displayProductCombs = Tools::getValue('orders_display_product_combs');
            $this->displayDailySales = Tools::getValue('orders_display_daily_sales');
            $this->displayMonthlySales = Tools::getValue('orders_display_monthly_sales');
            $this->displayTopCustomers = Tools::getValue('orders_display_top_customers');
            $this->displayPaymentMethods = Tools::getValue('orders_display_payment_methods');
            $this->displayPaymentMethods2 = Tools::getValue('orders_display_payment_methods2');
            $this->displayTaxes = Tools::getValue('orders_display_taxes');
            $this->displaySalesByCategories = Tools::getValue('orders_display_category_sales');
            $this->displaySalesByBrands = Tools::getValue('orders_display_manufacturer_sales');
            $this->displaySalesBySuppliers = Tools::getValue('orders_display_supplier_sales');
            $this->displaySalesByAttributes = Tools::getValue('orders_display_attribute_sales');
            $this->displaySalesByFeatures = Tools::getValue('orders_display_feature_sales');
            $this->displaySalesByShops = Tools::getValue('orders_display_shop_sales');
            $this->displayExplanations = Tools::getValue('orders_display_explanations');
            if (Tools::getValue('orders_display_currency_symbol')) {
                $this->setCurrencySymbol();
            }



            if ($fileType === 'excel') {
                $this->generateExcel();
            } elseif ($fileType === 'csv') {
                $this->generateCSV();
            } elseif ($fileType === 'html') {
                $this->generateHTML();
            } elseif ($fileType === 'pdf') {
                $this->generatePDF();
            }
        } elseif (is_numeric($auto)) {
            $this->uniqueFolder = $this->outputDir . '/' . uniqid() . '/';
            mkdir($this->uniqueFolder);
            $files = array();
            if (Configuration::get('OXSRP_AEXP_USE_EMAIL') == '1') {
                $sql = '
                    SELECT 
                        IFNULL(id_orders_export_srpro, 1) id_setting,
                        GROUP_CONCAT(email_address) email_addresses,
                        IFNULL(configuration,
                        (
                            SELECT configuration
                            FROM ' . _DB_PREFIX_ . 'orders_export_srpro
                            WHERE id_orders_export_srpro = 1)) configuration, 
                        datatables
                    FROM ' . _DB_PREFIX_ . 'oxsrp_aexp_email oae
                    LEFT JOIN ' . _DB_PREFIX_ . 'orders_export_srpro oes ON 
                        oae.email_setting = oes.`name`
                    WHERE oae.email_active = 1
                    GROUP BY oae.email_setting
                ';
                $result = Db::getInstance()->executeS($sql);
                foreach ($result as $res) {
                    $this->tempDir = $this->uniqueFolder . $res['id_setting'] . '/';
                    mkdir($this->tempDir);
                    parse_str($res['configuration'], $this->config);
                    $this->datatables = json_decode($res['datatables'], true);

                    $this->fileType = $fileType = $this->config['orders_export_as'];
                    $this->ordersMerge = $this->config['orders_merge'];
                    $this->docName = $this->config['orders_doc_name'] ?: $this->module->l('Sales', 'ExportSales');
                    $this->imageType = $this->config['orders_image_type'];
                    $this->displayHeader = $this->config['orders_display_header'];
                    $this->displayExplanations = $this->config['orders_display_explanations'];
                    if ($this->config['orders_display_currency_symbol']) {
                        $this->setCurrencySymbol();
                    }

                    if ($fileType === 'excel') {
                        $fileName = $this->generateExcel();
                        if ($fileName === 0) {
                            continue;
                        }
                        $files = array_merge($files, array($this->tempDir => $fileName));
                    } elseif ($fileType === 'csv') {
                        $fileName = $this->generateCSV();
                        if ($fileName === 0) {
                            continue;
                        }
                        $files = array_merge($files, array($this->tempDir => $fileName));
                    } elseif ($fileType === 'html') {
                        $fileName = $this->generateHTML();
                        if ($fileName === 0) {
                            continue;
                        }
                        $files = array_merge($files, array($this->tempDir => $fileName));
                    } elseif ($fileType === 'pdf') {
                        $fileName = $this->generatePDF();
                        if ($fileName === 0) {
                            continue;
                        }
                        $files = array_merge($files, array($this->tempDir => $fileName));
                    }
                    if ($orderState === 0) {
                        $subject = $this->module->l('New Order : #', 'ExportSales') . $auto . $this->module->l(' (by Advanced Sales Reports module)', 'ExportSales');
                    } elseif ($orderState > 0) {
                        $stateName = DB::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
                            SELECT `name` 
                            FROM ' . _DB_PREFIX_ . 'order_state_lang 
                            WHERE id_order_state = ' . $orderState . ' AND id_lang = ' . $this->langId);
                        $subject = $this->module->l('Order #', 'ExportSales') . $auto . ' - ' . $stateName . $this->module->l(' (by Advanced Sales Reports module)', 'ExportSales');
                    }
//                    $content = $this->module->l('The details are in the attachment.', 'ExportSales');
//                    $this->sendEmail(explode(',', $res['email_addresses']), $subject, $content, $this->tempDir . $fileName);
                    $this->sendPSEmail(explode(',', $res['email_addresses']), $subject, $this->tempDir . $fileName);
                }
            }

            if (Configuration::get('OXSRP_AEXP_USE_FTP') == '1') {
                $sql = '
                    SELECT 
                        IFNULL(id_orders_export_srpro, 1) id_setting, 
                        ftp_type,
                        ftp_mode,
                        ftp_url,
                        ftp_port,
                        ftp_username,
                        ftp_password,
                        ftp_folder,
                        ftp_timestamp,
                        IFNULL(configuration,
                        (
                            SELECT configuration
                            FROM ' . _DB_PREFIX_ . 'orders_export_srpro
                            WHERE id_orders_export_srpro = 1)) configuration, 
                        datatables
                    FROM ' . _DB_PREFIX_ . 'oxsrp_aexp_ftp oaf
                    LEFT JOIN ' . _DB_PREFIX_ . 'orders_export_srpro oes ON oaf.ftp_setting = oes.`name`
                    WHERE oaf.ftp_active = 1
                    ';
                $result = Db::getInstance()->executeS($sql);
                foreach ($result as $res) {
                    $this->tempDir = $this->uniqueFolder . $res['id_setting'] . '/';
                    if ($res['ftp_folder']) {
                        $res['ftp_folder'] .= '/' . $this->auto;
                    } else {
                        $res['ftp_folder'] = 'public_ftp/' . $this->auto;
                    }
                    if ($orderState === 0) {
                        $stateName = $this->module->l('New', 'ExportSales');
                    } elseif ($orderState > 0) {
                        $stateName = DB::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
                            SELECT `name` 
                            FROM ' . _DB_PREFIX_ . 'order_state_lang 
                            WHERE id_order_state = ' . $orderState . ' AND id_lang = ' . $this->langId);
                    }
                    if (isset($files[$this->tempDir])) {
                        $this->uploadToFTP(
                            $this->tempDir . $files[$this->tempDir],
                            $res['ftp_type'],
                            $res['ftp_mode'],
                            $res['ftp_url'],
                            $res['ftp_port'],
                            $res['ftp_username'],
                            $res['ftp_password'],
                            $res['ftp_folder'] . '/' . $this->module->l('Order', 'ExportSales') . '_' . $auto . '_' . $stateName,
                            (int) $res['ftp_timestamp']
                        );
                    } else {
                        mkdir($this->tempDir);

                        parse_str($res['configuration'], $this->config);
                        $this->datatables = json_decode($res['datatables'], true);

                        $this->fileType = $fileType = $this->config['orders_export_as'];
                        $this->ordersMerge = $this->config['orders_merge'];
                        $this->docName = $this->config['orders_doc_name'] ?: $this->module->l('Sales', 'ExportSales');
                        $this->imageType = $this->config['orders_image_type'];
                        $this->displayHeader = $this->config['orders_display_header'];
                        $this->displayExplanations = $this->config['orders_display_explanations'];
                        if ($this->config['orders_display_currency_symbol']) {
                            $this->setCurrencySymbol();
                        }

                        if ($fileType === 'excel') {
                            $fileName = $this->generateExcel();
                            if ($fileName === 0) {
                                continue;
                            }
                            $files = array_merge($files, array($this->tempDir => $fileName));
                        } elseif ($fileType === 'csv') {
                            $fileName = $this->generateCSV();
                            if ($fileName === 0) {
                                continue;
                            }
                            $files = array_merge($files, array($this->tempDir => $fileName));
                        } elseif ($fileType === 'html') {
                            $fileName = $this->generateHTML();
                            if ($fileName === 0) {
                                continue;
                            }
                            $files = array_merge($files, array($this->tempDir => $fileName));
                        } elseif ($fileType === 'pdf') {
                            $fileName = $this->generatePDF();
                            if ($fileName === 0) {
                                continue;
                            }
                            $files = array_merge($files, array($this->tempDir => $fileName));
                        }
                        $this->uploadToFTP(
                            $this->tempDir . $fileName,
                            $res['ftp_type'],
                            $res['ftp_mode'],
                            $res['ftp_url'],
                            $res['ftp_port'],
                            $res['ftp_username'],
                            $res['ftp_password'],
                            $res['ftp_folder'] . '/' . $this->module->l('Order', 'ExportSales') . '_' . $auto . '_' . $stateName,
                            (int) $res['ftp_timestamp']
                        );
                    }
                }
            }
            foreach ($files as $k => $v) {
                unlink($k . $v);
                rmdir($k);
            }
            rmdir($this->uniqueFolder);
        } elseif ($auto === 'schedule') {
            $this->uniqueFolder = $this->outputDir . '/' . uniqid() . '/';
            mkdir($this->uniqueFolder);
            $files = array();
            if (Configuration::get('OXSRP_SCHDL_USE_EMAIL') == '1') {
                $emails_limit = '';
                if (Tools::isSubmit('email_ids')) {
                    $emails_limit = ' AND id_oxsrp_schdl_email IN (' . pSQL(Tools::getValue('email_ids')) . ') ';
                }

                $sql = '
                    SELECT 
                        IFNULL(id_orders_export_srpro, 1) id_setting,
                        GROUP_CONCAT(email_address) email_addresses,
                        IFNULL(configuration,
                        (
                            SELECT configuration
                            FROM ' . _DB_PREFIX_ . 'orders_export_srpro
                            WHERE id_orders_export_srpro = 1)) configuration, 
                        datatables
                    FROM ' . _DB_PREFIX_ . 'oxsrp_schdl_email ose
                    LEFT JOIN ' . _DB_PREFIX_ . 'orders_export_srpro oes ON 
                        ose.email_setting = oes.`name`
                    WHERE ose.email_active = 1 ' . $emails_limit . '
                    GROUP BY ose.email_setting
                ';
                $result = Db::getInstance()->executeS($sql);
                foreach ($result as $res) {
                    $this->tempDir = $this->uniqueFolder . $res['id_setting'] . '/';
                    mkdir($this->tempDir);
                    parse_str($res['configuration'], $this->config);
                    $this->datatables = json_decode($res['datatables'], true);

                    $this->fileType = $fileType = $this->config['orders_export_as'];
                    $this->ordersMerge = $this->config['orders_merge'];
                    $this->docName = $this->config['orders_doc_name'] ?: $this->module->l('Sales', 'ExportSales');
                    $this->imageType = $this->config['orders_image_type'];
                    $this->displayHeader = $this->config['orders_display_header'];
                    $this->displayFooter = $this->config['orders_display_footer'];
                    $this->displayMainSales = $this->config['orders_display_main_sales'];
                    $this->displayTotals = $this->config['orders_display_totals'];
                    $this->displayBestSellers = $this->config['orders_display_bestsellers'];
                    $this->displayProductCombs = $this->config['orders_display_product_combs'];
                    $this->displayDailySales = $this->config['orders_display_daily_sales'];
                    $this->displayMonthlySales = $this->config['orders_display_monthly_sales'];
                    $this->displayTopCustomers = $this->config['orders_display_top_customers'];
                    $this->displayPaymentMethods = $this->config['orders_display_payment_methods'];
                    $this->displayPaymentMethods2 = $this->config['orders_display_payment_methods2'];
                    $this->displayTaxes = $this->config['orders_display_taxes'];
                    $this->displaySalesByCategories = $this->config['orders_display_category_sales'];
                    $this->displaySalesByBrands = $this->config['orders_display_manufacturer_sales'];
                    $this->displaySalesBySuppliers = $this->config['orders_display_supplier_sales'];
                    $this->displaySalesByAttributes = $this->config['orders_display_attribute_sales'];
                    $this->displaySalesByFeatures = $this->config['orders_display_feature_sales'];
                    $this->displaySalesByShops = $this->config['orders_display_shop_sales'];
                    $this->displayExplanations = $this->config['orders_display_explanations'];
                    if ($this->config['orders_display_currency_symbol']) {
                        $this->setCurrencySymbol();
                    }

                    if ($fileType === 'excel') {
                        $fileName = $this->generateExcel();
                        if ($fileName === 0) {
                            continue;
                        }
                        $files = array_merge($files, array($this->tempDir => $fileName));
                    } elseif ($fileType === 'csv') {
                        $fileName = $this->generateCSV();
                        if ($fileName === 0) {
                            continue;
                        }
                        $files = array_merge($files, array($this->tempDir => $fileName));
                    } elseif ($fileType === 'html') {
                        $fileName = $this->generateHTML();
                        if ($fileName === 0) {
                            continue;
                        }
                        $files = array_merge($files, array($this->tempDir => $fileName));
                    } elseif ($fileType === 'pdf') {
                        $fileName = $this->generatePDF();
                        if ($fileName === 0) {
                            continue;
                        }
                        $files = array_merge($files, array($this->tempDir => $fileName));
                    }
                    $subject = $this->module->l('Your Scheduled Sales (by Advanced Sales Reports module)', 'ExportSales');
//                    $content = $this->module->l('The details are in the attachment.', 'ExportSales');
//                    $this->sendEmail(explode(',', $res['email_addresses']), $subject, $content, $this->tempDir . $fileName);
                    $this->sendPSEmail(explode(',', $res['email_addresses']), $subject, $this->tempDir . $fileName);
                }
            }

            if (Configuration::get('OXSRP_SCHDL_USE_FTP') == '1') {
                $ftps_limit = '';
                if (Tools::isSubmit('ftp_ids')) {
                    $ftps_limit = ' AND id_oxsrp_schdl_ftp IN (' . pSQL(Tools::getValue('ftp_ids')) . ') ';
                }

                $sql = '
                    SELECT 
                        IFNULL(id_orders_export_srpro, 1) id_setting, 
                        ftp_type,
                        ftp_mode,
                        ftp_url,
                        ftp_port,
                        ftp_username,
                        ftp_password,
                        ftp_folder,
                        ftp_timestamp,
                        IFNULL(configuration,
                        (
                            SELECT configuration
                            FROM ' . _DB_PREFIX_ . 'orders_export_srpro
                            WHERE id_orders_export_srpro = 1)) configuration, 
                        datatables
                    FROM ' . _DB_PREFIX_ . 'oxsrp_schdl_ftp osf
                    LEFT JOIN ' . _DB_PREFIX_ . 'orders_export_srpro oes ON osf.ftp_setting = oes.`name`
                    WHERE osf.ftp_active = 1 ' . $ftps_limit . '
                    ';
                $result = Db::getInstance()->executeS($sql);
                foreach ($result as $res) {
                    $this->tempDir = $this->uniqueFolder . $res['id_setting'] . '/';
                    if (isset($files[$this->tempDir])) {
                        $this->uploadToFTP(
                            $this->tempDir . $files[$this->tempDir],
                            $res['ftp_type'],
                            $res['ftp_mode'],
                            $res['ftp_url'],
                            $res['ftp_port'],
                            $res['ftp_username'],
                            $res['ftp_password'],
                            $res['ftp_folder'] . '/' . $this->docName,
                            (int) $res['ftp_timestamp']
                        );
                    } else {
                        mkdir($this->tempDir);

                        parse_str($res['configuration'], $this->config);
                        $this->datatables = json_decode($res['datatables'], true);

                        $this->fileType = $fileType = $this->config['orders_export_as'];
                        $this->ordersMerge = $this->config['orders_merge'];
                        $this->docName = $this->config['orders_doc_name'] ?: $this->module->l('Sales', 'ExportSales');
                        $this->imageType = $this->config['orders_image_type'];
                        $this->displayHeader = $this->config['orders_display_header'];
                        $this->displayFooter = $this->config['orders_display_footer'];
                        $this->displayTotals = $this->config['orders_display_totals'];
                        $this->displayBestSellers = $this->config['orders_display_bestsellers'];
                        $this->displayProductCombs = $this->config['orders_display_product_combs'];
                        $this->displayDailySales = $this->config['orders_display_daily_sales'];
                        $this->displayMonthlySales = $this->config['orders_display_monthly_sales'];
                        $this->displayTopCustomers = $this->config['orders_display_top_customers'];
                        $this->displayPaymentMethods = $this->config['orders_display_payment_methods'];
                        $this->displayPaymentMethods2 = $this->config['orders_display_payment_methods2'];
                        $this->displayTaxes = $this->config['orders_display_taxes'];
                        $this->displaySalesByCategories = $this->config['orders_display_category_sales'];
                        $this->displaySalesByBrands = $this->config['orders_display_manufacturer_sales'];
                        $this->displaySalesBySuppliers = $this->config['orders_display_supplier_sales'];
                        $this->displaySalesByAttributes = $this->config['orders_display_attribute_sales'];
                        $this->displaySalesByFeatures = $this->config['orders_display_feature_sales'];
                        $this->displaySalesByShops = $this->config['orders_display_shop_sales'];
                        $this->displayExplanations = $this->config['orders_display_explanations'];
                        if ($this->config['orders_display_currency_symbol']) {
                            $this->setCurrencySymbol();
                        }

                        if ($fileType === 'excel') {
                            $fileName = $this->generateExcel();
                            if ($fileName === 0) {
                                continue;
                            }
                            $files = array_merge($files, array($this->tempDir => $fileName));
                        } elseif ($fileType === 'csv') {
                            $fileName = $this->generateCSV();
                            if ($fileName === 0) {
                                continue;
                            }
                            $files = array_merge($files, array($this->tempDir => $fileName));
                        } elseif ($fileType === 'html') {
                            $fileName = $this->generateHTML();
                            if ($fileName === 0) {
                                continue;
                            }
                            $files = array_merge($files, array($this->tempDir => $fileName));
                        } elseif ($fileType === 'pdf') {
                            $fileName = $this->generatePDF();
                            if ($fileName === 0) {
                                continue;
                            }
                            $files = array_merge($files, array($this->tempDir => $fileName));
                        }
                        $this->uploadToFTP(
                            $this->tempDir . $fileName,
                            $res['ftp_type'],
                            $res['ftp_mode'],
                            $res['ftp_url'],
                            $res['ftp_port'],
                            $res['ftp_username'],
                            $res['ftp_password'],
                            $res['ftp_folder'] . '/' . $this->docName,
                            (int) $res['ftp_timestamp']
                        );
                    }
                }
            }
            foreach ($files as $k => $v) {
                unlink($k . $v);
                rmdir($k);
            }
            rmdir($this->uniqueFolder);
        }
    }

    private function setCurrencySymbol()
    {
        $currency_def_iso = DB::getInstance()->getValue('SELECT iso_code FROM `'
            . _DB_PREFIX_ . 'currency` WHERE id_currency = ' . (int) Configuration::get('PS_CURRENCY_DEFAULT'));
        $curs = json_decode(Tools::file_get_contents(dirname(__FILE__) . '/../assets/currencies.json'));
        if (isset($curs->{$currency_def_iso})) {
            $this->currencySymbol = $curs->{$currency_def_iso} . ' ';
        } else {
            $this->currencySymbol = $currency_def_iso . ' ';
        }
    }

    public function sendPSEmail($toEmails, $subject = null, $attachment_path = null)
    {
        $mail_iso = $this->context->language->iso_code;
        if (!file_exists(dirname(__FILE__) . '/../mails/' . $mail_iso . '/order.txt') ||
            !file_exists(dirname(__FILE__) . '/../mails/' . $mail_iso . '/order.html')) {
            $this->copyDir(dirname(__FILE__) . '/../mails/en', dirname(__FILE__) . '/../mails/' . $mail_iso);
        }
        $dir_mail = dirname(__FILE__) . '/../mails/';

        $configuration = Configuration::getMultiple(array(
            'PS_SHOP_EMAIL',
            'PS_SHOP_NAME',
        ));

        $ext = pathinfo($attachment_path, PATHINFO_EXTENSION);
        $file_attachement = array();
        $file_attachement['content'] = Tools::file_get_contents($attachment_path);
        $file_attachement['name'] = basename($attachment_path);

        if ($ext === 'pdf') {
            $file_attachement['mime'] = 'application/pdf';
        } elseif ($ext === 'xlsx') {
            $file_attachement['mime'] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        } elseif ($ext === 'csv') {
            $file_attachement['mime'] = 'text/csv';
        } elseif ($ext === 'html') {
            $file_attachement['mime'] = 'text/html';
        }

        if (is_numeric($this->auto)) {
            $file_attachement['name'] = $this->auto . '.' . $ext;
        } else {
            $file_attachement['name'] = basename($attachment_path);
        }

        $template_vars = array('{shop_name}' => $configuration['PS_SHOP_NAME']);

        if (Mail::Send(
            $this->context->language->id,
            'order',
            $subject,
            $template_vars,
            $toEmails,
            null,
            $configuration['PS_SHOP_EMAIL'],
            $configuration['PS_SHOP_NAME'],
            $file_attachement,
            null,
            $dir_mail,
            null,
            $this->context->shop->id
        )) {
            if ($this->auto === 'schedule') {
                echo $this->module->l('Email successfully sent.') . ' <br> ';
            }
        } else {
            if ($this->auto === 'schedule') {
                echo $this->module->l('Email could not sent.') . ' <br> ';
            }
        }
    }

    public function sendEmail($toEmails, $subject = null, $content = null, $attachment = null)
    {
        if (version_compare(_PS_VERSION_, '1.7') === -1) {
            require_once _PS_TOOL_DIR_ . 'swift/swift_required.php';
        }

        /* Connect with the appropriate configuration */
        if (Configuration::get('PS_MAIL_METHOD') == 2) {
            if (empty(Configuration::get('PS_MAIL_SERVER')) || empty(Configuration::get('PS_MAIL_SMTP_PORT'))) {
                Tools::dieOrLog(Tools::displayError('Error: invalid SMTP server or SMTP port'), false);
                return false;
            }

            if (version_compare(_PS_VERSION_, '1.7.7') === -1) {
                $connection = Swift_SmtpTransport::newInstance(Configuration::get('PS_MAIL_SERVER'), Configuration::get('PS_MAIL_SMTP_PORT'), Configuration::get('PS_MAIL_SMTP_ENCRYPTION'))
                    ->setUsername(Configuration::get('PS_MAIL_USER'))
                    ->setPassword(Configuration::get('PS_MAIL_PASSWD'));
            } else {
                $connection = (new Swift_SmtpTransport(Configuration::get('PS_MAIL_SERVER'), Configuration::get('PS_MAIL_SMTP_PORT'), Configuration::get('PS_MAIL_SMTP_ENCRYPTION')))
                    ->setUsername(Configuration::get('PS_MAIL_USER'))
                    ->setPassword(Configuration::get('PS_MAIL_PASSWD'));
            }
        } else {
            if (version_compare(_PS_VERSION_, '1.7.7') === -1) {
                $connection = Swift_MailTransport::newInstance();
            } else {
                $connection = new Swift_SendmailTransport();
            }
        }

        // Create the message
        if (version_compare(_PS_VERSION_, '1.7.7') === -1) {
            $message = Swift_Message::newInstance();
        } else {
            $message = new Swift_Message();
        }
        $message->setTo($toEmails);
        $message->setSubject($subject);
        $message->setBody($content);
        $message->setFrom(
            Configuration::get('PS_SHOP_EMAIL'),
            Configuration::get('PS_SHOP_NAME')
        );
        if (version_compare(_PS_VERSION_, '1.7.7') === -1) {
            $attach = Swift_Attachment::fromPath($attachment);
        } else {
            $attach = (new Swift_Attachment())->fromPath($attachment);
        }
        if (is_numeric($this->auto)) {
            $attach->setFilename($this->module->l('Order', 'ExportSales') . '_' . $this->auto . '.' . pathinfo($attachment, PATHINFO_EXTENSION));
        }
        $message->attach($attach);

        // Send email
        if (version_compare(_PS_VERSION_, '1.7.7') === -1) {
            $mailer = Swift_Mailer::newInstance($connection);
        } else {
            $mailer = new Swift_Mailer($connection);
        }
        $mailer->send($message);
        if ($this->auto === 'schedule') {
            echo 'File was successfully sent to email(s). <br>';
        }
    }

    public function uploadToFTP($file, $ftp_type, $ftp_mode, $ftp_url, $ftp_port, $ftp_username, $ftp_password, $ftp_folder, $ftp_file_add_ts)
    {
        if (!$ftp_folder) {
            $ftp_folder = 'public_ftp/' . $this->docName;
        }
        $ext = '.' . pathinfo($file, PATHINFO_EXTENSION);
        $file_path = $ftp_file_add_ts ? $ftp_folder . '_' . date('Y-m-d His') . $ext : $ftp_folder . $ext;


        if ($ftp_type === 'sftp') {
            set_include_path(dirname(__FILE__) . '/../vendor/phpseclib');
            include('Net/SFTP.php');
            if (!$ftp_port) {
                $ftp_port = 22;
            }
            $sftp = new Net_SFTP($ftp_url, $ftp_port);
            if ($sftp->login($ftp_username, $ftp_password)) {
                if ($sftp->put($file_path, $file, NET_SFTP_LOCAL_FILE)) {
                    if ($this->auto === 'schedule') {
                        echo $this->module->l('File was successfully transferred using SFTP.') . ' <br> ';
                    }
                    return true;
                } else {
                    if ($this->auto === 'schedule') {
                        echo $this->module->l('File was unable to be transferred using SFTP.') . ' <br> ';
                    }
                    return false;
                }
            } else {
                if ($this->auto === 'schedule') {
                    echo $this->module->l('Cannot log in using SFTP.') . ' <br> ';
                }
                return false;
            }
        } else {
            if (!$ftp_port) {
                $ftp_port = 21;
            }
            // open an FTP/FTPS connection
            if ($ftp_type === 'ftps') {
                $connId = ftp_ssl_connect($ftp_url, (int) $ftp_port);
            } else {
                $connId = ftp_connect($ftp_url, (int) $ftp_port);
            }
            if ($connId) {
                // login to FTP server
                ftp_login($connId, $ftp_username, $ftp_password);
                if ($ftp_mode === 'passive') {
                    ftp_pasv($connId, true);
                }

                if (ftp_put($connId, $file_path, $file, FTP_BINARY)) {
                    if ($this->auto === 'schedule') {
                        echo $this->module->l('File was successfully transferred using FTP.') . ' <br> ';
                    }
                    ftp_close($connId);
                    return true;
                } else {
                    if ($this->auto === 'schedule') {
                        echo $this->module->l('File was unable to be transferred using FTP.') . ' <br> ';
                    }
                    ftp_close($connId);
                    return false;
                }
            } else {
                if ($this->auto === 'schedule') {
                    echo $this->module->l('Could not connect to FTP.') . ' <br> ';
                }
                return false;
            }
        }
    }

    private function copyDir($src, $dst)
    {
        // open the source directory
        $dir = opendir($src);

        // Make the destination directory if not exist
        @mkdir($dst);

        // Loop through the files in source directory
        while ($file = readdir($dir)) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if (is_dir($src . '/' . $file)) {
                    // Recursively calling custom copy function
                    // for sub directory
                    $this->copyDir($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }

        closedir($dir);
    }

    private function setOrderID()
    {
        $this->selectedColumns->order->id_order = 'id_order';
        $this->sql .= ' `order`.id_order `id_order`,';
        $this->orderId = true;
    }

    private function setProductID()
    {
        $this->selectedColumns->product->product_id = 'product_id';
        $this->sql .= ' product.product_id `product_id`,';
        $this->productId = true;
    }

    private function setTotalProducts()
    {
        $this->selectedColumns->order->total_products = 'total_products';
        $this->sql .= ' order.total_products `total_products`,';
        $this->totalProducts = true;
    }

    private function setTotalDiscountsTaxExcl()
    {
        $this->selectedColumns->order->total_discounts_tax_excl = 'total_discounts_tax_excl';
        $this->sql .= ' order.total_discounts_tax_excl `total_discounts_tax_excl`,';
        $this->totalDiscountsTaxExcl = true;
    }

    private function setPurchaseSupplierPrice()
    {
        $this->selectedColumns->product->purchase_supplier_price = 'purchase_supplier_price';
        $this->sql .= ' product.purchase_supplier_price `purchase_supplier_price`,';
        $this->purchaseSupplierPrice = true;
    }

    private function setProductQuantity()
    {
        $this->selectedColumns->product->product_quantity = 'product_quantity';
        $this->sql .= ' product.product_quantity `product_quantity`,';
        $this->productQuantity = true;
    }

    private function setProductLinkRewrite()
    {
        $this->selectedColumns->product->{'order_detail_lang.product_link_rewrite'} = 'product_link_rewrite';
        $this->sql .= ' order_detail_lang.product_link_rewrite `product_link_rewrite`,';
        $this->productRewriteLink = true;
    }

    private function setAttributeID()
    {
        $this->selectedColumns->product->product_attribute_id = 'product_attribute_id';
        $this->sql .= ' product.product_attribute_id `product_attribute_id`,';
        $this->attributeId = true;
    }

    private function setShopID()
    {
        $this->selectedColumns->shop->id_shop = 'id_shop';
        $this->sql .= ' shop.id_shop `id_shop`,';
        $this->shopId = true;
    }

    private function setCategoryID()
    {
        $this->selectedColumns->category->id_category = 'id_category';
        $this->sql .= ' category.id_category `id_category`,';
        $this->categoryId = true;
    }

    private function setCategoryLinkRewrite()
    {
        $this->selectedColumns->category->link_rewrite = 'category_link_rewrite';
        $this->sql .= ' category.link_rewrite `category_link_rewrite`,';
        $this->categoryRewriteLink = true;
    }

    private function setManufacturerID()
    {
        $this->selectedColumns->manufacturer->id_manufacturer = 'id_manufacturer';
        $this->sql .= ' manufacturer.id_manufacturer `id_manufacturer`,';
        $this->manufacturerId = true;
    }

    private function setSupplierID()
    {
        $this->selectedColumns->supplier->id_supplier = 'id_supplier';
        $this->sql .= ' supplier.id_supplier `id_supplier`,';
        $this->supplierId = true;
    }

    private function setIsoCode()
    {
        $this->selectedColumns->order->{'currency.iso_code'} = 'currency_iso_code';
        $this->sql .= ' currency.iso_code `currency_iso_code`,';
        $this->currencyIsoCode = true;
    }

    private function setConversionRate()
    {
        $this->selectedColumns->order->{'currency.conversion_rate'} = 'currency_conversion_rate';
        $this->sql .= ' currency.conversion_rate `currency_conversion_rate`,';
        $this->currencyConversionRate = true;
    }

    private function setTotalPriceTaxExcl()
    {
        $this->selectedColumns->product->total_price_tax_excl = 'total_price_tax_excl';
        $this->sql .= ' product.total_price_tax_excl `total_price_tax_excl`,';
        $this->totalPriceTaxExcl = true;
    }

    private function getBreakPoints($groups)
    {
        $sum = 0;
        $arr = [0];
        for ($i = 0; $i < count($groups) - 1; $i++) {
            $arr[] = $sum += (int) $groups[$i]['products'];
        }
        return $arr;
    }

    private function getBestSellers()
    {
        $sql = "SELECT 
                    product.product_id,
                    prod.reference prod_reference,
                    product_lang.name product_name,
                    SUM(product.product_quantity) product_quantity,
                    CONCAT('$this->currencySymbol', REPLACE(CAST(TRIM(ROUND(SUM(product.total_price_tax_excl - (product.product_quantity * product.purchase_supplier_price)), $this->fracPart)) + 0 AS CHAR), '.', '$this->decimalSeparator')) total_profit,
                    CONCAT('$this->currencySymbol', REPLACE(CAST(TRIM(ROUND(SUM(product.total_price_tax_excl), $this->fracPart)) + 0 AS CHAR), '.', '$this->decimalSeparator')) total_price_tax_excl,
                    CONCAT('$this->currencySymbol', REPLACE(CAST(TRIM(ROUND(SUM(product.total_price_tax_incl), $this->fracPart)) + 0 AS CHAR), '.', '$this->decimalSeparator')) total_price_tax_incl,
                    CONCAT('$this->currencySymbol', REPLACE(CAST(TRIM(ROUND(SUM(total_paid_tax_excl), $this->fracPart)) + 0 AS CHAR), '.', '$this->decimalSeparator')) total_paid_tax_excl,
                    CONCAT('$this->currencySymbol', REPLACE(CAST(TRIM(ROUND(SUM(total_paid_tax_incl), $this->fracPart)) + 0 AS CHAR), '.', '$this->decimalSeparator')) total_paid_tax_incl,
                    CONCAT('$this->currencySymbol', REPLACE(CAST(TRIM(ROUND(SUM(total_paid_real), $this->fracPart)) + 0 AS CHAR), '.', '$this->decimalSeparator')) total_paid_real
            " . $this->helperSql . '
                LEFT JOIN ' . _DB_PREFIX_ . 'product_lang product_lang ON
                    `order`.id_shop = product_lang.id_shop AND 
                    product.product_id = product_lang.id_product AND 
                    product_lang.id_lang = ' . $this->langId . '
                    WHERE 1 ' . $this->mutualSql . ' 
                    GROUP BY product.product_id
                    ORDER BY SUM(product.total_price_tax_excl) DESC;';
        return DB::getInstance()->executeS($sql);
    }

    private function getProductCombs()
    {
        $sql = "SELECT 
                    product.product_id,
                    SUBSTRING_INDEX(product.product_name, ' - ', 1) product_name,
                    product.product_attribute_id,
                    SUBSTRING_INDEX(product.product_name, ' - ', -1) combination,
                    SUM(product.product_quantity) product_quantity,
                    CONCAT('$this->currencySymbol', REPLACE(CAST(TRIM(ROUND(SUM(product.total_price_tax_excl - (product.product_quantity * product.purchase_supplier_price)), $this->fracPart)) + 0 AS CHAR), '.', '$this->decimalSeparator')) total_profit,
                    CONCAT('$this->currencySymbol', REPLACE(CAST(TRIM(ROUND(SUM(product.total_price_tax_excl), $this->fracPart)) + 0 AS CHAR), '.', '$this->decimalSeparator')) total_price_tax_excl
            " . $this->helperSql . '
                    WHERE 1 ' . $this->mutualSql . ' 
                    GROUP BY product.product_id, product.product_attribute_id
                    ORDER BY SUM(product.total_price_tax_excl) DESC;';
        return DB::getInstance()->executeS($sql);
    }

    private function getDailySales()
    {
        $sql = "SELECT 
                    DATE_FORMAT(`order`.date_add, '" . SalesExportHelper::$formatArray[$this->dateFormat] . "') order_date,
                    SUM(product.product_quantity) product_quantity,
                    CONCAT('$this->currencySymbol', REPLACE(CAST(TRIM(ROUND(SUM(product.total_price_tax_excl - (product.product_quantity * product.purchase_supplier_price)), $this->fracPart)) + 0 AS CHAR), '.', '$this->decimalSeparator')) total_profit,
                    CONCAT('$this->currencySymbol', REPLACE(CAST(TRIM(ROUND(SUM(product.total_price_tax_excl), $this->fracPart)) + 0 AS CHAR), '.', '$this->decimalSeparator')) total_price_tax_excl
            " . $this->helperSql . '
                    WHERE 1 ' . $this->mutualSql . ' 
                    GROUP BY DATE_FORMAT(`order`.date_add, "%Y-%m-%d")
                    ORDER BY DATE_FORMAT(`order`.date_add, "%Y-%m-%d");';
        return DB::getInstance()->executeS($sql);
    }

    private function getMonthlySales()
    {
        $sql = "SELECT 
                    DATE_FORMAT(`order`.date_add, '%Y-%m') order_date,
                    SUM(product.product_quantity) product_quantity,
                    CONCAT('$this->currencySymbol', REPLACE(CAST(TRIM(ROUND(SUM(product.total_price_tax_excl - (product.product_quantity * product.purchase_supplier_price)), $this->fracPart)) + 0 AS CHAR), '.', '$this->decimalSeparator')) total_profit,
                    CONCAT('$this->currencySymbol', REPLACE(CAST(TRIM(ROUND(SUM(product.total_price_tax_excl), $this->fracPart)) + 0 AS CHAR), '.', '$this->decimalSeparator')) total_price_tax_excl
            " . $this->helperSql . '
                    WHERE 1 ' . $this->mutualSql . ' 
                    GROUP BY DATE_FORMAT(`order`.date_add, "%Y-%m")
                    ORDER BY DATE_FORMAT(`order`.date_add, "%Y-%m");';
        return DB::getInstance()->executeS($sql);
    }

    private function getTopCustomers()
    {
        $sql = "SELECT 
                    id_customer,
                    email,
                    firstname,
                    lastname,
                    COUNT(id_order) order_count, 
                    CONCAT('$this->currencySymbol', REPLACE(CAST(TRIM(ROUND(SUM(total_products), $this->fracPart)) + 0 AS CHAR), '.', '$this->decimalSeparator')) total_products,
                    CONCAT('$this->currencySymbol', REPLACE(CAST(TRIM(ROUND(SUM(total_products_discounted), $this->fracPart)) + 0 AS CHAR), '.', '$this->decimalSeparator')) total_products_discounted
            FROM (
            SELECT DISTINCT
                    order.id_order,
                    customer.id_customer,
                    customer.email,
                    customer.firstname,
                    customer.lastname,
                    order.total_products,
                    order.total_products - order.total_discounts_tax_excl total_products_discounted
            " . $this->helperSql . '
                    WHERE 1 ' . $this->mutualSql . ') tmp
                    GROUP BY id_customer
                    ORDER BY SUM(total_products) DESC;';
        return DB::getInstance()->executeS($sql);
    }

    private function getPaymentSales()
    {
        $refund_state = Configuration::getGlobalValue('PS_OS_REFUND');
        $canceled_state = Configuration::getGlobalValue('PS_OS_CANCELED');
        $error_state = Configuration::getGlobalValue('PS_OS_ERROR');

        $sql = "SELECT 
                payment,
                module,
                SUM(valid) valid,
                CONCAT('$this->currencySymbol', REPLACE(CAST(TRIM(ROUND(SUM(total_products), $this->fracPart)) + 0 AS CHAR), '.', '$this->decimalSeparator')) total_products,
                CONCAT('$this->currencySymbol', REPLACE(CAST(TRIM(ROUND(SUM(total_products_wt), $this->fracPart)) + 0 AS CHAR), '.', '$this->decimalSeparator')) total_products_wt,
                CONCAT('$this->currencySymbol', REPLACE(CAST(TRIM(ROUND(SUM(total_discounts_wt), $this->fracPart)) + 0 AS CHAR), '.', '$this->decimalSeparator')) total_discounts_wt,
                CONCAT('$this->currencySymbol', REPLACE(CAST(TRIM(ROUND(SUM(total_paid_tax_incl), $this->fracPart)) + 0 AS CHAR), '.', '$this->decimalSeparator')) total_paid_tax_incl,
                CONCAT('$this->currencySymbol', REPLACE(CAST(TRIM(ROUND(SUM(amount_incl), $this->fracPart)) + 0 AS CHAR), '.', '$this->decimalSeparator')) order_slip_amount_tax_incl,
                CONCAT('$this->currencySymbol', REPLACE(CAST(TRIM(ROUND(SUM(canada_tax_total_amount), $this->fracPart)) + 0 AS CHAR), '.', '$this->decimalSeparator')) canada_tax_total_amount,
                CONCAT('$this->currencySymbol', REPLACE(CAST(TRIM(ROUND(SUM(quebec_tax_total_amount), $this->fracPart)) + 0 AS CHAR), '.', '$this->decimalSeparator')) quebec_tax_total_amount,
                CONCAT('$this->currencySymbol', REPLACE(CAST(TRIM(ROUND(SUM(rock_refund_tax_incl), $this->fracPart)) + 0 AS CHAR), '.', '$this->decimalSeparator')) rock_refund_tax_incl
            FROM(
            SELECT
                    order.id_order,
                    payment,
                    module,
                    IF(`order`.current_state = $refund_state OR `order`.current_state = $canceled_state OR `order`.current_state = $error_state, 0, 1) valid,
                    IF(`order`.current_state = $refund_state OR `order`.current_state = $canceled_state OR `order`.current_state = $error_state, 0, order.total_products) total_products,
                    IF(`order`.current_state = $refund_state OR `order`.current_state = $canceled_state OR `order`.current_state = $error_state, 0, order.total_products_wt) total_products_wt,
                    IF(`order`.current_state = $refund_state OR `order`.current_state = $canceled_state OR `order`.current_state = $error_state, 0, order.total_discounts_tax_excl) total_discounts,
                    IF(`order`.current_state = $refund_state OR `order`.current_state = $canceled_state OR `order`.current_state = $error_state, 0, order.total_discounts_tax_incl) total_discounts_wt,
                    IF(`order`.current_state = $refund_state OR `order`.current_state = $canceled_state OR `order`.current_state = $error_state, 0, order.total_paid_tax_excl) total_paid_tax_excl,
                    IF(`order`.current_state = $refund_state OR `order`.current_state = $canceled_state OR `order`.current_state = $error_state, 0, order.total_paid_tax_incl) total_paid_tax_incl,
                    SUM(IF(`order`.current_state = $refund_state OR `order`.current_state = $canceled_state OR `order`.current_state = $error_state, 0, canada_tax.total_amount)) canada_tax_total_amount,
                    SUM(IF(`order`.current_state = $refund_state OR `order`.current_state = $canceled_state OR `order`.current_state = $error_state, 0, quebec_tax.total_amount)) quebec_tax_total_amount,
                    IFNULL(order_slip.total_products_tax_excl, 0) amount_excl,
                    IFNULL(order_slip.total_products_tax_incl, 0) amount_incl,
                    IF(`order`.current_state = $refund_state, total_paid_tax_excl, 0) rock_refund_tax_excl,
                    IF(`order`.current_state = $refund_state, total_paid_tax_incl, 0) rock_refund_tax_incl
                    " . $this->helperSql . '
                    WHERE 1 ' . $this->mutualSql . '
                    GROUP BY order.id_order
                    ) tmp
                    GROUP BY module, IF (payment = "Carte de crédit" OR payment = "Credit Card(instore)", "Credit Card", payment)
                    ORDER BY SUM(total_products) DESC;';
        return DB::getInstance()->executeS($sql);
    }

    private function getTaxes()
    {
        $sql = "SELECT 
                    tax_lang.`name`,
                    CONCAT('$this->currencySymbol', REPLACE(CAST(TRIM(ROUND(SUM(odt.total_amount), $this->fracPart)) + 0 AS CHAR), '.', '$this->decimalSeparator')) total_price_tax_excl
            " . $this->helperSql . '
                    LEFT JOIN ' . _DB_PREFIX_ . 'order_detail_tax odt ON `product`.id_order_detail = odt.id_order_detail
                    LEFT JOIN ' . _DB_PREFIX_ . 'tax_lang tax_lang ON tax_lang.id_tax = odt.id_tax AND tax_lang.id_lang = ' . $this->langId . '
                    WHERE odt.id_tax IS NOT NULL AND odt.id_tax <> 0 ' . $this->mutualSql . ' GROUP BY odt.id_tax
                    ORDER BY odt.id_tax;';
        return DB::getInstance()->executeS($sql);
    }

    private function getPaymentSales2()
    {
        $refund_state = Configuration::getGlobalValue('PS_OS_REFUND');

        // $order_values = "
        //                 SELECT DISTINCT payment_method
        //                 FROM ps_order_payment
        //                 WHERE payment_method LIKE '%Paypal%' OR payment_method LIKE '%Stripe%'
        //                 UNION
        //                 SELECT ''
        //                 UNION
        //                 SELECT DISTINCT payment_method
        //                 FROM ps_order_payment
        //                 WHERE payment_method NOT LIKE '%Paypal%' AND payment_method NOT LIKE '%Stripe%'";


        //---------------refund online------------------
        $sql = "SELECT 
                order_payment.payment_method,
                order_payment.order_reference,
                module,
                CONCAT('$this->currencySymbol', REPLACE(CAST(TRIM(ROUND(SUM(order_payment.amount), $this->fracPart)) + 0 AS CHAR), '.', '$this->decimalSeparator')) payment_amount
                #COUNT(id_order) order_count
            FROM(
            SELECT DISTINCT
                    order.id_order,
                    order.reference,
                    `order`.module
                    " . $this->helperSql . '
                    WHERE 1 ' . $this->mutualSql . ') tmp
                    LEFT JOIN ' . _DB_PREFIX_. 'order_payment order_payment ON tmp.`reference` = order_payment.order_reference
                    WHERE order_payment.amount < 0 
                    GROUP BY module, payment_method
                    ORDER BY FIELD(payment_method, "Stripe Payment Pro","PayPal","Payment by Stripe","Card via Stripe","Gift card","Carte Cadeau","Credit Slip","Voucher");';



        $refunds_online =  Db::getInstance()->executeS($sql);

        $refund_sum = 0;

        $this->nth_total = array();


        $refund_refs = array();
        foreach ($refunds_online as $res) {
            if($res['payment_method'] == 'Online Gift Cart' || $res['payment_method'] == 'Online Voucher' || $res['payment_method'] == 'Online Credit Slip'){
                continue;
            }
            $refund_refs[] = $res['order_reference'];
        }



        //---------------refund online------------------

        $sql = "SELECT 
                order_payment.payment_method,
                order_payment.order_reference,
                module,
                CONCAT('$this->currencySymbol', REPLACE(CAST(TRIM(ROUND(SUM(order_payment.amount), $this->fracPart)) + 0 AS CHAR), '.', '$this->decimalSeparator')) payment_amount
                #COUNT(id_order) order_count
            FROM(
            SELECT DISTINCT
                    order.id_order,
                    order.reference,
                    `order`.module
                    " . $this->helperSql . '
                    WHERE 1 AND order.current_state !=7 ' . $this->mutualSql . ') tmp
                    LEFT JOIN ' . _DB_PREFIX_. 'order_payment order_payment ON tmp.`reference` = order_payment.order_reference
                    WHERE order_payment.payment_method LIKE "%Paypal%" OR order_payment.payment_method LIKE "%Stripe%"
                    GROUP BY module, payment_method
                    ORDER BY FIELD(payment_method, "Stripe Payment Pro","Paypal","Payment by Stripe","Card via Stripe","Gift card","Carte Cadeau","Credit Slip","Voucher");';



        $res1 =  Db::getInstance()->executeS($sql);



        $order_date_range = str_replace('order.','ord.',$this->mutualSql);

        $gift_res = array();
        $sql_custom = 'SELECT ord.module,SUM(ocr.value) payment_amount FROM ' . _DB_PREFIX_. 'orders  as ord, ' . _DB_PREFIX_. 'order_cart_rule as ocr WHERE ord.id_order = ocr.id_order AND ocr.name LIKE "%gift%"  AND ord.module != "hspointofsalepro" ' .$order_date_range;

        $gift_res = Db::getInstance()->executeS($sql_custom);

        $gift_res[0]['payment_amount']= '$ '. number_format($gift_res[0]['payment_amount'],2);



        $voucher_res = array();
        $sql_custom = 'SELECT ord.module, SUM(ocr.value) payment_amount FROM ' . _DB_PREFIX_. 'orders  as ord, ' . _DB_PREFIX_. 'order_cart_rule as ocr WHERE ord.id_order = ocr.id_order AND ocr.name LIKE "%voucher%"  AND ord.module != "hspointofsalepro" ' .$order_date_range;

        $voucher_res = Db::getInstance()->executeS($sql_custom);

        $voucher_res[0]['payment_amount']= '$ '. number_format($voucher_res[0]['payment_amount'],2);

        $credit_res = array();
        $sql_custom = 'SELECT ord.module,SUM(ocr.value) payment_amount FROM ' . _DB_PREFIX_. 'orders  as ord, ' . _DB_PREFIX_. 'order_cart_rule as ocr, ' . _DB_PREFIX_. 'cart_rule as cr WHERE ord.id_order = ocr.id_order AND cr.id_cart_rule = ocr.id_cart_rule AND cr.description LIKE "%slip%"  AND ord.module != "hspointofsalepro" ' .$order_date_range;

        $credit_res = Db::getInstance()->executeS($sql_custom);
        $credit_res[0]['payment_amount']= '$ '. number_format($credit_res[0]['payment_amount'],2);

        $total_discount_online = str_replace('$ ','',$credit_res[0]['payment_amount'] )+ str_replace('$ ','',$voucher_res[0]['payment_amount']) +str_replace('$ ','',$gift_res[0]['payment_amount']);

        $new_element = array('payment_method' => "Online Gift Cart");
        $gift_res[0] = $new_element+$gift_res[0];

        $new_element = array('payment_method' => "Online Voucher");
        $voucher_res[0] = $new_element+$voucher_res[0];

        $new_element = array('payment_method' => "Online Credit Slip");
        $credit_res[0] = $new_element+$credit_res[0];




        $sum1 = $sum2 = 0;

        $this->nth_total = array();


        $new_res1 = array();
        $res_count = 0;

        foreach ($res1 as $res) {
            if($res['payment_method'] == 'Online Gift Cart' || $res['payment_method'] == 'Online Voucher' || $res['payment_method'] == 'Online Credit Slip'){
                continue;
            }
            if(!in_array($res['order_reference'],$refund_refs)){
                $sum1 += trim($res['payment_amount'], $this->currencySymbol);
                $new_res1[$res_count]['payment_method']=$res['payment_method'];
                $new_res1[$res_count]['module']=$res['module'];
                $new_res1[$res_count]['payment_amount']=$res['payment_amount'];
                $res_count++;
            }

        }

        $new_res2 = array();

        $res_count = 0;
        foreach ($refunds_online as $res) {
            if($res['payment_method'] == 'Online Gift Cart' || $res['payment_method'] == 'Online Voucher' || $res['payment_method'] == 'Online Credit Slip'){
                continue;
            }
            if(in_array($res['order_reference'],$refund_refs)){
                $new_res2[$res_count]['payment_method']=$res['payment_method'];
                $new_res2[$res_count]['module']=$res['module'];
                $new_res2[$res_count]['payment_amount']=$res['payment_amount'];
                $res_count++;
            }

        }

        $res1 = array_merge($new_res1,$new_res2) ;




        $res1[] = $gift_res[0];
        $res1[] = $voucher_res[0];
        $res1[] = $credit_res[0];



        $sum1 = $this->currencySymbol . $sum1;

        $sql = "SELECT 
                order_payment.payment_method,
                module,
                CONCAT('$this->currencySymbol', REPLACE(CAST(TRIM(ROUND(SUM(order_payment.amount), $this->fracPart)) + 0 AS CHAR), '.', '$this->decimalSeparator')) payment_amount
                #COUNT(id_order) order_count
            FROM(
            SELECT DISTINCT
                    order.id_order,
                    order.reference,
                    `order`.module
                    " . $this->helperSql . '
                    WHERE order.module = "hspointofsalepro"  ' . $this->mutualSql . ') tmp
                    LEFT JOIN ' . _DB_PREFIX_. 'order_payment order_payment ON tmp.`reference` = order_payment.order_reference
                    WHERE order_payment.payment_method NOT LIKE "%Paypal%" AND order_payment.payment_method NOT LIKE "%Stripe%" AND order_payment.payment_method NOT IN ("Gift card","Carte Cadeau","Credit Slip")
                    GROUP BY module, IF (payment_method = "Carte de crédit", "Credit Card", payment_method)
                    ORDER BY FIELD(payment_method, "Credit Card","Cash","Cheque","Free order","unknown","Interac","InStore Gift Card","RockPOS","Installment","Gift Certificate ","Carte de crédit","Comptant","Deposit","Credit Card(instore)");';
        $res2 =  Db::getInstance()->executeS($sql);

        foreach ($res2 as $res) {
            $sum2 += trim($res['payment_amount'], $this->currencySymbol);
        }
        $sum2 = $this->currencySymbol . $sum2;

        $sql = "SELECT 
                order_payment.payment_method,
                module,
                CONCAT('$this->currencySymbol', REPLACE(CAST(TRIM(ROUND(SUM(order_payment.amount), $this->fracPart)) + 0 AS CHAR), '.', '$this->decimalSeparator')) payment_amount
                #COUNT(id_order) order_count
            FROM(
            SELECT DISTINCT
                    order.id_order,
                    order.reference,
                    `order`.module
                    " . $this->helperSql . '
                    WHERE 1 ' . $this->mutualSql . ') tmp
                    LEFT JOIN ' . _DB_PREFIX_. 'order_payment order_payment ON tmp.`reference` = order_payment.order_reference
                    WHERE order_payment.payment_method IN ("Gift card","Carte Cadeau","Credit Slip")
                    GROUP BY module, IF (payment_method = "Carte Cadeau", "Gift Card", payment_method)
                    ORDER BY FIELD(payment_method, "Gift card","Credit Slip");';
        $res3 =  Db::getInstance()->executeS($sql);

        $new_rows = array();
        $gap = array();
        $gap['payment_method'] = null;
        $gap['module'] = null;
        $gap['payment_amount'] = null;


        $sql_custom = 'SELECT ord.module, SUM(ocr.value) payment_amount FROM ' . _DB_PREFIX_. 'orders  as ord, ' . _DB_PREFIX_. 'order_cart_rule as ocr WHERE ord.id_order = ocr.id_order AND ocr.name LIKE "%promocode%"  AND ord.module != "hspointofsalepro" ' .$order_date_range;
        $dis_online_res = Db::getInstance()->executeS($sql_custom);
        $total_discount_online = '$ '. number_format($dis_online_res[0]['payment_amount'],2);


        $sql_custom = 'SELECT ord.module, SUM(ocr.value) payment_amount FROM ' . _DB_PREFIX_. 'orders  as ord, ' . _DB_PREFIX_. 'order_cart_rule as ocr WHERE ord.id_order = ocr.id_order AND ocr.name LIKE "%Point of Sale%"   ' .$order_date_range;
        $dis_offline_res = Db::getInstance()->executeS($sql_custom);
        $total_discount_offline = '$ '. number_format($dis_offline_res[0]['payment_amount'],2);



        $sql_custom = 'SELECT SUM(ocr.total_products_tax_incl) refund_online FROM ' . _DB_PREFIX_. 'orders  as ord, ' . _DB_PREFIX_. 'order_slip as ocr WHERE ord.id_order = ocr.id_order  AND ord.module != "hspointofsalepro" ' .$order_date_range;
        $refund_online_res = Db::getInstance()->executeS($sql_custom);
        $total_refund_online = '$ '. number_format($refund_online_res[0]['refund_online'],2);


        $sql_custom = 'SELECT SUM(ocr.total_products_tax_incl) refund_offline FROM ' . _DB_PREFIX_. 'orders  as ord, ' . _DB_PREFIX_. 'order_slip as ocr WHERE ord.id_order = ocr.id_order  AND ord.module = "hspointofsalepro" ' .$order_date_range;
        $refund_offline_res = Db::getInstance()->executeS($sql_custom);
        $total_refund_offline = '$ '. number_format($refund_offline_res[0]['refund_offline'],2);


        $discount_for_online = array();
        $discount_for_online['payment_method'] = 'Discount Online';
        $discount_for_online['module'] = ' ';
        $discount_for_online['payment_amount'] = $total_discount_online;

        $refund_for_online = array();
        $refund_for_online['payment_method'] = 'Refund Online';
        $refund_for_online['module'] = ' ';
        $refund_for_online['payment_amount'] = $total_refund_online;



        $discount_for_instore = array();
        $discount_for_instore['payment_method'] = 'Discount InStore';
        $discount_for_instore['module'] = '';
        $discount_for_instore['payment_amount'] = $total_discount_offline;

        $refund_for_offline = array();
        $refund_for_offline['payment_method'] = 'Instore Refund';
        $refund_for_offline['module'] = ' ';
        $refund_for_offline['payment_amount'] = $total_refund_offline;




        $new_rows[] = $gap;
        $new_rows_online[] = $discount_for_online;
        $new_rows_online[] = $refund_for_online;
        $new_rows_offline[] = $discount_for_instore;
        $new_rows_offline[] = $refund_for_offline;


        $this->nth_total['res1'] = count($res1) + count($new_rows_online);

        // $this->nth_total['res2'] = count($res2) + count($res3) + count($new_rows_offline);


        return array_merge( array(array(
            'module' => $this->module->l('TOTAL FOR ONLINE SALES', 'ExportSales'),
            'payment_method' => '',
            'payment_amount' => $sum1,
            'order_count' => '')),$res1,$new_rows_online,  array(array(
            'module' => $this->module->l('TOTAL FOR IN-STORE', 'ExportSales'),
            'payment_method' => '',
            'payment_amount' => $sum2,
            'order_count' => '')),$res2, $res3,$new_rows_offline);

    }

    private function getSalesByCategories()
    {
        $sql = "SELECT 
                    category.id_category,
                    category.name category_name,
                    CONCAT('$this->currencySymbol', REPLACE(CAST(TRIM(ROUND(SUM(product.total_price_tax_excl), $this->fracPart)) + 0 AS CHAR), '.', '$this->decimalSeparator')) total_price_tax_excl
            " . $this->helperSql . '
                    WHERE 1 ' . $this->mutualSql . ' GROUP BY category.id_category
                    ORDER BY SUM(product.total_price_tax_excl) DESC;';
        return DB::getInstance()->executeS($sql);
    }

    private function getSalesByBrands()
    {
        $sql = "SELECT 
                    manufacturer.id_manufacturer,
                    IFNULL(manufacturer.name, '" . $this->module->l('Without brand') . "') manufacturer_name,
                    CONCAT('$this->currencySymbol', REPLACE(CAST(TRIM(ROUND(SUM(product.total_price_tax_excl), $this->fracPart)) + 0 AS CHAR), '.', '$this->decimalSeparator')) total_price_tax_excl
            " . $this->helperSql . '
                    WHERE 1 ' . $this->mutualSql . ' GROUP BY manufacturer.id_manufacturer
                    ORDER BY SUM(product.total_price_tax_excl) DESC;';
        return DB::getInstance()->executeS($sql);
    }

    private function getSalesBySuppliers()
    {
        $sql = "SELECT 
                    supplier.id_supplier,
                    IFNULL(supplier.name, '" . $this->module->l('Without supplier') . "') supplier_name,
                    CONCAT('$this->currencySymbol', REPLACE(CAST(TRIM(ROUND(SUM(product.total_price_tax_excl), $this->fracPart)) + 0 AS CHAR), '.', '$this->decimalSeparator')) total_price_tax_excl
            " . $this->helperSql . '
                    WHERE 1 ' . $this->mutualSql . ' GROUP BY supplier.id_supplier
                    ORDER BY SUM(product.total_price_tax_excl) DESC;';
        return DB::getInstance()->executeS($sql);
    }

    private function getSalesByAttributes()
    {
        $helper_sql = str_replace($this->attributeJoin, '', $this->helperSql);
        $helper_sql = str_replace($this->attributeJoinForNull, '', $helper_sql);
        $mutual_sql = str_replace($this->attributeCond, '', $this->mutualSql);

        $sql = "SELECT 
                    attribute.attribute_group_name,
                    IFNULL(attribute.attribute_name, '" . $this->module->l('Without attribute') . "') attribute_name,
                    CONCAT('$this->currencySymbol', REPLACE(CAST(TRIM(ROUND(SUM(product.total_price_tax_excl), $this->fracPart)) + 0 AS CHAR), '.', '$this->decimalSeparator')) total_price_tax_excl
            " . $helper_sql;

        $attribute_cond = $attributes_cond2 = '';

        // If any attribute selected or unselected in the attributes datatable
        if ($this->attributeData['attributes']) {
            if ($this->attributeData['attributes_type'] === 'unselected') {
                $attributes_cond2 = "WHERE id_attribute NOT IN ('" . $this->attributeData['attributes'] . "')";
            } else {
                $attributes_cond2 = "WHERE id_attribute IN ('" . $this->attributeData['attributes'] . "')";
            }
        }
        $sql .= ' 
                    LEFT JOIN (
                           SELECT DISTINCT 
                            pac.id_product_attribute,
                            agl.name attribute_group_name,
                            al.name attribute_name
                           FROM ' . _DB_PREFIX_ . 'product_attribute_combination pac
                           LEFT JOIN ' . _DB_PREFIX_ . 'attribute a ON pac.id_attribute = a.id_attribute
                           LEFT JOIN ' . _DB_PREFIX_ . 'attribute_lang al ON a.id_attribute = al.id_attribute AND al.id_lang = ' . $this->langId . '
                           LEFT JOIN ' . _DB_PREFIX_ . 'attribute_group_lang agl ON a.id_attribute_group = agl.id_attribute_group AND agl.id_lang = ' . $this->langId . '
                           ' . $attributes_cond2 . '
                           ) attribute ON `product`.product_attribute_id = attribute.id_product_attribute
            ';

        $sql .= ' 
                    LEFT JOIN
                    (SELECT DISTINCT
                        id_product_attribute
                    FROM
                        ' . _DB_PREFIX_ . 'product_attribute_combination) for_null_attribute ON product.product_attribute_id = for_null_attribute.id_product_attribute';

        if ($this->attributeData['attributes']) {
            $attribute_cond .= 'attribute.id_product_attribute IS NOT NULL';
            if ($this->attributeData['attributes_without'] !== '0') {
                $attribute_cond .= ' OR for_null_attribute.id_product_attribute IS NULL';
            }
            $attribute_cond = ' AND (' . $attribute_cond . ')';
        } elseif ($this->attributeData['attributes_without'] !== '0' && $this->attributeData['attributes_type'] === 'selected') {
            $attribute_cond .= ' AND (for_null_attribute.id_product_attribute IS NULL)';
        } elseif ($this->attributeData['attributes_without'] === '0' && $this->attributeData['attributes_type'] === 'unselected') {
            $attribute_cond .= ' AND (for_null_attribute.id_product_attribute IS NOT NULL)';
        }

        $sql .= ' WHERE 1 ' . $mutual_sql . $attribute_cond . ' 
                    GROUP BY attribute.attribute_group_name, attribute.attribute_name
                    ORDER BY SUM(product.total_price_tax_excl) DESC;';

        return DB::getInstance()->executeS($sql);
    }

    private function getSalesByFeatures()
    {
        $helper_sql = str_replace($this->featureJoin, '', $this->helperSql);
        $helper_sql = str_replace($this->featureJoinForNull, '', $helper_sql);
        $mutual_sql = str_replace($this->featureCond, '', $this->mutualSql);

        $sql = "SELECT 
                    feature.feature_name,
                    IFNULL(feature.feature_value, '" . $this->module->l('Without feature') . "') feature_value,
                    IF(feature.feature_custom = 0, '" . $this->module->l('No', 'ExportSales') . "', IF(feature.feature_custom = 1, '" . $this->module->l('Yes', 'ExportSales') . "', NULL)) feature_custom,
                    CONCAT('$this->currencySymbol', REPLACE(CAST(TRIM(ROUND(SUM(product.total_price_tax_excl), $this->fracPart)) + 0 AS CHAR), '.', '$this->decimalSeparator')) total_price_tax_excl
            " . $helper_sql;

        $feature_cond = $features_cond2 = '';

        // If any feature selected or unselected in the features datatable
        if ($this->featureData['features']) {
            if ($this->featureData['features_type'] === 'unselected') {
                $features_cond2 = "WHERE CONCAT(fl.name, '_#&_', fvl.value, '_#&_', fv.custom) NOT IN ('" . $this->featureData['features'] . "')";
            } else {
                $features_cond2 = "WHERE CONCAT(fl.name, '_#&_', fvl.value, '_#&_', fv.custom) IN ('" . $this->featureData['features'] . "')";
            }
        }
        $sql .= ' 
                    LEFT JOIN (
                           SELECT DISTINCT 
                            fp.id_product,
                            fl.name feature_name,
                            fvl.value feature_value,
                            fv.custom feature_custom
                           FROM ' . _DB_PREFIX_ . 'feature_product fp
                           LEFT JOIN ' . _DB_PREFIX_ . 'feature_lang fl ON fp.id_feature = fl.id_feature AND fl.id_lang = ' . $this->langId . '
                           LEFT JOIN ' . _DB_PREFIX_ . 'feature_value fv ON fp.id_feature_value = fv.id_feature_value
                           LEFT JOIN ' . _DB_PREFIX_ . 'feature_value_lang fvl ON fv.id_feature_value = fvl.id_feature_value AND fvl.id_lang = ' . $this->langId . '
                           ' . $features_cond2 . '
                           ) feature ON `product`.product_id = feature.id_product
            ';

        $sql .= ' 
                    LEFT JOIN
                    (SELECT DISTINCT
                        id_product
                    FROM
                        ' . _DB_PREFIX_ . 'feature_product) for_null_feature ON product.product_id = for_null_feature.id_product';

        if ($this->featureData['features']) {
            $feature_cond .= 'feature.id_product IS NOT NULL';
            if ($this->featureData['features_without'] !== '0') {
                $feature_cond .= ' OR for_null_feature.id_product IS NULL';
            }
            $feature_cond = ' AND (' . $feature_cond . ')';
        } elseif ($this->featureData['features_without'] !== '0' && $this->featureData['features_type'] === 'selected') {
            $feature_cond .= ' AND (for_null_feature.id_product IS NULL)';
        } elseif ($this->featureData['features_without'] === '0' && $this->featureData['features_type'] === 'unselected') {
            $feature_cond .= ' AND (for_null_feature.id_product IS NOT NULL)';
        }

        $sql .= ' WHERE 1 ' . $mutual_sql . $feature_cond . ' 
                    GROUP BY feature.feature_name, feature.feature_value, feature.feature_custom
                    ORDER BY SUM(product.total_price_tax_excl) DESC;';

        return DB::getInstance()->executeS($sql);
    }

    private function getSalesByShops()
    {
        $sql = "SELECT 
                    shop.id_shop,
                    shop.name shop_name,
                    shop_group.name shop_group_name,
                    CONCAT('$this->currencySymbol', REPLACE(CAST(TRIM(ROUND(SUM(product.total_price_tax_excl), $this->fracPart)) + 0 AS CHAR), '.', '$this->decimalSeparator')) total_price_tax_excl
            " . $this->helperSql . '
                    WHERE 1 ' . $this->mutualSql . ' GROUP BY shop.id_shop
                    ORDER BY SUM(product.total_price_tax_excl) DESC;';
        return DB::getInstance()->executeS($sql);
    }

    private function setHelperSql()
    {
        $this->helperSql = '
                FROM ' . _DB_PREFIX_ . 'orders `order`
                LEFT JOIN ' . _DB_PREFIX_ . 'order_detail `product` USING(id_order)
                LEFT JOIN (
                    SELECT 
                        order_reference,
                        MIN(payment.`date_add`) min_date,
                        MAX(payment.`date_add`) max_date
                    FROM ' . _DB_PREFIX_ . 'order_payment payment
                    LEFT JOIN ' . _DB_PREFIX_ . 'currency `currency` ON `currency`.id_currency = `payment`.id_currency
                    GROUP BY order_reference
                    ) `payment` ON order.reference = payment.order_reference
                LEFT JOIN ' . _DB_PREFIX_ . 'order_state_lang `order_state` ON current_state = id_order_state 
                    AND `order_state`.id_lang = ' . $this->langId . '
                LEFT JOIN ' . _DB_PREFIX_ . 'customer `customer` USING(id_customer)
                LEFT JOIN (
                    SELECT
                        id_order,
                        id_carrier,
                        weight,
                        tracking_number,
                        `date_add`,
                        id_order_invoice
                    FROM ' . _DB_PREFIX_ . 'order_carrier
                    GROUP BY id_order
                    HAVING id_order_invoice = MIN(id_order_invoice)
                    ) `carrier` ON `carrier`.id_carrier = `order`.id_carrier AND `carrier`.id_order = `order`.id_order
                LEFT JOIN ' . _DB_PREFIX_ . 'carrier `carrier_name` ON `carrier_name`.id_carrier = `carrier`.id_carrier
                LEFT JOIN ' . _DB_PREFIX_ . 'currency `currency` ON `currency`.id_currency = `order`.id_currency
                LEFT JOIN ' . _DB_PREFIX_ . 'shop `shop` ON `shop`.id_shop = `order`.id_shop
                LEFT JOIN ' . _DB_PREFIX_ . 'shop_group `shop_group` ON `shop_group`.id_shop_group = `shop`.id_shop_group
                LEFT JOIN ' . _DB_PREFIX_ . 'product prod ON prod.id_product = product.product_id
                LEFT JOIN ' . _DB_PREFIX_ . 'supplier supplier ON supplier.id_supplier = prod.id_supplier
                LEFT JOIN ' . _DB_PREFIX_ . 'manufacturer manufacturer 
                    ON manufacturer.id_manufacturer = prod.id_manufacturer
                LEFT JOIN ' . _DB_PREFIX_ . 'category_lang category 
                    ON prod.id_category_default = category.id_category 
                        AND category.id_shop = `order`.id_shop AND category.id_lang = ' . $this->langId . '
                LEFT JOIN ' . _DB_PREFIX_ . 'address `address_delivery` ON `address_delivery`.id_address = `order`.id_address_delivery
                LEFT JOIN ' . _DB_PREFIX_ . 'country `delivery_country` ON `delivery_country`.id_country = `address_delivery`.id_country 
                LEFT JOIN (
                SELECT id_order, 
                    SUM(total_products_tax_excl) total_products_tax_excl,
                    SUM(total_products_tax_incl) total_products_tax_incl,
                    SUM(total_shipping_tax_excl) total_shipping_tax_excl,
                    SUM(total_shipping_tax_incl) total_shipping_tax_incl,
                    SUM(amount) amount,
                    SUM(shipping_cost_amount) shipping_cost_amount
                FROM ' . _DB_PREFIX_ . 'order_slip
                GROUP BY id_order) order_slip ON `order`.id_order = order_slip.id_order
            LEFT JOIN (
                SELECT id_order_detail, 
                    SUM(product_quantity) product_quantity,
                    SUM(unit_price_tax_excl) unit_price_tax_excl,
                    SUM(unit_price_tax_incl) unit_price_tax_incl,
                    SUM(total_price_tax_excl) total_price_tax_excl,
                    SUM(total_price_tax_incl) total_price_tax_incl,
                    SUM(amount_tax_excl) amount_tax_excl,
                    SUM(amount_tax_incl) amount_tax_incl
                FROM ' . _DB_PREFIX_ . 'order_slip_detail
                GROUP BY id_order_detail) order_slip_detail ON `product`.id_order_detail = order_slip_detail.id_order_detail
            LEFT JOIN ' . _DB_PREFIX_ . 'order_detail_tax canada_tax ON `product`.id_order_detail = canada_tax.id_order_detail AND canada_tax.id_tax = 1
            LEFT JOIN ' . _DB_PREFIX_ . 'order_detail_tax quebec_tax ON `product`.id_order_detail = quebec_tax.id_order_detail AND (quebec_tax.id_tax = 25 OR quebec_tax.id_tax = 34 OR quebec_tax.id_tax = 32 OR quebec_tax.id_tax = 31 OR quebec_tax.id_tax = 28)
            LEFT JOIN ' . _DB_PREFIX_ . 'order_invoice_tax canada_shipping_tax ON `order`.invoice_number = canada_shipping_tax.id_order_invoice AND canada_shipping_tax.id_tax = 1 AND canada_shipping_tax.`type` = "shipping"
            LEFT JOIN ' . _DB_PREFIX_ . 'order_invoice_tax quebec_shipping_tax ON `order`.invoice_number = quebec_shipping_tax.id_order_invoice AND (quebec_shipping_tax.id_tax = 25 OR quebec_shipping_tax.id_tax = 34 OR quebec_tax.id_tax = 32 OR quebec_tax.id_tax = 31 OR quebec_tax.id_tax = 28) AND quebec_shipping_tax.`type` = "shipping"
                '
            . $this->cartRulesJoin . $this->cartRulesJoinForNull
            . $this->featureJoin . $this->featureJoinForNull
            . $this->attributeJoin . $this->attributeJoinForNull;
    }



    private function setMutualSql () {
        $this->mutualSql = '';

        if ($this->auto) {
            if (is_numeric($this->auto)) {
                $this->sql .= '
                    AND `order`.id_order = ' . $this->auto;
            }
            // Filter By Date
            $orders_creation_date = $this->config['orders_creation_date'];
            if ($orders_creation_date === 'this_month') {
                $this->mutualSql .= " 
                        AND order.date_add >= '" . date("Y-m-d", strtotime("first day of this month")) . "'";
                $this->mutualSql .= " 
                        AND order.date_add < '" . date("Y-m-d", strtotime("first day of next month")) . "'";
            } elseif ($orders_creation_date === 'last_month') {
                $this->mutualSql .= " 
                        AND order.date_add >= '" . date("Y-m-d", strtotime("first day of previous month")) . "'";
                $this->mutualSql .= " 
                        AND order.date_add < '" . date("Y-m-d", strtotime("first day of this month")) . "'";
            } elseif ($orders_creation_date === 'this_week') {
                $this->mutualSql .= " 
                        AND order.date_add >= '" . date("Y-m-d", strtotime("last monday")) . "'";
                $this->mutualSql .= " 
                        AND order.date_add < '" . date("Y-m-d", strtotime("this monday")) . "'";
            } elseif ($orders_creation_date === 'last_week') {
                $this->mutualSql .= " 
                        AND order.date_add >= '" . date("Y-m-d", strtotime("last week monday")) . "'";
                $this->mutualSql .= " 
                        AND order.date_add < '" . date("Y-m-d", strtotime("last monday")) . "'";
            } elseif ($orders_creation_date === 'today') {
                $this->mutualSql .= " 
                        AND order.date_add >= '" . date("Y-m-d", strtotime("today")) . "'";
                $this->mutualSql .= " 
                        AND order.date_add < '" . date("Y-m-d", strtotime("tomorrow")) . "'";
            } elseif ($orders_creation_date === 'last_24_hours') {
                $this->mutualSql .= " 
                        AND order.date_add >= '" . date('Y-m-d H:i:s', strtotime("-1 day")) . "'";
                $this->mutualSql .= "
                        AND order.date_add < '" . date('Y-m-d H:i:s') . "'";
            } elseif ($orders_creation_date === 'yesterday') {
                $this->mutualSql .= " 
                        AND order.date_add >= '" . date("Y-m-d", strtotime("yesterday")) . "'";
                $this->mutualSql .= " 
                        AND order.date_add < '" . date("Y-m-d", strtotime("today")) . "'";
            } elseif ($orders_creation_date === 'select_date') {
                $fromDate = pSQL($this->config['orders_from_date']);
                $toDate = pSQL($this->config['orders_to_date']);
                if ($fromDate) {
                    $this->mutualSql .= " 
                            AND order.date_add >= '" . $fromDate . "'";
                }
                if ($toDate) {
                    $this->mutualSql .= " 
                            AND order.date_add < '" . $toDate . "'";
                }
            }

            $orders_invoice_date = $this->config['orders_invoice_date'];
            if ($orders_invoice_date === 'this_month') {
                $this->mutualSql .= " 
                        AND order.invoice_date >= '" . date("Y-m-d", strtotime("first day of this month")) . "'";
                $this->mutualSql .= " 
                        AND order.invoice_date < '" . date("Y-m-d", strtotime("first day of next month")) . "'";
            } elseif ($orders_invoice_date === 'last_month') {
                $this->mutualSql .= " 
                        AND order.invoice_date >= '" . date("Y-m-d", strtotime("first day of previous month")) . "'";
                $this->mutualSql .= " 
                        AND order.invoice_date < '" . date("Y-m-d", strtotime("first day of this month")) . "'";
            } elseif ($orders_invoice_date === 'this_week') {
                $this->mutualSql .= " 
                        AND order.invoice_date >= '" . date("Y-m-d", strtotime("last monday")) . "'";
                $this->mutualSql .= " 
                        AND order.invoice_date < '" . date("Y-m-d", strtotime("this monday")) . "'";
            } elseif ($orders_invoice_date === 'last_week') {
                $this->mutualSql .= " 
                        AND order.invoice_date >= '" . date("Y-m-d", strtotime("last week monday")) . "'";
                $this->mutualSql .= " 
                        AND order.invoice_date < '" . date("Y-m-d", strtotime("last monday")) . "'";
            } elseif ($orders_invoice_date === 'today') {
                $this->mutualSql .= " 
                        AND order.invoice_date >= '" . date("Y-m-d", strtotime("today")) . "'";
                $this->mutualSql .= " 
                        AND order.invoice_date < '" . date("Y-m-d", strtotime("tomorrow")) . "'";
            } elseif ($orders_invoice_date === 'last_24_hours') {
                $this->mutualSql .= " 
                        AND order.invoice_date >= '" . date('Y-m-d H:i:s', strtotime("-1 day")) . "'";
                $this->mutualSql .= "
                        AND order.invoice_date < '" . date('Y-m-d H:i:s') . "'";
            } elseif ($orders_invoice_date === 'yesterday') {
                $this->mutualSql .= " 
                        AND order.invoice_date >= '" . date("Y-m-d", strtotime("yesterday")) . "'";
                $this->mutualSql .= " 
                        AND order.invoice_date < '" . date("Y-m-d", strtotime("today")) . "'";
            } elseif ($orders_invoice_date === 'select_date') {
                $invoiceFromDate = pSQL($this->config['orders_invoice_from_date']);
                $invoiceToDate = pSQL($this->config['orders_invoice_to_date']);
                if ($invoiceFromDate) {
                    $this->mutualSql .= " 
                            AND order.invoice_date >= '" . $invoiceFromDate . "'";
                }
                if ($invoiceToDate) {
                    $this->mutualSql .= " 
                            AND order.invoice_date < '" . $invoiceToDate . "'";
                }
            }

            $orders_delivery_date = $this->config['orders_delivery_date'];
            if ($orders_delivery_date === 'this_month') {
                $this->mutualSql .= " 
                        AND order.delivery_date >= '" . date("Y-m-d", strtotime("first day of this month")) . "'";
                $this->mutualSql .= " 
                        AND order.delivery_date < '" . date("Y-m-d", strtotime("first day of next month")) . "'";
            } elseif ($orders_delivery_date === 'last_month') {
                $this->mutualSql .= " 
                        AND order.delivery_date >= '" . date("Y-m-d", strtotime("first day of previous month")) . "'";
                $this->mutualSql .= " 
                        AND order.delivery_date < '" . date("Y-m-d", strtotime("first day of this month")) . "'";
            } elseif ($orders_delivery_date === 'this_week') {
                $this->mutualSql .= " 
                        AND order.delivery_date >= '" . date("Y-m-d", strtotime("last monday")) . "'";
                $this->mutualSql .= " 
                        AND order.delivery_date < '" . date("Y-m-d", strtotime("this monday")) . "'";
            } elseif ($orders_delivery_date === 'last_week') {
                $this->mutualSql .= " 
                        AND order.delivery_date >= '" . date("Y-m-d", strtotime("last week monday")) . "'";
                $this->mutualSql .= " 
                        AND order.delivery_date < '" . date("Y-m-d", strtotime("last monday")) . "'";
            } elseif ($orders_delivery_date === 'today') {
                $this->mutualSql .= " 
                        AND order.delivery_date >= '" . date("Y-m-d", strtotime("today")) . "'";
                $this->mutualSql .= " 
                        AND order.delivery_date < '" . date("Y-m-d", strtotime("tomorrow")) . "'";
            } elseif ($orders_delivery_date === 'last_24_hours') {
                $this->mutualSql .= " 
                        AND order.delivery_date >= '" . date('Y-m-d H:i:s', strtotime("-1 day")) . "'";
                $this->mutualSql .= "
                        AND order.delivery_date < '" . date('Y-m-d H:i:s') . "'";
            } elseif ($orders_delivery_date === 'yesterday') {
                $this->mutualSql .= " 
                        AND order.delivery_date >= '" . date("Y-m-d", strtotime("yesterday")) . "'";
                $this->mutualSql .= " 
                        AND order.delivery_date < '" . date("Y-m-d", strtotime("today")) . "'";
            } elseif ($orders_delivery_date === 'select_date') {
                $deliveryFromDate = pSQL($this->config['orders_delivery_from_date']);
                $deliveryToDate = pSQL($this->config['orders_delivery_to_date']);
                if ($deliveryFromDate) {
                    $this->mutualSql .= " 
                            AND order.delivery_date >= '" . $deliveryFromDate . "'";
                }
                if ($deliveryToDate) {
                    $this->mutualSql .= " 
                            AND order.delivery_date < '" . $deliveryToDate . "'";
                }
            }

            $orders_payment_date = $this->config['orders_payment_date'];
            if ($orders_payment_date === 'this_month') {
                $this->mutualSql .= " 
                        AND payment.max_date >= '" . date("Y-m-d", strtotime("first day of this month")) . "'";
                $this->mutualSql .= " 
                        AND payment.max_date < '" . date("Y-m-d", strtotime("first day of next month")) . "'";
            } elseif ($orders_payment_date === 'last_month') {
                $this->mutualSql .= " 
                        AND payment.max_date >= '" . date("Y-m-d", strtotime("first day of previous month")) . "'";
                $this->mutualSql .= " 
                        AND payment.max_date < '" . date("Y-m-d", strtotime("first day of this month")) . "'";
            } elseif ($orders_payment_date === 'this_week') {
                $this->mutualSql .= " 
                        AND payment.max_date >= '" . date("Y-m-d", strtotime("last monday")) . "'";
                $this->mutualSql .= " 
                        AND payment.max_date < '" . date("Y-m-d", strtotime("this monday")) . "'";
            } elseif ($orders_payment_date === 'last_week') {
                $this->mutualSql .= " 
                        AND payment.max_date >= '" . date("Y-m-d", strtotime("last week monday")) . "'";
                $this->mutualSql .= " 
                        AND payment.max_date < '" . date("Y-m-d", strtotime("last monday")) . "'";
            } elseif ($orders_payment_date === 'today') {
                $this->mutualSql .= " 
                        AND payment.max_date >= '" . date("Y-m-d", strtotime("today")) . "'";
                $this->mutualSql .= " 
                        AND payment.max_date < '" . date("Y-m-d", strtotime("tomorrow")) . "'";
            } elseif ($orders_payment_date === 'last_24_hours') {
                $this->mutualSql .= " 
                        AND payment.max_date >= '" . date('Y-m-d H:i:s', strtotime("-1 day")) . "'";
                $this->mutualSql .= "
                        AND payment.max_date < '" . date('Y-m-d H:i:s') . "'";
            } elseif ($orders_payment_date === 'yesterday') {
                $this->mutualSql .= " 
                        AND payment.max_date >= '" . date("Y-m-d", strtotime("yesterday")) . "'";
                $this->mutualSql .= " 
                        AND payment.max_date < '" . date("Y-m-d", strtotime("today")) . "'";
            } elseif ($orders_payment_date === 'select_date') {
                $paymentFromDate = pSQL($this->config['orders_payment_from_date']);
                $paymentToDate = pSQL($this->config['orders_payment_to_date']);
                if ($paymentFromDate) {
                    $this->mutualSql .= " 
                            AND payment.max_date >= '" . $paymentFromDate . "'";
                }
                if ($paymentToDate) {
                    $this->mutualSql .= " 
                            AND payment.min_date < '" . $paymentToDate . "'";
                }
            }

            $orders_shipping_date = $this->config['orders_shipping_date'];
            if ($orders_shipping_date === 'this_month') {
                $this->mutualSql .= " 
                        AND carrier.date_add >= '" . date("Y-m-d", strtotime("first day of this month")) . "'";
                $this->mutualSql .= " 
                        AND carrier.date_add < '" . date("Y-m-d", strtotime("first day of next month")) . "'";
            } elseif ($orders_shipping_date === 'last_month') {
                $this->mutualSql .= " 
                        AND carrier.date_add >= '" . date("Y-m-d", strtotime("first day of previous month")) . "'";
                $this->mutualSql .= " 
                        AND carrier.date_add < '" . date("Y-m-d", strtotime("first day of this month")) . "'";
            } elseif ($orders_shipping_date === 'this_week') {
                $this->mutualSql .= " 
                        AND carrier.date_add >= '" . date("Y-m-d", strtotime("last monday")) . "'";
                $this->mutualSql .= " 
                        AND carrier.date_add < '" . date("Y-m-d", strtotime("this monday")) . "'";
            } elseif ($orders_shipping_date === 'last_week') {
                $this->mutualSql .= " 
                        AND carrier.date_add >= '" . date("Y-m-d", strtotime("last week monday")) . "'";
                $this->mutualSql .= " 
                        AND carrier.date_add < '" . date("Y-m-d", strtotime("last monday")) . "'";
            } elseif ($orders_shipping_date === 'today') {
                $this->mutualSql .= " 
                        AND carrier.date_add >= '" . date("Y-m-d", strtotime("today")) . "'";
                $this->mutualSql .= " 
                        AND carrier.date_add < '" . date("Y-m-d", strtotime("tomorrow")) . "'";
            } elseif ($orders_shipping_date === 'last_24_hours') {
                $this->mutualSql .= " 
                        AND carrier.date_add >= '" . date('Y-m-d H:i:s', strtotime("-1 day")) . "'";
                $this->mutualSql .= "
                        AND carrier.date_add < '" . date('Y-m-d H:i:s') . "'";
            } elseif ($orders_shipping_date === 'yesterday') {
                $this->mutualSql .= " 
                        AND carrier.date_add >= '" . date("Y-m-d", strtotime("yesterday")) . "'";
                $this->mutualSql .= " 
                        AND carrier.date_add < '" . date("Y-m-d", strtotime("today")) . "'";
            } elseif ($orders_shipping_date === 'select_date') {
                $shippingFromDate = pSQL($this->config['orders_shipping_from_date']);
                $shippingToDate = pSQL($this->config['orders_shipping_to_date']);
                if ($shippingFromDate) {
                    $this->mutualSql .= " 
                            AND carrier.date_add >= '" . $shippingFromDate . "'";
                }
                if ($shippingToDate) {
                    $this->mutualSql .= " 
                            AND carrier.date_add < '" . $shippingToDate . "'";
                }
            }

            if ($this->datatables) {
                // Filter By Group
                $groups = pSQL(implode(',', $this->datatables['groups']['data']));
                $groups_type = $this->datatables['groups']['type'];
                $groupCond = '';
                if ($groups) {
                    if ($groups_type === 'unselected') {
                        $groupCond = 'order.id_customer NOT IN (
                                            SELECT DISTINCT id_customer FROM ' . _DB_PREFIX_
                            . 'customer_group WHERE id_group IN (' . $groups . '))';
                        if ($this->config['orders_group_without'] === '0') {
                            if ($groupCond) {
                                $groupCond .= ' AND ';
                            }
                            $groupCond .= 'order.id_customer IN (
                                                SELECT DISTINCT id_customer FROM ' . _DB_PREFIX_ . 'customer_group)';
                        }
                    } else {
                        $groupCond = 'order.id_customer IN (
                                            SELECT DISTINCT id_customer FROM ' . _DB_PREFIX_
                            . 'customer_group WHERE id_group IN (' . $groups . '))';
                        if ($this->config['orders_group_without'] !== '0') {
                            if ($groupCond) {
                                $groupCond .= ' OR ';
                            }
                            $groupCond .= 'order.id_customer NOT IN (
                                                SELECT DISTINCT id_customer FROM ' . _DB_PREFIX_ . 'customer_group)';
                        }
                    }
                } elseif ($this->config['orders_group_without'] === '1' && $groups_type === 'selected') {
                    $groupCond .= 'order.id_customer NOT IN (
                            SELECT DISTINCT id_customer FROM ' . _DB_PREFIX_ . 'customer_group)';
                } elseif ($this->config['orders_group_without'] === '0' && $groups_type === 'unselected') {
                    $groupCond .= 'order.id_customer IN (
                            SELECT DISTINCT id_customer FROM ' . _DB_PREFIX_ . 'customer_group)';
                }

                if ($groupCond) {
                    $this->mutualSql .= ' 
                            AND (' . $groupCond . ') ';
                }


                // Filter By Cart Rule 2
                $this->mutualSql .= $this->cartRulesCond;

                // Filter By Feature
                $this->mutualSql .= $this->featureCond;


                // Filter By Attribute
                $this->mutualSql .= $this->attributeCond;


                // Filter By Customer
                $customers = pSQL(implode(',', $this->datatables['customers']['data']));
                $customers_type = $this->datatables['customers']['type'];
                $customerCond = '';
                if ($customers) {
                    if ($customers_type === 'unselected') {
                        $customerCond = 'order.id_customer NOT IN (' . $customers . ')';
                        if ($this->config['orders_customer_without'] === '0') {
                            if ($customerCond) {
                                $customerCond .= ' AND ';
                            }
                            $customerCond .= 'order.id_customer IS NOT NULL AND order.id_customer != 0';
                        }
                    } else {
                        $customerCond = 'order.id_customer IN (' . $customers . ')';
                        if ($this->config['orders_customer_without'] !== '0') {
                            if ($customerCond) {
                                $customerCond .= ' OR ';
                            }
                            $customerCond .= 'order.id_customer IS NULL OR order.id_customer = 0';
                        }
                    }
                } elseif ($this->config['orders_customer_without'] === '1' && $customers_type === 'selected') {
                    $customerCond .= 'order.id_customer IS NULL OR order.id_customer = 0';
                } elseif ($this->config['orders_customer_without'] === '0' && $customers_type === 'unselected') {
                    $customerCond .= 'order.id_customer IS NOT NULL AND order.id_customer != 0';
                }

                if ($customerCond) {
                    $this->mutualSql .= ' 
                            AND (' . $customerCond . ') ';
                }


                // Filter By Order
                $orders = pSQL(implode(',', $this->datatables['orders']['data']));
                $orders_type = $this->datatables['orders']['type'];
                $orders_cond = '';
                if ($orders) {
                    if ($orders_type === 'unselected') {
                        $orders_cond = 'order.id_order NOT IN (' . $orders . ')';
                    } else {
                        $orders_cond = 'order.id_order IN (' . $orders . ')';
                    }
                }
                if ($orders_cond) {
                    $this->mutualSql .= ' 
                            AND (' . $orders_cond . ') ';
                }


                // Filter By Order State
                $order_states = pSQL(implode(',', $this->datatables['orderStates']['data']));
                $order_states_type = $this->datatables['orderStates']['type'];
                $order_states_cond = '';
                if ($order_states) {
                    if ($order_states_type === 'unselected') {
                        $order_states_cond = 'order.current_state NOT IN (' . $order_states . ')';
                    } else {
                        $order_states_cond = 'order.current_state IN (' . $order_states . ')';
                    }
                }
                if ($order_states_cond) {
                    $this->mutualSql .= ' 
                            AND (' . $order_states_cond . ') ';
                }


                // Filter By Payment Method
                $payment_methods_post = pSQL(implode(',', $this->datatables['paymentMethods']['data']));
                $payment_methods_post = implode("','", explode(',', $payment_methods_post));
                $payment_methods_type = $this->datatables['paymentMethods']['type'];
                $payment_methods_cond = '';
                if ($payment_methods_post) {
                    if ($payment_methods_type === 'unselected') {
                        $payment_methods_cond = "CONCAT(IFNULL(order.module, ''), '_#&_', IFNULL(order.payment, '')) NOT IN ('" . $payment_methods_post . "')";
                    } else {
                        $payment_methods_cond = "CONCAT(IFNULL(order.module, ''), '_#&_', IFNULL(order.payment, '')) IN ('" . $payment_methods_post . "')";
                    }
                }
                if ($payment_methods_cond) {
                    $this->mutualSql .= ' 
                            AND (' . $payment_methods_cond . ') ';
                }


                // Filter By Category
                if ($this->config['orders_category_whether_filter'] === '1') {
                    $categories = $this->config['products_categories'];
                    $categoryCond = 'category.id_category IN (' . implode(',', $categories) . ')';
                    if ($this->config['orders_category_without'] === '1') {
                        $categoryCond .= ' OR category.id_category IS NULL OR category.id_category = 0';
                    }
                    $this->mutualSql .= ' 
                            AND (' . $categoryCond . ') ';
                }


                // Filter By Product
                $products = pSQL(implode(',', $this->datatables['products']['data']));
                $products_type = $this->datatables['products']['type'];
                $products_cond = '';
                if ($products) {
                    if ($products_type === 'unselected') {
                        $products_cond = 'product.product_id NOT IN (' . $products . ')';
                    } else {
                        $products_cond = 'product.product_id IN (' . $products . ')';
                    }
                }
                if ($products_cond) {
                    $this->mutualSql .= ' 
                            AND (' . $products_cond . ') ';
                }


                // Filter By Manufacturer
                $manufacturers = pSQL(implode(',', $this->datatables['manufacturers']['data']));
                $manufacturers_type = $this->datatables['manufacturers']['type'];
                $manufacturerCond = '';
                if ($manufacturers) {
                    if ($manufacturers_type === 'unselected') {
                        $manufacturerCond = 'prod.id_manufacturer NOT IN (' . $manufacturers . ')';
                        if ($this->config['orders_manufacturer_without'] === '0') {
                            if ($manufacturerCond) {
                                $manufacturerCond .= ' AND ';
                            }
                            $manufacturerCond .= 'prod.id_manufacturer IS NOT NULL AND prod.id_manufacturer != 0';
                        }
                    } else {
                        $manufacturerCond = 'prod.id_manufacturer IN (' . $manufacturers . ')';
                        if ($this->config['orders_manufacturer_without'] !== '0') {
                            if ($manufacturerCond) {
                                $manufacturerCond .= ' OR ';
                            }
                            $manufacturerCond .= 'prod.id_manufacturer IS NULL OR prod.id_manufacturer = 0';
                        }
                    }
                } elseif ($this->config['orders_manufacturer_without'] === '1' && $manufacturers_type === 'selected') {
                    $manufacturerCond .= 'prod.id_manufacturer IS NULL OR prod.id_manufacturer = 0';
                } elseif ($this->config['orders_manufacturer_without'] === '0' && $manufacturers_type === 'unselected') {
                    $manufacturerCond .= 'prod.id_manufacturer IS NOT NULL AND prod.id_manufacturer != 0';
                }

                if ($manufacturerCond) {
                    $this->mutualSql .= ' 
                            AND (' . $manufacturerCond . ') ';
                }


                // Filter By Supplier
                $suppliers = pSQL(implode(',', $this->datatables['suppliers']['data']));
                $suppliers_type = $this->datatables['suppliers']['type'];
                $supplierCond = '';
                if ($suppliers) {
                    if ($suppliers_type === 'unselected') {
                        $supplierCond = 'prod.id_supplier NOT IN (' . $suppliers . ')';
                        if ($this->config['orders_supplier_without'] === '0') {
                            if ($supplierCond) {
                                $supplierCond .= ' AND ';
                            }
                            $supplierCond .= 'prod.id_supplier IS NOT NULL AND prod.id_supplier != 0';
                        }
                    } else {
                        $supplierCond = 'prod.id_supplier IN (' . $suppliers . ')';
                        if ($this->config['orders_supplier_without'] !== '0') {
                            if ($supplierCond) {
                                $supplierCond .= ' OR ';
                            }
                            $supplierCond .= 'prod.id_supplier IS NULL OR prod.id_supplier = 0';
                        }
                    }
                } elseif ($this->config['orders_supplier_without'] === '1' && $suppliers_type === 'selected') {
                    $supplierCond .= 'prod.id_supplier IS NULL OR prod.id_supplier = 0';
                } elseif ($this->config['orders_supplier_without'] === '0' && $suppliers_type === 'unselected') {
                    $supplierCond .= 'prod.id_supplier IS NOT NULL AND prod.id_supplier != 0';
                }

                if ($supplierCond) {
                    $this->mutualSql .= ' 
                            AND (' . $supplierCond . ') ';
                }


                // Filter By Carrier
                $carriers = pSQL(implode(',', $this->datatables['carriers']['data']));
                $carriers_type = $this->datatables['carriers']['type'];
                $carrierCond = '';
                if ($carriers) {
                    if ($carriers_type === 'unselected') {
                        $carrierCond = 'carrier_name.id_reference NOT IN (' . $carriers . ')';
                        if ($this->config['orders_carrier_without'] === '0') {
                            if ($carrierCond) {
                                $carrierCond .= ' AND ';
                            }
                            $carrierCond .= 'order.id_carrier IS NOT NULL AND order.id_carrier != 0';
                        }
                    } else {
                        $carrierCond = 'carrier_name.id_reference IN (' . $carriers . ')';
                        if ($this->config['orders_carrier_without'] !== '0') {
                            if ($carrierCond) {
                                $carrierCond .= ' OR ';
                            }
                            $carrierCond .= 'order.id_carrier IS NULL OR order.id_carrier = 0';
                        }
                    }
                } elseif ($this->config['orders_carrier_without'] === '1' && $carriers_type === 'selected') {
                    $carrierCond .= 'order.id_carrier IS NULL OR order.id_carrier = 0';
                } elseif ($this->config['orders_carrier_without'] === '0' && $carriers_type === 'unselected') {
                    $carrierCond .= 'order.id_carrier IS NOT NULL AND order.id_carrier != 0';
                }

                if ($carrierCond) {
                    $this->mutualSql .= ' 
                            AND (' . $carrierCond . ') ';
                }


                // Filter By Shop
                $shops = pSQL(implode(',', $this->datatables['shops']['data']));
                $shops_type = $this->datatables['shops']['type'];
                $shops_cond = '';
                if ($shops) {
                    if ($shops_type === 'unselected') {
                        $shops_cond = 'order.id_shop NOT IN (' . $shops . ')';
                    } else {
                        $shops_cond = 'order.id_shop IN (' . $shops . ')';
                    }
                }
                if ($shops_cond) {
                    $this->mutualSql .= ' 
                            AND (' . $shops_cond . ') ';
                }


                // Filter By Delivery Country
                $countries = pSQL(implode(',', $this->datatables['countries']['data']));
                $countries_type = $this->datatables['countries']['type'];
                $countries_cond = '';
                if ($countries) {
                    if ($countries_type === 'unselected') {
                        $countries_cond = 'delivery_country.id_country NOT IN (' . $countries . ')';
                    } else {
                        $countries_cond = 'delivery_country.id_country IN (' . $countries . ')';
                    }
                }
                if ($countries_cond) {
                    $this->mutualSql .= ' 
                            AND (' . $countries_cond . ') ';
                }

                // Filter By Currency
                $currencies = pSQL(implode(',', $this->datatables['currencies']['data']));
                $currencies_type = $this->datatables['currencies']['type'];
                $currencies_cond = '';
                if ($currencies) {
                    if ($currencies_type === 'unselected') {
                        $currencies_cond = 'order.id_currency NOT IN (' . $currencies . ')';
                    } else {
                        $currencies_cond = 'order.id_currency IN (' . $currencies . ')';
                    }
                }
                if ($currencies_cond) {
                    $this->mutualSql .= ' 
                            AND (' . $currencies_cond . ') ';
                }
            }
        } else {
            // Filter By Date
            $orders_creation_date = Tools::getValue('orders_creation_date');
            if ($orders_creation_date === 'this_month') {
                $this->mutualSql .= " 
                    AND order.date_add >= '" . date("Y-m-d", strtotime("first day of this month")) . "'";
                $this->mutualSql .= " 
                    AND order.date_add < '" . date("Y-m-d", strtotime("first day of next month")) . "'";
                $this->filteredDate = date("Y-m");
            } elseif ($orders_creation_date === 'last_month') {
                $this->mutualSql .= " 
                    AND order.date_add >= '" . date("Y-m-d", strtotime("first day of previous month")) . "'";
                $this->mutualSql .= " 
                    AND order.date_add < '" . date("Y-m-d", strtotime("first day of this month")) . "'";
                $this->filteredDate = date("Y-m", strtotime("first day of previous month"));
            } elseif ($orders_creation_date === 'this_week') {
                $this->mutualSql .= " 
                    AND order.date_add >= '" . date("Y-m-d", strtotime("last monday")) . "'";
                $this->mutualSql .= " 
                    AND order.date_add < '" . date("Y-m-d", strtotime("this monday")) . "'";
                $this->filteredDate = date("Y-m-d", strtotime("last monday")) . ' - ' . date("Y-m-d", strtotime("this monday"));
            } elseif ($orders_creation_date === 'last_week') {
                $this->mutualSql .= " 
                    AND order.date_add >= '" . date("Y-m-d", strtotime("last week monday")) . "'";
                $this->mutualSql .= " 
                    AND order.date_add < '" . date("Y-m-d", strtotime("last monday")) . "'";
                $this->filteredDate = date("Y-m-d", strtotime("last week monday")) . ' - ' . date("Y-m-d", strtotime("last monday"));
            } elseif ($orders_creation_date === 'today') {
                $this->mutualSql .= " 
                    AND order.date_add >= '" . date("Y-m-d", strtotime("today")) . "'";
                $this->mutualSql .= " 
                    AND order.date_add < '" . date("Y-m-d", strtotime("tomorrow")) . "'";
                $this->filteredDate = date("Y-m-d", strtotime("today"));
            } elseif ($orders_creation_date === 'last_24_hours') {
                $this->mutualSql .= " 
                    AND order.date_add >= '" . date('Y-m-d H:i:s', strtotime("-1 day")) . "'";
                $this->mutualSql .= "
                    AND order.date_add < '" . date('Y-m-d H:i:s') . "'";
                $this->filteredDate = date('Y-m-d H:i:s', strtotime("-1 day")) . ' - ' . date('Y-m-d H:i:s');
            } elseif ($orders_creation_date === 'yesterday') {
                $this->mutualSql .= " 
                    AND order.date_add >= '" . date("Y-m-d", strtotime("yesterday")) . "'";
                $this->mutualSql .= " 
                    AND order.date_add < '" . date("Y-m-d", strtotime("today")) . "'";
                $this->filteredDate = date("Y-m-d", strtotime("yesterday"));
            } elseif ($orders_creation_date === 'select_date') {
                $fromDate = pSQL(Tools::getValue('orders_from_date'));
                $toDate = pSQL(Tools::getValue('orders_to_date'));
                if ($fromDate) {
                    $this->mutualSql .= " 
                        AND order.date_add >= '" . $fromDate . "'";
                }
                if ($toDate) {
                    $this->mutualSql .= " 
                        AND order.date_add < '" . $toDate . "'";
                }
                $this->filteredDate = $fromDate . ' - ' . $toDate;
            }

            $orders_invoice_date = Tools::getValue('orders_invoice_date');
            if ($orders_invoice_date === 'this_month') {
                $this->mutualSql .= " 
                    AND order.invoice_date >= '" . date("Y-m-d", strtotime("first day of this month")) . "'";
                $this->mutualSql .= " 
                    AND order.invoice_date < '" . date("Y-m-d", strtotime("first day of next month")) . "'";
            } elseif ($orders_invoice_date === 'last_month') {
                $this->mutualSql .= " 
                    AND order.invoice_date >= '" . date("Y-m-d", strtotime("first day of previous month")) . "'";
                $this->mutualSql .= " 
                    AND order.invoice_date < '" . date("Y-m-d", strtotime("first day of this month")) . "'";
            } elseif ($orders_invoice_date === 'this_week') {
                $this->mutualSql .= " 
                    AND order.invoice_date >= '" . date("Y-m-d", strtotime("last monday")) . "'";
                $this->mutualSql .= " 
                    AND order.invoice_date < '" . date("Y-m-d", strtotime("this monday")) . "'";
            } elseif ($orders_invoice_date === 'last_week') {
                $this->mutualSql .= " 
                    AND order.invoice_date >= '" . date("Y-m-d", strtotime("last week monday")) . "'";
                $this->mutualSql .= " 
                    AND order.invoice_date < '" . date("Y-m-d", strtotime("last monday")) . "'";
            } elseif ($orders_invoice_date === 'today') {
                $this->mutualSql .= " 
                    AND order.invoice_date >= '" . date("Y-m-d", strtotime("today")) . "'";
                $this->mutualSql .= " 
                    AND order.invoice_date < '" . date("Y-m-d", strtotime("tomorrow")) . "'";
            } elseif ($orders_invoice_date === 'last_24_hours') {
                $this->mutualSql .= " 
                    AND order.invoice_date >= '" . date('Y-m-d H:i:s', strtotime("-1 day")) . "'";
                $this->mutualSql .= "
                    AND order.invoice_date < '" . date('Y-m-d H:i:s') . "'";
            } elseif ($orders_invoice_date === 'yesterday') {
                $this->mutualSql .= " 
                    AND order.invoice_date >= '" . date("Y-m-d", strtotime("yesterday")) . "'";
                $this->mutualSql .= " 
                    AND order.invoice_date < '" . date("Y-m-d", strtotime("today")) . "'";
            } elseif ($orders_invoice_date === 'select_date') {
                $invoiceFromDate = pSQL(Tools::getValue('orders_invoice_from_date'));
                $invoiceToDate = pSQL(Tools::getValue('orders_invoice_to_date'));
                if ($invoiceFromDate) {
                    $this->mutualSql .= " 
                        AND order.invoice_date >= '" . $invoiceFromDate . "'";
                }
                if ($invoiceToDate) {
                    $this->mutualSql .= " 
                        AND order.invoice_date < '" . $invoiceToDate . "'";
                }
            }

            $orders_delivery_date = Tools::getValue('orders_delivery_date');
            if ($orders_delivery_date === 'this_month') {
                $this->mutualSql .= " 
                    AND order.delivery_date >= '" . date("Y-m-d", strtotime("first day of this month")) . "'";
                $this->mutualSql .= " 
                    AND order.delivery_date < '" . date("Y-m-d", strtotime("first day of next month")) . "'";
            } elseif ($orders_delivery_date === 'last_month') {
                $this->mutualSql .= " 
                    AND order.delivery_date >= '" . date("Y-m-d", strtotime("first day of previous month")) . "'";
                $this->mutualSql .= " 
                    AND order.delivery_date < '" . date("Y-m-d", strtotime("first day of this month")) . "'";
            } elseif ($orders_delivery_date === 'this_week') {
                $this->mutualSql .= " 
                    AND order.delivery_date >= '" . date("Y-m-d", strtotime("last monday")) . "'";
                $this->mutualSql .= " 
                    AND order.delivery_date < '" . date("Y-m-d", strtotime("this monday")) . "'";
            } elseif ($orders_delivery_date === 'last_week') {
                $this->mutualSql .= " 
                    AND order.delivery_date >= '" . date("Y-m-d", strtotime("last week monday")) . "'";
                $this->mutualSql .= " 
                    AND order.delivery_date < '" . date("Y-m-d", strtotime("last monday")) . "'";
            } elseif ($orders_delivery_date === 'today') {
                $this->mutualSql .= " 
                    AND order.delivery_date >= '" . date("Y-m-d", strtotime("today")) . "'";
                $this->mutualSql .= " 
                    AND order.delivery_date < '" . date("Y-m-d", strtotime("tomorrow")) . "'";
            } elseif ($orders_delivery_date === 'last_24_hours') {
                $this->mutualSql .= " 
                    AND order.delivery_date >= '" . date('Y-m-d H:i:s', strtotime("-1 day")) . "'";
                $this->mutualSql .= "
                    AND order.delivery_date < '" . date('Y-m-d H:i:s') . "'";
            } elseif ($orders_delivery_date === 'yesterday') {
                $this->mutualSql .= " 
                    AND order.delivery_date >= '" . date("Y-m-d", strtotime("yesterday")) . "'";
                $this->mutualSql .= " 
                    AND order.delivery_date < '" . date("Y-m-d", strtotime("today")) . "'";
            } elseif ($orders_delivery_date === 'select_date') {
                $deliveryFromDate = pSQL(Tools::getValue('orders_delivery_from_date'));
                $deliveryToDate = pSQL(Tools::getValue('orders_delivery_to_date'));
                if ($deliveryFromDate) {
                    $this->mutualSql .= " 
                        AND order.delivery_date >= '" . $deliveryFromDate . "'";
                }
                if ($deliveryToDate) {
                    $this->mutualSql .= " 
                        AND order.delivery_date < '" . $deliveryToDate . "'";
                }
            }

            $orders_payment_date = Tools::getValue('orders_payment_date');
            if ($orders_payment_date === 'this_month') {
                $this->mutualSql .= " 
                    AND payment.max_date >= '" . date("Y-m-d", strtotime("first day of this month")) . "'";
                $this->mutualSql .= " 
                    AND payment.max_date < '" . date("Y-m-d", strtotime("first day of next month")) . "'";
            } elseif ($orders_payment_date === 'last_month') {
                $this->mutualSql .= " 
                    AND payment.max_date >= '" . date("Y-m-d", strtotime("first day of previous month")) . "'";
                $this->mutualSql .= " 
                    AND payment.max_date < '" . date("Y-m-d", strtotime("first day of this month")) . "'";
            } elseif ($orders_payment_date === 'this_week') {
                $this->mutualSql .= " 
                    AND payment.max_date >= '" . date("Y-m-d", strtotime("last monday")) . "'";
                $this->mutualSql .= " 
                    AND payment.max_date < '" . date("Y-m-d", strtotime("this monday")) . "'";
            } elseif ($orders_payment_date === 'last_week') {
                $this->mutualSql .= " 
                    AND payment.max_date >= '" . date("Y-m-d", strtotime("last week monday")) . "'";
                $this->mutualSql .= " 
                    AND payment.max_date < '" . date("Y-m-d", strtotime("last monday")) . "'";
            } elseif ($orders_payment_date === 'today') {
                $this->mutualSql .= " 
                    AND payment.max_date >= '" . date("Y-m-d", strtotime("today")) . "'";
                $this->mutualSql .= " 
                    AND payment.max_date < '" . date("Y-m-d", strtotime("tomorrow")) . "'";
            } elseif ($orders_payment_date === 'last_24_hours') {
                $this->mutualSql .= " 
                    AND payment.max_date >= '" . date('Y-m-d H:i:s', strtotime("-1 day")) . "'";
                $this->mutualSql .= "
                    AND payment.max_date < '" . date('Y-m-d H:i:s') . "'";
            } elseif ($orders_payment_date === 'yesterday') {
                $this->mutualSql .= " 
                    AND payment.max_date >= '" . date("Y-m-d", strtotime("yesterday")) . "'";
                $this->mutualSql .= " 
                    AND payment.max_date < '" . date("Y-m-d", strtotime("today")) . "'";
            } elseif ($orders_payment_date === 'select_date') {
                $paymentFromDate = pSQL(Tools::getValue('orders_payment_from_date'));
                $paymentToDate = pSQL(Tools::getValue('orders_payment_to_date'));
                if ($paymentFromDate) {
                    $this->mutualSql .= " 
                        AND payment.max_date >= '" . $paymentFromDate . "'";
                }
                if ($paymentToDate) {
                    $this->mutualSql .= " 
                        AND payment.min_date < '" . $paymentToDate . "'";
                }
            }

            $orders_shipping_date = Tools::getValue('orders_shipping_date');
            if ($orders_shipping_date === 'this_month') {
                $this->mutualSql .= " 
                    AND carrier.date_add >= '" . date("Y-m-d", strtotime("first day of this month")) . "'";
                $this->mutualSql .= " 
                    AND carrier.date_add < '" . date("Y-m-d", strtotime("first day of next month")) . "'";
            } elseif ($orders_shipping_date === 'last_month') {
                $this->mutualSql .= " 
                    AND carrier.date_add >= '" . date("Y-m-d", strtotime("first day of previous month")) . "'";
                $this->mutualSql .= " 
                    AND carrier.date_add < '" . date("Y-m-d", strtotime("first day of this month")) . "'";
            } elseif ($orders_shipping_date === 'this_week') {
                $this->mutualSql .= " 
                    AND carrier.date_add >= '" . date("Y-m-d", strtotime("last monday")) . "'";
                $this->mutualSql .= " 
                    AND carrier.date_add < '" . date("Y-m-d", strtotime("this monday")) . "'";
            } elseif ($orders_shipping_date === 'last_week') {
                $this->mutualSql .= " 
                    AND carrier.date_add >= '" . date("Y-m-d", strtotime("last week monday")) . "'";
                $this->mutualSql .= " 
                    AND carrier.date_add < '" . date("Y-m-d", strtotime("last monday")) . "'";
            } elseif ($orders_shipping_date === 'today') {
                $this->mutualSql .= " 
                    AND carrier.date_add >= '" . date("Y-m-d", strtotime("today")) . "'";
                $this->mutualSql .= " 
                    AND carrier.date_add < '" . date("Y-m-d", strtotime("tomorrow")) . "'";
            } elseif ($orders_shipping_date === 'last_24_hours') {
                $this->mutualSql .= " 
                    AND carrier.date_add >= '" . date('Y-m-d H:i:s', strtotime("-1 day")) . "'";
                $this->mutualSql .= "
                    AND carrier.date_add < '" . date('Y-m-d H:i:s') . "'";
            } elseif ($orders_shipping_date === 'yesterday') {
                $this->mutualSql .= " 
                    AND carrier.date_add >= '" . date("Y-m-d", strtotime("yesterday")) . "'";
                $this->mutualSql .= " 
                    AND carrier.date_add < '" . date("Y-m-d", strtotime("today")) . "'";
            } elseif ($orders_shipping_date === 'select_date') {
                $shippingFromDate = pSQL(Tools::getValue('orders_shipping_from_date'));
                $shippingToDate = pSQL(Tools::getValue('orders_shipping_to_date'));
                if ($shippingFromDate) {
                    $this->mutualSql .= " 
                        AND carrier.date_add >= '" . $shippingFromDate . "'";
                }
                if ($shippingToDate) {
                    $this->mutualSql .= " 
                        AND carrier.date_add < '" . $shippingToDate . "'";
                }
            }

            // Filter By Group
            if (Tools::getIsset('groups_type')) {
                $group_without = Tools::getValue('orders_group_without');
                $groups = pSQL(Tools::getValue('groups_data'));
                $groups_type = Tools::getValue('groups_type');
                $groupCond = '';
                if ($groups) {
                    if ($groups_type === 'unselected') {
                        $groupCond = 'order.id_customer NOT IN (
                                        SELECT DISTINCT id_customer FROM ' . _DB_PREFIX_
                            . 'customer_group WHERE id_group IN (' . $groups . '))';
                        if ($group_without === '0') {
                            if ($groupCond) {
                                $groupCond .= ' AND ';
                            }
                            $groupCond .= 'order.id_customer IN (
                                            SELECT DISTINCT id_customer FROM ' . _DB_PREFIX_ . 'customer_group)';
                        }
                    } else {
                        $groupCond = 'order.id_customer IN (
                                        SELECT DISTINCT id_customer FROM ' . _DB_PREFIX_
                            . 'customer_group WHERE id_group IN (' . $groups . '))';
                        if ($group_without !== '0') {
                            if ($groupCond) {
                                $groupCond .= ' OR ';
                            }
                            $groupCond .= 'order.id_customer NOT IN (
                                            SELECT DISTINCT id_customer FROM ' . _DB_PREFIX_ . 'customer_group)';
                        }
                    }
                } elseif ($group_without === '1' && $groups_type === 'selected') {
                    $groupCond .= 'order.id_customer NOT IN (
                            SELECT DISTINCT id_customer FROM ' . _DB_PREFIX_ . 'customer_group)';
                } elseif ($group_without === '0' && $groups_type === 'unselected') {
                    $groupCond .= 'order.id_customer IN (
                            SELECT DISTINCT id_customer FROM ' . _DB_PREFIX_ . 'customer_group)';
                }

                if ($groupCond) {
                    $this->mutualSql .= ' 
                        AND (' . $groupCond . ') ';
                }
            }

            // Filter By Cart Rule 2
            $this->mutualSql .= $this->cartRulesCond;

            // Filter By Feature
            $this->mutualSql .= $this->featureCond;


            // Filter By Attribute
            $this->mutualSql .= $this->attributeCond;


            // Filter By Customer
            if (Tools::getIsset('customers_type')) {
                $customer_without = Tools::getValue('orders_customer_without');
                $customers = pSQL(Tools::getValue('customers_data'));
                $customers_type = Tools::getValue('customers_type');
                $customerCond = '';
                if ($customers) {
                    if ($customers_type === 'unselected') {
                        $customerCond = 'order.id_customer NOT IN (' . $customers . ')';
                        if ($customer_without === '0') {
                            if ($customerCond) {
                                $customerCond .= ' AND ';
                            }
                            $customerCond .= 'order.id_customer IS NOT NULL AND order.id_customer != 0';
                        }
                    } else {
                        $customerCond = 'order.id_customer IN (' . $customers . ')';
                        if ($customer_without !== '0') {
                            if ($customerCond) {
                                $customerCond .= ' OR ';
                            }
                            $customerCond .= 'order.id_customer IS NULL OR order.id_customer = 0';
                        }
                    }
                } elseif ($customer_without === '1' && $customers_type === 'selected') {
                    $customerCond .= 'order.id_customer IS NULL OR order.id_customer = 0';
                } elseif ($customer_without === '0' && $customers_type === 'unselected') {
                    $customerCond .= 'order.id_customer IS NOT NULL AND order.id_customer != 0';
                }

                if ($customerCond) {
                    $this->mutualSql .= ' 
                        AND (' . $customerCond . ') ';
                }
            }

            // Filter By Order
            if (Tools::getIsset('orders_type')) {
                $orders = pSQL(Tools::getValue('orders_data'));
                $orders_type = Tools::getValue('orders_type');
                $orders_cond = '';
                if ($orders) {
                    if ($orders_type === 'unselected') {
                        $orders_cond = 'order.id_order NOT IN (' . $orders . ')';
                    } else {
                        $orders_cond = 'order.id_order IN (' . $orders . ')';
                    }
                }
                if ($orders_cond) {
                    $this->mutualSql .= ' 
                        AND (' . $orders_cond . ') ';
                }
            }

            // Filter By Order State
            if (Tools::getIsset('order_states_type')) {
                $order_states = pSQL(Tools::getValue('order_states_data'));
                $order_states_type = Tools::getValue('order_states_type');
                $order_states_cond = '';
                if ($order_states) {
                    if ($order_states_type === 'unselected') {
                        $order_states_cond = 'order.current_state NOT IN (' . $order_states . ')';
                    } else {
                        $order_states_cond = 'order.current_state IN (' . $order_states . ')';
                    }
                }
                if ($order_states_cond) {
                    $this->mutualSql .= ' 
                        AND (' . $order_states_cond . ') ';
                }
            }

            // Filter By Payment Method
            if (Tools::getIsset('payment_methods_type')) {
//                $hook_payment = 'Payment';
//                if (Db::getInstance()->getValue('SELECT `id_hook` FROM `'
//                        ._DB_PREFIX_.'hook` WHERE `name` = \'displayPayment\'')) {
//                    $hook_payment = 'displayPayment';
//                }
//
//                $payment_methods = DB::getInstance()->executeS('SELECT DISTINCT m.`id_module`, m.`name`
//                    FROM `'._DB_PREFIX_.'module` m
//                    LEFT JOIN `'._DB_PREFIX_.'hook_module` hm ON hm.`id_module` = m.`id_module`
//                    LEFT JOIN `'._DB_PREFIX_.'hook` h ON hm.`id_hook` = h.`id_hook`
//                    WHERE h.`name` = \''.pSQL($hook_payment).'\'');

                $payment_methods_post = pSQL(Tools::getValue('payment_methods_data'));
                $payment_methods_post = implode("','", explode(',', $payment_methods_post));
                $payment_methods_type = Tools::getValue('payment_methods_type');
                $payment_methods_cond = '';
                if ($payment_methods_post) {
                    if ($payment_methods_type === 'unselected') {
                        $payment_methods_cond = "order.module NOT IN ('" . $payment_methods_post . "')";
                    } else {
                        $payment_methods_cond = "order.module IN ('" . $payment_methods_post . "')";
                    }
                }
                if ($payment_methods_cond) {
                    $this->mutualSql .= ' 
                        AND (' . $payment_methods_cond . ') ';
                }
            }

            // Filter By Category
            if (Tools::getValue('orders_category_whether_filter') === '1' && Tools::getIsset('products_categories')) {
                $categories = Tools::getValue('products_categories');
                $categoryCond = 'category.id_category IN (' . implode(',', $categories) . ')';
                if (Tools::getValue('orders_category_without') === '1') {
                    $categoryCond .= ' OR category.id_category IS NULL OR category.id_category = 0';
                }
                $this->mutualSql .= ' 
                        AND (' . $categoryCond . ') ';
            }

            // Filter By Product
            if (Tools::getIsset('products_type')) {
                $products = pSQL(Tools::getValue('products_data'));
                $products_type = Tools::getValue('products_type');
                $products_cond = '';
                if ($products) {
                    if ($products_type === 'unselected') {
                        $products_cond = 'product.product_id NOT IN (' . $products . ')';
                    } else {
                        $products_cond = 'product.product_id IN (' . $products . ')';
                    }
                }
                if ($products_cond) {
                    $this->mutualSql .= ' 
                        AND (' . $products_cond . ') ';
                }
            }

            // Filter By Manufacturer
            if (Tools::getIsset('manufacturers_type')) {
                $manufacturer_without = Tools::getValue('orders_manufacturer_without');
                $manufacturers = pSQL(Tools::getValue('manufacturers_data'));
                $manufacturers_type = Tools::getValue('manufacturers_type');
                $manufacturerCond = '';
                if ($manufacturers) {
                    if ($manufacturers_type === 'unselected') {
                        $manufacturerCond = 'prod.id_manufacturer NOT IN (' . $manufacturers . ')';
                        if ($manufacturer_without === '0') {
                            if ($manufacturerCond) {
                                $manufacturerCond .= ' AND ';
                            }
                            $manufacturerCond .= 'prod.id_manufacturer IS NOT NULL AND prod.id_manufacturer != 0';
                        }
                    } else {
                        $manufacturerCond = 'prod.id_manufacturer IN (' . $manufacturers . ')';
                        if ($manufacturer_without !== '0') {
                            if ($manufacturerCond) {
                                $manufacturerCond .= ' OR ';
                            }
                            $manufacturerCond .= 'prod.id_manufacturer IS NULL OR prod.id_manufacturer = 0';
                        }
                    }
                } elseif ($manufacturer_without === '1' && $manufacturers_type === 'selected') {
                    $manufacturerCond .= 'prod.id_manufacturer IS NULL OR prod.id_manufacturer = 0';
                } elseif ($manufacturer_without === '0' && $manufacturers_type === 'unselected') {
                    $manufacturerCond .= 'prod.id_manufacturer IS NOT NULL AND prod.id_manufacturer != 0';
                }

                if ($manufacturerCond) {
                    $this->mutualSql .= ' 
                        AND (' . $manufacturerCond . ') ';
                }
            }


            // Filter By Supplier
            if (Tools::getIsset('suppliers_type')) {
                $supplier_without = Tools::getValue('orders_supplier_without');
                $suppliers = pSQL(Tools::getValue('suppliers_data'));
                $suppliers_type = Tools::getValue('suppliers_type');
                $supplierCond = '';
                if ($suppliers) {
                    if ($suppliers_type === 'unselected') {
                        $supplierCond = 'prod.id_supplier NOT IN (' . $suppliers . ')';
                        if ($supplier_without === '0') {
                            if ($supplierCond) {
                                $supplierCond .= ' AND ';
                            }
                            $supplierCond .= 'prod.id_supplier IS NOT NULL AND prod.id_supplier != 0';
                        }
                    } else {
                        $supplierCond = 'prod.id_supplier IN (' . $suppliers . ')';
                        if ($supplier_without !== '0') {
                            if ($supplierCond) {
                                $supplierCond .= ' OR ';
                            }
                            $supplierCond .= 'prod.id_supplier IS NULL OR prod.id_supplier = 0';
                        }
                    }
                } elseif ($supplier_without === '1' && $suppliers_type === 'selected') {
                    $supplierCond .= 'prod.id_supplier IS NULL OR prod.id_supplier = 0';
                } elseif ($supplier_without === '0' && $suppliers_type === 'unselected') {
                    $supplierCond .= 'prod.id_supplier IS NOT NULL AND prod.id_supplier != 0';
                }

                if ($supplierCond) {
                    $this->mutualSql .= ' 
                        AND (' . $supplierCond . ') ';
                }
            }

            // Filter By Carrier
            if (Tools::getIsset('carriers_type')) {
                $carrier_without = Tools::getValue('orders_carrier_without');
                $carriers = pSQL(Tools::getValue('carriers_data'));
                $carriers_type = Tools::getValue('carriers_type');
                $carrierCond = '';
                if ($carriers) {
                    if ($carriers_type === 'unselected') {
                        $carrierCond = 'carrier_name.id_reference NOT IN (' . $carriers . ')';
                        if ($carrier_without === '0') {
                            if ($carrierCond) {
                                $carrierCond .= ' AND ';
                            }
                            $carrierCond .= 'order.id_carrier IS NOT NULL AND order.id_carrier != 0';
                        }
                    } else {
                        $carrierCond = 'carrier_name.id_reference IN (' . $carriers . ')';
                        if ($carrier_without !== '0') {
                            if ($carrierCond) {
                                $carrierCond .= ' OR ';
                            }
                            $carrierCond .= 'order.id_carrier IS NULL OR order.id_carrier = 0';
                        }
                    }
                } elseif ($carrier_without === '1' && $carriers_type === 'selected') {
                    $carrierCond .= 'order.id_carrier IS NULL OR order.id_carrier = 0';
                } elseif ($carrier_without === '0' && $carriers_type === 'unselected') {
                    $carrierCond .= 'order.id_carrier IS NOT NULL AND order.id_carrier != 0';
                }

                if ($carrierCond) {
                    $this->mutualSql .= ' 
                        AND (' . $carrierCond . ') ';
                }
            }

            // Filter By Shop
            if (Tools::getIsset('shops_type')) {
                $shops = pSQL(Tools::getValue('shops_data'));
                $shops_type = Tools::getValue('shops_type');
                $shops_cond = '';
                if ($shops) {
                    if ($shops_type === 'unselected') {
                        $shops_cond = 'order.id_shop NOT IN (' . $shops . ')';
                    } else {
                        $shops_cond = 'order.id_shop IN (' . $shops . ')';
                    }
                }
                if ($shops_cond) {
                    $this->mutualSql .= ' 
                        AND (' . $shops_cond . ') ';
                }
            }

            // Filter By Delivery Country
            if (Tools::getIsset('countries_type')) {
                $countries = pSQL(Tools::getValue('countries_data'));
                $countries_type = Tools::getValue('countries_type');
                $countries_cond = '';
                if ($countries) {
                    if ($countries_type === 'unselected') {
                        $countries_cond = 'delivery_country.id_country NOT IN (' . $countries . ')';
                    } else {
                        $countries_cond = 'delivery_country.id_country IN (' . $countries . ')';
                    }
                }
                if ($countries_cond) {
                    $this->mutualSql .= ' 
                        AND (' . $countries_cond . ') ';
                }
            }

            // Filter By Currency
            if (Tools::getIsset('currencies_type')) {
                $currencies = pSQL(Tools::getValue('currencies_data'));
                $currencies_type = Tools::getValue('currencies_type');
                $currencies_cond = '';
                if ($currencies) {
                    if ($currencies_type === 'unselected') {
                        $currencies_cond = 'order.id_currency NOT IN (' . $currencies . ')';
                    } else {
                        $currencies_cond = 'order.id_currency IN (' . $currencies . ')';
                    }
                }
                if ($currencies_cond) {
                    $this->mutualSql .= ' 
                        AND (' . $currencies_cond . ') ';
                }
            }
        }
    }

    private function getOrders()
    {
        $absentColumns = array(
            'product_link',
            'product_image',
            'product_image_link',
            'attribute_image',
            'attribute_image_link',
            'category_link',
            'category_image',
            'category_image_link',
            'manufacturer_link',
            'manufacturer_image',
            'manufacturer_image_link',
            'supplier_link',
            'supplier_image',
            'supplier_image_link',
            'profit_amount',
            'profit_margin',
            'profit_percentage',
            'net_profit_amount',
            'net_profit_margin',
            'net_profit_percentage',
        );

        $yesNoColumns = array(
//            'cart_rule.free_shipping',
            'discount_quantity_applied',
            'gift',
            'recyclable',
            'mobile_theme',
        );

        $fracPart = $this->fracPart;
        $dateFormat = $this->dateFormat;
        $timeFormat = $this->timeFormat;
        $decimalSeparator = $this->decimalSeparator;
        $langId = $this->langId;

        if (!(array) $this->selectedColumns->product &&
            !(array) $this->selectedColumns->category &&
            !(array) $this->selectedColumns->manufacturer &&
            !(array) $this->selectedColumns->supplier) {
            $this->noProduct = true;
        }

        $this->moneyColumns = array(
            $this->selectedColumns->order->total_discounts,
            $this->selectedColumns->order->total_discounts_tax_incl,
            $this->selectedColumns->order->total_discounts_tax_excl,
            $this->selectedColumns->order->total_paid,
            $this->selectedColumns->order->total_paid_tax_incl,
            $this->selectedColumns->order->total_paid_tax_excl,
            $this->selectedColumns->order->total_paid_real,
            $this->selectedColumns->order->total_products,
            $this->selectedColumns->order->total_products_wt,
//            $this->selectedColumns->order->{'cart_rule.value'},
//            $this->selectedColumns->order->{'cart_rule.value_tax_excl'},
            $this->selectedColumns->order->total_shipping,
            $this->selectedColumns->order->total_shipping_tax_incl,
            $this->selectedColumns->order->total_shipping_tax_excl,
            $this->selectedColumns->order->total_wrapping,
            $this->selectedColumns->order->total_wrapping_tax_incl,
            $this->selectedColumns->order->total_wrapping_tax_excl,
            $this->selectedColumns->order->{'order_slip.total_products_tax_excl'},
            $this->selectedColumns->order->{'order_slip.total_products_tax_incl'},
            $this->selectedColumns->order->{'order_slip.total_shipping_tax_excl'},
            $this->selectedColumns->order->{'order_slip.total_shipping_tax_incl'},
            $this->selectedColumns->order->{'order_slip.amount'},
            $this->selectedColumns->order->{'order_slip.shipping_cost_amount'},
            $this->selectedColumns->order->{'canada_shipping_tax.amount'},
            $this->selectedColumns->order->{'quebec_shipping_tax.amount'},
            $this->selectedColumns->product->product_price,
            $this->selectedColumns->product->total_price_tax_incl,
            $this->selectedColumns->product->total_price_tax_excl,
            $this->selectedColumns->product->unit_price_tax_incl,
            $this->selectedColumns->product->unit_price_tax_excl,
            $this->selectedColumns->product->{'order_details_tax.unit_amount_tax'},
            $this->selectedColumns->product->{'order_details_tax.total_amount_tax'},
            $this->selectedColumns->product->reduction_amount,
            $this->selectedColumns->product->reduction_amount_tax_incl,
            $this->selectedColumns->product->reduction_amount_tax_excl,
            $this->selectedColumns->product->product_quantity_discount,
            $this->selectedColumns->product->ecotax,
            $this->selectedColumns->product->total_shipping_price_tax_incl,
            $this->selectedColumns->product->total_shipping_price_tax_excl,
//            $this->selectedColumns->product->purchase_supplier_price,
            $this->selectedColumns->product->original_product_price,
            $this->selectedColumns->product->original_wholesale_price,
            $this->selectedColumns->product->{'product_tax.total_amount'},
            $this->selectedColumns->product->{'product_tax.unit_amount'},
            $this->selectedColumns->product->{'canada_tax.unit_amount'},
            $this->selectedColumns->product->{'canada_tax.total_amount'},
            $this->selectedColumns->product->{'quebec_tax.unit_amount'},
            $this->selectedColumns->product->{'quebec_tax.total_amount'},
            $this->selectedColumns->product->{'order_slip_detail.unit_price_tax_excl'},
            $this->selectedColumns->product->{'order_slip_detail.unit_price_tax_incl'},
            $this->selectedColumns->product->{'order_slip_detail.total_price_tax_excl'},
            $this->selectedColumns->product->{'order_slip_detail.total_price_tax_incl'},
            $this->selectedColumns->product->{'order_slip_detail.amount_tax_excl'},
            $this->selectedColumns->product->{'order_slip_detail.amount_tax_incl'},
            $this->selectedColumns->payment->amount,
            $this->selectedColumns->carrier->shipping_cost_tax_incl,
            $this->selectedColumns->carrier->shipping_cost_tax_excl,
            $this->selectedColumns->carrier->shipping_cost_tax_amount,
        );

        $this->orderMoneyColumns = array(
            $this->selectedColumns->order->total_discounts,
            $this->selectedColumns->order->total_discounts_tax_incl,
            $this->selectedColumns->order->total_discounts_tax_excl,
            $this->selectedColumns->order->total_paid,
            $this->selectedColumns->order->total_paid_tax_incl,
            $this->selectedColumns->order->total_paid_tax_excl,
            $this->selectedColumns->order->total_paid_real,
            $this->selectedColumns->order->total_products,
            $this->selectedColumns->order->total_products_wt,
//            $this->selectedColumns->order->{'cart_rule.value'},
//            $this->selectedColumns->order->{'cart_rule.value_tax_excl'},
            $this->selectedColumns->order->total_shipping,
            $this->selectedColumns->order->total_shipping_tax_incl,
            $this->selectedColumns->order->total_shipping_tax_excl,
            $this->selectedColumns->order->total_wrapping,
            $this->selectedColumns->order->total_wrapping_tax_incl,
            $this->selectedColumns->order->total_wrapping_tax_excl,
            $this->selectedColumns->order->{'order_slip.total_products_tax_excl'},
            $this->selectedColumns->order->{'order_slip.total_products_tax_incl'},
            $this->selectedColumns->order->{'order_slip.total_shipping_tax_excl'},
            $this->selectedColumns->order->{'order_slip.total_shipping_tax_incl'},
            $this->selectedColumns->order->{'order_slip.amount'},
            $this->selectedColumns->order->{'order_slip.shipping_cost_amount'},
            $this->selectedColumns->order->{'canada_shipping_tax.amount'},
            $this->selectedColumns->order->{'quebec_shipping_tax.amount'},
            $this->selectedColumns->payment->amount,
            $this->selectedColumns->carrier->shipping_cost_tax_incl,
            $this->selectedColumns->carrier->shipping_cost_tax_excl,
            $this->selectedColumns->carrier->shipping_cost_tax_amount,
        );

        $yes = $this->module->l('Yes', 'ExportSales');
        $no = $this->module->l('No', 'ExportSales');

        $rockColumns = array(
            'rock_total_paid_tax_excl',
            'rock_total_paid_tax_incl',
            'rock_total_shipping_tax_excl',
            'rock_total_shipping_tax_incl'
        );

        $this->sql = '
            SELECT ';
        foreach ($this->selectedColumns as $key => $value) {
            foreach ($value as $k => $v) {
                if (in_array($k, $absentColumns)) {
                    $this->{lcfirst(str_replace('_', '', ucwords($k, "_")))} = true;
                    $this->sql .= "
                        '' `$v`, ";
                    continue;
                } elseif (in_array($k, $yesNoColumns)) {
                    $this->sql .= "
                        IF($k = 0 OR $k IS NULL, '" . $no . "', '" . $yes . "') `$v`, ";
                } elseif (in_array($k, $rockColumns)) {
                    $this->sql .= "
                        (TRIM(ROUND(IF(`order`.current_state = ".Configuration::getGlobalValue('PS_OS_REFUND').', `order`.'.ltrim($k, 'rock_').", NULL), $fracPart)) + 0) `$v`, ";
                } elseif ($k === 'new_client') {
                    $this->sql .= "
                        IF((SELECT so.id_order FROM `" . _DB_PREFIX_ . "orders` so WHERE so.id_customer = order.id_customer AND so.id_order < order.id_order LIMIT 1) > 0, '" . $no . "', '" . $yes . "') `$v`, ";
                } elseif ($k === 'invoice_number') {
                    $this->sql .= "
                        CONCAT('" . Configuration::get('PS_INVOICE_PREFIX', $this->langId) . "', LPAD(`order`.invoice_number, 6, '0')) `$v`, ";
                } elseif ($k === 'delivery_number') {
                    $this->sql .= "
                        CONCAT('" . Configuration::get('PS_DELIVERY_PREFIX', $this->langId) . "', LPAD(`order`.delivery_number, 6, '0')) `$v`, ";
                } elseif ($k === 'total_paid_base_curr') {
                    $this->sql .= "
                        CONCAT(" . ($this->displayCurrSymbol ? "'" . Configuration::get('OXSRP_DEF_CURR_SMBL') . " ', " : '') . "REPLACE(ROUND(`order`.total_paid / currency.conversion_rate, $fracPart), '.', '$decimalSeparator')) `$v`, ";
                } elseif (strpos($k, '.') === false) {
                    if ($fracPart !== -1 &&
                        (
                            strpos($k, 'rate') !== false ||
                            strpos($k, 'total') !== false ||
                            strpos($k, 'weight') !== false ||
                            strpos($k, 'price') !== false ||
                            strpos($k, 'percent') !== false ||
                            strpos($k, 'amount') !== false ||
                            strpos($k, 'reduction') !== false ||
                            strpos($k, 'discount') !== false ||
                            strpos($k, 'ecotax') !== false
                        )) {
                        $percSymb = $this->displayCurrSymbol ? '%' : '';
                        $kg = $this->displayCurrSymbol ? ' kg' : '';
//                        if ($decimalSeparator === ',') {
//                            if ($k === 'reduction_percent' || $k === 'group_reduction') {
//                                $this->sql .= "
//                                    CONCAT(REPLACE(CAST(TRIM(ROUND($key.$k, $fracPart)) + 0 AS CHAR), '.', ','), '$percSymb') `$v`, ";
//                            } elseif ($k === 'product_weight') {
//                                $this->sql .= "
//                                    CONCAT(REPLACE(CAST(TRIM(ROUND($key.$k, $fracPart)) + 0 AS CHAR), '.', ','), '$kg') `$v`, ";
//                            } else {
//                                $this->sql .= "
//                                    REPLACE(CAST(TRIM(ROUND($key.$k, $fracPart)) + 0 AS CHAR), '.', ',') `$v`, ";
//                            }
//                        } else {
                        if ($k === 'reduction_percent' || $k === 'group_reduction') {
                            $this->sql .= "
                                    CONCAT((TRIM(ROUND($key.$k, $fracPart)) + 0), '$percSymb') `$v`, ";
                        } elseif ($k === 'product_weight') {
                            $this->sql .= "
                                    CONCAT((TRIM(ROUND($key.$k, $fracPart)) + 0), '$kg') `$v`, ";
                        } else {
                            $this->sql .= "
                                    (TRIM(ROUND($key.$k, $fracPart)) + 0) `$v`, ";
                        }
//                        }
                    } elseif (strpos($k, 'date') !== false || $k === 'download_deadline') {
                        $this->sql .= "
                            DATE_FORMAT($key.$k, '"
                            . SalesExportHelper::$formatArray[$dateFormat]
                            . SalesExportHelper::$formatArray[$timeFormat]
                            . "') `$v`, ";
                    } else {
                        $this->sql .= "
                            $key.$k `$v`, ";
                    }
                } else {
                    if ($fracPart !== -1 &&
                        (
                            strpos($k, 'rate') !== false ||
                            strpos($k, 'total') !== false ||
                            strpos($k, 'weight') !== false ||
                            strpos($k, 'price') !== false ||
                            strpos($k, 'percent') !== false ||
                            strpos($k, 'amount') !== false ||
                            strpos($k, 'reduction') !== false ||
                            strpos($k, 'discount') !== false ||
                            strpos($k, 'ecotax') !== false
                        )) {
//                        if ($decimalSeparator === ',') {
//                            $this->sql .= "
//                                REPLACE(CAST(ROUND($k, $fracPart) AS CHAR), '.', ',') `$v`, ";
//                        } else {
                        $this->sql .= "
                                (TRIM(ROUND($k, $fracPart)) + 0) `$v`, ";
//                        }
                    } elseif (strpos($k, 'date') !== false || $k === 'download_deadline') {
                        $this->sql .= "
                            DATE_FORMAT($k, '"
                            . SalesExportHelper::$formatArray[$dateFormat]
                            . SalesExportHelper::$formatArray[$timeFormat]
                            . "') `$v`, ";
                    } elseif ($k === 'cart_rule.free_shipping') {
                        $this->sql .= "
                            IF($k = 0 OR $k IS NULL, '" . $no . "', '" . $yes . "') `$v`, ";
                    } else {
                        $this->sql .= "
                            $k `$v`, ";
                    }
                }
            }
        }

        if (isset($this->profitAmount) ||
            isset($this->profitMagin) ||
            isset($this->profitPercentage)) {
            // Must come first
            if (!isset($this->selectedColumns->product->purchase_supplier_price)) {
                $this->setPurchaseSupplierPrice();
            }

            if (!isset($this->selectedColumns->order->total_products)) {
                $this->setTotalProducts();
            }

            if (!isset($this->selectedColumns->order->total_discounts_tax_excl)) {
                $this->setTotalDiscountsTaxExcl();
            }
        }
        if (isset($this->netProfitAmount) ||
            isset($this->netProfitMagin) ||
            isset($this->netProfitPercentage)) {
            // Must come first
            if (!isset($this->selectedColumns->product->purchase_supplier_price)) {
                $this->setPurchaseSupplierPrice();
            }

            if (!isset($this->selectedColumns->order->total_products)) {
                $this->setTotalProducts();
            }

            if (!isset($this->selectedColumns->order->total_discounts_tax_excl)) {
                $this->setTotalDiscountsTaxExcl();
            }
        }

        // If purchase price is set, get also quantity to multiply, then sum.
        if (isset($this->selectedColumns->product->purchase_supplier_price) && !isset($this->selectedColumns->product->product_quantity)) {
            $this->setProductQuantity();
        }

        if (!isset($this->selectedColumns->order->id_order)) {
            $this->setOrderID();
        }

        if ((isset($this->productImage) || isset($this->productLink)) && !isset($this->selectedColumns->product->product_id)) {
            $this->setProductID();
        }

        if (isset($this->attributeImage) || isset($this->attributeImageLink)) {
            if (!isset($this->selectedColumns->product->product_id) && !$this->productId) {
                $this->setProductID();
            }
            if (!isset($this->selectedColumns->product->product_attribute_id)) {
                $this->setAttributeID();
            }
            if (!isset($this->selectedColumns->shop->id_shop)) {
                $this->setShopID();
            }
        }

        if (isset($this->productImageLink) || isset($this->attributeImageLink)) {
            if (!isset($this->selectedColumns->product->product_id) && !$this->productId) {
                $this->setProductID();
            }
            if (!isset($this->selectedColumns->product->{'order_detail_lang.product_link_rewrite'})) {
                $this->setProductLinkRewrite();
            }
        }

        if ((isset($this->categoryImage) || isset($this->categoryLink)) && !isset($this->selectedColumns->category->id_category)) {
            $this->setCategoryID();
        }

        if (isset($this->categoryImageLink)) {
            if (!isset($this->selectedColumns->category->id_category) && !$this->categoryId) {
                $this->setCategoryID();
            }
            if (!isset($this->selectedColumns->category->link_rewrite)) {
                $this->setCategoryLinkRewrite();
            }
        }

        if ((isset($this->manufacturerLink) || isset($this->manufacturerImage) || isset($this->manufacturerImageLink)) &&
            !isset($this->selectedColumns->manufacturer->id_manufacturer)) {
            $this->setManufacturerID();
        }

        if ((isset($this->supplierLink) || isset($this->supplierImage) || isset($this->supplierImageLink)) &&
            !isset($this->selectedColumns->supplier->id_supplier)) {
            $this->setSupplierID();
        }

        if (!isset($this->selectedColumns->order->{'currency.iso_code'})) {
            $this->setIsoCode();
        }

        if (!isset($this->selectedColumns->order->{'currency.conversion_rate'})) {
            $this->setConversionRate();
        }


        if ($this->fileType === 'html') {
            if (isset($this->productImage) || isset($this->attributeImage)) {
                if (!isset($this->selectedColumns->product->{'order_detail_lang.product_link_rewrite'}) && !$this->productRewriteLink) {
                    $this->setProductLinkRewrite();
                }
            }

            if (isset($this->categoryImage)) {
                if (!isset($this->selectedColumns->category->link_rewrite) && !$this->categoryRewriteLink) {
                    $this->setCategoryLinkRewrite();
                }
            }
        }

        if ($this->displayTotals === '1' &&
            isset($this->selectedColumns->product->reduction_percent) &&
            !isset($this->selectedColumns->product->total_price_tax_excl)) {
            $this->setTotalPriceTaxExcl();
        }

        $this->sql = rtrim($this->sql, ', ') . '
            FROM ' . _DB_PREFIX_ . 'orders `order`
            LEFT JOIN ' . _DB_PREFIX_ . 'order_detail `product` USING(id_order)
            LEFT JOIN ' . _DB_PREFIX_ . 'product_attribute combination ON product.product_attribute_id = combination.id_product_attribute
            LEFT JOIN (SELECT 
                    id_order_detail, 
                    GROUP_CONCAT(order_detail_tax.id_tax ORDER BY order_detail_tax.id_tax SEPARATOR ", ") id_tax, 
                    GROUP_CONCAT(REPLACE(CAST(ROUND(`rate`, ' . $fracPart . ') AS CHAR), ".", "' . $decimalSeparator . '") ORDER BY order_detail_tax.id_tax SEPARATOR ", ") `rt`,
                    GROUP_CONCAT(`name` ORDER BY order_detail_tax.id_tax SEPARATOR ", ") `name`,
                    GROUP_CONCAT(REPLACE(CAST(ROUND(`unit_amount`, ' . $fracPart . ') AS CHAR), ".", "' . $decimalSeparator . '") ORDER BY order_detail_tax.id_tax SEPARATOR ", ") uat,
                    GROUP_CONCAT(REPLACE(CAST(ROUND(`total_amount`, ' . $fracPart . ') AS CHAR), ".", "' . $decimalSeparator . '") ORDER BY order_detail_tax.id_tax SEPARATOR ", ") tat 
                FROM ' . _DB_PREFIX_ . 'order_detail_tax order_detail_tax 
                LEFT JOIN ' . _DB_PREFIX_ . 'tax tax ON order_detail_tax.id_tax = tax.id_tax 
                LEFT JOIN ' . _DB_PREFIX_ . 'tax_lang tax_lang ON tax.id_tax = tax_lang.id_tax AND tax_lang.id_lang = ' . $langId . '
                GROUP BY id_order_detail) order_details_tax ON 
                    order_details_tax.id_order_detail = product.id_order_detail 
            LEFT JOIN (
                SELECT 
                    order_reference, 
                    SUM(amount) amount, 
                    MIN(payment.`date_add`) min_date,
                    MAX(payment.`date_add`) max_date,
                    GROUP_CONCAT(payment.date_add ORDER BY `date_add` DESC SEPARATOR ", ") payment_dt_add,
                    GROUP_CONCAT(id_order_payment ORDER BY `date_add` DESC SEPARATOR ", ") id_order_payment,
                    GROUP_CONCAT(payment.id_currency ORDER BY `date_add` DESC SEPARATOR ", ") id_currency,
                    GROUP_CONCAT(currency.name ORDER BY `date_add` DESC SEPARATOR ", ") currency_name,
                    GROUP_CONCAT(currency.iso_code ORDER BY `date_add` DESC SEPARATOR ", ") currency_code,
                    GROUP_CONCAT(CONCAT(REPLACE(CAST(ROUND(amount, ' . $fracPart . ') AS CHAR), ".", "' . $decimalSeparator . '"), " (", DATE_FORMAT(`date_add`, "' . SalesExportHelper::$formatArray[$dateFormat] . SalesExportHelper::$formatArray[$timeFormat] . '"), ")") ORDER BY `date_add` DESC SEPARATOR ", ") payment_details, 
                    GROUP_CONCAT(payment_method ORDER BY `date_add` DESC SEPARATOR ", ") payment_methods,
                    GROUP_CONCAT(payment.conversion_rate ORDER BY `date_add` DESC SEPARATOR ", ") conversion_rate,
                    GROUP_CONCAT(transaction_id ORDER BY `date_add` DESC SEPARATOR ", ") transaction_id/* ,
                    GROUP_CONCAT(card_number ORDER BY `date_add` DESC SEPARATOR ", ") card_number,
                    GROUP_CONCAT(card_brand ORDER BY `date_add` DESC SEPARATOR ", ") card_brand,
                    GROUP_CONCAT(card_expiration ORDER BY `date_add` DESC SEPARATOR ", ") card_expiration,
                    GROUP_CONCAT(card_holder ORDER BY `date_add` DESC SEPARATOR ", ") card_holder, */
                FROM ' . _DB_PREFIX_ . 'order_payment payment
                LEFT JOIN ' . _DB_PREFIX_ . 'currency `currency` ON `currency`.id_currency = `payment`.id_currency
                GROUP BY order_reference
                ) `payment` ON order.reference = payment.order_reference
            LEFT JOIN ' . _DB_PREFIX_ . 'order_state_lang `order_state` ON 
                current_state = id_order_state AND `order_state`.id_lang = ' . $langId . '
            LEFT JOIN ' . _DB_PREFIX_ . 'customer `customer` USING(id_customer)
            LEFT JOIN ' . _DB_PREFIX_ . 'gender_lang gender_lang ON `customer`.id_gender = `gender_lang`.id_gender
                AND gender_lang.id_lang = ' . $langId . '
            LEFT JOIN (
                    SELECT 
                        id_order,
                        GROUP_CONCAT(id_cart_rule SEPARATOR ", ") id_cart_rule,
                        GROUP_CONCAT(`name` SEPARATOR ", ") `name`,
                        GROUP_CONCAT(`value` SEPARATOR ", ") `value`,
                        GROUP_CONCAT(value_tax_excl SEPARATOR ", ") value_tax_excl,
                        GROUP_CONCAT(free_shipping SEPARATOR ", ") free_shipping
                    FROM ' . _DB_PREFIX_ . 'order_cart_rule
                    GROUP BY id_order) `cart_rule` ON `order`.id_order = `cart_rule`.id_order
            LEFT JOIN (
                SELECT
                    id_order,
                    id_carrier,
                    weight,
                    tracking_number,
                    shipping_cost_tax_incl,
                    shipping_cost_tax_excl,
                    (shipping_cost_tax_incl - shipping_cost_tax_excl) shipping_cost_tax_amount,
                    `date_add`,
                    id_order_invoice
                FROM ' . _DB_PREFIX_ . 'order_carrier
                GROUP BY id_order
                HAVING id_order_invoice = MIN(id_order_invoice)
                ) `carrier` ON `carrier`.id_carrier = `order`.id_carrier AND `carrier`.id_order = `order`.id_order
            LEFT JOIN ' . _DB_PREFIX_ . 'carrier `carrier_name` ON `carrier_name`.id_carrier = `carrier`.id_carrier
            LEFT JOIN ' . _DB_PREFIX_ . 'currency `currency` ON `currency`.id_currency = `order`.id_currency
            LEFT JOIN ' . _DB_PREFIX_ . 'address `address_delivery` ON `address_delivery`.id_address = id_address_delivery
            LEFT JOIN ' . _DB_PREFIX_ . 'address `address_invoice` ON `address_invoice`.id_address = id_address_invoice
            LEFT JOIN ' . _DB_PREFIX_ . 'country `delivery_country` ON `delivery_country`.id_country = `address_delivery`.id_country
            LEFT JOIN ' . _DB_PREFIX_ . 'country_lang `delivery_country_lang` 
                ON `delivery_country_lang`.id_country = `address_delivery`.id_country 
                AND `delivery_country_lang`.id_lang = ' . $langId . '
            LEFT JOIN ' . _DB_PREFIX_ . 'state `delivery_state` ON `delivery_state`.id_state = `address_delivery`.id_state
            LEFT JOIN ' . _DB_PREFIX_ . 'country `invoice_country` ON `invoice_country`.id_country = `address_invoice`.id_country
            LEFT JOIN ' . _DB_PREFIX_ . 'country_lang `invoice_country_lang` ON 
                `invoice_country_lang`.id_country = `address_invoice`.id_country AND `invoice_country_lang`.id_lang = ' . $langId . '
            LEFT JOIN ' . _DB_PREFIX_ . 'state `invoice_state` ON `invoice_state`.id_state = `address_invoice`.id_state
            LEFT JOIN ' . _DB_PREFIX_ . 'shop `shop` ON `shop`.id_shop = `order`.id_shop
            LEFT JOIN ' . _DB_PREFIX_ . 'shop_group `shop_group` ON `shop_group`.id_shop_group = `shop`.id_shop_group
            LEFT JOIN ' . _DB_PREFIX_ . 'lang `lang` ON `order`.id_lang = `lang`.id_lang
            LEFT JOIN (SELECT 
                    oh.id_order, 
                    GROUP_CONCAT(CONCAT(osl.`name`, " (", DATE_FORMAT(oh.`date_add`, "' . SalesExportHelper::$formatArray[$dateFormat] . SalesExportHelper::$formatArray[$timeFormat] . '"), ")")
                    ORDER BY oh.`date_add` DESC SEPARATOR ", ") order_history
                FROM ' . _DB_PREFIX_ . 'order_history oh
                LEFT JOIN ' . _DB_PREFIX_ . 'order_state_lang osl ON 
                    osl.id_order_state = oh.id_order_state AND osl.id_lang = ' . $langId . '
                GROUP BY oh.id_order) order_state_history ON `order`.id_order = order_state_history.id_order
            LEFT JOIN (SELECT 
                    od.id_order_detail,
                    pl.link_rewrite product_link_rewrite,
                    pl.`name` product_name,
                    pl.`description_short`,
                    pl.`description`,
                    GROUP_CONCAT(CONCAT(agl.`name`, ": ", al.`name`) SEPARATOR ", ") attributes
                FROM ' . _DB_PREFIX_ . 'order_detail od
                LEFT JOIN ' . _DB_PREFIX_ . 'product_attribute_combination pac ON od.product_attribute_id = pac.id_product_attribute
                LEFT JOIN ' . _DB_PREFIX_ . 'attribute a USING(id_attribute)
                LEFT JOIN ' . _DB_PREFIX_ . 'attribute_lang al ON a.id_attribute = al.id_attribute AND al.id_lang = ' . $langId . '
                LEFT JOIN ' . _DB_PREFIX_ . 'attribute_group_lang agl ON a.id_attribute_group = agl.id_attribute_group AND agl.id_lang = ' . $langId . '
                LEFT JOIN ' . _DB_PREFIX_ . 'product_lang pl ON od.product_id = pl.id_product AND od.id_shop = pl.id_shop AND pl.id_lang = ' . $langId . '
                GROUP BY od.id_order_detail) `order_detail_lang` 
                ON `product`.id_order_detail = `order_detail_lang`.id_order_detail
            LEFT JOIN (SELECT
                    id_product,
                    GROUP_CONCAT(CONCAT(' . _DB_PREFIX_ . "feature_lang.`name`, ': ', "
            . _DB_PREFIX_ . "feature_value_lang.`value`) SEPARATOR ', ') features
               FROM " . _DB_PREFIX_ . 'feature_product
               LEFT JOIN ' . _DB_PREFIX_ . 'feature_lang ON ' . _DB_PREFIX_ . 'feature_product.id_feature = '
            . _DB_PREFIX_ . 'feature_lang.id_feature AND ' . _DB_PREFIX_ . 'feature_lang.id_lang = ' . $langId . '
               LEFT JOIN ' . _DB_PREFIX_ . 'feature_value_lang ON ' . _DB_PREFIX_ . 'feature_product.id_feature_value = '
            . _DB_PREFIX_ . 'feature_value_lang.id_feature_value 
                   AND ' . _DB_PREFIX_ . 'feature_value_lang.id_lang = ' . $langId . '
               GROUP BY ' . _DB_PREFIX_ . 'feature_product.id_product
               ) product_features ON `product`.product_id = product_features.id_product
            LEFT JOIN ' . _DB_PREFIX_ . 'product prod ON prod.id_product = product.product_id
            LEFT JOIN ' . _DB_PREFIX_ . 'supplier supplier ON supplier.id_supplier = prod.id_supplier
            LEFT JOIN ' . _DB_PREFIX_ . 'manufacturer manufacturer 
                ON manufacturer.id_manufacturer = prod.id_manufacturer
            LEFT JOIN ' . _DB_PREFIX_ . 'category_lang category 
                ON prod.id_category_default = category.id_category 
                    AND category.id_shop = `order`.id_shop AND category.id_lang = ' . $langId . '
            LEFT JOIN (SELECT 
                        cp.id_product, 
                        cl.id_shop, 
                        GROUP_CONCAT(cl.id_category SEPARATOR ", ") ids, 
                        GROUP_CONCAT(cl.`name` SEPARATOR ", ") names
                    FROM ' . _DB_PREFIX_ . 'category_product cp
                    LEFT JOIN ' . _DB_PREFIX_ . 'category_lang cl ON cp.id_category = cl.id_category AND cl.id_lang = ' . $langId . '
                    GROUP BY cp.id_product, cl.id_shop) cat ON prod.id_product = cat.id_product AND `order`.id_shop = cat.id_shop
            LEFT JOIN (
                SELECT id_order, GROUP_CONCAT(message SEPARATOR ";; ") message
                FROM ' . _DB_PREFIX_ . 'message
                WHERE `private` = 0
                GROUP BY id_order) order_messages ON `order`.id_order = order_messages.id_order
            LEFT JOIN (
                SELECT id_order, 
                    SUM(total_products_tax_excl) total_products_tax_excl,
                    SUM(total_products_tax_incl) total_products_tax_incl,
                    SUM(total_shipping_tax_excl) total_shipping_tax_excl,
                    SUM(total_shipping_tax_incl) total_shipping_tax_incl,
                    SUM(amount) amount,
                    SUM(shipping_cost_amount) shipping_cost_amount
                FROM ' . _DB_PREFIX_ . 'order_slip
                GROUP BY id_order) order_slip ON `order`.id_order = order_slip.id_order
            LEFT JOIN (
                SELECT id_order_detail, 
                    SUM(product_quantity) product_quantity,
                    SUM(unit_price_tax_excl) unit_price_tax_excl,
                    SUM(unit_price_tax_incl) unit_price_tax_incl,
                    SUM(total_price_tax_excl) total_price_tax_excl,
                    SUM(total_price_tax_incl) total_price_tax_incl,
                    SUM(amount_tax_excl) amount_tax_excl,
                    SUM(amount_tax_incl) amount_tax_incl
                FROM ' . _DB_PREFIX_ . 'order_slip_detail
                GROUP BY id_order_detail) order_slip_detail ON `product`.id_order_detail = order_slip_detail.id_order_detail
            LEFT JOIN ' . _DB_PREFIX_ . 'order_detail_tax canada_tax ON `product`.id_order_detail = canada_tax.id_order_detail AND canada_tax.id_tax = 1
            LEFT JOIN ' . _DB_PREFIX_ . 'order_detail_tax quebec_tax ON `product`.id_order_detail = quebec_tax.id_order_detail AND (quebec_tax.id_tax = 25 OR quebec_tax.id_tax = 34 OR quebec_tax.id_tax = 32 OR quebec_tax.id_tax = 31 OR quebec_tax.id_tax = 28)
            LEFT JOIN ' . _DB_PREFIX_ . 'order_invoice_tax canada_shipping_tax ON `order`.invoice_number = canada_shipping_tax.id_order_invoice AND canada_shipping_tax.id_tax = 1 AND canada_shipping_tax.`type` = "shipping"
            LEFT JOIN ' . _DB_PREFIX_ . 'order_invoice_tax quebec_shipping_tax ON `order`.invoice_number = quebec_shipping_tax.id_order_invoice AND (quebec_shipping_tax.id_tax = 25 OR quebec_shipping_tax.id_tax = 34 OR quebec_tax.id_tax = 32 OR quebec_tax.id_tax = 31 OR quebec_tax.id_tax = 28) AND quebec_shipping_tax.`type` = "shipping"
            ';

        // Filter By Cart Rule
        if (!$this->auto) {
            $cart_rules = pSQL(Tools::getValue('cart_rules_data'));
            $cart_rules_type = Tools::getValue('cart_rules_type');
            $cart_rules_without = Tools::getValue('orders_cart_rule_without');
        } else {
            $cart_rules = pSQL(implode(',', $this->datatables['cartRules']['data']));
            $cart_rules_type = $this->datatables['cartRules']['type'];
            $cart_rules_without = $this->config['orders_cart_rule_without'];
        }

        $cart_rules_cond = '';
        $this->cartRulesCond = '';
        $this->cartRulesJoinForNull = '';
        if ($cart_rules) {
            if ($cart_rules_type === 'unselected') {
                $cart_rules_cond = "WHERE id_cart_rule NOT IN (" . $cart_rules . ")";
            } else {
                $cart_rules_cond = "WHERE id_cart_rule IN (" . $cart_rules . ")";
            }
            $this->cartRulesJoin = ' 
                    LEFT JOIN (
                           SELECT DISTINCT id_order FROM ' . _DB_PREFIX_ . 'order_cart_rule
                           ' . $cart_rules_cond . '
                           ) order_cart_rule ON `order`.id_order = order_cart_rule.id_order
            ';
            $this->sql .= $this->cartRulesJoin;
            $this->cartRulesCond .= 'order_cart_rule.id_order IS NOT NULL';
            if ($cart_rules_without !== '0') {
                $this->cartRulesJoinForNull .= ' 
                    LEFT JOIN
                    (SELECT DISTINCT
                        id_order
                    FROM
                        ' . _DB_PREFIX_ . 'order_cart_rule) for_null_order_cart_rule ON `order`.id_order = for_null_order_cart_rule.id_order';
                $this->cartRulesCond .= ' OR for_null_order_cart_rule.id_order IS NULL';
            }
            $this->cartRulesCond = ' AND (' . $this->cartRulesCond . ')';
        } else {
            $this->cartRulesJoin = '';
            if ($cart_rules_without !== '0' && $cart_rules_type === 'selected') {
                $this->cartRulesJoinForNull .= ' 
                    LEFT JOIN
                    (SELECT DISTINCT
                        id_order
                    FROM
                        ' . _DB_PREFIX_ . 'order_cart_rule) for_null_order_cart_rule ON `order`.id_order = for_null_order_cart_rule.id_order';
                $this->cartRulesCond .= ' AND (for_null_order_cart_rule.id_order IS NULL)';
            } elseif ($cart_rules_without === '0' && $cart_rules_type === 'unselected') {
                $this->cartRulesJoinForNull .= ' 
                    LEFT JOIN
                    (SELECT DISTINCT
                        id_order
                    FROM
                        ' . _DB_PREFIX_ . 'order_cart_rule) for_null_order_cart_rule ON `order`.id_order = for_null_order_cart_rule.id_order';
                $this->cartRulesCond .= ' AND (for_null_order_cart_rule.id_order IS NOT NULL)';
            }
        }
        $this->sql .= $this->cartRulesJoinForNull;
        // End Filter By Cart Rule


        // Filter By Feature
        if (!$this->auto) {
            $features = pSQL(Tools::getValue('features_data'));
            $features_type = Tools::getValue('features_type');
            $features_without = Tools::getValue('orders_feature_without');
        } else {
            $features = pSQL(implode(',', $this->datatables['features']['data']));
            $features_type = $this->datatables['features']['type'];
            $features_without = $this->config['orders_feature_without'];
        }
        $features = implode("','", explode(',', $features));

        $this->featureData = array(
            'features' => $features,
            'features_type' => $features_type,
            'features_without' => $features_without,
        );
        $features_cond = '';
        $this->featureCond = '';
        $this->featureJoinForNull = '';
        if ($features) {
            if ($features_type === 'unselected') {
                $features_cond = "WHERE CONCAT(fl.name, '_#&_', fvl.value, '_#&_', fv.custom) NOT IN ('" . $features . "')";
            } else {
                $features_cond = "WHERE CONCAT(fl.name, '_#&_', fvl.value, '_#&_', fv.custom) IN ('" . $features . "')";
            }
            $this->featureJoin = ' 
                    LEFT JOIN (
                           SELECT DISTINCT fp.id_product FROM ' . _DB_PREFIX_ . 'feature_product fp
                           LEFT JOIN ' . _DB_PREFIX_ . 'feature_value fv ON fp.id_feature_value = fv.id_feature_value
                           LEFT JOIN ' . _DB_PREFIX_ . 'feature_lang fl ON fp.id_feature = fl.id_feature AND fl.id_lang = ' . $this->langId . '
                           LEFT JOIN ' . _DB_PREFIX_ . 'feature_value_lang fvl ON fv.id_feature_value = fvl.id_feature_value AND fvl.id_lang = ' . $this->langId . '
                           ' . $features_cond . '
                           ) feature ON `product`.product_id = feature.id_product
            ';
            $this->sql .= $this->featureJoin;
            $this->featureCond .= 'feature.id_product IS NOT NULL';
            if ($features_without !== '0') {
                $this->featureJoinForNull .= ' 
                    LEFT JOIN
                    (SELECT DISTINCT
                        id_product
                    FROM
                        ' . _DB_PREFIX_ . 'feature_product) for_null_feature ON product.product_id = for_null_feature.id_product';
                $this->featureCond .= ' OR for_null_feature.id_product IS NULL';
            }
            $this->featureCond = ' AND (' . $this->featureCond . ')';
        } else {
            $this->featureJoin = '';
            if ($features_without !== '0' && $features_type === 'selected') {
                $this->featureJoinForNull .= ' 
                    LEFT JOIN
                    (SELECT DISTINCT
                        id_product
                    FROM
                        ' . _DB_PREFIX_ . 'feature_product) for_null_feature ON product.product_id = for_null_feature.id_product';
                $this->featureCond .= ' AND (for_null_feature.id_product IS NULL)';
            } elseif ($features_without === '0' && $features_type === 'unselected') {
                $this->featureJoinForNull .= ' 
                    LEFT JOIN
                    (SELECT DISTINCT
                        id_product
                    FROM
                        ' . _DB_PREFIX_ . 'feature_product) for_null_feature ON product.product_id = for_null_feature.id_product';
                $this->featureCond .= ' AND (for_null_feature.id_product IS NOT NULL)';
            }
        }
        $this->sql .= $this->featureJoinForNull;
        // End Filter By Feature

        // Filter By Attribute
        if (!$this->auto) {
            $attributes = pSQL(Tools::getValue('attributes_data'));
            $attributes_type = Tools::getValue('attributes_type');
            $attributes_without = Tools::getValue('orders_attribute_without');
        } else {
            $attributes = pSQL(implode(',', $this->datatables['attributes']['data']));
            $attributes_type = $this->datatables['attributes']['type'];
            $attributes_without = $this->config['orders_attribute_without'];
        }

        $this->attributeData = array(
            'attributes' => $attributes,
            'attributes_type' => $attributes_type,
            'attributes_without' => $attributes_without,
        );
        $attributes_cond = '';
        $this->attributeCond = '';
        $this->attributeJoinForNull = '';
        if ($attributes) {
            if ($attributes_type === 'unselected') {
                $attributes_cond = 'WHERE id_attribute NOT IN (' . $attributes . ')';
            } else {
                $attributes_cond = 'WHERE id_attribute IN (' . $attributes . ')';
            }
            $this->attributeJoin = ' 
                    LEFT JOIN (
                           SELECT DISTINCT id_product_attribute FROM ' . _DB_PREFIX_ . 'product_attribute_combination
                           ' . $attributes_cond . '
                           ) attribute ON `product`.product_attribute_id = attribute.id_product_attribute
            ';
            $this->sql .= $this->attributeJoin;
            $this->attributeCond .= 'attribute.id_product_attribute IS NOT NULL';
            if ($attributes_without !== '0') {
                $this->attributeJoinForNull .= ' 
                    LEFT JOIN
                    (SELECT DISTINCT
                        id_product_attribute
                    FROM
                        ' . _DB_PREFIX_ . 'product_attribute_combination) for_null_attribute ON product.product_attribute_id = for_null_attribute.id_product_attribute';
                $this->attributeCond .= ' OR for_null_attribute.id_product_attribute IS NULL';
            }
            $this->attributeCond = ' AND (' . $this->attributeCond . ')';
        } else {
            $this->attributeJoin = '';
            if ($attributes_without !== '0' && $attributes_type === 'selected') {
                $this->attributeJoinForNull .= ' 
                    LEFT JOIN
                    (SELECT DISTINCT
                        id_product_attribute
                    FROM
                        ' . _DB_PREFIX_ . 'product_attribute_combination) for_null_attribute ON product.product_attribute_id = for_null_attribute.id_product_attribute';
                $this->attributeCond .= ' AND (for_null_attribute.id_product_attribute IS NULL)';
            } elseif ($attributes_without === '0' && $attributes_type === 'unselected') {
                $this->attributeJoinForNull .= ' 
                    LEFT JOIN
                    (SELECT DISTINCT
                        id_product_attribute
                    FROM
                        ' . _DB_PREFIX_ . 'product_attribute_combination) for_null_attribute ON product.product_attribute_id = for_null_attribute.id_product_attribute';
                $this->attributeCond .= ' AND (for_null_attribute.id_product_attribute IS NOT NULL)';
            }
        }
        $this->sql .= $this->attributeJoinForNull;
        // End Filter By Attribute


        if (isset($this->selectedColumns->product->{'tax_rules_group.name'})) {
            if (version_compare(_PS_VERSION_, '1.6.1.0') > -1) {
                $this->sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'tax_rules_group tax_rules_group ON 
                            `product`.id_tax_rules_group = tax_rules_group.id_tax_rules_group ';
            } else {
                $this->sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'tax_rules_group tax_rules_group ON 
                            `prod`.id_tax_rules_group = tax_rules_group.id_tax_rules_group ';
            }
        }

        if ($this->selectedColumns->customer->{'def_group.id_group'} ||
            $this->selectedColumns->customer->{'def_group.name'}) {
            $this->sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'group_lang def_group ON 
                        `customer`.id_default_group = def_group.id_group AND def_group.id_lang = ' . $langId . ' ';
        }
        if ($this->selectedColumns->customer->{'groupp.group_ids'} ||
            $this->selectedColumns->customer->{'groupp.group_names'}) {
            $this->sql .= 'LEFT JOIN (SELECT 
                            c.id_customer, 
                            GROUP_CONCAT(cg.id_group SEPARATOR ", ") group_ids, 
                            GROUP_CONCAT(gl.`name` SEPARATOR ", ") group_names
                        FROM ' . _DB_PREFIX_ . 'customer c
                        LEFT JOIN ' . _DB_PREFIX_ . 'customer_group cg ON c.id_customer = cg.id_customer
                        LEFT JOIN ' . _DB_PREFIX_ . 'group_lang gl ON 
                            cg.id_group = gl.id_group AND gl.id_lang = ' . $langId . '
                        GROUP BY c.id_customer
                    ) groupp ON `customer`.id_customer = groupp.id_customer ';
        }

        if ($this->selectedColumns->product->{'prod_customs.customs'}) {
            $this->sql .= ' LEFT JOIN (
                        SELECT id_cart, id_product, id_product_attribute, id_shop, GROUP_CONCAT(customs SEPARATOR "; ") customs
                        FROM ' . _DB_PREFIX_ . 'customization c
                        LEFT JOIN (
                        SELECT id_customization, id_shop, GROUP_CONCAT(CONCAT(IFNULL(cfl.`name`, ""), ": ", cd.`value`) SEPARATOR ", ") customs FROM ' . _DB_PREFIX_ . 'customized_data cd 
                        LEFT JOIN ' . _DB_PREFIX_ . 'customization_field_lang cfl ON cd.`index` = cfl.id_customization_field AND cfl.id_lang = ' . $langId . '
                        GROUP BY id_customization, id_shop
                        ) tmp ON c.id_customization = tmp.id_customization
                        GROUP BY id_cart, id_product, id_product_attribute, id_shop) prod_customs ON 
                            `order`.id_cart = prod_customs.id_cart
                            AND product.product_id = prod_customs.id_product
                            AND `order`.id_shop = prod_customs.id_shop
                            AND prod_customs.id_product_attribute = 0 ';
        }
        if ($this->selectedColumns->product->{'attrib_customs.customs'}) {
            $this->sql .= ' LEFT JOIN (
                        SELECT id_cart, id_product, id_product_attribute, id_shop, GROUP_CONCAT(customs SEPARATOR "; ") customs
                        FROM ' . _DB_PREFIX_ . 'customization c
                        LEFT JOIN (
                        SELECT id_customization, id_shop, GROUP_CONCAT(CONCAT(IFNULL(cfl.`name`, ""), ": ", cd.`value`) SEPARATOR ", ") customs FROM ' . _DB_PREFIX_ . 'customized_data cd 
                        LEFT JOIN ' . _DB_PREFIX_ . 'customization_field_lang cfl ON cd.`index` = cfl.id_customization_field AND cfl.id_lang = ' . $langId . '
                        GROUP BY id_customization, id_shop
                        ) tmp ON c.id_customization = tmp.id_customization
                        GROUP BY id_cart, id_product, id_product_attribute, id_shop) attrib_customs ON 
                            `order`.id_cart = attrib_customs.id_cart
                            AND product.product_id = attrib_customs.id_product
                            AND `order`.id_shop = attrib_customs.id_shop
                            AND attrib_customs.id_product_attribute = product.product_attribute_id ';
        }
        $this->sql .= ' 
            WHERE 1
            ';

        // Sort By ...
        $this->sql .= $this->mutualSql . '
            ORDER BY ' . $this->sort;
        if ($this->sortAsc === 0) {
            $this->sql .= ' DESC';
        }
        if ($this->sort !== 'order.id_order') {
            $this->sql .= ', order.id_order DESC';
        }

//        die($this->sql);
        return Db::getInstance()->executeS($this->sql);
    }

    private function getGroups()
    {
        $mergeSql = '
                SELECT 
                    order.id_order,
                    IF(COUNT(product.id_order_detail)=0,1,COUNT(product.id_order_detail)) products
                ' . $this->helperSql . ' 
                    WHERE 1 ';

        if (is_numeric($this->auto)) {
            $mergeSql .= 'AND order.id_order = ' . $this->auto;
        }

        $mergeSql .= $this->mutualSql . '
                    GROUP BY order.id_order
                    ORDER BY ' . $this->sort;
        if ($this->sortAsc === 0) {
            $mergeSql .= ' DESC';
        }
        if ($this->sort !== 'order.id_order') {
            $mergeSql .= ', order.id_order DESC';
        }

//        die($mergeSql);
        return DB::getInstance()->executeS($mergeSql);
    }

    public function generateExcel()
    {
        $this->setMutualSql();

        $autoExportDoNotSend = Configuration::getGlobalValue('OXSRP_AUTOEXP_DNSEM');
        $scheduleDoNotSend = Configuration::getGlobalValue('OXSRP_SCHDL_DNSEM');

        require_once dirname(__FILE__) . '/../vendor/autoload.php';

        $helper = new Sample();
        if ($helper->isCli()) {
            $helper->log('This should only be run from a Web Browser' . PHP_EOL);
            return;
        }

        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        // Set document properties
        $spreadsheet->getProperties()->setCreator('Tehran Alishov')
            ->setLastModifiedBy('Tehran Alishov')
            ->setTitle('Office 2007 XLSX Sales Document')
            ->setSubject('Office 2007 XLSX Sales Document')
            ->setDescription('Orders document for Office 2007 XLSX, generated using PHP classes.')
            ->setKeywords('office 2007 openxml php')
            ->setCategory('Orders result file');

        $this->setHelperSql();

        if ($this->displayMainSales) {

            $orders = $this->getOrders();

            if (is_numeric($this->auto) && !$orders && $autoExportDoNotSend) {
                return 0;
            }

            if ($this->auto === 'schedule' && !$orders && $scheduleDoNotSend) {
                return 0;
            }

            $sheet = $spreadsheet->getActiveSheet();

            $excelColumns = array();
            //        d($orders);
            if (!empty($orders)) {
                $counter = count($orders[0]);
                if ($this->orderId) {
                    $counter--;
                }
                if ($this->productId) {
                    $counter--;
                }
                if ($this->attributeId) {
                    $counter--;
                }
                if ($this->shopId) {
                    $counter--;
                }
                if ($this->categoryId) {
                    $counter--;
                }
                if ($this->manufacturerId) {
                    $counter--;
                }
                if ($this->supplierId) {
                    $counter--;
                }
                if ($this->productRewriteLink) {
                    $counter--;
                }
                if ($this->categoryRewriteLink) {
                    $counter--;
                }
                if ($this->currencyIsoCode) {
                    $counter--;
                }
                if ($this->currencyConversionRate) {
                    $counter--;
                }
                if ($this->totalProducts) {
                    $counter--;
                }
                if ($this->productQuantity) {
                    $counter--;
                }
                if ($this->totalPriceTaxExcl) {
                    $counter--;
                }
                if ($this->totalDiscountsTaxExcl) {
                    $counter--;
                }
//            if ($this->purchaseSupplierPrice) {
//                $counter--;
//            }

                $headers = array_keys($orders[0]);
                $psp = 0;
                if ($this->purchaseSupplierPrice) {
                    $psp++;
                }

                $excelColumns = SalesExportHelper::createColumnsArray($counter - $psp);
                $sheet->getDefaultColumnDimension()->setWidth(21);

                if (isset($this->productImage) ||
                    isset($this->attributeImage) ||
                    isset($this->categoryImage) ||
                    isset($this->manufacturerImage) ||
                    isset($this->supplierImage)) {
                    $sheet->getDefaultRowDimension()->setRowHeight(42);
                } else {
                    $sheet->getDefaultRowDimension()->setRowHeight(30);
                }

                $sheet->getStyle('A1:' . end($excelColumns) . (count($orders) + 10))
                    ->getAlignment()->setWrapText(true);

                if ($this->displayHeader) {
                    $sheet->getStyle('A1:' . end($excelColumns) . '1')
                        ->getFont()->setBold(true);
                    $sheet->getStyle('A1:' . end($excelColumns) . '1')
                        ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFDCF0FF');
                    $sheet->getStyle('A1:' . end($excelColumns) . '1')->getBorders()
                        ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                    for ($i = 0; $i < $counter - $psp; $i++) {
                        $sheet->setCellValue($excelColumns[$i] . '1', $headers[$i]);
                    }
                }
                // Rename worksheet
                $sheet->setTitle($this->module->l('Sales', 'ExportSales'));
            } else {
                $sheet->setTitle($this->module->l('Sales', 'ExportSales'));
                $sheet->setCellValue('A1', $this->module->l('No Data', 'ExportSales'));
//            $sheet->setCellValue('A4', $this->module->l('Date', 'ExportSales') . ': ')
//                ->getStyle('A4')->getFont()->setBold(true);
//            $sheet->setCellValue('B4', date('Y-m-d H:i:s'));
            }


            if ($excelColumns) {
                $font = $sheet->getStyle('A1')->getFont();

                if (isset($this->selectedColumns->order->id_order)) {
                    $groupCount = $groupTotal = 0;
                    $groupOrder = $orders[0][$this->selectedColumns->order->id_order];
                }

                if ($this->selectedColumns->order->profit_amount ||
                    $this->selectedColumns->order->profit_margin ||
                    $this->selectedColumns->order->profit_percentage) {
                    $profits = true;
                } else {
                    $profits = false;
                }

                if ($this->selectedColumns->order->net_profit_amount ||
                    $this->selectedColumns->order->net_profit_margin ||
                    $this->selectedColumns->order->net_profit_percentage) {
                    $netProfits = true;
                } else {
                    $netProfits = false;
                }

                $totals = array();
                $purchase = $sale = $netSale = 0;
                $reductionTotals = array(
                    'full' => 0,
                    'reduced' => 0
                );
                $empty_rows = 0;
                $groups = $this->getGroups();
                if ($this->ordersMerge === '1') {
                    if (!$this->noProduct) {
                        $header = current(
                            $sheet->rangeToArray(
                                'A1:' . end($excelColumns) . '1', // The worksheet range that we want to retrieve
                                null, // Value that should be returned for empty cells
                                true, // Should formulas be calculated (the equivalent of getCalculatedValue() for each cell)
                                true, // Should values be formatted (the equivalent of getFormattedValue() for each cell)
                                true // Should the array be indexed by cell row and cell column
                            )
                        );
                        $nonProducts = array_diff(
                            $header,
                            array_merge(
                                (array) $this->selectedColumns->product,
                                (array) $this->selectedColumns->category,
                                (array) $this->selectedColumns->manufacturer,
                                (array) $this->selectedColumns->supplier
                            )
                        );
                        foreach (array_keys($nonProducts) as $value) {
                            $last = 2;
                            foreach ($groups as $group) {
                                if ((int) $group['products'] > 1) {
                                    $sheet->mergeCells("$value$last:$value" . ($last + (int) $group['products'] - 1));
                                    $last += (int) $group['products'];
                                } else {
                                    ++$last;
                                }
                            }
                        }
                    }

                    $j = 0;
                    foreach ($groups as $gk => $group) {
                        for ($i = 0; $i < (int) $group['products']; ++$i) {
                            if ($this->noProduct && $i !== 0) {
                                if ($profits || $netProfits) {
                                    $groupTotal += $orders[$j][$this->selectedColumns->product->purchase_supplier_price] * $orders[$j][$this->selectedColumns->product->product_quantity];
                                    if ($this->displayTotals === '1') {
                                        $purchase += (float) $orders[$j][$this->selectedColumns->product->purchase_supplier_price] * $orders[$j][$this->selectedColumns->product->product_quantity] / $orders[$j][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                    }
                                }
                                $j++;
                                $empty_rows++;
                                continue;
                            }
                            $count = 0;
                            foreach ($orders[$j] as $k => $val) {
                                if ($count >= $counter) {
                                    break;
                                }
                                if (in_array($k, (array) $this->selectedColumns->product) ||
                                    in_array($k, (array) $this->selectedColumns->category) ||
                                    in_array($k, (array) $this->selectedColumns->manufacturer) ||
                                    in_array($k, (array) $this->selectedColumns->supplier)) {
                                    if (($val || $val === '0') && in_array($k, $this->moneyColumns)) {
                                        $col = $excelColumns[$count];
                                        $curr = $this->displayCurrSymbol ? $this->curs->{$orders[$j][$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';
                                        $sheet->setCellValue($col . ($j - $empty_rows + 2), $curr . str_replace('.', $this->decimalSeparator, $val));
                                        if ($this->displayTotals === '1') {
                                            if (isset($totals[$col])) {
                                                $totals[$col]['val'] += (float) $val / $orders[$j][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                            } else {
                                                $totals[$col]['val'] = (float) $val / $orders[$j][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                                $totals[$col]['curr'] = (bool) $curr;
                                            }
                                        }
                                    } elseif ($k === $this->selectedColumns->product->purchase_supplier_price) {
                                        if (!$this->purchaseSupplierPrice) {
                                            $col = $excelColumns[$count];
                                            $curr = $this->displayCurrSymbol ? $this->curs->{$orders[$j][$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';
                                            $sheet->setCellValue($col . ($j - $empty_rows + 2), $curr . str_replace('.', $this->decimalSeparator, $val));
                                            if ($this->displayTotals === '1') {
                                                if (isset($totals[$col])) {
                                                    $totals[$col]['val'] += (float) $val * $orders[$j][$this->selectedColumns->product->product_quantity] / $orders[$j][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                                } else {
                                                    $totals[$col]['val'] = (float) $val * $orders[$j][$this->selectedColumns->product->product_quantity] / $orders[$j][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                                    $totals[$col]['curr'] = (bool) $curr;
                                                }
                                            }
                                        }
                                        if ($profits || $netProfits) {
                                            if ($groupOrder !== $orders[$j][$this->selectedColumns->order->id_order]) {
                                                $totalProducts = $orders[$j - 1][$this->selectedColumns->order->total_products];
                                                $totalDiscountsTaxExcl = $orders[$j - 1][$this->selectedColumns->order->total_discounts_tax_excl];
                                                $curr = $this->displayCurrSymbol ? $this->curs->{$orders[$j - 1][$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';
                                                if ($this->selectedColumns->order->profit_amount) {
                                                    $col = $excelColumns[array_search($this->selectedColumns->order->profit_amount, $headers)];
                                                    $profit_amount = $totalProducts - $groupTotal;
                                                    $sheet->setCellValue($col . ($j - $empty_rows + 2 - ($this->noProduct ? 1 : $groups[$gk - 1]['products'])), $curr . str_replace('.', $this->decimalSeparator, (string) round($profit_amount, $this->fracPart)));
                                                }
                                                if ($this->selectedColumns->order->profit_margin) {
                                                    $profit_margin = 100 * ($totalProducts - $groupTotal) / $totalProducts;
                                                    $sheet->setCellValue($excelColumns[array_search($this->selectedColumns->order->profit_margin, $headers)] . ($j - $empty_rows + 2 - ($this->noProduct ? 1 : $groups[$gk - 1]['products'])), str_replace('.', $this->decimalSeparator, (string) round($profit_margin, $this->fracPart)) . '%');
                                                }
                                                if ($this->selectedColumns->order->profit_percentage) {
                                                    $profit_percentage = 100 * ($totalProducts - $groupTotal) / $groupTotal;
                                                    $sheet->setCellValue($excelColumns[array_search($this->selectedColumns->order->profit_percentage, $headers)] . ($j - $empty_rows + 2 - ($this->noProduct ? 1 : $groups[$gk - 1]['products'])), str_replace('.', $this->decimalSeparator, (string) round($profit_percentage, $this->fracPart)) . '%');
                                                }
                                                if ($this->selectedColumns->order->net_profit_amount) {
                                                    $col = $excelColumns[array_search($this->selectedColumns->order->net_profit_amount, $headers)];
                                                    $net_profit_amount = $totalProducts - $totalDiscountsTaxExcl - $groupTotal;
                                                    $sheet->setCellValue($col . ($j - $empty_rows + 2 - ($this->noProduct ? 1 : $groups[$gk - 1]['products'])), $curr . str_replace('.', $this->decimalSeparator, (string) round($net_profit_amount, $this->fracPart)));
                                                }
                                                if ($this->selectedColumns->order->net_profit_margin) {
                                                    $net_profit_margin = 100 * ($totalProducts - $totalDiscountsTaxExcl - $groupTotal) / ($totalProducts - $totalDiscountsTaxExcl);
                                                    $sheet->setCellValue($excelColumns[array_search($this->selectedColumns->order->net_profit_margin, $headers)] . ($j - $empty_rows + 2 - ($this->noProduct ? 1 : $groups[$gk - 1]['products'])), str_replace('.', $this->decimalSeparator, (string) round($net_profit_margin, $this->fracPart)) . '%');
                                                }
                                                if ($this->selectedColumns->order->net_profit_percentage) {
                                                    $net_profit_percentage = 100 * ($totalProducts - $totalDiscountsTaxExcl - $groupTotal) / $groupTotal;
                                                    $sheet->setCellValue($excelColumns[array_search($this->selectedColumns->order->net_profit_percentage, $headers)] . ($j - $empty_rows + 2 - ($this->noProduct ? 1 : $groups[$gk - 1]['products'])), str_replace('.', $this->decimalSeparator, (string) round($net_profit_percentage, $this->fracPart)) . '%');
                                                }
                                                $groupTotal = 0;
                                                $groupOrder = $orders[$j][$this->selectedColumns->order->id_order];
                                                if ($this->displayTotals === '1') {
                                                    if ($profits) {
                                                        $sale += $totalProducts / $orders[$j - 1][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                                    }
                                                    if ($netProfits) {
                                                        $netSale += ($totalProducts - $totalDiscountsTaxExcl)  / $orders[$j - 1][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                                    }
                                                }
                                            }
                                            $groupTotal += $val * $orders[$j][$this->selectedColumns->product->product_quantity];
                                            if ($this->displayTotals === '1') {
                                                $purchase += (float) $val * $orders[$j][$this->selectedColumns->product->product_quantity] / $orders[$j][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                            }
                                        }
                                    } elseif ($k === $this->selectedColumns->product->product_quantity) {
                                        $col = $excelColumns[$count];
                                        $sheet->setCellValue($col . ($j - $empty_rows + 2), $val);
                                        if ($this->displayTotals === '1') {
                                            if (isset($totals[$col])) {
                                                $totals[$col]['val'] +=  $val;
                                            } else {
                                                $totals[$col]['val'] = $val;
                                                $totals[$col]['curr'] = 0;
                                            }
                                        }
                                    } elseif ($k === $this->selectedColumns->product->reduction_percent) {
                                        $reductionPercentCol = $excelColumns[$count];
                                        $sheet->setCellValue($reductionPercentCol . ($j - $empty_rows + 2), str_replace('.', $this->decimalSeparator, $val));
                                        if ($this->displayTotals === '1') {
                                            $reductionTotals['reduced'] += $orders[$j][$this->selectedColumns->product->total_price_tax_excl];
                                            $reductionTotals['full'] += 100 * $orders[$j][$this->selectedColumns->product->total_price_tax_excl] / (100 - $val);
                                        }
                                    } elseif ($k === $this->selectedColumns->product->product_image) {
                                        // Get image data of the given product id
                                        $image = Image::getCover($orders[$j][$this->selectedColumns->product->product_id]);
                                        if ($image) {
                                            $img = new Image($image['id_image']);
                                            $image_path = realpath(_PS_PROD_IMG_DIR_ . $img->getImgPath() . ($this->imageTypeForFile ? '-' . $this->imageTypeForFile : '') . '.jpg');
                                            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                                            // $drawing->setName('Product_' . $value['Product ID']);
                                            $drawing->setPath(realpath($image_path));

                                            $height = \PhpOffice\PhpSpreadsheet\Shared\Drawing::pointsToPixels(42, $font);

                                            $drawing->setHeight($height);

                                            $width = \PhpOffice\PhpSpreadsheet\Shared\Drawing::pixelsToCellDimension($drawing->getWidth(), $font);

                                            $sheet->getColumnDimension($excelColumns[$count])->setWidth($width);

                                            $drawing->setCoordinates($excelColumns[$count] . ($j - $empty_rows + 2));
                                            $drawing->setWorksheet($sheet);
                                            $drawing->getShadow()->setVisible(true);
                                        } else {
                                            $cell = $excelColumns[$count] . ($j - $empty_rows + 2);
                                            $sheet->setCellValue(
                                                $cell,
                                                $this->module->l('No Image', 'ExportSales')
                                            );
                                            $sheet->getStyle($cell)
                                                ->getFont()->setBold(true);
                                        }
                                    } elseif ($k === $this->selectedColumns->product->attribute_image) {
                                        if (method_exists('Image', 'getBestImageAttribute')) {
                                            // Get image data of the given product id
                                            $image = Image::getBestImageAttribute(
                                                $orders[$j][$this->selectedColumns->shop->id_shop],
                                                $this->langId,
                                                $orders[$j][$this->selectedColumns->product->product_id],
                                                $orders[$j][$this->selectedColumns->product->product_attribute_id]
                                            );
                                        } else {
                                            $image = Image::getImages(
                                                $this->langId,
                                                $orders[$j][$this->selectedColumns->product->product_id],
                                                $orders[$j][$this->selectedColumns->product->product_attribute_id]
                                            );
                                            $image = isset($image[0]) ? $image[0] : null;
                                        }
                                        if ($image) {
                                            $img = new Image($image['id_image']);
                                            $image_path = realpath(_PS_PROD_IMG_DIR_ . $img->getImgPath() . ($this->imageTypeForFile ? '-' . $this->imageTypeForFile : '') . '.jpg');
                                            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                                            // $drawing->setName('Product_' . $value['Product ID']);
                                            $drawing->setPath(realpath($image_path));

                                            $height = \PhpOffice\PhpSpreadsheet\Shared\Drawing::pointsToPixels(42, $font);

                                            $drawing->setHeight($height);

                                            $width = \PhpOffice\PhpSpreadsheet\Shared\Drawing::pixelsToCellDimension($drawing->getWidth(), $font);

                                            $sheet->getColumnDimension($excelColumns[$count])->setWidth($width);

                                            $drawing->setCoordinates($excelColumns[$count] . ($j - $empty_rows + 2));
                                            $drawing->setWorksheet($sheet);
                                            $drawing->getShadow()->setVisible(true);
                                        } else {
                                            $cell = $excelColumns[$count] . ($j - $empty_rows + 2);
                                            $sheet->setCellValue(
                                                $cell,
                                                $this->module->l('No Image', 'ExportSales')
                                            );
                                            $sheet->getStyle($cell)
                                                ->getFont()->setBold(true);
                                        }
                                    } elseif ($k === $this->selectedColumns->product->product_link) {
                                        $link = $this->context->link->getProductLink((int) $orders[$j][$this->selectedColumns->product->product_id], null, null, null, $this->langId);
                                        $cell = $excelColumns[$count] . ($j - $empty_rows + 2);
                                        $sheet->setCellValue($cell, $link);
                                        $sheet->getCell($cell)->getHyperlink()->setUrl($link);
                                        $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FF0000FF');
                                    } elseif ($k === $this->selectedColumns->product->product_image_link) {
                                        // Get image data of the given product id
                                        $image = Image::getCover($orders[$j][$this->selectedColumns->product->product_id]);
                                        if ($image) {
                                            $img_link = $this->context->link->getImageLink($orders[$j][$this->selectedColumns->product->{'order_detail_lang.product_link_rewrite'}], $image['id_image'], $this->imageType);
                                            $cell = $excelColumns[$count] . ($j - $empty_rows + 2);
                                            $sheet->setCellValue($cell, $img_link);
                                            $sheet->getCell($cell)->getHyperlink()->setUrl($img_link);
                                            $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FF0000FF');
                                        } else {
                                            $cell = $excelColumns[$count] . ($j - $empty_rows + 2);
                                            $sheet->setCellValue(
                                                $cell,
                                                $this->module->l('No Image Link', 'ExportSales')
                                            );
                                            $sheet->getStyle($cell)->getFont()->setBold(true);
                                        }
                                    } elseif ($k === $this->selectedColumns->product->attribute_image_link) {
                                        if (method_exists('Image', 'getBestImageAttribute')) {
                                            // Get image data of the given product id
                                            $image = Image::getBestImageAttribute(
                                                $orders[$j][$this->selectedColumns->shop->id_shop],
                                                $this->langId,
                                                $orders[$j][$this->selectedColumns->product->product_id],
                                                $orders[$j][$this->selectedColumns->product->product_attribute_id]
                                            );
                                        } else {
                                            $image = Image::getImages(
                                                $this->langId,
                                                $orders[$j][$this->selectedColumns->product->product_id],
                                                $orders[$j][$this->selectedColumns->product->product_attribute_id]
                                            );
                                            $image = isset($image[0]) ? $image[0] : null;
                                        }
                                        if ($image) {
                                            $img_link = $this->context->link->getImageLink($orders[$j][$this->selectedColumns->product->{'order_detail_lang.product_link_rewrite'}], $image['id_image'], $this->imageType);
                                            $cell = $excelColumns[$count] . ($j - $empty_rows + 2);
                                            $sheet->setCellValue($cell, $img_link);
                                            $sheet->getCell($cell)->getHyperlink()->setUrl($img_link);
                                            $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FF0000FF');
                                        } else {
                                            $cell = $excelColumns[$count] . ($j - $empty_rows + 2);
                                            $sheet->setCellValue(
                                                $cell,
                                                $this->module->l('No Image Link', 'ExportSales')
                                            );
                                            $sheet->getStyle($cell)->getFont()->setBold(true);
                                        }
                                    } elseif ($k === $this->selectedColumns->category->category_image) {
                                        $cat_img_path = realpath(_PS_CAT_IMG_DIR_ . $orders[$j][$this->selectedColumns->category->id_category] . ($this->catImageTypeForFile ? '-' . $this->catImageTypeForFile : '') . '.jpg');
                                        if (file_exists($cat_img_path)) {
                                            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                                            $drawing->setPath(realpath($cat_img_path));

                                            $height = \PhpOffice\PhpSpreadsheet\Shared\Drawing::pointsToPixels(42, $font);

                                            $drawing->setHeight($height);

                                            $width = \PhpOffice\PhpSpreadsheet\Shared\Drawing::pixelsToCellDimension($drawing->getWidth(), $font);

                                            $sheet->getColumnDimension($excelColumns[$count])->setWidth($width);

                                            $drawing->setCoordinates($excelColumns[$count] . ($j - $empty_rows + 2));
                                            $drawing->setWorksheet($sheet);
                                            $drawing->getShadow()->setVisible(true);
                                        } else {
                                            $cell = $excelColumns[$count] . ($j - $empty_rows + 2);
                                            $sheet->setCellValue(
                                                $cell,
                                                $this->module->l('No Image', 'ExportSales')
                                            );
                                            $sheet->getStyle($cell)->getFont()->setBold(true);
                                        }
                                    } elseif ($k === $this->selectedColumns->category->category_link) {
                                        if ((int) $orders[$j][$this->selectedColumns->category->id_category]) {
                                            $link = $this->context->link->getCategoryLink((int) $orders[$j][$this->selectedColumns->category->id_category], null, $this->langId);
                                            $cell = $excelColumns[$count] . ($j - $empty_rows + 2);
                                            $sheet->setCellValue($cell, $link);
                                            $sheet->getCell($cell)->getHyperlink()->setUrl($link);
                                            $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FF0000FF');
                                        }
                                    } elseif ($k === $this->selectedColumns->category->category_image_link) {
                                        $cat_img_path = realpath(_PS_CAT_IMG_DIR_ . $orders[$j][$this->selectedColumns->category->id_category] . ($this->catImageType ? '-' . $this->catImageType : '') . '.jpg');
                                        if (file_exists($cat_img_path)) {
                                            if (method_exists($this->context->link, 'getCatImageLink')) {
                                                // Get image data of the given product id
                                                $cat_img_link = $this->context->link->getCatImageLink(
                                                    $orders[$j][$this->selectedColumns->category->link_rewrite],
                                                    $orders[$j][$this->selectedColumns->category->id_category],
                                                    $this->imageType
                                                );
                                            } else {
                                                $cat_img_link = $this->context->link->getBaseLink() . 'c/'
                                                    . $orders[$j][$this->selectedColumns->category->id_category] . ($this->imageType ? '-' . $this->imageType : '') . '/'
                                                    . $orders[$j][$this->selectedColumns->category->link_rewrite] . '.jpg';
                                            }
                                            $cell = $excelColumns[$count] . ($j - $empty_rows + 2);
                                            $sheet->setCellValue($cell, $cat_img_link);
                                            $sheet->getCell($cell)->getHyperlink()->setUrl($cat_img_link);
                                            $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FF0000FF');
                                        } else {
                                            $cell = $excelColumns[$count] . ($j - $empty_rows + 2);
                                            $sheet->setCellValue(
                                                $cell,
                                                $this->module->l('No Image Link', 'ExportSales')
                                            );
                                            $sheet->getStyle($cell)->getFont()->setBold(true);
                                        }
                                    } elseif ($k === $this->selectedColumns->manufacturer->manufacturer_link) {
                                        if ((int) $orders[$j][$this->selectedColumns->manufacturer->id_manufacturer]) {
                                            $link = $this->context->link->getManufacturerLink((int) $orders[$j][$this->selectedColumns->manufacturer->id_manufacturer], null, $this->langId);
                                            $cell = $excelColumns[$count] . ($j - $empty_rows + 2);
                                            $sheet->setCellValue($cell, $link);
                                            $sheet->getCell($cell)->getHyperlink()->setUrl($link);
                                            $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FF0000FF');
                                        }
                                    } elseif ($k === $this->selectedColumns->manufacturer->manufacturer_image) {
                                        $man_img_path = realpath(_PS_MANU_IMG_DIR_ . $orders[$j][$this->selectedColumns->manufacturer->id_manufacturer] . ($this->imageTypeForFile ? '-' . $this->imageTypeForFile : '') . '.jpg');
                                        if (file_exists($man_img_path)) {
                                            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                                            $drawing->setPath(realpath($man_img_path));

                                            $height = \PhpOffice\PhpSpreadsheet\Shared\Drawing::pointsToPixels(42, $font);

                                            $drawing->setHeight($height);

                                            $width = \PhpOffice\PhpSpreadsheet\Shared\Drawing::pixelsToCellDimension($drawing->getWidth(), $font);

                                            $sheet->getColumnDimension($excelColumns[$count])->setWidth($width);

                                            $drawing->setCoordinates($excelColumns[$count] . ($j - $empty_rows + 2));
                                            $drawing->setWorksheet($sheet);
                                            $drawing->getShadow()->setVisible(true);
                                        } else {
                                            $cell = $excelColumns[$count] . ($j - $empty_rows + 2);
                                            $sheet->setCellValue(
                                                $cell,
                                                $this->module->l('No Image', 'ExportSales')
                                            );
                                            $sheet->getStyle($cell)->getFont()->setBold(true);
                                        }
                                    } elseif ($k === $this->selectedColumns->manufacturer->manufacturer_image_link) {
                                        $man_img_path = realpath(_PS_MANU_IMG_DIR_ . $orders[$j][$this->selectedColumns->manufacturer->id_manufacturer] . ($this->imageType ? '-' . $this->imageType : '') . '.jpg');
                                        if (file_exists($man_img_path)) {
                                            $man_img_link = $this->context->link->getBaseLink() . 'img/m/'
                                                . $orders[$j][$this->selectedColumns->manufacturer->id_manufacturer] . ($this->imageType ? '-' . $this->imageType : '') . '.jpg';
                                            $cell = $excelColumns[$count] . ($j - $empty_rows + 2);
                                            $sheet->setCellValue($cell, $man_img_link);
                                            $sheet->getCell($cell)->getHyperlink()->setUrl($man_img_link);
                                            $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FF0000FF');
                                        } else {
                                            $cell = $excelColumns[$count] . ($j - $empty_rows + 2);
                                            $sheet->setCellValue(
                                                $cell,
                                                $this->module->l('No Image Link', 'ExportSales')
                                            );
                                            $sheet->getStyle($cell)->getFont()->setBold(true);
                                        }
                                    } elseif ($k === $this->selectedColumns->supplier->supplier_link) {
                                        if ((int) $orders[$j][$this->selectedColumns->supplier->id_supplier]) {
                                            $link = $this->context->link->getSupplierLink((int) $orders[$j][$this->selectedColumns->supplier->id_supplier], null, $this->langId);
                                            $cell = $excelColumns[$count] . ($j - $empty_rows + 2);
                                            $sheet->setCellValue($cell, $link);
                                            $sheet->getCell($cell)->getHyperlink()->setUrl($link);
                                            $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FF0000FF');
                                        }
                                    } elseif ($k === $this->selectedColumns->supplier->supplier_image) {
                                        $supp_img_path = realpath(_PS_SUPP_IMG_DIR_ . $orders[$j][$this->selectedColumns->supplier->id_supplier] . ($this->imageTypeForFile ? '-' . $this->imageTypeForFile : '') . '.jpg');
                                        if (file_exists($supp_img_path)) {
                                            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                                            $drawing->setPath(realpath($supp_img_path));

                                            $height = \PhpOffice\PhpSpreadsheet\Shared\Drawing::pointsToPixels(42, $font);

                                            $drawing->setHeight($height);

                                            $width = \PhpOffice\PhpSpreadsheet\Shared\Drawing::pixelsToCellDimension($drawing->getWidth(), $font);

                                            $sheet->getColumnDimension($excelColumns[$count])->setWidth($width);

                                            $drawing->setCoordinates($excelColumns[$count] . ($j - $empty_rows + 2));
                                            $drawing->setWorksheet($sheet);
                                            $drawing->getShadow()->setVisible(true);
                                        } else {
                                            $cell = $excelColumns[$count] . ($j - $empty_rows + 2);
                                            $sheet->setCellValue(
                                                $cell,
                                                $this->module->l('No Image', 'ExportSales')
                                            );
                                            $sheet->getStyle($cell)->getFont()->setBold(true);
                                        }
                                    } elseif ($k === $this->selectedColumns->supplier->supplier_image_link) {
                                        $supp_img_path = realpath(_PS_SUPP_IMG_DIR_ . $orders[$j][$this->selectedColumns->supplier->id_supplier] . ($this->imageType ? '-' . $this->imageType : '') . '.jpg');
                                        if (file_exists($supp_img_path)) {
                                            $supp_img_link = $this->context->link->getBaseLink() . 'img/su/'
                                                . $orders[$j][$this->selectedColumns->supplier->id_supplier] . ($this->imageType ? '-' . $this->imageType : '') . '.jpg';
                                            $cell = $excelColumns[$count] . ($j - $empty_rows + 2);
                                            $sheet->setCellValue($cell, $supp_img_link);
                                            $sheet->getCell($cell)->getHyperlink()->setUrl($supp_img_link);
                                            $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FF0000FF');
                                        } else {
                                            $cell = $excelColumns[$count] . ($j - $empty_rows + 2);
                                            $sheet->setCellValue(
                                                $cell,
                                                $this->module->l('No Image Link', 'ExportSales')
                                            );
                                            $sheet->getStyle($cell)->getFont()->setBold(true);
                                        }
                                    } else {
                                        $col = $excelColumns[$count];
                                        $sheet->setCellValue($col . ($j - $empty_rows + 2), $val);
                                        if (Tools::strlen($val) <= 8) {
                                            $sheet->getColumnDimension($col)->setWidth(15);
                                        }
                                    }
                                } elseif ($i === 0) {
                                    if (($val || $val === '0') && in_array($k, $this->moneyColumns)) {
                                        $col = $excelColumns[$count];
                                        $curr = $this->displayCurrSymbol ? $this->curs->{$orders[$j][$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';
                                        $sheet->setCellValue($col . ($j - $empty_rows + 2), $curr . str_replace('.', $this->decimalSeparator, $val));
                                        if ($this->displayTotals === '1') {
                                            if (isset($totals[$col])) {
                                                $totals[$col]['val'] += (float) $val / $orders[$j][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                            } else {
                                                $totals[$col]['val'] = (float) $val / $orders[$j][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                                $totals[$col]['curr'] = (bool) $curr;
                                            }
                                        }
                                    } else {
                                        if (($profits || $netProfits) &&
                                            $groupOrder !== $orders[$j][$this->selectedColumns->order->id_order] && (
                                                $k === $this->selectedColumns->order->profit_amount ||
                                                $k === $this->selectedColumns->order->profit_margin ||
                                                $k === $this->selectedColumns->order->profit_percentage ||
                                                $k === $this->selectedColumns->order->net_profit_amount ||
                                                $k === $this->selectedColumns->order->net_profit_margin ||
                                                $k === $this->selectedColumns->order->net_profit_percentage
                                            )) {
                                            $curr = $this->displayCurrSymbol ? $this->curs->{$orders[$j - 1][$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';
                                            $totalProducts = $orders[$j - 1][$this->selectedColumns->order->total_products];
                                            $totalDiscountsTaxExcl = $orders[$j - 1][$this->selectedColumns->order->total_discounts_tax_excl];
                                            if ($this->selectedColumns->order->profit_amount) {
                                                $col = $excelColumns[array_search($this->selectedColumns->order->profit_amount, $headers)];
                                                $profit_amount = $totalProducts - $groupTotal;
                                                $sheet->setCellValue($col . ($j - $empty_rows + 2 - ($this->noProduct ? 1 : $groups[$gk - 1]['products'])), $curr . str_replace('.', $this->decimalSeparator, (string) round($profit_amount, $this->fracPart)));
                                            }
                                            if ($this->selectedColumns->order->profit_margin) {
                                                $profit_margin = 100 * ($totalProducts - $groupTotal) / $totalProducts;
                                                $sheet->setCellValue($excelColumns[array_search($this->selectedColumns->order->profit_margin, $headers)] . ($j - $empty_rows + 2 - ($this->noProduct ? 1 : $groups[$gk - 1]['products'])), str_replace('.', $this->decimalSeparator, (string) round($profit_margin, $this->fracPart)) . '%');
                                            }
                                            if ($this->selectedColumns->order->profit_percentage) {
                                                $profit_percentage = 100 * ($totalProducts - $groupTotal) / $groupTotal;
                                                $sheet->setCellValue($excelColumns[array_search($this->selectedColumns->order->profit_percentage, $headers)] . ($j - $empty_rows + 2 - ($this->noProduct ? 1 : $groups[$gk - 1]['products'])), str_replace('.', $this->decimalSeparator, (string) round($profit_percentage, $this->fracPart)) . '%');
                                            }
                                            if ($this->selectedColumns->order->net_profit_amount) {
                                                $col = $excelColumns[array_search($this->selectedColumns->order->net_profit_amount, $headers)];
                                                $net_profit_amount = $totalProducts - $totalDiscountsTaxExcl - $groupTotal;
                                                $sheet->setCellValue($col . ($j - $empty_rows + 2 - ($this->noProduct ? 1 : $groups[$gk - 1]['products'])), $curr . str_replace('.', $this->decimalSeparator, (string) round($net_profit_amount, $this->fracPart)));
                                            }
                                            if ($this->selectedColumns->order->net_profit_margin) {
                                                $net_profit_margin = 100 * ($totalProducts - $totalDiscountsTaxExcl - $groupTotal) / ($totalProducts - $totalDiscountsTaxExcl);
                                                $sheet->setCellValue($excelColumns[array_search($this->selectedColumns->order->net_profit_margin, $headers)] . ($j - $empty_rows + 2 - ($this->noProduct ? 1 : $groups[$gk - 1]['products'])), str_replace('.', $this->decimalSeparator, (string) round($net_profit_margin, $this->fracPart)) . '%');
                                            }
                                            if ($this->selectedColumns->order->net_profit_percentage) {
                                                $net_profit_percentage = 100 * ($totalProducts - $totalDiscountsTaxExcl - $groupTotal) / $groupTotal;
                                                $sheet->setCellValue($excelColumns[array_search($this->selectedColumns->order->net_profit_percentage, $headers)] . ($j - $empty_rows + 2 - ($this->noProduct ? 1 : $groups[$gk - 1]['products'])), str_replace('.', $this->decimalSeparator, (string) round($net_profit_percentage, $this->fracPart)) . '%');
                                            }
                                            $groupTotal = 0;
                                            $groupOrder = $orders[$j][$this->selectedColumns->order->id_order];
                                            if ($this->displayTotals === '1') {
                                                if ($profits) {
                                                    $sale += $totalProducts / $orders[$j - 1][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                                }
                                                if ($netProfits) {
                                                    $netSale += ($totalProducts - $totalDiscountsTaxExcl) / $orders[$j - 1][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                                }
                                            }
                                        } else {
                                            if ($k === $this->selectedColumns->order->{'order_messages.message'}) {
                                                $val = html_entity_decode($val);
                                            }
                                            $col = $excelColumns[$count];
                                            $sheet->setCellValue($col . ($j - $empty_rows + 2), $val);
                                            if (Tools::strlen($val) <= 8) {
                                                $sheet->getColumnDimension($col)->setWidth(15);
                                            }
                                        }
                                    }
                                }
                                $count++;
                            }
                            ++$j;
                        }
                    }
                    $key = $j - 1;
                    if ($profits || $netProfits) {
                        $curr = $this->displayCurrSymbol ? $this->curs->{$orders[$key][$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';
                        $totalProducts = $orders[$key][$this->selectedColumns->order->total_products];
                        $totalDiscountsTaxExcl = $orders[$key][$this->selectedColumns->order->total_discounts_tax_excl];

                        if (isset($this->selectedColumns->order->profit_amount)) {
                            $profit_amount = $totalProducts - $groupTotal;
                            $profitAmountCol = $excelColumns[array_search($this->selectedColumns->order->profit_amount, $headers)];
                            $sheet->setCellValue($profitAmountCol . ($j - $empty_rows + 2 - ($this->noProduct ? 1 : $group['products'])), $curr . str_replace('.', $this->decimalSeparator, (string) round($profit_amount, $this->fracPart)));
                        }
                        if (isset($this->selectedColumns->order->profit_margin)) {
                            $profit_margin = 100 * ($totalProducts - $groupTotal) / $totalProducts;
                            $profitMarginCol = $excelColumns[array_search($this->selectedColumns->order->profit_margin, $headers)];
                            $sheet->setCellValue($profitMarginCol . ($j - $empty_rows + 2 - ($this->noProduct ? 1 : $group['products'])), str_replace('.', $this->decimalSeparator, (string) round($profit_margin, $this->fracPart)) . '%');
                        }
                        if (isset($this->selectedColumns->order->profit_percentage)) {
                            $profit_percentage = 100 * ($totalProducts - $groupTotal) / $groupTotal;
                            $profitPercentageCol = $excelColumns[array_search($this->selectedColumns->order->profit_percentage, $headers)];
                            $sheet->setCellValue($profitPercentageCol . ($j - $empty_rows + 2 - ($this->noProduct ? 1 : $group['products'])), str_replace('.', $this->decimalSeparator, (string) round($profit_percentage, $this->fracPart)) . '%');
                        }
                        if (isset($this->selectedColumns->order->net_profit_amount)) {
                            $net_profit_amount = $totalProducts - $totalDiscountsTaxExcl - $groupTotal;
                            $netProfitAmountCol = $excelColumns[array_search($this->selectedColumns->order->net_profit_amount, $headers)];
                            $sheet->setCellValue($netProfitAmountCol . ($j - $empty_rows + 2 - ($this->noProduct ? 1 : $group['products'])), $curr . str_replace('.', $this->decimalSeparator, (string) round($net_profit_amount, $this->fracPart)));
                        }
                        if (isset($this->selectedColumns->order->net_profit_margin)) {
                            $net_profit_margin = 100 * ($totalProducts - $totalDiscountsTaxExcl - $groupTotal) / ($totalProducts - $totalDiscountsTaxExcl);
                            $netProfitMarginCol = $excelColumns[array_search($this->selectedColumns->order->net_profit_margin, $headers)];
                            $sheet->setCellValue($netProfitMarginCol . ($j - $empty_rows + 2 - ($this->noProduct ? 1 : $group['products'])), str_replace('.', $this->decimalSeparator, (string) round($net_profit_margin, $this->fracPart)) . '%');
                        }
                        if (isset($this->selectedColumns->order->net_profit_percentage)) {
                            $net_profit_percentage = 100 * ($totalProducts - $totalDiscountsTaxExcl - $groupTotal) / $groupTotal;
                            $netProfitPercentageCol = $excelColumns[array_search($this->selectedColumns->order->net_profit_percentage, $headers)];
                            $sheet->setCellValue($netProfitPercentageCol . ($j - $empty_rows + 2 - ($this->noProduct ? 1 : $group['products'])), str_replace('.', $this->decimalSeparator, (string) round($net_profit_percentage, $this->fracPart)) . '%');
                        }
                        if ($this->displayTotals === '1') {
                            if ($profits) {
                                $sale += $totalProducts / $orders[$key][$this->selectedColumns->order->{'currency.conversion_rate'}];
                            }
                            if ($netProfits) {
                                $netSale += ($totalProducts - $totalDiscountsTaxExcl) / $orders[$key][$this->selectedColumns->order->{'currency.conversion_rate'}];
                            }
                        }
                    }
                } else {
                    if ($this->noProduct) {
                        $break_points = $this->getBreakPoints($groups);
                    }
                    foreach ($orders as $key => $value) {
                        if ($this->noProduct && !in_array($key, $break_points)) {
                            if ($profits || $netProfits) {
                                $groupTotal += $value[$this->selectedColumns->product->purchase_supplier_price] * $value[$this->selectedColumns->product->product_quantity];
                                if ($this->displayTotals === '1') {
                                    $purchase += (float) $value[$this->selectedColumns->product->purchase_supplier_price] * $value[$this->selectedColumns->product->product_quantity] / $value[$this->selectedColumns->order->{'currency.conversion_rate'}];
                                }
                            }
                            $empty_rows++;
                            continue;
                        }
                        $i = 0;
                        $totaler = true;
                        foreach ($value as $k => $val) {
                            if ($i >= $counter) {
                                break;
                            }
                            if (($val || $val === '0') && in_array($k, $this->moneyColumns)) {
                                $col = $excelColumns[$i];
                                $curr = $this->displayCurrSymbol ? $this->curs->{$value[$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';
                                $sheet->setCellValue($col . ($key - $empty_rows + 2), $curr . str_replace('.', $this->decimalSeparator, $val));
                                if ($this->displayTotals === '1') {
                                    if (!isset($totals[$col])) {
                                        $totals[$col]['val'] = (float) $val / $value[$this->selectedColumns->order->{'currency.conversion_rate'}];
                                        $totals[$col]['curr'] = (bool) $curr;
                                        $groupOrder2 = $groupOrder3 = $value[$this->selectedColumns->order->id_order];
                                    } else {
                                        if (in_array($k, $this->orderMoneyColumns)) {
                                            if ($groupOrder2 !== $value[$this->selectedColumns->order->id_order]) {
                                                if ($totaler) {
                                                    if ($groupOrder3 !== $value[$this->selectedColumns->order->id_order]) {
                                                        $totals[$col]['val'] += (float) $val / $value[$this->selectedColumns->order->{'currency.conversion_rate'}];
                                                        $groupOrder3 = $value[$this->selectedColumns->order->id_order];
                                                    } else {
                                                        $groupOrder2 = $value[$this->selectedColumns->order->id_order];
                                                    }
                                                    $totaler = false;
                                                } else {
                                                    $totals[$col]['val'] += (float) $val / $value[$this->selectedColumns->order->{'currency.conversion_rate'}];
                                                }
                                            }
                                        } else {
                                            $totals[$col]['val'] += (float) $val / $value[$this->selectedColumns->order->{'currency.conversion_rate'}];
                                        }
                                    }
                                }
                            } elseif (($profits || $netProfits) &&
                                $value[$this->selectedColumns->order->id_order] !== $groupOrder &&
                                ($k === $this->selectedColumns->order->profit_amount ||
                                    $k === $this->selectedColumns->order->profit_margin ||
                                    $k === $this->selectedColumns->order->profit_percentage ||
                                    $k === $this->selectedColumns->order->net_profit_amount ||
                                    $k === $this->selectedColumns->order->net_profit_margin ||
                                    $k === $this->selectedColumns->order->net_profit_percentage)) {
                                $totalProducts = $orders[$key - 1][$this->selectedColumns->order->total_products];
                                $totalDiscountsTaxExcl = $orders[$key - 1][$this->selectedColumns->order->total_discounts_tax_excl];
                                $curr = $this->displayCurrSymbol ? $this->curs->{$orders[$key - 1][$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';
                                if ($this->selectedColumns->order->profit_amount) {
                                    $profit_amount = $totalProducts - $groupTotal;
                                    for ($l = 0; $l < $groupCount; $l++) {
                                        $sheet->setCellValue($excelColumns[array_search($this->selectedColumns->order->profit_amount, $headers)] . ($key - $empty_rows + 1 - $l), $curr . str_replace('.', $this->decimalSeparator, (string) round($profit_amount, $this->fracPart)));
                                    }
                                }
                                if ($this->selectedColumns->order->profit_margin) {
                                    $profit_margin = 100 * ($totalProducts - $groupTotal) / $totalProducts;
                                    for ($l = 0; $l < $groupCount; $l++) {
                                        $sheet->setCellValue($excelColumns[array_search($this->selectedColumns->order->profit_margin, $headers)] . ($key - $empty_rows + 1 - $l), str_replace('.', $this->decimalSeparator, (string) round($profit_margin, $this->fracPart)) . '%');
                                    }
                                }
                                if ($this->selectedColumns->order->profit_percentage) {
                                    $profit_percentage = 100 * ($totalProducts - $groupTotal) / $groupTotal;
                                    for ($l = 0; $l < $groupCount; $l++) {
                                        $sheet->setCellValue($excelColumns[array_search($this->selectedColumns->order->profit_percentage, $headers)] . ($key - $empty_rows + 1 - $l), str_replace('.', $this->decimalSeparator, (string) round($profit_percentage, $this->fracPart)) . '%');
                                    }
                                }
                                if ($this->selectedColumns->order->net_profit_amount) {
                                    $net_profit_amount = $totalProducts - $totalDiscountsTaxExcl - $groupTotal;
                                    for ($l = 0; $l < $groupCount; $l++) {
                                        $sheet->setCellValue($excelColumns[array_search($this->selectedColumns->order->net_profit_amount, $headers)] . ($key - $empty_rows + 1 - $l), $curr . str_replace('.', $this->decimalSeparator, (string) round($net_profit_amount, $this->fracPart)));
                                    }
                                }
                                if ($this->selectedColumns->order->net_profit_margin) {
                                    $net_profit_margin = 100 * ($totalProducts - $totalDiscountsTaxExcl - $groupTotal) / ($totalProducts - $totalDiscountsTaxExcl);
                                    for ($l = 0; $l < $groupCount; $l++) {
                                        $sheet->setCellValue($excelColumns[array_search($this->selectedColumns->order->net_profit_margin, $headers)] . ($key - $empty_rows + 1 - $l), str_replace('.', $this->decimalSeparator, (string) round($net_profit_margin, $this->fracPart)) . '%');
                                    }
                                }
                                if ($this->selectedColumns->order->net_profit_percentage) {
                                    $net_profit_percentage = 100 * ($totalProducts - $totalDiscountsTaxExcl - $groupTotal) / $groupTotal;
                                    for ($l = 0; $l < $groupCount; $l++) {
                                        $sheet->setCellValue($excelColumns[array_search($this->selectedColumns->order->net_profit_percentage, $headers)] . ($key - $empty_rows + 1 - $l), str_replace('.', $this->decimalSeparator, (string) round($net_profit_percentage, $this->fracPart)) . '%');
                                    }
                                }
                                $groupTotal = $groupCount = 0;
                                $groupOrder = $value[$this->selectedColumns->order->id_order];
                                if ($this->displayTotals === '1') {
                                    if ($profits) {
                                        $sale += $totalProducts / $orders[$key - 1][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                    }
                                    if ($netProfits) {
                                        $netSale += ($totalProducts - $totalDiscountsTaxExcl) / $orders[$key - 1][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                    }
                                }
                            } elseif ($k === $this->selectedColumns->product->purchase_supplier_price) {
                                if (!$this->purchaseSupplierPrice) {
                                    $col = $excelColumns[$i];
                                    $curr = $this->displayCurrSymbol ? $this->curs->{$value[$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';
                                    $sheet->setCellValue($col . ($key - $empty_rows + 2), $curr . str_replace('.', $this->decimalSeparator, $val));
                                    if ($this->displayTotals === '1') {
                                        if (isset($totals[$col])) {
                                            $totals[$col]['val'] += (float) $val * $value[$this->selectedColumns->product->product_quantity] / $value[$this->selectedColumns->order->{'currency.conversion_rate'}];
                                        } else {
                                            $totals[$col]['val'] = (float) $val * $value[$this->selectedColumns->product->product_quantity] / $value[$this->selectedColumns->order->{'currency.conversion_rate'}];
                                            $totals[$col]['curr'] = (bool) $curr;
                                        }
                                    }
                                }
                                if ($profits || $netProfits) {
                                    if ($groupOrder !== $value[$this->selectedColumns->order->id_order]) {
                                        $totalProducts = $orders[$key - 1][$this->selectedColumns->order->total_products];
                                        $totalDiscountsTaxExcl = $orders[$key - 1][$this->selectedColumns->order->total_discounts_tax_excl];
                                        $curr = $this->displayCurrSymbol ? $this->curs->{$orders[$key - 1][$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';
                                        if ($this->selectedColumns->order->profit_amount) {
                                            $profit_amount = $totalProducts - $groupTotal;
                                            for ($l = 0; $l < $groupCount; $l++) {
                                                $sheet->setCellValue($excelColumns[array_search($this->selectedColumns->order->profit_amount, $headers)] . ($key - $empty_rows + 1 - $l), $curr . str_replace('.', $this->decimalSeparator, (string) round($profit_amount, $this->fracPart)));
                                            }
                                        }
                                        if ($this->selectedColumns->order->profit_margin) {
                                            $profit_margin = 100 * ($totalProducts - $groupTotal) / $totalProducts;
                                            for ($l = 0; $l < $groupCount; $l++) {
                                                $sheet->setCellValue($excelColumns[array_search($this->selectedColumns->order->profit_margin, $headers)] . ($key - $empty_rows + 1 - $l), str_replace('.', $this->decimalSeparator, (string) round($profit_margin, $this->fracPart)) . '%');
                                            }
                                        }
                                        if ($this->selectedColumns->order->profit_percentage) {
                                            $profit_percentage = 100 * ($totalProducts - $groupTotal) / $groupTotal;
                                            for ($l = 0; $l < $groupCount; $l++) {
                                                $sheet->setCellValue($excelColumns[array_search($this->selectedColumns->order->profit_percentage, $headers)] . ($key - $empty_rows + 1 - $l), str_replace('.', $this->decimalSeparator, (string) round($profit_percentage, $this->fracPart)) . '%');
                                            }
                                        }
                                        if ($this->selectedColumns->order->net_profit_amount) {
                                            $net_profit_amount = $totalProducts - $totalDiscountsTaxExcl - $groupTotal;
                                            for ($l = 0; $l < $groupCount; $l++) {
                                                $sheet->setCellValue($excelColumns[array_search($this->selectedColumns->order->net_profit_amount, $headers)] . ($key - $empty_rows + 1 - $l), $curr . str_replace('.', $this->decimalSeparator, (string) round($net_profit_amount, $this->fracPart)));
                                            }
                                        }
                                        if ($this->selectedColumns->order->net_profit_margin) {
                                            $net_profit_margin = 100 * ($totalProducts - $totalDiscountsTaxExcl - $groupTotal) / ($totalProducts - $totalDiscountsTaxExcl);
                                            for ($l = 0; $l < $groupCount; $l++) {
                                                $sheet->setCellValue($excelColumns[array_search($this->selectedColumns->order->net_profit_margin, $headers)] . ($key - $empty_rows + 1 - $l), str_replace('.', $this->decimalSeparator, (string) round($net_profit_margin, $this->fracPart)) . '%');
                                            }
                                        }
                                        if ($this->selectedColumns->order->net_profit_percentage) {
                                            $net_profit_percentage = 100 * ($totalProducts - $totalDiscountsTaxExcl - $groupTotal) / $groupTotal;
                                            for ($l = 0; $l < $groupCount; $l++) {
                                                $sheet->setCellValue($excelColumns[array_search($this->selectedColumns->order->net_profit_percentage, $headers)] . ($key - $empty_rows + 1 - $l), str_replace('.', $this->decimalSeparator, (string) round($net_profit_percentage, $this->fracPart)) . '%');
                                            }
                                        }
                                        $groupTotal = $groupCount = 0;
                                        $groupOrder = $value[$this->selectedColumns->order->id_order];
                                        if ($this->displayTotals === '1') {
                                            if ($profits) {
                                                $sale += $totalProducts / $orders[$key - 1][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                            }
                                            if ($netProfits) {
                                                $netSale += ($totalProducts - $totalDiscountsTaxExcl) / $orders[$key - 1][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                            }
                                        }
                                    }
                                    $groupTotal += $val * $value[$this->selectedColumns->product->product_quantity];
                                    $groupCount++;
                                    if ($this->displayTotals === '1') {
                                        $purchase += (float) $val * $value[$this->selectedColumns->product->product_quantity] / $value[$this->selectedColumns->order->{'currency.conversion_rate'}];
                                    }
                                }
                            } elseif ($k === $this->selectedColumns->product->product_quantity) {
                                $col = $excelColumns[$i];
                                $sheet->setCellValue($col . ($key - $empty_rows + 2), $val);
                                if ($this->displayTotals === '1') {
                                    if (isset($totals[$col])) {
                                        $totals[$col]['val'] += $val;
                                    } else {
                                        $totals[$col]['val'] = $val;
                                        $totals[$col]['curr'] = 0;
                                    }
                                }
                            } elseif ($k === $this->selectedColumns->product->reduction_percent) {
                                $reductionPercentCol = $excelColumns[$i];
                                $sheet->setCellValue($reductionPercentCol . ($key - $empty_rows + 2), str_replace('.', $this->decimalSeparator, $val));
                                if ($this->displayTotals === '1') {
                                    $reductionTotals['reduced'] += $value[$this->selectedColumns->product->total_price_tax_excl];
                                    $reductionTotals['full'] += 100 * $value[$this->selectedColumns->product->total_price_tax_excl] / (100 - $val);
                                }
                            } elseif ($k === $this->selectedColumns->product->product_image) {
                                // Get image data of the given product id
                                $image = Image::getCover($value[$this->selectedColumns->product->product_id]);
                                if ($image) {
                                    $img = new Image($image['id_image']);
                                    $image_path = realpath(_PS_PROD_IMG_DIR_ . $img->getImgPath() . ($this->imageTypeForFile ? '-' . $this->imageTypeForFile : '') . '.jpg');
                                    $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                                    // $drawing->setName('Product_' . $value['Product ID']);
                                    $drawing->setPath(realpath($image_path));

                                    $height = \PhpOffice\PhpSpreadsheet\Shared\Drawing::pointsToPixels(42, $font);

                                    $drawing->setHeight($height);

                                    $width = \PhpOffice\PhpSpreadsheet\Shared\Drawing::pixelsToCellDimension($drawing->getWidth(), $font);

                                    $sheet->getColumnDimension($excelColumns[$i])->setWidth($width);

                                    $drawing->setCoordinates($excelColumns[$i] . ($key - $empty_rows + 2));
                                    $drawing->setWorksheet($sheet);
                                    $drawing->getShadow()->setVisible(true);
                                } else {
                                    $cell = $excelColumns[$i] . ($key - $empty_rows + 2);
                                    $sheet->setCellValue(
                                        $cell,
                                        $this->module->l('No Image', 'ExportSales')
                                    );
                                    $sheet->getStyle($cell)
                                        ->getFont()->setBold(true);
                                }
                            } elseif ($k === $this->selectedColumns->product->attribute_image) {
                                // Get image data of the given product id
                                if (method_exists('Image', 'getBestImageAttribute')) {
                                    // Get image data of the given product id
                                    $image = Image::getBestImageAttribute(
                                        $value[$this->selectedColumns->shop->id_shop],
                                        $this->langId,
                                        $value[$this->selectedColumns->product->product_id],
                                        $value[$this->selectedColumns->product->product_attribute_id]
                                    );
                                } else {
                                    $image = Image::getImages(
                                        $this->langId,
                                        $value[$this->selectedColumns->product->product_id],
                                        $value[$this->selectedColumns->product->product_attribute_id]
                                    );
                                    $image = isset($image[0]) ? $image[0] : null;
                                }
                                if ($image) {
                                    $img = new Image($image['id_image']);
                                    $image_path = realpath(_PS_PROD_IMG_DIR_ . $img->getImgPath() . ($this->imageTypeForFile ? '-' . $this->imageTypeForFile : '') . '.jpg');
                                    $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                                    // $drawing->setName('Product_' . $value['Product ID']);
                                    $drawing->setPath(realpath($image_path));

                                    $height = \PhpOffice\PhpSpreadsheet\Shared\Drawing::pointsToPixels(42, $font);

                                    $drawing->setHeight($height);

                                    $width = \PhpOffice\PhpSpreadsheet\Shared\Drawing::pixelsToCellDimension($drawing->getWidth(), $font);

                                    $sheet->getColumnDimension($excelColumns[$i])->setWidth($width);

                                    $drawing->setCoordinates($excelColumns[$i] . ($key - $empty_rows + 2));
                                    $drawing->setWorksheet($sheet);
                                    $drawing->getShadow()->setVisible(true);
                                } else {
                                    $cell = $excelColumns[$i] . ($key - $empty_rows + 2);
                                    $sheet->setCellValue(
                                        $cell,
                                        $this->module->l('No Image', 'ExportSales')
                                    );
                                    $sheet->getStyle($cell)
                                        ->getFont()->setBold(true);
                                }
                            } elseif ($k === $this->selectedColumns->product->product_link) {
                                $link = $this->context->link->getProductLink((int) $value[$this->selectedColumns->product->product_id], null, null, null, $this->langId);
                                $cell = $excelColumns[$i] . ($key - $empty_rows + 2);
                                $sheet->setCellValue($cell, $link);
                                $sheet->getCell($cell)->getHyperlink()->setUrl($link);
                                $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FF0000FF');
                            } elseif ($k === $this->selectedColumns->product->product_image_link) {
                                // Get image data of the given product id
                                $image = Image::getCover($value[$j][$this->selectedColumns->product->product_id]);
                                if ($image) {
                                    $img_link = $this->context->link->getImageLink($value[$this->selectedColumns->product->{'order_detail_lang.product_link_rewrite'}], $image['id_image'], $this->imageType);
                                    $cell = $excelColumns[$i] . ($key - $empty_rows + 2);
                                    $sheet->setCellValue($cell, $img_link);
                                    $sheet->getCell($cell)->getHyperlink()->setUrl($img_link);
                                    $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FF0000FF');
                                } else {
                                    $cell = $excelColumns[$i] . ($key - $empty_rows + 2);
                                    $sheet->setCellValue(
                                        $cell,
                                        $this->module->l('No Image Link', 'ExportSales')
                                    );
                                    $sheet->getStyle($cell)->getFont()->setBold(true);
                                }
                            } elseif ($k === $this->selectedColumns->product->attribute_image_link) {
                                if (method_exists('Image', 'getBestImageAttribute')) {
                                    // Get image data of the given product id
                                    $image = Image::getBestImageAttribute(
                                        $value[$this->selectedColumns->shop->id_shop],
                                        $this->langId,
                                        $value[$this->selectedColumns->product->product_id],
                                        $value[$this->selectedColumns->product->product_attribute_id]
                                    );
                                } else {
                                    $image = Image::getImages(
                                        $this->langId,
                                        $value[$this->selectedColumns->product->product_id],
                                        $value[$this->selectedColumns->product->product_attribute_id]
                                    );
                                    $image = isset($image[0]) ? $image[0] : null;
                                }
                                if ($image) {
                                    $img_link = $this->context->link->getImageLink($value[$this->selectedColumns->product->{'order_detail_lang.product_link_rewrite'}], $image['id_image'], $this->imageType);
                                    $cell = $excelColumns[$i] . ($key - $empty_rows + 2);
                                    $sheet->setCellValue($cell, $img_link);
                                    $sheet->getCell($cell)->getHyperlink()->setUrl($img_link);
                                    $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FF0000FF');
                                } else {
                                    $cell = $excelColumns[$i] . ($key - $empty_rows + 2);
                                    $sheet->setCellValue(
                                        $cell,
                                        $this->module->l('No Image Link', 'ExportSales')
                                    );
                                    $sheet->getStyle($cell)->getFont()->setBold(true);
                                }
                            } elseif ($k === $this->selectedColumns->category->category_image) {
                                $cat_img_path = realpath(_PS_CAT_IMG_DIR_ . $value[$this->selectedColumns->category->id_category] . ($this->catImageTypeForFile ? '-' . $this->catImageTypeForFile : '') . '.jpg');
                                if (file_exists($cat_img_path)) {
                                    $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                                    $drawing->setPath(realpath($cat_img_path));

                                    $height = \PhpOffice\PhpSpreadsheet\Shared\Drawing::pointsToPixels(42, $font);

                                    $drawing->setHeight($height);

                                    $width = \PhpOffice\PhpSpreadsheet\Shared\Drawing::pixelsToCellDimension($drawing->getWidth(), $font);

                                    $sheet->getColumnDimension($excelColumns[$i])->setWidth($width);

                                    $drawing->setCoordinates($excelColumns[$i] . ($key - $empty_rows + 2));
                                    $drawing->setWorksheet($sheet);
                                    $drawing->getShadow()->setVisible(true);
                                } else {
                                    $cell = $excelColumns[$i] . ($key - $empty_rows + 2);
                                    $sheet->setCellValue(
                                        $cell,
                                        $this->module->l('No Image', 'ExportSales')
                                    );
                                    $sheet->getStyle($cell)
                                        ->getFont()->setBold(true);
                                }
                            } elseif ($k === $this->selectedColumns->category->category_link) {
                                if ((int) $value[$this->selectedColumns->category->id_category]) {
                                    $link = $this->context->link->getCategoryLink((int) $value[$this->selectedColumns->category->id_category], null, $this->langId);
                                    $cell = $excelColumns[$i] . ($key - $empty_rows + 2);
                                    $sheet->setCellValue($cell, $link);
                                    $sheet->getCell($cell)->getHyperlink()->setUrl($link);
                                    $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FF0000FF');
                                }
                            } elseif ($k === $this->selectedColumns->category->category_image_link) {
                                $cat_img_path = realpath(_PS_CAT_IMG_DIR_ . $value[$this->selectedColumns->category->id_category] . ($this->catImageType ? '-' . $this->catImageType : '') . '.jpg');
                                if (file_exists($cat_img_path)) {
                                    if (method_exists($this->context->link, 'getCatImageLink')) {
                                        // Get image data of the given product id
                                        $cat_img_link = $this->context->link->getCatImageLink(
                                            $value[$this->selectedColumns->category->link_rewrite],
                                            $value[$this->selectedColumns->category->id_category],
                                            $this->imageType
                                        );
                                    } else {
                                        $cat_img_link = $this->context->link->getBaseLink() . 'c/'
                                            . $value[$this->selectedColumns->category->id_category] . ($this->imageType ? '-' . $this->imageType : '') . '/'
                                            . $value[$this->selectedColumns->category->link_rewrite] . '.jpg';
                                    }
                                    $cell = $excelColumns[$i] . ($key - $empty_rows + 2);
                                    $sheet->setCellValue($cell, $cat_img_link);
                                    $sheet->getCell($cell)->getHyperlink()->setUrl($cat_img_link);
                                    $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FF0000FF');
                                } else {
                                    $cell = $excelColumns[$i] . ($key - $empty_rows + 2);
                                    $sheet->setCellValue(
                                        $cell,
                                        $this->module->l('No Image Link', 'ExportSales')
                                    );
                                    $sheet->getStyle($cell)->getFont()->setBold(true);
                                }
                            } elseif ($k === $this->selectedColumns->manufacturer->manufacturer_link) {
                                if ((int) $value[$this->selectedColumns->manufacturer->id_manufacturer]) {
                                    $link = $this->context->link->getManufacturerLink((int) $value[$this->selectedColumns->manufacturer->id_manufacturer], null, $this->langId);
                                    $cell = $excelColumns[$i] . ($key - $empty_rows + 2);
                                    $sheet->setCellValue($cell, $link);
                                    $sheet->getCell($cell)->getHyperlink()->setUrl($link);
                                    $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FF0000FF');
                                }
                            } elseif ($k === $this->selectedColumns->manufacturer->manufacturer_image) {
                                $man_img_path = realpath(_PS_MANU_IMG_DIR_ . $value[$this->selectedColumns->manufacturer->id_manufacturer] . ($this->imageTypeForFile ? '-' . $this->imageTypeForFile : '') . '.jpg');
                                if (file_exists($man_img_path)) {
                                    $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                                    $drawing->setPath(realpath($man_img_path));

                                    $height = \PhpOffice\PhpSpreadsheet\Shared\Drawing::pointsToPixels(42, $font);

                                    $drawing->setHeight($height);

                                    $width = \PhpOffice\PhpSpreadsheet\Shared\Drawing::pixelsToCellDimension($drawing->getWidth(), $font);

                                    $sheet->getColumnDimension($excelColumns[$i])->setWidth($width);

                                    $drawing->setCoordinates($excelColumns[$i] . ($key - $empty_rows + 2));
                                    $drawing->setWorksheet($sheet);
                                    $drawing->getShadow()->setVisible(true);
                                } else {
                                    $cell = $excelColumns[$i] . ($key - $empty_rows + 2);
                                    $sheet->setCellValue(
                                        $cell,
                                        $this->module->l('No Image', 'ExportSales')
                                    );
                                    $sheet->getStyle($cell)
                                        ->getFont()->setBold(true);
                                }
                            } elseif ($k === $this->selectedColumns->manufacturer->manufacturer_image_link) {
                                $man_img_path = realpath(_PS_MANU_IMG_DIR_ . $value[$this->selectedColumns->manufacturer->id_manufacturer] . ($this->imageType ? '-' . $this->imageType : '') . '.jpg');
                                if (file_exists($man_img_path)) {
                                    $man_img_link = $this->context->link->getBaseLink() . 'img/m/'
                                        . $value[$this->selectedColumns->manufacturer->id_manufacturer] . ($this->imageType ? '-' . $this->imageType : '') . '.jpg';
                                    $cell = $excelColumns[$i] . ($key - $empty_rows + 2);
                                    $sheet->setCellValue($cell, $man_img_link);
                                    $sheet->getCell($cell)->getHyperlink()->setUrl($man_img_link);
                                    $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FF0000FF');
                                } else {
                                    $cell = $excelColumns[$i] . ($key - $empty_rows + 2);
                                    $sheet->setCellValue(
                                        $cell,
                                        $this->module->l('No Image Link', 'ExportSales')
                                    );
                                    $sheet->getStyle($cell)->getFont()->setBold(true);
                                }
                            } elseif ($k === $this->selectedColumns->supplier->supplier_link) {
                                if ((int) $value[$this->selectedColumns->supplier->id_supplier]) {
                                    $link = $this->context->link->getSupplierLink((int) $value[$this->selectedColumns->supplier->id_supplier], null, $this->langId);
                                    $cell = $excelColumns[$i] . ($key - $empty_rows + 2);
                                    $sheet->setCellValue($cell, $link);
                                    $sheet->getCell($cell)->getHyperlink()->setUrl($link);
                                    $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FF0000FF');
                                }
                            } elseif ($k === $this->selectedColumns->supplier->supplier_image) {
                                $supp_img_path = realpath(_PS_SUPP_IMG_DIR_ . $value[$this->selectedColumns->supplier->id_supplier] . ($this->imageTypeForFile ? '-' . $this->imageTypeForFile : '') . '.jpg');
                                if (file_exists($supp_img_path)) {
                                    $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                                    $drawing->setPath(realpath($supp_img_path));

                                    $height = \PhpOffice\PhpSpreadsheet\Shared\Drawing::pointsToPixels(42, $font);

                                    $drawing->setHeight($height);

                                    $width = \PhpOffice\PhpSpreadsheet\Shared\Drawing::pixelsToCellDimension($drawing->getWidth(), $font);

                                    $sheet->getColumnDimension($excelColumns[$i])->setWidth($width);

                                    $drawing->setCoordinates($excelColumns[$i] . ($key - $empty_rows + 2));
                                    $drawing->setWorksheet($sheet);
                                    $drawing->getShadow()->setVisible(true);
                                } else {
                                    $cell = $excelColumns[$i] . ($key - $empty_rows + 2);
                                    $sheet->setCellValue(
                                        $cell,
                                        $this->module->l('No Image', 'ExportSales')
                                    );
                                    $sheet->getStyle($cell)
                                        ->getFont()->setBold(true);
                                }
                            } elseif ($k === $this->selectedColumns->supplier->supplier_image_link) {
                                $supp_img_path = realpath(_PS_SUPP_IMG_DIR_ . $value[$this->selectedColumns->supplier->id_supplier] . ($this->imageType ? '-' . $this->imageType : '') . '.jpg');
                                if (file_exists($supp_img_path)) {
                                    $supp_img_link = $this->context->link->getBaseLink() . 'img/su/'
                                        . $value[$this->selectedColumns->supplier->id_supplier] . ($this->imageType ? '-' . $this->imageType : '') . '.jpg';
                                    $cell = $excelColumns[$i] . ($key - $empty_rows + 2);
                                    $sheet->setCellValue($cell, $supp_img_link);
                                    $sheet->getCell($cell)->getHyperlink()->setUrl($supp_img_link);
                                    $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FF0000FF');
                                } else {
                                    $cell = $excelColumns[$i] . ($key - $empty_rows + 2);
                                    $sheet->setCellValue(
                                        $cell,
                                        $this->module->l('No Image Link', 'ExportSales')
                                    );
                                    $sheet->getStyle($cell)->getFont()->setBold(true);
                                }
                            } else {
                                if ($k === $this->selectedColumns->order->{'order_messages.message'}) {
                                    $val = html_entity_decode($val);
                                }
                                $sheet->setCellValue($excelColumns[$i] . ($key - $empty_rows + 2), $val);
                                if (Tools::strlen($val) <= 8) {
                                    $sheet->getColumnDimension($excelColumns[$i])->setWidth(15);
                                }
                            }
                            ++$i;
                        }
                    }
                    if ($profits || $netProfits) {
                        $curr = $this->displayCurrSymbol ? $this->curs->{$orders[$key][$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';
                        $totalProducts = $orders[$key][$this->selectedColumns->order->total_products];
                        $totalDiscountsTaxExcl = $orders[$key][$this->selectedColumns->order->total_discounts_tax_excl];

                        if (isset($this->selectedColumns->order->profit_amount)) {
                            $profit_amount = $totalProducts - $groupTotal;
                            $profitAmountCol = $excelColumns[array_search($this->selectedColumns->order->profit_amount, $headers)];
                            for ($l = 0; $l < $groupCount; $l++) {
                                $sheet->setCellValue($profitAmountCol . ($key - $empty_rows + 2 - $l), $curr . str_replace('.', $this->decimalSeparator, (string) round($profit_amount, $this->fracPart)));
                            }
                        }
                        if (isset($this->selectedColumns->order->profit_margin)) {
                            $profit_margin = 100 * ($totalProducts - $groupTotal) / $totalProducts;
                            $profitMarginCol = $excelColumns[array_search($this->selectedColumns->order->profit_margin, $headers)];
                            for ($l = 0; $l < $groupCount; $l++) {
                                $sheet->setCellValue($profitMarginCol . ($key - $empty_rows + 2 - $l), str_replace('.', $this->decimalSeparator, (string) round($profit_margin, $this->fracPart)) . '%');
                            }
                        }
                        if (isset($this->selectedColumns->order->profit_percentage)) {
                            $profit_percentage = 100 * ($totalProducts - $groupTotal) / $groupTotal;
                            $profitPercentageCol = $excelColumns[array_search($this->selectedColumns->order->profit_percentage, $headers)];
                            for ($l = 0; $l < $groupCount; $l++) {
                                $sheet->setCellValue($profitPercentageCol . ($key - $empty_rows + 2 - $l), str_replace('.', $this->decimalSeparator, (string) round($profit_percentage, $this->fracPart)) . '%');
                            }
                        }
                        if (isset($this->selectedColumns->order->net_profit_amount)) {
                            $net_profit_amount = $totalProducts - $totalDiscountsTaxExcl - $groupTotal;
                            $netProfitAmountCol = $excelColumns[array_search($this->selectedColumns->order->net_profit_amount, $headers)];
                            for ($l = 0; $l < $groupCount; $l++) {
                                $sheet->setCellValue($netProfitAmountCol . ($key - $empty_rows + 2 - $l), $curr . str_replace('.', $this->decimalSeparator, (string) round($net_profit_amount, $this->fracPart)));
                            }
                        }
                        if (isset($this->selectedColumns->order->net_profit_margin)) {
                            $net_profit_margin = 100 * ($totalProducts - $totalDiscountsTaxExcl - $groupTotal) / ($totalProducts - $totalDiscountsTaxExcl);
                            $netProfitMarginCol = $excelColumns[array_search($this->selectedColumns->order->net_profit_margin, $headers)];
                            for ($l = 0; $l < $groupCount; $l++) {
                                $sheet->setCellValue($netProfitMarginCol . ($key - $empty_rows + 2 - $l), str_replace('.', $this->decimalSeparator, (string) round($net_profit_margin, $this->fracPart)) . '%');
                            }
                        }
                        if (isset($this->selectedColumns->order->net_profit_percentage)) {
                            $net_profit_percentage = 100 * ($totalProducts - $totalDiscountsTaxExcl - $groupTotal) / $groupTotal;
                            $netProfitPercentageCol = $excelColumns[array_search($this->selectedColumns->order->net_profit_percentage, $headers)];
                            for ($l = 0; $l < $groupCount; $l++) {
                                $sheet->setCellValue($netProfitPercentageCol . ($key - $empty_rows + 2 - $l), str_replace('.', $this->decimalSeparator, (string) round($net_profit_percentage, $this->fracPart)) . '%');
                            }
                        }
                        if ($this->displayTotals === '1') {
                            if ($profits) {
                                $sale += $totalProducts / $orders[$key][$this->selectedColumns->order->{'currency.conversion_rate'}];
                            }
                            if ($netProfits) {
                                $netSale += ($totalProducts - $totalDiscountsTaxExcl) / $orders[$key][$this->selectedColumns->order->{'currency.conversion_rate'}];
                            }
                        }
                    }
                }

                $key += 3;

                if ($this->displayTotals === '1') {
                    $styleArray = [
                        'font' => [
                            'bold' => true,
                            'color' => ['argb' => 'FF3C763D'],
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                                'color' => ['argb' => 'FF7CC67C'],
                            ],
                        ],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => [
                                'argb' => 'FFE7FFD9',
                            ],
                        ],
                    ];

                    $sheet->getStyle('A' . ($key - $empty_rows) . ':' . end($excelColumns) . ($key - $empty_rows))->applyFromArray($styleArray);

                    $def_curr = Configuration::get('OXSRP_DEF_CURR_SMBL') . ' ';

                    foreach ($totals as $k => $v) {
                        $sheet->setCellValue($k . ($key - $empty_rows), ((isset($v['curr']) && $v['curr']) ? $def_curr : '') . str_replace('.', $this->decimalSeparator, (string) round($v['val'], $this->fracPart)));
                    }
                    if (isset($profitAmountCol)) {
                        $val = str_replace('.', $this->decimalSeparator, (string) round($sale - $purchase, $this->fracPart));
                        $sheet->setCellValue($profitAmountCol . ($key - $empty_rows), $def_curr . $val);
                    }
                    if (isset($profitMarginCol)) {
                        $val = str_replace('.', $this->decimalSeparator, (string) round(100 * ($sale - $purchase) / $sale, $this->fracPart));
                        $sheet->setCellValue($profitMarginCol . ($key - $empty_rows), $val . '%');
                    }
                    if (isset($profitPercentageCol)) {
                        $val = str_replace('.', $this->decimalSeparator, (string) round(100 * ($sale - $purchase) / $purchase, $this->fracPart));
                        $sheet->setCellValue($profitPercentageCol . ($key - $empty_rows), $val . '%');
                    }
                    if (isset($netProfitAmountCol)) {
                        $val = str_replace('.', $this->decimalSeparator, (string) round($netSale - $purchase, $this->fracPart));
                        $sheet->setCellValue($netProfitAmountCol . ($key - $empty_rows), $def_curr . $val);
                    }
                    if (isset($netProfitMarginCol)) {
                        $val = str_replace('.', $this->decimalSeparator, (string) round(100 * ($netSale - $purchase) / $netSale, $this->fracPart));
                        $sheet->setCellValue($netProfitMarginCol . ($key - $empty_rows), $val . '%');
                    }
                    if (isset($netProfitPercentageCol)) {
                        $val = str_replace('.', $this->decimalSeparator, (string) round(100 * ($netSale - $purchase) / $purchase, $this->fracPart));
                        $sheet->setCellValue($netProfitPercentageCol . ($key - $empty_rows), $val . '%');
                    }
                    if (isset($reductionPercentCol)) {
                        $val = str_replace('.', $this->decimalSeparator, (string) round(100 * ($reductionTotals['full'] - $reductionTotals['reduced']) / $reductionTotals['full'], $this->fracPart));
                        $sheet->setCellValue($reductionPercentCol . ($key - $empty_rows), $val . '%');
                    }
                    $key++;
                }
                if ($this->displayFooter === '1') {
                    $sheet->getStyle('A' . ($key - $empty_rows) . ':' . end($excelColumns) . ($key - $empty_rows))
                        ->getFont()->setBold(true);
                    $sheet->getStyle('A' . ($key - $empty_rows) . ':' . end($excelColumns) . ($key - $empty_rows))
                        ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFDCF0FF');
                    $sheet->getStyle('A' . ($key - $empty_rows) . ':' . end($excelColumns) . ($key - $empty_rows))->getBorders()
                        ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                    for ($i = 0; $i < $counter - $psp; $i++) {
                        $sheet->setCellValue($excelColumns[$i] . ($key - $empty_rows), $headers[$i]);
                    }
                    $key++;
                }
                if (!$this->displayHeader) {
                    $sheet->removeRow(1);
                }
                if ($this->displayExplanations === '1') {
                    $key++;
                    if (isset($profitAmountCol)) {
                        $sheet->setCellValue($profitAmountCol . ($key - $empty_rows), '*' . $this->module->l(' Gross Profit Amount = S - P. Sale price of an order minus total purchase price of products in that order (taxes excluded).', 'ExportSales'));
                        $sheet->getStyle($profitAmountCol . ($key - $empty_rows))->getFont()->setSize(10);
                    }
                    if (isset($profitMarginCol)) {
                        $sheet->setCellValue($profitMarginCol . ($key - $empty_rows), '*' . $this->module->l(' Gross Profit Margin = 100 * (S - P) / S. Sale price of an order minus total purchase price of products in that order divided by the sale price multiplied by 100 (taxes excluded).', 'ExportSales'));
                        $sheet->getStyle($profitMarginCol . ($key - $empty_rows))->getFont()->setSize(10);
                    }
                    if (isset($profitPercentageCol)) {
                        $sheet->setCellValue($profitPercentageCol . ($key - $empty_rows), '*' . $this->module->l(' Gross Profit Percentage = 100 * (S - P) / P. Sale price of an order minus total purchase price of products in that order divided by the purchase price multiplied by 100 (taxes excluded).', 'ExportSales'));
                        $sheet->getStyle($profitPercentageCol . ($key - $empty_rows))->getFont()->setSize(10);
                    }
                    if (isset($netProfitAmountCol)) {
                        $sheet->setCellValue($netProfitAmountCol . ($key - $empty_rows), '*' . $this->module->l(' Net Profit Amount = S - D - P. Sale price of an order minus the discount of that order minus total purchase price of products in that order (taxes excluded).', 'ExportSales'));
                        $sheet->getStyle($netProfitAmountCol . ($key - $empty_rows))->getFont()->setSize(10);
                    }
                    if (isset($netProfitMarginCol)) {
                        $sheet->setCellValue($netProfitMarginCol . ($key - $empty_rows), '*' . $this->module->l(' Net Profit Margin = 100 * (S - D - P) / (S - D). Sale price of an order minus the discount of that order minus total purchase price of products in that order divided by the sale price minus the discount of that order multiplied by 100 (taxes excluded).', 'ExportSales'));
                        $sheet->getStyle($netProfitMarginCol . ($key - $empty_rows))->getFont()->setSize(10);
                    }
                    if (isset($netProfitPercentageCol)) {
                        $sheet->setCellValue($netProfitPercentageCol . ($key - $empty_rows), '*' . $this->module->l(' Net Profit Percentage = 100 * (S - D - P) / P. Sale price of an order minus the discount of that order minus total purchase price of products in that order divided by the purchase price multiplied by 100 (taxes excluded).', 'ExportSales'));
                        $sheet->getStyle($netProfitPercentageCol . ($key - $empty_rows))->getFont()->setSize(10);
                    }
                    if (!$this->purchaseSupplierPrice && $this->selectedColumns->product->purchase_supplier_price) {
                        $col = $excelColumns[array_search($this->selectedColumns->product->purchase_supplier_price, $headers)];
                        $sheet->setCellValue($col . ($key - $empty_rows), '*' . $this->module->l(' Product Quantity = Product purchase price multiplied by product purchased quantity, then summed.', 'ExportSales'));
                        $sheet->getStyle($col . ($key - $empty_rows))->getFont()->setSize(10);
                    }
//                $sheet->getRowDimension($key - $empty_rows)->setRowHeight(100);
                }

//            $sheet->setCellValue('A' . ++$key, $this->module->l('Date', 'ExportSales') . ': ')
//                ->getStyle('A' . $key)->getFont()->setBold(true);
//            $sheet->setCellValue('B' . $key, date('Y-m-d H:i:s'));


//            $wideColumns = array(
//                'customer' => array(
//                    'email' => 25
//                ),
//                'product' => array(
//                    'order_detail_lang.attributes' => 25,
//                    'product_features.features' => 25,
//                    'product_link' => 35,
//                    'product_image_link' => 35,
//                    'attribute_image_link' => 35,
//                ),
//                'payment' => array(
//                    'payment_details' => 25
//                ),
//                'order' => array(
//                    'order_state_history.order_history' => 30,
//                    'order_messages.message' => 30,
//                ),
//                'category' => array(
//                    'category_link' => 35,
//                    'category_image_link' => 35,
//                    'description' => 40
//                ),
//                'manufacturer' => array(
//                    'manufacturer_link' => 35,
//                    'manufacturer_image_link' => 35
//                ),
//                'supplier' => array(
//                    'supplier_link' => 35,
//                    'supplier_image_link' => 35
//                ),
//            );
//
//            foreach ($wideColumns as $key => $val) {
//                foreach ($val as $k => $v) {
//                    if (isset($this->selectedColumns->{$key}->{$k})) {
//                        $sheet->getColumnDimension($excelColumns[
//                                array_search($this->selectedColumns->{$key}->{$k}, $headers)])
//                            ->setWidth($v);
//                    }
//                }
//            }

                $sheet->getPageSetup()->setOrientation(PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);

                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 1);

                $sheet->setSelectedCell('A1');
            }
        } else {
            $spreadsheet->removeSheetByIndex(0);
        }

        if ($this->displayBestSellers === '1' && !is_numeric($this->auto)) {
            $sales = $this->getBestSellers();
            if (is_numeric($this->auto) && !$sales && $autoExportDoNotSend) {
                return 0;
            }

            if ($this->auto === 'schedule' && !$sales && $scheduleDoNotSend) {
                return 0;
            }
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($this->module->l('Sales by Products', 'ExportSales'));
            array_unshift($sales, array(
                $this->module->l('Product ID', 'ExportSales'),
                $this->module->l('Product Reference', 'ExportSales'),
                $this->module->l('Product Name', 'ExportSales'),
                $this->module->l('Sold Quantity', 'ExportSales'),
                $this->module->l('Total Profit (Tax Excl.)', 'ExportSales'),
                $this->module->l('Total Price (Tax Excl.)', 'ExportSales'),
                $this->module->l('Total Price (Tax Incl.)', 'ExportSales'),
                $this->module->l('Total Paid (Tax Excl.)', 'ExportSales'),
                $this->module->l('Total Paid (Tax Incl.)', 'ExportSales'),
                $this->module->l('Total Really Paid', 'ExportSales'),
            ));
            $sheet->fromArray($sales, null);
            $sheet->getDefaultRowDimension()->setRowHeight(30);
            $sheet->getColumnDimension('A')->setWidth(13);
            $sheet->getColumnDimension('B')->setWidth(20);
            $sheet->getColumnDimension('C')->setWidth(25);
            $sheet->getColumnDimension('D')->setWidth(20);
            $sheet->getColumnDimension('E')->setWidth(20);
            $sheet->getColumnDimension('F')->setWidth(20);
            $sheet->getColumnDimension('G')->setWidth(20);
            $sheet->getColumnDimension('H')->setWidth(20);
            $sheet->getColumnDimension('I')->setWidth(20);
            $sheet->getColumnDimension('J')->setWidth(20);
            $sheet->getStyle('A:J')->getAlignment()->setVertical('center')->setHorizontal('center');
            $sheet->getStyle('A1:J' . count($sales))
                ->getAlignment()->setWrapText(true);
            $sheet->getStyle('A1:J1')
                ->getFont()->setBold(true);
            $sheet->getStyle('A1:J1')
                ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFDCF0FF');
            $sheet->getStyle('A1:J1')->getBorders()
                ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $sheet->setSelectedCell('A1');
        }

        if ($this->displayProductCombs === '1' && !is_numeric($this->auto)) {
            $sales = $this->getProductCombs();
            if (is_numeric($this->auto) && !$sales && $autoExportDoNotSend) {
                return 0;
            }

            if ($this->auto === 'schedule' && !$sales && $scheduleDoNotSend) {
                return 0;
            }
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($this->module->l('Sales by Combinations', 'ExportSales'));
            array_unshift($sales, array(
                $this->module->l('Product ID', 'ExportSales'),
                $this->module->l('Product Name', 'ExportSales'),
                $this->module->l('Combination ID', 'ExportSales'),
                $this->module->l('Combination', 'ExportSales'),
                $this->module->l('Sold Quantity', 'ExportSales'),
                $this->module->l('Total Profit (Tax Excl.)', 'ExportSales'),
                $this->module->l('Total Price (Tax Excl.)', 'ExportSales')
            ));
            $sheet->fromArray($sales, null);
            $sheet->getDefaultRowDimension()->setRowHeight(30);
            $sheet->getColumnDimension('A')->setWidth(13);
            $sheet->getColumnDimension('B')->setWidth(25);
            $sheet->getColumnDimension('C')->setWidth(20);
            $sheet->getColumnDimension('D')->setWidth(25);
            $sheet->getColumnDimension('E')->setWidth(18);
            $sheet->getColumnDimension('F')->setWidth(18);
            $sheet->getColumnDimension('G')->setWidth(18);
            $sheet->getStyle('A:G')->getAlignment()->setVertical('center')->setHorizontal('center');
            $sheet->getStyle('A1:G' . count($sales))
                ->getAlignment()->setWrapText(true);
            $sheet->getStyle('A1:G1')
                ->getFont()->setBold(true);
            $sheet->getStyle('A1:G1')
                ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFDCF0FF');
            $sheet->getStyle('A1:G1')->getBorders()
                ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $sheet->setSelectedCell('A1');
        }

        if ($this->displayDailySales === '1' && !is_numeric($this->auto)) {
            $sales = $this->getDailySales();
            if (is_numeric($this->auto) && !$sales && $autoExportDoNotSend) {
                return 0;
            }

            if ($this->auto === 'schedule' && !$sales && $scheduleDoNotSend) {
                return 0;
            }
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($this->module->l('Daily Sales', 'ExportSales'));
            array_unshift($sales, array(
                $this->module->l('Date', 'ExportSales'),
                $this->module->l('Sold Quantity', 'ExportSales'),
                $this->module->l('Total Profit (Tax Excl.)', 'ExportSales'),
                $this->module->l('Total Price (Tax Excl.)', 'ExportSales')
            ));
            $sheet->fromArray($sales, null);
            $sheet->getDefaultRowDimension()->setRowHeight(30);
            $sheet->getColumnDimension('A')->setWidth(13);
            $sheet->getColumnDimension('B')->setWidth(25);
            $sheet->getColumnDimension('C')->setWidth(20);
            $sheet->getColumnDimension('D')->setWidth(25);
            $sheet->getStyle('A:D')->getAlignment()->setVertical('center')->setHorizontal('center');
            $sheet->getStyle('A1:D' . count($sales))
                ->getAlignment()->setWrapText(true);
            $sheet->getStyle('A1:D1')
                ->getFont()->setBold(true);
            $sheet->getStyle('A1:D1')
                ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFDCF0FF');
            $sheet->getStyle('A1:D1')->getBorders()
                ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $sheet->setSelectedCell('A1');
        }

        if ($this->displayMonthlySales === '1' && !is_numeric($this->auto)) {
            $sales = $this->getMonthlySales();
            if (is_numeric($this->auto) && !$sales && $autoExportDoNotSend) {
                return 0;
            }

            if ($this->auto === 'schedule' && !$sales && $scheduleDoNotSend) {
                return 0;
            }
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($this->module->l('Monthly Sales', 'ExportSales'));
            array_unshift($sales, array(
                $this->module->l('Date', 'ExportSales'),
                $this->module->l('Sold Quantity', 'ExportSales'),
                $this->module->l('Total Profit (Tax Excl.)', 'ExportSales'),
                $this->module->l('Total Price (Tax Excl.)', 'ExportSales')
            ));
            $sheet->fromArray($sales, null);
            $sheet->getDefaultRowDimension()->setRowHeight(30);
            $sheet->getColumnDimension('A')->setWidth(13);
            $sheet->getColumnDimension('B')->setWidth(25);
            $sheet->getColumnDimension('C')->setWidth(20);
            $sheet->getColumnDimension('D')->setWidth(25);
            $sheet->getStyle('A:D')->getAlignment()->setVertical('center')->setHorizontal('center');
            $sheet->getStyle('A1:D' . count($sales))
                ->getAlignment()->setWrapText(true);
            $sheet->getStyle('A1:D1')
                ->getFont()->setBold(true);
            $sheet->getStyle('A1:D1')
                ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFDCF0FF');
            $sheet->getStyle('A1:D1')->getBorders()
                ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $sheet->setSelectedCell('A1');
        }

        if ($this->displayTopCustomers === '1' && !is_numeric($this->auto)) {
            $sales = $this->getTopCustomers();
            if (is_numeric($this->auto) && !$sales && $autoExportDoNotSend) {
                return 0;
            }

            if ($this->auto === 'schedule' && !$sales && $scheduleDoNotSend) {
                return 0;
            }
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($this->module->l('Sales by Customers', 'ExportSales'));
            array_unshift($sales, array(
                $this->module->l('Customer ID', 'ExportSales'),
                $this->module->l('Customer Email', 'ExportSales'),
                $this->module->l('Customer Firstname', 'ExportSales'),
                $this->module->l('Customer Lastname', 'ExportSales'),
                $this->module->l('Number of Orders', 'ExportSales'),
                $this->module->l('Total Orders (Tax Excl.)', 'ExportSales'),
                $this->module->l('Total Orders with Discount (Tax Excl.)', 'ExportSales'),
            ));
            $sheet->fromArray($sales, null);
            $sheet->getDefaultRowDimension()->setRowHeight(30);
            $sheet->getColumnDimension('A')->setWidth(13);
            $sheet->getColumnDimension('B')->setWidth(25);
            $sheet->getColumnDimension('C')->setWidth(20);
            $sheet->getColumnDimension('D')->setWidth(20);
            $sheet->getColumnDimension('E')->setWidth(13);
            $sheet->getColumnDimension('F')->setWidth(23);
            $sheet->getColumnDimension('G')->setWidth(25);
            $sheet->getStyle('A:G')->getAlignment()->setVertical('center')->setHorizontal('center');
            $sheet->getStyle('A1:G' . count($sales))
                ->getAlignment()->setWrapText(true);
            $sheet->getStyle('A1:G1')
                ->getFont()->setBold(true);
            $sheet->getStyle('A1:G1')
                ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFDCF0FF');
            $sheet->getStyle('A1:G1')->getBorders()
                ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $sheet->setSelectedCell('A1');
        }

        if ($this->displayPaymentMethods === '1' && !is_numeric($this->auto)) {
            $sales = $this->getPaymentSales();


//            array_pop($sales);
            if (is_numeric($this->auto) && !$sales && $autoExportDoNotSend) {
                return 0;
            }

            if ($this->auto === 'schedule' && !$sales && $scheduleDoNotSend) {
                return 0;
            }
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($this->module->l('Sales by Payment Methods', 'ExportSales'));
            array_unshift($sales, array(
                $this->module->l('Combined Payment', 'ExportSales'),
                $this->module->l('Module', 'ExportSales'),
                $this->module->l('Confirmed Orders', 'ExportSales'),
                $this->module->l('Total Products (Tax Excl.)', 'ExportSales'),
                $this->module->l('Total Products (Tax Incl.)', 'ExportSales'),
                $this->module->l('Total Discounts (Tax Incl.)', 'ExportSales'),
                $this->module->l('Total Paid (Tax Incl.)', 'ExportSales'),
                $this->module->l('Refunded Instore (Tax Incl.)', 'ExportSales'),
                $this->module->l('Total Tax (CA 5%)', 'ExportSales'),
                $this->module->l('Total Tax (CA-QC 9.975%)', 'ExportSales'),
                $this->module->l('Refunds (Tax Incl.)', 'ExportSales'),
            ));

            $count = count($sales) + 1;

            $sheet->fromArray($sales, null);
            $sheet->getDefaultRowDimension()->setRowHeight(30);
            $sheet->getColumnDimension('A')->setWidth(20);
            $sheet->getColumnDimension('B')->setWidth(20);
            $sheet->getColumnDimension('C')->setWidth(13);
            $sheet->getColumnDimension('D')->setWidth(20);
            $sheet->getColumnDimension('E')->setWidth(20);
            $sheet->getColumnDimension('F')->setWidth(20);
            $sheet->getColumnDimension('G')->setWidth(20);
            $sheet->getColumnDimension('H')->setWidth(20);
            $sheet->getColumnDimension('I')->setWidth(20);
            $sheet->getColumnDimension('J')->setWidth(20);
            $sheet->getColumnDimension('K')->setWidth(20);
            $sheet->getStyle('A:K')->getAlignment()->setVertical('center')->setHorizontal('center');
            $sheet->getStyle('A1:K' . count($sales))
                ->getAlignment()->setWrapText(true);
            $sheet->getStyle('A1:K1')
                ->getFont()->setBold(true);
            $sheet->getStyle('A1:K1')
                ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFDCF0FF');
            $sheet->getStyle('A1:K1')->getBorders()
                ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            $sheet->setCellValue('A'.$count, $this->module->l('TOTALS', 'ExportSales'));

            array_shift($sales);

            // print_r($sales);
            // die;

            if (!empty($sales)) {

                $sheet->setCellValue('C'.$count, '=SUM(C2:C'.($count - 1).')');

                $sum = array(
                    'total_products' => 0,
                    'total_products_wt' => 0,
                    'total_discounts_wt' => 0,
                    'total_paid_tax_incl' => 0,
                    'order_slip_amount_tax_incl' => 0,
                    'canada_tax_total_amount' => 0,
                    'quebec_tax_total_amount' => 0,
                    'rock_refund_tax_incl' => 0
                );

                foreach ($sales as $res) {
                    $sum['total_products'] += (float) trim($res['total_products'], $this->currencySymbol);
                    $sum['total_products_wt'] += (float) trim($res['total_products_wt'], $this->currencySymbol);
                    $sum['total_discounts_wt'] += (float) trim($res['total_discounts_wt'], $this->currencySymbol);
                    $sum['total_paid_tax_incl'] += (float) trim($res['total_paid_tax_incl'], $this->currencySymbol);
                    $sum['order_slip_amount_tax_incl'] += (float) trim($res['order_slip_amount_tax_incl'], $this->currencySymbol);
                    $sum['canada_tax_total_amount'] += (float) trim($res['canada_tax_total_amount'], $this->currencySymbol);
                    $sum['quebec_tax_total_amount'] += (float) trim($res['quebec_tax_total_amount'], $this->currencySymbol);
                    $sum['rock_refund_tax_incl'] += (float) trim($res['rock_refund_tax_incl'], $this->currencySymbol);
                }

                foreach ($sum as $k => $s) {
                    $sum[$k] = $this->currencySymbol . $s;
                }

                $sheet->fromArray(array($sum), null, 'D' . $count);
            }

            $sheet->getStyle('A'.$count.':K'.$count)->getFont()->setBold(true);

            // $sheet->setSelectedCell('A1');

            ///////////////////////////////////////////////

            $count += 2;

            $sales = $this->getPaymentSales2();







            $newsales = array();
            $vr_and_gc = array();

            $vr_gc_total = 0;
            $sales_counter = 0;
            foreach($sales as $sale){
                if($sale['payment_method'] != 'Voucher' && $sale['payment_method'] != 'InStore Gift Card'){
                    $newsales[] = $sale;
                }else{

                    $payment_amount_arr = explode('$ ', $sale['payment_amount']);
                    $vr_gc_total = $vr_gc_total + $payment_amount_arr[1];

                    $minus_figure = -1 * $payment_amount_arr[1];
                    $sales[$sales_counter]['payment_amount'] = '$ '. $minus_figure;
                    $vr_and_gc[] =$sales[$sales_counter];
                }

                $sales_counter++;
            }
            $sales = $newsales;
            foreach($vr_and_gc as $vr_and_gc_single){
                $sales[] = $vr_and_gc_single;
            }


            $sales_counter = 0;




            foreach($sales as $sale){
                if($sale['module'] == 'TOTAL FOR IN-STORE'){

                    $payment_amount_arr = explode('$ ', $sale['payment_amount']);

                    $figure =  $payment_amount_arr[1] - $vr_gc_total;
                    $sales[$sales_counter]['payment_amount'] = '$ '. $figure;

                }
                $sales_counter++;
            }


            $final_sales = array();



            $discount_sales = array();
            foreach ($sales as $sale){
//                if($sale['payment_method'] == 'Discount Online' || $sale['payment_method'] == 'Discount InStore'){
                if( $sale['payment_method'] == 'Discount InStore'){
                    $discount_sales[]=$sale;
                }else{
                    $final_sales[]=$sale;
                }
            }



            $sales =array_merge($final_sales,$discount_sales)  ;



            array_unshift($sales, array(
                $this->module->l('Specific Payment', 'ExportSales'),
                $this->module->l('Module', 'ExportSales'),
                $this->module->l('Payment Amount', 'ExportSales'),
                // $this->module->l('Number of Orders', 'ExportSales')
            ));

            $sheet->fromArray($sales, null, 'A' . $count);

            // $sheet->getDefaultRowDimension()->setRowHeight(30);
            // $sheet->getColumnDimension('A')->setWidth(25);
            // $sheet->getColumnDimension('B')->setWidth(25);
            // $sheet->getColumnDimension('C')->setWidth(13);
            // $sheet->getColumnDimension('D')->setWidth(13);
            // $sheet->getStyle('A:D')->getAlignment()->setVertical('center')->setHorizontal('center');
            $sheet->getStyle('A'.$count.':C' . ($count + count($sales)))
                ->getAlignment()->setWrapText(true);
            $sheet->getStyle('A'.$count.':C'.$count)
                ->getFont()->setBold(true);
            $sheet->getStyle('A'.$count.':C'.$count)
                ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFDCF0FF');
            $sheet->getStyle('A'.$count.':C'.$count)->getBorders()
                ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            $sheet->getStyle('A'.($count + 1).':C'.($count + 1))
                ->getFont()->setBold(true);

            $sheet->getStyle('A'.($count + 2 + $this->nth_total['res1']).':C'.($count + 2 + $this->nth_total['res1']))
                ->getFont()->setBold(true);

            // var_dump($this->nth_total);
            // die;

            $sheet->getPageSetup()->setOrientation(PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
            // $sheet->getPageSetup()->setFitToPage(true);
            $sheet->getPageSetup()->setFitToWidth(1);
            $sheet->getPageSetup()->setFitToHeight(0);

            $sheet->setSelectedCell('A1');
        }

        if ($this->displayTaxes === '1' && !is_numeric($this->auto)) {
            $sales = $this->getTaxes();
            if (is_numeric($this->auto) && !$sales && $autoExportDoNotSend) {
                return 0;
            }

            if ($this->auto === 'schedule' && !$sales && $scheduleDoNotSend) {
                return 0;
            }
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($this->module->l('Taxes', 'ExportSales'));
            array_unshift($sales, array(
                $this->module->l('Tax Name', 'ExportSales'),
                $this->module->l('Tax Amount', 'ExportSales'),
            ));
            $sheet->fromArray($sales, null);
            $sheet->getDefaultRowDimension()->setRowHeight(30);
            $sheet->getColumnDimension('A')->setWidth(25);
            $sheet->getColumnDimension('B')->setWidth(25);
            $sheet->getStyle('A:B')->getAlignment()->setVertical('center')->setHorizontal('center');
            $sheet->getStyle('A1:B' . count($sales))
                ->getAlignment()->setWrapText(true);
            $sheet->getStyle('A1:B1')
                ->getFont()->setBold(true);
            $sheet->getStyle('A1:B1')
                ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFDCF0FF');
            $sheet->getStyle('A1:B1')->getBorders()
                ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $sheet->setSelectedCell('A1');
        }


        // if ($this->displayPaymentMethods2 === '1' && !is_numeric($this->auto)) {
        //     $sheet = $spreadsheet->createSheet();
        //     $sheet->setTitle($this->module->l('Sales by Payment Options', 'ExportSales'));
        //     $sales = $this->getPaymentSales2();
        //     array_unshift($sales, array(
        //         $this->module->l('Module', 'ExportSales'),
        //         $this->module->l('Payment Method', 'ExportSales'),
        //         $this->module->l('Payment Amount', 'ExportSales'),
        //         $this->module->l('Number of Orders', 'ExportSales'),
        //         $this->module->l('Total Orders (Tax Excl.)', 'ExportSales'),
        //         $this->module->l('Total Orders with Discount (Tax Excl.)', 'ExportSales'),
        //         $this->module->l('Refunded Amount ROCK (Tax Excl.)', 'ExportSales'),
        //         $this->module->l('Refunded Amount ROCK (Tax Incl.)', 'ExportSales'),
        //     ));
        //     $sheet->fromArray($sales, null);
        //     $sheet->getDefaultRowDimension()->setRowHeight(30);
        //     $sheet->getColumnDimension('A')->setWidth(25);
        //     $sheet->getColumnDimension('B')->setWidth(25);
        //     $sheet->getColumnDimension('C')->setWidth(13);
        //     $sheet->getColumnDimension('D')->setWidth(13);
        //     $sheet->getColumnDimension('E')->setWidth(20);
        //     $sheet->getColumnDimension('F')->setWidth(20);
        //     $sheet->getColumnDimension('G')->setWidth(20);
        //     $sheet->getColumnDimension('H')->setWidth(20);
        //     $sheet->getStyle('A:H')->getAlignment()->setVertical('center')->setHorizontal('center');
        //     $sheet->getStyle('A1:H' . count($sales))
        //         ->getAlignment()->setWrapText(true);
        //     $sheet->getStyle('A1:H1')
        //         ->getFont()->setBold(true);
        //     $sheet->getStyle('A1:H1')
        //         ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        //         ->getStartColor()->setARGB('FFDCF0FF');
        //     $sheet->getStyle('A1:H1')->getBorders()
        //         ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        //     // if (isset($this->nth_total['res1'])) {
        //         $sheet->getStyle('A'.$this->nth_total['res1'].':H'.$this->nth_total['res1'])
        //         ->getFont()->setBold(true);
        //     // }
        //     // if (isset($this->nth_total['res2'])) {
        //         $sheet->getStyle('A'.$this->nth_total['res2'].':H'.$this->nth_total['res2'])
        //         ->getFont()->setBold(true);
        //     // }

        //     $sheet->setSelectedCell('A1');
        // }

        if ($this->displaySalesByCategories === '1' && !is_numeric($this->auto)) {
            $sales = $this->getSalesByCategories();
            if (is_numeric($this->auto) && !$sales && $autoExportDoNotSend) {
                return 0;
            }

            if ($this->auto === 'schedule' && !$sales && $scheduleDoNotSend) {
                return 0;
            }
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($this->module->l('Sales by Categories', 'ExportSales'));
            array_unshift($sales, array(
                $this->module->l('Category ID', 'ExportSales'),
                $this->module->l('Category Name', 'ExportSales'),
                $this->module->l('Total Price (Tax Excl.)', 'ExportSales')
            ));
            $sheet->fromArray($sales, null);
            $sheet->getDefaultRowDimension()->setRowHeight(30);
            $sheet->getColumnDimension('A')->setWidth(13);
            $sheet->getColumnDimension('B')->setWidth(25);
            $sheet->getColumnDimension('C')->setWidth(18);
            $sheet->getStyle('A:C')->getAlignment()->setVertical('center')->setHorizontal('center');
            $sheet->getStyle('A1:C' . count($sales))
                ->getAlignment()->setWrapText(true);
            $sheet->getStyle('A1:C1')
                ->getFont()->setBold(true);
            $sheet->getStyle('A1:C1')
                ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFDCF0FF');
            $sheet->getStyle('A1:C1')->getBorders()
                ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $sheet->setSelectedCell('A1');
        }

        if ($this->displaySalesByBrands === '1' && !is_numeric($this->auto)) {
            $sales = $this->getSalesByBrands();
            if (is_numeric($this->auto) && !$sales && $autoExportDoNotSend) {
                return 0;
            }

            if ($this->auto === 'schedule' && !$sales && $scheduleDoNotSend) {
                return 0;
            }
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($this->module->l('Sales by Brands', 'ExportSales'));
            array_unshift($sales, array(
                $this->module->l('Brand ID', 'ExportSales'),
                $this->module->l('Brand Name', 'ExportSales'),
                $this->module->l('Total Price (Tax Excl.)', 'ExportSales')
            ));
            $sheet->fromArray($sales, null);
            $sheet->getDefaultRowDimension()->setRowHeight(30);
            $sheet->getColumnDimension('A')->setWidth(13);
            $sheet->getColumnDimension('B')->setWidth(25);
            $sheet->getColumnDimension('C')->setWidth(18);
            $sheet->getStyle('A:C')->getAlignment()->setVertical('center')->setHorizontal('center');
            $sheet->getStyle('A1:C' . count($sales))
                ->getAlignment()->setWrapText(true);
            $sheet->getStyle('A1:C1')
                ->getFont()->setBold(true);
            $sheet->getStyle('A1:C1')
                ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFDCF0FF');
            $sheet->getStyle('A1:C1')->getBorders()
                ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $sheet->setSelectedCell('A1');
        }

        if ($this->displaySalesBySuppliers === '1' && !is_numeric($this->auto)) {
            $sales = $this->getSalesBySuppliers();
            if (is_numeric($this->auto) && !$sales && $autoExportDoNotSend) {
                return 0;
            }

            if ($this->auto === 'schedule' && !$sales && $scheduleDoNotSend) {
                return 0;
            }
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($this->module->l('Sales by Suppliers', 'ExportSales'));
            array_unshift($sales, array(
                $this->module->l('Supplier ID', 'ExportSales'),
                $this->module->l('Supplier Name', 'ExportSales'),
                $this->module->l('Total Price (Tax Excl.)', 'ExportSales')
            ));
            $sheet->fromArray($sales, null);
            $sheet->getDefaultRowDimension()->setRowHeight(30);
            $sheet->getColumnDimension('A')->setWidth(13);
            $sheet->getColumnDimension('B')->setWidth(25);
            $sheet->getColumnDimension('C')->setWidth(18);
            $sheet->getStyle('A:C')->getAlignment()->setVertical('center')->setHorizontal('center');
            $sheet->getStyle('A1:C' . count($sales))
                ->getAlignment()->setWrapText(true);
            $sheet->getStyle('A1:C1')
                ->getFont()->setBold(true);
            $sheet->getStyle('A1:C1')
                ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFDCF0FF');
            $sheet->getStyle('A1:C1')->getBorders()
                ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $sheet->setSelectedCell('A1');
        }

        if ($this->displaySalesByAttributes === '1' && !is_numeric($this->auto)) {
            $sales = $this->getSalesByAttributes();
            if (is_numeric($this->auto) && !$sales && $autoExportDoNotSend) {
                return 0;
            }

            if ($this->auto === 'schedule' && !$sales && $scheduleDoNotSend) {
                return 0;
            }
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($this->module->l('Sales by Attributes', 'ExportSales'));
            array_unshift($sales, array(
                $this->module->l('Attribute Group Name', 'ExportSales'),
                $this->module->l('Attribute Name', 'ExportSales'),
                $this->module->l('Total Price (Tax Excl.)', 'ExportSales')
            ));
            $sheet->fromArray($sales, null);
            $sheet->getDefaultRowDimension()->setRowHeight(30);
            $sheet->getColumnDimension('A')->setWidth(25);
            $sheet->getColumnDimension('B')->setWidth(25);
            $sheet->getColumnDimension('C')->setWidth(18);
            $sheet->getStyle('A:C')->getAlignment()->setVertical('center')->setHorizontal('center');
            $sheet->getStyle('A1:C' . count($sales))
                ->getAlignment()->setWrapText(true);
            $sheet->getStyle('A1:C1')
                ->getFont()->setBold(true);
            $sheet->getStyle('A1:C1')
                ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFDCF0FF');
            $sheet->getStyle('A1:C1')->getBorders()
                ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $sheet->setSelectedCell('A1');
        }

        if ($this->displaySalesByFeatures === '1' && !is_numeric($this->auto)) {
            $sales = $this->getSalesByFeatures();
            if (is_numeric($this->auto) && !$sales && $autoExportDoNotSend) {
                return 0;
            }

            if ($this->auto === 'schedule' && !$sales && $scheduleDoNotSend) {
                return 0;
            }
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($this->module->l('Sales by Features', 'ExportSales'));
            array_unshift($sales, array(
                $this->module->l('Feature Name', 'ExportSales'),
                $this->module->l('Feature Value', 'ExportSales'),
                $this->module->l('Is Custom', 'ExportSales'),
                $this->module->l('Total Price (Tax Excl.)', 'ExportSales')
            ));
            $sheet->fromArray($sales, null);
            $sheet->getDefaultRowDimension()->setRowHeight(30);
            $sheet->getColumnDimension('A')->setWidth(25);
            $sheet->getColumnDimension('B')->setWidth(25);
            $sheet->getColumnDimension('C')->setWidth(13);
            $sheet->getColumnDimension('D')->setWidth(18);
            $sheet->getStyle('A:D')->getAlignment()->setVertical('center')->setHorizontal('center');
            $sheet->getStyle('A1:D' . count($sales))
                ->getAlignment()->setWrapText(true);
            $sheet->getStyle('A1:D1')
                ->getFont()->setBold(true);
            $sheet->getStyle('A1:D1')
                ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFDCF0FF');
            $sheet->getStyle('A1:D1')->getBorders()
                ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $sheet->setSelectedCell('A1');
        }

        if ($this->displaySalesByShops === '1' && !is_numeric($this->auto)) {
            $sales = $this->getSalesByShops();
            if (is_numeric($this->auto) && !$sales && $autoExportDoNotSend) {
                return 0;
            }

            if ($this->auto === 'schedule' && !$sales && $scheduleDoNotSend) {
                return 0;
            }
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($this->module->l('Sales by Shops', 'ExportSales'));
            array_unshift($sales, array(
                $this->module->l('Shop ID', 'ExportSales'),
                $this->module->l('Shop Name', 'ExportSales'),
                $this->module->l('Shop Group Name', 'ExportSales'),
                $this->module->l('Total Price (Tax Excl.)', 'ExportSales')
            ));
            $sheet->fromArray($sales, null);
            $sheet->getDefaultRowDimension()->setRowHeight(30);
            $sheet->getColumnDimension('A')->setWidth(13);
            $sheet->getColumnDimension('B')->setWidth(25);
            $sheet->getColumnDimension('C')->setWidth(25);
            $sheet->getColumnDimension('D')->setWidth(18);
            $sheet->getStyle('A:D')->getAlignment()->setVertical('center')->setHorizontal('center');
            $sheet->getStyle('A1:D' . count($sales))
                ->getAlignment()->setWrapText(true);
            $sheet->getStyle('A1:D1')
                ->getFont()->setBold(true);
            $sheet->getStyle('A1:D1')
                ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFDCF0FF');
            $sheet->getStyle('A1:D1')->getBorders()
                ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $sheet->setSelectedCell('A1');
        }

        $spreadsheet->setActiveSheetIndex(0);

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

        $id = Tools::getAdminToken($this->context->employee->id);
        $target_action = Tools::getValue('orders_target_action');

        if ($this->auto) {
            $fileName = $this->docName . '.xlsx';
            $writer->save($this->tempDir . $fileName);
            return $fileName;
        } elseif ($target_action !== 'download') {
            mkdir($this->outputDir . '/' . $id);
            $file = $this->outputDir . '/' . $id . '/' . $this->docName . '.xlsx';

            $writer->save($file);

            // If target action is either email or ftp
            if ($target_action === 'email') {
                $subject = $this->module->l('Your Sales (by Advanced Sales Reports module)', 'ExportSales');
//                $content = $this->module->l('The details are in the attachment.', 'ExportSales');
//                $this->sendEmail(explode(';', Tools::getValue('target_action_to_emails')), $subject, $content, $file);
                $this->sendPSEmail(explode(';', Tools::getValue('target_action_to_emails')), $subject, $file);
            } elseif ($target_action === 'ftp') {
                $ftp_type = Tools::getValue('orders_target_action_ftp_type');
                $ftp_mode = Tools::getValue('orders_target_action_ftp_mode');
                $ftp_url = Tools::getValue('orders_target_action_ftp_url');
                $ftp_port = Tools::getValue('orders_target_action_ftp_port');
                $ftp_username = Tools::getValue('orders_target_action_ftp_username');
                $ftp_password = Tools::getValue('orders_target_action_ftp_password');
                $ftp_folder = Tools::getValue('orders_target_action_ftp_folder');
                if ($ftp_folder) {
                    $ftp_folder .= '/' . $this->docName;
                }
//                $ftp_file_ext = Tools::getValue('orders_target_action_ftp_file_ext');

                $this->uploadToFTP($file, $ftp_type, $ftp_mode, $ftp_url, $ftp_port, $ftp_username, $ftp_password, $ftp_folder, 0);
            }

            unlink($file);
            rmdir($this->outputDir . '/' . $id);
        } else {
            $id = mt_rand() . uniqid();
            $filePath = $this->outputDir . '/' . $id . '.xlsx';

            $writer->save($filePath);
            header('Content-type: application/json');
            echo json_encode(array(
                'status' => 'ok',
                'id' => $id,
                'type' => 'excel',
                'name' => $this->docName . (Tools::getValue('orders_general_add_ts') && $this->filteredDate ? '_' . $this->filteredDate : '')
            ));
            if(isset($_GET['auto_export'])){
                if($_GET['auto_export'] == 'true'){
                    $file_name_exel =  $this->docName . (Tools::getValue('orders_general_add_ts') && $this->filteredDate ? '_' . $this->filteredDate : '');
                    $AdminOrdersExportSalesReportProController = Context::getContext()->link->getAdminLink('AdminOrdersExportSalesReportPro', true).'&action=getFile&id='.$id.'&type=excel&name='.$file_name_exel;
                    Tools::redirectAdmin($AdminOrdersExportSalesReportProController);
                }
            }

        }
    }


    public function generateCSV()
    {
        $orders = $this->getOrders();

        if (is_numeric($this->auto) && !$orders && Configuration::get('OXSRP_AUTOEXP_DNSEM')) {
            return 0;
        }

        if ($this->auto === 'schedule' && !$orders && Configuration::get('OXSRP_SCHDL_DNSEM')) {
            return 0;
        }

        $this->setHelperSql();

        $csv = '';

        if (!empty($orders)) {
            $counter = count($orders[0]);
            if ($this->orderId) {
                $counter--;
            }
            if ($this->productId) {
                $counter--;
            }
            if ($this->attributeId) {
                $counter--;
            }
            if ($this->shopId) {
                $counter--;
            }
            if ($this->categoryId) {
                $counter--;
            }
            if ($this->manufacturerId) {
                $counter--;
            }
            if ($this->supplierId) {
                $counter--;
            }
            if ($this->productRewriteLink) {
                $counter--;
            }
            if ($this->categoryRewriteLink) {
                $counter--;
            }
            if ($this->currencyIsoCode) {
                $counter--;
            }
            if ($this->currencyConversionRate) {
                $counter--;
            }
            if ($this->totalProducts) {
                $counter--;
            }
            if ($this->productQuantity) {
                $counter--;
            }
            if ($this->totalPriceTaxExcl) {
                $counter--;
            }
            if ($this->totalDiscountsTaxExcl) {
                $counter--;
            }

            if (isset($this->selectedColumns->order->id_order)) {
                $groupTotal = 0;
                $groupOrder = $orders[0][$this->selectedColumns->order->id_order];
            }

            if ($this->selectedColumns->order->profit_amount ||
                $this->selectedColumns->order->profit_margin ||
                $this->selectedColumns->order->profit_percentage) {
                $profits = true;
            } else {
                $profits = false;
            }

            if ($this->selectedColumns->order->net_profit_amount ||
                $this->selectedColumns->order->net_profit_margin ||
                $this->selectedColumns->order->net_profit_percentage) {
                $netProfits = true;
            } else {
                $netProfits = false;
            }

            if ($this->auto) {
                $dlm = $this->config['orders_csv_delimiter'];
                $encl = $this->config['orders_csv_enclosure'];
            } else {
                $dlm = Tools::getValue('orders_csv_delimiter');
                $encl = Tools::getValue('orders_csv_enclosure');
            }

            if ($dlm === 't') {
                $dlm = "\t";
            }

            if ($encl === 'none') {
                $encl = '';
            } elseif ($encl === 'quot') {
                $encl = '"';
            }

            $totals = array();
            $purchase = $sale = $netSale = 0;
            $reductionTotals = array(
                'full' => 0,
                'reduced' => 0
            );

            $headers = array_keys($orders[0]);
            $psp = 0;
            if ($this->purchaseSupplierPrice) {
                $psp++;
            }

            if ($this->displayHeader === '1') {
                $csv .= $encl . $this->module->l('Sales', 'ExportSales') . $encl . "\r\n";
                for ($i = 0; $i < $counter - $psp; $i++) {
                    $csv .= $encl . $headers[$i] . $encl . $dlm;
                }
                $csv = rtrim($csv, $dlm) . "\r\n";
            }

            $profitAmountNumber = $profitMarginNumber = $profitPercentageNumber = $netProfitAmountNumber = $netProfitMarginNumber = $netProfitPercentageNumber = $rPNumber = null;

            $groups = $this->getGroups();
            if ($this->ordersMerge === '1') {
                $j = 0;
                foreach ($groups as $group) {
                    for ($i = 0; $i < (int) $group['products']; ++$i) {
                        if ($this->noProduct && $i !== 0) {
                            if ($profits || $netProfits) {
                                $groupTotal += $orders[$j][$this->selectedColumns->product->purchase_supplier_price] * $orders[$j][$this->selectedColumns->product->product_quantity];
                                if ($this->displayTotals === '1') {
                                    $purchase += (float) $orders[$j][$this->selectedColumns->product->purchase_supplier_price] * $orders[$j][$this->selectedColumns->product->product_quantity] / $orders[$j][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                }
                            }
                            $j++;
                            continue;
                        }
                        $count = 0;
                        foreach ($orders[$j] as $k => $val) {
                            if ($count >= $counter) {
                                break;
                            }
                            $val = str_replace('"', '""', $val);
                            if (in_array($k, (array) $this->selectedColumns->product) ||
                                in_array($k, (array) $this->selectedColumns->category) ||
                                in_array($k, (array) $this->selectedColumns->manufacturer) ||
                                in_array($k, (array) $this->selectedColumns->supplier)) {
                                if (($val || $val === '0') && in_array($k, $this->moneyColumns)) {
                                    $curr = $this->displayCurrSymbo ? $this->curs->{$orders[$j][$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';
                                    $csv .= $encl . $curr . str_replace('.', $this->decimalSeparator, $val) . $encl . $dlm;
                                    if ($this->displayTotals === '1') {
                                        if (isset($totals[$count])) {
                                            $totals[$count]['val'] += (float) $val / $orders[$j][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                        } else {
                                            $totals[$count]['val'] = (float) $val / $orders[$j][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                            $totals[$count]['curr'] = (bool) $curr;
                                        }
                                    }
                                } elseif ($k === $this->selectedColumns->product->purchase_supplier_price) {
                                    if (!$this->purchaseSupplierPrice) {
                                        $curr = $this->displayCurrSymbol ? $this->curs->{$orders[$j][$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';
                                        $csv .= $encl . $curr . str_replace('.', $this->decimalSeparator, $val) . $encl . $dlm;
                                        if ($this->displayTotals === '1') {
                                            if (isset($totals[$count])) {
                                                $totals[$count]['val'] += (float) $val * $orders[$j][$this->selectedColumns->product->product_quantity] / $orders[$j][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                            } else {
                                                $totals[$count]['val'] = (float) $val * $orders[$j][$this->selectedColumns->product->product_quantity] / $orders[$j][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                                $totals[$count]['curr'] = (bool) $curr;
                                            }
                                        }
                                    }
                                    if ($profits || $netProfits) {
                                        if ($groupOrder !== $orders[$j][$this->selectedColumns->order->id_order]) {
                                            $totalProducts = $orders[$j - 1][$this->selectedColumns->order->total_products];
                                            $totalDiscountsTaxExcl = $orders[$j - 1][$this->selectedColumns->order->total_discounts_tax_excl];
                                            $curr = $this->displayCurrSymbol ? $this->curs->{$orders[$j - 1][$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';
                                            if ($this->selectedColumns->order->profit_amount) {
                                                $profit_amount = $totalProducts - $groupTotal;
                                                $csv = str_replace('profit_amount', $curr . str_replace('.', $this->decimalSeparator, (string) round($profit_amount, $this->fracPart)), $csv);
                                                if (!isset($profitAmountNumber)) {
                                                    $profitAmountNumber = array_search($this->selectedColumns->order->profit_amount, $headers);
                                                }
                                            }
                                            if ($this->selectedColumns->order->profit_margin) {
                                                $profit_margin = 100 * ($totalProducts - $groupTotal) / $totalProducts;
                                                $csv = str_replace('profit_margin', str_replace('.', $this->decimalSeparator, (string) round($profit_margin, $this->fracPart)) . '%', $csv);
                                                if (!isset($profitMarginNumber)) {
                                                    $profitMarginNumber = array_search($this->selectedColumns->order->profit_margin, $headers);
                                                }
                                            }
                                            if ($this->selectedColumns->order->profit_percentage) {
                                                $profit_percentage = 100 * ($totalProducts - $groupTotal) / $groupTotal;
                                                $csv = str_replace('profit_percentage', str_replace('.', $this->decimalSeparator, (string) round($profit_percentage, $this->fracPart)) . '%', $csv);
                                                if (!isset($profitPercentageNumber)) {
                                                    $profitPercentageNumber = array_search($this->selectedColumns->order->profit_percentage, $headers);
                                                }
                                            }
                                            if ($this->selectedColumns->order->net_profit_amount) {
                                                $net_profit_amount = $totalProducts - $totalDiscountsTaxExcl - $groupTotal;
                                                $csv = str_replace('net_profitt_amount', $curr . str_replace('.', $this->decimalSeparator, (string) round($net_profit_amount, $this->fracPart)), $csv);
                                                if (!isset($netProfitAmountNumber)) {
                                                    $netProfitAmountNumber = array_search($this->selectedColumns->order->net_profit_amount, $headers);
                                                }
                                            }
                                            if ($this->selectedColumns->order->net_profit_margin) {
                                                $net_profit_margin = 100 * ($totalProducts - $totalDiscountsTaxExcl - $groupTotal) / ($totalProducts - $totalDiscountsTaxExcl);
                                                $csv = str_replace('net_profitt_margin', str_replace('.', $this->decimalSeparator, (string) round($net_profit_margin, $this->fracPart)) . '%', $csv);
                                                if (!isset($netProfitMarginNumber)) {
                                                    $netProfitMarginNumber = array_search($this->selectedColumns->order->net_profit_margin, $headers);
                                                }
                                            }
                                            if ($this->selectedColumns->order->profit_percentage) {
                                                $net_profit_percentage = 100 * ($totalProducts - $totalDiscountsTaxExcl - $groupTotal) / $groupTotal;
                                                $csv = str_replace('net_profitt_percentage', str_replace('.', $this->decimalSeparator, (string) round($net_profit_percentage, $this->fracPart)) . '%', $csv);
                                                if (!isset($netProfitPercentageNumber)) {
                                                    $netProfitPercentageNumber = array_search($this->selectedColumns->order->net_profit_percentage, $headers);
                                                }
                                            }
                                            $groupTotal = 0;
                                            $groupOrder = $orders[$j][$this->selectedColumns->order->id_order];
                                            if ($this->displayTotals === '1') {
                                                if ($profits) {
                                                    $sale += $totalProducts / $orders[$j - 1][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                                }
                                                if ($netProfits) {
                                                    $netSale += ($totalProducts - $totalDiscountsTaxExcl) / $orders[$j - 1][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                                }
                                            }
                                        }
                                        $groupTotal += $val * $orders[$j][$this->selectedColumns->product->product_quantity];
                                        if ($this->displayTotals === '1') {
                                            $purchase += (float) $val * $orders[$j][$this->selectedColumns->product->product_quantity] / $orders[$j][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                        }
                                    }
                                } elseif ($k === $this->selectedColumns->product->product_quantity) {
                                    $csv .= $encl . $val . $encl . $dlm;
                                    if ($this->displayTotals === '1') {
                                        if (isset($totals[$count])) {
                                            $totals[$count]['val'] += $val;
                                        } else {
                                            $totals[$count]['val'] = $val;
                                            $totals[$count]['curr'] = 0;
                                        }
                                    }
                                } elseif ($k === $this->selectedColumns->product->reduction_percent) {
                                    $csv .= $encl . str_replace('.', $this->decimalSeparator, $val) . $encl . $dlm;
                                    if ($this->displayTotals === '1') {
                                        $reductionTotals['reduced'] += $orders[$j][$this->selectedColumns->product->total_price_tax_excl];
                                        $reductionTotals['full'] += 100 * $orders[$j][$this->selectedColumns->product->total_price_tax_excl] / (100 - $val);
                                    }
                                } elseif ($k === $this->selectedColumns->product->product_link) {
                                    $link = $this->context->link->getProductLink((int) $orders[$j][$this->selectedColumns->product->product_id], null, null, null, $this->langId);
                                    $csv .= $encl . $link . $encl . $dlm;
                                } elseif ($k === $this->selectedColumns->product->product_image_link) {
                                    // Get image data of the given product id
                                    $image = Image::getCover($orders[$j][$this->selectedColumns->product->product_id]);
                                    if ($image) {
                                        $img_link = $this->context->link->getImageLink($orders[$j][$this->selectedColumns->product->{'order_detail_lang.product_link_rewrite'}], $image['id_image'], $this->imageType);
                                        $csv .= $encl . $img_link . $encl . $dlm;
                                    } else {
                                        $csv .= $encl . $this->module->l('No Image Link', 'ExportSales') . $encl . $dlm;
                                    }
                                } elseif ($k === $this->selectedColumns->product->attribute_image_link) {
                                    if (method_exists('Image', 'getBestImageAttribute')) {
                                        // Get image data of the given product id
                                        $image = Image::getBestImageAttribute(
                                            $orders[$j][$this->selectedColumns->shop->id_shop],
                                            $this->langId,
                                            $orders[$j][$this->selectedColumns->product->product_id],
                                            $orders[$j][$this->selectedColumns->product->product_attribute_id]
                                        );
                                    } else {
                                        $image = Image::getImages(
                                            $this->langId,
                                            $orders[$j][$this->selectedColumns->product->product_id],
                                            $orders[$j][$this->selectedColumns->product->product_attribute_id]
                                        );
                                        $image = isset($image[0]) ? $image[0] : null;
                                    }
                                    if ($image) {
                                        $img_link = $this->context->link->getImageLink($orders[$j][$this->selectedColumns->product->{'order_detail_lang.product_link_rewrite'}], $image['id_image'], $this->imageType);
                                        $csv .= $encl . $img_link . $encl . $dlm;
                                    } else {
                                        $csv .= $encl . $this->module->l('No Image Link', 'ExportSales') . $encl . $dlm;
                                    }
                                } elseif ($k === $this->selectedColumns->category->category_link) {
                                    if ((int) $orders[$j][$this->selectedColumns->category->id_category]) {
                                        $link = $this->context->link->getCategoryLink((int) $orders[$j][$this->selectedColumns->category->id_category], null, $this->langId);
                                        $csv .= $encl . $link . $encl . $dlm;
                                    } else {
                                        $csv .= $encl . $encl . $dlm;
                                    }
                                } elseif ($k === $this->selectedColumns->category->category_image_link) {
                                    $cat_img_path = realpath(_PS_CAT_IMG_DIR_ . $orders[$j][$this->selectedColumns->category->id_category] . ($this->catImageType ? '-' . $this->catImageType : '') . '.jpg');
                                    if (file_exists($cat_img_path)) {
                                        if (method_exists($this->context->link, 'getCatImageLink')) {
                                            // Get image data of the given product id
                                            $cat_img_link = $this->context->link->getCatImageLink(
                                                $orders[$j][$this->selectedColumns->category->link_rewrite],
                                                $orders[$j][$this->selectedColumns->category->id_category],
                                                $this->imageType
                                            );
                                        } else {
                                            $cat_img_link = $this->context->link->getBaseLink() . 'c/'
                                                . $orders[$j][$this->selectedColumns->category->id_category] . ($this->imageType ? '-' . $this->imageType : '') . '/'
                                                . $orders[$j][$this->selectedColumns->category->link_rewrite] . '.jpg';
                                        }
                                        $csv .= $encl . $cat_img_link . $encl . $dlm;
                                    } else {
                                        $csv .= $encl . $this->module->l('No Image Link', 'ExportSales') . $encl . $dlm;
                                    }
                                } elseif ($k === $this->selectedColumns->manufacturer->manufacturer_link) {
                                    if ((int) $orders[$j][$this->selectedColumns->manufacturer->id_manufacturer]) {
                                        $link = $this->context->link->getManufacturerLink((int) $orders[$j][$this->selectedColumns->manufacturer->id_manufacturer], null, $this->langId);
                                        $csv .= $encl . $link . $encl . $dlm;
                                    } else {
                                        $csv .= $encl . $encl . $dlm;
                                    }
                                } elseif ($k === $this->selectedColumns->manufacturer->manufacturer_image_link) {
                                    $man_img_path = realpath(_PS_MANU_IMG_DIR_ . $orders[$j][$this->selectedColumns->manufacturer->id_manufacturer] . ($this->imageType ? '-' . $this->imageType : '') . '.jpg');
                                    if (file_exists($man_img_path)) {
                                        $man_img_link = $this->context->link->getBaseLink() . 'img/m/'
                                            . $orders[$j][$this->selectedColumns->manufacturer->id_manufacturer] . ($this->imageType ? '-' . $this->imageType : '') . '.jpg';
                                        $csv .= $encl . $man_img_link . $encl . $dlm;
                                    } else {
                                        $csv .= $encl . $this->module->l('No Image Link', 'ExportSales') . $encl . $dlm;
                                    }
                                } elseif ($k === $this->selectedColumns->supplier->supplier_link) {
                                    if ((int) $orders[$j][$this->selectedColumns->supplier->id_supplier]) {
                                        $link = $this->context->link->getSupplierLink((int) $orders[$j][$this->selectedColumns->supplier->id_supplier], null, $this->langId);
                                        $csv .= $encl . $link . $encl . $dlm;
                                    } else {
                                        $csv .= $encl . $encl . $dlm;
                                    }
                                } elseif ($k === $this->selectedColumns->supplier->supplier_image_link) {
                                    $supp_img_path = realpath(_PS_SUPP_IMG_DIR_ . $orders[$j][$this->selectedColumns->supplier->id_supplier] . ($this->imageType ? '-' . $this->imageType : '') . '.jpg');
                                    if (file_exists($supp_img_path)) {
                                        $supp_img_link = $this->context->link->getBaseLink() . 'img/su/'
                                            . $orders[$j][$this->selectedColumns->supplier->id_supplier] . ($this->imageType ? '-' . $this->imageType : '') . '.jpg';
                                        $csv .= $encl . $supp_img_link . $encl . $dlm;
                                    } else {
                                        $csv .= $encl . $this->module->l('No Image Link', 'ExportSales') . $encl . $dlm;
                                    }
                                } else {
                                    $csv .= $encl . $val . $encl . $dlm;
                                }
                            } elseif ($i === 0) {
                                if (($val || $val === '0') && in_array($k, $this->moneyColumns)) {
                                    $curr = $this->displayCurrSymbol ? $this->curs->{$orders[$j][$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';
                                    $csv .= $encl . $curr . str_replace('.', $this->decimalSeparator, $val) . $encl . $dlm;
                                    if ($this->displayTotals === '1') {
                                        if (isset($totals[$count])) {
                                            $totals[$count]['val'] += (float) $val / $orders[$j][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                        } else {
                                            $totals[$count]['val'] = (float) $val / $orders[$j][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                            $totals[$count]['curr'] = (bool) $curr;
                                        }
                                    }
                                } else {
                                    if (($profits || $netProfits) && (
                                            $k === $this->selectedColumns->order->profit_amount ||
                                            $k === $this->selectedColumns->order->profit_margin ||
                                            $k === $this->selectedColumns->order->profit_percentage ||
                                            $k === $this->selectedColumns->order->net_profit_amount ||
                                            $k === $this->selectedColumns->order->net_profit_margin ||
                                            $k === $this->selectedColumns->order->net_profit_percentage
                                        )) {
                                        if ($groupOrder !== $orders[$j][$this->selectedColumns->order->id_order]) {
                                            $curr = $this->displayCurrSymbol ? $this->curs->{$orders[$j - 1][$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';
                                            $totalProducts = $orders[$j - 1][$this->selectedColumns->order->total_products];
                                            $totalDiscountsTaxExcl = $orders[$j - 1][$this->selectedColumns->order->total_discounts_tax_excl];
                                            if ($this->selectedColumns->order->profit_amount) {
                                                $profit_amount = $totalProducts - $groupTotal;
                                                $csv = str_replace('profit_amount', $curr . str_replace('.', $this->decimalSeparator, (string) round($profit_amount, $this->fracPart)), $csv);
                                                if (!isset($profitAmountNumber)) {
                                                    $profitAmountNumber = array_search($this->selectedColumns->order->profit_amount, $headers);
                                                }
                                            }
                                            if ($this->selectedColumns->order->profit_margin) {
                                                $profit_margin = 100 * ($totalProducts - $groupTotal) / $totalProducts;
                                                $csv = str_replace('profit_margin', str_replace('.', $this->decimalSeparator, (string) round($profit_margin, $this->fracPart)) . '%', $csv);
                                                if (!isset($profitMarginNumber)) {
                                                    $profitMarginNumber = array_search($this->selectedColumns->order->profit_margin, $headers);
                                                }
                                            }
                                            if ($this->selectedColumns->order->profit_percentage) {
                                                $profit_percentage = 100 * ($totalProducts - $groupTotal) / $groupTotal;
                                                $csv = str_replace('profit_percentage', str_replace('.', $this->decimalSeparator, (string) round($profit_percentage, $this->fracPart)) . '%', $csv);
                                                if (!isset($profitPercentageNumber)) {
                                                    $profitPercentageNumber = array_search($this->selectedColumns->order->profit_percentage, $headers);
                                                }
                                            }
                                            if ($this->selectedColumns->order->net_profit_amount) {
                                                $net_profit_amount = $totalProducts - $totalDiscountsTaxExcl - $groupTotal;
                                                $csv = str_replace('net_profitt_amount', $curr . str_replace('.', $this->decimalSeparator, (string) round($net_profit_amount, $this->fracPart)), $csv);
                                                if (!isset($netProfitAmountNumber)) {
                                                    $netProfitAmountNumber = array_search($this->selectedColumns->order->net_profit_amount, $headers);
                                                }
                                            }
                                            if ($this->selectedColumns->order->net_profit_margin) {
                                                $net_profit_margin = 100 * ($totalProducts - $totalDiscountsTaxExcl - $groupTotal) / ($totalProducts - $totalDiscountsTaxExcl);
                                                $csv = str_replace('net_profitt_margin', str_replace('.', $this->decimalSeparator, (string) round($net_profit_margin, $this->fracPart)) . '%', $csv);
                                                if (!isset($netProfitMarginNumber)) {
                                                    $netProfitMarginNumber = array_search($this->selectedColumns->order->net_profit_margin, $headers);
                                                }
                                            }
                                            if ($this->selectedColumns->order->net_profit_percentage) {
                                                $net_profit_percentage = 100 * ($totalProducts - $totalDiscountsTaxExcl - $groupTotal) / $groupTotal;
                                                $csv = str_replace('net_profitt_percentage', str_replace('.', $this->decimalSeparator, (string) round($net_profit_percentage, $this->fracPart)) . '%', $csv);
                                                if (!isset($netProfitPercentageNumber)) {
                                                    $netProfitPercentageNumber = array_search($this->selectedColumns->order->net_profit_percentage, $headers);
                                                }
                                            }
                                            $groupTotal = 0;
                                            $groupOrder = $orders[$j][$this->selectedColumns->order->id_order];
                                            if ($this->displayTotals === '1') {
                                                if ($profits) {
                                                    $sale += $totalProducts / $orders[$j - 1][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                                }
                                                if ($netProfits) {
                                                    $netSale += ($totalProducts - $totalDiscountsTaxExcl) / $orders[$j - 1][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                                }
                                            }
                                        }
                                        if ($k === $this->selectedColumns->order->profit_amount) {
                                            $csv .= $encl . 'profit_amount' . $encl . $dlm;
                                        } elseif ($k === $this->selectedColumns->order->profit_margin) {
                                            $csv .= $encl . 'profit_margin' . $encl . $dlm;
                                        } elseif ($k === $this->selectedColumns->order->profit_percentage) {
                                            $csv .= $encl . 'profit_percentage' . $encl . $dlm;
                                        } elseif ($k === $this->selectedColumns->order->net_profit_amount) {
                                            $csv .= $encl . 'net_profitt_amount' . $encl . $dlm;
                                        } elseif ($k === $this->selectedColumns->order->net_profit_margin) {
                                            $csv .= $encl . 'net_profitt_margin' . $encl . $dlm;
                                        } elseif ($k === $this->selectedColumns->order->net_profit_percentage) {
                                            $csv .= $encl . 'net_profitt_percentage' . $encl . $dlm;
                                        }
                                    } else {
                                        if ($k === $this->selectedColumns->order->{'order_messages.message'}) {
                                            $val = html_entity_decode($val);
                                        }
                                        $csv .= $encl . str_replace('"', '""', $val) . $encl . $dlm;
                                    }
                                }
                            } else {
                                $csv .= $encl . $encl . $dlm;
                            }
                            $count++;
                        }
                        $csv = rtrim($csv, $dlm) . "\r\n";
                        ++$j;
                    }
                }
                $key = $j - 1;
            } else {
                if ($this->noProduct) {
                    $break_points = $this->getBreakPoints($groups);
                }
                foreach ($orders as $key => $value) {
                    if ($this->noProduct && !in_array($key, $break_points)) {
                        if ($profits || $netProfits) {
                            $groupTotal += $value[$this->selectedColumns->product->purchase_supplier_price] * $value[$this->selectedColumns->product->product_quantity];
                            if ($this->displayTotals === '1') {
                                $purchase += (float) $value[$this->selectedColumns->product->purchase_supplier_price] * $value[$this->selectedColumns->product->product_quantity] / $value[$this->selectedColumns->order->{'currency.conversion_rate'}];
                            }
                        }
                        continue;
                    }
                    $i = 0;
                    $totaler = true;
                    foreach ($value as $k => $val) {
                        if ($i >= $counter) {
                            break;
                        }
                        $val = str_replace('"', '""', $val);
                        if (($val || $val === '0') && in_array($k, $this->moneyColumns)) {
                            $curr = $this->displayCurrSymbol ? $this->curs->{$value[$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';
                            $csv .= $encl . $curr . str_replace('.', $this->decimalSeparator, $val) . $encl . $dlm;
                            if ($this->displayTotals === '1') {
                                if (!isset($totals[$i])) {
                                    $totals[$i]['val'] = (float) $val / $value[$this->selectedColumns->order->{'currency.conversion_rate'}];
                                    $totals[$i]['curr'] = (bool) $curr;
                                    $groupOrder2 = $groupOrder3 = $value[$this->selectedColumns->order->id_order];
                                } else {
                                    if (in_array($k, $this->orderMoneyColumns)) {
                                        if ($groupOrder2 !== $value[$this->selectedColumns->order->id_order]) {
                                            if ($totaler) {
                                                if ($groupOrder3 !== $value[$this->selectedColumns->order->id_order]) {
                                                    $totals[$i]['val'] += (float) $val / $value[$this->selectedColumns->order->{'currency.conversion_rate'}];
                                                    $groupOrder3 = $value[$this->selectedColumns->order->id_order];
                                                } else {
                                                    $groupOrder2 = $value[$this->selectedColumns->order->id_order];
                                                }
                                                $totaler = false;
                                            } else {
                                                $totals[$i]['val'] += (float) $val / $value[$this->selectedColumns->order->{'currency.conversion_rate'}];
                                            }
                                        }
                                    } else {
                                        $totals[$i]['val'] += (float) $val / $value[$this->selectedColumns->order->{'currency.conversion_rate'}];
                                    }
                                }
                            }
                        } elseif (($profits || $netProfits) && (
                                $k === $this->selectedColumns->order->profit_amount ||
                                $k === $this->selectedColumns->order->profit_margin ||
                                $k === $this->selectedColumns->order->profit_percentage ||
                                $k === $this->selectedColumns->order->net_profit_amount ||
                                $k === $this->selectedColumns->order->net_profit_margin ||
                                $k === $this->selectedColumns->order->net_profit_percentage
                            )) {
                            if ($value[$this->selectedColumns->order->id_order] !== $groupOrder) {
                                $totalProducts = $orders[$key - 1][$this->selectedColumns->order->total_products];
                                $totalDiscountsTaxExcl = $orders[$key - 1][$this->selectedColumns->order->total_discounts_tax_excl];
                                $curr = $this->displayCurrSymbol ? $this->curs->{$orders[$key - 1][$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';
                                if ($this->selectedColumns->order->profit_amount) {
                                    $profit_amount = $totalProducts - $groupTotal;
                                    $csv = str_replace('profit_amount', $curr . str_replace('.', $this->decimalSeparator, (string) round($profit_amount, $this->fracPart)), $csv);
                                    if (!isset($profitAmountNumber)) {
                                        $profitAmountNumber = array_search($this->selectedColumns->order->profit_amount, $headers);
                                    }
                                }
                                if ($this->selectedColumns->order->profit_margin) {
                                    $profit_margin = 100 * ($totalProducts - $groupTotal) / $totalProducts;
                                    $csv = str_replace('profit_margin', str_replace('.', $this->decimalSeparator, (string) round($profit_margin, $this->fracPart)) . '%', $csv);
                                    if (!isset($profitMarginNumber)) {
                                        $profitMarginNumber = array_search($this->selectedColumns->order->profit_margin, $headers);
                                    }
                                }
                                if ($this->selectedColumns->order->profit_percentage) {
                                    $profit_percentage = 100 * ($totalProducts - $groupTotal) / $groupTotal;
                                    $csv = str_replace('profit_percentage', str_replace('.', $this->decimalSeparator, (string) round($profit_percentage, $this->fracPart)) . '%', $csv);
                                    if (!isset($profitPercentageNumber)) {
                                        $profitPercentageNumber = array_search($this->selectedColumns->order->profit_percentage, $headers);
                                    }
                                }
                                if ($this->selectedColumns->order->net_profit_amount) {
                                    $net_profit_amount = $totalProducts - $totalDiscountsTaxExcl - $groupTotal;
                                    $csv = str_replace('net_profitt_amount', $curr . str_replace('.', $this->decimalSeparator, (string) round($net_profit_amount, $this->fracPart)), $csv);
                                    if (!isset($netProfitAmountNumber)) {
                                        $netProfitAmountNumber = array_search($this->selectedColumns->order->net_profit_amount, $headers);
                                    }
                                }
                                if ($this->selectedColumns->order->net_profit_margin) {
                                    $net_profit_margin = 100 * ($totalProducts - $totalDiscountsTaxExcl - $groupTotal) / ($totalProducts - $totalDiscountsTaxExcl);
                                    $csv = str_replace('net_profitt_margin', str_replace('.', $this->decimalSeparator, (string) round($net_profit_margin, $this->fracPart)) . '%', $csv);
                                    if (!isset($netProfitMarginNumber)) {
                                        $netProfitMarginNumber = array_search($this->selectedColumns->order->net_profit_margin, $headers);
                                    }
                                }
                                if ($this->selectedColumns->order->net_profit_percentage) {
                                    $net_profit_percentage = 100 * ($totalProducts - $totalDiscountsTaxExcl - $groupTotal) / $groupTotal;
                                    $csv = str_replace('net_profitt_percentage', str_replace('.', $this->decimalSeparator, (string) round($net_profit_percentage, $this->fracPart)) . '%', $csv);
                                    if (!isset($netProfitPercentageNumber)) {
                                        $netProfitPercentageNumber = array_search($this->selectedColumns->order->net_profit_percentage, $headers);
                                    }
                                }
                                $groupTotal = 0;
                                $groupOrder = $value[$this->selectedColumns->order->id_order];
                                if ($this->displayTotals === '1') {
                                    if ($profits) {
                                        $sale += $totalProducts / $orders[$key - 1][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                    }
                                    if ($netProfits) {
                                        $netSale += ($totalProducts - $totalDiscountsTaxExcl) / $orders[$key - 1][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                    }
                                }
                            }
                            if ($k === $this->selectedColumns->order->profit_amount) {
                                $csv .= $encl . 'profit_amount' . $encl . $dlm;
                            } elseif ($k === $this->selectedColumns->order->profit_margin) {
                                $csv .= $encl . 'profit_margin' . $encl . $dlm;
                            } elseif ($k === $this->selectedColumns->order->profit_percentage) {
                                $csv .= $encl . 'profit_percentage' . $encl . $dlm;
                            } elseif ($k === $this->selectedColumns->order->net_profit_amount) {
                                $csv .= $encl . 'net_profitt_amount' . $encl . $dlm;
                            } elseif ($k === $this->selectedColumns->order->net_profit_margin) {
                                $csv .= $encl . 'net_profitt_margin' . $encl . $dlm;
                            } elseif ($k === $this->selectedColumns->order->net_profit_percentage) {
                                $csv .= $encl . 'net_profitt_percentage' . $encl . $dlm;
                            }
                        } elseif ($k === $this->selectedColumns->product->purchase_supplier_price) {
                            if (!$this->purchaseSupplierPrice) {
                                $curr = $this->displayCurrSymbol ? $this->curs->{$value[$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';
                                $csv .= $encl . $curr . str_replace('.', $this->decimalSeparator, $val) . $encl . $dlm;
                                if ($this->displayTotals === '1') {
                                    if (isset($totals[$i])) {
                                        $totals[$i]['val'] += (float) $val * $value[$this->selectedColumns->product->product_quantity] / $value[$this->selectedColumns->order->{'currency.conversion_rate'}];
                                    } else {
                                        $totals[$i]['val'] = (float) $val * $value[$this->selectedColumns->product->product_quantity] / $value[$this->selectedColumns->order->{'currency.conversion_rate'}];
                                        $totals[$i]['curr'] = (bool) $curr;
                                    }
                                }
                            }
                            if ($profits || $netProfits) {
                                if ($groupOrder !== $value[$this->selectedColumns->order->id_order]) {
                                    $totalProducts = $orders[$key - 1][$this->selectedColumns->order->total_products];
                                    $totalDiscountsTaxExcl = $orders[$key - 1][$this->selectedColumns->order->total_discounts_tax_excl];
                                    $curr = $this->displayCurrSymbol ? $this->curs->{$orders[$key - 1][$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';
                                    if ($this->selectedColumns->order->profit_amount) {
                                        $profit_amount = $totalProducts - $groupTotal;
                                        $csv = str_replace('profit_amount', $curr . str_replace('.', $this->decimalSeparator, (string) round($profit_amount, $this->fracPart)), $csv);
                                        if (!isset($profitAmountNumber)) {
                                            $profitAmountNumber = array_search($this->selectedColumns->order->profit_amount, $headers);
                                        }
                                    }
                                    if ($this->selectedColumns->order->profit_margin) {
                                        $profit_margin = 100 * ($totalProducts - $groupTotal) / $totalProducts;
                                        $csv = str_replace('profit_margin', str_replace('.', $this->decimalSeparator, (string) round($profit_margin, $this->fracPart)) . '%', $csv);
                                        if (!isset($profitMarginNumber)) {
                                            $profitMarginNumber = array_search($this->selectedColumns->order->profit_margin, $headers);
                                        }
                                    }
                                    if ($this->selectedColumns->order->profit_percentage) {
                                        $profit_percentage = 100 * ($totalProducts - $groupTotal) / $groupTotal;
                                        $csv = str_replace('profit_percentage', str_replace('.', $this->decimalSeparator, (string) round($profit_percentage, $this->fracPart)) . '%', $csv);
                                        if (!isset($profitPercentageNumber)) {
                                            $profitPercentageNumber = array_search($this->selectedColumns->order->profit_percentage, $headers);
                                        }
                                    }
                                    if ($this->selectedColumns->order->net_profit_amount) {
                                        $net_profit_amount = $totalProducts - $totalDiscountsTaxExcl - $groupTotal;
                                        $csv = str_replace('net_profitt_amount', $curr . str_replace('.', $this->decimalSeparator, (string) round($net_profit_amount, $this->fracPart)), $csv);
                                        if (!isset($netProfitAmountNumber)) {
                                            $netProfitAmountNumber = array_search($this->selectedColumns->order->net_profit_amount, $headers);
                                        }
                                    }
                                    if ($this->selectedColumns->order->net_profit_margin) {
                                        $net_profit_margin = 100 * ($totalProducts - $totalDiscountsTaxExcl - $groupTotal) / ($totalProducts - $totalDiscountsTaxExcl);
                                        $csv = str_replace('net_profitt_margin', str_replace('.', $this->decimalSeparator, (string) round($net_profit_margin, $this->fracPart)) . '%', $csv);
                                        if (!isset($netProfitMarginNumber)) {
                                            $netProfitMarginNumber = array_search($this->selectedColumns->order->net_profit_margin, $headers);
                                        }
                                    }
                                    if ($this->selectedColumns->order->net_profit_percentage) {
                                        $net_profit_percentage = 100 * ($totalProducts - $totalDiscountsTaxExcl - $groupTotal) / $groupTotal;
                                        $csv = str_replace('net_profitt_percentage', str_replace('.', $this->decimalSeparator, (string) round($net_profit_percentage, $this->fracPart)) . '%', $csv);
                                        if (!isset($netProfitPercentageNumber)) {
                                            $netProfitPercentageNumber = array_search($this->selectedColumns->order->net_profit_percentage, $headers);
                                        }
                                    }
                                    $groupTotal = 0;
                                    $groupOrder = $value[$this->selectedColumns->order->id_order];
                                    if ($this->displayTotals === '1') {
                                        if ($profits) {
                                            $sale += $totalProducts / $orders[$key - 1][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                        }
                                        if ($netProfits) {
                                            $netSale += ($totalProducts - $totalDiscountsTaxExcl) / $orders[$key - 1][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                        }
                                    }
                                }
                                $groupTotal += $val * $value[$this->selectedColumns->product->product_quantity];
                                if ($this->displayTotals === '1') {
                                    $purchase += (float) $val * $value[$this->selectedColumns->product->product_quantity] / $value[$this->selectedColumns->order->{'currency.conversion_rate'}];
                                }
                            }
                        } elseif ($k === $this->selectedColumns->product->product_quantity) {
                            $csv .= $encl . $val . $encl . $dlm;
                            if ($this->displayTotals === '1') {
                                if (isset($totals[$i])) {
                                    $totals[$i]['val'] += $val;
                                } else {
                                    $totals[$i]['val'] = $val;
                                    $totals[$i]['curr'] = 0;
                                }
                            }
                        } elseif ($k === $this->selectedColumns->product->reduction_percent) {
                            $csv .= $encl . str_replace('.', $this->decimalSeparator, $val) . $encl . $dlm;
                            if ($this->displayTotals === '1') {
                                $reductionTotals['reduced'] += $value[$this->selectedColumns->product->total_price_tax_excl];
                                $reductionTotals['full'] += 100 * $value[$this->selectedColumns->product->total_price_tax_excl] / (100 - $val);
                                if (!isset($rPNumber)) {
                                    $rPNumber = $i;
                                }
                            }
                        } elseif ($k === $this->selectedColumns->product->product_link) {
                            $link = $this->context->link->getProductLink((int) $value[$this->selectedColumns->product->product_id], null, null, null, $this->langId);
                            $csv .= $encl . $link . $encl . $dlm;
                        } elseif ($k === $this->selectedColumns->product->product_image_link) {
                            // Get image data of the given product id
                            $image = Image::getCover($value[$this->selectedColumns->product->product_id]);
                            if ($image) {
                                $img_link = $this->context->link->getImageLink($value[$this->selectedColumns->product->{'order_detail_lang.product_link_rewrite'}], $image['id_image'], $this->imageType);
                                $csv .= $encl . $img_link . $encl . $dlm;
                            } else {
                                $csv .= $encl . $this->module->l('No Image Link', 'ExportSales') . $encl . $dlm;
                            }
                        } elseif ($k === $this->selectedColumns->product->attribute_image_link) {
                            if (method_exists('Image', 'getBestImageAttribute')) {
                                // Get image data of the given product id
                                $image = Image::getBestImageAttribute(
                                    $value[$this->selectedColumns->shop->id_shop],
                                    $this->langId,
                                    $value[$this->selectedColumns->product->product_id],
                                    $value[$this->selectedColumns->product->product_attribute_id]
                                );
                            } else {
                                $image = Image::getImages(
                                    $this->langId,
                                    $value[$this->selectedColumns->product->product_id],
                                    $value[$this->selectedColumns->product->product_attribute_id]
                                );
                                $image = isset($image[0]) ? $image[0] : null;
                            }
                            if ($image) {
                                $img_link = $this->context->link->getImageLink($value[$this->selectedColumns->product->{'order_detail_lang.product_link_rewrite'}], $image['id_image'], $this->imageType);
                                $csv .= $encl . $img_link . $encl . $dlm;
                            } else {
                                $csv .= $encl . $this->module->l('No Image Link', 'ExportSales') . $encl . $dlm;
                            }
                        } elseif ($k === $this->selectedColumns->category->category_link) {
                            if ((int) $value[$this->selectedColumns->category->id_category]) {
                                $link = $this->context->link->getCategoryLink((int) $value[$this->selectedColumns->category->id_category], null, $this->langId);
                                $csv .= $encl . $link . $encl . $dlm;
                            } else {
                                $csv .= $encl . $encl . $dlm;
                            }
                        } elseif ($k === $this->selectedColumns->category->category_image_link) {
                            $cat_img_path = realpath(_PS_CAT_IMG_DIR_ . $value[$this->selectedColumns->category->id_category] . ($this->catImageType ? '-' . $this->catImageType : '') . '.jpg');
                            if (file_exists($cat_img_path)) {
                                if (method_exists($this->context->link, 'getCatImageLink')) {
                                    // Get image data of the given product id
                                    $cat_img_link = $this->context->link->getCatImageLink(
                                        $value[$this->selectedColumns->category->link_rewrite],
                                        $value[$this->selectedColumns->category->id_category],
                                        $this->imageType
                                    );
                                } else {
                                    $cat_img_link = $this->context->link->getBaseLink() . 'c/'
                                        . $value[$this->selectedColumns->category->id_category] . ($this->imageType ? '-' . $this->imageType : '') . '/'
                                        . $value[$this->selectedColumns->category->link_rewrite] . '.jpg';
                                }
                                $csv .= $encl . $cat_img_link . $encl . $dlm;
                            } else {
                                $csv .= $encl . $this->module->l('No Image Link', 'ExportSales') . $encl . $dlm;
                            }
                        } elseif ($k === $this->selectedColumns->manufacturer->manufacturer_link) {
                            if ((int) $value[$this->selectedColumns->manufacturer->id_manufacturer]) {
                                $link = $this->context->link->getManufacturerLink((int) $value[$this->selectedColumns->manufacturer->id_manufacturer], null, $this->langId);
                                $csv .= $encl . $link . $encl . $dlm;
                            } else {
                                $csv .= $encl . $encl . $dlm;
                            }
                        } elseif ($k === $this->selectedColumns->manufacturer->manufacturer_image_link) {
                            $man_img_path = realpath(_PS_MANU_IMG_DIR_ . $value[$this->selectedColumns->manufacturer->id_manufacturer] . ($this->imageType ? '-' . $this->imageType : '') . '.jpg');
                            if (file_exists($man_img_path)) {
                                $man_img_link = $this->context->link->getBaseLink() . 'img/m/'
                                    . $value[$this->selectedColumns->manufacturer->id_manufacturer] . ($this->imageType ? '-' . $this->imageType : '') . '.jpg';
                                $csv .= $encl . $man_img_link . $encl . $dlm;
                            } else {
                                $csv .= $encl . $this->module->l('No Image Link', 'ExportSales') . $encl . $dlm;
                            }
                        } elseif ($k === $this->selectedColumns->supplier->supplier_link) {
                            if ((int) $value[$this->selectedColumns->supplier->id_supplier]) {
                                $link = $this->context->link->getSupplierLink((int) $value[$this->selectedColumns->supplier->id_supplier], null, $this->langId);
                                $csv .= $encl . $link . $encl . $dlm;
                            } else {
                                $csv .= $encl . $encl . $dlm;
                            }
                        } elseif ($k === $this->selectedColumns->supplier->supplier_image_link) {
                            $supp_img_path = realpath(_PS_SUPP_IMG_DIR_ . $value[$this->selectedColumns->supplier->id_supplier] . ($this->imageType ? '-' . $this->imageType : '') . '.jpg');
                            if (file_exists($supp_img_path)) {
                                $supp_img_link = $this->context->link->getBaseLink() . 'img/su/'
                                    . $value[$this->selectedColumns->supplier->id_supplier] . ($this->imageType ? '-' . $this->imageType : '') . '.jpg';
                                $csv .= $encl . $supp_img_link . $encl . $dlm;
                            } else {
                                $csv .= $encl . $this->module->l('No Image Link', 'ExportSales') . $encl . $dlm;
                            }
                        } else {
                            if ($k === $this->selectedColumns->order->{'order_messages.message'}) {
                                $val = html_entity_decode($val);
                            }
                            $csv .= $encl . str_replace('"', '""', $val) . $encl . $dlm;
                        }
                        ++$i;
                    }
                    $csv = rtrim($csv, $dlm) . "\r\n";
                }
            }

            $totalProducts = $orders[$key][$this->selectedColumns->order->total_products];
            $totalDiscountsTaxExcl = $orders[$key][$this->selectedColumns->order->total_discounts_tax_excl];
            $curr = $this->displayCurrSymbol ? $this->curs->{$orders[$key][$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';
            if (isset($this->selectedColumns->order->profit_amount)) {
                $profit_amount = $totalProducts - $groupTotal;
                $csv = str_replace('profit_amount', $curr . str_replace('.', $this->decimalSeparator, (string) round($profit_amount, $this->fracPart)), $csv);
            }
            if (isset($this->selectedColumns->order->profit_margin)) {
                $profit_margin = 100 * ($totalProducts - $groupTotal) / $totalProducts;
                $csv = str_replace('profit_margin', str_replace('.', $this->decimalSeparator, (string) round($profit_margin, $this->fracPart)) . '%', $csv);
            }
            if (isset($this->selectedColumns->order->profit_percentage)) {
                $profit_percentage = 100 * ($totalProducts - $groupTotal) / $groupTotal;
                $csv = str_replace('profit_percentage', str_replace('.', $this->decimalSeparator, (string) round($profit_percentage, $this->fracPart)) . '%', $csv);
            }
            if (isset($this->selectedColumns->order->net_profit_amount)) {
                $net_profit_amount = $totalProducts - $totalDiscountsTaxExcl - $groupTotal;
                $csv = str_replace('net_profitt_amount', $curr . str_replace('.', $this->decimalSeparator, (string) round($net_profit_amount, $this->fracPart)), $csv);
            }
            if (isset($this->selectedColumns->order->net_profit_margin)) {
                $net_profit_margin = 100 * ($totalProducts - $totalDiscountsTaxExcl - $groupTotal) / ($totalProducts - $totalDiscountsTaxExcl);
                $csv = str_replace('net_profitt_margin', str_replace('.', $this->decimalSeparator, (string) round($net_profit_margin, $this->fracPart)) . '%', $csv);
            }
            if (isset($this->selectedColumns->order->net_profit_percentage)) {
                $net_profit_percentage = 100 * ($totalProducts - $totalDiscountsTaxExcl - $groupTotal) / $groupTotal;
                $csv = str_replace('net_profitt_percentage', str_replace('.', $this->decimalSeparator, (string) round($net_profit_percentage, $this->fracPart)) . '%', $csv);
            }

            if ($this->displayTotals === '1') {
                if ($profits) {
                    $sale += $totalProducts / $orders[$key][$this->selectedColumns->order->{'currency.conversion_rate'}];
                }
                if ($netProfits) {
                    $netSale += ($totalProducts - $totalDiscountsTaxExcl) / $orders[$key][$this->selectedColumns->order->{'currency.conversion_rate'}];
                }

                if (isset($profitAmountNumber)) {
                    $val = $sale - $purchase;
                    $totals[$profitAmountNumber]['val'] = $val;
                    $totals[$profitAmountNumber]['curr'] = $this->displayCurrSymbol;
                }
                if (isset($profitMarginNumber)) {
                    $val = 100 * ($sale - $purchase) / $sale;
                    $totals[$profitMarginNumber]['val'] = $val . '%';
                }
                if (isset($profitPercentageNumber)) {
                    $val = 100 * ($sale - $purchase) / $purchase;
                    $totals[$profitPercentageNumber]['val'] = $val . '%';
                }
                if (isset($netProfitAmountNumber)) {
                    $val = $netSale - $purchase;
                    $totals[$netProfitAmountNumber]['val'] = $val;
                    $totals[$netProfitAmountNumber]['curr'] = $this->displayCurrSymbol;
                }
                if (isset($netProfitMarginNumber)) {
                    $val = 100 * ($netSale - $purchase) / $netSale;
                    $totals[$netProfitMarginNumber]['val'] = $val . '%';
                }
                if (isset($netProfitPercentageNumber)) {
                    $val = 100 * ($netSale - $purchase) / $purchase;
                    $totals[$netProfitPercentageNumber]['val'] = $val . '%';
                }
                if (isset($rPNumber)) {
                    $val = 100 * ($reductionTotals['full'] - $reductionTotals['reduced']) / $reductionTotals['full'];
                    $totals[$rPNumber]['val'] = $val . '%';
                }

                $def_curr = Configuration::get('OXSRP_DEF_CURR_SMBL') . ' ';

                for ($i = 0; $i < $counter - $psp; $i++) {
                    if (isset($totals[$i])) {
                        $csv .= $encl;
                        if (isset($totals[$i]['curr']) && $totals[$i]['curr']) {
                            $csv .= $def_curr;
                        }
                        if (Tools::substr($totals[$i]['val'], -1) === '%') {
                            $val = round(rtrim($totals[$i]['val'], '%'), $this->fracPart) . '%';
                        } else {
                            $val = round($totals[$i]['val'], $this->fracPart);
                        }
                        $csv .= str_replace('.', $this->decimalSeparator, $val) . $encl . $dlm;
                    } else {
                        $csv .= $encl . $encl . $dlm;
                    }
                }
                $csv = rtrim($csv, $dlm) . "\r\n";
            }

            if ($this->displayFooter === '1') {
                for ($i = 0; $i < $counter - $psp; $i++) {
                    $csv .= $encl . $headers[$i] . $encl . $dlm;
                }
                $csv = rtrim($csv, $dlm) . "\r\n";
            }

            if ($this->displayExplanations === '1') {
                $csv .= "\r\n";
                $explanations = array();
                if (isset($profitAmountNumber)) {
                    $explanations[$profitAmountNumber] = '*' . $this->module->l(' Gross Profit Amount = S - P. Sale price of an order minus total purchase price of products in that order (taxes excluded).', 'ExportSales');
                }
                if (isset($profitMarginNumber)) {
                    $explanations[$profitMarginNumber] = '*' . $this->module->l(' Gross Profit Margin = 100 * (S - P) / S. Sale price of an order minus total purchase price of products in that order divided by the sale price multiplied by 100 (taxes excluded).', 'ExportSales');
                }
                if (isset($profitPercentageNumber)) {
                    $explanations[$profitPercentageNumber] = '*' . $this->module->l(' Gross Profit Percentage = 100 * (S - P) / P. Sale price of an order minus total purchase price of products in that order divided by the purchase price multiplied by 100 (taxes excluded).', 'ExportSales');
                }
                if (isset($netProfitAmountNumber)) {
                    $explanations[$netProfitAmountNumber] = '*' . $this->module->l(' Net Profit Amount = S - D - P. Sale price of an order minus the discount of that order minus total purchase price of products in that order (taxes excluded).', 'ExportSales');
                }
                if (isset($netProfitMarginNumber)) {
                    $explanations[$netProfitMarginNumber] = '*' . $this->module->l(' Net Profit Margin = 100 * (S - D - P) / (S - D). Sale price of an order minus the discount of that order minus total purchase price of products in that order divided by the sale price minus the discount of that order multiplied by 100 (taxes excluded).', 'ExportSales');
                }
                if (isset($netProfitPercentageNumber)) {
                    $explanations[$netProfitPercentageNumber] = '*' . $this->module->l(' Net Profit Percentage = 100 * (S - D - P) / P. Sale price of an order minus the discount of that order minus total purchase price of products in that order divided by the purchase price multiplied by 100 (taxes excluded).', 'ExportSales');
                }
                if (!$this->purchaseSupplierPrice && $this->selectedColumns->product->purchase_supplier_price) {
                    $explanations[array_search($this->selectedColumns->product->purchase_supplier_price, $headers)] = '*' . $this->module->l(' Product Quantity = Product purchase price multiplied by product purchased quantity, then summed.', 'ExportSales');
                }

                for ($i = 0; $i < $counter - $psp; $i++) {
                    if (isset($explanations[$i])) {
                        $csv .= $encl . $explanations[$i] . $encl . $dlm;
                    } else {
                        $csv .= $encl . $encl . $dlm;
                    }
                }
                $csv = rtrim($csv, $dlm) . "\r\n";
            }

            if ($this->displayBestSellers === '1' && !is_numeric($this->auto)) {
                $csv .= "\r\n\r\n";
                if ($this->displayHeader === '1') {
                    $csv .= $encl . $this->module->l('Sales by Products', 'ExportSales') . $encl . $dlm . "\r\n";
                }
                $sales = $this->getBestSellers();
                array_unshift($sales, array(
                    $this->module->l('Product ID', 'ExportSales'),
                    $this->module->l('Product Reference', 'ExportSales'),
                    $this->module->l('Product Name', 'ExportSales'),
                    $this->module->l('Sold Quantity', 'ExportSales'),
                    $this->module->l('Total Profit (Tax Excl.)', 'ExportSales'),
                    $this->module->l('Total Price (Tax Excl.)', 'ExportSales'),
                    $this->module->l('Total Price (Tax Incl.)', 'ExportSales'),
                    $this->module->l('Total Paid (Tax Excl.)', 'ExportSales'),
                    $this->module->l('Total Paid (Tax Incl.)', 'ExportSales'),
                    $this->module->l('Total Really Paid', 'ExportSales'),
                ));
                foreach ($sales as $val) {
                    foreach ($val as $v) {
                        $csv .= $encl . $v . $encl . $dlm;
                    }
                    $csv .= "\r\n";
                }
            }

            if ($this->displayProductCombs === '1' && !is_numeric($this->auto)) {
                $csv .= "\r\n\r\n";
                if ($this->displayHeader === '1') {
                    $csv .= $encl . $this->module->l('Sales by Combinations', 'ExportSales') . $encl . $dlm . "\r\n";
                }
                $sales = $this->getProductCombs();
                array_unshift($sales, array(
                    $this->module->l('Product ID', 'ExportSales'),
                    $this->module->l('Product Name', 'ExportSales'),
                    $this->module->l('Combination ID', 'ExportSales'),
                    $this->module->l('Combination', 'ExportSales'),
                    $this->module->l('Sold Quantity', 'ExportSales'),
                    $this->module->l('Total Profit (Tax Excl.)', 'ExportSales'),
                    $this->module->l('Total Price (Tax Excl.)', 'ExportSales')
                ));
                foreach ($sales as $val) {
                    foreach ($val as $v) {
                        $csv .= $encl . $v . $encl . $dlm;
                    }
                    $csv .= "\r\n";
                }
            }

            if ($this->displayDailySales === '1' && !is_numeric($this->auto)) {
                $csv .= "\r\n\r\n";
                if ($this->displayHeader === '1') {
                    $csv .= $encl . $this->module->l('Daily Sales', 'ExportSales') . $encl . $dlm . "\r\n";
                }
                $sales = $this->getDailySales();
                array_unshift($sales, array(
                    $this->module->l('Date', 'ExportSales'),
                    $this->module->l('Sold Quantity', 'ExportSales'),
                    $this->module->l('Total Profit (Tax Excl.)', 'ExportSales'),
                    $this->module->l('Total Price (Tax Excl.)', 'ExportSales')
                ));
                foreach ($sales as $val) {
                    foreach ($val as $v) {
                        $csv .= $encl . $v . $encl . $dlm;
                    }
                    $csv .= "\r\n";
                }
            }

            if ($this->displayMonthlySales === '1' && !is_numeric($this->auto)) {
                $csv .= "\r\n\r\n";
                if ($this->displayHeader === '1') {
                    $csv .= $encl . $this->module->l('Monthly Sales', 'ExportSales') . $encl . $dlm . "\r\n";
                }
                $sales = $this->getMonthlySales();
                array_unshift($sales, array(
                    $this->module->l('Date', 'ExportSales'),
                    $this->module->l('Sold Quantity', 'ExportSales'),
                    $this->module->l('Total Profit (Tax Excl.)', 'ExportSales'),
                    $this->module->l('Total Price (Tax Excl.)', 'ExportSales')
                ));
                foreach ($sales as $val) {
                    foreach ($val as $v) {
                        $csv .= $encl . $v . $encl . $dlm;
                    }
                    $csv .= "\r\n";
                }
            }

            if ($this->displayTopCustomers === '1' && !is_numeric($this->auto)) {
                $csv .= "\r\n\r\n";
                if ($this->displayHeader === '1') {
                    $csv .= $encl . $this->module->l('Sales by Customers', 'ExportSales') . $encl . $dlm . "\r\n";
                }
                $sales = $this->getTopCustomers();
                array_unshift($sales, array(
                    $this->module->l('Customer ID', 'ExportSales'),
                    $this->module->l('Customer Email', 'ExportSales'),
                    $this->module->l('Customer Firstname', 'ExportSales'),
                    $this->module->l('Customer Lastname', 'ExportSales'),
                    $this->module->l('Number of Orders', 'ExportSales'),
                    $this->module->l('Total Orders (Tax Excl.)', 'ExportSales'),
                    $this->module->l('Total Orders with Discount (Tax Excl.)', 'ExportSales'),
                ));
                foreach ($sales as $val) {
                    foreach ($val as $v) {
                        $csv .= $encl . $v . $encl . $dlm;
                    }
                    $csv .= "\r\n";
                }
            }

            if ($this->displayPaymentMethods === '1' && !is_numeric($this->auto)) {
                $csv .= "\r\n\r\n";
                if ($this->displayHeader === '1') {
                    $csv .= $encl . $this->module->l('Sales by Payment Methods', 'ExportSales') . $encl . $dlm . "\r\n";
                }
                $sales = $this->getPaymentSales();
                array_unshift($sales, array(
                    $this->module->l('Payment', 'ExportSales'),
                    $this->module->l('Module', 'ExportSales'),
                    $this->module->l('Number of Orders', 'ExportSales'),
                    $this->module->l('Total Orders (Tax Excl.)', 'ExportSales'),
                    $this->module->l('Total Orders with Discount (Tax Excl.)', 'ExportSales'),
                    $this->module->l('Refunded Amount', 'ExportSales'),
                    $this->module->l('Refunded Amount ROCK (Tax Excl.)', 'ExportSales'),
                    $this->module->l('Refunded Amount ROCK (Tax Incl.)', 'ExportSales'),
                ));
                foreach ($sales as $val) {
                    foreach ($val as $v) {
                        $csv .= $encl . $v . $encl . $dlm;
                    }
                    $csv .= "\r\n";
                }
            }


            if ($this->displayPaymentMethods2 === '1' && !is_numeric($this->auto)) {
                $csv .= "\r\n\r\n";
                if ($this->displayHeader === '1') {
                    $csv .= $encl . $this->module->l('Sales by Payment Options', 'ExportSales') . $encl . $dlm . "\r\n";
                }
                $sales = $this->getPaymentSales2();
                array_unshift($sales, array(
                    $this->module->l('Payment', 'ExportSales'),
                    $this->module->l('Module', 'ExportSales'),
                    $this->module->l('Number of Orders', 'ExportSales'),
                    $this->module->l('Total Orders (Tax Excl.)', 'ExportSales'),
                    $this->module->l('Total Orders with Discount (Tax Excl.)', 'ExportSales'),
                    $this->module->l('Refunded Amount', 'ExportSales'),
                    $this->module->l('Refunded Amount ROCK (Tax Excl.)', 'ExportSales'),
                    $this->module->l('Refunded Amount ROCK (Tax Incl.)', 'ExportSales'),
                ));
                foreach ($sales as $val) {
                    foreach ($val as $v) {
                        $csv .= $encl . $v . $encl . $dlm;
                    }
                    $csv .= "\r\n";
                }
            }

            if ($this->displaySalesByCategories === '1' && !is_numeric($this->auto)) {
                $csv .= "\r\n\r\n";
                if ($this->displayHeader === '1') {
                    $csv .= $encl . $this->module->l('Sales by Cateogries', 'ExportSales') . $encl . $dlm . "\r\n";
                }
                $sales = $this->getSalesByCategories();
                array_unshift($sales, array(
                    $this->module->l('Category ID', 'ExportSales'),
                    $this->module->l('Category Name', 'ExportSales'),
                    $this->module->l('Total Price (Tax Excl.)', 'ExportSales')
                ));
                foreach ($sales as $val) {
                    foreach ($val as $v) {
                        $csv .= $encl . $v . $encl . $dlm;
                    }
                    $csv .= "\r\n";
                }
            }

            if ($this->displaySalesByBrands === '1' && !is_numeric($this->auto)) {
                $csv .= "\r\n\r\n";
                if ($this->displayHeader === '1') {
                    $csv .= $encl . $this->module->l('Sales by Brands', 'ExportSales') . $encl . $dlm . "\r\n";
                }
                $sales = $this->getSalesByBrands();
                array_unshift($sales, array(
                    $this->module->l('Brand ID', 'ExportSales'),
                    $this->module->l('Brand Name', 'ExportSales'),
                    $this->module->l('Total Price (Tax Excl.)', 'ExportSales')
                ));
                foreach ($sales as $val) {
                    foreach ($val as $v) {
                        $csv .= $encl . $v . $encl . $dlm;
                    }
                    $csv .= "\r\n";
                }
            }

            if ($this->displaySalesBySuppliers === '1' && !is_numeric($this->auto)) {
                $csv .= "\r\n\r\n";
                if ($this->displayHeader === '1') {
                    $csv .= $encl . $this->module->l('Sales by Suppliers', 'ExportSales') . $encl . $dlm . "\r\n";
                }
                $sales = $this->getSalesBySuppliers();
                array_unshift($sales, array(
                    $this->module->l('Supplier ID', 'ExportSales'),
                    $this->module->l('Supplier Name', 'ExportSales'),
                    $this->module->l('Total Price (Tax Excl.)', 'ExportSales')
                ));
                foreach ($sales as $val) {
                    foreach ($val as $v) {
                        $csv .= $encl . $v . $encl . $dlm;
                    }
                    $csv .= "\r\n";
                }
            }

            if ($this->displaySalesByAttributes === '1' && !is_numeric($this->auto)) {
                $csv .= "\r\n\r\n";
                if ($this->displayHeader === '1') {
                    $csv .= $encl . $this->module->l('Sales by Attributes', 'ExportSales') . $encl . $dlm . "\r\n";
                }
                $sales = $this->getSalesByAttributes();
                array_unshift($sales, array(
                    $this->module->l('Attribute Group Name', 'ExportSales'),
                    $this->module->l('Attribute Name', 'ExportSales'),
                    $this->module->l('Total Price (Tax Excl.)', 'ExportSales')
                ));
                foreach ($sales as $val) {
                    foreach ($val as $v) {
                        $csv .= $encl . $v . $encl . $dlm;
                    }
                    $csv .= "\r\n";
                }
            }

            if ($this->displaySalesByFeatures === '1' && !is_numeric($this->auto)) {
                $csv .= "\r\n\r\n";
                if ($this->displayHeader === '1') {
                    $csv .= $encl . $this->module->l('Sales by Features', 'ExportSales') . $encl . $dlm . "\r\n";
                }
                $sales = $this->getSalesByFeatures();
                array_unshift($sales, array(
                    $this->module->l('Feature Name', 'ExportSales'),
                    $this->module->l('Feature Value', 'ExportSales'),
                    $this->module->l('Is Custom', 'ExportSales'),
                    $this->module->l('Total Price (Tax Excl.)', 'ExportSales')
                ));
                foreach ($sales as $val) {
                    foreach ($val as $v) {
                        $csv .= $encl . $v . $encl . $dlm;
                    }
                    $csv .= "\r\n";
                }
            }

            if ($this->displaySalesByShops === '1' && !is_numeric($this->auto)) {
                $csv .= "\r\n\r\n";
                if ($this->displayHeader === '1') {
                    $csv .= $encl . $this->module->l('Sales by Shops', 'ExportSales') . $encl . $dlm . "\r\n";
                }
                $sales = $this->getSalesByShops();
                array_unshift($sales, array(
                    $this->module->l('Shop ID', 'ExportSales'),
                    $this->module->l('Shop Name', 'ExportSales'),
                    $this->module->l('Shop Group Name', 'ExportSales'),
                    $this->module->l('Total Price (Tax Excl.)', 'ExportSales')
                ));
                foreach ($sales as $val) {
                    foreach ($val as $v) {
                        $csv .= $encl . $v . $encl . $dlm;
                    }
                    $csv .= "\r\n";
                }
            }
        } else {
            $csv = $encl . $this->module->l('No Data', 'ExportSales') . $encl . "\r\n";
        }
//        $csv .= "\r\n\r\n";
//        $csv .= $encl . $this->module->l('Date', 'ExportSales') . ': ' . $encl . $dlm;
//        $csv .= $encl . date('Y-m-d H:i:s') . $encl;

        $id = Tools::getAdminToken($this->context->employee->id);
        $target_action = Tools::getValue('orders_target_action');

        if ($this->auto) {
            $fileName = $this->docName . '.csv';
            file_put_contents($this->tempDir . $fileName, "\xEF\xBB\xBF" . $csv);
            return $fileName;
        } elseif ($target_action !== 'download') {
            mkdir($this->outputDir . '/' . $id);
            $file = $this->outputDir . '/' . $id . '/' . $this->docName . '.csv';

            file_put_contents($file, "\xEF\xBB\xBF" . $csv);

            // If target action is either email or ftp
            if ($target_action === 'email') {
                $subject = $this->module->l('Your Sales (by Advanced Sales Reports module)', 'ExportSales');
//                $content = $this->module->l('The details are in the attachment.', 'ExportSales');
//                $this->sendEmail(explode(';', Tools::getValue('target_action_to_emails')), $subject, $content, $file);
                $this->sendPSEmail(explode(';', Tools::getValue('target_action_to_emails')), $subject, $file);
            } elseif ($target_action === 'ftp') {
                $ftp_type = Tools::getValue('orders_target_action_ftp_type');
                $ftp_mode = Tools::getValue('orders_target_action_ftp_mode');
                $ftp_url = Tools::getValue('orders_target_action_ftp_url');
                $ftp_port = Tools::getValue('orders_target_action_ftp_port');
                $ftp_username = Tools::getValue('orders_target_action_ftp_username');
                $ftp_password = Tools::getValue('orders_target_action_ftp_password');
                $ftp_folder = Tools::getValue('orders_target_action_ftp_folder');
                if ($ftp_folder) {
                    $ftp_folder .= '/' . $this->docName;
                }
//                $ftp_file_ext = Tools::getValue('orders_target_action_ftp_file_ext');

                $this->uploadToFTP($file, $ftp_type, $ftp_mode, $ftp_url, $ftp_port, $ftp_username, $ftp_password, $ftp_folder, 0);
            }

            unlink($file);
            rmdir($this->outputDir . '/' . $id);
        } else {
            $id = mt_rand() . uniqid();
            $filePath = $this->outputDir . '/' . $id . '.csv';

            file_put_contents($filePath, "\xEF\xBB\xBF" . $csv);
            header('Content-type: application/json');
            echo json_encode(array(
                'status' => 'ok',
                'id' => $id,
                'type' => 'csv',
                'name' => $this->docName . (Tools::getValue('orders_general_add_ts') && $this->filteredDate ? '_' . $this->filteredDate : '')
            ));
        }
    }

    public function generateHTML()
    {
        $orders = $this->getOrders();

        if (is_numeric($this->auto) && !$orders && Configuration::get('OXSRP_AUTOEXP_DNSEM')) {
            return 0;
        }

        if ($this->auto === 'schedule' && !$orders && Configuration::get('OXSRP_SCHDL_DNSEM')) {
            return 0;
        }

        $this->setHelperSql();

        $html = '<!DOCTYPE html>
                <html>
                    <head>
                    <meta charset="UTF-8">
                        <style>
                            @import url("https://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,700italic,300,400,700");
                            body {
                                font-family: "Open Sans", arial, sans-serif;
                                margin: 20px;
                                font-size: 14px;
                            }
                            table {
                                border: 1px solid #999;
                                border-collapse: collapse;
                            }
                            table th {
                                background-color: #DCF0FF;
                            }
                            table th, table td {
                                text-align: center;
                                border: 1px solid #aaa;
                                padding: 5px;
                            }
                            th div {
                                width: 100px;
                                margin: auto;
                            }
                            img {
                                height: 80px;
                            }
                            .totals {
                                height: 40px;
                                background: #E7FFD9;
                                color: #3C763D;
                                font-weight: bold;
                            }
                            .totals > td{
                                border: 2px solid #7CC67C;
                                font-size: 15px;
                            }
                        </style>
                    </head>
                    <body>
                ';
        if (!empty($orders)) {
            $counter = count($orders[0]);
            if ($this->orderId) {
                $counter--;
            }
            if ($this->productId) {
                $counter--;
            }
            if ($this->attributeId) {
                $counter--;
            }
            if ($this->shopId) {
                $counter--;
            }
            if ($this->categoryId) {
                $counter--;
            }
            if ($this->manufacturerId) {
                $counter--;
            }
            if ($this->supplierId) {
                $counter--;
            }
            if ($this->productRewriteLink) {
                $counter--;
            }
            if ($this->categoryRewriteLink) {
                $counter--;
            }
            if ($this->currencyIsoCode) {
                $counter--;
            }
            if ($this->currencyConversionRate) {
                $counter--;
            }
            if ($this->totalProducts) {
                $counter--;
            }
            if ($this->productQuantity) {
                $counter--;
            }
            if ($this->totalPriceTaxExcl) {
                $counter--;
            }
            if ($this->totalDiscountsTaxExcl) {
                $counter--;
            }

            if (isset($this->selectedColumns->order->id_order)) {
                $groupTotal = 0;
                $groupOrder = $orders[0][$this->selectedColumns->order->id_order];
            }

            if ($this->selectedColumns->order->profit_amount ||
                $this->selectedColumns->order->profit_margin ||
                $this->selectedColumns->order->profit_percentage) {
                $profits = true;
            } else {
                $profits = false;
            }
            if ($this->selectedColumns->order->net_profit_amount ||
                $this->selectedColumns->order->net_profit_margin ||
                $this->selectedColumns->order->net_profit_percentage) {
                $netProfits = true;
            } else {
                $netProfits = false;
            }

            $wideColumns = array(
                $this->selectedColumns->customer->email => 150,
                $this->selectedColumns->product->{'order_detail_lang.attributes'} => 200,
                $this->selectedColumns->product->{'product_features.features'} => 250,
                $this->selectedColumns->product->product_link => 300,
                $this->selectedColumns->product->product_image_link => 300,
                $this->selectedColumns->product->attribute_image_link => 300,
                $this->selectedColumns->payment->payment_details => 150,
                $this->selectedColumns->order->{'order_state_history.order_history'} => 250,
                $this->selectedColumns->order->{'order_messages.message'} => 250,
                $this->selectedColumns->category->category_link => 300,
                $this->selectedColumns->category->category_image_link => 300,
                $this->selectedColumns->category->description => 400,
                $this->selectedColumns->manufacturer->manufacturer_link => 300,
                $this->selectedColumns->manufacturer->manufacturer_image_link => 300,
                $this->selectedColumns->supplier->supplier_link => 300,
                $this->selectedColumns->supplier->supplier_image_link => 300,
            );

            $headers = array_keys($orders[0]);
            $psp = 0;
            if ($this->purchaseSupplierPrice) {
                $psp++;
            }
            if ($this->displayHeader === '1') {
                $html .= '<h2>' . $this->module->l('Sales', 'ExportSales') . '</h2><table>';
                $html .= '<tr>';
                for ($i = 0; $i < $counter - $psp; $i++) {
                    if (isset($wideColumns[$headers[$i]])) {
                        $html .= '<th><div style="width: ' . $wideColumns[$headers[$i]] . 'px;"><b>'
                            . $headers[$i] . '</b></div></th>';
                    } else {
                        $html .= '<th><div><b>' . $headers[$i] . '</b></div></th>';
                    }
                }
                $html .= '</tr>';
            }

            $totals = array();
            $purchase = $sale = $netSale = 0;
            $reductionTotals = array(
                'full' => 0,
                'reduced' => 0
            );

            $profitAmountNumber = $profitMarginNumber = $profitPercentageNumber = $netProfitAmountNumber = $netProfitMarginNumber = $netProfitPercentageNumber = $rPNumber = null;

            $groups = $this->getGroups();
            if ($this->ordersMerge === '1') {
                $j = 0;
                foreach ($groups as $group) {
                    for ($i = 0; $i < (int) $group['products']; ++$i) {
                        if ($this->noProduct && $i !== 0) {
                            if ($profits || $netProfits) {
                                $groupTotal += $orders[$j][$this->selectedColumns->product->purchase_supplier_price] * $orders[$j][$this->selectedColumns->product->product_quantity];
                                if ($this->displayTotals === '1') {
                                    $purchase += (float) $orders[$j][$this->selectedColumns->product->purchase_supplier_price] * $orders[$j][$this->selectedColumns->product->product_quantity] / $orders[$j][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                }
                            }
                            $j++;
                            continue;
                        }
                        $count = 0;
                        $html .= '<tr>';
                        foreach ($orders[$j] as $k => $val) {
                            if ($count >= $counter) {
                                break;
                            }
                            if (in_array($k, (array) $this->selectedColumns->product) ||
                                in_array($k, (array) $this->selectedColumns->category) ||
                                in_array($k, (array) $this->selectedColumns->manufacturer) ||
                                in_array($k, (array) $this->selectedColumns->supplier)) {
                                if (($val || $val === '0') && in_array($k, $this->moneyColumns)) {
                                    $curr = $this->displayCurrSymbol ? $this->curs->{$orders[$j][$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';
                                    $html .= '<td>' . $curr . str_replace('.', $this->decimalSeparator, $val) . '</td>';
                                    if ($this->displayTotals === '1') {
                                        if (isset($totals[$count])) {
                                            $totals[$count]['val'] += (float) $val / $orders[$j][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                        } else {
                                            $totals[$count]['val'] = (float) $val / $orders[$j][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                            $totals[$count]['curr'] = (bool) $curr;
                                        }
                                    }
                                } elseif ($k === $this->selectedColumns->product->purchase_supplier_price) {
                                    if (!$this->purchaseSupplierPrice) {
                                        $curr = $this->displayCurrSymbol ? $this->curs->{$orders[$j][$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';
                                        $html .= '<td>' . $curr . str_replace('.', $this->decimalSeparator, $val) . '</td>';
                                        if ($this->displayTotals === '1') {
                                            if (isset($totals[$count])) {
                                                $totals[$count]['val'] += (float) $val * $orders[$j][$this->selectedColumns->product->product_quantity] / $orders[$j][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                            } else {
                                                $totals[$count]['val'] = (float) $val * $orders[$j][$this->selectedColumns->product->product_quantity] / $orders[$j][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                                $totals[$count]['curr'] = (bool) $curr;
                                            }
                                        }
                                    }
                                    if ($profits || $netProfits) {
                                        if ($groupOrder !== $orders[$j][$this->selectedColumns->order->id_order]) {
                                            $totalProducts = $orders[$j - 1][$this->selectedColumns->order->total_products];
                                            $totalDiscountsTaxExcl = $orders[$j - 1][$this->selectedColumns->order->total_discounts_tax_excl];
                                            $curr = $this->displayCurrSymbol ? $this->curs->{$orders[$j - 1][$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';
                                            if ($this->selectedColumns->order->profit_amount) {
                                                $profit_amount = $totalProducts - $groupTotal;
                                                $html = str_replace('profit_amount', $curr . str_replace('.', $this->decimalSeparator, (string) round($profit_amount, $this->fracPart)), $html);
                                                if (!isset($profitAmountNumber)) {
                                                    $profitAmountNumber = array_search($this->selectedColumns->order->profit_amount, $headers);
                                                }
                                            }
                                            if ($this->selectedColumns->order->profit_margin) {
                                                $profit_margin = 100 * ($totalProducts - $groupTotal) / $totalProducts;
                                                $html = str_replace('profit_margin', str_replace('.', $this->decimalSeparator, (string) round($profit_margin, $this->fracPart)) . '%', $html);
                                                if (!isset($profitMarginNumber)) {
                                                    $profitMarginNumber = array_search($this->selectedColumns->order->profit_margin, $headers);
                                                }
                                            }
                                            if ($this->selectedColumns->order->profit_percentage) {
                                                $profit_percentage = 100 * ($totalProducts - $groupTotal) / $groupTotal;
                                                $html = str_replace('profit_percentage', str_replace('.', $this->decimalSeparator, (string) round($profit_percentage, $this->fracPart)) . '%', $html);
                                                if (!isset($profitPercentageNumber)) {
                                                    $profitPercentageNumber = array_search($this->selectedColumns->order->profit_percentage, $headers);
                                                }
                                            }
                                            if ($this->selectedColumns->order->net_profit_amount) {
                                                $net_profit_amount = $totalProducts - $totalDiscountsTaxExcl - $groupTotal;
                                                $html = str_replace('net_profitt_amount', $curr . str_replace('.', $this->decimalSeparator, (string) round($net_profit_amount, $this->fracPart)), $html);
                                                if (!isset($netProfitAmountNumber)) {
                                                    $netProfitAmountNumber = array_search($this->selectedColumns->order->net_profit_amount, $headers);
                                                }
                                            }
                                            if ($this->selectedColumns->order->net_profit_margin) {
                                                $net_profit_margin = 100 * ($totalProducts - $totalDiscountsTaxExcl - $groupTotal) / ($totalProducts - $totalDiscountsTaxExcl);
                                                $html = str_replace('net_profitt_margin', str_replace('.', $this->decimalSeparator, (string) round($net_profit_margin, $this->fracPart)) . '%', $html);
                                                if (!isset($netProfitMarginNumber)) {
                                                    $netProfitMarginNumber = array_search($this->selectedColumns->order->net_profit_margin, $headers);
                                                }
                                            }
                                            if ($this->selectedColumns->order->net_profit_percentage) {
                                                $net_profit_percentage = 100 * ($totalProducts - $totalDiscountsTaxExcl - $groupTotal) / $groupTotal;
                                                $html = str_replace('net_profitt_percentage', str_replace('.', $this->decimalSeparator, (string) round($net_profit_percentage, $this->fracPart)) . '%', $html);
                                                if (!isset($netProfitPercentageNumber)) {
                                                    $netProfitPercentageNumber = array_search($this->selectedColumns->order->net_profit_percentage, $headers);
                                                }
                                            }
                                            $groupTotal = 0;
                                            $groupOrder = $orders[$j][$this->selectedColumns->order->id_order];
                                            if ($this->displayTotals === '1') {
                                                if ($profits) {
                                                    $sale += $totalProducts / $orders[$j - 1][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                                }
                                                if ($netProfits) {
                                                    $netSale += ($totalProducts - $totalDiscountsTaxExcl) / $orders[$j - 1][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                                }
                                            }
                                        }
                                        $groupTotal += $val * $orders[$j][$this->selectedColumns->product->product_quantity];
                                        if ($this->displayTotals === '1') {
                                            $purchase += (float) $val * $orders[$j][$this->selectedColumns->product->product_quantity] / $orders[$j][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                        }
                                    }
                                } elseif ($k === $this->selectedColumns->product->product_quantity) {
                                    $html .= '<td>' . $val . '</td>';
                                    if ($this->displayTotals === '1') {
                                        if (isset($totals[$count])) {
                                            $totals[$count]['val'] += $val;
                                        } else {
                                            $totals[$count]['val'] = $val;
                                            $totals[$count]['curr'] = 0;
                                        }
                                    }
                                } elseif ($k === $this->selectedColumns->product->reduction_percent) {
                                    $html .= '<td>' . str_replace('.', $this->decimalSeparator, $val) . '</td>';
                                    if ($this->displayTotals === '1') {
                                        $reductionTotals['reduced'] += $orders[$j][$this->selectedColumns->product->total_price_tax_excl];
                                        $reductionTotals['full'] += 100 * $orders[$j][$this->selectedColumns->product->total_price_tax_excl] / (100 - $val);
                                        if (!isset($rPNumber)) {
                                            $rPNumber = $count;
                                        }
                                    }
                                } elseif ($k === $this->selectedColumns->product->product_image) {
                                    // Get image data of the given product id
                                    $image = Image::getCover($orders[$j][$this->selectedColumns->product->product_id]);
//                                    d($image);
                                    if ($image) {
                                        $img_link = $this->context->link->getImageLink($orders[$j][$this->selectedColumns->product->{'order_detail_lang.product_link_rewrite'}], (int) $image['id_image'], $this->imageTypeForFile);
                                        $html .= '<td><img src="' . $img_link
                                            . '" /></td>';
                                    } else {
                                        $html .= '<td><b>'
                                            . $this->module->l('No Image', 'ExportSales') . '</b></td>';
                                    }
                                } elseif ($k === $this->selectedColumns->product->attribute_image) {
                                    if (method_exists('Image', 'getBestImageAttribute')) {
                                        // Get image data of the given product id
                                        $image = Image::getBestImageAttribute(
                                            $orders[$j][$this->selectedColumns->shop->id_shop],
                                            $this->langId,
                                            $orders[$j][$this->selectedColumns->product->product_id],
                                            $orders[$j][$this->selectedColumns->product->product_attribute_id]
                                        );
                                    } else {
                                        $image = Image::getImages(
                                            $this->langId,
                                            $orders[$j][$this->selectedColumns->product->product_id],
                                            $orders[$j][$this->selectedColumns->product->product_attribute_id]
                                        );
                                        $image = isset($image[0]) ? $image[0] : null;
                                    }
                                    if ($image) {
                                        $img_link = $this->context->link->getImageLink($orders[$j][$this->selectedColumns->product->{'order_detail_lang.product_link_rewrite'}], $image['id_image'], $this->imageTypeForFile);
                                        $html .= '<td><img src="' . $img_link
                                            . '" /></td>';
                                    } else {
                                        $html .= '<td><b>'
                                            . $this->module->l('No Image', 'ExportSales') . '</b></td>';
                                    }
                                } elseif ($k === $this->selectedColumns->product->product_link) {
                                    $link = $this->context->link->getProductLink((int) $orders[$j][$this->selectedColumns->product->product_id], null, null, null, $this->langId);
                                    $html .= '<td><a href="' . $link . '">' . $link . '</a></td>';
                                } elseif ($k === $this->selectedColumns->product->product_image_link) {
                                    // Get image data of the given product id
                                    $image = Image::getCover($orders[$j][$this->selectedColumns->product->product_id]);
                                    if ($image) {
                                        $img_link = $this->context->link->getImageLink($orders[$j][$this->selectedColumns->product->{'order_detail_lang.product_link_rewrite'}], $image['id_image'], $this->imageType);
                                        $html .= '<td><a href="' . $img_link . '">' . $img_link . '</a></td>';
                                    } else {
                                        $html .= '<td><b>'
                                            . $this->module->l('No Image Link', 'ExportSales') . '</b></td>';
                                    }
                                } elseif ($k === $this->selectedColumns->product->attribute_image_link) {
                                    if (method_exists('Image', 'getBestImageAttribute')) {
                                        // Get image data of the given product id
                                        $image = Image::getBestImageAttribute(
                                            $orders[$j][$this->selectedColumns->shop->id_shop],
                                            $this->langId,
                                            $orders[$j][$this->selectedColumns->product->product_id],
                                            $orders[$j][$this->selectedColumns->product->product_attribute_id]
                                        );
                                    } else {
                                        $image = Image::getImages(
                                            $this->langId,
                                            $orders[$j][$this->selectedColumns->product->product_id],
                                            $orders[$j][$this->selectedColumns->product->product_attribute_id]
                                        );
                                        $image = isset($image[0]) ? $image[0] : null;
                                    }
                                    if ($image) {
                                        $img_link = $this->context->link->getImageLink($orders[$j][$this->selectedColumns->product->{'order_detail_lang.product_link_rewrite'}], $image['id_image'], $this->imageType);
                                        $html .= '<td><a href="' . $img_link . '">' . $img_link . '</a></td>';
                                    } else {
                                        $html .= '<td><b>'
                                            . $this->module->l('No Image Link', 'ExportSales') . '</b></td>';
                                    }
                                } elseif ($k === $this->selectedColumns->category->category_image) {
                                    $cat_img_path = realpath(_PS_CAT_IMG_DIR_ . $orders[$j][$this->selectedColumns->category->id_category] . ($this->catImageTypeForFile ? '-' . $this->catImageTypeForFile : '') . '.jpg');
                                    if (file_exists($cat_img_path)) {
                                        if (method_exists($this->context->link, 'getCatImageLink')) {
                                            // Get image data of the given product id
                                            $cat_img_link = $this->context->link->getCatImageLink(
                                                $orders[$j][$this->selectedColumns->category->link_rewrite],
                                                $orders[$j][$this->selectedColumns->category->id_category],
                                                $this->imageTypeForFile
                                            );
                                        } else {
                                            $cat_img_link = $this->context->link->getBaseLink() . 'c/'
                                                . $orders[$j][$this->selectedColumns->category->id_category] . '-'
                                                . $this->imageTypeForFile . '/'
                                                . $orders[$j][$this->selectedColumns->category->link_rewrite] . '.jpg';
                                        }
                                        $html .= '<td><img src="' . $cat_img_link . '" /></td>';
                                    } else {
                                        $html .= '<td><b>'
                                            . $this->module->l('No Image', 'ExportSales') . '</b></td>';
                                    }
                                } elseif ($k === $this->selectedColumns->category->category_link) {
                                    if ((int) $orders[$j][$this->selectedColumns->category->id_category]) {
                                        $link = $this->context->link->getCategoryLink((int) $orders[$j][$this->selectedColumns->category->id_category], null, $this->langId);
                                        $html .= '<td><a href="' . $link . '">' . $link . '</a></td>';
                                    } else {
                                        $html .= '<td></td>';
                                    }
                                } elseif ($k === $this->selectedColumns->category->category_image_link) {
                                    $cat_img_path = realpath(_PS_CAT_IMG_DIR_ . $orders[$j][$this->selectedColumns->category->id_category] . ($this->catImageType ? '-' . $this->catImageType : '') . '.jpg');
                                    if (file_exists($cat_img_path)) {
                                        if (method_exists($this->context->link, 'getCatImageLink')) {
                                            // Get image data of the given product id
                                            $cat_img_link = $this->context->link->getCatImageLink(
                                                $orders[$j][$this->selectedColumns->category->link_rewrite],
                                                $orders[$j][$this->selectedColumns->category->id_category],
                                                $this->imageType
                                            );
                                        } else {
                                            $cat_img_link = $this->context->link->getBaseLink() . 'c/'
                                                . $orders[$j][$this->selectedColumns->category->id_category] . ($this->imageType ? '-' . $this->imageType : '') . '/'
                                                . $orders[$j][$this->selectedColumns->category->link_rewrite] . '.jpg';
                                        }
                                        $html .= '<td><a href="' . $cat_img_link . '">' . $cat_img_link . '</a></td>';
                                    } else {
                                        $html .= '<td><b>'
                                            . $this->module->l('No Image Link', 'ExportSales') . '</b></td>';
                                    }
                                } elseif ($k === $this->selectedColumns->manufacturer->manufacturer_link) {
                                    if ((int) $orders[$j][$this->selectedColumns->manufacturer->id_manufacturer]) {
                                        $link = $this->context->link->getManufacturerLink((int) $orders[$j][$this->selectedColumns->manufacturer->id_manufacturer], null, $this->langId);
                                        $html .= '<td><a href="' . $link . '">' . $link . '</a></td>';
                                    } else {
                                        $html .= '<td></td>';
                                    }
                                } elseif ($k === $this->selectedColumns->manufacturer->manufacturer_image) {
                                    $man_img_path = realpath(_PS_MANU_IMG_DIR_ . $orders[$j][$this->selectedColumns->manufacturer->id_manufacturer] . ($this->imageTypeForFile ? '-' . $this->imageTypeForFile : '') . '.jpg');
                                    if (file_exists($man_img_path)) {
                                        $man_img_link = $this->context->link->getBaseLink() . 'img/m/'
                                            . $orders[$j][$this->selectedColumns->manufacturer->id_manufacturer] . ($this->imageTypeForFile ? '-' . $this->imageTypeForFile : '') . '.jpg';
                                        $html .= '<td><img src="' . $man_img_link . '" /></td>';
                                    } else {
                                        $html .= '<td><b>'
                                            . $this->module->l('No Image', 'ExportSales') . '</b></td>';
                                    }
                                } elseif ($k === $this->selectedColumns->manufacturer->manufacturer_image_link) {
                                    $man_img_path = realpath(_PS_MANU_IMG_DIR_ . $orders[$j][$this->selectedColumns->manufacturer->id_manufacturer] . ($this->imageType ? '-' . $this->imageType : '') . '.jpg');
                                    if (file_exists($man_img_path)) {
                                        $man_img_link = $this->context->link->getBaseLink() . 'img/m/'
                                            . $orders[$j][$this->selectedColumns->manufacturer->id_manufacturer] . ($this->imageType ? '-' . $this->imageType : '') . '.jpg';
                                        $html .= '<td><a href="' . $man_img_link . '">' . $man_img_link . '</a></td>';
                                    } else {
                                        $html .= '<td><b>'
                                            . $this->module->l('No Image Link', 'ExportSales') . '</b></td>';
                                    }
                                } elseif ($k === $this->selectedColumns->supplier->supplier_link) {
                                    if ((int) $orders[$j][$this->selectedColumns->supplier->id_supplier]) {
                                        $link = $this->context->link->getSupplierLink((int) $orders[$j][$this->selectedColumns->supplier->id_supplier], null, $this->langId);
                                        $html .= '<td><a href="' . $link . '">' . $link . '</a></td>';
                                    } else {
                                        $html .= '<td></td>';
                                    }
                                } elseif ($k === $this->selectedColumns->supplier->supplier_image) {
                                    $supp_img_path = realpath(_PS_SUPP_IMG_DIR_ . $orders[$j][$this->selectedColumns->supplier->id_supplier] . ($this->imageTypeForFile ? '-' . $this->imageTypeForFile : '') . '.jpg');
                                    if (file_exists($supp_img_path)) {
                                        $supp_img_link = $this->context->link->getBaseLink() . 'img/su/'
                                            . $orders[$j][$this->selectedColumns->supplier->id_supplier] . ($this->imageTypeForFile ? '-' . $this->imageTypeForFile : '') . '.jpg';
                                        $html .= '<td><img src="' . $supp_img_link . '" /></td>';
                                    } else {
                                        $html .= '<td><b>'
                                            . $this->module->l('No Image', 'ExportSales') . '</b></td>';
                                    }
                                } elseif ($k === $this->selectedColumns->supplier->supplier_image_link) {
                                    $supp_img_path = realpath(_PS_SUPP_IMG_DIR_ . $orders[$j][$this->selectedColumns->supplier->id_supplier] . ($this->imageType ? '-' . $this->imageType : '') . '.jpg');
                                    if (file_exists($supp_img_path)) {
                                        $supp_img_link = $this->context->link->getBaseLink() . 'img/su/'
                                            . $orders[$j][$this->selectedColumns->supplier->id_supplier] . ($this->imageType ? '-' . $this->imageType : '') . '.jpg';
                                        $html .= '<td><a href="' . $supp_img_link . '">' . $supp_img_link . '</a></td>';
                                    } else {
                                        $html .= '<td><b>'
                                            . $this->module->l('No Image Link', 'ExportSales') . '</b></td>';
                                    }
                                } else {
                                    $html .= '<td>' . $val . '</td>';
                                }
                            } elseif ($i === 0) {
                                if (($val || $val === '0') && in_array($k, $this->moneyColumns)) {
                                    $curr = $this->displayCurrSymbol ? $this->curs->{$orders[$j][$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';
                                    $html .= '<td rowspan="' . ($this->noProduct ? 1 : $group['products']) . '">' . $curr . str_replace('.', $this->decimalSeparator, $val) . '</td>';
                                    if ($this->displayTotals === '1') {
                                        if (isset($totals[$count])) {
                                            $totals[$count]['val'] += (float) $val / $orders[$j][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                        } else {
                                            $totals[$count]['val'] = (float) $val / $orders[$j][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                            $totals[$count]['curr'] = (bool) $curr;
                                        }
                                    }
                                } elseif (($profits || $netProfits) && (
                                        $k === $this->selectedColumns->order->profit_amount ||
                                        $k === $this->selectedColumns->order->profit_margin ||
                                        $k === $this->selectedColumns->order->profit_percentage ||
                                        $k === $this->selectedColumns->order->net_profit_amount ||
                                        $k === $this->selectedColumns->order->net_profit_margin ||
                                        $k === $this->selectedColumns->order->net_profit_percentage
                                    )) {
                                    if ($orders[$j][$this->selectedColumns->order->id_order] !== $groupOrder) {
                                        $totalProducts = $orders[$j - 1][$this->selectedColumns->order->total_products];
                                        $totalDiscountsTaxExcl = $orders[$j - 1][$this->selectedColumns->order->total_discounts_tax_excl];
                                        $curr = $this->displayCurrSymbol ? $this->curs->{$orders[$j - 1][$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';
                                        if ($this->selectedColumns->order->profit_amount) {
                                            $profit_amount = $totalProducts - $groupTotal;
                                            $html = str_replace('profit_amount', $curr . str_replace('.', $this->decimalSeparator, (string) round($profit_amount, $this->fracPart)), $html);
                                            if (!isset($profitAmountNumber)) {
                                                $profitAmountNumber = array_search($this->selectedColumns->order->profit_amount, $headers);
                                            }
                                        }
                                        if ($this->selectedColumns->order->profit_margin) {
                                            $profit_margin = 100 * ($totalProducts - $groupTotal) / $totalProducts;
                                            $html = str_replace('profit_margin', str_replace('.', $this->decimalSeparator, (string) round($profit_margin, $this->fracPart)) . '%', $html);
                                            if (!isset($profitMarginNumber)) {
                                                $profitMarginNumber = array_search($this->selectedColumns->order->profit_margin, $headers);
                                            }
                                        }
                                        if ($this->selectedColumns->order->profit_percentage) {
                                            $profit_percentage = 100 * ($totalProducts - $groupTotal) / $groupTotal;
                                            $html = str_replace('profit_percentage', str_replace('.', $this->decimalSeparator, (string) round($profit_percentage, $this->fracPart)) . '%', $html);
                                            if (!isset($profitPercentageNumber)) {
                                                $profitPercentageNumber = array_search($this->selectedColumns->order->profit_percentage, $headers);
                                            }
                                        }
                                        if ($this->selectedColumns->order->net_profit_amount) {
                                            $net_profit_amount = $totalProducts - $totalDiscountsTaxExcl - $groupTotal;
                                            $html = str_replace('net_profitt_amount', $curr . str_replace('.', $this->decimalSeparator, (string) round($net_profit_amount, $this->fracPart)), $html);
                                            if (!isset($netProfitAmountNumber)) {
                                                $netProfitAmountNumber = array_search($this->selectedColumns->order->net_profit_amount, $headers);
                                            }
                                        }
                                        if ($this->selectedColumns->order->net_profit_margin) {
                                            $net_profit_margin = 100 * ($totalProducts - $totalDiscountsTaxExcl - $groupTotal) / ($totalProducts - $totalDiscountsTaxExcl);
                                            $html = str_replace('net_profitt_margin', str_replace('.', $this->decimalSeparator, (string) round($net_profit_margin, $this->fracPart)) . '%', $html);
                                            if (!isset($netProfitMarginNumber)) {
                                                $netProfitMarginNumber = array_search($this->selectedColumns->order->net_profit_margin, $headers);
                                            }
                                        }
                                        if ($this->selectedColumns->order->net_profit_percentage) {
                                            $net_profit_percentage = 100 * ($totalProducts - $totalDiscountsTaxExcl - $groupTotal) / $groupTotal;
                                            $html = str_replace('net_profitt_percentage', str_replace('.', $this->decimalSeparator, (string) round($net_profit_percentage, $this->fracPart)) . '%', $html);
                                            if (!isset($netProfitPercentageNumber)) {
                                                $netProfitPercentageNumber = array_search($this->selectedColumns->order->net_profit_percentage, $headers);
                                            }
                                        }
                                        $groupTotal = 0;
                                        $groupOrder = $orders[$j][$this->selectedColumns->order->id_order];
                                        if ($this->displayTotals === '1') {
                                            if ($profits) {
                                                $sale += $totalProducts / $orders[$j - 1][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                            }
                                            if ($netProfits) {
                                                $netSale += ($totalProducts - $totalDiscountsTaxExcl) / $orders[$j - 1][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                            }
                                        }
                                    }
                                    if ($k === $this->selectedColumns->order->profit_amount) {
                                        $html .= '<td rowspan="' . ($this->noProduct ? 1 : $group['products']) . '">profit_amount</td>';
                                    } elseif ($k === $this->selectedColumns->order->profit_margin) {
                                        $html .= '<td rowspan="' . ($this->noProduct ? 1 : $group['products']) . '">profit_margin</td>';
                                    } elseif ($k === $this->selectedColumns->order->profit_percentage) {
                                        $html .= '<td rowspan="' . ($this->noProduct ? 1 : $group['products']) . '">profit_percentage</td>';
                                    } elseif ($k === $this->selectedColumns->order->net_profit_amount) {
                                        $html .= '<td rowspan="' . ($this->noProduct ? 1 : $group['products']) . '">net_profitt_amount</td>';
                                    } elseif ($k === $this->selectedColumns->order->net_profit_margin) {
                                        $html .= '<td rowspan="' . ($this->noProduct ? 1 : $group['products']) . '">net_profitt_margin</td>';
                                    } elseif ($k === $this->selectedColumns->order->net_profit_percentage) {
                                        $html .= '<td rowspan="' . ($this->noProduct ? 1 : $group['products']) . '">net_profitt_percentage</td>';
                                    }
                                } else {
                                    if ($k === $this->selectedColumns->order->{'order_messages.message'}) {
                                        $val = html_entity_decode($val);
                                    }
                                    $html .= '<td rowspan="' . ($this->noProduct ? 1 : $group['products']) . '">' . $val . '</td>';
                                }
                            }
                            $count++;
                        }
                        $html .= '</tr>';
                        ++$j;
                    }
                }
                $key = $j - 1;
            } else {
                if ($this->noProduct) {
                    $break_points = $this->getBreakPoints($groups);
                }
                foreach ($orders as $key => $value) {
                    if ($this->noProduct && !in_array($key, $break_points)) {
                        if ($profits || $netProfits) {
                            $groupTotal += $value[$this->selectedColumns->product->purchase_supplier_price] * $value[$this->selectedColumns->product->product_quantity];
                            if ($this->displayTotals === '1') {
                                $purchase += (float) $value[$this->selectedColumns->product->purchase_supplier_price] * $value[$this->selectedColumns->product->product_quantity] / $value[$this->selectedColumns->order->{'currency.conversion_rate'}];
                            }
                        }
                        continue;
                    }
                    $i = 0;
                    $totaler = true;
                    $html .= '<tr>';
                    foreach ($value as $k => $val) {
                        if ($i >= $counter) {
                            break;
                        }
                        if (($val || $val === '0') && in_array($k, $this->moneyColumns)) {
                            $curr = $this->displayCurrSymbol ? $this->curs->{$value[$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';
                            $html .= '<td>' . $curr . str_replace('.', $this->decimalSeparator, $val) . '</td>';
                            if ($this->displayTotals === '1') {
                                if (!isset($totals[$i])) {
                                    $totals[$i]['val'] = (float) $val / $value[$this->selectedColumns->order->{'currency.conversion_rate'}];
                                    $totals[$i]['curr'] = (bool) $curr;
                                    $groupOrder2 = $groupOrder3 = $value[$this->selectedColumns->order->id_order];
                                } else {
                                    if (in_array($k, $this->orderMoneyColumns)) {
                                        if ($groupOrder2 !== $value[$this->selectedColumns->order->id_order]) {
                                            if ($totaler) {
                                                if ($groupOrder3 !== $value[$this->selectedColumns->order->id_order]) {
                                                    $totals[$i]['val'] += (float) $val / $value[$this->selectedColumns->order->{'currency.conversion_rate'}];
                                                    $groupOrder3 = $value[$this->selectedColumns->order->id_order];
                                                } else {
                                                    $groupOrder2 = $value[$this->selectedColumns->order->id_order];
                                                }
                                                $totaler = false;
                                            } else {
                                                $totals[$i]['val'] += (float) $val / $value[$this->selectedColumns->order->{'currency.conversion_rate'}];
                                            }
                                        }
                                    } else {
                                        $totals[$i]['val'] += (float) $val / $value[$this->selectedColumns->order->{'currency.conversion_rate'}];
                                    }
                                }
                            }
                        } elseif (($profits || $netProfits) && (
                                $k === $this->selectedColumns->order->profit_amount ||
                                $k === $this->selectedColumns->order->profit_margin ||
                                $k === $this->selectedColumns->order->profit_percentage ||
                                $k === $this->selectedColumns->order->net_profit_amount ||
                                $k === $this->selectedColumns->order->net_profit_margin ||
                                $k === $this->selectedColumns->order->net_profit_percentage
                            )) {
                            if ($value[$this->selectedColumns->order->id_order] !== $groupOrder) {
                                $totalProducts = $orders[$key - 1][$this->selectedColumns->order->total_products];
                                $totalDiscountsTaxExcl = $orders[$key - 1][$this->selectedColumns->order->total_discounts_tax_excl];
                                $curr = $this->displayCurrSymbol ? $this->curs->{$orders[$key - 1][$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';
                                if ($this->selectedColumns->order->profit_amount) {
                                    $profit_amount = $totalProducts - $groupTotal;
                                    $html = str_replace('profit_amount', $curr . str_replace('.', $this->decimalSeparator, (string) round($profit_amount, $this->fracPart)), $html);
                                    if (!isset($profitAmountNumber)) {
                                        $profitAmountNumber = array_search($this->selectedColumns->order->profit_amount, $headers);
                                    }
                                }
                                if ($this->selectedColumns->order->profit_margin) {
                                    $profit_margin = 100 * ($totalProducts - $groupTotal) / $totalProducts;
                                    $html = str_replace('profit_margin', str_replace('.', $this->decimalSeparator, (string) round($profit_margin, $this->fracPart)) . '%', $html);
                                    if (!isset($profitMarginNumber)) {
                                        $profitMarginNumber = array_search($this->selectedColumns->order->profit_margin, $headers);
                                    }
                                }
                                if ($this->selectedColumns->order->profit_percentage) {
                                    $profit_percentage = 100 * ($totalProducts - $groupTotal) / $groupTotal;
                                    $html = str_replace('profit_percentage', str_replace('.', $this->decimalSeparator, (string) round($profit_percentage, $this->fracPart)) . '%', $html);
                                    if (!isset($profitPercentageNumber)) {
                                        $profitPercentageNumber = array_search($this->selectedColumns->order->profit_percentage, $headers);
                                    }
                                }
                                if ($this->selectedColumns->order->net_profit_amount) {
                                    $net_profit_amount = $totalProducts - $totalDiscountsTaxExcl - $groupTotal;
                                    $html = str_replace('net_profitt_amount', $curr . str_replace('.', $this->decimalSeparator, (string) round($net_profit_amount, $this->fracPart)), $html);
                                    if (!isset($netProfitAmountNumber)) {
                                        $netProfitAmountNumber = array_search($this->selectedColumns->order->net_profit_amount, $headers);
                                    }
                                }
                                if ($this->selectedColumns->order->net_profit_margin) {
                                    $net_profit_margin = 100 * ($totalProducts - $totalDiscountsTaxExcl - $groupTotal) / ($totalProducts - $totalDiscountsTaxExcl);
                                    $html = str_replace('net_profitt_margin', str_replace('.', $this->decimalSeparator, (string) round($net_profit_margin, $this->fracPart)) . '%', $html);
                                    if (!isset($netProfitMarginNumber)) {
                                        $netProfitMarginNumber = array_search($this->selectedColumns->order->net_profit_margin, $headers);
                                    }
                                }
                                if ($this->selectedColumns->order->net_profit_percentage) {
                                    $net_profit_percentage = 100 * ($totalProducts - $totalDiscountsTaxExcl - $groupTotal) / $groupTotal;
                                    $html = str_replace('net_profitt_percentage', str_replace('.', $this->decimalSeparator, (string) round($net_profit_percentage, $this->fracPart)) . '%', $html);
                                    if (!isset($netProfitPercentageNumber)) {
                                        $netProfitPercentageNumber = array_search($this->selectedColumns->order->net_profit_percentage, $headers);
                                    }
                                }
                                $groupTotal = 0;
                                $groupOrder = $value[$this->selectedColumns->order->id_order];
                                if ($this->displayTotals === '1') {
                                    if ($profits) {
                                        $sale += $totalProducts / $orders[$key - 1][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                    }
                                    if ($netProfits) {
                                        $netSale += ($totalProducts - $totalDiscountsTaxExcl) / $orders[$key - 1][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                    }
                                }
                            }
                            if ($k === $this->selectedColumns->order->profit_amount) {
                                $html .= '<td>profit_amount</td>';
                            } elseif ($k === $this->selectedColumns->order->profit_margin) {
                                $html .= '<td>profit_margin</td>';
                            } elseif ($k === $this->selectedColumns->order->profit_percentage) {
                                $html .= '<td>profit_percentage</td>';
                            } elseif ($k === $this->selectedColumns->order->net_profit_amount) {
                                $html .= '<td>net_profitt_amount</td>';
                            } elseif ($k === $this->selectedColumns->order->net_profit_margin) {
                                $html .= '<td>net_profitt_margin</td>';
                            } elseif ($k === $this->selectedColumns->order->net_profit_percentage) {
                                $html .= '<td>net_profitt_percentage</td>';
                            }
                        } elseif ($k === $this->selectedColumns->product->purchase_supplier_price) {
                            if (!$this->purchaseSupplierPrice) {
                                $curr = $this->displayCurrSymbol ? $this->curs->{$value[$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';
                                $html .= '<td>' . $curr . str_replace('.', $this->decimalSeparator, $val) . '</td>';
                                if ($this->displayTotals === '1') {
                                    if (isset($totals[$i])) {
                                        $totals[$i]['val'] += (float) $val * $value[$this->selectedColumns->product->product_quantity] / $value[$this->selectedColumns->order->{'currency.conversion_rate'}];
                                    } else {
                                        $totals[$i]['val'] = (float) $val * $value[$this->selectedColumns->product->product_quantity] / $value[$this->selectedColumns->order->{'currency.conversion_rate'}];
                                        $totals[$i]['curr'] = (bool) $curr;
                                    }
                                }
                            }
                            if ($profits || $netProfits) {
                                if ($groupOrder !== $value[$this->selectedColumns->order->id_order]) {
                                    $totalProducts = $orders[$key - 1][$this->selectedColumns->order->total_products];
                                    $totalDiscountsTaxExcl = $orders[$key - 1][$this->selectedColumns->order->total_discounts_tax_excl];
                                    $curr = $this->displayCurrSymbol ? $this->curs->{$orders[$key - 1][$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';

                                    if ($this->selectedColumns->order->profit_amount) {
                                        $profit_amount = $totalProducts - $groupTotal;
                                        $curr = $this->displayCurrSymbol ? $this->curs->{$orders[$key - 1][$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';
                                        $html = str_replace('profit_amount', $curr . str_replace('.', $this->decimalSeparator, (string) round($profit_amount, $this->fracPart)), $html);
                                        if (!isset($profitAmountNumber)) {
                                            $profitAmountNumber = array_search($this->selectedColumns->order->profit_amount, $headers);
                                        }
                                    }
                                    if ($this->selectedColumns->order->profit_margin) {
                                        $profit_margin = 100 * ($totalProducts - $groupTotal) / $totalProducts;
                                        $html = str_replace('profit_margin', str_replace('.', $this->decimalSeparator, (string) round($profit_margin, $this->fracPart)) . '%', $html);
                                        if (!isset($profitMarginNumber)) {
                                            $profitMarginNumber = array_search($this->selectedColumns->order->profit_margin, $headers);
                                        }
                                    }
                                    if ($this->selectedColumns->order->profit_percentage) {
                                        $profit_percentage = 100 * ($totalProducts - $groupTotal) / $groupTotal;
                                        $html = str_replace('profit_percentage', str_replace('.', $this->decimalSeparator, (string) round($profit_percentage, $this->fracPart)) . '%', $html);
                                        if (!isset($profitPercentageNumber)) {
                                            $profitPercentageNumber = array_search($this->selectedColumns->order->profit_percentage, $headers);
                                        }
                                    }

                                    if ($this->selectedColumns->order->net_profit_amount) {
                                        $net_profit_amount = $totalProducts - $totalDiscountsTaxExcl - $groupTotal;
                                        $curr = $this->displayCurrSymbol ? $this->curs->{$orders[$key - 1][$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';
                                        $html = str_replace('net_profitt_amount', $curr . str_replace('.', $this->decimalSeparator, (string) round($net_profit_amount, $this->fracPart)), $html);
                                        if (!isset($netProfitAmountNumber)) {
                                            $netProfitAmountNumber = array_search($this->selectedColumns->order->net_profit_amount, $headers);
                                        }
                                    }
                                    if ($this->selectedColumns->order->net_profit_margin) {
                                        $net_profit_margin = 100 * ($totalProducts - $totalDiscountsTaxExcl - $groupTotal) / ($totalProducts - $totalDiscountsTaxExcl);
                                        $html = str_replace('net_profitt_margin', str_replace('.', $this->decimalSeparator, (string) round($net_profit_margin, $this->fracPart)) . '%', $html);
                                        if (!isset($netProfitMarginNumber)) {
                                            $netProfitMarginNumber = array_search($this->selectedColumns->order->net_profit_margin, $headers);
                                        }
                                    }
                                    if ($this->selectedColumns->order->net_profit_percentage) {
                                        $net_profit_percentage = 100 * ($totalProducts - $totalDiscountsTaxExcl - $groupTotal) / $groupTotal;
                                        $html = str_replace('net_profitt_percentage', str_replace('.', $this->decimalSeparator, (string) round($net_profit_percentage, $this->fracPart)) . '%', $html);
                                        if (!isset($netProfitPercentageNumber)) {
                                            $netProfitPercentageNumber = array_search($this->selectedColumns->order->net_profit_percentage, $headers);
                                        }
                                    }
                                    $groupTotal = 0;
                                    $groupOrder = $value[$this->selectedColumns->order->id_order];
                                    if ($this->displayTotals === '1') {
                                        if ($profits) {
                                            $sale += $totalProducts / $orders[$key - 1][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                        }
                                        if ($netProfits) {
                                            $netSale += ($totalProducts - $totalDiscountsTaxExcl) / $orders[$key - 1][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                        }
                                    }
                                }
                                $groupTotal += $val * $value[$this->selectedColumns->product->product_quantity];
                                if ($this->displayTotals === '1') {
                                    $purchase += (float) $val * $value[$this->selectedColumns->product->product_quantity] / $value[$this->selectedColumns->order->{'currency.conversion_rate'}];
                                }
                            }
                        } elseif ($k === $this->selectedColumns->product->product_quantity) {
                            $html .= '<td>' . $val . '</td>';
                            if ($this->displayTotals === '1') {
                                if (isset($totals[$i])) {
                                    $totals[$i]['val'] += $val;
                                } else {
                                    $totals[$i]['val'] = $val;
                                    $totals[$i]['curr'] = 0;
                                }
                            }
                        } elseif ($k === $this->selectedColumns->product->reduction_percent) {
                            $html .= '<td>' . str_replace('.', $this->decimalSeparator, $val) . '</td>';
                            if ($this->displayTotals === '1') {
                                $reductionTotals['reduced'] += $value[$this->selectedColumns->product->total_price_tax_excl];
                                $reductionTotals['full'] += 100 * $value[$this->selectedColumns->product->total_price_tax_excl] / (100 - $val);
                                if (!isset($rPNumber)) {
                                    $rPNumber = $count;
                                }
                            }
                        } elseif ($k === $this->selectedColumns->product->product_image) {
                            $image = Image::getCover($value[$this->selectedColumns->product->product_id]);
                            if ($image) {
                                $img_link = $this->context->link->getImageLink($value[$this->selectedColumns->product->{'order_detail_lang.product_link_rewrite'}], $image['id_image'], $this->imageTypeForFile);
                                $html .= '<td><img src="' . $img_link . '" /></td>';
                            } else {
                                $html .= '<td><b>'
                                    . $this->module->l('No Image', 'ExportSales') . '</b></td>';
                            }
                        } elseif ($k === $this->selectedColumns->product->attribute_image) {
                            if (method_exists('Image', 'getBestImageAttribute')) {
                                // Get image data of the given product id
                                $image = Image::getBestImageAttribute(
                                    $value[$this->selectedColumns->shop->id_shop],
                                    $this->langId,
                                    $value[$this->selectedColumns->product->product_id],
                                    $value[$this->selectedColumns->product->product_attribute_id]
                                );
                            } else {
                                $image = Image::getImages(
                                    $this->langId,
                                    $value[$this->selectedColumns->product->product_id],
                                    $value[$this->selectedColumns->product->product_attribute_id]
                                );
                                $image = isset($image[0]) ? $image[0] : null;
                            }
                            if ($image) {
                                $img_link = $this->context->link->getImageLink($value[$this->selectedColumns->product->{'order_detail_lang.product_link_rewrite'}], $image['id_image'], $this->imageTypeForFile);
                                $html .= '<td><img src="' . $img_link . '" /></td>';
                            } else {
                                $html .= '<td><b>'
                                    . $this->module->l('No Image', 'ExportSales') . '</b></td>';
                            }
                        } elseif ($k === $this->selectedColumns->product->product_link) {
                            $link = $this->context->link->getProductLink((int) $value[$this->selectedColumns->product->product_id], null, null, null, $this->langId);
                            $html .= '<td><a href="' . $link . '">' . $link . '</a></td>';
                        } elseif ($k === $this->selectedColumns->product->product_image_link) {
                            // Get image data of the given product id
                            $image = Image::getCover($value[$this->selectedColumns->product->product_id]);
                            if ($image) {
                                $img_link = $this->context->link->getImageLink($value[$this->selectedColumns->product->{'order_detail_lang.product_link_rewrite'}], $image['id_image'], $this->imageType);
                                $html .= '<td><a href="' . $img_link . '">' . $img_link . '</a></td>';
                            } else {
                                $html .= '<td><b>'
                                    . $this->module->l('No Image Link', 'ExportSales') . '</b></td>';
                            }
                        } elseif ($k === $this->selectedColumns->product->attribute_image_link) {
                            if (method_exists('Image', 'getBestImageAttribute')) {
                                // Get image data of the given product id
                                $image = Image::getBestImageAttribute(
                                    $value[$this->selectedColumns->shop->id_shop],
                                    $this->langId,
                                    $value[$this->selectedColumns->product->product_id],
                                    $value[$this->selectedColumns->product->product_attribute_id]
                                );
                            } else {
                                $image = Image::getImages(
                                    $this->langId,
                                    $value[$this->selectedColumns->product->product_id],
                                    $value[$this->selectedColumns->product->product_attribute_id]
                                );
                                $image = isset($image[0]) ? $image[0] : null;
                            }
                            if ($image) {
                                $img_link = $this->context->link->getImageLink($value[$this->selectedColumns->product->{'order_detail_lang.product_link_rewrite'}], $image['id_image'], $this->imageType);
                                $html .= '<td><a href="' . $img_link . '">' . $img_link . '</a></td>';
                            } else {
                                $html .= '<td><b>'
                                    . $this->module->l('No Image Link', 'ExportSales') . '</b></td>';
                            }
                        } elseif ($k === $this->selectedColumns->category->category_image) {
                            $cat_img_path = realpath(_PS_CAT_IMG_DIR_ . $value[$this->selectedColumns->category->id_category] . ($this->catImageTypeForFile ? '-' . $this->catImageTypeForFile : '') . '.jpg');
                            if (file_exists($cat_img_path)) {
                                if (method_exists($this->context->link, 'getCatImageLink')) {
                                    // Get image data of the given product id
                                    $cat_img_link = $this->context->link->getCatImageLink(
                                        $value[$this->selectedColumns->category->link_rewrite],
                                        $value[$this->selectedColumns->category->id_category],
                                        $this->imageTypeForFile
                                    );
                                } else {
                                    $cat_img_link = $this->context->link->getBaseLink() . 'c/'
                                        . $value[$this->selectedColumns->category->id_category] . '-'
                                        . $this->imageTypeForFile . '/'
                                        . $value[$this->selectedColumns->category->link_rewrite] . '.jpg';
                                }
                                $html .= '<td><img src="' . $cat_img_link . '" /></td>';
                            } else {
                                $html .= '<td><b>'
                                    . $this->module->l('No Image', 'ExportSales') . '</b></td>';
                            }
                        } elseif ($k === $this->selectedColumns->category->category_link) {
                            if ((int) $value[$this->selectedColumns->category->id_category]) {
                                $link = $this->context->link->getCategoryLink((int) $value[$this->selectedColumns->category->id_category], null, $this->langId);
                                $html .= '<td><a href="' . $link . '">' . $link . '</a></td>';
                            } else {
                                $html .= '<td></td>';
                            }
                        } elseif ($k === $this->selectedColumns->category->category_image_link) {
                            $cat_img_path = realpath(_PS_CAT_IMG_DIR_ . $value[$this->selectedColumns->category->id_category] . ($this->catImageType ? '-' . $this->catImageType : '') . '.jpg');
                            if (file_exists($cat_img_path)) {
                                if (method_exists($this->context->link, 'getCatImageLink')) {
                                    // Get image data of the given product id
                                    $cat_img_link = $this->context->link->getCatImageLink(
                                        $value[$this->selectedColumns->category->link_rewrite],
                                        $value[$this->selectedColumns->category->id_category],
                                        $this->imageType
                                    );
                                } else {
                                    $cat_img_link = $this->context->link->getBaseLink() . 'c/'
                                        . $value[$this->selectedColumns->category->id_category] . ($this->imageType ? '-' . $this->imageType : '') . '/'
                                        . $value[$this->selectedColumns->category->link_rewrite] . '.jpg';
                                }
                                $html .= '<td><a href="' . $cat_img_link . '">' . $cat_img_link . '</a></td>';
                            } else {
                                $html .= '<td><b>'
                                    . $this->module->l('No Image Link', 'ExportSales') . '</b></td>';
                            }
                        } elseif ($k === $this->selectedColumns->manufacturer->manufacturer_link) {
                            if ((int) $value[$this->selectedColumns->manufacturer->id_manufacturer]) {
                                $link = $this->context->link->getManufacturerLink((int) $value[$this->selectedColumns->manufacturer->id_manufacturer], null, $this->langId);
                                $html .= '<td><a href="' . $link . '">' . $link . '</a></td>';
                            } else {
                                $html .= '<td></td>';
                            }
                        } elseif ($k === $this->selectedColumns->manufacturer->manufacturer_image) {
                            $man_img_path = realpath(_PS_MANU_IMG_DIR_ . $value[$this->selectedColumns->manufacturer->id_manufacturer] . ($this->imageTypeForFile ? '-' . $this->imageTypeForFile : '') . '.jpg');
                            if (file_exists($man_img_path)) {
                                $man_img_link = $this->context->link->getBaseLink() . 'img/m/'
                                    . $value[$this->selectedColumns->manufacturer->id_manufacturer] . ($this->imageTypeForFile ? '-' . $this->imageTypeForFile : '') . '.jpg';
                                $html .= '<td><img src="' . $man_img_link . '" /></td>';
                            } else {
                                $html .= '<td><b>'
                                    . $this->module->l('No Image', 'ExportSales') . '</b></td>';
                            }
                        } elseif ($k === $this->selectedColumns->manufacturer->manufacturer_image_link) {
                            $man_img_path = realpath(_PS_MANU_IMG_DIR_ . $value[$this->selectedColumns->manufacturer->id_manufacturer] . ($this->imageType ? '-' . $this->imageType : '') . '.jpg');
                            if (file_exists($man_img_path)) {
                                $man_img_link = $this->context->link->getBaseLink() . 'img/m/'
                                    . $value[$this->selectedColumns->manufacturer->id_manufacturer] . ($this->imageType ? '-' . $this->imageType : '') . '.jpg';
                                $html .= '<td><a href="' . $man_img_link . '">' . $man_img_link . '</a></td>';
                            } else {
                                $html .= '<td><b>'
                                    . $this->module->l('No Image Link', 'ExportSales') . '</b></td>';
                            }
                        } elseif ($k === $this->selectedColumns->supplier->supplier_link) {
                            if ((int) $value[$this->selectedColumns->supplier->id_supplier]) {
                                $link = $this->context->link->getSupplierLink((int) $value[$this->selectedColumns->supplier->id_supplier], null, $this->langId);
                                $html .= '<td><a href="' . $link . '">' . $link . '</a></td>';
                            } else {
                                $html .= '<td></td>';
                            }
                        } elseif ($k === $this->selectedColumns->supplier->supplier_image) {
                            $supp_img_path = realpath(_PS_SUPP_IMG_DIR_ . $value[$this->selectedColumns->supplier->id_supplier] . ($this->imageTypeForFile ? '-' . $this->imageTypeForFile : '') . '.jpg');
                            if (file_exists($supp_img_path)) {
                                $supp_img_link = $this->context->link->getBaseLink() . 'img/su/'
                                    . $value[$this->selectedColumns->supplier->id_supplier] . ($this->imageTypeForFile ? '-' . $this->imageTypeForFile : '') . '.jpg';
                                $html .= '<td><img src="' . $supp_img_link . '" /></td>';
                            } else {
                                $html .= '<td><b>'
                                    . $this->module->l('No Image', 'ExportSales') . '</b></td>';
                            }
                        } elseif ($k === $this->selectedColumns->supplier->supplier_image_link) {
                            $supp_img_path = realpath(_PS_SUPP_IMG_DIR_ . $value[$this->selectedColumns->supplier->id_supplier] . ($this->imageType ? '-' . $this->imageType : '') . '.jpg');
                            if (file_exists($supp_img_path)) {
                                $supp_img_link = $this->context->link->getBaseLink() . 'img/su/'
                                    . $value[$this->selectedColumns->supplier->id_supplier] . ($this->imageType ? '-' . $this->imageType : '') . '.jpg';
                                $html .= '<td><a href="' . $supp_img_link . '">' . $supp_img_link . '</a></td>';
                            } else {
                                $html .= '<td><b>'
                                    . $this->module->l('No Image Link', 'ExportSales') . '</b></td>';
                            }
                        } else {
                            if ($k === $this->selectedColumns->order->{'order_messages.message'}) {
                                $val = html_entity_decode($val);
                            }
                            $html .= '<td>' . $val . '</td>';
                        }
                        $i++;
                    }
                    $html .= '</tr>';
                }
            }

            $totalProducts = $orders[$key][$this->selectedColumns->order->total_products];
            $totalDiscountsTaxExcl = $orders[$key][$this->selectedColumns->order->total_discounts_tax_excl];
            $curr = $this->displayCurrSymbol ? $this->curs->{$orders[$key][$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';
            if (isset($this->selectedColumns->order->profit_amount)) {
                $profit_amount = $totalProducts - $groupTotal;
                $html = str_replace('profit_amount', $curr . str_replace('.', $this->decimalSeparator, (string) round($profit_amount, $this->fracPart)), $html);
            }
            if (isset($this->selectedColumns->order->profit_margin)) {
                $profit_margin = 100 * ($totalProducts - $groupTotal) / $totalProducts;
                $html = str_replace('profit_margin', str_replace('.', $this->decimalSeparator, (string) round($profit_margin, $this->fracPart)) . '%', $html);
            }
            if (isset($this->selectedColumns->order->profit_percentage)) {
                $profit_percentage = 100 * ($totalProducts - $groupTotal) / $groupTotal;
                $html = str_replace('profit_percentage', str_replace('.', $this->decimalSeparator, (string) round($profit_percentage, $this->fracPart)) . '%', $html);
            }

            if (isset($this->selectedColumns->order->net_profit_amount)) {
                $net_profit_amount = $totalProducts - $totalDiscountsTaxExcl - $groupTotal;
                $html = str_replace('net_profitt_amount', $curr . str_replace('.', $this->decimalSeparator, (string) round($net_profit_amount, $this->fracPart)), $html);
            }
            if (isset($this->selectedColumns->order->net_profit_margin)) {
                $net_profit_margin = 100 * ($totalProducts - $totalDiscountsTaxExcl - $groupTotal) / ($totalProducts - $totalDiscountsTaxExcl);
                $html = str_replace('net_profitt_margin', str_replace('.', $this->decimalSeparator, (string) round($net_profit_margin, $this->fracPart)) . '%', $html);
            }
            if (isset($this->selectedColumns->order->net_profit_percentage)) {
                $net_profit_percentage = 100 * ($totalProducts - $totalDiscountsTaxExcl - $groupTotal) / $groupTotal;
                $html = str_replace('net_profitt_percentage', str_replace('.', $this->decimalSeparator, (string) round($net_profit_percentage, $this->fracPart)) . '%', $html);
            }

            if ($this->displayTotals === '1') {
                if ($profits) {
                    $sale += $totalProducts / $orders[$key][$this->selectedColumns->order->{'currency.conversion_rate'}];
                }
                if ($netProfits) {
                    $netSale += ($totalProducts - $totalDiscountsTaxExcl) / $orders[$key][$this->selectedColumns->order->{'currency.conversion_rate'}];
                }

                if (isset($profitAmountNumber)) {
                    $val = $sale - $purchase;
                    $totals[$profitAmountNumber]['val'] = $val;
                    $totals[$profitAmountNumber]['curr'] = $this->displayCurrSymbol;
                }
                if (isset($profitMarginNumber)) {
                    $val = 100 * ($sale - $purchase) / $sale;
                    $totals[$profitMarginNumber]['val'] = $val . '%';
                }
                if (isset($profitPercentageNumber)) {
                    $val = 100 * ($sale - $purchase) / $purchase;
                    $totals[$profitPercentageNumber]['val'] = $val . '%';
                }
                if (isset($netProfitAmountNumber)) {
                    $val = $netSale - $purchase;
                    $totals[$netProfitAmountNumber]['val'] = $val;
                    $totals[$netProfitAmountNumber]['curr'] = $this->displayCurrSymbol;
                }
                if (isset($netProfitMarginNumber)) {
                    $val = 100 * ($netSale - $purchase) / $netSale;
                    $totals[$netProfitMarginNumber]['val'] = $val . '%';
                }
                if (isset($netProfitPercentageNumber)) {
                    $val = 100 * ($netSale - $purchase) / $purchase;
                    $totals[$netProfitPercentageNumber]['val'] = $val . '%';
                }
                if (isset($rPNumber)) {
                    $val = 100 * ($reductionTotals['full'] - $reductionTotals['reduced']) / $reductionTotals['full'];
                    $totals[$rPNumber]['val'] = $val . '%';
                }

                $def_curr = Configuration::get('OXSRP_DEF_CURR_SMBL') . ' ';

                $html .= '<tr class="totals">';
                for ($i = 0; $i < $counter - $psp; $i++) {
                    if (isset($totals[$i])) {
                        $html .= '<td>';
                        if (isset($totals[$i]['curr']) && $totals[$i]['curr']) {
                            $html .= $def_curr;
                        }
                        if (Tools::substr($totals[$i]['val'], -1) === '%') {
                            $val = round(rtrim($totals[$i]['val'], '%'), $this->fracPart) . '%';
                        } else {
                            $val = round($totals[$i]['val'], $this->fracPart);
                        }
                        $html .= str_replace('.', $this->decimalSeparator, $val) . '</td>';
                    } else {
                        $html .= '<td></td>';
                    }
                }
                $html .= '</tr>';
            }

            if ($this->displayFooter === '1') {
                $html .= '<tr>';
//                $headers = array_keys($orders[0]);
                for ($i = 0; $i < $counter - $psp; $i++) {
                    $html .= '<th><b>' . $headers[$i] . '</b></th>';
                }
                $html .= '</tr>';
            }

            $html .= '</table>';


            if ($this->displayExplanations === '1') {
                $html .= '<br /><br /><span>';
                if (isset($profitAmountNumber)) {
                    $html .= '* <b>' . $this->module->l('Gross Profit Amount', 'ExportSales') . '</b>' . $this->module->l(' = S - P. Sale price of an order minus total purchase price of products in that order (taxes excluded).', 'ExportSales') . '<br />';
                }
                if (isset($profitMarginNumber)) {
                    $html .= '* <b>' . $this->module->l('Gross Profit Margin', 'ExportSales') . '</b>' . $this->module->l(' = 100 * (S - P) / S. Sale price of an order minus total purchase price of products in that order divided by the sale price multiplied by 100 (taxes excluded).', 'ExportSales') . '<br />';
                }
                if (isset($profitPercentageNumber)) {
                    $html .= '* <b>' . $this->module->l('Gross Profit Percentage', 'ExportSales') . '</b>' . $this->module->l(' = 100 * (S - P) / P. Sale price of an order minus total purchase price of products in that order divided by the purchase price multiplied by 100 (taxes excluded).', 'ExportSales') . '<br />';
                }
                if (isset($netProfitAmountNumber)) {
                    $html .= '* <b>' . $this->module->l('Net Profit Amount', 'ExportSales') . '</b>' . $this->module->l(' = S - D - P. Sale price of an order minus the discount of that order minus total purchase price of products in that order (taxes excluded).', 'ExportSales') . '<br />';
                }
                if (isset($netProfitMarginNumber)) {
                    $html .= '* <b>' . $this->module->l('Net Profit Margin', 'ExportSales') . '</b>' . $this->module->l(' = 100 * (S - D - P) / (S - D). Sale price of an order minus the discount of that order minus total purchase price of products in that order divided by the sale price minus the discount of that order multiplied by 100 (taxes excluded).', 'ExportSales') . '<br />';
                }
                if (isset($netProfitPercentageNumber)) {
                    $html .= '* <b>' . $this->module->l('Net Profit Percentage', 'ExportSales') . '</b>' . $this->module->l(' = 100 * (S - D - P) / P. Sale price of an order minus the discount of that order minus total purchase price of products in that order divided by the purchase price multiplied by 100 (taxes excluded).', 'ExportSales') . '<br />';
                }
                if (!$this->purchaseSupplierPrice && $this->selectedColumns->product->purchase_supplier_price) {
                    $html .= '* <b>' . $this->module->l('Product Quantity', 'ExportSales') . '</b>' . $this->module->l(' = Product purchase price multiplied by product purchased quantity, then summed.', 'ExportSales');
                }

                $html .= '</span>';
            }

            if ($this->displayBestSellers === '1' && !is_numeric($this->auto)) {
                $html .= '<br /><br /><br />';
                if ($this->displayHeader === '1') {
                    $html .= '<h2>' . $this->module->l('Sales by Products', 'ExportSales') . '</h2>';
                }
                $sales = $this->getBestSellers();
                $html .= '<table>';
                $html .= '<tr>';
                $html .= '<th><div style="width: 100px;"><b>' . $this->module->l('Product ID', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 150px;"><b>' . $this->module->l('Product Reference', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 300px;"><b>' . $this->module->l('Product Name', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 200px;"><b>' . $this->module->l('Sold Quantity', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 200px;"><b>' . $this->module->l('Total Profit (Tax Excl.)', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 200px;"><b>' . $this->module->l('Total Price (Tax Excl.)', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 200px;"><b>' . $this->module->l('Total Price (Tax Incl.)', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 200px;"><b>' . $this->module->l('Total Paid (Tax Excl.)', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 200px;"><b>' . $this->module->l('Total Paid (Tax Incl.)', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 200px;"><b>' . $this->module->l('Total Really Paid', 'ExportSales') . '</b></div></th>';
                $html .= '</tr>';
                foreach ($sales as $val) {
                    $html .= '<tr>';
                    foreach ($val as $v) {
                        $html .= '<td>' . $v . '</td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '</table>';
            }

            if ($this->displayProductCombs === '1' && !is_numeric($this->auto)) {
                $html .= '<br /><br /><br />';
                if ($this->displayHeader === '1') {
                    $html .= '<h2>' . $this->module->l('Sales by Combinations', 'ExportSales') . '</h2>';
                }
                $sales = $this->getProductCombs();
                $html .= '<table>';
                $html .= '<tr>';
                $html .= '<th><div style="width: 100px;"><b>' . $this->module->l('Product ID', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 300px;"><b>' . $this->module->l('Product Name', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 120px;"><b>' . $this->module->l('Combination ID', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 350px;"><b>' . $this->module->l('Combination', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 120px;"><b>' . $this->module->l('Sold Quantity', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 150px;"><b>' . $this->module->l('Total Profit (Tax Excl.)', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 150px;"><b>' . $this->module->l('Total Price (Tax Excl.)', 'ExportSales') . '</b></div></th>';
                $html .= '</tr>';
                foreach ($sales as $val) {
                    $html .= '<tr>';
                    foreach ($val as $v) {
                        $html .= '<td>' . $v . '</td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '</table>';
            }

            if ($this->displayDailySales === '1' && !is_numeric($this->auto)) {
                $html .= '<br /><br /><br />';
                if ($this->displayHeader === '1') {
                    $html .= '<h2>' . $this->module->l('Daily Sales', 'ExportSales') . '</h2>';
                }
                $sales = $this->getDailySales();
                $html .= '<table>';
                $html .= '<tr>';
                $html .= '<th><div style="width: 100px;"><b>' . $this->module->l('Date', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 120px;"><b>' . $this->module->l('Sold Quantity', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 150px;"><b>' . $this->module->l('Total Profit (Tax Excl.)', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 150px;"><b>' . $this->module->l('Total Price (Tax Excl.)', 'ExportSales') . '</b></div></th>';
                $html .= '</tr>';
                foreach ($sales as $val) {
                    $html .= '<tr>';
                    foreach ($val as $v) {
                        $html .= '<td>' . $v . '</td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '</table>';
            }

            if ($this->displayMonthlySales === '1' && !is_numeric($this->auto)) {
                $html .= '<br /><br /><br />';
                if ($this->displayHeader === '1') {
                    $html .= '<h2>' . $this->module->l('Monthly Sales', 'ExportSales') . '</h2>';
                }
                $sales = $this->getMonthlySales();
                $html .= '<table>';
                $html .= '<tr>';
                $html .= '<th><div style="width: 100px;"><b>' . $this->module->l('Date', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 120px;"><b>' . $this->module->l('Sold Quantity', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 150px;"><b>' . $this->module->l('Total Profit (Tax Excl.)', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 150px;"><b>' . $this->module->l('Total Price (Tax Excl.)', 'ExportSales') . '</b></div></th>';
                $html .= '</tr>';
                foreach ($sales as $val) {
                    $html .= '<tr>';
                    foreach ($val as $v) {
                        $html .= '<td>' . $v . '</td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '</table>';
            }

            if ($this->displayTopCustomers === '1' && !is_numeric($this->auto)) {
                $html .= '<br /><br /><br />';
                if ($this->displayHeader === '1') {
                    $html .= '<h2>' . $this->module->l('Sales by Customers', 'ExportSales') . '</h2>';
                }
                $sales = $this->getTopCustomers();
                $html .= '<table>';
                $html .= '<tr>';
                $html .= '<th><div style="width: 100px;"><b>' . $this->module->l('Customer ID', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 200px;"><b>' . $this->module->l('Customer Email', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 130px;"><b>' . $this->module->l('Customer Firstname', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 130px;"><b>' . $this->module->l('Customer Lastname', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 100px;"><b>' . $this->module->l('Number of Orders', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 200px;"><b>' . $this->module->l('Total Orders (Tax Excl.)', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 200px;"><b>' . $this->module->l('Total Orders with Discount (Tax Excl.)', 'ExportSales') . '</b></div></th>';
                $html .= '</tr>';
                foreach ($sales as $val) {
                    $html .= '<tr>';
                    foreach ($val as $v) {
                        $html .= '<td>' . $v . '</td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '</table>';
            }

            if ($this->displayPaymentMethods === '1' && !is_numeric($this->auto)) {
                $html .= '<br /><br /><br />';
                if ($this->displayHeader === '1') {
                    $html .= '<h2>' . $this->module->l('Sales by Payment Methods', 'ExportSales') . '</h2>';
                }
                $sales = $this->getPaymentSales();
                $html .= '<table>';
                $html .= '<tr>';
                $html .= '<th><div style="width: 100px;"><b>' . $this->module->l('Payment', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 200px;"><b>' . $this->module->l('Module', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 100px;"><b>' . $this->module->l('Number of Orders', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 200px;"><b>' . $this->module->l('Total Orders (Tax Excl.)', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 200px;"><b>' . $this->module->l('Total Orders with Discount (Tax Excl.)', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 200px;"><b>' . $this->module->l('Refunded Amount', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 200px;"><b>' . $this->module->l('Refunded Amount ROCK (Tax Excl.)', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 200px;"><b>' . $this->module->l('Refunded Amount ROCK (Tax Incl.)', 'ExportSales') . '</b></div></th>';
                $html .= '</tr>';
                foreach ($sales as $val) {
                    $html .= '<tr>';
                    foreach ($val as $v) {
                        $html .= '<td>' . $v . '</td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '</table>';
            }

            if ($this->displayPaymentMethods2 === '1' && !is_numeric($this->auto)) {
                $html .= '<br /><br /><br />';
                if ($this->displayHeader === '1') {
                    $html .= '<h2>' . $this->module->l('Sales by Payment Options', 'ExportSales') . '</h2>';
                }
                $sales = $this->getPaymentSales2();
                $html .= '<table>';
                $html .= '<tr>';
                $html .= '<th><div style="width: 100px;"><b>' . $this->module->l('Payment', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 200px;"><b>' . $this->module->l('Module', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 100px;"><b>' . $this->module->l('Number of Orders', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 200px;"><b>' . $this->module->l('Total Orders (Tax Excl.)', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 200px;"><b>' . $this->module->l('Total Orders with Discount (Tax Excl.)', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 200px;"><b>' . $this->module->l('Refunded Amount', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 200px;"><b>' . $this->module->l('Refunded Amount ROCK (Tax Excl.)', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 200px;"><b>' . $this->module->l('Refunded Amount ROCK (Tax Incl.)', 'ExportSales') . '</b></div></th>';
                $html .= '</tr>';
                foreach ($sales as $val) {
                    $html .= '<tr>';
                    foreach ($val as $v) {
                        $html .= '<td>' . $v . '</td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '</table>';
            }

            if ($this->displaySalesByCategories === '1' && !is_numeric($this->auto)) {
                $html .= '<br /><br /><br />';
                if ($this->displayHeader === '1') {
                    $html .= '<h2>' . $this->module->l('Sales by Categories', 'ExportSales') . '</h2>';
                }
                $sales = $this->getSalesByCategories();
                $html .= '<table>';
                $html .= '<tr>';
                $html .= '<th><div style="width: 100px;"><b>' . $this->module->l('Category ID', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 300px;"><b>' . $this->module->l('Category Name', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 200px;"><b>' . $this->module->l('Total Price (Tax Excl.)', 'ExportSales') . '</b></div></th>';
                $html .= '</tr>';
                foreach ($sales as $val) {
                    $html .= '<tr>';
                    foreach ($val as $v) {
                        $html .= '<td>' . $v . '</td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '</table>';
            }

            if ($this->displaySalesByBrands === '1' && !is_numeric($this->auto)) {
                $html .= '<br /><br /><br />';
                if ($this->displayHeader === '1') {
                    $html .= '<h2>' . $this->module->l('Sales by Brands', 'ExportSales') . '</h2>';
                }
                $sales = $this->getSalesByBrands();
                $html .= '<table>';
                $html .= '<tr>';
                $html .= '<th><div style="width: 100px;"><b>' . $this->module->l('Brand ID', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 300px;"><b>' . $this->module->l('Brand Name', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 200px;"><b>' . $this->module->l('Total Price (Tax Excl.)', 'ExportSales') . '</b></div></th>';
                $html .= '</tr>';
                foreach ($sales as $val) {
                    $html .= '<tr>';
                    foreach ($val as $v) {
                        $html .= '<td>' . $v . '</td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '</table>';
            }

            if ($this->displaySalesBySuppliers === '1' && !is_numeric($this->auto)) {
                $html .= '<br /><br /><br />';
                if ($this->displayHeader === '1') {
                    $html .= '<h2>' . $this->module->l('Sales by Suppliers', 'ExportSales') . '</h2>';
                }
                $sales = $this->getSalesBySuppliers();
                $html .= '<table>';
                $html .= '<tr>';
                $html .= '<th><div style="width: 100px;"><b>' . $this->module->l('Supplier ID', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 300px;"><b>' . $this->module->l('Supplier Name', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 200px;"><b>' . $this->module->l('Total Price (Tax Excl.)', 'ExportSales') . '</b></div></th>';
                $html .= '</tr>';
                foreach ($sales as $val) {
                    $html .= '<tr>';
                    foreach ($val as $v) {
                        $html .= '<td>' . $v . '</td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '</table>';
            }

            if ($this->displaySalesByAttributes === '1' && !is_numeric($this->auto)) {
                $html .= '<br /><br /><br />';
                if ($this->displayHeader === '1') {
                    $html .= '<h2>' . $this->module->l('Sales by Attributes', 'ExportSales') . '</h2>';
                }
                $sales = $this->getSalesByAttributes();
                $html .= '<table>';
                $html .= '<tr>';
                $html .= '<th><div style="width: 300px;"><b>' . $this->module->l('Attribute Group Name', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 300px;"><b>' . $this->module->l('Attribute Name', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 200px;"><b>' . $this->module->l('Total Price (Tax Excl.)', 'ExportSales') . '</b></div></th>';
                $html .= '</tr>';
                foreach ($sales as $val) {
                    $html .= '<tr>';
                    foreach ($val as $v) {
                        $html .= '<td>' . $v . '</td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '</table>';
            }

            if ($this->displaySalesByFeatures === '1' && !is_numeric($this->auto)) {
                $html .= '<br /><br /><br />';
                if ($this->displayHeader === '1') {
                    $html .= '<h2>' . $this->module->l('Sales by Features', 'ExportSales') . '</h2>';
                }
                $sales = $this->getSalesByFeatures();
                $html .= '<table>';
                $html .= '<tr>';
                $html .= '<th><div style="width: 300px;"><b>' . $this->module->l('Feature Name', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 300px;"><b>' . $this->module->l('Feature Value', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 100px;"><b>' . $this->module->l('Is Custom', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 200px;"><b>' . $this->module->l('Total Price (Tax Excl.)', 'ExportSales') . '</b></div></th>';
                $html .= '</tr>';
                foreach ($sales as $val) {
                    $html .= '<tr>';
                    foreach ($val as $v) {
                        $html .= '<td>' . $v . '</td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '</table>';
            }

            if ($this->displaySalesByShops === '1' && !is_numeric($this->auto)) {
                $html .= '<br /><br /><br />';
                if ($this->displayHeader === '1') {
                    $html .= '<h2>' . $this->module->l('Sales by Shops', 'ExportSales') . '</h2>';
                }
                $sales = $this->getSalesByShops();
                $html .= '<table>';
                $html .= '<tr>';
                $html .= '<th><div style="width: 100px;"><b>' . $this->module->l('Shop ID', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 250px;"><b>' . $this->module->l('Shop Name', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 250px;"><b>' . $this->module->l('Shop Group Name', 'ExportSales') . '</b></div></th>';
                $html .= '<th><div style="width: 200px;"><b>' . $this->module->l('Total Price (Tax Excl.)', 'ExportSales') . '</b></div></th>';
                $html .= '</tr>';
                foreach ($sales as $val) {
                    $html .= '<tr>';
                    foreach ($val as $v) {
                        $html .= '<td>' . $v . '</td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '</table>';
            }
            $html .= '<br><br><br>';
        } else {
            $html .= '<h2>' . $this->module->l('No Data', 'ExportSales') . '</h2>';
        }

        $html .= '
                    </body>
                    </html>
                ';

        $id = Tools::getAdminToken($this->context->employee->id);
        $target_action = Tools::getValue('orders_target_action');

        if ($this->auto) {
            $fileName = $this->docName . '.html';
            file_put_contents($this->tempDir . $fileName, $html);
            return $fileName;
        } elseif ($target_action !== 'download') {
            mkdir($this->outputDir . '/' . $id);
            $file = $this->outputDir . '/' . $id . '/' . $this->docName . '.html';
            file_put_contents($file, $html);

            // If target action is either email or ftp
            if ($target_action === 'email') {
                $subject = $this->module->l('Your Sales (by Advanced Sales Reports module)', 'ExportSales');
//                $content = $this->module->l('The details are in the attachment.', 'ExportSales');
//                $this->sendEmail(explode(';', Tools::getValue('target_action_to_emails')), $subject, $content, $file);
                $this->sendPSEmail(explode(';', Tools::getValue('target_action_to_emails')), $subject, $file);
            } elseif ($target_action === 'ftp') {
                $ftp_type = Tools::getValue('orders_target_action_ftp_type');
                $ftp_mode = Tools::getValue('orders_target_action_ftp_mode');
                $ftp_url = Tools::getValue('orders_target_action_ftp_url');
                $ftp_port = Tools::getValue('orders_target_action_ftp_port');
                $ftp_username = Tools::getValue('orders_target_action_ftp_username');
                $ftp_password = Tools::getValue('orders_target_action_ftp_password');
                $ftp_folder = Tools::getValue('orders_target_action_ftp_folder');
                if ($ftp_folder) {
                    $ftp_folder .= '/' . $this->docName;
                }
//                $ftp_file_ext = Tools::getValue('orders_target_action_ftp_file_ext');

                $this->uploadToFTP($file, $ftp_type, $ftp_mode, $ftp_url, $ftp_port, $ftp_username, $ftp_password, $ftp_folder, 0);
            }

            unlink($file);
            rmdir($this->outputDir . '/' . $id);
        } else {
            $id = mt_rand() . uniqid();
            $filePath = $this->outputDir . '/' . $id . '.html';
            file_put_contents($filePath, $html);
            header('Content-type: application/json');
            echo json_encode(array(
                'status' => 'ok',
                'id' => $id,
                'type' => 'html',
                'name' => $this->docName . (Tools::getValue('orders_general_add_ts') && $this->filteredDate ? '_' . $this->filteredDate : '')
            ));
        }
    }

    public function generatePDF()
    {
        $orders = $this->getOrders();

        if (is_numeric($this->auto) && !$orders && Configuration::get('OXSRP_AUTOEXP_DNSEM')) {
            return 0;
        }

        if ($this->auto === 'schedule' && !$orders && Configuration::get('OXSRP_SCHDL_DNSEM')) {
            return 0;
        }

        $this->setHelperSql();

        $html = '<!DOCTYPE html>
                <html>
                    <head>
                        <meta charset="UTF-8">
                        <style>
                            table {
                                border: 1px solid #999;
                            }
                            table th {
                                text-align: center;
                            }
                            table th, table td {
                                border: 1px solid #aaa;
                                box-sizing: border-box;
                                width: 32mm;
                                text-align: center;
                            }
                            .totals {
                                /*height: 10mm;*/
                                background: #E7FFD9;
                                color: #3C763D;
                            }
                            .totals > td{
                                border: 2px solid #7CC67C;
                                font-size: 4mm;
                            }
                        </style>
                    </head>
                    <body>
                ';
        if (!empty($orders)) {
            $counter = count($orders[0]);
            $width = $counter * 32 + 35;
            if ($this->orderId) {
                $width -= 32;
                $counter--;
            }
            if ($this->productId) {
                $width -= 32;
                $counter--;
            }
            if ($this->attributeId) {
                $width -= 32;
                $counter--;
            }
            if ($this->shopId) {
                $width -= 32;
                $counter--;
            }
            if ($this->categoryId) {
                $width -= 32;
                $counter--;
            }
            if ($this->manufacturerId) {
                $width -= 32;
                $counter--;
            }
            if ($this->supplierId) {
                $width -= 32;
                $counter--;
            }
            if ($this->productRewriteLink) {
                $width -= 32;
                $counter--;
            }
            if ($this->categoryRewriteLink) {
                $width -= 32;
                $counter--;
            }
            if ($this->currencyIsoCode) {
                $width -= 32;
                $counter--;
            }
            if ($this->currencyConversionRate) {
                $width -= 32;
                $counter--;
            }
            if ($this->totalProducts) {
                $width -= 32;
                $counter--;
            }
            if ($this->productQuantity) {
                $width -= 32;
                $counter--;
            }
            if ($this->totalPriceTaxExcl) {
                $width -= 32;
                $counter--;
            }
            if ($this->totalDiscountsTaxExcl) {
                $width -= 32;
                $counter--;
            }
            if ($this->purchaseSupplierPrice) {
                $width -= 32;
            }

            if (isset($this->selectedColumns->order->id_order)) {
                $groupTotal = 0;
                $groupOrder = $orders[0][$this->selectedColumns->order->id_order];
            }

            if ($this->selectedColumns->order->profit_amount ||
                $this->selectedColumns->order->profit_margin ||
                $this->selectedColumns->order->profit_percentage) {
                $profits = true;
            } else {
                $profits = false;
            }

            if ($this->selectedColumns->order->net_profit_amount ||
                $this->selectedColumns->order->net_profit_margin ||
                $this->selectedColumns->order->net_profit_percentage) {
                $netProfits = true;
            } else {
                $netProfits = false;
            }

            $headers = array_keys($orders[0]);
            $psp = 0;
            if ($this->purchaseSupplierPrice) {
                $psp++;
            }

            $wideColumns = array(
                $this->selectedColumns->customer->email => 50,
                $this->selectedColumns->product->{'order_detail_lang.attributes'} => 60,
                $this->selectedColumns->product->{'product_features.features'} => 70,
                $this->selectedColumns->product->product_link => 80,
                $this->selectedColumns->product->product_image_link => 80,
                $this->selectedColumns->product->attribute_image_link => 80,
                $this->selectedColumns->payment->payment_details => 50,
                $this->selectedColumns->order->{'order_state_history.order_history'} => 70,
                $this->selectedColumns->order->{'order_messages.message'} => 70,
                $this->selectedColumns->category->category_link => 80,
                $this->selectedColumns->category->category_image_link => 80,
                $this->selectedColumns->category->description => 90,
                $this->selectedColumns->manufacturer->manufacturer_link => 80,
                $this->selectedColumns->manufacturer->manufacturer_image_link => 80,
                $this->selectedColumns->supplier->supplier_link => 80,
                $this->selectedColumns->supplier->supplier_image_link => 80,
            );

//            d($wideColumns);
            if ($this->displayHeader === '1') {
                $html .= '<h2>' . $this->module->l('Sales', 'ExportSales') . '</h2><table cellpadding="10" cellspacing="0">';
                $html .= '<tr>';
                for ($i = 0; $i < $counter - $psp; $i++) {
                    if (isset($wideColumns[$headers[$i]])) {
                        $html .= '<th valign="middle" style="background-color: #DCF0FF; width: '
                            . $wideColumns[$headers[$i]] . 'mm;"><b>'
                            . $headers[$i] . '</b></th>';
                        $width += $wideColumns[$headers[$i]] - 32;
                    } else {
                        $html .= '<th valign="middle" style="background-color: #DCF0FF;"><b>' . $headers[$i] . '</b></th>';
                    }
                }
                $html .= '</tr>';
            }

            if ($width > 320) {
                $height = $width / sqrt(2);
                $orientation = 'L';
            } else {
                $width = 320;
                $height = 320 * sqrt(2);
                $orientation = 'P';
            }

            $totals = array();
            $purchase = $sale = $netSale = 0;
            $reductionTotals = array(
                'full' => 0,
                'reduced' => 0
            );

            $profitAmountNumber = $profitMarginNumber = $profitPercentageNumber = $netProfitAmountNumber = $netProfitMarginNumber = $netProfitPercentageNumber = $rPNumber = null;

            $groups = $this->getGroups();
            if ($this->ordersMerge === '1') {
                $j = 0;
                foreach ($groups as $group) {
                    for ($i = 0; $i < (int) $group['products']; ++$i) {
                        if ($this->noProduct && $i !== 0) {
                            if ($profits || $netProfits) {
                                $groupTotal += $orders[$j][$this->selectedColumns->product->purchase_supplier_price] * $orders[$j][$this->selectedColumns->product->product_quantity];
                                if ($this->displayTotals === '1') {
                                    $purchase += (float) $orders[$j][$this->selectedColumns->product->purchase_supplier_price] * $orders[$j][$this->selectedColumns->product->product_quantity] / $orders[$j][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                }
                            }
                            $j++;
                            continue;
                        }
                        $count = 0;
                        $html .= '<tr>';
                        foreach ($orders[$j] as $k => $val) {
                            if ($count >= $counter) {
                                break;
                            }
                            if (in_array($k, (array) $this->selectedColumns->product) ||
                                in_array($k, (array) $this->selectedColumns->category) ||
                                in_array($k, (array) $this->selectedColumns->manufacturer) ||
                                in_array($k, (array) $this->selectedColumns->supplier)) {
                                if (($val || $val === '0') && in_array($k, $this->moneyColumns)) {
                                    $curr = $this->displayCurrSymbol ? $this->curs->{$orders[$j][$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';
                                    $html .= '<td valign="middle">' . $curr . str_replace('.', $this->decimalSeparator, $val) . '</td>';
                                    if ($this->displayTotals === '1') {
                                        if (isset($totals[$count])) {
                                            $totals[$count]['val'] += (float) $val / $orders[$j][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                        } else {
                                            $totals[$count]['val'] = (float) $val / $orders[$j][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                            $totals[$count]['curr'] = (bool) $curr;
                                        }
                                    }
                                } elseif ($k === $this->selectedColumns->product->purchase_supplier_price) {
                                    if (!$this->purchaseSupplierPrice) {
                                        $curr = $this->displayCurrSymbol ? $this->curs->{$orders[$j][$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';
                                        $html .= '<td>' . $curr . str_replace('.', $this->decimalSeparator, $val) . '</td>';
                                        if ($this->displayTotals === '1') {
                                            if (isset($totals[$count])) {
                                                $totals[$count]['val'] += (float) $val * $orders[$j][$this->selectedColumns->product->product_quantity] / $orders[$j][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                            } else {
                                                $totals[$count]['val'] = (float) $val * $orders[$j][$this->selectedColumns->product->product_quantity] / $orders[$j][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                                $totals[$count]['curr'] = (bool) $curr;
                                            }
                                        }
                                    }
                                    if ($profits || $netProfits) {
                                        if ($groupOrder !== $orders[$j][$this->selectedColumns->order->id_order]) {
                                            $totalProducts = $orders[$j - 1][$this->selectedColumns->order->total_products];
                                            $totalDiscountsTaxExcl = $orders[$j - 1][$this->selectedColumns->order->total_discounts_tax_excl];
                                            $curr = $this->displayCurrSymbol ? $this->curs->{$orders[$j - 1][$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';
                                            if ($this->selectedColumns->order->profit_amount) {
                                                $profit_amount = $totalProducts - $groupTotal;
                                                $html = str_replace('profit_amount', $curr . str_replace('.', $this->decimalSeparator, (string) round($profit_amount, $this->fracPart)), $html);
                                                if (!isset($profitAmountNumber)) {
                                                    $profitAmountNumber = array_search($this->selectedColumns->order->profit_amount, $headers);
                                                }
                                            }
                                            if ($this->selectedColumns->order->profit_margin) {
                                                $profit_margin = 100 * ($totalProducts - $groupTotal) / $totalProducts;
                                                $html = str_replace('profit_margin', str_replace('.', $this->decimalSeparator, (string) round($profit_margin, $this->fracPart)) . '%', $html);
                                                if (!isset($profitMarginNumber)) {
                                                    $profitMarginNumber = array_search($this->selectedColumns->order->profit_margin, $headers);
                                                }
                                            }
                                            if ($this->selectedColumns->order->profit_percentage) {
                                                $profit_percentage = 100 * ($totalProducts - $groupTotal) / $groupTotal;
                                                $html = str_replace('profit_percentage', str_replace('.', $this->decimalSeparator, (string) round($profit_percentage, $this->fracPart)) . '%', $html);
                                                if (!isset($profitPercentageNumber)) {
                                                    $profitPercentageNumber = array_search($this->selectedColumns->order->profit_percentage, $headers);
                                                }
                                            }
                                            if ($this->selectedColumns->order->net_profit_amount) {
                                                $net_profit_amount = $totalProducts - $totalDiscountsTaxExcl - $groupTotal;
                                                $html = str_replace('net_profitt_amount', $curr . str_replace('.', $this->decimalSeparator, (string) round($net_profit_amount, $this->fracPart)), $html);
                                                if (!isset($netProfitAmountNumber)) {
                                                    $netProfitAmountNumber = array_search($this->selectedColumns->order->net_profit_amount, $headers);
                                                }
                                            }
                                            if ($this->selectedColumns->order->net_profit_margin) {
                                                $net_profit_margin = 100 * ($totalProducts - $totalDiscountsTaxExcl - $groupTotal) / ($totalProducts - $totalDiscountsTaxExcl);
                                                $html = str_replace('net_profitt_margin', str_replace('.', $this->decimalSeparator, (string) round($net_profit_margin, $this->fracPart)) . '%', $html);
                                                if (!isset($netProfitMarginNumber)) {
                                                    $netProfitMarginNumber = array_search($this->selectedColumns->order->net_profit_margin, $headers);
                                                }
                                            }
                                            if ($this->selectedColumns->order->net_profit_percentage) {
                                                $net_profit_percentage = 100 * ($totalProducts - $totalDiscountsTaxExcl - $groupTotal) / $groupTotal;
                                                $html = str_replace('net_profitt_percentage', str_replace('.', $this->decimalSeparator, (string) round($net_profit_percentage, $this->fracPart)) . '%', $html);
                                                if (!isset($netProfitPercentageNumber)) {
                                                    $netProfitPercentageNumber = array_search($this->selectedColumns->order->net_profit_percentage, $headers);
                                                }
                                            }
                                            $groupTotal = 0;
                                            $groupOrder = $orders[$j][$this->selectedColumns->order->id_order];
                                            if ($this->displayTotals === '1') {
                                                if ($profits) {
                                                    $sale += $totalProducts / $orders[$j - 1][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                                }
                                                if ($netProfits) {
                                                    $netSale += ($totalProducts - $totalDiscountsTaxExcl) / $orders[$j - 1][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                                }
                                            }
                                        }
                                        $groupTotal += $val * $orders[$j][$this->selectedColumns->product->product_quantity];
                                        if ($this->displayTotals === '1') {
                                            $purchase += (float) $val * $orders[$j][$this->selectedColumns->product->product_quantity] / $orders[$j][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                        }
                                    }
                                } elseif ($k === $this->selectedColumns->product->product_quantity) {
                                    $html .= '<td>' . $val . '</td>';
                                    if ($this->displayTotals === '1') {
                                        if (isset($totals[$count])) {
                                            $totals[$count]['val'] += $val;
                                        } else {
                                            $totals[$count]['val'] = $val;
                                            $totals[$count]['curr'] = 0;
                                        }
                                    }
                                } elseif ($k === $this->selectedColumns->product->reduction_percent) {
                                    $html .= '<td>' . str_replace('.', $this->decimalSeparator, $val) . '</td>';
                                    if ($this->displayTotals === '1') {
                                        $reductionTotals['reduced'] += $orders[$j][$this->selectedColumns->product->total_price_tax_excl];
                                        $reductionTotals['full'] += 100 * $orders[$j][$this->selectedColumns->product->total_price_tax_excl] / (100 - $val);
                                        if (!isset($rPNumber)) {
                                            $rPNumber = $count;
                                        }
                                    }
                                } elseif ($k === $this->selectedColumns->product->product_image) {
                                    // Get image data of the given product id
                                    $image = Image::getCover($orders[$j][$this->selectedColumns->product->product_id]);
                                    if ($image) {
                                        $img = new Image($image['id_image']);
                                        $image_path = realpath(_PS_PROD_IMG_DIR_ . $img->getImgPath() . ($this->imageTypeForFile ? '-' . $this->imageTypeForFile : '') . '.jpg');
                                        $html .= '<td valign="middle"><img src="' . $image_path
                                            . '" /></td>';
                                    } else {
                                        $html .= '<td valign="middle"><b>'
                                            . $this->module->l('No Image', 'ExportSales') . '</b></td>';
                                    }
                                } elseif ($k === $this->selectedColumns->product->attribute_image) {
                                    if (method_exists('Image', 'getBestImageAttribute')) {
                                        // Get image data of the given product id
                                        $image = Image::getBestImageAttribute(
                                            $orders[$j][$this->selectedColumns->shop->id_shop],
                                            $this->langId,
                                            $orders[$j][$this->selectedColumns->product->product_id],
                                            $orders[$j][$this->selectedColumns->product->product_attribute_id]
                                        );
                                    } else {
                                        $image = Image::getImages(
                                            $this->langId,
                                            $orders[$j][$this->selectedColumns->product->product_id],
                                            $orders[$j][$this->selectedColumns->product->product_attribute_id]
                                        );
                                        $image = isset($image[0]) ? $image[0] : null;
                                    }
                                    if ($image) {
                                        $img = new Image($image['id_image']);
                                        $image_path = realpath(_PS_PROD_IMG_DIR_ . $img->getImgPath() . ($this->imageTypeForFile ? '-' . $this->imageTypeForFile : '') . '.jpg');
                                        $html .= '<td valign="middle"><img src="' . $image_path
                                            . '" /></td>';
                                    } else {
                                        $html .= '<td valign="middle"><b>'
                                            . $this->module->l('No Image', 'ExportSales') . '</b></td>';
                                    }
                                } elseif ($k === $this->selectedColumns->product->product_link) {
                                    $link = $this->context->link->getProductLink((int) $orders[$j][$this->selectedColumns->product->product_id], null, null, null, $this->langId);
                                    $html .= '<td valign="middle" style="width:80mm;"><a href="' . $link . '">' . $link . '</a></td>';
                                } elseif ($k === $this->selectedColumns->product->product_image_link) {
                                    // Get image data of the given product id
                                    $image = Image::getCover($orders[$j][$this->selectedColumns->product->product_id]);
                                    if ($image) {
                                        $img_link = $this->context->link->getImageLink($orders[$j][$this->selectedColumns->product->{'order_detail_lang.product_link_rewrite'}], $image['id_image'], $this->imageTypeForFile);
                                        $html .= '<td valign="middle" style="width:80mm;">' . $img_link . '</td>';
                                    } else {
                                        $html .= '<td valign="middle" style="width:80mm;><b>'
                                            . $this->module->l('No Image Link', 'ExportSales') . '</b></td>';
                                    }
                                } elseif ($k === $this->selectedColumns->product->attribute_image_link) {
                                    if (method_exists('Image', 'getBestImageAttribute')) {
                                        // Get image data of the given product id
                                        $image = Image::getBestImageAttribute(
                                            $orders[$j][$this->selectedColumns->shop->id_shop],
                                            $this->langId,
                                            $orders[$j][$this->selectedColumns->product->product_id],
                                            $orders[$j][$this->selectedColumns->product->product_attribute_id]
                                        );
                                    } else {
                                        $image = Image::getImages(
                                            $this->langId,
                                            $orders[$j][$this->selectedColumns->product->product_id],
                                            $orders[$j][$this->selectedColumns->product->product_attribute_id]
                                        );
                                        $image = isset($image[0]) ? $image[0] : null;
                                    }
                                    if ($image) {
                                        $img_link = $this->context->link->getImageLink($orders[$j][$this->selectedColumns->product->{'order_detail_lang.product_link_rewrite'}], $image['id_image'], $this->imageType);
                                        $html .= '<td valign="middle" style="width:80mm;"><a href="' . $img_link . '">' . $img_link . '</a></td>';
                                    } else {
                                        $html .= '<td valign="middle" style="width:80mm;"><b>'
                                            . $this->module->l('No Image Link', 'ExportSales') . '</b></td>';
                                    }
                                } elseif ($k === $this->selectedColumns->category->category_image) {
                                    $cat_img_path = realpath(_PS_CAT_IMG_DIR_ . $orders[$j][$this->selectedColumns->category->id_category] . ($this->catImageTypeForFile ? '-' . $this->catImageTypeForFile : '') . '.jpg');
                                    if (file_exists($cat_img_path)) {
                                        $html .= '<td valign="middle"><img src="' . $cat_img_path . '" /></td>';
                                    } else {
                                        $html .= '<td valign="middle"><b>'
                                            . $this->module->l('No Image', 'ExportSales') . '</b></td>';
                                    }
                                } elseif ($k === $this->selectedColumns->category->category_link) {
                                    if ((int) $orders[$j][$this->selectedColumns->category->id_category]) {
                                        $link = $this->context->link->getCategoryLink((int) $orders[$j][$this->selectedColumns->category->id_category], null, $this->langId);
                                        $html .= '<td valign="middle" style="width:80mm;"><a href="' . $link . '">' . $link . '</a></td>';
                                    } else {
                                        $html .= '<td valign="middle" style="width:80mm;"></td>';
                                    }
                                } elseif ($k === $this->selectedColumns->category->category_image_link) {
                                    $cat_img_path = realpath(_PS_CAT_IMG_DIR_ . $orders[$j][$this->selectedColumns->category->id_category] . ($this->catImageType ? '-' . $this->catImageType : '') . '.jpg');
                                    if (file_exists($cat_img_path)) {
                                        if (method_exists($this->context->link, 'getCatImageLink')) {
                                            // Get image data of the given product id
                                            $cat_img_link = $this->context->link->getCatImageLink(
                                                $orders[$j][$this->selectedColumns->category->link_rewrite],
                                                $orders[$j][$this->selectedColumns->category->id_category],
                                                $this->imageType
                                            );
                                        } else {
                                            $cat_img_link = $this->context->link->getBaseLink() . 'c/'
                                                . $orders[$j][$this->selectedColumns->category->id_category] . ($this->imageType ? '-' . $this->imageType : '') . '/'
                                                . $orders[$j][$this->selectedColumns->category->link_rewrite] . '.jpg';
                                        }
                                        $html .= '<td valign="middle" style="width:80mm;"><a href="' . $cat_img_link . '">' . $cat_img_link . '</a></td>';
                                    } else {
                                        $html .= '<td valign="middle" style="width:80mm;"><b>'
                                            . $this->module->l('No Image Link', 'ExportSales') . '</b></td>';
                                    }
                                } elseif ($k === $this->selectedColumns->manufacturer->manufacturer_link) {
                                    if ((int) $orders[$j][$this->selectedColumns->manufacturer->id_manufacturer]) {
                                        $link = $this->context->link->getManufacturerLink((int) $orders[$j][$this->selectedColumns->manufacturer->id_manufacturer], null, $this->langId);
                                        $html .= '<td valign="middle" style="width:80mm;"><a href="' . $link . '">' . $link . '</a></td>';
                                    } else {
                                        $html .= '<td valign="middle" style="width:80mm;"></td>';
                                    }
                                } elseif ($k === $this->selectedColumns->manufacturer->manufacturer_image) {
                                    $man_img_path = realpath(_PS_MANU_IMG_DIR_ . $orders[$j][$this->selectedColumns->manufacturer->id_manufacturer] . ($this->imageTypeForFile ? '-' . $this->imageTypeForFile : '') . '.jpg');
                                    if (file_exists($man_img_path)) {
                                        $html .= '<td valign="middle"><img src="' . $man_img_path . '" /></td>';
                                    } else {
                                        $html .= '<td valign="middle"><b>'
                                            . $this->module->l('No Image', 'ExportSales') . '</b></td>';
                                    }
                                } elseif ($k === $this->selectedColumns->manufacturer->manufacturer_image_link) {
                                    $man_img_path = realpath(_PS_MANU_IMG_DIR_ . $orders[$j][$this->selectedColumns->manufacturer->id_manufacturer] . ($this->imageType ? '-' . $this->imageType : '') . '.jpg');
                                    if (file_exists($man_img_path)) {
                                        $man_img_link = $this->context->link->getBaseLink() . 'img/m/'
                                            . $orders[$j][$this->selectedColumns->manufacturer->id_manufacturer] . ($this->imageType ? '-' . $this->imageType : '') . '.jpg';
                                        $html .= '<td valign="middle" style="width:80mm;"><a href="' . $man_img_link . '">' . $man_img_link . '</a></td>';
                                    } else {
                                        $html .= '<td valign="middle" style="width:80mm;"><b>'
                                            . $this->module->l('No Image Link', 'ExportSales') . '</b></td>';
                                    }
                                } elseif ($k === $this->selectedColumns->supplier->supplier_link) {
                                    if ((int) $orders[$j][$this->selectedColumns->supplier->id_supplier]) {
                                        $link = $this->context->link->getSupplierLink((int) $orders[$j][$this->selectedColumns->supplier->id_supplier], null, $this->langId);
                                        $html .= '<td valign="middle" style="width:80mm;"><a href="' . $link . '">' . $link . '</a></td>';
                                    } else {
                                        $html .= '<td valign="middle" style="width:80mm;"></td>';
                                    }
                                } elseif ($k === $this->selectedColumns->supplier->supplier_image) {
                                    $supp_img_path = realpath(_PS_SUPP_IMG_DIR_ . $orders[$j][$this->selectedColumns->supplier->id_supplier] . ($this->imageTypeForFile ? '-' . $this->imageTypeForFile : '') . '.jpg');
                                    if (file_exists($supp_img_path)) {
                                        $html .= '<td valign="middle"><img src="' . $supp_img_path . '" /></td>';
                                    } else {
                                        $html .= '<td valign="middle"><b>'
                                            . $this->module->l('No Image', 'ExportSales') . '</b></td>';
                                    }
                                } elseif ($k === $this->selectedColumns->supplier->supplier_image_link) {
                                    $supp_img_path = realpath(_PS_SUPP_IMG_DIR_ . $orders[$j][$this->selectedColumns->supplier->id_supplier] . ($this->imageType ? '-' . $this->imageType : '') . '.jpg');
                                    if (file_exists($supp_img_path)) {
                                        $supp_img_link = $this->context->link->getBaseLink() . 'img/su/'
                                            . $orders[$j][$this->selectedColumns->supplier->id_supplier] . ($this->imageType ? '-' . $this->imageType : '') . '.jpg';
                                        $html .= '<td valign="middle" style="width:80mm;"><a href="' . $supp_img_link . '">' . $supp_img_link . '</a></td>';
                                    } else {
                                        $html .= '<td valign="middle" style="width:80mm;"><b>'
                                            . $this->module->l('No Image Link', 'ExportSales') . '</b></td>';
                                    }
                                } else {
                                    if (isset($wideColumns[$k])) {
                                        $html .= '<td valign="middle" style="width: ' . $wideColumns[$k] . 'mm;">' . $val . '</td>';
                                    } else {
                                        $html .= '<td valign="middle">' . $val . '</td>';
                                    }
                                }
                            } elseif ($i === 0) {
                                if (isset($wideColumns[$k])) {
                                    if ($k === $this->selectedColumns->order->{'order_messages.message'}) {
                                        $val = html_entity_decode($val);
                                    }
                                    $html .= '<td valign="middle" rowspan="'
                                        . ($this->noProduct ? 1 : $group['products']) . '" style="width: ' . $wideColumns[$k] . 'mm;">' . $val . '</td>';
                                } elseif (($val || $val === '0') && in_array($k, $this->moneyColumns)) {
                                    $curr = $this->displayCurrSymbol ? $this->curs->{$orders[$j][$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';
                                    $html .= '<td valign="middle" rowspan="' . ($this->noProduct ? 1 : $group['products']) . '">' . $curr . str_replace('.', $this->decimalSeparator, $val) . '</td>';
                                    if ($this->displayTotals === '1') {
                                        if (isset($totals[$count])) {
                                            $totals[$count]['val'] += (float) $val / $orders[$j][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                        } else {
                                            $totals[$count]['val'] = (float) $val / $orders[$j][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                            $totals[$count]['curr'] = (bool) $curr;
                                        }
                                    }
                                } elseif (($profits || $netProfits) && (
                                        $k === $this->selectedColumns->order->profit_amount ||
                                        $k === $this->selectedColumns->order->profit_margin ||
                                        $k === $this->selectedColumns->order->profit_percentage ||
                                        $k === $this->selectedColumns->order->net_profit_amount ||
                                        $k === $this->selectedColumns->order->net_profit_margin ||
                                        $k === $this->selectedColumns->order->net_profit_percentage
                                    )) {
                                    if ($orders[$j][$this->selectedColumns->order->id_order] !== $groupOrder) {
                                        $totalProducts = $orders[$j - 1][$this->selectedColumns->order->total_products];
                                        $totalDiscountsTaxExcl = $orders[$j - 1][$this->selectedColumns->order->total_discounts_tax_excl];
                                        $curr = $this->displayCurrSymbol ? $this->curs->{$orders[$j - 1][$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';

                                        if ($this->selectedColumns->order->profit_amount) {
                                            $profit_amount = $totalProducts - $groupTotal;
                                            $html = str_replace('profit_amount', $curr . str_replace('.', $this->decimalSeparator, (string) round($profit_amount, $this->fracPart)), $html);
                                            if (!isset($profitAmountNumber)) {
                                                $profitAmountNumber = array_search($this->selectedColumns->order->profit_amount, $headers);
                                            }
                                        }
                                        if ($this->selectedColumns->order->profit_margin) {
                                            $profit_margin = 100 * ($totalProducts - $groupTotal) / $totalProducts;
                                            $html = str_replace('profit_margin', str_replace('.', $this->decimalSeparator, (string) round($profit_margin, $this->fracPart)) . '%', $html);
                                            if (!isset($profitMarginNumber)) {
                                                $profitMarginNumber = array_search($this->selectedColumns->order->profit_margin, $headers);
                                            }
                                        }
                                        if ($this->selectedColumns->order->profit_percentage) {
                                            $profit_percentage = 100 * ($totalProducts - $groupTotal) / $groupTotal;
                                            $html = str_replace('profit_percentage', str_replace('.', $this->decimalSeparator, (string) round($profit_percentage, $this->fracPart)) . '%', $html);
                                            if (!isset($profitPercentageNumber)) {
                                                $profitPercentageNumber = array_search($this->selectedColumns->order->profit_percentage, $headers);
                                            }
                                        }
                                        if ($this->selectedColumns->order->net_profit_amount) {
                                            $net_profit_amount = $totalProducts - $totalDiscountsTaxExcl - $groupTotal;
                                            $html = str_replace('net_profitt_amount', $curr . str_replace('.', $this->decimalSeparator, (string) round($net_profit_amount, $this->fracPart)), $html);
                                            if (!isset($netProfitAmountNumber)) {
                                                $netProfitAmountNumber = array_search($this->selectedColumns->order->net_profit_amount, $headers);
                                            }
                                        }
                                        if ($this->selectedColumns->order->net_profit_margin) {
                                            $net_profit_margin = 100 * ($totalProducts - $totalDiscountsTaxExcl - $groupTotal) / ($totalProducts - $totalDiscountsTaxExcl);
                                            $html = str_replace('net_profitt_margin', str_replace('.', $this->decimalSeparator, (string) round($net_profit_margin, $this->fracPart)) . '%', $html);
                                            if (!isset($netProfitMarginNumber)) {
                                                $netProfitMarginNumber = array_search($this->selectedColumns->order->net_profit_margin, $headers);
                                            }
                                        }
                                        if ($this->selectedColumns->order->net_profit_percentage) {
                                            $net_profit_percentage = 100 * ($totalProducts - $totalDiscountsTaxExcl - $groupTotal) / $groupTotal;
                                            $html = str_replace('net_profitt_percentage', str_replace('.', $this->decimalSeparator, (string) round($net_profit_percentage, $this->fracPart)) . '%', $html);
                                            if (!isset($netProfitPercentageNumber)) {
                                                $netProfitPercentageNumber = array_search($this->selectedColumns->order->net_profit_percentage, $headers);
                                            }
                                        }
                                        $groupTotal = 0;
                                        $groupOrder = $orders[$j][$this->selectedColumns->order->id_order];
                                        if ($this->displayTotals === '1') {
                                            if ($profits) {
                                                $sale += $totalProducts / $orders[$j - 1][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                            }
                                            if ($netProfits) {
                                                $netSale += ($totalProducts - $totalDiscountsTaxExcl) / $orders[$j - 1][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                            }
                                        }
                                    }
                                    if ($k === $this->selectedColumns->order->profit_amount) {
                                        $html .= '<td rowspan="' . ($this->noProduct ? 1 : $group['products']) . '">profit_amount</td>';
                                    } elseif ($k === $this->selectedColumns->order->profit_margin) {
                                        $html .= '<td rowspan="' . ($this->noProduct ? 1 : $group['products']) . '">profit_margin</td>';
                                    } elseif ($k === $this->selectedColumns->order->profit_percentage) {
                                        $html .= '<td rowspan="' . ($this->noProduct ? 1 : $group['products']) . '">profit_percentage</td>';
                                    } elseif ($k === $this->selectedColumns->order->net_profit_amount) {
                                        $html .= '<td rowspan="' . ($this->noProduct ? 1 : $group['products']) . '">net_profitt_amount</td>';
                                    } elseif ($k === $this->selectedColumns->order->net_profit_margin) {
                                        $html .= '<td rowspan="' . ($this->noProduct ? 1 : $group['products']) . '">net_profitt_margin</td>';
                                    } elseif ($k === $this->selectedColumns->order->net_profit_percentage) {
                                        $html .= '<td rowspan="' . ($this->noProduct ? 1 : $group['products']) . '">net_profitt_percentage</td>';
                                    }
                                } else {
                                    $html .= '<td valign="middle" rowspan="' . ($this->noProduct ? 1 : $group['products']) . '">' . $val . '</td>';
                                }
                            }
                            $count++;
                        }
                        $html .= '</tr>';
                        ++$j;
                    }
                }
                $key = $j - 1;
            } else {
                if ($this->noProduct) {
                    $break_points = $this->getBreakPoints($groups);
                }
                foreach ($orders as $key => $value) {
                    if ($this->noProduct && !in_array($key, $break_points)) {
                        if ($profits || $netProfits) {
                            $groupTotal += $value[$this->selectedColumns->product->purchase_supplier_price] * $value[$this->selectedColumns->product->product_quantity];
                            if ($this->displayTotals === '1') {
                                $purchase += (float) $value[$this->selectedColumns->product->purchase_supplier_price] * $value[$this->selectedColumns->product->product_quantity] / $value[$this->selectedColumns->order->{'currency.conversion_rate'}];
                            }
                        }
                        continue;
                    }
                    $i = 0;
                    $totaler = true;
                    $html .= '<tr>';
                    foreach ($value as $k => $val) {
                        if ($i >= $counter) {
                            break;
                        }
                        if (($val || $val === '0') && in_array($k, $this->moneyColumns)) {
                            $curr = $this->displayCurrSymbol ? $this->curs->{$value[$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';
                            $html .= '<td valign="middle">' . $curr . str_replace('.', $this->decimalSeparator, $val) . '</td>';
                            if ($this->displayTotals === '1') {
                                if (!isset($totals[$i])) {
                                    $totals[$i]['val'] = (float) $val / $value[$this->selectedColumns->order->{'currency.conversion_rate'}];
                                    $totals[$i]['curr'] = (bool) $curr;
                                    $groupOrder2 = $groupOrder3 = $value[$this->selectedColumns->order->id_order];
                                } else {
                                    if (in_array($k, $this->orderMoneyColumns)) {
                                        if ($groupOrder2 !== $value[$this->selectedColumns->order->id_order]) {
                                            if ($totaler) {
                                                if ($groupOrder3 !== $value[$this->selectedColumns->order->id_order]) {
                                                    $totals[$i]['val'] += (float) $val / $value[$this->selectedColumns->order->{'currency.conversion_rate'}];
                                                    $groupOrder3 = $value[$this->selectedColumns->order->id_order];
                                                } else {
                                                    $groupOrder2 = $value[$this->selectedColumns->order->id_order];
                                                }
                                                $totaler = false;
                                            } else {
                                                $totals[$i]['val'] += (float) $val / $value[$this->selectedColumns->order->{'currency.conversion_rate'}];
                                            }
                                        }
                                    } else {
                                        $totals[$i]['val'] += (float) $val / $value[$this->selectedColumns->order->{'currency.conversion_rate'}];
                                    }
                                }
                            }
                        } elseif (($profits || $netProfits) && (
                                $k === $this->selectedColumns->order->profit_amount ||
                                $k === $this->selectedColumns->order->profit_margin ||
                                $k === $this->selectedColumns->order->profit_percentage ||
                                $k === $this->selectedColumns->order->net_profit_amount ||
                                $k === $this->selectedColumns->order->net_profit_margin ||
                                $k === $this->selectedColumns->order->net_profit_percentage
                            )) {
                            if ($value[$this->selectedColumns->order->id_order] !== $groupOrder) {
                                $totalProducts = $orders[$key - 1][$this->selectedColumns->order->total_products];
                                $totalDiscountsTaxExcl = $orders[$key - 1][$this->selectedColumns->order->total_discounts_tax_excl];
                                $curr = $this->displayCurrSymbol ? $this->curs->{$orders[$key - 1][$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';

                                if ($this->selectedColumns->order->profit_amount) {
                                    $profit_amount = $totalProducts - $groupTotal;
                                    $html = str_replace('profit_amount', $curr . str_replace('.', $this->decimalSeparator, (string) round($profit_amount, $this->fracPart)), $html);
                                    if (!isset($profitAmountNumber)) {
                                        $profitAmountNumber = array_search($this->selectedColumns->order->profit_amount, $headers);
                                    }
                                }
                                if ($this->selectedColumns->order->profit_margin) {
                                    $profit_margin = 100 * ($totalProducts - $groupTotal) / $totalProducts;
                                    $html = str_replace('profit_margin', str_replace('.', $this->decimalSeparator, (string) round($profit_margin, $this->fracPart)) . '%', $html);
                                    if (!isset($profitMarginNumber)) {
                                        $profitMarginNumber = array_search($this->selectedColumns->order->profit_margin, $headers);
                                    }
                                }
                                if ($this->selectedColumns->order->profit_percentage) {
                                    $profit_percentage = 100 * ($totalProducts - $groupTotal) / $groupTotal;
                                    $html = str_replace('profit_percentage', str_replace('.', $this->decimalSeparator, (string) round($profit_percentage, $this->fracPart)) . '%', $html);
                                    if (!isset($profitPercentageNumber)) {
                                        $profitPercentageNumber = array_search($this->selectedColumns->order->profit_percentage, $headers);
                                    }
                                }
                                if ($this->selectedColumns->order->net_profit_amount) {
                                    $net_profit_amount = $totalProducts - $totalDiscountsTaxExcl - $groupTotal;
                                    $html = str_replace('net_profitt_amount', $curr . str_replace('.', $this->decimalSeparator, (string) round($net_profit_amount, $this->fracPart)), $html);
                                    if (!isset($netProfitAmountNumber)) {
                                        $netProfitAmountNumber = array_search($this->selectedColumns->order->net_profit_amount, $headers);
                                    }
                                }
                                if ($this->selectedColumns->order->net_profit_margin) {
                                    $net_profit_margin = 100 * ($totalProducts - $totalDiscountsTaxExcl - $groupTotal) / ($totalProducts - $totalDiscountsTaxExcl);
                                    $html = str_replace('net_profitt_margin', str_replace('.', $this->decimalSeparator, (string) round($net_profit_margin, $this->fracPart)) . '%', $html);
                                    if (!isset($netProfitMarginNumber)) {
                                        $netProfitMarginNumber = array_search($this->selectedColumns->order->net_profit_margin, $headers);
                                    }
                                }
                                if ($this->selectedColumns->order->net_profit_percentage) {
                                    $net_profit_percentage = 100 * ($totalProducts - $totalDiscountsTaxExcl - $groupTotal) / $groupTotal;
                                    $html = str_replace('net_profitt_percentage', str_replace('.', $this->decimalSeparator, (string) round($net_profit_percentage, $this->fracPart)) . '%', $html);
                                    if (!isset($netProfitPercentageNumber)) {
                                        $netProfitPercentageNumber = array_search($this->selectedColumns->order->net_profit_percentage, $headers);
                                    }
                                }
                                $groupTotal = 0;
                                $groupOrder = $value[$this->selectedColumns->order->id_order];
                                if ($this->displayTotals === '1') {
                                    if ($profits) {
                                        $sale += $totalProducts / $orders[$key - 1][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                    }
                                    if ($netProfits) {
                                        $netSale += ($totalProducts - $totalDiscountsTaxExcl) / $orders[$key - 1][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                    }
                                }
                            }
                            if ($k === $this->selectedColumns->order->profit_amount) {
                                $html .= '<td>profit_amount</td>';
                            } elseif ($k === $this->selectedColumns->order->profit_margin) {
                                $html .= '<td>profit_margin</td>';
                            } elseif ($k === $this->selectedColumns->order->profit_percentage) {
                                $html .= '<td>profit_percentage</td>';
                            } elseif ($k === $this->selectedColumns->order->net_profit_amount) {
                                $html .= '<td>net_profitt_amount</td>';
                            } elseif ($k === $this->selectedColumns->order->net_profit_margin) {
                                $html .= '<td>net_profitt_margin</td>';
                            } elseif ($k === $this->selectedColumns->order->net_profit_percentage) {
                                $html .= '<td>net_profitt_percentage</td>';
                            }
                        } elseif ($k === $this->selectedColumns->product->purchase_supplier_price) {
                            if (!$this->purchaseSupplierPrice) {
                                $curr = $this->displayCurrSymbol ? $this->curs->{$value[$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';
                                $html .= '<td>' . $curr . str_replace('.', $this->decimalSeparator, $val) . '</td>';
                                if ($this->displayTotals === '1') {
                                    if (isset($totals[$i])) {
                                        $totals[$i]['val'] += (float) $val * $value[$this->selectedColumns->product->product_quantity] / $value[$this->selectedColumns->order->{'currency.conversion_rate'}];
                                    } else {
                                        $totals[$i]['val'] = (float) $val * $value[$this->selectedColumns->product->product_quantity] / $value[$this->selectedColumns->order->{'currency.conversion_rate'}];
                                        $totals[$i]['curr'] = (bool) $curr;
                                    }
                                }
                            }
                            if ($profits || $netProfits) {
                                if ($groupOrder !== $value[$this->selectedColumns->order->id_order]) {
                                    $totalProducts = $orders[$key - 1][$this->selectedColumns->order->total_products];
                                    $totalDiscountsTaxExcl = $orders[$key - 1][$this->selectedColumns->order->total_discounts_tax_excl];
                                    $curr = $this->displayCurrSymbol ? $this->curs->{$orders[$key - 1][$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';

                                    if ($this->selectedColumns->order->profit_amount) {
                                        $profit_amount = $totalProducts - $groupTotal;
                                        $curr = $this->displayCurrSymbol ? $this->curs->{$orders[$key - 1][$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';
                                        $html = str_replace('profit_amount', $curr . str_replace('.', $this->decimalSeparator, (string) round($profit_amount, $this->fracPart)), $html);
                                        if (!isset($profitAmountNumber)) {
                                            $profitAmountNumber = array_search($this->selectedColumns->order->profit_amount, $headers);
                                        }
                                    }
                                    if ($this->selectedColumns->order->profit_margin) {
                                        $profit_margin = 100 * ($totalProducts - $groupTotal) / $totalProducts;
                                        $html = str_replace('profit_margin', str_replace('.', $this->decimalSeparator, (string) round($profit_margin, $this->fracPart)) . '%', $html);
                                        if (!isset($profitMarginNumber)) {
                                            $profitMarginNumber = array_search($this->selectedColumns->order->profit_margin, $headers);
                                        }
                                    }
                                    if ($this->selectedColumns->order->profit_percentage) {
                                        $profit_percentage = 100 * ($totalProducts - $groupTotal) / $groupTotal;
                                        $html = str_replace('profit_percentage', str_replace('.', $this->decimalSeparator, (string) round($profit_percentage, $this->fracPart)) . '%', $html);
                                        if (!isset($profitPercentageNumber)) {
                                            $profitPercentageNumber = array_search($this->selectedColumns->order->profit_percentage, $headers);
                                        }
                                    }
                                    if ($this->selectedColumns->order->net_profit_amount) {
                                        $net_profit_amount = $totalProducts - $totalDiscountsTaxExcl - $groupTotal;
                                        $curr = $this->displayCurrSymbol ? $this->curs->{$orders[$key - 1][$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';
                                        $html = str_replace('net_profitt_amount', $curr . str_replace('.', $this->decimalSeparator, (string) round($net_profit_amount, $this->fracPart)), $html);
                                        if (!isset($netProfitAmountNumber)) {
                                            $netProfitAmountNumber = array_search($this->selectedColumns->order->net_profit_amount, $headers);
                                        }
                                    }
                                    if ($this->selectedColumns->order->net_profit_margin) {
                                        $net_profit_margin = 100 * ($totalProducts - $totalDiscountsTaxExcl - $groupTotal) / ($totalProducts - $totalDiscountsTaxExcl);
                                        $html = str_replace('net_profitt_margin', str_replace('.', $this->decimalSeparator, (string) round($net_profit_margin, $this->fracPart)) . '%', $html);
                                        if (!isset($netProfitMarginNumber)) {
                                            $netProfitMarginNumber = array_search($this->selectedColumns->order->net_profit_margin, $headers);
                                        }
                                    }
                                    if ($this->selectedColumns->order->net_profit_percentage) {
                                        $net_profit_percentage = 100 * ($totalProducts - $totalDiscountsTaxExcl - $groupTotal) / $groupTotal;
                                        $html = str_replace('net_profitt_percentage', str_replace('.', $this->decimalSeparator, (string) round($net_profit_percentage, $this->fracPart)) . '%', $html);
                                        if (!isset($netProfitPercentageNumber)) {
                                            $netProfitPercentageNumber = array_search($this->selectedColumns->order->net_profit_percentage, $headers);
                                        }
                                    }
                                    $groupTotal = 0;
                                    $groupOrder = $value[$this->selectedColumns->order->id_order];
                                    if ($this->displayTotals === '1') {
                                        if ($profits) {
                                            $sale += $totalProducts / $orders[$key - 1][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                        }
                                        if ($netProfits) {
                                            $netSale += ($totalProducts - $totalDiscountsTaxExcl) / $orders[$key - 1][$this->selectedColumns->order->{'currency.conversion_rate'}];
                                        }
                                    }
                                }
                                $groupTotal += $val * $value[$this->selectedColumns->product->product_quantity];
                                if ($this->displayTotals === '1') {
                                    $purchase += (float) $val * $value[$this->selectedColumns->product->product_quantity] / $value[$this->selectedColumns->order->{'currency.conversion_rate'}];
                                }
                            }
                        } elseif ($k === $this->selectedColumns->product->product_quantity) {
                            $html .= '<td>' . $val . '</td>';
                            if ($this->displayTotals === '1') {
                                if (isset($totals[$i])) {
                                    $totals[$i]['val'] += $val;
                                } else {
                                    $totals[$i]['val'] = $val;
                                    $totals[$i]['curr'] = 0;
                                }
                            }
                        } elseif ($k === $this->selectedColumns->product->reduction_percent) {
                            $html .= '<td>' . str_replace('.', $this->decimalSeparator, $val) . '</td>';
                            if ($this->displayTotals === '1') {
                                $reductionTotals['reduced'] += $value[$this->selectedColumns->product->total_price_tax_excl];
                                $reductionTotals['full'] += 100 * $value[$this->selectedColumns->product->total_price_tax_excl] / (100 - $val);
                                if (!isset($rPNumber)) {
                                    $rPNumber = $count;
                                }
                            }
                        } elseif ($k === $this->selectedColumns->product->product_image) {
                            // Get image data of the given product id
                            $image = Image::getCover($value[$this->selectedColumns->product->product_id]);
                            if ($image) {
                                $img = new Image($image['id_image']);
                                $image_path = realpath(_PS_PROD_IMG_DIR_ . $img->getImgPath() . ($this->imageTypeForFile ? '-' . $this->imageTypeForFile : '') . '.jpg');
                                $html .= '<td valign="middle"><img src="' . $image_path . '" /></td>';
                            } else {
                                $html .= '<td valign="middle"><b>'
                                    . $this->module->l('No Image', 'ExportSales') . '</b></td>';
                            }
                        } elseif ($k === $this->selectedColumns->product->attribute_image) {
                            if (method_exists('Image', 'getBestImageAttribute')) {
                                // Get image data of the given product id
                                $image = Image::getBestImageAttribute(
                                    $value[$this->selectedColumns->shop->id_shop],
                                    $this->langId,
                                    $value[$this->selectedColumns->product->product_id],
                                    $value[$this->selectedColumns->product->product_attribute_id]
                                );
                            } else {
                                $image = Image::getImages(
                                    $this->langId,
                                    $value[$this->selectedColumns->product->product_id],
                                    $value[$this->selectedColumns->product->product_attribute_id]
                                );
                                $image = isset($image[0]) ? $image[0] : null;
                            }
                            if ($image) {
                                $img = new Image($image['id_image']);
                                $image_path = realpath(_PS_PROD_IMG_DIR_ . $img->getImgPath() . ($this->imageTypeForFile ? '-' . $this->imageTypeForFile : '') . '.jpg');
                                $html .= '<td valign="middle"><img src="' . $image_path . '" /></td>';
                            } else {
                                $html .= '<td valign="middle"><b>'
                                    . $this->module->l('No Image', 'ExportSales') . '</b></td>';
                            }
                        } elseif ($k === $this->selectedColumns->product->product_link) {
                            $link = $this->context->link->getProductLink((int) $value[$this->selectedColumns->product->product_id], null, null, null, $this->langId);
                            $html .= '<td valign="middle" style="width:80mm;"><a href="' . $link . '">' . $link . '</a></td>';
                        } elseif ($k === $this->selectedColumns->product->product_image_link) {
                            // Get image data of the given product id
                            $image = Image::getCover($value[$this->selectedColumns->product->product_id]);
                            if ($image) {
                                $img_link = $this->context->link->getImageLink($value[$this->selectedColumns->product->{'order_detail_lang.product_link_rewrite'}], $image['id_image'], $this->imageType);
                                $html .= '<td valign="middle" style="width:80mm;"><a href="' . $img_link . '">' . $img_link . '</a></td>';
                            } else {
                                $html .= '<td valign="middle" style="width:80mm;"><b>'
                                    . $this->module->l('No Image Link', 'ExportSales') . '</b></td>';
                            }
                        } elseif ($k === $this->selectedColumns->product->attribute_image_link) {
                            if (method_exists('Image', 'getBestImageAttribute')) {
                                // Get image data of the given product id
                                $image = Image::getBestImageAttribute(
                                    $value[$this->selectedColumns->shop->id_shop],
                                    $this->langId,
                                    $value[$this->selectedColumns->product->product_id],
                                    $value[$this->selectedColumns->product->product_attribute_id]
                                );
                            } else {
                                $image = Image::getImages(
                                    $this->langId,
                                    $value[$this->selectedColumns->product->product_id],
                                    $value[$this->selectedColumns->product->product_attribute_id]
                                );
                                $image = isset($image[0]) ? $image[0] : null;
                            }
                            if ($image) {
                                $img_link = $this->context->link->getImageLink($value[$this->selectedColumns->product->{'order_detail_lang.product_link_rewrite'}], $image['id_image'], $this->imageType);
                                $html .= '<td valign="middle"><a href="' . $img_link . '">' . $img_link . '</a></td>';
                            } else {
                                $html .= '<td valign="middle"><b>'
                                    . $this->module->l('No Image Link', 'ExportSales') . '</b></td>';
                            }
                        } elseif ($k === $this->selectedColumns->category->category_image) {
                            $cat_img_path = realpath(_PS_CAT_IMG_DIR_ . $value[$this->selectedColumns->category->id_category] . ($this->catImageTypeForFile ? '-' . $this->catImageTypeForFile : '') . '.jpg');
                            if (file_exists($cat_img_path)) {
                                $html .= '<td valign="middle"><img src="' . $cat_img_path . '" /></td>';
                            } else {
                                $html .= '<td valign="middle"><b>'
                                    . $this->module->l('No Image', 'ExportSales') . '</b></td>';
                            }
                        } elseif ($k === $this->selectedColumns->category->category_link) {
                            if ((int) $value[$this->selectedColumns->category->id_category]) {
                                $link = $this->context->link->getCategoryLink((int) $value[$this->selectedColumns->category->id_category], null, $this->langId);
                                $html .= '<td valign="middle" style="width:80mm;"><a href="' . $link . '">' . $link . '</a></td>';
                            } else {
                                $html .= '<td valign="middle" style="width:80mm;"></td>';
                            }
                        } elseif ($k === $this->selectedColumns->category->category_image_link) {
                            $cat_img_path = realpath(_PS_CAT_IMG_DIR_ . $value[$this->selectedColumns->category->id_category] . ($this->catImageType ? '-' . $this->catImageType : '') . '.jpg');
                            if (file_exists($cat_img_path)) {
                                if (method_exists($this->context->link, 'getCatImageLink')) {
                                    // Get image data of the given product id
                                    $cat_img_link = $this->context->link->getCatImageLink(
                                        $value[$this->selectedColumns->category->link_rewrite],
                                        $value[$this->selectedColumns->category->id_category],
                                        $this->imageType
                                    );
                                } else {
                                    $cat_img_link = $this->context->link->getBaseLink() . 'c/'
                                        . $value[$this->selectedColumns->category->id_category] . ($this->imageType ? '-' . $this->imageType : '') . '/'
                                        . $value[$this->selectedColumns->category->link_rewrite] . '.jpg';
                                }
                                $html .= '<td valign="middle" style="width:80mm;"><a href="' . $cat_img_link . '">' . $cat_img_link . '</a></td>';
                            } else {
                                $html .= '<td valign="middle" style="width:80mm;"><b>'
                                    . $this->module->l('No Image Link', 'ExportSales') . '</b></td>';
                            }
                        } elseif ($k === $this->selectedColumns->manufacturer->manufacturer_link) {
                            if ((int) $value[$this->selectedColumns->manufacturer->id_manufacturer]) {
                                $link = $this->context->link->getManufacturerLink((int) $value[$this->selectedColumns->manufacturer->id_manufacturer], null, $this->langId);
                                $html .= '<td valign="middle"><a href="' . $link . '">' . $link . '</a></td>';
                            } else {
                                $html .= '<td valign="middle"></td>';
                            }
                        } elseif ($k === $this->selectedColumns->manufacturer->manufacturer_image) {
                            $man_img_path = realpath(_PS_MANU_IMG_DIR_ . $value[$this->selectedColumns->manufacturer->id_manufacturer] . ($this->imageTypeForFile ? '-' . $this->imageTypeForFile : '') . '.jpg');
                            if (file_exists($man_img_path)) {
                                $html .= '<td valign="middle" style="width:80mm;"><img src="' . $man_img_path . '" /></td>';
                            } else {
                                $html .= '<td valign="middle" style="width:80mm;"><b>'
                                    . $this->module->l('No Image', 'ExportSales') . '</b></td>';
                            }
                        } elseif ($k === $this->selectedColumns->manufacturer->manufacturer_image_link) {
                            $man_img_path = realpath(_PS_MANU_IMG_DIR_ . $value[$this->selectedColumns->manufacturer->id_manufacturer] . ($this->imageType ? '-' . $this->imageType : '') . '.jpg');
                            if (file_exists($man_img_path)) {
                                $man_img_link = $this->context->link->getBaseLink() . 'img/m/'
                                    . $value[$this->selectedColumns->manufacturer->id_manufacturer] . ($this->imageType ? '-' . $this->imageType : '') . '.jpg';
                                $html .= '<td valign="middle" style="width:80mm;"><a href="' . $man_img_link . '">' . $man_img_link . '</a></td>';
                            } else {
                                $html .= '<td valign="middle" style="width:80mm;"><b>'
                                    . $this->module->l('No Image Link', 'ExportSales') . '</b></td>';
                            }
                        } elseif ($k === $this->selectedColumns->supplier->supplier_link) {
                            if ((int) $value[$this->selectedColumns->supplier->id_supplier]) {
                                $link = $this->context->link->getSupplierLink((int) $value[$this->selectedColumns->supplier->id_supplier], null, $this->langId);
                                $html .= '<td valign="middle" style="width:80mm;"><a href="' . $link . '">' . $link . '</a></td>';
                            } else {
                                $html .= '<td valign="middle" style="width:80mm;"></td>';
                            }
                        } elseif ($k === $this->selectedColumns->supplier->supplier_image) {
                            $supp_img_path = realpath(_PS_SUPP_IMG_DIR_ . $value[$this->selectedColumns->supplier->id_supplier] . ($this->imageTypeForFile ? '-' . $this->imageTypeForFile : '') . '.jpg');
                            if (file_exists($supp_img_path)) {
                                $html .= '<td valign="middle" style="width:80mm;"><img src="' . $supp_img_path . '" /></td>';
                            } else {
                                $html .= '<td valign="middle" style="width:80mm;"><b>'
                                    . $this->module->l('No Image', 'ExportSales') . '</b></td>';
                            }
                        } elseif ($k === $this->selectedColumns->supplier->supplier_image_link) {
                            $supp_img_path = realpath(_PS_SUPP_IMG_DIR_ . $value[$this->selectedColumns->supplier->id_supplier] . ($this->imageType ? '-' . $this->imageType : '') . '.jpg');
                            if (file_exists($supp_img_path)) {
                                $supp_img_link = $this->context->link->getBaseLink() . 'img/su/'
                                    . $value[$this->selectedColumns->supplier->id_supplier] . ($this->imageType ? '-' . $this->imageType : '') . '.jpg';
                                $html .= '<td valign="middle" style="width:80mm;"><a href="' . $supp_img_link . '">' . $supp_img_link . '</a></td>';
                            } else {
                                $html .= '<td valign="middle" style="width:80mm;"><b>'
                                    . $this->module->l('No Image Link', 'ExportSales') . '</b></td>';
                            }
                        } elseif (isset($wideColumns[$k])) {
                            if ($k === $this->selectedColumns->order->{'order_messages.message'}) {
                                $val = html_entity_decode($val);
                            }
                            $html .= '<td valign="middle" style="width: ' . $wideColumns[$k] . 'mm;">' . $val . '</td>';
                        } else {
                            $html .= '<td valign="middle">' . $val . '</td>';
                        }
                        $i++;
                    }
                    $html .= '</tr>';
                }
            }

            $totalProducts = $orders[$key][$this->selectedColumns->order->total_products];
            $totalDiscountsTaxExcl = $orders[$key][$this->selectedColumns->order->total_discounts_tax_excl];
            $curr = $this->displayCurrSymbol ? $this->curs->{$orders[$key][$this->selectedColumns->order->{'currency.iso_code'}]} . ' ' : '';

            if (isset($this->selectedColumns->order->profit_amount)) {
                $profit_amount = $totalProducts - $groupTotal;
                $html = str_replace('profit_amount', $curr . str_replace('.', $this->decimalSeparator, (string) round($profit_amount, $this->fracPart)), $html);
            }
            if (isset($this->selectedColumns->order->profit_margin)) {
                $profit_margin = 100 * ($totalProducts - $groupTotal) / $totalProducts;
                $html = str_replace('profit_margin', str_replace('.', $this->decimalSeparator, (string) round($profit_margin, $this->fracPart)) . '%', $html);
            }
            if (isset($this->selectedColumns->order->profit_percentage)) {
                $profit_percentage = 100 * ($totalProducts - $groupTotal) / $groupTotal;
                $html = str_replace('profit_percentage', str_replace('.', $this->decimalSeparator, (string) round($profit_percentage, $this->fracPart)) . '%', $html);
            }

            if (isset($this->selectedColumns->order->net_profit_amount)) {
                $net_profit_amount = $totalProducts - $totalDiscountsTaxExcl - $groupTotal;
                $html = str_replace('net_profitt_amount', $curr . str_replace('.', $this->decimalSeparator, (string) round($net_profit_amount, $this->fracPart)), $html);
            }
            if (isset($this->selectedColumns->order->net_profit_margin)) {
                $net_profit_margin = 100 * ($totalProducts - $totalDiscountsTaxExcl - $groupTotal) / ($totalProducts - $totalDiscountsTaxExcl);
                $html = str_replace('net_profitt_margin', str_replace('.', $this->decimalSeparator, (string) round($net_profit_margin, $this->fracPart)) . '%', $html);
            }
            if (isset($this->selectedColumns->order->net_profit_percentage)) {
                $net_profit_percentage = 100 * ($totalProducts - $totalDiscountsTaxExcl - $groupTotal) / $groupTotal;
                $html = str_replace('net_profitt_percentage', str_replace('.', $this->decimalSeparator, (string) round($net_profit_percentage, $this->fracPart)) . '%', $html);
            }

            if ($this->displayTotals === '1') {
                if ($profits) {
                    $sale += $totalProducts / $orders[$key][$this->selectedColumns->order->{'currency.conversion_rate'}];
                }
                if ($netProfits) {
                    $netSale += ($totalProducts - $totalDiscountsTaxExcl) / $orders[$key][$this->selectedColumns->order->{'currency.conversion_rate'}];
                }

                if (isset($profitAmountNumber)) {
                    $val = $sale - $purchase;
                    $totals[$profitAmountNumber]['val'] = $val;
                    $totals[$profitAmountNumber]['curr'] = $this->displayCurrSymbol;
                }
                if (isset($profitMarginNumber)) {
                    $val = 100 * ($sale - $purchase) / $sale;
                    $totals[$profitMarginNumber]['val'] = $val . '%';
                }
                if (isset($profitPercentageNumber)) {
                    $val = 100 * ($sale - $purchase) / $purchase;
                    $totals[$profitPercentageNumber]['val'] = $val . '%';
                }
                if (isset($netProfitAmountNumber)) {
                    $val = $netSale - $purchase;
                    $totals[$netProfitAmountNumber]['val'] = $val;
                    $totals[$netProfitAmountNumber]['curr'] = $this->displayCurrSymbol;
                }
                if (isset($netProfitMarginNumber)) {
                    $val = 100 * ($netSale - $purchase) / $netSale;
                    $totals[$netProfitMarginNumber]['val'] = $val . '%';
                }
                if (isset($netProfitPercentageNumber)) {
                    $val = 100 * ($netSale - $purchase) / $purchase;
                    $totals[$netProfitPercentageNumber]['val'] = $val . '%';
                }
                if (isset($rPNumber)) {
                    $val = 100 * ($reductionTotals['full'] - $reductionTotals['reduced']) / $reductionTotals['full'];
                    $totals[$rPNumber]['val'] = $val . '%';
                }

                $def_curr = Configuration::get('OXSRP_DEF_CURR_SMBL') . ' ';

                $html .= '<tr class="totals">';
                for ($i = 0; $i < $counter - $psp; $i++) {
                    if (isset($totals[$i])) {
                        $ccurr = '';
                        if (isset($totals[$i]['curr']) && $totals[$i]['curr']) {
                            $ccurr .= $def_curr;
                        }
                        if (Tools::substr($totals[$i]['val'], -1) === '%') {
                            $val = round(rtrim($totals[$i]['val'], '%'), $this->fracPart) . '%';
                        } else {
                            $val = round($totals[$i]['val'], $this->fracPart);
                        }
                        if (isset($wideColumns[$headers[$i]])) {
                            $html .= '<td style="height:15mm; background-color: #e7ffd8; width: ' . $wideColumns[$headers[$i]] . 'mm;"><b>' . $ccurr . str_replace('.', $this->decimalSeparator, $val);
                        } else {
                            $html .= '<td style="height:15mm; background-color: #e7ffd8;"><b>' . $ccurr . str_replace('.', $this->decimalSeparator, $val);
                        }
                        $html .= '</b></td>';
                    } else {
                        if (isset($wideColumns[$headers[$i]])) {
                            $html .= '<td style="width: ' . $wideColumns[$headers[$i]] . 'mm; background-color: #e7ffd8;"></td>';
                        } else {
                            $html .= '<td style="background-color: #e7ffd8;"></td>';
                        }
                    }
                }
                $html .= '</tr>';
            }

            if ($this->displayFooter === '1') {
                $html .= '<tr>';
//                $headers = array_keys($orders[0]);
                for ($i = 0; $i < $counter - $psp; $i++) {
                    if (isset($wideColumns[$headers[$i]])) {
                        $html .= '<th valign="middle" style="background-color: #DCF0FF; border-top: 2px solid #7CC67C; width: '
                            . $wideColumns[$headers[$i]] . 'mm;"><b>'
                            . $headers[$i] . '</b></th>';
                    } else {
                        $html .= '<th valign="middle" style="background-color: #DCF0FF; border-top: 2px solid #7CC67C;"><b>' . $headers[$i] . '</b></th>';
                    }
//                    if ($headers[$i] === $this->selectedColumns->customer->email ||
//                            $headers[$i] === $this->selectedColumns->product->{'order_detail_lang.attributes'} ||
//                            $headers[$i] === $this->selectedColumns->product->{'product_features.features'} ||
//                            $headers[$i] === $this->selectedColumns->order->{'order_state_history.order_history'} ||
//                            $headers[$i] === $this->selectedColumns->payment->payment_details) {
//                        $html .= '<th valign="middle" style="background-color: #DCF0FF; width: 50mm;"><b>'
//                                . $headers[$i] . '</b></th>';
//                    } else {
//                        $html .= '<th valign="middle" style="background-color: #DCF0FF;"><b>' . $headers[$i] . '</b></th>';
//                    }
                }
                $html .= '</tr>';
            }
            $html .= '</table>';

            if ($this->displayExplanations === '1') {
                $html .= '<div></div><div></div><span>';
                if (isset($profitAmountNumber)) {
                    $html .= '* <b>' . $this->module->l('Gross Profit Amount', 'ExportSales') . '</b>' . $this->module->l(' = S - P. Sale price of an order minus total purchase price of products in that order (taxes excluded).', 'ExportSales') . '<br />';
                }
                if (isset($profitMarginNumber)) {
                    $html .= '* <b>' . $this->module->l('Gross Profit Margin', 'ExportSales') . '</b>' . $this->module->l(' = 100 * (S - P) / S. Sale price of an order minus total purchase price of products in that order divided by the sale price multiplied by 100 (taxes excluded).', 'ExportSales') . '<br />';
                }
                if (isset($profitPercentageNumber)) {
                    $html .= '* <b>' . $this->module->l('Gross Profit Percentage', 'ExportSales') . '</b>' . $this->module->l(' = 100 * (S - P) / P. Sale price of an order minus total purchase price of products in that order divided by the purchase price multiplied by 100 (taxes excluded).', 'ExportSales') . '<br />';
                }
                if (isset($netProfitAmountNumber)) {
                    $html .= '* <b>' . $this->module->l('Net Profit Amount', 'ExportSales') . '</b>' . $this->module->l(' = S - D - P. Sale price of an order minus the discount of that order minus total purchase price of products in that order (taxes excluded).', 'ExportSales') . '<br />';
                }
                if (isset($netProfitMarginNumber)) {
                    $html .= '* <b>' . $this->module->l('Net Profit Margin', 'ExportSales') . '</b>' . $this->module->l(' = 100 * (S - D - P) / (S - D). Sale price of an order minus the discount of that order minus total purchase price of products in that order divided by the sale price minus the discount of that order multiplied by 100 (taxes excluded).', 'ExportSales') . '<br />';
                }
                if (isset($netProfitPercentageNumber)) {
                    $html .= '* <b>' . $this->module->l('Net Profit Percentage', 'ExportSales') . '</b>' . $this->module->l(' = 100 * (S - D - P) / P. Sale price of an order minus the discount of that order minus total purchase price of products in that order divided by the purchase price multiplied by 100 (taxes excluded).', 'ExportSales') . '<br />';
                }
                if (!$this->purchaseSupplierPrice && $this->selectedColumns->product->purchase_supplier_price) {
                    $html .= '* <b>' . $this->module->l('Product Quantity', 'ExportSales') . '</b>' . $this->module->l(' = Product purchase price multiplied by product purchased quantity, then summed.', 'ExportSales');
                }

                $html .= '</span>';
            }

            if ($this->displayBestSellers === '1' && !is_numeric($this->auto)) {
                $lngh = array(30, 42, 90, 31, 31, 31, 31, 31, 31, 31);
                $html .= '<div></div><div></div><div></div>';
                if ($this->displayHeader === '1') {
                    $html .= '<h2>' . $this->module->l('Sales by Products', 'ExportSales') . '</h2>';
                }
                $sales = $this->getBestSellers();
                $html .= '<table cellpadding = "10" cellspacing = "0">';
                $html .= '<tr>';
                $html .= '<th style="width: ' . $lngh[0] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Product ID', 'ExportSales') . '</b></th>';
                $html .= '<th style="width: ' . $lngh[1] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Product Reference', 'ExportSales') . '</b></th>';
                $html .= '<th style="width: ' . $lngh[2] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Product Name', 'ExportSales') . '</b></th>';
                $html .= '<th style="width: ' . $lngh[3] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Sold Quantity', 'ExportSales') . '</b></th>';
                $html .= '<th style="width: ' . $lngh[4] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Total Profit (Tax Excl.)', 'ExportSales') . '</b></th>';
                $html .= '<th style="width: ' . $lngh[5] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Total Price (Tax Excl.)', 'ExportSales') . '</b></th>';
                $html .= '<th style="width: ' . $lngh[6] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Total Price (Tax Incl.)', 'ExportSales') . '</b></th>';
                $html .= '<th style="width: ' . $lngh[7] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Total Paid (Tax Excl.)', 'ExportSales') . '</b></th>';
                $html .= '<th style="width: ' . $lngh[8] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Total Paid (Tax Incl.)', 'ExportSales') . '</b></th>';
                $html .= '<th style="width: ' . $lngh[9] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Total Really Paid', 'ExportSales') . '</b></th>';
                $html .= '</tr>';
                foreach ($sales as $val) {
                    $html .= '<tr>';
                    $i = 0;
                    foreach ($val as $v) {
                        $html .= '<td style = "width: ' . $lngh[$i++] . 'mm;">' . $v . '</td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '</table>';
            }

            if ($this->displayProductCombs === '1' && !is_numeric($this->auto)) {
                $lngh = array(30, 90, 35, 120, 31, 31, 31);
                $html .= '<div></div><div></div><div></div>';
                if ($this->displayHeader === '1') {
                    $html .= '<h2>' . $this->module->l('Sales by Combinations', 'ExportSales') . '</h2>';
                }
                $sales = $this->getProductCombs();
                $html .= '<table cellpadding = "10" cellspacing = "0">';
                $html .= '<tr>';
                $html .= '<th style="width: ' . $lngh[0] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Product ID', 'ExportSales') . '</b></th>';
                $html .= '<th style="width: ' . $lngh[1] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Product Name', 'ExportSales') . '</b></th>';
                $html .= '<th style="width: ' . $lngh[2] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Combination ID', 'ExportSales') . '</b></th>';
                $html .= '<th style="width: ' . $lngh[3] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Combination', 'ExportSales') . '</b></th>';
                $html .= '<th style="width: ' . $lngh[4] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Sold Quantity', 'ExportSales') . '</b></th>';
                $html .= '<th style="width: ' . $lngh[5] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Total Profit (Tax Excl.)', 'ExportSales') . '</b></th>';
                $html .= '<th style="width: ' . $lngh[6] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Total Price (Tax Excl.)', 'ExportSales') . '</b></th>';
                $html .= '</tr>';
                foreach ($sales as $val) {
                    $html .= '<tr>';
                    $i = 0;
                    foreach ($val as $v) {
                        $html .= '<td style = "width: ' . $lngh[$i++] . 'mm;">' . $v . '</td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '</table>';
            }

            if ($this->displayDailySales === '1' && !is_numeric($this->auto)) {
                $lngh = array(30, 31, 31, 31);
                $html .= '<div></div><div></div><div></div>';
                if ($this->displayHeader === '1') {
                    $html .= '<h2>' . $this->module->l('Daily Sales', 'ExportSales') . '</h2>';
                }
                $sales = $this->getDailySales();
                $html .= '<table cellpadding = "10" cellspacing = "0">';
                $html .= '<tr>';
                $html .= '<th style="width: ' . $lngh[0] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Product ID', 'ExportSales') . '</b></th>';
                $html .= '<th style="width: ' . $lngh[4] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Sold Quantity', 'ExportSales') . '</b></th>';
                $html .= '<th style="width: ' . $lngh[5] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Total Profit (Tax Excl.)', 'ExportSales') . '</b></th>';
                $html .= '<th style="width: ' . $lngh[6] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Total Price (Tax Excl.)', 'ExportSales') . '</b></th>';
                $html .= '</tr>';
                foreach ($sales as $val) {
                    $html .= '<tr>';
                    $i = 0;
                    foreach ($val as $v) {
                        $html .= '<td style = "width: ' . $lngh[$i++] . 'mm;">' . $v . '</td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '</table>';
            }

            if ($this->displayMonthlySales === '1' && !is_numeric($this->auto)) {
                $lngh = array(30, 31, 31, 31);
                $html .= '<div></div><div></div><div></div>';
                if ($this->displayHeader === '1') {
                    $html .= '<h2>' . $this->module->l('Monthly Sales', 'ExportSales') . '</h2>';
                }
                $sales = $this->getMonthlySales();
                $html .= '<table cellpadding = "10" cellspacing = "0">';
                $html .= '<tr>';
                $html .= '<th style="width: ' . $lngh[0] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Product ID', 'ExportSales') . '</b></th>';
                $html .= '<th style="width: ' . $lngh[4] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Sold Quantity', 'ExportSales') . '</b></th>';
                $html .= '<th style="width: ' . $lngh[5] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Total Profit (Tax Excl.)', 'ExportSales') . '</b></th>';
                $html .= '<th style="width: ' . $lngh[6] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Total Price (Tax Excl.)', 'ExportSales') . '</b></th>';
                $html .= '</tr>';
                foreach ($sales as $val) {
                    $html .= '<tr>';
                    $i = 0;
                    foreach ($val as $v) {
                        $html .= '<td style = "width: ' . $lngh[$i++] . 'mm;">' . $v . '</td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '</table>';
            }

            if ($this->displayTopCustomers === '1' && !is_numeric($this->auto)) {
                $lngh = array(30, 62, 30, 30, 30, 70, 70);
                $html .= '<div></div><div></div><div></div>';
                if ($this->displayHeader === '1') {
                    $html .= '<h2>' . $this->module->l('Sales by Customers', 'ExportSales') . '</h2>';
                }
                $sales = $this->getTopCustomers();
                $html .= '<table cellpadding = "10" cellspacing = "0">';
                $html .= '<tr>';
                $html .= '<th style = "width: ' . $lngh[0] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Customer ID', 'ExportSales') . '</b></th>';
                $html .= '<th style = "width: ' . $lngh[1] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Customer Email', 'ExportSales') . '</b></th>';
                $html .= '<th style = "width: ' . $lngh[2] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Customer Firstname', 'ExportSales') . '</b></th>';
                $html .= '<th style = "width: ' . $lngh[3] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Customer Lastname', 'ExportSales') . '</b></th>';
                $html .= '<th style = "width: ' . $lngh[4] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Number of Orders', 'ExportSales') . '</b></th>';
                $html .= '<th style = "width: ' . $lngh[5] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Total Orders (Tax Excl.)', 'ExportSales') . '</b></th>';
                $html .= '<th style = "width: ' . $lngh[6] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Total Orders with Discount (Tax Excl.)', 'ExportSales') . '</b></th>';
                $html .= '</tr>';
                foreach ($sales as $val) {
                    $html .= '<tr>';
                    $i = 0;
                    foreach ($val as $v) {
                        $html .= '<td style="width: ' . $lngh[$i++] . 'mm;">' . $v . '</td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '</table>';
            }

            if ($this->displayPaymentMethods === '1' && !is_numeric($this->auto)) {
                $lngh = array(30, 30, 30, 30, 70, 70, 70, 70);
                $html .= '<div></div><div></div><div></div>';
                if ($this->displayHeader === '1') {
                    $html .= '<h2>' . $this->module->l('Sales by Payment Methods', 'ExportSales') . '</h2>';
                }
                $sales = $this->getPaymentSales();
                $html .= '<table cellpadding = "10" cellspacing = "0">';
                $html .= '<tr>';
                $html .= '<th style = "width: ' . $lngh[0] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Payment', 'ExportSales') . '</b></th>';
                $html .= '<th style = "width: ' . $lngh[1] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Module', 'ExportSales') . '</b></th>';
                $html .= '<th style = "width: ' . $lngh[2] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Number of Orders', 'ExportSales') . '</b></th>';
                $html .= '<th style = "width: ' . $lngh[3] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Total Orders (Tax Excl.)', 'ExportSales') . '</b></th>';
                $html .= '<th style = "width: ' . $lngh[4] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Total Orders with Discount (Tax Excl.)', 'ExportSales') . '</b></th>';
                $html .= '<th style = "width: ' . $lngh[5] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Refunded Amount', 'ExportSales') . '</b></th>';
                $html .= '<th style = "width: ' . $lngh[6] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Refunded Amount ROCK (Tax Excl.)', 'ExportSales') . '</b></th>';
                $html .= '<th style = "width: ' . $lngh[7] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Refunded Amount ROCK (Tax Incl.)', 'ExportSales') . '</b></th>';

                $html .= '</tr>';
                foreach ($sales as $val) {
                    $html .= '<tr>';
                    $i = 0;
                    foreach ($val as $v) {
                        $html .= '<td style="width: ' . $lngh[$i++] . 'mm;">' . $v . '</td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '</table>';
            }

            if ($this->displayPaymentMethods2 === '1' && !is_numeric($this->auto)) {
                $lngh = array(30, 30, 30, 30, 70, 70, 70, 70);
                $html .= '<div></div><div></div><div></div>';
                if ($this->displayHeader === '1') {
                    $html .= '<h2>' . $this->module->l('Sales by Payment Options', 'ExportSales') . '</h2>';
                }
                $sales = $this->getPaymentSales2();
                $html .= '<table cellpadding = "10" cellspacing = "0">';
                $html .= '<tr>';
                $html .= '<th style = "width: ' . $lngh[0] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Payment', 'ExportSales') . '</b></th>';
                $html .= '<th style = "width: ' . $lngh[1] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Module', 'ExportSales') . '</b></th>';
                $html .= '<th style = "width: ' . $lngh[2] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Number of Orders', 'ExportSales') . '</b></th>';
                $html .= '<th style = "width: ' . $lngh[3] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Total Orders (Tax Excl.)', 'ExportSales') . '</b></th>';
                $html .= '<th style = "width: ' . $lngh[4] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Total Orders with Discount (Tax Excl.)', 'ExportSales') . '</b></th>';
                $html .= '<th style = "width: ' . $lngh[5] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Refunded Amount', 'ExportSales') . '</b></th>';
                $html .= '<th style = "width: ' . $lngh[6] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Refunded Amount ROCK (Tax Excl.)', 'ExportSales') . '</b></th>';
                $html .= '<th style = "width: ' . $lngh[7] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Refunded Amount ROCK (Tax Incl.)', 'ExportSales') . '</b></th>';

                $html .= '</tr>';
                foreach ($sales as $val) {
                    $html .= '<tr>';
                    $i = 0;
                    foreach ($val as $v) {
                        $html .= '<td style="width: ' . $lngh[$i++] . 'mm;">' . $v . '</td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '</table>';
            }

            if ($this->displaySalesByCategories === '1' && !is_numeric($this->auto)) {
                $lngh = array(30, 90, 31);
                $html .= '<div></div><div></div><div></div>';
                if ($this->displayHeader === '1') {
                    $html .= '<h2>' . $this->module->l('Sales by Categories', 'ExportSales') . '</h2>';
                }
                $sales = $this->getSalesByCategories();
                $html .= '<table cellpadding = "10" cellspacing = "0">';
                $html .= '<tr>';
                $html .= '<th style="width: ' . $lngh[0] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Category ID', 'ExportSales') . '</b></th>';
                $html .= '<th style="width: ' . $lngh[1] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Category Name', 'ExportSales') . '</b></th>';
                $html .= '<th style="width: ' . $lngh[2] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Total Price (Tax Excl.)', 'ExportSales') . '</b></th>';
                $html .= '</tr>';
                foreach ($sales as $val) {
                    $html .= '<tr>';
                    $i = 0;
                    foreach ($val as $v) {
                        $html .= '<td style = "width: ' . $lngh[$i++] . 'mm;">' . $v . '</td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '</table>';
            }

            if ($this->displaySalesByBrands === '1' && !is_numeric($this->auto)) {
                $lngh = array(30, 90, 31);
                $html .= '<div></div><div></div><div></div>';
                if ($this->displayHeader === '1') {
                    $html .= '<h2>' . $this->module->l('Sales by Brands', 'ExportSales') . '</h2>';
                }
                $sales = $this->getSalesByBrands();
                $html .= '<table cellpadding = "10" cellspacing = "0">';
                $html .= '<tr>';
                $html .= '<th style="width: ' . $lngh[0] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Brand ID', 'ExportSales') . '</b></th>';
                $html .= '<th style="width: ' . $lngh[1] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Brand Name', 'ExportSales') . '</b></th>';
                $html .= '<th style="width: ' . $lngh[2] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Total Price (Tax Excl.)', 'ExportSales') . '</b></th>';
                $html .= '</tr>';
                foreach ($sales as $val) {
                    $html .= '<tr>';
                    $i = 0;
                    foreach ($val as $v) {
                        $html .= '<td style = "width: ' . $lngh[$i++] . 'mm;">' . $v . '</td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '</table>';
            }

            if ($this->displaySalesBySuppliers === '1' && !is_numeric($this->auto)) {
                $lngh = array(30, 90, 31);
                $html .= '<div></div><div></div><div></div>';
                if ($this->displayHeader === '1') {
                    $html .= '<h2>' . $this->module->l('Sales by Suppliers', 'ExportSales') . '</h2>';
                }
                $sales = $this->getSalesBySuppliers();
                $html .= '<table cellpadding = "10" cellspacing = "0">';
                $html .= '<tr>';
                $html .= '<th style="width: ' . $lngh[0] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Supplier ID', 'ExportSales') . '</b></th>';
                $html .= '<th style="width: ' . $lngh[1] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Supplier Name', 'ExportSales') . '</b></th>';
                $html .= '<th style="width: ' . $lngh[2] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Total Price (Tax Excl.)', 'ExportSales') . '</b></th>';
                $html .= '</tr>';
                foreach ($sales as $val) {
                    $html .= '<tr>';
                    $i = 0;
                    foreach ($val as $v) {
                        $html .= '<td style = "width: ' . $lngh[$i++] . 'mm;">' . $v . '</td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '</table>';
            }

            if ($this->displaySalesByAttributes === '1' && !is_numeric($this->auto)) {
                $lngh = array(90, 90, 31);
                $html .= '<div></div><div></div><div></div>';
                if ($this->displayHeader === '1') {
                    $html .= '<h2>' . $this->module->l('Sales by Attributes', 'ExportSales') . '</h2>';
                }
                $sales = $this->getSalesByAttributes();
                $html .= '<table cellpadding = "10" cellspacing = "0">';
                $html .= '<tr>';
                $html .= '<th style="width: ' . $lngh[0] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Attribute Group Name', 'ExportSales') . '</b></th>';
                $html .= '<th style="width: ' . $lngh[1] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Attribute Name', 'ExportSales') . '</b></th>';
                $html .= '<th style="width: ' . $lngh[2] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Total Price (Tax Excl.)', 'ExportSales') . '</b></th>';
                $html .= '</tr>';
                foreach ($sales as $val) {
                    $html .= '<tr>';
                    $i = 0;
                    foreach ($val as $v) {
                        $html .= '<td style = "width: ' . $lngh[$i++] . 'mm;">' . $v . '</td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '</table>';
            }

            if ($this->displaySalesByFeatures === '1' && !is_numeric($this->auto)) {
                $lngh = array(90, 90, 90, 31);
                $html .= '<div></div><div></div><div></div>';
                if ($this->displayHeader === '1') {
                    $html .= '<h2>' . $this->module->l('Sales by Features', 'ExportSales') . '</h2>';
                }
                $sales = $this->getSalesByFeatures();
                $html .= '<table cellpadding = "10" cellspacing = "0">';
                $html .= '<tr>';
                $html .= '<th style="width: ' . $lngh[0] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Feature Name', 'ExportSales') . '</b></th>';
                $html .= '<th style="width: ' . $lngh[1] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Feature Value', 'ExportSales') . '</b></th>';
                $html .= '<th style="width: ' . $lngh[2] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Is Custom', 'ExportSales') . '</b></th>';
                $html .= '<th style="width: ' . $lngh[3] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Total Price (Tax Excl.)', 'ExportSales') . '</b></th>';
                $html .= '</tr>';
                foreach ($sales as $val) {
                    $html .= '<tr>';
                    $i = 0;
                    foreach ($val as $v) {
                        $html .= '<td style = "width: ' . $lngh[$i++] . 'mm;">' . $v . '</td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '</table>';
            }

            if ($this->displaySalesByShops === '1' && !is_numeric($this->auto)) {
                $lngh = array(30, 90, 90, 31);
                $html .= '<div></div><div></div><div></div>';
                if ($this->displayHeader === '1') {
                    $html .= '<h2>' . $this->module->l('Sales by Shops', 'ExportSales') . '</h2>';
                }
                $sales = $this->getSalesByShops();
                $html .= '<table cellpadding = "10" cellspacing = "0">';
                $html .= '<tr>';
                $html .= '<th style="width: ' . $lngh[0] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Shop ID', 'ExportSales') . '</b></th>';
                $html .= '<th style="width: ' . $lngh[1] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Shop Name', 'ExportSales') . '</b></th>';
                $html .= '<th style="width: ' . $lngh[2] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Shop Group Name', 'ExportSales') . '</b></th>';
                $html .= '<th style="width: ' . $lngh[3] . 'mm; background-color: #DCF0FF;"><b>' . $this->module->l('Total Price (Tax Excl.)', 'ExportSales') . '</b></th>';
                $html .= '</tr>';
                foreach ($sales as $val) {
                    $html .= '<tr>';
                    $i = 0;
                    foreach ($val as $v) {
                        $html .= '<td style = "width: ' . $lngh[$i++] . 'mm;">' . $v . '</td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '</table>';
            }
        } else {
            $width = 210;
            $height = 210 * sqrt(2);
            $orientation = 'P';
            $html .= '<h2>' . $this->module->l('No Data', 'ExportSales') . '</h2>';
        }

        $html .= '
                </body>
                </html>
                ';
//        d($html);
        if (version_compare(_PS_VERSION_, '1.7') === -1) {
            require_once _PS_TOOL_DIR_ . 'tcpdf/tcpdf.php';
        }

        $tcpdf = new TCPDF($orientation, PDF_UNIT, array(round($width, 2), round($height, 2)), true, 'UTF-8', false);
        $tcpdf->SetCreator('Tehran Alishov');
        $tcpdf->SetTitle($this->module->l('Sales', 'ExportSales'));
        $tcpdf->setHeaderData('', '', PDF_HEADER_TITLE, PDF_HEADER_STRING);
        $tcpdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $tcpdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $tcpdf->setFooterMargin(PDF_MARGIN_FOOTER);
        $tcpdf->SetMargins(PDF_MARGIN_LEFT, '15', PDF_MARGIN_RIGHT);
        $tcpdf->setPrintHeader(false);
        $tcpdf->setPrintFooter(false);
        $tcpdf->SetAutoPageBreak(true, 10);
        $tcpdf->SetFont('dejavusans', '', 10);
        $tcpdf->SetDisplayMode('real');
        $tcpdf->AddPage();
        $tcpdf->writeHTML($html);

        $id = Tools::getAdminToken($this->context->employee->id);
        $target_action = Tools::getValue('orders_target_action');

        if ($this->auto) {
            $fileName = $this->docName . '.pdf';
            $tcpdf->Output($this->tempDir . $fileName, 'F');
            return $fileName;
        } elseif ($target_action !== 'download') {
//                $id = mt_rand().uniqid();
            mkdir($this->outputDir . '/' . $id);
            $file = $this->outputDir . '/' . $id . '/' . $this->docName . '.pdf';
            $tcpdf->Output($file, 'F');


            // If target action is either email or ftp
            if ($target_action === 'email') {
                $subject = $this->module->l('Your Sales (by Advanced Sales Reports module)', 'ExportSales');
//                $content = $this->module->l('The details are in the attachment.', 'ExportSales');
//                $this->sendEmail(explode(';', Tools::getValue('target_action_to_emails')), $subject, $content, $file);
                $this->sendPSEmail(explode(';', Tools::getValue('target_action_to_emails')), $subject, $file);
            } elseif ($target_action === 'ftp') {
                $ftp_type = Tools::getValue('orders_target_action_ftp_type');
                $ftp_mode = Tools::getValue('orders_target_action_ftp_mode');
                $ftp_url = Tools::getValue('orders_target_action_ftp_url');
                $ftp_port = Tools::getValue('orders_target_action_ftp_port');
                $ftp_username = Tools::getValue('orders_target_action_ftp_username');
                $ftp_password = Tools::getValue('orders_target_action_ftp_password');
                $ftp_folder = Tools::getValue('orders_target_action_ftp_folder');
                if ($ftp_folder) {
                    $ftp_folder .= '/' . $this->docName;
                }
//                $ftp_file_ext = Tools::getValue('orders_target_action_ftp_file_ext');

                $this->uploadToFTP($file, $ftp_type, $ftp_mode, $ftp_url, $ftp_port, $ftp_username, $ftp_password, $ftp_folder, 0);
            }

            unlink($file);
            rmdir($this->outputDir . '/' . $id);
        } else {
            $id = mt_rand() . uniqid();
            $filePath = $this->outputDir . '/' . $id . '.pdf';
            $tcpdf->Output($filePath, 'F');
            header('Content-type: application/json');
            echo json_encode(array(
                'status' => 'ok',
                'id' => $id,
                'type' => 'pdf',
                'name' => $this->docName . (Tools::getValue('orders_general_add_ts') && $this->filteredDate ? '_' . $this->filteredDate : '')
            ));
        }
    }
}
