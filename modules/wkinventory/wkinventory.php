<?php
/**
* NOTICE OF LICENSE
*
* This file is part of the 'WK Inventory' module feature.
* Developped by Khoufi Wissem (2017).
* You are not allowed to use it on several site
* You are not allowed to sell or redistribute this module
* This header must not be removed
*
*  @author    KHOUFI Wissem - K.W
*  @copyright 2021 Khoufi Wissem
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  @version   2.1.63; PSCompatiblity 1.5.6.x => 1.7.x
*/

class WKInventory extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        require_once(dirname(__FILE__).'/classes/Workshop.php');

        $this->name = 'wkinventory';
        $this->tab = 'shipping_logistics';
        $this->version = '2.1.63';
        $this->author = 'Khoufi Wissem';
        $this->need_instance = 0;
        $this->module_key = '51e798dfd6db6b8884411a003b9a798a';
        $this->is_before_16 = version_compare(_PS_VERSION_, '1.5.6.3', '<=');
        $this->is_greater_17 = version_compare(_PS_VERSION_, '1.7', '>=');
        $this->bootstrap = true;

        parent::__construct();

        /* T A B S */
        $this->my_tabs = array(
            0 => array(
                'name' => array(
                    'en' => 'Wk Inventory Management',
                    'fr' => 'Wk Gestion Inventaire'
                ),
                'className' => (!$this->is_greater_17 ? 'AdminStocktakedash' : 'AdminParentStocktakedash'),
                'id_parent' => 0,
                'is_hidden' => 0,
                'is_tool' => 0,
                'ico' => 0
            ),
            1 => array(
                'name' => array(
                    'en' => 'Inventories Management',
                    'fr' => 'Gestion Inventaires'
                ),
                'className' => 'AdminStocktake',
                'id_parent' => -1,
                'is_hidden' => 0,
                'is_tool' => 0,
                'ico' => 'inventory.png'
            ),
            2 => array(
                'name' => array(
                    'en' => 'EAN / UPC Barcode Generator',
                    'fr' => 'Générateur Codes EAN / UPC'
                ),
                'className' => 'AdminBarcodesgen',
                'id_parent' => -1,
                'is_hidden' => 0,
                'is_tool' => 0,
                'ico' => 'barcodes.png'
            ),
            3 => array(
                'name' => array(
                    'en' => 'PDF Report Generation',
                    'fr' => 'Génération Rapport PDF'
                ),
                'className' => 'AdminStocktakegetpdf',
                'id_parent' => -1,
                'is_hidden' => 1,
                'is_tool' => 0,
                'ico' => 0
            ),
            4 => array(
                'name' => array(
                    'en' => 'Logs',
                    'fr' => 'Log (Journal)'
                ),
                'className' => 'AdminStocktakeLogs',
                'id_parent' => -1,
                'is_hidden' => 0,
                'is_tool' => 1,
                'ico' => 'log.png'
            ),
        );
        if ($this->is_greater_17) {
            $tab_dashboard = array(
                'name' => array(
                    'en' => 'Dashboard',
                    'fr' => 'Tableau De Bord'
                ),
                'className' => 'AdminStocktakedash',
                'id_parent' => 0,
                'is_hidden' => 0,
                'is_tool' => 0,
                'ico' => 0
            );
            array_splice($this->my_tabs, 1, 0, array($tab_dashboard));
        }

        $this->displayName = $this->l('Wk Inventory Management');
        $this->description = $this->l('Manage easily and quickly your inventory');
        $this->confirmUninstall = $this->l('Warning: all the data saved in your database will be deleted. Are you sure you want uninstall this module?');
    }

    public function install($install = true)
    {
        if (!parent::install() ||
            !Configuration::updateValue('WKINVENTORY_EMPL_RESTRICTION', 1) ||
            !Configuration::updateValue('WKINVENTORY_DEFAULTQTY_UPDATE', 1) ||
            !Configuration::updateValue('WKINVENTORY_GEN_EAN', 1) ||
            !Configuration::updateValue('WKINVENTORY_PREFIX_CODE', 400) ||
            !Configuration::updateValue('WKINVENTORY_ADDQTY_EXISTANT', 0) ||
            !Configuration::updateValue('WKINVENTORY_GEN_UPC', 1) ||
            !Configuration::updateValue('WKINVENTORY_ADDQTY_AUTO', 0) ||
            !Configuration::updateValue('WKINVENTORY_PDFREPORT_MODE', 'normal') ||
            !Configuration::updateValue('WKINVENTORY_RESETSTOCK_NOTINVENT', 0) ||
            !$this->registerHook('actionProductDelete') ||
            !$this->registerHook('actionAttributeCombinationDelete') || // For PS 1.7
            !$this->registerHook('actionObjectDeleteAfter') || // For PS 1.6
            !$this->registerHook('actionProductUpdate') ||
            !$this->registerHook('actionProductSave') ||
            !$this->registerHook('displayBackOfficeFooter') ||
            !$this->registerHook('backOfficeHeader')) {
            return false;
        }
        if ($install) {
            $this->getOrdersStatusesNotImpactStock(); // Set order statuses that don't impact stock
            $this->installTabs(); // Install tabs
            $this->installTables(); // Install tables
        }
        return true;
    }

    public function installTables()
    {
        $tables = array(
            dirname(__FILE__).'/install/install.sql',
            dirname(__FILE__).'/upgrade/sql/install-logs-table.sql',
        );
        $res = true;
        foreach ($tables as $table) {
            $res &= $this->loadSQLFile($table);
        }
        return $res;
    }

    /*
     * Upgrade & Install Functions
    */
    public function loadSQLFile($sql_file)
    {
        // Get MySQL file content
        $sql_content = Tools::file_get_contents($sql_file);
        $sql_content = str_replace('PREFIX_', _DB_PREFIX_, $sql_content);
        $sql_content = str_replace('_SQLENGINE_', _MYSQL_ENGINE_, $sql_content);
        $sql_requests = preg_split("/;\s*[\r\n]+/", $sql_content);
        // Execute each MySQL command
        $result = true;
        foreach ($sql_requests as $request) {
            if (!empty($request)) {
                $result &= Db::getInstance()->execute(trim($request));
            }
        }
        return $result;
    }

    public function getOrdersStatusesNotImpactStock()
    {
        Configuration::updateValue('WKINVENTORY_ORDER_STATES', '6,7,8');
    }

    public function installTabs()
    {
        $id_parent = null;
        foreach ($this->my_tabs as $k => $tab) {
            $tab_name = $tab['name'];

            $hidden_controllers = array(
                'AdminStocktakegetpdf',
            );

            $obj = new Tab();
            foreach (Language::getLanguages() as $lang) {
                if (!isset($tab_name[$lang['iso_code']])) {
                    $obj->name[$lang['id_lang']] = $tab_name['en'];
                } else {
                    $obj->name[$lang['id_lang']] = $tab_name[$lang['iso_code']];
                }
            }
            $obj->class_name = $tab['className'];

            // Process Parent ID
            if ($k == 0) {// First tab
                $parent_tab = Tab::getIdFromClassName(($this->is_greater_17 ? 'IMPROVE' : 'AdminParentModules'));
                if (property_exists($obj, 'icon')) {
                    $obj->icon = 'settings';
                }
            } else {
                $parent_tab = is_null($id_parent) || ($tab['is_hidden'] && !$this->is_greater_17) ? $tab['id_parent'] : $id_parent;
            }
            $obj->id_parent = (int)$parent_tab;
            // End processing parent ID

            $obj->active = (in_array($obj->class_name, $hidden_controllers) ? 0 : 1);
            $obj->module = $this->name;

            if ($obj->add()) {
                // Get the ID of the first tab that will be the parent ID of the next tabs
                if ($this->is_greater_17 && $k == 0) {
                    $id_parent = (int)$obj->id;
                }
            }
        }
        return true;
    }

    public function reset()
    {
        if (!$this->uninstall(false)) {
            return false;
        }
        if (!$this->install(false)) {
            return false;
        }
        return true;
    }

    public function uninstall($uninstall = true)
    {
        if (!parent::uninstall()) {
            return false;
        }
        // Delete all config. values
        Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'configuration` WHERE `name` LIKE "WKINVENTORY_%" ');

        if ($uninstall) {
            $this->dropTables(); // Drop Module Tables
            $this->uninstallTabs(); // Uninstall Tabs
        }
        return true;
    }

    private function dropTables()
    {
        Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'wkinventory`');
        Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'wkinventory_product`');
        Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'wkinventory_log`');
    }

    public function uninstallTabs()
    {
        $tabs = Tab::getCollectionFromModule($this->name);
        foreach ($tabs as $tab) {
            $tab->delete();
        }
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submitWkinventory')) {
            if (!preg_match('^-?[0-9]\d*(\d+)?$^', Tools::getValue('WKINVENTORY_DEFAULTQTY_UPDATE'))) {
                $output .= $this->displayError($this->l('Default quantity to update products: Invalid number!'));
            } else {
                Configuration::updateValue(
                    'WKINVENTORY_DEFAULTQTY_UPDATE',
                    (int)Tools::getValue('WKINVENTORY_DEFAULTQTY_UPDATE')
                );
                Configuration::updateValue(
                    'WKINVENTORY_ORDER_STATES',
                    (Tools::getValue('WKINVENTORY_ORDER_STATES') ? implode(Tools::getValue('WKINVENTORY_ORDER_STATES'), ',') : '')
                );
                if ($this->context->employee->isSuperAdmin()) {
                    Configuration::updateValue(
                        'WKINVENTORY_EMPL_RESTRICTION',
                        (int)Tools::getValue('WKINVENTORY_EMPL_RESTRICTION')
                    );
                }
                Configuration::updateValue('WKINVENTORY_GEN_EAN', (int)Tools::getValue('WKINVENTORY_GEN_EAN'));
                Configuration::updateValue('WKINVENTORY_GEN_UPC', (int)Tools::getValue('WKINVENTORY_GEN_UPC'));
                Configuration::updateValue('WKINVENTORY_PREFIX_CODE', Tools::getValue('WKINVENTORY_PREFIX_CODE'));
                Configuration::updateValue(
                    'WKINVENTORY_ADDQTY_EXISTANT',
                    (int)Tools::getValue('WKINVENTORY_ADDQTY_EXISTANT')
                );
                Configuration::updateValue(
                    'WKINVENTORY_ADDQTY_AUTO',
                    (int)Tools::getValue('WKINVENTORY_ADDQTY_AUTO')
                );
                Configuration::updateValue(
                    'WKINVENTORY_RESETSTOCK_NOTINVENT',
                    (int)Tools::getValue('WKINVENTORY_RESETSTOCK_NOTINVENT')
                );
                Configuration::updateValue('WKINVENTORY_PDFREPORT_MODE', Tools::getValue('WKINVENTORY_PDFREPORT_MODE'));
                if (empty($output)) {
                    Tools::redirectAdmin(
                        AdminController::$currentIndex.'&configure='.$this->name.'&token='
                        .Tools::getAdminTokenLite('AdminModules').'&conf=6'
                    );
                }
            }
        }

        return $output.$this->renderForm();
    }

    protected function renderForm()
    {
        $radio_type = $this->is_before_16 ? 'radio' : 'switch';
        $radioOptions = array(
            array('id' => 'active_on', 'value' => 1, 'label' => $this->l('Enabled')),
            array('id' => 'active_off', 'value' => 0, 'label' => $this->l('Disabled'))
        );

        $this->fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('What does this module do?'),
            ),
            'input' => array(
                array(
                    'type' => 'free',
                    'label' => '',
                    'name' => 'help_tab'
                )
            )
        );
        $this->fields_form[1]['form'] = array(
            'legend' => array(
                'title' => $this->l('General Settings'),
                'icon' => 'icon-cogs',
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->l('Order Statuses'),
                    'name' => 'WKINVENTORY_ORDER_STATES[]',
                    'desc' => $this->l('Select order statuses that will not impact your inventory').'.',
                    'multiple' => true,
                    'required' => true,
                    'class' => 'input fixed-width-xxl',
                    'options' => array(
                        'query' => OrderState::getOrderStates($this->context->language->id),
                        'id' => 'id_order_state',
                        'name' => 'name',
                    )
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('PDF Report generation'),
                    'name' => 'WKINVENTORY_PDFREPORT_MODE',
                    'class' => 'input',
                    'options' => array(
                        'query' => array(
                            array('id' => 'normal', 'name' => $this->l('For small inventories')),
                            array('id' => 'ajax', 'name' => $this->l('For big inventories (using ajax technology)')),
                        ),
                        'id' => 'id',
                        'name' => 'name'
                    ),
                    'desc' => $this->l('Use the second option for big inventories which contain a very large number of products').'.',
                ),
                array(
                    'type' => 'free',
                    'label' => $this->l('Quantity of Correction (Adjustment Quantity) Settings'),
                    'name' => 'option_settings'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Default quantity'),
                    'desc' => $this->l('Default quantity to add/decrease to products').'.',
                    'name' => 'WKINVENTORY_DEFAULTQTY_UPDATE',
                    'required' => true,
                    'class' => 'input fixed-width-xs',
                    'hint' => $this->l('Negative values are allowed')
                ),
                array(
                    'type' => $radio_type,
                    'label' => $this->l('Add to the existant adjustement quantity'),
                    'name' => 'WKINVENTORY_ADDQTY_EXISTANT',
                    'class' => 't',
                    'is_bool' => true,
                    'desc' => $this->l('If enabled, during inventory process, add (cumulate) the quantity of correction to the existant adjustment quantity, otherwise, it will be replaced').'.',
                    'values' => $radioOptions
                ),
                array(
                    'type' => $radio_type,
                    'label' => $this->l('Add automatically'),
                    'name' => 'WKINVENTORY_ADDQTY_AUTO',
                    'class' => 't',
                    'is_bool' => true,
                    'desc' => $this->l('This option if enabled let you add/update automatically the product quantity to the inventory without having to click every time on "Correct Product Quantity" button').'.<br />'
                    .$this->l('Note: automatic addition/update is effective only if the search finds of course a single product.'),
                    'values' => $radioOptions
                ),
                array(
                    'type' => $radio_type,
                    'label' => $this->l('Reset stock of unchanged products'),
                    'name' => 'WKINVENTORY_RESETSTOCK_NOTINVENT',
                    'class' => 't',
                    'is_bool' => true,
                    'desc' => $this->l('This option is only available for products of an inventory which they have not been inventoried (Adjustment quantities have not been changed, still at 0).').'<br />'
                    .$this->l('If that\'s so, after the finalization (closure) of an inventory, the stock (shop quantities) of those products will be reset (set to 0).'),
                    'values' => $radioOptions
                ),
            )
        );
        if ($this->context->employee->isSuperAdmin()) {
            array_unshift($this->fields_form[1]['form']['input'], array(
                'type' => $radio_type,
                'label' => $this->l('Each employee manage his inventories'),
                'class' => 't',
                'name' => 'WKINVENTORY_EMPL_RESTRICTION',
                'desc' => $this->l('The super administrator view and edit all').'.',
                'values' => $radioOptions
            ));
        }

        $this->fields_form[2]['form'] = array(
            'legend' => array(
                'title' => $this->l('Barcodes Generation Settings'),
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Prefix for EAN/UPC Code'),
                    'name' => 'WKINVENTORY_PREFIX_CODE',
                    'placeholder' => $this->l('E.g:').' 4009658795',
                    'desc' => 'prefix_code',
                    'required' => true
                ),
                array(
                    'type' => $radio_type,
                    'label' => $this->l('Generate EAN Codes'),
                    'name' => 'WKINVENTORY_GEN_EAN',
                    'class' => 't',
                    'is_bool' => true,
                    'desc' => $this->l('If enabled, EAN code will be generated automatically after each product creation/update')
                    .'.<br />'.$this->l('This feature is also valid when mass-generating codes').'.',
                    'values' => $radioOptions
                ),
                array(
                    'type' => $radio_type,
                    'label' => $this->l('Generate UPC Codes'),
                    'name' => 'WKINVENTORY_GEN_UPC',
                    'class' => 't',
                    'is_bool' => true,
                    'desc' => $this->l('If enabled, UPC code will be generated automatically after each product creation/update')
                    .'.<br />'.$this->l('This feature is also valid when mass-generating codes').'.',
                    'values' => $radioOptions
                )
            ),
            'submit' => array(
                'class' => $this->is_before_16 ? 'button' : 'btn btn-default pull-right',
                'title' => $this->l('Save')
            )
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitWkinventory';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
        .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
            'is_before_16' => $this->is_before_16,
            'this_path' => $this->_path,
        );
        return $helper->generateForm($this->fields_form);
    }

    /**
     * Set values
     */
    protected function getConfigFormValues()
    {
        return array(
            'WKINVENTORY_ORDER_STATES[]' => Tools::getValue('WKINVENTORY_ORDER_STATES', explode(',', Configuration::get('WKINVENTORY_ORDER_STATES'))),
            'WKINVENTORY_EMPL_RESTRICTION' => Tools::getValue('WKINVENTORY_EMPL_RESTRICTION', Configuration::get('WKINVENTORY_EMPL_RESTRICTION')),
            'WKINVENTORY_DEFAULTQTY_UPDATE' => Tools::getValue('WKINVENTORY_DEFAULTQTY_UPDATE', Configuration::get('WKINVENTORY_DEFAULTQTY_UPDATE')),
            'WKINVENTORY_GEN_EAN' => Tools::getValue('WKINVENTORY_GEN_EAN', Configuration::get('WKINVENTORY_GEN_EAN')),
            'WKINVENTORY_GEN_UPC' => Tools::getValue('WKINVENTORY_GEN_UPC', Configuration::get('WKINVENTORY_GEN_UPC')),
            'WKINVENTORY_PREFIX_CODE' => Tools::getValue('WKINVENTORY_PREFIX_CODE', Configuration::get('WKINVENTORY_PREFIX_CODE')),
            'WKINVENTORY_ADDQTY_EXISTANT' => Tools::getValue('WKINVENTORY_ADDQTY_EXISTANT', Configuration::get('WKINVENTORY_ADDQTY_EXISTANT')),
            'WKINVENTORY_ADDQTY_AUTO' => Tools::getValue('WKINVENTORY_ADDQTY_AUTO', Configuration::get('WKINVENTORY_ADDQTY_AUTO')),
            'WKINVENTORY_RESETSTOCK_NOTINVENT' => Tools::getValue('WKINVENTORY_RESETSTOCK_NOTINVENT', Configuration::get('WKINVENTORY_RESETSTOCK_NOTINVENT')),
            'WKINVENTORY_PDFREPORT_MODE' => Tools::getValue('WKINVENTORY_PDFREPORT_MODE', Configuration::get('WKINVENTORY_PDFREPORT_MODE')),
            'help_tab' => '',
            'option_settings' => '',
        );
    }

    public function hookActionProductSave($params)
    {
//        return true;
        return $this->modifyProduct($params);
    }

    public function hookActionProductUpdate($params)
    {
//        return true;
        return $this->modifyProduct($params);
    }

    public function modifyProduct($params)
    {
        $productObj = new Product($params['id_product'], false);
        if (Validate::isLoadedObject($productObj)) {
            $this->saveBarcodes($productObj, false);
        }
    }

    /*
     * This hook is called when a product is deleted
    */
    public function hookActionProductDelete($params)
    {
        if (Module::isInstalled($this->name) && $this->active) {
            $id_product = $this->getProductSID($params);

            if (!empty($id_product)) {
                $this->deleteInventoryProducts('product', $id_product);
            }
        }
        return true;
    }

    public function hookActionObjectDeleteAfter($params)
    {
        if (Module::isInstalled($this->name) && $this->active && !$this->is_greater_17) {
            $object = $params['object'];

            if ($object instanceof Combination) {
                $id_product_attribute = (int)$params['object']->id;
                if (!empty($id_product_attribute)) {
                    $this->deleteInventoryProducts('combination', $id_product_attribute);
                }
            }
        }
    }

    /*
     * This hook is called after a combination is deleted
    */
    public function hookActionAttributeCombinationDelete($params)
    {
        if (Module::isInstalled($this->name) && $this->active && $this->is_greater_17) {
            $id_product_attribute = (int)$params['id_product_attribute'];

            if (!empty($id_product_attribute)) {
                $this->deleteInventoryProducts('combination', $id_product_attribute);
            }
        }
        return true;
    }

    public function deleteInventoryProducts($mode, $sid)
    {
        require_once(dirname(__FILE__).'/classes/StockTake.php');
        require_once(dirname(__FILE__).'/classes/StockTakeProduct.php');
        $inventories = StockTake::getInventories();
        foreach ($inventories as $inventory) {
            Db::getInstance()->execute(
                'DELETE FROM `'._DB_PREFIX_.StockTakeProduct::$definition['table'].'`
                 WHERE `id_inventory` = '.(int)$inventory[StockTake::$definition['primary']].'
                 AND '.($mode == 'combination' ? 'id_product_attribute' : 'id_product').' = '.(int)$sid
            );
        }
    }

    public function getProductSID($params)
    {
        if (isset($params['product']->id)) {
            return $params['product']->id;
        } elseif (isset($params['id_product'])) {
            return $params['id_product'];
        } elseif (isset($params['product'])) {
            return $params['product']['id_product'];
        } else {
            return false;
        }
    }

    /**
     * @param string $prefix   barcode prefix
     * @param array  $product  product to update
     * @param bool   $force    force regenerating existing barcodes
     */
    public function saveBarcodes($product, $force = false)
    {
        $count = 0;
        $ean13_barcode = $upc_barcode = array();
        $completed = true;

        $combinations_count = (int)$product->hasAttributes();
        $count += $combinations_count > 0 ? $combinations_count : 1;

        if (!$count) {
            return;
        }

        // Get EAN13 barcode
        if (Configuration::get('WKINVENTORY_GEN_EAN')) {
            $ean13_barcode = $this->generateBarcodes('ean13', $count, $force);
        }
        // Get UPC barcode
        if (Configuration::get('WKINVENTORY_GEN_UPC')) {
            $upc_barcode = $this->generateBarcodes('upc', $count, $force);
        }
        if ((Configuration::get('WKINVENTORY_GEN_EAN') && !count($ean13_barcode)) ||
            (Configuration::get('WKINVENTORY_GEN_UPC') && !count($upc_barcode))) {
            return;
        }

        try {
            if ($combinations_count) {// Product with combinations
                $combinations_data = $product->getAttributeCombinations($this->context->language->id);
                $combinations = ObjectModel::hydrateCollection('Combination', $combinations_data);

                foreach ($combinations as $combination) {
                    if ($force || empty($combination->ean13)) {
                        $combination->ean13 = array_shift($ean13_barcode);
                    }
                    if ($force || empty($combination->upc)) {
                        $combination->upc = array_shift($upc_barcode);
                    }
                    $combination->save();
                }
            } else {// Simple product
                $values = array();
                if ($force || empty($product->ean13)) {
                    $values['ean13'] = array_shift($ean13_barcode);
                }
                if ($force || empty($product->upc)) {
                    $values['upc'] = array_shift($upc_barcode);
                }
                /* use Db::getInstance()->update function instead of $product->save or $product->update
                 * to avoid recursive call to hooks
                */
                if (!empty($values)) {
                    Db::getInstance()->update('product', $values, 'id_product = '.(int)$product->id);
                }
            }
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
        }
        return $completed;
    }

    /**
     * Generate barcodes and display error if generation is impossible.
     *
     * @param string $prefix barcode prefix
     * @param string $type   barcode type (EAN13 / UPC )
     * @param int    $count  barcodes number requested
     * @param bool   $force  regenerate existing barcodes
     *
     * @return array
     */
    private function generateBarcodes($type, $count, $force)
    {
        $prefix = configuration::get('WKINVENTORY_PREFIX_CODE');

        if ($type === 'ean13') {
            $barcode_length = 12;
            $weighting = 1;
        } else {
            $barcode_length = 11;
            $weighting = 3;
        }

        // Generable digit = 10^n, where n is barcode length - prefix
        $generable_digits = pow(10, $barcode_length - Tools::strlen($prefix));
        $max_generable = $generable_digits;
        $exclusion_list = WorkshopInv::getExistingBarcodes($type);

        if (!$force) {
            $max_generable -= count($exclusion_list);
        }

        if ($max_generable < (int)$count) {// Generation is impossible
            $error_msg = $this->l('%d %s barcodes to generate but only %d possibles with this prefix.');
            $this->errors[] = sprintf($error_msg, $count, Tools::strtoupper($type), $max_generable);
            $limit = $max_generable;
        } else {
            $limit = (int)$count;
        }

        $i = 0;
        $barcodes = array();

        for ($i; $i < $generable_digits; ++$i) {
            if (count($barcodes) >= $limit) {
                break;
            }
            $suffix = pSQL($i);
            $remaining = $barcode_length - (Tools::strlen($prefix) + Tools::strlen($suffix));
            $barcode = $prefix.str_repeat('0', $remaining).$suffix;
            $barcode .= WorkshopInv::getControlDigit($barcode, $weighting);

            if (in_array($barcode, $exclusion_list)) {
                continue;
            }
            $barcodes[] = $barcode;
        }
        return $barcodes;
    }

    /**
     * Add scripts / modal / etc to footer (Only PS 1.5.x)
     */
    public function hookDisplayBackOfficeFooter()
    {
        if ($this->is_before_16 && Tools::getValue('controller') == 'AdminStocktake') {
            $this->context->smarty->assign(array(
                'modal_id' => 'importProgress',
                'modal_class' => 'modal-md',
                'modal_task' => 'update',
                'modal_title' => $this->l('Updating your shop...'),
            ));
            return $this->display(__FILE__, 'views/templates/admin/modal.tpl');
        }
    }

    /**
     * Load the CSS & JavaScript files in the BO.
     */
    public function hookBackOfficeHeader()
    {
        $controllers = array('AdminStocktake', 'AdminStocktakedash', 'AdminBarcodesgen', 'AdminStocktakeLogs');
        if (Tools::getValue('configure') === $this->name ||
            in_array(Tools::getValue('controller'), $controllers)) {
            $this->context->controller->addJquery();
            $this->context->controller->addCSS($this->_path.'views/css/wkinventory.css');
            if ($this->is_before_16) {
                $this->context->controller->addCSS($this->_path.'views/css/wkinventory_15.css');
            }
            $this->context->controller->addJqueryPlugin('chosen');
            $this->context->controller->addJs($this->_path.'views/js/wkinventory.min.js');
        }
    }
}
