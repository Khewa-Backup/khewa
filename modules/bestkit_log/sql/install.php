<?php

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'bestkit_log` (
            `id_bestkit_log` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_employee` int(11) NOT NULL,
            `email` text NOT NULL,
            `path` text NOT NULL, 
            `action_type` text NOT NULL, /*Delete, Edit, New*/
            `object` text NOT NULL, /*class of the object*/
            `id_object` int(11) NOT NULL,
            `description` text NOT NULL,
            `id_shop` int(11) NOT NULL,
            `date_add` datetime NOT NULL,
            `date_upd` datetime NOT NULL,
            PRIMARY KEY (`id_bestkit_log`)
        ) ENGINE = ' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'bestkit_log_detail` (
            `id_bestkit_log_detail` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_bestkit_log` INT( 11 ) UNSIGNED NOT NULL,
            `name` text NOT NULL,
            `old_value` text NOT NULL,
            `new_value` text NOT NULL,
            `class` text NOT NULL,
            PRIMARY KEY (`id_bestkit_log_detail`)
        ) ENGINE = ' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';


$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'bestkit_log_login_attempt` (
            `id_bestkit_log_login_attempt` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_employee` int(11) NOT NULL,
            `email` text NOT NULL,
            `name` text NOT NULL,
            `ip` text NOT NULL,
            `status` text NOT NULL,
            `user_agent` text NOT NULL,
            `date_add` datetime NOT NULL,
            `date_upd` datetime NOT NULL,
            PRIMARY KEY (`id_bestkit_log_login_attempt`)
        ) ENGINE = ' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';


$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'bestkit_log_visit` (
            `id_bestkit_log_visit` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_employee` int(11) NOT NULL,
            `email` text NOT NULL,
            `name` text NOT NULL,
            `ip` text NOT NULL,
            `session_start` datetime NOT NULL,
            `session_end` datetime NOT NULL,
            PRIMARY KEY (`id_bestkit_log_visit`)
        ) ENGINE = ' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'bestkit_log_visit_detail` (
            `id_bestkit_log_visit_detail` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_bestkit_log_visit` int(11) NOT NULL,
            `path` text NOT NULL, 
            `url` text NOT NULL, 
            `stay_duration` int(11) NOT NULL,
            PRIMARY KEY (`id_bestkit_log_visit_detail`)
        ) ENGINE = ' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';
