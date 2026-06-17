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

function upgrade_module_2_0_5($object)
{
    if ($object) {
        $res = Db::getInstance()->execute(alterTable(array(
            'tbl' => 'ets_solo_user',
            'col' => 'last_login_type',
            'var' => 'varchar(3) NOT NULL',
        )));
        $res &= Db::getInstance()->execute(alterTable(array(
            'tbl' => 'ets_solo_user',
            'col' => 'last_login_time',
            'var' => 'datetime NOT NULL',
        )));
        $res &= Db::getInstance()->execute('CREATE UNIQUE INDEX id_customer_identifier ON ' . _DB_PREFIX_ . 'ets_solo_user (id_customer, identifier);');
        $res &= Db::getInstance()->execute('CREATE INDEX idx_ets_solo_user_customer ON ' . _DB_PREFIX_ . 'ets_solo_user (id_ets_solo_user, id_customer);');
        $res &= Db::getInstance()->execute('CREATE INDEX idx_ets_solo_user_network ON ' . _DB_PREFIX_ . 'ets_solo_user (network);');
        $res &= Db::getInstance()->execute('CREATE INDEX idx_ets_solo_user_identifier ON ' . _DB_PREFIX_ . 'ets_solo_user (identifier);');
        $res &= Db::getInstance()->execute('CREATE INDEX idx_ets_solo_connect_ets_solo_user ON ' . _DB_PREFIX_ . 'ets_solo_connect (id_ets_solo_connect, id_ets_solo_user);');
        $res &= Db::getInstance()->execute('
            UPDATE ' . _DB_PREFIX_ . 'ets_solo_user a
            INNER JOIN ' . _DB_PREFIX_ . 'ets_solo_connect b ON a.id_ets_solo_user = b.id_ets_solo_user 
            SET a.last_login_type = b.last_login_type, a.last_login_time = b.last_login_time
        ');
        return $res;
    }
    return true;
}

function alterTable($args = array())
{
    if (!isset($args['tbl']) || !$args['tbl'] || !(isset($args['col'])) || !$args['col'] || !(isset($args['var'])) || !$args['var'])
        return false;
    return '
            SET @preparedStatement = (SELECT IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE (table_name = "' . _DB_PREFIX_ . pSQL($args['tbl']) . '") AND (table_schema = DATABASE()) AND (column_name = "' . pSQL($args['col']) . '")) > 0,"SELECT 1", CONCAT("ALTER TABLE ", "' . _DB_PREFIX_ . pSQL($args['tbl']) . '", " ADD ", "' . pSQL($args['col']) . '"," ", "' . pSQL($args['var']) . ';")));
            PREPARE alterIfNotExists FROM @preparedStatement;
            EXECUTE alterIfNotExists;
            DEALLOCATE PREPARE alterIfNotExists;
        ';
}
