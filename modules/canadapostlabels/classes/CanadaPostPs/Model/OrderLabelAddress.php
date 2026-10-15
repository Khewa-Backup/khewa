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

class OrderLabelAddress extends \ObjectModel
{
    public $id_order_label_settings;

    public $sender;

    public $receiver;

    public $name;

    public $company;

    public $address_line_1;

    public $address_line_2;

    public $additional_address_info;

    public $client_voice_number;

    public $city;

    public $prov_state;

    public $country_code;

    public $postal_zip_code;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'cpl_order_label_address',
        'primary' => 'id_order_label_address',
        'fields' => array(
            'id_order_label_settings' => array('type' => self::TYPE_INT, 'required' => false, 'validate' => 'isUnsignedInt', 'size' => 10),
            'sender' => array('type' => self::TYPE_INT, 'required' => false, 'validate' => 'isUnsignedInt', 'size' => 10),
            'receiver' => array('type' => self::TYPE_INT, 'required' => false, 'validate' => 'isUnsignedInt', 'size' => 10),
            'name' => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'company' => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'address_line_1' => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'address_line_2' => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'additional_address_info' => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'client_voice_number' => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'city' => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'prov_state' => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'country_code' => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'postal_zip_code' => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
        ),
    );

    public static function getOrderLabelAddresses($where = false)
    {
        return \Db::getInstance()->ExecuteS(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ($where ? Tools::sanitizeWhere($where) : '')
        );
    }

    public static function getOrderLabelAdress($id)
    {
        return \Db::getInstance()->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ' WHERE `'.self::$definition['primary'].'` = ' . (int)$id
        );
    }
}
