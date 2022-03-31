<?php

class productsUpdateModel
{
  private $_context;

  public function __construct(){
    include_once(dirname(__FILE__).'/../../config/config.inc.php');
    include_once(dirname(__FILE__).'/../../init.php');
    $this->_context = Context::getContext();
  }

  public function searchProduct( $id_shop = false, $id_lang  = false, $search = false )
  {
    if($id_shop === false){
      $id_shop =  $this->_context->shop->id ;
    }
    if($id_lang === false){
      $id_lang =  $this->_context->language->id ;
    }
    $where = "";
    if( $search ){
      $where = " AND (pl.name LIKE '%".pSQL($search)."%' OR p.id_product LIKE '%".pSQL($search)."%' OR p.reference LIKE '%".pSQL($search)."%')";
    }
    $sql = '
			SELECT p.id_product, pl.name, p.reference
      FROM ' . _DB_PREFIX_ . 'product_lang as pl
      LEFT JOIN ' . _DB_PREFIX_ . 'product as p
      ON p.id_product = pl.id_product
      WHERE pl.id_lang = ' . (int)$id_lang . '
      AND pl.id_shop = ' . (int)$id_shop . '
      ' . $where . '
      ORDER BY p.id_product
      LIMIT 0,50
			';
    return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
  }

  public function searchManufacturer( $search = false )
  {
    $where = "";
    if( $search ){
      $where = " AND (m.name LIKE '%".pSQL($search)."%' OR m.id_manufacturer LIKE '%".pSQL($search)."%')";
    }
    $sql = '
			SELECT m.id_manufacturer, m.name
      FROM ' . _DB_PREFIX_ . 'manufacturer as m
      WHERE 1
      ' . $where . '
      ORDER BY m.name
      LIMIT 0,50
			';
    return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
  }

  public function searchSupplier( $search = false )
  {
    $where = "";
    if( $search ){
      $where = " AND (p.name LIKE '%".pSQL($search)."%' OR p.id_supplier LIKE '%".pSQL($search)."%')";
    }
    $sql = '
			SELECT p.id_supplier, p.name
      FROM ' . _DB_PREFIX_ . 'supplier as p
      WHERE 1
      ' . $where . '
      ORDER BY p.name
      LIMIT 0,50
			';
    return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
  }

  public function showCheckedProducts( $id_shop = false, $id_lang  = false, $products_check = false )
  {
    if($id_shop === false){
      $id_shop = $this->_context->shop->id ;
    }
    if($id_lang === false){
      $id_lang = $this->_context->language->id ;
    }
    $where = "";
    $limit = "  LIMIT 300 ";
    if( $products_check !== false ){
      if( !$products_check ){
        return array();
      }
      $products_check = implode(",", $products_check);
      $where = " AND p.id_product  IN ($products_check) ";
      $limit = "";
    }
    $sql = '
			SELECT p.id_product, pl.name
      FROM ' . _DB_PREFIX_ . 'product_lang as pl
      LEFT JOIN ' . _DB_PREFIX_ . 'product as p
      ON p.id_product = pl.id_product
      WHERE pl.id_lang = ' . (int)$id_lang . '
      AND pl.id_shop = ' . (int)$id_shop . '
      ' . $where . '
      ORDER BY pl.name
      ' . $limit . '
			';

    return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
  }

  public function showCheckedManufacturers( $items_check = false )
  {
    $where = "";
    $limit = "  LIMIT 300 ";
    if( $items_check !== false ){
      if( !$items_check ){
        return array();
      }
      $items_check = implode(",", $items_check);
      $where = " AND m.id_manufacturer  IN (".pSQL($items_check).") ";
      $limit = "";
    }
    $sql = '
			SELECT m.id_manufacturer, m.name
      FROM ' . _DB_PREFIX_ . 'manufacturer as m
      WHERE 1
      ' . $where . '
      ORDER BY m.name
      ' . $limit . '
			';

    return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
  }

  public function showCheckedSuppliers( $items_check = false )
  {
    $where = "";
    $limit = "  LIMIT 300 ";
    if( $items_check !== false ){
      if( !$items_check ){
        return array();
      }
      $items_check = implode(",", $items_check);
      $where = " AND s.id_supplier  IN (".pSQL($items_check).") ";
      $limit = "";
    }
    $sql = '
			SELECT s.id_supplier, s.name
      FROM ' . _DB_PREFIX_ . 'supplier as s
      WHERE 1
      ' . $where . '
      ORDER BY s.name
      ' . $limit . '
			';

    return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
  }

