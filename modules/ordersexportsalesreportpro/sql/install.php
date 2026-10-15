<?php
/**
 *
 * NOTICE OF LICENSE
 *
 *  @author    IntelliPresta <tehran.alishov@gmail.com>
 *  @copyright 2020 IntelliPresta
 *  @license   Commercial License
 */

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'orders_export_srpro` (
    `id_orders_export_srpro` int(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NULL,
    `configuration` TEXT NULL,
    `datatables` TEXT NULL,
    PRIMARY KEY  (`id_orders_export_srpro`),
    UNIQUE INDEX `uniq` (`name`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'INSERT IGNORE INTO `' . _DB_PREFIX_ . 'orders_export_srpro` (`name`, `configuration`) 
    VALUES ("orders_default", \'orders_selectedColumns={"order":{"id_order":"Order ID","reference":"Order Reference","new_client":"New Client","order_state.name":"Current State","order_state_history.order_history":"States History","payment":"Payment Method","order_messages.message":"Messages","total_discounts":"Total Discounts","total_discounts_tax_excl":"Total Discounts (Tax excluded)","total_paid":"Total Paid","total_products":"Total Products","total_products_wt":"Total Products With Tax","profit_amount":"Gross Profit Amount","profit_margin":"Gross Profit Margin","profit_percentage":"Gross Profit Percentage","net_profit_amount":"Net Profit Amount","net_profit_margin":"Net Profit Margin","net_profit_percentage":"Net Profit Percentage","cart_rule.name":"Cart Rule Name","cart_rule.value":"Cart Rule Value","cart_rule.free_shipping":"Free Shipping","total_shipping":"Total Shipping","total_wrapping":"Total Wrapping","order_slip.total_products_tax_excl":"Total Refunded Products (Tax excluded)","order_slip.total_products_tax_incl":"Total Refunded Products (Tax included)","order_slip.total_shipping_tax_excl":"Total Refunded Shipping (Tax excluded)","order_slip.total_shipping_tax_incl":"Total Refunded Shipping (Tax included)","order_slip.amount":"Refunded Amount","order_slip.shipping_cost_amount":"Refunded Shipping Cost Amount","invoice_number":"Invoice Number","delivery_number":"Delivery Number","date_add":"Ordered At"},"product":{"product_id":"Product ID","prod.reference":"Product Reference","order_detail_lang.product_name":"Product Name","product_link":"Product Link","product_image":"Product Image","product_name":"Product Details","product_attribute_id":"Combination ID","order_detail_lang.attributes":"Combination","attribute_image":"Combination Image","product_features.features":"Product Features","product_quantity":"Product Quantity","purchase_supplier_price":"Purchase Price from Supplier","product_price":"Product Price","total_price_tax_excl":"Total Price (Tax excluded)","unit_price_tax_excl":"Unit Price (Tax excluded)","canada_tax.unit_amount":"Unit Amount (CA 5%)","canada_tax.total_amount":"Total Amount (CA 5%)","quebec_tax.unit_amount":"Unit Amount (CA-QC 9.975%)","quebec_tax.total_amount":"Total Amount (CA-QC 9.975%)","reduction_percent":"Reduction Percent","reduction_amount":"Reduction Amount","product_quantity_discount":"Product Quantity Discount","tax_rules_group.name":"Tax Rules Group Name","order_details_tax.name":"Tax Name","order_details_tax.rate":"Tax Rate","order_details_tax.unit_amount_tax":"Tax of Unit Amount","order_details_tax.total_amount_tax":"Tax of Total Amount"},"category":{"cat.names":"Category Names","category.name":"Default Category Name"},"manufacturer":{"id_manufacturer":"Brand ID","manufacturer.name":"Brand Name"},"supplier":{"id_supplier":"Supplier ID","supplier.name":"Supplier Name"},"payment":{"amount":"Total Payment Amount","payment_details":"Payment Details","payment_dt_add":"Payment Date","payment_methods":"Payment Methods"},"customer":{"email":"Customer Email","firstname":"Customer Firstname","lastname":"Customer Lastname","def_group.name":"Default Group Name","groupp.group_names":"Group Names"},"carrier":{"carrier_name.name":"Carrier Name","tracking_number":"Tracking Number","shipping_cost_tax_excl":"Carrier Cost (Tax excluded)","shipping_cost_tax_incl":"Carrier Cost (Tax included)"},"address":{"address_invoice.firstname":"Customer Firstname of Invoice Address","address_invoice.lastname":"Customer Lastname of Invoice Address","address_invoice.alias":"Invoice Address Alias","address_invoice.city":"Invoice Address City","address_invoice.address1":"Invoice Address 1","address_invoice.address2":"Invoice Address 2","address_invoice.postcode":"Invoice Address Postcode","address_delivery.firstname":"Customer Firstname of Delivery Address","address_delivery.lastname":"Customer Lastname of Delivery Address","address_delivery.alias":"Delivery Address Alias","address_delivery.city":"Delivery City","address_delivery.address1":"Delivery Address 1","address_delivery.address2":"Delivery Address 2","address_delivery.postcode":"Delivery Address Postcode"},"shop":{"id_shop":"Shop ID","shop.name":"Shop Name"}}&orders_export_as=excel&orders_doc_name=Sales&orders_target_action=download&target_action_to_emails=&orders_target_action_ftp_type=ftp&orders_target_action_ftp_mode=active&orders_target_action_ftp_url=localhost&orders_target_action_ftp_port=&orders_target_action_ftp_username=&orders_target_action_ftp_password=&orders_target_action_ftp_folder=&orders_language=1&orders_csv_delimiter=;&orders_csv_enclosure=quot&orders_merge_helper=1&orders_merge=1&orders_sort=order.id_order&orders_sort_asc=0&orders_date_format=Y-m-d&orders_time_format=H:i:s&orders_image_type=&orders_display_header=1&orders_display_footer=1&orders_display_totals=1&orders_display_currency_symbol=1&orders_display_explanations=1&orders_decimal_separator=.&orders_round=2&orders_creation_date=this_month&orders_from_date=&orders_to_date=&orders_invoice_date=no_date&orders_invoice_from_date=&orders_invoice_to_date=&orders_delivery_date=no_date&orders_delivery_from_date=&orders_delivery_to_date=&orders_payment_date=no_date&orders_payment_from_date=&orders_payment_to_date=&orders_shipping_date=no_date&orders_shipping_from_date=&orders_shipping_to_date=&ctrl-show-selected-shops=all&orders_group_without=1&ctrl-show-selected-groups=all&orders_customer_without=1&ctrl-show-selected-customers=all&ctrl-show-selected-orders=all&ctrl-show-selected-orderStates=all&ctrl-show-selected-paymentMethods=all&orders_cart_rule_without=1&ctrl-show-selected-cartRules=all&orders_carrier_without=1&ctrl-show-selected-carriers=all&ctrl-show-selected-products=all&orders_category_whether_filter=0&orders_category_without=1&orders_attribute_without=1&ctrl-show-selected-attributes=all&orders_feature_without=1&ctrl-show-selected-features=all&orders_manufacturer_without=1&ctrl-show-selected-manufacturers=all&orders_supplier_without=1&ctrl-show-selected-suppliers=all&ctrl-show-selected-countries=all&ctrl-show-selected-currencies=all&orders_display_daily_sales=0&orders_display_monthly_sales=0&orders_display_bestsellers=0&orders_display_product_combs=0&orders_display_top_customers=0&orders_display_payment_methods=0&orders_display_category_sales=0&orders_display_manufacturer_sales=0&orders_display_supplier_sales=0&orders_display_attribute_sales=0&orders_display_feature_sales=0&orders_display_shop_sales=0\')';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'oxsrp_aexp_email` (
	`id_oxsrp_aexp_email` INT(11) NOT NULL AUTO_INCREMENT,
	`email_address` VARCHAR(255) NULL DEFAULT NULL,
	`email_setting` VARCHAR(255) NULL DEFAULT NULL,
	`email_active` TINYINT(1) NULL DEFAULT NULL,
	PRIMARY KEY (`id_oxsrp_aexp_email`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'oxsrp_aexp_ftp` (
	`id_oxsrp_aexp_ftp` INT(11) NOT NULL AUTO_INCREMENT,
        `ftp_type` ENUM(\'ftp\',\'ftps\',\'sftp\') NULL DEFAULT NULL,
        `ftp_mode` ENUM(\'active\',\'passive\') NULL DEFAULT NULL,
	`ftp_url` VARCHAR(255) NULL DEFAULT NULL,
        `ftp_port` VARCHAR(10) NULL DEFAULT NULL,
	`ftp_username` VARCHAR(255) NULL DEFAULT NULL,
	`ftp_password` VARCHAR(255) NULL DEFAULT NULL,
	`ftp_folder` VARCHAR(255) NULL DEFAULT NULL,
	`ftp_timestamp` TINYINT(1) NULL DEFAULT 1,
	`ftp_setting` VARCHAR(255) NULL DEFAULT NULL,
	`ftp_active` TINYINT(1) NULL DEFAULT 1,
        PRIMARY KEY (`id_oxsrp_aexp_ftp`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'oxsrp_schdl_email` (
	`id_oxsrp_schdl_email` INT(11) NOT NULL AUTO_INCREMENT,
	`email_address` VARCHAR(255) NULL DEFAULT NULL,
	`email_setting` VARCHAR(255) NULL DEFAULT NULL,
	`email_active` TINYINT(1) NULL DEFAULT NULL,
	PRIMARY KEY (`id_oxsrp_schdl_email`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'oxsrp_schdl_ftp` (
	`id_oxsrp_schdl_ftp` INT(11) NOT NULL AUTO_INCREMENT,
        `ftp_type` ENUM(\'ftp\',\'ftps\',\'sftp\') NULL DEFAULT NULL,
        `ftp_mode` ENUM(\'active\',\'passive\') NULL DEFAULT NULL,
	`ftp_url` VARCHAR(255) NULL DEFAULT NULL,
        `ftp_port` VARCHAR(10) NULL DEFAULT NULL,
	`ftp_username` VARCHAR(255) NULL DEFAULT NULL,
	`ftp_password` VARCHAR(255) NULL DEFAULT NULL,
	`ftp_folder` VARCHAR(255) NULL DEFAULT NULL,
	`ftp_timestamp` TINYINT(1) NULL DEFAULT 1,
	`ftp_setting` VARCHAR(255) NULL DEFAULT NULL,
	`ftp_active` TINYINT(1) NULL DEFAULT 1,
        PRIMARY KEY (`id_oxsrp_schdl_ftp`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}

Configuration::updateValue('OXSRP_AEXP_ON_WHAT', '0');

Configuration::deleteByName('OXSRP_SECURE_KEY');
Configuration::updateValue('OXSRP_SECURE_KEY', uniqid());
