<?php
/**
 * khewacorechanges — replacement for the back-office Orders grid query builder.
 *
 * Carries Khewa's performance fix for the Orders list (CORE_CHANGES.md #3,
 * commit 85d3d4ed1 "Order list long time loading issue solved").
 *
 * Stock PrestaShop computes the "New customer" column with a correlated
 * sub-select executed once per row, which is very slow on a large orders
 * table. This version pre-computes each customer's first order once, as a
 * derived table (`fo`), and LEFT JOINs it into the base query.
 *
 * The core class is declared `final`, so it cannot be extended — this is a
 * full copy of core with the customization applied. It is wired in through
 * modules/khewacorechanges/config/services.yml, which re-declares the
 * service `prestashop.core.grid.query_builder.order` with this class.
 *
 * Everything not related to the `fo` join is verbatim PrestaShop 1.7.8 core.
 */

namespace KhewaCoreChanges\Grid\Query;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use PDO;
use PrestaShop\PrestaShop\Core\Grid\Query\DoctrineQueryBuilderInterface;
use PrestaShop\PrestaShop\Core\Grid\Query\DoctrineSearchCriteriaApplicatorInterface;
use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteriaInterface;

final class KhewaOrderQueryBuilder implements DoctrineQueryBuilderInterface
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var string
     */
    protected $dbPrefix;

    /**
     * @var int
     */
    private $contextLangId;

    /**
     * @var DoctrineSearchCriteriaApplicatorInterface
     */
    private $criteriaApplicator;

    /**
     * @var array
     */
    private $contextShopIds;

    /**
     * @param Connection $connection
     * @param string $dbPrefix
     * @param DoctrineSearchCriteriaApplicatorInterface $criteriaApplicator
     * @param int $contextLangId
     * @param int[] $contextShopIds
     */
    public function __construct(
        Connection $connection,
        $dbPrefix,
        DoctrineSearchCriteriaApplicatorInterface $criteriaApplicator,
        $contextLangId,
        array $contextShopIds
    ) {
        $this->connection = $connection;
        $this->dbPrefix = $dbPrefix;
        $this->contextLangId = $contextLangId;
        $this->criteriaApplicator = $criteriaApplicator;
        $this->contextShopIds = $contextShopIds;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchQueryBuilder(SearchCriteriaInterface $searchCriteria)
    {
        $qb = $this
            ->getBaseQueryBuilder($searchCriteria->getFilters())
            ->addSelect($this->getCustomerField() . ' AS `customer`')
            ->addSelect('o.id_order, o.reference, o.total_paid_tax_incl, os.paid, osl.name AS osname')
            ->addSelect('o.id_currency, cur.iso_code')
            ->addSelect('o.current_state, o.id_customer')
            ->addSelect('cu.`id_customer` IS NULL as `deleted_customer`')
            ->addSelect('os.color, o.payment, s.name AS shop_name')
            ->addSelect('o.date_add, cu.company, cl.name AS country_name, o.invoice_number, o.delivery_number')
        ;

        $this->addNewCustomerField($qb);

        $this->applySorting($qb, $searchCriteria);

        $qb = $this->applyNewCustomerFilter($qb, $searchCriteria->getFilters());

        $this->criteriaApplicator
            ->applyPagination($searchCriteria, $qb)
        ;

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function getCountQueryBuilder(SearchCriteriaInterface $searchCriteria)
    {
        $qb = $this->getBaseQueryBuilder($searchCriteria->getFilters());
        $qb = $this->applyNewCustomerFilter($qb, $searchCriteria->getFilters());
        $qb->select('count(o.id_order)');

        return $qb;
    }

    /**
     * KHEWA: derived table giving each customer's first order id.
     * Joined once as `fo` instead of a per-row correlated sub-select.
     */
    private function getFirstOrdersSubSelect(): string
    {
        return $this->connection
            ->createQueryBuilder()
            ->select('id_customer, MIN(id_order) as first_order')
            ->from($this->dbPrefix . 'orders')
            ->groupBy('id_customer')
            ->getSQL();
    }

    /**
     * KHEWA: "new customer" flag derived from the `fo` join.
     *
     * @param QueryBuilder $qb
     */
    private function addNewCustomerField(QueryBuilder $qb): void
    {
        $qb->addSelect('CASE WHEN fo.first_order = o.id_order THEN 1 ELSE 0 END AS new');
    }

    /**
     * @param array $filters
     *
     * @return QueryBuilder
     */
    private function getBaseQueryBuilder(array $filters)
    {
        $qb = $this->connection
            ->createQueryBuilder()
            ->from($this->dbPrefix . 'orders', 'o')
            // KHEWA: first orders as a derived table (see getFirstOrdersSubSelect)
            ->leftJoin(
                'o',
                '(' . $this->getFirstOrdersSubSelect() . ')',
                'fo',
                'o.id_customer = fo.id_customer'
            )
            ->leftJoin('o', $this->dbPrefix . 'customer', 'cu', 'o.id_customer = cu.id_customer')
            ->leftJoin('o', $this->dbPrefix . 'currency', 'cur', 'o.id_currency = cur.id_currency')
            ->innerJoin('o', $this->dbPrefix . 'address', 'a', 'o.id_address_delivery = a.id_address')
            ->innerJoin('a', $this->dbPrefix . 'country', 'c', 'a.id_country = c.id_country')
            ->innerJoin(
                'c',
                $this->dbPrefix . 'country_lang',
                'cl',
                'c.id_country = cl.id_country AND cl.id_lang = :context_lang_id'
            )
            ->leftJoin('o', $this->dbPrefix . 'order_state', 'os', 'o.current_state = os.id_order_state')
            ->leftJoin(
                'os',
                $this->dbPrefix . 'order_state_lang',
                'osl',
                'os.id_order_state = osl.id_order_state AND osl.id_lang = :context_lang_id'
            )
            ->leftJoin('o', $this->dbPrefix . 'shop', 's', 'o.id_shop = s.id_shop')
            ->andWhere('o.`id_shop` IN (:context_shop_ids)')
            ->setParameter('context_lang_id', $this->contextLangId, PDO::PARAM_INT)
            ->setParameter('context_shop_ids', $this->contextShopIds, Connection::PARAM_INT_ARRAY);

        $strictComparisonFilters = [
            'id_order' => 'o.id_order',
            'country_name' => 'c.id_country',
            'total_paid_tax_incl' => 'o.total_paid_tax_incl',
            'osname' => 'os.id_order_state',
        ];

        $likeComparisonFilters = [
            'reference' => 'o.`reference`',
            'company' => 'cu.`company`',
            'payment' => 'o.`payment`',
            'customer' => $this->getCustomerField(),
        ];

        $havingLikeComparisonFilters = [];

        $dateComparisonFilters = [
            'date_add' => 'o.`date_add`',
        ];

        foreach ($filters as $filterName => $filterValue) {
            if (isset($strictComparisonFilters[$filterName])) {
                $alias = $strictComparisonFilters[$filterName];

                $qb->andWhere("$alias = :$filterName");
                $qb->setParameter($filterName, $filterValue);

                continue;
            }

            if (isset($likeComparisonFilters[$filterName])) {
                $alias = $likeComparisonFilters[$filterName];

                $qb->andWhere("$alias LIKE :$filterName");
                $qb->setParameter($filterName, '%' . $filterValue . '%');

                continue;
            }

            if (isset($havingLikeComparisonFilters[$filterName])) {
                $alias = $havingLikeComparisonFilters[$filterName];

                $qb->andHaving("$alias LIKE :$filterName");
                $qb->setParameter($filterName, '%' . $filterValue . '%');

                continue;
            }

            if (isset($dateComparisonFilters[$filterName])) {
                $alias = $dateComparisonFilters[$filterName];

                if (isset($filterValue['from'])) {
                    $name = sprintf('%s_from', $filterName);

                    $qb->andWhere("$alias >= :$name");
                    $qb->setParameter($name, sprintf('%s %s', $filterValue['from'], '0:0:0'));
                }

                if (isset($filterValue['to'])) {
                    $name = sprintf('%s_to', $filterName);

                    $qb->andWhere("$alias <= :$name");
                    $qb->setParameter($name, sprintf('%s %s', $filterValue['to'], '23:59:59'));
                }

                continue;
            }
        }

        return $qb;
    }

    /**
     * @return string
     */
    private function getCustomerField()
    {
        return 'CONCAT(LEFT(cu.`firstname`, 1), \'. \', cu.`lastname`)';
    }

    /**
     * KHEWA: "new customer" filter uses the `fo` join instead of a sub-select.
     *
     * @param QueryBuilder $qb
     * @param array $filters
     *
     * @return QueryBuilder
     */
    private function applyNewCustomerFilter(QueryBuilder $qb, array $filters)
    {
        if (!isset($filters['new'])) {
            return $qb;
        }

        $builder = $qb
            ->andWhere('CASE WHEN fo.first_order = o.id_order THEN 1 ELSE 0 END = :new')
            ->setParameter('new', $filters['new']);

        foreach ($qb->getParameters() as $name => $previousParam) {
            $builder->setParameter(
                $name,
                $previousParam,
                is_array($previousParam) ? Connection::PARAM_INT_ARRAY : null
            );
        }

        return $builder;
    }

    /**
     * @param QueryBuilder $qb
     * @param SearchCriteriaInterface $criteria
     */
    private function applySorting(QueryBuilder $qb, SearchCriteriaInterface $criteria)
    {
        $sortableFields = [
            'id_order' => 'o.id_order',
            'country_name' => 'c.id_country',
            'total_paid_tax_incl' => 'o.total_paid_tax_incl',
            'reference' => 'o.`reference`',
            'company' => 'cu.`company`',
            'payment' => 'o.`payment`',
            'customer' => 'customer',
            'osname' => 'osl.name',
            'date_add' => 'o.`date_add`',
        ];

        if (isset($sortableFields[$criteria->getOrderBy()])) {
            $qb->orderBy(
                $sortableFields[$criteria->getOrderBy()],
                $criteria->getOrderWay()
            );
        }
    }
}
