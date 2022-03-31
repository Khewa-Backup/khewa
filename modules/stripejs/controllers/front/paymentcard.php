<?php
/**
 * 2007-2018 PrestaShop
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2018 PrestaShop SA
 * @license   http://addons.prestashop.com/en/content/12-terms-and-conditions-of-use
 * International Registered Trademark & Property of PrestaShop SA
 */

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

class stripejsPaymentCardModuleFrontController extends ModuleFrontControllerCore
{

    public function __construct()
    {
        parent::__construct();
        $this->ssl = true;
    }

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();
		        
        if (!$this->module->active || !$this->module->checkCurrency($this->context->cart) || !$this->context->cart->nbProducts() || !$this->context->cart->id_address_invoice || $this->context->cart->delivery_option=='') {
            return;
        }

       $paymentOptionsFinder    = new PaymentOptionsFinder();
       $payment_options         = $paymentOptionsFinder->present();
        if (!$payment_options) {
            return false;
        }
		
        $this->context->smarty->assign(array(
            'name_module' => 'stripejs',
            'payment_options' => $payment_options['stripejs'],
            'language' => (array)$this->context->language
        ));

        $this->setTemplate('module:stripejs/views/templates/front/paymentcard.tpl');
    }
}
