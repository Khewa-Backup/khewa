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

//Include Etsy Module Class to inherit some common functions and callbacks
require_once(_PS_MODULE_DIR_ . 'kbetsy/classes/EtsyModule.php');

class AdminEtsyOrderSettingsController extends ModuleAdminController
{

    //Class Constructor
    public function __construct()
    {
        $this->name = 'EtsyOrderSettings';
        $this->context = Context::getContext();
        $this->bootstrap = true;

        parent::__construct();

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
        $this->addCSS($this->getModuleDirUrl() . 'kbetsy/views/css/style.css');
    }

    //Function definition to render a form
    public function initContent()
    {
        //Store Order Statuses List
        $orderStatuses = array();
        $statuses = OrderState::getOrderStates((int) $this->context->language->id);
        foreach ($statuses as $status) {
            $orderStatuses[] = array(
                'id_option' => $status['id_order_state'],
                'name' => $status['name']
            );
        }

        $formFields = array(
            array(
                'type' => 'select',
                'label' => $this->module->l('Order Default Status', 'AdminEtsyOrderSettingsController'),
                'desc' => $this->module->l('Choose an Order Default Status', 'AdminEtsyOrderSettingsController'),
                'name' => 'etsy_order_default_status',
                'required' => true,
                'options' => array(
                    'query' => $orderStatuses,
                    'id' => 'id_option',
                    'name' => 'name'
                )
            ),
            array(
                'type' => 'select',
                'label' => $this->module->l('Order Paid Status', 'AdminEtsyOrderSettingsController'),
                'desc' => $this->module->l('Choose an Order Status for Paid Orders placed on Etsy Marketplace', 'AdminEtsyOrderSettingsController'),
                'name' => 'etsy_order_paid_status',
                'required' => true,
                'options' => array(
                    'query' => $orderStatuses,
                    'id' => 'id_option',
                    'name' => 'name'
                )
            ),
            array(
                'type' => 'select',
                'label' => $this->module->l('Order Shipped Status', 'AdminEtsyOrderSettingsController'),
                'desc' => $this->module->l('Choose an Order Status for Shipped Orders placed on Etsy Marketplace', 'AdminEtsyOrderSettingsController'),
                'name' => 'etsy_order_shipped_status',
                'required' => true,
                'options' => array(
                    'query' => $orderStatuses,
                    'id' => 'id_option',
                    'name' => 'name'
                )
            )
        );

        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->module->l('Order Settings', 'AdminEtsyOrderSettingsController'),
                    'icon' => 'icon-cogs'
                ),
                'input' => $formFields,
                'submit' => array(
                    'class' => 'btn btn-default pull-right',
                    'title' => $this->module->l('Save', 'AdminEtsyOrderSettingsController')
                )
            )
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->default_form_language = $this->context->language->id;

        $helper->fields_value['etsy_order_default_status'] = Configuration::get('etsy_order_default_status');
        $helper->fields_value['etsy_order_paid_status'] = Configuration::get('etsy_order_paid_status');
        $helper->fields_value['etsy_order_shipped_status'] = Configuration::get('etsy_order_shipped_status');

        $this->content .= $helper->generateForm(array($fields_form));


        parent::initContent();
    }

    //Function definition to handle Form Submission
    public function postProcess()
    {
        parent::postProcess();

        //Handle form submission
        if (Tools::isSubmit('submitAddconfiguration')) {
            Configuration::updateGlobalValue('etsy_order_default_status', Tools::getValue('etsy_order_default_status'));
            Configuration::updateGlobalValue('etsy_order_paid_status', Tools::getValue('etsy_order_paid_status'));
            Configuration::updateGlobalValue('etsy_order_shipped_status', Tools::getValue('etsy_order_shipped_status'));

            //Audit Log Entry
            $auditLogEntryString = 'Order Settings updated for Etsy Marketplace Integration Module. Updated Values are as - <br>Order Default Status: ' . Tools::getValue('etsy_order_default_status') . '<br>Order Paid Status: ' . Tools::getValue('etsy_order_paid_status') . '<br>Order Shipped Status: ' . Tools::getValue('etsy_order_shipped_status');
            $auditMethodName = 'AdminEtsyOrderSettings::postProcess()';
            EtsyModule::auditLogEntry($auditLogEntryString, $auditMethodName);

            Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyOrderSettings') . '&etsyConf=3');
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
        $secure_key = Configuration::get('KBETSY_SECURE_KEY');
        $this->page_header_toolbar_btn['kb_sync_templates'] = array(
            'href' => $this->context->link->getModuleLink('kbetsy', 'cron', array('action' => 'syncOrdersStatus', 'secure_key' => $secure_key)),
            'target'=> '_blank',
            'desc' => $this->l('Update Order Status On Etsy'),
            'icon' => 'process-icon-update'
        );

        parent::initPageHeaderToolbar();
    }
}
