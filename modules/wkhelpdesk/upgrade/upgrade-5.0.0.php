<?php
/**
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License version 3.0
* that is bundled with this package in the file LICENSE.txt
* It is also available through the world-wide-web at this URL:
* https://opensource.org/licenses/AFL-3.0
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to a newer
* versions in the future. If you wish to customize this module for your
* needs please refer to CustomizationPolicy.txt file inside our module for more information.
*
* @author Webkul IN
* @copyright Since 2010 Webkul
* @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_5_0_0($module)
{
    // delete then create wk_hd_status_code
    Db::getInstance()->execute(
        'DROP TABLE IF EXISTS
        `'._DB_PREFIX_.'wk_hd_status_code`'
    );
    Db::getInstance()->execute(
        'Truncate
        `'._DB_PREFIX_.'wk_hd_status_mapping`'
    );
    Db::getInstance()->execute(
        'Truncate
        `'._DB_PREFIX_.'wk_hd_status_mapping_shop`'
    );
    //then add the table and insert data
    $wkQueries = array(
        "ALTER TABLE `"._DB_PREFIX_."wk_hd_ticket`
        ADD COLUMN `first_name` varchar(32) character set utf8 NOT NULL AFTER `hd_id_customer`",
        "ALTER TABLE `"._DB_PREFIX_."wk_hd_ticket`
        ADD COLUMN `last_name` varchar(32) character set utf8 NOT NULL AFTER `first_name`",
        "ALTER TABLE `"._DB_PREFIX_."wk_hd_customer`
        ADD COLUMN `is_spam` int(10) unsigned NOT NULL DEFAULT '0' AFTER `id_ps_customer`",
        "ALTER TABLE `"._DB_PREFIX_."wk_hd_status_mapping_shop`
        ADD COLUMN `id_status` int(10) unsigned NOT NULL AFTER `id_shop`",
        "ALTER TABLE `"._DB_PREFIX_."wk_hd_status_mapping_shop`
        ADD COLUMN `id_status_selected` int(10) unsigned NOT NULL AFTER `id_status`",
        "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_hd_status_code` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `date_add` datetime NOT NULL,
            `date_upd` datetime NOT NULL,
            PRIMARY KEY  (`id`)
        ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",

        "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_hd_status_code_shop` (
            `id` int(10) unsigned NOT NULL,
            `id_shop` int(11) unsigned DEFAULT '1',
            PRIMARY KEY (`id`, `id_shop`)
        ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",

        "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_hd_status_code_lang` (
            `id` int(10) unsigned NOT NULL,
            `id_shop` int(11) unsigned DEFAULT '1',
            `id_lang` int(10) unsigned NOT NULL,
            `ticket_status` varchar(50) character set utf8 NOT NULL,
            PRIMARY KEY (`id`, `id_lang`, `id_shop`)
        ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
        "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_customer_message_sync_imap` (
            `md5_header` varbinary(32),
            PRIMARY KEY  (`md5_header`)
        ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
        "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_customer_reply_sync_imap` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `id_ticket` int(11) unsigned NOT NULL DEFAULT '0',
            `message_id` varchar(256) character set utf8 NOT NULL,
            `reply_id` varchar(256) character set utf8,
            `date_add` datetime NOT NULL,
            `date_upd` datetime NOT NULL,
            PRIMARY KEY  (`id`)
        ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
        "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_customer_reply_sync_imap_shop` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `id_shop` int(11) unsigned DEFAULT '1',
            `id_ticket` int(11) unsigned NOT NULL DEFAULT '0',
            `message_id` varchar(256) character set utf8 NOT NULL,
            `reply_id` varchar(256) character set utf8,
            PRIMARY KEY  (`id`, `id_shop`)
        ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
    );
    $wkDatabaseInstance = Db::getInstance();
    $wkSuccess = true;
    foreach ($wkQueries as $wkQuery) {
        $wkSuccess &= $wkDatabaseInstance->execute(trim($wkQuery));
    }
    if ($wkSuccess) {
        $languages = Language::getLanguages(true);
        $status = array('Open', 'Closed', 'Answered', 'Pending', 'Resolved', 'Spam');
        foreach ($status as $sts) {
            $objStatus = new WkHdStatusCode();
            foreach ($languages as $language) {
                $objStatus->ticket_status[$language['id_lang']] = pSQL($sts);
            }
            $objStatus->save();
            $savedIdQuery = $objStatus->id;
            $mapObj = new WkHdStatusMapping();
            $mapObj->id_status = (int) $savedIdQuery;
            $mapObj->id_status_selected = (int) $savedIdQuery;
            $mapObj->save();
        }
        return (
            $module->installTab('AdminSpamTicket', 'Spam User Tickets', 'AdminHelpDeskManagement')
            && $module->installTab('AdminTicketStatus', 'Ticket Status', 'AdminHelpDeskManagement')
        );
    }
    return true;
}
