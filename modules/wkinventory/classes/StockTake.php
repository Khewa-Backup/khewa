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

class StockTake extends ObjectModel
{
    /**
     * Name
     *
     * @var string
     */
    public $name;

    /**
     * Employee.
     *
     * @var int
     */
    public $id_employee;

    /**
     * Supplier.
     *
     * @var int
     */
    public $id_supplier;

    /**
     * Shop.
     *
     * @var int
     */
    public $id_shop;

    /**
     * Categories ids.
     *
     * @varchar
     */
    public $category_ids;

    /**
     * Manufacturer ids.
     *
     * @varchar
     */
    public $manufacturer_ids;

    /**
     * Warehouse.
     *
     * @var int
     */
    public $id_warehouse;

    /**
     * If Inventory is closed.
     *
     * @var bool
     */
    public $done;

    /**
     * If Inventory is closed && stock updated.
     *
     * @var bool
     */
    public $stock_updated;

    /**
     * Employee.
     *
     * @var int
     */
    public $is_empty;

    /**
     * Date when added.
     *
     * @var date
     */
    public $date_add;

    /**
     * Date when updated.
     *
     * @var date
     */
    public $date_upd;

    /**
     * @var int
     */
    public $stock_zero;

    /**
     * Free HTML (especialy for PDF render).
     *
     * @var int
     */
    public $free_html;
    public $stock_valuation;
    public $inventory_count;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'wkinventory',
        'primary' => 'id_inventory',
        'multilang' => false,
        'fields' => array(
            'name' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required' => true,
                'size' => 255
            ),
            'id_employee' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'category_ids' => array('type' => self::TYPE_STRING),
            'id_supplier' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'manufacturer_ids' => array('type' => self::TYPE_STRING),
            'id_warehouse' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_shop' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'done' => array('type' => self::TYPE_BOOL),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'stock_updated' => array('type' => self::TYPE_BOOL),
            'is_empty' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'stock_zero' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
        ),
    );

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id, $id_lang, $id_shop);

        if (!$this->id_employee) {
            $this->id_employee = Context::getContext()->employee->id;
        }
    }

    /**
     * @see ObjectModel::add()
     */
    public function add($autodate = true, $null_values = false)
    {
        $is_empty = (int)Tools::getValue('is_empty');

        if (!$is_empty) {
            if (Tools::getIsset('manufacturer_ids') && Tools::getValue('manufacturer_ids')) {
                $this->manufacturer_ids = pSQL(implode(',', array_map('intval', Tools::getValue('manufacturer_ids'))));
            }
            if (Tools::getIsset('categoryBox') && Tools::getValue('categoryBox')) {
                $this->category_ids = pSQL(implode(',', array_map('intval', Tools::getValue('categoryBox'))));
            }
        } else {
            $this->category_ids = '';
            $this->id_supplier = 0;
            $this->manufacturer_ids = '';
        }
        return parent::add($autodate, $null_values);
    }

    /**
     * @see ObjectModel::update()
     */
    public function update($null_values = false)
    {
        $this->date_upd = date('Y-m-d H:i:s');
        return parent::update($null_values);
    }

    public function getInventoryProducts($stock_updated = false, $full = true, $offset = null, $limit = null)
    {
        $data = array();
        $primary = StockTakeProduct::$definition['primary'];

        $query = new DbQuery();
        $query->select($primary);
        $query->from(StockTakeProduct::$definition['table'], 'i');
        $query->leftJoin('product', 'p', 'p.`id_product` = i.`id_product`');
        $query->join(Shop::addSqlAssociation('product', 'p'));
        $query->where('`id_inventory` = '.(int)$this->id);
        if (!$stock_updated) {
            $query->where('`stock_updated` <> 1');
        }
        $query->orderBy('`id_inventory_product` DESC');
        if (!is_null($offset) && $offset !== false && $limit) {
            $query->limit((int)$limit, (int)$offset);
        }

        if ($full) {
            foreach (Db::getInstance()->ExecuteS($query) as $res) {
                $data[] = new StockTakeProduct((int)$res[$primary]);
            }
        } else {
            $data = Db::getInstance()->ExecuteS($query);
        }
        return $data;
    }

    public static function getInventories()
    {
        $query = new DbQuery();
        $query->select(self::$definition['primary']);
        $query->from(self::$definition['table'], 'i');
        $query->where('`stock_updated` <> 1 AND `done` <> 1');

        return Db::getInstance()->ExecuteS($query);
    }

    public function getInventoryProductId($product_id, $product_attribute_id, $product_warehouse_id = null)
    {
        $query = new DbQuery();
        $query->select('`'.StockTakeProduct::$definition['primary'].'`');
        $query->from(StockTakeProduct::$definition['table']);
        $query->where('`id_inventory` = '.(int)$this->id);
        $query->where('`id_product` = '.(int)$product_id);
        $query->where('`id_product_attribute` = '.(int)$product_attribute_id);
        if (!is_null($product_warehouse_id)) {
            $query->where('`id_warehouse` = '.(int)$product_warehouse_id);
        }
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
    }

    public function getInventoryProductCount($inventories_products_ids)
    {
        if (count($inventories_products_ids)) {
            $query = new DbQuery();
            $query->select('COUNT(`'.StockTakeProduct::$definition['primary'].'`)');
            $query->from(StockTakeProduct::$definition['table']);
            $query->where('`'.StockTakeProduct::$definition['primary'].'` IN ('.implode(',', array_map('intval', $inventories_products_ids)).')');

            return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
        }
        return 0;
    }

    public function getStockValue($inventories_products_ids)
    {
        if (count($inventories_products_ids)) {
            $query = new DbQuery();
            $query->select('SUM(`real_quantity` * `unit_price`)');
            $query->from(StockTakeProduct::$definition['table']);
            $query->where('`'.StockTakeProduct::$definition['primary'].'` IN ('.implode(',', array_map('intval', $inventories_products_ids)).')');

            return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
        }
        return 0;
    }
}
