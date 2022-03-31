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
*  @copyright Khoufi Wissem
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

@ini_set('max_execution_time', 0);

class AdminStocktakeController extends ModuleAdminController
{
    private $canUseAdvancedStockManagementNew;
    private $canUseAdvancedStockManagementOld;

    public function __construct()
    {
        include dirname(__FILE__).'/../../classes/StockTake.php';
        include dirname(__FILE__).'/../../classes/StockTakeProduct.php';
        include dirname(__FILE__).'/../../classes/StockTakeLog.php';
        include dirname(__FILE__).'/../../classes/Workshop.php';

        $this->bootstrap = true;
        $this->context = Context::getContext();

        $this->identifier = 'id_inventory';
        $this->table = 'wkinventory';
        $this->list_id = 'stocktake';
        $this->className = 'StockTake';
        $this->lang = false;
        $this->list_no_link = false;
        $this->ps172 = version_compare(_PS_VERSION_, '1.7.2', '>=');
        $this->canUseAdvancedStockManagementNew = WorkshopInv::canUseAdvancedStockManagementNew();
        $this->canUseAdvancedStockManagementOld = WorkshopInv::canUseAdvancedStockManagementOld();

        if (Tools::getIsset('updatewkinventory')) {
            $this->multishop_context = Shop::CONTEXT_SHOP;
            $this->display = 'edit';
        } else {
            $this->multishop_context = Shop::CONTEXT_ALL;
            $this->_select = 's.name AS `supplier`, s.date_add as created_date';
            $this->_join = 'LEFT JOIN `'._DB_PREFIX_.'supplier` s ON (s.`id_supplier` = a.`id_supplier`)';
            // Employee Restriction
            if (!$this->context->employee->isSuperAdmin()) {
                if (Configuration::get('WKINVENTORY_EMPL_RESTRICTION')) {
                    $this->_where = 'AND a.`id_employee` = '.(int)$this->context->employee->id;
                }
            }

            $suppliers = Supplier::getSuppliers();
            $sp_list = array();
            foreach ($suppliers as $supplier) {
                $sp_list[$supplier['id_supplier']] = $supplier['name'];
            }

            $this->fields_list['id_inventory'] = array(
                'title' => 'ID',
                'width' => 20,
                'type' => 'text',
            );
            $this->fields_list['name'] = array(
                'title' => $this->l('Label'),
                'width' => 'auto',
                'type' => 'text',
                'filter_key' => 'a!name',
            );
            $this->fields_list['category_ids'] = array(
                'title' => $this->l('Categories'),
                'type' => 'text',
                'callback' => 'getCategoriesNames',
                'orderby' => false,
                'search' => false
            );
            $this->fields_list['supplier'] = array(
                'align' => 'text-left',
                'title' => $this->l('Supplier'),
                'type' => 'select',
                'list' => $sp_list,
                'filter_key' => 's!id_supplier',
                'filter_type' => 'int',
                'order_key' => 'supplier'
            );
            $this->fields_list['manufacturer_ids'] = array(
                'title' => !version_compare(_PS_VERSION_, '1.7', '>=') ? $this->l('Manufacturers') : $this->l('Brands'),
                'type' => 'text',
                'callback' => 'getManufacturersNames',
                'orderby' => false,
                'search' => false
            );
            if ($this->canUseAdvancedStockManagementOld || $this->canUseAdvancedStockManagementNew) {
                $this->fields_list['id_warehouse'] = array(
                    'title' => $this->l('Warehouse'),
                    'type' => 'text',
                    'callback' => 'getWarehouseName',
                    'orderby' => false,
                    'search' => false
                );
            }
            $this->fields_list['date_add'] = array(
                'title' => $this->l('Creation date'),
                'type' => 'datetime',
                'width' => 40,
                'filter_key' => 'a!date_add',
                'order_key' => 'a!date_add'
            );
            $this->fields_list['date_upd'] = array(
                'title' => $this->l('Modification date'),
                'width' => 40,
                'type' => 'datetime',
            );
            if ($this->context->employee->isSuperAdmin() || !Configuration::get('WKINVENTORY_EMPL_RESTRICTION')) {
                $this->fields_list['id_employee'] = array(
                    'title' => $this->l('Employee'),
                    'width' => 140,
                    'type' => 'text',
                    'callback' => 'getEmployeeName',
                    'search' => false
                );
            }
            $this->fields_list['is_empty'] = array(
                'title' => $this->l('Progressive'),
                'type' => 'bool',
                'active' => 'is_empty',
            );
            $this->fields_list['done'] = array(
                'title' => $this->l('Finished'),
                'width' => 140,
                'type' => 'bool',
                'active' => 'done',
            );

            $this->actions = array('edit', 'delete');
        }
        parent::__construct();
    }

    public function getCategoriesNames($categories)
    {
        if (!empty($categories)) {
            return $this->returnCategoriesNames($categories);
        } else {
            return '--';
        }
    }

    public function getWarehouseName($id)
    {
        $warehouse = WorkshopInv::getWarehouseClass($id);
        if (Validate::isLoadedObject($warehouse)) {
            return $warehouse->name;
        }
        return '--';
    }

    public function getEmployeeName($id)
    {
        $fullname = WorkshopInv::getShopEmployeeName($id);
        if (empty($fullname)) {
            $fullname = $this->l('Unknown employee');
        }
        return $fullname;
    }

    public function getManufacturersNames($manufacturers)
    {
        if (!empty($manufacturers)) {
            return $this->returnManufacturersNames($manufacturers);
        } else {
            return '--';
        }
    }

    public function returnCategoriesNames($categories, $to_array = false)
    {
        $categories_list = explode(',', $categories);
        $categories_names = array();
        foreach ($categories_list as $id_category) {
            $obj = new Category((int)$id_category, $this->context->language->id);
            if (Validate::isLoadedObject($obj)) {
                $categories_names[] = $obj->name;
            }
        }
        if (count($categories_names)) {
            return (!$to_array ? implode(', ', $categories_names) : $categories_names);
        }
    }

    public function returnManufacturersNames($manufacturers, $to_array = false)
    {
        $manufacturers_list = explode(',', $manufacturers);
        $manufacturers_names = array();
        foreach ($manufacturers_list as $id_manufacturer) {
            $obj_manufacturer = new Manufacturer((int)$id_manufacturer);
            if (Validate::isLoadedObject($obj_manufacturer)) {
                $manufacturers_names[] = $obj_manufacturer->name;
            }
        }
        if (count($manufacturers_names)) {
            return !$to_array ? implode(', ', $manufacturers_names) : $manufacturers_names;
        }
    }

    public function ajaxProcessAddInventoryProduct()
    {
        $id_lang = (int)$this->context->language->id;
        $response = array(
            'success' => false,
            'message' => $this->l('Unable to load this inventory'),
        );

        $inventory = new StockTake(Tools::getValue('id_inventory'));
        $quantity = Tools::getValue('quantity', 1);

        if (!preg_match('^-?[0-9]\d*(\d+)?$^', $quantity)) {
            $response['message'] = $this->l('Invalid quantity!');
        } elseif (Validate::isLoadedObject($inventory)) {
            if (Tools::getIsset('product_id')) {
                $product_id = (int)Tools::getValue('product_id');
                //$query->select('p.`id_product`, pa.`id_product_attribute`, pl.`name`');
                $productObj = new Product($product_id, false, $id_lang);

                if (Validate::isLoadedObject($productObj)) {
                    $product_attribute_id = (Tools::getIsset('product_attribute_id') ? (int)Tools::getValue('product_attribute_id') : 0);
                    $product_warehouse_id = (Tools::getIsset('product_warehouse_id') ? (int)Tools::getValue('product_warehouse_id') : null);

                    $product_name = $productObj->name;
                    if (!empty($product_attribute_id)) {
                        $product_name .= ' ('.WorkshopInv::getAttributesCombinationNames($product_attribute_id).')';
                    }

                    // Check product existence
                    $id_inventory_product = $inventory->getInventoryProductId(
                        $product_id,
                        $product_attribute_id,
                        $product_warehouse_id
                    );
                    /*
                    * If inventory is not empty mode : adding product is not in progressive mode
                    */
                    if (!$inventory->is_empty) {
                        // If no product in inventoy table that matchs
                        if (empty($id_inventory_product)) {
                            $response['message'] = $this->l('The product you are looking for does not belong to this inventory!');
                        // If one result
                        } else {
                            $this->updateInventoryProduct($inventory, $id_inventory_product, $quantity, $response);
                        }
                    } else {
                        /*
                        * If adding product in progressive mode
                        * Check for each product found if it is already on the current inventory or not
                        * If not, create it, else update by adding the rectified quantity
                        */
                        $isNew = false;
                        if (empty($id_inventory_product)) {
                            $row = array('id_product' => $product_id, 'id_product_attribute' => $product_attribute_id);
                            if ($this->canUseAdvancedStockManagementOld || $this->canUseAdvancedStockManagementNew) {
                                $row['id_warehouse'] = (int)$product_warehouse_id;
                            }
                            WorkshopInv::createInventoryProduct(
                                $row,
                                $inventory->id,
                                $inventory->id_shop,
                                $quantity
                            );
                            $isNew = true;
                        }
                        $response['success'] = true;
                        if ($isNew) {// Add product to inventory
                            $response['message'] = $this->l('Product(s) added successfully to inventory. Refreshing page...');
                            $response['refresh_page'] = true;// reload page to refresh
                            $last_id_inventory_product = (int)Db::getInstance()->Insert_ID();
                            $this->context->cookie->last_id_inventory_product = $last_id_inventory_product;
                            $this->context->cookie->write();
                        } else {// Update product in inventory
                            $this->updateInventoryProduct($inventory, $id_inventory_product, $quantity, $response);
                        }
                    }
                } else {
                    $response['message'] = $this->l('Unknown Reference / Product Name / EAN / UPC');
                }
            } else {
                $response['message'] = $this->l('Invalid Reference / Product Name / EAN / UPC');
            }
        }
        WorkshopInv::renderJSON($response);
    }

