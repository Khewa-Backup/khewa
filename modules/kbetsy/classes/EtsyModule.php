<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 * We offer the best and most useful modules PrestaShop and modifications for your online store.
 *
 * @author    knowbandcom <support@knowband.com>
 * @copyright 2017 Knowband
 * @license   see file: LICENSE.txt
 * @category  PrestaShop Module
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(_PS_MODULE_DIR_ . 'kbetsy/libraries/http.php');
require_once(_PS_MODULE_DIR_ . 'kbetsy/libraries/oauth_client.php');
require_once(_PS_MODULE_DIR_ . 'kbetsy/classes/KbMarketplaceIntegration.php');
require_once(_PS_MODULE_DIR_ . 'kbetsy/classes/SyncShopSection.php');
require_once(_PS_MODULE_DIR_ . 'kbetsy/classes/SyncTemplate.php');

class EtsyModule extends Module
{

    public function __construct($index = null, $type = null)
    {
        parent::__construct();

        //List of confirmations
        $module = Module::getInstanceByName('kbetsy');
        $this->_etsyConf = array(
            '1' => $module->l('Settings has been saved & connected to Etsy Marketplace successfully.', 'EtsyModule'),
            '2' => $module->l('Disconnected successfully.', 'EtsyModule'),
            '3' => $module->l('Order settings has been saved successfully.', 'EtsyModule'),
            '4' => $module->l('Item is marked successfully for data updation. Item info will be sycned to etsy on next CRON run.', 'EtsyModule'),
            '5' => $module->l('Relisting of the item has been paused successfully.', 'EtsyModule'),
            '6' => $module->l('Item has marked for relist. Item will be relisted on the next cron run.', 'EtsyModule'),
            '7' => $module->l('Product has been marked for deletion. It will deleted from the etsy on next cron run OR run "Sync Products" cron manaully to delete immediately from etsy.', 'EtsyModule'),
            '8' => $module->l('Profile has been updated successfully.', 'EtsyModule'),
            '9' => $module->l('Profile has been added successfully.', 'EtsyModule'),
            '10' => $module->l('Profile disabled successfully.', 'EtsyModule'),
            '11' => $module->l('Profile enabled successfully.', 'EtsyModule'),
            '12' => $module->l('Profile Deleted Successfully.', 'EtsyModule'),
            '13' => $module->l('Shipping Template Updated Successfully.', 'EtsyModule'),
            '14' => $module->l('Shipping Template Added Successfully.', 'EtsyModule'),
            '15' => $module->l('Shipping Template Deleted Successfully.', 'EtsyModule'),
            '16' => $module->l('Shipping Template Entry Updated Successfully.', 'EtsyModule'),
            '17' => $module->l('Shipping Template Entry Added Successfully.', 'EtsyModule'),
            '18' => $module->l('Shipping Template Entry Deleted Successfully.', 'EtsyModule'),
            '19' => $module->l('Settings has been saved successfully.', 'EtsyModule'),
            '51' => $module->l('Shipping Upgrade Details Updated Successfully.', 'EtsyModule'),
            '52' => $module->l('Shipping Upgrade Added Successfully.', 'EtsyModule'),
            '53' => $module->l('Shipping Upgrade Deleted Successfully.', 'EtsyModule'),
            '54' => $module->l('New category mapped with profile successfully.', 'EtsyModule'),
            '55' => $module->l('Category mapping with profile updated successfully.', 'EtsyModule'),
            '56' => $module->l('Mapped category deleted successfully.', 'EtsyModule'),
            '57' => $module->l('Sorry!!! Some error has occurred during attribute mapping.. Please try again later.', 'EtsyModule'),
            '58' => $module->l('Attribute mapping has been updated successfully.', 'EtsyModule'),
            '59' => $module->l('Attribute mapping deleted successfully.', 'EtsyModule'),
            '60' => $module->l('Shop Section has been added successfully.', 'EtsyModule'),
            '61' => $module->l('Shop Section has been updated successfully.', 'EtsyModule'),
            '62' => $module->l('Shop Section has been deleted successfully.', 'EtsyModule'),
            '63' => $module->l('Selected products status has been updated successfully.', 'EtsyModule'),
            '64' => $module->l('The product has been enabled successfully.', 'EtsyModule'),
            '65' => $module->l('The product has been disabled successfully.', 'EtsyModule'),
            '66' => $module->l('Deletion has been stopped successfully.', 'EtsyModule')
        );

        //List of errors
        $this->_etsyError = array(
            '1' => $module->l('Settings has been saved but connection with the etsy could not be established. Please try again.', 'EtsyModule'),
            '2' => $module->l('Listing renewal failed. Try to relist product instead.', 'EtsyModule'),
            '3' => $module->l('Listing halt failed. Try to relist product instead.', 'EtsyModule'),
            '4' => $module->l('The product can not be deleted. Please try again', 'EtsyModule'),
            '5' => $module->l('Please provide valid Profile Title. Length should be between 0 - 255.', 'EtsyModule'),
            '6' => $module->l('Please select store categories to map with Profile.', 'EtsyModule'),
            '7' => $module->l('Profile already exists for atleast one of selected categories.', 'EtsyModule'),
            '8' => $module->l('Profile could not be deleted.', 'EtsyModule'),
            '9' => $module->l('Something went wrong.', 'EtsyModule'),
            '10' => $module->l('Please provide valid Shipping Template Title. Length should be between 0 - 255.', 'EtsyModule'),
            '11' => $module->l('Please choose Origin Country.', 'EtsyModule'),
            '12' => $module->l('Please enter valid Primary Shipping Cost.', 'EtsyModule'),
            '13' => $module->l('Please enter valid Secondary Shipping Cost.', 'EtsyModule'),
            '14' => $module->l('Please enter minimum number of processing days.', 'EtsyModule'),
            '15' => $module->l('Please enter maximum number of processing days.', 'EtsyModule'),
            '16' => $module->l('Minimum Processing Days cannot be greater than or equal to Maximum Processing Days.', 'EtsyModule'),
            '17' => $module->l('Shipping template with the same name already exist.', 'EtsyModule'),
            '18' => $module->l('Shipping Template could not be deleted.', 'EtsyModule'),
            '19' => $module->l('Origin Country cannot be empty.', 'EtsyModule'),
            '20' => $module->l('Please choose Destination Country.', 'EtsyModule'),
            '21' => $module->l('Origin and Destination cannot be same.', 'EtsyModule'),
            '22' => $module->l('Please choose Destination Region.', 'EtsyModule'),
            '23' => $module->l('Please enter valid Primary Shipping Cost.', 'EtsyModule'),
            '24' => $module->l('Please enter valid Secondary Shipping Cost.', 'EtsyModule'),
            '25' => $module->l('Provided details of Shipping Template Entry already exist.', 'EtsyModule'),
            '26' => $module->l('Shipping Template Entry could not be deleted.', 'EtsyModule'),
            '27' => $module->l('Shipping Template could not be deleted as it is being used in Profiles.', 'EtsyModule'),
            '28' => $module->l('Profile and Etsy Category already exist.', 'EtsyModule'),
            '29' => $module->l('Attribute already exist with Etsy Category, Store Category.', 'EtsyModule'),
            '30' => $module->l('Category cannot be deleted as atleast one category must be exist for the profile.', 'EtsyModule'),
            '31' => $module->l('Store category already exist with other Etsy Category.', 'EtsyModule'),
            '51' => $module->l('Provided details of Shipping Upgrade already exist.', 'EtsyModule'),
            '52' => $module->l('Shipping Upgrade could not be deleted.', 'EtsyModule'),
            '53' => $module->l('Shipping Upgrade details already deleted.', 'EtsyModule'),
            '54' => $module->l('Mapping already exist for selected store attribute.', 'EtsyModule'),
            '55' => $module->l('Please choose Etsy attribute from list.', 'EtsyModule'),
            '56' => $module->l('Please choose Store attribute from list.', 'EtsyModule'),
            '57' => $module->l('Sorry!!! Some error has occurred during attribute mapping.. Please try again later.', 'EtsyModule'),
            '58' => $module->l('Shop Section could not be deleted. Please try again later.', 'EtsyModule'),
            '59' => $module->l('Shop Section could not be deleted as it is being used in Profiles.', 'EtsyModule'),
            '64' => $module->l('Shop Section already exists. Please choose another title.', 'EtsyModule'),
            '65' => $module->l('Shop section can not be deleted because it is mapped with the Profile.', 'EtsyModule'),
        );

        if (!empty($index) && !empty($type) && $type == 'conf') {
            $index = explode(",", $index);
            foreach ($index as $value) {
                Context::getContext()->controller->confirmations[] = $this->_etsyConf[$value];
            }
        }

        if (!empty($index) && !empty($type) && $type == 'error') {
            $index = explode(",", $index);
            foreach ($index as $value) {
                Context::getContext()->controller->errors[] = $this->_etsyError[$value];
            }
        }
    }

    //Function definition to add an entry of Audit Log into Database
    public static function auditLogEntry($auditLogEntry = '', $auditMethodName = '', $auditLogUser = '')
    {
        $auditLogTime = date("Y-m-d H:i:s");

        if (empty($auditLogUser)) {
            if (!empty(Context::getContext()->employee->firstname) && !empty(Context::getContext()->employee->lastname)) {
                $auditLogUser = Context::getContext()->employee->firstname . ' ' . Context::getContext()->employee->lastname;
            } else {
                $auditLogUser = 'Default';
            }
        }

        if (!empty($auditLogEntry) && !empty($auditLogUser) && !empty($auditMethodName) && !empty($auditLogTime)) {
            $auditLogSQL = "INSERT INTO " . _DB_PREFIX_ . "etsy_audit_log VALUES (NULL, '" . pSQL($auditLogEntry, true) . "', '" . pSQL($auditLogUser) . "', '" . pSQL($auditMethodName) . "', '" . pSQL($auditLogTime) . "');";
            if (Db::getInstance()->execute($auditLogSQL)) {
                return true;
            }
            return false;
        } else {
            return false;
        }
    }

    //Get shipping template title by ID
    public static function getShippingTemplateTitleByID($shippingTemplateID = '')
    {
        if (!empty($shippingTemplateID)) {
            $template_details = Db::getInstance()->getRow("SELECT shipping_template_title FROM " . _DB_PREFIX_ . "etsy_shipping_templates WHERE id_etsy_shipping_templates = '" . (int) $shippingTemplateID . "'", true, false);

            if ($template_details) {
                return $template_details['shipping_template_title'];
            }
        }
    }

    //Function definition to send OAuth Request to Etsy Marketplace and get response
    public static function etsyGetOAuthResponse($etsyRequestURI = '', $etsyRequestMethod = '', $etsyQueryString = array(), $imageUpload = false, $fileUpload = false)
    {

        if (!empty($etsyRequestURI) && !empty($etsyRequestMethod)) {
            $etsyClient = new oauth_client_class;
            $etsyClient->server = 'Etsy';
            $etsyClient->debug = false;
            $etsyClient->debug_http = false;

            $etsyClient->client_id = Configuration::get('etsy_api_key');
            $etsyClient->client_secret = Configuration::get('etsy_api_secret');

            $etsyClient->scope = 'email_r listings_w listings_d listings_r transactions_r transactions_w shops_rw';
            $etsyClient->access_token = '';

            $etsyResponse = '';
            $etsyOAuthAccessToken = Configuration::get('etsy_oauth_access_token');
            $etsySwitchValue = Configuration::get('etsy_switch_value');
            if (!empty($etsyOAuthAccessToken) && !empty($etsySwitchValue) && $etsySwitchValue) {
                $accessTokenData = explode("#$#", $etsyOAuthAccessToken);
                $etsyClient->access_token = $accessTokenData[0];
                $etsyClient->access_token_secret = $accessTokenData[1];
                if ($etsySuccess = $etsyClient->Initialize()) {
                    if (Tools::strlen($etsyClient->access_token)) {
                        if ($imageUpload) {
                            $etsySuccess = $etsyClient->CallAPI(Configuration::get('etsy_api_host') . Configuration::get('etsy_api_version') . $etsyRequestURI, $etsyRequestMethod, $etsyQueryString, array('FailOnAccessError' => true, 'Files' => array('image' => array('ContentType' => 'image/jpeg'))), $etsyResponse);
                        } else if ($fileUpload) {
                            $mimetype = $etsyQueryString['mimetype'];
                            unset($etsyQueryString['mimetype']);
                            $etsySuccess = $etsyClient->CallAPI(Configuration::get('etsy_api_host') . Configuration::get('etsy_api_version') . $etsyRequestURI, $etsyRequestMethod, $etsyQueryString, array('FailOnAccessError' => true, 'Files' => array('file' => array('ContentType' => $mimetype))), $etsyResponse);
                        } else {
                            $etsySuccess = $etsyClient->CallAPI(Configuration::get('etsy_api_host') . Configuration::get('etsy_api_version') . $etsyRequestURI, $etsyRequestMethod, $etsyQueryString, array('FailOnAccessError' => true), $etsyResponse);
                        }
                    }
                    $etsySuccess = $etsyClient->Finalize($etsySuccess);
                }
            } else {
                if ($etsySuccess = $etsyClient->Initialize()) {
                    if ($etsySuccess = $etsyClient->Process()) {
                        if (Tools::strlen($etsyClient->access_token)) {
                            if ($imageUpload) {
                                $etsySuccess = $etsyClient->CallAPI(Configuration::get('etsy_api_host') . Configuration::get('etsy_api_version') . $etsyRequestURI, $etsyRequestMethod, $etsyQueryString, array('FailOnAccessError' => true, 'Files' => array('image' => array('ContentType' => 'image/jpeg'))), $etsyResponse);
                            } else if ($fileUpload) {
                                $mimetype = $etsyQueryString['mimetype'];
                                unset($etsyQueryString['mimetype']);
                                $etsySuccess = $etsyClient->CallAPI(Configuration::get('etsy_api_host') . Configuration::get('etsy_api_version') . $etsyRequestURI, $etsyRequestMethod, $etsyQueryString, array('FailOnAccessError' => true, 'Files' => array('file' => array('ContentType' => $mimetype))), $etsyResponse);
                            } else {
                                $etsySuccess = $etsyClient->CallAPI(Configuration::get('etsy_api_host') . Configuration::get('etsy_api_version') . $etsyRequestURI, $etsyRequestMethod, $etsyQueryString, array('FailOnAccessError' => true), $etsyResponse);
                            }
                        }
                    }
                    $etsySuccess = $etsyClient->Finalize($etsySuccess);
                }
            }

            if ($etsyClient->exit) {
                exit;
            }

            if (!empty($etsyClient->response_headers['x-ratelimit-remaining'])) {
                Configuration::updateValue('KBETSY_REMAINING_LIMIT', $etsyClient->response_headers['x-ratelimit-remaining'] . "/" . $etsyClient->response_headers['x-ratelimit-limit']);
            }

            if ($etsySuccess) {
                return Tools::jsonEncode($etsyResponse);
            } else {
                return Tools::jsonEncode($etsyResponse);
            }
        } else {
            return "Invalid Request.";
        }
    }

