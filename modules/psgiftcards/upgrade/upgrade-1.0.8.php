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
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @param $module
 *
 * @return bool
 *              Get the old value & delete configuration name
 */
function upgrade_module_1_0_8($module)
{
    $sql = ' CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'psgiftcards_mail_lang` (
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

    return Db::getInstance()->Execute($sql);
}
