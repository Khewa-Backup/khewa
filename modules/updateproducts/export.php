<?php

class exportProducts
{
  private $_context;
  private $_idShop;
  private $_shopGroupId;
  private $_idLang;
  private $_format;
  private $_model;
  private $_PHPExcel;
  private $_alphabet;
  private $_head;
  private $_separate;
  private $_more_settings;
  private $_name_file;
  private $_imageType;
  private $_productsCount;
  private $_limit;
  private $_limitN = 1000;

  public function __construct( $idShop, $idLang, $format, $shopGroupId, $separate, $more_settings, $name_file ){
    include_once(dirname(__FILE__).'/../../config/config.inc.php');
    include_once(dirname(__FILE__).'/../../init.php');

    if (!class_exists('PHPExcel')) {
      include_once(_PS_MODULE_DIR_ . 'updateproducts/libraries/PHPExcel_1.7.9/Classes/PHPExcel.php');
      include_once(_PS_MODULE_DIR_ . 'updateproducts/libraries/PHPExcel_1.7.9/Classes/PHPExcel/IOFactory.php');
    }

    include_once('datamodel.php');
    $this->_context = Context::getContext();
    $this->_idShop = $idShop;
    $this->_shopGroupId = $shopGroupId;
    $this->_idLang = (int)$idLang;
    $this->_format = $format;
    $this->_separate = $separate;
    $this->_name_file = $name_file;
    $this->_more_settings = $more_settings;
    $this->_model = new productsUpdateModel();
    $this->_PHPExcel = new PHPExcel();

    $imageTypes = ImageType::getImagesTypes('products');
    foreach ( $imageTypes  as $type ){
      if( $type['height'] > 150 ){
        $this->_imageType = $type['name'];
        break;
      }
    }

    for( $i = 0; $i < 2000; $i++ ){
      $this->_alphabet[$i] = $this->columnLetter($i+1);
    }
  }

  public function columnLetter($c){

    $c = intval($c);
    if ($c <= 0) return '';

    $letter = '';

    while($c != 0){
      $p = ($c - 1) % 26;
      $c = intval(($c - $p) / 26);
      $letter = chr(65 + $p) . $letter;
    }

    return $letter;

  }

  public function export( $limit = 0 )
  {
    $this->_limit = $limit;

    $selected_fields = Tools::unserialize(Configuration::get('GOMAKOIL_FIELDS_CHECKED','',$this->_shopGroupId,$this->_idShop));
    $selected_fields = $this->splitSpecificPriceFields($selected_fields);
    Configuration::updateValue('GOMAKOIL_FIELDS_CHECKED', serialize($selected_fields), false, $this->_shopGroupId, $this->_idShop);

    if(!$this->_separate && isset($selected_fields['images']) && $selected_fields['images']){
      throw new Exception( Module::getInstanceByName('updateproducts')->l('If you want to export image urls must activate option "Each product combinations in a separate line"!') );
    }


    if( !$limit ){
      Configuration::updateValue('EXPORT_PRODUCTS_TIME', Date('Y.m.d_G-i-s'), false, $this->_shopGroupId, $this->_idShop);
      $this->_productsCount = $this->_model->getExportIds( $this->_idShop, $this->_idLang, $this->_shopGroupId, $this->_separate, $this->_more_settings, $limit, $this->_limitN, true );
      Configuration::updateValue('EXPORT_PRODUCTS_COUNT', $this->_productsCount, false, $this->_shopGroupId, $this->_idShop);
      Configuration::updateValue('EXPORT_PRODUCTS_CURRENT_COUNT', 0, false, $this->_shopGroupId, $this->_idShop);
      if( !$this->_productsCount ){
        throw new Exception(Module::getInstanceByName('updateproducts')->l('No of matching products','export'));
      }
    }
    else{
      $this->_productsCount = Configuration::get('EXPORT_PRODUCTS_COUNT', '' ,$this->_shopGroupId, $this->_idShop);
      if( $this->_format == 'xlsx' || $this->_format == 'xls' ){
        $this->_PHPExcel = PHPExcel_IOFactory::load('files/export_products_' . Configuration::get('EXPORT_PRODUCTS_TIME', '' ,$this->_shopGroupId, $this->_idShop) . ( (int)$limit - 1 ) . '.' . $this->_format);
      }

      if( $this->_format == 'csv' ){
        $reader = PHPExcel_IOFactory::createReader("CSV");
        $this->_PHPExcel = $reader->load('files/export_products_' . Configuration::get('EXPORT_PRODUCTS_TIME', '' ,$this->_shopGroupId, $this->_idShop) . ( (int)$limit - 1 ) . '.' . $this->_format);
      }
    }

    $productIds = $this->_model->getExportIds( $this->_idShop, $this->_idLang, $this->_shopGroupId, $this->_separate, $this->_more_settings, $this->_limit, $this->_limitN );



    return $this->_getProductsData($productIds);
  }

  private function _getProductsData( $productIds )
  {
    $line = 2;
    if( $this->_limit ){

      foreach ( $this->_PHPExcel->getWorksheetIterator() as $worksheet ){
        $highestRow         = $worksheet->getHighestRow();
        break;
      }
      $line = $highestRow+1;
    }
    $this->_createHead();

    foreach( $productIds as $prodId ){
      $productId = $prodId['id_product'];
      if($this->_separate){
        $productAttributeId = $prodId['id_product_attribute'];
        $this->_setProductInFile($this->_getProductById($productId, $productAttributeId), $line);
        $line++;
      }
      else{
        $this->_setProductInFile($this->_getProductById($productId, false), $line);
        $line++;
      }

      $currentExported = Configuration::get('EXPORT_PRODUCTS_CURRENT_COUNT','',$this->_shopGroupId, Context::getContext()->shop->id);
      Configuration::updateValue('EXPORT_PRODUCTS_CURRENT_COUNT', ((int)$currentExported+1), false, $this->_shopGroupId, Context::getContext()->shop->id);

    }

    if( (int)$this->_productsCount <= ((int)$this->_limit*(int)$this->_limitN)+(int)$this->_limitN ){
      $this->_setStyle($line);
    }
    $fileName = $this->_saveFile();

    return $fileName;
  }

  private function _setStyle( $line )
  {
    $i = $line;
    $j = count($this->_head);

    $style_wrap = array(
      'borders'=>array(
        'outline' => array(
          'style'=>PHPExcel_Style_Border::BORDER_THICK
        ),
        'allborders'=>array(
          'style'=>PHPExcel_Style_Border::BORDER_THIN,
          'color' => array(
            'rgb'=>'696969'
          )
        )
      )
    );
    $this->_PHPExcel->getActiveSheet()->getStyle('A1:'.$this->_alphabet[$j-1].($i-1))->applyFromArray($style_wrap);

    $style_hprice = array(
      //выравнивание
      'alignment' => array(
        'horizontal' => PHPExcel_STYLE_ALIGNMENT::HORIZONTAL_CENTER,
      ),
      //заполнение цветом
      'fill' => array(
        'type' => PHPExcel_STYLE_FILL::FILL_SOLID,
        'color'=>array(
          'rgb' => 'CFCFCF'
        )
      ),
      //Шрифт
      'font'=>array(
        'bold' => true,
        'italic' => true,
        'name' => 'Times New Roman',
        'size' => 13
      ),
    );
    $this->_PHPExcel->getActiveSheet()->getStyle('A1:'.$this->_alphabet[$j-1].'1')->applyFromArray($style_hprice);

    $style_price = array(
      'alignment' => array(
        'horizontal' => PHPExcel_STYLE_ALIGNMENT::HORIZONTAL_LEFT,
      )
    );
    $this->_PHPExcel->getActiveSheet()->getStyle('A2:'.$this->_alphabet[$j-1].($i-1))->applyFromArray($style_price);

    $style_background1 = array(
      //заполнение цветом
      'fill' => array(
        'type' => PHPExcel_STYLE_FILL::FILL_SOLID,
        'color'=>array(
          'rgb' => 'F2F2F5'
        )
      ),
    );
    $this->_PHPExcel->getActiveSheet()->getStyle('A2:'.$this->_alphabet[$j-1].($i-1))->applyFromArray($style_background1);
  }

