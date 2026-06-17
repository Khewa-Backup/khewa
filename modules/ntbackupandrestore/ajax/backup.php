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
require_once dirname(__FILE__) . '/../autoload.php';

$ntbr = new NtbrChild();
$page = 'backup';

// d($ntbr->secure_key);

if ((!Tools::isSubmit('secure_key') && !isset($secure_key)) || (Tools::getValue('secure_key') != $ntbr->secure_key && $secure_key != $ntbr->secure_key)) {
    echo $ntbr->l('Your secure key is invalid', $page);

    return false;
}

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
    $id_config = Tools::getValue('config');
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
