<?php

if (!defined('_PS_VERSION_')) {
  exit;
}

require_once _PS_MODULE_DIR_ . 'featuredproducts/classes/MpmSliderTools.php';


class SliderProduct
{
  public static function getIdsOfProductsForSlider($product_type, $manually_selected_products, $manually_selected_categories, $id_category, $limit)
  {
    switch ($product_type) {
      case 'all':
        return self::getAllProductsIds();
      case 'category':
        return self::getCategoriseIds($manually_selected_categories);
      case 'products':
        return $manually_selected_products;
      case 'last_visited':
        return self::getProductsIdsOrderedByVisitTime($limit);
      case 'top':
        return self::getTopSellersIds();
      case 'new':
        return self::getNewProductsIds();
      case 'discount':
        return self::getDiscountIds();
      case 'current':
        return self::getCategoriseIds($id_category);
      default:
        return '';
    }
  }

  public static function getProductsByIds($ids, $order, $limit)
  {
    // Khewa PS 9.2: keep only real numeric ids; an empty IN() list was an SQL error and executeS() gave false
    $ids = implode(',', array_filter(array_map('intval', explode(',', (string) $ids))));
    if (!$ids) {
      return array();
    }

    $order_by = '';

    if ($order == 'random') {
      $order_by = 'RAND()';
    } elseif ($order == 'name') {
      $order_by = 'pl.name';
    } elseif ($order == 'date_add') {
      $order_by = 'p.date_add';
    }

    $id_shop = (int) MpmSliderTools::getShopId();

    // Khewa PS 9.2: read price/active from product_shop and the default combination from
    // product_attribute_shop for the current shop. The old query took the default combination from
    // ps_product_attribute (all shops). When that combination is not in this shop, PS 9.2 finds
    // no price and the product presenter crashes on formatPrice(null).
    $sql = '
			SELECT p.*, product_shop.*, pl.`description`, pl.`description_short`, pl.`available_now`,
					pl.`available_later`, pl.`link_rewrite`, pl.`meta_description`,  pl.`meta_title`, pl.`name`, IFNULL(pas.id_product_attribute, 0) AS id_product_attribute, pas.minimal_quantity AS product_attribute_minimal_quantity
					, i.id_image
      FROM ' . _DB_PREFIX_ . 'product_lang as pl
      INNER JOIN ' . _DB_PREFIX_ . 'product as p
      ON p.id_product = pl.id_product
      INNER JOIN ' . _DB_PREFIX_ . 'product_shop as product_shop
      ON product_shop.id_product = p.id_product AND product_shop.id_shop = ' . $id_shop . '
      LEFT JOIN ' . _DB_PREFIX_ . 'product_attribute_shop as pas
      ON p.id_product = pas.id_product AND pas.default_on = 1 AND pas.id_shop = ' . $id_shop . '
      LEFT JOIN ' . _DB_PREFIX_ . 'image_shop as i
      ON p.id_product = i.id_product AND i.cover = 1 AND i.id_shop = ' . $id_shop . '
      WHERE pl.id_lang = ' . MpmSliderTools::getLangId() . '
      AND pl.id_shop = ' . $id_shop . '
      AND product_shop.active = 1
      AND p.id_product IN(' . $ids . ')
      GROUP BY p.id_product
      ' . ($order_by ? 'ORDER BY ' . $order_by : '') . ' LIMIT ' . (int)$limit. '
			';

    $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

    // Khewa PS 9.2: always give back a list, never false (the caller loops over it)
    return is_array($result) ? $result : array();
  }

  private static function getProductsIdsOrderedByVisitTime($limit)
  {
    $all_ids = trim(Context::getContext()->cookie->viewed_slider, ' ,');

    if ($all_ids) {
      $all_ids = explode(",", $all_ids);
      $all_ids = array_slice($all_ids, -$limit);
      $all_ids = array_reverse($all_ids);
      $all_ids = implode(',', $all_ids);
    }

    return $all_ids;
  }

