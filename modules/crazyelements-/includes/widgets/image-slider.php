<?php
namespace CrazyElements;

use CrazyElements\PrestaHelper;
use CrazyElements\Widget_Base;

if ( ! defined( '_PS_VERSION_' ) ) {
	exit;
}

class Widget_ImageSlider extends Widget_Base {



	public function get_name() {
		return 'image_slider';
	}

	public function get_title() {
		return PrestaHelper::__( 'Image Slider', 'elementor' );
	}

	public function get_icon() {
		return 'ceicon-slider-widget';
	}

	public function get_categories() {
		return array( 'crazy_addons' );
	}


	protected function _register_controls() {
		$this->start_controls_section(
			'section_title',
			array(
				'label' => PrestaHelper::__( 'General', 'elementor' ),
			)
		);
		$this->add_control(
			'speed',
			array(
				'label'   => PrestaHelper::__( 'Speed (milliseconds)', 'elementor' ),
				'desc'    => PrestaHelper::__( 'The duration of the transition between two slides.', 'elementor' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 5000,
			)
		);

		$this->add_control(
			'pause_hover',
			array(
				'label'   => PrestaHelper::__( 'Pause on hover', 'elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'loop_forever',
			array(
				'label'   => PrestaHelper::__( 'Loop forever', 'elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'image',
			array(
				'label'   => PrestaHelper::__( 'Image', 'elementor' ),
				'type'    => Controls_Manager::MEDIA,
				'dynamic' => array(
					'active' => true,
				),
				'default' => array(
					'url' => Utils::get_placeholder_image_src(),
				),
			)
		);

		$repeater->add_control(
			'image_title',
			array(
				'label'       => PrestaHelper::__( 'Title', 'elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => PrestaHelper::__( 'Sample 1', 'elementor' ),
				'dynamic'     => array(
					'active' => true,
				),
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'image_url',
			array(
				'label'       => PrestaHelper::__( 'Target Link', 'elementor' ),
				'type'        => Controls_Manager::URL,
				'dynamic'     => array(
					'active' => true,
				),
				'placeholder' => PrestaHelper::__( 'https://your-link.com', 'elementor' ),
				'default'     => array(
					'url' => '#',
				),
			)
		);

		$repeater->add_control(
			'caption',
			array(
				'label'       => PrestaHelper::__( 'Caption', 'elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => PrestaHelper::__( 'sample-1', 'elementor' ),
				'dynamic'     => array(
					'active' => true,
				),
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'description',
			array(
				'label'       => PrestaHelper::__( 'Content', 'elementor' ),
				'type'        => Controls_Manager::WYSIWYG,
				'placeholder' => PrestaHelper::__( 'Enter your Content', 'elementor' ),
				'default'     => PrestaHelper::__(
					'<h3>EXCEPTEUR OCCAECAT</h3>
		<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin tristique in tortor et dignissim. Quisque non tempor leo. Maecenas egestas sem elit</p>',
					'elementor'
				),
				'separator'   => 'none',
				'show_label'  => false,
			)
		);

		$this->add_control(
			'images',
			array(
				'label'       => PrestaHelper::__( 'Image Slider Items', 'elementor' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array(
						'image_title' => PrestaHelper::__( 'Slider title #1', 'elementor' ),
						'tab_content' => PrestaHelper::__( 'Click edit button to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'elementor' ),
					),
					array(
						'image_title' => PrestaHelper::__( 'Slider title #2', 'elementor' ),
						'tab_content' => PrestaHelper::__( 'Click edit button to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'elementor' ),
					),
				),
				'title_field' => '{{{ image_title }}}',
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings     = $this->get_settings_for_display();
		$speed        = $settings['speed'];
		$pause_hover  = $settings['pause_hover'];
		$loop_forever = $settings['loop_forever'];
		$context      = \Context::getContext();
		if ( ! isset( $pause_hover ) ) {
			$pause_hover = 'hover';
		} else {
			if ( $pause_hover == 'yes' ) {
				$pause_hover = 'hover';
			} else {
				$pause_hover = '';
			}
		}

		if ( ! isset( $loop_forever ) ) {
			$loop_forever = 'true';
		} else {
			if ( $loop_forever == 'yes' ) {
				$loop_forever = 'true';
			} else {
				$loop_forever = 'false';
			}
		}

		$images = array();

		foreach ( $settings['images'] as $image ) {

			$images[] = array(
				'image_url'   => $image['image']['url'],
				'title'       => $image['image_title'],
				'legend'      => $image['caption'],
				'description' => $image['description'],
				'url'         => $image['image_url']['url'],
			);
		}
		$context->smarty->assign(
			array(
				'homeslider' => array(
					'speed'  => $speed,
					'pause'  => $pause_hover,
					'wrap'   => $loop_forever,
					'slides' => $images,
				),
			)
		);

		$tpl       = '/slider.tpl';
		$theme_tpl = _PS_THEME_DIR_ . 'modules/ps_imageslider/views/templates/hook' . $tpl;

		$output = $context->smarty->fetch( file_exists( $theme_tpl ) ? $theme_tpl : _PS_MODULE_DIR_ . 'ps_imageslider/views/templates/hook' . $tpl );

		echo $output;
	}
}