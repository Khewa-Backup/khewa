<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
$sql = [];

$sql[] = ' CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'psgiftcards` (
        `id_giftcard` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `id_product` int(10) unsigned NOT NULL,
        `is_active` int(10) unsigned NOT NULL,
        `id_shop` int(10) unsigned NOT NULL,
        PRIMARY KEY (`id_giftcard`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;';

$sql[] = ' CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'psgiftcards_history` (
        `id_giftcard_history` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `id_giftcard` int(10) unsigned NOT NULL,
        `id_product` int(10) unsigned NOT NULL,
        `amount` VARCHAR(250),
        `id_customer` int(10) NOT NULL,
        `id_order` int(10) NOT NULL,
        `type` int(10),
        `buyerName` VARCHAR(250),
        `recipientName` VARCHAR(250),
        `recipientMail` VARCHAR(250),
        `send_date` datetime,
        `id_state` int(10),
        `text` VARCHAR(4000),
        `image` VARCHAR(250),
        `sendLater` int(10),
        `id_cartRule` int(10),
        `validity_begin` datetime,
        `validity_end` datetime,
        `isUse` int(10),
        `id_shop` int(10) unsigned NOT NULL,
        PRIMARY KEY (`id_giftcard_history`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;';

$sql[] = ' CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'psgiftcards_mail_lang` (
        `id_template` int(10) unsigned NOT NULL,
        `id_lang` int(10) unsigned NOT NULL,
        `lang_iso` varchar(3) NOT NULL,
        `action` varchar(255) NULL,
        `email_subject` varchar(255) NOT NULL,
        `email_content` text(2500),
        `email_discount` text(2500),
        `email_cta` varchar(25) NOT NULL,
        `email_unsubscribe` text(2500),
        `email_unsubscribe_text` varchar(100) NOT NULL,
        PRIMARY KEY (`id_template`, `id_lang`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;';

$sql[] = 'INSERT INTO `' . _DB_PREFIX_ . 'psgiftcards_mail_lang` values
        (0,
        ' . $this->context->language->id . ',
        "' . Language::getIsoById($this->context->language->id) . '",
        "To configure",
        "To configure",
        "To configure",
        "To configure",
        "",
        "To configure",
        "To configure")';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
