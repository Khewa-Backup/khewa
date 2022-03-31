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

require_once(_PS_MODULE_DIR_ . 'kbetsy/classes/EtsyModule.php');
require_once(_PS_MODULE_DIR_ . 'kbetsy/classes/EtsyProductListing.php');

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
                'filter_key' => 'p!quantity',
                'align' => 'center'
            ),
            'listing_status' => array(
                'title' => $this->module->l('Listing Status', 'AdminEtsyProductsListingController'),
                'type' => 'select',
                'list' => array('Pending' => $this->l('Pending'), 'Listed' => $this->l('Listed'), 'Updated' => $this->l('Updated'), 'Inactive' => $this->l('Inactive'), 'Deletion Pending' => $this->l('Deletion Pending'), 'Expired' => $this->l('Expired'), 'Relisting' => $this->l('Marked for Relist')),
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
                /* 'date_listed' => array(
                  'title' => $this->module->l('Listed On', 'AdminEtsyProductsListingController'),
                  'type' => 'datetime'
                  )
                 */
        );


        $lang = Configuration::get('etsy_default_lang') != '' ? Configuration::get('etsy_default_lang') : Context::getContext()->language->id;

        $this->_select .= 'a.active, sc.quantity, pl.`name`, i.`id_image` as image, ep.profile_title';
        $this->_join .= ' JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (a.`id_product` = pl.`id_product` and pl.id_shop = ' . (int) $this->context->shop->id . ' ) AND id_lang = ' . (int) $lang;
        $this->_join .= ' JOIN `' . _DB_PREFIX_ . 'product` p ON (a.`id_product` = p.`id_product` and p.`active` = 1) ';
        $this->_join .= ' JOIN `' . _DB_PREFIX_ . 'etsy_profiles` ep ON (ep.`id_etsy_profiles` = a.`id_etsy_profiles` and ep.`active` = 1)';
        $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'image_shop` ims ON (a.`id_product` = ims.`id_product` AND ims.`cover` = 1 AND ims.id_shop = ' . (int) $this->context->shop->id . ')';
        $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'stock_available` sc ON p.`id_product` = sc.`id_product` ';
        $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'image` i ON (ims.`id_image` = i.`id_image`)';

        $this->_where.=' AND a.id_etsy_profiles != 0 ';
        $this->_group = 'GROUP BY a.id_etsy_products_list';

        //Line added to remove link from list row
        $this->module->list_no_link = true;
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected items?')
            ),
            /* Commented by Ashish on 1st Nov 2019. We will enable the same later as variation status condition needs to be handled for these
            'relist' => array(
                'text' => $this->l('Relist selected'),
                'icon' => 'icon-refresh',
                'confirm' => $this->l('Relist selected items?')
            ),
            'revise' => array(
                'text' => $this->l('Renew selected'),
                'icon' => 'icon-gear',
                'confirm' => $this->l('Renew selected items?')
            ),
            'halt' => array(
                'text' => $this->l('Halt selected'),
                'icon' => 'icon-ban',
                'confirm' => $this->l('Halt selected items?')
            ),
            */
            'activate' => array(
                'text' => $this->l('Enable selected'),
                'icon' => 'icon-power-off text-success',
                'confirm' => $this->l('Enable selected items?')
            ),
            'deactivate' => array(
                'text' => $this->l('Disable selected'),
                'icon' => 'icon-power-off text-danger',
                'confirm' => $this->l('Disable selected items?')
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
        return '<a href="'.$url.'" target="_blank">'.$row_data.'</a>';
    }
    /** Callback function to admin product edit in the helper list */
    public function showProductAdminUrl($data, $row_data)
    {
        return "<a target='_blank' href='" . $this->context->link->getAdminlink('AdminProducts', true, array("id_product" => $row_data['id_product'])) . "'>" . $data . "</a>";
    }
    
    public function showEtsyProductUrl($data, $row_data)
    {
        if (!empty($data)) {
            return "<a target='_blank' href='https://www.etsy.com/listing/".$data."'>" . $data . "</a>";
        } else {
            return $data;
        }
    }
    

    /** Callback function to display listing status in the helper list */
    public function getTranslatedListingStatus($status, $row_data)
    {
        $status_array = array('Pending' => $this->l('Pending'), 'Disabled' => $this->l('Disabled'), 'Updated' => $this->l('Updated'), 'Deletion Pending' => $this->l('Deletion Pending'), 'Listed' => $this->l('Listed'), 'Inactive' => $this->l('Inactive'), 'Expired' => $this->l('Expired'), 'Sold Out' => $this->l('Sold Out'), 'Relisting' => $this->l('Marked for Relist'));
        return $status_array[$status];
    }

    /** Callback function to display enabled status in the helper list */
    public function getTranslatedEnabledStatus($status, $row_data)
    {
        $status_array = array('0' => $this->l('No'), '1' => $this->l('Yes'));
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
        $this->addCSS($this->getModuleDirUrl() . 'kbetsy/views/css/style.css');
    }

    public function renderList()
    {
        $this->addRowAction('error');
        $this->addRowAction('sync');
        $this->addRowAction('renew');
        $this->addRowAction('status');
        $this->addRowAction('delete');

        $this->context->smarty->assign("message", $this->module->l('In case you want to set the products as Inactive, Delete those products from this page only (Instead from the Etsy account). Setting the product as Inactive on the Etsy account directly will relist the item on the Etsy on next CRON run.', 'AdminEtsyAttributeMappingController'));
        $this->context->smarty->assign("type", "alert-warning");
        $this->context->smarty->assign("KbMessageLink", '');
        $instrction_1 = $this->context->smarty->fetch(_PS_MODULE_DIR_ . "kbetsy/views/templates/admin/msgs.tpl");

        $this->context->smarty->assign("message", $this->module->l('Kindly do not delete the listed products from the Etsy account directly. If you want to delete the items from the Etsy, Kindly delete the same from the module itself.', 'AdminEtsyAttributeMappingController'));
        $this->context->smarty->assign("type", "alert-warning");
        $this->context->smarty->assign("KbMessageLink", '');
        $instrction_2 = $this->context->smarty->fetch(_PS_MODULE_DIR_ . "kbetsy/views/templates/admin/msgs.tpl");
        
        $remaining_limit = Configuration::get('KBETSY_REMAINING_LIMIT');
        if (!empty($remaining_limit)) {
            $this->context->smarty->assign("message", $this->module->l('Etsy API daily limit remaining: '.$remaining_limit, 'AdminEtsyAttributeMappingController'));
            $this->context->smarty->assign("type", "alert-info");
            $this->context->smarty->assign("KbMessageLink", '');
            $msgs = $this->context->smarty->fetch(_PS_MODULE_DIR_ . "kbetsy/views/templates/admin/msgs.tpl");
            return $msgs . parent::renderList().$instrction_1.$instrction_2;
        } else {
            return parent::renderList().$instrction_1.$instrction_2;
        }
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

    /** Display view listing error link */
    public function displaySyncLink($token = null, $id = null, $name = null)
    {
        $secure_key = Configuration::get('KBETSY_SECURE_KEY');
        
        $productDetails = DB::getInstance()->getRow("SELECT id_product, active, listing_id, listing_status FROM " . _DB_PREFIX_ . "etsy_products_list WHERE id_etsy_products_list = '" . (int) $id . "'", true, false);
        if (!empty($productDetails)) {
            if ($productDetails['active'] == 1) {
                if ($productDetails['listing_id'] == null || $productDetails['listing_status'] == 'Updated') {
                    $this->context->smarty->assign(array(
                        'href' => $this->context->link->getModuleLink('kbetsy', 'cron', array('action' => 'syncProductsListing', 'id_product' => $productDetails['id_product'], 'secure_key' => $secure_key)),
                        'action' => $this->l('Sync'),
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
        $productDetails = DB::getInstance()->getRow("SELECT * FROM " . _DB_PREFIX_ . "etsy_products_list WHERE active = 1 and listing_id != '' AND listing_id != 0 AND listing_id IS NOT NULL AND id_product_attribute = '0' AND id_etsy_products_list = '" . (int) $id . "'");
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
                    'action' => $this->l('Revise'),
                    'icon' => 'refresh'
                ));
                return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbetsy/views/templates/admin/list/list_action.tpl');
            }
        } else {
            $this->context->smarty->assign(array(
                'href' => $this->context->link->getAdminlink('AdminEtsyProductsListing') . '&' . $this->identifier . '=' . $id . '&action=halt',
                'action' => $this->l('Stop Deletion'),
                'icon' => 'ban'
            ));

            return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbetsy/views/templates/admin/list/list_action.tpl');
        }
    }

    /** Display view action link  */
    public function displayDeleteLink($token = null, $id = null, $name = null)
    {
        $productDetails = DB::getInstance()->getRow("SELECT * FROM " . _DB_PREFIX_ . "etsy_products_list WHERE id_product_attribute = '0' AND id_etsy_products_list = '" . (int) $id . "'");

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
                'action' => $this->l('Relist'),
                'icon' => 'list'
            ));
            return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbetsy/views/templates/admin/list/list_action.tpl');
        } else {
            $this->context->smarty->assign(array(
                'href' => $this->context->link->getAdminlink('AdminEtsyProductsListing') . '&' . $this->identifier . '=' . $id . '&delete' . $this->table,
                'action' => $this->l('Delete'),
                'icon' => 'trash',
                'warning_message' => $this->l('Are you sure to delete the item? Item status wil be set as Inactive in the etsy account.')
            ));
            return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbetsy/views/templates/admin/list/list_action_confirmation.tpl');
        }
    }

    /** Display view listing error link */
    public function displayErrorLink($token = null, $id = null, $name = null)
    {
        $productDetails = DB::getInstance()->getRow("SELECT listing_error, active FROM " . _DB_PREFIX_ . "etsy_products_list WHERE id_etsy_products_list = '" . (int) $id . "'", true, false);
        if (!empty($productDetails['listing_error'])) {
            if ($productDetails['active'] == 1) {
                $this->context->smarty->assign(array(
                    'href' => 'etsy-error-' . $id,
                    'action' => $this->l('View Error'),
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
        $productDetails = DB::getInstance()->getRow("SELECT active, listing_id FROM " . _DB_PREFIX_ . "etsy_products_list WHERE id_etsy_products_list = '" . (int) $id . "'", true, false);
        if (!empty($productDetails)) {
            if ($productDetails['active'] == 1) {
                if (empty($productDetails['listing_id'])) {
                    $this->context->smarty->assign(array(
                        'href' => $this->context->link->getAdminlink('AdminEtsyProductsListing') . '&' . $this->identifier . '=' . $id . '&action=disable',
                        'action' => $this->l('Disable'),
                        'icon' => 'power-off text-danger'
                    ));
                } else {
                    return '';
                }
            } else {
                $this->context->smarty->assign(array(
                    'href' => $this->context->link->getAdminlink('AdminEtsyProductsListing') . '&' . $this->identifier . '=' . $id . '&action=enable',
                    'action' => $this->l('Enable'),
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
            $product_details = DB::getInstance()->getRow("SELECT pl.name, epl.id_product, epl.listing_id  FROM " . _DB_PREFIX_ . "etsy_products_list epl, " . _DB_PREFIX_ . "product_lang pl WHERE epl.id_etsy_products_list = '" . (int) Tools::getValue('id_etsy_products_list') . "' AND epl.id_product = pl.id_product AND pl.id_lang = '" . (int) $this->context->language->id . "'");
            if (Tools::getValue('action') == 'revise') {
                DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_status = 'Updated', renew_flag = '0', is_error = 0, delete_flag = '0' WHERE  id_product = '" . (int) $product_details['id_product'] . "'");
                $auditLogEntryString = 'Revise of product recorded successfully';
                EtsyModule::auditLogEntry($auditLogEntryString, $method_name);
                Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyProductsListing') . '&etsyConf=4');
            } else if (Tools::getValue('action') == 'halt') {
                $checkDeleteFlag = DB::getInstance()->getValue("SELECT count(*) as count FROM " . _DB_PREFIX_ . "etsy_products_list WHERE id_etsy_products_list = '" . (int) Tools::getValue('id_etsy_products_list') . "' AND delete_flag = '1'");
                if (DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET renew_flag = '0', delete_flag = '0', listing_status = 'Listed', is_error = 0 WHERE id_product = '" . (int) $product_details['id_product'] . "'")) {
                    EtsyModule::auditLogEntry('Product deletion stopped. ' . $product_details['name'], $method_name);
                }
                Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyProductsListing') . '&etsyConf=66');
            } else if (Tools::getValue('action') == 'haltrenew') {
                if (DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET renew_flag = '0', delete_flag = '0', listing_status = 'Expired', is_error = 0 WHERE id_product = '" . (int) $product_details['id_product'] . "'")) {
                    EtsyModule::auditLogEntry('Product deletion stopped. ' . $product_details['name'], $method_name);
                }
                Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyProductsListing') . '&etsyConf=5');
            } else if (Tools::getValue('action') == 'relist') {
                DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_status = 'Relisting', delete_flag = '0', is_error = 0, renew_flag = '1' WHERE  id_product = '" . (int) $product_details['id_product'] . "'");
                EtsyModule::auditLogEntry('Product has been marked for relisting.' . $product_details['name'], $method_name);
                Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyProductsListing') . '&etsyConf=6');
            } else if (Tools::getValue('action') == 'enable') {
                /* Enable the product */
                DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET active = '1' WHERE id_product = '" . (int) $product_details['id_product'] . "'");
                DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_status = 'Updated' WHERE id_product = '" . (int) $product_details['id_product'] . "' AND listing_id IS NOT NULL");
                EtsyModule::auditLogEntry('Product enabled.' . $product_details['name'], $method_name);
                Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyProductsListing') . '&etsyConf=64');
            } else if (Tools::getValue('action') == 'disable') {
                /* Disable the product */
                DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET active = '0' WHERE id_product = '" . (int) $product_details['id_product'] . "'");
                EtsyModule::auditLogEntry('Product enabled.' . $product_details['name'], $method_name);
                Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyProductsListing') . '&etsyConf=65');
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
            $product_details = DB::getInstance()->getRow("SELECT pl.name, listing_id, epl.id_product FROM " . _DB_PREFIX_ . "etsy_products_list epl, " . _DB_PREFIX_ . "product_lang pl WHERE epl.id_etsy_products_list = '" . (int) Tools::getValue('id_etsy_products_list') . "' AND epl.id_product = pl.id_product AND pl.id_lang = '" . (int) $this->context->language->id . "'");
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
                $product_details = DB::getInstance()->getRow("SELECT pl.name, listing_id, epl.id_product FROM " . _DB_PREFIX_ . "etsy_products_list epl, " . _DB_PREFIX_ . "product_lang pl WHERE epl.id_etsy_products_list = '" . (int) $id_etsy_products_list . "' AND epl.id_product = pl.id_product AND pl.id_lang = '" . (int) $this->context->language->id . "'");
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
                $getProductListingDetails = DB::getInstance()->executeS($selectSQL, true, false);
                if ((int) $getProductListingDetails[0]['active']) {
                    if (DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_status = 'Pending', is_error = 0, delete_flag = '0', renew_flag = '0' WHERE  id_product = '" . (int) $getProductListingDetails[0]['id_product'] . "'")) {
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
                $getProductListingDetails = DB::getInstance()->executeS("SELECT pl.name, epl.id_product, epl.active  FROM " . _DB_PREFIX_ . "etsy_products_list epl, " . _DB_PREFIX_ . "product_lang pl WHERE epl.active = 1 AND epl.id_etsy_products_list = '" . (int) $id_etsy_products_list . "' AND epl.id_product = pl.id_product AND pl.id_lang = '" . (int) $this->context->language->id . "'");
                $checkDeleteFlag = DB::getInstance()->executeS("SELECT count(*) as count FROM " . _DB_PREFIX_ . "etsy_products_list WHERE id_etsy_products_list = '" . (int) $id_etsy_products_list . "' AND listing_id != '' AND listing_id != 0 AND listing_id IS NOT NULL AND (delete_flag = '1' OR delete_flag = '2' OR listing_status = 'Inactive')");

                if (!empty($checkDeleteFlag) && ($checkDeleteFlag[0]['count'] != 0) && ((int) $getProductListingDetails[0]['active'])) {
                    if (DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET renew_flag = '1', is_error = 0, delete_flag = '0' WHERE  id_product = '" . (int) $getProductListingDetails[0]['id_product'] . "'")) {
                        $selectid_prod = "SELECT epl.id_product FROM " . _DB_PREFIX_ . "etsy_products_list epl WHERE epl.id_etsy_products_list = '" . (int) $id_etsy_products_list . "'";
                        $getprodid = DB::getInstance()->executeS($selectid_prod, true, false);

                        if (!empty($getprodid)) {
                            $updateSQL = "UPDATE " . _DB_PREFIX_ . "etsy_translation SET status = 'Update', date_updated = NOW() WHERE id_product = '" . (int) $getprodid[0]['id_product'] . "' AND status = 'Listed'";
                            DB::getInstance()->execute($updateSQL);
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
                $getProductListingDetails = DB::getInstance()->executeS($selectSQL, true, false);
                $selectSQL = "SELECT count(*) as count FROM " . _DB_PREFIX_ . "etsy_products_list WHERE id_etsy_products_list = '" . (int) $id_etsy_products_list . "' AND delete_flag = '1'";
                $checkDeleteFlag = DB::getInstance()->executeS($selectSQL, true, false);

                if (!empty($checkDeleteFlag) && ($checkDeleteFlag[0]['count'] == 0) && ((int) $getProductListingDetails[0]['active'])) {
                    if (DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET renew_flag = '0', is_error = 0 WHERE id_product = '" . (int) $getProductListingDetails[0]['id_product'] . "'")) {
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
                DB::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_products_list SET listing_status = 'Updated' WHERE id_product = '" . (int) $profile_product[0]['id_product'] . "' AND listing_id IS NOT NULL");
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
            'desc' => $this->l('Local Sync'),
            'icon' => 'process-icon-update'
        );
        $this->page_header_toolbar_btn['kb_sync_product_list'] = array(
            'href' => $this->context->link->getModuleLink('kbetsy', 'cron', array('action' => 'syncProductsListing', 'secure_key' => $secure_key)),
            'target' => '_blank',
            'desc' => $this->l('Sync Products'),
            'icon' => 'process-icon-update'
        );
        $this->page_header_toolbar_btn['kb_sync_product_status'] = array(
            'href' => $this->context->link->getModuleLink('kbetsy', 'cron', array('action' => 'syncProductsListingStatus', 'secure_key' => $secure_key)),
            'target' => '_blank',
            'desc' => $this->l('Sync Product Status'),
            'icon' => 'process-icon-update'
        );
        parent::initPageHeaderToolbar();
    }
}