  private static function getDiscountIds()
  {
    $sql = '
    SELECT GROUP_CONCAT( a.id_product )  as id  FROM (SELECT p.id_product
        FROM ' . _DB_PREFIX_ . 'product_lang as pl
        INNER JOIN ' . _DB_PREFIX_ . 'product as p
        ON p.id_product = pl.id_product
        LEFT JOIN ' . _DB_PREFIX_ . 'specific_price as sp
        ON p.id_product = sp.id_product
        WHERE pl.id_lang = ' . MpmSliderTools::getLangId() . '
        AND pl.id_shop = ' . MpmSliderTools::getShopId() . '
        AND sp.id_specific_price IS NOT NULL
        AND p.active = 1) as a
        ';
    $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

    if (isset($res[0]['id']) && $res[0]['id']) {
      return $res[0]['id'];
    }

    return false;
  }

  private static function getTopSellersIds()
  {
    $sql = '
    SELECT GROUP_CONCAT( a.id_product )  as id  FROM (SELECT p.id_product
        FROM ' . _DB_PREFIX_ . 'product_sale as ps
        INNER JOIN ' . _DB_PREFIX_ . 'product_lang as pl
        ON ps.id_product = pl.id_product
        LEFT JOIN ' . _DB_PREFIX_ . 'product as p
        ON p.id_product = ps.id_product
        WHERE pl.id_lang = ' . MpmSliderTools::getLangId() . '
        AND pl.id_shop = ' . MpmSliderTools::getShopId() . '
        AND p.active = 1) as a
        ';
    $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

    if (isset($res[0]['id']) && $res[0]['id']) {
      return $res[0]['id'];
    }
    return false;
  }

  private static function getNewProductsIds()
  {
    $nb_days_new_product = (int) Configuration::get('PS_NB_DAYS_NEW_PRODUCT');

    $sql = '
    SELECT GROUP_CONCAT( a.id_product ) as id FROM (SELECT p.id_product
        FROM ' . _DB_PREFIX_ . 'product as p
        LEFT JOIN ' . _DB_PREFIX_ . 'product_lang as pl
        ON p.id_product = pl.id_product
        LEFT JOIN ' . _DB_PREFIX_ . 'product_shop as ps
        ON p.id_product = ps.id_product
        WHERE pl.id_lang = ' . MpmSliderTools::getLangId() . '
        AND ps.id_shop = ' . MpmSliderTools::getShopId() . '
        AND p.active = 1
        AND ps.`date_add` > "'.date('Y-m-d', strtotime('-'.$nb_days_new_product.' DAY')).'") as a
        ';

    $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

    if (isset($res[0]['id']) && $res[0]['id']) {
      return $res[0]['id'];
    }
    return false;
  }

  private static function getCategoriseIds($catIds)
  {
    if (!$catIds) {
      return false;
    }

    $sql = '
    SELECT GROUP_CONCAT( a.id_product )  as id  FROM (SELECT p.id_product
        FROM ' . _DB_PREFIX_ . 'product_lang as pl
        INNER JOIN ' . _DB_PREFIX_ . 'product as p
        ON p.id_product = pl.id_product
        INNER JOIN ' . _DB_PREFIX_ . 'category_product as cp
        ON p.id_product = cp.id_product
        WHERE pl.id_lang = ' . MpmSliderTools::getLangId() . '
        AND cp.id_category IN ( ' . pSQL($catIds) . ' )
        AND pl.id_shop = ' . MpmSliderTools::getShopId() . '
        AND p.active = 1) as a
        ';

    $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

    if (isset($res[0]['id']) && $res[0]['id']) {
      return $res[0]['id'];
    }

    return false;
  }

  private static function getAllProductsIds()
  {
    $sql = '
    SELECT GROUP_CONCAT( a.id_product )  as id  FROM (SELECT p.id_product
        FROM ' . _DB_PREFIX_ . 'product_lang as pl
        INNER JOIN ' . _DB_PREFIX_ . 'product as p
        ON p.id_product = pl.id_product
        WHERE pl.id_lang = ' . MpmSliderTools::getLangId() . '
        AND pl.id_shop = ' . MpmSliderTools::getShopId() . '
        AND p.active = 1) as a
        ';


    $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

    if (isset($res[0]['id']) && $res[0]['id']) {
      return $res[0]['id'];
    }
    return false;
  }
}