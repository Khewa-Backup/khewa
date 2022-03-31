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

class AdminPronesisSizeChartController extends ModuleAdminController
{
	public function __construct()
	{
		$this->name = 'pronesissizechart';
		$this->bootstrap = true;
		$this->table = 'pronesissizechartgroups';
		$this->className = 'PronesisSizeChartGroups';
		if(version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
			$this->fields_list = array (
				'psc_group_id' => array (
				'title' => Context::getContext()->getTranslator()->trans('ID'),
				'align' => 'left',
				'width' => 30
				),
				'name' => array (
				'title' => Context::getContext()->getTranslator()->trans('Name'),
				'align' => 'left',
				'width' => 170
				),
				'active' => array (
				'title' => Context::getContext()->getTranslator()->trans('Status'),
				'width' => 70,
				'active' => 'active',
				'search' => false,
				'align' => 'center',
				'type' => 'bool',
				'orderby' => false
				)
				);
		} else {
			$this->fields_list = array (
				'psc_group_id' => array (
				'title' => $this->l('ID'),
				'align' => 'left',
				'width' => 30
				),
				'name' => array (
				'title' => $this->l('Name'),
				'align' => 'left',
				'width' => 170
				),
				'active' => array (
				'title' => $this->l('Status'),
				'width' => 70,
				'active' => 'active',
				'search' => false,
				'align' => 'center',
				'type' => 'bool',
				'orderby' => false
				)
				);
		}
		$this->identifier = 'psc_group_id';
		$this->context = Context::getContext();
		$this->languages = Language::getLanguages(true, (int)$this->context->shop->id);

		$this->target_path = _PS_MODULE_DIR_.$this->name.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR;
		$this->imageType = 'jpg';
		$this->width = 500;
		$this->height = 500;

		parent::__construct();

		$this->objects = $this->loadObject(true);
		$this->oldimage = $this->objects->image;
		$this->action = 'view';
	}

