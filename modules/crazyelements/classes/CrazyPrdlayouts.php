<?php

if ( ! defined( '_PS_VERSION_' ) ) {
	exit;
}

class CrazyPrdlayouts extends ObjectModel {




	/**
	 * Id_crazyprdlayouts id of the item.
	 *
	 * @var mixed
	 */
	public $id_crazyprdlayouts;

	/**
	 * Active
	 *
	 * @var int
	 */
	public $active = 1;
	/**
	 * Product_page
	 *
	 * @var mixed
	 */
	public $product_page;
	/**
	 * Specific_product
	 *
	 * @var mixed
	 */
	public $specific_product;
	/**
	 * Specific_product_catg
	 *
	 * @var mixed
	 */
	public $specific_product_catg;
	/**
	 * Content_type
	 *
	 * @var mixed
	 */
	public $content_type;
	/**
	 * Modules_list
	 *
	 * @var mixed
	 */
	public $modules_list;
	/**
	 * Module_hook_list
	 *
	 * @var mixed
	 */
	public $module_hook_list;
	/**
	 * Position
	 *
	 * @var mixed
	 */
	public $position;
	/**
	 * Title
	 *
	 * @var mixed
	 */
	public $title;
	public $content;


	
	public static $definition = array(
		'table'     => 'crazyprdlayouts',
		'primary'   => 'id_crazyprdlayouts',
		'multilang' => true,
		'fields'    => array(
			'content_type'          => array(
				'type'     => self::TYPE_STRING,
				'validate' => 'isString',
			),
			'modules_list'          => array(
				'type'     => self::TYPE_STRING,
				'validate' => 'isString',
			),
			'module_hook_list'      => array(
				'type'     => self::TYPE_STRING,
				'validate' => 'isString',
			),
			'product_page'          => array(
				'type'     => self::TYPE_STRING,
				'validate' => 'isString',
			),
			'specific_product'      => array(
				'type'     => self::TYPE_STRING,
				'validate' => 'isString',
			),
			'specific_product_catg' => array(
				'type'     => self::TYPE_STRING,
				'validate' => 'isString',
			),
			'position'              => array( 'type' => self::TYPE_INT ),
			'active'                => array(
				'type'     => self::TYPE_BOOL,
				'validate' => 'isBool',
				'required' => true,
			),
			'title'                 => array(
				'type'     => self::TYPE_STRING,
				'lang'     => true,
				'validate' => 'isString',
				'required' => true,
			)
		)
	);


	/**
	 * __construct
	 *
	 * @param  mixed $id      id of the tab.
	 * @param  mixed $id_lang laguage id.
	 * @param  mixed $id_shop id of the shop.
	 * @return void
	 */
	public function __construct( $id = null, $id_lang = null, $id_shop = null ) {
		Shop::addTableAssociation( 'crazyprdlayouts', array( 'type' => 'shop' ) );
		parent::__construct( $id, $id_lang, $id_shop );
	}

	/**
	 * Add
	 *
	 * @param mixed $autodate    automatically add the date.
	 * @param mixed $null_values if accept null values.
	 */
	public function add( $autodate = true, $null_values = false ) {

		if ( $this->position <= 0 ) {
			$this->position = self::getHigherPosition() + 1;
		}
		if ( ! parent::add( $autodate, $null_values ) || ! Validate::isLoadedObject( $this ) ) {
			return false;
		}

		return true;
	}

	/**
	 * GetHigherPosition gets the higher position.
	 */
	public static function getHigherPosition() {
		$sql      = 'SELECT MAX(`position`)
                FROM `' . _DB_PREFIX_ . 'crazyprdlayouts`';
		$position = DB::getInstance()->getValue( $sql );
		return ( is_numeric( $position ) ) ? $position : -1;
	}

	/**
	 * GetInstance provides the instance of the class.
	 */
	public static function GetInstance() {
		$ins = new classyproductlayout();
		return $ins;
	}

	/**
	 * UpdatePosition updates the osition of the class.
	 *
	 * @param mixed $way      update way.
	 * @param mixed $position postion of the item.
	 */
	public function updatePosition( $way, $position ) {
		if ( ! $res = Db::getInstance()->executeS(
			'
            SELECT `id_crazyprdlayouts`, `position`
            FROM `' . _DB_PREFIX_ . 'crazyprdlayouts`
            ORDER BY `position` ASC'
		)
		) {
			return false;
		}
		foreach ( $res as $crazyprdlayouts ) {
			if ( (int) $crazyprdlayouts['id_crazyprdlayouts'] == (int) $this->id ) {
				$moved_crazyprdlayouts = $crazyprdlayouts;
			}
		}
		if ( ! isset( $moved_crazyprdlayouts ) || ! isset( $position ) ) {
			return false;
		}
		$query_1 = ' UPDATE `' . _DB_PREFIX_ . 'crazyprdlayouts`
        SET `position`= `position` ' . ( $way ? '- 1' : '+ 1' ) . '
        WHERE `position`
        ' . ( $way
		? '> ' . (int) $moved_crazyprdlayouts['position'] . ' AND `position` <= ' . (int) $position
		: '< ' . (int) $moved_crazyprdlayouts['position'] . ' AND `position` >= ' . (int) $position . '
        ' );
		$query_2 = ' UPDATE `' . _DB_PREFIX_ . 'crazyprdlayouts`
        SET `position` = ' . (int) $position . '
        WHERE `id_crazyprdlayouts` = ' . (int) $moved_crazyprdlayouts['id_crazyprdlayouts'];
		return ( Db::getInstance()->execute( $query_1 )
		&& Db::getInstance()->execute( $query_2 ) );
	}


