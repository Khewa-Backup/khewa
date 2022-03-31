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

class Manifest extends \ObjectModel
{
    public $poNumber;

    public $manifestDateTime;

    public $contractId;

    public $methodOfPayment;

    public $totalCost;

    public $self_link;

    public $details_link;

    public $label_link;

    public $manifest_shipments_link;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'cpl_manifest',
        'primary' => 'id_manifest',
        'fields' => array(
            'poNumber' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => false, 'size' => 32),
            'manifestDateTime' => array('type' => self::TYPE_DATE, 'validate' => 'isGenericName', 'required' => false),
            'contractId' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => false, 'size' => 32),
            'methodOfPayment' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => false, 'size' => 32),
            'totalCost' => array('type' => self::TYPE_FLOAT, 'validate' => 'isGenericName', 'required' => false, 'size' => 32),
            'self_link' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 255),
            'details_link' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 255),
            'label_link' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 255),
            'manifest_shipments_link' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 255),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
        ),
    );

    public static function getManifests($where = false)
    {
        return \Db::getInstance()->ExecuteS(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ($where ? Tools::sanitizeWhere($where) : '')
        );
    }

    public static function getManifest($id)
    {
        return \Db::getInstance()->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ' WHERE `'.self::$definition['primary'].'` = ' . (int)$id
        );
    }
}
