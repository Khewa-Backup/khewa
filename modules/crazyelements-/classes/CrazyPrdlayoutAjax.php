<?php

if ( ! defined( '_PS_VERSION_' ) ) {
	exit;
}

/**
 * ClassyperAjax conn functions to get ajax data.
 */
class CrazyPrdlayoutAjax {

	/**
	 * GetProductsByName gets the products by name written in the input.
	 */
	public function getProductsByName() {
		$context = Context::getContext();
		$id_lang = (int) $context->language->id;
		$front   = true;
		if ( ! in_array( $context->controller->controller_type, array( 'front', 'modulefront' ) ) ) {
			$front = false;
		}

		$q     = Tools::getValue( 'q' );
		$exid  = Tools::getValue( 'excludeIds' );
		$limit = Tools::getValue( 'limit' );
		$exSql = '';
		if ( ! empty( $exid ) ) {
			$exid   = substr( $exid, strlen( $exid ) - 1 ) == ',' ? substr( $exid, 0, strrpos( $exid, ',' ) ) : $exid;
			$exSql .= ' AND p.`id_product` NOT IN(';
			$exSql .= $exid;
			$exSql .= ') ';
		}

		$sql = 'SELECT p.`id_product`, pl.`name`
                FROM `' . _DB_PREFIX_ . 'product` p
                ' . Shop::addSqlAssociation( 'product', 'p' ) . '
                LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (p.`id_product` = pl.`id_product` ' . Shop::addSqlRestrictionOnLang( 'pl' ) . ')
                WHERE pl.`id_lang` = ' . (int) $id_lang . '
                ' . ( $front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '' ) .
		' AND pl.`name` LIKE "%' . pSQL( $q ) . '%" ' . $exSql .
		'ORDER BY pl.`name` LIMIT ' . $limit;

		$rs   = Db::getInstance( _PS_USE_SQL_SLAVE_ )->executeS( $sql, true, false );
		$rslt = '';
		foreach ( $rs as $r ) {
			$rslt .= $r['name'] . '&nbsp;|';
			$rslt .= $r['id_product'] . "\n";
		}
		return $rslt;
	}

