<?php
/**
* 2022 Anvanto
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
*
*  @author    Anvanto <anvantoco@gmail.com>
*  @copyright 2022 Anvanto
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/
 
class an_wishlistajaxModuleFrontController extends ModuleFrontController
{
    
    public function initContent()
    {
        $result = array();
        if (Tools::isSubmit('action')) {
            $actionName = Tools::getValue('action', '') . 'Action';
            if (method_exists($this, $actionName)) {
                $result = $this->$actionName();
            }
        }

        die(json_encode($result));
    }
    
    public function addRemoveAction()
    {
        if (Configuration::get('PS_TOKEN_ENABLE') && Tools::getValue('token') != Tools::getToken(false)) {
            Tools::redirect('index.php?controller=404');
        }
        
        $return = array();
        

        if (!$this->module->getParam('wishlist_for_guests') && !Context::getContext()->customer->isLogged()) {
            $return['error'] = 'notLogged';
            $this->context->smarty->assign('myAccount', Context::getContext()->link->getPageLink('my-account', null));
            $return['modal'] = $this->module->display($this->module->name, 'modal.tpl');
            $this->ajaxDie(json_encode($return));
        }
		


        $idProduct = (int) Tools::getValue('id_product');
        $id_product_attribute = (int) Tools::getValue('id_product_attribute');
        if (Context::getContext()->customer->isLogged()) {
            $idCustomer = (int) Context::getContext()->customer->id;
            $is_guest = 0;
        } else {
            $is_guest = 1;
            $idCustomer = $this->module->getIsGuest();
        }
		
		$idWish = an_wish::findWishlistByCustomer($idCustomer, $is_guest);
		
		
		
		if (!$idWish) {
			$an_wish = new an_wish;
            $an_wish->id_customer = $idCustomer;
            $an_wish->is_guest = $is_guest;
            $an_wish->add();	

			$idWish = an_wish::findWishlistByCustomer($idCustomer, $is_guest);

		} else {
			$an_wish = new an_wish($idWish);
			$an_wish->update();
		}
		

        
		
		$an_wish_products = new an_wish_products;
        
        if (an_wish_products::issetItem($idProduct, $idWish, $id_product_attribute)) {
            //  Delete
            $an_wish_products->removeItem($idProduct, $idWish, $id_product_attribute);
            $return['status'] = 0;
        } else {
            //  Add
            $an_wish_products->id_wishlist = $idWish;
            $an_wish_products->id_product = $idProduct;
			if ($id_product_attribute){
				$an_wish_products->id_product_attribute = $id_product_attribute;
			}
            $an_wish_products->add();
            $return['status'] = 1;
        }
        
        $return['count'] = an_wish_products::countProductsWishlist($idWish);
        $return['countWishlists'] = an_wish_products::countProductsAllWishlists($idProduct);

        $this->ajaxDie(json_encode($return));
    }	
}
