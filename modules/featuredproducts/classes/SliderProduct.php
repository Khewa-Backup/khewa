<?php

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
    $order_by = '';

    if ($order == 'random') {
      $order_by = 'RAND()';
    } elseif ($order == 'name') {
      $order_by = 'pl.name';
    } elseif ($order == 'date_add') {
      $order_by = 'p.date_add';
    }

    $sql = '
			SELECT p.*, pl.`description`, pl.`description_short`, pl.`available_now`,
					pl.`available_later`, pl.`link_rewrite`, pl.`meta_description`, pl.`meta_keywords`, pl.`meta_title`, pl.`name`, IFNULL(pa.id_product_attribute, 0) AS id_product_attribute,pa.minimal_quantity AS product_attribute_minimal_quantity
					, i.id_image
      FROM ' . _DB_PREFIX_ . 'product_lang as pl
      INNER JOIN ' . _DB_PREFIX_ . 'product as p
      ON p.id_product = pl.id_product
      LEFT JOIN ' . _DB_PREFIX_ . 'product_attribute as pa
      ON p.id_product = pa.id_product AND pa.default_on = 1
      LEFT JOIN ' . _DB_PREFIX_ . 'image as i
      ON p.id_product = i.id_product AND i.cover = 1
      LEFT JOIN ' . _DB_PREFIX_ . 'category_product as c
      ON p.id_product = c.id_product
      WHERE pl.id_lang = ' . (int)Context::getContext()->language->id . '
      AND pl.id_shop = ' . (int)Context::getContext()->shop->id . '
      AND p.active = 1
      AND p.id_product IN(' . pSQL($ids) . ')
      GROUP BY p.id_product
       ORDER BY ' . pSQL($order_by) . ' LIMIT ' . (int)$limit. '
			';

    return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
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
        WHERE pl.id_lang = ' . (int)Context::getContext()->language->id . '
        AND pl.id_shop = ' . (int)Context::getContext()->shop->id . '
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
        WHERE pl.id_lang = ' . (int)Context::getContext()->language->id . '
        AND pl.id_shop = ' . (int)Context::getContext()->shop->id . '
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
        WHERE pl.id_lang = ' . (int)Context::getContext()->language->id . '
        AND ps.id_shop = ' . (int)Context::getContext()->shop->id . '
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
        WHERE pl.id_lang = ' . (int)Context::getContext()->language->id . '
        AND cp.id_category IN ( ' . pSQL($catIds) . ' )
        AND pl.id_shop = ' . (int)Context::getContext()->shop->id . '
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
        WHERE pl.id_lang = ' . (int)Context::getContext()->language->id . '
        AND pl.id_shop = ' . (int)Context::getContext()->shop->id . '
        AND p.active = 1) as a
        ';


    $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

    if (isset($res[0]['id']) && $res[0]['id']) {
      return $res[0]['id'];
    }
    return false;
  }
}