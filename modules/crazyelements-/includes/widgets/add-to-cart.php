<?php
namespace CrazyElements;

use CrazyElements\PrestaHelper;
use CrazyElements\Widget_Base;
if ( ! defined( '_PS_VERSION_' ) ) {
	exit; // Exit if accessed directly.
}
class Widget_AddToCart extends Widget_Base {
	/**
	 * Get widget name.
	 *
	 * Retrieve accordion widget name.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'add_to_cart';
	}
	/**
	 * Get widget title.
	 *
	 * Retrieve accordion widget title.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return PrestaHelper::__( 'Add to cart', 'elementor' );
	}
	/**
	 * Get widget icon.
	 *
	 * Retrieve accordion widget icon.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'ceicon-add-to-cart-widget';
	}
	public function get_categories() {
		return array( 'products_free' );
	}
	private function get_products_array() {
		$sql  = 'SELECT p.`id_product`, pl.`name`
                FROM `' . _DB_PREFIX_ . 'product` p
                ' . \Shop::addSqlAssociation( 'product', 'p' ) . '
                LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (p.`id_product` = pl.`id_product` ' . \Shop::addSqlRestrictionOnLang( 'pl' ) . ')';
		$rs   = \Db::getInstance( _PS_USE_SQL_SLAVE_ )->executeS( $sql );
		$rslt = array();
		foreach ( $rs as $i => $r ) {
			$rslt[ $r['id_product'] ] = $r['name'];
			$i++;
		}
		return $rslt;
	}
	/**
	 * Register accordion widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since  1.0.0
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
			'ids',
			array(
				'label'     => PrestaHelper::__( 'Select products', 'elementor' ),
				'type'      => Controls_Manager::AUTOCOMPLETE,
				'item_type' => 'product',
				'multiple'  => false,
			)
		);
		$this->add_control(
			'btn_text_onoff',
			array(
				'label'   => PrestaHelper::__( 'Button Text On/Off', 'elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'dynamic' => array(
					'active' => true,
				),
				'default' => 'yes',
			)
		);
		$this->add_control(
			'btn_text',
			array(
				'label'     => PrestaHelper::__( 'Button Text', 'elementor' ),
				'type'      => Controls_Manager::TEXT,
				'dynamic'   => array(
					'active' => true,
				),
				'default'   => 'Add to cart',
				'condition' => array(
					'btn_text_onoff' => array( 'yes' ),
				),
			)
		);
		$this->add_control(
			'btn_icon_onoff',
			array(
				'label'   => PrestaHelper::__( 'Button Icon On/Off', 'elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'dynamic' => array(
					'active' => true,
				),
				'default' => 'yes',
			)
		);
		$this->add_control(
			'icon',
			array(
				'label'       => PrestaHelper::__( 'Icon', 'elementor' ),
				'type'        => Controls_Manager::ICONS,
				'label_block' => true,
				'condition'   => array(
					'btn_icon_onoff' => array( 'yes' ),
				),
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
				'default'      => 'center',
			)
		);
		$this->end_controls_section();
		$this->start_controls_section(
			'section_style_button',
			array(
				'label' => PrestaHelper::__( 'Button Style', 'elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,

			)
		);
		$this->add_control(
			'button_color',
			array(
				'label'     => PrestaHelper::__( 'Button Color', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} a.btn.btn-primary.add-to-cart'      => 'color: {{VALUE}};',
				),
			)
		);
		$this->add_control(
			'button_hover_color',
			array(
				'label'     => PrestaHelper::__( 'Button Hover Color', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} a.btn.btn-primary.add-to-cart:hover'      => 'color: {{VALUE}};',
				),
			)
		);
		$this->add_control(
			'button_bg_color',
			array(
				'label'     => PrestaHelper::__( 'Button BG Color', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} a.btn.btn-primary.add-to-cart'      => 'background-color: {{VALUE}};',
				),
			)
		);
		$this->add_control(
			'button_hover_bg_color',
			array(
				'label'     => PrestaHelper::__( 'Button Hover BG Color', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} a.btn.btn-primary.add-to-cart:hover'      => 'background-color: {{VALUE}};',
				),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'button_typography',
				'label'    => PrestaHelper::__( 'Button Typography', 'elementor' ),
				'selector' => '{{WRAPPER}} a.btn.btn-primary.add-to-cart',
			)
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'button_border',
				'label'    => PrestaHelper::__( 'Border', 'plugin-domain' ),
				'selector' => '{{WRAPPER}} a.btn.btn-primary.add-to-cart',
			)
		);
		$this->add_responsive_control(
			'button_border_radius',
			array(
				'label'      => PrestaHelper::__( 'Border Radius', 'elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'devices'    => array( 'desktop', 'tablet', 'mobile' ),
				'selectors'  => array(
					'{{WRAPPER}} a.btn.btn-primary.add-to-cart' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->add_responsive_control(
			'button_padding',
			array(
				'label'      => PrestaHelper::__( 'Padding', 'elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'devices'    => array( 'desktop', 'tablet', 'mobile' ),
				'selectors'  => array(
					'{{WRAPPER}} a.btn.btn-primary.add-to-cart' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
	 * @since  1.0.0
	 * @access protected
	 */
	protected function render() {
		$settings       = $this->get_settings_for_display();
		$btn_text_onoff = $settings['btn_text_onoff'];
		$btn_text       = $settings['btn_text'];
		$btn_icon_onoff = $settings['btn_icon_onoff'];
		$ids            = $settings['ids'];
		$context        = \Context::getContext();
		$out_put        = '';
		$id_lang        = $context->language->id;
		$front          = true;
		if ( ! in_array( $context->controller->controller_type, array( 'front', 'modulefront' ) ) ) {
			$front = false;
		}
		$str             = '';
		$ids = explode('_',$ids);
		if(isset($ids) && is_array($ids)){
			$str = $ids[0];
		}else{
			$str             = '';
		}
		if ( $str != '' ) {
			$sql      = 'SELECT p.*, product_shop.*, pl.*
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
			$rq       = \Db::getInstance( _PS_USE_SQL_SLAVE_ )->executeS( $sql );
			$products = \Product::getProductsProperties( $id_lang, $rq );
			if ( ! $products ) {
				return false;
			}
			$link         = new \Link();
			$add_cart_url = $link->getAddToCartURL( $products[0]['id_product'], $products[0]['id_product_attribute'] );
			?>
<div class="product-add-to-cart">
    <div class="add">
        <a href="<?php echo $add_cart_url; ?>" class="btn btn-primary add-to-cart">

            <?php
						if ( $btn_icon_onoff ) :
							if ( empty( $settings['icon'] ) && ! Icons_Manager::is_migration_allowed() ) {
								// add old default
								$settings['icon'] = 'material-icons shopping-cart';
							}

							if ( ! empty( $settings['icon'] ) ) {
								$this->add_render_attribute( 'icon', 'class', $settings['icon'] );
								$this->add_render_attribute( 'icon', 'aria-hidden', 'true' );
							}

							$migrated = isset( $settings['__fa4_migrated']['selected_icon'] );
							$is_new   = empty( $settings['icon'] ) && Icons_Manager::is_migration_allowed();
							if ( $is_new || $migrated ) :
								Icons_Manager::render_icon( $settings['selected_icon'], array( 'aria-hidden' => 'true' ) );
						 else :
								?>
            <i <?php echo $this->get_render_attribute_string( 'icon' ); ?>></i>
            <?php
						endif;
						endif;
						if ( $btn_text_onoff ) {
							echo $btn_text;
						}
						?>
        </a>
    </div>
</div>
<?php
		} else {
			echo 'No Product Selected';
		}
	}
}