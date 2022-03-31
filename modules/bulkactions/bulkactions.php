<?php
/**
* 2007-2019 Amazzing
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
*
*  @author    Amazzing <mail@amazzing.ru>
*  @copyright 2007-2019 Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class BulkActions extends Module
{
    public function __construct()
    {
        if (!defined('_PS_VERSION_')) {
            exit;
        }
        $this->name = 'bulkactions';
        $this->tab = 'quick_bulk_update';
        $this->version = '1.2.2';
        $this->author = 'Amazzing';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->module_key = '528d5c81f014cfd31425f42f3c635345';

        parent::__construct();

        $this->displayName = $this->l('Handy bulk actions');
        $this->description = $this->l('Advanced bulk action tools for products/combinations/categories/customers/');
        $this->db = Db::getInstance();
        $this->is_17 = Tools::substr(_PS_VERSION_, 0, 3) === '1.7';
        $this->warnings = $this->processed_items = array();
    }

    public function install()
    {
        if (!parent::install() ||
            !$this->registerHook('displayBackOfficeHeader')) {
            return false;
        }
        return true;
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    public function getContent()
    {
        $this->context->smarty->assign(array(
            'version' => $this->version,
            'info_links' => array(
                'changelog' => $this->_path.'Readme.md?v='.$this->version,
                'documentation' => $this->_path.'readme_en.pdf?v='.$this->version,
                'contact' => 'https://addons.prestashop.com/en/contact-us?id_product=21913',
                'modules' => 'http://addons.prestashop.com/en/2_community-developer?contributor=64815',
            ),
        ));
        return $this->display(__FILE__, 'views/templates/admin/configure.tpl');
    }

    public function getSrcById($id_image, $img_type)
    {
        $src = _THEME_PROD_DIR_.Image::getImgFolderStatic($id_image);
        $src .= $id_image.'-'.$img_type.'.jpg';
        return $src;
    }

    public function getSmallestImgType()
    {
        $type = $this->db->getRow('
            SELECT * FROM '._DB_PREFIX_.'image_type WHERE products = 1
            ORDER BY width ASC
        ');
        return $type;
    }

    public function getCurrentID($type = 'product')
    {
        $id = Tools::getValue('id_'.$type);
        if ($type == 'cat_parent' && !Tools::isSubmit('updatecategory')) {  // 1.6 - 1.7.5
            $id = Tools::getValue('id_category', $this->context->shop->getCategory());
        }
        if ($this->is_17 && $request = $this->getSfRequest()) {
            $id = $request->get('id');
            if ($type == 'cat_parent' && $request->get('_route') == 'admin_categories_index' &&
                !$id = $request->get('categoryId')) { // 1.7.6+
                $id = $this->context->shop->getCategory();
            }
        }
        return (int)$id;
    }

    public function getSfRequest()
    {
        $sf_container = PrestaShop\PrestaShop\Adapter\SymfonyContainer::getInstance();
        return $sf_container->get('request_stack')->getCurrentRequest();
    }

    public function getProductAttributeOptions($product)
    {
        $attributes = $product->getAttributesGroups($this->context->language->id);
        $sorted_data = $options = array();
        foreach ($attributes as $a) {
            if ($a['group_name'] && $a['attribute_name']) {
                $name = $a['group_name'].': '.$a['attribute_name'];
                $sorted_data[$name][] = $a['id_product_attribute'];
            }
        }
        ksort($sorted_data);
        foreach ($sorted_data as $name => $combination_ids) {
            // array is required here for proper sorting in js
            $options[] = array(
                'name' => $name.' ('.count($combination_ids).')',
                'value' => implode('-', $combination_ids)
            );
        }
        return $options;
    }

    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::isSubmit('ajax') && Tools::isSubmit('handybulkcactions') && $action = Tools::getValue('action')) {
            $this->ajaxAction($action);
        }
        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->css_files[$this->_path.'views/css/back.css?v='.$this->version] = 'all';
        }
        $action_type = false;
        $controller = Tools::getValue('controller');
        $hidden_data = array();
        if ($controller == 'AdminProducts') {
            $this->context->smarty->assign(array(
                'currency_sign' => Currency::getDefaultCurrency()->sign,
                'weight_sign' => Configuration::get('PS_WEIGHT_UNIT'),
            ));
            if ($id_product = $this->getCurrentID('product')) {
                $action_type = 'combinations';
                $product = new Product($id_product);
                $images = $product->getImages($this->context->language->id);
                $img_type = $this->getSmallestImgType();
                foreach ($images as $i => $img) {
                    $images[$i]['src'] = $this->getSrcById($img['id_image'], $img_type['name']);
                }
                $this->context->smarty->assign(array(
                    'assignable_images' => $images,
                    'attribute_options' => $this->getProductAttributeOptions($product),
                ));
            } else {
                $action_type = 'product';
                $this->context->smarty->assign(array(
                    'id_root' => Configuration::get('PS_ROOT_CATEGORY'),
                    'structured_categories' => $this->getStructuredCategories(),
                ));
            }
        } elseif ($controller == 'AdminCategories' && $id_cat_parent = $this->getCurrentID('cat_parent')) {
            $action_type = 'category';
            $this->context->smarty->assign(array(
                'id_root' => Configuration::get('PS_ROOT_CATEGORY'),
                'structured_categories' => $this->getStructuredCategories(),
                'groups' => Group::getGroups($this->context->language->id),
            ));
            $hidden_data['id_cat_parent'] = $id_cat_parent;
        } elseif ($controller == 'AdminCustomers' && !Tools::isSubmit('id_customer')) {
            $action_type = 'customer';
            $this->context->smarty->assign(array(
                'groups' => Group::getGroups($this->context->language->id),
            ));
        }
        if (!empty($action_type)) {
            $this->addJqueryBO();
            $this->context->controller->js_files[] = $this->_path.'views/js/back.js?v='.$this->version;
            $this->context->controller->css_files[$this->_path.'views/css/back.css?v='.$this->version] = 'all';
            $this->context->smarty->assign(array(
                'ba' => $this,
                'ba_type' => $action_type,
                'is_17' => $this->is_17,
                'hidden_data' => $hidden_data,
            ));
            $html = $this->display($this->local_path, 'views/templates/admin/additional-html.tpl');
            $html = preg_replace('/\s+/S', ' ', $html);
            $js_variables = array(
                'ba_ajax_path' => 'index.php?controller=AdminModules&configure='.$this->name.
                '&token='.Tools::getAdminTokenLite('AdminModules').'&ajax=1&handybulkcactions=1',
                'ba_type' => $action_type,
                'ba_html' => $html,
                'savedTxt' => $this->l('Saved'),
                'is_17' => $this->is_17,
            );
            $js = '<script type="text/javascript">'; // plain js for retro-compatibility
            foreach ($js_variables as $name => $value) {
                $js .= 'var '.$name.' = \''.$value.'\'; ';
            }
            $js .= '</script>';
            return $js;
        }
    }

    public function addJqueryBO()
    {
        if (empty($this->context->jqueryAdded)) {
            $this->is_17 ? $this->context->controller->setMedia() : $this->context->controller->addJquery();
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
        $ret = array('refresh_required' => 0);
        switch ($action) {
            case 'addToCategory':
            case 'removeFromCategory':
            case 'setDefaultCategory':
                $selected_items = $this->getSelectedItems();
                if (!$id_cat = Tools::getValue('id_cat')) {
                    $this->throwError($this->l('Please select category'));
                }
                foreach ($selected_items as $id_product) {
                    $product = new Product($id_product);
                    $saving_required = true;
                    if ($action == 'addToCategory') {
                        $saving_required &= $product->addToCategories(array($id_cat));
                    } elseif ($action == 'removeFromCategory') {
                        if ($product->getDefaultCategory() != $id_cat) {
                            $saving_required &= $product->deleteCategory($id_cat);
                        } else {
                            $this->warnings[] = $id_product.' - '.$product->name[$this->context->language->id].
                            ': '.$this->l('Can not be removed from default category');
                            $saving_required = false;
                        }
                    } elseif ($action == 'setDefaultCategory') {
                        $has_category = $this->db->getValue('
                            SELECT id_category
                            FROM '._DB_PREFIX_.'category_product
                            WHERE id_category = '.(int)$id_cat.'
                            AND id_product = '.(int)$id_product.'
                        ');
                        if (!$has_category) {
                            $saving_required &= $product->addToCategories(array($id_cat));
                        }
                        $product->id_category_default = $id_cat;
                    }
                    if ($saving_required) {
                        $this->saveObject($product);
                    }
                }
                $ret['displayed_value'] = utf8_encode($this->getCategoryNameById($id_cat));
                break;
            case 'setPrice':
                $price = Tools::getValue('price');
                if (!Validate::isPrice($price)) {
                    $this->throwError($this->l('Incorrect price format'));
                }
                foreach ($this->getSelectedItems() as $id_product) {
                    $product = new Product($id_product);
                    $product->price = $price;
                    $this->saveObject($product);
                    $ret['final_price_'.$id_product] = utf8_encode(Tools::displayPrice($product->getPrice()));
                }
                $ret['displayed_value'] = utf8_encode(Tools::displayPrice($price));
                break;
            case 'moveToParent':
            case 'copyToParent':
                $selected_items = $this->getSelectedItems();
                if (!$id_cat_destination = Tools::getValue('id_cat')) {
                    $this->throwError($this->l('Please select parent category'));
                }
                $current_page_id_parent = Tools::getValue('id_cat_parent', $this->context->shop->getCategory());
                foreach ($selected_items as $id_category) {
                    $category = new Category($id_category);
                    $category->id_parent = $id_cat_destination;
                    if ($action == 'copyToParent') {
                        $category->id = '';
                    }
                    if (!$this->parentCanBeSet($id_category, $id_cat_destination)) {
                        $this->warnings[] = $id_category.' - '.$category->name[$this->context->language->id].': '
                        .$this->l('Subcategory can not be used as a parent category');
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
                $selected_items = $this->getSelectedItems();
                if (!$id_group = Tools::getValue('id_group')) {
                    $this->throwError($this->l('Please select a group'));
                }
                foreach ($selected_items as $id_category) {
                    $category = new Category($id_category);
                    $saved = false;
                    if ($action == 'addGroupAccess') {
                        // not using $category->addGroups() because it doesnt include handling duplicate keys
                        $saved = $this->db->execute('
                            REPLACE INTO '._DB_PREFIX_.'category_group
                            VALUES ('.(int)$id_category.', '.(int)$id_group.')
                        ');
                    } else {
                        $other_groups_count = $this->db->getValue('
                            SELECT COUNT(id_group)
                            FROM '._DB_PREFIX_.'category_group
                            WHERE id_category = '.(int)$id_category.'
                            AND id_group <> '.(int)$id_group.'
                        ');
                        if ($other_groups_count) {
                            $saved = $this->db->execute('
                                DELETE FROM '._DB_PREFIX_.'category_group
                                WHERE id_category = '.(int)$id_category.'
                                AND id_group = '.(int)$id_group.'
                            ');
                        } else {
                            $this->warnings[] = $id_category.' - '.$category->name[$this->context->language->id].
                            ': '.$this->l('No other groups available');
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
                $selected_items = $this->getSelectedItems();
                if (!$id_group = Tools::getValue('id_group')) {
                    $this->throwError($this->l('Please select a group'));
                }
                foreach ($selected_items as $id_customer) {
                    $customer = new Customer($id_customer);
                    if ($action == 'removeFromGroup') {
                        if ($id_group != $customer->id_default_group) {
                            if ($this->db->execute('
                                DELETE FROM '._DB_PREFIX_.'customer_group
                                WHERE id_customer = '.(int)$id_customer.'
                                AND id_group = '.(int)$id_group.'
                            ')) {
                                $this->processed_items[$id_customer] = $id_customer;
                            }
                        } else {
                            $this->warnings[] = $id_customer.' - '.$customer->firstname.' '.$customer->lastname.
                            ': '.$this->l('Selected group is set as default');
                        }
                    } else {
                        $customer->addGroups(array($id_group));
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
                        $types = array(
                            'setPriceImpact' => 'price',
                            'setUnitPriceImpact' => 'unit_price',
                            'setWeightImpact' => 'weight',
                        );
                        $type = $types[$action];
                        $impact = Tools::getValue($type.'_impact');
                        $multiplier = Tools::substr($impact, 0, 1) != '-' ? 1 : - 1;
                        $impact = (float)preg_replace('/[^0-9.]/', '', str_replace(',', '.', $impact));
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
                if ($id_product = Tools::getValue('id_product')) {
                    $product = new Product($id_product);
                    $ret['options'] = $this->getProductAttributeOptions($product);
                }
                break;
        }
        if (!empty($this->warnings)) {
            $ret['warnings'] = utf8_encode(implode('<br>', $this->warnings)).'<br>';
        }
        $ret['processed_items'] = array_values(array_map('intval', $this->processed_items));
        exit(Tools::jsonEncode($ret));
    }

    public function formatNumber($number, $type)
    {
        if ($type == 'weight') {
            $number = number_format($number, 6, '.', '').Configuration::get('PS_WEIGHT_UNIT');
        } elseif ($type == 'price') {
            $number = Tools::displayPrice($number);
        }
        return $number;
    }

    public function saveObject($obj)
    {
        try {
            $obj->save();
            $this->processed_items[$obj->id] = $obj->id;
        } catch (Exception $e) {
            $id_lang = $this->context->language->id;
            $name = (is_array($obj->name) && isset($obj->name[$id_lang])) ?
            $obj->id.' - '.$obj->name[$id_lang] : get_class($obj).' '.$obj->id;
            $this->warnings[] = $name.': '.$e->getMessage();
            return false;
        }
        return true;
    }

    public function parentCanBeSet($id_cat, $id_parent_new)
    {
        $c_data = $this->db->getRow('
            SELECT nright, nleft FROM '._DB_PREFIX_.'category WHERE id_category = '.(int)$id_cat.'
        ');
        return !$c_data ? true : !$this->db->getValue('
            SELECT * FROM '._DB_PREFIX_.'category
            WHERE nright < '.(int)$c_data['nright'].' AND nleft > '.(int)$c_data['nleft'].'
            AND id_category = '.(int)$id_parent_new.'
        ');
    }

    public function getStructuredCategories()
    {
        $categories = $this->db->executeS('
            SELECT c.id_category, c.id_parent, cl.name
            FROM '._DB_PREFIX_.'category c
            '.Shop::addSqlAssociation('category', 'c').'
            LEFT JOIN '._DB_PREFIX_.'category_lang cl
                ON c.id_category = cl.id_category'.Shop::addSqlRestrictionOnLang('cl').'
            WHERE id_lang = '.(int)$this->context->language->id.'
        ');
        $structured_categories = array();
        foreach ($categories as $c) {
            $structured_categories[$c['id_parent']][$c['id_category']] = $c;
        }
        return $structured_categories;
    }

    public function getCategoryNameById($id)
    {
        return $this->db->getValue('
            SELECT name FROM '._DB_PREFIX_.'category_lang WHERE id_category = '.(int)$id.'
            AND id_lang = '.(int)$this->context->language->id.' AND id_shop = '.(int)$this->context->shop->id.'
        ');
    }

    public function formatCategoryID($id)
    {
        if (empty($this->max_id_cat_digits)) {
            $this->max_id_cat_digits = Tools::strlen(
                $this->db->getValue('SELECT MAX(id_category) FROM '._DB_PREFIX_.'category')
            );
        }
        $id = str_pad($id, $this->max_id_cat_digits, '0', STR_PAD_LEFT);
        return $id;
    }

    public function throwError($errors)
    {
        if (!is_array($errors)) {
            $errors = array($errors);
        }
        $html = implode('<br>', $errors);
        if (!Tools::isSubmit('ajax')) {
            return $this->displayError($html);
        }
        die(Tools::jsonEncode(array('errors' => utf8_encode($html))));
    }
}
