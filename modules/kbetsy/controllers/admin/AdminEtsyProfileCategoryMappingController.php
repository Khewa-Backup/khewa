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
require_once(_PS_MODULE_DIR_ . 'kbetsy/classes/EtsyProfiles.php');
require_once(_PS_MODULE_DIR_ . 'kbetsy/classes/EtsyProfileCategory.php');

class AdminEtsyProfileCategoryMappingController extends ModuleAdminController
{

    //Class Constructor
    public function __construct()
    {
//        $this->name = 'AdminEtsyProfileCategoryMapping1';
        $this->context = Context::getContext();
        $this->bootstrap = true;
        $this->list_no_link = true;
        $this->table = 'etsy_category_mapping';
        $this->className = 'EtsyProfileCategory';
        $this->identifier = 'id_profile_category';
        $this->display = 'list';
        parent::__construct();
        $this->fields_list = array(
            'id_profile_category' => array(
                'search' => false,
                'title' => $this->module->l('ID', 'AdminEtsyProfileCategoryMappingController'),
                'align' => 'center',
                'class' => 'fixed-width-xs'
            ),
            'profile_title' => array(
                'search' => false,
                'title' => $this->module->l('Profile Title', 'AdminEtsyProfileCategoryMappingController'),
            ),
            'category_name' => array(
                'search' => false,
                'title' => $this->module->l('Etsy Category', 'AdminEtsyProfileCategoryMappingController'),
                'align' => 'center'
            ),
            'date_added' => array(
                'search' => false,
                'title' => $this->module->l('Added On', 'AdminEtsyProfileCategoryMappingController'),
                'align' => 'center',
                'type' => 'datetime',
            ),
            'date_updated' => array(
                'search' => false,
                'title' => $this->module->l('Updated On', 'AdminEtsyProfileCategoryMappingController'),
                'align' => 'center',
                'type' => 'datetime',
            )
        );
        
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->_select = 'ec.*,ep.*';
        $this->_join = '
                LEFT JOIN ' . _DB_PREFIX_ . 'etsy_categories ec ON (ec.category_code = a.etsy_category_code)
                LEFT JOIN ' . _DB_PREFIX_ . 'etsy_profiles ep ON (ep.id_etsy_profiles = a.id_etsy_profiles)';
        $this->_where = ' AND ep.id_etsy_profiles='.(int)Tools::getValue('id_etsy_profiles');
        
//        $this->context->cookie->__set('profile_id', Tools::getValue('id_etsy_profiles'));
        //This is to show notification messages to admin
        if (!Tools::isEmpty(trim(Tools::getValue('etsyConf')))) {
            new EtsyModule(Tools::getValue('etsyConf'), 'conf');
        }

        if (!Tools::isEmpty(trim(Tools::getValue('etsyError')))) {
            new EtsyModule(Tools::getValue('etsyError'), 'error');
        }
        
        if (Tools::isSubmit('addetsy_category_mapping')) {
            $this->toolbar_title = $this->l('Add Etsy Profile Category Mapping', 'AdminEtsyProfileCategoryMappingController');
        } elseif (Tools::isSubmit('updateetsy_category_mapping')) {
            $this->toolbar_title = $this->l('Update Etsy Profile Category Mapping', 'AdminEtsyProfileCategoryMappingController');
        } else {
            $this->toolbar_title = $this->l('Etsy Profile Category Mapping', 'AdminEtsyProfileCategoryMappingController');
        }
    }
    
    /**
     * Function used display toolbar in page header
     */
    public function initPageHeaderToolbar()
    {
        $this->page_header_toolbar_btn['back_url'] = array(
            'href' => 'javascript: window.history.back();',
            'desc' => $this->module->l('Back', 'AdminEtsyProfileCategoryMappingController'),
            'icon' => 'process-icon-back'
        );
        
//        if (Tools::isEmpty(Tools::getValue('id_etsy_profiles')) && (!Tools::isSubmit('add'.$this->table))) {
        $this->page_header_toolbar_btn['new_template'] = array(
            'href' => self::$currentIndex.'&add'.$this->table.'&id_etsy_profiles='.Tools::getValue('id_etsy_profiles').'&token='.$this->token,
            'desc' => $this->module->l('Add new category', 'AdminEtsyProfileCategoryMappingController'),
            'icon' => 'process-icon-new'
        );
//        }
        
        parent::initPageHeaderToolbar();
    }
    
