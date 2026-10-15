<?php
/**
 * FMM Helpdesk Module
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @author    FMM Modules
 * @copyright FMM Modules
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @category  FMM Modules
 * @package   FmmHelpdesk
*/

include('../../../config/config.inc.php');
/* Getting cookie or logout */
require_once('../../../init.php');

$context = Context::getContext();
    
$aColumns = array('id_product', 'id_image', 'name', 'price', 'categories', 'status' );
$filterColumns = array( 'p.id_product', 'pl.name', 'p.price');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "id_product";

/* DB table to use */
$sTable = "ps_product";

/* 
 * Local functions
 */
function fatal_error ( $sErrorMessage = '' )
{
    header( $_SERVER['SERVER_PROTOCOL'] .' 500 Internal Server Error' );
    die( $sErrorMessage );
}

$db = Db::getInstance();

/* 
 * Paging
 */
$sLimit = "";
if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' ) {
    $sLimit = "LIMIT ".intval( $_GET['iDisplayStart'] ).", ".
        intval( $_GET['iDisplayLength'] );
}


/*
 * Ordering
 */
$sOrder = "";
if ( isset( $_GET['iSortCol_0'] ) ) {
    $sOrder = "ORDER BY  ";
    for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )  {
        if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" ) {
            $sOrder .= $aColumns[ intval( $_GET['iSortCol_'.$i] ) ]." ".
                ($_GET['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";
        }
    }
    
    $sOrder = substr_replace( $sOrder, "", -2 );
    if ( $sOrder == "ORDER BY" ) {
        $sOrder = "";
    }
}

/* 
 * Filtering
 * NOTE this does not match the built-in DataTables filtering which does it
 * word by word on any field. It's possible to do here, but concerned about efficiency
 * on very large tables, and MySQL's regex functionality is very limited
 */
$sWhere = "";
if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" ) {
    $sWhere = "AND (";
    for ( $i=0 ; $i<count($filterColumns) ; $i++ ) {
        if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" ) {
            $sWhere .= $filterColumns[$i]." LIKE '%".mysql_real_escape_string( $_GET['sSearch'] )."%' OR ";
        }
    }
    $sWhere = substr_replace( $sWhere, "", -3 );
    $sWhere .= ')';
}

/* Individual column filtering */
for ( $i=0 ; $i<count($filterColumns) ; $i++ ) {
    if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' ) {
        if ( $sWhere == "" )
        {
            $sWhere = " AND ";
        } else {
            $sWhere .= " AND ";
        }
        $sWhere .= "".$filterColumns[$i]." LIKE '%".mysql_real_escape_string($_GET['sSearch_'.$i])."%' ";
    }
}

/*
 * SQL queries
 * Get data to display
 */
$sQuery = "SELECT SQL_CALC_FOUND_ROWS image.id_image,pl.id_lang,p.id_product, IF(p.active=1,'Yes','No') as status, pl.name , GROUP_CONCAT(DISTINCT(cl.name) SEPARATOR ',') as categories, p.price, p.id_tax_rules_group, p.wholesale_price, p.reference, p.supplier_reference, p.id_supplier, p.id_manufacturer, p.upc, p.ecotax, p.weight, p.quantity, pl.description_short,     pl.description, pl.meta_title, pl.meta_keywords, pl.meta_description, pl.link_rewrite, pl.available_now, pl.available_later, p.available_for_order, p.date_add, p.show_price, p.online_only, p.condition, p.id_shop_default
    FROM $sTable p
    LEFT JOIN ps_product_lang pl ON (p.id_product = pl.id_product)
    LEFT JOIN ps_image image ON (p.id_product = image.id_product)
    LEFT JOIN ps_category_product cp ON (p.id_product = cp.id_product)
    LEFT JOIN ps_category_lang cl ON (cp.id_category = cl.id_category)
    LEFT JOIN ps_category c ON (cp.id_category = c.id_category)
    LEFT JOIN ps_product_tag pt ON (p.id_product = pt.id_product)
    WHERE pl.id_lang = 1
    AND cl.id_lang = 1
    AND p.id_shop_default = 1
    AND c.id_shop_default = 1
    $sWhere
    GROUP BY p.id_product
    $sOrder
    $sLimit
    ";
    
$rResult = Db::getInstance()->executeS($sQuery);

/* Data set length after filtering */
$sQuery = "SELECT FOUND_ROWS() as ttl";
$rResultFilterTotal = Db::getInstance()->executeS($sQuery);
if (false === $rResultFilterTotal) {
    echo mysql_error();
}
$iFilteredTotal = $rResultFilterTotal[0]['ttl'];

/* Total data set length */
$sQuery = "SELECT COUNT(`".$sIndexColumn."`) as total FROM  $sTable";
$rResultTotal = Db::getInstance()->executeS($sQuery);
$iTotal = $rResultTotal[0]['total'];

/*
 * Output
*/
$output = array(
    "sEcho" => intval($_GET['sEcho']),
    "iTotalRecords" => $iTotal,
    "iTotalDisplayRecords" => $iFilteredTotal,
    "aaData" => array()
);

function getImageLink2($id_image, $type = null)
{
  $uri_path = _THEME_PROD_DIR_.Image::getImgFolderStatic($id_image).$id_image.($type ? '-'.$type : '').'.jpg';

  return $this->protocol_content.Tools::getMediaServer($uri_path).$uri_path;
}

foreach ($rResult AS $aRow) {
    $row = array();
    // Add the row ID and class to the object
    $row['DT_RowId'] = 'row_'.$aRow['id_product'];
    $row['DT_RowClass'] = 'grade'.$aRow['status'];
    $row['DT_CheckVal'] = 'pids_'.$aRow['id_product'];
    $last = $aRow['id_image'][strlen($aRow['id_image'])-1]; 
    
    $id_image = Product::getCover($aRow['id_product']);
    // get Image by id
    if (sizeof($id_image) > 0) {
        $image = new Image($id_image['id_image']);
        // get image full URL
        $image_url = _PS_BASE_URL_._THEME_PROD_DIR_.$image->getExistingImgPath()."-small_default.jpg";
    }
    $row['DT_IMGPath'] = $image_url; 
    
    for ( $i=0 ; $i<count($aColumns) ; $i++ ) {
        if ( $aColumns[$i] == "version" ) {
            /* Special output formatting for 'version' column */
            $row[] = ($aRow[ $aColumns[$i] ]=="0") ? '-' : $aRow[ $aColumns[$i] ];
        } else if ( $aColumns[$i] != ' ' ) {
            /* General output */
            $row[] = $aRow[ $aColumns[$i] ];
        }
    }
    $output['aaData'][] = $row;
}

echo json_encode( $output );