  private function _setProductInFile( $product, $line )
  {
    $i = 0;
    foreach($this->_head as $field => $name){

      if( $field == 'image_cover'){
        if( ($mime = getimagesize($product[$field]) ) ){
          $gdImage = $this->_getImageObject($mime, $product[$field]);
          $objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
          $objDrawing->setImageResource($gdImage);
          $objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
          $objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
          $objDrawing->setHeight(150);
          $objDrawing->setOffsetX(6);
          $objDrawing->setOffsetY(6);
          $objDrawing->setCoordinates($this->_alphabet[$i].$line);
          $objDrawing->setWorksheet( $this->_PHPExcel->getActiveSheet() );
          $this->_PHPExcel->getActiveSheet()->getRowDimension($line)->setRowHeight(121);
          $this->_PHPExcel->getActiveSheet()->getColumnDimension($this->_alphabet[$i])->setWidth(23);
        }
      }
      else{
        $this->_PHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($this->_alphabet[$i].$line, isset($product[$field]) ? $product[$field] : '',PHPExcel_Cell_DataType::TYPE_STRING);
      }
      $i++;
    }

  }

  private function _getImageObject( $mime, $image )
  {
    switch(Tools::strtolower($mime['mime']))
    {
      case 'image/png':
        $img_r = imagecreatefrompng($image);
        break;
      case 'image/jpeg':
        $img_r = imagecreatefromjpeg($image);
        break;
      case 'image/gif':
        $img_r = imagecreatefromgif($image);
        break;
      default: $img_r = imagecreatefrompng($image);;
    }

    return $img_r;
  }

  private function _saveFile()
  {
    $date = Configuration::get('EXPORT_PRODUCTS_TIME', '' ,$this->_shopGroupId, $this->_idShop);

    $name_file = 'export_products_' . $date.'.'.$this->_format;

    if($this->_name_file){
      $name_file = $this->_name_file.'.'.$this->_format;
    }

    if ($this->_format == 'xlsx'){
      $objWriter = PHPExcel_IOFactory::createWriter($this->_PHPExcel, 'Excel2007');
      if( (int)$this->_productsCount <= ((int)$this->_limit*(int)$this->_limitN)+(int)$this->_limitN ){
        $objWriter->save('files/'.$name_file);
        for( $l = 0;$l<(int)$this->_limit;$l++ ){
          if( file_exists('files/export_products_' . $date . ((int)$l) . '.' . $this->_format) ){
            unlink('files/export_products_' . $date . ((int)$l) . '.' . $this->_format);
          }
        }
      }
      else{
        $objWriter->save('files/export_products_' . $date . $this->_limit . '.' . $this->_format);
      }
    }
    elseif ($this->_format == 'csv'){
      $objWriter = PHPExcel_IOFactory::createWriter($this->_PHPExcel, 'CSV');
      $objWriter->setUseBOM(true);


      if( (int)$this->_productsCount <= ((int)$this->_limit*(int)$this->_limitN)+(int)$this->_limitN ){
        $objWriter->save('files/'.$name_file);
        for( $l = 0;$l<(int)$this->_limit;$l++ ){
          if( file_exists('files/export_products_' . $date . ((int)$l) . '.' . $this->_format) ){
            unlink('files/export_products_' . $date . ((int)$l) . '.' . $this->_format);
          }
        }
      }
      else{
        $objWriter->save('files/export_products_' . $date . $this->_limit . '.' . $this->_format);
      }

    }

    if( (int)$this->_productsCount > ((int)$this->_limit*(int)$this->_limitN)+(int)$this->_limitN ){
      return (int)$this->_limit+1;
    }

    return _PS_BASE_URL_SSL_.__PS_BASE_URI__.'modules/updateproducts/files/'.$name_file;
  }

  private function _createHead()
  {
    $this->_head = $this->_getHeadFields();
    $this->_PHPExcel->getProperties()->setCreator("PHP")
      ->setLastModifiedBy("Admin")
      ->setTitle("Office 2007 XLSX")
      ->setSubject("Office 2007 XLSX")
      ->setDescription(" Office 2007 XLSX, PHPExcel.")
      ->setKeywords("office 2007 openxml php")
      ->setCategory("File");
    $this->_PHPExcel->getActiveSheet()->setTitle('Export');

    $i = 0;
    foreach($this->_head as $field => $name) {
      $this->_PHPExcel->setActiveSheetIndex(0)
        ->setCellValue($this->_alphabet[$i].'1', $name);
      if( $field == "product_link" ){
        $this->_PHPExcel->getActiveSheet()->getColumnDimension($this->_alphabet[$i])->setWidth(80);
      }
      elseif( $field == "images" ){
        $this->_PHPExcel->getActiveSheet()->getColumnDimension($this->_alphabet[$i])->setWidth(80);
      }
      elseif( $field == "name" ){
        $this->_PHPExcel->getActiveSheet()->getColumnDimension($this->_alphabet[$i])->setWidth(80);
      }
      elseif( $field == "description" ){
        $this->_PHPExcel->getActiveSheet()->getColumnDimension($this->_alphabet[$i])->setWidth(80);
      }
      elseif( $field == "description_short" ){
        $this->_PHPExcel->getActiveSheet()->getColumnDimension($this->_alphabet[$i])->setWidth(80);
      }
      else{
        $this->_PHPExcel->getActiveSheet()->getColumnDimension($this->_alphabet[$i])->setWidth(30);
      }
      $i++;
    }

    $this->_PHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(25);
  }

  private function _getHeadFields()
  {
    $selected_fields = Tools::unserialize(Configuration::get('GOMAKOIL_FIELDS_CHECKED','',$this->_shopGroupId,$this->_idShop));
    $selected_fields = $this->addFeaturesFieldsToSelectedFields($selected_fields);

    Configuration::updateValue('GOMAKOIL_FIELDS_CHECKED', serialize($selected_fields), false, $this->_shopGroupId, $this->_idShop);
    return $selected_fields;
  }

  private function _getProductById( $productId, $productAttributeId )
  {

    $selected_fields = Tools::unserialize(Configuration::get('GOMAKOIL_FIELDS_CHECKED','',$this->_shopGroupId,$this->_idShop));
    $product = new Product($productId, false, $this->_idLang, $this->_idShop);

    if($this->_separate){
      $productInfo = $this->getProductInfoSeparate($selected_fields, $productId, $productAttributeId, $product);
    } else{
      $productInfo = $this->getProductInfo($selected_fields, $productId, false, $product);
    }

    return $productInfo;
  }

