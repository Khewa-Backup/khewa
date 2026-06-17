<?php
if (!defined('_PS_VERSION_')) {
	exit;
}

function upgrade_module_2_0_1($object)
{
	$object->registerHook('actionCmsPageFormBuilderModifier');
	$object->registerHook('actionObjectCmsUpdateAfter');



	$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'crazy_layout_type` (
		`id_layout_type` int(11) NOT NULL,
		`id_content_type` int(11) NOT NULL,
		`hook` varchar(100) NOT NULL,
		`crazy_page_layout` varchar(100) NOT NULL
	  ) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;';
	  $sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'crazy_layout_type`
	  ADD PRIMARY KEY (`id_layout_type`);';
	  $sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'crazy_layout_type`
	  MODIFY `id_layout_type` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;';

	if(count($sql) >= 3){
		foreach ( $sql as $query ) {
			if ( Db::getInstance()->execute( $query ) == false ) {
				return false;
			}
		}
	}	
	return true;
}