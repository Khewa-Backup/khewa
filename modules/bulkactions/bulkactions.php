<?php
/**
 *  @author    Amazzing <mail@mirindevo.com>
 *  @copyright Amazzing
 *  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
class BulkActions extends Module
{
    public function __construct()
    {
        if (!defined('_PS_VERSION_')) {
            exit;
        }
        $this->name = 'bulkactions';
        $this->author = 'Amazzing';
        $this->version = '1.3.0';
        $this->ps_versions_compliancy = ['min' => '1.6.0.4', 'max' => _PS_VERSION_];
        $this->tab = 'quick_bulk_update';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->module_key = '528d5c81f014cfd31425f42f3c635345';

        parent::__construct();

        $this->displayName = $this->l('Handy bulk actions');
        $this->description = $this->l('Advanced bulk action tools for products/combinations/categories/customers');
        $this->db = Db::getInstance();
        $this->is_16 = Tools::substr(_PS_VERSION_, 0, 3) === '1.6';
        $this->warnings = $this->processed_items = [];
    }

    public function install()
    {
        return parent::install() && $this->registerHook('displayBackOfficeHeader');
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    public function getContent()
    {
        $this->context->smarty->assign([
            'version' => $this->version,
            'info_links' => [
                'changelog' => $this->_path . 'Readme.md?v=' . $this->version,
                'documentation' => $this->_path . 'readme_en.pdf?v=' . $this->version,
                'contact' => 'https://addons.prestashop.com/en/contact-us?id_product=21913',
                'modules' => 'http://addons.prestashop.com/en/2_community-developer?contributor=64815',
            ],
        ]);

        return $this->display($this->local_path, 'views/templates/admin/configure.tpl');
    }

    public function getCurrentID($type = 'product')
    {
        $id = Tools::getValue('id_' . $type);
        if ($type == 'cat_parent' && !Tools::isSubmit('updatecategory')) {  // 1.6 - 1.7.5
            $id = Tools::getValue('id_category', $this->context->shop->getCategory());
        }
        if (!$this->is_16 && $request = $this->getSfRequest()) {
            $id = $request->get('id');
            if ($type == 'cat_parent' && $request->get('_route') == 'admin_categories_index'
                && !$id = $request->get('categoryId')) { // 1.7.6+
                $id = $this->context->shop->getCategory();
            }
        }

        return (int) $id;
    }

    public function getSfRequest()
    {
        $sf_container = PrestaShop\PrestaShop\Adapter\SymfonyContainer::getInstance();

        return $sf_container->get('request_stack')->getCurrentRequest();
    }

    public function getProductImages($id_product)
    {
        $images = $this->db->executeS('
            SELECT DISTINCT(i.id_image) FROM ' . _DB_PREFIX_ . 'image i
            ' . Shop::addSqlAssociation('image', 'i') . '
            WHERE i.id_product = ' . (int) $id_product . ' ORDER BY i.position
        ');
        $img_type = $this->getSmallestImgType();
        foreach ($images as $i => $img) {
            $images[$i]['src'] = $this->getSrcById($img['id_image'], $img_type);
        }

        return $images;
    }

    public function getSrcById($id_image, $img_type)
    {
        $src = _THEME_PROD_DIR_ . Image::getImgFolderStatic($id_image);
        $src .= $id_image . '-' . $img_type . '.jpg';

        return $src;
    }

    public function getSmallestImgType()
    {
        return $this->db->getValue('
            SELECT name FROM ' . _DB_PREFIX_ . 'image_type WHERE products = 1 ORDER BY width ASC
        ');
    }

    public function getProductAttributeOptions($id_product)
    {
        $p_obj = new Product($id_product);
        $attributes = $p_obj->getAttributesGroups($this->context->language->id);
        $sorted_data = [];
        foreach ($attributes as $a) {
            if ($a['group_name'] && $a['attribute_name']) {
                $sorted_data[$a['group_name']][$a['attribute_name']][] = $a['id_product_attribute'];
            }
        }

        return $sorted_data;
    }

    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('configure') == $this->name) {
            if (Tools::isSubmit('ajax') && Tools::isSubmit('handybulkcactions')
                && $action = Tools::getValue('action')) {
                $this->ajaxAction($action);
            }
            $this->context->controller->css_files[$this->_path . 'views/css/back.css?v=' . $this->version] = 'all';
        } else {
            $action_type = false;
            $controller = Tools::getValue('controller');
            $hidden_data = $js_vars = [];
            if ($controller == 'AdminProducts') {
                $this->context->smarty->assign([
                    'currency_sign' => Currency::getDefaultCurrency()->sign,
                    'weight_sign' => Configuration::get('PS_WEIGHT_UNIT'),
                ]);
                if ($id_product = $this->getCurrentID('product')) {
                    $action_type = 'combinations';
                    $this->context->smarty->assign([
                        'assignable_images' => $this->getProductImages($id_product),
                        'attribute_options' => $this->getProductAttributeOptions($id_product),
                    ]);
                } else {
                    $action_type = 'product';
                    $this->context->smarty->assign([
                        'id_root' => Configuration::get('PS_ROOT_CATEGORY'),
                        'structured_categories' => $this->getStructuredCategories(),
                        'f_groups' => $this->getFeatures(),
                        'ba_languages' => array_column(Language::getLanguages(false), 'iso_code', 'id_lang'),
                        'ba_id_lang' => $this->context->language->id,
                    ]);
                    $js_vars['ba_feature_values'] = $this->getStructuredFeatureValues();
                }
            } elseif ($controller == 'AdminCategories' && $id_cat_parent = $this->getCurrentID('cat_parent')) {
                $action_type = 'category';
                $this->context->smarty->assign([
                    'id_root' => Configuration::get('PS_ROOT_CATEGORY'),
                    'structured_categories' => $this->getStructuredCategories(),
                    'groups' => Group::getGroups($this->context->language->id),
                ]);
                $hidden_data['id_cat_parent'] = $id_cat_parent;
            } elseif ($controller == 'AdminCustomers' && !Tools::isSubmit('id_customer')) {
                $action_type = 'customer';
                $this->context->smarty->assign([
                    'groups' => Group::getGroups($this->context->language->id),
                ]);
            }
            if (!empty($action_type)) {
                $this->addJqueryBO();
                $this->context->controller->js_files[] = $this->_path . 'views/js/back.js?v=' . $this->version;
                $this->context->controller->css_files[$this->_path . 'views/css/back.css?v=' . $this->version] = 'all';
                $this->context->smarty->assign([
                    'ba' => $this,
                    'ba_type' => $action_type,
                    'is_16' => $this->is_16,
                    'hidden_data' => $hidden_data,
                ]);
                $html = $this->display($this->local_path, 'views/templates/admin/additional-html.tpl');
                $js_vars += [
                    'ba_ajax_path' => 'index.php?controller=AdminModules&configure=' . $this->name
                        . '&token=' . Tools::getAdminTokenLite('AdminModules') . '&ajax=1&handybulkcactions=1',
                    'ba_type' => $action_type,
                    'ba_html' => preg_replace('/\s+/S', ' ', $html),
                    'ba_savedTxt' => $this->l('Saved'),
                    'is_16' => $this->is_16,
                ];
                Media::addJsDef($js_vars);
            }
        }
    }

    public function addJqueryBO()
    {
        if (empty($this->context->jqueryAdded)) {
            version_compare(_PS_VERSION_, '1.7.6.0', '>=') ? $this->context->controller->setMedia()
                : $this->context->controller->addJquery();
            $this->context->jqueryAdded = 1;
        }
    }

    public function getSelectedItems()
    {
        if (!$selected_items = Tools::getValue('selected_items')) {
            $this->throwError($this->l('Please select at least one item from the list'));
        }

        return $selected_items;
    }

    public function ajaxAction($action)
    {
        $ret = ['refresh_required' => 0];
        if (count($this->shopIDs()) > 1 && in_array($action, ['setDefaultCategory', 'setPrice'])) {
            $this->throwError($this->l('To perform this action, switch context to a single shop'));
        }
        switch ($action) {
            case 'addToCategory':
            case 'removeFromCategory':
            case 'setDefaultCategory':
                if (!$id_cat = (int) Tools::getValue('id_cat')) {
                    $this->throwError($this->l('Please select category'));
                }
                $selected_items = $this->getSelectedItems();
                $associated_products = array_column($this->db->executeS('
                    SELECT id_product FROM ' . _DB_PREFIX_ . 'category_product
                    WHERE id_category = ' . (int) $id_cat . '
                        AND id_product IN (' . $this->sqlIDs($selected_items) . ')
                '), 'id_product', 'id_product');
                foreach ($selected_items as $id_product) {
                    $p_obj = new Product($id_product);
                    $has_category = isset($associated_products[$id_product]);
                    $is_default = $has_category && $p_obj->getDefaultCategory() == $id_cat;
                    if ($action == 'addToCategory' && !$has_category) {
                        $p_obj->addToCategories([$id_cat]);
                        $this->saveObject($p_obj, ['hook' => 1]);
                    } elseif ($action == 'removeFromCategory' && $has_category) {
                        if ($is_default) {
                            $this->warnings[] = $this->objectLabel($p_obj) . ': '
                                . $this->l('Can not be removed from default category');
                        } else {
                            $p_obj->deleteCategory($id_cat);
                            $this->saveObject($p_obj, ['hook' => 1]);
                        }
                    } elseif ($action == 'setDefaultCategory' && !$is_default) {
                        if (!$has_category) {
                            $p_obj->addToCategories([$id_cat]);
                        }
                        $p_obj->id_category_default = $id_cat;
                        $this->saveObject($p_obj);
                    }
                }
                $ret['displayed_value'] = $this->getCategoryNameById($id_cat);
                break;
            case 'setPrice':
                $price = Tools::getValue('price');
                if (!Validate::isPrice($price)) {
                    $this->throwError($this->l('Incorrect price format'));
                }
                foreach ($this->getSelectedItems() as $id_product) {
                    $p_obj = new Product($id_product);
                    if ($p_obj->price != $price) {
                        $p_obj->price = $price;
                        $this->saveObject($p_obj);
                        Product::flushPriceCache();
                        $price_tax_incl_no_reduc = $p_obj->getPrice(true, null, 6, null, false, false);
                        $ret['final_price_' . $id_product] = Tools::displayPrice($price_tax_incl_no_reduc);
                    }
                }
                $ret['displayed_value'] = Tools::displayPrice($price);
                break;
            case 'addFeatureValue':
            case 'removeFeatureValue':
                $id_feature = (int) Tools::getValue('id_feature');
                $id_feature_value = (int) Tools::getValue('id_feature_value');
                if (!$id_feature || !$id_feature_value) {
                    $this->throwError($this->l('Please select a value'));
                }
                $selected_items = $this->getSelectedItems();
                $associated_products = array_column($this->db->executeS('
                    SELECT id_product FROM ' . _DB_PREFIX_ . 'feature_product
                    WHERE id_feature_value = ' . (int) $id_feature_value . '
                        AND id_product IN (' . $this->sqlIDs($selected_items) . ')
                '), 'id_product', 'id_product');
                if ($action == 'addFeatureValue') {
                    $rows = [];
                    $selected_items = array_diff($selected_items, $associated_products);
                    foreach ($selected_items as $id_product) {
                        $rows[] = '(' . (int) $id_feature . ', ' . (int) $id_product . ', '
                            . (int) $id_feature_value . ')';
                    }
                    if ($rows) {
                        $this->db->execute('
                            REPLACE INTO ' . _DB_PREFIX_ . 'feature_product VALUES ' . implode(', ', $rows) . '
                        ');
                        if ($this->is_16) {
                            // only 1 feature value per group in PS 1.6
                            $this->db->execute('
                                DELETE FROM ' . _DB_PREFIX_ . 'feature_product
                                WHERE id_feature = ' . (int) $id_feature . '
                                    AND id_feature_value <> ' . (int) $id_feature_value . '
                                    AND id_product IN (' . $this->sqlIDs($selected_items) . ')
                            ');
                        }
                    }
                } elseif ($selected_items = array_intersect($selected_items, $associated_products)) {
                    $this->db->execute('
                        DELETE FROM ' . _DB_PREFIX_ . 'feature_product
                        WHERE id_feature_value = ' . (int) $id_feature_value . '
                            AND id_product IN (' . $this->sqlIDs($selected_items) . ')
                    ');
                }
                foreach ($selected_items as $id_product) {
                    $p_obj = new Product($id_product);
                    $this->saveObject($p_obj, ['hook' => 1]);
                }
                break;
            case 'replaceText':
                $replace = Tools::getValue('replace');
                $from = strip_tags($replace['from']);
                $to = strip_tags($replace['to']);
                $id_lang = (int) $replace['id_lang'];
                if (!$from) {
                    $this->throwError($this->l('Please enter text to replace'));
                } elseif ($from != $replace['from'] || $to != $replace['to']) {
                    $this->throwError($this->l('Incorrect text format'));
                }
                $translatable_fields = $this->getTranslatableFields('Product');
                unset($translatable_fields['link_rewrite']);
                if (strpos($to, ',') !== false || Tools::strtolower($to) !== $to) {
                    unset($translatable_fields['meta_keywords']);
                }
                $ret['upd_names'] = [];
                foreach ($this->getSelectedItems() as $id_product) {
                    $p_obj = new Product($id_product);
                    $upd = [];
                    foreach (array_keys($translatable_fields) as $field_name) {
                        $upd_value = str_replace($from, $to, $p_obj->{$field_name}[$id_lang]);
                        if ($upd_value != $p_obj->{$field_name}[$id_lang]) {
                            $p_obj->{$field_name}[$id_lang] = $upd[$field_name][$id_lang] = $upd_value;
                        }
                    }
                    if ($upd && $this->saveObject($p_obj, ['lang' => $upd, 'hook' => 1])
                        && isset($upd['name']) && $id_lang == $this->context->language->id) {
                        $ret['upd_names'][$id_product] = $p_obj->name[$id_lang];
                    }
                }
                break;
            case 'moveToParent':
            case 'copyToParent':
                if (!$id_cat_destination = (int) Tools::getValue('id_cat')) {
                    $this->throwError($this->l('Please select parent category'));
                }
                $current_page_id_parent = Tools::getValue('id_cat_parent', $this->context->shop->getCategory());
                foreach ($this->getSelectedItems() as $id_category) {
                    $category = new Category($id_category);
                    $category->id_parent = $id_cat_destination;
                    if ($action == 'copyToParent') {
                        $category->id = '';
                    }
                    if (!$this->parentCanBeSet($id_category, $id_cat_destination)) {
                        $this->warnings[] = $this->objectLabel($category) . ': '
                            . $this->l('Subcategory can not be used as a parent category');
                    } else {
                        $this->saveObject($category);
                    }
                }
                if (!empty($this->processed_items)) {
                    if ($id_cat_destination == $current_page_id_parent && $action == 'copyToParent') {
                        $ret['refresh_required'] = 1;
                    } elseif ($id_cat_destination != $current_page_id_parent && $action == 'moveToParent') {
                        $ret['remove_processed'] = 1;
                    }
                }
                break;
            case 'addGroupAccess':
            case 'removeGroupAccess':
                if (!$id_group = (int) Tools::getValue('id_group')) {
                    $this->throwError($this->l('Please select a group'));
                }
                foreach ($this->getSelectedItems() as $id_category) {
                    $category = new Category($id_category);
                    $saved = false;
                    if ($action == 'addGroupAccess') {
                        // not using $category->addGroups() because it doesnt include handling duplicate keys
                        $saved = $this->db->execute('
                            REPLACE INTO ' . _DB_PREFIX_ . 'category_group
                            VALUES (' . (int) $id_category . ', ' . (int) $id_group . ')
                        ');
                    } else {
                        $other_groups_count = $this->db->getValue('
                            SELECT COUNT(id_group)
                            FROM ' . _DB_PREFIX_ . 'category_group
                            WHERE id_category = ' . (int) $id_category . '
                            AND id_group <> ' . (int) $id_group . '
                        ');
                        if ($other_groups_count) {
                            $saved = $this->db->execute('
                                DELETE FROM ' . _DB_PREFIX_ . 'category_group
                                WHERE id_category = ' . (int) $id_category . '
                                AND id_group = ' . (int) $id_group . '
                            ');
                        } else {
                            $this->warnings[] = $this->objectLabel($category) . ': '
                                . $this->l('No other groups available');
                        }
                    }
                    if ($saved) {
                        $this->processed_items[$id_category] = $id_category;
                    }
                }
                break;
            case 'addToGroup':
            case 'removeFromGroup':
            case 'setDefaultGroup':
                if (!$id_group = (int) Tools::getValue('id_group')) {
                    $this->throwError($this->l('Please select a group'));
                }
                foreach ($this->getSelectedItems() as $id_customer) {
                    $customer = new Customer($id_customer);
                    if ($action == 'removeFromGroup') {
                        if ($id_group != $customer->id_default_group) {
                            if ($this->db->execute('
                                DELETE FROM ' . _DB_PREFIX_ . 'customer_group
                                WHERE id_customer = ' . (int) $id_customer . '
                                AND id_group = ' . (int) $id_group . '
                            ')) {
                                $this->processed_items[$id_customer] = $id_customer;
                            }
                        } else {
                            $this->warnings[] = $id_customer . ' - ' . $customer->firstname
                                . ' ' . $customer->lastname . ': ' . $this->l('Selected group is set as default');
                        }
                    } else {
                        $customer->addGroups([$id_group]);
                        if ($action == 'setDefaultGroup') {
                            $customer->id_default_group = $id_group;
                        }
                        $this->saveObject($customer);
                    }
                }
                break;
            case 'assignImages':
            case 'setUnitPriceImpact':
            case 'setPriceImpact':
            case 'setWeightImpact':
                if (!$combination_ids = Tools::getValue('selected_combinations')) {
                    $this->throwError($this->l('Please select at least one combination'));
                }
                foreach ($combination_ids as $id_combination) {
                    $combination = new Combination($id_combination);
                    if ($action == 'assignImages') {
                        $image_ids = Tools::getValue('selected_images');
                        $combination->setImages($image_ids);
                        $this->saveObject($combination);
                    } else {
                        $types = [
                            'setPriceImpact' => 'price',
                            'setUnitPriceImpact' => 'unit_price',
                            'setWeightImpact' => 'weight',
                        ];
                        $type = $types[$action];
                        $impact = Tools::getValue($type . '_impact');
                        $multiplier = Tools::substr($impact, 0, 1) != '-' ? 1 : -1;
                        $impact = (float) preg_replace('/[^0-9.]/', '', str_replace(',', '.', $impact));
                        $impact = $impact * $multiplier;
                        if ($type == 'unit_price') {
                            $type .= '_impact';
                        }
                        $combination->$type = Tools::ps_round($impact, 6);
                        if ($type != 'unit_price_impact') {
                            $ret['applied_impacts'][$combination->id] = $this->formatNumber($combination->$type, $type);
                        }
                        $this->saveObject($combination);
                    }
                }
                break;
            case 'getUpdatedProducAttributesOptions':
                if ($id_product = (int) Tools::getValue('id_product')) {
                    $this->context->smarty->assign([
                        'options' => $this->getProductAttributeOptions($id_product),
                    ]);
                    $ret['html'] = $this->display($this->local_path, 'views/templates/admin/multiselect.tpl');
                }
                break;
        }
        if (!empty($this->warnings)) {
            $ret['warnings'] = $this->warnings;
        }
        $ret['processed_items'] = array_values($this->formatIDs($this->processed_items)); // JS expects plain array
        exit(json_encode($ret));
    }

    public function formatNumber($number, $type)
    {
        if ($type == 'weight') {
            $number = number_format($number, 6, '.', '') . Configuration::get('PS_WEIGHT_UNIT');
        } elseif ($type == 'price') {
            $number = Tools::displayPrice($number);
        }

        return $number;
    }

    public function getTranslatableFields($class_name)
    {
        $fields = [];
        if (class_exists($class_name)) {
            $obj = new $class_name();
            if (method_exists($obj, 'getDefinition') && $definition = ObjectModel::getDefinition($obj)) {
                foreach ($definition['fields'] as $field_name => $data) {
                    if (!empty($data['lang'])) {
                        $fields[$field_name] = !empty($data['size']) ? $data['size'] : 0;
                    }
                }
            }
        }

        return $fields;
    }

    public function saveObject($obj, $custom_upd = [])
    {
        try {
            if ($custom_upd && get_class($obj) == 'Product') {
                // avoid $p_obj->save() because it can override individual shop prices in multishop context
                $this->updateProductData($obj, $custom_upd);
            } else {
                $obj->save();
            }
            $this->processed_items[$obj->id] = $obj->id;
        } catch (Exception $e) {
            $this->warnings[] = $this->objectLabel($obj) . ': ' . $e->getMessage();

            return false;
        }

        return true;
    }

    public function updateProductData($p_obj, $upd = [])
    {
        $result = true;
        if (!empty($upd['lang'])) {
            $sql = $set = [];
            foreach ($p_obj->getFieldsLang() as $id_lang => $values) { // $values are validated and formatted
                foreach ($values as $name => $sanitized_value) {
                    if (isset($upd['lang'][$name][$id_lang])) {
                        $set[$id_lang][$name] = '`' . bqSQL($name) . '` = \'' . $sanitized_value . '\'';
                    }
                }
            }
            foreach ($set as $id_lang => $fields) {
                $sql[] = 'UPDATE ' . _DB_PREFIX_ . 'product_lang
                    SET ' . implode(', ', $fields) . '
                    WHERE `id_product` = ' . (int) $p_obj->id . '
                        AND `id_shop` IN (' . $this->shopIDs(true) . ')
                        AND `id_lang` = ' . (int) $id_lang;
            }
            $result = $this->runSql($sql);
            if ($sql && $result && in_array($p_obj->visibility, ['both', 'search'])
                && Configuration::get('PS_SEARCH_INDEXATION')) {
                Search::indexation(false, $p_obj->id); // update search index
            }
        }
        if (!empty($upd['hook'])) {
            Hook::exec('actionProductUpdate', ['id_product' => $p_obj->id, 'product' => $p_obj]);
        }

        return $result;
    }

    public function objectLabel($obj)
    {
        return isset($obj->name) && is_array($obj->name) && isset($obj->name[$this->context->language->id])
                ? $obj->id . ' - ' . htmlentities($obj->name[$this->context->language->id])
                : get_class($obj) . ' ' . $obj->id;
    }

    public function parentCanBeSet($id_cat, $id_parent_new)
    {
        $c_data = $this->db->getRow('
            SELECT nright, nleft FROM ' . _DB_PREFIX_ . 'category WHERE id_category = ' . (int) $id_cat . '
        ');

        return !$c_data ? true : !$this->db->getValue('
            SELECT * FROM ' . _DB_PREFIX_ . 'category
            WHERE nright < ' . (int) $c_data['nright'] . ' AND nleft > ' . (int) $c_data['nleft'] . '
            AND id_category = ' . (int) $id_parent_new . '
        ');
    }

    public function getStructuredCategories()
    {
        $categories = $this->db->executeS('
            SELECT c.id_category, c.id_parent, cl.name
            FROM ' . _DB_PREFIX_ . 'category c
            ' . Shop::addSqlAssociation('category', 'c') . '
            LEFT JOIN ' . _DB_PREFIX_ . 'category_lang cl
                ON c.id_category = cl.id_category' . Shop::addSqlRestrictionOnLang('cl') . '
            WHERE id_lang = ' . (int) $this->context->language->id . '
        ');
        $structured_categories = [];
        foreach ($categories as $c) {
            $structured_categories[$c['id_parent']][$c['id_category']] = $c;
        }

        return $structured_categories;
    }

    public function getCategoryNameById($id)
    {
        return $this->db->getValue('
            SELECT name FROM ' . _DB_PREFIX_ . 'category_lang WHERE id_category = ' . (int) $id . '
            AND id_lang = ' . (int) $this->context->language->id . ' AND id_shop = ' . (int) $this->context->shop->id . '
        ');
    }

    public function formatCategoryID($id)
    {
        if (empty($this->max_id_cat_digits)) {
            $this->max_id_cat_digits = Tools::strlen(
                $this->db->getValue('SELECT MAX(id_category) FROM ' . _DB_PREFIX_ . 'category')
            );
        }
        $id = str_pad($id, $this->max_id_cat_digits, '0', STR_PAD_LEFT);

        return $id;
    }

    public function getFeatures()
    {
        return array_column($this->db->executeS('
            SELECT DISTINCT(fl.id_feature), fl.name
            FROM ' . _DB_PREFIX_ . 'feature_lang fl ' . Shop::addSqlAssociation('feature', 'fl') . '
            WHERE fl.id_lang = ' . (int) $this->context->language->id . '
            ORDER BY fl.name ASC
        '), 'name', 'id_feature');
    }

    public function getStructuredFeatureValues()
    {
        $structured_data = [];
        $rows = $this->db->executeS('
            SELECT fvl.id_feature_value, fvl.value, fv.id_feature
            FROM ' . _DB_PREFIX_ . 'feature_value_lang fvl
            INNER JOIN ' . _DB_PREFIX_ . 'feature_value fv
                ON fv.id_feature_value = fvl.id_feature_value
            ' . Shop::addSqlAssociation('feature', 'fv') . '
            WHERE fvl.id_lang = ' . (int) $this->context->language->id . '
        ');
        foreach ($rows as $row) {
            $structured_data[$row['id_feature']][$row['id_feature_value']] = $row['value'];
        }

        return $structured_data;
    }

    public function formatIDs($ids, $return_string = false)
    {
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        $ids = array_map('intval', $ids);
        $ids = array_combine($ids, $ids);
        unset($ids[0]);

        return $return_string ? implode(',', $ids) : $ids;
    }

    public function sqlIDs($ids)
    {
        return $this->formatIDs($ids, true);
    }

    public function shopIDs($implode = false)
    {
        if (!isset($this->shop_ids)) {
            $this->shop_ids = Shop::getContextListShopID();
        }

        return $this->formatIDs($this->shop_ids, $implode);
    }

    public function runSql($sql)
    {
        foreach ($sql as $s) {
            if (!$this->db->execute($s)) {
                return false;
            }
        }

        return true;
    }

    public function throwError($errors)
    {
        if (!is_array($errors)) {
            $errors = [$errors];
        }
        $html = implode('<br>', $errors);
        if (!Tools::isSubmit('ajax')) {
            return $this->displayError($html);
        }
        exit(json_encode(['errors' => $html]));
    }
}
