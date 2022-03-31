<?php
/**
 * 2019 ZH Media
 *
 * NOTICE OF LICENSE
 *
 * Unauthorized copying of this file, via any medium, is strictly prohibited.
 * Do not resell or redistribute this file, either fully or partially.
 * Do not remove this comment containing author information and copyright.
 *
 * @author    Zack Hussain <me@zackhussain.ca>
 * @copyright 2019 ZH Media - All Rights Reserved
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(dirname(__FILE__) . '/autoload.php');

class CanadaPostLabels extends CarrierModule
{
    const PREFIX     = 'CPL_';
    const PREFIX_LOW = 'cpl_';

    const ACCOUNT_TYPE_REGULAR  = 1;
    const ACCOUNT_TYPE_CONTRACT = 2;

    const SPLIT_TYPE_OFF     = 1;
    const SPLIT_TYPE_SIMPLE  = 2;
    const SPLIT_TYPE_COMPLEX = 3;

    public $_prefix;
    public $_prefix_low;

    public $id_addons = '8311';

    public $objectPresenter;

    // Variable that the cart loads with carrier ID
    public $id_carrier;

    /**
     * @var string Token to secure AJAX requests
     */
    private $secure_key;

    private $_postErrors = array();
    private $_postWarnings = array();

    protected $tabs = array(
        array(
            'name' => 'Canada Post',
            'class_name' => 'AdminCanadaPostLabels',
            'parent' => 'SELL',
            'icon' => 'local_post_office',
        ),
        array(
            'name' => 'Create Bulk Order Labels',
            'class_name' => 'AdminCanadaPostLabelsCreateBulkLabels',
            'parent' => 'AdminCanadaPostLabels',
            'icon' => false,
        ),
        array(
            'name' => 'Create Label & Return',
            'class_name' => 'AdminCanadaPostLabelsCreateLabel',
            'parent' => 'AdminCanadaPostLabels',
            'icon' => false,
        ),
        array(
            'name' => 'View Shipments',
            'class_name' => 'AdminCanadaPostLabelsViewShipments',
            'parent' => 'AdminCanadaPostLabels',
            'icon' => false,
        ),
        array(
            'name' => 'View Return Shipments',
            'class_name' => 'AdminCanadaPostLabelsViewReturnShipments',
            'parent' => 'AdminCanadaPostLabels',
            'icon' => false,
        ),
        array(
            'name' => 'View Batches',
            'class_name' => 'AdminCanadaPostLabelsViewBatches',
            'parent' => 'AdminCanadaPostLabels',
            'icon' => false,
        ),
        array(
            'name' => 'View Manifests',
            'class_name' => 'AdminCanadaPostLabelsViewManifests',
            'parent' => 'AdminCanadaPostLabels',
            'icon' => false,
        ),
        array(
            'name' => 'End of Day / Transmit',
            'class_name' => 'AdminCanadaPostLabelsTransmitShipments',
            'parent' => 'AdminCanadaPostLabels',
            'icon' => false,
        ),
        array(
            'name' => 'Track Parcel',
            'class_name' => 'AdminCanadaPostLabelsTracking',
            'parent' => 'AdminCanadaPostLabels',
            'icon' => false,
        ),
    );

    // Hooks registered by the module
    public $hooks = array(
        'displayAdminOrder',
        'actionOrderStatusUpdate',
        'displayBackOfficeHeader',
        'displayHeader',
        'actionCarrierUpdate',
        'actionCartSave',
        'displayBeforeCarrier',
        'displayCarrierExtraContent',
        'actionAdminStatusAfter',
        'actionValidateStepComplete',
        'actionCarrierProcess',
        'displayAfterCarrier',
        'displayOrderDetail',
        'actionCronJob',
        'displayProductAdditionalInfo',
        'displayProductButtons',
        'displayShoppingCart',
    );

    // Objects created by the module with their own tables
    private $models = array(
        'Address',
        'Batch',
        'Box',
        'Cache',
        'CacheRate',
        'CarrierMapping',
        'Group',
        'Method',
        'OrderError',
        'OrderLabelSettings',
        'OrderLabelAddress',
        'OrderLabelCustoms',
        'OrderLabelCustomsProduct',
        'OrderLabelOptions',
        'OrderLabelParcel',
        'OrderLabelPreferences',
        'OrderLabelReturn',
        'Shipment',
        'RateDiscount',
        'ReturnShipment',
        'Service',
    );

    private $adminControllers = array(
        'AdminCanadaPostLabelsCreateLabel',
        'AdminCanadaPostLabelsCreateBulkLabels',
        'AdminCanadaPostLabelsViewShipments',
        'AdminCanadaPostLabelsViewReturnShipments',
        'AdminCanadaPostLabelsViewBatches',
        'AdminCanadaPostLabelsViewManifests',
        'AdminCanadaPostLabelsTransmitShipments',
        'AdminCanadaPostLabelsTracking',
    );

    private $contractControllers = array(
        'AdminCanadaPostLabelsViewManifests',
        'AdminCanadaPostLabelsTransmitShipments',
    );

    private $namespace = 'CanadaPostPs\\';

    private $labelsShippingPathUri;
    private $labelsShippingPathLocal;

    private $labelsReturnsPathUri;
    private $labelsReturnsPathLocal;

    private $manifestsPathUri;
    private $manifestsPathLocal;

    private $batchPathUri;
    private $batchPathLocal;

    public $modals = array();

    public $logo;

    /* @var \Symfony\Component\HttpFoundation\Session\Flash\FlashBag */
    public $flashBag;

    /* @var \Symfony\Component\HttpFoundation\RequestStack */
    public $requestStack;

    protected static $clearCacheOnCartUpdate = true;

    public function __construct()
    {
        $this->name                   = 'canadapostlabels';
        $this->version                = '4.0.8';
        $this->author                 = 'ZH Media';
        $this->tab                    = 'shipping_logistics';
        $this->bootstrap              = true;
        $this->limited_countries      = array('ca');
        $this->module_key             = 'c60dc9a7ef0f1cc3ffd51c37c5856dd2';
        $this->displayName            = $this->l('Canada Post: Rates, Bulk Labels, Returns, Tracking, Estimator');
        $this->description            = $this->l('Offer real-time rates to customers, create bulk order labels in one click, allow customers to track their orders, and provide shipping quotes on the product and cart pages.');
        $this->ps_versions_compliancy = array('min' => '1.6.0.0', 'max' => _PS_VERSION_);
        $this->secure_key = Tools::encrypt($this->name);

        if (version_compare(_PS_VERSION_, '1.7.0.0') >= 0) {
            $this->objectPresenter = new PrestaShop\PrestaShop\Adapter\ObjectPresenter();
        }

        if (self::getConfig('SAVE_INFO') == false) {
            $this->confirmUninstall = $this->l('Are you sure you want to remove all your settings and data? Your carriers, boxes, addresses, shipments, manifests, carrier mappings, rate discounts, will all be deleted. Enable Save Info on Uninstall in the module to keep this data when uninstalling/reinstalling, or perform a database backup first.');
        }

        $this->_prefix     = self::PREFIX;
        $this->_prefix_low = self::PREFIX_LOW;

        parent::__construct();

        Shop::addTableAssociation(\CanadaPostPs\RateDiscount::$definition['table'], array('type' => 'shop'));

        $this->labelsShippingPathUri   = $this->getPathUri() . '/pdf/shipping/';
        $this->labelsShippingPathLocal = _PS_MODULE_DIR_ . $this->name . '/pdf/shipping/';

        $this->labelsReturnsPathUri   = $this->getPathUri() . '/pdf/returns/';
        $this->labelsReturnsPathLocal = _PS_MODULE_DIR_ . $this->name . '/pdf/returns/';

        $this->manifestsPathUri   = $this->getPathUri() . '/pdf/manifests/';
        $this->manifestsPathLocal = _PS_MODULE_DIR_ . $this->name . '/pdf/manifests/';

        $this->batchPathUri   = $this->getPathUri() . '/pdf/batch/';
        $this->batchPathLocal = _PS_MODULE_DIR_ . $this->name . '/pdf/batch/';

        $heartIcon = \CanadaPostPs\Icon::getIconHtml('favorite');
        $this->context->smarty->assign(array('zhmediaHeartIcon' => $heartIcon));
        $this->logo = $this->context->smarty->fetch(sprintf(_PS_MODULE_DIR_.'%s/views/templates/admin/logo.tpl', $this->name));

        // Check if cURL is enabled
        if (!is_callable('curl_exec')) {
            $this->warning = $this->l('cURL extension must be enabled on your server to use this module.');
        }
    }

    public function install()
    {
        // Update default config values if previous values aren't saved
        if (!self::getConfig('SAVE_INFO')) {
            if (
                !self::updateConfig('LABEL_DELAY', 1000000) ||
                !self::updateConfig('LABELS_ORDER_BY', 'id_order') ||
                !self::updateConfig('LABELS_ORDER_WAY', 'ASC') ||
                !self::updateConfig('non_delivery_options', 'RTS') ||
                !self::updateConfig('ORDER_ID_REFERENCE', '1') ||
                !self::updateConfig('PICKUP', '1') ||
                !self::updateConfig('MODE', '1') ||
                !self::updateConfig('DELAY', '0') ||
                !self::updateConfig('MAX_BOXES', '1') ||
                !self::updateConfig('SPLIT_TYPE', '1') ||
                !self::updateConfig('reason-for-export', 'SOG') ||
                !self::updateConfig('CARRIER_LOGO_FILE', 'logo_40.png') ||
                !self::updateConfig('CARRIER_LOGO_FILE', 'logo_40.png') ||
                !self::updateConfig('TRACK_ORDER_STATUSES', Configuration::get('PS_OS_SHIPPING')) ||
                !self::updateConfig('DELIVERED_ORDER_STATUS', Configuration::get('PS_OS_DELIVERED'))
            ) {
                return false;
            }
        }
        if (!parent::install() ||
            !self::updateConfig('TOKEN_REQUEST', 'token_request') ||
            !Configuration::updateGlobalValue(self::PREFIX . 'DOWNLOAD_ID', '2300') ||
            !self::updateConfig('VS',true) ||
            !self::updateConfig('VE',true) ||
            !self::updateConfig('TOKEN_REQUEST', 'token_request_ps') ||
            !$this->registerHooks() ||
            !$this->installTabs()
        ) {
            return false;
        }

        if (!self::getConfig('ORDER_STATUS')) {
            self::updateConfig('ORDER_STATUS', _PS_OS_SHIPPING_);
        }

        // Delay DB table install on older PS versions that have namespace bug
        // Install the tables later in getContent after the Hook.php override is installed
        if (version_compare(_PS_VERSION_, '1.7.1.0') >= 0) {
            $this->installTables();
        }

        return true;
    }


    public function checkForUpdate()
    {
        if ($update = CanadaPostPs\Tools::update(self::getConfig('DOWNLOAD_ID'), $this->version)) {
            return $update;
        }

        return false;
    }

    public function uninstall()
    {
        // If we don't save module data, delete everything
        if (!self::getConfig('SAVE_INFO')) {
            if (!$this->uninstallCarriers() ||
                !$this->deleteConfig() ||
                !$this->deleteDbTables()) {
                return false;
            }
        }

        if (!$this->uninstallTabs() ||
            !Configuration::deleteByName(self::PREFIX . 'TOKEN_REQUEST') ||
            !parent::uninstall()) {
            return false;
        }

        return true;
    }

    /**
     * Add override for PS versions below 1.7.1 to fix namespace bug
     * Commit: 8eaf5a3677bc40c44959bfd2ac06b89039c1c1af
     * https://github.com/PrestaShop/PrestaShop/commit/8eaf5a3677bc40c44959bfd2ac06b89039c1c1af
     * */
    public function getOverrides()
    {
        if (version_compare(_PS_VERSION_, '1.7.1.0') < 0) {
            return parent::getOverrides();
        } else {
            return null;
        }
    }

    /*
     * Enable any module carriers that were previously active
     * */
    public function enable($force_all = false)
    {
        $tabs = Tab::getCollectionFromModule($this->name)->getResults();
        if (!empty($tabs)) {
            Tab::enablingForModule($this->name);
        }

        $this->toggleCarriers('enable');

        return parent::enable($force_all);
    }

    /*
     * Disable any carriers created by the module
     * */
    public function disable($force_all = false)
    {
        $tabs = Tab::getCollectionFromModule($this->name)->getResults();
        if (!empty($tabs)) {
            Tab::disablingForModule($this->name);
        }

        $this->toggleCarriers('disable');


        return parent::disable($force_all);
    }

    public function toggleCarriers($action)
    {
        if (is_null($action) && $action != 'enable' && $action != 'disable') {
            return;
        }

        if (CanadaPostPs\Tools::tableExists(CanadaPostPs\Method::$definition['table'])) {
            $methods = CanadaPostPs\Method::getMethods('id_carrier > 0');
            foreach ($methods as $method) {
                $carrier = new Carrier($method['id_carrier']);
                if (Validate::isLoadedObject($carrier)) {
                    if ($action == 'enable' && $method['active'] == 1) {
                        $carrier->active = 1;
                    } elseif ($action == 'disable') {
                        $carrier->active = 0;
                    }
                    $carrier->update();
                }
            }
        }
    }

    /*
     * Register module hooks
     * @return bool
     * */
    public function registerHooks()
    {
        foreach ($this->hooks as $hook) {
            if (!$this->registerHook($hook)) {
                return false;
            }
        }

        return true;
    }

    /*
     * Install module tables and default data
     * @return bool
     * */
    public function installTables()
    {
        require dirname(__FILE__) . '/sql/sql_install.php';

        if (
            !CanadaPostPs\Tools::installMethods() ||
            !CanadaPostPs\Tools::installBox() ||
            !CanadaPostPs\Tools::installGroup() ||
            !CanadaPostPs\Tools::installAddress()) {
            return false;
        }

        return true;
    }


    /**
     * Delete module configurations.
     * Replaces dots with _ from shipping method names. Removes brackets from array names.
     * @return boolean
     */
    private function deleteConfig()
    {
        if ($values = $this->getConfigFieldsValues()) {
            foreach ($values as $k => $v) {
                if (!Configuration::deleteByName(str_replace(array('.', '[', ']'), array('_', '', ''), $k))) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Delete module database tables.
     * @return boolean
     */
    private function deleteDbTables()
    {
        // Delete tables
        foreach ($this->models as $className) {
            $obj = $this->namespace . $className;
            if (!Db::getInstance()->Execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . $obj::$definition['table'] . '`')) {
                return false;
            }
        }

        return true;
    }

    /* Install tab in back office */
    public function installTabs()
    {
        $tabs = Tab::getCollectionFromModule($this->name)->getResults();
        if (!empty($tabs)) {
            return true;
        }

        foreach ($this->tabs as $tabArr) {
            $tab             = new Tab();
            $tab->class_name = $tabArr['class_name'];
            $tab->module     = $this->name;
            $tab->id_parent  = $tabArr['parent'] ? Tab::getIdFromClassName($tabArr['parent']) : -1;
            $tab->active     = 1;
            $tab->icon     = $tabArr['icon'];

            foreach (Language::getLanguages(false) as $lang) {
                $tab->name[(int)$lang['id_lang']] = $tabArr['name'];
            }

            if (!$tab->add()) {
                return false;
            }
        }
        return true;
    }

    // Uninstall Tabs
    public function uninstallTabs()
    {
        $tabs = Tab::getCollectionFromModule($this->name)->getResults();
        if (!empty($tabs)) {
            foreach ($tabs as $tab) {
                if (!$tab->delete()) {
                    return false;
                }
            }
        }

        return true;
    }

    private function enableContractTabs()
    {
        $tabs = Tab::getCollectionFromModule($this->name)->getResults();
        /* @var $Tab Tab */
        foreach ($tabs as $Tab) {
            if (in_array($Tab->class_name, $this->contractControllers)) {
                $ContractTab = new Tab($Tab->id);
                if (!empty(($ContractTab->name))) {
                    $ContractTab->active = 1;
                    $ContractTab->save();
                }
            }
        }
    }

    private function disableContractTabs()
    {
        $tabs = Tab::getCollectionFromModule($this->name)->getResults();
        /* @var $Tab Tab */
        foreach ($tabs as $Tab) {
            if (in_array($Tab->class_name, $this->contractControllers)) {
                $ContractTab = new Tab($Tab->id);
                if (!empty(($ContractTab->name))) {
                    $ContractTab->active = 0;
                    $ContractTab->save();
                }
            }
        }
    }

    /*
     * Get configuration variable with prepended module prefix
     *
     * @param string $var
     * @return string
     * */
    public static function getConfig($var)
    {
        return Configuration::get(self::PREFIX . $var);
    }

    /*
     * Update configuration variable with prepended module prefix
     *
     * @param string $var
     * @param string $newValue
     * @return string
     * */
    public static function updateConfig($var, $newValue)
    {
        return Configuration::updateValue(self::PREFIX . $var, $newValue);
    }

    /**
     * Log messages in error_log.txt and PrestaShopLogger if enabled in config
     *
     * @param string $msg
     * */
    public function log($msg)
    {
        if (self::getConfig('LOGGING')) {
            $msg = sprintf("Canada Post: %s \r\n", $msg);
            file_put_contents(
                dirname(__FILE__) . '/error_log.txt',
                '['.date('Y-m-d H:i:s') . '] ' . $msg,
                FILE_APPEND
            );
            PrestaShopLogger::addLog($msg);
        }
    }

    /* Check if a Canada Post account is connected */
    public function isConnected()
    {
        if (self::getConfig('PROD_API_PASS') &&
            self::getConfig('PROD_API_USER') &&
            self::getConfig('CUSTOMER_NUMBER')) {
            return true;
        }

        return false;
    }

    public function isVerified()
    {
        return (self::getConfig('VS') &&
                self::getConfig('VE')
        );
    }

    public function isContract()
    {
        return self::getConfig('ACCOUNT_TYPE') == self::ACCOUNT_TYPE_CONTRACT;
    }

    /*
     * @return array Object Models
     * */
    public function getModels()
    {
        return $this->models;
    }

    /*
     * @return string
     * */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /*
     * @return string
     * */
    public function getLabelsShippingPathUri()
    {
        return $this->labelsShippingPathUri;
    }

    /*
     * @return string
     * */
    public function getLabelsShippingPathLocal()
    {
        return $this->labelsShippingPathLocal;
    }

    /*
     * @return string
     * */
    public function getLabelsReturnsPathUri()
    {
        return $this->labelsReturnsPathUri;
    }

    /*
     * @return string
     * */
    public function getLabelsReturnsPathLocal()
    {
        return $this->labelsReturnsPathLocal;
    }

    /*
     * @return string
     * */
    public function getManifestsPathUri()
    {
        return $this->manifestsPathUri;
    }

    /*
     * @return string
     * */
    public function getManifestsPathLocal()
    {
        return $this->manifestsPathLocal;
    }

    /*
     * @return string
     * */
    public function getBatchPathUri()
    {
        return $this->batchPathUri;
    }

    /*
     * @return string
     * */
    public function getBatchPathLocal()
    {
        return $this->batchPathLocal;
    }

    /*
     * @return array
     * */
    public function getPostErrors()
    {
        return $this->_postErrors;
    }

    /**
     * @return string
     */
    public function getSecureKey()
    {
        return $this->secure_key;
    }

    /**
     * Get token from Canada Post Platform Provider (zhmedia.ca). It is used to authenticate this module
     */
    public function getToken()
    {
        // store token retrieval time so we can delete it after it expires in 30 minutes
        if ($this->context->cookie->__isset('canadapost_token_time')) {
            $start_date = new DateTime($this->context->cookie->canadapost_token_time);
            // Get time difference since token date
            $since_start = $start_date->diff(new DateTime());

            // if token is expired, delete it and get a new token
            if ((int)$since_start->i >= 30) {
                $this->context->cookie->__unset('canadapost_token_time');
                $this->context->cookie->__unset('canadapost_token');
            }
        }

        if (!$this->context->cookie->__isset('canadapost_token') || !self::getConfig('PLATFORM_ID')) {
            $API = new \CanadaPostPs\API();
            try {
                $TokenType = $API->getToken();
                if ($TokenType instanceof \CanadaPostWs\Type\Platform\TokenType) {
                    self::updateConfig('PLATFORM_ID', $TokenType->getPlatformId());
                    $this->context->cookie->canadapost_token      = $TokenType->getTokenId();
                    $this->context->cookie->canadapost_token_time = date('Y-m-d H:i:s');
                } elseif ($TokenType instanceof \CanadaPostWs\Type\Messages\MessagesType) {

                    /* @var $Message \CanadaPostWs\Type\Messages\MessageType */
                    foreach ($TokenType->getMessages() as $Message) {
                        $this->_postErrors[] = sprintf(
                            $this->l(CanadaPostPs\Tools::$error_messages['TOKEN_ERROR']),
                            $Message->getDescription()
                        );
                    }
                } else {
                    $this->_postErrors[] = $this->l('Error retrieving Canada Post Platform token. Please contact the module developer to resolve this issue.');
                }
            } catch (PrestaShopException $e) {
                $this->log($e->getMessage());
                $this->_postErrors[] = $e->getMessage();
            }
        }
    }

    public function processRegistration($status)
    {
        $configLink = AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules');

        switch ($status) {
            case "SUCCESS":
                // Store merchant API keys and merchant information
                try {
                    $API = new \CanadaPostPs\API();
                    /* @var $MerchantInfoType \CanadaPostWs\Type\Platform\MerchantInfoType */
                    $MerchantInfoType = $API->getMerchantInformation($this->context->cookie->canadapost_token);
                    if ($MerchantInfoType instanceof \CanadaPostWs\Type\Platform\MerchantInfoType) {
                        $contractNumber = $MerchantInfoType->getContractNumber();
                        if (!empty($contractNumber)) {
                            self::updateConfig('CONTRACT', $MerchantInfoType->getContractNumber());
                            self::updateConfig('ACCOUNT_TYPE', self::ACCOUNT_TYPE_CONTRACT);
                            self::updateConfig('output-format', '4x6');
                            self::updateConfig('intended-method-of-payment', 'Account');
                            $this->enableContractTabs();
                        } else {
                            self::updateConfig('ACCOUNT_TYPE', self::ACCOUNT_TYPE_REGULAR);
                            self::updateConfig('output-format', '8.5x11');
                            self::updateConfig('intended-method-of-payment', 'CreditCard');
                            $this->disableContractTabs();
                        }
                        self::updateConfig('CUSTOMER_NUMBER', $MerchantInfoType->getCustomerNumber());
                        self::updateConfig('PROD_API_USER', $MerchantInfoType->getMerchantUsername());
                        self::updateConfig('PROD_API_PASS', $MerchantInfoType->getMerchantPassword());
                        self::updateConfig('CREDIT', $MerchantInfoType->getHasDefaultCreditCard());

                        $this->context->cookie->__unset('canadapost_token');
                        $this->context->cookie->__unset('canadapost_token_time');

                        Tools::redirectAdmin($configLink);
                    } elseif ($MerchantInfoType instanceof \CanadaPostWs\Type\Messages\MessagesType) {

                        /* @var $Message \CanadaPostWs\Type\Messages\MessageType */
                        foreach ($MerchantInfoType->getMessages() as $Message) {
                            return $this->displayError(sprintf(
                                $this->l(CanadaPostPs\Tools::$error_messages['MERCHANT_INFO_ERROR']),
                                $Message->getDescription()
                            ));
                        }
                    } else {
                        return $this->displayError($this->l('Error retrieving Canada Post merchant information from Platform provider. Please contact the module developer to resolve this issue.'));
                    }
                } catch (PrestaShopException $e) {
                    return $this->displayError($e->getMessage());
                }
                break;
            case "CANCELLED":
                return $this->displayError($this->l(\CanadaPostPs\Tools::$error_messages['TOKEN_CANCEL']));
                break;
            case "SERVICE_UNAVAILABLE":
                return $this->displayError($this->l(\CanadaPostPs\Tools::$error_messages['TOKEN_SERVICE_UNAVAILABLE']));
                break;
            default:
                $this->context->cookie->__unset('canadapost_token');
                $this->context->cookie->__unset('canadapost_token_time');
                return $this->displayError($this->l(\CanadaPostPs\Tools::$error_messages['TOKEN_FAILURE']));
                break;
        }
    }

    /* Show config form */
    public function getContent()
    {
        // Delay DB table install on older PS versions that have namespace bug
        // Install the tables later in getContent after the Hook.php override is installed
        if (version_compare(_PS_VERSION_, '1.7.1.0') < 0 &&
            !\CanadaPostPs\Tools::tableExists(\CanadaPostPs\Box::$definition['table'])
        ) {
            $this->installTables();
        }

        $output = '';

        // Get Platform Token
        if (!$this->isConnected() && $this->isVerified()) {
            $this->getToken();

            // Canada Post platform authorization response
            if (Tools::getIsset('registration-status')) {
                $output .= $this->processRegistration(Tools::getValue('registration-status'));
            }
        }

        if ($update = $this->checkForUpdate()) {
            $output .= $this->displayWarning($update);
        }

        $this->_postValidation();
        if (!count($this->_postErrors)) {
            $output .= $this->_postProcess();
        } else {
            foreach ($this->_postErrors as $err) {
                $output .= $this->displayError($err);
            }
        }

        if (count($this->_postWarnings)) {
            foreach ($this->_postWarnings as $postWarning) {
                $output .= $this->displayWarning($postWarning);
            }
        }

        // Init forms object
        $forms = new CanadaPostPs\Forms();

        // Render update/edit Object forms
        foreach ($this->models as $className) {
            $obj                    = $this->namespace . $className;
            $renderObjectFormMethod = sprintf('render%sForm', $className);
            if (Tools::isSubmit('update' . _DB_PREFIX_ . $obj::$definition['table']) ||
                Tools::isSubmit('add' . _DB_PREFIX_ . $obj::$definition['table'])) {
                return $forms->$renderObjectFormMethod();
            }
        }

        $configForms = array();

        // Render config forms dynamically based on config tabs
        foreach ($this->getConfigTabs() as $name => $tab) {
            $renderObjectFormMethod = sprintf('render%s%s', \Tools::ucfirst($name), \Tools::ucfirst($tab['type']));
            $configForms[$name]     = $forms->$renderObjectFormMethod();
        }

        $this->context->smarty->assign(array(
            'output'         => $output,
            'connected'      => $this->isConnected(),
            'readmeUrl'      => _MODULE_DIR_ . $this->name . '/Readme.html',
            'faqUrl'         => _MODULE_DIR_ . $this->name . '/Readme.html#troubleshooting',
            'contactUrl'     => 'https://addons.prestashop.com/en/write-to-developper?id_product=' . $this->id_addons,
            'rateUrl'        => 'https://addons.prestashop.com/en/ratings.php',
            'module_version' => $this->version,
            'config_tabs'    => $this->getConfigTabs(),
            'config_forms'   => $configForms,
        ));

        return $this->display(__FILE__, 'content.tpl');
    }

    private function _displayInfos()
    {
        $this->context->smarty->assign(array(
            'pl' => self::PREFIX_LOW,
        ));

        return $this->display(__FILE__, 'infos.tpl');
    }

    private function _postValidation()
    {
        // Validate the store's units of measurement
        foreach (CanadaPostPs\Tools::$units as $unit => $names) {
            try {
                \CanadaPostPs\Tools::getUnit(Configuration::get('PS_' . $unit . '_UNIT'));
            } catch (PrestaShopException $e) {
                $this->_postWarnings[] = sprintf(
                    $this->l(CanadaPostPs\Tools::$error_messages['INVALID_UNITS']),
                    $unit,
                    Configuration::get('PS_' . $unit . '_UNIT'),
                    implode(array_keys(CanadaPostPs\Tools::$units[$unit]), ', ')
                );
            }
        }

        // Check currency settings
        if (!Currency::getIdByIsoCode('CAD')) {
            $this->_postWarnings[] = $this->l(CanadaPostPs\Tools::$error_messages['MISSING_CURRENCY']);
        }

        $boxes = CanadaPostPs\Box::getBoxes();
        if (empty($boxes)) {
            $this->_postWarnings[] = $this->l(CanadaPostPs\Tools::$error_messages['MISSING_BOX']);
        }

        $groups = CanadaPostPs\Group::getGroups();
        if (empty($groups)) {
            $this->_postWarnings[] = $this->l(CanadaPostPs\Tools::$error_messages['MISSING_GROUP']);
        }

        $addresses = CanadaPostPs\Address::getAddresses();
        if (empty($addresses)) {
            $this->_postWarnings[] = $this->l(CanadaPostPs\Tools::$error_messages['MISSING_ADDRESS']);
        }

        if (Tools::isSubmit('submitVerify')) {
            if (!Tools::getValue(self::PREFIX . 'VE')) {
                $this->_postErrors[] = sprintf(
                    $this->l(CanadaPostPs\Tools::$error_messages['REQUIRED_FIELD']),
                    $this->l('email')
                );
            }
            if (!Tools::getValue(self::PREFIX . 'VS')) {
                $this->_postErrors[] = sprintf(
                    $this->l(CanadaPostPs\Tools::$error_messages['REQUIRED_FIELD']),
                    $this->l('serial')
                );
            }
        }

        if (Tools::isSubmit('submitOrdertracking')) {
            if (in_array(
                Tools::getValue(self::PREFIX.'DELIVERED_ORDER_STATUS'),
                Tools::getValue(self::PREFIX.'TRACK_ORDER_STATUSES', array())
            )) {
                $this->_postErrors[] = $this->l(CanadaPostPs\Tools::$error_messages['CONFLICTING_STATUSES']);
            }
        }

        /*
         * Validate Object fields
         * We iterate through each of the module's Object types and dynamically instantiate them
         * */
        foreach ($this->models as $className) {
            $obj = $this->namespace . $className;

            if (Tools::isSubmit('save' . _DB_PREFIX_ . $obj::$definition['table'])) {
                foreach ($obj::$definition['fields'] as $field => $value) {
                    $validate_method = $value['validate'];
                    if (array_key_exists('required', $value) && $value['required'] &&
                        (!Tools::getIsset($field) || Tools::isEmpty(Tools::getValue($field)))) {
                        $this->_postErrors[] = sprintf(
                            $this->l(CanadaPostPs\Tools::$error_messages['REQUIRED_FIELD']),
                            $className . ' ' . $field
                        );
                    }
                    if (Tools::getIsset($field) &&
                        !Tools::isEmpty(Tools::getValue($field)) &&
                        !Validate::$validate_method(Tools::getValue($field))) {
                        $Class               = new $obj();
                        $this->_postErrors[] = $Class->validateField(
                            $field,
                            Tools::getValue($field),
                            null,
                            array(),
                            true
                        );
                    }
                }
            }
        }

        if (Tools::isSubmit('delete' . _DB_PREFIX_ . CanadaPostPs\Address::$definition['table']) &&
            Tools::getValue('id_address') == CanadaPostPs\Address::getOriginAddress()->id) {
            $this->_postErrors[] = $this->l(CanadaPostPs\Tools::$error_messages['CANNOT_DELETE_ORIGIN']);
        }

        if (Tools::isSubmit('save' . _DB_PREFIX_ . CanadaPostPs\Address::$definition['table'])) {
            if (Tools::getValue('id_address') == CanadaPostPs\Address::getOriginAddress()->id && Tools::getValue('origin') == 0) {
                $this->_postErrors[] = sprintf(
                    $this->l(CanadaPostPs\Tools::$error_messages['REQUIRED_FIELD']),
                    $this->l('origin address')
                );
            }
        }

        if (Tools::isSubmit('delete' . _DB_PREFIX_ . CanadaPostPs\Group::$definition['table'])) {
            $groups = \CanadaPostPs\Group::getGroups();
            if (count($groups) == 1) {
                $this->_postErrors[] = $this->l(CanadaPostPs\Tools::$error_messages['CANNOT_DELETE_GROUP']);
            }
        }

        if (Tools::isSubmit('save' . _DB_PREFIX_ . CanadaPostPs\RateDiscount::$definition['table']) ||
            Tools::isSubmit('update' . _DB_PREFIX_ . CanadaPostPs\RateDiscount::$definition['table'])) {

            // if discount type is amt/pct and discount value is missing
            if ((Tools::getValue('apply_discount') == 'percent' || Tools::getValue('apply_discount') == 'amount') &&
                (Tools::isEmpty(Tools::getValue('discount_value')) || !Tools::getIsset('discount_value'))) {
                $this->_postErrors[] = sprintf(
                    $this->l(CanadaPostPs\Tools::$error_messages['REQUIRED_CONDITIONAL_FIELD']),
                    $this->l('Discount Value'),
                    $this->l('Discount Type')
                );
            }

            $table = \CanadaPostPs\RateDiscount::$definition['table'];

            // Check if discount exists already for Method and Shop
            if (Shop::isFeatureActive() && Tools::isSubmit('checkBoxShopAsso_'.$table)) {
                $id_shops = implode(', ', Tools::getValue('checkBoxShopAsso_'.$table));
                $identifier = \CanadaPostPs\RateDiscount::$definition['primary'];

                $DbQuery = new DbQuery();
                $DbQuery->select('*');
                $DbQuery->from($table, 'rd');
                $DbQuery->leftJoin($table.'_shop', 's', 's.`'.$identifier.'` = rd.`'.$identifier.'`');
                $DbQuery->where('`id_method` = '.Tools::getValue('id_method'));
                $DbQuery->where('s.`id_shop` IN ('.$id_shops.')');

                // If currently updating a discount, exclude it from the query
                if (!Tools::isEmpty(Tools::getValue($identifier))) {
                    $DbQuery->where('rd.`'.$identifier.'` != '.Tools::getValue($identifier));
                }

                $query = Db::getInstance()->executeS($DbQuery->build());
                if (!empty($query)) {
                    $this->_postErrors[] = $this->l(CanadaPostPs\Tools::$error_messages['CONFLICTING_DISCOUNTS']);
                }
            }
        }
    }

    /* Process settings submission */
    private function _postProcess()
    {
        $configLink = AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules');

        // Check API and configuration settings via AJAX request
        if (Tools::isSubmit('check_status') && Tools::getValue('service') == 'api_connection') {
            try {
                \CanadaPostPs\Tools::checkApi(self::PREFIX);
                echo json_encode(array('status' => true));
            } catch (PrestaShopException $e) {
                echo json_encode(array(
                    'status'    => false,
                    'errorHtml' => $this->displayError($this->l(CanadaPostPs\Tools::$error_messages['API_TEST_FAILED']) . $e->getMessage())
                ));
            }
            exit;
        }

        // Check if there are any products that don't fit in any of the boxes via AJAX
        if (Tools::isSubmit('check_status') && Tools::getValue('service') == 'boxes') {
            // If module is not yet configured, exit
            $largest_box = CanadaPostPs\Box::getLargestBox(true);
            if (!$largest_box || !$this->isConnected()) {
                echo json_encode(array('status' => true));
                exit;
            }

            $largest_box = new \CanadaPostPs\Box($largest_box['id_box']);
            $boxesArr = \CanadaPostPs\Box::getBoxes(array('active' => 1));
            $BoxList = new \CanadaPost\BoxPacker\BoxList();
            foreach ($boxesArr as $boxArr) {
                $BoxList->insert(new \CanadaPostPs\Box($boxArr['id_box']));
            }

            $products        = Product::getProducts($this->context->language->id, 0, 0, 'id_product', 'ASC');
            $items_too_large = array();

            // Pack each product in the largest active box one by one to see if any of them are too large
            foreach ($products as $product) {
                $packer = new \CanadaPost\BoxPacker\Packer();
//                $packer->addBox($largest_box);
                $packer->setBoxes($BoxList);
                $packer->addItem(new CanadaPostPs\Item($product['id_product']), 1);

                try {
                    $packer->pack();
                } catch (\CanadaPost\BoxPacker\ItemTooLargeException $e) {
                    $items_too_large[] = $e->item;
                }
            }
            // Display warning if any items are too large
            if (!empty($items_too_large)) {
                $error_msg = '';
                foreach ($items_too_large as $i) {
                    $error_msg .= \CanadaPostPs\Tools::renderHtmlTag('li', $i->getDescription());
                }

                echo json_encode(array(
                    'status'    => false,
                    'errorHtml' => $this->displayWarning(
                            \CanadaPostPs\Tools::renderHtmlTag(
                                'b',
                                sprintf(
                                    $this->l(CanadaPostPs\Tools::$error_messages['PRODUCTS_DONT_FIT_BOX']),
                                    $largest_box->name
                                )
                            ) . \CanadaPostPs\Tools::renderHtmlTag('ul', $error_msg)
                    )
                ));
            } else {
                echo json_encode(array('status' => true));
            }
            exit;
        }

        if (Tools::isSubmit('submitVerify')) {
            if (!sizeof($this->_postErrors)) {
                $verify = CanadaPostPs\Tools::verify(
                    self::PREFIX,
                    Tools::getValue(self::PREFIX . 'VE'),
                    Tools::getValue(self::PREFIX . 'VS')
                );

                if ($verify['status'] == 1) {
                    Tools::redirectAdmin($configLink);
                } elseif ($verify['status'] == 0) {
                    return $this->displayError($verify['message']);
                } else {
                    return false;
                }
            }
        }

        /* Disconnect account */
        if (Tools::isSubmit('submitDisconnect')) {
            self::updateConfig('MODE', "1");
            $accountValues = array(
                'CUSTOMER_NUMBER',
                'CONTRACT',
                'PROD_API_USER',
                'PROD_API_PASS',
                'PLATFORM_ID',
            );
            foreach ($accountValues as $accountValue) {
                Configuration::deleteByName(self::PREFIX . $accountValue);
            }

            Tools::redirectAdmin($configLink);
        }

        // Save rates methods and install carriers
        if (Tools::isSubmit('submitRates')) {
            // Save uploaded logo file
            if (
                isset($_FILES[self::PREFIX . 'CARRIER_LOGO_FILE']) &&
                isset($_FILES[self::PREFIX . 'CARRIER_LOGO_FILE']['tmp_name']) &&
                !empty($_FILES[self::PREFIX . 'CARRIER_LOGO_FILE']['tmp_name'])
            ) {
                if ($error = ImageManager::validateUpload($_FILES[self::PREFIX . 'CARRIER_LOGO_FILE'], 4000000)) {
                    return $error;
                } else {
                    $file_name = $_FILES[self::PREFIX . 'CARRIER_LOGO_FILE']['name'];

                    if (!move_uploaded_file(
                        $_FILES[self::PREFIX . 'CARRIER_LOGO_FILE']['tmp_name'],
                        dirname(__FILE__) . DIRECTORY_SEPARATOR . 'views/img/uploads' . DIRECTORY_SEPARATOR . $file_name
                    )) {
                        return $this->displayError($this->l(CanadaPostPs\Tools::$error_messages['FILE_UPLOAD']));
                    } else {
                        self::updateConfig('CARRIER_LOGO_FILE', $file_name);
                    }
                }
            }

            // Install and update carriers
            foreach (CanadaPostPs\Method::getMethods() as $m) {
                $method = new CanadaPostPs\Method($m['id_method']);
                $file   = _PS_SHIP_IMG_DIR_ . (int)$m['id_carrier'] . '.jpg';

                //Form $_POST changes our "." to an "_" (e.g. DOM.RP to DOM_RP),
                //so we must change ours to correctly reference it.
                $code = str_replace(".", "_", $method->code);

                if (Tools::getIsset(self::PREFIX . 'METHODS_' . $code)) {
                    $method->active = 1;
                    // If the back-office carrier hasn't been installed yet, create a carrier for the selected method
                    if (!$method->id_carrier) {
                        $this->installCarrier($method);
                    } else {
                        $carrier = new Carrier((int)$method->id_carrier);
                        // If the carrier was deleted from BO carrier page, install it again. Otherwise activate it.
                        if ($carrier->deleted == 1) {
                            $this->installCarrier($method);
                        } else {
                            $carrier->active = true;
                            $carrier->update();
                        }
                    }
                } else {
                    $method->active = 0;
                    if ($method->id_carrier) {
                        $carrier = new Carrier((int)$m['id_carrier']);

                        $carrier->active = false;
                        $carrier->update();
                    }
                }
                $method->save();

                // Add or remove carrier image
                if (!self::getConfig('CARRIER_IMAGE')) {
                    if (file_exists($file)) {
                        unlink($file);
                    }
                } elseif (!file_exists($file) || isset($_FILES[self::PREFIX . 'CARRIER_LOGO_FILE'])) {
                    if (file_exists($file)) {
                        unlink($file);
                    }
                    Tools::copy(
                        dirname(__FILE__) . '/views/img/uploads/' . self::getConfig('CARRIER_LOGO_FILE'),
                        $file
                    );
                }
            }
        }

        // Save label preferences
        if (Tools::isSubmit('submitLabels')) {
            $checkboxFields = array(
                'options' => array_keys(\CanadaPostPs\Method::$options),
                'notification' => \CanadaPostPs\Method::$notifications
            );
            foreach ($checkboxFields as $name => $checkboxField) {
                foreach ($checkboxField as $field) {
                    $field = sprintf('%s%s_%s', self::PREFIX, $name, $field);
                    if (Tools::isSubmit($field)) {
                        Configuration::updateValue($field, Tools::getValue($field));
                    } else {
                        Configuration::updateValue($field, false);
                    }
                }
            }
        }

        // Save config values
        foreach ($this->getConfigTabs() as $id => $configTab) {
            if ($configTab['type'] == 'form') {
                if (Tools::isSubmit('submit'.\Tools::ucfirst($id))) {
                    foreach ($this->getConfigFieldsValues() as $name => $value) {
                        if (Tools::isSubmit($name) && Tools::strpos($name, 'FILE') === false) {
                            Configuration::updateValue($name, Tools::getValue($name));
                        }
                        foreach (\CanadaPostPs\Forms::$multiSelectValues as $multiSelectValue) {
                            if (Tools::getIsset(self::PREFIX . $multiSelectValue . '_FIELD')) {
                                if ($name == self::PREFIX . $multiSelectValue.'[]') {
                                    Configuration::updateValue(
                                        self::PREFIX . $multiSelectValue,
                                        \CanadaPostPs\Tools::setMultiSelectValues(Tools::getValue(self::PREFIX . $multiSelectValue))
                                    );
                                }
                            }
                        }
                    }

                    return $this->displayConfirmation('Settings updated.');
                }
            }
        }

        /* Save preferences */
        if (Tools::isSubmit('submitPreferences')) {
            // Toggle Contract admin tabs
            if ($this->isContract()) {
                $this->enableContractTabs();
            } else {
                $this->disableContractTabs();
            }
        }

        /*
         * Insert/delete Object
         * We iterate through each of the module's Object types and dynamically instantiate them
         * */
        foreach ($this->models as $className) {
            $obj = $this->namespace . $className;
            $table = _DB_PREFIX_ . $obj::$definition['table'];

            /* @var $Class ObjectModel */
            if (Tools::isSubmit('save' . $table)) {
                // Save object
                $Class = new $obj(Tools::getValue($obj::$definition['primary']));

                foreach ($obj::$definition['fields'] as $field => $value) {
                    $Class->{$field} = Tools::getValue($field);
                }

                if (Tools::getIsset('origin') && Tools::getValue('origin') == 1) {
                    $Class->setAsOrigin();
                }

                $Class->active = $Class->id ? $Class->active : Tools::getValue('active', 1);

                // Set default requested shipping point to address postcode if not set
                if ($className == 'Address' &&
                    Tools::getIsset('postcode') &&
                    !self::getConfig('REQUESTED_SHIPPING_POINT')
                ) {
                    $postcode = preg_replace(
                        '/[^A-Za-z0-9]/',
                        '',
                        Tools::strtoupper(Tools::getValue('postcode'))
                    );
                    self::updateConfig('REQUESTED_SHIPPING_POINT', $postcode);
                }

                if ($Class->save()) {
                    $this->updateAssoShop($obj, $Class->id);
                }
                Tools::redirectAdmin($configLink);
            } elseif (Tools::isSubmit('delete' . $table) ||
                      Tools::isSubmit('submitBulkdelete' . $table)) {
                // Delete object
                try {
                    // Bulk delete
                    if (Tools::getIsset($table . 'Box') &&
                        is_array(Tools::getValue($table . 'Box'))) {
                        foreach (Tools::getValue($table . 'Box') as $id_obj) {
                            $Class = new $obj((int)$id_obj);
                            $Class->delete();
                        }
                    } else {
                        // Single delete
                        $Class = new $obj((int)Tools::getValue($obj::$definition['primary']));
                        $Class->delete();
                    }
                    return $this->displayConfirmation($this->l($className . ' deleted.'));
                } catch (PrestaShopException $e) {
                    return $this->displayError($e->getMessage());
                }
            } elseif (Tools::isSubmit('status' . $table) ||
                      Tools::isSubmit('submitBulkdisableSelection' . $table) ||
                      Tools::isSubmit('submitBulkenableSelection' . $table)) {

                // Bulk toggle status
                if (Tools::getIsset($table . 'Box') &&
                    is_array(Tools::getValue($table . 'Box'))) {
                    foreach (Tools::getValue($table . 'Box') as $id_obj) {
                        $Class = new $obj((int)$id_obj);
                        $Class->active = Tools::isSubmit('submitBulkdisableSelection' . $table) ? false : true;
                        $Class->update(false);
                    }
                } else {
                    // Single toggle status
                    $Class = new $obj((int)Tools::getValue($obj::$definition['primary']));
                    $Class->toggleStatus();
                }

                Tools::redirectAdmin($configLink);
            } elseif (Tools::isSubmit('origin' . $table)) {
                $Class = new $obj((int)Tools::getValue($obj::$definition['primary']));
                $Class->setAsOrigin();
                $Class->save();
            }
        }
    }

    /* Values for configuration form */
    public function getConfigFieldsValues()
    {
        $configValues = array_merge(
            \CanadaPostPs\Forms::$preferencesValues,
            \CanadaPostPs\Forms::$ratesValues,
            \CanadaPostPs\Forms::$labelDefaultValues,
            \CanadaPostPs\Forms::$bulkLabelDefaultValues,
            \CanadaPostPs\Forms::$trackingDefaultValues
        );
        $values       = array();
        foreach ($configValues as $value) {
            $values[self::PREFIX . $value] = Tools::getValue(self::PREFIX . $value, self::getConfig($value));
        }

        // Default label options checkboxes
        foreach (\CanadaPostPs\Method::$options as $code => $name) {
            $value = 'options_' . $code;
            $values[self::PREFIX . $value] = Tools::getValue(self::PREFIX . $value, self::getConfig($value));
        }

        // Default label notification checkboxes
        foreach (\CanadaPostPs\Method::$notifications as $code) {
            $value = 'notification_' . $code;
            $values[self::PREFIX . $value] = Tools::getValue(self::PREFIX . $value, self::getConfig($value));
        }

        foreach (\CanadaPostPs\Forms::$multiSelectValues as $multiSelectValue) {
            $values[self::PREFIX . $multiSelectValue . '_FIELD'] = 'field_present';
            $values[self::PREFIX . $multiSelectValue . '[]'] = Tools::getValue(
                self::PREFIX . $multiSelectValue,
                \CanadaPostPs\Tools::getMultiSelectValues(self::getConfig($multiSelectValue))
            );
        }

        // Object values
        foreach ($this->models as $className) {
            $obj            = $this->namespace . $className;
            if (Tools::isSubmit('update' . _DB_PREFIX_ . $obj::$definition['table']) ||
                Tools::isSubmit('add' . _DB_PREFIX_ . $obj::$definition['table'])) {
                /* @var $Class ObjectModel */
                $Class                           = new $obj((int)Tools::getValue($obj::$definition['primary']));
                $values[$obj::$definition['primary']] = $Class->id;

                foreach ($obj::$definition['fields'] as $field => $value) {
                    $values[$field] = $Class->id ? $Class->{$field} : Tools::getValue($field, '');
                }
                $values['active'] = $Class->id ? $Class->active : Tools::getValue('active', 1);
            }
        }

        // Method values
        if (Tools::getValue('configure') == $this->name && CanadaPostPs\Tools::tableExists(CanadaPostPs\Method::$definition['table'])) {
            $methods = CanadaPostPs\Method::getMethods();
            foreach ($methods as $m) {
                // Replace dots from our shipping method IDs because Configuration obj doesn't accept dots
//                $val = self::PREFIX . 'METHODS_' . str_replace('.', '_', $m['code']);
//                $values[$val] = Tools::getValue($val, $m['active']);
                $values[self::PREFIX . 'METHODS_' . $m['code']] = Tools::getValue(
                    self::PREFIX . 'METHODS_' . $m['code'],
                    $m['active']
                );
            }
        }

        if (Tools::getValue('configure') == $this->name && !$this->isConnected()) {
            // We append "&request=1" to URL because Canada Post returns appended GET variables
            // with a "?" instead of "&" which interferes with GET variables already in our URL.
            $values['return-url'] = $this->context->link->getBaseLink() . basename(_PS_ADMIN_DIR_) . '/' . Dispatcher::getInstance()->createUrl('AdminModules', $this->context->language->id) . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules') . '&request=1';
            $values['token-id']    = $this->context->cookie->__isset('canadapost_token') ? $this->context->cookie->canadapost_token : '';
            $values['platform-id'] = self::getConfig('PLATFORM_ID') ? self::getConfig('PLATFORM_ID') : '';
        }

        // Image upload
        $values[self::PREFIX . 'CARRIER_LOGO_FILE'] = self::getConfig('CARRIER_LOGO_FILE');

        return $values;
    }

    /**
     * Returns an array with selected shops and type (group or boutique shop).
     *
     * @param string $table
     *
     * @return array
     */
    protected function getSelectedAssoShop($table)
    {
        if (!Shop::isFeatureActive() || !Shop::isTableAssociated($table)) {
            return array();
        }

        $shops = Shop::getShops(true, null, true);
        if (count($shops) == 1 && isset($shops[0])) {
            return array($shops[0], 'shop');
        }

        $assos = array();
        if (Tools::isSubmit('checkBoxShopAsso_' . $table)) {
            foreach (Tools::getValue('checkBoxShopAsso_' . $table) as $id_shop => $value) {
                $assos[] = (int) $id_shop;
            }
        } elseif (Shop::getTotalShops(false) == 1) {
            // if we do not have the checkBox multishop, we can have an admin with only one shop and being in multishop
            $assos[] = (int) Shop::getContextShopID();
        }

        return $assos;
    }

    /**
     * Update the associations of shops.
     *
     * @param string $Object
     * @param int $id_object
     *
     * @return bool|void
     *
     * @throws PrestaShopDatabaseException
     */
    protected function updateAssoShop($Object, $id_object)
    {
        if (!Shop::isFeatureActive()) {
            return;
        }

        if (!Shop::isTableAssociated($Object::$definition['table'])) {
            return;
        }

        $assos_data = $this->getSelectedAssoShop($Object::$definition['table']);

        // Get list of shop id we want to exclude from asso deletion
        $exclude_ids = $assos_data;
        foreach (Db::getInstance()->executeS('SELECT id_shop FROM ' . _DB_PREFIX_ . 'shop') as $row) {
            if (!$this->context->employee->hasAuthOnShop($row['id_shop'])) {
                $exclude_ids[] = $row['id_shop'];
            }
        }
        Db::getInstance()->delete($Object::$definition['table'] . '_shop', '`' . bqSQL($Object::$definition['primary']) . '` = ' . (int) $id_object . ($exclude_ids ? ' AND id_shop NOT IN (' . implode(', ', array_map('intval', $exclude_ids)) . ')' : ''));

        $insert = array();
        foreach ($assos_data as $id_shop) {
            $insert[] = array(
                $Object::$definition['primary'] => (int) $id_object,
                'id_shop' => (int) $id_shop,
            );
        }

        return Db::getInstance()->insert($Object::$definition['table'] . '_shop', $insert, false, true, Db::INSERT_IGNORE);
    }

    /**
     * Append params to getAdminLink function for older PS versions
     * */
    public function getAdminLink($controller, $withToken = true, $sfRouteParams = array(), $params = array())
    {
        if (version_compare(_PS_VERSION_, '1.7.0.3') < 0) {
            $url = $this->context->link->getAdminLink(
                $controller,
                $withToken
            );
            $getSeparator = Tools::strpos($url, '?') !== false ? '&' : '?';
            return $url.$getSeparator.http_build_query($params, '', '&');
        } else {
            return $this->context->link->getAdminLink(
                $controller,
                $withToken,
                $sfRouteParams,
                $params
            );
        }
    }

    public function getConfigTabs()
    {
        $tabs = array(
            'account'     => array(
                'title'   => $this->l('Account'),
                'id'      => 'account',
                'icon'    => 'icon-user',
                'badge'   => false,
                'type'    => 'form',
                'enabled' => $this->isVerified(),
            ),
            'preferences' => array(
                'title'   => $this->l('Preferences'),
                'id'      => 'preferences',
                'icon'    => 'icon-cogs',
                'badge'   => false,
                'type'    => 'form',
                'enabled' => $this->isConnected() && $this->isVerified(),
            ),
            'rates'       => array(
                'title'   => $this->l('Rates'),
                'id'      => 'rates',
                'icon'    => 'icon-usd',
                'badge'   => false,
                'type'    => 'form',
                'enabled' => $this->isConnected() && $this->isVerified(),
            ),
            'ordertracking'       => array(
                'title'   => $this->l('Tracking'),
                'id'      => 'ordertracking',
                'icon'    => 'icon-crosshairs',
                'badge'   => false,
                'type'    => 'form',
                'enabled' => $this->isConnected() && $this->isVerified(),
            ),
            'labels'       => array(
                'title'   => $this->l('Labels'),
                'id'      => 'labels',
                'icon'    => 'icon-barcode',
                'badge'   => false,
                'type'    => 'form',
                'enabled' => $this->isConnected() && $this->isVerified(),
            ),
            'bulklabels'       => array(
                'title'   => $this->l('Bulk Order Labels'),
                'id'      => 'bulklabels',
                'icon'    => 'icon-files-o',
                'badge'   => false,
                'type'    => 'form',
                'enabled' => $this->isConnected() && $this->isVerified(),
            ),
            'carriermapping'     => array(
                'title'   => $this->l('Carrier Mappings'),
                'id'      => 'carriermapping',
                'icon'    => 'icon-truck',
                'badge'   => count(\CanadaPostPs\CarrierMapping::getCarrierMappings()),
                'type'    => 'list',
                'enabled' => $this->isConnected() && $this->isVerified(),
            ),
            'address'     => array(
                'title'   => $this->l('Addresses'),
                'id'      => 'address',
                'icon'    => 'icon-globe',
                'badge'   => count(\CanadaPostPs\Address::getAddresses()),
                'type'    => 'list',
                'enabled' => $this->isConnected() && $this->isVerified(),
            ),
            'ratediscount'       => array(
                'title'   => $this->l('Rate Discount Rules'),
                'id'      => 'ratediscount',
                'icon'    => 'icon-tag',
                'badge'   => count(\CanadaPostPs\RateDiscount::getRateDiscounts(
                    false,
                    Context::getContext()->shop->id
                )),
                'type'    => 'list',
                'enabled' => $this->isConnected() && $this->isVerified(),
            ),
            'box'         => array(
                'title'   => $this->l('Boxes'),
                'id'      => 'box',
                'icon'    => 'icon-archive',
                'badge'   => count(\CanadaPostPs\Box::getBoxes()),
                'type'    => 'list',
                'enabled' => $this->isConnected() && $this->isVerified(),
            ),
            'group'       => array(
                'title'   => $this->l('Groups'),
                'id'      => 'group',
                'icon'    => 'icon-group',
                'badge'   => count(\CanadaPostPs\Group::getGroups()),
                'type'    => 'list',
                'enabled' => $this->isConnected() && $this->isVerified(),
            ),
            'performance'       => array(
                'title'   => $this->l('Performance and Storage'),
                'id'      => 'performance',
                'icon'    => 'icon-gear',
                'badge'   => false,
                'type'    => 'form',
                'enabled' => $this->isConnected() && $this->isVerified(),
            ),
        );

        if (!$this->isVerified()) {
            $verify = array(
                'verify' => array(
                    'title'   => $this->l('Product Activation'),
                    'id'      => 'verify',
                    'icon'    => 'icon-key',
                    'badge'   => false,
                    'type'    => 'form',
                    'enabled' => true,
                )
            );
            $tabs   = $verify + $tabs;
        }


        return $tabs;
    }

    public function getAdminOrderTabs($id_order)
    {
        $tabs = array(
            'createLabel'     => array(
                'title'   => $this->l('Create Label'),
                'id'      => 'createLabel',
                'icon'    => \CanadaPostPs\Icon::getIconHtml('print'),
                'badge'   => false,
                'enabled' => true,
            ),
            'createReturnLabel'     => array(
                'title'   => $this->l('Create Return Label'),
                'id'      => 'createReturnLabel',
                'icon'    => \CanadaPostPs\Icon::getIconHtml('arrow_back'),
                'badge'   => false,
                'enabled' => true,
            ),
            'shipmentList'     => array(
                'title'   => $this->l('View Shipments'),
                'id'      => 'shipmentList',
                'icon'    => \CanadaPostPs\Icon::getIconHtml('list'),
                'badge'   => count(\CanadaPostPs\Shipment::getShipments(array('id_order' => $id_order))),
                'enabled' => true,
            ),
            'returnShipmentList'     => array(
                'title'   => $this->l('View Return Shipments'),
                'id'      => 'returnShipmentList',
                'icon'    => \CanadaPostPs\Icon::getIconHtml('list'),
                'badge'   => count(\CanadaPostPs\ReturnShipment::getReturnShipments(array('id_order' => $id_order))),
                'enabled' => true,
            ),
        );


        return $tabs;
    }

    /**
     * Generate custom list actions for HelperList
     *
     * @throws Exception
     */
    public function generateShipmentLink($obj, $name, $title, $action, $icon, $token, $id, $target = false)
    {
        $helper = new HelperList();
        $helper->module = $this;
        $tpl = $helper->createTemplate('list_action_button.tpl');

        $viewOrder = Tools::getIsset('vieworder') ? '&id_order='.Tools::getValue('id_order').'&vieworder' : '';

        // Legacy controllers use $currentIndex
        $index = AdminController::$currentIndex;
        // in 1.7.7+, we use the Symfony request
        if (self::psVersionIsAtLeast('1.7.7') && isset($this->context->container)) {

            // Check if this is an order and get the individual order's URL
            if ($orderId = $this->getRequestStack()->getCurrentRequest()->attributes->get('orderId')) {

                $index = $this->getAdminLink(
                    'AdminOrders',
                    true,
                    array(),
                    array('id_order' => $orderId, 'vieworder' => true)
                );
            } else {
                // Otherwise use the current requestUri
                $request           = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
                $index = $request->getRequestUri();
            }

            $token = null;
        }

        $tpl->assign(array(
            'href' => $index . $viewOrder. '&' . $obj::$definition['primary'] . '=' . (int) $id . '&'.$action.'&' . _DB_PREFIX_ . $obj::$definition['table'] . '&token=' . ($token != null ? $token : Tools::getAdminTokenLite($this->context->controller->controller_name)),
            'action' => $this->l($title),
            'name' => $name,
            'icon' => $icon,
            'target' => $target
        ));

        if ($action == 'void') {
            $tpl->assign(array(
                'confirm' => $this->l('Are you sure you want to void this shipment?')
            ));
        }

        if ($action == 'refund') {
            $tpl->assign(array(
                'confirm' => $this->l('Are you sure you want to refund this shipment?')
            ));
        }

        return $tpl->fetch();
    }

    public function displayPrintLink($token, $id, $name)
    {
        return $this->generateShipmentLink('\CanadaPostPs\Shipment', $name, 'Print', 'print', 'print', $token, $id, '_blank');
    }

    public function displayPrintCommercialInvoiceLink($token, $id, $name)
    {
        $Shipment = new \CanadaPostPs\Shipment($id);
        if (Validate::isLoadedObject($Shipment)) {
            if (!empty($Shipment->commercial_invoice_link)) {
                return $this->generateShipmentLink(
                    '\CanadaPostPs\Shipment',
                    $name,
                    'Print Commercial Invoice',
                    'print_commercial_invoice',
                    'file-text',
                    $token,
                    $id,
                    '_blank'
                );
            }
            return false;
        }
        return false;
    }

    public function displayPrintReturnLink($token, $id, $name)
    {
        return $this->generateShipmentLink('\CanadaPostPs\ReturnShipment', $name, 'Print', 'print_return', 'print', $token, $id, '_blank');
    }

    public function displayPrintBatchLink($token, $id, $name)
    {
        return $this->generateShipmentLink('\CanadaPostPs\Batch', $name, 'Print', 'printbatch', 'print', $token, $id, '_blank');
    }

    public function displayVoidLink($token, $id, $name)
    {
        return $this->generateShipmentLink('\CanadaPostPs\Shipment', $name, 'Void', 'void', 'trash', $token, $id);
    }

    public function displayPrintManifestLink($token, $id, $name)
    {
        return $this->generateShipmentLink('\CanadaPostPs\Manifest', $name, 'Print', 'print_manifest', 'print', $token, $id, '_blank');
    }

    public function displayRefundLink($token, $id, $name)
    {
        $Shipment = new \CanadaPostPs\Shipment($id);
        if (Validate::isLoadedObject($Shipment)) {
            if (!empty($Shipment->refund_link) && !$Shipment->voided) {
                return $this->generateShipmentLink('\CanadaPostPs\Shipment', $name, 'Refund', 'refund', 'dollar', $token, $id);
            }
        }
    }

    public function displayCreateLabelLink($token, $id, $name)
    {
        return $this->generateShipmentLink('Order', $name, 'Create Label', 'create_label', 'print', $token, $id, '_blank');
    }

    public function displayUnmapLink($token, $id, $name)
    {
        $carrierMappingArr = \CanadaPostPs\CarrierMapping::getCarrierMappingByCarrierId($id);
        if ($carrierMappingArr) {
            $helper = new HelperList();
            $helper->module = $this;
            $tpl = $helper->createTemplate('list_action_button.tpl');

            $tpl->assign(array(
                'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&' . \CanadaPostPs\CarrierMapping::$definition['primary'] . '=' . (int) $carrierMappingArr['id_carrier_mapping'] . '&delete' . _DB_PREFIX_ . \CanadaPostPs\CarrierMapping::$definition['table'] . '&token=' . ($token != null ? $token : Tools::getAdminTokenLite($this->context->controller->controller_name)),
                'action' => $this->l('Delete Mapping'),
                'name' => $name,
                'icon' => 'trash',
                'target' => false
            ));
            return $tpl->fetch();
        }
    }

    /**
     * @var $ShipmentInfoType \CanadaPostWs\Type\Shipment\ShipmentInfoType|\CanadaPostWs\Type\NcShipment\NonContractShipmentInfoType
     * @return \CanadaPostPs\Shipment
     * */
    public function createShipmentObject($ShipmentInfoType, $id_order = false, $id_group = false, $id_batch = false)
    {
        $Shipment = new \CanadaPostPs\Shipment();
        if ($id_order) {
            $Shipment->id_order = $id_order;
        }
        if ($id_group) {
            $Shipment->id_group = $id_group;
        }
        if ($id_batch) {
            $Shipment->id_batch = $id_batch;
        }
        $Shipment->name = $ShipmentInfoType->getDeliverySpec()->getDestination()->getName();
        $Shipment->address1 = $ShipmentInfoType->getDeliverySpec()->getDestination()->getAddressDetails()->getAddressLine1();
        $Shipment->address2 = $ShipmentInfoType->getDeliverySpec()->getDestination()->getAddressDetails()->getAddressLine2();
        $Shipment->city = $ShipmentInfoType->getDeliverySpec()->getDestination()->getAddressDetails()->getCity();
        $Shipment->prov_state = $ShipmentInfoType->getDeliverySpec()->getDestination()->getAddressDetails()->getProvState();
        $Shipment->country_code = $ShipmentInfoType->getDeliverySpec()->getDestination()->getAddressDetails()->getCountryCode();
        $Shipment->postal_zip_code = $ShipmentInfoType->getDeliverySpec()->getDestination()->getAddressDetails()->getPostalZipCode();
        $Shipment->tracking_pin = $ShipmentInfoType->getTrackingPin();
        $Shipment->shipment_id = $ShipmentInfoType->getShipmentId();
        if ($ShipmentInfoType instanceof \CanadaPostWs\Type\Shipment\ShipmentInfoType && null !== $ShipmentInfoType->getReturnTrackingPin()) {
            $Shipment->return_tracking_pin = $ShipmentInfoType->getReturnTrackingPin();
        }
        $Shipment->service_code = $ShipmentInfoType->getDeliverySpec()->getServiceCode();
        if (null !== $ShipmentInfoType->getSelfLink()) {
            $Shipment->self_link = $ShipmentInfoType->getSelfLink()->getHref();
        }
        if (null !== $ShipmentInfoType->getDetailsLink()) {
            $Shipment->details_link = $ShipmentInfoType->getDetailsLink()->getHref();
        }
        if (null !== $ShipmentInfoType->getLabelLink()) {
            $Shipment->label_link = $ShipmentInfoType->getLabelLink()->getHref();
        }
        if ($ShipmentInfoType instanceof \CanadaPostWs\Type\Shipment\ShipmentInfoType && null !== $ShipmentInfoType->getReturnLabelLink()) {
            $Shipment->return_label_link = $ShipmentInfoType->getReturnLabelLink()->getHref();
        }
        if (null !== $ShipmentInfoType->getCommercialInvoiceLink()) {
            $Shipment->commercial_invoice_link = $ShipmentInfoType->getCommercialInvoiceLink()->getHref();
        }
        if (null !== $ShipmentInfoType->getRefundLink()) {
            $Shipment->refund_link = $ShipmentInfoType->getRefundLink()->getHref();
        }
        $Shipment->transmitted = false;
        $Shipment->voided = false;

        $Shipment->save();

        return $Shipment;
    }

    /**
     * @var $ManifestType \CanadaPostWs\Type\Manifest\ManifestType
     * @var $ManifestDetailsType \CanadaPostWs\Type\Manifest\ManifestDetailsType
     * @return \CanadaPostPs\Manifest
     * */
    public function createManifestObject($ManifestType, $ManifestDetailsType)
    {
        $Manifest = new \CanadaPostPs\Manifest();

        $Manifest->poNumber = $ManifestDetailsType->getPoNumber();
        $Manifest->contractId = $ManifestDetailsType->getContractId();
        $Manifest->methodOfPayment = $ManifestDetailsType->getMethodOfPayment();
        $Manifest->totalCost = $ManifestDetailsType->getManifestPricingInfoType()->getTotalDueCpc();
        $Manifest->manifestDateTime = $ManifestDetailsType->getManifestDate();

        if (null !== $ManifestType->getSelfLink()) {
            $Manifest->self_link = $ManifestType->getSelfLink()->getHref();
        }
        if (null !== $ManifestType->getArtifactLink()) {
            $Manifest->label_link = $ManifestType->getArtifactLink()->getHref();
        }
        if (null !== $ManifestType->getDetailsLink()) {
            $Manifest->details_link = $ManifestType->getDetailsLink()->getHref();
        }
        if (null !== $ManifestType->getShipmentsLink()) {
            $Manifest->manifest_shipments_link = $ManifestType->getShipmentsLink()->getHref();
        }

        $Manifest->save();

        return $Manifest;
    }

    /**
     * @var $AuthorizedReturnInfoType \CanadaPostWs\Type\AuthorizedReturn\AuthorizedReturnInfoType
     * @var $DeliverySpec \CanadaPostWs\Type\Shipment\DeliverySpecType
     * @return \CanadaPostPs\ReturnShipment
     * */
    public function createReturnShipmentObject($AuthorizedReturnInfoType, $DeliverySpec, $id_order = false, $id_batch = false)
    {
        $ReturnShipment = new \CanadaPostPs\ReturnShipment();
        if ($id_order) {
            $ReturnShipment->id_order = $id_order;
        }
        if ($id_batch) {
            $ReturnShipment->id_batch = $id_batch;
        }
        $ReturnShipment->name = $DeliverySpec->getDestination()->getName();
        $ReturnShipment->address1 = $DeliverySpec->getDestination()->getAddressDetails()->getAddressLine1();
        $ReturnShipment->address2 = $DeliverySpec->getDestination()->getAddressDetails()->getAddressLine2();
        $ReturnShipment->city = $DeliverySpec->getDestination()->getAddressDetails()->getCity();
        $ReturnShipment->province = $DeliverySpec->getDestination()->getAddressDetails()->getProvState();
        $ReturnShipment->postal_code = $DeliverySpec->getDestination()->getAddressDetails()->getPostalZipCode();
        $ReturnShipment->service_code = $DeliverySpec->getServiceCode();

        $ReturnShipment->tracking_pin = $AuthorizedReturnInfoType->getTrackingPin();
        if (null !== $AuthorizedReturnInfoType->getReturnLabelLink()) {
            $ReturnShipment->return_label_link = $AuthorizedReturnInfoType->getReturnLabelLink()->getHref();
        }

        $ReturnShipment->save();

        return $ReturnShipment;
    }

    /**
     * @var $TrackingSummaryType \CanadaPostWs\Type\Tracking\TrackingSummaryType
     * @var $id_order int
     * @var $id_shipment int
     * @var $CacheTracking \CanadaPostPs\CacheTracking|bool
     *
     * @return \CanadaPostPs\CacheTracking
     * */
    public function saveCacheTrackingObject($TrackingSummaryType, $id_order, $id_shipment, $CacheTracking = false)
    {
        if (!$CacheTracking) {
            $CacheTracking = new \CanadaPostPs\CacheTracking();
        }

        $CacheTracking->id_order = $id_order;
        $CacheTracking->id_shipment = $id_shipment;
        $CacheTracking->pin = $TrackingSummaryType->getPinSummary()->getPin();
        $CacheTracking->service_name = $TrackingSummaryType->getPinSummary()->getServiceName();
        $CacheTracking->event_type = $TrackingSummaryType->getPinSummary()->getEventType();
        $CacheTracking->event_location = $TrackingSummaryType->getPinSummary()->getEventLocation();
        $CacheTracking->event_description = $TrackingSummaryType->getPinSummary()->getEventDescription();
        $CacheTracking->expected_delivery_date = $TrackingSummaryType->getPinSummary()->getExpectedDeliveryDate();
        $CacheTracking->actual_delivery_date = $TrackingSummaryType->getPinSummary()->getActualDeliveryDate();
        $CacheTracking->mailed_on_date = $TrackingSummaryType->getPinSummary()->getMailedOnDate();

        if (null !== $TrackingSummaryType->getPinSummary()->getEventDateTime()) {
            $DateTime = DateTime::createFromFormat('Ymd:His', $TrackingSummaryType->getPinSummary()->getEventDateTime());
            $CacheTracking->event_date_time = $DateTime->format('Y-m-d H:i:s');
        }

        $CacheTracking->save();

        return $CacheTracking;
    }

    /**
     * Display AdminOrders forms
     * */
    public function hookDisplayAdminOrder($params)
    {
        if (!$this->isVerified() || !$this->isConnected()) {
            return false;
        }

        $adminOrdersControllerUrl = $this->getAdminLink(
            'AdminOrders',
            true,
            array(),
            array('id_order' => $params['id_order'], 'vieworder' => 1)
        );

        $forms = new \CanadaPostPs\Forms();

        // Process form submits
        $forms->postProcessShipments($adminOrdersControllerUrl);

        $logo = $this->context->smarty->fetch(sprintf(_PS_MODULE_DIR_.'%s/views/templates/admin/logo.tpl', $this->name));

        // modal.tpl template vars
        $this->modals[] = array(
            'modal_id' => 'labelModal',
            'modal_title' => \CanadaPostPs\Tools::renderHtmlTag('h4', $this->l('Print Label')),
            'modal_content' => \CanadaPostPs\Tools::renderHtmlTag(
                    'div', null, array('class' => 'modal-body')
                ) . $this->logo,
            'modal_actions' => true,
            'modal_class' => 'zhmedia-modal'
        );

        $formArr = array(
            'createLabel' => $forms->renderCreateLabelForm(
                $params['id_order'],
                $adminOrdersControllerUrl
            ),
            'createReturnLabel' => $forms->renderCreateReturnLabelForm(
                $params['id_order'],
                $adminOrdersControllerUrl
            ),
            'shipmentList' => $forms->renderShipmentList(
                $this->l('Canada Post: Shipping Labels'),
                array('id_order' => $params['id_order']),
                $adminOrdersControllerUrl
            ),
            'returnShipmentList' => $forms->renderReturnShipmentList(
                $this->l('Canada Post: Return Labels'),
                array('id_order' => $params['id_order']),
                $adminOrdersControllerUrl
            ),
        );

        Media::addJsDef(array(
            'viewOrder' => true,
        ));

        $this->context->smarty->assign(array(
            'forms' => $formArr,
            'modal' => $this->renderModal(),
            'form_tabs' => $this->getAdminOrderTabs($params['id_order']),
            'logo' => $this->context->smarty->fetch(sprintf(_PS_MODULE_DIR_.'%s/views/templates/admin/logo.tpl', $this->name)),
            'icon' => \CanadaPostPs\Icon::getIconHtml('local_shipping'),
        ));

        return $this->display(__FILE__, 'forms.tpl');
    }

    /**
     * Display tracking details on order page if available
     * */
    public function hookDisplayOrderDetail($params)
    {
        if (!$this->isVerified() ||
            !$this->isConnected() ||
            !self::getConfig('ENABLE_FRONT_TRACKING')
        ) {
            return false;
        }

        $trackingSummaries = array();
        $shipmentsArr = \CanadaPostPs\Shipment::getShipments(array('id_order' => $params['order']->id));
        if (!empty($shipmentsArr)) {
            foreach ($shipmentsArr as $shipmentArr) {
                $Order = new Order($params['order']->id);
                $Shipment = new \CanadaPostPs\Shipment($shipmentArr['id_shipment']);

                $CacheTracking = $this->trackOrderShipment($Order, $Shipment);

                if (Validate::isLoadedObject($CacheTracking)) {
                    // Format expected delivery date
                    if (Validate::isDate($CacheTracking->expected_delivery_date)) {
                        $DateTime = new DateTime($CacheTracking->expected_delivery_date);
                        $trackingSummaries[$Shipment->id]['delivers'] = sprintf(
                            $this->l('Delivers %s'),
                            $DateTime->format('D M d')
                        );
                    }
                    // Format actual delivery date
                    if (Validate::isDate($CacheTracking->actual_delivery_date)) {
                        $DateTime = new DateTime($CacheTracking->actual_delivery_date);
                        $trackingSummaries[$Shipment->id]['actualDelivery'] = sprintf(
                            $this->l('Delivered on %s'),
                            $DateTime->format('D M d')
                        );
                    }
                    $trackingSummaries[$Shipment->id]['CacheTracking'] = $CacheTracking;

                    // Determine tracking progress based on event type
                    $progressWidth = 16;
                    $progressColor = 'rgb(103, 205, 94)';
                    $progressState = 'shipped';
                    if (in_array($CacheTracking->event_type, \CanadaPostPs\CacheTracking::$receivedAtFacilityEventTypes)) {
                        $progressWidth = 25;
                    } elseif (in_array($CacheTracking->event_type, \CanadaPostPs\CacheTracking::$inTransitEventTypes)) {
                        $progressWidth = 49;
                        $progressState = 'in-transit';
                    } elseif (in_array($CacheTracking->event_type, \CanadaPostPs\CacheTracking::$notDeliverableEventTypes)) {
                        $progressWidth = 65;
                        $progressColor = 'rgb(222, 148, 67)';
                        $progressState = 'in-transit';
                    } elseif (in_array($CacheTracking->event_type, \CanadaPostPs\CacheTracking::$outForDeliveryEventTypes)) {
                        $progressWidth = 81;
                        $progressState = 'in-transit';
                    } elseif (in_array($CacheTracking->event_type, \CanadaPostPs\CacheTracking::$attemptedDeliveryEventTypes)) {
                        $progressWidth = 90;
                        $progressState = 'in-transit';
                    } elseif (in_array($CacheTracking->event_type, \CanadaPostPs\CacheTracking::$deliveredEventTypes)) {
                        $progressWidth = 100;
                        $progressState = 'delivered';
                    }
                    $trackingSummaries[$Shipment->id]['progressWidth'] = $progressWidth;
                    $trackingSummaries[$Shipment->id]['progressColor'] = $progressColor;
                    $trackingSummaries[$Shipment->id]['progressState'] = $progressState;

                    $trackingSummaries[$Shipment->id]['trackingLink'] = Tools::str_replace_once('@', $CacheTracking->pin, \CanadaPostPs\Method::$tracking_url);
                }
            }
        }

        $progressStates = array(
            'shipped' => array(
                'progressLabel' => $this->l('Shipped'),
                'heading' => $this->l('Your order has been shipped'),
            ),
            'in-transit' => array(
                'progressLabel' => $this->l('In Transit'),
                'heading' => $this->l('Your order is in transit'),
            ),
//            'out-for-delivery' => $this->l('Out For Delivery'),
            'delivered' => array(
                'progressLabel' => $this->l('Delivered'),
                'heading' => $this->l('Your order has been delivered'),
            )
        );

        $this->context->smarty->assign(array(
            'trackingSummaries' => $trackingSummaries,
            'progressStates' => $progressStates,
        ));

        return $this->display(__FILE__, 'order-details.tpl');
    }

    /**
     * @var $Order Order
     * @var $Shipment \CanadaPostPs\Shipment
     *
     * @return \CanadaPostPs\CacheTracking|bool
     * */
    public function trackOrderShipment($Order, $Shipment)
    {
        $API = new \CanadaPostPs\API();
        $apiParams = $API->getApiParams();
        // Tracking doesn't work in dev env
        $apiParams['env'] = CanadaPostWs\WebService::ENV_PROD;

        $Tracking = new CanadaPostWs\Tracking($apiParams);
        $fetchNewTrackingSummary = false;

        // Check if tracking details are cached first
        $cacheTrackingArr = \CanadaPostPs\CacheTracking::getByOrderIdAndShipmentId($Order->id, $Shipment->id);
        if ($cacheTrackingArr) {
            $CacheTracking = new \CanadaPostPs\CacheTracking($cacheTrackingArr['id_cache_tracking']);

            if ($CacheTracking->event_type != 'DELIVERED') {
                $DateTime              = new DateTime();
                $CacheTrackingDateTime = new DateTime($CacheTracking->date_upd);
                $interval              = $DateTime->diff($CacheTrackingDateTime);

                // If cache is older than an hour, re-fetch tracking summary
                if ($interval && $interval->h >= 1) {
                    $fetchNewTrackingSummary = true;
                }
            }
        } else {
            $fetchNewTrackingSummary = true;
            $CacheTracking = false;
        }

        if ($fetchNewTrackingSummary) {
            $Tracking->getTrackingSummary($Shipment->tracking_pin);

            /* @var $TrackingDetailsType CanadaPostWs\Type\Tracking\TrackingSummaryType */
            $TrackingSummaryType = $Tracking->getResponse();

            if ($TrackingSummaryType instanceof \CanadaPostWs\Type\Tracking\TrackingSummaryType && $Tracking->isSuccess()) {
                $CacheTracking = $this->saveCacheTrackingObject($TrackingSummaryType, $Order->id, $Shipment->id, $CacheTracking);
            } else {
                $this->log($Tracking->getErrorMessage());
            }
        }

        // Update order status if Delivered
        if (self::getConfig('ENABLE_DELIVERY_UPDATE') &&
            self::getConfig('TRACK_ORDER_STATUSES') &&
            self::getConfig('DELIVERED_ORDER_STATUS') &&
            Validate::isLoadedObject($CacheTracking)
        ) {
            // Get order statuses to check
            $orderStatuses = \CanadaPostPs\Tools::getMultiSelectValues(self::getConfig('TRACK_ORDER_STATUSES'));

            // Only update if the newest shipment of an order was delivered
            $newestShipment = \CanadaPostPs\Shipment::getNewestShipmentForOrder($Order->id);
            if (
                $Order->current_state != self::getConfig('DELIVERED_ORDER_STATUS') &&
                in_array($Order->current_state, $orderStatuses) &&
                $CacheTracking->event_type == 'DELIVERED' &&
                $Shipment->id == $newestShipment['id_shipment'] &&
                !in_array($CacheTracking->event_type, \CanadaPostPs\CacheTracking::$notDeliverableEventTypes)
            ) {
                $Order->setCurrentState(self::getConfig('DELIVERED_ORDER_STATUS'));
            }
        }

        return $CacheTracking;
    }

    /**
     * Track all orders with specified order statuses and associated shipments
     * */
    public function trackOrders()
    {
        // Get orders by status that have shipments associated with them
        $trackOrders = \CanadaPostPs\CacheTracking::getShippedOrdersByOrderStatusIds(
            self::getConfig('TRACK_ORDER_STATUSES')
        );

        $count = count($trackOrders);
        foreach ($trackOrders as $trackOrder) {

            // Reset script time limit
            set_time_limit(30);

            $Order = new Order($trackOrder['id_order']);

            $shipmentArr = \CanadaPostPs\Shipment::getNewestShipmentForOrder($Order->id);
            if ($shipmentArr) {
                $Shipment = new \CanadaPostPs\Shipment($shipmentArr['id_shipment']);

                // Only track shipments that are less than 30 days old
                $DateTime              = new DateTime();
                $ShipmentDateTime = new DateTime($Shipment->date_add);
                $interval              = $DateTime->diff($ShipmentDateTime);

                if ($interval && $interval->days <= 30) {
                    $this->trackOrderShipment($Order, $Shipment);

                    // Throttle to avoid API limit if over 60 orders
                    if ($count > 60) {
                        sleep(1);
                    }
                }
            }
        }
    }

    /*
     * Track orders and update status when delivered
     * */
    public function hookActionCronJob()
    {
        if (self::getConfig('ENABLE_DELIVERY_UPDATE') &&
            self::getConfig('TRACK_ORDER_STATUSES') &&
            self::getConfig('DELIVERED_ORDER_STATUS')
        ) {
            $fp = fopen(dirname(__FILE__) . '/cron.log', 'a+');
            fputs($fp, 'TRACKED ORDERS at ' . date('Y-m-d H:i:s') . "\n\n");
            fclose($fp);

            $this->trackOrders();
        }

        // Clear rate cache older than 3 months
        \CanadaPostPs\Cache::cleanOldCache();
    }

    public function getCronFrequency()
    {
        return array(
            'hour' => -1, // -1 equivalent à * en cron normal
            'day' => -1,
            'month' => -1,
            'day_of_week' => -1
        );
    }

    public function displayShippingEstimator($params, $error = false)
    {
        if (!self::getConfig('ESTIMATOR')) {
            return false;
        }

        if ((version_compare(_PS_VERSION_, '1.7.0.0') < 0 &&
             Tools::getValue('controller') == 'orderopc')) {
            return false;
        }

        $productError = false;
        $id_product_attribute = null;
        if (isset($params['product'])) {
            // Some PS versions use array params instead of objs
            if (is_array($params['product'])) {
                $Product = new Product($params['product']['id_product']);
                if (isset($params['product']['id_product_attribute'])) {
                    $id_product_attribute = $params['product']['id_product_attribute'];
                }
            } elseif (is_object($params['product'])) {
                $Product = new Product($params['product']->id);
                if (isset($params['product']->id_product_attribute)) {
                    $id_product_attribute = $params['product']->id_product_attribute;
                }
            }

            if ($Product->is_virtual) {
                return false;
            }

            if (!$Product->available_for_order) {
                $productError = $this->l('Product is unavailable for purchase.');
            } elseif (!$Product->checkQty(Tools::getValue('qty', 1))) {
                $productError = $this->l('There are not enough products in stock ');
            }
        }

        // Some PS versions use array params instead of objs
        if (isset($params['cart']) && is_array($params['cart'])) {
            $Cart = new Cart($params['cart']['id_cart']);
        } elseif (isset($params['cart']) && is_object($params['cart'])) {
            $Cart = new Cart($params['cart']->id);
        }

        $hasProducts = false;
        $id_canada = Country::getByIso('CA');
        $id_us = Country::getByIso('US');
        $id_country = Configuration::get('PS_COUNTRY_DEFAULT');
        $postcode = null;
        $hasAddress = false;
        $formattedAddresses = array();

        if ($Cart->id_address_delivery) {
            $Address    = new Address($Cart->id_address_delivery);
            $id_country = $Address->id_country;
            $postcode = $Address->postcode;
            $hasAddress = true;
        } else {
            // Get default country
            if (isset($this->context->cookie->id_country) && !Tools::isEmpty($this->context->cookie->id_country)) {
                $id_country = $this->context->cookie->id_country;
            }

            if (isset($this->context->cookie->postcode) && !Tools::isEmpty($this->context->cookie->postcode)) {
                $postcode = $this->context->cookie->postcode;
            }
        }

        $defaultCountry = new Country($id_country);

        if (Validate::isLoadedObject($Cart) &&
            Validate::isLoadedObject($defaultCountry) &&
            !$productError &&
            !$error &&
            (   // if CA/US, postal code is required
                (($defaultCountry->iso_code == 'CA' || $defaultCountry->iso_code == 'US') && !Tools::isEmpty($postcode)) ||
                ($defaultCountry->iso_code != 'CA' && $defaultCountry->iso_code != 'US')
            )
        ) {
            $carrierList = false;
            if (isset($params['product'])) {
                // if current product is not in cart, or cart has fewer qty than request,
                // temporarily add it to get carrier costs
                $products = $Cart->getProducts(false, $Product->id);
                if (empty($products) || count($products) < Tools::getValue('qty', 1) + 1) {

                    // temporarily disable clearing rates cache when cart qty changes
                    self::$clearCacheOnCartUpdate = false;

                    // add product
                    $Cart->updateQty(
                        Tools::getValue('qty', 1),
                        $Product->id,
                        Tools::getValue('id_product_attribute')
                    );

                    $carrierList = $this->getCarrierList($Cart, $defaultCountry);

                    // remove product
                    $Cart->updateQty(
                        Tools::getValue('qty', 1),
                        $Product->id,
                        Tools::getValue('id_product_attribute'),
                        null,
                        'down'
                    );

                    // re-enable cache clearing
                    self::$clearCacheOnCartUpdate = true;
                }
            }

            if ($carrierList === false) {
                $carrierList = $this->getCarrierList($Cart, $defaultCountry);
            }

            if (count($carrierList) > 0) {
                $this->context->smarty->assign(array(
                    'deliveryOptionList' => $carrierList,
                ));
            }

            $products = $Cart->getProducts();
            if (!empty($products)) {
                $hasProducts = true;
            }

            if ($Cart->id_customer) {
                $Customer = new Customer($Cart->id_customer);
                $addresses = $Customer->getAddresses($this->context->language->id);
                foreach ($addresses as $address) {
                    $Address = new Address($address['id_address']);
                    $formattedAddresses[$address['id_address']] = AddressFormat::generateAddress($Address, array(), " ");
                }
            }
        }

        // is guest
        $this->context->smarty->assign(array(
            'countries' => Country::getCountries($this->context->language->id, 1),
            'selectedCountry' => $defaultCountry->id,
            'selectedAddress' => $Cart->id_address_delivery,
            'addresses' => $formattedAddresses,
            'postcode' => $postcode,
            'id_canada' => $id_canada,
            'id_us' => $id_us,
            'moduleCarrierControllerUrl' => $this->context->link->getModuleLink(
                $this->name,
                'carrier',
                array('secure_key' => $this->getSecureKey())
            ),
            'error' => $error,
            'productError' => $productError,
            'hasProducts' => $hasProducts,
            'hasAddress' => $hasAddress,
            'isQuickview' => (Tools::getValue('action') == 'quickview'),
            'secure_key' => $this->getSecureKey(),
            'isProduct' => isset($params['product']),
            'id_product_attribute' => $id_product_attribute
        ));

        return $this->display(__FILE__, 'product-rates.tpl');
    }

    public function hookDisplayProductAdditionalInfo($params)
    {
        return $this->displayShippingEstimator($params);
    }

    /*
     * displayProductButtons has been renamed into displayProductAdditionalInfo
     * in 1.7.1.x
     *
     * */
    public function hookDisplayProductButtons($params)
    {
        return $this->displayShippingEstimator($params);
    }

    public function hookDisplayShoppingCart($params)
    {
        $Cart = new Cart($params['cart']->id);
        $products = $Cart->getProducts();
        if (!Validate::isLoadedObject($Cart) || empty($products)) {
            return false;
        }

        return $this->displayShippingEstimator($params);
    }

    public function getCarrierList(Cart $Cart, Country $defaultCountry)
    {
        $carrierList = array();
        $carriers = Carrier::getCarriers(
            $this->context->language->id,
            1,
            false,
            $defaultCountry->id_zone,
            null,
            Carrier::ALL_CARRIERS
        );

        $products = $Cart->getProducts();

        // Get carriers assigned to the cart products
        $productCarrierIds = array();
        foreach ($products as $product) {
            $Product = new Product($product['id_product']);
            $productCarriers = $Product->getCarriers();

            if (!empty($productCarriers)) {
                $tmpCarrierIds = array();
                foreach ($productCarriers as $productCarrier) {
                    $tmpCarrierIds[] = $productCarrier['id_carrier'];
                }
                // Get the carriers that all products have in common (array_intersect)
                if (empty($productCarrierIds)) {
                    $productCarrierIds = $tmpCarrierIds;
                } else {
                    $productCarrierIds = array_intersect($productCarrierIds, $tmpCarrierIds);
                }
            }
        }

        // If there are assigned product carriers, remove any unassigned carriers
        if (!empty($productCarrierIds)) {
            foreach ($carriers as $key => $carrier) {
                if (!in_array($carrier['id_carrier'], $productCarrierIds)) {
                    unset($carriers[$key]);
                }
            }
        }

        foreach ($carriers as $carrier) {
            if (!$Cart->isCarrierInRange($carrier['id_carrier'], $defaultCountry->id_zone)) {
                continue;
            }

            $carrierCost = $Cart->getPackageShippingCost(
                $carrier['id_carrier'],
                self::getConfig('TAX'),
                $defaultCountry,
                $products,
                $defaultCountry->id_zone
            );

            if (is_numeric($carrierCost)) {
                $carrierList[$carrier['id_carrier']] = array(
                    'id_carrier' => $carrier['id_carrier'],
                    'name' => $carrier['name'],
                    'cost' => $carrierCost,
                    'total_price_with_tax' => $carrierCost,
                    'position' => $carrier['position'],
                    'delay' => $carrier['delay'],
                    'selected' => false,
                );
            }
        }

        // Get delivery times if cached
        if (self::getConfig('ESTIMATES')) {
            $Cache = \CanadaPostPs\Cache::getByCartId($Cart->id);
            if (Validate::isLoadedObject($Cache)) {
                foreach ($carrierList as $id_carrier => $carrier) {
                    $CacheRate = \CanadaPostPs\CacheRate::getByCarrierId($Cache->id, $id_carrier);
                    if (Validate::isLoadedObject($CacheRate)) {
                        $days = $CacheRate->delay;
                        if ($days != null && $days > 0) {
                            // Add delay
                            if (self::getConfig('DELAY') && is_numeric(self::getConfig('DELAY')) && self::getConfig('DELAY') > 0) {
                                $days += self::getConfig('DELAY');
                            }
                            $carrierList[$id_carrier]['delay'] = $days . ' ' . ($days > 1 ? $this->l('Business days') : $this->l('Business day'));
                        }
                    }
                }
            }
        }

        // sort carriers
        uasort($carrierList, array('Cart', 'sortDeliveryOptionList'));

        // Set selected radio button
        if ($Cart->id_carrier && in_array($Cart->id_carrier, array_keys($carrierList))) {
            $carrierList[$Cart->id_carrier]['selected'] = true;
        }

        return $carrierList;
    }

    /**
     * @return string
     *
     * @throws Exception
     * @throws SmartyException
     */
    public function renderModal()
    {
        $modal_render = '';
        if (is_array($this->modals) && count($this->modals)) {
            foreach ($this->modals as $modal) {
                $this->context->smarty->assign($modal);
                $modal_render .= $this->context->smarty->fetch('modal.tpl');
            }
        }

        return $modal_render;
    }

    /**
     * Create dir hierarchy for labels formatted as YEAR/MONTH/DAY
     * Copy index.php to each new dir
     *
     * @var $DateTime DateTime
     * */
    public function makeLabelDirectoryForDate($DateTime, $path)
    {
        $dir = $path . $DateTime->format('Y/m/d');
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        $sourceIndexFile = _PS_MODULE_DIR_ . $this->name . '/index.php';
        $baseDir = $path . $DateTime->format('Y');
        $month = $DateTime->format('m');
        $day = $DateTime->format('d');

        if (!file_exists($baseDir . '/index.php')) {
            Tools::copy($sourceIndexFile, $baseDir . '/index.php');
        }
        if (!file_exists($baseDir . '/' . $month . '/index.php')) {
            Tools::copy($sourceIndexFile, $baseDir . '/' . $month . '/index.php');
        }
        if (!file_exists($baseDir . '/' . $month . '/' .  $day . '/index.php')) {
            Tools::copy($sourceIndexFile, $baseDir . '/' . $month . '/' . $day . '/index.php');
        }
    }

    /**
     * Add CSS and JS files to header
     */
    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('configure') != $this->name && !$this->active) {
            return false;
        }

        if (
            Tools::getValue('configure') == $this->name ||
            Tools::getValue('controller') == 'AdminOrders' ||
            in_array(Tools::getValue('controller'), $this->adminControllers)
        ) {

            // Add jQuery in older PS versions, it's automatically added in 1.7.7+
            if (version_compare(_PS_VERSION_, '1.7.7.0') < 0 &&
                method_exists($this->context->controller, 'addJquery')) {
                $this->context->controller->addJquery();
            }

            $this->context->controller->addJS(_MODULE_DIR_ . $this->name . '/views/js/back.js');
            $this->context->controller->addJS(_MODULE_DIR_ . $this->name . '/views/js/labelForm.js');
            $this->context->controller->addCSS(_MODULE_DIR_ . $this->name . '/views/css/back.css');
            $this->context->controller->addCSS(_MODULE_DIR_ . $this->name . '/views/css/labelForm.css');


            // Add material classes for styling on Symfony pages
            if (self::psVersionIsAtLeast('1.7.7') &&
                isset($this->context->container)) {
                $this->context->controller->addJS(_MODULE_DIR_ . $this->name . '/views/js/material-styles.js');
                $this->context->controller->addCSS(_MODULE_DIR_ . $this->name . '/views/css/material-styles.css');
            }

            if (Tools::getValue('configure') == $this->name) {
                Media::addJsDef(array(
                    'configUrl' => AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                ));
            }

            Media::addJsDef(array(
                'canadaPostCreateLabelControllerUrl' => $this->context->link->getAdminLink('AdminCanadaPostLabelsCreateLabel', true),
                'autoOpenLabel' => self::getConfig('OPEN_LABEL_ON_CREATION'),
            ));
        }
    }

    /**
     * Add CSS and JS files to header
     */
    public function hookDisplayHeader()
    {
        if (!$this->active) {
            return false;
        }

        if (self::getConfig('ESTIMATOR')) {
            $this->context->controller->addCSS(_MODULE_DIR_ . $this->name . '/views/css/front.css');
            $this->context->controller->addJS(_MODULE_DIR_ . $this->name . '/views/js/front.js');
            if (version_compare(_PS_VERSION_, '1.7.0.0') < 0) {
                $this->context->controller->addCSS(_MODULE_DIR_ . $this->name . '/views/css/front_16.css');
            }

            // Update cart carrier if selected
            if (Tools::isSubmit('submitChangeCarrier') && Tools::isSubmit('carrier')) {
                $this->processChangeCarrier();
            }

            Media::addJsDef(array(
                'updateCartLabel'            => $this->l('Select Carrier'),
                'updatingLabel'              => $this->l('Selecting...'),
                'moduleCarrierControllerUrl' => $this->context->link->getModuleLink(
                    $this->name,
                    'carrier',
                    array('secure_key' => $this->getSecureKey())
                ),
                'cartControllerUrl'          => $this->context->link->getPageLink(
                    'cart',
                    null,
                    $this->context->language->id,
                    array('action' => 'show')
                ),
                'isPsVersion16' => (version_compare(_PS_VERSION_, '1.7.0.0') < 0)
            ));
        }
    }

    public function processChangeCarrier()
    {
        $response = array();
        $deliveryOptionList = $this->context->cart->getDeliveryOptionList();
        if (count($deliveryOptionList) > 0) {
            foreach ($deliveryOptionList as $id_address => $optionList) {
                foreach ($optionList as $key => $option) {
                    if (in_array(Tools::getValue('carrier'), array_keys($option['carrier_list']))) {
                        $this->context->cart->setDeliveryOption(array($id_address => $key));
                        if ($this->context->cart->update()) {
                            if (!Tools::getIsset('ajax')) {
                                Tools::redirect(
                                    $this->context->link->getPageLink(
                                        'cart',
                                        null,
                                        $this->context->language->id,
                                        array('action' => 'show')
                                    )
                                );
                            } else {
                                $response['success'] = true;
                            }
                        } else {
                            $response['error'] = true;
                        }
                        break;
                    }
                }
            }
        } else {
            $response['error'] = true;
        }
        if (Tools::getIsset('ajax')) {
            die(json_encode($response));
        }
    }

    /*
        When a user edits a carrier in the back-office,
        the carrier gets deleted and a new one replaces it with a new ID.
        Make sure that our DB gets updated with the new ID.
    */
    public function hookActionCarrierUpdate($params)
    {
        if (!$this->isVerified() || !$this->isConnected()) {
            return false;
        }

        if ((int)($params['id_carrier']) != (int)($params['carrier']->id)) {
            $methods = Db::getInstance()->getRow('SELECT * FROM `' . _DB_PREFIX_ . CanadaPostPs\Method::$definition['table'] . '` WHERE `id_carrier` = ' . (int)$params['id_carrier']);

            $where   = '`id_carrier` = ' . (int)$params['id_carrier'];
            $history = (int)$methods['id_carrier_history'] . '|' . (int)$params['carrier']->id;
            Db::getInstance()->update(
                CanadaPostPs\Method::$definition['table'],
                array(
                    'id_carrier'         => (int)$params['carrier']->id,
                    'id_carrier_history' => $history,
                    'active'             => (int)$params['carrier']->active
                ),
                $where
            );

            // Update carrier mapping
            $carrierMappingArr = \CanadaPostPs\CarrierMapping::getCarrierMappingByCarrierId((int)$params['id_carrier']);
            if ($carrierMappingArr) {
                $CarrierMapping = new \CanadaPostPs\CarrierMapping($carrierMappingArr['id_carrier_mapping']);

                $CarrierMapping->id_carrier        = $params['carrier']->id;
                $CarrierMapping->save();
            }
        }
    }

    /*
     * Update methods DB on carrier status update
     * */
    public function hookActionAdminStatusAfter($params)
    {
        if (!$this->isVerified() || !$this->isConnected()) {
            return false;
        }

        if (is_a($params['controller'], 'AdminCarriersController')) {
            if (is_a($params['return'], 'Carrier')) {
                $where = '`id_carrier` = ' . (int)$params['return']->id;
                Db::getInstance()->update(
                    CanadaPostPs\Method::$definition['table'],
                    array('active' => (int)$params['return']->active),
                    $where
                );
            }
        }
    }

    /*
     * When user updates cart, deleted old rates information
     * */
    public function hookActionCartSave($params)
    {
        if (!$this->isVerified() || !$this->isConnected()) {
            return false;
        }

        if (is_object($params['cart'])) {
            $Cart = new Cart($params['cart']->id);

            if (Validate::isLoadedObject($Cart) && self::$clearCacheOnCartUpdate) {
                $Cache = \CanadaPostPs\Cache::getByCartId($Cart->id);
                if (Validate::isLoadedObject($Cache)) {
                    if (\CanadaPostPs\Cache::isCartUpdated($Cart)) {
                        $Cache->clearCacheRates();
                    }
                }
            }
        }
    }

    /**
     * Change carrier delay messages and display error before carrier list
     */
    public function hookDisplayBeforeCarrier($params)
    {
        if (!$this->isVerified() || !$this->isConnected()) {
            return false;
        }

        $errorArray = array();
        $methods    = array();

        // Load cached rate if it exists to display errors
        $Cache = CanadaPostPs\Cache::getByCartId($params['cart']->id);
        if (Validate::isLoadedObject($Cache)) {
            foreach ($Cache->rates as $rate) {
                if (!empty($rate['error_message']) && $rate['error_message'] != '') {
                    $Method                      = \CanadaPostPs\Method::getMethodByCode($rate['code']);
                    $errorArray[$Method['name']] = $rate['error_message'];
                }
                $methods[$rate['id_carrier']] = array(
                    'code'  => $rate['code'],
                    'delay' => $rate['delay'],
                );
            }

            // Get delivery time estimates
            if (empty($errorArray) && !empty($methods)) {

                if (version_compare(_PS_VERSION_, '1.7.0.0') >= 0) {
                    // Get array of eligible carriers
                    $deliveryOptionsFinder = new DeliveryOptionsFinder(
                        $this->context,
                        $this->getTranslator(),
                        $this->objectPresenter,
                        new PrestaShop\PrestaShop\Adapter\Product\PriceFormatter()
                    );
                    $delivery_options      = $deliveryOptionsFinder->getDeliveryOptions();

                    foreach ($delivery_options as $carrier) {
                        if (array_key_exists($carrier['id'], $methods)) {
                            $days = $methods[$carrier['id']]['delay'];

                            // Add delay
                            if (self::getConfig('DELAY') && is_numeric(self::getConfig('DELAY')) && self::getConfig('DELAY') > 0) {
                                $days += self::getConfig('DELAY');
                            }

                            if ($days != 0) {
                                $delivery_options[$carrier['id'] . ',']['delay'] = $days . ' ' . ($days > 1 ? $this->l('Business days.') : $this->l('Business day.'));
                            }
                        }
                    }
                    if (array_key_exists('delay_times', $this->context->smarty->tpl_vars) &&
                        is_array($this->context->smarty->tpl_vars['delay_times'])) {
                        $delivery_options = array_merge($this->context->smarty->tpl_vars['delay_times'],
                            $delivery_options);
                    }
                    $this->context->smarty->assign('delay_times', $delivery_options);
                } else {
                    if (isset($params['delivery_option_list'])) {
                        $option_list = $params['delivery_option_list'];
                        foreach ($option_list as $id_address => $carrier_list_raw) {
                            foreach ($carrier_list_raw as $key => $carrier_list) {
                                foreach ($carrier_list['carrier_list'] as $id_carrier => $carrier) {
                                    if (array_key_exists($id_carrier, $methods)) {
                                        $delay = &$option_list[$id_address][$key]['carrier_list'][$id_carrier]['instance']->delay;
                                        $days  = $methods[$id_carrier]['delay'];

                                        // Add delay
                                        if (self::getConfig('DELAY') && is_numeric(self::getConfig('DELAY')) && self::getConfig('DELAY') > 0) {
                                            $days += self::getConfig('DELAY');
                                        }

                                        if ($days != 0) {
                                            foreach ($delay as $k => $v) {
                                                $delay[$k] = $days . ' ' . ($days > 1 ? $this->l('Business days.') : $this->l('Business day.'));
                                            }
                                        }
                                    } else {
                                        continue;
                                    }
                                }
                            }
                        }
                        $this->context->smarty->assign('delivery_option_list', $option_list);
                    }
                }
            }
        }

        $this->context->smarty->assign(array(
            'errorArray' => $errorArray,
        ));

        return $this->display(__FILE__, 'before_carrier.tpl');
    }

    /**
     * Register a carrier in the back-office
     *
     * @param \CanadaPostPs\Method $Method
     */
    public function installCarrier($Method)
    {
        $config                     = array(
            'name'                 => 'Canada Post (' . $Method->name . ')',
            'id_tax_rules_group'   => 0,
            'active'               => true,
            'url'                  => \CanadaPostPs\Method::$tracking_url,
            'deleted'              => 0,
            'shipping_handling'    => false,
            'range_behavior'       => 0,
            'delay'                => array(
                'fr'                                                        => $Method->name,
                'en'                                                        => $Method->name,
                Language::getIsoById(Configuration::get('PS_LANG_DEFAULT')) => $Method->name
            ),
            'id_zone'              => 1,
            'is_module'            => true,
            'shipping_external'    => true,
            'external_module_name' => $this->name,
            'need_range'           => true
        );
        $id_carrier                 = $this->installExternalCarrier($config, $Method);
        $Method->id_carrier         = (int)$id_carrier;
        $Method->id_carrier_history = (int)$id_carrier;
        $Method->save();

        // Set up default carrier mapping
        $carrierMappingArr = \CanadaPostPs\CarrierMapping::getCarrierMappingByCarrierId($id_carrier);
        if ($carrierMappingArr) {
            $CarrierMapping = new \CanadaPostPs\CarrierMapping($carrierMappingArr['id_carrier_mapping']);
        } else {
            $CarrierMapping = new \CanadaPostPs\CarrierMapping();
        }
        $CarrierMapping->id_carrier = $id_carrier;
        $CarrierMapping->id_mapped_carrier = $Method->id;
        $CarrierMapping->save();
    }

    /**
     * Register a carrier in the back-office
     *
     * @param array $config
     * @param \CanadaPostPs\Method $Method
     */
    public static function installExternalCarrier($config, $Method)
    {
        $carrier                       = new Carrier();
        $carrier->name                 = $config['name'];
        $carrier->id_tax_rules_group   = $config['id_tax_rules_group'];
        $carrier->id_zone              = $config['id_zone'];
        $carrier->active               = $config['active'];
        $carrier->url                  = $config['url'];
        $carrier->deleted              = $config['deleted'];
        $carrier->delay                = $config['delay'];
        $carrier->shipping_handling    = $config['shipping_handling'];
        $carrier->range_behavior       = $config['range_behavior'];
        $carrier->is_module            = $config['is_module'];
        $carrier->shipping_external    = $config['shipping_external'];
        $carrier->external_module_name = $config['external_module_name'];
        $carrier->need_range           = $config['need_range'];

        $languages = Language::getLanguages(true);
        foreach ($languages as $language) {
            if ($language['iso_code'] == 'en') {
                $carrier->delay[(int)$language['id_lang']] = $config['delay'][$language['iso_code']];
            }
            if ($language['iso_code'] == Language::getIsoById(Configuration::get('PS_LANG_DEFAULT'))) {
                $carrier->delay[(int)$language['id_lang']] = $config['delay'][$language['iso_code']];
            }
        }

        if ($carrier->add()) {
            $groups = Group::getGroups(true);
            foreach ($groups as $group) {
                Db::getInstance()->insert(
                    'carrier_group',
                    array('id_carrier' => (int)($carrier->id), 'id_group' => (int)($group['id_group'])),
                    'INSERT'
                );
            }

            $rangePrice             = new RangePrice();
            $rangePrice->id_carrier = $carrier->id;
            $rangePrice->delimiter1 = '0';
            $rangePrice->delimiter2 = '10000';
            $rangePrice->add();

            $rangeWeight             = new RangeWeight();
            $rangeWeight->id_carrier = $carrier->id;
            $rangeWeight->delimiter1 = '0';
            $rangeWeight->delimiter2 = '10000';
            $rangeWeight->add();

            $countryCA = new Country(Country::getByIso('CA'));
            $countryUS = new Country(Country::getByIso('US'));

            $zones = Zone::getZones(true);
            foreach ($zones as $zone) {
                if ($Method->group == 'DOM' && $zone['id_zone'] != $countryCA->id_zone) {
                    continue;
                }
                if ($Method->group == 'USA' && $zone['id_zone'] != $countryUS->id_zone) {
                    continue;
                }
                Db::getInstance()->insert(
                    'carrier_zone',
                    array('id_carrier' => (int)($carrier->id), 'id_zone' => (int)($zone['id_zone'])),
                    'INSERT'
                );
                Db::getInstance()->insert('delivery', array(
                    'id_carrier'      => (int)($carrier->id),
                    'id_range_price'  => (int)($rangePrice->id),
                    'id_range_weight' => null,
                    'id_zone'         => (int)($zone['id_zone']),
                    'price'           => '0'
                ), 'INSERT');
                Db::getInstance()->insert('delivery', array(
                    'id_carrier'      => (int)($carrier->id),
                    'id_range_price'  => null,
                    'id_range_weight' => (int)($rangeWeight->id),
                    'id_zone'         => (int)($zone['id_zone']),
                    'price'           => '0'
                ), 'INSERT');
            }

            // Copy Logo
            if (self::getConfig('CARRIER_IMAGE')) {
                if (!Tools::copy(
                    dirname(__FILE__) . '/views/img/uploads/' . self::getConfig('CARRIER_LOGO_FILE'),
                    _PS_SHIP_IMG_DIR_ . '/' . (int)$carrier->id . '.jpg'
                )
                ) {
                    return false;
                }
            }

            // Return ID Carrier
            return (int)($carrier->id);
        }

        return false;
    }

    /* Set all installed Canada Post carriers to deleted */
    public function uninstallCarriers()
    {
        if (CanadaPostPs\Tools::tableExists(CanadaPostPs\Method::$definition['table'])) {
            $methods = CanadaPostPs\Method::getMethods();
            foreach ($methods as $m) {
                if ($m['id_carrier']) {
                    $carrier          = new Carrier($m['id_carrier']);
                    $carrier->deleted = 1;
                    $carrier->update();
                }
            }
        }

        return true;
    }

    /*
     * Retrieve and cache rates for all applicable carriers and return rate for current id_carrier
     *
     * @return bool|float
     * */
    public function getPackageShippingCost($params, $shipping_cost, $products)
    {
        if (!$this->isVerified() || !$this->isConnected()) {
            return false;
        }

        if (!$params->id_address_delivery && !$this->context->cookie->id_country) {
            return false;
        }

        $Rate = false;

        // Load cached rate if it exists
        $Cache = CanadaPostPs\Cache::getByCartId($params->id);
        if (Validate::isLoadedObject($Cache)) {
            $Rate = \CanadaPostPs\CacheRate::getByCarrierId($Cache->id, $this->id_carrier);

            // Return false if rate has an error to avoid re-fetching invalid rates
            // unless we're on a back-office page (manual order)
            if (Validate::isLoadedObject($Rate)) {
                if ($this->context->controller->controller_type == 'front' &&
                    $Rate->error_message && $Rate->error_message !== '') {
                    return false;
                }

                // Return cached rate
                if (is_numeric($Rate->rate)) {
                    return $this->calculateFinalRate($Rate, $shipping_cost);
                }
            }
        }

        $destinationAddress = new Address($params->id_address_delivery);
        $country            = new Country($destinationAddress->id_country);

        // If address is not loaded, we take data from shipping estimator module (if installed)
        if (!Validate::isLoadedObject($destinationAddress)) {
            $country                        = new Country($this->context->cookie->id_country);
            $destinationAddress->id_country = $this->context->cookie->id_country;
            $destinationAddress->id_state   = $this->context->cookie->id_state;
            $destinationAddress->postcode   = $this->context->cookie->postcode;
        }

        // Declare which group the customer belongs to (Domestic-Canada, USA or International)
        $group = \CanadaPostPs\Method::getMethodGroup($country->iso_code);

        // if requested method is not part of the requested group, exit
        $currentMethod = \CanadaPostPs\Method::getMethodByCarrierId($this->id_carrier);
        if (!$currentMethod || $currentMethod['group'] != $group) {
            return false;
        }

        // Get all shipping methods in the selected Group
        $methods = \CanadaPostPs\Method::getMethods(array('group' => $group, 'active' => 1));
        if ($methods && !empty($methods)) {

            // Store active service codes in array
            $serviceCodes = array();
            foreach ($methods as $m) {
                $serviceCodes[] = $m['code'];
            }

            // Because the Canada Post Rates API can retrieve multiple carrier rates at once,
            // we make sure that we only call the API once for all carriers even though
            // we are only returning a rate for one carrier.
            if (!Validate::isLoadedObject($Rate)) {
                $API = new \CanadaPostPs\API();

                // TODO - Use product dimensions if "USE_PRODUCT_DIMENSIONS" and cart contains only 1 product with 1 qty
                $packedBoxArr = $API->packProducts($products);
                $boxAmount    = count($packedBoxArr);
                $splitType    = self::getConfig('SPLIT_TYPE');

                $combineRates   = true;
                $ratesRetrieved = array();
                $senderAddress  = \CanadaPostPs\Address::getOriginAddress();


                // If box splitting is Off, we only need 1 box
                if ($splitType == self::SPLIT_TYPE_OFF) {
                    $boxAmount = 1;
                }

                // If box splitting is Off or Simple, we only need the largest box
                if ($splitType == self::SPLIT_TYPE_OFF ||
                    $splitType == self::SPLIT_TYPE_SIMPLE) {
                    $combineRates = false;
                    // Get the largest box
                    $packedBoxArr = array_slice(
                        $packedBoxArr,
                        -1,
                        1,
                        true
                    );
                }

                // Init cache for current cart
                $Cache = $API->cacheCart($params->id);

                if ($Cache) {
                    $Cache->clearCacheRates();

                    /* @var $PackedBox \BoxPacker\PackedBox */
                    foreach ($packedBoxArr as $PackedBox) {

                        /** @var \CanadaPostPs\Box $Box */
                        $Box    = $PackedBox->getBox();
                        $Box->convertDimensionsToUnit('cm');
                        $weight = \CanadaPostPs\Tools::convertUnitFromTo($PackedBox->getWeight(), 'g', 'kg', 3);

                        // Get rate
                        $Rating = $API->getRates($serviceCodes, $senderAddress, $destinationAddress, $Box, $weight);

                        // Cache rates
                        if ($Rating->isSuccess()) {
                            /* @var $Quote CanadaPostWs\Type\Rating\QuoteType */
                            foreach ($Rating->getResponse()->getQuotes() as $Quote) {
                                // Multiply rate by number of boxes
                                if ($splitType == self::SPLIT_TYPE_SIMPLE) {
                                    $Quote->setPriceTaxExcl($Quote->getPriceTaxExcl() * $boxAmount);
                                    $Quote->setPriceTaxIncl($Quote->getPriceTaxIncl() * $boxAmount);
                                }
                                $ratesRetrieved[] = $Quote->getServiceCode();
                                $tax              = self::getConfig('TAX') ? true : false;
                                $Cache->addRate($Quote, $tax, $combineRates);
                            }

                            // If certain methods didn't return a rate, cache them with $0 rate
                            // so they aren't fetched again and log the errors
                            $invalidMethods = array_diff($serviceCodes, $ratesRetrieved);
                            if (count($invalidMethods) > 0) {
                                $Cache->addRateError($invalidMethods, '');
                                $this->log(sprintf(
                                    \CanadaPostPs\Tools::$error_messages['RATES_NOT_RETURNED'],
                                    implode(', ', $invalidMethods),
                                    $params->id
                                ));
                            }
                        } else {
                            $Cache->addRateError($serviceCodes, $Rating->getErrorMessage());
                            $this->log('Error fetching rates from API: "' . $Rating->getErrorMessage() . '"');
                        }
                    }

                    // Reload Cache
                    $Cache = CanadaPostPs\Cache::getByCartId($params->id);
                    if (Validate::isLoadedObject($Cache)) {
                        $Rate = \CanadaPostPs\CacheRate::getByCarrierId($Cache->id, $this->id_carrier);
                    }
                } else {
                    return false;
                }
            }

            if (Validate::isLoadedObject($Rate)) {
                return $this->calculateFinalRate($Rate, $shipping_cost);
            }
        }

        return false;
    }

    public function getOrderShippingCost($params, $shipping_cost)
    {
        return $this->getPackageShippingCost($params, $shipping_cost);
    }

    public function getOrderShippingCostExternal($params)
    {
        return $this->getPackageShippingCost($params, 23);
    }

    /*
     * Convert rate from CAD to context currency, apply Rate Discounts, and add handling fees
     *
     * @param CanadaPostPs\CacheRate $Rate
     * @param float $shipping_cost
     *
     * @return float|bool
     * */
    public function calculateFinalRate($Rate, $shipping_cost)
    {
        $cost = 0;
        // Convert the price from CAD to the context currency
        if ($Rate->rate > 0 && empty($Rate->error_message)) {
            $CAD  = new Currency(Currency::getIdByIsoCode('CAD'));
            $cost = Tools::convertPriceFull((float)$Rate->rate, $CAD, $this->context->currency);

            $cost = \CanadaPostPs\RateDiscount::applyDiscountToRate($Rate, $cost);

            // Add handling fees
            $cost += $shipping_cost;

            if ($cost >= 0 && is_numeric($cost)) {
                return (float)$cost;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    /**
     * Use product dimensions if "USE_PRODUCT_DIMENSIONS" is true
     * and cart contains only 1 product with 1 qty
     *
     * @param $products
     *
     * @return bool|\CanadaPost\BoxPacker\PackedBoxList
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function getPackedBoxListUsingProductDimensions($products)
    {
        if (count($products) == 1 && self::getConfig('USE_PRODUCT_DIMENSIONS')) {

            $product = $products[0];

            $quantity = $product['quantity'];
            // If this is from an Order, get Cart Quantity instead
            if (array_key_exists('cart_quantity', $product)) {
                $quantity = $product['cart_quantity'];
            }

            if ($quantity == 1 && !$product['is_virtual']) {

                // Create a packed box list with a single custom box using the product dimensions as the size
                $ProductObject            = new Product($product['id_product']);
                $packedBoxList      = new \CanadaPost\BoxPacker\PackedBoxList();
                $ProductBox         = new \CanadaPostPs\Box();
                $ProductBox->length = $ProductObject->depth;
                $ProductBox->width  = $ProductObject->width;
                $ProductBox->height = $ProductObject->height;

                // Set dimensions/weights to non-zero value if zero
                foreach (array('length', 'width', 'height') as $dimension) {
                    $ProductBox->{$dimension} = $ProductBox->{$dimension} > 0 ? $ProductBox->{$dimension} : 0.1;
                }
                $ProductBox->weight = $ProductObject->weight > 0 ? $ProductObject->weight : 0.001;

                $packedBoxList->insert(new \CanadaPost\BoxPacker\PackedBox(
                    $ProductBox,
                    new \CanadaPost\BoxPacker\ItemList(),
                    0, 0, 0, 0, 0, 0, 0
                ));

                return $packedBoxList;

            } else {
                return false;
            }
        } else {
            return false;
        }
    }


    /**
     * @return object|\Symfony\Component\HttpFoundation\Session\Flash\FlashBag|null
     * @throws \Exception
     */
    public function getFlashBag()
    {
        if (!isset($this->flashBag)) {
            $this->flashBag = $this->context->container->get('session')->getFlashBag();
        }

        return $this->flashBag;
    }

    /**
     * @return object|\Symfony\Component\HttpFoundation\RequestStack|null
     * @throws \Exception
     */
    public function getRequestStack()
    {
        if (!isset($this->requestStack)) {
            $this->requestStack = $this->context->container->get('request_stack');
        }

        return $this->requestStack;
    }

    /**
     * Add flash messages to the session in PS 1.7.7+ with Symfony controllers.
     *
     * @param string $type
     * @param string|array $messages
     *
     * @return void
     * @throws \Exception
     */
    public function addFlash($type, $messages)
    {
        if (self::psVersionIsAtLeast('1.7.7') && isset($this->context->container)) {
            if (!is_array($messages)) {
                $messages = array($messages);
            }
            foreach ($messages as $message) {
                $this->getFlashBag()->add($type, $message);
            }
        }
    }

    /**
     * redirect to current base URI in PS 1.7.7+ with Symfony controllers.
     *
     * @return void
     * @throws \Exception
     */
    public function redirectToRequestUri()
    {
        if (self::psVersionIsAtLeast('1.7.7') && isset($this->context->container)) {

            $params = array();

            // Check if this is an order and get the individual order's URL
            if ($orderId = $this->getRequestStack()->getCurrentRequest()->attributes->get('orderId')) {
                $params = array('id_order' => $orderId, 'vieworder' => true);
            }

            $controllerLink = $this->getAdminLink(
                $this->context->controller->controller_name,
                true,
                array(),
                $params
            );

            \Tools::redirectAdmin($controllerLink);
        }
    }

    /**
     * Add a flash message to the session and redirect to current URI
     * in PS 1.7.7+ with Symfony controllers.
     *
     * @param string $type
     * @param string|array $messages
     *
     * @return void
     * @throws \Exception
     */
    public function redirectWithFlash($type, $messages)
    {
        if (self::psVersionIsAtLeast('1.7.7') && isset($this->context->container)) {
            $this->addFlash($type, $messages);
            $this->redirectToRequestUri();
        }
    }

    /**
     * Checks if current version is equal or greater than the given version
     *
     * @param string $version e.g. 1.7.7
     * @return bool
     */
    public static function psVersionIsAtLeast($version)
    {
        return version_compare(_PS_VERSION_, $version) >= 0;
    }
}
