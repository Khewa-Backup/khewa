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
 * @copyright Copyright 2018 © fmemodules All right reserved
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @category  FMM Modules
 * @package   PrettyURLs
*/

class CategoryController extends CategoryControllerCore
{
	public function init()
	{
		$link_rewrite = Tools::safeOutput(urldecode(Tools::getValue('category_rewrite')));
		$cat_pattern = '/.*?\/([0-9]+)\-([_a-zA-Z0-9-\pL]*)/';
		preg_match($cat_pattern, $_SERVER['REQUEST_URI'], $url_array);
		if (isset($url_array[2]) && $url_array[2] != '') {
			$link_rewrite = $url_array[2];
		}
		if ($link_rewrite) {
			$id_lang = $this->context->language->id;
			$id_shop = $this->context->shop->id;
			$sql = 'SELECT `id_category`
					FROM '._DB_PREFIX_.'category_lang
					WHERE `link_rewrite` = \''.pSQL($link_rewrite).'\'
					AND `id_lang` = '.(int)$id_lang.'
					AND `id_shop` = '.(int)$id_shop;
			$id_category = (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
			if ($id_category > 0) {
				$_POST['id_category'] = $id_category;
				$_GET['category_rewrite'] = '';
			}
		}
		//Fix for accented chars URLs
		$allow_accented_chars = (int)Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL');
		if ($allow_accented_chars > 0) {
			$id_category = (int)Tools::getValue('id_category');
			if ($id_category <= 0) {
				$id = (int)$this->crawlDbForId($_GET['category_rewrite']);
				if ($id > 0) {
					$_POST['id_category'] = $id;
				}
			}
		}
		parent::init();
	}
	
	protected function crawlDbForId($rew)
	{
		$id_lang = $this->context->language->id;
		$id_shop = $this->context->shop->id;
		$sql = new DbQuery();
        $sql->select('`id_category`');
        $sql->from('category_lang');
		$sql->where('`id_lang` = '.(int)$id_lang.' AND `id_shop` = '.(int)$id_shop.' AND `link_rewrite` = "'.pSQL($rew).'"');
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
	}
}