    public function initToolbar()
    {
        parent::initToolbar();
        unset($this->toolbar_btn['new']);
    }
    
    public function initContent()
    {
        parent::initContent();
    }
    
    /**
     * Render a form
     */
    public function renderForm()
    {
        $this->table = 'etsy_category_mapping';
        $this->className = 'EtsyProfileCategory';
        if (!Tools::isEmpty(trim(Tools::getValue('id_etsy_profiles')))) {
            $categoryTreeSelection = array();
            $getCategoryMapping = EtsyProfileCategory::getProfileCategory(Tools::getValue('id_etsy_profiles'));
            
            if (!Tools::isEmpty(Tools::getValue('id_profile_category'))) {
                $kbProfileCat = new EtsyProfileCategory(Tools::getValue('id_profile_category'));
                $this->fields_value = array(
                    'etsy_category_code' => $kbProfileCat->etsy_category_code,
                );
                
                if (!empty($kbProfileCat->prestashop_category)) {
                    $categoryTreeSelection = explode(",", $kbProfileCat->prestashop_category);
                }
            }
            
             
            //Prepare array of Etsy Categories List
            $getCategoriesListSQL = "SELECT category_code, category_name FROM " . _DB_PREFIX_ . "etsy_categories";
            $getCategoriesList = Db::getInstance()->executeS($getCategoriesListSQL, true, false);

            if ($getCategoriesList) {
                $etsyCategoriesList = array();
                foreach ($getCategoriesList as $getCategoriesList) {
                    $etsyCategoriesList[] = array(
                        'id_option' => $getCategoriesList['category_code'],
                        'name' => $getCategoriesList['category_code'] . '|' . $getCategoriesList['category_name']
                    );
                }
            }
            
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
            
            
            $this->fields_form = array(
                'legend' => array(
                    'title' => !Tools::isEmpty(trim(Tools::getValue('id_profile_category'))) ? $this->module->l('Update Category', 'AdminEtsyProfileCategoryMappingController') : $this->module->l('Add New Category', 'AdminEtsyProfileCategoryMappingController'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'hidden',
                        'name' => 'property_ajax_url'
                    ),
                    array(
                        'type' => 'hidden',
                        'name' => 'id_etsy_profiles'
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->module->l('Etsy Category', 'AdminEtsyProfileCategoryMappingController'),
                        'desc' => $this->module->l('Choose an Etsy Marketplace Category to list attributes', 'AdminEtsyProfileCategoryMappingController'),
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
                        'type' => 'categories_select',
                        'label' => $this->module->l('Store category', 'AdminEtsyProfileCategoryMappingController'),
                        'desc' => $this->module->l('Select Store Category', 'AdminEtsyProfileCategoryMappingController'),
                        'name' => 'prestashop_category',
                        'required' => true,
                        'category_tree' => $categoryTreePresta //This is the category_tree called in form.tpl
                    ),
                ),
                'submit' => array(
                        'class' => 'btn btn-default pull-right',
//                        'js' => "validation('etsy_profiles_category_form')",
                        'title' => $this->module->l('Save', 'AdminEtsyProfileCategoryMappingController'),
                )
            );
            //Value assigned to setup Ajax URL to get Properties List
            $this->fields_value['property_ajax_url'] = $this->context->link->getAdminlink('AdminEtsyProfileManagement');
        }
//        d($this->context->link->getAdminLink('AdminEtsyProfileCategoryMapping', true).'&id_etsy_profiles='.Tools::getValue('id_etsy_profiles'));
        return parent::renderForm();
    }
    
    public function postProcess()
    {
//        d(Tools::getAllValues());
        parent::postProcess();
//        return parent::postProcess();
        $this->content = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbetsy/views/templates/admin/velovalidation.tpl');
    }
    
