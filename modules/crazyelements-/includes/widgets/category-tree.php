<?php
namespace CrazyElements;

use CrazyElements\PrestaHelper;
use CrazyElements\Widget_Base;

if ( ! defined( '_PS_VERSION_' ) ) {
	exit;
}

class Widget_CategoryTree extends Widget_Base {

	private $catg_root;
	private $max_depth;
	private $orderby;
	private $order;

	public function get_name() {
		return 'category_tree_links';
	}

	public function get_title() {
		return PrestaHelper::__( 'Category Tree Links', 'elementor' );
	}

	public function get_icon() {
		return 'ceicon-catgtree-widget';
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
			'catg_root',
			array(
				'label'   => PrestaHelper::__( 'Select Category Root', 'elementor' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'home'           => PrestaHelper::__( 'Home category', 'elementor' ),
					'current'        => PrestaHelper::__( 'Current category', 'elementor' ),
					'parent'         => PrestaHelper::__( 'Parent category', 'elementor' ),
					'current_parent' => PrestaHelper::__( 'Current category, unless it has no subcategories, in which case the parent category of the current category is used', 'elementor' ),

				),
			)
		);

		$this->add_control(
			'max_depth',
			array(
				'label'   => PrestaHelper::__( 'Maximum depth', 'elementor' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 4,
			)
		);

		$this->add_control(
			'orderby',
			array(
				'label'   => PrestaHelper::__( 'Order By', 'elementor' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'by_name'     => PrestaHelper::__( 'By Name', 'elementor' ),
					'by_position' => PrestaHelper::__( 'By Position', 'elementor' ),
				),
			)
		);

		$this->add_control(
			'order',
			array(
				'label'   => PrestaHelper::__( 'Order', 'elementor' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'descending' => PrestaHelper::__( 'Descending', 'elementor' ),
					'ascending'  => PrestaHelper::__( 'Ascending', 'elementor' ),
				),
			)
		);

		$this->end_controls_section();
	}

	private function CrazyGetCategories( $category ) {
		$context  = \Context::getContext();
		$range    = '';
		$maxdepth = $this->max_depth;
		if ( \Validate::isLoadedObject( $category ) ) {
			if ( $maxdepth > 0 ) {
				$maxdepth += $category->level_depth;
			}
			$range = 'AND nleft >= ' . (int) $category->nleft . ' AND nright <= ' . (int) $category->nright;
		}

		$resultIds     = array();
		$resultParents = array();
		$result        = \Db::getInstance( _PS_USE_SQL_SLAVE_ )->executeS(
			'
			SELECT c.id_parent, c.id_category, cl.name, cl.description, cl.link_rewrite
			FROM `' . _DB_PREFIX_ . 'category` c
			INNER JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON (c.`id_category` = cl.`id_category` AND cl.`id_lang` = ' . (int) $context->language->id . \Shop::addSqlRestrictionOnLang( 'cl' ) . ')
			INNER JOIN `' . _DB_PREFIX_ . 'category_shop` cs ON (cs.`id_category` = c.`id_category` AND cs.`id_shop` = ' . (int) $context->shop->id . ')
			WHERE (c.`active` = 1 OR c.`id_category` = ' . (int) \Configuration::get( 'PS_HOME_CATEGORY' ) . ')
			AND c.`id_category` != ' . (int) \Configuration::get( 'PS_ROOT_CATEGORY' ) . '
			' . ( (int) $maxdepth != 0 ? ' AND `level_depth` <= ' . (int) $maxdepth : '' ) . '
			' . $range . '
			AND c.id_category IN (
				SELECT id_category
				FROM `' . _DB_PREFIX_ . 'category_group`
				WHERE `id_group` IN (' . pSQL( implode( ', ', \Customer::getGroupsStatic( (int) $context->customer->id ) ) ) . ')
			)
			ORDER BY `level_depth` ASC, ' . ( $this->orderby ? 'cl.`name`' : 'cs.`position`' ) . ' ' . ( $this->order ? 'DESC' : 'ASC' )
		);
		foreach ( $result as &$row ) {
			$resultParents[ $row['id_parent'] ][] = &$row;
			$resultIds[ $row['id_category'] ]     = &$row;
		}

		return $this->CrazyGetTree( $resultParents, $resultIds, $maxdepth, ( $category ? $category->id : null ) );
	}

