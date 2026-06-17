<?php
/**
 * FMM PrettyURLs
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @author    FMM Modules
 * @copyright Copyright 2018 © Fmemodules All right reserved
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @category  FMM Modules
 * @package   PrettyURLs
*/

class ManufacturerController extends ManufacturerControllerCore
{
	public function init()
	{
		$link_rewrite = Tools::safeOutput(urldecode(Tools::getValue('manufacturer_rewrite')));
		$man_pattern = '/.*?([0-9]+)\_([_a-zA-Z0-9-\pL]*)/';
		preg_match($man_pattern, $_SERVER['REQUEST_URI'], $url_array);
		if (isset($url_array[2]) && $url_array[2] != '') {
			$link_rewrite = $url_array[2];
		}

		$id_shop = $this->context->shop->id;
		if ($link_rewrite) {
			$manufacturer = Tools::strtolower(str_replace('-', '%', $link_rewrite));
			$sql = 'SELECT t1.id_manufacturer
					FROM '._DB_PREFIX_.'manufacturer t1
					LEFT JOIN '._DB_PREFIX_.'manufacturer_shop t2 ON (t1.id_manufacturer = t2.id_manufacturer)
					WHERE t1.name LIKE (\''.pSQL($manufacturer).'\') AND t2.id_shop = '.(int)$id_shop;
			$id_manufacturer = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);

			if ($id_manufacturer != '') {
				$_POST['id_manufacturer'] = $id_manufacturer;
				$_GET['manufacturer_rewrite'] = '';
			}
		}
		//Fix for accented chars URLs
		$allow_accented_chars = (int)Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL');
		if ($allow_accented_chars > 0) {
			$id_manufacturer = (int)Tools::getValue('id_manufacturer');
			if ($id_manufacturer <= 0) {
				$cls_rewrite = str_replace('-', ' ', $_GET['manufacturer_rewrite']);
				$id = $this->getKeyExistanceManuf($cls_rewrite);
				if ($id > 0) {
					$_POST['id_manufacturer'] = $id;
				}
			}
		}
		parent::init();
	}
	
	private function getKeyExistanceManuf($request)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `id_manufacturer`
				FROM '._DB_PREFIX_.'manufacturer
				WHERE `name` LIKE "'.pSQL($request).'"');
	}
}