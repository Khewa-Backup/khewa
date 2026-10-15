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

class ShadowDriveNtbr extends ObjectModel
{
    /** @var int id_ntbr_config */
    public $id_ntbr_config;

    /** @var bool active */
    public $active;

    /** @var string name */
    public $name;

    /** @var int config_nb_backup */
    public $config_nb_backup;

    /** @var string login */
    public $login;

    /** @var string password */
    public $password;

    /** @var string server */
    public $server;

    /** @var string directory */
    public $directory;

    /** @var string date_add */
    public $date_add;

    /** @var string date_upd */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'ntbr_shadow_drive',
        'primary' => 'id_ntbr_shadow_drive',
        'multilang' => false,
        'multilang_shop' => false,
        'fields' => [
            'id_ntbr_config' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
            ],
            'active' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'default' => '0',
            ],
            'name' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'size' => 255,
                'required' => true,
                'default' => 'Shadow Drive',
            ],
            'config_nb_backup' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'default' => '0',
            ],
            'login' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'size' => 255,
                'required' => true,
                'default' => '',
            ],
            'password' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'size' => 255,
                'required' => true,
                'default' => '',
            ],
            'server' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'size' => 255,
                'required' => true,
                'default' => '',
            ],
            'directory' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'size' => 255,
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

    public function save($null_values = false, $autodate = true)
    {
        $this->directory = str_replace(' ', '_', $this->directory);

        return parent::save($null_values, $autodate);
    }

    /**
     * Get the default values
     *
     * @return array Default values
     */
    public static function getDefaultValues()
    {
        $default_values = [];

        $default_values[self::$definition['primary']] = 0;
        $default_values['nb_account'] = self::getNbAccounts() + 1;

        foreach (self::$definition['fields'] as $name => $field) {
            if (isset($field['default'])) {
                $default_values[$name] = $field['default'];
            }
        }

        return $default_values;
    }

    /**
     * Get a list of all Shadow Drive accounts
     *
     * @return array List of all Shadow Drive accounts
     */
    public static function getListShadowDriveAccounts($id_ntbr_config)
    {
        $shadow_drive_accounts = Db::getInstance()->executeS('
            SELECT `id_ntbr_shadow_drive`, `active`, `name`, `config_nb_backup`, `login`, `password`, `server`, `directory`
            FROM `' . _DB_PREFIX_ . 'ntbr_shadow_drive`
            WHERE `id_ntbr_config` = ' . (int) $id_ntbr_config . '
            ORDER BY `date_upd` DESC
        ');

        if (!is_array($shadow_drive_accounts)) {
            return [];
        }

        return $shadow_drive_accounts;
    }

    /**
     * Get a list of all active Shadow Drive accounts
     *
     * @return array List of all active Shadow Drive accounts
     */
    public static function getListActiveShadowDriveAccounts($id_ntbr_config)
    {
        $shadow_drive_accounts = Db::getInstance()->executeS('
            SELECT `id_ntbr_shadow_drive`, `active`, `name`, `config_nb_backup`, `login`, `password`, `server`, `directory`
            FROM `' . _DB_PREFIX_ . 'ntbr_shadow_drive`
            WHERE `active` = 1
            AND `id_ntbr_config` = ' . (int) $id_ntbr_config . '
            ORDER BY `name`
        ');

        if (!is_array($shadow_drive_accounts)) {
            return [];
        }

        return $shadow_drive_accounts;
    }

    /**
     * Get nb Shadow Drive active accounts
     *
     * @return int Nb active accounts
     */
    public static function getNbAccountsActive($id_ntbr_config)
    {
        return (int) Db::getInstance()->getValue('
            SELECT count(`id_ntbr_shadow_drive`)
            FROM `' . _DB_PREFIX_ . 'ntbr_shadow_drive`
            WHERE `id_ntbr_config` = ' . (int) $id_ntbr_config . '
            AND `active` = 1
        ');
    }

    /**
     * Get Shadow Drive account data by ID
     *
     * @param int $id_ntbr_shadow_drive ID of the Shadow Drive account
     *
     * @return array Data of the account
     */
    public static function getShadowDriveAccountById($id_ntbr_shadow_drive)
    {
        $shadow_drive_account = Db::getInstance()->getRow('
            SELECT `id_ntbr_shadow_drive`, `active`, `name`, `config_nb_backup`, `login`, `password`, `server`, `directory`
            FROM `' . _DB_PREFIX_ . 'ntbr_shadow_drive`
            WHERE `id_ntbr_shadow_drive` = ' . (int) $id_ntbr_shadow_drive . '
        ');

        if (!is_array($shadow_drive_account)) {
            return [];
        }

        return $shadow_drive_account;
    }

    /**
     * Get Shadow Drive account ID by name
     *
     * @param int $id_ntbr_config ID of the configuration
     * @param string $name Name of the Shadow Drive account
     *
     * @return int ID of the account
     */
    public static function getIdByName($id_ntbr_config, $name)
    {
        return (int) Db::getInstance()->getValue('
            SELECT `id_ntbr_shadow_drive`
            FROM `' . _DB_PREFIX_ . 'ntbr_shadow_drive`
            WHERE `name` = "' . pSQL($name) . '"
            AND `id_ntbr_config` = ' . (int) $id_ntbr_config . '
        ');
    }

    /**
     * Get nb Shadow Drive accounts
     *
     * @return int Nb accounts
     */
    public static function getNbAccounts()
    {
        return (int) Db::getInstance()->getValue('
            SELECT count(`id_ntbr_shadow_drive`)
            FROM `' . _DB_PREFIX_ . 'ntbr_shadow_drive`
        ');
    }

    /**
     * Deactive all Shadow Drive accounts
     *
     * @return bool Success or failure of the operation
     */
    public static function deactiveAllShadowDrive()
    {
        return Db::getInstance()->execute('
            UPDATE `' . _DB_PREFIX_ . 'ntbr_shadow_drive`
            SET `active` = 0
        ');
    }
}
