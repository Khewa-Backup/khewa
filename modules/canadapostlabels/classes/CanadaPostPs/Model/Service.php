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

class Service extends \ObjectModel
{
    /* @var string $name */
    public $name;

    /* @var string $serviceCode */
    public $serviceCode;

    /* @var string $countryCode */
    public $countryCode;

    /* @var string $supportedOptionsArray */
    public $supportedOptionsArray;

    /* @var string $mandatoryOptionsArray */
    public $mandatoryOptionsArray;

    /* @var float $maxWeight */
    public $maxWeight;

    /* @var float $maxLength */
    public $maxLength;

    /* @var float $maxWidth */
    public $maxWidth;

    /* @var float $maxHeight */
    public $maxHeight;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'cpl_service',
        'primary' => 'id_service',
        'fields' => array(
            'name' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 255),
            'serviceCode' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 32),
            'countryCode' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 32),
            'supportedOptionsArray' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'mandatoryOptionsArray' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'maxWeight' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => true),
            'maxLength' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => true),
            'maxWidth' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => true),
            'maxHeight' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => true),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
        ),
    );

    public static function getServices($where = false)
    {
        return \Db::getInstance()->ExecuteS(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ($where ? Tools::sanitizeWhere($where) : '')
        );
    }

    public static function getService($id)
    {
        return \Db::getInstance()->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ' WHERE `'.self::$definition['primary'].'` = ' . (int)$id
        );
    }

    /**
     * @return Service|bool
     * */
    public static function getServiceByCodeAndCountry($serviceCode, $countryCode)
    {
        $result = \Db::getInstance()->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ' WHERE `serviceCode` = "' . pSQL($serviceCode) .'" AND `countryCode` = "'.pSQL($countryCode).'"'
        );

        return $result ? new Service($result['id_service']) : false;
    }


    /**
     * @param $array
     */
    public function setSupportedOptionsArray($array)
    {
        $this->supportedOptionsArray = implode(',', $array);
    }

    /**
     * @param $array
     */
    public function setMandatoryOptionsArray($array)
    {
        $this->mandatoryOptionsArray = implode(',', $array);
    }

    public function getSupportedOptionsArray()
    {
        return explode(',', $this->supportedOptionsArray);
    }

    public function getMandatoryOptionsArray()
    {
        return explode(',', $this->mandatoryOptionsArray);
    }
}
