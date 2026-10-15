<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';
class Psgiftcards extends Module
{
    public $front_controller = null;
    /**
     * @var string
     */
    public $css_path;
    /**
     * @var string
     */
    public $js_path;
    /**
     * @var string
     */
    public $awaiting_validation;
    /**
     * @var string
     */
    public $to_configure;
    /**
     * @var string
     */
    public $scheduled;
    /**
     * @var string
     */
    public $downloaded;
    /**
     * @var string
     */
    public $sent;
    /**
     * @var string
     */
    public $version;
    /**
     * @var string
     */
    public $author_address;
    /**
     * @var bool
     */
    public $bootstrap;
    /**
     * @var string
     */
    public $controller_name;
    /**
     * @var string
     */
    public $output;
    /**
     * @var bool
     */
    public $ps_version;
    /**
     * @var string
     */
    public $img_path;
    /**
     * @var string
     */
    public $docs_path;
    /**
     * @var string
     */
    public $logo_path;
    /**
     * @var string
     */
    public $module_path;
    /**
     * @var string
     */
    public $mails_path;
    /**
     * @var array
     */
    public $pdfTranslations;
    /**
     * @var array
     */
    public $frontControllerTranslations;
    /**
     * @var string
     */
    public $confirmUninstall;
    /**
     * @var string
     */
    public $controller_url;

    /**
     * @var array
     */
    public $configurationFields = [
        'PS_GIFCARDS_VALIDITY' => '12',
        'PS_GIFCARDS_SHIPPING' => '1',
        'PS_GIFCARDS_PREFIX_CODE' => 'GC',
        'PS_GIFCARDS_TEMPLATE' => 'sendy',
        'PS_GIFCARDS_PRIMARY_COLOR' => '#00B9DC',
        'PS_GIFCARDS_SECONDARY_COLOR' => '#D78F00',
    ];

    public $hooks = [
        'actionValidateOrder',
        'actionOrderStatusUpdate',
        'displayCustomerAccount',
        'displayAdminProductsExtra',
        'actionProductDelete',
        'displayOrderConfirmation',
        'actionAdminControllerSetMedia',
    ];

    public function __construct()
    {
        // Settings
        $this->name = 'psgiftcards';
        $this->version = '2.1.0';
        $this->author = 'PrestaShop';
        $this->need_instance = 0;

        $this->module_key = '58e6edfbd8268dd5356242a994c08848';
        $this->author_address = '0x64aa3c1e4034d07015f639b0e171b0d7b27d01aa';

        // bootstrap -> always set to true
        $this->bootstrap = true;

        parent::__construct();

        $this->controller_name = 'AdminAjaxPsgiftcards';

        $this->output = '';

        // some tranlations
        $this->awaiting_validation = $this->l('Awaiting validation');
        $this->to_configure = $this->l('To be configured');
        $this->scheduled = $this->l('Scheduled');
        $this->downloaded = $this->l('Downloaded');
        $this->sent = $this->l('Sent');

        $this->displayName = $this->l('Premium Gift card');
        $this->description = $this->l('This module allows you to create and sell gift cards to your clients on your website');
        $this->ps_version = (bool) version_compare(_PS_VERSION_, '1.7', '>=');

        // Settings paths
        $this->js_path = $this->_path . 'views/js/';
        $this->css_path = $this->_path . 'views/css/';
        $this->img_path = $this->_path . 'views/img/';
        $this->docs_path = $this->_path . 'docs/';
        $this->logo_path = $this->_path . 'logo.png';
        $this->module_path = $this->_path;
        $this->mails_path = $this->local_path . 'mails/';

        $this->pdfTranslations = [
            'from' => $this->l('From'),
            'to' => $this->l('To'),
            'message' => $this->l('Message'),
            'message1' => $this->l('Enjoy your'),
            'message2' => $this->l('gift card until'),
            'message3' => $this->l('with the following code :'),
            'excellent_shooping' => $this->l('We wish you an excellent shopping on'),
            'text_footer1' => $this->l('To take advantage of your gift card, select one or several products of your choice on the website'),
            'text_footer2' => $this->l('then add your code in the dedicated field in your cart before ending your purchase. This gift card can be used in several times : if the balance is positive, you will receive an email with the new code and the remaining amount.'),
        ];

        $this->frontControllerTranslations = [
            'recipientNameEmpty' => $this->l('Recipient name is empty.'),
            'buyerNameEmpty' => $this->l('Buyer name is empty.'),
            'recipientMailError' => $this->l('Recipient mail is not a valid mail.'),
            'recipientMailEmpty' => $this->l('Recipient mail is empty.'),
            'invalidDate' => $this->l('Invalid date.'),
            'notValidDate' => $this->l('The date is not a valid date.'),
            'provideDate' => $this->l('Please, provide a date to send the gift card.'),
            'giftCard' => $this->l('Gift card'),
            'mailObject' => $this->l('You have received a gift !'),
        ];

        // Confirm uninstall
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');
        $this->ps_versions_compliancy = ['min' => '1.6.1.0', 'max' => _PS_VERSION_];
    }

