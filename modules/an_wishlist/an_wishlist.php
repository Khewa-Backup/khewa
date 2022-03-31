<?php
/**
 * 2020 Anvanto
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses. 
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 *  @author Anvanto <anvantoco@gmail.com>
 *  @copyright  2020 Anvanto
 *  @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of Anvanto
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

require_once _PS_MODULE_DIR_ . 'an_wishlist/classes/an_wish.php';
require_once _PS_MODULE_DIR_ . 'an_wishlist/classes/an_wish_products.php';
require_once _PS_MODULE_DIR_ . 'an_wishlist/classes/an_wishListing.php';

class an_wishlist extends Module implements WidgetInterface
{
    const PREFIX = 'an_wishlist_';

    public function __construct()
    {
        $this->name = 'an_wishlist';
        $this->tab = 'others';
        $this->version = '2.2.5';
        $this->author = 'Anvanto';
        $this->need_instance = 1;
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->bootstrap = true;
        $this->module_key = '8d8604a928f6af510b15e20a6975504d';
		$this->url_rate = 'https://bit.ly/3eeR8Bq';
		$this->url_contact_us = 'http://bit.ly/2xhIMp4';
		
        parent::__construct();

        $this->displayName = $this->l('Anvanto Wishlist');
        $this->description = $this->l('Extend default functionality by adding Wishlist — users can add products to it and buy them later. Wishlisted products are offered to buy in the cart to increase average cart value. Feedback on the most wishlisted products is available in live mode.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall the module?');
    }

    /**
     * @return bool
     */
    public function install()
    {
        Configuration::updateValue(self::PREFIX.'display_likes_product_mini', 1);
        Configuration::updateValue(self::PREFIX.'display_likes_product', 1);
        Configuration::updateValue(self::PREFIX.'display_likes_nav', 1);
        Configuration::updateValue(self::PREFIX.'display_wishlist_in_cart', 1);
        Configuration::updateValue(self::PREFIX.'display_social_buttons', 1);
        Configuration::updateValue(self::PREFIX.'wishlist_for_guests', 1);

        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }
		
		$sql = include _PS_MODULE_DIR_ . 'an_wishlist/sql/install.php';
		foreach ($sql as $_sql) {
			Db::getInstance()->Execute($_sql);
		}

        if (
            !parent::install() ||
            !$this->registerHook('header') ||
            !$this->registerHook('displayNav2') ||
            !$this->registerHook('displayProductAdditionalInfo') ||
            !$this->registerHook('displayProductListReviews') ||
            !$this->registerHook('customerAccount') ||
            !$this->registerHook('displayShoppingCartFooter') ||
            !$this->registerHook('registerGDPRConsent') ||
            !$this->registerHook('actionDeleteGDPRCustomer') ||
            !$this->registerHook('actionExportGDPRData') ||
            !$this->registerHook('actionCustomerAccountAdd')
        ) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function uninstall()
    {
        $paramList = $this->getParamList();
        $this->deleteParams($paramList);	
		
		$sql = include _PS_MODULE_DIR_ . 'an_wishlist/sql/uninstall.php';
		foreach ($sql as $_sql) {
			Db::getInstance()->Execute($_sql);
		}	

		parent::uninstall();
    }
	

	
    /**
     * @param $key
     * @param null $value
     * @param null $id_lang
     * @return bool|string
     */
    public static function getParam($key, $value = null, $id_lang = null)
    {
        return $value === null ? Configuration::get(
            self::PREFIX . $key,
            $id_lang
        ) : Configuration::updateValue(self::PREFIX . $key, $value);
    }

    public function getParamList()
    {
        return array(
            'display_likes_product_mini',
            'display_likes_product',
            'display_likes_nav',
            'display_wishlist_in_cart',
            'wishlist_for_guest',
            'display_social_buttons',
			'wishlist_for_guests'
        );
    }
    
    protected function deleteParams($keys)
    {
        foreach ($keys as $key) {
            $this->deleteParam($key);
        }
    }

    protected function deleteParam($key)
    {
        return Configuration::deleteByName(self::PREFIX.$key);
    }
    
    protected function updateParam($key, $value)
    {
        return Configuration::updateValue(self::PREFIX.$key, $value);
    }
    
    public function getConfig()
    {
        $config = array();
        $paramList = $this->getParamList();
        foreach ($paramList as $key) {
            $config[$key] = $this->getParam($key);
        }
        return $config;
    }

    /**
     *
     */
    public function hookHeader()
    {		
		$this->context->controller->addJquery();
        $this->context->controller->registerStylesheet(
            "anwishcss",
            'modules/' . $this->name . '/views/css/front.css',
            array('server' => 'local', 'priority' => 150)
        );
        $this->context->controller->registerJavascript(
            "anwishjs",
            'modules/' . $this->name . '/views/js/front.js',
            array('server' => 'local', 'priority' => 150)
        );
        
        if (!Context::getContext()->customer->isLogged()) {
            $this->context->controller->registerStylesheet(
                "anwishcss2",
                'modules/' . $this->name . '/views/css/magnific-popup.css',
                array('server' => 'local', 'priority' => 150)
            );
            $this->context->controller->registerJavascript(
                "anwishjs2",
                'modules/' . $this->name . '/views/js/jquery.magnific-popup.min.js',
                array('server' => 'local', 'priority' => 150)
            );
        }
    }

    public function hookCustomerAccount($params)
    {		
		$this->context->smarty->assign('an_whislist_link', Context::getContext()->link->getModuleLink(
            'an_wishlist',
            'list',
			[],
            true
        ));
        return $this->display(__FILE__, '/views/templates/front/my-account-link.tpl');
    }

    public function hookdisplayShoppingCartFooter($params)
    {
		if (Context::getContext()->customer->isLogged()) {
			$idCustomer = (int) Context::getContext()->customer->id;
			$is_guest = 0;
		} else {
			$is_guest = 1;
			$idCustomer = $this->getIsGuest();
		}
		
		$idWish = an_wish::findWishlistByCustomer($idCustomer, $is_guest);
		
        $products = an_wish_products::getProductsWishlist((int) $idWish);
        
        $listing = new an_wishListing();
        $products =  $listing->prepare($products);
        
        $this->context->smarty->assign('products', $products);
        
        return $this->display(__FILE__, '/views/templates/front/shoppingCartFooter.tpl');
    }

    /**
     * @param $hookName
     * @param array $params
     * @return mixed|void
     */
    public function renderWidget($hookName, array $params)
    {
        $variables = $this->getWidgetVariables($hookName, $params);
		
		$this->smarty->assign($variables);
        $tplFile = 'nav.tpl';
		
		if (isset($variables['id_product'])){
			$addLinks = Context::getContext()->link->getModuleLink(
				'an_wishlist', 
				'ajax', 
				[
					'token' => $variables['token'], 
					'id_product' => $variables['id_product'],
					'id_product_attribute' => $variables['id_product_attribute'],
					'action' => 'addRemove',
				],
				true
			);
			$addLinks = str_replace ('https:', '', $addLinks);
			
			$this->smarty->assign(
				array(
				 'an_wishlistAjax' => $addLinks,
				 'link' => Context::getContext()->link
				)
			);
		}
        
        if (preg_match('/^displayNav\w*$/', $hookName)) {
            $tplFile = 'nav.tpl';
        } elseif (preg_match('/^displayProductAdditionalInfo\w*$/', $hookName)) {
            $tplFile = 'product.tpl';
        } elseif ($hookName == 'displayProductListReviews' | preg_match('/^displayProductListWishlist\w*$/', $hookName)) {
            $tplFile = 'product-miniature.tpl';
        }
        
		return $this->fetch('module:an_wishlist/views/templates/front/' . $tplFile);
    }
    /**
     * @param $hookName
     * @param array $params
     * @return array
     */
    public function getWidgetVariables($hookName, array $params)
    {
        $return = array(
            'hook' => $hookName,
            'token' => Tools::getToken(false),
			'count' => 0,
			'status' => 0,
        );
		
		if (Context::getContext()->customer->isLogged()) {
			$idCustomer = (int) Context::getContext()->customer->id;
			$is_guest = 0;
		} else {
			$is_guest = 1;
			$idCustomer = $this->getIsGuest();
		}
		
		$idWish = an_wish::findWishlistByCustomer($idCustomer, $is_guest);
			
		if ($this->getParam('wishlist_for_guests') || Context::getContext()->customer->isLogged()) {
			$return['count'] = an_wish_products::countProductsWishlist($idWish);	
		}
        		
        if (preg_match('/^displayProductAdditionalInfo\w*$/', $hookName) | $hookName == 'displayProductListReviews' | preg_match('/^displayProductListWishlist\w*$/', $hookName)) {
            if (array_key_exists('product', $params) && array_key_exists('id_product', $params['product'])) {
                $return['id_product'] = $params['product']['id_product'];
                $return['id_product_attribute'] = $params['product']['id_product_attribute'];
            }
            else {
                $return['id_product'] = $params['smarty']->tpl_vars['product']->value['id_product'];
                $return['id_product_attribute'] = $params['smarty']->tpl_vars['product']->value['id_product_attribute'];
            }
			
			if ($this->getParam('wishlist_for_guests') || Context::getContext()->customer->isLogged()) {
				$return['status'] = an_wish_products::issetItem($return['id_product'], $idWish, $return['id_product_attribute']);
			}
			$return['countWishlists'] = an_wish_products::countProductsAllWishlists($return['id_product']);
        }
        
        $return['config'] = $this->getConfig();

        return $return;
    }
    
    public function hookActionDeleteGDPRCustomer($customer)
    {
        if (!empty($customer['id'])) {
			
			$idWish = an_wish::findWishlistByCustomer($customer['id'], 0);
			
			$sql = "DELETE FROM "._DB_PREFIX_."an_wishlist_products WHERE id_wishlist = '".(int)$idWish."'    ";
			Db::getInstance()->execute($sql);
			
            $sql = "DELETE FROM "._DB_PREFIX_."an_wishlist WHERE id_customer = '".(int)$customer['id']."'";
            if (Db::getInstance()->execute($sql)) {
                return Tools::jsonEncode(true);
            }
        }
    }

    public function hookActionExportGDPRData($customer)
    {
        if (!empty($customer['id'])) {
			
			$idWish = an_wish::findWishlistByCustomer($customer['id'], 0);
			
			$products = an_wish_products::getProductsWishlist((int) $idWish);

			$exportProducts = array();
			foreach ($products as $product){
				$exportProducts[] = array('id' => $product['id_product'], 'name' => $product['name'], 'reference' => $product['reference']);
			}

			return Tools::jsonEncode($exportProducts);	
        }
    }
	
    protected function csvExport($datas)
    {
        if (count($datas['columns'])) {
            foreach ($datas['columns'] as $column) {
                $this->_csv .= $column['header'] . ';';
            }
            $this->_csv = rtrim($this->_csv, ';') . "\n";

            foreach ($datas['values'] as $value) {
                foreach ($datas['columns'] as $column) {
                    $this->_csv .= $value[$column['dataIndex']] . ';';
                }
                $this->_csv = rtrim($this->_csv, ';') . "\n";
            }
        }
        $this->_displayCsv();
    }

    protected function _displayCsv()
    {
        if (ob_get_level() && ob_get_length() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $this->displayName . ' - ' . time() . '.csv"');
        echo $this->_csv;
        exit;
    }	
	
    
    public function getContent()
    {
		
		$antab = Tools::getValue('antab');
		
		$this->context->smarty->assign(array(
			'admUrl' => $this->context->link->getAdminLink('AdminModules'),
			'theme' => $this->getThemeInfo(),
		));		
		
		if ($antab == 'products'){
			
			// TOP Products
			$wish_products = new an_wish_products;
			$topProducts = $wish_products->getTopProducts(15);
			
			foreach ($topProducts as $key => $product) {
				 $id_image = Product::getCover($product['id_image']);
				 
				 $image = new Image($id_image);
				 $image = Context::getContext()->link->getImageLink($product['link_rewrite'], $product['id_image'], ImageType::getFormattedName('cart'));
				 $topProducts[$key]['image'] = $image;
			}
			
			$this->context->smarty->assign('topProducts', $topProducts);
			
			$content = $this->display(__FILE__, 'views/templates/admin/products.tpl');
		
		} elseif ($antab == 'customers') {
			
			
			$customers = an_wish::getCustomers();
			foreach ($customers as $key => $item){
				$customers[$key]['countProducts'] = an_wish_products::countProductsWishlistCustomer($item['id_customer']);
			}
					
			$this->context->smarty->assign('customers', $customers);
			
			$content = $this->display(__FILE__, 'views/templates/admin/customers.tpl');
			
			//	Export
			if (Tools::getValue('export')){
				$exportDatas['columns'] = [
					[
						'header' => 'First Name',
						'dataIndex' => 'firstname',
					],
					[
						'header' => 'Last Name',
						'dataIndex' => 'lastname',
					],
					[
						'header' => 'Email',
						'dataIndex' => 'email',
					],					
					[
						'header' => 'Last Wishlist update or visit',
						'dataIndex' => 'anw_date_upd',
					],	
 					[
						'header' => 'In Wishlist',
						'dataIndex' => 'countProducts',
					], 					
				];
				$exportDatas['values'] = $customers;
				$this->csvExport($exportDatas);
			}
			
		} else {
			$antab = false;
		}
		
		//	Settings
		if (!$antab){
			
			$output = null;

			if (Tools::isSubmit('submit'.$this->name)) {
				$output = $this->getSubmitOutput();
			}

			$content = $this->display(__FILE__, 'views/templates/admin/important_to_know.tpl').$output.$this->displayForm();
		}
				
		$top = $this->display(__FILE__, 'views/templates/admin/config_top.tpl');

        return $top.$content;
    }
    
    protected function getSubmitOutput()
    {
        $params = array();
        $paramList = $this->getParamList();

        foreach ($paramList as $key) {
            $params[$key] = Tools::getValue(self::PREFIX.$key);
        }
 
        foreach ($paramList as $key) {
            $this->updateParam($key, $params[$key]);
        }

        return $this->displayConfirmation($this->l('Settings updated'));
    }


    public function displayForm()
    {

        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        $fields_form = array();

        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Settings'),
            ),
            'input' => array(
            
                array(
                    'type' => 'switch',
                    'label' => $this->l('Number of adding to wishlists (product miniature)'),
                    'name' => self::PREFIX.'display_likes_product_mini',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'is_enabled_on',
                            'value' => 1
                        ),
                        array(
                            'id' => 'is_enabled_off',
                            'value' => 0
                        )
                    ),
                ),

                array(
                    'type' => 'switch',
                    'label' => $this->l('Number of adding to wishlists (product page)'),
                    'name' => self::PREFIX.'display_likes_product',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'is_enabled_on',
                            'value' => 1
                        ),
                        array(
                            'id' => 'is_enabled_off',
                            'value' => 0
                        )
                    ),
                ),

                array(
                    'type' => 'switch',
                    'label' => $this->l('Number of items in the wishlist (navigation bar)'),
                    'name' => self::PREFIX.'display_likes_nav',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'is_enabled_on',
                            'value' => 1
                        ),
                        array(
                            'id' => 'is_enabled_off',
                            'value' => 0
                        )
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Wishlist products in the cart'),
                    'name' => self::PREFIX.'display_wishlist_in_cart',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'is_enabled_on',
                            'value' => 1
                        ),
                        array(
                            'id' => 'is_enabled_off',
                            'value' => 0
                        )
                    ),
                ),
		
                array(
                    'type' => 'switch',
                    'label' => $this->l('Guests may use WishList'),
                    'name' => self::PREFIX.'wishlist_for_guests',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'is_enabled_on',
                            'value' => 1
                        ),
                        array(
                            'id' => 'is_enabled_off',
                            'value' => 0
                        )
                    ),
                ),
			
                array(
                    'type' => 'switch',
                    'label' => $this->l('Display social buttons'),
                    'name' => self::PREFIX.'display_social_buttons',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'is_enabled_on',
                            'value' => 1
                        ),
                        array(
                            'id' => 'is_enabled_off',
                            'value' => 0
                        )
                    ),
                ),
                
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            )
        );

        $helper = new HelperForm();

        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        $helper->title = $this->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submit' . $this->name;
        $helper->toolbar_btn = array(
            'save' =>
                array(
                    'desc' => $this->l('Save'),
                    'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
                        '&token='.Tools::getAdminTokenLite('AdminModules'),
                ),
            'back' => array(
                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            )
        );

        $paramList = $this->getParamList();

        foreach ($paramList as $key) {
            $helper->fields_value[self::PREFIX.$key] = $this->getParam($key);
        }

        return $helper->generateForm($fields_form);
    }

    public function hookActionCustomerAccountAdd($params)
    {
        $idCustomer = $this->getIsGuest();
		
		$sql = "UPDATE "._DB_PREFIX_."an_wishlist
        SET `id_customer` =  '" . (int) Context::getContext()->customer->id . "', `is_guest` =  '0'
        WHERE `id_customer` =  '" . (int) $idCustomer . "' AND `is_guest` =  '1' ";
        Db::getInstance()->execute($sql);
    }

	public function getIsGuest(){
 		
		$idCustomer = Context::getContext()->cookie->id_guest;
		
		$cookie = Context::getContext()->cookie;
		
		if ($idCustomer){
			return $idCustomer;
		} else {
			if (!$cookie->__isset('an_id_guest') | $cookie->__get('an_id_guest') == ''){
				$idCustomer = '111' . tools::passwdGen(5, 'NUMERIC');
				$cookie->__set('an_id_guest', $idCustomer);
				$cookie->setExpire(time() + 60 * 60 * 24 * 180);
				$cookie->write();
			
			} else {
				$idCustomer = $cookie->__get('an_id_guest');
			
			}
		}

		return $idCustomer;
	}
	
	public function getThemeInfo()
	{
		$theme = [];
		$themeFileJson = _PS_THEME_DIR_.'/config/theme.json';
		if (Tools::file_exists_no_cache($themeFileJson)) {
			$theme = (array)Tools::jsonDecode(Tools::file_get_contents($themeFileJson), 1);			
		}

		if (!isset($theme['url_contact_us']) || $theme['url_contact_us'] == ''){
			
			$urlContactUs = 'https://addons.prestashop.com/contact-form.php';

			if (isset($theme['addons_id']) && $theme['addons_id'] != ''){
				$urlContactUs .= '?id_product=' .$theme['addons_id'];
			} elseif (isset($this->url_contact_us) && $this->url_contact_us != ''){
				$urlContactUs = $this->url_contact_us;
			} elseif (isset($this->addons_product_id) && $this->addons_product_id != ''){
				$urlContactUs .= '?id_product=' .$this->addons_product_id;
			}
			
			$theme['url_contact_us'] = $urlContactUs;
		}
		
		if (!isset($theme['url_rate']) || $theme['url_rate'] == ''){
			
			$urlRate = 'https://addons.prestashop.com/ratings.php';

			if (isset($theme['addons_id']) && $theme['addons_id'] != ''){
				$urlRate .= '?id_product=' .$theme['addons_id'];
			} elseif (isset($this->url_rate) && $this->url_rate != ''){
				$urlRate = $this->url_rate;
			} elseif (isset($this->addons_product_id) && $this->addons_product_id != ''){
				$urlRate .= '?id_product=' .$this->addons_product_id;
			}
			
			$theme['url_rate'] = $urlRate;
		}		
		
		return $theme;
	}			
}
