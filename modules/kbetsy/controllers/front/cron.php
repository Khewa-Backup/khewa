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
require_once(_PS_ROOT_DIR_ . '/init.php');
require_once(_PS_MODULE_DIR_ . 'kbetsy/classes/EtsyModule.php');

class KbetsyCronModuleFrontController extends ModuleFrontController
{

    public $limit = 20;

    public function init()
    {
        @ini_set('memory_limit', -1);
        @ini_set('max_execution_time', -1);
        @set_time_limit(0);

        parent::init();

        if (Configuration::get('KBETSY_DEMO')) {
            echo $this->module->l('This is an demo version. Synchronization is not allowed in the demo version.', 'cron');
            die();
        }

        $action = Tools::getValue('action');
        /**
         * Check if access token is exists or not
         * @date 09-04-2023
         * @author Tanisha Gupta
         */
        $kb_etsy_token = Configuration::get('kb_etsy_token');
        $etsyOAuthAccessToken = 1;
        if (empty($kb_etsy_token)) {
            $etsyOAuthAccessToken = 0;
        } else {
            $token_data_array = json_decode($kb_etsy_token, true);
            if (isset($token_data_array['access_token'])) {
                if ($token_data_array['access_token'] == 'null') {
                    $etsyOAuthAccessToken = 0;
                }
            } else {
                $etsyOAuthAccessToken = 0;
            }
        }

        $etsySwitchValue = Configuration::get('etsy_switch_value');

        if (empty($etsySwitchValue)) {
            echo $this->module->l('The module is not enabled. Please enable the module to continue.', 'cron');
            die();
        }

        if (empty($etsyOAuthAccessToken) && $action != 'testConnection') {
            echo $this->module->l('The module is not connected to etsy. Please connect the modue with etsy to continue.', 'cron');
            die();
        }

        if (!empty(Tools::getValue('limit'))) {
            $this->limit = Tools::getValue('limit');
        } else if (!empty(Configuration::get('etsy_sync_item'))) {
            $this->limit = Configuration::get('etsy_sync_item');
        }

        if (!Tools::isEmpty(trim(Tools::getValue('action')))) {
            $secure_key = Configuration::get('KBETSY_SECURE_KEY');
            if (($secure_key == Tools::getValue('secure_key')) || ($action == 'testConnection')) {
                switch ($action) {
                    case 'testConnection':
                        $this->testConnection();
                        break;
                    case 'syncShopSections':
                        $this->syncShopSections();
                        break;
                    case 'syncReturnPolicies':
                        $this->syncReturnPolicies();
                        break;
                    case 'syncCountriesRegions':
                        $this->syncCountriesRegions();
                        break;
                    case 'syncCategory':
                        $this->syncCategory();
                        break;
                    case 'syncShippingTemplates':
                        $this->syncShippingTemplates();
                        break;
                    case 'localSync':
                        $this->localSync();
                        break;
                    case 'syncProductsListing':
                        $this->syncProductsListing();
                        break;
                    case 'syncProductsListingStatus':
                        $this->syncProductsListingStatus();
                        break;
                    case 'syncOrdersListing':
                        $this->syncOrdersListing();
                        break;
                    case 'syncOrdersStatus':
                        $this->syncOrdersStatus();
                        break;
                }
            } else {
                echo $this->module->l('You are not authorized to access', 'cron');
                die();
            }
        }
        echo $this->module->l('Sync Process Completed.', 'cron');
        die();
    }

