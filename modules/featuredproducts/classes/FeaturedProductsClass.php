<?php

class FeaturedProductsClass extends ObjectModel
{
    public $id_featuredproducts;

    public $active = 1;
    public $display_page = 'home';
    public $display_hook = 'displayTop';
    public $pause = 4000;
    public $speed = 500;
    public $total_number_of_slides = 20;
    public $number_of_visible_slides = 4;
    public $scroll_slides = 1;
    public $type_products_show = 'all';
    public $order_by = 'random';
    public $use_custom_design = 1;
    public $type_image;
    public $show_product_name = 1;
    public $show_product_flags = 1;
    public $show_product_variants = 1;
    public $show_product_availability_status = 1;
    public $show_product_link_to_full_page = 1;
    public $show_product_quickview = 1;
    public $show_product_description = 1;
    public $show_product_price = 1;
    public $show_product_button_add = 1;
    public $show_control = 1;
    public $show_navigation_arrow = 1;
    public $auto_scroll = 1;
    public $stop_after_hover = 1;
    public $title;
    public $productIds;
    public $catIds;
    public $show_in_specific_categories;
    public $specific_categories;

    public $slider_navigation_color = '#333333';
    public $slide_background_color = '#ffffff';
    public $product_name_color = '#323232';
    public $product_name_hover_color = '#7a7a7a';
    public $product_price_color = '#f13340';
    public $product_regular_price_color = '#7a7a7a';
    public $product_description_color = '#7a7a7a';
    public $product_link_to_full_page_color = '#f13340';
    public $product_link_to_full_page_hover_color = '#4787ce';
    public $product_quickview_color = '#f13340';
    public $product_quickview_hover_color = '#4787ce';
    public $product_addtocart_background_color = '#f13340';
    public $product_addtocart_hover_background_color = '#cf0022';
    public $product_addtocart_color = '#ffffff';
    public $product_addtocart_hover_color = '#ffffff';
    public $product_availability_status_text_color = '#323232';
    public $product_availability_status_icon_color = '#ff9a52';

