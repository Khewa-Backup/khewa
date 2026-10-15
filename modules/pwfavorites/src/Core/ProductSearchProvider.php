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

use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchContext;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchProviderInterface;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchQuery;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchResult;
use PrestaShop\PrestaShop\Core\Product\Search\SortOrder;
use PrestaShop\PrestaShop\Core\Product\Search\SortOrderFactory;
use Symfony\Component\Translation\TranslatorInterface;

class ProductSearchProvider implements ProductSearchProviderInterface
{
    /**
     * @var SortOrderFactory
     */
    protected $sortOrderFactory;

    /**
     * ProductSearchProvider constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->sortOrderFactory = new SortOrderFactory($translator);
    }

    /**
     * @param ProductSearchContext $context
     * @param ProductSearchQuery   $query
     *
     * @return ProductSearchResult
     *
     * @throws \PrestaShopDatabaseException
     */
    public function runQuery(ProductSearchContext $context, ProductSearchQuery $query)
    {
        $result = new ProductSearchResult();

        /** @var SortOrder[] $sortOrders */
        $sortOrders = $this->sortOrderFactory->getDefaultSortOrders();
        array_shift($sortOrders);

        $sortOrderNames = [];
        foreach ($sortOrders as $sortOrder) {
            $sortOrderNames[] = $sortOrder->getField();
        }

        /** @var SortOrder $sortOrder */
        $sortOrder = $query->getSortOrder();

        if (in_array($sortOrder->getField(), $sortOrderNames)) {
            $products = $this->getProducts($context, false, $query->getPage(), $query->getResultsPerPage(), $sortOrder->toLegacyOrderBy(), $sortOrder->toLegacyOrderWay());
            $count = $this->getProducts($context, true);

            $result
                ->setProducts($products)
                ->setTotalProductsCount($count)
                ->setAvailableSortOrders($sortOrders);
        }

        return $result;
    }

    /**
     * @param ProductSearchContext $context
     * @param bool                 $count
     * @param int                  $page
     * @param int                  $perPage
     * @param string|null          $orderBy
     * @param string|null          $orderWay
     *
     * @return array|int
     *
     * @throws \PrestaShopDatabaseException
     */
    protected function getProducts(ProductSearchContext $context, $count = false, $page = 1, $perPage = 12, $orderBy = null, $orderWay = null)
    {
        $orderByPrefix = null;
        if ('price' === $orderBy) {
            $orderByPrefix = 'ps';
        }

        $query = Db::query('pwfavorites', 'f')
            ->select($count ? 'COUNT(*)' : null)
            ->leftJoin('product_lang', 'pl', 'pl.`id_product` = f.`id_product`')
            ->leftJoin('product_shop', 'ps', 'ps.`id_product` = f.`id_product` AND ps.`id_shop` = '.(int)$context->getIdShop())
            ->where('f.`id_customer` = '.(int)$context->getIdCustomer())
            ->where('pl.`id_lang` = '.(int)$context->getIdLang())
            ->where('pl.`id_shop` = '.(int)$context->getIdShop())
            ->where('ps.`active` = 1');

        if (!$count) {
            $query->limit($perPage, ($page - 1) * $perPage);
        }

        if (!$count && $orderBy) {
            $query->orderBy(($orderByPrefix ? $orderByPrefix.'.' : '').$orderBy.($orderWay ? ' '.$orderWay : ''));
        }

        return $count ? (int)Db::getValue($query) : Db::getResults($query);
    }
}
