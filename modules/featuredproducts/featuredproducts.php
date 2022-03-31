<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(dirname(__FILE__) . '/classes/FeaturedProductsClass.php');
require_once(dirname(__FILE__) . '/classes/SliderProduct.php');

class featuredproducts extends Module
{
    private $_featuredProductsClass;
    private $language_id;
    private $shop_id;

    public function __construct()
    {
        $this->name = 'featuredproducts';
        $this->tab = 'front_office_features';
        $this->version = '3.0.5';
        $this->author = 'MyPrestaModules';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->bootstrap = true;
        $this->displayName = $this->l('Featured products slider');
        $this->module_key = "7f3e95f552ff8808e37e416bda29ef22";
        $this->description = $this->l('Featured products slider on Home, Category and Product pages is an easy way to increase your sales.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        parent::__construct();

        $this->_featuredProductsClass = new FeaturedProductsClass();
        $this->language_id = Context::getContext()->language->id;
        $this->shop_id = Context::getContext()->shop->id;
    }

    public function install()
    {
        if (!parent::install() || !$this->registerHooks()) {
            return false;
        }

        $this->_createTab('AdminFeaturedProducts', 'Featured products slider');
        FeaturedProductsClass::createTables();

        return true;
    }

    private function registerHooks()
    {
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $hooks_for_registration = array(
                'header',
                'actionAdminControllerSetMedia',
                'displayWrapperTop',
                'displayWrapperBottom',
                'displayFooterBefore',
                'displayFooterProduct',
                'displayNavFullWidth',
                'displayTop',
                'displayHome',
                'displayContentWrapperTop',
                'displayContentWrapperBottom',
                'displayLeftColumn',
                'displayRightColumn',
            );
        } else {
            $hooks_for_registration = array(
                'header',
                'actionAdminControllerSetMedia',
                'displayTop',
                'displayTopColumn',
                'displayHome',
                'displayHomeTab',
                'displayFooterProduct',
                'displayLeftColumn',
                'displayRightColumn',
            );
        }

        if (!$this->registerHook($hooks_for_registration)) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }

        $this->_removeTab('AdminFeaturedProducts');
        FeaturedProductsClass::dropTables();

