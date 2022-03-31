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
require_once(_PS_MODULE_DIR_ . 'kbetsy/classes/EtsyShopSection.php');

class AdminEtsyShopSectionController extends ModuleAdminController
{

    //Class Constructor
    public function __construct()
    {
        $this->name = 'EtsyShopSection';
        $this->context = Context::getContext();
        $this->bootstrap = true;
        $this->table = 'etsy_shop_section';
        $this->className = 'EtsyShopSection';
        $this->identifier = 'id_etsy_shop_section';
        
        parent::__construct();
        
        $this->fields_list = array(
            'id_etsy_shop_section' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs'
            ),
            'shop_section_title' => array(
                'title' => $this->l('Shop Section Title'),
            ),
            'shop_section_id' => array(
                'title' => $this->l('Section ID'),
            ),
            'shop_section_date_update' => array(
                'title' => $this->l('Last Updated Date'),
            )
        );

        $this->_where = " = 1 AND delete_flag = '0'";

        //Line added to remove link from list row
        $this->list_no_link = true;

        //This is to show notification messages to admin
        if (!Tools::isEmpty(trim(Tools::getValue('etsyConf')))) {
            new EtsyModule(Tools::getValue('etsyConf'), 'conf');
        }

        if (!Tools::isEmpty(trim(Tools::getValue('etsyError')))) {
            new EtsyModule(Tools::getValue('etsyError'), 'error');
        }
    }

    //Set JS and CSS
    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $this->addJS($this->getModuleDirUrl() . 'kbetsy/views/js/script.js');
        $this->addJS($this->getModuleDirUrl() . 'kbetsy/views/js/velovalidation.js');
        $this->addCSS($this->getModuleDirUrl() . 'kbetsy/views/css/style.css');
    }

    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        if (EtsyShopSection::getTotalShopSections() <= 0) {
            $this->context->smarty->assign("message", $this->module->l('Shop section has not been added yet. Click on the "Add new" icon to add the same OR click on the "Sync Shop Sections" icon to download the existing Etsy shop section from the Etsy account.', 'AdminEtsyShopSectionController'));
            $this->context->smarty->assign("type", "alert-info");
            $this->context->smarty->assign("KbMessageLink", '');
            $msgs = $this->context->smarty->fetch(_PS_MODULE_DIR_ . "kbetsy/views/templates/admin/msgs.tpl");
            return $msgs;
        } else {
            return parent::renderList();
        }
    }

    /** Render a form */
    public function renderForm()
    {
        $this->fields_form = array(
            'legend' => array(
                'title' => !Tools::isEmpty(trim(Tools::getValue('id_etsy_shop_section'))) ? $this->l('Update Shop Section') : $this->l('Add New Shop Section'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'hidden',
                    'name' => 'id_etsy_shop_section'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Shop Section Title'),
                    'desc' => $this->l('Provide Shop Section Title. Maximum 24 Characters Long.'),
                    'name' => 'shop_section_title',
                    'maxlength' => 24,
                    'required' => true
                ),
            ),
            'buttons' => array(
                array(
                    'class' => 'btn btn-default pull-right',
                    'name' => 'submit' . $this->name,
                    'js' => "validation('etsy_shop_section_form')",
                    'title' => $this->l('Save'),
                    'icon' => 'process-icon-save'
                )
            )
        );

        //Code for Form Editing
        if (!Tools::isEmpty(trim(Tools::getValue('id_etsy_shop_section')))) {
            $getShopSectionDetails = EtsyShopSection::getShopSectionDetails(Tools::getValue('id_etsy_shop_section'));

            if (isset($getShopSectionDetails)) {
                $this->fields_value = array(
                    'id_etsy_shop_section' => Tools::getValue('id_etsy_shop_section'),
                    'shop_section_title' => $getShopSectionDetails['shop_section_title'],
                );
            }
        }
        return parent::renderForm();
    }

    public function postProcess()
    {
        $method_name = 'AdminEtsyShopSection::postProcess()';

        //Handle Form Submission
        if (Tools::isSubmit('submitAddetsy_shop_section')) {
            //Prepare variables holding  post values
            $shopSectionTitle = pSQL(Tools::getValue('shop_section_title'));

            if (!Tools::isEmpty(trim(Tools::getValue('id_etsy_shop_section')))) {
                $dataExistenceResult = Db::getInstance()->getRow("SELECT * FROM " . _DB_PREFIX_ . "etsy_shop_section WHERE shop_section_title = '" . pSQL($shopSectionTitle) . "' AND delete_flag = '0' AND id_etsy_shop_section != '" . (int) Tools::getValue('id_etsy_shop_section') . "'");
                if (empty($dataExistenceResult)) {
                    $shopSectionDetails = EtsyShopSection::getShopSectionDetails(Tools::getValue('id_etsy_shop_section'));
                    
                    Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_shop_section SET shop_section_title = '" . pSQL($shopSectionTitle) . "', renew_flag = '1' WHERE id_etsy_shop_section = '" . (int) Tools::getValue('id_etsy_shop_section') . "'");

                    $log_entry = 'Shop section updated. Updated value: <br>Shop Section Title: ' . $shopSectionTitle;
                    EtsyModule::auditLogEntry($log_entry, $method_name);

                    /** Update value on Etsy */
                    SyncShopSection::updateShopSection(array("shop_section_title" => $shopSectionTitle, "id_etsy_shop_section" => Tools::getValue('id_etsy_shop_section'), "shop_section_id" => $shopSectionDetails['shop_section_id']));

                    Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyShopSection') . '&etsyConf=61');
                } else {
                    Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyShopSection') . '&etsyError=64');
                }
            } else {
                $dataExistenceResult = Db::getInstance()->getRow("SELECT * FROM " . _DB_PREFIX_ . "etsy_shop_section WHERE shop_section_title = '" . pSQL($shopSectionTitle) . "' AND delete_flag = '0'");
                if (empty($dataExistenceResult)) {
                    Db::getInstance()->execute("INSERT INTO " . _DB_PREFIX_ . "etsy_shop_section (shop_section_title, shop_section_date_added, shop_section_date_update) VALUES ('" . pSQL($shopSectionTitle) . "', NOW(), NOW())");
                    $shop_section_id = Db::getInstance()->Insert_ID();

                    $log_entry = 'Shop section added. Added value: <br>Shop Section Title: ' . $shopSectionTitle;
                    EtsyModule::auditLogEntry($log_entry, $method_name);
                    
                    /** Sync value on Etsy */
                    SyncShopSection::createShopSection(array("shop_section_title" => $shopSectionTitle, "id_etsy_shop_section" => $shop_section_id));

                    Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyShopSection') . '&etsyConf=60');
                } else {
                    Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyShopSection') . '&etsyError=64');
                }
            }
        } else {
            parent::postProcess();
        }
        $this->content = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbetsy/views/templates/admin/velovalidation.tpl');
    }

    public function processDelete()
    {
        $method_name = 'AdminEtsyShopSection::processDelete()';

        if (!Tools::isEmpty(trim(Tools::getValue('id_etsy_shop_section')))) {
            $shopSectionDetails = EtsyShopSection::getShopSectionDetails(Tools::getValue('id_etsy_shop_section'));

            //Check if shop section is mapped with profile
            $profileMapping = Db::getInstance()->getValue("SELECT count(*) as count FROM " . _DB_PREFIX_ . "etsy_profiles WHERE id_etsy_shop_section = '" . (int) Tools::getValue('id_etsy_shop_section') . "'", true, false);

            if ($profileMapping == 0) {
                /* If Etsy Shop Section ID exists then delete the same from the Etsy first else directly delete from the DB */
                if (is_null($shopSectionDetails['shop_section_id'])) {
                    $result = Db::getInstance()->execute("DELETE FROM " . _DB_PREFIX_ . "etsy_shop_section WHERE id_etsy_shop_section = '" . (int) Tools::getValue('id_etsy_shop_section') . "'");
                    if ($result) {
                        $log_string = 'Shop section deleted: ' . $shopSectionDetails['shop_section_title'];
                        EtsyModule::auditLogEntry($log_string, $method_name);

                        Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyShopSection') . '&etsyConf=62');
                    } else {
                        $log_string = 'Deletion of shop section failed: ' . $shopSectionDetails['shop_section_title'];
                        EtsyModule::auditLogEntry($log_string, $method_name);

                        Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyShopSection') . '&etsyError=58');
                    }
                } else {
                    /** If System is not able to delete teh Shop section from Etsy then Set the delete flag to 1 to delete the Shop section via CRON */
                    $result = SyncShopSection::deleteShopSection(array("shop_section_title" => $shopSectionDetails['shop_section_title'], "id_etsy_shop_section" => Tools::getValue('id_etsy_shop_section'), "shop_section_id" => $shopSectionDetails['shop_section_id']));
                    if (!$result) {
                        Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_shop_section SET delete_flag = '1' WHERE id_etsy_shop_section = '" . (int) Tools::getValue('id_etsy_shop_section') . "'");

                        $log_string = 'Shop Section marked for deletion: ' . $shopSectionDetails['shop_section_title'];
                        EtsyModule::auditLogEntry($log_string, $method_name);
                    }
                    Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyShopSection') . '&etsyConf=62');
                }
            } else {
                $log_string = 'Deletion of shop section failed.' . $shopSectionDetails['shop_section_title'];
                EtsyModule::auditLogEntry($log_string, $method_name);

                Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyShopSection') . '&etsyError=65');
            }
        }
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
        if (!Tools::getValue('id_etsy_shop_section') && !Tools::isSubmit('addetsy_shop_section')) {
            $this->page_header_toolbar_btn['new_template'] = array(
                'href' => self::$currentIndex . '&add' . $this->table . '&token=' . $this->token,
                'desc' => $this->l('Add new'),
                'icon' => 'process-icon-new'
            );
            $secure_key = Configuration::get('KBETSY_SECURE_KEY');
            $this->page_header_toolbar_btn['kb_sync_shopsections'] = array(
                'href' => $this->context->link->getModuleLink('kbetsy', 'cron', array(
                    'action' => 'syncShopSections',
                    'secure_key' => $secure_key)),
                'target' => '_blank',
                'desc' => $this->l('Sync Shop Sections'),
                'icon' => 'process-icon-update'
            );
        }
        if (Tools::getValue('id_etsy_shop_section') || Tools::isSubmit('id_etsy_shop_section') || Tools::isSubmit('addetsy_shop_section')) {
            $this->page_header_toolbar_btn['kb_cancel_action'] = array(
                'href' => self::$currentIndex . '&token=' . $this->token,
                'desc' => $this->l('Cancel'),
                'icon' => 'process-icon-cancel'
            );
        }

        parent::initPageHeaderToolbar();
    }
}
