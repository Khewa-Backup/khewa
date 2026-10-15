<?php
/**
 * Google Merchant Center Pro
 *
 * @author    businesstech.fr <modules@businesstech.fr> - https://www.businesstech.fr/
 * @copyright Business Tech - https://www.businesstech.fr/
 * @license   see file: LICENSE.txt
 *
 *           ____    _______
 *          |  _ \  |__   __|
 *          | |_) |    | |
 *          |  _ <     | |
 *          | |_) |    | |
 *          |____/     |_|
 */

require_once(dirname(__FILE__) . '/common.conf.php');

/* defines hook library path */
define('_GMCP_PATH_LIB_HOOK', _GMCP_PATH_LIB . 'hook/');

/* defines front tpl path */
define('_GMCP_TPL_FRONT_PATH', 'front/');

/* defines hook empty tpl path */
define('_GMCP_TPL_EMPTY', 'empty.tpl');

/* defines variable for setting all request params */
$GLOBALS['GMCP_REQUEST_PARAMS'] = array();