    //Send cURL request to Etsy & get response
    public static function etsyGetResponse($etsyRequestURI = '', $etsyRequestMethod = '', $etsyQueryString = '')
    {
        if (!empty($etsyRequestURI)) {
            $etsycURL = curl_init();
            curl_setopt($etsycURL, CURLOPT_URL, $etsyRequestURI);
            if ($etsyRequestMethod && $etsyQueryString != '') {
                curl_setopt($etsycURL, CURLOPT_POST, 1);
                curl_setopt($etsycURL, CURLOPT_POSTFIELDS, $etsyQueryString);
            }
            curl_setopt($etsycURL, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($etsycURL, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($etsycURL, CURLOPT_RETURNTRANSFER, 1);
            $etsyResult = curl_exec($etsycURL);
            curl_close($etsycURL);
            return $etsyResult;
        } else {
            return 'failed';
        }
    }

    //Function definition to test oAuth connection with Etsy Marketplace API
    public static function etsyTestConnection($apiData = array())
    {
        //Audit Log Entry
        $auditLogEntryString = 'Job execution statrted to setup connection with Etsy Marketplace.';
        $auditMethodName = 'EtsyModule::etsyTestConnection()';
        self::auditLogEntry($auditLogEntryString, $auditMethodName);
        $etsyRequestURI = '/users/__SELF__';
        $etsyRequestMethod = 'GET';
        $etsyQueryString = array();

        if (!empty($apiData) && !empty($etsyRequestURI) && !empty($etsyRequestMethod)) {
            $etsyClient = new oauth_client_class;
            $etsyClient->server = 'Etsy';
            $etsyClient->debug = true;
            $etsyClient->debug_http = true;
            $etsyClient->redirect_uri = Context::getContext()->link->getModuleLink('kbetsy', 'cron', array('action' => 'testConnection'));

            $etsyClient->client_id = $apiData['etsy_api_key'];
            $etsyClient->client_secret = $apiData['etsy_api_secret'];

            $etsyClient->scope = 'email_r listings_w listings_d listings_r transactions_r transactions_w shops_rw';
            $etsyClient->access_token = '';

            $etsyResponse = '';
            $etsyOAuthAccessToken = Configuration::get('etsy_oauth_access_token');
            $etsySwitchValue = Configuration::get('etsy_switch_value');
            if (!empty($etsyOAuthAccessToken) && !empty($etsySwitchValue) && $etsySwitchValue) {
                $accessTokenData = explode("#$#", $etsyOAuthAccessToken);
                $etsyClient->access_token = $accessTokenData[0];
                $etsyClient->access_token_secret = $accessTokenData[1];
                if ($etsySuccess = $etsyClient->Initialize()) {
                    if (Tools::strlen($etsyClient->access_token)) {
                        $etsySuccess = $etsyClient->CallAPI($apiData['etsy_api_host'] . $apiData['etsy_api_version'] . $etsyRequestURI, $etsyRequestMethod, $etsyQueryString, array('FailOnAccessError' => true), $etsyResponse);
                    }
                    $etsySuccess = $etsyClient->Finalize($etsySuccess);
                }
            } else {
                if ($etsySuccess = $etsyClient->Initialize()) {
                    if ($etsySuccess = $etsyClient->Process()) {
                        if (Tools::strlen($etsyClient->access_token)) {
                            $etsySuccess = $etsyClient->CallAPI($apiData['etsy_api_host'] . $apiData['etsy_api_version'] . $etsyRequestURI, $etsyRequestMethod, $etsyQueryString, array('FailOnAccessError' => true), $etsyResponse);
                        }
                    }
                    $etsySuccess = $etsyClient->Finalize($etsySuccess);
                }
            }

            if ($etsyClient->exit) {
                exit;
            }

            //Audit Log Entry
            $auditLogEntryString = 'Job execution completed to setup connection with Etsy Marketplace.';
            $auditMethodName = 'EtsyModule::etsyTestConnection()';
            self::auditLogEntry($auditLogEntryString, $auditMethodName);

            if ($etsySuccess) {
                if (!empty($etsyResponse) && isset($etsyResponse->results)) {
                    //If connection established
                    if (!empty($etsyClient->access_token)) {
                        Configuration::updateGlobalValue('etsy_oauth_access_token', $etsyClient->access_token . '#$#' . $etsyClient->access_token_secret);
                        Configuration::updateGlobalValue('etsy_api_user_id', $etsyResponse->results[0]->user_id);
                        Tools::redirect(Configuration::get('etsy_redirect_url') . '&etsyConf=1');
                    } else {
                        //If connection failed
                        Configuration::updateGlobalValue('etsy_oauth_access_token', '');
                        Tools::redirect(Configuration::get('etsy_redirect_url') . '&etsyError=1');
                    }
                } else {
                    //If connection failed
                    Configuration::updateGlobalValue('etsy_oauth_access_token', '');
                    Tools::redirect(Configuration::get('etsy_redirect_url') . '&etsyError=1');
                }
            } else {
                //If connection failed
                Configuration::updateGlobalValue('etsy_oauth_access_token', '');
                Tools::redirect(Configuration::get('etsy_redirect_url') . '&etsyError=1');
            }
        }
    }

    //To disconnect connection with Etsy
    public static function disconnect()
    {
        $oauthClass = new oauth_client_class();
        $oauthClass->disconnectEtsy();
    }

    //To get user etsy shops details
    public static function etsyGetShopDetails()
    {
        $etsyRequestURI = Configuration::get('etsy_api_host') . Configuration::get('etsy_api_version') . "/users/" . Configuration::get('etsy_api_user_id') . "/shops/?api_key=" . Configuration::get('etsy_api_key');
        return self::etsyGetResponse($etsyRequestURI);
    }

    //Function to save All Countries
    public static function etsyGetAllCountries()
    {
        $etsyRequestURI = Configuration::get('etsy_api_host') . Configuration::get('etsy_api_version') . "/countries/?api_key=" . Configuration::get('etsy_api_key');
        $etsyCountriesList = self::etsyGetResponse($etsyRequestURI);
        if (!empty($etsyCountriesList)) {
            $etsyCountriesList = Tools::jsonDecode($etsyCountriesList);
            if (isset($etsyCountriesList->results)) {
                $emptyDBCountryList = "TRUNCATE TABLE " . _DB_PREFIX_ . "etsy_countries";
                if (Db::getInstance()->execute($emptyDBCountryList)) {
                    foreach ($etsyCountriesList->results as $etsyCountry) {
                        Db::getInstance()->execute("INSERT INTO " . _DB_PREFIX_ . "etsy_countries VALUES (NULL, '" . (int) $etsyCountry->country_id . "', '" . pSQL($etsyCountry->name) . "', '" . pSQL($etsyCountry->iso_country_code) . "')");
                    }
                }
            }
        }
        return true;
    }

    public static function etsyGetAllRegions()
    {
        $etsyRequestURI = Configuration::get('etsy_api_host') . Configuration::get('etsy_api_version') . "/regions/?api_key=" . Configuration::get('etsy_api_key');
        $etsyRegionsList = self::etsyGetResponse($etsyRequestURI);
        if (!empty($etsyRegionsList)) {
            $etsyRegionsList = Tools::jsonDecode($etsyRegionsList);
            if (isset($etsyRegionsList->results)) {
                Db::getInstance()->execute("TRUNCATE TABLE " . _DB_PREFIX_ . "etsy_regions");
                foreach ($etsyRegionsList->results as $etsyRegion) {
                    Db::getInstance()->execute("INSERT INTO " . _DB_PREFIX_ . "etsy_regions VALUES (NULL, '" . (int) $etsyRegion->region_id . "', '" . pSQL($etsyRegion->region_name) . "')");
                }
            }
        }
        return true;
    }

    /** To sync profile products from PS table to etsy table */
    public static function getAllProfileProducts()
    {
        $method_name = 'EtsyModule::getAllProfileProducts()';
        EtsyModule::auditLogEntry('Local Sync job execution statrted.', $method_name);

        $productsInserted = 0;
        $productsUpdated = 0;

        DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET delete_track = '1'");

        $profiles = DB::getInstance()->executeS("SELECT * FROM " . _DB_PREFIX_ . "etsy_profiles WHERE active = '1'");
        if (!empty($profiles)) {
            foreach ($profiles as $profile) {
                /* If Product Selection Type is Product in the Profile */
                if ($profile['etsy_product_type'] == 1) {
                    $etsy_selected_products = $profile['etsy_selected_products'];
                    $etsy_selected_product_array = explode("-", $etsy_selected_products);
                    if (!empty($etsy_selected_product_array)) {
                        foreach ($etsy_selected_product_array as $etsy_product) {
                            if (!empty($etsy_product)) {
                                /** Check if product is already exists in DB table */
                                $dataExistenceResult = Db::getInstance()->getValue("SELECT count(*) as count FROM " . _DB_PREFIX_ . "etsy_products_list WHERE id_product = '" . (int) $etsy_product . "'");

                                $product_info = new Product($etsy_product, false, Context::getContext()->language->id);
                                if ($dataExistenceResult == 0) {
                                    $insertSQL = "INSERT INTO " . _DB_PREFIX_ . "etsy_products_list SET "
                                            . "id_etsy_products_list = '', "
                                            . "id_etsy_profiles = '" . (int) $profile['id_etsy_profiles'] . "',"
                                            . "id_product = '" . (int) $etsy_product . "', "
                                            . "reference = '" . pSQL($product_info->reference) . "', "
                                            . "delete_track = '0',"
                                            . "date_added = NOW()";
                                    DB::getInstance()->execute($insertSQL);
                                    $productsInserted++;
                                } else {
                                    $updateSQL = "UPDATE " . _DB_PREFIX_ . "etsy_products_list SET "
                                            . "id_etsy_profiles = '" . (int) $profile['id_etsy_profiles'] . "',"
                                            . "reference = '" . pSQL($product_info->reference) . "',"
                                            . "delete_track = '0', "
                                            . "is_error = '0'"
                                            . "WHERE id_product = '" . (int) $etsy_product . "'";
                                    if (DB::getInstance()->execute($updateSQL)) {
                                        $productsUpdated++;
                                    }
                                }
                            }
                        }
                    }
                } else {
                    $categories = self::getStoreCategories($profile);

                    //Get Products as per the categories selected under profile.
                    if (!empty($categories)) {
                        foreach ($categories as $category) {
                            $categoryProductCount = KbMarketplaceIntegration::getCountProductByDefaultCategoryId($category);
                            if (isset($categoryProductCount['error']) && $categoryProductCount['error'] == '' && $categoryProductCount['success'] > 0) {
                                $categoryProductsList = KbMarketplaceIntegration::getProductsByDefaultCategoryId($category, 0, $categoryProductCount['success']);

                                if (isset($categoryProductsList['error']) && $categoryProductsList['error'] == '') {
                                    foreach ($categoryProductsList['success'] as $categoryProduct) {

                                        /** Check if product is already exists in DB table */
                                        $dataExistenceResult = Db::getInstance()->getValue("SELECT count(*) as count FROM " . _DB_PREFIX_ . "etsy_products_list WHERE id_product = '" . (int) $categoryProduct['id_product'] . "'");
                                        if ($dataExistenceResult == 0) {
                                            $insertSQL = "INSERT INTO " . _DB_PREFIX_ . "etsy_products_list SET "
                                                    . "id_etsy_products_list = '', "
                                                    . "id_etsy_profiles = '" . (int) $profile['id_etsy_profiles'] . "',"
                                                    . "id_product = '" . (int) $categoryProduct['id_product'] . "', "
                                                    . "reference = '" . pSQL($categoryProduct['reference']) . "', "
                                                    . "delete_track = '0',"
                                                    . "date_added = NOW()";
                                            DB::getInstance()->execute($insertSQL);
                                            $productsInserted++;
                                        } else {
                                            $updateSQL = "UPDATE " . _DB_PREFIX_ . "etsy_products_list SET "
                                                    . "id_etsy_profiles = '" . (int) $profile['id_etsy_profiles'] . "',"
                                                    . "reference = '" . pSQL($categoryProduct['reference']) . "',"
                                                    . "delete_track = '0', "
                                                    . "is_error = '0'"
                                                    . "WHERE id_product = '" . (int) $categoryProduct['id_product'] . "'";
                                            if (DB::getInstance()->execute($updateSQL)) {
                                                $productsUpdated++;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        //Set delete flag for the products which are not present the the list (OR directly delete from the DB if products are not listed on etsy)
        //Set Profile ID to 0 for such products so that once item is made inactive on etsy (on deleteItemsFromEtsy function execution). Item will be deleted from the table if profile id is blank as its unmapped from the profile
        DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET id_etsy_profiles = 0, delete_flag = '1' WHERE delete_track = '1' AND listing_id IS NOT NULL");

        DB::getInstance()->execute("DELETE FROM " . _DB_PREFIX_ . "etsy_products_list WHERE delete_track = '1' AND listing_id IS NULL");

        $auditLogEntryString = 'Local sync execution completed.<br>Total products added - ' . $productsInserted . ' <br>Total Products Updated - ' . $productsUpdated;
        EtsyModule::auditLogEntry($auditLogEntryString, $method_name);

        return true;
    }

    //Fetch store categories based on multiple category mapping
    public static function getStoreCategories($etsy_profile)
    {
        $categories = array();
        $category_mappings = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'etsy_category_mapping WHERE id_etsy_profiles = ' . (int) $etsy_profile['id_etsy_profiles'], true, false);
        if (!Tools::isEmpty($category_mappings) && is_array($category_mappings)) {
            foreach ($category_mappings as $category_mapping) {
                $prestashop_category = explode(',', $category_mapping['prestashop_category']);
                if (is_array($prestashop_category) && !empty($prestashop_category)) {
                    foreach ($prestashop_category as $sub_category) {
                        $categories[] = $sub_category;
                    }
                } elseif (!is_array($prestashop_category) && !empty($prestashop_category)) {
                    $categories[] = $prestashop_category;
                }
            }
        }
        return $categories;
    }

    /** To get products from table which needs to be listed */
    public static function getProductsToListOnEtsy($limit, $kbproductid = false)
    {
        /** TODO Add Producy status condtion& join with product table */
        /*
         * @author - Rishabh Jain
         * DOC - 2nd Apr 2020
         * Added stock available condition to avoid products having 0 quantity to sync on etsy
         */
        $products_query = "SELECT pl.* FROM " . _DB_PREFIX_ . "etsy_products_list pl "
                . "INNER JOIN " . _DB_PREFIX_ . "product p ON p.id_product = pl.id_product "
                . "WHERE p.active = '1'"
                . "AND delete_flag = '0' "
                . "AND pl.active = '1'"
                . "AND (listing_status = 'Updated' OR listing_status = 'Pending' OR listing_status = 'Relisting')";
        // OR listing_status = 'Sold Out' Removed the Sold_Out status as on product update from the admin panel, We are setting the status to Updated. To avoid the following situation: In case of large number of Sold Out Product, CRON will stuck in exectuing starting 20 Sold Out products each time
        if ($kbproductid) {
            $products_query .= ' AND pl.id_product = ' . (int) $kbproductid;
        } else {
            $products_query .= ' AND is_error = 0';
        }
        $products_query .= ' LIMIT ' . $limit;
        return DB::getInstance()->executeS($products_query, true, false);
    }

    /** To prepare array to listing on etsy */
    public static function prepareArrayToListingOnEtsy($product = array(), $language = '', $updateListing = 0, $renewListing = 0)
    {
        $listingArray = array();
        if (isset($product) && count($product) > 0 && !empty($language)) {
            $profile_details = DB::getInstance()->getRow("SELECT ef.*, ss.shop_section_id FROM " . _DB_PREFIX_ . "etsy_profiles ef "
                    . "LEFT JOIN " . _DB_PREFIX_ . "etsy_shop_section ss "
                    . "on (ef.id_etsy_shop_section = ss.id_etsy_shop_section) "
                    . "WHERE id_etsy_profiles = '" . (int) $product['id_etsy_profiles'] . "'", true, false);

            //Get Product Inventory
            $quantity = KbMarketplaceIntegration::getProductInventory($product['id_product']);
            /*
             * changes by ridhabh jain for out of stock product
             */
            if ($quantity <= 0) {
                $pro_obj = new Product($product['id_product']);
                $stock = $pro_obj->out_of_stock;
                if ((int)$stock == 1) {
                    $quantity = 999;
                } else if ((int)$stock == 2) {
                    $out_of_stock = Configuration::get('PS_ORDER_OUT_OF_STOCK');
                    if ($out_of_stock == 1) {
                        $quantity = 999;
                    }
                }
            }
            /*
             * changes over
             */
            $product_details = KbMarketplaceIntegration::getProductByProductId($product['id_product'], $language);

            $tagArray = array();
            $tagTempArray = array();
            $productTags = Tag::getProductTags($product['id_product']);
            if (count($productTags) && isset($productTags[$language])) {
                $tagArray = $productTags[$language];
                if (count($tagArray) > 13) {
                    $tagArray = array_slice($tagArray, 0, 13);
                }
            }
            if (count($tagArray)) {
                foreach ($tagArray as $tag) {
                    $tag = preg_replace('/[^A-Za-z0-9 ]/', '', $tag);
                    $tagTempArray[Tools::strtolower(Tools::substr($tag, 0, 19))] = Tools::substr($tag, 0, 19); // TO make tag unique & length upto 20 Char. strtolowe to avoid dupliate due to case sensitivity
                }
            }
            $tagTempArray = array_unique($tagTempArray);


            $featureArray = array();
            if (isset($profile_details['material_feature']) && is_numeric($profile_details['material_feature'])) {
                $selected_feature_id = $profile_details['material_feature'];
                $features = $product_details->getFrontFeatures($language);
                if (count($features)) {
                    foreach ($features as $feature) {
                        if ($feature['id_feature'] == $selected_feature_id) {
                            $featureArray = explode(',', $feature['value'], 13);
                        }
                    }
                }
            }

            $featureTempArray = array();
            if (count($featureArray)) {
                foreach ($featureArray as $feature) {
                    $feature = preg_replace('/[^A-Za-z0-9 ]/', '', $feature);
                    $featureTempArray[] = Tools::substr($feature, 0, 45);
                }
            }

            /* If Quantity is greater than 0 & Item is marked as Sold Out then mark the item for relist again */
            //if ($product['sold_flag'] == 1) {
            //DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_status = 'Relisting', delete_flag = '0', is_error = 0, renew_flag = '1', sold_flag = '0' WHERE  id_product = '" . (int) $product_details->id . "'");
            //} else {
            $profileCategory = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'etsy_category_mapping WHERE id_etsy_profiles = ' . (int) $product['id_etsy_profiles'], true, false);
            $etsy_category = self::getEtsyCategorybyProfileANDCategory($profileCategory, $product_details->id_category_default, $profile_details['etsy_product_type']);

            //Get Shipping Template ID
            $shipping_template_id = DB::getInstance()->getValue("SELECT shipping_template_id FROM " . _DB_PREFIX_ . "etsy_shipping_templates WHERE id_etsy_shipping_templates = '" . (int) $profile_details['id_etsy_shipping_templates'] . "'");

            $lang_data = new Language($language);
            if (!empty($shipping_template_id)) {
                if ($quantity > 999) {
                    $quantity = 999;
                }

                $price = Product::getPriceStatic($product['id_product'], true, null, 6, null, false, true);

                $customize_price = $profile_details['custom_pricing'];
                $customize_price_value = $profile_details['custom_price'];
                $customize_price_type = $profile_details['price_type'];
                $customize_price_reduction = $profile_details['price_reduction'];

                $price_change = '';
                if ($customize_price) {
                    if ($customize_price_type == 'Percentage') {
                        $price_change = ($customize_price_value / 100) * $price;
                    } else {
                        $price_change = $customize_price_value;
                    }
                    if ($customize_price_reduction == 'increase') {
                        $price = $price + $price_change;
                    } else {
                        $price = $price - $price_change;
                    }
                }
                if ($price < 0) {
                    $price = 0;
                }

                $etsy_desc_type = Configuration::get('etsy_desc_type');
                $etsy_currency_id = Currency::getIdByIsoCode(Configuration::get('etsy_currency'), Context::getContext()->shop->id);

                $strip_tags = array('</p>', '<br />', '<br>', '</div>', '</li>');
                if ($etsy_desc_type == 'short') {
                    $description = str_replace($strip_tags, "\n", $product_details->description_short);
                } else if ($etsy_desc_type == 'long') {
                    $description = str_replace($strip_tags, "\n", $product_details->description);
                } else {
                    if (Tools::isEmpty($product_details->description_short)) {
                        $description = str_replace($strip_tags, "\n", $product_details->description);
                    } else {
                        $description = str_replace($strip_tags, "\n", $product_details->description_short . "\n" . $product_details->description);
                    }
                }
                $description = trim(strip_tags($description));

                $short_description = strip_tags(str_replace($strip_tags, "\n", $product_details->description_short));
                $customize_title = $profile_details['customize_product_title'];
                if (!Tools::isEmpty($customize_title)) {
                    $customize_title = str_replace('{product_title}', $product_details->name, $customize_title);
                    $customize_title = str_replace('{id_product}', $product_details->id, $customize_title);
                    $customize_title = str_replace('{manufacturer_name}', Manufacturer::getNameById($product_details->id_manufacturer), $customize_title);
                    $customize_title = str_replace('{supplier_name}', $product_details->supplier_name, $customize_title);
                    $customize_title = str_replace('{reference}', $product_details->reference, $customize_title);
                    $customize_title = str_replace('{ean13}', $product_details->ean13, $customize_title);
                    $customize_title = str_replace('{short_description}', $short_description, $customize_title);
                    $customize_title = str_replace('{price}', Tools::convertPrice($price, $etsy_currency_id), $customize_title);
                } else {
                    $customize_title = $product_details->name;
                }

                if (Tools::isEmpty($description)) {
                    $description = "NA";
                }
                
                // As per etsy, title cannot include any of the following characters: $ ^ `.
                $filtered_title = str_replace(array("$", "^", ".", "`"), array("", "", "", ""), $customize_title);
                
                //Title cannot contain the characters %, &, or : more than once
                $filtered_title = self::replaceInstance($filtered_title);
                
                $filtered_title = Tools::substr(trim($filtered_title), 0, 140);

                $listingArray = array(
                    'id_product' => $product['id_product'],
                    'id_profile' => $profile_details['id_etsy_profiles'],
                    'quantity' => $quantity,
                    'sku' => $product_details->reference,
                    'title' => $filtered_title,
                    'description' => $description,
                    'tags' => implode(',', $tagTempArray),
                    'price' => Tools::convertPrice($price, $etsy_currency_id),
                    'is_customizable' => $profile_details['is_customizable'],
                    'taxonomy_id' => $etsy_category,
                    'who_made' => $profile_details['who_made'],
                    'is_supply' => $profile_details['is_supply'],
                    'when_made' => $profile_details['when_made'],
                    'shop_section_id' => $profile_details['shop_section_id'],
                    'occassion' => $profile_details['occassion'],
                    'should_auto_renew' => $profile_details['should_auto_renew'],
                    'language' => Tools::strtolower($lang_data->iso_code),
                    'shipping_template_id' => $shipping_template_id,
                    'materials' => implode(',', $featureTempArray),
                    'listing_status' => $product['listing_status']
                );

                //In case recipient is not provided
//                if (empty($profile_details['recipient'])) {
//                    unset($listingArray['recipient']);
//                }
                //changes by gopi for sycing weight and dimension on 23 march 2021
                $dimension_unit = Configuration::get('PS_DIMENSION_UNIT');
                $weight_unit = Configuration::get('PS_WEIGHT_UNIT');
                //only below mentioned units are allowed on etsy
                $etsy_allowed_weight_unit = array('oz', 'lb', 'g', 'kg');
                $etsy_dimension_allowed = array('in', 'ft', 'mm', 'cm', 'm');
                if ($weight_unit != '' && in_array($weight_unit, $etsy_allowed_weight_unit)) {

                    
                    $listingArray['item_weight'] = (float) number_format((float) $product_details->weight, 2, '.', '');
                    if ($weight_unit = 'kg') {
                        $listingArray['item_weight']  =  $listingArray['item_weight'] *1000;
                        $weight_unit = 'g';
                    }
                    $listingArray['item_weight_unit'] = $weight_unit; 

                }
                
                if ($dimension_unit != '' && in_array($dimension_unit, $etsy_dimension_allowed)) {
                    $listingArray['item_length'] = (float) number_format((float) $product_details->width, 2, '.', '');
                    $listingArray['item_width'] = (float) number_format((float) $product_details->height, 2, '.', '');
                    $listingArray['item_height'] = (float) number_format((float) $product_details->depth, 2, '.', '');
                    if ($dimension_unit = 'cm') {
                        $listingArray['item_length'] = $listingArray['item_length']* 10;
                        $listingArray['item_width'] =  $listingArray['item_width'] * 10;
                        $listingArray['item_height'] =  $listingArray['item_height'] * 10;
                        $dimension_unit = 'mm';
                    }
                    $listingArray['item_dimensions_unit'] = $dimension_unit;   
                }
                //print_r($listingArray);die;
                //changes by gopi end here
                if (empty($profile_details['shop_section_id'])) {
                    unset($listingArray['shop_section_id']);
                }

                if (empty($profile_details['is_customizable'])) {
                    unset($listingArray['is_customizable']);
                }

                //In case occasion is not provided
                if (empty($profile_details['occassion'])) {
                    unset($listingArray['occasion']);
                }

                $renew_flag = false;
                $update_flag = false;
                if ($product['listing_status'] == 'Relisting' && !empty($product['listing_id'])) {
                    $renew_flag = true;
                }

                if ($product['listing_status'] == 'Updated' && !empty($product['listing_id'])) {
                    $update_flag = true;
                }

                //Check if product has variations then unset "Price" option as prices are set on variations
                if ($update_flag || $renew_flag) {
                    if ($product_details->hasAttributes()) {
                        unset($listingArray['price']);
                    }
                }

                if ($update_flag || $renew_flag) {
                    $listingArray['listing_id'] = $product['listing_id'];
                    $listingArray['update_flag'] = 1;
                }

                if ($renew_flag) {
                    $listingArray['renew'] = 1;
                    $listingArray['state'] = 'active';
                    $listingArray['renew_flag'] = 1;
                }
            }
            //}
        }
        //print_r($listingArray);die;
        return $listingArray;
    }

    /** To get the etsy category based on profile store category */
    public static function getEtsyCategorybyProfileANDCategory($profileCategory, $default_category, $etsy_product_type)
    {
        $etsy_category = 0;
        if (!Tools::isEmpty($profileCategory) && is_array($profileCategory)) {
            foreach ($profileCategory as $category) {
                /* If store category selected in the profile */
                if ($etsy_product_type == 0) {
                    if (!empty($category['prestashop_category'])) {
                        $prestashop_category = explode(',', $category['prestashop_category']);
                        if (is_array($prestashop_category)) {
                            if (in_array($default_category, $prestashop_category)) {
                                $etsy_category = $category['etsy_category_code'];
                            }
                        } else {
                            if ($default_category == $prestashop_category) {
                                $etsy_category = $category['etsy_category_code'];
                            }
                        }
                    } else {
                        $etsy_category = $category['etsy_category_code'];
                    }
                } else {
                    $etsy_category = $category['etsy_category_code'];
                }
            }
        }
        return $etsy_category;
    }

    //To send request on Etsy to Create Products Listings
    public static function etsyCreateListings($langauge_id, $listingArray = array())
    {
        $listingsCreated = 0;
        $listingsUpdated = 0;
        $listingsRenewed = 0;
        $method_name = 'EtsyModule::etsyCreateListings()';
        self::auditLogEntry('Job execution started to sync item on etsy.', $method_name);
        if (!empty($listingArray) && count($listingArray) > 0) {
            foreach ($listingArray as $listing) {
                if (isset($listing['id_product'])) {
                    /* In case of renew & update product */
                    $item_inventory = KbMarketplaceIntegration::getProductInventory($listing['id_product']);
                    
                    /*
                     * changes by rishabh jain
                     */
                    $quantity = $item_inventory;
                    if ($quantity <= 0) {
                        $pro_obj = new Product($listing['id_product']);
                           $stock = $pro_obj->out_of_stock;
                        if ((int)$stock == 1) {
                            $quantity = 999;
                        } else if ((int)$stock == 2) {
                            $out_of_stock = Configuration::get('PS_ORDER_OUT_OF_STOCK');
                            if ($out_of_stock == 1) {
                                $quantity = 999;
                            }
                        }
                    }
                    $item_inventory = $quantity;
                    /*
                     * changes over
                     */
                    if (!empty($listing['listing_id'])) {
                        $etsyRequestURI = '/listings/' . $listing['listing_id'] . '/';
                        $etsyRequestMethod = 'PUT';
                        $etsyQueryString = $listing;
                        unset($etsyQueryString['property']);
                        unset($etsyQueryString['id_product']);
                        //unset($etsyQueryString['quantity']);
                        unset($etsyQueryString['price']);
                        unset($etsyQueryString['listing_status']);

                        /** Update current status of item by requesting product info from etsy. */
                        $request_url = '/listings/' . $listing['listing_id'] . '/';
                        $listing_status_data = Tools::jsonDecode(self::etsyGetOAuthResponse($request_url, "GET", array()));
                        //item
                        //print_r($listing_status_data);die;
                        /** In case of sold out, Inventory needs to passed so unsettting Inventory in else condition (If item inventory is zero on Etsy) Otherwise Etsy will return the following error i.e. quantity_cannot_be_empty_,_Invalid_edit_attempted_] */
                        if ($listing_status_data->results[0]->state == 'sold_out') {
                            if ($listing['quantity'] > 0) {
                                $etsyQueryString['renew'] = 1;
                                DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_status = 'Updated', expiry_date = '" . date("Y-m-d H:i:s", $listing_status_data->results[0]->ending_tsz) . "', sold_flag = '1' WHERE id_product = '" . (int) $listing['id_product'] . "'");
                            } else {
                                DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_status = 'Inactive', expiry_date = '" . date("Y-m-d H:i:s", $listing_status_data->results[0]->ending_tsz) . "', sold_flag = '1' WHERE id_product = '" . (int) $listing['id_product'] . "'");
                                continue;
                            }
                        } else {
                            if ($listing_status_data->results[0]->state == 'inactive' || $listing_status_data->results[0]->state == 'edit') {
                                /* In case renew_flag is not set in the $listing & Item is expired OR Inactive (As per above product status request of etsy), then no need to update the product on the server because without renew flag of the expired/inactive item, relist. In that case, Reset the Update flag in the DB  */
                                //if(empty($listing['renew_flag'])) {
                                //    DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_status = 'Inactive', expiry_date = '".date("Y-m-d H:i:s", $listing_status_data->results[0]->ending_tsz)."', renew_flag = '0', is_error = '0', listing_error = '' WHERE id_product = '" . (int) $listing['id_product'] . "'");
                                //    continue;
                                //}
                            } else if ($listing_status_data->results[0]->state == 'expired') {
                                if (empty($listing['renew_flag'])) {
                                    DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_status = 'Expired', expiry_date = '" . date("Y-m-d H:i:s", $listing_status_data->results[0]->ending_tsz) . "', renew_flag = '0', is_error = '0', listing_error = ''  WHERE id_product = '" . (int) $listing['id_product'] . "'");
                                    continue;
                                } else {
                                    /* In case of relist as well, It item inventory is zero, then don't do anything */
                                    if ($item_inventory == 0) {
                                        continue;
                                    }
                                }
                            }
                            unset($etsyQueryString['quantity']);
                        }

                        /* In cae of edit, If item is expired, Set the renew flag else remove the renew flag */
                        if (date("Y-m-d H:i:s", $listing_status_data->results[0]->ending_tsz) > date("Y-m-d H:i:s") && $listing_status_data->results[0]->state != 'sold_out') {
                            unset($etsyQueryString['renew']);
                        } else {
                            $etsyQueryString['renew'] = 1;
                        }
                        
                        /* Parameter to set status as Sold Out in DB in case item is SOLD OUT */
                        $sold_out = false;
                        if ($item_inventory == 0 && !empty($listing['listing_id'])) {
                            $sold_out = true;
                            /* In case of Sold Out, Set the Status as Inactive on Etsy */
                            $etsyQueryString['state'] = 'inactive';
                        }
                    } else {
                        /* In case of new item, If inventory is zero then product will not be synced */
                        if ($item_inventory == 0) {
                            continue;
                        }
                        /* Create new product on etsy */
                        $etsyRequestURI = '/listings/';
                        $etsyRequestMethod = 'POST';
                        $etsyQueryString = $listing;
                        unset($etsyQueryString['id_product']);
                        unset($etsyQueryString['id_profile']);
                    }

                    $response = Tools::jsonDecode(self::etsyGetOAuthResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString));
                    if (!empty($response) && isset($response->results)) {
                        $listing_id = $response->results[0]->listing_id;
                        if (!empty($listing_id)) {
                            /* If listing id was not set in the Original Array, then product was created else product was  updated OR Renewed */
                            if (empty($listing['listing_id'])) {
                                $listingsCreated++;
                                DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_id = '" . (int) $listing_id . "', listing_status = 'Listed', expiry_date = '" . date("Y-m-d H:i:s", $response->results[0]->ending_tsz) . "', date_listed = NOW(), listing_error = '' WHERE id_product = '" . (int) $listing['id_product'] . "' AND id_product_attribute = '0'");
                            } else {
                                /* In case of update/renew, Get listing details & set the price/quantity of the item. In case of variation/normal product, price & quantity will be set.
                                 * Variation will be removed & after variation sync, Variation will be listed again.
                                 */
                                $etsyRequestURI = '/listings/' . $listing['listing_id'] . '/inventory';
                                $etsyRequestMethod = 'GET';
                                $etsyQueryString = array(
                                    'listing_id' => $listing['listing_id']
                                );

                                $listing_response = Tools::jsonDecode(self::etsyGetOAuthResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString));

                                if (!empty($listing_response->results)) {
                                    $listing_price = isset($listing['price']) ? $listing['price'] : 0;
                                    $listing_quantity = isset($listing['quantity']) ? $listing['quantity'] : 0;
                                    $listing_response->results->products[0]->offerings[0]->price = $listing_price;
                                    $listing_response->results->products[0]->offerings[0]->quantity = $listing_quantity;

                                    $etsyQueryString = array(
                                        'listing_id' => $listing['listing_id'],
                                        'products' => Tools::jsonEncode($listing_response->results->products)
                                    );
                                    $etsyRequestMethod = 'PUT';
                                    Tools::jsonDecode(self::etsyGetOAuthResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString));
                                }

                                if ($sold_out == true) {
                                    DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_id = '" . (int) $listing_id . "', expiry_date = '" . date("Y-m-d H:i:s", $response->results[0]->ending_tsz) . "', listing_status = 'Inactive', sold_flag = '1', renew_flag = '0', date_last_renewed = NOW(), listing_error = '' WHERE id_product = " . (int) $listing['id_product']);
                                } else {
                                    $listing_status = 'Listed';
                                    if ($response == 'expired') {
                                        $listing_status = 'Expired';
                                    } else if ($response == 'edit') {
                                        $listing_status = 'Inactive';
                                    }
                                    if (!empty($listing['renew_flag'])) {
                                        $listingsRenewed++;
                                        DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_id = '" . (int) $listing_id . "', expiry_date = '" . date("Y-m-d H:i:s", $response->results[0]->ending_tsz) . "', listing_status = '" . $listing_status . "', renew_flag = '0', sold_flag = '0', date_last_renewed = NOW(), listing_error = '' WHERE id_product = " . (int) $listing['id_product']);
                                    } else {
                                        $listingsUpdated++;
                                        DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_status = '" . $listing_status . "', expiry_date = '" . date("Y-m-d H:i:s", $response->results[0]->ending_tsz) . "', listing_error = '' WHERE id_product = '" . (int) $listing['id_product'] . "'");
                                    }
                                }
                            }

                            /* State is set in case of inactive only. In Item is not being set to Inactive, Then sync product other data as well, In case of Inactive, No need to sync other Info */
                            if (empty($etsyQueryString['state'])) {
                                /* Update the Etsy Category Attributes */
                                self::syncEtsyAttribute($listing_id);

                                self::updateListingVariation($listing['id_product'], $listing_id, $langauge_id, $listing['id_profile']);
                                self::etsySyncTranslation($listing['id_product'], $listing_id, $listing['id_profile']);
                                self::etsyImageListings($listing['id_product'], $listing_id, $langauge_id);
                                self::etsySyncDownloadFile($listing['id_product'], $listing_id);
                            }
                        }
                    } else {
                        $listingError = str_replace("_", " ", key((array) $response));
                        DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET is_error = 1, listing_error = '" . pSQL($listingError) . "' WHERE id_product = '" . (int) $listing['id_product'] . "' AND id_product_attribute = '0'");
                    }
                    sleep(1);
                }
            }
        }
        self::auditLogEntry('Job execution completed to list/update items on Etsy. <br>Total Listings Created: ' . $listingsCreated, $method_name);
        return true;
    }

    /** Function to sync selected etsy attribute on the Etsy */
    public static function syncEtsyAttribute($listing_id)
    {
        $etsyAttributes = DB::getInstance()->executeS("SELECT eam.* FROM `" . _DB_PREFIX_ . "etsy_products_list` pl INNER JOIN `" . _DB_PREFIX_ . "etsy_attribute_mapping` eam ON pl.id_etsy_profiles = eam.id_etsy_profiles WHERE listing_id = '" . pSQL($listing_id) . "' AND id_product_attribute = '0'");
        if (!empty($etsyAttributes)) {
            foreach ($etsyAttributes as $etsyAttribute) {
                if ($etsyAttribute['id_attribute_group'] != "") {
                    $etsyRequestURI = '/listings/' . $listing_id . '/attributes/' . $etsyAttribute['property_id'];
                    $etsyRequestMethod = 'PUT';
                    $etsyQueryString = array("value_ids" => $etsyAttribute['id_attribute_group']);
                    Tools::jsonDecode(self::etsyGetOAuthResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString));
                }
            }
        }
        return true;
    }

    public static function updateListingVariation($product_id, $listing_id, $language_id, $profile_id)
    {
        $method_name = 'EtsyModule:updateListingVariation';
        $listingArray = array();
        $product = new Product($product_id, false, $language_id);
        if (!empty($product) && $product->hasAttributes()) {
            self::auditLogEntry('Job execution started to list the variation on Etsy', $method_name);

            $attributes = $product->getAttributeCombinations($language_id);
            if (!empty($attributes)) {
                foreach ($attributes as $attribute) {
                    $propertyDetail = DB::getInstance()->getRow("SELECT * FROM " . _DB_PREFIX_ . "etsy_attribute_mapping1 am1 INNER JOIN " . _DB_PREFIX_ . "etsy_attributes ea ON ea.attribute_id = am1.property_id WHERE am1.id_attribute_group = '" . (int) $attribute['id_attribute_group'] . "'");
                    if (!empty($propertyDetail)) {
                        //Get Attribute Name
                        $attribute_details = new Attribute($attribute['id_attribute'], $language_id);
                        $attributeAvailability = KbMarketplaceIntegration::getInventoryByProductAttributeId($attribute['id_product'], $attribute['id_product_attribute']);
                        $productPricewithAttribute = Product::getPriceStatic($product->id, true, $attribute['id_product_attribute'], 6, null, false, true);

                        $profileDetails = DB::getInstance()->getRow("SELECT * FROM " . _DB_PREFIX_ . "etsy_profiles WHERE id_etsy_profiles = '" . (int) $profile_id . "'");

                        $listingArray[] = array(
                            'listing_id' => $listing_id,
                            'property_id' => $propertyDetail['etsy_property_id'],
                            'value' => $attribute_details->name,
                            'name' => $propertyDetail['etsy_property_title'],
                            'is_available' => (isset($attributeAvailability['success'][0]['quantity']) && $attributeAvailability['success'][0]['quantity'] > 0) ? 1 : 0,
                            'price' => $productPricewithAttribute,
                            'id_product' => $product->id,
                            'id_product_attribute' => $attribute['id_product_attribute'],
                            'etsy_currency' => $profileDetails['etsy_currency'],
                            'property' => $profileDetails['property'],
                            'enable_max_qty' => $profileDetails['enable_max_qty'],
                            'enable_min_qty' => $profileDetails['enable_min_qty'],
                            'min_qty' => $profileDetails['min_qty'],
                            'max_qty' => $profileDetails['max_qty'],
                            'custom_pricing' => $profileDetails['custom_pricing'],
                            'custom_price' => $profileDetails['custom_price'],
                            'price_type' => $profileDetails['price_type'],
                            'price_reduction' => $profileDetails['price_reduction'],
                        );
                    }
                }

                $attribute_data = array();
                foreach ($listingArray as $attribute) {
                    if (isset($attribute_data[$attribute['listing_id']][$attribute['property_id']]['value'])) {
                        if (!in_array($attribute['value'], $attribute_data[$attribute['listing_id']][$attribute['property_id']]['value'])) {
                            $attribute_data[$attribute['listing_id']][$attribute['property_id']]['value'][] = $attribute['value'];
                        }
                    } else {
                        $attribute_data[$attribute['listing_id']][$attribute['property_id']]['value'][] = $attribute['value'];
                    }

                    if (isset($attribute_data[$attribute['listing_id']][$attribute['property_id']]['name'])) {
                        if (!in_array($attribute['name'], $attribute_data[$attribute['listing_id']][$attribute['property_id']]['name'])) {
                            $attribute_data[$attribute['listing_id']][$attribute['property_id']]['name'][] = $attribute['name'];
                        }
                    } else {
                        $attribute_data[$attribute['listing_id']][$attribute['property_id']]['name'][] = $attribute['name'];
                    }

                    if (!isset($attribute_data[$attribute['listing_id']]['id_product'])) {
                        $attribute_data[$attribute['listing_id']]['id_product'] = $attribute['id_product'];
                    }

                    if (!isset($attribute_data[$attribute['listing_id']]['etsy_currency'])) {
                        $attribute_data[$attribute['listing_id']]['etsy_currency'] = $attribute['etsy_currency'];
                    }

                    if (!isset($attribute_data[$attribute['listing_id']]['custom_pricing'])) {
                        $attribute_data[$attribute['listing_id']]['custom_pricing'] = $attribute['custom_pricing'];
                    }

                    if (!isset($attribute_data[$attribute['listing_id']]['custom_price'])) {
                        $attribute_data[$attribute['listing_id']]['custom_price'] = $attribute['custom_price'];
                    }

                    if (!isset($attribute_data[$attribute['listing_id']]['price_type'])) {
                        $attribute_data[$attribute['listing_id']]['price_type'] = $attribute['price_type'];
                    }

                    if (!isset($attribute_data[$attribute['listing_id']]['price_reduction'])) {
                        $attribute_data[$attribute['listing_id']]['price_reduction'] = $attribute['price_reduction'];
                    }

                    if (isset($attribute_data[$attribute['listing_id']]['id_product_attribute'])) {
                        if (!in_array($attribute['id_product_attribute'], $attribute_data[$attribute['listing_id']]['id_product_attribute'])) {
                            $attribute_data[$attribute['listing_id']]['id_product_attribute'][] = $attribute['id_product_attribute'];
                        }
                    } else {
                        $attribute_data[$attribute['listing_id']]['id_product_attribute'][] = $attribute['id_product_attribute'];
                    }
                }


                foreach ($attribute_data as $attrs) {
                    $product_id = $attrs['id_product'];
                    $product_attributes = $attrs['id_product_attribute'];
                    $etsy_currency = $attrs['etsy_currency'];

                    $customize_price = $attrs['custom_pricing'];
                    $customize_price_value = $attrs['custom_price'];
                    $customize_price_type = $attrs['price_type'];
                    $customize_price_reduction = $attrs['price_reduction'];

                    unset($attrs['id_product']);
                    unset($attrs['id_product_attribute']);
                    unset($attrs['etsy_currency']);
                    unset($attrs['custom_pricing']);
                    unset($attrs['custom_price']);
                    unset($attrs['price_type']);
                    unset($attrs['price_reduction']);

                    /**
                      $properties Array with details like Size, Color & Property code
                      $variation_propery list of property code like 100, 200 etc associated with the product
                     */
                    $properties = array();
                    $variation_propery = array();
                    foreach ($attrs as $prop_id => $attr) {
                        $variation_propery[] = $prop_id;
                        $count = 0;
                        foreach ($attr['value'] as $value) {
                            $properties[$prop_id][] = array(
                                'property_id' => $prop_id,
                                'property_name' => $attr['name'][0],
                                'values' => array($value),
                            );
                            $count++;
                        }
                    }

                    $combination_array = array();
                    $combination_count = 0;
                    foreach ($properties as $property) {
                        $combination_array[$combination_count] = $property;
                        $combination_count++;
                    }


                    /** Possible combinations generator */
                    if ($combination_count == 1) {
                        $possible_combinations = array();
                    } else {
                        $tempArray = array();
                        foreach ($combination_array as $combination) {
                            $tempArray[] = $combination;
                        }
                        $possible_combinations = self::combinations($tempArray);
                    }

                    $products = array();
                    $generated_key = 0;

                    /* If item is having only one combination. Either Size Small OR Size Medium */
                    if ($combination_count == 1) {
                        $tempArray = array();
                        foreach ($combination_array[0] as $iteration => $combination) {
                            $sku = '';
                            $tempArray = array();
                            $tempArray[] = $combination_array[0][$iteration];
                            $property_value = array();
                            foreach ($tempArray as $temp) {
                                $property_value[] = $temp['values'][0]; //It will have value like Small OR Red
                            }
                            $product_attr_id = '';
                            foreach ($product_attributes as $product_attribute_id) {
                                $attributesList = $product->getAttributeCombinationsById($product_attribute_id, $language_id);
                                $wrongipa = true;
                                if (count($property_value) == count($attributesList)) {
                                    foreach ($attributesList as $key => $pro_attributes) {
                                        if (in_array($pro_attributes['attribute_name'], $property_value)) {
                                            $wrongipa = false;
                                        }
                                    }
                                }
                                if (!$wrongipa) {
                                    $product_attr_id = $product_attribute_id;
                                }
                            }
                            if ($product_attr_id == '') {
                                $product_attr_id = self::getVariationIdByPropertyValue($combination, $product->id);
                            }

                            if ($product_attr_id != '') {
                                $attributes = $product->getAttributeCombinationsById($product_attr_id, $language_id);
                                $sku = $attributes[0]['reference'];
                                $productInventory = KbMarketplaceIntegration::getProductInventory($product_id, $product_attr_id);
                                $price = Product::getPriceStatic($product_id, true, $product_attr_id, 6, null, false, true);
                                $etsy_currency_id = Currency::getIdByIsoCode($etsy_currency, Context::getContext()->shop->id);
                                $price = Tools::convertPrice($price, $etsy_currency_id);
                            } else {
                                /* In case, combination doesn't exist in the DB. Set Quantity as 0 */
                                $productInventory = 0;
                                $price = Product::getPriceStatic($product_id, true, null, 6, null, false, true);
                                $etsy_currency_id = Currency::getIdByIsoCode($etsy_currency, Context::getContext()->shop->id);
                                $price = Tools::convertPrice($price, $etsy_currency_id);
                            }

                            if ($productInventory > 999) {
                                $quantity = 999;
                            } else {
                                $quantity = $productInventory;
                                if ($quantity <= 0) {
                                    $pro_obj = new Product($product_id);
                                       $stock = $pro_obj->out_of_stock;
                                    if ((int)$stock == 1) {
                                        $quantity = 999;
                                    } else if ((int)$stock == 2) {
                                        $out_of_stock = Configuration::get('PS_ORDER_OUT_OF_STOCK');
                                        if ($out_of_stock == 1) {
                                            $quantity = 999;
                                        }
                                    }
                                }
                            }

                            $price_change = '';
                            if ($customize_price) {
                                if ($customize_price_type == 'Percentage') {
                                    $price_change = ($customize_price_value / 100) * $price;
                                } else {
                                    $price_change = $customize_price_value;
                                }
                                if ($customize_price_reduction == 'increase') {
                                    $price = $price + $price_change;
                                } else {
                                    $price = $price - $price_change;
                                }
                            }
                            if (!in_array($product_attr_id, $product_attributes)) {
                                $quantity = 0;
                            }
                            $products[$generated_key]['property_values'] = $tempArray;
//                            $products[$generated_key]['sku'] = "SKU_" . $product_id . "_" . $product_attr_id;
                            if ($sku == '') {
                                $products[$generated_key]['sku'] = "SKU_" . $product_id . "_" . $product_attr_id;
                            } else {
                                $products[$generated_key]['sku'] = $sku;
                            }
                            $products[$generated_key]['offerings'] = array(array(
                                'price' => $price,
                                'quantity' => $quantity,
                                'is_enabled' => 1
                            ));
                            $generated_key++;
                        }
                    } else {
                        $k = 0;
                        foreach ($possible_combinations as $combination) {
                            $sku = '';
                            $property_value = array();
                            foreach ($combination as $temp) {
                                $property_value[] = $temp['values'][0];
                            }

                            $product_attr_id = '';
                            /* Loop through each attributes of the product & find the attribute id which is matching the all values (i.e. Size Small and Color Red) of the attribute with the combination value */
                            foreach ($product_attributes as $product_attribute_id) {
                                $attributesList = $product->getAttributeCombinationsById($product_attribute_id, $language_id);
                                $checking_matching_count = 0;
                                if (count($property_value) == count($attributesList)) {
                                    foreach ($attributesList as $key => $pro_attributes) {
                                        if (in_array($pro_attributes['attribute_name'], $property_value)) {
                                            $checking_matching_count++;

                                            /* If All values of the attribute is matced in the $property_value array then pick that attribute */
                                            if ($checking_matching_count == count($property_value)) {
                                                $product_attr_id = $product_attribute_id;
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                            if ($product_attr_id == '') {
                                $product_attr_id = self::getVariationIdByPropertyValue($combination, $product->id);
                            }
                            if ($product_attr_id != '') {
                                $attributes = $product->getAttributeCombinationsById($product_attr_id, $language_id);
                                $sku = $attributes[0]['reference'];
                                $productInventory = KbMarketplaceIntegration::getProductInventory($product_id, $product_attr_id);
                                $price = Product::getPriceStatic($product_id, true, $product_attr_id, 6, null, false, true);
                                $etsy_currency_id = Currency::getIdByIsoCode($etsy_currency, Context::getContext()->shop->id);
                                $price = Tools::convertPrice($price, $etsy_currency_id);
                            } else {
                                /* In case, combination doesn't exist in the DB. Set Quantity as 0 */
                                $productInventory = 0;
                                $price = Product::getPriceStatic($product_id, true, null, 6, null, false, true);
                                $etsy_currency_id = Currency::getIdByIsoCode($etsy_currency, Context::getContext()->shop->id);
                                $price = Tools::convertPrice($price, $etsy_currency_id);
                            }

                            if ($productInventory > 999) {
                                $quantity = 999;
                            } else {
                                $quantity = $productInventory;
                                if ($quantity <= 0) {
                                    $pro_obj = new Product($product_id);
                                    $stock = $pro_obj->out_of_stock;
                                    if ((int)$stock == 1) {
                                        $quantity = 999;
                                    } else if ((int)$stock == 2) {
                                        $out_of_stock = Configuration::get('PS_ORDER_OUT_OF_STOCK');
                                        if ($out_of_stock == 1) {
                                            $quantity = 999;
                                        }
                                    }
                                }
                            }
                            if (!in_array($product_attr_id, $product_attributes)) {
                                $quantity = 0;
                            }
                            $products[$generated_key]['property_values'] = $combination;
                            if ($sku == '') {
                                $products[$generated_key]['sku'] = "SKU_" . $product_id . "_" . $product_attr_id;
                            } else {
                                $products[$generated_key]['sku'] = $sku;
                            }
//                            $products[$generated_key]['sku'] = "SKU_" . $product_id . "_" . $product_attr_id;

                            $price_change = '';
                            if ($customize_price) {
                                if ($customize_price_type == 'Percentage') {
                                    $price_change = ($customize_price_value / 100) * $price;
                                } else {
                                    $price_change = $customize_price_value;
                                }
                                if ($customize_price_reduction == 'increase') {
                                    $price = $price + $price_change;
                                } else {
                                    $price = $price - $price_change;
                                }
                            }
                            $products[$generated_key]['offerings'] = array(array(
                                    'price' => $price,
                                    'quantity' => $quantity,
                                    'is_enabled' => 1
                            ));
                            $k++;
                            $generated_key++;
                        }
                    }
                    $etsyQueryString = array(
                        'products' => Tools::jsonEncode($products),
                        'price_on_property' => implode(',', $variation_propery),
                        'quantity_on_property' => implode(',', $variation_propery),
                        'sku_on_property' => implode(',', $variation_propery),
                    );

                    $etsyRequestURI = '/listings/' . $listing_id . '/inventory';
                    $etsyRequestMethod = 'PUT';

                    $response = Tools::jsonDecode(self::etsyGetOAuthResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString));

                    if (!empty($response) && isset($response->results)) {
                        /* Nothing needs to be done if variation updated successfully */
                    } else {
                        $listingError = str_replace("_", " ", key((array) $response));
                        DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET is_error = 1, listing_error = '" . pSQL($listingError) . "' WHERE id_product = '" . (int) $product_id . "'");
                    }
                    sleep(1); //Sleep job to avoid exceed limit rate
                }
                self::auditLogEntry('Job execution completed to update the variation on Etsy', $method_name);
            }
        }
        return true;
    }

    /** Generate all combinations of the array */
    private static function combinations($arrays, $i = 0)
    {
        if (!isset($arrays[$i])) {
            return array();
        }
        if ($i == count($arrays) - 1) {
            return $arrays[$i];
        }

        //Get combinations from subsequent arrays
        $tmp = self::combinations($arrays, $i + 1);

        //Concat each array from tmp with each element from $arrays[$i]
        $result = array();
        foreach ($arrays[$i] as $v) {
            foreach ($tmp as $t) {
                $result[] = is_array($t) ? array($v, $t) : array($v, $t);
            }
        }
        return $result;
    }

    //To upload images on etsy marketplace
    public static function etsyImageListings($product_id, $listing_id, $language_id)
    {
        $method_name = 'EtsyModule::etsyImageListings()';
        self::auditLogEntry('Job execution started to list images on Etsy Marketplace.', $method_name);

        $imagesListed = 0;
        $images = self::prepareArrayToUploadImageOnEtsy($product_id, $listing_id, $language_id);
        
        if (!empty($images) && count($images) > 0) {
            if (isset($listing_id)) {
                /* Delete those images listed from the OLD version of the module so avoid duplicate image on etsy. One in listting_image_id column & another one in etsy_image table */
                $existing_images = Db::getInstance()->getValue("SELECT listing_image_id FROM " . _DB_PREFIX_ . "etsy_products_list WHERE listing_id = '" . (int) $listing_id . "'");
                if (!empty($existing_images)) {
                    $existing_images_array = explode(",", $existing_images);
                    foreach ($existing_images_array as $existing_image) {
                        $etsyRequestURI = '/listings/' . $listing_id . '/images/'.$existing_image;
                        $etsyRequestMethod = 'DELETE';
                        Tools::jsonDecode(self::etsyGetOAuthResponse($etsyRequestURI, 'DELETE', array()));
                    }
                    Db::getInstance()->getValue("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_image_id = NULL WHERE listing_id = '" . (int) $listing_id . "'");
                }
                /* END Delete those images listed from the OLD version of the module */
                
                
                $etsyRequestURI = '/listings/' . $listing_id . '/images/';
                $etsyRequestMethod = 'POST';
                $i = 1;
                foreach ($images as $image) {
                    $etsyQueryString = array();
                    $etsyQueryString['listing_id'] = $listing_id;
                    $etsyQueryString['image'] = $image["image"];
                    $etsyQueryString['rank'] = $i;
                    if (!empty($image['overwrite'])) {
                        $etsyQueryString['overwrite'] = 1;
                    }

                    if (!empty($image['listing_image_id'])) {
                        $etsyQueryString['listing_image_id'] = $image['listing_image_id'];
                    }
                    $image_list_response = Tools::jsonDecode(self::etsyGetOAuthResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString, true));
                    

                    if (!empty($image_list_response) && isset($image_list_response->results)) {
                        $sql = "UPDATE " . _DB_PREFIX_ . "etsy_images SET etsy_image_id = '" . pSQL($image_list_response->results[0]->listing_image_id) . "' WHERE image_id = '" . (int) $image['product_etsy_image_id'] . "'";
                        $imagesListed++;
                        DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_images SET etsy_image_id = '" . pSQL($image_list_response->results[0]->listing_image_id) . "' WHERE image_id = '" . (int) $image['product_etsy_image_id'] . "'");
                    } else {
                        $listingError = str_replace("_", " ", key((array) $image_list_response));
                        DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET is_error = '1', listing_error = '" . pSQL($listingError) . "' WHERE listing_id = '" . (int) $listing_id . "'");
                    }
                    sleep(1); //Sleep job to avoid exceed limit rate
                    $i++;
                }
            }
        }
        
        self::deleteAlreadyDeletedImages($product_id, $listing_id, $language_id);
        
        self::auditLogEntry('Job execution completed to list images on etsy marketplace.<br>Total Images Listed: ' . $imagesListed, $method_name);
        return true;
    }
    
    public static function deleteAlreadyDeletedImages($product_id, $listing_id, $language_id)
    {
        /* Delete those images listed which has been delete from prestashop */
        $sql = "SELECT etsy_image_id,image_id,ps_image_id FROM " . _DB_PREFIX_ . "etsy_images ei LEFT JOIN " . _DB_PREFIX_ . "image i ON (ei.ps_image_id = i.id_image and ei.product_id = i.id_product) WHERE ei.product_id = '" . (int) $product_id . "' and i.id_image IS NULL";
        $deleted_images = Db::getInstance()->executeS($sql);
        if (!empty($deleted_images)) {
            foreach ($deleted_images as $delete_image) {
                $is_deletable = true;
                if ($delete_image['ps_image_id'] == 999999) {
                    $id_profile = Db::getInstance()->getValue("SELECT id_etsy_profiles FROM " . _DB_PREFIX_ . "etsy_products_list WHERE id_product = '" . (int) $product_id . "'");
                    if ((int)$id_profile != 0) {
                        $is_size_chart_image_enable = (bool) Db::getInstance()->getValue("SELECT size_chart_image FROM " . _DB_PREFIX_ . "etsy_profiles WHERE id_etsy_profiles = '" . (int) $id_profile . "'");
                        if ($is_size_chart_image_enable) {
                            $is_deletable = false;
                        }
                    }
                }
                if ($is_deletable) {
                    $etsyRequestURI = '/listings/' . $listing_id . '/images/'.$delete_image['etsy_image_id'];
                    $etsyRequestMethod = 'DELETE';
                    Tools::jsonDecode(self::etsyGetOAuthResponse($etsyRequestURI, 'DELETE', array()));
                    Db::getInstance()->execute("Delete FROM " . _DB_PREFIX_ . "etsy_images WHERE image_id = '" . (int) $delete_image['image_id'] . "'");
                }
            }
        }
        /* End Delete those images listed which has been delete from prestashop */
    }

    public static function prepareArrayToUploadImageOnEtsy($product_id, $listing_id, $language_id)
    {
        $size_chart_image_id = 999999;
        $listing_images = array();
        $protocol_link = (Configuration::get('PS_SSL_ENABLED') || Tools::usingSecureMode()) ? 'https://' : 'http://';
        $useSSL = ((Configuration::get('PS_SSL_ENABLED')) || Tools::usingSecureMode()) ? true : false;
        $protocol_content = ($useSSL) ? 'https://' : 'http://';
        $link = new Link($protocol_link, $protocol_content);

        /** Fetch already uploaded images from the table */
        $existing_images = Db::getInstance()->executeS("SELECT * FROM " . _DB_PREFIX_ . "etsy_images WHERE product_id = '" . (int) $product_id . "'");
        if (!empty($listing_id)) {
            $images = array();
            $image_arrays = Image::getImages($language_id, $product_id);
            $length_img_array = count($image_arrays);
            /*
             * changing by rishabh jain for adding size chart image
             */
            $id_profile = Db::getInstance()->getValue("SELECT id_etsy_profiles FROM " . _DB_PREFIX_ . "etsy_products_list WHERE id_product = '" . (int) $product_id . "'");
            if ((int)$id_profile != 0) {
                $exist_file = _PS_MODULE_DIR_. 'kbetsy/views/img/profile/'.$id_profile. '.*';
                $is_size_chart_image_enable = (bool) Db::getInstance()->getValue("SELECT size_chart_image FROM " . _DB_PREFIX_ . "etsy_profiles WHERE id_etsy_profiles = '" . (int) $id_profile . "'");
                $match1 = glob($exist_file);
                if ($is_size_chart_image_enable && count($match1) > 0) {
                    $ban = explode('/', $match1[0]);
                    $ban = end($ban);
                    $ban = trim($ban);
                    $img_url = self::getModuleDirUrl() . 'kbetsy/views/img/profile/' . $ban;
                    if (file_exists($match1[0])) {
                        $size_chart_file_path = _PS_MODULE_DIR_. 'kbetsy/views/img/profile/'.$ban;
                        if ($length_img_array >= 10) {
                            $image_arrays[9] = array(
                                'id_image' => $size_chart_image_id,
                                'id_product' => $product_id,
                                'path' => $size_chart_file_path,
                                'position' => 1,
                                'cover' => 1,
                                'id_lang' => 1,
                                'legend' => ''
                            );
                        } else {
                            $image_arrays[$length_img_array] = array(
                                'id_image' => $size_chart_image_id,
                                'id_product' => $product_id,
                                'path' => $size_chart_file_path,
                                'position' => 1,
                                'cover' => 1,
                                'id_lang' => 1,
                                'legend' => ''
                            );
                        }
                    }
                }
            }
            /*
             * changes over
             */
            $image_count = 0;
            foreach ($image_arrays as $image_array) {
                if ($image_count >= 10) {
                    continue;
                }

                $product_data = new Product($product_id, false, $language_id);

                $images['listing_id'] = $listing_id;
                if (is_array($product_data->link_rewrite)) {
                    //$images['image'] = $link->getImageLink($product_data->link_rewrite[$language_id], $image_array['id_image'], ImageType::getFormatedName('large'));
                } else {
                    //$images['image'] = $link->getImageLink($product_data->link_rewrite, $image_array['id_image'], ImageType::getFormatedName('large'));
                }
                if ($image_array['id_image'] == $size_chart_image_id) {
                    $image_dir_path = $image_array['path'];
                } else {
                    $image_object = new Image($image_array['id_image'], 1);
                    $image_dir_path = _PS_PROD_IMG_DIR_ . $image_object->getExistingImgPath() . '-' .ImageType::getFormatedName('large') .'.'. $image_object->image_format;

                    /* If large thumbnail is not exist then use home default image */
                    if (!file_exists($image_dir_path)) {
                        $image_dir_path = _PS_PROD_IMG_DIR_ . $image_object->getExistingImgPath() . '-' .ImageType::getFormatedName('home').'.'. $image_object->image_format;
                        if (is_array($product_data->link_rewrite)) {
                            //$images['image'] = $link->getImageLink($product_data->link_rewrite[$language_id], $image_array['id_image'], ImageType::getFormatedName('home'));
                        } else {
                            //$images['image'] = $link->getImageLink($product_data->link_rewrite, $image_array['id_image'], ImageType::getFormatedName('home'));
                        }
                    }
                }
                $images['image'] = $image_dir_path;


                $is_updated = false;
                $is_existing = false;
                
                $esty_image_id = 0;
                $product_etsy_image_id = 0; // Module Etsy Table Auto Increment ID
                if (!empty($existing_images)) {
                    foreach ($existing_images as $existing_image) {
                        /** If current image is already exist in the DB */
                        if ($image_array['id_image'] == $existing_image['ps_image_id'] && $existing_image['ps_image_id']  == $size_chart_image_id) {
                            $is_existing = true;
                            $esty_image_id = $existing_image['etsy_image_id'];
                            $is_updated = true;
                            $product_etsy_image_id = $existing_image['image_id'];
                        } else if ($image_array['id_image'] == $existing_image['ps_image_id']) {
//                            $is_updated = true;
                            $is_existing = true;
                            $esty_image_id = $existing_image['etsy_image_id'];
                            $product_etsy_image_id = $existing_image['image_id'];
                            /** Check if image is already uploaded on the etsy but image content has been changed so need to update the image on etsy again */
                            if (!empty($existing_image['etsy_image_id'])) {
                                if ($existing_image['path_hash'] != md5_file($image_dir_path)) {
                                    $is_updated = true;
                                }
                            }
                        }
                    }
                }

                /** If image is already exist & no changes in the conten then no need to upload that image */
                if ($is_updated == false && $is_existing == true && !empty($esty_image_id)) {
                    continue;
                }

                if ($is_existing == true) {
                    Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_images SET "
                            . "path_hash = '" . pSQL(md5_file($image_dir_path)) . "',"
                            . "path = '" . pSQL($image_dir_path) . "'"
                            . "WHERE `ps_image_id` = '" . (int) $image_array['id_image'] . "' AND "
                            . "product_id = " . (int) $product_id); /* 'ps_image_id' is column name, not DB prefix */
                    if ($is_updated == true) {
                        $images['listing_image_id'] = $esty_image_id;
                        $images['overwrite'] = 1;
                    }
                } else {
                    Db::getInstance()->execute("INSERT INTO " . _DB_PREFIX_ . "etsy_images SET "
                            . "ps_image_id = '" . (int) $image_array['id_image'] . "',"
                            . "product_id = " . (int) $product_id . ","
                            . "path_hash = '" . pSQL(md5_file($image_dir_path)) . "',"
                            . "path = '" . pSQL($image_dir_path) . "'");
                    $product_etsy_image_id = Db::getInstance()->Insert_ID();
                }
                $images['product_etsy_image_id'] = $product_etsy_image_id;
                $listing_images[] = $images;
                $image_count++;
            }
        }
        return $listing_images;
    }

    private static function getModuleDirUrl()
    {
        $module_dir = '';
        if (self::checkSecureUrl()) {
            $module_dir = _PS_BASE_URL_SSL_ . __PS_BASE_URI__ . str_replace(_PS_ROOT_DIR_ . '/', '', _PS_MODULE_DIR_);
        } else {
            $module_dir = _PS_BASE_URL_ . __PS_BASE_URI__ . str_replace(_PS_ROOT_DIR_ . '/', '', _PS_MODULE_DIR_);
        }
        return $module_dir;
    }
    
    private static function checkSecureUrl()
    {
        $custom_ssl_var = 0;

        if (isset($_SERVER['HTTPS'])) {
            if ($_SERVER['HTTPS'] == 'on') {
                $custom_ssl_var = 1;
            }
        } else if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            $custom_ssl_var = 1;
        }

        if ((bool) Configuration::get('PS_SSL_ENABLED') && $custom_ssl_var == 1) {
            return true;
        } else {
            return false;
        }
    }
    
    //To update the translation on etsy
    public static function etsySyncTranslation($product_id, $listing_id, $profile_id)
    {
        $method_name = 'EtsyModule::etsySyncTranslation()';
        self::auditLogEntry('Job execution started to sync translation on etsy', $method_name);

        $translations = self::prepareArrayToUpdateTranslationOnEtsy($product_id, $listing_id, $profile_id);
        if (!empty($translations)) {
            foreach ($translations as $translation) {
                $etsyRequestURI = '/listings/' . $listing_id . '/translations/' . $translation['language'];
                $etsyRequestMethod = 'PUT';

                $translation_response = Tools::jsonDecode(self::etsyGetOAuthResponse($etsyRequestURI, $etsyRequestMethod, $translation));
                if (!empty($translation_response) && isset($translation_response->params)) {
                } else {
                    $listingError = str_replace("_", " ", key((array) $translation_response));
                    DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET is_error = 1, listing_error = '" . pSQL($listingError) . "' WHERE listing_id = '" . (int) $listing_id . "'");
                }
                sleep(1); //Sleep job to avoid exceed limit rate
            }
        }
        self::auditLogEntry('Job execution completed to sync translation on etsy.', $method_name);
        return true;
    }

    // To prepare data to sync translation on etsy marketplace
    public static function prepareArrayToUpdateTranslationOnEtsy($product_id, $listing_id, $profile_id)
    {
        $listingArray = array();
        $languages_to_sync = Configuration::get('etsy_sync_lang');
        $etsy_desc_type = Configuration::get('etsy_desc_type');

        if (!empty($languages_to_sync)) {
            $sync_languages = explode(',', $languages_to_sync);
            $sync_language_array = array();
            if (is_array($sync_languages)) {
                foreach ($sync_languages as $sync_language) {
                    $language_data = new Language($sync_language);
                    if ($sync_language != Configuration::get('etsy_default_lang')) {
                        $sync_language_array[$language_data->id] = $language_data->iso_code;
                    }
                }
            }

            if (!empty($sync_language_array)) {
                $i = 0;
                foreach ($sync_language_array as $language_id => $language_code) {
                    $profile_details = DB::getInstance()->getRow("SELECT ef.* FROM " . _DB_PREFIX_ . "etsy_profiles ef "
                            . "WHERE id_etsy_profiles = '" . (int) $profile_id . "'", true, false);

                    $product_details = KbMarketplaceIntegration::getProductByProductId($product_id, $language_id);

                    $quantity = KbMarketplaceIntegration::getProductInventory($product_id);
                    if ($quantity > 999) {
                        $quantity = 999;
                    }

                    $price = Product::getPriceStatic($product_id, true, null, 6, null, false, true);

                    $customize_price = $profile_details['custom_pricing'];
                    $customize_price_value = $profile_details['custom_price'];
                    $customize_price_type = $profile_details['price_type'];
                    $customize_price_reduction = $profile_details['price_reduction'];

                    $price_change = '';
                    if ($customize_price) {
                        if ($customize_price_type == 'Percentage') {
                            $price_change = ($customize_price_value / 100) * $price;
                        } else {
                            $price_change = $customize_price_value;
                        }
                        if ($customize_price_reduction == 'increase') {
                            $price = $price + $price_change;
                        } else {
                            $price = $price - $price_change;
                        }
                    }
                    if ($price < 0) {
                        $price = 0;
                    }

                    $etsy_currency_id = Currency::getIdByIsoCode(Configuration::get('etsy_currency'), Context::getContext()->shop->id);
                    $strip_tags = array('</p>', '<br />', '<br>', '</div>', '</li>');
                    if ($etsy_desc_type == 'short') {
                        $description = str_replace($strip_tags, "\n", $product_details->description_short);
                    } else if ($etsy_desc_type == 'long') {
                        $description = str_replace($strip_tags, "\n", $product_details->description);
                    } else {
                        if (Tools::isEmpty($product_details->description_short)) {
                            $description = str_replace($strip_tags, "\n", $product_details->description);
                        } else {
                            $description = str_replace($strip_tags, "\n", $product_details->description_short . "\n" . $product_details->description);
                        }
                    }
                    $description = trim(strip_tags($description));

                    $short_description = strip_tags(str_replace($strip_tags, "\n", $product_details->description_short));
                    $customize_title = $profile_details['customize_product_title'];
                    if (!Tools::isEmpty($customize_title)) {
                        $customize_title = str_replace('{product_title}', $product_details->name, $customize_title);
                        $customize_title = str_replace('{id_product}', $product_details->id, $customize_title);
                        $customize_title = str_replace('{manufacturer_name}', Manufacturer::getNameById($product_details->id_manufacturer), $customize_title);
                        $customize_title = str_replace('{supplier_name}', $product_details->supplier_name, $customize_title);
                        $customize_title = str_replace('{reference}', $product_details->reference, $customize_title);
                        $customize_title = str_replace('{ean13}', $product_details->ean13, $customize_title);
                        $customize_title = str_replace('{short_description}', $short_description, $customize_title);
                        $customize_title = str_replace('{price}', Tools::convertPrice($price, $etsy_currency_id), $customize_title);
                    } else {
                        $customize_title = $product_details->name;
                    }
                    $tagArray = array();
                    $tagTempArray = array();
                    $productTags = Tag::getProductTags($product_id);
                    if (count($productTags) && isset($productTags[$language_id])) {
                        $tagArray = $productTags[$language_id];
                        if (count($tagArray) > 13) {
                            $tagArray = array_slice($tagArray, 0, 13);
                        }
                    }
                    if (count($tagArray)) {
                        foreach ($tagArray as $tag) {
                            $tag = preg_replace('/[^A-Za-z0-9 ]/', '', $tag);
                            $tagTempArray[Tools::strtolower(Tools::substr($tag, 0, 19))] = Tools::substr($tag, 0, 19);
                        }
                    }
                    $tagTempArray = array_unique($tagTempArray);

                    $listingArray[$i]['listing_id'] = $listing_id;
                    $listingArray[$i]['language'] = $language_code;
                    $listingArray[$i]['description'] = $description;
                    $listingArray[$i]['title'] = $customize_title;
                    if (!empty($tagTempArray)) {
                        $listingArray[$i]['tags'] = implode(',', $tagTempArray);
                    }
                    $i++;
                }
            }
        }
        return $listingArray;
    }

    //To upload download file on etsy
    public static function etsySyncDownloadFile($product_id, $listing_id)
    {
        $method_name = 'EtsyModule::etsySyncDownloadFile()';
        
        $download_details = DB::getInstance()->getRow("SELECT pl.* , id_product_download FROM " . _DB_PREFIX_ . "etsy_products_list pl "
                . "INNER JOIN " . _DB_PREFIX_ . "product_download pd on pl.id_product = pd.id_product "
                . "WHERE pd.active = '1' AND pl.active = 1 AND pl.id_product = " . (int) $product_id);

        if (!empty($download_details)) {
            $download_directory = _PS_DOWNLOAD_DIR_;
            $download_file = $download_directory . "/" . $download_details['filename'];

            /** If etsy file id is avaliable & uploaded file hash & current file has is same then no need to do anything */
            if (!empty($download_details['listing_file_id'])) {
                if ($download_details['listing_file_id'] == md5_file($download_file)) {
                    return true;
                }
            }

            self::auditLogEntry('Job execution started to list/update product file on etsy.', $method_name);
            $data = array();
            $data['listing_id'] = $listing_id;
            $data['file'] = $download_file;
            $data['name'] = $download_details['display_filename'];
            $data['rank'] = 1;
            if (!empty($download_details['listing_file_id'])) {
                $data['listing_file_id'] = $download_details['listing_file_id'];
            }

            $etsyRequestURI = '/listings/' . $listing_id . '/files/';
            $etsyRequestMethod = 'POST';

            $file_list_response = json_decode(self::etsyGetOAuthResponse($etsyRequestURI, $etsyRequestMethod, $data, false, true));

            if (!empty($file_list_response) && isset($file_list_response->results)) {
                if (!empty($file_list_response->results[0]->listing_file_id)) {
                    $listing_file_id = $file_list_response->results[0]->listing_file_id;
                    DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET "
                            . "listing_file_id = '" . pSQL($listing_file_id) . "', "
                            . "listing_file_hash = '" . pSQL(md5_file($download_file)) . "' "
                            . "WHERE listing_id = '" . (int) $listing_id . "'");
                }
            } else {
                $listingError = str_replace("_", " ", key((array) $file_list_response));
                DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET is_error = 1, listing_error = '" . pSQL($listingError) . "' WHERE listing_id = '" . (int) $listing_id . "'");
                self::auditLogEntry('File Upload error' . $listingError, $method_name);
            }
            sleep(1); //Sleep job to avoid exceed limit rate
        }
        self::auditLogEntry('Job execution completed to list file on etsy.', $method_name);
        return true;
    }

    //Get products from etsy products table which needs to be deleted from etsy
    public static function getProductsToDeleteOnEtsy($kbproductid = false)
    {
        $condition = '';
        if ($kbproductid) {
            $condition .= ' AND id_product = ' . (int) $kbproductid;
        }
        return DB::getInstance()->executeS("SELECT * FROM " . _DB_PREFIX_ . "etsy_products_list "
                        . "WHERE listing_id IS NOT NULL AND "
                        . "listing_id != '' AND listing_id != 0 "
                        . "AND renew_flag = '0' "
                        . "AND delete_flag = '1'" . $condition, true, false);
    }

    // To Delete the item from etsy. Unused Method. Instead of delete, Now we are making Product Inactive in the Etsy */
    public static function deleteItemsFromEtsy1($listing_id, $id_etsy_profiles = 0)
    {
        $method_name = 'EtsyModule::deleteItemsFromEtsy()';
        self::auditLogEntry('Job execution started to delete the item: ' . $listing_id . ' from the etsy.', $method_name);
        $etsyRequestURI = '/listings/' . $listing_id;
        $etsyRequestMethod = 'DELETE';
        $delete_response = json_decode(self::etsyGetOAuthResponse($etsyRequestURI, $etsyRequestMethod, array(), false, false));
        if (!empty($delete_response) && isset($delete_response->results)) {
            //DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_status = 'Pending', renew_flag = '0', delete_flag = '0', status = '0', is_error = '0', delete_track = '0', sold_flag = '0' WHERE listing_id = '" . pSQL($listing_id) . "' AND listing_status != 'Sold Out'");
            //DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_status = 'Sold Out', renew_flag = '0', delete_flag = '0', status = '0', is_error = '0', delete_track = '0', sold_flag = '1' WHERE listing_id = '" . pSQL($listing_id) . "' AND listing_status = 'Sold Out'");
            DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_id = NULL, listing_status = 'Pending', renew_flag = '0', delete_flag = '0', is_error = '0', delete_track = '0', sold_flag = '0', active = '0' WHERE listing_id = '" . pSQL($listing_id) . "'");
        }
        self::auditLogEntry('Job execution completed to delete the items from the etsy.', $method_name);
    }

    public static function deleteItemsFromEtsy($profile_product)
    {
        $listing_id = $profile_product['listing_id'];
        $method_name = 'EtsyModule::deleteItemsFromEtsy()';
        self::auditLogEntry('Job execution started to delete the item: ' . $listing_id . ' from the etsy.', $method_name);
        $etsyRequestURI = '/listings/' . $listing_id;
        $etsyRequestMethod = 'PUT';
        $etsyQueryString = array();
        $etsyQueryString['state'] = 'inactive';
        $inactive_response = json_decode(self::etsyGetOAuthResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString, false, false));
        if (!empty($inactive_response) && isset($inactive_response->results)) {
            DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_status = 'Inactive', renew_flag = '0', delete_flag = '0', is_error = '0', delete_track = '0', sold_flag = '0' WHERE listing_id = '" . pSQL($listing_id) . "'");

            // If $id_etsy_profiles is zero that means product is unmapped with the profile so need to delete from the table
            /* Below Logic Removed & updated only the Listing Status to Inactive only. No Need to delete the item from the etsy_products_list table who is having 0 profile_id in the table (As profile_id is 0 so item will not be used anywhere). Thus no need of the etsy_products_history table.
              if(!empty($profile_product['id_etsy_profiles'])) {
              DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_status = 'Inactive', renew_flag = '0', delete_flag = '0', is_error = '0', delete_track = '0', sold_flag = '0' WHERE listing_id = '" . pSQL($listing_id) . "'");
              } else {
              $history_exist = DB::getInstance()->getValue("SELECT count(*) FROM " . _DB_PREFIX_ . "etsy_products_history WHERE etsy_list_id = ". (int) $listing_id);
              if($history_exist <= 0) {
              DB::getInstance()->execute("INSERT INTO " . _DB_PREFIX_ . "etsy_products_history(product_id,etsy_list_id, expiry_date) VALUES (".(int) $profile_product['id_product']. ", '".pSQL($listing_id)."', '".pSQL($profile_product['expiry_date'])."'");
              }
              DB::getInstance()->execute("DELETE FROM " . _DB_PREFIX_ . "etsy_products_list WHERE listing_id = '" . pSQL($listing_id) . "'");
              }
             */
        }
        self::auditLogEntry('Job execution completed to delete the items from the etsy.', $method_name);
    }

    //To get products from etsy products table to get their current status from etsy marketplace.
    //Logic of product status sync is changed. Initially to sync the status of the product, Individual request of each product was being sent. Now changed the logic the get all the listing from the Etsy & sync status accordingly.
    public static function getProductsListedOnEtsy()
    {
        return DB::getInstance()->getValue("SELECT count(*) FROM " . _DB_PREFIX_ . "etsy_products_list WHERE listing_id IS NOT NULL AND listing_id != '' AND listing_id != 0 AND renew_flag = '0' AND delete_flag = '0' AND active = '1'");
        //return DB::getInstance()->executeS("SELECT listing_id FROM " . _DB_PREFIX_ . "etsy_products_list WHERE listing_id IS NOT NULL AND listing_id != '' AND listing_id != 0 AND renew_flag = '0' AND delete_flag = '0' AND active = 1", true, false);
    }

    public static function syncItemListingStatus()
    {
        /* Get Shop details */
        $shop = Tools::jsonDecode(self::etsyGetShopDetails());

        self::getItemsFromEtsy($shop->results[0]->shop_id, 'active', 1);
        self::getItemsFromEtsy($shop->results[0]->shop_id, 'expired', 1);
        self::getItemsFromEtsy($shop->results[0]->shop_id, 'inactive', 1);
        //self::getItemsFromEtsy($shop->results[0]->shop_id, 'sold_out', 1); //Not possible to find out the sold out listing
    }

    // Type like active, inactive, expired
    public static function getItemsFromEtsy($shop_id, $type, $page)
    {
        $etsyRequestURI = '/shops/' . $shop_id . '/listings/' . $type . '/';
        $etsyRequestMethod = 'GET';
        $etsyQueryString = array("limit" => 100, "page" => $page, "shop_id" => $shop_id);
        $response = Tools::jsonDecode(self::etsyGetOAuthResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString));
        if (!empty($response->results)) {
            foreach ($response->results as $item) {
                $listing_status = $item->state;
                $db_listing_status = '';
                if ($listing_status == 'inactive' || $listing_status == 'sold_out' || $listing_status == 'edit') {
                    $db_listing_status = 'Inactive';
                } else if ($listing_status == 'expired') {
                    $db_listing_status = 'Expired';
                } else if ($listing_status == 'active') {
                    $db_listing_status = 'Listed';
                } else if ($listing_status == 'sold_out') {
                    $db_listing_status = 'Sold Out';
                }
                if (!empty($db_listing_status)) {
                    if ($db_listing_status == "Inactive" || $db_listing_status == "Expired") {
                        DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_status = '" . pSQL($db_listing_status) . "', expiry_date = '" . date("Y-m-d H:i:s", $response->results[0]->ending_tsz) . "', delete_flag = '0', is_error = '0', renew_flag = '0', listing_error = '' WHERE listing_id = '" . (int) $item->listing_id . "' AND listing_status in ('Pending', 'Inactive', 'Expired', 'Listed', 'Updated')");
                    } else if ($db_listing_status == "Sold Out") {
                        // Considering Sold Out Items as Inactive Status
                        //DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_status = '" . pSQL($db_listing_status) . "', expiry_date = '".date("Y-m-d H:i:s", $response->results[0]->ending_tsz)."', sold_flag = '1', delete_flag = '0', is_error = '0', renew_flag = '0', listing_error = '' WHERE listing_id = '" . (int) $item->listing_id . "' AND listing_status in ('Pending', 'Inactive', 'Expired', 'Listed', 'Updated')");
                        DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_status = 'Inactive', expiry_date = '" . date("Y-m-d H:i:s", $response->results[0]->ending_tsz) . "', sold_flag = '1', delete_flag = '0', is_error = '0', renew_flag = '0', listing_error = '' WHERE listing_id = '" . (int) $item->listing_id . "' AND listing_status in ('Pending', 'Inactive', 'Expired', 'Listed', 'Updated')");
                    } else if ($db_listing_status = 'Listed') {
                        /* If Item is Marked as Pending, Inactive, Expired, Listed then only mark the item as Listed.
                         * Don't Mark item as listed if item is in following state: Updated, Relisting, Deletion Pending, Sold Out
                         * In case of Sold Out, Item should remain in Sold Out stauts so that it can be relist in case of restock
                         */
                        DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_status = '" . pSQL($db_listing_status) . "', expiry_date = '" . date("Y-m-d H:i:s", $response->results[0]->ending_tsz) . "' WHERE listing_id = '" . (int) $item->listing_id . "' AND listing_status in ('Pending', 'Inactive', 'Expired', 'Listed')");
                    }
                }
            }

            /* If page is equal to 1 then only run the loop. Because at page number 1, we are running loop for all the pages */
            if ($response->count > 100 && $page == 1) {
                $total_pages = ceil($response->count / 100);
                for ($i = 2; $i <= $total_pages; $i++) {
                    self::getItemsFromEtsy($shop_id, $type, $i);
                }
            }
        }
        return true;
    }

    //To send request on etsy to get product listings by listing_id. Function is not being used as logic has been changed to sync the status */
    public static function etsyGetListings($listingArray = array())
    {
        $method_name = 'EtsyModule::etsyGetListings()';
        self::auditLogEntry('Job execution started to get listing status from Etsy Marketplace.', $method_name);
        $statusUpdated = 0;
        if (!empty($listingArray) && count($listingArray) > 0) {
            foreach ($listingArray as $listing) {
                //Prepare parameters to send request
                $etsyRequestURI = '/listings/' . $listing['listing_id'] . '/';
                $etsyRequestMethod = 'GET';
                $etsyQueryString = $listing;

                $getListingResponse = Tools::jsonDecode(self::etsyGetOAuthResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString));

                if (!empty($getListingResponse) && isset($getListingResponse->results)) {
                    $listingStatus = $getListingResponse->results[0]->state;

                    //Check and update listing status as per database table column values
                    if ($listingStatus == 'inactive' || $listingStatus == 'sold_out' || $listingStatus == 'edit') {
                        $listingStatus = 'Inactive';
                    } else if ($listingStatus == 'expired') {
                        $listingStatus = 'Expired';
                    } else if ($listingStatus == 'active') {
                        $listingStatus = 'Listed';
                    } else if ($listingStatus == 'draft') {
                        $listingStatus = 'Draft';
                    } else {
                        $listingStatus = 'Pending';
                    }
                    
                    if (!empty($listingStatus)) {
                        $statusUpdated++;
                        DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_status = '" . pSQL($listingStatus) . "' WHERE listing_id = '" . (int) $listing['listing_id'] . "'");
                    }
                } else {
                    $listingError = str_replace("_", " ", key((array) $getListingResponse));
                    DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET is_error = 1, listing_error = '" . pSQL($listingError) . "' WHERE listing_id = '" . (int) $listing['listing_id'] . "'");
                }
                sleep(1); //Sleep job to avoid exceed limit rate
            }
        }
        self::auditLogEntry('Job execution completed to get listing status from etsy.<br>Total Listing Status Updated: ' . $statusUpdated, $method_name);
        return true;
    }

    //To get all shop receipts/orders from the etsy & add the same on PS
    public static function etsyGetShopReceipts()
    {
        $method_name = 'EtsyModule::etsyGetShopReceipts()';
        self::auditLogEntry('Job execution started to get orders from etsy.', $method_name);

        $receiptsFetched = 0;

        //Get Shop ID
        $shop = Tools::jsonDecode(self::etsyGetShopDetails());
        if (!empty($shop) && isset($shop->results)) {
            //Get date to fetch orders from etsy order table
            $lastDate = DB::getInstance()->getValue("SELECT MAX(date_added) as last_date FROM " . _DB_PREFIX_ . "etsy_orders_list");

            if (empty($lastDate)) {
                $lastDate = date("Y-m-d H:i:s", strtotime("-2 days"));
            }

            //Prepare parameters to send request
            $etsyRequestURI = '/shops/' . $shop->results[0]->shop_id . '/receipts/';
            $etsyRequestMethod = 'GET';
            $etsyQueryString = array(
                'min_created' => strtotime($lastDate)
            );
            $shopReceipts = Tools::jsonDecode(self::etsyGetOAuthResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString));
            if (!empty($shopReceipts) && isset($shopReceipts->results)) {
                $shopReceiptsList = self::prepareReceiptFieldsList($shopReceipts->results);

                if (!empty($shopReceiptsList)) {
                    foreach ($shopReceiptsList as $shopReceiptList) {
                        $orderResponse = KbMarketplaceIntegration::writeOrderIntoDb('kbetsy', $shopReceiptList);
                        if (isset($orderResponse['error']) && $orderResponse['error'] == '') {
                            if (!empty($orderResponse['success']['order_id'])) {
                                $receiptsFetched++;
                                DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_orders_list "
                                        . "SET id_order = '" . (int) $orderResponse['success']['order_id'] . "' "
                                        . "WHERE id_etsy_order = '" . (int) $shopReceiptList['order']['id_etsy_order'] . "'");
                            }
                        }
                        sleep(1); //Sleep job to avoid exceed limit rate
                    }
                }
            }
        }
        self::auditLogEntry('Job execution completed to get orders from etsy.<br>Total Orders Fetched: ' . $receiptsFetched, $method_name);
        return true;
    }

    //To prepare etsy Receipts
    private static function prepareReceiptFieldsList($receiptDetails = array())
    {
        $orderDetails = array();
        if (!empty($receiptDetails) && count($receiptDetails) > 0) {
            foreach ($receiptDetails as $receiptDetail) {
                //Get Transactions Details
                $etsyRequestURI = '/receipts/' . $receiptDetail->receipt_id . '/transactions/';
                $etsyRequestMethod = 'GET';
                $etsyQueryString = array();

                $receiptTransactions = Tools::jsonDecode(self::etsyGetOAuthResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString));
                if (!empty($receiptTransactions) && isset($receiptTransactions->results)) {
                    //Add Etsy Order entry in specific etsy order list table
                    $dataExistenceResult = Db::getInstance()->getValue("SELECT count(*) as count FROM " . _DB_PREFIX_ . "etsy_orders_list WHERE id_etsy_order = '" . (int) $receiptDetail->receipt_id . "'");
                    if ($dataExistenceResult == 0) {
                        DB::getInstance()->execute("INSERT INTO " . _DB_PREFIX_ . "etsy_orders_list VALUES (NULL, 0, '" . (int) $receiptDetail->receipt_id . "', '0','0', '" . pSQL(date("Y-m-d H:i:s", $receiptDetail->creation_tsz)) . "', NOW())");
                        //Set Firstname and Lastname parameters
                        if (!empty($receiptDetail->name)) {
                            $customerName = explode(' ', $receiptDetail->name, 2);
                        }
                        self::createCustomerByReceipts($receiptDetail, $customerName);

                        $receiptTransactionsList = $receiptTransactions->results;

                        //Get Country ID from Store Database
                        $orderCountry = self::getStoreCountryID($receiptDetail->country_id);

                        //Get State ID from Store Database
//                        $orderState = Configuration::get('etsy_order_default_status');
                        $orderState = self::getStoreStateID($receiptDetail->state, $orderCountry);

                        //Prepare Products Array for all ordered items
                        $productsArray = array();
                        

                        foreach ($receiptTransactionsList as $receiptTransactionList) {
                            //Get Product ID from Etsy Product List Table
                            $productID = DB::getInstance()->getRow("SELECT * FROM " . _DB_PREFIX_ . "etsy_products_list WHERE listing_id = '" . (int) $receiptTransactionList->listing_id . "'");

                            if (!empty($productID)) {
                                $productDetails = new ProductCore($productID['id_product']);

                                $productInventory = KbMarketplaceIntegration::getProductInventory($productID['id_product']);

                                //Get Product Attribute ID
                                $attributesString = '';
                                $finalAttributeProductID = array();
                                $variations = $receiptTransactionList->variations;
                                
                                if (!empty($variations)) {
                                    $attributeProductID = array();
                                    $counter = 0;
                                    
                                    $finalAttributeProductID = array();
                                    
                                    // If Order Item sku is in SKU_PRODUCTID_VARIATIONID. Pick the prestashop variation id from the etst SKU.
                                    if (!empty($receiptTransactionList->product_data->sku)) {
                                        if (Tools::substr($receiptTransactionList->product_data->sku, 0, 4) == "SKU_") {
                                            $sku_parts = explode("_", $receiptTransactionList->product_data->sku);
                                            if (count($sku_parts) == 3) {
                                                $finalAttributeProductID[0] = $sku_parts['2'];
                                            }
                                        }
                                    }
                                    
                                    // Find the Variation ID from the Property name like Size Small etc
//                                    if (empty($finalAttributeProductID)) {
                                    if (empty($finalAttributeProductID) || (isset($finalAttributeProductID[0]) &&  $finalAttributeProductID[0] == '')) {
                                        foreach ($variations as $variation) {
                                            $property_id = $variation->property_id;

                                            $selectSQL = "SELECT id_attribute_group FROM " . _DB_PREFIX_ . "etsy_attributes ea INNER JOIN " . _DB_PREFIX_ . "etsy_attribute_mapping1 am1 ON am1.property_id = ea.attribute_id WHERE etsy_property_id = '" . (int) $property_id . "'";
                                            $attributeGroupDetail = DB::getInstance()->executeS($selectSQL, true, false);
                                            if ($attributeGroupDetail != '') {
                                                $attributeGroup = $attributeGroupDetail[0]['id_attribute_group'];
                                                $attributeValue = html_entity_decode($variation->formatted_value);
                                                foreach ($attributeGroupDetail as $key => $singleAttributeGroup) {
                                                    $attributeGroup = $singleAttributeGroup['id_attribute_group'];
                                                    $selectSQL = "SELECT distinct(ppa.id_product_attribute) FROM " . _DB_PREFIX_ . "product_attribute ppa LEFT JOIN " . _DB_PREFIX_ . "product_attribute_combination pac ON ppa.id_product_attribute = pac.id_product_attribute LEFT JOIN " . _DB_PREFIX_ . "attribute_lang al ON pac.id_attribute = al.id_attribute LEFT JOIN " . _DB_PREFIX_ . "attribute a ON a.id_attribute = al.id_attribute WHERE a.id_attribute_group = '" . (int) $attributeGroup . "' AND al.name = '" . pSQL($attributeValue) . "' AND ppa.id_product = '" . (int) $productID['id_product'] . "'";
                                                    $attributeProductDetails = DB::getInstance()->executeS($selectSQL, true, false);
                                                    if (!empty($attributeProductDetails)) {
                                                        break;
                                                    }
                                                }

                                                $selectSQL = "SELECT distinct(ppa.id_product_attribute) FROM " . _DB_PREFIX_ . "product_attribute ppa LEFT JOIN " . _DB_PREFIX_ . "product_attribute_combination pac ON ppa.id_product_attribute = pac.id_product_attribute LEFT JOIN " . _DB_PREFIX_ . "attribute_lang al ON pac.id_attribute = al.id_attribute LEFT JOIN " . _DB_PREFIX_ . "attribute a ON a.id_attribute = al.id_attribute WHERE a.id_attribute_group = '" . (int) $attributeGroup . "' AND al.name = '" . pSQL($attributeValue) . "' AND ppa.id_product = '" . (int) $productID['id_product'] . "'";
                                                $attributeProductDetails = DB::getInstance()->executeS($selectSQL, true, false);

                                                if (!empty($attributeProductDetails)) {
                                                    foreach ($attributeProductDetails as $attributeProductDetail) {
                                                        $attributeProductID[$counter][] = $attributeProductDetail['id_product_attribute'];
                                                    }
                                                    if ($counter > 0 && isset($attributeProductID[$counter]) && isset($attributeProductID[$counter - 1])) {
                                                        $attributeProductID[$counter] = array_intersect($attributeProductID[$counter], $attributeProductID[$counter - 1]);
                                                    }
                                                }
                                            }

                                            if (isset($attributeProductID[$counter])) {
                                                $finalAttributeProductID = array_values($attributeProductID[$counter]);
                                            }
                                            $counter++;
                                        }
                                    }
                                    
                                    //Get Product Attributes details to concatenate with name
                                    if (isset($finalAttributeProductID[0])) {
                                        $attributesList = $productDetails->getAttributeCombinationsById($finalAttributeProductID[0], Context::getContext()->language->id);
                                        if (!empty($attributesList)) {
                                            foreach ($attributesList as $attributesList) {
                                                if (!empty($attributesString)) {
                                                    $attributesString .= ', ';
                                                }
                                                $attributesString .= $attributesList['group_name'] . ': ' . $attributesList['attribute_name'];
                                            }
                                        }
                                    }
                                }
                                $reference = $productDetails->reference;
                                $upc = $productDetails->upc;
                                $ean13 = $productDetails->ean13;
                                if (isset($finalAttributeProductID[0]) && !empty($finalAttributeProductID[0])) {
                                    $combination = new Combination($finalAttributeProductID[0]);
                                    $reference = $combination->reference;
                                    $upc = $combination->upc;
                                    $ean13 = $combination->ean13;
                                }

                                $productsArray[] = array(
                                    'id_product' => $productID['id_product'],
                                    'name' => $receiptTransactionList->title,
                                    'attributes' => !empty($attributesString) ? $attributesString : '',
                                    'weight' => $productDetails->weight,
                                    'ean13' => $ean13,
                                    'upc' => $upc,
                                    'ecotax' => 0,
                                    'reference' => $reference,
                                    'supplier_reference' => $productDetails->supplier_reference,
                                    'weight_attribute' => 0,
                                    'id_product_attribute' => !empty($finalAttributeProductID[0]) ? $finalAttributeProductID[0] : '',
                                    'cart_quantity' => $receiptTransactionList->quantity,
                                    'stock_quantity' => $productInventory,
                                    'id_customization' => $productDetails->customizable,
                                    'additional_shipping_cost' => 0,
                                    'id_shop' => Context::getContext()->shop->id,
                                    'price_wt' => $receiptTransactionList->price,
                                    'price' => $receiptTransactionList->price,
                                    'total_wt' => $receiptTransactionList->price * $receiptTransactionList->quantity,
                                    'total' => $receiptTransactionList->price * $receiptTransactionList->quantity,
                                    'wholesale_price' => $productDetails->wholesale_price,
                                    'id_supplier' => $productDetails->id_supplier
                                );
                            } else {
                                $productsArray[] = array(
                                    'id_product' => '0',
                                    'name' => $receiptTransactionList->title,
                                    'attributes' => '',
                                    'weight' => 0,
                                    'ean13' => '',
                                    'upc' => '',
                                    'ecotax' => 0,
                                    'reference' => '',
                                    'supplier_reference' => '',
                                    'weight_attribute' => 0,
                                    'id_product_attribute' => '',
                                    'cart_quantity' => $receiptTransactionList->quantity,
                                    'stock_quantity' => $receiptTransactionList->quantity,
                                    'id_customization' => 0,
                                    'additional_shipping_cost' => 0,
                                    'id_shop' => Context::getContext()->shop->id,
                                    'price_wt' => $receiptTransactionList->price,
                                    'price' => $receiptTransactionList->price,
                                    'total_wt' => $receiptTransactionList->price * $receiptTransactionList->quantity,
                                    'total' => $receiptTransactionList->price * $receiptTransactionList->quantity,
                                    'wholesale_price' => $receiptTransactionList->price,
                                    'id_supplier' => 0
                                );
                            }
                        }

                        $firstname = $receiptDetail->name;
                        if (!empty($customerName[0])) {
                            $firstname = $customerName[0];
                        }
                        $orderDetails[] = array(
                            'customer' => array(
                                'email' => $receiptDetail->buyer_email,
                                'firstname' => $firstname,
                                'lastname' => !empty($customerName[1]) ? $customerName[1] : $firstname,
                                'address1' => $receiptDetail->first_line,
                                'address2' => $receiptDetail->second_line,
                                'postcode' => $receiptDetail->zip,
                                'city' => $receiptDetail->city,
                                'phone_mobile' => '', //Etsy does not provide phone/mobile number
                                'id_state' => $orderState,
                                'id_country' => $orderCountry
                            ),
                            'order' => array(
                                'id_language' => Context::getContext()->language->id,
                                'currency_iso_code' => $receiptDetail->currency_code,
                                'name_carrier' => $receiptDetail->shipping_details->shipping_method,
                                'payment_method' => $receiptDetail->payment_method,
                                'id_warehouse' => 0, //As of now this module does not support advance stock management system
                                'cart_recyclable' => 0,
                                'cart_gift' => 0,
                                'id_shop' => Context::getContext()->shop->id,
                                'id_shop_group' => Context::getContext()->shop->id_shop_group,
                                'current_state' => !empty($receiptDetail->was_paid) ? Configuration::get('etsy_order_default_status') : Configuration::get('etsy_order_unpaid_status'),
//                                'current_state' => !empty($receiptDetail->was_paid) ? Configuration::get('etsy_order_paid_status') : Configuration::get('etsy_order_default_status'),
                                'order_reference' => Order::generateReference(),
                                'total_paid_real' => $receiptDetail->subtotal,
                                'total_products' => $receiptDetail->subtotal,
                                'total_products_wt' => $receiptDetail->subtotal,
                                'total_discounts_tax_excl' => 0,
                                'total_discounts_tax_incl' => 0,
                                'total_shipping_tax_excl' => $receiptDetail->total_shipping_cost,
                                'total_shipping_tax_incl' => $receiptDetail->total_shipping_cost,
                                'total_wrapping_tax_excl' => 0,
                                'total_wrapping_tax_incl' => 0,
                                'total_paid_tax_excl' => $receiptDetail->grandtotal,
                                // changes by rishabh jain for order message custom change
                                'order_msg' => $receiptDetail->message_from_buyer,
                                // changes over
                                'total_paid_tax_incl' => $receiptDetail->grandtotal,
                                'invoice_date' => '0000-00-00 00:00:00',
                                'delivery_date' => '0000-00-00 00:00:00',
                                'id_etsy_order' => $receiptDetail->receipt_id,
                                'is_paid' => $receiptDetail->was_paid,
                                'is_shipped' => $receiptDetail->was_shipped
                            ),
                            'products' => $productsArray
                        );
                    }
                }
            }
        }
        return $orderDetails;
    }

