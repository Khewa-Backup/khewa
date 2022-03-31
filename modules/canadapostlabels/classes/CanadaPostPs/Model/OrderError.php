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

class OrderError extends \ObjectModel
{
    public $id_order;

    public $id_batch;

    public $errorMessage;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'cpl_order_error',
        'primary' => 'id_order_error',
        'fields' => array(
            'id_order' => array('type' => self::TYPE_INT, 'required' => false, 'validate' => 'isUnsignedInt', 'size' => 10),
            'id_batch' => array('type' => self::TYPE_INT, 'required' => false, 'validate' => 'isUnsignedInt', 'size' => 10),
            'errorMessage' => array('type' => self::TYPE_STRING, 'required' => true, 'size' => 255),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
        ),
    );

    public static function getOrderErrors($where = false)
    {
        return \Db::getInstance()->ExecuteS(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ($where ? Tools::sanitizeWhere($where) : '')
        );
    }

    public static function getOrderError($id)
    {
        return \Db::getInstance()->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ' WHERE `'.self::$definition['primary'].'` = ' . (int)$id
        );
    }

    public static function getOrderErrorByOrderId($id_order)
    {
        return \Db::getInstance()->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ' WHERE `id_order` = ' . (int)$id_order
        );
    }
}
