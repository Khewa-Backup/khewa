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

class ScanSizeNtbr extends ObjectModel
{
    /** @var string file_path */
    public $file_path;

    /** @var int file_size */
    public $file_size;

    /** @var string date_add */
    public $date_add;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'ntbr_scan_size',
        'primary' => 'id_ntbr_scan_size',
        'multilang' => false,
        'multilang_shop' => false,
        'fields' => [
            'file_path' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required' => true,
            ],
            'file_size' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
            ],
        ],
    ];

    /**
     * Get the last scan date
     *
     * @return string Date
     */
    public static function getLastDate()
    {
        return Db::getInstance()->getValue('
            SELECT MIN(`date_add`)
            FROM `' . _DB_PREFIX_ . 'ntbr_scan_size`
        ');
    }

    /**
     * Get the size of a path
     *
     * @return int Total size
     */
    public static function getTotalSize()
    {
        return (float) Db::getInstance()->getValue('
            SELECT SUM(`file_size`)
            FROM `' . _DB_PREFIX_ . 'ntbr_scan_size`
        ');
    }

    /**
     * Get the size of a path
     *
     * @return int Total size
     */
    public static function getSizeDirByPath($path)
    {
        $path = rtrim($path, '/') . '/';

        $result = (float) Db::getInstance()->getValue('
            SELECT SUM(`file_size`)
            FROM `' . _DB_PREFIX_ . 'ntbr_scan_size`
            WHERE `file_path` LIKE "' . pSQL($path) . '%"
        ');

        if ($result === false || $result === '') {
            return -1;
        }

        return (float) $result;
    }

    /**
     * Get the size of a path
     *
     * @return int Total size
     */
    public static function getSizeFileByPath($path)
    {
        $result = Db::getInstance()->getValue('
            SELECT `file_size`
            FROM `' . _DB_PREFIX_ . 'ntbr_scan_size`
            WHERE `file_path` = "' . pSQL($path) . '"
        ');

        if ($result === false || $result === '') {
            return -1;
        }

        return (float) $result;
    }

    /**
     * Get children of directory
     *
     * @return array List of children
     */
    public static function getDirChildren($path)
    {
        $list = [];
        $path = rtrim($path, '/') . '/';

        $result = Db::getInstance()->executeS('
            SELECT `file_path`
            FROM `' . _DB_PREFIX_ . 'ntbr_scan_size`
            WHERE `file_path` REGEXP "' . pSQL($path) . '[^/]+$|' . pSQL($path) . '[^/]+/{1}[^/]+$"
        ');

        foreach ($result as &$r) {
            $clean_path = str_replace($path, '', $r['file_path']);
            $pos = strpos($clean_path, '/');

            if ($pos !== false) {
                $r['file_path'] = $path . Tools::substr($clean_path, 0, $pos);
            }

            $list[$r['file_path']] = $r['file_path'];
        }

        return $list;
    }
}
