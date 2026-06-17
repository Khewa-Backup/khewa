<?php
require_once dirname( __FILE__ ) . '/../../classes/CrazyContent.php';
require_once dirname( __FILE__ ) . '/../../classes/CrazyPrdlayouts.php';
require_once dirname( __FILE__ ) . '/../../classes/CrazyPrdlayoutAjax.php';


// require_once dirname( __FILE__ ) . '/../../includes/template-library/classes/class-import-images.php';

require_once _PS_MODULE_DIR_ . 'crazyelements/includes/plugin.php';
use CrazyElements\PrestaHelper;
use CrazyElements\TemplateLibrary\Classes\Import_Images;

class AdminCrazyPrdlayoutsController extends ModuleAdminController {


	public $activeButton = true;
	public function __construct() {
		$this->table        = 'crazyprdlayouts';
		$this->className    = 'CrazyPrdlayouts';
		$this->lang         = true;
		$this->deleted      = false;
		$this->bootstrap    = true;
		$this->module       = 'crazyelements';
		$this->activeButton = PrestaHelper::get_option( 'ce_licence', 'false' );
		if ( Shop::isFeatureActive() ) {
			Shop::addTableAssociation( $this->table, array( 'type' => 'shop' ) );
		}
		parent::__construct();

		$this->fields_list  = array(
			'id_crazyprdlayouts' => array(
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
			'active'           => array(
				'title'   => $this->l( 'Status' ),
				'width'   => '70',
				'align'   => 'center',
				'active'  => 'status',
				'type'    => 'bool',
				'orderby' => false,
				'filter'  => false,
				'search'  => false,
			)
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

	public function init() {
		parent::init();
		$this->_join            = 'LEFT JOIN ' . _DB_PREFIX_ . 'crazyprdlayouts_shop prdlayoutshops ON a.id_crazyprdlayouts=prdlayoutshops.id_crazyprdlayouts && prdlayoutshops.id_shop IN(' . implode( ',', Shop::getContextListShopID() ) . ')';
		$this->_select          = 'prdlayoutshops.id_shop';
		$this->_defaultOrderBy  = 'a.position';
		$this->_defaultOrderWay = 'DESC';
		if ( Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_SHOP ) {
			$this->_group = 'GROUP BY a.id_crazyprdlayouts';
		}
		$this->_select = 'a.position position';
	}

	public function getList( $id_lang, $order_by = null, $order_way = null, $start = 0, $limit = null, $id_lang_shop = false ) {
		if ( $order_way == null ) {
			$order_way = 'ASC';
		}
		return parent::getList( $id_lang, $order_by, $order_way, $start, $limit, $id_lang_shop );
	}

	public function initContent() {
		if ( Tools::getvalue( 'crazyprdlayout_ajaxgetproducts' ) ) {
			$ajax_obj = new CrazyPrdlayoutAjax();
			echo $ajax_obj->getProductsByName();
			die();
		}
		if ( Tools::getvalue( 'crazyprdlayout_ajaxgetcats' ) ) {
			$ajax_obj = new CrazyPrdlayoutAjax();
			echo $ajax_obj->getCatsByName();
			die();
		}
		return parent::initContent();
	}

	public function display() {
		parent::display();
	}

	public function renderForm() {
		$classypextra_is_edit         = false;
		$specific_prd_values          = '';
		$specific_product_catg_values = '';
		$product_page_values          = '';
		$products_list_array          = array();
		$cats_list_array              = array();
		$classy_productlayoutbuilder_hascrazy         = 0;
		$proper_url                   = '';
		$icon_url                     = '';
		$edit_all                     = true;

		if ( Tools::getvalue( 'id_crazyprdlayouts' ) ) {
			$classypextra_is_edit         = true;
			$crazyprdlayouts       = new CrazyPrdlayouts( Tools::getvalue( 'id_crazyprdlayouts' ) );
			$specific_prd_values          = $crazyprdlayouts->specific_product;
			$specific_product_catg_values = $crazyprdlayouts->specific_product_catg;
			$edit_all                     = $crazyprdlayouts->product_page;

			$provider_obj        = new CrazyPrdlayoutAjax();
			$products_list_array = $provider_obj->getProductsById( $specific_prd_values );
			$cats_list_array     = $provider_obj->getCatsById( $specific_product_catg_values );
		}

		$this->fields_form = array(
			'legend'  => array(
				'title' => $this->l( 'Product Layout Builder' ),
			),
			'input'   => array(
				array(
					'type'     => 'text',
					'label'    => $this->l( 'Title' ),
					'name'     => 'title',
					'lang'     => true,
					'required' => true,
					'desc'     => $this->l( 'Enter Your Title' ),
				),
				array(
					'type'         => 'textarea',
					'label'        => $this->l( 'Content' ),
					'name'         => 'content',
					'rows'         => 10,
					'cols'         => 62,
					'class'        => 'crazy_productlayoutbuilder_content_class',
					'lang'         => true,
					'autoload_rte' => false,
					'desc'         => $this->l( 'Enter Your Description' ),
				),
				array(
					'type'                => 'crazy_productlayoutbuilder_content_type',
					'name'                => 'title',
					'classy_productlayoutbuilder_is_edit' => $classypextra_is_edit,
					'specific_prd_values' => $specific_prd_values,
					'product_page_values' => $product_page_values,
				),
				array(
					'type'     => 'switch',
					'label'    => $this->l( 'Show All Product Page' ),
					'name'     => 'product_page',
					'required' => false,
					'class'    => 'product_page_class',
					'is_bool'  => true,
					'values'   => array(
						array(
							'id'    => 'product_page_id_on',
							'value' => 1,
							'label' => $this->l( 'Enabled' ),
						),
						array(
							'id'    => 'product_page_id_off',
							'value' => 0,
							'label' => $this->l( 'Disabled' ),
						),
					),
				),
				array(
					'type'     => 'ajaxproducts',
					'label'    => $this->l( 'Select Products' ),
					'name'     => 'specific_product_temp',
					'class'    => 'specific_product_class',
					'id'       => 'specific_product_id',
					'multiple' => true,
					'saved'    => $products_list_array,
				),
				array(
					'type'     => 'ajaxproductcats',
					'label'    => $this->l( 'Select Product Categories' ),
					'name'     => 'specific_product_catg_temp',
					'class'    => 'specific_product_catg_class',
					'id'       => 'specific_product_catg_id',
					'multiple' => true,
					'saved'    => $cats_list_array,
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

		if ( ! ( $crazyprdlayouts = $this->loadObject( true ) ) ) {
			return;
		}
		$this->fields_form['submit'] = array(
			'title' => $this->l( 'Save And Close' ),
			'class' => 'btn btn-default pull-right',
		);

		if ( ! Tools::getvalue( 'id_crazyprdlayouts' ) ) {
			$this->fields_value['content_type'] = 1;
		} else {
			$crazyprdlayouts                              = new CrazyPrdlayouts( Tools::getvalue( 'id_crazyprdlayouts' ) );
			$this->fields_value['specific_product_temp']       = $crazyprdlayouts->specific_product;
			$this->fields_value['specific_product_catg_temp']  = $crazyprdlayouts->specific_product_catg;
			$this->fields_value['content_type']                = $crazyprdlayouts->content_type;
		}

		return parent::renderForm();
	}

	public function setMedia( $isNewTheme = false ) {
		parent::setMedia();
		$this->addJqueryUi( 'ui.widget' );
		$this->addJqueryPlugin( 'tagify' );
		$this->addJqueryPlugin( 'autocomplete' );
		$this->addJs( CRAZY_ASSETS_URL . 'js/prdbuilder_admin.js' );
		Media::addJsDef( array( 'crazyprdlayout_ajaxurl' => $this->context->link->getAdminLink( 'AdminCrazyPrdlayouts' ) ) );
	}

	public function renderList() {
		$ce_licence = PrestaHelper::get_option('ce_licence', 'false');
        if ($ce_licence == "false") {
            $active_link = PrestaHelper::get_setting_page_url();
            return '<style>.need_to_active {font-size: 20px;color: #495157 !important;font-weight: bold;}.need_to_active_a {font-size: 12px;font-weight: bold;}</style><div class="panel col-lg-12"> <div class="panel-heading"> Need To Active Licence.<span class="badge"></span></div><div class="col-md-12"><div class="need_to_active">Need To Active Licence.</div><a class="need_to_active_a" href="' . $active_link . '">Click For Active.</a></div></div>';
        }
		$this->addRowAction( 'edit' );
		$this->addRowAction( 'delete' );
		$this->addRowAction( 'duplicate' );
		return parent::renderList();
	}

	public function initProcess() {
		if ( Tools::getIsset( 'duplicate' . $this->table ) ) {
			$this->action = 'duplicate';
		}
		
		if ( ! $this->action ) {
			parent::initProcess();
		} else {
			$this->id_object = (int) Tools::getValue( $this->identifier );
		}
	}

	public function processSave() {

		if ( Tools::isSubmit( 'submitAddcrazyprdlayoutsAndStay' )
			|| Tools::isSubmit( 'submitAddcrazyprdlayouts' )
		) {
			$object = parent::processSave();

			if ( Tools::isSubmit( 'inputAccessories' ) ) {
					$object->specific_product = Tools::getValue( 'inputAccessories' );
			}
			if ( Tools::isSubmit( 'inputCatAccessories' ) ) {
					$object->specific_product_catg = Tools::getValue( 'inputCatAccessories' );
			}
			if ( $object ) {
				$object->update();
				return $object;
			} else {
				return false;
			}
		}

		return true;
	}

	public function initPageHeaderToolbar() {
		parent::initPageHeaderToolbar();
	}

	public function initToolbar() {
		parent::initToolbar();
	}
}