  public function getProductSuppliersID(  $productId = false ){

    $sql = '
			SELECT GROUP_CONCAT(DISTINCT ps.id_supplier SEPARATOR ";") as suppliers_ids,
			GROUP_CONCAT(DISTINCT s.name SEPARATOR ";") as suppliers_name
      FROM ' . _DB_PREFIX_ . 'product_supplier as ps
      INNER JOIN ' . _DB_PREFIX_ . 'supplier as s
       ON ps.id_supplier = s.id_supplier
      WHERE  ps.id_product = '.(int)$productId.'
      AND ps.id_product_attribute = 0
			';

    return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    
  }


  public function getExportIds( $idShop, $idLang, $shopGroupId, $separate , $more_settings, $limit = 0, $limitN, $count = false, $specific_price_max_count = false)
  {
    if( !$limit ){
      $limit = " LIMIT 0,".(int)$limitN." ";
    } else {
      $limit = " LIMIT ".( (int)$limit*(int)$limitN ).",".(int)$limitN." ";
    }

    $products_check = Tools::unserialize(Configuration::get('GOMAKOIL_PRODUCTS_CHECKED','',$shopGroupId, $idShop));
    $selected_manufacturers = Tools::unserialize(Configuration::get('GOMAKOIL_MANUFACTURERS_CHECKED','',$shopGroupId, $idShop));
    $selected_suppliers = Tools::unserialize(Configuration::get('GOMAKOIL_SUPPLIERS_CHECKED','',$shopGroupId, $idShop));
    $selected_categories = Tools::unserialize(Configuration::get('GOMAKOIL_CATEGORIES_CHECKED','',$shopGroupId, $idShop));
    $where = "";
    $justProducts = true;



    $price = $more_settings['price_products'];
    $quantity = $more_settings['quantity_products'];

    if($price['price_value'] !== '' && $price['selection_type_price']){
      if($price['selection_type_price'] == 1){
        $where .= ' AND (ps.price) < '. (float)$price['price_value'];
      }
      if($price['selection_type_price'] == 2){
        $where .= ' AND (ps.price) > '. (float)$price['price_value'];
      }
      if($price['selection_type_price'] == 3){
        $where .= ' AND (ps.price) = '. (float)$price['price_value'];
      }
    }

    if($quantity['quantity_value'] !== '' && $quantity['selection_type_quantity']){
      if($quantity['selection_type_quantity'] == 1){
        $where .= ' AND (sa.quantity) < '. (int)$quantity['quantity_value'];
      }
      if($quantity['selection_type_quantity'] == 2){
        $where .= ' AND (sa.quantity) > '. (int)$quantity['quantity_value'];
      }
      if($quantity['selection_type_quantity'] == 3){
        $where .= ' AND (sa.quantity) = '. (int)$quantity['quantity_value'];
      }
    }

    if($more_settings['active_products']){
      $where .= " AND ps.active = 1 ";
    }

    if($more_settings['inactive_products']){
      $where .= " AND ps.active = 0 ";
    }

    if($more_settings['ean_products']){
      $where .= " AND p.ean13 != 0 ";
    }

    if($more_settings['specific_prices_products']){
      $where .= " AND sp.id_specific_price != 0 ";
    }

    if($more_settings['selection_type_visibility']){
      $visibility = '';
      foreach($more_settings['selection_type_visibility'] as $keu=>$value){
        if($value == 1){
          $visibility .= "'both'";
        }
        elseif($value == 2){
          $visibility .= "'catalog'";
        }
        elseif($value == 3){
          $visibility .= "'search'";
        }
        elseif($value == 4){
          $visibility .= "'none'";
        }
        $visibility .= ',';
      }

      $visibility = Tools::substr($visibility, 0, -1);
      $where .= " AND ps.visibility IN (".pSQL($visibility).") ";
    }

    if($more_settings['selection_type_condition']){
      $condition = '';
      foreach($more_settings['selection_type_condition'] as $keu=>$value){
        if($value == 1){
          $condition .= "'new'";
        }
        elseif($value == 2){
          $condition .= "'used'";
        }
        elseif($value == 3){
          $condition .= "'refurbished'";
        }
        $condition .= ',';
      }

      $condition = Tools::substr($condition, 0, -1);
      $where .= " AND ps.condition IN (".pSQL($condition).") ";
    }


    if( $selected_manufacturers ){
      $justProducts = false;
      $selected_manufacturers = implode(",", $selected_manufacturers);
      $where .= " AND p.id_manufacturer IN (".pSQL($selected_manufacturers).") ";
    }

    if( $selected_suppliers ){
      $justProducts = false;
      $selected_suppliers = implode(",", $selected_suppliers);
      $where .= " AND s.id_supplier IN (".pSQL($selected_suppliers).") ";
    }

    if( $selected_categories ){
      $justProducts = false;
      $selected_categories = implode(",", $selected_categories);
      $where .= " AND cp.id_category IN (".pSQL($selected_categories).") ";
    }

    if( $products_check ){
      $products_check = implode(",", $products_check);
      $justProducts = $justProducts ? 'AND' : 'OR';
      $where .= " $justProducts p.id_product IN (".pSQL($products_check).") ";
    }

    $orderby = $more_settings['orderby'];
    $orderway = $more_settings['orderway'];

    if(!$separate){

      $select = ' DISTINCT p.id_product ';

      if( $count ){
        $select = ' count(DISTINCT p.id_product) as count ';
        $order = ' ORDER BY p.id_product DESC';
      }
      else{
        if($orderway == 'asc'){
          $order_way = ' ASC';
        }
        else{
          $order_way = ' DESC';
        }
        if($orderby == 'id'){
          $order = ' ORDER BY p.id_product'.$order_way;
        }
        if($orderby == 'name'){
          $order = ' ORDER BY pl.name '.$order_way.', p.id_product ASC';
        }
        if($orderby == 'price'){
          $order = ' ORDER BY p.price '.$order_way.', p.id_product ASC';
        }
        if($orderby == 'quantity'){
          $order = ' ORDER BY sa.quantity '.$order_way.', p.id_product ASC';
        }
        if($orderby == 'date_add'){
          $order = ' ORDER BY p.date_add '.$order_way.', p.id_product ASC';
        }
        if($orderby == 'date_update'){
          $order = ' ORDER BY p.date_upd '.$order_way.', p.id_product ASC';
        }
      }

      $sql = "
        SELECT $select
         FROM " . _DB_PREFIX_ . "product as p
         INNER JOIN " . _DB_PREFIX_ . "product_shop as ps
         ON p.id_product = ps.id_product
         LEFT JOIN " . _DB_PREFIX_ . "category_product as cp
         ON p.id_product = cp.id_product
         LEFT JOIN " . _DB_PREFIX_ . "product_lang as pl
         ON p.id_product = pl.id_product
         LEFT JOIN " . _DB_PREFIX_ . "product_supplier as s
         ON p.id_product = s.id_product
         LEFT JOIN " . _DB_PREFIX_ . "stock_available as sa
         ON p.id_product = sa.id_product AND sa.id_product_attribute = 0
         LEFT JOIN " . _DB_PREFIX_ . "product_attribute as pa
         ON p.id_product = pa.id_product
         LEFT JOIN " . _DB_PREFIX_ . "specific_price as sp
         ON p.id_product = sp.id_product
         WHERE ps.id_shop = " . (int)$idShop . "
          AND pl.id_lang = " . (int)$idLang . "
         " . $where . "
         " . $order . "
         " . $limit . "

      ";
    }
    else{

      if( $count ){
        $sql = "
        SELECT count(*) as count FROM (SELECT DISTINCT p.id_product , pa.id_product_attribute
         FROM " . _DB_PREFIX_ . "product as p
         INNER JOIN " . _DB_PREFIX_ . "product_shop as ps
         ON p.id_product = ps.id_product
         LEFT JOIN " . _DB_PREFIX_ . "category_product as cp
         ON p.id_product = cp.id_product
         LEFT JOIN " . _DB_PREFIX_ . "product_supplier as s
         ON p.id_product = s.id_product
         LEFT JOIN " . _DB_PREFIX_ . "product_attribute as pa
         ON p.id_product = pa.id_product
         LEFT JOIN " . _DB_PREFIX_ . "stock_available as sa
         ON p.id_product = sa.id_product AND sa.id_product_attribute = 0
         LEFT JOIN " . _DB_PREFIX_ . "specific_price as sp
         ON p.id_product = sp.id_product
         WHERE ps.id_shop = " . (int)$idShop . "
            ".$where."
      ) as a
      ";
      }
      else{


        $order = ' ORDER BY p.id_product DESC, pa.id_product_attribute';
        if($orderway == 'asc'){
          $order_way = ' ASC';
        }
        else{
          $order_way = ' DESC';
        }
        if($orderby == 'id'){
          $order = ' ORDER BY p.id_product'.$order_way.', pa.id_product_attribute ASC';
        }
        if($orderby == 'name'){
          $order = ' ORDER BY pl.name '.$order_way.', pa.id_product_attribute ASC';
        }
        if($orderby == 'price'){
          $order = ' ORDER BY p.price '.$order_way.', pa.price '.$order_way.', pa.id_product_attribute ASC';
        }
        if($orderby == 'quantity'){
          $order = ' ORDER BY sa.quantity '.$order_way.', pa.id_product_attribute ASC';
        }
        if($orderby == 'date_add'){
          $order = ' ORDER BY p.date_add '.$order_way.', pa.id_product_attribute ASC';
        }
        if($orderby == 'date_update'){
          $order = ' ORDER BY p.date_upd '.$order_way.', pa.id_product_attribute ASC';
        }

        $sql = "
        SELECT DISTINCT p.id_product , pa.id_product_attribute
         FROM " . _DB_PREFIX_ . "product as p
         INNER JOIN " . _DB_PREFIX_ . "product_shop as ps
         ON p.id_product = ps.id_product
         LEFT JOIN " . _DB_PREFIX_ . "category_product as cp
         ON p.id_product = cp.id_product
         LEFT JOIN " . _DB_PREFIX_ . "product_lang as pl
         ON p.id_product = pl.id_product
         LEFT JOIN " . _DB_PREFIX_ . "product_supplier as s
         ON p.id_product = s.id_product
         LEFT JOIN " . _DB_PREFIX_ . "product_attribute as pa
         ON p.id_product = pa.id_product
         LEFT JOIN " . _DB_PREFIX_ . "stock_available as sa
         ON p.id_product = sa.id_product AND sa.id_product_attribute = 0
         LEFT JOIN " . _DB_PREFIX_ . "specific_price as sp
         ON p.id_product = sp.id_product
         WHERE ps.id_shop = " . (int)$idShop . "
         AND pl.id_lang = " . (int)$idLang . "
         " . $where . "
         " . $order . "
         " . $limit . "
      ";
      }

    }


    $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

    if ($specific_price_max_count) {
      $sql = "
      SELECT max(specific_price_count) as specific_price_max_count
      FROM(
        SELECT  count(DISTINCT sp.id_specific_price) as specific_price_count
         FROM " . _DB_PREFIX_ . "product as p
         INNER JOIN " . _DB_PREFIX_ . "product_shop as ps
         ON p.id_product = ps.id_product
         LEFT JOIN " . _DB_PREFIX_ . "category_product as cp
         ON p.id_product = cp.id_product
         LEFT JOIN " . _DB_PREFIX_ . "image as i
         ON p.id_product = i.id_product     
         LEFT JOIN " . _DB_PREFIX_ . "product_lang as pl
         ON p.id_product = pl.id_product
         LEFT JOIN " . _DB_PREFIX_ . "product_supplier as s
         ON p.id_product = s.id_product
         LEFT JOIN " . _DB_PREFIX_ . "stock_available as sa
         ON p.id_product = sa.id_product AND sa.id_product_attribute = 0
         LEFT JOIN " . _DB_PREFIX_ . "specific_price as sp
         ON p.id_product = sp.id_product
         WHERE ps.id_shop = " . (int)$idShop . "
         AND pl.id_lang = " . (int)$idLang . "
            ".$where."
         GROUP BY sp.id_product
      ) as a";

      $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

      $specific_price_max_count = 0;
      if(isset($res[0]['specific_price_max_count']) && $res[0]['specific_price_max_count']){
        $specific_price_max_count = $res[0]['specific_price_max_count'];
      }

      return $specific_price_max_count;
    }


    if( $count ){
      return $res[0]['count'];
    }

    return $res;
  }

