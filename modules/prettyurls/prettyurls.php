<?php
/**
 * Pretty URLs
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @author    FMM Modules
 * @copyright Copyright 2019 © FMM Modules All right reserved
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @category  FMM Modules
 * @package   Prettyurls
*/

if (!defined('_PS_VERSION_'))
exit;
if (!defined('_MYSQL_ENGINE_'))
define('_MYSQL_ENGINE_', 'MyISAM');
class Prettyurls extends Module
{
	public function __construct()
	{
		$this->name = 'prettyurls';
		$this->tab = 'seo';
		$this->version = '2.2.8';
		$this->author = 'FMM Modules';
		$this->bootstrap = true;
		$this->module_key = 'db5a06ecc8e806d8941ed9a91d6d3ad2';
		$this->author_address = '0xcC5e76A6182fa47eD831E43d80Cd0985a14BB095';
		parent::__construct();
		$this->displayName = $this->l('Pretty URLs');
		$this->description = $this->l('This module will remove IDs from URLs and make them SEO friendly.');
		$this->tabClass = 'AdminPrettyUrls';
	}

	public function install()
	{
		return (parent::install()
				&& $this->installTab()
				&& $this->moveFiles()
				&& $this->clearCache()
				&& $this->registerHook('displayBackOfficeHeader'));
	}

	public function uninstall()
	{
		return ($this->removeOverloadedFiles()
				&& parent::uninstall()
				&& $this->uninstallTab()
				&& $this->clearCache());
	}

	public function installTab()
	{
		$tab = new Tab();
		$tab->active = 1;
		$tab->class_name = $this->tabClass;
		$tab->name = array();
		foreach (Language::getLanguages(true) as $lang) {
			$tab->name[$lang['id_lang']] = 'Duplicate URLs';
		}
		$tab->id_parent = (Tools::version_compare(_PS_VERSION_, '1.7.0.0', '>=')) ? (int)Tab::getIdFromClassName('AdminParentMeta') : (int)Tab::getIdFromClassName('AdminParentPreferences');
		$tab->module = $this->name;
		return $tab->add();
	}
	
	public function uninstallTab()
	{
		$id_tab = (int)Tab::getIdFromClassName('AdminPrettyUrls');
		if ($id_tab) {
			$tab = new Tab($id_tab);
			return $tab->delete();
		}
		return true;
	}
	
	public function moveFiles()
	{
		if (Tools::version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
			Tools::deleteFile(_PS_OVERRIDE_DIR_.'controllers'.DIRECTORY_SEPARATOR.'front'.DIRECTORY_SEPARATOR.'ProductController.php');
			Tools::deleteFile(_PS_OVERRIDE_DIR_.'controllers'.DIRECTORY_SEPARATOR.'front'.DIRECTORY_SEPARATOR.'CategoryController.php');
			Tools::copy(_PS_MODULE_DIR_.$this->name.DIRECTORY_SEPARATOR.'override'.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'front'.DIRECTORY_SEPARATOR.'CategoryController.php',
						_PS_OVERRIDE_DIR_.'controllers'.DIRECTORY_SEPARATOR.'front'.DIRECTORY_SEPARATOR.'CategoryController.php');
			Tools::copy(_PS_MODULE_DIR_.$this->name.DIRECTORY_SEPARATOR.'override'.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'front'.DIRECTORY_SEPARATOR.'ManufacturerController.php',
						_PS_OVERRIDE_DIR_.'controllers'.DIRECTORY_SEPARATOR.'front'.DIRECTORY_SEPARATOR.'ManufacturerController.php');
			Tools::copy(_PS_MODULE_DIR_.$this->name.DIRECTORY_SEPARATOR.'override'.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'front'.DIRECTORY_SEPARATOR.'SupplierController.php',
						_PS_OVERRIDE_DIR_.'controllers'.DIRECTORY_SEPARATOR.'front'.DIRECTORY_SEPARATOR.'SupplierController.php');
			Tools::copy(_PS_MODULE_DIR_.$this->name.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'ProductController.php',
						_PS_OVERRIDE_DIR_.'controllers'.DIRECTORY_SEPARATOR.'front'.DIRECTORY_SEPARATOR.'ProductController.php');
			if (Tools::version_compare(_PS_VERSION_, '1.7.4.0', '<')) {
				Tools::copy(_PS_MODULE_DIR_.$this->name.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'CartController.php',
						_PS_OVERRIDE_DIR_.'controllers'.DIRECTORY_SEPARATOR.'front'.DIRECTORY_SEPARATOR.'CartController.php');
			}
		}
		return true;
	}

