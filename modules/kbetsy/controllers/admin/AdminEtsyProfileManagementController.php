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

require_once(_PS_MODULE_DIR_ . 'kbetsy/classes/EtsyModule.php');
require_once(_PS_MODULE_DIR_ . 'kbetsy/classes/EtsyProfiles.php');
require_once(_PS_MODULE_DIR_ . 'kbetsy/classes/EtsyShippingTemplates.php');
require_once(_PS_MODULE_DIR_ . 'kbetsy/classes/EtsyProfileCategory.php');

class AdminEtsyProfileManagementController extends ModuleAdminController
{

    public function __construct()
    {
        $this->name = 'EtsyProfileManagement';
        $this->context = Context::getContext();
        $this->bootstrap = true;
        $this->table = 'etsy_profiles';
        $this->className = 'EtsyProfiles';
        $this->identifier = 'id_etsy_profiles';

        parent::__construct();
        $this->fields_list = array(
            'id_etsy_profiles' => array(
                'title' => $this->module->l('ID', 'AdminEtsyProfileManagementController'),
                'align' => 'left',
                'class' => 'fixed-width-xs'
            ),
            'profile_title' => array(
                'title' => $this->module->l('Profile Title', 'AdminEtsyProfileManagementController'),
            ),
            'category_name' => array(
                'title' => $this->module->l('Etsy Category', 'AdminEtsyProfileManagementController'),
                'align' => 'left'
            ),
            'shipping_template_title' => array(
                'title' => $this->module->l('Shipping Template', 'AdminEtsyProfileManagementController'),
                'align' => 'left'
            ),
            'date_added' => array(
                'title' => $this->module->l('Added On', 'AdminEtsyProfileManagementController'),
                'align' => 'left',
                'type' => 'datetime',
            ),
            'date_updated' => array(
                'title' => $this->module->l('Updated On', 'AdminEtsyProfileManagementController'),
                'align' => 'left',
                'type' => 'datetime',
            )
        );

        $this->_select = 'ec.category_name, st.shipping_template_title';
        /* Start-MK made changes on 23-11-2017 to fetch records based on category mapping table */
        $this->_join = '
                LEFT JOIN ' . _DB_PREFIX_ . 'etsy_category_mapping ecm ON (ecm.id_etsy_profiles = a.id_etsy_profiles)
                LEFT JOIN ' . _DB_PREFIX_ . 'etsy_categories ec ON (ec.category_code = ecm.etsy_category_code)
                LEFT JOIN ' . _DB_PREFIX_ . 'etsy_shipping_templates st ON (st.id_etsy_shipping_templates = a.id_etsy_shipping_templates)';
        $this->_group = ' group by ecm.id_etsy_profiles';
        /* End-MK made changes on 23-11-2017 to fetch records based on category mapping table */

        //This is to show notification messages to admin
        if (!Tools::isEmpty(trim(Tools::getValue('etsyConf')))) {
            new EtsyModule(Tools::getValue('etsyConf'), 'conf');
        }

        if (!Tools::isEmpty(trim(Tools::getValue('etsyError')))) {
            new EtsyModule(Tools::getValue('etsyError'), 'error');
        }
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->addJS($this->getModuleDirUrl() . 'kbetsy/views/js/script.js');
        $this->addJS($this->getModuleDirUrl() . 'kbetsy/views/js/velovalidation.js');
        $this->addCSS($this->getModuleDirUrl() . 'kbetsy/views/css/style.css');
        $this->addJqueryPlugin('autocomplete');
    }

