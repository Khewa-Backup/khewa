<?php 

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;

if (!defined('_PS_VERSION_')) {
    exit;
}

class NewProdSlider extends Module implements WidgetInterface
{
    public function __construct()
    {
        $this->name = 'newprodslider';
        $this->author = 'prestashoot';
        $this->version = '1.0.0';
        $this->need_instance = 0;

        $this->ps_versions_compliancy = array(
            'min' => '1.7.0.0',
            'max' => _PS_VERSION_,
        );

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->trans('New products slider', array(), 'Modules.Newproducts.Admin');
        $this->description = $this->trans('Displays a slider block featuring your store\'s newest products.', array(), 'Modules.Newproducts.Admin');

        $this->templateFile = 'module:'.$this->name.'/views/templates/hook/home.tpl';
    }
    public function install()
    {
        // $this->_clearCache('*');

        return parent::install()
            
            && $this->registerHook('header')
            && $this->registerHook('displayHome')
        ;
    }

    public function hookHeader()
    {
        $this->context->controller->registerStylesheet('owlslider', $this->getPathUri().'views/css/owl.carousel.min.css', ['media' => 'all', 'priority' => 100]);
        $this->context->controller->registerJavascript('owlslider', $this->getPathUri().'views/js/owl.carousel.min.js', ['position' => 'bottom', 'priority' => 150]);

        // $this->context->controller->addCSS($this->getPathUri().'views/css/owl.carousel.min.css');
        // $this->context->controller->addJS($this->getPathUri().'views/js/owl.carousel.min.js');
    }

    public function renderWidget($hookName, array $configuration)
    {
        // if (!$this->isCached($this->templateFile, $this->getCacheId('ps_newproducts'))) {
            $variables = $this->getWidgetVariables($hookName, $configuration);

            if (empty($variables)) {
                return false;
            }

            $this->smarty->assign($variables);
        // }

        return $this->fetch($this->templateFile, $this->getCacheId('ps_newproducts'));
    }

    public function getWidgetVariables($hookName, array $configuration)
    {
        $products = $this->getNewProducts();

        if (!empty($products)) {
            return array(
                'products' => $products,
                'allNewProductsLink' => Context::getContext()->link->getPageLink('new-products'),
            );
        }
        return false;
    }
    protected function getNewProducts()
    {
        $newProducts = false;

        if (Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) {
            $newProducts = Product::getNewProducts(
                (int) $this->context->language->id,
                0,
                (int) Configuration::get('NEW_PRODUCTS_NBR')
            );
        }
        
        $assembler = new ProductAssembler($this->context);

        $presenterFactory = new ProductPresenterFactory($this->context);
        $presentationSettings = $presenterFactory->getPresentationSettings();
        $presenter = new ProductListingPresenter(
            new ImageRetriever(
                $this->context->link
            ),
            $this->context->link,
            new PriceFormatter(),
            new ProductColorsRetriever(),
            $this->context->getTranslator()
        );

        $products_for_template = [];

        if (is_array($newProducts)) {
            foreach ($newProducts as $rawProduct) {
                $products_for_template[] = $presenter->present(
                    $presentationSettings,
                    $assembler->assembleProduct($rawProduct),
                    $this->context->language
                );
            }
        }

        return $products_for_template;
    }
}