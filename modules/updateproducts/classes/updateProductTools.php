<?php

if (!defined('_PS_VERSION_')){
    exit;
}
class updateProductTools{

  public static function getContext()
  {
      return Context::getContext();
  }

  public static function getModuleInstance()
  {
      return Module::getInstanceByName('updateproducts');
  }

  public static function getShopId()
  {
      return self::getContext()->shop->id;
  }

  public static function getLangId()
  {
      return self::getContext()->language->id;
  }
  public static function getSmartyInstance()
  {
      return self::getContext()->smarty;
  }
  public static function getShopGroupId()
  {
      if(isset(self::getContext()->shop->id_shop_group) ){
          return self::getContext()->shop->id_shop_group;
      } elseif( isset(self::getContext()->shop->id_group_shop) ){
          return self::getContext()->shop->id_group_shop;
      } else {
          return 0;
      }
  }

  public static function jsonEncode($data, $options = 0, $depth = 512)
  {
    return json_encode($data, $options, $depth);
  }

  public static function jsonDecode($data, $assoc = true, $depth = 512, $options = 0)
  {
    if ( ($unserialized = Tools::unSerialize($data)) !== false ){
      return $unserialized;
    }

    return json_decode($data, $assoc, $depth, $options);
  }
}