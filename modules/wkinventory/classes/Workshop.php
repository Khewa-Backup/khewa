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

class WorkshopInv
{
    public static function implodeKey($glue = '&', $pieces = array())
    {
        $attributes_str = array();
        foreach ($pieces as $attribute => $value) {
            $attributes_str[] = $attribute.'='.$value;
        }

        return implode($glue, $attributes_str);
    }

    public static function getShopEmployeeName($id)
    {
        $fullname = '';
        $employee = new Employee((int)$id);

        if (Validate::isLoadedObject($employee)) {
            $fullname = Tools::ucfirst(Tools::substr($employee->firstname, 0, 1));
            $fullname .= '. '.Tools::ucfirst($employee->lastname);
        }
        return $fullname;
    }

    public static function getBackofficeProductUrl($id_product)
    {
        $vars_queries = array(
            'id_product' => $id_product,
            'updateproduct' => 1
        );
        return (
            !version_compare(_PS_VERSION_, '1.7', '>=') ?
            Context::getContext()->link->getAdminLink('AdminProducts').'&'.self::implodeKey('&', $vars_queries) :
            Context::getContext()->link->getAdminLink('AdminProducts', true, $vars_queries)
        );
    }

    public static function getProductImage($id_product, $id_lang = null)
    {
        $thumb = '';
        if (empty($id_lang)) {
            $id_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        }

        $image = Product::getCover((int)$id_product);

        // Check if image exists physically
        if (!empty($image['id_image'])) {
            $imgObj = new Image($image['id_image'], $id_lang);
            if (Validate::isLoadedObject($imgObj)) {
                $image_path = _PS_PROD_IMG_DIR_.$imgObj->getImgPath().'.'.$imgObj->image_format;
                if (file_exists($image_path)) { // Verifying, whether the file with the image exists
                    $product = new Product($id_product, false, $id_lang);
                    if (Validate::isLoadedObject($product)) {
                        $thumb = Context::getContext()->link->getImageLink(
                            $product->link_rewrite,
                            $imgObj->id_image,
                            (version_compare(_PS_VERSION_, '1.7', '<') ? ImageType::getFormatedName('small') : ImageType::getFormattedName('small'))
                        );
                    }
                }
            }
        }
        return $thumb;
    }

    public static function renderJSON(array $data)
    {
        header('Content-Type: application/json');
        die(Tools::jsonEncode($data));
    }

    public static function getAttributesCombinationNames($id_product_attribute)
    {
        // Collect attributes name
        $attributes_name = '';
        if (!empty($id_product_attribute)) {
            $combination = new Combination($id_product_attribute);
            $attributes = $combination->getAttributesName((int)Context::getContext()->language->id);
            foreach ($attributes as $attribute) {
                $attributes_name .= $attribute['name'].' - ';
            }
            $attributes_name = rtrim($attributes_name, ' - ');
        }
        return $attributes_name;
    }

