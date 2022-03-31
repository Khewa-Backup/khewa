<?php
/**
 * 2019 ZH Media
 *
 * NOTICE OF LICENSE
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 * Do not resell or redistribute this file, either fully or partially.
 * Do not remove this comment containing author information and copyright.
 *
 * @author    Zack Hussain <me@zackhussain.ca>
 * @copyright 2019 ZH Media - All Rights Reserved
 */

namespace CanadaPostPs;

use \ObjectModel;
use \Db;
use \PrestaShopDatabaseException;
use \PrestaShopException;

class OrderLabelCustomsProduct extends \ObjectModel
{
    public $id_order_label_settings;
    public $id_product;

    public $customs_description;
    public $customs_number_of_units;
    public $hs_tariff_code;
    public $sku;
    public $unit_weight;
    public $customs_value_per_unit;
    public $country_of_origin;
    public $province_of_origin;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'cpl_order_label_customs_product',
        'primary' => 'id_order_label_customs_product',
        'fields' => array(
            'id_order_label_settings' => array('type' => self::TYPE_INT, 'required' => false, 'validate' => 'isUnsignedInt', 'size' => 10),
            'id_product' => array('type' => self::TYPE_INT, 'required' => false, 'validate' => 'isUnsignedInt', 'size' => 10),
            'customs_description'     => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'customs_number_of_units'     => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'hs_tariff_code'     => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'sku'     => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'unit_weight'     => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'customs_value_per_unit'     => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'country_of_origin'     => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'province_of_origin'     => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
        ),
    );

    public static function getOrderLabelCustomsProducts($where = false)
    {
        return \Db::getInstance()->ExecuteS(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ($where ? Tools::sanitizeWhere($where) : '')
        );
    }

    public static function getOrderLabelCustomProduct($id)
    {
        return \Db::getInstance()->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ' WHERE `'.self::$definition['primary'].'` = ' . (int)$id
        );
    }
}