	public function CrazyGetTree( $resultParents, $resultIds, $maxDepth, $id_category = null, $currentDepth = 0 ) {
		$context = \Context::getContext();

		if ( is_null( $id_category ) ) {
			$id_category = $context->shop->getCategory();
		}

		$children = array();

		if ( isset( $resultParents[ $id_category ] ) && count( $resultParents[ $id_category ] ) && ( $maxDepth == 0 || $currentDepth < $maxDepth ) ) {
			foreach ( $resultParents[ $id_category ] as $subcat ) {
				$children[] = $this->CrazyGetTree( $resultParents, $resultIds, $maxDepth, $subcat['id_category'], $currentDepth + 1 );
			}
		}

		if ( isset( $resultIds[ $id_category ] ) ) {
			$link = $context->link->getCategoryLink( $id_category, $resultIds[ $id_category ]['link_rewrite'] );
			$name = $resultIds[ $id_category ]['name'];
			$desc = $resultIds[ $id_category ]['description'];
		} else {
			$link = $name = $desc = '';
		}

		return array(
			'id'       => $id_category,
			'link'     => $link,
			'name'     => $name,
			'desc'     => $desc,
			'children' => $children,
		);
	}

	private function crazy_last_visited_category( $context ) {
		if ( method_exists( $context->controller, 'getCategory' ) && ( $category = $context->controller->getCategory() ) ) {
			$context->cookie->last_visited_category = $category->id;
		} elseif ( method_exists( $context->controller, 'getProduct' ) && ( $product = $context->controller->getProduct() ) ) {
			if ( ! isset( $context->cookie->last_visited_category )
				|| ! \Product::idIsOnCategoryId( $product->id, array( array( 'id_category' => $context->cookie->last_visited_category ) ) )
				|| ! \Category::inShopStatic( $context->cookie->last_visited_category, $context->shop )
			) {
				$context->cookie->last_visited_category = (int) $product->id_category_default;
			}
		}
	}

	protected function render() {
		$settings        = $this->get_settings_for_display();
		$this->catg_root = $settings['catg_root'];
		$this->max_depth = $settings['max_depth'];
		$this->orderby   = $settings['orderby'];
		$this->order     = $settings['order'];
		$context         = \Context::getContext();

		$this->crazy_last_visited_category( $context );

		$category = new \Category( (int) \Configuration::get( 'PS_HOME_CATEGORY' ), $context->language->id );

		if ( $this->catg_root && isset( $context->cookie->last_visited_category ) && $context->cookie->last_visited_category ) {
			$category = new \Category( $context->cookie->last_visited_category, $context->language->id );
			if ( $this->catg_root == 'parent' && ! $category->is_root_category && $category->id_parent ) {
				$category = new \Category( $category->id_parent, $context->language->id );
			} elseif ( $this->catg_root == 'current_parent' && ! $category->is_root_category && ! $category->getSubCategories( $category->id, true ) ) {
				$category = new \Category( $category->id_parent, $context->language->id );
			}
		}
		$context->smarty->assign(
			array(
				'categories'      => $this->CrazyGetCategories( $category ),
				'currentCategory' => $category->id,
			)
		);

		$tpl       = 'ps_categorytree.tpl';
		$theme_tpl = _PS_THEME_DIR_ . 'modules/ps_categorytree/views/templates/hook/' . $tpl;

		echo $context->smarty->fetch( file_exists( $theme_tpl ) ? $theme_tpl : _PS_MODULE_DIR_ . $tpl );
	}

	protected function _content_template() {
	}
}
