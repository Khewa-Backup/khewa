<?php
/**
 * 2007-2020 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

namespace Pw\Favorites\Core;

use Db as PsDb;
use DbQuery;
use PrestaShopDatabaseException;

class Db
{
    /**
     * @var PsDb
     */
    protected static $db;

    public static function delete($table, $where = null)
    {
        return self::getDb()->delete($table, is_array($where) ? self::where($where) : $where);
    }

    public static function getDb()
    {
        if (!self::$db) {
            self::$db = PsDb::getInstance();
        }

        return self::$db;
    }

    /**
     * @param string|DbQuery $query
     *
     * @return array|false|\mysqli_result|\PDOStatement|resource|null
     *
     * @throws PrestaShopDatabaseException
     */
    public static function getResults($query)
    {
        return self::getDb()->executeS($query);
    }

    public static function getRow($query)
    {
        return self::getDb()->getRow($query);
    }

    public static function getValue($query)
    {
        return self::getDb()->getValue($query);
    }

    /**
     * @param       $table
     * @param array $data
     * @param int   $type
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     */
    public static function insert($table, array $data, $type = PsDb::INSERT)
    {
        return self::getDb()->insert($table, $data, false, true, $type);
    }

    public static function query($table = null, $alias = null)
    {
        $query = new DbQuery();

        if ($table) {
            $query->from($table, $alias);
        }

        return $query;
    }

    public static function update($table, array $data, $where = null)
    {
        return self::getDb()->update($table, $data, is_array($where) ? self::where($where) : $where);
    }

    public static function where(array $criteria)
    {
        $conditions = [];
        foreach ($criteria as $key => $value) {
            $where = '`'.bqSQL($key).'` ';
            if (null === $value) {
                $where .= 'IS NULL';
            } elseif (is_string($value)) {
                $where .= '= \''.pSQL($value).'\'';
            } elseif (is_bool($value)) {
                $where .= '= '.(int)$value;
            } else {
                $where .= '= '.pSQL($value);
            }

            $conditions[] = $where;
        }

        return '('.implode(') AND (', $conditions).')';
    }
}
