<?php
/**
 * RockPOS - Point of Sale for PrestaShop.
 *
 * @author    Hamsa Technologies
 * @copyright Hamsa Technologies
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * Search index for Point of Sale.
 */
class PosCustomerSearchIndex extends Search
{

    /**
     * @var array
     *            <pre>
     *            array(
     *            int,
     *            int,
     *            ...
     *            )</pre>
     */
    public $id_customers = array();

    /**
     * @var bool
     */
    public $full = false;

    /**
     * @var bool
     */
    public $delete = false;

    /**
     * @var array
     *            <pre>
     *            array(
     *            string => int,
     *            string => int,
     *            ...
     *            )</pre>
     */
    protected $weights = array();

    /**
     * @var array
     *            <pre>
     *            array(
     *            PosCustomerIndex,
     *            ...
     *            )
     */
    protected $customer_indexes = array();

    const PAGE_LIMIT = 50;

    /**
     * @param bool $full
     * @param bool $delete
     * @param int $id_customer
     */
    public function __construct($full = false, $delete = false, $id_customer = 0)
    {
        $this->full = (bool) $full;
        $this->delete = (bool) $delete;
        if ($id_customer > 0) {
            $this->id_customers = array((int) $id_customer);
        }
    }

    public function run()
    {
        ini_set('max_execution_time', 7200);
        $offset = (int) Tools::getValue('offset', 0);

        $this->loadWeights();
        if (empty($this->weights)) {
            return false;
        }

        $id_customers_to_index = array_diff($this->getAvailableIdCustomersByOffset($offset), $this->getIndexedIdCustomers());

        if (empty($id_customers_to_index)) {
            return true;
        }

        foreach (array_chunk($id_customers_to_index, self::PAGE_LIMIT) as $id_customer) {
            $this->customer_indexes = array();
            $this->loadCustomerIndexes($id_customer);
            if (empty($this->customer_indexes)) {
                continue;
            }
            foreach ($this->customer_indexes as $customer_index) {
                $customer_index->indexing();
            }
        }
        $this->clearCache();
        return true;
    }

    /**
     * Only return id_customers whose visibility are in scope.
     *
     * @return array
     *               <pre>
     *               array (
     *               int,
     *               int,
     *               ...
     *               )</pre>
     */
    protected function getAvailableIdCustomers()
    {
        $cache_key = __CLASS__ . __FUNCTION__;
        if (!Cache::isStored($cache_key)) {
            $id_customers_to_index = array();
            if (!empty($this->id_customers)) {
                $id_customers_to_index = $this->id_customers;
            } else {
                $offset = 0;
                $page = 1;
                while (1) {
                    $query = new DbQuery();
                    $query->select('c.`id_customer`');
                    $query->from('customer', 'c');
                    $query->join(Shop::addSqlAssociation('customer', 'c', true, null, true));
                    $query->limit(self::PAGE_LIMIT, $offset);
                    $customers = Db::getInstance()->executeS($query);

                    if (empty($customers)) {
                        break;
                    } else {
                        foreach ($customers as $customer) {
                            $id_customers_to_index[] = (int) $customer['id_customer'];
                        }
                        $offset = (++$page - 1) * self::PAGE_LIMIT;
                    }
                }
            }
            Cache::store($cache_key, $id_customers_to_index);
        }

        return Cache::retrieve($cache_key);
    }

    /**
     * Only return id_customers whose visibility are in scope.
     *
     * @return array
     *               <pre>
     *               array (
     *               int,
     *               int,
     *               ...
     *               )</pre>
     */
    protected function getAvailableIdCustomersByOffset($offset)
    {
        $cache_key = __CLASS__ . __FUNCTION__;
        if (!Cache::isStored($cache_key)) {
            $id_customers_to_index = array();
            if (!empty($this->id_customers)) {
                $id_customers_to_index = $this->id_customers;
            } else {
                $page = 1;
                $query = new DbQuery();
                $query->select('c.`id_customer`');
                $query->from('customer', 'c');
                $query->join(Shop::addSqlAssociation('customer', 'c', true, null, true));
                $query->where('c.`active` = 1');
                $query->limit(self::PAGE_LIMIT, $offset);
                $customers = Db::getInstance()->executeS($query);
                foreach ($customers as $customer) {
                    $id_customers_to_index[] = (int) $customer['id_customer'];
                }
            }
            Cache::store($cache_key, $id_customers_to_index);
        }

        return Cache::retrieve($cache_key);
    }

    /**
     * @param array $id_customer
     *                           <pre>
     *                           array(
     *                           int,
     *                           int,
     *                           ...
     *                           )
     */
    protected function loadCustomerIndexes(array $id_customers)
    {
        $db = Db::getInstance();
        $query = new DbQuery();
        $query->select('c.`id_customer`');
        $query->select('c.`id_shop`');
        $select_fields = array(
            'email' => 'c.`email`',
            'firstname' => 'c.`firstname`',
            'lastname' => 'c.`lastname`',
        );

        foreach ($select_fields as $key => $field) {
            if (isset($this->weights[$key])) {
                $query->select($field);
            }
        }
        $query->from('customer', 'c');
        $query->where('c.`id_customer` IN (' . implode(',', array_map('intval', $id_customers)) . ')');
        $customers_to_index = Db::getInstance()->executeS($query);
        foreach ($customers_to_index as $customer) {
            $pos_customer_index = new PosCustomerIndex($customer['id_customer'], $customer['id_shop']);

            foreach ($customer as $key => $value) {
                if (isset($this->weights[$key])) {
                    $words = is_array($value) ? $value : $this->breakKeywordsIntoArray($value, null, null);
                    $pos_customer_index->addWordIndexes($words, $this->weights[$key]);
                }
            }
            $this->customer_indexes[] = $pos_customer_index;
        }
    }

