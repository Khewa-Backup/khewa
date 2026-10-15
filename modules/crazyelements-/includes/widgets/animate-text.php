<?php
namespace CrazyElements;

if ( ! defined( '_PS_VERSION_' ) ) {
	exit; // Exit if accessed directly.
}
use CrazyElements\Modules\DynamicTags\Module as TagsModule;

use CrazyElements\PrestaHelper;
use CrazyElements\Widget_Base;
use CrazyElements\Controls_Manager;
use CrazyElements\Core\Schemes;
class Widget_AnimateText extends Widget_Base {


	public function get_name() {
		return 'ce_animation_text';
	}
	public function get_title() {
		return PrestaHelper::__( 'Animation text', 'elementor' );
	}
	public function get_icon() {
		return 'ceicon-animated';
	}
	public function get_categories() {
		return array( 'crazy_addons' );
	}
	protected function _register_controls() {
		$this->start_controls_section(
			'animation_content_area',
			array(
				'label' => PrestaHelper::__( 'Animation content', 'elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'title_before_text',
			array(
				'label'   => PrestaHelper::__( 'Title Before text', 'plugin-domain' ),
				'type'    => Controls_Manager::TEXTAREA,
				'default' => 'Before Text&nbsp;',
			)
		);
		$this->add_control(
			'title_after_text',
			array(
				'label'   => PrestaHelper::__( 'Title After text', 'plugin-domain' ),
				'type'    => Controls_Manager::TEXTAREA,
				'default' => '&nbsp;After Text',
			)
		);
		$this->end_controls_section();
		$this->start_controls_section(
			'animation_main_content_area',
			array(
				'label' => PrestaHelper::__( 'Animation Text', 'elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'animation_hub',
			array(
				'label'   => PrestaHelper::__( 'animation concept design', 'plugin-domain' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'moving_animation' => 'Moving animation',
					'typing_animation' => 'Typeing animation',
				),
				'default' => 'moving_animation',
			)
		);
		$this->add_control(
			'animate_list',
			array(
				'label'      => PrestaHelper::__( 'Select Animation', 'plugin-domain' ),
				'type'       => Controls_Manager::SELECT,
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'     => 'animation_hub',
							'operator' => '==',
							'value'    => 'moving_animation',
						),
					),
				),
				'options'    => array(
					'fadeIn'            => 'Fade In',
					'fadeInDown'        => 'Fade In Down',
					'fadeInLeft'        => 'Fade In Left',
					'fadeInRight'       => 'Fade In Right',
					'fadeInUp'          => 'Fade In Up',
					'zoomIn'            => 'Zoom In',
					'zoomInDown'        => 'Zoom In Down',
					'zoomInLeft'        => 'Zoom In Left',
					'zoomInRight'       => 'Zoom In Right',
					'zoomInUp'          => 'Zoom In Up',
					'bounceIn'          => 'Bounce In',
					'bounceInDown'      => 'Bounce In Down',
					'bounceInLeft'      => 'Bounce In Left',
					'bounceInRight'     => 'Bounce In Right',
					'bounceInUp'        => 'Bounce In Up',
					'slideInDown'       => 'Slide In Down',
					'slideInLeft'       => 'Slide In Left',
					'slideInRight'      => 'Slide In Right',
					'slideInUp'         => 'Slide In Up',
					'rotateIn'          => 'Rotate In',
					'rotateInDownLeft'  => 'Rotate In Down Left',
					'rotateInDownRight' => 'Rotate In Down Right',
					'rotateInUpLeft'    => 'Rotate In Up Left',
					'rotateInUpRight'   => 'Rotate In Up Right',
					'bounce'            => 'Bounce',
					'flash'             => 'Flash',
					'pulse'             => 'Pulse',
					'rubberBand'        => 'Rubber Band',
					'shake'             => 'Shake',
					'headShake'         => 'Head Shake',
					'swing'             => 'Swing',
					'tada'              => 'Tada',
					'wobble'            => 'Wobble',
					'jello'             => 'Jello',
					'lightSpeedIn'      => 'Light Speed In',
					'rollIn'            => 'Roll In',
				),
			)
		);
		$this->add_control(
			'animation_speed',
			array(
				'label'   => PrestaHelper::__( 'Animation speed', 'elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '1000',
			)
		);
		$this->add_control(
			'arow_right',
			array(
				'label'      => PrestaHelper::__( 'Enable Cursor', 'plugin-domain' ),
				'type'       => Controls_Manager::SWITCHER,
				'label_off'  => PrestaHelper::__( 'Off', 'your-plugin' ),
				'label_on'   => PrestaHelper::__( 'On', 'your-plugin' ),
				'default'    => 'false',
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'     => 'animation_hub',
							'operator' => '==',
							'value'    => 'typing_animation',
						),
					),
				),
			)
		);
		$repeater = new Repeater();
		$repeater->add_control(
			'animation_text_item_single',
			array(
				'label' => PrestaHelper::__( 'Animation Text', 'elementor' ),
				'type'  => Controls_Manager::TEXT,
			)
		);
		$this->add_control(
			'animation_text_items_list',
			array(
				'label'   => PrestaHelper::__( 'Animation Text items', 'plugin-domain' ),
				'type'    => Controls_Manager::REPEATER,
				'fields'  => $repeater->get_controls(),
				'default' => array(
					array(
						'animation_text_item_single' => PrestaHelper::__( 'Animated Text 1', 'plugin-domain' ),
					),
					array(
						'animation_text_item_single' => PrestaHelper::__( 'Animated Text 2', 'plugin-domain' ),
					),
				),
			)
		);
		$this->end_controls_section();
		$this->start_controls_section(
			'base_style_section',
			array(
				'label' => PrestaHelper::__( 'Base Text', 'elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'base_text_item_typo',
				'label'    => PrestaHelper::__( 'Typography', 'kidszone-core' ),
				'selector' => '{{WRAPPER}} .base-typo-text',
			)
		);
		$this->add_control(
			'base_text_item_align',
			array(
				'label'   => PrestaHelper::__( 'Alignment', 'plugin-domain' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => array(
					'align-left'   => array(
						'title' => PrestaHelper::__( 'Left', 'plugin-domain' ),
						'icon'  => 'fa fa-align-left',
					),
					'align-center' => array(
						'title' => PrestaHelper::__( 'Center', 'plugin-domain' ),
						'icon'  => 'fa fa-align-center',
					),
					'align-right'  => array(
						'title' => PrestaHelper::__( 'Right', 'plugin-domain' ),
						'icon'  => 'fa fa-align-right',
					),
				),
				'default' => 'center',
				'toggle'  => true,
			)
		);
		$this->add_control(
			'base_text_item_align_color',
			array(
				'label'     => PrestaHelper::__( 'Text Color', 'kidszone-core' ),
				'separator' => 'before',
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .base-typo-text' => 'color: {{VALUE}} !important',
				),
			)
		);
		$this->end_controls_section();
		$this->start_controls_section(
			'style_section',
			array(
				'label' => PrestaHelper::__( 'Animation Text', 'elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'animation_text_item_typo',
				'label'    => PrestaHelper::__( 'Typography', 'kidszone-core' ),
				'selector' => '{{WRAPPER}} .ce-animation-text',
			)
		);
		$this->add_control(
			'animation_text_item_color',
			array(
				'label'     => PrestaHelper::__( 'Color', 'kidszone-core' ),
				'separator' => 'before',
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .ce-animation-text' => 'color: {{VALUE}} !important',
				),
			)
		);
		$this->add_control(
			'animation_text_item_align',
			array(
				'label'   => PrestaHelper::__( 'Animation Alignment', 'plugin-domain' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => array(
					'align-left'   => array(
						'title' => PrestaHelper::__( 'Left', 'plugin-domain' ),
						'icon'  => 'fa fa-align-left',
					),
					'align-center' => array(
						'title' => PrestaHelper::__( 'Center', 'plugin-domain' ),
						'icon'  => 'fa fa-align-center',
					),
					'align-right'  => array(
						'title' => PrestaHelper::__( 'Right', 'plugin-domain' ),
						'icon'  => 'fa fa-align-right',
					),
				),
				'default' => 'center',
				'toggle'  => true,
			)
		);
		$this->end_controls_section();
	}
	protected function render() {
		$settings = $this->get_settings_for_display();

		$title_before_text    = $settings['title_before_text'];
		$title_after_text     = $settings['title_after_text'];
		$base_text_item_align = $settings['base_text_item_align'];

		$animation_text_items_list = $settings['animation_text_items_list'] ? $settings['animation_text_items_list'] : array();
		$animation_text_item_align = $settings['animation_text_item_align'];

		$animate_list    = $settings['animate_list'] ? $settings['animate_list'] : '';
		$animation_speed = $settings['animation_speed'];
		$arow_right      = $settings['arow_right'];
		if ( $arow_right == 'yes' ) :
			$arow_right = 'true';
		endif;

		$animation_hub = $settings['animation_hub'];

		$content_display_class = '';
		if ( $title_before_text || $title_before_text ) {
			$content_display_class = 'd-flex';
		}
		?>
<div class="ce-animated-text-area">
		<?php if ( $animation_hub == 'moving_animation' ) : ?>
	<div data-optionce='{ "nameAnimi": "<?php echo $animate_list; ?>", "speed": <?php echo $animation_speed; ?> }'
		class="moving-animation-loop ce-animated-text display-<?php echo $base_text_item_align . ' ' . $animation_hub; ?> base-typo-text <?php echo $content_display_class; ?>">
			<?php if ( $title_before_text ) : ?>
		<div class="ce-animated-text-before">
				<?php echo $title_before_text; ?>
		</div>
			<?php endif; ?>
		<div class="ce-animation-text text-<?php echo $animation_text_item_align; ?>">
			<?php
			$list_array = array();
			foreach ( $animation_text_items_list as $item ) {
				$list_array[] = $item['animation_text_item_single'] . ', ';
			}
			$last = array_pop( $list_array );
			array_push( $list_array, rtrim( $last, ', ' ) );
			foreach ( $list_array as $item_single ) {
				echo $item_single;
			}
			?>
		</div>
			<?php if ( $title_after_text ) : ?>
		<div class="ce-animated-text-after">
				<?php echo $title_after_text; ?>
		</div>
			<?php endif; ?>
	</div>
		<?php endif; ?>
		<?php
		if ( $animation_hub == 'typing_animation' ) :
			$uniqId = uniqid();
			?>
	<div data-optionce='{ "arow_off": "<?php echo $arow_right; ?>", "animiList_id": "typed-strings-<?php echo $uniqId; ?>", "animiList_id_echo": "typed-<?php echo $uniqId; ?>", "speed": <?php echo $animation_speed; ?> }'
		class="typing-animation-loop ce-animated-text display-<?php echo $base_text_item_align . ' ' . $animation_hub . ' ' . $uniqId; ?> base-typo-text <?php echo $content_display_class; ?>">
			<?php if ( $title_before_text ) : ?>
		<div class="ce-animated-text-before">
				<?php echo $title_before_text; ?>
		</div>
			<?php endif; ?>
		<div class="ce-animation-text text-<?php echo $animation_text_item_align; ?>">
			<div id="typed-strings-<?php echo $uniqId; ?>">
			<?php
			foreach ( $animation_text_items_list as $item ) {
				?>
				<p>
				<?php
				echo $item['animation_text_item_single'];
				?>
				</p>
				<?php
			}
			?>
			</div>
			<span id="typed-<?php echo $uniqId; ?>"></span>
		</div>
			<?php if ( $title_after_text ) : ?>
		<div class="ce-animated-text-after">
				<?php echo $title_after_text; ?>
		</div>
			<?php endif; ?>
	</div>
		<?php endif; ?>
</div>
		<?php
	}
}
