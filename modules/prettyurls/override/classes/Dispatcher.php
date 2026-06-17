<?php
/**
 * FMM PrettyURLs
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @author    FMM Modules
 * @copyright Copyright 2021 © FMM Modules All right reserved
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @category  FMM Modules
 * @package   PrettyURLs
*/

class Dispatcher extends DispatcherCore
{
	protected function __construct()
	{
		parent::__construct();
		$this->loadRoutes();
	}

	public $default_routes = array(
		'category_rule' => array(
			'controller' =>	'category',
			'rule' =>		'{rewrite}',
			'keywords' => array(
				'id' =>				array('regexp' => '[0-9]+'),
				'rewrite' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'category_rewrite'),
				'meta_keywords' =>	array('regexp' => '[_a-zA-Z0-9-\pL]*'),
				'meta_title' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                'categories' =>		array('regexp' => '[/_a-zA-Z0-9-\pL]*'),
			),
		),
		'supplier_rule' => array(
			'controller' =>	'supplier',
			'rule' =>		'supplier/{rewrite}',
			'keywords' => array(
				'id' =>				array('regexp' => '[0-9]+'),
				'rewrite' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'supplier_rewrite'),
				'meta_keywords' =>	array('regexp' => '[_a-zA-Z0-9-\pL]*'),
				'meta_title' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*'),
			),
		),
		'manufacturer_rule' => array(
			'controller' =>	'manufacturer',
			'rule' =>		'manufacturer/{rewrite}',
			'keywords' => array(
				'id' =>				array('regexp' => '[0-9]+'),
				'rewrite' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'manufacturer_rewrite'),
				'meta_keywords' =>	array('regexp' => '[_a-zA-Z0-9-\pL]*'),
				'meta_title' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*'),
			),
		),
		'cms_rule' => array(
			'controller' =>	'cms',
			'rule' =>		'content/{rewrite}',
			'keywords' => array(
				'id' =>				array('regexp' => '[0-9]+'),
				'rewrite' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'cms_rewrite'),
				'meta_keywords' =>	array('regexp' => '[_a-zA-Z0-9-\pL]*'),
				'meta_title' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*'),
			),
		),
		'cms_category_rule' => array(
			'controller' =>	'cms',
			'rule' =>		'content/category/{rewrite}',
			'keywords' => array(
				'id' =>				array('regexp' => '[0-9]+'),
				'rewrite' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'cms_category_rewrite'),
				'meta_keywords' =>	array('regexp' => '[_a-zA-Z0-9-\pL]*'),
				'meta_title' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*'),
			),
		),
		'module' => array(
			'controller' =>	null,
			'rule' =>		'module/{module}{/:controller}',
			'keywords' => array(
				'module' =>			array('regexp' => '[_a-zA-Z0-9_-]+', 'param' => 'module'),
				'controller' =>		array('regexp' => '[_a-zA-Z0-9_-]+', 'param' => 'controller'),
			),
			'params' => array(

				'fc' => 'module',
			),
		),
		'product_rule' => array(
			'controller' =>	'product',
			'rule' =>		'{categories:/}{rewrite}',
			'keywords' => array(
				'id' =>				array('regexp' => '[0-9]+'),
				'rewrite' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'product_rewrite'),
				'id_product_attribute' => array('regexp' => '[0-9]+'),
				'ean13' =>			array('regexp' => '[0-9\pL]*'),
				'category' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*'),
				'categories' =>		array('regexp' => '[/_a-zA-Z0-9-\pL]*'),
				'reference' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*'),
				'meta_keywords' =>	array('regexp' => '[_a-zA-Z0-9-\pL]*'),
				'meta_title' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*'),
				'manufacturer' =>	array('regexp' => '[_a-zA-Z0-9-\pL]*'),
				'supplier' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*'),
				'price' =>			array('regexp' => '[0-9\.,]*'),
				'tags' =>			array('regexp' => '[a-zA-Z0-9-\pL]*'),
			),
		),
		/*	Must be after the product and category rules in order to avoid conflict	*/
		'layered_rule' => array(
			'controller' =>	'category',
			'rule' =>		'{rewrite}/filter{selected_filters}',
			'keywords' => array(
				'id' =>				array('regexp' => '[0-9]+'),
				/*	Selected filters is used by the module blocklayered	*/
				'selected_filters' =>		array('regexp' => '.*', 'param' => 'selected_filters'),
				'rewrite' => array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'category_rewrite'),
				'meta_keywords' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
				'meta_title' =>	array('regexp' => '[_a-zA-Z0-9-\pL]*'),
			),
		),
	);

	protected function loadRoutes($id_shop = null)
	{
		/* Old Category URL Checking */
		$cat_pattern = '/.*?\/([0-9]+)\-([_a-zA-Z0-9-\pL]*)/';
		preg_match($cat_pattern, $_SERVER['REQUEST_URI'], $url_array);
		if (!empty($url_array)) {
			if (!strstr($_SERVER['REQUEST_URI'], '/content/')) {
				$this->default_routes['category_rule']['rule'] = '{rewrite}';
			}
		}
		/* Old Product URL Checking */
		$prod_pattern = '/.*?\/([0-9]+)\-([_a-zA-Z0-9-\pL]*)/';
		preg_match($prod_pattern, $_SERVER['REQUEST_URI'], $pro_array);
		if (!empty($pro_array)) {
			$this->default_routes['product_rule']['rule'] = '{categories:/}{rewrite}';
		}

		/* Old Supplier URL Checking */
		$sup_pattern = '/.*?([0-9]+)\_\_([_a-zA-Z0-9-\pL]*)/';
		preg_match($sup_pattern, $_SERVER['REQUEST_URI'], $sup_array);

		if (!empty($sup_array)) {
			$this->default_routes['supplier_rule']['rule'] = '{rewrite}';
		}

		/* Old Manufacturer URL Checking */
		$man_pattern = '/.*?([0-9]+)\_([_a-zA-Z0-9-\pL]*)/';
		preg_match($man_pattern, $_SERVER['REQUEST_URI'], $man_array);
		if (!empty($man_array)) {
			$this->default_routes['manufacturer_rule']['rule'] = '{rewrite}';
		}

		/* Old CMS Page URL Checking */
		$cms_pattern = '/.*?content\/([0-9]+)\-([_a-zA-Z0-9-\pL]*)/';
		preg_match($cms_pattern, $_SERVER['REQUEST_URI'], $cms_array);
		if (!empty($cms_array)) {
			$this->default_routes['cms_rule']['rule'] = 'content/{rewrite}';
		}
		/* Old CMS Category URL Checking */
		$cms_cat_pattern = '/.*?content\/category\/([0-9]+)\-([_a-zA-Z0-9-\pL]*)/';
		preg_match($cms_cat_pattern, $_SERVER['REQUEST_URI'], $cms_cat_array);
		if (!empty($cms_cat_array)) {
			if (strstr($_SERVER['REQUEST_URI'], '/content/category/'))
				$this->default_routes['cms_category_rule']['rule'] = 'content/category/{rewrite}';
		}
		// Load custom routes from modules
        $modules_routes = Hook::exec('moduleRoutes', array('id_shop' => $id_shop), null, true, false);
        if (is_array($modules_routes) && count($modules_routes)) {
            foreach ($modules_routes as $module_route) {
                if (is_array($module_route) && count($module_route)) {
                    foreach ($module_route as $route => $route_details) {
                        if (array_key_exists('controller', $route_details) && array_key_exists('rule', $route_details)
                            && array_key_exists('keywords', $route_details) && array_key_exists('params', $route_details)) {
                            if (!isset($this->default_routes[$route])) {
                                $this->default_routes[$route] = array();
                            }
                            $this->default_routes[$route] = array_merge($this->default_routes[$route], $route_details);
                        }
                    }
                }
            }
        }
		/* Set default routes */
		foreach (Language::getLanguages() as $lang)
			foreach ($this->default_routes as $id => $route)
				$this->addRoute(
					$id,
					$route['rule'],
					$route['controller'],
					$lang['id_lang'],
					$route['keywords'],
					isset($route['params']) ? $route['params'] : array(),
					$id_shop
				);
		/* Load the custom routes prior the defaults to avoid infinite loops */
		if ($this->use_routes)
		{
			/* Load routes from meta table */
			$sql = 'SELECT m.page, ml.url_rewrite, ml.id_lang
					FROM `'._DB_PREFIX_.'meta` m
					LEFT JOIN `'._DB_PREFIX_.'meta_lang` ml ON (m.id_meta = ml.id_meta'.Shop::addSqlRestrictionOnLang('ml', (int)$id_shop).')
					ORDER BY LENGTH(ml.url_rewrite) DESC';
			if ($results = Db::getInstance()->executeS($sql))
				foreach ($results as $row)
				{
					if ($row['url_rewrite'])
						$this->addRoute($row['page'], $row['url_rewrite'], $row['page'], $row['id_lang'], array(), array(), $id_shop);
				}
			/* Set default empty route if  no empty route (that's weird I know) */
			if (!$this->empty_route)
				$this->empty_route = array(
					'routeID' =>	'index',
					'rule' =>		'',
					'controller' =>	'index',
				);
			/* Load custom routes */

			foreach ($this->default_routes as $route_id => $route_data)
				if ($custom_route = Configuration::get('PS_ROUTE_'.$route_id, null, null, $id_shop))
					foreach (Language::getLanguages() as $lang)
						$this->addRoute(
							$route_id,
							$custom_route,
							$route_data['controller'],
							$lang['id_lang'],
							$route_data['keywords'],
							isset($route_data['params']) ? $route_data['params'] : array(),
							$id_shop
						);
		}
	}

	public function getController($id_shop = null)
	{
		if (defined('_PS_ADMIN_DIR_')) {
			$_GET['controllerUri'] = Tools::getvalue('controller');
		}
		if ($this->controller) {
			$_GET['controller'] = $this->controller;
			return $this->controller;
		}
		//~~~~~~Check for PS-1.7.x.x~~~~~~~
		$ps_17 = (Tools::version_compare(_PS_VERSION_, '1.7.0.0', '>=') == true) ? 1 : 0;
		if (isset(Context::getContext()->shop) && $id_shop === null) {
			$id_shop = (int)Context::getContext()->shop->id;
		}
		$controller = Tools::getValue('controller');

		if (isset($controller) && is_string($controller) && preg_match('/^([0-9a-z_-]+)\?(.*)=(.*)$/Ui', $controller, $m)) {
			$controller = $m[1];
			if (isset($_GET['controller'])) {
				$_GET[$m[2]] = $m[3];
			}
			else if (isset($_POST['controller'])) {
				$_POST[$m[2]] = $m[3];
			}
		}

		if (!Validate::isControllerName($controller)) {
			$controller = false;
		}
		// Use routes ? (for url rewriting)
		if ($this->use_routes && !$controller && !defined('_PS_ADMIN_DIR_')) {
			if (!$this->request_uri) {
				return Tools::strtolower($this->controller_not_found);
			}
			$controller = $this->controller_not_found;
			$test_request_uri = preg_replace('/(=http:\/\/)/', '=', $this->request_uri);
			// If the request_uri matches a static file, then there is no need to check the routes, we keep "controller_not_found" (a static file should not go through the dispatcher)
            if (!preg_match('/\.(gif|jpe?g|png|css|js|ico)$/i', parse_url($test_request_uri, PHP_URL_PATH))) {
                // Add empty route as last route to prevent this greedy regexp to match request uri before right time
                if ($this->empty_route) {
                    $this->addRoute($this->empty_route['routeID'], $this->empty_route['rule'], $this->empty_route['controller'], Context::getContext()->language->id, array(), array(), $id_shop);
                }

                list($uri) = explode('?', $this->request_uri);

                if (isset($this->routes[$id_shop][Context::getContext()->language->id])) {
                    foreach ($this->routes[$id_shop][Context::getContext()->language->id] as $route) {
                        if (preg_match($route['regexp'], $uri, $m)) {
                            // Route found ! Now fill $_GET with parameters of uri
                            foreach ($m as $k => $v) {
                                if (!is_numeric($k)) {
                                    $_GET[$k] = $v;
                                }
                            }
                            $controller = $route['controller'] ? $route['controller'] : $_GET['controller'];
                            if (!empty($route['params'])) {
                                foreach ($route['params'] as $k => $v) {
                                    $_GET[$k] = $v;
                                }
                            }
                            // A patch for module friendly urls
                            if (preg_match('#module-([a-z0-9_-]+)-([a-z0-9_]+)$#i', $controller, $m)) {
                                $_GET['module'] = $m[1];
                                $_GET['fc'] = 'module';
                                $controller = $m[2];
                            }
                            if (isset($_GET['fc']) && $_GET['fc'] == 'module') {
                                $this->front_controller = self::FC_MODULE;
                            }
                            break;
                        }
                    }
                }
            }
			$req_uri = explode('/', $this->request_uri);
			if (preg_match('/\?/', $req_uri[1])) {
				$req_uri_qmark = explode('?', $req_uri[1]);
				$req_uri[1] = $req_uri_qmark[0];
			}
			if ($controller == 'index' || preg_match('/^\/index.php(?:\?.*)?$/', $this->request_uri) || $req_uri[1] == '') {
				$controller = (_PS_VERSION_ >= '1.6.0' || _PS_VERSION_ >= '1.6.0.0') ? $this->useDefaultController() : $this->default_controller;
			}
			$check_url_type_existance = (int)$this->getKeyExistance($req_uri[1]);
			$get_controller_page = $this->getControllerPageById($check_url_type_existance);
			if ($check_url_type_existance > 0) {
					$controller = $get_controller_page;
			}
		}
		//~~~~~~~~~~Fixes for FRONT only~~~~~~~~~~ - Make sure its FO
		if (!defined('_PS_ADMIN_DIR_')) {
			//FIX for ending slash
			$ender = Tools::substr($this->request_uri, -1);
            if ($ender == '/') {
				$this->request_uri = rtrim($this->request_uri, '/');
            }
			//Finally Check for 404 page
			if ($controller == '404' || $controller == 404 || $controller == 'page-not-found' || $controller == 'pagenotfound' || (isset($_GET['controller']) && $_GET['controller'] == 'pagenotfound')) {
				$controller = 'pagenotfound';
				//Another check added in Link.php |getPageLink| for correct 404 page
			}
			//Fix for Category/Products with parameters
			if (preg_match('/\?/', $this->request_uri) && !preg_match('/module/', $this->request_uri)) {
				$req_uri_qmark = explode('?', $this->request_uri);
				if (Tools::substr($req_uri_qmark[0], -1) == '/') {
					$req_uri_qmark[0] = Tools::substr($req_uri_qmark[0], 0, -1);
				}
				$cat_or_product = explode('/', $req_uri_qmark[0]);
				$request = end($cat_or_product);
				if (preg_match('/html/', $request)) {
					$request = explode('.', $request);
					$request = $request[0];
				}
	
				$callback = (int)$this->getProductExistance($request);
				if ($callback > 0) {
					$controller = 'product';
					$_POST['id_product'] = $callback;
				}
			}
			elseif (!preg_match('/module/', $this->request_uri) && $controller == 'category') {
				$cat_uri_exist_case = explode('/', $this->request_uri);
				$cat_uri_exist_case = array_filter($cat_uri_exist_case);
				$cat_uri_exist_case = end($cat_uri_exist_case);
				$cat_uri_exist = (int)$this->getCategoryId($cat_uri_exist_case);
				if ($cat_uri_exist <= 0) {
					$callback = (int)$this->getProductExistance($cat_uri_exist_case);
					if ($callback > 0) {
						$controller = 'product';
						$_POST['id_product'] = $callback;
					}
				}
			}
			if ($controller == 'pagenotfound') {
				$req_uri = explode('/', $this->request_uri);
				$request = end($req_uri);
				$req_uri_qmark = explode('?', $request);
				$clearify_request = str_replace('-', ' ', $req_uri_qmark[0]);
				$manu_existance = (int)$this->getKeyExistanceManuf($clearify_request);
				if ($manu_existance > 0) {
					$controller = 'manufacturer';
					$_POST['id_manufacturer'] = $manu_existance;
				}
			}
			if ($controller == 'pagenotfound' && preg_match('/content_only/', $this->request_uri)) {
				$explode_url_params = explode('/', $this->request_uri);
				$explode_url_params = end($explode_url_params);
				$explode_url = explode('?', $explode_url_params);
				$check_for_cms_404 = (int)$this->getKeyExistanceCMS($explode_url[0]);
				if ($check_for_cms_404 > 0) {
					$controller = 'cms';
					$_POST['id_cms'] = $check_for_cms_404;
				}
			}
			if ($controller == 'pagenotfound') {
				$check_url_type_existance_cms = (int)$this->getKeyExistanceCMS($req_uri[1]);
			} else {
				$check_url_type_existance_cms = 0;
			}
			if ($check_url_type_existance_cms > 0 && !preg_match('/^\/blog.html*/', $this->request_uri) && !preg_match('/^\/blog*/', $this->request_uri)) {
				$controller = 'cms';
				$_POST['id_cms'] = $check_url_type_existance_cms;
			}
			if ($controller == 'pagenotfound' || $controller == 'category') {
				$request_uri_match = explode('/', $this->request_uri);
				$request_uri_match = array_filter($request_uri_match);
				$request_uri_match = end($request_uri_match);
				$check_url_type_existance = (int)$this->getKeyExistance($request_uri_match);
				$get_controller_page = $this->getControllerPageById($check_url_type_existance);
				if ($check_url_type_existance > 0) {
					$controller = $get_controller_page;
				}
			}
			if (!preg_match('/\?/', $this->request_uri) && preg_match('/.*?\/([0-9]+)\-([_a-zA-Z0-9-\pL]*)/', $this->request_uri)
			&& $controller == 'pagenotfound') {
				$product_request = explode('/', $this->request_uri);
				$product_request = end($product_request);
				$product_uri = explode('/', $this->request_uri);
				if (!preg_match('/module/', $this->request_uri) && !preg_match('/blog/', $this->request_uri)) {
					$controller = 'product';
					$_POST['id_product'] = $product_uri[0];
				}
			}
			if ($controller == 'pagenotfound' && preg_match('/\?/', $this->request_uri)) {
				$cat_id_req_uri = explode('/', $this->request_uri);
				$cat_id_req_uri = array_filter($cat_id_req_uri);
				$cat_id_req_uri = end($cat_id_req_uri);
				$cat_id_req_uri = explode('?', $cat_id_req_uri);
				$cat_id_req_uri = $cat_id_req_uri[0];
				$cat_id_req_uri = explode('.', $cat_id_req_uri);
				$cat_id_req_uri = $cat_id_req_uri[0];
				$get_cat_page_id = (int)$this->getCategoryId($cat_id_req_uri);
				if (!preg_match('/module/', $this->request_uri)) {
					if ($get_cat_page_id > 0) {
						$_POST['id_category'] = $get_cat_page_id;
						$controller = 'category';
					}
				}
			}
			elseif (($controller == 'product' || $controller == 'category') && !preg_match('/\?/', $this->request_uri)) {
				$get_id = (int)Tools::getValue('id_category');
				$get_id = ($get_id <= 0) ? (int)Tools::getValue('id_product') : $get_id;
				if ($get_id < 1) {
					$simple_uri = explode('/', $this->request_uri);
					$simple_uri = end($simple_uri);
					$get_id = (int)$this->getCategoryId($simple_uri);
					if ($get_id > 0) {
						$_POST['id_category'] = $get_id;
						$controller = 'category';
					}
					elseif ($get_id < 1) {
						$get_id = (int)$this->getProductExistance($simple_uri);
						if ($get_id > 0) {
							$_POST['id_product'] = $get_id;
							$controller = 'product';
						}
					}
				}
			}
			//FIX for Advance CMS module
			if (preg_match('/cms/', $this->request_uri) && $controller != 'cms') {
				//Lets make sure its a module
				$module = (int)$this->getModule('advancedcms');
				if ($module > 0) {
					$this->front_controller = self::FC_MODULE;
					$_GET['module'] = 'advancedcms';
					if (!isset($_GET['id_cms'])) {
						$_GET['rewrite'] = $_GET['product_rewrite'];
						$controller = 'adcms';
						$_GET['fc'] = 'module';
						$_id = (int)$this->getIdAdvanceCms($_GET['rewrite']);
						$_GET['id_cms'] = $_id;
						unset($_GET['product_rewrite']);
					}
				}
			}
			//FIX for Form Maker module
			if (preg_match('/forms/', $this->request_uri) && !preg_match('/module/', $this->request_uri)) {
				//Lets make sure its a module
				$module = (int)$this->getModule('formmaker');
				if ($module > 0) {
					$this->front_controller = self::FC_MODULE;
					$_GET['module'] = 'formmaker';
					if (!isset($_GET['id_form'])) {
						if (preg_match('/\?/', $this->request_uri)) {
							$req_uri = explode('?', $this->request_uri);
							$_new_uri = $req_uri[0];
							$_new_uri = explode('/', $_new_uri);
							$_new_uri = end($_new_uri);
							$_GET['rewrite'] = $_new_uri;
						}
						else {
							$_GET['rewrite'] = $_GET['product_rewrite'];
						}
						$controller = 'form';
						$_GET['fc'] = 'module';
						$_id = (int)$this->getIdFormsMod($_GET['rewrite']);
						$_GET['id_form'] = $_id;
						unset($_GET['product_rewrite']);
					}
				}
			}
			//FIX for Form Maker module
			if (preg_match('/formmaker/', $this->request_uri) && preg_match('/module/', $this->request_uri)) {
				$this->front_controller = self::FC_MODULE;
				$_GET['module'] = 'formmaker';
				$controller = 'formsuccess';
			}
			//FIX for Gallerique Image Gallery module
			if (preg_match('/gallery/', $this->request_uri)) {
				//Lets make sure its a module
				$module = (int)$this->getModule('gallerique');
				if ($module > 0) {
					$this->front_controller = self::FC_MODULE;
					$_GET['module'] = 'gallerique';
					if (!isset($_GET['id_gallery'])) {
						$_GET['link_rewrite'] = $_GET['product_rewrite'];
						$controller = 'gallery';
						$_GET['fc'] = 'module';
						$_id = (int)$this->getIdGallery($_GET['link_rewrite']);
						$_GET['id_gallery'] = $_id;
						unset($_GET['product_rewrite']);
					}
				}
			}
			//FIX for Gallerique Image Gallery module
			if (preg_match('/galleries/', $this->request_uri)) {
				//Lets make sure its a module
				$module = (int)$this->getModule('gallerique');
				if ($module > 0) {
					$this->front_controller = self::FC_MODULE;
					$_GET['module'] = 'gallerique';
					if (!isset($_GET['id_gallery'])) {
						$_GET['link_rewrite'] = $_GET['product_rewrite'];
						$controller = 'gallerylist';
						$_GET['fc'] = 'module';
						unset($_GET['product_rewrite']);
					}
				}
			}
			//ONLY for PS-1.7
			if ((int)$ps_17 > 0) {
				//Fix for PS-1.7 attributes change
				if (isset($_POST['action']) && isset($_POST['ajax']) && isset($_GET['quantity_wanted'])) {
					$id_unique_ipa = (int)Context::getContext()->cookie->__get('id_unique_ipa');
					if ($id_unique_ipa > 0) {
						$_POST['id_product_attribute'] = $id_unique_ipa;
					}
				}
				elseif (isset($_POST['action']) && isset($_POST['ajax']) && isset($_POST['quantity_wanted'])) {
					$id_unique_ipa = (int)Context::getContext()->cookie->__get('id_unique_ipa');
					if ($id_unique_ipa > 0) {
						$_POST['id_product_attribute'] = $id_unique_ipa;
					}
				}
				//FIX for PS-1.7 Layered Nav filters
				if (($controller == 'category' || $controller == 'pagenotfound') && isset($_GET['q']) && isset($_GET['from-xhr'])) {
					$cat_id_req_uri = explode('?', $this->request_uri);
					$_cleanup_uri = str_replace('/', '', $cat_id_req_uri[0]);
					$_page_id = (int)$this->getCategoryId($_cleanup_uri);
					if ($_page_id > 0) {
						$_GET['id_category'] = $_page_id;
						$controller = 'category';
					}
				}
			}
			$test_request_uri = preg_replace('/(=http:\/\/)/', '=', $this->request_uri);
			if (!preg_match('/\.(gif|jpe?g|png|css|js|ico)$/i', parse_url($test_request_uri, PHP_URL_PATH))) {
				//Fix module routes dynamically-----------------------------
				if (preg_match('/module/', $this->request_uri) && $controller == 'pagenotfound' && !isset($_GET['fc']) && preg_match('/\?/', $this->request_uri)) {
					$_disperse_uri = explode('?', $this->request_uri);
					$_disperse_uri = $_disperse_uri[0];
					$three_parts = array_values(array_filter(explode('/', $_disperse_uri)));
					//THE BIG THREE PARTS --------
					$_GET['fc'] = $three_parts[0];
					$_GET['module'] = $three_parts[1];
					$_GET['controller'] = $three_parts[2];
					$controller = $three_parts[2];
					$this->front_controller = self::FC_MODULE;
				}
				elseif (preg_match('/module/', $this->request_uri) && preg_match('/\?/', $this->request_uri) && isset($_GET['module']) && !isset($_GET['fc']) && !isset($_GET['redirect'])) {
					$this->front_controller = self::FC_MODULE;
					$_GET['fc'] = 'module';
					$_disperse_uri = explode('?', $this->request_uri);
					$_disperse_uri = $_disperse_uri[0];
					$three_parts = array_values(array_filter(explode('/', $_disperse_uri)));
					//Just make sure its correct module.
					$_GET['module'] = $three_parts[1];
				}
			}
			//FIX Friendly Module Routes
			if (preg_match('/module/', $controller)) {
					if (isset($_GET['category_rewrite']) && !empty($_GET['category_rewrite'])) {
						$_mod_uri = $_GET['category_rewrite'];
					}
					else {
						$disperseuri = explode('?', $this->request_uri);
						$disperseuri = $disperseuri[0];
						$_mod_uri = end(array_values(array_filter(explode('/', $disperseuri))));
					}
					$modules_routes = $this->getFriendlyModRoute($_mod_uri);
					if ($modules_routes) {
						$modules_routes = explode('-', $modules_routes);
						$_GET['fc'] = $modules_routes[0];
						$_GET['module'] = $modules_routes[1];
						$_GET['controller'] = $modules_routes[2];
						$controller = $modules_routes[2];
						$this->front_controller = self::FC_MODULE;
					}
			}
			//Universal All Blogs fix - BETA
			if (preg_match('/blog/', $this->request_uri)) {
				$modules_routes = Hook::exec('moduleRoutes', array('id_shop' => $id_shop), null, true, false);
				//Check if Blog is FMM's Advance Blog
				$module_exists = Module::isEnabled('advanceblog');
				//Check for PrestBlog
				$module_exists_prestablog = Module::isEnabled('prestablog');
				//Check for PH Simple Blog
				$module_exists_ph_simpleblog = Module::isEnabled('ph_simpleblog');
				//Check for ST Blog
				$module_exists_stblog = Module::isEnabled('stblog');
                //Check for PS Blog
                $module_exists_psblog = Module::isEnabled('psblog');
				$this->request_uri = ltrim($this->request_uri, '/');
				if ($module_exists == true) {
					//Its a FMM Blog
					$_blogpost_rule = '/.*blog\/([0-9]+)\-([_a-zA-Z0-9-\pL]*)/';
					$_blogcategory_rule = '/.*blog\/category\/([0-9]+)\-([_a-zA-Z0-9-\pL]*)/';
					preg_match($_blogpost_rule, $this->request_uri, $_result_pattern);
					preg_match($_blogcategory_rule, $this->request_uri, $_result_pattern_cat);
					if (isset($_GET['page']) && !empty($_GET['page'])) {
						$this->request_uri = 'blog';
					}
					  if (is_array($modules_routes) && count($modules_routes)) {
						foreach ($modules_routes as $module_route) {
							if (is_array($module_route) && count($module_route)) {
								foreach ($module_route as $route => $route_details) {
									if ($route_details['rule'] == $this->request_uri) {
										//its a blog landing page
										$_GET['module'] = $route_details['params']['module'];
										$_GET['fc'] = $route_details['params']['fc'];
										$controller = $route_details['controller'];
										$this->front_controller = self::FC_MODULE;
										unset($_GET['product_rewrite']);
										unset($_GET['category_rewrite']);
									}
									elseif (isset($_result_pattern[1]) && (int)$_result_pattern[1] > 0) {
										//Its a blog detail page
										if ($route_details['rule'] == 'blog{/:id}-{post}') {
											$_GET['module'] = $route_details['params']['module'];
											$_GET['fc'] = $route_details['params']['fc'];
											$controller = $route_details['controller'];
											$this->front_controller = self::FC_MODULE;
											$_GET['id'] = (int)$_result_pattern[1];
											unset($_GET['product_rewrite']);
											unset($_GET['category_rewrite']);
										}
									}
									elseif (isset($_result_pattern_cat[1]) && (int)$_result_pattern_cat[1] > 0) {
										//Its a blog category page
										if ($route_details['rule'] == 'blog/category{/:id}-{cat}') {
											$_GET['module'] = $route_details['params']['module'];
											$_GET['fc'] = $route_details['params']['fc'];
											$controller = $route_details['controller'];
											$this->front_controller = self::FC_MODULE;
											$_GET['id'] = (int)$_result_pattern_cat[1];
											unset($_GET['product_rewrite']);
											unset($_GET['category_rewrite']);
										}
									}
									elseif (isset($_GET['arc']) && $route_details['rule'] == 'blog') {
										//its an archive page
										$_GET['module'] = $route_details['params']['module'];
										$_GET['fc'] = $route_details['params']['fc'];
										$controller = $route_details['controller'];
										$this->front_controller = self::FC_MODULE;
										unset($_GET['product_rewrite']);
										unset($_GET['category_rewrite']);
									}
									elseif (isset($_GET['blog_rss']) && $route_details['rule'] == 'rss') {
										//its an archive page
										$_GET['module'] = $route_details['params']['module'];
										$_GET['fc'] = $route_details['params']['fc'];
										$controller = $route_details['controller'];
										$this->front_controller = self::FC_MODULE;
										unset($_GET['product_rewrite']);
										unset($_GET['category_rewrite']);
									}
								}
							}
						}
					}
				}
				elseif ($module_exists_prestablog == true) {
					$blogpost_rule = '/.*blog\/([_a-zA-Z0-9-\pL]+)\-n([0-9]*)/';
					$blogpost_rule_pag = '/.*blog\/([0-9]+)p([0-9]*)/';
					$blogcategory_rule = '/.*blog\/([_a-zA-Z0-9-\pL]+)\-c([0-9]*)/';
					$blogcategory_pag_rule = '/.*blog\/([_a-zA-Z0-9-\pL]+)\-([0-9]+)p([0-9]+)-c([0-9]*)/';
					preg_match($blogpost_rule, $this->request_uri, $p_result_pattern);
					preg_match($blogcategory_rule, $this->request_uri, $c_result_pattern);
					preg_match($blogpost_rule_pag, $this->request_uri, $page_result_pattern);
					preg_match($blogcategory_pag_rule, $this->request_uri, $c_page_result_pattern);
					if (isset($page_result_pattern[1]) && isset($page_result_pattern[2])) {
						$filter_pagination = explode('/', $this->request_uri);
						$filter_pagination_flat = end($filter_pagination);
						if (preg_match('/p/', $filter_pagination_flat)) {
							$this->request_uri = 'blog';
							$_GET['p'] = $page_result_pattern[2];
							$_GET['start'] = $page_result_pattern[1];
						}
					}
					if (is_array($modules_routes) && count($modules_routes)) {
						foreach ($modules_routes as $module_route) {
							if (is_array($module_route) && count($module_route)) {
								foreach ($module_route as $route => $route_details) {
									if ($route_details['rule'] == '{controller}' && $this->request_uri == 'blog') {
										//its a blog landing page
										$_GET['module'] = $route_details['params']['module'];
										$_GET['fc'] = $route_details['params']['fc'];
										$controller = 'blog';//$route_details['controller'];
										$this->front_controller = self::FC_MODULE;
										unset($_GET['product_rewrite']);
										unset($_GET['category_rewrite']);
									}
									elseif (isset($p_result_pattern[2]) && (int)$p_result_pattern[2] > 0) {
										//Its a blog detail page
										if ($route_details['rule'] == '{controller}/{urlnews}-n{n}') {
											$_GET['module'] = $route_details['params']['module'];
											$_GET['fc'] = $route_details['params']['fc'];
											$controller = 'blog';
											$_GET['controller'] = $controller;
											$this->front_controller = self::FC_MODULE;
											$_GET['n'] = (int)$p_result_pattern[2];
											$_GET['urlnews'] = $p_result_pattern[1];
											$_GET['id'] = (int)$p_result_pattern[2];
											unset($_GET['product_rewrite']);
											unset($_GET['category_rewrite']);
										}
									}
									elseif ((isset($c_result_pattern[2]) && (int)$c_result_pattern[2] > 0) || (isset($c_page_result_pattern[3]) && isset($c_page_result_pattern[4]))) {
										if ($route_details['rule'] == '{controller}/{urlcat}-c{c}') {
											$_GET['module'] = $route_details['params']['module'];
											$_GET['fc'] = $route_details['params']['fc'];
											$controller = 'blog';
											$_GET['controller'] = $controller;
											$this->front_controller = self::FC_MODULE;
											$_GET['c'] = (int)$c_result_pattern[2];
											$_GET['urlcat'] = $c_result_pattern[1];
											if (isset($c_page_result_pattern[3]) && isset($c_page_result_pattern[4])) {
												$_GET['p'] = $c_page_result_pattern[3];
												$_GET['start'] = $c_page_result_pattern[2];
											}
											unset($_GET['product_rewrite']);
											unset($_GET['category_rewrite']);
										}
									}
								}
							}
						}
					}
				}
				elseif ($module_exists_ph_simpleblog == true) {
					$ph_blogpost_rule = '/.*blog\/([_a-zA-Z0-9-\pL]+)\/([_a-zA-Z0-9-\pL]*)/';
					$ph_blogcategory_rule = '/.*blog\/([_a-zA-Z0-9-\pL]*)/';
					preg_match($ph_blogpost_rule, $this->request_uri, $ph_blogpost_rule_result);
					preg_match($ph_blogcategory_rule, $this->request_uri, $ph_blogcategory_rule_result);
					if (preg_match('/page/', $this->request_uri)) {
						$ph_pagin_rule = '/.*blog\/page\/([0-9]*)/';
						preg_match($ph_pagin_rule, $this->request_uri, $ph_pagin_rule_result);
						if (isset($ph_pagin_rule_result[1])) {
							$_GET['p'] = $ph_pagin_rule_result[1];
							$this->request_uri = 'blog';
						}
					}
					if (is_array($modules_routes) && count($modules_routes)) {
						foreach ($modules_routes as $module_route) {
							if (is_array($module_route) && count($module_route)) {
								foreach ($module_route as $route => $route_details) {
									if ($route_details['rule'] == 'blog' && $this->request_uri == 'blog') {
										//its a blog landing page
										$_GET['module'] = $route_details['params']['module'];
										$_GET['fc'] = $route_details['params']['fc'];
										$controller = 'list';
										$this->front_controller = self::FC_MODULE;
										unset($_GET['product_rewrite']);
										unset($_GET['category_rewrite']);
									}
									elseif (isset($ph_blogpost_rule_result[1]) && isset($ph_blogpost_rule_result[2]) && !isset($_GET['p']) && $ph_blogpost_rule_result[2] != 'page') {
										//Its a blog detail page
										if ($route_details['rule'] == 'blog/{sb_category}/{rewrite}') {
											$_GET['module'] = $route_details['params']['module'];
											$_GET['fc'] = $route_details['params']['fc'];
											$controller = $route_details['controller'];
											$this->front_controller = self::FC_MODULE;
											$_GET['sb_category'] = $ph_blogpost_rule_result[1];
											$_GET['rewrite'] = $ph_blogpost_rule_result[2];
											unset($_GET['product_rewrite']);
											unset($_GET['category_rewrite']);
										}
									}
									elseif (isset($ph_blogcategory_rule_result[1]) && !isset($_GET['p'])) {
										//Its a blog category page
										if ($route_details['rule'] == 'blog/{sb_category}') {
											$_GET['module'] = $route_details['params']['module'];
											$_GET['fc'] = $route_details['params']['fc'];
											$controller = $route_details['controller'];
											$this->front_controller = self::FC_MODULE;
											$_GET['sb_category'] = $ph_blogcategory_rule_result[1];
											unset($_GET['product_rewrite']);
											unset($_GET['category_rewrite']);
											if (preg_match('/page/', $this->request_uri)) {
												$pagin_splitter = explode('/', $this->request_uri);
												$pagin_splitter = array_filter($pagin_splitter);
												$pagin_splitter = end($pagin_splitter);
												$_GET['p'] = (int)$pagin_splitter;
											}
										}
									}
								}
							}
						}
					}
				}
				elseif ($module_exists_stblog == true) {
					$stblogcategory_rule = '/.*blog\/([0-9]+)\-([_a-zA-Z0-9-\pL]*)/';
					preg_match($stblogcategory_rule, $this->request_uri, $result_pattern_st);
					if (is_array($modules_routes) && count($modules_routes)) {
						foreach ($modules_routes as $module_route) {
							if (is_array($module_route) && count($module_route)) {
								foreach ($module_route as $route => $route_details) {
									if ($route_details['rule'] == 'blog' && $this->request_uri == 'blog') {
										//its a blog landing page
										$_GET['module'] = $route_details['params']['module'];
										$_GET['fc'] = $route_details['params']['fc'];
										$controller = 'default';
										$this->front_controller = self::FC_MODULE;
										unset($_GET['product_rewrite']);
										unset($_GET['category_rewrite']);
									}
									elseif (isset($result_pattern_st[1]) && (int)$result_pattern_st[1] > 0) {
										//Its a blog detail page
										if ($route_details['rule'] == 'blog/{id_st_blog_category}-{rewrite}') {
											$_GET['module'] = $route_details['params']['module'];
											$_GET['fc'] = $route_details['params']['fc'];
											$controller = $route_details['controller'];
											$this->front_controller = self::FC_MODULE;
											$_GET['id_st_blog_category'] = (int)$result_pattern_st[1];
											$_GET['rewrite'] = (int)$result_pattern_st[2];
											unset($_GET['product_rewrite']);
											unset($_GET['category_rewrite']);
										}
									}
								}
							}
						}
					}
				}
				elseif (Module::isEnabled('ybc_blog') == true) {
					$ybc_pagination_rule = '/.*blog\/([0-9]*)/';
					preg_match($ybc_pagination_rule, $this->request_uri, $result_pattern_ybc_p);
					$ybc_tag_rule = '/.*blog\/tag\/([_a-zA-Z0-9-\pL]*)/';
					preg_match($ybc_tag_rule, $this->request_uri, $result_pattern_ybc_tag);
					$ybc_search_rule = '/.*blog\/search\/([_a-zA-Z0-9-\pL]*)/';
					preg_match($ybc_search_rule, $this->request_uri, $result_pattern_ybc_search);
					$ybc_gallery_rule = '/.*blog\/(gallery)*/';
					preg_match($ybc_gallery_rule, $this->request_uri, $result_pattern_ybc_gallery);
					$ybc_author_rule = '/.*blog\/author\/([0-9]+)\-([_a-zA-Z0-9-\pL]*)/';
					preg_match($ybc_author_rule, $this->request_uri, $result_pattern_ybc_author);
					$ybc_author_page_rule = '/.*blog\/author\/([0-9]+)\/([0-9]+)\-([_a-zA-Z0-9-\pL]*)/';
					preg_match($ybc_author_page_rule, $this->request_uri, $result_pattern_ybc_author_p);
					if (is_array($modules_routes) && count($modules_routes)) {
						foreach ($modules_routes as $module_route) {
							if (is_array($module_route) && count($module_route)) {
								foreach ($module_route as $route => $route_details) {
									if ($route_details['rule'] == 'blog' && $this->request_uri == 'blog') {
										//its a blog landing page
										$_GET['module'] = $route_details['params']['module'];
										$_GET['fc'] = $route_details['params']['fc'];
										$controller = 'blog';
										$this->front_controller = self::FC_MODULE;
										unset($_GET['product_rewrite']);
										unset($_GET['category_rewrite']);
									}
									elseif (isset($result_pattern_ybc_p[1]) && !empty($result_pattern_ybc_p[1]) && (int)$result_pattern_ybc_p[1] > 0) {
										//Its a blog detail page
										if ($route_details['rule'] == 'blog/{page}') {
											$_GET['module'] = $route_details['params']['module'];
											$_GET['fc'] = $route_details['params']['fc'];
											$controller = $route_details['controller'];
											$this->front_controller = self::FC_MODULE;
											$_GET['page'] = $result_pattern_ybc_p[1];
											unset($_GET['product_rewrite']);
											unset($_GET['category_rewrite']);
										}
									}
									elseif (isset($result_pattern_ybc_tag[1]) && !empty($result_pattern_ybc_tag[1])) {
										//Its a blog detail page
										if ($route_details['rule'] == 'blog/tag/{tag}') {
											$_GET['module'] = $route_details['params']['module'];
											$_GET['fc'] = $route_details['params']['fc'];
											$controller = $route_details['controller'];
											$this->front_controller = self::FC_MODULE;
											$_GET['tag'] = $result_pattern_ybc_tag[1];
											unset($_GET['product_rewrite']);
											unset($_GET['category_rewrite']);
										}
									}
									elseif (isset($result_pattern_ybc_search[1]) && !empty($result_pattern_ybc_search[1])) {
										//Its a blog detail page
										if ($route_details['rule'] == 'blog/search/{search}') {
											$_GET['module'] = $route_details['params']['module'];
											$_GET['fc'] = $route_details['params']['fc'];
											$controller = $route_details['controller'];
											$this->front_controller = self::FC_MODULE;
											$_GET['search'] = $result_pattern_ybc_search[1];
											unset($_GET['product_rewrite']);
											unset($_GET['category_rewrite']);
										}
									}
									elseif (isset($result_pattern_ybc_gallery[1]) && !empty($result_pattern_ybc_gallery[1]) && $result_pattern_ybc_gallery[1] == 'gallery') {
										//Its a blog detail page
										if ($route_details['rule'] == 'blog/gallery') {
											$_GET['module'] = $route_details['params']['module'];
											$_GET['fc'] = $route_details['params']['fc'];
											$controller = $route_details['controller'];
											$this->front_controller = self::FC_MODULE;
											unset($_GET['product_rewrite']);
											unset($_GET['category_rewrite']);
										}
									}
									elseif (isset($result_pattern_ybc_author[2]) && !isset($result_pattern_ybc_author_p[3]) && !empty($result_pattern_ybc_author[2])) {
										//Its a blog detail page
										if ($route_details['rule'] == 'blog/author/{id_author}-{author_name}') {
											$_GET['module'] = $route_details['params']['module'];
											$_GET['fc'] = $route_details['params']['fc'];
											$controller = $route_details['controller'];
											$this->front_controller = self::FC_MODULE;
											$_GET['id_author'] = $result_pattern_ybc_author[1];
											$_GET['author_name'] = $result_pattern_ybc_author[2];
											unset($_GET['product_rewrite']);
											unset($_GET['category_rewrite']);
										}
									}
									elseif (isset($result_pattern_ybc_author_p[3]) && !empty($result_pattern_ybc_author_p[3])) {
										//Its a blog detail page
										if ($route_details['rule'] == 'blog/author/{page}/{id_author}-{author_name}') {
											$_GET['module'] = $route_details['params']['module'];
											$_GET['fc'] = $route_details['params']['fc'];
											$controller = $route_details['controller'];
											$this->front_controller = self::FC_MODULE;
											$_GET['page'] = $result_pattern_ybc_author_p[1];
											$_GET['id_author'] = $result_pattern_ybc_author_p[2];
											$_GET['author_name'] = $result_pattern_ybc_author_p[3];
											unset($_GET['product_rewrite']);
											unset($_GET['category_rewrite']);
										}
									}
								}
							}
						}
					}
				}
			}
			//FIX for PM-Advance-Pack module
			if (preg_match('/pack/', $this->request_uri) && isset($_GET['rand'])) {
				//Check if PM Advance Pack Module is active
				$pack_module_exists = Module::isEnabled('pm_advancedpack');
				if ($pack_module_exists == true) {
					$modules_routes = Hook::exec('moduleRoutes', array('id_shop' => $id_shop), null, true, false);
					$pm_pack_rule = '/.*pack\/([a-z]+)\/([0-9]*)/';
					preg_match($pm_pack_rule, $this->request_uri, $_result_pack);
					if (isset($_result_pack[1])) {
						if (is_array($modules_routes) && count($modules_routes)) {
							foreach ($modules_routes as $module_route) {
								if (is_array($module_route) && count($module_route)) {
									foreach ($module_route as $route => $route_details) {
										//FIX for newer versions of pm advancepack module
										if (strpos($route_details['rule'], 'ap5')) {
												if ($_result_pack[1] == 'update' && $route_details['rule'] == 'pack/update/{id_pack}/ap5') {
												$_GET['module'] = $route_details['params']['module'];
												$_GET['fc'] = $route_details['params']['fc'];
												$controller = $route_details['controller'];
												$this->front_controller = self::FC_MODULE;
												$_POST['id_pack'] = (int)$_result_pack[2];
											}
											elseif ($_result_pack[1] == 'add' && $route_details['rule'] == 'pack/add/{id_pack}/ap5') {
												$_GET['module'] = $route_details['params']['module'];
												$_GET['fc'] = $route_details['params']['fc'];
												$controller = $route_details['controller'];
												$this->front_controller = self::FC_MODULE;
												$_POST['id_pack'] = (int)$_result_pack[2];
											}
										}
										else {
											if ($_result_pack[1] == 'update' && $route_details['rule'] == 'pack/update/{id_pack}') {
												$_GET['module'] = $route_details['params']['module'];
												$_GET['fc'] = $route_details['params']['fc'];
												$controller = $route_details['controller'];
												$this->front_controller = self::FC_MODULE;
												$_POST['id_pack'] = (int)$_result_pack[2];
											}
											elseif ($_result_pack[1] == 'add' && $route_details['rule'] == 'pack/add/{id_pack}') {
												$_GET['module'] = $route_details['params']['module'];
												$_GET['fc'] = $route_details['params']['fc'];
												$controller = $route_details['controller'];
												$this->front_controller = self::FC_MODULE;
												$_POST['id_pack'] = (int)$_result_pack[2];
											}
										}
									}
								}
							}
						}
					}
				}
			}
			elseif (preg_match('/packs/', $this->request_uri) || preg_match('/pack/', $this->request_uri)) {
				//FIX for NDK-Pack module
				$ndkpack_module_exists = Module::isEnabled('ndk_steppingpack');
				if ($ndkpack_module_exists == true) {
					$ndk_pack_rule = '/.*packs\/([_a-zA-Z0-9-\pL]*)/';
					$ndk_pack_rule_ii = '/.*pack\/([_a-zA-Z0-9-\pL]*)/';
					preg_match($ndk_pack_rule, $this->request_uri, $_result_ndkpack);
					preg_match($ndk_pack_rule_ii, $this->request_uri, $_result_ndkpack_ii);
					if (isset($_result_ndkpack[1])) {
						$controller = 'list';
						$this->front_controller = self::FC_MODULE;
						$_GET['fc'] = 'module';
						$_GET['module'] = 'ndk_steppingpack';
					}
					elseif (isset($_result_ndkpack_ii[1])) {
						$ndk_split = explode('_', $_result_ndkpack_ii[1]);
						$ndk_split = (int)$ndk_split[0];
						$controller = 'default';
						$this->front_controller = self::FC_MODULE;
						$_GET['fc'] = 'module';
						$_GET['module'] = 'ndk_steppingpack';
						$_POST['id_pack'] = $ndk_split;
					}
				}
			}
			//FIX for PM Advance Search module
			if (preg_match('/s-/', $this->request_uri) && ($controller == 'pagenotfound' || $controller == 'product' || $controller == 'searchresults')) {
				$pm_advancedsearch_module_exists = (int)Module::isEnabled('pm_advancedsearch4');
				if ($pm_advancedsearch_module_exists && $pm_advancedsearch_module_exists > 0) {
					//$pm_search_rule = '/.*([_a-zA-Z0-9-\pL]+)\/s\-([0-9]*)/';
					$pm_search_rule = '/.*\/s\-([0-9]*)/';
					preg_match($pm_search_rule, $this->request_uri, $result_pmsearch);
					//Make really sure its PM Search URL
					if (!empty($result_pmsearch) && (int)$result_pmsearch[1] > 0) {
						//Now check if a pm advance search exists for the matched
						$pm_as_id = (int)$this->getPmAdvanceSearchIdExistance($result_pmsearch[1]);
						if ($pm_as_id > 0) {
							$simplify_source = ltrim($result_pmsearch[0], '/');
							$exploded_url_rewrite = explode('/', $simplify_source);
							$exploded_url_rewrite = $exploded_url_rewrite[0];
							$id_pm_search_cat = (int)$this->getCategoryId($exploded_url_rewrite);
							if ($id_pm_search_cat > 0) {
								//Its a category search
								$_GET['controller'] = 'searchresults';
								$controller = 'searchresults';
								$_GET['fc'] = 'module';
								$_GET['module'] = 'pm_advancedsearch4';
								$_GET['id_category_search'] = $id_pm_search_cat;
								$_GET['id'] = $id_pm_search_cat;
								$_POST['id_search'] = (int)$result_pmsearch[1];
								$_GET['id_search'] = (int)$result_pmsearch[1];
								$flattern_assq = explode('s-'.$result_pmsearch[1], $this->request_uri);
								$flattern_assq[0] = trim($flattern_assq[0], '/');
								$t = $flattern_assq[0].$flattern_assq[1];
								if (isset($_GET['page'])) {
									$t = explode('?', $t);
									$t = $t[0];
								}
								$_GET['as4_sq'] = $t;
								$this->front_controller = self::FC_MODULE;
							}
							else {
								//Check if its Manufacturer page
								$manufacturer_route = Configuration::get('PS_ROUTE_manufacturer_rule', null, null, Context::getContext()->shop->id);
								$manufacturer_route = explode('/', $manufacturer_route);
								$manufacturer_route = $manufacturer_route[0];
								if ($exploded_url_rewrite == $manufacturer_route || $exploded_url_rewrite == 'manufacturer') {
									//Its a manufacturer search
									$m_source = ltrim($result_pmsearch[0], '/');
									$m_source = explode('/', $m_source);
									$m_source_key = $m_source[1];
									$m_source_key = str_replace('-', ' ', $m_source_key);
									$id_manufact = (int)$this->getKeyExistanceManuf($m_source_key);
									if ($id_manufact > 0) {
										$id_pm_search_manufact = $id_manufact;
										$_GET['controller'] = 'searchresults';
										$controller = 'searchresults';
										$_GET['fc'] = 'module';
										$_GET['module'] = 'pm_advancedsearch4';
										$_GET['id_manufacturer_search'] = $id_pm_search_manufact;
										$_GET['id'] = $id_pm_search_manufact;
										$_POST['id_search'] = (int)$result_pmsearch[1];
										$_GET['id_search'] = (int)$result_pmsearch[1];
										$flattern_assq = explode('s-'.$result_pmsearch[1], $this->request_uri);
										$flattern_assq[0] = trim($flattern_assq[0], '/');
										$t = $flattern_assq[0].$flattern_assq[1];
										if (isset($_GET['page'])) {
											$t = explode('?', $t);
											$t = $t[0];
										}
										$_GET['as4_sq'] = $t;
										$this->front_controller = self::FC_MODULE;
									}
								}
							}
						}
					}
				}
			}
			//FIX for PM Advance Search module - SEO urls
			if ($controller == 'pagenotfound' || $controller == 'product' || $controller == 'searchresults') {
				$pm_advancedsearch_module_exists = (int)Module::isEnabled('pm_advancedsearch4');
				if ($pm_advancedsearch_module_exists && $pm_advancedsearch_module_exists > 0) {
					$pm_seo_rule = '/.*\/s\/([0-9]+)\/([_a-zA-Z0-9-\pL]*)/';
					preg_match($pm_seo_rule, $this->request_uri, $result_pm_seo);
					if (isset($result_pm_seo[1]) && isset($result_pm_seo[2])) {
						$pm_seo_id = (int)$this->getPmAdvanceSearchSeoIdExistance($result_pm_seo[1]);
						
						if ($pm_seo_id > 0) {
							$_GET['controller'] = 'seo';
							$controller = 'seo';
							$_GET['fc'] = 'module';
							$_GET['module'] = 'pm_advancedsearch4';
							$this->front_controller = self::FC_MODULE;
							$_GET['id_seo'] = $result_pm_seo[1];
							$_GET['seo_url'] = $result_pm_seo[2];
						}
					}
				}
			}
			//Fix for LookBook by DevHub
			if ($controller == 'pagenotfound' || $controller == 'module-productlookbooks-list') {
				$lookbook_module_exists = (int)Module::isEnabled('productlookbooks');
				if ($lookbook_module_exists && $lookbook_module_exists > 0) {
					$route_used_lookbook = Configuration::get('PS_ROUTE_module-productlookbooks-list');
					$get_uri_displaypage = explode($route_used_lookbook, $this->request_uri);
					$get_uri_displaypage = end($get_uri_displaypage);
					$get_uri_displaypage = ltrim($get_uri_displaypage, '/');
					$lb_rule = '/.*?\/([0-9]+)\-([_a-zA-Z0-9-\pL]*)/';
					preg_match($lb_rule, $this->request_uri, $lb_rule_result);
					if (isset($lb_rule_result[1])) {
						$_GET['id'] = $lb_rule_result[1];
						$_GET['rewrite'] = $lb_rule_result[2];
					}
					$_GET['fc'] = 'module';
					$_GET['module'] = 'productlookbooks';
					$controller = 'display';
					$_GET['controller'] = $controller;
					$this->front_controller = self::FC_MODULE;
				}
			}
            //Fix for Marketplace module by Webkul
            $webkul_market_module_exists = (int)Module::isEnabled('marketplace');
            if ($webkul_market_module_exists > 0) {
                $webkul_seo_enabled = (int)Configuration::get('WK_MP_URL_REWRITE_ADMIN_APPROVE');
                if ($webkul_seo_enabled > 0 && ($controller == 'product' || $controller == 'category')) {
                    $webkul_shop_prefix = Configuration::get('WK_MP_SELLER_SHOP_PREFIX');
                    $webkul_shop_profile_prefix = Configuration::get('WK_MP_SELLER_PROFILE_PREFIX');
                    if (!empty($webkul_shop_prefix) && preg_match('/'.$webkul_shop_prefix.'/', $this->request_uri)) {
                        if (preg_match('/\?/', $this->request_uri)) {
                            $webkul_shop_name_w_mark = explode('?', $this->request_uri);
                            $webkul_shop_name_w_mark_filtered = $webkul_shop_name_w_mark[0];
                            $webkul_shop_name = explode('/', $webkul_shop_name_w_mark_filtered);
                            $webkul_shop_name = end($webkul_shop_name);
                        }
                        else {
                            $webkul_shop_name = explode('/', $this->request_uri);
                            $webkul_shop_name = end($webkul_shop_name);
                        }
                        if (!empty($webkul_shop_name)) {
                            $webkul_shop_id = (int)$this->getSellerIdWebkul($webkul_shop_name);
                            if ($webkul_shop_id > 0) {
                                $_GET['fc'] = 'module';
                                $_GET['module'] = 'marketplace';
                                $controller = 'shopstore';
                                $_GET['controller'] = $controller;
                                $_GET['mp_shop_name'] = $webkul_shop_name;
                                $this->front_controller = self::FC_MODULE;
                            }
                        }
                    }
                    elseif (!empty($webkul_shop_profile_prefix) && preg_match('/'.$webkul_shop_profile_prefix.'/', $this->request_uri)) {
                        $webkul_shop_name = explode('/', $this->request_uri);
                        $webkul_shop_name = end($webkul_shop_name);
                        if (!empty($webkul_shop_name)) {
                            $webkul_shop_id = (int)$this->getSellerIdWebkul($webkul_shop_name);
                            if ($webkul_shop_id > 0) {
                                $_GET['fc'] = 'module';
                                $_GET['module'] = 'marketplace';
                                $controller = 'sellerprofile';
                                $_GET['controller'] = $controller;
                                $_GET['mp_shop_name'] = $webkul_shop_name;
                                $this->front_controller = self::FC_MODULE;
                            }
                        }
                    }
                }
            }
			//FIX for AMP modules
			if (Module::isEnabled('pk_amp') == true) {
				$amp_settings = Tools::unSerialize(Configuration::get('AMP_CONFIG'));
				$amp_slug = $amp_settings['general_slug'];
				$slug_uri = ltrim($this->request_uri, '/');
				if(strpos($slug_uri, '/') !== false) {
					$slug_uri = explode('/', $slug_uri);
					$slug_uri = $slug_uri[0];
				}
				//Making sure its pk-amp module
				if ($slug_uri == $amp_slug) {
                    $modules_routes = Hook::exec('moduleRoutes', array('id_shop' => $id_shop), null, true, false);
					if (is_array($modules_routes) && count($modules_routes)) {
						$rule_i = '/.*'.$amp_slug.'\/category\/([0-9]+)\-([_a-zA-Z0-9-\pL]*)/';
						$rule_ii = '/.*'.$amp_slug.'\/product\/([0-9]+)\-([_a-zA-Z0-9-\pL]*)/';
						$rule_iii = '/.*'.$amp_slug.'\/page\/([0-9]+)\-([_a-zA-Z0-9-\pL]*)/';
						$rule_iv = '/.*'.$amp_slug.'\/brand\/([0-9]+)\-([_a-zA-Z0-9-\pL]*)/';
						preg_match($rule_i, $this->request_uri, $result_i);
						preg_match($rule_ii, $this->request_uri, $result_ii);
						preg_match($rule_iii, $this->request_uri, $result_iii);
						preg_match($rule_iv, $this->request_uri, $result_iv);
						foreach ($modules_routes as $module_route) {
							if (is_array($module_route) && count($module_route)) {
								foreach ($module_route as $route => $route_details) {
									$slug_uri_trimmed = ltrim($this->request_uri, '/');
									if ($route_details['rule'] == $slug_uri_trimmed) {
										//its a blog landing page
										$_GET['module'] = $route_details['params']['module'];
										$_GET['fc'] = $route_details['params']['fc'];
										$controller = 'home';
										$this->front_controller = self::FC_MODULE;
										unset($_GET['product_rewrite']);
										unset($_GET['category_rewrite']);
									}
									elseif (isset($result_i[1]) && (int)$result_i[1] > 0 && $route_details['rule'] == $amp_slug.'/category/{id_category}-{link_rewrite}') {
										$_GET['module'] = $route_details['params']['module'];
										$_GET['fc'] = $route_details['params']['fc'];
										$controller = 'category';
										$this->front_controller = self::FC_MODULE;
										$_GET['id_category'] = (int)$result_i[1];
										unset($_GET['product_rewrite']);
										unset($_GET['category_rewrite']);
									}
									elseif (isset($result_ii[1]) && (int)$result_ii[1] > 0 && $route_details['rule'] == $amp_slug.'/product/{id_product}-{link_rewrite}') {
										$_GET['module'] = $route_details['params']['module'];
										$_GET['fc'] = $route_details['params']['fc'];
										$controller = 'product';
										$this->front_controller = self::FC_MODULE;
										$_GET['id_product'] = (int)$result_ii[1];
										unset($_GET['product_rewrite']);
										unset($_GET['category_rewrite']);
									}
									elseif (isset($result_iii[1]) && (int)$result_iii[1] > 0 && $route_details['rule'] == $amp_slug.'/page/{id_cms}-{link_rewrite}') {
										$_GET['module'] = $route_details['params']['module'];
										$_GET['fc'] = $route_details['params']['fc'];
										$controller = 'cms';
										$this->front_controller = self::FC_MODULE;
										$_GET['id_cms'] = (int)$result_iii[1];
										unset($_GET['product_rewrite']);
										unset($_GET['category_rewrite']);
									}
									elseif (isset($result_iv[1]) && (int)$result_iv[1] > 0 && $route_details['rule'] == $amp_slug.'/brand/{id_manufacturer}-{link_rewrite}') {
										$_GET['module'] = $route_details['params']['module'];
										$_GET['fc'] = $route_details['params']['fc'];
										$controller = 'manufacturer';
										$this->front_controller = self::FC_MODULE;
										$_GET['id_manufacturer'] = (int)$result_iv[1];
										unset($_GET['product_rewrite']);
										unset($_GET['category_rewrite']);
									}
								}
							}
						}
					}
				}
			}
			//Fix for Home Comments module by Lineven
			if (Module::isEnabled('homecomments') == true) {
				$homecomments_url_rewrite = (int)Configuration::get('LINEVEN_HCOM_SEO_ACTIVE_RWRT');
				if ($homecomments_url_rewrite > 0) {
					$id_lang = Context::getContext()->language->id;
					$homecomments_url_route = Meta::getMetaByPage('module-homecomments-reviews', $id_lang);
					if (is_array($homecomments_url_route) && !empty($homecomments_url_route)) {
						if (preg_match('/'.$homecomments_url_route['url_rewrite'].'/', $this->request_uri)) {
							$match_finder = '/.*'.$homecomments_url_route['url_rewrite'].'\/([_a-zA-Z0-9-\pL]+)\-([0-9]*)/';
							preg_match($match_finder, $this->request_uri, $finder_score);
							if (isset($finder_score[2])) {//its product page reviews
								$_GET['module'] = 'homecomments';
								$_GET['fc'] = 'module';
								$controller = 'reviews';
								$this->front_controller = self::FC_MODULE;
								if (empty($finder_score[2])) {
									$id_customer = (int)Context::getContext()->customer->id;
									$_GET['idc'] = $id_customer ? $id_customer : null;
								}
							}
						}
					}
				}
			}
			//FIX for onepagecheckoutps module by Presteamshop
			if (preg_match('/checkout/', $this->request_uri) && Module::isEnabled('onepagecheckoutps')) {
				$this->request_uri = ltrim($this->request_uri, '/');
				$modules_routes = Hook::exec('moduleRoutes', array('id_shop' => $id_shop), null, true, false);
				if (is_array($modules_routes) && count($modules_routes)) {
					foreach ($modules_routes as $module_route) {
						if (is_array($module_route) && count($module_route)) {
							foreach ($module_route as $route => $route_details) {
								if ($route_details['rule'] == $this->request_uri) {
									$_GET['module'] = $route_details['params']['module'];
									$_GET['fc'] = $route_details['params']['fc'];
									$_GET['action'] = $route_details['params']['action'];

									$controller = $route_details['controller'];
									$this->front_controller = self::FC_MODULE;
									unset($_GET['product_rewrite']);
									unset($_GET['category_rewrite']);
								}
							}
						}
					}
				}
			}
            //FIX for {categories} route for category URLs
            if ($controller == 'category') {
                    $id_category = (int)Tools::getValue('id_category');
                    $cataegory_rule = Configuration::get('PS_ROUTE_category_rule');
					$id_product = (int)Tools::getValue('id_product');
                if ($id_category <= 0 && strpos($cataegory_rule, 'categories')) {
					if (preg_match('/module/', $this->request_uri) && !isset($_GET['fc'])) {
						if (preg_match('/\?/', $this->request_uri)) {
							$_disperse_uri = explode('?', $this->request_uri);
							$_disperse_uri = $_disperse_uri[0];
						}
						else {
							$_disperse_uri = $this->request_uri;
						}
						$three_parts = array_values(array_filter(explode('/', $_disperse_uri)));
						//THE BIG THREE PARTS --------
						$_GET['fc'] = $three_parts[0];
						$_GET['module'] = $three_parts[1];
						$_GET['controller'] = $three_parts[2];
						$controller = $three_parts[2];
						$this->front_controller = self::FC_MODULE;
					}
					elseif ($id_product <= 0 && $id_category <= 0) {
						if (preg_match('/\?/', $this->request_uri)) {
							$_disperse_uri = explode('?', $this->request_uri);
							$_disperse_uri = $_disperse_uri[0];
						}
						else {
							$_disperse_uri = $this->request_uri;
						}
						$three_parts = array_values(array_filter(explode('/', $_disperse_uri)));
						if (isset($three_parts[1]) && !empty($three_parts[1]) && !isset($three_parts[2])) {
							$check_for_cms_404 = (int)$this->getKeyExistanceCMS($three_parts[1]);
							if ($check_for_cms_404 > 0) {
								$controller = 'cms';
								$_POST['id_cms'] = $check_for_cms_404;
							}
						}
						elseif (isset($three_parts[2])) {
							$check_for_cms_404 = (int)$this->getKeyExistanceCMSCategory($three_parts[2]);
							if ($check_for_cms_404 > 0) {
								$controller = 'cms';
								$_POST['id_cms_category'] = $check_for_cms_404;
							}
						}
					}
                }
            }
		}
		//FIx for Iqit front editor - BETA
		if (preg_match('/Preview/', $this->request_uri) && preg_match('/module/', $this->request_uri) && isset($_GET['id_employee']) && $controller != 'Widget') {
			$module_iqit_exists = Module::isEnabled('iqitelementor');
			if ($module_iqit_exists == true) {
				$controller = 'Preview';
				$_GET['fc'] = 'module';
				$_GET['module'] = 'iqitelementor';
				$this->front_controller = self::FC_MODULE;
			}
		}
		//~~~~~~~~CLOSED~~~~~~~~~ only Front office fixes
		$this->controller = str_replace('-', '', $controller);
		$_GET['controller'] = $this->controller;
		return $this->controller;
	}

	private function getCategoryId($request)
	{
		$id_lang = Context::getContext()->language->id;
		$id_shop = Context::getContext()->shop->id;
		$sql = 'SELECT id_category FROM '._DB_PREFIX_.'category_lang
				WHERE link_rewrite = "'.pSQL($request).'" AND id_lang = '.(int)$id_lang.' AND id_shop = '.(int)$id_shop;
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
	}

	private function getControllerPageById($id)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `page` 
				FROM '._DB_PREFIX_.'meta
				WHERE id_meta = '.(int)$id);
	}

	private function getKeyExistance($req_uri)
	{
		$id_lang = Context::getContext()->language->id;
		$id_shop = Context::getContext()->shop->id;
		if (strpos($req_uri, '?'))
		{
			$req_uri_qmark = explode('?', $req_uri);
			$req_uri = $req_uri_qmark[0];
			return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT id_meta 
					FROM '._DB_PREFIX_.'meta_lang
					WHERE url_rewrite = "'.pSQL($req_uri).'"'.'
					AND `id_lang` = '.(int)$id_lang.' AND `id_shop` = '.(int)$id_shop);
		}
		else
		{
			return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT id_meta 
					FROM '._DB_PREFIX_.'meta_lang
					WHERE url_rewrite = "'.pSQL($req_uri).'"'.'
					AND `id_lang` = '.(int)$id_lang.' AND `id_shop` = '.(int)$id_shop);
		}
	}

	private function getProductExistance($request)
	{
		$id_lang = Context::getContext()->language->id;
		$id_shop = Context::getContext()->shop->id;
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `id_product`
		FROM '._DB_PREFIX_.'product_lang
		WHERE `link_rewrite` = "'.pSQL($request).'"'.'
		AND `id_lang` = '.(int)$id_lang.'
		AND `id_shop` = '.(int)$id_shop);
	}

	private function getKeyExistanceCMS($request)
	{
		$id_lang = Context::getContext()->language->id;
		$id_shop = Context::getContext()->shop->id;
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `id_cms`
		FROM '._DB_PREFIX_.'cms_lang
		WHERE `link_rewrite` = "'.pSQL($request).'"'.'
		AND `id_lang` = '.(int)$id_lang.'
		AND `id_shop` = '.(int)$id_shop);
	}

	private function getKeyExistanceCMSCategory($request)
	{
		$id_lang = Context::getContext()->language->id;
		$id_shop = Context::getContext()->shop->id;
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `id_cms_category`
		FROM '._DB_PREFIX_.'cms_category_lang
		WHERE `link_rewrite` = "'.pSQL($request).'"'.'
		AND `id_lang` = '.(int)$id_lang.'
		AND `id_shop` = '.(int)$id_shop);
	}
	
	private function getKeyExistanceManuf($request)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `id_manufacturer`
				FROM '._DB_PREFIX_.'manufacturer
				WHERE `name` LIKE "'.pSQL($request).'"');
	}

	private function getProductExistanceByRewrite($id)
	{
		$id_lang = Context::getContext()->language->id;
		$id_shop = Context::getContext()->shop->id;
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `link_rewrite`
			FROM '._DB_PREFIX_.'product_lang
			WHERE `id_product` = '.(int)$id.'
			AND `id_lang` = '.(int)$id_lang.'
			AND `id_shop` = '.(int)$id_shop);
	}
	
	private function getIdAdvanceCms($request)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `id_ad_cms`
				FROM '._DB_PREFIX_.'ad_cms_lang
				WHERE `link_rewrite` = "'.pSQL($request).'"');
	}
	
	private function getIdFormsMod($request)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `id_fm_form`
				FROM '._DB_PREFIX_.'fm_form_lang
				WHERE `link_rewrite` = "'.pSQL($request).'"');
	}
	
	private function getIdGallery($request)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `id_gallery`
				FROM '._DB_PREFIX_.'gallery_lang
				WHERE `link_rewrite` = "'.pSQL($request).'"');
	}
	
	private function getModule($module)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `active`
		FROM '._DB_PREFIX_.'module
		WHERE `name` = "'.pSQL($module).'"');
	}
	
	private function getFriendlyModRoute($uri)
	{
		$id_lang = Context::getContext()->language->id;
		$id_shop = Context::getContext()->shop->id;
		if (empty($uri)) {
			return false;
		}
		else {
			return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT a.`page` 
			FROM '._DB_PREFIX_.'meta a
			LEFT JOIN '._DB_PREFIX_.'meta_lang b ON (a.`id_meta` = b.`id_meta`)
			WHERE a.`page` LIKE "%module%"
			AND b.`url_rewrite` = "'.pSQL($uri).'"
			AND b.`id_lang` = '.(int)$id_lang.' AND b.`id_shop` = '.(int)$id_shop);
		}
	}
	
	private function getPmAdvanceSearchIdExistance($id)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `active`
		FROM '._DB_PREFIX_.'pm_advancedsearch
		WHERE `id_search` = '.(int)$id);
	}
	
	private function getPmAdvanceSearchSeoIdExistance($id)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `id_search`
		FROM '._DB_PREFIX_.'pm_advancedsearch_seo
		WHERE `id_seo` = '.(int)$id);
	}
    
    private function getSellerIdWebkul($url)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `id_seller`
		FROM '._DB_PREFIX_.'wk_mp_seller
		WHERE `link_rewrite` = "'.pSQL($url).'"');
	}
}