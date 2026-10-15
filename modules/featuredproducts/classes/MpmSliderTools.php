<?php
if (!defined('_PS_VERSION_')){
  exit;
}
class MpmSliderTools {

  public static function getContext()
  {
      return Context::getContext();
  }

  public static function getModuleInstance()
  {
      return Module::getInstanceByName('featuredproducts');
  }

  public static function getShopId()
  {
      return (int) self::getContext()->shop->id;
  }

  public static function getLangId()
  {
      return (int) self::getContext()->language->id;
  }
  public static function getSmartyInstance()
  {
      return self::getContext()->smarty;
  }
  public static function getShopGroupId()
  {
      if(isset(self::getContext()->shop->id_shop_group) ){
          return (int) self::getContext()->shop->id_shop_group;
      } elseif( isset(self::getContext()->shop->id_group_shop) ){
          return (int) self::getContext()->shop->id_group_shop;
      } else {
          return (int) 0;
      }
  }
    
}