    public function init()
    {
        parent::init();

        if (!Tools::isEmpty(trim(Tools::getValue('ajaxPropertiesList'))) && !Tools::isEmpty(trim(Tools::getValue('category_code')))) {
            return $this->ajaxGetPropertiesList();
        } else if (!Tools::isEmpty(trim(Tools::getValue('ajaxCheckCategoryExist')))) {
            return $this->ajaxCheckCategoryExist();
        }
    }

    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        $etsy_categories = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'etsy_categories WHERE parent_id = 0 ORDER BY category_name ASC');
        if (EtsyShippingTemplates::getTotalTeamplates() <= 0) {
            $this->context->smarty->assign("message", $this->module->l('Shipping template has not been added yet. Kindly go to Shipping Template Page & Click on the "Add new" icon to add the same OR click on the "Sync Shipping Templates" icon to download the existing shipping templates from the Etsy account.', 'AdminEtsyShopSectionController'));
            $this->context->smarty->assign("type", "alert-info");
            $this->context->smarty->assign("KbMessageLink", '');
            return $this->context->smarty->fetch(_PS_MODULE_DIR_ . "kbetsy/views/templates/admin/msgs.tpl");
        } else if (!empty($etsy_categories)) {
            $this->context->smarty->assign("etsy_categories", $etsy_categories);
            $this->context->smarty->assign("categories_imported", 'yes');
            $this->context->smarty->assign("list", parent::renderList());
            $this->context->smarty->assign('ajax_category_action', $this->context->link->getAdminlink('AdminEtsyProfileManagement'));
            return $this->context->smarty->fetch(_PS_MODULE_DIR_ . "kbetsy/views/templates/admin/profile_list.tpl");
        } else {
            $secure_key = Configuration::get('KBETSY_SECURE_KEY');
            $this->context->smarty->assign("categories_imported", 'no');
            $this->context->smarty->assign("type", 'alert-warning');
            $this->context->smarty->assign("message", sprintf($this->l('Etsy categories is not imported yet. <a href="%s" target="_blank">Click here</a> to import the categories to continue.'), $this->context->link->getModuleLink('kbetsy', 'cron', array('action' => 'syncCategory', 'secure_key' => $secure_key))));
            $this->context->smarty->assign("KbMessageLink", $this->context->link->getModuleLink('kbetsy', 'cron', array('action' => 'syncCategory', 'secure_key' => $secure_key)));
            return $this->context->smarty->fetch(_PS_MODULE_DIR_ . "kbetsy/views/templates/admin/msgs.tpl");
        }
    }

    public function renderForm()
    {
        $categoryTreeSelection = array();
        $product_mapping = array();
        $custom_pricing_array = array();
        $product_selection_type = array();
        
        $is_size_chart_image_exists = 0;
        if (!Tools::isEmpty(trim(Tools::getValue('id_etsy_profiles')))) {
            $getProfileDetails = EtsyProfiles::getProfileDetails(Tools::getValue('id_etsy_profiles'));
            /* Start-MK made changes on 23-11-2017 to persist category */
            $getCategoryMapping = EtsyProfileCategory::getProfileCategory(Tools::getValue('id_etsy_profiles'));
            /* End-MK made changes on 23-11-2017 to persist category */
            if (isset($getProfileDetails)) {
                $this->fields_value = array(
                    'id_etsy_profiles' => Tools::getValue('id_etsy_profiles'),
                    'profile_title' => $getProfileDetails[0]['profile_title'],
                    'id_etsy_shipping_templates' => $getProfileDetails[0]['id_etsy_shipping_templates'],
                    'who_made' => $getProfileDetails[0]['who_made'],
                    'when_made' => $getProfileDetails[0]['when_made'],
                    'recipient' => $getProfileDetails[0]['recipient'],
                    'currency' => $getProfileDetails[0]['etsy_currency'],
                    'occassion' => $getProfileDetails[0]['occassion'],
                    'feature' => $getProfileDetails[0]['material_feature'],
                    'custom_pricing' => $getProfileDetails[0]['custom_pricing'],
                    'size_chart_image' => $getProfileDetails[0]['size_chart_image'],
                    'id_etsy_shop_section' => $getProfileDetails[0]['id_etsy_shop_section'],
                    'etsy_product_type' => $getProfileDetails[0]['etsy_product_type'],
                    'kbetsy_selected_products' => $getProfileDetails[0]['etsy_selected_products'],
                    /* Start-MK made changes on 23-11-2017 to persist etsy category and property */
                    'etsy_category_code' => (!empty($getCategoryMapping) && is_array($getCategoryMapping)) ? $getCategoryMapping[0]['etsy_category_code'] : '',
                    'property[]' => explode(',', $getProfileDetails[0]['property']),
                        /* End-MK made changes on 23-11-2017 to persist etsy category and property */
                );
                $custom_pricing_array['price_reduction'] = $getProfileDetails[0]['price_reduction'];
                $custom_pricing_array['price_type'] = $getProfileDetails[0]['price_type'];
                $custom_pricing_array['custom_price'] = $getProfileDetails[0]['custom_price'];

                if ($getProfileDetails[0]['is_customizable']) {
                    $this->fields_value['is_customizable_1'] = 'on';
                }

                if ($getProfileDetails[0]['is_supply']) {
                    $this->fields_value['is_supply_1'] = 'on';
                }
                /* Start-MK made changes on 23-11-2017 to persist category */
                if (!empty($getCategoryMapping[0]['prestashop_category'])) {
                    $categoryTreeSelection = explode(",", $getCategoryMapping[0]['prestashop_category']);
                }
                /* end-MK made changes on 23-11-2017 to persist category */
            }

            /* Exclude product is not required as its moved to product listing page
              $kb_product_mapping = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'etsy_exclude_product where id_profiles=' . (int) Tools::getValue('id_etsy_profiles') . ' AND id_shop=' . (int) $this->context->shop->id);
              if (!empty($kb_product_mapping)) {
              foreach ($kb_product_mapping as $key => $mapping) {
              $product_mapping[$key] = $mapping;
              $product_mapping[$key]['product'] = new Product($mapping['id_product'], false, $this->context->language->id);
              }
              }
             */

            if (!empty($getProfileDetails[0]['etsy_selected_products'])) {
                $kb_product_mapping = explode("-", $getProfileDetails[0]['etsy_selected_products']);
                foreach ($kb_product_mapping as $key => $mapping) {
                    $product_info = new Product($mapping, false, $this->context->language->id);
                    if (!empty($product_info->id)) {
                        $product_mapping[$key]['product'] = $product_info;
                    }
                }
            }
            
            /*
             * to check if image exists already
             */
            if ((int)Tools::getValue('id_etsy_profiles') != 0) {
                $id_profile = (int)Tools::getValue('id_etsy_profiles');
                $exist_file = _PS_MODULE_DIR_. 'kbetsy/views/img/profile/'.$id_profile. '.*';
                $match1 = glob($exist_file);
                if (isset($match1) && count($match1) > 0) {
                    $ban = explode('/', $match1[0]);
                    $ban = end($ban);
                    $ban = trim($ban);
                    $img_url = $this->getModuleDirUrl() . 'kbetsy/views/img/profile/' . $ban;
                    if (file_exists($match1[0])) {
                        $is_size_chart_image_exists = 1;
                    }
                }
            }
        }
        /*
         * Start: Added By Anshul Mittal to fix the category selection issue on 30/01/2020
         */
        $sql = 'SELECT category_code FROM '._DB_PREFIX_.'etsy_categories';
        $categories_code = Db::getInstance()->executeS($sql);
        $query = '';
        foreach ($categories_code as $code) {
            $query .= '(category_code = '.$code['category_code'].') OR ';
        }
        /*
         * End: Added By Anshul Mittal to fix the category selection issue on 30/01/2020
         */
        $etsy_categories = Db::getInstance()->executeS("SELECT category_code, category_name FROM " . _DB_PREFIX_ . "etsy_categories WHERE last_level = 1 OR ".$query." parent_id = 0 ORDER BY category_name ASC", true, false);
        $etsyCategoriesList = array();
        if ($etsy_categories) {
            foreach ($etsy_categories as $etsy_category) {
                $category_name = $etsy_category['category_name'];
                $etsyCategoriesList[] = array(
                    'id_option' => $etsy_category['category_code'],
                    'name' => $category_name
                );
            }
        }

        $product_selection_type[] = array("id" => "0", "name" => $this->l("Category"));
        $product_selection_type[] = array("id" => "1", "name" => $this->l("Product"));

        //Prepare array of Shipping Templates
        $getShippingTemplates = EtsyShippingTemplates::getShippingTemplateDetails('', 'id_etsy_shipping_templates, shipping_template_title, shipping_template_id, delete_flag');

        $shippingTemplatesList = array();
        if ($getShippingTemplates) {
            foreach ($getShippingTemplates as $getShippingTemplates) {
                if (!empty($getShippingTemplates['shipping_template_id']) && !$getShippingTemplates['delete_flag']) {
                    $shippingTemplatesList[] = array(
                        'id_option' => $getShippingTemplates['id_etsy_shipping_templates'],
                        'name' => $getShippingTemplates['shipping_template_title']
                    );
                }
            }
        }

        $store_currencies = Currency::getCurrenciesByIdShop(Context::getContext()->shop->id);
        $etsycurrency = array();
        foreach ($store_currencies as $currency) {
            $etsycurrency[] = array(
                'id_option' => $currency['iso_code'],
                'name' => $currency['name']
            );
        }

        $featureList = array();
        $getFeatureListSQL = "SELECT id_feature, name FROM " . _DB_PREFIX_ . "feature_lang where id_lang =" . $this->context->language->id;
        $getFeatureList = Db::getInstance()->executeS($getFeatureListSQL, true, false);
        $featureList[] = array(
            'id_option' => '',
            'name' => $this->l('Select Feature'),
        );
        foreach ($getFeatureList as $feature) {
            $featureList[] = array(
                'id_option' => $feature['id_feature'],
                'name' => $feature['name']
            );
        }

        $etsyShopSection = array();
        $getShopSectionSQL = "SELECT id_etsy_shop_section, shop_section_title FROM " . _DB_PREFIX_ . "etsy_shop_section where delete_flag = 0 and shop_section_id IS NOT NULL AND shop_section_id != '' AND shop_section_id != 0";
        $getShopSectionList = Db::getInstance()->executeS($getShopSectionSQL, true, false);
        $etsyShopSection[] = array(
            'id_option' => '',
            'name' => $this->l('Select Shop Section'),
        );
        foreach ($getShopSectionList as $feature) {
            $etsyShopSection[] = array(
                'id_option' => $feature['id_etsy_shop_section'],
                'name' => $feature['shop_section_title']
            );
        }


        /* Start-MK made changes on 23-11-2017 to creating property array */
        $etsyProperty = array(
            array(
                'id' => 'taxonomy_id',
                'name' => $this->l('Etsy Category'),
            ),
            array(
                'id' => 'shipping_template_id',
                'name' => $this->l('Shipping Template'),
            ),
            array(
                'id' => 'recipient',
                'name' => $this->l('Recipient'),
            ),
            array(
                'id' => 'occasion',
                'name' => $this->l('Occasion'),
            ),
        );

        /* Start-MK made changes on 23-11-2017 to creating property array */
        //Get Store root category
        $root = Category::getRootCategory();

        //Generating the tree for the first column
        $tree = new HelperTreeCategories('prestashop_category'); //The string in param is the ID used by the generated tree
        $tree->setUseCheckBox(true)
                ->setAttribute('is_category_filter', $root->id)
                ->setRootCategory($root->id)
                ->setSelectedCategories($categoryTreeSelection)
                ->setInputName('prestashop_category')
                //->setDisabledCategories($categoryListDisabled)
                ->setFullTree(true); //Set the name of input. The option "name" of $fields_form doesn't seem to work with "categories_select" type

        $categoryTreePresta = $tree->render();

        //Prepare array of Who Made
        $whoMadeOptions = array(
            'i_did' => $this->module->l('I Did', 'AdminEtsyProfileManagementController'),
            'collective' => $this->module->l('Collective', 'AdminEtsyProfileManagementController'),
            'someone_else' => $this->module->l('Someone Else', 'AdminEtsyProfileManagementController')
        );

        if ($whoMadeOptions) {
            $whoMadeList = array();
            foreach ($whoMadeOptions as $key => $value) {
                $whoMadeList[] = array(
                    'id_option' => $key,
                    'name' => $value
                );
            }
        }

        //Prepare array of When Made
        $whenMadeOptions = array(
            'made_to_order' => $this->module->l('Made to Order', 'AdminEtsyProfileManagementController'),
            '2020_'.date("Y") => '2020 - '.date("Y"),
            '2010_2019' => '2010 - 2019',
            '2001_2009' => '2001 - 2009',
            'before_2001' => $this->module->l('Before', 'AdminEtsyProfileManagementController') . ' 2001',
            '2000_2000' => '2000',
            '1990s' => '1990s',
            '1980s' => '1980s',
            '1970s' => '1970s',
            '1960s' => '1960s',
            '1950s' => '1950s',
            '1940s' => '1940s',
            '1930s' => '1930s',
            '1920s' => '1920s',
            '1910s' => '1910s',
            '1900s' => '1900s',
            '1800s' => '1800s',
            '1700s' => '1700s',
            'before_1700' => $this->module->l('Before', 'AdminEtsyProfileManagementController') . ' 1700'
        );

        if ($whenMadeOptions) {
            $whenMadeList = array();
            foreach ($whenMadeOptions as $key => $value) {
                $whenMadeList[] = array(
                    'id_option' => $key,
                    'name' => $value
                );
            }
        }
        $lang_data = new Language(Configuration::get('etsy_default_lang'));
        //Prepare array of Recipient
        //de,en,es,fr,it,ja,nl,pt,ru
        switch (Tools::strtolower($lang_data->iso_code)) {
            case 'de':
                $recipientOptions = array(
                    '' => $this->module->l('Select Recipient', 'AdminEtsyProfileManagementController'),
                    'mnner' => $this->module->l('Men', 'AdminEtsyProfileManagementController'),
                    'frauen' => $this->module->l('Women', 'AdminEtsyProfileManagementController'),
                    'unisex_erwachsene' => $this->module->l('Unisex Adults', 'AdminEtsyProfileManagementController'),
                    'teenager__jungen' => $this->module->l('Teen Boys', 'AdminEtsyProfileManagementController'),
                    'teenager__mdchen' => $this->module->l('Teen Girls', 'AdminEtsyProfileManagementController'),
                    'jugendliche' => $this->module->l('Teens', 'AdminEtsyProfileManagementController'),
                    'jungs' => $this->module->l('Boys', 'AdminEtsyProfileManagementController'),
                    'mdchen' => $this->module->l('Girls', 'AdminEtsyProfileManagementController'),
                    'kinder' => $this->module->l('Children', 'AdminEtsyProfileManagementController'),
                    'babys__jungen' => $this->module->l('Baby Boys', 'AdminEtsyProfileManagementController'),
                    'babys__mdchen' => $this->module->l('Baby Girls', 'AdminEtsyProfileManagementController'),
                    'babys' => $this->module->l('Babies', 'AdminEtsyProfileManagementController'),
                    'vgel' => $this->module->l('Birds', 'AdminEtsyProfileManagementController'),
                    'katzen' => $this->module->l('Cats', 'AdminEtsyProfileManagementController'),
                    'hunde' => $this->module->l('Dogs', 'AdminEtsyProfileManagementController'),
                    'haustiere' => $this->module->l('Pets', 'AdminEtsyProfileManagementController'),
                    'not_specified' => $this->module->l('Not Specified', 'AdminEtsyProfileManagementController')
                );

                $occassionOptions = array(
                    '' => $this->module->l('Select Occasion', 'AdminEtsyProfileManagementController'),
                    'jubilum' => $this->module->l('Anniversary', 'AdminEtsyProfileManagementController'),
                    'taufe' => $this->module->l('Baptism', 'AdminEtsyProfileManagementController'),
                    'bar_oder_bat_mizwa' => $this->module->l('Bar or Bat Mitzvah', 'AdminEtsyProfileManagementController'),
                    'geburtstag' => $this->module->l('Birthday', 'AdminEtsyProfileManagementController'),
                    'canada_day' => $this->module->l('Canada Day', 'AdminEtsyProfileManagementController'),
                    'chinesisches_neujahr' => $this->module->l('Chinese New Year', 'AdminEtsyProfileManagementController'),
                    'cinco_de_mayo' => $this->module->l('Cinco de Mayo', 'AdminEtsyProfileManagementController'),
                    'konfirmation' => $this->module->l('Confirmation', 'AdminEtsyProfileManagementController'),
                    'weihnachten' => $this->module->l('Christmas', 'AdminEtsyProfileManagementController'),
                    'day_of_the_dead' => $this->module->l('Day of the Dead', 'AdminEtsyProfileManagementController'),
                    'ostern' => $this->module->l('Easter', 'AdminEtsyProfileManagementController'),
                    'eid' => $this->module->l('Eid', 'AdminEtsyProfileManagementController'),
                    'verlobung' => $this->module->l('Engagement', 'AdminEtsyProfileManagementController'),
                    'vatertag' => $this->module->l('Fathers Day', 'AdminEtsyProfileManagementController'),
                    'gute_besserung' => $this->module->l('Get Well', 'AdminEtsyProfileManagementController'),
                    'abschluss' => $this->module->l('Graduation', 'AdminEtsyProfileManagementController'),
                    'halloween' => $this->module->l('Halloween', 'AdminEtsyProfileManagementController'),
                    'hanukkah' => $this->module->l('Hanukkah', 'AdminEtsyProfileManagementController'),
                    'hauseinweihung' => $this->module->l('House Warming', 'AdminEtsyProfileManagementController'),
                    'kwanzaa' => $this->module->l('Kwanzaa', 'AdminEtsyProfileManagementController'),
                    'prom' => $this->module->l('Prom', 'AdminEtsyProfileManagementController'),
                    'der_4_juli' => $this->module->l('4th July', 'AdminEtsyProfileManagementController'),
                    'muttertag' => $this->module->l('Mothers Day', 'AdminEtsyProfileManagementController'),
                    'neugeborenes' => $this->module->l('New Baby', 'AdminEtsyProfileManagementController'),
                    'neujahr' => $this->module->l('New Year', 'AdminEtsyProfileManagementController'),
                    'quinceanera' => $this->module->l('Quinceanera', 'AdminEtsyProfileManagementController'),
                    'ruhestand' => $this->module->l('Retirement', 'AdminEtsyProfileManagementController'),
                    'st_patricks_day' => $this->module->l('St. Patricks Day', 'AdminEtsyProfileManagementController'),
                    'sweet_16' => $this->module->l('Sweet 16', 'AdminEtsyProfileManagementController'),
                    'anteilnahme' => $this->module->l('Sympathy', 'AdminEtsyProfileManagementController'),
                    'thanksgiving' => $this->module->l('Thanks Giving', 'AdminEtsyProfileManagementController'),
                    'valentinstag' => $this->module->l('Valentines', 'AdminEtsyProfileManagementController'),
                    'hochzeit' => $this->module->l('Wedding', 'AdminEtsyProfileManagementController')
                );
                break;

            case 'en':
                $recipientOptions = array(
                    '' => $this->module->l('Select Recipient', 'AdminEtsyProfileManagementController'),
                    'men' => $this->module->l('Men', 'AdminEtsyProfileManagementController'),
                    'women' => $this->module->l('Women', 'AdminEtsyProfileManagementController'),
                    'unisex_adults' => $this->module->l('Unisex Adults', 'AdminEtsyProfileManagementController'),
                    'teen_boys' => $this->module->l('Teen Boys', 'AdminEtsyProfileManagementController'),
                    'teen_girls' => $this->module->l('Teen Girls', 'AdminEtsyProfileManagementController'),
                    'teens' => $this->module->l('Teens', 'AdminEtsyProfileManagementController'),
                    'boys' => $this->module->l('Boys', 'AdminEtsyProfileManagementController'),
                    'girls' => $this->module->l('Girls', 'AdminEtsyProfileManagementController'),
                    'children' => $this->module->l('Children', 'AdminEtsyProfileManagementController'),
                    'baby_boys' => $this->module->l('Baby Boys', 'AdminEtsyProfileManagementController'),
                    'baby_girls' => $this->module->l('Baby Girls', 'AdminEtsyProfileManagementController'),
                    'babies' => $this->module->l('Babies', 'AdminEtsyProfileManagementController'),
                    'birds' => $this->module->l('Birds', 'AdminEtsyProfileManagementController'),
                    'cats' => $this->module->l('Cats', 'AdminEtsyProfileManagementController'),
                    'dogs' => $this->module->l('Dogs', 'AdminEtsyProfileManagementController'),
                    'pets' => $this->module->l('Pets', 'AdminEtsyProfileManagementController'),
                    'not_specified' => $this->module->l('Not Specified', 'AdminEtsyProfileManagementController')
                );

                $occassionOptions = array(
                    '' => $this->module->l('Select Occasion', 'AdminEtsyProfileManagementController'),
                    'anniversary' => $this->module->l('Anniversary', 'AdminEtsyProfileManagementController'),
                    'baptism' => $this->module->l('Baptism', 'AdminEtsyProfileManagementController'),
                    'bar_or_bat_mitzvah' => $this->module->l('Bar or Bat Mitzvah', 'AdminEtsyProfileManagementController'),
                    'birthday' => $this->module->l('Birthday', 'AdminEtsyProfileManagementController'),
                    'canada_day' => $this->module->l('Canada Day', 'AdminEtsyProfileManagementController'),
                    'chinese_new_year' => $this->module->l('Chinese New Year', 'AdminEtsyProfileManagementController'),
                    'cinco_de_mayo' => $this->module->l('Cinco de Mayo', 'AdminEtsyProfileManagementController'),
                    'confirmation' => $this->module->l('Confirmation', 'AdminEtsyProfileManagementController'),
                    'christmas' => $this->module->l('Christmas', 'AdminEtsyProfileManagementController'),
                    'day_of_the_dead' => $this->module->l('Day of the Dead', 'AdminEtsyProfileManagementController'),
                    'easter' => $this->module->l('Easter', 'AdminEtsyProfileManagementController'),
                    'eid' => $this->module->l('Eid', 'AdminEtsyProfileManagementController'),
                    'engagement' => $this->module->l('Engagement', 'AdminEtsyProfileManagementController'),
                    'fathers_day' => $this->module->l('Fathers Day', 'AdminEtsyProfileManagementController'),
                    'get_well' => $this->module->l('Get Well', 'AdminEtsyProfileManagementController'),
                    'graduation' => $this->module->l('Graduation', 'AdminEtsyProfileManagementController'),
                    'halloween' => $this->module->l('Halloween', 'AdminEtsyProfileManagementController'),
                    'hanukkah' => $this->module->l('Hanukkah', 'AdminEtsyProfileManagementController'),
                    'housewarming' => $this->module->l('House Warming', 'AdminEtsyProfileManagementController'),
                    'kwanzaa' => $this->module->l('Kwanzaa', 'AdminEtsyProfileManagementController'),
                    'prom' => $this->module->l('Prom', 'AdminEtsyProfileManagementController'),
                    'july_4th' => $this->module->l('4th July', 'AdminEtsyProfileManagementController'),
                    'mothers_day' => $this->module->l('Mothers Day', 'AdminEtsyProfileManagementController'),
                    'new_baby' => $this->module->l('New Baby', 'AdminEtsyProfileManagementController'),
                    'new_years' => $this->module->l('New Year', 'AdminEtsyProfileManagementController'),
                    'quinceanera' => $this->module->l('Quinceanera', 'AdminEtsyProfileManagementController'),
                    'retirement' => $this->module->l('Retirement', 'AdminEtsyProfileManagementController'),
                    'st_patricks_day' => $this->module->l('St. Patricks Day', 'AdminEtsyProfileManagementController'),
                    'sweet_16' => $this->module->l('Sweet 16', 'AdminEtsyProfileManagementController'),
                    'sympathy' => $this->module->l('Sympathy', 'AdminEtsyProfileManagementController'),
                    'thanksgiving' => $this->module->l('Thanks Giving', 'AdminEtsyProfileManagementController'),
                    'valentines' => $this->module->l('Valentines', 'AdminEtsyProfileManagementController'),
                    'wedding' => $this->module->l('Wedding', 'AdminEtsyProfileManagementController')
                );
                break;

            case 'fr':
                $recipientOptions = array(
                    '' => $this->module->l('Select Recipient', 'AdminEtsyProfileManagementController'),
                    'hommes' => $this->module->l('Men', 'AdminEtsyProfileManagementController'),
                    'femmes' => $this->module->l('Women', 'AdminEtsyProfileManagementController'),
                    'adultes_unisexe' => $this->module->l('Unisex Adults', 'AdminEtsyProfileManagementController'),
                    'ados_garons' => $this->module->l('Teen Boys', 'AdminEtsyProfileManagementController'),
                    'ados_filles' => $this->module->l('Teen Girls', 'AdminEtsyProfileManagementController'),
                    'adolescents' => $this->module->l('Teens', 'AdminEtsyProfileManagementController'),
                    'garons' => $this->module->l('Boys', 'AdminEtsyProfileManagementController'),
                    'filles' => $this->module->l('Girls', 'AdminEtsyProfileManagementController'),
                    'enfants' => $this->module->l('Children', 'AdminEtsyProfileManagementController'),
                    'bbs_garons' => $this->module->l('Baby Boys', 'AdminEtsyProfileManagementController'),
                    'bbs_filles' => $this->module->l('Baby Girls', 'AdminEtsyProfileManagementController'),
                    'bbs' => $this->module->l('Babies', 'AdminEtsyProfileManagementController'),
                    'oiseaux' => $this->module->l('Birds', 'AdminEtsyProfileManagementController'),
                    'chats' => $this->module->l('Cats', 'AdminEtsyProfileManagementController'),
                    'chiens' => $this->module->l('Dogs', 'AdminEtsyProfileManagementController'),
                    'animaux_domestiques' => $this->module->l('Pets', 'AdminEtsyProfileManagementController'),
                    'not_specified' => $this->module->l('Not Specified', 'AdminEtsyProfileManagementController')
                );

                $occassionOptions = array(
                    '' => $this->module->l('Select Occasion', 'AdminEtsyProfileManagementController'),
                    'anniversaire_de_mariage' => $this->module->l('Anniversary', 'AdminEtsyProfileManagementController'),
                    'baptme' => $this->module->l('Baptism', 'AdminEtsyProfileManagementController'),
                    'bar_ou_bat_mitzvah' => $this->module->l('Bar or Bat Mitzvah', 'AdminEtsyProfileManagementController'),
                    'anniversaire' => $this->module->l('Birthday', 'AdminEtsyProfileManagementController'),
                    'fte_du_canada' => $this->module->l('Canada Day', 'AdminEtsyProfileManagementController'),
                    'nouvel_an_chinois' => $this->module->l('Chinese New Year', 'AdminEtsyProfileManagementController'),
                    'cinco_de_mayo' => $this->module->l('Cinco de Mayo', 'AdminEtsyProfileManagementController'),
                    'confirmation' => $this->module->l('Confirmation', 'AdminEtsyProfileManagementController'),
                    'nol' => $this->module->l('Christmas', 'AdminEtsyProfileManagementController'),
                    'fte_des_morts' => $this->module->l('Day of the Dead', 'AdminEtsyProfileManagementController'),
                    'pques' => $this->module->l('Easter', 'AdminEtsyProfileManagementController'),
                    'ad' => $this->module->l('Eid', 'AdminEtsyProfileManagementController'),
                    'fianaille' => $this->module->l('Engagement', 'AdminEtsyProfileManagementController'),
                    'fte_des_pres' => $this->module->l('Fathers Day', 'AdminEtsyProfileManagementController'),
                    'voeux_de_bon_rtablissement' => $this->module->l('Get Well', 'AdminEtsyProfileManagementController'),
                    'remise_des_diplmes' => $this->module->l('Graduation', 'AdminEtsyProfileManagementController'),
                    'halloween' => $this->module->l('Halloween', 'AdminEtsyProfileManagementController'),
                    'hanoukka' => $this->module->l('Hanukkah', 'AdminEtsyProfileManagementController'),
                    'pendaison_de_crmaillre' => $this->module->l('House Warming', 'AdminEtsyProfileManagementController'),
                    'kwanzaa' => $this->module->l('Kwanzaa', 'AdminEtsyProfileManagementController'),
                    'bal_de_promo' => $this->module->l('Prom', 'AdminEtsyProfileManagementController'),
                    'jour_de_lindpendance_des_etatsunis' => $this->module->l('4th July', 'AdminEtsyProfileManagementController'),
                    'fte_des_mres' => $this->module->l('Mothers Day', 'AdminEtsyProfileManagementController'),
                    'nouveaun' => $this->module->l('New Baby', 'AdminEtsyProfileManagementController'),
                    'nouvel_an' => $this->module->l('New Year', 'AdminEtsyProfileManagementController'),
                    'fte_des_15_ans' => $this->module->l('Quinceanera', 'AdminEtsyProfileManagementController'),
                    'retraite' => $this->module->l('Retirement', 'AdminEtsyProfileManagementController'),
                    'fte_de_la_saintpatrick' => $this->module->l('St. Patricks Day', 'AdminEtsyProfileManagementController'),
                    'majorit' => $this->module->l('Sweet 16', 'AdminEtsyProfileManagementController'),
                    'amiti' => $this->module->l('Sympathy', 'AdminEtsyProfileManagementController'),
                    'thanksgiving' => $this->module->l('Thanks Giving', 'AdminEtsyProfileManagementController'),
                    'saintvalentin' => $this->module->l('Valentines', 'AdminEtsyProfileManagementController'),
                    'mariage' => $this->module->l('Wedding', 'AdminEtsyProfileManagementController')
                );
                break;

            case 'es':
                $recipientOptions = array(
                    '' => $this->module->l('Select Recipient', 'AdminEtsyProfileManagementController'),
                    'hombre' => $this->module->l('Men', 'AdminEtsyProfileManagementController'),
                    'mujer' => $this->module->l('Women', 'AdminEtsyProfileManagementController'),
                    'adultos_unisex' => $this->module->l('Unisex Adults', 'AdminEtsyProfileManagementController'),
                    'nios_adolescentes' => $this->module->l('Teen Boys', 'AdminEtsyProfileManagementController'),
                    'nias_adolescentes' => $this->module->l('Teen Girls', 'AdminEtsyProfileManagementController'),
                    'adolescentes' => $this->module->l('Teens', 'AdminEtsyProfileManagementController'),
                    'nios' => $this->module->l('Children', 'AdminEtsyProfileManagementController'),
                    'nias' => $this->module->l('Girls', 'AdminEtsyProfileManagementController'),
                    'bebs' => $this->module->l('Babies', 'AdminEtsyProfileManagementController'),
                    'pjaros' => $this->module->l('Birds', 'AdminEtsyProfileManagementController'),
                    'gatos' => $this->module->l('Cats', 'AdminEtsyProfileManagementController'),
                    'perros' => $this->module->l('Dogs', 'AdminEtsyProfileManagementController'),
                    'mascotas' => $this->module->l('Pets', 'AdminEtsyProfileManagementController'),
                    'not_specified' => $this->module->l('Not Specified', 'AdminEtsyProfileManagementController')
                );

                $occassionOptions = array(
                    '' => $this->module->l('Select Occasion', 'AdminEtsyProfileManagementController'),
                    'aniversario' => $this->module->l('Anniversary', 'AdminEtsyProfileManagementController'),
                    'bautizo' => $this->module->l('Baptism', 'AdminEtsyProfileManagementController'),
                    'bar_o_bat_mitzvah' => $this->module->l('Bar or Bat Mitzvah', 'AdminEtsyProfileManagementController'),
                    'cumpleaos' => $this->module->l('Birthday', 'AdminEtsyProfileManagementController'),
                    'da_de_canad' => $this->module->l('Canada Day', 'AdminEtsyProfileManagementController'),
                    'ao_nuevo_chino' => $this->module->l('Chinese New Year', 'AdminEtsyProfileManagementController'),
                    'cinco_de_mayo' => $this->module->l('Cinco de Mayo', 'AdminEtsyProfileManagementController'),
                    'confirmacin' => $this->module->l('Confirmation', 'AdminEtsyProfileManagementController'),
                    'navidad' => $this->module->l('Christmas', 'AdminEtsyProfileManagementController'),
                    'da_de_los_muertos' => $this->module->l('Day of the Dead', 'AdminEtsyProfileManagementController'),
                    'pascuas' => $this->module->l('Easter', 'AdminEtsyProfileManagementController'),
                    'eid' => $this->module->l('Eid', 'AdminEtsyProfileManagementController'),
                    'compromiso' => $this->module->l('Engagement', 'AdminEtsyProfileManagementController'),
                    'da_del_padre' => $this->module->l('Fathers Day', 'AdminEtsyProfileManagementController'),
                    'que_te_mejores' => $this->module->l('Get Well', 'AdminEtsyProfileManagementController'),
                    'graduacin' => $this->module->l('Graduation', 'AdminEtsyProfileManagementController'),
                    'halloween' => $this->module->l('Halloween', 'AdminEtsyProfileManagementController'),
                    'januc' => $this->module->l('Hanukkah', 'AdminEtsyProfileManagementController'),
                    'inauguracin' => $this->module->l('House Warming', 'AdminEtsyProfileManagementController'),
                    'kwanzaa' => $this->module->l('Kwanzaa', 'AdminEtsyProfileManagementController'),
                    'promocin' => $this->module->l('Prom', 'AdminEtsyProfileManagementController'),
                    '4_de_julio' => $this->module->l('4th July', 'AdminEtsyProfileManagementController'),
                    'da_de_la_madre' => $this->module->l('Mothers Day', 'AdminEtsyProfileManagementController'),
                    'recin_nacido' => $this->module->l('New Baby', 'AdminEtsyProfileManagementController'),
                    'ao_nuevo' => $this->module->l('New Year', 'AdminEtsyProfileManagementController'),
                    'quinceaera' => $this->module->l('Quinceanera', 'AdminEtsyProfileManagementController'),
                    'jubilacin' => $this->module->l('Retirement', 'AdminEtsyProfileManagementController'),
                    'da_de_san_patricio' => $this->module->l('St. Patricks Day', 'AdminEtsyProfileManagementController'),
                    'dulces_16' => $this->module->l('Sweet 16', 'AdminEtsyProfileManagementController'),
                    'condolencias' => $this->module->l('Sympathy', 'AdminEtsyProfileManagementController'),
                    'accin_de_gracias' => $this->module->l('Thanks Giving', 'AdminEtsyProfileManagementController'),
                    'san_valentn' => $this->module->l('Valentines', 'AdminEtsyProfileManagementController'),
                    'boda' => $this->module->l('Wedding', 'AdminEtsyProfileManagementController')
                );
                break;

            case 'it':
                $recipientOptions = array(
                    '' => $this->module->l('Select Recipient', 'AdminEtsyProfileManagementController'),
                    'uomini' => $this->module->l('Men', 'AdminEtsyProfileManagementController'),
                    'donne' => $this->module->l('Women', 'AdminEtsyProfileManagementController'),
                    'adulti_unisex' => $this->module->l('Unisex Adults', 'AdminEtsyProfileManagementController'),
                    'ragazzi_adolescenti' => $this->module->l('Teen Boys', 'AdminEtsyProfileManagementController'),
                    'ragazze_adolescenti' => $this->module->l('Teen Girls', 'AdminEtsyProfileManagementController'),
                    'ragazzi' => $this->module->l('Teens', 'AdminEtsyProfileManagementController'),
                    'bambini_48' => $this->module->l('Boys', 'AdminEtsyProfileManagementController'),
                    'bambine_48' => $this->module->l('Girls', 'AdminEtsyProfileManagementController'),
                    'bambini' => $this->module->l('Children', 'AdminEtsyProfileManagementController'),
                    'bimbo_03' => $this->module->l('Baby Boys', 'AdminEtsyProfileManagementController'),
                    'bimba_03' => $this->module->l('Baby Girls', 'AdminEtsyProfileManagementController'),
                    'bimbi' => $this->module->l('Babies', 'AdminEtsyProfileManagementController'),
                    'uccelli' => $this->module->l('Birds', 'AdminEtsyProfileManagementController'),
                    'gatti' => $this->module->l('Cats', 'AdminEtsyProfileManagementController'),
                    'cani' => $this->module->l('Dogs', 'AdminEtsyProfileManagementController'),
                    'animali_domestici' => $this->module->l('Pets', 'AdminEtsyProfileManagementController'),
                    'non_specificato' => $this->module->l('Not Specified', 'AdminEtsyProfileManagementController')
                );

                $occassionOptions = array(
                    '' => $this->module->l('Select Occasion', 'AdminEtsyProfileManagementController'),
                    'anniversario' => $this->module->l('Anniversary', 'AdminEtsyProfileManagementController'),
                    'battesimo' => $this->module->l('Baptism', 'AdminEtsyProfileManagementController'),
                    'bar_or_bat_mitzvah' => $this->module->l('Bar or Bat Mitzvah', 'AdminEtsyProfileManagementController'),
                    'compleanno' => $this->module->l('Birthday', 'AdminEtsyProfileManagementController'),
                    'canada_day' => $this->module->l('Canada Day', 'AdminEtsyProfileManagementController'),
                    'nuovo_anno_cinese' => $this->module->l('Chinese New Year', 'AdminEtsyProfileManagementController'),
                    'cinco_de_mayo' => $this->module->l('Cinco de Mayo', 'AdminEtsyProfileManagementController'),
                    'cresima' => $this->module->l('Confirmation', 'AdminEtsyProfileManagementController'),
                    'natale' => $this->module->l('Christmas', 'AdminEtsyProfileManagementController'),
                    'giorno_dei_morti' => $this->module->l('Day of the Dead', 'AdminEtsyProfileManagementController'),
                    'pasqua' => $this->module->l('Easter', 'AdminEtsyProfileManagementController'),
                    'giuramento' => $this->module->l('Eid', 'AdminEtsyProfileManagementController'),
                    'fidanzamento' => $this->module->l('Engagement', 'AdminEtsyProfileManagementController'),
                    'festa_del_pap' => $this->module->l('Fathers Day', 'AdminEtsyProfileManagementController'),
                    'guarigione' => $this->module->l('Get Well', 'AdminEtsyProfileManagementController'),
                    'laurea' => $this->module->l('Graduation', 'AdminEtsyProfileManagementController'),
                    'halloween' => $this->module->l('Halloween', 'AdminEtsyProfileManagementController'),
                    'hanukkah' => $this->module->l('Hanukkah', 'AdminEtsyProfileManagementController'),
                    'inaugurazione' => $this->module->l('House Warming', 'AdminEtsyProfileManagementController'),
                    'kwanzaa' => $this->module->l('Kwanzaa', 'AdminEtsyProfileManagementController'),
                    'ballo_studentesco' => $this->module->l('Prom', 'AdminEtsyProfileManagementController'),
                    '4_luglio' => $this->module->l('4th July', 'AdminEtsyProfileManagementController'),
                    'festa_della_mamma' => $this->module->l('Mothers Day', 'AdminEtsyProfileManagementController'),
                    'nuovo_nato' => $this->module->l('New Baby', 'AdminEtsyProfileManagementController'),
                    'capodanno' => $this->module->l('New Year', 'AdminEtsyProfileManagementController'),
                    'quinceanera' => $this->module->l('Quinceanera', 'AdminEtsyProfileManagementController'),
                    'pensione' => $this->module->l('Retirement', 'AdminEtsyProfileManagementController'),
                    'festa_di_san_patrizio' => $this->module->l('St. Patricks Day', 'AdminEtsyProfileManagementController'),
                    'sweet_16' => $this->module->l('Sweet 16', 'AdminEtsyProfileManagementController'),
                    'condoglianze' => $this->module->l('Sympathy', 'AdminEtsyProfileManagementController'),
                    'giorno_del_ringraziamento' => $this->module->l('Thanks Giving', 'AdminEtsyProfileManagementController'),
                    'san_valentino' => $this->module->l('Valentines', 'AdminEtsyProfileManagementController'),
                    'matrimonio' => $this->module->l('Wedding', 'AdminEtsyProfileManagementController')
                );
                break;

            case 'nl':
                $recipientOptions = array(
                    '' => $this->module->l('Select Recipient', 'AdminEtsyProfileManagementController'),
                    'mannen' => $this->module->l('Men', 'AdminEtsyProfileManagementController'),
                    'vrouwen' => $this->module->l('Women', 'AdminEtsyProfileManagementController'),
                    'unisex_volwassenen' => $this->module->l('Unisex Adults', 'AdminEtsyProfileManagementController'),
                    'tienerjongens' => $this->module->l('Teen Boys', 'AdminEtsyProfileManagementController'),
                    'tienermeisjes' => $this->module->l('Teen Girls', 'AdminEtsyProfileManagementController'),
                    'tieners' => $this->module->l('Teens', 'AdminEtsyProfileManagementController'),
                    'jongens' => $this->module->l('Boys', 'AdminEtsyProfileManagementController'),
                    'meisjes' => $this->module->l('Girls', 'AdminEtsyProfileManagementController'),
                    'kinderen' => $this->module->l('Children', 'AdminEtsyProfileManagementController'),
                    'babyjongentjes' => $this->module->l('Baby Boys', 'AdminEtsyProfileManagementController'),
                    'babymeisjes' => $this->module->l('Baby Girls', 'AdminEtsyProfileManagementController'),
                    'babys' => $this->module->l('Babies', 'AdminEtsyProfileManagementController'),
                    'vogels' => $this->module->l('Birds', 'AdminEtsyProfileManagementController'),
                    'katten' => $this->module->l('Cats', 'AdminEtsyProfileManagementController'),
                    'honden' => $this->module->l('Dogs', 'AdminEtsyProfileManagementController'),
                    'huisdieren' => $this->module->l('Pets', 'AdminEtsyProfileManagementController'),
                    'not_specified' => $this->module->l('Not Specified', 'AdminEtsyProfileManagementController')
                );

                $occassionOptions = array(
                    '' => $this->module->l('Select Occasion', 'AdminEtsyProfileManagementController'),
                    'jubileum' => $this->module->l('Anniversary', 'AdminEtsyProfileManagementController'),
                    'doop' => $this->module->l('Baptism', 'AdminEtsyProfileManagementController'),
                    'bar_mitzvah' => $this->module->l('Bar or Bat Mitzvah', 'AdminEtsyProfileManagementController'),
                    'verjaardag' => $this->module->l('Birthday', 'AdminEtsyProfileManagementController'),
                    'canadese_feestdag' => $this->module->l('Canada Day', 'AdminEtsyProfileManagementController'),
                    'chinees_nieuwjaar' => $this->module->l('Chinese New Year', 'AdminEtsyProfileManagementController'),
                    'cinco_de_mayo' => $this->module->l('Cinco de Mayo', 'AdminEtsyProfileManagementController'),
                    'vormsel' => $this->module->l('Confirmation', 'AdminEtsyProfileManagementController'),
                    'kerst' => $this->module->l('Christmas', 'AdminEtsyProfileManagementController'),
                    'dag_van_de_doden' => $this->module->l('Day of the Dead', 'AdminEtsyProfileManagementController'),
                    'pasen' => $this->module->l('Easter', 'AdminEtsyProfileManagementController'),
                    'suikerfeest' => $this->module->l('Eid', 'AdminEtsyProfileManagementController'),
                    'verloving' => $this->module->l('Engagement', 'AdminEtsyProfileManagementController'),
                    'vaderdag' => $this->module->l('Fathers Day', 'AdminEtsyProfileManagementController'),
                    'beterschap' => $this->module->l('Get Well', 'AdminEtsyProfileManagementController'),
                    'geslaagd' => $this->module->l('Graduation', 'AdminEtsyProfileManagementController'),
                    'halloween' => $this->module->l('Halloween', 'AdminEtsyProfileManagementController'),
                    'hannukkah' => $this->module->l('Hanukkah', 'AdminEtsyProfileManagementController'),
                    'housewarming' => $this->module->l('House Warming', 'AdminEtsyProfileManagementController'),
                    'kwanzaa' => $this->module->l('Kwanzaa', 'AdminEtsyProfileManagementController'),
                    'gala' => $this->module->l('Prom', 'AdminEtsyProfileManagementController'),
                    'amerikaanse_independence_day' => $this->module->l('4th July', 'AdminEtsyProfileManagementController'),
                    'moederdag' => $this->module->l('Mothers Day', 'AdminEtsyProfileManagementController'),
                    'geboorte' => $this->module->l('New Baby', 'AdminEtsyProfileManagementController'),
                    'nieuwjaar' => $this->module->l('New Year', 'AdminEtsyProfileManagementController'),
                    'quinceanera' => $this->module->l('Quinceanera', 'AdminEtsyProfileManagementController'),
                    'pensioen' => $this->module->l('Retirement', 'AdminEtsyProfileManagementController'),
                    'st_patricks_dag' => $this->module->l('St. Patricks Day', 'AdminEtsyProfileManagementController'),
                    'sweet_16' => $this->module->l('Sweet 16', 'AdminEtsyProfileManagementController'),
                    'oprechte_deelneming' => $this->module->l('Sympathy', 'AdminEtsyProfileManagementController'),
                    'thanksgiving' => $this->module->l('Thanks Giving', 'AdminEtsyProfileManagementController'),
                    'valentijnsdag' => $this->module->l('Valentines', 'AdminEtsyProfileManagementController'),
                    'trouwdag' => $this->module->l('Wedding', 'AdminEtsyProfileManagementController')
                );
                break;

            case 'pt':
                $recipientOptions = array(
                    '' => $this->module->l('Select Recipient', 'AdminEtsyProfileManagementController'),
                    'homens' => $this->module->l('Men', 'AdminEtsyProfileManagementController'),
                    'mulheres' => $this->module->l('Women', 'AdminEtsyProfileManagementController'),
                    'adultos_unisexo' => $this->module->l('Unisex Adults', 'AdminEtsyProfileManagementController'),
                    'rapazes_adolescentes' => $this->module->l('Teen Boys', 'AdminEtsyProfileManagementController'),
                    'raparigas_adolescentes' => $this->module->l('Teen Girls', 'AdminEtsyProfileManagementController'),
                    'adolescentes' => $this->module->l('Teens', 'AdminEtsyProfileManagementController'),
                    'rapazes' => $this->module->l('Boys', 'AdminEtsyProfileManagementController'),
                    'raparigas' => $this->module->l('Girls', 'AdminEtsyProfileManagementController'),
                    'crianas' => $this->module->l('Children', 'AdminEtsyProfileManagementController'),
                    'bebmenino' => $this->module->l('Baby Boys', 'AdminEtsyProfileManagementController'),
                    'bebs_do_sexo_feminino' => $this->module->l('Baby Girls', 'AdminEtsyProfileManagementController'),
                    'bebs' => $this->module->l('Babies', 'AdminEtsyProfileManagementController'),
                    'pssaros' => $this->module->l('Birds', 'AdminEtsyProfileManagementController'),
                    'gatos' => $this->module->l('Cats', 'AdminEtsyProfileManagementController'),
                    'ces' => $this->module->l('Dogs', 'AdminEtsyProfileManagementController'),
                    'animais_de_estimao' => $this->module->l('Pets', 'AdminEtsyProfileManagementController'),
                    'not_specified' => $this->module->l('Not Specified', 'AdminEtsyProfileManagementController')
                );

                $occassionOptions = array(
                    '' => $this->module->l('Select Occasion', 'AdminEtsyProfileManagementController'),
                    'aniversrio' => $this->module->l('Anniversary', 'AdminEtsyProfileManagementController'),
                    'batizado' => $this->module->l('Baptism', 'AdminEtsyProfileManagementController'),
                    'bar_ou_bat_mitzvah' => $this->module->l('Bar or Bat Mitzvah', 'AdminEtsyProfileManagementController'),
                    'dia_do_canad' => $this->module->l('Canada Day', 'AdminEtsyProfileManagementController'),
                    'ano_novo_chins' => $this->module->l('Chinese New Year', 'AdminEtsyProfileManagementController'),
                    'cinco_de_maio' => $this->module->l('Cinco de Mayo', 'AdminEtsyProfileManagementController'),
                    'confirmao' => $this->module->l('Confirmation', 'AdminEtsyProfileManagementController'),
                    'natal' => $this->module->l('Christmas', 'AdminEtsyProfileManagementController'),
                    'dia_dos_mortos' => $this->module->l('Day of the Dead', 'AdminEtsyProfileManagementController'),
                    'pscoa' => $this->module->l('Easter', 'AdminEtsyProfileManagementController'),
                    'eid' => $this->module->l('Eid', 'AdminEtsyProfileManagementController'),
                    'noivado' => $this->module->l('Engagement', 'AdminEtsyProfileManagementController'),
                    'dia_do_pai' => $this->module->l('Fathers Day', 'AdminEtsyProfileManagementController'),
                    'as_melhoras' => $this->module->l('Get Well', 'AdminEtsyProfileManagementController'),
                    'formatura' => $this->module->l('Graduation', 'AdminEtsyProfileManagementController'),
                    'dia_das_bruxas' => $this->module->l('Halloween', 'AdminEtsyProfileManagementController'),
                    'hanukkah' => $this->module->l('Hanukkah', 'AdminEtsyProfileManagementController'),
                    'prendas_de_inaugurao' => $this->module->l('House Warming', 'AdminEtsyProfileManagementController'),
                    'kwanzaa' => $this->module->l('Kwanzaa', 'AdminEtsyProfileManagementController'),
                    'baile_de_finalistas' => $this->module->l('Prom', 'AdminEtsyProfileManagementController'),
                    '4_de_julho' => $this->module->l('4th July', 'AdminEtsyProfileManagementController'),
                    'dia_da_me' => $this->module->l('Mothers Day', 'AdminEtsyProfileManagementController'),
                    'novo_beb' => $this->module->l('New Baby', 'AdminEtsyProfileManagementController'),
                    'ano_novo' => $this->module->l('New Year', 'AdminEtsyProfileManagementController'),
                    'quinceanera' => $this->module->l('Quinceanera', 'AdminEtsyProfileManagementController'),
                    'reforma' => $this->module->l('Retirement', 'AdminEtsyProfileManagementController'),
                    'dia_de_so_patrcio' => $this->module->l('St. Patricks Day', 'AdminEtsyProfileManagementController'),
                    'ocasio_festa_de_15_anos' => $this->module->l('Sweet 16', 'AdminEtsyProfileManagementController'),
                    'simpatia' => $this->module->l('Sympathy', 'AdminEtsyProfileManagementController'),
                    'aco_de_graas' => $this->module->l('Thanks Giving', 'AdminEtsyProfileManagementController'),
                    'dia_dos_namorados' => $this->module->l('Valentines', 'AdminEtsyProfileManagementController'),
                    'casamento' => $this->module->l('Wedding', 'AdminEtsyProfileManagementController')
                );
                break;

            case 'ru':
                $recipientOptions = array(
                    '' => $this->module->l('Select Recipient', 'AdminEtsyProfileManagementController'),
                    'not_specified' => $this->module->l('Not Specified', 'AdminEtsyProfileManagementController')
                );

                $occassionOptions = array(
                    '' => $this->module->l('Select Occasion', 'AdminEtsyProfileManagementController'),
                );
                break;

            default:
                $recipientOptions = array(
                    '' => $this->module->l('Select Recipient', 'AdminEtsyProfileManagementController'),
                    'men' => $this->module->l('Men', 'AdminEtsyProfileManagementController'),
                    'women' => $this->module->l('Women', 'AdminEtsyProfileManagementController'),
                    'unisex_adults' => $this->module->l('Unisex Adults', 'AdminEtsyProfileManagementController'),
                    'teen_boys' => $this->module->l('Teen Boys', 'AdminEtsyProfileManagementController'),
                    'teen_girls' => $this->module->l('Teen Girls', 'AdminEtsyProfileManagementController'),
                    'teens' => $this->module->l('Teens', 'AdminEtsyProfileManagementController'),
                    'boys' => $this->module->l('Boys', 'AdminEtsyProfileManagementController'),
                    'girls' => $this->module->l('Girls', 'AdminEtsyProfileManagementController'),
                    'children' => $this->module->l('Children', 'AdminEtsyProfileManagementController'),
                    'baby_boys' => $this->module->l('Baby Boys', 'AdminEtsyProfileManagementController'),
                    'baby_girls' => $this->module->l('Baby Girls', 'AdminEtsyProfileManagementController'),
                    'babies' => $this->module->l('Babies', 'AdminEtsyProfileManagementController'),
                    'birds' => $this->module->l('Birds', 'AdminEtsyProfileManagementController'),
                    'cats' => $this->module->l('Cats', 'AdminEtsyProfileManagementController'),
                    'dogs' => $this->module->l('Dogs', 'AdminEtsyProfileManagementController'),
                    'pets' => $this->module->l('Pets', 'AdminEtsyProfileManagementController'),
                    'not_specified' => $this->module->l('Not Specified', 'AdminEtsyProfileManagementController')
                );

                $occassionOptions = array(
                    '' => $this->module->l('Select Occasion', 'AdminEtsyProfileManagementController'),
                    'anniversary' => $this->module->l('Anniversary', 'AdminEtsyProfileManagementController'),
                    'baptism' => $this->module->l('Baptism', 'AdminEtsyProfileManagementController'),
                    'bar_or_bat_mitzvah' => $this->module->l('Bar or Bat Mitzvah', 'AdminEtsyProfileManagementController'),
                    'birthday' => $this->module->l('Birthday', 'AdminEtsyProfileManagementController'),
                    'canada_day' => $this->module->l('Canada Day', 'AdminEtsyProfileManagementController'),
                    'chinese_new_year' => $this->module->l('Chinese New Year', 'AdminEtsyProfileManagementController'),
                    'cinco_de_mayo' => $this->module->l('Cinco de Mayo', 'AdminEtsyProfileManagementController'),
                    'confirmation' => $this->module->l('Confirmation', 'AdminEtsyProfileManagementController'),
                    'christmas' => $this->module->l('Christmas', 'AdminEtsyProfileManagementController'),
                    'day_of_the_dead' => $this->module->l('Day of the Dead', 'AdminEtsyProfileManagementController'),
                    'easter' => $this->module->l('Easter', 'AdminEtsyProfileManagementController'),
                    'eid' => $this->module->l('Eid', 'AdminEtsyProfileManagementController'),
                    'engagement' => $this->module->l('Engagement', 'AdminEtsyProfileManagementController'),
                    'fathers_day' => $this->module->l('Fathers Day', 'AdminEtsyProfileManagementController'),
                    'get_well' => $this->module->l('Get Well', 'AdminEtsyProfileManagementController'),
                    'graduation' => $this->module->l('Graduation', 'AdminEtsyProfileManagementController'),
                    'halloween' => $this->module->l('Halloween', 'AdminEtsyProfileManagementController'),
                    'hanukkah' => $this->module->l('Hanukkah', 'AdminEtsyProfileManagementController'),
                    'housewarming' => $this->module->l('House Warming', 'AdminEtsyProfileManagementController'),
                    'kwanzaa' => $this->module->l('Kwanzaa', 'AdminEtsyProfileManagementController'),
                    'prom' => $this->module->l('Prom', 'AdminEtsyProfileManagementController'),
                    'july_4th' => $this->module->l('4th July', 'AdminEtsyProfileManagementController'),
                    'mothers_day' => $this->module->l('Mothers Day', 'AdminEtsyProfileManagementController'),
                    'new_baby' => $this->module->l('New Baby', 'AdminEtsyProfileManagementController'),
                    'new_years' => $this->module->l('New Year', 'AdminEtsyProfileManagementController'),
                    'quinceanera' => $this->module->l('Quinceanera', 'AdminEtsyProfileManagementController'),
                    'retirement' => $this->module->l('Retirement', 'AdminEtsyProfileManagementController'),
                    'st_patricks_day' => $this->module->l('St. Patricks Day', 'AdminEtsyProfileManagementController'),
                    'sweet_16' => $this->module->l('Sweet 16', 'AdminEtsyProfileManagementController'),
                    'sympathy' => $this->module->l('Sympathy', 'AdminEtsyProfileManagementController'),
                    'thanksgiving' => $this->module->l('Thanks Giving', 'AdminEtsyProfileManagementController'),
                    'valentines' => $this->module->l('Valentines', 'AdminEtsyProfileManagementController'),
                    'wedding' => $this->module->l('Wedding', 'AdminEtsyProfileManagementController')
                );
                break;
        }

        if ($recipientOptions) {
            $recipientList = array();
            foreach ($recipientOptions as $key => $value) {
                $recipientList[] = array(
                    'id_option' => $key,
                    'name' => $value
                );
            }
        }

        //Prepare array of Occassions
        if ($occassionOptions) {
            $occassionList = array();
            foreach ($occassionOptions as $key => $value) {
                $occassionList[] = array(
                    'id_option' => $key,
                    'name' => $value
                );
            }
        }

        // changes by rishabh jain
        $logo_image = $this->getModuleDirUrl() . 'kbetsy/views/img/profile/sample.jpg?time='.time();
        if ((int)Tools::getValue('id_etsy_profiles') != 0) {
            $id_profile = (int)Tools::getValue('id_etsy_profiles');
            $exist_file = _PS_MODULE_DIR_. 'kbetsy/views/img/profile/'.$id_profile. '.*';
            $match1 = glob($exist_file);
            if (count($match1) > 0) {
                $ban = explode('/', $match1[0]);
                $ban = end($ban);
                $ban = trim($ban);
                $img_url = $this->getModuleDirUrl() . 'kbetsy/views/img/profile/' . $ban;
                if (file_exists($match1[0])) {
                    $logo_image = $img_url.'?time='.time();
                }
            }
        }
        $logo_url = "<img id='kbsizechartlogo' class='img img-thumbnail'  src='".$logo_image."'>";
        // changes over
        $this->fields_form = array(
            'legend' => array(
                'title' => !Tools::isEmpty(trim(Tools::getValue('id_etsy_profiles'))) ? $this->module->l('Update Profile', 'AdminEtsyProfileManagementController') : $this->module->l('Add New Profile', 'AdminEtsyProfileManagementController'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'hidden',
                    'name' => 'ps_version'
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'update_profile_product'
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'property_ajax_url'
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'id_etsy_profiles'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->module->l('Profile title', 'AdminEtsyProfileManagementController'),
                    'desc' => $this->module->l('Provide Profile Title', 'AdminEtsyProfileManagementController'),
                    'name' => 'profile_title',
                    'maxlength' => 255,
                    'required' => true
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Etsy category', 'AdminEtsyProfileManagementController'),
                    'desc' => $this->module->l('Choose an Etsy Marketplace Category to list attributes', 'AdminEtsyProfileManagementController'),
                    'name' => 'etsy_category_code',
                    'required' => true,
                    'options' => array(
                        'query' => $etsyCategoriesList,
                        'id' => 'id_option',
                        'name' => 'name'
                    ),
                    'onchange' => 'getPropertiesList(this.value)'
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Product selection type', 'AdminEtsyProfileManagementController'),
                    'name' => 'etsy_product_type',
                    'required' => true,
                    'options' => array(
                        'query' => $product_selection_type,
                        'id' => 'id',
                        'name' => 'name'
                    ),
                    'onchange' => 'showHideProductType(this.value)'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->module->l('Select Products', 'AdminEtsyProfileManagementController'),
                    'name' => 'etsy_selected_products',
                    'col' => 4,
                    'class' => 'etsy_selected_products'
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'kbetsy_selected_products'
                ),
                array(
                    'type' => 'categories_select',
                    'label' => $this->module->l('Store category', 'AdminEtsyProfileManagementController'),
                    'name' => 'prestashop_category',
                    'required' => true,
                    'category_tree' => $categoryTreePresta
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Shop section'),
                    'desc' => $this->l('Map Etsy Shop Section with this profile.'),
                    'name' => 'id_etsy_shop_section',
                    'options' => array(
                        'query' => $etsyShopSection,
                        'id' => 'id_option',
                        'name' => 'name'
                    )
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Shipping template', 'AdminEtsyProfileManagementController'),
                    'desc' => $this->module->l('Choose a Shipping Template to avail shipping options for customers. In case, if Shipping Templates are not being displayed, Go to Shipping Template page to add OR sync existing etsy template', 'AdminEtsyProfileManagementController'),
                    'name' => 'id_etsy_shipping_templates',
                    'required' => true,
                    'options' => array(
                        'query' => $shippingTemplatesList,
                        'id' => 'id_option',
                        'name' => 'name'
                    )
                ),
                array(
                    'type' => 'hidden',
                    'label' => $this->module->l('Etsy Currency', 'AdminEtsyProfileManagementController'),
                    'desc' => $this->module->l('This currency should be same as the currency of your Etsy Admin panel, you can check the Etsy currecy in Finances > Payment Settings > Currency', 'AdminEtsyProfileManagementController'),
                    'name' => 'currency',
                    'required' => true,
                ),
                /* Start-MK made changes on 22-11-2017 for adding inventory field, property and custom product name field */
                array(
                    'type' => 'text',
                    'label' => $this->l('Customize product title'),
                    'name' => 'customize_product_title',
                    'required' => true,
                    'default_value' => '{product_title}',
                    'desc' => $this->l('Customize the product title by using the following place-holders. Place-holders like {sample} will be replace by dynamic content at the time of execution.'),
                ),
                array(
                    'type' => 'hidden',
                    'label' => $this->l('Sync Property'),
                    'desc' => $this->l('Select property to sync with Etsy Marketplace.This will work when updating the product and its variations'),
                    'name' => 'property',
                ),
                array(
                    'type' => 'hidden',
                    'label' => $this->l('Enable minimum quantity'),
                    'name' => 'enable_min_qty',
                    'default_value' => 0,
                    'desc' => $this->l('Enable to enter minimum quantity')
                ),
                array(
                    'type' => 'hidden',
                    'label' => $this->l('Minimum Quantity'),
                    'name' => 'min_qty',
                    'col' => 2,
                    'default_value' => 1
                ),
                array(
                    'type' => 'hidden',
                    'label' => $this->l('Enable maximum quantity'),
                    'name' => 'enable_max_qty',
                    'default_value' => 1,
                    'desc' => $this->l('The maximum quantity of the products which needs to be synced. If quantity of the items is greater than defined value than the system will sync defined quantity of those products.')
                ),
                array(
                    'type' => 'hidden',
                    'label' => $this->l('Maximum quantity'),
                    'name' => 'max_qty',
                    'col' => 2,
                    'default_value' => 999,
                ),
                array(
                    'type' => 'switch',
                    'name' => 'custom_pricing',
                    'label' => $this->l('Enable custom pricing'),
                    'desc' => $this->l('Enable if you want to sync different price from the actual price of products associated with this profile.'),
                    'values' => array(
                        array(
                            'value' => 1
                        ),
                        array(
                            'value' => 0
                        )
                    ),
                ),
                /* End-MK made changes on 22-11-2017 for adding inventory field, property and custom product name field */
                array(
                    'type' => 'switch',
                    'name' => 'should_auto_renew',
                    'label' => $this->module->l('Enable auto renewal'),
                    'desc' => $this->module->l('Enable if you want to set automatical renewals of products associated with this profile'),
                    'values' => array(
                        array(
                            'value' => 1
                        ),
                        array(
                            'value' => 0
                        )
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->module->l('Customizable product?', 'AdminEtsyProfileManagementController'),
                    'desc' => $this->module->l('Is product a customizable product?', 'AdminEtsyProfileManagementController'),
                    'name' => 'is_customizable',
                    'values' => array(
                        array(
                            'value' => 1
                        ),
                        array(
                            'value' => 0
                        )
                    )
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Who made it?', 'AdminEtsyProfileManagementController'),
                    'desc' => $this->module->l('Specify who made the products associated with this profile', 'AdminEtsyProfileManagementController'),
                    'name' => 'who_made',
                    'required' => true,
                    'options' => array(
                        'query' => $whoMadeList,
                        'id' => 'id_option',
                        'name' => 'name'
                    )
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->l('When did you make it?', 'AdminEtsyProfileManagementController'),
                    'desc' => $this->module->l('Specify when did you make the products associated with this profile', 'AdminEtsyProfileManagementController'),
                    'name' => 'when_made',
                    'required' => true,
                    'options' => array(
                        'query' => $whenMadeList,
                        'id' => 'id_option',
                        'name' => 'name'
                    )
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->module->l('What is it?', 'AdminEtsyProfileManagementController'),
                    'name' => 'is_supply',
                    'values' => array(
                        array(
                            'id' => 'finished_product',
                            'value' => 0,
                            'label' => $this->l('A finished product')
                        ),
                        array(
                            'id' => 'tool',
                            'value' => 1,
                            'label' => $this->l('A supply or tool to make things')
                        )
                    )
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Recipient', 'AdminEtsyProfileManagementController'),
                    'desc' => $this->module->l('Specify recipient of the products associated with this profile', 'AdminEtsyProfileManagementController'),
                    'name' => 'recipient',
                    'options' => array(
                        'query' => $recipientList,
                        'id' => 'id_option',
                        'name' => 'name'
                    )
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Occasion', 'AdminEtsyProfileManagementController'),
                    'desc' => $this->module->l('Specify occasion of the products associated with this profile', 'AdminEtsyProfileManagementController'),
                    'name' => 'occassion',
                    'options' => array(
                        'query' => $occassionList,
                        'id' => 'id_option',
                        'name' => 'name'
                    )
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Materials used'),
                    'desc' => $this->l('Map store features which contains material details.'),
                    'name' => 'feature',
                    'options' => array(
                        'query' => $featureList,
                        'id' => 'id_option',
                        'name' => 'name'
                    )
                ),
                array(
                    'type' => 'hidden',
                    'label' => $this->l('Exclude Products'),
                    'desc' => $this->l('Exclude products to sync on Etsy marketplace'),
                    'name' => 'exclude_product',
                    'col' => 4,
                    'class' => 'etsy_exclude_product'
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'kbesty_exclude_product'
                ),
                array(
                    'type' => 'switch',
                    'name' => 'size_chart_image',
                    'label' => $this->l('Enable Size Chart Image'),
                    'desc' => $this->l('Enable if you want to associate an additional Image For the Product.'),
                    'values' => array(
                        array(
                            'value' => 1
                        ),
                        array(
                            'value' => 0
                        )
                    ),
                ),
                array(
                    'type' => 'file',
                    'label' => $this->l('Size Chart Image'),
                    'name' => 'banner_image',
                    'required' => true,
                    'image' => $logo_url ? $logo_url : false,
//                    'desc' => $this->module->l('For the best view, upload 30 x 30 pixel size PNG image file.'),
                    'display_image' => true,
                    'hint' => $this->l('The Uploaded image will be sent to etsy as a normal Image In addition to the product image.')
            ),
            ),
            'buttons' => array(
                array(
                    'class' => 'btn btn-default pull-right',
                    'name' => 'submit' . $this->name,
                    'js' => "validation('etsy_profiles_form')",
                    'title' => $this->module->l('Save', 'AdminEtsyProfileManagementController'),
                    'icon' => 'process-icon-save'
                )
            )
        );

        //Value assigned to setup Ajax URL to get Properties List
        $this->fields_value['property_ajax_url'] = $this->context->link->getAdminlink('AdminEtsyProfileManagement');
        $this->fields_value['update_profile_product'] = '0';


        $this->context->smarty->assign(array(
            'KbcurrentToken' => Tools::getAdminTokenLite('AdminEtsyProfileManagement'),
            'controller_path' => $this->context->link->getAdminLink('AdminEtsyProfileManagement', true),
            'product_mapping' => $product_mapping,
            'is_size_chart_image_exists' => $is_size_chart_image_exists,
            'custom_pricing_array' => $custom_pricing_array,
            'custom_pricing' => true
        ));

        /* Start-MK made changes on 22-11-2017 for display placeholder for product title */
        $customize_product_title = $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'kbetsy/views/templates/admin/customize_product_title.tpl'
        );
        $tpl = $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/kb_etsy_profile.tpl'
        );
        /* Start-MK made changes on 22-11-2017 for display placeholder for product title */
        return $customize_product_title . $tpl . parent::renderForm();
    }

    /* Exclude Product Search */

    public function kbAjaxProductList($id_lang, $start, $limit, $order_by, $order_way, $search_product = false, $only_active = true, Context $context = null)
    {
        if ($search_product) {
            $prod_query = trim(Tools::getValue('q', false));
            if (!$prod_query or $prod_query == '' or Tools::strlen($prod_query) < 1) {
                die();
            }
            /*
             * In the SQL request the "q" param is used entirely to match result in database.
             * In this way if string:"(ref : #ref_pattern#)" is displayed on the return list,
             * they are no return values just because string:"(ref : #ref_pattern#)"
             * is not write in the name field of the product.
             * So the ref pattern will be cut for the search request.
             */
            if ($pos = strpos($prod_query, ' (ref:')) {
                $prod_query = Tools::substr($prod_query, 0, $pos);
            }
        }

        $excludeIds = Tools::getValue('excludeIds', false);
        if ($excludeIds && $excludeIds != 'NaN') {
            $excludeIds = implode(',', array_map('intval', explode(',', $excludeIds)));
        } else {
            $excludeIds = '';
        }

        if (!$context) {
            $context = Context::getContext();
        }

        $front = true;
        if (!in_array($context->controller->controller_type, array('front', 'modulefront'))) {
            $front = false;
        }

        if (!Validate::isOrderBy($order_by) || !Validate::isOrderWay($order_way)) {
            die(Tools::displayError());
        }
        if ($order_by == 'id_product' || $order_by == 'price' || $order_by == 'date_add' || $order_by == 'date_upd') {
            $order_by_prefix = 'p';
        } elseif ($order_by == 'name') {
            $order_by_prefix = 'pl';
        } elseif ($order_by == 'position') {
            $order_by_prefix = 'c';
        }

        if (strpos($order_by, '.') > 0) {
            $order_by = explode('.', $order_by);
            $order_by_prefix = $order_by[0];
            $order_by = $order_by[1];
        }
        $sql = 'SELECT p.*, product_shop.*, pl.* FROM `' . _DB_PREFIX_ . 'product` p ' . Shop::addSqlAssociation('product', 'p') . ''
                . 'LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (p.`id_product` = pl.`id_product` ' . Shop::addSqlRestrictionOnLang('pl') . ') '
                . 'LEFT JOIN `' . _DB_PREFIX_ . 'etsy_products_list` epl ON (p.`id_product` = epl.`id_product`) '
                . 'WHERE (epl.id_product is null OR epl.id_etsy_profiles = 0) and p.id_product NOT in (SELECT fp.id_product FROM ' . _DB_PREFIX_ . 'etsy_exclude_product fp) AND  pl.`id_lang` = ' . (int) $id_lang .
                (!empty($excludeIds) ? ' AND p.id_product NOT IN (' . $excludeIds . ') ' : ' ') .
                (($search_product && $prod_query != '') ? ' AND (pl.name LIKE \'%' . pSQL($prod_query) . '%\' OR p.reference LIKE \'%' . pSQL($prod_query) . '%\')' : '') .
                ($front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '') .
                ($only_active ? ' AND p.`active` = 1' : '') . '
				ORDER BY ' . (isset($order_by_prefix) ? pSQL($order_by_prefix) . '.' : '') . '`' . pSQL($order_by) . '` ' . pSQL($order_way) .
                ($limit > 0 ? ' LIMIT ' . (int) $start . ',' . (int) $limit : '');
        $rq = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        return $rq;
    }

    public function postProcess()
    {
        /* Below 3-4 lines of code is no longer requured. It was added to display category selection on the Popup */
        if (!empty(Tools::getValue('action')) && Tools::getValue('action') == 'getEtsyCategory') {
            $etsy_categories = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'etsy_categories WHERE parent_id = "' . pSQL(Tools::getValue('category_code')) . '" ORDER BY category_name ASC');
            die(Tools::jsonEncode($etsy_categories));
        }

        if (Tools::isSubmit('searchKbProduct')) {
            $id_lang = $this->context->language->id;
            $product = $this->kbAjaxProductList($id_lang, 0, 0, 'price', 'desc', true);
            die(Tools::jsonEncode($product));
        }

        if (Tools::isSubmit('is_product_selected')) {
            if (Tools::getValue('ajax')) {
                $id_product = Tools::getValue('id_product');
                if ($id_product != '') {
                    $product = new Product($id_product, true, $this->context->language->id);
                    $product->link = $this->context->link->getProductLink($product);
                    $image = Image::getCover($id_product);
                    if (!empty($image)) {
                        $product->id_image = $image['id_image'];
                    } else {
                        $product->id_image = 0;
                    }
                    $this->context->smarty->assign(array(
                        'link' => $this->context->link,
                        'product' => $product,
                    ));
                    $tpl = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbetsy/views/templates/admin/kb_add_product.tpl');
                    echo $tpl;
                    die();
                }
            }
        }

        //Handle Form Submission
        if (Tools::isSubmit('submitAddetsy_profiles')) {
            $formError = 0;
            $customErrors = array();

            //Prepare variables holding  post values
            $profileTitle = pSQL(Tools::getValue('profile_title'));
            $profileEtsyCategory = Tools::getValue('etsy_category_code');
            $custmize_product_title = trim(Tools::getValue('customize_product_title'));
            $etsy_product_type = Tools::getValue('etsy_product_type');

            $storeCategoriesList = '';
            if (Tools::getValue('prestashop_category')) {
                $storeCategories = Tools::getValue('prestashop_category');
                $storeCategoriesList = implode(",", $storeCategories);
            }

            /* If Product Selection Type is Product then Save the Categories as Blank */
            if ($etsy_product_type == "1") {
                $storeCategoriesList = '';
            }

            $shippingTemplateID = Tools::getValue('id_etsy_shipping_templates');
            
            $is_customizable = '0';
            if (!empty((Tools::getValue('is_customizable')))) {
                $is_customizable = '1';
            } else if (!empty(trim(Tools::getValue('is_customizable_1')))) {
                $is_customizable = '1';
            }

            $whoMade = Tools::getValue('who_made');
            $currency = Tools::getValue('currency');
            $whenMade = Tools::getValue('when_made');

            $is_supply = '0';
            if (!empty(trim(Tools::getValue('is_supply')))) {
                $is_supply = '1';
            } else if (!empty(trim(Tools::getValue('is_supply_1')))) {
                $is_supply = '1';
            }
            
            $recipient = Tools::getValue('recipient');
            $occassion = Tools::getValue('occassion');

            /* Start-MK made changes on 22-11-2017 for fetching field values */
            $enable_min_qty = Tools::getValue('enable_min_qty');
            $min_qty = trim(Tools::getValue('min_qty'));
            $enable_max_qty = Tools::getValue('enable_max_qty');
            $should_auto_renew = Tools::getValue('should_auto_renew');
            $max_qty = trim(Tools::getValue('max_qty'));
            $property = Tools::getValue('property');
            if (!empty($property)) {
                /** Line commented by Ashish as field is hidden now */
                //$property = implode(',', $property);
            }
            $mapped_material_feature = Tools::getValue('feature');

            $kbesty_exclude_product = Tools::getValue('kbesty_exclude_product');
            if (!empty($kbesty_exclude_product)) {
                $kbesty_exclude_product = array_filter(explode('-', $kbesty_exclude_product));
            } else {
                $kbesty_exclude_product = Tools::getValue('exclude_product');
                if (!empty($kbesty_exclude_product)) {
                    $kbesty_exclude_product = array_filter(explode('-', $kbesty_exclude_product));
                }
            }
            $custom_pricing = Tools::getValue('custom_pricing');
            $custom_price = Tools::getValue('custom_price');
            $enable_size_chart_image = Tools::getValue('size_chart_image');
            $price_type = Tools::getValue('price_type');
            $price_reduction = Tools::getValue('price_reduction');

            $etsy_selected_products = Tools::getValue('kbetsy_selected_products');

            /* Remove duplicate from the selected products */
            if ($etsy_product_type == "1") {
                if (!empty($etsy_selected_products)) {
                    $etsy_selected_product_array = explode("-", $etsy_selected_products);
                    $etsy_selected_product_array = array_unique($etsy_selected_product_array);
                    $etsy_selected_products = implode("-", $etsy_selected_product_array);
                }
            } else {
                $etsy_selected_products = '';
            }

            //Validate Profile Title
            if (empty($profileTitle)) {
                $formError = 1;
                $customErrors[] = 5;
            }

            //Validate Store Categories
            if (!empty($storeCategoriesList)) {
                /* If Category is seleted in 'Product Selection Type' Dropdown */
                if ($etsy_product_type == "0") {
                    $storeCategoryExist = 0;
                    $ProfileCategoryExist = 0;
                    $storeCategoryProfileExist = 0;
                    foreach ($storeCategories as $key => $value) {
                        //SQL to check details existence
                        if (!Tools::isEmpty(trim(Tools::getValue('id_etsy_profiles')))) {
                            $selectSQL = 'SELECT count(*) as count FROM ' . _DB_PREFIX_ . 'etsy_category_mapping WHERE FIND_IN_SET("' . pSQL($value) . '", prestashop_category) AND id_etsy_profiles !=' . (int) Tools::getValue('id_etsy_profiles');
                            $dataExistenceResult = Db::getInstance()->executeS($selectSQL, true, false);

                            $dataSQL = 'SELECT count(*) as count FROM ' . _DB_PREFIX_ . 'etsy_category_mapping WHERE FIND_IN_SET("' . pSQL($value) . '", prestashop_category) AND etsy_category_code !=' . (int) $profileEtsyCategory . ' AND id_etsy_profiles =' . (int) Tools::getValue('id_etsy_profiles');
                            $dataExistenceCatRes = Db::getInstance()->executeS($dataSQL, true, false);

                            if ($dataExistenceResult[0]['count'] > 0) {
                                $storeCategoryExist = 1;
                            } elseif ($dataExistenceCatRes[0]['count'] > 0) {
                                $storeCategoryProfileExist = 1;
                            }
                        } else {
                            $selectSQL = "SELECT count(*) as count FROM " . _DB_PREFIX_ . "etsy_category_mapping WHERE FIND_IN_SET(" . pSQL($value) . ", prestashop_category)";
                            $dataExistenceResult = Db::getInstance()->executeS($selectSQL, true, false);
                            if ($dataExistenceResult[0]['count'] > 0) {
                                $storeCategoryExist = 1;
                            }
                        }
                    }

                    if ($ProfileCategoryExist) {
                        $formError = 1;
                        $customErrors[] = 28;
                    } elseif ($storeCategoryExist) {
                        $formError = 1;
                        $customErrors[] = 7;
                    } elseif ($storeCategoryProfileExist) {
                        $formError = 1;
                        $customErrors[] = 31;
                    }
                }
            }

            if (!$formError) {
                /* Start-MK made changes on 22-11-2017 to add/Update the profile based on category mapping and added new fields */
                if (!Tools::isEmpty(trim(Tools::getValue('id_etsy_profiles')))) {
                    //Update data SQL
                    $updateProfileSQL = 'UPDATE ' . _DB_PREFIX_ . 'etsy_profiles SET '
                            . 'profile_title = "' . pSQL($profileTitle) . '", '
                            . 'customize_product_title = "' . pSQL($custmize_product_title) . '", '
                            . 'id_etsy_shipping_templates =' . (int) $shippingTemplateID . ', '
                            . 'etsy_currency = "' . pSQL($currency) . '", '
                            . 'is_customizable = "' . (int) $is_customizable . '", '
                            . 'who_made = "' . pSQL($whoMade) . '", '
                            . 'when_made = "' . pSQL($whenMade) . '", '
                            . 'is_supply = "' . (int) $is_supply . '", '
                            . 'recipient = "' . pSQL($recipient) . '", '
                            . 'occassion = "' . pSQL($occassion) . '", '
                            . 'should_auto_renew = "' . (int) $should_auto_renew . '", '
                            . 'enable_max_qty = "' . (int) $enable_max_qty . '", '
                            . 'max_qty = "' . (int) $max_qty . '", '
                            . 'enable_min_qty = "' . (int) $enable_min_qty . '", '
                            . 'min_qty = "' . (int) $min_qty . '", '
                            . 'property = "' . pSQL($property) . '",'
                            . 'material_feature = "' . pSQL($mapped_material_feature) . '", '
                            . 'etsy_selected_products = "' . pSQL($etsy_selected_products) . '", '
                            . 'etsy_product_type = "' . pSQL($etsy_product_type) . '", '
                            . 'custom_pricing="' . pSQL($custom_pricing) . '", '
                            . 'custom_price="' . pSQL($custom_price) . '", '
                            . 'size_chart_image = "' . (int) $enable_size_chart_image . '", '
                            . 'price_type="' . pSQL($price_type) . '", '
                            . 'id_etsy_shop_section = "' . pSQL(Tools::getValue('id_etsy_shop_section')) . '", '
                            . 'price_reduction="' . pSQL($price_reduction) . '", '
                            . 'date_updated = NOW() '
                            . 'WHERE id_etsy_profiles = ' . (int) Tools::getValue('id_etsy_profiles');

                    /*
                    * changes by rishabh jain for saving size chart image
                    */
                    if (!empty($_FILES)) {
                        $id_etsy_profiles = (int) Tools::getValue('id_etsy_profiles');
                        // changes by rishabh jain
                        if ($_FILES['banner_image']['error'] == 0 && $_FILES['banner_image']['name'] != '' && $_FILES['banner_image']['size'] > 0) {
                            $file_extension = pathinfo($_FILES['banner_image']['name'], PATHINFO_EXTENSION);
                            $path = _PS_MODULE_DIR_ . 'kbetsy/views/img/profile/' . $id_etsy_profiles . '.' . $file_extension;
                            $exist_image = glob(_PS_MODULE_DIR_ . 'kbetsy/views/img/profile/' . $id_etsy_profiles . '.*');
                            if (isset($exist_image[0]) && file_exists($exist_image[0])) {
                                unlink($exist_image[0]);
                            }
                            move_uploaded_file(
                                $_FILES['banner_image']['tmp_name'],
                                $path
                            );
                            chmod(_PS_MODULE_DIR_ . 'kbetsy/views/img/profile/' . $id_etsy_profiles . '.' . $file_extension, 0777);
                        }
                        // changes over
                    }
                    /*
                     * chanegs over
                     */
                    Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'etsy_category_mapping SET '
                            . 'etsy_category_code = "' . pSQL($profileEtsyCategory) . '",'
                            . 'prestashop_category = "' . pSQL($storeCategoriesList) . '" '
                            . 'WHERE id_etsy_profiles = ' . (int) Tools::getValue('id_etsy_profiles'));

                    $insert_profile_category_id = Db::getInstance()->getValue('SELECT id_profile_category from ' . _DB_PREFIX_ . 'etsy_category_mapping WHERE id_etsy_profiles = ' . (int) Tools::getValue('id_etsy_profiles') . ' ORDER BY id_profile_category asc');

                    if (Db::getInstance()->execute($updateProfileSQL)) {
                        //Attribute Mapping List
                        $propertyList = Tools::getValue('property_attr');
                        if (!empty($propertyList)) {
                            Db::getInstance()->execute("DELETE FROM " . _DB_PREFIX_ . "etsy_attribute_mapping WHERE id_etsy_profiles = '" . (int) Tools::getValue('id_etsy_profiles') . "' AND id_profile_category = '" . (int) $insert_profile_category_id . "'");
                            foreach ($propertyList as $key => $value) {
                                if ($value != "") {
                                    if (is_array($value)) {
                                        $value = implode(",", $value);
                                    }
                                    $insertAttributeMappingSQL = "INSERT INTO " . _DB_PREFIX_ . "etsy_attribute_mapping (property_id, id_attribute_group, id_profile_category, id_etsy_profiles, date_added, date_updated) VALUES ('" . pSQL($key) . "', '" . pSQL($value) . "', '" . pSQL($insert_profile_category_id) . "','" . (int) Tools::getValue('id_etsy_profiles') . "', NOW(), NOW())";
                                    Db::getInstance()->execute($insertAttributeMappingSQL);
                                }
                            }
                        }

                        Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'etsy_exclude_product where id_profiles=' . (int) Tools::getValue('id_etsy_profiles') . ' AND id_shop=' . (int) $this->context->shop->id);

                        if (!empty($kbesty_exclude_product)) {
                            foreach ($kbesty_exclude_product as $product) {
                                Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'etsy_exclude_product set id_product=' . (int) $product . ', id_profiles=' . (int) Tools::getValue('id_etsy_profiles') . ',id_shop=' . (int) $this->context->shop->id);
                            }
                        }

                        $shippingTemplateTitle = EtsyModule::getShippingTemplateTitleByID($shippingTemplateID);
                        
                        /* Reset product is_error flag to so that item can be send to sync again as profile info updated & error might has been fixed */
                        DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list "
                                . "SET is_error = '0' "
                                . "WHERE id_etsy_profiles = '" . (int) Tools::getValue('id_etsy_profiles') . "'");
                        
                        /* Start-Harish made changes on 16-10-2018 to Update profile products when profile updates  */
                        if (Tools::getValue('update_profile_product', 0) == '1') {
                            DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list "
                                    . "SET listing_status = 'Updated',"
                                    . "is_error = '0' "
                                    . "WHERE  listing_id != '' AND listing_id != 0 AND listing_id IS NOT NULL "
                                    . "AND listing_status IN ('Listed','Sold Out','Inactive') "
                                    . "AND delete_flag = '0' "
                                    . "AND active = '1' "
                                    . "AND id_etsy_profiles = '" . (int) Tools::getValue('id_etsy_profiles') . "'");

                            DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET is_error = 0 WHERE id_etsy_profiles = '" . (int) Tools::getValue('id_etsy_profiles') . "'");
                        }
                        /* End-Harish made changes on 16-10-2018 for Update profile products when profile updates */

                        //Audit Log Entry
                        $auditLogEntryString = 'Profile Updated. Updated values are - <br>Profile Title: ' . $profileTitle . '<br>Etsy Category Code: ' . $profileEtsyCategory . '<br>Customize Product Title:' . $custmize_product_title . '<br>Store Categories: ' . $storeCategoriesList . '<br>Shipping Template Title: ' . $shippingTemplateTitle . '<br>Is Customizable: ' . $is_customizable . '<br>Who Made: ' . $whoMade . '<br>When Made: ' . $whenMade . '<br>Is Supply: ' . $is_supply . '<br>Recipient: ' . $recipient . '<br>Occassion: ' . $occassion;
                        $auditMethodName = 'AdminEtsyProfileManagement::postProcess()';
                        EtsyModule::auditLogEntry($auditLogEntryString, $auditMethodName);

                        Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyProfileManagement') . '&etsyConf=8');
                    }
                } else {
                    //Insert data SQL
                    $insertProfileSQL = "INSERT INTO " . _DB_PREFIX_ . "etsy_profiles (
                        profile_title, 
                        customize_product_title, 
                        id_etsy_shipping_templates, 
                        is_customizable, 
                        who_made, 
                        when_made, 
                        is_supply, 
                        recipient, 
                        occassion, 
                        active, 
                        date_added, 
                        date_updated, 
                        etsy_currency, 
                        should_auto_renew,
                        enable_max_qty, 
                        max_qty, 
                        enable_min_qty, 
                        min_qty, 
                        material_feature, 
                        property, 
                        custom_pricing, 
                        custom_price, 
                        size_chart_image,
                        price_type, 
                        price_reduction, 
                        id_etsy_shop_section,
                        etsy_selected_products,
                        etsy_product_type) VALUES ('" . pSQL($profileTitle) . "', '" . pSQL($custmize_product_title) . "','" . (int) $shippingTemplateID . "', '" . (int) $is_customizable . "', '" . pSQL($whoMade) . "', '" . pSQL($whenMade) . "', '" . (int) $is_supply . "', '" . pSQL($recipient) . "', '" . pSQL($occassion) . "', '1', NOW(), NOW(), '" . pSQL($currency) . "','" . (int) $should_auto_renew . "', '" . (int) $enable_max_qty . "','" . (int) $max_qty . "','" . (int) $enable_min_qty . "', '" . (int) $min_qty . "', '" . pSQL($mapped_material_feature) . "', '" . pSQL($property) . "', '" . pSQL($custom_pricing) . "', '" . pSQL($custom_price) . "'," . (int) $enable_size_chart_image .  ",'" . pSQL($price_type) . "', '" . pSQL($price_reduction) . "', '" . pSQL(Tools::getValue('id_etsy_shop_section')) . "', '" . pSQL($etsy_selected_products) . "','" . pSQL($etsy_product_type) . "')";

                    if (Db::getInstance()->execute($insertProfileSQL)) {
                        $id_etsy_profiles = Db::getInstance()->Insert_ID();
                        //Profile Category mapping
                        $kbprofilecateg = new EtsyProfileCategory();
                        $kbprofilecateg->id_etsy_profiles = $id_etsy_profiles;
                        $kbprofilecateg->etsy_category_code = $profileEtsyCategory;
                        $kbprofilecateg->prestashop_category = $storeCategoriesList;
                        $kbprofilecateg->add();
                        $id_profile_category = $kbprofilecateg->id;

                        /*
                         * changes by rishabh jain for saving size chart image
                         */
                        if (!empty($_FILES)) {
                            // changes by rishabh jain
                            if ($_FILES['banner_image']['error'] == 0 && $_FILES['banner_image']['name'] != '' && $_FILES['banner_image']['size'] > 0) {
                                $file_extension = pathinfo($_FILES['banner_image']['name'], PATHINFO_EXTENSION);
                                $path = _PS_MODULE_DIR_ .'kbetsy/views/img/profile/'.$id_etsy_profiles.'.'. $file_extension;
                                $exist_image = glob(_PS_MODULE_DIR_ . 'kbetsy/views/img/profile/'.$id_etsy_profiles.'.*');
                                if (file_exists($exist_image[0])) {
                                    unlink($exist_image[0]);
                                }
                                move_uploaded_file(
                                    $_FILES['banner_image']['tmp_name'],
                                    $path
                                );
                                chmod(_PS_MODULE_DIR_ . 'kbetsy/views/img/profile/'.$id_etsy_profiles.'.'. $file_extension, 0777);
                            }
                            // changes over
                        }
                        /*
                         * chanegs over
                         */
                        //Attribute Mapping List
                        $propertyList = Tools::getValue('property_attr');
                        if (!empty($propertyList)) {
                            foreach ($propertyList as $key => $value) {
                                if ($value != "") {
                                    if (is_array($value)) {
                                        $value = implode(",", $value);
                                    }
                                    Db::getInstance()->execute("INSERT INTO " . _DB_PREFIX_ . "etsy_attribute_mapping (property_id, id_attribute_group, id_profile_category, id_etsy_profiles, date_added, date_updated) VALUES ('" . pSQL($key) . "', '" . pSQL($value) . "', '" . pSQL($id_profile_category) . "','" . (int) $id_etsy_profiles . "', NOW(), NOW())");
                                }
                            }
                        }

                        if (!empty($kbesty_exclude_product)) {
                            foreach ($kbesty_exclude_product as $product) {
                                Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'etsy_exclude_product set id_product=' . (int) $product . ', id_profiles=' . (int) $id_etsy_profiles . ',id_shop=' . (int) $this->context->shop->id);
                            }
                        }

                        $shippingTemplateTitle = EtsyModule::getShippingTemplateTitleByID($shippingTemplateID);

                        //Audit Log Entry
                        $auditLogEntryString = 'Profile added successfully. Added values are - <br>Profile Title: ' . $profileTitle . '<br>Customize Product Title:' . $custmize_product_title . '<br>Etsy Category Code: ' . $profileEtsyCategory . '<br>Store Categories: ' . $storeCategoriesList . '<br>Shipping Template Title: ' . $shippingTemplateTitle . '<br>Is Customizable: ' . $is_customizable . '<br>Who Made: ' . $whoMade . '<br>When Made: ' . $whenMade . '<br>Is Supply: ' . $is_supply . '<br>Recipient: ' . $recipient . '<br>Occassion: ' . $occassion;
                        $auditMethodName = 'AdminEtsyProfileManagement::postProcess()';
                        EtsyModule::auditLogEntry($auditLogEntryString, $auditMethodName);
                        Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyProfileManagement') . '&etsyConf=9');
                    }
                }
                /* End-MK made changes on 22-11-2017 to add/Update the profile based on category mapping and added new fields */
            } else {
                if (empty($customErrors)) {
                    $customErrors[] = 9;
                }
                Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyProfileManagement') . '&etsyError=' . implode(",", $customErrors));
            }
        } else if (!Tools::isEmpty(trim(Tools::getValue('status'))) && !Tools::isEmpty(trim(Tools::getValue('id_etsy_profiles')))) { //Status Updated
            //Get Profile Details
            $getProfileDetails = EtsyProfiles::getProfileDetails(Tools::getValue('id_etsy_profiles'));

            if (Tools::getValue('status') == 'Disable') {
                if (DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_profiles SET active = '0' WHERE id_etsy_profiles = '" . (int) Tools::getValue('id_etsy_profiles') . "'")) {
                    //Audit Log Entry
                    $auditLogEntryString = 'Profile - <b>' . $getProfileDetails[0]['profile_title'] . '</b> Disabled.';
                    $auditMethodName = 'AdminEtsyProfileManagement::postProcess()';
                    EtsyModule::auditLogEntry($auditLogEntryString, $auditMethodName);

                    Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyProfileManagement') . '&etsyConf=10');
                }
            } else if (Tools::getValue('status') == 'Enable') {
                if (DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_profiles SET active = '1' WHERE id_etsy_profiles = '" . (int) Tools::getValue('id_etsy_profiles') . "'")) {
                    //Audit Log Entry
                    $auditLogEntryString = 'Profile - <b>' . $getProfileDetails[0]['profile_title'] . '</b> Enabled.';
                    $auditMethodName = 'AdminEtsyProfileManagement::postProcess()';
                    EtsyModule::auditLogEntry($auditLogEntryString, $auditMethodName);

                    Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyProfileManagement') . '&etsyConf=11');
                }
            }
        } else if (!Tools::isEmpty(trim(Tools::getValue('action'))) && Tools::getValue('action') == 'delete') {
            $this->processDelete();
        } else {
            parent::postProcess();
        }
        $this->content = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbetsy/views/templates/admin/velovalidation.tpl');
    }

    //To delete profile
    public function processDelete()
    {
        if (!Tools::isEmpty(trim(Tools::getValue('id_etsy_profiles')))) {
            //Get Profile Details
            $getProfileDetails = EtsyProfiles::getProfileDetails(Tools::getValue('id_etsy_profiles'));

            //SQL Query to delete Profile
            $deleteProfileSQL = "DELETE FROM " . _DB_PREFIX_ . "etsy_profiles WHERE id_etsy_profiles = '" . (int) Tools::getValue('id_etsy_profiles') . "';";
            if (Db::getInstance()->execute($deleteProfileSQL)) {
                Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'etsy_category_mapping WHERE id_etsy_profiles=' . (int) Tools::getValue('id_etsy_profiles'));
                Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'etsy_attribute_mapping WHERE id_etsy_profiles=' . (int) Tools::getValue('id_etsy_profiles'));
                $this->deleteProfileRelatedProduct(Tools::getValue('id_etsy_profiles'));

                //Audit Log Entry
                $auditLogEntryString = 'Profile - <b>' . $getProfileDetails[0]['profile_title'] . '</b> Deleted';
                $auditMethodName = 'AdminEtsyProfileManagement::processDelete()';
                EtsyModule::auditLogEntry($auditLogEntryString, $auditMethodName);

                Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyProfileManagement') . '&etsyConf=12');
            } else {
                //Audit Log Entry
                $auditLogEntryString = 'Deletion of Profile - <b>' . $getProfileDetails[0]['profile_title'] . '</b> Failed';
                $auditMethodName = 'AdminEtsyProfileManagement::processDelete()';
                EtsyModule::auditLogEntry($auditLogEntryString, $auditMethodName);
                Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyProfileManagement') . '&etsyError=8');
            }
        }
    }

    private function deleteProfileRelatedProduct($profile_id)
    {
        $getAllProfileProductSQL = 'SELECT * FROM ' . _DB_PREFIX_ . 'etsy_products_list WHERE id_etsy_profiles = ' . (int) $profile_id;
        $getAllProfileProducts = Db::getInstance()->executeS($getAllProfileProductSQL);
        if (count($getAllProfileProducts)) {
            foreach ($getAllProfileProducts as $singleProduct) {
                if (Tools::isEmpty($singleProduct['listing_id'])) {
                    Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'etsy_products_list WHERE id_product = ' . (int) $singleProduct['id_product'] . ' AND id_etsy_profiles =' . (int) $profile_id);
                } else {
                    DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET delete_flag = '1', renew_flag = '0', listing_status = 'Inactive', id_etsy_profiles = NULL WHERE id_product = " . (int) $singleProduct['id_product'] . " AND id_etsy_profiles = " . (int) $profile_id);
                }
            }
        }
    }

    //To check category mapping exist
    public function ajaxCheckCategoryExist()
    {
        $customErrors = '';
        $storeCategoriesList = '';
        if (Tools::getValue('prestashop_category')) {
            $storeCategories = Tools::getValue('prestashop_category');
            $storeCategoriesList = explode(",", $storeCategories);
        }
        $profileEtsyCategory = Tools::getValue('etsy_category_code');
        if (empty($storeCategoriesList)) {
            $customErrors = $this->l('Please select store categories to map with Profile.');
        } else {
            if (isset($storeCategories)) {
                /* Start-MK made changes on 22-11-2017 for display error message based on category mapping */
                $storeCategoryExist = 0;
                $ProfileCategoryExist = 0;
                $storeCategoryProfileExist = 0;
                foreach ($storeCategoriesList as $key => $value) {
                    //SQL to check details existence
                    if (!Tools::isEmpty($value)) {
                        if (!Tools::isEmpty(trim(Tools::getValue('id_etsy_profiles')))) {
                            $selectSQL = 'SELECT count(*) as count FROM ' . _DB_PREFIX_ . 'etsy_category_mapping WHERE FIND_IN_SET("' . pSQL($value) . '", prestashop_category) AND id_etsy_profiles !=' . (int) Tools::getValue('id_etsy_profiles');
                            $dataSQL = 'SELECT count(*) as count FROM ' . _DB_PREFIX_ . 'etsy_category_mapping WHERE FIND_IN_SET("' . pSQL($value) . '", prestashop_category) AND etsy_category_code !=' . (int) $profileEtsyCategory . ' AND id_etsy_profiles =' . (int) Tools::getValue('id_etsy_profiles');
                            $dataExistenceResult = Db::getInstance()->executeS($selectSQL, true, false);
                            $dataExistenceCatRes = Db::getInstance()->executeS($dataSQL, true, false);
                            if ($dataExistenceResult[0]['count'] > 0) {
                                $storeCategoryExist = 1;
                            } elseif ($dataExistenceCatRes[0]['count'] > 0) {
                                $storeCategoryProfileExist = 1;
                            }
                        } else {
                            $selectSQL = "SELECT count(*) as count FROM " . _DB_PREFIX_ . "etsy_category_mapping WHERE FIND_IN_SET(" . pSQL($value) . ", prestashop_category)";
                            $dataExistenceResult = Db::getInstance()->executeS($selectSQL, true, false);
                            if ($dataExistenceResult[0]['count'] > 0) {
                                $storeCategoryExist = 1;
                            }
                        }
                    }
                }

                if ($ProfileCategoryExist) {
                    $customErrors = $this->l('Profile and Etsy Category already exist.');
                } elseif ($storeCategoryExist) {
                    $customErrors = $this->l('Profile already exists for atleast one of selected categories.');
                } elseif ($storeCategoryProfileExist) {
                    $customErrors = $this->l('Store category already exist with other Etsy Category.');
                }
            }
        }
        die($customErrors);
    }

    //To get get properties list of an Etsy Category, based on the selected category on the profile
    public function ajaxGetPropertiesList()
    {
        $propertiesListHTML = '';
        if (!Tools::isEmpty(trim(Tools::getValue('category_code')))) {
            $propertSetJson = Tools::file_get_contents("https://www.etsy.com/api/v3/ajax/public/taxonomy/" . trim(Tools::getValue('category_code')) . "/properties");
            $propertySet = Tools::jsonDecode($propertSetJson, true);
            if (!empty($propertySet)) {
                $propertiesListHTML = $this->displayPropertiesList($propertySet);
            }
        }
        echo $this->displayAttributeMappingSection($propertiesListHTML);
        die();
    }

    /** Display Attribute Mapping Section */
    public function displayAttributeMappingSection($propertiesListHTML)
    {
        $this->context->smarty->assign(array(
            'attribute_mapping' => $propertiesListHTML,
        ));
        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbetsy/views/templates/admin/attribute_mapping.tpl');
    }

    /** Display Attribute Dropdown */
    public function displayPropertiesList($properties = array())
    {
        $fields = array();
        if (!empty($properties)) {
            //Get Mapped Attribute
            $selected_attribute = array();
            if (!Tools::isEmpty(trim(Tools::getValue('id_etsy_profiles')))) {
                $mappedAttribute = DB::getInstance()->executeS("SELECT * FROM " . _DB_PREFIX_ . "etsy_attribute_mapping WHERE id_etsy_profiles = '" . (int) Tools::getValue('id_etsy_profiles') . "'");
                if (!empty($mappedAttribute)) {
                    foreach ($mappedAttribute as $mapped) {
                        $selected_attribute[] = array("id" => $mapped['property_id'], "value" => $mapped['id_attribute_group']);
                    }
                }
            }

            foreach ($properties as $propery) {
                if ($propery["attributeId"] != null && $propery["attributeId"] != 3) {
                    $selected_values = array();
                    $flag = false;

                    /* Popuplate Selecte Values from the DB Values */
                    foreach ($selected_attribute as $attribute) {
                        if ($attribute["id"] == $propery["propertyId"]) {
                            $selected_value = $attribute["value"];
                            $selected_values = explode(",", $selected_value);
                            $flag = true;
                        }
                    }

                    /* In case no DB selected value, Pick the default selected value from the Etsy */
                    if ($flag == false) {
                        if (isset($propery['selectedValues'])) {
                            foreach ($propery['selectedValues'] as $selectedValues) {
                                $selected_values[] = $selectedValues['id'];
                            }
                        }
                    }

                    $fields[] = array(
                        'type' => 'checkbox',
                        'name' => $propery['name'],
                        'multi' => $propery['isMultiValued'],
                        'id' => $propery['propertyId'],
                        'required' => $propery['isRequired'],
                        'values' => $propery['possibleValues'],
                        'selected' => $selected_values
                    );
                }
            }

            $this->context->smarty->assign(array(
                'property_list' => $fields
            ));

            return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbetsy/views/templates/admin/properties_list.tpl');
        } else {
            return "";
        }
    }

    /** Display Attribute Dropdown  */
    public function displayAttributeDropdown($attributeID = '')
    {
        //Get Attribute List
        $getAttributeList = AttributeGroup::getAttributesGroups($this->context->language->id);

        $this->context->smarty->assign(array(
            'options' => $getAttributeList,
            'attribute_id' => $attributeID
        ));

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbetsy/views/templates/admin/attribute_dropdown.tpl');
    }

    /** Display Enable/Disable action link  */
    public function displayStatusLink($token = null, $id = null, $name = null)
    {
        $getProfileStatus = EtsyProfiles::getProfileDetails($id, 'active');
        $status_text = 'Enable';
        $status = $this->l('Enable');
        $icon = 'circle';
        if ($getProfileStatus[0]['active'] == 1) {
            $status = $this->l('Disable');
            $status_text = 'Disable';
            $icon = 'circle-o';
        }
        if (!array_key_exists($status, self::$cache_lang)) {
            self::$cache_lang[$status] = $this->module->l($status, 'Helper');
        }

        $this->context->smarty->assign(array(
            'href' => $this->context->link->getAdminlink('AdminEtsyProfileManagement') . '&' . $this->identifier . '=' . $id . '&status=' . $status_text,
            'action' => $status,
            'icon' => $icon
        ));

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbetsy/views/templates/admin/list/list_action.tpl');
    }

    /** Display Enable/Disable action link  */
    public function displayDeleteLink($token = null, $id = null, $name = null)
    {
        $this->context->smarty->assign(array(
            'href' => $this->context->link->getAdminlink('AdminEtsyProfileManagement') . '&' . $this->identifier . '=' . $id . '&action=delete',
            'action' => $this->l('Delete'),
            'icon' => 'trash',
            'warning_message' => $this->l('Are you sure to delete the profile? Corresponding profile item will also be deleted from the Etsy.')
        ));

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbetsy/views/templates/admin/list/list_action_confirmation.tpl');
    }

    /** Display Category Mapping action link  */
    public function displayCategoryLink($token = null, $id = null, $name = null)
    {
        $category = 'profilecategory';

        if (!array_key_exists($category, self::$cache_lang)) {
            self::$cache_lang[$category] = $this->l('View Mapped Category');
        }

        $this->context->smarty->assign(array(
            'href' => $this->context->link->getAdminlink('AdminEtsyProfileCategoryMapping') . '&' . $this->identifier . '=' . $id . '&profilecategory',
            'action' => self::$cache_lang[$category],
            'icon' => 'search-plus',
        ));

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbetsy/views/templates/admin/list/list_action.tpl');
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

    public function initPageHeaderToolbar()
    {
        /*
         * @author - Rishabh Jain
         * DOC - 2nd Apr 2020
         * To remove the toolbar header if categories or shipping templates are empty
         */
        $etsy_categories = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'etsy_categories WHERE parent_id = 0 ORDER BY category_name ASC');
        if ((EtsyShippingTemplates::getTotalTeamplates() <= 0) || empty($etsy_categories)) {
        } else {
            if (!Tools::getValue('id_etsy_profiles') && !Tools::isSubmit('addetsy_profiles')) {
                $this->page_header_toolbar_btn['new_template'] = array(
                    'href' => self::$currentIndex . '&add' . $this->table . '&token=' . $this->token,
                    'desc' => $this->l('Add new'),
                    'icon' => 'process-icon-new'
                );
                $secure_key = Configuration::get('KBETSY_SECURE_KEY');
                $this->page_header_toolbar_btn['kb_sync_translation'] = array(
                    'href' => $this->context->link->getModuleLink('kbetsy', 'cron', array('action' => 'localSync', 'secure_key' => $secure_key)),
                    'target' => '_blank',
                    'desc' => $this->l('Local Sync'),
                    'icon' => 'process-icon-update'
                );
            }
            if (Tools::getValue('id_etsy_profiles') || Tools::isSubmit('id_etsy_profiles') || Tools::isSubmit('addetsy_profiles')) {
                $this->page_header_toolbar_btn['kb_cancel_action'] = array(
                    'href' => self::$currentIndex . '&token=' . $this->token,
                    'desc' => $this->l('Cancel'),
                    'icon' => 'process-icon-cancel'
                );
            }
        }
        parent::initPageHeaderToolbar();
    }
}