    /*
    * Save inventory products
    */
    public static function createInventoryProduct($row, $id_inventory, $id_shop, $qty = 0)
    {
        $id_lang = Context::getContext()->language->id;
        $asm = self::canUseAdvancedStockManagementOld() || self::canUseAdvancedStockManagementNew() ? true : false;
        $id_product = (int)$row['id_product'];
        $id_product_attribute = (int)$row['id_product_attribute'];
        $id_warehouse = ($asm && !empty($row['id_warehouse']) ? $row['id_warehouse'] : 0);

        $product = new Product($id_product, false, $id_lang, $id_shop);

        // Supplier infos
        $product_infos = Supplier::getProductInformationsBySupplier(
            $product->id_supplier,
            $id_product,
            $id_product_attribute
        );
        // Get wholesale price
        $unit_price = empty($product_infos) ? 0 : $product_infos['product_supplier_price_te'];
        if (!($unit_price > 0)) {
            if ($id_product_attribute) {
                $combination = new Combination($id_product_attribute);
                $unit_price = $combination->wholesale_price;
                if (!($unit_price > 0)) {
                    $unit_price = $product->wholesale_price;
                }
            } else {
                $unit_price = $product->wholesale_price;
            }
        }
        if ($unit_price == 0) {
            // Be carefull, if price = 0, that may cause error in calculateWA function
            $unit_price = Product::getPriceStatic($id_product, false, 0, 6, null, false, false);
        }
        $unit_price = round((float)$unit_price, 6);

        $inventory_product = new StockTakeProduct();
        $inventory_product->id_inventory = (int)$id_inventory;
        $inventory_product->id_product = (int)$id_product;
        $inventory_product->id_product_attribute = (int)$id_product_attribute;
        $inventory_product->id_warehouse = (int)$id_warehouse;

        if (Tools::getIsset('stock_zero') && Tools::getValue('stock_zero')) {
            $inventory_product->shop_quantity = 0;
        } else {
            // For a given product/combination, gets its stock available
            $inventory_product->shop_quantity = (int)self::getRealProductStock(
                $id_product,
                $id_product_attribute,
                $id_shop,
                $id_warehouse
            );
        }

        $inventory_product->real_quantity = $inventory_product->shop_quantity + (int)$qty;
        $inventory_product->unit_price = (float)$unit_price;
        $inventory_product->id_currency = (int)(
            empty($product_infos) ? Configuration::get('PS_CURRENCY_DEFAULT') : $product_infos['id_currency']
        );

        if ($inventory_product->save()) {
            return true;
        }
        return false;
    }

    public static function getRealProductStock(
        $id_product,
        $id_product_attribute,
        $id_shop = null,
        $id_warehouse = null
    ) {
        if ($id_product_attribute === null) {
            $id_product_attribute = 0;
        }
        $canUseAdvancedStockManagementOld = self::canUseAdvancedStockManagementOld();
        $canUseAdvancedStockManagementNew = self::canUseAdvancedStockManagementNew();

        if (($canUseAdvancedStockManagementOld || $canUseAdvancedStockManagementNew)) {
            if ($canUseAdvancedStockManagementOld && StockAvailable::dependsOnStock($id_product)) {
                return (int)(new StockManager())->getProductPhysicalQuantities(
                    $id_product,
                    $id_product_attribute,
                    $id_warehouse
                );
            } else {
                $product = new Product($id_product, false);
                if ($canUseAdvancedStockManagementNew && class_exists('WorkshopAsm') &&
                    $product->advanced_stock_management) {/* From Wk Warehouses management Module */
                    return (int)WorkshopAsm::getProductPhysicalQuantities($id_product, $id_product_attribute, $id_warehouse);
                } else {
                    return (int)StockAvailable::getQuantityAvailableByProduct($id_product, $id_product_attribute, $id_shop);
                }
            }
        } else {
            return (int)StockAvailable::getQuantityAvailableByProduct($id_product, $id_product_attribute, $id_shop);
        }
    }

    /*
     * Search products by terms (Autocomplete Search)
    */
    public static function searchByTerms($query)
    {
        $query = Tools::strtolower(trim($query));
        $id_lang = (int)Context::getContext()->language->id;

        $sql = new DbQuery();
        $sql->select(
            'p.`id_product`,
             pl.`name`,
             p.`ean13`,
             p.`upc`,
             p.`active`,
             p.`reference`'
        );
        $sql->from('product', 'p');
        $sql->join(Shop::addSqlAssociation('product', 'p'));
        $sql->leftJoin(
            'product_lang',
            'pl',
            'p.`id_product` = pl.`id_product` AND 
             pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl')
        );
        $where = 'pl.`name` LIKE \'%'.pSQL($query).'%\'
            OR LOWER(p.`ean13`) LIKE \'%'.pSQL($query).'%\'
            OR LOWER(p.`upc`) LIKE \'%'.pSQL($query).'%\'
            OR LOWER(p.`reference`) LIKE \'%'.pSQL($query).'%\'
            OR LOWER(p.`supplier_reference`) LIKE \'%'.pSQL($query).'%\'
            OR EXISTS(
                SELECT * FROM `'._DB_PREFIX_.'product_supplier` sp 
                WHERE sp.`id_product` = p.`id_product` AND `product_supplier_reference` LIKE \'%'.pSQL($query).'%\'
            )';
        if (Combination::isFeatureActive()) {
            $sql->select('pa.`id_product_attribute`');
            $sql->leftJoin(
                'product_attribute',
                'pa',
                'p.`id_product` = pa.`id_product`'
            );
            $where .= ' OR LOWER(pa.`reference`) LIKE \'%'.pSQL($query).'%\' OR
                    LOWER(pa.`supplier_reference`) LIKE \'%'.pSQL($query).'%\' OR
                    LOWER(pa.`ean13`) LIKE \'%'.pSQL($query).'%\' OR
                    LOWER(pa.`upc`) LIKE \'%'.pSQL($query).'%\' ';
        }
        $sql->join(Product::sqlStock('p', 0));
        $sql->where('('.$where.')');
        $sql->orderBy('pl.`name` ASC');
        //echo('<pre>'.$sql->build()); exit();// return sql query
        $result = Db::getInstance()->executeS($sql);

        if (!$result) {
            return false;
        }
        return $result;
    }

