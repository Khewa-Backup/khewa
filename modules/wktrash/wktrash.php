<?php
/**
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License version 3.0
* that is bundled with this package in the file LICENSE.txt
* It is also available through the world-wide-web at this URL:
* https://opensource.org/licenses/AFL-3.0
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to a newer
* versions in the future. If you wish to customize this module for your
* needs please refer to CustomizationPolicy.txt file inside our module for more information.
*
* @author Webkul IN
* @copyright Since 2010 Webkul
* @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*/
if (!defined('_PS_VERSION_')) {
    exit;
}
require_once 'classes/WkTrashRequiredClasses.php';
class WkTrash extends Module
{
    // Khewa PS 9.2: declared here, PHP 8.2+ warns about properties added on the fly
    public $secure_key;

    // Khewa PS 9.2: remembers what was already copied to the trash in this request, so a supplier is not saved twice
    private static $alreadySaved = [];

    public function __construct()
    {
        $this->name = 'wktrash';      /* Name of the Module */
        $this->tab = 'administration';   /* Tab to Display [Categories in Backoffice Module Page] */
        $this->version = '4.0.2';               /* Module version display in module list */
        $this->author = 'Webkul';
        $this->module_key = 'b3425416650fe2b291c038b5ced6b469';
        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
        parent::__construct();
        // Khewa PS 9.2: Tools::encrypt() was removed (admin pages crashed). Tools::hash() is the same idea.
        $this->secure_key = Tools::hash($this->name);
        $this->displayName = $this->l('Restore Deleted Data');
        $this->description = $this->l('This module provides a feature to restore products, categories, brands, suppliers, customers, attributes, attribute values and features.');
        $this->bootstrap = true;
        $this->confirmUninstall = $this->l('Are you sure?');
    }

    /**
     * Installation Process,
     *
     * Overriding the Module::install() function
     *
     * @return bool
     */
    public function install()
    {
        $objTrashDb = new WkTrashDb();
        if (!parent::install()
            || !$this->callInstallTab()
            || !$objTrashDb->createTables()
            || !$this->registerModuleHook()
            || !Configuration::updateValue('WK_RESTORE_CHILD_CATEGORY', true)
            || !Configuration::updateValue('WK_RESTORE_IN_ROOT_CATEGORY', true)) {
            return false;
        }

        return true;
    }

    /**
     * To register required hooks
     *
     * @return void
     */
    public function registerModuleHook()
    {
        return $this->registerHook([
            'actionObjectProductDeleteBefore',
            'actionObjectSupplierDeleteBefore',
            'actionObjectCategoryDeleteBefore',
            'actionObjectManufacturerDeleteBefore',
            'actionObjectCustomerUpdateBefore',
            'actionDeleteGDPRCustomer',
            'actionExportGDPRData',
            // Khewa PS 9.2: these replace the module's old overrides (Customer, Attribute, AttributeGroup,
            // Feature, AdminSuppliersController). Overrides of those classes can't be used on 9.2.
            'actionObjectCustomerDeleteBefore',
            'actionObjectProductAttributeDeleteBefore',
            'actionObjectAttributeGroupDeleteBefore',
            'actionObjectFeatureDeleteBefore',
            'actionDispatcherBefore',
        ]);
    }

    public function callInstallTab()
    {
        $this->installTab('AdminWkTrash', 'Trash', 'Sell');
        $this->installTab('AdminWkDeletedProducts', 'Deleted Products', 'AdminWkTrash');
        $this->installTab('AdminWkDeletedCategories', 'Deleted Categories', 'AdminWkTrash');
        $this->installTab('AdminWkDeletedManufacturers', 'Deleted Brands', 'AdminWkTrash');
        $this->installTab('AdminWkDeletedSuppliers', 'Deleted Suppliers', 'AdminWkTrash');
        $this->installTab('AdminWkDeletedCustomers', 'Deleted Customers', 'AdminWkTrash');
        $this->installTab('AdminWkDeletedAttributeAndGroups', 'Deleted Attributes', 'AdminWkTrash');
        $this->installTab(
            'AdminWkDeletedAttributeGroups',
            'Deleted Attribute Groups',
            'AdminWkDeletedAttributeAndGroups'
        );
        $this->installTab('AdminWkDeletedAttributes', 'Deleted Attributes', 'AdminWkDeletedAttributeAndGroups');
        $this->installTab('AdminWkDeletedFeatures', 'Deleted Features', 'AdminWkTrash');
        $this->installTab('AdminWkEntityRestoreHistory', 'Entity Restore History', 'AdminWkTrash');

        return true;
    }

