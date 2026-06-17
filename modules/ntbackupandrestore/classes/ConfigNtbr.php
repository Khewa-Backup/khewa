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

class ConfigNtbr extends ObjectModel
{
    /** @var bool is_default */
    public $is_default;

    /** @var bool disable */
    public $disable;

    /** @var string name */
    public $name;

    /** @var string type_backup */
    public $type_backup;

    /** @var int nb_backup */
    public $nb_backup;

    /** @var bool send_email */
    public $send_email;

    /** @var bool receive_email_version */
    public $receive_email_version;

    /** @var bool email_only_error */
    public $email_only_error;

    /** @var string mail_backup */
    public $mail_backup;

    /** @var string mail_version */
    public $mail_version;

    /** @var bool send_restore */
    public $send_restore;

    /** @var bool activate_log */
    public $activate_log;

    /** @var int part_size */
    public $part_size;

    /** @var int max_file_to_backup */
    public $max_file_to_backup;

    /** @var int dump_max_values */
    public $dump_max_values;

    /** @var int dump_lines_limit */
    public $dump_lines_limit;

    /** @var bool disable_refresh */
    public $disable_refresh;

    /** @var int time_between_refresh */
    public $time_between_refresh;

    /** @var int time_pause_between_refresh */
    public $time_pause_between_refresh;

    /** @var int time_between_progress_refresh */
    public $time_between_progress_refresh;

    /** @var bool increase_server_timeout */
    public $increase_server_timeout;

    /** @var int server_timeout_value */
    public $server_timeout_value;

    /** @var bool increase_server_memory */
    public $increase_server_memory;

    /** @var int server_memory_value */
    public $server_memory_value;

    /** @var bool dump_low_interest_tables */
    public $dump_low_interest_tables;

    /** @var bool maintenance */
    public $maintenance;

    /** @var int time_between_backups */
    public $time_between_backups;

    /** @var bool activate_xsendfile */
    public $activate_xsendfile;

    /** @var int ignore_product_image */
    public $ignore_product_image;

    /** @var bool only_origin_img */
    public $only_origin_img;

    /** @var bool ignore_compression */
    public $ignore_compression;

    /** @var bool delete_local_backup */
    public $delete_local_backup;

    /** @var bool create_on_distant */
    public $create_on_distant;

    /** @var bool crypt_backup */
    public $crypt_backup;

    /** @var string sodium_key */
    public $sodium_key;

    /** @var bool js_download */
    public $js_download;

    /** @var bool disable_curl_file_size */
    public $disable_curl_file_size;

    /** @var bool scan_files */
    public $scan_files;

    /** @var string backup_dir */
    public $backup_dir;

    /** @var string ignore_directories */
    public $ignore_directories;

    /** @var string ignore_file_types */
    public $ignore_file_types;

    /** @var string ignore_tables */
    public $ignore_tables;

    /** @var string not_recreate_tables */
    public $not_recreate_tables;

    /** @var string date_add */
    public $date_add;

