<?php

require_once(dirname(__FILE__) . '/../../classes/FeaturedProductsClass.php');

class AdminFeaturedProductsController extends ModuleAdminController
{
    private $_idShop;
    private $_idLang;

    protected $position_identifier = 'id_featuredproducts';

    public function __construct()
    {
        $this->className = 'FeaturedProductsClass';
        $this->table = 'featuredproducts';
        $this->bootstrap = true;
        $this->lang = true;
        $this->edit = true;
        $this->delete = true;
        parent::__construct();
        $this->multishop_context = -1;
        $this->multishop_context_group = true;
        $this->position_identifier = 'id_featuredproducts';
        $this->show_form_cancel_button = true;
        $this->_idShop = Context::getContext()->shop->id;
        $this->_idLang = Context::getContext()->language->id;

        Shop::addTableAssociation('featuredproducts', array('type' => 'shop'));

        $this->_join .= 'LEFT JOIN `' . _DB_PREFIX_ . 'featuredproducts_shop` sa ON (a.`id_featuredproducts` = sa.`id_featuredproducts`)';
        $this->_select .= 'sa.*';
        $this->_where .= 'AND sa.id_shop IN(' . implode(',', Shop::getContextListShopID()) . ') GROUP BY sa.id_featuredproducts';

        $this->fields_list = array(
            'id_featuredproducts' => array(
                'title'      => $this->l('ID'),
                'search'     => true,
                'onclick'    => false,
                'filter_key' => 'a!id_featuredproducts',
                'width'      => 20
            ),
            'title'               => array(
                'title'      => $this->l('Title'),
                'width'      => 100,
                'filter_key' => 'b!title',
                'orderby'    => true
            ),
            'display_page'        => array(
                'title'   => $this->l('Display Page'),
                'width'   => 100,
                'orderby' => true
            ),
            'display_hook'        => array(
                'title'   => $this->l('Display Hook'),
                'width'   => 100,
                'orderby' => true
            ),
            'active'              => array(
                'title'      => $this->l('Active'),
                'active'     => 'status',
                'filter_key' => 'sa!active',
                'align'      => 'center',
                'type'       => 'bool',
                'width'      => 70,
                'orderby'    => false
            )
        );
    }

    public function init()
    {
        parent::init();
    }

    public function initProcess()
    {
        parent::initProcess();
    }

