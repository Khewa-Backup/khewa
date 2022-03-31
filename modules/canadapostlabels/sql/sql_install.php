<?php
/**
 * 2019 ZH Media
 *
 * NOTICE OF LICENSE
 *
 * Unauthorized copying of this file, via any medium, is strictly prohibited.
 * Do not resell or redistribute this file, either fully or partially.
 * Do not remove this comment containing author information and copyright.
 *
 * @author    Zack Hussain <me@zackhussain.ca>
 * @copyright 2019 ZH Media - All Rights Reserved
 */

$sql = array();

$sql['cache'] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . CanadaPostPs\Cache::$definition['table'] . '` (
				`id_cache` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`id_cart` int(10) unsigned NOT NULL,
				`cart_quantity` int(10) unsigned NOT NULL,
				`id_address` int(10) unsigned NOT NULL,
				`date_add` DATETIME NULL,
				`date_upd` DATETIME NULL,
				PRIMARY KEY (`id_cache`))
		ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';

$sql['cache_rate'] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . CanadaPostPs\CacheRate::$definition['table'] . '` (
                `id_cache_rate` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `id_cache` int(10) unsigned NOT NULL,
                `id_carrier` int(10) unsigned NOT NULL,
                `code` text NOT NULL,
                `rate` float unsigned NOT NULL,
                `delay` text NULL,
                `error_message` text NULL,
                `date_add` DATETIME NULL,
                `date_upd` DATETIME NULL,
				PRIMARY KEY (`id_cache_rate`))
		ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';

$sql['cache_tracking'] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . CanadaPostPs\CacheTracking::$definition['table'] . '` (
                `id_cache_tracking` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `id_order` int(10) unsigned NOT NULL,
                `id_shipment` int(10) unsigned NOT NULL,
                `pin` VARCHAR(255) NULL,
                `service_name` VARCHAR(255) NULL,
                `event_type` VARCHAR(255) NULL,
                `event_description` VARCHAR(255) NULL,
                `event_location` VARCHAR(255) NULL,
                `expected_delivery_date` VARCHAR(255) NULL,
                `actual_delivery_date` VARCHAR(255) NULL,
                `mailed_on_date` VARCHAR(255) NULL,
                `event_date_time` VARCHAR(255) NULL,
                `date_add` DATETIME NULL,
                `date_upd` DATETIME NULL,
				PRIMARY KEY (`id_cache_tracking`))
		ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';

$sql['rate_discount'] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . CanadaPostPs\RateDiscount::$definition['table'] . '` (
                `id_cpl_rate_discount` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `id_method` int(10) unsigned NOT NULL,
                `apply_discount` VARCHAR(255) NULL,
                `discount_value` float unsigned NULL,
                `id_discount_currency` int(10) unsigned NOT NULL,
                `order_value` float unsigned NULL,
                `id_order_currency` int(10) unsigned NOT NULL,
                `include_tax` int(10) unsigned NULL,
                `include_discounts` int(10) unsigned NULL,
                `include_shipping` int(10) unsigned NULL,
                `active` int(10) unsigned NOT NULL,
                `date_add` DATETIME NULL,
                `date_upd` DATETIME NULL,
				PRIMARY KEY (`id_cpl_rate_discount`))
		ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';

$sql['rate_discount_shop'] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . CanadaPostPs\RateDiscount::$definition['table'] . '_shop` (
                `id_cpl_rate_discount` int(10) unsigned NOT NULL,
                `id_shop` int(10) unsigned NOT NULL,
				PRIMARY KEY (`id_cpl_rate_discount`, `id_shop`),
				INDEX (`id_shop`))
		ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

$sql['carrier_mapping'] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . CanadaPostPs\CarrierMapping::$definition['table'] . '` (
                `id_carrier_mapping` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `id_carrier` int(10) unsigned NOT NULL,
                `id_mapped_carrier` int(10) unsigned NOT NULL,
                `date_add` DATETIME NULL,
                `date_upd` DATETIME NULL,
				PRIMARY KEY (`id_carrier_mapping`))
		ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';

