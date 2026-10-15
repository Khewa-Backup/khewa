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

class ReportSaleData extends ReportSale
{
    public function createTableInstall()
    {
        $sql_basic = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'ba_report_basic(id_report INT(11) '
                . 'UNSIGNED AUTO_INCREMENT PRIMARY KEY,'
                . 'id_shop int(11),shop_name nvarchar(50),order_id int(11),cart_id int(11),'
                . 'order_add_date DATETIME,order_number int(11),'
                . 'invoice_add_date DATETIME,invoice_number int(11),'
                . 'invoice_status int(11),last_name nvarchar(50),'
                . 'delivery_date DATETIME, products_name varchar(5000),'
                . 'first_name nvarchar(50),postcode nvarchar(10),'
                . 'city nvarchar(50),country nvarchar(50),id_country int(11),'
                . 'total_with_tax decimal(20,6),total_products_no_tax decimal(20,6),'
                . 'products_tax decimal(20,6),including_ecotax_tax_excl decimal(20,6),'
                . 'ecluding_ecotax_tax_amount decimal(20,6),total_shipping_without_tax decimal(20,6),'
                . 'shipping_tax_amount decimal(20,6),total_discounts_tax_excl decimal(20,6),'
                . 'discounts_tax_amount decimal(20,6),total_wrapping_tax_excl decimal(20,6),'
                . 'wrapping_tax_amount decimal(20,6),total_tax decimal(20,6),order_state nvarchar(50),'
                . 'total_cost decimal(20,6),gross_profit_before_discounts decimal(20,6),'
                . 'net_profit_tax_excl decimal(20,6),gross_margin_before_discounts decimal(20,6),'
                . 'net_margin_tax_excl decimal(20,6),sign_currency nvarchar(8),'
                . 'iso_currency nvarchar(10),id_currency int(11), reference nvarchar(250) NULL'
                . ',id_state int(11) DEFAULT "0", state nvarchar(255) NULL'
                . ')';
        Db::getInstance()->query($sql_basic);
        $sql_tax_only = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'ba_report_tax_only(id_report INT(11) '
                . 'UNSIGNED AUTO_INCREMENT PRIMARY KEY,'
                . 'id_shop int(11),shop_name nvarchar(50),order_id int(11),'
                . 'order_add_date DATETIME,order_number int(11),'
                . 'invoice_add_date DATETIME,invoice_number int(11),'
                . 'invoice_status int(11),cart_id int(11),'
                . 'delivery_date DATETIME, products_name varchar(5000), '
                . 'total_products_no_tax decimal(20,6),product_tax decimal(20,6),'
                . 'including_ecotax_tax_excl decimal(20,6),'
                . 'including_ecotax_tax_amount decimal(20,6),total_discounts_tax_excl decimal(20,6),'
                . 'discounts_tax_amount decimal(20,6),total_wrapping_tax_excl decimal(20,6),'
                . 'wrapping_tax_amount decimal(20,6),total_shipping_without_tax decimal(20,6),'
                . 'shipping_tax_amount decimal(20,6),total_tax decimal(20,6),last_name nvarchar(50),'
                . 'first_name nvarchar(50),country nvarchar(50),id_country int(11),'
                . 'company nvarchar(50),sign_currency nvarchar(8),'
                . 'iso_currency nvarchar(10),id_currency int(11), reference nvarchar(250) NULL'
                . ',id_state int(11) DEFAULT "0", state nvarchar(255) NULL'
                . ')';
        Db::getInstance()->query($sql_tax_only);
        $sql_profit = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'ba_report_profit(id_report INT(11) '
                . 'UNSIGNED AUTO_INCREMENT PRIMARY KEY,'
                . 'id_shop int(11),shop_name nvarchar(50),id_cart int(11),id_order int(11),order_add_date DATETIME,'
                . 'invoice_add_date DATETIME,delivery_date DATETIME,order_number int(11),'
                . 'invoice_number int(11),invoice_status int(11),products_name varchar(5000),'
                . 'total_discounts_tax_excl decimal(20,6),discounts_tax_amount decimal(20,6),'
                . 'total_wrapping_tax_excl decimal(20,6),wrapping_tax_amount decimal(20,6),'
                . 'total_products_no_tax decimal(20,6),product_tax decimal(20,6),'
                . 'including_ecotax_tax_excl decimal(20,6),including_ecotax_tax_amount decimal(20,6),'
                . 'total_cost decimal(20,6),gross_profit_before_discounts decimal(20,6),'
                . 'net_profit_tax_excl decimal(20,6),gross_margin_before_discounts decimal(20,6),'
                . 'net_margin_tax_excl decimal(20,6),email nvarchar(50),'
                . 'last_name nvarchar(50),fist_name nvarchar(50),country nvarchar(50),'
                . 'id_country int(50),company nvarchar(50),'
                . 'sign_currency nvarchar(8),iso_currency nvarchar(10),id_currency int(11)'
                . ',reference nvarchar(250) NULL'
                . ',id_state int(11) DEFAULT "0", state nvarchar(255) NULL'
                . ')';
        Db::getInstance()->query($sql_profit);
        $sql_full = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'ba_report_full(id_report INT(11) '
                . 'UNSIGNED AUTO_INCREMENT PRIMARY KEY,'
                . 'id_shop int(11),shop_name nvarchar(50),id_cart int(11),id_order int(11),order_add_date DATETIME,'
                . 'invoice_add_date DATETIME,delivery_date DATETIME,order_number int(11),'
                . 'invoice_number int(11),invoice_status int(11),'
                . 'delivery_number int(11),payment_method nvarchar(50),products_name varchar(5000),'
                . 'carrier nvarchar(50),total_paid_with_tax decimal(20,6),'
                . 'total_really_paid_with_tax decimal(20,6),total_shipping_without_tax decimal(20,6),'
                . 'shipping_tax_amount decimal(20,6),total_discounts_tax_excl decimal(20,6),'
                . 'discounts_tax_amount decimal(20,6),total_wrapping_tax_excl decimal(20,6),'
                . 'wrapping_tax_amount decimal(20,6),total_products_no_tax decimal(20,6),'
                . 'products_tax decimal(20,6),including_ecotax_tax_excl decimal(20,6),'
                . 'including_ecotax_tax_amount decimal(20,6),total_tax decimal(20,6),total_cost decimal(20,6),'
                . 'gross_profit_before_discounts decimal(20,6),net_profit_tax_excl decimal(20,6),'
                . 'gross_margin_before_discounts decimal(20,6),net_margin_tax_excl decimal(20,6),'
                . 'email nvarchar(50),birthday DATETIME,last_name nvarchar(50),first_name nvarchar(50),'
                . 'customer_adding_date DATETIME,customer_updating_date DATETIME,company nvarchar(50),'
                . 'address_1 nvarchar(100),address_2 nvarchar(100),postcode nvarchar(10),city nvarchar(50),'
                . 'country nvarchar(50),id_country int(11),phone nvarchar(38),'
                . 'sign_currency nvarchar(8),iso_currency nvarchar(10),id_currency int(11),'
                .'`weight` decimal(11,6) NULL, reference nvarchar(250) NULL'
                . ',id_state int(11) DEFAULT "0", state nvarchar(255) NULL'
                .')';
        Db::getInstance()->query($sql_full);
        $sql_products = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'ba_report_products(id_report INT(11) '
                . 'UNSIGNED AUTO_INCREMENT PRIMARY KEY,'
                . 'id_shop int(11),shop_name nvarchar(50),id_cart int(11),id_order int(11),order_add_date DATETIME,'
                . 'invoice_add_date DATETIME,delivery_date DATETIME,order_number int(11),'
                . 'invoice_number int(11),invoice_status int(11),'
                . 'products_id int(11),product_reference nvarchar(50),product_name nvarchar(250),'
                . 'supplier_reference nvarchar(50),EAN_reference varchar(255),UPC_reference varchar(255),'
                . 'current_stock int(11),'
                . 'total_quantity int(11),AVG_unit_price decimal(20,6),tax_rate decimal(20,6),'
                . 'total_discounts_tax_excl decimal(20,6),'
                . 'discounts_tax_amount decimal(20,6),total_products_no_tax decimal(20,6),products_tax decimal(20,6),'
                . 'including_ecotax_tax_amount decimal(20,6),including_ecotax_tax_excl decimal(20,6),'
                . 'net_tax_product_reduction decimal(20,6),total_cost decimal(20,6),gross_profit decimal(20,6),'
                . 'gross_margin decimal(20,6),net_profit decimal(20,6),net_margin decimal(20,6),'
                . 'manufacturer_name nvarchar(50),category_name nvarchar(50),'
                . 'of_total_sales decimal(20,6),cumulative_of_total_sales decimal(20,6),'
                . 'of_total_gross_profits decimal(20,6),cumulative_of_total_gross_profits decimal(20,6),'
                . 'of_total_net_profits decimal(20,6),cumulative_of_total_net_profits decimal(20,6),'
                . 'customers_data text,total_customers int(11), orders_data text, total_orders int(11),'
                . 'id_combinations int(11), supplier_name nvarchar(250) NULL)';
        Db::getInstance()->query($sql_products);
        $sql_brand = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'ba_report_brand(id_report INT(11) '
                . 'UNSIGNED AUTO_INCREMENT PRIMARY KEY,'
                . 'id_shop int(11),shop_name nvarchar(50),id_cart int(11),id_order int(11),order_add_date DATETIME,'
                . 'invoice_add_date DATETIME,delivery_date DATETIME,order_number int(11),'
                . 'invoice_number int(11),invoice_status int(11),' /*                 * ********* */
                . 'manufacturer_id int(11),manufacturer_name nvarchar(50),total_quantity int(11),'
                . 'total_discounts_tax_excl decimal(20,6),total_products_no_tax decimal(20,6),'
                . 'including_ecotax_tax_excl decimal(20,6),total_cost decimal(20,6),gross_profit decimal(20,6),'
                . 'gross_margin decimal(20,6),net_profit decimal(20,6),net_margin decimal(20,6),'
                . 'of_total_sales decimal(20,6),'
                . 'cumulative_of_total_sales decimal(20,6),of_total_gross_profits decimal(20,6),'
                . 'cumulative_of_total_gross_profits decimal(20,6),of_total_net_profits decimal(20,6),'
                . 'cumulative_of_total_net_profits decimal(20,6))';
        Db::getInstance()->query($sql_brand);
        $sql_supplier = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'ba_report_supplier(id_report INT(11) '
                . 'UNSIGNED AUTO_INCREMENT PRIMARY KEY,'
                . 'id_shop int(11),shop_name nvarchar(50),id_cart int(11),id_order int(11),order_add_date DATETIME,'
                . 'invoice_add_date DATETIME,delivery_date DATETIME,order_number int(11),'
                . 'invoice_number int(11),invoice_status int(11),'
                . 'supplier_id int(11),supplier_name nvarchar(50),total_quantity int(11),'
                . 'total_discounts_tax_excl decimal(20,6),discounts_tax_amount decimal(20,6),'
                . 'total_products_no_tax decimal(20,6),'
                . 'including_ecotax_tax_excl decimal(20,6),total_cost decimal(20,6),gross_profit decimal(20,6),'
                . 'gross_margin decimal(20,6),net_profit decimal(20,6),net_margin decimal(20,6),'
                . 'of_total_sales decimal(20,6),'
                . 'cumulative_of_total_sales decimal(20,6),of_total_gross_profits decimal(20,6),'
                . 'cumulative_of_total_gross_profits decimal(20,6),of_total_net_profits decimal(20,6),'
                . 'cumulative_of_total_net_profits decimal(20,6))';
        Db::getInstance()->query($sql_supplier);
        $sql_category = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'ba_report_category(id_report INT(11) '
                . 'UNSIGNED AUTO_INCREMENT PRIMARY KEY,'
                . 'id_shop int(11),shop_name nvarchar(50),id_cart int(11),id_order int(11),order_add_date DATETIME,'
                . 'invoice_add_date DATETIME,delivery_date DATETIME,order_number int(11),'
                . 'invoice_number int(11),invoice_status int(11),' /*                 * ********* */
                . 'category_id int(11),category_name nvarchar(50),total_quantity int(11),'
                . 'total_discounts_tax_excl decimal(20,6),discounts_tax_amount decimal(20,6),'
                . 'total_products_no_tax decimal(20,6),including_ecotax_tax_excl decimal(20,6),'
                . 'total_cost decimal(20,6),gross_profit decimal(20,6),gross_margin decimal(20,6),'
                . 'net_profit decimal(20,6),'
                . 'net_margin decimal(20,6),of_total_sales decimal(20,6),'
                . 'cumulative_of_total_sales decimal(20,6),of_total_gross_profits decimal(20,6),'
                . 'cumulative_of_total_gross_profits decimal(20,6),of_total_net_profits decimal(20,6),'
                . 'cumulative_of_total_net_profits decimal(20,6))';
        Db::getInstance()->query($sql_category);
        $sql_customer = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'ba_report_customer(id_report INT(11) '
                . 'UNSIGNED AUTO_INCREMENT PRIMARY KEY,'
                . 'id_shop int(11),shop_name nvarchar(50),id_cart int(11),id_order int(11),order_add_date DATETIME,'
                . 'invoice_add_date DATETIME,delivery_date DATETIME,order_number int(11),'
                . 'invoice_number int(11),invoice_status int(11),' /*                 * ********* */
                . 'customer_id int(11),last_name nvarchar(50),first_name nvarchar(50),email nvarchar(50),'
                . 'company nvarchar(50),address_1 nvarchar(100),address_2 nvarchar(100),postcode nvarchar(10),'
                . 'city nvarchar(50),country nvarchar(50),id_country int(11),phone nvarchar(30),'
                . 'first_order DATETIME,last_order DATETIME,of_order int(11),of_products_ordered int(11),'
                . 'average_cart_all_included decimal(20,6),products_ordered nvarchar(50),'
                . 'total_products_no_tax decimal(20,6),total_cost decimal(20,6),total_discounts_tax_excl decimal(20,6),'
                . 'gross_profit decimal(20,6),net_profit decimal(20,6),total_paid_with_tax decimal(20,6),'
                . 'gross_margin decimal(20,6),net_margin decimal(20,6))';
        Db::getInstance()->query($sql_customer);
        $sql_store_credit = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'ba_report_store_credit(id_report INT(11) '
                . 'UNSIGNED AUTO_INCREMENT PRIMARY KEY,'
                . 'id_shop int(11),shop_name nvarchar(50),id_cart int(11),id_order int(11),order_add_date DATETIME,'
                . 'invoice_add_date DATETIME,delivery_date DATETIME,order_number int(11),'
                . 'invoice_number int(11),invoice_status int(11),'
                . 'credit_slip_id int(11),order_id int(11),last_name nvarchar(50),'
                . 'first_name nvarchar(50),order_invoice_date DATETIME,'
                . 'credit_slip_date DATETIME,payment_method nvarchar(50),'
                . 'total_products_no_tax decimal(20,6),products_tax decimal(20,6),'
                . 'total_shipping_without_tax decimal(20,6),'
                . 'shipping_tax_amount decimal(20,6),total_no_tax decimal(20,6),'
                . 'total_tax decimal(20,6),total_tax_incl decimal(20,6),'
                . 'sign_currency nvarchar(8),iso_currency nvarchar(10),id_currency int(11)'
                . ',reference nvarchar(250) NULL'
                . ',id_country int(11) DEFAULT "0", country nvarchar(255) NULL'
                . ',id_state int(11) DEFAULT "0", state nvarchar(255) NULL'
                . ')';
        Db::getInstance()->query($sql_store_credit);
        return true;
    }
    public function deleteTableUninstall()
    {
        $ba_report_basic = 'DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'ba_report_basic';
        Db::getInstance()->query($ba_report_basic);
        $ba_report_tax_only = 'DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'ba_report_tax_only';
        Db::getInstance()->query($ba_report_tax_only);
        $ba_report_profit = 'DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'ba_report_profit';
        Db::getInstance()->query($ba_report_profit);
        $ba_report_full = 'DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'ba_report_full';
        Db::getInstance()->query($ba_report_full);
        $ba_report_products = 'DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'ba_report_products';
        Db::getInstance()->query($ba_report_products);
        $ba_report_brand = 'DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'ba_report_brand';
        Db::getInstance()->query($ba_report_brand);
        $ba_report_supplier = 'DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'ba_report_supplier';
        Db::getInstance()->query($ba_report_supplier);
        $ba_report_category = 'DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'ba_report_category';
        Db::getInstance()->query($ba_report_category);
        $ba_report_customer = 'DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'ba_report_customer';
        Db::getInstance()->query($ba_report_customer);
        $ba_report_store_credit = 'DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'ba_report_store_credit';
        Db::getInstance()->query($ba_report_store_credit);
        return true;
    }
}
