<?php
/**
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2015 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
exit;

include_once dirname(__FILE__).'/classes/pronesissizechartgroups.php';

class PronesisSizeChart extends Module
{
	public function __construct()
	{
		$this->name = 'pronesissizechart';
		$this->tab = 'front_office_features';
		$this->version = '2.0.2';
		$this->author = 'Pronesis Srl';
		$this->module_key = '4885c2c51f565f23fa42437d1b57d5a4';
		$this->languages = Language::getLanguages();
		$this->need_instance = 0;
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
		$this->bootstrap = true;

		parent::__construct();

		$this->displayName = $this->l('Fast and Easy Size Chart');
		$this->description = $this->l('Manage your product size chart in a click');
		$this->confirmUninstall = $this->l('Are you sure you want to delete module data?');
		$this->lang_id = Context::getContext()->language->id;
		$this->shop_id = Context::getContext()->shop->id;
		$this->path = __PS_BASE_URI__.'modules'.DIRECTORY_SEPARATOR.$this->name.DIRECTORY_SEPARATOR;
		$this->image_folder = _PS_MODULE_DIR_.$this->name.DIRECTORY_SEPARATOR.'uploads';
		$this->set_pempty = 'b%3A0%3B';

	}
	public function install()
	{
		if (!parent::install()
	|| !$this->installModuleTab()
	|| !$this->registerHook('displayHeader')
	|| !$this->registerHook('displayBackOfficeHeader')
	|| !$this->registerHook('actionProductUpdate')
	|| !$this->registerHook('displayAdminProductsExtra')
	|| !$this->registerHook('displayProductButtons')
	|| !$this->registerHook('displayRightColumnProduct') 
	|| !$this->registerHook('displayMySizeChartHook') 
	|| !$this->registerHook('actionFrontControllerSetMedia')) 
		return false;
		if (!$this->createTables())
			return false;
		if (!is_dir($this->image_folder))
		if (!mkdir($this->image_folder, 0777))
			return false;
		return true;
	}
	public function uninstall()
	{
		if (!parent::uninstall()
	|| !$this->uninstallModuleTab()
	|| !$this->dropTables())
			return false;
		return true;
	}
	public function reset()
	{
		if (!$this->uninstall(false)) 
		{
            return false;
        }
		if (!$this->install(false)) 
		{
            return false;
        }
        return true;
	}
	private function installModuleTab()
	{
		$admin_tab_catalog_id = Tab::getIdFromClassName('AdminCatalog');
		$tabpscgroups = new Tab();
		foreach ($this->languages as $language)
		$tabpscgroups->name[(int)$language['id_lang']] = 'Fast and Easy Size Chart';
		$tabpscgroups->class_name = 'AdminPronesisSizeChart';
		$tabpscgroups->module = 'pronesissizechart';
		$tabpscgroups->id_parent = $admin_tab_catalog_id;
		if (!$tabpscgroups->save())
			return false;
		return true;
	}
	private function uninstallModuleTab()
	{
		$id_tab_product = Tab::getIdFromClassName('AdminPronesisSizeChart');
		if ($id_tab_product != 0)
		{
			$tab_product = new Tab($id_tab_product);
			$tab_product->delete();
			return true;
		}
		return false;
	}
	private function createTables()
	{
		$res = (bool)Db::getInstance()->execute('
		CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'pronesissizechartgroups` (
		`psc_group_id` int(11) NOT NULL AUTO_INCREMENT,
		`shop_id` int(11) NOT NULL,
		`name` varchar(128) COLLATE utf8_general_ci DEFAULT NULL,
		`description` LONGTEXT NULL,
		`categories` LONGTEXT NULL DEFAULT NULL,
		`value` BLOB NULL DEFAULT NULL ,
		`image` varchar(128) COLLATE utf8_general_ci DEFAULT NULL,
		`active` tinyint(1) NOT NULL DEFAULT \'0\',
		`date_add` datetime DEFAULT NULL,
		`date_upd` datetime DEFAULT NULL,
		PRIMARY KEY (`psc_group_id`),
		UNIQUE KEY `psc_group_id` (`psc_group_id`,`shop_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1');
		$res &= Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'pronesissizechartproducts` (
		`id_product` int(11) NOT NULL,
		`shop_id` int(11) NOT NULL,
		`hide_all` tinyint(1) NOT NULL DEFAULT \'0\',
		`psc_group_id` int(11) NULL DEFAULT NULL,
		`description` BLOB NULL,
		`value` BLOB NULL DEFAULT NULL,
		`date_upd` datetime DEFAULT NULL,
		UNIQUE KEY `id_product` (`id_product`,`shop_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1 ;');
		return $res;
	}
	private function dropTables()
	{
		if (Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'pronesissizechartgroups`, `'._DB_PREFIX_.'pronesissizechartproducts`;'))
			return true;
		return false;
	}

	public function hookDisplayBackOfficeHeader()
	{
		$this->context->controller->addCSS($this->path.'views/css/pronesissizechart.css');
	}

	public function hookDisplayAdminProductsExtra($params)
	{
		$id_product = @(int)Tools::getValue('id_product', $params['id_product']);
		//if (!$id_product)
		//	return false;
		$product = new Product((int)$id_product, false, $this->lang_id);
		$string_sctocat = '';
		$sizechart_to_category = PronesisSizeChartGroups::sizeChartToCategory((int)$product->id_category_default);
		if (count($sizechart_to_category))
			foreach ($sizechart_to_category as $v)
				$string_sctocat .= $v['name'].', ';
		if(version_compare(_PS_VERSION_,'1.7','>')) {
			$tpl = 'admin_products17.tpl';
		} else {
			$tpl = 'admin_products.tpl';
		}
		$template_path = dirname(__FILE__).'/views/templates/admin/'.$tpl;
		$data = $this->context->smarty->createTemplate($template_path);
		$lang_text = array($this->l('The description that will appear above the specific size chart:'),
		$this->l('Click the buttons below to show/hide the cells of the sizing chart based on selected languages:'),
		$this->l('Add row'),$this->l('Add column'));
		$data->assign(array(
		'languages' => $this->languages,
		'path' => $this->path,
		'base_url' => __PS_BASE_URI__,
		'set_empty' => $this->set_pempty,
		'the_default_category' => (int)$product->id_category_default,
		'sizechart_to_category' => $sizechart_to_category,
		'string_sctocat' => Tools::substr($string_sctocat, 0, -2),
		'sizechartgroups' => PronesisSizeChartGroups::getSizeChartGroups(),
		'sizechartproduct' => PronesisSizeChartGroups::getSizeChartByIdProduct((int)$id_product),
		'langtext' => urlencode(serialize($lang_text))));

		return $data->fetch();
	}

	public function hookActionProductUpdate($params)
	{
//        return true;
		if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {


		    if(Tools::getValue('cell') != '' && Tools::getValue('cell') != false && Tools::getValue('cell') != null){
		        $value = urlencode(serialize(Tools::getValue('cell')));
            }else{
                $value = urlencode(serialize(array()));
            }

            if(Tools::getValue('description') != '' && Tools::getValue('description') != false && Tools::getValue('description') != null){
                $description = urlencode(serialize(Tools::getValue('description')));
            }else{
                $description = urlencode(serialize(array()));
            }


//		    $value = pSQL(((count(Tools::getValue('cell'))) ? urlencode(serialize(Tools::getValue('cell'))) : urlencode(serialize(array()))));
//		    $description =  pSQL(((count(Tools::getValue('description')))? urlencode(serialize(Tools::getValue('description'))) : urlencode(serialize(array()))));



			$data = array('id_product' => (int)Tools::getValue('id_product', $params['id_product']),
				'shop_id' => (int)$this->shop_id,
				'hide_all' => (int)Tools::getValue('hide_all'),
				'psc_group_id' => (int)Tools::getValue('sizechart_per_product'),
				'description' =>$description,
				'value' =>$value ,
				'date_upd' => date('Y-m-d H:i:s'));
				Db::getInstance()->insert('pronesissizechartproducts', $data, false, true, Db::REPLACE);
		} else {
			if (Tools::isSubmit('submitAddproductAndStay') || Tools::isSubmit('submitAddproduct') || Tools::isSubmit('submitAddProductAndPreview'))
			{
				
				if (Tools::getValue('key_tab') != 'ModulePronesissizechart' || !Tools::getValue('id_product'))
					return false;

				$data = array('id_product' => (int)Tools::getValue('id_product'),
				'shop_id' => (int)$this->shop_id,
				'hide_all' => (int)Tools::getValue('hide_all'),
				'psc_group_id' => (int)Tools::getValue('sizechart_per_product'),
				'description' => pSQL(((count(Tools::getValue('description')))
				? urlencode(serialize(Tools::getValue('description'))) : urlencode(serialize(array())))),
				'value' => pSQL(((count(Tools::getValue('cell'))) ? urlencode(serialize(Tools::getValue('cell'))) : urlencode(serialize(array())))),
				'date_upd' => date('Y-m-d H:i:s'));
				Db::getInstance()->insert('pronesissizechartproducts', $data, false, true, Db::REPLACE);
			}
		}
	}
	
	public function hookActionFrontControllerSetMedia($params)
	{
		if(version_compare(_PS_VERSION_,'1.7','>')) 
		{
			if ('product' === $this->context->controller->php_self) {
				$this->context->controller->registerStylesheet(
					'module-'.$this->name.'-style',
					'modules/'.$this->name.'/views/css/style_psc.css',
					[
					'media' => 'all',
					'priority' => 200,
					]
				);
			}
		}
	}

	public function hookDisplayMySizeChartHook($params)
	{
		return $this->hookDisplayRightColumnProduct($params);
	}

	public function hookDisplayProductAdditionalInfo($params)
	{
		return $this->hookDisplayRightColumnProduct($params);
	}
	
	public function hookDisplayRightColumnProduct($params)
	{
	$id_product = @(int)Tools::getValue('id_product', $params['id_product']);
	if(!isset($id_product)){
	    return false;
	}
		if (!$id_product)
			return false;
		if(version_compare(_PS_VERSION_,'1.7','<')) 
		{
			$this->context->controller->addJS($this->path.'views/js/modal_psc.js');
			$this->context->controller->addCSS($this->path.'views/css/style_psc.css');
		}
		$product = new Product((int)$id_product, false, $this->lang_id);
		$sizechartproduct = PronesisSizeChartGroups::getSizeChartByIdProduct((int)$id_product);
		$hide_all = 0;
		if (count($sizechartproduct))
			$hide_all = (int)$sizechartproduct[0]['hide_all'];
		$image = '';
		$get_groupchart = array();
		$sizechart = array();
//		if (count($sizechartproduct) && ((int)$sizechartproduct[0]['psc_group_id'] != 0 || $sizechartproduct[0]['value'] != $this->set_pempty))
//		{
//
//            var_dump(11111);
//            die("okman");
//			if ((int)$sizechartproduct[0]['psc_group_id'] != 0)
//			{
//				$get_groupchart[] = PronesisSizeChartGroups::getSizeChartByIdGroup($sizechartproduct[0]['psc_group_id']);
//				if (count($get_groupchart))
//					$get_groupchart[0]['value'] = Tools::unSerialize(urldecode($get_groupchart[0]['value']));
//				if ($get_groupchart[0]['image'] != '' && file_exists($this->image_folder.'/'.$get_groupchart[0]['image']))
//					$image = $this->path.'uploads/'.$get_groupchart[0]['image'];
//				else
//					$image = '';
//				$get_groupchart[0]['description'] = Tools::unSerialize(urldecode($get_groupchart[0]['description']));
//			}
//			$get_groupchart[0]['image'] = $image;
//			if ($sizechartproduct[0]['value'] != $this->set_pempty)
//				$get_groupchart[0]['value'] = Tools::unSerialize(urldecode($sizechartproduct[0]['value']));
//			if ($sizechartproduct[0]['description'] != $this->set_pempty)
//				$get_groupchart[0]['description'] = Tools::unSerialize(urldecode($sizechartproduct[0]['description']));
//			$sizechart = $get_groupchart;
//		}
//		else
//		{

			$default_sizecharts = PronesisSizeChartGroups::getSizeChartByIdCategory((int)$product->id_category_default);
			if (count($default_sizecharts))
				foreach ($default_sizecharts as $k => $val)
				{
					$default_sizecharts[$k]['value'] = Tools::unSerialize(urldecode($val['value']));
					if ($val['image'] != '' && file_exists($this->image_folder.'/'.$val['image']))
						$image = $this->path.'uploads/'.$val['image'];
					else
						$image = '';
					$default_sizecharts[$k]['image'] = $image;
					$default_sizecharts[$k]['description'] = Tools::unSerialize(urldecode($val['description']));
				}
			$sizechart = $default_sizecharts;
//		}
		if(version_compare(_PS_VERSION_,'1.7','>')) 
		{
			$template_path = dirname(__FILE__).'/views/templates/hook/product_page17.tpl';
		} 
		else 
		{
			$template_path = dirname(__FILE__).'/views/templates/hook/product_page.tpl';	
		}
		$data = $this->context->smarty->createTemplate($template_path);
		$data->assign(array(
		'language' => $this->lang_id,
		'path' => $this->path,
		'base_url' => __PS_BASE_URI__,
		'hide_all' => $hide_all,
		'sizechart' => $sizechart));
		return $data->fetch();
	}
}