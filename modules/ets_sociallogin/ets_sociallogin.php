<?php
/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */

use Symfony\Component\Validator\Constraints\Valid;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(dirname(__FILE__) . '/classes/src/autoload.php');
require_once(dirname(__FILE__) . '/classes/Solo_translate.php');
require_once(dirname(__FILE__) . '/classes/Solo_user.php');
require_once(dirname(__FILE__) . '/classes/Solo_connect.php');
require_once(dirname(__FILE__) . '/classes/Solo_defines.php');
require_once(dirname(__FILE__) . '/classes/Solo_presenter.php');

class Ets_sociallogin extends Module
{
    public static $trans = array();
    public $pos_default = '_TRP';
    public $baseAdminPath = '';
    public $is17 = false;
    public $ps1760 = false;
    public $is16 = false;
    private $errorMessage = '';
    private $_html = '';
    protected $list_id = '';
    protected $_filter;
    protected $_filterHaving;
    public $secure_key;
    public $author_address;
    public $errors = [];
    public static $document_link = 'https://demo2.presta-demos.com/docs/sociallogin/';

    public function __construct()
    {
        $this->name = 'ets_sociallogin';
        $this->tab = 'front_office_features';
        $this->version = '2.6.8';
        $this->author = 'PrestaHero';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->module_key = 'b5a65079dc8b8c3a2af9359a8ca59963';
        $this->author_address = '0xd81C21A85a637315C623D9c1F9D4f5Bb3144A617';
        parent::__construct();
        $this->displayName = $this->l('SOCIAL LOGIN');
        $this->description = $this->l('Login or register new account using 32+ payment gateways and social networks such as Amazon, Paypal, Facebook, Google, X, Linked In, etc.'); //Twitter
        if (version_compare(_PS_VERSION_, '1.5.6.0', '>') || version_compare(_PS_VERSION_, '1.5.6.0', '<'))
            $this->ps_versions_compliancy = array('min' => '1.5.6.0', 'max' => _PS_VERSION_);
        $this->translates();
        $this->list_id = Solo_user::$definition['table'];
        if (version_compare(_PS_VERSION_, '1.7', '>='))
            $this->is17 = true;
        elseif (version_compare(_PS_VERSION_, '1.6', '>='))
            $this->is16 = true;
        $this->ps1760 = version_compare(_PS_VERSION_, '1.7.6.0', '>=');
        if (Tools::isSubmit('ajax') && Tools::getValue('action') == 'updatePreview' && Tools::getValue('configure') == $this->name) {
            $default = Tools::getValue('default');
            if ($default && !is_array($default) && !Validate::isCleanHtml($default)) {
                $default = '';
            }
            die(json_encode(array(
                'html' => $this->hookDisplaySoloPreview(array('default' => $default)),
            )));
        }
    }

    public static function getCallbackUrl($context, $isMultiLang = false)
    {
        if (!$isMultiLang) {
            return $context->link->getModuleLink('ets_sociallogin', 'callback', array(), (bool)Configuration::get('PS_SSL_ENABLED'), $context->language->id);
        } else {
            $languages = Language::getLanguages();
            $urls = array();
            foreach ($languages as $lang) {
                $urls[$lang['id_lang']] = $context->link->getModuleLink('ets_sociallogin', 'callback', array(), (bool)Configuration::get('PS_SSL_ENABLED'), $lang['id_lang']);
            }
            return $urls;
        }
    }

    public function setOrderCurrency($echo, $tr)
    {
        if ($echo)
            return $this->callBack(array(
                'value' => Tools::displayPriceSmarty(['price' => $echo, 'currency' => $this->context->currency->id], $this->context->smarty),
                'tr' => $tr,
                'type' => 'badge',
                'badgeType' => 'success'
            ));
        return '--';
    }

