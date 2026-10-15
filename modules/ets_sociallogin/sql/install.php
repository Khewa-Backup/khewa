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


$sql = array();
$sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "ets_solo_user` (
            `id_ets_solo_user` INT(11) NOT NULL AUTO_INCREMENT,
            `id_customer` int(11) NOT NULL,
            `id_shop` int(11) NOT NULL,
            `id_country` int(11) NOT NULL,
            `identifier` varchar(100) NOT NULL,
            `network` varchar(3) NOT NULL,
            `profile_url` varchar(300) NOT NULL,
            `profile_img` varchar(300) NOT NULL,
            `discount_code` varchar(100) NOT NULL,
            `last_login_type` varchar(3) NOT NULL,
            `last_login_time` datetime NOT NULL,
            PRIMARY KEY (`id_ets_solo_user`),
            CONSTRAINT id_customer_identifier UNIQUE (id_customer, identifier)
        ) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4;
";
$sql[] = "CREATE INDEX idx_ets_solo_user_customer ON " . _DB_PREFIX_ . "ets_solo_user (id_ets_solo_user, id_customer);";
$sql[] = "CREATE INDEX idx_ets_solo_user_network ON " . _DB_PREFIX_ . "ets_solo_user (network);";
$sql[] = "CREATE INDEX idx_ets_solo_user_identifier ON " . _DB_PREFIX_ . "ets_solo_user (identifier);";
$sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "ets_solo_connect` (
            `id_ets_solo_connect` INT(11) NOT NULL AUTO_INCREMENT,
            `id_ets_solo_user` int(11) NOT NULL,
            `identifier` varchar(100) NOT NULL,
            `last_login_type` varchar(3) NOT NULL,
            `last_login_time` datetime NOT NULL,
            PRIMARY KEY (`id_ets_solo_connect`)
        ) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4;
";
$sql[] = "CREATE INDEX idx_ets_solo_connect_ets_solo_user ON " . _DB_PREFIX_ . "ets_solo_connect (id_ets_solo_connect, id_ets_solo_user);";

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