$sql['shipment'] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.CanadaPostPs\Shipment::$definition['table'].'` (
				`id_shipment` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`id_order` int(10) unsigned NULL,
				`id_group` int(10) unsigned NULL,
				`id_batch` int(10) unsigned NULL,
				`name` VARCHAR(255) NULL,
				`address1` VARCHAR(255) NULL,
				`address2` VARCHAR(255) NULL,
				`city` VARCHAR(255) NULL,
				`prov_state` VARCHAR(255) NULL,
				`country_code` VARCHAR(255) NULL,
				`postal_zip_code` VARCHAR(255) NULL,
				`tracking_pin` VARCHAR(255) NULL,
				`return_tracking_pin` VARCHAR(255) NULL,
				`shipment_id` VARCHAR(255) NULL,
				`service_code` VARCHAR(255) NULL,
				`self_link` VARCHAR(255) NULL,
				`details_link` VARCHAR(255) NULL,
				`label_link` VARCHAR(255) NULL,
				`return_label_link` VARCHAR(255) NULL,
				`commercial_invoice_link` VARCHAR(255) NULL,
				`refund_link` VARCHAR(255) NULL,
				`transmitted` tinyint(1) NULL,
				`voided` tinyint(1) NULL,
				`date_add` DATETIME NULL,
				`date_upd` DATETIME NULL,
				PRIMARY KEY (`id_shipment`))
		ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';

$sql['return_shipment'] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.CanadaPostPs\ReturnShipment::$definition['table'].'` (
				`id_return_shipment` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`id_order` int(10) unsigned NULL,
				`id_batch` int(10) unsigned NULL,
				`name` VARCHAR(255) NULL,
				`address1` VARCHAR(255) NULL,
				`address2` VARCHAR(255) NULL,
				`city` VARCHAR(255) NULL,
				`province` VARCHAR(255) NULL,
				`postal_code` VARCHAR(255) NULL,
				`tracking_pin` VARCHAR(255) NULL,
				`service_code` VARCHAR(255) NULL,
				`return_label_link` VARCHAR(255) NULL,
				`date_add` DATETIME NULL,
				`date_upd` DATETIME NULL,
				PRIMARY KEY (`id_return_shipment`))
		ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';

$sql['manifest'] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.CanadaPostPs\Manifest::$definition['table'].'` (
				`id_manifest` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `poNumber` VARCHAR(255) NULL,
                `manifestDateTime` DATETIME NULL,
                `contractId` VARCHAR(255) NULL,
                `methodOfPayment` VARCHAR(255) NULL,
                `totalCost` float unsigned ,
                `self_link`  VARCHAR(255) NULL,
                `details_link` VARCHAR(255) NULL,
                `label_link` VARCHAR(255) NULL,
                `manifest_shipments_link` VARCHAR(255) NULL,
				`date_add` DATETIME NULL,
				`date_upd` DATETIME NULL,
				PRIMARY KEY (`id_manifest`))
		ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';

$sql['batch'] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.CanadaPostPs\Batch::$definition['table'].'` (
				`id_batch` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`date_add` DATETIME NULL,
				`date_upd` DATETIME NULL,
				PRIMARY KEY (`id_batch`))
		ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';

$sql['order_error'] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.CanadaPostPs\OrderError::$definition['table'].'` (
				`id_order_error` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`id_order` int(10) unsigned NULL,
				`id_batch` int(10) unsigned NULL,
				`errorMessage` varchar(255) NULL,
				`date_add` DATETIME NULL,
				`date_upd` DATETIME NULL,
				PRIMARY KEY (`id_order_error`))
		ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';

$sql['group'] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.CanadaPostPs\Group::$definition['table'].'` (
				`id_group` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`name` varchar(255) NOT NULL,
				`active` int(10) DEFAULT 1 NOT NULL,
				`date_add` DATETIME NULL,
				`date_upd` DATETIME NULL,
				PRIMARY KEY (`id_group`))
		ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';

$sql['method'] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.CanadaPostPs\Method::$definition['table'].'` (
				`id_method` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`id_carrier` int(10) NULL,
				`id_carrier_history` text NULL,
				`name` varchar(255) NOT NULL,
				`code` varchar(16) NOT NULL,
				`group` varchar(16) NOT NULL,
				`active` tinyint(1) NOT NULL,
				`date_add` DATETIME NULL,
				`date_upd` DATETIME NULL,
				UNIQUE(`name`),
				PRIMARY KEY (`id_method`))
		ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';

