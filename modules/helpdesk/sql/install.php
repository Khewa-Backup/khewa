<?php
/**
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    FMM Modules
*  @copyright © 2022 FME Modules
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'fmm_hd_departments (
        `departments_id` int(11) unsigned NOT NULL auto_increment,
        `department_email` varchar(255) NOT NULL,
        `department_status` smallint(6) NOT NULL default \'0\',
        `department_type` smallint(6) default \'0\',
        `department_signature` text,
        `department_email_temp` smallint(6) default NULL,
        `dept_new_message` smallint(6) default \'0\',
        `dept_new_ticket` smallint(6) default \'0\',
        `created_time` datetime default NULL,
        `update_time` datetime default NULL,
        PRIMARY KEY  (`departments_id`)
        ) ENGINE='._MYSQL_ENGINE_.' AUTO_INCREMENT=1 DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'fmm_hd_departments_lang (
        `departments_id` int(11) NOT NULL auto_increment,
        `id_lang` int(11) NOT NULL,
        `department_title` varchar(255) character set utf8 default NULL,
        PRIMARY KEY  (`departments_id`,`id_lang`)
        ) ENGINE='._MYSQL_ENGINE_.' AUTO_INCREMENT=1 DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'fmm_hd_departments_shop(
        `departments_id` int(11) NOT NULL,
        `id_shop` int(11) NOT NULL,
        PRIMARY KEY  (`departments_id`, `id_shop`),
        KEY `id_shop` (`id_shop`)
       ) ENGINE='._MYSQL_ENGINE_.' AUTO_INCREMENT=1 DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'fmm_hd_emailtemp (
       `emailtemp_id` int(11) unsigned NOT NULL auto_increment,
       `emailtemp_title` varchar(255) NOT NULL default \'\',
       `emailtemp_status` smallint(6) NOT NULL default \'0\',
       `created_time` datetime default NULL,
       `update_time` datetime default NULL,
        PRIMARY KEY  (`emailtemp_id`)
        ) ENGINE='._MYSQL_ENGINE_.' AUTO_INCREMENT=1 DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'fmm_hd_emailtemp_lang (         
        `emailtemp_id` int(11) NOT NULL auto_increment,
        `id_lang` int(11) NOT NULL,
        `new_ticket_user_subject` varchar(255) character set utf8 default NULL,
        `new_ticket_user_message` text,
        `new_message_user_subject` varchar(255) character set utf8 default NULL,
        `new_message_user_message` text,
        `close_ticket_user_subject` varchar(255) character set utf8 default NULL,
        `close_ticket_user_message` text,
        `new_ticket_staff_subject` varchar(255) character set utf8 default NULL,
        `new_ticket_staff_message` text,
        `new_message_staff_subject` varchar(255) character set utf8 default NULL,
        `new_message_staff_message` text,
        `new_reply_user_subject` varchar(255) default NULL,
        `new_reply_user_message` text,
        PRIMARY KEY  (`emailtemp_id`,`id_lang`)
      ) ENGINE='._MYSQL_ENGINE_.' AUTO_INCREMENT=1 DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'fmm_hd_premade (
        `premade_id` int(11) unsigned NOT NULL auto_increment,
        `premade_status` smallint(6) NOT NULL default \'0\',
        `created_time` datetime default NULL,
        `update_time` datetime default NULL,
        PRIMARY KEY  (`premade_id`)
       ) ENGINE='._MYSQL_ENGINE_.' AUTO_INCREMENT=1 DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'fmm_hd_premade_lang (
        `premade_id` int(11) NOT NULL auto_increment,
        `id_lang` int(11) NOT NULL,
        `premade_title` varchar(255) character set utf8 default NULL,
        `premade_content` text,
        PRIMARY KEY  (`premade_id`,`id_lang`)
        ) ENGINE='._MYSQL_ENGINE_.' AUTO_INCREMENT=1 DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'fmm_hd_priorities (
        `priorities_id` int(11) unsigned NOT NULL auto_increment,
        `priorities_status` smallint(6) NOT NULL default \'0\',
        `priority_color` varchar(255) default NULL,
        `created_time` datetime default NULL,
        `update_time` datetime default NULL,
        PRIMARY KEY  (`priorities_id`)
      ) ENGINE='._MYSQL_ENGINE_.' AUTO_INCREMENT=1 DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'fmm_hd_priorities_lang (    
        `priorities_id` int(11) NOT NULL auto_increment,
        `id_lang` int(11) NOT NULL,
        `priorities_title` varchar(255) character set utf8 default NULL,
        PRIMARY KEY  (`priorities_id`,`id_lang`)
       ) ENGINE='._MYSQL_ENGINE_.' AUTO_INCREMENT=1 DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'fmm_hd_ticketstatus (  
        `ticketstatus_id` int(11) unsigned NOT NULL auto_increment,
        `ticketstatus_status` smallint(6) NOT NULL default \'0\',
        `created_time` datetime default NULL,
        `update_time` datetime default NULL,
        PRIMARY KEY  (`ticketstatus_id`)
        ) ENGINE='._MYSQL_ENGINE_.' AUTO_INCREMENT=1 DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'fmm_hd_ticketstatus_lang (  
        `ticketstatus_id` int(11) NOT NULL auto_increment,
        `id_lang` int(11) NOT NULL,
        `ticketstatus_title` varchar(255) character set utf8 default NULL,
        PRIMARY KEY  (`ticketstatus_id`,`id_lang`)
        ) ENGINE='._MYSQL_ENGINE_.' AUTO_INCREMENT=1 DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'fmm_hd_tickets(
        `ticket_id` int(11) unsigned NOT NULL auto_increment,
        `t_department_id` int(11) unsigned NOT NULL,
        `t_priority_id` int(11) unsigned NOT NULL,
        `t_customer_id` int(11) default NULL,
        `ticket_subject` varchar(255) default NULL,
        `t_status_id` int(11) unsigned NOT NULL default \'0\',
        `ticket_attachment` varchar(255) default NULL,
        `t_created_time` datetime default NULL,
        `t_update_time` datetime default NULL,
        `last_response_client` datetime default NULL,
        `last_response_staff` datetime default NULL,
        `id_shop` int(11) default NULL,
        PRIMARY KEY  (`ticket_id`)
        ) ENGINE='._MYSQL_ENGINE_.' AUTO_INCREMENT=1 DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'fmm_hd_tickets_responses (
    `response_id` int(11) unsigned NOT NULL auto_increment,
    `r_ticket_id` int(11) unsigned NOT NULL,
    `r_message` text,
    `r_attachment` varchar(255) default NULL,
    `r_client_id` int(11) default NULL,
    `r_created_time` datetime default NULL,
    PRIMARY KEY  (`response_id`)
    ) ENGINE='._MYSQL_ENGINE_.' AUTO_INCREMENT=1 DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'fmm_hd_notes (
        `note_id` int(11) unsigned NOT NULL auto_increment,
        `note_ticket_id` int(11) unsigned NOT NULL,
        `note_title` varchar(255) default NULL,
        `note_content` text,
        `note_created` datetime default NULL,
        PRIMARY KEY  (`note_id`)
        ) ENGINE='._MYSQL_ENGINE_.' AUTO_INCREMENT=1 DEFAULT CHARSET=utf8';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