	public function renderForm()
	{
		if (!$this->objects)
			return;
		if (Tools::isSubmit('addpronesissizechartgroups'))
		{
			if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
				$the_desc = Context::getContext()->getTranslator()->trans('Create a new size chart group');
			} else {
				$the_desc =  $this->l('Create a new size chart group');
			}
			$this->toolbar_title = $the_desc;
			$this->action = 'new';
		}
		if (Tools::isSubmit('updatepronesissizechartgroups') && (int)Tools::getValue('psc_group_id') > 0)
		{
			if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
				$the_desc = Context::getContext()->getTranslator()->trans('Manage Size Chart');
			} else {
				$the_desc =  $this->l('Manage Size Chart');
			}
			$this->toolbar_title = $the_desc;
			$this->action = 'update';
		}
		//Categories
		$root_category = new Category($this->context->shop->id_category);
		$selected_cat = array();
		if ($this->objects->categories)
			$selected_cat = explode('|', $this->objects->categories);
		//Image
		$image_url = false;
		$image_size = false;
		if ($this->objects->image != '' && file_exists($this->target_path.$this->objects->image))
		{
			$image = $this->target_path.$this->objects->image;
			$image_url = ImageManager::thumbnail($image, $this->table.'_'.(int)$this->objects->id.'.'.$this->imageType, 500, $this->imageType, true, true);
			$image_size = filesize($image) / 1000;
		}
		//Description
		$description = array();
		if (!$this->objects->description)
			foreach ($this->languages as $lang)
				$description[$lang['id_lang']] = '';
		else
		$description = Tools::unSerialize(urldecode($this->objects->description));

		$html_description = '';
		foreach ($this->languages as $lang)
			$html_description .= '<div class="row" style="margin-bottom:10px"><div class="col-xs-1" style="text-align:center"><img src="'.
			__PS_BASE_URI__.'img/l/'.$lang['id_lang'].'.jpg" style="padding-top:8px"></div><div class="col-xs-11"><input type="text" name="description['.
			$lang['id_lang'].']" value="'.htmlentities($description[$lang['id_lang']], ENT_QUOTES, 'UTF-8').'"></div></div>';
		//Matrix
		$keys = array();
		if (!$this->objects->value)
		{
			foreach ($this->languages as $lang)
			$keys[] = $lang['id_lang'];
			$cell = array_fill(0, 3, array_fill(0, 3, array_fill_keys($keys, '')));
			$this->objects->value = urlencode(serialize($cell));
		}
		$value = array();
		$value = Tools::unSerialize(urldecode($this->objects->value));
		//$max_height = count($value);
		$max_width = max(array_map('count', $value));

		$firstcol = 0;
		$firstrow = 0;

		$buttons = '';
		foreach ($this->languages as $lang)
			$buttons .= '<a class="psc_button btn btn-danger" rel="psc_lang'.$lang['id_lang'].'"><i class="icon-times"></i> '.
			$lang['name'].' <img src="'.__PS_BASE_URI__.'img/l/'.$lang['id_lang'].'.jpg"></a>';

			if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
				$the_desc = Context::getContext()->getTranslator()->trans('Manage the size chart');
				$the_desc1 =  Context::getContext()->getTranslator()->trans('Click on the buttons below to show/hide the cells of the sizing chart based on selected languages');
			} else {
				$the_desc =  $this->l('Manage the size chart');
				$the_desc1 =  $this->l('Click on the buttons below to show/hide the cells of the sizing chart based on selected languages');
			}

		$html_content = '</div><h3 style="margin:0"><i class="icon-pencil"></i> '.$the_desc.'</h3><label id="psc_lang_desc" '.
		((count($this->languages) > 1) ? : 'style="display:none"').'>'.
		$the_desc1.
		':</label><div id="psc_buttons_container" '.((count($this->languages) > 1) ? : 'style="display:none"').'>'.$buttons.
		'</div><div class="col-lg-12 table-container"><table class="table-editor table" id="table1" style="width:100%;"><tfoot><tr id="remove-cols">';
		for ($i = 0; $i <= $max_width; $i++)
		{
			if ($firstcol <= 1)
				$html_content .= '<td><a style="opacity:0" class="btn btn-danger"><i class="icon-trash"></i></a></td>';
			else
				$html_content .= '<td class="remove"><a class="btn btn-danger"><i class="icon-trash"></i></a></td>';
			$firstcol++;
		}
		$html_content .= '</tr></tfoot><tbody>';
		foreach ($value as $rows => $row)
		{
			$html_content .= '<tr id="'.$rows.'">';
			if ($firstrow == 0)
				$html_content .= '<td width="3%">&nbsp;</td>';
			else
				$html_content .= '<td class="remrow" width="3%"><a class="btn btn-danger"><i class="icon-trash"></i></a></td>';
			foreach ($row as $cols => $col)
			{
				$html_content .= '<td class="psc_td" id="'.$cols.'">';
				foreach ($this->languages as $lang)
				$html_content .= '<div class="row psc_lang'.$lang['id_lang'].
				'" style="display:block"><div class="table-flag"><img src="'.__PS_BASE_URI__.'img/l/'.
				$lang['id_lang'].'.jpg"></div><div class="col-xs-11"><input value="'.htmlentities($col[$lang['id_lang']], ENT_QUOTES, 'UTF-8').
				'" type="text" name="cell['.$rows.']['.$cols.']['.$lang['id_lang'].']" id="r'.
				$rows.'-c'.$cols.'-l'.$lang['id_lang'].'"></div></div>';
				$html_content .= '</td>';
			}
			$html_content .= '</tr>';
			$firstrow++;
		}
		if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
			$html_content .= '</tbody>
			</table>
			</div>
			<div class="row row-add" style="margin: 0 0 20px">
			<a id="addrow" class="btn btn btn-success add-button" ><i class="icon-minus"></i> '.Context::getContext()->getTranslator()->trans('Add row').'</a>
			<a id="addcol" class="btn btn btn-success add-button" ><i class="icon-minus icon-rotate-90"></i> '.Context::getContext()->getTranslator()->trans('Add column').'</a>';
			$this->fields_form = array(
				'legend' => array(
				'title' => Context::getContext()->getTranslator()->trans('Manage Size chart Group'),
				'icon' => 'icon-gears'
				),
				'input' => array(
				array(
				'type' => 'text',
				'label' => Context::getContext()->getTranslator()->trans('Internal Name'),
				'name' => 'name',
				'required' => true,
				'hint' => Context::getContext()->getTranslator()->trans('The internal name of size chart group.'),
				),
				array(
				'type'      => 'switch',
				'label'     => Context::getContext()->getTranslator()->trans('Enable this size chart'),
				'name'      => 'active',
				'required'  => true,
				'class'     => 't',
				'is_bool'   => true,
				'values'    => array(
					array(
					'id'    => 'active_on',
					'value' => 1,
					'label' => Context::getContext()->getTranslator()->trans('Enabled')
					),
					array(
					'id'    => 'active_off',
					'value' => 0,
					'label' => Context::getContext()->getTranslator()->trans('Disabled')
					)
				),
				),
				array(
				'type' => 'categories',
				'name' => 'categories',
				'label' => Context::getContext()->getTranslator()->trans('Associated categories'),
				'tree' => array(
				'id' => 'categories-tree',
				'selected_categories' => $selected_cat,
				'root_category' => $root_category->id,
				'use_search' => false,
				'use_checkbox' => true
				),
				'desc' => Context::getContext()->getTranslator()->trans('By selecting associated categories, the size chart will be available for the products having the same default category.')
				),
				array(
				'type' => 'file',
				'label' => Context::getContext()->getTranslator()->trans('Size Chart image'),
				'name' => 'filename',
				'display_image' => true,
				'image' => $image_url ? $image_url : false,
				'size' => $image_size ? $image_size : false,
				'delete_url' => self::$currentIndex.'&'.$this->identifier.'='.$this->objects->id.'&token='.$this->token.'&deleteImage=1',
				'required' => false,
				'desc' => Context::getContext()->getTranslator()->trans('You can upload an image for your size chart, the image will be resized to '.
				$this->width.'x'.$this->height.' and only jpg are allowed. ')
				),
				array(
				'type' => 'html',
				'label' => Context::getContext()->getTranslator()->trans('Description'),
				'html_content' => $html_description,
				'name' => 'description',
				'required' => false,
				'desc' => Context::getContext()->getTranslator()->trans('The description that will appear above the size chart'),
				),
				array(
				'type' => 'html',
				'html_content' => $html_content,
				'name' => 'tabledraw',
				'required' => false,
				'class' => 'col-lg-10 col-lg-offset-1',
				)
				),
				'submit' => array(
				'title' => Context::getContext()->getTranslator()->trans('Save'),
				'class' => 'btn btn-default pull-right'
				),
				'buttons' => array(
				'save-and-stay' => array(
				'title' => Context::getContext()->getTranslator()->trans('Save and stay'),
				'name' => 'submitAddpronesissizechartgroupsAndStay',
				'type' => 'submit',
				'class' => 'btn btn-default pull-right',
				'icon' => 'process-icon-save'
				)
				)
				);
		} else {
			$html_content .= '</tbody>
			</table>
			</div>
			<div class="row row-add" style="margin: 0 0 20px">
			<a id="addrow" class="btn btn btn-success add-button" ><i class="icon-minus"></i> '.$this->l('Add row').'</a>
			<a id="addcol" class="btn btn btn-success add-button" ><i class="icon-minus icon-rotate-90"></i> '.$this->l('Add column').'</a>';
			
		$this->fields_form = array(
		'legend' => array(
		'title' => $this->l('Manage Size chart Group'),
		'icon' => 'icon-gears'
		),
		'input' => array(
		array(
		'type' => 'text',
		'label' => $this->l('Internal Name'),
		'name' => 'name',
		'required' => true,
		'hint' => $this->l('The internal name of size chart group.'),
		),
		array(
		'type'      => 'switch',
		'label'     => $this->l('Enable this size chart'),
		'name'      => 'active',
		'required'  => true,
		'class'     => 't',
		'is_bool'   => true,
		'values'    => array(
			array(
			'id'    => 'active_on',
			'value' => 1,
			'label' => $this->l('Enabled')
			),
			array(
			'id'    => 'active_off',
			'value' => 0,
			'label' => $this->l('Disabled')
			)
		),
		),
		array(
		'type' => 'categories',
		'name' => 'categories',
		'label' => $this->l('Associated categories'),
		'tree' => array(
		'id' => 'categories-tree',
		'selected_categories' => $selected_cat,
		'root_category' => $root_category->id,
		'use_search' => false,
		'use_checkbox' => true
		),
		'desc' => $this->l('By selecting associated categories, the size chart will be available for the products having the same default category.')
		),
		array(
		'type' => 'file',
		'label' => $this->l('Size Chart image'),
		'name' => 'filename',
		'display_image' => true,
		'image' => $image_url ? $image_url : false,
		'size' => $image_size ? $image_size : false,
		'delete_url' => self::$currentIndex.'&'.$this->identifier.'='.$this->objects->id.'&token='.$this->token.'&deleteImage=1',
		'required' => false,
		'desc' => $this->l('You can upload an image for your size chart, the image will be resized to '.
		$this->width.'x'.$this->height.' and only jpg are allowed. ')
		),
		array(
		'type' => 'html',
		'label' => $this->l('Description'),
		'html_content' => $html_description,
		'name' => 'description',
		'required' => false,
		'desc' => $this->l('The description that will appear above the size chart'),
		),
		array(
		'type' => 'html',
		'html_content' => $html_content,
		'name' => 'tabledraw',
		'required' => false,
		'class' => 'col-lg-10 col-lg-offset-1',
		)
		),
		'submit' => array(
		'title' => $this->l('Save'),
		'class' => 'btn btn-default pull-right'
		),
		'buttons' => array(
		'save-and-stay' => array(
		'title' => $this->l('Save and stay'),
		'name' => 'submitAddpronesissizechartgroupsAndStay',
		'type' => 'submit',
		'class' => 'btn btn-default pull-right',
		'icon' => 'process-icon-save'
		)
		)
		);
		}
		

		return parent::renderForm();
	}
	public function renderList()
	{
		if (Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_ALL)
			$this->_where .= ' and shop_id='.(int)$this->context->shop->id;

		$this->addRowAction('edit');
		$this->addRowAction('duplicate');
		$this->addRowAction('delete');

		$lists = parent::renderList();
		parent::initToolbar();
		$html = '';
		$html .= $lists;
		return ($html);
	}

	public function initPageHeaderToolbar()
	{
		if ($this->action == 'view')
		{
			if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
				$the_desc = Context::getContext()->getTranslator()->trans('Add new SizeChart');
			} else {
				$the_desc =  $this->l('Add new SizeChart', null, null, false);
			}
			
			$this->page_header_toolbar_btn['new_value'] = array(
			'href' => self::$currentIndex.'&add'.$this->table.'&token='.$this->token,
			'desc' => $the_desc,
			'icon' => 'process-icon-new'
			);
		}
		parent::initPageHeaderToolbar();
	}

	public function postProcess()
	{
		if (Tools::getIsset('duplicate'.$this->table))
			$this->processDuplicate();
		if (Tools::getIsset('active'.$this->table))
			$this->processActive();
		if (Tools::getIsset('submitBulkenableSelection'.$this->table))
			$this->processBulkEnable();
		if (Tools::getIsset('submitBulkdisableSelection'.$this->table))
			$this->processBulkDisable();

		$this->addCSS(array (_MODULE_DIR_.'pronesissizechart/views/css/pronesissizechart.css'));
		$this->is_editing = false;

		if (Tools::isSubmit('submitAddpronesissizechartgroups') || Tools::isSubmit('submitAddpronesissizechartgroupsAndStay'))
		{
			
			
			$this->is_editing = true;
			if (isset($_FILES['filename']['tmp_name']) && !empty($_FILES['filename']['tmp_name']))
			{
				if (pathinfo($_FILES['filename']['name'], PATHINFO_EXTENSION) != $this->imageType)
				$this->errors[] = Tools::displayError('Only jpg are allowed');
			}
			if (!count($this->errors))
			{
				if (Tools::getValue('psc_group_id') && (int)Tools::getValue('psc_group_id') > 0)
					$this->processUpdate();
				else
					$this->processAdd();
			}
		}
		if (Tools::getValue('deleteImage') && (int)Tools::getValue('deleteImage') == 1 && (int)Tools::getValue('psc_group_id') > 0)
			$this->processDeleteImage();

		if (!$this->is_editing)
			parent::postProcess();
	}

	public function processDuplicate()
	{
		if (Tools::getValue('psc_group_id') && (int)Tools::getValue('psc_group_id') > 0)
		{
			if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
				$the_desc = Context::getContext()->getTranslator()->trans('COPY');
			} else {
				$the_desc =  $this->l('COPY');
			}
			$image_name = '';
			$image = Db::getInstance()->getRow('select image from '._DB_PREFIX_.$this->table.' where psc_group_id='.(int)Tools::getValue('psc_group_id'));
			if ($image['image'] != '' && file_exists($this->target_path.$image['image']))
			if (Tools::copy($this->target_path.$image['image'], $this->target_path.'cp'.$image['image']))
			$image_name = 'cp'.$image['image'];
			Db::getInstance()->execute('insert into '._DB_PREFIX_.$this->table.
			' (shop_id,name,categories,description,value,image,active,date_add) select shop_id,CONCAT(\''.
			$the_desc.'\',\' \',name),categories,description,value,\''.pSQL($image_name).'\',0,now() FROM '.
			_DB_PREFIX_.$this->table.' where psc_group_id='.(int)Tools::getValue('psc_group_id'));
		}
		return true;
	}

	public function processActive()
	{
		if (Tools::getValue('psc_group_id') && (int)Tools::getValue('psc_group_id') > 0)
			Db::getInstance()->execute('update '._DB_PREFIX_.$this->table.' set active=(1-active) where psc_group_id='.(int)Tools::getValue('psc_group_id'));
		return true;
	}

	public function processBulkEnable()
	{
		if (count(Tools::getValue('pronesissizechartgroupsBox')))
			foreach (Tools::getValue('pronesissizechartgroupsBox') as $b)
				Db::getInstance()->execute('update '._DB_PREFIX_.$this->table.' set active=1 where psc_group_id='.(int)$b);
		return true;
	}

	public function processBulkDisable()
	{
		if (count(Tools::getValue('pronesissizechartgroupsBox')))
			foreach (Tools::getValue('pronesissizechartgroupsBox') as $b)
				Db::getInstance()->execute('update '._DB_PREFIX_.$this->table.' set active=0 where psc_group_id='.(int)$b);
		return true;
	}

	public function processDelete()
	{
		if (Tools::getValue('psc_group_id') && (int)Tools::getValue('psc_group_id') > 0)
		{
			$temp_image = Db::getInstance()->getRow('select image from '._DB_PREFIX_.$this->table.' where psc_group_id='.
			(int)Tools::getValue('psc_group_id').' and shop_id='.(int)$this->context->shop->id);
			if ($temp_image['image'] != '' && file_exists($this->target_path.$temp_image['image']))
				unlink($this->target_path.$temp_image['image']);
			Db::getInstance()->delete($this->table, 'psc_group_id='.(int)Tools::getValue('psc_group_id'));
			Db::getInstance()->update('pronesissizechartproducts', array('psc_group_id' => 0), 'psc_group_id='.(int)Tools::getValue('psc_group_id'));
		}
		return true;
	}

	public function processAdd()
	{
		$this->postImage();

		if (Db::getInstance()->insert($this->table,
		array('shop_id' => (int)$this->context->shop->id,
		'name' => pSQL(Tools::getValue('name')),
		'description' => pSQL(urlencode(serialize(Tools::getValue('description')))),
		'categories' => ((Tools::getValue('categories'))? pSQL($this->processCategories(Tools::getValue('categories'))) : ''),
		'value' => pSQL(urlencode(serialize(Tools::getValue('cell')))),
		'image' => pSQL($this->objects->image),
		'active' => (int)Tools::getValue('active'),
		'date_add' => date('Y-m-d H:i:s'))))
		{
			$this->objects->value = urlencode(serialize(Tools::getValue('cell')));
			$this->objects->description = urlencode(serialize(Tools::getValue('description')));

			$this->redirect_after = self::$currentIndex.'&psc_group_id='.(int)Db::getInstance()->Insert_ID().
			'&updatepronesissizechartgroups&token='.$this->token;
		}
	}

	public function processUpdate()
	{
		if (Tools::getValue('psc_group_id') && (int)Tools::getValue('psc_group_id') > 0)
		{
			$this->postImage();

			Db::getInstance()->update($this->table, array('name' => pSQL(Tools::getValue('name')),
			'description' => pSQL(urlencode(serialize(Tools::getValue('description')))),
			'categories' => ((Tools::getValue('categories'))? pSQL($this->processCategories(Tools::getValue('categories'))) : ''),
			'value' => pSQL(urlencode(serialize(Tools::getValue('cell')))),
			'active' => (int)Tools::getValue('active'),
			'date_upd' => date('Y-m-d H:i:s')),
			'psc_group_id='.(int)Tools::getValue('psc_group_id').' and shop_id='.(int)$this->context->shop->id);

			$this->objects->value = urlencode(serialize(Tools::getValue('cell')));
			$this->objects->description = urlencode(serialize(Tools::getValue('description')));
		}
	}

	public function processCategories($categories)
	{
		$sqlcat = '';
		if (count($categories))
		{
			foreach ($categories as $v)
				$sqlcat .= $v.'|';
			$sqlcat = '|'.$sqlcat;
		}
		$this->objects->categories = $sqlcat;
		return $sqlcat;
	}

	public function processDeleteImage()
	{
		if (!(int)Tools::getValue('psc_group_id') || (int)Tools::getValue('psc_group_id') <= 0)
			return false;
		if ($this->objects->image != '' && file_exists($this->target_path.$this->objects->image))
		if (Db::getInstance()->update('pronesissizechartgroups',
		array('image' => ''),
		'psc_group_id='.(int)Tools::getValue('psc_group_id').' and shop_id='.(int)$this->context->shop->id))
		unlink($this->target_path.$this->objects->image);

		$this->redirect_after = self::$currentIndex.'&psc_group_id='.(int)Tools::getValue('psc_group_id').
		'&updatepronesissizechartgroups&conf=3&token='.$this->token;
	}

	public function postImage($method = 'auto')
	{
		if ($method != 'auto')
			return false;
		if (!isset($_FILES['filename']['tmp_name']) || empty($_FILES['filename']['tmp_name']))
			return false;
		if (isset($_FILES['error']))
		{
			if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
				$this->errors[] = sprintf(Tools::displayError(Context::getContext()->getTranslator()->trans('Error while uploading image. (Error code: %s)')), $_FILES['error']);
			} else {
				$this->errors[] = sprintf(Tools::displayError($this->l('Error while uploading image. (Error code: %s)')), $_FILES['error']);
			}
			return false;
		}
		$temp_file = $_FILES['filename']['tmp_name'];
		$ext = pathinfo($_FILES['filename']['name'], PATHINFO_EXTENSION);
		$this->main_file_name = time().'.'.$ext;
		$main_file = $this->target_path.$this->main_file_name;
		if (!ImageManager::resize($temp_file, $main_file, (int)$this->width, (int)$this->height, ($ext ? $ext : $this->imageType)))
			$this->errors[] = Tools::displayError('Something went wrong on uploading image');
		else
		{
			if (Db::getInstance()->update('pronesissizechartgroups',
			array('image' => pSQL($this->main_file_name)),
			'psc_group_id='.(int)Tools::getValue('psc_group_id').' and shop_id='.(int)$this->context->shop->id))
			if ($this->oldimage != '' && file_exists($this->target_path.$this->oldimage))
				unlink($this->target_path.$this->oldimage);
			$this->objects->image = $this->main_file_name;
		}
	}
}