$sql['address'] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.CanadaPostPs\Address::$definition['table'].'` (
				`id_address` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`id_country` int(10) NOT NULL,
				`id_state` int(10) NULL,
				`name` varchar(255) NOT NULL,
				`company` varchar(255) NULL,
				`address1` varchar(255) NOT NULL,
				`address2` varchar(255) NULL,
				`city` varchar(255) NOT NULL,
				`postcode` varchar(255) NOT NULL,
				`phone` varchar(255) NULL,
				`origin` int(10) DEFAULT 1 NOT NULL,
				`active` int(10) DEFAULT 1 NOT NULL,
				`date_add` DATETIME NULL,
				`date_upd` DATETIME NULL,
				PRIMARY KEY (`id_address`))
		ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';

$sql['box'] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.CanadaPostPs\Box::$definition['table'].'` (
				`id_box` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`name` varchar(32) NOT NULL,
				`width` decimal(10,1) NOT NULL,
				`height` decimal(10,1) NOT NULL,
				`length` decimal(10,1) NOT NULL,
				`weight` decimal(10,3) NOT NULL,
				`cube` decimal(30,3) NOT NULL,
				`active` int(10) DEFAULT 1 NOT NULL,
				`date_add` DATETIME NULL,
				`date_upd` DATETIME NULL,
				PRIMARY KEY (`id_box`))
		ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';

$sql['service'] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.CanadaPostPs\Service::$definition['table'].'` (
				`id_service` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`name` varchar(255) NOT NULL,
				`serviceCode` varchar(255) NOT NULL,
				`countryCode` varchar(255) NOT NULL,
				`supportedOptionsArray` varchar(255) NOT NULL,
				`mandatoryOptionsArray` varchar(255) NOT NULL,
				`maxWeight` decimal(10,3) NOT NULL,
				`maxLength` decimal(10,1) NOT NULL,
				`maxWidth` decimal(10,1) NOT NULL,
				`maxHeight` decimal(10,1) NOT NULL,
				`date_add` DATETIME NULL,
				`date_upd` DATETIME NULL,
				PRIMARY KEY (`id_service`))
		ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';

$sql['order_label_settings'] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.CanadaPostPs\OrderLabelSettings::$definition['table'].'` (
				`id_order_label_settings` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`id_order` int(10) unsigned NULL,
				`id_order_label_parcel` int(10) unsigned NULL,
				`id_order_label_address` int(10) unsigned NULL,
				`id_order_label_options` int(10) unsigned NULL,
				`id_order_label_preferences` int(10) unsigned NULL,
				`id_order_label_customs` int(10) unsigned NULL,
				`id_order_label_return` int(10) unsigned NULL,
				`date_add` DATETIME NULL,
				`date_upd` DATETIME NULL,
				PRIMARY KEY (`id_order_label_settings`))
		ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';

$sql['order_label_parcel'] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.CanadaPostPs\OrderLabelParcel::$definition['table'].'` (
				`id_order_label_parcel` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`id_order_label_settings` int(10) unsigned NOT NULL,
				`service_code` varchar(255) NULL,
				`box` int(10) unsigned NULL,
				`weight` varchar(255) NULL,
				`length` varchar(255) NULL,
				`width` varchar(255) NULL,
				`height` varchar(255) NULL,
				`group_id` int(10) unsigned NULL,
				`date_add` DATETIME NULL,
				`date_upd` DATETIME NULL,
				PRIMARY KEY (`id_order_label_parcel`))
		ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';

$sql['order_label_address'] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.CanadaPostPs\OrderLabelAddress::$definition['table'].'` (
				`id_order_label_address` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`id_order_label_settings` int(10) unsigned NOT NULL,
				`sender` int(10) unsigned NOT NULL,
				`receiver` int(10) unsigned NOT NULL,
				`name` varchar(255) NULL,
				`company` varchar(255) NULL,
				`address_line_1` varchar(255) NULL,
				`address_line_2` varchar(255) NULL,
				`additional_address_info` varchar(255) NULL,
				`client_voice_number` varchar(255) NULL,
				`city` varchar(255) NULL,
				`prov_state` varchar(255) NULL,
				`country_code` varchar(255) NULL,
				`postal_zip_code` varchar(255) NULL,
				`date_add` DATETIME NULL,
				`date_upd` DATETIME NULL,
				PRIMARY KEY (`id_order_label_address`))
		ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';