    public function processAdd()
    {
        if (Tools::isSubmit('submitAdd'. $this->table)) {
            $profileEtsyCategory = Tools::getValue('etsy_category_code');
            $propertyList = Tools::getValue('property_id');
            $attributeList = Tools::getValue('attribute_list');
            $storeCategoriesList = '';
            if (Tools::getValue('prestashop_category')) {
                $storeCategories = Tools::getValue('prestashop_category');
                $storeCategoriesList = implode(",", $storeCategories);
            }
            $formError = 0;
            $customErrors = array();
            $propertyList = Tools::getValue('property_id');
            $attributeList = Tools::getValue('attribute_list');
                
            if (!empty($attributeList) && !empty($propertyList)) {
                foreach ($propertyList as $key => $value) {
                    $propertyDetails = explode("|", $value);
                }
            }

            //Validate Store Categories
            if (empty($storeCategoriesList)) {
                $formError = 1;
                $customErrors[] = 6;
            } else {
                if (isset($storeCategories)) {
                    $storeCategoryExist = 0;
                    $ProfileCategoryExist = 0;
                    foreach ($storeCategories as $key => $value) {
                        //SQL to check details existence
                        if (!Tools::isEmpty(trim(Tools::getValue('id_etsy_profiles')))) {
//                            $etsyProfileCat = 'SELECT count(*) as count FROM '._DB_PREFIX_.'etsy_category_mapping WHERE etsy_category_code='.(int)$profileEtsyCategory.' AND id_etsy_profiles = ' . (int) Tools::getValue('id_etsy_profiles');
                            $selectSQL = 'SELECT count(*) as count FROM ' . _DB_PREFIX_ . 'etsy_category_mapping WHERE FIND_IN_SET("' . pSQL($value) . '", prestashop_category) AND id_etsy_profiles != ' . (int) Tools::getValue('id_etsy_profiles');
                            $ProfileCategory = 'SELECT count(*) as count FROM ' . _DB_PREFIX_ . 'etsy_category_mapping WHERE FIND_IN_SET("' . pSQL($value) . '", prestashop_category) AND id_etsy_profiles = ' . (int) Tools::getValue('id_etsy_profiles');
                        }
//                        $dataExistenceProfileCat = Db::getInstance()->executeS($etsyProfileCat, true, false);
                        $dataExistenceResult = Db::getInstance()->executeS($selectSQL, true, false);
                        $dataExistenceCategory = Db::getInstance()->executeS($ProfileCategory, true, false);
//                        if ($dataExistenceProfileCat[0]['count'] >0) {
//                            $ProfileCategoryExist = 1;
//                        } else
                        if ($dataExistenceResult[0]['count'] > 0) {
                            $storeCategoryExist = 1;
                        } elseif ($dataExistenceCategory[0]['count'] > 0) {
                            $storeCategoryExist = 1;
                        }
                    }
                    
                    if ($ProfileCategoryExist) {
                        $formError = 1;
                        $customErrors[] = 28;
                    } elseif ($storeCategoryExist) {
                        $formError = 1;
                        $customErrors[] = 7;
                    }
                }
            }
            
            if (!$formError) {
                $etsyMapping = new EtsyProfileCategory();
                $etsyMapping->etsy_category_code = $profileEtsyCategory;
                $etsyMapping->prestashop_category = $storeCategoriesList;
                $etsyMapping->id_etsy_profiles = Tools::getValue('id_etsy_profiles');
                $etsyMapping->add();
                $id_profile_category = $etsyMapping->id;
                
                //attribute Mapping
                $propertyList = Tools::getValue('property_id');
                $attributeList = Tools::getValue('attribute_list');
                
                if (!empty($attributeList) && !empty($propertyList)) {
                    foreach ($propertyList as $key => $value) {
                        $propertyDetails = explode("|", $value);
                        //Check Attribute Mapping Existence
                        $selectSQL = "SELECT count(*) as count FROM " . _DB_PREFIX_ . "etsy_attribute_mapping WHERE property_id = '" . (int) $propertyDetails[0] . "' AND id_attribute_group = '" . (int) $attributeList[$key] . "' AND id_etsy_profiles = '" . (int) Tools::getValue('id_etsy_profiles') . "' AND id_profile_category='" . (int) $id_profile_category . "'";
                        $dataExistenceResult = Db::getInstance()->executeS($selectSQL, true, false);
                        if ($dataExistenceResult[0]['count'] == 0) {
                            $insertAttributeMappingSQL = "INSERT INTO " . _DB_PREFIX_ . "etsy_attribute_mapping VALUES (NULL, '" . (int) $propertyDetails[0] . "', '" . pSQL($propertyDetails[1]) . "','" . (int) $id_profile_category . "' ,'" . (int) Tools::getValue('id_etsy_profiles') . "', '" . (int) $attributeList[$key] . "', NOW(), NOW())";
                            Db::getInstance()->execute($insertAttributeMappingSQL);
                        }
                    }
                }
                $kbEtsy = new KbEtsy();
                $kbEtsy->sendCurlRequestToFront(0, 'syncProfileProducts');
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminEtsyProfileCategoryMapping').'&etsyConf=54&id_etsy_profiles='.Tools::getValue('id_etsy_profiles'));
            } else {
                if (empty($customErrors)) {
                    $customErrors[] = 9;
                }
                Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyProfileCategoryMapping') . '&id_etsy_profiles='.Tools::getValue('id_etsy_profiles').'&etsyError=' . implode(",", $customErrors));
            }
        }
    }
    
