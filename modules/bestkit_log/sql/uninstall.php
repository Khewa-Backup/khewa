<?php
/**
 * Database schema removal queries.
 *
 * @author Best-Kit
 * @copyright 2026 Best-Kit
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

return array(
    'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'bestkit_log_visit_detail`',
    'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'bestkit_log_visit`',
    'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'bestkit_log_login_attempt`',
    'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'bestkit_log_detail`',
    'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'bestkit_log`',
);