    public function updateInventoryProduct($inventory, $id_inventory_product, $quantity, &$response)
    {
        $inv_p = new StockTakeProduct($id_inventory_product);
        if (Configuration::get('WKINVENTORY_ADDQTY_EXISTANT')) {
            $inv_p->real_quantity = (int)$inv_p->real_quantity + (int)$quantity;
        } else {
            $inv_p->real_quantity = (int)($inv_p->shop_quantity - $inv_p->sold_quantity) + (int)$quantity;
        }

        if ($inv_p->save()) {
            $inventory->update(); // last modification date

            $response['success'] = true;
            $response['message'] = $this->l('Product quantities have been updated successfully');
            $response['inventoryProduct'] = array(
                'id' => (int)$inv_p->id,
                'real_quantity' => (int)$inv_p->real_quantity,
                'sold_quantity' => (int)$inv_p->sold_quantity,
                'shop_quantity' => (int)$inv_p->shop_quantity,
                'stock_difference' => (int)$inv_p->real_quantity - ($inv_p->shop_quantity - $inv_p->sold_quantity),
                'employee' => $this->getEmployeeName($this->context->employee->id),
                'date_upd' => Tools::displayDate($inv_p->date_upd, null, true),
            );
        } else {
            $response['message'] = $this->l('Unable to save modifications');
        }
    }

    public function isSupervisor($id_employee)
    {
        $is_supervisor = true;
        if (!$this->context->employee->isSuperAdmin()) {
            if (Configuration::get('WKINVENTORY_EMPL_RESTRICTION')) {// If employee restriction
                $is_supervisor = ((int)$id_employee === (int)$this->context->employee->id);
            }
        }
        return $is_supervisor;
    }

    public function ajaxProcessUpdateRealQuantity()
    {
        $response = array(
            'success' => false,
            'message' => $this->l('Unable to load this inventory'),
        );
        $inventory = new StockTake(Tools::getValue('id_inventory'));

        if (Validate::isLoadedObject($inventory)) {
            $is_supervisor = $this->isSupervisor($inventory->id_employee);

            if ($is_supervisor && Tools::getIsset('inventoryProducts')) {
                $updated_ip = 0;
                $errors = 0;
                $input_prefix = 'real_quantity_';

                foreach (Tools::getValue('inventoryProducts') as $row) {
                    if (strpos($row['name'], $input_prefix) === false) {
                        continue;
                    }
                    $id_inventory_product = (int)Tools::substr($row['name'], Tools::strlen($input_prefix));
                    $inventory_product = new StockTakeProduct($id_inventory_product);
                    if (Validate::isLoadedObject($inventory_product)) {
                        if ($inventory->id == $inventory_product->id_inventory) {
                            $inventory_product->real_quantity = (int)$row['value'];
                            if ($inventory_product->save()) {
                                ++$updated_ip;
                            } else {
                                ++$errors;
                            }
                        } else {
                            ++$errors;
                        }
                    } else {
                        ++$errors;
                    }
                }
                $response['success'] = ($updated_ip > $errors);
                $inventory->update(); // last modification date
                $response['message'] = sprintf($this->l('%d product(s) updated, %d error(s).'), $updated_ip, $errors);
            } else {
                $response['message'] = $this->l('Forbidden action or no products to update');
            }
        }
        WorkshopInv::renderJSON($response);
    }

    public function productsFoundHtml()
    {
        $this->context->smarty->assign(array(
            'asm' => $this->canUseAdvancedStockManagementOld || $this->canUseAdvancedStockManagementNew ? true : false,
        ));
        return $this->context->smarty->createTemplate(
            _PS_MODULE_DIR_.$this->module->name.'/views/templates/admin/stocktake/html_data.tpl'
        )->fetch();
    }

