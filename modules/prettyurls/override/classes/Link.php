<?php
/**
 * PrettyURLs
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @author    FMM Modules
 * @copyright Copyright 2019 © Fmemodules All right reserved
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @category  FMM Modules
 * @package   PrettyURLs
*/

class Link extends LinkCore
{
	public function getCategoryLink($category, $alias = null, $id_lang = null, $selected_filters = null, $id_shop = null, $relative_protocol = false)
	{
		if (!$id_lang) {
			$id_lang = Context::getContext()->language->id;
		}
		$url = $this->getBaseLink($id_shop, null, $relative_protocol).$this->getLangLink($id_lang, null, $id_shop);

		if (!is_object($category)) {
			$category = new Category($category, $id_lang);
		}
		// Set available keywords
		$params = array();
		$params['id'] = $category->id;
		$params['rewrite'] = (!$alias) ? $category->link_rewrite : $alias;
		$params['meta_keywords'] =	@Tools::str2url($category->meta_keywords);
		$params['meta_title'] = @Tools::str2url($category->meta_title);
		// Selected filters is used by the module blocklayered
		$selected_filters = is_null($selected_filters) ? '' : $selected_filters;

		if (empty($selected_filters)) {
			$rule = 'category_rule';
		} else {
			$rule = 'layered_rule';
			$params['selected_filters'] = $selected_filters;
		}
		$cat_array = array();
		$parent_categories = $this->getAllParentCategories($category->id);
		$parent_categories = is_array($parent_categories) === true ? array_reverse($parent_categories) : $parent_categories;
		$skip_list = array(Configuration::get('PS_HOME_CATEGORY'), Configuration::get('PS_ROOT_CATEGORY'));
		$skip_list[] = $category->id;

		foreach ($parent_categories as $parent_cat) {
			if (!in_array($parent_cat['id_category'], $skip_list)) {
				$cat_array[] = $parent_cat['link_rewrite'];
			}
		}
        $dispatcher = Dispatcher::getInstance();
		if ($dispatcher->hasKeyword($rule, $id_lang, 'categories', $id_shop)) {
			$cats = array();
			foreach ($category->getParentsCategories() as $cat) {
				if (!in_array($cat['id_category'], array(1, 2, $category->id))) {
					$cats[] = $cat['link_rewrite'];//remove root, home and current category from the URL
				}
			}
			$params['categories'] = implode('/', array_reverse($cats));
		}
		$r_url = $url.Dispatcher::getInstance()->createUrl($rule, $id_lang, $params, $this->allow, '', $id_shop);
		return $r_url;
	}