    /**
     * Load the configuration form
     *
     * By just defining this function, you can see the 'Configure' Button in the Module list
     */
    public function getContent()
    {
        if (Tools::isSubmit('submit' . $this->name)) {
            $this->postProcess();
        }
        Media::addJsDef([
            'wkModuleAddonKey' => $this->module_key,
            'wkModuleAddonsId' => 47657,
            'wkModuleTechName' => $this->name,
            'wkModuleDoc' => file_exists(_PS_MODULE_DIR_ . $this->name . '/doc_en.pdf'),
        ]);
        $this->context->controller->addJs('https://prestashop.webkul.com/crossselling/wkcrossselling.min.js?t=' . time());

        return $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->submit_action = 'submit' . $this->name;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$this->getConfigForm()]);
    }

    /**
     * Create the structure of your form
     */
    protected function getConfigForm()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Restore entities with new ID'),
                        'name' => 'WK_RESTORE_ENTITY_NEW_ID',
                        'is_bool' => true,
                        'hint' => $this->l('If selected yes, then entities i.e. products, categories, customers etc ') .
                        $this->l('will be restored with new IDs'),
                        'values' => [
                            [
                                'id' => 'category_apply',
                                'value' => true,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'category_not_apply',
                                'value' => false,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Restore category with child category'),
                        'name' => 'WK_RESTORE_CHILD_CATEGORY',
                        'is_bool' => true,
                        'hint' => $this->l('If selected yes, then parent category will be restored along with ') .
                        $this->l(' it\'s child categories.'),
                        'values' => [
                            [
                                'id' => 'with_child',
                                'value' => true,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'without_child',
                                'value' => false,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Restore category in root category'),
                        'name' => 'WK_RESTORE_IN_ROOT_CATEGORY',
                        'is_bool' => true,
                        'hint' => $this->l('If selected yes, then child categories get restored in root category in ') .
                        $this->l(' case it\'s parent category does not exist.'),
                        'values' => [
                            [
                                'id' => 'with_child',
                                'value' => true,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'without_child',
                                'value' => false,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Apply category on product while restoring'),
                        'name' => 'WK_CATEGORY_APPLY_ON_PRODUCT',
                        'is_bool' => true,
                        'hint' => $this->l('If enabled, then category gets applied on ') .
                        $this->l('it\'s associated product(s) after restoration.'),
                        'values' => [
                            [
                                'id' => 'category_apply',
                                'value' => true,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'category_not_apply',
                                'value' => false,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Apply brand on product while restoring'),
                        'name' => 'WK_BRAND_APPLY_ON_PRODUCT',
                        'is_bool' => true,
                        'hint' => $this->l('If enabled, then brand gets applied on ') .
                        $this->l('it\'s associated product(s) after restoration.'),
                        'values' => [
                            [
                                'id' => 'brand_apply',
                                'value' => true,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'brand_not_apply',
                                'value' => false,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Apply supplier on product while restoring'),
                        'name' => 'WK_SUPPLIER_APPLY_ON_PRODUCT',
                        'is_bool' => true,
                        'hint' => $this->l('If enabled, then supplier gets applied on ') .
                        $this->l('it\'s associated product(s) after restoration.'),
                        'values' => [
                            [
                                'id' => 'supp_apply',
                                'value' => true,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'supp_not_apply',
                                'value' => false,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Apply feature on product while restoring'),
                        'name' => 'WK_FEATURE_APPLY_ON_PRODUCT',
                        'is_bool' => true,
                        'hint' => $this->l('If enabled, then feature gets applied on ') .
                        $this->l('it\'s associated product(s) after restoration.'),
                        'values' => [
                            [
                                'id' => 'feature_apply',
                                'value' => true,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'feature_not_apply',
                                'value' => false,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Apply attribute on product while restoring'),
                        'name' => 'WK_ATTRIBUTE_APPLY_ON_PRODUCT',
                        'is_bool' => true,
                        'hint' => $this->l('If enabled, then attribute gets applied on ') .
                        $this->l('it\'s associated product(s) after restoration.'),
                        'values' => [
                            [
                                'id' => 'attr_apply',
                                'value' => true,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'attr_not_apply',
                                'value' => false,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return [
            'WK_RESTORE_ENTITY_NEW_ID' => (bool) Configuration::get('WK_RESTORE_ENTITY_NEW_ID'),
            'WK_CATEGORY_APPLY_ON_PRODUCT' => (bool) Configuration::get('WK_CATEGORY_APPLY_ON_PRODUCT'),
            'WK_BRAND_APPLY_ON_PRODUCT' => (bool) Configuration::get('WK_BRAND_APPLY_ON_PRODUCT'),
            'WK_SUPPLIER_APPLY_ON_PRODUCT' => (bool) Configuration::get('WK_SUPPLIER_APPLY_ON_PRODUCT'),
            'WK_FEATURE_APPLY_ON_PRODUCT' => (bool) Configuration::get('WK_FEATURE_APPLY_ON_PRODUCT'),
            'WK_ATTRIBUTE_APPLY_ON_PRODUCT' => (bool) Configuration::get('WK_ATTRIBUTE_APPLY_ON_PRODUCT'),
            'WK_RESTORE_CHILD_CATEGORY' => (bool) Configuration::get('WK_RESTORE_CHILD_CATEGORY'),
            'WK_RESTORE_IN_ROOT_CATEGORY' => (bool) Configuration::get('WK_RESTORE_IN_ROOT_CATEGORY'),
        ];
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $formValues = $this->getConfigFormValues();

        foreach (array_keys($formValues) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }

        if (empty($this->context->controller->errors)) {
            Tools::redirectAdmin(
                $this->context->link->getAdminLink('AdminModules', false)
                . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name .
                '&token=' . Tools::getAdminTokenLite('AdminModules') . '&activeTab=designSetting&conf=6'
            );
        }
    }

    /**
     * Calling Hook actionDeleteGDPRCustomer,
     *
     * Delete Customer data to compliant with GDPR
     *
     * @param [object] $customer
     */
    public function hookActionDeleteGDPRCustomer($customer)
    {
        // We add a check in override/classes/customer class for this
        if (!empty($customer['email']) && Validate::isEmail($customer['email'])) {
            if (WkDeletedCustomer::deleteGDPRCustomer($customer['id'])) {
                return json_encode(true);
            }

            return json_encode($this->l('Trash(customer) : Unable to delete customer using id.'));
        }
    }

    /**
     * Calling Hook actionExportGDPRData,
     *
     * To export customer data
     *
     * @param [object] $customer
     */
    public function hookActionExportGDPRData($customer)
    {
        if (!Tools::isEmpty($customer['email']) && Validate::isEmail($customer['email'])) {
            if (WkDeletedCustomer::getGDPRCustomer($customer['id'])) {
                return json_encode(true);
            }

            return json_encode($this->l('Trash(customer) : Unable to export customer using id.'));
        }
    }

    public function hookActionObjectProductDeleteBefore($params)
    {
        // Khewa PS 9.2: skip if already saved earlier in this request (see hookActionDispatcherBefore)
        if (!$this->markSaved('product', (int) $params['object']->id)) {
            return;
        }
        $objDeletedProduct = new WkDeletedProduct();
        $objDeletedProduct->getProductDetailBeforeDelete($params['object']->id);
    }

    public function hookActionObjectSupplierDeleteBefore($params)
    {
        // Khewa PS 9.2: skip if already saved earlier in this request (see hookActionDispatcherBefore)
        if (!$this->markSaved('supplier', (int) $params['object']->id)) {
            return;
        }
        $objDeletedSupplier = new WkDeletedSupplier();
        $objDeletedSupplier->getSupplierDetailBeforeDelete($params['object']->id);
    }

    /**
     * Khewa PS 9.2: true the first time a type + id is seen in this request, false after that.
     */
    private function markSaved($type, $id)
    {
        if (!$id || isset(self::$alreadySaved[$type][$id])) {
            return false;
        }
        self::$alreadySaved[$type][$id] = true;

        return true;
    }

    /**
     * Khewa PS 9.2: on 9.2 the supplier page is a Symfony page. Its delete removes the supplier's product links
     * and address before Supplier::delete() runs, so the "delete before" hook is too late to keep them.
     * This hook runs before the page action (it replaces the old AdminSuppliersController override).
     * Same for products: the new product page removes the images and combinations before Product::delete(),
     * so they were lost from the trash copy. The product is now copied before the page action starts.
     * Same for customers, attribute groups and features: their delete() removes linked data first.
     */
    public function hookActionDispatcherBefore($params)
    {
        if (!defined('_PS_ADMIN_DIR_')) {
            return;
        }
        try {
            $container = PrestaShop\PrestaShop\Adapter\SymfonyContainer::getInstance();
            $request = $container ? $container->get('request_stack')->getCurrentRequest() : null;
        } catch (Exception $e) {
            return;
        }
        if (!$request) {
            return;
        }
        $route = (string) $request->attributes->get('_route');

        // route => [type, id in the url, list of ids posted by a bulk action]
        $routes = [
            'admin_products_delete_from_all_shops' => ['product', 'productId', null],
            'admin_products_delete_from_shop' => ['product', 'productId', null],
            'admin_products_delete_from_shop_group' => ['product', 'productId', null],
            'admin_products_bulk_delete_from_all_shops' => ['product', null, 'product_bulk'],
            'admin_products_bulk_delete_from_shop' => ['product', null, 'product_bulk'],
            'admin_products_bulk_delete_from_shop_group' => ['product', null, 'product_bulk'],
            'admin_suppliers_delete' => ['supplier', 'supplierId', null],
            'admin_suppliers_bulk_delete' => ['supplier', null, 'supplier_bulk'],
            'admin_attribute_groups_delete' => ['attribute_group', 'attributeGroupId', null],
            'admin_attribute_groups_bulk_delete' => ['attribute_group', null, 'attribute_group_bulk'],
            'admin_features_delete' => ['feature', 'featureId', null],
            'admin_features_bulk_delete' => ['feature', null, 'feature_bulk'],
            'admin_customers_delete' => ['customer', null, null],
            'admin_customers_delete_bulk' => ['customer', null, null],
        ];
        if (!isset($routes[$route])) {
            return;
        }
        list($type, $urlKey, $bulkKey) = $routes[$route];

        $ids = [];
        if ($type === 'customer') {
            // Khewa PS 9.2: Customer::delete() removes the groups, addresses, vouchers and specific prices before
            // its "delete before" hook runs.
            $form = (array) $request->request->all('delete_customers');
            $ids = (array) ($form['customers_to_delete'] ?? []);
        } elseif ($urlKey) {
            $ids[] = $request->attributes->get($urlKey);
        } elseif ($bulkKey) {
            $ids = (array) $request->request->all($bulkKey);
        }

        foreach (array_map('intval', $ids) as $id) {
            if (!$this->markSaved($type, $id)) {
                continue;
            }
            switch ($type) {
                case 'product':
                    (new WkDeletedProduct())->getProductDetailBeforeDelete($id);
                    break;
                case 'supplier':
                    (new WkDeletedSupplier())->getSupplierDetailBeforeDelete($id);
                    break;
                case 'attribute_group':
                    // Khewa PS 9.2: AttributeGroup::delete() removes the attribute values before its hook runs
                    (new WkDeletedAttributeGroup())->getAttributeGroupDetailBeforeDelete($id);
                    break;
                case 'feature':
                    // Khewa PS 9.2: Feature::delete() removes the feature values and product links before its hook runs
                    (new WkDeletedFeature())->getFeatureDetailBeforeDelete($id);
                    break;
                case 'customer':
                    (new WkDeletedCustomer())->getCustomerDetailBeforeDelete($id);
                    break;
            }
        }
    }

    /**
     * Khewa PS 9.2: replaces the old Customer override (full delete, "allow the customer to register again").
     * Same checks as the override: not for the GDPR module's own delete and not for ajax calls.
     */
    public function hookActionObjectCustomerDeleteBefore($params)
    {
        if (Tools::getValue('controller') == 'AdminAjaxPsgdpr'
            || Tools::getValue('ajax')
            || Tools::getValue('action') == 'DeleteCustomer'
        ) {
            return;
        }
        if (!$this->markSaved('customer', (int) $params['object']->id)) {
            return;
        }
        $objDeletedCustomer = new WkDeletedCustomer();
        $objDeletedCustomer->getCustomerDetailBeforeDelete($params['object']->id);
    }

    /**
     * Khewa PS 9.2: replaces the old Attribute override. The class is ProductAttribute on 9.2.
     */
    public function hookActionObjectProductAttributeDeleteBefore($params)
    {
        $objDeletedAttribute = new WkDeletedAttribute();
        $objDeletedAttribute->getAttributeDetailBeforeDelete($params['object']->id);
    }

    /**
     * Khewa PS 9.2: replaces the old AttributeGroup override.
     */
    public function hookActionObjectAttributeGroupDeleteBefore($params)
    {
        if (!$this->markSaved('attribute_group', (int) $params['object']->id)) {
            return;
        }
        $objDeletedAttributeGroup = new WkDeletedAttributeGroup();
        $objDeletedAttributeGroup->getAttributeGroupDetailBeforeDelete($params['object']->id);
    }

    /**
     * Khewa PS 9.2: replaces the old Feature override.
     */
    public function hookActionObjectFeatureDeleteBefore($params)
    {
        if (!$this->markSaved('feature', (int) $params['object']->id)) {
            return;
        }
        $objDeletedFeature = new WkDeletedFeature();
        $objDeletedFeature->getFeatureDetailBeforeDelete($params['object']->id);
    }

    public function hookActionObjectCategoryDeleteBefore($params)
    {
        $objDeletedCategory = new WkDeletedCategory();
        $objDeletedCategory->getCategoryDetailBeforeDelete($params['object']->id);
    }

    public function hookActionObjectManufacturerDeleteBefore($params)
    {
        $objDeletedManufacturer = new WkDeletedManufacturer();
        $objDeletedManufacturer->getManufacturerDetailBeforeDelete($params['object']->id);
    }

    public function hookActionObjectCustomerUpdateBefore($params)
    {
        // Khewa PS 9.2: skip if this customer was already saved in this request
        if ($params['object']->deleted && $this->markSaved('customer', (int) $params['object']->id)) {
            $objDeletedCustomer = new WkDeletedCustomer();
            $objDeletedCustomer->getCustomerDetailBeforeDelete($params['object']->id);
        }
    }

    /**
     * To install Tabs at back end
     *
     * @param [type] $className
     * @param [type] $tabName
     * @param [type] $parentTabName
     */
    public function installTab($className, $tabName, $parentTabName)
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = $className;
        $tab->name = [];
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $tabName;
        }
        if ($tab->class_name == 'AdminWkTrash') {
            $tab->icon = 'delete';
        }
        if ($parentTabName) {
            $tab->id_parent = (int) Tab::getIdFromClassName($parentTabName);
        } else {
            $tab->id_parent = 0;
        }
        $tab->module = $this->name;

        return $tab->add();
    }

    /**
     * To uninstall Tabs at back end
     *
     * @return bool
     */
    public function uninstallTab()
    {
        $moduleTabs = Tab::getCollectionFromModule($this->name);
        if (!empty($moduleTabs)) {
            foreach ($moduleTabs as $moduleTab) {
                $moduleTab->delete();
            }
        }

        return true;
    }

    public function deleteConfigKeys()
    {
        $var = [
            'WK_CATEGORY_APPLY_ON_PRODUCT', 'WK_BRAND_APPLY_ON_PRODUCT', 'WK_SUPPLIER_APPLY_ON_PRODUCT',
            'WK_FEATURE_APPLY_ON_PRODUCT', 'WK_ATTRIBUTE_APPLY_ON_PRODUCT', 'WK_RESTORE_CHILD_CATEGORY',
            'WK_RESTORE_IN_ROOT_CATEGORY', 'WK_RESTORE_ENTITY_NEW_ID',
        ];
        foreach ($var as $key) {
            if (!Configuration::deleteByName($key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Uninstallation Process,
     *
     * Overriding Module::uninstall()
     *
     * @return bool
     */
    public function uninstall()
    {
        $objTrashDb = new WkTrashDb();
        if (!parent::uninstall()
            || !$this->uninstallTab()
            || !$objTrashDb->deleteTables()
            || !$this->deleteConfigKeys()) {
            return false;
        }

        return true;
    }
}