  public function getProductInfoSeparate($selected_fields, $productId, $id_product_attribute, $product)
  {
    $productInfo = array();
    $more_settings = $this->_more_settings;
    $round_value  = $more_settings['round_value'];
    $combination = new Combination($id_product_attribute);

    $address = null;
    if (is_object(Context::getContext()->cart) && Context::getContext()->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')} != null) {
      $address = Context::getContext()->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')};
    }

    $product->tax_rate = $product->getTaxesRate(new Address($address));
    $product->base_price = $product->price;
    $product->unit_price = ($product->unit_price_ratio != 0  ? $product->price / $product->unit_price_ratio : 0);
    $product->manufacturer_name = Manufacturer::getNameById((int)$product->id_manufacturer);

    foreach ($selected_fields as $field => $value) {
      if ($field == "id_product") {
        $productInfo[$field]= $productId;
      } elseif ($field == "id_product_attribute") {
        $productInfo[$field] = $id_product_attribute;
      }
      elseif ($field == "categories_ids") {
        $productInfo[$field] = "";
        foreach ($product->getWsCategories() as $category) {
          $productInfo[$field] .= $category['id'] . ";";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ";");
      } elseif ($field == "categories_names") {
        $productInfo[$field] = "";
        foreach ($product->getWsCategories() as $category) {
          $cat_obj = new Category($category['id'], $this->_idLang, $this->_idShop);
          $productInfo[$field] .= $cat_obj->name . ";";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ";");
      } elseif ($field == 'suppliers_ids') {
        $product_supplier = $this->_model->getProductSuppliersID($productId);
        if ($product_supplier) {
          $productInfo[$field] = $product_supplier[0]['suppliers_ids'];
        }
      } elseif($field == 'supplier_reference'){
        $sReference =  ProductSupplier::getProductSupplierReference($productId, $id_product_attribute, $product->id_supplier);
        if( !$sReference ){
          $sReference = '';
        }
        $productInfo[$field] = $sReference;
      }
      elseif($field == 'supplier_price'){
        $sPrice =  ProductSupplier::getProductSupplierPrice($productId, $id_product_attribute, $product->id_supplier);
        if(!$sPrice){
          $sPrice = '';
        } else {
          $sPrice = Tools::ps_round($sPrice,$round_value);
          $sPrice = number_format($sPrice, $round_value, '.','');
        }

        $productInfo[$field] = $sPrice;
      }
      elseif($field == 'supplier_price_currency'){
        $sPriceCurrency =  ProductSupplier::getProductSupplierPrice($productId, $id_product_attribute, $product->id_supplier, true);
        if( isset($sPriceCurrency['id_currency']) ){
          $tmpCurrency = new Currency($sPriceCurrency['id_currency']);
          $sPriceCurrency['id_currency'] = $tmpCurrency->iso_code;
          $productInfo[$field] = $sPriceCurrency['id_currency'];
        }
        else{
          $productInfo[$field] = '';
        }
      }
      elseif ($field == 'suppliers_name') {
        $product_supplier = $this->_model->getProductSuppliersID($productId);
        if ($product_supplier) {
          $productInfo[$field] = $product_supplier[0]['suppliers_name'];
        }
      }
      elseif ($field == 'suppliers_reference') {
        $productInfo[$field] = $this->getSuppliersPropertyInOneLine($productId, !empty($combinations), 'product_supplier_reference', $id_product_attribute);
      }
      elseif ($field == 'suppliers_price') {
        $productInfo[$field] = $this->getSuppliersPropertyInOneLine($productId, !empty($combinations), 'product_supplier_price_te', $id_product_attribute);
      }
      elseif ($field == 'suppliers_price_currency') {
        $productInfo[$field] = $this->getSuppliersPropertyInOneLine($productId, !empty($combinations), 'id_currency', $id_product_attribute);
      }
      elseif ($field == "base_price" || $field == "ecotax"  || $field == "additional_shipping_cost"  || $field == "unit_price") {
        $tmpPrice = Tools::ps_round($product->$field,$round_value);
        $tmpPrice = number_format($tmpPrice, $round_value, '.','');
        $productInfo[$field] = $tmpPrice;
      }
      elseif ($field == "base_price_with_tax") {
        $taxPrice = $product->base_price;
        if ($product->tax_rate) {
          $taxPrice = $taxPrice + ($taxPrice * ($product->tax_rate / 100));
        }
        $tmpPrice = Tools::ps_round($taxPrice,$round_value);
        $tmpPrice = number_format($tmpPrice, $round_value, '.','');
        $productInfo[$field] = $tmpPrice;
      } elseif ($field == "wholesale_price") {
        $tmpPrice = Tools::ps_round($product->$field,$round_value);
        $tmpPrice = number_format($tmpPrice, $round_value, '.','');
        $productInfo[$field] = $tmpPrice;
      } elseif ($field == "price") {
        $taxPrice = $product->getPrice(false, $combination->id, $round_value);
        $tmpPrice = Tools::ps_round($taxPrice,$round_value);
        $tmpPrice = number_format($tmpPrice, $round_value, '.','');
        $productInfo[$field] = $tmpPrice;
      } elseif ($field == "final_price_with_tax") {
        $price = $product->getPrice(true, $combination->id, $round_value);
        $tmpPrice = Tools::ps_round($price,$round_value);
        $tmpPrice = number_format($tmpPrice, $round_value, '.','');
        $productInfo[$field] = $tmpPrice;
      } elseif ($field == "combinations_name") {
        $productInfo[$field] = str_replace($product->name . " : ", '', Product::getProductName($product->id, $combination->id));
      }
      elseif ($field == "combinations_price") {
        $tmpPrice = Tools::ps_round($combination->price, $round_value);
        $tmpPrice = number_format($tmpPrice, $round_value, '.','');
        $productInfo[$field] = $tmpPrice;
      }
      elseif ($field == "combinations_price_with_tax") {
        $taxPrice = $combination->price;
        $tmpPrice = ($taxPrice + ($taxPrice * ($product->tax_rate / 100)));
        $tmpPrice = Tools::ps_round($tmpPrice, $round_value);
        $tmpPrice = number_format($tmpPrice, $round_value, '.','');
        $productInfo[$field] = $tmpPrice;
      }
      elseif (preg_match('/id_specific_price_\d+/', $field)) {
        $productInfo[$field] = $this->getSpecificPriceAttribute('id_specific_price', $productId, $field, $id_product_attribute);
      } elseif (preg_match('/^(?:c_)?specific_price_\d+/', $field)) {
        $tmpPrice = $this->getSpecificPriceAttribute('price', $productId, $field, $id_product_attribute);

        if ($tmpPrice > 0) {
          $tmpPrice = Tools::ps_round($tmpPrice, $round_value);
          $tmpPrice = number_format($tmpPrice, $round_value, '.','');
          $productInfo[$field] = $tmpPrice;
        }
      } elseif (preg_match('/^(?:c_)?specific_price_from_quantity_\d+$/', $field)) {
        $productInfo[$field] = $this->getSpecificPriceAttribute('from_quantity', $productId, $field, $id_product_attribute);
      } elseif (preg_match('/^(?:c_)?specific_price_reduction_\d+$/', $field)) {
        $tmpPrice = $this->getSpecificPriceAttribute('reduction', $productId, $field, $id_product_attribute);

        if ($tmpPrice > 0) {
          $tmpPrice = Tools::ps_round($tmpPrice, $round_value);
          $tmpPrice = number_format($tmpPrice, $round_value, '.', '');
          $productInfo[$field] = $tmpPrice;
        }
      } elseif (preg_match('/^(?:c_)?specific_price_reduction_type_\d+$/', $field)) {
        $productInfo[$field] = $this->getSpecificPriceAttribute('reduction_type', $productId, $field, $id_product_attribute);
      } elseif (preg_match('/^(?:c_)?specific_price_from_\d+$/', $field)) {
        $productInfo[$field] = $this->getSpecificPriceAttribute('from', $productId, $field, $id_product_attribute);
      } elseif (preg_match('/^(?:c_)?specific_price_to_\d+$/', $field)) {
        $productInfo[$field] = $this->getSpecificPriceAttribute('to', $productId, $field, $id_product_attribute);
      } elseif (preg_match('/^(?:c_)?specific_price_id_group_\d+$/', $field)) {
        $productInfo[$field] = $this->getSpecificPriceAttribute('id_group', $productId, $field, $id_product_attribute);
      } elseif ($field == "quantity") {
        $productInfo[$field] = $product->getQuantity($productId, $id_product_attribute);
      } elseif ($field == "combinations_wholesale_price") {
        $tmpPrice = Tools::ps_round($combination->wholesale_price,$round_value);
        $tmpPrice = number_format($tmpPrice, $round_value, '.','');
        $productInfo[$field] = $tmpPrice;
      }
      elseif ($field == "combinations_unit_price_impact") {
        $tmpPrice = Tools::ps_round($combination->unit_price_impact,$round_value);
        $tmpPrice = number_format($tmpPrice, $round_value, '.','');
        $productInfo[$field] = $tmpPrice;
      }
      elseif ($field == "minimal_quantity") {
        if( $id_product_attribute ){
          $productInfo[$field] = $combination->minimal_quantity;
        }
        else{
          $productInfo[$field] = $product->minimal_quantity;
        }
      }
      elseif ($field == "location") {
        if ($id_product_attribute) {
          if( method_exists( new StockAvailable(), 'getLocation' ) ){
            $productInfo[$field] = StockAvailable::getLocation($product->id, $combination->id, $this->_idShop);
          }
          else{
            $productInfo[$field] = $combination->location;
          }

        } else {
          if( method_exists( new StockAvailable(), 'getLocation' ) ){
            $productInfo[$field] = StockAvailable::getLocation($product->id, 0, $this->_idShop);
          }
          else{
            $productInfo[$field] = $product->location;
          }
        }
      }
      elseif ($field == "low_stock_threshold") {
        if ($id_product_attribute) {
          $productInfo[$field] = $combination->low_stock_threshold;
        } else {
          $productInfo[$field] = $product->low_stock_threshold;
        }
      }
      elseif ($field == "low_stock_alert") {
        if ($id_product_attribute) {
          $productInfo[$field] = $combination->low_stock_alert;
        } else {
          $productInfo[$field] = $product->low_stock_alert;
        }
      }
      elseif ($field == "available_date") {
        if ($id_product_attribute) {
          $productInfo[$field] = $combination->available_date;
        } else {
          $productInfo[$field] = $product->available_date;
        }
      }
      elseif ($field == "combinations_reference") {
        $productInfo[$field] = $combination->reference;
      }
      elseif ($field == "combinations_weight") {
        $tmpPrice = Tools::ps_round($combination->weight,$round_value);
        $productInfo[$field] =  number_format($tmpPrice, $round_value, '.','');
      } elseif ($field == "combinations_ecotax") {
        $tmpPrice = Tools::ps_round($combination->ecotax,$round_value);
        $tmpPrice = number_format($tmpPrice, $round_value, '.','');
        $productInfo[$field] = $tmpPrice;
      } elseif ($field == "combinations_ean13") {
        $productInfo[$field] = $combination->ean13;
      } elseif ($field == "combinations_upc") {
        $productInfo[$field] = $combination->upc;
      } elseif ($field == "tags") {
        $productInfo[$field] = $product->getTags($this->_idLang);
      }
      elseif ($field == "id_attachments") {
        $productInfo[$field] = "";
        foreach ($product->getAttachments( $this->_idLang ) as $attachments ) {
          $productInfo[$field] .= $attachments['id_attachment'] . ";";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ";");
      }
      elseif ($field == "attachments_name") {
        $productInfo[$field] = "";
        foreach ($product->getAttachments( $this->_idLang ) as $attachments ) {
          $productInfo[$field] .= $attachments['name'] . ";";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ";");
      }
      elseif ($field == "attachments_description") {
        $productInfo[$field] = "";
        foreach ($product->getAttachments( $this->_idLang ) as $attachments ) {
          $productInfo[$field] .= $attachments['description'] . ";";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ";");
      }
      elseif ($field == "attachments_file") {
        $productInfo[$field] = "";
        $link = new Link(null, 'http://');
        foreach ($product->getAttachments( $this->_idLang ) as $attachments ) {
          $productInfo[$field] .= $link->getPageLink('attachment', true, NULL, "id_attachment=".$attachments['id_attachment']) . ";";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ";");
      }

      elseif ($field == "id_carriers") {
        $productInfo[$field] = "";
        foreach ($product->getCarriers() as $carriers) {
          $productInfo[$field] .= $carriers['id_carrier'] . ";";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ";");
      } elseif ($field == "id_product_accessories") {
        $productInfo[$field] = "";
        foreach ($product->getWsAccessories() as $accessories) {
          $productInfo[$field] .= $accessories['id'] . ";";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ";");
      } elseif ($field == "image_caption") {
        $productInfo[$field] = "";
        if($id_product_attribute){
          if($combination->getWsImages()){
            foreach ($combination->getWsImages() as $image) {
              $img = new Image($image['id'], $this->_idLang);
              $productInfo[$field] .= $img->legend . ";";
            }
          }
        }
        else{
          if($product->getWsImages()){
            foreach ($product->getWsImages() as $image) {
              $img = new Image($image['id'], $this->_idLang);
              $productInfo[$field] .= $img->legend . ";";
            }
          }
        }
        $productInfo[$field] = rtrim($productInfo[$field], ";");
      }
      elseif ($field == "images") {
        $productInfo[$field] = "";
        $link = new Link(null, 'http://');

        if($id_product_attribute && $combination->getWsImages()){
          if($combination->getWsImages()){
            foreach ($combination->getWsImages() as $image) {
              $productInfo[$field] .= $link->getImageLink($product->link_rewrite, $image['id']) . ";";
            }
          }
        }
        else{
          if($product->getWsImages()){
            foreach ($product->getWsImages() as $image) {
              $productInfo[$field] .= $link->getImageLink($product->link_rewrite, $image['id']) . ";";
            }
          }
        }

        $productInfo[$field] = rtrim($productInfo[$field], ";");
      }
      elseif( $field == "image_cover" ){
        $cover = $product->getCover($product->id);
        $images = $this->getCombinationImageById($id_product_attribute, $this->_idLang);
        if( !$cover && !$images ){
          $productInfo[$field] = false;
        }
        else{
          if($images['id_image']){
            $url_cover = _PS_ROOT_DIR_.'/img/p/'.Image::getImgFolderStatic($images['id_image']).$images['id_image'].'-'.$this->_imageType.'.jpg';
          }
          else{
            $url_cover = _PS_ROOT_DIR_.'/img/p/'.Image::getImgFolderStatic($cover['id_image']).$cover['id_image'].'-'.$this->_imageType.'.jpg';
          }
          $productInfo[$field] = $url_cover;
        }
      }
      elseif( strpos($field, 'feature') !== false){
        $exploded_feature_field = explode('_', $field, 2);
        $feature_id = $exploded_feature_field[1];

        if( Module::getInstanceByName('pm_multiplefeatures') ){
          $features = Module::getInstanceByName('pm_multiplefeatures')->getFrontFeatures($productId);
        } else{
          $features = $product->getFrontFeatures($this->_idLang);
        }

        foreach ($features as $feature) {
          if ($feature_id != $feature['id_feature']) {
            continue;
          }

          $productInfo[$field] = $feature['value'];
        }
      }
      elseif ($field == "product_link") {
        $productInfo[$field] = "";
        $link = new Link(null, 'http://');
        $productInfo[$field] = $link->getProductLink($productId);
      }
      elseif( $field == "description" || $field == "description_short"){
        $mora_settings = $this->_more_settings;
        if($mora_settings['strip_tags']){
          $productInfo[$field] = strip_tags($product->$field);
        }
        else{
          $productInfo[$field] = $product->$field;
        }
      }
      elseif( $field == "width" || $field == "height" || $field == "depth" || $field == "weight" ){
        $tmpPrice = Tools::ps_round($product->$field,$round_value);
        $productInfo[$field] =  number_format($tmpPrice, $round_value, '.','');
      }
      elseif( $field == "out_of_stock" ){
        $productInfo[$field] = StockAvailable::outOfStock($product->id);
      }
      else {
        $productInfo[$field] = $product->$field;
      }
    }

    return $productInfo;
  }