	/**
	 * GetCatsByName gets the categories by name written in the input.
	 */
	public function getCatsByName() {
		$context = Context::getContext();
		$id_lang = (int) $context->language->id;
		$limit   = Tools::getValue( 'limit' );
		$q       = Tools::getValue( 'q' );
		$exid    = Tools::getValue( 'excludeIds' );

		$exSql = '';
		if ( ! empty( $exid ) ) {
			$exid   = substr( $exid, strlen( $exid ) - 1 ) == ',' ? substr( $exid, 0, strrpos( $exid, ',' ) ) : $exid;
			$exSql .= ' AND p.`id_category` NOT IN(';
			$exSql .= $exid;
			$exSql .= ') ';
		}

		$sql = 'SELECT p.`id_category`, pl.`name`
                FROM `' . _DB_PREFIX_ . 'category` p
                LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` pl ON (p.`id_category` = pl.`id_category` ' . Shop::addSqlRestrictionOnLang( 'pl' ) . ')
                WHERE pl.`id_lang` = ' . (int) $id_lang . '
                 AND pl.`name` LIKE "%' . pSQL( $q ) . '%" ' . $exSql .
		'ORDER BY pl.`name` ASC LIMIT ' . $limit;

		$rs   = Db::getInstance( _PS_USE_SQL_SLAVE_ )->executeS( $sql, true, false );
		$rslt = '';
		foreach ( $rs as $r ) {
			$rslt .= $r['name'] . '&nbsp;|';
			$rslt .= $r['id_category'] . "\n";
		}
		return $rslt;
	}
    public function getProductsById( $ids ) {

		$context = Context::getContext();
		$id_lang = (int) Context::getContext()->language->id;
		$front   = true;
		if ( ! in_array( $context->controller->controller_type, array( 'front', 'modulefront' ) ) ) {
			$front = false;
		}
		if ( empty( $ids ) ) {
			return false;
		} elseif ( $ids == '' ) {
			return false;
		}
		$limit = Tools::getValue( 'limit' ) ? pSQL( Tools::getValue( 'limit' ) ) : 60;

		$sqlids = '';
		if ( is_string( $ids ) ) {
			if ( $ids == 'random' ) {
				$sqlids = '';
				$limit  = 1;
			} else {
				$ids = explode( '-', $ids );
				unset( $ids[ count( $ids ) - 1 ] );
				foreach ( $ids as $k => $id ) {
					if ( $k > 0 ) {
						$sqlids .= ',';
					}
					$sqlids .= $id;
				}
			}
		} else {
			foreach ( $ids as $k => $id ) {
				if ( $k > 0 ) {
					$sqlids .= ',';
				}
				$sqlids .= $id['id_specify_page'];
			}
		}

		$sql = '';

		if ( $sqlids == '' ) {
			$sql = 'SELECT p.`id_product`, pl.`name`
			FROM `' . _DB_PREFIX_ . 'product` p
			' . Shop::addSqlAssociation( 'product', 'p' ) . '
			LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (p.`id_product` = pl.`id_product` ' . Shop::addSqlRestrictionOnLang( 'pl' ) . ')
			WHERE pl.`id_lang` = ' . (int) $id_lang . '
			' . ( $front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '' ) .
			'ORDER BY pl.`name` LIMIT ' . $limit;
		} else {
			$sql = 'SELECT p.`id_product`, pl.`name`
			FROM `' . _DB_PREFIX_ . 'product` p
			' . Shop::addSqlAssociation( 'product', 'p' ) . '
			LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (p.`id_product` = pl.`id_product` ' . Shop::addSqlRestrictionOnLang( 'pl' ) . ')
			WHERE pl.`id_lang` = ' . (int) $id_lang . '
			' . ( $front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '' ) .
			' AND p.`id_product` IN(' . $sqlids . ')' .
			'ORDER BY pl.`name` LIMIT ' . $limit;
		}

		$rs   = Db::getInstance( _PS_USE_SQL_SLAVE_ )->executeS( $sql );
		$rslt = array();
		foreach ( $rs as $i => $r ) {
			$rslt[ $i ]['id_product'] = $r['id_product'];
			$rslt[ $i ]['name']       = $r['name'];
			$i++;
		}
		return $rslt;
	}

	/**
	 * GetCatsByIdgets the categories by id fetched from database.
	 *
	 * @param mixed $ids categories ids.
	 */
	public function getCatsById( $ids ) {
		$context = Context::getContext();
		$id_lang = (int) Context::getContext()->language->id;
		$front   = true;
		if ( ! in_array( $context->controller->controller_type, array( 'front', 'modulefront' ) ) ) {
			$front = false;
		}

		if ( empty( $ids ) ) {
			return false;
		}

		$sqlids = '';
		if ( is_string( $ids ) ) {
			$ids = explode( '-', $ids );
			unset( $ids[ count( $ids ) - 1 ] );
			foreach ( $ids as $k => $id ) {
				if ( $k > 0 ) {
					$sqlids .= ',';
				}
				$sqlids .= $id;
			}
		} else {
			foreach ( $ids as $k => $id ) {
				if ( $k > 0 ) {
					$sqlids .= ',';
				}
				$sqlids .= $id['id_specify_page'];
			}
		}
		$limit = Tools::getValue( 'limit' ) ? pSQL( Tools::getValue( 'limit' ) ) : 60;

		$sql = 'SELECT c.`id_category`, cl.`name`
                FROM `' . _DB_PREFIX_ . 'category` c
                ' . Shop::addSqlAssociation( 'category', 'c' ) . '
                LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON (c.`id_category` = cl.`id_category` ' . Shop::addSqlRestrictionOnLang( 'cl' ) . ')
                WHERE cl.`id_lang` = ' . (int) $id_lang . '
                ' . ( $front ? ' AND c.`active` = 1' : '' ) .
		' AND c.`id_category` IN(' . $sqlids . ')' .
		'ORDER BY cl.`name` LIMIT ' . $limit;

		$rs   = Db::getInstance( _PS_USE_SQL_SLAVE_ )->executeS( $sql );
		$rslt = array();
		foreach ( $rs as $i => $r ) {
			$rslt[ $i ]['id_category'] = $r['id_category'];
			$rslt[ $i ]['name']        = $r['name'];
			$i++;
		}

		return $rslt;
	}

}