    //Test Connection From Etsy Marketplace
    private function testConnection()
    {
        $apiData = array(
            'etsy_api_key' => !Tools::isEmpty(trim(Tools::getValue('etsy_api_key'))) ? Tools::getValue('etsy_api_key') : Configuration::get('etsy_api_key'),
            'etsy_api_secret' => !Tools::isEmpty(trim(Tools::getValue('etsy_api_secret'))) ? Tools::getValue('etsy_api_secret') : Configuration::get('etsy_api_secret'),
            'etsy_api_host' => !Tools::isEmpty(trim(Tools::getValue('etsy_api_host'))) ? Tools::getValue('etsy_api_host') : Configuration::get('etsy_api_host'),
            'etsy_api_version' => !Tools::isEmpty(trim(Tools::getValue('etsy_api_version'))) ? Tools::getValue('etsy_api_version') : Configuration::get('etsy_api_version'),
        );
        /**
         * Changes done to generate the access token from the etsy and saved into the database.
         * @date 14-04-2023
         * @author Tanisha Gupta
         */
        $auditLogEntryString = 'Job execution statrted to setup connection with Etsy Marketplace.';
        $auditMethodName = 'EtsyModule::etsyTestConnection()';
        EtsyModule::auditLogEntry($auditLogEntryString, $auditMethodName);
        $etsyRequestURI = '/users/__SELF__';
        $etsyRequestMethod = 'GET';
        $etsyQueryString = array();
        $redirect_url = Context::getContext()->link->getModuleLink('kbetsy', 'cron', array('action' => 'testConnection'));
        $redirect_url = str_replace("&", "%26", $redirect_url);
        $config = $apiData['etsy_api_key'];
        $secure_key = Configuration::get('KBETSY_SECURE_KEY');
        $code_verifier = $secure_key . $secure_key;
        $scope = 'address_r address_w billing_r cart_r cart_w email_r feedback_r listings_d listings_r listings_w profile_r profile_w recommend_r recommend_w shops_r shops_w transactions_r transactions_w';
        $code = '';
        if (!empty(Tools::getValue('code'))) {
            $authCode = Tools::getValue('code');
        }
        if ((Tools::getValue('cron') == 'token_generate') && empty($authCode)) {
            $challenge_bytes = hash("sha256", $code_verifier, true);
            $code_challenge = rtrim(strtr(base64_encode($challenge_bytes), "+/", "-_"), "=");
            $location = 'https://www.etsy.com/oauth/connect?response_type=code&client_id=' . $config . '&redirect_uri=' . $redirect_url . '&state=' . $secure_key . '&code_challenge=' . $code_challenge . '&code_challenge_method=S256&scope=address_r address_w billing_r cart_r cart_w email_r feedback_r listings_d listings_r listings_w profile_r profile_w recommend_r recommend_w shops_r shops_w transactions_r transactions_w';
            Tools::redirect($location);
            die();
        } elseif (!empty($authCode)) {
            $auditLogEntryString = 'Job execution completed to setup connection with Etsy Marketplace.';
            $auditMethodName = 'EtsyModule::etsyTestConnection()';
            EtsyModule::auditLogEntry($auditLogEntryString, $auditMethodName);
            $url = 'https://api.etsy.com/v3/public/oauth/token';
            $headers = [
                'Content-Type: application/x-www-form-urlencoded'
            ];
            $redirect_url = Context::getContext()->link->getModuleLink('kbetsy', 'cron', array('action' => 'testConnection'));
            $body = http_build_query([
                'client_id' => $config,
                'grant_type' => 'authorization_code',
                'code' => $authCode,
                'redirect_uri' => $redirect_url,
                'code_verifier' => $code_verifier

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

            if (empty($err)) {
                $token_data_array = json_decode($response, true);
                if (isset($token_data_array['access_token'])) {
                    Configuration::updateGlobalValue('kb_etsy_token', $response);
                    $token = $token_data_array['access_token'];
                    $user_id = explode('.', $token);
                    Configuration::updateGlobalValue('etsy_api_user_id', $user_id[0]);
                    Tools::redirect(Configuration::get('etsy_redirect_url') . '&etsyConf=1');
                } else {
                    Configuration::updateGlobalValue('kb_etsy_token', '');
                    Tools::redirect(Configuration::get('etsy_redirect_url') . '&etsyError=1');
                }
            } else {
                //If connection failed
                Configuration::updateGlobalValue('kb_etsy_token', '');
                Tools::redirect(Configuration::get('etsy_redirect_url') . '&etsyError=1');
            }
        } else {
            echo $this->module->l('Error!!! Please try again.', 'cron');
            die;
        }
    }

    //To sync shipping templates and shipping templates entries
    private function syncShippingTemplates()
    {
        SyncTemplate::syncShippingTemplatesToEtsy();
        SyncTemplate::getAllExistingShippingTemplates();
    }

    private function syncShopSections()
    {
        SyncShopSection::syncEtsyShopSections();
        SyncShopSection::createShopSections();
        SyncShopSection::updateShopSections();
        SyncShopSection::deleteShopSections();
    }

    /*
     * Added syncReturnPolicies method to sync return policies from Etsy
     * @modifier Himanshu Vishwakarma 
     * @date 15-12-2025
     */
    private function syncReturnPolicies()
    {
        SyncReturnPolicy::syncEtsyReturnPolicies();
    }

    //To sync countries and regions
    private function syncCountriesRegions()
    {
        $method_name = 'KbetsyCronModuleFrontController::syncCountriesRegions()';
        EtsyModule::auditLogEntry('Job execution statrted to update countries & regions from etsy.', $method_name);

        EtsyModule::etsyGetAllCountries();
        EtsyModule::etsyGetAllRegions();

        EtsyModule::auditLogEntry('Job execution completed to update countries & regions from etsy.', $method_name);
    }

    /** Populate products on etsy table from PS table based on matching categories of profile */
    private function localSync()
    {
        /**
         * To add row action for local sync in case if the profile id is present, then sync the products of that profile only
         * @modifier Pragya Maurya
         * @date 26-05-2024
         * PMMay2024 ebay-profile-level-sync
         */
        $id_profile = false;
        if (!empty(Tools::getValue('profile_id'))) {
            $id_profile = Tools::getValue('profile_id');
        }
        EtsyModule::getAllProfileProducts($id_profile);
    }

    //To sync the new products  on etsy
    private function syncProductsListing()
    {
        $language = Configuration::get('etsy_default_lang') != '' ? Configuration::get('etsy_default_lang') : Context::getContext()->language->id;
        $listingArray = array();
        $id_product = false;
        if (!empty(Tools::getValue('id_product'))) {
            $id_product = Tools::getValue('id_product');
        }

        /**
         * Changes added by pragya maurya for localsync on profile level
         * Etsy-enhancement-profile-level
         * @modifier pragya maurya
         * @date 04-06-2024
         */
        $id_profile = false;
        if (!empty(Tools::getValue('profile_id'))) {
            $id_profile = Tools::getValue('profile_id');
        }

        /**
         * to add the id_profile as well in the syncDeleteProductsListing function so that the products of that profile only get deleted
         * Etsy-enhancement-profile-level
         * @modifier pragya maurya
         * @date 04-06-2024
         */
        $this->syncDeleteProductsListing($id_product, $id_profile);
        $products = EtsyModule::getProductsToListOnEtsy($this->limit, $id_product, $id_profile);
        if (isset($products) && count($products) > 0) {
            foreach ($products as $product) {
                $product_data = EtsyModule::prepareArrayToListingOnEtsy($product, $language);
                if (!empty($product_data)) {
                    $listingArray[] = $product_data;
                }
            }
        }
        if (isset($listingArray) && count($listingArray) > 0) {
            EtsyModule::etsyCreateListings($language, $listingArray);
        }
    }


    /**
     * Modified changes to delete the products of that profile only
     * Etsy-enhancement-profile-level
     * @modifier pragya maurya
     * @date 04-06-2024
     */
    private function syncDeleteProductsListing($kbproductid, $kbprofileid)
    {
        $productsList = EtsyModule::getProductsToDeleteOnEtsy($kbproductid, $kbprofileid);

        if (!empty($productsList)) {
            foreach ($productsList as $product) {
                if (!empty($product['listing_id'])) {
                    //changes by gopi
                    //non need to delete profile with 0 profile id
                    if ($product['id_etsy_profiles'] != 0) {
                        EtsyModule::deleteItemsFromEtsy($product);
                    }
                    //change by gopi end here
                } else {
                    Db::getInstance()->execute("DELETE FROM " . _DB_PREFIX_ . "etsy_products_list WHERE id_product = '" . (int) $product['id_product'] . "'");
                }
            }
        }
        return true;
    }

    //To sync update products listing status
    private function syncProductsListingStatus()
    {
        EtsyModule::getProductsListedOnEtsy();
        EtsyModule::syncItemListingStatus();
    }

    //To sync orders from etsy to PS
    private function syncOrdersListing()
    {
        EtsyModule::etsyGetShopReceipts();
    }

    //To update orders status on Etsy Marketplace
    private function syncOrdersStatus()
    {
        EtsyModule::etsyUpdateShopReceipts();
    }

    private function syncCategory()
    {
        Db::getInstance()->execute("TRUNCATE TABLE " . _DB_PREFIX_ . "etsy_categories");

        $language = Configuration::get('etsy_default_lang') != '' ? Configuration::get('etsy_default_lang') : Context::getContext()->language->id;
        $lang_data = new Language($language);

        $importFile = _PS_MODULE_DIR_ . 'kbetsy/data/categories_' . Tools::strtolower($lang_data->iso_code) . '.sql';
        if (!file_exists($importFile)) {
            $importFile = _PS_MODULE_DIR_ . 'kbetsy/data/categories_en.sql';
        }
        if (file_exists($importFile)) {
            $queryData = '';
            $lines = file($importFile);
            foreach ($lines as $line) {
                //This IF Remove Comment Inside SQL FILE
                if (Tools::substr($line, 0, 2) == '--' || $line == '') {
                    continue;
                }
                $queryData .= $line;
                //Breack Line Upto ';' NEW QUERY
                if (Tools::substr(trim($line), -1, 1) == ';') {
                    Db::getInstance()->execute(str_replace('_PREFIX_', _DB_PREFIX_, $queryData));
                    $queryData = '';
                }
            }
        }
        //$this->importCategory(0);
    }

    private function importCategory($parent_id, $name = "", $level = false, $data = array())
    {
        if ($level == false) {
            /* In case of first call, Pick data from the File */
            $language = Configuration::get('etsy_default_lang') != '' ? Configuration::get('etsy_default_lang') : Context::getContext()->language->id;
            $language_data = new Language($language);

            $importFile = _PS_MODULE_DIR_ . 'kbetsy/categories_' . Tools::strtolower($language_data->iso_code) . '.json';
            if (!file_exists($importFile)) {
                $importFile = _PS_MODULE_DIR_ . 'kbetsy/categories_en.json';
            }
            $category_data = Tools::file_get_contents($importFile);
            $categoryArray = json_decode($category_data, true);
            $data = $categoryArray["results"];
        } else {
            /* Data is being passed from the recursive call to insert sub category (data array variable).  */
        }

        foreach ($data as $category) {
            if ($name != "") {
                $final_name = $name . " > " . $category['name'];
            } else {
                $final_name = $category['name'];
            }
            Db::getInstance()->execute("INSERT INTO " . _DB_PREFIX_ . "etsy_categories SET "
                . "category_code = '" . (int) $category['id'] . "',"
                . "tag = '" . pSQL($category['path']) . "',"
                . "category_name = '" . pSQL($final_name) . "',"
                . "property_set = '',"
                . "parent_id = '" . (int) $parent_id . "'");
            $category_inserted_id = Db::getInstance()->Insert_ID();

            if ($category_inserted_id) {
                if (!empty($category['children'])) {
                    $this->importCategory($category_inserted_id, $final_name, true, $category['children']);
                } else {
                    Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_categories SET "
                        . "last_level = 1 "
                        . "WHERE id_etsy_categories = '" . (int) $category_inserted_id . "'");
                }
            }
        }
    }

    private function importEtsyProducts()
    {
        $data = array();
        EtsyModule::downloadItem($data);
    }
}