    /** @var string date_upd */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'ntbr_config',
        'primary' => 'id_ntbr_config',
        'multilang' => false,
        'multilang_shop' => false,
        'fields' => [
            'is_default' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'default' => '0',
            ],
            'disable' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'default' => '0',
            ],
            'name' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'size' => 255,
                'required' => true,
            ],
            'type_backup' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'size' => 255,
                'required' => true,
            ],
            'nb_backup' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
            ],
            'send_email' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'default' => '0',
            ],
            'receive_email_version' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'default' => '0',
            ],
            'email_only_error' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'default' => '0',
            ],
            'mail_backup' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required' => true,
            ],
            'mail_version' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required' => true,
            ],
            'send_restore' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'default' => '0',
            ],
            'activate_log' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'default' => '0',
            ],
            'part_size' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'default' => '0',
            ],
            'max_file_to_backup' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'default' => '0',
            ],
            'dump_max_values' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'default' => NtbrCore::DUMP_MAX_VALUES,
            ],
            'dump_lines_limit' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'default' => NtbrCore::DUMP_LINES_LIMIT,
            ],
            'disable_refresh' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'default' => '0',
            ],
            'time_between_refresh' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'default' => NtbrCore::MAX_TIME_BEFORE_REFRESH,
            ],
            'time_pause_between_refresh' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'default' => '0',
            ],
            'time_between_progress_refresh' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'default' => NtbrCore::MAX_TIME_BEFORE_PROGRESS_REFRESH,
            ],
            'increase_server_timeout' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'default' => '0',
            ],
            'server_timeout_value' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'default' => NtbrCore::SET_TIME_LIMIT,
            ],
            'increase_server_memory' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'default' => '0',
            ],
            'server_memory_value' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'default' => NtbrCore::SET_MEMORY_LIMIT,
            ],
            'dump_low_interest_tables' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'default' => '0',
            ],
            'maintenance' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'default' => '0',
            ],
            'time_between_backups' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'default' => NtbrCore::MIN_TIME_NEW_BACKUP,
            ],
            'activate_xsendfile' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'default' => '0',
            ],
            'ignore_product_image' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'default' => '0',
            ],
            'only_origin_img' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'default' => '0',
            ],
            'ignore_compression' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'default' => '0',
            ],
            'delete_local_backup' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'default' => '0',
            ],
            'create_on_distant' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'default' => '0',
            ],
            'crypt_backup' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'default' => '0',
            ],
            'sodium_key' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => false,
                'default' => '',
            ],
            'js_download' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'default' => '0',
            ],
            'disable_curl_file_size' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'default' => '0',
            ],
            'scan_files' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'default' => '0',
            ],
            'backup_dir' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => false,
                'default' => '',
            ],
            'ignore_directories' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => false,
                'default' => '',
            ],
            'ignore_file_types' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => false,
                'default' => '',
            ],
            'ignore_tables' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => false,
                'default' => '',
            ],
            'not_recreate_tables' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => false,
                'default' => '',
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
            ],
            'date_upd' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
            ],
        ],
    ];

    public function add($auto_date = true, $null_values = false)
    {
        if (!$this->mail_backup) {
            $this->mail_backup = Configuration::get('PS_SHOP_EMAIL');
        } else {
            $this->mail_backup = preg_replace('/\s/', '', $this->mail_backup);
        }

        if (!$this->mail_version) {
            $this->mail_version = Configuration::get('PS_SHOP_EMAIL');
        } else {
            $this->mail_version = preg_replace('/\s/', '', $this->mail_version);
        }

        if (!$this->backup_dir || !is_dir($this->backup_dir)) {
            $this->backup_dir = NtBackupAndRestore::getModuleBackupDirectory();
        }

        $this->disable = 0;

        if (!self::getIdDefault()) {
            $this->is_default = 1;
        } elseif ($this->is_default) {
            self::cleanDefault($this->id);
        }

        return parent::add($auto_date, $null_values);
    }

    public function update($null_values = false)
    {
        $id_default = self::getIdDefault();

        if (!$this->mail_backup) {
            $this->mail_backup = Configuration::get('PS_SHOP_EMAIL');
        } else {
            $this->mail_backup = preg_replace('/\s/', '', $this->mail_backup);
        }

        if (!$this->mail_version) {
            $this->mail_version = Configuration::get('PS_SHOP_EMAIL');
        } else {
            $this->mail_version = preg_replace('/\s/', '', $this->mail_version);
        }

        // Backups should not be keep at the root of the shop
        $physic_path_root = Apparatus::getRealPath(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR);

        if (!$this->backup_dir || !is_dir($this->backup_dir) || $this->backup_dir == $physic_path_root || $this->backup_dir == $physic_path_root . DIRECTORY_SEPARATOR) {
            $this->backup_dir = NtBackupAndRestore::getModuleBackupDirectory();
        }

        // If the config was the default one and we try to remove the default attribut, prevent it.
        if ($id_default == $this->id && !$this->is_default) {
            $this->is_default = 1;
        } elseif (!$id_default) {
            // If there is no default config. Force this one to be the default one
            $this->is_default = 1;
        } elseif ($this->is_default) {
            self::cleanDefault($this->id);
        }

        if ($this->is_default) {
            $this->disable = 0;
        }

        return parent::update($null_values);
    }

    public function delete()
    {
        if ($this->is_default) {
            return false;
        }

        $result = true;
        $list_accounts = [
            'ftp',
            'aws',
            'owncloud',
            'webdav',
            'googledrive',
            'googlecloud',
            'pcloud',
            'box',
            'shadow_drive',
            'onedrive',
            'dropbox',
            'yandex',
            'sugarsync',
        ];

        foreach ($list_accounts as $account) {
            if (!Db::getInstance()->delete('ntbr_' . $account, '`id_ntbr_config` = ' . (int) $this->id)) {
                $result = false;
            }
        }

        if ($result) {
            return parent::delete();
        }

        return false;
    }

    /**
     * Get a list of all configs
     *
     * @return array List of all configs
     */
    public static function getListConfigs()
    {
        $configs = Db::getInstance()->executeS('
            SELECT
                `id_ntbr_config`, `is_default`, `name`, `type_backup`, `nb_backup`, `send_email`, `email_only_error`,
                `mail_backup`, `send_restore`, `activate_log`, `part_size`, `max_file_to_backup`, `dump_max_values`,
                `dump_lines_limit`, `disable_refresh`, `time_between_refresh`, `time_pause_between_refresh`,
                `time_between_progress_refresh`, `increase_server_timeout`, `increase_server_memory`, `mail_version`,
                `server_timeout_value`, `server_memory_value`, `dump_low_interest_tables`, `maintenance`,
                `time_between_backups`, `activate_xsendfile`, `ignore_product_image`, `ignore_compression`,
                `delete_local_backup`, `create_on_distant`, `js_download`, `backup_dir`, `ignore_directories`,
                `ignore_file_types`, `ignore_tables`, `not_recreate_tables`, `receive_email_version`, `scan_files`,
                `disable`, `disable_curl_file_size`, `crypt_backup`, `sodium_key`, `only_origin_img`
            FROM `' . _DB_PREFIX_ . 'ntbr_config`
            ORDER BY `date_add`
        ');

        if (!is_array($configs)) {
            return [];
        }

        return $configs;
    }

    /**
     * Get a list of all configs backup directories
     *
     * @return array List of all configs backup directories
     */
    public static function getListBackupDirectories()
    {
        $backup_dirs = Db::getInstance()->executeS('
            SELECT DISTINCT `backup_dir`
            FROM `' . _DB_PREFIX_ . 'ntbr_config`
            ORDER BY `backup_dir`
        ');

        if (!is_array($backup_dirs)) {
            return [];
        }

        return $backup_dirs;
    }

    /**
     * Get config ID by name
     *
     * @param string $name Name of the config
     *
     * @return int ID of the config
     */
    public static function getIdByName($name)
    {
        return (int) Db::getInstance()->getValue('
            SELECT `id_ntbr_config`
            FROM `' . _DB_PREFIX_ . 'ntbr_config`
            WHERE `name` = "' . pSQL($name) . '"
        ');
    }

    /**
     * Get config name by ID
     *
     * @param int $id_ntbr_config ID of the config
     *
     * @return string Name of the config
     */
    public static function getNameById($id_ntbr_config)
    {
        return Db::getInstance()->getValue('
            SELECT `name`
            FROM `' . _DB_PREFIX_ . 'ntbr_config`
            WHERE `id_ntbr_config` = ' . (int) $id_ntbr_config . '
        ');
    }

    /**
     * Get config backup directory by ID
     *
     * @param int $id_ntbr_config ID of the config
     *
     * @return string Backup directory of the config
     */
    public static function getBackupDirectoryById($id_ntbr_config)
    {
        return Db::getInstance()->getValue('
            SELECT `backup_dir`
            FROM `' . _DB_PREFIX_ . 'ntbr_config`
            WHERE `id_ntbr_config` = ' . (int) $id_ntbr_config . '
        ');
    }

    /**
     * Get default config
     *
     * @return int ID of the config
     */
    public static function getIdDefault()
    {
        return (int) Db::getInstance()->getValue('
            SELECT `id_ntbr_config`
            FROM `' . _DB_PREFIX_ . 'ntbr_config`
            WHERE `is_default` = 1
        ');
    }

    /**
     * Get first config of a type
     *
     * @return int ID of the config
     */
    public static function getIdByType($type_backup)
    {
        return (int) Db::getInstance()->getValue('
            SELECT `id_ntbr_config`
            FROM `' . _DB_PREFIX_ . 'ntbr_config`
            WHERE `type_backup` = "' . pSQL($type_backup) . '"
            ORDER BY `id_ntbr_config`
        ');
    }

    /**
     * Get nb config
     *
     * @return int nb of config
     */
    public static function getNbConfig()
    {
        return (int) Db::getInstance()->getValue('
            SELECT COUNT(`id_ntbr_config`)
            FROM `' . _DB_PREFIX_ . 'ntbr_config`
        ');
    }

    public static function cleanDefault($new_id_default)
    {
        Db::getInstance()->execute('
            UPDATE `' . _DB_PREFIX_ . 'ntbr_config`
            SET `is_default` = 0
            WHERE `id_ntbr_config` <> ' . (int) $new_id_default . ';
        ');
    }

    /**
     * Get config data by ID
     *
     * @param int $id_ntbr_config ID of the config
     *
     * @return array Data of the config
     */
    public static function getConfigById($id_ntbr_config)
    {
        $config = Db::getInstance()->getRow('
            SELECT
                `id_ntbr_config`, `is_default`, `name`, `type_backup`, `nb_backup`, `send_email`, `email_only_error`,
                `mail_backup`, `send_restore`, `activate_log`, `part_size`, `max_file_to_backup`, `dump_max_values`,
                `disable_refresh`, `time_between_refresh`, `time_pause_between_refresh`, `dump_lines_limit`,
                `time_between_progress_refresh`, `increase_server_timeout`, `increase_server_memory`, `js_download`,
                `server_timeout_value`, `server_memory_value`, `dump_low_interest_tables`, `maintenance`,
                `time_between_backups`, `backup_dir`, `activate_xsendfile`, `ignore_product_image`,
                `ignore_compression`, `delete_local_backup`, `ignore_directories`, `ignore_file_types`, `ignore_tables`,
                `not_recreate_tables`, `create_on_distant`, `receive_email_version`, `mail_version`, `scan_files`,
                `disable`, `disable_curl_file_size`, `crypt_backup`, `sodium_key`, `only_origin_img`
            FROM `' . _DB_PREFIX_ . 'ntbr_config`
            WHERE `id_ntbr_config` = ' . (int) $id_ntbr_config . '
        ');

        if (!is_array($config)) {
            return [];
        }

        return $config;
    }
}
