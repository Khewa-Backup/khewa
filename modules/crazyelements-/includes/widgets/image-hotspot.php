<?php

namespace CrazyElements;

use CrazyElements\PrestaHelper;
use CrazyElements\Widget_Base;

if ( ! defined( '_PS_VERSION_' ) ) {
	exit;
}

class Widget_ImageHotspot extends Widget_Base {

	public function get_name() {
		return 'image_hotspot';
	}

	public function get_title() {
		return PrestaHelper::__( 'Image Hotspot', 'elementor' );
	}

	public function get_icon() {
		return 'ceicon-image-hotspot-widget';
	}

	public function get_categories() {
		return array( 'crazy_addons' );
	}

	public function randomString() {
			$length = 16;
			$chars  = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$str    = '';

		for ( $i = 0; $i < $length; $i++ ) {
			$str .= $chars[ mt_rand( 0, strlen( $chars ) - 1 ) ];
		}

			return $str;
	}

	protected function _register_controls() {
		$this->start_controls_section(
			'section_image',
			array(
				'label' => PrestaHelper::__( 'Image', 'elementor' ),
			)
		);
		$this->add_control(
			'main_image',
			array(
				'label'   => PrestaHelper::__( 'Main Image', 'elementor' ),
				'type'    => Controls_Manager::MEDIA,
				'dynamic' => array(
					'active' => true,
				),
				'default' => array(
					'url' => Utils::get_placeholder_image_src(),
				),
			)
		);

		$repeater = new Repeater();
		$repeater->add_control(
			'hotspot_type',
			array(
				'label'   => PrestaHelper::__( 'Hotspot Type ', 'elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'text',
				'options' => array(
					'text'  => PrestaHelper::__( 'Text', 'elementor' ),
					'icon'  => PrestaHelper::__( 'Icon', 'elementor' ),
					'image' => PrestaHelper::__( 'Image', 'elementor' ),
				),
			)
		);
		$repeater->add_control(
			'text',
			array(
				'label'     => PrestaHelper::__( 'Text', 'elementor' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => PrestaHelper::__( 'Hotspot1', 'elementor' ),
				'condition' => array(
					'hotspot_type' => array( 'text' ),
				),
			)
		);
		$repeater->add_control(
			'selected_icon',
			array(
				'label'       => PrestaHelper::__( 'Icon', 'elementor' ),
				'type'        => Controls_Manager::ICONS,
				'label_block' => true,
				'condition'   => array(
					'hotspot_type' => array( 'icon' ),
				),
			)
		);
		$repeater->add_control(
			'image',
			array(
				'label'       => PrestaHelper::__( 'Image', 'elementor' ),
				'type'        => Controls_Manager::MEDIA,
				'label_block' => true,
				'condition'   => array(
					'hotspot_type' => array( 'image' ),
				),
			)
		);
		$repeater->add_control(
			'x_position',
			array(
				'name'      => 'x',
				'label'     => PrestaHelper::__( 'X Position', 'elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 50,
					'unit' => '%',
				),
				'range'     => array(
					'%' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .tooltip-wrapper{{CURRENT_ITEM}}' => 'left: {{SIZE}}{{UNIT}};',
				),
			)
		);
		$repeater->add_control(
			'y_position',
			array(
				'name'      => 'y',
				'label'     => PrestaHelper::__( 'Y Position', 'elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 50,
					'unit' => '%',
				),
				'range'     => array(
					'%' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .tooltip-wrapper{{CURRENT_ITEM}}' => 'top: {{SIZE}}{{UNIT}}',
				),
			)
		);
		$repeater->add_control(
			'content',
			array(
				'label'       => PrestaHelper::__( 'Content', 'elementor' ),
				'type'        => Controls_Manager::WYSIWYG,
				'placeholder' => PrestaHelper::__( 'Enter your Content', 'elementor' ),
				'separator'   => 'none',
				'show_label'  => false,
				'default'     => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
			)
		);
		$this->add_control(
			'hotspots',
			array(
				'label'   => PrestaHelper::__( 'Hotspots Items', 'elementor' ),
				'type'    => Controls_Manager::REPEATER,
				'fields'  => $repeater->get_controls(),
				'default' => array(
					array(
						'_id'         => $this->randomString(),
						'image_title' => PrestaHelper::__( 'Hotspots #1', 'elementor' ),
						'tab_content' => PrestaHelper::__( 'Click edit button to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'elementor' ),
					),
				),

			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'hotspots_settings',
			array(
				'label' => PrestaHelper::__( 'Hotspots Settings', 'elementor' ),
			)
		);
		$this->add_control(
			'position',
			array(
				'label'              => PrestaHelper::__( 'Position', 'elementor' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'top',
				'options'            => array(
					'right'        => PrestaHelper::__( 'Right', 'elementor' ),
					'left'         => PrestaHelper::__( 'Left', 'elementor' ),
					'top'          => PrestaHelper::__( 'Top', 'elementor' ),
					'top-right'    => PrestaHelper::__( 'Top Right', 'elementor' ),
					'top-left'     => PrestaHelper::__( 'Top Left', 'elementor' ),
					'bottom'       => PrestaHelper::__( 'Bottom', 'elementor' ),
					'bottom-right' => PrestaHelper::__( 'Bottom Right', 'elementor' ),
					'bottom-left'  => PrestaHelper::__( 'Bottom Left', 'elementor' ),
				),
				'frontend_available' => true,
			)
		);
		$this->add_control(
			'animation',
			array(
				'label'              => PrestaHelper::__( 'Animation', 'elementor' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => PrestaHelper::__( 'fade', 'elementor' ),
				'options'            => array(
					'fadeIn' => PrestaHelper::__( 'Fade In', 'elementor' ),
					'grow'   => PrestaHelper::__( 'Grow', 'elementor' ),
					'swing'  => PrestaHelper::__( 'Swing', 'elementor' ),
					'slide'  => PrestaHelper::__( 'Slide', 'elementor' ),
					'fall'   => PrestaHelper::__( 'Fall', 'elementor' ),
				),
				'default'            => 'fadeIn',
				'frontend_available' => true,
			)
		);
		$this->add_control(
			'delay',
			array(
				'label'              => PrestaHelper::__( 'Delay', 'elementor' ),
				'type'               => Controls_Manager::TEXT,
				'default'            => PrestaHelper::__( '200', 'elementor' ),
				'frontend_available' => true,
			)
		);
		$this->add_control(
			'speed',
			array(
				'label'              => PrestaHelper::__( 'Speed', 'elementor' ),
				'type'               => Controls_Manager::TEXT,
				'default'            => PrestaHelper::__( '350', 'elementor' ),
				'frontend_available' => true,
			)
		);
		$this->add_control(
			'timer',
			array(
				'label'              => PrestaHelper::__( 'Timer', 'elementor' ),
				'type'               => Controls_Manager::TEXT,
				'default'            => PrestaHelper::__( '0', 'elementor' ),
				'frontend_available' => true,
			)
		);
		$this->add_control(
			'trigger',
			array(
				'label'              => PrestaHelper::__( 'Trigger', 'elementor' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => PrestaHelper::__( 'hover', 'elementor' ),
				'options'            => array(
					'hover' => PrestaHelper::__( 'Hover', 'elementor' ),
					'click' => PrestaHelper::__( 'Click', 'elementor' ),
				),
				'frontend_available' => true,
			)
		);
		$this->end_controls_section();
		$this->start_controls_section(
			'section_style_hotspots_point',
			array(
				'label' => PrestaHelper::__( 'Hotspots Point', 'elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,

			)
		);
		$this->add_control(
			'hotspot_text_icon_color',
			array(
				'label'     => PrestaHelper::__( 'Hotspot Text And Icon Color', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elementor-icon'      => 'color: {{VALUE}};',
					'{{WRAPPER}} .elementor-icon span' => 'color: {{VALUE}};',
				),
			)
		);
		$this->add_control(
			'hotspot_text_icon_bg_color',
			array(
				'label'     => PrestaHelper::__( 'Hotspot Text And Icon BG Color', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elementor-icon'      => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .elementor-icon span' => 'background-color: {{VALUE}};',
				),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'hotspot_text_icon_typography',
				'label'    => PrestaHelper::__( 'Hotspot Text And Icon Typography', 'elementor' ),
				'selector' => '{{WRAPPER}} .elementor-icon',
			)
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'hotspot_text_icon_border',
				'label'    => PrestaHelper::__( 'Border', 'plugin-domain' ),
				'selector' => '{{WRAPPER}} .elementor-icon',
			)
		);
		$this->add_responsive_control(
			'hotspot_text_icon_border_radius',
			array(
				'label'      => PrestaHelper::__( 'Border Radius', 'elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'devices'    => array( 'desktop', 'tablet', 'mobile' ),
				'selectors'  => array(
					'{{WRAPPER}} .elementor-icon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->add_responsive_control(
			'hotspot_text_icon_padding',
			array(
				'label'      => PrestaHelper::__( 'Padding', 'elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'devices'    => array( 'desktop', 'tablet', 'mobile' ),
				'selectors'  => array(
					'{{WRAPPER}} .elementor-icon' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->end_controls_section();
		$this->start_controls_section(
			'section_style_hotspots_content',
			array(
				'label' => PrestaHelper::__( 'Hotspots Content', 'elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,

			)
		);
		$this->add_control(
			'hotspot_content_bg_color',
			array(
				'label'     => PrestaHelper::__( 'Hotspot Content BG Color', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'.tooltipster-base'             => 'background: {{VALUE}};',
					'.tooltipster-arrow-right span' => 'border-color: {{VALUE}};',
				),
			)
		);
		$this->add_control(
			'hotspot_content_color',
			array(
				'label'     => PrestaHelper::__( 'Hotspot Content Color', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'.tooltipster-base'   => 'color: {{VALUE}};',
					'.tooltipster-base p' => 'color: {{VALUE}};',
				),
			)
		);
		$this->add_responsive_control(
			'alignment',
			array(
				'label'   => PrestaHelper::__( 'Alignment', 'elecounter' ),
				'type'    => Controls_Manager::CHOOSE,
				'devices' => array( 'desktop', 'tablet', 'mobile' ),
				'options' => array(
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
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'hotspot_content_typography',
				'label'    => PrestaHelper::__( 'Hotspot Content Typography', 'elementor' ),
				'selector' => '.tooltipster-base .tooltipster-content',
			)
		);
		$this->add_responsive_control(
			'hotspot_content_padding',
			array(
				'label'      => PrestaHelper::__( 'Padding', 'elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'devices'    => array( 'desktop', 'tablet', 'mobile' ),
				'selectors'  => array(
					'.tooltipster-base' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'hotspot_content_border',
				'label'    => PrestaHelper::__( 'Border', 'plugin-domain' ),
				'selector' => '.tooltipster-base',
			)
		);
		$this->add_responsive_control(
			'hotspot_content_border_radius',
			array(
				'label'      => PrestaHelper::__( 'Border Radius', 'elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'devices'    => array( 'desktop', 'tablet', 'mobile' ),
				'selectors'  => array(
					'.tooltipster-base' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->end_controls_section();
	}

	protected function render() {
		$settings   = $this->get_settings_for_display();
		$main_image = ( $settings['main_image']['id'] != '' ) ? wp_get_attachment_image_url( $settings['main_image']['id'], 'full' ) : $settings['main_image']['url'];
		$list_items = $settings['hotspots'];
		$alignment  = $settings['alignment'];
		?>
<style>
.tooltipster-base {
    text-align: <?php echo $alignment;
    ?>;
}
</style>
<div class="crazyelement-image-hotspot">
    <img src="<?php echo $main_image; ?>" title="" alt="" loading="lazy">
    <?php

			$settings_hots['position']  = $settings['position'];
			$settings_hots['animation'] = $settings['animation'];
			$settings_hots['delay']     = $settings['delay'];
			$settings_hots['trigger']   = $settings['trigger'];
			$settings_hots['timer']     = $settings['timer'];
			$settings_hots['speed']     = $settings['speed'];
			$settings_hots              = json_encode( $settings_hots );

			foreach ( $list_items as $list_item ) {

				$content      = $list_item['content'];
				$hotspot_type = $list_item['hotspot_type'];

				?>
    <div class="tooltip-wrapper elementor-repeater-item-<?php echo $list_item['_id']; ?>"
        title='<?php echo $content; ?>' data-settings_hots='<?php echo $settings_hots; ?>'>
        <div class="elementor-icon">
            <?php
						if ( $hotspot_type == 'image' ) {
							$image = $list_item['image']['url'];
							?>
            <img src="<?php echo $image; ?>" alt="" loading="lazy" />
            <?php
						} elseif ( $hotspot_type == 'icon' ) {

							if ( ! isset( $list_item['icon'] ) && ! Icons_Manager::is_migration_allowed() ) {
								$list_item['icon'] = 'fa fa-star';
							}
							$migrated = isset( $list_item['__fa4_migrated']['selected_icon'] );
							$is_new   = ! isset( $list_item['icon'] ) && Icons_Manager::is_migration_allowed();
							if ( $is_new || $migrated ) {
								Icons_Manager::render_icon( $list_item['selected_icon'], array( 'aria-hidden' => 'true' ) );
							} else {
								?>
            <i class="<?php echo $list_item['icon']; ?>" aria-hidden="true"></i>
            <?php
							}
						} else {
							$text = $list_item['text'];
							?>
            <span><?php echo $text; ?></span>
            <?php } ?>
        </div>
    </div>
    <?php } ?>
</div>

<?php

	}


	public function on_import( $element ) {
		return Icons_Manager::on_import_migration( $element, 'icon', 'selected_icon' );
	}
}