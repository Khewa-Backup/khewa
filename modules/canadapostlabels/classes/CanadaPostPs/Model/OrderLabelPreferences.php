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

class OrderLabelPreferences extends \ObjectModel
{
    public $id_order_label_settings;
    public $show_packing_instructions;
    public $show_postage_rate;
    public $show_insured_value;
    public $cost_centre;
    public $customer_ref_1;
    public $customer_ref_2;
    public $unpackaged;
    public $oversized;
    public $mailing_tube;
    public $output_format;
    public $intended_method_of_payment;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'cpl_order_label_preferences',
        'primary' => 'id_order_label_preferences',
        'fields' => array(
            'id_order_label_settings' => array('type' => self::TYPE_INT, 'required' => false, 'validate' => 'isUnsignedInt', 'size' => 10),
            'show_packing_instructions'   => array('type' => self::TYPE_BOOL, 'required' => false),
            'show_postage_rate'   => array('type' => self::TYPE_BOOL, 'required' => false),
            'show_insured_value'   => array('type' => self::TYPE_BOOL, 'required' => false),
            'cost_centre'   => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'customer_ref_1'   => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'customer_ref_2'   => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'unpackaged'   => array('type' => self::TYPE_BOOL, 'required' => false),
            'oversized'   => array('type' => self::TYPE_BOOL, 'required' => false),
            'mailing_tube'   => array('type' => self::TYPE_BOOL, 'required' => false),
            'output_format'   => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'intended_method_of_payment'   => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
        ),
    );

    public static function getOrderLabelPreferences($where = false)
    {
        return \Db::getInstance()->ExecuteS(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ($where ? Tools::sanitizeWhere($where) : '')
        );
    }

    public static function getOrderLabelPreference($id)
    {
        return \Db::getInstance()->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ' WHERE `'.self::$definition['primary'].'` = ' . (int)$id
        );
    }
}
