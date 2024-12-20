<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
//use Symfony\Component\HttpFoundation\Request;
//use PrestaShopBundle\Controller\Admin\ProductController;


class Khewabackend extends Module
{
    public function __construct()
    {
        $this->name = 'khewabackend';
        $this->tab = 'administration';
        $this->version = '1.0.1';
        $this->author = 'Masudur Rahman';
        $this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->l('Khewa Backend');
        $this->description = $this->l('Adds language switcher for employees on product edit page.');

        $this->ps_versions_compliancy = array('min' => '1.7.8.0', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        return parent::install()
//            && $this->registerHook('displayAdminProductsMainStepLeftColumnMiddle')
            && $this->registerHook('actionAdminControllerInitBefore')
            && $this->registerHook('displayBackOfficeTop')
            ;
    }

    public function uninstall()
    {
        return parent::uninstall();
    }
    private function getSymfonyContainer()
    {
        return SymfonyContainer::getInstance();
    }

    private function getProductIdFromRequest()
    {
        $container = $this->getSymfonyContainer();
        if (!$container) {
            return null;
        }

        $request = $container->get('request_stack')->getCurrentRequest();
        if (!$request) {
            return null;
        }

        // Get product ID from different possible sources
        $productId = $request->attributes->get('id');

        if (!$productId) {
            $productId = $request->query->get('id_product');
        }

        if (!$productId && $request->attributes->get('_route') === 'admin_product_form') {
            $productId = $request->attributes->get('id_product');
        }

        return $productId;
    }

    private function isProductEditPage()
    {
        $container = $this->getSymfonyContainer();
        if (!$container) {
            return false;
        }

        $request = $container->get('request_stack')->getCurrentRequest();
        if (!$request) {
            return false;
        }

        // Check if we're on the product edit page
        $route = $request->attributes->get('_route');
        return in_array($route, [
            'admin_product_form',
            'admin_products_edit'
        ]);
    }

    private function getAdminProductLink($productId, $params = [])
    {
        $container = $this->getSymfonyContainer();
        if (!$container) {
            return '';
        }

        try {
            $router = $container->get('router');
            $params['id'] = $productId;

            return $router->generate('admin_product_form', $params);
        } catch (Exception $e) {
            return '';
        }
    }

    public function hookDisplayBackOfficeTop($params)
    {
        // Check if we're in product edit page
        if (!$this->isProductEditPage()) {
            return '';
        }

        $productId = $this->getProductIdFromRequest();
        if (!$productId) {
            return '';
        }

        $currentLangId = $this->context->employee->id_lang;
        $newLangId = ($currentLangId == 1) ? 2 : 1; // Toggle between English (1) and French (2)

        // Generate URL using Symfony router
        $switchUrl = $this->getAdminProductLink($productId, [
            'switch_employee_lang' => $newLangId
        ]);

        if (!$switchUrl) {
            return '';
        }

        $currentLang = ($currentLangId == 1) ? 'English' : 'French';
        $newLang = ($newLangId == 1) ? 'English' : 'French';

        $this->context->smarty->assign([
            'switch_url' => $switchUrl,
            'current_lang' => $currentLang,
            'new_lang' => $newLang
        ]);

        return $this->display(__FILE__, 'views/templates/hook/language_switcher.tpl');
    }

    public function hookActionAdminControllerInitBefore($params)
    {

        // Check if we're in product edit page
        if (!$this->isProductEditPage()) {
            return '';
        }

        $switchLang = Tools::getValue('switch_employee_lang');
        if ($switchLang && in_array($switchLang, [1, 2])) {
            $employee = $this->context->employee;
            $employee->id_lang = (int)$switchLang;
            $employee->update();

            // Force reload of the page to apply new language


//            Tools::redirectAdmin($this->context->link->getAdminLink('AdminProducts', true, [
//                'id_product' => Tools::getValue('id_product'),
//                'updateproduct' => 1
//            ]));

            $productId = $this->getProductIdFromRequest();

            Tools::redirectAdmin($this->getAdminProductLink($productId, []));


        }
    }
}
