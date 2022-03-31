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

class an_wishlistlistModuleFrontController extends ModuleFrontController
{

    const PREFIX = "an_wishlist_";

    public function __construct()
    {
        if (!Module::getInstanceByName('an_wishlist')->getParam('wishlist_for_guests') && !Tools::getValue('wishlist')) { 
			$this->auth = true;
		}
		
        parent::__construct();
    }

    public function initContent()
    {
        parent::initContent();	
		
        if (Context::getContext()->customer->isLogged()) {
            $idCustomer = (int) Context::getContext()->customer->id;
            $is_guest = 0;
        } else {
            $is_guest = 1;
            $idCustomer = $this->module->getIsGuest();
        }		
		
		$idWish = an_wish::findWishlistByCustomer($idCustomer, $is_guest);
		
		if (Tools::getValue('wishlist')){
			$idWish = (int)Tools::getValue('wishlist');
		} else {
			//date_last_visit
			$an_wish = new an_wish($idWish);
			$an_wish->update();
			
		}
		
        $products = an_wish_products::getProductsWishlist((int) $idWish);
		
		// Create to correct links
		foreach ($products as $key => $product){
			$products[$key]['link'] = Context::getContext()->link->getProductLink(
				(int) $product['id_product'], 
				$product['link_rewrite'], 
				$product['category'], 
				$product['ean13'],
				null,
				null,
				$product['id_product_attribute']
				);
		}
		
        $listing = new an_wishListing();
        $products =  $listing->prepare($products);
		
 		$wishlistLink = Context::getContext()->link->getModuleLink(
			'an_wishlist', 
			'list', 
			[
				'wishlist' => $idWish, 
			],
			true
		); 		
		
		$this->setTemplate("module:an_wishlist/views/templates/front/list.tpl");
		
        
		$this->context->smarty->assign('wishlistLink', $wishlistLink);
        $this->context->smarty->assign('products', $products);
        $this->context->smarty->assign('config', Module::getInstanceByName('an_wishlist')->getConfig());
    }
	
    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();

        $breadcrumb['links'][] = $this->addMyAccountToBreadcrumb();
		$breadcrumb['links'][] = [
            'title' => $this->l('My wishlist'),
            'url' => $this->context->link->getModuleLink('an_wishlist', 'list', array(), true),
        ];
		
        return $breadcrumb;
    }
	
	
/*     public function getTemplateVarPage()
    {
        $page = parent::getTemplateVarPage();

        $page['meta']['title'] = Configuration::get(self::PREFIX.'meta_title', $this->context->language->id);
        $page['meta']['keywords'] = Configuration::get(self::PREFIX.'meta_descr', $this->context->language->id);
        $page['meta']['description'] = Configuration::get(self::PREFIX.'meta_key', $this->context->language->id);
        return $page;
    } */
}
