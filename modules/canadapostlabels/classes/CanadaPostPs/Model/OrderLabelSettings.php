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

class OrderLabelSettings extends \ObjectModel
{
    public $id_order;

    public $id_order_label_parcel;

    public $id_order_label_address;

    public $id_order_label_options;

    public $id_order_label_preferences;

    public $id_order_label_customs;

    public $id_order_label_return;

    public $parcel;
    public $address;
    public $options;
    public $preferences;
    public $customs;
    public $customsProducts;
    public $return;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    public static $labelSettings = array(
        'parcel',
        'address',
        'options',
        'preferences',
        'customs',
        'return',
    );

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'cpl_order_label_settings',
        'primary' => 'id_order_label_settings',
        'fields' => array(
            'id_order' => array('type' => self::TYPE_INT, 'required' => false, 'validate' => 'isUnsignedInt', 'size' => 10),
            'id_order_label_parcel' => array('type' => self::TYPE_INT, 'required' => false, 'validate' => 'isUnsignedInt', 'size' => 10),
            'id_order_label_address' => array('type' => self::TYPE_INT, 'required' => false, 'validate' => 'isUnsignedInt', 'size' => 10),
            'id_order_label_options' => array('type' => self::TYPE_INT, 'required' => false, 'validate' => 'isUnsignedInt', 'size' => 10),
            'id_order_label_preferences' => array('type' => self::TYPE_INT, 'required' => false, 'validate' => 'isUnsignedInt', 'size' => 10),
            'id_order_label_customs' => array('type' => self::TYPE_INT, 'required' => false, 'validate' => 'isUnsignedInt', 'size' => 10),
            'id_order_label_return' => array('type' => self::TYPE_INT, 'required' => false, 'validate' => 'isUnsignedInt', 'size' => 10),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
        ),
    );

    public function __construct($id = null, $id_lang = null, $id_shop = null, $translator = null)
    {
        parent::__construct($id, $id_lang, $id_shop, $translator);

        $this->parcel = $this->getParcel();
        $this->address = $this->getAddress();
        $this->options = $this->getOptions();
        $this->preferences = $this->getPreferences();
        $this->customs = $this->getCustoms();
        $this->customsProducts = $this->getCustomsProductsArray();
        $this->return = $this->getReturn();
    }

    public static function getOrderLabelSettings($where = false)
    {
        return \Db::getInstance()->ExecuteS(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ($where ? Tools::sanitizeWhere($where) : '')
        );
    }

    public static function getOrderLabelSetting($id)
    {
        return \Db::getInstance()->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ' WHERE `'.self::$definition['primary'].'` = ' . (int)$id
        );
    }

    public static function getOrderLabelSettingForOrderId($id_order)
    {
        return \Db::getInstance()->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ' WHERE `id_order` = ' . (int)$id_order
        );
    }

    /**
     * @return OrderLabelParcel|bool
     * */
    public function getParcel()
    {
        if ($this->id_order_label_parcel) {
            return new OrderLabelParcel($this->id_order_label_parcel);
        } else {
            return new OrderLabelParcel();
        }
    }

    /**
     * @return OrderLabelAddress|bool
     * */
    public function getAddress()
    {
        if ($this->id_order_label_address) {
            return new OrderLabelAddress($this->id_order_label_address);
        } else {
            return new OrderLabelAddress();
        }
    }

    /**
     * @return OrderLabelOptions|bool
     * */
    public function getOptions()
    {
        if ($this->id_order_label_options) {
            return new OrderLabelOptions($this->id_order_label_options);
        } else {
            return new OrderLabelOptions();
        }
    }

    /**
     * @return OrderLabelPreferences|bool
     * */
    public function getPreferences()
    {
        if ($this->id_order_label_preferences) {
            return new OrderLabelPreferences($this->id_order_label_preferences);
        } else {
            return new OrderLabelPreferences();
        }
    }

    /**
     * @return OrderLabelCustoms|bool
     * */
    public function getCustoms()
    {
        if ($this->id_order_label_customs) {
            return new OrderLabelCustoms($this->id_order_label_customs);
        } else {
            return new OrderLabelCustoms();
        }
    }

    /**
     * @return array
     * */
    public function getCustomsProductsArray()
    {
        return OrderLabelCustomsProduct::getOrderLabelCustomsProducts(array('id_order_label_settings' => $this->id));
    }

    /**
     * @return OrderLabelReturn|bool
     * */
    public function getReturn()
    {
        if ($this->id_order_label_return) {
            return new OrderLabelReturn($this->id_order_label_return);
        } else {
            return new OrderLabelReturn();
        }
    }
}