    //To create customer in prestashop if customer is not exist who placed orders on the Etsy
    public static function createCustomerByReceipts($receiptDetail, $customerName)
    {
        if (!empty($receiptDetail->name)) {
            $customerName = explode(' ', $receiptDetail->name, 2);
        }
        $firstname = $receiptDetail->name;
        if (!empty($customerName[0])) {
            $firstname = $customerName[0];
        }
        /* Remove Special Char & numbers from the name as PS doesn't allow the same in name. Added by Ashish on 6-Feb-2020*/
        $firstname = preg_replace('/[^\da-z ]/i', '', $firstname);
        $firstname = preg_replace('/[0-9]+/', '', $firstname);
        
        if (!empty($customerName[1])) {
            $customerName[1] = preg_replace('/[^\da-z ]/i', '', $customerName[1]);
            $customerName[1] = preg_replace('/[0-9]+/', '', $customerName[1]);
        }

        $check_customer_exist = Customer::customerExists($receiptDetail->buyer_email, false, false);
        if (!$check_customer_exist) {
            $create_customer = new Customer();
            $create_customer->email = $receiptDetail->buyer_email;
            $create_customer->firstname = $firstname;
            $create_customer->lastname = !empty($customerName[1]) ? $customerName[1] : $firstname;
            $create_customer->is_guest = 1;
            $create_customer->active = 1;
            $original_passd = Tools::substr(md5(uniqid(mt_rand(), true)), 0, 8);
            $passd = Tools::encrypt($original_passd);
            $create_customer->passwd = $passd;
            $create_customer->secure_key = md5(uniqid(rand(), true));
            $create_customer->add();
        }
    }