    protected function loadWeights()
    {
        $this->weights = array_filter(array(
            'firstname' => 5,
            'email' => 3,
            'lastname' => 4,
        ));
    }

    protected function delete()
    {
        $truncate = !Shop::isFeatureActive() || Shop::getContext() == Shop::CONTEXT_ALL;
        if ($this->full) {
            if ($truncate) {
                Db::getInstance()->execute('TRUNCATE ' . _DB_PREFIX_ . 'pos_customer_search_index');
                Db::getInstance()->execute('TRUNCATE ' . _DB_PREFIX_ . 'pos_customer_search_word');
            } else {
                Db::getInstance()->execute('DELETE `pcsi`, `pcsw` FROM `' . _DB_PREFIX_ . 'pos_customer_search_index` psi INNER JOIN `' . _DB_PREFIX_ . 'pos_customer_search_word` pcsw ON ( pcsw.`id_word` = pcsi.`id_word` AND pcsw.`id_shop` = ' . (int) Shop::getContextShopID() . ')');
            }
        } else {
            if ($this->id_customers) {
                $id_customers_to_delete = $this->id_customers;
            } else {
                // Add missing indexes:
                // Chances are, some products don't need to index anymore (due to settings changed).
                // In this case, just simply delete those indexes.
                $id_customers_to_index = $this->getAvailableIdCustomers();
                $indexed_id_customers = $this->getIndexedIdCustomers();
                $id_customers_to_delete = array_diff($indexed_id_customers, $id_customers_to_index);
            }
            if (!empty($id_customers_to_delete)) {
                $query = 'DELETE FROM `' . _DB_PREFIX_ . 'pos_customer_search_index` WHERE `id_customer` IN (' . implode(',', $id_customers_to_delete) . ')';
                Db::getInstance()->execute($query);
            }
        }
    }

    /**
     * @param string $string
     * @param int $id_lang
     * @param string $iso_code
     *
     * @return array
     *               <pre>
     *               array(
     *               string,
     *               string,
     *               ...
     *               )</pre>
     */
    protected function breakKeywordsIntoArray($string, $id_lang, $iso_code)
    {
        $words = array();
        $sanitize_words = explode(' ', $string);
        foreach ($sanitize_words as $word) {
            if (!empty($word)) {
                $words[] = $word;
            }
        }

        return $words;
    }

    /**
     * @param int $id_product
     *
     * @return array
     *               <pre>
     *               array(
     *               string => string,// field => keywords
     *               string => string,
     *               ...
     *               )</pre>
     */
    protected function getAttributeCodes($id_product)
    {
        $select_attribute_codes = array(
            'pa_reference' => 'GROUP_CONCAT(`reference` SEPARATOR " ") AS `pa_reference`',
            'pa_supplier_reference' => 'GROUP_CONCAT(`supplier_reference` SEPARATOR " ") AS `pa_supplier_reference`',
            'pa_ean13' => 'GROUP_CONCAT(`ean13` SEPARATOR " ") AS `pa_ean13`',
            'pa_upc' => 'GROUP_CONCAT(`upc` SEPARATOR " ") AS `pa_upc`',
        );
        $select_fields = array();
        foreach ($select_attribute_codes as $key => $select_field) {
            if (isset($this->weights[$key])) {
                $select_fields[] = $select_field;
            }
        }
        $product_attributes = array();
        if (!empty($select_fields)) {
            $query = new DbQuery();
            $query->select(implode(',', $select_fields));
            $query->from('product_attribute');
            $query->where('`id_product` = ' . (int) $id_product . ' ');
            $query->groupBy('`id_product`');
            $product_attributes = Db::getInstance()->getRow($query);
        }

        return !empty($product_attributes) ? $product_attributes : array();
    }

    /**
     * @return array
     *               <pre>
     *               array(
     *               int,
     *               int,
     *               ...
     *               )</pre>
     */
    protected function getIndexedIdCustomers()
    {
        $sub_query = new DbQuery(); // Get id_words based on shops
        $sub_query->select('DISTINCT `id_word`');
        $sub_query->from('pos_customer_search_word');
        $sub_query->where('`id_shop` IN (' . implode(',', Shop::getContextListShopID()) . ')');

        $query = new DbQuery(); // Get id_customers based on id_words
        $query->select('DISTINCT `id_customer`');
        $query->from('pos_customer_search_index');
        $query->where('`id_word` IN (' . $sub_query->build() . ')');
        $id_customers = Db::getInstance()->executeS($query, false);

        $indexed_id_customers = array();
        if (!empty($id_customers)) {
            foreach ($id_customers as $id_customer) {
                $indexed_id_customers[] = (int) $id_customer['id_customer'];
            }
        }

        return $indexed_id_customers;
    }

    protected function clearCache()
    {
        if (_PS_CACHE_ENABLED_) {
            switch (_PS_CACHING_SYSTEM_) {
                case 'CacheFs':
                    CacheFs::deleteCacheDirectory();
                    CacheFs::createCacheDirectories((int) Configuration::get('PS_CACHEFS_DIRECTORY_DEPTH'));
                    break;

                default:
                    Cache::getInstance()->flush();
                    break;
            }
        }
    }
}
