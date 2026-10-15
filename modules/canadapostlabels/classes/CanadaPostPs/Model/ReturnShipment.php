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

class ReturnShipment extends \ObjectModel
{
    public $id_order;

    public $id_batch;

    public $name;

    public $address1;

    public $address2;

    public $city;

    public $province;

    public $postal_code;

    public $tracking_pin;

    public $service_code;

    public $return_label_link;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'cpl_return_shipment',
        'primary' => 'id_return_shipment',
        'fields' => array(
            'id_order' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => false, 'size' => 32),
            'id_batch' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => false, 'size' => 32),
            'name' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 32),
            'address1' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 255),
            'address2' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 255),
            'city' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 255),
            'province' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 255),
            'postal_code' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 255),
            'tracking_pin' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 255),
            'service_code' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 255),
            'return_label_link' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 255),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
        ),
    );

    public static function getReturnShipments($where = false)
    {
        return \Db::getInstance()->ExecuteS(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ($where ? Tools::sanitizeWhere($where) : '')
        );
    }

    public static function getReturnShipment($id)
    {
        return \Db::getInstance()->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ' WHERE `'.self::$definition['primary'].'` = ' . (int)$id
        );
    }

    public static function getReturnShipmentByTrackingPin($tracking_pin)
    {
        return \Db::getInstance()->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ' WHERE `tracking_pin` = ' . (int)$tracking_pin
        );
    }
}
