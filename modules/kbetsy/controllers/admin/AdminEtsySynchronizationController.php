<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 * We offer the best and most useful modules PrestaShop and modifications for your online store.
 *
 * @author    knowband.com <support@knowband.com>
 * @copyright 2017 Knowband
 * @license   see file: LICENSE.txt
 * @category  PrestaShop Module
 */

//Include Etsy Module Class to inherit some common functions and callbacks
require_once(_PS_MODULE_DIR_ . 'kbetsy/classes/EtsyModule.php');

class AdminEtsySynchronizationController extends ModuleAdminController
{

    //Class Constructor
    public function __construct()
    {
        $this->name = 'EtsySynchronization';
        $this->context = Context::getContext();
        $this->bootstrap = true;
        $this->display = 'view';

        parent::__construct();
    }

    //Set JS and CSS
    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $this->addJS($this->getModuleDirUrl() . 'kbetsy/views/js/script.js');
        $this->addCSS($this->getModuleDirUrl() . 'kbetsy/views/css/style.css');
    }

    public function initPageHeaderToolbar()
    {
        $this->page_header_toolbar_title = $this->l('Etsy Synchronization');
        parent::initPageHeaderToolbar();
    }

    public function renderView()
    {
        $secure_key = Configuration::get('KBETSY_SECURE_KEY');
        $this->context->smarty->assign(array(
            'sync_shipping_templates_url' => $this->context->link->getModuleLink('kbetsy', 'cron', array('action' => 'syncShippingTemplates', 'secure_key' => $secure_key)),
            'sync_countries_regions_url' => $this->context->link->getModuleLink('kbetsy', 'cron', array('action' => 'syncCountriesRegions', 'secure_key' => $secure_key)),
            'sync_products_listing_url' => $this->context->link->getModuleLink('kbetsy', 'cron', array('action' => 'syncProductsListing', 'secure_key' => $secure_key)),
            'sync_variations_listing_url' => $this->context->link->getModuleLink('kbetsy', 'cron', array('action' => 'syncVariationsListing', 'secure_key' => $secure_key)),
            'sync_orders_listing_url' => $this->context->link->getModuleLink('kbetsy', 'cron', array('action' => 'syncOrdersListing', 'secure_key' => $secure_key)),
            'sync_orders_status_url' => $this->context->link->getModuleLink('kbetsy', 'cron', array('action' => 'syncOrdersStatus', 'secure_key' => $secure_key)),
            'sync_translations_url' => $this->context->link->getModuleLink('kbetsy', 'cron', array('action' => 'syncTranslations', 'secure_key' => $secure_key)),
            'sync_queue_url' => $this->context->link->getModuleLink('kbetsy', 'cron', array('action' => 'syncQueue', 'secure_key' => $secure_key)),
            'reset_queue_url' => $this->context->link->getModuleLink('kbetsy', 'cron', array('action' => 'resetQueue', 'secure_key' => $secure_key)),
            'upload_image_url' => $this->context->link->getModuleLink('kbetsy', 'cron', array('action' => 'uploadProductImage', 'secure_key' => $secure_key)),
            'sync_update_products_listing_url' => $this->context->link->getModuleLink('kbetsy', 'cron', array('action' => 'syncUpdateProductsListing', 'secure_key' => $secure_key)),
            'sync_renew_products_listing_url' => $this->context->link->getModuleLink('kbetsy', 'cron', array('action' => 'syncRenewProductsListing', 'secure_key' => $secure_key)),
            'sync_delete_products_listing_url' => $this->context->link->getModuleLink('kbetsy', 'cron', array('action' => 'syncDeleteProductsListing', 'secure_key' => $secure_key)),
            'sync_products_listing_status_url' => $this->context->link->getModuleLink('kbetsy', 'cron', array('action' => 'syncProductsListingStatus', 'secure_key' => $secure_key)),
//            'sync_product_quantity_url' => $this->context->link->getModuleLink('kbetsy', 'cron', array('action' => 'syncProductQunatity', 'secure_key' => $secure_key)),
        ));

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbetsy/views/templates/admin/synchronization.tpl');
    }

    private function getModuleDirUrl()
    {
        $module_dir = '';
        if ($this->checkSecureUrl()) {
            $module_dir = _PS_BASE_URL_SSL_ . __PS_BASE_URI__ . str_replace(_PS_ROOT_DIR_ . '/', '', _PS_MODULE_DIR_);
        } else {
            $module_dir = _PS_BASE_URL_ . __PS_BASE_URI__ . str_replace(_PS_ROOT_DIR_ . '/', '', _PS_MODULE_DIR_);
        }
        return $module_dir;
    }

    private function checkSecureUrl()
    {
        $custom_ssl_var = 0;

        if (isset($_SERVER['HTTPS'])) {
            if ($_SERVER['HTTPS'] == 'on') {
                $custom_ssl_var = 1;
            }
        } else if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            $custom_ssl_var = 1;
        }



        if ((bool) Configuration::get('PS_SSL_ENABLED') && $custom_ssl_var == 1) {
            return true;
        } else {
            return false;
        }
    }
}
