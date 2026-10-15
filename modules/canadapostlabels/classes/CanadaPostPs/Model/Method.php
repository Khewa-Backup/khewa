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

use \DbQuery;
use \ObjectModel;
use \Db;
use \PrestaShopDatabaseException;
use \PrestaShopException;

class Method extends \ObjectModel
{
    /*
     * @var int PrestaShop ID for the carrier
     * */
    public $id_carrier;

    /*
     * @var string comma separated string of all previous IDs for this carrier given by PrestaShop
     * */
    public $id_carrier_history;

    /*
     * @var string name of the method
     * */
    public $name;

    /*
     * @var string Service ID from Carrier e.g. DOM.EP
     * */
    public $code;

    /*
     * @var string USA or INT
     * */
    public $group;

    /*
     * @var bool
     * */
    public $active;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    public static $tracking_url = 'http://www.canadapost.ca/cpotools/apps/track/personal/findByTrackNumber?trackingNumber=@';

    public static $shipping_methods = array(
        'DOM' => array(
            'DOM.RP' => 'Regular',
            'DOM.EP' => 'Expedited',
            'DOM.XP' => 'Xpresspost',
            'DOM.PC' => 'Priority',
        ),
        'USA' => array(
            'USA.EP' => 'Expedited Parcel USA',
            'USA.XP' => 'Xpresspost USA',
            'USA.PW.ENV' => 'Priority Worldwide Envelope USA',
            'USA.PW.PAK' => 'Priority Worldwide pak USA',
            'USA.PW.PARCEL' => 'Priority Worldwide Parcel USA',
            'USA.SP.AIR' => 'Small Packet USA Air (less than 1kg)',
            'USA.TP' => 'Tracked Packet USA',
            'USA.TP.LVM' => 'Tracked Packet – USA (LVM)'
        ),
        'INT' => array(
            'INT.PW.ENV' => 'Priority Worldwide Envelope Int’l',
            'INT.PW.PAK' => 'Priority Worldwide pak Int’l',
            'INT.PW.PARCEL' => 'Priority Worldwide parcel Int’l',
            'INT.XP' => 'Xpresspost International',
            'INT.IP.AIR' => 'International Parcel Air',
            'INT.IP.SURF' => 'International Parcel Surface',
            'INT.SP.AIR' => 'Small Packet Int’l Air (less than 2kg)',
            'INT.SP.SURF' => 'Small Packet Int’l Surface (less than 2kg)',
            'INT.TP' => 'Tracked Packet – Int’l',
        ),
    );

    public static $options = array(
        'SO' => 'Signature',
        'COV' => 'Coverage',
        'COD' => 'Collect on delivery',
        'PA18' => 'Proof of Age Required - 18',
        'PA19' => 'Proof of Age Required - 19',
        'HFP' => 'Card for pickup',
        'DNS' => 'Do not safe drop',
        'LAD' => 'Leave at door - do not card',
        'D2PO' => 'Deliver to Post Office',
    );

    public static $non_delivery_options = array(
        'RASE' => 'Return at Sender’s Expense',
        'RTS' => 'Return to Sender',
        'ABAN' => 'Abandon',
    );

    public static $notifications = array(
        'on-shipment',
        'on-exception',
        'on-delivery',
    );

    public static $customsProductFields = array(
        'customs-description',
        'customs-number-of-units',
        'hs-tariff-code',
        'sku',
        'unit-weight',
        'customs-value-per-unit',
        'country-of-origin',
        'province-of-origin',
    );

    public static $labelFields = array(
        'group-id',
        'service-code',
        'weight',
        'box',
        'length',
        'width',
        'height',
        'unpackaged',
        'oversized',
        'mailing-tube',
        'sender',
        'name',
        'company',
        'address-line-1',
        'address-line-2',
        'additional-address-info',
        'client-voice-number',
        'city',
        'prov-state',
        'country-code',
        'postal-zip-code',
        'non_delivery_options',
        'COV-option-amount',
        'COD-option-amount',
        'COD-option-qualifier-1',
        'D2PO-option-qualifier-2',
        'email',
        'show-packing-instructions',
        'show-postage-rate',
        'show-insured-value',
        'cost-centre',
        'customer-ref-1',
        'customer-ref-2',
        'output-format',
        'intended-method-of-payment',
        'customs',
        'currency',
        'conversion-rate-from-cad',
        'reason-for-export',
        'other-reason',
        'certificate-number',
        'licence-number',
        'invoice-number',
        'return-spec',
        'return-recipient',
        'return-service-code',
        'id_order',
        'vieworder',
        'REQUESTED_SHIPPING_POINT',
        'SHIPPING_POINT',
        'PICKUP',
        'returner',
        'receiver',
    );

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'cpl_method',
        'primary' => 'id_method',
        'fields' => array(
            'id_carrier' => array('type' => self::TYPE_INT, 'required' => false, 'size' => 10),
            'id_carrier_history' => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 255),
            'name' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 255),
            'code' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 32),
            'group' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 32),
            'active' => array('type' => self::TYPE_INT, 'required' => true, 'size' => 10),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
        ),
    );

//    public function save($null_values = true, $auto_date = true)
//    {
//        return parent::save($null_values, $auto_date);
//    }
//
//    public function add($auto_date = true, $null_values = true)
//    {
//        return parent::add($null_values, $auto_date);
//    }

    public static function getMethods($where = false)
    {
        return \Db::getInstance()->ExecuteS(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ' '. ($where ? Tools::sanitizeWhere($where) : '')
        );
    }

    public static function getMethod($id)
    {
        return \Db::getInstance()->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ' WHERE `'.self::$definition['primary'].'` = ' . (int)$id
        );
    }

    public static function getMethodByCode($code)
    {
        return \Db::getInstance()->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ' WHERE `code` = "' . pSQL($code).'"'
        );
    }

    public static function getMethodByCarrierId($id_carrier)
    {
        return \Db::getInstance()->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ' WHERE `id_carrier` = ' . (int)$id_carrier
        );
    }

    /**
     * Get address group (DOM, USA, INT) from country iso_code
     *
     * @param int $id_country
     * @return string
     * */
    public static function getMethodGroup($iso_code)
    {
        switch ($iso_code) {
            case 'CA':
                return 'DOM';
                break;
            case 'US':
                return 'USA';
                break;
            default:
                return 'INT';
                break;
        }
    }
}