    private static function getStoreStateID($state_iso_code, $etsyCountryID = '')
    {
        $storeStateID = 0;
        if (!empty($etsyCountryID)) {
            $sql = "SELECT id_state FROM " . _DB_PREFIX_ . "state WHERE country_id = '" . (int) $etsyCountryID . "' and iso_code = '".psql($state_iso_code)."'";
            $stateDetail = DB::getInstance()->getValue("SELECT id_state FROM " . _DB_PREFIX_ . "state WHERE id_country = '" . (int) $etsyCountryID . "' and iso_code = '".psql($state_iso_code)."'");
            if ($stateDetail) {
                return $stateDetail;
            }
        }
        return $storeStateID;
    }
    public static function getVariationIdByPropertyValue($variations, $productID)
    {
        $counter = 0;
        $attributeProductID = array();
        foreach ($variations as $variation) {
            $property_id = $variation['property_id'];
            $selectSQL = "SELECT id_attribute_group FROM " . _DB_PREFIX_ . "etsy_attributes ea INNER JOIN " . _DB_PREFIX_ . "etsy_attribute_mapping1 am1 ON am1.property_id = ea.attribute_id WHERE etsy_property_id = '" . (int) $property_id . "'";
            $attributeGroupDetail = DB::getInstance()->executeS($selectSQL, true, false);
            if ($attributeGroupDetail != '') {
                $attributeGroup = $attributeGroupDetail[0]['id_attribute_group'];
                $attributeValue = html_entity_decode($variation['values'][0]);
                foreach ($attributeGroupDetail as $key => $singleAttributeGroup) {
                    $attributeGroup = $singleAttributeGroup['id_attribute_group'];
                    $selectSQL = "SELECT distinct(ppa.id_product_attribute) FROM " . _DB_PREFIX_ . "product_attribute ppa LEFT JOIN " . _DB_PREFIX_ . "product_attribute_combination pac ON ppa.id_product_attribute = pac.id_product_attribute LEFT JOIN " . _DB_PREFIX_ . "attribute_lang al ON pac.id_attribute = al.id_attribute LEFT JOIN " . _DB_PREFIX_ . "attribute a ON a.id_attribute = al.id_attribute WHERE a.id_attribute_group = '" . (int) $attributeGroup . "' AND al.name = '" . pSQL($attributeValue) . "' AND ppa.id_product = '" . (int) $productID . "'";
                    $attributeProductDetails = DB::getInstance()->executeS($selectSQL, true, false);
                    if (!empty($attributeProductDetails)) {
                        break;
                    }
                }
                $selectSQL = "SELECT distinct(ppa.id_product_attribute) FROM " . _DB_PREFIX_ . "product_attribute ppa LEFT JOIN " . _DB_PREFIX_ . "product_attribute_combination pac ON ppa.id_product_attribute = pac.id_product_attribute LEFT JOIN " . _DB_PREFIX_ . "attribute_lang al ON pac.id_attribute = al.id_attribute LEFT JOIN " . _DB_PREFIX_ . "attribute a ON a.id_attribute = al.id_attribute WHERE a.id_attribute_group = '" . (int) $attributeGroup . "' AND al.name = '" . pSQL($attributeValue) . "' AND ppa.id_product = '" . (int) $productID . "'";
                $attributeProductDetails = DB::getInstance()->executeS($selectSQL, true, false);
                if (!empty($attributeProductDetails)) {
                    foreach ($attributeProductDetails as $attributeProductDetail) {
                        $attributeProductID[$counter][] = $attributeProductDetail['id_product_attribute'];
                    }
                    if ($counter > 0 && isset($attributeProductID[$counter]) && isset($attributeProductID[$counter - 1])) {
                        $attributeProductID[$counter] = array_intersect($attributeProductID[$counter], $attributeProductID[$counter - 1]);
                    }
                }
            }
            if (isset($attributeProductID[$counter])) {
                $finalAttributeProductID = array_values($attributeProductID[$counter]);
            }
            $counter++;
        }
        if (isset($finalAttributeProductID[0])) {
            return $finalAttributeProductID[0];
        } else {
            return '';
        }
    }
    //To update status of the orders/shop receipts on Etsy (Based on the status on PS)
    public static function etsyUpdateShopReceipts()
    {
        $reciptsUpdated = 0;

        $method_name = 'EtsyModule::etsyUpdateShopReceipts()';
        self::auditLogEntry('Job execution started to update orders status on etsy.', $method_name);

//        $paidStatus = Configuration::get('etsy_order_paid_status');
        $shippedStatus = Configuration::get('etsy_order_shipped_status');

        if (!empty($shippedStatus)) {            //Get orders to update status on etsy marketplace
            $receipts = DB::getInstance()->executeS("SELECT eol.id_order, eol.id_etsy_order, o.current_state "
                    . "FROM " . _DB_PREFIX_ . "etsy_orders_list eol, " . _DB_PREFIX_ . "orders o "
                    . "WHERE o.id_order = eol.id_order "
                    . "AND eol.is_status_updated = '1' "
                    . "AND (o.current_state = '" . (int) $shippedStatus . "')", true, false);

            if (!empty($receipts)) {
                foreach ($receipts as $receipt) {
                    $etsyRequestURI = '/receipts/' . $receipt['id_etsy_order'] . '/';
                    $etsyRequestMethod = 'PUT';

                    if ($receipt['current_state'] == $shippedStatus) {
                        $etsyQueryString = array(
                            'was_paid' => 1,
                            'was_shipped' => 1
                        );
                    }

                    if (!empty($etsyQueryString)) {
                        $response = Tools::jsonDecode(self::etsyGetOAuthResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString));

                        if (!empty($response) && isset($response->results)) {
                            $reciptsUpdated++;

                            DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_orders_list "
                                    . "SET is_status_updated = '0' "
                                    . "WHERE id_etsy_order = '" . (int) $receipt['id_etsy_order'] . "'");
                        } else {
                            $listingError = str_replace("_", " ", key((array) $response));
                            self::auditLogEntry($listingError, $method_name);
                        }
                    }
                    sleep(1); //Sleep job to avoid exceed limit rate
                }
            }
        }
        self::auditLogEntry('Job execution completed to update orders status on etsy marketplace.<br>Total Orders Status Updated: ' . $reciptsUpdated, $method_name);
        
        if (Configuration::get('upload_tracking_number')) {
            self::etsyUpdateTracking();
        }
        return true;
    }

    public static function etsyUpdateTracking()
    {
        $method_name = 'EtsyModule::etsyUpdateTracking()';
        self::auditLogEntry('Job execution started to add orders tracking on etsy.', $method_name);
        
        $shop = Tools::jsonDecode(self::etsyGetShopDetails());
        $reciptsUpdated = 0;


        $shippedName = Configuration::get('etsy_selected_shipment_name');
        
        if (!empty($shippedName)) {
            $receipts = DB::getInstance()->executeS("SELECT eol.id_order, eol.id_etsy_order,o.shipping_number "
                    . "FROM " . _DB_PREFIX_ . "etsy_orders_list eol, " . _DB_PREFIX_ . "orders o "
                    . "WHERE o.id_order = eol.id_order "
                    . "AND eol.is_tracking_updated = '0' "
                    . "AND o.shipping_number != '' ", true, false);
            if (!empty($receipts)) {
                foreach ($receipts as $receipt) {
                    $etsyRequestMethod = 'POST';
                    $etsyRequestURI = '/shops/' . $shop->results[0]->shop_id . '/receipts/' . $receipt['id_etsy_order'] . '/tracking/';
                    
                    $etsyQueryString = array(
                        'carrier_name' => $shippedName,
                        'tracking_code' => $receipt['shipping_number'],
                        'send_bcc' => 0,
                    );

                    if (!empty($etsyQueryString)) {
                        $response = Tools::jsonDecode(self::etsyGetOAuthResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString));
                        if (!empty($response) && isset($response->results)) {
                            $reciptsUpdated++;
                            DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_orders_list "
                                    . "SET is_tracking_updated = '1' "
                                    . "WHERE id_etsy_order = '" . (int) $receipt['id_etsy_order'] . "'");
                        } else {
                            $listingError = str_replace("_", " ", key((array) $response));
                            self::auditLogEntry($listingError, $method_name);
                        }
                    }
                    sleep(1); //Sleep job to avoid exceed limit rate
                }
            }
        }
        
        self::auditLogEntry('Job execution completed to update Tracking status on etsy marketplace.<br>Total Orders Tracking Added: ' . $reciptsUpdated, $method_name);
        return true;
    }

    //To get Store Country ID
    private static function getStoreCountryID($etsyCountryID = '')
    {
        $storeCountryID = 0;
        if (!empty($etsyCountryID)) {
            $countryDetail = DB::getInstance()->getRow("SELECT country_name, iso_code FROM " . _DB_PREFIX_ . "etsy_countries WHERE country_id = '" . (int) $etsyCountryID . "'");
            if (!empty($countryDetail)) {
                if (!empty($countryDetail['iso_code'])) {
                    $storeCountryID = Country::getByIso($countryDetail['iso_code']);
                } else if (!empty($countryDetail['country_name'])) {
                    $storeCountryID = Country::getIdByName(null, $countryDetail['country_name']);
                }
            }
        }
        return $storeCountryID;
    }

    //Get countries from DB
    public static function etsyGetAllCountriesFromDB()
    {
        return Db::getInstance()->executeS("SELECT * FROM " . _DB_PREFIX_ . "etsy_countries", true, false);
    }

    //To get country name from DB
    public static function etsyGetCountryNameByCountryId($country_id)
    {
        return Db::getInstance()->getValue("SELECT country_name FROM " . _DB_PREFIX_ . "etsy_countries WHERE country_id = " . (int) $country_id, true, false);
    }

    //To get region name from DB
    public static function etsyGetRegionNameByRegionId($region_id)
    {
        return Db::getInstance()->getValue("SELECT region_name FROM " . _DB_PREFIX_ . "etsy_regions WHERE region_id = " . (int) $region_id, true, false);
    }

    //To get etsy regions from DB
    public static function etsyGetAllRegionsFromDB()
    {
        return Db::getInstance()->executeS("SELECT * FROM " . _DB_PREFIX_ . "etsy_regions");
    }

    public function downloadItem(&$data)
    {
        $shop = Tools::jsonDecode(self::etsyGetShopDetails());
        self::getItemsFromEtsyToDownload($shop->results[0]->shop_id, 'active', 1, $data);
    }

    // Type like active, inactive, expired
    public static function getCommentItemsFromEtsyToDownload($shop_id, $type, $page, &$data_item)
    {
        $data_item = array();
        $lang_id = context::getContext()->language->id;
        $etsyRequestURI = '/shops/' . $shop_id . '/listings/' . $type . '/';
        $etsyRequestMethod = 'GET';
        $etsyQueryString = array("limit" => 1, "page" => $page, "shop_id" => $shop_id, "language" => "en", "includes" => "Translations");
        $response = Tools::jsonDecode(self::etsyGetOAuthResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString));
        $languages = Language::getLanguages(false);
        if (!empty($response->results)) {
            foreach ($response->results as $item) {
                $sql = 'SELECT COUNT(*) as count FROM ' . _DB_PREFIX_ . 'product_mapping_from_etsy WHERE listing_id = ' . (int) $item->listing_id;
                $avl = DB::getInstance()->getRow($sql);
                if ($avl['count'] == 0) {
                    $data = array();
                    $data['listing_id'] = $item->listing_id;
                    $data['sku'] = implode(",", $item->sku);

                    //multi lang fields
                    foreach ($item->Translations as $lang) {
                        if (strpos($lang->language, 'US') >= 0) {
                            $d = explode('-', $lang->language);
                            $lang->language = $d[0];
                        }
                        if ($lang->language == 'en' || $lang->language == 'it') {
                            $id = Language::getIdByIso($lang->language);
                            if ($id) {
                                $data['title'][$id] = $lang->title;  //
                                $data['description'][$id] = $lang->description;  //
                                $data['tags'][$id] = implode(",", $lang->tags);  //
                            }
                        }
                    }
                    $data['price'] = $item->price;
                    $data['currency_code'] = $item->currency_code;
                    $data['quantity'] = $item->quantity;
                    $data['materials'] = implode(",", $item->materials);

                    $etsyRequestURI = '/listings/' . $item->listing_id . '/images';
                    $images = Tools::jsonDecode(self::etsyGetOAuthResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString));
                    $i = 1;
                    foreach ($images->results as $image) {
                        if ($i > 10) {
                            break;
                        }
                        $data['images'][$i] = $image->url_fullxfull;
                        $i++;
                    }
                    $etsyRequestURI = '/listings/' . $item->listing_id . '/inventory';
                    $inventory = Tools::jsonDecode(self::etsyGetOAuthResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString));
                    $variation_data = array();
                    if ($item->has_variations) {
                        if (!empty($inventory->results->products)) {
                            foreach ($inventory->results->products as $variation) {
                                $variation_data[] = array("name" => $variation->property_values[0]->property_name,
                                    "values" => $variation->property_values[0]->values[0],
                                    'price' => $variation->offerings[0]->price->currency_formatted_raw,
                                    'currency' => $variation->offerings[0]->price->currency_code,
                                    'quantity' => $variation->offerings[0]->quantity,
                                );
                            }
                        }
                        $variationData = array();
                        if (!empty($variation_data)) {
                            foreach ($variation_data as $key => $values) {
                                $variationData[$values['name']][] = $values['values'];
                            }
                        }

                        // save new attributes
                        $languages = Language::getLanguages(false);
                        $array_id_attr = array();
                        $attr_grp_id = array();
                        foreach ($variationData as $key => $value) {
                            $sql = 'SELECT COUNT(*) as count FROM ' . _DB_PREFIX_ . 'attribute_group_lang WHERE name = "' . $key . '" AND id_lang = ' . (int) $lang_id;
                            $val = DB::getInstance()->getRow($sql);
                            if ($val['count'] == 0) {
                                $attrGrp = new AttributeGroup();
                                foreach ($languages as $language) {
                                    $attrGrp->name[(int) $language['id_lang']] = $key;
                                    $attrGrp->public_name[(int) $language['id_lang']] = $key;
                                }
                                $attrGrp->is_color_group = 0;
                                $attrGrp->group_type = 'select';
                                if ($key == 'Primary color') {
                                    $attrGrp->is_color_group = 1;
                                    $attrGrp->group_type = 'color';
                                }
                                $attrGrp->add();
                                $attr_grp_id[$key] = $attrGrp->id;
                            } else {
                                $sql = 'SELECT id_attribute_group FROM ' . _DB_PREFIX_ . 'attribute_group_lang WHERE name = "' . $key . '" AND id_lang = ' . (int) $lang_id;
                                $val = DB::getInstance()->getRow($sql);
                                $attr_grp_id[$key] = $val['id_attribute_group'];
                            }
                        }



                        foreach ($variationData as $key => $value) {
                            foreach ($value as $attribute) {
                                $sql = 'SELECT COUNT(*) as count FROM ' . _DB_PREFIX_ . 'attribute_lang WHERE name = "' . $attribute . '" AND id_lang = ' . (int) $lang_id;
                                $val = DB::getInstance()->getRow($sql);
                                if ($val['count'] == 0) {
                                    $attr = new Attribute();
                                    $attr->id_attribute_group = $attr_grp_id[$key];
                                    foreach ($languages as $language) {
                                        $attr->name[(int) $language['id_lang']] = $attribute;
                                    }
                                    if ($attr->add()) {
                                        $array_id_attr[] = $attr->id;
                                    }
                                } else {
                                    $sql = 'SELECT id_attribute FROM ' . _DB_PREFIX_ . 'attribute_lang WHERE name = "' . $attribute . '" AND id_lang = ' . (int) $lang_id;
                                    $val = DB::getInstance()->getRow($sql);
                                    $array_id_attr[] = $val['id_attribute'];
                                }
                            }
                        }

                        foreach ($variation_data as $key => &$value) {
                            $value['id_attr'] = $array_id_attr[$key];
                        }
                        $data['variation_data'] = $variation_data;
                    }
                    $data_item[] = $data;
                }
            }

            if (isset($data_item) && !empty($data_item)) {
                foreach ($data_item as $key => $value1) {
                    self::saveProduct($value1);
                }
            }

            /* If page is equal to 1 then only run the loop. Because at page number 1, we are running loop for all the pages */
