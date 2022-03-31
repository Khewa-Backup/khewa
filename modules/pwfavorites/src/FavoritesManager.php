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

namespace Pw\Favorites;

use Cart;
use Context;
use Db as PsDb;
use Pw\Favorites\Core\Db;

class FavoritesManager
{
    const TABLE = 'pwfavorites';

    /**
     * @param int $id_product
     * @param int $id_customer
     *
     * @return bool
     *
     * @throws \PrestaShopDatabaseException
     */
    public static function add($id_product, $id_customer)
    {
        return Db::insert(self::TABLE, [
            'id_customer' => (int)$id_customer,
            'id_product' => (int)$id_product
        ], PsDb::INSERT_IGNORE);
    }

    /**
     * @param int $id_product
     * @param int $id_customer
     *
     * @return bool
     */
    public static function delete($id_product, $id_customer)
    {
        return Db::delete(self::TABLE, [
            'id_product' => (int)$id_product,
            'id_customer' => (int)$id_customer
        ]);
    }

    /**
     * @param int $id_customer
     *
     * @return bool
     */
    public static function deleteByCustomer($id_customer)
    {
        return Db::delete(self::TABLE, ['id_customer' => (int)$id_customer]);
    }

    /**
     * @param int $id_product
     *
     * @return bool
     */
    public static function deleteByProduct($id_product)
    {
        return Db::delete(self::TABLE, ['id_product' => (int)$id_product]);
    }

    /**
     * @param int $id_product
     * @param int $id_customer
     *
     * @return array
     */
    public static function get($id_product, $id_customer)
    {
        return Db::getRow(
            Db::query(self::TABLE)->where(Db::where([
                'id_product' => (int)$id_product,
                'id_customer' => (int)$id_customer
            ]))
        );
    }

    /**
     * @param string $email
     *
     * @return array
     *
     * @throws \PrestaShopDatabaseException
     */
    public static function getByCustomerEmail($email)
    {
        $context = Context::getContext();

        return Db::getResults(
            Db::query(self::TABLE, 'f')
                ->select('f.`id_product`, pl.`name`')
                ->leftJoin('product_lang', 'pl', 'pl.`id_product` = f.`id_product`')
                ->leftJoin('customer', 'c', 'c.`id_customer` = f.`id_customer`')
                ->where('pl.`id_lang` = '.(int)$context->language->id)
                ->where('pl.`id_shop` = '.(int)$context->shop->id)
                ->where('c.`email` = \''.pSQL($email).'\'')
        );
    }

    /**
     * @param Cart $cart
     *
     * @return array
     *
     * @throws \PrestaShopDatabaseException
     */
    public static function getNotInCart(Cart $cart)
    {
        if (!$products = $cart->getProducts()) {
            return [];
        }

        return Db::getResults(
            Db::query(self::TABLE)
                ->select('`id_product`')
                ->where('`id_product` NOT IN('.pSQL(implode(', ', array_column($products, 'id_product'))).')')
        );
    }

    /**
     * @param int $id_product
     * @param int $id_customer
     *
     * @return array
     */
    public static function getWithProductName($id_product, $id_customer)
    {
        $context = Context::getContext();

        return Db::getRow(
            Db::query('product_lang', 'pl')
                ->select('pl.`name`, f.`id_customer`')
                ->leftJoin(self::TABLE, 'f', 'f.`id_product` = pl.`id_product` AND f.`id_customer` = '.(int)$id_customer)
                ->where('pl.`id_lang` = '.(int)$context->language->id)
                ->where('pl.`id_shop` = '.(int)$context->shop->id)
                ->where('pl.`id_product` = '.(int)$id_product)
        );
    }
}
