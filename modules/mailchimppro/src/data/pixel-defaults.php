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

/*
 * Single source of truth for the pixel-feature default Configuration values.
 *
 * Read by:
 *   - MailchimpProConfig::seedPixelDefaultsIfMissing() (canonical helper)
 *   - upgrade/upgrade-3.1.0.php (BO Upload safe — bypasses the stale-class
 *     fatal path; see CLAUDE.md "Composer autoload_files + upgrade scripts").
 *
 * Keys here MUST match the const string values declared in
 * src/MailchimpProConfig.php for the same Configuration row names.
 * Changing a default here is enough — both consumers read this array.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

return [
    'module-mailchimpproconfig-pixel-log-enabled' => 1,
    'module-mailchimpproconfig-pixel-log-max-rows' => 10000,
    'module-mailchimpproconfig-pixel-log-rate-limit-per-min' => 60,
    'module-mailchimpproconfig-pixel-log-level' => 'default',
    'module-mailchimpproconfig-pixel-autotrack-enabled' => 1,
    'module-mailchimpproconfig-pixel-identify-format' => 'EMAIL_SHA256',
    'module-mailchimpproconfig-pixel-identify-ajax-mode' => 0,
    'module-mailchimpproconfig-pixel-hook-displayproductadditionalinfo-enabled' => 0,
    'module-mailchimpproconfig-pixel-checkout-controllers' => 'opc',
    'module-mailchimpproconfig-pixel-order-confirmation-controllers' => '',
    'module-mailchimpproconfig-pixel-require-consent' => 0,
    'module-mailchimpproconfig-pixel-sdk-settle-delay-ms' => 500,
    'module-mailchimpproconfig-pixel-identify-on-forms' => 1,
    'module-mailchimpproconfig-pixel-identify-newsletter-selectors-custom' => '',
    'module-mailchimpproconfig-pixel-identify-restock-selectors-custom' => '',
    'module-mailchimpproconfig-pixel-identify-contact-selectors-custom' => '',
    'module-mailchimpproconfig-pixel-identify-forgot-password-selectors-custom' => '',
];
