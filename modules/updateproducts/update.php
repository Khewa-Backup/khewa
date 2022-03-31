<?php

  class updateProductCatalog
  {
    private $_context;
    private $_idShop;
    private $_shopGroupId;
    private $_idLang;
    private $_format;
    private $_model;
    private $_ids_images = array();
    private $_PHPExcelFactory;
    private $_alphabet;
    private $_updateFields;
    private $_head;
    private $_old_images = array();
    private $_productsForUpdate = 0;
    private $_updatedProducts = 0;
    private $_isFeature = false;
    private $_separate;
    private $_removeImages;
    private $_limit;
    private $_image = false;
    private $_cover = false;
    private $_limitN = 250;

    private $all_specific_price_fields = array();

    public function __construct( $idShop, $idLang, $format, $fields, $separate, $remove_images, $disableHooks ){
      include_once(dirname(__FILE__).'/../../config/config.inc.php');
      include_once(dirname(__FILE__).'/../../init.php');

      if (!class_exists('PHPExcel')) {
        include_once(_PS_MODULE_DIR_ . 'updateproducts/libraries/PHPExcel_1.7.9/Classes/PHPExcel.php');
        include_once(_PS_MODULE_DIR_ . 'updateproducts/libraries/PHPExcel_1.7.9/Classes/PHPExcel/IOFactory.php');
      }

      include_once(_PS_MODULE_DIR_ . 'updateproducts/updateproducts.php');
      include_once('datamodel.php');

        if( $disableHooks ){
        define('PS_INSTALLATION_IN_PROGRESS', true);
      }

      $this->_context = Context::getContext();
      $this->_idShop = $idShop;
      if( isset(Context::getContext()->shop->id_shop_group) ){
        $this->_shopGroupId = Context::getContext()->shop->id_shop_group;
      }
      elseif( isset(Context::getContext()->shop->id_group_shop) ){
        $this->_shopGroupId = Context::getContext()->shop->id_group_shop;
      }
      $this->_idLang = (int)$idLang;
      $this->_format = $format;
      $this->_separate = $separate;
      $this->_removeImages = (int)$remove_images;
      $this->_model = new productsUpdateModel();

      $this->_updateFields = $fields;
      $this->_alphabet = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
                               'AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ',
                               'BA','BB','BC','BD','BE','BF','BG','BH', 'BI','BJ','BK','BL','BM','BN','BO','BP','BQ', 'BR','BS','BT','BU','BV','BW','BX','BY','BZ',
                               'CA','CC','CC','CD','CE','CF','CG','CH', 'CI','CJ','CK','CL','CM','CN','CO','CP','CQ', 'CR','CS','CT','CU','CV','CW','CX','CY','CZ',
                               'DA','DD','DD','DD','DE','DF','DG','DH', 'DI','DJ','DK','DL','DM','DN','DO','DP','DQ', 'DR','DS','DT','DU','DV','DW','DX','DY','DZ',
                               'EA', 'EB', 'EC', 'ED', 'EE', 'EF', 'EG', 'EH', 'EI', 'EJ', 'EK', 'EL', 'EM', 'EN', 'EO', 'EP', 'EQ', 'ER', 'ES', 'ET', 'EU', 'EV', 'EW', 'EX', 'EY', 'EZ',
                               'FA', 'FB', 'FC', 'FD', 'FE', 'FF', 'FG', 'FH', 'FI', 'FJ', 'FK', 'FL', 'FM', 'FN', 'FO', 'FP', 'FQ', 'FR', 'FS', 'FT', 'FU', 'FV', 'FW', 'FX', 'FY', 'FZ',
                               'GA', 'GB', 'GC', 'GD', 'GE', 'GF', 'GG', 'GH', 'GI', 'GJ', 'GK', 'GL', 'GM', 'GN', 'GO', 'GP', 'GQ', 'GR', 'GS', 'GT', 'GU', 'GV', 'GW', 'GX', 'GY', 'GZ',
                               'HA', 'HB', 'HC', 'HD', 'HE', 'HF', 'HG', 'HH', 'HI', 'HJ', 'HK', 'HL', 'HM', 'HN', 'HO', 'HP', 'HQ', 'HR', 'HS', 'HT', 'HU', 'HV', 'HW', 'HX', 'HY', 'HZ',
                               'IA', 'IB', 'IC', 'ID', 'IE', 'IF', 'IG', 'IH', 'II', 'IJ', 'IK', 'IL', 'IM', 'IN', 'IO', 'IP', 'IQ', 'IR', 'IS', 'IT', 'IU', 'IV', 'IW', 'IX', 'IY', 'IZ',
                               'JA', 'JB', 'JC', 'JD', 'JE', 'JF', 'JG', 'JH', 'JI', 'JJ', 'JK', 'JL', 'JM', 'JN', 'JO', 'JP', 'JQ', 'JR', 'JS', 'JT', 'JU', 'JV', 'JW', 'JX', 'JY', 'JZ',
                               'KA', 'KB', 'KC', 'KD', 'KE', 'KF', 'KG', 'KH', 'KI', 'KJ', 'KK', 'KL', 'KM', 'KN', 'KO', 'KP', 'KQ', 'KR', 'KS', 'KT', 'KU', 'KV', 'KW', 'KX', 'KY', 'KZ',
                               'LA', 'LB', 'LC', 'LD', 'LE', 'LF', 'LG', 'LH', 'LI', 'LJ', 'LK', 'LL', 'LM', 'LN', 'LO', 'LP', 'LQ', 'LR', 'LS', 'LT', 'LU', 'LV', 'LW', 'LX', 'LY', 'LZ',
                               'MA', 'MB', 'MC', 'MD', 'ME', 'MF', 'MG', 'MH', 'MI', 'MJ', 'MK', 'ML', 'MM', 'MN', 'MO', 'MP', 'MQ', 'MR', 'MS', 'MT', 'MU', 'MV', 'MW', 'MX', 'MY', 'MZ',
                               'NA', 'NB', 'NC', 'ND', 'NE', 'NF', 'NG', 'NH', 'NI', 'NJ', 'NK', 'NL', 'NM', 'NN', 'NO', 'NP', 'NQ', 'NR', 'NS', 'NT', 'NU', 'NV', 'NW', 'NX', 'NY', 'NZ',
                               'OA', 'OB', 'OC', 'OD', 'OE', 'OF', 'OG', 'OH', 'OI', 'OJ', 'OK', 'OL', 'OM', 'ON', 'OO', 'OP', 'OQ', 'OR', 'OS', 'OT', 'OU', 'OV', 'OW', 'OX', 'OY', 'OZ',
                               'PA', 'PB', 'PC', 'PD', 'PE', 'PF', 'PG', 'PH', 'PI', 'PJ', 'PK', 'PL', 'PM', 'PN', 'PO', 'PP', 'PQ', 'PR', 'PS', 'PT', 'PU', 'PV', 'PW', 'PX', 'PY', 'PZ',
                               'QA', 'QB', 'QC', 'QD', 'QE', 'QF', 'QG', 'QH', 'QI', 'QJ', 'QK', 'QL', 'QM', 'QN', 'QO', 'QP', 'QQ', 'QR', 'QS', 'QT', 'QU', 'QV', 'QW', 'QX', 'QY', 'QZ',
                               'RA', 'RB', 'RC', 'RD', 'RE', 'RF', 'RG', 'RH', 'RI', 'RJ', 'RK', 'RL', 'RM', 'RN', 'RO', 'RP', 'RQ', 'RR', 'RS', 'RT', 'RU', 'RV', 'RW', 'RX', 'RY', 'RZ',
                               'SA', 'SB', 'SC', 'SD', 'SE', 'SF', 'SG', 'SH', 'SI', 'SJ', 'SK', 'SL', 'SM', 'SN', 'SO', 'SP', 'SQ', 'SR', 'SS', 'ST', 'SU', 'SV', 'SW', 'SX', 'SY', 'SZ',
                               'TA', 'TB', 'TC', 'TD', 'TE', 'TF', 'TG', 'TH', 'TI', 'TJ', 'TK', 'TL', 'TM', 'TN', 'TO', 'TP', 'TQ', 'TR', 'TS', 'TT', 'TU', 'TV', 'TW', 'TX', 'TY', 'TZ',
                               'UA', 'UB', 'UC', 'UD', 'UE', 'UF', 'UG', 'UH', 'UI', 'UJ', 'UK', 'UL', 'UM', 'UN', 'UO', 'UP', 'UQ', 'UR', 'US', 'UT', 'UU', 'UV', 'UW', 'UX', 'UY', 'UZ',
                               'VA', 'VB', 'VC', 'VD', 'VE', 'VF', 'VG', 'VH', 'VI', 'VJ', 'VK', 'VL', 'VM', 'VN', 'VO', 'VP', 'VQ', 'VR', 'VS', 'VT', 'VU', 'VV', 'VW', 'VX', 'VY', 'VZ',
                               'WA', 'WB', 'WC', 'WD', 'WE', 'WF', 'WG', 'WH', 'WI', 'WJ', 'WK', 'WL', 'WM', 'WN', 'WO', 'WP', 'WQ', 'WR', 'WS', 'WT', 'WU', 'WV', 'WW', 'WX', 'WY', 'WZ',
                               'XA', 'XB', 'XC', 'XD', 'XE', 'XF', 'XG', 'XH', 'XI', 'XJ', 'XK', 'XL', 'XM', 'XN', 'XO', 'XP', 'XQ', 'XR', 'XS', 'XT', 'XU', 'XV', 'XW', 'XX', 'XY', 'XZ',
                               'YA', 'YB', 'YC', 'YD', 'YE', 'YF', 'YG', 'YH', 'YI', 'YJ', 'YK', 'YL', 'YM', 'YN', 'YO', 'YP', 'YQ', 'YR', 'YS', 'YT', 'YU', 'YV', 'YW', 'YX', 'YY', 'YZ',
                               'ZA', 'ZB', 'ZC', 'ZD', 'ZE', 'ZF', 'ZG', 'ZH', 'ZI', 'ZJ', 'ZK', 'ZL', 'ZM', 'ZN', 'ZO', 'ZP', 'ZQ', 'ZR', 'ZS', 'ZT', 'ZU', 'ZV', 'ZW', 'ZX', 'ZY', 'ZZ',
      );

      $this->all_specific_price_fields = array(
        'id_specific_price' => array('id' => 'id_specific_price', 'name' => false, 'to_update' => false, 'values' => ''),
        'specific_price' => array('id' => 'specific_price', 'name' => false, 'to_update' => false, 'values' => ''),
        'specific_price_reduction' => array('id' => 'specific_price_reduction', 'name' => false, 'to_update' => false, 'values' => ''),
        'specific_price_reduction_type' => array('id' => 'specific_price_reduction_type', 'name' => false, 'to_update' => false, 'values' => ''),
        'specific_price_from' => array('id' => 'specific_price_from', 'name' => false, 'to_update' => false, 'values' => ''),
        'specific_price_to' => array('id' => 'specific_price_to', 'name' => false, 'to_update' => false, 'values' => ''),
        'specific_price_from_quantity' => array('id' => 'specific_price_from_quantity', 'name' => false, 'to_update' => false, 'values' => ''),
        'specific_price_id_group' => array('id' => 'specific_price_id_group', 'name' => false, 'to_update' => false, 'values' => ''),
      );
    }
    private function _truncateImageTable()
    {
      Db::getInstance()->execute('TRUNCATE '._DB_PREFIX_.'updateproducts_images');
    }
    public function update( $limit = false )
    {
      $this->_limit = $limit;

      if( !$this->_limit ){
        $this->_clearErrorFile();
        $this->_truncateImageTable();
        Configuration::updateValue('UPDATED_PRODUCTS_CURRENT_COUNT', 0, false, $this->_shopGroupId, $this->_idShop);
        Configuration::updateValue('UPDATED_PRODUCTS_OLD_IMAGES', serialize(array()), false, $this->_shopGroupId, $this->_idShop);
        Configuration::updateValue('GOMAKOIL_IMPORT_IMAGES_ERROR_LOG', (int)0, false, $this->_shopGroupId, $this->_idShop);
        $this->_updatedProducts = 0;
      }
      else{
        $this->_updatedProducts = (int)Configuration::get('UPDATED_PRODUCTS_CURRENT_COUNT', '' ,$this->_shopGroupId, $this->_idShop);
      }


      $this->_copyFile();
      $this->_updateData();


      Configuration::updateValue('UPDATED_PRODUCTS_CURRENT_COUNT', $this->_updatedProducts, false, $this->_shopGroupId, $this->_idShop);

      if( (int)$this->_productsForUpdate > ((int)$this->_limit*(int)$this->_limitN)+(int)$this->_limitN ){
        return (int)$this->_limit+1;
      }



      $images = Tools::unserialize(Configuration::get('UPDATED_PRODUCTS_OLD_IMAGES', '' ,$this->_shopGroupId, $this->_idShop));
      if($this->_removeImages && $images){
        foreach ($images as $img){
          $image_del = new Image($img);
          $image_del->delete();
        }
      }

      if( $this->_updatedProducts != $this->_productsForUpdate ){

        $res = array(
          'message'     =>  sprintf(Module::getInstanceByName('updateproducts')->l('Successfully updated %1s items from: %2s'), $this->_updatedProducts, $this->_productsForUpdate),
          'error_logs'  => _PS_BASE_URL_SSL_.__PS_BASE_URI__.'modules/updateproducts/error/error_logs.csv',
        );

        return $res;
      }

      $res = array(
        'message'     =>  sprintf(Module::getInstanceByName('updateproducts')->l('Successfully updated %s items!'), $this->_updatedProducts),
        'error_logs'  => false
      );

      if( Configuration::get('GOMAKOIL_IMPORT_IMAGES_ERROR_LOG', null, $this->_shopGroupId, $this->_idShop) ){
        $res['error_logs'] = _PS_BASE_URL_SSL_.__PS_BASE_URI__.'modules/updateproducts/error/error_logs.csv';
      }



      return $res;

    }


    private function _clearErrorFile()
    {
      $write_fd = fopen('error/error_logs.csv', 'w');
      fwrite($write_fd, 'id_product,error'."\r\n");
      fclose($write_fd);
    }

    private function _checkHead( $fileFields )
    {

      if(!$this->_separate && isset($this->_updateFields['images']) && $this->_updateFields['images']){
        throw new Exception( Module::getInstanceByName('updateproducts')->l('If you want to update images, must activate option "Each product combinations in a separate line"!') );
      }

      $mappingFields = array();
      foreach( $this->_updateFields as $key => $field ){
        if (array_key_exists($key, $this->all_specific_price_fields)){
          foreach($fileFields as $fileField) {
            if (preg_match( '/^' . preg_quote($this->_updateFields[$key]) . '_[\d+]$/', $fileField)) {
              $this->all_specific_price_fields[$key]['name'] = $this->_updateFields[$key];
              $this->all_specific_price_fields[$key]['to_update'] = true;
            }
          }

          if ($this->all_specific_price_fields[$key]['name'] == false && $this->all_specific_price_fields[$key]['to_update'] == false) {
            throw new Exception( sprintf(Module::getInstanceByName('updateproducts')->l('Field: %s need to be in file for update!'), $field) );
          }

          continue;
        }

        if( $key == "features" ){
          foreach( $fileFields as $fileField ){
            if( stripos( $fileField, "feature" ) !== false ){
              $this->_isFeature = true;
            }
          }
          if( !$this->_isFeature ){
            throw new Exception( sprintf(Module::getInstanceByName('updateproducts')->l('Field: %s need to be in file for update!'), $field) );
          }
          continue;
        }

        if (strpos($key, 'FEATURE_') !== false) {
          $key = $this->getFeatureKeyByLang($key);
          $field = $key;
        }

        if( !in_array( $field, $fileFields )){
          throw new Exception( sprintf(Module::getInstanceByName('updateproducts')->l('Field: %s need to be in file for update!'), $field) );
        } else{
          $mappingFields[$field] = $key;
        }
      }

      return $mappingFields;
    }

    private function getFeatureKeyByLang($feature_key_default_lang)
    {
      $exploded_feature_key = explode('_', $feature_key_default_lang, 3);
      $feature_id = $exploded_feature_key[0];
      $exploded_feature_key[2] = productsUpdateModel::getFeatureNameById($feature_id, $this->_idLang);

      return implode('_', $exploded_feature_key);
    }

    private function _updateData()
    {
      foreach ($this->_PHPExcelFactory->getWorksheetIterator() as $worksheet) {
        $highestRow         = $worksheet->getHighestRow(); // e.g. 10
        $highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
        $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
        $fileFields = array();
        $this->_productsForUpdate = ($highestRow - 1);
        $checkHead = false;

        if ($this->_productsForUpdate <= ($this->_updatedProducts)) {
          return false;
        }

        $rowLimit = ( (($this->_limit+1) * $this->_limitN ) );

        if ($rowLimit > $this->_productsForUpdate + 1) {
          $rowLimit = $this->_productsForUpdate + 1;
        }
        if( $rowLimit == $this->_productsForUpdate ){
          $rowLimit = $this->_productsForUpdate + 1;
        }
        for ($row = 1; $row <= $rowLimit; ++ $row) {

          if ($row != 1 && ($this->_limit*$this->_limitN) >= $row) {
            continue;
          }
          $product = array();

          $spec_price_col_values = array(
            'id_specific_price' => array('id' => 'id_specific_price', 'values' => ''),
            'specific_price' => array('id' => 'specific_price', 'values' => ''),
            'specific_price_reduction' => array('id' => 'specific_price_reduction', 'values' => ''),
            'specific_price_reduction_type' => array('id' => 'specific_price_reduction_type', 'values' => ''),
            'specific_price_from' => array('id' => 'specific_price_from', 'values' => ''),
            'specific_price_to' => array('id' => 'specific_price_to', 'values' => ''),
            'specific_price_from_quantity' => array('id' => 'specific_price_from_quantity', 'values' => ''),
            'specific_price_id_group' => array('id' => 'specific_price_id_group', 'values' => ''),
          );

          for ($col = 0; $col < $highestColumnIndex; ++ $col) {
            $cell = $worksheet->getCellByColumnAndRow($col, $row);
            $val = $cell->getValue();

            if($row == 1){
              if(!$val){
                continue;
              }

              $fileFields[$col] = $val;
            } else{
              if(!isset($fileFields[$col])){
                continue;
              }

              if(!$checkHead && $col == 0){
                $mappingFields = $this->_checkHead($fileFields);
                $checkHead = true;
              }

              if (isset($this->all_specific_price_fields['id_specific_price']) && $this->all_specific_price_fields['id_specific_price']['to_update']) {
                foreach ($this->all_specific_price_fields as $spec_price_field) {
                  if (preg_match( '/^' . preg_quote($spec_price_field['name']) . '_[\d+]$/', $fileFields[$col]) &&
                    $spec_price_field['to_update'] &&
                    !empty($val)
                  ) {
                    $spec_price_col_values[$spec_price_field['id']]['values'] .= $val . ';';
                  }
                }
              }

              if(isset($mappingFields[$fileFields[$col]])){
                $product[$mappingFields[$fileFields[$col]]] = $val;
              }

              if( $this->_isFeature ){
                if( stripos( $fileFields[$col], "feature" ) !== false ){
                  $product['features'][$fileFields[$col]] = $val;
                }
              }
            }
          }


          foreach ($spec_price_col_values as $spec_price_col_value) {
            if (!empty($spec_price_col_value['values']) && $spec_price_col_value['values'] != '') {
              $product[$spec_price_col_value['id']] = trim($spec_price_col_value['values'], ';');
            }
          }


          if($product){
            if( !$product['id_product'] ){
              continue;
            }
            if($this->_separate){
              $this->_updateProductSeparate($product);
            } else{
              $this->_updateProduct($product);
            }
          }

        }
      }
    }


    private function _checkCombinationImage($idProduct)
    {
      $sql = 'SELECT count(*) as count
     FROM '._DB_PREFIX_.'updateproducts_images
     WHERE id_product = "'.$idProduct.'"
     AND id_shop = "'.$this->_idShop.'"
     ';

      $res = Db::getInstance()->executeS($sql);
      return (bool)$res[0]['count'];
    }

    private function _updateProductSeparate( $product )
    {
      $productObject = new Product( $product['id_product'], false );
      $address = null;
      if (is_object(Context::getContext()->cart) && Context::getContext()->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')} != null) {
        $address = Context::getContext()->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')};
      }

      $productObject->tax_rate = $productObject->getTaxesRate(new Address($address));
      $productObject->base_price = $productObject->price;
      $productObject->unit_price = ($productObject->unit_price_ratio != 0  ? $productObject->price / $productObject->unit_price_ratio : 0);
      $productObject->date_upd = date('Y-m-d H:i:s');

      foreach( $product as $field => $value ){

        if( $field == "categories_ids" ){
          $value = str_replace(" ", "", $value);
          $value = explode(";", $value);
          $value = array_unique($value);
          if( $value ){
            foreach( $value as $key => $categoryId ){
              if( !$categoryId ){
                unset($value[$key]);
                continue;
              }
              $value[$key] = (int)$categoryId;
            }
          }
          $productObject->updateCategories($value);
        }
        elseif( $field == "image_caption" &&  !isset($product['images']) ){

          if(isset($value) && $value){
            $image_caption = explode(";", $value);

            $id_product_attribute = trim($product['id_product_attribute']);

            if($id_product_attribute){
              $combination_obj = new Combination($id_product_attribute);
            }
            else{
              $combination_obj = $productObject;
            }


            $all_images = $combination_obj->getWsImages();

            if($all_images){
              foreach($all_images as $key => $value){
                if( isset( $image_caption[$key] ) && $image_caption[$key]  && $image_caption[$key] !== "" ){
                  $imgTmp = new Image($value['id'], $this->_idLang);
                  $imgTmp->legend = $image_caption[$key];
                  $imgTmp->update();
                }
              }
            }
          }
        }
        elseif( $field == "images" ){
          $ids = array();
          $id_product_attribute = trim($product['id_product_attribute']);

          if(!$productObject->getImages( $this->_idLang ) || $this->_removeImages && !$this->_checkCombinationImage($product['id_product'])){
            $this->_cover = true;
          }


          if(isset($value) && $value){
            $img_products = explode(";", $value);

            $this->_image = false;
            $image_caption = '';
            foreach($img_products as $key => $url_img){
              if(isset($product['image_caption']) && $product['image_caption']){
                $captions = explode(";", $product['image_caption']);
                $image_caption = $captions[$key];
              }

              $this->_productImages($product['id_product'], $url_img, $id_product_attribute, $image_caption);
            }

            if(isset($this->_ids_images[$id_product_attribute]) && $this->_ids_images[$id_product_attribute]){
              $ids = $this->_ids_images[$id_product_attribute];
            }

            if(isset($ids) && $ids && $id_product_attribute){
              $value = array(
                'new' => array(
                  $id_product_attribute => $ids,
                ),
              );
              Image::duplicateAttributeImageAssociations($value);
            }


            $settings = Tools::unserialize(Configuration::get('UPDATED_PRODUCTS_OLD_IMAGES', '' ,$this->_shopGroupId, $this->_idShop));
            $all = array_merge($settings, $this->_old_images);
            Configuration::updateValue('UPDATED_PRODUCTS_OLD_IMAGES', serialize($all), false, $this->_shopGroupId, $this->_idShop);
            $this->_old_images = array();

          }
        }
        elseif( $field == "combinations_price" ){
          $attribute =  trim($product['id_product_attribute']);
          $values = trim($product['combinations_price']);
          if( $attribute && $values != "" ){
            $combination = new Combination($attribute);
            $combination->price = number_format($values, 4, '.', '');
            if( ( $error = $combination->validateFields(false, true) ) !== true ){
              return $this->_createErrorsFile($error, $product['id_product']);
            }
            $combination->update();
          }
        }
        elseif( $field == "combinations_price_with_tax" ){
          $attribute =  trim($product['id_product_attribute']);
          $values = trim($product['combinations_price_with_tax']);
          if( $attribute && $values != '' ){
            $combination = new Combination($attribute);
            $taxPrice = (float)$values;
            if( $productObject->tax_rate ){
              $taxPrice = $taxPrice / (($productObject->tax_rate/100)+1);
            }
            $combination->price = number_format($taxPrice, 4, '.', '');
            if( ( $error = $combination->validateFields(false, true) ) !== true ){
              return $this->_createErrorsFile($error, $product['id_product']);
            }
            $combination->update();
          }
        }
        elseif( $field == "combinations_final_price" ){
          $current_product_price = (float)$productObject->price;
          $final_price = (float)$product['combinations_final_price'];

          $combination_impact_on_price = $final_price - $current_product_price;

          $combination = new Combination(trim($product['id_product_attribute']));
          $combination->price = str_replace(',','.', $combination_impact_on_price);
          $combination->price = number_format($combination_impact_on_price, 4, '.', '');
          $combination->update();
        }
        elseif( $field == "combinations_final_price_with_tax" ){
          $address = null;
          if (is_object(Context::getContext()->cart) && Context::getContext()->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')} != null) {
            $address = Context::getContext()->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')};
          }

          $tax_rate = $productObject->getTaxesRate(new Address($address));

          $current_product_price = (float)$productObject->price;
          $current_product_price = $current_product_price*(($tax_rate/100)+1);

          $final_price = (float)$product['combinations_final_price_with_tax'];
          $combination_impact_on_price = $final_price - $current_product_price;
          $combination_impact_on_price = $combination_impact_on_price/(($tax_rate/100)+1);

          $combination = new Combination(trim($product['id_product_attribute']));
          $combination->price = str_replace(',','.', $combination_impact_on_price);
          $combination->price = number_format($combination_impact_on_price, 4, '.', '');
          $combination->update();
        }
        elseif( $field == "combinations_wholesale_price" ){
          $attribute =  trim($product['id_product_attribute']);
          $values = trim($product['combinations_wholesale_price']);
          if( $attribute && $values != "" ){
            $combination = new Combination($attribute);
            $combination->wholesale_price = number_format($values, 4, '.', '');
            if( ( $error = $combination->validateFields(false, true) ) !== true ){
              return $this->_createErrorsFile($error, $product['id_product']);
            }
            $combination->update();
          }
        }
        elseif( $field == "combinations_unit_price_impact" ){
          $attribute =  trim($product['id_product_attribute']);
          $values = trim($product['combinations_unit_price_impact']);
          if( $attribute && $values != "" ){
            $combination = new Combination($attribute);
            $combination->unit_price_impact = number_format($values, 4, '.', '');
            if( ( $error = $combination->validateFields(false, true) ) !== true ){
              return $this->_createErrorsFile($error, $product['id_product']);
            }
            $combination->update();
          }
        }
        elseif( $field == "minimal_quantity" ){
          if( trim($product['id_product_attribute']) ){
            $attribute =  trim($product['id_product_attribute']);
            $values = trim($product['minimal_quantity']);
            if( $values != "" && $attribute ){
              $combination = new Combination($attribute);
              $combination->minimal_quantity = $values;
              if( ( $error = $combination->validateFields(false, true) ) !== true ){
                return $this->_createErrorsFile($error, $product['id_product']);
              }
              $combination->update();
            }
          }
          else{
            $productObject->minimal_quantity = $value;
          }
        }
        elseif( $field == "location" ){
          if( trim($product['id_product_attribute']) ){
            $attribute =  trim($product['id_product_attribute']);
            $values = trim($product['location']);
            if( $attribute ){
              if(method_exists(new StockAvailable(), 'setLocation')){
                StockAvailable::setLocation($product['id_product'], (string)$values, $this->_idShop, $attribute);
              }
              else{
                $combination = new Combination($attribute);
                $combination->location = (string)$values;
                if( ( $error = $combination->validateFields(false, true) ) !== true ){
                  return $this->_createErrorsFile($error, $product['id_product']);
                }
                $combination->update();
              }
            }
          }
          else{
            if( method_exists(new StockAvailable(), 'setLocation') ){
              StockAvailable::setLocation($product['id_product'], (string)$product['location'], $this->_idShop, 0);
            }
            else{
              $productObject->location = (string)$product['location'];
            }
          }
        }
        elseif( $field == "low_stock_threshold" ){
          if( trim($product['id_product_attribute']) ){
            $attribute =  trim($product['id_product_attribute']);
            $values = trim($product['low_stock_threshold']);
            if( $values != "" && $attribute ){
              $combination = new Combination($attribute);
              $combination->low_stock_threshold = $values;
              if( ( $error = $combination->validateFields(false, true) ) !== true ){
                return $this->_createErrorsFile($error, $product['id_product']);
              }
              $combination->update();
            }
          }
          else{
            $productObject->low_stock_threshold = $value;
          }
        }
        elseif( $field == "low_stock_alert" ){
          if( trim($product['id_product_attribute']) ){
            $attribute =  trim($product['id_product_attribute']);
            $values = trim($product['low_stock_alert']);
            if( $values != "" && $attribute ){
              $combination = new Combination($attribute);
              $combination->low_stock_alert = (string)$values == '0' ? 0 : 1;
              if( ( $error = $combination->validateFields(false, true) ) !== true ){
                return $this->_createErrorsFile($error, $product['id_product']);
              }
              $combination->update();
            }
          }
          else{
            $productObject->low_stock_alert = (string)$value == '0' ? 0 : 1;
          }
        }
        elseif( $field == "available_date" ){
          if( $product['available_date'] != '0000-00-00' ){
            if( trim($product['id_product_attribute']) ){
              $attribute =  trim($product['id_product_attribute']);
              $product['available_date'] = trim($product['available_date']);
              $product['available_date'] = strtotime($product['available_date']);
              $product['available_date'] = date('Y-m-d', $product['available_date']);

              if( $product['available_date'] != "" && $attribute ){
                $combination = new Combination($attribute);
                $combination->available_date = $product['available_date'];
                if( ( $error = $combination->validateFields(false, true) ) !== true ){
                  return $this->_createErrorsFile($error, $product['id_product']);
                }
                $combination->update();
              }
            }
            else{
              $productObject->available_date = $product['available_date'];
            }
          }
        }
        elseif( $field == "combinations_reference" ){
          $attribute =  trim($product['id_product_attribute']);
          $values = trim($product['combinations_reference']);
          if( $attribute && $values != ''){
            $combination = new Combination($attribute);
            $combination->reference = $values;
            if( ( $error = $combination->validateFields(false, true) ) !== true ){
              return $this->_createErrorsFile($error, $product['id_product']);
            }
            $combination->update();
          }


        }
        elseif( $field == "combinations_weight" ){
          $attribute =  trim($product['id_product_attribute']);
          $values = trim($product['combinations_weight']);
          if($attribute && $values != "" ){
            $combination = new Combination($attribute);
            $combination->weight = $values;
            if( ( $error = $combination->validateFields(false, true) ) !== true ){
              return $this->_createErrorsFile($error, $product['id_product']);
            }
            $combination->update();
          }
        }
        elseif( $field == "combinations_ecotax" ){
          $attribute =  trim($product['id_product_attribute']);
          $values = trim($product['combinations_ecotax']);
          if($attribute && $values != "" ){
            $combination = new Combination($attribute);
            $combination->ecotax = $values;
            if( ( $error = $combination->validateFields(false, true) ) !== true ){
              return $this->_createErrorsFile($error, $product['id_product']);
            }
            $combination->update();
          }
        }
        elseif( $field == "combinations_ean13" ){
          $attribute =  trim($product['id_product_attribute']);
          $values = trim($product['combinations_ean13']);
          if( $values && $attribute ){
            $combination = new Combination($attribute);
            $combination->ean13 = $values;
            if( ( $error = $combination->validateFields(false, true) ) !== true ){
              return $this->_createErrorsFile($error, $product['id_product']);
            }
            $combination->update();
          }
        }
        elseif( $field == "combinations_upc" ){
          $attribute =  trim($product['id_product_attribute']);
          $values = trim($product['combinations_upc']);
          if( $values != '' && $attribute ){
            $combination = new Combination($attribute);
            $combination->upc = $values;
            if( ( $error = $combination->validateFields(false, true) ) !== true ){
              return $this->_createErrorsFile($error, $product['id_product']);
            }
            $combination->update();
          }
        }
        elseif( $field == "tags" ){
          Db::getInstance()->delete('product_tag', 'id_product = '.(int)$product['id_product'] . ' AND id_lang=' . (int)$this->_idLang);
          Tag::updateTagCount();
          $tags = trim($value);
          if( $tags ){
            Tag::addTags( $this->_idLang, $product['id_product'], $tags );
          }
        }
        elseif( $field == "specific_price_from_quantity" ){
          $specific_price = $product['id_specific_price'];
          $specific_price = str_replace(" ", "", $specific_price);
          $specific_price = explode(";", $specific_price);
          $values = $product['specific_price_from_quantity'];
          $values = str_replace(" ", "", $values);
          if( $values != "" ){
            $values = explode(";", $values);
            foreach( $specific_price as $key => $specific_id ){
              $specificObject = new SpecificPrice($specific_id);
              if( !isset($values[$key]) || !$specific_id){
                continue;
              }
              $specificObject->from_quantity = $values[$key];
              if( ( $error = $specificObject->validateFields(false, true) ) !== true ){
                return $this->_createErrorsFile($error, $product['id_product']);
              }
              $specificObject->update();
            }
          }
        }
        elseif( $field == "specific_price_reduction" ){
          $specific_price = $product['id_specific_price'];
          $specific_price = str_replace(" ", "", $specific_price);
          $specific_price = explode(";", $specific_price);
          $values = $product['specific_price_reduction'];
          $values = str_replace(" ", "", $values);
          if( $values != "" ){
            $values = explode(";", $values);
            foreach( $specific_price as $key => $specific_id ){
              $specificObject = new SpecificPrice($specific_id);
              if( !isset($values[$key]) || !$specific_id){
                continue;
              }
              $specificObject->reduction = $values[$key];

              if( ( $error = $specificObject->validateFields(false, true) ) !== true ){
                return $this->_createErrorsFile($error, $product['id_product']);
              }
              $specificObject->update();
            }
          }
        }
        elseif( $field == "specific_price" ){
          $specific_price = $product['id_specific_price'];
          $specific_price = str_replace(" ", "", $specific_price);
          $specific_price = explode(";", $specific_price);
          $values = $product['specific_price'];
          $values = str_replace(" ", "", $values);
          if( $values != "" ){
            $values = explode(";", $values);
            foreach( $specific_price as $key => $specific_id ){
              $specificObject = new SpecificPrice($specific_id);
              if( !isset($values[$key]) || !$specific_id){
                continue;
              }
              $specificObject->price = $values[$key];

              if( ( $error = $specificObject->validateFields(false, true) ) !== true ){
                return $this->_createErrorsFile($error, $product['id_product']);
              }
              $specificObject->update();
            }
          }
        }
        elseif( $field == "specific_price_reduction_type" ){
          $specific_price = $product['id_specific_price'];
          $specific_price = str_replace(" ", "", $specific_price);
          $specific_price = explode(";", $specific_price);
          $values = $product['specific_price_reduction_type'];
          $values = str_replace(" ", "", $values);
          if( $values != "" ){
            $values = explode(";", $values);
            foreach( $specific_price as $key => $specific_id ){
              $specificObject = new SpecificPrice($specific_id);
              if( !isset($values[$key]) || !$specific_id){
                continue;
              }
              $specificObject->reduction_type = $values[$key];
              if( ( $error = $specificObject->validateFields(false, true) ) !== true ){
                return $this->_createErrorsFile($error, $product['id_product']);
              }
              $specificObject->update();
            }
          }
        }
        elseif( $field == "specific_price_from" ){
          $specific_price = $product['id_specific_price'];
          $specific_price = str_replace(" ", "", $specific_price);
          $specific_price = explode(";", $specific_price);
          $values = $product['specific_price_from'];
          $values = str_replace("; ", ";", $values);
          $values = str_replace(" ;", ";", $values);
          if( $values != "" ){
            $values = explode(";", $values);
            foreach( $specific_price as $key => $specific_id ){
              $specificObject = new SpecificPrice($specific_id);
              if( !isset($values[$key]) || !$specific_id){
                continue;
              }
              $specificObject->from = $values[$key];
              if( ( $error = $specificObject->validateFields(false, true) ) !== true ){
                return $this->_createErrorsFile($error, $product['id_product']);
              }
              $specificObject->update();
            }
          }

        }
        elseif( $field == "specific_price_to" ){
          $specific_price = $product['id_specific_price'];
          $specific_price = str_replace(" ", "", $specific_price);
          $specific_price = explode(";", $specific_price);
          $values = $product['specific_price_to'];
          $values = str_replace("; ", ";", $values);
          $values = str_replace(" ;", ";", $values);
          if( $values != "" ){
            $values = explode(";", $values);
            foreach( $specific_price as $key => $specific_id ){
              $specificObject = new SpecificPrice($specific_id);
              if( !isset($values[$key]) || !$specific_id){
                continue;
              }
              $specificObject->to = $values[$key];
              if( ( $error = $specificObject->validateFields(false, true) ) !== true ){
                return $this->_createErrorsFile($error, $product['id_product']);
              }
              $specificObject->update();
            }
          }
        }
        elseif( $field == "specific_price_id_group" ){
          $specific_price = $product['id_specific_price'];
          $specific_price = str_replace(" ", "", $specific_price);
          $specific_price = explode(";", $specific_price);

          $values = $product['specific_price_id_group'];
          $values = str_replace(" ", "", $values);
          if( $values != "" ){
            $values = explode(";", $values);
            foreach( $specific_price as $key => $specific_id ){
              $specificObject = new SpecificPrice($specific_id);
              if( !isset($values[$key]) || !$specific_id){
                continue;
              }
              $specificObject->id_group = $values[$key];

              if( ( $error = $specificObject->validateFields(false, true) ) !== true ){
                return $this->_createErrorsFile($error, $product['id_product']);
              }
              $specificObject->update();
            }
          }

        }
        elseif( strpos($field, 'FEATURE_') !== false){
          $featureId = explode('_FEATURE_',$field);
          $featureId = $featureId[0];

          $this->deleteFeatureFromProduct($product['id_product'], $featureId);

          $featureValue = explode(',', $value);
          foreach($featureValue as $key=>$fValue){
            $fValue = trim($fValue);
            if(!$fValue){
              continue;
            }

            $featureValueId = FeatureValue::addFeatureValueImport( $featureId, $fValue, false, $this->_idLang );
            if( Module::getInstanceByName('pm_multiplefeatures') ){
              $this->_addFeatureProductImport( $product['id_product'], $featureId, $featureValueId, $key );
            }
            else{
              $productObject->addFeatureProductImport( $product['id_product'], $featureId, $featureValueId );
            }
          }
        }
        elseif( $field == "features" ){
          $productObject->deleteProductFeatures();
          foreach( $value as $feature => $featureValue ){
            $featureValue = explode(',', $featureValue);
            foreach( $featureValue as $key=>$fValue ){
              $fValue = trim($fValue);
              if( !$fValue ){
                continue;
              }
              $featureId = explode('_FEATURE_',$feature);
              $featureId = $featureId[0];
              $featureValueId = FeatureValue::addFeatureValueImport( $featureId, $fValue, false, $this->_idLang );
              if( Module::getInstanceByName('pm_multiplefeatures') ){
                $this->_addFeatureProductImport( $product['id_product'], $featureId, $featureValueId, $key );
              }
              else{
                $productObject->addFeatureProductImport( $product['id_product'], $featureId, $featureValueId );
              }
            }
          }
        }
        elseif( $field == "quantity"){
          $attribute =  trim($product['id_product_attribute']);
          if( !$attribute ){
            $attribute = 0;
          }

          StockAvailable::setQuantity($product['id_product'], (int)$attribute, $value);
        }
        elseif( $field == "redirect_type" ){
          $productObject->redirect_type = (string)$value;
        }
        elseif( $field == "id_carriers" ){
          $carriers = explode(';', $value);
          if( $carriers[0] ){
            $carrierReferences = array();
            foreach( $carriers as $carrier ){
              $carrierObject = new Carrier($carrier);
              $carrierReferences[] = $carrierObject->id_reference;
            }
            $productObject->setCarriers($carrierReferences);
          }
        }
        elseif ($field == 'suppliers_reference') {
          $this->updateProductSupplierPropertySeparate($product, 'suppliers_reference');
        } elseif ($field == 'suppliers_price') {
          $this->updateProductSupplierPropertySeparate($product, 'suppliers_price');
        } elseif ($field == 'suppliers_price_currency') {
          $this->updateProductSupplierPropertySeparate($product, 'suppliers_price_currency');
        }
        elseif( $field == "supplier_reference" ){
          $attribute =  trim($product['id_product_attribute']);
          if( !$attribute ){
            $attribute = 0;
          }
          $sId = ProductSupplier::getIdByProductAndSupplier($product['id_product'], $attribute, $productObject->id_supplier);
          if( $sId ){
            $sReference = new ProductSupplier($sId);
          }
          else{
            $sReference = new ProductSupplier();
            $sReference->id_product = $product['id_product'];
            $sReference->id_product_attribute = $attribute;
            $sReference->id_supplier = $productObject->id_supplier;
          }
          $sReference->product_supplier_reference = (string)$value;
          if( ( $error = $sReference->validateFields(false, true) ) !== true ){
            return $this->_createErrorsFile($error, $product['id_product']);
          }
          $sReference->save();
        }
        elseif( $field == "supplier_price" ){
          $attribute =  trim($product['id_product_attribute']);
          if( !$attribute ){
            $attribute = 0;
          }

          if( $value != '' ){
            $sId = ProductSupplier::getIdByProductAndSupplier($product['id_product'], $attribute, $productObject->id_supplier);
            if( $sId ){
              $sReference = new ProductSupplier($sId);
            }
            else{
              $sReference = new ProductSupplier();
              $sReference->id_product = $product['id_product'];
              $sReference->id_product_attribute = $attribute;
              $sReference->id_supplier = $productObject->id_supplier;
            }
            $sReference->product_supplier_price_te = number_format($value, 4, '.', '');
            if( ( $error = $sReference->validateFields(false, true) ) !== true ){
              return $this->_createErrorsFile($error, $product['id_product']);
            }
            $sReference->save();
          }
        }
        elseif( $field == "supplier_price_currency" ){
          $attribute =  trim($product['id_product_attribute']);
          if( !$attribute ){
            $attribute = 0;
          }
          if( $value ){
            $sId = ProductSupplier::getIdByProductAndSupplier($product['id_product'], $attribute, $productObject->id_supplier);
            if( $sId ){
              $sReference = new ProductSupplier($sId);
            }
            else{
              $sReference = new ProductSupplier();
              $sReference->id_product = $product['id_product'];
              $sReference->id_product_attribute = $attribute;
              $sReference->id_supplier = $productObject->id_supplier;
            }
            $sCurrencyId = Currency::getIdByIsoCode(trim($value));
            $sReference->id_currency = $sCurrencyId;
            if( ( $error = $sReference->validateFields(false, true) ) !== true ){
              return $this->_createErrorsFile($error, $product['id_product']);
            }
            $sReference->save();
          }
        }
        elseif( $field == "meta_description" ){
          $currentValue = $productObject->meta_description;
          $currentValue[$this->_idLang] = (string)$value;
          $productObject->meta_description = $currentValue;
        }
        elseif( $field == "meta_keywords" ){
          $currentValue = $productObject->meta_keywords;
          $currentValue[$this->_idLang] = (string)$value;
          $productObject->meta_keywords = $currentValue;
        }
        elseif( $field == "meta_title" ){
          $currentValue = $productObject->meta_title;
          $currentValue[$this->_idLang] = (string)$value;
          $productObject->meta_title = $currentValue;
        }
        elseif( $field == "link_rewrite" ){
          $currentValue = $productObject->link_rewrite;
          $currentValue[$this->_idLang] = (string)$value;
          $productObject->link_rewrite = $currentValue;
        }
        elseif( $field == "name" ){
          $currentValue = $productObject->name;
          $currentValue[$this->_idLang] = (string)$value;
          $productObject->name = $currentValue;
        }
        elseif( $field == "description" ){
          $currentValue = $productObject->description;
          $currentValue[$this->_idLang] = (string)$value;
          $productObject->description = $currentValue;
        }
        elseif( $field == "description_short" ){
          $currentValue = $productObject->description_short;
          $currentValue[$this->_idLang] = (string)$value;
          $productObject->description_short = $currentValue;
        }
        elseif( $field == "available_now" ){
          $currentValue = $productObject->available_now;
          $currentValue[$this->_idLang] = (string)$value;
          $productObject->available_now = $currentValue;
        }
        elseif( $field == "out_of_stock" ){
          if(isset( $value ) &&  $value !== ''){
            if( $value == 1 ){
              StockAvailable::setProductOutOfStock($product['id_product'], (int)1);
            }
            elseif( $value == 2 ){
              StockAvailable::setProductOutOfStock($product['id_product'], (int)2);
            }
            else{
              StockAvailable::setProductOutOfStock($product['id_product'], (int)0);
            }
          }
        }
        elseif( $field == "id_product_accessories" ){
          $accessories = array();
          $ids = explode(";", $value);
          foreach ($ids as $accessory) {
            $accessories[]['id'] = $accessory;
          }
          $productObject->setWsAccessories($accessories);
        }
        elseif( $field == "available_later" ){
          $currentValue = $productObject->available_later;
          $currentValue[$this->_idLang] = (string)$value;
          $productObject->available_later = $currentValue;
        }
        elseif( $field == "suppliers_ids" ){
          //$productObject->deleteFromSupplier();
          $values = explode(";", $value);
          foreach ($values as $id) {
            $id_product_supplier = ProductSupplier::getIdByProductAndSupplier($product['id_product'], 0, $id);
            if ($id_product_supplier) {
              continue;
            }

            $supplierExists = Supplier::supplierExists($id);
            if($supplierExists){
              $product_supplier = new ProductSupplier();
              $product_supplier->id_product = $product['id_product'];
              $product_supplier->id_product_attribute = 0;
              $product_supplier->id_supplier = $id;
              $product_supplier->save();
            }
          }
        }
        elseif( $field == "id_supplier" ){
          $supplierExists = Supplier::supplierExists($value);
          if($supplierExists){
            $productObject->id_supplier = $value;
            $product_supplier = new ProductSupplier();
            $product_supplier->id_product = $product['id_product'];
            $product_supplier->id_product_attribute = 0;
            $product_supplier->id_supplier = $value;
            $product_supplier->save();
          }
        }
        elseif( $field == "base_price" ){
          $productObject->price = number_format($value, 4, '.', '');
        }
        elseif( $field == "base_price_with_tax" ){
          $taxPrice = (float)$value;
          if( $productObject->tax_rate ){
            $taxPrice = $taxPrice / (($productObject->tax_rate/100)+1);
          }
          $productObject->price = number_format($taxPrice, 4, '.', '');
        }
        elseif( $field == "unit_price" ){
          $productObject->unit_price = number_format($value, 4, '.', '');
        }
        elseif( $field == "wholesale_price" ){
          $productObject->wholesale_price = number_format($value, 4, '.', '');
        }
        elseif( $field == "cache_default_attribute" ){
          $productObject->deleteDefaultAttributes();
          $productObject->setDefaultAttribute((int)$value);
        }
        else{
          if( $field != "id_product" && $field != "id_product_attribute" && $field != "id_specific_price" ){
            $productObject->$field = $value;
          }
        }
      }

      if( ( $error = $productObject->validateFieldsLang(false, true) ) !== true ){
        return $this->_createErrorsFile($error, $product['id_product']);
      }

      if( ( $error = $productObject->validateFields(false, true) ) !== true ){
        return $this->_createErrorsFile($error, $product['id_product']);
      }

      $productObject->update();
      $this->_updatedProducts++;
      Configuration::updateValue('UPDATED_PRODUCTS_CURRENT_COUNT', $this->_updatedProducts, false, $this->_shopGroupId, $this->_idShop);
      return true;
    }

    private function _getExistsImageId( $imgUrl, $idProduct )
    {
      $sql = 'SELECT id_image
     FROM '._DB_PREFIX_.'updateproducts_images
     WHERE id_product = "'.$idProduct.'"
     AND image_url = "'.$imgUrl.'"
     AND id_shop = "'.$this->_idShop.'"
     ';

      $res = Db::getInstance()->executeS($sql);
      if( isset( $res[0] ) && $res[0] ){
        return $res[0]['id_image'];
      }

      return false;
    }

    private function _addCombinationImage( $imgUrl, $idProduct, $idImage )
    {

      $data = array(
        'image_url'  => $imgUrl,
        'id_product' => $idProduct,
        'id_image'   => $idImage,
        'id_shop'    => $this->_idShop,
      );

      Db::getInstance()->insert('updateproducts_images', $data);
    }

    private function _productImages($productId, $url_img, $combination = false, $image_caption){

      $url_img = trim($url_img);
      $url_img = str_replace(' ','%20', $url_img);

      if(getimagesize($url_img)){

        if($combination){
          $obj = new Combination($combination);
        }
        else{
          $obj = new Product($productId);
        }

        $obj_prod = new Product($productId);

        if($obj_prod->getWsImages() && !$this->_image  &&  !$this->_checkCombinationImage($productId)){
          foreach ($obj_prod->getWsImages() as $image){
            $this->_old_images[] = $image['id'];
          }
          $this->_image = true;
        }


        if( $combination ){
          if( ($idImage = $this->_getExistsImageId($url_img, $productId)) ){
            $this->_ids_images[$combination][]= $idImage;
            return true;
          }
        }

        $path_parts_prod = pathinfo($url_img);
        $name_img = $path_parts_prod['basename'];
        $newpath_prod =  dirname(__FILE__).'/upload/';
        if (PHP_VERSION_ID < 50300)
          clearstatcache();
        else
          clearstatcache(true, $newpath_prod.$name_img);

        if ( copy($url_img, $newpath_prod.$name_img) ) {
          $image = new Image();
          $image->id_product = $productId;
          if($image_caption){
            $image->legend = $image_caption;
          }

          if( $this->_cover  &&  !$this->_checkCombinationImage($productId) ){

            $prod = new Product($productId);
            $cover = $prod->getCoverWs();

            if($cover){
              $cover_obj = new Image($cover);
              $cover_obj->cover = 0;
              $cover_obj->save();
            }

            $image->cover = 1;
            $this->_cover = false;
          }
          if( ( $error = $image->validateFields(false, true) ) !== true ){
            $this->_createErrorsFile($error,'Product ID - ' . $productId);
            return false;
          }
          if( ( $error = $image->validateFieldsLang(false, true) ) !== true ){
            $this->_createErrorsFile($error,'Product ID - ' . $productId);
            return false;
          }

          $image->add();

          if($image->id ){
            $this->_addCombinationImage($url_img, $productId, $image->id);
          }

          $this->_ids_images[$combination][]= $image->id;

          $new_path = $image->getPathForCreation();

          if( !ImageManager::resize($newpath_prod.$name_img, $new_path.'.'.$image->image_format, null, null, 'jpg', false) ){
            return false;
          }

          $imagesTypes = ImageType::getImagesTypes('products');
          foreach ($imagesTypes as $imageType)
          {
            ImageManager::resize($newpath_prod.$name_img, $new_path.'-'. Tools::stripslashes($imageType['name']).'.'.$image->image_format, $imageType['width'], $imageType['height'], $image->image_format);
          }

          $image->update();
          if( file_exists($newpath_prod.$name_img) ){
            unlink($newpath_prod.$name_img);
          }
        }
      }
      else{
        if( $url_img ){
          $this->_createErrorsFile('"Image is not available for uploading, Image Url: ' . $url_img . '"' ,'Product ID - ' . $productId);
        }
      }

    }



    private function _updateProduct( $product )
    {
      $productObject = new Product(  $product['id_product'], false );
      $address = null;
      if (is_object(Context::getContext()->cart) && Context::getContext()->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')} != null) {
        $address = Context::getContext()->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')};
      }

      $productObject->tax_rate = $productObject->getTaxesRate(new Address($address));
      $productObject->base_price = $productObject->price;
      $productObject->unit_price = ($productObject->unit_price_ratio != 0  ? $productObject->price / $productObject->unit_price_ratio : 0);
      $productObject->date_upd = date('Y-m-d H:i:s');

      foreach( $product as $field => $value ){

        if( $field == "categories_ids" ){
          $value = str_replace(" ", "", $value);
          $value = explode(";", $value);
          $value = array_unique($value);
          if( $value ){
            foreach( $value as $key => $categoryId ){
              if( !$categoryId ){
                unset($value[$key]);
                continue;
              }
              $value[$key] = (int)$categoryId;
            }
          }

          $productObject->updateCategories($value);
        }
        elseif( $field == "image_caption" ){

          if(isset($value) && $value){
            $image_caption = explode(";", $value);
            $all_images = $productObject->getImages( $this->_idLang );


            if($all_images){
              foreach($all_images as $key => $value){
                if( isset( $image_caption[$key] ) && $image_caption[$key]  && $image_caption[$key] !== "" ){
                  $imgTmp = new Image($value['id_image'], $this->_idLang);
                  $imgTmp->legend = $image_caption[$key];
                  $imgTmp->update();
                }
              }
            }
          }
        }

        elseif( $field == "combinations_price" ){
          $attributes = $product['id_product_attribute'];
          $attributes = str_replace(" ", "", $attributes);
          $attributes = explode(";", $attributes);

          $values = $product['combinations_price'];
          $values = str_replace(" ", "", $values);
          if($attributes && $values != "" ){
            $values = explode(";", $values);
            foreach( $attributes as $key => $attribute ){
              $combination = new Combination($attribute);
              if( !isset($values[$key]) || !$attribute){
                continue;
              }
              $combination->price = number_format($values[$key], 4, '.', '');

              if( ( $error = $combination->validateFields(false, true) ) !== true ){
                return $this->_createErrorsFile($error, $product['id_product']);
              }
              $combination->update();
            }
          }
        }
        elseif( $field == "combinations_price_with_tax" ){
          $attributes = $product['id_product_attribute'];
          $attributes = str_replace(" ", "", $attributes);
          $attributes = explode(";", $attributes);

          $values = $product['combinations_price_with_tax'];
          $values = str_replace(" ", "", $values);
          if( $attributes && $values != '' ){
            $values = explode(";", $values);
            foreach( $attributes as $key => $attribute ){
              $combination = new Combination($attribute);
              if( !isset($values[$key]) || !$attribute){
                continue;
              }
              $taxPrice = (float)$values[$key];
              if( $productObject->tax_rate ){
                $taxPrice = $taxPrice / (($productObject->tax_rate/100)+1);
              }
              $combination->price = number_format($taxPrice, 4, '.', '');
              if( ( $error = $combination->validateFields(false, true) ) !== true ){
                return $this->_createErrorsFile($error, $product['id_product']);
              }
              $combination->update();
            }
          }

        }
        elseif( $field == "combinations_final_price" ){
          $combination_ids = str_replace(" ", "", $product['id_product_attribute']);
          $combination_ids = explode(";", $combination_ids);

          $combination_final_prices = str_replace(" ", "", $product['combinations_final_price']);
          $combination_final_prices = explode(";", $combination_final_prices);

          $is_data_valid = !empty($combination_ids) && !empty($combination_final_prices) && (count($combination_ids) === count($combination_final_prices));

          if ($is_data_valid) {
            foreach ($combination_ids as $combination_final_price_key => $combination_id) {
              $current_product_price = (float)$productObject->price;
              $final_price = (float)$combination_final_prices[$combination_final_price_key];

              $combination_impact_on_price = $final_price - $current_product_price;

              $combination = new Combination(trim($combination_id));
              $combination->price = str_replace(',','.', $combination_impact_on_price);
              $combination->price = number_format($combination_impact_on_price, 4, '.', '');
              $combination->update();
            }
          }
        }
        elseif( $field == "combinations_final_price_with_tax" ){
          $combination_ids = str_replace(" ", "", $product['id_product_attribute']);
          $combination_ids = explode(";", $combination_ids);

          $combination_final_prices = str_replace(" ", "", $product['combinations_final_price_with_tax']);
          $combination_final_prices = explode(";", $combination_final_prices);

          $address = null;
          if (is_object(Context::getContext()->cart) && Context::getContext()->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')} != null) {
            $address = Context::getContext()->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')};
          }

          $tax_rate = $productObject->getTaxesRate(new Address($address));

          $is_data_valid = !empty($combination_ids) && !empty($combination_final_prices) && (count($combination_ids) === count($combination_final_prices));

          if ($is_data_valid) {
            foreach ($combination_ids as $combination_final_price_key => $combination_id) {
              $current_product_price = (float)$productObject->price;
              $current_product_price = $current_product_price*(($tax_rate/100)+1);

              $final_price = (float)$combination_final_prices[$combination_final_price_key];

              $combination_impact_on_price = $final_price - $current_product_price;
              $combination_impact_on_price = $combination_impact_on_price/(($tax_rate/100)+1);

              $combination = new Combination(trim($combination_id));
              $combination->price = str_replace(',','.', $combination_impact_on_price);
              $combination->price = number_format($combination_impact_on_price, 4, '.', '');
              $combination->update();
            }
          }
        }
        elseif( $field == "combinations_wholesale_price" ){
          $attributes = $product['id_product_attribute'];
          $attributes = str_replace(" ", "", $attributes);
          $attributes = explode(";", $attributes);
          $values = $product['combinations_wholesale_price'];
          $values = str_replace(" ", "", $values);
          if($attributes && $values != "" ){
            $values = explode(";", $values);
            foreach( $attributes as $key => $attribute ){
              $combination = new Combination($attribute);
              if( !isset($values[$key]) || !$attribute){
                continue;
              }
              $combination->wholesale_price = number_format($values[$key], 4, '.', '');
              if( ( $error = $combination->validateFields(false, true) ) !== true ){
                return $this->_createErrorsFile($error, $product['id_product']);
              }
              $combination->update();
            }
          }

        }
        elseif( $field == "combinations_unit_price_impact" ){
          $attributes = $product['id_product_attribute'];
          $attributes = str_replace(" ", "", $attributes);
          $attributes = explode(";", $attributes);

          $values = $product['combinations_unit_price_impact'];
          $values = str_replace(" ", "", $values);
          if($attributes && $values != "" ){
            $values = explode(";", $values);
            foreach( $attributes as $key => $attribute ){
              $combination = new Combination($attribute);
              if( !isset($values[$key]) || !$attribute){
                continue;
              }
              $combination->unit_price_impact = number_format($values[$key], 4, '.', '');
              if( ( $error = $combination->validateFields(false, true) ) !== true ){
                return $this->_createErrorsFile($error, $product['id_product']);
              }
              $combination->update();
            }
          }

        }
        elseif( $field == "minimal_quantity" ){
          if( trim($product['id_product_attribute']) ){
            $attributes = $product['id_product_attribute'];
            $attributes = str_replace(" ", "", $attributes);
            $attributes = explode(";", $attributes);

            $values = $product['minimal_quantity'];
            $values = str_replace(" ", "", $values);
            if( $values != "" && $attributes ){
              $values = explode(";", $values);
              foreach( $attributes as $key => $attribute ){
                $combination = new Combination($attribute);
                if( !isset($values[$key]) || !$attribute){
                  continue;
                }
                $combination->minimal_quantity = $values[$key];
                if( ( $error = $combination->validateFields(false, true) ) !== true ){
                  return $this->_createErrorsFile($error, $product['id_product']);
                }
                $combination->update();
              }
            }
          }
          else{
            $productObject->minimal_quantity = $value;
          }
        }
        elseif( $field == "location" ){
          if( trim($product['id_product_attribute']) ){
            $attributes = $product['id_product_attribute'];
            $attributes = str_replace(" ", "", $attributes);
            $attributes = explode(";", $attributes);

            $values = $product['location'];
            $values = str_replace(" ", "", $values);
            if( $values != "" && $attributes ){
              $values = explode(";", $values);
              foreach( $attributes as $key => $attribute ){
                if( !isset($values[$key]) || !$attribute){
                  continue;
                }
                if( method_exists(new StockAvailable(), 'setLocation') ){
                  StockAvailable::setLocation($product['id_product'], (string)$values[$key], $this->_idShop, $attribute);
                }
                else{
                  $combination = new Combination($attribute);
                  $combination->location = (string)$values[$key];
                  if( ( $error = $combination->validateFields(false, true) ) !== true ){
                    return $this->_createErrorsFile($error, $product['id_product']);
                  }
                  $combination->update();
                }
              }
            }
          }
          else{
            if( method_exists(new StockAvailable(), 'setLocation') ){
              StockAvailable::setLocation($product['id_product'], (string)$value, $this->_idShop, 0);
            }
            else{
              $productObject->location = (string)$value;
            }
          }
        }
        elseif( $field == "low_stock_threshold" ){
          if( trim($product['id_product_attribute']) ){
            $attributes = $product['id_product_attribute'];
            $attributes = str_replace(" ", "", $attributes);
            $attributes = explode(";", $attributes);

            $values = $product['low_stock_threshold'];
            $values = str_replace(" ", "", $values);
            if( $values != "" && $attributes ){
              $values = explode(";", $values);
              foreach( $attributes as $key => $attribute ){
                $combination = new Combination($attribute);
                if( !isset($values[$key]) || !$attribute){
                  continue;
                }
                $combination->low_stock_threshold = $values[$key];
                if( ( $error = $combination->validateFields(false, true) ) !== true ){
                  return $this->_createErrorsFile($error, $product['id_product']);
                }
                $combination->update();
              }
            }
          }
          else{
            $productObject->low_stock_threshold = $value;
          }
        }
        elseif( $field == "low_stock_alert" ){
          if( trim($product['id_product_attribute']) ){
            $attributes = $product['id_product_attribute'];
            $attributes = str_replace(" ", "", $attributes);
            $attributes = explode(";", $attributes);

            $values = $product['low_stock_alert'];
            $values = str_replace(" ", "", $values);
            if( $values != "" && $attributes ){
              $values = explode(";", $values);
              foreach( $attributes as $key => $attribute ){
                $combination = new Combination($attribute);
                if( !isset($values[$key]) || !$attribute){
                  continue;
                }
                $combination->low_stock_alert = $values[$key];
                if( ( $error = $combination->validateFields(false, true) ) !== true ){
                  return $this->_createErrorsFile($error, $product['id_product']);
                }
                $combination->update();
              }
            }
          }
          else{
            $productObject->low_stock_alert = $value;
          }
        }
        elseif( $field == "available_date" ){
          if( trim($product['id_product_attribute']) ){
            $attributes = $product['id_product_attribute'];
            $attributes = str_replace(" ", "", $attributes);
            $attributes = explode(";", $attributes);

            $values = $product['available_date'];
            $values = str_replace(" ", "", $values);
            if( $values != "" && $attributes ){
              $values = explode(";", $values);
              foreach( $attributes as $key => $attribute ){
                $combination = new Combination($attribute);
                if( !isset($values[$key]) || !$attribute){
                  continue;
                }

                $values[$key] = trim($values[$key]);
                if( $values[$key] == '0000-00-00' ){
                  continue;
                }
                $values[$key] = strtotime($values[$key]);
                $values[$key] = date('Y-m-d', $values[$key]);

                $combination->available_date = $values[$key];

                if (!Validate::isDateFormat($values[$key])) {
                  return $this->_createErrorsFile('Available Date - date format is not valid',$product['id_product']);
                }
                if( ( $error = $combination->validateFields(false, true) ) !== true ){
                  return $this->_createErrorsFile($error, $product['id_product']);
                }


                $combination->update();
              }
            }
          }
          else{
            $value = trim($value);
            if( $value != '0000-00-00' ){
              $value = strtotime($value);
              $value = date('Y-m-d', $value);
              if (!Validate::isDateFormat($value)) {
                return $this->_createErrorsFile('Available Date - date format is not valid',$product['id_product']);
              }
              $productObject->available_date = $value;
            }
          }
        }
        elseif( $field == "combinations_reference" ){
          $attributes = $product['id_product_attribute'];
          $attributes = str_replace(" ", "", $attributes);
          $attributes = explode(";", $attributes);

          $values = $product['combinations_reference'];
          if( $attributes != '' ){
            $values = str_replace(" ", "", $values);
            $values = explode(";", $values);
            foreach( $attributes as $key => $attribute ){
              if( !isset($values[$key]) || !$attribute ){
                continue;
              }
              $combination = new Combination($attribute);

              $combination->reference = $values[$key];
              if( ( $error = $combination->validateFields(false, true) ) !== true ){
                return $this->_createErrorsFile($error, $product['id_product']);
              }
              $combination->update();
            }
          }
        }
        elseif( $field == "combinations_weight" ){
          $attributes = $product['id_product_attribute'];
          $attributes = str_replace(" ", "", $attributes);
          $attributes = explode(";", $attributes);

          $values = $product['combinations_weight'];
          $values = str_replace(" ", "", $values);
          $values = explode(";", $values);
          foreach( $attributes as $key => $attribute ){
            $combination = new Combination($attribute);
            if( !isset($values[$key]) || !$attribute ){
              continue;
            }
            $combination->weight = $values[$key];
            if( ( $error = $combination->validateFields(false, true) ) !== true ){
              return $this->_createErrorsFile($error, $product['id_product']);
            }
            $combination->update();
          }
        }
        elseif( $field == "combinations_ecotax" ){
          $attributes = $product['id_product_attribute'];
          $attributes = str_replace(" ", "", $attributes);
          $attributes = explode(";", $attributes);

          $values = $product['combinations_ecotax'];
          $values = str_replace(" ", "", $values);
          $values = explode(";", $values);
          foreach( $attributes as $key => $attribute ){
            $combination = new Combination($attribute);
            if( !isset($values[$key]) || !$attribute ){
              continue;
            }
            $combination->ecotax = $values[$key];
            if( ( $error = $combination->validateFields(false, true) ) !== true ){
              return $this->_createErrorsFile($error, $product['id_product']);
            }
            $combination->update();
          }
        }
        elseif( $field == "combinations_ean13" ){
          $attributes = $product['id_product_attribute'];
          $attributes = str_replace(" ", "", $attributes);
          $attributes = explode(";", $attributes);

          $values = $product['combinations_ean13'];
          if( $values && $attributes ){
            $values = str_replace(" ", "", $values);
            $values = explode(";", $values);
            foreach( $attributes as $key => $attribute ){

              $combination = new Combination($attribute);
              if( !isset($values[$key]) || !$attribute ){
                continue;
              }
              $combination->ean13 = $values[$key];
              if( ( $error = $combination->validateFields(false, true) ) !== true ){
                return $this->_createErrorsFile($error, $product['id_product']);
              }
              $combination->update();
            }
          }
        }
        elseif( $field == "combinations_upc" ){
          $attributes = $product['id_product_attribute'];
          $attributes = str_replace(" ", "", $attributes);
          $attributes = explode(";", $attributes);

          $values = $product['combinations_upc'];
          if( $values != '' && $attributes ){
            $values = str_replace(" ", "", $values);
            $values = explode(";", $values);
            foreach( $attributes as $key => $attribute ){
              $combination = new Combination($attribute);
              if( !isset($values[$key]) || !$attribute ){
                continue;
              }
              $combination->upc = $values[$key];
              if( ( $error = $combination->validateFields(false, true) ) !== true ){
                return $this->_createErrorsFile($error, $product['id_product']);
              }
              $combination->update();
            }
          }

        }
        elseif( $field == "tags" ){
          Db::getInstance()->delete('product_tag', 'id_product = '.(int)$product['id_product'] . ' AND id_lang=' . (int)$this->_idLang);
          Tag::updateTagCount();
          $tags = trim($value);
          if( $tags ){
            Tag::addTags( $this->_idLang, $product['id_product'], $tags );
          }
        }
        elseif( $field == "specific_price_from_quantity" ){
          $specific_price = $product['id_specific_price'];
          $specific_price = str_replace(" ", "", $specific_price);
          $specific_price = explode(";", $specific_price);

          $values = $product['specific_price_from_quantity'];
          $values = str_replace(" ", "", $values);
          if( $values != "" ){
            $values = explode(";", $values);
            foreach( $specific_price as $key => $specific_id ){
              $specificObject = new SpecificPrice($specific_id);
              if( !isset($values[$key]) || !$specific_id){
                continue;
              }
              $specificObject->from_quantity = $values[$key];

              if( ( $error = $specificObject->validateFields(false, true) ) !== true ){
                return $this->_createErrorsFile($error, $product['id_product']);
              }
              $specificObject->update();
            }
          }

        }
        elseif( $field == "specific_price_reduction" ){
          $specific_price = $product['id_specific_price'];
          $specific_price = str_replace(" ", "", $specific_price);
          $specific_price = explode(";", $specific_price);

          $values = $product['specific_price_reduction'];
          $values = str_replace(" ", "", $values);
          if( $values != "" ){
            $values = explode(";", $values);
            foreach( $specific_price as $key => $specific_id ){
              $specificObject = new SpecificPrice($specific_id);
              if( !isset($values[$key]) || !$specific_id){
                continue;
              }
              $specificObject->reduction = $values[$key];
              if( ( $error = $specificObject->validateFields(false, true) ) !== true ){
                return $this->_createErrorsFile($error, $product['id_product']);
              }
              $specificObject->update();
            }
          }

        }
        elseif( $field == "specific_price" ){
          $specific_price = $product['id_specific_price'];
          $specific_price = str_replace(" ", "", $specific_price);
          $specific_price = explode(";", $specific_price);

          $values = $product['specific_price'];
          $values = str_replace(" ", "", $values);
          if( $values != "" ){
            $values = explode(";", $values);
            foreach( $specific_price as $key => $specific_id ){
              $specificObject = new SpecificPrice($specific_id);
              if( !isset($values[$key]) || !$specific_id){
                continue;
              }
              $specificObject->price = $values[$key];
              if( ( $error = $specificObject->validateFields(false, true) ) !== true ){
                return $this->_createErrorsFile($error, $product['id_product']);
              }
              $specificObject->update();
            }
          }

        }
        elseif( $field == "specific_price_reduction_type" ){
          $specific_price = $product['id_specific_price'];
          $specific_price = str_replace(" ", "", $specific_price);
          $specific_price = explode(";", $specific_price);

          $values = $product['specific_price_reduction_type'];
          $values = str_replace(" ", "", $values);
          if( $values != "" ){
            $values = explode(";", $values);
            foreach( $specific_price as $key => $specific_id ){
              $specificObject = new SpecificPrice($specific_id);
              if( !isset($values[$key]) || !$specific_id){
                continue;
              }
              $specificObject->reduction_type = $values[$key];
              if( ( $error = $specificObject->validateFields(false, true) ) !== true ){
                return $this->_createErrorsFile($error, $product['id_product']);
              }
              $specificObject->update();
            }
          }
        }
        elseif( $field == "specific_price_from" ){
          $specific_price = $product['id_specific_price'];
          $specific_price = str_replace(" ", "", $specific_price);
          $specific_price = explode(";", $specific_price);

          $values = $product['specific_price_from'];
          $values = str_replace("; ", ";", $values);
          $values = str_replace(" ;", ";", $values);
          if( $values != "" ){
            $values = explode(";", $values);
            foreach( $specific_price as $key => $specific_id ){
              $specificObject = new SpecificPrice($specific_id);
              if( !isset($values[$key]) || !$specific_id){
                continue;
              }
              $specificObject->from = $values[$key];
              if( ( $error = $specificObject->validateFields(false, true) ) !== true ){
                return $this->_createErrorsFile($error, $product['id_product']);
              }
              $specificObject->update();
            }
          }
        }
        elseif( $field == "specific_price_to" ){
          $specific_price = $product['id_specific_price'];
          $specific_price = str_replace(" ", "", $specific_price);
          $specific_price = explode(";", $specific_price);
          $values = $product['specific_price_to'];
          $values = str_replace("; ", ";", $values);
          $values = str_replace(" ;", ";", $values);
          if( $values != "" ){
            $values = explode(";", $values);

            foreach( $specific_price as $key => $specific_id ){
              $specificObject = new SpecificPrice($specific_id);
              if( !isset($values[$key]) || !$specific_id){
                continue;
              }
              $specificObject->to = $values[$key];
              if( ( $error = $specificObject->validateFields(false, true) ) !== true ){
                return $this->_createErrorsFile($error, $product['id_product']);
              }
              $specificObject->update();
            }
          }
        }
        elseif( $field == "specific_price_id_group" ){
          $specific_price = $product['id_specific_price'];
          $specific_price = str_replace(" ", "", $specific_price);
          $specific_price = explode(";", $specific_price);

          $values = $product['specific_price_id_group'];
          $values = str_replace(" ", "", $values);
          if( $values != "" ){
            $values = explode(";", $values);
            foreach( $specific_price as $key => $specific_id ){
              $specificObject = new SpecificPrice($specific_id);
              if( !isset($values[$key]) || !$specific_id){
                continue;
              }
              $specificObject->id_group = $values[$key];

              if( ( $error = $specificObject->validateFields(false, true) ) !== true ){
                return $this->_createErrorsFile($error, $product['id_product']);
              }
              $specificObject->update();
            }
          }

        }
        elseif( strpos($field, 'FEATURE_') !== false){
          $featureId = explode('_FEATURE_',$field);
          $featureId = $featureId[0];

          $this->deleteFeatureFromProduct($product['id_product'], $featureId);

          $featureValue = explode(',', $value);
          foreach($featureValue as $key=>$fValue){
            $fValue = trim($fValue);
            if(!$fValue){
              continue;
            }

            $featureValueId = FeatureValue::addFeatureValueImport( $featureId, $fValue, false, $this->_idLang );
            if( Module::getInstanceByName('pm_multiplefeatures') ){
              $this->_addFeatureProductImport( $product['id_product'], $featureId, $featureValueId, $key );
            }
            else{
              $productObject->addFeatureProductImport( $product['id_product'], $featureId, $featureValueId );
            }
          }
        }
        elseif($field == "features"){
          $productObject->deleteProductFeatures();
          foreach($value as $feature => $featureValue){
            $featureValue = explode(',', $featureValue);
            foreach($featureValue as $key=>$fValue){
              $fValue = trim($fValue);
              if(!$fValue){
                continue;
              }
              $featureId = explode('_FEATURE_',$feature);
              $featureId = $featureId[0];
              $featureValueId = FeatureValue::addFeatureValueImport( $featureId, $fValue, false, $this->_idLang );
              if( Module::getInstanceByName('pm_multiplefeatures') ){
                $this->_addFeatureProductImport( $product['id_product'], $featureId, $featureValueId, $key );
              }
              else{
                $productObject->addFeatureProductImport( $product['id_product'], $featureId, $featureValueId );
              }
            }
          }
        }

        elseif( $field == "quantity" ){
          if( trim($product['id_product_attribute']) ){
            $attributes = $product['id_product_attribute'];
            $attributes = str_replace(" ", "", $attributes);
            $attributes = explode(";", $attributes);

            $values = $product['quantity'];
            $values = str_replace(" ", "", $values);
            if( $values != "" ){
              $values = explode(";", $values);
              foreach( $attributes as $key => $attribute ){
                if( !isset($values[$key]) || !$attribute){
                  continue;
                }
                StockAvailable::setQuantity($product['id_product'], (int)$attribute, (int)$values[$key]);
              }
            }
          }
          else{
            StockAvailable::setQuantity($product['id_product'], 0, $value);
          }
        }
        elseif( $field == "redirect_type" ){
          $productObject->redirect_type = (string)$value;
        }
        elseif( $field == "id_carriers" ){
          $carriers = explode(';', $value);
          if( $carriers[0] ){
            $carrierReferences = array();
            foreach( $carriers as $carrier ){
              $carrierObject = new Carrier($carrier);
              $carrierReferences[] = $carrierObject->id_reference;
            }
            $productObject->setCarriers($carrierReferences);
          }
        } elseif ($field == 'suppliers_reference') {
          $this->updateProductSupplierProperty($product, 'suppliers_reference');
        } elseif ($field == 'suppliers_price') {
          $this->updateProductSupplierProperty($product, 'suppliers_price');
        } elseif ($field == 'suppliers_price_currency') {
          $this->updateProductSupplierProperty($product, 'suppliers_price_currency');
        } elseif( $field == "supplier_reference" ){

          if( trim($product['id_product_attribute']) ){
            $attributes = $product['id_product_attribute'];
            $attributes = str_replace(" ", "", $attributes);
            $attributes = explode(";", $attributes);
            $values = $product['supplier_reference'];
            $values = str_replace(" ", "", $values);
            if( $values != "" ){
              $values = explode(";", $values);
              foreach( $attributes as $key => $attribute ){
                if( !isset($values[$key]) || !$attribute){
                  continue;
                }
                $sId = ProductSupplier::getIdByProductAndSupplier($product['id_product'], $attribute, $productObject->id_supplier);
                if( $sId ){
                  $sReference = new ProductSupplier($sId);
                }
                else{
                  $sReference = new ProductSupplier();
                  $sReference->id_product = $product['id_product'];
                  $sReference->id_product_attribute = $attribute;
                  $sReference->id_supplier = $productObject->id_supplier;
                }
                $sReference->product_supplier_reference = (string)$values[$key];
                if( ( $error = $sReference->validateFields(false, true) ) !== true ){
                  return $this->_createErrorsFile($error, $product['id_product']);
                }
                $sReference->save();
              }
            }
          }
          else{
            $sId = ProductSupplier::getIdByProductAndSupplier($product['id_product'], 0, $productObject->id_supplier);
            if( $sId ){
              $sReference = new ProductSupplier($sId);
            }
            else{
              $sReference = new ProductSupplier();
              $sReference->id_product = $product['id_product'];
              $sReference->id_product_attribute = 0;
              $sReference->id_supplier = $productObject->id_supplier;
            }
            $sReference->product_supplier_reference = (string)$value;
            if( ( $error = $sReference->validateFields(false, true) ) !== true ){
              return $this->_createErrorsFile($error, $product['id_product']);
            }
            $sReference->save();
          }
        }
        elseif( $field == "supplier_price" ){

          if( trim($product['id_product_attribute']) ){
            $attributes = $product['id_product_attribute'];
            $attributes = str_replace(" ", "", $attributes);
            $attributes = explode(";", $attributes);

            $values = $product['supplier_price'];
            $values = str_replace(" ", "", $values);
            if( $values != "" ){
              $values = explode(";", $values);
              foreach( $attributes as $key => $attribute ){
                if( !isset($values[$key]) || !$attribute){
                  continue;
                }
                $sId = ProductSupplier::getIdByProductAndSupplier($product['id_product'], $attribute, $productObject->id_supplier);
                if( $sId ){
                  $sReference = new ProductSupplier($sId);
                }
                else{
                  $sReference = new ProductSupplier();
                  $sReference->id_product = $product['id_product'];
                  $sReference->id_product_attribute = $attribute;
                  $sReference->id_supplier = $productObject->id_supplier;
                }
                $sReference->product_supplier_price_te = number_format($values[$key], 4, '.', '');
                if( ( $error = $sReference->validateFields(false, true) ) !== true ){
                  return $this->_createErrorsFile($error, $product['id_product']);
                }
                $sReference->save();
              }
            }
          }
          else{
            $sId = ProductSupplier::getIdByProductAndSupplier($product['id_product'], 0, $productObject->id_supplier);
            if( $sId ){
              $sReference = new ProductSupplier($sId);
            }
            else{
              $sReference = new ProductSupplier();
              $sReference->id_product = $product['id_product'];
              $sReference->id_product_attribute = 0;
              $sReference->id_supplier = $productObject->id_supplier;
            }
            $sReference->product_supplier_price_te = number_format($value, 4, '.', '');
            if( ( $error = $sReference->validateFields(false, true) ) !== true ){
              return $this->_createErrorsFile($error, $product['id_product']);
            }
            $sReference->save();
          }
        }
        elseif( $field == "supplier_price_currency" ){

          if( trim($product['id_product_attribute']) ){
            $attributes = $product['id_product_attribute'];
            $attributes = str_replace(" ", "", $attributes);
            $attributes = explode(";", $attributes);

            $values = $product['supplier_price_currency'];
            $values = str_replace(" ", "", $values);
            if( $values != "" ){
              $values = explode(";", $values);
              foreach( $attributes as $key => $attribute ){
                if( !isset($values[$key]) || !$attribute){
                  continue;
                }
                $sId = ProductSupplier::getIdByProductAndSupplier($product['id_product'], $attribute, $productObject->id_supplier, true);
                if( $sId ){
                  $sReference = new ProductSupplier($sId);
                }
                else{
                  $sReference = new ProductSupplier();
                  $sReference->id_product = $product['id_product'];
                  $sReference->id_product_attribute = $attribute;
                  $sReference->id_supplier = $productObject->id_supplier;
                }
                $sCurrencyId = Currency::getIdByIsoCode(trim($values[$key]));
                $sReference->id_currency = $sCurrencyId;
                if( ( $error = $sReference->validateFields(false, true) ) !== true ){
                  return $this->_createErrorsFile($error, $product['id_product']);
                }
                $sReference->save();
              }
            }
          }
          else{
            $sId = ProductSupplier::getIdByProductAndSupplier($product['id_product'], 0, $productObject->id_supplier);
            if( $sId ){
              $sReference = new ProductSupplier($sId);
            }
            else{
              $sReference = new ProductSupplier();
              $sReference->id_product = $product['id_product'];
              $sReference->id_product_attribute = 0;
              $sReference->id_supplier = $productObject->id_supplier;
            }
            $sCurrencyId = Currency::getIdByIsoCode(trim($value));
            $sReference->id_currency = $sCurrencyId;
            if( ( $error = $sReference->validateFields(false, true) ) !== true ){
              return $this->_createErrorsFile($error, $product['id_product']);
            }
            $sReference->update();
          }
        }
        elseif( $field == "meta_description" ){
          $currentValue = $productObject->meta_description;
          $currentValue[$this->_idLang] = (string)$value;
          $productObject->meta_description = $currentValue;
        }
        elseif( $field == "meta_keywords" ){
          $currentValue = $productObject->meta_keywords;
          $currentValue[$this->_idLang] = (string)$value;
          $productObject->meta_keywords = $currentValue;
        }
        elseif( $field == "meta_title" ){
          $currentValue = $productObject->meta_title;
          $currentValue[$this->_idLang] = (string)$value;
          $productObject->meta_title = $currentValue;
        }
        elseif( $field == "link_rewrite" ){
          $currentValue = $productObject->link_rewrite;
          $currentValue[$this->_idLang] = (string)$value;
          $productObject->link_rewrite = $currentValue;
        }
        elseif( $field == "name" ){
          $currentValue = $productObject->name;
          $currentValue[$this->_idLang] = (string)$value;
          $productObject->name = $currentValue;
        }
        elseif( $field == "description" ){
          $currentValue = $productObject->description;
          $currentValue[$this->_idLang] = (string)$value;
          $productObject->description = $currentValue;
        }
        elseif( $field == "description_short" ){
          $currentValue = $productObject->description_short;
          $currentValue[$this->_idLang] = (string)$value;
          $productObject->description_short = $currentValue;
        }
        elseif( $field == "available_now" ){
          $currentValue = $productObject->available_now;
          $currentValue[$this->_idLang] = (string)$value;
          $productObject->available_now = $currentValue;
        }
        elseif( $field == "out_of_stock" ){
          if(isset( $value ) &&  $value !== ''){
            if( $value == 1 ){
              StockAvailable::setProductOutOfStock((int)$product['id_product'], (int)1);
            }
            elseif( $value == 2 ){
              StockAvailable::setProductOutOfStock((int)$product['id_product'], (int)2);
            }
            else{
              StockAvailable::setProductOutOfStock((int)$product['id_product'], (int)0);
            }
          }
        }
        elseif( $field == "id_product_accessories" ){
          $accessories = array();
          $ids = explode(";", $value);
          foreach ($ids as $accessory) {
            $accessories[]['id'] = $accessory;
          }
          $productObject->setWsAccessories($accessories);
        }
        elseif( $field == "available_later" ){
          $currentValue = $productObject->available_later;
          $currentValue[$this->_idLang] = (string)$value;
          $productObject->available_later = $currentValue;
        }
        elseif( $field == "suppliers_ids" ){
          //$productObject->deleteFromSupplier();
          $values = explode(";", $value);
          foreach ($values as $id) {
            $id_product_supplier = ProductSupplier::getIdByProductAndSupplier($product['id_product'], 0, $id);
            if ($id_product_supplier) {
              continue;
            }

            $supplierExists = Supplier::supplierExists($id);
            if($supplierExists){
              $product_supplier = new ProductSupplier();
              $product_supplier->id_product = $product['id_product'];
              $product_supplier->id_product_attribute = 0;
              $product_supplier->id_supplier = $id;
              $product_supplier->save();
            }
          }
        }
        elseif( $field == "id_supplier" ){
          $supplierExists = Supplier::supplierExists($value);
          if($supplierExists){
            $productObject->id_supplier = $value;
            $product_supplier = new ProductSupplier();
            $product_supplier->id_product = $product['id_product'];
            $product_supplier->id_product_attribute = 0;
            $product_supplier->id_supplier = $value;
            $product_supplier->save();
          }
        }
        elseif( $field == "base_price" ){
          $productObject->price = number_format($value, 4, '.', '');
        }
        elseif( $field == "base_price_with_tax" ){
          $taxPrice = (float)$value;
          if( $productObject->tax_rate ){
            $taxPrice = $taxPrice / (($productObject->tax_rate/100)+1);
          }
          $productObject->price = number_format($taxPrice, 4, '.', '');
        }
        elseif( $field == "unit_price" ){
          $productObject->unit_price = number_format($value, 4, '.', '');
        }
        elseif( $field == "wholesale_price" ){
          $productObject->wholesale_price = number_format($value, 4, '.', '');
        }
        elseif( $field == "cache_default_attribute" ){
          $productObject->deleteDefaultAttributes();
          $productObject->setDefaultAttribute((int)$value);
        }
        else{
          if( $field != "id_product" && $field != "id_product_attribute" && $field != "id_specific_price" ){
            $productObject->$field = $value;
          }
        }
      }

      if( ( $error = $productObject->validateFieldsLang(false, true) ) !== true ){
        return $this->_createErrorsFile($error, $product['id_product']);
      }

      if( ( $error = $productObject->validateFields(false, true) ) !== true ){
        return $this->_createErrorsFile($error, $product['id_product']);
      }

      $productObject->update();
      $this->_updatedProducts++;
      Configuration::updateValue('UPDATED_PRODUCTS_CURRENT_COUNT', $this->_updatedProducts, false, $this->_shopGroupId, $this->_idShop);
      return true;
    }

    private function _addFeatureProductImport($id_product, $id_feature, $id_feature_value, $position)
    {
      return Db::getInstance()->execute('
			INSERT INTO `'._DB_PREFIX_.'feature_product` (`id_feature`, `id_product`, `id_feature_value`, `position`)
			VALUES ('.(int)$id_feature.', '.(int)$id_product.', '.(int)$id_feature_value.', '.(int)$position.')
			ON DUPLICATE KEY UPDATE `id_feature_value` = '.(int)$id_feature_value
      );
    }

    private function _copyFile()
    {
      if ( !isset($_FILES['file']) )
      {
        throw new Exception( Module::getInstanceByName('updateproducts')->l('Please select file for update!') );
      }

      $file_type = Tools::substr($_FILES['file']['name'], strrpos($_FILES['file']['name'], '.')+1);

      if( $file_type != $this->_format && $this->_format == 'xlsx' ){
        throw new Exception( Module::getInstanceByName('updateproducts')->l('File must have XLSX extension!') );
      }

      if( $file_type != $this->_format && $this->_format == 'csv' ){
        throw new Exception( Module::getInstanceByName('updateproducts')->l('File must have CSV extension!') );
      }

      if (!Tools::copy($_FILES['file']['tmp_name'],  dirname(__FILE__).'/files/import_products.'. $this->_format)){
        throw new Exception(Module::getInstanceByName('updateproducts')->l('An error occurred while uploading file!', 'import') );
      }

      $this->_PHPExcelFactory = PHPExcel_IOFactory::load("files/import_products." . $this->_format );
    }


    private function _createErrorsFile($error, $nameProduct)
    {

      $write_fd = fopen('error/error_logs.csv', 'a+');
      if (@$write_fd !== false){
        fwrite($write_fd, $nameProduct . ',' . $error . "\r\n");
      }
      fclose($write_fd);

      if( !Configuration::get('GOMAKOIL_IMPORT_IMAGES_ERROR_LOG', null, $this->_shopGroupId, $this->_idShop) ){
        Configuration::updateValue('GOMAKOIL_IMPORT_IMAGES_ERROR_LOG', (int)1, false, $this->_shopGroupId, $this->_idShop);
      }

      return false;
    }

    /**
     * Delete features
     *
     */
    public function deleteFeatureFromProduct($id_product, $id_feature)
    {
      $all_shops = Context::getContext()->shop->getContext() == Shop::CONTEXT_ALL ? true : false;

      // List products features
      $features = Db::getInstance()->executeS('
            SELECT p.*, f.*
            FROM `'._DB_PREFIX_.'feature_product` as p
            LEFT JOIN `'._DB_PREFIX_.'feature_value` as f ON (f.`id_feature_value` = p.`id_feature_value`)
            '.(!$all_shops ? 'LEFT JOIN `'._DB_PREFIX_.'feature_shop` fs ON (f.`id_feature` = fs.`id_feature`)' : null).'
            WHERE `id_product` = '.(int)$id_product
        .(!$all_shops ? ' AND fs.`id_shop` = '.(int)Context::getContext()->shop->id : '') .
        ' AND p.`id_feature` = ' . (int)$id_feature
      );

      foreach ($features as $tab) {
        // Delete product custom features
        if ($tab['custom']) {
          Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'feature_value` WHERE `id_feature_value` = '.(int)$tab['id_feature_value']);
          Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'feature_value_lang` WHERE `id_feature_value` = '.(int)$tab['id_feature_value']);
        }
      }
      // Delete product features
      $result = Db::getInstance()->execute('
    		DELETE `'._DB_PREFIX_.'feature_product` FROM `'._DB_PREFIX_.'feature_product`
    		WHERE `id_product` = '.(int)$id_product.
        ' AND `id_feature` = ' . (int)$id_feature
      );

      SpecificPriceRule::applyAllRules(array((int)$id_product));
      return ($result);
    }

    private function updateProductSupplierProperty($product, $property_name)
    {
      $product_suppliers_properties_for_update = $product[$property_name];
      $product_suppliers_ids_container = $this->_model->getProductSuppliersID($product['id_product']);
      $product_suppliers_ids = $product_suppliers_ids_container[0]['suppliers_ids'];
      $product_suppliers_ids = explode(';', $product_suppliers_ids);
      $combinations = $product['id_product_attribute'];
      $combinations = str_replace(" ", "", $combinations);
      $combinations = explode(";", $combinations);

      if (empty($product_suppliers_properties_for_update)) {
        return false;
      }

      $properties_grouped_by_suppliers = explode(',', $product_suppliers_properties_for_update);

      foreach ($properties_grouped_by_suppliers as $property_key => $properties) {
        if (empty($product_suppliers_ids[$property_key])) {
          continue;
        }

        $properties_ready_for_update = explode(';', $properties);

        foreach ($combinations as $key => $id_product_attribute) {
          $product_supplier_id = ProductSupplier::getIdByProductAndSupplier($product['id_product'], $id_product_attribute, $product_suppliers_ids[$property_key]);

          if($product_supplier_id){
            $product_supplier = new ProductSupplier($product_supplier_id);
          } else{
            $product_supplier = new ProductSupplier();
            $product_supplier->id_product = $product['id_product'];
            $product_supplier->id_product_attribute = $id_product_attribute;
            $product_supplier->id_supplier = $product_suppliers_ids[$property_key];
          }

          switch ($property_name) {
            case 'suppliers_reference':
              $product_supplier->product_supplier_reference = (string)$properties_ready_for_update[$key];
              break;
            case 'suppliers_price':
              $product_supplier->product_supplier_price_te = $properties_ready_for_update[$key];
              break;
            case 'suppliers_price_currency':
              $product_supplier->id_currency = Currency::getIdByIsoCode($properties_ready_for_update[$key]);
              break;
          }

          $product_supplier->save();
        }
      }

      return true;
    }

    private function updateProductSupplierPropertySeparate($product, $property_name)
    {
      $product_suppliers_properties_for_update = $product[$property_name];
      $product_suppliers_ids_container = $this->_model->getProductSuppliersID($product['id_product']);
      $product_suppliers_ids = $product_suppliers_ids_container[0]['suppliers_ids'];
      $product_suppliers_ids = explode(';', $product_suppliers_ids);

      if (empty($product_suppliers_properties_for_update)) {
        return false;
      }

      $properties_grouped_by_suppliers = explode(',', $product_suppliers_properties_for_update);

      foreach ($properties_grouped_by_suppliers as $property_key => $property) {
        if (empty($product_suppliers_ids[$property_key])) {
          continue;
        }

        $product_supplier_id = ProductSupplier::getIdByProductAndSupplier($product['id_product'], $product['id_product_attribute'], $product_suppliers_ids[$property_key]);

        if ($product_supplier_id) {
          $product_supplier = new ProductSupplier($product_supplier_id);
        } else {
          $product_supplier = new ProductSupplier();
          $product_supplier->id_product = $product['id_product'];
          $product_supplier->id_product_attribute = $product['id_product_attribute'];
          $product_supplier->id_supplier = $product_suppliers_ids[$property_key];
        }

        switch ($property_name) {
          case 'suppliers_reference':
            $product_supplier->product_supplier_reference = (string)$property;
            break;
          case 'suppliers_price':
            $product_supplier->product_supplier_price_te = $property;
            break;
          case 'suppliers_price_currency':
            $product_supplier->id_currency = Currency::getIdByIsoCode($property);
            break;
        }

        $product_supplier->save();
      }

      return true;
    }
  }