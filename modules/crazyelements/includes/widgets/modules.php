<?php
namespace CrazyElements;

use CrazyElements\PrestaHelper;
use CrazyElements\Widget_Base;

if ( ! defined( '_PS_VERSION_' ) ) {
	exit; // Exit if accessed directly.
}


class Widget_Modules extends Widget_Base {

	/**
	 * Get widget name.
	 *
	 * Retrieve accordion widget name.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'modules';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve accordion widget title.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return PrestaHelper::__( 'Modules', 'elementor' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve accordion widget icon.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'ceicon-modules-widget';
	}
	public function get_categories() {
		return array( 'crazy_addons' );
	}

	private function get_modules_list() {

		$modules_list = array();

		$include_modules = array(
			'ps_banner',
			'contactform',
			'ps_categorytree',
			'ps_contactinfo',
			'ps_currencyselector',
			'ps_customeraccountlinks',
			'ps_customersignin',
			'ps_customtext',
			'ps_emailsubscription',
			'ps_featuredproducts',
			'ps_imageslider',
			'ps_languageselector',
			'ps_linklist',
			'ps_mainmenu',
			'ps_searchbar',
			'ps_shoppingcart',
			'ps_socialfollow',
			'blockreassurance',
		);
		$id_shop         = \Context::getcontext()->shop->id;
		$sql             = 'SELECT m.`name` FROM `' . _DB_PREFIX_ . 'module` m
        JOIN `' . _DB_PREFIX_ . 'module_shop` ms ON (m.`id_module` = ms.`id_module` AND ms.`id_shop` = ' . (int) ( $id_shop ) . ')
            WHERE m.active=1';
		$results         = \Db::getInstance()->executeS( $sql );
		if ( ! empty( $results ) ) {
			foreach ( $results as $module ) {
				if ( in_array( $module['name'], $include_modules ) ) {
					$modules_list[ $module['name'] ] = ucwords( str_replace( '_', ' ', $module['name'] ) );
				}
			}
		}
		return $modules_list;

	}

	/**
	 * Register accordion widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since  1.0
	 * @access protected
	 */
	protected function _register_controls() {
		$this->start_controls_section(
			'section_title',
			array(
				'label' => PrestaHelper::__( 'Modules', 'elementor' ),
			)
		);
		$this->add_control(
			'select_modules',
			array(
				'label'   => PrestaHelper::__( 'Select Modules', 'elementor' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $this->get_modules_list(),
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Render accordion widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since  1.0
	 * @access protected
	 */
	protected function render() {

		if ( PrestaHelper::is_admin() ) {
			return;
		}
		$settings       = $this->get_settings_for_display();
		$select_modules = $settings['select_modules'];
		$context        = \Context::getContext();
		echo $this->crazy_module_execute( $select_modules );
	}

	/**
	 * Render accordion widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since  1.0
	 * @access protected
	 */
	protected function _content_template() {
	}

	public function crazy_module_execute( $mod_name = '' ) {
		$results = '';
		if ( \Module::isInstalled( $mod_name ) && \Module::isEnabled( $mod_name ) ) {
			$mod_ins = \Module::getInstanceByName( $mod_name );
			if ( ! PrestaHelper::is_admin() ) {
				\Hook::exec( 'displayHeader' );

			}

			if ( ! is_object( $mod_ins ) ) {
				return $results;
			}
			$results         = '';
			$context         = \Context::getContext();
			$retro_hook_name = PrestaHelper::$hook_current;
			if ( \Validate::isLoadedObject( $mod_ins ) && method_exists( $mod_ins, 'renderWidget' ) ) {
				$results = $mod_ins->renderWidget( $retro_hook_name, array() );
			}
			return $results;
		}
	}
}