$sql['order_label_options'] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.CanadaPostPs\OrderLabelOptions::$definition['table'].'` (
				`id_order_label_options` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`id_order_label_settings` int(10) unsigned NOT NULL,
				`options_SO` varchar(255) NULL,
				`options_COV` varchar(255) NULL,
				`options_COD` varchar(255) NULL,
				`options_PA18` varchar(255) NULL,
				`options_PA19` varchar(255) NULL,
				`options_HFP` varchar(255) NULL,
				`options_DNS` varchar(255) NULL,
				`options_LAD` varchar(255) NULL,
				`options_D2PO` varchar(255) NULL,
				`non_delivery_options` varchar(255) NULL,
				`COV_option_amount` varchar(255) NULL,
				`COD_option_amount` varchar(255) NULL,
				`COD_option_qualifier_1` int(10) NULL,
				`D2PO_option_qualifier_2` varchar(255) NULL,
				`email` varchar(255) NULL,
				`notification_on_shipment` varchar(255) NULL,
				`notification_on_exception` varchar(255) NULL,
				`notification_on_delivery` varchar(255) NULL,
				`date_add` DATETIME NULL,
				`date_upd` DATETIME NULL,
				PRIMARY KEY (`id_order_label_options`))
		ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';

$sql['order_label_preferences'] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.CanadaPostPs\OrderLabelPreferences::$definition['table'].'` (
				`id_order_label_preferences` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`id_order_label_settings` int(10) unsigned NOT NULL,
				`show_packing_instructions` int(10) NULL,
				`show_postage_rate` int(10) NULL,
				`show_insured_value` int(10) NULL,
				`cost_centre` varchar(255) NULL,
				`customer_ref_1` varchar(255) NULL,
				`customer_ref_2` varchar(255) NULL,
				`unpackaged` int(10) NULL,
				`oversized` int(10) NULL,
				`mailing_tube` int(10) NULL,
				`output_format` varchar(255) NULL,
				`intended_method_of_payment` varchar(255) NULL,
				`date_add` DATETIME NULL,
				`date_upd` DATETIME NULL,
				PRIMARY KEY (`id_order_label_preferences`))
		ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';

$sql['order_label_customs'] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.CanadaPostPs\OrderLabelCustoms::$definition['table'].'` (
				`id_order_label_customs` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`id_order_label_settings` int(10) unsigned NOT NULL,
				`currency` varchar(255) NULL,
				`conversion_rate_from_cad` varchar(255) NULL,
				`reason_for_export` varchar(255) NULL,
				`other_reason` varchar(255) NULL,
				`certificate_number` varchar(255) NULL,
				`licence_number` varchar(255) NULL,
				`invoice_number` varchar(255) NULL,
				`date_add` DATETIME NULL,
				`date_upd` DATETIME NULL,
				PRIMARY KEY (`id_order_label_customs`))
		ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';

$sql['order_label_customs_product'] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.CanadaPostPs\OrderLabelCustomsProduct::$definition['table'].'` (
				`id_order_label_customs_product` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`id_order_label_settings` int(10) unsigned NOT NULL,
				`id_product` int(10) unsigned NOT NULL,
				`customs_description` varchar(255) NULL,
				`customs_number_of_units` varchar(255) NULL,
				`hs_tariff_code` varchar(255) NULL,
				`sku` varchar(255) NULL,
				`unit_weight` varchar(255) NULL,
				`customs_value_per_unit` varchar(255) NULL,
				`country_of_origin` varchar(255) NULL,
				`province_of_origin` varchar(255) NULL,
				`date_add` DATETIME NULL,
				`date_upd` DATETIME NULL,
				PRIMARY KEY (`id_order_label_customs_product`))
		ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';

$sql['order_label_return'] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.CanadaPostPs\OrderLabelReturn::$definition['table'].'` (
				`id_order_label_return` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`id_order_label_settings` int(10) unsigned NOT NULL,
				`return_spec` int(10) NULL,
				`return_recipient` int(10) NULL,
				`return_service_code` varchar(255) NULL,
				`date_add` DATETIME NULL,
				`date_upd` DATETIME NULL,
				PRIMARY KEY (`id_order_label_return`))
		ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
