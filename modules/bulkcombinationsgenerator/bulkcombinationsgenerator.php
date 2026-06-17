<?php
/**
 *  @author    Amazzing <mail@mirindevo.com>
 *  @copyright Amazzing
 *  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
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
        $this->version = '2.1.4';
        $this->ps_versions_compliancy = ['min' => '1.6.0.4', 'max' => _PS_VERSION_];
        $this->author = 'Amazzing';
        $this->need_instance = 0;
        $this->module_key = '76fa37d23dff4b3ad6afc517d8a25c44';
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('Bulk combinations generator');
        $this->description = $this->l('Bulk combinations generator');
        $this->db = Db::GetInstance();
        $this->combinations_num = ['max' => 300, 'added' => 0, 'updated' => 0, 'deleted' => 0];
        $this->max_combinations_per_product = 800;
        $this->time_before_reset = 60;
        $this->x = []; // quick cache
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
        $this->context->controller->js_files[] = $this->_path . 'views/js/back.js?' . $this->version;
        $this->context->controller->css_files[$this->_path . 'views/css/back.css?' . $this->version] = 'all';
        $this->context->smarty->assign([
            'bcg_js_vars' => [
                'l' => $this->getTranslatableTexts(),
            ],
            'product_filters' => $this->getProductFilters(),
            'combination_fields' => $this->getCombinationFields(),
            'attribute_options_fields' => $this->getAttributeOptionsFields(),
            'duplicate_fields' => $this->getDuplicateFields(),
            'reference_variables' => $this->getRefVariables(),
            'version' => $this->version,
            'info_links' => $this->getInfoLinks(),
        ]);

        return $this->display(__FILE__, 'views/templates/admin/configure.tpl');
    }

    public function getTranslatableTexts()
    {
        return [
            'loading' => $this->l('Loading...'),
            'complete' => $this->l('COMPLETE!'),
            'saved' => $this->l('Saved'),
            'dont_close' => $this->l('Please do not close this browser tab'),
            'products_processed' => $this->l('Products processed: %s'),
            'combs_added' => $this->l('Combinations created: %s'),
            'combs_updated' => $this->l('Combinations updated: %s'),
            'combs_deleted' => $this->l('Combinations deleted: %s'),
            'upd_existing' => $this->l('Selected attributes will be added to existing combinations'),
            'add_new_maybe' => $this->l('New combinations may be created, if required'),
            'override_all' => $this->l('%s will be updated for all existing combinations'),
            'override_selected' => $this->l('%s will be updated for existing combinations with selected attributes'),
            'add_new' => $this->l('New combinations will be created from selected attributes'),
            'if_dont_exist' => $this->l('if they do not already exist'),
            'delete_all' => $this->l('All existing combinations will be deleted'),
            'delete_selected' => $this->l('Combinations with selected attributes will be deleted'),
            'time_spent' => $this->l('Time spent: %s'),
            'time_remaining' => $this->l('Estimated remaining time: %s'),
            'check_console' => $this->l('Error. Check console log'),
        ];
    }

    public function getProductFilters()
    {
        $filters = [
            'id_category' => [
                'label' => $this->l('Categories'),
                'options' => $this->getOptions('category'),
                'id_parent' => Configuration::get('PS_ROOT_CATEGORY'),
                'col' => ['group' => 12, 'label' => 2, 'value' => 10],
            ],
            'id_manufacturer' => [
                'label' => $this->l('Manufacturers'),
                'options' => $this->getOptions('manufacturer'),
                'col' => ['group' => 4, 'label' => 2, 'value' => 3],
            ],
            'id_supplier' => [
                'label' => $this->l('Suppliers'),
                'options' => $this->getOptions('supplier'),
                'col' => ['group' => 4, 'label' => 2, 'value' => 3],
            ],
            'id_product' => [
                'label' => $this->l('Product IDs'),
                'tooltip' => $this->l('Separated by commas'),
                'col' => ['group' => 4, 'label' => 2, 'value' => 3],
            ],
        ];

        return $filters;
    }

    public function getOptions($type)
    {
        $options = [];
        $id_lang = $this->context->language->id;
        switch ($type) {
            case 'manufacturer':
            case 'supplier':
                $items = $this->db->executeS('SELECT * FROM `' . _DB_PREFIX_ . bqSQL($type) . '`');
                foreach ($items as $row) {
                    $options[$row['id_' . $type]] = $row['name'];
                }
                break;
            case 'category':
                $categories = $this->db->executeS('
                    SELECT * FROM ' . _DB_PREFIX_ . 'category c
                    ' . Shop::addSqlAssociation('category', 'c') . '
                    LEFT JOIN ' . _DB_PREFIX_ . 'category_lang cl
                        ON c.id_category = cl.id_category
                    WHERE id_lang = ' . (int) $id_lang . '
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
        $fields = [ // keys should be exactly same as in database
            'price' => ['name' => $this->l('Price impact'), 'suffix' => $p_suffix],
            'unit_price_impact' => ['name' => $this->l('Unit price impact'), 'suffix' => $p_suffix],
            'wholesale_price' => ['name' => $this->l('Wholesale price impact'), 'suffix' => $p_suffix],
            'weight' => ['name' => $this->l('Weight impact'), 'suffix' => $w_suffix],
        ];

        return $fields;
    }

    public function getAttributeOptionsFields()
    {
        $fields = [
            'default_combination' => [
                'label' => $this->l('Default combination'),
                'options' => [
                    '0' => $this->l('First available'),
                    'min_price' => $this->l('With lowest price'),
                    'max_price' => $this->l('With highest price'),
                    'min_weight' => $this->l('With lowest weight'),
                    'max_weight' => $this->l('With highest weight'),
                ],
            ],
            'quantity' => [
                'label' => $this->l('Quantity'),
                'value' => 100,
                'override' => 0,
            ],
            'minimal_quantity' => [
                'label' => $this->l('Min quantity for order'),
                'value' => 1,
                'override' => 0,
            ],
            'reference' => [
                'label' => $this->l('Reference'),
                'override' => 0,
            ],
            'complex_percentage' => [
                'label' => $this->l('Calculate percentage'),
                'options' => [
                    '' => $this->l('From base value'),
                    '1' => $this->l('From base value + other impacts'),
                ],
                'class' => 'complex-percentage hidden',
            ],
        ];

        return $fields;
    }

    public function getDuplicateFields()
    {
        $fields = [
            'id_product_original' => [
                'label' => $this->l('Original product ID'),
            ],
            'new_reference' => [
                'label' => $this->l('Pattern for new references'),
            ],
        ];

        return $fields;
    }

    public function ajaxAction()
    {
        $ret = [];
        $action = Tools::getValue('action');
        switch ($action) {
            case 'getFilteredProductsNum':
                $filters = Tools::getValue('filters');
                $ret['products_num'] = count($this->getProductIDs($filters));
                $ret['log_txt'] = sprintf($this->l('%d products are ready to be processed'), $ret['products_num']);
                break;
            case 'showAttributes':
                $this->context->smarty->assign(['available_items' => $this->getGroupedAttributes()]);
                $ret['content'] = $this->display(__FILE__, 'views/templates/admin/available-items.tpl');
                $ret['title'] = $this->l('Available attributes');
                break;
            case 'getDynamicRows':
                $this->context->smarty->assign([
                    'rows' => $this->getAttributeRows(['att_ids' => Tools::getValue('ids')]),
                    'combination_fields' => $this->getCombinationFields(),
                ]);
                $ret['rows_html'] = $this->display(__FILE__, 'views/templates/admin/dynamic-rows.tpl');
                break;
            case 'update':
            case 'updateByAtts':
            case 'addNew':
            case 'regenerate':
            case 'deleteByAtts':
            case 'delete':
            case 'duplicate':
                $ret += $this->processItems($action);
                break;
            case 'getCombinationsSummary':
                $ret['summary'] = $this->getCombinationsSummary(Tools::getValue('id_product'));
                break;
            case 'eraseData':
                $this->eraseData();
                break;
        }
        exit(json_encode($ret));
    }

    public function getAttributeRows($params = [])
    {
        $id_lang = $this->context->language->id;
        $q = new DbQuery();
        $q->select('a.id_attribute AS id, a.id_attribute_group AS id_group,
            al.name, agl.name AS group_name, a.position');
        $q->from('attribute', 'a')->join(Shop::addSqlAssociation('attribute', 'a'));
        $q->leftJoin('attribute_lang', 'al', '
            a.id_attribute = al.id_attribute AND al.id_lang = ' . (int) $id_lang);
        $q->leftJoin('attribute_group_lang', 'agl', '
            a.id_attribute_group = agl.id_attribute_group AND agl.id_lang = ' . (int) $id_lang);
        $q->groupBy('a.id_attribute');
        if (isset($params['att_ids'])) {
            if (!$att_ids_ = $this->sqlIDs($params['att_ids'])) {
                return [];
            }
            $q->where('a.id_attribute IN (' . $att_ids_ . ')');
            $q->orderBy('FIELD(a.id_attribute, ' . $att_ids_ . ')');
        } else {
            $q->orderBy('id_group, a.position');
        }

        return $this->db->executeS($q);
    }

    public function getGroupedAttributes()
    {
        $grouped_attributes = [];
        foreach ($this->getAttributeRows() as $row) {
            $grouped_attributes[$row['group_name']][$row['id']] = $row;
        }

        return $grouped_attributes;
    }

    public function getCombinationsSummary($id_product)
    {
        if ($name = $this->db->getValue('
            SELECT name FROM ' . _DB_PREFIX_ . 'product_lang
            WHERE id_product = ' . (int) $id_product . ' AND id_lang = ' . (int) $this->context->language->id . '
        ')) {
            $ret = $name . ' | ' . $this->l('Total combinations') . ': ' . count($this->getExistingCombinations($id_product));
        } else {
            $ret = $this->l('No product found with this ID');
        }

        return $ret;
    }

    public function getProductIDs($filters, $throw_error = true)
    {
        if ($throw_error && !array_filter($filters)) {
            $this->throwError($this->l('Please select products, that should be processed'), 'warning');
        }
        $query = new DbQuery();
        $query->select('DISTINCT p.id_product');
        $query->from('product', 'p');
        $query->join(Shop::addSqlAssociation('product', 'p'));
        // combinations are not available for virtual products and packs
        $query->where('p.is_virtual < 1 AND p.cache_is_pack < 1');
        foreach ($filters as $name => $value) {
            if (is_string($value)) {
                $value = $this->getIDsFromString($value);
            }
            if ($ids_ = $this->sqlIDs($value)) {
                $alias = 'p';
                if ($name == 'id_category') {
                    $alias = 'cp';
                    $query->innerJoin('category_product', $alias, 'p.id_product = `' . bqSQL($alias) . '`.id_product');
                } elseif ($name == 'id_supplier') {
                    $alias = 'sp';
                    $query->innerJoin('product_supplier', $alias, 'p.id_product = `' . bqSQL($alias) . '`.id_product');
                }
                $query->where('`' . bqSQL($alias) . '`.`' . bqSQL($name) . '` IN (' . $ids_ . ')');
            }
        }
        if ($exclude_ids_ = $this->sqlIDs($this->getExcludedIDs())) {
            $query->where('p.id_product NOT IN (' . $exclude_ids_ . ')');
        }
        $query->orderBy('product_shop.date_add DESC');
        $ids = array_column($this->db->executeS($query), 'id_product', 'id_product');
        if ($throw_error && !$ids) {
            $this->throwError($this->l('No matching products'));
        }

        return $ids;
    }

    public function getExcludedIDs()
    {
        if (!$exclude_ids = Tools::getValue('exclude_ids')) {
            if (Tools::getValue('action') == 'duplicate') {
                $a = Tools::getValue('a');
                if (!empty($a['id_product_original'])) {
                    $exclude_ids = $a['id_product_original'];
                }
            }
        }

        return $exclude_ids;
    }

    public function dataCanBeReset($data_type)
    {
        $age = time() - filemtime($this->getDataPath($data_type));
        $time_diff = $this->time_before_reset - $age;
        if ($time_diff > 1) {
            $err = $this->l('Please wait, someone else is generating combinations') .
            '. ' . sprintf($this->l('%s seconds left before automatic reset.'), $time_diff);
            $this->throwError($err);
        }

        return true;
    }

    public function processItems($action)
    {
        $filters = Tools::getValue('filters');
        $identifier = Tools::getValue('identifier');
        if (!$products_data = $this->getData('products')) {
            $this->eraseData();
            $products_data = [
                'num' => ['processed' => 0, 'to_process' => 0],
                'combs_num' => ['added' => 0, 'updated' => 0, 'deleted' => 0],
                'deleted_combinations' => '',
                'to_process' => $this->getProductIDs($filters),
                'identifier' => $identifier,
            ];
            $products_data['num']['to_process'] = count($products_data['to_process']);
            $this->saveData('products', $products_data);
        } elseif ($products_data['identifier'] != $identifier) {
            if ($this->dataCanBeReset('products')) {
                $this->eraseData();

                return $this->processItems($action);
            }
        }
        if (!$a = $this->getData('a')) {
            $a = Tools::getValue('a');
            $a['complex_percentage'] = !empty($a['options']['complex_percentage']);
            $a['action'] = $action;
            if (!isset($a['values']) || $action == 'delete') {
                $a['values'] = [];
            }
            if (isset($a['impacts'])) {
                foreach ($a['impacts'] as $type => $att_impacts) {
                    foreach ($att_impacts as $id_att => $impact) {
                        if ($impact['value'] === '') {
                            unset($a['impacts'][$type][$id_att]);
                        }
                    }
                    if (empty($a['impacts'][$type])) {
                        unset($a['impacts'][$type]);
                    }
                }
            } else {
                $a['impacts'] = [];
            }
            $this->saveData('a', $a);
        }
        if ($id_product = current($products_data['to_process'])) {
            $complete = true;
            $this->validateEssentialData($action, $a);
            SpecificPriceRule::disableAnyApplication();
            switch ($action) {
                case 'regenerate':
                case 'duplicate':
                    if ($products_data['deleted_combinations'] != $id_product) {
                        if ($complete &= $this->deleteCombinations($id_product, $a)) {
                            $products_data['deleted_combinations'] = $id_product;
                        }
                    }
                    if ($products_data['deleted_combinations'] == $id_product) {
                        $complete &= $this->updateCombinations($id_product, $a);
                    }
                    break;
                case 'update':
                case 'updateByAtts':
                case 'addNew':
                    $complete &= $this->updateCombinations($id_product, $a);
                    break;
                case 'delete':
                case 'deleteByAtts':
                    $complete &= $this->deleteCombinations($id_product, $a);
                    break;
            }
            foreach (['added', 'updated', 'deleted'] as $key) {
                $products_data['combs_num'][$key] += $this->combinations_num[$key];
            }
            if ($complete) {
                if (is_callable(['Tools', 'clearColorListCache'])) {
                    Tools::clearColorListCache($id_product); // retro-compatibility
                }
                SpecificPriceRule::enableAnyApplication();
                SpecificPriceRule::applyAllRules([$id_product]);
                unset($products_data['to_process'][$id_product]);
                ++$products_data['num']['processed'];
                --$products_data['num']['to_process'];
            }
        }
        if ($products_data['to_process']) {
            $this->saveData('products', $products_data);
        } else {
            $this->eraseData();
        }

        return $products_data;
    }

    public function validateEssentialData($action, $a)
    {
        $values_required = ['updateByAtts' => 1, 'addNew' => 1, 'regenerate' => 1, 'deleteByAtts' => 1];
        $check_limit = ['update' => 1, 'addNew' => 1, 'regenerate' => 1];
        if (!empty($a['values'])) {
            if (isset($check_limit[$action])
                && $this->getPossibleCombinationsNum($a['values']) > $this->max_combinations_per_product) {
                $this->throwError('You are trying to generate too many combinations per product.
                    Please decrease the number of attributes, or contact module developer to remove this limitation.');
            }
        } elseif (isset($values_required[$action])) {
            $this->throwError($this->l('Please specify attributes'));
        }
        if ($action == 'duplicate' && empty($a['id_product_original'])) {
            $this->throwError($this->l('Please specify Original product ID'));
        }
        if (isset($a['options']['minimal_quantity']) && (int) $a['options']['minimal_quantity'] < 1) {
            $this->throwError('Incorrect value for "Min qty for order"');
        }
    }

    public function getPossibleCombinationsNum($grouped_values)
    {
        if ($num = (int) $grouped_values) {
            foreach ($grouped_values as $values) {
                $num *= count($values);
            }
        }

        return $num;
    }

    public function getPossibleCombinations($data, &$all = [], $comb = [], $first_call = true)
    {
        if ($data) {
            if ($first_call) {
                ksort($data); // make sure $data is sorted by id_group
            }
            $id_group = current(array_keys($data));
            $atts_in_group = $data[$id_group];
            unset($data[$id_group]);
            foreach ($atts_in_group as $id_att) {
                $comb[$id_group] = $id_att;
                $this->getPossibleCombinations($data, $all, $comb, false);
            }
        } elseif ($comb) {
            $all[] = $comb;
        }

        return $all;
    }

    public function updateCombinations($id_product, $a)
    {
        $ret = true;
        if (!isset($a['combinations_to_update'][$id_product])) {
            $a['combinations_to_update'][$id_product] = $this->getCombinationsToUpdate($id_product, $a);
        }
        $att_rows = $att_rows_comb_ids = $upd_qty = [];
        foreach ($a['combinations_to_update'][$id_product] as $c_key => $c) {
            if (!$this->combinations_num['max']--) {
                $ret &= false;
                break;
            }
            $c['options'] = [];
            if (!empty($c['id_product_original']) && !empty($a['new_reference'])) {
                $c['options']['reference'] = $a['new_reference']; // update reference for duplicated combinations
            }
            foreach (['quantity', 'minimal_quantity', 'reference'] as $name) {
                if (isset($a['options'][$name]) && (!$c['id_orig'] || !empty($a['override_options'][$name]))) {
                    $c['options'][$name] = $a['options'][$name];
                }
            }
            foreach ($c['shop_ids'] as $id_shop) {
                if (!empty($a['impacts']) && (!$c['id_orig'] || !empty($a['override_options']['impacts']))) {
                    $c['options'] += $this->calculateImpacts($c, $a, $id_shop);
                }
                if (!empty($c['options']) || !$c['id_comb']) {
                    $combination = $this->updateCombinationObj($c, $id_shop);
                    $c['id_comb'] = $combination->id;
                    $upd_qty[$id_shop][$combination->id] = $combination->quantity;
                }
            }
            if ($c['upd_atts'] && $c['id_comb']) {
                $att_rows_comb_ids[$c['id_comb']] = $c['id_comb'];
                foreach ($c['att_ids'] as $id_att) {
                    $att_rows[$id_att . '-' . $c['id_comb']] = '(' . (int) $id_att . ', ' . (int) $c['id_comb'] . ')';
                }
            }
            if ($c['id_comb'] != $c['id_orig']) {
                ++$this->combinations_num['added'];
            } elseif ($c['upd_atts'] || !empty($c['options'])) {
                ++$this->combinations_num['updated'];
            }
            unset($a['combinations_to_update'][$id_product][$c_key]);
        }
        $this->saveData('a', $a);
        if ($att_rows && $comb_ids_ = $this->sqlIDs($att_rows_comb_ids)) {
            $sql = [
                'DELETE FROM ' . _DB_PREFIX_ . 'product_attribute_combination
                    WHERE id_product_attribute IN (' . $comb_ids_ . ')',
                'REPLACE INTO ' . _DB_PREFIX_ . 'product_attribute_combination
                    VALUES ' . implode(', ', $att_rows),
            ];
            $ret &= $this->runSql($sql);
        }
        if ($upd_qty) {
            $this->backupContext();
            foreach ($upd_qty as $id_shop => $quantities) {
                Shop::setContext(Shop::CONTEXT_SHOP, $id_shop);
                foreach ($quantities as $id_comb => $qty) {
                    StockAvailable::setQuantity($id_product, $id_comb, $qty, $id_shop);
                }
            }
            $this->restoreContext();
        }
        if ($ret && !$a['combinations_to_update'][$id_product]) {
            $ret &= $this->updateDefaultCombinationAndSaveProduct($id_product, $a);
        }

        return $ret;
    }

    public function getTaxesRate($id_tax_rules_group)
    {
        $address = Address::initialize();
        $tax_manager = TaxManagerFactory::getManager($address, $id_tax_rules_group);
        $tax_calculator = $tax_manager->getTaxCalculator();

        return $tax_calculator->getTotalRate() / 100;
    }

    public function impactKeys()
    {
        if (!isset($this->impact_keys)) {
            $this->impact_keys = array_keys($this->getCombinationFields());
        }

        return $this->impact_keys;
    }

    public function calculateImpacts($c, $a, $id_shop)
    {
        $impacts = [];
        $base_values = $this->getBaseProductValues($c['id_product'], $id_shop, $a['options']['tax_incl']);
        foreach ($this->impactKeys() as $key) {
            $new_impacts_available = !empty($a['impacts'][$key])
            && array_intersect($c['att_ids'], array_keys($a['impacts'][$key]));
            if ($c['id_comb'] && !$new_impacts_available) {
                continue;
            }
            $value = 0;
            if (isset($c['initial_impacts'][$id_shop][$key])
                && (!$new_impacts_available || empty($a['override_options']['erase_impacts']))) {
                $value = $c['initial_impacts'][$id_shop][$key];
            }
            $percentage_impacts = [];
            foreach ($c['att_ids'] as $id_att) {
                if (!empty($a['impacts'][$key][$id_att]['value'])) {
                    $att_impact = $a['impacts'][$key][$id_att];
                    $number = $this->getImpactNumericValue($att_impact);
                    if ($att_impact['suffix'] == '%') {
                        $percentage_impacts[] = $number;
                    } else {
                        if ($key == 'price' && $base_values['tax_impact']) {
                            $number = $number / (1 + $base_values['tax_impact']);
                        }
                        $value += $number;
                    }
                }
            }
            foreach ($percentage_impacts as $pi) {
                $value += ($base_values[$key] + ($a['complex_percentage'] ? $value : 0)) * $pi / 100;
            }
            if ($value < -$base_values[$key]) {
                $value = -$base_values[$key];
            }
            $impacts[$key] = round($value, 6);
        }

        return $impacts;
    }

    public function getImpactNumericValue($impact)
    {
        $value = (float) preg_replace('/[^0-9.]/', '', str_replace(',', '.', $impact['value']));
        $multiplier = $impact['prefix'] == '-' ? -1 : 1;

        return $value * $multiplier;
    }

    public function updateCombinationObj($c, $id_shop)
    {
        if (!isset($c['id_orig'])) {
            $c['id_orig'] = $c['id_comb'];
        }
        $obj = new Combination($c['id_orig'], null, $id_shop);
        $obj->id_shop_list = [$id_shop];
        $obj->id_product = $c['id_product'];
        $obj->id = $c['id_comb'];
        if (isset($c['id_product_original'])) {
            $c['orig_ref'] = $obj->reference;
            $obj->reference = '';
        } elseif ($c['id_comb'] != $c['id_orig']) {
            $obj->default_on = '';
            $obj->reference = '';
        }
        if ($obj->default_on) {
            // avoid possible Duplicate entry for key 'product_default' in complex multishop scenarios
            $this->eraseDefaultCombinationRecordFromMainTable($obj->id_product);
        }
        if ($c['id_orig'] && !isset($c['options']['quantity'])) {
            $obj->quantity = (int) $this->db->getValue('
                SELECT sa.quantity FROM ' . _DB_PREFIX_ . 'stock_available sa
                WHERE sa.id_product_attribute = ' . (int) $c['id_orig'] . '
                AND sa.id_shop = ' . (int) $id_shop . '
            ');
        }
        foreach ($c['options'] as $name => $value) {
            $obj->$name = $this->formatCombinationValue($value, $name, $c);
        }
        $obj->save();

        return $obj;
    }

    public function getImplodedAttNames($c, $max_chars_per_word)
    {
        $data = $this->db->executeS('
            SELECT al.id_attribute, al.name FROM ' . _DB_PREFIX_ . 'attribute_lang al
            INNER JOIN ' . _DB_PREFIX_ . 'attribute a ON a.id_attribute = al.id_attribute
            INNER JOIN ' . _DB_PREFIX_ . 'attribute_group ag ON ag.id_attribute_group = a.id_attribute_group
            WHERE al.id_lang = ' . (int) Configuration::get('PS_LANG_DEFAULT') . '
            AND al.id_attribute IN (' . $this->sqlIDs($c['att_ids']) . ')
            ORDER BY ag.position ASC
        ');
        $names = [];
        foreach ($data as $d) {
            $name = str_replace([',', '.', '*'], '-', $d['name']);
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
                $replacements = [
                    '{id_product}' => $c['id_product'],
                    '{base_ref}' => $this->getProductReference($c['id_product']),
                    '{iterate}' => $this->getNextIterationNum($c),
                ];
                if (isset($c['id_product_original']) && isset($c['orig_ref'])) {
                    $replacements['{orig_ref}'] = $c['orig_ref'];
                    $base_ref_orig = $this->getProductReference($c['id_product_original']);
                    $replacements['{orig_ref_without_base}'] = str_replace($base_ref_orig, '', $c['orig_ref']);
                }
                if (strpos($value, '{att_names_') !== false) {
                    $max_chars = explode('{att_names_', $value);
                    $max_chars = isset($max_chars[1]) && (int) $max_chars[1] ? (int) $max_chars[1] : 5;
                    $replacements['{att_names_' . $max_chars . '}'] = $this->getImplodedAttNames($c, $max_chars);
                }
                $value = str_replace(array_keys($replacements), $replacements, $value);
                $value = Tools::substr($value, 0, 32); // max allowed length for $combination->reference
                break;
            case 'quantity':
            case 'minimal_quantity':
                $value = (int) $value;
                break;
            default: // impacts
                $value = (float) $value;
                break;
        }

        return strip_tags($value);
    }

    public function getRefVariables()
    {
        $iso_lang = Tools::strtoupper(Language::getIsoById(Configuration::get('PS_LANG_DEFAULT')));
        $variables = [
            '{id_product}' => $this->l('ID of product'),
            '{base_ref}' => $this->l('Base reference of product'),
            '{att_names_5}' => sprintf($this->l('Abbreviated attribute names, 5 characters per word (%s)'), $iso_lang),
            '{iterate}' => $this->l('Iteration number for new combination'),
            '{orig_ref}' => $this->l('Reference of original combination'),
            '{orig_ref_without_base}' => $this->l('same as {orig_ref}, but without base reference'),
        ];

        return $variables;
    }

    public function getProductReference($id_product)
    {
        $var = 'ref_' . $id_product;
        $this->$var = isset($this->$var) ? $this->$var : $this->db->getValue('
            SELECT reference FROM ' . _DB_PREFIX_ . 'product WHERE id_product = ' . (int) $id_product . '
        ');

        return $this->$var;
    }

    public function getNextIterationNum($c)
    {
        $num = $this->db->getValue('
            SELECT COUNT(id_product_attribute) FROM ' . _DB_PREFIX_ . 'product_attribute
            WHERE id_product = ' . (int) $c['id_product']
            . ($c['id_comb'] ? ' AND id_product_attribute < ' . (int) $c['id_comb'] : '') . '
        ');

        return (int) $num + 1;
    }

    public function getBaseProductValues($id_product, $id_shop, $tax_incl)
    {
        $cache_key = 'base_values_' . (int) $id_product . '_' . (int) $id_shop . '_' . (int) $tax_incl;
        if (!isset($this->x[$cache_key])) {
            $data = $this->db->getRow('
                SELECT * FROM ' . _DB_PREFIX_ . 'product p
                INNER JOIN ' . _DB_PREFIX_ . 'product_shop ps
                    ON ps.id_product = p.id_product AND ps.id_shop = ' . (int) $id_shop . '
                WHERE p.id_product = ' . (int) $id_product . '
            ');
            // use same keys as in combination: price, unit_price_impact, etc.
            $this->x[$cache_key] = [
                'price' => $data['price'],
                'unit_price_impact' => $data['unit_price_ratio'] > 0 ?
                $data['price'] / $data['unit_price_ratio'] : 0,
                'wholesale_price' => $data['wholesale_price'],
                'weight' => $data['weight'],
                'tax_impact' => $tax_incl ? $this->getTaxesRate($data['id_tax_rules_group']) : 0,
            ];
        }

        return $this->x[$cache_key];
    }

    public function deleteCombinations($id_product, $a)
    {
        $ret = true;
        $shop_ids = $this->shopIDs();
        $selected_atts = $a['action'] == 'deleteByAtts' ? $a['values'] : [];
        $combination_ids = $this->getExistingCombinations($id_product, $shop_ids, $selected_atts);
        foreach ($combination_ids as $id_comb) {
            if (!$this->combinations_num['max']--) {
                $ret &= false;
                break;
            }
            $c_obj = new Combination($id_comb);
            $ret &= $c_obj->delete();
            ++$this->combinations_num['deleted'];
        }
        if ($ret) {
            if ($selected_atts && $this->getExistingCombinations($id_product, $shop_ids)) {
                $this->updateDefaultCombinationAndSaveProduct($id_product, $a);
            } else {
                Hook::exec(
                    'actionProductAttributeDelete',
                    [
                        'id_product_attribute' => 0,
                        'id_product' => (int) $id_product,
                        'deleteAllAttributes' => true,
                    ]
                );
            }
        }

        return $ret;
    }

    public function getCombinationsToUpdate($id_product, $a)
    {
        $existing_combinations = $to_update = $used_comb_ids = $existing_atts = [];
        $shop_ids = $this->shopIDs();
        $source_product_id = isset($a['id_product_original']) ? $a['id_product_original'] : $id_product;
        $data = $this->db->executeS('
            SELECT
                pac.id_product_attribute AS id_comb, pac.id_attribute AS id_att,
                a.id_attribute_group AS id_group, pa.id_product, pas.id_shop,
                pas.`' . implode('`, pas.`', array_map('bqSQL', $this->impactKeys())) . '`
            FROM ' . _DB_PREFIX_ . 'product_attribute_combination pac
            INNER JOIN ' . _DB_PREFIX_ . 'attribute a ON a.id_attribute = pac.id_attribute
            INNER JOIN ' . _DB_PREFIX_ . 'product_attribute pa
                ON pa.id_product_attribute = pac.id_product_attribute
                AND pa.id_product = ' . (int) $source_product_id . '
            INNER JOIN ' . _DB_PREFIX_ . 'product_attribute_shop pas
                ON pas.id_product_attribute = pa.id_product_attribute
                AND pas.id_shop IN (' . $this->sqlIDs($shop_ids) . ')
            ORDER BY pac.id_product_attribute ASC, a.id_attribute_group ASC'
                . (count($shop_ids) > 1 ? ', FIELD(pas.id_shop,' . $this->sqlIDs($shop_ids) . ')' : '') . '
        ');
        foreach ($data as $row) {
            $id_comb = $row['id_comb'];
            $id_shop = $row['id_shop'];
            if (!isset($existing_combinations[$id_comb])) {
                $existing_combinations[$id_comb] = [
                    'att_ids' => [],
                    'initial_impacts' => [],
                    'shop_ids' => [],
                ];
            }
            if (!isset($existing_combinations[$id_comb]['initial_impacts'][$id_shop])) {
                $existing_combinations[$id_comb]['shop_ids'][$id_shop] = $id_shop;
                foreach ($this->impactKeys() as $key) {
                    $existing_combinations[$id_comb]['initial_impacts'][$id_shop][$key] = $row[$key];
                }
            }
            $existing_combinations[$id_comb]['att_ids'][$row['id_group']] = $row['id_att'];
            $existing_atts[$row['id_group']][$row['id_att']] = $row['id_att'];
        }
        if ($a['action'] == 'duplicate') {
            foreach ($existing_combinations as $id_comb => $c) {
                $to_update[] = $c + [
                    'id_product' => $id_product,
                    'id_product_original' => $a['id_product_original'],
                    'id_comb' => 0,
                    'id_orig' => $id_comb,
                    'upd_atts' => true,
                ];
            }
        } else {
            if ($a['action'] != 'updateByAtts' && $required_atts = $a['values']) {
                if ($a['action'] == 'update') {
                    foreach ($existing_atts as $id_group => $atts) {
                        foreach ($atts as $id_att) {
                            $required_atts[$id_group][$id_att] = $id_att;
                        }
                    }
                }
                $possible_combinations = $this->getPossibleCombinations($required_atts);
            } elseif (isset($a['override_options']) && array_filter($a['override_options'])) {
                $possible_combinations = array_column($existing_combinations, 'att_ids'); // update existing
            } else {
                $possible_combinations = []; // skip all, only updateDefaultCombinationAndSaveProduct
            }
            foreach ($possible_combinations as $att_ids) {
                $comb = [
                    'id_product' => $id_product,
                    'id_comb' => 0,
                    'id_orig' => 0,
                    'att_ids' => $att_ids,
                    'initial_impacts' => [],
                    'shop_ids' => $shop_ids,
                    'upd_atts' => true,
                ];
                foreach ($existing_combinations as $id_comb => $c) {
                    $exactly_same_atts = $att_ids == $c['att_ids'];
                    if ($a['action'] == 'addNew') {
                        if ($comb['skip'] = $exactly_same_atts) {
                            break;
                        }
                    } elseif (array_intersect($c['att_ids'], $att_ids) == $c['att_ids']) {
                        $comb['id_comb'] = !isset($used_comb_ids[$id_comb]) ? $id_comb : 0;
                        $used_comb_ids[$id_comb] = 1;
                        $comb['id_orig'] = $id_comb;
                        $comb['initial_impacts'] = $c['initial_impacts'];
                        $comb['shop_ids'] = $c['shop_ids'];
                        if ($comb['id_comb']) {
                            $comb['upd_atts'] = !$exactly_same_atts;
                            $comb['skip'] = !$comb['upd_atts'] && !$this->matchingSelectedAtts($att_ids, $a['values']);
                        }
                        break;
                    }
                }
                if (empty($comb['skip'])) {
                    $to_update[] = $comb;
                }
            }
        }

        return $to_update;
    }

    public function matchingSelectedAtts($current_atts, $selected_atts)
    {
        $matching = true;
        foreach ($selected_atts as $atts_in_group) {
            $matching &= (bool) array_intersect($current_atts, $atts_in_group);
        }

        return $matching;
    }

    public function getExistingCombinations($id_product, $shop_ids = [], $selected_atts = [])
    {
        $query = new DbQuery();
        $query->select('pa.id_product_attribute AS id_comb');
        $query->from('product_attribute', 'pa');
        if ($shop_ids_ = $this->sqlIDs($shop_ids)) {
            $on = 'pas.id_product_attribute = pa.id_product_attribute AND pas.id_shop IN (' . $shop_ids_ . ')';
            $query->innerJoin('product_attribute_shop', 'pas', $on);
        }
        $query->where('pa.id_product = ' . (int) $id_product);
        $comb_ids = array_column($this->db->executeS($query), 'id_comb', 'id_comb');
        if ($selected_atts && $comb_ids_ = $this->sqlIDs($comb_ids)) {
            $comb_atts = [];
            $rows = $this->db->executeS('
                SELECT pac.id_product_attribute AS id_comb,
                pac.id_attribute AS id_att, a.id_attribute_group AS id_group
                FROM ' . _DB_PREFIX_ . 'product_attribute_combination pac
                INNER JOIN ' . _DB_PREFIX_ . 'attribute a ON a.id_attribute = pac.id_attribute
                WHERE pac.id_product_attribute IN (' . $comb_ids_ . ')
            ');
            foreach ($rows as $row) {
                $comb_atts[$row['id_comb']][$row['id_group']] = $row['id_att'];
            }
            foreach ($comb_atts as $id_comb => $atts) {
                foreach ($selected_atts as $id_group => $atts_in_group) {
                    if (!array_intersect($atts, $atts_in_group)) {
                        unset($comb_ids[$id_comb]);
                    }
                }
            }
        }

        return $comb_ids;
    }

    public function updateDefaultCombinationAndSaveProduct($id_product, $a)
    {
        $custom_order = [
            'min_price' => ['by' => 'pas.price', 'way' => 'ASC'],
            'max_price' => ['by' => 'pas.price', 'way' => 'DESC'],
            'min_weight' => ['by' => 'pas.weight', 'way' => 'ASC'],
            'max_weight' => ['by' => 'pas.weight', 'way' => 'DESC'],
        ];
        $order = false;
        if (isset($a['options']['default_combination'])
            && isset($custom_order[$a['options']['default_combination']])) {
            $order = $custom_order[$a['options']['default_combination']];
        }
        $shop_ids = $this->shopIDs();
        $default_shop_id = Configuration::get('PS_SHOP_DEFAULT');
        if (isset($shop_ids[$default_shop_id])) {
            unset($shop_ids[$default_shop_id]);
            $shop_ids[$default_shop_id] = $default_shop_id; // move to the end
        }
        $this->backupContext();
        foreach ($shop_ids as $id_shop) {
            Shop::setContext(Shop::CONTEXT_SHOP, $id_shop);
            if ($id_combination_default = $this->db->getValue('
                SELECT pa.id_product_attribute FROM ' . _DB_PREFIX_ . 'product_attribute pa
                INNER JOIN ' . _DB_PREFIX_ . 'product_attribute_shop pas
                    ON pa.id_product_attribute = pas.id_product_attribute
                    AND pas.id_shop = ' . (int) $id_shop . '
                WHERE pa.id_product = ' . (int) $id_product . '
                ORDER BY ' . ($order ? $this->sqlOrder($order) . ', ' : '')
                    . 'pas.default_on DESC, pa.id_product_attribute ASC
            ')) {
                try {
                    $product = new Product($id_product);
                    $product->deleteDefaultAttributes();
                    $this->eraseDefaultCombinationRecordFromMainTable($id_product);
                    $product->setDefaultAttribute($id_combination_default);
                    Hook::exec('actionProductUpdate', ['id_product' => $product->id, 'product' => $product]);
                    if ($product->depends_on_stock) {
                        StockAvailable::synchronize($product->id);
                    }
                    // $this->updateSupplierReferences($product->id);
                } catch (Exception $e) {
                    $this->throwError($this->l('Product [ID=' . $id_product . ']') . ': ' . $e->getMessage());
                }
            }
        }
        if (end($shop_ids) != $default_shop_id) {
            // update default combination records in ps_product, ps_product_attribute
            Shop::setContext(Shop::CONTEXT_SHOP, $default_shop_id);
            Product::updateDefaultAttribute($this->id_product);
        }
        $this->restoreContext();

        return true;
    }

    public function eraseDefaultCombinationRecordFromMainTable($id_product)
    {
        return $this->db->execute('
            UPDATE ' . _DB_PREFIX_ . 'product_attribute SET default_on = NULL
            WHERE id_product = ' . (int) $id_product . ' AND default_on = 1
        ');
    }

    /*
    * Temporarily not used
    */
    public function updateSupplierReferences($id_product)
    {
        $combination_ids = $this->getExistingCombinations($id_product);
        $supplier_ids = array_column($this->db->executeS('
            SELECT DISTINCT(id_supplier) FROM ' . _DB_PREFIX_ . 'product_supplier
            WHERE id_product = ' . (int) $id_product . ' AND id_product_attribute = 0
        '), 'id_supplier');
        $rows = [];
        foreach ($supplier_ids as $id_supplier) {
            foreach ($combination_ids as $id_comb) {
                $rows[] = '(\'\', ' . (int) $id_product . ', ' . (int) $id_comb . ', ' . (int) $id_supplier . ', \'\')';
            }
        }
        if ($rows) {
            $this->db->execute('
                INSERT INTO ' . _DB_PREFIX_ . 'product_supplier
                (id_product, id_product_attribute, id_supplier)
                VALUES ' . implode(', ', $rows) . ' ON DUPLICATE KEY UPDATE id_supplier=VALUES(id_supplier)
            ');
        }
    }

    public function getDataPath($type)
    {
        return $this->local_path . 'data/' . $type . '.txt';
    }

    public function getData($type)
    {
        $path = $this->getDataPath($type);

        return file_exists($path) ? json_decode(Tools::file_get_contents($path), true) : [];
    }

    public function saveData($type, $data, $append = false)
    {
        $path = $this->getDataPath($type);
        $data = is_string($data) ? $data : json_encode($data);

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
        $this->backup_context = ['shop_context' => Shop::getContext(), 'shop_context_id' => null];
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

    public function sqlIDs($ids)
    {
        return $this->formatIDs($ids, true);
    }

    public function sqlColumn($column_name)
    {
        return strpos($column_name, '.') === false ? '`' . bqSQL($column_name) . '`'
            : '`' . implode('`.`', array_map('bqSQL', explode('.', $column_name))) . '`'; // has alias
    }

    public function sqlOrder($order)
    {
        return $this->sqlColumn($order['by']) . ' '
            . (isset($order['way']) && strtoupper($order['way']) == 'DESC' ? 'DESC' : 'ASC');
    }

    public function shopIDs($type = 'context', $implode = false)
    {
        if (!isset($this->x['shop_ids'][$type])) {
            $this->x['shop_ids'][$type] = $type == 'context' ? Shop::getContextListShopID()
                : Shop::getShops(false, null, true);
        }

        return $this->formatIDs($this->x['shop_ids'][$type], $implode);
    }

    public function formatIDs($ids, $return_string = true)
    {
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        $ids = array_map('intval', $ids);
        $ids = array_combine($ids, $ids);
        unset($ids[0]);

        return $return_string ? implode(',', $ids) : $ids;
    }

    public function getIDsFromString($string_of_ids)
    {
        $ids = explode(',', $string_of_ids);
        if (strpos($string_of_ids, '-') !== false) {
            $upd_ids = [];
            foreach ($ids as $id) {
                if (strpos($id, '-')) { // 0 if $id starts from '-'
                    $r = explode('-', $id);
                    foreach (range((int) $r[0], (int) $r[1]) as $id_in_range) {
                        $upd_ids[] = $id_in_range;
                    }
                } else {
                    $upd_ids[] = (int) $id;
                }
            }
            $ids = $upd_ids;
        }

        return array_unique($ids);
    }

    public function getInfoLinks()
    {
        $links = [
            'documentation' => [
                'title' => $this->l('Documentation'),
                'icon' => 'file-text',
                'url' => $this->_path . 'readme_en.pdf?v=' . $this->version,
            ],
            'changelog' => [
                'title' => $this->l('Changelog'),
                'icon' => 'code-fork',
                'url' => $this->_path . 'Readme.md?v=' . $this->version,
            ],
            'contact' => [
                'title' => $this->l('Contact us'),
                'icon' => 'envelope',
                'url' => 'https://addons.prestashop.com/en/contact-us?id_product=18240',
            ],
            'modules' => [
                'title' => $this->l('Our modules'),
                'icon' => 'download',
                'url' => 'https://addons.prestashop.com/en/2_community-developer?contributor=64815',
            ],
        ];

        return $links;
    }

    public function exportSettings()
    {
        $data = [];
        parse_str(Tools::getValue('serialized_data'), $data);
        $file_content = json_encode($data);
        $file_name = 'bcg-settings-' . date('d-m-Y') . '.txt';
        header('Content-disposition: attachment; filename=' . $file_name);
        header('Content-type: text/plain');
        echo $file_content;
        exit;
    }

    public function throwError($error_text, $class = 'error')
    {
        $this->eraseData();
        exit(json_encode(['error' => $error_text, 'class' => $class]));
    }
}
