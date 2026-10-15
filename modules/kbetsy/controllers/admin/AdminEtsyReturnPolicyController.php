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
require_once(_PS_MODULE_DIR_ . 'kbetsy/classes/EtsyReturnPolicy.php');
require_once(_PS_MODULE_DIR_ . 'kbetsy/classes/SyncReturnPolicy.php');

/*
 * Created AdminEtsyReturnPolicyController for return policy management similar to AdminEtsyShopSectionController
 * @modifier Himanshu Vishwakarma
 * @date 15-12-2025
 */
class AdminEtsyReturnPolicyController extends ModuleAdminController
{

    //Class Constructor
    public function __construct()
    {
        $this->name = 'EtsyReturnPolicy';
        $this->context = Context::getContext();
        $this->bootstrap = true;
        $this->table = 'etsy_return_policy';
        $this->className = 'EtsyReturnPolicy';
        $this->identifier = 'id_etsy_return_policy';
        
        parent::__construct();
        
        /*
         * Updated fields_list to show only: return_policy_id, accepts_returns, accepts_exchanges, return_deadline
	 * @modifier Himanshu Vishwakarma
         * @date 15-12-2025
         */
        $this->fields_list = array(
            'return_policy_id' => array(
                'title' => $this->module->l('Return Policy ID','AdminEtsyReturnPolicyController'),
            ),
            'accepts_returns' => array(
                'title' => $this->module->l('Accepts Returns','AdminEtsyReturnPolicyController'),
            ),
            'accepts_exchanges' => array(
                'title' => $this->module->l('Accepts Exchanges','AdminEtsyReturnPolicyController'),
            ),
            'return_deadline' => array(
                'title' => $this->module->l('Return Deadline (days)','AdminEtsyReturnPolicyController'),
            )
        );

        $this->_where = " = 1";

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
        if (EtsyReturnPolicy::getTotalReturnPolicies() <= 0) {
            /*
             * Updated message to match shop section format when no return policies exist
	     * @modifier Himanshu Vishwakarma
             * @date 15-12-2025
             */
            $this->context->smarty->assign("message", $this->module->l('Return policy has not been added yet. Click on the "Add new" icon to add the same OR click on the "Sync Return Policies" icon to download the existing Etsy return policy from the Etsy account.', 'AdminEtsyReturnPolicyController'));
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
                'title' => !Tools::isEmpty(trim(Tools::getValue('id_etsy_return_policy'))) ? $this->module->l('Update Return Policy','AdminEtsyReturnPolicyController') : $this->module->l('Add New Return Policy','AdminEtsyReturnPolicyController'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'hidden',
                    'name' => 'id_etsy_return_policy'
                ),
                /*
                 * Updated form to show only three fields: accepts_returns, accepts_exchanges, return_deadline
                 * return_policy_id and shop_id will be handled automatically
		 * @modifier Himanshu Vishwakarma
                 * @date 15-12-2025
                 */
                array(
                    'type' => 'switch',
                    'label' => $this->module->l('Accepts Returns','AdminEtsyReturnPolicyController'),
                    'desc' => $this->module->l('Indicates whether the shop accepts product returns.','AdminEtsyReturnPolicyController'),
                    'name' => 'accepts_returns',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'accepts_returns_on',
                            'value' => 1,
                            'label' => $this->module->l('Yes', 'AdminEtsyReturnPolicyController')
                        ),
                        array(
                            'id' => 'accepts_returns_off',
                            'value' => 0,
                            'label' => $this->module->l('No', 'AdminEtsyReturnPolicyController')
                        )
                    )
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->module->l('Accepts Exchanges','AdminEtsyReturnPolicyController'),
                    'desc' => $this->module->l('Indicates whether the shop accepts exchanges (e.g., size, color, or replacement instead of a refund).','AdminEtsyReturnPolicyController'),
                    'name' => 'accepts_exchanges',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'accepts_exchanges_on',
                            'value' => 1,
                            'label' => $this->module->l('Yes', 'AdminEtsyReturnPolicyController')
                        ),
                        array(
                            'id' => 'accepts_exchanges_off',
                            'value' => 0,
                            'label' => $this->module->l('No', 'AdminEtsyReturnPolicyController')
                        )
                    )
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Return Deadline','AdminEtsyReturnPolicyController'),
                    'desc' => $this->module->l('Specifies the number of days after delivery within which a buyer can request a return or exchange.','AdminEtsyReturnPolicyController'),
                    'name' => 'return_deadline',
                    'required' => true,
                    'options' => array(
                        'query' => array(
                            array('id' => '0', 'name' => $this->module->l('No deadline', 'AdminEtsyReturnPolicyController')),
                            array('id' => '14', 'name' => $this->module->l('14 days', 'AdminEtsyReturnPolicyController')),
                            array('id' => '21', 'name' => $this->module->l('21 days', 'AdminEtsyReturnPolicyController')),
                            array('id' => '30', 'name' => $this->module->l('30 days', 'AdminEtsyReturnPolicyController')),
                            array('id' => '45', 'name' => $this->module->l('45 days', 'AdminEtsyReturnPolicyController')),
                            array('id' => '60', 'name' => $this->module->l('60 days', 'AdminEtsyReturnPolicyController')),
                            array('id' => '90', 'name' => $this->module->l('90 days', 'AdminEtsyReturnPolicyController'))
                        ),
                        'id' => 'id',
                        'name' => 'name'
                    )
                ),
            ),
            'buttons' => array(
                array(
                    'class' => 'btn btn-default pull-right',
                    'name' => 'submit' . $this->name,
                    'js' => "validation('etsy_return_policy_form')",
                    'title' => $this->module->l('Save','AdminEtsyReturnPolicyController'),
                    'icon' => 'process-icon-save'
                )
            )
        );

        //Code for Form Editing
        if (!Tools::isEmpty(trim(Tools::getValue('id_etsy_return_policy')))) {
            $getReturnPolicyDetails = EtsyReturnPolicy::getReturnPolicyDetails(Tools::getValue('id_etsy_return_policy'));

            if (isset($getReturnPolicyDetails)) {
                $this->fields_value = array(
                    'id_etsy_return_policy' => Tools::getValue('id_etsy_return_policy'),
                    'return_policy_id' => isset($getReturnPolicyDetails['return_policy_id']) ? $getReturnPolicyDetails['return_policy_id'] : '',
                    'shop_id' => isset($getReturnPolicyDetails['shop_id']) ? $getReturnPolicyDetails['shop_id'] : '',
                    'accepts_returns' => isset($getReturnPolicyDetails['accepts_returns']) ? (int)$getReturnPolicyDetails['accepts_returns'] : 0,
                    'accepts_exchanges' => isset($getReturnPolicyDetails['accepts_exchanges']) ? (int)$getReturnPolicyDetails['accepts_exchanges'] : 0,
                    'return_deadline' => isset($getReturnPolicyDetails['return_deadline']) ? (int)$getReturnPolicyDetails['return_deadline'] : 0,
                );
            }
        } else {
            //Set default values for new return policy - only the three visible fields
            $this->fields_value = array(
                'accepts_returns' => 0,
                'accepts_exchanges' => 0,
                'return_deadline' => 0,
            );
        }

        return parent::renderForm();
    }

    public function postProcess()
    {
        $method_name = 'AdminEtsyReturnPolicy::postProcess()';

        //Handle Form Submission
        if (Tools::isSubmit('submitAddetsy_return_policy')) {
            //Prepare variables holding post values - only required fields: return_policy_id, shop_id, accepts_returns, accepts_exchanges, return_deadline
            /*
             * Updated to use only required fields as per Etsy API: return_policy_id, shop_id, accepts_returns, accepts_exchanges, return_deadline
             * @modifier Himanshu Vishwakarma
	     * @date 15-12-2025
             */
            $returnPolicyId = pSQL(Tools::getValue('return_policy_id', ''));
            $shopId = pSQL(Tools::getValue('shop_id', ''));
            $acceptsReturns = (int)Tools::getValue('accepts_returns', 0);
            $acceptsExchanges = (int)Tools::getValue('accepts_exchanges', 0);
            $returnDeadline = (int)Tools::getValue('return_deadline', 0);

            //Get shop_id from Etsy if not provided
            if (empty($shopId)) {
                $shop = EtsyModule::etsyGetShopDetails();
                $shopId = isset($shop['shop_id']) ? pSQL($shop['shop_id']) : '';
            }

            if (!Tools::isEmpty(trim(Tools::getValue('id_etsy_return_policy')))) {
                //Update existing return policy
                $returnPolicyDetails = EtsyReturnPolicy::getReturnPolicyDetails(Tools::getValue('id_etsy_return_policy'));
                
                /*
                 * Added validation to check if updated policy values match an existing policy
		 * @modifier Himanshu Vishwakarma
                 * @date 15-12-2025
                 */
                $duplicateCheckSQL = "SELECT COUNT(*) as count FROM " . _DB_PREFIX_ . "etsy_return_policy 
                    WHERE accepts_returns = '" . (int)$acceptsReturns . "' 
                    AND accepts_exchanges = '" . (int)$acceptsExchanges . "' 
                    AND return_deadline = '" . (int)$returnDeadline . "' 
                    AND id_etsy_return_policy != '" . (int)Tools::getValue('id_etsy_return_policy') . "'";
                $duplicateCount = Db::getInstance()->getValue($duplicateCheckSQL, true, false);
                
                if ($duplicateCount > 0) {
                    $log_string = 'Update failed: Return policy with same values already exists. Accepts Returns: ' . ($acceptsReturns ? 'Yes' : 'No') . ', Accepts Exchanges: ' . ($acceptsExchanges ? 'Yes' : 'No') . ', Return Deadline: ' . $returnDeadline . ' days';
                    EtsyModule::auditLogEntry($log_string, $method_name);
                    Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyReturnPolicy') . '&etsyError=74');
                }
                
                //Update local database first
                Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_return_policy SET shop_id = '" . $shopId . "', accepts_returns = '" . (int)$acceptsReturns . "', accepts_exchanges = '" . (int)$acceptsExchanges . "', return_deadline = '" . (int)$returnDeadline . "' WHERE id_etsy_return_policy = '" . (int) Tools::getValue('id_etsy_return_policy') . "'");

                //Update on Etsy if return_policy_id exists
                if (!empty($returnPolicyDetails['return_policy_id']) && $returnPolicyDetails['return_policy_id'] != '' && $returnPolicyDetails['return_policy_id'] != '0') {
                    /*
                     * Added Etsy API call to update return policy on Etsy
		     * @modifier Himanshu Vishwakarma
                     * @date 15-12-2025
                     */
                    $updateData = array(
                        'id_etsy_return_policy' => Tools::getValue('id_etsy_return_policy'),
                        'return_policy_id' => $returnPolicyDetails['return_policy_id'],
                        'accepts_returns' => $acceptsReturns,
                        'accepts_exchanges' => $acceptsExchanges,
                        'return_deadline' => $returnDeadline
                    );
                    SyncReturnPolicy::updateReturnPolicy($updateData);
                }

                $log_entry = 'Return policy updated. Updated values: <br>Return Policy ID: ' . (isset($returnPolicyDetails['return_policy_id']) ? $returnPolicyDetails['return_policy_id'] : 'N/A') . '<br>Shop ID: ' . $shopId . '<br>Accepts Returns: ' . ($acceptsReturns ? 'Yes' : 'No') . '<br>Accepts Exchanges: ' . ($acceptsExchanges ? 'Yes' : 'No') . '<br>Return Deadline: ' . $returnDeadline . ' days';
                EtsyModule::auditLogEntry($log_entry, $method_name);

                Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyReturnPolicy') . '&etsyConf=71');
            } else {
                //Create new return policy
                if (!empty($shopId)) {
                    /*
                     * Added validation to check if a return policy with same values already exists
		     * @modifier Himanshu Vishwakarma
                     * @date 15-12-2025
                     */
                    $duplicateCheckSQL = "SELECT COUNT(*) as count FROM " . _DB_PREFIX_ . "etsy_return_policy 
                        WHERE accepts_returns = '" . (int)$acceptsReturns . "' 
                        AND accepts_exchanges = '" . (int)$acceptsExchanges . "' 
                        AND return_deadline = '" . (int)$returnDeadline . "'";
                    $duplicateCount = Db::getInstance()->getValue($duplicateCheckSQL, true, false);
                    
                    if ($duplicateCount > 0) {
                        $log_string = 'Creation failed: Return policy with same values already exists. Accepts Returns: ' . ($acceptsReturns ? 'Yes' : 'No') . ', Accepts Exchanges: ' . ($acceptsExchanges ? 'Yes' : 'No') . ', Return Deadline: ' . $returnDeadline . ' days';
                        EtsyModule::auditLogEntry($log_string, $method_name);
                        Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyReturnPolicy') . '&etsyError=74');
                    }
                    
                    //Insert into local database first
                    Db::getInstance()->execute("INSERT INTO " . _DB_PREFIX_ . "etsy_return_policy (return_policy_id, shop_id, accepts_returns, accepts_exchanges, return_deadline) VALUES ('" . $returnPolicyId . "', '" . $shopId . "', '" . (int)$acceptsReturns . "', '" . (int)$acceptsExchanges . "', '" . (int)$returnDeadline . "')");
                    $return_policy_db_id = Db::getInstance()->Insert_ID();

                    /*
                     * Added Etsy API call to create return policy on Etsy
		     * @modifier Himanshu Vishwakarma
                     * @date 15-12-2025
                     */
                    $createData = array(
                        'id_etsy_return_policy' => $return_policy_db_id,
                        'shop_id' => $shopId,
                        'accepts_returns' => $acceptsReturns,
                        'accepts_exchanges' => $acceptsExchanges,
                        'return_deadline' => $returnDeadline
                    );
                    SyncReturnPolicy::createReturnPolicy($createData);

                    $log_entry = 'Return policy added. Added values: <br>Shop ID: ' . $shopId . '<br>Accepts Returns: ' . ($acceptsReturns ? 'Yes' : 'No') . '<br>Accepts Exchanges: ' . ($acceptsExchanges ? 'Yes' : 'No') . '<br>Return Deadline: ' . $returnDeadline . ' days';
                    EtsyModule::auditLogEntry($log_entry, $method_name);

                    Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyReturnPolicy') . '&etsyConf=70');
                } else {
                    Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyReturnPolicy') . '&etsyError=68');
                }
            }
        } else {
            parent::postProcess();
        }
        $this->content = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbetsy/views/templates/admin/velovalidation.tpl');
    }

    public function processDelete()
    {
        $method_name = 'AdminEtsyReturnPolicy::processDelete()';

        if (!Tools::isEmpty(trim(Tools::getValue('id_etsy_return_policy')))) {
            $returnPolicyDetails = EtsyReturnPolicy::getReturnPolicyDetails(Tools::getValue('id_etsy_return_policy'));

            //Check if return policy is mapped with profile
            $profileMapping = Db::getInstance()->getValue("SELECT count(*) as count FROM " . _DB_PREFIX_ . "etsy_profiles WHERE id_etsy_return_policy = '" . (int) Tools::getValue('id_etsy_return_policy') . "'", true, false);

            if ($profileMapping == 0) {
                /*
                 * Updated delete process to delete return policy from Etsy API first, then from database
		 * @modifier Himanshu Vishwakarma
                 * @date 15-12-2025
                 */
                /* If Etsy Return Policy ID exists then delete the same from the Etsy first else directly delete from the Db */
                if (!empty($returnPolicyDetails['return_policy_id']) && $returnPolicyDetails['return_policy_id'] != '' && $returnPolicyDetails['return_policy_id'] != '0') {
                    $result = SyncReturnPolicy::deleteReturnPolicy(array(
                        "id_etsy_return_policy" => Tools::getValue('id_etsy_return_policy'), 
                        "return_policy_id" => $returnPolicyDetails['return_policy_id'],
                        "shop_id" => isset($returnPolicyDetails['shop_id']) ? $returnPolicyDetails['shop_id'] : ''
                    ));
                    if ($result) {
                        Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyReturnPolicy') . '&etsyConf=72');
                    } else {
                        //If deletion from Etsy fails, still delete from local database
                        $result = Db::getInstance()->execute("DELETE FROM " . _DB_PREFIX_ . "etsy_return_policy WHERE id_etsy_return_policy = '" . (int) Tools::getValue('id_etsy_return_policy') . "'");
                        if ($result) {
                            $log_string = 'Return policy deleted from local database (Etsy deletion failed). Return Policy ID: ' . (isset($returnPolicyDetails['return_policy_id']) ? $returnPolicyDetails['return_policy_id'] : 'N/A');
                            EtsyModule::auditLogEntry($log_string, $method_name);
                            Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyReturnPolicy') . '&etsyConf=72');
                        } else {
                            $log_string = 'Deletion of return policy failed. Return Policy ID: ' . (isset($returnPolicyDetails['return_policy_id']) ? $returnPolicyDetails['return_policy_id'] : 'N/A');
                            EtsyModule::auditLogEntry($log_string, $method_name);
                            Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyReturnPolicy') . '&etsyError=68');
                        }
                    }
                } else {
                    //If return_policy_id doesn't exist, delete directly from database
                    $result = Db::getInstance()->execute("DELETE FROM " . _DB_PREFIX_ . "etsy_return_policy WHERE id_etsy_return_policy = '" . (int) Tools::getValue('id_etsy_return_policy') . "'");
                    if ($result) {
                        $log_string = 'Return policy deleted from local database (no Etsy ID). Return Policy ID: N/A';
                        EtsyModule::auditLogEntry($log_string, $method_name);
                        Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyReturnPolicy') . '&etsyConf=72');
                    } else {
                        $log_string = 'Deletion of return policy failed. Return Policy ID: N/A';
                        EtsyModule::auditLogEntry($log_string, $method_name);
                        Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyReturnPolicy') . '&etsyError=68');
                    }
                }
            } else {
                $log_string = 'Deletion of return policy failed. Return Policy is mapped with Profile. Return Policy ID: ' . (isset($returnPolicyDetails['return_policy_id']) ? $returnPolicyDetails['return_policy_id'] : 'N/A');
                EtsyModule::auditLogEntry($log_string, $method_name);

                Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyReturnPolicy') . '&etsyError=75');
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
        if (!Tools::getValue('id_etsy_return_policy') && !Tools::isSubmit('addetsy_return_policy')) {
            $this->page_header_toolbar_btn['new_template'] = array(
                'href' => self::$currentIndex . '&add' . $this->table . '&token=' . $this->token,
                'desc' => $this->module->l('Add new','AdminEtsyReturnPolicyController'),
                'icon' => 'process-icon-new'
            );
            $secure_key = Configuration::get('KBETSY_SECURE_KEY');
            $this->page_header_toolbar_btn['kb_sync_returnpolicies'] = array(
                'href' => $this->context->link->getModuleLink('kbetsy', 'cron', array(
                    'action' => 'syncReturnPolicies',
                    'secure_key' => $secure_key)),
                'target' => '_blank',
                'desc' => $this->module->l('Sync Return Policies','AdminEtsyReturnPolicyController'),
                'icon' => 'process-icon-update'
            );
        }
        if (Tools::getValue('id_etsy_return_policy') || Tools::isSubmit('id_etsy_return_policy') || Tools::isSubmit('addetsy_return_policy')) {
            $this->page_header_toolbar_btn['kb_cancel_action'] = array(
                'href' => self::$currentIndex . '&token=' . $this->token,
                'desc' => $this->module->l('Cancel','AdminEtsyReturnPolicyController'),
                'icon' => 'process-icon-cancel'
            );
        }

        parent::initPageHeaderToolbar();
    }
}

