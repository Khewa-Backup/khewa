<?php
/**
 * RockPOS - Point of Sale for PrestaShop.
 *
 * @author    Hamsa Technologies
 * @copyright Hamsa Technologies
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 *
 */
abstract class SearchAbstract extends Search
{

    /**
     *
     * @var string
     */
    public $keyword;

    /**
     *
     * @var int
     */
    public $id_lang;

    public $iso_code;

    /**
     *
     * @var Shop
     */
    public $shop;

    /**
     *
     * @var int
     */
    public $limit = 12;

    /**
     *
     * @var int
     */
    public $offest = 0;

    /**
     *
     * @param string $keyword
     * @param Context $context
     */
    public function __construct($keyword = null, $context = null)
    {
        $context = $context ? $context : Context::getcontext();
        if (!is_null($keyword)) {
            $this->keyword = $keyword;
        }
        $this->id_lang = $context->language->id;
        $this->id_lang = $context->language->id;
        $this->iso_code = $context->language->iso_code;
        $this->shop = $context->shop;
    }

    abstract public function search($is_with_image = false);

    /**
     * @param $product_query
     * @return mixed
     */
    public function getWithImage($product_query)
    {
        $product_query->select('pl.`link_rewrite`');
        $product_query->select('IFNULL(i.`id_image`,0) AS `id_image`');
        $product_query->leftJoin('image', 'i', 'i.`id_product` = p.`id_product`AND i.`cover` = 1');
        $product_query->leftJoin('image_shop', 'image_shop', 'image_shop.`id_image` = i.`id_image` AND image_shop.id_shop=' . (int)$this->shop->id);
        $product_query->leftJoin('image_lang', 'il', 'image_shop.`id_image` = il.`id_image` AND il.`id_lang` = ' . (int)$this->id_lang);

        return $product_query;
    }

    public function orderByScore(&$products, $scored_words)
    {
        if (count($scored_words) && !empty($products)) {
            $start_search = Configuration::get('PS_SEARCH_START') ? '%' : '';
            $end_search = Configuration::get('PS_SEARCH_END') ? '' : '%';
            $score_query = new DbQuery();
            $score_query->select('SUM(`weight`) weight');
            $score_query->select('psi.`id_product`');
            $score_query->from('pos_search_word', 'psw');
            $score_query->leftJoin('pos_search_index', 'psi', 'psw.`id_word` = psi.`id_word`');
            $score_query->where('psw.`id_lang` = ' . (int)$this->id_lang);
            $score_query->where('psw.`id_shop` = ' . (int)$this->shop->id);
            $score_query->where('psi.`id_product` IN (' . implode(',', array_column($products, 'id_product')) . ')');

            $score_where = array();
            foreach ($scored_words as $scored_word) {
                $score_where[] = 'psw.`word` LIKE \'' . pSQL($start_search) . pSQL(Tools::substr($scored_word, 0, PS_SEARCH_MAX_WORD_LENGTH)) . pSQL($end_search) . '\'';
            }
            $score_query->where(implode(' OR ', $score_where));
            $score_query->groupBy('psi.`id_product`');
            $scores = Db::getInstance()->executeS($score_query);

            $results = [];

            foreach ($products as &$product) {
                foreach ($scores as $score) {
                    if ($product['id_product'] == $score['id_product']) {
                        $product['weight'] = $score['weight'];
                        unset($score);
                    }
                }
                if (!isset($product['weight'])) {
                    unset($product);
                }
            }
        }

        usort($products, function ($item1, $item2) {
            if ($item1['weight'] == $item2['weight']) {
                return 0;
            }
            return ($item1['weight'] < $item2['weight']) ? 1 : -1;
        });
    }
}
