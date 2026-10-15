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

class BaViewBrand extends ReportSale
{
    private $orderby;
    private $orderway;
    private $ps_searchable_fields = array('id_shop','shop_name', 'id_cart',
        'id_order',
        'order_number', 'invoice_number',
        'invoice_status', 'manufacturer_id',
        'manufacturer_name', 'total_quantity',
        'total_discounts_tax_excl', 'total_products_no_tax',
        'including_ecotax_tax_excl', 'total_cost',
        'gross_profit', 'gross_margin',
        'net_profit', 'net_margin',
        'of_total_sales', 'of_total_gross_profits',
        'of_total_net_profits');
    public function setWhereClauseDate($helper)
    {
        $sql = null;
        $orderDateArr_order_date = Tools::getValue($helper->list_id . "Filter_order_add_date", null);
        $orderDateArr_invoice_date = Tools::getValue($helper->list_id . "Filter_invoice_add_date", null);
        $orderDateArr_delivery_date = Tools::getValue($helper->list_id . "Filter_delivery_date", null);
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
        $helper_list_id = $this->name . 'ba_report_brand';
        foreach ($this->ps_searchable_fields as $search_field) {
            $this->context->cookie->{$helper_list_id . 'Filter_' . $search_field} = null;
        }
        Configuration::updateValue($this->name.'_brand_where', null);
    }
    public function viewbrandlist()
    {
        $helper = new HelperList();
        $fields_list = array(
            'id_shop' => array(
                'title' => $this->l('ID Shop'),
                'type' => 'text'
            ),
            'shop_name' => array(
                'title' => $this->l('Shop name'),
                'type' => 'text'
            ),
            'manufacturer_id' => array(
                'title' => $this->l('Manufacturer ID'),
                'type' => 'text'
            ),
            'manufacturer_name' => array(
                'title' => $this->l('Manufacturer Name'),
                'type' => 'text'
            ),
            'total_quantity' => array(
                'title' => $this->l('Total Sold'),
                'type' => 'text'
            ),
            'total_discounts_tax_excl' => array(
                'title' => $this->l('Total Discounts Tax Excl'),
                'type' => 'text',
                'callback' => 'convertMoneyBrand',
                'callback_object' => $this
            ),
            'total_products_no_tax' => array(
                'title' => $this->l('Total Products No Tax'),
                'type' => 'text',
                'callback' => 'convertMoneyBrand',
                'callback_object' => $this
            ),
            'including_ecotax_tax_excl' => array(
                'title' => $this->l('Including Ecotax Tax Excl'),
                'type' => 'text',
                'callback' => 'convertMoneyBrand',
                'callback_object' => $this
            ),
            'total_cost' => array(
                'title' => $this->l('Total Cost'),
                'type' => 'text',
                'callback' => 'convertMoneyBrand',
                'callback_object' => $this
            ),
            'gross_profit' => array(
                'title' => $this->l('Gross Profit'),
                'type' => 'text',
                'callback' => 'convertMoneyBrand',
                'callback_object' => $this
            ),
            'gross_margin' => array(
                'title' => $this->l('Gross Margin'),
                'type' => 'text',
                'callback' => 'convertPercentBrand',
                'callback_object' => $this
            ),
            'net_profit' => array(
                'title' => $this->l('Net Profit'),
                'type' => 'text',
                'callback' => 'convertMoneyBrand',
                'callback_object' => $this
            ),
            'net_margin' => array(
                'title' => $this->l('Net Margin'),
                'type' => 'text',
                'callback' => 'convertPercentBrand',
                'callback_object' => $this
            ),
            'of_total_sales' => array(
                'title' => $this->l('% Of Total Sales'),
                'type' => 'text',
                'callback' => 'convertPercentBrand',
                'callback_object' => $this
            ),
            'of_total_gross_profits' => array(
                'title' => $this->l('% Of Total Gross Profits'),
                'type' => 'text',
                'callback' => 'convertPercentBrand',
                'callback_object' => $this
            ),
            'of_total_net_profits' => array(
                'title' => $this->l('% Of Total Net Profits'),
                'type' => 'text',
                'callback' => 'convertPercentBrand',
                'callback_object' => $this
            )
        );
        $helper->shopLinkType = '';
        $helper->identifier = 'id_report';
        $helper->show_toolbar = true;
        $helper->simple_header = false;
        $helper->no_link = true;
        $helper->title = $this->l('Report Manufacturers');
        $helper->table = $this->name . 'ba_report_brand';
        $helper->list_id = $this->name . 'ba_report_brand';
        $this->orderby = pSQL(Tools::getValue($helper->list_id . "Orderby", "manufacturer_id"));
        $this->orderway = pSQL(Tools::getValue($helper->list_id . "Orderway", "ASC"));
        $helper->orderBy = $this->orderby;
        $helper->orderWay = Tools::strtoupper($this->orderway);
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $c1 = AdminController::$currentIndex;
        $n1 = $this->name;
        $ad1 = Tools::getAdminTokenLite('AdminModules');
        $rp1 = "reportsaleba_report_brandOrderby";
        $rp2 = "reportsaleba_report_brandOrderway";
        $tor1 = $this->orderby;
        $tor2 = $this->orderway;
        $mn1 = "csv=manufacturers";
        $ts1 = "task=manufacturers";
        $helper->toolbar_btn['export'] = array(
            'href' => $c1.'&configure='.$n1.'&token='.$ad1.'&'.$ts1.'&'.$rp1.'='.$tor1.'&'.$rp2.'='.$tor2.'&'.$mn1.'',
            'desc' => $this->l('export csv')
        );
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name . '&task=manufacturers';
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
        $rows = $this->selectdatabrand($helper, $start, $selected_pagination);
        $table_helper = $helper->generateList($rows, $fields_list);
        $table_helper .= $this->getSummaryBlock($helper, $fields_list);
        return $table_helper;
    }
    public function getSummaryBlock($helper, $fields_list)
    {
        $sql = 'SELECT COUNT(DISTINCT id_shop) as id_shop';
        $sql .= ', COUNT(DISTINCT shop_name) as shop_name';
        $sql .= ', COUNT(DISTINCT manufacturer_id) as manufacturer_id';
        $sql .= ', COUNT(DISTINCT manufacturer_name) as manufacturer_name';
        $sql .= ', SUM(total_quantity) as total_quantity';
        $sql .= ' FROM ' . _DB_PREFIX_ . 'ba_report_brand ';
        $sql .= $this->setWhereClause($helper);
        $item1 = DB::getInstance()->getRow($sql, false);
        if (empty($item1)) {
            return false;
        }
        $sql = 'SELECT manufacturer_id';
        $sql .= ', SUM(total_discounts_tax_excl) as total_discounts_tax_excl';
        $sql .= ', SUM(total_products_no_tax) as total_products_no_tax';
        $sql .= ', SUM(including_ecotax_tax_excl) as including_ecotax_tax_excl';
        $sql .= ', SUM(total_cost) as total_cost';
        $sql .= ', SUM(gross_profit) as gross_profit';
        $sql .= ', SUM(net_profit) as net_profit';
        $sql .= ', SUM(cumulative_of_total_sales) as cumulative_of_total_sales';
        $sql .= ', SUM(cumulative_of_total_gross_profits) as cumulative_of_total_gross_profits';
        $sql .= ', SUM(cumulative_of_total_net_profits) as cumulative_of_total_net_profits';
        $sql .= ', SUM(of_total_sales) as of_total_sales';
        $sql .= ', SUM(of_total_gross_profits) as of_total_gross_profits';
        $sql .= ', SUM(of_total_net_profits) as of_total_net_profits';
        $sql .= ' FROM ' . _DB_PREFIX_ . 'ba_report_brand ';
        $sql .= $this->setWhereClause($helper);
        $item2 = DB::getInstance()->getRow($sql, false);
        if (empty($item2)) {
            return false;
        }
        $data = array(
            'total_discounts_tax_excl' => $item2['total_discounts_tax_excl'],
            'total_products_no_tax' => $item2['total_products_no_tax'],
            'including_ecotax_tax_excl' => $item2['including_ecotax_tax_excl'],
            'total_cost' => $item2['total_cost'],
            'gross_profit' => $item2['gross_profit'],
            'gross_margin' => 0,
            'net_profit' => $item2['net_profit'],
            'net_margin' => 0,
            'of_total_sales' => $item2['of_total_sales'],
            'of_total_gross_profits' => $item2['of_total_gross_profits'],
            'of_total_net_profits' => $item2['of_total_net_profits'],
        );
        $default_currency = (int) Configuration::get('PS_CURRENCY_DEFAULT');
        $c_to = new Currency($default_currency);
        // calculate % gross margin
        if ($data['total_products_no_tax'] > 0) {
            $t = (float) $data['total_products_no_tax'];
            $data['gross_margin'] = (float) $data['gross_profit'] / $t;
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
        $summary['manufacturer_id'] = array(
            $this->l('#Manufacturer ID'),
            number_format($data['manufacturer_id'])
        );
        $summary['manufacturer_name'] = array(
            $this->l('#Manufacturer Name'),
            number_format($data['manufacturer_name'])
        );
        $summary['total_quantity'] = array(
            $this->l('#Total Sold'),
            number_format($data['total_quantity'])
        );
        $summary['total_discounts_tax_excl'] = array(
            $this->l('Total Discounts Tax Excl'),
            Tools::displayPrice($data['total_discounts_tax_excl'], $c_to)
        );
        $summary['total_products_no_tax'] = array(
            $this->l('Total Products No Tax'),
            Tools::displayPrice($data['total_products_no_tax'], $c_to)
        );
        $summary['including_ecotax_tax_excl'] = array(
            $this->l('Including Ecotax Tax Excl'),
            Tools::displayPrice($data['including_ecotax_tax_excl'], $c_to)
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
        $summary['of_total_sales'] = array(
            $this->l('% Of Total Sales'),
            round($data['of_total_sales'], 2).$this->l('%')
        );
        $summary['of_total_gross_profits'] = array(
            $this->l('% Of Total Gross Profits'),
            round($data['of_total_gross_profits'], 2).$this->l('%')
        );
        $summary['of_total_net_profits'] = array(
            $this->l('% Of Total Net Profits'),
            round($data['of_total_net_profits'], 2).$this->l('%')
        );
        $this->smarty->assign('summary', $summary);
        $this->smarty->assign('fields_list', $fields_list);
        return $this->shortDisplay('views/templates/admin/summary_table.tpl');
    }
    public function selectdatabrand($helper, $start, $selected_pagination)
    {
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'ba_report_brand ';
        $where = $this->setWhereClause($helper);
        $sql .= $where;
        Configuration::updateValue($this->name.'_brand_where', $where);
        $sql .=' ORDER BY ' . pSQL($this->orderby) . ' ' . pSQL($this->orderway)
        . ' LIMIT ' . (int) $start . ', ' . (int) $selected_pagination;
        $rows = Db::getInstance()->ExecuteS($sql, true, false);
        return($rows);
    }
    public function insertreportbrand($id_order)
    {
        $order = new Order($id_order);
        $products = $order->getproducts();
        $id_currency = (int) $order->id_currency;
        $c_from = new Currency($id_currency);
        foreach ($products as &$product) {
            if (!isset($product['original_wholesale_price'])) {
                $p_id = $product['product_id'];
                $a_id = $product['product_attribute_id'];
                $product['original_wholesale_price'] = $this->getWholeSalePrice($p_id, $a_id);
            }
            $p = $this->convertProductToDefaultCurrenct($product, $c_from);
            $this->insertbrand($p, $order);
        }
        return true;
    }
    public function insertbrand($product, $order)
    {
        $manufacturer_id = (int) $product['id_manufacturer'];
        $manufacturer_name = Manufacturer::getNameById($manufacturer_id);
        $unit_price = $product['unit_price_tax_excl'];
        $original_price = (double) $product['original_product_price'];
        $shop_name = $this->getShopName((int)$order->id_shop);
        $id_cart = $order->id_cart;
        $id_order = $order->id;
        $order_add_date = $order->date_add;
        $invoice_add_date = $order->invoice_date;
        $delivery_date = $order->delivery_date;
        $invoice_number = $order->invoice_number;
        $invoice_status = '';
        $total_quantity = $product['product_quantity'];
        $total_discounts_tax_excl = '';
        /******** discount total ****/
        $product_discount = $this->calcDiscount($product);
        $total_discounts_tax_excl = $product_discount['total_discounts_tax_excl'];
        $total_products_no_tax = (double) $unit_price * (int) $total_quantity;
        /** eco TAX **/
        $ecotax_incl = $product['ecotax'];
        $ecotax_tax_rate = $product['ecotax_tax_rate'];
        $ecotax_tax_excl = ($ecotax_incl/ (1+$ecotax_tax_rate/100));
        $including_ecotax_tax_excl = $ecotax_tax_excl * $total_quantity;

        $total_cost = ($product['original_wholesale_price']) * (int) $total_quantity;
        $gross_profit = ($original_price - ($product['original_wholesale_price'])) * (int) $total_quantity;
        $net_profit = $gross_profit - $total_discounts_tax_excl;

        if ($total_products_no_tax > 0) {
            $gross_margin = ($gross_profit / $total_products_no_tax) * 100;
            $net_margin = ($net_profit / $total_products_no_tax) * 100;
        } else {
            $gross_margin = 0;
            $net_margin = 0;
        }
        $data = $this->getreportBrand($manufacturer_id, (int)$order->id_shop);
        if ($data != null) {
            $getdata = $data[0];
            $total_quantity = $total_quantity + $getdata['total_quantity'];
            $total_discounts_tax_excl = $total_discounts_tax_excl + $getdata['total_discounts_tax_excl'];
            $total_products_no_tax = $total_products_no_tax + $getdata['total_products_no_tax'];
            $including_ecotax_tax_excl = $including_ecotax_tax_excl + $getdata['including_ecotax_tax_excl'];
            $total_cost = $total_cost + $getdata['total_cost'];
            $gross_profit = $gross_profit + $getdata['gross_profit'];
            $net_profit = $net_profit + $getdata['net_profit'];
            $query = 'UPDATE ' . _DB_PREFIX_ . 'ba_report_brand SET '
                    .'total_quantity="' . (int)$total_quantity . '",'
                    . 'total_products_no_tax="' . (double)$total_products_no_tax . '",'
                    .'including_ecotax_tax_excl="' . (double)$including_ecotax_tax_excl . '",'
                    .'total_cost="' . (double)$total_cost . '",'
                    .'gross_profit="' . (double)$gross_profit . '",'
                    . 'net_profit="' . (double)$net_profit . '",'
                    .'total_discounts_tax_excl="' . (double)$total_discounts_tax_excl . '" WHERE '
                    .'manufacturer_id = ' . (int) $manufacturer_id
                    .' AND id_shop = '.(int)$order->id_shop;
            Db::getInstance()->query($query);
            $this->updateAllBrandReport();
        }
        if ($data == null) {
            Db::getInstance()->insert('ba_report_brand', array(
                'id_shop' => (int)$order->id_shop,
                'shop_name' => pSQL($shop_name),
                'id_cart' => (int)$id_cart,
                'id_order' => (int)$id_order,
                'order_add_date' => pSQL($order_add_date),
                'invoice_add_date' => pSQL($invoice_add_date),
                'delivery_date' => pSQL($delivery_date),
                'order_number' => '',
                'invoice_number' => (int)$invoice_number,
                'invoice_status' => (int)$invoice_status,
                'manufacturer_id' => (int)$manufacturer_id,
                'manufacturer_name' => pSQL($manufacturer_name),
                'total_quantity' => (int)$total_quantity,
                'total_discounts_tax_excl' => (double)$total_discounts_tax_excl,
                'total_products_no_tax' => (double)$total_products_no_tax,
                'including_ecotax_tax_excl' => (double)$including_ecotax_tax_excl,
                'total_cost' => (double)$total_cost,
                'gross_profit' => (double)$gross_profit,
                'gross_margin' => (double)$gross_margin,
                'net_profit' => (double)$net_profit,
                'net_margin' => (double)$net_margin
            ));
            $this->updateAllBrandReport();
        }
        return true;
    }
    public function gettotalproducnotax()
    {
        $query = 'SELECT SUM(total_products_no_tax) FROM ' . _DB_PREFIX_ . 'ba_report_brand';
        $data = (double) Db::getInstance()->getValue($query, false);
        return $data;
    }
    public function gettotalgrossprofits()
    {
        $query = 'SELECT SUM(gross_profit) FROM ' . _DB_PREFIX_ . 'ba_report_brand';
        $data = (double) Db::getInstance()->getValue($query, false);
        return $data;
    }
    public function gettotalnetprofits()
    {
        $query = 'SELECT SUM(net_profit) FROM ' . _DB_PREFIX_ . 'ba_report_brand';
        $data = (double) Db::getInstance()->getValue($query, false);
        return $data;
    }
    public function getreportBrand($manufacturer_id, $id_shop)
    {
        $query = 'SELECT * FROM ' . _DB_PREFIX_ . 'ba_report_brand WHERE '
                .'manufacturer_id = ' . (int) $manufacturer_id;
        $query .= ' AND id_shop='.(int)$id_shop;
        $data = DB::getInstance()->executeS($query, true, false);
        return $data;
    }
    public function updateAllBrandReport()
    {
        $query = 'SELECT * FROM ' . _DB_PREFIX_ . 'ba_report_brand';
        $data = DB::getInstance()->executeS($query, true, false);
        $n = count($data);
        for ($i = 0; $i < $n; $i++) {
            $get_data = $data[$i];
            $of_total_sales = ((double)$get_data['total_products_no_tax']/(double)$this->gettotalproducnotax())*100;
            $of_total_gross_profits =((double)$get_data['gross_profit']/(double) $this->gettotalgrossprofits())*100;
            $of_total_net_profits = ((double) $get_data['net_profit'] / (double) $this->gettotalnetprofits()) * 100;

            $total_no_tx =(double) $get_data['total_products_no_tax'];
            if ($total_no_tx > 0) {
                $net_margin = ($get_data['net_profit'] / $total_no_tx) * 100;
                $gross_margin = ($get_data['gross_profit'] / $total_no_tx) * 100;
            } else {
                $net_margin = 0;
                $gross_margin = 0;
            }
            $query = 'UPDATE ' . _DB_PREFIX_ . 'ba_report_brand SET '
                    .'of_total_sales="' . (double)$of_total_sales . '",'
                    . 'of_total_gross_profits="' . (double)$of_total_gross_profits . '",'
                    .'of_total_net_profits="' . (double)$of_total_net_profits . '",'
                    . 'net_margin="' . (double)$net_margin . '",'
                    .'gross_margin="' . (double)$gross_margin . '" '
                    .'WHERE id_report=' . (int)$get_data['id_report'];
            Db::getInstance()->query($query);
        }
        return true;
    }
    public function convertPercentBrand($value)
    {
        $data_view = round($value, 2) . '%';
        return $data_view;
    }
    public function convertMoneyBrand($value)
    {
        $tool = new Tools();
        $a = round($value, 2);
        $data_view = $tool->displayPrice($a);
        return $data_view;
    }
    public function countData($helper)
    {
        $sql = 'SELECT count(*) FROM ' . _DB_PREFIX_ . 'ba_report_brand '
                . $this->setWhereClause($helper);
        $data = DB::getInstance()->getValue($sql, false);
        return $data;
    }
}
