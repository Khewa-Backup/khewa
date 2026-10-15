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
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class PronesisSizeChartGroups extends ObjectModel
{
	public $psc_group_id;
	public $name;
	public $description;
	public $categories;
	public $value;
	public $image;
	public $active = 0;
	public $date_add;
	public $date_upd;

	protected $table = 'pronesissizechartgroups';
	protected $identifier = 'psc_group_id';
	public static $definition = array (
	'table' => 'pronesissizechartgroups',
	'primary' => 'psc_group_id',
	'fields' => array (
	'name' => array (
	'type' => self::TYPE_STRING,
	'lang' => true,
	'validate' => 'isCatalogName',
	'required' => true,
	'size' => 128
	),
	'description' => array (
	'type' => self::TYPE_STRING,
	'lang' => true,
	'validate' => 'isCatalogName',
	'required' => true,
	'size' => 128
	),
	'categories' => array (
	'type' => self::TYPE_STRING,
	'lang' => true,
	'validate' => 'isCatalogName',
	'required' => false,
	'size' => 255
	),
	'value' => array (
	'type' => self::TYPE_STRING,
	'lang' => true,
	'validate' => 'isCatalogName',
	'required' => false,
	'size' => 255
	),
	'image' => array (
	'type' => self::TYPE_STRING,
	'lang' => true,
	'validate' => 'isCatalogName',
	'required' => false,
	'size' => 255
	),
	'active' => array (
	'type' => self::TYPE_BOOL,
	'validate' => 'isBool',
	'required' => true
	),
	'date_add' => array (
	'type' => self::TYPE_DATE,
	'validate' => 'isDateFormat'
	),
	'date_upd' => array (
	'type' => self::TYPE_DATE,
	'validate' => 'isDateFormat'
	)
	)
	);


	public static function getSizeChartGroups($active = 1)
	{
		$res = Db::getInstance()->ExecuteS('select psc_group_id, name from `'._DB_PREFIX_.
		'pronesissizechartgroups` where active='.(int)$active.' order by name');
		return $res;
	}
	public static function getSizeChartByIdProduct($proid)
	{
		if (!$proid || (int)$proid <= 0)
		return false;
		$res = Db::getInstance()->ExecuteS('select * from `'._DB_PREFIX_.
		'pronesissizechartproducts` where id_product='.(int)$proid.' and shop_id='.(int)Context::getContext()->shop->id);
		return $res;
	}
	public static function getSizeChartByIdGroup($gid)
	{
		if (!$gid || (int)$gid <= 0)
		return false;
		$res = Db::getInstance()->getRow('select description,value,image from `'._DB_PREFIX_.
		'pronesissizechartgroups` where active=1 and psc_group_id='.(int)$gid.' and shop_id='.(int)Context::getContext()->shop->id);
		return $res;
	}
	public static function getSizeChartByIdCategory($catid)
	{
		if (!$catid || (int)$catid <= 0)
		return false;
		$res = Db::getInstance()->ExecuteS('select description,value,image from `'._DB_PREFIX_.
		'pronesissizechartgroups` where active=1 and categories LIKE \'%|'.(int)$catid.'|%\' and shop_id='.(int)Context::getContext()->shop->id);
		return $res;
	}
	public static function sizeChartToCategory($catid)
	{
		if (!$catid || (int)$catid <= 0)
		return false;
		$res = Db::getInstance()->ExecuteS('select name from `'._DB_PREFIX_.
		'pronesissizechartgroups` where categories LIKE \'%|'.(int)$catid.'|%\' and active=1 and shop_id='.(int)Context::getContext()->shop->id);
		return $res;
	}
}