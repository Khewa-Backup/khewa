<?php
/**
 * Upgrade the module schema and hooks to version 2.0.0.
 *
 * @author Best-Kit
 * @copyright 2026 Best-Kit
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_0_0($module)
{
    if (!Db::getInstance()->execute(
        'UPDATE `' . _DB_PREFIX_ . 'bestkit_log_visit`
        SET `session_end` = `session_start`
        WHERE `session_end` IS NULL
            OR `session_end` = \'0000-00-00 00:00:00\''
    )) {
        return false;
    }

    $queries = array(
        'ALTER TABLE `' . _DB_PREFIX_ . 'bestkit_log`
            MODIFY `id_employee` INT UNSIGNED NOT NULL DEFAULT 0,
            MODIFY `email` VARCHAR(255) NOT NULL DEFAULT \'\',
            MODIFY `action_type` VARCHAR(32) NOT NULL,
            MODIFY `object` VARCHAR(255) NOT NULL,
            MODIFY `id_object` INT UNSIGNED NOT NULL DEFAULT 0,
            MODIFY `id_shop` INT UNSIGNED NOT NULL DEFAULT 0',
        'ALTER TABLE `' . _DB_PREFIX_ . 'bestkit_log_detail`
            MODIFY `name` VARCHAR(255) NOT NULL,
            MODIFY `old_value` LONGTEXT NOT NULL,
            MODIFY `new_value` LONGTEXT NOT NULL,
            MODIFY `class` VARCHAR(255) NOT NULL',
        'ALTER TABLE `' . _DB_PREFIX_ . 'bestkit_log_login_attempt`
            MODIFY `id_employee` INT UNSIGNED NOT NULL DEFAULT 0,
            MODIFY `email` VARCHAR(255) NOT NULL DEFAULT \'\',
            MODIFY `name` VARCHAR(255) NOT NULL DEFAULT \'\',
            MODIFY `ip` VARCHAR(45) NOT NULL DEFAULT \'\',
            MODIFY `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0',
        'ALTER TABLE `' . _DB_PREFIX_ . 'bestkit_log_visit`
            MODIFY `id_employee` INT UNSIGNED NOT NULL DEFAULT 0,
            MODIFY `email` VARCHAR(255) NOT NULL DEFAULT \'\',
            MODIFY `name` VARCHAR(255) NOT NULL DEFAULT \'\',
            MODIFY `ip` VARCHAR(45) NOT NULL DEFAULT \'\'',
        'ALTER TABLE `' . _DB_PREFIX_ . 'bestkit_log_visit_detail`
            MODIFY `id_bestkit_log_visit` INT UNSIGNED NOT NULL,
            MODIFY `stay_duration` INT UNSIGNED NOT NULL DEFAULT 0',
    );

    foreach ($queries as $query) {
        if (!Db::getInstance()->execute($query)) {
            return false;
        }
    }

    if ($module->isRegisteredInHook('actionObjectAddBefore')) {
        $module->unregisterHook('actionObjectAddBefore');
    }

    $requiredHooks = array(
        'actionObjectAddAfter',
        'actionObjectUpdateBefore',
        'actionObjectUpdateAfter',
        'actionObjectDeleteBefore',
        'actionObjectDeleteAfter',
        'actionAdminControllerSetMedia',
    );

    foreach ($requiredHooks as $hook) {
        if (!$module->isRegisteredInHook($hook) && !$module->registerHook($hook)) {
            return false;
        }
    }

    return true;
}
