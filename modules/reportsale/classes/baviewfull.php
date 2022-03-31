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

class BaViewfull extends ReportSale
{

    private $orderby;
    private $orderway;
    private $ps_searchable_fields = array('id_shop','shop_name', 'id_cart',
        'id_order', 'invoice_number', 'invoice_status',
        'delivery_number', 'payment_method', 'carrier', 'products_name',
        'total_paid_with_tax', 'total_really_paid_with_tax',
        'total_shipping_without_tax', 'shipping_tax_amount',
        'total_discounts_tax_excl', 'discounts_tax_amount',
        'total_wrapping_tax_excl', 'wrapping_tax_amount',
        'total_products_no_tax', 'products_tax',
        'including_ecotax_tax_excl', 'including_ecotax_tax_amount',
        'total_tax', 'total_cost', 'gross_profit_before_discounts',
        'net_profit_tax_excl', 'gross_margin_before_discounts',
        'net_margin_tax_excl', 'email', 'birthday', 'last_name',
        'first_name', 'customer_adding_date', 'customer_updating_date',
        'company', 'address_1', 'address_2', 'postcode',
        'city', 'country', 'id_country', 'phone', 'weight','reference','state');
    public function setWhereClauseDate($helper)
    {
        $sql = null;
        $orderDateArr_order_date = Tools::getValue($helper->list_id . "Filter_order_add_date", null);
        if ($orderDateArr_order_date !== null) {
            $d = $orderDateArr_order_date;
            $this->context->cookie->{$helper->list_id.'Filter_order_add_date'} = serialize($d);
        }
        if (!empty($orderDateArr_order_date[0])) {
            $sql.=" AND order_add_date >= '" . pSQL($orderDateArr_order_date[0]) . " 00:00:00' ";
        }
        if (!empty($orderDateArr_order_date[1])) {
            $sql.=" AND order_add_date <= '" . pSQL($orderDateArr_order_date[1]) . " 23:59:59' ";
        }
        return $sql;
    }
    public function setWhereClause($helper)
    {
        foreach ($this->ps_searchable_fields as $search_field) {
            $search_value = trim(Tools::getValue($helper->list_id . "Filter_" . $search_field, null));
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
        $whereClause.=$this->setWhereClauseDate($helper);
        return $whereClause;
    }
    public function resetList()
    {
        $helper_list_id = $this->name . 'ba_report_full';
        foreach ($this->ps_searchable_fields as $search_field) {
            $this->context->cookie->{$helper_list_id . 'Filter_' . $search_field} = null;
        }
        Configuration::updateValue($this->name.'_all_where', null);
    }
    public function viewfulllist()
    {
        $fields_list = array(
            'id_shop' => array(
                'title' => $this->l('ID shop'),
                'type' => 'text'
            ),
            'shop_name' => array(
                'title' => $this->l('Shop name'),
                'type' => 'text'
            ),
            'id_order' => array(
                'title' => $this->l('ID Order'),
                'type' => 'text'
            ),
            'reference' => array(
                'title' => $this->l('Reference'),
                'type' => 'text'
            ),
            'order_add_date' => array(
                'title' => $this->l('Order Add Date'),
                'type' => 'date'
            ),
            'payment_method' => array(
                'title' => $this->l('Payment Method'),
                'type' => 'text'
            ),
            'carrier' => array(
                'title' => $this->l('Carrier'),
                'type' => 'text'
            ),
            'weight' => array(
                'title' => $this->l('Weight'),
                'type' => 'text',
                'callback' => 'displayWeight',
                'callback_object' => $this
            ),
            'products_name' => array(
                'title' => $this->l('Products'),
                'type' => 'text',
                'width' => 300,
                'orderby' => false,
                'callback' => 'displayProducts',
                'callback_object' => $this
            ),
            'total_paid_with_tax' => array(
                'title' => $this->l('Total Paid With Tax'),
                'type' => 'text',
                'callback' => 'convertMoneyFull',
                'callback_object' => $this
            ),
            'total_really_paid_with_tax' => array(
                'title' => $this->l('Total Really Paid With Tax'),
                'type' => 'text',
                'callback' => 'convertMoneyFull',
                'callback_object' => $this
            ),
            'total_shipping_without_tax' => array(
                'title' => $this->l('Total Shipping Without Tax'),
                'type' => 'text',
                'callback' => 'convertMoneyFull',
                'callback_object' => $this
            ),
            'shipping_tax_amount' => array(
                'title' => $this->l('Shipping Tax Amount'),
                'type' => 'text',
                'callback' => 'convertMoneyFull',
                'callback_object' => $this
            ),
            'total_discounts_tax_excl' => array(
                'title' => $this->l('Total Discounts Tax Excl'),
                'type' => 'text',
                'callback' => 'convertMoneyFull',
                'callback_object' => $this
            ),
            'discounts_tax_amount' => array(
                'title' => $this->l('Discounts Tax Amount'),
                'type' => 'text',
                'callback' => 'convertMoneyFull',
                'callback_object' => $this
            ),
            'total_wrapping_tax_excl' => array(
                'title' => $this->l('Total Wrapping Tax Excl'),
                'type' => 'text',
                'callback' => 'convertMoneyFull',
                'callback_object' => $this
            ),
            'wrapping_tax_amount' => array(
                'title' => $this->l('Wrapping Tax Amount'),
                'type' => 'text',
                'callback' => 'convertMoneyFull',
                'callback_object' => $this
            ),
            'total_products_no_tax' => array(
                'title' => $this->l('Total Products No Tax'),
                'type' => 'text',
                'callback' => 'convertMoneyFull',
                'callback_object' => $this
            ),
            'products_tax' => array(
                'title' => $this->l('Products Tax'),
                'type' => 'text',
                'callback' => 'convertMoneyFull',
                'callback_object' => $this
            ),
            'including_ecotax_tax_excl' => array(
                'title' => $this->l('Including Ecotax Tax Excl'),
                'type' => 'text',
                'callback' => 'convertMoneyFull',
                'callback_object' => $this
            ),
            'including_ecotax_tax_amount' => array(
                'title' => $this->l('Including Ecotax Tax Amount'),
                'type' => 'text',
                'callback' => 'convertMoneyFull',
                'callback_object' => $this
            ),
            'total_tax_5' => array(
                'title' => $this->l('Total Tax 5%'),
                'type' => 'text',
                'callback' => 'convertMoneyFull',
                'callback_object' => $this
            ),
			'total_tax_9975' => array(
                'title' => $this->l('Total Tax 9.975%'),
                'type' => 'text',
                'callback' => 'convertMoneyFull',
                'callback_object' => $this
            ),
			'total_tax' => array(
                'title' => $this->l('Total Tax'),
                'type' => 'text',
                'callback' => 'convertMoneyFull',
                'callback_object' => $this
            ),
            'total_cost' => array(
                'title' => $this->l('Total Cost'),
                'type' => 'text',
                'callback' => 'convertMoneyFull',
                'callback_object' => $this
            ),
            'gross_profit_before_discounts' => array(
                'title' => $this->l('Gross Profit Before Discounts'),
                'type' => 'text',
                'callback' => 'convertMoneyFull',
                'callback_object' => $this
            ),
            'net_profit_tax_excl' => array(
                'title' => $this->l('Net Profit Tax Excl'),
                'type' => 'text',
                'callback' => 'convertMoneyFull',
                'callback_object' => $this
            ),
            'gross_margin_before_discounts' => array(
                'title' => $this->l('Gross Margin Before Discounts'),
                'type' => 'text',
                'callback' => 'convertPercentFull',
                'callback_object' => $this
            ),
            'net_margin_tax_excl' => array(
                'title' => $this->l('Net Margin Tax Excl'),
                'type' => 'text',
                'callback' => 'convertPercentFull',
                'callback_object' => $this
            ),
            'email' => array(
                'title' => $this->l('Email'),
                'type' => 'text'
            ),
            'birthday' => array(
                'title' => $this->l('Birthday'),
                'type' => 'text'
            ),
            'last_name' => array(
                'title' => $this->l('Last Name'),
                'type' => 'text'
            ),
            'first_name' => array(
                'title' => $this->l('First Name'),
                'type' => 'text'
            ),
            'customer_adding_date' => array(
                'title' => $this->l('Customer Adding Date'),
                'type' => 'text'
            ),
            'customer_updating_date' => array(
                'title' => $this->l('Customer Updating Date'),
                'type' => 'text'
            ),
            'company' => array(
                'title' => $this->l('Company'),
                'type' => 'text'
            ),
            'address_1' => array(
                'title' => $this->l('Address 1'),
                'type' => 'text'
            ),
            'address_2' => array(
                'title' => $this->l('address 2'),
                'type' => 'text'
            ),
            'postcode' => array(
                'title' => $this->l('Postcode'),
                'type' => 'text'
            ),
            'city' => array(
                'title' => $this->l('City'),
                'type' => 'text'
            ),
            'country' => array(
                'title' => $this->l('Country'),
                'type' => 'text'
            ),
            'id_country' => array(
                'title' => $this->l('ID Country'),
                'type' => 'text'
            ),
            'state' => array(
                'title' => $this->l('State'),
                'type' => 'text'
            ),
            'phone' => array(
                'title' => $this->l('Phone'),
                'type' => 'text'
            ),
            'iso_currency' => array(
                'title' => $this->l('Currency ISO'),
                'type' => 'text'
            ),
        );
        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->identifier = 'id_report';
        $helper->show_toolbar = true;
        $helper->simple_header = false;
        $helper->no_link = true;
        $helper->title = $this->l('Report All');
        $helper->table = $this->name . 'ba_report_full';
        $helper->list_id = $this->name . 'ba_report_full';
        $this->orderby = pSQL(Tools::getValue($helper->list_id . "Orderby", "id_order"));
        $this->orderway = pSQL(Tools::getValue($helper->list_id . "Orderway", "ASC"));
        $helper->orderBy = $this->orderby;
        $helper->orderWay = Tools::strtoupper($this->orderway);
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $c1 = AdminController::$currentIndex;
        $n1 = $this->name;
        $ad1 = Tools::getAdminTokenLite('AdminModules');
        $o1 = "reportsaleba_report_fullOrderby";
        $o2 = "reportsaleba_report_fullOrderway";
        $od1 = $this->orderby;
        $od2 = $this->orderway;
        $helper->toolbar_btn['export'] = array(
            'href' => $c1.'&configure='.$n1.'&token='.$ad1.'&task=all&'.$o1.'='.$od1.'&'.$o2.'='.$od2.'&csv=all',
            'desc' => $this->l('export csv')
        );
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name . '&task=all';
        $helper->currentIndex .= '&'.$helper->list_id . "Orderby=".$helper->orderBy;
        $helper->currentIndex .= '&'.$helper->list_id . "Orderway=".$helper->orderWay;
        $con = (int) $this->countData($helper);
        $helper->listTotal = $con;

        if ($this->context->cookie->{$helper->list_id . '_pagination'} < 20) {/* get value pagination */
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
        $rows = $this->selectdatafull($helper, $start, $selected_pagination);
        $table_helper = $helper->generateList($rows, $fields_list);
        $table_helper .= $this->getSummaryBlock($helper, $fields_list);
        return $table_helper;
    }
    public function getSummaryBlock($helper, $fields_list)
    {
        $sql = 'SELECT COUNT(DISTINCT id_shop) as id_shop';
        $sql .= ', COUNT(DISTINCT shop_name) as shop_name';
        $sql .= ', COUNT(DISTINCT id_order) as id_order';
        $sql .= ', COUNT(DISTINCT reference) as reference';
        $sql .= ", COUNT(DISTINCT order_number) - COUNT(DISTINCT case when order_number='' then 1 end)";
        $sql .= " as order_number";
        $sql .= ", COUNT(DISTINCT invoice_number) - COUNT(DISTINCT case when invoice_number='' then 1 end)";
        $sql .= " as invoice_number";
        $sql .= ", COUNT(DISTINCT invoice_status) - COUNT(DISTINCT case when invoice_status='' then 1 end)";
        $sql .= " as invoice_status";
        $sql .= ", COUNT(DISTINCT delivery_number) - COUNT(DISTINCT case when delivery_number='' then 1 end)";
        $sql .= " as delivery_number";
        $sql .= ", COUNT(DISTINCT payment_method) - COUNT(DISTINCT case when payment_method='' then 1 end)";
        $sql .= " as payment_method";
        $sql .= ", COUNT(DISTINCT carrier) - COUNT(DISTINCT case when carrier='' then 1 end) as carrier";
        $sql .= ", COUNT(DISTINCT email) - COUNT(DISTINCT case when email='' then 1 end) as email";
        $sql .= ", COUNT(DISTINCT birthday) - COUNT(DISTINCT case when birthday='' then 1 end) as birthday";
        $sql .= ", COUNT(DISTINCT address_1) - COUNT(DISTINCT case when address_1='' then 1 end) as address_1";
        $sql .= ", COUNT(DISTINCT address_2) - COUNT(DISTINCT case when address_2='' then 1 end) as address_2";
        $sql .= ", COUNT(DISTINCT postcode) - COUNT(DISTINCT case when postcode='' then 1 end) as postcode";
        $sql .= ", COUNT(DISTINCT city) - COUNT(DISTINCT case when city='' then 1 end) as city";
        $sql .= ", COUNT(DISTINCT country) - COUNT(DISTINCT case when country='' then 1 end) as country";
        $sql .= ', COUNT(DISTINCT id_country) as id_country';
        $sql .= ", COUNT(DISTINCT state) - COUNT(DISTINCT case when state='' then 1 end)";
        $sql .= " as state";
        $sql .= ", COUNT(DISTINCT phone) - COUNT(DISTINCT case when phone='' then 1 end) as phone";
        $sql .= ', COUNT(DISTINCT iso_currency) as iso_currency';
        $sql .= ', COUNT(DISTINCT id_currency) as id_currency';
        $sql .= ", COUNT(DISTINCT company) - COUNT(DISTINCT case when company='' then 1 end) as company";
        $sql .= ', MIN(order_add_date) as min_order_add_date';
        $sql .= ', MAX(order_add_date) as max_order_add_date';
        $sql .= ', MIN(invoice_add_date) as min_invoice_add_date';
        $sql .= ', MAX(invoice_add_date) as max_invoice_add_date';
        $sql .= ', MIN(delivery_date) as min_delivery_date';
        $sql .= ', MAX(delivery_date) as max_delivery_date';
        $sql .= ', MIN(customer_adding_date) as min_customer_adding_date';
        $sql .= ', MAX(customer_adding_date) as max_customer_adding_date';
        $sql .= ', MIN(customer_updating_date) as min_customer_updating_date';
        $sql .= ', MAX(customer_updating_date) as max_customer_updating_date';
        $sql .= ', SUM(weight) as weight';
        $sql .= ' FROM ' . _DB_PREFIX_ . 'ba_report_full ';
        $sql .= $this->setWhereClause($helper);
        $item1 = DB::getInstance()->getRow($sql, false);
        if (empty($item1)) {
            return false;
        }
        $sql = 'SELECT id_currency';
        $sql .= ', SUM(total_paid_with_tax) as total_paid_with_tax';
        $sql .= ', SUM(total_really_paid_with_tax) as total_really_paid_with_tax';
        $sql .= ', SUM(total_shipping_without_tax) as total_shipping_without_tax';
        $sql .= ', SUM(shipping_tax_amount) as shipping_tax_amount';
        $sql .= ', SUM(total_discounts_tax_excl) as total_discounts_tax_excl';
        $sql .= ', SUM(discounts_tax_amount) as discounts_tax_amount';
        $sql .= ', SUM(total_wrapping_tax_excl) as total_wrapping_tax_excl';
        $sql .= ', SUM(wrapping_tax_amount) as wrapping_tax_amount';
        $sql .= ', SUM(total_products_no_tax) as total_products_no_tax';
        $sql .= ', SUM(products_tax) as products_tax';
        $sql .= ', SUM(including_ecotax_tax_excl) as including_ecotax_tax_excl';
        $sql .= ', SUM(including_ecotax_tax_amount) as including_ecotax_tax_amount';
        $sql .= ', SUM(total_tax_5) as total_tax_5';
        $sql .= ', SUM(total_tax_9975) as total_tax_9975';
        $sql .= ', SUM(total_tax) as total_tax';
        $sql .= ', SUM(total_cost) as total_cost';
        $sql .= ', SUM(gross_profit_before_discounts) as gross_profit_before_discounts';
        $sql .= ', SUM(net_profit_tax_excl) as net_profit_tax_excl';
        $sql .= ' FROM ' . _DB_PREFIX_ . 'ba_report_full ';
        $sql .= $this->setWhereClause($helper);
        $sql .= ' GROUP BY id_currency ';
        $item2 = DB::getInstance()->executeS($sql, true, false);
        if (empty($item2)) {
            return false;
        }
        $data = array(
            'total_paid_with_tax' => 0,
            'total_really_paid_with_tax' => 0,
            'total_shipping_without_tax' => 0,
            'shipping_tax_amount' => 0,
            'total_discounts_tax_excl' => 0,
            'discounts_tax_amount' => 0,
            'total_wrapping_tax_excl' => 0,
            'wrapping_tax_amount' => 0,
            'total_products_no_tax' => 0,
            'products_tax' => 0,
            'including_ecotax_tax_excl' => 0,
            'including_ecotax_tax_amount' => 0,
            'total_tax_5' => 0,
            'total_tax_9975' => 0,
            'total_tax' => 0,
            'total_cost' => 0,
            'gross_profit_before_discounts' => 0,
            'net_profit_tax_excl' => 0,
            'gross_margin_before_discounts' => 0,
            'net_margin_tax_excl' => 0,
        );
        $default_currency = (int) Configuration::get('PS_CURRENCY_DEFAULT');
        $c_to = new Currency($default_currency);
        // Convert all amount to default currency before calculate
        foreach ($item2 as $value) {
            $id_currency = (int) $value['id_currency'];
            $c_from = new Currency($id_currency);
            $data['total_paid_with_tax'] += Tools::convertPriceFull($value['total_paid_with_tax'], $c_from, $c_to);
            $t = $value['total_really_paid_with_tax'];
            $data['total_really_paid_with_tax'] += Tools::convertPriceFull($t, $c_from, $c_to);
            $t = $value['total_shipping_without_tax'];
            $data['total_shipping_without_tax'] += Tools::convertPriceFull($t, $c_from, $c_to);
            $data['shipping_tax_amount'] += Tools::convertPriceFull($value['shipping_tax_amount'], $c_from, $c_to);
            $t = $value['total_discounts_tax_excl'];
            $data['total_discounts_tax_excl'] += Tools::convertPriceFull($t, $c_from, $c_to);
            $data['discounts_tax_amount'] += Tools::convertPriceFull($value['discounts_tax_amount'], $c_from, $c_to);
            $t = $value['total_wrapping_tax_excl'];
            $data['total_wrapping_tax_excl'] += Tools::convertPriceFull($t, $c_from, $c_to);
            $data['wrapping_tax_amount'] += Tools::convertPriceFull($value['wrapping_tax_amount'], $c_from, $c_to);
            $data['total_products_no_tax'] += Tools::convertPriceFull($value['total_products_no_tax'], $c_from, $c_to);
            $data['products_tax'] += Tools::convertPriceFull($value['products_tax'], $c_from, $c_to);
            $t = $value['including_ecotax_tax_excl'];
            $data['including_ecotax_tax_excl'] += Tools::convertPriceFull($t, $c_from, $c_to);
            $t = $value['including_ecotax_tax_amount'];
            $data['including_ecotax_tax_amount'] += Tools::convertPriceFull($t, $c_from, $c_to);
            $data['total_tax_5'] += Tools::convertPriceFull($value['total_tax_5'], $c_from, $c_to);
            $data['total_tax_9975'] += Tools::convertPriceFull($value['total_tax_9975'], $c_from, $c_to);
            $data['total_tax'] += Tools::convertPriceFull($value['total_tax'], $c_from, $c_to);
            $data['total_cost'] += Tools::convertPriceFull($value['total_cost'], $c_from, $c_to);
            $t = $value['gross_profit_before_discounts'];
            $data['gross_profit_before_discounts'] += Tools::convertPriceFull($t, $c_from, $c_to);
            $data['net_profit_tax_excl'] += Tools::convertPriceFull($value['net_profit_tax_excl'], $c_from, $c_to);
        }
        // calculate % gross margin
        if ($data['total_products_no_tax'] > 0) {
            $t = (float) $data['total_products_no_tax'];
            $data['gross_margin_before_discounts'] = (float) $data['gross_profit_before_discounts'] / $t;
            $data['net_margin_tax_excl'] = (float) $data['net_profit_tax_excl'] / $t;
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
        $summary['id_order'] = array(
            $this->l('#ID Order'),
            number_format($data['id_order'])
        );
        $summary['reference'] = array(
            $this->l('#Reference'),
            number_format($data['reference'])
        );
        $summary['order_add_date'] = array(
            $this->l('Order Add Date'),
            $this->l('From: ').Tools::displayDate($data['min_order_add_date']),
            $this->l('To: ').Tools::displayDate($data['max_order_add_date']),
        );
        $summary['payment_method'] = array(
            $this->l('#Payment Method'),
            number_format($data['payment_method'])
        );
        $summary['carrier'] = array(
            $this->l('#Carrier'),
            number_format($data['carrier'])
        );
        $summary['weight'] = array(
            $this->l('#Weight'),
            number_format($data['weight'], 4).' '. Configuration::get('PS_WEIGHT_UNIT')
        );
        $summary['products_name'] = array(
            $this->l('Products'),
            $this->l('-')
        );
        $summary['total_paid_with_tax'] = array(
            $this->l('Total Paid With Tax'),
            Tools::displayPrice($data['total_paid_with_tax'], $c_to)
        );
        $summary['total_really_paid_with_tax'] = array(
            $this->l('Total Really Paid With Tax'),
            Tools::displayPrice($data['total_really_paid_with_tax'], $c_to)
        );
        $summary['total_shipping_without_tax'] = array(
            $this->l('Total Shipping Without Tax'),
            Tools::displayPrice($data['total_shipping_without_tax'], $c_to)
        );
        $summary['shipping_tax_amount'] = array(
            $this->l('Shipping Tax Amount'),
            Tools::displayPrice($data['shipping_tax_amount'], $c_to)
        );
        $summary['total_discounts_tax_excl'] = array(
            $this->l('Total Discounts Tax Excl'),
            Tools::displayPrice($data['total_discounts_tax_excl'], $c_to)
        );
        $summary['discounts_tax_amount'] = array(
            $this->l('Discounts Tax Amount'),
            Tools::displayPrice($data['discounts_tax_amount'], $c_to)
        );
        $summary['total_wrapping_tax_excl'] = array(
            $this->l('Total Wrapping Tax Excl'),
            Tools::displayPrice($data['total_wrapping_tax_excl'], $c_to)
        );
        $summary['wrapping_tax_amount'] = array(
            $this->l('Wrapping Tax Amount'),
            Tools::displayPrice($data['wrapping_tax_amount'], $c_to)
        );
        $summary['total_products_no_tax'] = array(
            $this->l('Total Products No Tax'),
            Tools::displayPrice($data['total_products_no_tax'], $c_to)
        );
        $summary['products_tax'] = array(
            $this->l('Products Tax'),
            Tools::displayPrice($data['products_tax'], $c_to)
        );
        $summary['including_ecotax_tax_excl'] = array(
            $this->l('Including Ecotax Tax Excl'),
            Tools::displayPrice($data['including_ecotax_tax_excl'], $c_to)
        );
        $summary['including_ecotax_tax_amount'] = array(
            $this->l('Including Ecotax Tax Amount'),
            Tools::displayPrice($data['including_ecotax_tax_amount'], $c_to)
        );
        $summary['total_tax_5'] = array(
            $this->l('Total Tax 5%'),
            Tools::displayPrice($data['total_tax_5'], $c_to)
        );
		$summary['total_tax_9975'] = array(
            $this->l('Total Tax 9.975%'),
            Tools::displayPrice($data['total_tax_9975'], $c_to)
        );
		$summary['total_tax'] = array(
            $this->l('Total Tax'),
            Tools::displayPrice($data['total_tax'], $c_to)
        );
        $summary['total_cost'] = array(
            $this->l('Total Cost'),
            Tools::displayPrice($data['total_cost'], $c_to)
        );
        $summary['gross_profit_before_discounts'] = array(
            $this->l('Gross Profit Before Discounts'),
            Tools::displayPrice($data['gross_profit_before_discounts'], $c_to)
        );
        $summary['net_profit_tax_excl'] = array(
            $this->l('Net Profit Tax Excl'),
            Tools::displayPrice($data['net_profit_tax_excl'], $c_to)
        );
        $summary['gross_margin_before_discounts'] = array(
            $this->l('Gross Margin Before Discounts'),
            round($data['gross_margin_before_discounts'] * 100, 2).$this->l('%')
        );
        $summary['net_margin_tax_excl'] = array(
            $this->l('Net Margin Tax Excl'),
            round($data['net_margin_tax_excl'] * 100, 2).$this->l('%')
        );
        $summary['email'] = array(
            $this->l('#Email'),
            number_format($data['email'])
        );
        $summary['birthday'] = array(
            $this->l('#Email'),
            number_format($data['birthday'])
        );
        $summary['last_name'] = array(
            $this->l('Last Name'),
            $this->l('-')
        );
        $summary['first_name'] = array(
            $this->l('First Name'),
            $this->l('-')
        );
        $summary['customer_adding_date'] = array(
            $this->l('Customer Adding Date'),
            $this->l('From: ').Tools::displayDate($data['min_customer_adding_date']),
            $this->l('To: ').Tools::displayDate($data['max_customer_adding_date']),
        );
        $summary['customer_updating_date'] = array(
            $this->l('Customer Updating Date'),
            $this->l('From: ').Tools::displayDate($data['min_customer_updating_date']),
            $this->l('To: ').Tools::displayDate($data['max_customer_updating_date']),
        );
        $summary['company'] = array(
            $this->l('#Company'),
            number_format($data['company'])
        );
        $summary['address_1'] = array(
            $this->l('#Address 1'),
            number_format($data['address_1'])
        );
        $summary['address_2'] = array(
            $this->l('#Address 2'),
            number_format($data['address_2'])
        );
        $summary['postcode'] = array(
            $this->l('#Postcode'),
            number_format($data['postcode'])
        );
        $summary['city'] = array(
            $this->l('#City'),
            number_format($data['city'])
        );
        $summary['country'] = array(
            $this->l('#Country'),
            number_format($data['country'])
        );
        $summary['id_country'] = array(
            $this->l('#ID Country'),
            number_format($data['id_country'])
        );
        $summary['state'] = array(
            $this->l('#State'),
            number_format($data['state'])
        );
        $summary['phone'] = array(
            $this->l('#Phone'),
            number_format($data['phone'])
        );
        $summary['iso_currency'] = array(
            $this->l('#Currency ISO'),
            number_format($data['iso_currency'])
        );
        $this->smarty->assign('summary', $summary);
        $this->smarty->assign('fields_list', $fields_list);
        return $this->shortDisplay('views/templates/admin/summary_table.tpl');
    }
    public function selectdatafull($helper, $start, $selected_pagination)
    {
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'ba_report_full ';
        $where = $this->setWhereClause($helper);
        $sql .= $where;
        Configuration::updateValue($this->name.'_all_where', $where);
        $sql .=' ORDER BY ' . pSQL($this->orderby) . ' ' . pSQL($this->orderway)
        . ' LIMIT ' . (int) $start . ', ' . (int) $selected_pagination;
        $rows = Db::getInstance()->executeS($sql, true, false);
        return($rows);
    }
    public function insertreportfull($id_order)
    {
        $order = new Order($id_order);
        $a = $order->getproducts();

        $default_currency = (int) Configuration::get('PS_CURRENCY_DEFAULT');
        $c_from = new Currency($default_currency);
        $id_currency = (int) $order->id_currency;
        $c_to = new Currency($id_currency);

        foreach ($a as &$value) {
            if (!isset($value['original_wholesale_price'])) {
                $p_id = $value['product_id'];
                $a_id = $value['product_attribute_id'];
                $value['original_wholesale_price'] = $this->getWholeSalePrice($p_id, $a_id);
            }
            $o = $value['original_wholesale_price'];
            $value['original_wholesale_price'] = Tools::convertPriceFull($o, $c_from, $c_to);
            $o = $value['wholesale_price'];
            $value['wholesale_price'] = Tools::convertPriceFull($o, $c_from, $c_to);

            $this->insertfull($value, $order);
        }
        // tính lại giảm giá cho Order
        $order_total_discount = $this->calcDiscountOrder($order);
        $products_name = $this->getProductsNameOfOrder($order);
        $weight = $order->getTotalWeight();
        $reference = $order->reference;
        // tính lại total tax
        $query = 'UPDATE ' . _DB_PREFIX_ . 'ba_report_full SET '
                ."total_discounts_tax_excl=total_discounts_tax_excl+"
                .(double) $order_total_discount["total_discounts_tax_excl"]
                .", discounts_tax_amount=discounts_tax_amount+".(double) $order_total_discount["discounts_tax_amount"]
                .", net_profit_tax_excl=gross_profit_before_discounts-total_discounts_tax_excl"
                .", total_tax=total_tax+shipping_tax_amount+wrapping_tax_amount-"
                .(double) $order_total_discount["discounts_tax_amount"]
                .', products_name = "'.pSQL($products_name).'"'
                .', reference = "'.pSQL($reference).'"'
                .', weight = '.$weight
                . ' WHERE id_order=' . (int)$id_order;
        
        Db::getInstance()->query($query);
        $this->updateAllFullReport($id_order);
		$this->updateTaxesBreakdown('ba_report_full', 'id_order', $order);
        return true;
    }
    public function insertfull($product, $order)
    {
        /************** */
        $address = new Address($order->id_address_invoice);
        $customer = new Customer($address->id_customer);

        /*******Price and Tax and Quantity******** */
        $unit_price = $product['unit_price_tax_excl'];
        $tax_rate = $product['tax_rate'];
        $total_quantity = $product['product_quantity'];
        
        /*******End Price and Tax and Quantity*********/
        $shop_name = $this->getShopName((int)$order->id_shop);
        $id_cart = $order->id_cart;
        $id_order = $order->id;
        $id_currency = $order->id_currency;
        $order_add_date = $order->date_add;
        $delivery_date = $order->delivery_date;
        $invoice_number = $order->invoice_number;
        $invoice_status = '';
        $delivery_number = '';
        $payment_method = $order->payment;

        $carrier = '';
        $carrier_obj = new Carrier($order->id_carrier);
        $carrier = $carrier_obj->name;
        $total_paid_with_tax = $order->total_paid;
        $total_really_paid_with_tax = $order->total_paid_real;
        $total_shipping_tax_incl = $order->total_shipping_tax_incl;
        $total_shipping_without_tax = $order->total_shipping_tax_excl;
        $shipping_tax_amount = 0;
        if ($total_shipping_tax_incl != 0) {
            $shipping_tax_amount = $total_shipping_tax_incl - $total_shipping_without_tax;
        }
        $total_discounts_tax_excl = '';
        $discounts_tax_amount = '';
        /******** discount total ****/
        $product_discount = $this->calcDiscount($product);
        $total_discounts_tax_excl = $product_discount['total_discounts_tax_excl'];
        $discounts_tax_amount = $product_discount['discounts_tax_amount'];

        $total_wrapping_tax_incl = $order->total_wrapping_tax_incl;
        $total_wrapping_tax_excl = $order->total_wrapping_tax_excl;
        $wrapping_tax_amount = 0;
        if ($total_wrapping_tax_incl != 0) {
            $wrapping_tax_amount = $total_wrapping_tax_incl - $total_wrapping_tax_excl;
        }
        $total_products_no_tax = (double) $unit_price * (int) $total_quantity;
        $products_tax = ((double) ($tax_rate / 100) * ($unit_price)) * (int) $total_quantity;
        /** eco TAX **/
        $ecotax_incl = $product['ecotax'];
        $ecotax_tax_rate = $product['ecotax_tax_rate'];
        $ecotax_tax_excl = ($ecotax_incl/ (1+$ecotax_tax_rate/100));
        $including_ecotax_tax_amount = ($ecotax_incl -  $ecotax_tax_excl) * $total_quantity;
        $including_ecotax_tax_excl = $ecotax_tax_excl * $total_quantity;
        $total_tax = $products_tax;
        $total_cost = ($product['original_wholesale_price']) * (int) $total_quantity;
        $gross_profit_before_discounts = $total_products_no_tax - $total_cost; /**/
        $net_profit_tax_excl = $gross_profit_before_discounts - $total_discounts_tax_excl; /**/
        $gross_margin_before_discounts = '';
        $net_margin_tax_excl = '';

        $email = $customer->email;
        $birthday = $customer->birthday;
        $last_name = $customer->lastname;
        $first_name = $customer->firstname;
        $customer_adding_date = $customer->date_add;
        $customer_updating_date = $customer->date_upd;
        $company = $address->company;
        $address_1 = $address->address1;
        $address_2 = $address->address2;
        $postcode = $address->postcode;
        $city = $address->city;
        $country = $address->country;
        $id_country = $address->id_country;
        // since 1.0.27+
        $state_name = "";
        $id_state = (int) $address->id_state;
        if (!empty($id_state)) {
            $state_name = State::getNameById($id_state);
        }

        $phone = $address->phone;
        $currency = new Currency($id_currency);
        $sign_currency = $currency->sign;
        $iso_currency = $currency->iso_code;

        $data = $this->getreportFull($id_order);
        if ($data != null) {
            $get_data = $data[0];
            $shipping_tax_amount = $shipping_tax_amount + $get_data['shipping_tax_amount'];
            $total_discounts_tax_excl=$total_discounts_tax_excl+$get_data['total_discounts_tax_excl'];
            $discounts_tax_amount=$discounts_tax_amount+$get_data['discounts_tax_amount'];
            $wrapping_tax_amount = $wrapping_tax_amount + $get_data['wrapping_tax_amount'];
            $total_products_no_tax = $total_products_no_tax + $get_data['total_products_no_tax'];
            $products_tax = $products_tax + $get_data['products_tax'];
            $including_ecotax_tax_amount = $including_ecotax_tax_amount + $get_data['including_ecotax_tax_amount'];
            $including_ecotax_tax_excl = $including_ecotax_tax_excl + $get_data['including_ecotax_tax_excl'];
            $total_tax = $total_tax + $get_data['total_tax'];
            $total_cost = $total_cost + $get_data['total_cost'];
            $gross_profit_before_discounts = $gross_profit_before_discounts+$get_data['gross_profit_before_discounts'];
            $net_profit_tax_excl = $net_profit_tax_excl + $get_data['net_profit_tax_excl'];

            $query = 'UPDATE ' . _DB_PREFIX_ . 'ba_report_full SET '
                    .'total_paid_with_tax="' . (double)$total_paid_with_tax . '",'
                    . 'total_discounts_tax_excl="' . (double)$total_discounts_tax_excl . '",'
                    . 'discounts_tax_amount="' . (double)$discounts_tax_amount . '",'
                    . 'total_products_no_tax="' . (double)$total_products_no_tax . '",'
                    . 'products_tax="'.(double)$products_tax.'",'
                    .'including_ecotax_tax_amount="'.(double)$including_ecotax_tax_amount.'",'
                    . 'including_ecotax_tax_excl="' . (double)$including_ecotax_tax_excl . '",'
                    . 'total_tax="' . (double)$total_tax . '",total_cost="' . (double)$total_cost . '",'
                    . 'gross_profit_before_discounts="' . (double)$gross_profit_before_discounts . '",'
                    . 'net_profit_tax_excl="' . (double)$net_profit_tax_excl . '",'
                    . 'gross_margin_before_discounts="' . (double)$gross_margin_before_discounts . '",'
                    . 'net_margin_tax_excl="' . (double)$net_margin_tax_excl . '",'
                    . 'sign_currency="' . $sign_currency . '",'
                    . 'iso_currency="' . $iso_currency . '",'
                    . 'id_currency="' . $id_currency . '"'
                    .'WHERE id_order="' . (int)$id_order . '"';
            Db::getInstance()->query($query);
        }
        if ($data == null) {
            Db::getInstance()->insert('ba_report_full', array(
                'id_shop' => (int)$order->id_shop,
                'shop_name' => pSQL($shop_name),
                'id_cart' => (int)$id_cart,
                'id_order' => (int)$id_order,
                'order_add_date' => pSQL($order_add_date),
                'invoice_add_date' => '', /**/
                'delivery_date' => pSQL($delivery_date),
                'invoice_number' => (int)$invoice_number,
                'invoice_status' => (int)$invoice_status,
                'delivery_number' => (int)$delivery_number,
                'payment_method' => pSQL($payment_method),
                'carrier' => pSQL($carrier),
                'total_paid_with_tax' => (double)$total_paid_with_tax,
                'total_really_paid_with_tax' => (double)$total_really_paid_with_tax,
                'total_shipping_without_tax' => (double)$total_shipping_without_tax,
                'shipping_tax_amount' => (double)$shipping_tax_amount,
                'total_discounts_tax_excl' => (double)$total_discounts_tax_excl,
                'discounts_tax_amount' => (double)$discounts_tax_amount,
                'total_wrapping_tax_excl' => (double)$total_wrapping_tax_excl,
                'wrapping_tax_amount' => (double)$wrapping_tax_amount,
                'total_products_no_tax' => (double)$total_products_no_tax,
                'products_tax' => (double)$products_tax,
                'including_ecotax_tax_excl' => (double)$including_ecotax_tax_excl,
                'including_ecotax_tax_amount' => (double)$including_ecotax_tax_amount,
                'total_tax' => (double)$total_tax,
                'total_cost' => (double)$total_cost,
                'gross_profit_before_discounts' => (double)$gross_profit_before_discounts,
                'net_profit_tax_excl' => (double)$net_profit_tax_excl,
                'gross_margin_before_discounts' => (double)$gross_margin_before_discounts,
                'net_margin_tax_excl' => (double)$net_margin_tax_excl,
                'email' => pSQL($email),
                'birthday' => pSQL($birthday),
                'last_name' => psql($last_name),
                'first_name' => pSQL($first_name),
                'customer_adding_date' => pSQL($customer_adding_date),
                'customer_updating_date' => pSQL($customer_updating_date),
                'company' => pSQL($company),
                'address_1' => pSQL($address_1),
                'address_2' => pSQL($address_2),
                'postcode' => pSQL($postcode),
                'city' => pSQL($city),
                'country' => pSQL($country),
                'id_country' => (int)$id_country,
                'phone' => pSQL($phone),
                'sign_currency' => $sign_currency,
                'iso_currency' => $iso_currency,
                'id_currency' => $id_currency,
                'id_state' => $id_state,
                'state' => $state_name,
            ));
        }
        return true;
    }
    public function getreportFull($order_id)
    {
        $query = 'SELECT * FROM ' . _DB_PREFIX_ . 'ba_report_full WHERE id_order="' . (int)$order_id . '"';
        $data = DB::getInstance()->executeS($query);
        return $data;
    }
    public function updateAllFullReport($order_id)
    {
        $query = 'SELECT * FROM ' . _DB_PREFIX_ . 'ba_report_full WHERE id_order=' . (int)$order_id;
        $data = DB::getInstance()->executeS($query, true, false);
        if (empty($data)) {
            return false;
        }
        $get_data = $data[0];
        $total_no_tx =(double) $get_data['total_products_no_tax'];
        if ($total_no_tx > 0) {
            $gross_mar_bef_dis =((double)$get_data['gross_profit_before_discounts']/$total_no_tx)*100;
            $net_margin_tax_excl =((double)$get_data['net_profit_tax_excl']/$total_no_tx)*100;
        } else {
            $gross_mar_bef_dis = 100;
            $net_margin_tax_excl = 100;
        }
        $query = 'UPDATE '._DB_PREFIX_.'ba_report_full SET '
                .'gross_margin_before_discounts="'.(double)$gross_mar_bef_dis.'", '
                . 'net_margin_tax_excl="' . (double)$net_margin_tax_excl.'" '
                . ' WHERE id_order=' . (int)$order_id;
        Db::getInstance()->query($query);
        return true;
    }
    public function convertPercentFull($value)
    {
        $data_view = round($value, 2) . '%';
        return $data_view;
    }
    public function convertMoneyFull($value, $row)
    {
        $a = round($value, 2);
        $id_currency = (int) $row['id_currency'];
        return Tools::displayPrice($a, $id_currency);
    }
    public function countData($helper)
    {
        $sql = 'SELECT count(*) FROM ' . _DB_PREFIX_ . 'ba_report_full '
                . $this->setWhereClause($helper);
        $data = DB::getInstance()->getValue($sql, false);
        return $data;
    }
}