    public function processUpdate()
    {
        if (Tools::isSubmit('submitAdd'. $this->table)) {
            $profileEtsyCategory = Tools::getValue('etsy_category_code');
            $propertyList = Tools::getValue('property_id');
            $attributeList = Tools::getValue('attribute_list');
            $storeCategoriesList = '';
            if (Tools::getValue('prestashop_category')) {
                $storeCategories = Tools::getValue('prestashop_category');
                $storeCategoriesList = implode(",", $storeCategories);
            }
            $formError = 0;
            $customErrors = array();
            //Validate Store Categories
            if (empty($storeCategoriesList)) {
                $formError = 1;
                $customErrors[] = 6;
            } else {
                if (isset($storeCategories)) {
                    $storeCategoryExist = 0;
                    $ProfileCategoryExist = 0;
                    foreach ($storeCategories as $key => $value) {
                        //SQL to check details existence
                        if (!Tools::isEmpty(trim(Tools::getValue('id_etsy_profiles')))) {
//                            $etsyProfileCat = 'SELECT count(*) as count FROM '._DB_PREFIX_.'etsy_category_mapping WHERE etsy_category_code ='.(int)$profileEtsyCategory.' AND id_etsy_profiles = ' . (int) Tools::getValue('id_etsy_profiles').' AND id_profile_category !='.(int)Tools::getValue('id_profile_category');
                            $selectSQL = 'SELECT count(*) as count FROM ' . _DB_PREFIX_ . 'etsy_category_mapping WHERE FIND_IN_SET("' . pSQL($value) . '", prestashop_category) AND id_profile_category !='.(int)Tools::getValue('id_profile_category');
                        }
//                        $dataExistenceProfileCat = Db::getInstance()->executeS($etsyProfileCat, true, false);
                        $dataExistenceResult = Db::getInstance()->executeS($selectSQL, true, false);
//                        if ($dataExistenceProfileCat[0]['count'] >0) {
//                            $ProfileCategoryExist = 1;
//                        } else
                        if ($dataExistenceResult[0]['count'] > 0) {
                            $storeCategoryExist = 1;
                        }
                    }
                    
                    if ($ProfileCategoryExist) {
                        $formError = 1;
                        $customErrors[] = 28;
                    } elseif ($storeCategoryExist) {
                        $formError = 1;
                        $customErrors[] = 7;
                    }
                }
            }
            
            if (!$formError) {
                $etsyMapping = new EtsyProfileCategory(Tools::getValue('id_profile_category'));
                $etsyMapping->etsy_category_code = $profileEtsyCategory;
                $etsyMapping->prestashop_category = $storeCategoriesList;
                $etsyMapping->id_etsy_profiles = Tools::getValue('id_etsy_profiles');
                $etsyMapping->update();
                
                //attribute mapping
                if (!empty($attributeList) && !empty($propertyList)) {
                    $deleteSQL = "DELETE FROM " . _DB_PREFIX_ . "etsy_attribute_mapping WHERE id_etsy_profiles = '" . (int) Tools::getValue('id_etsy_profiles') . "' AND id_profile_category='" . (int) Tools::getValue('id_profile_category') . "'";
                    Db::getInstance()->execute($deleteSQL);
                    foreach ($propertyList as $key => $value) {
                        $propertyDetails = explode("|", $value);
                        //Check Attribute Mapping Existence
                        $selectSQL = "SELECT count(*) as count FROM " . _DB_PREFIX_ . "etsy_attribute_mapping WHERE property_id = '" . (int) $propertyDetails[0] . "' AND id_attribute_group = '" . (int) $attributeList[$key] . "' AND id_etsy_profiles = '" . (int) Tools::getValue('id_etsy_profiles') . "' AND id_profile_category='" . (int) Tools::getValue('id_profile_category') . "'";
                        $dataExistenceResult = Db::getInstance()->executeS($selectSQL, true, false);
                        if ($dataExistenceResult[0]['count'] == 0) {
                            $insertAttributeMappingSQL = "INSERT INTO " . _DB_PREFIX_ . "etsy_attribute_mapping VALUES (NULL, '" . (int) $propertyDetails[0] . "', '" . pSQL($propertyDetails[1]) . "','" . (int) Tools::getValue('id_profile_category') . "' ,'" . (int) Tools::getValue('id_etsy_profiles') . "', '" . (int) $attributeList[$key] . "', NOW(), NOW())";
                            Db::getInstance()->execute($insertAttributeMappingSQL);
                        }
                    }
                }
                $kbEtsy = new KbEtsy();
                $kbEtsy->sendCurlRequestToFront(0, 'syncProfileProducts');
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminEtsyProfileCategoryMapping').'&etsyConf=55&id_etsy_profiles='.Tools::getValue('id_etsy_profiles'));
            } else {
                if (empty($customErrors)) {
                    $customErrors[] = 9;
                }
                Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyProfileCategoryMapping') . '&id_etsy_profiles='.Tools::getValue('id_etsy_profiles').'&etsyError=' . implode(",", $customErrors));
            }
        }
    }
    
