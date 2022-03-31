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

if (!defined('_PS_VERSION_'))
	exit;
function upgrade_module_1_1_1($object)
{
    $sqls = array();
    if(!$object->checkCreatedColumn('ets_superspeed_blog_slide_image','image'))
    {
        $sqls[]='ALTER TABLE `'._DB_PREFIX_.'ets_superspeed_blog_slide_image` ADD `image` VARCHAR(222) NOT NULL AFTER `type_image`';
        $sqls[] ='ALTER TABLE `'._DB_PREFIX_.'ets_superspeed_blog_slide_image` DROP PRIMARY KEY, ADD PRIMARY KEY (`id_slide`, `type_image`, `image`) USING BTREE';
    }
    if(!$object->checkCreatedColumn('ets_superspeed_blog_gallery_image','image'))
    {
        $sqls[]='ALTER TABLE `'._DB_PREFIX_.'ets_superspeed_blog_gallery_image` ADD `image` VARCHAR(222) NOT NULL AFTER `type_image`, ADD `thumb` VARCHAR(222) NOT NULL AFTER `image`';
        $sqls[] ='ALTER TABLE `'._DB_PREFIX_.'ets_superspeed_blog_gallery_image` DROP PRIMARY KEY, ADD PRIMARY KEY (`id_gallery`, `type_image`, `image`,`thumb`) USING BTREE';
    }
    if(!$object->checkCreatedColumn('ets_superspeed_blog_category_image','image'))
    {
        $sqls[]='ALTER TABLE `'._DB_PREFIX_.'ets_superspeed_blog_category_image` ADD `image` VARCHAR(222) NOT NULL AFTER `type_image`, ADD `thumb` VARCHAR(222) NOT NULL AFTER `image`';
        $sqls[] ='ALTER TABLE `'._DB_PREFIX_.'ets_superspeed_blog_category_image` DROP PRIMARY KEY, ADD PRIMARY KEY (`id_category`, `type_image`, `image`,`thumb`) USING BTREE';
    }    
    if(!$object->checkCreatedColumn('ets_superspeed_blog_post_image','image'))
    {
        $sqls[] ='ALTER TABLE `'._DB_PREFIX_.'ets_superspeed_blog_post_image` ADD `image` VARCHAR(222) NOT NULL AFTER `type_image`, ADD `thumb` VARCHAR(222) NOT NULL AFTER `image`';
        $sqls[] ='ALTER TABLE `'._DB_PREFIX_.'ets_superspeed_blog_post_image` DROP PRIMARY KEY, ADD PRIMARY KEY (`id_post`, `type_image`, `image`,`thumb`) USING BTREE';
    }    
    if($sqls)
    {
        foreach($sqls as $sql)
            Db::getInstance()->execute($sql);
    }
    Configuration::updateValue('ETS_TIME_AJAX_CHECK_SPEED',5);
    return true;
}