    public function initContent()
    {
        parent::initContent();
    }

    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        return parent::renderList();
    }

    public function postProcess()
    {
        return parent::postProcess();
    }

    /**
     * Must be overrided for correct work of multishop
     *
     * @param int $id_object
     * @return bool
     */
    protected function updateAssoShop($id_object)
    {
        return true;
    }

    public function renderForm()
    {

        $obj = new FeaturedProductsClass(Tools::getValue('id_featuredproducts'));
        $productIds = $obj->productIds;
        $catIds = explode(",", $obj->catIds);

        $categories = new HelperTreeCategories('associated-categories-home-tree');
        $categories->setUseCheckBox(1)->setUseSearch(1);
        $categories->setSelectedCategories($catIds);
        $categories->setInputName('catIds');

        $categories_in_which_to_show_slider_tree_ids = explode(',', $obj->specific_categories);
        $categories_in_which_to_show_slider_tree = new HelperTreeCategories('associated-categories-home-tree2');
        $categories_in_which_to_show_slider_tree->setUseCheckBox(1)->setUseSearch(1)->setInputName('specific_categories');
        $categories_in_which_to_show_slider_tree->setSelectedCategories($categories_in_which_to_show_slider_tree_ids);

        $show_in_slider = array(
            array(
                'id'   => 'all',
                'val'  => 'all',
                'name' => $this->l('All')
            ),
            array(
                'id'   => 'category',
                'val'  => 'category',
                'name' => $this->l('Category')
            ),
            array(
                'id'   => 'products',
                'val'  => 'products',
                'name' => $this->l('Select products')
            ),
            array(
                'id'   => 'last_visited',
                'val'  => 'last_visited',
                'name' => $this->l('Last visited products')
            ),
            array(
                'id'   => 'top',
                'val'  => 'top_sales',
                'name' => $this->l('Top sales')
            ),
            array(
                'id'   => 'discount',
                'val'  => 'discount',
                'name' => $this->l('Products with discount')
            ),
            array(
                'id'   => 'current',
                'val'  => 'current',
                'name' => $this->l('Current category')
            ),
            array(
                'id'   => 'new',
                'val'  => 'new',
                'name' => $this->l('New')
            )
        );

        $order_by_slider = array(
            array(
                'id'   => 'name',
                'val'  => 'name',
                'name' => $this->l('Name')
            ),
            array(
                'id'   => 'date_add',
                'val'  => 'date_add',
                'name' => $this->l('Creation date')
            ),
            array(
                'id'   => 'random',
                'val'  => 'random',
                'name' => $this->l('Random')
            ),
        );

        $page_types = array(
            array(
                'id'   => 'home',
                'val'  => 'home',
                'name' => $this->l('Home Page')
            ),
            array(
                'id'   => 'category',
                'val'  => 'category',
                'name' => $this->l('Category Page')
            ),
            array(
                'id'   => 'product',
                'val'  => 'product',
                'name' => $this->l('Product Page')
            ),
        );

        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $hooks = array(
                array(
                    'id'   => 'displayWrapperTop',
                    'val'  => 'displayWrapperTop',
                    'name' => $this->l('displayWrapperTop')
                ),
                array(
                    'id'   => 'displayWrapperBottom',
                    'val'  => 'displayWrapperBottom',
                    'name' => $this->l('displayWrapperBottom')
                ),
                array(
                    'id'   => 'displayFooterBefore',
                    'val'  => 'displayFooterBefore',
                    'name' => $this->l('displayFooterBefore')
                ),
                array(
                    'id'   => 'displayFooterProduct',
                    'val'  => 'displayFooterProduct',
                    'name' => $this->l('displayFooterProduct')
                ),
                array(
                    'id'   => 'displayNavFullWidth',
                    'val'  => 'displayNavFullWidth',
                    'name' => $this->l('displayNavFullWidth')
                ),
                array(
                    'id'   => 'displayTop',
                    'val'  => 'displayTop',
                    'name' => $this->l('displayTop')
                ),
                array(
                    'id'   => 'displayHome',
                    'val'  => 'displayHome',
                    'name' => $this->l('displayHome')
                ),
                array(
                    'id'   => 'displayContentWrapperTop',
                    'val'  => 'displayContentWrapperTop',
                    'name' => $this->l('displayContentWrapperTop')
                ),
                array(
                    'id'   => 'displayContentWrapperBottom',
                    'val'  => 'displayContentWrapperBottom',
                    'name' => $this->l('displayContentWrapperBottom')
                ),
                array(
                    'id'   => 'displayLeftColumn',
                    'val'  => 'displayLeftColumn',
                    'name' => $this->l('displayLeftColumn')
                ),
                array(
                    'id'   => 'displayRightColumn',
                    'val'  => 'displayRightColumn',
                    'name' => $this->l('displayRightColumn')
                ),
            );
        } else {
            $hooks = array(
                array(
                    'id'   => 'displayTop',
                    'val'  => 'displayTop',
                    'name' => $this->l('displayTop')
                ),
                array(
                    'id'   => 'displayTopColumn',
                    'val'  => 'displayTopColumn',
                    'name' => $this->l('displayTopColumn')
                ),
                array(
                    'id'   => 'displayHome',
                    'val'  => 'displayHome',
                    'name' => $this->l('displayHome')
                ),
                array(
                    'id'   => 'displayHomeTab',
                    'val'  => 'displayHomeTab',
                    'name' => $this->l('displayHomeTab')
                ),
                array(
                    'id'   => 'displayFooterProduct',
                    'val'  => 'displayFooterProduct',
                    'name' => $this->l('displayFooterProduct')
                ),
                array(
                    'id'   => 'displayLeftColumn',
                    'val'  => 'displayLeftColumn',
                    'name' => $this->l('displayLeftColumn')
                ),
                array(
                    'id'   => 'displayRightColumn',
                    'val'  => 'displayRightColumn',
                    'name' => $this->l('displayRightColumn')
                ),
            );
        }

        $this->fields_form = array(
            'legend'  => array(
                'title' => $this->l('Add/Edit slider'),
                'icon'  => 'icon-list-ul'
            ),
            'tabs'    => array(
                'slider_general' => $this->l('General Slider Settings'),
                'single_slide'   => $this->l('Single Slide View'),
                'support'        => $this->l('Support'),
                'modules'        => $this->l('Related Modules'),
            ),
            'input'   => array(
                array(
                    'type'             => 'html',
                    'tab'              => 'support',
                    'form_group_class' => 'support_tab_content',
                    'name'             => ''
                ),
                array(
                    'type'             => 'html',
                    'tab'              => 'modules',
                    'form_group_class' => 'support_tab_content',
                    'name'             => $this->displayTabModules()
                ),
                array(
                    'type'    => 'switch',
                    'label'   => $this->l('Active'),
                    'name'    => 'active',
                    'class'   => 'active',
                    'tab'     => 'slider_general',
                    'is_bool' => true,
                    'values'  => array(
                        array(
                            'id'    => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id'    => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type'             => 'html',
                    'tab'              => 'slider_general',
                    'name'             => '<hr>',
                    'form_group_class' => 'block_hr',
                ),
                array(
                    'type'    => 'select',
                    'label'   => $this->l('Type of page on which to display slider'),
                    'name'    => 'display_page',
                    'tab'     => 'slider_general',
                    'options' => array(
                        'query' => $page_types,
                        'id'    => 'id',
                        'name'  => 'name'
                    )
                ),
                array(
                    'type'    => 'select',
                    'label'   => $this->l('Hook'),
                    'name'    => 'display_hook',
                    'tab'     => 'slider_general',
                    'options' => array(
                        'query' => $hooks,
                        'id'    => 'id',
                        'name'  => 'name'
                    )
                ),
                array(
                    'type'             => 'html',
                    'tab'              => 'slider_general',
                    'name'             => '<hr>',
                    'form_group_class' => 'block_hr',
                ),
                array(
                    'type'  => 'text',
                    'label' => $this->l('Title'),
                    'name'  => 'title',
                    'tab'   => 'slider_general',
                    'lang'  => true
                ),
                array(
                    'type'             => 'html',
                    'tab'              => 'slider_general',
                    'name'             => '<hr>',
                    'form_group_class' => 'block_hr',
                ),
                array(
                    'type'             => 'text',
                    'label'            => $this->l('Autoplay Speed (ms)'),
                    'name'             => 'pause',
                    'form_group_class' => 'form_group_int_value',
                    'tab'              => 'slider_general',
                ),
                array(
                    'type'             => 'text',
                    'label'            => $this->l('Slide animation speed (ms)'),
                    'name'             => 'speed',
                    'form_group_class' => 'form_group_int_value',
                    'tab'              => 'slider_general',
                ),
                array(
                    'type'             => 'text',
                    'label'            => $this->l('Total products in the slider'),
                    'name'             => 'total_number_of_slides',
                    'form_group_class' => 'form_group_int_value',
                    'tab'              => 'slider_general',
                ),
                array(
                    'type'             => 'text',
                    'label'            => $this->l('Number of visible products in slider'),
                    'name'             => 'number_of_visible_slides',
                    'form_group_class' => 'form_group_int_value mpm-featuredproducts-num-of-products-in-slider',
                    'tab'              => 'slider_general',
                ),
                array(
                    'type'             => 'text',
                    'label'            => $this->l('Number of products to scroll'),
                    'name'             => 'scroll_slides',
                    'form_group_class' => 'form_group_int_value',
                    'tab'              => 'slider_general',
                ),
                array(
                    'type'    => 'select',
                    'label'   => $this->l('Type of products to show'),
                    'name'    => 'type_products_show',
                    'tab'     => 'slider_general',
                    'options' => array(
                        'query' => $show_in_slider,
                        'id'    => 'id',
                        'name'  => 'name'
                    )
                ),
                array(
                    'type'             => 'html',
                    'label'            => $this->l('Select categories'),
                    'name'             => 'html_data',
                    'tab'              => 'slider_general',
                    'form_group_class' => 'categories_page categories_block_page',
                    'html_content'     => $categories->render(),
                ),
                array(
                    'type'             => 'html',
                    'label'            => $this->l('Select products'),
                    'name'             => 'html_data',
                    'tab'              => 'slider_general',
                    'form_group_class' => 'products_page products_block_page',
                    'html_content'     => $this->getBlockSearchProduct($productIds, 'home'),
                ),
                array(
                    'type'    => 'select',
                    'label'   => $this->l('Order Products By'),
                    'name'    => 'order_by',
                    'tab'     => 'slider_general',
                    'options' => array(
                        'query' => $order_by_slider,
                        'id'    => 'id',
                        'name'  => 'name'
                    )
                ),
                array(
                    'type'    => 'switch',
                    'label'   => $this->l('Show slider only in specific categories'),
                    'name'    => 'show_in_specific_categories',
                    'class'   => 'show_in_specific_categories',
                    'form_group_class' => 'show-in-specific-categories-form-group',
                    'tab'     => 'slider_general',
                    'is_bool' => true,
                    'values'  => array(
                        array(
                            'id'    => 'show_in_specific_categories_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id'    => 'show_in_specific_categories_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type'             => 'html',
                    'label'            => $this->l('Select categories on which to display slider'),
                    'name'             => 'html_data',
                    'tab'              => 'slider_general',
                    'form_group_class' => 'categories_in_which_to_show_slider',
                    'html_content'     => $categories_in_which_to_show_slider_tree->render(),
                ),
                array(
                    'type'             => 'html',
                    'tab'              => 'slider_general',
                    'name'             => '<hr>',
                    'form_group_class' => 'block_hr',
                ),
                array(
                    'type'    => 'switch',
                    'label'   => $this->l('Show navigation buttons in slider'),
                    'name'    => 'show_control',
                    'class'   => 'show_control',
                    'tab'     => 'slider_general',
                    'is_bool' => true,
                    'values'  => array(
                        array(
                            'id'    => 'show_control_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id'    => 'show_control_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type'    => 'switch',
                    'label'   => $this->l('Show navigation arrows in slider'),
                    'name'    => 'show_navigation_arrow',
                    'class'   => 'show_navigation_arrow',
                    'tab'     => 'slider_general',
                    'is_bool' => true,
                    'values'  => array(
                        array(
                            'id'    => 'show_navigation_arrow_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id'    => 'show_navigation_arrow_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type'             => 'color',
                    'label'            => $this->l('Color of active navigation buttons'),
                    'name'             => 'slider_navigation_color',
                    'form_group_class' => 'slider-navigation-color-form-group',
                    'hint'             => $this->l('Choose a color with the color picker, or enter an HTML color (e.g. "lightblue", "#CC6600").'),
                    'tab'              => 'slider_general',
                ),
                array(
                    'type'             => 'html',
                    'tab'              => 'slider_general',
                    'name'             => '<hr>',
                    'form_group_class' => 'block_hr',
                ),
                array(
                    'type'    => 'switch',
                    'label'   => $this->l('Auto play'),
                    'name'    => 'auto_scroll',
                    'class'   => 'auto_scroll',
                    'tab'     => 'slider_general',
                    'is_bool' => true,
                    'values'  => array(
                        array(
                            'id'    => 'auto_scroll_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id'    => 'auto_scroll_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type'    => 'switch',
                    'label'   => $this->l('Stop after hover'),
                    'name'    => 'stop_after_hover',
                    'class'   => 'stop_after_hover',
                    'tab'     => 'slider_general',
                    'is_bool' => true,
                    'values'  => array(
                        array(
                            'id'    => 'stop_after_hover_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id'    => 'stop_after_hover_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type'             => 'switch',
                    'label'            => $this->l('Use our custom design for product slide'),
                    'name'             => 'use_custom_design',
                    'class'            => 'use_custom_design',
                    'form_group_class' => 'use-custom-design-switch',
                    'tab'              => 'single_slide',
                    'is_bool'          => true,
                    'values'           => array(
                        array(
                            'id'    => 'use_custom_design_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id'    => 'use_custom_design_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type'             => 'color',
                    'label'            => $this->l('Slide background color'),
                    'name'             => 'slide_background_color',
                    'form_group_class' => 'mpm-featuredproducts-single-slide-settings',
                    'tab'              => 'single_slide',
                ),
                array(
                    'type'             => 'select',
                    'label'            => $this->l('Product Image Type'),
                    'name'             => 'type_image',
                    'tab'              => 'single_slide',
                    'form_group_class' => 'mpm-featuredproducts-single-slide-settings',
                    'options'          => array(
                        'query' => ImageType::getImagesTypes('products'),
                        'id'    => 'name',
                        'name'  => 'name'
                    )
                ),
                array(
                    'type'             => 'html',
                    'tab'              => 'single_slide',
                    'name'             => '<hr>',
                    'form_group_class' => 'mpm-featuredproducts-single-slide-settings block_hr',
                ),
                array(
                    'type'             => 'switch',
                    'label'            => $this->l('Show product name'),
                    'name'             => 'show_product_name',
                    'class'            => 'show_product_name',
                    'form_group_class' => 'mpm-featuredproducts-single-slide-settings',
                    'tab'              => 'single_slide',
                    'is_bool'          => true,
                    'values'           => array(
                        array(
                            'id'    => 'show_product_name_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id'    => 'show_product_name_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type'             => 'color',
                    'label'            => $this->l('Font color of product name'),
                    'name'             => 'product_name_color',
                    'form_group_class' => 'mpm-featuredproducts-single-slide-settings product-name-color-form-group',
                    'tab'              => 'single_slide',
                ),
                array(
                    'type'             => 'color',
                    'label'            => $this->l('Font color of product name on hover'),
                    'name'             => 'product_name_hover_color',
                    'form_group_class' => 'mpm-featuredproducts-single-slide-settings product-name-color-form-group',
                    'tab'              => 'single_slide',
                ),
                array(
                    'type'             => 'html',
                    'tab'              => 'single_slide',
                    'name'             => '<hr>',
                    'form_group_class' => 'mpm-featuredproducts-single-slide-settings block_hr',
                ),
                array(
                    'type'             => 'switch',
                    'label'            => $this->l('Show product description'),
                    'name'             => 'show_product_description',
                    'class'            => 'show_product_description',
                    'form_group_class' => 'mpm-featuredproducts-single-slide-settings',
                    'tab'              => 'single_slide',
                    'is_bool'          => true,
                    'values'           => array(
                        array(
                            'id'    => 'show_product_description_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id'    => 'show_product_description_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type'             => 'color',
                    'label'            => $this->l('Font color of product description'),
                    'name'             => 'product_description_color',
                    'form_group_class' => 'mpm-featuredproducts-single-slide-settings product-description-color-form-group',
                    'tab'              => 'single_slide',
                ),
                array(
                    'type'             => 'html',
                    'tab'              => 'single_slide',
                    'name'             => '<hr>',
                    'form_group_class' => 'mpm-featuredproducts-single-slide-settings block_hr',
                ),
                array(
                    'type'             => 'switch',
                    'label'            => $this->l('Show product price'),
                    'name'             => 'show_product_price',
                    'class'            => 'show_product_price',
                    'form_group_class' => 'mpm-featuredproducts-single-slide-settings',
                    'tab'              => 'single_slide',
                    'is_bool'          => true,
                    'values'           => array(
                        array(
                            'id'    => 'show_product_price_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id'    => 'show_product_price_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type'             => 'color',
                    'label'            => $this->l('Font color of product price'),
                    'name'             => 'product_price_color',
                    'form_group_class' => 'mpm-featuredproducts-single-slide-settings product-price-color-form-group',
                    'tab'              => 'single_slide',
                ),
                array(
                    'type'             => 'color',
                    'label'            => $this->l('Font color of product regular price'),
                    'name'             => 'product_regular_price_color',
                    'form_group_class' => 'mpm-featuredproducts-single-slide-settings product-price-color-form-group',
                    'tab'              => 'single_slide',
                ),
                array(
                    'type'             => 'html',
                    'tab'              => 'single_slide',
                    'name'             => '<hr>',
                    'form_group_class' => 'mpm-featuredproducts-single-slide-settings block_hr',
                ),
                array(
                    'type'             => 'switch',
                    'label'            => $this->l('Show product flags'),
                    'name'             => 'show_product_flags',
                    'class'            => 'show_product_flags',
                    'tab'              => 'single_slide',
                    'form_group_class' => 'mpm-featuredproducts-single-slide-settings',
                    'is_bool'          => true,
                    'values'           => array(
                        array(
                            'id'    => 'show_product_flags_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id'    => 'show_product_flags_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type'             => 'html',
                    'tab'              => 'single_slide',
                    'name'             => '<hr>',
                    'form_group_class' => 'mpm-featuredproducts-single-slide-settings block_hr',
                ),
                array(
                    'type'             => 'switch',
                    'label'            => $this->l('Show product color variants'),
                    'name'             => 'show_product_variants',
                    'class'            => 'show_product_variants',
                    'tab'              => 'single_slide',
                    'form_group_class' => 'mpm-featuredproducts-single-slide-settings',
                    'is_bool'          => true,
                    'values'           => array(
                        array(
                            'id'    => 'show_product_variants_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id'    => 'show_product_variants_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type'             => 'html',
                    'tab'              => 'single_slide',
                    'name'             => '<hr>',
                    'form_group_class' => 'mpm-featuredproducts-single-slide-settings block_hr',
                ),
                array(
                    'type'             => 'switch',
                    'label'            => $this->l('Show product availability status'),
                    'name'             => 'show_product_availability_status',
                    'class'            => 'show_product_availability_status',
                    'form_group_class' => 'mpm-featuredproducts-single-slide-settings',
                    'tab'              => 'single_slide',
                    'is_bool'          => true,
                    'values'           => array(
                        array(
                            'id'    => 'show_product_availability_status_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id'    => 'show_product_availability_status_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type'             => 'color',
                    'label'            => $this->l('Font color of availability status text'),
                    'name'             => 'product_availability_status_text_color',
                    'form_group_class' => 'mpm-featuredproducts-single-slide-settings product-availability-status-color-form-group',
                    'tab'              => 'single_slide',
                ),
                array(
                    'type'             => 'color',
                    'label'            => $this->l('Font color of availability status icon'),
                    'name'             => 'product_availability_status_icon_color',
                    'form_group_class' => 'mpm-featuredproducts-single-slide-settings product-availability-status-color-form-group',
                    'tab'              => 'single_slide',
                ),
                array(
                    'type'             => 'html',
                    'tab'              => 'single_slide',
                    'name'             => '<hr>',
                    'form_group_class' => 'mpm-featuredproducts-single-slide-settings block_hr',
                ),
                array(
                    'type'             => 'switch',
                    'label'            => $this->l('Show link to product full page'),
                    'name'             => 'show_product_link_to_full_page',
                    'class'            => 'show_product_link_to_full_page',
                    'form_group_class' => 'mpm-featuredproducts-single-slide-settings',
                    'tab'              => 'single_slide',
                    'is_bool'          => true,
                    'values'           => array(
                        array(
                            'id'    => 'show_product_link_to_full_page_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id'    => 'show_product_link_to_full_page_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type'             => 'color',
                    'label'            => $this->l('Color of link to full product page'),
                    'name'             => 'product_link_to_full_page_color',
                    'form_group_class' => 'mpm-featuredproducts-single-slide-settings product-link-to-full-page-color-form-group',
                    'tab'              => 'single_slide',
                ),
                array(
                    'type'             => 'color',
                    'label'            => $this->l('Color of link to full product page on hover'),
                    'name'             => 'product_link_to_full_page_hover_color',
                    'form_group_class' => 'mpm-featuredproducts-single-slide-settings product-link-to-full-page-color-form-group',
                    'tab'              => 'single_slide',
                ),
                array(
                    'type'             => 'html',
                    'tab'              => 'single_slide',
                    'name'             => '<hr>',
                    'form_group_class' => 'mpm-featuredproducts-single-slide-settings block_hr',
                ),
                array(
                    'type'             => 'switch',
                    'label'            => $this->l('Show "Quickview" button'),
                    'name'             => 'show_product_quickview',
                    'class'            => 'show_product_quickview',
                    'form_group_class' => 'mpm-featuredproducts-single-slide-settings',
                    'tab'              => 'single_slide',
                    'is_bool'          => true,
                    'values'           => array(
                        array(
                            'id'    => 'show_product_quickview_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id'    => 'show_product_quickview_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type'             => 'color',
                    'label'            => $this->l('Color of quickview button'),
                    'name'             => 'product_quickview_color',
                    'form_group_class' => 'mpm-featuredproducts-single-slide-settings product-quickview-color-form-group',
                    'tab'              => 'single_slide',
                ),
                array(
                    'type'             => 'color',
                    'label'            => $this->l('Color of quickview button on hover'),
                    'name'             => 'product_quickview_hover_color',
                    'form_group_class' => 'mpm-featuredproducts-single-slide-settings product-quickview-color-form-group',
                    'tab'              => 'single_slide',
                ),
                array(
                    'type'             => 'html',
                    'tab'              => 'single_slide',
                    'name'             => '<hr>',
                    'form_group_class' => 'mpm-featuredproducts-single-slide-settings block_hr',
                ),
                array(
                    'type'             => 'switch',
                    'label'            => $this->l('Show "Add to cart" button'),
                    'name'             => 'show_product_button_add',
                    'class'            => 'show_product_button_add',
                    'form_group_class' => 'mpm-featuredproducts-single-slide-settings',
                    'tab'              => 'single_slide',
                    'is_bool'          => true,
                    'values'           => array(
                        array(
                            'id'    => 'show_product_button_add_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id'    => 'show_product_button_add_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type'             => 'color',
                    'label'            => $this->l('Background of Add To Cart button'),
                    'name'             => 'product_addtocart_background_color',
                    'form_group_class' => 'mpm-featuredproducts-single-slide-settings product-addtocart-color-form-group',
                    'tab'              => 'single_slide',
                ),
                array(
                    'type'             => 'color',
                    'label'            => $this->l('Background of Add To Cart button on hover'),
                    'name'             => 'product_addtocart_hover_background_color',
                    'form_group_class' => 'mpm-featuredproducts-single-slide-settings product-addtocart-color-form-group',
                    'tab'              => 'single_slide',
                ),
                array(
                    'type'             => 'color',
                    'label'            => $this->l('Font color of Add To Cart button'),
                    'name'             => 'product_addtocart_color',
                    'form_group_class' => 'mpm-featuredproducts-single-slide-settings product-addtocart-color-form-group',
                    'tab'              => 'single_slide',
                ),
                array(
                    'type'             => 'color',
                    'label'            => $this->l('Font color of Add To Cart button on hover'),
                    'name'             => 'product_addtocart_hover_color',
                    'form_group_class' => 'mpm-featuredproducts-single-slide-settings product-addtocart-color-form-group',
                    'tab'              => 'single_slide',
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'token_featuredproducts',
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'idLang',
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'idShop',
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'ps_version',
                ),
            ),
            'buttons' => array(
                'save-and-stay' => array(
                    'title' => $this->l('Save and Stay'),
                    'name'  => 'submitAdd' . $this->table . 'AndStay',
                    'type'  => 'submit',
                    'class' => 'btn btn-default pull-right',
                    'icon'  => 'process-icon-save'
                ),
                'save'          => array(
                    'title' => $this->l('Save'),
                    'name'  => 'submitAdd' . $this->table,
                    'type'  => 'submit',
                    'class' => 'btn btn-default pull-right',
                    'icon'  => 'process-icon-save'
                ),
            ),
        );
        $this->fields_value['token_featuredproducts'] = Tools::getAdminTokenLite('AdminFeaturedProducts');
        $this->fields_value['idLang'] = $this->_idLang;
        $this->fields_value['idShop'] = $this->_idShop;
        $this->fields_value['ps_version'] = _PS_VERSION_;

        $this->tpl_form_vars['PS_ALLOW_ACCENTED_CHARS_URL'] = (int)Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL');

        return parent::renderForm();
    }

    public function displayTabSupport()
    {
        $data = Context::getContext()->smarty->createTemplate(_PS_MODULE_DIR_ . 'featuredproducts/views/templates/hook/tabSuppor.tpl');
        return $data->fetch();
    }

    public function displayTabModules()
    {
        $data = Context::getContext()->smarty->createTemplate(_PS_MODULE_DIR_ . 'featuredproducts/views/templates/hook/modules.tpl');
        return $data->fetch();
    }


    public function displayAjax()
    {
        $json = array();
        try {
            if (Tools::getValue('action') == 'addProduct') {
                $products = Tools::getValue('products');

                if ($products) {
                    $products = implode(",", $products);
                }

                $list = $this->getProductList($products, Tools::getValue('idLang'), Tools::getValue('idShop'));

                if (!$list) {
                    $json['list'] = ' ';
                    $json['products'] = ' ';
                } else {
                    $json['list'] = $list;
                    $json['products'] = $products;
                }

            }

            die(json_encode($json));
        } catch (Exception $e) {
            $json['error'] = $e->getMessage();
            if ($e->getCode() == 10) {
                $json['error_message'] = $e->getMessage();
            }
        }
        die(json_encode($json));
    }


    public function getBlockSearchProduct($ids)
    {
        $data = Context::getContext()->smarty->createTemplate(_PS_MODULE_DIR_ . 'featuredproducts/views/templates/hook/block-search.tpl');

        $list = $this->getProductList($ids, Context::getContext()->language->id, Context::getContext()->shop->id);
        $class = 'productIds';

        $data->assign(
            array(
                'ids'   => $ids,
                'list'  => $list,
                'class' => $class,
            )
        );

        return $data->fetch();
    }


    public function getProductList($ids, $idLang, $idShop)
    {

        $data = Context::getContext()->smarty->createTemplate(_PS_MODULE_DIR_ . 'featuredproducts/views/templates/hook/productList.tpl');

        if ($ids) {
            $items = $this->getProductsByIds($idLang, $idShop, $ids);
            $type_img = ImageType::getImagesTypes('products');
            foreach ($type_img as $key => $val) {
                $pos = strpos($val['name'], 'cart_def');
                if ($pos !== false) {
                    $type_i = $val['name'];
                }
            }
            foreach ($items as $key => $item) {
                $items[$key]['image'] = str_replace('http://', Tools::getShopProtocol(), Context::getContext()->link->getImageLink($item['link_rewrite'], $item['id_image'], $type_i));
            }
        } else {
            $items = false;
        }

        $data->assign(
            array(
                'id_shop' => $idShop,
                'id_lang' => $idLang,
                'items'   => $items,
            )
        );

        return $data->fetch();
    }


    public function getProductsByIds($id_lang, $id_shop, $productsIds)
    {
        $sql = '
			SELECT pl.name, p.*, i.id_image, pl.link_rewrite, p.reference
      FROM ' . _DB_PREFIX_ . 'product_lang AS pl
      LEFT JOIN ' . _DB_PREFIX_ . 'image AS i
      ON i.id_product = pl.id_product AND i.cover=1
      INNER JOIN ' . _DB_PREFIX_ . 'product AS p
      ON p.id_product = pl.id_product
      WHERE pl.id_lang = ' . (int)$id_lang . '
      AND pl.id_shop = ' . (int)$id_shop . '
      AND p.id_product IN (' . pSQL($productsIds) . ')
			';

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    }

    public function ajaxProcessSearchProduct()
    {
        $search = Tools::getValue('q');
        $limit = 50;
        $idLang = $this->context->language->id;
        $idShop = $this->context->shop->id;
        $where = "";
        $limit_p = '';
        if ($search) {
            $where = " AND (pl.name LIKE '%" . pSQL($search) . "%' OR pl.id_product LIKE '%" . pSQL($search) . "%')";
        }


        if ($limit) {
            $limit_p = ' LIMIT ' . (int)$limit;
        }
        $sql = '
			SELECT pl.name, pl.id_product AS id, i.id_image, pl.link_rewrite, p.reference AS ref
      FROM ' . _DB_PREFIX_ . 'product_lang AS pl
      LEFT JOIN ' . _DB_PREFIX_ . 'image AS i
      ON i.id_product = pl.id_product AND i.cover=1
      INNER JOIN ' . _DB_PREFIX_ . 'product AS p
      ON p.id_product = pl.id_product
      WHERE pl.id_lang = ' . (int)$idLang . '
      AND pl.id_shop = ' . (int)$idShop . '
      ' . $where . $limit_p . '
			';

        $items = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        foreach ($items as $key => $item) {
            $items[$key]['image'] = str_replace('http://', Tools::getShopProtocol(), $this->context->link->getImageLink($item['link_rewrite'], $item['id_image'], ''));
        }

        die(json_encode($items));
    }


}