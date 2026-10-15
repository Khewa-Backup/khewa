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

class GlobConfNtbr extends ObjectModel
{
    /** @var string name */
    public $name;

    /** @var string value */
    public $value;

    /** @var string date_add */
    public $date_add;

    /** @var string date_upd */
    public $date_upd;

    protected static $_ntbr_cache_glob_config;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'ntbr_glob_conf',
        'primary' => 'id_ntbr_glob_conf',
        'multilang' => false,
        'multilang_shop' => false,
        'fields' => [
            'name' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isConfigName',
                'required' => true,
            ],
            'value' => [
                'type' => self::TYPE_STRING,
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
     * Get config value by name
     *
     * @param string $name Name of the config
     *
     * @return mixed Value of the config
     */
    public static function get($name)
    {
        if (!isset(self::$_ntbr_cache_glob_config[$name]) || !defined('_PS_CACHE_ENABLED_') || !_PS_CACHE_ENABLED_) {
            self::$_ntbr_cache_glob_config[$name] = Db::getInstance()->getValue('
                SELECT `value`
                FROM `' . _DB_PREFIX_ . 'ntbr_glob_conf`
                WHERE `name` = "' . pSQL($name) . '"
            ');
        }

        return self::$_ntbr_cache_glob_config[$name];
    }

    /**
     * Check if config exists
     *
     * @param string $name Name of the config
     *
     * @return int ID if exists
     */
    public static function checkExists($name)
    {
        if (!isset(self::$_ntbr_cache_glob_config[$name]) || !defined('_PS_CACHE_ENABLED_') || !_PS_CACHE_ENABLED_) {
            return (int) Db::getInstance()->getValue('
                SELECT `id_ntbr_glob_conf`
                FROM `' . _DB_PREFIX_ . 'ntbr_glob_conf`
                WHERE `name` = "' . pSQL($name) . '"
            ');
        }

        return isset(self::$_ntbr_cache_glob_config[$name]) ? true : false;
    }

    /**
     * Check config last changed date
     *
     * @param string $name Name of the config
     *
     * @return string Date of last change for the config
     */
    public static function getLastChangeDate($name)
    {
        if (!self::checkExists($name)) {
            return '0000-00-00 00:00:00';
        }

        return Db::getInstance()->getValue('
            SELECT `date_upd`
            FROM `' . _DB_PREFIX_ . 'ntbr_glob_conf`
            WHERE `name` = "' . pSQL($name) . '"
        ');
    }

    /**
     * Set config value by name
     *
     * @param string $name Name of the config
     * @param   mixed       Value of the config
     *
     * @return bool Success or failure
     */
    public static function set($name, $value)
    {
        if (!Validate::isConfigName($name)) {
            return false;
        }

        if (self::checkExists($name)) {
            $result = Db::getInstance()->update(
                self::$definition['table'],
                [
                    'value' => pSQL($value),
                    'date_upd' => date('Y-m-d H:i:s'),
                ],
                '`name` = "' . pSQL($name) . '"'
            );
        } else {
            $now = date('Y-m-d H:i:s');
            $data = [
                'name' => pSQL($name),
                'value' => pSQL($value),
                'date_add' => $now,
                'date_upd' => $now,
            ];
            $result = Db::getInstance()->insert(self::$definition['table'], $data);
        }

        if (!$result) {
            return false;
        }

        self::$_ntbr_cache_glob_config[$name] = $value;

        return true;
    }

    /**
     * Delete a configuration name in database
     *
     * @param string $name Name to delete
     *
     * @return bool Deletion result
     */
    public static function deleteByName($name)
    {
        if (!self::checkExists($name)) {
            return true;
        }

        $result = Db::getInstance()->execute('
        DELETE FROM `' . _DB_PREFIX_ . bqSQL(self::$definition['table']) . '`
        WHERE `name` = "' . pSQL($name) . '"');

        self::$_ntbr_cache_glob_config = false;

        return $result;
    }
}