    /**
     * install()
     *
     * @return bool
     */
    public function install()
    {
        $this->createTax();
        $id_taxRulesGroup = TaxRulesGroup::getIdByName($this->l('Gift card (0%)'));

        foreach ($this->configurationFields as $key => $value) {
            Configuration::updateValue($key, $value);
        }

        Configuration::updateValue('PS_GIFCARDS_TAX', $id_taxRulesGroup);

        include dirname(__FILE__) . '/sql/install.php'; // sql querries

        // register hook used by the module
        if (
            parent::install() &&
            $this->installTab() &&
            $this->registerHook($this->hooks)
        ) {
            return true;
        } else { // if something wrong return false
            $this->_errors[] = $this->l('There was an error during the uninstallation. Please contact us through Addons website.');

            return false;
        }
    }

    /**
     * uninstall()
     *
     * @return bool
     */
    public function uninstall()
    {
        include dirname(__FILE__) . '/sql/uninstall.php'; // sql querriers

        foreach ($this->configurationFields as $key => $value) {
            Configuration::deleteByName($key);
        }
        // unregister hook
        if (
            parent::uninstall() &&
            $this->uninstallTab() &&
            $this->unregisterHook('actionValidateOrder') &&
            $this->unregisterHook('displayBackOfficeHeader') &&
            $this->unregisterHook('actionOrderStatusUpdate') &&
            $this->unregisterHook('displayCustomerAccount') &&
            $this->unregisterHook('displayOrderConfirmation') &&
            $this->unregisterHook('displayAdminProductsExtra') &&
            $this->unregisterHook('actionProductDelete')
        ) {
            return parent::uninstall();
        } else {
            $this->_errors[] = $this->l('There was an error during the desinstallation. Please contact us through Addons website');

            return false;
        }
    }

    /**
     * This method is often use to create an ajax controller
     *
     * @return bool
     */
    public function installTab()
    {
        $tab = new Tab();
        $tab->active = true;
        $tab->class_name = $this->controller_name;
        $tab->name = [];
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $this->name;
        }
        $tab->id_parent = -1;
        $tab->module = $this->name;
        $result = $tab->add();

