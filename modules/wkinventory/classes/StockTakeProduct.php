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

class StockTakeProduct extends ObjectModel
{
    /**
     * @var int StockTakeProduct
     */
    public $id;

    /**
     * @var int Inventory
     */
    public $id_inventory;

    /**
     * @var int Product
     */
    public $id_product;

    /**
     * @var int ProductAttribute
     */
    public $id_product_attribute;

    /**
     * Warehouse.
     *
     * @var int
     */
    public $id_warehouse;

    /**
     * @var string Product reference
     */
    public $reference;

    /**
     * @var string Product ean13
     */
    public $ean13;

    /**
     * @var string Product upc
     */
    public $upc;

    /**
     * @var date Date when updated
     */
    public $date_upd;

    /**
     * @var int Employee
     */
    public $id_employee;

    /**
     * @var int shop quantity in stock
     */
    public $shop_quantity;

    /**
     * @var int Quantity actualy in stock
     */
    public $real_quantity;

    /**
     * @var int Quantity sold since the inventory starts
     */
    public $sold_quantity;

    /**
     * @var float Product price
     */
    public $unit_price;

    /**
     * @var float Product price * quantity
     */
    public $total_price;

    /**
     * @var int ERP stock reserved
     */
    public $reserved_stock;

    /**
     * @var int Difference between real and shop quantity
     */
    public $stock_difference;

    /**
     * @var float Difference cost
     */
    public $stock_difference_cost;

    /**
     * @var bool Whenever stock is updated or not
     */
    public $stock_updated;

    /**
     * @var int has error after update
     */
    public $has_error;
    
    /**
     * @var int Currency
     */
    public $id_currency;

    public static $definition = array(
        'table' => 'wkinventory_product',
        'primary' => 'id_inventory_product',
        'multilang' => false,
        'fields' => array(
            'id_inventory' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_product' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_product_attribute' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_warehouse' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_employee' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'shop_quantity' => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),
            'real_quantity' => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),
            'sold_quantity' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'unit_price' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'id_currency' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'has_error' => array('type' => self::TYPE_BOOL),
            'stock_updated' => array('type' => self::TYPE_BOOL),
        )
    );

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id, $id_lang, $id_shop);
        $this->id_employee = Context::getContext()->employee->id;
        $this->stock_difference = (int)$this->real_quantity - ((int)$this->shop_quantity - (int)$this->sold_quantity);
        $this->total_price = (int)$this->real_quantity * (float)$this->unit_price;
    }

    public static function inventoryHasErrors($id_inventory)
    {
        return (int)Db::getInstance()->getValue(
            'SELECT COUNT(*)
             FROM `'._DB_PREFIX_.self::$definition['table'].'` 
             WHERE `id_inventory` = '.(int)$id_inventory.' AND `has_error` = 1 AND `stock_updated` = 0'
        );
    }

    public static function updateInventoriedProducts($id_inventory)
    {
        Db::getInstance()->Execute(
            'UPDATE `'._DB_PREFIX_.self::$definition['table'].'` 
             SET stock_updated = 1
             WHERE has_error <> 1 AND `id_inventory` = '.(int)$id_inventory
        );
    }

    public static function productsNeedInventory($id_inventory)
    {
        return (int)Db::getInstance()->getValue(
            'SELECT (SUM(real_quantity) - SUM(shop_quantity - sold_quantity)) as gap
             FROM `'._DB_PREFIX_.self::$definition['table'].'` 
             WHERE `id_inventory` = '.(int)$id_inventory.' AND `stock_updated` = 0'
        );
    }
}
