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

// Need to be independant of the backup script to not be force to wait

require_once dirname(__FILE__) . '/../autoload.php';

$ntbr = new NtbrChild();
$page = 'backup_stop';

if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $ntbr->secure_key) {
    $message = $ntbr->l('Your secure key is invalid', $page);
    NtBackupAndRestore::lg($message);
    echo $message;

    return '';
}

if (!Module::isInstalled($ntbr->name)) {
    $message = $ntbr->l('Your module is not installed', $page);
    NtBackupAndRestore::lg($message);
    echo $message;

    return '';
}

$ntbr->log('ERR' . $ntbr->l('The backup was stopped manually', $page));
$handle = fopen(_PS_MODULE_DIR_ . $ntbr->name . '/' . NtbrCore::STOP_FILE, 'w');
fclose($handle);

return;
