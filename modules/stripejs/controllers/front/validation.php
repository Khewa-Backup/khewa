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

class stripejsValidationModuleFrontController extends ModuleFrontController
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
		if (Configuration::get('PS_SSL_ENABLED')) {
            $domain = Tools::getShopDomainSsl(true);
        } else {
            $domain = Tools::getShopDomain(true);
        }
		
        Context::getContext()->smarty->assign(array(
            'stripe_source' => Tools::getValue('source'),
			'stripe_source_type' => Tools::getValue('source_type'),
            'stripe_client_secret' => Tools::getValue('client_secret'),
            'publishableKey' => Configuration::get('STRIPE_MODE') ? Configuration::get('STRIPE_PUBLIC_KEY_LIVE') : Configuration::get('STRIPE_PUBLIC_KEY_TEST'),
            'module_dir' => _PS_MODULE_DIR_,
            'order_page' => $this->context->link->getPageLink('order', true),
			'baseDir' => $domain.__PS_BASE_URI__,
        ));
        Context::getContext()->controller->registerJavascript('stripejs-payment_validation', 'modules/stripejs/views/js/payment_validation.js');

        $this->setTemplate('module:stripejs/views/templates/hook/payment_validation.tpl');

    }
}