	public function getAllParentCategories($id_current = null, $id_lang = null)
	{
		$context = Context::getContext()->cloneContext();
		$context->shop = clone($context->shop);

		if (is_null($id_lang)) {
			$id_lang = $context->language->id;
		}
		$categories = null;
		$cat_wo_parent = count(Category::getCategoriesWithoutParent());
		$multishop_feature = Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE');
		if ($cat_wo_parent > 1 && $multishop_feature && count(Shop::getShops(true, null, true)) != 1) {
			$context->shop->id_category = Category::getTopCategory()->id;
		}
		elseif (!$context->shop->id) {
			$context->shop = new Shop(Configuration::get('PS_SHOP_DEFAULT'));
		}
		$id_shop = $context->shop->id;
		while (true) {
			$sql = '
			SELECT c.*, cl.*
			FROM `'._DB_PREFIX_.'category` c
			LEFT JOIN `'._DB_PREFIX_.'category_lang` cl
				ON (c.`id_category` = cl.`id_category`
				AND `id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('cl').')';
			if (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_SHOP)
				$sql .= '
			LEFT JOIN `'._DB_PREFIX_.'category_shop` cs
				ON (c.`id_category` = cs.`id_category` AND cs.`id_shop` = '.(int)$id_shop.')';
			$sql .= '
			WHERE c.`id_category` = '.(int)$id_current;
			if (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_SHOP) {
				$sql .= '
				AND cs.`id_shop` = '.(int)$context->shop->id;
			}
			$root_category = Category::getRootCategory();

			$f_active = Shop::isFeatureActive();
			$submit_id_cat = Tools::isSubmit('id_category');
			$g_id_cat = (int)Tools::getValue('id_category');
			$r_cat_id = (int)$root_category->id;
			$c_id_cat = (int)$context->shop->id_category;

			if ($f_active && Shop::getContext() == Shop::CONTEXT_SHOP && (!$submit_id_cat || $g_id_cat == $r_cat_id || $r_cat_id == $c_id_cat)) {
				$sql .= ' AND c.`id_parent` != 0';
			}
			$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

			if (Tools::getIsset($result[0])) {
				$categories[] = $result[0];
			}
			else if (!$categories) {
				$categories = array();
			}
			if (!$result || ($result[0]['id_category'] == $context->shop->id_category)) {
				return $categories;
			}
			$id_current = $result[0]['id_parent'];
		}
	}

	public function getPaginationLink($type, $id_object, $nb = false, $sort = false, $pagination = false, $array = false)
	{
		if (!$type && !$id_object) {
			$method_name = 'get'.Dispatcher::getInstance()->getController().'Link';
			if (method_exists($this, $method_name) && Tools::getIsset(Tools::getValue('id_'.Dispatcher::getInstance()->getController()))) {
				$type = Dispatcher::getInstance()->getController();
				$id_object = Tools::getValue('id_'.$type);
			}
		}

		if ($type && $id_object) {
			$url = $this->{'get'.$type.'Link'}($id_object, null);
		} else {
			if (Tools::getIsset(Context::getContext()->controller->php_self)) {
				$name = Context::getContext()->controller->php_self;
			} else {
				$name = Dispatcher::getInstance()->getController();
			}

			if ($name == 'category') {
				$url = $this->getCategoryLink(Tools::getValue('id_category'));
			} else {
				$url = $this->getPageLink($name);
			}
		}

		$vars = array();
		$vars_nb = array('n', 'search_query');
		$vars_sort = array('orderby', 'orderway');
		$vars_pagination = array('p');
		foreach ($_GET as $k => $value) {
			if ($k != 'id_'.$type && $k != $type.'_rewrite' && $k != 'controller') {
				if (Configuration::get('PS_REWRITING_SETTINGS') && ($k == 'isolang' || $k == 'id_lang')) {
					continue;
				}
				$if_nb = (!$nb || ($nb && !in_array($k, $vars_nb)));
				$if_sort = (!$sort || ($sort && !in_array($k, $vars_sort)));
				$if_pagination = (!$pagination || ($pagination && !in_array($k, $vars_pagination)));
				if ($if_nb && $if_sort && $if_pagination) {
					if (!is_array($value)) {
						$vars[urlencode($k)] = $value;
					} else {
						foreach (explode('&', http_build_query(array($k => $value), '', '&')) as $val) {
							$data = explode('=', $val);
							$vars[urldecode($data[0])] = $data[1];
						}
					}
				}
			}
		}

		if ($name == 'category') {
			unset($vars['categories_rewrite']);
			unset($vars['category_rewrite']);
		}
			$manuf_uri = explode('/', $_SERVER['REQUEST_URI']);
			$manuf_end = end($manuf_uri);
			if (preg_match('/\?/', $manuf_end)) {
				$manuf_end = explode('?', $manuf_end);
				$manuf_end = $manuf_end[0];
				$clearify_request = str_replace('-', ' ', $manuf_end);
				$manu_existance = (int)$this->getKeyExistanceManuf($clearify_request);
				$supp_existance = (int)$this->getKeyExistanceSup($clearify_request);
				if ($manu_existance > 0) {
					$vars['manufacturer_rewrite'] = $manuf_end;
				}
				elseif ($supp_existance > 0) {
					$vars['supplier_rewrite'] = $manuf_end;
				}
			} else {
				$clearify_request = str_replace('-', ' ', $manuf_end);
				$manu_existance = (int)$this->getKeyExistanceManuf($clearify_request);
				$supp_existance = (int)$this->getKeyExistanceSup($clearify_request);
				if ($manu_existance > 0) {
					$vars['manufacturer_rewrite'] = $manuf_end;
				}
				elseif ($supp_existance > 0) {
					$vars['supplier_rewrite'] = $manuf_end;
				}
			}
		if (!$array)
			if (count($vars))
				return $url.(($this->allow == 1 || $url == $this->url) ? '?' : '&').http_build_query($vars, '', '&');
			else
				return $url;
		$vars['requestUrl'] = $url;

		if ($type && $id_object) {
			$vars['id_'.$type] = (is_object($id_object) ? (int)$id_object->id : (int)$id_object);
		}

		if (!$this->allow == 1) {
			$vars['controller'] = Dispatcher::getInstance()->getController();
		}

		if ($name == 'newproducts' || $name == 'pricesdrop' || $name == 'bestsales') {
				if (preg_match('/index/', $vars['requestUrl'])) {
					if (array_key_exists('p', $vars)) {
						$get_controller_page = Context::getContext()->controller->php_self;
						$old_url = $vars['requestUrl'];
						$req_uri_new = explode('index', $old_url);
						$req_uri_new = $req_uri_new[0];
						$vars['requestUrl'] = $req_uri_new.$get_controller_page;
						unset($vars['category_rewrite']);
					} else {
						$get_controller_page = Context::getContext()->controller->php_self;
						$vars['category_rewrite'] = $get_controller_page;
						$old_url = $vars['requestUrl'];
						$req_uri_new = explode('index', $old_url);
						$req_uri_new = $req_uri_new[0];
						$vars['requestUrl'] = $req_uri_new.$vars['category_rewrite'];
						unset($vars['category_rewrite']);
					}
				}
		}
		return $vars;
	}

	public function getManufacturerLink($manufacturer, $alias = null, $id_lang = null, $id_shop = null, $relative_protocol = false)
	{
		if (!$id_lang) {
			$id_lang = Context::getContext()->language->id;
		}
		$url = $this->getBaseLink($id_shop, null, $relative_protocol).$this->getLangLink($id_lang, null, $id_shop);

		$dispatcher = Dispatcher::getInstance();

		if (!is_object($manufacturer)) {
			$d_man_rule_keywords = $dispatcher->hasKeyword('manufacturer_rule', $id_lang, 'meta_keywords', $id_shop);
			$d_man_rule_title = $dispatcher->hasKeyword('manufacturer_rule', $id_lang, 'meta_title', $id_shop);
			if ($alias !== null && !$d_man_rule_keywords && !$d_man_rule_title) {
				$man_rewrite = array('id' => (int)$manufacturer, 'rewrite' => (string)$alias);
				return $url.$dispatcher->createUrl('manufacturer_rule', $id_lang, $man_rewrite, $this->allow, '', $id_shop);
			}
			$manufacturer = new Manufacturer($manufacturer, $id_lang);
		}
		// Set available keywords
		$link_rewrite = (!$alias) ? $manufacturer->link_rewrite : $alias;
		$params = array();
		$params['id'] = $manufacturer->id;
		$params['rewrite'] = $link_rewrite;
		$params['meta_keywords'] =	Tools::str2url($manufacturer->meta_keywords);
		$params['meta_title'] = Tools::str2url($manufacturer->meta_title);
		$man_pattern = '/.*?([0-9]+)\_([_a-zA-Z0-9-\pL]*)/';
		preg_match($man_pattern, $_SERVER['REQUEST_URI'], $url_array);
		if (!empty($url_array)) {
			return $url.'manufacturer/'.$dispatcher->createUrl('manufacturer_rule', $id_lang, $params, $this->allow, '', $id_shop);
		} else {
			return $url.$dispatcher->createUrl('manufacturer_rule', $id_lang, $params, $this->allow, '', $id_shop);
		}
	}

	public function getSupplierLink($supplier, $alias = null, $id_lang = null, $id_shop = null, $relative_protocol = false)
	{
		if (!$id_lang) {
			$id_lang = Context::getContext()->language->id;
		}
		$url = $this->getBaseLink($id_shop, null, $relative_protocol).$this->getLangLink($id_lang, null, $id_shop);
		$dispatcher = Dispatcher::getInstance();
		if (!is_object($supplier)) {
			$sup_rule_keywords = $dispatcher->hasKeyword('supplier_rule', $id_lang, 'meta_keywords', $id_shop);
			if ($alias !== null && !$sup_rule_keywords && !$dispatcher->hasKeyword('supplier_rule', $id_lang, 'meta_title', $id_shop)) {
				return $url.$dispatcher->createUrl('supplier_rule', $id_lang, array('id' => (int)$supplier, 'rewrite' => (string)$alias),
				$this->allow, '', $id_shop);
			}
			$supplier = new Supplier($supplier, $id_lang);
		}
		// Set available keywords
		$params = array();
		$params['id'] = $supplier->id;
		$params['rewrite'] = (!$alias) ? $supplier->link_rewrite : $alias;
		$params['meta_keywords'] =	Tools::str2url($supplier->meta_keywords);
		$params['meta_title'] = Tools::str2url($supplier->meta_title);
		$sup_pattern = '/.*?([0-9]+)\_\_([_a-zA-Z0-9-\pL]*)/';
		preg_match($sup_pattern, $_SERVER['REQUEST_URI'], $sup_array);
		if (!empty($sup_array)) {
			return $url.'supplier/'.$dispatcher->createUrl('supplier_rule', $id_lang, $params, $this->allow, '', $id_shop);
		} else {
			return $url.$dispatcher->createUrl('supplier_rule', $id_lang, $params, $this->allow, '', $id_shop);
		}
	}

	public function getLanguageLink($id_lang, Context $context = null)
	{
		if (!$context) {
			$context = Context::getContext();
		}
		$params = $_GET;
		unset($params['isolang'], $params['controller']);
		if (!$this->allow) {
			$params['id_lang'] = $id_lang;
		} else {
			unset($params['id_lang']);
		}
		$controller = Dispatcher::getInstance()->getController();

		if (!empty(Context::getContext()->controller->php_self)) {
			$controller = Context::getContext()->controller->php_self;
		}
		$def_page = (int)$this->checkKeyExistance($controller);
		//dump($params); exit;
		if ($controller == 'manufacturer') {
			$manuf_uri = explode('/', $_SERVER['REQUEST_URI']);
			$manuf_end = end($manuf_uri);
			$clearify_request = str_replace('-', ' ', $manuf_end);
			$manu_existance = (int)$this->getKeyExistanceManuf($clearify_request);
			if ($manu_existance > 0) {
				$params['id_manufacturer'] = $manu_existance;
			}
		}
		elseif ($controller == 'supplier') {
			$supp_uri = explode('/', $_SERVER['REQUEST_URI']);
			$supp_end = end($supp_uri);
			$clearify_request = str_replace('-', ' ', $supp_end);
			$supp_existance = (int)$this->getKeyExistanceSup($clearify_request);
			if ($supp_existance > 0) {
				$params['id_supplier'] = $supp_existance;
			}
		}
		elseif ($controller == 'category' && isset($params['category_rewrite']) && empty($params['category_rewrite'])) {
			$this->request_uri = $_SERVER['REQUEST_URI'];
			if (preg_match('/\?/', $this->request_uri)) {
				$uri_split_w_q = explode('/', $this->request_uri);
				$uri_split_w_q = array_filter($uri_split_w_q);
				$uri_split_w_q = end($uri_split_w_q);
				$uri_split_w_q = explode('?', $uri_split_w_q);
				$uri_split_w_q = $uri_split_w_q[0];
				$_id = (int)$this->getCategoryId($uri_split_w_q);
			} else {
				$uri_split = explode('/', $this->request_uri);
				$uri_split = array_filter($uri_split);
				$uri_split = end($uri_split);
				$_id = (int)$this->getCategoryId($uri_split);
			}
			if ($_id > 0) {
				$params['id_category'] = (int)$_id;
			}
		}
		elseif ($controller == 'category' && isset($params['category_rewrite']) && !empty($params['category_rewrite'])) {
			//Fix for accented chars URLs
			$allow_accented_chars = (int)Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL');
			if ($allow_accented_chars > 0) {
				$id_category = (int)Tools::getValue('id_category');
				if ($id_category > 0) {
					$params['id_category'] = $id_category;
				}
			}
		}
		elseif ($controller == 'product' && isset($params['product_rewrite']) && empty($params['product_rewrite'])) {
			$this->request_uri = $_SERVER['REQUEST_URI'];
			$uri_split = explode('/', $this->request_uri);
			$uri_split = array_filter($uri_split);
			$uri_split = end($uri_split);
			if (preg_match('/html/', $uri_split)) {
				$uri_split = str_replace('.html', '', $uri_split);
			}
			$_id = (int)$this->getProductExistance($uri_split);
			if ($_id > 0) {
				$params['id_product'] = (int)$_id;
			}
			elseif ($_id <= 0 && preg_match('/\?/', $this->request_uri)) {
				$_uri_with_q = explode('?', $this->request_uri);
				$_uri_with_q = explode('/', $_uri_with_q[0]);
				$_uri_with_q = end($_uri_with_q);
				$_id = (int)$this->getProductExistance($_uri_with_q);
				if ($_id > 0) {
					$params['id_product'] = (int)$_id;
				}
			}
		}
		elseif ($controller == 'product' && isset($params['product_rewrite']) && !empty($params['product_rewrite'])) {
			//Fix for accented chars URLs
			$allow_accented_chars = (int)Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL');
			if ($allow_accented_chars > 0) {
				$id_product = (int)Tools::getValue('id_product');
				if ($id_product > 0) {
					$params['id_product'] = $id_product;
				}
			}
		}
		elseif ($controller == 'cms' && isset($params['cms_rewrite']) && !empty($params['cms_rewrite'])) {
			//Fix for accented chars URLs
			$allow_accented_chars = (int)Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL');
			if ($allow_accented_chars > 0) {
				$id_cms = (int)Tools::getValue('id_cms');
				if ($id_cms > 0) {
					$params['id_cms'] = $id_cms;
				}
			}
		}
		elseif ($controller == 'product' && isset($params['category_rewrite']) && !empty($params['category_rewrite'])) {
			$_id = (int)$this->getProductExistance($params['category_rewrite']);
			if ($_id > 0) {
				$params['id_product'] = (int)$_id;
			}
		}
		elseif ($controller == 'cms' && isset($params['cms_rewrite']) && empty($params['cms_rewrite'])) {
			$this->request_uri = $_SERVER['REQUEST_URI'];
			$uri_split = explode('/', $this->request_uri);
			$uri_split = array_filter($uri_split);
			$uri_split = end($uri_split);
			$_id = (int)$this->getKeyExistanceCMS($uri_split);
			if ($_id > 0) {
				$params['id_cms'] = (int)$_id;
			}
			elseif ($_id <= 0 && preg_match('/\?/', $this->request_uri) && isset($params['SubmitCurrency'])) {
				$_uri_cms_clear = explode('?', $this->request_uri);
				$_uri_cms_clear = explode('/', $_uri_cms_clear[0]);
				$_uri_cms_clear = end($_uri_cms_clear);
				$_id = (int)$this->getKeyExistanceCMS($_uri_cms_clear);
				if ($_id > 0) {
					$params['id_cms'] = (int)$_id;
				}
			}
		}
		if ($controller == 'supplier' && isset($params['supplier_rewrite']) && !empty($params['supplier_rewrite'])) {
			//Fix for accented chars URLs
			$allow_accented_chars = (int)Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL');
			if ($allow_accented_chars > 0) {
				$id_supp = (int)Tools::getValue('id_supplier');
				if ($id_supp > 0) {
					$params['id_supplier'] = $id_supp;
				}
			}
		}
		if ($controller == 'manufacturer' && isset($params['manufacturer_rewrite']) && !empty($params['manufacturer_rewrite'])) {
			//Fix for accented chars URLs
			$allow_accented_chars = (int)Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL');
			if ($allow_accented_chars > 0) {
				$id_manufacturer = (int)Tools::getValue('id_manufacturer');
				if ($id_manufacturer > 0) {
					$params['id_manufacturer'] = $id_manufacturer;
				}
			}
		}
		//check for LookBook module
		if ($controller == 'list' && isset($params['module']) && $params['module'] == 'productlookbooks') {
			unset($params['category_rewrite']);
			unset($params['product_rewrite']);
		}
		elseif ($controller == 'display' && isset($params['module']) && $params['module'] == 'productlookbooks') {
			unset($params['category_rewrite']);
			unset($params['product_rewrite']);
		}
		if ($controller == 'product' && isset($params['id_product'])) {
			return $this->getProductLink((int)$params['id_product'], null, null, null, (int)$id_lang);
		}
		elseif ($controller == 'category' && isset($params['id_category'])) {
			return $this->getCategoryLink((int)$params['id_category'], null, (int)$id_lang);
		}
		elseif ($controller == 'supplier' && isset($params['id_supplier'])) {
			return $this->getSupplierLink((int)$params['id_supplier'], null, (int)$id_lang);
		}
		elseif ($controller == 'manufacturer' && isset($params['id_manufacturer'])) {
			return $this->getManufacturerLink((int)$params['id_manufacturer'], null, (int)$id_lang);
		}
		elseif ($controller == 'cms' && isset($params['id_cms'])) {
			return $this->getCMSLink((int)$params['id_cms'], null, false, (int)$id_lang);
		}
		elseif ($controller == 'cms' && isset($params['id_cms_category'])) {
			return $this->getCMSCategoryLink((int)$params['id_cms_category'], null, (int)$id_lang);
		}
		elseif ($def_page > 0 && !isset($params['id'])) {
			return $this->getPageLink($controller, null, $id_lang);
		}
		elseif (isset($params['fc']) && $params['fc'] == 'module')
		{
			$module = Validate::isModuleName(Tools::getValue('module')) ? Tools::getValue('module') : '';
			if (!empty($module)) {
				unset($params['fc'], $params['module']);
				return $this->getModuleLink($module, $controller, $params, null, (int)$id_lang);
			}
		}
		return $this->getPageLink($controller, null, $id_lang, $params);
	}

	public function getPageLink($controller, $ssl = null, $idLang = null, $request = null, $requestUrlEncode = false, $idShop = null, $relativeProtocol = false)
    {
		if ($controller == 'page-not-found') {
			$controller = 'pagenotfound';
		}
        //If $controller contains '&' char, it means that $controller contains request data and must be parsed first
        $p = strpos($controller, '&');
        if ($p !== false) {
            $request = substr($controller, $p + 1);
            $requestUrlEncode = false;
            $controller = substr($controller, 0, $p);
        }

        $controller = Tools::strReplaceFirst('.php', '', $controller);
        if (!$idLang) {
            $idLang = (int) Context::getContext()->language->id;
        }

        //need to be unset because getModuleLink need those params when rewrite is enable
        if (is_array($request)) {
            if (isset($request['module'])) {
                unset($request['module']);
            }
            if (isset($request['controller'])) {
                unset($request['controller']);
            }
        } else {
            // @FIXME html_entity_decode has been added due to '&amp;' => '%3B' ...
            $request = html_entity_decode($request);
            if ($requestUrlEncode) {
                $request = urlencode($request);
            }
            parse_str($request, $request);
        }

        if ($controller === 'cart' && (!empty($request['add']) || !empty($request['delete'])) && Configuration::get('PS_TOKEN_ENABLE')) {
            $request['token'] = Tools::getToken(false);
        }
		//Check for PM Advance search module
		$pm_advancedsearch_module_exists = (int)Module::isEnabled('pm_advancedsearch4');
		if (empty($request) && $pm_advancedsearch_module_exists > 0) {
			if (isset($_GET['id_search'])) {
				$request['id_search'] = $_GET['id_search'];
				$request['as4_sq'] = $_GET['as4_sq'];
			}
			elseif (isset($_GET['id_seo'])) {
				$request['id_seo'] = $_GET['id_seo'];
				$request['seo_url'] = $_GET['seo_url'];
			}
		}
        $uriPath = Dispatcher::getInstance()->createUrl($controller, $idLang, $request, false, '', $idShop);

        return $this->getBaseLink($idShop, $ssl, $relativeProtocol).$this->getLangLink($idLang, null, $idShop).ltrim($uriPath, '/');
    }
	
	private function checkKeyExistance($controller)
	{
			$sql = 'SELECT id_meta 
					FROM '._DB_PREFIX_.'meta
					WHERE page = "'.pSQL($controller).'"';
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
	}

	private function getKeyExistanceManuf($request)
	{
		$sql = 'SELECT `id_manufacturer`
					FROM '._DB_PREFIX_.'manufacturer
					WHERE `name` LIKE "'.pSQL($request).'"';
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
	}

	private function getKeyExistanceSup($request)
	{
		$sql = 'SELECT `id_supplier`
					FROM '._DB_PREFIX_.'supplier
					WHERE `name` LIKE "'.pSQL($request).'"';
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
	}
	
	private function getCategoryId($request)
	{
		$id_lang = Context::getContext()->language->id;
		$id_shop = Context::getContext()->shop->id;
		$sql = 'SELECT id_category FROM '._DB_PREFIX_.'category_lang
				WHERE link_rewrite = "'.pSQL($request).'" AND id_lang = '.(int)$id_lang.' AND id_shop = '.(int)$id_shop;
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
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
}