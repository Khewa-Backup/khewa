<?php
/**
 * RockPOS - Point of Sale for PrestaShop.
 *
 * @author    Hamsa Technologies
 * @copyright Hamsa Technologies
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * Search index stats for Point of Sale.
 */
class PosCustomerCustomerIndexStats extends PosCustomerSearchIndex
{
    /**
     * @param array $id_shops
     *                        array(<pre>
     *                        int,
     *                        int,
     *                        ...
     *                        )</pre>
     *
     * @return int
     */
    public static function getTotalIndexedCustomers($id_shop)
    {
        $sub_query = new DbQuery(); // Get id_words based on shops
        $sub_query->select('DISTINCT `id_word`');
        $sub_query->from('pos_customer_search_word');
        $sub_query->where('`id_shop` = '.$id_shop);

        $query = new DbQuery(); // Count id_products based on id_words
        $query->select('COUNT(DISTINCT `id_customer`)');
        $query->from('pos_customer_search_index');
        $query->where('`id_word` IN (' . $sub_query->build() . ')');

        return (int)Db::getInstance()->getValue($query);
    }

    /**
     * @return int
     */
    public static function getTotalCustomers()
    {
        $query = new DbQuery();
        $query->select('COUNT(c.`id_customer`)');
        $query->from('customer', 'c');
        $query->join(Shop::addSqlAssociation('customer', 'c'));
        $query->where('c.`active` = 1');

        return (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query, false);
    }

    /**
     * @return int
     */
    public static function getTotalCustomersAllShop()
    {
        $query = new DbQuery();
        $query->select('COUNT(c.`id_customer`)');
        $query->from('customer', 'c');
        $query->join(Shop::addSqlAssociation('customer', 'c', true, null, true));
        $query->where('c.`active` = 1');

        return (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query, false);
    }
}
