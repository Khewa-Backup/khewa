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

class BaViewStoCredit extends ReportSale
{
    private $orderby;
    private $orderway;
    private $ps_searchable_fields = array('shop_name', 'id_cart',
        'order_id', 'order_number', 'invoice_number',
        'invoice_status', 'credit_slip_id', 'first_name',
        'last_name',
        'payment_method', 'total_products_no_tax',
        'products_tax', 'total_shipping_without_tax',
        'shipping_tax_amount', 'total_no_tax',
        'total_tax', 'total_tax_incl','reference','state','country','id_country');
    public function setWhereClauseDate($helper)
    {
        $sql = null;
        $credit_slip_date = Tools::getValue($helper->list_id . "Filter_credit_slip_date", null);
        if ($credit_slip_date !== null) {
            $d = $credit_slip_date;
            $this->context->cookie->{$helper->list_id.'Filter_credit_slip_date'} = serialize($d);
        }
        if (!empty($credit_slip_date[0])) {
            $sql.=" AND credit_slip_date >= '" . pSQL($credit_slip_date[0]) . " 00:00:00' ";
        }
        if (!empty($credit_slip_date[1])) {
            $sql.=" AND credit_slip_date <= '" . pSQL($credit_slip_date[1]) . " 23:59:59' ";
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
        $helper_list_id = $this->name . 'ba_report_storecredit';
        foreach ($this->ps_searchable_fields as $search_field) {
            $this->context->cookie->{$helper_list_id . 'Filter_' . $search_field} = null;
        }
        Configuration::updateValue($this->name.'_credit_where', null);
    }
    public function viewstocreditlist()
    {
        $helper = new HelperList();
        $fields_list = array(
            'shop_name' => array(
                'title' => $this->l('Shop name'),
                'type' => 'text'
            ),
            'credit_slip_id' => array(
                'title' => $this->l('Credit Slip ID'),
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
            'first_name' => array(
                'title' => $this->l('First Name'),
                'type' => 'text'
            ),
            'last_name' => array(
                'title' => $this->l('Last Name'),
                'type' => 'text'
            ),
            'credit_slip_date' => array(
                'title' => $this->l('Credit Slip Date'),
                'type' => 'date'
            ),
            'payment_method' => array(
                'title' => $this->l('Payment Method'),
                'type' => 'text'
            ),
            'total_products_no_tax' => array(
                'title' => $this->l('Total Products No Tax'),
                'type' => 'text',
                'callback' => 'convertMoneyStocredit',
                'callback_object' => $this
            ),
            'products_tax' => array(
                'title' => $this->l('Products Tax'),
                'type' => 'text',
                'callback' => 'convertMoneyStocredit',
                'callback_object' => $this
            ),
            'total_shipping_without_tax' => array(
                'title' => $this->l('Total Shipping Without Tax'),
                'type' => 'text',
                'callback' => 'convertMoneyStocredit',
                'callback_object' => $this
            ),
            'shipping_tax_amount' => array(
                'title' => $this->l('Shipping Tax Amount'),
                'type' => 'text',
                'callback' => 'convertMoneyStocredit',
                'callback_object' => $this
            ),
            'total_no_tax' => array(
                'title' => $this->l('Total No Tax'),
                'type' => 'text',
                'callback' => 'convertMoneyStocredit',
                'callback_object' => $this
            ),
            'total_tax' => array(
                'title' => $this->l('Total Tax'),
                'type' => 'text',
                'callback' => 'convertMoneyStocredit',
                'callback_object' => $this
            ),
            'total_tax_incl' => array(
                'title' => $this->l('Total Tax Incl'),
                'type' => 'text',
                'callback' => 'convertMoneyStocredit',
                'callback_object' => $this
            ),
            'iso_currency' => array(
                'title' => $this->l('Currency ISO'),
                'type' => 'text'
            ),
            'country' => array(
                'title' => $this->l('Country'),
                'type' => 'text'
            ),
            'state' => array(
                'title' => $this->l('State'),
                'type' => 'text'
            ),
        );
        $helper->shopLinkType = '';
        $helper->identifier = 'id_report';
        $helper->show_toolbar = true;
        $helper->simple_header = false;
        $helper->no_link = true;
        $helper->title = $this->l('Report Credit Slips');
        $helper->table = $this->name . 'ba_report_storecredit';
        $helper->list_id = $this->name . 'ba_report_storecredit';
        $this->orderby = pSQL(Tools::getValue($helper->list_id . "Orderby", "credit_slip_id"));
        $this->orderway = pSQL(Tools::getValue($helper->list_id . "Orderway", "ASC"));
        $helper->orderBy = $this->orderby;
        $helper->orderWay = Tools::strtoupper($this->orderway);
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $c1 = AdminController::$currentIndex;
        $n1 = $this->name;
        $ad1 = Tools::getAdminTokenLite('AdminModules');
        $o1 = "reportsaleba_report_storecreditOrderby";
        $o2 = "reportsaleba_report_storecreditOrderway";
        $od1 = $this->orderby;
        $od2 = $this->orderway;
        $l1 = "csv=creditslips";
        $l2 = "task=creditslips";
        $helper->toolbar_btn['export'] = array(
            'href' => $c1. '&configure='.$n1.'&token='.$ad1.'&'.$l2.'&'.$o1.'='.$od1.'&'.$o2.'='.$od2.'&'.$l1.'',
            'desc' => $this->l('export csv')
        );
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name . '&task=creditslips';
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
        $rows = $this->selectdatastocredit($helper, $start, $selected_pagination);
        $table_helper = $helper->generateList($rows, $fields_list);
        $table_helper .= $this->getSummaryBlock($helper, $fields_list);
        return $table_helper;
    }
    public function getSummaryBlock($helper, $fields_list)
    {
        $sql = 'SELECT COUNT(DISTINCT id_shop) as id_shop';
        $sql .= ', COUNT(DISTINCT shop_name) as shop_name';
        $sql .= ", COUNT(DISTINCT credit_slip_id) - COUNT(DISTINCT case when credit_slip_id=0 then 1 end)";
        $sql .= " as credit_slip_id";
        $sql .= ", COUNT(DISTINCT id_order) - COUNT(DISTINCT case when order_id=0 then 1 end) as id_order";
        $sql .= ", COUNT(DISTINCT reference) as reference";
        $sql .= ", MIN(credit_slip_date) as min_credit_slip_date";
        $sql .= ", MAX(credit_slip_date) as max_credit_slip_date";
        $sql .= ", COUNT(DISTINCT payment_method) - COUNT(DISTINCT case when payment_method='' then 1 end)";
        $sql .= " as payment_method";
        $sql .= ", COUNT(DISTINCT iso_currency) - COUNT(DISTINCT case when iso_currency='' then 1 end) as iso_currency";
        $sql .= ', COUNT(DISTINCT country) as country';
        $sql .= ', COUNT(DISTINCT id_country) as id_country';
        $sql .= ", COUNT(DISTINCT state) - COUNT(DISTINCT case when state='' then 1 end)";
        $sql .= " as state";
        $sql .= ' FROM ' . _DB_PREFIX_ . 'ba_report_store_credit ';
        $sql .= $this->setWhereClause($helper);
        $item1 = DB::getInstance()->getRow($sql, false);
        if (empty($item1)) {
            return false;
        }
        $sql = 'SELECT id_currency';
        $sql .= ', SUM(total_products_no_tax) as total_products_no_tax';
        $sql .= ', SUM(products_tax) as products_tax';
        $sql .= ', SUM(total_shipping_without_tax) as total_shipping_without_tax';
        $sql .= ', SUM(shipping_tax_amount) as shipping_tax_amount';
        $sql .= ', SUM(total_no_tax) as total_no_tax';
        $sql .= ', SUM(total_tax) as total_tax';
        $sql .= ', SUM(total_tax_incl) as total_tax_incl';
        $sql .= ' FROM ' . _DB_PREFIX_ . 'ba_report_store_credit ';
        $sql .= $this->setWhereClause($helper);
        $sql .= ' GROUP BY id_currency ';

        $item2 = DB::getInstance()->executeS($sql, true, false);
        if (empty($item2)) {
            return false;
        }
        $data = array(
            'total_products_no_tax' => 0,
            'products_tax' => 0,
            'total_shipping_without_tax' => 0,
            'shipping_tax_amount' => 0,
            'total_no_tax' => 0,
            'total_tax' => 0,
            'total_tax_incl' => 0,
        );
        $default_currency = (int) Configuration::get('PS_CURRENCY_DEFAULT');
        $c_to = new Currency($default_currency);
        // Convert all amount to default currency before calculate
        foreach ($item2 as $value) {
            $id_currency = (int) $value['id_currency'];
            $c_from = new Currency($id_currency);
            $data['total_products_no_tax'] += Tools::convertPriceFull($value['total_products_no_tax'], $c_from, $c_to);
            $data['products_tax'] += Tools::convertPriceFull($value['products_tax'], $c_from, $c_to);
            $t = $value['total_shipping_without_tax'];
            $data['total_shipping_without_tax'] += Tools::convertPriceFull($t, $c_from, $c_to);
            $data['shipping_tax_amount'] += Tools::convertPriceFull($value['shipping_tax_amount'], $c_from, $c_to);
            $data['total_no_tax'] += Tools::convertPriceFull($value['total_no_tax'], $c_from, $c_to);
            $data['total_tax'] += Tools::convertPriceFull($value['total_tax'], $c_from, $c_to);
            $data['total_tax_incl'] += Tools::convertPriceFull($value['total_tax_incl'], $c_from, $c_to);
        }
        $data = array_merge($item1, $data);
        $summary = array();
        $summary['shop_name'] = array(
            $this->l('#Shop name'),
            number_format($data['shop_name'])
        );
        $summary['credit_slip_id'] = array(
            $this->l('#Credit Slip ID'),
            number_format($data['credit_slip_id'])
        );
        $summary['id_order'] = array(
            $this->l('#ID Order'),
            number_format($data['id_order'])
        );
        $summary['reference'] = array(
            $this->l('#Reference'),
            number_format($data['reference'])
        );
        $summary['first_name'] = array(
            $this->l('First Name'),
            $this->l('-')
        );
        $summary['last_name'] = array(
            $this->l('Last Name'),
            $this->l('-')
        );
        $summary['credit_slip_date'] = array(
            $this->l('Credit Date'),
            $this->l('From: ').Tools::displayDate($data['min_credit_slip_date']),
            $this->l('To: ').Tools::displayDate($data['max_credit_slip_date']),
        );
        $summary['payment_method'] = array(
            $this->l('#Payment Method'),
            number_format($data['payment_method'])
        );
        $summary['total_products_no_tax'] = array(
            $this->l('Total Products No Tax'),
            Tools::displayPrice($data['total_products_no_tax'], $c_to)
        );
        $summary['products_tax'] = array(
            $this->l('Products Tax'),
            Tools::displayPrice($data['products_tax'], $c_to)
        );
        $summary['total_shipping_without_tax'] = array(
            $this->l('Total Shipping Without Tax'),
            Tools::displayPrice($data['total_shipping_without_tax'], $c_to)
        );
        $summary['shipping_tax_amount'] = array(
            $this->l('Shipping Tax Amount'),
            Tools::displayPrice($data['shipping_tax_amount'], $c_to)
        );
        $summary['total_no_tax'] = array(
            $this->l('Total No Tax'),
            Tools::displayPrice($data['total_no_tax'], $c_to)
        );
        $summary['total_tax'] = array(
            $this->l('Total Tax'),
            Tools::displayPrice($data['total_tax'], $c_to)
        );
        $summary['total_tax_incl'] = array(
            $this->l('Total Tax Incl'),
            Tools::displayPrice($data['total_tax_incl'], $c_to)
        );
        $summary['iso_currency'] = array(
            $this->l('#Currency ISO'),
            number_format($data['iso_currency'])
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
        $this->smarty->assign('summary', $summary);
        $this->smarty->assign('fields_list', $fields_list);
        return $this->shortDisplay('views/templates/admin/summary_table.tpl');
    }
    public function selectdatastocredit($helper, $start, $selected_pagination)
    {
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'ba_report_store_credit ';
        $where = $this->setWhereClause($helper);
        $sql .= $where;
        Configuration::updateValue($this->name.'_credit_where', $where);
        $sql.=' ORDER BY ' . pSQL($this->orderby) . ' ' . pSQL($this->orderway)
        . ' LIMIT ' . (int) $start . ', ' . (int) $selected_pagination;
        $rows = Db::getInstance()->executeS($sql, true, false);
        return($rows);
    }
    public function insertreportcredit($id_order)
    {
        $date_to = Configuration::get('SC_credit_date_to') . " 23:59:59";
        $date_from = Configuration::get('SC_credit_date_from') . " 00:00:00";
        $sql_order_slip='SELECT * FROM '._DB_PREFIX_.'order_slip WHERE id_order = '.(int)$id_order;
        if (Configuration::get('SC_credit_date_from')!= null) {
            $sql_order_slip.=' AND date_add >= "'.$date_from.'"';
        }
        if (Configuration::get('SC_credit_date_to')!= null) {
            $sql_order_slip.=' AND date_add <= "'.$date_to.'"';
        }
        $order_slip=Db::getInstance()->executeS($sql_order_slip, true, false);
        if (count($order_slip)!=0) {
            for ($i=0; $i < count($order_slip); $i++) {
                $slip_data=$order_slip[$i];
                $this->insertcredit($slip_data, $id_order);
            }
        }
        return true;
    }
    public function insertcredit($slip_data, $id_order)
    {
        $order=new Order($id_order);
        $id_currency = $order->id_currency;
        $shop_name=$this->getShopName((int)$order->id_shop);
        $total_products_tax_excl=$slip_data['total_products_tax_excl'];
        $total_products_tax_incl=$slip_data['total_products_tax_incl'];
        $products_tax=$total_products_tax_incl-$total_products_tax_excl;
        $total_shipping_without_tax=$slip_data['total_shipping_tax_excl'];
        $shipping_tax_amount=$slip_data['total_shipping_tax_incl']-$total_shipping_without_tax;
        $total_no_tax=$total_products_tax_excl+$total_shipping_without_tax;
        $total_tax=$products_tax+$shipping_tax_amount;
        $total_tax_incl=$total_no_tax+$total_tax;

        $currency = new Currency($id_currency);
        $sign_currency = $currency->sign;
        $iso_currency = $currency->iso_code;
        $customer = new Customer($order->id_customer);
        $reference = $order->reference;
        // since 1.0.27+
        $address = new Address($order->id_address_invoice);
        $country = $address->country;
        $id_country = $address->id_country;
        $state_name = "";
        $id_state = (int) $address->id_state;
        if (!empty($id_state)) {
            $state_name = State::getNameById($id_state);
        }
        Db::getInstance()->insert('ba_report_store_credit', array(
            'shop_name' => pSQL($shop_name),
            'id_shop' => (int) $order->id_shop,
            'id_cart' => $order->id_cart,
            'id_order' => (int)$id_order,
            'order_id' => (int)$id_order,
            'order_add_date' =>'' ,
            'invoice_add_date' =>'' ,
            'delivery_date' => '',
            'order_number' => '',
            'invoice_number' =>'' ,
            'invoice_status' => '',
            'credit_slip_id' =>(int)$slip_data['id_order_slip'],
            'last_name' => pSQL($customer->lastname),
            'first_name' => pSQL($customer->firstname),
            'order_invoice_date' =>pSQL(''),
            'credit_slip_date' => pSQL($slip_data['date_add']),
            'payment_method' => pSQL($order->payment),
            'total_products_no_tax' =>(double) $total_products_tax_excl,
            'products_tax' => (double)$products_tax,
            'total_shipping_without_tax' =>(double)$total_shipping_without_tax,
            'shipping_tax_amount' => (double)$shipping_tax_amount,
            'total_no_tax' =>(double)$total_no_tax,
            'total_tax' => (double)$total_tax,
            'total_tax_incl' => (double)$total_tax_incl,
            'sign_currency' => $sign_currency,
            'iso_currency' => $iso_currency,
            'id_currency' => $id_currency,
            'reference' => pSQL($reference),
            'country' => pSQL($country),
            'id_country' => pSQL($id_country),
            'id_state' => pSQL($id_state),
            'state' => pSQL($state_name),
        ));
        return true;
    }
    public function updateAllStoreCredit($order_id)
    {
        $query = 'SELECT * FROM ' . _DB_PREFIX_ . 'ba_report_store_credit WHERE id_order=' . (int) $order_id;
        $data = DB::getInstance()->executeS($query, true, false);
        $get_data = $data[0];
        $products_tax = $get_data['products_tax'];
        $shipping_tax_amount = $get_data['shipping_tax_amount'];
        $total_no_tax=$get_data['total_products_no_tax']+$get_data['total_shipping_without_tax'];
        $total_tax = $products_tax + $shipping_tax_amount;
        $total_tax_incl=$total_tax+$total_no_tax;
        $query = 'UPDATE ' . _DB_PREFIX_ . 'ba_report_store_credit SET total_tax="' . (double)$total_tax . '",'
                . 'total_tax_incl="' . (double)$total_tax_incl . '",total_no_tax="'.(double)$total_no_tax.'"'
                . ' WHERE id_order=' . (int)$order_id;
        Db::getInstance()->query($query);
        return true;
    }
    public function convertMoneyStocredit($value, $row)
    {
        $a = round($value, 2);
        $order = new Order($row['id_order']);
        return Tools::displayPrice($a, (int)$order->id_currency);
    }
    public function countData($helper)
    {
        $sql = 'SELECT count(*) FROM ' . _DB_PREFIX_ . 'ba_report_store_credit '
                . $this->setWhereClause($helper);
        $data = DB::getInstance()->getValue($sql, false);
        return $data;
    }
}
