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
require_once(_PS_MODULE_DIR_ . 'kbetsy/classes/EtsyModule.php');
require_once(_PS_MODULE_DIR_ . 'kbetsy/classes/EtsyProductListing.php');
/**
 * To include the below class as required to fetch the product details i.e titles and desc
 * @modifier Pragya Maurya
 * @date 26-05-2024
 * PMMay2024 ebay-custom-product-details
 */
require_once(_PS_MODULE_DIR_ . 'kbetsy/classes/KbMarketplaceIntegration.php');


class AdminEtsyProductsListingController extends ModuleAdminController
{

    public function __construct()
    {
        $this->name = 'EtsyProductsListing';
        $this->context = Context::getContext();
        $this->bootstrap = true;
        $this->display = 'list';
        $this->identifier = 'id_etsy_products_list';
        $this->no_link = true;
        $this->list_no_link = true;
        $this->lang = false;
        $this->className = 'EtsyProductListing';
        $this->table = 'etsy_products_list';
        parent::__construct();
        $this->icon = array(0 => 'disabled.gif', 1 => 'enabled.gif');

        $this->fields_list = array(
            'image' => array(
                'title' => $this->module->l('Image', 'AdminEtsyProductsListingController'),
                'align' => 'center',
                'orderby' => false,
                'filter' => false,
                'search' => false,
                'callback' => 'showCoverImage'
            ),
            'id_product' => array(
                'title' => $this->module->l('ID', 'AdminEtsyProductsListingController'),
                'align' => 'center',
                'filter_key' => 'pl!id_product',
                'callback' => 'showProductAdminUrl'
            ),
            'name' => array(
                'title' => $this->module->l('Name', 'AdminEtsyProductsListingController'),
                'filter_key' => 'pl!name',
                //                'callback' => 'showProductUrl'
            ),
            'reference' => array(
                'title' => $this->module->l('Reference', 'AdminEtsyProductsListingController'),
                'filter_key' => 'p!reference',
            ),
            'quantity' => array(
                'title' => $this->module->l('Quantity', 'AdminEtsyProductsListingController'),
                'filter_key' => 'sc!quantity',
                'align' => 'center'
            ),
            'listing_status' => array(
                'title' => $this->module->l('Listing Status', 'AdminEtsyProductsListingController'),
                'type' => 'select',
                'list' => array('Pending' => $this->module->l('Pending','AdminEtsyProductsListingController'), 'Listed' => $this->module->l('Listed','AdminEtsyProductsListingController'), 'Updated' => $this->module->l('Updated','AdminEtsyProductsListingController'), 'Inactive' => $this->module->l('Inactive','AdminEtsyProductsListingController'), 'Deletion Pending' => $this->module->l('Deletion Pending','AdminEtsyProductsListingController'), 'Expired' => $this->module->l('Expired','AdminEtsyProductsListingController'), 'Relisting' => $this->module->l('Marked for Relist','AdminEtsyProductsListingController')),
                'callback' => 'getTranslatedListingStatus',
                'filter_key' => 'listing_status'
            ),
            'active' => array(
                'title' => $this->module->l('Enabled', 'AdminEtsyProductsListingController'),
                'type' => 'select',
                'list' => array('0' => 'No', '1' => 'Yes'),
                'callback' => 'getTranslatedEnabledStatus',
                'filter_key' => 'a!active'
            ),
            'listing_id' => array(
                'title' => $this->module->l('Listing ID', 'AdminEtsyProductsListingController'),
                'callback' => 'showEtsyProductUrl',
            ),
            'profile_title' => array(
                'title' => $this->module->l('Etsy Profile', 'AdminEtsyProductsListingController'),
                'align' => 'center',
                'search' => true,
                'filter_key' => 'profile_title'
            ),

        );


        $lang = Configuration::get('etsy_default_lang') != '' ? Configuration::get('etsy_default_lang') : Context::getContext()->language->id;

        $this->_select .= 'a.active, sc.quantity, pl.`name`, i.`id_image` as image, ep.profile_title';
        $this->_join .= ' JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (a.`id_product` = pl.`id_product` and pl.id_shop = ' . (int) $this->context->shop->id . ' ) AND id_lang = ' . (int) $lang;
        $this->_join .= ' JOIN `' . _DB_PREFIX_ . 'product` p ON (a.`id_product` = p.`id_product` and p.`active` = 1) ';
        $this->_join .= ' JOIN `' . _DB_PREFIX_ . 'etsy_profiles` ep ON (ep.`id_etsy_profiles` = a.`id_etsy_profiles` and ep.`active` = 1)';
        $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'image_shop` ims ON (a.`id_product` = ims.`id_product` AND ims.`cover` = 1 AND ims.id_shop = ' . (int) $this->context->shop->id . ')';
        $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'stock_available` sc ON p.`id_product` = sc.`id_product` ';
        $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'image` i ON (ims.`id_image` = i.`id_image`)';

        $this->_where .= ' AND a.id_etsy_profiles != 0 ';
        $this->_group = 'GROUP BY a.id_etsy_products_list';

        //Line added to remove link from list row
        $this->module->list_no_link = true;
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->module->l('Delete selected','AdminEtsyProductsListingController'),
                'icon' => 'icon-trash',
                'confirm' => $this->module->l('Delete selected items?','AdminEtsyProductsListingController')
            ),
            /* Commented by Ashish on 1st Nov 2019. We will enable the same later as variation status condition needs to be handled for these
            'relist' => array(
                'text' => $this->module->l('Relist selected'),
                'icon' => 'icon-refresh',
                'confirm' => $this->module->l('Relist selected items?')
            ),
            'revise' => array(
                'text' => $this->module->l('Renew selected'),
                'icon' => 'icon-gear',
                'confirm' => $this->module->l('Renew selected items?')
            ),
            'halt' => array(
                'text' => $this->module->l('Halt selected'),
                'icon' => 'icon-ban',
                'confirm' => $this->module->l('Halt selected items?')
            ),
            */
            'activate' => array(
                'text' => $this->module->l('Enable selected','AdminEtsyProductsListingController'),
                'icon' => 'icon-power-off text-success',
                'confirm' => $this->module->l('Enable selected items?','AdminEtsyProductsListingController')
            ),
            'deactivate' => array(
                'text' => $this->module->l('Disable selected','AdminEtsyProductsListingController'),
                'icon' => 'icon-power-off text-danger',
                'confirm' => $this->module->l('Disable selected items?','AdminEtsyProductsListingController')
            ),
        );

        //This is to show notification messages to admin
        if (!Tools::isEmpty(trim(Tools::getValue('etsyConf')))) {
            new EtsyModule(Tools::getValue('etsyConf'), 'conf');
        }

        if (!Tools::isEmpty(trim(Tools::getValue('etsyError')))) {
            new EtsyModule(Tools::getValue('etsyError'), 'error');
        }
    }

    public function showProductUrl($row_data, $tr)
    {
        $product_id = $tr['id_product'];
        $url = $this->context->link->getProductLink((int)$product_id);
        return '<a href="' . $url . '" target="_blank">' . $row_data . '</a>';
    }
    /** Callback function to admin product edit in the helper list */
    public function showProductAdminUrl($data, $row_data)
    {
        return "<a target='_blank' href='" . $this->context->link->getAdminlink('AdminProducts', true, array("id_product" => $row_data['id_product'])) . "'>" . $data . "</a>";
    }

    public function showEtsyProductUrl($data, $row_data)
    {
        if (!empty($data)) {
            return "<a target='_blank' href='https://www.etsy.com/listing/" . $data . "'>" . $data . "</a>";
        } else {
            return $data;
        }
    }


    /** Callback function to display listing status in the helper list */
    public function getTranslatedListingStatus($status, $row_data)
    {
        $status_array = array('Pending' => $this->module->l('Pending','AdminEtsyProductsListingController'), 'Disabled' => $this->module->l('Disabled','AdminEtsyProductsListingController'), 'Updated' => $this->module->l('Updated','AdminEtsyProductsListingController'), 'Deletion Pending' => $this->module->l('Deletion Pending','AdminEtsyProductsListingController'), 'Listed' => $this->module->l('Listed','AdminEtsyProductsListingController'), 'Inactive' => $this->module->l('Inactive','AdminEtsyProductsListingController'), 'Expired' => $this->module->l('Expired','AdminEtsyProductsListingController'), 'Sold Out' => $this->module->l('Sold Out','AdminEtsyProductsListingController'), 'Relisting' => $this->module->l('Marked for Relist','AdminEtsyProductsListingController'));
        return $status_array[$status];
    }

    /** Callback function to display enabled status in the helper list */
    public function getTranslatedEnabledStatus($status, $row_data)
    {
        $status_array = array('0' => $this->module->l('No','AdminEtsyProductsListingController'), '1' => $this->module->l('Yes','AdminEtsyProductsListingController'));
        return $status_array[$status];
    }

    /** Callback function to display image in the helper list */
    public function showCoverImage($id_row, $row_data)
    {
        if (!empty($row_data['id_product'])) {
            $product = new ProductCore($row_data['id_product']);
            $coverImage = $product->getCover($row_data['id_product']);

            if (!empty($coverImage)) {
                $path_to_image = _PS_IMG_DIR_ . 'p/' . Image::getImgFolderStatic($coverImage['id_image']) . (int) $coverImage['id_image'] . '.' . $this->imageType;
                return ImageManagerCore::thumbnail($path_to_image, 'product_mini_' . $row_data['id_product'] . '_' . $this->context->shop->id . '.' . $this->imageType, 45, $this->imageType);
            }
        }
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->addJS($this->getModuleDirUrl() . 'kbetsy/views/js/script.js');
        /**
         * To include tinymce editor on product listing page
         * product.js file will be used to handl the ajax request and modals for the custom product detais
         * @modifier Pragya Maurya
         * @date 26-05-2024
         * PMMay2024 ebay-custom-product-details
         */
        if (version_compare(_PS_VERSION_, '1.7.8', '>=')) {
        /**
         * To include tinymce editor on product listing page
         * @modifier Pragya Maurya
         * @date 26-05-2024
         * PMMay2024 ebay-custom-product-details
         */
            $this->addJs(_MODULE_DIR_ . 'kbetsy/views/js/tinymce.inc.js');
            $this->addJS($this->getModuleDirUrl() . 'kbetsy/views/js/product.js');
        }else{
         //   $this->addJs(_MODULE_DIR_ . 'kbetsy/views/js/tinymce.inc.js');
            $this->addJs(_MODULE_DIR_ . 'kbetsy/views/js/old_tinymce.inc.js');
            $this->addJS($this->getModuleDirUrl() . 'kbetsy/views/js/old_product.js');

        }
        $this->addJs(_MODULE_DIR_ . 'kbetsy/views/js/tiny_mce.js');       
        $this->addCSS($this->getModuleDirUrl() . 'kbetsy/views/css/style.css');
    }

    public function renderList()
    {
        $this->addRowAction('error');
        $this->addRowAction('sync');
        $this->addRowAction('renew');
        $this->addRowAction('status');
        $this->addRowAction('delete');
        /**
         * To add the row action for the custom product details
         * @modifier Pragya Maurya
         * @date 26-05-2024
         * PMMay2024 ebay-custom-product-details
         */
        $this->addRowAction('customDetails');

        $this->context->smarty->assign("message", $this->module->l('In case you want to set the products as Inactive, Delete those products from this page only (Instead from the Etsy account). Setting the product as Inactive on the Etsy account directly will relist the item on the Etsy on next CRON run.', 'AdminEtsyAttributeMappingController'));
        $this->context->smarty->assign("type", "alert-warning");
        $this->context->smarty->assign("KbMessageLink", '');
        $instrction_1 = $this->context->smarty->fetch(_PS_MODULE_DIR_ . "kbetsy/views/templates/admin/msgs.tpl");

        $this->context->smarty->assign("message", $this->module->l('Kindly do not delete the listed products from the Etsy account directly. If you want to delete the items from the Etsy, Kindly delete the same from the module itself.', 'AdminEtsyAttributeMappingController'));
        $this->context->smarty->assign("type", "alert-warning");
        $this->context->smarty->assign("KbMessageLink", '');
        $instrction_2 = $this->context->smarty->fetch(_PS_MODULE_DIR_ . "kbetsy/views/templates/admin/msgs.tpl");

        $remaining_limit = Configuration::get('KBETSY_REMAINING_LIMIT');
        /**
         * Set limit to 0 to prevent the remaining limit message from being displayed on the product listing page as we are not getting the remaining limit during token generation or renewal.
         * TG2023may Hide-Limit-Message
         * @date 23-03-2023
         * @modifier Tanisha Gupta
         */
        $remaining_limit = 0;
        if (!empty($remaining_limit)) {
            $this->context->smarty->assign("message", $this->module->l('Etsy API daily limit remaining: ' . $remaining_limit, 'AdminEtsyAttributeMappingController'));
            $this->context->smarty->assign("type", "alert-info");
            $this->context->smarty->assign("KbMessageLink", '');
            $msgs = $this->context->smarty->fetch(_PS_MODULE_DIR_ . "kbetsy/views/templates/admin/msgs.tpl");
            return $msgs . parent::renderList() . $instrction_1 . $instrction_2;
        } else {
            return parent::renderList() . $instrction_1 . $instrction_2;
        }
    }

    /**
     * To show the custom details link on the product listing page
     * @modifier Pragya Maurya
     * @date 26-05-2024
     * PMMay2024 ebay-custom-product-details
     */
    public function displaycustomdetailsLink($token = null, $id = null, $name = null)
    {

        $selectSQL = "SELECT * FROM " . _DB_PREFIX_ . "etsy_products_list WHERE id_etsy_products_list = '" . (int) $id . "'";
        $productDetails = Db::getInstance()->getRow($selectSQL, true, false);

        if (empty($productDetails)) {
            return null;
        } else {
            $tpl = $this->context->smarty->createTemplate(_PS_MODULE_DIR_ . 'kbetsy/views/templates/admin/list/list_action_list_custom_details.tpl');
            $tpl->assign(array(
                'profile_product_id' => $productDetails['id_etsy_products_list'],
                'action' => $this->module->l('Product Customization Info for Etsy', 'AdminEtsyProductsListing'),
                'custom_description' => 'pencil',
                'icon' => 'refresh',
                'href' =>  $this->context->link->getAdminlink('AdminEtsyProductsListing') . '&id_etsy_profile_products=' . $id . '&action=editDetails',
            ));
        }
        return $tpl->fetch();
    }


    public function initToolbar()
    {
        parent::initToolbar();
        unset($this->toolbar_btn['new']);
    }

    public function init()
    {
        if (Tools::getIsset('statusetsy_products_list')) {
            $id_ebay_profile_products = Tools::getValue('id_etsy_products_list');
            $profile_active = Db::getInstance()->getValue('SELECT active FROM ' . _DB_PREFIX_ . 'etsy_products_list  WHERE id_etsy_products_list = ' . (int) $id_ebay_profile_products);

            $final_status = $profile_active == 1 ? 0 : 1;
            Db::getInstance()->query('UPDATE ' . _DB_PREFIX_ . 'etsy_products_list SET active = "' . (int) $final_status . '" WHERE id_etsy_products_list = ' . (int) $id_ebay_profile_products);

            Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyProductsListing') . '&etsyConf=63');
        }
        parent::init();
    }

    /**
     * Assigning titles labels for the custom details
     * @modifier Pragya Maurya
     * @date 26-05-2024
     * PMMay2024 ebay-custom-product-details
     */
    public function initContent()
    {
        parent::initContent();

        $this->context->smarty->assign(array(
            'custom_details_title' => $this->module->l('Product Title', 'AdminEtsyProductsListingController'),
            'custom_details_description' => $this->module->l('Product Description', 'AdminEtsyProductsListingController'),
            'custom_product_information' => $this->module->l('Product Customization Info for Etsy', 'AdminEtsyProductsListingController'),
            'default_product_details_text' => $this->module->l('Want to use default product details', 'AdminEtsyProductsListingController'),
            'action_get_custom_details' => $this->context->link->getAdminlink('AdminEtsyProductsListing')
        ));

        $content = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbetsy/views/templates/admin/list/custom_details.tpl');


        $this->context->smarty->assign(array(
            'content' => $this->content . $content,
        ));
    }

    /** Display view listing error link */
    public function displaySyncLink($token = null, $id = null, $name = null)
    {
        $secure_key = Configuration::get('KBETSY_SECURE_KEY');
        /**
         * Made changes to show sync link for the relisting status as well
         * TGmay2023 Inactive-Product
         * @date 24-05-2023
         * @modifier Tanisha Gupta
         */
        $productDetails = Db::getInstance()->getRow("SELECT id_product, active, listing_id, listing_status, renew_flag FROM " . _DB_PREFIX_ . "etsy_products_list WHERE id_etsy_products_list = '" . (int) $id . "'", true, false);
        if (!empty($productDetails)) {
            if ($productDetails['active'] == 1) {
                if ($productDetails['listing_id'] == null || $productDetails['listing_status'] == 'Updated' || ($productDetails['listing_status'] == 'Relisting' && $productDetails['renew_flag'] == 1)) {
                    $this->context->smarty->assign(array(
                        'href' => $this->context->link->getModuleLink('kbetsy', 'cron', array('action' => 'syncProductsListing', 'id_product' => $productDetails['id_product'], 'secure_key' => $secure_key)),
                        'action' => $this->module->l('Sync','AdminEtsyProductsListingController'),
                        'icon' => 'refresh'
                    ));
                    return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbetsy/views/templates/admin/list/list_action_tab.tpl');
                }
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    /** Display view action link  */
    public function displayRenewLink($token = null, $id = null, $name = null)
    {
        $productDetails = Db::getInstance()->getRow("SELECT * FROM " . _DB_PREFIX_ . "etsy_products_list WHERE active = 1 and listing_id != '' AND listing_id != 0 AND listing_id IS NOT NULL AND id_product_attribute = '0' AND id_etsy_products_list = '" . (int) $id . "'");
        if (empty($productDetails)) {
            return null;
        }

        $action = 'Renew';

        if ($productDetails['listing_status'] == 'Deletion Pending') {
            $action = 'halt';
        }

        if ($action == 'Renew') {
            /** Renew option will not be present if status is Inactive */
            if ($productDetails['listing_status'] == 'Inactive' || $productDetails['listing_status'] == 'Expired' || $productDetails['listing_status'] == 'Updated' || $productDetails['listing_status'] == 'Sold Out' || $productDetails['listing_status'] == 'Relisting') {
                return null;
            } else {
                $this->context->smarty->assign(array(
                    'href' => $this->context->link->getAdminlink('AdminEtsyProductsListing') . '&' . $this->identifier . '=' . $id . '&action=revise',
                    'action' => $this->module->l('Revise','AdminEtsyProductsListingController'),
                    'icon' => 'refresh'
                ));
                return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbetsy/views/templates/admin/list/list_action.tpl');
            }
        } else {
            $this->context->smarty->assign(array(
                'href' => $this->context->link->getAdminlink('AdminEtsyProductsListing') . '&' . $this->identifier . '=' . $id . '&action=halt',
                'action' => $this->module->l('Stop Deletion','AdminEtsyProductsListingController'),
                'icon' => 'ban'
            ));

            return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbetsy/views/templates/admin/list/list_action.tpl');
        }
    }

    /** Display view action link  */
    public function displayDeleteLink($token = null, $id = null, $name = null)
    {
        $productDetails = Db::getInstance()->getRow("SELECT * FROM " . _DB_PREFIX_ . "etsy_products_list WHERE id_product_attribute = '0' AND id_etsy_products_list = '" . (int) $id . "'");

        if (empty($productDetails)) {
            return null;
        }
        if ($productDetails['active'] == 0) {
            return null;
        }

        if (empty($productDetails['listing_id'])) {
            return null;
        }

        if ($productDetails['listing_status'] == 'Deletion Pending' || $productDetails['listing_status'] == 'Sold Out' || $productDetails['listing_status'] == 'Relisting') {
            return null;
        }

        $action = 'Delete';
        if (!empty($productDetails) && ($productDetails['listing_status'] == 'Inactive' || $productDetails['listing_status'] == 'Expired')) {
            $action = 'Relist';
        }
        if ($action == 'Relist') {
            if ($productDetails['active'] == 0) {
                return null;
            }
            $this->context->smarty->assign(array(
                'href' => $this->context->link->getAdminlink('AdminEtsyProductsListing') . '&' . $this->identifier . '=' . $id . '&action=relist',
                'action' => $this->module->l('Relist','AdminEtsyProductsListingController'),
                'icon' => 'list'
            ));
            return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbetsy/views/templates/admin/list/list_action.tpl');
        } else {
            $this->context->smarty->assign(array(
                'href' => $this->context->link->getAdminlink('AdminEtsyProductsListing') . '&' . $this->identifier . '=' . $id . '&delete' . $this->table,
                'action' => $this->module->l('Delete','AdminEtsyProductsListingController'),
                'icon' => 'trash',
                'warning_message' => $this->module->l('Are you sure to delete the item? Item status wil be set as Inactive in the etsy account.','AdminEtsyProductsListingController')
            ));
            return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbetsy/views/templates/admin/list/list_action_confirmation.tpl');
        }
    }

    /** Display view listing error link */
    public function displayErrorLink($token = null, $id = null, $name = null)
    {
        $productDetails = Db::getInstance()->getRow("SELECT listing_error, active FROM " . _DB_PREFIX_ . "etsy_products_list WHERE id_etsy_products_list = '" . (int) $id . "'", true, false);
        if (!empty($productDetails['listing_error'])) {
            if ($productDetails['active'] == 1) {
                $this->context->smarty->assign(array(
                    'href' => 'etsy-error-' . $id,
                    'action' => $this->module->l('View Error','AdminEtsyProductsListingController'),
                    'icon' => 'search-plus',
                    'text' => !empty($productDetails['listing_error']) ? $productDetails['listing_error'] : 'No Listing Error Found.'
                ));

                return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbetsy/views/templates/admin/list/list_action_view_error.tpl');
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    /** Display view listing error link */
    public function displayStatusLink($token = null, $id = null, $name = null)
    {
        $productDetails = Db::getInstance()->getRow("SELECT active, listing_id FROM " . _DB_PREFIX_ . "etsy_products_list WHERE id_etsy_products_list = '" . (int) $id . "'", true, false);
        if (!empty($productDetails)) {
            if ($productDetails['active'] == 1) {
                if (empty($productDetails['listing_id'])) {
                    $this->context->smarty->assign(array(
                        'href' => $this->context->link->getAdminlink('AdminEtsyProductsListing') . '&' . $this->identifier . '=' . $id . '&action=disable',
                        'action' => $this->module->l('Disable','AdminEtsyProductsListingController'),
                        'icon' => 'power-off text-danger'
                    ));
                } else {
                    return '';
                }
            } else {
                $this->context->smarty->assign(array(
                    'href' => $this->context->link->getAdminlink('AdminEtsyProductsListing') . '&' . $this->identifier . '=' . $id . '&action=enable',
                    'action' => $this->module->l('Enable','AdminEtsyProductsListingController'),
                    'icon' => 'power-off text-success'
                ));
            }
            return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbetsy/views/templates/admin/list/list_action.tpl');
        } else {
            return '';
        }
    }

    public function postProcess()
    {

        $method_name = 'AdminEtsyProductsListing::postProcess()';

        if ($this->action == 'bulkdelete') {
            $this->processBulkDelete($this->boxes);
        } else if ($this->action == 'bulkrelist') {
            $this->processBulkrelist();
        } else if ($this->action == 'bulkrevise') {
            $this->processBulkrevise();
        } else if ($this->action == 'bulkhalt') {
            $this->processBulkhalt();
        } else if ($this->action == 'bulkactivate') {
            $this->processBulkactivate();
        } else if ($this->action == 'bulkdeactivate') {
            $this->processBulkdeactivate();
        } else if (!Tools::isEmpty(trim(Tools::getValue('action'))) && !Tools::isEmpty(trim(Tools::getValue('id_etsy_products_list')))) {
            /**
             * Issue: For the inactive product listing status was showing as "Updated". But it should be "Inactive".
             * When enabling a product, now fetches the product listing status, Only if the status is "Updated" or "Listed," the module updates the status. Otherwise, not.
             * TGsep2023 Inactive-Product-Listing 
             * @date 27-09-2023
             * @author Tanisha Gupta
             */
            $product_details = Db::getInstance()->getRow("SELECT pl.name, epl.id_product, epl.listing_id,epl.listing_status  FROM " . _DB_PREFIX_ . "etsy_products_list epl, " . _DB_PREFIX_ . "product_lang pl WHERE epl.id_etsy_products_list = '" . (int) Tools::getValue('id_etsy_products_list') . "' AND epl.id_product = pl.id_product AND pl.id_lang = '" . (int) $this->context->language->id . "'");
            if (Tools::getValue('action') == 'revise') {
                Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_status = 'Updated', renew_flag = '0', is_error = 0, delete_flag = '0' WHERE  id_product = '" . (int) $product_details['id_product'] . "'");
                $auditLogEntryString = 'Revise of product recorded successfully';
                EtsyModule::auditLogEntry($auditLogEntryString, $method_name);
                Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyProductsListing') . '&etsyConf=4');
            } else if (Tools::getValue('action') == 'halt') {
                $checkDeleteFlag = Db::getInstance()->getValue("SELECT count(*) as count FROM " . _DB_PREFIX_ . "etsy_products_list WHERE id_etsy_products_list = '" . (int) Tools::getValue('id_etsy_products_list') . "' AND delete_flag = '1'");
                if (Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET renew_flag = '0', delete_flag = '0', listing_status = 'Listed', is_error = 0 WHERE id_product = '" . (int) $product_details['id_product'] . "'")) {
                    EtsyModule::auditLogEntry('Product deletion stopped. ' . $product_details['name'], $method_name);
                }
                Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyProductsListing') . '&etsyConf=66');
            } else if (Tools::getValue('action') == 'haltrenew') {
                if (Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET renew_flag = '0', delete_flag = '0', listing_status = 'Expired', is_error = 0 WHERE id_product = '" . (int) $product_details['id_product'] . "'")) {
                    EtsyModule::auditLogEntry('Product deletion stopped. ' . $product_details['name'], $method_name);
                }
                Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyProductsListing') . '&etsyConf=5');
            } else if (Tools::getValue('action') == 'relist') {
                Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_status = 'Relisting', delete_flag = '0', is_error = 0, renew_flag = '1' WHERE  id_product = '" . (int) $product_details['id_product'] . "'");
                EtsyModule::auditLogEntry('Product has been marked for relisting.' . $product_details['name'], $method_name);
                Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyProductsListing') . '&etsyConf=6');
            } else if (Tools::getValue('action') == 'enable') {
                /* Enable the product */
                Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET active = '1' WHERE id_product = '" . (int) $product_details['id_product'] . "'");
                /**
                 * Issue: For the inactive product listing status was showing as "Updated". But it should be "Inactive".
                 * When enabling a product, now fetches the product listing status, Only if the status is "Updated" or "Listed," the module updates the status. Otherwise, not.
                 * TGsep2023 Inactive-Product-Listing 
                 * @date 27-09-2023
                 * @author Tanisha Gupta
                 */
                if ($product_details['listing_status'] == 'Listed' || $product_details['listing_status'] == 'Updated') {
                    Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_status = 'Updated' WHERE id_product = '" . (int) $product_details['id_product'] . "' AND listing_id IS NOT NULL");
                }
                EtsyModule::auditLogEntry('Product enabled.' . $product_details['name'], $method_name);
                Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyProductsListing') . '&etsyConf=64');
            } else if (Tools::getValue('action') == 'disable') {
                /* Disable the product */
                Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET active = '0' WHERE id_product = '" . (int) $product_details['id_product'] . "'");
                EtsyModule::auditLogEntry('Product enabled.' . $product_details['name'], $method_name);
                Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyProductsListing') . '&etsyConf=65');
            }
        } else if (Tools::isSubmit('method')) {
            /**
             * To check if the method is getCustomdetails then get the custom details of the product otherwise the default product details i.e title and descriptions
             * @modifier Pragya Maurya
             * @date 26-05-2024
             * PMMay2024 ebay-custom-product-details
             */
            if (Tools::isSubmit('id_etsy_profile_products')) {

                $data = [];
                $methodType = Tools::getValue('method');
                $id_etsy_profile_products = Tools::getValue('id_etsy_profile_products');
                if ($methodType == 'getCustomdetails') {
                    $profile_product = '';
                    if ($id_etsy_profile_products > 0) {
                        $query_get_product =  'SELECT * FROM ' . _DB_PREFIX_ . 'kb_etsy_profile_product_custom_details  WHERE profile_product_id = ' . (int) $id_etsy_profile_products;
                        $profile_product = Db::getInstance()->getRow($query_get_product);
                        $title = $profile_product['title'] ?? '';
                        $description = $profile_product['description'] ?? '';
                    }

                    $data = [];

                    if (!empty($profile_product)) {
                        //$data = $result;
                        $data['title'] = $title;
                        $data['description'] = $description;
                        $data['profile_product_id'] = (int)$id_etsy_profile_products;
                        $data['flag'] = ($profile_product['custom_status'] == 1) ? false : true;
                    } else {

                        $product_id_query = 'SELECT id_product FROM ' . _DB_PREFIX_ . 'etsy_products_list WHERE id_etsy_products_list = ' . (int)$id_etsy_profile_products;
                        $product_id =  Db::getInstance()->getRow($product_id_query);
                        $language = Configuration::get('etsy_default_lang') != '' ? Configuration::get('etsy_default_lang') : Context::getContext()->language->id;

                        $product_details = KbMarketplaceIntegration::getProductByProductId($product_id['id_product'], (int) $language);

                        if (!empty($product_details)) {
                            $data['title'] = $product_details->name;
                            $data['description'] = $product_details->description;
                            $data['profile_product_id'] = (int)$id_etsy_profile_products;
                            $data['flag'] = true;
                        } else {
                            $data['title'] = '';
                            $data['description'] = '';
                            $data['profile_product_id'] = (int)$id_etsy_profile_products;
                            $data['flag'] = true;
                        }
                    }
                    header('Content-Type: application/json');
                    die(json_encode($data));
                }

                /**
                 * To check if the method is updatecustomdetails then update the custom details of the product details and in case if defaultdetails is 1 then delete the custom details so that the default details can be shown and used
                 * @modifier Pragya Maurya
                 * @date 26-05-2024
                 * PMMay2024 ebay-custom-product-details
                 */
                if ($methodType == 'updatecustomdetails') {
                    $query_get_product_exists = 'SELECT * FROM ' . _DB_PREFIX_ . 'kb_etsy_profile_product_custom_details  WHERE profile_product_id = ' . (int)(Tools::getValue('id_etsy_profile_product_custom'));

                    $profile_product = Db::getInstance()->getRow($query_get_product_exists);
                    if (Tools::getValue('defaultdetails') == 1) {
                        if (!empty($profile_product)) {
                            Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'kb_etsy_profile_product_custom_details set custom_status = "0" WHERE profile_product_id = ' . (int)(Tools::getValue('id_etsy_profile_product_custom')));
                        }
                    } else {
                        if (!empty($profile_product)) {

                            Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'kb_etsy_profile_product_custom_details SET `title` = "' . pSQL(Tools::getValue('product_custom_title')) . '", `description` = "' . pSQL(Tools::getValue('product_custom_description')) . '", custom_status = "1"  WHERE profile_product_id = ' . (int)(Tools::getValue('id_etsy_profile_product_custom')));
                            Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_status = 'Updated', renew_flag = '0', is_error = 0, delete_flag = '0' WHERE  id_etsy_products_list = '" . (int)(Tools::getValue('id_etsy_profile_product_custom')) . "'");
                        } else {
                            Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'kb_etsy_profile_product_custom_details SET `title` = "' . pSQL(Tools::getValue('product_custom_title')) . '", `description` = "' . pSQL(Tools::getValue('product_custom_description')) . '" , custom_status = "1", profile_product_id = ' . (int)(Tools::getValue('id_etsy_profile_product_custom')));
                            Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_status = 'Updated', renew_flag = '0', is_error = 0, delete_flag = '0' WHERE  id_etsy_products_list = '" . (int)(Tools::getValue('id_etsy_profile_product_custom')) . "'");
                        }
                    }

                    $success = $this->module->l('Product details has been updated successfully.', 'AdminKbProductsController');
                    $this->context->cookie->__set('kb_redirect_success', $success);


                    $data['success'] = $success;
                    header('Content-Type: application/json');
                    die(json_encode($data));
                }
            }
        } else {
            parent::postProcess();
        }
    }

    //To delete product listing
    public function processDelete()
    {
        $method_name = 'AdminEtsyProductsListing::processDelete()';
        if (!Tools::isEmpty(trim(Tools::getValue('id_etsy_products_list')))) {
            $product_details = Db::getInstance()->getRow("SELECT pl.name, listing_id, epl.id_product FROM " . _DB_PREFIX_ . "etsy_products_list epl, " . _DB_PREFIX_ . "product_lang pl WHERE epl.id_etsy_products_list = '" . (int) Tools::getValue('id_etsy_products_list') . "' AND epl.id_product = pl.id_product AND pl.id_lang = '" . (int) $this->context->language->id . "'");
            if (!empty($product_details)) {
                /* If listing ID is not null, mark for deletion, else set status to pending & active to 0 so that product can be avaliable for listing */
                if (!empty($product_details['listing_id'])) {
                    Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET delete_flag = '1', is_error = 0, renew_flag = '0', listing_status = 'Deletion Pending', active = '0', sold_flag = '0', listing_error = '' WHERE  id_product = '" . (int) $product_details['id_product'] . "'");
                } else {
                    Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET delete_flag = '0', is_error = 0, renew_flag = '0', listing_status = 'Pending', active = '0', sold_flag = '0', listing_error = '' WHERE id_product = '" . (int) $product_details['id_product'] . "'");
                }
                EtsyModule::auditLogEntry('Product ' . $product_details['name'] . ' is marked to set Inactive on etsy', $method_name);
                Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyProductsListing') . '&etsyConf=7');
            } else {
                Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyProductsListing') . '&etsyError=4');
            }
        }
    }

    protected function processBulkDelete()
    {
        $method_name = 'AdminEtsyProductsListing::processBulkDelete()';
        if (is_array($this->boxes) && !empty($this->boxes)) {
            foreach ($this->boxes as $id_etsy_products_list) {
                $product_details = Db::getInstance()->getRow("SELECT pl.name, listing_id, epl.id_product FROM " . _DB_PREFIX_ . "etsy_products_list epl, " . _DB_PREFIX_ . "product_lang pl WHERE epl.id_etsy_products_list = '" . (int) $id_etsy_products_list . "' AND epl.id_product = pl.id_product AND pl.id_lang = '" . (int) $this->context->language->id . "'");
                if (!empty($product_details)) {
                    /* If listing ID is not null, mark for deletion, else set status to pending & active to 0 so that product can be avaliable for listing */
                    if (!empty($product_details['listing_id'])) {
                        Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET delete_flag = '1', is_error = 0, renew_flag = '0', listing_status = 'Deletion Pending', active = '0', sold_flag = '0', listing_error = '' WHERE  id_product = '" . (int) $product_details['id_product'] . "'");
                    } else {
                        Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET delete_flag = '0', is_error = 0, renew_flag = '0', listing_status = 'Pending', active = '0', sold_flag = '0', listing_error = '' WHERE id_product = '" . (int) $product_details['id_product'] . "'");
                    }
                    EtsyModule::auditLogEntry('Product ' . $product_details['name'] . ' is marked to set Inactive on etsy', $method_name);
                }
            }
        }
        Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyProductsListing') . '&etsyConf=7');
    }

    protected function processBulkrelist()
    {
        if (is_array($this->boxes) && !empty($this->boxes)) {
            $dbQuery = Db::getInstance();
            foreach ($this->boxes as $id_etsy_products_list) {
                $selectSQL = "SELECT pl.name, epl.id_product, epl.active  FROM " . _DB_PREFIX_ . "etsy_products_list epl, " . _DB_PREFIX_ . "product_lang pl WHERE epl.active = 1 AND epl.id_etsy_products_list = '" . (int) $id_etsy_products_list . "' AND epl.id_product = pl.id_product AND pl.id_lang = '" . (int) $this->context->language->id . "'";
                $getProductListingDetails = Db::getInstance()->executeS($selectSQL, true, false);
                if ((int) $getProductListingDetails[0]['active']) {
                    if (Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_status = 'Pending', is_error = 0, delete_flag = '0', renew_flag = '0' WHERE  id_product = '" . (int) $getProductListingDetails[0]['id_product'] . "'")) {
                        //Audit Log Entry
                        $auditLogEntryString = 'Listing of Product - <b>' . $getProductListingDetails[0]['name'] . '</b> Resumed Successfully';
                        $auditMethodName = 'AdminEtsyProductsListing::processBulkrelist()';
                        EtsyModule::auditLogEntry($auditLogEntryString, $auditMethodName);
                    }
                }
            }
        }
        Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyProductsListing') . '&etsyConf=6');
    }

    protected function processBulkrevise()
    {
        if (is_array($this->boxes) && !empty($this->boxes)) {
            foreach ($this->boxes as $id_etsy_products_list) {
                $getProductListingDetails = Db::getInstance()->executeS("SELECT pl.name, epl.id_product, epl.active  FROM " . _DB_PREFIX_ . "etsy_products_list epl, " . _DB_PREFIX_ . "product_lang pl WHERE epl.active = 1 AND epl.id_etsy_products_list = '" . (int) $id_etsy_products_list . "' AND epl.id_product = pl.id_product AND pl.id_lang = '" . (int) $this->context->language->id . "'");
                $checkDeleteFlag = Db::getInstance()->executeS("SELECT count(*) as count FROM " . _DB_PREFIX_ . "etsy_products_list WHERE id_etsy_products_list = '" . (int) $id_etsy_products_list . "' AND listing_id != '' AND listing_id != 0 AND listing_id IS NOT NULL AND (delete_flag = '1' OR delete_flag = '2' OR listing_status = 'Inactive')");

                if (!empty($checkDeleteFlag) && ($checkDeleteFlag[0]['count'] != 0) && ((int) $getProductListingDetails[0]['active'])) {
                    if (Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET renew_flag = '1', is_error = 0, delete_flag = '0' WHERE  id_product = '" . (int) $getProductListingDetails[0]['id_product'] . "'")) {
                        $selectid_prod = "SELECT epl.id_product FROM " . _DB_PREFIX_ . "etsy_products_list epl WHERE epl.id_etsy_products_list = '" . (int) $id_etsy_products_list . "'";
                        $getprodid = Db::getInstance()->executeS($selectid_prod, true, false);

                        if (!empty($getprodid)) {
                            $updateSQL = "UPDATE " . _DB_PREFIX_ . "etsy_translation SET status = 'Update', date_updated = NOW() WHERE id_product = '" . (int) $getprodid[0]['id_product'] . "' AND status = 'Listed'";
                            Db::getInstance()->execute($updateSQL);
                        }
                        //Audit Log Entry
                        $auditLogEntryString = 'Revise of Product - <b>' . $getProductListingDetails[0]['name'] . '</b> Recorded Successfully';
                        $auditMethodName = 'AdminEtsyProductsListing::processBulkrevise()';
                        EtsyModule::auditLogEntry($auditLogEntryString, $auditMethodName);
                    }
                } else {
                    //Audit Log Entry
                    $auditLogEntryString = 'Revise of Product - <b>' . $getProductListingDetails[0]['name'] . '</b> Failed';
                    $auditMethodName = 'AdminEtsyProductsListing::processBulkrevise()';
                    EtsyModule::auditLogEntry($auditLogEntryString, $auditMethodName);
                }
            }
        }
        Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyProductsListing') . '&etsyConf=4');
    }

    protected function processBulkhalt()
    {
        if (is_array($this->boxes) && !empty($this->boxes)) {
            $dbQuery = Db::getInstance();
            foreach ($this->boxes as $id_etsy_products_list) {
                $selectSQL = "SELECT pl.name , epl.id_product, epl.active FROM " . _DB_PREFIX_ . "etsy_products_list epl, " . _DB_PREFIX_ . "product_lang pl WHERE epl.active = 1 AND epl.id_etsy_products_list = '" . (int) $id_etsy_products_list . "' AND epl.id_product = pl.id_product AND pl.id_lang = '" . (int) $this->context->language->id . "'";
                $getProductListingDetails = Db::getInstance()->executeS($selectSQL, true, false);
                $selectSQL = "SELECT count(*) as count FROM " . _DB_PREFIX_ . "etsy_products_list WHERE id_etsy_products_list = '" . (int) $id_etsy_products_list . "' AND delete_flag = '1'";
                $checkDeleteFlag = Db::getInstance()->executeS($selectSQL, true, false);

                if (!empty($checkDeleteFlag) && ($checkDeleteFlag[0]['count'] == 0) && ((int) $getProductListingDetails[0]['active'])) {
                    if (Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET renew_flag = '0', is_error = 0 WHERE id_product = '" . (int) $getProductListingDetails[0]['id_product'] . "'")) {
                        //Audit Log Entry
                        $auditLogEntryString = 'Renewal of Product - <b>' . $getProductListingDetails[0]['name'] . '</b> Stopped Successfully';
                        $auditMethodName = 'AdminEtsyProductsListing::processBulkhalt()';
                        EtsyModule::auditLogEntry($auditLogEntryString, $auditMethodName);

                        //                        Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyProductsListing') . '&etsyConf=5');
                    }
                } else {
                    //Audit Log Entry
                    $auditLogEntryString = 'Halt Renewal of Product - <b>' . $getProductListingDetails[0]['name'] . '</b> Failed';
                    $auditMethodName = 'AdminEtsyProductsListing::processBulkhalt()';
                    EtsyModule::auditLogEntry($auditLogEntryString, $auditMethodName);

                    //                    Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyProductsListing') . '&etsyError=3');
                }
            }
        }
        Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyProductsListing') . '&etsyConf=5');
    }

    protected function processBulkactivate()
    {
        if (is_array($this->boxes) && !empty($this->boxes)) {
            $dbQuery = Db::getInstance();
            foreach ($this->boxes as $id_profile_product) {
                $query_get_product = 'SELECT * FROM ' . _DB_PREFIX_ . 'etsy_products_list  WHERE id_etsy_products_list = ' . (int) $id_profile_product;
                $profile_product = Db::getInstance()->executeS($query_get_product);
                $dbQuery->query('UPDATE ' . _DB_PREFIX_ . 'etsy_products_list SET active = "1" WHERE id_product = ' . (int) $profile_product[0]['id_product']);
                Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_status = 'Updated' WHERE id_product = '" . (int) $profile_product[0]['id_product'] . "' AND listing_id IS NOT NULL");
            }
        }
        Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyProductsListing') . '&etsyConf=63');
    }

    protected function processBulkdeactivate()
    {
        if (is_array($this->boxes) && !empty($this->boxes)) {
            $dbQuery = Db::getInstance();
            foreach ($this->boxes as $id_profile_product) {
                $query_get_product = 'SELECT * FROM ' . _DB_PREFIX_ . 'etsy_products_list  WHERE id_etsy_products_list = ' . (int) $id_profile_product;
                $profile_product = Db::getInstance()->executeS($query_get_product);
                $dbQuery->query('UPDATE ' . _DB_PREFIX_ . 'etsy_products_list SET active = "0" WHERE id_product = ' . (int) $profile_product[0]['id_product']);
            }
        }
        Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyProductsListing') . '&etsyConf=63');
    }

    private function getModuleDirUrl()
    {
        $module_dir = '';
        if ($this->checkSecureUrl()) {
            $module_dir = _PS_BASE_URL_SSL_ . __PS_BASE_URI__ . str_replace(_PS_ROOT_DIR_ . '/', '', _PS_MODULE_DIR_);
        } else {
            $module_dir = _PS_BASE_URL_ . __PS_BASE_URI__ . str_replace(_PS_ROOT_DIR_ . '/', '', _PS_MODULE_DIR_);
        }
        return $module_dir;
    }

    private function checkSecureUrl()
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

    public function initPageHeaderToolbar()
    {
        $secure_key = Configuration::get('KBETSY_SECURE_KEY');
        $this->page_header_toolbar_btn['kb_sync_profile_products'] = array(
            'href' => $this->context->link->getModuleLink('kbetsy', 'cron', array('action' => 'localSync', 'secure_key' => $secure_key)),
            'target' => '_blank',
            'desc' => $this->module->l('Local Sync','AdminEtsyProductsListingController'),
            'icon' => 'process-icon-update'
        );
        $this->page_header_toolbar_btn['kb_sync_product_list'] = array(
            'href' => $this->context->link->getModuleLink('kbetsy', 'cron', array('action' => 'syncProductsListing', 'secure_key' => $secure_key)),
            'target' => '_blank',
            'desc' => $this->module->l('Sync Products','AdminEtsyProductsListingController'),
            'icon' => 'process-icon-update'
        );
        $this->page_header_toolbar_btn['kb_sync_product_status'] = array(
            'href' => $this->context->link->getModuleLink('kbetsy', 'cron', array('action' => 'syncProductsListingStatus', 'secure_key' => $secure_key)),
            'target' => '_blank',
            'desc' => $this->module->l('Sync Product Status','AdminEtsyProductsListingController'),
            'icon' => 'process-icon-update'
        );
        parent::initPageHeaderToolbar();
    }
}
