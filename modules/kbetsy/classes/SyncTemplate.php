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

if (!defined('_PS_VERSION_')) {
    exit;
}

class SyncTemplate extends Module
{

    public function __construct()
    {
        parent::__construct();
    }

    public static function getAllExistingShippingTemplates()
    {
        $method_name = 'SyncTemplate::getAllExistingShippingTemplates()';
        EtsyModule::auditLogEntry('Job execution started to import shipping templates from etsy to prestashop.', $method_name);
        /**
         * Made changes to fetch as shop id is required in url
         * @date 10-04-2023
         * @author Tanisha Gupta
         */
        $shop = EtsyModule::etsyGetShopDetails();
        if (isset($shop['shop_id'])) {
            $etsy_shipping_templates = array();
            $etsyQueryString = array();
            //$etsyRequestURI = '/users/' . Configuration::get('etsy_api_user_id') . '/shipping/templates?limit=100';
            $etsyRequestURI = '/shops/' . $shop['shop_id'] . '/shipping-profiles';
            $etsyRequestMethod = 'GET';
            $shipping_templates = EtsyModule::etsyGetResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString);

            if (!empty($shipping_templates['results'])) {
                foreach ($shipping_templates['results'] as $shippingTemplateDetails) {
                    $shipping_template_id = $shippingTemplateDetails['shipping_profile_id'];
                    $title = $shippingTemplateDetails['title'];
		    /*
		    * If minimum and maximum processing days are not set then setting it to 1
		    * @modifier Himanshu Vishwakarma
     		    * @date 13-10-2025
		    */
                    $min_processing_days = $shippingTemplateDetails['min_processing_days'] ?? 1;
                    $max_processing_days = $shippingTemplateDetails['max_processing_days'] ?? 1;
                    /**
                     * In v3 response, country code is getting instead of country id. So, fetch the country id and country name based on the country iso
                     * @date 10-04-2023
                     * @author Tanisha Gupta
                     */
                    //$origin_country_id = $shippingTemplateDetails['origin_country_id'];
                    //$shippingOriginCountryName = EtsyModule::etsyGetCountryNameByCountryId($origin_country_id);
                    $origin_country_iso = $shippingTemplateDetails['origin_country_iso'];
                    $shippingOriginCountryData = EtsyModule::etsyGetCountryByIsoCode($origin_country_iso);

                    $origin_country_id = $shippingOriginCountryData[0]['country_id'];
                    $shippingOriginCountryName = $shippingOriginCountryData[0]['country_name'];
                    $etsy_shipping_templates[] = $shippingTemplateDetails['shipping_profile_id'];
                    /**
                     * Changes done to save postal code as well into the database
                     * @date 10-04-2023
                     * @author Tanisha Gupta
                     */
                    $origin_postal_code = $shippingTemplateDetails['origin_postal_code'];
                    $checkShippingTemplateExistQuery = "SELECT * FROM " . _DB_PREFIX_ . "etsy_shipping_templates WHERE shipping_template_id = '" . pSQL($shippingTemplateDetails['shipping_profile_id']) . "'";
                    $result = Db::getInstance()->getRow($checkShippingTemplateExistQuery);
                    $id_etsy_shipping_templates = "";
                    /**
                     * Added mb_convert_encoding before inserting the title to fix the encoding issue
                     * @date 17-04-2023
                     * @author Tanisha Gupta
                     */
                    if ($result === false) {
		    	        /**
                         * Updated the query according to new sync error Db column.
                         * Etsy001-Mar-2024 etsy-handle-template-sync
                         * @date 10-03-2024
                         * @author Ashish
                         */
                        $add_result = Db::getInstance()->execute("INSERT INTO " . _DB_PREFIX_ . "etsy_shipping_templates VALUES (NULL, '" . pSQL($shipping_template_id) . "', '" . pSQL(mb_convert_encoding($title, 'UTF-8', 'HTML-ENTITIES')) . "', '" . (int) $origin_country_id . "', '" . pSQL($shippingOriginCountryName) . "', '" . pSQL($origin_postal_code) . "', '', '', '" . (int) $min_processing_days . "', '" . (int) $max_processing_days . "', '0', '0', '', NOW(), NOW())");
                        if ($add_result) {
                            $id_etsy_shipping_templates = Db::getInstance()->Insert_ID();
                            EtsyModule::auditLogEntry('Shipping template synced. Added template is<br>Shipping Template Title: ' . $title, $method_name);
                        }
                    } else {
                        Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_shipping_templates SET "
                            . "shipping_template_title = '" . pSQL(mb_convert_encoding($title, 'UTF-8', 'HTML-ENTITIES')) . "',"
                            . "shipping_origin_country_id = '" . (int) $origin_country_id . "',"
                            . "shipping_origin_country = '" . pSQL($shippingOriginCountryName) . "',"
                            /**
                             * To save postal code
                             * @date 10-04-2023
                             * @author Tanisha Gupta
                             */
                            . "postal_code = '" . pSQL($origin_postal_code) . "',"
                            . "shipping_min_process_days = '" . (int) $min_processing_days . "',"
                            . "shipping_max_process_days = '" . (int) $max_processing_days . "' "
                            . "WHERE shipping_template_id = '" . pSQL($shippingTemplateDetails['shipping_profile_id']) . "'");

                        $id_etsy_shipping_templates = $result['id_etsy_shipping_templates'];
                    }
                    if ((int) $id_etsy_shipping_templates) {
                        /**
                         * Added parameter shop id as there is no need fetch the same again and again
                         * @date 10-04-2023
                         * @author Tanisha Gupta
                         */
                        self::syncShippingEntires($shipping_template_id, $id_etsy_shipping_templates, $shop['shop_id']);
                        self::syncUpgrades($shipping_template_id, $id_etsy_shipping_templates, $shop['shop_id']);
                    }
                }
                self::deleteEtsyDeletedTemplates($etsy_shipping_templates);
            } else {
                EtsyModule::auditLogEntry($shipping_templates['error'], $method_name);
            }
        } else {
            EtsyModule::auditLogEntry($shop['error'], $method_name);
        }
        EtsyModule::auditLogEntry('Job execution completed to import shipping templates from etsy to prestashop.', $method_name);
        return true;
    }

    /** Sync Shipping Templates from Etsy to Db */
    public static function syncShippingEntires($shipping_template_id, $id_etsy_shipping_templates, $shopid)
    {
        $etsyQueryString = array();
        /**
         * Set url to fetch the ShopShippingProfileDestinations
         * @date 10-04-2023
         * @author Tanisha Gupta
         */
        //$etsyRequestURI = '/shipping/templates/' . $shipping_template_id . '/entries?limit=500';
        $etsyRequestURI = '/shops/' . $shopid . '/shipping-profiles/' . $shipping_template_id . '/destinations';
        $etsyRequestMethod = 'GET';
        $template_entries = EtsyModule::etsyGetResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString);
        Db::getInstance()->execute("DELETE FROM " . _DB_PREFIX_ . "etsy_shipping_templates_entries WHERE id_etsy_shipping_templates = '" . (int) $id_etsy_shipping_templates . "' AND (shipping_template_entry_id != 0 AND shipping_template_entry_id IS NOT NULL AND shipping_template_entry_id != '')");
        if (!empty($template_entries['results'])) {
            foreach ($template_entries['results'] as $template_entry) {
                $shippingDestinationCountryName = null;
                $shippingDestinationRegionName = null;
                $destination_country_id = '';
                $destination_region_id = 0;
                /**
                 * Made changes according to the country iso as in response destination_country_iso is getting
                 * @date 10-04-2023
                 * @author Tanisha Gupta
                 */
                if ($template_entry['destination_country_iso'] != "") {
                    /**
                     * In v3 response, country code is getting instead of country id. So, fetch the country id and country name based on the country iso
                     * @date 10-04-2023
                     * @author Tanisha Gupta
                     */

                    $shippingDestinationCountryData = EtsyModule::etsyGetCountryByIsoCode($template_entry['destination_country_iso']);
                    $destination_country_id = $shippingDestinationCountryData[0]['country_id'];
                    $shippingDestinationCountryName = $shippingDestinationCountryData[0]['country_name'];
                    //$shippingDestinationCountryName = EtsyModule::etsyGetCountryNameByCountryId($template_entry['destination_country_id']);
                    /**
                     * Made changes according to the region iso as in response destination_region is getting
                     * @date 10-04-2023
                     * @author Tanisha Gupta
                     */
                } else if ($template_entry['destination_region'] != 'none') {
                    //$shippingDestinationRegionName = EtsyModule::etsyGetRegionNameByRegionId($template_entry['destination_region_id']);
                    $shippingDestinationRegionData = EtsyModule::etsyGetRegionByIsoCode($template_entry['destination_region']);
                    if (!empty($shippingDestinationRegionData) && count($shippingDestinationRegionData) > 0) {
                        $shippingDestinationRegionName = $shippingDestinationRegionData[0]['region_name'];
                        $destination_region_id = $shippingDestinationRegionData[0]['region_id'];
                    }
                }
                /**
                 * Set Carrier and min or max delivery days data
                 * @date 10-04-2023
                 * @author Tanisha Gupta
                 */
                if (($template_entry['shipping_carrier_id'] > 0) && ($template_entry['mail_class'] != '')) {
                    $transmit_data = 'shipping_carrier';
                } else {
                    $transmit_data = 'time_delivery';
                }
                if (empty($template_entry['min_delivery_days']) && empty($template_entry['max_delivery_days'])) {
                    $template_entry['min_delivery_days'] = 0;
                    $template_entry['max_delivery_days'] = 0;
                }
                if (empty($template_entry['shipping_carrier_id'])) {
                    $template_entry['shipping_carrier_id'] = 0;
                }
                $primary_cost = 0.0;
                $secondary_cost = 0.0;
                $primary_cost = (float)$template_entry['primary_cost']['amount'] / $template_entry['primary_cost']['divisor'];
                $secondary_cost = (float)$template_entry['secondary_cost']['amount'] / $template_entry['secondary_cost']['divisor'];
                if ($template_entry['destination_region'] != 'none') {
                    /* If Region ID etnry for shipping template exist, then don't add more entry because others are duplicate */

                    $template_entry_region_check = Db::getInstance()->getValue("SELECT count(*) as total FROM " . _DB_PREFIX_ . "etsy_shipping_templates_entries WHERE id_etsy_shipping_templates = " . $id_etsy_shipping_templates . " AND shipping_entry_destination_region_id = " . (int) $destination_region_id);
                    if ($template_entry_region_check > 0) {
                        continue;
                    }

                    $templateSQL = "INSERT INTO " . _DB_PREFIX_ . "etsy_shipping_templates_entries VALUES ("
                        . "NULL,"
                        . "'" . (int) $id_etsy_shipping_templates . "',"
                        . "'" . pSQL($template_entry['shipping_profile_destination_id']) . "',"
                        . "NULL, "
                        . "NULL,"
                        . "'" . pSQL($primary_cost) . "',"
                        . "'" . pSQL($secondary_cost) . "',"
                        . "'" . pSQL($transmit_data) . "',"
                        . "'" . pSQL($template_entry['shipping_carrier_id']) . "',"
                        . "'" . pSQL($template_entry['mail_class']) . "',"
                        . "'" . pSQL($template_entry['min_delivery_days']) . "',"
                        . "'" . pSQL($template_entry['max_delivery_days']) . "',"
                        . "'" . pSQL($destination_region_id) . "',"
                        . "'" . pSQL($shippingDestinationRegionName) . "',"
                        . "'0',"
                        . "'0',"
                        . "NOW(),"
                        . "NOW())";
                } else if ($template_entry['destination_country_iso'] != '') {
                    $templateSQL = "INSERT INTO " . _DB_PREFIX_ . "etsy_shipping_templates_entries VALUES "
                        . "(NULL,"
                        . "'" . (int) $id_etsy_shipping_templates . "',"
                        . "'" . pSQL($template_entry['shipping_profile_destination_id']) . "',"
                        . "'" . pSQL($destination_country_id) . "',"
                        . "'" . pSQL($shippingDestinationCountryName) . "',"
                        . "'" . pSQL($primary_cost) . "',"
                        . "'" . pSQL($secondary_cost) . "',"
                        . "'" . pSQL($transmit_data) . "',"
                        . "'" . pSQL($template_entry['shipping_carrier_id']) . "',"
                        . "'" . pSQL($template_entry['mail_class']) . "',"
                        . "'" . pSQL($template_entry['min_delivery_days']) . "',"
                        . "'" . pSQL($template_entry['max_delivery_days']) . "',"
                        . "NULL,"
                        . "NULL,"
                        . "'0',"
                        . "'0',"
                        . "NOW(),"
                        . "NOW())";
                } else {
                    /* This condition is for anywhere else region */
                    $templateSQL = "INSERT INTO " . _DB_PREFIX_ . "etsy_shipping_templates_entries VALUES "
                        . "(NULL,"
                        . "'" . (int) $id_etsy_shipping_templates . "',"
                        . "'" . pSQL($template_entry['shipping_profile_destination_id']) . "',"
                        . "'',"
                        . "'',"
                        . "'" . pSQL($primary_cost) . "',"
                        . "'" . pSQL($secondary_cost) . "',"
                        . "'" . pSQL($transmit_data) . "',"
                        . "'" . pSQL($template_entry['shipping_carrier_id']) . "',"
                        . "'" . pSQL($template_entry['mail_class']) . "',"
                        . "'" . pSQL($template_entry['min_delivery_days']) . "',"
                        . "'" . pSQL($template_entry['max_delivery_days']) . "',"
                        . "NULL,"
                        . "NULL,"
                        . "'0',"
                        . "'0',"
                        . "NOW(),"
                        . "NOW())";
                }
                Db::getInstance()->execute($templateSQL);
            }
        }
    }

    /** Sync Shipping Upgrades from Etsy to the Db */
    public static function syncUpgrades($shipping_template_id, $id_etsy_shipping_templates, $shopid)
    {
        $etsyQueryString = array();
        // https://openapi.etsy.com/v3/application/shops/{shop_id}/shipping-profiles/{shipping_profile_id}/upgrades
        // $etsyRequestURI = '/shipping/templates/' . $shipping_template_id . '/upgrades?limit=100';
        $etsyRequestURI = '/shops/' . $shopid . '/shipping-profiles/' . $shipping_template_id . '/upgrades';
        $etsyRequestMethod = 'GET';

        $shipping_upgrades = EtsyModule::etsyGetResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString);
        Db::getInstance()->execute("DELETE FROM " . _DB_PREFIX_ . "etsy_shipping_upgrades WHERE id_etsy_shipping_templates = '" . (int) $id_etsy_shipping_templates . "' AND (shipping_upgrade_id != 0 AND shipping_upgrade_id IS NOT NULL AND shipping_upgrade_id != '')");
        if (!empty($shipping_upgrades['results'])) {
            foreach ($shipping_upgrades['results'] as $key => $shipping_upgrade) {
                /**
                 * Set Carrier and min or max delivery days data
                 * @date 10-04-2023
                 * @author Tanisha Gupta
                 */
                if (($shipping_upgrade['shipping_carrier_id'] > 0) && ($shipping_upgrade['mail_class'] != '')) {
                    $transmit_data = 'shipping_carrier';
                } else {
                    $transmit_data = 'time_delivery';
                }
                if (empty($shipping_upgrade['min_delivery_days']) && empty($shipping_upgrade['max_delivery_days'])) {
                    $shipping_upgrade['min_delivery_days'] = 0;
                    $shipping_upgrade['max_delivery_days'] = 0;
                }
                if (empty($shipping_upgrade['shipping_carrier_id'])) {
                    $shipping_upgrade['shipping_carrier_id'] = 0;
                }
                $primary_cost = 0.0;
                $secondary_cost = 0.0;
                $primary_cost = (float)$shipping_upgrade['price']['amount'] / $shipping_upgrade['price']['divisor'];
                $secondary_cost = (float)$shipping_upgrade['secondary_price']['amount'] / $shipping_upgrade['secondary_price']['divisor'];
                Db::getInstance()->execute("INSERT INTO " . _DB_PREFIX_ . "etsy_shipping_upgrades "
                    . "VALUES ("
                    . "NULL, "
                    . "'" . (int) $id_etsy_shipping_templates . "', "
                    . "'" . pSQL($shipping_upgrade['upgrade_id']) . "', "
                    . "'" . pSQL(mb_convert_encoding($shipping_upgrade['upgrade_name'], 'UTF-8', 'HTML-ENTITIES')) . "', "
                    . "'" . pSQL($shipping_upgrade['type']) . "', "
                    . "'" . (float) $primary_cost . "', "
                    . "'" . (float) $secondary_cost . "',"
                    . "'" . pSQL($transmit_data) . "', "
                    . "'" . pSQL($shipping_upgrade['shipping_carrier_id']) . "', "
                    . "'" . pSQL($shipping_upgrade['mail_class']) . "', "
                    . "'" . pSQL($shipping_upgrade['min_delivery_days']) . "', "
                    . "'" . pSQL($shipping_upgrade['max_delivery_days']) . "', "
                    . "'0',"
                    . "'0',"
                    . "NOW(),"
                    . "NOW())");
            }
        }
    }

    /* Delete the templates which are no longer avaliable in the Etsy */
    public static function deleteEtsyDeletedTemplates($shipping_templates = array())
    {
        if (!empty($shipping_templates)) {
            Db::getInstance()->execute("DELETE FROM " . _DB_PREFIX_ . "etsy_shipping_templates WHERE (shipping_template_id IS NOT NULL AND shipping_template_id != '' AND shipping_template_id != 0) AND shipping_template_id NOT IN ('" . implode("','", $shipping_templates) . "')");
        } else {
            Db::getInstance()->execute("DELETE FROM " . _DB_PREFIX_ . "etsy_shipping_templates WHERE (shipping_template_id IS NOT NULL AND shipping_template_id != '' shipping_template_id != 0)");
        }
    }

    /** Add created templates in Etsy from local Db */
    public static function syncShippingTemplatesToEtsy()
    {
        $shippingTemplatesCreated = 0;
        $method_name = 'SyncTemplate::syncShippingTemplatesToEtsy()';
        EtsyModule::auditLogEntry('Job execution started to create shipping templates on Etsy.', $method_name);

        /* Delete Shipping Template first before creation. */
        self::deleteShippingTemplates();

        //Get all Shipping Templates to list on Etsy Marketplace
        $templates = Db::getInstance()->executeS("SELECT * FROM " . _DB_PREFIX_ . "etsy_shipping_templates WHERE shipping_template_id IS NULL", true, false);

        if (!empty($templates)) {
            $shop = EtsyModule::etsyGetShopDetails();
            if (isset($shop['shop_id'])) {
                foreach ($templates as $template) {
                    /**
                     * We can pass a country iso code or a region when creating a ShippingProfile, not country id, so fecthing the iso code
                     * @date 14-04-2023
                     * @author Tanisha Gupta
                     */
                    $country_data = EtsyModule::geyEtsyCountry($template['shipping_origin_country_id']);
                    //Prepare Array to send with request on Etsy
                    $etsyQueryString = array(
                        'title' => $template['shipping_template_title'],
                        'origin_country_iso' => $country_data['iso_code'],
                        /**
                         * Send postal code and both min and max_delivery_days
                         * @date 14-04-2023
                         * @author Tanisha Gupta
                         */
                        'origin_postal_code' => $template['postal_code'],
                        'primary_cost' => $template['shipping_primary_cost'],
                        'secondary_cost' => $template['shipping_secondary_cost'],
                        'min_processing_time' => $template['shipping_min_process_days'],
                        'max_processing_time' => $template['shipping_max_process_days'],
                        'min_delivery_days' => $template['shipping_min_process_days'],
                        'max_delivery_days' => $template['shipping_max_process_days'],
                        'destination_country_iso' => $country_data['iso_code']
                    );
                    $etsyQueryString = http_build_query($etsyQueryString);
                    $etsyRequestURI = '/shops/' . $shop['shop_id'] . '/shipping-profiles';
                    $etsyRequestMethod = 'POST';
                    $shippingTemplateResponse = EtsyModule::etsyGetResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString);

                    if (!empty($shippingTemplateResponse) && isset($shippingTemplateResponse['shipping_profile_id'])) {
                        $shippingTemplateID = $shippingTemplateResponse['shipping_profile_id'];
                        if (!empty($shippingTemplateID)) {
                            $shippingTemplatesCreated++;
                            Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_shipping_templates SET shipping_template_id = '" . pSQL($shippingTemplateID) . "', sync_error = '' WHERE id_etsy_shipping_templates = '" . (int) $template['id_etsy_shipping_templates'] . "'");
                        }
                    } else {
                        /**
                         * Added by Ashish to Handle show the template sync errors.
                         * Etsy001-Mar-2024 etsy-handle-template-sync
                         * @date 08-03-2024
                         * @author Ashish
                         */
                        echo "Error in syncing the new template from Prestashop to Etsy " . $template['shipping_template_title'] . ": " . $shippingTemplateResponse['error']."<br>";
                        Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_shipping_templates SET sync_error = '". pSQL($shippingTemplateResponse['error']) . "' WHERE id_etsy_shipping_templates = '" . (int) $template['id_etsy_shipping_templates'] . "'");


                        EtsyModule::auditLogEntry("Error in creating template " . $template['shipping_template_title'] . ":" . $shippingTemplateResponse['error'], $method_name);
                    }
                    sleep(1);
                }
            } else {
                EtsyModule::auditLogEntry("Error in creating template " . $template['shipping_template_title'] . ":" . $shop['error'], $method_name);
            }
        }
        EtsyModule::auditLogEntry('Job execution completed to create shipping templates on Etsy. <br>Total shipping templates created: ' . $shippingTemplatesCreated, $method_name);

        /** Update modified templates on Etsy */
        self::updateShippingTemplates();

        /** Template Entires Sync */
        self::deleteTemplateEntriesOnEtsy();
        self::syncTemplateEntriesToEtsy();
        self::updateTemplateEntriesOnEtsy();

        /** Template Upgarde Sync */
        self::deleteTemplateUpgradesOnEtsy();
        self::syncTemplateUpgradeToEtsy();
        self::updateTemplateUpgradeOnEtsy();
    }

    public static function deleteShippingTemplates()
    {
        $method_name = 'SyncTemplate::deleteShippingTemplates()';
        $templates = Db::getInstance()->executeS("SELECT * FROM " . _DB_PREFIX_ . "etsy_shipping_templates WHERE shipping_template_id IS NOT NULL AND shipping_template_id != '' AND shipping_template_id != 0 AND delete_flag = '1'", true, false);

        if (!empty($templates)) {
            /**
             * Fetch Shop id to send with the request url
             * @date 14-04-2023
             * @author Tanisha Gupta
             */
            $shop = EtsyModule::etsyGetShopDetails();
            if (isset($shop['shop_id'])) {
                foreach ($templates as $template) {
                    /**
                     * Commented below code as shipping_template_id is pasing in the url
                     * @date 14-04-2023
                     * @author Tanisha Gupta
                     */
                    $etsyQueryString = array();
                    $etsyRequestURI = 'shops/' . $shop['shop_id'] . '/shipping-profiles/' . $template['shipping_template_id'];
                    $etsyRequestMethod = 'DELETE';
                    $shippingTemplateResponse = EtsyModule::etsyGetResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString);

                    if (!empty($shippingTemplateResponse) && isset($shippingTemplateResponse['error'])) {
                        EtsyModule::auditLogEntry($shippingTemplateResponse['error'], $method_name);
                    } else {
                        Db::getInstance()->execute("DELETE FROM " . _DB_PREFIX_ . "etsy_shipping_templates WHERE shipping_template_id = '" . pSQL($template['shipping_template_id']) . "' AND id_etsy_shipping_templates = '" . (int) $template['id_etsy_shipping_templates'] . "'");
                        Db::getInstance()->execute("DELETE FROM " . _DB_PREFIX_ . "etsy_shipping_templates_entriess WHERE id_etsy_shipping_templates = '" . (int) $template['id_etsy_shipping_templates'] . "'");
                        Db::getInstance()->execute("DELETE FROM " . _DB_PREFIX_ . "etsy_shipping_upgrades WHERE id_etsy_shipping_templates = '" . (int) $template['id_etsy_shipping_templates'] . "'");
                        EtsyModule::auditLogEntry("Template deleted from etsy" . $template['shipping_template_title'], $method_name);
                    }
                }
            }
        }
        return true;
    }

    /** To send request on etsy to update shipping templates */
    public static function updateShippingTemplates()
    {
        $shippingTemplatesRenewed = 0;

        $method_name = 'SyncTemplate::updateShippingTemplates()';
        EtsyModule::auditLogEntry('Job execution started to update the shipping templates on etsy.', $method_name);

        $shippingTemplates = Db::getInstance()->executeS("SELECT * FROM " . _DB_PREFIX_ . "etsy_shipping_templates WHERE shipping_template_id IS NOT NULL AND shipping_template_id != '' AND shipping_template_id != 0 AND renew_flag = '1' AND delete_flag = '0'", true, false);
        if (!empty($shippingTemplates)) {
            $shop = EtsyModule::etsyGetShopDetails();
            if (isset($shop['shop_id'])) {
                foreach ($shippingTemplates as $template) {
                    /**
                     * We can pass a country iso code or a region when creating a ShippingProfile, not country id, so fecthing the iso code
                     * @date 14-04-2023
                     * @author Tanisha Gupta
                     */
                    $country_data = EtsyModule::geyEtsyCountry($template['shipping_origin_country_id']);
                    $etsyQueryString = array(
                        'title' => $template['shipping_template_title'],
                        'origin_country_iso' => $country_data['iso_code'],
                        /**
                         * Send postal code and both min and max_delivery_days
                         * @date 14-04-2023
                         * @author Tanisha Gupta
                         */
                        'origin_postal_code' => $template['postal_code'],
                        'min_processing_time' => $template['shipping_min_process_days'],
                        'max_processing_time' => $template['shipping_max_process_days']
                    );
                    $etsyQueryString = http_build_query($etsyQueryString);
                    $etsyRequestURI = '/shops/' . $shop['shop_id'] . '/shipping-profiles/' . $template['shipping_template_id'];
                    $etsyRequestMethod = 'PUT';
                    $shippingTemplateResponse = EtsyModule::etsyGetResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString);

                    if (!empty($shippingTemplateResponse) && isset($shippingTemplateResponse['shipping_profile_id'])) {
                        $shippingTemplateID = $shippingTemplateResponse['shipping_profile_id'];
                        if (!empty($shippingTemplateID)) {
                            $shippingTemplatesRenewed++;
                            Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_shipping_templates SET renew_flag = '0' WHERE shipping_template_id = '" . pSQL($shippingTemplateID) . "' AND id_etsy_shipping_templates = '" . (int) $template['id_etsy_shipping_templates'] . "'");
                        }
                    } else {
                        EtsyModule::auditLogEntry("Error in updating the template " . $template['shipping_template_title'] . ":" . $shippingTemplateResponse['error'], $method_name);
                    }
                    sleep(1);
                }
            } else {
                EtsyModule::auditLogEntry("Error in updating template: " . $shop['error'], $method_name);
            }
        }
        EtsyModule::auditLogEntry('Job execution completed to update the shipping templates on etsy.. <br>Total shipping templates updated: ' . $shippingTemplatesRenewed, $method_name);
        return true;
    }

    /** To create shipping template entries from etsy which has been created from the system */
    public static function syncTemplateEntriesToEtsy()
    {
        $method_name = 'SyncTemplate::syncTemplatesEntriesToEtsy()';
        $template_entries = Db::getInstance()->executeS("SELECT te.*, st.shipping_template_id FROM " . _DB_PREFIX_ . "etsy_shipping_templates_entries te INNER JOIN " . _DB_PREFIX_ . "etsy_shipping_templates st ON te.id_etsy_shipping_templates = st.id_etsy_shipping_templates WHERE shipping_template_entry_id IS NULL AND shipping_template_id is not NULL", true, false);

        if (!empty($template_entries)) {
            $shop = EtsyModule::etsyGetShopDetails();
            if (isset($shop['shop_id'])) {
                foreach ($template_entries as $template_entry) {
                    $etsyQueryString = array(
                        'primary_cost' => $template_entry['shipping_entry_primary_cost'],
                        'secondary_cost' => $template_entry['shipping_entry_secondary_cost']
                    );

                    if ($template_entry['shipping_entry_destination_region_id'] != null && $template_entry['shipping_entry_destination_region_id'] != '0') {
                        $region_data = EtsyModule::etsyGetRegionById($template_entry['shipping_entry_destination_region_id']);
                        $etsyQueryString['destination_region'] = $region_data['region_iso'];
                    } else {
                        $country_data = EtsyModule::geyEtsyCountry($template_entry['shipping_entry_destination_country_id']);
                        $etsyQueryString['destination_country_iso'] = $country_data['iso_code'];
                    }
                    /**
                     * Need to send  either both a shipping_carrier_id AND mail_class or both min_delivery_days AND max_delivery_days. So, set the same
                     * @date 14-04-2023
                     * @author Tanisha Gupta
                     */
                    if ($template_entry['shipping_entry_transmit_type'] == 'shipping_carrier') {
                        $etsyQueryString['shipping_carrier_id'] = (int) $template_entry['shipping_entry_carrier_id'];
                        $etsyQueryString['mail_class'] = (string)$template_entry['shipping_entry_mail_class_key'];
                    } else {
                        $etsyQueryString['min_delivery_days'] = (int) $template_entry['shipping_entry_min_delivery_days'];
                        $etsyQueryString['max_delivery_days'] = (string) $template_entry['shipping_entry_max_delivery_days'];
                    }
                    $etsyRequestURI = '/shops/' . $shop['shop_id'] . '/shipping-profiles/' . $template_entry['shipping_template_id'] . '/destinations';
                    $etsyQueryString = http_build_query($etsyQueryString);
                    $etsyRequestMethod = 'POST';
                    $template_entry_response = EtsyModule::etsyGetResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString);
                    if (!empty($template_entry_response) && isset($template_entry_response['shipping_profile_destination_id'])) {
                        if (!empty($template_entry_response['shipping_profile_destination_id'])) {
                            Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_shipping_templates_entries SET shipping_template_entry_id = '" . pSQL($template_entry_response['shipping_profile_destination_id']) . "' WHERE id_etsy_shipping_templates_entries = '" . (int) $template_entry['id_etsy_shipping_templates_entries'] . "'");
                        }
                    } else {
                        EtsyModule::auditLogEntry("Error in adding template entry (" . $template_entry['id_etsy_shipping_templates_entries'] . "):" . $template_entry_response['error'], $method_name);
                    }
                }
            } else {
                EtsyModule::auditLogEntry("Error in adding template entry (" . $template_entry['id_etsy_shipping_templates_entries'] . "):" . $shop['error'], $method_name);
            }
        }
        return true;
    }

    /** To update the shipping template entires from etsy which has been updated in the system */
    public static function updateTemplateEntriesOnEtsy()
    {
        $method_name = 'SyncTemplate::updateTemplateEntriesOnEtsy()';

        $template_entries = Db::getInstance()->executeS("SELECT te.*, st.shipping_template_id FROM " . _DB_PREFIX_ . "etsy_shipping_templates_entries te INNER JOIN " . _DB_PREFIX_ . "etsy_shipping_templates st ON te.id_etsy_shipping_templates = st.id_etsy_shipping_templates WHERE shipping_template_entry_id IS NOT NULL AND shipping_template_entry_id != '' AND shipping_template_entry_id != 0 AND te.renew_flag = '1' AND te.delete_flag = '0'  AND shipping_template_id is not NULL", true, false);
        if (!empty($template_entries)) {
            $shop = EtsyModule::etsyGetShopDetails();
            if (isset($shop['shop_id'])) {
                foreach ($template_entries as $template_entry) {
                    $etsyQueryString = array(
                        //'shipping_template_entry_id' => $template_entry['shipping_template_entry_id'],
                        'primary_cost' => $template_entry['shipping_entry_primary_cost'],
                        'secondary_cost' => $template_entry['shipping_entry_secondary_cost']
                    );
                    if ($template_entry['shipping_entry_destination_country_id'] != null && $template_entry['shipping_entry_destination_country_id'] != '0') {
                        $country_data = EtsyModule::geyEtsyCountry($template_entry['shipping_entry_destination_country_id']);
                        $etsyQueryString['destination_country_iso'] = $country_data['iso_code'];
                    } else {
                        $region_data = EtsyModule::etsyGetRegionById($template_entry['shipping_entry_destination_region_id']);
                        $etsyQueryString['destination_region'] = $region_data['region_iso'];
                    }
                    /**
                     * Need to send  either both a shipping_carrier_id AND mail_class or both min_delivery_days AND max_delivery_days. So, set the same
                     * @date 14-04-2023
                     * @author Tanisha Gupta
                     */
                    if ($template_entry['shipping_entry_transmit_type'] == 'shipping_carrier') {
                        $etsyQueryString['shipping_carrier_id'] = (int) $template_entry['shipping_entry_carrier_id'];
                        $etsyQueryString['mail_class'] = (string)$template_entry['shipping_entry_mail_class_key'];
                    } else {
                        $etsyQueryString['min_delivery_days'] = (int) $template_entry['shipping_entry_min_delivery_days'];
                        $etsyQueryString['max_delivery_days'] = (string) $template_entry['shipping_entry_max_delivery_days'];
                    }
                    $etsyQueryString = http_build_query($etsyQueryString);
                    $etsyRequestURI = '/shops/' . $shop['shop_id'] . '/shipping-profiles/' . $template_entry['shipping_template_id'] . '/destinations/' . $template_entry['shipping_template_entry_id'];
                    $etsyRequestMethod = 'PUT';
                    $template_entry_response = EtsyModule::etsyGetResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString);
                    if (!empty($template_entry_response) && isset($template_entry_response['shipping_profile_destination_id'])) {
                        $shipping_template_entry_id = $template_entry_response['shipping_profile_destination_id'];

                        if (!empty($shipping_template_entry_id)) {
                            Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_shipping_templates_entries SET renew_flag = '0', shipping_template_entry_id = '" . pSQL($shipping_template_entry_id) . "' WHERE id_etsy_shipping_templates_entries = '" . (int) $template_entry['id_etsy_shipping_templates_entries'] . "'");
                        }
                    } else {
                        EtsyModule::auditLogEntry("Error in adding template entry (" . $template_entry['id_etsy_shipping_templates_entries'] . "):" . $template_entry_response['error'], $method_name);
                    }
                }
            } else {
                EtsyModule::auditLogEntry("Error in adding template entry (" . $template_entry['id_etsy_shipping_templates_entries'] . "):" . $shop['error'], $method_name);
            }
        }
        return true;
    }

    /** To delete shipping template entries from etsy which has been deleted from the system */
    public static function deleteTemplateEntriesOnEtsy()
    {
        $method_name = 'SyncTemplate::deleteTemplateEntriesOnEtsy()';

        $template_entries = Db::getInstance()->executeS("SELECT te.*, st.shipping_template_id FROM " . _DB_PREFIX_ . "etsy_shipping_templates_entries te INNER JOIN " . _DB_PREFIX_ . "etsy_shipping_templates st ON te.id_etsy_shipping_templates = st.id_etsy_shipping_templates WHERE shipping_template_entry_id IS NOT NULL AND shipping_template_entry_id != '' AND shipping_template_entry_id != 0 AND te.delete_flag = '1' AND shipping_template_id is not NULL", true, false);

        if (!empty($template_entries)) {
            $shop = EtsyModule::etsyGetShopDetails();
            if (isset($shop['shop_id'])) {
                foreach ($template_entries as $template_entry) {
                    /**
                     * Passing shipping_template_entry_id in the request url so not need to set the same in array
                     * @date 14-04-2023
                     * @author Tanisha Gupta
                     */
                    $etsyQueryString = array();
                    //                    $etsyQueryString = array(
                    //                        'shipping_template_entry_id' => $template_entry['shipping_template_entry_id']
                    //                    );
                    $etsyRequestURI = '/shops/' . $shop['shop_id'] . '/shipping-profiles/' . $template_entry['shipping_template_id'] . '/destinations/' . $template_entry['shipping_template_entry_id'];
                    $etsyRequestMethod = 'DELETE';
                    $template_entry_response = EtsyModule::etsyGetResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString);

                    if (!empty($template_entry_response) && isset($template_entry_response['error'])) {
                        EtsyModule::auditLogEntry("Error in adding template entry (" . $template_entry['id_etsy_shipping_templates_entries'] . "):" . $template_entry_response['error'], $method_name);
                    } else {
                        Db::getInstance()->execute("DELETE FROM " . _DB_PREFIX_ . "etsy_shipping_templates_entries WHERE shipping_template_entry_id = '" . pSQL($template_entry['shipping_template_entry_id']) . "' AND id_etsy_shipping_templates_entries = '" . (int) $template_entry['id_etsy_shipping_templates_entries'] . "'");
                    }
                }
            } else {
                EtsyModule::auditLogEntry("Error in adding template entry (" . $template_entry['id_etsy_shipping_templates_entries'] . "):" . $shop['error'], $method_name);
            }
        }
        return true;
    }

    /** To create shipping template upgrades from etsy which has been created into the system */
    public static function syncTemplateUpgradeToEtsy()
    {
        $method_name = 'SyncTemplate::syncTemplateEntriesToUpgrades()';
        $template_upgrades = Db::getInstance()->executeS("SELECT tu.*, st.shipping_template_id FROM " . _DB_PREFIX_ . "etsy_shipping_upgrades tu INNER JOIN " . _DB_PREFIX_ . "etsy_shipping_templates st ON tu.id_etsy_shipping_templates = st.id_etsy_shipping_templates WHERE shipping_upgrade_id IS NULL AND shipping_template_id is not NULL", true, false);
        if (!empty($template_upgrades)) {
            $shop = EtsyModule::etsyGetShopDetails();
            if (isset($shop['shop_id'])) {
                foreach ($template_upgrades as $template_upgrade) {
                    $etsyQueryString = array(
                        // 'shipping_template_id' => $template_upgrade['shipping_template_id'],
                        'price' => (float) $template_upgrade['shipping_upgrade_primary_cost'],
                        'secondary_price' => (float) $template_upgrade['shipping_upgrade_secondary_cost'],
                        'type' => (string) $template_upgrade['shipping_upgrade_destination'],
                        'value' => (string) $template_upgrade['shipping_upgrade_title'],
                        'upgrade_name' => (string) $template_upgrade['shipping_upgrade_title']
                    );
                    /**
                     * Need to send  either both a shipping_carrier_id AND mail_class or both min_delivery_days AND max_delivery_days. So, set the same
                     * @date 14-04-2023
                     * @author Tanisha Gupta
                     */
                    if ($template_upgrade['shipping_upgrade_transmit_type'] == 'shipping_carrier') {
                        $etsyQueryString['shipping_carrier_id'] = (int) $template_upgrade['shipping_upgrade_carrier_id'];
                        $etsyQueryString['mail_class'] = (string)$template_upgrade['shipping_upgrade_mail_class_key'];
                    } else {
                        $etsyQueryString['min_delivery_days'] = (int) $template_upgrade['shipping_upgrade_min_delivery_days'];
                        $etsyQueryString['max_delivery_days'] = (string) $template_upgrade['shipping_upgrade_max_delivery_days'];
                    }
                    $etsyQueryString = http_build_query($etsyQueryString);
                    $etsyRequestURI = '/shops/' . $shop['shop_id'] . '/shipping-profiles/' . $template_upgrade['shipping_template_id'] . '/upgrades';
                    $etsyRequestMethod = 'POST';

                    $template_upgrade_response = EtsyModule::etsyGetResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString);

                    if (!empty($template_upgrade_response) && isset($template_upgrade_response['shipping_profile_id'])) {
                        $shipping_upgrade_id = $template_upgrade_response['upgrade_id'];
                        if (!empty($shipping_upgrade_id)) {
                            Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_shipping_upgrades SET shipping_upgrade_id = '" . pSQL($shipping_upgrade_id) . "' WHERE id_etsy_shipping_upgrades = '" . (int) $template_upgrade['id_etsy_shipping_upgrades'] . "'");
                        }
                    } else {
                        EtsyModule::auditLogEntry("Error in adding template upgrade (" . $template_upgrade['id_etsy_shipping_upgrades'] . "):" . $template_upgrade_response['error'], $method_name);
                    }
                }
            }
        }
        return true;
    }

    /** To update the shipping template upgrades from etsy which has been updated in the system */
    public static function updateTemplateUpgradeOnEtsy()
    {
        $method_name = 'SyncTemplate::updateTemplateUpgradeOnEtsy()';

        $template_upgrades = Db::getInstance()->executeS("SELECT tu.*, st.shipping_template_id FROM " . _DB_PREFIX_ . "etsy_shipping_upgrades tu INNER JOIN " . _DB_PREFIX_ . "etsy_shipping_templates st ON tu.id_etsy_shipping_templates = st.id_etsy_shipping_templates WHERE shipping_upgrade_id IS NOT NULL AND shipping_upgrade_id != '' AND shipping_upgrade_id != 0 AND tu.renew_flag = '1' AND tu.delete_flag = '0' AND shipping_template_id is not NULL");

        if (!empty($template_upgrades)) {
            $shop = EtsyModule::etsyGetShopDetails();
            if (isset($shop['shop_id'])) {
                foreach ($template_upgrades as $template_upgrade) {
                    $etsyQueryString = array(
                        //'shipping_template_id' => $template_upgrade['shipping_template_id'],
                        'price' => (float) $template_upgrade['shipping_upgrade_primary_cost'],
                        'secondary_price' => (float) $template_upgrade['shipping_upgrade_secondary_cost'],
                        'type' => (string) $template_upgrade['shipping_upgrade_destination'],
                        'value_id' => (string) $template_upgrade['shipping_upgrade_id'],
                        'upgrade_name' => (string) $template_upgrade['shipping_upgrade_title']
                    );
                    /**
                     * Need to send  either both a shipping_carrier_id AND mail_class or both min_delivery_days AND max_delivery_days. So, set the same
                     * @date 14-04-2023
                     * @author Tanisha Gupta
                     */
                    if ($template_upgrade['shipping_upgrade_transmit_type'] == 'shipping_carrier') {
                        $etsyQueryString['shipping_carrier_id'] = (int) $template_upgrade['shipping_upgrade_carrier_id'];
                        $etsyQueryString['mail_class'] = (string)$template_upgrade['shipping_upgrade_mail_class_key'];
                    } else {
                        $etsyQueryString['min_delivery_days'] = (int) $template_upgrade['shipping_upgrade_min_delivery_days'];
                        $etsyQueryString['max_delivery_days'] = (string) $template_upgrade['shipping_upgrade_max_delivery_days'];
                    }
                    $etsyQueryString = http_build_query($etsyQueryString);
                    $etsyRequestURI = '/shops/' . $shop['shop_id'] . '/shipping-profiles/' . $template_upgrade['shipping_template_id'] . '/upgrades/' . $template_upgrade['shipping_upgrade_id'];
                    $etsyRequestMethod = 'PUT';

                    $template_upgrade_response = EtsyModule::etsyGetResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString);
                    if (!empty($template_upgrade_response) && isset($template_upgrade_response['shipping_profile_id'])) {
                        Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_shipping_upgrades SET renew_flag = '0' WHERE id_etsy_shipping_upgrades = '" . (int) $template_upgrade['id_etsy_shipping_upgrades'] . "' AND id_etsy_shipping_templates = '" . (int) $template_upgrade['id_etsy_shipping_templates'] . "'");
                    } else {
                        EtsyModule::auditLogEntry("Error in updating template upgrade (" . $template_upgrade['id_etsy_shipping_upgrades'] . "):" . $template_upgrade_response['error'], $method_name);
                    }
                }
            } else {
                EtsyModule::auditLogEntry("Error in updating template upgrade (" . $template_upgrade['id_etsy_shipping_upgrades'] . "):" . $shop['error'], $method_name);
            }
        }
        return true;
    }

    //To delete shipping template upgardes from etsy which has been deleted from the system */
    public static function deleteTemplateUpgradesOnEtsy()
    {
        $method_name = 'SyncTemplate::updateTemplateUpgradeOnEtsy()';

        $template_upgrades = Db::getInstance()->executeS("SELECT tu.*, st.shipping_template_id FROM " . _DB_PREFIX_ . "etsy_shipping_upgrades tu INNER JOIN " . _DB_PREFIX_ . "etsy_shipping_templates st ON tu.id_etsy_shipping_templates = st.id_etsy_shipping_templates WHERE shipping_upgrade_id IS NOT NULL AND shipping_upgrade_id != '' AND shipping_upgrade_id != 0 AND tu.delete_flag = '1' AND shipping_template_id is not NULL", true, false);
        if (!empty($template_upgrades)) {
            $shop = EtsyModule::etsyGetShopDetails();
            if (isset($shop['shop_id'])) {
                foreach ($template_upgrades as $template_upgrade) {
                    $etsyQueryString = array();
                    $etsyRequestURI = '/shops/' . $shop['shop_id'] . '/shipping-profiles/' . $template_upgrade['shipping_template_id'] . '/upgrades/' . $template_upgrade['shipping_upgrade_id'];
                    $etsyRequestMethod = 'DELETE';

                    $template_upgrade_response = EtsyModule::etsyGetResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString);
                    if (!empty($template_upgrade_response) && isset($template_upgrade_response['error'])) {
                        EtsyModule::auditLogEntry("Error in deleting template upgrade (" . $template_upgrade['id_etsy_shipping_upgrades'] . "):" . $template_upgrade_response['error'], $method_name);
                    } else {
                        Db::getInstance()->execute("DELETE FROM " . _DB_PREFIX_ . "etsy_shipping_upgrades WHERE shipping_upgrade_id = '" . pSQL($template_upgrade['shipping_upgrade_id']) . "' AND id_etsy_shipping_upgrades = '" . (int) $template_upgrade['id_etsy_shipping_upgrades'] . "'");
                    }
                }
            } else {
                EtsyModule::auditLogEntry("Error in deleting template upgrade (" . $template_upgrade['id_etsy_shipping_upgrades'] . "):" . $shop['error'], $method_name);
            }
        }
        return true;
    }
    /**
     * This function is responsible to fetch shipping profile title based on shipping id
     * TGmay2023 Shipping-Order
     * @date 18-05-2023
     * @author Tanisha Gupta
     * @param bigint $id
     * @return string
     */
    public static function getShippingProfileTitleByProfileId($id)
    {
        /**
         * Custom changes: Made changes to get title  value from database
         * @date 18-05-2023
         * @modifier Tanisha Gupta
         */
        $selectSQL = "SELECT shipping_template_title FROM " . _DB_PREFIX_ . "etsy_shipping_templates WHERE shipping_template_id = '" . pSQL($id) . "'";
        $getResult = Db::getInstance()->getValue($selectSQL, false);
        return $getResult;
    }
}
