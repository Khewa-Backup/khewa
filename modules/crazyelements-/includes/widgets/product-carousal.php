<?php
namespace CrazyElements;

use CrazyElements\PrestaHelper;
use CrazyElements\Widget_Base;

if ( ! defined( '_PS_VERSION_' ) ) {
	exit; // Exit if accessed directly.
}

class Widget_ProductCarousal extends Widget_Base {


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
		return 'products_carousal';
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
		return PrestaHelper::__( 'Products Carousal', 'elementor' );
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
		return 'ceicon-products-carousal-widget';
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
				'description' =>  PrestaHelper::__( 'Type Product Name and Select', 'elementor' ), 
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
			'carousal_settings',
			array(
				'label' => PrestaHelper::__( 'Carousel Settings', 'elementor' ),
			)
		);

		$slides_to_show = range( 1, 10 );
		$slides_to_show = array_combine( $slides_to_show, $slides_to_show );

		$this->add_responsive_control(
			'slides_to_show',
			array(
				'label'              => PrestaHelper::__( 'Slides to Show', 'elementor' ),
				'type'               => Controls_Manager::SELECT,
				'options'            => array(
					'' => PrestaHelper::__( 'Default', 'elementor' ),
				) + $slides_to_show,
				'frontend_available' => true,
			)
		);

		$this->add_responsive_control(
			'slides_to_scroll',
			array(
				'label'              => PrestaHelper::__( 'Slides to Scroll', 'elementor' ),
				'type'               => Controls_Manager::SELECT,
				'description'        => PrestaHelper::__( 'Set how many slides are scrolled per swipe.', 'elementor' ),
				'options'            => array(
					'' => PrestaHelper::__( 'Default', 'elementor' ),
				) + $slides_to_show,
				'condition'          => array(
					'slides_to_show!' => '1',
				),
				'frontend_available' => true,
			)
		);

		$this->add_control(
			'navigation',
			array(
				'label'              => PrestaHelper::__( 'Navigation', 'elementor' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'both',
				'options'            => array(
					'both'   => PrestaHelper::__( 'Arrows and Dots', 'elementor' ),
					'arrows' => PrestaHelper::__( 'Arrows', 'elementor' ),
					'dots'   => PrestaHelper::__( 'Dots', 'elementor' ),
					'none'   => PrestaHelper::__( 'None', 'elementor' ),
				),
				'frontend_available' => true,
			)
		);

		$this->add_control(
			'pause_on_hover',
			array(
				'label'              => PrestaHelper::__( 'Pause on Hover', 'elementor' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'yes',
				'options'            => array(
					'yes' => PrestaHelper::__( 'Yes', 'elementor' ),
					'no'  => PrestaHelper::__( 'No', 'elementor' ),
				),
				'frontend_available' => true,
			)
		);

		$this->add_control(
			'autoplay',
			array(
				'label'              => PrestaHelper::__( 'Autoplay', 'elementor' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'yes',
				'options'            => array(
					'yes' => PrestaHelper::__( 'Yes', 'elementor' ),
					'no'  => PrestaHelper::__( 'No', 'elementor' ),
				),
				'frontend_available' => true,
			)
		);

		$this->add_control(
			'autoplay_speed',
			array(
				'label'              => PrestaHelper::__( 'Autoplay Speed', 'elementor' ),
				'type'               => Controls_Manager::NUMBER,
				'default'            => 5000,
				'frontend_available' => true,
			)
		);

		$this->add_control(
			'infinite',
			array(
				'label'              => PrestaHelper::__( 'Infinite Loop', 'elementor' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'yes',
				'options'            => array(
					'yes' => PrestaHelper::__( 'Yes', 'elementor' ),
					'no'  => PrestaHelper::__( 'No', 'elementor' ),
				),
				'frontend_available' => true,
			)
		);

		$this->add_control(
			'effect',
			array(
				'label'              => PrestaHelper::__( 'Effect', 'elementor' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'slide',
				'options'            => array(
					'slide' => PrestaHelper::__( 'Slide', 'elementor' ),
					'fade'  => PrestaHelper::__( 'Fade', 'elementor' ),
				),
				'condition'          => array(
					'slides_to_show' => '1',
				),
				'frontend_available' => true,
			)
		);

		$this->add_control(
			'speed',
			array(
				'label'              => PrestaHelper::__( 'Animation Speed', 'elementor' ),
				'type'               => Controls_Manager::NUMBER,
				'default'            => 500,
				'frontend_available' => true,
			)
		);

		$this->add_control(
			'direction',
			array(
				'label'              => PrestaHelper::__( 'Direction', 'elementor' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'ltr',
				'options'            => array(
					'ltr' => PrestaHelper::__( 'Left', 'elementor' ),
					'rtl' => PrestaHelper::__( 'Right', 'elementor' ),
				),
				'frontend_available' => true,
			)
		);

		$this->end_controls_section();
		$this->start_controls_section(
			'section_style_product_box',
			array(
				'label' => PrestaHelper::__( 'Product Box', 'elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,

			)
		);
		$this->add_responsive_control(
			'padding',
			array(
				'label'      => PrestaHelper::__( 'Padding', 'elecounter' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'devices'    => array( 'desktop', 'tablet', 'mobile' ),
				'selectors'  => array(
					'{{WRAPPER}} .product-miniature' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->add_responsive_control(
			'margin',
			array(
				'label'      => PrestaHelper::__( 'Margin', 'elecounter' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'devices'    => array( 'desktop', 'tablet', 'mobile' ),
				'selectors'  => array(
					'{{WRAPPER}} .product-miniature' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->end_controls_section();
		$this->start_controls_section(
			'section_style_product_image',
			array(
				'label' => PrestaHelper::__( 'Product Image', 'elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,

			)
		);
		$this->add_responsive_control(
			'img_padding',
			array(
				'label'      => PrestaHelper::__( 'Padding', 'elecounter' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'devices'    => array( 'desktop', 'tablet', 'mobile' ),
				'selectors'  => array(
					'{{WRAPPER}} .product-miniature .thumbnail-container .product-thumbnail img' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'border',
				'label'    => PrestaHelper::__( 'Border', 'plugin-domain' ),
				'selector' => '{{WRAPPER}} .product-miniature .thumbnail-container .product-thumbnail img',
			)
		);
		$this->end_controls_section();
		$this->start_controls_section(
			'section_style_product_content',
			array(
				'label' => PrestaHelper::__( 'Content', 'elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_responsive_control(
			'alignment',
			array(
				'label'        => PrestaHelper::__( 'Alignment', 'elecounter' ),
				'type'         => Controls_Manager::CHOOSE,
				'devices'      => array( 'desktop', 'tablet', 'mobile' ),
				'options'      => array(
					'left'    => array(
						'title' => PrestaHelper::__( 'Left', 'elecounter' ),
						'icon'  => 'fa fa-align-left',
					),
					'center'  => array(
						'title' => PrestaHelper::__( 'Center', 'elecounter' ),
						'icon'  => 'fa fa-align-center',
					),
					'right'   => array(
						'title' => PrestaHelper::__( 'Right', 'elecounter' ),
						'icon'  => 'fa fa-align-right',
					),
					'justify' => array(
						'title' => PrestaHelper::__( 'Justify', 'elecounter' ),
						'icon'  => 'fa fa-align-justify',
					),
				),
				'prefix_class' => 'alignment%s',
			)
		);
		$this->start_controls_tabs( 'product_style' );
		$this->start_controls_tab(
			'product_title',
			array(
				'label' => PrestaHelper::__( 'Title', 'elementor' ),
			)
		);
		$this->add_control(
			'title_color',
			array(
				'label'     => PrestaHelper::__( 'Title Color', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .product-miniature .product-title a' => 'color: {{VALUE}};',
				),
			)
		);
		$this->add_control(
			'title_hover_color',
			array(
				'label'     => PrestaHelper::__( 'Title Hover Color', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .product-miniature .product-title a:hover' => 'color: {{VALUE}};',
				),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'label'    => PrestaHelper::__( 'Title Typography', 'elementor' ),
				'selector' => '{{WRAPPER}} .product-miniature .product-title a',
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab(
			'product_price',
			array(
				'label' => PrestaHelper::__( 'Price', 'elementor' ),
			)
		);

		$this->add_control(
			'price_color',
			array(
				'label'     => PrestaHelper::__( 'Price Color', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .product-miniature .product-price-and-shipping' => 'color: {{VALUE}};',
				),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'product_price_typography',
				'label'    => PrestaHelper::__( 'Price Typography', 'elementor' ),
				'selector' => '{{WRAPPER}} .product-miniature .product-price-and-shipping',
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab(
			'product_quick_view',
			array(
				'label' => PrestaHelper::__( 'Quickview', 'elementor' ),
			)
		);

		$this->add_control(
			'product_quick_view_color',
			array(
				'label'     => PrestaHelper::__( 'Quickview Color', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .highlighted-informations .quick-view' => 'color: {{VALUE}};',
				),
			)
		);
		$this->add_control(
			'product_quick_view_hover_color',
			array(
				'label'     => PrestaHelper::__( 'Quickview Hover Color', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .highlighted-informations .quick-view:hover' => 'color: {{VALUE}};',
				),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'product_quick_view_typography',
				'label'    => PrestaHelper::__( 'Quickview Typography', 'elementor' ),
				'selector' => '{{WRAPPER}} .highlighted-informations .quick-view',
			)
		);
		$this->end_controls_tab();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_navigation',
			array(
				'label'     => PrestaHelper::__( 'Navigation', 'elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'navigation' => array( 'arrows', 'dots', 'both' ),
				),
			)
		);

		$this->add_control(
			'heading_style_arrows',
			array(
				'label'     => PrestaHelper::__( 'Arrows', 'elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'navigation' => array( 'arrows', 'both' ),
				),
			)
		);

		$this->add_control(
			'arrows_position',
			array(
				'label'     => PrestaHelper::__( 'Position', 'elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'inside',
				'options'   => array(
					'inside'  => PrestaHelper::__( 'Inside', 'elementor' ),
					'outside' => PrestaHelper::__( 'Outside', 'elementor' ),
				),
				'condition' => array(
					'navigation' => array( 'arrows', 'both' ),
				),
			)
		);

		$this->add_control(
			'arrows_size',
			array(
				'label'     => PrestaHelper::__( 'Size', 'elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 20,
						'max' => 60,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .elementor-image-carousel-wrapper .slick-slider .slick-prev:before, {{WRAPPER}} .elementor-image-carousel-wrapper .slick-slider .slick-next:before' => 'font-size: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'navigation' => array( 'arrows', 'both' ),
				),
			)
		);

		$this->add_control(
			'arrows_color',
			array(
				'label'     => PrestaHelper::__( 'Color', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elementor-image-carousel-wrapper .slick-slider .slick-prev:before, {{WRAPPER}} .elementor-image-carousel-wrapper .slick-slider .slick-next:before' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'navigation' => array( 'arrows', 'both' ),
				),
			)
		);

		$this->add_control(
			'heading_style_dots',
			array(
				'label'     => PrestaHelper::__( 'Dots', 'elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'navigation' => array( 'dots', 'both' ),
				),
			)
		);

		$this->add_control(
			'dots_position',
			array(
				'label'     => PrestaHelper::__( 'Position', 'elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'outside',
				'options'   => array(
					'outside' => PrestaHelper::__( 'Outside', 'elementor' ),
					'inside'  => PrestaHelper::__( 'Inside', 'elementor' ),
				),
				'condition' => array(
					'navigation' => array( 'dots', 'both' ),
				),
			)
		);

		$this->add_control(
			'dots_size',
			array(
				'label'     => PrestaHelper::__( 'Size', 'elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 5,
						'max' => 10,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .elementor-image-carousel-wrapper .elementor-image-carousel .slick-dots li button:before' => 'font-size: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'navigation' => array( 'dots', 'both' ),
				),
			)
		);

		$this->add_control(
			'dots_color',
			array(
				'label'     => PrestaHelper::__( 'Color', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elementor-image-carousel-wrapper .elementor-image-carousel .slick-dots li button:before' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'navigation' => array( 'dots', 'both' ),
				),
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

		$settings = $this->get_settings_for_display();
		$title    = $settings['title'];
		$orderby  = $settings['orderby'];
		$order    = $settings['order'];
		$ids      = $settings['ids'];
		$context  = \Context::getContext();
		$out_put  = '';
		$context->controller->addCSS( _THEME_CSS_DIR_ . 'product.css' );
		$context->controller->addCSS( _THEME_CSS_DIR_ . 'product_list.css' );
		$context->controller->addCSS( _THEME_CSS_DIR_ . 'print.css', 'print' );
		$context->controller->addJqueryPlugin( array( 'fancybox', 'idTabs', 'scrollTo', 'serialScroll', 'bxslider' ) );
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
			$context->smarty->assign(
				array(
					'vc_products'         => $products_for_template,
					'vc_title'            => $title,
					'elementprefix'       => 'single-product',
					'theme_template_path' => _PS_THEME_DIR_ . 'templates/catalog/_partials/miniatures/product.tpl',

				)
			);
			$template_file_name = CRAZY_PATH . 'views/templates/front/productcarousal.tpl';
			$out_put           .= $context->smarty->fetch( $template_file_name );

			echo $out_put;
		} else {
			echo 'No products Selected On Select Product to Show.';
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