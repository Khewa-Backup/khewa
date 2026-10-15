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

if (!defined('_PS_VERSION_')) {
    exit;
}


class Solo_connect extends ObjectModel
{
    public $id_ets_solo_user;
    public $last_login_type;
    public $identifier;
    public $last_login_time;

    public static $definition = array(
        'table' => 'ets_solo_connect',
        'primary' => 'id_ets_solo_connect',
        'fields' => array(
            'id_ets_solo_user' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'identifier' => array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
            'last_login_type' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'last_login_time' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );

    public static function getUserByEmail($id_ets_solo_user, $last_login_type)
    {
        return (int)Db::getInstance()->getValue(
            '
			SELECT id_ets_solo_connect FROM `' . _DB_PREFIX_ . bqSQL(trim(self::$definition['table'])) . '`
		    WHERE id_ets_solo_user =' . (int)$id_ets_solo_user . ' AND last_login_type = "' . pSQL($last_login_type) . '"'
        );
    }

    public static function getTotalConnections()
    {
        return (int)Db::getInstance()->getValue('SELECT COUNT(id_ets_solo_connect) FROM `' . _DB_PREFIX_ . bqSQL(self::$definition['table']) . '`');
    }

    public static function getConnectWithParams($params, $context)
    {
        if ($context == null || !Validate::isLoadedObject($context->shop)) {
            return false;
        }
        return Db::getInstance()->executeS('
            SELECT sc.last_login_type, COUNT(IF(su.id_ets_solo_user is NOT NULL, 1, 0)) as `con_total`
            FROM `' . _DB_PREFIX_ . 'ets_solo_connect` sc
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_solo_user` su ON (sc.id_ets_solo_user = su.id_ets_solo_user)
            WHERE su.id_shop = ' . (int)$context->shop->id
            . (isset($params['month']) && $params['month'] ? " AND MONTH(sc.last_login_time) = " . (int)$params['month'] : '')
            . (isset($params['year']) && $params['year'] ? " AND YEAR(sc.last_login_time) = " . (int)$params['year'] : '')
            . (isset($params['id_country']) && $params['id_country'] ? " AND su.id_country = " . (int)$params['id_country'] : '')
            . ' GROUP BY sc.last_login_type
        ');
    }

    public static function disconnect($id_customer, $type)
    {
        $id_ets_solo_user = Solo_user::getUserIdByIdCustomer($id_customer);
        if (!$id_ets_solo_user)
            return false;
        $result = Db::getInstance()->delete(self::$definition['table'], 'id_ets_solo_user=' . (int)$id_ets_solo_user . ' AND last_login_type=\'' . pSQL($type) . '\'');
        if ($result) {
            $user = new Solo_user($id_ets_solo_user);
            if ($user->network == $type) {
                $user->identifier = '';
                $user->save();
            }
        }
        return $result;
    }

    public static function getIdCustomerByIdentifier($identifier)
    {
        if (trim($identifier) == '' || !Validate::isCleanHtml($identifier)) {
            return false;
        }
        return (int)Db::getInstance()->getValue('
            SELECT c.id_customer 
            FROM `' . _DB_PREFIX_ . 'ets_solo_user` su
            INNER JOIN `' . _DB_PREFIX_ . 'ets_solo_connect` sc ON sc.`id_ets_solo_user` = su.`id_ets_solo_user`
            INNER JOIN `' . _DB_PREFIX_ . 'customer` c ON c.id_customer = su.id_customer
            WHERE sc.`identifier`=\'' . pSQL($identifier) . '\'
        ');
    }
}
