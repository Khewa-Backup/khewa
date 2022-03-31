<?php
namespace CrazyElements;

use CrazyElements\PrestaHelper;
use CrazyElements\Widget_Base;

if ( ! defined( '_PS_VERSION_' ) ) {
	exit; // Exit if accessed directly.
}

class Widget_AjaxSearch extends Widget_Base {








	public function get_name() {
		return 'ajax_search';
	}

	public function get_title() {
		return PrestaHelper::__( 'Ajax Search', 'elementor' );
	}

	public function get_icon() {
		return 'ceicon-ajax-search-widget';
	}

	public function get_categories() {
		return array( 'products' );
	}

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
				'label'   => PrestaHelper::__( 'Style', 'elementor' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'default'              => PrestaHelper::__( 'Default', 'elementor' ),
					'search-container'     => PrestaHelper::__( 'One', 'elementor' ),
					'meterial_search_form' => PrestaHelper::__( 'Two', 'elementor' ),
				),
				'default' => 'default',
			)
		);
		$this->add_control(
			'title',
			array(
				'label'       => PrestaHelper::__( 'Title', 'elementor' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => PrestaHelper::__( 'Type your title here', 'elementor' ),
			)
		);
		$this->add_control(
			'placeholder',
			array(
				'label'       => PrestaHelper::__( 'Placeholder', 'elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => PrestaHelper::__( 'Search', 'elementor' ),
				'dynamic'     => array(
					'active' => true,
				),
				'label_block' => true,
			)
		);
		$this->add_control(
			'selected_icon',
			array(
				'label'            => PrestaHelper::__( 'Icon', 'elementor' ),
				'type'             => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
				'default'          => array(
					'value'   => 'fas fa-star',
					'library' => 'fa-solid',
				),
			)
		);
		$this->add_control(
			'button_title',
			array(
				'label'       => PrestaHelper::__( 'Button Text', 'elementor' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => PrestaHelper::__( 'Type your title here', 'elementor' ),
				'conditions'  => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'     => 'layout',
							'operator' => '==',
							'value'    => 'search-container',
						),
					),
				),
			)
		);
		$this->add_control(
			'type',
			array(
				'label'   => PrestaHelper::__( 'Search Type', 'elementor' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'products'      => PrestaHelper::__( 'Products', 'elementor' ),
					'category'      => PrestaHelper::__( 'Categories', 'elementor' ),
					'suppliers'     => PrestaHelper::__( 'Suppliers', 'elementor' ),
					'manufacturers' => PrestaHelper::__( 'Manufacturers', 'elementor' ),
				),
				'default' => 'products',
			)
		);

		$this->add_control(
			'show_of_current_cat',
			array(
				'label'     => PrestaHelper::__( 'Show Product of Current Category', 'elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'dynamic'   => array(
					'active' => true,
				),
				'default'   => 'yes',
				'condition' => array(
					'type' => array( 'products' ),
				),
			)
		);

		$this->end_controls_section();
		$this->start_controls_section(
			'title_style',
			array(
				'label' => PrestaHelper::__( 'Title & Form', 'elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_control(
			'title_color',
			array(
				'label'     => PrestaHelper::__( 'Title Color', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .form-search label' => 'color: {{VALUE}}',
				),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'search_typography',
				'label'    => PrestaHelper::__( 'Typography', 'elementor' ),
				'selector' => '{{WRAPPER}} .form-search label',
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'search_typography_placeholder',
				'label'    => PrestaHelper::__( 'Typography Form', 'elementor' ),
				'selector' => '{{WRAPPER}} .form-search, {{WRAPPER}} .smart_search_top #search_autocomplete ul li a',
			)
		);
		$this->add_responsive_control(
			'form_width',
			array(
				'label'      => PrestaHelper::__( 'Form Width', 'elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'devices'    => array( 'desktop', 'tablet', 'mobile' ),
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 1000,
						'step' => 5,
					),
					'%'  => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'default'    => array(
					'unit' => '%',
					'size' => 50,
				),
				'selectors'  => array(
					'{{WRAPPER}} .smart_search_top' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'border_form',
				'label'    => PrestaHelper::__( 'Form Border', 'elementor' ),
				'selector' => '{{WRAPPER}} .smart_search_top .form-search #search_query_top',
			)
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'border_form_hover',
				'label'    => PrestaHelper::__( 'Form Border Focus', 'elementor' ),
				'selector' => '{{WRAPPER}} .smart_search_top .form-search #search_query_top:focus',
			)
		);
		$this->end_controls_section();
		$this->start_controls_section(
			'button_style',
			array(
				'label' => PrestaHelper::__( 'Button & Icon', 'elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_control(
			'alignment',
			array(
				'label'        => PrestaHelper::__( 'Alignment', 'elementor' ),
				'type'         => Controls_Manager::CHOOSE,
				'options'      => array(
					'left'    => array(
						'title' => PrestaHelper::__( 'Left', 'elementor' ),
						'icon'  => 'fa fa-align-left',
					),
					'right'   => array(
						'title' => PrestaHelper::__( 'Right', 'elementor' ),
						'icon'  => 'fa fa-align-right',
					),
					'justify' => array(
						'title' => PrestaHelper::__( 'Justify', 'elementor' ),
						'icon'  => 'fa fa-align-justify',
					),
				),
				'prefix_class' => 'alignment%s',
			)
		);
		$this->add_responsive_control(
			'width',
			array(
				'label'      => PrestaHelper::__( 'Width', 'elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'devices'    => array( 'desktop', 'tablet', 'mobile' ),
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 1000,
						'step' => 5,
					),
					'%'  => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'default'    => array(
					'unit' => '%',
					'size' => 50,
				),
				'selectors'  => array(
					'{{WRAPPER}} .search-container button' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'btn_typography',
				'label'    => PrestaHelper::__( 'Typography', 'elementor' ),
				'selector' => '{{WRAPPER}} .search-container button ,{{WRAPPER}} .meterial_search_form',
			)
		);
		$this->add_control(
			'button_color',
			array(
				'label'     => PrestaHelper::__( 'Button Color', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .search-container button ,{{WRAPPER}} .meterial_search_form button' => 'color: {{VALUE}}',
				),
			)
		);
		$this->add_control(
			'button_icon_color',
			array(
				'label'     => PrestaHelper::__( 'Icon Color', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .smart_search_top .form-search button[type=submit] i' => 'color: {{VALUE}}',
				),
			)
		);
		$this->add_control(
			'button_icon_hover_color',
			array(
				'label'     => PrestaHelper::__( 'Icon Hover Color', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .smart_search_top .form-search button[type=submit] i:hover' => 'color: {{VALUE}}',
				),
			)
		);
		$this->start_controls_tabs( 'tabs_button_style' );
		$this->start_controls_tab(
			'tab_button_normal',
			array(
				'label' => PrestaHelper::__( 'Normal', 'elementor' ),
			)
		);
		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'button_background',
				'label'    => PrestaHelper::__( 'Background', 'elementor' ),
				'types'    => array( 'classic', 'gradient', 'video' ),
				'selector' => '{{WRAPPER}} .search-container button , {{WRAPPER}} .meterial_search_form button',
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab(
			'tab_button_hover',
			array(
				'label' => PrestaHelper::__( 'Hover', 'elementor' ),
			)
		);
		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'background',
				'label'    => PrestaHelper::__( 'Hover Effect', 'elementor' ),
				'types'    => array( 'classic', 'gradient', 'video' ),
				'selector' => '{{WRAPPER}} .search-container button:hover , {{WRAPPER}} .meterial_search_form button:hover',
			)
		);
		$this->end_controls_tab();
		$this->end_controls_section();
		$this->start_controls_section(
			'advanced',
			array(
				'label' => PrestaHelper::__( 'Advanced', 'elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_responsive_control(
			'gap',
			array(
				'label'      => PrestaHelper::__( 'Gap', 'elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'devices'    => array( 'desktop', 'tablet', 'mobile' ),
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 200,
						'step' => 5,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 10,
				),
				'selectors'  => array(
					'{{WRAPPER}} .search-container , {{WRAPPER}} .meterial_search_form' => 'gap: {{SIZE}}{{UNIT}};',
				),
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'     => 'layout',
							'operator' => '==',
							'value'    => 'search-container',
						),
					),
				),
			)
		);
		$this->add_responsive_control(
			'height',
			array(
				'label'      => PrestaHelper::__( 'Height', 'elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'devices'    => array( 'desktop', 'tablet', 'mobile' ),
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 200,
						'step' => 5,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 85,
				),
				'selectors'  => array(
					'{{WRAPPER}} .search-container , {{WRAPPER}} .meterial_search_form, {{WRAPPER}} .meterial_search_form input[type=text]' => 'height: {{SIZE}}{{UNIT}};',
				),
			)
		);
		$this->add_responsive_control(
			'padding',
			array(
				'label'      => PrestaHelper::__( 'Padding', 'elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'devices'    => array( 'desktop', 'tablet', 'mobile' ),
				'selectors'  => array(
					'{{WRAPPER}} .form-search' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->add_responsive_control(
			'margin',
			array(
				'label'      => PrestaHelper::__( 'Margin', 'elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'devices'    => array( 'desktop', 'tablet', 'mobile' ),
				'selectors'  => array(
					'{{WRAPPER}} .form-search' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->end_controls_section();
	}

	protected function render() {
		$settings      = $this->get_settings_for_display();
		$layout        = $settings['layout'];
		$type          = $settings['type'];
		$placeholder   = $settings['placeholder'];
		$selected_icon = $settings['selected_icon'];
		$title         = $settings['title'];
		$button_title  = $settings['button_title'];
		$alignment     = $settings['alignment'];
		$link          = new \Link();
		?>
		<script>
			var frontajaxurl = '<?php echo PrestaHelper::getAjaxUrl(); ?>';
		</script>
		<div class="search-wrapper" >
			<div class="smart_search_top">
				<p class="search_title t_align_c"></p>
				<form id="searchbox" action="<?php echo $link->getPageLink( 'search' ); ?>" method="get">
				<div class="form-search">
		<?php
		if ( $title ) :
			?>
					<label for="search_query_top"><?php echo $title; ?></label>
		<?php endif; ?>
					<div class="<?php echo $layout; ?> float-<?php echo $alignment; ?>">
						<input type="hidden" class="search_query_type" value="<?php echo $type; ?>"/>
		<?php
		if ( $type == 'products' ) {
			$show_of_current_cat = $settings['show_of_current_cat'];
			if ( ! isset( $show_of_current_cat ) || $show_of_current_cat == '' ) {
				$show_of_current_cat = 'no';
			} else {
				$show_of_current_cat = \Tools::getValue( 'id_category' );
			}
			?>
							<input type="hidden" class="is_current_catg" value="<?php echo $show_of_current_cat; ?>"/>
			<?php
		}
		?>
						<input class="search_query search_query_top input-text" type="text" id="search_query_top" name="search_query" placeholder="<?php echo $placeholder; ?>"/>
						<button type="submit" class="<?php echo $layout; ?>">
		<?php Icons_Manager::render_icon( $settings['selected_icon'], array( 'aria-hidden' => 'true' ) ); ?>
		<?php echo $button_title; ?></button>
					</div>
				</div>     
				</form> 
				<div id="search_autocomplete" class="search-autocomplete" style="display:none;">
					<ul id="autocomplete_appender" class="autocomplete_appender">        
					</ul>
				</div>
			</div>
		</div>
		<?php
	}

	protected function _content_template() {
	}
}