    public function displayError($errors)
    {
        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $this->smarty->assign('errors', $errors);
            return $this->display(__FILE__, 'errors.tpl');
        } else {
            return parent::displayError($errors);
        }
    }

    public function getConfigs($params = array())
    {
        $is_sort = isset($params['is_sort']) && $params['is_sort'] ? 1 : 0;
        $enabled = isset($params['enabled']) && $params['enabled'] ? 1 : 0;
        $pos = isset($params['pos']) && $params['pos'] ? $this->formatPos($params['pos']) : $this->pos_default;
        $socials = Solo_defines::getInstance($this->context, $this)->getFields('socials');
        $networks_order = Configuration::get('ETS_SOLO_NETWORKS_ORDER' . $pos);
        if (trim($networks_order) == '')
            $networks_order = Solo_defines::NETWORK_ORDER;
        $networks_orders = $pos && $networks_order ? explode(',', $networks_order) : $socials;
        $providers = $disabled = array();
        if ($networks_orders) {
            foreach ($networks_orders as $key => $val) {
                if ($networks_order) {
                    $key = $val;
                }
                if (isset($socials[$key]['label']) && $socials[$key]['label'] && (int)Configuration::get('ETS_SOLO_' . Tools::strtoupper($socials[$key]['label']) . '_ENABLED') > 0) {
                    $social = 'ETS_SOLO_' . Tools::strtoupper($socials[$key]['label']);
                    $config = array(
                        'id' => Configuration::get($social . '_APP_ID'),
                        'secret' => Configuration::get($social . '_APP_SECRET')
                    );
                    if (Configuration::get($social . '_APP_KEY') !== false) {
                        $config['key'] = Configuration::get($social . '_APP_KEY');
                    }
                    $providers[($is_sort ? $key : $socials[$key]['label'])] = $is_sort ? $key : array(
                        'enabled' => true,
                        'keys' => $config,
                    );
                } else
                    $disabled[$key] = $val;
            }
        }
        if ($is_sort) {
            return ($enabled || !$disabled ? $providers : array_merge($providers, $disabled));
        }
        return array(
            'callback' => self::getCallbackUrl($this->context),
            'providers' => $providers
        );
    }

    public function smarty_back_end($args)
    {
        if (!(isset($this->context->employee->id)) || !isset($this->context->employee->id) || (!(isset($args['key'])) || !$args['key']))
            return false;
        $this->smarty->assign($args);
        return $this->display(__FILE__, 'smarty_back_end.tpl');
    }

    public function smarty_front_end($args)
    {
        if (!(isset($args['key'])) || !$args['key'])
            return false;
        $this->smarty->assign($args);
        return $this->display(__FILE__, 'smarty_front_end.tpl');
    }


    public function callBack($params)
    {
        if (empty($params) || empty($params['type']))
            return null;
        if ($params['type'] == 'link') {
            $this->smarty->assign('link', isset($params['tr']['id_customer']) ? $this->context->link->getAdminLink('AdminCustomers', true, $this->ps1760 ? ['route' => 'admin_customers_view', 'customerId' => (int)$params['tr']['id_customer']] : [], ['viewcustomer' => '', 'id_customer' => (int)$params['tr']['id_customer']]) : null);
        } elseif ($params['type'] == 'img') {
            $this->smarty->assign('img_base_dir', $this->_path);
        }
        $this->smarty->assign($params);
        return $this->display(__FILE__, 'callback.tpl');
    }

    public function translates()
    {
        self::$trans = array(
            'required_text' => $this->l('is required'),
            'data_saved' => $this->l('Saved'),
            'unknown_error' => $this->l('Unknown error happens'),
            'object_empty' => $this->l('Object is empty'),
            'field_not_valid' => $this->l('Field is not valid'),
            'file_too_large' => $this->l('Upload file cannot be larger than 100MB'),
            'file_existed' => $this->l('File name already exists. Try to rename the file and upload again'),
            'can_not_upload' => $this->l('Cannot upload file'),
            'upload_error_occurred' => $this->l('An error occurred during the image upload process.'),
            'image_deleted' => $this->l('Image deleted'),
            'item_deleted' => $this->l('Item deleted'),
            'cannot_delete' => $this->l('Cannot delete the item due to an unknown technical problem'),
            'invalid_text' => $this->l('is invalid'),

            'content_required_text' => $this->l('Text content is required'),
            'link_required_text' => $this->l('Link is required'),
            'image_required_text' => $this->l('Image is required'),

            'provider_required' => $this->l('Provider is required'),
            'email_required' => $this->l('Email is required'),
            'email_invalid' => $this->l('Invalid email address'),
            'password_invalid' => $this->l('Password is required'),
            'password_required' => $this->l('Invalid password'),
            'account_available' => $this->l('Your account is not available at this time, please contact us'),
            'authentication_failed' => $this->l('Authentication failed.'),
        );
    }

    public function _installMailTemplate()
    {
        $languages = Language::getLanguages(false);
        if ($languages && is_array($languages)) {
            $temp_dir_ltr = dirname(__FILE__) . '/mails/en';
            $temp_dir_rtl = dirname(__FILE__) . '/mails/he';

            if (!@file_exists($temp_dir_ltr) || !@file_exists($temp_dir_rtl))
                return true;
            foreach ($languages as $language) {
                if (isset($language['iso_code']) && ($language['iso_code'] != 'en' || $language['iso_code'] != 'he')) {
                    $new_dir = dirname(__FILE__) . '/mails/' . $language['iso_code'];
                    if (is_dir(dirname($new_dir))) {
                        $this->recurseCopy(($language['is_rtl'] ? $temp_dir_rtl : $temp_dir_ltr), $new_dir);
                    }
                }
            }
        }
        return true;
    }

    public function recurseCopy($src, $dst)
    {
        if (!@file_exists($src))
            return false;
        $dir = opendir($src);
        if (!@is_dir($dst))
            @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->recurseCopy($src . '/' . $file, $dst . '/' . $file);
                } elseif (!@file_exists($dst . '/' . $file)) {
                    @copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    public function _registerHook()
    {
        $_hooks = array(
            'displayCustomerAccount',
            'displayBackOfficeHeader',
            'displaySoloPreview',
            'actionCustomerLogoutAfter',
            'displaySoloOnPage',
            'displayLeftColumn',
            'displayTop',
        );
        if ($hooks = Solo_defines::getInstance($this->context, $this)->getFields('hooks')) {
            foreach ($hooks as $hook)
                if (isset($hook['hook']) && $hook['hook'])
                    $this->registerHook($hook['hook']);
        }
        foreach ($_hooks as $hook) {
            $this->registerHook($hook);
        }
        return true;
    }

    public function _default($pos, $default)
    {
        if (!$pos)
            return $default;
        if (is_array($default) && $default) {
            foreach ($default as $k => $v) {
                if ($k == $pos)
                    return $v;
            }
            return isset($default[$this->pos_default]) && $default[$this->pos_default] ? $default[$this->pos_default] : '';
        }
        return $default;
    }

    public function _configs($configs, $upgrade, $languages, $pos = '')
    {
        if ($configs) {
            foreach ($configs as $key => $config) {
                $key .= (isset($config['common']) && $config['common'] ? '' : $pos);
                if (isset($config['lang']) && $config['lang']) {
                    $values = array();
                    foreach ($languages as $lang) {
                        $values[$lang['id_lang']] = isset($config['default']) ? $this->_default($pos, $config['default']) : '';
                    }
                    if (!Configuration::hasKey($key) || !$upgrade)
                        Configuration::updateValue($key, $values, true);
                } elseif (!Configuration::hasKey($key) || !$upgrade)
                    Configuration::updateValue($key, isset($config['default']) ? $this->_default($pos, $config['default']) : '', true);
            }
        }
        return true;
    }

    public function _installConfigs($upgrade = false)
    {
        $languages = Language::getLanguages(false);
        $init = Solo_defines::getInstance($this->context, $this);
        if (($configTabs = $init->getFields('tabs'))) {
            $hooks = array();
            foreach ($configTabs as $tab) {
                if (!($tab_name = $tab['name'] ? $tab['name'] : false))
                    continue;
                $result = $this->inTabs($tab['name']) ? $init->getFields($tab_name) : array();
                if (isset($result['configs']) && ($configs = $result['configs'])) {
                    if ($tab_name != 'design') {
                        $this->_configs($configs, $upgrade, $languages);
                    } elseif ($hooks || ($hooks = $init->getFields('hooks'))) {
                        foreach ($hooks as $hook) {
                            if ($hook['id_option'] != '' && $hook['init']) {
                                $this->_configs($configs, $upgrade, $languages, $this->formatPos($hook['id_option']));
                            }
                        }
                    }
                }
            }
        }
        if (!$upgrade) {
            Configuration::updateValue('ETS_SOLO_INSTALL_DATE', date('Y-m-d H:i:s'));
        }
        return true;
    }

    public function installDefaultConfig()
    {
        $configs = Solo_defines::getInstance($this->context, $this)->getFields('design');
        if ($configs && is_array($configs)) {
            $languages = Language::getLanguages(false);
            foreach ($configs as $key => $config) {
                if (isset($config['default']) && $config['default']) {
                    if (isset($config['lang']) && $config['lang']) {
                        $value = array();
                        foreach ($languages as $lang) {
                            if (!is_array($config['default']) && Validate::isCleanHtml($config['default']))
                                $value[$lang['id_lang']] = $config['default'];
                            elseif (is_array($config['default'])) {
                                foreach ($config['default'] as $text) {
                                    if ($text && Validate::isCleanHtml($text)) {
                                        $value[$lang['id_lang']] = $text;
                                        break;
                                    }
                                }
                            }
                        }
                        Configuration::updateValue($key, $value);
                    } else {
                        if (!is_array($config['default']) && Validate::isCleanHtml($config['default']))
                            Configuration::updateValue($key, $config['default']);
                        elseif (is_array($config['default'])) {
                            foreach ($config['default'] as $text) {
                                if ($text && Validate::isCleanHtml($text)) {
                                    Configuration::updateValue($key, $text);
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }
        return true;
    }

    public function install()
    {
        require dirname(__FILE__) . '/sql/install.php';

        return parent::install()
            && $this->_registerHook()
            && $this->_installConfigs()
            && $this->installDefaultConfig()
            && $this->_installMailTemplate();
    }

    public function uninstall()
    {
        require dirname(__FILE__) . '/sql/uninstall.php';

        return parent::uninstall() && $this->_uninstallConfigs();
    }

    public function del_configs($configs, $pos = '')
    {
        if ($configs) {
            foreach ($configs as $key => $config) {
                $key .= $pos;
                Configuration::deleteByName($key);
                unset($config);
            }
        }
    }

    public function _uninstallConfigs()
    {
        $init = Solo_defines::getInstance($this->context, $this);
        if (($configTabs = $init->getFields('tabs'))) {
            $hooks = array();
            foreach ($configTabs as $tab) {
                if (!($tab_name = $tab['name'] ? $tab['name'] : false))
                    continue;
                $result = $this->inTabs($tab['name']) ? $init->getFields($tab_name) : array();
                if (isset($result['configs']) && ($configs = $result['configs'])) {
                    if ($tab_name != 'design') {
                        $this->del_configs($configs);
                    } elseif ($hooks || ($hooks = $init->getFields('hooks'))) {
                        foreach ($hooks as $hook)
                            $this->del_configs($configs, $this->formatPos($hook['id_option']));
                    }
                }
            }
        }
        Configuration::deleteByName('ETS_SOLO_INSTALL_DATE');
        return true;
    }

    public function inTabs($tab_name)
    {
        if ($configTabs = Solo_defines::getInstance($this->context, $this)->getFields('tabs'))
            foreach ($configTabs as $configTab) {
                if ($configTab['name'] == $tab_name && !in_array($tab_name, array('dashboard', 'statistic', 'social_users', 'help')))
                    return true;
            }
        return false;
    }

    public function isUsingNewTranslationSystem()
    {
        return false;
    }

    public function getContent()
    {
        if (!$this->active)
            return self::displayText($this->l('Module has been disabled. You need to enable the module before continuing with its configuration.'), 'p', ['class' => 'alert alert-warning']);
        $tab = ($tab = Tools::strtolower(trim(Tools::getValue('tabActive', 'dashboard')))) && Validate::isCleanHtml($tab) ? $tab : '';
        if ($tab == 'dashboard')
            $this->list_id = Solo_connect::$definition['table'];
        $this->postProcess();
        if ($this->errorMessage)
            $this->_html .= $this->errorMessage;
        if ($this->inTabs($tab)) {
            $params = array('config' => $tab);
            if (($pos = Tools::getValue('pos', ($tab != 'design' ? false : $this->pos_default))) && Validate::isCleanHtml($pos))
                $params['pos'] = $pos;
            $this->renderForm($params);
        } elseif ($tab == 'social_users') {
            $this->renderList(
                array(
                    'fields_list' => Solo_defines::getInstance($this->context, $this)->getFields('fields_list'),
                    'title' => $this->l('Social Users'),
                    'actions' => version_compare(_PS_VERSION_, '1.6', '>=') ? array('views') : array('view'),
                    'orderBy' => 'date_add',
                    'orderWay' => 'DESC',
                    'nb' => 'getUsers',
                    'list' => 'getUsers',
                    'no_link' => true,
                    'bulk_actions' => false,
                    'tab' => 'social_users',
                    'toolbar_btn' => true
                )
            );
        } elseif ($tab == 'dashboard') {
            $this->dashboard();
        } elseif ($tab == 'statistic') {
            $this->statistic();
        }
        $assign = array(
            'base_admin_url' => $this->baseAdminUrl(),
            'html_content' => $this->_html,
            'tabActive' => $tab,
            'base_dir' => $this->_path,
            'configTabs' => Solo_defines::getInstance($this->context, $this)->getFields('tabs'),
            'is15' => !$this->is16 && !$this->is17 ? 1 : 0,
            'refsLink' => false,
        );
        $this->smarty->assign($assign);
        return $this->display(__FILE__, 'admin-form.tpl');
    }

    public function getStatSocial($params = array())
    {
        if ($socials = Solo_defines::getInstance($this->context, $this)->getFields('socials')) {
            $return = isset($params['return']) && $params['return'] ? 1 : 0;
            $social_enabled = array();
            foreach ($socials as $key => $social) {
                if (!empty($socials[$key]['label']) && (int)Configuration::get('ETS_SOLO_' . Tools::strtoupper($socials[$key]['label']) . '_ENABLED') > 0)
                    $social_enabled[$key] = $social;
            }
            if ($social_enabled) {
                $reg_result = Solo_user::getUserWithParams($params, $this->context);
                $con_result = Solo_connect::getConnectWithParams($params, $this->context);
                $dashboards = array();
                if ($return) {
                    $con_series = $reg_series = $labels = array();
                }
                foreach ($social_enabled as $key => $social) {
                    if ($return) {
                        $each_reg = $each_con = 0;
                        $labels[] = $social['name'];
                    } else {
                        $dashboards[$key] = array(
                            'name' => $social['name'],
                            'label' => $social['label'],
                            'con_total' => 0,
                            'reg_total' => 0
                        );
                    }
                    //register bar.
                    if ($reg_result) {
                        foreach ($reg_result as $reg) {
                            if ($key == $reg['network']) {
                                if ($return)
                                    $each_reg = (int)$reg['reg_total'];
                                else
                                    $dashboards[$key]['reg_total'] = $reg['reg_total'];
                                break;
                            }
                        }
                    }
                    //connection bar.
                    if ($con_result) {
                        foreach ($con_result as $con) {
                            if ($key == $con['last_login_type']) {
                                if ($return)
                                    $each_con = (int)$con['con_total'];
                                else
                                    $dashboards[$key]['con_total'] = $con['con_total'];
                                break;
                            }
                        }
                    }
                    if ($return && isset($each_reg) && isset($each_con)) {
                        $reg_series[] = $each_reg;
                        $con_series[] = $each_con;
                    }
                }
                if ($return && isset($labels) && isset($con_series) && isset($reg_series)) {
                    $horizontalBar = array();
                    $horizontalBar[] = array(
                        'label' => $this->l('Social connections'),
                        "backgroundColor" => '#ef7d31',
                        "borderColor" => '#ef7d31',
                        "borderWidth" => 1,
                        "barThickness" => 5,
                        'data' => $con_series,
                    );
                    $horizontalBar[] = array(
                        'label' => $this->l('Social registrations'),
                        "backgroundColor" => '#5a9ad6',
                        "borderColor" => '#5a9ad6',
                        "borderWidth" => 1,
                        "barThickness" => 5,
                        'data' => $reg_series,
                    );
                    return array(
                        'labels' => $labels,
                        'datasets' => $horizontalBar
                    );
                }
                $this->smarty->assign(array(
                    'social_networks' => $dashboards,
                    'path' => $this->_path,
                ));
            }
        }
    }

    public function dashboard()
    {
        $slMonth = (int)date('m');
        $slYear = (int)date('Y');
        $assign = array(
            'total_dashboard' => array(
                array(
                    'id' => 'connections',
                    'label' => $this->l('Social connections'),
                    'total' => Solo_connect::getTotalConnections(),
                    'link' => $this->baseAdminUrl() . '&tabActive=statistic',
                ),
                array(
                    'id' => 'registrations',
                    'label' => $this->l('Social registrations'),
                    'total' => Solo_user::getTotalRegistrations($this->context),
                    'link' => $this->baseAdminUrl() . '&tabActive=social_users',
                ),
                array(
                    'id' => 'code_generated',
                    'label' => $this->l('Voucher codes generated'),
                    'total' => Solo_user::getTotalVoucherGenerated($this->context),
                    'link' => $this->context->link->getAdminLink('AdminCartRules', true)
                ),
                array(
                    'id' => 'code_used',
                    'label' => $this->l('Voucher codes used'),
                    'total' => Solo_user::getTotalVoucherUsed($this->context),
                    'link' => $this->context->link->getAdminLink('AdminCartRules', true)
                )
            ),
            'time_ranges' => array(
                array(
                    'label' => $this->l('Month'),
                    'month' => (int)$slMonth,
                    'year' => (int)$slYear,
                    'id' => 'month',
                ),
                array(
                    'label' => $this->l('Month - 1'),
                    'month' => (int)$slMonth <= 1 ? 12 : (int)$slMonth - 1,
                    'year' => (int)$slMonth <= 1 ? (int)$slYear - 1 : (int)$slYear,
                    'id' => 'month-1',
                ),
                array(
                    'label' => $this->l('Year'),
                    'month' => 0,
                    'year' => (int)$slYear,
                    'id' => 'year',
                ),
                array(
                    'label' => $this->l('Year - 1'),
                    'month' => 0,
                    'year' => (int)$slYear - 1,
                    'id' => 'year-1',
                ),
                array(
                    'label' => $this->l('All'),
                    'month' => 0,
                    'year' => 0,
                    'id' => 'all',
                ),
            ),
        );
        $assign = array_merge($assign, $this->dashboardChart(0, 0));
        //custom fields.
        $this->getStatSocial();
        $assign['last_login'] = $this->dashboardList();
        $this->smarty->assign($assign);
        $this->_html .= $this->display(__FILE__, 'dashboard.tpl');
    }

    public function dashboardList()
    {
        if ($fields = Solo_defines::getInstance($this->context, $this)->getFields('fields_list')) {
            foreach ($fields as &$field) {
                $field['search'] = false;
                $field['orderby'] = false;
            }
        }
        return $this->renderList(
            array(
                'fields_list' => $fields,
                'title' => '',
                'actions' => version_compare(_PS_VERSION_, '1.6', '>=') ? array('views') : array('view'),
                'orderBy' => 'su.last_login_time',
                'orderWay' => 'DESC',
                'nb' => 'getUsers',
                'list' => 'getUsers',
                'no_link' => true,
                'bulk_actions' => false,
                'tab' => 'dashboard',
                'limit' => 10,
                'return' => true,
                'toolbar_btn' => false,
                'show_toolbar' => false,
                'simple_header' => true,
            )
        );
    }

    public $y_max_value = 0;

    public function dashboardChart($slMonth, $slYear)
    {
        if ($slYear < 0)
            $slYear = date('Y');
        if ($slMonth < 0)
            $slMonth = date('m');
        $dataSets = $this->getDataCharts(array(
            'chart' => 'line',
            'month' => (int)$slMonth,
            'year' => (int)$slYear,
            'chart_js' => 1,
        ));
        $each = array();
        $format = 'Y';
        return array(
            'datasets' => $dataSets,
            'labels' => $this->timeSeries(1, (int)$slMonth, (int)$slYear, $each, $format),
            'title' => $this->l('Traffic: ') . ($slMonth && ($months = Tools::dateMonths()) && !empty($months[$slMonth]) ? ' ' . $months[$slMonth] : '') . ' ' . ($slYear ? $slYear : $this->l('All')),
            'labelX' => $format != 'm' ? $this->l('Day') : $this->l('Month'),
            'labelY' => $this->l('Count'),
            'y_max_value' => (int)$this->getMaxY($this->y_max_value),
        );
    }

    public function assignJsConfig($params)
    {
        if (!(isset($params['model'])) || !$params['model']) {
            return false;
        }
        $isJs = isset($params['isJs']) && $params['isJs'] ? true : false;
        $pos = isset($params['pos']) && $params['pos'] ? $this->formatPos($params['pos']) : '';
        if ($pos && !$this->is_config($pos))
            $pos = $this->pos_default;
        $configs = array();
        $definition = $params['model'];
        if (is_array($definition)) {
            foreach ($definition as $key => $val) {
                if (isset($val['design']) && !in_array($pos, $val['design']))
                    continue;
                $value = Configuration::get($key . (isset($val['common']) && $val['common'] ? '' : $pos), (isset($val['lang']) && $val['lang'] ? $this->context->language->id : null));
                if (isset($val['multiple']) && $val['multiple'])
                    $value = explode(',', $value);
                $configs[$key] = $isJs ? array(
                    'value' => $value,
                    'type' => isset($val['jsType']) && $val['jsType'] ? $val['jsType'] : 'int',
                ) : $value;
            }
        }
        if (isset($configs['ETS_SOLO_NETWORKS_ORDER']) && trim($configs['ETS_SOLO_NETWORKS_ORDER']) == '')
            $configs['ETS_SOLO_NETWORKS_ORDER'] = Solo_defines::NETWORK_ORDER;
        if ($isJs)
            $this->smarty->assign(array('ETS_SOLO_CONFIG' => $configs));
        return $configs;
    }

    public function is_config($pos)
    {
        if (!$pos || (Configuration::hasKey('ETS_SOLO_NETWORKS_ORDER' . $pos, null, $this->context->shop->id_shop_group, $this->context->shop->id) || Configuration::hasKey('ETS_SOLO_NETWORKS_ORDER' . $pos)))
            return true;
        return false;
    }

    public function renderForm($params)
    {
        if (!(isset($params['config'])))
            return false;
        $config_form = Solo_defines::getInstance($this->context, $this)->getFields($params['config']);
        $fields_form = array();
        $fields_form['form'] = $config_form['form'];
        if (!$this->is16 && !$this->is17 && $params['config'] == 'social_networks' && isset($fields_form['form']['submit']))
            unset($fields_form['form']['submit']);
        $configs = $config_form['configs'];
        $pos = isset($params['pos']) && $params['pos'] ? $this->formatPos($params['pos']) : '';
        if ($configs) {
            foreach ($configs as $key => $config) {
                if (!(isset($config['common'])) || !isset($config['common']))
                    $key .= $pos;
                $confFields = array(
                    'name' => $key,
                    'type' => $config['type'],
                    'label' => $config['label'],
                    'desc' => isset($config['desc']) ? $config['desc'] : false,
                    'required' => isset($config['required']) && $config['required'] ? true : false,
                    'autoload_rte' => isset($config['autoload_rte']) && $config['autoload_rte'] ? true : false,
                    'options' => isset($config['options']) && $config['options'] ? $config['options'] : array(),
                    'multiple' => isset($config['multiple']) && $config['multiple'],
                    'img_dir' => isset($config['img_dir']) && $config['img_dir'] ? $config['img_dir'] : false,
                    'form_group_class' => isset($config['form_group_class']) ? $config['form_group_class'] : '',
                    'values' => $config['type'] == 'switch' ? array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ) : (isset($config['values']) && $config['values'] ? $config['values'] : false),
                    'lang' => isset($config['lang']) ? $config['lang'] : false,
                    'col' => isset($config['col']) ? $config['col'] : '9',
                    'common' => isset($config['common']) ? $config['common'] : false,
                    'default' => isset($config['default']) ? $config['default'] : false,
                );
                if (isset($config['suffix']))
                    $confFields['suffix'] = $config['suffix'];
                if (isset($config['cols']))
                    $confFields['cols'] = $config['cols'];
                if (isset($config['rows']))
                    $confFields['rows'] = $config['rows'];
                if (isset($config['group']))
                    $confFields['group'] = $config['group'];
                if (isset($config['size']))
                    $confFields['size'] = $config['size'];
                if (!$confFields['multiple']) {
                    unset($confFields['multiple']);
                } elseif ($config['type'] == 'select' && stripos($confFields['name'], '[]') === false) {
                    $confFields['name'] .= '[]';
                }
                if ((isset($config['enabled']) && $config['enabled']) || (isset($config['design']) && in_array($pos, $config['design'])) || (!(isset($config['enabled'])) && !(isset($config['design'])))) {
                    $fields_form['form']['input'][] = $confFields;
                }
            }
        }
        $language = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper = new HelperForm();
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->table = $this->table;
        $helper->default_form_language = $language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'save' . Tools::ucfirst($fields_form['form']['name']);
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name . $this->getUrlParams();
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->override_folder = '/';
        $helper->show_cancel_button = false;
        $fields = array();
        $languages = Language::getLanguages(false);
        if (Tools::isSubmit('save' . Tools::ucfirst(trim($config_form['form']['name'])))) {
            if ($configs) {
                foreach ($configs as $key => $config) {
                    if (isset($config['design']) && !in_array($pos, $config['design']))
                        continue;
                    $key .= (isset($config['common']) && $config['common'] ? '' : $pos);
                    if (isset($config['lang']) && $config['lang']) {
                        foreach ($languages as $l) {
                            $fields[$key][$l['id_lang']] = ($val = Tools::getValue($key . '_' . $l['id_lang'], isset($config['default']) ? $this->_default($pos, $config['default']) : '')) && Validate::isCleanHtml($val) ? $val : '';
                        }
                    } elseif (($config['type'] == 'select' && isset($config['multiple']) && $config['multiple']) || $config['type'] == 'solo_group') {
                        $fields[$key . ($config['type'] == 'select' ? '[]' : '')] = Tools::getValue($key, array());
                        foreach ($fields[$key . ($config['type'] == 'select' ? '[]' : '')] as $ki => $ii) {
                            if (!is_array($ii) && !Validate::isCleanHtml($ii)) {
                                unset($fields[$key . ($config['type'] == 'select' ? '[]' : '')][$ki]);
                            }
                        }
                    } else {
                        $fields[$key] = ($val = Tools::getValue($key, (isset($config['default']) ? $this->_default($pos, $config['default']) : ''))) && Validate::isCleanHtml($val) ? $val : '';
                    }
                }
            }
        } else {
            if ($configs) {
                $pos_default = $this->is_config($pos) ? $pos : $this->pos_default;
                foreach ($configs as $key => $config) {
                    if (isset($config['design']) && !in_array($pos, $config['design']))
                        continue;
                    $key2 = $key . (isset($config['common']) && $config['common'] ? '' : $pos_default);
                    $key .= (isset($config['common']) && $config['common'] ? '' : $pos);
                    if (isset($config['lang']) && $config['lang']) {
                        foreach ($languages as $l) {
                            $fields[$key][$l['id_lang']] = Configuration::get($key2, $l['id_lang']);
                        }
                    } elseif (($config['type'] == 'select' && isset($config['multiple']) && $config['multiple']) || $config['type'] == 'solo_group') {
                        $fields[$key . ($config['type'] == 'select' ? '[]' : '')] = Configuration::get($key2) != '' ? explode(',', Configuration::get($key2)) : array();
                    } else {
                        $fields[$key] = Configuration::get($key2);
                    }
                }
            }
        }
        $assign = array(
            'base_url' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__,
            'language' => array(
                'id_lang' => $language->id,
                'iso_code' => $language->iso_code
            ),
            'fields_value' => $fields,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
            'is15' => version_compare(_PS_VERSION_, '1.6', '<') ? true : false,
        );
        if ($params['config'] == 'social_networks') {
            $assign['networks'] = Solo_defines::getInstance($this->context, $this)->getFields('socials');
            $assign['path'] = $this->_path;
            $assign['link_document'] = $this->context->link->getModuleLink($this->name, 'document', array(), (Tools::usingSecureMode() ? true : false)) . ((int)Configuration::get('PS_REWRITING_SETTINGS') ? '?' : '&');
            $assign['callback_url'] = self::getCallbackUrl($this->context, true);
        } elseif ($params['config'] == 'design') {
            $assign['pos'] = $pos;
            $assign['design_link'] = $this->context->link->getAdminLink('AdminModules', true) . '&configure=' . $this->name . '&tabActive=design';
            $assign['hooks'] = Solo_defines::getInstance($this->context, $this)->getFields('hooks');
            $assign['social_pages'] = explode(',', Configuration::get('ETS_SOLO_DISPLAY_SOCIAL_PAGE'));
        }
        $helper->tpl_vars = $assign;
        $this->_html .= $helper->generateForm(array($fields_form));
    }

    public function getUrlParams()
    {
        $params = '';
        if (($cTabs = Tools::getValue('tabActive', false)) && Validate::isCleanHtml($cTabs))
            $params .= '&tabActive=' . $cTabs;
        if (($ID = Tools::getValue('id_' . $this->list_id, false)) && Validate::isCleanHtml($ID))
            $params .= '&id_' . $this->list_id . '=' . $ID;
        if (($pos = Tools::getValue('pos', false)) && Validate::isCleanHtml($pos))
            $params .= '&pos=' . $pos;
        return $params;
    }

    public function getDataCharts($params)
    {
        if (!(isset($params['chart'])))
            return;
        $totalTraffic = Solo_presenter::getInstance($this->context, $this)->getNbTraffic($params);
        $data = array();
        if ($socials = Solo_defines::getInstance($this->context, $this)->getFields('socials')) {
            foreach ($socials as $social => $network) {
                if (isset($socials[$social]['label']) && ($upper = Tools::strtoupper($socials[$social]['label'])) && Configuration::get('ETS_SOLO_' . $upper . '_ENABLED')) {
                    $value = Solo_presenter::getInstance($this->context, $this)->getNbTraffic(array_merge($params, array('network' => $social)));
                    $data[] = $this->bindChart($params, $network['name'], ($params['chart'] != 'pie' ? $value : ($totalTraffic > 0 ? $value * 100 / $totalTraffic : 0)));
                }
            }
        }
        if ($params['chart'] != 'pie') {
            $data[] = $this->bindChart($params, $this->l('Total'), $totalTraffic);
        }
        if ($params['chart'] == 'pie' && $data) {
            $countValue = 0;
            foreach ($data as $item)
                if ($item['value'] <= 0)
                    $countValue++;
            if ($countValue == count($data))
                $data = array();
        }
        return $data;
    }

    public function bindChart($params, $label, $value)
    {
        if (isset($params['chart']) && $params['chart']) {
            $chartJs = isset($params['chart_js']) && $params['chart_js'] ? 1 : 0;
            if ($params['chart'] != 'pie') {
                $each = array();
                $format = 'Y';
                $this->timeSeries(0, $params['month'], $params['year'], $each, $format);
                $values = array();
                foreach ($each as $index) {
                    $res = $chartJs ? 0 : array($index, 0);
                    if (!empty($value)) {
                        foreach ($value as $data) {
                            if (date($format, strtotime($data['date_series'])) == $index) {
                                $res = $chartJs ? (int)$data['total'] : array($index, (int)$data['total']);
                                break;
                            }
                        }
                    }
                    if ($label == $this->l('Total') && $this->y_max_value < $res)
                        $this->y_max_value = $res;
                    $values[] = $res;
                }
            }
            $series = array(
                ($params['chart'] != 'pie' && !$chartJs ? 'key' : 'label') => $label,
                ($chartJs ? 'data' : ($params['chart'] != 'pie' ? 'values' : 'value')) => isset($values) ? $values : $value,
            );
            if ($chartJs) {
                $randomColor = $this->getColor($label);
                $series['backgroundColor'] = sprintf($randomColor, 0.5);
                $series['borderColor'] = sprintf($randomColor, 1);
                $series['borderWidth'] = 1;
                $series['fill'] = true;
            }
            if ($params['chart'] != 'pie' && $label != $this->l('Total') && !$chartJs) {
                $series = array_merge($series, array('disabled' => true));
            }
            return $series;
        }
        return array();
    }

    public function getMaxY($top)
    {
        $top = (int)$top;
        if ($top <= 1)
            return 2;
        if ($top < 10)
            return ($top < 5) ? $top + 1 : $top + 2;
        elseif ($top < 100)
            return ($top % 10 < 5) ? (floor($top / 10) + 1) * 10 : (floor($top / 10) + 2) * 10;
        elseif ($top < 1000)
            return ($top % 100 < 5) ? (floor($top / 100) + 1) * 100 : (floor($top / 100) + 2) * 100;
        else
            return ($top % 1000 < 5) ? (floor($top / 1000) + 1) * 1000 : (floor($top / 1000) + 1) * 1000;
    }

    public function getColor($num)
    {
        $hash = md5('color' . $num); // modify 'color' to get a different palette
        $rgb = array(
            hexdec(Tools::substr($hash, 0, 2)), // r
            hexdec(Tools::substr($hash, 2, 2)), // g
            hexdec(Tools::substr($hash, 4, 2))
        ); //b
        return 'rgba(' . implode(',', $rgb) . ', %s)';
    }

    public function timeSeries($return, $slMonth, $slYear, &$each, &$format)
    {
        if ($slMonth && $slYear) {
            $days = function_exists('cal_days_in_month') ? call_user_func('cal_days_in_month', CAL_GREGORIAN, (int)$slMonth, (int)$slYear) : (int)date('t', mktime(0, 0, 0, (int)$slMonth, 1, (int)$slYear));
            for ($day = 1; $day <= $days; $day++)
                $each[] = $day;
            $format = 'd';
        } elseif ($slYear) {
            if ($return) {
                $months = Tools::dateMonths();
                foreach ($months as $dateMonth)
                    $each[] = $dateMonth;
            } else {
                for ($month = 1; $month <= 12; $month++)
                    $each[] = $month;
            }
            $format = 'm';
        } else {
            $each = $this->dateYears();
            if (count($each) == 1) {
                $tmp = array((int)date('Y', strtotime('-1 years')));
                $tmp[] = $each[0];
                $each = $tmp;
            }
        }
        if ($return)
            return $each;
    }

    public function dateYears()
    {
        $startDate = Configuration::get('ETS_SOLO_INSTALL_DATE');
        $tab = array();
        for ($i = date('Y', strtotime($startDate)); $i <= date('Y'); $i++) {
            $tab[] = $i;
        }
        return $tab;
    }

    public function postChart()
    {
        $years = $this->dateYears();
        $months = Tools::dateMonths();
        $selectedYears = 0;
        $selectedMonths = 0;
        $selectedCountry = 0;
        if (Tools::isSubmit('submitFilterChart')) {
            $selectedYears = (int)Tools::getValue('years', 0);
            $selectedMonths = (int)Tools::getValue('months', 0);
            $selectedCountry = ($selectedCountry = Tools::getValue('countries')) && Validate::isCleanHtml($selectedCountry) ? $selectedCountry : '';
        }
        $val = array(
            'month' => $selectedMonths,
            'year' => $selectedYears,
            'id_country' => $selectedCountry,
            'return' => 1,
        );
        $this->smarty->assign(array(
            'pie_chart' => $this->getDataCharts(array_merge($val, array('chart' => 'pie'))),
            'line_chart' => $this->getDataCharts(array_merge($val, array('chart' => 'line'))),
            'bar_chart' => $this->getStatSocial($val),
            'users' => Solo_presenter::getInstance($this->context, $this)->getUsers(null),
            'years' => $years,
            'solo_year' => $selectedYears,
            'months' => $months,
            'solo_month' => $selectedMonths,
            'countries' => Country::getCountries($this->context->language->id),
            'solo_country' => $selectedCountry,
        ));
    }

    public function statistic()
    {
        $this->postChart();
        $this->smarty->assign(array(
            'action' => AdminController::$currentIndex . '&configure=' . $this->name . '&tabActive=statistic&token=' . Tools::getAdminTokenLite('AdminModules'),
        ));
        $this->_html .= $this->display(__FILE__, 'stats.tpl');
    }

    protected $toolbar_btn = [];

    public function initToolbar()
    {
        $this->toolbar_btn['new'] = array(
            'short' => 'Add SOCIAL LOGIN',
            'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&tabActive=social_users&add' . $this->list_id . '&token=' . Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->l('Add SOCIAL LOGIN')
        );
    }

    public function processResetFilters($list_id = null)
    {
        $fields_list = Solo_defines::getInstance($this->context, $this)->getFields('fields_list');
        if ($list_id === null) {
            $list_id = isset($this->list_id) ? $this->list_id : $this->name;
        }
        $prefix = null;
        $filters = $this->context->cookie->getFamily($prefix . $list_id . 'Filter_');
        if (!empty($filters)) {
            foreach ($filters as $cookie_key => $filter) {
                if (strncmp($cookie_key, $prefix . $list_id . 'Filter_', 7 + Tools::strlen($prefix . $list_id)) == 0) {
                    $key = Tools::substr($cookie_key, 7 + Tools::strlen($prefix . $list_id));
                    if (is_array($fields_list) && array_key_exists($key, $fields_list)) {
                        $this->context->cookie->__set($cookie_key, null);
                    }
                    $this->context->cookie->__unset($cookie_key);
                }
            }
        }
        $submitFilter = 'submitFilter' . $list_id;
        if ($this->context->cookie->__isset($submitFilter)) {
            $this->context->cookie->__unset($submitFilter);
        }
        $orderBy = $prefix . $list_id . 'Orderby';
        if ($this->context->cookie->__isset($orderBy)) {
            $this->context->cookie->__unset($orderBy);
        }
        $orderWay = $prefix . $list_id . 'Orderway';
        if ($this->context->cookie->__isset($orderWay)) {
            $this->context->cookie->__unset($orderWay);
        }

        $_POST = array();
    }

    public function processFilter($params)
    {
        if (empty($params))
            return false;
        if (!isset($this->list_id)) {
            $this->list_id = 'ets_solo_user';
        }
        $prefix = null;
        // Filter memorization
        if (!empty($_POST) && isset($this->list_id)) {
            foreach ($_POST as $key => $value) {
                $prop = $prefix . $key;
                if ($value === '') {
                    $this->context->cookie->__unset($prop);
                } elseif (stripos($key, $this->list_id . 'Filter_') === 0) {
                    $this->context->cookie->__set($prop, !is_array($value) ? $value : json_encode($value));
                } elseif (stripos($key, 'submitFilter') === 0) {
                    $this->context->cookie->__set($key, !is_array($value) ? $value : json_encode($value));
                }
            }
        }
        if (!empty($_GET) && isset($this->list_id)) {
            foreach ($_GET as $key => $value) {
                $prop = $prefix . $key;
                if (stripos($key, $this->list_id . 'Filter_') === 0) {
                    $this->context->cookie->__set($prop, !is_array($value) ? $value : json_encode($value));
                } elseif (stripos($key, 'submitFilter') === 0) {
                    $this->context->cookie->__set($key, !is_array($value) ? $value : json_encode($value));
                }
                if (stripos($key, $this->list_id . 'Orderby') === 0 && Validate::isOrderBy($value)) {
                    if ($value === '' || $value == $params['orderBy']) {
                        $this->context->cookie->__unset($prop);
                    } else {
                        $this->context->cookie->__set($prop, $value);
                    }
                } elseif (stripos($key, $this->list_id . 'Orderway') === 0 && Validate::isOrderWay($value)) {
                    if ($value === '' || $value == $params['orderWay']) {
                        $this->context->cookie->__unset($prop);
                    } else {
                        $this->context->cookie->__set($prop, $value);
                    }
                }
            }
        }

        $filters = $this->context->cookie->getFamily($prefix . $this->list_id . 'Filter_');

        foreach ($filters as $key => $value) {
            /* Extracting filters from $_POST on key filter_ */
            if ($value != null && !strncmp($key, $prefix . $this->list_id . 'Filter_', 7 + Tools::strlen($prefix . $this->list_id))) {
                $key = Tools::substr($key, 7 + Tools::strlen($prefix . $this->list_id));
                /* Table alias could be specified using a ! eg. alias!field */
                $tmp_tab = explode('!', $key);
                $filter = count($tmp_tab) > 1 ? $tmp_tab[1] : $tmp_tab[0];

                if ($field = $this->filterToField($key, $filter)) {
                    $type = (array_key_exists('filter_type', $field) ? $field['filter_type'] : (array_key_exists('type', $field) ? $field['type'] : false));
                    if (($type == 'date' || $type == 'datetime') && is_string($value))
                        $value = json_decode($value, true);
                    $key = isset($tmp_tab[1]) ? $tmp_tab[0] . '.`' . $tmp_tab[1] . '`' : '`' . $tmp_tab[0] . '`';
                    $sql_filter = '';
                    /* Only for date filtering (from, to) */
                    if (is_array($value)) {
                        if (isset($value[0]) && !empty($value[0])) {
                            if (!Validate::isDate($value[0])) {
                                $this->errors[] = Tools::displayError('The \'From\' date format is invalid (YYYY-MM-DD)');
                            } else {
                                $sql_filter .= ' AND ' . pSQL($key) . ' >= \'' . pSQL(Tools::dateFrom($value[0])) . '\'';
                            }
                        }

                        if (isset($value[1]) && !empty($value[1])) {
                            if (!Validate::isDate($value[1])) {
                                $this->errors[] = Tools::displayError('The \'To\' date format is invalid (YYYY-MM-DD)');
                            } else {
                                $sql_filter .= ' AND ' . pSQL($key) . ' <= \'' . pSQL(Tools::dateTo($value[1])) . '\'';
                            }
                        }
                    } else {
                        $sql_filter .= ' AND ';
                        $check_key = ($key == 'id_' . $this->list_id || $key == '`id_' . $this->list_id . '`');
                        $alias = 'su';

                        if ($type == 'int' || $type == 'bool') {
                            $sql_filter .= (($check_key || $key == '`active`') ? $alias . '.' : '') . pSQL($key) . ' = ' . (int)$value . ' ';
                        } elseif ($type == 'decimal') {
                            $sql_filter .= ($check_key ? $alias . '.' : '') . pSQL($key) . ' = ' . (float)$value . ' ';
                        } elseif ($type == 'select') {
                            $sql_filter .= ($check_key ? $alias . '.' : '') . pSQL($key) . ' = \'' . pSQL($value) . '\' ';
                        } elseif ($type == 'price') {
                            $value = (float)str_replace(',', '.', $value);
                            $sql_filter .= ($check_key ? $alias . '.' : '') . pSQL($key) . ' = ' . (float)$value . ' ';
                        } else {
                            $sql_filter .= ($check_key ? $alias . '.' : '') . pSQL($key) . ' LIKE \'%' . pSQL(trim($value)) . '%\' ';
                        }
                    }
                    if (isset($field['havingFilter']) && $field['havingFilter'])
                        $this->_filterHaving .= $sql_filter;
                    else
                        $this->_filter .= $sql_filter;
                }
            }
        }
    }

    protected function filterToField($key, $filter)
    {
        $fields_list = Solo_defines::getInstance($this->context, $this)->getFields('fields_list');
        if (!isset($fields_list))
            return false;

        foreach ($fields_list as $field)
            if (array_key_exists('filter_key', $field) && $field['filter_key'] == $key)
                return $field;
        if (array_key_exists($filter, $fields_list))
            return $fields_list[$filter];
        return false;
    }

    public function displayViewsLink($token, $id)
    {
        $user = new Solo_user($id);
        return Validate::isLoadedObject($user) ? $this->callBack(array(
            'value' => $token,
            'tr' => (array)$user,
            'type' => 'link'
        )) : $this->l('--');
    }

    public function renderList($params)
    {
        if (!$params)
            return $this->_html;
        $fields_list = $params['fields_list'];
        $this->initToolbar();
        $helper = new HelperList();
        $helper->title = $params['title'];
        $helper->table = $this->list_id;
        $helper->identifier = 'id_ets_solo_user';
        if (version_compare(_PS_VERSION_, '1.6.1', '>=')) {
            $helper->_pagination = array(20, 50, 100, 300);
            $helper->_default_pagination = 20;
        }
        $helper->_defaultOrderBy = $params['orderBy'];
        if ($params['orderBy'] == 'position') {
            $helper->position_identifier = 'position';
        }
        $this->processFilter($params);
        //Sort order
        $od = ($od = Tools::getValue(($orderBy = $helper->table . 'Orderby'))) && Validate::isCleanHtml($od) ? $od : '';
        $order_by = urldecode($od);
        if (!$order_by) {
            if ($this->context->cookie->__get($orderBy)) {
                $order_by = $this->context->cookie->__get($orderBy);
            } elseif ($helper->orderBy) {
                $order_by = $helper->orderBy;
            } else {
                $order_by = $helper->_defaultOrderBy;
            }
        }
        $ow = ($ow = Tools::getValue(($orderWay = $helper->table . 'Orderway'))) && Validate::isCleanHtml($ow) ? $ow : '';
        $order_way = urldecode($ow);
        if (!$order_way) {
            if ($this->context->cookie->__get($orderWay)) {
                $order_way = $this->context->cookie->__get($orderWay);
            } elseif ($helper->orderWay) {
                $order_way = $helper->orderWay;
            } else {
                $order_way = $params['orderWay'];
            }
        }
        if (isset($fields_list[$order_by]) && isset($fields_list[$order_by]['filter_key'])) {
            $order_by = $fields_list[$order_by]['filter_key'];
        }
        //Pagination.

        $limit = ($limit = Tools::getValue(($pagination = $helper->table . '_pagination'), false)) && Validate::isCleanHtml($limit) ? $limit : false;
        if (!$limit) {
            if ($this->context->cookie->__isset($pagination) && $this->context->cookie->__get($pagination))
                $limit = $this->context->cookie->__get($pagination);
            else
                $limit = (version_compare(_PS_VERSION_, '1.6.1', '>=') ? $helper->_default_pagination : 20);
        }
        $this->context->cookie->__set($pagination, $limit);
        $begin = $helper->table . '_start';
        $start = 0;
        if ((int)Tools::getValue('submitFilter' . $helper->table)) {
            $start = ((int)Tools::getValue('submitFilter' . $helper->table) - 1) * $limit;
        } elseif ($this->context->cookie->__isset($begin) && Tools::isSubmit('export' . $helper->table)) {
            $start = $this->context->cookie->__get($begin);
        }
        if ($start) {
            $this->context->cookie->__set($begin, $start);
        } elseif ($this->context->cookie->__isset($begin)) {
            $this->context->cookie->__unset($begin);
        }

        if (
            !Validate::isOrderBy($order_by) || !Validate::isOrderWay($order_way)
            || !is_numeric($start) || !is_numeric($limit)
        ) {
            $this->_errors = array($this->l('get list params is not valid'));
        }
        $helper->orderBy = $order_by;
        if (preg_match('/[.!]/', $order_by)) {
            $order_by_split = preg_split('/[.!]/', $order_by);
            $order_by = bqSQL($order_by_split[0]) . '.`' . bqSQL($order_by_split[1]) . '`';
        } elseif ($order_by) {
            $order_by = '`' . bqSQL($order_by) . '`';
        }
        $args = $params;
        $args['filter'] = $this->_filter;
        $args['having'] = $this->_filterHaving;
        $args['is_full'] = 1;
        $args['nb'] = 1;
        $methodNb = $params['nb'];
        $helper->listTotal = Solo_presenter::getInstance($this->context, $this)->{$methodNb}($args);
        unset($args['nb']);
        $args['start'] = $start;
        $args['limit'] = (isset($params['limit']) && $params['limit'] ? $params['limit'] : $limit);
        $args['sort'] = ($order_by != 'id_' . $this->list_id ? '' : (isset($params['alias']) ? $params['alias'] . '.' : '')) . $order_by . ' ' . Tools::strtoupper($order_way);
        $methodList = $params['list'];
        $list = Solo_presenter::getInstance($this->context, $this)->{$methodList}($args);

        $helper->orderWay = Tools::strtoupper($order_way);
        if (isset($params['toolbar_btn']) && $params['toolbar_btn'])
            $helper->toolbar_btn = $this->toolbar_btn;
        $helper->shopLinkType = '';
        $helper->row_hover = true;
        $helper->no_link = isset($params['no_link']) ? $params['no_link'] : false;
        $helper->simple_header = isset($params['simple_header']) ? $params['simple_header'] : false;
        $helper->actions = isset($params['actions']) ? $params['actions'] : array();
        $helper->show_toolbar = isset($params['show_toolbar']) ? $params['show_toolbar'] : true;
        $helper->module = $this;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name . (isset($params['tab']) && $params['tab'] ? '&tabActive=' . $params['tab'] : '');
        $helper->bulk_actions = isset($params['bulk_actions']) && $params['bulk_actions'] ? array(
            'enableSelection' => array(
                'text' => $this->l('Enable selection'),
                'icon' => 'icon-power-off text-success'
            ),
            'disableSelection' => array(
                'text' => $this->l('Disable selection'),
                'icon' => 'icon-power-off text-danger'
            ),
            'divider' => array(
                'text' => 'divider'
            ),
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected items?')
            )
        ) : false;

        $html = $helper->generateList($list, $fields_list);
        if (isset($params['return']) && $params['return'])
            return $html;
        $this->_html .= $html;
    }

    private function postProcess()
    {
        if (Tools::isSubmit('viewets_solo_user')) {
            $itemId = (int)Tools::getValue('id_ets_solo_user', 0);
            $user = new Solo_user($itemId);
            if (Validate::isLoadedObject($user)) {
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminCustomers', true, $this->ps1760 ? ['route' => 'admin_customers_view', 'customerId' => (int)$user->id_customer] : [], ['viewcustomer' => '', 'id_customer' => (int)$user->id_customer]));
            }
        }
        if (($tab = Tools::getValue('tabActive', 'dashboard')) && Validate::isCleanHtml($tab) && $this->inTabs($tab) && Tools::isSubmit('save' . Tools::ucfirst($tab))) {
            $params = array('tab' => $tab);
            if (($pos = Tools::getValue('pos', ($tab != 'design' ? false : $this->pos_default))) && Validate::isCleanHtml($pos))
                $params['pos'] = $pos;
            $this->processSave($params);
        } elseif (Tools::isSubmit('submitReset' . $this->list_id)) {
            $this->processResetFilters();
        } elseif (($positions = Tools::getValue('positions', false)) && Validate::isCleanHtml($positions)) {
            $result = (bool)Configuration::updateValue('ETS_SOLO_DISPLAY_SOCIAL_PAGE', $positions, true);
            die(json_encode(array(
                'errors' => $result ? false : true,
                'msg' => $result ? $this->l('Configuration successful') : $this->l('Configuration failed'),
                'positions' => $positions,
            )));
        } elseif ((int)Tools::getValue('ETS_SOLO_ENABLED', false)) {
            $social = ($social = Tools::getValue('group', '')) && Validate::isCleanHtml($social) ? $social : '';
            $enabled = (int)Tools::getValue('enabled', 0);
            if (!$social)
                $this->_errors[] = $this->l('Unknown social networks.');
            elseif (!Configuration::updateValue($social, (int)$enabled))
                $this->_errors[] = $this->l('Can not enable social networks.');
            die(json_encode(array(
                'errors' => $this->_errors ? true : false,
                'msg' => !$this->_errors ? $this->l('Successful update.') : $this->displayError($this->_errors),
            )));
        } elseif ((bool)Tools::isSubmit('updateChart')) {
            $slMonth = (int)Tools::getValue('month', 0);
            $slYear = (int)Tools::getValue('year', 0);
            die(json_encode($this->dashboardChart($slMonth, $slYear)));
        } elseif (Tools::getValue('tabActive', 'dashboard') == 'dashboard' && ((($sp = Tools::getValue('selected_pagination')) && Validate::isCleanHtml($sp)) || (($page = (int)Tools::getValue('page')) && $page > 0))) {
            die(json_encode(array(
                'html' => $this->dashboardList()
            )));
        }
    }

    public function customRequired($params)
    {
        if (!(isset($params['name'])) || !$params['name'] || !(isset($params['tab'])) || !$params['tab']) {
            return false;
        }
        $keyVal = Tools::getValue($params['name'], '');
        switch ($params['tab']) {
            case 'social_networks':
                $socials = isset($params['socials']) ? $params['socials'] : array();
                $configs = isset($params['configs']) ? $params['configs'] : array();
                if ($socials && $configs && ($tab = $configs[$params['name']]['group']) && Tools::getIsset('ETS_SOLO_' . Tools::strtoupper($socials[$tab]['label']) . '_ENABLED') && trim($keyVal) == '')
                    return true;
                break;
            case 'discount':
                if ((int)Tools::getValue('ETS_SOLO_DISCOUNT_ENABLED') > 0) {
                    $optionVal = ($optionVal = Tools::getValue('ETS_SOLO_DISCOUNT_OPTION')) && Validate::isCleanHtml($optionVal) ? $optionVal : '';
                    $applyVal = ($applyVal = Tools::getValue('ETS_SOLO_APPLY_DISCOUNT')) && Validate::isCleanHtml($applyVal) ? $applyVal : '';
                    switch ($params['name']) {
                        case 'ETS_SOLO_DISCOUNT_CODE':
                            if ($optionVal == 'fixed' && trim($keyVal) == '')
                                return true;
                            break;
                        case 'ETS_SOLO_REDUCTION_PERCENT':
                            if ($optionVal == 'auto' && $applyVal == 'percent' && trim($keyVal) == '')
                                return true;
                            break;
                        case 'ETS_SOLO_REDUCTION_AMOUNT':
                            if ($optionVal == 'auto' && $applyVal == 'amount' && trim($keyVal) == '')
                                return true;
                            break;
                        case 'ETS_SOLO_DISCOUNT_NAME_' . $this->context->language->id:
                        case 'ETS_SOLO_APPLY_DISCOUNT_IN':
                            if ($optionVal == 'auto' && trim($keyVal) == '')
                                return true;
                            break;
                        default:
                            $pName = ($pName = trim(Tools::getValue($params['name']))) && Validate::isCleanHtml($pName) ? $pName : '';
                            if ($pName == '')
                                return true;
                            break;
                    }
                }
                break;
            default:
                if (trim($keyVal) == '')
                    return true;
                break;
        }
        return false;
    }

    public function customValidate($key, $id_lang = false)
    {
        if (!$key)
            return false;
        $val = Tools::getValue($key . ($id_lang ? '_' . $id_lang : ''));
        if (!is_array($val) && !Validate::isCleanHtml($val)) {
            $val = '';
        } elseif (is_array($val)) {
            foreach ($val as $ki => $ii) {
                if (!is_array($ii) && !Validate::isCleanHtml($ii)) {
                    unset($val[$ki]);
                }
            }
        }
        $discountOption = ($discountOption = Tools::getValue('ETS_SOLO_DISCOUNT_OPTION')) && Validate::isCleanHtml($discountOption) ? $discountOption : '';
        $appDiscount = ($appDiscount = Tools::getValue('ETS_SOLO_APPLY_DISCOUNT')) && Validate::isCleanHtml($appDiscount) ? $appDiscount : '';
        switch ($key) {
            case 'ETS_SOLO_DISCOUNT_CODE':
                if (
                    $discountOption == 'fixed'
                    && (!Validate::isGenericName($val) || !CartRule::cartRuleExists($val))
                ) {
                    return true;
                }
                break;
            case 'ETS_SOLO_APPLY_DISCOUNT_IN':
                if ($discountOption == 'auto' && ($val == '0' || ($val != '' && !Validate::isUnsignedId($val)))) {
                    return true;
                }
                break;
            case 'ETS_SOLO_REDUCTION_PERCENT':
                if ($discountOption == 'auto' && $appDiscount == 'percent' && !Validate::isUnsignedFloat($val))
                    return true;
                break;
            case 'ETS_SOLO_REDUCTION_AMOUNT':
                if ($discountOption == 'auto' && $appDiscount == 'amount' && !Validate::isUnsignedFloat($val))
                    return true;
                break;
        }
        return false;
    }

    public function formatPos($pos)
    {
        if ($pos && !preg_match('/^_[A-Z]{3}$/', $pos))
            return '_' . Tools::strtoupper(Tools::substr(ltrim($pos, '_'), 0, 3));
        return $pos;
    }

    private function processSave($params)
    {
        if (empty($params))
            return false;
        $errors = array();
        $languages = Language::getLanguages(false);
        $id_lang_default = (int)Configuration::get('PS_LANG_DEFAULT');
        $configTabs = Solo_defines::getInstance($this->context, $this)->getFields($params['tab']);
        $configs = $configTabs['configs'];
        $pos = isset($params['pos']) && $params['pos'] ? $this->formatPos($params['pos']) : '';
        $tab_social_network = ($params['tab'] == 'social_networks' ? 1 : 0);
        $socials = $tab_social_network ? Solo_defines::getInstance($this->context, $this)->getFields('socials') : false;
        if ($configs) {
            $args = array(
                'tab' => $params['tab'],
                'configs' => $configs,
            );
            if ($socials) {
                $args['socials'] = $socials;
            }
            foreach ($configs as $key => $config) {
                if (isset($config['design']) && !in_array($pos, $config['design']))
                    continue;
                $key .= (isset($config['common']) && $config['common'] ? '' : $pos);
                if (isset($config['lang']) && $config['lang']) {
                    $args['name'] = $key . '_' . $id_lang_default;
                    if (isset($config['required']) && $config['required'] && $config['type'] != 'switch' && $this->customRequired($args)) {
                        $errors[] = ($socials ? $socials[$config['group']]['name'] . ' ' . Tools::strtolower($config['label']) : $config['label']) . ' ' . $this->l('is required');
                    }
                } else {
                    $args['name'] = $key;
                    if (isset($config['required']) && $config['required'] && $config['type'] != 'switch' && $this->customRequired($args)) {
                        $errors[] = ($socials ? $socials[$config['group']]['name'] . ' ' . Tools::strtolower($config['label']) : $config['label']) . ' ' . $this->l('is required');
                    } elseif (($val = Tools::getValue($key)) && !is_array($val) && Validate::isCleanHtml($val) && isset($config['validate']) && method_exists('Validate', $config['validate'])) {
                        $validate = $config['validate'];
                        if (trim($val) && !Validate::$validate(trim($val)))
                            $errors[] = ($socials ? $socials[$config['group']]['name'] . ' ' . Tools::strtolower($config['label']) : $config['label']) . ' ' . $this->l('is invalid');
                        unset($validate);
                    } elseif ($this->customValidate($key) || (!is_array(Tools::getValue($key)) && (!Validate::isCleanHtml(trim(Tools::getValue($key)))))) {
                        $errors[] = ($socials ? $socials[$config['group']]['name'] . ' ' . Tools::strtolower($config['label']) : $config['label']) . ' ' . $this->l('is invalid');
                    }
                }
            }
        }
        if (!$errors) {
            if ($configs) {
                foreach ($configs as $key => $config) {
                    if (isset($config['design']) && !in_array($pos, $config['design']) || ($tab_social_network && !Tools::getIsset($key)))
                        continue;
                    $key .= (isset($config['common']) && $config['common'] ? '' : $pos);
                    if (isset($config['lang']) && $config['lang']) {
                        $values = array();
                        foreach ($languages as $lang) {
                            $val = ($val = Tools::getValue($key . '_' . $lang['id_lang'])) && Validate::isCleanHtml($val) ? $val : '';
                            $defaultVal = ($defaultVal = Tools::getValue($key . '_' . $id_lang_default)) && Validate::isCleanHtml($defaultVal) ? $defaultVal : '';
                            if ($config['type'] == 'switch') {
                                $values[$lang['id_lang']] = (int)trim($val) ? 1 : 0;
                            } else {
                                $values[$lang['id_lang']] = trim($val) ? trim($val) : trim($defaultVal);
                            }
                        }
                        Configuration::updateValue($key, $values, true);
                    } else {
                        if ($config['type'] == 'switch') {
                            Configuration::updateValue($key, (int)trim(Tools::getValue($key)) ? 1 : 0, true);
                        } elseif (($config['type'] == 'select' && isset($config['multiple']) && $config['multiple']) || $config['type'] == 'solo_group') {
                            $val = Tools::getValue($key, array());
                            if (is_array($val)) {
                                foreach ($val as $ki => $ii) {
                                    if (!Validate::isCleanHtml($ii)) {
                                        unset($val[$ki]);
                                    }
                                }
                            }
                            Configuration::updateValue($key, implode(',', $val));
                        } else
                            Configuration::updateValue($key, ($val = Tools::getValue($key, '')) && Validate::isCleanHtml($val) ? trim($val) : '', true);
                    }
                }
            }
        }
        $tabActive = ($tabActive = Tools::getValue('tabActive')) && Validate::isCleanHtml($tabActive) ? $tabActive : '';
        if ((($position = Tools::getValue('ajax', false)) && Validate::isCleanHtml($position)) || Tools::isSubmit('saveSocial_networks')) {
            $json = array();
            if ($position) {
                $json['hasError'] = count($errors) > 0 ? true : false;
                $json['msg'] = $json['hasError'] ? $this->l('Position update failed') : $this->l('Successful update.');
            } else {
                $json['errors'] = count($errors) > 0 ? $this->displayError($errors) : false;
                $json['msg'] = $json['errors'] ? '' : $this->l('Successful update.');
            }
            die(json_encode($json));
        } elseif (count($errors)) {
            $this->errorMessage = $this->displayError($errors);
        } else
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true) . '&conf=4&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name . '&tabActive=' . $tabActive . (($pos = Tools::getValue('pos', false)) && Validate::isCleanHtml($pos) ? '&pos=' . $pos : ''));
    }

    public function hookDisplaySoLoHelp()
    {
        return $this->display(__FILE__, 'admin-help.tpl');
    }

    public static function file_get_contents($url, $use_include_path = false, $stream_context = null, $curl_timeout = 60)
    {
        if ($stream_context == null && preg_match('/^https?:\/\//', $url)) {
            $stream_context = stream_context_create(array(
                "http" => array(
                    "timeout" => $curl_timeout,
                    "max_redirects" => 101,
                    "header" => 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36'
                ),
                "ssl" => array(
                    "allow_self_signed" => true,
                    "verify_peer" => false,
                    "verify_peer_name" => false,
                ),
            ));
        }
        if (function_exists('curl_init')) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => html_entity_decode($url),
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36',
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT => $curl_timeout,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_FOLLOWLOCATION => true,
            ));
            $content = curl_exec($curl);
            curl_close($curl);
            return $content;
        } elseif (in_array(ini_get('allow_url_fopen'), array('On', 'on', '1')) || !preg_match('/^https?:\/\//', $url)) {
            return Tools::file_get_contents($url, $use_include_path, $stream_context);
        } else {
            return false;
        }
    }

    public function hookDisplayBackOfficeHeader()
    {
        $config = ($config = Tools::getValue('configure')) && Validate::isCleanHtml($config) ? $config : '';
        $controller = ($controller = Tools::getValue('controller')) && Validate::isCleanHtml($controller) ? $controller : '';
        if ($controller == 'AdminModules' && $config == $this->name) {
            $this->context->controller->addJqueryUI('ui.sortable');
            $this->context->controller->addCss(array(
                $this->_path . 'views/css/common.css',
                $this->_path . 'views/css/admin.css',
            ), 'all');
            $this->context->controller->addJS($this->_path . 'views/js/sticky.js');
            $this->context->controller->addJS($this->_path . 'views/js/chart.js');
            if (version_compare(_PS_VERSION_, '1.6', '<')) {
                $this->context->controller->addCss($this->_path . 'views/css/common.css', 'all');
                $this->context->controller->addCss($this->_path . 'views/css/admin_fix15.css', 'all');
                $this->context->controller->addCss($this->_path . 'views/css/nv.d3' . ($this->context->language->is_rtl ? '_rtl' : '') . '.css');
                $this->context->controller->addJS(array(
                    $this->_path . 'views/js/d3.v3.min.js',
                    $this->_path . 'views/js/nv.d3.min.js'
                ));
            } elseif (!$this->is17 && version_compare(_PS_VERSION_, '1.6', '>=')) {
                $this->context->controller->addCss($this->_path . 'views/css/admin_fix16.css', 'all');
            } else {
                $admin_webpath = str_ireplace(_PS_CORE_DIR_, '', _PS_ADMIN_DIR_);
                $admin_webpath = preg_replace('/^' . preg_quote(DIRECTORY_SEPARATOR, '/') . '/', '', $admin_webpath);
                $this->context->controller->addCSS(__PS_BASE_URI__ . $admin_webpath . '/themes/' . $this->context->employee->bo_theme . '/css/vendor/nv.d3.css');
                $this->context->controller->addJS(array(
                    _PS_JS_DIR_ . 'vendor/d3.v3.min.js',
                    __PS_BASE_URI__ . $admin_webpath . '/themes/' . $this->context->employee->bo_theme . '/js/vendor/nv.d3.min.js'
                ));
            }
            return $this->display(__FILE__, 'header.tpl');
        }
    }

    public function baseAdminUrl()
    {
        return $this->context->link->getAdminLink('AdminModules', true) . '&configure=' . $this->name;
    }

    public function hookDisplayHeader()
    {
        $this->context->controller->addCSS(array(
            $this->_path . 'views/css/fix' . ($this->is17 ? '17' : (version_compare(_PS_VERSION_, '1.6', '>=') ? '16' : '15')) . '.css',
            $this->_path . 'views/css/front.css',
            $this->_path . 'views/css/common.css',
        ), 'all');
        if (!$this->is17) {
            $this->smarty->assign('base_dir', $this->_path);
        } else {
            $this->context->controller->addJS($this->_path . 'views/js/front.js');
        }
        if ($html = $this->displayFrontNetworks(array('hook' => 'hea'))) {
            $this->smarty->assign('html', $html);
        }
        $this->smarty->assign(array(
            'callback' => $this->context->link->getModuleLink($this->name, 'oauth', array(), Tools::usingSecureMode()),
        ));
        return $this->display(__FILE__, 'header.tpl');
    }

    public function hookActionCustomerLogoutAfter()
    {
        if ($this->context->cookie->__isset('soloProvider')) {
            $storage = new ETSHybridauth\Storage\Session($this->context);
            $storage->clear();
            $this->context->cookie->__unset('soloProvider');
            $this->context->cookie->write();
        }
    }

    public function prepareDataToSave($profile)
    {
        if ($profile->firstName && $profile->lastName && Validate::isName($profile->firstName) && Validate::isName($profile->lastName)) {
            return $profile;
        } elseif ($profile->firstName && Validate::isName($profile->firstName)) {
            $profile->lastName = $profile->firstName;
        } elseif ($profile->lastName && Validate::isName($profile->lastName)) {
            $profile->firstName = $profile->lastName;
        } elseif ($profile->displayName) {
            $profile->displayName = str_replace('+', '', $profile->displayName);
            $parts = explode(' ', trim($profile->displayName));
            $nameParts = array();
            foreach ($parts as $part) {
                if (trim($part) == '') continue;
                $nameParts[] = $part;
            }
            if (count($nameParts) == 1) {
                $profile->firstName = $profile->lastName = $nameParts[0];
            } elseif (count($nameParts) > 1) {
                $profile->firstName = $nameParts[0];
                unset($nameParts[0]);
                $profile->lastName = implode(' ', $nameParts);
            }
        }
        if (!$profile->firstName || !Validate::isName($profile->firstName))
            $profile->firstName = $this->l('Unknown');
        if (!$profile->lastName || !Validate::isName($profile->lastName))
            $profile->lastName = $this->l('Unknown');
        return $profile;
    }

    public function createUser($profile, $provider)
    {
        if (!$profile) {
            die(json_encode(array('errors' => $this->l('Connect API error! Please check again your account.'))));
        } elseif ($provider) {
            $profile = $this->prepareDataToSave($profile);
            $customer = new Customer();
            $customer->id_shop = (int)$this->context->shop->id;
            $customer->lastname = $profile->lastName;
            $customer->firstname = $profile->firstName;
            $customer->email = $profile->email;
            $customer->id_default_group = (int)Configuration::get('PS_CUSTOMER_GROUP');
            $passwdGen = Tools::passwdGen(8, 'RANDOM');
            if ($this->is17 && class_exists('PrestaShop\PrestaShop\Adapter\ServiceLocator') && class_exists('PrestaShop\PrestaShop\Core\Crypto\Hashing')) {
                /** @var PrestaShop\PrestaShop\Core\Crypto\Hashing $crypto */
                $crypto = PrestaShop\PrestaShop\Adapter\ServiceLocator::get('\\PrestaShop\\PrestaShop\\Core\\Crypto\\Hashing');
                $customer->passwd = $crypto->hash($passwdGen);
            } else {
                $customer->passwd = md5(_COOKIE_KEY_ . $passwdGen);
            }
            if ($customer->save()) {
                $customer->updateGroup(array(Configuration::get('ETS_SOLO_CUSTOMER_GROUP') != '' ? (int)Configuration::get('ETS_SOLO_CUSTOMER_GROUP') : (int)Configuration::get('PS_CUSTOMER_GROUP')));
                $socialNetworkName = $this->getSocialNetwork(['label' => $provider], true);
                if ($this->sendConfirmationMail($customer, $passwdGen, $socialNetworkName)) {
                    $_GET['psgdpr-consent'] = 1;
                    Hook::exec('actionCustomerAccountAdd', array('_POST' => $_POST, 'newCustomer' => $customer));
                }
                $discountCode = $this->firstConnects($provider, $customer);
                $this->trackingUser($profile, $customer->id, $provider, $discountCode);
                $this->trackingLogin($profile, $provider);
                $this->updateContext($customer);
            } else
                die(json_encode(array('errors' => $this->l('Create account error. Please check account profile.'))));
        }
    }


    public static function getIdCompareByIdCustomer($id_customer)
    {
        return (int)Db::getInstance()->getValue('
		SELECT `id_compare`
		FROM `' . _DB_PREFIX_ . 'compare`
		WHERE `id_customer`= ' . (int)$id_customer);
    }

    public function updateContext(Customer $customer)
    {
        $this->context->customer = $customer;
        if (!$this->is17) {
            if (!$this->context->cookie->__isset('id_compare')) {
                $this->context->cookie->__set('id_compare', self::getIdCompareByIdCustomer($customer->id));
            }
        }
        $this->context->cookie->__set('id_customer', (int)($customer->id));
        $this->context->cookie->__set('customer_lastname', $customer->lastname);
        $this->context->cookie->__set('customer_firstname', $customer->firstname);
        $this->context->cookie->__set('passwd', $customer->passwd);
        $this->context->cookie->__set('logged', true);
        $customer->logged = true;
        $this->context->cookie->__set('email', $customer->email);
        $this->context->cookie->__set('is_guest', $customer->isGuest());
        $cookieCartId = (int)$this->context->cookie->__get('id_cart');
        $lastCartId = (int)Cart::lastNoneOrderedCart($this->context->customer->id);
        // Add customer to the context
        if (Configuration::get('PS_CART_FOLLOWING') && (!$cookieCartId || Cart::getNbProducts($cookieCartId) == 0) && $lastCartId) {
            $this->context->cart = new Cart($lastCartId);
        } else {
            if ($this->is17)
                $idCarrier = (int)$this->context->cart->id_carrier;
            $this->context->cart->id_carrier = 0;
            $this->context->cart->setDeliveryOption(null);
            if ($this->is17)
                $this->context->cart->updateAddressId($this->context->cart->id_address_delivery, (int)Address::getFirstCustomerAddressId((int)($customer->id)));
            $this->context->cart->id_address_delivery = (int)Address::getFirstCustomerAddressId((int)($customer->id));
            $this->context->cart->id_address_invoice = (int)Address::getFirstCustomerAddressId((int)($customer->id));
        }
        $this->context->cart->id_customer = (int)$customer->id;
        if (isset($idCarrier) && $idCarrier) {
            $deliveryOption = array($this->context->cart->id_address_delivery => $idCarrier . ',');
            $this->context->cart->setDeliveryOption($deliveryOption);
        }
        $this->context->cart->secure_key = $customer->secure_key;
        $this->context->cart->save();
        $this->context->cookie->__set('id_cart', (int)$this->context->cart->id);
        if (method_exists($this->context->cookie, 'registerSession') && class_exists('CustomerSession')) {
            $this->context->cookie->registerSession(new CustomerSession());
        }

        $this->context->cart->autosetProductAddress();
        Hook::exec('actionAuthentication', array('customer' => $customer));
        // Login information have changed, so we check if the cart rules still apply
        CartRule::autoRemoveFromCart($this->context);
        CartRule::autoAddToCart($this->context);

        $this->context->cookie->write();
    }

    public function popup_exit()
    {
        echo $this->context->smarty->fetch($this->getLocalPath() . 'views/templates/hook/js.tpl');
        exit();
    }

    public function firstConnects($provider, $customer)
    {
        if ((int)Configuration::get('ETS_SOLO_DISCOUNT_ENABLED') > 0 && $this->applyDiscount($provider)) {
            if (Configuration::get('ETS_SOLO_DISCOUNT_OPTION') == 'auto') {
                $cartRule = $this->genCartRule($customer->id);
            } elseif (($code = Configuration::get('ETS_SOLO_DISCOUNT_CODE')) && ($itemId = CartRule::getIdByCode($code))) {
                $cartRule = new CartRule($itemId, $customer->id_lang);
            }
            if (isset($cartRule) && property_exists($cartRule, 'id') && $cartRule->id) {
                if (Configuration::get('ETS_SOLO_SEND_DISCOUNT') == 'email') {
                    $this->sendEmail($customer, $cartRule);
                } elseif (Configuration::get('ETS_SOLO_SEND_DISCOUNT') == 'popup') {
                    $this->showPopup($cartRule);
                } else {
                    $this->sendEmail($customer, $cartRule);
                    $this->showPopup($cartRule);
                }
                return $cartRule->code;
            }
        }
        return null;
    }

    public function showPopup($cartRule)
    {
        if (property_exists($cartRule, 'code') && $cartRule->code) {
            $this->context->cookie->__set('solo_new_account', $cartRule->code);
            $this->context->cookie->write();
        }
    }

    public function applyDiscount($provider)
    {
        $networks = explode(',', Configuration::get('ETS_SOLO_DISCOUNT_NETWORKS'));
        if (in_array('all', $networks))
            return true;
        else {
            $net = $this->getSocialNetwork(array(
                'label' => $provider
            ));
            if ($net && in_array($net, $networks))
                return true;
        }
        return false;
    }

    public function getSocialNetwork($params, $returnName = false)
    {
        if ($params && ($socials = Solo_defines::getInstance($this->context, $this)->getFields('socials'))) {
            foreach ($socials as $key => $net) {
                if ((isset($params['label']) && Tools::strtolower($params['label']) == Tools::strtolower($net['label'])) || (isset($params['id']) && $params['id'] == $key))
                    return $returnName ? $net['name'] : $key;
            }
        }
    }

    public function sendEmail($customer, $cartRule)
    {
        $subject = ($title = Configuration::get('ETS_SOLO_EMAIL_SUBJECT', $customer->id_lang, $customer->id_shop_group, $customer->id_shop)) ? $title : $this->l('Here is your discount!');
        $content = Configuration::get('ETS_SOLO_EMAIL_CONTENT', $customer->id_lang, $customer->id_shop_group, $customer->id_shop);
        if (!$content)
            $content = Configuration::get('ETS_SOLO_EMAIL_CONTENT', $this->context->language->id, $this->context->shop->id_shop_group, $this->context->shop->id);
        if ($content && $subject) {
            $content = $this->replaceCode($content, $cartRule);
            return Mail::Send(
                $this->context->language->id,
                'voucher',
                $subject,
                array(
                    '{firstname}' => $customer->firstname,
                    '{lastname}' => $customer->lastname,
                    '{content}' => $content
                ),
                $customer->email,
                $customer->firstname . ' ' . $customer->lastname,
                null,
                null,
                null,
                null,
                $this->getLocalPath() . 'mails/'
            );
        }
    }

    public function getCartRule($cartRule)
    {
        $with_taxes = true;
        if (!Configuration::get('PS_TAX')) {
            $with_taxes = false;
        }
        if ($cartRule->checkValidity($this->context, false, false)) {
            $this->context->cart->addCartRule($cartRule->id);
        }
        $total_shipping = $this->context->cart->getOrderTotal($with_taxes, Cart::ONLY_DISCOUNTS);
        if (!$cartRule->checkValidity($this->context, false, false)) {
            $this->context->cart->removeCartRule($cartRule->id);
        }
        return $total_shipping ? (float)Tools::ps_round($total_shipping, 2) : $cartRule->reduction_amount;
    }

    public function replaceCode($content, $cartRule)
    {
        if ($cartRule) {
            $content = str_replace(
                array('[discount_code]', '[available_from]', '[available_to]', '[percentage]', '[amount]'),
                array(
                    $this->smarty_front_end(array('key' => 'discount_code', 'text' => $cartRule->code)),
                    date($this->context->language->date_format_lite, strtotime($cartRule->date_from)),
                    date($this->context->language->date_format_lite, strtotime($cartRule->date_to)),
                    ($cartRule->reduction_percent ? $cartRule->reduction_percent : 0) . '%',
                    ($cartRule->reduction_amount > 0 ? Tools::displayPriceSmarty(['price' => $this->getCartRule($cartRule), 'currency' => (int)Configuration::get('ETS_SOLO_ID_CURRENCY')], $this->context->smarty) : '')
                ),
                $content
            );
        }
        return $content;
    }

    public function trackingUser($profile, $id_customer, $network, $discount_code = null)
    {
        if (!empty($profile) && $id_customer) {
            $user = new Solo_user((($itemId = (int)Solo_user::getUserByEmail($profile->email)) ? $itemId : null));
            $user->id_customer = (int)$id_customer;
            if ($profile->identifier)
                $user->identifier = $profile->identifier;
            if ($network) {
                $user->network = $this->getSocialNetwork(array(
                    'label' => $network
                ));
            }
            if (isset($profile->photoURL) && $profile->photoURL)
                $user->profile_img = trim($profile->photoURL);
            if (isset($profile->profileURL) && $profile->profileURL)
                $user->profile_url = trim($profile->profileURL);
            if ($discount_code)
                $user->discount_code = $discount_code;
            $user->id_shop = (int)$this->context->shop->id;
            if (!count($user->validateFieldsRequiredDatabase()))
                $user->save();
        }
    }

    /**
     * @param \ETSHybridauth\User\Profile $profile
     * @param $network
     */
    public function trackingLogin($profile, $network)
    {
        $userId = Solo_user::getUserByEmail($profile->email);
        $user = new Solo_user($userId);
        if (Validate::isLoadedObject($user) && $network) {
            $social = $this->getSocialNetwork(array(
                'label' => $network
            ));
            $login_time = date('Y-m-d H:i:s');
            $user->identifier = $profile->identifier;
            $user->last_login_type = $social;
            $user->last_login_time = $login_time;
            if ($user->update()) {
                $connect = new Solo_connect((($itemId = Solo_connect::getUserByEmail($user->id, $social)) ? $itemId : null));
                $connect->id_ets_solo_user = $user->id;
                $connect->identifier = $profile->identifier;
                $connect->last_login_type = $social;
                $connect->last_login_time = $login_time;
                if (!count($connect->validateFieldsRequiredDatabase()))
                    $connect->save();
            }
        }
    }

    public function sendConfirmationMail(Customer $customer, $passwdGen, $socialNetworkName)
    {
        return Mail::Send(
            $this->context->language->id,
            'account_' . ((int)Configuration::get('ETS_SOLO_SEND_PASSWORD') ? 'show' : 'hide') . 'password',
            $this->l('Welcome!'),
            array(
                '{firstname}' => $customer->firstname,
                '{lastname}' => $customer->lastname,
                '{email}' => $customer->email,
                '{change_password}' => $this->context->link->getPageLink('identity', null, null, null, false, null),
                '{password}' => (int)Configuration::get('ETS_SOLO_SEND_PASSWORD') ? $passwdGen : '************',
                '{social_network_name}' => $socialNetworkName,
            ),
            $customer->email,
            $customer->firstname . ' ' . $customer->lastname,
            null,
            null,
            null,
            null,
            $this->getLocalPath() . 'mails/'
        );
    }

    public function genCartRule($id_customer = 0)
    {
        $languages = Language::getLanguages(false);
        $cart_rule = new CartRule();
        $cart_rule->id_customer = $id_customer;
        if ($languages) {
            $rule_name = array();
            foreach ($languages as $lang)
                $rule_name[$lang['id_lang']] = Configuration::get('ETS_SOLO_DISCOUNT_NAME', $lang['id_lang']);
            $cart_rule->name = $rule_name;
        }
        $cart_rule->free_shipping = Configuration::get('ETS_SOLO_FREE_SHIPPING');
        $cart_rule->code = Configuration::get('ETS_SOLO_DISCOUNT_PREFIX') . Tools::passwdGen(8);
        if (Configuration::get('ETS_SOLO_APPLY_DISCOUNT') == 'percent') {
            $cart_rule->reduction_percent = Configuration::get('ETS_SOLO_REDUCTION_PERCENT');
        } elseif (Configuration::get('ETS_SOLO_APPLY_DISCOUNT') == 'amount') {
            $cart_rule->reduction_amount = Configuration::get('ETS_SOLO_REDUCTION_AMOUNT');
            $cart_rule->reduction_currency = (int)Configuration::get('ETS_SOLO_ID_CURRENCY');
            $cart_rule->reduction_tax = (bool)Configuration::get('ETS_SOLO_REDUCTION_TAX');
        }
        $cart_rule->date_from = date('Y-m-d H:i:s');
        $cart_rule->date_to = ($day = Configuration::get('ETS_SOLO_APPLY_DISCOUNT_IN')) ? date('Y-m-d H:i:s', strtotime('+' . $day . ' days')) : date('Y-m-d H:i:s', strtotime('+30 days'));

        return ($cart_rule->add()) ? $cart_rule : false;
    }

    public function hookVal($params)
    {
        if (isset($params['hook']) && ($pos = $params['hook']) && Configuration::get('ETS_SOLO_DISPLAY_SOCIAL_PAGE')) {
            $position = explode(',', Configuration::get('ETS_SOLO_DISPLAY_SOCIAL_PAGE'));
            if (is_array($position) && in_array($pos, $position))
                return true;
        }
        return false;
    }

    public function displayFrontNetworks($params)
    {
        if (!$params)
            return false;
        if (((isset($params['hook']) && $params['hook'] == 'admin') || (!$this->context->customer->isLogged() && $this->hookVal($params))) && ($config = $this->getConfigs(array('pos' => isset($params['hook']) ? $params['hook'] : '')))) {
            $design = Solo_defines::getInstance($this->context, $this)->getFields('design');
            $collect = array('model' => $design['configs']);
            $hook = isset($params['hook']) && $params['hook'] ? $params['hook'] : '';
            if (($pos = Tools::getValue('pos', ($hook != 'admin' ? $hook : ''))) && Validate::isCleanHtml($pos))
                $collect['pos'] = $pos;
            if ($hook == 'admin' && empty($collect['pos']))
                $collect['pos'] = $this->pos_default;
            $ETSHybridauth = new ETSHybridauth\ETSHybridauth($config, null, null, null, $this->context);
            $link = $this->context->link->getPageLink('authentication', Tools::usingSecureMode() ? true : false);
            $link .= (strpos($link, '?') === false ? '?' : '&') . 'create_account=1';
            $js_design = $this->assignJsConfig($collect);
            $this->smarty->assign(array(
                'hook' => $hook,
                'nets' => $ETSHybridauth->getProviders(),
                'socials' => Solo_defines::getInstance($this->context, $this)->getFields('socials'),
                'design' => $js_design,
                'base_url' => $this->_path,
                'is15' => version_compare(_PS_VERSION_, '1.6', '<') ? 1 : 0,
                'is17' => $this->is17,
                'submit' => $this->context->link->getModuleLink($this->name, 'oauth', array(), (Tools::usingSecureMode() ? true : false)),
                'recover_password' => $this->context->link->getPageLink('password', Tools::usingSecureMode() ? true : false),
                'create_account' => $link,
                'pos' => $pos,
                'is_rtl' => $this->context->language->is_rtl
            ));

            return $this->display(__FILE__, 'social-login.tpl');
        }
    }

    public function hookDisplayCustomerAccount()
    {
        if (Configuration::get('ETS_SOLO_MY_ACCOUNT_PAGE')) {
            $this->smarty->assign(array(
                'link' => $this->context->link->getModuleLink($this->name, 'social', array(), Tools::usingSecureMode() ? true : false),
                'is17' => $this->is17
            ));
            return $this->display(__FILE__, 'block.tpl');
        }
    }

    public function hookDisplaySoloOnPage()
    {
        if (Configuration::get('ETS_SOLO_MY_ACCOUNT_PAGE') && $this->context->customer->isLogged()) {
            $connect_array = array();
            $connects = Solo_presenter::getInstance($this->context, $this)->getUserConnects(array('id_customer' => $this->context->customer->id, 'front_end' => true));
            if ($connects) {
                foreach ($connects as $connect) {
                    $connect_array[$connect['last_login_type']] = $connect;
                }
            }
            $nets = $this->getConfigs(array('is_sort' => 1, 'enabled' => 1));
            if ($nets) {
                foreach ($nets as $key => $net) {
                    if (!(isset($connect_array[$key]))) {
                        $connect_array[$key] = array(
                            'last_login_type' => $net,
                            'last_login_time' => null,
                            'identifier' => null,
                            'id_ets_solo_user' => null,
                        );
                    }
                }
            }
            $this->smarty->assign(array(
                'soloProvider' => $this->context->cookie->__get('soloProvider'),
                'socials' => Solo_defines::getInstance($this->context, $this)->getFields('socials'),
                'connects' => $connect_array,
            ));
        }
        return $this->display(__FILE__, 'social-page.tpl');
    }

    public function hookDisplayFooter()
    {
        $solo_new_account = $this->context->cookie->__get('solo_new_account');
        if ($solo_new_account) {
            $id_cart_rule = CartRule::getIdByCode($solo_new_account);
            $cartRule = new CartRule($id_cart_rule);
            if (Validate::isLoadedObject($cartRule)) {
                $customer = $this->context->customer;
                $title = ($res = Configuration::get('ETS_SOLO_POPUP_TITLE', $customer->id_lang, $customer->id_shop_group, $customer->id_shop)) ? $res : $this->l('You get discounted for your next order');
                $content = Configuration::get('ETS_SOLO_POPUP_CONTENT', $customer->id_lang, $customer->id_shop_group, $customer->id_shop);
                if (!$content)
                    $content = Configuration::get('ETS_SOLO_POPUP_CONTENT', $this->context->language->id, $this->context->shop->id_shop_group, $this->context->shop->id);
                if ($content) {
                    $content = $this->replaceCode($content, $cartRule);
                }
                $this->smarty->assign(array(
                    'content' => $content,
                    'title' => $title,
                ));
                $this->context->cookie->__unset('solo_new_account');
                return $this->display(__FILE__, 'popup.tpl');
            }
        }
        return $this->displayFrontNetworks(array(
            'hook' => 'foo'
        ));
    }

    public function getBreadcrumb()
    {
        $breadcrumb = $this->getBreadcrumbLinks();
        $breadcrumb['count'] = count($breadcrumb['links']);
        if ($this->is17)
            return $breadcrumb;
        else
            return $this->displayBreadcrumb($breadcrumb);
    }

    public function getBreadcrumbLinks()
    {
        $controller = ($controller = Tools::getValue('controller', false)) && Validate::isCleanHtml($controller) ? $controller : '';
        $breadcrumb = array();
        if ($this->is17) {
            $breadcrumb['links'][] = array(
                'title' => $this->l('Home'),
                'url' => $this->context->link->getPageLink('index', true),
            );
        }
        $breadcrumb['links'][] = array(
            'title' => $this->l('My account'),
            'url' => $this->context->link->getPageLink('my-account', true),
        );
        if ($controller == 'social') {
            $breadcrumb['links'][] = array(
                'title' => $this->l('Social networks'),
                'url' => $this->context->link->getModuleLink($this->name, 'social', array(), Tools::usingSecureMode() ? true : false),
            );
        }
        return $breadcrumb;
    }

    public function displayBreadcrumb($breadcrumb)
    {
        $this->smarty->assign('breadcrumb', $breadcrumb);
        return $this->display(__FILE__, 'breadcrumb.tpl');
    }

    public function hookDisplayLeftColumn()
    {
        $controller = ($controller = Tools::getValue('controller', false)) && Validate::isCleanHtml($controller) ? $controller : '';
        if ($controller == 'social')
            return false;
    }

    public function hookDisplaySoloPreview($params)
    {
        $params['hook'] = 'admin';
        return $this->displayFrontNetworks($params);
    }

    public function hookDisplayReassurance()
    {
        $controller = ($controller = Tools::getValue('controller')) && Validate::isCleanHtml($controller) ? $controller : '';
        if ($this->is17 && $controller == 'order') {
            return $this->displayFrontNetworks(array('hook' => 'slw')) . $this->displayFrontNetworks(array('hook' => 'lgp'));
        }
    }

    public function hookDisplayTop()
    {
        return (!$this->is17 && !$this->is16 ? $this->displayFrontNetworks(array('hook' => 'nav')) . $this->displayFrontNetworks(array('hook' => 'alw')) : '') . $this->displayFrontNetworks(array('hook' => 'slw'));
    }

    public function hookDisplayNav()
    {
        if ($this->is16) {
            return $this->displayFrontNetworks(array('hook' => 'nav')) . $this->displayFrontNetworks(array('hook' => 'alw'));
        }
    }

    public function hookDisplayNav2()
    {
        if ($this->is17) {
            return $this->displayFrontNetworks(array('hook' => 'nav')) . $this->displayFrontNetworks(array('hook' => 'alw'));
        }
    }

    public function hookDisplayCustomerLoginFormAfter()
    {
        return $this->displayFrontNetworks(array('hook' => 'lgp'));
    }

    public function hookDisplayCustomerAccountForm()
    {
        return $this->displayFrontNetworks(array('hook' => 'brp'));
    }

    public function hookDisplayCustomerAccountFormTop()
    {
        return $this->displayFrontNetworks(array('hook' => 'trp'));
    }

    public function hookDisplayAfterProductThumbs()
    {
        return $this->displayFrontNetworks(array('hook' => 'ptn'));
    }

    public function hookDisplayProductAdditionalInfo()
    {
        return $this->displayFrontNetworks(array('hook' => 'pai'));
    }

    public function hookDisplayShoppingCartFooter()
    {
        return $this->displayFrontNetworks(array('hook' => 'scf'));
    }

    public function hookDisplayRightColumnProduct()
    {
        return $this->displayFrontNetworks(array('hook' => 'prc'));
    }

    public function hookDisplayLeftColumnProduct()
    {
        return $this->displayFrontNetworks(array('hook' => 'plc'));
    }

    public function hookDisplaySoLoSocialLogin()
    {
        return $this->displayFrontNetworks(array('hook' => 'cus'));
    }

    public static function displayText($content, $tag, $attr_datas = array())
    {
        $text = '<' . $tag;
        if ($attr_datas) {
            foreach ($attr_datas as $key => $value) {
                if ($value === null)
                    $text .= ' ' . $key;
                else
                    $text .= ' ' . $key . '="' . $value . '"';
            }
        }
        if ($tag == 'img' || $tag == 'br' || $tag == 'path' || $tag == 'input')
            $text .= ' />';
        else
            $text .= '>';
        if ($tag && $tag != 'img' && $tag != 'input' && $tag != 'br' && !is_null($content))
            $text .= $content;
        if ($tag && $tag != 'img' && $tag != 'path' && $tag != 'input' && $tag != 'br')
            $text .= '<' . '/' . $tag . '>';
        return $text;
    }
}
