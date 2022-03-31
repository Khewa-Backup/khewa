<?php
/**
 * 2007-2021 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@buy-addons.com so we can send you a copy.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@buy-addons.com>
 *  @copyright 2007-2021 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class BaViewProduct extends ReportSale
{
    private $orderby;
    private $orderway;
    private $ps_searchable_fields = array('id_shop','shop_name', 'products_id',
        'product_reference', 'product_name',
        'supplier_reference', 'EAN_reference',
        'UPC_reference', 'total_quantity', 'current_stock',
        'AVG_unit_price', 'tax_rate',
        'total_discounts_tax_excl', 'discounts_tax_amount',
        'total_products_no_tax', 'products_tax',
        'including_ecotax_tax_amount', 'including_ecotax_tax_excl',
        'net_tax_product_reduction', 'total_cost',
        'gross_profit', 'gross_margin',
        'net_profit', 'net_margin',
        'manufacturer_name', 'category_name',
        'of_total_sales', 'of_total_gross_profits',
        'of_total_net_profits',
        'customers_data', 'total_customers', 'orders_data', 'total_orders', 'supplier_name'
        );

    public function setWhereClause($helper)
    {
        foreach ($this->ps_searchable_fields as $search_field) {
            $search_value = Tools::getValue($helper->list_id . "Filter_" . $search_field, null);
            if ($search_value !== null) {
                $this->ps_where[] = " $search_field LIKE '%" . pSQL($search_value) . "%' ";
                $this->context->cookie->{$helper->list_id . 'Filter_' . $search_field} = pSQL($search_value);
            } else {
                $this->context->cookie->{$helper->list_id . 'Filter_' . $search_field} = null;
            }
        }
        if (!empty($this->ps_where)) {
            $whereClause = " WHERE " . implode(" AND ", $this->ps_where);
        } else {
            $whereClause = '';
        }
        return $whereClause;
    }
    public function resetList()
    {
        $helper_list_id = $this->name . 'ba_report_product';
        foreach ($this->ps_searchable_fields as $search_field) {
            $this->context->cookie->{$helper_list_id . 'Filter_' . $search_field} = null;
        }
        Configuration::updateValue($this->name.'_product_where', null);
    }
    public function viewproductlist()
    {
        $helper = new HelperList();
        $fields_list = array(
            'id_shop' => array(
                'title' => $this->l('ID shop'),
                'type' => 'text'
            ),
            'shop_name' => array(
                'title' => $this->l('Shop name'),
                'type' => 'text'
            ),
            'products_id' => array(
                'title' => $this->l('Products ID'),
                'type' => 'text'
            ),
            'product_reference' => array(
                'title' => $this->l('Product Reference'),
                'type' => 'text'
            ),
            'product_name' => array(
                'title' => $this->l('Product Name'),
                'type' => 'text',
                'width' => 300,
            ),
            'supplier_reference' => array(
                'title' => $this->l('Supplier Reference'),
                'type' => 'text'
            ),
            'supplier_name' => array(
                'title' => $this->l('Supplier Name'),
                'type' => 'text'
            ),
            'total_customers' => array(
                'title' => $this->l('#Customers Purchased'),
                'type' => 'text',
                'align' => 'right',
                'callback' => 'formatNumber',
                'callback_object' => $this
            ),
            'customers_data' => array(
                'title' => $this->l('Customers Data'),
                'type' => 'text',
                'orderby' => false,
                'callback' => 'formatCustomerData',
                'callback_object' => $this
            ),
            'total_orders' => array(
                'title' => $this->l('#Orders'),
                'type' => 'text',
                'align' => 'right',
                'callback' => 'formatNumber',
                'callback_object' => $this
            ),
            'orders_data' => array(
                'title' => $this->l('Order Data'),
                'type' => 'text',
                'orderby' => false,
                'callback' => 'formatOrderData',
                'callback_object' => $this
            ),
            'EAN_reference' => array(
                'title' => $this->l('EAN Reference'),
                'type' => 'text'
            ),
            'UPC_reference' => array(
                'title' => $this->l('UPC Reference'),
                'type' => 'text'
            ),
            'current_stock' => array(
                'title' => $this->l('Current Stock'),
                'type' => 'text'
            ),
            'total_quantity' => array(
                'title' => $this->l('Total Sold'),
                'type' => 'text'
            ),
            'AVG_unit_price' => array(
                'title' => $this->l('AVG Unit Price'),
                'type' => 'text',
                'callback' => 'convertMoney',
                'callback_object' => $this
            ),
            'tax_rate' => array(
                'title' => $this->l('Tax Rate'),
                'type' => 'text',
                'callback' => 'convertPercent',
                'callback_object' => $this
            ),
            'total_discounts_tax_excl' => array(
                'title' => $this->l('Total Discounts Tax Excl'),
                'type' => 'text',
                'callback' => 'convertMoney',
                'callback_object' => $this
            ),
            'discounts_tax_amount' => array(
                'title' => $this->l('Discounts Tax Amount'),
                'type' => 'text',
                'callback' => 'convertMoney',
                'callback_object' => $this
            ),
            'total_products_no_tax' => array(
                'title' => $this->l('Total Products No Tax'),
                'type' => 'text',
                'callback' => 'convertMoney',
                'callback_object' => $this
            ),
            'products_tax' => array(
                'title' => $this->l('Products Tax'),
                'type' => 'text',
                'callback' => 'convertMoney',
                'callback_object' => $this
            ),
            'including_ecotax_tax_amount' => array(
                'title' => $this->l('Including Ecotax Tax Amount'),
                'type' => 'text',
                'callback' => 'convertMoney',
                'callback_object' => $this
            ),
            'including_ecotax_tax_excl' => array(
                'title' => $this->l('Including Ecotax Tax Excl'),
                'type' => 'text',
                'callback' => 'convertMoney',
                'callback_object' => $this
            ),
            'net_tax_product_reduction' => array(
                'title' => $this->l('Net Tax Product Reduction'),
                'type' => 'text',
                'callback' => 'convertMoney',
                'callback_object' => $this
            ),
            'total_cost' => array(
                'title' => $this->l('Total Cost'),
                'type' => 'text',
                'callback' => 'convertMoney',
                'callback_object' => $this
            ),
            'gross_profit' => array(
                'title' => $this->l('Gross Profit'),
                'type' => 'text',
                'callback' => 'convertMoney',
                'callback_object' => $this
            ),
            'gross_margin' => array(
                'title' => $this->l('Gross Margin'),
                'type' => 'text',
                'callback' => 'convertPercent',
                'callback_object' => $this
            ),
            'net_profit' => array(
                'title' => $this->l('Net Profit'),
                'type' => 'text',
                'callback' => 'convertMoney',
                'callback_object' => $this
            ),
            'net_margin' => array(
                'title' => $this->l('Net Margin'),
                'type' => 'text',
                'callback' => 'convertPercent',
                'callback_object' => $this
            ),
            'manufacturer_name' => array(
                'title' => $this->l('Manufacturer Name'),
                'type' => 'text'
            ),
            'category_name' => array(
                'title' => $this->l('Category Name'),
                'type' => 'text'
            ),
            'of_total_sales' => array(
                'title' => $this->l('% Of Total Sales'),
                'type' => 'text',
                'callback' => 'convertPercent',
                'callback_object' => $this
            ),
            'of_total_gross_profits' => array(
                'title' => $this->l('% Of Total Gross profits'),
                'type' => 'text',
                'callback' => 'convertPercent',
                'callback_object' => $this
            ),
            'of_total_net_profits' => array(
                'title' => $this->l('% Of Total Net Profits'),
                'type' => 'text',
                'callback' => 'convertPercent',
                'callback_object' => $this
            )
        );
        $helper->shopLinkType = '';
        $helper->identifier = 'id_report';
        $helper->show_toolbar = true;
        $helper->simple_header = false;
        $helper->no_link = true;
        $helper->title = $this->l('Report Product');
        $helper->table = $this->name . 'ba_report_product';
        $helper->list_id = $this->name . 'ba_report_product';
        $this->orderby = pSQL(Tools::getValue($helper->list_id . "Orderby", "products_id"));
        $this->orderway = pSQL(Tools::getValue($helper->list_id . "Orderway", "ASC"));
        $helper->orderBy = $this->orderby;
        $helper->orderWay = Tools::strtoupper($this->orderway);
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $c1 = AdminController::$currentIndex;
        $n1 = $this->name;
        $ad1 = Tools::getAdminTokenLite('AdminModules');
        $o1 = "reportsaleba_report_productOrderby";
        $o2 = "reportsaleba_report_productOrderway";
        $od1 = $this->orderby;
        $od2 = $this->orderway;
        $l1 = "csv=product";
        $helper->toolbar_btn['export'] = array(
            'href' => $c1.'&configure='.$n1.'&token='.$ad1.'&task=product&'.$o1.'='.$od1.'&'.$o2.'='.$od2.'&'.$l1.'',
            'desc' => $this->l('export csv')
        );
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name . '&task=product';
        $helper->currentIndex .= '&'.$helper->list_id . "Orderby=".$helper->orderBy;
        $helper->currentIndex .= '&'.$helper->list_id . "Orderway=".$helper->orderWay;
        $con = (int) $this->countData($helper);
        $helper->listTotal = $con;
        /*****-----------------Pagination----------------********** */
        if ($this->context->cookie->{$helper->list_id . '_pagination'} < 20) {
            $this->context->cookie->{$helper->list_id . '_pagination'} = 20;
        }
        $page = $this->context->cookie->{$helper->list_id . '_pagination'};
        $selected_pagination = (int) Tools::getValue($helper->list_id . '_pagination', $page);
        if ($selected_pagination <= 0) {
            $selected_pagination = 20;
        }
        $page = (int) Tools::getValue('submitFilter' . $helper->list_id);
        if (!$page) {
            $page = 1;
        }
        $start = ($page - 1 ) * $selected_pagination;
        $rows = $this->selectdataproduct($helper, $start, $selected_pagination);
        $table_helper = $helper->generateList($rows, $fields_list);
        $table_helper .= $this->getSummaryBlock($helper, $fields_list);
        return $table_helper;
    }
    public function getSummaryBlock($helper, $fields_list)
    {
        $sql = 'SELECT COUNT(DISTINCT id_shop) as id_shop';
        $sql .= ", COUNT(DISTINCT shop_name) - COUNT(DISTINCT case when shop_name='' then 1 end) as shop_name";
        $sql .= ", COUNT(DISTINCT id_order) - COUNT(DISTINCT case when id_order='' then 1 end) as id_order";
        $sql .= ", COUNT(DISTINCT products_id) - COUNT(DISTINCT case when products_id='' then 1 end) as products_id";
        $sql .= ", COUNT(DISTINCT product_reference) - COUNT(DISTINCT case when product_reference='' then 1 end)";
        $sql .= " as product_reference";
        $sql .= ", COUNT(DISTINCT supplier_reference) - COUNT(DISTINCT case when supplier_reference='' then 1 end)";
        $sql .= " as supplier_reference";
        $sql .= ", COUNT(DISTINCT supplier_name) - COUNT(DISTINCT case when supplier_name='' then 1 end)";
        $sql .= " as supplier_name";
        $sql .= ", COUNT(DISTINCT EAN_reference) - COUNT(DISTINCT case when EAN_reference='' then 1 end)";
        $sql .= " as EAN_reference";
        $sql .= ", COUNT(DISTINCT UPC_reference) - COUNT(DISTINCT case when UPC_reference='' then 1 end)";
        $sql .= " as UPC_reference";
        $sql .= ", SUM(total_quantity) as total_quantity";
        $sql .= ", SUM(current_stock) as current_stock";
        $sql .= ", COUNT(DISTINCT manufacturer_name) - COUNT(DISTINCT case when manufacturer_name='' then 1 end)";
        $sql .= " as manufacturer_name";
        $sql .= ", COUNT(DISTINCT category_name) - COUNT(DISTINCT case when category_name='' then 1 end)";
        $sql .= " as category_name";
        $sql .= ', MIN(order_add_date) as min_order_add_date';
        $sql .= ', MAX(order_add_date) as max_order_add_date';
        $sql .= ', MIN(total_customers) as min_total_customers';
        $sql .= ', MAX(total_customers) as max_total_customers';
        $sql .= ', MIN(total_orders) as min_total_orders';
        $sql .= ', MAX(total_orders) as max_total_orders';
        $sql .= ' FROM ' . _DB_PREFIX_ . 'ba_report_products ';
        $sql .= $this->setWhereClause($helper);
        $item1 = DB::getInstance()->getRow($sql, false);
        if (empty($item1)) {
            return false;
        }
        $sql = 'SELECT products_id';
        $sql .= ', AVG(AVG_unit_price) as AVG_unit_price';
        $sql .= ', AVG(tax_rate) as tax_rate';
        $sql .= ', SUM(total_discounts_tax_excl) as total_discounts_tax_excl';
        $sql .= ', SUM(discounts_tax_amount) as discounts_tax_amount';
        $sql .= ', SUM(total_products_no_tax) as total_products_no_tax';
        $sql .= ', SUM(products_tax) as products_tax';
        $sql .= ', SUM(including_ecotax_tax_amount) as including_ecotax_tax_amount';
        $sql .= ', SUM(including_ecotax_tax_excl) as including_ecotax_tax_excl';
        $sql .= ', SUM(net_tax_product_reduction) as net_tax_product_reduction';
        $sql .= ', SUM(total_cost) as total_cost';
        $sql .= ', SUM(gross_profit) as gross_profit';
        $sql .= ', SUM(gross_margin) as gross_margin';
        $sql .= ', SUM(net_profit) as net_profit';
        $sql .= ', SUM(net_margin) as net_margin';
        $sql .= ', SUM(of_total_sales) as of_total_sales';
        $sql .= ', SUM(cumulative_of_total_sales) as cumulative_of_total_sales';
        $sql .= ', SUM(of_total_gross_profits) as of_total_gross_profits';
        $sql .= ', SUM(cumulative_of_total_gross_profits) as cumulative_of_total_gross_profits';
        $sql .= ', SUM(of_total_net_profits) as of_total_net_profits';
        $sql .= ', SUM(cumulative_of_total_net_profits) as cumulative_of_total_net_profits';
        $sql .= ', SUM(AVG_unit_price * total_quantity) as total';
        $sql .= ' FROM ' . _DB_PREFIX_ . 'ba_report_products ';
        $sql .= $this->setWhereClause($helper);
        $item2 = DB::getInstance()->getRow($sql, false);
        if (empty($item2)) {
            return false;
        }
        $data = array(
            'AVG_unit_price' => $item2['AVG_unit_price'],
            'tax_rate' => $item2['tax_rate'],
            'total_discounts_tax_excl' => $item2['total_discounts_tax_excl'],
            'discounts_tax_amount' => $item2['discounts_tax_amount'],
            'total_products_no_tax' => $item2['total_products_no_tax'],
            'products_tax' => $item2['products_tax'],
            'including_ecotax_tax_amount' => $item2['including_ecotax_tax_amount'],
            'including_ecotax_tax_excl' => $item2['including_ecotax_tax_excl'],
            'net_tax_product_reduction' => $item2['net_tax_product_reduction'],
            'total_cost' => $item2['total_cost'],
            'gross_profit' => $item2['gross_profit'],
            'gross_margin' => 0,
            'net_profit' => $item2['net_profit'],
            'net_margin' => 0,
            'of_total_sales' => $item2['of_total_sales'],
            'cumulative_of_total_sales' => $item2['cumulative_of_total_sales'],
            'of_total_gross_profits' => $item2['of_total_gross_profits'],
            'cumulative_of_total_gross_profits' => $item2['cumulative_of_total_gross_profits'],
            'of_total_net_profits' => $item2['of_total_net_profits'],
            'cumulative_of_total_net_profits' => $item2['cumulative_of_total_net_profits'],
        );
        $default_currency = (int) Configuration::get('PS_CURRENCY_DEFAULT');
        $c_to = new Currency($default_currency);
        // calculate % gross margin
        $total = $item2['total'];
        if ($total > 0) {
            $data['gross_margin'] = (float) $data['gross_profit'] / $total;
        }
        if ($item2['total_products_no_tax'] > 0) {
            $t = (float) $item2['total_products_no_tax'];
            $data['net_margin'] = (float) $data['net_profit'] / $t;
        }
        $data = array_merge($item1, $data);
        $summary = array();
        $summary['id_shop'] = array(
            $this->l('#ID Shop'),
            number_format($data['id_shop'])
        );
        $summary['shop_name'] = array(
            $this->l('#Shop name'),
            number_format($data['shop_name'])
        );
        $summary['products_id'] = array(
            $this->l('#Products ID'),
            number_format($data['products_id'])
        );
        $summary['product_reference'] = array(
            $this->l('#Product Reference'),
            number_format($data['product_reference'])
        );
        $summary['product_name'] = array(
            $this->l('Product Name'),
            $this->l('-')
        );
        $summary['supplier_reference'] = array(
            $this->l('#Supplier Reference'),
            number_format($data['supplier_reference'])
        );
        $summary['supplier_name'] = array(
            $this->l('#Supplier Name'),
            number_format($data['supplier_name'])
        );
        $summary['total_customers'] = array(
            $this->l('#Customers Purchased'),
            number_format($data['min_total_customers']).$this->l(' - ').number_format($data['max_total_customers'])
        );
        $summary['customers_data'] = array(
            $this->l('Customers Data'),
            $this->l('-')
        );
        $summary['total_orders'] = array(
            $this->l('#Orders '),
            number_format($data['min_total_orders']).$this->l(' - ').number_format($data['max_total_orders'])
        );
        $summary['orders_data'] = array(
            $this->l('Order Data'),
            $this->l('-')
        );
        $summary['EAN_reference'] = array(
            $this->l('#EAN Reference'),
            number_format($data['EAN_reference'])
        );
        $summary['UPC_reference'] = array(
            $this->l('#UPC Reference'),
            number_format($data['UPC_reference'])
        );
        $summary['current_stock'] = array(
            $this->l('#Current Stock'),
            number_format($data['current_stock'])
        );
        $summary['total_quantity'] = array(
            $this->l('Total Sold'),
            number_format($data['total_quantity'])
        );
        $summary['AVG_unit_price'] = array(
            $this->l('AVG Unit Price'),
            Tools::displayPrice($data['AVG_unit_price'], $c_to)
        );
        $summary['tax_rate'] = array(
            $this->l('AVG Tax Rate'),
            round($data['tax_rate'], 0).$this->l('%')
        );
        $summary['total_discounts_tax_excl'] = array(
            $this->l('Total Discounts Tax Excl'),
            Tools::displayPrice($data['total_discounts_tax_excl'], $c_to)
        );
        $summary['discounts_tax_amount'] = array(
            $this->l('Discounts Tax Amount'),
            Tools::displayPrice($data['discounts_tax_amount'], $c_to)
        );
        $summary['total_products_no_tax'] = array(
            $this->l('Total Products No Tax'),
            Tools::displayPrice($data['total_products_no_tax'], $c_to)
        );
        $summary['products_tax'] = array(
            $this->l('Products Tax'),
            Tools::displayPrice($data['products_tax'], $c_to)
        );
        $summary['including_ecotax_tax_amount'] = array(
            $this->l('Including Ecotax Tax Amount'),
            Tools::displayPrice($data['including_ecotax_tax_amount'], $c_to)
        );
        $summary['including_ecotax_tax_excl'] = array(
            $this->l('Including Ecotax Tax Excl'),
            Tools::displayPrice($data['including_ecotax_tax_excl'], $c_to)
        );
        $summary['net_tax_product_reduction'] = array(
            $this->l('Net Tax Product Reduction'),
            Tools::displayPrice($data['net_tax_product_reduction'], $c_to)
        );
        $summary['total_cost'] = array(
            $this->l('Total Cost'),
            Tools::displayPrice($data['total_cost'], $c_to)
        );
        $summary['gross_profit'] = array(
            $this->l('Gross Profit'),
            Tools::displayPrice($data['gross_profit'], $c_to)
        );
        $summary['gross_margin'] = array(
            $this->l('Gross Margin'),
            round($data['gross_margin'] * 100, 2).$this->l('%')
        );
        $summary['net_profit'] = array(
            $this->l('Net Profit'),
            Tools::displayPrice($data['net_profit'], $c_to)
        );
        $summary['net_margin'] = array(
            $this->l('Net Margin'),
            round($data['net_margin'] * 100, 2).$this->l('%')
        );
        $summary['manufacturer_name'] = array(
            $this->l('#Manufacturer Name'),
            number_format($data['manufacturer_name'])
        );
        $summary['category_name'] = array(
            $this->l('#Category Name'),
            number_format($data['category_name'])
        );
        $summary['of_total_sales'] = array(
            $this->l('% Of Total Sales'),
            number_format($data['of_total_sales']).$this->l('%')
        );
        $summary['of_total_gross_profits'] = array(
            $this->l('% Of Total Gross profits'),
            number_format($data['of_total_gross_profits']).$this->l('%')
        );
        $summary['of_total_net_profits'] = array(
            $this->l('% Of Total Net Profits'),
            number_format($data['of_total_net_profits']).$this->l('%')
        );
        $this->smarty->assign('summary', $summary);
        $this->smarty->assign('fields_list', $fields_list);
        return $this->shortDisplay('views/templates/admin/summary_table.tpl');
    }
    public function selectdataproduct($helper, $start, $selected_pagination)
    {
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'ba_report_products ';
        $where = $this->setWhereClause($helper);
        $sql .= $where;
        Configuration::updateValue($this->name.'_product_where', $where);
        $sql .=' ORDER BY ' . pSQL($this->orderby) . ' ' . pSQL($this->orderway)
        . ' LIMIT ' . (int) $start . ', ' . (int) $selected_pagination;
        $rows = Db::getInstance()->executeS($sql, true, false);
        return ($rows);
    }
    public function insertreportproduct($id_order)
    {
        $order = new Order($id_order);
        $id_currency = (int) $order->id_currency;
        $c_from = new Currency($id_currency);
        
        $products = $order->getproducts();
        foreach ($products as &$product) {
            if (!isset($product['original_wholesale_price'])) {
                $p_id = $product['product_id'];
                $a_id = $product['product_attribute_id'];
                $product['original_wholesale_price'] = $this->getWholeSalePrice($p_id, $a_id);
            }
            $p = $this->convertProductToDefaultCurrenct($product, $c_from);
            // since 1.0.20
            $customer = new Customer($order->id_customer);
            $this->insertProduct($p, $order, $customer);
        }
        return true;
    }
    public function insertProduct($product, $order, $customer)
    {
        $new_id_manufacturer = (int) $product['id_manufacturer'];
        $new_id_category_default = (int) $product['id_category_default'];
        $new_id_supplier = (int) $product['id_supplier'];
        $manufacturer_name = Manufacturer::getNameById($new_id_manufacturer);
        $categories = json_decode(Configuration::get('PR_category'), true);
        $manufacturers = json_decode(Configuration::get('PR_manufacturers'), true);
        $suppliers = json_decode(Configuration::get('PR_supplier'), true);
        if (!empty($categories) && !in_array($new_id_category_default, $categories)) {
            return false;
        }
        if (!empty($manufacturers) && !in_array($new_id_manufacturer, $manufacturers)) {
            return false;
        }
        if (!empty($suppliers) && !in_array($new_id_supplier, $suppliers)) {
            return false;
        }
        $category_name = $this->getCategoryName($new_id_category_default, $order->id_shop);
        $shop_name = $this->getShopName((int)$order->id_shop);
        $unit_price = $product['unit_price_tax_excl'];
        $tax_rate = $product['tax_rate'];
        /* get data in product order */
        $id_cart = $order->id_cart;
        $id_order = $order->id;
        $order_add_date = $order->date_add;
        $invoice_add_date = $order->invoice_date;
        $delivery_date = $order->delivery_date;
        $invoice_number = $order->invoice_number;
        $invoice_status = '';
        $product_id = $product['product_id'];
        $product_reference = $product['product_reference'];
        $product_name = $product['product_name'];
        $supplier_reference = $product['product_supplier_reference'];
        $supplier_name = $this->getSupplierName($new_id_supplier);
        $EAN_reference = $product['product_ean13'];
        $UPC_reference = $product['product_upc'];
        $total_quantity = (int) $product['product_quantity'];
        $AVG_unit_price = $unit_price;

        $total_discounts_tax_excl = '';
        $discounts_tax_amount = '';
        /******** discount total ****/
        $product_discount = $this->calcDiscount($product);
        $total_discounts_tax_excl = $product_discount['total_discounts_tax_excl'];
        $discounts_tax_amount = $product_discount['discounts_tax_amount'];
        
        $total_products_no_tax = (double) $unit_price * (int) $total_quantity;
        $product_tax = ((double) ($tax_rate / 100) * ($AVG_unit_price)) * (int) $total_quantity;
        
        /** eco TAX **/
        $ecotax_incl = $product['ecotax'];
        $ecotax_tax_rate = $product['ecotax_tax_rate'];
        $ecotax_tax_excl = ($ecotax_incl/ (1+$ecotax_tax_rate/100));
        $including_ecotax_tax_amount = ($ecotax_incl -  $ecotax_tax_excl) * $total_quantity;
        $including_ecotax_tax_excl = $ecotax_tax_excl * $total_quantity;
        $net_tax_product_reduction = ((double) ($tax_rate / 100) * ($AVG_unit_price)) * $total_quantity;
        $total_cost = ($product['original_wholesale_price']) * $total_quantity;
        $gross_profit = ($AVG_unit_price - $product['original_wholesale_price']) * $total_quantity;
        if ($total_products_no_tax > 0) {
            $gross_margin = ($gross_profit / $total_products_no_tax) * 100;
        } else {
            $gross_margin = 100;
        }
        $net_profit = $gross_profit - $total_discounts_tax_excl;
        if ($total_products_no_tax > 0) {
            $net_margin = ($net_profit / $total_products_no_tax) * 100;
        } else {
            $net_margin = 100;
        }
        $id_combinations = $product['product_attribute_id'];

        $data = $this->getreport($product_id, $id_combinations, (int)$order->id_shop);
        $current_stock = StockAvailable::getQuantityAvailableByProduct($product_id, $id_combinations);
        $count_product=(int)$this->getCountProduct($product['product_id']);

        // since 1.0.20
        $customers_data = array(
            'id' => $customer->id,
            'last_name' => $customer->lastname,
            'first_name' => $customer->firstname,
            'email' => $customer->email,
            'total_order' => 1,
        );
        $orders_data = array(
            'id' => $order->id,
            'reference' => $order->reference,
            'quantity' => (int) $product['product_quantity'],
        );
        if ($data != null && $count_product!=0) {
            $get_data = $data[0];
            $total_qtity = $total_quantity + $get_data['total_quantity'];
            $AVG = $get_data['AVG_unit_price'];
            $qtity = $get_data['total_quantity'];
            $AVG_unit_price=(($AVG*$qtity)+$total_products_no_tax)/($total_qtity);
            $total_discounts_tax_excl = $total_discounts_tax_excl + $get_data['total_discounts_tax_excl'];
            $discounts_tax_amount = $discounts_tax_amount + $get_data['discounts_tax_amount'];
            $total_products_no_t = $AVG_unit_price * $total_qtity;
            $product_tax = $product_tax + $get_data['products_tax'];
            $including_ecotax_tax_amount = $including_ecotax_tax_amount + $get_data['including_ecotax_tax_amount'];
            $including_ecotax_tax_excl = $including_ecotax_tax_excl + $get_data['including_ecotax_tax_excl'];
            $net_tax_product_reduction = $net_tax_product_reduction + $get_data['net_tax_product_reduction'];
            $total_cost = (double) ($product['original_wholesale_price']) * (int) $total_qtity;
            $gross_prof = ($AVG_unit_price - (double) ($product['original_wholesale_price'])) * $total_qtity;
            $net_prof = $net_profit + $get_data['net_profit'];
            $gross_margin = ($gross_prof / $total_products_no_t) * 100;
            $net_margin = ($net_prof / $total_products_no_t) * 100;
            // since 1.0.20
            $customers_data = $this->buildCustomerData($get_data['customers_data'], $customers_data);
            $c = (array) Tools::jsonDecode($customers_data, true);
            $total_customers = (int) count($c);
            $orders_data_old = (array) Tools::jsonDecode($get_data['orders_data'], true);
            $orders_data_old[] = $orders_data;
            $total_orders = count($orders_data_old);
            $orders_data = Tools::jsonEncode($orders_data_old);
            
            $query = 'UPDATE '._DB_PREFIX_.'ba_report_products SET total_quantity="'.(int)$total_qtity . '",'
                    .'AVG_unit_price="'.(double)$AVG_unit_price.'",'
                    .'current_stock="'.(int)$current_stock.'",'
                    .'total_discounts_tax_excl="'.(double)$total_discounts_tax_excl . '",'
                    .'discounts_tax_amount="'.(double)$discounts_tax_amount . '",'
                    .'total_products_no_tax="' . (double)$total_products_no_t . '",'
                    .'products_tax="'.(double)$product_tax.'",'
                    .'including_ecotax_tax_amount="'.(double)$including_ecotax_tax_amount . '",'
                    .'including_ecotax_tax_excl="'.(double)$including_ecotax_tax_excl . '",'
                    .'net_tax_product_reduction="' . (double)$net_tax_product_reduction . '",'
                    .'total_cost="'.(double)$total_cost.'",gross_profit="'.(double)$gross_prof.'",'
                    .'gross_margin="'.(double)$gross_margin . '",'
                    .'net_profit="'.(double)$net_prof.'",net_margin="'.(double)$net_margin.'",'
                    .'product_reference="'.pSQL($product_reference).'"'
                    .",customers_data='".pSQL($customers_data)."' "
                    .",orders_data='".pSQL($orders_data)."' "
                    .",total_customers='".pSQL($total_customers)."' "
                    .",total_orders='".pSQL($total_orders)."' "
                    .'WHERE products_id="'.(int)$product_id . '" '
                    .'AND id_combinations="'.(int)$id_combinations . '" AND id_shop='.(int)$order->id_shop;
            Db::getInstance()->query($query);
        }
        if ($data == null && $count_product!=0) {
            // since 1.0.20
            $customers_data = Tools::jsonEncode(array($customers_data));
            $orders_data = Tools::jsonEncode(array($orders_data));
            $total_customers = 1;
            $total_orders = 1;
            $query = array(
                'id_shop' => (int)$order->id_shop,
                'shop_name' => pSQL($shop_name),
                'id_cart' => (int)$id_cart,
                'id_order' => (int)$id_order,
                'order_add_date' => pSQL($order_add_date),
                'invoice_add_date' => pSQL($invoice_add_date),
                'delivery_date' => pSQL($delivery_date),
                'invoice_number' => (int)$invoice_number,
                'invoice_status' => (int)$invoice_status,
                'products_id' => (int)$product_id,
                'product_reference' => pSQL($product_reference),
                'product_name' => pSQL($product_name),
                'supplier_reference' => pSQL($supplier_reference),
                'EAN_reference' => $EAN_reference,
                'UPC_reference' => $UPC_reference,
                'current_stock' => (int)$current_stock,
                'total_quantity' => (int)$total_quantity,
                'AVG_unit_price' => (double) $AVG_unit_price,
                'tax_rate' => (double)$tax_rate,
                'total_discounts_tax_excl' => (double)$total_discounts_tax_excl,
                'discounts_tax_amount' => (double)$discounts_tax_amount,
                'total_products_no_tax' => (double)$total_products_no_tax,
                'products_tax' => (double)$product_tax,
                'including_ecotax_tax_amount' => (double)$including_ecotax_tax_amount,
                'including_ecotax_tax_excl' => (double)$including_ecotax_tax_excl,
                'net_tax_product_reduction' => (double)$net_tax_product_reduction,
                'total_cost' => (double)$total_cost,
                'gross_profit' => (double)$gross_profit,
                'gross_margin' => (double)$gross_margin,
                'net_profit' => (double)$net_profit,
                'net_margin' => (double)$net_margin,
                'manufacturer_name' => pSQl($manufacturer_name),
                'category_name' => pSQL($category_name),
                'id_combinations' => (int)$id_combinations,
                'customers_data' => $customers_data,
                'orders_data' => $orders_data,
                'total_customers' => $total_customers,
                'total_orders' => $total_orders,
                'supplier_name' => $supplier_name,
            );
            Db::getInstance()->insert('ba_report_products', $query);
        }
        return true;
    }
    public function gettotalproducnotax()
    {
        $query = 'SELECT total_products_no_tax FROM ' . _DB_PREFIX_ . 'ba_report_products';
        $data = Db::getInstance()->executeS($query, true, false);
        $total = 0;
        foreach ($data as $key) {
            $total = $total + (double) $key['total_products_no_tax'];
        }
        return $total;
    }
    public function gettotalgrossprofits()
    {
        $query = 'SELECT gross_profit FROM ' . _DB_PREFIX_ . 'ba_report_products';
        $data = Db::getInstance()->executeS($query, true, false);
        $total = 0;
        foreach ($data as $key) {
            $total = $total + (double) $key['gross_profit'];
        }
        return $total;
    }
    public function gettotalnetprofits()
    {
        $query = 'SELECT net_profit FROM ' . _DB_PREFIX_ . 'ba_report_products';
        $data = Db::getInstance()->executeS($query, true, false);
        $total = 0;
        foreach ($data as $key) {
            $total = $total + (double) $key['net_profit'];
        }
        return $total;
    }
    public function getreport($id_product, $id_combinations, $id_shop)
    {
        $query = 'SELECT * FROM ' . _DB_PREFIX_ . 'ba_report_products WHERE '
                .'products_id="' . (int)$id_product . '" AND id_combinations="' . (int)$id_combinations . '"';
        $query .= ' AND id_shop='.(int)$id_shop;
        $data = DB::getInstance()->executeS($query, true, false);
        return $data;
    }
    public function updateAllproducreport()
    {
        $query = 'SELECT total_products_no_tax,gross_profit,net_profit';
        $query .= ',products_id,product_reference,product_name,id_shop FROM ';
        $query .= _DB_PREFIX_ . 'ba_report_products';
        $data = DB::getInstance()->executeS($query, true, false);
        $n = count($data);
        for ($i = 0; $i < $n; $i++) {
            $get_data = $data[$i];
            $of_total_sales =((double)$get_data['total_products_no_tax']/(double) $this->gettotalproducnotax()) * 100;
            $of_total_gross_profits =((double) $get_data['gross_profit']/(double) $this->gettotalgrossprofits()) * 100;
            $of_total_net_profits = ((double) $get_data['net_profit']/(double) $this->gettotalnetprofits()) * 100;

            $query = 'UPDATE ' . _DB_PREFIX_ . 'ba_report_products '
                    .'SET of_total_sales="' . (double)$of_total_sales . '",'
                    . 'of_total_gross_profits="' . (double)$of_total_gross_profits . '",'
                    .'of_total_net_profits="' . (double)$of_total_net_profits . '"'
                    . ' WHERE products_id="' . (int)$get_data['products_id'] . '" AND '
                    . 'product_reference="' . pSQL($get_data['product_reference']) . '" '
                    .'AND product_name="' . pSQL($get_data['product_name']) . '"';
            $query .= ' AND id_shop='.(int)$get_data['id_shop'];
            Db::getInstance()->query($query);
        }
    }
    public function convertPercent($value)
    {
        $data_view = round($value, 2) . '%';
        return $data_view;
    }
    public function convertMoney($value)
    {
        $tool = new Tools();
        $a = round($value, 2);
        $data_view = $tool->displayPrice($a);
        return $data_view;
    }
    public function countData($helper)
    {
        $sql = 'SELECT count(*) FROM ' . _DB_PREFIX_ . 'ba_report_products '
                . $this->setWhereClause($helper);
        $data = DB::getInstance()->getValue($sql, false);
        return $data;
    }
    public function getCountProduct($id_product)
    {
        $sql='SELECT count(*) FROM '._DB_PREFIX_.'product WHERE id_product='.(int)$id_product;
        $count_data=Db::getInstance()->getValue($sql, false);
        return $count_data;
    }
    // since 1.0.20
    public function formatOrderData($value, $row)
    {
        $arr = Tools::jsonDecode($value, true);
        $c_controller = 'index.php?controller=AdminOrders&vieworder';
        $c_controller .='&token='.Tools::getAdminTokenLite('AdminOrders');
        $ajax_token = Tools::getAdminTokenLite('AdminReportSale');
        $this->context->smarty->assign('c_controller', $c_controller);
        $this->context->smarty->assign('orders', $arr);
        $this->context->smarty->assign('row', $row);
        $this->context->smarty->assign('ajax_token', $ajax_token);
        $tpl = $this->name."/views/templates/admin/order_data.tpl";
        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $tpl);
    }
    public function formatCustomerData($value, $row)
    {
        $arr = Tools::jsonDecode($value, true);
        $c_controller = 'index.php?controller=AdminCustomers&viewcustomer';
        $c_controller .='&token='.Tools::getAdminTokenLite('AdminCustomers');
        $ajax_token = Tools::getAdminTokenLite('AdminReportSale');
        $this->context->smarty->assign('c_controller', $c_controller);
        $this->context->smarty->assign('customer', $arr);
        $this->context->smarty->assign('row', $row);
        $this->context->smarty->assign('ajax_token', $ajax_token);
        $tpl = $this->name."/views/templates/admin/customer_data.tpl";
        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $tpl);
    }
    public function buildCustomerData($old_string, $customers_data)
    {
        if (empty($old_string)) {
            return Tools::jsonEncode($customers_data);
        }
        $data_old = (array) Tools::jsonDecode($old_string, true);
        foreach ($data_old as &$c) {
            if ($c['id'] == $customers_data['id']) {
                $c['total_order']++;
                return Tools::jsonEncode($data_old);
            }
        }
        $data_old[] = $customers_data;
        return Tools::jsonEncode($data_old);
    }
}
