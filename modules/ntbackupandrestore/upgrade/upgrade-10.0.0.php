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
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/../autoload.php';

function upgrade_module_10_0_0($module)
{
    if (!Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ntbr_backup`;')) {
        return false;
    }

    $create_table_backups = Db::getInstance()->execute('
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ntbr_backups` (
            `id_ntbr_backups`   int(10)         unsigned    NOT NULL    auto_increment,
            `id_ntbr_config`    int(10)         unsigned    NOT NULL,
            `backup_name`       varchar(255)                NOT NULL    DEFAULT "",
            `comment`           text                        NOT NULL,
            `date_add`          datetime,
            `date_upd`          datetime,
            PRIMARY KEY (`id_ntbr_backups`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 ;
    ');

    if (!Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ntbr_config`;')) {
        return false;
    }

    $create_table_config = Db::getInstance()->execute('
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ntbr_config` (
            `id_ntbr_config`                int(10)         unsigned    NOT NULL    auto_increment,
            `is_default`                    tinyint(1)                  NOT NULL    DEFAULT "0",
            `name`                          varchar(255)                NOT NULL,
            `type_backup`                   varchar(255)                NOT NULL    DEFAULT "complete",
            `nb_backup`                     int(10)         unsigned    NOT NULL    DEFAULT "1",
            `send_email`                    tinyint(1)                  NOT NULL    DEFAULT "0",
            `email_only_error`              tinyint(1)                  NOT NULL    DEFAULT "0",
            `mail_backup`                   varchar(255)                NOT NULL,
            `send_restore`                  tinyint(1)                  NOT NULL    DEFAULT "0",
            `activate_log`                  tinyint(1)                  NOT NULL    DEFAULT "0",
            `part_size`                     int(10)         unsigned    NOT NULL    DEFAULT "0",
            `max_file_to_backup`            int(10)         unsigned    NOT NULL    DEFAULT "0",
            `dump_max_values`               int(10)         unsigned    NOT NULL    DEFAULT "100",
            `dump_lines_limit`              int(10)         unsigned    NOT NULL    DEFAULT "25000",
            `disable_refresh`               tinyint(1)                  NOT NULL    DEFAULT "0",
            `time_between_refresh`          int(10)         unsigned    NOT NULL    DEFAULT "25",
            `time_pause_between_refresh`    int(10)         unsigned    NOT NULL    DEFAULT "0",
            `time_between_progress_refresh` int(10)         unsigned    NOT NULL    DEFAULT "1",
            `disable_server_timeout`        tinyint(1)                  NOT NULL    DEFAULT "0",
            `increase_server_memory`        tinyint(1)                  NOT NULL    DEFAULT "0",
            `server_memory_value`           int(10)         unsigned    NOT NULL    DEFAULT "128",
            `dump_low_interest_tables`      tinyint(1)                  NOT NULL    DEFAULT "0",
            `maintenance`                   tinyint(1)                  NOT NULL    DEFAULT "0",
            `time_between_backups`          int(10)         unsigned    NOT NULL    DEFAULT "600",
            `activate_xsendfile`            tinyint(1)                  NOT NULL    DEFAULT "0",
            `ignore_product_image`          tinyint(1)                  NOT NULL    DEFAULT "0",
            `ignore_compression`            tinyint(1)                  NOT NULL    DEFAULT "0",
            `delete_local_backup`           tinyint(1)                  NOT NULL    DEFAULT "0",
            `ignore_directories`            text,
            `ignore_file_types`             text,
            `ignore_tables`                 text,
            `date_add`                      datetime,
            `date_upd`                      datetime,
            PRIMARY KEY (`id_ntbr_config`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 ;
    ');

    if (!$create_table_backups || !$create_table_config) {
        return false;
    }

    $now = date('Y-m-d H:i:s');
    $id_shop = (int) Configuration::get('PS_SHOP_DEFAULT');
    $id_shop_group = (int) Shop::getGroupFromShop($id_shop);

    if (Configuration::hasKey('NTBR_TIME_BTW_PROGRESS_REFRESH')) {
        $time_between_progress_refresh = (int) (Configuration::get('NTBR_TIME_BTW_PROGRESS_REFRESH') ? Configuration::get('NTBR_TIME_BTW_PROGRESS_REFRESH') : Configuration::get('NTBR_TIME_BTW_PROGRESS_REFRESH', null, $id_shop_group, $id_shop));
    } else {
        $time_between_progress_refresh = (int) (Configuration::get('NTBR_TIME_BETWEEN_PROGRESS_REFRESH') ? Configuration::get('NTBR_TIME_BETWEEN_PROGRESS_REFRESH') : Configuration::get('NTBR_TIME_BETWEEN_PROGRESS_REFRESH', null, $id_shop_group, $id_shop));
    }

    $nb_backup = (int) (Configuration::get('NB_KEEP_BACKUP') ? Configuration::get('NB_KEEP_BACKUP') : Configuration::get('NB_KEEP_BACKUP', null, $id_shop_group, $id_shop));
    $nb_backup_file = (int) (Configuration::get('NB_KEEP_BACKUP_FILE') ? Configuration::get('NB_KEEP_BACKUP_FILE') : Configuration::get('NB_KEEP_BACKUP_FILE', null, $id_shop_group, $id_shop));
    $nb_backup_base = (int) (Configuration::get('NB_KEEP_BACKUP_BASE') ? Configuration::get('NB_KEEP_BACKUP_BASE') : Configuration::get('NB_KEEP_BACKUP_BASE', null, $id_shop_group, $id_shop));
    $send_email = (int) (bool) (Configuration::get('SEND_EMAIL') ? Configuration::get('SEND_EMAIL') : Configuration::get('SEND_EMAIL', null, $id_shop_group, $id_shop));
    $email_only_error = (int) (bool) (Configuration::get('EMAIL_ONLY_ERROR') ? Configuration::get('EMAIL_ONLY_ERROR') : Configuration::get('EMAIL_ONLY_ERROR', null, $id_shop_group, $id_shop));
    $mail_backup = (Configuration::get('MAIL_BACKUP') ? Configuration::get('MAIL_BACKUP') : Configuration::get('MAIL_BACKUP', null, $id_shop_group, $id_shop));
    $send_restore = (int) (bool) (Configuration::get('SEND_RESTORE') ? Configuration::get('SEND_RESTORE') : Configuration::get('SEND_RESTORE', null, $id_shop_group, $id_shop));
    $activate_log = (int) (bool) (Configuration::get('ACTIVATE_LOG') ? Configuration::get('ACTIVATE_LOG') : Configuration::get('ACTIVATE_LOG', null, $id_shop_group, $id_shop));
    $part_size = (int) (Configuration::get('NTBR_PART_SIZE') ? Configuration::get('NTBR_PART_SIZE') : Configuration::get('NTBR_PART_SIZE', null, $id_shop_group, $id_shop));
    $max_file_to_backup = (int) (Configuration::get('NTBR_MAX_FILE_TO_BACKUP') ? Configuration::get('NTBR_MAX_FILE_TO_BACKUP') : Configuration::get('NTBR_MAX_FILE_TO_BACKUP', null, $id_shop_group, $id_shop));
    $dump_max_values = (int) (Configuration::get('NTBR_DUMP_MAX_VALUES') ? Configuration::get('NTBR_DUMP_MAX_VALUES') : Configuration::get('NTBR_DUMP_MAX_VALUES', null, $id_shop_group, $id_shop));
    $disable_refresh = (int) (bool) (Configuration::get('NTBR_DISABLE_REFRESH') ? Configuration::get('NTBR_DISABLE_REFRESH') : Configuration::get('NTBR_DISABLE_REFRESH', null, $id_shop_group, $id_shop));
    $time_between_refresh = (int) (Configuration::get('NTBR_TIME_BETWEEN_REFRESH') ? Configuration::get('NTBR_TIME_BETWEEN_REFRESH') : Configuration::get('NTBR_TIME_BETWEEN_REFRESH', null, $id_shop_group, $id_shop));
    $time_pause_between_refresh = (int) (Configuration::get('NTBR_TIME_PAUSE_BETWEEN_REFRESH') ? Configuration::get('NTBR_TIME_PAUSE_BETWEEN_REFRESH') : Configuration::get('NTBR_TIME_PAUSE_BETWEEN_REFRESH', null, $id_shop_group, $id_shop));
    $disable_server_timeout = (int) (bool) (Configuration::get('NTBR_DISABLE_SERVER_TIMEOUT') ? Configuration::get('NTBR_DISABLE_SERVER_TIMEOUT') : Configuration::get('NTBR_DISABLE_SERVER_TIMEOUT', null, $id_shop_group, $id_shop));
    $increase_server_memory = (int) (bool) (Configuration::get('NTBR_INCREASE_SERVER_MEMORY') ? Configuration::get('NTBR_INCREASE_SERVER_MEMORY') : Configuration::get('NTBR_INCREASE_SERVER_MEMORY', null, $id_shop_group, $id_shop));
    $server_memory_value = (int) (Configuration::get('NTBR_SERVER_MEMORY_VALUE') ? Configuration::get('NTBR_SERVER_MEMORY_VALUE') : Configuration::get('NTBR_SERVER_MEMORY_VALUE', null, $id_shop_group, $id_shop));
    $dump_low_interest_tables = (int) (bool) (Configuration::get('DUMP_LOW_INTEREST_TABLES') ? Configuration::get('DUMP_LOW_INTEREST_TABLES') : Configuration::get('DUMP_LOW_INTEREST_TABLES', null, $id_shop_group, $id_shop));
    $maintenance = (int) (bool) (Configuration::get('NTBR_MAINTENANCE') ? Configuration::get('NTBR_MAINTENANCE') : Configuration::get('NTBR_MAINTENANCE', null, $id_shop_group, $id_shop));
    $time_between_backups = (int) (Configuration::get('NTBR_TIME_BETWEEN_BACKUPS') ? Configuration::get('NTBR_TIME_BETWEEN_BACKUPS') : Configuration::get('NTBR_TIME_BETWEEN_BACKUPS', null, $id_shop_group, $id_shop));
    $activate_xsendfile = (int) (bool) (Configuration::get('ACTIVATE_XSENDFILE') ? Configuration::get('ACTIVATE_XSENDFILE') : Configuration::get('ACTIVATE_XSENDFILE', null, $id_shop_group, $id_shop));
    $ignore_product_image = (int) (bool) (Configuration::get('IGNORE_PRODUCT_IMAGE') ? Configuration::get('IGNORE_PRODUCT_IMAGE') : Configuration::get('IGNORE_PRODUCT_IMAGE', null, $id_shop_group, $id_shop));
    $ignore_compression = (int) (bool) (Configuration::get('IGNORE_COMPRESSION') ? Configuration::get('IGNORE_COMPRESSION') : Configuration::get('IGNORE_COMPRESSION', null, $id_shop_group, $id_shop));
    $delete_local_backup = (int) (bool) (Configuration::get('NTBR_DELETE_LOCAL_BACKUP') ? Configuration::get('NTBR_DELETE_LOCAL_BACKUP') : Configuration::get('NTBR_DELETE_LOCAL_BACKUP', null, $id_shop_group, $id_shop));
    $ignore_directories = (Configuration::get('NTBR_IGNORE_DIRECTORIES') ? Configuration::get('NTBR_IGNORE_DIRECTORIES') : Configuration::get('NTBR_IGNORE_DIRECTORIES', null, $id_shop_group, $id_shop));
    $ignore_files_types = (Configuration::get('NTBR_IGNORE_FILES_TYPES') ? Configuration::get('NTBR_IGNORE_FILES_TYPES') : Configuration::get('NTBR_IGNORE_FILES_TYPES', null, $id_shop_group, $id_shop));
    $ignore_tables = (Configuration::get('NTBR_IGNORE_TABLES') ? Configuration::get('NTBR_IGNORE_TABLES') : Configuration::get('NTBR_IGNORE_TABLES', null, $id_shop_group, $id_shop));

    // If there is an issue with getting the previous configuration
    if (!$dump_max_values) {
        return false;
    }

    $config_complete = Db::getInstance()->execute('
        INSERT INTO ' . _DB_PREFIX_ . 'ntbr_config (
            `id_ntbr_config`, `is_default`, `name`, `type_backup`, `nb_backup`, `send_email`, `email_only_error`,
            `mail_backup`, `send_restore`, `activate_log`, `part_size`, `max_file_to_backup`, `dump_max_values`,
            `disable_refresh`, `time_between_refresh`, `time_pause_between_refresh`, `time_between_progress_refresh`,
            `disable_server_timeout`, `increase_server_memory`, `server_memory_value`, `dump_low_interest_tables`,
            `maintenance`, `time_between_backups`, `activate_xsendfile`, `ignore_product_image`, `ignore_compression`,
            `delete_local_backup`, `ignore_directories`, `ignore_file_types`, `ignore_tables`, `date_add`, `date_upd`
        ) VALUES (
            1, 1, "Complete backup", "complete", ' . $nb_backup . ', ' . $send_email . ', ' . $email_only_error . ',
            "' . pSQL($mail_backup) . '", ' . $send_restore . ', ' . $activate_log . ', ' . $part_size . ', ' . $max_file_to_backup . ',
            ' . $dump_max_values . ', ' . $disable_refresh . ', ' . $time_between_refresh . ', ' . $time_pause_between_refresh . ',
            ' . $time_between_progress_refresh . ', ' . $disable_server_timeout . ', ' . $increase_server_memory . ',
            ' . $server_memory_value . ', ' . $dump_low_interest_tables . ', ' . $maintenance . ', ' . $time_between_backups . ',
            ' . $activate_xsendfile . ', ' . $ignore_product_image . ', ' . $ignore_compression . ', ' . $delete_local_backup . ',
            "' . pSQL($ignore_directories) . '", "' . pSQL($ignore_files_types) . '", "' . pSQL($ignore_tables) . '",
            "' . pSQL($now) . '", "' . pSQL($now) . '"
        )
    ');

    $config_file = Db::getInstance()->execute('
        INSERT INTO ' . _DB_PREFIX_ . 'ntbr_config (
            `id_ntbr_config`, `is_default`, `name`, `type_backup`, `nb_backup`, `send_email`, `email_only_error`,
            `mail_backup`, `send_restore`, `activate_log`, `part_size`, `max_file_to_backup`, `dump_max_values`,
            `disable_refresh`, `time_between_refresh`, `time_pause_between_refresh`, `time_between_progress_refresh`,
            `disable_server_timeout`, `increase_server_memory`, `server_memory_value`, `dump_low_interest_tables`,
            `maintenance`, `time_between_backups`, `activate_xsendfile`, `ignore_product_image`, `ignore_compression`,
            `delete_local_backup`, `ignore_directories`, `ignore_file_types`, `ignore_tables`, `date_add`, `date_upd`
        ) VALUES (
            2, 0, "File backup", "file", ' . $nb_backup_file . ', ' . $send_email . ', ' . $email_only_error . ',
            "' . pSQL($mail_backup) . '", ' . $send_restore . ', ' . $activate_log . ', ' . $part_size . ', ' . $max_file_to_backup . ',
            ' . $dump_max_values . ', ' . $disable_refresh . ', ' . $time_between_refresh . ', ' . $time_pause_between_refresh . ',
            ' . $time_between_progress_refresh . ', ' . $disable_server_timeout . ', ' . $increase_server_memory . ',
            ' . $server_memory_value . ', ' . $dump_low_interest_tables . ', ' . $maintenance . ', ' . $time_between_backups . ',
            ' . $activate_xsendfile . ', ' . $ignore_product_image . ', ' . $ignore_compression . ', ' . $delete_local_backup . ',
            "' . pSQL($ignore_directories) . '", "' . pSQL($ignore_files_types) . '", "' . pSQL($ignore_tables) . '",
            "' . pSQL($now) . '", "' . pSQL($now) . '"
        )
    ');

    $config_dump = Db::getInstance()->execute('
        INSERT INTO ' . _DB_PREFIX_ . 'ntbr_config (
            `id_ntbr_config`, `is_default`, `name`, `type_backup`, `nb_backup`, `send_email`, `email_only_error`,
            `mail_backup`, `send_restore`, `activate_log`, `part_size`, `max_file_to_backup`, `dump_max_values`,
            `disable_refresh`, `time_between_refresh`, `time_pause_between_refresh`, `time_between_progress_refresh`,
            `disable_server_timeout`, `increase_server_memory`, `server_memory_value`, `dump_low_interest_tables`,
            `maintenance`, `time_between_backups`, `activate_xsendfile`, `ignore_product_image`, `ignore_compression`,
            `delete_local_backup`, `ignore_directories`, `ignore_file_types`, `ignore_tables`, `date_add`, `date_upd`
        ) VALUES (
            3, 0, "Base backup", "dump", ' . $nb_backup_base . ', ' . $send_email . ', ' . $email_only_error . ',
            "' . pSQL($mail_backup) . '", ' . $send_restore . ', ' . $activate_log . ', ' . $part_size . ', ' . $max_file_to_backup . ',
            ' . $dump_max_values . ', ' . $disable_refresh . ', ' . $time_between_refresh . ', ' . $time_pause_between_refresh . ',
            ' . $time_between_progress_refresh . ', ' . $disable_server_timeout . ', ' . $increase_server_memory . ',
            ' . $server_memory_value . ', ' . $dump_low_interest_tables . ', ' . $maintenance . ', ' . $time_between_backups . ',
            ' . $activate_xsendfile . ', ' . $ignore_product_image . ', ' . $ignore_compression . ', ' . $delete_local_backup . ',
            "' . pSQL($ignore_directories) . '", "' . pSQL($ignore_files_types) . '", "' . pSQL($ignore_tables) . '",
            "' . pSQL($now) . '", "' . pSQL($now) . '"
        )
    ');

    $aws_table = Db::getInstance()->execute('
        ALTER TABLE `' . _DB_PREFIX_ . 'ntbr_aws`
        ADD `id_ntbr_config`    INT(10)             NOT NULL                AFTER `id_ntbr_aws`,
        ADD `config_nb_backup`  INT(10) unsigned    NOT NULL    DEFAULT "0" AFTER `name`
    ');

    $dropbox_table = Db::getInstance()->execute('
        ALTER TABLE `' . _DB_PREFIX_ . 'ntbr_dropbox`
        ADD `id_ntbr_config`    INT(10)             NOT NULL                AFTER `id_ntbr_dropbox`,
        ADD `config_nb_backup`  INT(10) unsigned    NOT NULL    DEFAULT "0" AFTER `name`
    ');

    $ftp_table = Db::getInstance()->execute('
        ALTER TABLE `' . _DB_PREFIX_ . 'ntbr_ftp`
        ADD `id_ntbr_config`    INT(10)             NOT NULL                AFTER `id_ntbr_ftp`,
        ADD `config_nb_backup`  INT(10) unsigned    NOT NULL    DEFAULT "0" AFTER `name`
    ');

    $googledrive_table = Db::getInstance()->execute('
        ALTER TABLE `' . _DB_PREFIX_ . 'ntbr_googledrive`
        ADD `id_ntbr_config`    INT(10)              NOT NULL                AFTER `id_ntbr_googledrive`,
        ADD `config_nb_backup`  INT(10) unsigned    NOT NULL    DEFAULT "0" AFTER `name`
    ');

    $hubic_table = Db::getInstance()->execute('
        ALTER TABLE `' . _DB_PREFIX_ . 'ntbr_hubic`
        ADD `id_ntbr_config`    INT(10)             NOT NULL                AFTER `id_ntbr_hubic`,
        ADD `config_nb_backup`  INT(10) unsigned    NOT NULL    DEFAULT "0" AFTER `name`
    ');

    $onedrive_table = Db::getInstance()->execute('
        ALTER TABLE `' . _DB_PREFIX_ . 'ntbr_onedrive`
        ADD `id_ntbr_config`    INT(10)             NOT NULL                AFTER `id_ntbr_onedrive`,
        ADD `config_nb_backup`  INT(10) unsigned    NOT NULL    DEFAULT "0" AFTER `name`
    ');

    $owncloud_table = Db::getInstance()->execute('
        ALTER TABLE `' . _DB_PREFIX_ . 'ntbr_owncloud`
        ADD `id_ntbr_config`    INT(10)             NOT NULL                AFTER `id_ntbr_owncloud`,
        ADD `config_nb_backup`  INT(10) unsigned    NOT NULL    DEFAULT "0" AFTER `name`
    ');

    $webdav_table = Db::getInstance()->execute('
        ALTER TABLE `' . _DB_PREFIX_ . 'ntbr_webdav`
        ADD `id_ntbr_config`    INT(10)             NOT NULL                AFTER `id_ntbr_webdav`,
        ADD `config_nb_backup`  INT(10) unsigned    NOT NULL    DEFAULT "0" AFTER `name`
    ');

    if (!$config_complete || !$config_file || !$config_dump || !$aws_table || !$dropbox_table || !$ftp_table
        || !$googledrive_table || !$hubic_table || !$onedrive_table || !$owncloud_table || !$webdav_table) {
        return false;
    }

    $comments = Db::getInstance()->executeS('
        SELECT `backup_name`, `comment`, `date_add`, `date_upd`
        FROM `' . _DB_PREFIX_ . 'ntbr_comments`
    ');

    if (is_array($comments)) {
        foreach ($comments as $comment) {
            $id_ntbr_config = 1;

            if (strpos($comment['backup_name'], '.file.') !== false) {
                $id_ntbr_config = 2;
            } elseif (strpos($comment['backup_name'], '.dump.') !== false) {
                $id_ntbr_config = 3;
            }

            Db::getInstance()->execute('
                INSERT INTO `' . _DB_PREFIX_ . 'ntbr_backups` (
                    `id_ntbr_config`, `backup_name`, `comment`, `date_add`, `date_upd`
                ) VALUES (
                    ' . (int) $id_ntbr_config . ', "' . pSQL($comment['backup_name']) . '", "' . pSQL($comment['comment']) . '",
                    "' . pSQL($comment['date_add']) . '", "' . pSQL($comment['date_upd']) . '"
                )
            ');
        }
    }

    $physic_path_modules = realpath(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'modules') . DIRECTORY_SEPARATOR;
    $module_path_physic = $physic_path_modules . 'ntbackupandrestore' . DIRECTORY_SEPARATOR;
    $backup_folder = $module_path_physic . 'backup' . DIRECTORY_SEPARATOR;

    if (($dir = opendir($backup_folder)) !== false) {
        while (($file = readdir($dir)) !== false) {
            if ($file == '.' || $file == '..' || is_dir($backup_folder . $file) || strpos($file, '.tar') === false) {
                continue;
            }

            $clean_file = preg_replace('/([0-9]+\.part\.)/', '', $file);

            if ($clean_file == '') {
                continue;
            }

            $backup_exists = (int) Db::getInstance()->getValue('
                SELECT `id_ntbr_backups`
                FROM `' . _DB_PREFIX_ . 'ntbr_backups`
                WHERE `backup_name` = "' . pSQL($clean_file) . '"
            ');

            if (!$backup_exists) {
                $id_ntbr_config = 1;

                if (strpos($clean_file, '.file.') !== false) {
                    $id_ntbr_config = 2;
                } elseif (strpos($clean_file, '.dump.') !== false) {
                    $id_ntbr_config = 3;
                }

                Db::getInstance()->execute('
                    INSERT INTO `' . _DB_PREFIX_ . 'ntbr_backups` (
                        `id_ntbr_config`, `backup_name`, `comment`, `date_add`, `date_upd`
                    ) VALUES (
                        ' . (int) $id_ntbr_config . ', "' . pSQL($clean_file) . '", "", "' . pSQL($now) . '", "' . pSQL($now) . '"
                    )
                ');
            }
        }
    }

    $aws_accounts = Db::getInstance()->executeS('
        SELECT `id_ntbr_aws`, `active`, `name`, `nb_backup`, `nb_backup_file`, `nb_backup_base`,
            `access_key_id`, `secret_access_key`, `region`, `bucket`, `directory_key`, `directory_path`
        FROM `' . _DB_PREFIX_ . 'ntbr_aws`
        ORDER BY `name`
    ');

    $ntbr = new NtbrChild();

    if (is_array($aws_accounts)) {
        foreach ($aws_accounts as $aws_account) {
            $access_key_id = $ntbr->encrypt($aws_account['access_key_id']);
            $secret_access_key = $ntbr->encrypt($aws_account['secret_access_key']);

            Db::getInstance()->execute('
                UPDATE `' . _DB_PREFIX_ . 'ntbr_aws`
                SET `id_ntbr_config` = 1, `config_nb_backup` = ' . (int) $aws_account['nb_backup'] . ',
                    `access_key_id` = "' . pSQL($access_key_id) . '", `secret_access_key` = "' . pSQL($secret_access_key) . '"
                WHERE `id_ntbr_aws` = ' . (int) $aws_account['id_ntbr_aws'] . '
            ');

            Db::getInstance()->execute('
                INSERT INTO `' . _DB_PREFIX_ . 'ntbr_aws` (
                    `id_ntbr_config`, `active`, `name`, `config_nb_backup`, `access_key_id`, `secret_access_key`,
                    `region`, `bucket`, `directory_key`, `directory_path`, `date_add`, `date_upd`
                ) VALUES (
                    2, ' . (int) (bool) $aws_account['active'] . ', "' . pSQL($aws_account['name']) . '",
                    ' . (int) $aws_account['nb_backup_file'] . ', "' . pSQL($access_key_id) . '", "' . pSQL($secret_access_key) . '",
                    "' . pSQL($aws_account['region']) . '", "' . pSQL($aws_account['bucket']) . '",
                    "' . pSQL($aws_account['directory_key']) . '", "' . pSQL($aws_account['directory_path']) . '",
                    "' . pSQL($now) . '", "' . pSQL($now) . '"
                )
            ');

            Db::getInstance()->execute('
                INSERT INTO `' . _DB_PREFIX_ . 'ntbr_aws` (
                    `id_ntbr_config`, `active`, `name`, `config_nb_backup`, `access_key_id`, `secret_access_key`,
                    `region`, `bucket`, `directory_key`, `directory_path`, `date_add`, `date_upd`
                ) VALUES (
                    3, ' . (int) (bool) $aws_account['active'] . ', "' . pSQL($aws_account['name']) . '",
                    ' . (int) $aws_account['nb_backup_base'] . ', "' . pSQL($access_key_id) . '", "' . pSQL($secret_access_key) . '",
                    "' . pSQL($aws_account['region']) . '", "' . pSQL($aws_account['bucket']) . '",
                    "' . pSQL($aws_account['directory_key']) . '", "' . pSQL($aws_account['directory_path']) . '",
                    "' . pSQL($now) . '", "' . pSQL($now) . '"
                )
            ');
        }
    }

    $dropbox_accounts = Db::getInstance()->executeS('
        SELECT `id_ntbr_dropbox`, `active`, `name`, `nb_backup`, `nb_backup_file`, `nb_backup_base`, `directory`,
            `token`
        FROM `' . _DB_PREFIX_ . 'ntbr_dropbox`
        ORDER BY `name`
    ');

    if (is_array($dropbox_accounts)) {
        foreach ($dropbox_accounts as $dropbox_account) {
            $token = $ntbr->encrypt($dropbox_account['token']);

            Db::getInstance()->execute('
                UPDATE `' . _DB_PREFIX_ . 'ntbr_dropbox`
                SET `id_ntbr_config` = 1, `config_nb_backup` = ' . (int) $dropbox_account['nb_backup'] . ',
                    `token` = "' . pSQL($token) . '"
                WHERE `id_ntbr_dropbox` = ' . (int) $dropbox_account['id_ntbr_dropbox'] . '
            ');

            Db::getInstance()->execute('
                INSERT INTO `' . _DB_PREFIX_ . 'ntbr_dropbox` (
                    `id_ntbr_config`, `active`, `name`, `config_nb_backup`, `directory`, `token`, `date_add`, `date_upd`
                ) VALUES (
                    2, ' . (int) (bool) $dropbox_account['active'] . ', "' . pSQL($dropbox_account['name']) . '",
                    ' . (int) $dropbox_account['nb_backup_file'] . ', "' . pSQL($dropbox_account['directory']) . '",
                    "' . pSQL($token) . '", "' . pSQL($now) . '", "' . pSQL($now) . '"
                )
            ');

            Db::getInstance()->execute('
                INSERT INTO `' . _DB_PREFIX_ . 'ntbr_dropbox` (
                    `id_ntbr_config`, `active`, `name`, `config_nb_backup`, `directory`, `token`, `date_add`, `date_upd`
                ) VALUES (
                    3, ' . (int) (bool) $dropbox_account['active'] . ', "' . pSQL($dropbox_account['name']) . '",
                    ' . (int) $dropbox_account['nb_backup_base'] . ', "' . pSQL($dropbox_account['directory']) . '",
                    "' . pSQL($token) . '", "' . pSQL($now) . '", "' . pSQL($now) . '"
                )
            ');
        }
    }

    $ftp_accounts = Db::getInstance()->executeS('
        SELECT `id_ntbr_ftp`, `active`, `name`, `nb_backup`, `nb_backup_file`, `nb_backup_base`, `sftp`, `ssl`,
            `passive_mode`, `server`, `login`, `password`, `port`, `directory`
        FROM `' . _DB_PREFIX_ . 'ntbr_ftp`
        ORDER BY `name`
    ');

    if (is_array($ftp_accounts)) {
        foreach ($ftp_accounts as $ftp_account) {
            Db::getInstance()->execute('
                UPDATE `' . _DB_PREFIX_ . 'ntbr_ftp`
                SET `id_ntbr_config` = 1, `config_nb_backup` = ' . (int) $ftp_account['nb_backup'] . '
                WHERE `id_ntbr_ftp` = ' . (int) $ftp_account['id_ntbr_ftp'] . '
            ');

            Db::getInstance()->execute('
                INSERT INTO `' . _DB_PREFIX_ . 'ntbr_ftp` (
                    `id_ntbr_config`, `active`, `name`, `config_nb_backup`, `sftp`, `ssl`, `passive_mode`, `server`,
                    `login`, `password`, `port`, `directory`, `date_add`, `date_upd`
                ) VALUES (
                    2, ' . (int) (bool) $ftp_account['active'] . ', "' . pSQL($ftp_account['name']) . '",
                    ' . (int) $ftp_account['nb_backup_file'] . ', ' . (int) (bool) $ftp_account['sftp'] . ',
                    ' . (int) (bool) $ftp_account['ssl'] . ', ' . (int) (bool) $ftp_account['passive_mode'] . ',
                    "' . pSQL($ftp_account['server']) . '", "' . pSQL($ftp_account['login']) . '",
                    "' . pSQL($ftp_account['password']) . '", ' . (int) $ftp_account['port'] . ',
                    "' . pSQL($ftp_account['directory']) . '", "' . pSQL($now) . '", "' . pSQL($now) . '"
                )
            ');

            Db::getInstance()->execute('
                INSERT INTO `' . _DB_PREFIX_ . 'ntbr_ftp` (
                    `id_ntbr_config`, `active`, `name`, `config_nb_backup`, `sftp`, `ssl`, `passive_mode`, `server`,
                    `login`, `password`, `port`, `directory`, `date_add`, `date_upd`
                ) VALUES (
                    3, ' . (int) (bool) $ftp_account['active'] . ', "' . pSQL($ftp_account['name']) . '",
                    ' . (int) $ftp_account['nb_backup_base'] . ', ' . (int) (bool) $ftp_account['sftp'] . ',
                    ' . (int) (bool) $ftp_account['ssl'] . ', ' . (int) (bool) $ftp_account['passive_mode'] . ',
                    "' . pSQL($ftp_account['server']) . '", "' . pSQL($ftp_account['login']) . '",
                    "' . pSQL($ftp_account['password']) . '", ' . (int) $ftp_account['port'] . ',
                    "' . pSQL($ftp_account['directory']) . '", "' . pSQL($now) . '", "' . pSQL($now) . '"
                )
            ');
        }
    }

    $googledrive_accounts = Db::getInstance()->executeS('
        SELECT `id_ntbr_googledrive`, `active`, `name`, `nb_backup`, `nb_backup_file`, `nb_backup_base`,
            `directory_key`, `directory_path`, `token`
        FROM `' . _DB_PREFIX_ . 'ntbr_googledrive`
        ORDER BY `name`
    ');

    if (is_array($googledrive_accounts)) {
        foreach ($googledrive_accounts as $googledrive_account) {
            $decode_token = json_decode($googledrive_account['token'], true);
            $decode_token['access_token'] = $ntbr->encrypt($decode_token['access_token']);
            $decode_token['refresh_token'] = $ntbr->encrypt($decode_token['refresh_token']);

            $token = json_encode($decode_token);

            Db::getInstance()->execute('
                UPDATE `' . _DB_PREFIX_ . 'ntbr_googledrive`
                SET `id_ntbr_config` = 1, `config_nb_backup` = ' . (int) $googledrive_account['nb_backup'] . ',
                    `token` = "' . pSQL($token) . '"
                WHERE `id_ntbr_googledrive` = ' . (int) $googledrive_account['id_ntbr_googledrive'] . '
            ');

            Db::getInstance()->execute('
                INSERT INTO `' . _DB_PREFIX_ . 'ntbr_googledrive` (
                    `id_ntbr_config`, `active`, `name`, `config_nb_backup`, `directory_key`, `directory_path`,
                    `token`, `date_add`, `date_upd`
                ) VALUES (
                    2, ' . (int) (bool) $googledrive_account['active'] . ', "' . pSQL($googledrive_account['name']) . '",
                    ' . (int) $googledrive_account['nb_backup_file'] . ', "' . pSQL($googledrive_account['directory_key']) . '",
                    "' . pSQL($googledrive_account['directory_path']) . '", "' . pSQL($token) . '", "' . pSQL($now) . '",
                    "' . pSQL($now) . '"
                )
            ');

            Db::getInstance()->execute('
                INSERT INTO `' . _DB_PREFIX_ . 'ntbr_googledrive` (
                    `id_ntbr_config`, `active`, `name`, `config_nb_backup`, `directory_key`, `directory_path`,
                    `token`, `date_add`, `date_upd`
                ) VALUES (
                    3, ' . (int) (bool) $googledrive_account['active'] . ', "' . pSQL($googledrive_account['name']) . '",
                    ' . (int) $googledrive_account['nb_backup_base'] . ', "' . pSQL($googledrive_account['directory_key']) . '",
                    "' . pSQL($googledrive_account['directory_path']) . '", "' . pSQL($token) . '", "' . pSQL($now) . '",
                    "' . pSQL($now) . '"
                )
            ');
        }
    }

    $hubic_accounts = Db::getInstance()->executeS('
        SELECT `id_ntbr_hubic`, `active`, `name`, `nb_backup`, `nb_backup_file`, `nb_backup_base`, `directory`,
            `token`, `credential`
        FROM `' . _DB_PREFIX_ . 'ntbr_hubic`
        ORDER BY `name`
    ');

    if (is_array($hubic_accounts)) {
        foreach ($hubic_accounts as $hubic_account) {
            $decode_token = json_decode($hubic_account['token'], true);
            $decode_credential = json_decode($hubic_account['credential'], true);

            $decode_token['access_token'] = $ntbr->encrypt($decode_token['access_token']);
            $decode_token['refresh_token'] = $ntbr->encrypt($decode_token['refresh_token']);

            $decode_credential['token'] = $ntbr->encrypt($decode_credential['token']);

            $token = json_encode($decode_token);
            $credential = json_encode($decode_credential);

            Db::getInstance()->execute('
                UPDATE `' . _DB_PREFIX_ . 'ntbr_hubic`
                SET `id_ntbr_config` = 1, `config_nb_backup` = ' . (int) $hubic_account['nb_backup'] . ',
                    `token` = "' . pSQL($token) . '", `credential` = "' . pSQL($credential) . '"
                WHERE `id_ntbr_hubic` = ' . (int) $hubic_account['id_ntbr_hubic'] . '
            ');

            Db::getInstance()->execute('
                INSERT INTO `' . _DB_PREFIX_ . 'ntbr_hubic` (
                    `id_ntbr_config`, `active`, `name`, `config_nb_backup`, `directory`, `token`, `credential`,
                    `date_add`, `date_upd`
                ) VALUES (
                    2, ' . (int) (bool) $hubic_account['active'] . ', "' . pSQL($hubic_account['name']) . '",
                    ' . (int) $hubic_account['nb_backup_file'] . ', "' . pSQL($hubic_account['directory']) . '",
                    "' . pSQL($token) . '", "' . pSQL($credential) . '", "' . pSQL($now) . '", "' . pSQL($now) . '"
                )
            ');

            Db::getInstance()->execute('
                INSERT INTO `' . _DB_PREFIX_ . 'ntbr_hubic` (
                    `id_ntbr_config`, `active`, `name`, `config_nb_backup`, `directory`, `token`, `credential`,
                    `date_add`, `date_upd`
                ) VALUES (
                    3, ' . (int) (bool) $hubic_account['active'] . ', "' . pSQL($hubic_account['name']) . '",
                    ' . (int) $hubic_account['nb_backup_base'] . ', "' . pSQL($hubic_account['directory']) . '",
                    "' . pSQL($token) . '", "' . pSQL($credential) . '", "' . pSQL($now) . '", "' . pSQL($now) . '"
                )
            ');
        }
    }

    $onedrive_accounts = Db::getInstance()->executeS('
        SELECT `id_ntbr_onedrive`, `active`, `name`, `nb_backup`, `nb_backup_file`, `nb_backup_base`, `directory_key`,
            `directory_path`, `token`
        FROM `' . _DB_PREFIX_ . 'ntbr_onedrive`
        ORDER BY `name`
    ');

    if (is_array($onedrive_accounts)) {
        foreach ($onedrive_accounts as $onedrive_account) {
            $decode_token = json_decode($onedrive_account['token'], true);

            $decode_token['access_token'] = $ntbr->encrypt($decode_token['access_token']);
            $decode_token['refresh_token'] = $ntbr->encrypt($decode_token['refresh_token']);

            $token = json_encode($decode_token);

            Db::getInstance()->execute('
                UPDATE `' . _DB_PREFIX_ . 'ntbr_onedrive`
                SET `id_ntbr_config` = 1, `config_nb_backup` = ' . (int) $onedrive_account['nb_backup'] . ',
                    `token` = "' . pSQL($token) . '"
                WHERE `id_ntbr_onedrive` = ' . (int) $onedrive_account['id_ntbr_onedrive'] . '
            ');

            Db::getInstance()->execute('
                INSERT INTO `' . _DB_PREFIX_ . 'ntbr_onedrive` (
                    `id_ntbr_config`, `active`, `name`, `config_nb_backup`, `directory_key`, `directory_path`,
                    `token`, `date_add`, `date_upd`
                ) VALUES (
                    2, ' . (int) (bool) $onedrive_account['active'] . ', "' . pSQL($onedrive_account['name']) . '",
                    ' . (int) $onedrive_account['nb_backup_file'] . ', "' . pSQL($onedrive_account['directory_key']) . '",
                    "' . pSQL($onedrive_account['directory_path']) . '", "' . pSQL($token) . '", "' . pSQL($now) . '",
                    "' . pSQL($now) . '"
                )
            ');

            Db::getInstance()->execute('
                INSERT INTO `' . _DB_PREFIX_ . 'ntbr_onedrive` (
                    `id_ntbr_config`, `active`, `name`, `config_nb_backup`, `directory_key`, `directory_path`,
                    `token`,`date_add`, `date_upd`
                ) VALUES (
                    3, ' . (int) (bool) $onedrive_account['active'] . ', "' . pSQL($onedrive_account['name']) . '",
                    ' . (int) $onedrive_account['nb_backup_base'] . ', "' . pSQL($onedrive_account['directory_key']) . '",
                    "' . pSQL($onedrive_account['directory_path']) . '", "' . pSQL($token) . '", "' . pSQL($now) . '",
                    "' . pSQL($now) . '"
                )
            ');
        }
    }

    $owncloud_accounts = Db::getInstance()->executeS('
        SELECT `id_ntbr_owncloud`, `active`, `name`, `nb_backup`, `nb_backup_file`, `nb_backup_base`, `login`,
            `password`, `server`, `directory`
        FROM `' . _DB_PREFIX_ . 'ntbr_owncloud`
        ORDER BY `name`
    ');

    if (is_array($owncloud_accounts)) {
        foreach ($owncloud_accounts as $owncloud_account) {
            Db::getInstance()->execute('
                UPDATE `' . _DB_PREFIX_ . 'ntbr_owncloud`
                SET `id_ntbr_config` = 1, `config_nb_backup` = ' . (int) $owncloud_account['nb_backup'] . '
                WHERE `id_ntbr_owncloud` = ' . (int) $owncloud_account['id_ntbr_owncloud'] . '
            ');

            Db::getInstance()->execute('
                INSERT INTO `' . _DB_PREFIX_ . 'ntbr_owncloud` (
                    `id_ntbr_config`, `active`, `name`, `config_nb_backup`, `login`, `password`, `server`, `directory`,
                    `date_add`, `date_upd`
                ) VALUES (
                    2, ' . (int) (bool) $owncloud_account['active'] . ', "' . pSQL($owncloud_account['name']) . '",
                    ' . (int) $owncloud_account['nb_backup_file'] . ', "' . pSQL($owncloud_account['login']) . '",
                    "' . pSQL($owncloud_account['password']) . '", "' . pSQL($owncloud_account['server']) . '",
                    "' . pSQL($owncloud_account['directory']) . '", "' . pSQL($now) . '", "' . pSQL($now) . '"
                )
            ');

            Db::getInstance()->execute('
                INSERT INTO `' . _DB_PREFIX_ . 'ntbr_owncloud` (
                    `id_ntbr_config`, `active`, `name`, `config_nb_backup`, `login`, `password`, `server`, `directory`,
                    `date_add`, `date_upd`
                ) VALUES (
                    3, ' . (int) (bool) $owncloud_account['active'] . ', "' . pSQL($owncloud_account['name']) . '",
                    ' . (int) $owncloud_account['nb_backup_base'] . ', "' . pSQL($owncloud_account['login']) . '",
                    "' . pSQL($owncloud_account['password']) . '", "' . pSQL($owncloud_account['server']) . '",
                    "' . pSQL($owncloud_account['directory']) . '", "' . pSQL($now) . '", "' . pSQL($now) . '"
                )
            ');
        }
    }

    $webdav_accounts = Db::getInstance()->executeS('
        SELECT `id_ntbr_webdav`, `active`, `name`, `nb_backup`, `nb_backup_file`, `nb_backup_base`, `login`,
            `password`, `server`, `directory`
        FROM `' . _DB_PREFIX_ . 'ntbr_webdav`
        ORDER BY `name`
    ');

    if (is_array($webdav_accounts)) {
        foreach ($webdav_accounts as $webdav_account) {
            Db::getInstance()->execute('
                UPDATE `' . _DB_PREFIX_ . 'ntbr_webdav`
                SET `id_ntbr_config` = 1, `config_nb_backup` = ' . (int) $webdav_account['nb_backup'] . '
                WHERE `id_ntbr_webdav` = ' . (int) $webdav_account['id_ntbr_webdav'] . '
            ');

            Db::getInstance()->execute('
                INSERT INTO `' . _DB_PREFIX_ . 'ntbr_webdav` (
                    `id_ntbr_config`, `active`, `name`, `config_nb_backup`, `login`, `password`, `server`, `directory`,
                    `date_add`, `date_upd`
                ) VALUES (
                    2, ' . (int) (bool) $webdav_account['active'] . ', "' . pSQL($webdav_account['name']) . '",
                    ' . (int) $webdav_account['nb_backup_file'] . ', "' . pSQL($webdav_account['login']) . '",
                    "' . pSQL($webdav_account['password']) . '", "' . pSQL($webdav_account['server']) . '",
                    "' . pSQL($webdav_account['directory']) . '", "' . pSQL($now) . '", "' . pSQL($now) . '"
                )
            ');

            Db::getInstance()->execute('
                INSERT INTO `' . _DB_PREFIX_ . 'ntbr_webdav` (
                    `id_ntbr_config`, `active`, `name`, `config_nb_backup`, `login`, `password`, `server`, `directory`,
                    `date_add`, `date_upd`
                ) VALUES (
                    3, ' . (int) (bool) $webdav_account['active'] . ', "' . pSQL($webdav_account['name']) . '",
                    ' . (int) $webdav_account['nb_backup_base'] . ', "' . pSQL($webdav_account['login']) . '",
                    "' . pSQL($webdav_account['password']) . '", "' . pSQL($webdav_account['server']) . '",
                    "' . pSQL($webdav_account['directory']) . '", "' . pSQL($now) . '", "' . pSQL($now) . '"
                )
            ');
        }
    }

    $shops = Shop::getShops();

    foreach ($shops as $shop) {
        if (!Configuration::updateValue('NTBR_MULTI_CONFIG', 0, false, $shop['id_shop_group'], $shop['id_shop'])) {
            return false;
        }
    }

    return $module;
}
