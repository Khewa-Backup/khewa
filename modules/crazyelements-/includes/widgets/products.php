<?php
namespace CrazyElements;

use CrazyElements\PrestaHelper;
use CrazyElements\Widget_Base;

if ( ! defined( '_PS_VERSION_' ) ) {
	exit; // Exit if accessed directly.
}


class Widget_Products extends Widget_Base {


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
		return 'products';
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
		return PrestaHelper::__( 'Products', 'elementor' );
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
		return 'ceicon-products-widget';
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
			'ids',
			array(
				'label'     => PrestaHelper::__( 'Select products', 'elementor' ),
				'type'      => Controls_Manager::AUTOCOMPLETE,
				'item_type' => 'product',
				'multiple'  => true,
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

	protected function getIdFromTitle($ids) {
		$array=array();
		foreach($ids as $id){
			$exp=explode('_',$id);
			$array[]=$exp[0];
		}
		return $array;
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
		$title        = $settings['title'];
		$layout       = $settings['layout'];
		$orderby      = $settings['orderby'];
		$order        = $settings['order'];
		$ids          = $settings['ids'];
		$classic_skin = $settings['classic_skin'];
		$column_width = $settings['column_width'];
		// Style Control
		$ed_short_desc  = $settings['ed_short_desc'];
		$ed_desc        = $settings['ed_desc'];
		$ed_manufacture = $settings['ed_manufacture'];
		$ed_supplier    = $settings['ed_supplier'];
		$ed_catagories  = $settings['ed_catagories'];
		$ed_dis_amount  = $settings['ed_dis_amount'];
		$ed_dis_percent  = $settings['ed_dis_percent'];
		$quantity_spin = $settings['quantity_spin'];
		// Style Control
		$context = \Context::getContext();
		$out_put = '';

		$context->controller->addCSS( CRAZY_PATH . 'assets/css/widgetonload/products_skin.css' );
		$context->controller->addCSS( _THEME_CSS_DIR_ . 'product.css' );
		$context->controller->addCSS( _THEME_CSS_DIR_ . 'product_list.css' );
		$context->controller->addCSS( _THEME_CSS_DIR_ . 'print.css', 'print' );
		$context->controller->addJqueryPlugin( array( 'fancybox', 'idTabs', 'scrollTo', 'serialScroll', 'bxslider' ) );
		$context->controller->addJqueryUI(array('ui.spinner'));
		$context->controller->addJS(
			array(
				_THEME_JS_DIR_ . 'tools.js',
			)
		);

		$id_lang = $context->language->id;
		$front   = true;
		if ( ! in_array( $context->controller->controller_type, array( 'front', 'modulefront' ) ) ) {
			$front = false;
		}

		if ( ! empty( $ids ) ) {
			
			$ids=$this->getIdFromTitle($ids);

			$str             = implode( ',', $ids );
			$order_by_prefix = '';
			if ( $orderby == 'id_product' || $orderby == 'price' || $orderby == 'date_add' || $orderby == 'date_upd' ) {
				$order_by_prefix = 'p';
			} elseif ( $orderby == 'name' ) {
				$order_by_prefix = 'pl';
			}

			$sql = 'SELECT p.*, product_shop.*, pl.*, image_shop.`id_image`, il.`legend`, m.`name` AS manufacturer_name, s.`name` AS supplier_name
				FROM `' . _DB_PREFIX_ . 'product` p
				' . \Shop::addSqlAssociation( 'product', 'p' ) . '
				LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (p.`id_product` = pl.`id_product` ' . \Shop::addSqlRestrictionOnLang( 'pl' ) . ')
				LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON (m.`id_manufacturer` = p.`id_manufacturer`)
				LEFT JOIN `' . _DB_PREFIX_ . 'supplier` s ON (s.`id_supplier` = p.`id_supplier`)
                                LEFT JOIN `' . _DB_PREFIX_ . 'image` i ON (i.`id_product` = p.`id_product`)' .
			\Shop::addSqlAssociation( 'image', 'i', false, 'image_shop.cover=1' ) . '
				LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = ' . (int) \Context::getContext()->language->id . ')
				WHERE pl.`id_lang` = ' . (int) $id_lang .
			' AND p.`id_product` IN( ' . $str . ')' .
			( $front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '' ) .
			' AND ((image_shop.id_image IS NOT NULL OR i.id_image IS NULL) OR (image_shop.id_image IS NULL AND i.cover=1))' .
			' AND product_shop.`active` = 1';

			if ( ! empty( $orderby ) && isset( $order_by_prefix ) ) {
				$sql .= " ORDER BY {$order_by_prefix}.{$orderby} {$order}";
			}

			$rq = \Db::getInstance( _PS_USE_SQL_SLAVE_ )->executeS( $sql );

			$product = \Product::getProductsProperties( $id_lang, $rq );

			if ( ! $product ) {
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

			foreach ( $product as $rawProduct ) {
				$products_for_template[] = $presenter->present(
					$presentationSettings,
					$assembler->assembleProduct( $rawProduct ),
					$context->language
				);
			}

			if ( $layout == 'default' ) {
				$context->smarty->assign(
					array(
						'vc_products'         => $products_for_template,
						'vc_title'            => $title,
						'elementprefix'       => 'single-product',
						'theme_template_path' => _PS_THEME_DIR_ . 'templates/catalog/_partials/miniatures/product.tpl',
					)
				);
				$template_file_name = CRAZY_PATH . 'views/templates/front/blocknewproducts.tpl';
				$out_put           .= $context->smarty->fetch( $template_file_name );
			} else {
				$from_cat_addon = false;
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