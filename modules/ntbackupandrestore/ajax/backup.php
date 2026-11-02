<?php
/**
 * 2013-2024 2N Technologies
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@2n-tech.com so we can send you a copy immediately.
 *
 * @author    2N Technologies <contact@2n-tech.com>
 * @copyright 2013-2024 2N Technologies
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
// Khewa PS 9.2: web calls go through the front controller "cron" (secret key checked there, then this file is
// included with $khewa_cron_ok set). Direct web calls are refused. From the command line it runs without a key:
//   php modules/ntbackupandrestore/ajax/backup.php [config id]
if (PHP_SAPI !== 'cli' && empty($khewa_cron_ok)) {
    header('HTTP/1.1 403 Forbidden');
    exit('Forbidden');
}

require_once dirname(__FILE__) . '/../autoload.php';

$ntbr = new NtbrChild();
$page = 'backup';

if (PHP_SAPI === 'cli') {
    // Khewa PS 9.2: command line = trusted (needs shell access already).
    $secure_key = $ntbr->secure_key;
    if (isset($argv[1]) && (int) $argv[1] > 0) {
        $id_config = (int) $argv[1];
    }
}

// Khewa PS 9.2: strict, timing-safe key check (the old check used loose != and ?? undefined $secure_key).
$khewa_given_key = isset($secure_key) ? (string) $secure_key : (string) Tools::getValue('secure_key');
if ($khewa_given_key === '' || !hash_equals((string) $ntbr->secure_key, $khewa_given_key)) {
    echo $ntbr->l('Your secure key is invalid', $page);

    return false;
}

// d($ntbr->secure_key);


if (!Module::isInstalled($ntbr->name)) {
    echo 'Your module is not installed';

    return false;
}

if (!Module::isEnabled($ntbr->name)) {
    echo 'Your module is not enabled';

    return false;
}

if (Tools::isSubmit('refresh')) {
    $refresh = (bool) Tools::getValue('refresh');
} else {
    $refresh = false;
}

if (Tools::isSubmit('config')) {
    $id_config = (int) Tools::getValue('config'); // Khewa PS 9.2: int
} elseif (!isset($id_config)) {
    $id_config = ConfigNtbr::getIdDefault();
}

if (!$id_config) {
    $error_config = $ntbr->l('The configuration is not valid, please check the advanced automation for correct link', $page);
    $ntbr->log('ERR' . $error_config);
    echo $error_config;

    return false;
}

$current_time = time();
$config = new ConfigNtbr($id_config);
$time_between_backups = $config->time_between_backups;

if ($time_between_backups <= 0) {
    $time_between_backups = NtbrCore::MIN_TIME_NEW_BACKUP;
}

$ntbr->config = $config;

$ntbr_ongoing = GlobConfNtbr::get('NTBR_ONGOING');

if (($current_time - $ntbr_ongoing >= $time_between_backups) || $refresh) {
    GlobConfNtbr::set('NTBR_ONGOING', time());
    $filesize = $ntbr->backup($id_config, $refresh, true);
    echo $ntbr->l('Filesize:', $page) . ' ' . $filesize;

    return true;
} else {
    $time_to_wait = $time_between_backups - ($current_time - $ntbr_ongoing);
    $error_time = sprintf(
        $ntbr->l('For security reason, some time is needed between two backups. Please wait %d seconds', $page),
        $time_to_wait
    );
    $ntbr->log('ERR' . $error_time);
    echo $error_time;

    return false;
}

echo 'ERROR';

return false;
