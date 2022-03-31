<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 * We offer the best and most useful modules PrestaShop and modifications for your online store.
 *
 * @author    knowband.com <support@knowband.com>
 * @copyright 2017 Knowband
 * @license   see file: LICENSE.txt
 * @category  PrestaShop Module
 */

//First condition to check if PS Version defined
if (!defined('_PS_VERSION_')) {
    exit;
}

//Module class extends parent module class to use its methods and objects
class KbEtsy extends Module
{

    const PARENT_TAB_CLASS = 'AdminEtsyModule';
    const SELL_CLASS_NAME = 'SELL';

    private $demo_flag = 0;

    public function __construct()
    {
        $this->name = 'kbetsy';
        $this->tab = 'market_place';
        $this->version = '2.0.4';
        $this->author = 'Knowband';
        $this->module_key = 'e27f7356a26b98b8b15fcced480bb2c0';
        $this->author_address = '0x2C366b113bd378672D4Ee91B75dC727E857A54A6';
        $this->need_instance = 0;
        $this->ps_version_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Knowband Etsy');
        $this->description = $this->l('Module to sync products on Etsy Marketplace.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall ?');
        if (Configuration::get('etsy_default_lang') == '') {
            Configuration::updateGlobalValue('etsy_default_lang', Context::getContext()->language->id);
            Configuration::updateGlobalValue('etsy_store_lang', 'de,en,es,fr,it,ja,nl,pt,ru,pl');
        }
    }

