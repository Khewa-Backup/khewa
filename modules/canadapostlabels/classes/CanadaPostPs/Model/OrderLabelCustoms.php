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

class OrderLabelCustoms extends \ObjectModel
{
    public $id_order_label_settings;
    public $currency;
    public $conversion_rate_from_cad;
    public $reason_for_export;
    public $other_reason;
    public $certificate_number;
    public $licence_number;
    public $invoice_number;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'cpl_order_label_customs',
        'primary' => 'id_order_label_customs',
        'fields' => array(
            'id_order_label_settings' => array('type' => self::TYPE_INT, 'required' => false, 'validate' => 'isUnsignedInt', 'size' => 10),
            'currency'    => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'conversion_rate_from_cad'    => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'reason_for_export'    => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'other_reason'    => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'certificate_number'    => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'licence_number'    => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'invoice_number'    => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
        ),
    );

    public static function getOrderLabelCustoms($where = false)
    {
        return \Db::getInstance()->ExecuteS(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ($where ? Tools::sanitizeWhere($where) : '')
        );
    }

    public static function getOrderLabelCustom($id)
    {
        return \Db::getInstance()->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ' WHERE `'.self::$definition['primary'].'` = ' . (int)$id
        );
    }
}