    public function getFormScanUpdateProduct(StockTake $inventory)
    {
        $helper = new HelperForm();
        $helper->id = 'form_inventory_product_scan';
        $quantity_update = (int)Configuration::get('WKINVENTORY_DEFAULTQTY_UPDATE');
        $is_supervisor = $this->isSupervisor($inventory->id_employee);

        $form_fields = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Scan/Find Product And Update Stock'),
                    'icon' => 'icon-barcode',
                ),
                'id_form' => 'wkinventory_panel_form_scan',
                'input' => array(
                    array(
                        'type' => 'text',
                        'name' => 'ean',
                        'hint' => $this->l('Start by typing the first letters of the product’s (name, reference, EAN or UPC), then select the product/combination from the drop-down list'),
                        'class' => 'form-control input-lg',
                        'label' => $this->l('Reference / Product Name / EAN13 / UPC'),
                        'size' => 60,// PS 1.5
                    ),
                    array(
                        'type' => ($this->module->is_before_16 ? 'free' : 'html'),
                        'name' => 'products_found',
                        'html_content' => ($this->module->is_before_16 ? '' : $this->productsFoundHtml()),
                    ),
                ),
                'submit' => array(
                    'class' => ($this->module->is_before_16 ? 'button' : 'btn btn-default pull-right').' addInventoryProduct-btn addInventoryProduct-disabled',
                    'title' => $this->l('Correct Product Quantity'),
                    'id' => 'wkaddInventoryProduct',
                ),
                'buttons' => array(
                    'auto-fill-quantities' => array(
                        'title' => $this->l('Correct Bulk Quantities'),
                        'type' => 'button',
                        'id' => 'btnAutofillQties',
                        'href' => 'javascript:WK_INVENTORY.autoFill()',
                        'class' => 'btn btn-default pull-left',
                        'icon' => 'process-icon-duplicate'
                    )
                )
            ),
        );

        if ($is_supervisor) {
            $form_fields['form']['input'][] = array(
                'type' => 'text',
                'label' => $this->l('Quantity to increase/decrease'),
                'name' => 'quantity',
                'class' => 'input fixed-width-md',
                'desc' => $this->l('Enter here the quantity to increase/decrease the available product quantity to correct the final stock').'.',
                'hint' => $this->l('Negative values are allowed')
            );
        }

        $default_field_value = array(
            'ean' => null,
            'quantity' => $quantity_update ? $quantity_update : 1,
        );
        if ($this->module->is_before_16) {
            $default_field_value['products_found'] = $this->productsFoundHtml();
        }

        $helper->show_toolbar = false;
        $helper->table = StockTakeProduct::$definition['table'];
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = 'formscanupdateproduct';
        $helper->submit_action = 'submitWkinventoryproduct';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminStocktake', false)
        .'&id_inventory='.$inventory->id;
        $helper->token = Tools::getAdminTokenLite('AdminStocktake');

        $helper->tpl_vars = array(
            'fields_value' => $default_field_value, /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );
        return $helper->generateForm(array('form' => $form_fields));
    }

    // Search & choose product
    public function ajaxProcessSearchProducts()
    {
        if ($products = WorkshopInv::searchByTerms(pSQL(Tools::getValue('product_search')))) {
            $id_lang = (int)$this->context->language->id;
            /* Instanciate current inventory */
            $inventory = new StockTake((int)Tools::getValue('id_inventory'));
            $canManageWarehouses = ($this->canUseAdvancedStockManagementOld || $this->canUseAdvancedStockManagementNew) && $inventory->is_empty;

            foreach ($products as &$product) {
                $id_product = (int)$product['id_product'];
                $productObj = new Product($id_product, false, $id_lang);
                // Get product attribute ID if at least one of each reference|ean13|upc is known
                // this will be used to show it as default option in dropdown list
                $default_attribute_id = 0;
                if (Combination::isFeatureActive()) {
                    if (!empty($product['id_product_attribute'])) {
                        $default_attribute_id = (int)$product['id_product_attribute'];
                    } else {
                        $default_attribute_id = Product::getDefaultAttribute((int)$id_product);
                    }
                }

                $combinations = $warehouses = array();
                $attributes = $productObj->getAttributesGroups($id_lang);

                // Attributes
                foreach ($attributes as $attribute) {
                    if (!isset($combinations[$attribute['id_product_attribute']]['attributes'])) {
                        $combinations[$attribute['id_product_attribute']]['attributes'] = '';
                    }
                    $combinations[$attribute['id_product_attribute']]['attributes'] .= $attribute['attribute_name'].' - ';
                    $combinations[$attribute['id_product_attribute']]['id_product_attribute'] = $attribute['id_product_attribute'];
                    $combinations[$attribute['id_product_attribute']]['default_on'] = $attribute['default_on'];
                }
                // Combinations
                foreach ($combinations as &$combination) {
                    $combination['attributes'] = rtrim($combination['attributes'], ' - ');
                }
                $product['combinations'] = $combinations;

                // Warehouses
                $use_asm = false;
                if (($this->canUseAdvancedStockManagementOld && StockAvailable::dependsOnStock($id_product)) ||
                    ($this->canUseAdvancedStockManagementNew && $productObj->advanced_stock_management)) {/* A.S.M ? */
                    $use_asm = true;
                    $classWhName = WorkshopInv::getWarehouseClassName();
                    $warehouses_collection = $classWhName::getProductWarehouseList(
                        $id_product,
                        $default_attribute_id
                    );
                    foreach ($warehouses_collection as $warehouse_location) {
                        $wh = WorkshopInv::getWarehouseClass($warehouse_location['id_warehouse']);
                        $warehouses[$wh->id]['id_warehouse'] = $wh->id;
                        $warehouses[$wh->id]['name'] = $wh->name;
                    }
                }
                $product['depend_stocks'] = $use_asm;
                $product['warehouses'] = $warehouses;
                $product['warehouses_count'] = count($warehouses);
            }

            $to_return = array(
                'found' => true,
                'products' => $products,
                'default_attribute_select' => $default_attribute_id,
                'canManageWarehouses' => $canManageWarehouses,
                'automaticAddToInventory' => (int)Configuration::get('WKINVENTORY_ADDQTY_AUTO'),
                'count_products' => count($products),
            );
        } else {
            $to_return = array('found' => false);
        }

        die(Tools::jsonEncode($to_return));
    }

    // Load warehouses for selected product/combination
    public function ajaxProcessLoadLocationsByWarehouse()
    {
        die(Tools::jsonEncode(array(
            'locations' => WorkshopInv::getWarehousesLocations(Tools::getValue('id_warehouse')),
        )));
    }

    // Load warehouses for selected product/combination
    public function ajaxProcessLoadWarehousesCombination()
    {
        $id_product = (int)Tools::getValue('id_product');
        $id_product_attribute = (int)Tools::getValue('id_product_attribute');
        $warehouses = array();

        $productObj = new Product($id_product, false);

        if (Validate::isLoadedObject($productObj) &&
            (($this->canUseAdvancedStockManagementOld && StockAvailable::dependsOnStock($id_product)) ||
            ($this->canUseAdvancedStockManagementNew && $productObj->advanced_stock_management))) {
            $classWhName = WorkshopInv::getWarehouseClassName();
            $warehouses_collection = $classWhName::getProductWarehouseList(
                $id_product,
                $id_product_attribute
            );
            foreach ($warehouses_collection as $warehouse_location) {
                $wh = WorkshopInv::getWarehouseClass($warehouse_location['id_warehouse']);
                array_push($warehouses, array(
                    'id_warehouse' => $wh->id,
                    'name' => $wh->name,
                ));
            }
        }
        die(Tools::jsonEncode(array(
            'warehouses' => $warehouses,
        )));
    }

    public function getProductCoverImg($id_product)
    {
        $url = WorkshopInv::getProductImage($id_product, $this->context->language->id);

        $tpl = $this->createTemplate('helpers/list/tpl_column.tpl');
        $tpl->assign(array(
            'column' => 'image',
            'url' => $url
        ));
        return $tpl->fetch();
    }

    public function productNameWithCombination($name, $tr)
    {
        if (empty($tr['combination'])) {
            return $name;
        }
        $tpl = $this->createTemplate('helpers/list/tpl_column.tpl');
        $tpl->assign(array(
            'column' => 'name',
            'name' => $name,
            'combination' => $tr['combination'],
        ));
        return $tpl->fetch();
    }

    public function getProductLink($id_product)
    {
        $tpl = $this->createTemplate('helpers/list/tpl_column.tpl');
        $tpl->assign(array(
            'id_product' => $id_product,
            'column' => 'url',
            'url' => WorkshopInv::getBackofficeProductUrl($id_product),
        ));
        return $tpl->fetch();
    }

    public function initProcess()
    {
        if (Tools::isSubmit('submitReset'.StockTakeProduct::$definition['table'])) {
            $this->list_id = StockTakeProduct::$definition['table'];
            $this->processResetFilters();
        }
        parent::initProcess();
    }

    public function getProductsList(StockTake $inventory)
    {
        $id_lang = (int)$this->context->language->id;

        $this->table = StockTakeProduct::$definition['table'];
        $this->className = 'StockTakeProduct';
        $this->identifier = 'id';
        $this->list_id = StockTakeProduct::$definition['table'];
        $this->lang = false;
        $this->list_no_link = true;
        $this->_pagination = array(20, 50, 100, 300, 500, 1000, 1500, 2000);
        $this->toolbar_title = $this->l('Products / Combinations');

        self::$currentIndex = self::$currentIndex.'&updatewkinventory&id_inventory='.$inventory->id;
        $ps_asm = $this->canUseAdvancedStockManagementOld || $this->canUseAdvancedStockManagementNew ? true : false;

        $this->_defaultOrderBy = 'a.id_inventory_product';
        $this->_defaultOrderWay = 'DESC';

        // Change shop according to the defined inventory shop
        if (Shop::isFeatureActive() && ShopGroup::getTotalShopGroup() >= 1 && Shop::getTotalShops() > 0) {
            Shop::setContext(Shop::CONTEXT_SHOP, $inventory->id_shop);
        }

        // Remove bulk inventory products
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?'),
                'icon' => 'icon-trash'
            )
        );
        $this->_select = '`'.StockTakeProduct::$definition['primary'].'` id, a.`id_product` as product_id, 
            IF(a.`id_product_attribute` > 0, pa.`reference`, p.`reference`) as reference,
            IF(a.`id_product_attribute` > 0, pa.`ean13`, p.`ean13`) as ean13,
            IF(a.`id_product_attribute` > 0, pa.`upc`, p.`upc`) as upc,
            pl.`name`, a.`date_upd` as last_update';
        // Join Queries
        $this->_join = 'LEFT JOIN `'._DB_PREFIX_.'product` p ON p.`id_product` = a.`id_product` 
        '.Shop::addSqlAssociation('product', 'p');
        $this->_join .= 'LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (
             p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.$id_lang
             .Shop::addSqlRestrictionOnLang('pl').'
        )';
        $this->_join .= 'LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (
             a.`id_product_attribute` = pa.`id_product_attribute`
        )';
        $this->_join .= 'LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON (
             m.`id_manufacturer` = p.`id_manufacturer`
        )';
        // Product Image
        if (version_compare(_PS_VERSION_, '1.6.1.0', '>=')) {
            $this->_join .= '
                LEFT JOIN `'._DB_PREFIX_.'image_shop` image_shop ON (
                    image_shop.`id_product` = p.`id_product` AND image_shop.`cover` = 1 AND image_shop.id_shop = p.id_shop_default
                )';
        }
        $this->_join .=  'LEFT JOIN `'._DB_PREFIX_.'image` i ON '.(
            version_compare(_PS_VERSION_, '1.6.1.0', '>=') ? '(i.`id_image` = image_shop.`id_image`)' : '(i.`id_product` = p.`id_product`)'
        );
        if (version_compare(_PS_VERSION_, '1.6.1.0', '<')) {
            $this->_join .= Shop::addSqlAssociation('image', 'i', false, 'image_shop.cover=1');
        }
        $this->_select .= ', '.(version_compare(_PS_VERSION_, '1.6.1.0', '>=') ? 'image_shop.`id_image`' : 'MAX(image_shop.`id_image`)').' as `id_image`';

        // Get Warehouse
        if ($ps_asm) {
            $this->_select .= ', wpl.`location`';
            if (empty($inventory->id_warehouse)) {
                $this->_select .= ', '.($this->canUseAdvancedStockManagementOld ? 'w' : 'wl').'.`name` as warehouse_name';
                $this->_join .= 'LEFT JOIN `'._DB_PREFIX_.'warehouse` w ON (
                     a.`id_warehouse` = w.`id_warehouse`
                )';
                $this->_join .= ($this->canUseAdvancedStockManagementNew ? 'LEFT JOIN `'._DB_PREFIX_.'warehouse_lang` wl ON (
                     w.`id_warehouse` = wl.`id_warehouse` AND wl.`id_lang` = '.$id_lang.'
                )' : '');
            }
            $this->_join .= 'LEFT JOIN `'._DB_PREFIX_.'warehouse_product_location` wpl ON (
                 wpl.`id_warehouse` = a.`id_warehouse` AND 
                 wpl.`id_product` = a.`id_product` AND 
                 wpl.`id_product_attribute` = a.`id_product_attribute`
            )';
        }
        // Where query
        $this->_where = sprintf('AND `'.StockTake::$definition['primary'].'` = %d', (int)$inventory->id);

        if (version_compare(_PS_VERSION_, '1.6.1.0', '<')) {
            $this->_group = 'GROUP BY a.id_product, a.id_product_attribute';
        }

        $is_supervisor = $this->isSupervisor($inventory->id_employee);
        if (!$is_supervisor) {// Display just warning above the list
            $this->errors[] = $this->displayWarning($this->l('You don\'t have permissions to edit this inventory!'));
        }

        $this->actions = $is_supervisor ? array('delete') : array('-');

        $this->fields_list = array(
            'product_id' => array(
                'title' => '#',
                'callback' => 'getProductLink',
                'filter_key' => 'a!id_product'
            ),
            'image' => array(
                'title' => 'Photo',
                'orderby' => false,
                'search' => false,
                'image' => 'p',
            ),
            'name' => array(
                'title' => $this->l('Name'),
                'filter_key' => 'pl!name',
                'callback' => 'productNameWithCombination',
            ),
            'ean13' => array(
                'title' => $this->l('Ean13'),
                'filter_key' => 'p!ean13',
                'order_key' => 'ean13'
            ),
            'reference' => array(
                'title' => $this->l('Reference'),
                'filter_key' => 'p!reference',
                'order_key' => 'reference'
            ),
            'last_update' => array(
                'title' => $this->l('Last modification'),
                'class' => 'date_upd',
                'type' => 'datetime',
                'filter_key' => 'a!date_upd',
            ),
            'unit_price' => array(
                'type' => 'price',
                'title' => $this->l('Price'),
                'hint' => $this->l('Wholesale price'),
                'class' => 'unit_price',
                'badge_danger' => true,
                'align' => 'right',
                'width' => 70
            ),
        );
        // Advanced stock management
        if ($ps_asm) {
            if (empty($inventory->id_warehouse)) {
                $this->fields_list['warehouse_name'] = array(
                    'title' => $this->l('Warehouse'),
                    'filter_key' => 'wh!name',
                );
            }
            $this->fields_list['location'] = array(
                'title' => $this->l('Location'),
                'filter_key' => 'wpl!location',
            );
        }
        $this->fields_list['shop_quantity'] = array(
            'title' => (!$ps_asm ? $this->l('Available qty') : $this->l('Quantity')),
            'hint' => (!$ps_asm ?
                $this->l('Quantity found in your Backoffice') :
                $this->l('Can be the available quantity found in Backoffice or the physical quantity if product is based on advanced stock management system.')
            ),
            'class' => 'shop_quantity',
            'align' => 'center'.($this->module->is_before_16 ? ' shop_quantity' : ''),
            'width' => 100
        );
        $this->fields_list['sold_quantity'] = array(
            'title' => ($this->ps172 === true ? $this->l('Reserved quantity') : $this->l('Quantity sold')),
            'hint' => ($this->ps172 === true ? $this->l('Reserved quantity') : $this->l('Quantity sold')).' '.$this->l('since the beginning of the inventory'),
            'class' => 'sold_quantity',
            'align' => 'center'.($this->module->is_before_16 ? ' sold_quantity' : ''),
            'width' => 100,
        );
        $this->fields_list['stock_difference'] = array(
            'type' => ($is_supervisor && !$inventory->stock_updated) ? 'editable' : 'integrer',
            'title' => $this->l('Adjustment qty'),
            'hint' => $this->l('Stock gap'),
            'align' => 'center',
            'search' => false,
            'orderby' => false,
            'class' => 'stock_difference',
            'width' => 100,
            'remove_onclick' => true
        );
        $this->fields_list['real_quantity'] = array(
            'type' => ($is_supervisor && !$inventory->done && !$inventory->stock_updated) ? 'editable' : 'integrer',
            'title' => $this->l('Real qty'),
            'hint' => $this->l('Real quantity found in your store/warehouse'),
            'align' => 'center',
            'orderby' => false,
            'search' => false,
            'width' => 110,
            'remove_onclick' => true
        );

        $this->processFilter();

        // Categories / brands / supplier & warehouse informations to which products belong
        $inventory_for = array();
        if ($inventory->category_ids) {
            $inventory_categories = $this->returnCategoriesNames($inventory->category_ids, true);
            if (count($inventory_categories)) {
                $inventory_for[$this->l('CATEGORIES')] = $inventory_categories;
            }
        }
        if ($inventory->id_supplier) {
            $inventory_supplier = new Supplier($inventory->id_supplier);
            if (Validate::isLoadedObject($inventory_supplier)) {
                $inventory_for[$this->l('SUPPLIER')] = array($inventory_supplier->name);
            }
        }
        if ($inventory->manufacturer_ids) {
            $inventory_manufacturers = $this->returnManufacturersNames($inventory->manufacturer_ids, true);
            if (count($inventory_manufacturers)) {
                $inventory_for[$this->module->is_greater_17 ? $this->l('MANUFACTURERS') : $this->l('BRANDS')] = $inventory_manufacturers;
            }
        }
        if ($inventory->id_warehouse) {
            $inventory_warehouse = WorkshopInv::getWarehouseClass($inventory->id_warehouse);
            if (Validate::isLoadedObject($inventory_warehouse)) {
                $inventory_for[$this->l('WAREHOUSE')] = array($inventory_warehouse->name);
            }
        }
        $this->tpl_list_vars['inventory_for'] = $inventory_for;
        /***********************************************************************************/
        $this->tpl_list_vars['has_bulk_actions'] = true;
        // Set anchor - Scroll to the added product when empty inventory
        if (isset($this->context->cookie->last_id_inventory_product) && $this->context->cookie->last_id_inventory_product) {
            $this->tpl_list_vars['last_id_inventory_product'] = (int)$this->context->cookie->last_id_inventory_product;
        }
        return parent::renderList();
    }

    /*
    * Get the generated list before display and process some changes
    */
    public function getList($id_lang, $orderBy = null, $orderWay = null, $start = 0, $limit = null, $id_lang_shop = null)
    {
        if (Tools::isSubmit('exportCSV') || Tools::isSubmit('exportwkinventory_product')) {
            $limit = $this->module->is_greater_17 ? 99999999 : false;
        }
        if (isset($this->context->cookie->last_id_inventory_product) && $this->context->cookie->last_id_inventory_product) {
            $orderBy = 'a.id_inventory_product';
            $orderWay = 'DESC';
            unset($this->context->cookie->last_id_inventory_product);
        }
        parent::getList($id_lang, $orderBy, $orderWay, $start, $limit, $id_lang_shop);

        if ($this->_list && $this->display == 'edit') {
            foreach ($this->_list as &$product) {
                $product['id_cover'] = $product['id_product'];
                $product['stock_difference'] = (int)$product['real_quantity'] - ((int)$product['shop_quantity'] - (int)$product['sold_quantity']);
                $product['combination'] = (!empty($product['id_product_attribute']) ? WorkshopInv::getAttributesCombinationNames($product['id_product_attribute']) : '');
                // Put inventory product identifier in <tr> tag in class
                $product['class'] = $product['id_inventory_product']
                .($product['has_error'] ? ' hasError' : '')
                .($product['stock_updated'] ? ' stockUpdate' : '')
                ;
                $product['badge_danger'] = $product['unit_price'] <= 0;
            }
        }
        // Export list to CSV format
        if (Tools::isSubmit('exportCSV') && Tools::getIsset('id_inventory')) {
            if (count($this->_list) > 0) {
                $this->renderCSV();
            } else {
                $this->displayWarning($this->l('There is nothing to export as a CSV.'));
            }
        }
    
        // Export list to PDF format
        if (Tools::isSubmit('exportwkinventory_product') && Tools::getIsset('id_inventory') &&
            Configuration::get('WKINVENTORY_PDFREPORT_MODE') == 'normal') {
            $inventory = new StockTake((int)Tools::getValue('id_inventory'));

            if (!Validate::isLoadedObject($inventory)) {
                $this->errors[] = $this->l('Unable to load this inventory');
            } else {
                require_once(_PS_MODULE_DIR_.$this->module->name.'/classes/HTMLTemplateInventoryList.php');

                $this->context->smarty->assign(array(
                    'inventory_products' => $this->_list,
                ));
                $inventory->free_html = $this->context->smarty->fetch(
                    _PS_MODULE_DIR_.$this->module->name.'/views/templates/admin/pdf/products_report.tpl'
                );

                // Extract inventories products ID from the current list
                $inventories_products_ids = array();
                if (count($this->_list) > 0) {
                    foreach ($this->_list as $item) {
                        $inventories_products_ids[] = (int)$item['id_inventory_product'];
                    }
                }
                $inventory->stock_valuation = Tools::displayPrice($inventory->getStockValue($inventories_products_ids));
                $inventory->inventory_count = count($this->_list);

                $pdf = new PDF($inventory, 'InventoryList', Context::getContext()->smarty);
                $pdf->render();
                exit();
            }
        }
    }

    /*
    * Export all rows in CSV format
    */
    public function renderCSV()
    {
        $inventory = new StockTake((int)Tools::getValue('id_inventory'));
        if (!Validate::isLoadedObject($inventory)) {
            $this->errors[] = $this->l('Unable to load this inventory');
        } else {
            $collection = array();
            foreach ($this->_list as $inventory_product) {
                $unit_price = Tools::ps_round((float)$inventory_product['unit_price'], 2);
                $item = array(
                    'id' => (int)$inventory_product['id'],
                    'product_id' => (int)$inventory_product['id_product'],
                    'product_name' => $inventory_product['name'],
                    'combination' => $inventory_product['combination'],
                    'reference' => pSQL($inventory_product['reference']),
                    'ean13' => $inventory_product['ean13'],
                    'upc' => $inventory_product['upc'],
                    'shop_quantity' => (int)$inventory_product['shop_quantity'],
                    'sold_quantity' => (int)$inventory_product['sold_quantity'],
                    'stock_difference' => (int)$inventory_product['stock_difference'],
                    'real_quantity' => (int)$inventory_product['real_quantity'],
                    'unit_price' => $unit_price,
                    'stock_difference_cost' => (int)$inventory_product['stock_difference'] * $unit_price,
                    'total_price' => (int)$inventory_product['real_quantity'] * $unit_price,
                );
                if (!empty($inventory_product['id_warehouse'])) {
                    $warehouse = WorkshopInv::getWarehouseClass($inventory_product['id_warehouse']);
                    $item['warehouse'] = $warehouse->name;
                    $classWhLocationName = (class_exists('StorehouseProductLocation') ? 'StorehouseProductLocation' : 'WarehouseProductLocation');
                    $item['location'] = $classWhLocationName::getProductLocation(
                        $inventory_product['id_product'],
                        $inventory_product['id_product_attribute'],
                        $warehouse->id
                    );
                }
                $collection[] = (object)$item;
            }

            $csv = new CSV($collection, $this->l('products_').$inventory->name);
            die($csv->export());
        }
    }

    /*
    * Override Filter Process Query
    */
    public function processFilter()
    {
        if (!Tools::isSubmit('submitReset'.StockTakeProduct::$definition['table'])) {
            parent::processFilter();
        }
        if (Tools::isSubmit('submitFilter'.StockTakeProduct::$definition['table'])) {
            $list_id = StockTakeProduct::$definition['table'];
            $Filter_reference = Tools::getValue($list_id.'Filter_p!reference');
            if (!empty($Filter_reference)) {
                if (Combination::isFeatureActive()) {
                    // Allowing to search also by attribute reference in addition with product reference
                    $attribute_where = ' (IF(a.`id_product_attribute` > 0, pa.`reference`, p.`reference`) LIKE \'%'.pSQL($Filter_reference).'%\')';
                    $this->_filter = str_replace('p.`reference` LIKE \'%'.pSQL($Filter_reference).'%\'', $attribute_where, $this->_filter);
                }
            }
            $Filter_ean13 = Tools::getValue($list_id.'Filter_p!ean13');
            if (!empty($Filter_ean13)) {
                // Allowing to search also by attribute ean13 in addition with product ean13
                $ean13_where = ' (IF(a.`id_product_attribute` > 0, pa.`ean13`, p.`ean13`) LIKE \'%'.pSQL($Filter_ean13).'%\')';
                $this->_filter = str_replace('p.`ean13` LIKE \'%'.pSQL($Filter_ean13).'%\'', $ean13_where, $this->_filter);
            }
        }
    }

    public function processUpdateInventory()
    {
        $this->identifier = 'id_inventory';
        $this->table = 'wkinventory';
        $this->list_id = 'stocktake';

        $this->processUpdate();
    }

    /*
    * Update Inventory
    */
    public function processUpdate()
    {
        return parent::processUpdate();
    }

    public function postProcess()
    {
        parent::postProcess();

        if (Tools::isSubmit('submitAddwkinventory_product')) {
            $this->processUpdateInventory();
        }
        // Inventory product Bulk deletion
        if (Tools::isSubmit('submitBulkdeletewkinventory_product')) {
            $productsBox = Tools::getValue(StockTakeProduct::$definition['table'].'Box');
            if ($productsBox) {
                foreach ($productsBox as $id_product) {
                    $product = new StockTakeProduct((int)$id_product);
                    $product->delete();
                }
                Tools::redirectAdmin(self::$currentIndex.'&updatewkinventory&id_inventory='.(int)Tools::getValue('id_inventory').'&token='.$this->token.'&conf=1');
            }
        }
        // Delete current inventory product
        if (Tools::isSubmit('deletewkinventory_product')) {
            $id_inventory_product = (int)Tools::getValue('id');
            if (!empty($id_inventory_product)) {
                $obj = new StockTakeProduct($id_inventory_product);
                $obj->delete();

                Tools::redirectAdmin(self::$currentIndex.'&updatewkinventory&id_inventory='.(int)Tools::getValue('id_inventory').'&token='.$this->token.'&conf=1');
            } else {
                $this->errors[] = $this->l('An error occurred while attempting to delete inventory product');
            }
        }
    }

    /*
    * Add / Edit Inventory
    */
    public function renderForm()
    {
        $id_lang = $this->context->language->id;
        $this->default_form_language = $id_lang;
        $id_inventory = (int)Tools::getValue('id_inventory');

        $this->fields_form['legend'] = array(
            'title' => (!empty($id_inventory) ? $this->l('Manage') : $this->l('Create')).' '.$this->l('inventory'),
            'icon' => 'icon-edit',
        );
        if (empty($id_inventory)) {
            $this->fields_form['help_tab'] = array();
        }
        $this->fields_form['input'] = array(array(
            'type' => 'text',
            'label' => $this->l('Label'),
            'name' => 'name',
            'class' => 'input fixed-width-xl',
        ));

        // I F   E D I T   M O D E
        if (!empty($id_inventory)) {
            $this->show_form_cancel_button = false;
            $inventory = new StockTake($id_inventory);
            if (!Validate::isLoadedObject($inventory)) {
                throw new PrestaShopException('Unable to load this inventory');
            }
            // Is employee supervisor
            $is_supervisor = $this->isSupervisor($inventory->id_employee);

            /* Finish Inventory Field */
            if ($is_supervisor && !$inventory->stock_updated) {
                $this->fields_form['input'][] = array(
                    'type' => $this->module->is_before_16 ? 'radio' : 'switch',
                    'label' => $this->l('Finish this inventory?'),
                    'class' => 't',
                    'name' => 'done',
                    'is_bool' => true,
                    'desc' => $this->l('When quantities are up to date, set this option to « Yes » and click on « Save » button to allow real stocks update of your shop and close this inventory').'.',
                    'values' => array(
                        array('id' => 'done_on', 'value' => true, 'label' => $this->l('Yes')),
                        array('id' => 'done_off', 'value' => false, 'label' => $this->l('No'))
                    ),
                );
                if (!$this->module->is_before_16) {
                    $this->fields_form['buttons'] = array(array(
                        'title' => $this->l('Update'),
                        'icon' => 'process-icon-save',
                        'js' => 'return (beginUpdateProcess(this));',
                        'class' => 'btn btn-default pull-right'
                    ));
                } else {
                    $this->fields_form['submit'] = array(/* Save btn */
                        'title' => $this->l('Update'),
                        'class' => 'button beginUpdateProcess',
                    );
                }
            }

            $ajax_url = $this->context->link->getAdminLink('AdminStocktake', false);
            // Check if the current page use SSL connection or not
            if (Tools::usingSecureMode()) {
                $ajax_url = str_replace('http://', 'https://', $ajax_url);
            }

            $template_data = array(
                'id_inventory' => $id_inventory,
                'is_empty' => $inventory->is_empty,
                'adminstocktakeLink' => $ajax_url,
                'inventoryDone' => $inventory->done,
                'formScanUpdateProduct' => $this->getFormScanUpdateProduct($inventory),
                'isSupervisor' => $is_supervisor,
                'productsList' => $this->getProductsList($inventory),
                'defaultQty' => (int)Configuration::get('WKINVENTORY_DEFAULTQTY_UPDATE'),
                'addToExistantQty' => (int)Configuration::get('WKINVENTORY_ADDQTY_EXISTANT'),
                'module_path' => _MODULE_DIR_.$this->module->name,
            );
            $this->context->smarty->assign($template_data);
        } else {
            // I F   A D D   M  O D E
            $this->fields_form['submit'] = array(/* Save btn */
                'title' => $this->l('Save'),
                'class' => $this->module->is_before_16 ? 'button' : 'btn btn-default pull-right',
            );

            // For categories
            $categories = array();
            $root_category = Category::getRootCategory();
            $shops = Shop::getShops($id_lang);
            $suppliers = Supplier::getSuppliers(false, $id_lang);
            array_unshift($suppliers, array(
                'id_supplier' => 0,
                'name' => $this->l('No supplier'),
            ));
            if (version_compare(_PS_VERSION_, '1.6', '>=')) {
                $root = Category::getRootCategory();
                /* Generating the tree for the first column
                   The string in param is the ID used by the generated tree */
                $tree = new HelperTreeCategories('categories_tree');
                $tree->setUseCheckBox(true)
                    ->setAttribute('is_category_filter', $root->id)
                    ->setRootCategory($root->id)
                    ->setFullTree(true)
                    ->setSelectedCategories(explode(',', Configuration::get('categories')))
                    ->setInputName('categoryBox'); // Set the name of input. The option "name" of $fields_form doesn't seem to work with "categories_select" type
                $categories = $tree->render();
            } else {
                $root_category = array('id_category' => $root_category->id, 'name' => $root_category->name);
            }

            // For what shop
            $this->fields_form['input'][] = array(
                'type' => 'select',
                'label' => $this->l('Shop'),
                'name' => 'id_shop',
                'options' => array(
                    'query' => $shops,
                    'id' => 'id_shop',
                    'name' => 'name',
                )
            );
            // Progressif (empty or not) ?
            $this->fields_form['input'][] = array(
                'type' => $this->module->is_before_16 ? 'radio' : 'switch',
                'label' => $this->l('Empty without products?'),
                'class' => 't',
                'desc' => $this->l('If set to « Yes », an empty inventory will be created letting you to add gradually the products to manage their stocks')
                .'.<br />'.$this->l('Filters selections below will not be taken into account').'.',
                'name' => 'is_empty',
                'is_bool' => true,
                'values' => array(
                    array('id' => 'is_empty_on', 'value' => 1, 'label' => $this->l('Yes')),
                    array('id' => 'is_empty_off', 'value' => 0, 'label' => $this->l('No'))
                )
            );
            // For categories
            if (version_compare(_PS_VERSION_, '1.6', '>=')) {
                $this->fields_form['input'][] = array(
                    'type' => 'categories_select',
                    'name' => 'categories',
                    'label' => $this->l('Categories'),
                    'category_tree' => $categories,
                    'tree' => array(
                        'use_search' => false,
                        'id' => 'categories',
                        'use_checkbox' => true,
                        'selected_categories' => Tools::getValue('categories', array()),
                    ),
                    'desc' => $this->l('Click on Expand All to see the entire tree').'.',
                );
            } else {
                $this->fields_form['input'][] = array(
                    'type' => 'categories',
                    'name' => 'categories',
                    'label' => $this->l('Categories'),
                    'desc' => $this->l('Click on Expand All to see the entire tree').'.',
                    'values' => array(
                        'trads' => array(
                            'Root' => $root_category,
                            'selected' => $this->l('Selected'),
                            'Collapse All' => $this->l('Collapse All'),
                            'Expand All' => $this->l('Expand All'),
                            'Check All' => $this->l('Check All'),
                            'Uncheck All' => $this->l('Uncheck All'),
                        ),
                        'selected_cat' => Tools::getValue('categories', array()),
                        'input_name' => 'categoryBox[]',
                        'use_radio' => false,
                        'use_search' => false,
                        'disabled_categories' => array(),
                        'top_category' => Category::getTopCategory(),
                        'use_context' => true,
                    ),
                );
            }
            // For supplier
            $this->fields_form['input'][] = array(
                'type' => 'select',
                'label' => $this->l('Supplier'),
                'name' => 'id_supplier',
                'options' => array(
                    'query' => $suppliers,
                    'id' => 'id_supplier',
                    'name' => 'name',
                )
            );
            // For manufacturers
            $manufacturers = Manufacturer::getManufacturers(false, 0, true, false, false, false, true);
            array_unshift($manufacturers, array(
                'id_manufacturer' => 0,
                'name' => !$this->module->is_greater_17 ? $this->l('No manufacturers') : $this->l('No brands'),
            ));
            $this->fields_form['input'][] = array(
                'type' => 'select',
                'label' => !$this->module->is_greater_17 ? $this->l('Manufacturers') : $this->l('Brands'),
                'name' => 'manufacturer_ids[]',
                'multiple' => true,
                'options' => array(
                    'query' => $manufacturers,
                    'id' => 'id_manufacturer',
                    'name' => 'name'
                )
            );
            // If A.S.M
            if ($this->canUseAdvancedStockManagementOld || $this->canUseAdvancedStockManagementNew) {
                $classWhName = WorkshopInv::getWarehouseClassName();
                $warehouses = $classWhName::getWarehouses();

                array_unshift($warehouses, array(
                    'id_warehouse' => '',
                    'name' => $this->l('No warehouse'),
                ));
                $this->fields_form['input'][] = array(
                    'type' => 'select',
                    'label' => $this->l('Warehouse'),
                    'name' => 'id_warehouse',
                    'options' => array(
                        'query' => $warehouses,
                        'id' => 'id_warehouse',
                        'name' => 'name',
                    ),
                );
                $warehouses_locations = WorkshopInv::getWarehousesLocations();
                if (count($warehouses_locations) > 1) {
                    $this->fields_form['input'][] = array(
                        'type' => 'select',
                        'label' => $this->l('Warehouse location(s)'),
                        'name' => 'warehouses_locations[]',
                        'id' => 'warehouses_locations',
                        'multiple' => true,
                        'options' => array(
                            'query' => $warehouses_locations,
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    );
                }
            }
            // Inventoty with stock at zero ?
            $this->fields_form['input'][] = array(
                'type' => $this->module->is_before_16 ? 'radio' : 'switch',
                'label' => $this->l('Start inventory with « Available Quantities » set to 0'),
                'class' => 't',
                'desc' => $this->l('If set to « Yes », the inventory will be created and started with products whose their available quantities will be set to zero')
                .'.<br />'.$this->l('Notes:').'<br />'
                .$this->l('- It is not the « Available Quantities » of your shop that will be really set to zero but whose found in « Available Qty » column in the inventory list')
                .'.<br />- '.$this->l('After finishing and closing an inventory, the shop quantities will be REPLACED by the new ones set in that inventory.'),
                'name' => 'stock_zero',
                'is_bool' => true,
                'values' => array(
                    array('id' => 'active_on', 'value' => 1, 'label' => $this->l('Yes')),
                    array('id' => 'active_off', 'value' => 0, 'label' => $this->l('No'))
                )
            );
        }
        if ($this->module->is_before_16) {
            $this->initToolbar();
        }

        return parent::renderForm();
    }

    /*
    * Add New Inventory
    */
    public function processAdd()
    {
        $categories = array_map('intval', Tools::getValue('categoryBox', array()));
        $id_supplier = Tools::getValue('id_supplier');
        $manufacturer_ids = Tools::getValue('manufacturer_ids');
        $id_shop = Tools::getValue('id_shop');
        $is_empty = (int)Tools::getValue('is_empty');
        $ps_asm = $this->canUseAdvancedStockManagementOld || $this->canUseAdvancedStockManagementNew ? true : false;

        if ($ps_asm) {
            $id_warehouse = Tools::getValue('id_warehouse');
            // Choosen warehouses locations
            $warehouses_locations = array();
            if (Tools::getValue('warehouses_locations')) {
                $warehouses_locations = WorkshopInv::getWarehousesLocations(
                    $id_warehouse,
                    Tools::getValue('warehouses_locations'),
                    true
                );
            }
        }
        if (!$is_empty) {
            // Search if product(s) exists
            $query = new DbQuery();
            $query->select('p.`id_product`, pa.`id_product_attribute`, ps.`advanced_stock_management`'.($ps_asm ? ', wpl.`id_warehouse`' : ''));
            $query->from('product', 'p');
            $query->innerJoin('product_shop', 'ps', 'p.`id_product` = ps.`id_product`');
            $query->where('ps.`id_shop` = '.(int)$id_shop);
            $query->leftJoin('product_attribute', 'pa', 'p.`id_product` = pa.`id_product`');
            // By Supplier
            if (!empty($id_supplier)) {
                $query->where('p.`id_supplier` = '.(int)$id_supplier);
            }
            // By Manufacturers
            $current_manufacturer = current($manufacturer_ids);
            if (sizeof($manufacturer_ids) && !empty($current_manufacturer)) {
                $query->where('p.`id_manufacturer` IN ('.pSQL(implode(',', array_map('intval', $manufacturer_ids))).')');
            }
            // By Categories
            if (count($categories)) {
                $query->innerJoin('category_product', 'cp', 'p.`id_product` = cp.`id_product`');
                $query->where(sprintf('cp.`id_category` IN (%s)', implode(',', array_filter($categories, 'is_numeric'))));
            }
            // By Warehouses / locations
            if ($ps_asm) {
                $query->leftJoin(
                    'warehouse_product_location',
                    'wpl',
                    'p.`id_product` = wpl.`id_product` AND (
                        wpl.`id_product_attribute` = pa.`id_product_attribute` OR wpl.`id_product_attribute` = 0
                     )'
                );
                if (!empty($id_warehouse)) {
                    $query->where('wpl.`id_warehouse` = '.(int)$id_warehouse);
                }
                if (count($warehouses_locations)) {
                    $query->where(sprintf('wpl.`location` IN (%s)', implode(',', $warehouses_locations)));
                }
            }
            $query->groupBy('p.`id_product`, pa.`id_product_attribute`'.($ps_asm ? ', wpl.`id_warehouse`' : ''));
            $results = Db::getInstance()->executeS($query);

            if (count($results)) {
                // Create Inventory instance
                if ($inventory = parent::processAdd()) {
                    $count = 0;
                    // Save inventory products
                    foreach ($results as $row) {
                        // ASM ? clean results and keep only products that are associated to warehouses
                        if ($ps_asm && empty($row['id_warehouse']) &&
                            (($this->canUseAdvancedStockManagementNew && $row['advanced_stock_management']) ||
                             ($this->canUseAdvancedStockManagementOld && StockAvailable::dependsOnStock($row['id_product'])))) {
                            continue;
                        }
                        $res = WorkshopInv::createInventoryProduct($row, $inventory->id, $inventory->id_shop);
                        if ($res) {
                            ++$count;
                        }
                    }
                } else {
                    $this->errors[] = $this->l('Unable to save inventory').'.';
                }
            } else {
                $this->errors[] = $this->l('No products found according to the selected filters').'.';
            }
        } else {
            return parent::processAdd();
        }
    }

    public function ajaxProcessCloseInventory()
    {
        $offset = (int)Tools::getValue('offset');
        $limit = (int)Tools::getValue('limit');
        $validateBefore = ((int)Tools::getValue('validateBefore') == 1);

        if ($limit === 0) {
            StockTakeLog::addLog(
                $this->l('Unable to process inventory! Pagination Limit = 0!'),
                3, // severity
                null,
                $this->l('Close Inventory'),
                (int)Tools::getValue('id_inventory'),
                true
            );
            $this->errors[] = $this->l('Unable to process inventory! Please refresh page and try again!');
        }

        if ($offset === 0) {
            if (!$validateBefore) {
                $this->processUpdateInventory(); // Update inventory (title & done fields)
            } else {
                // Update sold quantities
                $this->handleSoldQuantitiesFromOrders((new StockTake((int)Tools::getValue('id_inventory'))));
            }
        }

        $results = array();
        try {
            $this->updateByCollection($offset, $limit, $results, $validateBefore);
        } catch (Exception $e) {// catch any error and save it in logs
            StockTakeLog::addLog(
                WorkshopInv::filterMessageException($e->getMessage()),
                3, // severity
                $e->getCode(),
                $this->l('Close Inventory'),
                (int)Tools::getValue('id_inventory'),
                true
            );
            $this->errors[] = $this->l('Critical Error!');
        }

        // Retrieve errors/warnings if any
        if (count($this->errors) > 0) {
            $results['errors'] = $this->errors;
        }
        if (count($this->warnings) > 0) {
            $results['warnings'] = $this->warnings;
        }
        if (count($this->informations) > 0) {
            $results['informations'] = $this->informations;
        }

        if ((bool)$results['isFinished']) {/* finished */
            /* Check after just validating an inventory if it's worth to make an inventory */
            if ($validateBefore) {// validate inventory is finished
                if (StockTakeProduct::productsNeedInventory(Tools::getValue('id_inventory')) == 0 &&
                    !Configuration::get('WKINVENTORY_RESETSTOCK_NOTINVENT')) {
                    $this->errors[] = $this->l('There are no products needing inventory (No movement of stock found for the products of this inventory)!');
                    $results['errors'] = $this->errors;
                }
            } else {
                $inventory = new StockTake((int)Tools::getValue('id_inventory'));

                // Mark product as inventoried if no error
                StockTakeProduct::updateInventoriedProducts($inventory->id);

                if (StockTakeProduct::inventoryHasErrors($inventory->id) > 0) {
                    $inventory->stock_updated = 0;
                    $inventory->done = 0; // Keep inventory opened to have ability to fix errors
                    $inventory->save();

                    $this->warnings[] = $this->l('Successful update. However, it seems that some products have not been updated!')
                    .' '.$this->l('Edit again this inventory to check and manage these products (marked with red lines).');
                    $results['warnings'] = $this->warnings;
                } else {
                    $inventory->stock_updated = 1;
                    $inventory->save();
                }
            }
        }
        die(json_encode($results));
    }

    public function updateByCollection($offset = false, $limit = false, &$results = null, $validateBefore = false)
    {
        $doneCount = 0;

        if (method_exists('Db', 'disableCache')) {
            Db::getInstance()->disableCache();
        }

        $doneCount += $this->updateProductStock($offset, $limit, $validateBefore);

        $this->clearSmartyCache();

        if ($results !== null) {
            $results['isFinished'] = ($doneCount < $limit);
            $results['doneCount'] = $offset + $doneCount;

            if ($offset === 0) {
                // Compute total count only once
                if (Tools::getValue('id_inventory')) {
                    $inventory = new StockTake((int)Tools::getValue('id_inventory'));
                    $results['totalCount'] = count($inventory->getInventoryProducts(false, false));
                }
            }
            if (!$results['isFinished']) {
                // Since we'll have to POST this array from ajax for the next call, we should care about its size.
                $results['nextPostSize'] = 1024*64; // 64KB more for the rest of the POST query.
                $results['postSizeLimit'] = Tools::getMaxUploadSize();
            }
        }

        if (!$validateBefore && $limit !== 0) {
            $log_message = $this->l('Closing inventory: processing products');
            if ($offset !== false && $limit !== false) {
                $log_message .= ' '.sprintf($this->l('from %s to %s'), $offset, $limit);
            }
            StockTakeLog::addLog(
                $log_message,
                1,
                null,
                $this->l('Inventory'),
                (int)Tools::getValue('id_inventory'),
                true,
                $this->context->employee->id
            );
        }
        if (method_exists('Db', 'enableCache')) {
            Db::getInstance()->enableCache();
        }
    }
    
    /*
    * Update products stocks
    */
    public function updateProductStock($offset = false, $limit = false, $validateBefore = false)
    {
        $ps_asm = $this->canUseAdvancedStockManagementOld || $this->canUseAdvancedStockManagementNew ? true : false;
        // Init Option: Reset stock of unchanged products
        $reset_qty = (int)Configuration::get('WKINVENTORY_RESETSTOCK_NOTINVENT');

        $inventory = new StockTake((int)Tools::getValue('id_inventory'));

        $line_count = 0;
        if (Validate::isLoadedObject($inventory) && !$inventory->stock_updated) {
            $id_shop = $inventory->id_shop;

            // Change shop according to the defined inventory shop
            if (Shop::isFeatureActive() && ShopGroup::getTotalShopGroup() >= 1 && Shop::getTotalShops() > 0) {
                Shop::setContext(Shop::CONTEXT_SHOP, $inventory->id_shop);
            }

            // Get inventory products
            $inventoryProducts = $inventory->getInventoryProducts(false, true, $offset, $limit);

            if (!$validateBefore && $inventory->done) {
                foreach ($inventoryProducts as $inventory_product) {
                    $line_count++;

                    $id_product = (int)$inventory_product->id_product;
                    $stock_difference = (int)$inventory_product->stock_difference;

                    // Skip product if there isn't stock difference or product already updated
                    if ($inventory_product->stock_updated || (!$stock_difference && !$reset_qty)) {
                        continue;
                    }

                    $update_product_inventory = true;
    
                    $delta_quantity = $stock_difference;
                    if ($this->ps172) {// Because later we will synchronize available qty with reserved & physical qties
                        $delta_quantity += (int)$inventory_product->sold_quantity;
                    }

                    $productObj = new Product($id_product, false, $this->context->language->id);

                    if (Validate::isLoadedObject($productObj)) {
                        $id_product_attribute = (int)$inventory_product->id_product_attribute;
                        $id_warehouse = (int)$inventory_product->id_warehouse;

                        $mvt_params = array('id_stock_mvt_reason' => ($delta_quantity > 0 ? 4 : 5));

                        // IF A.S.M
                        if ($ps_asm && !empty($id_warehouse) &&
                            (($this->canUseAdvancedStockManagementOld && StockAvailable::dependsOnStock($id_product)) ||
                            ($this->canUseAdvancedStockManagementNew && $productObj->advanced_stock_management))) {
                            if (class_exists('WorkshopAsm') && class_exists('StoreHouse')) {
                                $warehouse = new StoreHouse($id_warehouse);
                                $stock_manager = new WorkshopAsm();
                                $old_manager = false;
                            } else {
                                $warehouse = new Warehouse($id_warehouse);
                                $stock_manager = StockManagerFactory::getManager();
                                $old_manager = true;
                            }

                            /* Valid warehouse ? */
                            if (Validate::isLoadedObject($warehouse)) {
                                if ($delta_quantity < 0) {/* Quantity is negative */
                                    $original_qty = (int)WorkshopInv::getRealProductStock(
                                        $id_product,
                                        $id_product_attribute,
                                        $id_shop,
                                        $id_warehouse
                                    );
                                    $delta_quantity *= -1;
                                    $removed_product = $stock_manager->removeProduct(
                                        $id_product,
                                        $id_product_attribute,
                                        $warehouse,
                                        (!$old_manager ? $delta_quantity : min($original_qty, $delta_quantity)),
                                        (!$old_manager ? true : 5)// Add Mvt (Decrease stock)
                                    );
                                    if ($old_manager) {
                                        if (count($removed_product) > 0) {
                                            StockAvailable::synchronize($id_product);
                                        } else {
                                            $update_product_inventory = false;

                                            $product_name = $this->getFullProductName($productObj->name, $id_product_attribute);

                                            $physical_quantity_in_stock = (int)$stock_manager->getProductPhysicalQuantities(
                                                $id_product,
                                                $id_product_attribute,
                                                array($id_warehouse),
                                                false
                                            );
                                            $usable_quantity_in_stock = (int)$stock_manager->getProductPhysicalQuantities(
                                                $id_product,
                                                $id_product_attribute,
                                                array($id_warehouse),
                                                true
                                            );
                                            $not_usable_quantity = ($physical_quantity_in_stock - $usable_quantity_in_stock);

                                            if (!$physical_quantity_in_stock) {
                                                $this->warnings[] = sprintf(
                                                    $this->l('It is not possible to remove the stock for the product %s from warehouse %s!')
                                                    .':<br>'.$this->l('The stock is already empty').'!',
                                                    '"'.$product_name.'"',
                                                    $warehouse->name
                                                );
                                            } elseif ($usable_quantity_in_stock < $delta_quantity) {
                                                $this->warnings[] = sprintf(
                                                    $this->l('You don\'t have enough usable quantity for the product %s. Cannot remove %d items out of %d from %s.'),
                                                    '"'.$product_name.'"',
                                                    (int)$delta_quantity,
                                                    (int)$usable_quantity_in_stock,
                                                    $warehouse->name
                                                );
                                            } elseif ($not_usable_quantity < $delta_quantity) {
                                                $this->warnings[] = sprintf(
                                                    $this->l('You don\'t have enough usable quantity for the product %s. Cannot remove %d items out of %d from %s.'),
                                                    '"'.$product_name.'"',
                                                    (int)$delta_quantity,
                                                    (int)$not_usable_quantity,
                                                    $warehouse->name
                                                );
                                            } else {
                                                $this->warnings[] = sprintf(
                                                    $this->l('It is not possible to remove the stock for the product %s from warehouse %s!')
                                                    .' '.$this->l('Therefore no stock was removed.'),
                                                    '"'.$product_name.'"',
                                                    $warehouse->name
                                                );
                                            }
                                        }
                                    } else {
                                        if (count($removed_product) > 0) {
                                            $delta_quantity *= -1; // Restore negative value
                                            WorkshopInv::updateQuantity(
                                                $id_product,
                                                $id_product_attribute,
                                                $delta_quantity,
                                                $id_shop,
                                                true// Add movement
                                            );
                                            WorkshopInv::updatePhysicalProductAvailableQuantity($id_product);
                                        }
                                    }
                                } else {/* Quantity is positive */
                                    // If we start stock with quantities at 0 OR "Reset stock of unchanged products" option is enabled,
                                    // it will be a replacement not an update
                                    if ($inventory->stock_zero || (empty($delta_quantity) && $reset_qty)) {
                                        $physical_quantity_in_stock = (int)WorkshopInv::getRealProductStock(
                                            $id_product,
                                            $id_product_attribute,
                                            $id_shop,
                                            $id_warehouse
                                        );
                                        if ($physical_quantity_in_stock > 0) {
                                            $removed_product = $stock_manager->removeProduct(
                                                $id_product,
                                                $id_product_attribute,
                                                $warehouse,
                                                $physical_quantity_in_stock,
                                                (!$old_manager ? true : 5)// Add Mvt (Decrease stock)
                                            );
                                            if (count($removed_product) > 0) {
                                                if ($old_manager) {
                                                    StockAvailable::synchronize($id_product);
                                                } else {
                                                    $physical_quantity_in_stock *= -1;
                                                    WorkshopInv::updateQuantity(
                                                        $id_product,
                                                        $id_product_attribute,
                                                        $physical_quantity_in_stock,
                                                        $id_shop,
                                                        true// Add movement
                                                    );
                                                    WorkshopInv::updatePhysicalProductAvailableQuantity($id_product);
                                                }
                                            } else {
                                                if (empty($delta_quantity) && $reset_qty) {
                                                    $update_product_inventory = false;
                                                }
                                            }
                                        }
                                    }
                                    if (!empty($delta_quantity)) {
                                        if ($old_manager) {
                                            $added_product = $stock_manager->addProduct(
                                                $id_product,
                                                $id_product_attribute,
                                                $warehouse,
                                                $delta_quantity,
                                                4, // Add Mvt (Increase stock)
                                                $inventory_product->unit_price
                                            );
                                        } else {
                                            $added_product = $stock_manager->addProduct(
                                                $id_product,
                                                $id_product_attribute,
                                                $warehouse,
                                                $delta_quantity,
                                                $inventory_product->unit_price
                                            );
                                        }
                                        if ($added_product) {
                                            if ($old_manager) {
                                                StockAvailable::synchronize($id_product);
                                            } else {
                                                /* Update available quantity */
                                                StockAvailable::updateQuantity(
                                                    $id_product,
                                                    $id_product_attribute,
                                                    $delta_quantity,
                                                    $id_shop,
                                                    true// Add movement
                                                );
                                            }
                                        } else {
                                            $update_product_inventory = false;
                                            $this->warnings[] = sprintf(
                                                $this->l('It is not possible to add stock to the product %s in warehouse %s!'),
                                                '"'.$this->getFullProductName($productObj->name, $id_product_attribute).'"',
                                                $warehouse->name
                                            );
                                        }
                                    }
                                }
                            }
                        } else {/* Warehouse is not defined */
                            $id_stock_available = (int)StockAvailable::getStockAvailableIdByProductId(
                                $id_product,
                                $id_product_attribute,
                                $id_shop
                            );
                            // Set the available quantity
                            if (!$id_stock_available || ($id_stock_available && ($inventory->stock_zero || (empty($delta_quantity) && $reset_qty)))) {
                                try {
                                    $this->setQuantity(
                                        $id_product,
                                        $id_product_attribute,
                                        $delta_quantity,
                                        $id_shop,
                                        $mvt_params
                                    );
                                    $result = true;
                                } catch (Exception $e) {// catch any error and save it in logs
                                    StockTakeLog::addLog(
                                        WorkshopInv::filterMessageException($e->getMessage()),
                                        3, // severity
                                        $e->getCode(),
                                        $this->l('Set Quantity'),
                                        $id_product.'-'.$id_product_attribute,
                                        true
                                    );
                                    $result = false;
                                }
                            } else {// Update the available quantity
                                try {
                                    if ($this->ps172 === true) {
                                        // Add Reason Mvt: Regulation following an inventory of stock
                                        // 4: decrease | 5: increase
                                        $result = StockAvailable::updateQuantity(
                                            $id_product,
                                            $id_product_attribute,
                                            $delta_quantity,
                                            $id_shop,
                                            true, // Add Mvt
                                            $mvt_params
                                        );
                                    } else {
                                        $result = StockAvailable::updateQuantity(
                                            $id_product,
                                            $id_product_attribute,
                                            $delta_quantity,
                                            $id_shop
                                        );
                                    }
                                } catch (Exception $e) {// catch any error and save it in logs
                                    StockTakeLog::addLog(
                                        WorkshopInv::filterMessageException($e->getMessage()),
                                        3, // severity
                                        $e->getCode(),
                                        $this->l('Update Quantity'),
                                        $id_product.'-'.$id_product_attribute,
                                        true
                                    );
                                    $result = false;
                                }
                            }
                            if (!$result) {
                                $update_product_inventory = false;
                            }
                            // PS >= 1.7.2: Synchronize physical, available and reserved quantities
                            if ($result && $this->ps172 === true) {
                                WorkshopInv::updatePhysicalProductAvailableQuantity($id_product, $id_shop);
                            }
                            /*
                            * !Important: PS 1.7 | If warehouse ID not indicated
                            *  but product uses Advanced stock management =>
                            *  Force quantities synchronization anyway to have the same warehouses qties (according to PS physical qty)
                            *  see ActionUpdateQuantity hook for more information
                            */
                            if ($this->canUseAdvancedStockManagementNew && $productObj->advanced_stock_management) {
                                try {
                                    (new WorkshopAsm())->synchronize($id_product, $id_product_attribute, null, array(), false);
                                } catch (Exception $e) {// catch any error and save it in logs
                                    StockTakeLog::addLog(
                                        WorkshopInv::filterMessageException($e->getMessage()),
                                        3, // severity
                                        $e->getCode(),
                                        $this->l('Sync Stock'),
                                        $id_product.'-'.$id_product_attribute,
                                        true
                                    );
                                }
                            }
                        }

                        // Is there an error
                        $inventory_product->has_error = !$update_product_inventory ? 1 : 0;
                        try {
                            $inventory_product->save();
                        } catch (Exception $e) {// catch any error and save it in logs
                            StockTakeLog::addLog(
                                WorkshopInv::filterMessageException($e->getMessage()),
                                3, // severity
                                $e->getCode(),
                                $this->l('Update Product Inventory'),
                                $id_product.'-'.$id_product_attribute,
                                true
                            );
                        }
                    }
                } // End loop
            }
        }
        return $line_count;
    }

    private function setQuantity(
        $id_product,
        $id_product_attribute,
        $delta_quantity,
        $id_shop,
        $mvt_params
    ) {
        // For PS >= 1.7.2: initiate stockmanager adapter
        if ($this->ps172 === true && class_exists('PrestaShop\PrestaShop\Adapter\StockManager')) {// PS >= 1.7.2
            StockAvailable::setQuantity(
                $id_product,
                $id_product_attribute,
                $delta_quantity,
                $id_shop,
                false// don't save by default movement and let us to save mine
            );
            if ($delta_quantity != 0) {// Save mvt with reason
                (new PrestaShop\PrestaShop\Core\Stock\StockManager())->saveMovement(
                    $id_product,
                    $id_product_attribute,
                    $delta_quantity,
                    $mvt_params
                );
            }
        } else {// PS < 1.7.2
            StockAvailable::setQuantity(
                $id_product,
                $id_product_attribute,
                $delta_quantity,
                $id_shop
            );
        }
    }

    /* Get Full Product Name */
    public function getFullProductName($product_name, $id_product_attribute)
    {
        if (!empty($id_product_attribute)) {
            $product_name .= ' ('.WorkshopInv::getAttributesCombinationNames($id_product_attribute).' #'.$id_product_attribute.')';
        }
        return $product_name;
    }

    public function clearSmartyCache()
    {
        Tools::enableCache();
        Tools::clearCache($this->context->smarty);
        Tools::restoreCacheSettings();
    }

    public function ajaxProcessUpdateOrdersQuantity()
    {
        $response = array();
        $inventory = new StockTake(Tools::getValue('id_inventory'));
        if (Validate::isLoadedObject($inventory)) {
            $response = $this->handleSoldQuantitiesFromOrders($inventory);
        } else {
            $response['success'] = false;
            $response['message'] = $this->l('Unable to load this inventory');
        }
        WorkshopInv::renderJSON($response);
    }

    public function handleSoldQuantitiesFromOrders(StockTake $inventory)
    {
        $ps_asm = $this->canUseAdvancedStockManagementOld || $this->canUseAdvancedStockManagementNew ? true : false;
        $orders_statuses_not_impact_inventory = Configuration::get('WKINVENTORY_ORDER_STATES');
        if (empty($orders_statuses_not_impact_inventory)) {
            return array(
                'success' => false,
                'message' => $this->l('Orders statuses are not set in config page.')
            );
        }

        $query = new DbQuery();
        $query->select('ip.`id_inventory_product`, SUM(od.`product_quantity` - od.`product_quantity_refunded`) `product_quantity`');
        $query->from(StockTakeProduct::$definition['table'], 'ip');
        $query->innerJoin(
            'order_detail',
            'od',
            'od.`product_id` = ip.`id_product` AND 
             od.`product_attribute_id` = ip.`id_product_attribute`
             '.($ps_asm ? 'AND od.`id_warehouse` = ip.`id_warehouse`' : '')
        );
        /*
        * Order statuses that don't impact the stock (statuses for which the stock is not reduced).
        * By default it is set with the following statuses "Cancel", "Payment error" and "Refund" stocks.
        */
        $query->where(
            'od.`id_order` IN (
                SELECT `id_order` 
                FROM `'._DB_PREFIX_.'orders` 
                WHERE `date_add` > \''.$inventory->date_add.'\' AND 
                `current_state` NOT IN ('.$orders_statuses_not_impact_inventory.')
            )'
        );
        $query->where('ip.`id_inventory` = '.(int)$inventory->id);
        $query->where('od.`id_shop` = '.(int)$inventory->id_shop);
        $query->groupBy('ip.`id_inventory_product`');

        $results = Db::getInstance()->executeS($query);
        if (count($results)) {
            $updated_ip = 0;
            $inventory_products = array();

            foreach ($results as $row) {
                $inv_p = new StockTakeProduct($row['id_inventory_product']);
                if (!Validate::isLoadedObject($inv_p) || $inv_p->stock_updated) {
                    continue;
                }
                // Set before real quantities to their original values (without sold quantities)
                $inv_p->real_quantity = $inv_p->real_quantity + $inv_p->sold_quantity;
                $inv_p->sold_quantity = (int)$row['product_quantity'];
                // Recalculate new real quantities values
                $inv_p->real_quantity = $inv_p->real_quantity - $inv_p->sold_quantity;

                if ($inv_p->save()) {// Save
                    ++$updated_ip;
                    $inventory_products[] = array(
                        'id' => $inv_p->id,
                        'sold_quantity' => $inv_p->sold_quantity,
                        'real_quantity' => $inv_p->real_quantity,
                        'date_upd' => $inv_p->date_upd,
                    );
                }
            }
            $response = array(
                'success' => true,
                'message' => sprintf($this->l('%d product(s) updated.'), $updated_ip),
                'WKInventoryProducts' => $inventory_products,
            );
        } else {
            $response = array(
                'success' => false,
                'message' => $this->l('No orders since the beginning of the inventory').'.',
            );
        }
        return $response;
    }

    public function initModal()
    {
        parent::initModal();

        $modal_content = $this->context->smarty->fetch(
            _PS_MODULE_DIR_.$this->module->name.'/views/templates/admin/modal_update_progress.tpl'
        );
        $this->modals[] = array(
            'modal_id' => 'importProgress',
            'modal_class' => 'modal-md',
            'modal_title' => $this->l('Updating your shop...'),
            'modal_content' => html_entity_decode($modal_content)
        );
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->addJqueryPlugin(array('typewatch'));
        $this->addJqueryUI('ui.dialog');
        /* Enable modal on PS 1.5.6.x */
        if ($this->module->is_before_16) {
            $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/bootstrap.min.js');
            $this->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/modal.css');
        }
    }

    // For PS 1.5
    public function initToolbar()
    {
        parent::initToolbar();

        if ($this->module->is_before_16) {
            $this->toolbar_btn['back'] = array(
                'href' => $this->context->link->getAdminLink('AdminStocktakedash'),
                'desc' => $this->l('Dashboard')
            );
            if ($this->display == 'edit' || $this->display == 'add') {
                unset($this->toolbar_btn['save']);
                $this->toolbar_btn['duplicate'] = array(
                    'href' => $this->context->link->getAdminLink('AdminStocktake'),
                    'desc' => $this->l('Back to list')
                );
            }
            // Edit Inventory Mode
            if ($this->display == 'edit' && Tools::getIsset('id_inventory')) {
                $id_inventory = (int)Tools::getValue('id_inventory');
                $inventory = new StockTake($id_inventory);

                // Modify / End inventory button
                if ($this->isSupervisor($inventory->id_employee)) {
                    $this->toolbar_btn['edit'] = array(
                        'href' => 'javascript:void(0);',
                        'js' => 'loadInventoryEditForm();',
                        'desc' => $this->l('Edit', null, null, false).(!$inventory->done ? ' / '.$this->l('Finish', null, null, false) : '').' '.$this->l('Inventory', null, null, false),
                    );
                }
                // How to generate PDF report
                if (!$inventory->done && !$inventory->stock_updated) {
                    $this->toolbar_btn['refresh-index'] = array(
                        'href' => 'javascript:void(0);',
                        'js' => 'updateSoldQuantities();',
                        'desc' => $this->l('Update sold quantities', null, null, false),
                    );
                }
                if (Configuration::get('WKINVENTORY_PDFREPORT_MODE') == 'normal') {
                    $this->toolbar_btn['export'] = array(
                        'href' => self::$currentIndex.'&exportwkinventory_product&token='.$this->token.'&updatewkinventory&id_inventory='.$id_inventory,
                        'desc' => $this->l('PDF Report', null, null, false),
                    );
                } else {
                    $this->toolbar_btn['export'] = array(
                        'href' => $this->context->link->getAdminLink('AdminStocktakegetpdf').'&id_inventory='.$id_inventory,
                        'desc' => $this->l('PDF Report', null, null, false),
                        'target' => 'blank'
                    );
                }
                $this->toolbar_btn['export-csv-details'] = array(
                    'href' => self::$currentIndex.'&exportCSV&token='.$this->token.'&updatewkinventory&id_inventory='.$id_inventory,
                    'desc' => $this->l('Export CSV', null, null, false),
                );
            }
        }
    }

    public function initPageHeaderToolbar()
    {
        if (empty($this->display) || $this->display == 'list') {
            $this->page_header_toolbar_btn['new_inventory'] = array(
                'href' => self::$currentIndex.'&addwkinventory&token='.$this->token,
                'desc' => $this->l('Add Inventory', null, null, false),
                'icon' => 'process-icon-new',
            );
        }
        // Edit Inventory Mode
        if ($this->display == 'edit' && Tools::getIsset('id_inventory')) {
            $id_inventory = (int)Tools::getValue('id_inventory');
            $inventory = new StockTake($id_inventory);

            // Modify / End inventory button
            if ($this->isSupervisor($inventory->id_employee)) {
                $this->page_header_toolbar_btn['update_end_inv'] = array(
                    'href' => 'javascript:void(0);',
                    'js' => 'loadInventoryEditForm();',
                    'desc' => $this->l('Edit', null, null, false).(!$inventory->done ? ' / '.$this->l('Finish', null, null, false) : '').' '.$this->l('Inventory', null, null, false),
                    'icon' => 'process-icon-'.(!$inventory->done ? 'off' : 'edit')
                );
            }
            if (!$inventory->done && !$inventory->stock_updated) {
                $this->page_header_toolbar_btn['update_sold_qties'] = array(
                    'href' => 'javascript:void(0);',
                    'js' => 'updateSoldQuantities();',
                    'desc' => $this->l('Update sold quantities', null, null, false),
                    'icon' => 'process-icon-cart'
                );
            }
            // How to generate PDF report
            if (Configuration::get('WKINVENTORY_PDFREPORT_MODE') == 'normal') {
                $this->page_header_toolbar_btn['export_pdf'] = array(
                    'href' => self::$currentIndex.'&exportwkinventory_product&token='.$this->token.'&updatewkinventory&id_inventory='.$id_inventory,
                    'desc' => $this->l('PDF Report', null, null, false),
                    'icon' => 'process-icon-download'
                );
            } else {
                $this->page_header_toolbar_btn['export_pdf'] = array(
                    'href' => $this->context->link->getAdminLink('AdminStocktakegetpdf').'&id_inventory='.$id_inventory,
                    'desc' => $this->l('PDF Report', null, null, false),
                    'icon' => 'process-icon-download',
                    'target' => 'blank'
                );
            }
            $this->page_header_toolbar_btn['export_csv'] = array(
                'href' => self::$currentIndex.'&exportCSV&token='.$this->token.'&updatewkinventory&id_inventory='.$id_inventory,
                'desc' => $this->l('Export CSV', null, null, false),
                'icon' => 'process-icon-export'
            );
        }
        if ($this->display == 'edit' || $this->display == 'add') {
            $this->page_header_toolbar_btn['back_to_list'] = array(
                'href' => $this->context->link->getAdminLink('AdminStocktake'),
                'desc' => $this->l('Back to list', null, null, false),
                'icon' => 'process-icon-reset'
            );
        }
        $this->page_header_toolbar_btn['back_to_dashboard'] = array(
            'href' => $this->context->link->getAdminLink('AdminStocktakedash'),
            'desc' => $this->l('Dashboard', null, null, false),
            'icon' => 'process-icon-back'
        );
        parent::initPageHeaderToolbar();
    }

    /*
    * Method Translation Override For PS 1.7
    */
    protected function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        if (method_exists('Context', 'getTranslator')) {
            $this->translator = Context::getContext()->getTranslator();
            $translated = $this->translator->trans($string);
    
            if ($translated !== $string) {
                return $translated;
            }
        }
        if ($class === null || $class == 'AdminTab') {
            $class = Tools::substr(get_class($this), 0, -10);
        } elseif (Tools::strtolower(Tools::substr($class, -10)) == 'controller') {
            $class = Tools::substr($class, 0, -10);
        }
        return Translate::getAdminTranslation($string, $class, $addslashes, $htmlentities);
    }
}
