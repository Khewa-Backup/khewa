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

class SyncShopSection extends Module
{

    public function __construct()
    {
        parent::__construct();
    }

    /** Create Shop Section on the Etsy */
    public static function createShopSection($shopsection)
    {
        $method_name = 'SyncShopSection::createShopSection()';
        EtsyModule::auditLogEntry('Creating shop section on etsy: ' . $shopsection['shop_section_title'], $method_name);

        $language_data = new Language(Configuration::get('etsy_default_lang'));
        $shopSectionDetails = array(
            'title' => $shopsection['shop_section_title'],
            /**
             * Commented below code as there is no parameters for the language in v3
             * @date 10-04-2023
             * @author Tanisha Gupta
             */
           // 'language' => $language_data->iso_code,
        );
        /**
         * Converted data into the URL-encoded query string from the array
         * @date 10-04-2023
         * @author Tanisha Gupta
         */
        $shopSectionDetails = http_build_query($shopSectionDetails);
        /**
         * Removed json_decode as result is already decoded in the etsyGetResponse function
         * @date 10-04-2023
         * @author Tanisha Gupta
         */
        $shop = EtsyModule::etsyGetShopDetails();
        if(isset($shop['shop_id'])){
            $etsyRequestURI = '/shops/' . $shop['shop_id'] . '/sections';
            $etsyRequestMethod = '';
            $response = EtsyModule::etsyGetResponse($etsyRequestURI, $etsyRequestMethod, $shopSectionDetails);
            if (!empty($response) && isset($response['shop_section_id'])) {
                $shopSectionId = $response['shop_section_id'];
                if (!empty($shopSectionId)) {
                    Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_shop_section SET shop_section_id = '" . pSQL($shopSectionId) . "' WHERE id_etsy_shop_section = '" . (int) $shopsection['id_etsy_shop_section'] . "'");
                }
                EtsyModule::auditLogEntry('Shop section creation on etsy completed.', $method_name);
                return true;
            } else {
                EtsyModule::auditLogEntry("Error in creating the shop section on etsy: " . $response['error'], $method_name);
                return false;
            }
        }else{
                EtsyModule::auditLogEntry("Error in creating the shop section on etsy: " . $shop['error'], $method_name);
                return false;
        }
    }

    /** Update Shop Section on the Etsy */
    public static function updateShopSection($shopsection)
    {
        $method_name = 'SyncShopSection::updateShopSection()';
        EtsyModule::auditLogEntry('Updating shop section on etsy: ' . $shopsection['shop_section_title'], $method_name);

        $language_data = new Language(Configuration::get('etsy_default_lang'));
        $shopSectionDetails = array(
            /**
             * Commented shop_section_id as section id needs to send with the url.
             * Commented language as there is no parameter as language in v3 api
             * @date 10-04-2023
             * @author Tanisha Gupta
             */
            //'shop_section_id' => $shopsection['shop_section_id'],
            'title' => $shopsection['shop_section_title'],
            //'language' => $language_data->iso_code,
        );
        /**
         * Converted data into the URL-encoded query string from the array
         * @date 10-04-2023
         * @author Tanisha Gupta
         */
        $shopSectionDetails = http_build_query($shopSectionDetails);
         $shop = EtsyModule::etsyGetShopDetails();
        if(isset($shop['shop_id'])){
            $etsyRequestURI = '/shops/' . $shop['shop_id'] . '/sections/' . $shopsection['shop_section_id'];
            $etsyRequestMethod = 'PUT';
            $response = EtsyModule::etsyGetResponse($etsyRequestURI, $etsyRequestMethod, $shopSectionDetails);
            if (!empty($response) && isset($response['shop_section_id'])) {
                $shopSectionId = $response['shop_section_id'];
                if (!empty($shopSectionId)) {
                    Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_shop_section SET renew_flag = '0' WHERE shop_section_id = '" . pSQL($shopSectionId) . "' AND id_etsy_shop_section = '" . (int) $shopsection['id_etsy_shop_section'] . "'");
                }
                EtsyModule::auditLogEntry('Shop section updation on etsy completed.', $method_name);
                return true;
            } else {
                EtsyModule::auditLogEntry("Error in updating the shop section on etsy: " . $response['error'], $method_name);
                return false;
            }
        }else{
            EtsyModule::auditLogEntry("Error in updating the shop section on etsy: " .$shop['error'] , $method_name);
            return false;
        }
    }

