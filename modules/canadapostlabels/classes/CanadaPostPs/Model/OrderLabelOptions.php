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

class OrderLabelOptions extends \ObjectModel
{
    public $id_order_label_settings;

    public $options_SO;

    public $options_COV;

    public $options_COD;

    public $options_PA18;

    public $options_PA19;

    public $options_HFP;

    public $options_DNS;

    public $options_LAD;

    public $options_D2PO;

    public $non_delivery_options;

    public $COV_option_amount;

    public $COD_option_amount;

    public $COD_option_qualifier_1;

    public $D2PO_option_qualifier_2;

    public $email;

    public $notification_on_shipment;

    public $notification_on_exception;

    public $notification_on_delivery;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'cpl_order_label_options',
        'primary' => 'id_order_label_options',
        'fields' => array(
            'id_order_label_settings' => array('type' => self::TYPE_INT, 'required' => false, 'validate' => 'isUnsignedInt', 'size' => 10),
            'options_SO'  => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'options_COV'  => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'options_COD'  => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'options_PA18'  => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'options_PA19'  => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'options_HFP'  => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'options_DNS'  => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'options_LAD'  => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'options_D2PO'  => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'non_delivery_options'  => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'COV_option_amount'  => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'COD_option_amount'  => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'COD_option_qualifier_1'  => array('type' => self::TYPE_BOOL, 'required' => false),
            'D2PO_option_qualifier_2'  => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'email'  => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'notification_on_shipment'  => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'notification_on_exception'  => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'notification_on_delivery'  => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
        ),
    );

    public static function getOrderLabelOptions($where = false)
    {
        return \Db::getInstance()->ExecuteS(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ($where ? Tools::sanitizeWhere($where) : '')
        );
    }

    public static function getOrderLabelOption($id)
    {
        return \Db::getInstance()->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ' WHERE `'.self::$definition['primary'].'` = ' . (int)$id
        );
    }
}
