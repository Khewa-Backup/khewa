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
use \PrestaShopException;
use \PrestaShopDatabaseException;

class Address extends \ObjectModel
{
    /** @var int Country ID */
    public $id_country;

    /** @var int State ID */
    public $id_state;

    /** @var string Alias (eg. Home, Work...) */
    public $name;

    /** @var string Company (optional) */
    public $company;

    /** @var string Address first line */
    public $address1;

    /** @var string Address second line (optional) */
    public $address2;

    /** @var string Postal code */
    public $postcode;

    /** @var string City */
    public $city;

    /** @var string Phone number */
    public $phone;

    /** @var bool Origin */
    public $origin;

    /** @var bool */
    public $active;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    /** @var array Zone IDs cache */
    protected static $_idZones = array();
    /** @var array Country IDs cache */
    protected static $_idCountries = array();

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'cpl_address',
        'primary' => 'id_address',
        'fields' => array(
            'id_country' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_state' => array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'required' => true),
            'name' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 32),
            'company' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 255),
            'address1' => array('type' => self::TYPE_STRING, 'validate' => 'isAddress', 'required' => true, 'size' => 128),
            'address2' => array('type' => self::TYPE_STRING, 'validate' => 'isAddress', 'size' => 128),
            'postcode' => array('type' => self::TYPE_STRING, 'validate' => 'isPostCode', 'required' => true,  'size' => 12),
            'city' => array('type' => self::TYPE_STRING, 'validate' => 'isCityName', 'required' => true, 'size' => 64),
            'phone' => array('type' => self::TYPE_STRING, 'validate' => 'isPhoneNumber', 'required' => true,  'size' => 32),
            'origin' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'size' => 10),
            'active' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'size' => 10),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
        ),
    );

    public function save($null_values = false, $auto_date = true)
    {
        $this->postcode = preg_replace('/[^A-Za-z0-9]/', '', Tools::strtoupper($this->postcode));
        if (!Tools::isCanadianPostalCode($this->postcode)) {
            return false;
        }

        return parent::save($null_values, $auto_date);
    }

    /*
     * Return all addresses
     * */
    public static function getAddresses($where = false)
    {
        return \Db::getInstance()->ExecuteS(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ' '. ($where ? Tools::sanitizeWhere($where) : '')
        );
    }

    /*
     * Return address
     *  */
    public static function getAddress($id)
    {
        return \Db::getInstance()->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ' WHERE `'.self::$definition['primary'].'` = ' . (int)$id
        );
    }

    /*
     * Return origin address
     * */
    public static function getOriginAddress()
    {
        $origin = \Db::getInstance()->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ' WHERE `origin` = 1'
        );
        return new Address($origin['id_address']);
    }

    /*
     * Set address as the origin for rate calculation
     * */
    public function setAsOrigin()
    {
        // Set all other addresses as non-origin first
        \Db::getInstance()->update(self::$definition['table'], array('origin' => 0));
        $this->origin = 1;
    }
}
