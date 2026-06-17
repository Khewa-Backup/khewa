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

class YandexNtbr extends ObjectModel
{
    /** @var int id_ntbr_config */
    public $id_ntbr_config;

    /** @var bool active */
    public $active;

    /** @var string name */
    public $name;

    /** @var int config_nb_backup */
    public $config_nb_backup;

    /** @var string directory */
    public $directory;

    /** @var string token */
    public $token;

    /** @var string date_add */
    public $date_add;

    /** @var string date_upd */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'ntbr_yandex',
        'primary' => 'id_ntbr_yandex',
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
                'default' => 'Yandex',
            ],
            'config_nb_backup' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'default' => '0',
            ],
            'directory' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'size' => 255,
                'default' => '',
            ],
            'token' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => true,
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
     * Get a list of all Yandex accounts
     *
     * @return array List of all Yandex accounts
     */
    public static function getListYandexAccounts($id_ntbr_config)
    {
        $yandex_accounts = Db::getInstance()->executeS('
            SELECT `id_ntbr_yandex`, `active`, `name`, `config_nb_backup`, `directory`, `token`
            FROM `' . _DB_PREFIX_ . 'ntbr_yandex`
            WHERE `id_ntbr_config` = ' . (int) $id_ntbr_config . '
            ORDER BY `date_upd` DESC
        ');

        if (!is_array($yandex_accounts)) {
            return [];
        }

        return $yandex_accounts;
    }

    /**
     * Get a list of all active Yandex accounts
     *
     * @return array List of all active Yandex accounts
     */
    public static function getListActiveYandexAccounts($id_ntbr_config)
    {
        $yandex_accounts = Db::getInstance()->executeS('
            SELECT `id_ntbr_yandex`, `active`, `name`, `config_nb_backup`, `directory`, `token`
            FROM `' . _DB_PREFIX_ . 'ntbr_yandex`
            WHERE `active` = 1
            AND `id_ntbr_config` = ' . (int) $id_ntbr_config . '
            ORDER BY `name`
        ');

        if (!is_array($yandex_accounts)) {
            return [];
        }

        return $yandex_accounts;
    }

    /**
     * Get nb Yandex active accounts
     *
     * @return int Nb active accounts
     */
    public static function getNbAccountsActive($id_ntbr_config)
    {
        return (int) Db::getInstance()->getValue('
            SELECT count(`id_ntbr_yandex`)
            FROM `' . _DB_PREFIX_ . 'ntbr_yandex`
            WHERE `id_ntbr_config` = ' . (int) $id_ntbr_config . '
            AND `active` = 1
        ');
    }

    /**
     * Get Yandex account data by ID
     *
     * @param int $id_ntbr_yandex ID of the Yandex account
     *
     * @return array Data of the account
     */
    public static function getYandexAccountById($id_ntbr_yandex)
    {
        $yandex_account = Db::getInstance()->getRow('
            SELECT `id_ntbr_yandex`, `active`, `name`, `config_nb_backup`, `directory`, `token`
            FROM `' . _DB_PREFIX_ . 'ntbr_yandex`
            WHERE `id_ntbr_yandex` = ' . (int) $id_ntbr_yandex . '
        ');

        if (!is_array($yandex_account)) {
            return [];
        }

        return $yandex_account;
    }

    /**
     * Get Yandex token by ID
     *
     * @param int $id_ntbr_yandex ID of the Yandex account
     *
     * @return string Token the account
     */
    public static function getYandexTokenById($id_ntbr_yandex)
    {
        return Db::getInstance()->getValue('
            SELECT `token`
            FROM `' . _DB_PREFIX_ . 'ntbr_yandex`
            WHERE `id_ntbr_yandex` = ' . (int) $id_ntbr_yandex . '
        ');
    }

    /**
     * Get Yandex account ID by name
     *
     * @param int $id_ntbr_config ID of the configuration
     * @param string $name Name of the Yandex account
     *
     * @return int ID of the account
     */
    public static function getIdByName($id_ntbr_config, $name)
    {
        return (int) Db::getInstance()->getValue('
            SELECT `id_ntbr_yandex`
            FROM `' . _DB_PREFIX_ . 'ntbr_yandex`
            WHERE `name` = "' . pSQL($name) . '"
            AND `id_ntbr_config` = ' . (int) $id_ntbr_config . '
        ');
    }

    /**
     * Get nb Yandex accounts
     *
     * @return int Nb accounts
     */
    public static function getNbAccounts()
    {
        return (int) Db::getInstance()->getValue('
            SELECT count(`id_ntbr_yandex`)
            FROM `' . _DB_PREFIX_ . 'ntbr_yandex`
        ');
    }

    /**
     * Deactive all Yandex accounts
     *
     * @return bool Success or failure of the operation
     */
    public static function deactiveAllYandex()
    {
        return Db::getInstance()->execute('
            UPDATE `' . _DB_PREFIX_ . 'ntbr_yandex`
            SET `active` = 0
        ');
    }
}