    public static function getExistingBarcodes($type = 'ean13')
    {
        $query = new DbQuery();
        $select = sprintf('DISTINCT `%s`', pSQL($type));
        $sql = $query->select($select)
            ->from('product')
            ->build();

        $products_barcodes = array_map('current', Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql));
        $sql = str_replace('product', 'product_attribute', $sql);
        $products_attributes_barcodes = array_map('current', Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql));

        $barcodes = array_merge($products_barcodes, $products_attributes_barcodes);
        $barcodes = array_unique($barcodes);

        return array_filter($barcodes);
    }

    public static function getWarehousesLocations($id_warehouse = null, $ids = array(), $only_location = false)
    {
        $warehouses_locations = array();
        $locations = Db::getInstance()->executeS(
            'SELECT DISTINCT `location`, id_warehouse_product_location
             FROM '._DB_PREFIX_.'warehouse_product_location
             WHERE 1 '.
             (!empty($id_warehouse) ? ' AND id_warehouse = '.(int)$id_warehouse : '').
             (!empty($ids) ? ' AND id_warehouse_product_location IN ('.implode(',', array_map('intval', $ids)).')' : '')
        );
        foreach ($locations as $location) {
            if (empty($location['location'])) {
                continue;
            }
            if ($only_location) {
                $warehouses_locations[] = "'".addslashes($location['location'])."'";
            } else {
                $warehouses_locations[] = array(
                    'id' => (int)$location['id_warehouse_product_location'],
                    'name' => $location['location'],
                );
            }
        }
        return $warehouses_locations;
    }

    /**
     * For a given id_product and id_product_attribute sets the quantity available
     * This function is the same as copied from \src\Core\Stock\StockManager.php
     * We copied and execute this function from here to avoid executing the "actionUpdateQuantity" hook
     * @return bool
     */
    public static function updateQuantity(
        $id_product,
        $id_product_attribute,
        $delta_quantity,
        $id_shop = null,
        $add_movement = true,
        $params = array()
    ) {
        if (!Validate::isUnsignedId($id_product)) {
            return false;
        }
        $product = new Product((int)$id_product);
        if (!Validate::isLoadedObject($product)) {
            return false;
        }

        // @TODO We should call the needed classes with the Symfony dependency injection instead of the Homemade Service Locator
        $serviceLocator = new PrestaShop\PrestaShop\Adapter\ServiceLocator();
        $stockManager = $serviceLocator::get('\\PrestaShop\\PrestaShop\\Core\\Stock\\StockManager');
        $packItemsManager = $serviceLocator::get('\\PrestaShop\\PrestaShop\\Adapter\\Product\\PackItemsManager');
        $cacheManager = $serviceLocator::get('\\PrestaShop\\PrestaShop\\Adapter\\CacheManager');

        $availableStockManager = $serviceLocator::get('\\PrestaShop\\PrestaShop\\Adapter\\StockManager');
        $stockAvailable = $availableStockManager->getStockAvailableByProduct($product, $id_product_attribute, $id_shop);

        // Update quantity of the pack products
        if ($packItemsManager->isPack($product)) {
            // The product is a pack
            $stockManager->updatePackQuantity($product, $stockAvailable, $delta_quantity, $id_shop);
        } else {
            // The product is not a pack
            $stockAvailable->quantity = $stockAvailable->quantity + $delta_quantity;
            $stockAvailable->update();

            // Decrease case only: the stock of linked packs should be decreased too.
            if ($delta_quantity < 0) {
                // The product is not a pack, but the product combination is part of a pack (use of isPacked, not isPack)
                if ($packItemsManager->isPacked($product, $id_product_attribute)) {
                    $stockManager->updatePacksQuantityContainingProduct($product, $id_product_attribute, $stockAvailable, $id_shop);
                }
            }
        }

        // Prepare movement and save it
        if (true === $add_movement && 0 != $delta_quantity) {
            $stockManager->saveMovement($product->id, $id_product_attribute, $delta_quantity, $params);
        }
        $cacheManager->clean('StockAvailable::getQuantityAvailableByProduct_'.(int)$product->id.'*');
    }

    public static function updatePhysicalProductAvailableQuantity($id_product, $id_shop = null)
    {
        if (empty($id_shop)) {
            $id_shop = Context::getContext()->shop->id;
        }
        if (class_exists('PrestaShop\PrestaShop\Adapter\StockManager')) {
            (new PrestaShop\PrestaShop\Adapter\StockManager())->updatePhysicalProductQuantity(
                (int)$id_shop,
                (int)Configuration::get('PS_OS_ERROR'),
                (int)Configuration::get('PS_OS_CANCELED'),
                (int)$id_product
            );
        }
    }

    public static function setAdvancedStockManagement($id_product, $value)
    {
        Db::getInstance()->execute(
            'UPDATE `'._DB_PREFIX_.'product_shop`
             SET `advanced_stock_management` = '.(int)$value.'
             WHERE id_product = '.(int)$id_product.Shop::addSqlRestriction()
        );
        Db::getInstance()->execute(
            'UPDATE `'._DB_PREFIX_.'product`
             SET `advanced_stock_management` = '.(int)$value.'
             WHERE `id_product` = '.(int)$id_product
        );
    }

    public static function canUseAdvancedStockManagementOld()
    {
        if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') && !version_compare(_PS_VERSION_, '1.7', '>=')) {
            return true;
        }
        return false;
    }

    public static function canUseAdvancedStockManagementNew()
    {
        $use = false;
        if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') && Module::isInstalled('wkwarehouses') &&
            version_compare(_PS_VERSION_, '1.7.2', '>=')) {
            $module = Module::getInstanceByName('wkwarehouses');
            if ($module->active) {
                $use = true;
            }
        }
        return $use;
    }

    public static function getWarehouseClass($id)
    {
        return (class_exists('StoreHouse') ? new StoreHouse($id, Context::getContext()->language->id) : new Warehouse($id));
    }

    public static function getWarehouseClassName()
    {
        return (class_exists('StoreHouse') ? 'StoreHouse' : 'Warehouse');
    }

    /**
     * Get calculated Control digit.
     *
     * @param string barcode EAN13 / UPC code
     * @param int weighting 1 for EAN, 3 for UPC
     *
     * @return int control digit
     */
    public static function getControlDigit($barcode, $weighting)
    {
        $sum = 0;
        $i = 0;
        $barcode_digits = str_split($barcode);
        $l = count($barcode_digits);

        for ($i; $i < $l; ++$i) {
            $value = (int)$barcode_digits[$i];
            $sum += $value * $weighting;
            $weighting = ($weighting === 3) ? 1 : 3;
        }
        $mod = $sum % 10;
        return ($mod === 0) ? 0 : 10 - $mod;
    }

    public static function filterMessageException($message)
    {
        return str_replace(array('<br>', '<br/>', '<br />'), ' - ', $message);
    }
}