    /** Update Shop Section on the Etsy */
    public static function deleteShopSection($shopsection)
    {
        $method_name = 'SyncShopSection::deleteShopSection()';
        EtsyModule::auditLogEntry('Deleting the shop section from Etsy: ' . $shopsection['shop_section_title'], $method_name);
        $shop = EtsyModule::etsyGetShopDetails();
        if(isset($shop['shop_id'])){
            $etsyRequestURI = '/shops/' . $shop['shop_id'] . '/sections/' . $shopsection['shop_section_id'];
            $etsyRequestMethod = 'DELETE';
            $etsyQueryString = array();
            $response = EtsyModule::etsyGetResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString);
            if (!empty($response) && isset($response['error'])) {
                EtsyModule::auditLogEntry("Error in deleting the shop section on from etsy: " .$response['error'] , $method_name);
                return false;  
            } else {
                Db::getInstance()->execute("DELETE FROM " . _DB_PREFIX_ . "etsy_shop_section WHERE shop_section_id = '" . pSQL($shopsection['shop_section_id']) . "' AND id_etsy_shop_section = '" . (int) $shopsection['id_etsy_shop_section'] . "'");
                EtsyModule::auditLogEntry('Shop section deleted from the etsy.', $method_name);
                return true;
            }
        }else{
            EtsyModule::auditLogEntry("Error in deleting the shop section on from etsy: " . $response['error'], $method_name);
            return false;
        }
    }

    public static function createShopSections()
    {
        $shopSectionCreated = 0;

        $method_name = 'SyncShopSection::etsyCreateShopSections()';
        EtsyModule::auditLogEntry('Job execution started to create shop sections on etsy.', $method_name);

        $shop_sections = Db::getInstance()->executeS("SELECT * FROM " . _DB_PREFIX_ . "etsy_shop_section WHERE shop_section_id IS NULL or shop_section_id = ''", true, false);
        if (!empty($shop_sections)) {
            foreach ($shop_sections as $shop_section) {
                $shop_section_data = array(
                    'shop_section_title' => $shop_section['shop_section_title'],
                    'id_etsy_shop_section' => $shop_section['id_etsy_shop_section']
                );
                $result = self::createShopSection($shop_section_data);
                if ($result == true) {
                    $shopSectionCreated++;
                }
            }
        }
        EtsyModule::auditLogEntry('Job execution completed to create shop section on etsy.<br>Total shop section created: ' . $shopSectionCreated, $method_name);
        return true;
    }

    public static function updateShopSections()
    {
        $shopSectionRenewed = 0;

        $method_name = 'SyncShopSection::updateShopSections()';
        EtsyModule::auditLogEntry('Job execution started to update the shop sections on etsy.', $method_name);

        $shop_sections = Db::getInstance()->executeS("SELECT * FROM " . _DB_PREFIX_ . "etsy_shop_section WHERE shop_section_id IS NOT NULL AND shop_section_id != '' AND shop_section_id != 0 AND renew_flag = '1' AND delete_flag = '0'", true, false);
        if (!empty($shop_sections)) {
            foreach ($shop_sections as $shop_section) {
                $result = self::updateShopSection($shop_section);
                if ($result == true) {
                    $shopSectionRenewed++;
                }
            }
        }
        EtsyModule::auditLogEntry('Job execution completed to update the shop section from etsy.<br>Total shop section updated: ' . $shopSectionRenewed, $method_name);
        return true;
    }

    public static function deleteShopSections()
    {
        $shopSectionDeleted = 0;

        $method_name = 'SyncShopSection::deleteShopSections()';
        EtsyModule::auditLogEntry('Job execution started to delete the shop sections from etsy.', $method_name);

        $shop_sections = Db::getInstance()->executeS("SELECT * FROM " . _DB_PREFIX_ . "etsy_shop_section WHERE shop_section_id IS NOT NULL AND shop_section_id != '' AND shop_section_id != 0 AND delete_flag = '1'", true, false);
        if (!empty($shop_sections)) {
            foreach ($shop_sections as $shop_section) {
                $result = self::deleteShopSection($shop_section);
                if ($result == true) {
                    $shopSectionDeleted++;
                }
            }
        }
        EtsyModule::auditLogEntry('Job execution completed to delete the shop section from etsy.<br>Total shop section deleted: ' . $shopSectionDeleted, $method_name);
        return true;
    }

    /** Functions to sync etsy shop sections to PrestaShop */
    public static function syncEtsyShopSections()
    {
        $method_name = 'SyncShopSection::syncEtsyShopSections()';
        EtsyModule::auditLogEntry('Job execution started to import shop sections from etsy to prestashop store.', $method_name);
        $etsyShopSections = array();
        $shop = EtsyModule::etsyGetShopDetails();
        if(isset($shop['shop_id'])){
        $etsyQueryString = array();
        $etsyRequestURI = '/shops/' . $shop['shop_id'] . '/sections';
        $etsyRequestMethod = 'GET';

        //$shop_sections = json_decode(EtsyModule::etsyGetOAuthResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString), true);
        $shop_sections = EtsyModule::etsyGetResponse($etsyRequestURI);
        if (count($shop_sections) && isset($shop_sections['results']) && count($shop_sections['results'])) {
            if (!empty($shop_sections['results'])) {
                foreach ($shop_sections['results'] as $shop_section) {
                    $etsyShopSections[] = $shop_section['shop_section_id'];

                    /* If Shop section doesn't exist in the Db, Insert the same OR update the title */
                    $result = Db::getInstance()->getRow("SELECT * FROM " . _DB_PREFIX_ . "etsy_shop_section WHERE shop_section_id = '" . pSQL($shop_section['shop_section_id']) . "'");
                    if ($result === false) {
                        Db::getInstance()->execute("INSERT INTO " . _DB_PREFIX_ . "etsy_shop_section (shop_section_title, shop_section_date_added, shop_section_date_update, shop_section_id) VALUES ('" . pSQL(mb_convert_encoding($shop_section['title'], 'UTF-8', 'HTML-ENTITIES')) . "', NOW(), NOW(),'" . pSQL($shop_section['shop_section_id']) . "')");
                        $log_entry = 'New shop section imported from etsy. Added section is:<br>: ' . $shop_section['title'];
                        EtsyModule::auditLogEntry($log_entry, $method_name);
                    } else {
                        Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_shop_section SET "
                                . "shop_section_title = '" . pSQL($shop_section['title']) . "' "
                                . "WHERE shop_section_id = '" . pSQL($shop_section['shop_section_id']) . "'");
                    }
                }
            }
            /* Delete the Shop sections which are no longer avaliable in the Etsy */
            if (!empty($etsyShopSections)) {
                Db::getInstance()->execute("DELETE FROM " . _DB_PREFIX_ . "etsy_shop_section WHERE (shop_section_id IS NOT NULL AND shop_section_id != 0 AND shop_section_id != '') AND shop_section_id NOT IN ('" . implode("','", $etsyShopSections) . "')");
            } else {
                Db::getInstance()->execute("DELETE FROM " . _DB_PREFIX_ . "etsy_shop_section WHERE (shop_section_id IS NOT NULL AND shop_section_id != 0 AND shop_section_id != '')");
            }
        } else {
            EtsyModule::auditLogEntry("Error in syncing etsy shop sections to prestashop:" . $shop_sections['error'], $method_name);
        }
        }else{
            EtsyModule::auditLogEntry("Error in syncing etsy shop sections to prestashop:" . $shop['error'], $method_name);
        }
        EtsyModule::auditLogEntry('Job execution completed to import shop sections from etsy to prestashop store.', $method_name);
        return true;
    }
}
