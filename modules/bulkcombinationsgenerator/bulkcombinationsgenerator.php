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

class BulkCombinationsGenerator extends Module
{
    public function __construct()
    {
        if (!defined('_PS_VERSION_')) {
            exit;
        }
        $this->name = 'bulkcombinationsgenerator';
        $this->tab = 'administration';
        $this->version = '2.1.1';
        $this->author = 'Amazzing';
        $this->need_instance = 0;
        $this->module_key = '76fa37d23dff4b3ad6afc517d8a25c44';
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('Bulk combinations generator');
        $this->description = $this->l('Bulk combinations generator');
        $this->db = Db::GetInstance();
        $this->combinations_num_left = 300;
        $this->time_before_reset = 60;
    }

    public function install()
    {
        return parent::install();
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    public function getContent()
    {
        if (Tools::getValue('ajax')) {
            $this->ajaxAction();
        }
        if (Tools::getValue('exportSettings')) {
            $this->exportSettings();
        }
        $this->context->controller->addJquery();
        $this->context->controller->js_files[] = $this->_path.'views/js/back.js?'.$this->version;
        $this->context->controller->css_files[$this->_path.'views/css/back.css?'.$this->version] = 'all';
        $this->context->smarty->assign(array(
            'grouped_attributes' => $this->getGroupedAttributes(),
            'product_filters' => $this->getProductFilters(),
            'combination_fields' => $this->getCombinationFields(),
            'attribute_options_fields' => $this->getAttributeOptionsFields(),
            'duplicate_fields' => $this->getDuplicateFields(),
            'reference_variables' => $this->getRefVariables(),
            'version' => $this->version,
            'info_links' => $this->getInfoLinks(),
        ));
        $js_def = array(
            'l' => array(
                'loading' => $this->l('Loading...'),
                'complete' => $this->l('COMPLETE!'),
                'dont_close' => $this->l('Please do not close this browser tab'),
                'products_processed' => $this->l('Products processed: %s'),
                'time_spent' => $this->l('Time spent: %s'),
                'time_remaining' => $this->l('Approximate remaining time: %s'),
                'check_console' => $this->l('Error. Check console log'),
            ),
        );
        $js = $this->addJsDef($js_def);
        return $js.$this->display(__FILE__, 'views/templates/admin/configure.tpl');
    }

    public function addJsDef($js_def)
    {
        $js = '<script type="text/javascript">'; // plain js for retro-compatibility
        foreach ($js_def as $name => $value) {
            $value = is_array($value) ? Tools::jsonEncode($value) : '\''.str_replace("'", "\'", $value).'\'';
            $js .= "\nvar $name = $value;";
        }
        $js .= "\n </script>";
        return $js;
    }

    public function getProductFilters()
    {
         $filters = array(
            'id_category' => array(
                'label' => $this->l('Categories'),
                'options' => $this->getOptions('category'),
                'id_parent' => Configuration::get('PS_ROOT_CATEGORY'),
                'col' => array('group' => 12, 'label' => 2, 'value' => 10),
            ),
            'id_manufacturer' => array(
                'label' => $this->l('Manufacturers'),
                'options' => $this->getOptions('manufacturer'),
                'col' => array('group' => 4, 'label' => 2, 'value' => 3),
            ),
            'id_supplier' => array(
                'label' => $this->l('Suppliers'),
                'options' => $this->getOptions('supplier'),
                'col' => array('group' => 4, 'label' => 2, 'value' => 3),
            ),
            'id_product' => array(
                'label' => $this->l('Product IDs'),
                'tooltip' => $this->l('Separated by commas'),
                'col' => array('group' => 4, 'label' => 2, 'value' => 3),
            ),
        );
        return $filters;
    }

    public function getOptions($type)
    {
        $options = array();
        $id_lang = $this->context->language->id;
        switch ($type) {
            case 'manufacturer':
            case 'supplier':
                $items = $this->db->executeS('SELECT * FROM '._DB_PREFIX_.pSQL($type));
                $options  = array();
                foreach ($items as $row) {
                    $options[$row['id_'.$type]] = $row['name'];
                }
                break;
            case 'category':
                $categories = $this->db->executeS('
                    SELECT * FROM '._DB_PREFIX_.'category c
                    '.Shop::addSqlAssociation('category', 'c').'
                    LEFT JOIN '._DB_PREFIX_.'category_lang cl
                        ON c.id_category = cl.id_category
                    WHERE id_lang = '.(int)$id_lang.'
                ');
                foreach ($categories as $cat) {
                    $options[$cat['id_parent']][$cat['id_category']] = $cat['name'];
                }
                break;
        }
        return $options;
    }

    public function getCombinationFields()
    {
        $p_suffix = Currency::getDefaultCurrency()->sign;
        $w_suffix = Configuration::get('PS_WEIGHT_UNIT');
        $fields = array( // keys should be exactly same as in database
            'price' => array('name' => $this->l('Price impact'), 'suffix' => $p_suffix),
            'unit_price_impact' => array('name' => $this->l('Unit price impact'), 'suffix' => $p_suffix),
            'wholesale_price' => array('name' => $this->l('Wholesale price impact'), 'suffix' => $p_suffix),
            'weight' => array('name' => $this->l('Weight impact'), 'suffix' => $w_suffix),
        );
        return $fields;
    }

    public function getAttributeOptionsFields()
    {
        $fields = array(
            'default_combination' => array(
                'label' => $this->l('Default combination'),
                'options' => array(
                    '0' => $this->l('First available'),
                    'min_price' => $this->l('With lowest price'),
                    'max_price' => $this->l('With highest price'),
                    'min_weight' => $this->l('With lowest weight'),
                    'max_weight' => $this->l('With highest weight'),
                ),
            ),
            'quantity' => array(
                'label' => $this->l('Default qty'),
                'value' => 100,
                'override' => 0,
            ),
            'minimal_quantity' => array(
                'label' => $this->l('Min qty for order'),
                'value' => 1,
                'override' => 0,
            ),
            'reference' => array(
                'label' => $this->l('Reference'),
                'override' => 0,
            ),
            'complex_percentage' => array(
                'label' => $this->l('Calculate percentage'),
                'options' => array(
                    '0' => $this->l('Basing on initial value'),
                    '1' => $this->l('Basing on final value including other impacts')
                ),
                'class' => 'complex-percentage hidden',
            ),
        );
        return $fields;
    }

    public function getDuplicateFields()
    {
         $fields = array(
            'id_product_original' => array(
                'label' => $this->l('Original product ID'),
            ),
            'new_reference' => array(
                'label' => $this->l('Pattern for new references'),
            )
        );
        return $fields;
    }

    public function ajaxAction()
    {
        $ret = array();
        $action = Tools::getValue('action');
        switch ($action) {
            case 'getFilteredProductsNum':
                $filters = Tools::getValue('filters');
                $total = count($this->getProductIDs($filters));
                $txt = sprintf($this->l('%d products are ready to be processed'), $total);
                $ret['log_txt'] = utf8_encode($txt);
                break;
            case 'showAttributes':
                $this->context->smarty->assign(array('available_items' => $this->getGroupedAttributes()));
                $ret['content'] = utf8_encode($this->display(__FILE__, 'views/templates/admin/available-items.tpl'));
                $ret['title'] = utf8_encode($this->l('Available attributes'));
                break;
            case 'getDynamicRows':
                $this->context->smarty->assign(array(
                    'rows' => $this->getAttributeRowsData(Tools::getValue('ids')),
                    'combination_fields' => $this->getCombinationFields(),
                ));
                $ret['rows_html'] = utf8_encode($this->display(__FILE__, 'views/templates/admin/dynamic-rows.tpl'));
                break;
            case 'regenerateCombinations':
            case 'updateCombinations':
            case 'deleteCombinations':
            case 'duplicateCombinations':
                $ret += $this->processItems($action);
                break;
            case 'getCombinationsSummary':
                $ret['summary'] = $this->getCombinationsSummary(Tools::getValue('id_product'));
                break;
        }
        exit(Tools::jsonEncode($ret));
    }

    public function getAttributeRowsData($ids)
    {
        $data = array();
        if ($imploded_ids = $this->formatIDs($ids)) {
            $id_lang = $this->context->language->id;
            $data = $this->db->executeS('
                SELECT a.id_attribute AS id, a.id_attribute_group AS id_group, al.name, agl.name AS group_name
                FROM '._DB_PREFIX_.'attribute a '.Shop::addSqlAssociation('attribute', 'a').'
                LEFT JOIN '._DB_PREFIX_.'attribute_lang al
                    ON a.id_attribute = al.id_attribute AND al.id_lang = '.(int)$id_lang.'
                LEFT JOIN '._DB_PREFIX_.'attribute_group_lang agl
                    ON a.id_attribute_group = agl.id_attribute_group AND agl.id_lang = '.(int)$id_lang.'
                WHERE a.id_attribute IN ('.pSQL($imploded_ids).')
                GROUP BY a.id_attribute
                ORDER BY FIELD(a.id_attribute, '.pSQL($imploded_ids).')
            ');
        }
        return $data;
    }

    public function getGroupedAttributes()
    {
        $id_lang = $this->context->language->id;
         $sorted_attributes = $grouped_attributes = array();
        foreach (Attribute::getAttributes($id_lang, true) as $a) {
            $a['id'] = $a['id_attribute'];
            $a['id_group'] = $a['id_attribute_group'];
            $sorted_attributes[$a['id_group']][$a['id']] = $a;
        }
        // not using AttributeGroup::getAttributesGroups($id_lang) because of sorting
        $att_groups = $this->db->executeS('
            SELECT ag.id_attribute_group, agl.name FROM '._DB_PREFIX_.'attribute_group ag
            '.Shop::addSqlAssociation('attribute_group', 'ag').'
            INNER JOIN '._DB_PREFIX_.'attribute_group_lang agl
                ON (ag.id_attribute_group = agl.id_attribute_group AND id_lang = '.(int)$id_lang.')
            ORDER BY ag.id_attribute_group ASC
        ');
        foreach ($att_groups as $group) {
            $grouped_attributes[$group['name']] = $sorted_attributes[$group['id_attribute_group']];
        }
        return $grouped_attributes;
    }

    public function getCombinationsSummary($id_product)
    {
        if ($name = $this->db->getValue('
            SELECT name FROM '._DB_PREFIX_.'product_lang
            WHERE id_product = '.(int)$id_product.' AND id_lang = '.(int)$this->context->language->id.'
        ')) {
            $ret = $name.' | '.$this->l('Total combinations').': '.count($this->getExistingCombinations($id_product));
        } else {
            $ret = $this->l('No product found with this ID');
        }
        return $ret;
    }

    public function getProductIDs($filters, $throw_error = true)
    {
        $query = new DbQuery();
        $query->select('DISTINCT p.id_product');
        $query->from('product', 'p');
        $query->join(Shop::addSqlAssociation('product', 'p'));
        // combinations are not available for virtual products and packs
        $query->where('p.is_virtual < 1 AND p.cache_is_pack < 1');
        foreach ($filters as $name => $value) {
            if ($imploded_ids = $this->formatIDs($value)) {
                $identifier_name = $name;
                $name = str_replace('id_', '', $name);
                $alias = 'p';
                switch ($name) {
                    case 'category':
                    case 'supplier':
                        $table = $name == 'category' ? $name.'_product' : 'product_'.$name;
                        $alias = $name[0].$alias;
                        $query->innerJoin($table, $alias, 'p.id_product = '.pSQL($alias).'.id_product');
                        break;
                }
                $query->where(pSQL($alias).'.'.pSQL($identifier_name).' IN ('.pSQL($imploded_ids).')');
            }
        }
        if ($exclude_ids = $this->getExcludedIds()) {
            $query->where('p.id_product NOT IN ('.pSQL($exclude_ids).')');
        }
        $query->orderBy('product_shop.date_add DESC');
        // d($query->build());
        $ids = array();
        foreach ($this->db->executeS($query) as $row) {
            $ids[$row['id_product']] = $row['id_product'];
        }
        if ($throw_error) {
            if (!$ids) {
                $this->throwError($this->l('No matching products'));
            } elseif (empty($filters['id_product'])) {
                unset($filters['id_product']);
                if (empty($filters)) {
                    $this->throwError($this->l('Please select products, that should be processed'), 'warning');
                }
            }
        }
        return $ids;
    }

    public function getExcludedIds()
    {
        if (!$exclude_ids = Tools::getValue('exclude_ids')) {
            if (Tools::getValue('action') == 'duplicateCombinations') {
                $a = Tools::getValue('a');
                if (!empty($a['id_product_original'])) {
                    $exclude_ids = $a['id_product_original'];
                }
            }
        }
        return $this->formatIDs($exclude_ids);
    }

    public function dataCanBeReset($data_type)
    {
        $age = time() - filemtime($this->getDataPath($data_type));
        $time_diff = $this->time_before_reset - $age;
        if ($time_diff > 1) {
            $err = $this->l('Please wait, someone else is generating combinations').
            '. '.sprintf($this->l('%s seconds left before automatic reset.'), $time_diff);
            $this->throwError($err);
        }
        return true;
    }

    public function processItems($action, $count_processed = true)
    {
        $filters = Tools::getValue('filters');
        $identifier = Tools::getValue('identifier');
        if (!$products_data = $this->getData('products')) {
            $this->eraseData();
            $products_data = array(
                'processed' => array(),
                'deleted_combinations' => 0,
                'to_process' => $this->getProductIDs($filters),
                'filters' => $filters, // can be used for resuming in next versions
                'identifier' => $identifier,
            );
        } elseif ($products_data['identifier'] != $identifier) {
            if ($this->dataCanBeReset('products')) {
                $this->eraseData();
                return $this->processItems($action, $count_processed);
            }
        }

        if (!$a = $this->getData('a')) {
            $a = $this->prepareAttributesData(Tools::getValue('a'));
            $this->saveData('a', $a);
        }

        if ($id_product = current($products_data['to_process'])) {
            $complete = true;
            $this->validateEssentialData($action, $a);
            switch ($action) {
                case 'regenerateCombinations':
                case 'duplicateCombinations':
                    if ($products_data['deleted_combinations'] != $id_product) {
                        if ($complete &= $this->deleteCombinations($id_product)) {
                            $products_data['deleted_combinations'] = $id_product;
                        }
                    }
                    if ($products_data['deleted_combinations'] == $id_product) {
                        $complete &= $this->updateCombinations($id_product, $a);
                    }
                    break;
                case 'updateCombinations':
                    $complete &= $this->updateCombinations($id_product, $a);
                    break;
                case 'deleteCombinations':
                    $complete &= $this->deleteCombinations($id_product);
                    break;
            }
            if ($complete) {
                if (is_callable(array('Tools', 'clearColorListCache'))) {
                    Tools::clearColorListCache($id_product); // retro-compatibility
                }
                $products_data['processed'][] = $id_product;
                unset($products_data['to_process'][$id_product]);
            }
        }
        if ($products_data['to_process']) {
            $this->saveData('products', $products_data);
        } else {
            $this->eraseData();
        }
        if ($count_processed) {
            $products_data['processed'] = count($products_data['processed']);
            $products_data['to_process'] = count($products_data['to_process']);
        }
        return $products_data;
    }

    public function validateEssentialData($action, $a)
    {
        if ($action == 'regenerateCombinations' && empty($a['values'])) {
            $this->throwError($this->l('Please add attributes for new combinations'));
        } elseif ($action == 'duplicateCombinations' && empty($a['id_product_original'])) {
            $this->throwError($this->l('Please specify Original product ID'));
        }
        if (isset($a['options']['minimal_quantity']) && (int)$a['options']['minimal_quantity'] < 1) {
            $this->throwError('Incorrect value for "Min qty for order"');
        }
    }

    public function prepareAttributesData($a)
    {
        $combinations = $this->createCombinations(array_values($a['values']));

        // temporary fix. TODO: update createCombinations()
        $att_groups = array();
        foreach ($a['values'] as $id_group => $attributes) {
            $att_groups += array_fill_keys($attributes, $id_group);
        }
        foreach ($combinations as $i => $c) {
            $c_updated = array();
            foreach ($c as $id_att) {
                $c_updated[$att_groups[$id_att]] = $id_att;
            }
            ksort($c_updated); // order by id group
            $combinations[$i] = $c_updated;
        }
        //

        $a['combinations'] = $combinations;
        return $a;
    }

    public function createCombinations($list)
    {
        if (count($list) <= 1) {
            return count($list) ? array_map(create_function('$v', 'return (array($v));'), $list[0]) : $list;
        }
        $res = array();
        $first = array_pop($list);
        foreach ($first as $attribute) {
            $tab = $this->createCombinations($list);
            foreach ($tab as $to_add) {
                $res[] = is_array($to_add) ? array_merge($to_add, array($attribute)) : array($to_add, $attribute);
            }
        }
        return $res;
    }

    public function prepareCombinationsForUpdate($id_product, $a)
    {
        $to_update = $dont_update = $used_comb_ids = array();
        $update_all = isset($a['id_product_original']) || !empty($a['override_options']) ||
        Tools::strpos($a['options']['reference'], '{iterate}') !== false;
        $required_atts_num = 0;
        $new_combinations = isset($a['combinations']) ? $a['combinations'] : array();
        $source_product_id = isset($a['id_product_original']) ? $a['id_product_original'] : $id_product;
        foreach ($this->getExistingCombinations($source_product_id) as $id_comb => $atts) {
            foreach ($new_combinations as $new_atts) {
                $upd_atts = $atts + $new_atts; // atts are sorted by group ASC
                $imploded_att_ids = implode('-', $upd_atts);
                if ($upd_atts != $atts || $update_all) {
                    $id_comb_new = !isset($used_comb_ids[$id_comb]) ? $id_comb : 0;
                    $used_comb_ids[$id_comb] = 1;
                    if (!isset($to_update[$imploded_att_ids])) { // do not override id_comb_new
                        $to_update[$imploded_att_ids] = array('id_comb' => $id_comb_new, 'id_orig' => $id_comb);
                    }
                } else {
                    $dont_update[$imploded_att_ids] = $id_comb;
                }
                if (!$required_atts_num) {
                    $required_atts_num = count($upd_atts);
                }
            }
            if (!$new_combinations && $update_all) {
                $implded_atts = implode('-', $atts);
                $to_update[$implded_atts] = array('id_comb' => $id_comb, 'id_orig' => $id_comb);
                if (isset($a['id_product_original'])) {
                    $to_update[$implded_atts]['id_comb'] = 0;
                    $to_update[$implded_atts]['id_product_original'] = $a['id_product_original'];
                }
            }
        }
        if (!$required_atts_num || ($required_atts_num == count(current($new_combinations)))) {
            // add new combinations if number of attributes is enough and they were not used
            foreach ($new_combinations as $new_atts) {
                $imploded_atts = implode('-', $new_atts);
                if (!isset($to_update[$imploded_atts]) && !isset($dont_update[$imploded_atts])) {
                    $to_update[$imploded_atts] = array('id_comb' => 0, 'id_orig' => 0);
                }
            }
        }
        $existing_impacts = $this->getExistingImpactsMultishop($source_product_id);
        foreach ($to_update as &$u) {
            $u['initial_impacts'] = isset($existing_impacts[$u['id_orig']]) ?
            $existing_impacts[$u['id_orig']] : array();
        }
        // d([$this->getExistingCombinations($id_product), $a]);
        // d($to_update);
        return $to_update;
    }

    public function getExistingImpactsMultishop($id_product)
    {
        $combination_field_names = array_keys($this->getCombinationFields());
        $where = $select = array();
        foreach ($combination_field_names as $name) {
            $name = 'product_attribute_shop.'.$name;
            $where[] = pSQL($name).' <> 0';
            $select[] = pSQL($name);
        }
        $data = $this->db->executeS('
            SELECT pa.id_product_attribute AS id_comb,
            product_attribute_shop.id_shop, '.implode(', ', $select).'
            FROM '._DB_PREFIX_.'product_attribute pa
            '.Shop::addSqlAssociation('product_attribute', 'pa').'
            WHERE pa.id_product = '.(int)$id_product.'
            AND ('.implode(' OR ', $where).')
            AND pa.id_product_attribute > 0
        ');
        $impacts = array();
        foreach ($data as $d) {
            foreach ($combination_field_names as $name) {
                if ($d[$name] <> 0) {
                    $impacts[$d['id_comb']][$name][$d['id_shop']] = $d[$name];
                }
            }
        }
        return $impacts;
    }

    public function combinationExistsInCurrentShopContext($id_combination)
    {
        if ((int)$id_combination) {
            return (bool)$this->db->getValue('
                SELECT id_product_attribute FROM '._DB_PREFIX_.'product_attribute_shop
                WHERE id_product_attribute = '.(int)$id_combination.'
                AND id_shop IN ('.pSQL($this->getContextShopIds(true)).')
            ');
        }
    }

    public function updateCombinations($id_product, $a)
    {
        $ret = true;
        $updated_combinations = $sql = $rows = array();
        if (!isset($a['combinations_to_update'][$id_product])) {
            $a['combinations_to_update'][$id_product] = $this->prepareCombinationsForUpdate($id_product, $a);
        }
        $combinations_to_update = $a['combinations_to_update'][$id_product];
        foreach ($combinations_to_update as $imploded_atts => $c) {
            if (!$this->combinations_num_left--) {
                $ret &= false;
                break;
            }
            $c['options'] = array();
            foreach (array('quantity', 'minimal_quantity', 'reference') as $name) {
                if ((!$this->combinationExistsInCurrentShopContext($c['id_comb']) ||
                    !empty($a['override_options'][$name])) && isset($a['options'][$name])) {
                    $c['options'][$name] = $a['options'][$name];
                }
            }

            if (!empty($c['id_product_original']) && !empty($a['new_reference'])) {
                $c['options']['reference'] = $a['new_reference']; // update reference for duplicated combinations
            }

            $c['id_product'] = $id_product;
            $c['att_ids'] = explode('-', $imploded_atts);
            $combination = $this->updateCombinationObj($c);
            $id_comb = $combination->id;
            foreach ($c['att_ids'] as $id_att) {
                $rows['product_attribute_combination'][] = array(
                    'id_attribute' => $id_att,
                    'id_product_attribute' => $id_comb
                );
            }
            $updated_combinations[$id_comb] = $c;
            unset($a['combinations_to_update'][$id_product][$imploded_atts]);
        }

        $this->saveData('a', $a);

        if ($updated_combinations && !empty($rows['product_attribute_combination'])) {
            $sql['del_product_attribute_combination'] = 'DELETE FROM '._DB_PREFIX_.'product_attribute_combination
            WHERE id_product_attribute IN ('.pSQL(implode(',', array_keys($updated_combinations))).')';
        }
        $rows += $this->prepareMultishopRows($id_product, $updated_combinations, $a);
        foreach ($rows as $table_name => $table_rows) {
            if (!empty($table_rows)) {
                $duplicate_update = array();
                $col_names = array_keys(current($table_rows));
                foreach ($col_names as $c) {
                    $duplicate_update[] = $c.'=VALUES('.$c.')';
                }
                foreach ($table_rows as $key => $row) {
                    $table_rows[$key] = '('.implode(', ', array_map('pSQL', $row)).')';
                }
                $sql['upd_'.$table_name] = 'INSERT INTO '._DB_PREFIX_.pSQL($table_name).'
                ('.pSQL(implode(', ', $col_names)).') VALUES '.implode(', ', $table_rows).'
                ON DUPLICATE KEY UPDATE '.pSQL(implode(', ', $duplicate_update));
            }
        }

        if ($ret &= $this->runSql($sql)) {
            $ret &= $this->updateDefaultCombinationAndSaveProduct($id_product, $a);
        }
        return $ret;
    }

    public function getTaxesRate($id_tax_rules_group)
    {
        $address = Address::initialize();
        $tax_manager = TaxManagerFactory::getManager($address, $id_tax_rules_group);
        $tax_calculator = $tax_manager->getTaxCalculator();
        return $tax_calculator->getTotalRate()/100;
    }

    public function prepareMultishopRows($id_product, $updated_combinations, $a)
    {
        $imact_keys = array_keys($this->getCombinationFields());
        $complex_percentage = !empty($a['options']['complex_percentage']);
        $rows = array();
        foreach ($this->getContextShopIds() as $id_shop) {
            $product_data = $this->getProductData($id_product, $id_shop);
            $tax_impact = $a['options']['tax_incl'] ? $this->getTaxesRate($product_data['id_tax_rules_group']) : 0;
            foreach ($updated_combinations as $id_comb => $c) {
                $row = array('id_product_attribute' => $id_comb);
                foreach ($imact_keys as $key) {
                    $value = isset($c['initial_impacts'][$key][$id_shop]) ? $c['initial_impacts'][$key][$id_shop] : 0;
                    $percentage_impacts = array();
                    foreach ($c['att_ids'] as $id_att) {
                        if (!empty($a['impacts'][$key][$id_att]['value'])) {
                            $att_impact = $a['impacts'][$key][$id_att];
                            $number = $this->getImpactNumericValue($att_impact);
                            if ($key == 'price' && $tax_impact) {
                                $number = $number/(1 + $tax_impact);
                            }
                            if ($att_impact['suffix'] == '%') {
                                $percentage_impacts[] = $number;
                            } else {
                                $value += $number;
                            }
                        }
                    }
                    foreach ($percentage_impacts as $pi) {
                        $base_key = $key == 'weight' ? 'weight' : 'price';
                        $value += ($product_data[$base_key] + ($complex_percentage ? $value : 0)) * $pi / 100;
                    }
                    $value = ($product_data[$key] + $value < 0) ? -$product_data[$key] : $value;
                    $row[$key] = $value;
                }
                $rows['product_attribute_shop'][$id_comb.'_'.$id_shop] = $row + array('id_shop' => $id_shop);
                if (!isset($rows['product_attribute'][$id_comb])) {
                    $rows['product_attribute'][$id_comb] = $row;
                }
            }
        }
        // d($rows);
        return $rows;
    }

    public function getImpactNumericValue($impact)
    {
        $value = (float)preg_replace('/[^0-9.]/', '', str_replace(',', '.', $impact['value']));
        $multiplier = $impact['prefix'] == '-' ? -1 : 1;
        return $value * $multiplier;
    }

    public function updateCombinationObj($c)
    {
        $obj = new Combination($c['id_orig']);
        $obj->id_product = $c['id_product'];
        $obj->id = $c['id_comb'];
        if (isset($c['id_product_original'])) {
            $c['orig_ref'] = $obj->reference;
            $obj->reference = '';
        } elseif ($c['id_comb'] != $c['id_orig']) {
            $obj->default_on = '';
            $obj->reference = '';
        }
        if ($c['id_orig'] && !isset($c['options']['quantity'])) {
            $obj->quantity = $this->db->getValue('
                SELECT sa.quantity FROM '._DB_PREFIX_.'stock_available sa
                '.Shop::addSqlAssociation('product_attribute', 'sa').'
                WHERE sa.id_product_attribute = '.(int)$c['id_orig'].'
            ');
        }
        foreach ($c['options'] as $name => $value) {
            $obj->$name = $this->formatCombinationValue($value, $name, $c);
        }
        $obj->save();
        StockAvailable::setQuantity($obj->id_product, $obj->id, $obj->quantity);
        return $obj;
        // debug
        // if (!$obj->id) $obj->id = $this->getNextAutoInctementValue('product_attribute');
        // return $obj;
    }

    public function getNextAutoInctementValue($table_name)
    {
        $data = $this->db->executeS('SHOW TABLE STATUS LIKE \''._DB_PREFIX_.pSQL($table_name).'\'');
        $value = isset($data[0]['Auto_increment']) ? $data[0]['Auto_increment'] + 1 : 0;
        // test
        $this->ta = isset($this->ta) ? $this->ta : 0;
        $value += $this->ta++;
        // test
        return $value;
    }

    public function getImplodedAttNames($c, $max_chars_per_word)
    {
        $data = $this->db->executeS('
            SELECT al.id_attribute, al.name FROM '._DB_PREFIX_.'attribute_lang al
            INNER JOIN '._DB_PREFIX_.'attribute a ON a.id_attribute = al.id_attribute
            INNER JOIN '._DB_PREFIX_.'attribute_group ag ON ag.id_attribute_group = a.id_attribute_group
            WHERE al.id_lang = '.(int)Configuration::get('PS_LANG_DEFAULT').'
            AND al.id_attribute IN ('.pSQL($this->formatIDs($c['att_ids'])).')
            ORDER BY ag.position ASC
        ');
        $names = array();
        foreach ($data as $d) {
            $name = str_replace(array(',', '.', '*'), '-', $d['name']);
            $name = explode('-', Tools::str2url($name));
            foreach ($name as &$word) {
                $word = Tools::substr($word, 0, $max_chars_per_word);
            }
            $names[$d['id_attribute']] = implode('_', $name);
        }
        return implode('_', $names);
    }

    public function formatCombinationValue($value, $name, $c)
    {
        switch ($name) {
            case 'reference':
                $replacements = array(
                   '{id_product}' => $c['id_product'],
                   '{base_ref}'   => $this->getProductReference($c['id_product']),
                   '{iterate}'  => $this->getNextIterationNum($c),
                );
                if (isset($c['id_product_original']) && isset($c['orig_ref'])) {
                    $replacements['{orig_ref}'] = $c['orig_ref'];
                    $base_ref_orig = $this->getProductReference($c['id_product_original']);
                    $replacements['{orig_ref_without_base}'] = str_replace($base_ref_orig, '', $c['orig_ref']);
                }
                if (strpos($value, '{att_names_') !== false) {
                    $max_chars = explode('{att_names_', $value);
                    $max_chars = isset($max_chars[1]) && (int)$max_chars[1] ? (int)$max_chars[1] : 5;
                    $replacements['{att_names_'.$max_chars.'}'] = $this->getImplodedAttNames($c, $max_chars);
                }
                $value = str_replace(array_keys($replacements), $replacements, $value);
                $value = Tools::substr($value, 0, 32); // max allowed length for $combination->reference
                break;
            default:
                $value = (int)$value;
                break;
        }
        return strip_tags($value);
    }

    public function getRefVariables()
    {
        $iso_lang = Tools::strtoupper(Language::getIsoById(Configuration::get('PS_LANG_DEFAULT')));
        $variables = array(
            '{id_product}' => $this->l('ID of product'),
            '{base_ref}' => $this->l('Base reference of product'),
            '{att_names_5}' => sprintf($this->l('Abbreviated attribute names, 5 characters per word (%s)'), $iso_lang),
            '{iterate}' => $this->l('Iteration number for new combination'),
            '{orig_ref}' => $this->l('Reference of original combination'),
            '{orig_ref_without_base}' => $this->l('same as {orig_ref}, but without base reference'),
        );
        return $variables;
    }

    public function getProductReference($id_product)
    {
        $var = 'ref_'.$id_product;
        $this->$var = isset($this->$var) ? $this->$var : $this->db->getValue('
            SELECT reference FROM '._DB_PREFIX_.'product WHERE id_product = '.(int)$id_product.'
        ');
        return $this->$var;
    }

    public function getNextIterationNum($c)
    {
        $num = $this->db->getValue('
            SELECT COUNT(id_product_attribute) FROM '._DB_PREFIX_.'product_attribute
            WHERE id_product = '.(int)$c['id_product']
            .($c['id_comb'] ? ' AND id_product_attribute < '.(int)$c['id_comb'] : '').'
        ');
        return (int)$num + 1;
    }

    public function getProductData($id_product, $id_shop)
    {
        return $this->db->getRow('
            SELECT p.*, ps.*, sa.out_of_stock FROM '._DB_PREFIX_.'product p
            INNER JOIN '._DB_PREFIX_.'product_shop ps
                ON ps.id_product = p.id_product AND ps.id_shop = '.(int)$id_shop.'
            INNER JOIN '._DB_PREFIX_.'stock_available sa
                ON sa.id_product = p.id_product AND sa.id_product_attribute = 0 AND sa.id_shop = '.(int)$id_shop.'
            WHERE p.id_product = '.(int)$id_product.'
        ');
    }

    public function deleteCombinations($id_product)
    {
        $ret = true;
        $combinations = new PrestaShopCollection('Combination');
        $combinations->where('id_product', '=', $id_product);
        foreach ($combinations as $combination) {
            if (!$this->combinations_num_left--) {
                $ret &= false;
                break;
            }
            $ret &= $combination->delete();
        }
        if ($ret) {
            Hook::exec(
                'actionProductAttributeDelete',
                array(
                    'id_product_attribute' => 0,
                    'id_product' => (int)$id_product,
                    'deleteAllAttributes' => true
                )
            );
            SpecificPriceRule::applyAllRules(array($id_product));
        }
        return $ret;
    }

    /*
    * @return all available combinations in all shops
    */
    public function getExistingCombinations($id_product)
    {
        $data = $this->db->executeS('
            SELECT pac.id_product_attribute as id_comb,
            pac.id_attribute as id_att, a.id_attribute_group as id_group
            FROM '._DB_PREFIX_.'product_attribute_combination pac
            INNER JOIN '._DB_PREFIX_.'attribute a ON a.id_attribute = pac.id_attribute
            INNER JOIN '._DB_PREFIX_.'product_attribute pa
                ON pac.id_product_attribute = pa.id_product_attribute
                AND pa.id_product = '.(int)$id_product.'
            ORDER BY a.id_attribute_group ASC
        ');
        $combinations = array();
        foreach ($data as $d) {
            $combinations[$d['id_comb']][$d['id_group']] = $d['id_att'];
        }
        ksort($combinations);
        return $combinations;
    }

    public function updateDefaultCombinationAndSaveProduct($id_product, $a)
    {
        $order = array(
            'min_price'  => 'pas.price ASC',
            'max_price'  => 'pas.price DESC',
            'min_weight' => 'pas.weight ASC',
            'max_weight' => 'pas.weight DESC',
        );
        $custom_order = '';
        if (isset($a['options']['default_combination']) && isset($order[$a['options']['default_combination']])) {
            $custom_order = $order[$a['options']['default_combination']].', ';
        }
        $this->backupContext();
        foreach ($this->getContextShopIds() as $id_shop) {
            Shop::setContext(Shop::CONTEXT_SHOP, $id_shop);
            if ($id_combination_default = $this->db->getValue('
                SELECT pa.id_product_attribute FROM '._DB_PREFIX_.'product_attribute pa
                INNER JOIN '._DB_PREFIX_.'product_attribute_shop pas
                    ON pa.id_product_attribute = pas.id_product_attribute
                    AND pas.id_shop = '.(int)$id_shop.'
                WHERE pa.id_product = '.(int)$id_product.'
                ORDER BY '.pSQL($custom_order).'pas.default_on DESC, pa.id_product_attribute ASC
            ')) {
                try {
                    $product = new Product($id_product);
                    $product->deleteDefaultAttributes();
                    $product->setDefaultAttribute($id_combination_default);
                    Hook::exec('actionProductUpdate', array('id_product' => $product->id, 'product' => $product));
                    if ($product->depends_on_stock) {
                         StockAvailable::synchronize($product->id);
                    }
                    // $this->updateSupplierReferences($product->id);
                } catch (Exception $e) {
                    $this->throwError($this->l('Product [ID='.$id_product.']').': '.$e->getMessage());
                }
            }
        }
        $this->restoreContext();
        return true;
    }

    public function updateSupplierReferences($id_product)
    {
        // Temporarily not used
        $combination_ids = array_keys($this->getExistingCombinations($id_product));
        $supplier_ids = $this->getProductSuppliers($id_product);
        $rows = array();
        foreach ($supplier_ids as $id_supplier) {
            foreach ($combination_ids as $id_comb) {
                $rows[] = '(\'\', '.(int)$id_product.', '.(int)$id_comb.', '.(int)$id_supplier.', \'\')';
            }
        }
        if ($rows) {
            $this->db->execute('
                INSERT INTO '._DB_PREFIX_.'product_supplier
                (id_product_supplier, id_product,id_product_attribute, id_supplier, product_supplier_reference)
                VALUES '.implode(', ', $rows).' ON DUPLICATE KEY UPDATE id_supplier=VALUES(id_supplier)
            ');
        }
    }

    public function getProductSuppliers($id_product)
    {
        $suppliers = $this->db->executeS('
            SELECT DISTINCT(id_supplier) FROM '._DB_PREFIX_.'product_supplier
            WHERE id_product = '.(int)$id_product.' AND id_product_attribute = 0
        ');
        foreach ($suppliers as &$sup) {
            $sup = $sup['id_supplier'];
        }
        return $suppliers;
    }

    public function getContextShopIds($imploded = false)
    {
        $shop_ids = Shop::getContextListShopID();
        return $imploded ? implode(',', $shop_ids) : $shop_ids;
    }

    public function getDataPath($type)
    {
        return $this->local_path.'data/'.$type.'.txt';
    }

    public function getData($type)
    {
        $path = $this->getDataPath($type);
        return file_exists($path) ? Tools::jsonDecode(Tools::file_get_contents($path), true) : array();
    }

    public function saveData($type, $data, $append = false)
    {
        $path = $this->getDataPath($type);
        $data = is_string($data) ? $data : Tools::jsonEncode($data);
        return $append ? file_put_contents($path, $data, FILE_APPEND) : file_put_contents($path, $data);
    }

    public function eraseData()
    {
        $erased = true;
        foreach (glob($this->getDataPath('*')) as $file) {
            $erased &= unlink($file);
        }
        return $erased;
    }

    public function backupContext()
    {
        $this->backup_context = array('shop_context' => Shop::getContext(), 'shop_context_id' => null);
        if ($this->backup_context['shop_context'] == Shop::CONTEXT_GROUP) {
            $this->backup_context['shop_context_id'] = $this->context->shop->id_shop_group;
        } elseif ($this->backup_context['shop_context'] == Shop::CONTEXT_SHOP) {
            $this->backup_context['shop_context_id'] = $this->context->shop->id;
        }
    }

    public function restoreContext()
    {
        if (!empty($this->backup_context)) {
            Shop::setContext($this->backup_context['shop_context'], $this->backup_context['shop_context_id']);
        }
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

    public function formatIDs($ids, $return_string = true)
    {
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        $ids = array_map('intval', $ids);
        $ids = array_combine($ids, $ids);
        unset($ids[0]);
        return $return_string ? implode(',', $ids) : $ids;
    }

    public function getInfoLinks()
    {
        $links = array(
            'documentation' => array(
                'title' => $this->l('Documentation'),
                'icon' => 'file-text',
                'url' => $this->_path.'readme_en.pdf?v='.$this->version,
            ),
            'changelog' => array(
                'title' => $this->l('Changelog'),
                'icon' => 'code-fork',
                'url' => $this->_path.'Readme.md?v='.$this->version,
            ),
            'contact' => array(
                'title' => $this->l('Contact us'),
                'icon' => 'envelope',
                'url' => 'https://addons.prestashop.com/en/contact-us?id_product=18240',
            ),
            'modules' => array(
                'title' => $this->l('Our modules'),
                'icon' => 'download',
                'url' => 'https://addons.prestashop.com/en/2_community-developer?contributor=64815',
            ),
        );
        return $links;
    }

    public function exportSettings()
    {
        $data = array();
        parse_str(Tools::getValue('serialized_data'), $data);
        $file_content = Tools::jsonEncode($data);
        $file_name = 'bcg-settings-'.date('d-m-Y').'.txt';
        header('Content-disposition: attachment; filename='.$file_name);
        header('Content-type: text/plain');
        echo $file_content;
        exit();
    }

    public function throwError($error_text, $class = 'error')
    {
        die(Tools::jsonEncode(array('error' => utf8_encode($error_text), 'class' => $class)));
    }
}
