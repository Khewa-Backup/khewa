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

class BaViewBasic extends ReportSale
{
    protected $statuses_array = array();
    private $orderby;
    private $orderway;
    private $ps_searchable_fields = array('id_shop','shop_name', 'order_id',
        'cart_id', 'order_number',
        'invoice_number', 'invoice_status',
        'first_name', 'last_name', 'products_name',
        'postcode', 'city',
        'country', 'id_country',
        'total_with_tax', 'total_products_no_tax',
        'products_tax', 'including_ecotax_tax_excl',
        'ecluding_ecotax_tax_amount', 'total_shipping_without_tax',
        'shipping_tax_amount', 'total_discounts_tax_excl',
        'discounts_tax_amount', 'total_wrapping_tax_excl',
        'wrapping_tax_amount', 'total_tax',
        'order_state', 'total_cost',
        'gross_profit_before_discounts', 'net_profit_tax_excl',
        'gross_margin_before_discounts', 'net_margin_tax_excl', 'reference'
        ,'state'
        );
    public function setWhereClauseDate($helper)
    {
        $sql = null;
        $orderDateArr_order_date = Tools::getValue($helper->list_id . "Filter_order_add_date", null);
        if ($orderDateArr_order_date !== null) {
            $d = $orderDateArr_order_date;
            $this->context->cookie->{$helper->list_id.'Filter_order_add_date'} = serialize($d);
            if (!empty($orderDateArr_order_date[0])) {
                $sql.=" AND order_add_date >= '" . pSQL($orderDateArr_order_date[0]) . " 00:00:00' ";
            }
            if (!empty($orderDateArr_order_date[1])) {
                $sql.=" AND order_add_date <= '" . pSQL($orderDateArr_order_date[1]) . " 23:59:59' ";
            }
        }
        //////
        $invoice_add_date = Tools::getValue($helper->list_id . "Filter_invoice_add_date", null);
        if ($invoice_add_date !== null) {
            $d = $invoice_add_date;
            $this->context->cookie->{$helper->list_id.'Filter_invoice_add_date'} = serialize($d);
            if (!empty($invoice_add_date[0])) {
                $sql.=" AND invoice_add_date >= '" . pSQL($invoice_add_date[0]) . " 00:00:00' ";
            }
            if (!empty($invoice_add_date[1])) {
                $sql.=" AND invoice_add_date <= '" . pSQL($invoice_add_date[1]) . " 23:59:59' ";
            }
        }

        return $sql;
    }
    public function setWhereClause($helper)
    {
        foreach ($this->ps_searchable_fields as $search_field) {
            $search_value = trim(Tools::getValue($helper->list_id . "Filter_" . $search_field, null));
            if (!empty($search_value)) {
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
        $helper_list_id = $this->name . 'ba_report_basic';
        foreach ($this->ps_searchable_fields as $search_field) {
            $this->context->cookie->{$helper_list_id . 'Filter_' . $search_field} = null;
        }
        Configuration::updateValue($this->name.'_basic_where', null);
    }
    public function viewbasiclist()
    {
        $statuses = OrderState::getOrderStates((int)$this->context->language->id);
        foreach ($statuses as $status) {
            $this->statuses_array[$status['id_order_state']] = $status['name'];
        }
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
            'order_id' => array(
                'title' => $this->l('Order ID'),
                'type' => 'text'
            ),
            'reference' => array(
                'title' => $this->l('Reference'),
                'type' => 'text'
            ),
            'order_add_date' => array(
                'title' => $this->l('Order add date'),
                'type' => 'date'
            ),
            'invoice_number' => array(
                'title' => $this->l('Invoice Number'),
                'type' => 'text',
                'callback' => 'displayInvoiceNumber',
                'callback_object' => $this
            ),
            'invoice_add_date' => array(
                'title' => $this->l('Invoice Date'),
                'type' => 'date'
            ),
            'products_name' => array(
                'title' => $this->l('Products'),
                'type' => 'text',
                'width' => 300,
                'orderby' => false,
                'callback' => 'displayProducts',
                'callback_object' => $this
            ),
            'first_name' => array(
                'title' => $this->l('First name'),
                'type' => 'text'
            ),
            'last_name' => array(
                'title' => $this->l('Last name'),
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
            'total_with_tax' => array(
                'title' => $this->l('Total Paid With Tax'),
                'type' => 'text',
                'callback' => 'convertMoneyBasic',
                'callback_object' => $this
            ),
            'total_products_no_tax' => array(
                'title' => $this->l('Total products no tax'),
                'type' => 'text',
                'callback' => 'convertMoneyBasic',
                'callback_object' => $this
            ),
            'products_tax' => array(
                'title' => $this->l('Products tax'),
                'type' => 'text',
                'callback' => 'convertMoneyBasic',
                'callback_object' => $this
            ),
            'including_ecotax_tax_excl' => array(
                'title' => $this->l('Including ecotax tax excl'),
                'type' => 'text',
                'callback' => 'convertMoneyBasic',
                'callback_object' => $this
            ),
            'ecluding_ecotax_tax_amount' => array(
                'title' => $this->l('Ecluding ecotax tax amount'),
                'type' => 'text',
                'callback' => 'convertMoneyBasic',
                'callback_object' => $this
            ),
            'total_shipping_without_tax' => array(
                'title' => $this->l('Total shipping without tax'),
                'type' => 'text',
                'callback' => 'convertMoneyBasic',
                'callback_object' => $this
            ),
            'shipping_tax_amount' => array(
                'title' => $this->l('Shipping tax amount'),
                'type' => 'text',
                'callback' => 'convertMoneyBasic',
                'callback_object' => $this
            ),
            'total_discounts_tax_excl' => array(
                'title' => $this->l('Total discounts tax excl'),
                'type' => 'text',
                'callback' => 'convertMoneyBasic',
                'callback_object' => $this
            ),
            'discounts_tax_amount' => array(
                'title' => $this->l('Discounts tax amount'),
                'type' => 'text',
                'callback' => 'convertMoneyBasic',
                'callback_object' => $this
            ),
            'total_wrapping_tax_excl' => array(
                'title' => $this->l('Total wrapping tax excl'),
                'type' => 'text',
                'callback' => 'convertMoneyBasic',
                'callback_object' => $this
            ),
            'wrapping_tax_amount' => array(
                'title' => $this->l('Wrapping tax amount'),
                'type' => 'text',
                'callback' => 'convertMoneyBasic',
                'callback_object' => $this
            ),
            'total_tax' => array(
                'title' => $this->l('Total tax'),
                'type' => 'text',
                'callback' => 'convertMoneyBasic',
                'callback_object' => $this
            ),
            'order_state' => array(
                'title' => $this->l('Order state'),
                'type' => 'select',
                'color' => 'color',
                'list' => $this->statuses_array,
                'callback'=>'getNameOrderState',
                'callback_object'=>$this,
                'filter_key' => 'order_state',
                'filter_type' => 'int',
                'class'=>'ba_report_sale_order_state'
            ),
            'total_cost' => array(
                'title' => $this->l('Total cost'),
                'type' => 'text',
                'callback' => 'convertMoneyBasic',
                'callback_object' => $this
            ),
            'gross_profit_before_discounts' => array(
                'title' => $this->l('Gross profit before discounts'),
                'type' => 'text',
                'callback' => 'convertMoneyBasic',
                'callback_object' => $this
            ),
            'net_profit_tax_excl' => array(
                'title' => $this->l('Net profit tax excl'),
                'type' => 'text',
                'callback' => 'convertMoneyBasic',
                'callback_object' => $this
            ),
            'gross_margin_before_discounts' => array(
                'title' => $this->l('Gross margin before discounts'),
                'type' => 'text',
                'callback' => 'convertPercentBasic',
                'callback_object' => $this
            ),
            'net_margin_tax_excl' => array(
                'title' => $this->l('Net margin tax excl'),
                'type' => 'text',
                'callback' => 'convertPercentBasic',
                'callback_object' => $this
            ),
            'iso_currency' => array(
                'title' => $this->l('Currency ISO'),
                'type' => 'text'
            ),
            'iso_currency' => array(
                'title' => $this->l('Currency ISO'),
                'type' => 'text'
            ),
        );
        $helper->shopLinkType = '';
        $helper->identifier = 'id_report';
        $helper->show_toolbar = true;
        $helper->simple_header = false;
        $helper->no_link = true;
        $helper->list_id = $this->name . 'ba_report_basic';
        $this->orderby = pSQL(Tools::getValue($helper->list_id . "Orderby", "order_id"));
        $this->orderway = pSQL(Tools::getValue($helper->list_id . "Orderway", "ASC"));
        $helper->orderBy = $this->orderby;
        $helper->orderWay = Tools::strtoupper($this->orderway);

        $helper->title = $this->l('Report Basic');
        $helper->table = $this->name . 'ba_report_basic';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $ad1 = AdminController::$currentIndex;
        $n1 = $this->name;
        $tk1 = Tools::getAdminTokenLite('AdminModules');
        $o1 = "reportsaleba_report_basicOrderby";
        $o2 = "reportsaleba_report_basicOrderway";
        $b1 = "csv=basic";
        $tor1 = $this->orderby;
        $tor2 = $this->orderway;
        $helper->toolbar_btn['export'] = array(
            'href' => $ad1.'&configure='.$n1.'&token='.$tk1.'&task=basic&'.$o1.'='.$tor1.'&'.$o2.'='.$tor2.'&'.$b1.'',
            'desc' => $this->l('export csv')
        );
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name .'&task=basic';
        $helper->currentIndex .= '&'.$helper->list_id . "Orderby=".$helper->orderBy;
        $helper->currentIndex .= '&'.$helper->list_id . "Orderway=".$helper->orderWay;
        $con = (int) $this->countData($helper);
        $helper->listTotal = $con;
        /*****-----------------Pagination----------------***********/
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
        $rows = $this->selectdatabasic($helper, $start, $selected_pagination);

        $table_helper = $helper->generateList($rows, $fields_list);
        $table_helper .= $this->getSummaryBlock($helper, $fields_list);
        return $table_helper;
    }
    public function selectdatabasic($helper, $start, $selected_pagination)
    {
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'ba_report_basic';
        $where = $this->setWhereClause($helper);
        $sql .= $where;
        Configuration::updateValue($this->name.'_basic_where', $where);
        $sql.=' ORDER BY ' . pSQL($this->orderby) . ' ' . pSQL($this->orderway)
        . ' LIMIT ' . (int) $start . ', ' . (int) $selected_pagination;
        $rows = Db::getInstance()->executeS($sql, true, false);
        return($rows);
    }
    public function insertreportbasic($id_order)
    {
        $order = new Order($id_order);
        $a = $order->getproducts();

        $default_currency = (int) Configuration::get('PS_CURRENCY_DEFAULT');
        $c_from = new Currency($default_currency);
        $id_currency = (int) $order->id_currency;
        $c_to = new Currency($id_currency);
        foreach ($a as $key => &$value) {
            if (!isset($value['original_wholesale_price'])) {
                $p_id = $value['product_id'];
                $a_id = $value['product_attribute_id'];
                $value['original_wholesale_price'] = $this->getWholeSalePrice($p_id, $a_id);
            }
            $o = $value['original_wholesale_price'];
            $value['original_wholesale_price'] = Tools::convertPriceFull($o, $c_from, $c_to);
            $o = $value['wholesale_price'];
            $value['wholesale_price'] = Tools::convertPriceFull($o, $c_from, $c_to);
            $this->insertbasic($value, $order);
            echo $key;
        }
        // tính lại giảm giá cho Order
        $order_total_discount = $this->calcDiscountOrder($order);
        $products_name = $this->getProductsNameOfOrder($order);
        $reference = $order->reference;
        // tính lại total tax
        $query = 'UPDATE ' . _DB_PREFIX_ . 'ba_report_basic SET '
                ."total_discounts_tax_excl=total_discounts_tax_excl+"
                .(double) $order_total_discount["total_discounts_tax_excl"]
                .", discounts_tax_amount=discounts_tax_amount+".(double) $order_total_discount["discounts_tax_amount"]
                .", net_profit_tax_excl=gross_profit_before_discounts-total_discounts_tax_excl"
                .", total_tax=total_tax+shipping_tax_amount+wrapping_tax_amount-"
                .(double) $order_total_discount["discounts_tax_amount"]
                .', products_name = "'.pSQL($products_name).'"'
                .', reference = "'.pSQL($reference).'"'
                . ' WHERE order_id=' . (int)$id_order;
        
        Db::getInstance()->query($query);
        $this->updateAllBasicReport($id_order);
        return true;
    }
    public function insertbasic($product, $order)
    {
        $address = new Address($order->id_address_invoice);
        $customer = new Customer($address->id_customer);
        /*******Price and Tax and Quantity*********/
        $unit_price = $product['unit_price_tax_excl'];
        $tax_rate = $product['tax_rate'];
        $total_quantity = (int) $product['product_quantity'];
        /*********End Price and Tax and Quantity******** */
        $shop_name = $this->getShopName((int)$order->id_shop);
        $order_id = $order->id;
        $id_currency = $order->id_currency;
        $cart_id = $order->id_cart;
        $order_add_date = $order->date_add;
        $order_number = '';
        $invoice_add_date = $order->invoice_date;
        $invoice_number = $order->invoice_number;
        $invoice_status = '';
        $delivery_date = $order->delivery_date;
        $last_name = $customer->lastname;
        $first_name = $customer->firstname;
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
        $total_with_tax = $order->total_paid;
        $total_products_no_tax = (double) $unit_price * (int) $total_quantity;
        $original_price = (double) $product['original_product_price'];
        $products_tax = ((double) ($tax_rate / 100) * ($unit_price)) * (int) $total_quantity;
        /** eco TAX **/
        $ecotax_incl = $product['ecotax'];
        $ecotax_tax_rate = $product['ecotax_tax_rate'];
        $ecotax_tax_excl = ($ecotax_incl/ (1+$ecotax_tax_rate/100));
        $ecluding_ecotax_tax_amount = ($ecotax_incl -  $ecotax_tax_excl) * $total_quantity;
        $including_ecotax_tax_excl = $ecotax_tax_excl * $total_quantity;
        
        $total_shipping_tax_incl = $order->total_shipping_tax_incl; /*         * total shipping* */
        $total_shipping_without_tax = $order->total_shipping_tax_excl;
        $shipping_tax_amount = 0;
        if ($total_shipping_tax_incl != 0) {
            $shipping_tax_amount = $total_shipping_tax_incl - $total_shipping_without_tax;
        }
        /** total shipping* */
        $total_discounts_tax_excl = '';
        $discounts_tax_amount = '';
        /** ****** discount total****/
        $product_discount = $this->calcDiscount($product);
        $total_discounts_tax_excl = $product_discount['total_discounts_tax_excl'];
        $discounts_tax_amount = $product_discount['discounts_tax_amount'];
        /* Total Wrapping**/
        $total_wrapping_tax_incl = $order->total_wrapping_tax_incl;
        $total_wrapping_tax_excl = $order->total_wrapping_tax_excl;
        $wrapping_tax_amount = 0;
        if ($total_wrapping_tax_incl != 0) {
            $wrapping_tax_amount = $total_wrapping_tax_incl - $total_wrapping_tax_excl;
        }
        /*total product tax */
        $total_tax = $products_tax; /* total tax */
        $order_state = (int)$order->current_state;
        $total_cost = ($product['original_wholesale_price']) * (int) $total_quantity;
        $gross_profit_before_discounts = $original_price * $total_quantity  - $total_cost;

        $net_profit_tax_excl = $gross_profit_before_discounts - $total_discounts_tax_excl;
        $gross_margin_before_discounts = '';
        $net_margin_tax_excl = "";
        /////////////////////////
        $currency = new Currency($id_currency);
        $sign_currency = $currency->sign;
        $iso_currency = $currency->iso_code;

        $data = $this->getreportBasic($order_id);
        if ($data != null) {
            $get_data = $data[0];
            $total_tax = $get_data['total_tax'] + $total_tax;
            $total_products_no_tax = $total_products_no_tax + $get_data['total_products_no_tax'];
            $products_tax = $products_tax + $get_data['products_tax'];
            $ecluding_ecotax_tax_amount = $ecluding_ecotax_tax_amount + $get_data['ecluding_ecotax_tax_amount'];
            $including_ecotax_tax_excl = $including_ecotax_tax_excl + $get_data['including_ecotax_tax_excl'];
            $total_discounts_tax_excl=$total_discounts_tax_excl+$get_data['total_discounts_tax_excl'];
            $discounts_tax_amount=$discounts_tax_amount+$get_data['discounts_tax_amount'];
            $total_cost = $total_cost + $get_data['total_cost'];
            $gross_profit_before_discounts=$gross_profit_before_discounts+$get_data['gross_profit_before_discounts'];
            $net_profit_tax_excl = $net_profit_tax_excl + $get_data['net_profit_tax_excl'];

            $query = 'UPDATE ' . _DB_PREFIX_ . 'ba_report_basic SET '
                    . 'total_products_no_tax="' . (double)$total_products_no_tax . '",'
                    .'products_tax="' . (double)$products_tax . '",'
                    . 'ecluding_ecotax_tax_amount="' . (double)$ecluding_ecotax_tax_amount . '",'
                    . 'including_ecotax_tax_excl="' . (double)$including_ecotax_tax_excl . '",'
                    . 'total_discounts_tax_excl="' . (double)$total_discounts_tax_excl . '",'
                    . 'discounts_tax_amount="' . (double)$discounts_tax_amount . '",'
                    . 'total_tax="' . (double)$total_tax . '",total_cost="' . (double)$total_cost . '",'
                    . 'gross_profit_before_discounts="' . (double)$gross_profit_before_discounts . '",'
                    . 'net_profit_tax_excl="' . (double)$net_profit_tax_excl . '",'
                    . 'sign_currency="' . $sign_currency . '",'
                    . 'iso_currency="' . $iso_currency . '",'
                    . 'id_currency="' . $id_currency . '"'
                    . ' WHERE '
                    . 'order_id="' . (int)$order_id . '"';
            Db::getInstance()->query($query);
        }
        if ($data == null) {
            Db::getInstance()->insert('ba_report_basic', array(
                'shop_name' => $shop_name,
                'id_shop' => (int)$order->id_shop,
                'order_id' => (int)$order_id,
                'cart_id' => (int)$cart_id,
                'order_add_date' => pSQL($order_add_date),
                'order_number' => (int)$order_number,
                'invoice_add_date' => pSQL($invoice_add_date),
                'invoice_number' => (int)$invoice_number,
                'invoice_status' => (int)$invoice_status,
                'last_name' => pSQL($last_name),
                'delivery_date' => pSQL($delivery_date),
                'first_name' => pSQL($first_name),
                'postcode' => pSQL($postcode),
                'city' => pSQL($city),
                'country' => pSQL($country),
                'id_country' => (int)$id_country,
                'total_with_tax' => (double)$total_with_tax,
                'total_products_no_tax' => (double)$total_products_no_tax,
                'products_tax' => (double)$products_tax,
                'including_ecotax_tax_excl' => (double)$including_ecotax_tax_excl,
                'ecluding_ecotax_tax_amount' => (double)$ecluding_ecotax_tax_amount,
                'total_shipping_without_tax' => (double)$total_shipping_without_tax,
                'shipping_tax_amount' => (double)$shipping_tax_amount,
                'total_discounts_tax_excl' => (double)$total_discounts_tax_excl,
                'discounts_tax_amount' => (double)$discounts_tax_amount,
                'total_wrapping_tax_excl' => (double)$total_wrapping_tax_excl,
                'wrapping_tax_amount' => (double)$wrapping_tax_amount,
                'total_tax' => (double)$total_tax,
                'order_state' => pSQL($order_state),
                'total_cost' => (double)$total_cost,
                'gross_profit_before_discounts' => (double)$gross_profit_before_discounts,
                'net_profit_tax_excl' => (double)$net_profit_tax_excl,
                'gross_margin_before_discounts' => (double)$gross_margin_before_discounts,
                'net_margin_tax_excl' => (double)$net_margin_tax_excl,
                'sign_currency' => $sign_currency,
                'iso_currency' => $iso_currency,
                'id_currency' => $id_currency,
                'id_state' => $id_state,
                'state' => $state_name
            ));
        }
        return true;
    }
    public function getreportBasic($order_id)
    {
        $query = 'SELECT * FROM ' . _DB_PREFIX_ . 'ba_report_basic WHERE order_id="' . (int)$order_id . '"';
        $data = DB::getInstance()->executeS($query, true, false);
        return $data;
    }
    public function updateAllBasicReport($order_id)
    {
        $query = 'SELECT * FROM ' . _DB_PREFIX_ . 'ba_report_basic WHERE order_id=' . (int)$order_id;
        $data = DB::getInstance()->executeS($query, true, false);
        $get_data = $data[0];
        $gross_profit=(double) $get_data['gross_profit_before_discounts'];
        $gross_margin_before_discounts = 0;
        if ($get_data['total_products_no_tax'] > 0) {
            $gross_margin_before_discounts = ($gross_profit / (double) $get_data['total_products_no_tax']) * 100;
        }
        $net_profit=(double) $get_data['net_profit_tax_excl'];
        $net_margin_tax_excl = 0;
        if ($get_data['total_products_no_tax'] > 0) {
            $net_margin_tax_excl = ($net_profit / (double) $get_data['total_products_no_tax']) * 100;
        }

        $query = 'UPDATE ' . _DB_PREFIX_ . 'ba_report_basic SET '
                .'gross_margin_before_discounts="' . (double)$gross_margin_before_discounts . '",'
                . 'net_margin_tax_excl="' . (double)$net_margin_tax_excl . '"'
                . ' WHERE order_id=' . (int)$order_id;
        Db::getInstance()->query($query);
        return true;
    }
    public function convertPercentBasic($value)
    {
        $data_view = round($value, 2) . '%';
        return $data_view;
    }
    public function convertMoneyBasic($value, $row)
    {
        $a = round($value, 2);
        $order = new Order($row['order_id']);
        return Tools::displayPrice($a, (int)$order->id_currency);
    }
    public function getSummaryBlock($helper, $fields_list)
    {
        $sql = 'SELECT COUNT(DISTINCT id_shop) as id_shop';
        $sql .= ', COUNT(DISTINCT shop_name) as shop_name';
        $sql .= ', COUNT(DISTINCT order_id) as order_id';
        $sql .= ', COUNT(DISTINCT reference) as reference';
        $sql .= ', MIN(order_add_date) as min_order_add_date';
        $sql .= ', MAX(order_add_date) as max_order_add_date';
        $sql .= ", COUNT(DISTINCT invoice_number) - COUNT(DISTINCT case when invoice_number='' then 1 end)";
        $sql .= " as invoice_number";
        $sql .= ', MIN(invoice_add_date) as min_invoice_add_date';
        $sql .= ', MAX(invoice_add_date) as max_invoice_add_date';
        $sql .= ', COUNT(DISTINCT postcode) as postcode';
        $sql .= ', COUNT(DISTINCT city) as city';
        $sql .= ', COUNT(DISTINCT country) as country';
        $sql .= ', COUNT(DISTINCT id_country) as id_country';
        $sql .= ", COUNT(DISTINCT state) - COUNT(DISTINCT case when state='' then 1 end)";
        $sql .= " as state";
        $sql .= ', COUNT(DISTINCT order_state) as order_state';
        $sql .= ', COUNT(DISTINCT iso_currency) as iso_currency';
        $sql .= ' FROM ' . _DB_PREFIX_ . 'ba_report_basic ';
        $sql .= $this->setWhereClause($helper);
        $item1 = DB::getInstance()->getRow($sql, false);
        if (empty($item1)) {
            return false;
        }
        $sql = 'SELECT id_currency';
        $sql .= ', SUM(total_with_tax) as total_with_tax';
        $sql .= ', SUM(total_products_no_tax) as total_products_no_tax';
        $sql .= ', SUM(products_tax) as products_tax';
        $sql .= ', SUM(including_ecotax_tax_excl) as including_ecotax_tax_excl';
        $sql .= ', SUM(ecluding_ecotax_tax_amount) as ecluding_ecotax_tax_amount';
        $sql .= ', SUM(total_shipping_without_tax) as total_shipping_without_tax';
        $sql .= ', SUM(shipping_tax_amount) as shipping_tax_amount';
        $sql .= ', SUM(total_discounts_tax_excl) as total_discounts_tax_excl';
        $sql .= ', SUM(discounts_tax_amount) as discounts_tax_amount';
        $sql .= ', SUM(total_wrapping_tax_excl) as total_wrapping_tax_excl';
        $sql .= ', SUM(wrapping_tax_amount) as wrapping_tax_amount';
        $sql .= ', SUM(total_tax) as total_tax';
        $sql .= ', SUM(total_cost) as total_cost';
        $sql .= ', SUM(gross_profit_before_discounts) as gross_profit_before_discounts';
        $sql .= ', SUM(net_profit_tax_excl) as net_profit_tax_excl';
        $sql .= ' FROM ' . _DB_PREFIX_ . 'ba_report_basic ';
        $sql .= $this->setWhereClause($helper);
        $sql .= ' GROUP BY id_currency ';
        $item2 = DB::getInstance()->executeS($sql, true, false);
        if (empty($item2)) {
            return false;
        }
        $data = array(
            'total_with_tax' => 0,
            'total_products_no_tax' => 0,
            'products_tax' => 0,
            'including_ecotax_tax_excl' => 0,
            'ecluding_ecotax_tax_amount' => 0,
            'total_shipping_without_tax' => 0,
            'shipping_tax_amount' => 0,
            'total_discounts_tax_excl' => 0,
            'discounts_tax_amount' => 0,
            'total_wrapping_tax_excl' => 0,
            'wrapping_tax_amount' => 0,
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
            $data['total_with_tax'] += Tools::convertPriceFull($value['total_with_tax'], $c_from, $c_to);
            $data['total_products_no_tax'] += Tools::convertPriceFull($value['total_products_no_tax'], $c_from, $c_to);
            $data['products_tax'] += Tools::convertPriceFull($value['products_tax'], $c_from, $c_to);
            $i = $value['including_ecotax_tax_excl'];
            $data['including_ecotax_tax_excl'] += Tools::convertPriceFull($i, $c_from, $c_to);
            $e = $value['ecluding_ecotax_tax_amount'];
            $data['ecluding_ecotax_tax_amount'] += Tools::convertPriceFull($e, $c_from, $c_to);
            $t = $value['total_shipping_without_tax'];
            $data['total_shipping_without_tax'] += Tools::convertPriceFull($t, $c_from, $c_to);
            $data['shipping_tax_amount'] += Tools::convertPriceFull($value['shipping_tax_amount'], $c_from, $c_to);
            $t = $value['total_discounts_tax_excl'];
            $data['total_discounts_tax_excl'] += Tools::convertPriceFull($t, $c_from, $c_to);
            $data['discounts_tax_amount'] += Tools::convertPriceFull($value['discounts_tax_amount'], $c_from, $c_to);
            $t = $value['total_wrapping_tax_excl'];
            $data['total_wrapping_tax_excl'] += Tools::convertPriceFull($t, $c_from, $c_to);
            $data['wrapping_tax_amount'] += Tools::convertPriceFull($value['wrapping_tax_amount'], $c_from, $c_to);
            $data['total_tax'] += Tools::convertPriceFull($value['total_tax'], $c_from, $c_to);
            $data['total_cost'] += Tools::convertPriceFull($value['total_cost'], $c_from, $c_to);
            $g = $value['gross_profit_before_discounts'];
            $data['gross_profit_before_discounts'] += Tools::convertPriceFull($g, $c_from, $c_to);
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
            $this->l('#Shop Name'),
            number_format($data['shop_name'])
        );
        $summary['order_id'] = array(
            $this->l('#Order ID'),
            number_format($data['order_id'])
        );
        $summary['reference'] = array(
            $this->l('#Reference'),
            number_format($data['reference'])
        );
        $summary['order_add_date'] = array(
            $this->l('Order add date'),
            $this->l('From: ').Tools::displayDate($data['min_order_add_date']),
            $this->l('To: ').Tools::displayDate($data['max_order_add_date']),
        );
        $summary['invoice_number'] = array(
            $this->l('#Invoice Number'),
            number_format($data['invoice_number'])
        );
        $summary['invoice_add_date'] = array(
            $this->l('Invoice Date'),
            $this->l('From: ').$this->displayFormattedDate($data['min_invoice_add_date']),
            $this->l('To: ').$this->displayFormattedDate($data['max_invoice_add_date']),
        );
        $summary['first_name'] = array(
            $this->l('First name'),
            $this->l('-')
        );
        $summary['last_name'] = array(
            $this->l('Last name'),
            $this->l('-')
        );
        $summary['products_name'] = array(
            $this->l('Products'),
            $this->l('-')
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
        $summary['order_state'] = array(
            $this->l('#Order state'),
            number_format($data['order_state'])
        );
        $summary['iso_currency'] = array(
            $this->l('#Currency ISO'),
            number_format($data['iso_currency'])
        );
        $summary['total_with_tax'] = array(
            $this->l('Total Paid With Tax'),
            Tools::displayPrice($data['total_with_tax'], $c_to)
        );
        $summary['total_products_no_tax'] = array(
            $this->l('Total products no tax'),
            Tools::displayPrice($data['total_products_no_tax'], $c_to)
        );
        $summary['products_tax'] = array(
            $this->l('Products tax'),
            Tools::displayPrice($data['products_tax'], $c_to)
        );
        $summary['including_ecotax_tax_excl'] = array(
            $this->l('Including ecotax tax excl'),
            Tools::displayPrice($data['including_ecotax_tax_excl'], $c_to)
        );
        $summary['ecluding_ecotax_tax_amount'] = array(
            $this->l('Ecluding ecotax tax amount'),
            Tools::displayPrice($data['ecluding_ecotax_tax_amount'], $c_to)
        );
        $summary['total_shipping_without_tax'] = array(
            $this->l('Total shipping without tax'),
            Tools::displayPrice($data['total_shipping_without_tax'], $c_to)
        );
        $summary['shipping_tax_amount'] = array(
            $this->l('Shipping tax amount'),
            Tools::displayPrice($data['shipping_tax_amount'], $c_to)
        );
        $summary['total_discounts_tax_excl'] = array(
            $this->l('Total discounts tax excl'),
            Tools::displayPrice($data['total_discounts_tax_excl'], $c_to)
        );
        $summary['discounts_tax_amount'] = array(
            $this->l('Discounts tax amount'),
            Tools::displayPrice($data['discounts_tax_amount'], $c_to)
        );
        $summary['total_wrapping_tax_excl'] = array(
            $this->l('Total wrapping tax excl'),
            Tools::displayPrice($data['total_wrapping_tax_excl'], $c_to)
        );
        $summary['wrapping_tax_amount'] = array(
            $this->l('Wrapping tax amount'),
            Tools::displayPrice($data['wrapping_tax_amount'], $c_to)
        );
        $summary['total_tax'] = array(
            $this->l('Total tax'),
            Tools::displayPrice($data['total_tax'], $c_to)
        );
        $summary['total_cost'] = array(
            $this->l('Total cost'),
            Tools::displayPrice($data['total_cost'], $c_to)
        );
        $summary['gross_profit_before_discounts'] = array(
            $this->l('Gross profit before discounts'),
            Tools::displayPrice($data['gross_profit_before_discounts'], $c_to)
        );
        $summary['net_profit_tax_excl'] = array(
            $this->l('Net profit tax excl'),
            Tools::displayPrice($data['net_profit_tax_excl'], $c_to)
        );
        $summary['gross_margin_before_discounts'] = array(
            $this->l('Gross margin before discounts'),
            round($data['gross_margin_before_discounts'] * 100, 2).$this->l('%')
        );
        $summary['net_margin_tax_excl'] = array(
            $this->l('Net margin tax excl'),
            round($data['net_margin_tax_excl'] * 100, 2).$this->l('%')
        );
        $this->smarty->assign('summary', $summary);
        $this->smarty->assign('fields_list', $fields_list);
        return $this->shortDisplay('views/templates/admin/summary_table.tpl');
    }
    public function countData($helper)
    {
        $sql = 'SELECT count(*) FROM ' . _DB_PREFIX_ . 'ba_report_basic '
                . $this->setWhereClause($helper);
        $data = DB::getInstance()->getValue($sql, false);
        return $data;
    }
    public function getOrderState($id_state, $id_lang)
    {
        $sql = 'SELECT name FROM ' . _DB_PREFIX_ . 'order_state_lang '
            . 'WHERE id_order_state='.(int) $id_state.' AND id_lang='.(int) $id_lang;
        $data = DB::getInstance()->executeS($sql, true, false);
        if (empty($data)) {
            return $this->l('undefined');
        }
        $value=$data[0];
        return $value['name'];
    }
    public function getNameOrderState($value)
    {
        $id_lang=$this->context->language->id;
        $name=$this->getOrderState($value, $id_lang);
        $return='';
        switch ($value) {
            case 1:
                $style='style="background-color:#4169E1;color:white"';
                $return = '<span class="report_sale_color" '.$style.'>'.$name.'</span>';
                break;
            case 2:
                $style='style="background-color:#32CD32;color:#383838"';
                $return = '<span class="report_sale_color" '.$style.'>'.$name.'</span>';
                break;
            case 3:
                $style='style="background-color:#FF8C00;color:#383838"';
                $return = '<span class="report_sale_color" '.$style.'>'.$name.'</span>';
                break;
            case 4:
                $style='style="background-color:#8A2BE2;color:white"';
                $return = '<span class="report_sale_color" '.$style.'>'.$name.'</span>';
                break;
            case 5:
                $style='style="background-color:#108510;color:white"';
                $return = '<span class="report_sale_color" '.$style.'>'.$name.'</span>';
                break;
            case 6:
                $style='style="background-color:#DC143C;color:white"';
                $return = '<span class="report_sale_color" '.$style.'>'.$name.'</span>';
                break;
            case 7:
                $style='style="background-color:#ec2e15;color:white"';
                $return = '<span class="report_sale_color" '.$style.'>'.$name.'</span>';
                break;
            case 8:
                $style='style="background-color:#8f0621;color:white"';
                $return = '<span class="report_sale_color" '.$style.'>'.$name.'</span>';
                break;
            case 9:
                $style='style="background-color:#FF69B4;color:#383838"';
                $return = '<span class="report_sale_color" '.$style.'>'.$name.'</span>';
                break;
            case 10:
                $style='style="background-color:#4169E1;color:white"';
                $return = '<span class="report_sale_color" '.$style.'>'.$name.'</span>';
                break;
            case 11:
                $style='style="background-color:#4169E1;color:white"';
                $return = '<span class="report_sale_color" '.$style.'>'.$name.'</span>';
                break;
            case 12:
                $style='style="background-color:#32CD32;color:#383838"';
                $return = '<span class="report_sale_color" '.$style.'>'.$name.'</span>';
                break;
            case 13:
                $style='style="background-color:#FF69B4;color:#383838"';
                $return = '<span class="report_sale_color" '.$style.'>'.$name.'</span>';
                break;
            case 14:
                $style='style="background-color:#4169E1;color:white"';
                $return = '<span class="report_sale_color" '.$style.'>'.$name.'</span>';
                break;
            default:
                $style='style="background-color:#cecece;color:white"';
                $return = '<span class="report_sale_color" '.$style.'>'.$name.'</span>';
                break;
        }
        return $return;
    }
}
