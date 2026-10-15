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
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *   @author    Buy-addons <contact@buy-addons.com>
 *   @copyright 2007-2021 PrestaShop SA
 *   @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *   International Registered Trademark & Property of PrestaShop SA
 */

class Autoexport extends ReportSale
{
    public function funcAutoExport()
    {
        $id_shop = $this->context->shop->id;
        $gr_shop = $this->context->shop->id_shop_group;
        $basettgcronj = Configuration::get('basettgcronj', false, $gr_shop, $id_shop);
        $basettgcronj = json_decode($basettgcronj);
        if ($this->shouldBeExecuted($basettgcronj)==true) {
            $files = glob(_PS_MODULE_DIR_.'reportsale/excsv/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            };
            foreach ($basettgcronj->tableex as $vtableex) {
                if ($vtableex == 1) {
                    $this->basicdata();
                } elseif ($vtableex == 2) {
                    $this->taxesdata();
                } elseif ($vtableex == 3) {
                    $this->revenuedata();
                } elseif ($vtableex == 4) {
                    $this->alldata();
                } elseif ($vtableex == 5) {
                    $this->productdata();
                } elseif ($vtableex == 6) {
                    $this->manufacturersdata();
                } elseif ($vtableex == 7) {
                    $this->supplierdata();
                } elseif ($vtableex == 8) {
                    $this->categorydata();
                } elseif ($vtableex == 9) {
                    $this->clientdata();
                } elseif ($vtableex == 10) {
                    $this->creditslipsdata();
                }
            }
        }
    }
    protected function shouldBeExecuted($cron)
    {
        $hour = ($cron->hour == -1) ? date('H') : $cron->hour;
        $day = ($cron->day == -1) ? date('d') : $cron->day;
        $month = ($cron->month == -1) ? date('m') : $cron->month;
        $aa = strtotime('Sunday +' . $cron->day_of_week . ' days');
        $day_of_week = ($cron->day_of_week == -1) ? date('D') : date('D', $aa);

        $day = date('Y').'-'.str_pad($month, 2, '0', STR_PAD_LEFT).'-'.str_pad($day, 2, '0', STR_PAD_LEFT);
        $execution = $day_of_week.' '.$day.' '.str_pad($hour, 2, '0', STR_PAD_LEFT);
        $now = date('D Y-m-d H');
        
        return !(bool)strcmp($now, $execution);
    }
    public function basicdata()
    {
        $basic_orderby = 'id_shop';
        $basic_orderway = 'ASC';
        $basic= 'SELECT `id_shop`,
        `shop_name`,
        `order_id`,
        `order_add_date`,
        `products_name`,
        `first_name`,
        `last_name`,
        `postcode`,
        `city`,
        `country`,
        `id_country`,
        `state`,
        `total_with_tax`,
        `total_products_no_tax`,
        `products_tax`,
        `including_ecotax_tax_excl`,
        `ecluding_ecotax_tax_amount`,
        `total_shipping_without_tax`,
        `shipping_tax_amount`,
        `total_discounts_tax_excl`,
        `discounts_tax_amount`,
        `total_wrapping_tax_excl`,
        `wrapping_tax_amount`,
        `total_tax`,
        `order_state`,
        `total_cost`,
        `gross_profit_before_discounts`,
        `net_profit_tax_excl`,
        `gross_margin_before_discounts`,
        `net_margin_tax_excl`,
        `iso_currency`,
        `id_currency`
        FROM `'._DB_PREFIX_.'ba_report_basic` ORDER  BY `'. pSQL($basic_orderby) .'` '. pSQL($basic_orderway) .'';
        $result = Db::getInstance()->executeS($basic, true, false);
        foreach ($result as $key1 => $aaa) {
            $aaa;
            $a = (int)$result[$key1]['id_currency'];
            $total_with_tax = $result[$key1]['total_with_tax'];
            $total_products_no_tax = $result[$key1]['total_products_no_tax'];
            $including_ecotax_tax_excl = $result[$key1]['including_ecotax_tax_excl'];
            $ecluding_ecotax_tax_amount = $result[$key1]['ecluding_ecotax_tax_amount'];
            $total_shipping_without_tax = $result[$key1]['total_shipping_without_tax'];
            $shipping_tax_amount = $result[$key1]['shipping_tax_amount'];
            $total_discounts_tax_excl = $result[$key1]['total_discounts_tax_excl'];
            $discounts_tax_amount = $result[$key1]['discounts_tax_amount'];
            $total_wrapping_tax_excl = $result[$key1]['total_wrapping_tax_excl'];
            $wrapping_tax_amount = $result[$key1]['wrapping_tax_amount'];
            $total_tax = $result[$key1]['total_tax'];
            $gross_profit_before_disco = $result[$key1]['gross_profit_before_discounts'];
            $net_profit_tax_excl = $result[$key1]['net_profit_tax_excl'];
            $gross_margin_before_disco = $result[$key1]['gross_margin_before_discounts'];
            $net_margin_tax_excl = $result[$key1]['net_margin_tax_excl'];
            $products_tax = $result[$key1]['products_tax'];

            $result[$key1]['total_with_tax'] = Tools::displayPrice($total_with_tax, (int)$a);
            $total_products_no_tax = Tools::displayPrice($total_products_no_tax, (int)$a);
            $result[$key1]['total_products_no_tax'] = $total_products_no_tax;
            $result[$key1]['products_tax'] = Tools::displayPrice($products_tax, (int)$a);
            $result[$key1]['including_ecotax_tax_excl'] = Tools::displayPrice($including_ecotax_tax_excl, (int)$a);
            $result[$key1]['ecluding_ecotax_tax_amount'] = Tools::displayPrice($ecluding_ecotax_tax_amount, (int)$a);
            $result[$key1]['total_shipping_without_tax'] = Tools::displayPrice($total_shipping_without_tax, (int)$a);
            $result[$key1]['shipping_tax_amount'] = Tools::displayPrice($shipping_tax_amount, (int)$a);
            $result[$key1]['total_discounts_tax_excl'] = Tools::displayPrice($total_discounts_tax_excl, (int)$a);
            $result[$key1]['discounts_tax_amount'] = Tools::displayPrice($discounts_tax_amount, (int)$a);
            $result[$key1]['total_wrapping_tax_excl'] = Tools::displayPrice($total_wrapping_tax_excl, (int)$a);
            $result[$key1]['wrapping_tax_amount'] = Tools::displayPrice($wrapping_tax_amount, (int)$a);
            $result[$key1]['total_tax'] = Tools::displayPrice($total_tax, (int)$a);
            $result[$key1]['gross_profit_before_discounts'] = Tools::displayPrice($gross_profit_before_disco, (int)$a);
            $result[$key1]['net_profit_tax_excl'] = Tools::displayPrice($net_profit_tax_excl, (int)$a);
            $result[$key1]['gross_margin_before_discounts'] = Tools::displayPrice($gross_margin_before_disco, (int)$a);
            $result[$key1]['net_margin_tax_excl'] = Tools::displayPrice($net_margin_tax_excl, (int)$a);
            $result[$key1]['id_currency'] = '';
            // since 1.0.21
            $products_name = $result[$key1]['products_name'];
            $result[$key1]['products_name'] = $this->formatProductsName($products_name);
        }
        $basic_array = array(
            "ID shop",
            "Shop name",
            "Order ID",
            "Order add date",
            "Products",
            "First name" ,
            "Last name",
            "Postcode",
            "City",
            "Country",
            "ID Country",
            "State",
            "Total Paid With Tax",
            "Total products no tax",
            "Products tax",
            "Including ecotax tax excl",
            "Ecluding ecotax tax amount",
            "Total shipping without tax",
            "Shipping tax amount",
            "Total discounts tax excl ",
            "Discounts tax amount",
            "Total wrapping tax excl",
            "Wrapping tax amount ",
            "Total tax ",
            "Order state",
            "Total cost",
            "Gross profit before discounts",
            "Net profit tax excl ",
            "Gross margin before discounts",
            "Net margin tax excl",
            "Currency ISO");
        if (count($basic_array) == 0) {
            return null;
        }
        $datetimefile = date('Y-m-d').'_'.date('H_i');
        $df = fopen(_PS_MODULE_DIR_.'reportsale/excsv/basic'.$datetimefile.'.csv', 'wb');
        fprintf($df, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($df, $basic_array);
        foreach ($result as $row) {
            fputcsv($df, $row);
        }
    }
    public function taxesdata()
    {
        $taxes_orderby = 'id_shop';
        $taxes_orderway = 'ASC';
        $taxes= 'SELECT `id_shop`,
        `shop_name`,
        `order_id`,
        `order_add_date`,
        `products_name`,
        `total_products_no_tax`,
        `product_tax`,
        `including_ecotax_tax_excl`,
        `including_ecotax_tax_amount`,
        `total_discounts_tax_excl`,
        `discounts_tax_amount`,
        `total_wrapping_tax_excl`,
        `wrapping_tax_amount`,
        `total_shipping_without_tax`,
        `shipping_tax_amount`,
        `total_tax`,
        `last_name`,
        `first_name`,
        `country`,
        `id_country`,
        `state`,
        `company`,
        `iso_currency`,
        `id_currency`
        FROM `'._DB_PREFIX_.'ba_report_tax_only` ORDER  BY `'. pSQL($taxes_orderby) .'`  '. pSQL($taxes_orderway) .'';
        $result = Db::getInstance()->executeS($taxes, true, false);
        foreach ($result as $key1 => $aaa) {
            $aaa;
            $a = (int)$result[$key1]['id_currency'];
            $total_products_no_tax = $result[$key1]['total_products_no_tax'];
            $including_ecotax_tax_excl = $result[$key1]['including_ecotax_tax_excl'];
            $total_shipping_without_tax = $result[$key1]['total_shipping_without_tax'];
            $shipping_tax_amount = $result[$key1]['shipping_tax_amount'];
            $total_discounts_tax_excl = $result[$key1]['total_discounts_tax_excl'];
            $discounts_tax_amount = $result[$key1]['discounts_tax_amount'];
            $total_wrapping_tax_excl = $result[$key1]['total_wrapping_tax_excl'];
            $wrapping_tax_amount = $result[$key1]['wrapping_tax_amount'];
            $total_tax = $result[$key1]['total_tax'];
            $product_tax = $result[$key1]['product_tax'];
            $including_ecotax_tax_amount = $result[$key1]['including_ecotax_tax_amount'];

            $result[$key1]['total_products_no_tax'] = Tools::displayPrice($total_products_no_tax, (int)$a);
            $result[$key1]['product_tax'] = Tools::displayPrice($product_tax, (int)$a);
            $result[$key1]['including_ecotax_tax_excl'] = Tools::displayPrice($including_ecotax_tax_excl, (int)$a);
            $result[$key1]['including_ecotax_tax_amount'] = Tools::displayPrice($including_ecotax_tax_amount, (int)$a);
            $result[$key1]['total_shipping_without_tax'] = Tools::displayPrice($total_shipping_without_tax, (int)$a);
            $result[$key1]['shipping_tax_amount'] = Tools::displayPrice($shipping_tax_amount, (int)$a);
            $result[$key1]['total_discounts_tax_excl'] = Tools::displayPrice($total_discounts_tax_excl, (int)$a);
            $result[$key1]['discounts_tax_amount'] = Tools::displayPrice($discounts_tax_amount, (int)$a);
            $result[$key1]['total_wrapping_tax_excl'] = Tools::displayPrice($total_wrapping_tax_excl, (int)$a);
            $result[$key1]['wrapping_tax_amount'] = Tools::displayPrice($wrapping_tax_amount, (int)$a);
            $result[$key1]['total_tax'] = Tools::displayPrice($total_tax, (int)$a);
            $result[$key1]['id_currency'] = '';
            // since 1.0.21
            $products_name = $result[$key1]['products_name'];
            $result[$key1]['products_name'] = $this->formatProductsName($products_name);
        }
        $taxes_array = array(
            "ID shop",
            "Shop name",
            "Order ID",
            "Order add date",
            "Products",
            "Total Products No Tax" ,
            "Product Tax",
            "Including Ecotax Tax Excl",
            "Including Ecotax Tax Amount ",
            "Total Discounts Tax Excl",
            "Discounts Tax Amount ",
            "Total Wrapping Tax Excl",
            "Wrapping Tax Amount ",
            "Total Shipping Without Tax ",
            "Shipping Tax Amount ",
            "Total Tax ",
            "Last Name",
            "First Name",
            "Country ",
            "ID Country",
            "State",
            "Company ",
            "Currency ISO "
        );
        if (count($taxes_array) == 0) {
            return null;
        }
        $datetimefile = date('Y-m-d').'_'.date('H_i');
        $df = fopen(_PS_MODULE_DIR_.'reportsale/excsv/taxes'.$datetimefile.'.csv', 'wb');
        fprintf($df, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($df, $taxes_array);
        foreach ($result as $row) {
            fputcsv($df, $row);
        }
    }
    public function revenuedata()
    {
        $revenue_orderby = 'id_shop';
        $revenue_orderway = 'ASC';
        $revenue= 'SELECT `id_shop`,
            `shop_name`,
            `id_order`,
            `order_add_date`,
            `products_name`,
            `total_discounts_tax_excl`,
            `discounts_tax_amount`,
            `total_wrapping_tax_excl`,
            `wrapping_tax_amount`,
            `total_products_no_tax`,
            `product_tax`,
            `including_ecotax_tax_excl`,
            `including_ecotax_tax_amount`,
            `total_cost`,
            `gross_profit_before_discounts`,
            `net_profit_tax_excl`,
            `gross_margin_before_discounts`,
            `net_margin_tax_excl`,
            `email`,
            `last_name`,
            `fist_name`,
            `country`,
            `id_country`,
            `state`,
            `company`,
            `iso_currency`,
            `id_currency`
            FROM `'._DB_PREFIX_.'ba_report_profit` ORDER BY `'.pSQL($revenue_orderby).'` '.pSQL($revenue_orderway).'';
        $result = Db::getInstance()->executeS($revenue, true, false);
        foreach ($result as $key1 => $aaa) {
            $aaa;
            $a = (int)$result[$key1]['id_currency'];
            $total_products_no_tax = $result[$key1]['total_products_no_tax'];
            $including_ecotax_tax_excl = $result[$key1]['including_ecotax_tax_excl'];
            $total_discounts_tax_excl = $result[$key1]['total_discounts_tax_excl'];
            $discounts_tax_amount = $result[$key1]['discounts_tax_amount'];
            $total_wrapping_tax_excl = $result[$key1]['total_wrapping_tax_excl'];
            $wrapping_tax_amount = $result[$key1]['wrapping_tax_amount'];
            $gross_profit_before_disco = $result[$key1]['gross_profit_before_discounts'];
            $net_profit_tax_excl = $result[$key1]['net_profit_tax_excl'];
            $gross_margin_before_disco = $result[$key1]['gross_margin_before_discounts'];
            $net_margin_tax_excl = $result[$key1]['net_margin_tax_excl'];
            $product_tax = $result[$key1]['product_tax'];
            $including_ecotax_tax_amount = $result[$key1]['including_ecotax_tax_amount'];
            $total_cost = $result[$key1]['total_cost'];

            $result[$key1]['including_ecotax_tax_amount'] = Tools::displayPrice($including_ecotax_tax_amount, (int)$a);
            $result[$key1]['total_products_no_tax'] = Tools::displayPrice($total_products_no_tax, (int)$a);
            $result[$key1]['product_tax'] = Tools::displayPrice($product_tax, (int)$a);
            $result[$key1]['including_ecotax_tax_excl'] = Tools::displayPrice($including_ecotax_tax_excl, (int)$a);
            $result[$key1]['total_discounts_tax_excl'] = Tools::displayPrice($total_discounts_tax_excl, (int)$a);
            $result[$key1]['discounts_tax_amount'] = Tools::displayPrice($discounts_tax_amount, (int)$a);
            $result[$key1]['total_wrapping_tax_excl'] = Tools::displayPrice($total_wrapping_tax_excl, (int)$a);
            $result[$key1]['wrapping_tax_amount'] = Tools::displayPrice($wrapping_tax_amount, (int)$a);
            $result[$key1]['total_cost'] = Tools::displayPrice($total_cost, (int)$a);
            $result[$key1]['gross_profit_before_discounts'] = Tools::displayPrice($gross_profit_before_disco, (int)$a);
            $result[$key1]['net_profit_tax_excl'] = Tools::displayPrice($net_profit_tax_excl, (int)$a);
            $result[$key1]['gross_margin_before_discounts'] = Tools::displayPrice($gross_margin_before_disco, (int)$a);
            $result[$key1]['net_margin_tax_excl'] = Tools::displayPrice($net_margin_tax_excl, (int)$a);
            $result[$key1]['id_currency'] = '';
            // since 1.0.21
            $products_name = $result[$key1]['products_name'];
            $result[$key1]['products_name'] = $this->formatProductsName($products_name);
        }
        $revenue_array = array(
            "ID shop",
            "Shop name",
            "Order ID",
            "Order add date",
            "Products",
            "Total Discounts Tax Excl " ,
            "Discounts Tax Amount ",
            "Total Wrapping Tax Excl",
            "Wrapping Tax Amount",
            "Total Products No Tax",
            "Product Tax ",
            "Including Ecotax Tax Excl",
            "Including Ecotax Tax Amount",
            "Total Cost ",
            "Gross Profit Before Discounts",
            "Net Profit Tax Excl ",
            "Gross Margin Before Discounts ",
            "Net Margin Tax Excl",
            "Email",
            "Last Name",
            "Fist Name",
            "Country",
            "ID Country",
            "State",
            "Company",
            "Currency ISO"
        );
        if (count($revenue_array) == 0) {
            return null;
        }
        $datetimefile = date('Y-m-d').'_'.date('H_i');
        $df = fopen(_PS_MODULE_DIR_.'reportsale/excsv/revenue'.$datetimefile.'.csv', 'wb');
        fprintf($df, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($df, $revenue_array);
        foreach ($result as $row) {
            fputcsv($df, $row);
        }
    }
    public function alldata()
    {
        $all_orderby = 'id_shop';
        $all_orderway = 'ASC';
        $all= 'SELECT `id_shop`,
            `shop_name`,
            `id_order`,
            `order_add_date`,
            `products_name`,
            `payment_method`,
            `carrier`,
            `weight`,
            `total_paid_with_tax`,
            `total_really_paid_with_tax`,
            `total_shipping_without_tax`,
            `shipping_tax_amount`,
            `total_discounts_tax_excl`,
            `discounts_tax_amount`,
            `total_wrapping_tax_excl`,
            `wrapping_tax_amount`,
            `total_products_no_tax`,
            `products_tax`,
            `including_ecotax_tax_excl`,
            `including_ecotax_tax_amount`,
            `total_tax`,
            `total_cost`,
            `gross_profit_before_discounts`,
            `net_profit_tax_excl`,
            `gross_margin_before_discounts`,
            `net_margin_tax_excl`,
            `email`,
            `birthday`,
            `last_name`,
            `first_name`,
            `customer_adding_date`,
            `customer_updating_date`,
            `company`,
            `address_1`,
            `address_2`,
            `postcode`,
            `city`,
            `country`,
            `id_country`,
            `state`,
            `phone`,
            `iso_currency`,
            `id_currency`
            FROM `'._DB_PREFIX_.'ba_report_full` ORDER BY `'. pSQL($all_orderby) .'` '. pSQL($all_orderway) .'';
        $result = Db::getInstance()->executeS($all, true, false);
        foreach ($result as $key1 => $aaa) {
            $aaa;
            $a = (int)$result[$key1]['id_currency'];
            $total_products_no_tax = $result[$key1]['total_products_no_tax'];
            $including_ecotax_tax_excl = $result[$key1]['including_ecotax_tax_excl'];
            $total_shipping_without_tax = $result[$key1]['total_shipping_without_tax'];
            $shipping_tax_amount = $result[$key1]['shipping_tax_amount'];
            $total_discounts_tax_excl = $result[$key1]['total_discounts_tax_excl'];
            $discounts_tax_amount = $result[$key1]['discounts_tax_amount'];
            $total_wrapping_tax_excl = $result[$key1]['total_wrapping_tax_excl'];
            $wrapping_tax_amount = $result[$key1]['wrapping_tax_amount'];
            $total_tax = $result[$key1]['total_tax'];
            $gross_profit_before_disco = $result[$key1]['gross_profit_before_discounts'];
            $net_profit_tax_excl = $result[$key1]['net_profit_tax_excl'];
            $gross_margin_before_disco = $result[$key1]['gross_margin_before_discounts'];
            $net_margin_tax_excl = $result[$key1]['net_margin_tax_excl'];
            $products_tax = $result[$key1]['products_tax'];
            $total_really_paid_with_tax = $result[$key1]['total_really_paid_with_tax'];
            $including_ecotax_tax_amount = $result[$key1]['including_ecotax_tax_amount'];
            $total_cost = $result[$key1]['total_cost'];
            $total_paid_with_tax = $result[$key1]['total_paid_with_tax'];

            $result[$key1]['total_paid_with_tax'] = Tools::displayPrice($total_paid_with_tax, (int)$a);
            $result[$key1]['total_products_no_tax'] = Tools::displayPrice($total_products_no_tax, (int)$a);
            $result[$key1]['products_tax'] = Tools::displayPrice($products_tax, (int)$a);
            $result[$key1]['including_ecotax_tax_excl'] = Tools::displayPrice($including_ecotax_tax_excl, (int)$a);
            $result[$key1]['total_shipping_without_tax'] = Tools::displayPrice($total_shipping_without_tax, (int)$a);
            $result[$key1]['shipping_tax_amount'] = Tools::displayPrice($shipping_tax_amount, (int)$a);
            $result[$key1]['total_discounts_tax_excl'] = Tools::displayPrice($total_discounts_tax_excl, (int)$a);
            $result[$key1]['discounts_tax_amount'] = Tools::displayPrice($discounts_tax_amount, (int)$a);
            $result[$key1]['total_wrapping_tax_excl'] = Tools::displayPrice($total_wrapping_tax_excl, (int)$a);
            $result[$key1]['wrapping_tax_amount'] = Tools::displayPrice($wrapping_tax_amount, (int)$a);
            $result[$key1]['total_tax'] = Tools::displayPrice($total_tax, (int)$a);
            $result[$key1]['gross_profit_before_discounts'] = Tools::displayPrice($gross_profit_before_disco, (int)$a);
            $result[$key1]['net_profit_tax_excl'] = Tools::displayPrice($net_profit_tax_excl, (int)$a);
            $result[$key1]['gross_margin_before_discounts'] = Tools::displayPrice($gross_margin_before_disco, (int)$a);
            $result[$key1]['net_margin_tax_excl'] = Tools::displayPrice($net_margin_tax_excl, (int)$a);
            $result[$key1]['total_really_paid_with_tax'] = Tools::displayPrice($total_really_paid_with_tax, (int)$a);
            $result[$key1]['including_ecotax_tax_amount'] = Tools::displayPrice($including_ecotax_tax_amount, (int)$a);
            $result[$key1]['total_cost'] = Tools::displayPrice($total_cost, (int)$a);
            $result[$key1]['id_currency'] = '';
            // since 1.0.21
            $products_name = $result[$key1]['products_name'];
            $result[$key1]['products_name'] = $this->formatProductsName($products_name);
        }
        $all_array = array(
            "ID shop",
            "Shop name",
            "Order ID",
            "Order add date",
            "Products",
            "Payment Method " ,
            "Carrier ",
            "Weight ",
            "Total Paid With Tax ",
            "Total Really Paid With Tax",
            "Total Shipping Without Tax ",
            "Shipping Tax Amount ",
            "Total Discounts Tax Excl ",
            "Discounts Tax Amount",
            "Total Wrapping Tax Excl ",
            "Wrapping Tax Amount",
            "Total Products No Tax",
            "Products Tax ",
            "Including Ecotax Tax Excl",
            "Including Ecotax Tax Amount",
            "Total Tax","Total Cost ",
            "Gross Profit Before Discounts",
            "Net Profit Tax Excl",
            "Gross Margin Before Discounts",
            "Net Margin Tax Excl",
            "Email","Birthday",
            "Last Name ",
            "First Name",
            "Customer Adding Date",
            "Customer Updating Date",
            "Company",
            "Address 1",
            "address 2 ",
            "Postcode",
            "City",
            "Country",
            "ID Country",
            "State",
            "Phone",
            "Currency ISO"
        );
        if (count($all_array) == 0) {
            return null;
        }
        $datetimefile = date('Y-m-d').'_'.date('H_i');
        $df = fopen(_PS_MODULE_DIR_.'reportsale/excsv/all'.$datetimefile.'.csv', 'wb');
        fprintf($df, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($df, $all_array);
        foreach ($result as $row) {
            fputcsv($df, $row);
        }
    }
    public function productdata()
    {
        $product_orderby = 'id_shop';
        $product_orderway = 'ASC';
        $product= 'SELECT `id_shop`,
            `shop_name`,
            `products_id`,
            `product_reference`,
            `product_name`,
            `supplier_reference`,
            `EAN_reference`,
            `UPC_reference`,
            `total_quantity`,
            `AVG_unit_price`,
            `tax_rate`,
            `total_discounts_tax_excl`,
            `discounts_tax_amount`,
            `total_products_no_tax`,
            `products_tax`,
            `including_ecotax_tax_amount`,
            `including_ecotax_tax_excl`,
            `net_tax_product_reduction`,
            `total_cost`,
            `gross_profit`,
            `gross_margin`,
            `net_margin`,
            `net_profit`,
            `manufacturer_name`,
            `category_name`,
            `of_total_sales`,
            `of_total_gross_profits`,
            `of_total_net_profits`
            FROM `'._DB_PREFIX_.'ba_report_products` ORDER BY `'.pSQL($product_orderby).'` '.pSQL($product_orderway).'';
        $result = Db::getInstance()->executeS($product, true, false);
        $product_array = array(
            "ID shop",
            "Shop name",
            "Products ID",
            "Product Reference",
            "Product Name" ,
            "Supplier Reference ",
            "EAN Reference",
            "UPC Reference",
            "Total Quantity",
            "AVG Unit Price",
            "Tax Rate",
            "Total Discounts Tax Excl",
            "Discounts Tax Amount",
            "Total Products No Tax",
            "Products Tax",
            "Including Ecotax Tax Amount",
            "Including Ecotax Tax Excl",
            "Net Tax Product Reduction",
            "Total Cost",
            "Gross Profit",
            "Gross Margin",
            "Net Profit",
            "Net Margin",
            "Manufacturer Name",
            "Category Name",
            "% Of Total Sales",
            "% Of Total Gross profits",
            "% Of Total Net Profits"
        );
        if (count($product_array) == 0) {
            return null;
        }
        $datetimefile = date('Y-m-d').'_'.date('H_i');
        $df = fopen(_PS_MODULE_DIR_.'reportsale/excsv/product'.$datetimefile.'.csv', 'wb');
        fprintf($df, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($df, $product_array);
        foreach ($result as $row) {
            fputcsv($df, $row);
        }
    }
    public function manufacturersdata()
    {
        $facturers_orderby = 'id_shop';
        $facturers_orderway = 'ASC';
        $manufacturers= 'SELECT `id_shop`,
        `shop_name`,
        `manufacturer_id`,
        `manufacturer_name`,
        `total_quantity`,
        `total_discounts_tax_excl`,
        `total_products_no_tax`,
        `including_ecotax_tax_excl`,
        `total_cost`,
        `gross_profit`,
        `gross_margin`,
        `net_profit`,
        `net_margin`,
        `of_total_sales`,
        `of_total_gross_profits`,
        `of_total_net_profits`
        FROM `'._DB_PREFIX_.'ba_report_brand` ORDER BY `'.pSQL($facturers_orderby).'` '.pSQL($facturers_orderway).'';
        $result = Db::getInstance()->executeS($manufacturers, true, false);
        $manufacturers_array = array(
            "ID shop",
            "Shop name",
            "Manufacturer ID",
            "Manufacturer Name",
            "Total Quantity " ,
            "Total Discounts Tax Excl",
            "Total Products No Tax",
            "Including Ecotax Tax Excl",
            "Total Cost",
            "Gross Profit",
            "Gross Margin",
            "Net Profit",
            "Net Margin",
            "% Of Total Sales",
            "% Of Total Gross Profits",
            "% Of Total Net Profits"
        );
        if (count($manufacturers_array) == 0) {
            return null;
        }
        $datetimefile = date('Y-m-d').'_'.date('H_i');
        $df = fopen(_PS_MODULE_DIR_.'reportsale/excsv/manufacturers'.$datetimefile.'.csv', 'wb');
        fprintf($df, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($df, $manufacturers_array);
        foreach ($result as $row) {
            fputcsv($df, $row);
        }
    }
    public function supplierdata()
    {
        $supplier_orderby = 'id_shop';
        $supplier_orderway = 'ASC';
        $supplier= 'SELECT `id_shop`,
        `shop_name`,
        `supplier_id`,
        `supplier_name`,
        `total_quantity`,
        `total_discounts_tax_excl`,
        `discounts_tax_amount`,
        `total_products_no_tax`,
        `including_ecotax_tax_excl`,
        `total_cost`,
        `gross_profit`,
        `gross_margin`,
        `net_profit`,
        `net_margin`,
        `of_total_sales`,
        `of_total_gross_profits`,
        `of_total_net_profits`
        FROM `'._DB_PREFIX_.'ba_report_supplier`  ORDER BY `'.pSQL($supplier_orderby).'` '.pSQL($supplier_orderway).'';
        $result = Db::getInstance()->executeS($supplier, true, false);
        $supplier_array = array(
            "ID shop",
            "Shop name",
            "Supplier ID",
            "Supplier Name",
            "Total Quantity " ,
            "Total Discounts Tax Excl",
            "Total Products No Tax",
            "Including Ecotax Tax Excl",
            "Discounts Tax Amount",
            "Total Products No Tax",
            "Including Ecotax Tax Excl",
            "Total Cost",
            "Gross Profit",
            "Gross Margin",
            "Net Profit",
            "Net Margin",
            "% Of Total Sales",
            "% Of Total Gross Profits",
            "% Of Total Net Profits"
        );
        if (count($supplier_array) == 0) {
            return null;
        }
        $datetimefile = date('Y-m-d').'_'.date('H_i');
        $df = fopen(_PS_MODULE_DIR_.'reportsale/excsv/supplier'.$datetimefile.'.csv', 'wb');
        fprintf($df, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($df, $supplier_array);
        foreach ($result as $row) {
            fputcsv($df, $row);
        }
    }
    public function categorydata()
    {
        $category_orderby = 'id_shop';
        $category_orderway = 'ASC';
        $category= 'SELECT `id_shop`,
        `shop_name`,
        `category_id`,
        `category_name`,
        `total_quantity`,
        `total_discounts_tax_excl`,
        `discounts_tax_amount`,
        `total_products_no_tax`,
        `including_ecotax_tax_excl`,
        `total_cost`,`gross_profit`,
        `gross_margin`,
        `net_profit`,
        `net_margin`,
        `of_total_sales`,
        `of_total_gross_profits`,
        `of_total_net_profits`
        FROM `'._DB_PREFIX_.'ba_report_category`  ORDER BY `'.pSQL($category_orderby).'` '.pSQL($category_orderway).'';
        $result = Db::getInstance()->executeS($category, true, false);
        $category_array = array(
            "ID shop",
            "Shop name",
            "Category ID",
            "Category Name",
            "Total Quantity " ,
            "Total Discounts Tax Excl",
            "Total Products No Tax",
            "Including Ecotax Tax Excl",
            "Total Cost",
            "Gross Margin",
            "Net Profit",
            "Net Margin",
            "% Of Total Sales",
            "% Of Total Gross Profits",
            "% Of Total Net Profits"
        );
        if (count($category_array) == 0) {
            return null;
        }
        $datetimefile = date('Y-m-d').'_'.date('H_i');
        $df = fopen(_PS_MODULE_DIR_.'reportsale/excsv/category'.$datetimefile.'.csv', 'wb');
        fprintf($df, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($df, $category_array);
        foreach ($result as $row) {
            fputcsv($df, $row);
        }
    }
    public function clientdata()
    {
        $client_orderby = 'id_shop';
        $client_orderway = 'ASC';
        $client= 'SELECT `id_shop`,
        `shop_name`,
        `customer_id`,
        `last_name`,
        `first_name`,
        `email`,
        `company`,
        `address_1`,
        `address_2`,
        `postcode`,
        `city`,
        `country`,
        `id_country`,
        `phone`,
        `first_order`,
        `last_order`,
        `of_order`,
        `of_products_ordered`,
        `average_cart_all_included`,
        `products_ordered`,
        `total_products_no_tax`,
        `total_cost`,
        `total_discounts_tax_excl`,
        `gross_profit`,
        `net_profit`,
        `gross_margin`,
        `net_margin`
        FROM `'._DB_PREFIX_.'ba_report_customer` ORDER BY `'. pSQL($client_orderby) .'` '. pSQL($client_orderway) .'';
        $result = Db::getInstance()->executeS($client, true, false);
        $client_array = array(
            "ID shop",
            "Shop name",
            "Customer ID",
            "Last Name",
            "First Name",
            "Email",
            "Company",
            "address 1",
            "Address 2",
            "Postcode",
            "City",
            "Country",
            "ID Country",
            "Phone",
            "First Order",
            "Last Order",
            "Of Order",
            "Of Products Ordered",
            "Average Cart All Included",
            "Products Ordered",
            "Total Products No Tax","Total Cost",
            "Total Discounts Tax Excl",
            "Gross Profit",
            "Net Profit",
            "Gross Margin",
            "Net Margin"
        );
        if (count($client_array) == 0) {
            return null;
        }
        $datetimefile = date('Y-m-d').'_'.date('H_i');
        $df = fopen(_PS_MODULE_DIR_.'reportsale/excsv/client'.$datetimefile.'.csv', 'wb');
        fprintf($df, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($df, $client_array);
        foreach ($result as $row) {
            fputcsv($df, $row);
        }
    }
    public function creditslipsdata()
    {
        $credit_orderby = 'id_shop';
        $credit_orderway = 'ASC';
        $creditslips= 'SELECT `shop_name`,
        `credit_slip_id`,
        `order_id`,
        `first_name`,
        `last_name`,
        `credit_slip_date`,
        `payment_method`,
        `total_products_no_tax`,
        `products_tax`,
        `total_shipping_without_tax`,
        `shipping_tax_amount`,
        `total_no_tax`,
        `total_tax`,
        `total_tax_incl`,
        `iso_currency`,
        `id_currency`,
		`country`,
        `state` 
        FROM `'._DB_PREFIX_.'ba_report_store_credit` ORDER BY `'.pSQL($credit_orderby).'` '.pSQL($credit_orderway).'';
        $result = Db::getInstance()->executeS($creditslips, true, false);
        foreach ($result as $key1 => $aaa) {
            $aaa;
            $a = (int)$result[$key1]['id_currency'];
            $total_products_no_tax = $result[$key1]['total_products_no_tax'];
            $total_shipping_without_tax = $result[$key1]['total_shipping_without_tax'];
            $shipping_tax_amount = $result[$key1]['shipping_tax_amount'];
            $total_tax = $result[$key1]['total_tax'];
            $products_tax = $result[$key1]['products_tax'];
            $total_no_tax = $result[$key1]['total_no_tax'];
            $total_tax_incl = $result[$key1]['total_tax_incl'];

            $result[$key1]['total_products_no_tax'] = Tools::displayPrice($total_products_no_tax, (int)$a);
            $result[$key1]['products_tax'] = Tools::displayPrice($products_tax, (int)$a);
            $result[$key1]['total_shipping_without_tax'] = Tools::displayPrice($total_shipping_without_tax, (int)$a);
            $result[$key1]['shipping_tax_amount'] = Tools::displayPrice($shipping_tax_amount, (int)$a);
            $result[$key1]['total_no_tax'] = Tools::displayPrice($total_no_tax, (int)$a);
            $result[$key1]['total_tax'] = Tools::displayPrice($total_tax, (int)$a);
            $result[$key1]['total_tax_incl'] = Tools::displayPrice($total_tax_incl, (int)$a);
            $result[$key1]['id_currency'] = $a;
        }
        $creditslips_array = array(
            "Shop name",
            "Credit Slip ID",
            "ID Order",
            "First Name",
            "Last Name",
            "Credit Slip Date",
            "Payment Method",
            "Total Products No Tax",
            "Products Tax",
            "Total Shipping Without Tax",
            "Shipping Tax Amount",
            "Total No Tax",
            "Total Tax",
            "Total Tax Incl",
            "Currency ISO",
            "ID Currency",
            "Country",
            "State",
        );
        if (count($creditslips_array) == 0) {
            return null;
        }
        $datetimefile = date('Y-m-d').'_'.date('H_i');
        $df = fopen(_PS_MODULE_DIR_.'reportsale/excsv/creditslips'.$datetimefile.'.csv', 'wb');
        fprintf($df, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($df, $creditslips_array);
        foreach ($result as $row) {
            fputcsv($df, $row);
        }
    }
}
