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

        $etsy_shipping_templates = array();

        $etsyQueryString = array();
        $etsyRequestURI = '/users/' . Configuration::get('etsy_api_user_id') . '/shipping/templates?limit=100';
        $etsyRequestMethod = 'GET';
        $shipping_templates = Tools::jsonDecode(EtsyModule::etsyGetOAuthResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString), true);
        if (!empty($shipping_templates['results'])) {
            foreach ($shipping_templates['results'] as $shippingTemplateDetails) {
                $shipping_template_id = $shippingTemplateDetails['shipping_template_id'];
                $title = $shippingTemplateDetails['title'];
                $min_processing_days = $shippingTemplateDetails['min_processing_days'];
                $max_processing_days = $shippingTemplateDetails['max_processing_days'];
                $origin_country_id = $shippingTemplateDetails['origin_country_id'];
                $shippingOriginCountryName = EtsyModule::etsyGetCountryNameByCountryId($origin_country_id);
                $etsy_shipping_templates[] = $shippingTemplateDetails['shipping_template_id'];

                $checkShippingTemplateExistQuery = "SELECT * FROM " . _DB_PREFIX_ . "etsy_shipping_templates WHERE shipping_template_id = '" . pSQL($shippingTemplateDetails['shipping_template_id']) . "'";
                $result = DB::getInstance()->getRow($checkShippingTemplateExistQuery);
                $id_etsy_shipping_templates = "";
                if ($result === false) {
                    $add_result = Db::getInstance()->execute("INSERT INTO " . _DB_PREFIX_ . "etsy_shipping_templates VALUES (NULL, '" . pSQL($shipping_template_id) . "', '" . pSQL($title) . "', '" . (int) $origin_country_id . "', '" . pSQL($shippingOriginCountryName) . "', '', '', '" . (int) $min_processing_days . "', '" . (int) $max_processing_days . "', '0', '0', NOW(), NOW())");
                    if ($add_result) {
                        $id_etsy_shipping_templates = DB::getInstance()->Insert_ID();
                        EtsyModule::auditLogEntry('Shipping template synced. Added template is<br>Shipping Template Title: ' . $title, $method_name);
                    }
                } else {
                    Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_shipping_templates SET "
                            . "shipping_template_title = '" . pSQL($title) . "',"
                            . "shipping_origin_country_id = '" . (int) $origin_country_id . "',"
                            . "shipping_origin_country = '" . pSQL($shippingOriginCountryName) . "',"
                            . "shipping_min_process_days = '" . (int) $min_processing_days . "',"
                            . "shipping_max_process_days = '" . (int) $max_processing_days . "' "
                            . "WHERE shipping_template_id = '" . pSQL($shippingTemplateDetails['shipping_template_id']) . "'");

                    $id_etsy_shipping_templates = $result['id_etsy_shipping_templates'];
                }
                if ((int) $id_etsy_shipping_templates) {
                    self::syncShippingEntires($shipping_template_id, $id_etsy_shipping_templates);
                    self::syncUpgrades($shipping_template_id, $id_etsy_shipping_templates);
                }
            }
            self::deleteEtsyDeletedTemplates($etsy_shipping_templates);
        } else {
            EtsyModule::auditLogEntry(str_replace("_", " ", key((array) $shipping_templates)), $method_name);
        }
        EtsyModule::auditLogEntry('Job execution completed to import shipping templates from etsy to prestashop.', $method_name);
        return true;
    }

    /** Sync Shipping Templates from Etsy to DB */
    public static function syncShippingEntires($shipping_template_id, $id_etsy_shipping_templates)
    {
        $etsyQueryString = array();
        $etsyRequestURI = '/shipping/templates/' . $shipping_template_id . '/entries?limit=500';
        $etsyRequestMethod = 'GET';
        $template_entries = Tools::jsonDecode(EtsyModule::etsyGetOAuthResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString), true);

        Db::getInstance()->execute("DELETE FROM " . _DB_PREFIX_ . "etsy_shipping_templates_entries WHERE id_etsy_shipping_templates = '" . (int) $id_etsy_shipping_templates . "' AND (shipping_template_entry_id != 0 AND shipping_template_entry_id IS NOT NULL AND shipping_template_entry_id != '')");

        if (!empty($template_entries['results'])) {
            foreach ($template_entries['results'] as $template_entry) {
                $shippingDestinationCountryName = null;
                $shippingDestinationRegionName = null;

                if ((int) $template_entry['destination_country_id'] > 0) {
                    $shippingDestinationCountryName = EtsyModule::etsyGetCountryNameByCountryId($template_entry['destination_country_id']);
                } else if ((int) $template_entry['destination_region_id'] > 0) {
                    $shippingDestinationRegionName = EtsyModule::etsyGetRegionNameByRegionId($template_entry['destination_region_id']);
                }

                if ((int) $template_entry['destination_region_id'] > 0) {
                    /* If Region ID etnry for shipping template exist, then don't add more entry because others are duplicate */
                    $template_entry_region_check = Db::getInstance()->getValue("SELECT count(*) as total FROM " . _DB_PREFIX_ . "etsy_shipping_templates_entries WHERE id_etsy_shipping_templates = " . $id_etsy_shipping_templates . " AND shipping_entry_destination_region_id = " . $template_entry['destination_region_id']);
                    if ($template_entry_region_check > 0) {
                        continue;
                    }

                    $templateSQL = "INSERT INTO " . _DB_PREFIX_ . "etsy_shipping_templates_entries VALUES ("
                            . "NULL,"
                            . "'" . (int) $id_etsy_shipping_templates . "',"
                            . "'" . pSQL($template_entry['shipping_template_entry_id']) . "',"
                            . "NULL, "
                            . "NULL,"
                            . "'" . pSQL($template_entry['primary_cost']) . "',"
                            . "'" . pSQL($template_entry['secondary_cost']) . "',"
                            . "'" . pSQL($template_entry['destination_region_id']) . "',"
                            . "'" . pSQL($shippingDestinationRegionName) . "',"
                            . "'0',"
                            . "'0',"
                            . "NOW(),"
                            . "NOW())";
                } else if ((int) $template_entry['destination_country_id'] > 0) {
                    $templateSQL = "INSERT INTO " . _DB_PREFIX_ . "etsy_shipping_templates_entries VALUES "
                            . "(NULL,"
                            . "'" . (int) $id_etsy_shipping_templates . "',"
                            . "'" . pSQL($template_entry['shipping_template_entry_id']) . "',"
                            . "'" . pSQL($template_entry['destination_country_id']) . "',"
                            . "'" . pSQL($shippingDestinationCountryName) . "',"
                            . "'" . pSQL($template_entry['primary_cost']) . "',"
                            . "'" . pSQL($template_entry['secondary_cost']) . "',"
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
                            . "'" . pSQL($template_entry['shipping_template_entry_id']) . "',"
                            . "'',"
                            . "'',"
                            . "'" . pSQL($template_entry['primary_cost']) . "',"
                            . "'" . pSQL($template_entry['secondary_cost']) . "',"
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

    /** Sync Shipping Upgrades from Etsy to the DB */
    public static function syncUpgrades($shipping_template_id, $id_etsy_shipping_templates)
    {
        $etsyQueryString = array();
        $etsyRequestURI = '/shipping/templates/' . $shipping_template_id . '/upgrades?limit=100';
        $etsyRequestMethod = 'GET';

        $shipping_upgrades = Tools::jsonDecode(EtsyModule::etsyGetOAuthResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString), true);
        Db::getInstance()->execute("DELETE FROM " . _DB_PREFIX_ . "etsy_shipping_upgrades WHERE id_etsy_shipping_templates = '" . (int) $id_etsy_shipping_templates . "' AND (shipping_upgrade_id != 0 AND shipping_upgrade_id IS NOT NULL AND shipping_upgrade_id != '')");
        if (!empty($shipping_upgrades['results'])) {
            foreach ($shipping_upgrades['results'] as $key => $shipping_upgrade) {
                Db::getInstance()->execute("INSERT INTO " . _DB_PREFIX_ . "etsy_shipping_upgrades "
                        . "VALUES ("
                        . "NULL, "
                        . "'" . (int) $id_etsy_shipping_templates . "', "
                        . "'" . pSQL($shipping_upgrade['value_id']) . "', "
                        . "'" . pSQL($shipping_upgrade['value']) . "', "
                        . "'" . pSQL($shipping_upgrade['type']) . "', "
                        . "'" . (float) $shipping_upgrade['price'] . "', "
                        . "'" . (float) $shipping_upgrade['secondary_price'] . "',"
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

    /** Add created templates in Etsy from local DB */
    public static function syncShippingTemplatesToEtsy()
    {
        $shippingTemplatesCreated = 0;
        $method_name = 'SyncTemplate::syncShippingTemplatesToEtsy()';
        EtsyModule::auditLogEntry('Job execution started to create shipping templates on Etsy.', $method_name);

        /* Delete Shipping Template first before creation. */
        self::deleteShippingTemplates();

        //Get all Shipping Templates to list on Etsy Marketplace
        $templates = DB::getInstance()->executeS("SELECT * FROM " . _DB_PREFIX_ . "etsy_shipping_templates WHERE shipping_template_id IS NULL", true, false);

        if (!empty($templates)) {
            foreach ($templates as $template) {
                //Prepare Array to send with request on Etsy
                $etsyQueryString = array(
                    'title' => $template['shipping_template_title'],
                    'origin_country_id' => $template['shipping_origin_country_id'],
                    'primary_cost' => $template['shipping_primary_cost'],
                    'secondary_cost' => $template['shipping_secondary_cost'],
                    'min_processing_days' => $template['shipping_min_process_days'],
                    'max_processing_days' => $template['shipping_max_process_days']
                );

                $etsyRequestURI = '/shipping/templates/';
                $etsyRequestMethod = 'POST';
                $shippingTemplateResponse = Tools::jsonDecode(EtsyModule::etsyGetOAuthResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString));

                if (!empty($shippingTemplateResponse) && isset($shippingTemplateResponse->results)) {
                    $shippingTemplateID = $shippingTemplateResponse->results[0]->shipping_template_id;
                    if (!empty($shippingTemplateID)) {
                        $shippingTemplatesCreated++;
                        DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_shipping_templates SET shipping_template_id = '" . pSQL($shippingTemplateID) . "' WHERE id_etsy_shipping_templates = '" . (int) $template['id_etsy_shipping_templates'] . "'");
                    }
                } else {
                    EtsyModule::auditLogEntry("Error in creating template " . $template['shipping_template_title'] . ":" . str_replace("_", " ", key((array) $shippingTemplateResponse)), $method_name);
                }
                sleep(1);
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
        $templates = DB::getInstance()->executeS("SELECT * FROM " . _DB_PREFIX_ . "etsy_shipping_templates WHERE shipping_template_id IS NOT NULL AND shipping_template_id != '' AND shipping_template_id != 0 AND delete_flag = '1'", true, false);

        if (!empty($templates)) {
            foreach ($templates as $template) {
                $etsyQueryString = array(
                    'shipping_template_id' => $template['shipping_template_id']
                );

                $etsyRequestURI = '/shipping/templates/' . $template['shipping_template_id'] . '/';
                $etsyRequestMethod = 'DELETE';
                $shippingTemplateResponse = Tools::jsonDecode(EtsyModule::etsyGetOAuthResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString));

                if (!empty($shippingTemplateResponse) && isset($shippingTemplateResponse->results)) {
                    DB::getInstance()->execute("DELETE FROM " . _DB_PREFIX_ . "etsy_shipping_templates WHERE shipping_template_id = '" . pSQL($template['shipping_template_id']) . "' AND id_etsy_shipping_templates = '" . (int) $template['id_etsy_shipping_templates'] . "'");
                    DB::getInstance()->execute("DELETE FROM " . _DB_PREFIX_ . "etsy_shipping_templates_entriess WHERE id_etsy_shipping_templates = '" . (int) $template['id_etsy_shipping_templates'] . "'");
                    DB::getInstance()->execute("DELETE FROM " . _DB_PREFIX_ . "etsy_shipping_upgrades WHERE id_etsy_shipping_templates = '" . (int) $template['id_etsy_shipping_templates'] . "'");
                    EtsyModule::auditLogEntry("Template deleted from etsy" . $template['shipping_template_title'], $method_name);
                } else {
                    EtsyModule::auditLogEntry(str_replace("_", " ", key((array) $shippingTemplateResponse)), $method_name);
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

        $shippingTemplates = DB::getInstance()->executeS("SELECT * FROM " . _DB_PREFIX_ . "etsy_shipping_templates WHERE shipping_template_id IS NOT NULL AND shipping_template_id != '' AND shipping_template_id != 0 AND renew_flag = '1' AND delete_flag = '0'", true, false);
        if (!empty($shippingTemplates)) {
            foreach ($shippingTemplates as $template) {
                $etsyQueryString = array(
                    'shipping_template_id' => $template['shipping_template_id'],
                    'title' => $template['shipping_template_title'],
                    'origin_country_id' => $template['shipping_origin_country_id'],
                    'min_processing_days' => $template['shipping_min_process_days'],
                    'max_processing_days' => $template['shipping_max_process_days']
                );

                $etsyRequestURI = '/shipping/templates/' . $template['shipping_template_id'] . '/';
                $etsyRequestMethod = 'PUT';
                $shippingTemplateResponse = Tools::jsonDecode(EtsyModule::etsyGetOAuthResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString));

                if (!empty($shippingTemplateResponse) && isset($shippingTemplateResponse->results)) {
                    $shippingTemplateID = $shippingTemplateResponse->results[0]->shipping_template_id;
                    if (!empty($shippingTemplateID)) {
                        $shippingTemplatesRenewed++;
                        DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_shipping_templates SET renew_flag = '0' WHERE shipping_template_id = '" . pSQL($shippingTemplateID) . "' AND id_etsy_shipping_templates = '" . (int) $template['id_etsy_shipping_templates'] . "'");
                    }
                } else {
                    EtsyModule::auditLogEntry("Error in updating template " . $template['shipping_template_title'] . ":" . str_replace("_", " ", key((array) $shippingTemplateResponse)), $method_name);
                }
                sleep(1);
            }
        }
        EtsyModule::auditLogEntry('Job execution completed to update the shipping templates on etsy.. <br>Total shipping templates updated: ' . $shippingTemplatesRenewed, $method_name);
        return true;
    }

    /** To create shipping template entries from etsy which has been created from the system */
    public static function syncTemplateEntriesToEtsy()
    {
        $method_name = 'SyncTemplate::syncTemplatesEntriesToEtsy()';
        $template_entries = DB::getInstance()->executeS("SELECT te.*, st.shipping_template_id FROM " . _DB_PREFIX_ . "etsy_shipping_templates_entries te INNER JOIN " . _DB_PREFIX_ . "etsy_shipping_templates st ON te.id_etsy_shipping_templates = st.id_etsy_shipping_templates WHERE shipping_template_entry_id IS NULL AND shipping_template_id is not NULL", true, false);

        if (!empty($template_entries)) {
            foreach ($template_entries as $template_entry) {
                $etsyQueryString = array(
                    'shipping_template_id' => $template_entry['shipping_template_id'],
                    'primary_cost' => $template_entry['shipping_entry_primary_cost'],
                    'secondary_cost' => $template_entry['shipping_entry_secondary_cost']
                );
                if ($template_entry['shipping_entry_destination_region_id'] != null && $template_entry['shipping_entry_destination_region_id'] != '0') {
                    $etsyQueryString['destination_region_id'] = $template_entry['shipping_entry_destination_region_id'];
                } else {
                    $etsyQueryString['destination_country_id'] = $template_entry['shipping_entry_destination_country_id'];
                }

                $etsyRequestURI = '/shipping/templates/entries/';
                $etsyRequestMethod = 'POST';
                $template_entry_response = Tools::jsonDecode(EtsyModule::etsyGetOAuthResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString));
                if (!empty($template_entry_response) && isset($template_entry_response->results)) {
                    if (!empty($template_entry_response->results[0]->shipping_template_entry_id)) {
                        DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_shipping_templates_entries SET shipping_template_entry_id = '" . pSQL($template_entry_response->results[0]->shipping_template_entry_id) . "' WHERE id_etsy_shipping_templates_entries = '" . (int) $template_entry['id_etsy_shipping_templates_entries'] . "'");
                    }
                } else {
                    EtsyModule::auditLogEntry("Error in adding template entry (" . $template_entry['id_etsy_shipping_templates_entries'] . "):" . str_replace("_", " ", key((array) $template_entry_response)), $method_name);
                }
            }
        }
        return true;
    }

    /** To update the shipping template entires from etsy which has been updated in the system */
    public static function updateTemplateEntriesOnEtsy()
    {
        $method_name = 'SyncTemplate::updateTemplateEntriesOnEtsy()';

        $template_entries = DB::getInstance()->executeS("SELECT te.*, st.shipping_template_id FROM " . _DB_PREFIX_ . "etsy_shipping_templates_entries te INNER JOIN " . _DB_PREFIX_ . "etsy_shipping_templates st ON te.id_etsy_shipping_templates = st.id_etsy_shipping_templates WHERE shipping_template_entry_id IS NOT NULL AND shipping_template_entry_id != '' AND shipping_template_entry_id != 0 AND te.renew_flag = '1' AND te.delete_flag = '0'  AND shipping_template_id is not NULL", true, false);
        if (!empty($template_entries)) {
            foreach ($template_entries as $template_entry) {
                $etsyQueryString = array(
                    'shipping_template_entry_id' => $template_entry['shipping_template_entry_id'],
                    'primary_cost' => $template_entry['shipping_entry_primary_cost'],
                    'secondary_cost' => $template_entry['shipping_entry_secondary_cost']
                );
                if ($template_entry['shipping_entry_destination_country_id'] != null && $template_entry['shipping_entry_destination_country_id'] != '0') {
                    $etsyQueryString['destination_country_id'] = $template_entry['shipping_entry_destination_country_id'];
                } else {
                    $etsyQueryString['destination_region_id'] = $template_entry['shipping_entry_destination_region_id'];
                }

                $etsyRequestURI = '/shipping/templates/entries/' . $template_entry['shipping_template_entry_id'] . '/';
                $etsyRequestMethod = 'PUT';

                $template_entry_response = Tools::jsonDecode(EtsyModule::etsyGetOAuthResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString));

                if (!empty($template_entry_response) && isset($template_entry_response->results)) {
                    $shipping_template_entry_id = $template_entry_response->results[0]->shipping_template_entry_id;

                    if (!empty($shipping_template_entry_id)) {
                        DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_shipping_templates_entries SET renew_flag = '0', shipping_template_entry_id = '" . pSQL($shipping_template_entry_id) . "' WHERE id_etsy_shipping_templates_entries = '" . (int) $template_entry['id_etsy_shipping_templates_entries'] . "'");
                    }
                } else {
                    EtsyModule::auditLogEntry("Error in adding template entry (" . $template_entry['id_etsy_shipping_templates_entries'] . "):" . str_replace("_", " ", key((array) $template_entry_response)), $method_name);
                }
            }
        }
        return true;
    }

    /** To delete shipping template entries from etsy which has been deleted from the system */
    public static function deleteTemplateEntriesOnEtsy()
    {
        $method_name = 'SyncTemplate::deleteTemplateEntriesOnEtsy()';

        $template_entries = DB::getInstance()->executeS("SELECT te.*, st.shipping_template_id FROM " . _DB_PREFIX_ . "etsy_shipping_templates_entries te INNER JOIN " . _DB_PREFIX_ . "etsy_shipping_templates st ON te.id_etsy_shipping_templates = st.id_etsy_shipping_templates WHERE shipping_template_entry_id IS NOT NULL AND shipping_template_entry_id != '' AND shipping_template_entry_id != 0 AND te.delete_flag = '1' AND shipping_template_id is not NULL", true, false);

        if (!empty($template_entries)) {
            foreach ($template_entries as $template_entry) {
                $etsyQueryString = array(
                    'shipping_template_entry_id' => $template_entry['shipping_template_entry_id']
                );

                $etsyRequestURI = '/shipping/templates/entries/' . $template_entry['shipping_template_entry_id'] . '/?method=DELETE';
                $etsyRequestMethod = 'GET';
                $template_entry_response = Tools::jsonDecode(EtsyModule::etsyGetOAuthResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString), true);

                if (!empty($template_entry_response) && isset($template_entry_response->results)) {
                    DB::getInstance()->execute("DELETE FROM " . _DB_PREFIX_ . "etsy_shipping_templates_entries WHERE shipping_template_entry_id = '" . pSQL($template_entry['shipping_template_entry_id']) . "' AND id_etsy_shipping_templates_entries = '" . (int) $template_entry['id_etsy_shipping_templates_entries'] . "'");
                } else {
                    EtsyModule::auditLogEntry("Error in adding template entry (" . $template_entry['id_etsy_shipping_templates_entries'] . "):" . str_replace("_", " ", key((array) $template_entry_response)), $method_name);
                }
            }
        }
        return true;
    }

    /** To create shipping template upgrades from etsy which has been created into the system */
    public static function syncTemplateUpgradeToEtsy()
    {
        $method_name = 'SyncTemplate::syncTemplateEntriesToUpgrades()';
        $template_upgrades = DB::getInstance()->executeS("SELECT tu.*, st.shipping_template_id FROM " . _DB_PREFIX_ . "etsy_shipping_upgrades tu INNER JOIN " . _DB_PREFIX_ . "etsy_shipping_templates st ON tu.id_etsy_shipping_templates = st.id_etsy_shipping_templates WHERE shipping_upgrade_id IS NULL AND shipping_template_id is not NULL", true, false);
        if (!empty($template_upgrades)) {
            foreach ($template_upgrades as $template_upgrade) {
                $etsyQueryString = array(
                    'shipping_template_id' => $template_upgrade['shipping_template_id'],
                    'price' => $template_upgrade['shipping_upgrade_primary_cost'],
                    'secondary_price' => $template_upgrade['shipping_upgrade_secondary_cost'],
                    'type' => $template_upgrade['shipping_upgrade_destination'],
                    'value' => $template_upgrade['shipping_upgrade_title'],
                );

                $etsyRequestURI = '/shipping/templates/' . $template_upgrade['shipping_template_id'] . '/upgrades/';
                $etsyRequestMethod = 'POST';

                $template_upgrade_response = Tools::jsonDecode(EtsyModule::etsyGetOAuthResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString), true);
                
                if (!empty($template_upgrade_response) && isset($template_upgrade_response['results'])) {
                    $shipping_upgrade_id = $template_upgrade_response['results'][count($template_upgrade_response['results']) - 1]['value_id'];
                    if (!empty($shipping_upgrade_id)) {
                        DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_shipping_upgrades SET shipping_upgrade_id = '" . pSQL($shipping_upgrade_id) . "' WHERE id_etsy_shipping_upgrades = '" . (int) $template_upgrade['id_etsy_shipping_upgrades'] . "'");
                    }
                } else {
                    EtsyModule::auditLogEntry("Error in adding template upgrade (" . $template_upgrade['id_etsy_shipping_upgrades'] . "):" . str_replace("_", " ", key((array) $template_upgrade_response)), $method_name);
                }
            }
        }
        return true;
    }

    /** To update the shipping template upgrades from etsy which has been updated in the system */
    public static function updateTemplateUpgradeOnEtsy()
    {
        $method_name = 'SyncTemplate::updateTemplateUpgradeOnEtsy()';

        $template_upgrades = DB::getInstance()->executeS("SELECT tu.*, st.shipping_template_id FROM " . _DB_PREFIX_ . "etsy_shipping_upgrades tu INNER JOIN " . _DB_PREFIX_ . "etsy_shipping_templates st ON tu.id_etsy_shipping_templates = st.id_etsy_shipping_templates WHERE shipping_upgrade_id IS NOT NULL AND shipping_upgrade_id != '' AND shipping_upgrade_id != 0 AND tu.renew_flag = '1' AND tu.delete_flag = '0' AND shipping_template_id is not NULL");

        if (!empty($template_upgrades)) {
            foreach ($template_upgrades as $template_upgrade) {
                $etsyQueryString = array(
                    'shipping_template_id' => $template_upgrade['shipping_template_id'],
                    'price' => $template_upgrade['shipping_upgrade_primary_cost'],
                    'secondary_price' => $template_upgrade['shipping_upgrade_secondary_cost'],
                    'type' => $template_upgrade['shipping_upgrade_destination'],
                    'value_id' => $template_upgrade['shipping_upgrade_id'],
                );

                $etsyRequestURI = '/shipping/templates/' . $template_upgrade['shipping_template_id'] . '/upgrades/';
                $etsyRequestMethod = 'PUT';

                $template_upgrade_response = Tools::jsonDecode(EtsyModule::etsyGetOAuthResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString), true);
                if (!empty($template_upgrade_response) && isset($template_upgrade_response['results']) && count($template_upgrade_response['results'])) {
                    DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_shipping_upgrades SET renew_flag = '0' WHERE id_etsy_shipping_upgrades = '" . (int) $template_upgrade['id_etsy_shipping_upgrades'] . "' AND id_etsy_shipping_templates = '" . (int) $template_upgrade['id_etsy_shipping_templates'] . "'");
                } else {
                    EtsyModule::auditLogEntry("Error in updating template upgrade (" . $template_upgrade['id_etsy_shipping_upgrades'] . "):" . str_replace("_", " ", key((array) $template_upgrade_response)), $method_name);
                }
            }
        }
        return true;
    }

    //To delete shipping template upgardes from etsy which has been deleted from the system */
    public static function deleteTemplateUpgradesOnEtsy()
    {
        $method_name = 'SyncTemplate::updateTemplateUpgradeOnEtsy()';

        $template_upgrades = DB::getInstance()->executeS("SELECT tu.*, st.shipping_template_id FROM " . _DB_PREFIX_ . "etsy_shipping_upgrades tu INNER JOIN " . _DB_PREFIX_ . "etsy_shipping_templates st ON tu.id_etsy_shipping_templates = st.id_etsy_shipping_templates WHERE shipping_upgrade_id IS NOT NULL AND shipping_upgrade_id != '' AND shipping_upgrade_id != 0 AND tu.delete_flag = '1' AND shipping_template_id is not NULL", true, false);
        if (!empty($template_upgrades)) {
            foreach ($template_upgrades as $template_upgrade) {
                $etsyQueryString = array(
                    'shipping_template_id' => $template_upgrade['shipping_template_id'],
                    'value_id' => $template_upgrade['shipping_upgrade_id'],
                    'type' => $template_upgrade['shipping_upgrade_destination'],
                );

                $etsyRequestURI = '/shipping/templates/' . $template_upgrade['shipping_template_id'] . '/upgrades?method=DELETE';
                $etsyRequestMethod = 'GET';

                $template_upgrade_response = Tools::jsonDecode(EtsyModule::etsyGetOAuthResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString), true);
                if (!empty($template_upgrade_response) && isset($template_upgrade_response->results)) {
                    DB::getInstance()->execute("DELETE FROM " . _DB_PREFIX_ . "etsy_shipping_upgrades WHERE shipping_upgrade_id = '" . pSQL($template_upgrade['shipping_upgrade_id']) . "' AND id_etsy_shipping_upgrades = '" . (int) $template_upgrade['id_etsy_shipping_upgrades'] . "'");
                } else {
                    EtsyModule::auditLogEntry("Error in deleting template upgrade (" . $template_upgrade['id_etsy_shipping_upgrades'] . "):" . str_replace("_", " ", key((array) $template_upgrade_response)), $method_name);
                }
            }
        }
        return true;
    }
}
