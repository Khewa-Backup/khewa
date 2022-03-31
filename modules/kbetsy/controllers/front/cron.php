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
        $etsyOAuthAccessToken = Configuration::get('etsy_oauth_access_token');
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
        echo $this->module->l('Success', 'cron');
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
        EtsyModule::etsyTestConnection($apiData);
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
        EtsyModule::getAllProfileProducts();
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

        /* Call delete product funcation before syncing the product */
        $this->syncDeleteProductsListing($id_product);

        $products = EtsyModule::getProductsToListOnEtsy($this->limit, $id_product);
        
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

    //To delete the products on etsy which has been marked as deleted on the Prestashop module
    private function syncDeleteProductsListing($kbproductid)
    {
        $productsList = EtsyModule::getProductsToDeleteOnEtsy($kbproductid);
        if (!empty($productsList)) {
            foreach ($productsList as $product) {
                if (!empty($product['listing_id'])) {
                    EtsyModule::deleteItemsFromEtsy($product);
                } else {
                    DB::getInstance()->execute("DELETE FROM " . _DB_PREFIX_ . "etsy_products_list WHERE id_product = '" . (int) $product['id_product'] . "'");
                }
            }
        }
        return true;
    }

    //To sync update products listing status
    private function syncProductsListingStatus()
    {
        $productsList = EtsyModule::getProductsListedOnEtsy();
        if ($productsList > 0) {
            EtsyModule::syncItemListingStatus();
        }

        /* Below code is commented as logic of status sync is changed. Fetching all the items from the etsy & updating the status instead of checking the stauts of Individual Item */
        /**
          $listingArray = array();
          if (isset($productsList) && count($productsList) > 0) {
          foreach ($producsyncProductsListingStatustsList as $productsList) {
          $listingArray[] = array(
          'listing_id' => $productsList['listing_id'],
          );
          }
          }

          if (isset($listingArray) && count($listingArray) > 0) {
          if (EtsyModule::etsyGetListings($listingArray)) {
          return true;
          }
          } else {
          return true;
          }
         */
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
