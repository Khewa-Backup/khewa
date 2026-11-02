<?php
/**
 * Database schema installation queries.
 *
 * @author Best-Kit
 * @copyright 2026 Best-Kit
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

return array(
    'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'bestkit_log` (
            `id_bestkit_log` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_employee` INT UNSIGNED NOT NULL DEFAULT 0,
            `email` VARCHAR(255) NOT NULL DEFAULT \'\',
            `path` TEXT NOT NULL,
            `action_type` VARCHAR(32) NOT NULL,
            `object` VARCHAR(255) NOT NULL,
            `id_object` INT UNSIGNED NOT NULL DEFAULT 0,
            `description` TEXT NOT NULL,
            `id_shop` INT UNSIGNED NOT NULL DEFAULT 0,
            `date_add` DATETIME NOT NULL,
            `date_upd` DATETIME NOT NULL,
            PRIMARY KEY (`id_bestkit_log`),
            KEY `bestkit_log_employee` (`id_employee`),
            KEY `bestkit_log_object` (`object`, `id_object`),
            KEY `bestkit_log_date_add` (`date_add`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',

    'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'bestkit_log_detail` (
            `id_bestkit_log_detail` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_bestkit_log` INT UNSIGNED NOT NULL,
            `name` VARCHAR(255) NOT NULL,
            `old_value` LONGTEXT NOT NULL,
            `new_value` LONGTEXT NOT NULL,
            `class` VARCHAR(255) NOT NULL,
            PRIMARY KEY (`id_bestkit_log_detail`),
            KEY `bestkit_log_detail_log` (`id_bestkit_log`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',

    'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'bestkit_log_login_attempt` (
            `id_bestkit_log_login_attempt` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_employee` INT UNSIGNED NOT NULL DEFAULT 0,
            `email` VARCHAR(255) NOT NULL DEFAULT \'\',
            `name` VARCHAR(255) NOT NULL DEFAULT \'\',
            `ip` VARCHAR(45) NOT NULL DEFAULT \'\',
            `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
            `user_agent` TEXT NOT NULL,
            `date_add` DATETIME NOT NULL,
            `date_upd` DATETIME NOT NULL,
            PRIMARY KEY (`id_bestkit_log_login_attempt`),
            KEY `bestkit_log_login_employee` (`id_employee`),
            KEY `bestkit_log_login_date_add` (`date_add`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',

    'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'bestkit_log_visit` (
            `id_bestkit_log_visit` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_employee` INT UNSIGNED NOT NULL DEFAULT 0,
            `email` VARCHAR(255) NOT NULL DEFAULT \'\',
            `name` VARCHAR(255) NOT NULL DEFAULT \'\',
            `ip` VARCHAR(45) NOT NULL DEFAULT \'\',
            `session_start` DATETIME NOT NULL,
            `session_end` DATETIME NOT NULL,
            PRIMARY KEY (`id_bestkit_log_visit`),
            KEY `bestkit_log_visit_employee` (`id_employee`),
            KEY `bestkit_log_visit_start` (`session_start`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',

    'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'bestkit_log_visit_detail` (
            `id_bestkit_log_visit_detail` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_bestkit_log_visit` INT UNSIGNED NOT NULL,
            `path` TEXT NOT NULL,
            `url` TEXT NOT NULL,
            `stay_duration` INT UNSIGNED NOT NULL DEFAULT 0,
            PRIMARY KEY (`id_bestkit_log_visit_detail`),
            KEY `bestkit_log_visit_detail_visit` (`id_bestkit_log_visit`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
);
