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

class BaViewTaxOnly extends ReportSale
{

    private $orderby;
    private $orderway;
    private $ps_searchable_fields = array('id_shop','shop_name', 'order_id',
        'order_number',
        'invoice_number', 'invoice_status', 'products_name',
        'cart_id', 'total_products_no_tax',
        'product_tax', 'including_ecotax_tax_excl',
        'including_ecotax_tax_amount', 'total_discounts_tax_excl',
        'discounts_tax_amount', 'total_wrapping_tax_excl',
        'wrapping_tax_amount', 'total_shipping_without_tax',
        'shipping_tax_amount', 'total_tax', 'last_name',
        'first_name', 'country', 'id_country', 'company', 'reference', 'state');
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
        $helper_list_id = $this->name . 'ba_report_taxonly';
        foreach ($this->ps_searchable_fields as $search_field) {
            $this->context->cookie->{$helper_list_id . 'Filter_' . $search_field} = null;
        }
        Configuration::updateValue($this->name.'_taxes_where', null);
    }
    public function viewtaxonlylist()
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
            'order_id' => array(
                'title' => $this->l('Order ID'),
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
            'products_name' => array(
                'title' => $this->l('Products'),
                'type' => 'text',
                'width' => 300,
                'orderby' => false,
                'callback' => 'displayProducts',
                'callback_object' => $this
            ),
            'total_products_no_tax' => array(
                'title' => $this->l('Total Products No Tax'),
                'type' => 'text',
                'callback' => 'convertMoneyTaxOnly',
                'callback_object' => $this
            ),
            'product_tax' => array(
                'title' => $this->l('Product Tax'),
                'type' => 'text',
                'callback' => 'convertMoneyTaxOnly',
                'callback_object' => $this
            ),
            'including_ecotax_tax_excl' => array(
                'title' => $this->l('Including Ecotax Tax Excl'),
                'type' => 'text',
                'callback' => 'convertMoneyTaxOnly',
                'callback_object' => $this
            ),
            'including_ecotax_tax_amount' => array(
                'title' => $this->l('Including Ecotax Tax Amount'),
                'type' => 'text',
                'callback' => 'convertMoneyTaxOnly',
                'callback_object' => $this
            ),
            'total_discounts_tax_excl' => array(
                'title' => $this->l('Total Discounts Tax Excl'),
                'type' => 'text',
                'callback' => 'convertMoneyTaxOnly',
                'callback_object' => $this
            ),
            'discounts_tax_amount' => array(
                'title' => $this->l('Discounts Tax Amount'),
                'type' => 'text',
                'callback' => 'convertMoneyTaxOnly',
                'callback_object' => $this
            ),
            'total_wrapping_tax_excl' => array(
                'title' => $this->l('Total Wrapping Tax Excl'),
                'type' => 'text',
                'callback' => 'convertMoneyTaxOnly',
                'callback_object' => $this
            ),
            'wrapping_tax_amount' => array(
                'title' => $this->l('Wrapping Tax Amount'),
                'type' => 'text',
                'callback' => 'convertMoneyTaxOnly',
                'callback_object' => $this
            ),
            'total_shipping_without_tax' => array(
                'title' => $this->l('Total Shipping Without Tax'),
                'type' => 'text',
                'callback' => 'convertMoneyTaxOnly',
                'callback_object' => $this
            ),
            'shipping_tax_amount' => array(
                'title' => $this->l('Shipping Tax Amount'),
                'type' => 'text',
                'callback' => 'convertMoneyTaxOnly',
                'callback_object' => $this
            ),
            'total_tax_5' => array(
                'title' => $this->l('Total Tax 5%'),
                'type' => 'text',
                'callback' => 'convertMoneyTaxOnly',
                'callback_object' => $this
            ),
			'total_tax_9975' => array(
                'title' => $this->l('Total Tax 9.975%'),
                'type' => 'text',
                'callback' => 'convertMoneyTaxOnly',
                'callback_object' => $this
            ),
			'total_tax' => array(
                'title' => $this->l('Total Tax'),
                'type' => 'text',
                'callback' => 'convertMoneyTaxOnly',
                'callback_object' => $this
            ),
            'last_name' => array(
                'title' => $this->l('Last Name'),
                'type' => 'text'
            ),
            'first_name' => array(
                'title' => $this->l('First Name'),
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
            'company' => array(
                'title' => $this->l('Company'),
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
        $helper->title = $this->l('Report Taxes');
        $helper->table = $this->name . 'ba_report_taxonly';
        $helper->list_id = $this->name . 'ba_report_taxonly';
        $this->orderby = pSQL(Tools::getValue($helper->list_id . "Orderby", "order_id"));
        $this->orderway = pSQL(Tools::getValue($helper->list_id . "Orderway", "ASC"));
        $c1 = AdminController::$currentIndex;
        $n1 = $this->name;
        $ad1 = Tools::getAdminTokenLite('AdminModules');
        $rp1 = "reportsaleba_report_taxonlyOrderby";
        $rp2 = "reportsaleba_report_taxonlyOrderway";
        $o1 = $this->orderby;
        $o2 = $this->orderway;
        $helper->toolbar_btn['export'] = array(
            'href' => $c1.'&configure='.$n1.'&token='.$ad1.'&task=taxes&'.$rp1.'='.$o1.'&'.$rp2.'='.$o2.'&csv=taxes',
            'desc' => $this->l('export csv')
        );
        $helper->orderBy = $this->orderby;
        $helper->orderWay = Tools::strtoupper($this->orderway);
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name . '&task=taxes';
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
        $rows = $this->selectdatataxonly($helper, $start, $selected_pagination);
        $table_helper = $helper->generateList($rows, $fields_list);
        $table_helper .= $this->getSummaryBlock($helper, $fields_list);
        return $table_helper;
    }

    public function selectdatataxonly($helper, $start, $selected_pagination)
    {
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'ba_report_tax_only ';
        $where = $this->setWhereClause($helper);
        $sql .= $where;
        Configuration::updateValue($this->name.'_taxes_where', $where);
        $sql.=' ORDER BY ' . pSQL($this->orderby) . ' ' . pSQL($this->orderway)
        . ' LIMIT ' . (int) $start . ', ' . (int) $selected_pagination;
        $rows = Db::getInstance()->executeS($sql, true, false);
        return($rows);
    }
    public function insertreporttaxonly($id_order)
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
            
            $this->inserttaxonly($value, $order);
            echo $key;
        }
        // tính lại giảm giá cho Order
        $order_total_discount = $this->calcDiscountOrder($order);
        $products_name = $this->getProductsNameOfOrder($order);
        $reference = $order->reference;
        // tính lại total tax
        $query = 'UPDATE ' . _DB_PREFIX_ . 'ba_report_tax_only SET '
                ."total_discounts_tax_excl=total_discounts_tax_excl+"
                .(double) $order_total_discount["total_discounts_tax_excl"]
                .", discounts_tax_amount=discounts_tax_amount+".(double) $order_total_discount["discounts_tax_amount"]
                .", total_tax=total_tax+shipping_tax_amount+wrapping_tax_amount-"
                .(double) $order_total_discount["discounts_tax_amount"]
                .', products_name = "'.pSQL($products_name).'"'
                .', reference = "'.pSQL($reference).'"'
                . ' WHERE order_id=' . (int)$id_order;
        
        Db::getInstance()->query($query);
		$this->updateTaxesBreakdown('ba_report_tax_only', 'order_id', $order);
        return true;
    }
    public function inserttaxonly($product, $order)
    {
        $address = new Address($order->id_address_invoice);
        $unit_price = $product['unit_price_tax_excl'];
        $tax_rate = $product['tax_rate'];
        $total_quantity = $product['product_quantity'];
        $shop_name = $this->getShopName((int)$order->id_shop);
        $order_id = $order->id;
        $id_currency = $order->id_currency;
        $order_add_date = $order->date_add;
        $order_number = '';
        $invoice_add_date = $order->invoice_date;
        $invoice_number = $order->invoice_number;
        $invoice_status = '';
        $cart_id = $order->id_cart;
        $delivery_date = $order->delivery_date;
        $total_products_no_tax = (double) $unit_price * (int) $total_quantity;
        $product_tax = ((double) ($tax_rate / 100) * ($unit_price)) * (int) $total_quantity;
        /** eco TAX **/
        $ecotax_incl = $product['ecotax'];
        $ecotax_tax_rate = $product['ecotax_tax_rate'];
        $ecotax_tax_excl = ($ecotax_incl/ (1+$ecotax_tax_rate/100));
        $including_ecotax_tax_amount = ($ecotax_incl -  $ecotax_tax_excl) * $total_quantity;
        $including_ecotax_tax_excl = $ecotax_tax_excl * $total_quantity;
        $total_discounts_tax_excl = '';
        $discounts_tax_amount = '';
        $product_discount = $this->calcDiscount($product);
        $total_discounts_tax_excl = $product_discount['total_discounts_tax_excl'];
        $discounts_tax_amount = $product_discount['discounts_tax_amount'];
        $total_wrapping_tax_incl = $order->total_wrapping_tax_incl;
        $total_wrapping_tax_excl = $order->total_wrapping_tax_excl;
        $wrapping_tax_amount = 0;
        if ($total_wrapping_tax_incl != 0) {
            $wrapping_tax_amount = $total_wrapping_tax_incl - $total_wrapping_tax_excl;
        }
        $total_shipping_tax_incl = $order->total_shipping_tax_incl;
        $total_shipping_without_tax = $order->total_shipping_tax_excl;
        $shipping_tax_amount = 0;
        if ($total_shipping_tax_incl != 0) {
            $shipping_tax_amount = $total_shipping_tax_incl - $total_shipping_without_tax;
        }
        $total_tax = $product_tax;
        $last_name = $address->lastname;
        $first_name = $address->firstname;
        $country = $address->country;
        $id_country = $address->id_country;
        // since 1.0.27+
        $state_name = "";
        $id_state = (int) $address->id_state;
        if (!empty($id_state)) {
            $state_name = State::getNameById($id_state);
        }

        $company = $address->company;
        $currency = new Currency($id_currency);
        $sign_currency = $currency->sign;
        $iso_currency = $currency->iso_code;
        $data = $this->getreportTaxOnly($order_id);
        if ($data != null) {
            $get_data = $data[0];
            $total_products_no_tax = $total_products_no_tax + $get_data['total_products_no_tax'];
            $product_tax = $product_tax + $get_data['product_tax'];
            $total_tax = $total_tax + $get_data['total_tax'];
            $including_ecotax_tax_excl = $including_ecotax_tax_excl + $get_data['including_ecotax_tax_excl'];
            $including_ecotax_tax_amount = $including_ecotax_tax_amount + $get_data['including_ecotax_tax_amount'];
            $total_discounts_tax_excl=$total_discounts_tax_excl+$get_data['total_discounts_tax_excl'];
            $discounts_tax_amount=$discounts_tax_amount+$get_data['discounts_tax_amount'];

            $query = 'UPDATE ' . _DB_PREFIX_ . 'ba_report_tax_only SET '
                    . 'total_products_no_tax="' . (double)$total_products_no_tax . '",'
                    . 'product_tax="' . (double)$product_tax . '",'
                    . 'including_ecotax_tax_excl="' . (double)$including_ecotax_tax_excl . '",'
                    . 'including_ecotax_tax_amount="' . (double)$including_ecotax_tax_amount . '",'
                    . 'total_discounts_tax_excl="' . (double)$total_discounts_tax_excl . '",'
                    . 'discounts_tax_amount="' . (double)$discounts_tax_amount . '",'
                    . 'total_tax="' . (double)$total_tax . '",'
                    . 'sign_currency="' . $sign_currency . '",'
                    . 'iso_currency="' . $iso_currency . '",'
                    . 'id_currency="' . $id_currency . '"'
                    . 'WHERE order_id="' . (int)$order_id . '"';
            Db::getInstance()->query($query);
        }
        if ($data == null) {
            Db::getInstance()->insert('ba_report_tax_only', array(
                'id_shop' => (int)$order->id_shop,
                'shop_name' => pSQL($shop_name),
                'order_id' => (int)$order_id,
                'order_add_date' => pSQL($order_add_date),
                'order_number' => (int)$order_number,
                'invoice_add_date' => pSQL($invoice_add_date),
                'invoice_number' => (int)$invoice_number,
                'invoice_status' => (int)$invoice_status,
                'cart_id' => (int)$cart_id,
                'delivery_date' => pSQL($delivery_date),
                'total_products_no_tax' => (double)$total_products_no_tax,
                'product_tax' => (double)$product_tax,
                'including_ecotax_tax_excl' => (double)$including_ecotax_tax_excl,
                'including_ecotax_tax_amount' => (double)$including_ecotax_tax_amount,
                'total_discounts_tax_excl' => (double)$total_discounts_tax_excl,
                'discounts_tax_amount' => (double)$discounts_tax_amount,
                'total_wrapping_tax_excl' => (double)$total_wrapping_tax_excl,
                'wrapping_tax_amount' => (double)$wrapping_tax_amount,
                'total_shipping_without_tax' => (double)$total_shipping_without_tax,
                'shipping_tax_amount' => (double)$shipping_tax_amount,
                'total_tax' => $total_tax,
                'last_name' => pSQL($last_name),
                'first_name' => pSQL($first_name),
                'country' => pSQL($country),
                'id_country' => (int)$id_country,
                'company' => pSQL($company),
                'sign_currency' => $sign_currency,
                'iso_currency' => $iso_currency,
                'id_currency' => $id_currency,
                'id_state' => $id_state,
                'state' => $state_name,
            ));
        }
        return true;
    }
    public function updateAllTaxOnly($order_id)
    {
        $query = 'SELECT * FROM ' . _DB_PREFIX_ . 'ba_report_tax_only WHERE order_id=' . (int)$order_id;
        $data = DB::getInstance()->executeS($query, true, false);
        $get_data = $data[0];
        $product_tax = $get_data['product_tax'];
        $iclecotax_tax_amount = $get_data['including_ecotax_tax_amount'];
        $dis_tax_amount = $get_data['discounts_tax_amount'];
        $wrapping_tax_amount = $get_data['wrapping_tax_amount'];
        $shipping_tax_amount = $get_data['shipping_tax_amount'];
        $total_tax = $product_tax+$iclecotax_tax_amount - $dis_tax_amount + $wrapping_tax_amount + $shipping_tax_amount;
        $query = 'UPDATE ' . _DB_PREFIX_ . 'ba_report_tax_only SET total_tax="' . (double)$total_tax . '"'
                . ' WHERE order_id=' . (int)$order_id;
        Db::getInstance()->query($query);
        return true;
    }
    public function getreportTaxOnly($order_id)
    {
        $query = 'SELECT * FROM ' . _DB_PREFIX_ . 'ba_report_tax_only WHERE order_id="' . (int)$order_id . '"';
        $data = DB::getInstance()->executeS($query, true, false);
        return $data;
    }
    public function convertMoneyTaxOnly($value, $row)
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
        $sql .= ', MIN(invoice_add_date) as min_invoice_add_date';
        $sql .= ', MAX(invoice_add_date) as max_invoice_add_date';
        $sql .= ', MIN(delivery_date) as min_delivery_date';
        $sql .= ', MAX(delivery_date) as max_delivery_date';
        $sql .= ', COUNT(DISTINCT order_number) as order_number';
        $sql .= ', COUNT(DISTINCT invoice_number) as invoice_number';
        $sql .= ', COUNT(DISTINCT invoice_status) as invoice_status';
        $sql .= ', COUNT(DISTINCT country) as country';
        $sql .= ', COUNT(DISTINCT id_country) as id_country';
        $sql .= ", COUNT(DISTINCT state) - COUNT(DISTINCT case when state='' then 1 end)";
        $sql .= " as state";
        $sql .= ', COUNT(DISTINCT company) as company';
        $sql .= ", SUM(CASE WHEN company IS NOT NULL AND company !='' THEN 1 ELSE 0 END) as notempty_c";
        $sql .= ', COUNT(DISTINCT iso_currency) as iso_currency';
        $sql .= ' FROM ' . _DB_PREFIX_ . 'ba_report_tax_only ';
        $sql .= $this->setWhereClause($helper);
        $item1 = DB::getInstance()->getRow($sql, false);
        if (empty($item1)) {
            return false;
        }
        if (empty($item1['notempty_c'])) {
            $item1['company'] = 0;
        }
        $sql = 'SELECT id_currency';
        $sql .= ', SUM(total_products_no_tax) as total_products_no_tax';
        $sql .= ', SUM(product_tax) as product_tax';
        $sql .= ', SUM(including_ecotax_tax_excl) as including_ecotax_tax_excl';
        $sql .= ', SUM(including_ecotax_tax_amount) as including_ecotax_tax_amount';
        $sql .= ', SUM(total_discounts_tax_excl) as total_discounts_tax_excl';
        $sql .= ', SUM(discounts_tax_amount) as discounts_tax_amount';
        $sql .= ', SUM(total_wrapping_tax_excl) as total_wrapping_tax_excl';
        $sql .= ', SUM(wrapping_tax_amount) as wrapping_tax_amount';
        $sql .= ', SUM(total_shipping_without_tax) as total_shipping_without_tax';
        $sql .= ', SUM(shipping_tax_amount) as shipping_tax_amount';
        $sql .= ', SUM(total_tax_5) as total_tax_5';
        $sql .= ', SUM(total_tax_9975) as total_tax_9975';
        $sql .= ', SUM(total_tax) as total_tax';
        $sql .= ' FROM ' . _DB_PREFIX_ . 'ba_report_tax_only ';
        $sql .= $this->setWhereClause($helper);
        $sql .= ' GROUP BY id_currency ';
        $item2 = DB::getInstance()->executeS($sql, true, false);
        if (empty($item2)) {
            return false;
        }
        $data = array(
            'total_products_no_tax' => 0,
            'product_tax' => 0,
            'including_ecotax_tax_excl' => 0,
            'including_ecotax_tax_amount' => 0,
            'total_discounts_tax_excl' => 0,
            'discounts_tax_amount' => 0,
            'total_wrapping_tax_excl' => 0,
            'wrapping_tax_amount' => 0,
            'total_shipping_without_tax' => 0,
            'shipping_tax_amount' => 0,
            'total_tax_5' => 0,
            'total_tax_9975' => 0,
            'total_tax' => 0,
        );
        $default_currency = (int) Configuration::get('PS_CURRENCY_DEFAULT');
        $c_to = new Currency($default_currency);
        foreach ($item2 as $value) {
            $id_currency = (int) $value['id_currency'];
            $c_from = new Currency($id_currency);
            $data['total_products_no_tax'] += Tools::convertPriceFull($value['total_products_no_tax'], $c_from, $c_to);
            $data['product_tax'] += Tools::convertPriceFull($value['product_tax'], $c_from, $c_to);
            $i = $value['including_ecotax_tax_excl'];
            $data['including_ecotax_tax_excl'] += Tools::convertPriceFull($i, $c_from, $c_to);
            $i = $value['including_ecotax_tax_amount'];
            $data['including_ecotax_tax_amount'] += Tools::convertPriceFull($i, $c_from, $c_to);
            $t = $value['total_discounts_tax_excl'];
            $data['total_discounts_tax_excl'] += Tools::convertPriceFull($t, $c_from, $c_to);
            $data['discounts_tax_amount'] += Tools::convertPriceFull($value['discounts_tax_amount'], $c_from, $c_to);
            $t = $value['total_wrapping_tax_excl'];
            $data['total_wrapping_tax_excl'] += Tools::convertPriceFull($t, $c_from, $c_to);
            $data['wrapping_tax_amount'] += Tools::convertPriceFull($value['wrapping_tax_amount'], $c_from, $c_to);
            $t = $value['total_shipping_without_tax'];
            $data['total_shipping_without_tax'] += Tools::convertPriceFull($t, $c_from, $c_to);
            $data['shipping_tax_amount'] += Tools::convertPriceFull($value['shipping_tax_amount'], $c_from, $c_to);
            $data['total_tax'] += Tools::convertPriceFull($value['total_tax'], $c_from, $c_to);
            $data['total_tax_5'] += Tools::convertPriceFull($value['total_tax_5'], $c_from, $c_to);
            $data['total_tax_9975'] += Tools::convertPriceFull($value['total_tax_9975'], $c_from, $c_to);
        }
        $data = array_merge($item1, $data);
        $summary = array();
        $summary['id_shop'] = array(
            $this->l('#ID shop'),
            number_format($data['id_shop'])
        );
        $summary['shop_name'] = array(
            $this->l('#Shop name'),
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
        $summary['products_name'] = array(
            $this->l('Products'),
            $this->l('-')
        );
        $summary['total_products_no_tax'] = array(
            $this->l('Total Products No Tax'),
            Tools::displayPrice($data['total_products_no_tax'], $c_to)
        );
        $summary['product_tax'] = array(
            $this->l('Product Tax'),
            Tools::displayPrice($data['product_tax'], $c_to)
        );
        $summary['including_ecotax_tax_excl'] = array(
            $this->l('Including Ecotax Tax Excl'),
            Tools::displayPrice($data['including_ecotax_tax_excl'], $c_to)
        );
        $summary['including_ecotax_tax_amount'] = array(
            $this->l('Including Ecotax Tax Amount'),
            Tools::displayPrice($data['including_ecotax_tax_amount'], $c_to)
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
        $summary['total_shipping_without_tax'] = array(
            $this->l('Total Shipping Without Tax'),
            Tools::displayPrice($data['total_shipping_without_tax'], $c_to)
        );
        $summary['shipping_tax_amount'] = array(
            $this->l('Shipping Tax Amount'),
            Tools::displayPrice($data['shipping_tax_amount'], $c_to)
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
        $summary['last_name'] = array(
            $this->l('Last Name'),
            $this->l('-')
        );
        $summary['first_name'] = array(
            $this->l('First Name'),
            $this->l('-')
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
        $summary['company'] = array(
            $this->l('#Company'),
            number_format($data['company'])
        );
        $summary['iso_currency'] = array(
            $this->l('#Currency ISO'),
            number_format($data['iso_currency'])
        );
        $this->smarty->assign('summary', $summary);
        $this->smarty->assign('fields_list', $fields_list);
        return $this->shortDisplay('views/templates/admin/summary_table.tpl');
    }
    public function countData($helper)
    {
        $sql = 'SELECT count(*) FROM ' . _DB_PREFIX_ . 'ba_report_tax_only '
                . $this->setWhereClause($helper);
        $data = DB::getInstance()->getValue($sql, false);
        return $data;
    }
}