    public static $definition = array(
        'table'     => 'featuredproducts',
        'primary'   => 'id_featuredproducts',
        'multilang' => true,
        'fields'    => array(
            //basic fields
            'active'                           => array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'shop' => true),
            'display_page'                     => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'display_hook'                     => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'productIds'                       => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'catIds'                           => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'pause'                            => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'speed'                            => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'total_number_of_slides'           => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'number_of_visible_slides'         => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'scroll_slides'                    => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'type_products_show'               => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'order_by'                         => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'use_custom_design'                => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'type_image'                       => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'show_product_flags'               => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'show_product_variants'            => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'show_product_availability_status' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'show_product_name'                => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'show_product_link_to_full_page'   => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'show_product_quickview'           => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'show_product_description'         => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'show_product_price'               => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'show_product_button_add'          => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'show_control'                     => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'show_navigation_arrow'            => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'auto_scroll'                      => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'stop_after_hover'                 => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'show_in_specific_categories'      => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'specific_categories'              => array('type' => self::TYPE_STRING, 'validate' => 'isString'),


            'slider_navigation_color'                  => array('type' => self::TYPE_HTML, 'validate' => 'isString'),
            'slide_background_color'                   => array('type' => self::TYPE_HTML, 'validate' => 'isString'),
            'product_name_color'                       => array('type' => self::TYPE_HTML, 'validate' => 'isString'),
            'product_name_hover_color'                 => array('type' => self::TYPE_HTML, 'validate' => 'isString'),
            'product_price_color'                      => array('type' => self::TYPE_HTML, 'validate' => 'isString'),
            'product_regular_price_color'              => array('type' => self::TYPE_HTML, 'validate' => 'isString'),
            'product_description_color'                => array('type' => self::TYPE_HTML, 'validate' => 'isString'),
            'product_link_to_full_page_color'          => array('type' => self::TYPE_HTML, 'validate' => 'isString'),
            'product_link_to_full_page_hover_color'    => array('type' => self::TYPE_HTML, 'validate' => 'isString'),
            'product_quickview_color'                  => array('type' => self::TYPE_HTML, 'validate' => 'isString'),
            'product_quickview_hover_color'            => array('type' => self::TYPE_HTML, 'validate' => 'isString'),
            'product_addtocart_background_color'       => array('type' => self::TYPE_HTML, 'validate' => 'isString'),
            'product_addtocart_hover_background_color' => array('type' => self::TYPE_HTML, 'validate' => 'isString'),
            'product_addtocart_color'                  => array('type' => self::TYPE_HTML, 'validate' => 'isString'),
            'product_addtocart_hover_color'            => array('type' => self::TYPE_HTML, 'validate' => 'isString'),
            'product_availability_status_text_color'   => array('type' => self::TYPE_HTML, 'validate' => 'isString'),
            'product_availability_status_icon_color'   => array('type' => self::TYPE_HTML, 'validate' => 'isString'),

            // Lang fields
            'title'                                    => array('type' => self::TYPE_STRING, 'required' => true, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 512),
        )
    );

    public function __construct($id_featuredproducts = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id_featuredproducts, $id_lang, $id_shop);

        Shop::addTableAssociation('featuredproducts', array('type' => 'shop'));

        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $this->type_image = $this->type_image ?: ImageType::getFormattedName('home');
        } else {
            $this->type_image = $this->type_image ?: ImageType::getFormatedName('home');
        }

        /**
         * When shop context is - 'ALL_SHOPS', we need to manually set id_shop
         * for the items that is associated with not default shop.
         */
        if (!isset($this->active)) {
            foreach (Shop::getContextListShopID() as $shop_id) {
                parent::__construct($id_featuredproducts, $id_lang, $shop_id);

                if (isset($this->active)) {
                    break;
                }
            }
        }

        if (!empty(Tools::getValue('catIds'))) {
            $catIds = implode(',', (array)Tools::getValue('catIds'));
            $_POST['catIds'] = $catIds;
        } else {
            $_POST['catIds'] = '';
        }

        if (!empty(Tools::getValue('specific_categories'))) {
            $specific_categories = implode(',', (array)Tools::getValue('specific_categories'));
            $_POST['specific_categories'] = $specific_categories;
        } else {
            $_POST['specific_categories'] = '';
        }
    }

    public function update($null_values = false)
    {
        $res = parent::update($null_values);
        return $res;
    }

    public function add($autodate = true, $null_values = false)
    {
        $res = parent::add($autodate, $null_values);
        return $res;
    }

    public function delete()
    {
        $res = parent::delete();
        return $res;
    }

    public static function createTables()
    {
        $sql = 'DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'featuredproducts';
        Db::getInstance()->execute($sql);

        $sql = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'featuredproducts(
				id_featuredproducts INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
				active INT(11) NULL,
				display_page VARCHAR(255) NULL,
				display_hook VARCHAR(255) NULL,
				pause INT(11) NULL,
				speed INT(11) NULL,
				total_number_of_slides INT(11) NULL,
                number_of_visible_slides INT(11) NULL,
                scroll_slides INT(11) NULL,
                type_products_show VARCHAR(255) NULL,
                order_by VARCHAR(255) NULL,
                type_image VARCHAR(255) NULL,
                show_product_flags INT(11) NULL,
                show_product_variants INT(11) NULL,
                show_product_availability_status INT(11) NULL,
                show_product_name INT(11) NULL,
                show_product_link_to_full_page INT(11) NULL,
                show_product_quickview INT(11) NULL,
				show_product_description INT(11) NULL,
				show_product_price INT(11) NULL,
				show_product_button_add INT(11) NULL,
				show_control INT(11) NULL,
				show_navigation_arrow INT(11) NULL,
				use_custom_design INT(11) NULL,
				auto_scroll INT(11) NULL,
				stop_after_hover INT(11) NULL,
				show_in_specific_categories INT(11) NULL,
				specific_categories TEXT NULL,
				productIds VARCHAR(255) NULL,
				catIds VARCHAR(255) NULL,
				`slider_navigation_color` TEXT  NOT NULL,
				`slide_background_color` TEXT  NOT NULL,
				`product_availability_status_text_color` TEXT NOT NULL,
				`product_availability_status_icon_color` TEXT NOT NULL,
				`product_name_color` TEXT  NOT NULL,
				`product_name_hover_color` TEXT  NOT NULL,
				`product_price_color` TEXT  NOT NULL,
				`product_regular_price_color` TEXT  NOT NULL,
				`product_description_color` TEXT  NOT NULL,
				`product_link_to_full_page_color` TEXT  NOT NULL,
				`product_link_to_full_page_hover_color` TEXT  NOT NULL,
				`product_quickview_color` TEXT  NOT NULL,
				`product_quickview_hover_color` TEXT  NOT NULL,
				`product_addtocart_background_color` TEXT  NOT NULL,
				`product_addtocart_hover_background_color` TEXT  NOT NULL,
				`product_addtocart_color` TEXT  NOT NULL,
				`product_addtocart_hover_color` TEXT  NOT NULL,

				PRIMARY KEY (`id_featuredproducts`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8';

        Db::getInstance()->execute($sql);

        $sql = 'DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'featuredproducts_lang';
        Db::getInstance()->execute($sql);

        $sql = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'featuredproducts_lang(
				id_featuredproducts INT(11) UNSIGNED NOT NULL,
				id_lang INT(11) UNSIGNED NOT NULL,
				title VARCHAR(512) NULL,

				PRIMARY KEY(id_featuredproducts, id_lang)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8';
        Db::getInstance()->execute($sql);

        $sql = 'DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'featuredproducts_shop';
        Db::getInstance()->execute($sql);

        $sql = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'featuredproducts_shop(
				id_featuredproducts INT(11) UNSIGNED NOT NULL,
				id_shop INT(11) UNSIGNED NOT NULL,
				active BOOLEAN NULL,
				PRIMARY KEY(id_featuredproducts, id_shop)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8';
        Db::getInstance()->execute($sql);
    }

    public static function dropTables()
    {
        $sql = 'DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'featuredproducts';
        Db::getInstance()->execute($sql);

        $sql = 'DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'featuredproducts_lang';
        Db::getInstance()->execute($sql);

        $sql = 'DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'featuredproducts_shop';
        Db::getInstance()->execute($sql);
    }

    /**
     * @param $table_name
     * @return bool
     */
    public static function isTableExists($table_name)
    {
        $sql = "SHOW TABLES LIKE '" . _DB_PREFIX_ . $table_name . "'";

        if (!Db::getInstance()->executeS($sql)) {
            return false;
        }

        return true;
    }

    /**
     *
     * @param $display_page
     * @param $display_hook
     * @param $id_lang
     * @param $id_shop
     * @return bool|FeaturedProductsClass
     */
    public static function getSlidersByDisplayPageAndHook($display_page, $display_hook, $id_lang, $id_shop)
    {
        $sliders = array();
        $query = 'SELECT fp.id_featuredproducts AS id_slider
              FROM ' . _DB_PREFIX_ . 'featuredproducts fp
              LEFT JOIN ' . _DB_PREFIX_ . 'featuredproducts_lang AS fpl
              ON fp.id_featuredproducts = fpl.id_featuredproducts 
              LEFT JOIN ' . _DB_PREFIX_ . 'featuredproducts_shop AS fps
              ON fp.id_featuredproducts = fps.id_featuredproducts
              WHERE fp.display_page = "' . pSQL($display_page) . '" 
              AND fp.display_hook = "' . pSQL($display_hook) . '"
              AND fpl.id_lang = ' . (int)$id_lang . '
              AND fps.id_shop = ' . (int)$id_shop . '
              AND fps.active = 1
              ';

        $matched_slider_ids = Db::getInstance()->executeS($query);

        if (!empty($matched_slider_ids) && isset($matched_slider_ids[0]['id_slider'])) {
            foreach ($matched_slider_ids as $slider_id_container) {
                $slider_id = $slider_id_container['id_slider'];
                $sliders[] = new FeaturedProductsClass($slider_id, $id_lang, $id_shop);
            }
        }

        return $sliders;
    }

    public static function getAllTableData($lang)
    {
        if (!self::isTableExists('featuredproducts')) {
            return false;
        }

        if ($lang) {
            return Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'featuredproducts_lang');
        }

        return Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'featuredproducts');
    }

    /**
     * For Upgrade 3.0.0
     * @return array
     */
    public static function getSlidersRestructuredForInsertionInSeparateRows()
    {
        $slides_in_new_structure = array(
            'home'     => array('id_featuredproducts' => 1, 'display_page' => 'home'),
            'category' => array('id_featuredproducts' => 2, 'display_page' => 'category'),
            'product'  => array('id_featuredproducts' => 3, 'display_page' => 'product')
        );

        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $sliders_table_data = FeaturedProductsClass::getAllTableData(false);
            $sliders_table_data = $sliders_table_data[0];

            $sliders_table_data_lang = FeaturedProductsClass::getAllTableData(true);
            $all_sliders_data_is_retrieved = !empty($sliders_table_data) && !empty($sliders_table_data_lang);

            if ($all_sliders_data_is_retrieved) {
                foreach ($sliders_table_data as $old_data_key => $old_data_val) {
                    if ($old_data_key == 'id_featuredproducts') {
                        continue;
                    }

                    $parts_of_exploded_key = explode('_', $old_data_key);
                    $vanilla_key = $parts_of_exploded_key[0];
                    $identifier_of_slider_category = end($parts_of_exploded_key);

                    if ($identifier_of_slider_category !== 'home' &&
                        $identifier_of_slider_category !== 'category' &&
                        $identifier_of_slider_category !== 'product'
                    ) {
                        continue;
                    }

                    $is_key_name_in_default_pattern_format = $vanilla_key && $identifier_of_slider_category && count($parts_of_exploded_key) > 1;
                    $is_key_name_for_manually_selected_products_or_cats = preg_match('/productIds/', $old_data_key) || preg_match('/catIds/', $old_data_key);

                    if ($is_key_name_in_default_pattern_format) {
                        switch ($vanilla_key) {
                            case 'display_slider':
                                $slides_in_new_structure[$identifier_of_slider_category]['active'] = $old_data_val;
                                break;
                            case 'number_slides':
                                $slides_in_new_structure[$identifier_of_slider_category]['total_number_of_slides'] = $old_data_val;
                                break;
                            case 'show_slides':
                                $slides_in_new_structure[$identifier_of_slider_category]['number_of_visible_slides'] = $old_data_val;
                                break;
                            default:
                                $slides_in_new_structure[$identifier_of_slider_category][$vanilla_key] = $old_data_val;
                                break;
                        }
                    } elseif ($is_key_name_for_manually_selected_products_or_cats) {
                        switch ($old_data_key) {
                            case 'productIds':
                                $slides_in_new_structure['home']['productIds'] = $old_data_val;
                                break;
                            case 'productIdsCategory':
                                $slides_in_new_structure['category']['productIds'] = $old_data_val;
                                break;
                            case 'productIdsProduct':
                                $slides_in_new_structure['product']['productIds'] = $old_data_val;
                                break;
                            case 'catIds':
                                $slides_in_new_structure['home']['catIds'] = $old_data_val;
                                break;
                            case 'catIdsCategory':
                                $slides_in_new_structure['category']['catIds'] = $old_data_val;
                                break;
                            case 'catIdsProduct':
                                $slides_in_new_structure['product']['catIds'] = $old_data_val;
                                break;
                        }
                    }
                }

                foreach ($sliders_table_data_lang as $old_data_key_lang => $old_data_val_lang) {
                    $current_lang_id = $sliders_table_data_lang[$old_data_key_lang]['id_lang'];

                    $slides_in_new_structure['home']['title'][$current_lang_id] = $sliders_table_data_lang[$old_data_key_lang]['title_home'];
                    $slides_in_new_structure['category']['title'][$current_lang_id] = $sliders_table_data_lang[$old_data_key_lang]['title_category'];
                    $slides_in_new_structure['product']['title'][$current_lang_id] = $sliders_table_data_lang[$old_data_key_lang]['title_product'];
                }
            }
        } else {
            $home_old = unserialize(Configuration::get('GOMAKOIL_HOMEPAGE_CONFIG'));
            $cat_old = unserialize(Configuration::get('GOMAKOIL_CATEGORY_PAGE_CONFIG'));
            $prod_old = unserialize(Configuration::get('GOMAKOIL_PRODUCT_PAGE_CONFIG'));
            $sliders_table_data = $home_old + $cat_old + $prod_old;

            foreach ($sliders_table_data as $old_data_key => $old_data_val) {
                $parts_of_exploded_key = explode('_', $old_data_key);
                $identifier_of_slider_category = array_pop($parts_of_exploded_key);
                $vanilla_key = implode('_', $parts_of_exploded_key);

                if ($identifier_of_slider_category === 'pr' || $identifier_of_slider_category === 'prod') {
                    $identifier_of_slider_category = 'product';
                } else if ($identifier_of_slider_category === 'cat') {
                    $identifier_of_slider_category = 'category';
                }

                if ($identifier_of_slider_category !== 'home' &&
                    $identifier_of_slider_category !== 'category' &&
                    $identifier_of_slider_category !== 'product'
                ) {
                    continue;
                }

                $is_key_name_in_default_pattern_format = $vanilla_key && $identifier_of_slider_category && count(explode('_', $old_data_key)) > 1;
                $is_key_name_for_manually_selected_products_or_cats = preg_match('/products/', $old_data_key) || preg_match('/category_list/', $old_data_key);

                if ($is_key_name_in_default_pattern_format) {
                    switch ($vanilla_key) {
                        case 'display_slider':
                            $slides_in_new_structure[$identifier_of_slider_category]['active'] = $old_data_val;
                            break;
                        case 'n_slid':
                            $slides_in_new_structure[$identifier_of_slider_category]['total_number_of_slides'] = $old_data_val;
                            break;
                        case 'count_slid':
                            $slides_in_new_structure[$identifier_of_slider_category]['number_of_visible_slides'] = $old_data_val;
                            break;
                        case 'n_scr_slid':
                            $slides_in_new_structure[$identifier_of_slider_category]['scroll_slides'] = $old_data_val;
                            break;
                        case 'selection_product':
                            $slides_in_new_structure[$identifier_of_slider_category]['type_products_show'] = $old_data_val;
                            break;
                        case 'images':
                            $slides_in_new_structure[$identifier_of_slider_category]['type_image'] = $old_data_val;
                            break;
                        case 'title':
                            foreach (Language::getLanguages() as $lang) {
                                $slides_in_new_structure[$identifier_of_slider_category]['title'][$lang['id_lang']] = $old_data_val;
                            }
                            break;
                        default:
                            $slides_in_new_structure[$identifier_of_slider_category][$vanilla_key] = $old_data_val;
                            break;
                    }
                } elseif ($is_key_name_for_manually_selected_products_or_cats) {
                    switch ($old_data_key) {
                        case 'products':
                            $slides_in_new_structure['home']['productIds'] = $old_data_val;
                            break;
                        case 'products_cat':
                            $slides_in_new_structure['category']['productIds'] = $old_data_val;
                            break;
                        case 'products_pr':
                            $slides_in_new_structure['product']['productIds'] = $old_data_val;
                            break;
                        case 'category_list':
                            $slides_in_new_structure['home']['catIds'] = $old_data_val;
                            break;
                        case 'category_list_cat':
                            $slides_in_new_structure['category']['catIds'] = $old_data_val;
                            break;
                        case 'category_list_pr':
                            $slides_in_new_structure['product']['catIds'] = $old_data_val;
                            break;
                    }
                }
            }
        }

        return $slides_in_new_structure;
    }
}