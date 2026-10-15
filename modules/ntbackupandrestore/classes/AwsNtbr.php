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

class AwsNtbr extends ObjectModel
{
    /** @var int id_ntbr_config */
    public $id_ntbr_config;

    /** @var bool active */
    public $active;

    /** @var string name */
    public $name;

    /** @var int config_nb_backup */
    public $config_nb_backup;

    /** @var int type_s3 */
    public $type_s3;

    /** @var bool accept_unvalid_ssl */
    public $accept_unvalid_ssl;

    /** @var string access_key_id */
    public $access_key_id;

    /** @var string secret_access_key */
    public $secret_access_key;

    /** @var string region */
    public $region;

    /** @var string bucket */
    public $bucket;

    /** @var string host */
    public $host;

    /** @var string storage_class */
    public $storage_class;

    /** @var string directory_key */
    public $directory_key;

    /** @var string directory_path */
    public $directory_path;

    /** @var string date_add */
    public $date_add;

    /** @var string date_upd */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'ntbr_aws',
        'primary' => 'id_ntbr_aws',
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
            'accept_unvalid_ssl' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'default' => '0',
            ],
            'name' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'size' => 255,
                'required' => true,
                'default' => 'S3',
            ],
            'config_nb_backup' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'default' => '0',
            ],
            'type_s3' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'default' => NtbrCore::S3_TYPE_AWS,
            ],
            'access_key_id' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'size' => 255,
                'required' => true,
                'default' => '',
            ],
            'secret_access_key' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'size' => 255,
                'required' => true,
                'default' => '',
            ],
            'region' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'size' => 255,
                'required' => true,
                'default' => '',
            ],
            'bucket' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'size' => 255,
                'required' => true,
                'default' => '',
            ],
            'host' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => true,
                'default' => NtbrCore::S3_AWS_HOST,
            ],
            'storage_class' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => true,
                'default' => 'STANDARD',
            ],
            'directory_key' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'size' => 255,
                'default' => '',
            ],
            'directory_path' => [
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
     * Get a list of all AWS accounts
     *
     * @return array List of all AWS accounts
     */
    public static function getListAwsAccounts($id_ntbr_config)
    {
        $aws_accounts = Db::getInstance()->executeS('
            SELECT `id_ntbr_aws`, `active`, `name`, `config_nb_backup`, `access_key_id`, `secret_access_key`, `region`,
                `bucket`, `host`, `directory_key`, `directory_path`, `storage_class`, `type_s3`, `accept_unvalid_ssl`
            FROM `' . _DB_PREFIX_ . 'ntbr_aws`
            WHERE `id_ntbr_config` = ' . (int) $id_ntbr_config . '
            ORDER BY `date_upd` DESC
        ');

        if (!is_array($aws_accounts)) {
            return [];
        }

        return $aws_accounts;
    }

    /**
     * Get a list of all active AWS accounts
     *
     * @return array List of all active AWS accounts
     */
    public static function getListActiveAwsAccounts($id_ntbr_config)
    {
        $aws_accounts = Db::getInstance()->executeS('
            SELECT `id_ntbr_aws`, `active`, `name`, `config_nb_backup`, `access_key_id`, `secret_access_key`, `region`,
                `bucket`, `host`, `directory_key`, `directory_path`, `storage_class`, `type_s3`, `accept_unvalid_ssl`
            FROM `' . _DB_PREFIX_ . 'ntbr_aws`
            WHERE `active` = 1
            AND `id_ntbr_config` = ' . (int) $id_ntbr_config . '
            ORDER BY `name`
        ');

        if (!is_array($aws_accounts)) {
            return [];
        }

        return $aws_accounts;
    }

    /**
     * Get nb AWS active accounts
     *
     * @return int Nb active accounts
     */
    public static function getNbAccountsActive($id_ntbr_config)
    {
        return (int) Db::getInstance()->getValue('
            SELECT count(`id_ntbr_aws`)
            FROM `' . _DB_PREFIX_ . 'ntbr_aws`
            WHERE `id_ntbr_config` = ' . (int) $id_ntbr_config . '
            AND `active` = 1
        ');
    }

    /**
     * Get AWS account data by ID
     *
     * @param int $id_ntbr_aws ID of the AWS account
     *
     * @return array Data of the account
     */
    public static function getAwsAccountById($id_ntbr_aws)
    {
        $aws_account = Db::getInstance()->getRow('
            SELECT `id_ntbr_aws`, `active`, `name`, `config_nb_backup`, `access_key_id`, `secret_access_key`, `region`,
                `bucket`, `host`, `directory_key`, `directory_path`, `storage_class`, `type_s3`, `accept_unvalid_ssl`
            FROM `' . _DB_PREFIX_ . 'ntbr_aws`
            WHERE `id_ntbr_aws` = ' . (int) $id_ntbr_aws . '
        ');

        if (!is_array($aws_account)) {
            return [];
        }

        return $aws_account;
    }

    /**
     * Get AWS account ID by name
     *
     * @param int $id_ntbr_config ID of the configuration
     * @param string $name Name of the AWS account
     *
     * @return int ID of the account
     */
    public static function getIdByName($id_ntbr_config, $name)
    {
        return (int) Db::getInstance()->getValue('
            SELECT `id_ntbr_aws`
            FROM `' . _DB_PREFIX_ . 'ntbr_aws`
            WHERE `name` = "' . pSQL($name) . '"
            AND `id_ntbr_config` = ' . (int) $id_ntbr_config . '
        ');
    }

    /**
     * Get nb AWS accounts
     *
     * @return int Nb accounts
     */
    public static function getNbAccounts()
    {
        return (int) Db::getInstance()->getValue('
            SELECT count(`id_ntbr_aws`)
            FROM `' . _DB_PREFIX_ . 'ntbr_aws`
        ');
    }

    /**
     * Deactive all AWS accounts
     *
     * @return bool Success or failure of the operation
     */
    public static function deactiveAllAws()
    {
        return Db::getInstance()->execute('
            UPDATE `' . _DB_PREFIX_ . 'ntbr_aws`
            SET `active` = 0
        ');
    }
}
