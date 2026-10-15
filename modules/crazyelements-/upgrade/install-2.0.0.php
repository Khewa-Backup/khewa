<?php
if (!defined('_PS_VERSION_')) {
	exit;
}

function upgrade_module_2_0_0($object)
{
	$object->registerHook( 'DisplayOverrideTemplate' );
	$id_tab   = (int) Tab::getIdFromClassName( 'AdminCrazyPrdlayouts' );
	$id_parent = (int) Tab::getIdFromClassName( 'AdminCrazyEditor' );


	if ( ! $id_tab ) {
		$tab             = new Tab();
		$tab->active     = 1;
		$tab->class_name = 'AdminCrazyPrdlayouts';
		$tab->name       = array();
		foreach ( Language::getLanguages( true ) as $lang ) {
			$tab->name[ $lang['id_lang'] ] = 'Product Layout Builder';
		}
		$tab->id_parent = $id_parent;
		$tab->module    = $object->name;

		$tab->add();
	}

	$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'crazyprdlayouts`(
		`id_crazyprdlayouts` int(11) NOT NULL auto_increment,
		`active` int(11) DEFAULT NULL,
		`content_type` LONGTEXT DEFAULT NULL,
		`modules_list` varchar(150) DEFAULT NULL,
		`module_hook_list` varchar(150) DEFAULT NULL,
		`product_page` varchar(150) DEFAULT NULL,
		`specific_product` varchar(150) DEFAULT NULL,
		`specific_product_catg` varchar(150) DEFAULT NULL,
		`position` int(11) DEFAULT NULL,
		PRIMARY KEY (`id_crazyprdlayouts`)
	  ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';
	  
	  $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'crazyprdlayouts_lang` (
		`id_crazyprdlayouts` int(11) NOT NULL,
		`id_lang` int(11) NOT NULL,
		`title` varchar(500) DEFAULT NULL,
		PRIMARY KEY (`id_crazyprdlayouts`,`id_lang`)
	  ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';
	  
	  $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'crazyprdlayouts_shop` (
		`id_crazyprdlayouts_shop`  int(11) NOT NULL auto_increment,
		`id_crazyprdlayouts`  int(11) NOT NULL,
		`id_shop` int(11) NOT NULL,
		KEY(`id_crazyprdlayouts_shop`),
		PRIMARY KEY (`id_crazyprdlayouts`,`id_shop`)
	  ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';
	  

	
	if(count($sql) >= 3){
		foreach ( $sql as $query ) {
			if ( Db::getInstance()->execute( $query ) == false ) {
				return false;
			}
		}
	}		
	return true;
}