//            if ($response->count > 100 && $page == 1) {
//                $total_pages = ceil($response->count / 100);
//                for ($i = 2; $i <= $total_pages; $i++) {
//                    //self::getItemsFromEtsy($shop_id, $type, $i);
//                }
//            }
        }
        die;
        return true;
    }

    // Added By Anshul for saving the new product
    public static function saveProduct($productData)
    {
        $object = new Product();
        $shop_id = context::getContext()->shop->id;
        $languages = Language::getLanguages(false);
        //Add Name & Desc
        foreach ($languages as $language) {
            $object->name[(int) $language['id_lang']] = isset($productData['title'][(int) $language['id_lang']]) ? Tools::substr($productData['title'][(int) $language['id_lang']], 0, 60) : '';
            $object->description[(int) $language['id_lang']] = isset($productData['description'][(int) $language['id_lang']]) ? $productData['description'][(int) $language['id_lang']] : '';
        }
        //Add price, qty and reference
        $object->price = isset($productData['price']) ? $productData['price'] : 0.00;
        $object->quantity = isset($productData['quantity']) ? $productData['quantity'] : 0;
        $object->reference = isset($productData['sku']) ? $productData['sku'] : '';
        $object->id_tax_rules_group = 6;
        if ($object->add()) {
            //Add Tags
            foreach ($languages as $language) {
                Tag::addTags($language['id_lang'], (int) $object->id, isset($productData['tags'][(int) $language['id_lang']]) ? $productData['tags'][(int) $language['id_lang']] : '');
            }

            //Save Images
            if (isset($productData['images']) && count($productData['images']) > 0) {
                self::processSaveImages($productData, (int) $object->id);
            }

            //Add Combination
            if (isset($productData['variation_data']) && count($productData['variation_data']) > 0) {
                self::processSaveCombination($productData, (int) $object->id);
            } else {
                StockAvailable::setQuantity(
                    $object->id,
                    0,
                    (int) $productData['quantity'],
                    (int) $shop_id
                );
            }
            Db::getInstance()->insert('product_mapping_from_etsy', array(
                'id_product' => (int) $object->id,
                'listing_id' => pSQL($productData['listing_id']),
                'date_added' => Date('Y-m-d H:i:s', time()),
                'date_updated' => Date('Y-m-d H:i:s', time()),
            ));
        }
    }

    public static function processSaveImages($productData, $product_id)
    {
        $product = new Product((int) $product_id);

        if (!Validate::isLoadedObject($product)) {
            return;
        } else if (count($productData['images']) == 0) {
            return;
        }

        $file = array();
        foreach ($productData['images'] as $filePath) {
            $image = new Image();
            $image->id_product = (int) ($product->id);
            $image->position = Image::getHighestPosition($product->id) + 1;

            if (!Image::getCover($image->id_product)) {
                $image->cover = 1;
            } else {
                $image->cover = 0;
            }

            if (isset($file['error']) && (!is_numeric($file['error']) || $file['error'] != 0)) {
                continue;
            }

            if (!$image->add()) {
                $file['error'] = 'Error while creating additional image.';
            } else {
                if (!$new_path = $image->getPathForCreation()) {
                    $file['error'] = 'An error occurred during new folder creation.';
                    continue;
                }
                $data = Tools::file_get_contents($filePath);
                $time = time();
                $fp = _PS_MODULE_DIR_ . 'kbetsy/images/image_' . $time . '.jpg';
                file_put_contents($fp, $data);
                $error = 0;
                if (!ImageManager::resize(
                    $fp,
                    $new_path . '.' . $image->image_format,
                    null,
                    null,
                    'jpg',
                    false,
                    $error
                )) {
                    switch ($error) {
                        case ImageManager::ERROR_FILE_NOT_EXIST:
                            $file['error'] = 'An error occurred while copying image, file does not exist anymore.';
                            break;
                        case ImageManager::ERROR_FILE_WIDTH:
                            $file['error'] = 'An error occurred while copying image, file width is 0px.';
                            break;
                        case ImageManager::ERROR_MEMORY_LIMIT:
                            $file['error'] = 'An error occurred while copying image, check your memory limit.';
                            break;
                        default:
                            $file['error'] = 'An error occurred while copying image.';
                            break;
                    }
                    continue;
                } else {
                    $imagesTypes = ImageType::getImagesTypes('products');
                    foreach ($imagesTypes as $imageType) {
                        if (!ImageManager::resize(
                            $fp,
                            $new_path . '-' . Tools::stripslashes($imageType['name']) . '.' . $image->image_format,
                            $imageType['width'],
                            $imageType['height'],
                            $image->image_format
                        )
                        ) {
                            $file['error'] = sprintf(
                                'An error occurred while copying image: %s',
                                Tools::stripslashes($imageType['name'])
                            );
                            continue;
                        }
                    }
                }

                //Necesary to prevent hacking
                Hook::exec('actionWatermark', array('id_image' => $image->id, 'id_product' => $product->id));

                if (!$image->update()) {
                    $file['error'] = 'Error while updating status.';
                    continue;
                }
            }
        }

        if (isset($file['error']) && !empty($file['error'])) {
            return false;
        } else {
            return true;
        }
    }

    // Added By Anshul for saving the combination of new product
    public static function processSaveCombination($productData, $product_id)
    {
        $id_product = (int) $product_id;
        $shop_id = context::getContext()->shop->id;
        $product = new Product($id_product);
        foreach ($productData['variation_data'] as $key => $value) {
            $impact_on_price = (float) $value['price'] - (float) $productData['price'];
            $id_product_attribute = $product->addCombinationEntity(
                0,
                (float) $impact_on_price,
                0,
                0,
                0,
                0,
                0,
                $productData['sku'],
                null,
                '',
                0,
                null,
                '',
                1,
                array(),
                null
            );

            StockAvailable::setQuantity(
                $product->id,
                (int) $id_product_attribute,
                (int) $value['quantity'],
                (int) $shop_id
            );
            $data_id_attr = array();
            $combination = new Combination((int) $id_product_attribute);
            $data_id_attr[] = $value['id_attr'];
            $combination->setAttributes($data_id_attr);
            unset($data_id_attr);
        }
    }
    
    // Function to filter products title. Title cannot contain the characters %, &, or : more than once
    private static function replaceInstance($string)
    {
        $pos = strpos($string, '%');
        if ($pos !== false) {
            $string = Tools::substr($string, 0, $pos + 1) . str_replace('%', ' ', Tools::substr($string, $pos + 1));
        }
        
        $pos = strpos($string, '&');
        if ($pos !== false) {
            $string = Tools::substr($string, 0, $pos + 1) . str_replace('&', ' ', Tools::substr($string, $pos + 1));
        }
        
        $pos = strpos($string, ':');
        if ($pos !== false) {
            $string = Tools::substr($string, 0, $pos + 1) . str_replace(':', ' ', Tools::substr($string, $pos + 1));
        }
        
        return $string;
    }
}
