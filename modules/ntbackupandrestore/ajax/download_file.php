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
$page = 'download_file';

if (!Module::isInstalled($ntbr->name)) {
    echo 'Your module is not installed';

    return false;
}

if (Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $ntbr->secure_key) {
    $secure_key = Tools::getValue('secure_key');
    $secure_key_test = hash('sha512', $secure_key . $ntbr->secure_key . GlobConfNtbr::get('NTBR_SEL'));
    $secure_key_test_temp = hash('sha512', $secure_key . $ntbr->secure_key . GlobConfNtbr::get('NTBR_SEL_TEMP'));

    // Must be eather from link generated in automation tab (not temp) or ajax from admin (temp)
    if ($secure_key_test_temp != GlobConfNtbr::get('NTBR_HASH_TEMP')
        && $secure_key_test != GlobConfNtbr::get('NTBR_HASH')
    ) {
        sleep(5); // Limit brute force
        echo $ntbr->l('Forbidden', $page);

        return false;
    }
} else {
    echo $ntbr->l('Forbidden', $page);

    return false;
}

if (Tools::isSubmit('backup')) {
    if (!Tools::isSubmit('nb')) {
        echo $ntbr->l('Error', $page);

        return false;
    }

    $old_backups = $ntbr->findOldBackups();
    $nb_file = Tools::getValue('nb');
    $nb_detail = explode('.', $nb_file);
    $backup = '';

    if (!isset($nb_detail[0])) {
        $ntbr->log('ERR' . $ntbr->l('Error, the number of the backup is invalid', $page));
        echo $ntbr->l('Error', $page);

        return false;
    }

    if (!isset($old_backups[$nb_detail[0]])) {
        $ntbr->log('ERR' . $ntbr->l('Error, the backup asked was not found', $page));
        echo $ntbr->l('Error', $page);

        return false;
    }

    if (!isset($old_backups[$nb_detail[0]]['backup_dir']) || !is_dir($old_backups[$nb_detail[0]]['backup_dir'])) {
        $ntbr->log('ERR' . $ntbr->l('Error, the backup directory is invalid', $page));
        echo $ntbr->l('Error', $page);

        return false;
    }

    $backup_dir = $old_backups[$nb_detail[0]]['backup_dir'];

    // If file is only a part of the backup
    if (isset($nb_detail[1])) {
        if (!isset($old_backups[$nb_detail[0]]['part'][$nb_file]['name'])) {
            $ntbr->log('ERR' . $ntbr->l('Error, the backup part is invalid', $page));
            echo $ntbr->l('Error', $page);

            return false;
        }

        $backup = $old_backups[$nb_detail[0]]['part'][$nb_file]['name'];
    } else {
        if (!isset($old_backups[$nb_detail[0]]['name'])) {
            $ntbr->log('ERR' . $ntbr->l('Error, the backup is invalid', $page));
            echo $ntbr->l('Error', $page);

            return false;
        }

        $backup = $old_backups[$nb_detail[0]]['name'];
    }

    $ntbr->downloadFile($backup_dir . $backup, 'application/x-tar');
} elseif (Tools::isSubmit('log')) {
    $log_file = NtBackupAndRestore::getModuleBackupDirectory() . 'log.txt';
    if (Apparatus::checkFileExists($log_file)) {
        $ntbr->downloadFile($log_file, 'application/octet-stream'); // ou text/plain
    } else {
        echo $ntbr->l('No log file available', $page);

        return false;
    }
} elseif (Tools::isSubmit('restore')) {
    $ntbr->downloadFile(
        _PS_ROOT_DIR_ . '/modules/' . $ntbr->name . '/restore.txt',
        'application/octet-stream',
        'restore.php'
    ); // ou application/octet-stream
} else {
    echo $ntbr->l('Error', $page);

    return false;
}
