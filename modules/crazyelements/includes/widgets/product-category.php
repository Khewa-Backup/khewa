<?php
namespace CrazyElements;

use CrazyElements\PrestaHelper;
use CrazyElements\Widget_Base;

if ( ! defined( '_PS_VERSION_' ) ) {
	exit; // Exit if accessed directly.
}

class Widget_ProductCategory extends Widget_Base {


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
		return 'product_category';
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
		return PrestaHelper::__( 'Product Category', 'elementor' );
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
		return 'ceicon-categories-widget';
	}

	public function get_categories() {
		return array( 'products' );
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
				'label' => PrestaHelper::__( 'General', 'elementor' ),
			)
		);

		$this->add_control(
			'layout',
			array(
				'label'   => PrestaHelper::__( 'Select Style', 'elementor' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'default'   => PrestaHelper::__( 'Default', 'elementor' ),
					'style_one' => PrestaHelper::__( 'Classic', 'elementor' ),
				),
				'default' => 'default',
			)
		);

		$this->add_control(
			'classic_skin',
			array(
				'label'      => PrestaHelper::__( 'Skin', 'elementor' ),
				'type'       => Controls_Manager::SELECT,
				'options'    => array(
					'skin_one'   => PrestaHelper::__( 'One', 'elementor' ),
					'skin_two'   => PrestaHelper::__( 'Two', 'elementor' ),
					'skin_three' => PrestaHelper::__( 'Three', 'elementor' ),
				),
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'     => 'layout',
							'operator' => '==',
							'value'    => 'style_one',
						),
					),
				),
				'default'    => 'skin_one',
			)
		);

		$this->add_control(
			'column_width',
			array(
				'label'      => PrestaHelper::__( 'Column', 'elementor' ),
				'type'       => Controls_Manager::SELECT,
				'options'    => array(
					'col-lg-2 col-md-6 col-sm-12 col-xs-12'  => PrestaHelper::__( 'Six', 'elementor' ),
					'col-lg-3 col-md-6 col-sm-12 col-xs-12'  => PrestaHelper::__( 'Four', 'elementor' ),
					'col-lg-4 col-md-6 col-sm-12 col-xs-12'  => PrestaHelper::__( 'Three', 'elementor' ),
					'col-lg-6 col-md-6 col-sm-12 col-xs-12'  => PrestaHelper::__( 'Two', 'elementor' ),
					'col-lg-12 col-md-12 col-sm-12 col-xs-12' => PrestaHelper::__( 'One', 'elementor' ),
				),
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'     => 'layout',
							'operator' => '==',
							'value'    => 'style_one',
						),
					),
				),
				'default'    => 'col-lg-4',
			)
		);

		$this->add_control(
			'title',
			array(
				'label'       => PrestaHelper::__( 'Title', 'elementor' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => array(
					'active' => true,
				),
				'label_block' => true,
			)
		);

		$this->add_control(
			'per_page',
			array(
				'label'   => PrestaHelper::__( 'Per Page', 'elementor' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 3,
			)
		);

		$this->add_control(
			'random_query',
			array(
				'label'   => PrestaHelper::__( 'Random?', 'elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'dynamic' => array(
					'active' => true,
				),
				'default' => false,
			)
		);

		$this->add_control(
			'id_category',
			array(
				'label'     => PrestaHelper::__( 'Select category', 'elementor' ),
				'type'      => Controls_Manager::AUTOCOMPLETE,
				'item_type' => 'category',
				'multiple'  => false,
			)
		);

		$this->add_control(
			'orderby',
			array(
				'label'   => PrestaHelper::__( 'Order by', 'elementor' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'id_product'   => PrestaHelper::__( 'Product Id', 'elementor' ),
					'price'        => PrestaHelper::__( 'Price', 'elementor' ),
					'date_add'     => PrestaHelper::__( 'Published Date', 'elementor' ),
					'name'         => PrestaHelper::__( 'Product Name', 'elementor' ),
					'position'     => PrestaHelper::__( 'Position', 'elementor' ),
					'manufacturer' => PrestaHelper::__( 'Manufacturer', 'elementor' ),
				),
				'default' => 'id_product',
				'conditions' => array(
					'terms'    => array(
						array(
							'name'     => 'random_query',
							'operator' => '!=',
							'value'    => 'yes',
						),
					),
				),
			)
		);

		$this->add_control(
			'order',
			array(
				'label'   => PrestaHelper::__( 'Order', 'elementor' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'DESC' => PrestaHelper::__( 'DESC', 'elementor' ),
					'ASC'  => PrestaHelper::__( 'ASC', 'elementor' ),
				),
				'default' => 'ASC',
				'conditions' => array(
					'terms'    => array(
						array(
							'name'     => 'random_query',
							'operator' => '!=',
							'value'    => 'yes',
						),
					),
				),
			)
		);

		$this->add_control(
			'display_type',
			array(
				'label'   => PrestaHelper::__( 'Display Type', 'elementor' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'grid'    => PrestaHelper::__( 'Grid View', 'elementor' ),
					'sidebar' => PrestaHelper::__( 'Sidebar View', 'elementor' ),
				),
				'default' => 'grid',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'features',
			array(
				'label'     => PrestaHelper::__( 'Features', 'elementor' ),
				'condition' => array(
					'layout' => 'style_one',
				),
			)
		);

		$this->add_control(
			'ed_short_desc',
			array(
				'label'   => PrestaHelper::__( 'Short Description', 'elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'dynamic' => array(
					'active' => true,
				),
				'default' => 'yes',
			)
		);
		$this->add_control(
			'ed_desc',
			array(
				'label'   => PrestaHelper::__( 'Description', 'elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'dynamic' => array(
					'active' => true,
				),
				'default' => 'yes',
			)
		);
		$this->add_control(
			'ed_manufacture',
			array(
				'label'   => PrestaHelper::__( 'Manufacture', 'elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'dynamic' => array(
					'active' => true,
				),
				'default' => 'yes',
			)
		);
		$this->add_control(
			'ed_supplier',
			array(
				'label'   => PrestaHelper::__( 'Supplier', 'elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'dynamic' => array(
					'active' => true,
				),
				'default' => 'yes',
			)
		);
		$this->add_control(
			'ed_catagories',
			array(
				'label'   => PrestaHelper::__( 'Catagories', 'elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'dynamic' => array(
					'active' => true,
				),
				'default' => 'yes',
			)
		);

		$this->add_control(
			'quantity_spin',
			array(
				'label'   => PrestaHelper::__( 'Quantity Selector', 'elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'dynamic' => array(
					'active' => true,
				),
				'default' => false,
			)
		);

		$this->add_control(
			'ed_dis_percent',
			array(
				'label'   => PrestaHelper::__( 'Show Discount Percentage', 'elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'dynamic' => array(
					'active' => true,
				),
				'default' => 'yes',
			)
		);

		$this->add_control(
			'ed_dis_amount',
			array(
				'label'   => PrestaHelper::__( 'Show Discount Amount', 'elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'dynamic' => array(
					'active' => true,
				),
				'default' => 'yes',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'product_section',
			array(
				'label'      => PrestaHelper::__( 'Product Section', 'elementor' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'     => 'layout',
							'operator' => '==',
							'value'    => 'style_one',
						),
					),
				),
			)
		);

		$this->add_control(
			'product_inner_bg',
			array(
				'label'     => PrestaHelper::__( 'Background', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .product_inner ,{{WRAPPER}} .ce_pr.skin_two .ce_pr_row .product_desc, {{WRAPPER}} .ce_pr.skin_three .ce_pr_row .product_desc' => 'background: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'product_inner_shadow',
				'selector' => '{{WRAPPER}} .product_inner',
			)
		);

		$this->add_responsive_control(
			'product_inner_radius',
			array(
				'label'      => PrestaHelper::__( 'Border Radius', 'elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'devices'    => array( 'desktop', 'tablet', 'mobile' ),
				'selectors'  => array(
					'{{WRAPPER}} .product_inner' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'title_typo',
			array(
				'label'      => PrestaHelper::__( 'TItle', 'elementor' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'     => 'layout',
							'operator' => '==',
							'value'    => 'style_one',
						),
					),
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'Title',
				'label'    => PrestaHelper::__( 'Typography', 'elementor' ),
				'selector' => '{{WRAPPER}} .product_desc .name',
				'scheme'   => Scheme_Typography::TYPOGRAPHY_1,
			)
		);

		$this->add_control(
			'name_color',
			array(
				'label'     => PrestaHelper::__( 'Color', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .product_desc .name' => 'color: {{VALUE}};',
				),
				'separator' => 'after',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'product_flag',
			array(
				'label'      => PrestaHelper::__( 'Flag', 'elementor' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'     => 'layout',
							'operator' => '==',
							'value'    => 'style_one',
						),
					),
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'product_flag_typo',
				'label'    => PrestaHelper::__( 'Typography', 'elementor' ),
				'selector' => '{{WRAPPER}} .product_inner .product_flag p',
				'scheme'   => Scheme_Typography::TYPOGRAPHY_1,
			)
		);

		$this->add_control(
			'product_flag_color',
			array(
				'label'     => PrestaHelper::__( 'Color', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .product_inner .product_flag p' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'product_flag_bg',
			array(
				'label'     => PrestaHelper::__( 'Background', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .product_inner .product_flag p' => 'background: {{VALUE}};',
				),
				'separator' => 'after',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'atc_btn',
			array(
				'label'      => PrestaHelper::__( 'Cart Button', 'elementor' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'     => 'layout',
							'operator' => '==',
							'value'    => 'style_one',
						),
					),
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'atc_btn_typo',
				'label'    => PrestaHelper::__( 'Typography', 'elementor' ),
				'selector' => '{{WRAPPER}} .product_inner .add_to_cart .add_to_cart_btn,{{WRAPPER}} .product_inner .add_to_cart .avail_msg',
				'scheme'   => Scheme_Typography::TYPOGRAPHY_1,
			)
		);

		$this->add_control(
			'atc_btn_icon',
			array(
				'label'     => PrestaHelper::__( 'Icon Size', 'elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'default'   => array(
					'unit' => 'px',
					'size' => 20,
				),
				'selectors' => array(
					'{{WRAPPER}} .product_inner .add_to_cart i' => 'font-size: {{SIZE}}{{UNIT}};',
				),

			)
		);

		$this->add_control(
			'atc_btn_color',
			array(
				'label'     => PrestaHelper::__( 'Color', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .product_inner .add_to_cart' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'atc_btn_bg',
			array(
				'label'     => PrestaHelper::__( 'Background', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .product_inner .add_to_cart' => 'background: {{VALUE}};',
				),
				'separator' => 'after',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'short_desc_typo',
			array(
				'label'      => PrestaHelper::__( 'Short Description', 'elementor' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'conditions' => array(
					'relation' => 'and',
					'terms'    => array(
						array(
							'name'     => 'layout',
							'operator' => '==',
							'value'    => 'style_one',
						),
						array(
							'name'     => 'ed_short_desc',
							'operator' => '==',
							'value'    => 'yes',
						),
					),
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'short_desc_typo',
				'label'    => PrestaHelper::__( 'Typography', 'elementor' ),
				'selector' => '{{WRAPPER}} .product_desc .description_short',
				'scheme'   => Scheme_Typography::TYPOGRAPHY_1,
			)
		);

		$this->add_control(
			'short_desc_color',
			array(
				'label'     => PrestaHelper::__( 'Color', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .product_desc .description_short' => 'color: {{VALUE}};',
				),
				'separator' => 'after',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'desc_typo',
			array(
				'label'      => PrestaHelper::__( 'Description', 'elementor' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'conditions' => array(
					'relation' => 'and',
					'terms'    => array(
						array(
							'name'     => 'layout',
							'operator' => '==',
							'value'    => 'style_one',
						),
						array(
							'name'     => 'ed_desc',
							'operator' => '==',
							'value'    => 'yes',
						),
					),
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'desc_typo',
				'label'    => PrestaHelper::__( 'Typography', 'elementor' ),
				'selector' => '{{WRAPPER}} .product_desc .description',
				'scheme'   => Scheme_Typography::TYPOGRAPHY_1,
			)
		);

		$this->add_control(
			'desc_color',
			array(
				'label'     => PrestaHelper::__( 'Color', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .product_desc .description' => 'color: {{VALUE}};',
				),
				'separator' => 'after',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'manufacturer_typo',
			array(
				'label'      => PrestaHelper::__( 'Manufacturer', 'elementor' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'conditions' => array(
					'relation' => 'and',
					'terms'    => array(
						array(
							'name'     => 'layout',
							'operator' => '==',
							'value'    => 'style_one',
						),
						array(
							'name'     => 'ed_manufacture',
							'operator' => '==',
							'value'    => 'yes',
						),
					),
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'manufacturer_name',
				'label'    => PrestaHelper::__( 'Typography', 'elementor' ),
				'selector' => '{{WRAPPER}} .texonom .manufacturer_name',
				'scheme'   => Scheme_Typography::TYPOGRAPHY_1,
			)
		);
		$this->add_control(
			'manufacturer_color',
			array(
				'label'     => PrestaHelper::__( 'Color', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .texonom .manufacturer_name' => 'color: {{VALUE}};',
				),
			)
		);
		$this->add_control(
			'manufacturer_bg',
			array(
				'label'     => PrestaHelper::__( 'Background', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .texonom .manufacturer_name' => 'background: {{VALUE}};',
				),
				'separator' => 'after',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'Supplier_typo',
			array(
				'label'      => PrestaHelper::__( 'Supplier', 'elementor' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'conditions' => array(
					'relation' => 'and',
					'terms'    => array(
						array(
							'name'     => 'layout',
							'operator' => '==',
							'value'    => 'style_one',
						),
						array(
							'name'     => 'ed_supplier',
							'operator' => '==',
							'value'    => 'yes',
						),

					),
				),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'supplier_name',
				'label'    => PrestaHelper::__( 'Typography', 'elementor' ),
				'selector' => '{{WRAPPER}} .texonom .supplier_name',
				'scheme'   => Scheme_Typography::TYPOGRAPHY_1,
			)
		);
		$this->add_control(
			'supplier_color',
			array(
				'label'     => PrestaHelper::__( 'Color', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .texonom .supplier_name' => 'color: {{VALUE}};',
				),
			)
		);
		$this->add_control(
			'supplier_bg',
			array(
				'label'     => PrestaHelper::__( 'Background', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .texonom .supplier_name' => 'background: {{VALUE}};',
				),
				'separator' => 'after',
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'category_typo',
			array(
				'label'      => PrestaHelper::__( 'Category', 'elementor' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'conditions' => array(
					'relation' => 'and',
					'terms'    => array(
						array(
							'name'     => 'layout',
							'operator' => '==',
							'value'    => 'style_one',
						),
						array(
							'name'     => 'ed_catagories',
							'operator' => '==',
							'value'    => 'yes',
						),
					),
				),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'category_name',
				'label'    => PrestaHelper::__( 'Typography', 'elementor' ),
				'selector' => '{{WRAPPER}} .texonom .category_name',
				'scheme'   => Scheme_Typography::TYPOGRAPHY_1,
			)
		);
		$this->add_control(
			'Category_color',
			array(
				'label'     => PrestaHelper::__( 'Color', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .texonom .category_name' => 'color: {{VALUE}};',
				),
			)
		);
		$this->add_control(
			'Category_bg',
			array(
				'label'     => PrestaHelper::__( 'Background', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .texonom .category_name' => 'background: {{VALUE}};',
				),
				'separator' => 'after',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'price_typo',
			array(
				'label'      => PrestaHelper::__( 'Price', 'elementor' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'     => 'layout',
							'operator' => '==',
							'value'    => 'style_one',
						),
					),
				),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'price_name',
				'label'    => PrestaHelper::__( 'Typography', 'elementor' ),
				'selector' => '{{WRAPPER}} .product_desc .product_info p',
				'scheme'   => Scheme_Typography::TYPOGRAPHY_1,
			)
		);

		$this->add_responsive_control(
			'price_padding',
			array(
				'label'      => PrestaHelper::__( 'Padding', 'elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .product_desc .product_info p' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'after',
			)
		);
		$this->add_control(
			'price_color',
			array(
				'label'     => PrestaHelper::__( 'Price Color', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .product_desc .product_info .regular_price, {{WRAPPER}} .product_desc .product_info .price' => 'color: {{VALUE}};',
				),
			)
		);
		$this->add_control(
			'price_bg',
			array(
				'label'     => PrestaHelper::__( 'Background', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .product_desc .product_info .regular_price, {{WRAPPER}} .product_desc .product_info .price' => 'background: {{VALUE}};',
				),
				'separator' => 'after',
			)
		);
		$this->add_control(
			'discount_price_color',
			array(
				'label'     => PrestaHelper::__( 'Discount Color', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .product_desc .product_info .has_discount' => 'color: {{VALUE}};',
				),
			)
		);
		$this->add_control(
			'discount_price_bg',
			array(
				'label'     => PrestaHelper::__( 'Discount Background', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .product_desc .product_info .has_discount' => 'background: {{VALUE}};',
				),
				'separator' => 'after',
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

		$settings     = $this->get_settings_for_display();
		$layout       = $settings['layout'];
		$title        = $settings['title'];
		$id_category  = $settings['id_category'];
		
		$id_category = explode('_',$id_category);
		if(isset($id_category) && is_array($id_category)){
			$id_category = $id_category[0];
		}else{
			$id_category             = '';
		}
		$limit     = $settings['per_page'];
		$orderby      = $settings['orderby'];
		$random_query      = $settings['random_query'];
		$order        = $settings['order'];
		$display_type = $settings['display_type'];
		$quantity_spin = $settings['quantity_spin'];
		$page         = 0;
		$context      = \Context::getContext();
		$output       = '';

		$id_lang     = $context->language->id;
		$id_shop     = $context->shop->id;
		$id_supplier = '';
		$active      = true;
		$front       = true;
		$context->controller->addJqueryUI(array('ui.spinner'));
		$classic_skin = $settings['classic_skin'];
		$column_width = $settings['column_width'];
		// Style Control
		$ed_short_desc  = $settings['ed_short_desc'];
		$ed_desc        = $settings['ed_desc'];
		$ed_dis_amount  = $settings['ed_dis_amount'];
		$ed_dis_percent  = $settings['ed_dis_percent'];
		$ed_manufacture = $settings['ed_manufacture'];
		$ed_supplier    = $settings['ed_supplier'];
		$ed_catagories  = $settings['ed_catagories'];
		$cache_products = array();
		if($random_query != 'yes'){
				$sql = 'SELECT p.*, product_shop.*, stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity, MAX(product_attribute_shop.id_product_attribute) id_product_attribute, product_attribute_shop.minimal_quantity AS product_attribute_minimal_quantity, pl.`description`, pl.`description_short`, pl.`available_now`,
										pl.`available_later`, pl.`link_rewrite`, pl.`meta_description`, pl.`meta_keywords`, pl.`meta_title`, pl.`name`, MAX(image_shop.`id_image`) id_image,
										il.`legend`, m.`name` AS manufacturer_name, cl.`name` AS category_default,
										DATEDIFF(product_shop.`date_add`, DATE_SUB(NOW(),
										INTERVAL ' . ( \Validate::isUnsignedInt( \Configuration::get( 'PS_NB_DAYS_NEW_PRODUCT' ) ) ? \Configuration::get( 'PS_NB_DAYS_NEW_PRODUCT' ) : 20 ) . '
							DAY)) > 0 AS new, product_shop.price AS orderprice
					FROM `' . _DB_PREFIX_ . 'category_product` cp
					LEFT JOIN `' . _DB_PREFIX_ . 'product` p
						ON p.`id_product` = cp.`id_product`
					' . \Shop::addSqlAssociation( 'product', 'p' ) . '
					LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute` pa
					ON (p.`id_product` = pa.`id_product`)
					' . \Shop::addSqlAssociation( 'product_attribute', 'pa', false, 'product_attribute_shop.`default_on` = 1' ) . '
					' . \Product::sqlStock( 'p', 'product_attribute_shop', false, $context->shop ) . '
					LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl
						ON (product_shop.`id_category_default` = cl.`id_category`
						AND cl.`id_lang` = ' . (int) $id_lang . \Shop::addSqlRestrictionOnLang( 'cl' ) . ')
					LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
						ON (p.`id_product` = pl.`id_product`
						AND pl.`id_lang` = ' . (int) $id_lang . \Shop::addSqlRestrictionOnLang( 'pl' ) . ')
					LEFT JOIN `' . _DB_PREFIX_ . 'image` i
						ON (i.`id_product` = p.`id_product`)' .
			\Shop::addSqlAssociation( 'image', 'i', false, 'image_shop.cover=1' ) . '
					LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il
						ON (image_shop.`id_image` = il.`id_image`
						AND il.`id_lang` = ' . (int) $id_lang . ')
					LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m
						ON m.`id_manufacturer` = p.`id_manufacturer`
					WHERE product_shop.`id_shop` = ' . (int) $context->shop->id . '
						AND cp.`id_category` = ' . (int) $id_category
			. ( $active ? ' AND product_shop.`active` = 1' : '' )
			. ( $front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '' )
			. ( $id_supplier ? ' AND p.id_supplier = ' . (int) $id_supplier : '' )
			. ' GROUP BY product_shop.id_product';

			if ( empty( $orderby ) || $orderby == 'position' ) {
				$order_by_prefix = 'cp';
			}
			if ( empty( $order ) ) {
				$order = 'DESC';
			}
			if ( $orderby == 'id_product' || $orderby == 'price' || $orderby == 'date_add' || $orderby == 'date_upd' ) {
				$order_by_prefix = 'p';
			} elseif ( $orderby == 'name' ) {
				$order_by_prefix = 'pl';
			}

			$sql .= " ORDER BY {$order_by_prefix}.{$orderby} {$order}";

			if ( ! empty( $limit ) && is_numeric( $limit ) ) {
				$sql .= " LIMIT {$limit}";
			}
			$cache_id = md5( $sql );
			if ( ! \Cache::isStored( $cache_id ) ) {
				$result = \Db::getInstance( _PS_USE_SQL_SLAVE_ )->executeS( $sql );
				if ( ! $result ) {
					return array();
				}
				$outputs = \Product::getProductsProperties( $id_lang, $result );
				\Cache::store( $cache_id, $outputs );
			}
			$cache_products = \Cache::retrieve( $cache_id );

		}else{

			if(!isset($id_category) || $id_category == ''){
				$id_category = $context->shop->getCategory();
			}

			$category       = new \Category( $id_category, (int) $context->language->id );
			$cache_products = $category->getProducts( (int) $context->language->id, 1, $limit, $orderby, $order, false, true, (bool) true,  $limit);
		}

		if ( ! $cache_products ) {
			return false;
		}

		$assembler = new \ProductAssembler( $context );

		$presenterFactory     = new \ProductPresenterFactory( $context );
		$presentationSettings = $presenterFactory->getPresentationSettings();
		$presenter            = new \PrestaShop\PrestaShop\Core\Product\ProductListingPresenter(
			new \PrestaShop\PrestaShop\Adapter\Image\ImageRetriever(
				$context->link
			),
			$context->link,
			new \PrestaShop\PrestaShop\Adapter\Product\PriceFormatter(),
			new \PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever(),
			$context->getTranslator()
		);

		$products_for_template = array();

		foreach ( $cache_products as $rawProduct ) {
			$products_for_template[] = $presenter->present(
				$presentationSettings,
				$assembler->assembleProduct( $rawProduct ),
				$context->language
			);
		}

		$out_put = '';
		if ( $layout == 'default' ) {
			$context->smarty->assign(
				array(
					'vc_products'         => $products_for_template,
					'vc_title'            => $title,
					'elementprefix'       => 'productbycat',
					'theme_template_path' => _PS_THEME_DIR_ . 'templates/catalog/_partials/miniatures/product.tpl',
	
				)
			);
	
			
			if ( $display_type == 'sidebar' ) {
				$out_put = $context->smarty->fetch( CRAZY_PATH . 'views/templates/front/blockviewed.tpl' );
			} else {
	
				$out_put = $context->smarty->fetch( CRAZY_PATH . 'views/templates/front/blocknewproducts.tpl' );
			}
		} else {

			if($random_query == 'yes'){
				if(!isset($id_category) || $id_category == ''){
					$id_category = $context->shop->getCategory();
				}
	
				$category       = new \Category( $id_category, (int) $context->language->id );
				$from_cat_addon = $category->name;
			}else{
				$from_cat_addon = false;
			}
			$context->smarty->assign(
				array(
					'crazy_products' => $products_for_template,
					'vc_title'       => $title,
					'elementprefix'  => 'single-product',
					'skin_class'     => $classic_skin,
					'ed_short_desc'  => $ed_short_desc,
					'ed_dis_amount'  => $ed_dis_amount,
					'ed_dis_percent'  => $ed_dis_percent,
					'column_width'   => $column_width,
					'ed_desc'        => $ed_desc,
					'ed_manufacture' => $ed_manufacture,
					'ed_catagories'  => $ed_catagories,
					'quantity_spin'  => $quantity_spin,
					'from_cat_addon'  => $from_cat_addon,
				)
			);
			$template_file_name = CRAZY_PATH . 'views/templates/front/products/products_skin_one.tpl';
			$out_put           .= $context->smarty->fetch( $template_file_name );	
		}
		echo $out_put;		
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
}