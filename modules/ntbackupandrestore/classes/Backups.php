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

class Backups extends ObjectModel
{
    /** @var int id_ntbr_config */
    public $id_ntbr_config;

    /** @var string backup_name */
    public $backup_name;

    /** @var string comment */
    public $comment;

    /** @var bool safe */
    public $safe;

    /** @var string date_add */
    public $date_add;

    /** @var string date_upd */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'ntbr_backups',
        'primary' => 'id_ntbr_backups',
        'multilang' => false,
        'multilang_shop' => false,
        'fields' => [
            'id_ntbr_config' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'backup_name' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => true,
            ],
            'comment' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
            ],
            'safe' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'default' => '0',
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
     * Get the comment of a backup
     *
     * @param string $backup_name Name of the backup
     *
     * @return string Comment of the backup
     */
    public static function getBackupComment($backup_name)
    {
        return Db::getInstance()->getValue('
            SELECT `comment`
            FROM `' . _DB_PREFIX_ . 'ntbr_backups`
            WHERE `backup_name` = "' . pSQL($backup_name) . '"
        ');
    }

    /**
     * Get the id_config of a backup
     *
     * @param string $backup_name Name of the backup
     *
     * @return int ID of the config used to create this backup
     */
    public static function getBackupIdConfig($backup_name)
    {
        return (int) Db::getInstance()->getValue('
            SELECT `id_ntbr_config`
            FROM `' . _DB_PREFIX_ . 'ntbr_backups`
            WHERE `backup_name` = "' . pSQL($backup_name) . '"
        ');
    }

    /**
     * Check if the backup exist in database
     *
     * @param string $backup_name Name of the backup
     *
     * @return int ID of the config used to create this backup
     */
    public static function backupExist($backup_name)
    {
        return (int) Db::getInstance()->getValue('
            SELECT COUNT(`id_ntbr_config`)
            FROM `' . _DB_PREFIX_ . 'ntbr_backups`
            WHERE `backup_name` = "' . pSQL($backup_name) . '"
        ');
    }

    /**
     * Check if the backup files exists
     *
     * @return int ID of the config used to create this backup
     */
    public static function clearBackupFile()
    {
        $list_infos = self::getListBackupsInfos();

        foreach ($list_infos as $infos) {
            $backup_dir = ConfigNtbr::getBackupDirectoryById($infos['id_ntbr_config']);

            // The backup_name does not contain the part, but we need to check for files with it
            $part_1_name = str_replace(
                '.' . NtbrChild::EXT_UNCOMPRESS,
                '.1.part.' . NtbrChild::EXT_UNCOMPRESS,
                $infos['backup_name']
            );

            if (!file_exists($backup_dir . $infos['backup_name']) && !file_exists($backup_dir . $part_1_name)) {
                $backup = new Backups($infos['id_ntbr_backups']);
                $backup->delete();
            }
        }
    }

    /**
     * Get infos of a backup
     *
     * @param string $backup_name Name of the backup
     *
     * @return array Infos of the backup
     */
    public static function getBackupInfos($backup_name)
    {
        $infos = Db::getInstance()->getRow('
            SELECT `id_ntbr_backups`, `id_ntbr_config`, `backup_name`, `comment`, `safe`
            FROM `' . _DB_PREFIX_ . 'ntbr_backups`
            WHERE `backup_name` = "' . pSQL($backup_name) . '"
        ');

        if (!is_array($infos)) {
            return [];
        }

        return $infos;
    }

    /**
     * Get list of backups infos
     *
     * @return array List of backups infos
     */
    public static function getListBackupsInfos()
    {
        $backups = [];

        $list = Db::getInstance()->executeS('
            SELECT `id_ntbr_backups`, `id_ntbr_config`, `backup_name`, `comment`, `safe`
            FROM `' . _DB_PREFIX_ . 'ntbr_backups`
        ');

        if (!is_array($list)) {
            return [];
        }

        foreach ($list as $item) {
            $backups[$item['backup_name']] = $item;
        }

        return $backups;
    }

    /**
     * Get list of backups infos for a given config ID
     *
     * @return array List of backups infos
     */
    public static function getListBackupsInfosByConfig($id_ntbr_config)
    {
        $backups = Db::getInstance()->executeS('
            SELECT `id_ntbr_backups`, `backup_name`, `comment`, `safe`
            FROM `' . _DB_PREFIX_ . 'ntbr_backups`
            WHERE `id_ntbr_config` = ' . (int) $id_ntbr_config . '
        ');

        if (!is_array($backups)) {
            return [];
        }

        return $backups;
    }
}
