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
 * @license   Commercial
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
    const MODEL_FILE = 'model.sql';
    private $demo_flag = 0;
    /*
     * Declared $module property to prevent undefined property notices and allow $this->module usage
     * @modifier Himanshu Vishwakarma
     * @date 13-10-2025
     */
    public $module;

    public function __construct()
    {
        $this->name = 'kbetsy';
        $this->tab = 'market_place';
        $this->version = '4.0.1';
        $this->author = 'Knowband';
        $this->module_key = 'e27f7356a26b98b8b15fcced480bb2c0';
        $this->author_address = '0x2C366b113bd378672D4Ee91B75dC727E857A54A6';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct();

        /*
         * Assigned module instance to $this->module for compatibility with existing calls
         * @modifier Himanshu Vishwakarma
     	 * @date 13-10-2025
         */
        $this->module = $this;

        $this->displayName = $this->module->l('Knowband Etsy', 'KbEtsy');
        $this->description = $this->module->l('Module to sync products on Etsy Marketplace.', 'KbEtsy');
        $this->confirmUninstall = $this->module->l('Are you sure you want to uninstall ?', 'KbEtsy');
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
        /** Create Database table and if there is some problem then display error message 
         * Moved database code inside the installModel function
         * @date 07-04-2023
         * @author Tanisha Gupta
         */
        if (!$this->installModel()) {
            $this->custom_errors[] = $this->module->l('Error occurred while installing/upgrading modal.', 'KbEtsy');
            return false;
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
        
        $alter_datatype = "ALTER TABLE " . _DB_PREFIX_ . "etsy_images MODIFY COLUMN etsy_image_id bigint(25)";
        if (!Db::getInstance()->execute($alter_datatype)) {
            $this->custom_errors[] = $this->module->l('Error occurred during table update.', 'KbEtsy');
            return false;
        }

        /**
         * Added by Ashish to Handle show the template sync errors.
         * Etsy001-Mar-2024 etsy-handle-template-sync
         * @date 08-03-2024
         * @author Ashish
         */
        $sync_error_check_column_sql = 'SELECT count(*) FROM information_schema.COLUMNS
                      WHERE COLUMN_NAME = "sync_error"
                      AND TABLE_NAME = "' . _DB_PREFIX_ . 'etsy_shipping_templates"
                      AND TABLE_SCHEMA = "' . _DB_NAME_ . '"';
        $sync_error_check_column = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sync_error_check_column_sql);
        if ((int) $sync_error_check_column == 0) {
            Db::getInstance()->execute("ALTER TABLE `" . _DB_PREFIX_ . "etsy_shipping_templates` ADD `sync_error` VARCHAR(255) NOT NULL DEFAULT '' AFTER `delete_flag`");
        }

        if (!parent::install() || !$this->registerHook('displayBackOfficeHeader') || !$this->registerHook('actionValidateOrder') || !$this->registerHook('actionOrderStatusUpdate') || !$this->registerHook('actionProductUpdate') || !$this->registerHook('actionUpdateQuantity')) {
            return false;
        }

        //Admin tabs for Etsy Marketplace module
        $this->installEtsyTabs();

        return true;
    }
    protected function installModel()
    {
        $installation_error = false;
        if (!file_exists(_PS_MODULE_DIR_ . $this->name . '/' . self::MODEL_FILE)) {
            $this->custom_errors[] = $this->module->l('Model installation file not found.', 'KbEtsy');
            $installation_error = true;
        } elseif (!is_readable(_PS_MODULE_DIR_ . $this->name . '/' . self::MODEL_FILE)) {
            $this->custom_errors[] = $this->module->l('Model installation file is not readable.', 'KbEtsy');
            $installation_error = true;
        } elseif (!$sql = Tools::file_get_contents(_PS_MODULE_DIR_ . $this->name . '/' . self::MODEL_FILE)) {
            $this->custom_errors[] = $this->module->l('Model installation file is empty.', 'KbEtsy');
            $installation_error = true;
        }

        if (!$installation_error) {
            /** Replace _PREFIX_ and ENGINE_TYPE with default Prestashop values */
            $sql = str_replace(
                array('_PREFIX_', 'ENGINE_TYPE'),
                array(_DB_PREFIX_, _MYSQL_ENGINE_),
                $sql
            );
            $sql = preg_split("/;\s*[\r\n]+/", trim($sql));
            foreach ($sql as $query) {
                if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->execute(trim($query))) {
                    $installation_error = true;
                }
            }
        }    
        if(!$installation_error) {
            $attribute_data_query = "SELECT count(*) as total FROM `" . _DB_PREFIX_ . "etsy_attributes`";
            $attribute_data = Db::getInstance()->getRow($attribute_data_query);
            if ($attribute_data['total'] <= 0) {
                /**
                 * Updated to use new property IDs logic for deprecated properties
                 * @modifier Himanshu Vishwakarma
                 * @date 08-10-2025
                 */
                $etsy_attribute_data_query = "INSERT INTO `" . _DB_PREFIX_ . "etsy_attributes` (`attribute_id`, `etsy_property_id`, `etsy_property_title`) VALUES
                    (1, 200, 'Color'),
                    (2, 513, 'Size')";
                Db::getInstance()->execute($etsy_attribute_data_query);
            }

            $select_datatype = "SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '" . _DB_PREFIX_ . "etsy_profiles' AND COLUMN_NAME = 'recipient'";
            $data_type = Db::getInstance()->executeS($select_datatype, true, false);
            if ($data_type[0]['DATA_TYPE'] == 'enum') {
                $alter_datatype = "ALTER TABLE " . _DB_PREFIX_ . "etsy_profiles MODIFY COLUMN recipient VARCHAR(50) NULL";
                if (!Db::getInstance()->execute($alter_datatype)) {
                    $installation_error = true;
                }
            }

            $select_datatype = "SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '" . _DB_PREFIX_ . "etsy_profiles' AND COLUMN_NAME = 'when_made'";
            $data_type = Db::getInstance()->executeS($select_datatype, true, false);
            /**
             * Added below code to changes datatype of when_made column
             * @date 18-04-2023
             * @modifier Tanisha Gupta
             */
            if(!empty($data_type)){
                foreach ($data_type as $data_type1){
                    if ($data_type1['DATA_TYPE'] == 'enum') {
                        $alter_datatype = "ALTER TABLE  " . _DB_PREFIX_ . "etsy_profiles MODIFY when_made VARCHAR(50);";
                        if (!Db::getInstance()->execute($alter_datatype)) {
                            $installation_error = true;
                        }
                        break;
                    }
                }
                
            }
            /**
             * Added query to modify the id_attribute_group column datatype from int to the varchar
             * @date 18-04-2023
             * @author Tanisha Gupta
             */
            $alter_datatype = "ALTER TABLE " . _DB_PREFIX_ . "etsy_attribute_mapping MODIFY COLUMN id_attribute_group VARCHAR(200) NULL";
                if (!Db::getInstance()->execute($alter_datatype)) {
                    $installation_error = true;
                }

            $select_datatype = "SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '" . _DB_PREFIX_ . "etsy_profiles' AND COLUMN_NAME = 'occassion'";
            $data_type = Db::getInstance()->executeS($select_datatype, true, false);
            if ($data_type[0]['DATA_TYPE'] == 'enum') {
                $alter_datatype = "ALTER TABLE " . _DB_PREFIX_ . "etsy_profiles MODIFY COLUMN occassion VARCHAR(50) NULL";
                if (!Db::getInstance()->execute($alter_datatype)) {
                    $installation_error = true;
                }
            }

            $select_datatype = "SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '" . _DB_PREFIX_ . "etsy_products_list' AND COLUMN_NAME = 'listing_image_id'";
            $data_type = Db::getInstance()->executeS($select_datatype, true, false);
            if ($data_type[0]['DATA_TYPE'] == 'bigint') {
                $alter_datatype = "ALTER TABLE " . _DB_PREFIX_ . "etsy_products_list MODIFY COLUMN listing_image_id VARCHAR(300) NULL";
                if (!Db::getInstance()->execute($alter_datatype)) {
                    $installation_error = true;
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

            /*
             * Added migration code to add id_etsy_return_policy column to etsy_profiles table
	     * @modifier Himanshu Vishwakarma
             * @date 15-12-2025
             */
            $check_column_exist = 'SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = "' . _DB_NAME_ . '" AND TABLE_NAME = "' . _DB_PREFIX_ . 'etsy_profiles" AND COLUMN_NAME = "id_etsy_return_policy"';
            $column_result = Db::getInstance()->getRow($check_column_exist);
            if (!(is_array($column_result) && count($column_result) > 0)) {
                $update_table = 'ALTER TABLE `' . _DB_PREFIX_ . 'etsy_profiles` ADD `id_etsy_return_policy` INT(5) NULL DEFAULT NULL';
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
            //changes by gopi for alter quantity column in profile table
            $check_column_exist = 'SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = "' . _DB_NAME_ . '" AND TABLE_NAME = "' . _DB_PREFIX_ . 'etsy_profiles" AND COLUMN_NAME = "alter_quantity"';
            $column_result = Db::getInstance()->getRow($check_column_exist);
            if (!(is_array($column_result) && count($column_result) > 0)) {
                $update_table = 'ALTER TABLE `' . _DB_PREFIX_ . 'etsy_profiles` ADD alter_quantity int(11) NOT NULL DEFAULT 0';
                Db::getInstance()->execute($update_table);
            }
            //changes by gopi end
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
            /**
            * Add column "id_attribute_value in etsy_attribute_mapping1 table
            * @date 07-04-2023
            * @author Tanisha Gupta
            */
       
            $id_attribute_value_column = 'SELECT count(*) FROM information_schema.COLUMNS
                          WHERE COLUMN_NAME = "id_attribute_value"
                          AND TABLE_NAME = "' . _DB_PREFIX_ . 'etsy_attribute_mapping1"
                          AND TABLE_SCHEMA = "' . _DB_NAME_ . '"';
            $check_active_col = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($id_attribute_value_column);
            if ((int) $check_active_col == 0) {
                Db::getInstance()->execute("ALTER TABLE " . _DB_PREFIX_ . "etsy_attribute_mapping1 ADD `id_attribute_value` text NOT NULL AFTER `id_attribute_group`");
            }
            $id_attribute_value_column = 'SELECT count(*) FROM information_schema.COLUMNS
                          WHERE COLUMN_NAME = "id_attribute_value"
                          AND TABLE_NAME = "' . _DB_PREFIX_ . 'etsy_attribute_mapping"
                          AND TABLE_SCHEMA = "' . _DB_NAME_ . '"';
            $check_active_col = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($id_attribute_value_column);
            if ((int) $check_active_col == 0) {
                Db::getInstance()->execute("ALTER TABLE " . _DB_PREFIX_ . "etsy_attribute_mapping ADD `id_attribute_value` text NOT NULL AFTER `id_attribute_group`");
            }
            /**
             * Added columns custom_property_id and listing_id in correspondence to new property_id handling logic for etsy
             * @modifier Himanshu Vishwakarma
             * @date 16-10-2025
             */
            $id_attribute_value_column = 'SELECT count(*) FROM information_schema.COLUMNS
                          WHERE COLUMN_NAME = "custom_property_id"
                          AND TABLE_NAME = "' . _DB_PREFIX_ . 'etsy_attribute_mapping1"
                          AND TABLE_SCHEMA = "' . _DB_NAME_ . '"';
            $check_active_col = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($id_attribute_value_column);
            if ((int) $check_active_col == 0) {
                Db::getInstance()->execute("ALTER TABLE " . _DB_PREFIX_ . "etsy_attribute_mapping1 ADD `custom_property_id` text NOT NULL AFTER `property_id`");
            }
            $id_attribute_value_column = 'SELECT count(*) FROM information_schema.COLUMNS
                          WHERE COLUMN_NAME = "listing_id"
                          AND TABLE_NAME = "' . _DB_PREFIX_ . 'etsy_attribute_mapping1"
                          AND TABLE_SCHEMA = "' . _DB_NAME_ . '"';
            $check_active_col = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($id_attribute_value_column);
            if ((int) $check_active_col == 0) {
                Db::getInstance()->execute("ALTER TABLE " . _DB_PREFIX_ . "etsy_attribute_mapping1 ADD `listing_id` text NOT NULL AFTER `custom_property_id`");
            }
            /**
             * Added by Tanisha to add column "region_iso" in etsy_regions table
             * @date 07-04-2023
             * @author Tanisha Gupta
             */
            $region_iso_column  = 'SELECT count(*) FROM information_schema.COLUMNS
                          WHERE COLUMN_NAME = "region_iso"
                          AND TABLE_NAME = "' . _DB_PREFIX_ . 'etsy_regions"
                          AND TABLE_SCHEMA = "' . _DB_NAME_ . '"';
            $region_iso_column_result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($region_iso_column);
            if ((int) $region_iso_column_result == 0) {
                Db::getInstance()->execute("ALTER TABLE `" . _DB_PREFIX_ . "etsy_regions` ADD `region_iso` varchar(10) DEFAULT NULL AFTER `region_name`");
            }
            /**
             * Added by Tanisha to add columns  in etsy_shipping_templates table
             * @date 07-04-2023
             * @author  Tanisha Gupta
             */
            $postal_code_column  = 'SELECT count(*) FROM information_schema.COLUMNS
                          WHERE COLUMN_NAME = "postal_code"
                          AND TABLE_NAME = "' . _DB_PREFIX_ . 'etsy_shipping_templates"
                          AND TABLE_SCHEMA = "' . _DB_NAME_ . '"';
            $postal_code_column_result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($postal_code_column);
            if ((int) $postal_code_column_result == 0) {
                Db::getInstance()->execute("ALTER TABLE `" . _DB_PREFIX_ . "etsy_shipping_templates` ADD `postal_code` varchar(20) DEFAULT NULL AFTER `shipping_origin_country`");
            }
            
            //End here 
            /*
             *To add columns  in etsy_shipping_templates_entries table
             * @date 12-04-2023
             * @author Tanisha Gupta
             */
        
            $transmit_type_column1  = 'SELECT count(*) FROM information_schema.COLUMNS
                          WHERE COLUMN_NAME = "shipping_entry_transmit_type"
                          AND TABLE_NAME = "' . _DB_PREFIX_ . 'etsy_shipping_templates_entries"
                          AND TABLE_SCHEMA = "' . _DB_NAME_ . '"';
            $transmit_type_column_result1  = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($transmit_type_column1);
            if ((int) $transmit_type_column_result1 == 0) {
                Db::getInstance()->execute("ALTER TABLE `" . _DB_PREFIX_ . "etsy_shipping_templates_entries` ADD `shipping_entry_transmit_type` varchar(30) NOT NULL AFTER `shipping_entry_secondary_cost`");
            }
            $carrier_column1  = 'SELECT count(*) FROM information_schema.COLUMNS
                          WHERE COLUMN_NAME = "shipping_entry_carrier_id"
                          AND TABLE_NAME = "' . _DB_PREFIX_ . 'etsy_shipping_templates_entries"
                          AND TABLE_SCHEMA = "' . _DB_NAME_ . '"';
            $carrier_column_result1 = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($carrier_column1);
            if ((int) $carrier_column_result1 == 0) {
                Db::getInstance()->execute("ALTER TABLE `" . _DB_PREFIX_ . "etsy_shipping_templates_entries` ADD `shipping_entry_carrier_id` int(11) DEFAULT NULL AFTER `shipping_entry_transmit_type`");
            }
            $mail_class_column1  = 'SELECT count(*) FROM information_schema.COLUMNS
                          WHERE COLUMN_NAME = "shipping_entry_mail_class_key"
                          AND TABLE_NAME = "' . _DB_PREFIX_ . 'etsy_shipping_templates_entries"
                          AND TABLE_SCHEMA = "' . _DB_NAME_ . '"';
            $mail_class_column_result1 = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($mail_class_column1);
            if ((int) $mail_class_column_result1 == 0) {
                Db::getInstance()->execute("ALTER TABLE `" . _DB_PREFIX_ . "etsy_shipping_templates_entries` ADD `shipping_entry_mail_class_key` varchar(200) DEFAULT NULL AFTER `shipping_entry_carrier_id`");
            }
            $min_delivery_column1  = 'SELECT count(*) FROM information_schema.COLUMNS
                          WHERE COLUMN_NAME = "shipping_entry_min_delivery_days"
                          AND TABLE_NAME = "' . _DB_PREFIX_ . 'etsy_shipping_templates_entries"
                          AND TABLE_SCHEMA = "' . _DB_NAME_ . '"';
            $min_delivery_column_result1 = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($min_delivery_column1);
            if ((int) $min_delivery_column_result1 == 0) {
                Db::getInstance()->execute("ALTER TABLE `" . _DB_PREFIX_ . "etsy_shipping_templates_entries` ADD `shipping_entry_min_delivery_days` int(2) NOT NULL AFTER `shipping_entry_mail_class_key`");
            }
            $max_delivery_column1  = 'SELECT count(*) FROM information_schema.COLUMNS
                          WHERE COLUMN_NAME = "shipping_entry_max_delivery_days"
                          AND TABLE_NAME = "' . _DB_PREFIX_ . 'etsy_shipping_templates_entries"
                          AND TABLE_SCHEMA = "' . _DB_NAME_ . '"';
            $max_delivery_column_result1 = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($max_delivery_column1);
            if ((int) $max_delivery_column_result1 == 0) {
                Db::getInstance()->execute("ALTER TABLE `" . _DB_PREFIX_ . "etsy_shipping_templates_entries` ADD `shipping_entry_max_delivery_days` int(2) NOT NULL AFTER `shipping_entry_min_delivery_days`");
            }
            
            //End: Added by Tanisha to add columns in etsy_shipping_templates_entries table
            
            /**
             * Added Column to the etsy_shipping_upgrades table
             * @date 12-04-2023
             * @author Tanisha Gupta
             */
            $transmit_type_column  = 'SELECT count(*) FROM information_schema.COLUMNS
                          WHERE COLUMN_NAME = "shipping_upgrade_transmit_type"
                          AND TABLE_NAME = "' . _DB_PREFIX_ . 'etsy_shipping_upgrades"
                          AND TABLE_SCHEMA = "' . _DB_NAME_ . '"';
            $transmit_type_column_result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($transmit_type_column);
            if ((int) $transmit_type_column_result == 0) {
                Db::getInstance()->execute("ALTER TABLE `" . _DB_PREFIX_ . "etsy_shipping_upgrades` ADD `shipping_upgrade_transmit_type` varchar(30) NOT NULL AFTER `shipping_upgrade_secondary_cost`");
            }
            $carrier_column  = 'SELECT count(*) FROM information_schema.COLUMNS
                          WHERE COLUMN_NAME = "shipping_upgrade_carrier_id"
                          AND TABLE_NAME = "' . _DB_PREFIX_ . 'etsy_shipping_upgrades"
                          AND TABLE_SCHEMA = "' . _DB_NAME_ . '"';
            $carrier_column_result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($carrier_column);
            if ((int) $carrier_column_result == 0) {
                Db::getInstance()->execute("ALTER TABLE `" . _DB_PREFIX_ . "etsy_shipping_upgrades` ADD `shipping_upgrade_carrier_id` int(11) DEFAULT NULL AFTER `shipping_upgrade_transmit_type`");
            }
            $mail_class_column  = 'SELECT count(*) FROM information_schema.COLUMNS
                          WHERE COLUMN_NAME = "shipping_upgrade_mail_class_key"
                          AND TABLE_NAME = "' . _DB_PREFIX_ . 'etsy_shipping_upgrades"
                          AND TABLE_SCHEMA = "' . _DB_NAME_ . '"';
            $mail_class_column_result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($mail_class_column);
            if ((int) $mail_class_column_result == 0) {
                Db::getInstance()->execute("ALTER TABLE `" . _DB_PREFIX_ . "etsy_shipping_upgrades` ADD `shipping_upgrade_mail_class_key` varchar(200) DEFAULT NULL AFTER `shipping_upgrade_carrier_id`");
            }
            $min_delivery_column  = 'SELECT count(*) FROM information_schema.COLUMNS
                          WHERE COLUMN_NAME = "shipping_upgrade_min_delivery_days"
                          AND TABLE_NAME = "' . _DB_PREFIX_ . 'etsy_shipping_upgrades"
                          AND TABLE_SCHEMA = "' . _DB_NAME_ . '"';
            $min_delivery_column_result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($min_delivery_column);
            if ((int) $min_delivery_column_result == 0) {
                Db::getInstance()->execute("ALTER TABLE `" . _DB_PREFIX_ . "etsy_shipping_upgrades` ADD `shipping_upgrade_min_delivery_days` int(2) NOT NULL AFTER `shipping_upgrade_mail_class_key`");
            }
            $max_delivery_column  = 'SELECT count(*) FROM information_schema.COLUMNS
                          WHERE COLUMN_NAME = "shipping_upgrade_max_delivery_days"
                          AND TABLE_NAME = "' . _DB_PREFIX_ . 'etsy_shipping_upgrades"
                          AND TABLE_SCHEMA = "' . _DB_NAME_ . '"';
            $max_delivery_column_result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($max_delivery_column);
            if ((int) $max_delivery_column_result == 0) {
                Db::getInstance()->execute("ALTER TABLE `" . _DB_PREFIX_ . "etsy_shipping_upgrades` ADD `shipping_upgrade_max_delivery_days` int(2) NOT NULL AFTER `shipping_upgrade_min_delivery_days`");
            }
            //End here 
            /*
             * Added migration code to update etsy_return_policy table structure to include only required fields: return_policy_id, shop_id, accepts_returns, accepts_exchanges, return_deadline
	     * @modifier Himanshu Vishwakarma
             * @date 15-12-2025
             */
            $check_column_exist = 'SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = "' . _DB_NAME_ . '" AND TABLE_NAME = "' . _DB_PREFIX_ . 'etsy_return_policy" AND COLUMN_NAME = "shop_id"';
            $column_result = Db::getInstance()->getRow($check_column_exist);
            if (!(is_array($column_result) && count($column_result) > 0)) {
                //Add shop_id column if it doesn't exist
                Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'etsy_return_policy` ADD `shop_id` varchar(20) NOT NULL AFTER `return_policy_id`');
            }
            $check_column_exist = 'SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = "' . _DB_NAME_ . '" AND TABLE_NAME = "' . _DB_PREFIX_ . 'etsy_return_policy" AND COLUMN_NAME = "accepts_returns"';
            $column_result = Db::getInstance()->getRow($check_column_exist);
            if (!(is_array($column_result) && count($column_result) > 0)) {
                //Add accepts_returns, accepts_exchanges, and return_deadline columns if they don't exist
                Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'etsy_return_policy` ADD `accepts_returns` tinyint(1) NOT NULL DEFAULT \'0\' AFTER `shop_id`, ADD `accepts_exchanges` tinyint(1) NOT NULL DEFAULT \'0\' AFTER `accepts_returns`, ADD `return_deadline` int(3) NOT NULL DEFAULT \'0\' AFTER `accepts_exchanges`');
            }
            //Added by Tanisha to add countries and regions in table on module installation
            $country_data_query = "SELECT count(*) as total FROM `" . _DB_PREFIX_ . "etsy_countries`";
            $country_data = Db::getInstance()->getRow($country_data_query);
            if ($country_data['total'] <= 0) {
                $etsy_country_data_query = "INSERT INTO `" . _DB_PREFIX_ . "etsy_countries` (`id_etsy_countries`, `country_id`, `country_name`,`iso_code`) VALUES
                (1, 55, 'Afghanistan', 'AF'),
                (2, 306, 'Aland Islands', 'AX'),
                (3, 57, 'Albania', 'AL'),
                (4, 95, 'Algeria', 'DZ'),
                (5, 250, 'American Samoa', 'AS'),
                (6, 228, 'Andorra', 'AD'),
                (7, 56, 'Angola', 'AO'),
                (8, 251, 'Anguilla', 'AI'),
                (9, 10, 'Antarctica', 'AQ'),
                (10, 252, 'Antigua and Barbuda', 'AG'),
                (11, 59, 'Argentina', 'AR'),
                (12, 60, 'Armenia', 'AM'),
                (13, 253, 'Aruba', 'AW'),
                (14, 61, 'Australia', 'AU'),
                (15, 62, 'Austria', 'AT'),
                (16, 63, 'Azerbaijan', 'AZ'),
                (17, 229, 'Bahamas', 'BS'),
                (18, 232, 'Bahrain', 'BH'),
                (19, 68, 'Bangladesh', 'BD'),
                (20, 237, 'Barbados', 'BB'),
                (21, 71, 'Belarus', 'BY'),
                (22, 65, 'Belgium', 'BE'),
                (23, 72, 'Belize', 'BZ'),
                (24, 66, 'Benin', 'BJ'),
                (25, 225, 'Bermuda', 'BM'),
                (26, 76, 'Bhutan', 'BT'),
                (27, 73, 'Bolivia', 'BO'),
                (28, 535, 'Bonaire, Sint Eustatius and Saba', 'BQ'),
                (29, 70, 'Bosnia and Herzegovina', 'BA'),
                (30, 77, 'Botswana', 'BW'),
                (31, 254, 'Bouvet Island', 'BV'),
                (32, 74, 'Brazil', 'BR'),
                (33, 255, 'British Indian Ocean Territory', 'IO'),
                (34, 231, 'British Virgin Islands', 'VG'),
                (35, 75, 'Brunei', 'BN'),
                (36, 69, 'Bulgaria', 'BG'),
                (37, 67, 'Burkina Faso', 'BF'),
                (38, 64, 'Burundi', 'BI'),
                (39, 135, 'Cambodia', 'KH'),
                (40, 84, 'Cameroon', 'CM'),
                (41, 79, 'Canada', 'CA'),
                (42, 222, 'Cape Verde', 'CV'),
                (43, 247, 'Cayman Islands', 'KY'),
                (44, 78, 'Central African Republic', 'CF'),
                (45, 196, 'Chad', 'TD'),
                (46, 81, 'Chile', 'CL'),
                (47, 82, 'China', 'CN'),
                (48, 257, 'Christmas Island', 'CX'),
                (49, 258, 'Cocos (Keeling) Islands', 'CC'),
                (50, 86, 'Colombia', 'CO'),
                (51, 259, 'Comoros', 'KM'),
                (52, 85, 'Congo, Republic of', 'CG'),
                (53, 260, 'Cook Islands', 'CK'),
                (54, 87, 'Costa Rica', 'CR'),
                (55, 118, 'Croatia', 'HR'),
                (56, 338, 'Curacao', 'CW'),
                (57, 89, 'Cyprus', 'CY'),
                (58, 90, 'Czech Republic', 'CZ'),
                (59, 93, 'Denmark', 'DK'),
                (60, 92, 'Djibouti', 'DJ'),
                (61, 261, 'Dominica', 'DM'),
                (62, 94, 'Dominican Republic', 'DO'),
                (63, 96, 'Ecuador', 'EC'),
                (64, 97, 'Egypt', 'EG'),
                (65, 187, 'El Salvador', 'SV'),
                (66, 111, 'Equatorial Guinea', 'GQ'),
                (67, 98, 'Eritrea', 'ER'),
                (68, 100, 'Estonia', 'EE'),
                (69, 101, 'Ethiopia', 'ET'),
                (70, 262, 'Falkland Islands (Malvinas)', 'FK'),
                (71, 241, 'Faroe Islands', 'FO'),
                (72, 234, 'Fiji', 'FJ'),
                (73, 102, 'Finland', 'FI'),
                (74, 103, 'France', 'FR'),
                (75, 115, 'French Guiana', 'GF'),
                (76, 263, 'French Polynesia', 'PF'),
                (77, 264, 'French Southern Territories', 'TF'),
                (78, 104, 'Gabon', 'GA'),
                (79, 109, 'Gambia', 'GM'),
                (80, 106, 'Georgia', 'GE'),
                (81, 91, 'Germany', 'DE'),
                (82, 107, 'Ghana', 'GH'),
                (83, 226, 'Gibraltar', 'GI'),
                (84, 112, 'Greece', 'GR'),
                (85, 113, 'Greenland', 'GL'),
                (86, 245, 'Grenada', 'GD'),
                (87, 265, 'Guadeloupe', 'GP'),
                (88, 266, 'Guam', 'GU'),
                (89, 114, 'Guatemala', 'GT'),
                (90, 305, 'Guernsey', 'GG'),
                (91, 108, 'Guinea', 'GN'),
                (92, 110, 'Guinea-Bissau', 'GW'),
                (93, 116, 'Guyana', 'GY'),
                (94, 119, 'Haiti', 'HT'),
                (95, 267, 'Heard Island and McDonald Islands', 'HM'),
                (96, 268, 'Holy See (Vatican City State)', 'VA'),
                (97, 117, 'Honduras', 'HN'),
                (98, 219, 'Hong Kong', 'HK'),
                (99, 120, 'Hungary', 'HU'),
                (100, 126, 'Iceland', 'IS'),
                (101, 122, 'India', 'IN'),
                (102, 121, 'Indonesia', 'ID'),
                (103, 125, 'Iraq', 'IQ'),
                (104, 123, 'Ireland', 'IE'),
                (105, 269, 'Isle of Man', 'IM'),
                (106, 127, 'Israel', 'IL'),
                (107, 128, 'Italy', 'IT'),
                (108, 83, 'Ivory Coast', 'IC'),
                (109, 129, 'Jamaica', 'JM'),
                (110, 131, 'Japan', 'JP'),
                (111, 307, 'Jersey', 'JE'),
                (112, 130, 'Jordan', 'JO'),
                (113, 132, 'Kazakhstan', 'KZ'),
                (114, 133, 'Kenya', 'KE'),
                (115, 270, 'Kiribati', 'KI'),
                (116, 271, 'Kosovo', 'KV'),
                (117, 137, 'Kuwait', 'KW'),
                (118, 134, 'Kyrgyzstan', 'KG'),
                (119, 138, 'Laos', 'LA'),
                (120, 146, 'Latvia', 'LV'),
                (121, 139, 'Lebanon', 'LB'),
                (122, 143, 'Lesotho', 'LS'),
                (123, 140, 'Liberia', 'LR'),
                (124, 141, 'Libya', 'LY'),
                (125, 272, 'Liechtenstein', 'LI'),
                (126, 144, 'Lithuania', 'LT'),
                (127, 145, 'Luxembourg', 'LU'),
                (128, 273, 'Macao', 'MO'),
                (129, 151, 'Macedonia', 'MK'),
                (130, 149, 'Madagascar', 'MG'),
                (131, 158, 'Malawi', 'MW'),
                (132, 159, 'Malaysia', 'MY'),
                (133, 238, 'Maldives', 'MV'),
                (134, 152, 'Mali', 'ML'),
                (135, 227, 'Malta', 'MT'),
                (136, 274, 'Marshall Islands', 'MH'),
                (137, 275, 'Martinique', 'MQ'),
                (138, 157, 'Mauritania', 'MR'),
                (139, 239, 'Mauritius', 'MU'),
                (140, 276, 'Mayotte', 'YT'),
                (141, 150, 'Mexico', 'MX'),
                (142, 277, 'Micronesia, Federated States of', 'FM'),
                (143, 148, 'Moldova', 'MD'),
                (144, 278, 'Monaco', 'MC'),
                (145, 154, 'Mongolia', 'MN'),
                (146, 155, 'Montenegro', 'ME'),
                (147, 279, 'Montserrat', 'MS'),
                (148, 147, 'Morocco', 'MA'),
                (149, 156, 'Mozambique', 'MZ'),
                (150, 153, 'Myanmar (Burma)', 'MM'),
                (151, 160, 'Namibia', 'NA'),
                (152, 280, 'Nauru', 'NR'),
                (153, 166, 'Nepal', 'NP'),
                (154, 243, 'Netherlands Antilles', 'AN'),
                (155, 233, 'New Caledonia', 'NC'),
                (156, 167, 'New Zealand', 'NZ'),
                (157, 163, 'Nicaragua', 'NI'),
                (158, 161, 'Niger', 'NE'),
                (159, 162, 'Nigeria', 'NG'),
                (160, 281, 'Niue', 'NU'),
                (161, 282, 'Norfolk Island', 'NF'),
                (162, 283, 'Northern Mariana Islands', 'MP'),
                (163, 165, 'Norway', 'NO'),
                (164, 168, 'Oman', 'OM'),
                (165, 169, 'Pakistan', 'PK'),
                (166, 284, 'Palau', 'PW'),
                (167, 285, 'Palestine, State of', 'PS'),
                (168, 170, 'Panama', 'PA'),
                (169, 173, 'Papua New Guinea', 'PG'),
                (170, 178, 'Paraguay', 'PY'),
                (171, 171, 'Peru', 'PE'),
                (172, 172, 'Philippines', 'PH'),
                (173, 174, 'Poland', 'PL'),
                (174, 177, 'Portugal', 'PT'),
                (175, 175, 'Puerto Rico', 'PR'),
                (176, 179, 'Qatar', 'QA'),
                (177, 304, 'Reunion', 'RE'),
                (178, 180, 'Romania', 'RO'),
                (179, 181, 'Russia', 'RU'),
                (180, 182, 'Rwanda', 'RW'),
                (181, 308, 'Saint Barth?lemy', 'BL'),
                (182, 286, 'Saint Helena', 'SH'),
                (183, 287, 'Saint Kitts and Nevis', 'KN'),
                (184, 244, 'Saint Lucia', 'LC'),
                (185, 288, 'Saint Martin (French part)', 'MF'),
                (186, 289, 'Saint Pierre and Miquelon', 'PM'),
                (187, 249, 'Saint Vincent and the Grenadines', 'VC'),
                (188, 290, 'Samoa', 'WS'),
                (189, 291, 'San Marino', 'SM'),
                (190, 292, 'Sao Tome and Principe', 'ST'),
                (191, 183, 'Saudi Arabia', 'SA'),
                (192, 185, 'Senegal', 'SN'),
                (193, 189, 'Serbia', 'RS'),
                (194, 891, 'Serbia and Montenegro', 'CS'),
                (195, 293, 'Seychelles', 'SC'),
                (196, 186, 'Sierra Leone', 'SL'),
                (197, 220, 'Singapore', 'SG'),
                (198, 337, 'Sint Maarten (Dutch part)', 'SX'),
                (199, 191, 'Slovakia', 'SK'),
                (200, 192, 'Slovenia', 'SI'),
                (201, 242, 'Solomon Islands', 'SB'),
                (202, 188, 'Somalia', 'SO'),
                (203, 215, 'South Africa', 'ZA'),
                (204, 294, 'South Georgia and the South Sandwich Islands', 'GS'),
                (205, 136, 'South Korea', 'KR'),
                (206, 339, 'South Sudan', 'SS'),
                (207, 99, 'Spain', 'ES'),
                (208, 142, 'Sri Lanka', 'LK'),
                (209, 184, 'Sudan', 'SD'),
                (210, 190, 'Suriname', 'SR'),
                (211, 295, 'Svalbard and Jan Mayen', 'SJ'),
                (212, 194, 'Swaziland', 'SZ'),
                (213, 193, 'Sweden', 'SE'),
                (214, 80, 'Switzerland', 'CH'),
                (215, 204, 'Taiwan', 'TW'),
                (216, 199, 'Tajikistan', 'TJ'),
                (217, 205, 'Tanzania', 'TZ'),
                (218, 198, 'Thailand', 'TH'),
                (219, 164, 'The Netherlands', 'NL'),
                (220, 296, 'Timor-Leste', 'TL'),
                (221, 197, 'Togo', 'TG'),
                (222, 297, 'Tokelau', 'TK'),
                (223, 298, 'Tonga', 'TO'),
                (224, 201, 'Trinidad', 'TT'),
                (225, 202, 'Tunisia', 'TN'),
                (226, 203, 'Turkey', 'TR'),
                (227, 200, 'Turkmenistan', 'TM'),
                (228, 299, 'Turks and Caicos Islands', 'TC'),
                (229, 300, 'Tuvalu', 'TV'),
                (230, 206, 'Uganda', 'UG'),
                (231, 207, 'Ukraine', 'UA'),
                (232, 58, 'United Arab Emirates', 'AE'),
                (233, 105, 'United Kingdom', 'GB'),
                (234, 209, 'United States', 'US'),
                (235, 302, 'United States Minor Outlying Islands', 'UM'),
                (236, 208, 'Uruguay', 'UY'),
                (237, 248, 'U.S. Virgin Islands', 'VI'),
                (238, 210, 'Uzbekistan', 'UZ'),
                (239, 221, 'Vanuatu', 'VU'),
                (240, 211, 'Venezuela', 'VE'),
                (241, 212, 'Vietnam', 'VN'),
                (242, 224, 'Wallis and Futuna', 'WF'),
                (243, 213, 'Western Sahara', 'EH'),
                (244, 214, 'Yemen', 'YE'),
                (245, 216, 'Zaire (Democratic Republic of Congo)', 'CD'),
                (246, 217, 'Zambia', 'ZM'),
                (247, 218, 'Zimbabwe', 'ZW')";
                Db::getInstance()->execute($etsy_country_data_query);
            }
            $region_data_query = "SELECT count(*) as total FROM `" . _DB_PREFIX_ . "etsy_regions`";
            $region_data = Db::getInstance()->getRow($region_data_query);
            if ($region_data['total'] <= 0) {
                $etsy_region_data_query = "INSERT INTO `" . _DB_PREFIX_ . "etsy_regions` (`id_etsy_regions`, `region_id`, `region_name`, `region_iso`) VALUES
                (1, 11, 'European Union', 'eu'),
                (2, 12, 'Europe non-EU', 'non_eu')";
                Db::getInstance()->execute($etsy_region_data_query);
            }
        }
        if ($installation_error) {
            return false;
        } else {
            return true;
        }
    }
    protected function installEtsyTabs()
    {
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $lang = Language::getLanguages();
            //Admin tabs for Etsy Marketplace module
            if ($this->installModuleTabs('AdminEtsyModule', $this->module->l('Etsy Marketplace', 'KbEtsy'), 0, 1)) {
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
                $parentTab->name[$lang['id_lang']] = $this->module->l('Etsy Marketplace', 'KbEtsy');
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
                    $tab->name[$lang['id_lang']] = $this->module->l($menu['name'], 'KbEtsy');
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
                    'name' => $this->module->l('General Settings', 'KbEtsy'),
                    'parent_id' => Tab::getIdFromClassName('AdminEtsyModule'),
                    'active' => 1,
                ),
                array(
                    'class' => 'AdminEtsyAttributeMapping',
                    'name' => $this->module->l('Attribute Mapping', 'KbEtsy'),
                    'parent_id' => Tab::getIdFromClassName('AdminEtsyModule'),
                    'active' => 1,
                ),
                array(
                    'class' => 'AdminEtsyShopSection',
                    'name' => $this->module->l('Shop Section', 'KbEtsy'),
                    'parent_id' => Tab::getIdFromClassName('AdminEtsyModule'),
                    'active' => 1,
                ),
                /*
                 * Added return policy tab entry in admin menu
		 * @modifier Himanshu Vishwakarma
                 * @date 15-12-2025
                 */
                array(
                    'class' => 'AdminEtsyReturnPolicy',
                    'name' => $this->module->l('Return Policy', 'KbEtsy'),
                    'parent_id' => Tab::getIdFromClassName('AdminEtsyModule'),
                    'active' => 1,
                ),
                array(
                    'class' => 'AdminEtsyShippingTemplates',
                    'name' => $this->module->l('Shipping Templates', 'KbEtsy'),
                    'parent_id' => Tab::getIdFromClassName('AdminEtsyModule'),
                    'active' => 1,
                ),
                array(
                    'class' => 'AdminEtsyShippingTemplatesEntries',
                    'name' => $this->module->l('Shipping Templates Entries', 'KbEtsy'),
                    'parent_id' => Tab::getIdFromClassName('AdminEtsyModule'),
                    'active' => 0,
                ),
                array(
                    'class' => 'AdminEtsyShippingUpgrades',
                    'name' => $this->module->l('Shipping Upgrades', 'KbEtsy'),
                    'parent_id' => Tab::getIdFromClassName('AdminEtsyModule'),
                    'active' => 0,
                ),
                array(
                    'class' => 'AdminEtsyOrderSettings',
                    'name' => $this->module->l('Order Settings', 'KbEtsy'),
                    'parent_id' => Tab::getIdFromClassName('AdminEtsyModule'),
                    'active' => 0,
                ),
                array(
                    'class' => 'AdminEtsyProfileManagement',
                    'name' => $this->module->l('Profile Management', 'KbEtsy'),
                    'parent_id' => Tab::getIdFromClassName('AdminEtsyModule'),
                    'active' => 1,
                ),
                array(
                    'class' => 'AdminEtsyProductsListing',
                    'name' => $this->module->l('Products Listing', 'KbEtsy'),
                    'parent_id' => Tab::getIdFromClassName('AdminEtsyModule'),
                    'active' => 1,
                ),
                array(
                    'class' => 'AdminEtsyOrdersListing',
                    'name' => $this->module->l('Orders Listing', 'KbEtsy'),
                    'parent_id' => Tab::getIdFromClassName('AdminEtsyModule'),
                    'active' => 1,
                ),
                array(
                    'class' => 'AdminEtsyCustomersListing',
                    'name' => $this->module->l('Customers Listing', 'KbEtsy'),
                    'parent_id' => Tab::getIdFromClassName('AdminEtsyModule'),
                    'active' => 1,
                ),
                array(
                    'class' => 'AdminEtsyImport',
                    'name' => $this->module->l('Import Products From Etsy', 'KbEtsy'),
                    'parent_id' => Tab::getIdFromClassName('AdminEtsyModule'),
                    'active' => 0,
                ),
                array(
                    'class' => 'AdminEtsySynchronization',
                    'name' => $this->module->l('Synchronization', 'KbEtsy'),
                    'parent_id' => Tab::getIdFromClassName('AdminEtsyModule'),
                    'active' => 1,
                ),
                array(
                    'class' => 'AdminEtsySalesReport',
                    'name' => $this->module->l('Sales Report', 'KbEtsy'),
                    'parent_id' => Tab::getIdFromClassName('AdminEtsyModule'),
                    'active' => 1,
                ),
                array(
                    'class' => 'AdminEtsyProductSalesReport',
                    'name' => $this->module->l('Product Sales Report', 'KbEtsy'),
                    'parent_id' => Tab::getIdFromClassName('AdminEtsyModule'),
                    'active' => 1,
                ),
                array(
                    'class' => 'AdminEtsyAuditLog',
                    'name' => $this->module->l('Audit Log', 'KbEtsy'),
                    'parent_id' => Tab::getIdFromClassName('AdminEtsyModule'),
                    'active' => 1,
                ),
            );
        } else {
            return array(
                array(
                    'class_name' => 'AdminEtsyGeneralSettings',
                    'active' => 1,
                    'name' => $this->module->l('General Settings', 'KbEtsy')
                ),
                array(
                    'class_name' => 'AdminEtsyAttributeMapping',
                    'active' => 1,
                    'name' => $this->module->l('Attribute Mapping', 'KbEtsy')
                ),
                array(
                    'class_name' => 'AdminEtsyShopSection',
                    'active' => 1,
                    'name' => $this->module->l('Shop Section', 'KbEtsy'),
                ),
                /*
                 * Added return policy tab entry in admin menu
		 * @modifier Himanshu Vishwakarma
                 * @date 15-12-2025
                 */
                array(
                    'class_name' => 'AdminEtsyReturnPolicy',
                    'active' => 1,
                    'name' => $this->module->l('Return Policy', 'KbEtsy'),
                ),
                array(
                    'class_name' => 'AdminEtsyShippingTemplates',
                    'active' => 1,
                    'name' => $this->module->l('Shipping Templates', 'KbEtsy')
                ),
                array(
                    'class_name' => 'AdminEtsyShippingTemplatesEntries',
                    'active' => 0,
                    'name' => $this->module->l('Shipping Templates Entries', 'KbEtsy')
                ),
                array(
                    'class_name' => 'AdminEtsyShippingUpgrades',
                    'name' => $this->module->l('Shipping Upgrades', 'KbEtsy'),
                    'active' => 0
                ),
                array(
                    'class_name' => 'AdminEtsyProfileManagement',
                    'active' => 1,
                    'name' => $this->module->l('Profile Management', 'KbEtsy')
                ),
                array(
                    'class_name' => 'AdminEtsyProductsListing',
                    'active' => 1,
                    'name' => $this->module->l('Products Listing', 'KbEtsy')
                ),
                array(
                    'class_name' => 'AdminEtsyOrdersListing',
                    'active' => 1,
                    'name' => $this->module->l('Orders Listing', 'KbEtsy')
                ),
                array(
                    'class_name' => 'AdminEtsyCustomersListing',
                    'active' => 1,
                    'name' => $this->module->l('Customers Listing', 'KbEtsy'),
                ),
                array(
                    'class_name' => 'AdminEtsyImport',
                    'active' => 0,
                    'name' => $this->module->l('Import Products From Etsy', 'KbEtsy'),
                ),
                array(
                    'class_name' => 'AdminEtsySynchronization',
                    'active' => 1,
                    'name' => $this->module->l('Synchronization', 'KbEtsy')
                ),
                array(
                    'class_name' => 'AdminEtsySalesReport',
                    'active' => 1,
                    'name' => $this->module->l('Sales Report', 'KbEtsy'),
                ),
                array(
                    'class_name' => 'AdminEtsyProductSalesReport',
                    'active' => 1,
                    'name' => $this->module->l('Product Sales Report', 'KbEtsy'),
                ),
                array(
                    'class_name' => 'AdminEtsyAuditLog',
                    'active' => 1,
                    'name' => $this->module->l('Audit Log', 'KbEtsy'),
                ),
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
        if (!empty($params['id_product'])) {
            $quantity_data = Db::getInstance()->getRow('SELECT quantity FROM ' . _DB_PREFIX_ . 'stock_available WHERE id_product_attribute = 0 AND id_product = ' . (int) $params['id_product']);

            $update = ($quantity_data['quantity'] > 0) ? 1 : 0;

            Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET "
                    . "is_error = '0' WHERE "
                    . "id_product = '" . (int) $params['id_product'] . "'");

            if (!(bool) $params['product']->active) {
                $update = 0;
                Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET "
                        . "listing_status = 'Inactive', "
                        . "delete_flag = '1', "
                        . "renew_flag = '0', "
                        . "is_error = '0' "
                        . "WHERE id_product = '" . (int) $params['id_product'] . "' "
                        . "AND listing_id IS NOT NULL");
            }
            //'Pending','Listed','Inactive','Expired','Draft','Deletion Pending','Updated','Sold Out','Relisting'
            Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET "
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
                Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list "
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
        Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list "
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
