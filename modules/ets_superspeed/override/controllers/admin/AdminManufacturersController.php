<?php
/**
 * 2007-2020 ETS-Soft
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses. 
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 * 
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please contact us for extra customization service at an affordable price
 *
 *  @author ETS-Soft <etssoft.jsc@gmail.com>
 *  @copyright  2007-2021 ETS-Soft
 *  @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

class AdminManufacturersController extends AdminManufacturersControllerCore
{
    protected function afterImageUpload()
    {
        parent::afterImageUpload();
        if(Module::isInstalled('ets_superspeed') && Module::isEnabled('ets_superspeed') && $ets_superspeed= Module::getInstanceByName('ets_superspeed'))
        {
            $id_manufacturer = (int)Tools::getValue('id_manufacturer');
            $path = _PS_MANU_IMG_DIR_.$id_manufacturer;
            $quality=(int)Configuration::get('ETS_SPEED_QUALITY_OPTIMIZE_NEW') >0 ? (int)Configuration::get('ETS_SPEED_QUALITY_OPTIMIZE_NEW'):90;
            if(isset($_FILES) && count($_FILES) && Configuration::get('ETS_SPEED_OPTIMIZE_NEW_IMAGE') && Configuration::get('ETS_SPEED_OPTIMIZE_NEW_IMAGE_MANUFACTURER_TYPE') && file_exists($path.'.jpg'))
            {
                $types= Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'image_type` WHERE manufacturers=1 AND  name IN ("'.implode('","',array_map('pSQL',explode(',',Configuration::get('ETS_SPEED_OPTIMIZE_NEW_IMAGE_MANUFACTURER_TYPE')))).'")');
                if($types)
                {
                    foreach($types as $type)
                    {
                        if($size_old = $ets_superspeed->createImage($path,$type))
                        {
                            if($ets_superspeed->checkOptimizeImageResmush())
                                $url_image= $ets_superspeed->getLinkTable('manufacturer').$id_manufacturer.'-'.$type['name'].'.jpg';
                            else
                                $url_image=null;
                            $compress = $ets_superspeed->compress($path,$type,$quality,$url_image,0);
                            while($compress===false)
                                $compress = $ets_superspeed->compress($path,$type,$quality,$url_image,0);
                            Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'ets_superspeed_manufacturer_image` WHERE id_manufacturer="'.(int)$id_manufacturer.'" AND type_image="'.pSQL($type['name']).'"');
                            Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'ets_superspeed_manufacturer_image` (id_manufacturer,type_image,quality,size_old,size_new,optimize_type) VALUES("'.(int)$id_manufacturer.'","'.pSQL($type['name']).'","'.(int)$quality.'","'.(float)$size_old.'","'.(float)$compress['file_size'].'","'.pSQL($compress['optimize_type']).'")');
                        }
                        
                    }
                }
            }
        }
        
    }
}