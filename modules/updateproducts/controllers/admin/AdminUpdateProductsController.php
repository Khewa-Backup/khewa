<?php

if (!defined('_PS_VERSION_')){
    exit;
}

@ini_set('display_errors', 'off');
error_reporting(0);

class AdminUpdateProductsController extends ModuleAdminController
{
  public function __construct()
  {
    ini_set("max_execution_time","0");
    ini_set('memory_limit', '-1');

    $write_fd = fopen(_PS_MODULE_DIR_ . 'updateproducts/error/error.log', 'w');
    fwrite($write_fd, " ");
    fclose($write_fd);
    ini_set("log_errors", 1);
    ini_set("error_log", _PS_MODULE_DIR_ . 'updateproducts/error/error.log');
    parent::__construct();
  }

  public function ajaxProcessAddProduct()
  {
    $name_config = 'GOMAKOIL_PRODUCTS_CHECKED';
    $config = updateProductTools::jsonDecode(Configuration::get($name_config, null ,Tools::getValue('shopGroupId'), Tools::getValue('id_shop')));
    if( !$config ){
      $config = array();
    }
    if (!in_array( Tools::getValue('id_product'), $config)){
      array_push($config, Tools::getValue('id_product'));
    }
    else{
      $key = array_search(Tools::getValue('id_product'), $config);
      if ($key !== false)
      {
        unset ($config[$key]);
      }
    }
    $products =updateProductTools::jsonEncode($config);
    Configuration::updateValue($name_config, $products, false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
  }

  public function ajaxProcessAddManufacturer()
  {
      $name_config = 'GOMAKOIL_MANUFACTURERS_CHECKED';
      $config = updateProductTools::jsonDecode(Configuration::get($name_config, null ,Tools::getValue('shopGroupId'), Tools::getValue('id_shop')));
      if( !$config ){
        $config = array();
      }
      if (!in_array( Tools::getValue('id_manufacturer'), $config)){
        array_push($config, Tools::getValue('id_manufacturer'));
      }
      else{
        $key = array_search(Tools::getValue('id_manufacturer'), $config);
        if ($key !== false)
        {
          unset($config[$key]);
        }
      }
      $config = updateProductTools::jsonEncode($config);
      Configuration::updateValue($name_config, $config, false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
    }

    public function ajaxProcessAddSupplier()
    {
      $name_config = 'GOMAKOIL_SUPPLIERS_CHECKED';
      $config = updateProductTools::jsonDecode(Configuration::get($name_config, null ,Tools::getValue('shopGroupId'), Tools::getValue('id_shop')));
      if( !$config ){
        $config = array();
      }
      if (!in_array( Tools::getValue('id_supplier'), $config)){
        array_push($config, Tools::getValue('id_supplier'));
      }
      else{
        $key = array_search(Tools::getValue('id_supplier'), $config);
        if ($key !== false)
        {
          unset($config[$key]);
        }
      }
      $config = updateProductTools::jsonEncode($config);
      Configuration::updateValue($name_config, $config, false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
    }

    public function ajaxProcessShowCheckedProducts()
    {
      try {
        $updateProducts = Module::getInstanceByName('updateproducts');
        /**
         * @var updateProducts $updateProducts
         */
        $json['products'] = $updateProducts->showCheckedProducts(Tools::getValue('id_shop'), Tools::getValue('id_lang'), Tools::getValue('shopGroupId'));
        echo json_encode($json);
      }
      catch (Exception $e) {
        $json['error'] = $e->getMessage();
        echo json_encode($json);
      }
    }

  public function ajaxProcessShowCheckedManufacturers()
  {
    try {
      $updateProducts = Module::getInstanceByName('updateproducts');
      /**
       * @var updateProducts $updateProducts
       */
      $json['manufacturers'] = $updateProducts->showCheckedManufacturers(Tools::getValue('id_shop'), Tools::getValue('shopGroupId'));
      echo json_encode($json);
    }
    catch (Exception $e) {
      $json['error'] = $e->getMessage();
      echo json_encode($json);
    }
  }

  public function ajaxProcessShowCheckedSuppliers()
  {
    try {
      $updateProducts = Module::getInstanceByName('updateproducts');
      /**
       * @var updateProducts $updateProducts
       */
      $json['suppliers'] = $updateProducts->showCheckedSuppliers(Tools::getValue('id_shop'), Tools::getValue('shopGroupId'));
      echo json_encode($json);
    }
    catch (Exception $e) {
      $json['error'] = $e->getMessage();
      echo json_encode($json);
    }
  }

  public function ajaxProcessShowAllProducts()
  {
    try {
      $updateProducts = Module::getInstanceByName('updateproducts');
      /**
       * @var updateProducts $updateProducts
       */
      $json['products'] = $updateProducts->showAllProducts(Tools::getValue('id_shop'), Tools::getValue('id_lang'), Tools::getValue('shopGroupId'));
      echo json_encode($json);
    }
    catch (Exception $e) {
      $json['error'] = $e->getMessage();
      echo json_encode($json);
    }
  }

  public function ajaxProcessShowAllManufacturers()
  {
    try {
      $updateProducts = Module::getInstanceByName('updateproducts');
      /**
       * @var updateProducts $updateProducts
       */
      $json['manufacturers'] = $updateProducts->showAllManufacturers(Tools::getValue('id_shop'), Tools::getValue('shopGroupId'));
      echo json_encode($json);
    }
    catch (Exception $e) {
      $json['error'] = $e->getMessage();
      echo json_encode($json);
    }
  }

  public function ajaxProcessShowAllSuppliers()
  {
    try {
      $updateProducts = Module::getInstanceByName('updateproducts');
      /**
       * @var updateProducts $updateProducts
       */
      $json['suppliers'] = $updateProducts->showAllSuppliers(Tools::getValue('id_shop'), Tools::getValue('shopGroupId'));
      echo json_encode($json);
    }
    catch (Exception $e) {
      $json['error'] = $e->getMessage();
      echo json_encode($json);
    }
  }

  public function ajaxProcessSearchProduct()
  {
      $json = [];
    try {
      if( Tools::getValue('search_product') !== false){
        $updateProducts = Module::getInstanceByName('updateproducts');
        /**
         * @var updateProducts $updateProducts
         */
        $json['products'] = $updateProducts->searchProducts(Tools::getValue('search_product'), Tools::getValue('id_shop'), Tools::getValue('id_lang'),Tools::getValue('shopGroupId'));
      }
      echo json_encode($json);
    }
    catch (Exception $e) {
      $json['error'] = $e->getMessage();
      echo json_encode($json);
    }
  }

  public function ajaxProcessSearchManufacturer()
  {
      $json = [];
    try {
      if( Tools::getValue('search_manufacturer') !== false){
        $updateProducts = Module::getInstanceByName('updateproducts');
        /**
         * @var updateProducts $updateProducts
         */
        $json['manufacturers'] = $updateProducts->searchManufacturers(Tools::getValue('search_manufacturer'),Tools::getValue('id_shop'), Tools::getValue('shopGroupId'));
      }
      echo json_encode($json);
    }
    catch (Exception $e) {
      $json['error'] = $e->getMessage();
      echo json_encode($json);
    }
  }

  public function ajaxProcessSearchSupplier()
  {
      $json = [];
    try {
      if( Tools::getValue('search_supplier') !== false){
        $updateProducts = Module::getInstanceByName('updateproducts');
        /**
         * @var updateProducts $updateProducts
         */
        $json['suppliers'] = $updateProducts->searchSuppliers(Tools::getValue('search_supplier'),Tools::getValue('id_shop'), Tools::getValue('shopGroupId'));
      }
      echo json_encode($json);
    }
    catch (Exception $e) {
      $json['error'] = $e->getMessage();
      echo json_encode($json);
    }
  }

  public function ajaxProcessRemoveSetting()
  {
    try {
      $id = Tools::getValue('id');
      Configuration::deleteByName('GOMAKOIL_PRODUCTS_CHECKED_'.$id);
      Configuration::deleteByName('GOMAKOIL_MANUFACTURERS_CHECKED_'.$id);
      Configuration::deleteByName('GOMAKOIL_SUPPLIERS_CHECKED_'.$id);
      Configuration::deleteByName('GOMAKOIL_CATEGORIES_CHECKED_'.$id);
      Configuration::deleteByName('GOMAKOIL_FIELDS_CHECKED_'.$id);
      Configuration::deleteByName('GOMAKOIL_LANG_CHECKED_'.$id);
      Configuration::deleteByName('GOMAKOIL_TYPE_FILE_'.$id);
      Configuration::deleteByName('GOMAKOIL_NAME_SETTING_'.$id);
      Configuration::deleteByName('GOMAKOIL_STRIP_TAGS_'.$id);
      Configuration::deleteByName('GOMAKOIL_ORDER_BY_'.$id);
      Configuration::deleteByName('GOMAKOIL_ORDER_WAY_'.$id);
      Configuration::deleteByName('GOMAKOIL_DESIMAL_POINTS_'.$id);
      Configuration::deleteByName('GOMAKOIL_SEPARATE_SETTING_'.$id);
      Configuration::deleteByName('GOMAKOIL_ACTIVE_PRODUCTS_SETTING_2_'.$id);
      Configuration::deleteByName('GOMAKOIL_INACTIVE_PRODUCTS_SETTING_2_'.$id);
      Configuration::deleteByName('GOMAKOIL_EAN_PRODUCTS_SETTING_2_'.$id);
      Configuration::deleteByName('GOMAKOIL_SPECIFIC_PRICES_PRODUCTS_SETTING_2_'.$id);
      Configuration::deleteByName('GOMAKOIL_PRODUCTS_PRICE_2_'.$id);
      Configuration::deleteByName('GOMAKOIL_PRODUCTS_QUANTITY_2_'.$id);
      Configuration::deleteByName('GOMAKOIL_PRODUCTS_VISIBILITY_2_'.$id);
      Configuration::deleteByName('GOMAKOIL_PRODUCTS_CONDITION_2_'.$id);
      Configuration::deleteByName('GOMAKOIL_SHOW_NAME_FILE_2_'.$id);
      Configuration::deleteByName('GOMAKOIL_NAME_FILE_2_'.$id);

      $settings = updateProductTools::jsonDecode(Configuration::get('GOMAKOIL_ALL_SETTINGS', null ,Tools::getValue('shopGroupId'), Tools::getValue('id_shop')));
      if(in_array($id, $settings)){
        $key = array_search($id, $settings);
        unset ($settings[$key]);
        $settings =updateProductTools::jsonEncode($settings);
        Configuration::updateValue('GOMAKOIL_ALL_SETTINGS', $settings, false,  Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
      }
      $json['success'] = true;

      echo json_encode($json);
    }
    catch (Exception $e) {
      $json['error'] = $e->getMessage();
      echo json_encode($json);
    }
  }

  public function ajaxProcessRemoveSettingUpdate()
  {
    try {
      $id = Tools::getValue('id');
      Configuration::deleteByName('GOMAKOIL_FIELDS_CHECKED_UPDATE_'.$id);
      Configuration::deleteByName('GOMAKOIL_LANG_CHECKED_UPDATE_'.$id);
      Configuration::deleteByName('GOMAKOIL_TYPE_FILE_UPDATE_'.$id);
      Configuration::deleteByName('GOMAKOIL_NAME_SETTING_UPDATE_'.$id);
      Configuration::deleteByName('GOMAKOIL_SEPARATE_SETTING_UPDATE_'.$id);
      Configuration::deleteByName('GOMAKOIL_REMOVE_IMAGES_SETTING_UPDATE_'.$id);
      $settings = updateProductTools::jsonDecode(Configuration::get('GOMAKOIL_ALL_UPDATE_SETTINGS', null ,Tools::getValue('shopGroupId'), Tools::getValue('id_shop')));
      if(in_array($id, $settings)){
        $key = array_search($id, $settings);
        unset ($settings[$key]);
        $settings =updateProductTools::jsonEncode($settings);
        Configuration::updateValue('GOMAKOIL_ALL_UPDATE_SETTINGS', $settings, false,  Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
      }
      $json['success'] = true;
      echo json_encode($json);
    }
    catch (Exception $e) {
      $json['error'] = $e->getMessage();
      echo json_encode($json);
    }
  }

  public function ajaxProcessSaveSettings()
  {
    try {
      $error_list = array();

      if( !Tools::getValue('save_setting') ){
        $error_list[] = array('tab' => 'new_settings', 'field' => 'save_setting', 'msg' => Module::getInstanceByName('updateproducts')->l('Please enter settings name!', 'send'));
      }

      if( Tools::getValue('name_export_file') && !Tools::getValue('name_file') ){
        $error_list[] = array('tab' => 'export', 'field' => 'name_file', 'msg' => Module::getInstanceByName('updateproducts')->l('Please set name export file', 'send'));
      }

      if( Tools::getValue('price_value') !== '' && !Tools::getValue('selection_type_price') ){
        $error_list[] = array('tab' => 'filter_products', 'field' => 'selection_type_price', 'msg' => Module::getInstanceByName('updateproducts')->l('Please select sign inequality', 'send'));
      }

      if( Tools::getValue('price_value') !== '' && !Validate::isFloat( Tools::getValue('price_value')) ){
        $error_list[] = array('tab' => 'filter_products', 'field' => 'price_value',  'msg' => Module::getInstanceByName('updateproducts')->l('Please enter valid price value', 'send'));
      }

      if( Tools::getValue('quantity_value') !== '' && !Tools::getValue('selection_type_quantity') ){
        $error_list[] = array('tab' => 'filter_products', 'field' => 'selection_type_quantity',  'msg' => Module::getInstanceByName('updateproducts')->l('Please select sign inequality', 'send'));
      }

      if( Tools::getValue('quantity_value') !== '' && !Validate::isInt( Tools::getValue('quantity_value')) ){
        $error_list[] = array('tab' => 'filter_products', 'field' => 'quantity_value', 'msg' => Module::getInstanceByName('updateproducts')->l('Please enter valid quantity value', 'send'));
      }

      if(!$error_list){
        $id = Tools::getValue('last_id') ? Tools::getValue('last_id') : 0;
        $name_config = 'GOMAKOIL_NAME_SETTING_'.$id;
        $config = Configuration::get($name_config, null ,Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
        if($config && $config !== Tools::getValue('save_setting')){
          $all_setting = updateProductTools::jsonDecode( Configuration::get('GOMAKOIL_ALL_SETTINGS',null,Tools::getValue('shopGroupId'), Tools::getValue('id_shop')));
          if($all_setting){
            $all_setting = max($all_setting);
            $id = $all_setting + 1;
          }
        }

        $priceSettings = array(
          'price_value'       => Tools::getValue('price_value'),
          'selection_type_price'   => Tools::getValue('selection_type_price'),
        );

        $quantitySettings = array(
          'quantity_value'       => Tools::getValue('quantity_value'),
          'selection_type_quantity'   => Tools::getValue('selection_type_quantity'),
        );

        Configuration::updateValue('GOMAKOIL_ACTIVE_PRODUCTS_SETTING_2_'.$id, Tools::getValue('active_products'), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
        Configuration::updateValue('GOMAKOIL_INACTIVE_PRODUCTS_SETTING_2_'.$id, Tools::getValue('inactive_products'), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
        Configuration::updateValue('GOMAKOIL_EAN_PRODUCTS_SETTING_2_'.$id, Tools::getValue('ean_products'), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
        Configuration::updateValue('GOMAKOIL_SPECIFIC_PRICES_PRODUCTS_SETTING_2_'.$id, Tools::getValue('specific_prices_products'), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
        Configuration::updateValue('GOMAKOIL_PRODUCTS_VISIBILITY_2_'.$id, updateProductTools::jsonEncode(Tools::getValue('selection_type_visibility')), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
        Configuration::updateValue('GOMAKOIL_PRODUCTS_CONDITION_2_'.$id, updateProductTools::jsonEncode(Tools::getValue('selection_type_condition')), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
        Configuration::updateValue('GOMAKOIL_PRODUCTS_PRICE_2_'.$id, updateProductTools::jsonEncode($priceSettings), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
        Configuration::updateValue('GOMAKOIL_PRODUCTS_QUANTITY_2_'.$id, updateProductTools::jsonEncode($quantitySettings), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
        Configuration::updateValue('GOMAKOIL_SEPARATE_SETTING_'.$id, Tools::getValue('separate'), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
        Configuration::updateValue('GOMAKOIL_NAME_SETTING_'.$id, Tools::getValue('save_setting'), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
        Configuration::updateValue('GOMAKOIL_STRIP_TAGS_' . $id, Tools::getValue('strip_tags'), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
        Configuration::updateValue('GOMAKOIL_DESIMAL_POINTS_' . $id, Tools::getValue('round_value'), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
        Configuration::updateValue('GOMAKOIL_ORDER_BY_' . $id, Tools::getValue('orderby'), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
        Configuration::updateValue('GOMAKOIL_ORDER_WAY_' . $id, Tools::getValue('orderway'), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
        Configuration::updateValue('GOMAKOIL_SHOW_NAME_FILE_2_'.$id, Tools::getValue('name_export_file'), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
        Configuration::updateValue('GOMAKOIL_NAME_FILE_2_'.$id, Tools::getValue('name_file'), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));

        $config = Configuration::get('GOMAKOIL_PRODUCTS_CHECKED', null ,Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
        $name_config = 'GOMAKOIL_PRODUCTS_CHECKED_'.$id;
        Configuration::updateValue($name_config, $config, false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
        $config = Configuration::get('GOMAKOIL_MANUFACTURERS_CHECKED', null ,Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
        $name_config = 'GOMAKOIL_MANUFACTURERS_CHECKED_'.$id;
        Configuration::updateValue($name_config, $config, false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
        $config = Configuration::get('GOMAKOIL_SUPPLIERS_CHECKED', null ,Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
        $name_config = 'GOMAKOIL_SUPPLIERS_CHECKED_'.$id;
        Configuration::updateValue($name_config, $config, false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
        $name_config = 'GOMAKOIL_CATEGORIES_CHECKED_'.$id;
        Configuration::updateValue($name_config, updateProductTools::jsonEncode(Tools::getValue('categories')), false,  Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
        $name_config = 'GOMAKOIL_FIELDS_CHECKED_'.$id;
        Configuration::updateValue($name_config, updateProductTools::jsonEncode(Tools::getValue('field')), false,  Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
        $name_config = 'GOMAKOIL_LANG_CHECKED_'.$id;
        Configuration::updateValue($name_config, Tools::getValue('id_lang'), false,  Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
        $name_config = 'GOMAKOIL_TYPE_FILE_'.$id;
        Configuration::updateValue($name_config, Tools::getValue('format_file'), false,  Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
        $settings = array();
        $settings = updateProductTools::jsonDecode(Configuration::get('GOMAKOIL_ALL_SETTINGS', null ,Tools::getValue('shopGroupId'), Tools::getValue('id_shop')));

        if($settings){
          if(!in_array($id, $settings)){
            $settings[] = $id;
            $settings =updateProductTools::jsonEncode($settings);
            Configuration::updateValue('GOMAKOIL_ALL_SETTINGS', $settings, false,  Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
          }
        }
        else{
          $settings[] = $id;
          $settings =updateProductTools::jsonEncode($settings);
          Configuration::updateValue('GOMAKOIL_ALL_SETTINGS', $settings, false,  Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));

        }
        $json['id'] = $id;
      }
      else{
        $json['error_list'] = $error_list;
      }

      echo json_encode($json);
    }
    catch (Exception $e) {
      $json['error'] = $e->getMessage();
      echo json_encode($json);
    }
  }

  public function ajaxProcessSaveSettingsUpdate()
  {
    try {
      $error_list = array();
      $id = 0;
      if( !Tools::getValue('save_setting_update') || Tools::getValue('save_setting_update') == ' ' ){
        $error_list[] = array('tab' => 'update_settings', 'field' => 'save_setting_update', 'msg' => Module::getInstanceByName('updateproducts')->l('Please enter settings name!', 'send'));
      }

      if(!$error_list){
        $name_config = 'GOMAKOIL_NAME_SETTING_UPDATE_'.Tools::getValue('last_id_update');
        $config = Configuration::get($name_config, null ,Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
        if($config && $config !== Tools::getValue('save_setting_update')){
          $all_setting = updateProductTools::jsonDecode( Configuration::get('GOMAKOIL_ALL_UPDATE_SETTINGS',null,Tools::getValue('shopGroupId'), Tools::getValue('id_shop')));
          if($all_setting){
            $all_setting = max($all_setting);
            $id = $all_setting + 1;
          }
        }
        else{
          $id = Tools::getValue('last_id_update');
        }

        Configuration::updateValue('GOMAKOIL_REMOVE_IMAGES_SETTING_UPDATE_'.$id, Tools::getValue('remove_images'), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
        Configuration::updateValue('GOMAKOIL_SEPARATE_SETTING_UPDATE_'.$id, Tools::getValue('separate_update'), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
        Configuration::updateValue('GOMAKOIL_DISABLE_HOOKS_SETTING_UPDATE_'.$id, Tools::getValue('disable_hooks'), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
        Configuration::updateValue('GOMAKOIL_NAME_SETTING_UPDATE_'.$id, trim(Tools::getValue('save_setting_update')), false, Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
        $name_config = 'GOMAKOIL_FIELDS_CHECKED_UPDATE_'.$id;
        Configuration::updateValue($name_config, updateProductTools::jsonEncode(Tools::getValue('field_update')), false,  Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
        $name_config = 'GOMAKOIL_LANG_CHECKED_UPDATE_'.$id;
        Configuration::updateValue($name_config, Tools::getValue('id_lang_update'), false,  Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
        $name_config = 'GOMAKOIL_TYPE_FILE_UPDATE_'.$id;
        Configuration::updateValue($name_config, Tools::getValue('format_file_update'), false,  Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));

        $settings = array();
        $settings = updateProductTools::jsonDecode(Configuration::get('GOMAKOIL_ALL_UPDATE_SETTINGS', null ,Tools::getValue('shopGroupId'), Tools::getValue('id_shop')));
        if($settings){
          if(!in_array($id, $settings)){
            $settings[] = $id;
            $settings =updateProductTools::jsonEncode($settings);
            Configuration::updateValue('GOMAKOIL_ALL_UPDATE_SETTINGS', $settings, false,  Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
          }
        }
        else{
          $settings[] = $id;
          $settings =updateProductTools::jsonEncode($settings);
          Configuration::updateValue('GOMAKOIL_ALL_UPDATE_SETTINGS', $settings, false,  Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));

        }
        $json['id'] = $id;
      }
      else{
        $json['error_list'] = $error_list;
      }
      echo json_encode($json);
    }
    catch (Exception $e) {
      $json['error'] = $e->getMessage();
      echo json_encode($json);
    }
  }

  public function ajaxProcessUpdate()
  {
    try {
      if( !Tools::getValue('field_update') ){
        throw new Exception(Module::getInstanceByName('updateproducts')->l('Please select fields for update!'));
      }

      include_once(_PS_MODULE_DIR_.'updateproducts/classes/update.php');

      $export = new updateProductCatalog( Tools::getValue('id_shop'), Tools::getValue('id_lang_update'), Tools::getValue('format_file'), Tools::getValue('field_update'), Tools::getValue('separate_update'), Tools::getValue('remove_images'), Tools::getValue('disable_hooks') );
      $res = $export->update( Tools::getValue('page_limit') );

      if( is_int($res) ){
        $json['page_limit'] = $res;
      }
      else{
        $json['success'] = $res;
      }
      echo json_encode($json);
    }
    catch (Exception $e) {
      $json['error'] = $e->getMessage();
      echo json_encode($json);
    }
  }

  public function ajaxProcessExport()
  {
    try {
      $error_list = array();
      $name_file = false;
      if( Tools::getValue('name_export_file') && !Tools::getValue('name_file') ){
        $error_list[] = array('tab' => 'export', 'field' => 'name_file', 'msg' => Module::getInstanceByName('updateproducts')->l('Please set name export file', 'send'));
      }

      if(Tools::getValue('name_export_file') && Tools::getValue('name_file')){
        $name_file = Tools::getValue('name_file');
      }


      if( Tools::getValue('price_value') !== '' && !Tools::getValue('selection_type_price') ){
        $error_list[] = array('tab' => 'filter_products', 'field' => 'selection_type_price', 'msg' => Module::getInstanceByName('updateproducts')->l('Please select sign inequality', 'send'));
      }

      if( Tools::getValue('price_value') !== '' && !Validate::isFloat( Tools::getValue('price_value')) ){
        $error_list[] = array('tab' => 'filter_products', 'field' => 'price_value',  'msg' => Module::getInstanceByName('updateproducts')->l('Please enter valid price value', 'send'));
      }

      if( Tools::getValue('quantity_value') !== '' && !Tools::getValue('selection_type_quantity') ){
        $error_list[] = array('tab' => 'filter_products', 'field' => 'selection_type_quantity',  'msg' => Module::getInstanceByName('updateproducts')->l('Please select sign inequality', 'send'));
      }

      if( Tools::getValue('quantity_value') !== '' && !Validate::isInt( Tools::getValue('quantity_value')) ){
        $error_list[] = array('tab' => 'filter_products', 'field' => 'quantity_value', 'msg' => Module::getInstanceByName('updateproducts')->l('Please enter valid quantity value', 'send'));
      }


      if(!$error_list){
        Configuration::updateValue('GOMAKOIL_CATEGORIES_CHECKED', null, false,  Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
        if( Tools::getValue('categories') ){
          Configuration::updateValue('GOMAKOIL_CATEGORIES_CHECKED', updateProductTools::jsonEncode(Tools::getValue('categories')), false,  Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
        }
        if( Tools::getValue('field') ){
          Configuration::updateValue('GOMAKOIL_FIELDS_CHECKED', updateProductTools::jsonEncode(Tools::getValue('field')), false,  Tools::getValue('shopGroupId'), Tools::getValue('id_shop'));
        }

        $more_settings = array(
          'active_products' => Tools::getValue('active_products'),
          'inactive_products' => Tools::getValue('inactive_products'),
          'ean_products' => Tools::getValue('ean_products'),
          'strip_tags' => Tools::getValue('strip_tags'),
          'round_value' => Tools::getValue('round_value'),
          'orderby' => Tools::getValue('orderby'),
          'orderway' => Tools::getValue('orderway'),
          'specific_prices_products' => Tools::getValue('specific_prices_products'),
          'price_products' => array('price_value' => Tools::getValue('price_value'), 'selection_type_price' => Tools::getValue('selection_type_price')),
          'quantity_products' => array('quantity_value' => Tools::getValue('quantity_value'), 'selection_type_quantity' => Tools::getValue('selection_type_quantity')),
          'selection_type_visibility' => Tools::getValue('selection_type_visibility'),
          'selection_type_condition' => Tools::getValue('selection_type_condition'),
        );

        include_once(_PS_MODULE_DIR_.'updateproducts/classes/export.php');

        $export = new mpmExport( Tools::getValue('id_shop'), Tools::getValue('id_lang'), Tools::getValue('format_file'), Tools::getValue('shopGroupId'), Tools::getValue('separate'),$more_settings, $name_file );
        $fileName = $export->export( Tools::getValue('page_limit') );
        if( is_int($fileName) ){
          $json['page_limit'] = $fileName;
        }
        else{
          $json['file'] = $fileName;
        }
      }
      else{
        $json['error_list'] = $error_list;
      }
      echo json_encode($json);
    }
    catch (Exception $e) {
      $json['error'] = $e->getMessage();
      echo json_encode($json);
    }
  }

  public function ajaxProcessReturnExportCount()
  {
    try {
      $productsCount = Configuration::get('EXPORT_PRODUCTS_COUNT',null,updateProductTools::getShopGroupId(), Tools::getValue('id_shop'));
      $currentExportedProducts = Configuration::get('EXPORT_PRODUCTS_CURRENT_COUNT',null,updateProductTools::getShopGroupId(), Tools::getValue('id_shop'));
      $json['export_notification'] = Module::getInstanceByName('updateproducts')->l('Successfully exported ' . $currentExportedProducts . ' from ' . $productsCount . ' items', 'send');
      echo json_encode($json);
    }
    catch (Exception $e) {
      $json['error'] = $e->getMessage();
      echo json_encode($json);
    }
  }

  public function ajaxProcessReturnUpdateCount()
  {
    try {
      $currentUpdatedProducts = Configuration::get('UPDATED_PRODUCTS_CURRENT_COUNT',null,updateProductTools::getShopGroupId(), Tools::getValue('id_shop'));
      $json['update_notification'] = Module::getInstanceByName('updateproducts')->l('Successfully updated ' . $currentUpdatedProducts . ' items', 'send');
      echo json_encode($json);
    }
    catch (Exception $e) {
      $json['error'] = $e->getMessage();
      echo json_encode($json);
    }
  }

}