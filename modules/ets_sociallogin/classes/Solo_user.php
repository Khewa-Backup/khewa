<?php
/**
  * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */

if (!defined('_PS_VERSION_')) { exit; }


class Solo_user extends ObjectModel
{
    public $id_ets_solo_user;
    public $id_shop;
    public $id_customer;
    public $id_country;
    public $identifier;
    public $network;
    public $profile_url;
    public $profile_img;
    public $discount_code;
    public $last_login_type;
    public $last_login_time;

    public static $definition = array(
        'table' => 'ets_solo_user',
        'primary' => 'id_ets_solo_user',
        'fields' => array(
            'id_shop' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_customer' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_country' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'network' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'identifier' => array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
            'profile_url' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'profile_img' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'discount_code' => array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
            'last_login_type' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'last_login_time' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );

    public static function getUserByEmail($email)
    {
        return (int)Db::getInstance()->getValue('
            SELECT id_ets_solo_user 
            FROM `' . _DB_PREFIX_ . bqSQL(trim(self::$definition['table'])) . '` u
            INNER JOIN `' . _DB_PREFIX_ . 'customer` c ON (u.id_customer = c.id_customer)
            WHERE c.email ="' . pSQL($email) . '"'
        );
    }

    public static function getUserIdByIdCustomer($id_customer)
    {
        return (int)Db::getInstance()->getValue('
            SELECT id_ets_solo_user 
            FROM `' . _DB_PREFIX_ . bqSQL(trim(self::$definition['table'])) . '` u
            INNER JOIN `' . _DB_PREFIX_ . 'customer` c ON (u.id_customer = c.id_customer)
            WHERE c.id_customer='.(int)$id_customer
        );

    }

    public static function getIdentifier($identifier)
    {
        return Db::getInstance()->getValue('
			SELECT c.email FROM `' . _DB_PREFIX_ . bqSQL(self::$definition['table']) . '` u
			 INNER JOIN `' . _DB_PREFIX_ . 'customer` c ON (u.id_customer = c.id_customer)
			 WHERE u.identifier ="' . pSQL($identifier) . '"'
        );
    }

    public static function getTotalRegistrations($context)
    {
        if ($context == null || !Validate::isLoadedObject($context->shop)) {
            return 0;
        }
        return (int)Db::getInstance()->getValue('
			SELECT COUNT(id_customer) 
			FROM `' . _DB_PREFIX_ . bqSQL(self::$definition['table']) . '` 
            WHERE id_shop = ' . (int)$context->shop->id
        );
    }

    public static function getTotalVoucherGenerated($context)
    {
        if ($context == null || !Validate::isLoadedObject($context->shop)) {
            return 0;
        }
        return (int)Db::getInstance()->getValue('
			SELECT SUM(IF(u.discount_code <> "" OR u.discount_code is NOT NULL, 1, 0)) 
			FROM `' . _DB_PREFIX_ . bqSQL(self::$definition['table']) . '` u
			LEFT JOIN `' . _DB_PREFIX_ . 'cart_rule` cr ON (u.discount_code = CONVERT(cr.code USING utf8))' . Shop::addSqlAssociation('cart_rule', 'cr') . '
            WHERE IF(cr.id_cart_rule is NULL, 0, 1) AND id_shop = ' . (int)$context->shop->id
        );
    }

    public static function getTotalVoucherUsed($context)
    {
        if ($context == null || !Validate::isLoadedObject($context->shop)) {
            return 0;
        }
        return (int)Db::getInstance()->getValue('
			SELECT SUM(IF(u.discount_code != "" OR u.discount_code is NOT NULL, 1, 0)) 
			FROM `' . _DB_PREFIX_ . bqSQL(self::$definition['table']) . '` u
			LEFT JOIN `' . _DB_PREFIX_ . 'cart_rule` cr ON (u.discount_code = CONVERT(cr.code USING utf8))' . Shop::addSqlAssociation('cart_rule', 'cr') . '
			LEFT JOIN `' . _DB_PREFIX_ . 'cart_cart_rule` ccr ON (cr.id_cart_rule = ccr.id_cart_rule)
            WHERE IF(ccr.id_cart_rule is NULL, 0, 1) AND u.id_shop = ' . (int)$context->shop->id
        );
    }

    public static function getUserWithParams($params, $context)
    {
        if ($context == null || !Validate::isLoadedObject($context->shop)) {
            return false;
        }
        return Db::getInstance()->executeS('
            SELECT network, COUNT(IF(su.id_ets_solo_user is NOT NULL, 1, 0)) as `reg_total`
            FROM `' . _DB_PREFIX_ . 'ets_solo_user` su
            INNER JOIN `' . _DB_PREFIX_ . 'customer` c ON (c.id_customer = su.id_customer) 
            WHERE su.id_shop = ' . (int)$context->shop->id
            . (isset($params['month']) && $params['month'] ? " AND MONTH(c.date_add) = " . (int)$params['month'] : '')
            . (isset($params['year']) && $params['year'] ? " AND YEAR(c.date_add) = " . (int)$params['year'] : '')
            . (isset($params['id_country']) && $params['id_country'] ? " AND su.id_country = " . (int)$params['id_country'] : '')
            . ' GROUP BY network
        ');
    }

    public static function getIdCustomerByEmail($email)
    {
        if (trim($email) == '' || !Validate::isEmail($email)) {
            return false;
        }

        $shopGroup = Shop::getGroupFromShop(Shop::getContextShopID(), false);
        $sql = new DbQuery();
        $sql->select('c.`id_customer`');
        $sql->from('customer', 'c');
        $sql->where('c.`email` = \'' . pSQL($email) . '\'');
        if (Shop::getContext() == Shop::CONTEXT_SHOP && $shopGroup['share_customer']) {
            $sql->where('c.`id_shop_group` = ' . (int) Shop::getContextShopGroupID());
        } else {
            $sql->where('c.`id_shop` IN (' . implode(', ', Shop::getContextListShopID(Shop::SHARE_CUSTOMER)) . ')');
        }
        $sql->where('c.`is_guest` = 0');
        $sql->where('c.`deleted` = 0');

        return (int)Db::getInstance()->getValue($sql);
    }

    public static function getIdCustomerByIdentifier($identifier)
    {
        if (trim($identifier) == '' || !Validate::isCleanHtml($identifier)) {
            return false;
        }
        return (int)Db::getInstance()->getValue('SELECT c.id_customer 
            FROM `' . _DB_PREFIX_ . 'ets_solo_user` su
            INNER JOIN `' . _DB_PREFIX_ . 'customer` c ON c.id_customer = su.id_customer
            WHERE `identifier`=\'' . pSQL($identifier) . '\'
        ');
    }

    public static function getIdCustomerByOtherSocial($id_customer)
    {
        if (!$id_customer || !Validate::isUnsignedInt($id_customer)) {
            return false;
        }
        return (int)Db::getInstance()->getValue('
            SELECT c.id_customer 
            FROM `' . _DB_PREFIX_ . 'ets_solo_user` su
            INNER JOIN `' . _DB_PREFIX_ . 'customer` c ON c.id_customer = su.id_customer
            WHERE su.`id_customer`=' . (int)$id_customer
        );
    }
}
