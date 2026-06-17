<?php
if (!defined('_PS_VERSION_')) {
	exit;
}

function upgrade_module_1_0_3($object)
{
	$id_tab   = (int) Tab::getIdFromClassName( 'AdminCrazyExtendedmodules' );
	$id_parent = (int) Tab::getIdFromClassName( 'AdminCrazyMain' );


	if ( ! $id_tab ) {
		$tab             = new Tab();
		$tab->active     = 1;
		$tab->class_name = 'AdminCrazyExtendedmodules';
		$tab->name       = array();
		foreach ( Language::getLanguages( true ) as $lang ) {
			$tab->name[ $lang['id_lang'] ] = 'Extend Third Party Modules';
		}
		$tab->id_parent = $id_parent;
		$tab->module    = $object->name;

		$tab->add();
	}

	$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'crazy_extended_modules` (
		`id_crazy_extended_modules` int(11) NOT NULL,
		`title` varchar(100) NOT NULL,
		`module_name` varchar(255) NOT NULL,
		`controller_name` varchar(100) NOT NULL,
		`front_controller_name` varchar(255) NOT NULL,
		`field_name` varchar(100) NOT NULL,
		`extended_item_key` varchar(300) NOT NULL,
		`active` int(1) NOT NULL
	  ) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;';
	$sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'crazy_extended_modules`
	ADD PRIMARY KEY (`id_crazy_extended_modules`);';
	$sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'crazy_extended_modules`
	MODIFY `id_crazy_extended_modules` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;';
	
	if(count($sql) >= 3){
		foreach ( $sql as $query ) {
			if ( Db::getInstance()->execute( $query ) == false ) {
				return false;
			}
		}
	}

	
	return true;
}