  public function getProductInfo($selected_fields, $productId, $id_product_attribute, $product){
    $combinations = array();
    $productInfo = array();
    $more_settings = $this->_more_settings;
    $round_value  = $more_settings['round_value'];

    $address = null;
    if (is_object(Context::getContext()->cart) && Context::getContext()->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')} != null) {
      $address = Context::getContext()->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')};
    }

    $product->tax_rate = $product->getTaxesRate(new Address($address));
    $product->base_price = $product->price;
    $product->unit_price = ($product->unit_price_ratio != 0  ? $product->price / $product->unit_price_ratio : 0);
    $product->manufacturer_name = Manufacturer::getNameById((int)$product->id_manufacturer);

    foreach( $product->getWsCombinations() as $attribute ){
      $combination = new Combination($attribute['id']);
      $combinations[$attribute['id']] = $combination;
    }

    foreach( $selected_fields as $field => $value ){
      if( $field == "id_product" ){
        $productInfo[$field] = $productId;
      }
      elseif( $field == "id_product_attribute" ){
        $productInfo[$field] = "";
        foreach( $combinations as $key=>$attribute ){
          $productInfo[$field] .= $key . ";";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ";");
      }
      elseif( $field == "categories_ids" ){
        $productInfo[$field] = "";
        foreach( $product->getWsCategories() as $category ){
          $productInfo[$field] .= $category['id'] . ";";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ";");
      }
      elseif( $field == "categories_names" ){
        $productInfo[$field] = "";
        foreach( $product->getWsCategories() as $category ){
          $cat_obj = new Category($category['id'], $this->_idLang, $this->_idShop);
          $productInfo[$field] .=   $cat_obj->name. ";";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ";");
      }
      elseif($field == 'suppliers_ids'){
        $product_supplier = $this->_model->getProductSuppliersID( $productId  );
        if($product_supplier){
          $productInfo[$field] = $product_supplier[0]['suppliers_ids'];
        }
      }
      elseif($field == 'supplier_reference'){
        $sReference = '';
        if( $combinations ){
          foreach( $combinations as $combination ){
            $sReference .= ProductSupplier::getProductSupplierReference($productId, $combination->id, $product->id_supplier) . ";";
          }
          $sReference = rtrim($sReference, ";");
        }
        else{
          $sReference =  ProductSupplier::getProductSupplierReference($productId, 0, $product->id_supplier);
          if( !$sReference ){
            $sReference = '';
          }
        }
        $productInfo[$field] = $sReference;
      }
      elseif($field == 'supplier_price'){
        $sPrice = '';
        if( $combinations ){
          foreach( $combinations as $combination ){
            $tmpPrice =  ProductSupplier::getProductSupplierPrice($productId, $combination->id, $product->id_supplier);

            if ($tmpPrice == '') {
              continue;
            }

            $tmpPrice = Tools::ps_round($tmpPrice,$round_value);
            $tmpPrice = number_format($tmpPrice, $round_value, '.','');
            $sPrice .= $tmpPrice . ";";
          }

          $sPrice = rtrim($sPrice, ";");
        }
        else{
          $sPrice =  ProductSupplier::getProductSupplierPrice($productId, 0, $product->id_supplier);
          if( !$sPrice ){
            $sPrice = '';
          }
          else{
            $sPrice = Tools::ps_round($sPrice,$round_value);
            $sPrice = number_format($sPrice, $round_value, '.','');
          }
        }

        $productInfo[$field] = $sPrice;

      }
      elseif($field == 'supplier_price_currency'){
        if( $combinations ){
          $productInfo[$field] = "";
          foreach( $combinations as $combination ){
            $sPriceCurrency =  ProductSupplier::getProductSupplierPrice($productId, $combination->id, $product->id_supplier, true);
            if( isset($sPriceCurrency['id_currency']) && $sPriceCurrency['id_currency'] ){
              $tmpCurrency = new Currency($sPriceCurrency['id_currency']);
              $sPriceCurrency['id_currency'] = $tmpCurrency->iso_code;
              $productInfo[$field] .= $sPriceCurrency['id_currency'] . ";";
            }
          }
          $productInfo[$field] = rtrim($productInfo[$field], ";");
        }
        else{
          $sPriceCurrency =  ProductSupplier::getProductSupplierPrice($productId, 0, $product->id_supplier, true);
          if( isset($sPriceCurrency['id_currency']) ){
            $tmpCurrency = new Currency($sPriceCurrency['id_currency']);
            $sPriceCurrency['id_currency'] = $tmpCurrency->iso_code;
            $productInfo[$field] = $sPriceCurrency['id_currency'];
          }
          else{
            $productInfo[$field] = '';
          }
        }
      }
      elseif($field == 'suppliers_name'){
        $product_supplier = $this->_model->getProductSuppliersID( $productId  );
        if($product_supplier){
          $productInfo[$field] = $product_supplier[0]['suppliers_name'];
        }
      }
      elseif ($field == 'suppliers_reference') {
        $productInfo[$field] = $this->getSuppliersPropertyInOneLine($productId, !empty($combinations), 'product_supplier_reference');
      }
      elseif ($field == 'suppliers_price') {
        $productInfo[$field] = $this->getSuppliersPropertyInOneLine($productId, !empty($combinations), 'product_supplier_price_te');
      }
      elseif ($field == 'suppliers_price_currency') {
        $productInfo[$field] = $this->getSuppliersPropertyInOneLine($productId, !empty($combinations), 'id_currency');
      }
      elseif( $field == "base_price" || $field == "ecotax" || $field == "additional_shipping_cost"  || $field == "unit_price"){
        $tmpPrice = Tools::ps_round($product->$field,$round_value);
        $tmpPrice = number_format($tmpPrice, $round_value, '.','');
        $productInfo[$field] = $tmpPrice;
      }
      elseif( $field == "base_price_with_tax" ){
        $taxPrice = $product->base_price;
        if( $product->tax_rate ){
          $taxPrice = $taxPrice + ($taxPrice * ($product->tax_rate/100));
        }
        $tmpPrice = Tools::ps_round($taxPrice,$round_value);
        $tmpPrice = number_format($tmpPrice, $round_value, '.','');
        $productInfo[$field] = $tmpPrice;
      }
      elseif ($field == "wholesale_price") {
        $tmpPrice = Tools::ps_round($product->$field,$round_value);
        $tmpPrice = number_format($tmpPrice, $round_value, '.','');
        $productInfo[$field] = $tmpPrice;
      }
      elseif( $field == "price" ){
        if( $combinations ){
          $productInfo[$field] = "";
          foreach( $combinations as $combination ){
            $price =  $product->getPrice(false, $combination->id, $round_value) . ";";
            $tmpPrice = Tools::ps_round($price, $round_value);
            $tmpPrice = number_format($tmpPrice, $round_value, '.','');
            $productInfo[$field] .=  $tmpPrice . ";";
          }
          $productInfo[$field] = rtrim($productInfo[$field], ";");
        }
        else{
          $taxPrice = $product->getPrice(false, 0, $round_value);
          $tmpPrice = Tools::ps_round($taxPrice,$round_value);
          $tmpPrice = number_format($tmpPrice, $round_value, '.','');
          $productInfo[$field] = $tmpPrice;
        }
      }
      elseif( $field == "final_price_with_tax" ){
        if( $combinations ){
          $productInfo[$field] = "";
          foreach( $combinations as $combination ){
            $price =  $product->getPrice(true, $combination->id, $round_value) . ";";
            $tmpPrice = Tools::ps_round($price, $round_value);
            $tmpPrice = number_format($tmpPrice, $round_value, '.','');
            $productInfo[$field] .=  $tmpPrice . ";";

          }
          $productInfo[$field] = rtrim($productInfo[$field], ";");
        }
        else{
          $taxPrice = $product->getPrice(true, 0, $round_value);
          $tmpPrice = Tools::ps_round($taxPrice,$round_value);
          $tmpPrice = number_format($tmpPrice, $round_value, '.','');
          $productInfo[$field] = $tmpPrice;
        }
      }
      elseif( $field == "combinations_name" ){
        $productInfo[$field] = "";
        foreach( $combinations as $combination ){
          $productInfo[$field] .= str_replace($product->name . " : ",'',Product::getProductName( $product->id, $combination->id )). ";";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ";");
      }
      elseif( $field == "combinations_price" ){
        $productInfo[$field] = "";
        foreach( $combinations as $combination ){
          $tmpPrice = Tools::ps_round($combination->price,$round_value);
          $tmpPrice = number_format($tmpPrice, $round_value, '.','');
          $productInfo[$field] .= $tmpPrice . ";";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ";");
      }
      elseif ($field == "combinations_supplier_price") {
        $productInfo[$field] = "";
        foreach( $combinations as $combination ){
          $sPrice =  ProductSupplier::getProductSupplierPrice($productId, $combination->id, $product->id_supplier);
          $tmpPrice = Tools::ps_round($sPrice,$round_value);
          $tmpPrice = number_format($tmpPrice, $round_value, '.','');
          $productInfo[$field] .= $tmpPrice . ";";
        }

        $productInfo[$field] = rtrim($productInfo[$field], ";");
      }
      elseif( $field == "combinations_price_with_tax" ){
        $productInfo[$field] = "";
        foreach( $combinations as $combination ){
          $taxPrice = $combination->price;
          $price = ( $taxPrice + ($taxPrice * ($product->tax_rate/100)) );
          $tmpPrice = Tools::ps_round($price,$round_value);
          $tmpPrice = number_format($tmpPrice, $round_value, '.','');
          $productInfo[$field] .= $tmpPrice . ";";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ";");
      }
      elseif (preg_match('/id_specific_price_\d+/', $field)) {
        $productInfo[$field] = $this->getSpecificPriceAttribute('id_specific_price', $productId, $field);
      } elseif (preg_match('/specific_price_\d+/', $field)) {
        $tmpPrice = $this->getSpecificPriceAttribute('price', $productId, $field);

        if ($tmpPrice > 0) {
          $tmpPrice = Tools::ps_round($tmpPrice, $round_value);
          $tmpPrice = number_format($tmpPrice, $round_value, '.','');
          $productInfo[$field] = $tmpPrice;
        }
      } elseif (preg_match('/^specific_price_from_quantity_\d+$/', $field)) {
        $productInfo[$field] = $this->getSpecificPriceAttribute('from_quantity', $productId, $field);
      } elseif (preg_match('/^specific_price_reduction_\d+$/', $field)) {
        $tmpPrice = $this->getSpecificPriceAttribute('reduction', $productId, $field);

        if ($tmpPrice > 0) {
          $tmpPrice = Tools::ps_round($tmpPrice, $round_value);
          $tmpPrice = number_format($tmpPrice, $round_value, '.', '');
          $productInfo[$field] = $tmpPrice;
        }
      } elseif (preg_match('/^specific_price_reduction_type_\d+$/', $field)) {
        $productInfo[$field] = $this->getSpecificPriceAttribute('reduction_type', $productId, $field);
      } elseif (preg_match('/^specific_price_from_\d+$/', $field)) {
        $productInfo[$field] = $this->getSpecificPriceAttribute('from', $productId, $field);
      } elseif (preg_match('/^specific_price_to_\d+$/', $field)) {
        $productInfo[$field] = $this->getSpecificPriceAttribute('to', $productId, $field);
      } elseif (preg_match('/^specific_price_id_group_\d+$/', $field)) {
        $productInfo[$field] = $this->getSpecificPriceAttribute('id_group', $productId, $field);
      }
      elseif( $field == "quantity" ){
        if( $combinations ){
          $productInfo[$field] = "";
          foreach( $combinations as $key=>$combination ){
            $productInfo[$field] .=  $product->getQuantity( $productId, $key ) . ";";
          }
          $productInfo[$field] = rtrim($productInfo[$field], ";");
        }
        else{
          $productInfo[$field] = $product->getQuantity($productId, 0);
        }
      }
      elseif( $field == "combinations_wholesale_price" ){
        $productInfo[$field] = "";
        foreach( $combinations as $combination ){
          $tmpPrice = Tools::ps_round($combination->wholesale_price, $round_value);
          $tmpPrice = number_format($tmpPrice, $round_value, '.','');
          $productInfo[$field] .=  $tmpPrice . ";";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ";");
      }
      elseif( $field == "combinations_unit_price_impact" ){
        $productInfo[$field] = "";
        foreach( $combinations as $combination ){
          $tmpPrice = Tools::ps_round($combination->unit_price_impact, $round_value);
          $tmpPrice = number_format($tmpPrice, $round_value, '.','');
          $productInfo[$field] .=  $tmpPrice . ";";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ";");
      }
      elseif( $field == "minimal_quantity" ){
        if( $combinations ){
          $productInfo[$field] = "";
          foreach( $combinations as $combination ){
            $productInfo[$field] .=  $combination->minimal_quantity . ";";
          }
          $productInfo[$field] = rtrim($productInfo[$field], ";");
        }
        else{
          $productInfo[$field] = $product->minimal_quantity;
        }
      }
      elseif ($field == "location") {
        if ($combinations) {
          $productInfo[$field] = "";
          foreach ($combinations as $combination) {
            if( method_exists( new StockAvailable(), 'getLocation' ) ){
              $productInfo[$field] .= StockAvailable::getLocation($product->id, $combination->id, $this->_idShop) . ";";
            }
            else{
              $productInfo[$field] .= $combination->location;
            }
          }
          $productInfo[$field] = rtrim($productInfo[$field], ";");
        } else {
          if( method_exists( new StockAvailable(), 'getLocation' ) ){
            $productInfo[$field] = StockAvailable::getLocation($product->id, 0, $this->_idShop);
          }
          else{
            $productInfo[$field] = $product->location;
          }
        }
      }
      elseif ($field == "low_stock_threshold") {
        if ($combinations) {
          $productInfo[$field] = "";
          foreach ($combinations as $combination) {
            $productInfo[$field] .= $combination->low_stock_threshold . ";";
          }
          $productInfo[$field] = rtrim($productInfo[$field], ";");
        } else {
          $productInfo[$field] = $product->low_stock_threshold;
        }
      }
      elseif ($field == "low_stock_alert") {
        if ($combinations) {
          $productInfo[$field] = "";
          foreach ($combinations as $combination) {
            $productInfo[$field] .= $combination->low_stock_alert . ";";
          }
          $productInfo[$field] = rtrim($productInfo[$field], ";");
        } else {
          $productInfo[$field] = $product->low_stock_alert;
        }
      }
      elseif ($field == "available_date") {
        if ($combinations) {
          $productInfo[$field] = "";
          foreach ($combinations as $combination) {
            $productInfo[$field] .= $combination->available_date . ";";
          }
          $productInfo[$field] = rtrim($productInfo[$field], ";");
        } else {
          $productInfo[$field] = $product->available_date;
        }
      }
      elseif( $field == "combinations_reference" ){
        $productInfo[$field] = "";
        foreach( $combinations as $combination ){
          $productInfo[$field] .=  $combination->reference . ";";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ";");
      }
      elseif( $field == "combinations_weight" ){
        $productInfo[$field] = "";
        foreach( $combinations as $combination ){
          $tmpPrice = Tools::ps_round($combination->weight,$round_value);
          $productInfo[$field] .=   number_format($tmpPrice, $round_value, '.','') . ";";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ";");
      }
      elseif( $field == "combinations_ecotax" ){
        $productInfo[$field] = "";
        foreach( $combinations as $combination ){
          $tmpPrice = Tools::ps_round($combination->ecotax,$round_value);
          $tmpPrice = number_format($tmpPrice, $round_value, '.','');
          $productInfo[$field] .=  $tmpPrice . ";";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ";");
      }
      elseif( $field == "combinations_ean13" ){
        $productInfo[$field] = "";
        foreach( $combinations as $combination ){
          $productInfo[$field] .=  $combination->ean13 . ";";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ";");
      }
      elseif( $field == "combinations_upc" ){
        $productInfo[$field] = "";
        foreach( $combinations as $combination ){
          $productInfo[$field] .=  $combination->upc . ";";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ";");
      }
      elseif( $field == "tags" ){
        $productInfo[$field] = $product->getTags( $this->_idLang );
      }
      elseif ($field == "id_attachments") {
        $productInfo[$field] = "";
        foreach ($product->getAttachments( $this->_idLang ) as $attachments ) {
          $productInfo[$field] .= $attachments['id_attachment'] . ";";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ";");
      }
      elseif ($field == "attachments_name") {
        $productInfo[$field] = "";
        foreach ($product->getAttachments( $this->_idLang ) as $attachments ) {
          $productInfo[$field] .= $attachments['name'] . ";";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ";");
      }
      elseif ($field == "attachments_description") {
        $productInfo[$field] = "";
        foreach ($product->getAttachments( $this->_idLang ) as $attachments ) {
          $productInfo[$field] .= $attachments['description'] . ";";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ";");
      }
      elseif ($field == "attachments_file") {
        $productInfo[$field] = "";
        $link = new Link(null, 'http://');
        foreach ($product->getAttachments( $this->_idLang ) as $attachments ) {
          $productInfo[$field] .= $link->getPageLink('attachment', true, NULL, "id_attachment=".$attachments['id_attachment']) . ";";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ";");
      }
      elseif ($field == "id_carriers") {
        $productInfo[$field] = "";
        foreach ($product->getCarriers() as $carriers) {
          $productInfo[$field] .= $carriers['id_carrier'] . ";";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ";");
      }
      elseif ($field == "id_product_accessories") {
        $productInfo[$field] = "";
        foreach ($product->getWsAccessories() as $accessories) {
          $productInfo[$field] .= $accessories['id'] . ";";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ";");
      }
      elseif ($field == "image_caption") {
        $productInfo[$field] = "";
        foreach ($product->getWsImages() as $image) {
          $img = new Image($image['id'], $this->_idLang);
          $productInfo[$field] .= $img->legend . ";";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ";");
      }
      elseif( $field == "images" ){
        $productInfo[$field] = "";
        $link = new Link(null, 'http://');
        foreach( $product->getWsImages() as $image ){
          $productInfo[$field] .= $link->getImageLink( $product->link_rewrite, $image['id'] ) . ";";
        }
        $productInfo[$field] = rtrim($productInfo[$field], ";");
      }
      elseif( $field == "image_cover" ){
        $cover = $product->getCover($product->id);
        if( !$cover ){
          $productInfo[$field] = false;
        }
        else{
          $url_cover = _PS_ROOT_DIR_.'/img/p/'.Image::getImgFolderStatic($cover['id_image']).$cover['id_image'].'-'.$this->_imageType.'.jpg';
          $productInfo[$field] = $url_cover;
        }
      }
      elseif( strpos($field, 'feature') !== false){
        $exploded_feature_field = explode('_', $field, 2);
        $feature_id = $exploded_feature_field[1];

        if( Module::getInstanceByName('pm_multiplefeatures') ){
          $features = Module::getInstanceByName('pm_multiplefeatures')->getFrontFeatures($productId);
        } else{
          $features = $product->getFrontFeatures($this->_idLang);
        }

        foreach ($features as $feature) {
          if ($feature_id != $feature['id_feature']) {
            continue;
          }

          $productInfo[$field] = $feature['value'];
        }
      }
      elseif( $field == "product_link" ){
        $productInfo[$field] = "";
        $link = new Link(null, 'http://');
        $productInfo[$field] = $link->getProductLink($productId);
      }
      elseif( $field == "description" || $field == "description_short"){
        $mora_settings = $this->_more_settings;
        if($mora_settings['strip_tags']){
          $productInfo[$field] = strip_tags($product->$field);
        }
        else{
          $productInfo[$field] = $product->$field;
        }
      }
      elseif( $field == "width" || $field == "height" || $field == "depth" || $field == "weight"){
        $tmpPrice = Tools::ps_round($product->$field,$round_value);
        $productInfo[$field] =  number_format($tmpPrice, $round_value, '.','');
      }
      elseif( $field == "out_of_stock" ){
        $productInfo[$field] = StockAvailable::outOfStock($product->id);
      }
      else{
        $productInfo[$field] = $product->$field;
      }
    }

    return $productInfo;
  }

