<?php
/**
* 2007-2018 PrestaShop.
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2018 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once(_PS_MODULE_DIR_.'wpheadertext/classes/WPHeaderTextClass.php');

class WPHeaderText extends Module
{
    public $_html = '';
    
    public function __construct()
    {
        $this->name = 'wpheadertext';
        $this->tab = 'front_office_features';
        $this->version = '1.1.3';
        $this->module_key = '3e8baf61b527a653e05591f6b916c142';
        $this->author = 'WEB-PLUS';
        $this->need_instance = 0;

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Text block in header');
        $this->description = $this->l('Display any content in header text block.');
        $this->ps_versions_compliancy = array('min' => '1.7.0.0', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        if (parent::install() && $this->registerHook('displayAfterBodyOpeningTag') && $this->registerHook('displayHeader')) {
            $res = Db::getInstance()->execute('
                CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'wpheadertext` (
                `id_wpheadertext` int(10) unsigned NOT NULL auto_increment,
                `id_shop` int(10) unsigned NOT NULL,
                `active` varchar(64) NULL,
                `box_bg` varchar(64) NULL,
                `box_brd` varchar(64) NULL,
                `cls_btn` varchar(64) NULL,
                `icon_c` varchar(64) NULL,            
                `icon_bg` varchar(64) NULL,
                `icon_c_h` varchar(64) NULL,
                `icon_bg_h` varchar(64) NULL,
                `icon_btn_c` varchar(64) NULL,
                `icon_btn_bg` varchar(64) NULL,
                `icon_btn_c_h` varchar(64) NULL,
                `icon_btn_bg_h` varchar(64) NULL,
                `text_size` int(2) NULL,
                `text_size_mobile` int(2) NULL,
                `wp_google_link` varchar(255) NULL,
                `wp_google_name` varchar(255) NULL,
                `mobile_sw` varchar(64) NULL,
                `googleyn` varchar(64) NULL,
                `wpheadertext_file` VARCHAR(100) NOT NULL,
                PRIMARY KEY (`id_wpheadertext`))
                ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8');

            if ($res) {
                $res &= Db::getInstance()->execute('
                    CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'wpheadertext_lang` (
                    `id_wpheadertext` int(10) unsigned NOT NULL,
                    `id_lang` int(10) unsigned NOT NULL,
                    `wpheadertext_text` text NOT NULL,
                    `wpheadertext_image_link` varchar(255) NOT NULL,
                    PRIMARY KEY (`id_wpheadertext`, `id_lang`))
                    ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8');
            }

            if ($res) {
                foreach (Shop::getShops(false) as $shop) {
                    $res &= $this->createExampleWPHeaderText($shop['id_shop']);
                }
            }

            return (bool)$res;
        }

        return false;
    }

    private function createExampleWPHeaderText($id_shop)
    {
        $wpheadertext = new WPHeaderTextClass();
        $wpheadertext->id_shop = (int) $id_shop;
        $wpheadertext->box_bg = '#e6e6e6';
        $wpheadertext->box_brd = '#cccccc';
        $wpheadertext->cls_btn = '1';
        $wpheadertext->icon_c = '#ffffff';
        $wpheadertext->icon_c_h = '#ffffff';
        $wpheadertext->icon_bg = '#555555';
        $wpheadertext->icon_bg_h = '#16A085';
        $wpheadertext->icon_btn_c = '#ffffff';
        $wpheadertext->icon_btn_bg = '#2E8ECE';
        $wpheadertext->icon_btn_c_h = '#ffffff';
        $wpheadertext->icon_btn_bg_h = '#449eda';
        $wpheadertext->text_size = '16';
        $wpheadertext->text_size_mobile = '14';
        $wpheadertext->wp_google_link = 'http://fonts.googleapis.com/css?family=Open+Sans:400,300,300italic,400italic,600,600italic,700,700italic&subset=latin,latin-ext';
        $wpheadertext->wp_google_name = '\'Open Sans\', sans-serif';
        $wpheadertext->mobile_sw = '1';
        $wpheadertext->active = '1';
        $wpheadertext->googleyn = '0';
        $wpheadertext->wpheadertext_file = '';

        foreach (Language::getLanguages(false) as $lang) {
            $wpheadertext->wpheadertext_text[$lang['id_lang']] = '<p>Display any content you like. This is a perfect place for news, advertisements, alerts, notices or anything else. Change text color directly in editor (with second icon from left in toolbar). See module manual for more tips.</p>';
        }
        $wpheadertext->wpheadertext_image_link[$lang['id_lang']] = 'http://www.prestashop.com';

        return $wpheadertext->add();
    }

    public function uninstall()
    {
        $res = Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'wpheadertext`');
        $res &= Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'wpheadertext_lang`');

        if (!$res || !parent::uninstall()) {
            return false;
        }
        return true;
    }

    private function renderForm()
    {
        $languages = Language::getLanguages(false);
        foreach ($languages as $k => $language) {
            $languages[$k]['is_default'] = (int) $language['id_lang'] == Configuration::get('PS_LANG_DEFAULT');
        }
        
        $id_shop = (int) $this->context->shop->id;
        $wpheadertext = WPHeaderTextClass::getByIdShop($id_shop);


        $file = dirname(__FILE__).'/views/img/'.$wpheadertext->wpheadertext_file.'';
   
        $wpheadertext_img = (is_file($file) ? '<img src="'.$this->_path.'views/img/'.$wpheadertext->wpheadertext_file.'">' : '');


        $this->fields_form[0]['form'] = array(
            'tinymce' => true,
            'legend' => array(
                'title' => $this->displayName,
                ),
            'input' => array(
                array(
                    'type' => 'switch',
                    'label' => $this->l('Active'),
                    'name' => 'active',
                    'desc' => $this->l('Choose if you want to show or hide the module.'),
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                            ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Header text content'),
                    'name' => 'wpheadertext_text',
                    'lang' => true,
                    'autoload_rte' => true,
                    'hint' => $this->l('Insert text, upload images or any other content'),
                    'cols' => 60,
                    'rows' => 30,
                    ),
                array(
                    'type' => 'file',
                    'label' => $this->l('Image'),
                    'name' => 'wpheadertext_image',
                    'desc' => $this->l('Upload an image in .jpg or .png format'),
                    'display_image' => true,
                    'image' => $wpheadertext_img,
                    'delete_url' => 'index.php?tab=AdminModules&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&deleteHeaderTextImage=1',
                    ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Image link'),
                    'name' => 'wpheadertext_image_link',
                    'lang' => true,
                    'size' => 33,
                    ),
                array(
                    'type' => 'color',
                    'label' => $this->l('Header block background'),
                    'desc' => $this->l('Default #e6e6e6'),
                    'name' => 'box_bg',
                    'size' => 30,
                    ),
                array(
                    'type' => 'color',
                    'label' => $this->l('Header block bottom border color'),
                    'desc' => $this->l('Default #cccccc'),
                    'name' => 'box_brd',
                    'size' => 30,
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Display Close button'),
                        'desc' => $this->l('If you choose No, then Close button will be hidden and box will be always visible.'),
                        'name' => 'cls_btn',
                        'values' => array(
                            array(
                                'id' => 'cls_btn_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                                ),
                            array(
                                'id' => 'cls_btn_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                                ),
                            ),
                        ),
                array(
                    'type' => 'color',
                    'label' => $this->l('Close icon color'),
                    'desc' => $this->l('Setup color of close icon using color picker. Default #ffffff'),
                    'name' => 'icon_c',
                    'size' => 30,
                    ),
                array(
                    'type' => 'color',
                    'label' => $this->l('Close icon background'),
                    'desc' => $this->l('Setup close icon background color using color picker. Default #555555'),
                    'name' => 'icon_bg',
                    'size' => 30,
                    ),
                array(
                    'type' => 'color',
                    'label' => $this->l('Close close icon color on hover'),
                    'desc' => $this->l('Default #ffffff'),
                    'name' => 'icon_c_h',
                    'size' => 30,
                    ),
                array(
                    'type' => 'color',
                    'label' => $this->l('Close close icon background on hover'),
                    'desc' => $this->l('Default #16A085'),
                    'name' => 'icon_bg_h',
                    'size' => 30,
                    ),
                array(
                    'type' => 'color',
                    'label' => $this->l('Button background'),
                    'desc' => $this->l('Background color of button inserted via editor. Default #2e8ece'),
                    'name' => 'icon_btn_bg',
                    'size' => 30,
                    ),
                array(
                    'type' => 'color',
                    'label' => $this->l('Button text color'),
                    'desc' => $this->l('Text color of button inserted via editor. Default #ffffff'),
                    'name' => 'icon_btn_c',
                    'size' => 30,
                    ),
                array(
                    'type' => 'color',
                    'label' => $this->l('Button background on hover'),
                    'name' => 'icon_btn_bg_h',
                    'size' => 30,
                    ),
                array(
                    'type' => 'color',
                    'label' => $this->l('Button text color on hover'),
                    'name' => 'icon_btn_c_h',
                    'size' => 30,
                    ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Display on mobile'),
                    'name' => 'mobile_sw',
                    'values' => array(
                        array(
                            'id' => 'mobile_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                            ),
                        array(
                            'id' => 'mobile_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Font size'),
                    'name' => 'text_size',
                    'desc' => $this->l('Size of text. Default: 16'),
                    'suffix_wrapper' => true,
                    'size' => 30,
                    'suffix' => 'pixels'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Font size'),
                    'name' => 'text_size_mobile',
                    'desc' => $this->l('Size of text. Default: 14'),
                    'suffix_wrapper' => true,
                    'size' => 30,
                    'suffix' => 'pixels'
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Enable Google fonts?'),
                    'name' => 'googleyn',
                    'values' => array(
                        array(
                            'id' => 'googleyn_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ),
                        array(
                            'id' => 'googleyn_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ),
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Google font url'),
                    'desc' => $this->l('Example: http://fonts.googleapis.com/css?family=Open+Sans:400,300,300italic,400italic,600,600italic,700,700italic&subset=latin,latin-ext ').' <br><a href="https://fonts.google.com/" target="_blank">'.$this->l('Check google fonts').'</a>',
                    'name' => 'wp_google_link',
                    'size' => 60
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Google font family'),
                    'name' => 'wp_google_name',
                    'desc' => $this->l('Example: \'Open Sans\', sans-serif '),
                    'suffix_wrapper' => true,
                    'size' => 30
                ),
                ),
            'submit' => array(
                'name' => 'submitUpdateWPHeaderText',
                'title' => $this->l('Save '),
                ),
            );

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = 'wpheadertext';
        $helper->identifier = $this->identifier;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->languages = $languages;
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = true;
        $helper->toolbar_scroll = true;
        $helper->title = $this->displayName;
        $helper->submit_action = 'submitUpdateWPHeaderText';

        return $helper;
    }
   
    public function getContent()
    {
        $this->postProcess();
        $this->generateCss();

        $helper = $this->renderForm();

        $id_shop = (int) $this->context->shop->id;
        $wpheadertext = WPHeaderTextClass::getByIdShop($id_shop);

        if (!$wpheadertext) {
            /* if wpheadertext ddo not exist for this shop => create a new example one */
            $this->createExampleWPHeaderText($id_shop);
        }

        foreach ($this->fields_form[0]['form']['input'] as $input) {
            /* fill all form fields */
            if ($input['name'] != 'wpheadertext_image') {
                $helper->fields_value[$input['name']] = $wpheadertext->{$input['name']};
            }
        }

        $file = dirname(__FILE__).'/views/img/wpheadertext_image_'.(int) $id_shop.'.jpg';
        $helper->fields_value['wpheadertext_image']['image'] = (file_exists($file) ? '<img src="'.$this->_path.'views/img/wpheadertext_image_'.(int) $id_shop.'.jpg">' : '');
        if ($helper->fields_value['wpheadertext_image'] && file_exists($file)) {
            $helper->fields_value['wpheadertext_image']['size'] = filesize($file) / 1000;
        }

        $this->_html .= $helper->generateForm($this->fields_form);

        return $this->_html;
    }

    public function postProcess()
    {
        $errors = array();
        $id_shop = (int) $this->context->shop->id;
        $wpheadertext = WPHeaderTextClass::getByIdShop($id_shop);

        /* Delete image */
        if (Tools::isSubmit('deleteHeaderTextImage')) {
            if (!file_exists(dirname(__FILE__).'/views/img/'.$wpheadertext->wpheadertext_file.'')) {
                $errors[] = $this->l('This action cannot be made.');
            } else {
                unlink(dirname(__FILE__).'/views/img/'.$wpheadertext->wpheadertext_file.'');
                $wpheadertext->wpheadertext_file = '';
                $wpheadertext->save();

                Tools::clearSmartyCache();
                Media::clearCache();
                /* Following confirmation message is not visible because we make redirection (neccessary to get rid of deleted image in BO, otherwise client may be confused */
                $this->_html .= $this->displayConfirmation($this->l('Image deleted'));
                Tools::redirectAdmin('index.php?tab=AdminModules&configure='.$this->name.'&token='.Tools::getAdminToken('AdminModules'.(int) Tab::getIdFromClassName('AdminModules').(int) $this->context->employee->id));
            }
            if (count($errors)) {
                foreach ($errors as $err) {
                    $this->_html .= $this->displayError($err);
                }
            }
        }

        /* upload the image */
        if (isset($_FILES['wpheadertext_image']) && isset($_FILES['wpheadertext_image']['tmp_name']) && !empty($_FILES['wpheadertext_image']['tmp_name'])) {
            if ($error = ImageManager::validateUpload($_FILES['wpheadertext_image'], 4000000)) {
                return $error;
            } else {
                $ext = Tools::substr($_FILES['wpheadertext_image']['name'], strrpos($_FILES['wpheadertext_image']['name'], '.') + 1);
                $file_name = md5($_FILES['wpheadertext_image']['name']).'.'.$ext;

                if (!move_uploaded_file($_FILES['wpheadertext_image']['tmp_name'], dirname(__FILE__).DIRECTORY_SEPARATOR.'views/img'.DIRECTORY_SEPARATOR.$file_name)) {
                    return $this->displayError($this->trans('An error occurred while attempting to upload the file.', array(), 'Admin.Notifications.Error'));
                } else {
                    if ($wpheadertext->wpheadertext_file != $file_name) {
                        @unlink(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'views/img' . DIRECTORY_SEPARATOR . $wpheadertext->wpheadertext_file);
                    }
                    $wpheadertext->wpheadertext_file = $file_name;
                    $wpheadertext->save();
                }
            }
        }

        if (file_exists(dirname(__FILE__).'/views/img/wpheadertext_image_'.(int) $id_shop.'.jpg')) {
            list($width, $height, $type, $attr) = getimagesize(dirname(__FILE__).'/views/img/wpheadertext_image_'.(int) $id_shop.'.jpg');
        }
        
        if (Tools::isSubmit('submitUpdateWPHeaderText')) {
            $id_shop = (int) $this->context->shop->id;
            $wpheadertext = WPHeaderTextClass::getByIdShop($id_shop);

            $languages = Language::getLanguages(false);
            foreach ($languages as $language) {
                if (Tools::strlen(Tools::getValue('wpheadertext_image_link_'.$language['id_lang'])) > 0 && !Validate::isUrl(Tools::getValue('wpheadertext_image_link_'.$language['id_lang']))) {
                    $errors[] = $this->l('The URL format is not correct.');
                }
            }

            if (Tools::strlen(Tools::getValue('box_bg')) > 0 && !Validate::isColor(Tools::getValue('box_bg'))) {
                $errors[] = $this->l('Header block background value is not valid, please use color picker to select color.');
            }
            if (Tools::strlen(Tools::getValue('box_brd')) > 0 && !Validate::isColor(Tools::getValue('box_brd'))) {
                $errors[] = $this->l('Header block bottom border value is not valid, please use color picker to select color.');
            }
            if (Tools::strlen(Tools::getValue('icon_c')) > 0 && !Validate::isColor(Tools::getValue('icon_c'))) {
                $errors[] = $this->l('Icon color value is not valid, please use color picker to select color.');
            }
            if (Tools::strlen(Tools::getValue('icon_bg')) > 0 && !Validate::isColor(Tools::getValue('icon_bg'))) {
                $errors[] = $this->l('Icon background value is not valid, please use color picker to select color.');
            }
            if (Tools::strlen(Tools::getValue('icon_c_h')) > 0 && !Validate::isColor(Tools::getValue('icon_c_h'))) {
                $errors[] = $this->l('Icon color on hover value is not valid, please use color picker to select color.');
            }
            if (Tools::strlen(Tools::getValue('icon_bg_h')) > 0 && !Validate::isColor(Tools::getValue('icon_bg_h'))) {
                $errors[] = $this->l('Icon background on hover value is not valid, please use color picker to select color.');
            }
            if (Tools::strlen(Tools::getValue('icon_btn_bg')) > 0 && !Validate::isColor(Tools::getValue('icon_btn_bg'))) {
                $errors[] = $this->l('Button background value is not valid, please use color picker to select color.');
            }
            if (Tools::strlen(Tools::getValue('icon_btn_c')) > 0 && !Validate::isColor(Tools::getValue('icon_btn_c'))) {
                $errors[] = $this->l('Button text color value is not valid, please use color picker to select color.');
            }
            if (Tools::strlen(Tools::getValue('icon_btn_bg_h')) > 0 && !Validate::isColor(Tools::getValue('icon_btn_bg_h'))) {
                $errors[] = $this->l('Button background on hover value is not valid, please use color picker to select color.');
            }
            if (Tools::strlen(Tools::getValue('icon_btn_c_h')) > 0 && !Validate::isColor(Tools::getValue('icon_btn_c_h'))) {
                $errors[] = $this->l('Button text color on hover  value is not valid, please use color picker to select color.');
            }
            if (Tools::getValue('googleyn') == 1  && Tools::strlen(Tools::getValue('wp_google_link')) == '') {
                $errors[] = $this->l('Please fill Google font URL.');
            }
            if (Tools::getValue('googleyn') == 1  && Tools::strlen(Tools::getValue('wp_google_name')) == '') {
                $errors[] = $this->l('Please fill Google font family.');
            }
            if (!Validate::isInt(Tools::getValue('text_size')) || Tools::getValue('text_size') <= 0) {
                $errors[] = $this->l('Text size must be number higher then 0.');
            }
            if (!Validate::isInt(Tools::getValue('text_size_mobile')) || Tools::getValue('text_size_mobile') <= 0) {
                $errors[] = $this->l('Text size on mobile must be number higher then 0.');
            }

            if (!count($errors)) {
            /* save colors */
                $wpheadertext->box_bg = Tools::getValue('box_bg');
                $wpheadertext->box_brd = Tools::getValue('box_brd');
                $wpheadertext->cls_btn = Tools::getValue('cls_btn');
                $wpheadertext->icon_c = Tools::getValue('icon_c');
                $wpheadertext->icon_bg = Tools::getValue('icon_bg');
                $wpheadertext->icon_c_h = Tools::getValue('icon_c_h');
                $wpheadertext->icon_bg_h = Tools::getValue('icon_bg_h');
                $wpheadertext->icon_btn_bg = Tools::getValue('icon_btn_bg');
                $wpheadertext->icon_btn_c = Tools::getValue('icon_btn_c');
                $wpheadertext->icon_btn_bg_h = Tools::getValue('icon_btn_bg_h');
                $wpheadertext->icon_btn_c_h = Tools::getValue('icon_btn_c_h');
                $wpheadertext->text_size = Tools::getValue('text_size');
                $wpheadertext->text_size_mobile = Tools::getValue('text_size_mobile');
                $wpheadertext->mobile_sw = Tools::getValue('mobile_sw');
                $wpheadertext->active = Tools::getValue('active');
                $wpheadertext->googleyn = Tools::getValue('googleyn');
                $wpheadertext->wp_google_name = Tools::getValue('wp_google_name');
                $wpheadertext->wp_google_link = Tools::getValue('wp_google_link');
           
                $wpheadertext->copyFromPost();
                if (empty($wpheadertext->id_shop)) {
                    $wpheadertext->id_shop = (int) $id_shop;
                }

                $wpheadertext->save();
                Tools::clearSmartyCache();
                Media::clearCache();
            
                $this->_html .= $this->displayConfirmation($this->l('Settings updated'));
            } else {
                foreach ($errors as $err) {
                    $this->_html .= $this->displayError($err);
                }
            }
        }

        return true;
    }

    public function hookdisplayAfterBodyOpeningTag($params)
    {
        $id_shop = (int) $this->context->shop->id;
        $wpheadertext = WPHeaderTextClass::getByIdShop($id_shop);

        if (!$wpheadertext) {
            return;
        }

        $wpheadertext = new WPHeaderTextClass((int) $wpheadertext->id, $this->context->language->id);
        if (!$wpheadertext) {
            return;
        }
        if (!$wpheadertext->active) {
            return;
        }

        $image_path = $this->_path . 'views/img/' . $wpheadertext->wpheadertext_file;
        // Check if image path is a valid file and assign new variable if not
        if (!file_exists($image_path) || !is_file($image_path)) {
            $wpheadertextIsFile = 1; 
        }

        if (!$this->isCached('wpheadertext.tpl', $this->getCacheId())) {
            $this->smarty->assign(array(
                'wpheadertext' => $wpheadertext,
                'wpheadertextIsFile' => $wpheadertextIsFile,
                'image_path' => $image_path,
                ));
        }

        if (isset($_COOKIE['wpheadertext']) && $wpheadertext->cls_btn == 1) {
            return;
        }

        return $this->display(__FILE__, 'wpheadertext.tpl', $this->getCacheId());
    }

    public function hookDisplayHeader()
    {
        $this->context->controller->registerStylesheet('modules-wpheadertext', 'modules/'.$this->name.'/views/css/wpheadertext.css', array('media' => 'all', 'priority' => 150));
        $this->context->controller->registerJavascript('modules-wpheadertext', 'modules/'.$this->name.'/views/js/wpheadertext.js', array('position' => 'bottom', 'priority' => 150));

        if (Shop::getContext() == Shop::CONTEXT_GROUP) {
            $this->context->controller->registerStylesheet('modules-wpheadertext-g', 'modules/'.$this->name.'/views/css/wpheadertext_g_'.(int) $this->context->shop->getContextShopGroupID().'.css', array('media' => 'all', 'priority' => 150));
        } elseif (Shop::getContext() == Shop::CONTEXT_SHOP) {
            $this->context->controller->registerStylesheet('modules-wpheadertext-s', 'modules/'.$this->name.'/views/css/wpheadertext_s_'.(int) $this->context->shop->getContextShopID().'.css', array('media' => 'all', 'priority' => 150));
        }

        $id_shop = (int) $this->context->shop->id;
        $wpheadertext = WPHeaderTextClass::getByIdShop($id_shop);
        $wp_google_link = $wpheadertext->wp_google_link;
        $googleyn = $wpheadertext->googleyn;

        if ($googleyn == 1) {
            if (!$this->isCached('wpheadertext-header.tpl', $this->getCacheId())) {
                $this->smarty->assign(array(
                  'wp_google_link' => str_replace(array('http://','https://'), '', $wp_google_link),
                  'googleyn' => $googleyn,
                ));
            }
            return $this->display(__FILE__, 'wpheadertext-header.tpl', $this->getCacheId());
        }
    }

    public function generateCss()
    {
        $id_shop = (int) $this->context->shop->id;
        $css = '';

        $wpheadertext = WPHeaderTextClass::getByIdShop($id_shop);
        $box_bg = $wpheadertext->box_bg;
        $box_brd = $wpheadertext->box_brd;
        $icon_c = $wpheadertext->icon_c;
        $icon_bg = $wpheadertext->icon_bg;
        $icon_c_h = $wpheadertext->icon_c_h;
        $icon_bg_h = $wpheadertext->icon_bg_h;
        $icon_btn_bg = $wpheadertext->icon_btn_bg;
        $icon_btn_c = $wpheadertext->icon_btn_c;
        $icon_btn_bg_h = $wpheadertext->icon_btn_bg_h;
        $icon_btn_c_h = $wpheadertext->icon_btn_c_h;
        $text_size = $wpheadertext->text_size;
        $text_size_mobile = $wpheadertext->text_size_mobile;
        $googleyn = $wpheadertext->googleyn;
        $wp_google_name = $wpheadertext->wp_google_name;

        if (Shop::getContext() == Shop::CONTEXT_GROUP) {
            if ($googleyn == 1) {
                $css .= '#wpalert-text, #wpalert-text p, #wpalert-text h1, #wpalert-text h2, #wpalert-text h3, #wpalert-text h4  {font-family: '.$wp_google_name.';}';
            }
            $css .= '#wpalert-text, #wpalert-text p {font-size: '.$text_size.'px;}';
            $css .= '@media (max-width: 991px) { #wpalert-text, #wpalert-text p {font-size: '.$text_size_mobile.'px; }}';
            $css .= '#wpalert-header {background: '.$box_bg.';}';
            $css .= '#wpalert-header {border-bottom: 1px solid '.$box_brd.';}';
            $css .= '.wpalert-header-close svg {fill: '.$icon_c.';}';
            $css .= '.wpalert-header-close svg:hover {fill: '.$icon_c_h.';}';
            $css .= '.wpalert-header-close {background: '.$icon_bg.';}';
            $css .= '.wpalert-header-close:hover {background: '.$icon_bg_h.';}';
            $css .= '#wpalert-text .btn-default {background: '.$icon_btn_bg.'; color: '.$icon_btn_c.';}';
            $css .= '#wpalert-text .btn-default:hover {background: '.$icon_btn_bg_h.'; color: '.$icon_btn_c_h.';}';
            $this->cssFile = $this->local_path.'views/css/wpheadertext_g_'.(int) $this->context->shop->getContextShopGroupID().'.css';
            $fw = fopen($this->cssFile, 'w') or die("can't open file");
            fwrite($fw, $css);
            fclose($fw);
        } elseif (Shop::getContext() == Shop::CONTEXT_SHOP) {
            if ($googleyn == 1) {
                $css .= '#wpalert-text, #wpalert-text p, #wpalert-text h1, #wpalert-text h2, #wpalert-text h3, #wpalert-text h4  {font-family: '.$wp_google_name.';}';
            }
            $css .= '#wpalert-text, #wpalert-text p {font-size: '.$text_size.'px;}';
            $css .= '@media (max-width: 991px) { #wpalert-text, #wpalert-text p {font-size: '.$text_size_mobile.'px; }}';
            $css .= '#wpalert-header {background: '.$box_bg.';}';
            $css .= '#wpalert-header {border-bottom: 1px solid '.$box_brd.';}';
            $css .= '.wpalert-header-close svg {fill: '.$icon_c.';}';
            $css .= '.wpalert-header-close svg {fill: '.$icon_c.';}';
            $css .= '.wpalert-header-close svg:hover {fill: '.$icon_c_h.';}';
            $css .= '.wpalert-header-close {background: '.$icon_bg.';}';
            $css .= '.wpalert-header-close:hover {background: '.$icon_bg_h.';}';
            $css .= '#wpalert-text .btn-default {background: '.$icon_btn_bg.'; color: '.$icon_btn_c.';}';
            $css .= '#wpalert-text .btn-default:hover {background: '.$icon_btn_bg_h.'; color: '.$icon_btn_c_h.';}';
            $this->cssFile = $this->local_path.'views/css/wpheadertext_s_'.(int) $this->context->shop->getContextShopID().'.css';
            $fw = fopen($this->cssFile, 'w') or die("can't open file");
            fwrite($fw, $css);
            fclose($fw);
        }
    }
}
