<?php
/**
 * 2010-2021 Webkul.
 *
 * NOTICE OF LICENSE
 *
 * All right is reserved,
 * Please go through LICENSE.txt file inside our module
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please refer to CustomizationPolicy.txt file inside our module for more information.
 *
 * @author Webkul IN
 * @copyright 2010-2021 Webkul IN
 * @license LICENSE.txt
 */
class WkTrashDb
{
    /**
     * Execute SQL query
     */
    public function createTables()
    {
        if ($sql = $this->getModuleSql()) {
            foreach ($sql as $query) {
                if ($query) {
                    if (!Db::getInstance()->execute(trim($query))) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     *  SQL query for Table Creation
     */
    public function getModuleSql()
    {
        return [
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . "wk_deleted_product` (
                `id_wk_deleted_product` int(10) unsigned NOT NULL auto_increment,
                `id_product` int(10) unsigned NOT NULL,
                `id_supplier` int(10) unsigned DEFAULT NULL,
                `id_manufacturer` int(10) unsigned DEFAULT NULL,
                `id_category_default` int(10) unsigned DEFAULT NULL,
                `id_shop_default` int(10) unsigned NOT NULL DEFAULT 1,
                `id_tax_rules_group` int(11) UNSIGNED NOT NULL,
                `product_name` varchar(128) NOT NULL,
                `ean13` varchar(13) DEFAULT NULL,
                `isbn` varchar(32) DEFAULT NULL,
                `upc` varchar(12) DEFAULT NULL,
                `quantity` int(10) NOT NULL DEFAULT '0',
                `minimal_quantity` int(10) unsigned NOT NULL DEFAULT '1',
                `price` decimal(20, 6) NOT NULL DEFAULT '0.000000',
                `wholesale_price` decimal(20, 6) NOT NULL DEFAULT '0.000000',
                `unity` varchar(255) DEFAULT NULL,
                `unit_price_ratio` decimal(20, 6) NOT NULL DEFAULT '0.000000',
                `reference` varchar(64) DEFAULT NULL,
                `supplier_reference` varchar(64) DEFAULT NULL,
                `location` varchar(64) DEFAULT NULL,
                `width` DECIMAL(20, 6) NOT NULL DEFAULT '0',
                `height` DECIMAL(20, 6) NOT NULL DEFAULT '0',
                `depth` DECIMAL(20, 6) NOT NULL DEFAULT '0',
                `weight` DECIMAL(20, 6) NOT NULL DEFAULT '0',
                `out_of_stock` int(10) unsigned NOT NULL DEFAULT '2',
                `additional_delivery_times` tinyint(1) unsigned NOT NULL DEFAULT '1',
                `quantity_discount` tinyint(1) DEFAULT '0',
                `cache_is_pack` tinyint(1) NOT NULL DEFAULT '0',
                `cache_has_attachments` tinyint(1) NOT NULL DEFAULT '0',
                `is_virtual` tinyint(1) NOT NULL DEFAULT '0',
                `state` int(11) unsigned NOT NULL DEFAULT '1',
                `shop` text DEFAULT NULL,
                `lang` text DEFAULT NULL,
                `combination` text DEFAULT NULL,
                `feature` text DEFAULT NULL,
                `attachment` text DEFAULT NULL,
                `download` text DEFAULT NULL,
                `category` text DEFAULT NULL,
                `tag` text DEFAULT NULL,
                `supplier` text DEFAULT NULL,
                `image` text DEFAULT NULL,
                `customization_field` text DEFAULT NULL,
                `customized_data` text DEFAULT NUll,
                `carrier` text DEFAULT NULL,
                `stock` text DEFAULT NULL,
                `specific_price` text DEFAULT NULL,
                `specific_price_priority` text DEFAULT NULL,
                `product_sale` text DEFAULT NULL,
                `product_group_reduction` text DEFAULT NULL,
                `related_product` text DEFAULT NULL,
                `pack_product` text DEFAULT NULL,
                `old_date_add` text DEFAULT NULL,
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_wk_deleted_product`)
            ) ENGINE = " . _MYSQL_ENGINE_ . ' DEFAULT CHARSET = utf8',
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . "wk_deleted_category` (
                `id_wk_deleted_category` int(10) unsigned NOT NULL auto_increment,
                `id_category` int(10) unsigned NOT NULL,
                `id_parent` int(10) unsigned NOT NULL,
                `id_shop_default` int(10) unsigned NOT NULL DEFAULT 1,
                `category_name` varchar(128) NOT NULL,
                `level_depth` tinyint(3) unsigned NOT NULL DEFAULT '0',
                `nleft` int(10) unsigned NOT NULL DEFAULT '0',
                `nright` int(10) unsigned NOT NULL DEFAULT '0',
                `active` tinyint(1) unsigned NOT NULL DEFAULT '0',
                `position` int(10) unsigned NOT NULL DEFAULT '0',
                `is_root_category` tinyint(1) NOT NULL DEFAULT '0',
                `shop` text DEFAULT NULL,
                `lang` text DEFAULT NULL,
                `category_product` text DEFAULT NULL,
                `category_group` text DEFAULT NULL,
                `group_reduction` text DEFAULT NULL,
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_wk_deleted_category`)
            ) ENGINE = " . _MYSQL_ENGINE_ . ' DEFAULT CHARSET = utf8',
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . "wk_deleted_manufacturer` (
                `id_wk_deleted_manufacturer` int(10) unsigned NOT NULL auto_increment,
                `id_manufacturer` int(10) unsigned NOT NULL,
                `name` varchar(64) NOT NULL,
                `active` tinyint(1) unsigned NOT NULL DEFAULT '0',
                `shop` text DEFAULT NULL,
                `lang` text DEFAULT NULL,
                `address` text DEFAULT NULL,
                `product_manufacturer` text DEFAULT NULL,
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_wk_deleted_manufacturer`)
            ) ENGINE = " . _MYSQL_ENGINE_ . ' DEFAULT CHARSET = utf8',
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . "wk_deleted_supplier` (
                `id_wk_deleted_supplier` int(10) unsigned NOT NULL auto_increment,
                `id_supplier` int(10) unsigned NOT NULL,
                `name` varchar(64) NOT NULL,
                `active` tinyint(1) unsigned NOT NULL DEFAULT '0',
                `shop` text DEFAULT NULL,
                `lang` text DEFAULT NULL,
                `address` text DEFAULT NULL,
                `product_supplier` text DEFAULT NULL,
                `supplier_order` text DEFAULT NULL,
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_wk_deleted_supplier`)
            ) ENGINE = " . _MYSQL_ENGINE_ . ' DEFAULT CHARSET = utf8',
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . "wk_deleted_customer` (
                `id_wk_deleted_customer` int(10) unsigned NOT NULL auto_increment,
                `id_customer` int(10) unsigned NOT NULL,
                `id_shop_group` INT(11) UNSIGNED NOT NULL DEFAULT '1',
                `id_shop` INT(11) UNSIGNED NOT NULL DEFAULT '1',
                `id_gender` int(10) unsigned NOT NULL,
                `id_default_group` int(10) unsigned NOT NULL DEFAULT '1',
                `id_lang` int(10) unsigned NULL,
                `id_risk` int(10) unsigned NOT NULL DEFAULT '1',
                `company` varchar(255),
                `siret` varchar(14),
                `ape` varchar(5),
                `firstname` varchar(255) NOT NULL,
                `lastname` varchar(255) NOT NULL,
                `email` varchar(255) NOT NULL,
                `passwd` varchar(255) NOT NULL,
                `last_passwd_gen` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `birthday` date DEFAULT NULL,
                `newsletter` tinyint(1) unsigned NOT NULL DEFAULT '0',
                `ip_registration_newsletter` varchar(15) DEFAULT NULL,
                `newsletter_date_add` datetime DEFAULT NULL,
                `optin` tinyint(1) unsigned NOT NULL DEFAULT '0',
                `website` varchar(128),
                `outstanding_allow_amount` DECIMAL(20, 6) NOT NULL DEFAULT '0.00',
                `show_public_prices` tinyint(1) unsigned NOT NULL DEFAULT '0',
                `max_payment_days` int(10) unsigned NOT NULL DEFAULT '60',
                `secure_key` varchar(32) NOT NULL DEFAULT '-1',
                `note` text,
                `active` tinyint(1) unsigned NOT NULL DEFAULT '0',
                `is_guest` tinyint(1) NOT NULL DEFAULT '0',
                `deleted` tinyint(1) NOT NULL DEFAULT '0',
                `reset_password_token` varchar(40) DEFAULT NULL,
                `reset_password_validity` datetime DEFAULT NULL,
                `customer_group` text DEFAULT NULL,
                `customer_address` text DEFAULT NULL,
                `customer_cart_rule` text DEFAULT NULL,
                `customer_message` text DEFAULT NULL,
                `guest` text DEFAULT NULL,
                `specific_price` text DEFAULT NULL,
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_wk_deleted_customer`)
            ) ENGINE = " . _MYSQL_ENGINE_ . ' DEFAULT CHARSET = utf8',
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'wk_deleted_attribute_group` (
                `id_wk_deleted_attribute_group` int(10) unsigned NOT NULL auto_increment,
                `id_attribute_group` int(10) unsigned NOT NULL,
                `attribute_group_name` varchar(128) NOT NULL,
                `is_color_group` tinyint(1) unsigned NOT NULL,
                `group_type` varchar(255) NOT NULL,
                `position` int(11) unsigned NOT NULL,
                `shop` text DEFAULT NULL,
                `lang` text DEFAULT NULL,
                `attribute_value` text DEFAULT NULL,
                -- `product_attribute` text DEFAULT NULL,
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_wk_deleted_attribute_group`)
            ) ENGINE = ' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET = utf8',
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'wk_deleted_attribute` (
                `id_wk_deleted_attribute` int(10) unsigned NOT NULL auto_increment,
                `id_attribute` int(10) unsigned NOT NULL,
                `id_attribute_group` int(10) unsigned NOT NULL,
                `attribute_name` varchar(128) NOT NULL,
                `color` varchar(32) NOT NULL,
                `position` int(11) unsigned NOT NULL,
                `shop` text DEFAULT NULL,
                `lang` text DEFAULT NULL,
                `product_attribute` text DEFAULT NULL,
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_wk_deleted_attribute`)
            ) ENGINE = ' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET = utf8',
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . "wk_deleted_feature` (
                `id_wk_deleted_feature` int(10) unsigned NOT NULL auto_increment,
                `id_feature` int(10) unsigned NOT NULL,
                `feature_name` varchar(128) NOT NULL,
                `position` int(10) unsigned NOT NULL DEFAULT '0',
                `shop` text DEFAULT NULL,
                `lang` text DEFAULT NULL,
                `product_feature` text DEFAULT NULL,
                `feature_value` text DEFAULT NULL,
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_wk_deleted_feature`)
            ) ENGINE = " . _MYSQL_ENGINE_ . ' DEFAULT CHARSET = utf8',
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'wk_entity_restore_history` (
                `id_wk_entity_restore_history` int(10) unsigned NOT NULL auto_increment,
                `type` int(10) unsigned NOT NULL,
                `id_old_entity` int(10) unsigned NOT NULL,
                `id_new_entity` int(10) unsigned DEFAULT 0,
                `date_del` datetime NOT NULL,
                `date_res` datetime DEFAULT NULL,
                PRIMARY KEY (`id_wk_entity_restore_history`)
            ) ENGINE = ' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET = utf8',
        ];
    }

    /**
     * Execute SQL query for Table Deletion
     */
    public function deleteTables()
    {
        $sql = [
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'wk_deleted_product`',
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'wk_deleted_category`',
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'wk_deleted_manufacturer`',
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'wk_deleted_supplier`',
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'wk_deleted_customer`',
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'wk_deleted_attribute_group`',
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'wk_deleted_attribute`',
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'wk_deleted_feature`',
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'wk_entity_restore_history`',
        ];

        foreach ($sql as $query) {
            if ($query) {
                if (!Db::getInstance()->execute(trim($query))) {
                    return false;
                }
            }
        }

        return true;
    }
}
