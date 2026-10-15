<?php
/**
 * MailChimp
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Commercial License
 * you can't distribute, modify or sell this code
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file
 * If you need help please contact leo@prestachamps.com
 *
 * @author    Mailchimp
 * @copyright Mailchimp
 * @license   commercial
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Pixel-feature migration: creates ps_mailchimppro_pixel_log + seeds the
 * pixel config keys. Idempotent — every CREATE / INSERT is gated on
 * existence (IF NOT EXISTS / hasKey()).
 *
 * Stale-class-table safe: every Configuration key is a hardcoded literal
 * string, and the pixel defaults are read from src/data/pixel-defaults.php
 * (NOT through MailchimpProConfig::*). This avoids the PS 9.x BO Upload
 * trap where composer's autoload_files eagerly caches the OLD module
 * classes during Kernel preBoot (BEFORE ZIP extraction). Calling a method
 * or constant on those cached classes fatals when the new version added
 * something the old one didn't.
 * See CLAUDE.md gotcha "Composer autoload_files + upgrade scripts".
 *
 * @return bool
 */
function upgrade_module_3_1_0(Mailchimppro $object)
{
    $pf = _DB_PREFIX_ . 'mailchimppro_';

    $tableOk = Db::getInstance()->execute("
        CREATE TABLE IF NOT EXISTS `{$pf}pixel_log` (
            `id_log` INT NOT NULL AUTO_INCREMENT,
            `severity` TINYINT(1) NOT NULL DEFAULT 1,
            `source` ENUM('bo', 'fo') NOT NULL DEFAULT 'bo',
            `event_type` VARCHAR(50) NOT NULL,
            `message` TEXT NULL,
            `id_shop` INT NULL,
            `ip` VARCHAR(45) NULL,
            `date_add` DATETIME NOT NULL,
            PRIMARY KEY (`id_log`),
            INDEX `idx_pixel_log_date_add` (`date_add`),
            INDEX `idx_pixel_log_source` (`source`),
            INDEX `idx_pixel_log_ip_date` (`ip`, `date_add`)
        ) ENGINE = InnoDB;
    ");

    // Seed pixel feature defaults from the single source of truth.
    // hasKey-guarded with self-heal on empty-string / literal-'null' rows;
    // any genuine merchant value is preserved untouched.
    $seeds = require dirname(__FILE__) . '/../src/data/pixel-defaults.php';
    $seedOk = true;
    foreach ($seeds as $key => $value) {
        if (!Configuration::hasKey($key)) {
            $seedOk = Configuration::updateValue($key, $value) && $seedOk;
            continue;
        }
        $current = Configuration::get($key);
        if (false === $current || '' === $current || 'null' === $current) {
            $seedOk = Configuration::updateValue($key, $value) && $seedOk;
        }
    }

    // Each registerHook is AND-ed into the return value so a partial
    // failure (e.g. one hook fails to register due to a transient DB blip)
    // reports false to the caller. Mailchimppro::selfHealUpgradeIfNeeded()
    // halts on false and leaves ps_module.version at the previous value
    // so the next BO request retries this whole script instead of
    // marking the install "healthy" with a missing hook.
    $hooksOk = $object->registerHook('actionFrontControllerSetMedia')
        // PRODUCT_VIEWED pixel event — server-side path emits to
        // window.MC_PIXEL_EVENTS_QUEUE from inside the product page (and
        // re-fires on combination change in PS 1.7.8+ via the AJAX refresh
        // fragment). See hookDisplayProductAdditionalInfo() for the
        // 3-redundant-source design.
        && $object->registerHook('displayProductAdditionalInfo')
        // PURCHASED pixel event — primary trigger. PS dispatches this hook
        // from stock OrderConfirmationController with $params['order'] (the
        // Order object). Handler builds full payload server-side from
        // Order::* and emits inline <script> push to MC_PIXEL_EVENTS_QUEUE.
        // AJAX fallback (pixelorderinfo.php) covers 3rd-party confirmation
        // controllers that don't fire this hook.
        && $object->registerHook('displayOrderConfirmation');

    return (bool) $tableOk && $seedOk && $hooksOk;
}
