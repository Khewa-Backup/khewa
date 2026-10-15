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

class Shipment extends \ObjectModel
{
    public $id_order;

    public $id_group;

    public $id_batch;

    public $name;

    public $address1;

    public $address2;

    public $city;

    public $prov_state;

    public $country_code;

    public $postal_zip_code;

    public $tracking_pin;

    public $return_tracking_pin;

    /** @var string ID given to shipment by Canada Post */
    public $shipment_id;

    public $service_code;

    public $self_link;

    public $details_link;

    public $label_link;

    public $return_label_link;

    public $commercial_invoice_link;

    public $refund_link;

    public $transmitted;

    public $voided;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'cpl_shipment',
        'primary' => 'id_shipment',
        'fields' => array(
            'id_order' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => false, 'size' => 32),
            'id_group' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => false, 'size' => 32),
            'id_batch' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => false, 'size' => 32),
            'name' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => false, 'size' => 255),
            'address1' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 255),
            'address2' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 255),
            'city' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 255),
            'prov_state' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 255),
            'country_code' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 255),
            'postal_zip_code' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 255),
            'tracking_pin' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 255),
            'return_tracking_pin' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 255),
            'shipment_id' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 255),
            'service_code' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 255),
            'self_link' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 255),
            'details_link' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 255),
            'label_link' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 255),
            'return_label_link' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 255),
            'commercial_invoice_link' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 255),
            'refund_link' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 255),
            'transmitted' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => false, 'size' => 32),
            'voided' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => false, 'size' => 32),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
        ),
    );

    public static function getShipments($where = false)
    {
        return \Db::getInstance()->ExecuteS(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ($where ? Tools::sanitizeWhere($where) : '')
        );
    }

    public static function getShipment($id)
    {
        return \Db::getInstance()->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ' WHERE `'.self::$definition['primary'].'` = ' . (int)$id
        );
    }

    public static function getShipmentByShipmentId($shipment_id)
    {
        return \Db::getInstance()->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ' WHERE `shipment_id` = ' . (int)$shipment_id
        );
    }

    public static function getShipmentByTrackingPin($tracking_pin)
    {
        return \Db::getInstance()->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ' WHERE `tracking_pin` = ' . (int)$tracking_pin
        );
    }

    public static function getNewestShipmentForOrder($id_order)
    {
        return \Db::getInstance()->getRow(
            'SELECT *
            FROM ' . _DB_PREFIX_ . self::$definition['table'] . '
            WHERE `id_order` = ' . (int)$id_order.'
            ORDER BY `date_add` DESC'
        );
    }
}
