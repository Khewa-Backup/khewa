<?php
namespace CrazyElements;

use CrazyElements\PrestaHelper;
use CrazyElements\Widget_Base;

if ( ! defined( '_PS_VERSION_' ) ) {
	exit; // Exit if accessed directly.
}


class Widget_NewsletterSubscribe extends Widget_Base {



	public function get_name() {
		return 'mail_champ';
	}

	public function get_title() {
		return PrestaHelper::__( 'Mailchimp', 'elementor' );
	}

	public function get_icon() {
		return 'ceicon-mailchimp';
	}

	public function get_categories() {
		return array( 'crazy_addons' );
	}

	private function mail_champ_list( $url, $request_type, $api_key, $data = array() ) {
		if ( $request_type == 'GET' ) {
			$url .= '?' . http_build_query( $data );
		}

		$curl    = curl_init( $url );
		$headers = array(
			'Content-Type: application/json',
			'Authorization: Basic ' . base64_encode( 'user:' . $api_key ),
		);
		curl_setopt( $curl, CURLOPT_URL, $url );
		curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
		// curl_setopt($curl, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0');
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, $request_type );
		curl_setopt( $curl, CURLOPT_TIMEOUT, 50 );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
		$res    = curl_exec( $curl );
		$err    = curl_errno( $curl );
		$errmsg = curl_error( $curl );
		$header = curl_getinfo( $curl );
		return $res;
	}

	private function list_id_mailchamp() {
		$api_key = PrestaHelper::get_option( 'mailchimp_data' );
		$data    = array(
			'fields' => 'lists',
			'count'  => 5,
		);
		$url     = 'https://' . substr( $api_key, strpos( $api_key, '-' ) + 1 ) . '.api.mailchimp.com/3.0/lists/';
		$someOne = $this->mail_champ_list( $url, 'GET', $api_key, $data );
		$result  = json_decode( $someOne );

		$mail_list_id_arry = array();
		if ( ! empty( $result->lists ) ) {
			foreach ( $result->lists as $list ) {
				$mail_list_id_arry[ $list->id ] = $list->name . ' ' . $list->stats->member_count;
			}
		}
		return $mail_list_id_arry;
	}

	protected function _register_controls() {
		$this->start_controls_section(
			'form',
			array(
				'label' => PrestaHelper::__( 'Form', 'elementor' ),
			)
		);
		$this->add_control(
			'form_api_key',
			array(
				'label' => PrestaHelper::__( 'Form api key', 'elementor' ),
				'type'  => Controls_Manager::TEXT,
			)
		);
		$this->add_control(
			'form_api_list_id',
			array(
				'label'   => PrestaHelper::__( 'Form api List Id', 'elementor' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $this->list_id_mailchamp(),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'form_header',
			array(
				'label' => PrestaHelper::__( 'Form Header', 'elementor' ),
			)
		);

		$this->add_control(
			'form_header_tag_line',
			array(
				'label'   => PrestaHelper::__( 'Tagline', 'elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => PrestaHelper::__( 'Join with us', 'elementor' ),
			)
		);
		$this->add_control(
			'form_header_title',
			array(
				'label'   => PrestaHelper::__( 'Headings', 'elementor' ),
				'type'    => Controls_Manager::TEXTAREA,
				'default' => PrestaHelper::__( 'Stock clearance! <br> Up to 70% Off for All Items.', 'elementor' ),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'form_fields',
			array(
				'label' => PrestaHelper::__( 'Form Fields', 'elementor' ),
			)
		);

		$this->add_control(
			'icon',
			array(
				'label'   => PrestaHelper::__( 'Icon', 'elementor' ),
				'type'    => Controls_Manager::ICONS,
				'default' => array(
					'value'   => 'fa fa-search',
					'library' => 'solid',
				),
			)
		);

		$this->add_control(
			'button_name',
			array(
				'label'   => PrestaHelper::__( 'Button Text', 'elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => PrestaHelper::__( 'Submit', 'elementor' ),
			)
		);

		$this->add_control(
			'email_placeholder',
			array(
				'label'     => PrestaHelper::__( 'Email Placeholder', 'elementor' ),
				'type'      => Controls_Manager::TEXT,
				'separator' => 'before',
				'default'   => PrestaHelper::__( 'Enter your Email', 'elementor' ),
			)
		);
		$this->add_control(
			'form_fields_first_name',
			array(
				'label'        => PrestaHelper::__( 'First Name', 'elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => '1',
			)
		);

		$this->add_control(
			'first_name_placeholder',
			array(
				'label'     => PrestaHelper::__( 'First Name Placeholder', 'elementor' ),
				'type'      => Controls_Manager::TEXT,
				'condition' => array( 'form_fields_first_name' => '1' ),
				'default'   => PrestaHelper::__( 'Enter your First Name', 'elementor' ),
			)
		);
		$this->add_control(
			'form_fields_last_name',
			array(
				'label'        => PrestaHelper::__( 'Last Name', 'elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => '1',
			)
		);
		$this->add_control(
			'last_name_placeholder',
			array(
				'label'     => PrestaHelper::__( 'Last Name Placeholder', 'elementor' ),
				'type'      => Controls_Manager::TEXT,
				'condition' => array( 'form_fields_last_name' => '1' ),
				'default'   => PrestaHelper::__( 'Enter your Last Name', 'elementor' ),
			)
		);
		$this->add_control(
			'form_fields_phone',
			array(
				'label'        => PrestaHelper::__( 'Phone', 'elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => '1',
			)
		);
		$this->add_control(
			'phone_number_placeholder',
			array(
				'label'     => PrestaHelper::__( 'Phone Placeholder', 'elementor' ),
				'type'      => Controls_Manager::TEXT,
				'condition' => array( 'form_fields_phone' => '1' ),
				'default'   => PrestaHelper::__( 'Enter your Phone Number', 'elementor' ),
			)
		);

		$this->end_controls_section();

		// ------------------------------------------------------------------------------------------------- Style Section

		$this->start_controls_section(
			'advanced',
			array(
				'label' => PrestaHelper::__( 'Dimensions', 'elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'flexed',
			array(
				'label'        => PrestaHelper::__( 'Display Inline', 'elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'flexed',
				'default'      => false,
			)
		);

		$this->add_responsive_control(
			'flexed_gap',
			array(
				'label'      => PrestaHelper::__( 'Gap', 'plugin-domain' ),
				'type'       => Controls_Manager::SLIDER,
				'devices'    => array( 'desktop', 'tablet', 'mobile' ),
				'condition'  => array( 'flexed' => 'flexed' ),
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 1000,
						'step' => 5,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 50,
				),
				'selectors'  => array(
					'{{WRAPPER}} .flexed' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'btn_absolute',
			array(
				'label'        => PrestaHelper::__( 'Button Absolute', 'elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'separator'    => 'before',
				'return_value' => 'btn-absolute',
				'default'      => false,
			)
		);

		$this->add_responsive_control(
			'btn_absolute_top',
			array(
				'label'      => PrestaHelper::__( 'Top', 'plugin-domain' ),
				'type'       => Controls_Manager::SLIDER,
				'devices'    => array( 'desktop', 'tablet', 'mobile' ),
				'condition'  => array( 'btn_absolute' => 'btn-absolute' ),
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 1000,
						'step' => 5,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 3,
				),
				'selectors'  => array(
					'{{WRAPPER}} .btn-absolute' => 'top: {{SIZE}}{{UNIT}};',
				),
			)
		);
		$this->add_responsive_control(
			'btn_absolute_right',
			array(
				'label'      => PrestaHelper::__( 'Right', 'plugin-domain' ),
				'type'       => Controls_Manager::SLIDER,
				'devices'    => array( 'desktop', 'tablet', 'mobile' ),
				'condition'  => array( 'btn_absolute' => 'btn-absolute' ),
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 1000,
						'step' => 5,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 3,
				),
				'selectors'  => array(
					'{{WRAPPER}} .btn-absolute' => 'right: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'inner_box_padding',
			array(
				'label'      => PrestaHelper::__( 'Padding', 'plugin-domain' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .inner-box' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'form_option',
			array(
				'label' => PrestaHelper::__( 'Option', 'elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'tagline_color',
			array(
				'label'     => PrestaHelper::__( 'Tagline Color', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .typo-tagline-text' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'tagline_typography',
				'label'    => PrestaHelper::__( 'Typography', 'plugin-domain' ),
				'selector' => '{{WRAPPER}} .typo-tagline-text',
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => PrestaHelper::__( 'Title Color', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .typo-title-text' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'label'    => PrestaHelper::__( 'Typography', 'plugin-domain' ),
				'selector' => '{{WRAPPER}} .typo-title-text',
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'mobile_background',
				'label'    => PrestaHelper::__( 'Background', 'plugin-domain' ),
				'types'    => array( 'classic', 'gradient', 'video' ),
				'selector' => '{{WRAPPER}} .subscribe-section ,{{WRAPPER}} .newsletter-section',
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'form_input',
			array(
				'label' => PrestaHelper::__( 'Input Option', 'elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'input_bg',
			array(
				'label'     => PrestaHelper::__( 'Background', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .form-group input' => 'background: {{VALUE}}',
				),
			)
		);

		// Tabs

		$this->start_controls_tabs( 'input_tab' );

		$this->start_controls_tab(
			'tab_input',
			array(
				'label' => PrestaHelper::__( 'Input', 'elementor' ),
			)
		);

		$this->add_control(
			'input_text_color',
			array(
				'label'     => PrestaHelper::__( 'Input Font Color', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .subscribe-form .form-group input' => 'color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();
		$this->start_controls_tab(
			'tab_placeholder',
			array(
				'label' => PrestaHelper::__( 'Placeholder', 'elementor' ),
			)
		);

		$this->add_control(
			'placeholder_text_color',
			array(
				'label'     => PrestaHelper::__( 'Input Font Color', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .subscribe-section .inner-box .subscribe-form .form-group input::-webkit-input-placeholder' => 'color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		// Tabs

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'input_typography',
				'label'    => PrestaHelper::__( 'Typography', 'plugin-domain' ),
				'selector' => '{{WRAPPER}} .form-group input',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'      => 'border',
				'separator' => 'before',
				'label'     => PrestaHelper::__( 'Border', 'plugin-domain' ),
				'selector'  => '{{WRAPPER}} .subscribe-section .inner-box .subscribe-form .form-group input',
			)
		);

		$this->add_control(
			'input_padding',
			array(
				'label'      => PrestaHelper::__( 'Padding', 'plugin-domain' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .subscribe-section .inner-box .subscribe-form .form-group input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->add_control(
			'input_margin',
			array(
				'label'      => PrestaHelper::__( 'Margin', 'plugin-domain' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .subscribe-section .inner-box .subscribe-form .form-group input' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'border_radius',
			array(
				'label'      => PrestaHelper::__( 'Border Radius', 'plugin-domain' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .subscribe-section .inner-box .subscribe-form .form-group input' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'input_flexed_width',
			array(
				'label'      => PrestaHelper::__( 'Width', 'plugin-domain' ),
				'type'       => Controls_Manager::SLIDER,
				'condition'  => array( 'flexed' => 'flexed' ),
				'devices'    => array( 'desktop', 'tablet', 'mobile' ),
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 1000,
						'step' => 5,
					),
					'%'  => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 300,
				),
				'selectors'  => array(
					'{{WRAPPER}} .form-group input' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'form_btn',
			array(
				'label' => PrestaHelper::__( 'Button Option', 'elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'form_btn_typography',
				'label'    => PrestaHelper::__( 'Typography', 'plugin-domain' ),
				'selector' => '{{WRAPPER}} .form-btn',
			)
		);

		// Tabs

		$this->start_controls_tabs( 'button_tab' );
		$this->start_controls_tab(
			'tab_button_normal',
			array(
				'label' => PrestaHelper::__( 'Normal', 'elementor' ),
			)
		);
		$this->add_control(
			'btn_normal',
			array(
				'label'     => PrestaHelper::__( 'Color', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .form-btn' => 'color: {{VALUE}}',
				),
			)
		);
		$this->add_control(
			'btn_bg_normal',
			array(
				'label'     => PrestaHelper::__( 'Background', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .form-btn' => 'background: {{VALUE}}',
				),
			)
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'btn_border',
				'label'    => PrestaHelper::__( 'Border', 'plugin-domain' ),
				'selector' => '{{WRAPPER}} .form-btn',
			)
		);
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'box_shadow',
				'label'    => PrestaHelper::__( 'Box Shadow', 'plugin-domain' ),
				'selector' => '{{WRAPPER}} .form-btn',
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab(
			'tab_button_hover',
			array(
				'label' => PrestaHelper::__( 'Hover', 'elementor' ),
			)
		);
		$this->add_control(
			'btn_hover',
			array(
				'label'     => PrestaHelper::__( 'Color', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .form-btn:hover' => 'color: {{VALUE}}',
				),
			)
		);
		$this->add_control(
			'btn_bg_hover',
			array(
				'label'     => PrestaHelper::__( 'Background', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .form-btn:hover' => 'background: {{VALUE}}',
				),
			)
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'btn_border_hover',
				'label'    => PrestaHelper::__( 'Border', 'plugin-domain' ),
				'selector' => '{{WRAPPER}} .form-btn:hover',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'box_shadow_hover',
				'label'    => PrestaHelper::__( 'Box Shadow', 'plugin-domain' ),
				'selector' => '{{WRAPPER}} .form-btn:hover',
			)
		);
		$this->end_controls_tab();

		$this->end_controls_tabs();

		// Tabs

		$this->add_responsive_control(
			'flexed_btn_width',
			array(
				'label'      => PrestaHelper::__( 'Width', 'plugin-domain' ),
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
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 300,
				),
				'selectors'  => array(
					'{{WRAPPER}} .form-btn' => 'width: {{SIZE}}{{UNIT}};',
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
					'{{WRAPPER}} .form-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'btn_border_radius',
			array(
				'label'      => PrestaHelper::__( 'Border Radius', 'plugin-domain' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .form-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings               = $this->get_settings();
		$form_api_key           = $settings['form_api_key'];
		$form_api_list_id       = $settings['form_api_list_id'];
		$form_fields_first_name = $settings['form_fields_first_name'];
		$form_fields_last_name  = $settings['form_fields_last_name'];
		$form_fields_phone      = $settings['form_fields_phone'];
		$form_header_tag_line   = $settings['form_header_tag_line'];
		$form_header_title      = $settings['form_header_title'];
		$url                    = PrestaHelper::getAjaxUrl();
		?>

<section class="subscribe-section ps-newsletter-section centred ps-newsletter-section-one">
	<div class="auto-container clearfix">
		<div class="inner-box">
			<?php if ( $form_header_tag_line ) : ?>
			<h4 class="typo-tagline-text"><?php echo $form_header_tag_line; ?></h4>
				<?php
					endif;
			if ( $form_header_title ) :
				?>
			<h2 class="typo-title-text">
				<?php echo $form_header_title; ?>
			</h2>
			<?php endif; ?>

			<form class="subscribe-form" action="<?php echo $url; ?>" id="mailchimp">
				<div class="form-group <?php echo $settings['flexed']; ?>">

					<?php
					if ( $form_fields_first_name == 1 ) :
						?>
					<input type="text" name="fname" placeholder="<?php echo $settings['first_name_placeholder']; ?>" />
						<?php
									else :
										?>
					<input type="hidden" name="fname"
						placeholder="<?php echo $settings['first_name_placeholder']; ?>" />
										<?php
											endif;
									?>

					<?php
					if ( $form_fields_last_name == 1 ) :
						?>
					<input type="text" name="lname" placeholder="<?php echo $settings['last_name_placeholder']; ?>" />
						<?php
									else :
										?>
					<input type="hidden" name="lname" placeholder="<?php echo $settings['last_name_placeholder']; ?>" />
										<?php
											endif;
									?>

					<?php
					if ( $form_fields_phone == 1 ) :
						?>
					<input type="phone" name="phone"
						placeholder="<?php echo $settings['phone_number_placeholder']; ?>" />
						<?php
									else :
										?>
					<input type="hidden" name="phone"
						placeholder="<?php echo $settings['phone_number_placeholder']; ?>" />
										<?php
											endif;
									?>

					<input type="email" name="email" placeholder="<?php echo $settings['email_placeholder']; ?>" />

					<input type="hidden" name="listidhidden" value="<?php echo $form_api_list_id; ?>" />
					<input type="hidden" name="action" value="mailchimpsubscribe" />
					<button class="form-btn theme-btn <?php echo $settings['btn_absolute']; ?>"
						id="news_subscribe"><?php Icons_Manager::render_icon( $settings['icon'], array( 'aria-hidden' => 'true' ) ); ?>
						<?php echo $settings['button_name']; ?></button>
				</div>
			</form>
			<div id="ps-mc-form-notice-area" style="display: none;">
				<div id="ps-mc-form-notice">

				</div>
			</div>
		</div>
	</div>
</section>
		<?php
	}
}