  public static function getCombinationImageById($id_product_attribute, $id_lang)
  {
    if (!Combination::isFeatureActive() || !$id_product_attribute) {
      return false;
    }

    $result = Db::getInstance()->executeS('
    SELECT pai.`id_image`, pai.`id_product_attribute`, il.`legend`
    FROM `'._DB_PREFIX_.'product_attribute_image` pai
    LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (il.`id_image` = pai.`id_image`)
    LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_image` = pai.`id_image`)
    WHERE pai.`id_product_attribute` = '.(int)$id_product_attribute.' AND il.`id_lang` = '.(int)$id_lang.' ORDER by i.`position` LIMIT 1'
    );

    if (!$result) {
      return false;
    }

    return $result[0];
  }

  private function splitSpecificPriceFields($selected_fields)
  {
    $specific_price_fields = array(
      'id_specific_price' => '',
      'specific_price' => '',
      'specific_price_reduction' => '',
      'specific_price_reduction_type' => '',
      'specific_price_from' => '',
      'specific_price_to' => '',
      'specific_price_from_quantity' => '',
      'specific_price_id_group' => '',
    );

    $specific_price_selected_fields = array_intersect_key($selected_fields, $specific_price_fields);
    $specific_price_num_of_cols = $this->_model->getExportIds($this->_idShop, $this->_idLang, $this->_shopGroupId, $this->_separate, $this->_more_settings, $this->_limit, $this->_limitN, false, true);

    if (!empty($specific_price_selected_fields)) {
      foreach ($specific_price_selected_fields as $key => $value) {
        unset($selected_fields[$key]);

        if ($this->_separate) {
          $max_num_of_spec_prices = $this->getMaxNumOfSpecPrices(false);
          $max_num_of_spec_prices_comb = $this->getMaxNumOfSpecPrices(true);

          $name_id = 1;
          for ($i = 1; $i <= $max_num_of_spec_prices; $i++) {
            $selected_fields[$key . '_' . $i] = $value . '_' . $i;
            $name_id = $i + 1;
          }

          for ($i = 1; $i <= $max_num_of_spec_prices_comb; $i++) {
            $selected_fields['c_' . $key . '_' . $i] = $value . '_' . $name_id;
            $name_id += $i;
          }
        } else {
          for ($i = 1; $i <= $specific_price_num_of_cols; $i++) {
            $selected_fields[$key . '_' . $i] = $value . '_' . $i;
          }
        }
      }
    }

    return $selected_fields;
  }

  private function getMaxNumOfSpecPrices($with_combination)
  {
    $products_for_export = $productIds = $this->_model->getIdsOfProductsForExport( $this->_idShop, $this->_idLang, $this->_shopGroupId, $this->_separate, $this->_more_settings, $this->_limit, $this->_limitN );

    if ($with_combination) {
      $and = ' AND id_product_attribute != 0 ';
      $group_by = ' GROUP BY id_product_attribute ';
    } else {
      $and = ' AND id_product_attribute = 0 ';
      $group_by = ' GROUP BY id_product ';
    }

    return Db::getInstance()->getValue("SELECT MAX(sp.num_of_spec_prices) FROM 
                                              (SELECT COUNT(id_product) as num_of_spec_prices 
                                                FROM "._DB_PREFIX_."specific_price 
                                                WHERE id_product IN (".$products_for_export.")
                                                  ".$and."
                                                  ".$group_by."
                                              ) as sp;");
  }

  public static function getSpecificPriceByProduct($id_product, $id_product_attribute = false)
  {
    return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
          SELECT *
          FROM `' . _DB_PREFIX_ . 'specific_price`
          WHERE `id_product` = ' . (int) $id_product .
            ($id_product_attribute !== false ? ' AND id_product_attribute = ' . (int) $id_product_attribute : '')
    );
  }

  private function getSpecificPriceAttribute($specific_price_attr_name, $product_id, $field, $product_attribute_id = null)
  {
    $specific_price_attribute = '';
    $specific_price_field_numbers = explode('_', $field);
    $specific_price_field_number = end($specific_price_field_numbers);

    $specific_prices = $this->getSpecificPriceByProduct($product_id);

    if ($this->_separate) {
      if ($specific_price_field_numbers[0] != 'c') {
        $product_attribute_id = 0;
      }

      $specific_prices = $this->getSpecificPriceByProduct($product_id, $product_attribute_id);
    }

    if (isset($specific_prices[$specific_price_field_number - 1][$specific_price_attr_name])) {
      $specific_price_attribute = $specific_prices[$specific_price_field_number - 1][$specific_price_attr_name];
    }

    return $specific_price_attribute;
  }

  private function getSuppliersPropertyInOneLine($id_product, $has_combinations, $property_name, $id_product_attribute = false)
  {
    $all_product_suppliers = productsUpdateModel::getProductSuppliers($id_product, $id_product_attribute);
    $suppliers_references = '';

    if (!empty($all_product_suppliers)) {
      foreach ($all_product_suppliers as $product_suppliers) {
        foreach ($product_suppliers as $product_supplier) {
          if ($has_combinations && $product_supplier['id_product_attribute'] == 0) {
            continue;
          }

          switch ($property_name) {
            case 'product_supplier_price_te':
              $suppliers_references .= $this->formatPrice($product_supplier[$property_name]) . ';';
              break;
            case 'id_currency':
              $currency = Currency::getCurrency($product_supplier[$property_name]);
              $suppliers_references .= $currency['iso_code'] . ';';
              break;
            default:
              $suppliers_references .= $product_supplier[$property_name] . ';';
          }
        }

        $suppliers_references = trim($suppliers_references, '; ');
        $suppliers_references .= ',';
      }
    }

    return trim($suppliers_references, ',');
  }

  private function formatPrice($price)
  {
    $formatted_price = Tools::ps_round($price, $this->_more_settings['round_value']);
    return number_format($formatted_price, $this->_more_settings['round_value'], '.','');
  }

  private function addFeaturesFieldsToSelectedFields($selected_fields)
  {
    foreach ($selected_fields as $field_key => $field_name) {
      if (strpos($field_key, 'feature') !== false) {
        $exploded_field_key = explode('_', $field_key, 2);
        $feature_id = trim($exploded_field_key[1]);
        $feature_name = productsUpdateModel::getFeatureNameById($feature_id, $this->_idLang);
        $selected_fields['feature_' . $feature_id] = $feature_id . '_FEATURE_' . $feature_name;
      }
    }

    return $selected_fields;
  }
}