	private function clearCache()
	{
		Tools::clearSmartyCache();
		$timestamp = time();
		if (file_exists(_PS_CACHE_DIR_.'class_index.php') && Tools::version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
			(rename(_PS_CACHE_DIR_.'class_index.php', _PS_CACHE_DIR_.$timestamp.'_class_index.php'));
		}
		if (Tools::version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
			Tools::clearSf2Cache();
		}
		return true;
	}

	public function removeOverloadedFiles()
	{
		Tools::deleteFile(_PS_OVERRIDE_DIR_.'classes'.DIRECTORY_SEPARATOR.'Dispatcher.php');
		Tools::deleteFile(_PS_OVERRIDE_DIR_.'classes'.DIRECTORY_SEPARATOR.'Link.php');
		if (Tools::version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
			Tools::deleteFile(_PS_OVERRIDE_DIR_.'controllers'.DIRECTORY_SEPARATOR.'front'.DIRECTORY_SEPARATOR.'CategoryController.php');
			Tools::deleteFile(_PS_OVERRIDE_DIR_.'controllers'.DIRECTORY_SEPARATOR.'front'.DIRECTORY_SEPARATOR.'ProductController.php');
			Tools::deleteFile(_PS_OVERRIDE_DIR_.'controllers'.DIRECTORY_SEPARATOR.'front'.DIRECTORY_SEPARATOR.'ManufacturerController.php');
			Tools::deleteFile(_PS_OVERRIDE_DIR_.'controllers'.DIRECTORY_SEPARATOR.'front'.DIRECTORY_SEPARATOR.'SupplierController.php');
			Tools::deleteFile(_PS_OVERRIDE_DIR_.'controllers'.DIRECTORY_SEPARATOR.'front'.DIRECTORY_SEPARATOR.'CartController.php');
			Tools::generateIndex();
		}
		return true;
	}

	public function hookDisplayBackOfficeHeader()
	{
		$this->context->controller->addCSS($this->_path.'views/css/admin.css');
	}
	
	public function disable($force_all = true)
    {
		if (Tools::version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
			Tools::deleteFile(_PS_OVERRIDE_DIR_.'classes'.DIRECTORY_SEPARATOR.'Dispatcher.php');
			Tools::deleteFile(_PS_OVERRIDE_DIR_.'classes'.DIRECTORY_SEPARATOR.'Link.php');
		}
		return true;
	}
	
	public function getContent()
    {
		$this->html = $this->display(__FILE__, 'views/templates/hook/info.tpl');
		$performance_link = $this->context->link->getAdminLink('AdminPerformance');
		$seo_link = $this->context->link->getAdminLink('AdminMeta');
		$warning = $this->html;
		$warning .= '<div style="clear:both"></div>';
		$warning .= $this->displayWarning($this->l('1. Turn OFF the cache for the testing of new URLs, you can turn it ON later.'));
		$warning .= $this->displayWarning($this->l('2. Please make sure overrides are NOT disabled in').' <a href="'.$performance_link.'" target="_blank">Performance</a>');
		$warning .= $this->displayWarning($this->l('3. Please see the settings of module in ').' <a href="'.$seo_link.'#meta_fieldset_routes" target="_blank">SEO Settings</a>');
		return $warning;
	}
}