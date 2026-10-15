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


class Solo_presenter extends Solo_translate
{
    static $_INSTANCE = null;

    public static function getInstance($context, $module)
    {
        if (self::$_INSTANCE == null) {
            self::$_INSTANCE = new Solo_presenter($context, $module);
        }
        if (self::$_INSTANCE->context !== $context || self::$_INSTANCE->module !== $module) {
            self::$_INSTANCE->context = $context;
            self::$_INSTANCE->module = $module;
        }
        return self::$_INSTANCE;
    }

    public function getUserConnects($params)
    {
        $front_end = isset($params['front_end']) && $params['front_end'] ? 1 : 0;
        $sql = 'SELECT uc.*  FROM `' . _DB_PREFIX_ . 'ets_solo_connect` uc 
			LEFT JOIN `' . _DB_PREFIX_ . 'ets_solo_user` u ON (u.id_ets_solo_user = uc.id_ets_solo_user)
			LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON (c.id_customer = u.id_customer)
			WHERE u.identifier != \'\' AND u.id_shop = ' . (int)$this->context->shop->id .
            (isset($params['id_customer']) && $params['id_customer'] ? ' AND u.id_customer = ' . (int)$params['id_customer'] : '') . ' 
			GROUP BY uc.id_ets_solo_connect
			ORDER BY uc.last_login_time DESC';
        $res = Db::getInstance()->executeS($sql);
        if ($res && $front_end) {
            foreach ($res as &$connect) {
                $loginTime = strtotime($connect['last_login_time']);
                $connect['last_login_time'] = date('Y-m-d', $loginTime) . ' ' . $this->l('at', 'Solo_presenter') . ' ' . date('H:i:s', $loginTime);
            }
        }
        return $res;
    }

    public function getUsers($params = array())
    {
        $is_full = isset($params['is_full']) && $params['is_full'] ? true : false;
        $sql = 'SELECT su.*' . (Tools::usingSecureMode() ? ', REPLACE(su.profile_url, "http://", "https://") as `profile_url`, REPLACE(su.profile_img, "http://", "https://") as `profile_img`' : '') . ', ' . ($is_full ? 'c.id_customer, CONCAT(c.firstname, " ", c.lastname) `customer_name`, c.email, su.last_login_type, su.last_login_time, (
            SELECT SUM(total_paid_real / conversion_rate)
            FROM `' . _DB_PREFIX_ . 'orders` o
            WHERE o.id_customer = c.id_customer
            ' . Shop::addSqlRestriction(Shop::SHARE_ORDER, 'o') . '
            AND o.valid = 1
        ) `total_spent`,' : '') . ' c.date_add 
		FROM `' . _DB_PREFIX_ . 'ets_solo_user` su
        INNER JOIN `' . _DB_PREFIX_ . 'customer` c ON (su.id_customer = c.id_customer) 
        WHERE su.identifier != \'\' AND su.id_shop = ' . (int)$this->context->shop->id
            . (isset($params['filter']) && $params['filter'] ? $params['filter'] : '')
            . (isset($params['slMonth']) && $params['slMonth'] ? ' AND MONTH(su.last_login_time) =' . (int)$params['slMonth'] : '')
            . (isset($params['slYear']) && $params['slYear'] ? ' AND YEAR(su.last_login_time) =' . (int)$params['slYear'] : '')
            . ' GROUP BY su.id_ets_solo_user 
        HAVING 1 ' . (isset($params['having']) && $params['having'] ? $params['having'] : '');

        if (isset($params['nb']) && $params['nb'])
            return ($nb = Db::getInstance()->executeS($sql)) ? count($nb) : 0;

        $sql .= ' ORDER BY ' . (isset($params['sort']) && $params['sort'] ? $params['sort'] : ' c.date_add DESC')
            . ((isset($params['start']) && $params['start'] !== false) && (isset($params['limit']) && $params['limit']) ? " LIMIT " . (int)$params['start'] . ", " . (int)$params['limit'] : '');
        $users = Db::getInstance()->executeS($sql);
        if ($users && $is_full) {
            $socials = Solo_defines::getInstance($this->context, $this->module)->getFields('socials');
            foreach ($users as &$user) {
                $user['network'] = $socials[trim($user['network'])]['name'];
                $user['last_login_type'] = $socials[trim($user['last_login_type'])]['name'];
            }
        }
        return $users;
    }

    public function getNbTraffic($params)
    {
        if (!(isset($params['chart'])) || !$params['chart'])
            return 0;
        $groupBy = ' GROUP BY YEAR(c.date_add)';
        if (isset($params['month']) && $params['month'])
            $groupBy .= ', MONTH(c.date_add), DAY(c.date_add)';
        elseif (isset($params['year']) && $params['year'])
            $groupBy .= ', MONTH(c.date_add)';

        $sql = "SELECT " . ($params['chart'] != 'pie' ? " DATE_FORMAT(c.date_add, '%Y-%m-%d') `date_series`," : '') . " COUNT(su.id_ets_solo_user) `total` 
            FROM `" . _DB_PREFIX_ . "ets_solo_user` su
            INNER JOIN `" . _DB_PREFIX_ . "customer` c ON (su.id_customer = c.id_customer)
            WHERE su.id_shop = " . (int)$this->context->shop->id
            . (isset($params['month']) && (int)$params['month'] > 0 ? " AND MONTH(c.date_add) = " . (int)$params['month'] : '')
            . (isset($params['year']) && (int)$params['year'] > 0 ? " AND YEAR(c.date_add) = " . (int)$params['year'] : '')
            . (isset($params['id_country']) && (int)$params['id_country'] > 0 ? " AND su.id_country = " . (int)$params['id_country'] : '')
            . (isset($params['network']) && trim($params['network']) !== '' ? " AND su.network = '" . pSQL($params['network']) . "'" : '')
            . ($params['chart'] != 'pie' ? $groupBy . " ORDER BY c.date_add " : '');

        return $params['chart'] != 'pie' ? Db::getInstance()->executeS($sql) : (int)Db::getInstance()->getValue($sql);
    }
}
