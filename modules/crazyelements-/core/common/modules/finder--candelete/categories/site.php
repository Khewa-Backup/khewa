<?php
namespace CrazyElements\Core\Common\Modules\Finder\Categories;

use CrazyElements\Core\Common\Modules\Finder\Base_Category;
use CrazyElements\Core\RoleManager\Role_Manager;
use CrazyElements\TemplateLibrary\Source_Local;

use CrazyElements\PrestaHelper; if ( ! defined( '_PS_VERSION_' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Site Category
 *
 * Provides general site items.
 */
class Site extends Base_Category {

	/**
	 * Get title.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @return string
	 */
	public function get_title() {
		return PrestaHelper::__( 'Site', 'elementor' );
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
		return [
			'homepage' => [
				'title' => PrestaHelper::__( 'Homepage', 'elementor' ),
				'url' => PrestaHelper::home_url(),
				'icon' => 'home-heart',
				'keywords' => [ 'home', 'page' ],
			],
			'wordpress-dashboard' => [
				'title' => PrestaHelper::__( 'Dashboard', 'elementor' ),
				'icon' => 'dashboard',
				'url' => PrestaHelper::admin_url(),
				'keywords' => [ 'dashboard', 'wordpress' ],
			],
			'wordpress-menus' => [
				'title' => PrestaHelper::__( 'Menus', 'elementor' ),
				'icon' => 'wordpress',
				'url' => PrestaHelper::admin_url( 'nav-menus.php' ),
				'keywords' => [ 'menu', 'wordpress' ],
			],
			'wordpress-themes' => [
				'title' => PrestaHelper::__( 'Themes', 'elementor' ),
				'icon' => 'wordpress',
				'url' => PrestaHelper::admin_url( 'themes.php' ),
				'keywords' => [ 'themes', 'wordpress' ],
			],
			'wordpress-customizer' => [
				'title' => PrestaHelper::__( 'Customizer', 'elementor' ),
				'icon' => 'wordpress',
				'url' => PrestaHelper::admin_url( 'customize.php' ),
				'keywords' => [ 'customizer', 'wordpress' ],
			],
			'wordpress-plugins' => [
				'title' => PrestaHelper::__( 'Plugins', 'elementor' ),
				'icon' => 'wordpress',
				'url' => PrestaHelper::admin_url( 'plugins.php' ),
				'keywords' => [ 'plugins', 'wordpress' ],
			],
			'wordpress-users' => [
				'title' => PrestaHelper::__( 'Users', 'elementor' ),
				'icon' => 'wordpress',
				'url' => PrestaHelper::admin_url( 'users.php' ),
				'keywords' => [ 'users', 'profile', 'wordpress' ],
			],
		];
	}
}