        return (bool) $result;
    }

    /**
     * Uninstall tab
     *
     * @return bool
     */
    public function uninstallTab()
    {
        $id_tab = (int) Tab::getIdFromClassName($this->controller_name);
        if ($id_tab) {
            $tab = new Tab($id_tab);
            if (Validate::isLoadedObject($tab)) {
                return $tab->delete();
            } else {
                $return = false;
            }
        } else {
            $return = true;
        }

        return (bool) $return;
    }

    /**
     * load dependencies
     */
    public function loadAsset()
    {
        $controller = Context::getContext()->controller;
        // Load CSS
        $css = [
            $this->css_path . 'font-awesome.min.css',
            $this->css_path . 'faq.css',
            $this->css_path . 'front.css',
            $this->css_path . 'menu.css',
            $this->css_path . 'back.css',
            $this->css_path . 'datatables.css',
            $this->css_path . $this->name . '.css',
            $this->css_path . '17_style.css',
            $this->css_path . 'template.css',

            $this->js_path . 'pickr/css/pickr.min.css',
            $this->js_path . 'pickr/css/pickr-override.css',

            $this->css_path . 'admin/reminder_plan/general.css',
            $this->css_path . 'reminder_plan/listing.css',
            $this->css_path . 'reminder_plan/email_test.css',
        ];

        $this->context->controller->addCSS($css, 'all');

        $ckeditor_path = _PS_MODULE_DIR_ . $this->name . '/node_modules/ckeditor/ckeditor.js';

        // Load JS
        $jss = [
            $this->js_path . 'vue.min.js',
            $this->js_path . 'vue-grid.js',
            $this->js_path . 'faq.js',
            $this->js_path . 'menu.js',
            $this->js_path . 'front.js',
            $this->js_path . 'back.js',
            $this->js_path . 'datatables.min.js',
            $this->js_path . 'sweetalert.min.js',

            $this->js_path . 'pickr/js/pickr.js',
            $this->js_path . 'dropzone/dropzone.js',
            $ckeditor_path,

            $this->js_path . 'reminder_plan/email_test.js',
            $this->js_path . 'reminder_plan/general.js',
            $this->js_path . 'reminder_plan/listing.js',

            $this->js_path . 'reminder_plan/steps/discount.js',
            $this->js_path . 'reminder_plan/steps/target_frequency.js',
            $this->js_path . 'reminder_plan/steps/template_email.js',
            $this->js_path . 'reminder_plan/steps/template_show.js',
            $this->js_path . 'reminder_plan/ckeditor.js',
        ];

        // prestashop plugin
        $controller->addJqueryPlugin('colorpicker');
        $controller->addJqueryUI('ui.sortable');

        $this->context->controller->addJS($jss);

        // Clean memory
        unset($jss, $css);
    }

    /**
     * FAQ API
     */
    public function loadFaq()
    {
        $api = new APIFAQ();
        $faq = $api->getData($this->module_key, $this->version);

        return $faq;
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $template_datas = [];
        $faq = $this->loadFaq();
        $this->loadAsset();
        $this->postProcess();
        $this->checkIfGiftcardIsUsed();
        $this->manageEmailFolders();
        $aPresetsTags = $this->getCustomEmailContent();
        $giftcardsMailLang = GiftcardHistory::getGiftcardsMailsLang();

        if (!empty($giftcardsMailLang)) {
            foreach ($giftcardsMailLang as $key => $value) {
                $template_datas[$value['id_lang']]['email_subject'] = $value['email_subject'];
                $template_datas[$value['id_lang']]['email_content'] = $value['email_content'];
                $template_datas[$value['id_lang']]['email_cta'] = $value['email_cta'];
                $template_datas[$value['id_lang']]['email_unsubscribe'] = $value['email_unsubscribe'];
                if (!empty($value['email_discount']) && $value['email_discount'] != 'To configure') {
                    $template_datas['email_discount'] = Tools::getShopDomainSsl(true) . '/modules/psgiftcards/views/img/DL/' . $value['email_discount'];
                }
            }
        }

        // some stuff useful in smarty
        $id_lang = $this->context->language->id;
        $id_shop = $this->context->shop->id;
        $params = ['configure' => $this->name];
        $moduleAdminLink = $this->context->link->getAdminLink('AdminModules', true, false, $params);

        // Controllers
        $this->controller_url = $this->context->link->getAdminLink($this->controller_name);

        //get readme
        $iso_lang = Language::getIsoById($id_lang);
        $doc = $this->docs_path . 'readme_' . $iso_lang . '.pdf';

        // get current page
        $currentPage = 'conf';
        $getPage = Tools::getValue('page');
        if (!empty($getPage)) {
            $currentPage = Tools::getValue('page');
        }

        $params = ['addproduct'];
        $productPage = $this->context->link->getAdminLink('AdminProducts', true);

        // get all tax
        $taxes = TaxRulesGroup::getTaxRulesGroups(true);

        // get all order state
        $orderStates = OrderState::getOrderStates($id_lang);

        if (version_compare(_PS_VERSION_, '1.7.3', '<') == true) {
            $array = ['id_product' => 0];
            $newProductLink = $this->context->link->getAdminLink('AdminProducts', true, $array);
        } else {
            $sfContainer = PrestaShop\PrestaShop\Adapter\SymfonyContainer::getInstance();
            $newProductLink = $sfContainer->get('router')->generate('admin_product_new') . '#tab-hooks';
        }

        // total number of giftcard sold
        $totalGiftcardSold = GiftcardHistory::getGiftcardsTotal($id_shop);
        // total number of giftcard which has been already used
        $totalGiftcardUsed = GiftcardHistory::getGiftcardsUsed();
        // total amount
        $totalAmount = GiftcardHistory::getGiftcardTotalAmount();
        //get giftcard history
        $giftcardsHistory = GiftcardHistory::getGiftcardsHistory();
        $template_appearance['model_name'] = Configuration::get('PS_GIFCARDS_TEMPLATE');
        $exist = strpos(Configuration::get('PS_GIFCARDS_PRIMARY_COLOR'), '#');
        $template_appearance['primary_color'] = ($exist != false) ? Configuration::get('PS_GIFCARDS_PRIMARY_COLOR') : '#' . Configuration::get('PS_GIFCARDS_PRIMARY_COLOR');
        $template_appearance['secondary_color'] = ($exist != false) ? Configuration::get('PS_GIFCARDS_SECONDARY_COLOR') : '#' . Configuration::get('PS_GIFCARDS_SECONDARY_COLOR');

        // assign var to smarty
        $this->context->smarty->assign([
            'module_name' => $this->name,
            'id_shop' => $id_shop,
            'module_version' => $this->version,
            'moduleAdminLink' => $moduleAdminLink,
            'id_lang' => $id_lang,
            'controller_url' => $this->controller_url,
            'apifaq' => $faq,
            'doc' => $doc,
            'module_display' => $this->displayName,
            'module_path' => $this->module_path,
            'logo_path' => $this->logo_path,
            'img_path' => $this->img_path,
            'languages' => $this->context->controller->getLanguages(),
            'taxes' => $taxes,
            'orderStates' => $orderStates,
            'gc_validity' => Configuration::get('PS_GIFCARDS_VALIDITY'),
            'gc_free_shipping' => Configuration::get('PS_GIFCARDS_SHIPPING'),
            'gc_tax' => Configuration::get('PS_GIFCARDS_TAX'),
            'gc_giftcard_prefix_code' => Configuration::get('PS_GIFCARDS_PREFIX_CODE'),
            'totalGiftcardSold' => $totalGiftcardSold,
            'totalGiftcardUsed' => $totalGiftcardUsed,
            'totalAmount' => $totalAmount,
            'giftcardsHistory' => $giftcardsHistory,
            'productPage' => $productPage,
            'newProductLink' => $newProductLink,
            'defaultFormLanguage' => (int) $this->context->employee->id_lang,
            'tokenCron' => Tools::getAdminToken('psgiftcards'),
            'tokenOrder' => Tools::getAdminTokenLite('AdminOrders'),
            'tokenCartRule' => Tools::getAdminTokenLite('AdminCartRules'),
            'currentPage' => $currentPage,
            'ps_base_dir' => _PS_BASE_URL_,
            'base_dir' => _PS_BASE_URL_,
            'ps_version' => $this->ps_version,
            'site_url' => Configuration::get('PS_SHOP_NAME', null, null, $id_shop),
            'logo_shop_url' => _PS_IMG_ . Configuration::get('PS_LOGO', null, null, 1),
            'cron_url' => $this->context->link->getModulelink($this->name, 'Cron', ['token' => Tools::getAdminToken('psgiftcards')]),

            'color1' => Configuration::get('PS_GIFCARDS_PRIMARY_COLOR'),
            'color2' => Configuration::get('PS_GIFCARDS_SECONDARY_COLOR'),
            'template' => Configuration::get('PS_GIFCARDS_TEMPLATE'),

            'employeeLangId' => $this->context->employee->id_lang,

            'custom_content' => $aPresetsTags['content'],
            //'discount_content' => $aPresetsTags['discount'],
            'unsubscribe_content' => $aPresetsTags['unsubscribe'],

            'shop_name' => Configuration::get('PS_SHOP_NAME', null, null, $id_shop),

            'blabla' => '',
            'buyerName' => '',
            'recipientName' => '',
            'text' => '',
            'price' => '',
            'validity' => '',
            'cart_rule_code' => '',
            'shopName' => '',
            'shop_addr1' => '',
            'shop_addr2' => '',
            'shop_city' => '',
            'shop_zipcode' => '',
            'shop_country' => '',
            'shop_phone' => '',
            'shop_fax' => '',

            'template_datas' => $template_datas,
            'template_appearance' => $template_appearance,
        ]);

        //translate jquery datatable
        Media::addJsDef([
            'img_link' => $template_datas,
            'successMessage' => $this->l('Success! Your configurations have been saved.'),
            'errorMessage' => $this->l('Please make sure you have filled an email subject before saving your configurations.'),
            'dataTableShow' => $this->l('Show'),
            'dataTableEntries' => $this->l('entries'),
            'dataTableShowing' => $this->l('Showing'),
            'dataTableTo' => $this->l('to'),
            'dataTableOf' => $this->l('of'),
            'dataTablePrevious' => $this->l('Previous'),
            'dataTableNext' => $this->l('Next'),
            'dataTableSearch' => $this->l('Search'),
            'color1' => Configuration::get('PS_GIFCARDS_PRIMARY_COLOR'),
            'psg_template_demo_first_name' => 'John',
            'psg_template_demo_last_name' => 'Doe',
            'psg_template_demo_gender' => $this->l('Mr.'),
            'psg_template_demo_nb_product' => '3',
            'psg_template_demo_cart_link' => '<a href="' . $this->context->link->getPageLink('cart') . '">' . $this->l('Cart link') . '</a>',
            'psg_template_demo_discount_code' => '<span class="secondary_color-textcolor discount-code">CODE' . date('y') . '</span>',
            'psg_template_demo_discount_value' => '<span class="secondary_color-textcolor discount-value">' . Tools::displayPrice(25) . '  </span>',
            'psg_template_demo_discount_validity' => date('d/m/Y'),
            'psg_template_demo_shop_link' => '<a href="' . $this->context->link->getPageLink('index') . '">' . $this->context->shop->name . '</a>',
            'psg_template_demo_unsubscribe' => '<a href="#" class="unsubscribe_link">' . $this->l('Unsubscribe') . '</a>',
            'psg_template_demo_buyer_message' => $this->context->smarty->fetch($this->local_path . 'views/templates/admin/emails/templateBuyerMessage.tpl'),

            'default_error_upload' => $this->l('An error occured, please check your zip file'),
            'file_not_valid' => $this->l('The file is not valid.'),
            'langs' => $this->context->controller->getLanguages(),
        ]);

        $this->output .= $this->context->smarty->fetch($this->local_path . 'views/templates/admin/menu.tpl');

        return $this->output;
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitPsgiftcardsModule')) {
            $errors = [];

            $gcValidity = (int) Tools::getValue('GC_VALIDITY');
            $gcTax = (int) Tools::getValue('GC_TAX');
            $gcOrderState = (int) Tools::getValue('GC_ORDER_STATE');
            $gcFreeShipping = (int) Tools::getValue('GC_FREE_SHIPPING');
            $gcGiftcardPrefixCode = pSQL(Tools::getValue('GC_GIFTCARD_PREFIX_CODE'));

            if (empty($gcValidity)) {
                $errors[] = $this->l('Gift card validity period is required');
            }

            $nbErrors = count($errors);
            if ($nbErrors <= 0) {
                Configuration::updateValue('PS_GIFCARDS_VALIDITY', $gcValidity);
                Configuration::updateValue('PS_GIFCARDS_SHIPPING', $gcFreeShipping);
                Configuration::updateValue('PS_GIFCARDS_TAX', $gcTax);
                Configuration::updateValue('PS_GIFCARDS_PREFIX_CODE', $gcGiftcardPrefixCode);

                $this->updateGiftcardsTax($gcTax);

                $this->output .= $this->displayConfirmation($this->l('Success! Your configurations have been saved.'));
            } else {
                $this->output .= $this->displayError($errors);
            }
        }
    }

    public function hookDisplayAdminProductsExtra($params)
    {
        // Controllers
        $this->controller_url = $this->context->link->getAdminLink($this->controller_name);

        $giftcard = Giftcard::getGiftCardId((int) $params['id_product'], (int) $this->context->shop->id);
        if ($this->ps_version) { // if 1.7
            $id_product = $params['id_product'];
        } else {
            $id_product = Tools::getValue('id_product');
        }

        // assign var to smarty
        $this->context->smarty->assign([
            'module_name' => $this->name,
            'module_version' => $this->version,
            'isGiftcard' => $giftcard,
            'id_product' => $id_product,
            'controller_url' => $this->controller_url,
            'languages' => $this->context->controller->getLanguages(),
            'ps_version' => $this->ps_version,
        ]);

        return $this->display(dirname(__FILE__), '/views/templates/hook/displayAdminProductsExtra/productAdminExtra.tpl');
    }

    public function hookActionAdminControllerSetMedia()
    {
        if ($this->ps_version) { // if on ps 1.7
            $request_uri = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
            $parm1 = array_search('product', $request_uri);
            $parm2 = array_search('form', $request_uri);

            if (empty($parm1) && empty($parm2)) { // if on ps 1.7.5 product and form doens't exit
                $parm1 = array_search('catalog', $request_uri);
                $parm2 = array_search('products', $request_uri);
            }

            // Execute only on product page
            if (Tools::getValue('controller') == 'AdminProducts' && !empty($parm1) && !empty($parm2)) { // only load asset if on update product page or add product page
                Media::addJsDef([
                    'ps_version' => $this->ps_version,
                ]);

                $this->context->controller->addJS($this->js_path . 'vue.min.js');
                $this->context->controller->addCSS($this->css_path . 'productTab.css');
                $this->context->controller->addJS($this->js_path . 'productTab17.js');
            }
        } else { // if on ps 1.6
            $request_uri = explode('&', trim($_SERVER['REQUEST_URI'], '/'));
            $parm1 = array_search('updateproduct', $request_uri);
            $parm2 = array_search('addproduct', $request_uri);

            if (Tools::getValue('controller') == 'AdminProducts' && !empty($parm1) || !empty($parm2)) { // only load asset if on update product page or add product page
                Media::addJsDef([
                    'ps_version' => $this->ps_version,
                ]);

                $this->context->controller->addJquery();
                $this->context->controller->addJS($this->js_path . 'vue.min.js');
                $this->context->controller->addCSS($this->css_path . 'productTab.css');
                $this->context->controller->addJS($this->js_path . 'productTab16.js');
            }
        }
    }

    public function hookDisplayCustomerAccount()
    {
        $id_customer = $this->context->customer->id;

        $this->context->smarty->assign([
            'front_controller' => $this->front_controller,
            'id_customer' => $id_customer,
            'ps_version' => $this->ps_version,
        ]);

        return $this->display(dirname(__FILE__), '/views/templates/front/customerAccount.tpl');
    }

    /**
     * Register giftcard purchase
     */
    public function hookActionValidateOrder($params)
    {
        $id_lang = $this->context->language->id;
        // get cart details
        $cart = $params['cart'];
        $cart = new Cart($cart->id);

        // get customer details
        $customer = $params['customer'];

        $id_shop = $params['cart']->id_shop;

        // get order details
        $order = $params['order'];

        // get products in cart
        $products = $cart->getProducts();

        $currencySymbol = $this->context->currency->sign;

        // check if there is a giftcard in the cart
        foreach ($products as $product) {
            if ($this->checkIsGiftcard($product['id_product'])) { // if yes, save it
                for ($i = 0; $i < $product['cart_quantity']; ++$i) { // loop if there is more than one giftcard
                    $price = str_replace('.00', '', number_format(Product::getPriceStatic($product['id_product'], true), 2)) . ' ' . $currencySymbol;

                    $id_giftcard = Giftcard::getIdGiftcard($product['id_product']);
                    $id_giftcard = $id_giftcard['id_giftcard'];

                    $giftcard_history = new GiftcardHistory();
                    $giftcard_history->id_product = $product['id_product'];
                    $giftcard_history->id_giftcard = $id_giftcard;
                    $giftcard_history->amount = $price;
                    $giftcard_history->id_customer = $customer->id;
                    $giftcard_history->id_order = $order->id;
                    $giftcard_history->type = 0;
                    $giftcard_history->sendLater = 0;
                    $giftcard_history->id_state = 1;
                    $giftcard_history->id_shop = $this->context->shop->id;
                    $giftcard_history->save();

                    $this->sendMailConfirmation($product['id_product'], $customer->firstname, $customer->lastname, $customer->email, $id_lang, $id_shop);
                }
            }
        }
    }

    public function sendMailConfirmation($id_product, $firstname, $lastname, $email, $id_lang, $id_shop)
    {
        $shop = new Shop($id_shop);
        $shop = $shop->getAddress();

        $product = new Product((int) $id_product, false, $id_lang);

        $link_rewrite = $product->link_rewrite[$id_lang];
        $id_image = Product::getCover((int) $id_product);
        $id_image = $id_image['id_image'];

        if ($this->ps_version) { // if on ps 1.7
            $image_link = Context::getContext()->link->getImageLink($link_rewrite, $id_image, ImageType::getFormattedName('home'));
        } else { // if on ps 1.6
            $image_link = Context::getContext()->link->getImageLink($link_rewrite, $id_image, ImageType::getFormatedName('home'));
        }

        $data = [
            '{{blabla}}' => $this->l('Gift card'),
            '{{shopName}}' => Configuration::get('PS_SHOP_NAME'),
            '{{firstname}}' => $firstname,
            '{{lastname}}' => $lastname,
            '{{giftcard_name}}' => Product::getProductName($id_product, null, $id_lang),
            '{{product_image}}' => $image_link,
            '{{shop_addr1}}' => $shop->address1,
            '{{shop_addr2}}' => $shop->address2,
            '{{shop_zipcode}}' => $shop->postcode,
            '{{shop_city}}' => $shop->city,
            '{{shop_country}}' => $shop->address2,
            '{{shop_phone}}' => $shop->phone,
            '{{shop_fax}}' => $shop->address2,
            '{{shop_name}}' => Configuration::get('PS_SHOP_NAME'),
            '{{shop_logo}}' => Tools::getShopDomain(true) . __PS_BASE_URI__ . '/img/' . Configuration::get('PS_LOGO'),
            '{{site_url}}' => Configuration::get('PS_SHOP_DOMAIN'),
            '{{contact_url}}' => Context::getContext()->link->getPageLink('contact', true, $id_lang),
            '{{client_account_url}}' => Context::getContext()->link->getPageLink('my-account', true, $id_lang),
        ];

        unset($product, $shop);

        $dir = _PS_MODULE_DIR_ . 'psgiftcards/mails/';

        Mail::Send($id_lang, 'giftcard_confirmation', $this->l('Gift card confirmation'), $data, $email, null, null, null, null, null, $dir);
    }

    /**
     * hook called each time an order is updated
     */
    public function hookActionOrderStatusUpdate($params)
    {
        $id_order = $params['id_order']; // get id order which is currently updated
        $orderState = $params['newOrderStatus']; // get state
        $paymentStatus = $orderState->logable;
        $id_shop = $this->context->shop->id; // get shop id

        $giftcards = GiftcardHistory::getGiftcardHistoryByOrder($id_order, $id_shop); // get gift card

        foreach ($giftcards as $giftcard) {
            $id_cart_rule = GiftcardHistory::getCartRule($giftcard['id_giftcard_history'], $id_shop); // get cart rule associated to the gift card

            if ($id_cart_rule) { // if gift card has already a cart rule
                if ($paymentStatus === '0') { // disable the cart rule if the order state is set as not paid (logable)
                    $this->toggleCartRule($id_cart_rule, 0);
                } else {
                    $this->toggleCartRule($id_cart_rule, 1);
                }
            }

            if ($giftcard['id_state'] != '1' && $paymentStatus == '0') {
                GiftcardHistory::setStatus($giftcard['id_giftcard_history'], 1);
            } elseif ($giftcard['id_state'] == '1' && $paymentStatus == '1') {
                GiftcardHistory::setStatus($giftcard['id_giftcard_history'], 2);
            }
        }
    }

    /**
     * function that allow to disable or enable cart rule and also associated cart_rule (made by partial use)
     *
     * @param int $id_cart_rule
     * @param int $active
     */
    public function toggleCartRule($id_cart_rule, $active)
    {
        // get cart rule code
        $sql = 'SELECT ca.code FROM `' . _DB_PREFIX_ . 'cart_rule` ca WHERE ca.id_cart_rule = ' . (int) $id_cart_rule;
        $code = Db::getInstance()->getValue($sql);

        // update all cart rule by code
        $sql = 'UPDATE `' . _DB_PREFIX_ . 'cart_rule` ca SET ca.active = ' . (int) $active . " WHERE ca.code LIKE '" . $code . "%'";
        $update = Db::getInstance()->execute($sql);
    }

    public function hookActionProductDelete($params)
    {
        $id_product = $params['id_product'];

        $giftcard = Giftcard::getIdGiftcard($id_product);
        if (!empty($giftcard)) {
            $card = new Giftcard($giftcard['id_giftcard']);
            $card->delete();
        }
    }

    /**
     * generateCartRule
     *
     * @param mixed $id_giftcard
     *
     * @return mixed
     */
    public function generateCartRule($id_giftcard)
    {
        $giftcard = new GiftcardHistory($id_giftcard);
        $giftcard_cartRule = $giftcard->id_cartRule;

        if (!empty($giftcard_cartRule)) {
            $cartRule = new CartRule($giftcard->id_cartRule);

            $result = [
                'code' => $cartRule->code,
                'validity' => $cartRule->date_to,
            ];

            unset($cartRule, $giftcard);

            return $result;
        }

        // create cart in order to get price
        $cart = Cart::getCartByOrderId($giftcard->id_order);
        // create cart rule
        $cartRule = new CartRule();
        foreach (Language::getLanguages(true) as $lang) {
            $cartRule->name[$lang['id_lang']] = 'GIFTCARD_' . $giftcard->id;
        }
        $card_validity = '+' . Configuration::get('PS_GIFCARDS_VALIDITY') . ' months';
        //generate code
        $code = '';
        for ($z = 0; $z < 3; ++$z) {
            $code .= chr(rand(97, 122)) . rand(0, 100);
        }
        $code = Tools::strtoupper($code);

        $cartRule->partial_use = 1;
        $cartRule->code = Configuration::get('PS_GIFCARDS_PREFIX_CODE') . $code;
        $cartRule->active = true;
        $cartRule->date_from = date('Y-m-d');
        $cartRule->date_to = date('Y-m-d', strtotime($card_validity, strtotime(date('Y-m-d'))));
        $cartRule->reduction_amount = (int) str_replace('.0', '', Product::getPriceStatic($giftcard->id_product, true, null, 2, null, false, true, 1, false, null, $cart->id));
        $cartRule->reduction_tax = true;
        $cartRule->free_shipping = Configuration::get('PS_GIFCARDS_SHIPPING');

        $cartRule->save();
        $giftcard->id_cartRule = $cartRule->id;
        $giftcard->save();

        $result = [
            'code' => Configuration::get('PS_GIFCARDS_PREFIX_CODE') . $code,
            'validity' => date('d-m-Y', strtotime($card_validity, strtotime(date('Y-m-d')))),
        ];

        return $result;
    }

    public function executeCron()
    {
        $id_shop = (int) Tools::getValue('id_shop');
        $shop = $this->context->shop->getAddress();

        $total_mail_send = 0;

        $giftcards = GiftcardHistory::getGiftcards($id_shop);
        $giftcardsMailLang = GiftcardHistory::getGiftcardsMailsLang();
        $img_name = '';

        foreach ($giftcards as $giftcard) {
            if ($giftcard['id_state'] <= 3) {
                if (date('Y.m.d', strtotime($giftcard['send_date'])) == date('Y.m.d')) {
                    $cartRule = $this->generateCartRule($giftcard['id_giftcard_history']);
                    $customer = new Customer((int) $giftcard['id_customer']);
                    $subject = $text = $cta = $unsubscribe = false;
                    foreach ($giftcardsMailLang as $gcml) {
                        if ($gcml['id_lang'] == $customer->id_lang) {
                            $subject = $gcml['email_subject'];
                            $text = $gcml['email_content'];
                            $cta = $gcml['email_cta'];
                            $unsubscribe = $gcml['email_unsubscribe'];
                            $img_name = $gcml['email_discount'];
                        }
                    }

                    $text = str_replace('{buyer_name}', $giftcard['buyerName'], $text);
                    $text = str_replace('{recipient_name}', $giftcard['recipientName'], $text);
                    $text = str_replace('{buyer_message}', '<div class="gift-message-content" style="background:#f1f3f5;background-color:#f1f3f5;Margin:0px auto;max-width:600px;height:150px">' . $giftcard['text'] . '</div>', $text);
                    $text = str_replace('{discount_value}', $giftcard['amount'], $text);
                    $text = str_replace('{discount_validity}', $cartRule['validity'], $text);
                    $text = str_replace('{discount_code}', $cartRule['code'], $text);
                    $text = str_replace('{shop_link}', Configuration::get('PS_SHOP_DOMAIN'), $text);
                    $unsubscribe = str_replace('{$site_url}', Configuration::get('PS_SHOP_DOMAIN'), $unsubscribe);

                    $content = html_entity_decode($text);

                    $data = [
                        '{{color1}}' => '#' . Configuration::get('PS_GIFCARDS_PRIMARY_COLOR'),
                        '{{color2}}' => '#' . Configuration::get('PS_GIFCARDS_SECONDARY_COLOR'),
                        '{{last_message}}' => $unsubscribe,
                        '{{blabla}}' => $this->l('Gift card'),
                        '{{shopName}}' => Configuration::get('PS_SHOP_NAME'),
                        '{{recipientName}}' => $giftcard['recipientName'],
                        '{{buyerName}}' => $giftcard['buyerName'],
                        '{{text}}' => $giftcard['text'],
                        '{{validity}}' => $cartRule['validity'],
                        '{{cart_rule_code}}' => $cartRule['code'],
                        '{{price}}' => $giftcard['amount'],
                        '{{site_url}}' => Configuration::get('PS_SHOP_DOMAIN'),
                        '{{shop_addr1}}' => $shop->address1,
                        '{{shop_addr2}}' => $shop->address2,
                        '{{shop_zipcode}}' => $shop->postcode,
                        '{{shop_city}}' => $shop->city,
                        '{{shop_country}}' => $shop->address2,
                        '{{shop_phone}}' => $shop->phone,
                        '{{shop_fax}}' => $shop->address2,
                        '{{image_url}}' => _PS_BASE_URL_ . $this->img_path . 'giftcardMail.png',
                        '{shop_name}' => Configuration::get('PS_SHOP_NAME'),
                        '{{shop_logo}}' => _PS_BASE_URL_ . '/img/' . Configuration::get('PS_LOGO'),
                        '{{discount_code}}' => $cartRule['code'],
                        '{{content}}' => $content,
                        '{{CTA}}' => $cta,
                    ];
                    if ($img_name != 'To configure') {
                        $data['{{img_link}}'] = Tools::getShopDomainSsl(true) . '/modules/psgiftcards/views/img/DL/' . $img_name;
                    }

                    $dir = dirname(__FILE__) . '/mails/';

                    Mail::Send(
                        $customer->id_lang,
                        Configuration::get('PS_GIFCARDS_TEMPLATE'),
                        $this->l('You have received a gift !'),
                        $data,
                        $giftcard['recipientMail'],
                        null,
                        null,
                        null,
                        null,
                        null,
                        $dir
                    );

                    GiftcardHistory::setStatus($giftcard['id_giftcard_history'], 5);

                    $total_mail_send = $total_mail_send + 1;

                    unset($customer);
                }
            }
        }

        exit($total_mail_send . ' mail send');
    }

    public function updateGiftcardsTax($id_tax)
    {
        $giftcards = Giftcard::getGiftcards();

        foreach ($giftcards as $giftcard) {
            $product = new Product($giftcard['id_product']);
            $product->id_tax_rules_group = $id_tax;
            $product->save();
            unset($product);
        }
    }

    public function checkIsGiftcard($id_product)
    {
        $query = 'SELECT id_giftcard, id_product FROM ' . _DB_PREFIX_ . 'psgiftcards WHERE id_product = ' . (int) $id_product;
        $result = Db::getInstance()->getRow($query);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function createTax()
    {
        //install new tax with 0%
        $tax = new Tax();
        $tax->name = [];
        foreach (Language::getLanguages(true) as $lang) {
            $tax->name[$lang['id_lang']] = $this->l('Gift card 0%');
        }
        $tax->rate = 0.00;
        $tax->active = true;
        $tax->save();

        $taxRulesGroup = new TaxRulesGroup();
        $taxRulesGroup->name = $this->l('Gift card (0%)');
        $taxRulesGroup->active = true;
        $taxRulesGroup->save();

        $countries = Country::getCountries($this->context->language->id);
        $selected_countries = [];
        foreach ($countries as $country) {
            $selected_countries[] = (int) $country['id_country'];
        }

        foreach ($selected_countries as $id_country) {
            $taxRule = new TaxRule();
            $taxRule->id_tax = $tax->id;
            $taxRule->id_country = $id_country;
            $taxRule->id_tax_rules_group = $taxRulesGroup->id;
            $taxRule->save();
        }

        unset($tax, $taxRule, $taxRulesGroup);
    }

    /**
     * checkIfGiftcardIsUsed
     */
    public function checkIfGiftcardIsUsed()
    {
        $sql = 'SELECT `id_cart_rule` FROM `' . _DB_PREFIX_ . 'cart_rule`';
        $cartRules = Db::getInstance()->executeS($sql);

        $merged = [];
        $giftcards_cartRules = \GiftcardHistory::getCartRules();
        foreach ($giftcards_cartRules as $value) {
            array_push($merged, $value['id_cartRule']);
        }

        foreach ($cartRules as $cartRule) {
            if (in_array($cartRule['id_cart_rule'], $merged)) {
                $rule = new CartRule($cartRule['id_cart_rule']);
                $quantityLeft = $rule->quantity;
                if ($quantityLeft == 0) {
                    $sql = 'UPDATE `' . _DB_PREFIX_ . 'psgiftcards_history` SET `isUse`= 1 WHERE `id_cartRule` =' . $cartRule['id_cart_rule'];
                    $result = Db::getInstance()->execute($sql);
                }
                unset($rule);
            }
        }
    }

    /**
     * Hook executed after the payment
     * 1.6 : /controllers/front/OrderConfirmationController.php
     * 1.7 : /controllers/front/OrderConfirmationController.php
     *       /prestashop/controllers/front/OrderConfirmationController.php
     *
     * @param array $params payload returned from the hook
     */
    public function hookdisplayOrderConfirmation($params)
    {
        if (true === empty($params['order']->id)) {
            return false;
        }

        $order = new Order($params['order']->id);
        $products = $order->getProducts();

        if (false === $this->findGiftcard($products)) {
            return false;
        }

        $this->context->smarty->assign([
            'gfLink' => Context::getContext()->link->getModuleLink('psgiftcards', 'Giftcards'),
        ]);

        return $this->display(__FILE__, 'views/templates/front/hookorderconfirmation.tpl');
    }

    /**
     * Function that allow you to check if there is a giftcard in a product list
     *
     * @param array $products List of products
     *
     * @return bool return true if a giftcard is find
     */
    public function findGiftcard($products)
    {
        foreach ($products as $key => $product) {
            $giftcard = Giftcard::getGiftCardId((int) $product['product_id'], (int) $this->context->shop->id);
            if (true === is_array($giftcard)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return the custom content you will show on the template email
     *
     * @return array
     */
    protected function getCustomEmailContent()
    {
        return [
            'content' => [
                $this->l('Buyer Name') => '{buyer_name}',
                $this->l('Recipient Name') => '{recipient_name}',
                $this->l('Buyer Message') => '{buyer_message}',
                $this->l('Gift Card Value') => '{gift_card_value}',
                $this->l('Gift Card Validity') => '{gift_card_validity}',
                $this->l('Gift Card Code') => '{discount_code}',
                $this->l('shop Link') => '{shop_link}',
            ],
            // 'discount' => array(
            //     $this->l('Discount Code') => '{discount_code}',
            //     $this->l('Discount Value') => '{discount_value}',
            //     $this->l('Discount Validity') => '{discount_validity}',
            // ),
            'unsubscribe' => [
                $this->l('shop Link') => '{shop_link}',
            ],
        ];
    }

    /**
     * Manage the email folders by languages
     *
     * @return bool
     */
    private function manageEmailFolders()
    {
        $languages = Language::getLanguages(true);

        $createEmailFolders = new GiftCardCreateEmailByLang();

        $canCreateNewFolders = $createEmailFolders->checkIfWeCanCreateFolders($this->mails_path);

        if ($canCreateNewFolders) {
            $createEmailFolders->intializeEmailTemplatesByLanguage($this->mails_path, $languages);
        }

        return $canCreateNewFolders;
    }
}
