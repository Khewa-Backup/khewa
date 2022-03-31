<?php
namespace CrazyElements\Core\Common\Modules\Finder\Categories;

use CrazyElements\Core\Common\Modules\Finder\Base_Category;
use CrazyElements\Core\RoleManager\Role_Manager;
use CrazyElements\TemplateLibrary\Source_Local;

use CrazyElements\PrestaHelper; if ( ! defined( '_PS_VERSION_' ) ) {
	exit; // Exit if accessed directly
}

/**
 * General Category
 *
 * Provides general items related to Elementor Admin.
 */
class General extends Base_Category {

	/**
	 * Get title.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @return string
	 */
	public function get_title() {
		return PrestaHelper::__( 'General', 'elementor' );
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
			'saved-templates' => [
				'title' => PrestaHelper::_x( 'Saved Templates', 'Template Library', 'elementor' ),
				'icon' => 'library-save',
				'url' => Source_Local::get_admin_url(),
				'keywords' => [ 'template', 'section', 'page', 'library' ],
			],
			'system-info' => [
				'title' => PrestaHelper::__( 'System Info', 'elementor' ),
				'icon' => 'info',
				'url' => PrestaHelper::admin_url( 'admin.php?page=elementor-system-info' ),
				'keywords' => [ 'system', 'info', 'environment', 'elementor' ],
			],
			'role-manager' => [
				'title' => PrestaHelper::__( 'Role Manager', 'elementor' ),
				'icon' => 'person',
				'url' => Role_Manager::get_url(),
				'keywords' => [ 'role', 'manager', 'user', 'elementor' ],
			],
			'knowledge-base' => [
				'title' => PrestaHelper::__( 'Knowledge Base', 'elementor' ),
				'url' => PrestaHelper::admin_url( 'admin.php?page=go_knowledge_base_site' ),
				'keywords' => [ 'help', 'knowledge', 'docs', 'elementor' ],
			],
		];
	}
}
