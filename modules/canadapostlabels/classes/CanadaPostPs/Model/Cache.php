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

class Cache extends \ObjectModel
{
    /** @var int */
    public $id_cache;

    /** @var int */
    public $id_cart;

    /** @var array */
    public $rates;

    /** @var int */
    public $cart_quantity;

    /** @var int */
    public $id_address;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    /** @var object Cart */
    public $cart;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'cpl_cache',
        'primary' => 'id_cache',
        'fields' => array(
            'id_cart' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true, 'size' => 10),
            'cart_quantity' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'id_address' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
        ),
    );

    public function __construct($id = null, $id_lang = null, $id_shop = null, $translator = null)
    {
        parent::__construct($id, $id_lang, $id_shop, $translator);

        if (\Validate::isLoadedObject($this)) {
            // Instantiate cart
            $this->cart = new Cart($this->id_cart);

            // Load rates
            $this->rates = CacheRate::getByCacheId($this->id);
        }
    }

    /*
     * Get cache for a specific cart
     * @return Cache|bool
     * */
    public static function getByCartId($id_cart)
    {
        $Cache = \Db::getInstance()->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ' WHERE `id_cart` = ' . (int)$id_cart
        );

        return $Cache ? new Cache($Cache['id_cache']) : false;
    }

    /*
     * Cache the rates in the DB using CacheRate object
     * @param \CanadaPostWs\Type\Rating\QuoteType $Quote
     * @param bool $tax
     * @param bool $combine
     * @return Cache|Bool
     * */
    public function addRate($Quote, $tax = false, $combine = false)
    {
        /* @var $Quote \CanadaPostWs\Type\Rating\QuoteType */
        $methodArray = Method::getMethodByCode($Quote->getServiceCode());
        $id_carrier = $methodArray ? $methodArray['id_carrier'] : 0;

        // Check if rate is already available so we can update it
        $cacheRateArr = CacheRate::getByMethodCode($this->id, $methodArray['code']);
        $id = $cacheRateArr ? $cacheRateArr['id_cache_rate'] : null;

        $rate = $tax ? $Quote->getPriceTaxIncl() : $Quote->getPriceTaxExcl();

        try {
            $CacheRate             = new CacheRate($id);
            $CacheRate->id_cache   = $this->id;
            $CacheRate->id_carrier = $id_carrier;
            $CacheRate->code       = $Quote->getServiceCode();
            // Combine new rate with previous rate if necessary
            if ($combine && is_numeric($CacheRate->rate) && $CacheRate->rate > 0) {
                $CacheRate->rate = (float)$CacheRate->rate + (float)$rate;
            } else {
                $CacheRate->rate = (float)$rate;
            }
            $CacheRate->delay      = (string)$Quote->getTransitTime();
            $CacheRate->save();
        } catch (\Exception $e) {
            return false;
        }

        // fetch updated rates
        $this->rates = CacheRate::getByCacheId($this->id);

        return $this;
    }

    /*
     * Cache the rates in the DB using CacheRate object
     * @param array $serviceCodes
     * @param string $message
     * @return Cache|Bool
     * */
    public function addRateError($serviceCodes, $message)
    {
        foreach ($serviceCodes as $serviceCode) {
            $methodArray = Method::getMethodByCode($serviceCode);
            $id_carrier = $methodArray ? $methodArray['id_carrier'] : 0;

            // Check if rate is already available so we can update it
            $cacheRateArr = CacheRate::getByMethodCode($this->id, $methodArray['code']);
            $id = $cacheRateArr ? $cacheRateArr['id_cache'] : null;

            try {
                $CacheRate                = new CacheRate($id);
                $CacheRate->id_cache      = $this->id;
                $CacheRate->id_carrier    = $id_carrier;
                $CacheRate->code          = $serviceCode;
                $CacheRate->rate          = 0;
                $CacheRate->delay         = 0;
                $CacheRate->error_message = Tools::formatErrorMessage($message);
                $CacheRate->save();
            } catch (\Exception $e) {
                return false;
            }
        }

        // fetch updated rates
        $this->rates = CacheRate::getByCacheId($this->id);

        return $this;
    }

    /*
     * Clear rates for a specific cart by setting them to 0
     * @return bool
     * */
    public function clearCacheRates()
    {
        return \Db::getInstance()->delete(
            CacheRate::$definition['table'],
            'id_cache = ' . (int)$this->id
        );
    }

    /*
     * Get total quantity of non-virtual products in a cart.
     * @return int
     * */
    public static function getTotalCartQty($id_cart)
    {
        $qty = 0;
        $cart = new Cart($id_cart);
        foreach ($cart->getProducts() as $product) {
            if ($product['is_virtual']) {
                continue;
            }
            $qty += $product['quantity'];
        }
        return $qty;
    }

    /*
     * Check if cart products or address is different from cache
     * @return bool
     * */
    public static function isCartUpdated(Cart $Cart)
    {
        $Cache = self::getByCartId($Cart->id);

        if (\Validate::isLoadedObject($Cache)) {
            $cartAddress = new \Address($Cart->id_address_delivery);

            // Check if Address ID is different
            if ($cartAddress->id != $Cache->id_address) {
                return true;
            }

            // Check if address updated
            $cartAddressUpdateDate = new DateTime($cartAddress->date_upd);
            $cacheAddressUpdateDate = new DateTime($Cache->date_upd);
            if ($cartAddressUpdateDate > $cacheAddressUpdateDate) {
                return true;
            }

            // Check if products changed
            if (self::getTotalCartQty($Cart->id) != $Cache->cart_quantity) {
                return true;
            }
        }
        return false;
    }

    /**
     * Delete cache older than 3 months
     * @return bool
     * */
    public static function cleanOldCache()
    {
        $DateTime = new DateTime();
        $DateTime->modify('-3 months');

        $sql = '
            DELETE c, cr FROM `'._DB_PREFIX_.self::$definition['table'].'` c
            LEFT JOIN `'._DB_PREFIX_.CacheRate::$definition['table'].'` cr ON cr.`id_cache` = c.`id_cache`
            WHERE c.`date_upd` < "'.$DateTime->format('Y-m-d H:i:s').'"
        ';

        return \Db::getInstance()->execute($sql);
    }
}