  public function getIdsOfProductsForExport( $idShop, $idLang, $shopGroupId, $separate , $more_settings, $limit = 0, $limitN)
  {
    if( !$limit ){
      $limit = " LIMIT 0,".(int)$limitN." ";
    } else {
      $limit = " LIMIT ".( (int)$limit*(int)$limitN ).",".(int)$limitN." ";
    }

    $products_check = Tools::unserialize(Configuration::get('GOMAKOIL_PRODUCTS_CHECKED','',$shopGroupId, $idShop));
    $selected_manufacturers = Tools::unserialize(Configuration::get('GOMAKOIL_MANUFACTURERS_CHECKED','',$shopGroupId, $idShop));
    $selected_suppliers = Tools::unserialize(Configuration::get('GOMAKOIL_SUPPLIERS_CHECKED','',$shopGroupId, $idShop));
    $selected_categories = Tools::unserialize(Configuration::get('GOMAKOIL_CATEGORIES_CHECKED','',$shopGroupId, $idShop));
    $where = "";
    $justProducts = true;

    $price = $more_settings['price_products'];
    $quantity = $more_settings['quantity_products'];

    if($price['price_value'] !== '' && $price['selection_type_price']){
      if($price['selection_type_price'] == 1){
        $where .= ' AND (ps.price) < '. (float)$price['price_value'];
      }
      if($price['selection_type_price'] == 2){
        $where .= ' AND (ps.price) > '. (float)$price['price_value'];
      }
      if($price['selection_type_price'] == 3){
        $where .= ' AND (ps.price) = '. (float)$price['price_value'];
      }
    }

    if($quantity['quantity_value'] !== '' && $quantity['selection_type_quantity']){
      if($quantity['selection_type_quantity'] == 1){
        $where .= ' AND (sa.quantity) < '. (int)$quantity['quantity_value'];
      }
      if($quantity['selection_type_quantity'] == 2){
        $where .= ' AND (sa.quantity) > '. (int)$quantity['quantity_value'];
      }
      if($quantity['selection_type_quantity'] == 3){
        $where .= ' AND (sa.quantity) = '. (int)$quantity['quantity_value'];
      }
    }

    if($more_settings['active_products']){
      $where .= " AND ps.active = 1 ";
    }

    if($more_settings['inactive_products']){
      $where .= " AND ps.active = 0 ";
    }

    if($more_settings['ean_products']){
      $where .= " AND p.ean13 != 0 ";
    }

    if($more_settings['specific_prices_products']){
      $where .= " AND sp.id_specific_price != 0 ";
    }

    if($more_settings['selection_type_visibility']){
      $visibility = '';
      foreach($more_settings['selection_type_visibility'] as $keu=>$value){
        if($value == 1){
          $visibility .= "'both'";
        }
        elseif($value == 2){
          $visibility .= "'catalog'";
        }
        elseif($value == 3){
          $visibility .= "'search'";
        }
        elseif($value == 4){
          $visibility .= "'none'";
        }
        $visibility .= ',';
      }

      $visibility = Tools::substr($visibility, 0, -1);
      $where .= " AND ps.visibility IN (".pSQL($visibility).") ";
    }

    if($more_settings['selection_type_condition']){
      $condition = '';
      foreach($more_settings['selection_type_condition'] as $keu=>$value){
        if($value == 1){
          $condition .= "'new'";
        }
        elseif($value == 2){
          $condition .= "'used'";
        }
        elseif($value == 3){
          $condition .= "'refurbished'";
        }
        $condition .= ',';
      }

      $condition = Tools::substr($condition, 0, -1);
      $where .= " AND ps.condition IN (".pSQL($condition).") ";
    }


    if( $selected_manufacturers ){
      $justProducts = false;
      $selected_manufacturers = implode(",", $selected_manufacturers);
      $where .= " AND p.id_manufacturer IN (".pSQL($selected_manufacturers).") ";
    }

    if( $selected_suppliers ){
      $justProducts = false;
      $selected_suppliers = implode(",", $selected_suppliers);
      $where .= " AND s.id_supplier IN (".pSQL($selected_suppliers).") ";
    }

    if( $selected_categories ){
      $justProducts = false;
      $selected_categories = implode(",", $selected_categories);
      $where .= " AND cp.id_category IN (".pSQL($selected_categories).") ";
    }

    if( $products_check ){
      $products_check = implode(",", $products_check);
      $justProducts = $justProducts ? 'AND' : 'OR';
      $where .= " $justProducts p.id_product IN (".pSQL($products_check).") ";
    }

    $orderby = $more_settings['orderby'];
    $orderway = $more_settings['orderway'];


      $order = ' ORDER BY p.id_product DESC, pa.id_product_attribute';
      if($orderway == 'asc'){
        $order_way = ' ASC';
      }
      else{
        $order_way = ' DESC';
      }
      if($orderby == 'id'){
        $order = ' ORDER BY p.id_product'.$order_way.', pa.id_product_attribute ASC';
      }
      if($orderby == 'name'){
        $order = ' ORDER BY pl.name '.$order_way.', pa.id_product_attribute ASC';
      }
      if($orderby == 'price'){
        $order = ' ORDER BY p.price '.$order_way.', pa.price '.$order_way.', pa.id_product_attribute ASC';
      }
      if($orderby == 'quantity'){
        $order = ' ORDER BY sa.quantity '.$order_way.', pa.id_product_attribute ASC';
      }
      if($orderby == 'date_add'){
        $order = ' ORDER BY p.date_add '.$order_way.', pa.id_product_attribute ASC';
      }
      if($orderby == 'date_update'){
        $order = ' ORDER BY p.date_upd '.$order_way.', pa.id_product_attribute ASC';
      }

      $sql = "
      SELECT GROUP_CONCAT(DISTINCT p.id_product) as products_ids
       FROM " . _DB_PREFIX_ . "product as p
       INNER JOIN " . _DB_PREFIX_ . "product_shop as ps
       ON p.id_product = ps.id_product
       LEFT JOIN " . _DB_PREFIX_ . "category_product as cp
       ON p.id_product = cp.id_product
       LEFT JOIN " . _DB_PREFIX_ . "product_lang as pl
       ON p.id_product = pl.id_product
       LEFT JOIN " . _DB_PREFIX_ . "product_supplier as s
       ON p.id_product = s.id_product
       LEFT JOIN " . _DB_PREFIX_ . "product_attribute as pa
       ON p.id_product = pa.id_product
       LEFT JOIN " . _DB_PREFIX_ . "stock_available as sa
       ON p.id_product = sa.id_product AND sa.id_product_attribute = 0
       LEFT JOIN " . _DB_PREFIX_ . "specific_price as sp
       ON p.id_product = sp.id_product
       WHERE ps.id_shop = " . (int)$idShop . "
       AND pl.id_lang = " . (int)$idLang . "
       " . $where . "
       " . $order . "
       " . $limit . "
    ";

    $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    return $result[0]['products_ids'];
  }

  public static function getProductSuppliers($id_product, $id_product_attribute)
  {
    $combination = '';
    if ($id_product_attribute) {
      $combination = ' AND id_product_attribute = ' . (int)$id_product_attribute;
    }

    $product_suppliers = Db::getInstance()->executeS("SELECT * FROM " . _DB_PREFIX_ . "product_supplier 
                                                          WHERE id_product = " . (int)$id_product
                                                          . $combination);
    $grouped_product_suppliers = array();

    if (!empty($product_suppliers)) {
      foreach ($product_suppliers as $product_supplier) {
        if (!isset($grouped_product_suppliers[$product_supplier['id_supplier']])) {
          $grouped_product_suppliers[$product_supplier['id_supplier']] = array();
        }

        array_push($grouped_product_suppliers[$product_supplier['id_supplier']], $product_supplier);
      }
    }

    return $grouped_product_suppliers;
  }

  public static function getFeatureNameById($id_feature, $id_lang)
  {
    return Db::getInstance()->getValue("
        SELECT `name` FROM `" . _DB_PREFIX_ . "feature_lang` 
        WHERE `id_feature` = '" . (int)$id_feature . "'
        AND `id_lang` = '".(int)$id_lang."'");
  }
}