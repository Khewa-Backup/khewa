<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a trade license awarded by
 * Garamo Online L.T.D.
 *
 * Any use, reproduction, modification or distribution
 * of this source file without the written consent of
 * Garamo Online L.T.D It Is prohibited.
 *
 * @author    ReactionCode <info@reactioncode.com>
 * @copyright 2015-2021 Garamo Online L.T.D
 * @license   Commercial license
 */

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'rc_pgtagmanager_orders_sent` (
    `id_order` INT(10) UNSIGNED NOT NULL,
    `id_shop` INT(10) UNSIGNED NOT NULL,
    `sent_from` VARCHAR(2) NOT NULL,
    `sent_at` DATETIME NOT NULL,
    PRIMARY KEY  (`id_order`, `id_shop`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'rc_pgtagmanager_client_id` (
    `id_customer` INT(10) UNSIGNED NOT NULL,
    `id_shop` INT(10) UNSIGNED NOT NULL,
    `client_id` VARCHAR(50) NOT NULL,
    PRIMARY KEY  (`id_customer`, `id_shop`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'INSERT IGNORE INTO `'._DB_PREFIX_.'rc_pgtagmanager_orders_sent` (id_order, id_shop, sent_from, sent_at)
    SELECT `id_order`, `id_shop`, "st" AS sent_from, `date_add` AS `sent_at`
    FROM `'._DB_PREFIX_.'orders`';

foreach ($sql as $query) {
    if (!Db::getInstance()->execute($query)) {
        return false;
    }
}