    public function displayEditLink($token, $id)
    {
        if (!array_key_exists('update', self::$cache_lang)) {
            self::$cache_lang['update'] = $this->module->l('Edit', 'AdminEtsyProfileCategoryMappingController');
        }

        $this->context->smarty->assign(
            array(
                    'href' => self::$currentIndex .
                    '&' . $this->identifier . '=' . $id .
                    '&update'.$this->table.'&id_etsy_profiles='.Tools::getValue('id_etsy_profiles').'&token=' . ($token != null ? $token : $this->token),
                    'action' => self::$cache_lang['update'],
                    )
        );

        return $this->context->smarty->fetch('helpers/list/list_action_edit.tpl');
    }
    
    public function displayDeleteLink($token, $id)
    {
        if (!array_key_exists('delete', self::$cache_lang)) {
            self::$cache_lang['delete'] = $this->module->l('Delete', 'AdminEtsyProfileCategoryMappingController');
        }

        $this->context->smarty->assign(
            array(
                'href' => self::$currentIndex .
                '&' . $this->identifier . '=' . $id .
                '&delete'.$this->table.'&id_etsy_profiles='.Tools::getValue('id_etsy_profiles').'&token=' . ($token != null ? $token : $this->token),
                'action' => self::$cache_lang['delete'],
            )
        );

        return $this->context->smarty->fetch('helpers/list/list_action_edit.tpl');
    }
    
    public function processDelete()
    {
        $customErrors = array();
        $check_category = Db::getInstance()->getValue('SELECT count(*) as count from '._DB_PREFIX_.'etsy_category_mapping WHERE id_profile_category!='.(int)Tools::getValue('id_profile_category'));
        if ($check_category <= 0) {
            $customErrors[] = 30;
            Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyProfileCategoryMapping') . '&id_etsy_profiles='.Tools::getValue('id_etsy_profiles').'&etsyError=' . implode(",", $customErrors));
        }
        parent::processDelete();
        if (Tools::isSubmit('delete'.$this->table)) {
            Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.'etsy_attribute_mapping WHERE id_profile_category='.(int)Tools::getValue('id_profile_category'));
        }
        $kbEtsy = new KbEtsy();
        $kbEtsy->sendCurlRequestToFront(0, 'syncProfileProducts');
        Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyProfileCategoryMapping') . '&id_etsy_profiles='.Tools::getValue('id_etsy_profiles').'&etsyConf=56');
    }
    
    //Set JS and CSS
    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $this->addJS($this->getModuleDirUrl() . 'kbetsy/views/js/script.js');
        $this->addJS($this->getModuleDirUrl() . 'kbetsy/views/js/velovalidation.js');
        $this->addCSS($this->getModuleDirUrl() . 'kbetsy/views/css/style.css');
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
