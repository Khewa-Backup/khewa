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

class BaViewCustomer extends ReportSale
{

    private $orderby;
    private $orderway;
    private $ps_searchable_fields = array('id_shop','shop_name', 'id_cart',
        'id_order', 'order_add_date', 'invoice_add_date',
        'delivery_date', 'order_number', 'invoice_number',
        'invoice_status', 'customer_id', 'last_name',
        'first_name', 'email', 'company', 'address_1',
        'address_2', 'postcode', 'city', 'country',
        'id_country', 'phone',
        'of_order', 'of_products_ordered', 'average_cart_all_included',
        'products_ordered', 'total_products_no_tax',
        'total_cost', 'total_discounts_tax_excl',
        'gross_profit', 'net_profit', 'gross_margin',
        'net_margin', '');
    public function setWhereClauseDate($helper)
    {
        $sql = null;
        $orderDateArr_order_date = Tools::getValue($helper->list_id . "Filter_order_add_date", null);
        $orderDateArr_invoice_date = Tools::getValue($helper->list_id . "Filter_invoice_add_date", null);
        $orderDateArr_delivery_date = Tools::getValue($helper->list_id . "Filter_delivery_date", null);
        $orderDateArr_first_order = Tools::getValue($helper->list_id . "Filter_first_order", null);
        $orderDateArr_last_order = Tools::getValue($helper->list_id . "Filter_last_order", null);
        if (!empty($orderDateArr_order_date[0])) {
            $sql.=" AND order_add_date >= '" . pSQL($orderDateArr_order_date[0]) . " 00:00:00' ";
        }
        if (!empty($orderDateArr_order_date[1])) {
            $sql.=" AND order_add_date <= '" . pSQL($orderDateArr_order_date[1]) . " 23:59:59' ";
        }
        if (!empty($orderDateArr_invoice_date[0])) {
            $sql.=" AND invoice_add_date >= '" . pSQL($orderDateArr_invoice_date[0]) . " 00:00:00' ";
        }
        if (!empty($orderDateArr_invoice_date[1])) {
            $sql.=" AND invoice_add_date <= '" . pSQL($orderDateArr_invoice_date[1]) . " 23:59:59' ";
        }
        if (!empty($orderDateArr_delivery_date[0])) {
            $sql.=" AND delivery_date >= '" . pSQL($orderDateArr_delivery_date[0]) . " 00:00:00' ";
        }
        if (!empty($orderDateArr_delivery_date[1])) {
            $sql.=" AND delivery_date <= '" . pSQL($orderDateArr_delivery_date[1]) . " 23:59:59' ";
        }
        if (!empty($orderDateArr_first_order[0])) {
            $sql.=" AND first_order >= '" . pSQL($orderDateArr_first_order[0]) . " 00:00:00' ";
        }
        if (!empty($orderDateArr_first_order[1])) {
            $sql.=" AND first_order <= '" . pSQL($orderDateArr_first_order[1]) . " 23:59:59' ";
        }
        if (!empty($orderDateArr_last_order[0])) {
            $sql.=" AND last_order >= '" . pSQL($orderDateArr_last_order[0]) . " 00:00:00' ";
        }
        if (!empty($orderDateArr_last_order[1])) {
            $sql.=" AND last_order <= '" . pSQL($orderDateArr_last_order[1]) . " 23:59:59' ";
        }
        return $sql;
    }
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
        $whereClause.=$this->setWhereClauseDate($helper);
        return $whereClause;
    }
    public function resetList()
    {
        $helper_list_id = $this->name . 'ba_report_customer';
        foreach ($this->ps_searchable_fields as $search_field) {
            $this->context->cookie->{$helper_list_id . 'Filter_' . $search_field} = null;
        }
        Configuration::updateValue($this->name.'_customer_where', null);
    }
    public function viewcustomerlist()
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
            'customer_id' => array(
                'title' => $this->l('Customer ID'),
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
            'email' => array(
                'title' => $this->l('Email'),
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
                'title' => $this->l('Address 2'),
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
            'phone' => array(
                'title' => $this->l('Phone'),
                'type' => 'text'
            ),
            'first_order' => array(
                'title' => $this->l('First Order'),
                'type' => 'date'
            ),
            'last_order' => array(
                'title' => $this->l('Last Order'),
                'type' => 'date'
            ),
            'of_order' => array(
                'title' => $this->l('Of Order'),
                'type' => 'text'
            ),
            'of_products_ordered' => array(
                'title' => $this->l('Of Products Ordered'),
                'type' => 'text'
            ),
            'average_cart_all_included' => array(
                'title' => $this->l('Average Cart All Included'),
                'type' => 'text',
                'callback' => 'convertMoneyCustomer',
                'callback_object' => $this
            ),
            'products_ordered' => array(
                'title' => $this->l('Products Ordered'),
                'type' => 'text'
            ),
            'total_products_no_tax' => array(
                'title' => $this->l('Total Products No Tax'),
                'type' => 'text',
                'callback' => 'convertMoneyCustomer',
                'callback_object' => $this
            ),
            'total_cost' => array(
                'title' => $this->l('Total Cost'),
                'type' => 'text',
                'callback' => 'convertMoneyCustomer',
                'callback_object' => $this
            ),
            'total_discounts_tax_excl' => array(
                'title' => $this->l('Total Discounts Tax Excl'),
                'type' => 'text',
                'callback' => 'convertMoneyCustomer',
                'callback_object' => $this
            ),
            'gross_profit' => array(
                'title' => $this->l('Gross Profit'),
                'type' => 'text',
                'callback' => 'convertMoneyCustomer',
                'callback_object' => $this
            ),
            'net_profit' => array(
                'title' => $this->l('Net Profit'),
                'type' => 'text',
                'callback' => 'convertMoneyCustomer',
                'callback_object' => $this
            ),
            'gross_margin' => array(
                'title' => $this->l('Gross Margin'),
                'type' => 'text',
                'callback' => 'convertPercentCustomer',
                'callback_object' => $this
            ),
            'net_margin' => array(
                'title' => $this->l('Net Margin'),
                'type' => 'text',
                'callback' => 'convertPercentCustomer',
                'callback_object' => $this
            )
        );
        $helper->shopLinkType = '';
        $helper->identifier = 'id_report';
        $helper->show_toolbar = true;
        $helper->no_link = true;
        $helper->simple_header = false;
        $helper->title = $this->l('Report Client');
        $helper->table = $this->name . 'ba_report_customer';
        $helper->list_id = $this->name . 'ba_report_customer';
        $this->orderby = pSQL(Tools::getValue($helper->list_id . "Orderby", "customer_id"));
        $this->orderway = pSQL(Tools::getValue($helper->list_id . "Orderway", "ASC"));
        $helper->orderBy = $this->orderby;
        $helper->orderWay = Tools::strtoupper($this->orderway);
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $c1 = AdminController::$currentIndex;
        $n1 = $this->name;
        $ad1 = Tools::getAdminTokenLite('AdminModules');
        $o1 = "reportsaleba_report_customerOrderby";
        $o2 = "reportsaleba_report_customerOrderway";
        $od1 = $this->orderby;
        $od2 = $this->orderway;
        $helper->toolbar_btn['export'] = array(
            'href' => $c1.'&configure='.$n1.'&token='.$ad1.'&task=client&'.$o1.'='.$od1.'&'.$o2.'='.$od2.'&csv=client',
            'desc' => $this->l('export csv')
        );
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name . '&task=client';
        $helper->currentIndex .= '&'.$helper->list_id . "Orderby=".$helper->orderBy;
        $helper->currentIndex .= '&'.$helper->list_id . "Orderway=".$helper->orderWay;
        $con = (int) $this->countData($helper);
        $helper->listTotal = $con;
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
        $rows = $this->selectdatacustomer($helper, $start, $selected_pagination);
        $table_helper = $helper->generateList($rows, $fields_list);
        $table_helper .= $this->getSummaryBlock($helper, $fields_list);
        return $table_helper;
    }
    public function getSummaryBlock($helper, $fields_list)
    {
        $sql = 'SELECT COUNT(DISTINCT id_shop) as id_shop';
        $sql .= ', COUNT(DISTINCT shop_name) as shop_name';
        $sql .= ", COUNT(DISTINCT customer_id) - COUNT(DISTINCT case when customer_id=0 then 1 end) as customer_id";
        $sql .= ", COUNT(DISTINCT email) - COUNT(DISTINCT case when email='' then 1 end) as email";
        $sql .= ", COUNT(DISTINCT company) - COUNT(DISTINCT case when company='' then 1 end) as company";
        $sql .= ", COUNT(DISTINCT address_1) - COUNT(DISTINCT case when address_1='' then 1 end) as address_1";
        $sql .= ", COUNT(DISTINCT address_2) - COUNT(DISTINCT case when address_2='' then 1 end) as address_2";
        $sql .= ", COUNT(DISTINCT postcode) - COUNT(DISTINCT case when postcode='' then 1 end) as postcode";
        $sql .= ", COUNT(DISTINCT city) - COUNT(DISTINCT case when city='' then 1 end) as city";
        $sql .= ", COUNT(DISTINCT country) - COUNT(DISTINCT case when country='' then 1 end) as country";
        $sql .= ", COUNT(DISTINCT id_country) - COUNT(DISTINCT case when id_country='' then 1 end) as id_country";
        $sql .= ", COUNT(DISTINCT phone) - COUNT(DISTINCT case when phone='' then 1 end) as phone";
        $sql .= ", MIN(first_order) as first_order";
        $sql .= ", MAX(last_order) as last_order";
        $sql .= ' FROM ' . _DB_PREFIX_ . 'ba_report_customer ';
        $sql .= $this->setWhereClause($helper);
        $item1 = DB::getInstance()->getRow($sql, false);
        if (empty($item1)) {
            return false;
        }
        $sql = 'SELECT customer_id';
        $sql .= ', SUM(of_order) as of_order';
        $sql .= ', SUM(of_products_ordered) as of_products_ordered';
        $sql .= ', SUM(products_ordered) as products_ordered';
        $sql .= ', SUM(total_products_no_tax) as total_products_no_tax';
        $sql .= ', SUM(total_cost) as total_cost';
        $sql .= ', SUM(total_discounts_tax_excl) as total_discounts_tax_excl';
        $sql .= ', SUM(gross_profit) as gross_profit';
        $sql .= ', SUM(net_profit) as net_profit';
        $sql .= ', SUM(total_paid_with_tax) as total_paid_with_tax';
        $sql .= ' FROM ' . _DB_PREFIX_ . 'ba_report_customer ';
        $sql .= $this->setWhereClause($helper);
        $item2 = DB::getInstance()->getRow($sql, false);
        if (empty($item2)) {
            return false;
        }
        $data = $item2;
        $data['gross_margin'] = 0;
        $data['net_margin'] = 0;
        $data['average_cart_all_included'] = 0;
        $default_currency = (int) Configuration::get('PS_CURRENCY_DEFAULT');
        $c_to = new Currency($default_currency);
        // calculate % gross margin
        if ($data['total_products_no_tax'] > 0) {
            $t = (float) $data['total_products_no_tax'];
            $data['gross_margin'] = (float) $data['gross_profit'] / $t;
            $data['net_margin'] = (float) $data['net_profit'] / $t;
        }
        if ($data['of_order'] > 0) {
            $t = (float) $data['total_paid_with_tax'];
            $data['average_cart_all_included'] = (float) $data['total_paid_with_tax'] / $data['of_order'];
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
        $summary['customer_id'] = array(
            $this->l('#Customer ID'),
            number_format($data['customer_id'])
        );
        $summary['last_name'] = array(
            $this->l('last_name'),
            $this->l('-'),
        );
        $summary['first_name'] = array(
            $this->l('First Name'),
            $this->l('-'),
        );
        $summary['email'] = array(
            $this->l('#Email'),
            number_format($data['email'])
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
        $summary['phone'] = array(
            $this->l('#Phone'),
            number_format($data['phone'])
        );
        $summary['first_order'] = array(
            $this->l('First Order'),
            Tools::displayDate($data['first_order'])
        );
        $summary['last_order'] = array(
            $this->l('Last Order'),
            Tools::displayDate($data['last_order'])
        );
        $summary['of_order'] = array(
            $this->l('Of Order'),
            number_format($data['of_order'])
        );
        $summary['of_products_ordered'] = array(
            $this->l('Of Products Ordered'),
            number_format($data['of_products_ordered'])
        );
        $summary['average_cart_all_included'] = array(
            $this->l('Average Cart All Included'),
            Tools::displayPrice($data['average_cart_all_included'], $c_to)
        );
        $summary['products_ordered'] = array(
            $this->l('Products Ordered'),
            number_format($data['products_ordered'])
        );
        $summary['total_products_no_tax'] = array(
            $this->l('Total Products No Tax'),
            Tools::displayPrice($data['total_products_no_tax'], $c_to)
        );
        $summary['total_cost'] = array(
            $this->l('Total Cost'),
            Tools::displayPrice($data['total_cost'], $c_to)
        );
        $summary['total_discounts_tax_excl'] = array(
            $this->l('Total Discounts Tax Excl'),
            Tools::displayPrice($data['total_discounts_tax_excl'], $c_to)
        );
        $summary['gross_profit'] = array(
            $this->l('Gross Profit'),
            Tools::displayPrice($data['gross_profit'], $c_to)
        );
        $summary['net_profit'] = array(
            $this->l('Net Profit'),
            Tools::displayPrice($data['net_profit'], $c_to)
        );
        $summary['gross_margin'] = array(
            $this->l('Gross Margin'),
            round($data['gross_margin'] * 100, 2).$this->l('%')
        );
        $summary['net_margin'] = array(
            $this->l('Net Margin'),
            round($data['net_margin'] * 100, 2).$this->l('%')
        );
        $this->smarty->assign('summary', $summary);
        $this->smarty->assign('fields_list', $fields_list);
        return $this->shortDisplay('views/templates/admin/summary_table.tpl');
    }
    public function selectdatacustomer($helper, $start, $selected_pagination)
    {
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'ba_report_customer ';
        $where = $this->setWhereClause($helper);
        $sql .= $where;
        Configuration::updateValue($this->name.'_customer_where', $where);
        $sql.=' ORDER BY ' . pSQL($this->orderby) . ' ' . pSQL($this->orderway)
        . ' LIMIT ' . (int) $start . ', ' . (int) $selected_pagination;
        $rows = Db::getInstance()->executeS($sql, true, false);
        return($rows);
    }
    public function insertreportcustomer($id_order)
    {
        $order = new Order($id_order);
        
        $id_currency = (int) $order->id_currency;
        $c_from = new Currency($id_currency);
        $default_currency = (int) Configuration::get('PS_CURRENCY_DEFAULT');
        $c_to = new Currency($default_currency);
        $order->total_paid = Tools::convertPriceFull($order->total_paid, $c_from, $c_to);
        $products = $order->getproducts();
        foreach ($products as &$product) {
            if (!isset($product['original_wholesale_price'])) {
                $p_id = $product['product_id'];
                $a_id = $product['product_attribute_id'];
                $product['original_wholesale_price'] = $this->getWholeSalePrice($p_id, $a_id);
            }
            $p = $this->convertProductToDefaultCurrenct($product, $c_from);
            $this->insertcustomer($p, $order);
        }
        // tính lại giảm giá cho Order
        $order_total_discount = $this->calcDiscountOrder($order);
        $address = new Address($order->id_address_invoice);
        $customer_id = $address->id_customer;
        // tính lại total tax
        $query = 'UPDATE ' . _DB_PREFIX_ . 'ba_report_customer SET '
                ."total_discounts_tax_excl=total_discounts_tax_excl+"
                .(double) $order_total_discount["total_discounts_tax_excl"]
                .", net_profit=gross_profit-total_discounts_tax_excl"
                . ' WHERE customer_id=' . (int)$customer_id;
        
        Db::getInstance()->query($query);
        $this->updateAllCustomerReport();
        return true;
    }
    public function insertcustomer($product, $order)
    {
        $address = new Address($order->id_address_invoice);
        $customer = new Customer($address->id_customer);
        /** Price product***/
        $unit_price = $product['unit_price_tax_excl'];
        $total_quantity = $product['product_quantity'];
        /** end price product***/
        $shop_name = $this->getShopName((int)$order->id_shop);
        $id_cart = $order->id_cart;
        $id_order = $order->id;
        $order_add_date = $order->date_add;
        $invoice_add_date = $order->invoice_date;
        $delivery_date = $order->delivery_date;
        $order_number = '';
        $invoice_number = $order->invoice_number;
        $invoice_status = '';
        $customer_id = $address->id_customer;
        $last_name = $customer->lastname;
        $first_name = $customer->firstname;
        $email = $customer->email;
        $company = $address->company;
        $address_1 = $address->address1;
        $address_2 = $address->address2;
        $postcode = (string)$address->postcode;
        $city = $address->city;
        $country = $address->country;
        $id_country = $address->id_country;
        $phone = $address->phone;
        $first_order = $order->date_add;
        $last_order = $order->date_add;
        $of_order = 0;
        $of_products_ordered = $product['product_quantity'];
        $average_cart_all_included = ''; /*         * ************************* */
        $products_ordered = $total_quantity;
        $total_products_no_tax = (double) $unit_price * (int) $total_quantity;
        $total_cost = ($product['original_wholesale_price']) * (int) $total_quantity;
        $total_discounts_tax_excl = '';
        $product_discount = $this->calcDiscount($product);
        $total_discounts_tax_excl = $product_discount['total_discounts_tax_excl'];
        $gross_profit = (($unit_price) - ($product['original_wholesale_price'])) * (int) $total_quantity;
        $net_profit = $gross_profit - $total_discounts_tax_excl;
        $gross_margin = ($gross_profit / ($unit_price * $total_quantity)) * 100;
        $net_margin = ($net_profit / $total_products_no_tax) * 100;
        $data = $this->getreportCustomer($customer_id, (int)$order->id_shop);
        $total_paid_with_tax = $order->total_paid;
        if ($data != null) {
            $get_data = $data[0];
            if ($id_order!=$get_data['id_order']) {
                $of_order=$get_data['of_order']+1;
                $total_paid_with_tax=$get_data['total_paid_with_tax']+$total_paid_with_tax;
            }
            if ($id_order==$get_data['id_order']) {
                $of_order=$get_data['of_order'];
                $total_paid_with_tax=$get_data['total_paid_with_tax'];
            }
            $of_products_ordered = $get_data['of_products_ordered'] + $of_products_ordered;
            $average_cart_all_included = ''; /**/
            $products_ordered = $products_ordered + $get_data['products_ordered']; /**/
            $total_products_no_tax = $get_data['total_products_no_tax'] + $total_products_no_tax;
            $total_cost = $get_data['total_cost'] + $total_cost;
            $total_discounts_tax_excl = $get_data['total_discounts_tax_excl'] + $total_discounts_tax_excl;
            $gross_profit = $get_data['gross_profit'] + $gross_profit;
            $net_profit = $get_data['net_profit'] + $net_profit;
            $gross_margin = ($gross_profit / $total_products_no_tax) * 100;
            $net_margin = ($net_profit / $total_products_no_tax) * 100;

            $query = 'UPDATE ' . _DB_PREFIX_ . 'ba_report_customer SET last_order="' . pSQL($last_order) . '",'
                    . 'of_order="' . (int)$of_order . '",id_order="'.(int)$id_order.'",'
                    .'of_products_ordered="' . (int)$of_products_ordered . '",'
                    . 'average_cart_all_included="' . (double)$average_cart_all_included . '",'
                    . 'products_ordered="' . pSQL($products_ordered) . '",'
                    .'total_products_no_tax="' . (double)$total_products_no_tax . '",'
                    . 'total_cost="' . (double)$total_cost . '",'
                    .'total_discounts_tax_excl="' . (double)$total_discounts_tax_excl . '",'
                    . 'gross_profit="' . (double)$gross_profit . '",net_profit="' . (double)$net_profit . '",'
                    . 'gross_margin="' . (double)$gross_margin . '",net_margin="' . (double)$net_margin . '",'
                    . 'total_paid_with_tax="'.(double)$total_paid_with_tax.'" '
                    . 'WHERE customer_id="' . (int)$customer_id . '" AND id_shop='.(int)$order->id_shop;
            Db::getInstance()->query($query);
        }
        if ($data == null) {
            $of_order=1;
            Db::getInstance()->insert('ba_report_customer', array(
                'id_shop' => (int)$order->id_shop,
                'shop_name' => pSQL($shop_name),
                'id_cart' => (int)$id_cart,
                'id_order' => (int)$id_order,
                'order_add_date' => pSQL($order_add_date),
                'invoice_add_date' => pSQL($invoice_add_date),
                'delivery_date' => pSQL($delivery_date),
                'order_number' => (int)$order_number,
                'invoice_number' => (int)$invoice_number,
                'invoice_status' => (int)$invoice_status,
                'customer_id' => (int)$customer_id,
                'last_name' => pSQL($last_name),
                'first_name' =>pSQL($first_name),
                'email' => pSQL($email),
                'company' => pSQL($company),
                'address_1' => pSQL($address_1),
                'address_2' => pSQL($address_2),
                'postcode' => pSQL($postcode),
                'city' => pSQL($city),
                'country' => pSQL($country),
                'id_country' => (int)$id_country,
                'phone' => pSQL($phone),
                'first_order' => pSQL($first_order),
                'last_order' => pSQL($last_order),
                'of_order' => (int)$of_order,
                'of_products_ordered' => (int)$of_products_ordered,
                'average_cart_all_included' => (double)$average_cart_all_included,
                'products_ordered' => pSQL($products_ordered),
                'total_products_no_tax' => (double)$total_products_no_tax,
                'total_cost' => (double)$total_cost,
                'total_discounts_tax_excl' => (double)$total_discounts_tax_excl,
                'gross_profit' => (double)$gross_profit,
                'net_profit' => (double)$net_profit,
                'gross_margin' => (double)$gross_margin,
                'net_margin' => (double)$net_margin,
                'total_paid_with_tax'=>(double)$total_paid_with_tax
            ));
        }
        return true;
    }
    public function updateAllCustomerReport()
    {
        $query = 'SELECT * FROM ' . _DB_PREFIX_ . 'ba_report_customer';
        $data = DB::getInstance()->executeS($query, true, false);
        $n = count($data);
        for ($i = 0; $i < $n; $i++) {
            $get_data=$data[$i];
            $gross_profit=(double) $get_data['gross_profit'];
            $gross_margin = ($gross_profit / (double) $get_data['total_products_no_tax']) * 100;
            $net_profit=(double) $get_data['net_profit'];
            $net_margin_tax_excl = ($net_profit / (double) $get_data['total_products_no_tax']) * 100;
            $average= ((double) $get_data['total_paid_with_tax'] / (double) $get_data['of_order']);
            /*******************/
            $query = 'UPDATE ' . _DB_PREFIX_ . 'ba_report_customer SET'
                    .' average_cart_all_included="' .(double) $average . '" '
                    .', gross_margin="' .(double) $gross_margin . '" '
                    .', net_margin="' .(double) $net_margin_tax_excl . '" '
                    . 'WHERE id_report= ' . (int) $get_data['id_report'];
            Db::getInstance()->query($query);
        }
        return true;
    }
    public function getreportCustomer($id_customer, $id_shop)
    {
        $query = 'SELECT * FROM ' . _DB_PREFIX_ . 'ba_report_customer '
            .'WHERE customer_id="' . (int)$id_customer . '"';
        $query .= ' AND id_shop=' . (int)$id_shop;
        $data = DB::getInstance()->executeS($query, true, false);
        return $data;
    }
    public function convertPercentCustomer($value)
    {
        $data_view = round($value, 2) . '%';
        return $data_view;
    }
    public function convertMoneyCustomer($value)
    {
        $tool = new Tools();
        $a = round($value, 2);
        $data_view = $tool->displayPrice($a);
        return $data_view;
    }
    public function countData($helper)
    {
        $sql = 'SELECT count(*) FROM ' . _DB_PREFIX_ . 'ba_report_customer '
                . $this->setWhereClause($helper);
        $data = DB::getInstance()->getValue($sql, false);
        return $data;
    }
}
