<?php
/**
 * 2020 Anvanto
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses. 
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 *  @author Anvanto <anvantoco@gmail.com>
 *  @copyright  2020 Anvanto
 *  @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of Anvanto
 */

class an_wish_products extends ObjectModel
{
    /**
     * @var int
     */
    public $id_wishlist_products;
    /**
     * @var int
     */
    public $id;

    public $id_wishlist;
    public $id_product;
    public $id_product_attribute;

    /** @var string Object creation date */
    public $date_add;

    /**
     * @var array
     */
    public static $definition = array(
        'table' => 'an_wishlist_products',
        'primary' => 'id_wishlist_products',
        'multilang' => false,
        'multilang_shop' => false,
        'fields' => array(
            'id_wishlist' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_product' => array('type' =>self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true ),
            'id_product_attribute' => array('type' =>self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => false ),
            'date_add' => array('type' => self::TYPE_DATE),
        ),
    );
    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id, $id_lang, $id_shop);
        if (Shop::isFeatureActive()) {
            $this->id_shop = Context::getContext()->shop->id;
        } else {
            $this->id_shop = 1;
        }
    }

    public function removeItem($productId, $id_wishlist, $id_product_attribute = 0)
    {
        $sql = "DELETE FROM "._DB_PREFIX_."an_wishlist_products 
		WHERE `id_product` = '".(int)$productId."' AND 
		`id_wishlist` = '".(int)$id_wishlist."'  AND 
		`id_product_attribute` = " . (int)$id_product_attribute . "
		";
        if (Db::getInstance()->execute($sql)) {
            return true;
        } else {
            return false;
        }
    }

    public static function issetItem($productId, $id_wishlist, $id_product_attribute = 0)
    {
        if (!$id_wishlist) {
            return false;
        }

        return (bool)Db::getInstance()->getValue('
            SELECT COUNT(*)
            FROM `' . _DB_PREFIX_ . 'an_wishlist_products`
            WHERE `id_wishlist` = ' . (int)$id_wishlist . '
			AND `id_product_attribute` = ' . (int)$id_product_attribute . '
            AND `id_product` = ' . (int)$productId);
    }

    public static function countProductsWishlistCustomer($id_customer)
    {
        if (!$id_customer) {
            return 0;
        }
		
		$id_wishlist = an_wish::findWishlistByCustomer($id_customer);

        return Db::getInstance()->getValue('
            SELECT COUNT(*)
            FROM `' . _DB_PREFIX_ . 'an_wishlist_products`
            WHERE `id_wishlist` = ' . (int)$id_wishlist);
    }

    public static function countProductsWishlist($id_wishlist)
    {
        if (!$id_wishlist) {
            return 0;
        }

        return Db::getInstance()->getValue('
            SELECT COUNT(*)
            FROM `' . _DB_PREFIX_ . 'an_wishlist_products`
            WHERE `id_wishlist` = ' . (int)$id_wishlist);
    }

    //  у скольких пользователей товар анходится в вишлисте
    public static function countProductsAllWishlists($productId)
    {
        if (!$productId) {
            return 0;
        }

        return Db::getInstance()->getValue('
            SELECT COUNT(*)
            FROM `' . _DB_PREFIX_ . 'an_wishlist_products`
            WHERE `id_product` = ' . (int)$productId );
    }


    //
    public static function getProductsWishlist($id_wishlist)
    {
        if (!$id_wishlist) {
            return [];
        }

		$sql = '
		SELECT  *, p.*
		FROM `' . _DB_PREFIX_ . 'an_wishlist_products` awl
		
        LEFT JOIN `' . _DB_PREFIX_ . 'product` p
            ON (p.`id_product` = awl.`id_product`)
		
		LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
            ON (p.`id_product` = pl.`id_product`
            AND pl.`id_lang` = ' . (int) Context::getContext()->language->id . Shop::addSqlRestrictionOnLang('pl') . ')		
		
		WHERE awl.`id_wishlist` = ' . (int) $id_wishlist . ' ';

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql, true, false);
		
        return Product::getProductsProperties(Context::getContext()->language->id, $result);
    }

    public function getTopProducts($limit = 10, $start = 0)
    {

        $context = Context::getContext();
        $id_lang = $context->language->id;

        $sql = '
        SELECT
            p.*, product_shop.*, stock.out_of_stock, IFnull(stock.quantity, 0) as quantity, pl.`description`, pl.`description_short`, pl.`available_now`, pl.`available_later`,
            IFnull(product_attribute_shop.id_product_attribute, 0) id_product_attribute,
            pl.`link_rewrite`, pl.`meta_description`, pl.`meta_keywords`, pl.`meta_title`,
            pl.`name`, image_shop.`id_image` id_image, il.`legend`, m.`name` AS manufacturer_name,
            
            (SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'an_wishlist_products`,  `' . _DB_PREFIX_ . 'an_wishlist` 
			WHERE `' . _DB_PREFIX_ . 'an_wishlist_products`.`id_product` = p.`id_product` AND `' . _DB_PREFIX_ . 'an_wishlist_products`.`id_wishlist` = `' . _DB_PREFIX_ . 'an_wishlist`.`id_wishlist`) AS count_wishlist,
			
			(SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'an_wishlist_products`,  `' . _DB_PREFIX_ . 'an_wishlist` 
			WHERE `' . _DB_PREFIX_ . 'an_wishlist_products`.`id_product` = p.`id_product` AND `' . _DB_PREFIX_ . 'an_wishlist_products`.`id_wishlist` = `' . _DB_PREFIX_ . 'an_wishlist`.`id_wishlist` AND `' . _DB_PREFIX_ . 'an_wishlist`.`is_guest` = 0) AS count_wishlist_guest
    
        FROM `' . _DB_PREFIX_ . 'an_wishlist_products` awl, `' . _DB_PREFIX_ . 'an_wishlist` anwish, `' . _DB_PREFIX_ . 'product` p
		
		
		
        ' . Shop::addSqlAssociation('product', 'p') . '
        LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_shop` product_attribute_shop
            ON (p.`id_product` = product_attribute_shop.`id_product` AND product_attribute_shop.`default_on` = 1 AND product_attribute_shop.id_shop=' . (int) $context->shop->id . ')
        ' . Product::sqlStock('p', 0, false, $context->shop) . '
        LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (
            p.`id_product` = pl.`id_product`
            AND pl.`id_lang` = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('pl') . '
        )
        LEFT JOIN `' . _DB_PREFIX_ . 'image_shop` image_shop
            ON (image_shop.`id_product` = p.`id_product` AND image_shop.cover=1 AND image_shop.id_shop=' . (int) $context->shop->id . ')
        LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il ON (image_shop.`id_image` = il.`id_image` AND il.`id_lang` = ' . (int) $id_lang . ')
        LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON (m.`id_manufacturer` = p.`id_manufacturer`)
        WHERE product_shop.`active` = 1
        AND product_shop.`show_price` = 1
        AND awl.`id_product` = p.`id_product`
        AND awl.`id_wishlist` = anwish.`id_wishlist`
        GROUP BY p.`id_product`
        ORDER BY count_wishlist DESC
        
        LIMIT ' . $start . ', '.(int)$limit.'
        ';

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        if (!$result) {
            return [];
        }

        return Product::getProductsProperties($id_lang, $result);
    }
}
