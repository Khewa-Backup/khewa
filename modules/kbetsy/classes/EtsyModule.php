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

require_once(_PS_MODULE_DIR_ . 'kbetsy/vendor/http.php');
require_once(_PS_MODULE_DIR_ . 'kbetsy/vendor/oauth_client.php');
require_once(_PS_MODULE_DIR_ . 'kbetsy/classes/KbMarketplaceIntegration.php');
require_once(_PS_MODULE_DIR_ . 'kbetsy/classes/SyncShopSection.php');
require_once(_PS_MODULE_DIR_ . 'kbetsy/classes/SyncReturnPolicy.php');
require_once(_PS_MODULE_DIR_ . 'kbetsy/classes/SyncTemplate.php');
require_once(_PS_MODULE_DIR_ . 'kbetsy/classes/EtsyProfiles.php');
require_once(_PS_MODULE_DIR_ . 'kbetsy/classes/EtsyShippingTemplates.php');

class EtsyModule extends Module
{

    public function __construct($index = null, $type = null)
    {
        parent::__construct();

        //List of confirmations
        $module = Module::getInstanceByName('kbetsy');
        $this->_etsyConf = array(
            '1' => $module->l('Settings has been saved and connected to Etsy Marketplace successfully.', 'EtsyModule'),
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
            /*
             * Added language strings for return policy success messages
             * @modifier Himanshu Vishwakarma
             * @date 15-12-2025
             */
            '70' => $module->l('Return Policy has been added successfully.', 'EtsyModule'),
            '71' => $module->l('Return Policy has been updated successfully.', 'EtsyModule'),
            '72' => $module->l('Return Policy has been deleted successfully.', 'EtsyModule'),
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
            /*
             * Added language strings for return policy error messages
             * @modifier Himanshu Vishwakarma
             * @date 15-12-2025
             */
            '68' => $module->l('Return Policy could not be deleted. Please try again later.', 'EtsyModule'),
            '74' => $module->l('Return Policy already exists. A return policy with the same values (Accepts Returns, Accepts Exchanges, Return Deadline) already exists.', 'EtsyModule'),
            '75' => $module->l('Return Policy can not be deleted because it is mapped with the Profile.', 'EtsyModule'),
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

    /* Function definition to add an entry of Audit Log into Database
     * @date 23-05-2023
     * @author
     * @commenter Tanisha Gupta
     */
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
                return json_encode($etsyResponse);
            } else {
                return json_encode($etsyResponse);
            }
        } else {
            return "Invalid Request.";
        }
    }

    //Send cURL request to Etsy & get response
    /*
     * Added new parameter $etsyContentType that defines data content type
     * @date 09-04-2023
     * @author Tanisha Gupta
     */
    public static function etsyGetResponse($etsyRequestURI = '', $etsyRequestMethod = '', $etsyQueryString = '', $etsyContentType = '')
    {
        /**
         * Added to generate access token when send request on etsy
         * @date 09-04-2023
         * @author Tanisha Gupta
         */
        $token = self::getAccessToken();
        $config = Configuration::get('etsy_api_key');
        /**
         * Added the secret to the x-api-key header
         * @modifier Himanshu Vishwakarma
         * @date 11-12-2025
         */
        $secret = Configuration::get('etsy_api_secret');
        if (!empty($etsyQueryString) && (empty($etsyContentType))) {
            $headers = array(
                'Content-Type: application/x-www-form-urlencoded',
                'x-api-key: ' . $config . ':' . $secret,
                'Authorization: Bearer ' . $token
            );
        } else if (!empty($etsyQueryString) && ($etsyContentType == 'formtype')) {
            $headers = array(
                'Content-Type: multipart/form-data',
                'x-api-key: ' . $config . ':' . $secret,
                'Authorization: Bearer ' . $token
            );
        } else {
            $headers = array(
                'Content-Type: application/json',
                'x-api-key: ' . $config . ':' . $secret,
                'Authorization: Bearer ' . $token
            );
        }
        $connection = curl_init();
        $url = Configuration::get('etsy_api_host') . Configuration::get('etsy_api_version') . "/application" . $etsyRequestURI;

        /*
        * Chages done to add the query parameter in the get request
        * @date 31-03-2026
        * @modifier Manish
        * MPMAR2026 module_specific_issues
        */
        if ($etsyRequestMethod == 'GET' && !empty($etsyQueryString)) {
            $url = $url . '?' . $etsyQueryString;
        }
        curl_setopt($connection, CURLOPT_URL, $url);
        if ((!empty($etsyQueryString) && (empty($etsyRequestMethod) || $etsyRequestMethod == 'POST'))) {
            curl_setopt($connection, CURLOPT_POST, 1);
            curl_setopt($connection, CURLOPT_POSTFIELDS, $etsyQueryString);
        } else if (!empty($etsyQueryString) && ($etsyRequestMethod == 'PUT' || $etsyRequestMethod == 'PATCH')) {
            curl_setopt($connection, CURLOPT_CUSTOMREQUEST, $etsyRequestMethod);
            curl_setopt($connection, CURLOPT_POSTFIELDS, $etsyQueryString);
        }
        if ($etsyRequestMethod == 'DELETE') {
            curl_setopt($connection, CURLOPT_CUSTOMREQUEST, "DELETE");
        }
        curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($connection, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($connection);
        $info = curl_getinfo($connection);
        curl_close($connection);
        $result_data = json_decode($response, true);
        return $result_data;
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
        /**
         * Changed url to fetch shop according to the v3
         */
        //$etsyRequestURI = Configuration::get('etsy_api_host') . Configuration::get('etsy_api_version') . "/users/" . Configuration::get('etsy_api_user_id') . "/shops/?api_key=" . Configuration::get('etsy_api_key');
        $etsyRequestURI = "/users/" . Configuration::get('etsy_api_user_id') . "/shops";
        return self::etsyGetResponse($etsyRequestURI);
    }

    //Function to save All Countries
    public static function etsyGetAllCountries()
    {
        $etsyRequestURI = Configuration::get('etsy_api_host') . Configuration::get('etsy_api_version') . "/countries/?api_key=" . Configuration::get('etsy_api_key');
        $etsyCountriesList = self::etsyGetResponse($etsyRequestURI);
        if (!empty($etsyCountriesList)) {
            $etsyCountriesList = json_decode($etsyCountriesList);
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
            $etsyRegionsList = json_decode($etsyRegionsList);
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
    /**
     * Changes added by for localsync on profile level. Modified the function params to get the profile id
     * @modifier pragya maurya
     * @date 04-06-2024
     * Etsy-enhancement-profile-level
     */
    public static function getAllProfileProducts($kbprofileid = false)
    {
        $method_name = 'EtsyModule::getAllProfileProducts()';
        EtsyModule::auditLogEntry('Local Sync job execution statrted.', $method_name);

        $productsInserted = 0;
        $productsUpdated = 0;

        /**
         * To fetch the products from that profile only
         * @modifier pragya maurya
         * @date 04-06-2024
         * Etsy-enhancement-profile-level
         */
        if ($kbprofileid) {
            Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET delete_track = '1' where id_etsy_profiles = " . (int) $kbprofileid);
            $profiles = Db::getInstance()->executeS("SELECT * FROM " . _DB_PREFIX_ . "etsy_profiles WHERE active = '1' AND id_etsy_profiles = " . (int) $kbprofileid);
        } else {
            Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET delete_track = '1'");
            $profiles = Db::getInstance()->executeS("SELECT * FROM " . _DB_PREFIX_ . "etsy_profiles WHERE active = '1'");
        }
        //End changes added

        if (!empty($profiles)) {
            foreach ($profiles as $profile) {
                /* If Product Selection Type is Product in the Profile */
                if ($profile['etsy_product_type'] == 1) {
                    $etsy_selected_products = $profile['etsy_selected_products'];
                    $etsy_selected_product_array = explode("-", $etsy_selected_products);
                    if (!empty($etsy_selected_product_array)) {
                        foreach ($etsy_selected_product_array as $etsy_product) {
                            if (!empty($etsy_product)) {
                                /** Check if product is already exists in Db table */
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
                                    Db::getInstance()->execute($insertSQL);
                                    $productsInserted++;
                                } else {
                                    $updateSQL = "UPDATE " . _DB_PREFIX_ . "etsy_products_list SET "
                                        . "id_etsy_profiles = '" . (int) $profile['id_etsy_profiles'] . "',"
                                        . "reference = '" . pSQL($product_info->reference) . "',"
                                        . "delete_track = '0', "
                                        . "is_error = '0'"
                                        . "WHERE id_product = '" . (int) $etsy_product . "'";
                                    if (Db::getInstance()->execute($updateSQL)) {
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

                                        /** Check if product is already exists in Db table */
                                        $dataExistenceResult = Db::getInstance()->getValue("SELECT count(*) as count FROM " . _DB_PREFIX_ . "etsy_products_list WHERE id_product = '" . (int) $categoryProduct['id_product'] . "'");
                                        if ($dataExistenceResult == 0) {
                                            $insertSQL = "INSERT INTO " . _DB_PREFIX_ . "etsy_products_list SET "
                                                . "id_etsy_products_list = '', "
                                                . "id_etsy_profiles = '" . (int) $profile['id_etsy_profiles'] . "',"
                                                . "id_product = '" . (int) $categoryProduct['id_product'] . "', "
                                                . "reference = '" . pSQL($categoryProduct['reference']) . "', "
                                                . "delete_track = '0',"
                                                . "date_added = NOW()";
                                            Db::getInstance()->execute($insertSQL);
                                            $productsInserted++;
                                        } else {
                                            $updateSQL = "UPDATE " . _DB_PREFIX_ . "etsy_products_list SET "
                                                . "id_etsy_profiles = '" . (int) $profile['id_etsy_profiles'] . "',"
                                                . "reference = '" . pSQL($categoryProduct['reference']) . "',"
                                                . "delete_track = '0', "
                                                . "is_error = '0'"
                                                . "WHERE id_product = '" . (int) $categoryProduct['id_product'] . "'";
                                            if (Db::getInstance()->execute($updateSQL)) {
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

        //Set delete flag for the products which are not present the the list (OR directly delete from the Db if products are not listed on etsy)
        //Set Profile ID to 0 for such products so that once item is made inactive on etsy (on deleteItemsFromEtsy function execution). Item will be deleted from the table if profile id is blank as its unmapped from the profile
        Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET id_etsy_profiles = 0, delete_flag = '1' WHERE delete_track = '1' AND listing_id IS NOT NULL");

        Db::getInstance()->execute("DELETE FROM " . _DB_PREFIX_ . "etsy_products_list WHERE delete_track = '1' AND listing_id IS NULL");

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
    /**
     * Changes added for product sync on etsy on profile level. Modified the function params to get the profile id
     * @modifier pragya maurya
     * @date 04-06-2024
     * Etsy-enhancement-profile-level
     */
    public static function getProductsToListOnEtsy($limit, $kbproductid = false, $kbprofileid = false)
    {
        /** TODO Add Producy status condtion& join with product table */
        /*
         * @author - Rishabh Jain
         * DOC - 2nd Apr 2020
         * Added stock available condition to avoid products having 0 quantity to sync on etsy
         */
        /*
        * Added the missing alias name for the etsy_product_list table in the delete flad and lisitng status.
        * @date - 31-03-2026
        * @modifier - Manish
        * MPMAR2026 module_specific_issue
        */
        $products_query = "SELECT pl.* FROM " . _DB_PREFIX_ . "etsy_products_list pl "
            . "INNER JOIN " . _DB_PREFIX_ . "product p ON p.id_product = pl.id_product "
            . "WHERE p.active = '1'"
            . "AND pl.delete_flag = '0' "
            . "AND pl.active = '1'"
            . "AND (pl.listing_status = 'Updated' OR pl.listing_status = 'Pending' OR pl.listing_status = 'Relisting')";
        // OR listing_status = 'Sold Out' Removed the Sold_Out status as on product update from the admin panel, We are setting the status to Updated. To avoid the following situation: In case of large number of Sold Out Product, CRON will stuck in exectuing starting 20 Sold Out products each time
        if ($kbproductid) {
            $products_query .= ' AND pl.id_product = ' . (int) $kbproductid;
        }
        /**
         * Changes added for product sync on etsy on profile level. modified the query in case if the profile_id is present
         * @modifier pragya maurya
         * @date 04-06-2024
         * Etsy-enhancement-profile-level
         */ else if ($kbprofileid) {
            $products_query .= ' AND pl.id_etsy_profiles = ' . (int) $kbprofileid;
        } else {
            $products_query .= ' AND is_error = 0';
        }
        $products_query .= ' LIMIT ' . $limit;
        return Db::getInstance()->executeS($products_query, true, false);
    }

    /** To prepare array to listing on etsy */
    public static function prepareArrayToListingOnEtsy($product = array(), $language = '', $updateListing = 0, $renewListing = 0)
    {
        $listingArray = array();
        if (isset($product) && count($product) > 0 && !empty($language)) {
            /*
             * Updated query to include return_policy_id from etsy_return_policy table
             * @modifier Himanshu Vishwakarma
             * @date 15-12-2024
             */
            $profile_details = Db::getInstance()->getRow("SELECT ef.*, ss.shop_section_id, rp.return_policy_id FROM " . _DB_PREFIX_ . "etsy_profiles ef "
                . "LEFT JOIN " . _DB_PREFIX_ . "etsy_shop_section ss "
                . "on (ef.id_etsy_shop_section = ss.id_etsy_shop_section) "
                . "LEFT JOIN " . _DB_PREFIX_ . "etsy_return_policy rp "
                . "on (ef.id_etsy_return_policy = rp.id_etsy_return_policy) "
                . "WHERE id_etsy_profiles = '" . (int) $product['id_etsy_profiles'] . "'", true, false);

            //Get Product Inventory
            $quantity = KbMarketplaceIntegration::getProductInventory($product['id_product']);
            /**
             * Made changes to fix the issue with out-of-stock products.
             * TGoct2023 Out-of-stock-issue
             * @date 12-10-2023
             * @author Tanisha Gupta
             */
            $availibilty = false;
            $pro_obj = new Product($product['id_product']);
            $allow_oosp = $pro_obj->isAvailableWhenOutOfStock(StockAvailable::outOfStock($pro_obj->id));
            //If the Item is available_for_order then only check other conditions otherwise Out of stock.
            //If the Item is having quantity then set it as In stock
            if ($pro_obj->available_for_order) {
                if ($quantity > 0) {
                    // The product is available when quantity is less than or equal to 0
                    $availibilty = true;
                } else if ($allow_oosp == 1) {
                    // The product is available when "allow_oosp" is enabled
                    $availibilty = true;
                }
            }
            // If the product is not available and lisiting id not exist, return $listingArray 
            if (!$availibilty && empty($product['listing_id'])) {
                // If the product is not available, return $listingArray 
                return $listingArray;
            } else if ($quantity <= 0) {
                // Set quantity to '999' when available but with a quantity of 0
                $quantity = '999';
            }
            /*
             * changes over
             */
            $product_details = KbMarketplaceIntegration::getProductByProductId($product['id_product'], $language);

            $tagArray = array();
            $tagTempArray = array();
            $productTags = Tag::getProductTags($product['id_product']);
            /**
             * Added empty condition to fix the error
             * @date 12-04-2023
             * @author Tanisha Gupta
             */
            if (!empty($productTags) && isset($productTags[$language])) {
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
            $profileCategory = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'etsy_category_mapping WHERE id_etsy_profiles = ' . (int) $product['id_etsy_profiles'], true, false);
            $etsy_category = self::getEtsyCategorybyProfileANDCategory($profileCategory, $product_details->id_category_default, $profile_details['etsy_product_type']);

            //Get Shipping Template ID
            $shipping_template_id = Db::getInstance()->getValue("SELECT shipping_template_id FROM " . _DB_PREFIX_ . "etsy_shipping_templates WHERE id_etsy_shipping_templates = '" . (int) $profile_details['id_etsy_shipping_templates'] . "'");

            $lang_data = new Language($language);
            if (!empty($shipping_template_id)) {
                if ($quantity > 999) {
                    $quantity = 999;
                }
                //changes by gopi for alter quantity
                /* Alter quantity logic for product */
                if ($profile_details['alter_quantity'] == "" || $profile_details['alter_quantity'] == 0 || $quantity < $profile_details['alter_quantity']) {
                    $quantity = $quantity;
                } else {
                    $quantity = $profile_details['alter_quantity'];
                }
                //changes by gopi end

                /**
                 * Force default (base) currency to get the price in the base currency
                 * Added this to fix the issue with the price not calculated according to the excange rate.
                 * @modifier Himanshu Vishwakarma
                 * @date 11-12-2025
                 */
                $context = Context::getContext();

                // Force default (base) currency
                $default_currency_id = (int) Configuration::get('PS_CURRENCY_DEFAULT');
                $context->currency = new Currency($default_currency_id);
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


                /**
                 * This code is responsible to check that if the custom product details are present then need to use the description of the same
                 * @modifier Pragya Maurya
                 * @date 26-05-2024
                 * PMMay2024 ebay-custom-product-details
                 */
                $query_get_site = 'SELECT * FROM ' . _DB_PREFIX_ . 'kb_etsy_profile_product_custom_details WHERE profile_product_id = ' . (int) $product['id_etsy_products_list'];
                $strip_tags = array('</p>', '<br />', '<br>', '</div>', '</li>');
                $site_details = Db::getInstance()->getRow($query_get_site);
                if ((!empty($site_details['description']) && $site_details['custom_status'] == '1')) {
                    $desc = str_replace($strip_tags, "\n", $site_details['description']);
                    $description = trim(strip_tags($desc));
                } else {
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
                }

                $short_description = strip_tags(str_replace($strip_tags, "\n", $product_details->description_short));

                /**
                 * This code is responsible to check that if the custom product details are present then need to use the description of the same
                 * @modifier Pragya Maurya
                 * @date 26-05-2024
                 * PMMay2024 ebay-custom-product-details
                 */
                if (!empty($site_details['title']) && $site_details['custom_status'] == '1') {
                    $customize_title = strip_tags($site_details['title']);
                } else {
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
                    'price' => (float) Tools::convertPrice($price, $etsy_currency_id),
                    'is_customizable' => $profile_details['is_customizable'],
                    'taxonomy_id' => $etsy_category,
                    'who_made' => $profile_details['who_made'],
                    'is_supply' => $profile_details['is_supply'],
                    'when_made' => $profile_details['when_made'],
                    'shop_section_id' => $profile_details['shop_section_id'],
                    /*
                     * Added return_policy_id to listing array for product sync
                     * @modifier Himanshu Vishwakarma
                     * @date 15-12-2025
                     */
                    'return_policy_id' => isset($profile_details['return_policy_id']) ? $profile_details['return_policy_id'] : '',
                    'occassion' => $profile_details['occassion'],
                    'should_auto_renew' => $profile_details['should_auto_renew'],
                    'language' => Tools::strtolower($lang_data->iso_code),
                    /**
                     * Changed parameter from shipping_template_id to shipping_profile_id
                     * @date 12-04-2023
                     * @author Tanisha Gupta 
                     */
                    'shipping_profile_id' => $shipping_template_id,
                    'materials' => implode(',', $featureTempArray),
                    'listing_status' => $product['listing_status']
                );




                //changes by gopi for sycing weight and dimension on 23 march 2021
                $dimension_unit = Configuration::get('PS_DIMENSION_UNIT');
                $weight_unit = Configuration::get('PS_WEIGHT_UNIT');
                //only below mentioned units are allowed on etsy
                $etsy_allowed_weight_unit = array('oz', 'lb', 'g', 'kg');
                $etsy_dimension_allowed = array('in', 'ft', 'mm', 'cm', 'm');
                /**
                 * Added condition if length, width, height and item_dimensions_unit should be greater than 0
                 * @date 12-04-2023
                 * @author Tanisha Gupta
                 */
                if ($weight_unit != '' && in_array($weight_unit, $etsy_allowed_weight_unit) && (float) $product_details->weight > 0) {
                    /**
                     * Made changes to refrain from applying a formatting function to the number in order to maintain its original value and avoid changing it to 0.00.
                     * As it gives output for 0.004 as 0.00
                     * TG2023may Remove-Formatting
                     * @date 22-05-2023
                     * @modifier Tanisha Gupta
                     */
                    $listingArray['item_weight'] = (float) $product_details->weight;
                    $listingArray['item_weight_unit'] = $weight_unit;
                }

                if ($dimension_unit != '' && in_array($dimension_unit, $etsy_dimension_allowed)) {
                    if ((float) $product_details->depth > 0) {
                        /**
                         * Made changes to refrain from applying a formatting function to the number in order to maintain its original value and avoid changing it to 0.00.
                         * As it gives output for 0.004 as 0.00
                         * TG2023may Remove-Formatting
                         * @date 22-05-2023
                         * @modifier Tanisha Gupta
                         */
                        $listingArray['item_length'] = (float) $product_details->depth;
                        $listingArray['item_dimensions_unit'] = $dimension_unit;
                    }
                    if ((float) $product_details->width > 0) {
                        /**
                         * Made changes to refrain from applying a formatting function to the number in order to maintain its original value and avoid changing it to 0.00.
                         * As it gives output for 0.004 as 0.00
                         * TG2023may Remove-Formatting
                         * @date 22-05-2023
                         * @modifier Tanisha Gupta
                         */
                        $listingArray['item_width'] = (float) $product_details->width;
                        $listingArray['item_dimensions_unit'] = $dimension_unit;
                    }
                    if ((float) $product_details->height > 0) {
                        /**
                         * Made changes to refrain from applying a formatting function to the number in order to maintain its original value and avoid changing it to 0.00.
                         * As it gives output for 0.004 as 0.00
                         * TG2023may Remove-Formatting
                         * @date 22-05-2023
                         * @modifier Tanisha Gupta
                         */
                        $listingArray['item_height'] = (float) $product_details->height;
                        $listingArray['item_dimensions_unit'] = $dimension_unit;
                    }
                }
                //changes by gopi end here
                if (empty($profile_details['shop_section_id'])) {
                    unset($listingArray['shop_section_id']);
                }

                /*
                 * Added check to unset return_policy_id if empty
                 * @modifier Himanshu Vishwakarma
                 * @date 15-12-2025
                 */
                if (empty($profile_details['return_policy_id'])) {
                    unset($listingArray['return_policy_id']);
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
            /**
             * Fetch shop id to set the same in the request URL
             * @date 13-04-2023
             * @author Tanisha Gupta
             */
            $shop = self::etsyGetShopDetails();
            if (isset($shop['shop_id'])) {
                foreach ($listingArray as $listing) {
                    if (isset($listing['id_product'])) {
                        /* In case of renew & update product */
                        $item_inventory = KbMarketplaceIntegration::getProductInventory($listing['id_product']);
                        /*
                         * changes by rishabh jain
                         */
                        $quantity = $item_inventory;
                        /**
                         * Made changes to fix the issue with out-of-stock products.
                         * TGoct2023 Out-of-stock-issue
                         * @date 12-10-2023
                         * @author Tanisha Gupta
                         */
                        $availibilty = false;
                        $pro_obj = new Product($listing['id_product']);
                        $allow_oosp = $pro_obj->isAvailableWhenOutOfStock(StockAvailable::outOfStock($pro_obj->id));
                        //If the Item is available_for_order then only check other conditions otherwise Out of stock.
                        //If the Item is having quantity then set it as In stock
                        if ($pro_obj->available_for_order) {
                            if ($quantity > 0) {
                                // The product is available when quantity is less than or equal to 0
                                $availibilty = true;
                            } else if ($allow_oosp == 1) {
                                // The product is available when "allow_oosp" is enabled
                                $availibilty = true;
                            }
                        }

                        if (!$availibilty) {
                            // If the product is not available, then set quantity as 0. 
                            $quantity = 0;
                        } else if ($quantity <= 0) {
                            // Set quantity to '999' when available but with a quantity of 0
                            $quantity = '999';
                        }
                        $item_inventory = $quantity;
                        /*
                         * changes over
                         */
                        if (!empty($listing['listing_id'])) {
                            $etsyRequestMethod = 'PUT';
                            $etsyQueryString = $listing;
                            unset($etsyQueryString['property']);
                            unset($etsyQueryString['id_product']);
                            //unset($etsyQueryString['quantity']);
                            unset($etsyQueryString['price']);
                            unset($etsyQueryString['listing_status']);

                            /*
                             * Get readiness_state_id using the new centralized method
                             * This handles both existing and new listings
                             * @date 15-01-2025
                             * @modifier Himanshu Vishwakarma
                             */
                            $readiness_state_id = self::getShopReadinessStateId($shop['shop_id'], $listing['listing_id'], $listing['id_profile']);

                            /*
                             * Add readiness_state_id for physical listings if available
                             * @modifier Himanshu Vishwakarma
                             * @date 15-01-2025
                             */
                            if (!empty($readiness_state_id)) {
                                $etsyQueryString['readiness_state_id'] = $readiness_state_id;
                            }

                            /** Update current status of item by requesting product info from etsy. */
                            $etsyRequestURI = '/listings/' . $listing['listing_id'];
                            $listing_status_data = self::etsyGetResponse($etsyRequestURI, "GET", array());


                            /** In case of sold out, Inventory needs to passed so unsettting Inventory in else condition (If item inventory is zero on Etsy) Otherwise Etsy will return the following error i.e. quantity_cannot_be_empty_,_Invalid_edit_attempted_] */
                            if ($listing_status_data['state'] == 'sold_out') {
                                /**
                                 * Fixes to add the state active param in the API to make the listing active when item is sold out and reslisting again. 
                                 * @modifer Manish
                                 * @date 22-04-2026
                                 * MPAPR2026 sold_out_item_not_resync_issue
                                 */
                               if ($listing['quantity'] > 0) {
                                    $etsyQueryString['renew'] = 1;
                                    $etsyQueryString['state'] = 'active';
                                    Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_status = 'Updated', expiry_date = '" . date("Y-m-d H:i:s", $listing_status_data['ending_timestamp']) . "', sold_flag = '1' WHERE id_product = '" . (int) $listing['id_product'] . "'");
                                } else {
                                    Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_status = 'Inactive', expiry_date = '" . date("Y-m-d H:i:s", $listing_status_data['ending_timestamp']) . "', sold_flag = '1' WHERE id_product = '" . (int) $listing['id_product'] . "'");
                                    continue;
                                }
                            } else {
                                if ($listing_status_data['state'] == 'inactive' || $listing_status_data['state'] == 'edit') {
                                    /* In case renew_flag is not set in the $listing & Item is expired OR Inactive (As per above product status request of etsy), then no need to update the product on the server because without renew flag of the expired/inactive item, relist. In that case, Reset the Update flag in the Db  */
                                } else if ($listing_status_data['state'] == 'expired') {
                                    if (empty($listing['renew_flag'])) {
                                        Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_status = 'Expired', expiry_date = '" . date("Y-m-d H:i:s", $listing_status_data['ending_timestamp']) . "', renew_flag = '0', is_error = '0', listing_error = ''  WHERE id_product = '" . (int) $listing['id_product'] . "'");
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
                            if (date("Y-m-d H:i:s", $listing_status_data['ending_timestamp']) > date("Y-m-d H:i:s") && $listing_status_data['state'] != 'sold_out') {
                                unset($etsyQueryString['renew']);
                            } else {
                                $etsyQueryString['renew'] = 1;
                            }

                            /* Parameter to set status as Sold Out in Db in case item is SOLD OUT */
                            $sold_out = false;
                            if ($item_inventory == 0 && !empty($listing['listing_id'])) {
                                $sold_out = true;
                                /* In case of Sold Out, Set the Status as Inactive on Etsy */
                                $etsyQueryString['state'] = 'inactive';
                            }
                            if (isset($etsyQueryString['listing_id'])) {
                                unset($etsyQueryString['listing_id']);
                            }
                            $etsyRequestURI = '/shops/' . $shop['shop_id'] . '/listings/' . $listing['listing_id'];
                            $etsyRequestMethod = 'PATCH';

                            $etsyQueryData = http_build_query($etsyQueryString);
                            $response = self::etsyGetResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryData);


                            /**
                             * Removed json decode as data will be returned in the array
                             * Send request to the etsyGetResponse method as made changes to use only method to get etsy data
                             * @date 14-03-2023
                             * modifier Tanisha Gupta
                             */

                            /* In case of update/renew, Get listing details & set the price/quantity of the item. In case of variation/normal product, price & quantity will be set.
                             * Variation will be removed & after variation sync, Variation will be listed again.
                             */
                            if (!empty($response) && isset($response['listing_id'])) {
                                $listing_id = $response['listing_id'];
                                $etsyRequestURI = '/listings/' . $listing['listing_id'] . '/inventory';
                                $etsyRequestMethod = 'GET';
                                $etsyQueryString = array(
                                    'listing_id' => $listing['listing_id']
                                );
                                /**
                                 * No need to send data as listing id is appended to the url
                                 * @date 13-04-2023
                                 * @modified Tanisha Gupta
                                 */
                                $listing_response = self::etsyGetResponse($etsyRequestURI, $etsyRequestMethod, array());

                                if (isset($listing_response['products'])) {
                                    $listing_price = isset($listing['price']) ? $listing['price'] : 0;
                                    $listing_quantity = isset($listing['quantity']) ? $listing['quantity'] : 0;
                                    /**
                                     * Unset additional data getting in response
                                     * @date 13-04-2023
                                     * @author Tanisha Gupta
                                     */
                                    unset($listing_response['products'][0]['product_id']);
                                    unset($listing_response['products'][0]['is_deleted']);
                                    unset($listing_response['products'][0]['offerings'][0]['offering_id']);
                                    unset($listing_response['products'][0]['offerings'][0]['is_deleted']);
                                    unset($listing_response['products'][0]['offerings'][0]['price']);
                                    unset($listing_response['products'][0]['offerings'][0]['quantity']);

                                    /*
                                     * Updated this from latest module code regarding scale id and name.
                                     * @modifier Himanshu Vishwakarma
                                     * @date 03-10-2025
                                     */
                                    if (empty($listing_response['products'][0]['property_values'][0]['scale_id'])) {
                                        unset($listing_response['products'][0]['property_values'][0]['scale_id']);
                                        unset($listing_response['products'][0]['property_values'][0]['scale_name']);
                                    }
                                    $listing_response['products'][0]['offerings'][0]['price'] = $listing_price;
                                    $listing_response['products'][0]['offerings'][0]['quantity'] = $listing_quantity;

                                    /*
                                     * Added readiness_state_id to inventory update for existing products
                                     * @date 15-01-2025
                                     * @modifier Himanshu Vishwakarma
                                     */
                                    if (!empty($readiness_state_id)) {
                                        $listing_response['products'][0]['offerings'][0]['readiness_state_id'] = $readiness_state_id;
                                    }
                                    $etsyQueryString = array(
                                        'products' => $listing_response['products']
                                    );
                                    $etsyQueryString = json_encode($etsyQueryString);


                                    $etsyRequestMethod = 'PUT';
                                    $updateInventoryResult = self::etsyGetResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString, 'JSON');
                                    if (isset($updateInventoryResult['error'])) {
                                        $listingError = $updateInventoryResult['error'];
                                        Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET is_error = 1, listing_error = '" . pSQL($listingError) . "' WHERE id_product = '" . (int) $listing['id_product'] . "' AND id_product_attribute = '0'");
                                    }
                                }
                                if ($sold_out == true) {
                                    Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_id = '" . (int) $listing_id . "', expiry_date = '" . pSQL(date("Y-m-d H:i:s", $response['ending_timestamp'])) . "', listing_status = 'Inactive', sold_flag = '1', renew_flag = '0', date_last_renewed = NOW(), listing_error = '' WHERE id_product = " . (int) $listing['id_product']);
                                } else {
                                    $listing_status = 'Listed';
                                    if ($response['state'] == 'expired') {
                                        $listing_status = 'Expired';
                                    } else if ($response['state'] == 'edit') {
                                        $listing_status = 'Inactive';
                                    }
                                    if (!empty($listing['renew_flag'])) {
                                        $listingsRenewed++;
                                        Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_id = '" . (int) $listing_id . "', expiry_date = '" . pSQL(date("Y-m-d H:i:s", $response['ending_timestamp'])) . "', listing_status = '" . $listing_status . "', renew_flag = '0', sold_flag = '0', date_last_renewed = NOW(), listing_error = '' WHERE id_product = " . (int) $listing['id_product']);
                                    } else {
                                        $listingsUpdated++;
                                        Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_status = '" . $listing_status . "', expiry_date = '" . pSQL(date("Y-m-d H:i:s", $response['ending_timestamp'])) . "', listing_error = '' WHERE id_product = '" . (int) $listing['id_product'] . "'");
                                    }
                                }
                            }
                        } else {
                            /* In case of new item, If inventory is zero then product will not be synced */
                            if ($item_inventory == 0) {
                                /* Changes done by Manish to mark the item diabled when the listing id not exist and the item inventory is zero so the cron wont pick up the same item in the next cron execution.
                                * @modifier Manish
                                * @dare 08-04-2026
                                * MPAPR2026 cron_pick_same_item_issue
                                */
                                Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET active = '0' WHERE id_product = '" . (int) $listing['id_product'] . "'");
                                continue;
                            }
                            /* Create new product on etsy */

                            $etsyRequestURI = '/shops/' . $shop['shop_id'] . '/listings';
                            $etsyRequestMethod = 'POST';
                            $etsyQueryString = $listing;
                            unset($etsyQueryString['id_product']);
                            unset($etsyQueryString['id_profile']);

                            /*
                             * Get readiness_state_id for new listing creation
                             * @date 15-01-2025
                             * @modifier Himanshu Vishwakarma
                             */
                            $readiness_state_id = self::getShopReadinessStateId($shop['shop_id'], null, $listing['id_profile']);
                            /*
                             * Add readiness_state_id for physical listings if available
                             * @modifier Himanshu Vishwakarma
                             * 27-12-2024
                             */
                            if (!empty($readiness_state_id)) {
                                $etsyQueryString['readiness_state_id'] = $readiness_state_id;
                            }
                            /**
                             * Changed data array to the query parameter
                             * @date 12-04-2023
                             * @author Tanisha Gupta
                             */

                            $etsyQueryStringData = http_build_query($etsyQueryString);

                            /**
                             * Sync product listing on Etsy and also sync listing information of product. Once all informations are listed on etsy, then update listing status as Active
                             * @date 13-04-2023
                             * @author Tanisha Gupta
                             */
                            $response = self::etsyGetResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryStringData);
                        }



                        if (!empty($response) && isset($response['listing_id'])) {
                            $listing_id = $response['listing_id'];
                            if (!empty($listing_id)) {

                                /* State is set in case of inactive only. In Item is not being set to Inactive, Then sync product other data as well, In case of Inactive, No need to sync other Info */
                                if (empty($etsyQueryString['state'])) {
                                    /* Update the Etsy Category Attributes */
                                    /*
                                     * Send Shop id, $listing['id_product'] and $listing['id_profile'] parameters.
                                     * @date 13-04-2023
                                     * @author Tanisha Gupta
                                     */
                                    self::syncEtsyAttribute($listing['id_product'], $listing_id, $listing['id_profile'], $shop['shop_id']);

                                    self::updateListingVariation($listing['id_product'], $listing_id, $langauge_id, $listing['id_profile'], $shop['shop_id']);
                                    self::etsySyncTranslation($listing['id_product'], $listing_id, $listing['id_profile'], $shop['shop_id']);
                                    self::etsyImageListings($listing['id_product'], $listing_id, $langauge_id, $shop['shop_id']);
                                    self::etsySyncDownloadFile($listing['id_product'], $listing_id, $listing['id_profile'], $shop['shop_id']);
                                    /**
                                     * Changes added to sync the images of the variations on etsy
                                     * @modifier Pragya Maurya
                                     * @date 12-06-2024
                                     * PMJune2024 etsy-variation-images
                                     */
                                    self::etsySyncVariationsImages($listing['id_product'], $listing_id, $langauge_id, $shop['shop_id']);
                                    /* If listing id was not set in the Original Array, then active the product as etsy creates the listing as draft*/
                                    if (empty($listing['listing_id'])) {

                                        unset($etsyQueryString['price']);
                                        $etsyRequestURI = '/shops/' . $shop['shop_id'] . '/listings/' . $listing_id;
                                        $etsyQueryString['state'] = 'active';
                                        $listingdata = http_build_query($etsyQueryString);
                                        $etsyRequestMethod = "PATCH";

                                        $resultdata = self::etsyGetResponse($etsyRequestURI, $etsyRequestMethod, $listingdata);

                                        if (isset($resultdata['listing_id'])) {
                                            if ($resultdata['state'] == "active") {
                                                $listingsCreated++;
                                                Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_id = '" . (int) $listing_id . "', listing_status = 'Listed', expiry_date = '" . date("Y-m-d H:i:s", $response['ending_timestamp']) . "', date_listed = NOW(), listing_error = '' WHERE id_product = '" . (int) $listing['id_product'] . "' AND id_product_attribute = '0'");
                                            }
                                        } else {
                                            $listingError = $resultdata['error'];
                                            Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET is_error = 1, listing_error = '" . pSQL($listingError) . "' WHERE id_product = '" . (int) $listing['id_product'] . "' AND id_product_attribute = '0'");
                                        }
                                    }
                                }
                            }
                        } else {
                            if (isset($response['error'])) {
                                $listingError = $response['error'];
                                Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET is_error = 1, listing_error = '" . pSQL($listingError) . "' WHERE id_product = '" . (int) $listing['id_product'] . "' AND id_product_attribute = '0'");
                            } else if (isset($response[0]['message']) && isset($response[0]['path'])) {
                                $listingError = $response[0]['message'] . " " . $response[0]['path'];
                                Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET is_error = 1, listing_error = '" . pSQL($listingError) . "' WHERE id_product = '" . (int) $listing['id_product'] . "' AND id_product_attribute = '0'");
                            }
                        }
                        sleep(1);
                    }
                }
            }
        }
        self::auditLogEntry('Job execution completed to list/update items on Etsy. <br>Total Listings Created: ' . $listingsCreated, $method_name);
        return true;
    }


    /**
     * Changes added to sync the images of the variations on etsy
     * @modifier Pragya Maurya
     * @date 12-06-2024
     * PMJune2024 etsy-variation-images
     */
    public static function etsySyncVariationsImages($listing_id_product, $listing_id, $langauge_id, $shop_id)
    {
        $listing_id = $listing_id;
        $etsyRequestURI = '/listings/' . $listing_id . '/inventory';
        $etsyRequestMethod = 'GET';
        $etsyQueryString = array(
            'listing_id' => $listing_id
        );

        $listing_response = self::etsyGetResponse($etsyRequestURI, $etsyRequestMethod, array());
        $variation_images = array();
        foreach ($listing_response['products'] as $product) {
            $i = 0;
            $id_product_attribute = array();

            foreach ($product['property_values'] as $prop_values) {
                /**
                 * Fetching the attribute id from the mapping table based on listing id, as ids 500-512 have been removed from the mapping table
                 * @modifier Himanshu Vishwakarma
                 * @date 16-10-2025
                 */
                $etsyattributesmaps = Db::getInstance()->executeS("SELECT * FROM " . _DB_PREFIX_ . "etsy_attribute_mapping1 am1 WHERE custom_property_id = '" . (int) $prop_values['property_id'] . "' AND listing_id = " . (int) $listing_id);
                if (empty($etsyattributesmaps)) {
                    $etsyattributes = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'etsy_attributes WHERE etsy_property_id = ' . (int) $prop_values['property_id']);
                    $etsyattributesmaps = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'etsy_attribute_mapping1 WHERE property_id = ' . (int) $etsyattributes[0]['attribute_id']);
                }
                $sql = "SELECT distinct(ppa.id_product_attribute), al.name FROM " . _DB_PREFIX_ . "product_attribute ppa LEFT JOIN " . _DB_PREFIX_ . "product_attribute_combination pac ON ppa.id_product_attribute = pac.id_product_attribute LEFT JOIN " . _DB_PREFIX_ . "attribute_lang al ON pac.id_attribute = al.id_attribute LEFT JOIN " . _DB_PREFIX_ . "attribute a ON a.id_attribute = al.id_attribute AND al.name = '" . pSQL($prop_values['values'][0]) . "' WHERE a.id_attribute_group = '" . (int) $etsyattributesmaps[0]['id_attribute_group'] . "' AND ppa.id_product = " . (int) $listing_id_product;
                $product_attributes = Db::getInstance()->executeS($sql);

                $id_product_attribute[$i] = $product_attributes;

                $prop_value_id = $prop_values['value_ids'][0];
                $property_id = $prop_values['property_id'];
                $i++;
            }

            // Extract id_product_attribute values
            //If product is having having two variations OR single variation.
            $common_id_product_attributes = array();
            if (!empty($id_product_attribute[1])) {
                $id_product_attributes_1 = array_column($id_product_attribute[0], 'id_product_attribute');
                $id_product_attributes_2 = array_column($id_product_attribute[1], 'id_product_attribute');

                // Find the intersection
                $common_id_product_attributes = array_intersect($id_product_attributes_1, $id_product_attributes_2);
            } else {
                /**
                 * Start changes added for fixing the noticewe get for undefined index for 0
                 * @modifier Pragya Maurya
                 * @date 18-10-2024
                 * PMOct2024 etsy-variation-images
                 */
                if (!empty($id_product_attribute) && isset($id_product_attribute[0])) {
                    $common_id_product_attributes = array_column($id_product_attribute[0], 'id_product_attribute');
                }
            }

            //Reset Array Index as common values can be on either 0 OR 1 etc index. 
            $common_id_product_attributes = array_values($common_id_product_attributes);

            //If multiple attributes is coming the pick pone
            if (is_array($common_id_product_attributes) && !empty($common_id_product_attributes[0])) {
                $common_id_product_attribute = $common_id_product_attributes[0];
            } else {
                $common_id_product_attribute = $common_id_product_attributes;
            }

            $arr['property_values'] = '';
            $sql_image_id = "SELECT id_image FROM " . _DB_PREFIX_ . "product_attribute_image WHERE id_product_attribute = " . (int) $common_id_product_attribute;

            $attribute_image_id = Db::getInstance()->executeS($sql_image_id);

            if (!empty($attribute_image_id)) {

                $sql = "SELECT etsy_image_id FROM " . _DB_PREFIX_ . "etsy_images ei LEFT JOIN " . _DB_PREFIX_ . "image i ON (ei.ps_image_id = i.id_image and ei.product_id = i.id_product) WHERE ei.product_id = '" . (int) $listing_id_product . "' and ei.ps_image_id = " . (int) $attribute_image_id[0]['id_image'];
                $etsy_image_id = Db::getInstance()->executeS($sql);

                //Index to create the variation images unqiue
                $primary_index = $property_id . "_" . $prop_value_id . "_" . $etsy_image_id[0]['etsy_image_id'];
                $variation_images[$primary_index] = array(
                    'property_id' => (int) $property_id,
                    'value_id' => $prop_value_id,
                    'image_id' => $etsy_image_id[0]['etsy_image_id']
                );
            }
        }

        if (!empty($variation_images)) {
            //Reset Index on the Variatin images
            $variation_images = array_values($variation_images);
            $etsyRequestURI = '/shops/' . $shop_id . '/listings/' . $listing_id . '/variation-images';
            $etsyRequestMethod = 'POST';
            $etsyQueryString = array(
                'variation_images' => $variation_images
            );
            $etsyQueryString = http_build_query($etsyQueryString);
            $image_list_response = self::etsyGetResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString);
        }
    }


    /** Function to sync selected etsy attribute on the Etsy 
     * Added product_id and profile_id parameter to update the product listing details
     * @date 15-04-2023
     * @modifier Tanisha Gupta
     */
    public static function syncEtsyAttribute($product_id, $listing_id, $profile_id, $shopid)
    {
        /**
         * Changed conditions in where clause as listing id will be saved once all information update on etsy
         * Updated error based on the profile and product id
         * @date 15-04-2023
         * @modifier Tanisha Gupta
         */
        //$etsyAttributes = Db::getInstance()->executeS("SELECT eam.* FROM `" . _DB_PREFIX_ . "etsy_products_list` pl INNER JOIN `" . _DB_PREFIX_ . "etsy_attribute_mapping` eam ON pl.id_etsy_profiles = eam.id_etsy_profiles WHERE listing_id = '" . pSQL($listing_id) . "' AND id_product_attribute = '0'");
        $etsyAttributes = Db::getInstance()->executeS("SELECT eam.* FROM `" . _DB_PREFIX_ . "etsy_products_list` pl INNER JOIN `" . _DB_PREFIX_ . "etsy_attribute_mapping` eam ON pl.id_etsy_profiles = eam.id_etsy_profiles WHERE pl.id_product = '" . (int) $product_id . "' AND pl.id_etsy_profiles = '" . (int) $profile_id . "' AND id_product_attribute = '0'");
        if (!empty($etsyAttributes)) {
            foreach ($etsyAttributes as $etsyAttribute) {
                if ($etsyAttribute['id_attribute_group'] != "") {
                    /**
                     *Set Url to send sync attributes
                     *@date 13-04-2023
                     *@author Tanisha Gupta
                     */
                    $etsyRequestURI = '/shops/' . $shopid . '/listings/' . $listing_id . '/properties/' . $etsyAttribute['property_id'];
                    $etsyRequestMethod = 'PUT';
                    $etsyQueryString = array("value_ids" => explode(",", $etsyAttribute['id_attribute_group']), "values" => explode(",", $etsyAttribute['id_attribute_value']));
                    $etsyQueryString = http_build_query($etsyQueryString);
                    self::etsyGetResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString);
                }
            }
        }
        return true;
    }

    /**
     * Get readiness_state_id from existing shop listings or create new one
     * @param string $shop_id
     * @param string $listing_id (optional, for new listings)
     * @param int $profile_id (required to fetch readiness_state and processing times)
     * @return string|null
     * @date 15-01-2025
     * @modifier Himanshu Vishwakarma
     */
    public static function getShopReadinessStateId(
        $shop_id,
        $listing_id = null,
        $profile_id = null
    ) {
        try {
            /*
             * Fetch profile data to determine readiness_state and processing times
             * @date 15-01-2025
             * @modifier Himanshu Vishwakarma
             */
            $readiness_state = 'ready_to_ship';
            $min_processing_time = 0;
            $max_processing_time = 0;

            if (!empty($profile_id)) {
                // Fetch profile details to get when_made field
                $profile_details = EtsyProfiles::getProfileDetails($profile_id, 'when_made, id_etsy_shipping_templates');

                if (!empty($profile_details) && isset($profile_details[0]['when_made'])) {
                    // Set readiness_state based on when_made field
                    if ($profile_details[0]['when_made'] === 'made_to_order') {
                        $readiness_state = 'made_to_order';
                    } else {
                        $readiness_state = 'ready_to_ship';
                    }

                    // Fetch processing times from shipping template
                    if (!empty($profile_details[0]['id_etsy_shipping_templates'])) {
                        $shipping_template_details = EtsyShippingTemplates::getShippingTemplateDetails(
                            $profile_details[0]['id_etsy_shipping_templates'],
                            'shipping_min_process_days, shipping_max_process_days'
                        );

                        if (!empty($shipping_template_details) && isset($shipping_template_details[0])) {
                            $min_processing_time = (int) $shipping_template_details[0]['shipping_min_process_days'];
                            $max_processing_time = (int) $shipping_template_details[0]['shipping_max_process_days'];
                        }
                    }
                }
            }

            // 1️ If listing ID exists, fetch directly from GET /application/listings/{listing_id}?includes=Inventory
            if (!empty($listing_id)) {
                $listingURI = '/listings/' . (int) $listing_id . '?includes=Inventory';
                $listingResponse = self::etsyGetResponse($listingURI, 'GET', []);

                if (
                    !empty($listingResponse)
                    && isset($listingResponse['readiness_state_id'])
                    && (int) $listingResponse['readiness_state_id'] > 0
                ) {
                    return (int) $listingResponse['readiness_state_id'];
                }
            }

            // 2️ No listing_id or readiness not found → GET /shops/{shop_id}/readiness-state-definitions
            $definitionsURI = '/shops/' . (int) $shop_id . '/readiness-state-definitions?limit=100';
            $definitionsResponse = self::etsyGetResponse($definitionsURI, 'GET', []);

            if (!empty($definitionsResponse['results']) && is_array($definitionsResponse['results'])) {
                foreach ($definitionsResponse['results'] as $item) {
                    if (
                        isset($item['readiness_state'], $item['readiness_state_id'])
                        && $item['readiness_state'] === $readiness_state
                    ) {
                        return (int) $item['readiness_state_id'];
                    }
                }
            }

            // 3️ Still nothing → create via POST /shops/{shop_id}/readiness-state-definitions
            $createURI = '/shops/' . (int) $shop_id . '/readiness-state-definitions';
            $payload = [
                'readiness_state' => $readiness_state,
                'min_processing_time' => (int) $min_processing_time == 0 ? 1 : (int) $min_processing_time,
                'max_processing_time' => (int) $max_processing_time == 0 ? 1 : (int) $max_processing_time,
            ];

            $createResponse = self::etsyGetResponse($createURI, 'POST', http_build_query($payload));
            if (
                !empty($createResponse)
                && isset($createResponse['readiness_state_id'])
                && (int) $createResponse['readiness_state_id'] > 0
            ) {
                return (int) $createResponse['readiness_state_id'];
            }

            return null; // fallback if all fail

        } catch (Exception $e) {
            return null;
        }
    }


    public static function updateListingVariation($product_id, $listing_id, $language_id, $profile_id, $shopid)
    {
        $method_name = 'EtsyModule:updateListingVariation';
        $listingArray = array();
        $product = new Product($product_id, false, $language_id);

        $etsy_currency_id = Currency::getIdByIsoCode(Configuration::get('etsy_currency'), Context::getContext()->shop->id);

        if (!empty($product) && $product->hasAttributes()) {
            self::auditLogEntry('Job execution started to list the variation on Etsy', $method_name);

            $attributes = $product->getAttributeCombinations($language_id);
            if (!empty($attributes)) {

                /*
                 * Get readiness_state_id using the new centralized method
                 * This handles both existing and new listings
                 * Updated to pass profile_id to fetch readiness_state and processing times
                 * @date 15-01-2025
                 * @modifier Himanshu Vishwakarma
                 */
                $readiness_state_id = self::getShopReadinessStateId($shopid, $listing_id, $profile_id);

                /**
                 * Assigning 513 or 514 id based upon already existing property id in the mapping table
                 * @modifier Himanshu Vishwakarma
                 * @date 16-10-2025
                 */
                $alreadyAssignedProperty = Db::getInstance()->executeS("SELECT * FROM " . _DB_PREFIX_ . "etsy_attribute_mapping1 am1 WHERE listing_id = " . (int) $listing_id);
                $starting_property_id = 513;
                //Checking already assigned propoerty to lisitng ID and avalible propeerty ID from 513 OR 514.   
                if (!empty($alreadyAssignedProperty)) {
                    foreach ($alreadyAssignedProperty as $alreadyAssigned) {
                        if ($alreadyAssigned['custom_property_id'] == "513") {
                            $starting_property_id = 514;
                        } else if ($alreadyAssigned['custom_property_id'] == "514") {
                            $starting_property_id = 513;
                        }
                    }
                }

                $id_attribute_group = array();
                foreach ($attributes as $attribute) {
                    if (!in_array($attribute['id_attribute_group'], $id_attribute_group)) {
                        $id_attribute_group[$attribute['id_attribute_group']] = $attribute['id_attribute_group'];
                    }
                }


                foreach ($attributes as $attribute) {
                    $propertyDetail = Db::getInstance()->getRow("SELECT * FROM " . _DB_PREFIX_ . "etsy_attribute_mapping1 am1 INNER JOIN " . _DB_PREFIX_ . "etsy_attributes ea ON ea.attribute_id = am1.property_id WHERE am1.id_attribute_group = '" . (int) $attribute['id_attribute_group'] . "'");
                    if (!empty($propertyDetail)) {
                        /**
                         * Get Attribute Name
                         * Added PS vrsion condition as Attibute class has been renamed to ProductAttribute in PS 8
                         * @date 15-04-2023
                         * @modifier Tanisha Gupta
                         */
                        if (_PS_VERSION_ >= '8.0.0') {
                            $attribute_details = new ProductAttribute($attribute['id_attribute'], $language_id);
                        } else {
                            $attribute_details = new Attribute($attribute['id_attribute'], $language_id);
                        }
                        $attributeAvailability = KbMarketplaceIntegration::getInventoryByProductAttributeId($attribute['id_product'], $attribute['id_product_attribute']);
                        $productPricewithAttribute = Product::getPriceStatic($product->id, true, $attribute['id_product_attribute'], 6, null, false, true);

                        $profileDetails = Db::getInstance()->getRow("SELECT * FROM " . _DB_PREFIX_ . "etsy_profiles WHERE id_etsy_profiles = '" . (int) $profile_id . "'");

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
                    } else {
                        //Only 2 propert id can be associated with each Listing. 513 and 514 only.

                        if (count($id_attribute_group) <= 2) {
                            $propertyDetailCheck = Db::getInstance()->getRow("SELECT * FROM " . _DB_PREFIX_ . "etsy_attribute_mapping1 am1 WHERE am1.id_attribute_group = '" . (int) $attribute['id_attribute_group'] . "' AND listing_id = " . (int) $listing_id);
                            if (empty($propertyDetailCheck)) {
                                Db::getInstance()->query("INSERT INTO " . _DB_PREFIX_ . "etsy_attribute_mapping1(custom_property_id, listing_id, property_title, id_attribute_group, date_added) VALUES('" . (int) $starting_property_id . "', '" . (int) $listing_id . "', '" . pSQL($attribute['group_name']) . "', '" . (int) $attribute['id_attribute_group'] . "', '" . date("Y-m-d H:i:s") . "')");
                                $propertyDetailCheck = Db::getInstance()->getRow("SELECT * FROM " . _DB_PREFIX_ . "etsy_attribute_mapping1 am1 WHERE am1.id_attribute_group = '" . (int) $attribute['id_attribute_group'] . "' AND listing_id = " . (int) $listing_id);
                                $starting_property_id = 514; // Next Property ID will be 514.
                            } else {
                                //If propery Id is already added in the database then check if its 513 OR 514. Next will be alternative of the same.
                                $starting_property_id = $propertyDetailCheck['custom_property_id'] == 513 ? 514 : 513;
                            }

                            if (_PS_VERSION_ >= '8.0.0') {
                                $attribute_details = new ProductAttribute($attribute['id_attribute'], $language_id);
                            } else {
                                $attribute_details = new Attribute($attribute['id_attribute'], $language_id);
                            }
                            $attributeAvailability = KbMarketplaceIntegration::getInventoryByProductAttributeId($attribute['id_product'], $attribute['id_product_attribute']);
                            $productPricewithAttribute = Product::getPriceStatic($product->id, true, $attribute['id_product_attribute'], 6, null, false, true);

                            $profileDetails = Db::getInstance()->getRow("SELECT * FROM " . _DB_PREFIX_ . "etsy_profiles WHERE id_etsy_profiles = '" . (int) $profile_id . "'");

                            $listingArray[] = array(
                                'listing_id' => $listing_id,
                                'property_id' => $propertyDetailCheck['custom_property_id'],
                                'value' => $attribute_details->name,
                                'name' => $propertyDetailCheck['property_title'],
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
                //changes by gopi forater quantity
                $alter_quantity = 0;
                $profileDetails_alter = Db::getInstance()->getRow("SELECT * FROM " . _DB_PREFIX_ . "etsy_profiles WHERE id_etsy_profiles = '" . (int) $profile_id . "'");
                $alter_quantity = $profileDetails_alter['alter_quantity'];
                //changes by gopi end
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
                     *$properties Array with details like Size, Color & Property code
                     *$variation_propery list of property code like 100, 200 etc associated with the product
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
                                $product_attr_id = self::getVariationIdByPropertyValue($combination, $product->id, $listing_id);
                            }

                            if ($product_attr_id != '') {
                                $attributes = $product->getAttributeCombinationsById($product_attr_id, $language_id);
                                $sku = $attributes[0]['reference'];
                                $productInventory = KbMarketplaceIntegration::getProductInventory($product_id, $product_attr_id);
                                $context = Context::getContext();

                                // Force default (base) currency
                                $default_currency_id = (int) Configuration::get('PS_CURRENCY_DEFAULT');
                                $context->currency = new Currency($default_currency_id);
                                $price = Product::getPriceStatic($product_id, true, $product_attr_id, 6, null, false, true);
                                $price = Tools::convertPrice($price, $etsy_currency_id);
                            } else {
                                /* In case, combination doesn't exist in the Db. Set Quantity as 0 */
                                $productInventory = 0;
                                $context = Context::getContext();

                                // Force default (base) currency
                                $default_currency_id = (int) Configuration::get('PS_CURRENCY_DEFAULT');
                                $context->currency = new Currency($default_currency_id);
                                $price = Product::getPriceStatic($product_id, true, null, 6, null, false, true);
                                $price = Tools::convertPrice($price, $etsy_currency_id);
                            }
                            if ($productInventory > 999) {
                                $quantity = 999;
                            } else {
                                $quantity = $productInventory;
                                /**
                                 * Made changes to fix the issue with out-of-stock products.
                                 * TGoct2023 Out-of-stock-issue
                                 * @date 12-10-2023
                                 * @author Tanisha Gupta
                                 */
                                $availibilty = false;
                                $pro_obj = new Product($product_id);
                                $allow_oosp = $pro_obj->isAvailableWhenOutOfStock(StockAvailable::outOfStock($pro_obj->id));
                                //If the Item is available_for_order then only check other conditions otherwise Out of stock.
                                //If the Item is having quantity then set it as In stock
                                if ($pro_obj->available_for_order) {
                                    if ($quantity > 0) {
                                        // The product is available when quantity is less than or equal to 0
                                        $availibilty = true;
                                    } else if ($allow_oosp == 1) {
                                        // The product is available when "allow_oosp" is enabled
                                        $availibilty = true;
                                    }
                                }
                                if (!$availibilty) {
                                    // If the product is not available, then set quantity as 0. 
                                    $quantity = 0;
                                } else if ($quantity <= 0) {
                                    // Set quantity to '999' when available but with a quantity of 0
                                    $quantity = '999';
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
                            //changes by gopi start
                            /* Alter quantity logic for product */
                            if ($alter_quantity == "" || $alter_quantity == 0 || $quantity < $alter_quantity) {
                                $quantity = $quantity;
                            } else {
                                $quantity = $alter_quantity;
                            }
                            //changes by gopi end
                            $products[$generated_key]['property_values'] = $tempArray;
                            if ($sku == '') {
                                $products[$generated_key]['sku'] = "SKU_" . $product_id . "_" . $product_attr_id;
                            } else {
                                $products[$generated_key]['sku'] = $sku;
                            }
                            if (!empty($readiness_state_id)) {
                                $products[$generated_key]['offerings'] = array(
                                    array(
                                        'price' => $price,
                                        'quantity' => $quantity,
                                        'is_enabled' => 1,
                                        'readiness_state_id' => $readiness_state_id
                                    )
                                );
                            } else {
                                $products[$generated_key]['offerings'] = array(
                                    array(
                                        'price' => $price,
                                        'quantity' => $quantity,
                                        'is_enabled' => 1
                                    )
                                );
                            }
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
                                $product_attr_id = self::getVariationIdByPropertyValue($combination, $product->id, $listing_id);
                            }
                            if ($product_attr_id != '') {
                                $attributes = $product->getAttributeCombinationsById($product_attr_id, $language_id);
                                $sku = $attributes[0]['reference'];
                                $productInventory = KbMarketplaceIntegration::getProductInventory($product_id, $product_attr_id);
                                $context = Context::getContext();

                                // Force default (base) currency
                                $default_currency_id = (int) Configuration::get('PS_CURRENCY_DEFAULT');
                                $context->currency = new Currency($default_currency_id);
                                $price = Product::getPriceStatic($product_id, true, $product_attr_id, 6, null, false, true);
                                $price = Tools::convertPrice($price, $etsy_currency_id);
                            } else {
                                /* In case, combination doesn't exist in the Db. Set Quantity as 0 */
                                $productInventory = 0;
                                $context = Context::getContext();

                                // Force default (base) currency
                                $default_currency_id = (int) Configuration::get('PS_CURRENCY_DEFAULT');
                                $context->currency = new Currency($default_currency_id);
                                $price = Product::getPriceStatic($product_id, true, null, 6, null, false, true);
                                $price = Tools::convertPrice($price, $etsy_currency_id);
                            }

                            if ($productInventory > 999) {
                                $quantity = 999;
                            } else {
                                $quantity = $productInventory;
                                /**
                                 * Made changes to fix the issue with out-of-stock products.
                                 * TGoct2023 Out-of-stock-issue
                                 * @date 12-10-2023
                                 * @author Tanisha Gupta
                                 */
                                $availibilty = false;
                                $pro_obj = new Product($product_id);
                                $allow_oosp = $pro_obj->isAvailableWhenOutOfStock(StockAvailable::outOfStock($pro_obj->id));
                                //If the Item is available_for_order then only check other conditions otherwise Out of stock.
                                //If the Item is having quantity then set it as In stock
                                if ($pro_obj->available_for_order) {
                                    if ($quantity > 0) {
                                        // The product is available when quantity is less than or equal to 0
                                        $availibilty = true;
                                    } else if ($allow_oosp == 1) {
                                        // The product is available when "allow_oosp" is enabled
                                        $availibilty = true;
                                    }
                                }
                                if (!$availibilty) {
                                    // If the product is not available, then set quantity as 0. 
                                    $quantity = 0;
                                } else if ($quantity <= 0) {
                                    // Set quantity to '999' when available but with a quantity of 0
                                    $quantity = '999';
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
                            //changes by gopi start
                            /* Alter quantity logic for product */
                            if ($alter_quantity == "" || $alter_quantity == 0 || $quantity < $alter_quantity) {
                                $quantity = $quantity;
                            } else {
                                $quantity = $alter_quantity;
                            }


                            //changes by gopi end
                            /*
                             * Added readiness_state_id to offerings for products without variations
                             * @modifier Himanshu Vishwakarma
                             * @date 13-10-2025
                             */
                            if (!empty($readiness_state_id)) {
                                $products[$generated_key]['offerings'] = array(
                                    array(
                                        'price' => $price,
                                        'quantity' => $quantity,
                                        'is_enabled' => 1,
                                        'readiness_state_id' => $readiness_state_id
                                    )
                                );
                            } else {
                                $products[$generated_key]['offerings'] = array(
                                    array(
                                        'price' => $price,
                                        'quantity' => $quantity,
                                        'is_enabled' => 1
                                    )
                                );
                            }
                            $k++;
                            $generated_key++;
                        }
                    }




                    $etsyQueryString = array(
                        /**
                         * No need to encode json as later needs to encode complete array in json
                         * @date 13-04-2023
                         * @author Tanisha Gupta
                         */
                        'products' => $products,
                        'price_on_property' => implode(',', $variation_propery),
                        'quantity_on_property' => implode(',', $variation_propery),
                        'sku_on_property' => implode(',', $variation_propery),
                    );


                    $etsyRequestURI = '/listings/' . $listing_id . '/inventory';
                    $etsyRequestMethod = 'PUT';
                    /**
                     * decode data and changed function to send request
                     * @date 13-04-2023
                     * @modifier Tanisha Gupta
                     */
                    $etsyQueryStringData = json_encode($etsyQueryString);
                    $etsyContentType = 'JSON';
                    $response = self::etsyGetResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryStringData, $etsyContentType);

                    if (!empty($response) && isset($response['products'])) {
                        /* Nothing needs to be done if variation updated successfully */
                    } else if (!empty($response) && isset($response['error'])) {
                        $listingError = $response['error'];
                        Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET is_error = 1, listing_error = '" . pSQL($listingError) . "' WHERE id_product = '" . (int) $product_id . "'");
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
    public static function etsyImageListings($product_id, $listing_id, $language_id, $shopid)
    {
        $method_name = 'EtsyModule::etsyImageListings()';
        self::auditLogEntry('Job execution started to list images on Etsy Marketplace.', $method_name);

        /*
         * Added check to prevent duplicate image processing
         * Fixed duplicate image issue during product revision and sync
         * @date 15-01-2025
         * @modifier Himanshu Vishwakarma
         */
        static $processed_products = array();
        $product_key = $product_id . '_' . $listing_id;

        self::auditLogEntry('Checking duplicate processing for product key: ' . $product_key . ', Processed products: ' . json_encode(array_keys($processed_products)), $method_name);

        if (isset($processed_products[$product_key])) {
            self::auditLogEntry('Product images already processed, skipping duplicate processing for product ' . $product_id, $method_name);
            return true;
        }

        $processed_products[$product_key] = true;
        self::auditLogEntry('Marking product as processed: ' . $product_key, $method_name);
        /**
         * Made changes to delete product images first(which are not longer exists on PS)
         * TG2023may Images-Issue
         * @date 22-05-2023
         * @author Ashish Kumar
         * @commenter Tanisha Gupta 
         */
        self::deleteAlreadyDeletedImages($product_id, $listing_id, $language_id, $shopid);

        $imagesListed = 0;
        /**
         * Modified below code to pass shop id as parameter
         * TG2023may Images-Issue
         * @date 22-05-2023
         * @author Ashish Kumar
         * @commenter Tanisha Gupta 
         */

        $etsyRequestURI = '/listings/' . $listing_id . '/images';
        $response = self::etsyGetResponse($etsyRequestURI, 'GET', array());

        if (!empty($response['results'])) {
            $existing_etsy_images = Db::getInstance()->executeS("SELECT etsy_image_id FROM " . _DB_PREFIX_ . "etsy_images WHERE product_id = '" . (int) $product_id . "' AND etsy_image_id > 0");

            foreach ($response['results'] as $result) {
                $found = false;
                foreach ($existing_etsy_images as $existing_image) {
                    if ($existing_image['etsy_image_id'] == $result['listing_image_id']) {
                        $found = true;
                        break;
                    }
                }
                //Delete the image from Etsy if not found in the existing images
                if (!$found) {
                    $etsyRequestURI = '/shops/' . $shopid . '/listings/' . $listing_id . '/images/' . $result['listing_image_id'];
                    $response = self::etsyGetResponse($etsyRequestURI, 'DELETE', array());
                }
            }
        }


        self::auditLogEntry('Calling prepareArrayToUploadImageOnEtsy for product ' . $product_id, $method_name);
        $images = self::prepareArrayToUploadImageOnEtsy($product_id, $listing_id, $language_id, $shopid);
        self::auditLogEntry('prepareArrayToUploadImageOnEtsy returned ' . count($images) . ' images for product ' . $product_id, $method_name);

        if (!empty($images) && count($images) > 0) {
            if (isset($listing_id)) {
                /* Delete those images listed from the OLD version of the module so avoid duplicate image on etsy. One in listting_image_id column & another one in etsy_image table */
                $existing_images = Db::getInstance()->getValue("SELECT listing_image_id FROM " . _DB_PREFIX_ . "etsy_products_list WHERE listing_id = '" . (int) $listing_id . "'");

                if (!empty($existing_images)) {
                    $existing_images_array = explode(",", $existing_images);
                    foreach ($existing_images_array as $existing_image) {
                        /**
                         * Set URL to send request for deleteListingImage API
                         * @date 13-04-2023
                         * @modifier Tanisha Gupta
                         */
                        //Correct parameter of image id - Fixed bug where $existing_image was treated as array instead of string
                        // Fixed duplicate image issue during product revision and sync
                        $etsyRequestURI = '/shops/' . $shopid . '/listings/' . $listing_id . '/images/' . trim($existing_image);
                        /**
                         * Removed json decode as data will be returned in the array
                         * Send request to the etsyGetResponse method as made changes to use only method to get etsy data
                         * @date 14-03-2023
                         * modifier Tanisha Gupta
                         */
                        self::etsyGetResponse($etsyRequestURI, 'DELETE', array());
                    }
                    Db::getInstance()->getValue("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_image_id = NULL WHERE listing_id = '" . (int) $listing_id . "'");
                }
                /* END Delete those images listed from the OLD version of the module */

                /**
                 * Set URL to send request for upload Images API
                 * @date 13-04-2023
                 * @modifier Tanisha Gupta
                 */
                $etsyRequestURI = '/shops/' . $shopid . '/listings/' . $listing_id . '/images';
                $etsyRequestMethod = 'POST';
                $i = 1;
                foreach ($images as $image) {
                    $etsyQueryString = array();
                    /*
                     * Remove Listing id other etsy gives the error
                     * @date 13-04-2023
                     * @modifier Tanisha Gupta
                     */
                    $etsyQueryString['image'] = new CURLFILE($image["image"]);
                    //changes by gopi ,image size chart position issuse fixes
                    if (!empty($image['rank'])) {
                        $rank = $image['rank'];
                    } else {
                        $rank = $i;
                    }
                    $etsyQueryString['rank'] = $rank;
                    //change by gopi end
                    if (!empty($image['overwrite'])) {
                        $etsyQueryString['overwrite'] = 1;
                    }

                    if (!empty($image['listing_image_id'])) {
                        $etsyQueryString['listing_image_id'] = $image['listing_image_id'];
                    }
                    /**
                     * Removed json decode as data will be returned in the array
                     * Send request to the etsyGetResponse method as made changes to use only method to get etsy data
                     * @date 14-03-2023
                     * modifier Tanisha Gupta
                     */
                    $image_list_response = self::etsyGetResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString, 'formtype');


                    if (!empty($image_list_response) && isset($image_list_response['listing_image_id'])) {
                        $sql = "UPDATE " . _DB_PREFIX_ . "etsy_images SET etsy_image_id = '" . pSQL($image_list_response['listing_image_id']) . "' WHERE image_id = '" . (int) $image['product_etsy_image_id'] . "'";
                        $imagesListed++;
                        Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_images SET etsy_image_id = '" . pSQL($image_list_response['listing_image_id']) . "' WHERE image_id = '" . (int) $image['product_etsy_image_id'] . "'");
                    } else {
                        $listingError = $image_list_response['error'];
                        /**
                         * Made changes to update error based on product id instead of listing id. As for new product, listing id will update after updating all information on etsy
                         * TG2023may Images-Issue
                         * @date 22-05-2023
                         * @modifier Tanisha Gupta
                         */
                        Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET is_error = '1', listing_error = '" . pSQL($listingError) . "' WHERE id_product = '" . (int) $product_id . "'");
                    }
                    sleep(1); //Sleep job to avoid exceed limit rate
                    $i++;
                }
            }
        }
        self::auditLogEntry('Job execution completed to list images on etsy marketplace.<br>Total Images Listed: ' . $imagesListed, $method_name);
        return true;
    }


    /**
     * This function is responsible to delete images listed which has been deleted for Prestashop
     * @date 22-05-2023
     * @commenter Tanisha Gupta
     * @param type $product_id
     * @param type $listing_id
     * @param type $language_id
     * @param type $shopid
     */
    public static function deleteAlreadyDeletedImages($product_id, $listing_id, $language_id, $shopid)
    {
        /* Delete those images listed which has been delete from prestashop */
        $sql = "SELECT etsy_image_id,image_id,ps_image_id FROM " . _DB_PREFIX_ . "etsy_images ei LEFT JOIN " . _DB_PREFIX_ . "image i ON (ei.ps_image_id = i.id_image and ei.product_id = i.id_product) WHERE ei.product_id = '" . (int) $product_id . "' and i.id_image IS NULL";
        $deleted_images = Db::getInstance()->executeS($sql);
        if (!empty($deleted_images)) {
            foreach ($deleted_images as $delete_image) {
                $is_deletable = true;
                if ($delete_image['ps_image_id'] == 999999) {
                    $id_profile = Db::getInstance()->getValue("SELECT id_etsy_profiles FROM " . _DB_PREFIX_ . "etsy_products_list WHERE id_product = '" . (int) $product_id . "'");
                    if ((int) $id_profile != 0) {
                        $is_size_chart_image_enable = (bool) Db::getInstance()->getValue("SELECT size_chart_image FROM " . _DB_PREFIX_ . "etsy_profiles WHERE id_etsy_profiles = '" . (int) $id_profile . "'");
                        if ($is_size_chart_image_enable) {
                            $is_deletable = false;
                        }
                    }
                }
                if ($is_deletable) {
                    $etsyRequestURI = '/shops/' . $shopid . '/listings/' . $listing_id . '/images/' . $delete_image['etsy_image_id'];
                    $etsyRequestMethod = 'DELETE';
                    self::etsyGetResponse($etsyRequestURI, 'DELETE', array());
                    Db::getInstance()->execute("Delete FROM " . _DB_PREFIX_ . "etsy_images WHERE image_id = '" . (int) $delete_image['image_id'] . "'");
                }
            }
        }
        /* End Delete those images listed which has been delete from prestashop */
    }
    /**
     * The purpose of this function is to prepare the image data for uploading to Etsy. 
     * Added shop id parameter to check images is exist or not
     * TG2023may Images-Issue
     * @date 22-05-2023
     * @author
     * @modifier Tanisha Gupta 
     */
    public static function prepareArrayToUploadImageOnEtsy($product_id, $listing_id, $language_id, $shopid)
    {

        $size_chart_image_id = 999999;
        $listing_images = array();
        $protocol_link = (Configuration::get('PS_SSL_ENABLED') || Tools::usingSecureMode()) ? 'https://' : 'http://';
        $useSSL = ((Configuration::get('PS_SSL_ENABLED')) || Tools::usingSecureMode()) ? true : false;
        $protocol_content = ($useSSL) ? 'https://' : 'http://';
        $link = new Link($protocol_link, $protocol_content);

        /** Fetch already uploaded images from the table */
        $existing_images = Db::getInstance()->executeS("SELECT * FROM " . _DB_PREFIX_ . "etsy_images WHERE product_id = '" . (int) $product_id . "'");

        /*
         * Filter out images that are already on Etsy to prevent duplicates
         * Fixed duplicate image issue during product revision and sync
         * @date 15-01-2025
         * @modifier Himanshu Vishwakarma
         */
        $etsy_existing_images = array();
        if (!empty($listing_id)) {
            $etsyRequestURI = '/listings/' . $listing_id . '/images';
            $response = self::etsyGetResponse($etsyRequestURI, 'GET', array());

            if (!empty($response['results'])) {
                foreach ($response['results'] as $result) {
                    $etsy_existing_images[] = $result['listing_image_id'];
                }
                self::auditLogEntry('Found ' . count($etsy_existing_images) . ' existing images on Etsy for listing ' . $listing_id, 'EtsyModule::prepareArrayToUploadImageOnEtsy');
            }
        }
        if (!empty($listing_id)) {
            $images = array();
            $image_arrays = Image::getImages($language_id, $product_id);
            $length_img_array = count($image_arrays);
            /*
             * changing by rishabh jain for adding size chart image
             */
            $id_profile = Db::getInstance()->getValue("SELECT id_etsy_profiles FROM " . _DB_PREFIX_ . "etsy_products_list WHERE id_product = '" . (int) $product_id . "'");
            if ((int) $id_profile != 0) {
                $exist_file = _PS_MODULE_DIR_ . 'kbetsy/views/img/profile/' . $id_profile . '.*';
                $is_size_chart_image_enable = (bool) Db::getInstance()->getValue("SELECT size_chart_image FROM " . _DB_PREFIX_ . "etsy_profiles WHERE id_etsy_profiles = '" . (int) $id_profile . "'");
                $match1 = glob($exist_file);
                if ($is_size_chart_image_enable && count($match1) > 0) {
                    $ban = explode('/', $match1[0]);
                    $ban = end($ban);
                    $ban = trim($ban);
                    $img_url = self::getModuleDirUrl() . 'kbetsy/views/img/profile/' . $ban;
                    if (file_exists($match1[0])) {
                        $size_chart_file_path = _PS_MODULE_DIR_ . 'kbetsy/views/img/profile/' . $ban;
                        if ($length_img_array >= 10) {
                            $image_arrays[9] = array(
                                'id_image' => $size_chart_image_id,
                                'id_product' => $product_id,
                                'path' => $size_chart_file_path,
                                'position' => 10, //changes by gopi ,replace 1 with 10 as size chart can not be at position one
                                'cover' => '',
                                'id_lang' => 1,
                                'legend' => '',
                                'rank' => 10, //changes by gopi ,aded rank in array so that we can use the same while sycing image on etsy
                            );
                        } else {
                            $image_arrays[$length_img_array] = array(
                                'id_image' => $size_chart_image_id,
                                'id_product' => $product_id,
                                'path' => $size_chart_file_path,
                                'position' => $length_img_array + 1, //changes by gopi ,replace 1 with 10 as size chart can not be at position one
                                'cover' => '',
                                'id_lang' => 1,
                                'legend' => '',
                                'rank' => $length_img_array + 1, //changes by gopi ,aded rank in array so that we can use the same while sycing image on etsy
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
                //changes by gopi for getting position which will be rank on etsy
                if ($image_array['id_image'] == $size_chart_image_id) {
                    $images['rank'] = $image_array['position'];
                }
                //changes by gopi end
                if ($image_array['id_image'] == $size_chart_image_id) {
                    $image_dir_path = $image_array['path'];
                } else {
                    $imgtype = empty(Configuration::get('KBETSY_IMAGE_SIZE')) ? ImageType::getFormattedName('large') : Configuration::get('KBETSY_IMAGE_SIZE');
                    $image_object = new Image($image_array['id_image'], 1);
                    /*
                     * Updated to use _PS_PRODUCT_IMG_DIR_ constant for PrestaShop 9.0 compatibility
                     * Added fallback to _PS_PROD_IMG_DIR_ for backward compatibility
                     * 27-12-2024
                     */
                    $image_dir_path = (defined('_PS_PRODUCT_IMG_DIR_') ? _PS_PRODUCT_IMG_DIR_ : _PS_PROD_IMG_DIR_) . $image_object->getExistingImgPath() . '-' . $imgtype . '.' . $image_object->image_format;

                    /* If large thumbnail is not exist then use home default image */
                    if (!file_exists($image_dir_path)) {
                        $image_dir_path = (defined('_PS_PRODUCT_IMG_DIR_') ? _PS_PRODUCT_IMG_DIR_ : _PS_PROD_IMG_DIR_) . $image_object->getExistingImgPath() . '-' . ImageType::getFormattedName('home') . '.' . $image_object->image_format;
                    }
                }
                $images['image'] = $image_dir_path;

                /**
                 * Made changes to list images again which are not available on etsy but image id is saved in database
                 * TGsep2023 Image-Upload-again
                 * @date 27-09-2023
                 * @author Tanisha Gupta
                 */
                $is_not = false;
                $is_updated = false;
                $is_existing = false;

                $esty_image_id = 0;
                $product_etsy_image_id = 0; // Module Etsy Table Auto Increment ID
                if (!empty($existing_images)) {
                    foreach ($existing_images as $existing_image) {
                        /** If current image is already exist in the Db */
                        if ($image_array['id_image'] == $existing_image['ps_image_id'] && $existing_image['ps_image_id'] == $size_chart_image_id) {
                            $is_existing = true;
                            $esty_image_id = $existing_image['etsy_image_id'];
                            $product_etsy_image_id = $existing_image['image_id'];
                            /*
                             * Check if size chart image needs to be updated
                             * Fixed duplicate image issue during product revision and sync
                             * @date 15-01-2025
                             * @modifier Himanshu Vishwakarma
                             */
                            if (!empty($existing_image['etsy_image_id'])) {
                                if ($existing_image['path_hash'] != md5_file($image_dir_path)) {
                                    $is_updated = true;
                                } else {
                                    $is_updated = false;
                                }
                            } else {
                                $is_updated = true; // New image needs to be uploaded
                            }
                        } else if ($image_array['id_image'] == $existing_image['ps_image_id']) {
                            $is_existing = true;
                            $esty_image_id = $existing_image['etsy_image_id'];
                            $product_etsy_image_id = $existing_image['image_id'];
                            /** Check if image is already uploaded on the etsy but image content has been changed so need to update the image on etsy again */
                            if (!empty($existing_image['etsy_image_id'])) {
                                if ($existing_image['path_hash'] != md5_file($image_dir_path)) {
                                    $is_updated = true;
                                } else {
                                    /**Checking if image exist on the Etsy. If not, then add the image into the system again
                                     * TG2023may Images-Issue
                                     * @date 22-05-2023
                                     * @author Ashish Kumar
                                     * @commenter Tanisha Gupta
                                     */

                                    $etsyRequestURI = '/shops/' . $shopid . '/listings/' . $listing_id . '/images/' . $existing_image['etsy_image_id'];
                                    $response = self::etsyGetResponse($etsyRequestURI, 'GET', array());
                                    if (!empty($response['error'])) {
                                        $is_updated = true;
                                        /**
                                         * Made changes to list images again which are not available on etsy but image id is saved in database
                                         * TGsep2023 Image-Upload-again
                                         * @date 27-09-2023
                                         * @author Tanisha Gupta
                                         */
                                        $is_not = true;
                                    } else {
                                        /*
                                         * Image exists on Etsy and content hasn't changed, no need to upload
                                         * Fixed duplicate image issue during product revision and sync
                                         * @date 15-01-2025
                                         * @modifier Himanshu Vishwakarma
                                         */
                                        $is_updated = false;
                                    }
                                }
                            }
                        }
                    }
                }

                /** If image is already exist & no changes in the content then no need to upload that image */
                if ($is_existing == true && !empty($esty_image_id) && $is_updated == false) {
                    /*
                     * Skip image upload if image already exists on Etsy and hasn't changed
                     * Fixed duplicate image issue during product revision and sync
                     * @date 15-01-2025
                     * @modifier Himanshu Vishwakarma
                     */
                    continue;
                }

                if ($is_existing == true) {
                    Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_images SET "
                        . "path_hash = '" . pSQL(md5_file($image_dir_path)) . "',"
                        . "path = '" . pSQL($image_dir_path) . "'"
                        . "WHERE `ps_image_id` = '" . (int) $image_array['id_image'] . "' AND "
                        . "product_id = " . (int) $product_id); /* 'ps_image_id' is column name, not Db prefix */
                    /**
                     * Made changes to list images again which are not available on etsy but image id is saved in database
                     * TGsep2023 Image-Upload-again
                     * @date 27-09-2023
                     * @author Tanisha Gupta
                     */
                    if ($is_updated == true && $is_not == false) {
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

                /*
                 * Check if image already exists on Etsy to prevent duplicates
                 * Fixed duplicate image issue during product revision and sync
                 * @date 15-01-2025
                 * @modifier Himanshu Vishwakarma
                 */
                if (!empty($esty_image_id) && in_array($esty_image_id, $etsy_existing_images)) {
                    continue;
                }
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
    /*
     * Added shop id parameter
     * @date 13-04-2023
     * @modifier Tanisha Gupta
     */
    public static function etsySyncTranslation($product_id, $listing_id, $profile_id, $shopid)
    {
        $method_name = 'EtsyModule::etsySyncTranslation()';
        self::auditLogEntry('Job execution started to sync translation on etsy', $method_name);
        $translations = self::prepareArrayToUpdateTranslationOnEtsy($product_id, $listing_id, $profile_id);
        if (!empty($translations)) {
            foreach ($translations as $translation) {
                /**
                 * Fetch Translations if exists or not
                 * @date 13-04-2023
                 * @author Tanisha Gupta
                 */
                $etsyRequestURI = '/shops/' . $shopid . '/listings/' . $listing_id . '/translations/' . $translation['language'];
                $etsyRequestMethod = 'GET';
                $response = self::etsyGetResponse($etsyRequestURI, $etsyRequestMethod);
                unset($translation['listing_id']);
                $translation_data = http_build_query($translation);
                /* if title is blank then create transation otherwise update translation */
                if (isset($response['title']) == "") {
                    $etsyRequestMethod = 'POST';
                } else {
                    $etsyRequestMethod = "PUT";
                }
                $translation_response = self::etsyGetResponse($etsyRequestURI, $etsyRequestMethod, $translation_data);
                if (!empty($translation_response) && isset($translation_response['title'])) {
                } else {
                    /**
                     * Changed conditions in where clause as listing id will be saved once all information update on etsy
                     * Updated error based on the profile and product id
                     * @date 15-04-2023
                     * @modifier Tanisha Gupta
                     */
                    $listingError = $translation_response['error'];
                    Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET is_error = 1, listing_error = '" . pSQL($listingError) . "' WHERE id_product = '" . (int) $product_id . "' AND id_etsy_profiles = '" . (int) $profile_id . "'");
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
                    $profile_details = Db::getInstance()->getRow("SELECT ef.* FROM " . _DB_PREFIX_ . "etsy_profiles ef "
                        . "WHERE id_etsy_profiles = '" . (int) $profile_id . "'", true, false);

                    $product_details = KbMarketplaceIntegration::getProductByProductId($product_id, $language_id);

                    $quantity = KbMarketplaceIntegration::getProductInventory($product_id);
                    if ($quantity > 999) {
                        $quantity = 999;
                    }

                    $context = Context::getContext();

                    // Force default (base) currency    
                    $default_currency_id = (int) Configuration::get('PS_CURRENCY_DEFAULT');
                    $context->currency = new Currency($default_currency_id);
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
                    /**
                     * Changes added to fix the issue as in case if there are no tags for the proucts then it returns bool values
                     * @modifier Pragya Maurya
                     * @date 10-06-2024                     * 
                     * PMJune2024 Tags-issue-fixes
                     */
                    if (is_array($productTags) && count($productTags) && isset($productTags[$language_id])) {
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
    public static function etsySyncDownloadFile($product_id, $listing_id, $profile_id, $shopid)
    {
        $method_name = 'EtsyModule::etsySyncDownloadFile()';
        $download_details = Db::getInstance()->getRow("SELECT pl.* , id_product_download FROM " . _DB_PREFIX_ . "etsy_products_list pl "
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
            $data['file'] = new CURLFILE($download_file);
            $data['name'] = $download_details['display_filename'];
            $data['rank'] = 1;
            if (!empty($download_details['listing_file_id'])) {
                $data['listing_file_id'] = $download_details['listing_file_id'];
            }
            $etsyRequestURI = '/shops/' . $shopid . '/listings/' . $listing_id . '/files';
            $etsyRequestMethod = 'POST';
            /**
             * Removed json decode as data will be returned in the array
             * Send request to the etsyGetResponse method as made changes to use only method to get etsy data
             * @date 14-03-2023
             * modifier Tanisha Gupta
             */
            $file_list_response = self::etsyGetResponse($etsyRequestURI, $etsyRequestMethod, $data, 'formtype');
            if (!empty($file_list_response) && isset($file_list_response['listing_file_id'])) {
                if (!empty($file_list_response['listing_file_id'])) {
                    /**
                     * Changed conditions in where clause as listing id will be saved once all information update on etsy
                     * Updated error based on the profile and product id
                     * @date 15-04-2023
                     * @modifier Tanisha Gupta
                     */
                    $listing_file_id = $file_list_response['listing_file_id'];
                    Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET "
                        . "listing_file_id = '" . pSQL($listing_file_id) . "', "
                        . "listing_file_hash = '" . pSQL(md5_file($download_file)) . "' "
                        . "WHERE id_product = '" . (int) $product_id . "' AND id_etsy_profiles = '" . (int) $profile_id . "'");
                }
            } else {
                //$listingError = str_replace("_", " ", key((array) $file_list_response));
                $listingError = $file_list_response['error'];
                //Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET is_error = 1, listing_error = '" . pSQL($listingError) . "' WHERE listing_id = '" . (int) $listing_id . "'");
                Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET is_error = 1, listing_error = '" . pSQL($listingError) . "' WHERE id_product = '" . (int) $product_id . "' AND id_etsy_profiles = '" . (int) $profile_id . "'");
                self::auditLogEntry('File Upload error' . $listingError, $method_name);
            }
            sleep(1); //Sleep job to avoid exceed limit rate
        }
        self::auditLogEntry('Job execution completed to list file on etsy.', $method_name);
        return true;
    }

    //Get products from etsy products table which needs to be deleted from etsy
    /**
     * Modified the function params as we are deleting the product while syncing. So used profileid in case if the request is from the profile level sync
     * @modifier Pragya Maurya
     * @date 13-06-2024
     */
    public static function getProductsToDeleteOnEtsy($kbproductid = false, $kbprofileid = false)
    {
        $condition = '';
        if ($kbproductid) {
            $condition .= ' AND id_product = ' . (int) $kbproductid;
        }
        if ($kbprofileid) {
            $condition .= ' AND id_etsy_profiles = ' . (int) $kbprofileid;
        }
        return Db::getInstance()->executeS("SELECT * FROM " . _DB_PREFIX_ . "etsy_products_list "
            . "WHERE listing_id IS NOT NULL AND "
            . "listing_id != '' AND listing_id != 0 "
            . "AND renew_flag = '0' "
            . "AND active = '1' " //change by gopi to perform any action on only enabled product
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
            Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_id = NULL, listing_status = 'Pending', renew_flag = '0', delete_flag = '0', is_error = '0', delete_track = '0', sold_flag = '0', active = '0' WHERE listing_id = '" . pSQL($listing_id) . "'");
        }
        self::auditLogEntry('Job execution completed to delete the items from the etsy.', $method_name);
    }

    public static function deleteItemsFromEtsy($profile_product)
    {
        $listing_id = $profile_product['listing_id'];
        $method_name = 'EtsyModule::deleteItemsFromEtsy()';
        self::auditLogEntry('Job execution started to delete the item: ' . $listing_id . ' from the etsy.', $method_name);
        $shop = self::etsyGetShopDetails();
        if (isset($shop['shop_id'])) {
            $etsyRequestURI = '/shops/' . $shop['shop_id'] . '/listings/' . $listing_id;
            /**
             * Changed made to inactive product on etsy instead of delete.
             * TGmay2023 Inactive-Product
             * @date 24-05-2023
             * @author Tanisha Gupta
             */
            $etsyRequestMethod = 'PATCH';
            $etsyQueryString = array();
            $etsyQueryString['state'] = 'inactive';
            /**
             * Removed json decode as data will be returned in the array
             * Send request to the etsyGetResponse method as made changes to use only method to get etsy data
             * @date 14-03-2023
             * @author Tanisha Gupta
             */
            $inactive_response = self::etsyGetResponse($etsyRequestURI, $etsyRequestMethod, http_build_query($etsyQueryString));
            if (!empty($inactive_response) && isset($inactive_response['error'])) {
                self::auditLogEntry("Error in deleting the product listing from etsy: " . $inactive_response['error'], $method_name);
            } else {
                Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_status = 'Inactive', renew_flag = '0', delete_flag = '0', is_error = '0', delete_track = '0', sold_flag = '0' WHERE listing_id = '" . pSQL($listing_id) . "'");
            }
        }
        self::auditLogEntry('Job execution completed to delete the items from the etsy.', $method_name);
    }

    //To get products from etsy products table to get their current status from etsy marketplace.
    //Logic of product status sync is changed. Initially to sync the status of the product, Individual request of each product was being sent. Now changed the logic the get all the listing from the Etsy & sync status accordingly.
    public static function getProductsListedOnEtsy()
    {
        return Db::getInstance()->getValue("SELECT count(*) FROM " . _DB_PREFIX_ . "etsy_products_list WHERE listing_id IS NOT NULL AND listing_id != '' AND listing_id != 0 AND renew_flag = '0' AND delete_flag = '0' AND active = '1'");
    }

    public static function syncItemListingStatus()
    {
        /* Get Shop details */
        /**
         * Remove json_decode as response is already decoded
         * @date 12-04-2023
         * @author Tanisha Gupta
         */
        $shop = self::etsyGetShopDetails();
        if (isset($shop['shop_id'])) {
            /**
             * While fetching listing data, there is no type parameter to send, So call getItemsFromEtsy
             */
            self::getItemsFromEtsy($shop['shop_id'], 'active', 1);
            self::getItemsFromEtsy($shop['shop_id'], 'expired', 1);
            self::getItemsFromEtsy($shop['shop_id'], 'inactive', 1);
        }
    }

    // Type like active, inactive, expired
    public static function getItemsFromEtsy($shop_id, $type, $page)
    {
        $etsyRequestURI = '/shops/' . $shop_id . '/listings?state=' . $type;
        $etsyRequestMethod = 'GET';
        $etsyQueryString = array("limit" => 100, "page" => $page, "shop_id" => $shop_id);
        $etsyQueryString = http_build_query($etsyQueryString);
        /**
         * Removed json decode as data will be returned in the array
         * Send request to the etsyGetResponse method as made changes to use only method to get etsy data
         * @date 14-03-2023
         * @author Tanisha Gupta
         */
        $response = self::etsyGetResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString);
        if (!empty($response) && isset($response['results']) && !empty($response['results'])) {
            foreach ($response['results'] as $item) {
                $listing_status = $item['state'];
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
                        Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_status = '" . pSQL($db_listing_status) . "', expiry_date = '" . pSQL(date("Y-m-d H:i:s", $item['ending_timestamp'])) . "', delete_flag = '0', is_error = '0', renew_flag = '0', listing_error = '' WHERE listing_id = '" . (int) $item['listing_id'] . "' AND listing_status in ('Pending', 'Inactive', 'Expired', 'Listed', 'Updated')");
                    } else if ($db_listing_status == "Sold Out") {
                        // Considering Sold Out Items as Inactive Status
                        Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_status = 'Inactive', expiry_date = '" . pSQL(date("Y-m-d H:i:s", $item['ending_timestamp'])) . "', sold_flag = '1', delete_flag = '0', is_error = '0', renew_flag = '0', listing_error = '' WHERE listing_id = '" . (int) $item['listing_id'] . "' AND listing_status in ('Pending', 'Inactive', 'Expired', 'Listed', 'Updated')");
                    } else if ($db_listing_status = 'Listed') {
                        /* If Item is Marked as Pending, Inactive, Expired, Listed then only mark the item as Listed.
                         * Don't Mark item as listed if item is in following state: Updated, Relisting, Deletion Pending, Sold Out
                         * In case of Sold Out, Item should remain in Sold Out stauts so that it can be relist in case of restock
                         */
                        /**
                         * if item is in Relisting, then also mark the item as Listed
                         * TG2023may Mark-Status-ListedForRelisting-Case
                         * @date 23-05-2023
                         * @modifier Tanisha Gupta
                         */
                        Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_status = '" . pSQL($db_listing_status) . "',delete_flag = '0', is_error = '0', renew_flag = '0', listing_error = '', expiry_date = '" . pSQL(date("Y-m-d H:i:s", $item['ending_timestamp'])) . "' WHERE listing_id = '" . (int) $item['listing_id'] . "' AND listing_status in ('Pending', 'Inactive', 'Expired', 'Listed','Relisting')");
                    }
                }
            }

            /* If page is equal to 1 then only run the loop. Because at page number 1, we are running loop for all the pages */
            if ($response['count'] > 100 && $page == 1) {
                $total_pages = ceil($response['count'] / 100);
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

                $getListingResponse = json_decode(self::etsyGetOAuthResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString));

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
                        Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_status = '" . pSQL($listingStatus) . "' WHERE listing_id = '" . (int) $listing['listing_id'] . "'");
                    }
                } else {
                    $listingError = str_replace("_", " ", key((array) $getListingResponse));
                    Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET is_error = 1, listing_error = '" . pSQL($listingError) . "' WHERE listing_id = '" . (int) $listing['listing_id'] . "'");
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
        /**
         * Get Shop ID
         * Changes done according to the get response using the v3 api
         * @date @12-04-2023
         * @author Tanisha Gupta
         */
        $shop = self::etsyGetShopDetails();
        if (isset($shop['shop_id'])) {
            //Get date to fetch orders from etsy order table
            $lastDate = Db::getInstance()->getValue("SELECT MAX(date_added) as last_date FROM " . _DB_PREFIX_ . "etsy_orders_list");

            if (empty($lastDate)) {
                $lastDate = date("Y-m-d H:i:s", strtotime("-2 days"));
            }

            //Prepare parameters to send request
            /*
             * Changes Shop id and send min_created as query parameter with the url 
             * @date 12-04-2023
             * @author Tanisha Gupta
             */
            $etsyRequestURI = '/shops/' . $shop['shop_id'] . '/receipts?min_created=' . strtotime($lastDate);
            $etsyRequestMethod = 'GET';
            $etsyQueryString = array();
            /**
             * Removed json decode as data will be returned in the array
             * Send request to the etsyGetResponse method as made changes to use only method to get etsy data
             * @date 14-03-2023
             * @author Tanisha Gupta
             */
            $shopReceipts = self::etsyGetResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString);

            if (!empty($shopReceipts) && isset($shopReceipts['results'])) {
                $shopReceiptsList = self::prepareReceiptFieldsList($shopReceipts['results']);
                if (!empty($shopReceiptsList)) {
                    foreach ($shopReceiptsList as $shopReceiptList) {
                        $orderResponse = KbMarketplaceIntegration::writeOrderIntoDb('kbetsy', $shopReceiptList);
                        if (isset($orderResponse['error']) && $orderResponse['error'] == '') {
                            if (!empty($orderResponse['success']['order_id'])) {
                                $receiptsFetched++;
                                Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_orders_list "
                                    . "SET id_order = '" . (int) $orderResponse['success']['order_id'] . "' "
                                    . "WHERE id_etsy_order = '" . pSQL($shopReceiptList['order']['id_etsy_order']) . "'");
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
                if (!empty($receiptDetail['transactions']) && isset($receiptDetail['transactions'])) {
                    //Add Etsy Order entry in specific etsy order list table
                    $dataExistenceResult = Db::getInstance()->getValue("SELECT count(*) as count FROM " . _DB_PREFIX_ . "etsy_orders_list WHERE id_etsy_order = '" . pSQL($receiptDetail['receipt_id']) . "'");

                    if ($dataExistenceResult == 0) {
                        Db::getInstance()->execute("INSERT INTO " . _DB_PREFIX_ . "etsy_orders_list VALUES (NULL, 0, '" . pSQL($receiptDetail['receipt_id']) . "', '0','0', '" . pSQL(date("Y-m-d H:i:s", $receiptDetail['create_timestamp'])) . "', NOW())");
                        //Set Firstname and Lastname parameters
                        if (!empty($receiptDetail['name'])) {
                            $customerName = explode(' ', $receiptDetail['name'], 2);
                        }


                        //Checking if Etsy is returning the buyer's email.
                        /**
                         * Added to Check if Etsy is returning the buyer's email as Etsy stopped sharing the emailId in the order response.
                         * PM2024Feb etsy-response-emailId-order
                         * @modifier Pragya Maurya
                         * @date 12-02-2024
                         */
                        if (empty($receiptDetail['buyer_email'])) {
                            //If buyer's email is empty then create the fake email ID using the buyer ID.
                            $receiptDetail['buyer_email'] = $receiptDetail['buyer_user_id'] . "@example.com";
                        }

                        self::createCustomerByReceipts($receiptDetail, $customerName);

                        $receiptTransactionsList = $receiptDetail['transactions'];

                        /**
                         * Get Country ID from Store Database
                         * @date 08-01-2026
                         * @author Manish
                         * MPJan2026 address_issue
                         */

                        $storeCountryId = (int) Configuration::get('PS_COUNTRY_DEFAULT');
                        $storeStateId = 0;

                        $storeStateId = (int) Db::getInstance()->getValue(
                            'SELECT id_state FROM ' . _DB_PREFIX_ . 'state 
                            WHERE id_country = ' . (int) $storeCountryId . ' 
                            AND active = 1 
                            ORDER BY id_state ASC'
                        );

                        //Get Country ID from Store Database
                        /**
                         * Etsy return country iso code so fetch store country id directly from ps based on the iso code
                         * @modifier Manish
                         * @date 08-01-2026
                         * MPJAN2026 address_issue
                         * 
                         * 
                         */

                        if (!empty($receiptDetail['country_iso'])) {
                            $orderCountry = Country::getByIso($receiptDetail['country_iso']);
                        } else {
                            $orderCountry = $storeCountryId;
                        }
                        /**
                         * Etsy return country iso code so fetch store country id directly from ps based on the iso code
                         * @date 12-04-2023
                         * @author Tanisha Gupta
                         */
                        /**
                         * Etsy return state iso code so fetch store state id directly from ps based on the iso code
                         * TG2023may Order-State
                         * @date 12-04-2023
                         * @author Tanisha Gupta
                         */
                        $orderState = 0;

                        if (!empty($receiptDetail['state']) && $orderCountry) {
                            $orderState = State::getIdByName($receiptDetail['state']);
                            if (empty($orderState)) {
                                $orderState = State::getIdByIso($receiptDetail['state'], (int) $orderCountry);
                            }
                        }
                        /**
                         * Added to Check if state is set otherwise fallback to store state
                         * @modifier Manish
                         * @date 08-01-2026
                         * MPJAN2026 address_issue
                         */

                        if (!$orderState && $storeStateId) {
                            $orderState = $storeStateId;
                        }

                        //Prepare Products Array for all ordered items
                        $productsArray = array();
                        foreach ($receiptTransactionsList as $receiptTransactionList) {
                            //Get Product ID from Etsy Product List Table
                            $productID = Db::getInstance()->getRow("SELECT * FROM " . _DB_PREFIX_ . "etsy_products_list WHERE listing_id = '" . (int) $receiptTransactionList['listing_id'] . "'");
                            if (!empty($productID)) {
                                $productDetails = new ProductCore($productID['id_product']);
                                $productInventory = KbMarketplaceIntegration::getProductInventory($productID['id_product']);
                                //Get Product Attribute ID
                                $attributesString = '';
                                $finalAttributeProductID = array();
                                $variations = $receiptTransactionList['variations'];
                                if (!empty($variations)) {
                                    $attributeProductID = array();
                                    $counter = 0;
                                    $finalAttributeProductID = array();
                                    // If Order Item sku is in SKU_PRODUCTID_VARIATIONID. Pick the prestashop variation id from the etst SKU.
                                    if (!empty($receiptTransactionList['sku'])) {
                                        if (Tools::substr($receiptTransactionList['sku'], 0, 4) == "SKU_") {
                                            $sku_parts = explode("_", $receiptTransactionList['sku']);
                                            if (count($sku_parts) == 3) {
                                                $finalAttributeProductID[0] = $sku_parts['2'];
                                            }
                                        }
                                    }

                                    // Find the Variation ID from the Property name like Size Small etc
                                    if (empty($finalAttributeProductID) || (isset($finalAttributeProductID[0]) && $finalAttributeProductID[0] == '')) {
                                        foreach ($variations as $variation) {
                                            $property_id = $variation['property_id'];
                                            $selectSQL = "SELECT id_attribute_group FROM " . _DB_PREFIX_ . "etsy_attributes ea INNER JOIN " . _DB_PREFIX_ . "etsy_attribute_mapping1 am1 ON am1.property_id = ea.attribute_id WHERE etsy_property_id = '" . (int) $property_id . "'";
                                            $attributeGroupDetail = Db::getInstance()->executeS($selectSQL, true, false);

                                            //Changes for the Order Mapping for the Custom Variation.
                                            if (empty($attributeGroupDetail)) {
                                                $selectSQL = "SELECT id_attribute_group FROM " . _DB_PREFIX_ . "etsy_attribute_mapping1 am1 WHERE custom_property_id = '" . (int) $property_id . "' AND listing_id = " . (int) $receiptTransactionList['listing_id'];
                                                $attributeGroupDetail = Db::getInstance()->executeS($selectSQL, true, false);
                                            }

                                            if ($attributeGroupDetail != '') {
                                                $attributeGroup = $attributeGroupDetail[0]['id_attribute_group'];
                                                $attributeValue = html_entity_decode($variation['formatted_value']);
                                                foreach ($attributeGroupDetail as $key => $singleAttributeGroup) {
                                                    $attributeGroup = $singleAttributeGroup['id_attribute_group'];
                                                    $selectSQL = "SELECT distinct(ppa.id_product_attribute) FROM " . _DB_PREFIX_ . "product_attribute ppa LEFT JOIN " . _DB_PREFIX_ . "product_attribute_combination pac ON ppa.id_product_attribute = pac.id_product_attribute LEFT JOIN " . _DB_PREFIX_ . "attribute_lang al ON pac.id_attribute = al.id_attribute LEFT JOIN " . _DB_PREFIX_ . "attribute a ON a.id_attribute = al.id_attribute WHERE a.id_attribute_group = '" . (int) $attributeGroup . "' AND al.name = '" . pSQL($attributeValue) . "' AND ppa.id_product = '" . (int) $productID['id_product'] . "'";
                                                    $attributeProductDetails = Db::getInstance()->executeS($selectSQL, true, false);
                                                    if (!empty($attributeProductDetails)) {
                                                        break;
                                                    }
                                                }

                                                $selectSQL = "SELECT distinct(ppa.id_product_attribute) FROM " . _DB_PREFIX_ . "product_attribute ppa LEFT JOIN " . _DB_PREFIX_ . "product_attribute_combination pac ON ppa.id_product_attribute = pac.id_product_attribute LEFT JOIN " . _DB_PREFIX_ . "attribute_lang al ON pac.id_attribute = al.id_attribute LEFT JOIN " . _DB_PREFIX_ . "attribute a ON a.id_attribute = al.id_attribute WHERE a.id_attribute_group = '" . (int) $attributeGroup . "' AND al.name = '" . pSQL($attributeValue) . "' AND ppa.id_product = '" . (int) $productID['id_product'] . "'";
                                                $attributeProductDetails = Db::getInstance()->executeS($selectSQL, true, false);

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
                                $price = 0.0;
                                /**
                                 * Correct parameter to fetch price 
                                 * TGmay2023 Price-Parameter
                                 * @date 22-05-2023
                                 * @modifier Tanisha Gupta
                                 */
                                $price = (float) $receiptTransactionList['price']['amount'] / $receiptTransactionList['price']['divisor'];
                                $productsArray[] = array(
                                    'id_product' => $productID['id_product'],
                                    'name' => $receiptTransactionList['title'],
                                    'attributes' => !empty($attributesString) ? $attributesString : '',
                                    'weight' => $productDetails->weight,
                                    'ean13' => $ean13,
                                    'upc' => $upc,
                                    'ecotax' => 0,
                                    'reference' => $reference,
                                    'supplier_reference' => $productDetails->supplier_reference,
                                    'weight_attribute' => 0,
                                    'id_product_attribute' => !empty($finalAttributeProductID[0]) ? $finalAttributeProductID[0] : '',
                                    'cart_quantity' => $receiptTransactionList['quantity'],
                                    'stock_quantity' => $productInventory,
                                    'id_customization' => $productDetails->customizable,
                                    'additional_shipping_cost' => 0,
                                    'id_shop' => Context::getContext()->shop->id,
                                    'price_wt' => $price,
                                    'price' => $price,
                                    'total_wt' => $price * $receiptTransactionList['quantity'],
                                    'total' => $price * $receiptTransactionList['quantity'],
                                    'wholesale_price' => $productDetails->wholesale_price,
                                    'id_supplier' => $productDetails->id_supplier
                                );
                            } else {
                                $price = 0.0;
                                /**
                                 * Correct parameter to fetch price 
                                 * TGmay2023 Price-Parameter
                                 * @date 22-05-2023
                                 * @modifier Tanisha Gupta
                                 */
                                $price = (float) $receiptTransactionList['price']['amount'] / $receiptTransactionList['price']['divisor'];
                                $productsArray[] = array(
                                    'id_product' => '0',
                                    'name' => $receiptTransactionList['title'],
                                    'attributes' => '',
                                    'weight' => 0,
                                    'ean13' => '',
                                    'upc' => '',
                                    'ecotax' => 0,
                                    'reference' => '',
                                    'supplier_reference' => '',
                                    'weight_attribute' => 0,
                                    'id_product_attribute' => '',
                                    'cart_quantity' => $receiptTransactionList['quantity'],
                                    'stock_quantity' => $receiptTransactionList['quantity'],
                                    'id_customization' => 0,
                                    'additional_shipping_cost' => 0,
                                    'id_shop' => Context::getContext()->shop->id,
                                    'price_wt' => $price,
                                    'price' => $price,
                                    'total_wt' => $price * $receiptTransactionList['quantity'],
                                    'total' => $price * $receiptTransactionList['quantity'],
                                    'wholesale_price' => $price,
                                    'id_supplier' => 0
                                );
                            }
                        }

                        $firstname = $receiptDetail['name'];
                        if (!empty($customerName[0])) {
                            $firstname = $customerName[0];
                        }

                        //Added By Ashish on 6th May
                        $total_tax_cost = 0;
                        /**
                         * Set price according to the Api response
                         * @date 12-04-2023
                         * @author Tanisha Gupta
                         */
                        $total_tax_cost = (float) $receiptDetail['total_tax_cost']['amount'] / $receiptDetail['total_tax_cost']['divisor'];
                        /**
                         * Made changes to fetch shipping profile title based on the shipping profile id getting in order api response
                         * TGmay2023 Shipping-Order
                         * @date 18-05-2023
                         * @modifier Tanisha Gupta
                         */
                        $carrier_name = '';

                        if (!empty($receiptTransactionList['shipping_method'])) {
                            $carrier_name = $receiptTransactionList['shipping_method'];
                        } else if (!empty($receiptTransactionList['shipping_profile_id'])) {
                            $carrier_name = SyncTemplate::getShippingProfileTitleByProfileId($receiptTransactionList['shipping_profile_id']);
                            if (empty($carrier_name)) {
                                $carrier_name = 'EtsyCarrier';
                            }
                        } else {
                            $carrier_name = 'EtsyCarrier';
                        }
                        /**
                         * Made changes to fallback address to dummy if not exist in etsy api
                         * @date 08-01-2026
                         * @modifier Manish
                         * MPJAN2026 address_issue
                         */
                        $dummyAddress1 = 'Dummy Address Line 1';
                        $dummyAddress2 = 'Dummy Address Line 2';
                        $dummyCity = 'Dummy City';
                        $dummyPostcode = '000000';

                        $address1 = !empty($receiptDetail['first_line']) ? self::kbConvertToUTF8($receiptDetail['first_line']) : $dummyAddress1;

                        $address2 = !empty($receiptDetail['second_line']) ? self::kbConvertToUTF8($receiptDetail['second_line']) : $dummyAddress2;

                        $city = !empty($receiptDetail['city']) ? self::kbConvertToUTF8($receiptDetail['city']) : $dummyCity;

                        $postcode = !empty($receiptDetail['zip']) ? $receiptDetail['zip'] : $dummyPostcode;

                        $orderDetails[] = array(
                            'customer' => array(
                                'email' => $receiptDetail['buyer_email'],
                                /**
                                 * If input string contains any HTML entities,then, converts them to their corresponding UTF-8 characters
                                 * TGmay2023 Fixed-UTF8-Issue-Address
                                 * @date 04-05-2023
                                 * @modifier Tanisha Gupta
                                 */
                                'firstname' => self::kbConvertToUTF8($firstname),
                                'lastname' => !empty($customerName[1]) ? self::kbConvertToUTF8($customerName[1]) : self::kbConvertToUTF8($firstname),
                                //changes done by Manish to assign address field to fallback to dummy
                                'address1' => $address1,
                                'address2' => $address2,
                                'postcode' => $postcode,
                                'city' => $city,
                                // changes end by Manish
                                'phone_mobile' => '', //Etsy does not provide phone/mobile number
                                'id_state' => $orderState,
                                'id_country' => $orderCountry
                            ),
                            'order' => array(
                                'id_language' => Context::getContext()->language->id,
                                'currency_iso_code' => $receiptDetail['grandtotal']['currency_code'],
                                /**
                                 * Made changes to fetch shipping profile title based on the shipping profile id getting in order api response
                                 * TGmay2023 Shipping-Order
                                 * @date 18-05-2023
                                 * @modifier Tanisha Gupta
                                 */
                                'name_carrier' => $carrier_name,
                                'payment_method' => $receiptDetail['payment_method'],
                                'id_warehouse' => 0, //As of now this module does not support advance stock management system
                                'cart_recyclable' => 0,
                                'cart_gift' => 0,
                                'id_shop' => Context::getContext()->shop->id,
                                'id_shop_group' => Context::getContext()->shop->id_shop_group,
                                'current_state' => !empty($receiptDetail['is_paid']) ? Configuration::get('etsy_order_default_status') : Configuration::get('etsy_order_unpaid_status'),
                                //                                'current_state' => !empty($receiptDetail->was_paid) ? Configuration::get('etsy_order_paid_status') : Configuration::get('etsy_order_default_status'),
                                'order_reference' => Order::generateReference(),
                                'total_paid_real' => (float) $receiptDetail['subtotal']['amount'] / $receiptDetail['subtotal']['divisor'],
                                'total_products' => (float) $receiptDetail['subtotal']['amount'] / $receiptDetail['subtotal']['divisor'],
                                'total_products_wt' => (((float) $receiptDetail['subtotal']['amount'] / $receiptDetail['subtotal']['divisor']) + $total_tax_cost), //Ashish on 6th May
                                'total_discounts_tax_excl' => 0,
                                'total_discounts_tax_incl' => 0,
                                'total_shipping_tax_excl' => (float) $receiptDetail['total_shipping_cost']['amount'] / $receiptDetail['total_shipping_cost']['divisor'],
                                'total_shipping_tax_incl' => (float) $receiptDetail['total_shipping_cost']['amount'] / $receiptDetail['total_shipping_cost']['divisor'],
                                'total_wrapping_tax_excl' => 0,
                                'total_wrapping_tax_incl' => 0,
                                'total_paid_tax_excl' => (((float) $receiptDetail['grandtotal']['amount'] / $receiptDetail['grandtotal']['divisor']) - $total_tax_cost), //Ashish on 6th May
                                // changes by rishabh jain for order message custom change
                                'order_msg' => $receiptDetail['message_from_buyer'],
                                // changes over
                                'total_paid_tax_incl' => (float) $receiptDetail['grandtotal']['amount'] / $receiptDetail['grandtotal']['divisor'],
                                'invoice_date' => '0000-00-00 00:00:00',
                                'delivery_date' => '0000-00-00 00:00:00',
                                'id_etsy_order' => $receiptDetail['receipt_id'],
                                'is_paid' => $receiptDetail['is_paid'],
                                'is_shipped' => $receiptDetail['is_shipped']
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
        if (!empty($receiptDetail['name'])) {
            $customerName = explode(' ', $receiptDetail['name'], 2);
        }
        $firstname = $receiptDetail['name'];
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

        $check_customer_exist = Customer::customerExists($receiptDetail['buyer_email'], false, false);
        if (!$check_customer_exist) {
            $create_customer = new Customer();
            $create_customer->email = $receiptDetail['buyer_email'];
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
            $sql = "SELECT id_state FROM " . _DB_PREFIX_ . "state WHERE country_id = '" . (int) $etsyCountryID . "' and iso_code = '" . psql($state_iso_code) . "'";
            $stateDetail = Db::getInstance()->getValue("SELECT id_state FROM " . _DB_PREFIX_ . "state WHERE id_country = '" . (int) $etsyCountryID . "' and iso_code = '" . psql($state_iso_code) . "'");
            if ($stateDetail) {
                return $stateDetail;
            }
        }
        return $storeStateID;
    }
    public static function getVariationIdByPropertyValue($variations, $productID, $listing_id = "")
    {
        $counter = 0;
        $attributeProductID = array();
        foreach ($variations as $variation) {
            /**
             * Start changes added for the warning message of the undefined offser exception
             * @modifier Pragya Mauurya
             * @date 18-10-2024
             * PMOct2024 warning-message-fixes
             */
            if (is_array($variation) && isset($variation['property_id'])) {
                $property_id = $variation['property_id'];
                $selectSQL = "SELECT id_attribute_group FROM " . _DB_PREFIX_ . "etsy_attributes ea INNER JOIN " . _DB_PREFIX_ . "etsy_attribute_mapping1 am1 ON am1.property_id = ea.attribute_id WHERE etsy_property_id = '" . (int) $property_id . "'";
                $attributeGroupDetail = Db::getInstance()->executeS($selectSQL, true, false);
                if (empty($attributeGroupDetail)) {
                    if (empty($attributeGroupDetail) && !empty($listing_id)) {
                        $selectSQL = "SELECT id_attribute_group FROM " . _DB_PREFIX_ . "etsy_attribute_mapping1 am1 WHERE custom_property_id = '" . (int) $property_id . "' AND listing_id = " . (int) $listing_id;
                        $attributeGroupDetail = Db::getInstance()->executeS($selectSQL, true, false);
                    }
                }

            } else {
                $attributeGroupDetail = '';
            }
            if ($attributeGroupDetail != '') {
                /**
                 * Start changes added for the notice message of the undefined offset exception
                 * @modifier Pragya Maurya 
                 * @date 16-101-2024
                 * PMOct2024 warning-message-fixes
                 */
                if (isset($attributeGroupDetail[0]['id_attribute_group'])) {
                    $attributeGroup = $attributeGroupDetail[0]['id_attribute_group'];
                } else {
                    $attributeGroup = null;
                }
                $attributeValue = html_entity_decode($variation['values'][0]);
                foreach ($attributeGroupDetail as $key => $singleAttributeGroup) {
                    $attributeGroup = $singleAttributeGroup['id_attribute_group'];
                    $selectSQL = "SELECT distinct(ppa.id_product_attribute) FROM " . _DB_PREFIX_ . "product_attribute ppa LEFT JOIN " . _DB_PREFIX_ . "product_attribute_combination pac ON ppa.id_product_attribute = pac.id_product_attribute LEFT JOIN " . _DB_PREFIX_ . "attribute_lang al ON pac.id_attribute = al.id_attribute LEFT JOIN " . _DB_PREFIX_ . "attribute a ON a.id_attribute = al.id_attribute WHERE a.id_attribute_group = '" . (int) $attributeGroup . "' AND al.name = '" . pSQL($attributeValue) . "' AND ppa.id_product = '" . (int) $productID . "'";
                    $attributeProductDetails = Db::getInstance()->executeS($selectSQL, true, false);
                    if (!empty($attributeProductDetails)) {
                        break;
                    }
                }
                $selectSQL = "SELECT distinct(ppa.id_product_attribute) FROM " . _DB_PREFIX_ . "product_attribute ppa LEFT JOIN " . _DB_PREFIX_ . "product_attribute_combination pac ON ppa.id_product_attribute = pac.id_product_attribute LEFT JOIN " . _DB_PREFIX_ . "attribute_lang al ON pac.id_attribute = al.id_attribute LEFT JOIN " . _DB_PREFIX_ . "attribute a ON a.id_attribute = al.id_attribute WHERE a.id_attribute_group = '" . (int) $attributeGroup . "' AND al.name = '" . pSQL($attributeValue) . "' AND ppa.id_product = '" . (int) $productID . "'";
                $attributeProductDetails = Db::getInstance()->executeS($selectSQL, true, false);
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


        $shippedStatus = Configuration::get('etsy_order_shipped_status');
        /**
         * Fetch Shop id
         * @date 12-04-2023
         * @author Tanisha Gupta
         */
        $shop = self::etsyGetShopDetails();
        if (!empty($shippedStatus) && isset($shop['shop_id'])) {            //Get orders to update status on etsy marketplace
            $receipts = Db::getInstance()->executeS("SELECT eol.id_order, eol.id_etsy_order, o.current_state "
                . "FROM " . _DB_PREFIX_ . "etsy_orders_list eol, " . _DB_PREFIX_ . "orders o "
                . "WHERE o.id_order = eol.id_order "
                . "AND eol.is_status_updated = '1' "
                . "AND (o.current_state = '" . (int) $shippedStatus . "')", true, false);

            if (!empty($receipts)) {
                foreach ($receipts as $receipt) {
                    /**
                     * Set shop parameter in the url
                     * @date 12-04-2023
                     * @author Tanisha Gupta
                     */
                    $etsyRequestURI = '/shops/' . $shop['shop_id'] . '/receipts/' . $receipt['id_etsy_order'];
                    $etsyRequestMethod = 'PUT';

                    if ($receipt['current_state'] == $shippedStatus) {
                        $etsyQueryString = array(
                            'was_paid' => 1,
                            'was_shipped' => 1
                        );
                    }
                    $etsyQueryString = http_build_query($etsyQueryString);
                    if (!empty($etsyQueryString)) {
                        $response = self::etsyGetResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString);
                        if (!empty($response) && isset($response['receipt_id'])) {
                            $reciptsUpdated++;

                            Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_orders_list "
                                . "SET is_status_updated = '0' "
                                . "WHERE id_etsy_order = '" . (int) $receipt['id_etsy_order'] . "'");
                        } else {
                            $listingError = $response['error'];
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
        /**
         * Getting response is already decoded, so removed decode function
         * @date 15-04-2023
         * @author Tanisha Gupta
         */
        $shop = self::etsyGetShopDetails();
        $reciptsUpdated = 0;
        $shippedName = Configuration::get('etsy_selected_shipment_name');
        if (!empty($shippedName)) {
            /**
             * PS8 Fixes: Fetching the tracking number from the 'order_carrier' table instead of the 'order' table 
             * since the 'shipping number' column has been removed in PrestaShop 8 version.
             * TGoct2023 Tracking-number-fetching
             * @date 12-09-2023
             * @author Tanisha Gupta 
             */
            $receipts = Db::getInstance()->executeS("SELECT eol.id_order, eol.id_etsy_order,o.tracking_number "
                . "FROM " . _DB_PREFIX_ . "etsy_orders_list eol, " . _DB_PREFIX_ . "order_carrier o "
                . "WHERE o.id_order = eol.id_order "
                . "AND eol.is_tracking_updated = '0' "
                . "AND o.tracking_number != '' ", true, false);
            if (!empty($receipts)) {
                foreach ($receipts as $receipt) {
                    $etsyRequestMethod = 'POST';
                    $etsyRequestURI = '/shops/' . $shop['shop_id'] . '/receipts/' . $receipt['id_etsy_order'] . '/tracking';
                    $etsyQueryString = array(
                        'carrier_name' => $shippedName,
                        'tracking_code' => $receipt['tracking_number'],
                        'send_bcc' => 0,
                    );
                    $etsyQueryString = http_build_query($etsyQueryString);
                    if (!empty($etsyQueryString)) {
                        $response = self::etsyGetResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString);
                        if (!empty($response) && isset($response['receipt_id'])) {
                            $reciptsUpdated++;
                            Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_orders_list "
                                . "SET is_tracking_updated = '1' "
                                . "WHERE id_etsy_order = '" . (int) $receipt['id_etsy_order'] . "'");
                        } else {
                            $listingError = $response['error'];
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
            $countryDetail = Db::getInstance()->getRow("SELECT country_name, iso_code FROM " . _DB_PREFIX_ . "etsy_countries WHERE country_id = '" . (int) $etsyCountryID . "'");
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

    //Get countries from Db
    public static function etsyGetAllCountriesFromDB()
    {
        return Db::getInstance()->executeS("SELECT * FROM " . _DB_PREFIX_ . "etsy_countries", true, false);
    }

    //To get country name from Db
    public static function etsyGetCountryNameByCountryId($country_id)
    {
        return Db::getInstance()->getValue("SELECT country_name FROM " . _DB_PREFIX_ . "etsy_countries WHERE country_id = " . (int) $country_id, true, false);
    }
    /**
     * To get Country name and country id based on country iso from Db
     * @date 10-04-2023
     * @author Tanisha Gupta
     */
    public static function etsyGetCountryByIsoCode($code)
    {
        return Db::getInstance()->executeS("SELECT * FROM " . _DB_PREFIX_ . "etsy_countries WHERE iso_code = '" . pSQL($code) . "'", true, false);
    }
    /**
     * To get Region name and Region id based on Region iso from Db
     * @date 10-04-2023
     * @author Tanisha Gupta
     */
    public static function etsyGetRegionByIsoCode($code)
    {
        return Db::getInstance()->executeS("SELECT * FROM " . _DB_PREFIX_ . "etsy_regions WHERE region_iso = '" . pSQL($code) . "'", true, false);
    }
    //To get region name from Db
    public static function etsyGetRegionNameByRegionId($region_id)
    {
        return Db::getInstance()->getValue("SELECT region_name FROM " . _DB_PREFIX_ . "etsy_regions WHERE region_id = " . (int) $region_id, true, false);
    }

    //To get etsy regions from Db
    public static function etsyGetAllRegionsFromDB()
    {
        return Db::getInstance()->executeS("SELECT * FROM " . _DB_PREFIX_ . "etsy_regions");
    }

    public static function downloadItem(&$data)
    {
        $shop = json_decode(self::etsyGetShopDetails());
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
        $response = json_decode(self::etsyGetOAuthResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString));
        $languages = Language::getLanguages(false);
        if (!empty($response->results)) {
            foreach ($response->results as $item) {
                $sql = 'SELECT COUNT(*) as count FROM ' . _DB_PREFIX_ . 'product_mapping_from_etsy WHERE listing_id = ' . (int) $item->listing_id;
                $avl = Db::getInstance()->getRow($sql);
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
                    $images = json_decode(self::etsyGetOAuthResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString));
                    $i = 1;
                    foreach ($images->results as $image) {
                        if ($i > 10) {
                            break;
                        }
                        $data['images'][$i] = $image->url_fullxfull;
                        $i++;
                    }
                    $etsyRequestURI = '/listings/' . $item->listing_id . '/inventory';
                    $inventory = json_decode(self::etsyGetOAuthResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString));
                    $variation_data = array();
                    if ($item->has_variations) {
                        if (!empty($inventory->results->products)) {
                            foreach ($inventory->results->products as $variation) {
                                $variation_data[] = array(
                                    "name" => $variation->property_values[0]->property_name,
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
                            $val = Db::getInstance()->getRow($sql);
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
                                $val = Db::getInstance()->getRow($sql);
                                $attr_grp_id[$key] = $val['id_attribute_group'];
                            }
                        }



                        foreach ($variationData as $key => $value) {
                            foreach ($value as $attribute) {
                                $sql = 'SELECT COUNT(*) as count FROM ' . _DB_PREFIX_ . 'attribute_lang WHERE name = "' . $attribute . '" AND id_lang = ' . (int) $lang_id;
                                $val = Db::getInstance()->getRow($sql);
                                if ($val['count'] == 0) {
                                    /**
                                     * Added PS vrsion condition as Attibute class has been renamed to ProductAttribute in PS 8
                                     * @date 15-04-2023
                                     * @modifier Tanisha Gupta
                                     */
                                    if (_PS_VERSION_ >= '8.0.0') {
                                        $attr = new ProductAttribute();
                                    } else {
                                        $attr = new Attribute();
                                    }

                                    $attr->id_attribute_group = $attr_grp_id[$key];
                                    foreach ($languages as $language) {
                                        $attr->name[(int) $language['id_lang']] = $attribute;
                                    }
                                    if ($attr->add()) {
                                        $array_id_attr[] = $attr->id;
                                    }
                                } else {
                                    $sql = 'SELECT id_attribute FROM ' . _DB_PREFIX_ . 'attribute_lang WHERE name = "' . $attribute . '" AND id_lang = ' . (int) $lang_id;
                                    $val = Db::getInstance()->getRow($sql);
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
        // return true;
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
                if (
                    !ImageManager::resize(
                        $fp,
                        $new_path . '.' . $image->image_format,
                        null,
                        null,
                        'jpg',
                        false,
                        $error
                    )
                ) {
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
                        if (
                            !ImageManager::resize(
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

    /*
     * Function is used to Requesting a Refresh OAuth Token
     * Added static keyword to avoid error Non-static method 
     * TGmay2023 Added-Static-Keyword-function
     * @date 09-04-2023
     * @author Tanisha Gupta
     */
    private static function getAccessToken()
    {
        $config = Configuration::get('etsy_api_key');
        $token_data = Configuration::get('kb_etsy_token');
        $token = '';
        if (!empty($token_data)) {
            $url = 'https://api.etsy.com/v3/public/oauth/token';
            $headers = [
                'Content-Type: application/x-www-form-urlencoded'
            ];
            $token_data_array = json_decode($token_data, true);
            if (!empty($token_data_array['refresh_token'])) {
                $body = http_build_query([
                    'client_id' => $config,
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $token_data_array['refresh_token']

                ]);
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => $body,
                    CURLOPT_HTTPHEADER => $headers
                ));
                $response = curl_exec($curl);
                $err = curl_error($curl);
                curl_close($curl);
                $response_array = json_decode($response, true);
                /**
                 * Disconnect the module when token is revoked.
                 * TGsep2023 Disconnect-module-token-revoked
                 * @date 27-09-2023 
                 * @author Tanisha Gupta
                 */
                if (!empty($err) || isset($response_array['error'])) {
                    if (isset($response_array['error'])) {
                        self::auditLogEntry($response_array['error_description'], 'getAccessToken');
                    } else {
                        self::auditLogEntry($err, 'getAccessToken');
                    }
                    Configuration::updateGlobalValue('kb_etsy_token', '');
                    die('Please check log');
                } else {
                    $token = $response_array['access_token'];
                    Configuration::updateGlobalValue('kb_etsy_token', $response_array);
                }
            }
        }
        return $token;
    }

    /**
     * Fetch etsy country information based on the country id
     * Added static keyword to avoid error Non-static method
     * TGmay2023 Added-Static-Keyword-function
     * @date 13-04-2023
     * @author Tanisha Gupta
     * @param type $country_id
     * @return string
     */
    public static function geyEtsyCountry($etsyCountryID)
    {
        $countryDetail = Db::getInstance()->getRow("SELECT country_name, iso_code FROM " . _DB_PREFIX_ . "etsy_countries WHERE country_id = '" . (int) $etsyCountryID . "'");
        if (!empty($countryDetail)) {
            return $countryDetail;
        } else {
            return "";
        }
    }

    //To get region name from Db
    public static function etsyGetRegionById($region_id)
    {
        return Db::getInstance()->getRow("SELECT region_name,region_iso FROM " . _DB_PREFIX_ . "etsy_regions WHERE region_id = '" . (int) $region_id . "'");
    }

    /**
     * Function used to fetch shipping carriers from etsy based on country iso code and saved to the Db
     * Added static keyword to avoid error Non-static method
     * TGmay2023 Added-Static-Keyword-function
     * @date 13-04-2023
     * @author Tanisha Gupta
     */
    public static function getShippingCarriers($countryid)
    {
        $shippingcarriers = array();
        $country_data = self::geyEtsyCountry($countryid);
        $etsyRequestURI = '/shipping-carriers?origin_country_iso=' . $country_data['iso_code'];
        $shippingcarriers = self::etsyGetResponse($etsyRequestURI);
        if (isset($shippingcarriers['count']) && $shippingcarriers['count'] > 0 && isset($shippingcarriers['results'])) {
            foreach ($shippingcarriers['results'] as $shippingcarriers1) {
                if (count($shippingcarriers1['domestic_classes']) > 0) {
                    $shippingcarriers1['domestic_classes'] = json_encode($shippingcarriers1['domestic_classes']);
                } else {
                    $shippingcarriers1['domestic_classes'] = '';
                }
                if (count($shippingcarriers1['international_classes']) > 0) {
                    $shippingcarriers1['international_classes'] = json_encode($shippingcarriers1['international_classes']);
                } else {
                    $shippingcarriers1['international_classes'] = '';
                }
                $insertData = "INSERT INTO " . _DB_PREFIX_ . "kb_etsy_shipping_carriers SET etsy_shipping_carrier_id = " . (int) $shippingcarriers1['shipping_carrier_id'] . ", etsy_shipping_carrier_name = '" . pSQL($shippingcarriers1['name']) . "', domestic_shipping = '" . pSQL($shippingcarriers1['domestic_classes']) . "', international_shipping ='" . pSQL($shippingcarriers1['international_classes']) . "', country_id =" . (int) $countryid;
                Db::getInstance()->execute($insertData);
            }
        } elseif (isset($shippingcarriers['count']) && $shippingcarriers['count'] == 0) {
            $insertData = "INSERT INTO " . _DB_PREFIX_ . "kb_etsy_shipping_carriers SET etsy_shipping_carrier_id = 0, etsy_shipping_carrier_name = 'NULL', domestic_shipping = 'NULL', international_shipping ='NULL', country_id =" . (int) $countryid;
            Db::getInstance()->execute($insertData);
        }
        return $shippingcarriers;
    }

    /**
     * Create method to convert a string to UTF-8 using mb_convert_encoding only if it contains encoded strings
     * TGmay2023 Fixed-UTF8-Issue-Address
     * @author Tanisha Gupta
     * @param string  $input_string
     * @return string
     */
    public static function kbConvertToUTF8($input_string)
    {
        // Check if the string contains any HTML entities
        if (strpos($input_string, '&') !== false) {
            // Convert HTML entities to UTF-8
            $output_string = mb_convert_encoding(html_entity_decode($input_string), 'UTF-8');
        } else {
            // The input string does not contain any HTML entities, so we can assume that it is already in UTF-8
            $output_string = $input_string;
        }

        // Return the converted string
        return $output_string;
    }

    public static function test()
    {
        self::etsyImageListings(2, 1675736570, 1, 14389944);
    }
}
