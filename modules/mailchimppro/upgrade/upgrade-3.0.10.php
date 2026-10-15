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

function upgrade_module_3_0_10(Mailchimppro $object)
{
    $request = 'SELECT count(*) FROM `' . _DB_PREFIX_ . 'mailchimppro_jobs`';

    $jobs_count = Db::getInstance()->getValue($request);

    $result = Db::getInstance()->delete('mailchimppro_jobs');

    // Configuration key strings inlined rather than referenced via
    // MailchimpProConfig::DELETED_JOBS_ON_UPGRADE_* — the latter would fatal
    // on PS 9.x BO Upload if a pre-v3.0.10 MailchimpProConfig is cached in
    // PHP's class table by composer's autoload_files (which pre-loads it
    // during Kernel preBoot, before ZIP extraction).
    // See CLAUDE.md gotcha "Composer autoload_files + upgrade scripts".
    Configuration::updateGlobalValue('module-mailchimpproconfig-deleted-jobs-on-upgrade-count', $jobs_count);
    Configuration::updateGlobalValue('module-mailchimpproconfig-deleted-jobs-on-upgrade-date', date('Y-m-d H:i:s'));
    Configuration::updateGlobalValue('module-mailchimpproconfig-deleted-jobs-on-upgrade-accepted-message-date', null);
    Configuration::updateGlobalValue('module-mailchimpproconfig-deleted-jobs-on-upgrade-accepted-message-employee', null);

    return true;
}
