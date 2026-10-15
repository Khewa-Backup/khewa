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
 * Drop legacy MAILCHIMP_PIXEL_AUTO_ENABLE_DONE row (constant removed).
 *
 * @return bool
 */
function upgrade_module_3_1_2(Mailchimppro $object)
{
    Configuration::deleteByName('module-mailchimpproconfig-pixel-auto-enable-done');

    return true;
}
