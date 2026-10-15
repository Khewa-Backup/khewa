<?php

namespace CrazyElements\Core\Common\Modules\Finder\Categories;

use CrazyElements\Core\Common\Modules\Finder\Base_Category;
use CrazyElements\Settings as ElementorSettings;

use CrazyElements\PrestaHelper; if ( ! defined( '_PS_VERSION_' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Settings Category
 *
 * Provides items related to Elementor's settings.
 */
class Settings extends Base_Category {

	/**
	 * Get title.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @return string
	 */
	public function get_title() {
		return PrestaHelper::__( 'Settings', 'elementor' );
	}

	/**
	 * Get category items.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @param array $options
	 *
	 * @return array
	 */
	public function get_category_items( array $options = [] ) {
		$settings_url = ElementorSettings::get_url();

		return [
			'general-settings' => [
				'title' => PrestaHelper::__( 'General Settings', 'elementor' ),
				'url' => $settings_url,
				'keywords' => [ 'general', 'settings', 'elementor' ],
			],
			'style' => [
				'title' => PrestaHelper::__( 'Style', 'elementor' ),
				'url' => $settings_url . '#tab-style',
				'keywords' => [ 'style', 'settings', 'elementor' ],
			],
			'advanced' => [
				'title' => PrestaHelper::__( 'Advanced', 'elementor' ),
				'url' => $settings_url . '#tab-advanced',
				'keywords' => [ 'advanced', 'settings', 'elementor' ],
			],
		];
	}
}