    //Function definition to install the module
    public function install()
    {
        if (!Configuration::get('KBETSY_SECURE_KEY')) {
            Configuration::updateValue('KBETSY_SECURE_KEY', $this->kbmaSecureKeyGenerator());
        }
        Configuration::updateValue('KBETSY_DEMO', $this->demo_flag);

        //Create SQL Tables
        $createTableSQL = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "etsy_audit_log` (
                            `id_etsy_audit_log` int(11) NOT NULL AUTO_INCREMENT,
                            `log_entry` text NOT NULL,
                            `log_user` int(11) NOT NULL,
                            `log_class_method` varchar(255) NOT NULL,
                            `log_time` datetime NOT NULL,
                            PRIMARY KEY (`id_etsy_audit_log`)
                          ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;
                          CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "etsy_categories` (
                            `id_etsy_categories` int(10) NOT NULL AUTO_INCREMENT,
                            `category_code` int(10) NOT NULL,
                            `category_name` text NOT NULL,
                            `property_set` text NOT NULL,
                            PRIMARY KEY (`id_etsy_categories`),
                            KEY `category_code` (`category_code`)
                          ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;
                          CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "etsy_countries` (
                            `id_etsy_countries` int(10) NOT NULL AUTO_INCREMENT,
                            `country_id` int(10) NOT NULL,
                            `country_name` varchar(255) NOT NULL,
                            `iso_code` varchar(3) NOT NULL,
                            PRIMARY KEY (`id_etsy_countries`),
                            KEY `country_id` (`country_id`)
                          ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;
                          CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "etsy_regions` (
                            `id_etsy_regions` int(10) NOT NULL AUTO_INCREMENT,
                            `region_id` int(10) NOT NULL,
                            `region_name` varchar(255) NOT NULL,
                            PRIMARY KEY (`id_etsy_regions`),
                            KEY `region_id` (`region_id`)
                          ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;
                          CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "etsy_attribute_mapping1` (
                             `id_attribute_mapping` int(11) NOT NULL AUTO_INCREMENT,
                             `property_id` int(11) NOT NULL,
                             `property_title` varchar(500) NOT NULL,
                             `id_attribute_group` int(11) NOT NULL,
                             `date_added` datetime NOT NULL,
                             `date_updated` datetime NOT NULL,
                             PRIMARY KEY (`id_attribute_mapping`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;
                          CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "etsy_shipping_templates` (
                            `id_etsy_shipping_templates` int(10) NOT NULL AUTO_INCREMENT,
                            `shipping_template_id` bigint(25) DEFAULT NULL,
                            `shipping_template_title` varchar(255) NOT NULL,
                            `shipping_origin_country_id` int(10) NOT NULL,
                            `shipping_origin_country` varchar(255) NOT NULL,
                            `shipping_primary_cost` decimal(15,2) NOT NULL,
                            `shipping_secondary_cost` decimal(15,2) NOT NULL,
                            `shipping_min_process_days` int(2) NOT NULL,
                            `shipping_max_process_days` int(2) NOT NULL,
                            `renew_flag` enum('0','1') NOT NULL DEFAULT '0',
                            `delete_flag` enum('0','1') NOT NULL DEFAULT '0',
                            `shipping_date_added` datetime NOT NULL,
                            `shipping_date_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                            PRIMARY KEY (`id_etsy_shipping_templates`),
                            UNIQUE KEY `shipping_template_id` (`shipping_template_id`),
                            KEY `renew_flag` (`renew_flag`,`delete_flag`)
                          ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;
                          CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "etsy_shipping_templates_entries` (
                            `id_etsy_shipping_templates_entries` int(10) NOT NULL AUTO_INCREMENT,
                            `id_etsy_shipping_templates` int(10) NOT NULL,
                            `shipping_template_entry_id` bigint(25) DEFAULT NULL,
                            `shipping_entry_destination_country_id` int(10) DEFAULT NULL,
                            `shipping_entry_destination_country` varchar(255) DEFAULT NULL,
                            `shipping_entry_primary_cost` decimal(15,2) NOT NULL,
                            `shipping_entry_secondary_cost` decimal(15,2) NOT NULL,
                            `shipping_entry_destination_region_id` int(10) DEFAULT NULL,
                            `shipping_entry_destination_region` varchar(255) DEFAULT NULL,
                            `renew_flag` enum('0','1') NOT NULL DEFAULT '0',
                            `delete_flag` enum('0','1') NOT NULL DEFAULT '0',
                            `shipping_entry_date_added` datetime NOT NULL,
                            `shipping_entry_date_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                            PRIMARY KEY (`id_etsy_shipping_templates_entries`),
                            UNIQUE KEY `shipping_template_entry_id` (`shipping_template_entry_id`),
                            KEY `id_etsy_shipping_templates` (`id_etsy_shipping_templates`),
                            KEY `renew_flag` (`renew_flag`,`delete_flag`)
                          ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;
                          CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "etsy_profiles` (
                            `id_etsy_profiles` int(10) NOT NULL AUTO_INCREMENT,
                            `profile_title` varchar(255) NOT NULL,
                            `customize_product_title` text NULL,
                            `id_etsy_shipping_templates` int(10) NOT NULL,
                            `etsy_currency` varchar(5) NOT NULL,
                            `is_customizable` enum('1','0') NOT NULL DEFAULT '0',
                            `who_made` enum('i_did','collective','someone_else') NOT NULL DEFAULT 'i_did',
                            `when_made` enum('made_to_order','2020_2020','2010_2019', '2001_2009', 'before_2001', '2000_2000', '1990s','1980s','1970s','1960s','1950s','1940s','1930s','1920s','1910s','1900s','1800s','1700s','before_1700') NOT NULL DEFAULT 'made_to_order',
                            `is_supply` enum('1','0') NOT NULL DEFAULT '0',
                            `recipient` VARCHAR(50) NULL,
                            `occassion` VARCHAR(50) NULL,
                            `enable_max_qty` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                            `max_qty` int(10) UNSIGNED NOT NULL DEFAULT '0',
                            `enable_min_qty` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                            `min_qty` int(10) UNSIGNED NOT NULL DEFAULT '0',
                            `property` VARCHAR(255) NULL,
                            `active` enum('1','0') NOT NULL DEFAULT '1',
                            `date_added` datetime NOT NULL,
                            `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                            PRIMARY KEY (`id_etsy_profiles`)
                          ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;
                          CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "etsy_attribute_mapping` (
                            `id_etsy_attribute_mapping` int(10) NOT NULL AUTO_INCREMENT,
                            `property_id` int(10) NOT NULL,
                            `property_title` varchar(255) NOT NULL,
                            `id_profile_category` int(10) NOT NULL,
                            `id_etsy_profiles` int(10) NOT NULL,
                            `id_attribute_group` int(10) NOT NULL,
                            `date_added` datetime NOT NULL,
                            `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                            PRIMARY KEY (`id_etsy_attribute_mapping`),
                            KEY `property_id` (`property_id`,`property_title`,`id_profile_category`,`id_etsy_profiles`,`id_attribute_group`)
                          ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;
                          CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "etsy_products_list` (
                            `id_etsy_products_list` int(10) NOT NULL AUTO_INCREMENT,
                            `id_etsy_profiles` int(10) NOT NULL,
                            `id_product` int(10) NOT NULL,
                            `reference` varchar(32) NOT NULL,
                            `id_product_attribute` int(10) NOT NULL,
                            `listing_status` enum('Pending','Listed','Inactive','Expired','Draft') NOT NULL DEFAULT 'Pending',
                            `listing_id` bigint(25) DEFAULT NULL,
                            `listing_image_id` varchar(300) DEFAULT NULL,
                            `renew_flag` enum('0','1') NOT NULL DEFAULT '0',
                            `delete_flag` enum('0','1','2') NOT NULL DEFAULT '0',
                            `date_added` datetime NOT NULL,
                            `date_listed` datetime NOT NULL,
                            `date_last_renewed` datetime NOT NULL,
                            `listing_error` text NOT NULL,
                            PRIMARY KEY (`id_etsy_products_list`),
                            UNIQUE KEY `listing_id` (`listing_id`),
                            UNIQUE KEY `listing_image_id` (`listing_image_id`),
                            KEY `listing_status` (`listing_status`,`renew_flag`,`delete_flag`),
                            KEY `id_product_attribute` (`id_product_attribute`),
                            KEY `id_etsy_profiles` (`id_etsy_profiles`)
                          ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;
                          CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "etsy_orders_list` (
                            `id_etsy_orders_list` int(10) NOT NULL AUTO_INCREMENT,
                            `id_order` int(10) NOT NULL,
                            `id_etsy_order` bigint(25) NOT NULL,
                            `is_status_updated` enum('0','1') NOT NULL DEFAULT '0',
                            `is_tracking_updated` enum('0','1') NOT NULL DEFAULT '0',
                            `date_added` datetime NOT NULL,
                            `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                            PRIMARY KEY (`id_etsy_orders_list`),
                            KEY `is_status_updated` (`is_status_updated`)
                          ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;
                          CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "etsy_translation` (
                            `translation_id` int(11) NOT NULL AUTO_INCREMENT,
                            `id_product` int(11) NOT NULL,
                            `listing_id` int(11) NOT NULL,
                            `status` enum('Listed','Pending','Update') NOT NULL,
                            `lang_code` varchar(5) NOT NULL,
                            `date_added` datetime NOT NULL,
                            `date_updated` datetime NOT NULL,
                            `translation_error` text,
                            PRIMARY KEY (`translation_id`),
                            UNIQUE KEY `id_product_lang_code` (`id_product`,`lang_code`)
                          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
                          CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "etsy_queue` (
                            `id` int(10) UNSIGNED NOT NULL auto_increment,
                            `filename` varchar(255) NULL,
                            `process_date` datetime NULL,
                            `current_file` varchar(255) NULL,
                            `queue_status` enum('Pending', 'Processing', 'Completed') default 'Pending',
                            `queue_date` datetime NULL,
                            `flag` tinyint(1) UNSIGNED NOT NULL default 0,
                            `total_record` int(11) NULL,
                            `processed_record` int(11) NULL,
                            `type` text null,
                            `position` text null,
                             PRIMARY KEY (`id`)
                        ) ENGINE=ENGINE_TYPE  DEFAULT CHARSET=utf8;
                        CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "etsy_exclude_product` (
                            `id_exclude` int(14) UNSIGNED NOT NULL auto_increment,
                            `id_product` int(14) NULL,
                            `id_profiles` int(14) NULL,
                            `id_shop` int(10) NULL,
                             PRIMARY KEY (`id_exclude`)
                        ) ENGINE=ENGINE_TYPE  DEFAULT CHARSET=utf8;
                          CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "etsy_category_mapping` (
                            `id_profile_category` int(10) NOT NULL AUTO_INCREMENT,
                            `id_etsy_profiles` int(10) NOT NULL,
                            `etsy_category_code` TEXT NULL,
                            `prestashop_category` TEXT NULL,
                            `date_add` datetime NOT NULL,
                            `date_upd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                            PRIMARY KEY (`id_profile_category`),
                            KEY `id_etsy_profiles` (`id_etsy_profiles`)
                        ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;
                          CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "etsy_shipping_upgrades` (
                            `id_etsy_shipping_upgrades` int(10) NOT NULL AUTO_INCREMENT,
                            `id_etsy_shipping_templates` int(10) NOT NULL,
                            `shipping_upgrade_id` bigint(25) DEFAULT NULL,
                            `shipping_upgrade_title` varchar(100) NOT NULL,
                            `shipping_upgrade_destination` varchar(100) NOT NULL,
                            `shipping_upgrade_primary_cost` decimal(15,2) NOT NULL DEFAULT '0',
                            `shipping_upgrade_secondary_cost` decimal(15,2) NOT NULL DEFAULT '0',
                            `renew_flag` enum('0','1') NOT NULL DEFAULT '0',
                            `delete_flag` enum('0','1') NOT NULL DEFAULT '0',
                            `shipping_upgrade_date_added` datetime NOT NULL,
                            `shipping_upgrade_date_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                            PRIMARY KEY (`id_etsy_shipping_upgrades`),
                            KEY `id_etsy_shipping_templates` (`id_etsy_shipping_templates`),
                            KEY `renew_flag` (`renew_flag`,`delete_flag`)
                        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
                        CREATE TABLE `" . _DB_PREFIX_ . "etsy_shop_section` (
                            `id_etsy_shop_section` int(5) NOT NULL AUTO_INCREMENT,
                            `shop_section_title` varchar(25) NOT NULL,
                            `delete_flag` int(1) NOT NULL DEFAULT '0',
                            `renew_flag` int(1) NOT NULL DEFAULT '0',
                            `shop_section_date_added` datetime NOT NULL,
                            `shop_section_date_update` datetime NOT NULL,
                            `shop_section_id` varchar(20) NOT NULL,
                            PRIMARY KEY (`id_etsy_shop_section`)
                        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
        /* End-MK made changes on 23-11-2017 to update the tables and also create a new table for mapping of category */
        if (!Db::getInstance()->execute($createTableSQL)) {
            $this->custom_errors[] = $this->l('Error occurred during DB installation.');
            return false;
        }

        $etsy_attribute_query = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "etsy_attributes` (
            `attribute_id` int(11) NOT NULL AUTO_INCREMENT,
            `etsy_property_id` int(11) NOT NULL,
            `etsy_property_title` varchar(100) NOT NULL,
            PRIMARY KEY (`attribute_id`)
          ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;";
        Db::getInstance()->execute($etsy_attribute_query);

        $attribute_data_query = "SELECT count(*) as total FROM `" . _DB_PREFIX_ . "etsy_attributes`";
        $attribute_data = Db::getInstance()->getRow($attribute_data_query);
        if ($attribute_data['total'] <= 0) {
            $etsy_attribute_data_query = "INSERT INTO `" . _DB_PREFIX_ . "etsy_attributes` (`attribute_id`, `etsy_property_id`, `etsy_property_title`) VALUES
                (1, 200, 'Color'),
                (2, 515, 'Device'),
                (3, 504, 'Diameter'),
                (4, 501, 'Dimensions'),
                (5, 502, 'Fabric'),
                (6, 500, 'Finish'),
                (7, 503, 'Flavor'),
                (8, 505, 'Height'),
                (9, 506, 'Length'),
                (10, 507, 'Material'),
                (11, 508, 'Pattern'),
                (12, 509, 'Scent'),
                (13, 510, 'Style'),
                (14, 100, 'Size'),
                (15, 511, 'Weight'),
                (16, 512, 'Width')";
            Db::getInstance()->execute($etsy_attribute_data_query);
        }

        $select_datatype = "SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '" . _DB_PREFIX_ . "etsy_profiles' AND COLUMN_NAME = 'recipient'";
        $data_type = Db::getInstance()->executeS($select_datatype, true, false);
        if ($data_type[0]['DATA_TYPE'] == 'enum') {
            $alter_datatype = "ALTER TABLE " . _DB_PREFIX_ . "etsy_profiles MODIFY COLUMN recipient VARCHAR(50) NULL";
            if (!Db::getInstance()->execute($alter_datatype)) {
                $this->custom_errors[] = $this->l('Error occurred during table update.');
                return false;
            }
        }
        
        $select_datatype = "SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '" . _DB_PREFIX_ . "etsy_profiles' AND COLUMN_NAME = 'when_made'";
        $data_type = Db::getInstance()->executeS($select_datatype, true, false);
        if ($data_type[0]['DATA_TYPE'] == 'enum') {
            $alter_datatype = "ALTER TABLE  " . _DB_PREFIX_ . "etsy_profiles MODIFY when_made VARCHAR(50);";
            if (!Db::getInstance()->execute($alter_datatype)) {
                $this->custom_errors[] = $this->l('Error occurred during table update.');
                return false;
            }
        }


        $select_datatype = "SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '" . _DB_PREFIX_ . "etsy_profiles' AND COLUMN_NAME = 'occassion'";
        $data_type = Db::getInstance()->executeS($select_datatype, true, false);
        if ($data_type[0]['DATA_TYPE'] == 'enum') {
            $alter_datatype = "ALTER TABLE " . _DB_PREFIX_ . "etsy_profiles MODIFY COLUMN occassion VARCHAR(50) NULL";
            if (!Db::getInstance()->execute($alter_datatype)) {
                $this->custom_errors[] = $this->l('Error occurred during table update.');
                return false;
            }
        }

        $select_datatype = "SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '" . _DB_PREFIX_ . "etsy_products_list' AND COLUMN_NAME = 'listing_image_id'";
        $data_type = Db::getInstance()->executeS($select_datatype, true, false);
        if ($data_type[0]['DATA_TYPE'] == 'bigint') {
            $alter_datatype = "ALTER TABLE " . _DB_PREFIX_ . "etsy_products_list MODIFY COLUMN listing_image_id VARCHAR(300) NULL";
            if (!Db::getInstance()->execute($alter_datatype)) {
                $this->custom_errors[] = $this->l('Error occurred during table update.');
                return false;
            }
        }

        $check_column_exist = 'SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = "' . _DB_NAME_ . '" AND TABLE_NAME = "' . _DB_PREFIX_ . 'etsy_profiles" AND COLUMN_NAME = "etsy_currency"';
        $column_result = Db::getInstance()->getRow($check_column_exist);
        if (!(is_array($column_result) && count($column_result) > 0)) {
            $update_table = 'ALTER TABLE `' . _DB_PREFIX_ . 'etsy_profiles` ADD etsy_currency varchar(5) NOT NULL';
            Db::getInstance()->execute($update_table);
        }

        $check_column_exist = 'SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = "' . _DB_NAME_ . '" AND TABLE_NAME = "' . _DB_PREFIX_ . 'etsy_profiles" AND COLUMN_NAME = "id_etsy_shop_section"';
        $column_result = Db::getInstance()->getRow($check_column_exist);
        if (!(is_array($column_result) && count($column_result) > 0)) {
            $update_table = 'ALTER TABLE `' . _DB_PREFIX_ . 'etsy_profiles` ADD `id_etsy_shop_section` INT(5) NULL DEFAULT NULL';
            Db::getInstance()->execute($update_table);
        }

        $check_column_exist = 'SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = "' . _DB_NAME_ . '" AND TABLE_NAME = "' . _DB_PREFIX_ . 'etsy_profiles" AND COLUMN_NAME = "material_feature"';
        $column_result = Db::getInstance()->getRow($check_column_exist);
        if (!(is_array($column_result) && count($column_result) > 0)) {
            $update_table = 'ALTER TABLE `' . _DB_PREFIX_ . 'etsy_profiles` ADD material_feature varchar(2) NULL';
            Db::getInstance()->execute($update_table);
        }

        $check_column_exist = 'SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = "' . _DB_NAME_ . '" AND TABLE_NAME = "' . _DB_PREFIX_ . 'etsy_profiles" AND COLUMN_NAME = "custom_pricing"';
        $column_result = Db::getInstance()->getRow($check_column_exist);
        if (!(is_array($column_result) && count($column_result) > 0)) {
            $update_table = "ALTER TABLE `" . _DB_PREFIX_ . "etsy_profiles` ADD `custom_pricing` INT NOT NULL DEFAULT '0', ADD `custom_price` DECIMAL(18,2) NOT NULL DEFAULT '0.00' , ADD `price_type` ENUM('Fixed','Percentage') NULL , ADD `price_reduction` ENUM('increase','decrease') NULL ";
            Db::getInstance()->execute($update_table);
        }

        $check_column_exist = 'SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = "' . _DB_NAME_ . '" AND TABLE_NAME = "' . _DB_PREFIX_ . 'etsy_profiles" AND COLUMN_NAME = "etsy_product_type"';
        $column_result = Db::getInstance()->getRow($check_column_exist);
        if (!(is_array($column_result) && count($column_result) > 0)) {
            $update_table = "ALTER TABLE `" . _DB_PREFIX_ . "etsy_profiles` "
                    . "ADD `etsy_product_type` INT NOT NULL DEFAULT '0', "
                    . "ADD `etsy_selected_products` TEXT NULL DEFAULT NULL ";
            Db::getInstance()->execute($update_table);
        }

        $check_category_tag_exist = 'SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = "' . _DB_NAME_ . '" AND TABLE_NAME = "' . _DB_PREFIX_ . 'etsy_categories" AND COLUMN_NAME = "tag"';
        $check_category_tag = Db::getInstance()->getRow($check_category_tag_exist);
        if (!(is_array($check_category_tag) && count($check_category_tag) > 0)) {
            $update_category_table = "ALTER TABLE `" . _DB_PREFIX_ . "etsy_categories` "
                    . "ADD `tag` varchar(250) NULL";
            Db::getInstance()->execute($update_category_table);
        }


        $check_column_exist = 'SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = "' . _DB_NAME_ . '" AND TABLE_NAME = "' . _DB_PREFIX_ . 'etsy_profiles" AND COLUMN_NAME = "should_auto_renew"';
        $column_result = Db::getInstance()->getRow($check_column_exist);
        if (!(is_array($column_result) && count($column_result) > 0)) {
            $update_table = "ALTER TABLE `" . _DB_PREFIX_ . "etsy_profiles` ADD `should_auto_renew` tinyint(1) NOT NULL DEFAULT '0'";
            Db::getInstance()->execute($update_table);
        }

        $check_column_exist = 'SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = "' . _DB_NAME_ . '" AND TABLE_NAME = "' . _DB_PREFIX_ . 'etsy_products_list" AND COLUMN_NAME = "offering_id"';
        $column_result = Db::getInstance()->getRow($check_column_exist);
        if (!(is_array($column_result) && count($column_result) > 0)) {
            $update_table = 'ALTER TABLE `' . _DB_PREFIX_ . 'etsy_products_list` ADD offering_id varchar(25) NULL DEFAULT NULL';
            Db::getInstance()->execute($update_table);
        }

        $check_column_exist = 'SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = "' . _DB_NAME_ . '" AND TABLE_NAME = "' . _DB_PREFIX_ . 'etsy_products_list" AND COLUMN_NAME = "threshold_status"';
        $column_result = Db::getInstance()->getRow($check_column_exist);
        if (!(is_array($column_result) && count($column_result) > 0)) {
            $update_table = 'ALTER TABLE `' . _DB_PREFIX_ . 'etsy_products_list` ADD threshold_status ENUM("Available","Critical") NOT NULL DEFAULT "Available"';
            Db::getInstance()->execute($update_table);
        }

        $check_column_exist = 'SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = "' . _DB_NAME_ . '" AND TABLE_NAME = "' . _DB_PREFIX_ . 'etsy_products_list" AND COLUMN_NAME = "listing_file_id"';
        $column_result = Db::getInstance()->getRow($check_column_exist);
        if (!(is_array($column_result) && count($column_result) > 0)) {
            $update_table = 'ALTER TABLE `' . _DB_PREFIX_ . 'etsy_products_list` ADD listing_file_id varchar(25) NULL DEFAULT NULL';
            Db::getInstance()->execute($update_table);
        }
        /*
         * changes by rishabh jain to add trcaking column in etsy order table
         */
        $check_column_exist = 'SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = "' . _DB_NAME_ . '" AND TABLE_NAME = "' . _DB_PREFIX_ . 'etsy_profiles" AND COLUMN_NAME = "size_chart_image"';
        $column_result = Db::getInstance()->getRow($check_column_exist);
        if (!(is_array($column_result) && count($column_result) > 0)) {
            $update_table = 'ALTER TABLE `' . _DB_PREFIX_ . 'etsy_profiles` ADD size_chart_image tinyInt(1) NOT NULL DEFAULT 0';
            Db::getInstance()->execute($update_table);
        }
        
        $check_column_exist = 'SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = "' . _DB_NAME_ . '" AND TABLE_NAME = "' . _DB_PREFIX_ . 'etsy_orders_list" AND COLUMN_NAME = "is_tracking_updated"';
        $column_result = Db::getInstance()->getRow($check_column_exist);
        if (!(is_array($column_result) && count($column_result) > 0)) {
            $update_table = 'ALTER TABLE `' . _DB_PREFIX_ . 'etsy_orders_list` ADD is_tracking_updated enum("0","1") NOT NULL DEFAULT "0" AFTER is_status_updated';
            Db::getInstance()->execute($update_table);
        }
        /*
         * changes over
         */
        

        $check_active_col_sql = 'SELECT count(*) FROM information_schema.COLUMNS
                      WHERE COLUMN_NAME = "active"
                      AND TABLE_NAME = "' . _DB_PREFIX_ . 'etsy_products_list"
                      AND TABLE_SCHEMA = "' . _DB_NAME_ . '"';
        $check_active_col = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($check_active_col_sql);
        if ((int) $check_active_col == 0) {
            Db::getInstance()->execute("ALTER TABLE " . _DB_PREFIX_ . "etsy_products_list ADD `active` INT(1) NULL Default '1'");
        }

        $check_active_col_sql = 'SELECT count(*) FROM information_schema.COLUMNS
                      WHERE COLUMN_NAME = "is_error"
                      AND TABLE_NAME = "' . _DB_PREFIX_ . 'etsy_products_list"
                      AND TABLE_SCHEMA = "' . _DB_NAME_ . '"';
        $check_active_col = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($check_active_col_sql);
        if ((int) $check_active_col == 0) {
            Db::getInstance()->execute("ALTER TABLE " . _DB_PREFIX_ . "etsy_products_list ADD `is_error` INT(1) NULL Default '0'");
        }

        $check_last_level_sql = 'SELECT count(*) FROM information_schema.COLUMNS
                      WHERE COLUMN_NAME = "last_level"
                      AND TABLE_NAME = "' . _DB_PREFIX_ . 'etsy_categories"
                      AND TABLE_SCHEMA = "' . _DB_NAME_ . '"';
        $check_last_level = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($check_last_level_sql);
        if ((int) $check_last_level == 0) {
            Db::getInstance()->execute("ALTER TABLE " . _DB_PREFIX_ . "etsy_categories ADD `parent_id` INT(1) NULL Default '0'");
            Db::getInstance()->execute("ALTER TABLE " . _DB_PREFIX_ . "etsy_categories ADD `last_level` INT(1) NULL Default '0'");
        }

        $delete_track_sql = 'SELECT count(*) FROM information_schema.COLUMNS
                      WHERE COLUMN_NAME = "delete_track"
                      AND TABLE_NAME = "' . _DB_PREFIX_ . 'etsy_products_list"
                      AND TABLE_SCHEMA = "' . _DB_NAME_ . '"';
        $delete_track = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($delete_track_sql);
        if ((int) $delete_track == 0) {
            Db::getInstance()->execute("ALTER TABLE `" . _DB_PREFIX_ . "etsy_products_list` ADD `delete_track` ENUM('0','1') NOT NULL DEFAULT '0' AFTER `is_error`");
            Db::getInstance()->execute("ALTER TABLE `" . _DB_PREFIX_ . "etsy_products_list` CHANGE `listing_status` `listing_status` ENUM('Pending','Listed','Inactive','Expired','Draft','Deletion Pending','Updated','Sold Out','Relisting') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'Pending';");
            Db::getInstance()->execute("ALTER TABLE `" . _DB_PREFIX_ . "etsy_products_list` ADD `sold_flag` ENUM('0','1') NOT NULL DEFAULT '0' AFTER `delete_track`");
            Db::getInstance()->execute("ALTER TABLE `" . _DB_PREFIX_ . "etsy_products_list` ADD `listing_file_hash` VARCHAR(250) NOT NULL AFTER `listing_file_id`");
        }

        $etsy_image_query = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "etsy_images` (
            `image_id` int(11) NOT NULL AUTO_INCREMENT,
            `ps_image_id` int(11) NOT NULL,
            `product_id` int(11) NOT NULL,
            `etsy_image_id` bigint(25) NOT NULL,
            `path` varchar(250) NOT NULL,
            `path_hash` varchar(250) NOT NULL,
            PRIMARY KEY (`image_id`)
          ) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
        Db::getInstance()->execute($etsy_image_query);
        
        $etsy_product_history = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "etsy_products_history` (
            `history_id` int(11) NOT NULL AUTO_INCREMENT,
            `product_id` int(11) NOT NULL,
            `etsy_list_id` varchar(100) NOT NULL,
            `expiry_date` datetime NOT NULL,
            PRIMARY KEY (`history_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
        Db::getInstance()->execute($etsy_product_history);
        
        Db::getInstance()->execute("ALTER TABLE `" . _DB_PREFIX_ . "etsy_attribute_mapping` CHANGE `property_id` `property_id` VARCHAR(20) NOT NULL");
        Db::getInstance()->execute("ALTER TABLE `" . _DB_PREFIX_ . "etsy_categories` CHANGE `category_name` `category_name` TEXT CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL");
        
        $expiry_date_sql = 'SELECT count(*) FROM information_schema.COLUMNS
                      WHERE COLUMN_NAME = "expiry_date"
                      AND TABLE_NAME = "' . _DB_PREFIX_ . 'etsy_products_list"
                      AND TABLE_SCHEMA = "' . _DB_NAME_ . '"';
        $expiry_date = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($expiry_date_sql);
        if ((int) $expiry_date == 0) {
            Db::getInstance()->execute("ALTER TABLE `" . _DB_PREFIX_ . "etsy_products_list` ADD `expiry_date` DATETIME NOT NULL AFTER `sold_flag`");
        }
        
//        $select_datatype = "SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '" . _DB_PREFIX_ . "etsy_images' AND COLUMN_NAME = 'etsy_image_id'";
//        $data_type = Db::getInstance()->executeS($select_datatype, true, false);
//        if ($data_type[0]['DATA_TYPE'] != 'bigint') {
//        }
        $alter_datatype = "ALTER TABLE " . _DB_PREFIX_ . "etsy_images MODIFY COLUMN etsy_image_id bigint(25)";
        if (!Db::getInstance()->execute($alter_datatype)) {
            $this->custom_errors[] = $this->l('Error occurred during table update.');
            return false;
        }
        

        if (!parent::install() || !$this->registerHook('displayBackOfficeHeader') || !$this->registerHook('actionValidateOrder') || !$this->registerHook('actionOrderStatusUpdate') || !$this->registerHook('actionProductUpdate') || !$this->registerHook('actionUpdateQuantity')) {
            return false;
        }

        //Admin tabs for Etsy Marketplace module
        $this->installEtsyTabs();

        return true;
    }

    protected function installEtsyTabs()
    {
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $lang = Language::getLanguages();
            //Admin tabs for Etsy Marketplace module
            if ($this->installModuleTabs('AdminEtsyModule', $this->l('Etsy Marketplace'), 0, 1)) {
                //Code to add submenus
                $subMenuList = $this->getAdminMenus();
                if (isset($subMenuList)) {
                    foreach ($subMenuList as $subList) {
                        $this->installModuleTabs($subList['class'], $subList['name'], $subList['parent_id'], $subList['active']);
                    }
                }
            }
        } else {
            $parentTab = new Tab();
            $parentTab->name = array();
            foreach (Language::getLanguages(true) as $lang) {
                $parentTab->name[$lang['id_lang']] = $this->l('Etsy Marketplace');
            }

            $parentTab->class_name = self::PARENT_TAB_CLASS;
            $parentTab->module = $this->name;
            $parentTab->active = 1;
            $parentTab->id_parent = Tab::getIdFromClassName(self::SELL_CLASS_NAME);
            $parentTab->icon = 'cloud';
            $parentTab->add();

            $id_parent_tab = (int) Tab::getIdFromClassName(self::PARENT_TAB_CLASS);
            $admin_menus = $this->getAdminMenus();

            foreach ($admin_menus as $menu) {
                $tab = new Tab();
                foreach (Language::getLanguages(true) as $lang) {
                    $tab->name[$lang['id_lang']] = $this->l($menu['name']);
                }
                $tab->class_name = $menu['class_name'];
                $tab->module = $this->name;
                $tab->active = $menu['active'];
                $tab->id_parent = $id_parent_tab;
                $tab->add($this->id);
            }
        }
        return true;
    }
    
    //Function definition to install module tabs
    public function installModuleTabs($tabClass = '', $tabName = '', $idTabParent = 0, $active = 1)
    {
        if (!empty($tabClass) && !empty($tabName)) {
            if (Tab::getIdFromClassName($tabClass)) {
                return (true);
            }

            $tabNameLang = array();

            foreach (Language::getLanguages() as $language) {
                $tabNameLang[$language['id_lang']] = $tabName;
            }

            $tab = new Tab();
            $tab->name = $tabNameLang;
            $tab->class_name = $tabClass;
            $tab->module = $this->name;
            $tab->active = $active;
            $tab->id_parent = (int) $idTabParent;

            if ($tab->save()) {
                return true;
            }
        }
    }

    //Function defination to get submenus list
    private function getAdminMenus()
    {
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            return array(
                array(
                    'class' => 'AdminEtsyGeneralSettings',
                    'name' => $this->l('General Settings'),
                    'parent_id' => Tab::getIdFromClassName('AdminEtsyModule'),
                    'active' => 1,
                ),
                array(
                    'class' => 'AdminEtsyAttributeMapping',
                    'name' => $this->l('Attribute Mapping'),
                    'parent_id' => Tab::getIdFromClassName('AdminEtsyModule'),
                    'active' => 1,
                ),
                array(
                    'class' => 'AdminEtsyShopSection',
                    'name' => $this->l('Shop Section'),
                    'parent_id' => Tab::getIdFromClassName('AdminEtsyModule'),
                    'active' => 1,
                ),
                array(
                    'class' => 'AdminEtsyShippingTemplates',
                    'name' => $this->l('Shipping Templates'),
                    'parent_id' => Tab::getIdFromClassName('AdminEtsyModule'),
                    'active' => 1,
                ),
                array(
                    'class' => 'AdminEtsyShippingTemplatesEntries',
                    'name' => $this->l('Shipping Templates Entries'),
                    'parent_id' => Tab::getIdFromClassName('AdminEtsyModule'),
                    'active' => 0,
                ),
                array(
                    'class' => 'AdminEtsyShippingUpgrades',
                    'name' => $this->l('Shipping Upgrades'),
                    'parent_id' => Tab::getIdFromClassName('AdminEtsyModule'),
                    'active' => 0,
                ),
                array(
                    'class' => 'AdminEtsyOrderSettings',
                    'name' => $this->l('Order Settings'),
                    'parent_id' => Tab::getIdFromClassName('AdminEtsyModule'),
                    'active' => 0,
                ),
                array(
                    'class' => 'AdminEtsyProfileManagement',
                    'name' => $this->l('Profile Management'),
                    'parent_id' => Tab::getIdFromClassName('AdminEtsyModule'),
                    'active' => 1,
                ),
                array(
                    'class' => 'AdminEtsyProductsListing',
                    'name' => $this->l('Products Listing'),
                    'parent_id' => Tab::getIdFromClassName('AdminEtsyModule'),
                    'active' => 1,
                ),
                array(
                    'class' => 'AdminEtsyOrdersListing',
                    'name' => $this->l('Orders Listing'),
                    'parent_id' => Tab::getIdFromClassName('AdminEtsyModule'),
                    'active' => 1,
                ),
                array(
                    'class' => 'AdminEtsyImport',
                    'name' => $this->l('Import Products From Etsy'),
                    'parent_id' => Tab::getIdFromClassName('AdminEtsyModule'),
                    'active' => 0,
                ),
                array(
                    'class' => 'AdminEtsySynchronization',
                    'name' => $this->l('Synchronization'),
                    'parent_id' => Tab::getIdFromClassName('AdminEtsyModule'),
                    'active' => 1,
                ),
                array(
                    'class' => 'AdminEtsySalesReport',
                    'name' => $this->l('Sales Report'),
                    'parent_id' => Tab::getIdFromClassName('AdminEtsyModule'),
                    'active' => 1,
                ),
                array(
                    'class' => 'AdminEtsyProductSalesReport',
                    'name' => $this->l('Product Sales Report'),
                    'parent_id' => Tab::getIdFromClassName('AdminEtsyModule'),
                    'active' => 1,
                ),
                array(
                    'class' => 'AdminEtsyAuditLog',
                    'name' => $this->l('Audit Log'),
                    'parent_id' => Tab::getIdFromClassName('AdminEtsyModule'),
                    'active' => 1,
                )
            );
        } else {
            return array(
                array(
                    'class_name' => 'AdminEtsyGeneralSettings',
                    'active' => 1,
                    'name' => $this->l('General Settings')
                ),
                array(
                    'class_name' => 'AdminEtsyAttributeMapping',
                    'active' => 1,
                    'name' => $this->l('Attribute Mapping')
                ),
                array(
                    'class_name' => 'AdminEtsyShopSection',
                    'active' => 1,
                    'name' => $this->l('Shop Section'),
                ),
                array(
                    'class_name' => 'AdminEtsyShippingTemplates',
                    'active' => 1,
                    'name' => $this->l('Shipping Templates')
                ),
                array(
                    'class_name' => 'AdminEtsyShippingTemplatesEntries',
                    'active' => 0,
                    'name' => $this->l('Shipping Templates Entries')
                ),
                array(
                    'class_name' => 'AdminEtsyShippingUpgrades',
                    'name' => $this->l('Shipping Upgrades'),
                    'active' => 0
                ),
                array(
                    'class_name' => 'AdminEtsyProfileManagement',
                    'active' => 1,
                    'name' => $this->l('Profile Management')
                ),
                array(
                    'class_name' => 'AdminEtsyProductsListing',
                    'active' => 1,
                    'name' => $this->l('Products Listing')
                ),
                array(
                    'class_name' => 'AdminEtsyOrdersListing',
                    'active' => 1,
                    'name' => $this->l('Orders Listing')
                ),
                array(
                    'class_name' => 'AdminEtsyImport',
                    'active' => 0,
                    'name' => $this->l('Import Products From Etsy'),
                ),
                array(
                    'class_name' => 'AdminEtsySynchronization',
                    'active' => 1,
                    'name' => $this->l('Synchronization')
                ),
                array(
                    'class_name' => 'AdminEtsySalesReport',
                    'active' => 1,
                    'name' => $this->l('Sales Report'),
                ),
                array(
                    'class_name' => 'AdminEtsyProductSalesReport',
                    'active' => 1,
                    'name' => $this->l('Product Sales Report'),
                ),
                array(
                    'class_name' => 'AdminEtsyAuditLog',
                    'active' => 1,
                    'name' => $this->l('Audit Log'),
                )
            );
        }
    }

    //Function definition to uninstall the module
    public function uninstall()
    {
        if (!parent::uninstall() || !$this->unregisterHook('displayBackOfficeHeader') || !$this->unregisterHook('actionValidateOrder') || !$this->unregisterHook('actionOrderStatusUpdate') || !$this->unregisterHook('actionProductUpdate') || !$this->unregisterHook('actionUpdateQuantity')) {
            return false;
        }
        $this->unInstallEtsyTabs();

        return true;
    }

    protected function unInstallEtsyTabs()
    {
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $idTab = Tab::getIdFromClassName(self::PARENT_TAB_CLASS);
            if ($idTab != 0) {
                $tab = new Tab($idTab);
                if ($tab->delete()) {
                    $subMenuList = $this->getAdminMenus();
                    if (isset($subMenuList)) {
                        foreach ($subMenuList as $subList) {
                            $idTab = Tab::getIdFromClassName($subList['class']);
                            if ($idTab != 0) {
                                $tab = new Tab($idTab);
                                $tab->delete();
                            }
                        }
                    }
                }
            }
        } else {
            $parentTab = new Tab(Tab::getIdFromClassName(self::PARENT_TAB_CLASS));
            $parentTab->delete();

            $admin_menus = $this->getAdminMenus();

            foreach ($admin_menus as $menu) {
                $sql = 'SELECT id_tab FROM `' . _DB_PREFIX_ . 'tab` WHERE class_name = "' . pSQL($menu['class_name']) . '" 
                    AND module = "' . pSQL($this->name) . '"';
                $id_tab = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
                $tab = new Tab($id_tab);
                $tab->delete();
            }
        }
        return true;
    }

    //Hook to add content on Back Office Header
    public function hookDisplayBackOfficeHeader()
    {
        $this->context->controller->addCSS($this->_path . 'views/css/tab.css');
    }

    //Hook to check if order status get updated
    public function hookActionOrderStatusUpdate($params)
    {
        if (!empty($params['id_order'])) {
            Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_orders_list SET is_status_updated = '1' WHERE id_order = '" . (int) $params['id_order'] . "'");
        }
    }

    //Hook to check if product details get updated
    public function hookActionProductUpdate($params)
    {
//        return true;
        if (!empty($params['id_product'])) {
            $quantity_data = DB::getInstance()->getRow('SELECT quantity FROM ' . _DB_PREFIX_ . 'stock_available WHERE id_product_attribute = 0 AND id_product = ' . (int) $params['id_product']);

            $update = ($quantity_data['quantity'] > 0) ? 1 : 0;

            DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET "
                    . "is_error = '0' WHERE "
                    . "id_product = '" . (int) $params['id_product'] . "'");

            if (!(bool) $params['product']->active) {
                $update = 0;
                DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET "
                        . "listing_status = 'Inactive', "
                        . "delete_flag = '1', "
                        . "renew_flag = '0', "
                        . "is_error = '0' "
                        . "WHERE id_product = '" . (int) $params['id_product'] . "' "
                        . "AND listing_id IS NOT NULL");
            }
            //'Pending','Listed','Inactive','Expired','Draft','Deletion Pending','Updated','Sold Out','Relisting'
            DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET "
                    . "listing_status = 'Updated', "
                    . "is_error = '0', "
                    . "sold_flag = '0' "
                    . "WHERE delete_flag = '0' "
                    . "AND active = '1' "
                    . "AND listing_status IN ('Listed','Sold Out','Inactive')"
                    . "AND id_product = '" . (int) $params['id_product'] . "' AND listing_id IS NOT NULL");
        }
    }

    private function kbmaSecureKeyGenerator($length = 32)
    {
        $random = '';
        for ($i = 0; $i < $length; $i++) {
            $random .= chr(mt_rand(33, 126));
        }
        return md5($random);
    }

    public function hookActionValidateOrder($params)
    {
        $order_id = $params['order']->id;
        if (!empty($order_id)) {
            $products = Context::getContext()->cart->getProducts();
            foreach ($products as $product) {
                $id_product = $product['id_product'];
                DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list "
                        . "SET listing_status = 'Updated', "
                        . "is_error = '0', "
                        . "sold_flag = '0' "
                        . "WHERE id_product = '" . (int) $id_product . "' "
                        . "AND delete_flag = '0' "
                        . "AND active = '1' "
                        . "AND listing_status IN ('Listed','Sold Out','Inactive')"
                        . "AND listing_id IS NOT NULL");
            }
        }
    }
    
    public function hookActionUpdateQuantity($params = array())
    {
        $id_product = $params['id_product'];
        DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list "
        . "SET listing_status = 'Updated', "
        . "is_error = '0', "
        . "sold_flag = '0' "
        . "WHERE id_product = '" . (int) $id_product . "' "
        . "AND delete_flag = '0' "
        . "AND active = '1' "
        . "AND listing_status IN ('Listed','Sold Out','Inactive')"
        . "AND listing_id IS NOT NULL");
    }
}