	/**
	 * GetTabContentByProductId gets the tab contents by product id.
	 *
	 * @param mixed $id_product id of the product.
	 */
	public function getLayoutByProductId( $id_product = 1 ) {
		$reslt       = array();
		$resltcat    = array();
		$id_lang     = (int) Context::getContext()->language->id;
		$id_shop     = (int) Context::getContext()->shop->id;
		$sql         = 'SELECT * FROM `' . _DB_PREFIX_ . 'crazyprdlayouts` v 
                INNER JOIN `' . _DB_PREFIX_ . 'crazyprdlayouts_lang` vl ON (v.`id_crazyprdlayouts` = vl.`id_crazyprdlayouts` AND vl.`id_lang` = ' . $id_lang . ')
                INNER JOIN `' . _DB_PREFIX_ . 'crazyprdlayouts_shop` vs ON (v.`id_crazyprdlayouts` = vs.`id_crazyprdlayouts` AND vs.`id_shop` = ' . $id_shop . ')
                WHERE ';
		$sql        .= ' v.`active` = 1 ORDER BY v.`position` ASC';
		$sqlcat      = 'SELECT `id_category` FROM `' . _DB_PREFIX_ . 'category_product` 
                		WHERE `id_product`=' . $id_product;
		$cache_id    = md5( $sql );
		$cachecat_id = md5( $sqlcat );

		if ( ! Cache::isStored( $cache_id ) ) {
			$resultcats = Db::getInstance()->executeS( $sqlcat );
			if ( isset( $resultcats ) && ! empty( $resultcats ) ) {

				foreach ( $resultcats as $i => $result ) {

					$resltcat[] = $result['id_category'];
				}
			}
		}

		if ( ! Cache::isStored( $cache_id ) ) {
			$results = Db::getInstance()->executeS( $sql );
			if ( isset( $results ) && ! empty( $results ) ) {

				foreach ( $results as $i => $result ) {
					if ( isset( $result['product_page'] ) && $result['product_page'] == 1 ) {
						$reslt[ $i ] = $result;
					} elseif ( isset( $result['specific_product_catg'] ) && $result['specific_product_catg'] != '' ) {

						$specific_product_catg_arr = explode( '-', $result['specific_product_catg'] );
						unset( $specific_product_catg_arr[ count( $specific_product_catg_arr ) - 1 ] );
						$intersect = array_intersect( $resltcat, $specific_product_catg_arr );
						if ( isset( $intersect ) && ! empty( $intersect ) ) {
							$reslt[ $i ] = $result;
						}
					} else {
						$specific_product_arr = explode( '-', $result['specific_product'] );
						if ( isset( $specific_product_arr ) && ! empty( $specific_product_arr ) ) {
							unset( $specific_product_arr[ count( $specific_product_arr ) - 1 ] );
							if ( in_array( $id_product, $specific_product_arr ) ) {
								$reslt[ $i ] = $result;
							}
						}
					}
				}
			}
			$outputs = $this->ContentFilterEngine( $reslt );
			Cache::store( $cache_id, $outputs );
		}
		return Cache::retrieve( $cache_id );
	}

	/**
	 * ContentFilterEngine filters the results.
	 *
	 * @param mixed $results results fetched from database.
	 */
	public function ContentFilterEngine( $results = array() ) {
		$outputs = array();
		if ( isset( $results ) && ! empty( $results ) ) {
			$i = 0;
			foreach ( $results as $classyprdlayout_values ) {
				foreach ( $classyprdlayout_values as $classyprdlayout_key => $vcval ) {
					if ( $classyprdlayout_key == 'content' ) {
						// $outputs[ $i ]['content'] = $this->vctab_content_filter( $vcval );
						$outputs[ $i ]['content'] = $vcval;
					}
					if ( $classyprdlayout_key == 'title' ) {
						$outputs[ $i ]['title'] = $vcval;
					}
					if ( $classyprdlayout_key == 'id_crazyprdlayouts' ) {
						$outputs[ $i ]['id_crazyprdlayouts'] = $vcval;
					}
					if ( $classyprdlayout_key == 'content_type' ) {
						$outputs[ $i ]['content_type'] = $vcval;
					}
					if ( $classyprdlayout_key == 'modules_list' ) {
						$outputs[ $i ]['modules_list'] = $vcval;
					}
					if ( $classyprdlayout_key == 'module_hook_list' ) {
						$outputs[ $i ]['module_hook_list'] = $vcval;
					}
				}
				$i++;
			}
		}
		return $outputs;
	}
}