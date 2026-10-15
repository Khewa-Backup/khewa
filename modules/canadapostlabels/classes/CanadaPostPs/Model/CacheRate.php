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

class CacheRate extends \ObjectModel
{
    /** @var int */
    public $id_cache_rate;

    /** @var int */
    public $id_cache;

    /** @var int */
    public $id_carrier;

    /** @var string */
    public $code;

    /** @var float */
    public $rate;

    /** @var string */
    public $delay;

    /** @var string */
    public $error_message;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'cpl_cache_rate',
        'primary' => 'id_cache_rate',
        'fields' => array(
            'id_cache' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true, 'size' => 10),
            'id_carrier' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true, 'size' => 10),
            'code' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
            'rate' => array('type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat', 'required' => true),
            'delay' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'error_message' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
        ),
    );

    /*
     * Get all cached rates for a specific cache ID
     * @return array
     * */
    public static function getByCacheId($id_cache)
    {
        return \Db::getInstance()->executeS(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ' WHERE `id_cache` = ' . (int)$id_cache
        );
    }

    /*
     * Get a single rate for a specific carrier
     * @return CacheRate
     * */
    public static function getByCarrierId($id_cache, $id_carrier)
    {
        $CacheRate = \Db::getInstance()->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ' WHERE `id_cache` = '.(int)$id_cache.' AND `id_carrier` = ' . (int)$id_carrier
        );

        return $CacheRate ? new CacheRate($CacheRate['id_cache_rate']) : false;
    }

    /*
     * Get a single rate for a specific carrier
     * @return array
     * */
    public static function getByMethodCode($id_cache, $code)
    {
        return \Db::getInstance()->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ' WHERE `id_cache` = '.(int)$id_cache.' AND `code` = "' . pSQL($code).'"'
        );
    }

    /*
     * Clear rate by setting it to 0
     * @return bool
     * */
    public function clearRate()
    {
        $this->rate = 0;
        $this->update();
    }
}
