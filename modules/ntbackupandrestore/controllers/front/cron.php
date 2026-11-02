<?php
/**
 * Khewa PS 9.2: new file. Cron entry point for NT Backup And Restore.
 *
 * PS 9.2 blocks direct calls to modules/*.php, so the old ajax/backup.php URL no longer works from the web.
 * This front controller replaces it:
 *   https://<shop>/index.php?fc=module&module=ntbackupandrestore&controller=cron&secure_key=<key>[&config=<id>]
 * <key> = md5(_COOKIE_KEY_ . 'ntbackupandrestore_khewa_cron'), shown in the module's Automation tab.
 * A wrong or missing key gets a 403 after a short pause.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class NtbackupandrestoreCronModuleFrontController extends ModuleFrontController
{
    /** @var bool */
    public $auth = false;

    /** @var bool */
    public $ajax = true;

    public function init()
    {
        // No theme, no cart, no cookie work needed: check the key and run the backup.
        $given = Tools::getValue('secure_key');
        $expected = (string) $this->module->secure_key;

        if (!is_string($given) || $given === '' || $expected === '' || !hash_equals($expected, $given)) {
            sleep(2); // slow down guessing
            header('HTTP/1.1 403 Forbidden');
            exit('Forbidden');
        }

        header('Content-Type: text/plain; charset=utf-8');
        header('X-Robots-Tag: noindex, nofollow');

        $khewa_cron_ok = true;
        $secure_key = $given;
        if (Tools::getValue('config')) {
            $id_config = (int) Tools::getValue('config');
        }

        include _PS_MODULE_DIR_ . 'ntbackupandrestore/ajax/backup.php';

        exit;
    }
}
