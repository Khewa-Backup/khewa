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

class Schema
{
    public static function create($name, array $columns, array $primary = [])
    {
        $sql = [];
        foreach ($columns as $columnName => $type) {
            $sql[] = sprintf('`%1$s` %2$s', $columnName, $type);
        }

        if ($primary) {
            $sql[] = self::primary($primary);
        }

        return Db::getDb()->execute(
            sprintf(
                'CREATE TABLE IF NOT EXISTS `%1$s` (%2$s) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8',
                _DB_PREFIX_.$name,
                implode(', ', $sql)
            )
        );
    }

    public static function drop($name)
    {
        return Db::getDb()->execute(
            'DROP TABLE IF EXISTS `'._DB_PREFIX_.$name.'`'
        );
    }

    protected static function primary(array $columns)
    {
        return 'PRIMARY KEY (`'.implode('`, `', $columns).'`)';
    }
}
