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

use Cart;
use DateTime;
use \Configuration;
use \Context;
use \ObjectModel;
use \Db;
use \PrestaShopDatabaseException;
use \PrestaShopException;
use \PrestaShopLogger;
use \Validate;

class CacheTracking extends \ObjectModel
{
    /** @var int */
    public $id_cache_tracking;

    /** @var int */
    public $id_order;

    /** @var int */
    public $id_shipment;

    /** @var string */
    public $pin;

    /** @var string */
    public $service_name;

    /** @var string */
    public $event_type;

    /** @var string */
    public $event_description;

    /** @var string */
    public $event_location;

    /** @var string */
    public $expected_delivery_date;

    /** @var string */
    public $actual_delivery_date;

    /** @var string */
    public $mailed_on_date;

    /** @var string */
    public $event_date_time;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    public static $receivedAtFacilityEventTypes = array(
        'PR01_RECEIVED',
    );

    public static $inTransitEventTypes = array(
        'INFO',
        'INDUCTION',
        'CONTAINER',
        'DISPATCH',
        'INCOMING',
        'VEHICLE_INFO',
        'NOT_CUST',
        'ARRIVAL_IN_CANADA',
        'TO_CUST',
        'FROM_CUST',
        'TRANSFER',
        'INFO_TID',
        'TRANSFER_ITEM',
        'RTC_LABEL_PROC',
        'FOR_REVIEW',
        'CC_INFO',
        'CC_INFO_W_TID',
        'CC_PIN_IN_CONT_TRAIL',
    );

    public static $outForDeliveryEventTypes = array(
        'OUT',
    );

    public static $attemptedDeliveryEventTypes = array(
        'ATTEMPTED',
        'TO_RETAIL',
    );

    public static $notDeliverableEventTypes = array(
        'DETENTION',
        'RTS_LABEL_PROC',
    );

    public static $deliveredEventTypes = array(
        'DELIVERED',
    );

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'cpl_cache_tracking',
        'primary' => 'id_cache_tracking',
        'fields' => array(
            'id_order' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true, 'size' => 10),
            'id_shipment' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true, 'size' => 10),
            'pin' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false),
            'service_name' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false),
            'event_type' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false),
            'event_description' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false),
            'event_location' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false),
            'expected_delivery_date' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false),
            'actual_delivery_date' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false),
            'mailed_on_date' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false),
            'event_date_time' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
        ),
    );

    public static function getCacheTrackings($where = false)
    {
        return \Db::getInstance()->ExecuteS(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ($where ? Tools::sanitizeWhere($where) : '')
        );
    }

    /**
     * @var $orderStateIdArr string
     * */
    public static function getShippedOrdersByOrderStatusIds($orderStateIdArr)
    {
        return \Db::getInstance()->ExecuteS(
            'SELECT o.`id_order`
                FROM '._DB_PREFIX_.'orders o
                LEFT JOIN '._DB_PREFIX_.\CanadaPostPs\Shipment::$definition['table'].' s 
                ON (s.`id_order` = o.`id_order`)
                WHERE o.`current_state` IN ('.$orderStateIdArr.')
                '.\Shop::addSqlRestriction(false, 'o').'
                GROUP BY o.`id_order`
                ORDER BY o.`id_order` ASC'
        );
    }

    public static function getCacheTracking($id)
    {
        return \Db::getInstance()->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ' WHERE `'.self::$definition['primary'].'` = ' . (int)$id
        );
    }

    public static function getByOrderIdAndShipmentId($id_order, $id_shipment)
    {
        return \Db::getInstance()->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ' 
            WHERE `id_order` = ' . (int)$id_order . ' 
            AND `id_shipment` =' . $id_shipment
        );
    }
}
