<?php
require_once dirname( __FILE__ ) . '/../../classes/CrazyExtendedmodules.php';

require_once _PS_MODULE_DIR_ . 'crazyelements/includes/plugin.php';

use CrazyElements\PrestaHelper;
use CrazyElements\TemplateLibrary\Classes\Import_Images;

class AdminCrazyExtendedmodulesController extends ModuleAdminController {

	public $activeButton=true;
	public function __construct() {
		$this->table     = 'crazy_extended_modules';
		$this->className = 'CrazyExtendedmodules';
		$this->lang      = false;
		$this->deleted   = false;
		$this->bootstrap = true;
		$this->module    = 'crazyelements';
		$this->activeButton=PrestaHelper::get_option('ce_licence','false');

		parent::__construct();

		$this->fields_list  = array(
			'id_crazy_extended_modules' => array(
				'title'   => $this->l( 'Id' ),
				'width'   => 100,
				'type'    => 'text',
				'orderby' => false,
				'filter'  => false,
				'search'  => false,
			),
			'title'            => array(
				'title'   => $this->l( 'Title' ),
				'width'   => 440,
				'type'    => 'text',
				'lang'    => true,
				'orderby' => false,
				'filter'  => false,
				'search'  => false,
			),
			'module_name'            => array(
				'title'   => $this->l( 'Module Name' ),
				'width'   => 440,
				'type'    => 'text',
				'lang'    => true,
				'orderby' => false,
				'filter'  => false,
				'search'  => false,
			),
			'active'           => array(
				'title'   => $this->l( 'Status' ),
				'width'   => '70',
				'align'   => 'center',
				'active'  => 'status',
				'type'    => 'bool',
				'orderby' => false,
				'filter'  => false,
				'search'  => false,
			),
		);
		$this->bulk_actions = array(
			'delete' => array(
				'text'    => $this->l( 'Delete selected' ),
				'icon'    => 'icon-trash',
				'confirm' => $this->l( 'Delete selected items?' ),
			),
		);
		parent::__construct();
	}

	public function initContent() {
		if ( $this->display == 'list' ) {
			$this->display = '';
		}
		if ( isset( $this->display ) && method_exists( $this, 'render' . $this->display ) ) {
			$this->content .= $this->initPageHeaderToolbar();
			$this->content .= $this->{'render' . $this->display}();
			$this->context->smarty->assign(
				array(
					'content'                   => $this->content,
				)
			);
		} else {
			return parent::initContent();
		}
	}

	public function display() {
		parent::display();
	}

	public function renderForm() {
		
		$this->fields_form = array(
			'legend'  => array(
				'title' => $this->l( 'Extended Module' ),
			),
			'input'   => array(
				array(
					'type'     => 'text',
					'label'    => $this->l( 'Title' ),
					'name'     => 'title',
					'required' => true,
					'desc'     => $this->l( 'Enter Your Title' ),
				),
				array(
					'type'     => 'text',
					'label'    => $this->l( 'Module Name' ),
					'name'     => 'module_name',
					'required' => true,
					'desc'     => $this->l( 'Enter The Extended Module Name' ),
				),
				array(
					'type'     => 'text',
					'label'    => $this->l( 'Controller Name' ),
					'name'     => 'controller_name',
				),
				array(
					'type'     => 'text',
					'label'    => $this->l( 'Front Controller Name' ),
					'name'     => 'front_controller_name',
				),
				array(
					'type'     => 'text',
					'label'    => $this->l( 'Field Name to Edit With Crazy Editor' ),
					'name'     => 'field_name',
				),
				array(
					'type'     => 'text',
					'label'    => $this->l( 'Key of the Id of The Extended Item' ),
					'name'     => 'extended_item_key',
				),
				array(
					'type'     => 'switch',
					'label'    => $this->l( 'Status' ),
					'name'     => 'active',
					'required' => false,
					'class'    => 't',
					'is_bool'  => true,
					'values'   => array(
						array(
							'id'    => 'active',
							'value' => 1,
							'label' => $this->l( 'Enabled' ),
						),
						array(
							'id'    => 'active',
							'value' => 0,
							'label' => $this->l( 'Disabled' ),
						),
					),
				),
			),
			'submit'  => array(
				'title' => $this->l( 'Save And Close' ),
				'class' => 'btn btn-default pull-right',
			),
			'buttons' => array(
				'save-and-stay' => array(
					'name'  => 'submitAdd' . $this->table . 'AndStay',
					'type'  => 'submit',
					'title' => $this->l( 'Save And Stay' ),
					'class' => 'btn btn-default pull-right',
					'icon'  => 'process-icon-save',
				),
			),
		);

		if ( Shop::isFeatureActive() ) {
			$this->fields_form['input'][] = array(
				'type'  => 'shop',
				'label' => $this->l( 'Shop association:' ),
				'name'  => 'checkBoxShopAsso',
			);
		}
		return parent::renderForm();
	}

	public function setMedia( $isNewTheme = false ) {
		parent::setMedia();
	}

	public function renderList() {
		$this->addRowAction( 'edit' );
		$this->addRowAction( 'delete' );
		return parent::renderList();
	}

	public function initToolbar() {
		parent::initToolbar();
	}
}