        return true;
    }

    private function _createTab($class_name, $name)
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = $class_name;
        $tab->name = array();

        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $name;
        }

        $tab->id_parent = -1;
        $tab->module = $this->name;
        $tab->add();
    }

    private function _removeTab($class_name)
    {
        $id_tab = (int)Tab::getIdFromClassName($class_name);
        if ($id_tab) {
            $tab = new Tab($id_tab);
            $tab->delete();
        }
    }

    public function upgradeModuleTo_3_0_5()
    {
        $this->registerHook('actionAdminControllerSetMedia');

        return true;
    }

    public function upgradeModuleTo_3_0_3()
    {
        $new_columns = array(
            'show_in_specific_categories'        => array('complete_name' => '`show_in_specific_categories` int(11) NULL', 'table' => 'featuredproducts'),
            'specific_categories'     => array('complete_name' => '`specific_categories` TEXT NULL', 'table' => 'featuredproducts'),
        );

        if (!$this->addNewColumnsToDbTables($new_columns)) {
            return false;
        }

        return true;
    }

    public function upgradeModuleTo_3_0_0()
    {
        $sliders_data = FeaturedProductsClass::getSlidersRestructuredForInsertionInSeparateRows();

        FeaturedProductsClass::dropTables();
        FeaturedProductsClass::createTables();
        $this->registerHooks();

        if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
            $this->_createTab('AdminFeaturedProducts', 'Featured products slider');
            $this->uninstallOverrides();
        }

        $this->removeRedundantFilesAndFolders();

        if ($sliders_data) {
            foreach ($sliders_data as $slider) {
                $slider_data_obj = new FeaturedProductsClass();

                foreach ($slider as $property_name => $property_val) {
                    if (property_exists($slider_data_obj, $property_name)) {
                        $slider_data_obj->$property_name = $property_val;
                    }
                }

                $slider_data_obj->add();
            }
        }

        return true;
    }

    private function removeRedundantFilesAndFolders()
    {
        $folders_to_remove = array('override', 'views/templates/admin');
        $files_to_remove = array('datamodel.php',
            'send.php',
            'logo.gif',
            'readme_en.pdf',
            'Readme.md',
            'views/templates/hook/categoryslider.tpl',
            'views/templates/hook/columslidercat.tpl',
            'views/templates/hook/columsliderhome.tpl',
            'views/templates/hook/columsliderprod.tpl',
            'views/templates/hook/homeslider.tpl',
            'views/templates/hook/pageProducts.tpl',
            'views/templates/hook/productslider.tpl',
            'views/css/homeslider.css',
            'views/css/jquery.bxslider.css',
            'views/css/jcarousel.responsive.css',
            'views/js/homepage.js',
            'views/js/jquery.bxslider.js',
            'views/js/slider.js',
            'views/js/jcarousel.responsive.js',
            'views/js/jquery.jcarousel.js',
            'views/js/jquery.jcarousel-autoscroll.js',
            'views/js/jquery.jcarousel-control.js',
            'views/js/jquery.jcarousel-core.js',
            'views/js/jquery.jcarousel-pagination.js',
            'views/js/jquery.jcarousel-scrollintoview.js');

        foreach ($folders_to_remove as $folder) {
            Tools::deleteDirectory($this->getLocalPath() . $folder);
        }

        foreach ($files_to_remove as $file) {
            Tools::deleteFile($this->getLocalPath() . $file);
        }
    }

    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminFeaturedProducts') . '&updatefeaturedproducts');
    }

    public function isUsingNewTranslationSystem()
    {
        return false;
    }

    public function hookHeader($params)
    {
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $this->context->controller->registerJavascript('featuredproducts', 'modules/featuredproducts/views/js/featuredproducts.js', array('media' => 'all', 'position' => 'bottom', 'priority' => 150));
            $this->context->controller->registerJavascript('slick_js', 'modules/featuredproducts/libraries/slick/slick.js', array('media' => 'all', 'position' => 'bottom', 'priority' => 150));
            $this->context->controller->registerStylesheet('featuredproducts', 'modules/featuredproducts/views/css/myprestamodules-fonts.css', array('media' => 'all', 'priority' => 150));
            $this->context->controller->registerStylesheet('featuredproducts_product_miniature', 'modules/featuredproducts/views/css/featuredproducts_product_miniature.css', array('media' => 'all', 'priority' => 150));
            $this->context->controller->registerStylesheet('featuredproducts_slider', 'modules/featuredproducts/views/css/featuredproducts_slider.css', array('media' => 'all', 'priority' => 150));
            $this->context->controller->registerStylesheet('slick_css', 'modules/featuredproducts/libraries/slick/slick.css', array('media' => 'all', 'priority' => 150));
        } else {
            $this->context->controller->addJS(_PS_MODULE_DIR_ . '/featuredproducts/views/js/featuredproducts.js');
            $this->context->controller->addJS(_PS_MODULE_DIR_ . '/featuredproducts/libraries/slick/slick.js');
            $this->context->controller->addCSS(_PS_MODULE_DIR_ . '/featuredproducts/views/css/myprestamodules-fonts.css');
            $this->context->controller->addCSS(_PS_MODULE_DIR_ . '/featuredproducts/views/css/featuredproducts_product_miniature.css');
            $this->context->controller->addCSS(_PS_MODULE_DIR_ . '/featuredproducts/views/css/featuredproducts_slider.css');
            $this->context->controller->addCSS(_PS_MODULE_DIR_ . '/featuredproducts/libraries/slick/slick.css');
        }

        $id_product = (int)Tools::getValue('id_product');
        $productsViewed = explode(',', $params['cookie']->viewed_slider);

        if (!in_array($id_product, $productsViewed)) {
            $product = new Product((int)$id_product);
            if ($product->checkAccess((int)$this->context->customer->id)) {
                $productsViewed[] = $id_product;
                $params['cookie']->viewed_slider = implode(',', $productsViewed);
            }
        }
    }

    public function hookActionAdminControllerSetMedia()
    {
        $controller = Dispatcher::getInstance()->getController();
        $is_module_controller = ($controller == 'AdminFeaturedProducts');

        if ($is_module_controller) {
            $this->context->controller->addCSS(array(
                _PS_MODULE_DIR_ . 'featuredproducts/views/css/featuredproducts_admin.css',
            ));

            $this->context->controller->addjQueryPlugin(array(
                'select2',
            ));

            $this->context->controller->addJS(array(
                _PS_MODULE_DIR_ . 'featuredproducts/views/js/featuredproducts_admin.js',
                _PS_MODULE_DIR_ . 'featuredproducts/views/js/featuredproducts_admin.js',
            ));
        }
    }

    public function hookDisplayWrapperTop()
    {
        return $this->getSliders('displayWrapperTop');
    }

    public function hookDisplayWrapperBottom()
    {
        return $this->getSliders('displayWrapperBottom');
    }

    public function hookDisplayFooterBefore()
    {
        return $this->getSliders('displayFooterBefore');
    }

    public function hookDisplayFooterProduct()
    {
        return $this->getSliders('displayFooterProduct');
    }

    public function hookDisplayNavFullWidth()
    {
        return $this->getSliders('displayNavFullWidth');
    }

    public function hookDisplayTop()
    {
        return $this->getSliders('displayTop');
    }

    public function hookDisplayContentWrapperTop()
    {
        return $this->getSliders('displayContentWrapperTop');
    }

    public function hookDisplayContentWrapperBottom()
    {
        return $this->getSliders('displayContentWrapperBottom');
    }

    public function hookDisplayTopColumn()
    {
        return $this->getSliders('displayTopColumn');
    }

    public function hookDisplayHome()
    {
        return $this->getSliders('displayHome');
    }

    public function hookDisplayHomeTab()
    {
        return $this->getSliders('displayHomeTab');
    }

    public function hookDisplayLeftColumn()
    {
        return $this->getSliders('displayLeftColumn');
    }

    public function hookDisplayRightColumn()
    {
        return $this->getSliders('hookDisplayRightColumn');
    }

    private function getSliders($hook_name)
    {
        $page_type = $this->getCurrentPageType();
        $sliders = FeaturedProductsClass::getSlidersByDisplayPageAndHook($page_type, $hook_name, $this->language_id, $this->shop_id);

        if (!$sliders) {
            return false;
        }

        $sliders_templates = '';

        foreach ($sliders as $slider) {
            if ($page_type == 'category' && !empty(Tools::getValue('id_category'))) {
                if (!$this->allowedToShowSliderInCurrentCategory($slider, Tools::getValue('id_category'))) {
                    continue;
                }
            }

            $sliders_templates .= $this->getSliderTpl($slider);
        }

        return $sliders_templates;
    }

    private function getCurrentPageType()
    {
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $current_page_id = Context::getContext()->controller->getPageName();
        } else {
            $current_page_id = Context::getContext()->controller->php_self;
        }

        switch ($current_page_id) {
            case 'index':
                return 'home';
            case 'category':
                return 'category';
            case 'product':
                return 'product';
        }
    }

    public function getSliderTpl(FeaturedProductsClass $slider_object)
    {
        if (!$slider_object->active) {
            return false;
        }

        $type = $slider_object->type_products_show;
        $productIds = $slider_object->productIds;
        $catIds = $slider_object->catIds;
        $order = $slider_object->order_by;
        $limit = $slider_object->total_number_of_slides;

        $id_category = false;

        if ($slider_object->display_page === 'category') {
            $id_category = Tools::getValue('id_category');

            if (!$id_category) {
                return false;
            }
        } else if ($slider_object->display_page === 'product') {
            $id_product = Tools::getValue('id_product');
            $current_product_category = $this->context->controller->getCategory();
            $id_category = $current_product_category->id;

            if (!$id_category || !$id_product) {
                return false;
            }
        }

        $ids_of_products_to_be_displayed_in_slider = SliderProduct::getIdsOfProductsForSlider($type, $productIds, $catIds, $id_category, $limit);

        if (!$ids_of_products_to_be_displayed_in_slider) {
            return false;
        }

        if ($slider_object->display_page === 'product') {
            $ids_of_products_to_be_displayed_in_slider = $this->removeCurrentProductFromSlider($id_product, $ids_of_products_to_be_displayed_in_slider);
        }

        $path_to_product_template = './product.tpl';

        if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
            $path_to_product_template = './product16.tpl';
        }

        $this->context->smarty->assign(
            array(
                'settings'            => $slider_object,
                'page_slider'         => $slider_object->display_page,
                'products'            => $this->getPresentedForSliderProducts($ids_of_products_to_be_displayed_in_slider, $order, $limit),
                'ps_version'          => _PS_VERSION_,
                'path_to_product_min' => $path_to_product_template,
            )
        );

        return $this->display(__FILE__, 'views/templates/hook/slider.tpl');
    }

    private function allowedToShowSliderInCurrentCategory(FeaturedProductsClass $slider, $category_id) {
        if ($slider->show_in_specific_categories && !empty($slider->specific_categories)) {
            if (in_array($category_id, explode(',', $slider->specific_categories))) {
                return true;
            }
        } else {
            return true;
        }

        return false;
    }

    private function removeCurrentProductFromSlider($id_product, $slider_products_ids)
    {
        $exploded_slider_products_ids = explode(',', $slider_products_ids);
        $key_of_current_product = array_search($id_product, $exploded_slider_products_ids);
        unset($exploded_slider_products_ids[$key_of_current_product]);

        return implode(',', $exploded_slider_products_ids);
    }

    /**
     * Assemble product to be properly presented in slider
     * @param $product_ids
     * @param $order_by
     * @param $limit
     * @return array
     */
    private function getPresentedForSliderProducts($product_ids, $order_by, $limit)
    {
        $products = SliderProduct::getProductsByIds($product_ids, $order_by, $limit);

        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $assembler = new ProductAssembler($this->context);
            $presenterFactory = new ProductPresenterFactory($this->context);
            $presentationSettings = $presenterFactory->getPresentationSettings();
            $presenter = new PrestaShop\PrestaShop\Core\Product\ProductListingPresenter(
                new PrestaShop\PrestaShop\Adapter\Image\ImageRetriever(
                    $this->context->link
                ),
                $this->context->link,
                new PrestaShop\PrestaShop\Adapter\Product\PriceFormatter(),
                new PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever(),
                $this->context->getTranslator()
            );

            $array_result = array();
            foreach ($products as $prow) {
                $array_result[] = $presenter->present(
                    $presentationSettings,
                    $assembler->assembleProduct($prow),
                    $this->context->language
                );
            }

            return $array_result;
        } else {
            $products_properties = Product::getProductsProperties($this->context->language->id, $products);

            foreach ($products_properties as $product_key => $product) {
                $product_data_obj = new Product($product['id_product'], true);
                $products_properties[$product_key]['new'] = $product_data_obj->new;
            }

            $front_controller = new FrontController();
            $front_controller->addColorsToProductList($products_properties);

            return $products_properties;
        }
    }

    public function checkIfColumnExists($col_name, $table_name)
    {
        $check_query = "SELECT NULL
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE table_name = '" . _DB_PREFIX_ . $table_name . "'
            AND table_schema = '" . _DB_NAME_ . "'
            AND column_name = '" . $col_name . "'
        ";

        if (!Db::getInstance()->executeS($check_query)) {
            return false;
        }

        return true;
    }

    private function addNewColumnsToDbTables($new_columns)
    {
        foreach ($new_columns as $column_short_name => $column_details) {
            $table_name = $new_columns[$column_short_name]['table'];
            $column_complete_name = $new_columns[$column_short_name]['complete_name'];

            if (!$this->checkIfColumnExists($column_short_name, $table_name)) {
                $alter_table = 'ALTER TABLE ' . _DB_PREFIX_ . $table_name . ' ADD COLUMN ' . $column_complete_name;

                if (!Db::getInstance()->execute($alter_table)) {
                    return false;
                }
            }
        }

        return true;
    }
}




