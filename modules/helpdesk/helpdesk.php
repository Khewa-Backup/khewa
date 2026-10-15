<?php
/**
* FMM Helpdesk Module
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
*
* @author    FMM Modules
* @copyright FMM Modules
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* @category  FMM Modules
* @package   Helpdesk
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_.'helpdesk/models/Ticketstatus.php';
require_once _PS_MODULE_DIR_.'helpdesk/models/Ticketpriorities.php';
require_once _PS_MODULE_DIR_.'helpdesk/models/Ticketdepartments.php';
require_once _PS_MODULE_DIR_.'helpdesk/models/Ticketpremades.php';
require_once _PS_MODULE_DIR_.'helpdesk/models/Ticketemailtemps.php';
require_once _PS_MODULE_DIR_.'helpdesk/models/Tickets.php';

class Helpdesk extends Module
{
    private $tabParentClass = null;
    private $tabClass  = 'AdminHelpdesk';
    private $tabModule = 'helpdesk';
    private $tabName   = 'HelpDesk';

    public function __construct()
    {
        $this->name         = 'helpdesk';
        $this->tab          = 'front_office_features';
        $this->version      = '2.1.0';
        $this->author       = 'FMM Modules';
        $this->displayName  = $this->l('Help Desk');
        $this->description  = $this->l('Allow users to post tickets using Help Desk feature.');
        $this->bootstrap = true;
        $this->module_key = 'ccd7ca154f9145c8d0a7b040652c069c';
        $this->author_address = '0xcC5e76A6182fa47eD831E43d80Cd0985a14BB095';
        parent::__construct();
    }

    public function install()
    {
        include(dirname(__FILE__).'/sql/install.php');
        $this->installConfiguration();
        if (!$this->existsTab($this->tabClass)) {
            if (!$this->addTab($this->tabClass, 0)) {
                return false;
            }
        }
        $base_dir = _PS_IMG_DIR_.'helpdesk';
        if (!file_exists($base_dir)) {
            @mkdir($base_dir, 0777);
        }
        return parent::install()
        && $this->registerHook('leftColumn')
        && $this->registerHook('ModuleRoutes')
        && $this->registerHook('displayCustomerAccount')
        && $this->registerHook('displayBackOfficeHeader')
        && $this->registerHook('registerGDPRConsent')
        && $this->registerHook('actionDeleteGDPRCustomer')
        && $this->registerHook('actionExportGDPRData');
    }

    public function uninstall()
    {
        include(dirname(__FILE__).'/sql/uninstall.php');
        $this->removeTab($this->tabClass);
        if (parent::uninstall()) {
            return true;
        }

        $img_dir = _PS_IMG_DIR_.'helpdesk';
        if (!file_exists($img_dir)) {
            @rmdir($img_dir);
        }
        return true;
    }

    private function addTab($tabClass, $id_parent)
    {
        $tab = new Tab();
        $tab->class_name = $tabClass;
        $tab->id_parent = $id_parent;
        $tab->module = $this->tabModule;
        $tab->name[(int)(Configuration::get('PS_LANG_DEFAULT'))] = $this->l('Helpdesk');
        $tab->add();

        $subtab = new Tab();
        $subtab->class_name = 'AdminTickets';
        $subtab->id_parent = Tab::getIdFromClassName($tabClass);
        $subtab->module = $this->tabModule;
        $subtab->name[(int)(Configuration::get('PS_LANG_DEFAULT'))] = $this->l('Manage Tickets');
        $subtab->add();

        $subtab = new Tab();
        $subtab->class_name = 'AdminTicketStatus';
        $subtab->id_parent = Tab::getIdFromClassName($tabClass);
        $subtab->module = $this->tabModule;
        $subtab->name[(int)(Configuration::get('PS_LANG_DEFAULT'))] = $this->l('Manage Ticket Status');
        $subtab->add();

        $subtab = new Tab();
        $subtab->class_name = 'AdminTicketPriorities';
        $subtab->id_parent = Tab::getIdFromClassName($tabClass);
        $subtab->module = $this->tabModule;
        $subtab->name[(int)(Configuration::get('PS_LANG_DEFAULT'))] = $this->l('Manage Ticket Priorities');
        $subtab->add();

        $subtab = new Tab();
        $subtab->class_name = 'AdminTicketDepartments';
        $subtab->id_parent = Tab::getIdFromClassName($tabClass);
        $subtab->module = $this->tabModule;
        $subtab->name[(int)(Configuration::get('PS_LANG_DEFAULT'))] = $this->l('Manage Departments');
        $subtab->add();

        $subtab = new Tab();
        $subtab->class_name = 'AdminTicketEmailTemplates';
        $subtab->id_parent = Tab::getIdFromClassName($tabClass);
        $subtab->module = $this->tabModule;
        $subtab->name[(int)(Configuration::get('PS_LANG_DEFAULT'))] = $this->l('Manage Email Templates');
        $subtab->add();

        $subtab = new Tab();
        $subtab->class_name = 'AdminTicketPremadeTemplates';
        $subtab->id_parent = Tab::getIdFromClassName($tabClass);
        $subtab->module = $this->tabModule;
        $subtab->name[(int)(Configuration::get('PS_LANG_DEFAULT'))] = $this->l('Manage Premade Replies');
        $subtab->add();
        return true;
    }

    private function removeTab($tabClass)
    {
        $idTab = Tab::getIdFromClassName($tabClass);
        if ($idTab != 0) {
            $tab = new Tab($idTab);
            $tab->delete();
            return true;
        }
        return true;
    }

    public function getIdTabFromClassName($tabClass)
    {
        $sql = 'SELECT id_tab FROM '._DB_PREFIX_.'tab WHERE class_name="'.pSQL($tabClass).'"';
        $tab = Db::getInstance()->getRow($sql);
        return (int)$tab['id_tab'];
    }

    public function existsTab($tabClass)
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT id_tab AS id 
        FROM `'._DB_PREFIX_.'tab` t WHERE LOWER(t.`class_name`) = \''.pSQL($tabClass).'\'');
        if (count($result) == 0) {
            return false;
        }
        return true;
    }

    public function installConfiguration()
    {
        Configuration::updateValue('HELPDESK_PAGE_TITLE'.$this->context->language->id, 'Helpdesk');
        Configuration::updateValue('HELPDESK_LEFT_ENABLE_DISABLE', 1);
        Configuration::updateValue('HELPDESK_PRIORITIES', 1);
        Configuration::updateValue('HELPDESK_DEFAULT_EMAIL_TEMPLATE', 1);
        Configuration::updateValue('HELPDESK_DEFAULT_PRIORITY', 1);
        Configuration::updateValue('HELPDESK_SHOW_DEPARTMENTS', 1);
        Configuration::updateValue('HELPDESK_DEFAULT_EMAIL_SERVER', '{imap.gmail.com:993/ssl}INBOX');
        Configuration::updateValue('HELPDESK_DEFAULT_NEW_STATUS', 1);
        Configuration::updateValue('HELPDESK_DEFAULT_CLOSE_STATUS', 1);
        Configuration::updateValue('HELPDESK_FILE_UPLOADS', 1);
        Configuration::updateValue('HELPDESK_MAX_FILE_SIZE', '2');
        Configuration::updateValue('HELPDESK_ACCEPTED_FILE_TYPES', 'jpg,doc,pdf,jpeg,png');
        Configuration::updateValue('HELPDESK_NEW_TICKET_ALERT', 1);
        Configuration::updateValue('HELPDESK_NEW_MESSAGE_ALERT', 1);
        Configuration::updateValue('HELPDESK_EMAIL_COPY', 'demo@demo.com');
        Configuration::updateValue('HELPDESK_DEFAULT_ALERT_NAME', 'Help Desk');
        Configuration::updateValue('HELPDESK_DEFAULT_ALERT_EMAIL', 'demo@demo.com');
        Configuration::updateValue('HELPDESK_NEW_TICKET_RESPOND', 1);
        Configuration::updateValue('HELPDESK_NEW_MESSAGE_RESPOND', 1);
        Configuration::updateValue('HELPDESK_CLOSE_TICKET_NOTICE', 1);
        Configuration::updateValue('HELPDESK_ALLOW_CUSTOMERS_CLOSE', 1);
        Configuration::updateValue('HELPDESK_ALLOW_CUSTOMERS_REOPEN', 1);
        Configuration::updateValue('HELPDESK_ENABLE_EDITOR_MESSAGE', 0);
        Configuration::updateValue('HELPDESK_ENABLE_GOOGLE_CAPTCHA', 0);
        Configuration::updateValue('HELPDESK_SITEKEY_CAPTCHA', '');
        Configuration::updateValue('HELPDESK_SECURE_KEY', Tools::strtoupper(Tools::passwdGen(16)));
        return true;
    }

    public function uninstallConfiguration()
    {
        Configuration::updateValue('HELPDESK_PAGE_TITLE');
        Configuration::updateValue('HELPDESK_LEFT_ENABLE_DISABLE');
        Configuration::updateValue('HELPDESK_PRIORITIES');
        Configuration::updateValue('HELPDESK_DEFAULT_EMAIL_TEMPLATE');
        Configuration::updateValue('HELPDESK_DEFAULT_PRIORITY');
        Configuration::updateValue('HELPDESK_SHOW_DEPARTMENTS');
        Configuration::updateValue('HELPDESK_DEFAULT_DEPARTMENT');
        Configuration::updateValue('HELPDESK_DEFAULT_EMAIL_SERVER');
        Configuration::updateValue('HELPDESK_DEFAULT_NEW_STATUS');
        Configuration::updateValue('HELPDESK_DEFAULT_CLOSE_STATUS');
        Configuration::updateValue('HELPDESK_FILE_UPLOADS');
        Configuration::updateValue('HELPDESK_MAX_FILE_SIZE');
        Configuration::updateValue('HELPDESK_ACCEPTED_FILE_TYPES');
        Configuration::updateValue('HELPDESK_NEW_TICKET_ALERT');
        Configuration::updateValue('HELPDESK_NEW_MESSAGE_ALERT');
        Configuration::updateValue('HELPDESK_EMAIL_COPY');
        Configuration::updateValue('HELPDESK_DEFAULT_ALERT_NAME');
        Configuration::updateValue('HELPDESK_DEFAULT_ALERT_EMAIL');
        Configuration::updateValue('HELPDESK_NEW_TICKET_RESPOND');
        Configuration::updateValue('HELPDESK_NEW_MESSAGE_RESPOND');
        Configuration::updateValue('HELPDESK_CLOSE_TICKET_NOTICE');
        Configuration::updateValue('HELPDESK_ALLOW_CUSTOMERS_CLOSE');
        Configuration::updateValue('HELPDESK_ALLOW_CUSTOMERS_REOPEN');
        Configuration::updateValue('HELPDESK_ENABLE_EDITOR_MESSAGE');
        Configuration::updateValue('HELPDESK_ENABLE_GOOGLE_CAPTCHA');
        Configuration::deleteByName('HELPDESK_SECURE_KEY', Tools::strtoupper(Tools::passwdGen(16)));
        Configuration::updateValue('HELPDESK_USER_NAME');
        Configuration::updateValue('HELPDESK_PASSWORD');
        return true;
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitHd')) {
            $languages = Language::getLanguages(false);
            $values = array();
            foreach ($languages as $lang) {
                $values['HELPDESK_PAGE_TITLE'][$lang['id_lang']] = Tools::getValue('HELPDESK_PAGE_TITLE_'.$lang['id_lang']);
            }
            Configuration::updateValue('HELPDESK_PAGE_TITLE', $values['HELPDESK_PAGE_TITLE']);
            Configuration::updateValue('HELPDESK_ENABLE_DISABLE', Tools::getValue('HELPDESK_ENABLE_DISABLE'));
            Configuration::updateValue('HELPDESK_LEFT_ENABLE_DISABLE', Tools::getValue('HELPDESK_LEFT_ENABLE_DISABLE'));
            Configuration::updateValue('HELPDESK_DEFAULT_PRIORITY', Tools::getValue('HELPDESK_DEFAULT_PRIORITY'));
            Configuration::updateValue('HELPDESK_PRIORITIES', Tools::getValue('HELPDESK_PRIORITIES'));
            Configuration::updateValue('HELPDESK_SHOW_DEPARTMENTS', Tools::getValue('HELPDESK_SHOW_DEPARTMENTS'));
            Configuration::updateValue('HELPDESK_DEFAULT_DEPARTMENT', Tools::getValue('HELPDESK_DEFAULT_DEPARTMENT'));
            Configuration::updateValue('HELPDESK_DEFAULT_NEW_STATUS', Tools::getValue('HELPDESK_DEFAULT_NEW_STATUS'));
            Configuration::updateValue('HELPDESK_DEFAULT_CLOSE_STATUS', Tools::getValue('HELPDESK_DEFAULT_CLOSE_STATUS'));
            Configuration::updateValue('HELPDESK_FILE_UPLOADS', Tools::getValue('HELPDESK_FILE_UPLOADS'));
            Configuration::updateValue('HELPDESK_MAX_FILE_SIZE', Tools::getValue('HELPDESK_MAX_FILE_SIZE'));
            Configuration::updateValue('HELPDESK_NEW_TICKET_ALERT', Tools::getValue('HELPDESK_NEW_TICKET_ALERT'));
            Configuration::updateValue('HELPDESK_NEW_MESSAGE_ALERT', Tools::getValue('HELPDESK_NEW_MESSAGE_ALERT'));
            Configuration::updateValue('HELPDESK_DEFAULT_ALERT_NAME', Tools::getValue('HELPDESK_DEFAULT_ALERT_NAME'));
            Configuration::updateValue('HELPDESK_ACCEPTED_FILE_TYPES', Tools::getValue('HELPDESK_ACCEPTED_FILE_TYPES'));
            Configuration::updateValue('HELPDESK_EMAIL_COPY', Tools::getValue('HELPDESK_EMAIL_COPY'));
            Configuration::updateValue('HELPDESK_DEFAULT_ALERT_EMAIL', Tools::getValue('HELPDESK_DEFAULT_ALERT_EMAIL'));
            Configuration::updateValue('HELPDESK_NEW_TICKET_RESPOND', Tools::getValue('HELPDESK_NEW_TICKET_RESPOND'));
            Configuration::updateValue('HELPDESK_NEW_MESSAGE_RESPOND', Tools::getValue('HELPDESK_NEW_MESSAGE_RESPOND'));
            Configuration::updateValue('HELPDESK_CLOSE_TICKET_NOTICE', Tools::getValue('HELPDESK_CLOSE_TICKET_NOTICE'));
            Configuration::updateValue('HELPDESK_ALLOW_CUSTOMERS_CLOSE', Tools::getValue('HELPDESK_ALLOW_CUSTOMERS_CLOSE'));
            Configuration::updateValue('HELPDESK_ENABLE_GOOGLE_CAPTCHA', Tools::getValue('HELPDESK_ENABLE_GOOGLE_CAPTCHA'));
            Configuration::updateValue('HELPDESK_SITEKEY_CAPTCHA', Tools::getValue('HELPDESK_SITEKEY_CAPTCHA'));
            Configuration::updateValue('HELPDESK_DEFAULT_EMAIL_SERVER', Tools::getValue('HELPDESK_DEFAULT_EMAIL_SERVER'));
            Configuration::updateValue('HELPDESK_PASSWORD', Tools::getValue('HELPDESK_PASSWORD'));
            Configuration::updateValue('HELPDESK_USER_NAME', Tools::getValue('HELPDESK_USER_NAME'));
            return $this->displayConfirmation($this->l('The settings have been updated.'));
        }
        return '';
    }

    public function getContent()
    {
        $this->html = $this->display(__FILE__, 'views/templates/hook/info.tpl');
        return $this->postProcess().$this->html.$this->renderForm();
    }

    public function renderForm()
    {
        $cron_msg = '';
        $cron_msg .= Tools::getShopDomain(true, true).__PS_BASE_URI__.'modules/helpdesk/help_desk.php?secure_key='.Configuration::get('HELPDESK_SECURE_KEY');
        if (Tools::version_compare(_PS_VERSION_, '1.6.0.0', '>=')) {
            $status_admin = array(
                'type' => 'switch',
                'label' => $this->l('Enable Module?'),
                'name' => 'HELPDESK_ENABLE_DISABLE',
                'required' => false,
                'class' => 't',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'fmmem_on',
                        'value' => 1,
                        'label' => $this->l('Yes')
                    ),
                    array(
                        'id' => 'fmmem_off',
                        'value' => 0,
                        'label' => $this->l('No')
                        )
                    ),
                );
            $status_admin2 = array(
                'type' => 'switch',
                'label' => $this->l('Enable Left Block?'),
                'name' => 'HELPDESK_LEFT_ENABLE_DISABLE',
                'required' => false,
                'class' => 't',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'fmmem_on',
                        'value' => 1,
                        'label' => $this->l('Yes')
                    ),
                    array(
                        'id' => 'fmmem_off',
                        'value' => 0,
                        'label' => $this->l('No')
                        )
                    ),
                );
            $status_admin3 = array(
                'type' => 'switch',
                'label' => $this->l('Show Priorities?'),
                'name' => 'HELPDESK_PRIORITIES',
                'required' => false,
                'class' => 't',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'fmmem_on',
                        'value' => 1,
                        'label' => $this->l('Yes')
                    ),
                    array(
                        'id' => 'fmmem_off',
                        'value' => 0,
                        'label' => $this->l('No')
                        )
                    ),
                );
            $status_admin4 = array(
                'type' => 'switch',
                'label' => $this->l('Show Departments?'),
                'name' => 'HELPDESK_SHOW_DEPARTMENTS',
                'required' => false,
                'class' => 't',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'fmmem_on',
                        'value' => 1,
                        'label' => $this->l('Yes')
                    ),
                    array(
                        'id' => 'fmmem_off',
                        'value' => 0,
                        'label' => $this->l('No')
                        )
                    ),
                );
            $status_admin5 = array(
                'type' => 'switch',
                'label' => $this->l('Allow File Upload?'),
                'name' => 'HELPDESK_FILE_UPLOADS',
                'required' => false,
                'class' => 't',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'fmmem_on',
                        'value' => 1,
                        'label' => $this->l('Yes')
                    ),
                    array(
                        'id' => 'fmmem_off',
                        'value' => 0,
                        'label' => $this->l('No')
                        )
                    ),
                );
            $status_admin6 = array(
                'type' => 'switch',
                'label' => $this->l('New Ticket Alert?'),
                'name' => 'HELPDESK_NEW_TICKET_ALERT',
                'required' => false,
                'class' => 't',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'fmmem_on',
                        'value' => 1,
                        'label' => $this->l('Yes')
                    ),
                    array(
                        'id' => 'fmmem_off',
                        'value' => 0,
                        'label' => $this->l('No')
                        )
                    ),
                );
            $status_admin7 = array(
                'type' => 'switch',
                'label' => $this->l('New Message Alert?'),
                'name' => 'HELPDESK_NEW_MESSAGE_ALERT',
                'required' => false,
                'class' => 't',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'fmmem_on',
                        'value' => 1,
                        'label' => $this->l('Yes')
                    ),
                    array(
                        'id' => 'fmmem_off',
                        'value' => 0,
                        'label' => $this->l('No')
                        )
                    ),
                );
            $status_admin8 = array(
                'type' => 'switch',
                'label' => $this->l('Default New Ticket Respond?'),
                'name' => 'HELPDESK_NEW_TICKET_RESPOND',
                'required' => false,
                'class' => 't',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'fmmem_on',
                        'value' => 1,
                        'label' => $this->l('Yes')
                    ),
                    array(
                        'id' => 'fmmem_off',
                        'value' => 0,
                        'label' => $this->l('No')
                        )
                    ),
                );
            $status_admin9 = array(
                'type' => 'switch',
                'label' => $this->l('Default New Message Respond?'),
                'name' => 'HELPDESK_NEW_MESSAGE_RESPOND',
                'required' => false,
                'class' => 't',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'fmmem_on',
                        'value' => 1,
                        'label' => $this->l('Yes')
                    ),
                    array(
                        'id' => 'fmmem_off',
                        'value' => 0,
                        'label' => $this->l('No')
                        )
                    ),
                );
            $status_admin10 = array(
                'type' => 'switch',
                'label' => $this->l('Close Ticket Notice?'),
                'name' => 'HELPDESK_CLOSE_TICKET_NOTICE',
                'required' => false,
                'class' => 't',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'fmmem_on',
                        'value' => 1,
                        'label' => $this->l('Yes')
                    ),
                    array(
                        'id' => 'fmmem_off',
                        'value' => 0,
                        'label' => $this->l('No')
                        )
                    ),
                );
            $status_admin11 = array(
                'type' => 'switch',
                'label' => $this->l('Allow Customer to Close Ticket?'),
                'name' => 'HELPDESK_ALLOW_CUSTOMERS_CLOSE',
                'required' => false,
                'class' => 't',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'fmmem_on',
                        'value' => 1,
                        'label' => $this->l('Yes')
                    ),
                    array(
                        'id' => 'fmmem_off',
                        'value' => 0,
                        'label' => $this->l('No')
                        )
                    ),
                );
            $status_admin12 = array(
                'type' => 'switch',
                'label' => $this->l('Show Google Captcha?'),
                'name' => 'HELPDESK_ENABLE_GOOGLE_CAPTCHA',
                'required' => false,
                'class' => 't',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'fmmem_on',
                        'value' => 1,
                        'label' => $this->l('Yes')
                    ),
                    array(
                        'id' => 'fmmem_off',
                        'value' => 0,
                        'label' => $this->l('No')
                        )
                    ),
                );
        } else {
            $status_admin = array(
                'type' => 'radio',
                'label' => $this->l('Enable Module?'),
                'name' => 'HELPDESK_ENABLE_DISABLE',
                'required' => false,
                'class' => 't',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->l('Enabled')
                        ),
                    array(
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => $this->l('Disabled')
                        )
                    ),
                );
            $status_admin2 = array(
                'type' => 'radio',
                'label' => $this->l('Enable Left Block?'),
                'name' => 'HELPDESK_LEFT_ENABLE_DISABLE',
                'required' => false,
                'class' => 't',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->l('Enabled')
                        ),
                    array(
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => $this->l('Disabled')
                        )
                    ),
                );
            $status_admin3 = array(
                'type' => 'radio',
                'label' => $this->l('Show Priorities?'),
                'name' => 'HELPDESK_PRIORITIES',
                'required' => false,
                'class' => 't',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->l('Enabled')
                        ),
                    array(
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => $this->l('Disabled')
                        )
                    ),
                );
            $status_admin4 = array(
                'type' => 'radio',
                'label' => $this->l('Show Departments?'),
                'name' => 'HELPDESK_SHOW_DEPARTMENTS',
                'required' => false,
                'class' => 't',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->l('Enabled')
                        ),
                    array(
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => $this->l('Disabled')
                        )
                    ),
                );
            $status_admin5 = array(
                'type' => 'radio',
                'label' => $this->l('Allow File Upload?'),
                'name' => 'HELPDESK_FILE_UPLOADS',
                'required' => false,
                'class' => 't',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->l('Enabled')
                        ),
                    array(
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => $this->l('Disabled')
                        )
                    ),
                );
            $status_admin6 = array(
                'type' => 'radio',
                'label' => $this->l('New Ticket Alert?'),
                'name' => 'HELPDESK_NEW_TICKET_ALERT',
                'required' => false,
                'class' => 't',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->l('Enabled')
                        ),
                    array(
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => $this->l('Disabled')
                        )
                    ),
                );
            $status_admin7 = array(
                'type' => 'radio',
                'label' => $this->l('New Message Alert?'),
                'name' => 'HELPDESK_NEW_MESSAGE_ALERT',
                'required' => false,
                'class' => 't',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->l('Enabled')
                        ),
                    array(
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => $this->l('Disabled')
                        )
                    ),
                );
            $status_admin8 = array(
                'type' => 'radio',
                'label' => $this->l('Default New Ticket Respond?'),
                'name' => 'HELPDESK_NEW_TICKET_RESPOND',
                'required' => false,
                'class' => 't',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->l('Enabled')
                        ),
                    array(
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => $this->l('Disabled')
                        )
                    ),
                );
            $status_admin9 = array(
                'type' => 'radio',
                'label' => $this->l('Default New Message Respond?'),
                'name' => 'HELPDESK_NEW_MESSAGE_RESPOND',
                'required' => false,
                'class' => 't',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->l('Enabled')
                        ),
                    array(
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => $this->l('Disabled')
                        )
                    ),
                );
            $status_admin10 = array(
                'type' => 'radio',
                'label' => $this->l('Close Ticket Notice?'),
                'name' => 'HELPDESK_CLOSE_TICKET_NOTICE',
                'required' => false,
                'class' => 't',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->l('Enabled')
                        ),
                    array(
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => $this->l('Disabled')
                        )
                    ),
                );
            $status_admin11 = array(
                'type' => 'radio',
                'label' => $this->l('Allow Customer to Close Ticket?'),
                'name' => 'HELPDESK_ALLOW_CUSTOMERS_CLOSE',
                'required' => false,
                'class' => 't',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->l('Enabled')
                        ),
                    array(
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => $this->l('Disabled')
                        )
                    ),
                );
            $status_admin12 = array(
                'type' => 'radio',
                'label' => $this->l('Show Google Captcha?'),
                'name' => 'HELPDESK_ENABLE_GOOGLE_CAPTCHA',
                'required' => false,
                'class' => 't',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->l('Enabled')
                        ),
                    array(
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => $this->l('Disabled')
                        )
                    ),
                );
        }
            $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-wrench'
                ),
                'input' => array(
                    $status_admin,
                    $status_admin2,
                    array(
                        'type' => 'text',
                        'lang' => true,
                        'label' => $this->l('Page Title'),
                        'name' => 'HELPDESK_PAGE_TITLE'
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Select Default Priority'),
                        'name' => 'HELPDESK_DEFAULT_PRIORITY',
                        'required' => true,
                        'default_value' => (int)Configuration::get('HELPDESK_DEFAULT_PRIORITY'),
                        'options' => array(
                            'query' => Tickets::getPriorities(),
                            'id' => 'priorities_id',
                            'name' => 'priorities_title'
                        )
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save')
                )
            ),
        );
        $fields_form_i = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Ticket Settings'),
                    'icon' => 'icon-wrench'
                ),
                'input' => array(
                    $status_admin3,
                    $status_admin4,
                    array(
                        'type' => 'select',
                        'label' => $this->l('Select Default Department'),
                        'name' => 'HELPDESK_DEFAULT_DEPARTMENT',
                        'required' => true,
                        'desc' => 'If customers wont able to see departments list then this will be assigned',
                        'default_value' => (int)Configuration::get('HELPDESK_DEFAULT_DEPARTMENT'),
                        'options' => array(
                            'query' => Tickets::getDepartments(),
                            'id' => 'departments_id',
                            'name' => 'department_title'
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Default New Ticket Status'),
                        'name' => 'HELPDESK_DEFAULT_NEW_STATUS',
                        'required' => true,
                        'desc' => 'This would be the default status of the new ticket.',
                        'default_value' => (int)Configuration::get('HELPDESK_DEFAULT_NEW_STATUS'),
                        'options' => array(
                            'query' => Tickets::getStatuses(),
                            'id' => 'ticketstatus_id',
                            'name' => 'ticketstatus_title'
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Default Close Ticket Status'),
                        'name' => 'HELPDESK_DEFAULT_CLOSE_STATUS',
                        'required' => true,
                        'desc' => 'This would be the status of ticket when its closed.',
                        'default_value' => (int)Configuration::get('HELPDESK_DEFAULT_CLOSE_STATUS'),
                        'options' => array(
                            'query' => Tickets::getStatuses(),
                            'id' => 'ticketstatus_id',
                            'name' => 'ticketstatus_title'
                        )
                    ),
                    $status_admin6,
                    $status_admin7
                ),
                'submit' => array(
                    'title' => $this->l('Save')
                )
            ),
        );
        $fields_form_ii = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Upload Settings'),
                    'icon' => 'icon-wrench'
                ),
                'input' => array(
                    $status_admin5,
                    array(
                        'type' => 'text',
                        'lang' => false,
                        'label' => $this->l('Max File Size'),
                        'name' => 'HELPDESK_MAX_FILE_SIZE',
                        'suffix' => 'megabytes'
                    ),
                    array(
                        'type' => 'text',
                        'lang' => false,
                        'label' => $this->l('Accepted File Types'),
                        'name' => 'HELPDESK_ACCEPTED_FILE_TYPES',
                        'desc' => 'Comma seperated file types i.e. jpg, gif, png'
                    ),
                    array(
                        'type' => 'text',
                        'lang' => false,
                        'label' => $this->l('Default Alert Name'),
                        'name' => 'HELPDESK_DEFAULT_ALERT_NAME',
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save')
                )
            ),
        );
        $fields_form_iii = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Captcha/Email Settings'),
                    'icon' => 'icon-wrench'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'lang' => false,
                        'label' => $this->l('Send Email Copy'),
                        'name' => 'HELPDESK_EMAIL_COPY',
                        'suffix' => 'email'
                    ),
                    array(
                        'type' => 'text',
                        'lang' => false,
                        'label' => $this->l('Default Alert Email'),
                        'name' => 'HELPDESK_DEFAULT_ALERT_EMAIL',
                        'suffix' => 'email'
                    ),
                    $status_admin8,
                    $status_admin9,
                    $status_admin10,
                    $status_admin11,
                    $status_admin12,
                    array(
                        'type' => 'text',
                        'lang' => false,
                        'label' => $this->l('Google captcha SiteKey'),
                        'name' => 'HELPDESK_SITEKEY_CAPTCHA',
                        'desc' => 'Get your Google key: <a target="_blank" href="https://www.google.com/recaptcha/intro/index.html">Click here</a>'
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save')
                )
            ),
        );
        $fields_form_iv = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Auto Post Ticket Replies (IMAP)'),
                    'icon' => 'icon-wrench'
                ),
                'description' => 'Requires IMAP enabled on your server. Also requires IMAP enabled
                        on your Email Account.<br /><br />You must trigger below link to update Help Desk messages database. Better option will be to put it in
                        Cron Tab by using official PrestaShop Cron tasks manager module.<br /><br /><b><span style="color:#5F932F">Cron Link:</span> '.$cron_msg.'</b>',
                'input' => array(
                    array(
                        'type' => 'radio',
                        'label' => $this->l('Email Server Type'),
                        'name' => 'HELPDESK_DEFAULT_EMAIL_SERVER',
                        'values' => array(
                            array(
                                'id' => 'st_asc',
                                'value' => '{imap.gmail.com:993/ssl}INBOX',
                                'label' => $this->l('Gmail')
                            ),
                            array(
                                'id' => 'end_dsc',
                                'value' => '{imap.mail.yahoo.com:993/ssl}INBOX',
                                'label' => $this->l('Yahoo Mail')
                            ),
                        ),
                        'desc' => 'Select email server which you are using.'
                    ),
                    array(
                        'type' => 'text',
                        'lang' => false,
                        'label' => $this->l('Username'),
                        'name' => 'HELPDESK_USER_NAME',
                        'suffix' => 'email',
                        'desc' => 'Enter your email address where you receive emails from users.'
                    ),
                    array(
                        'type' => 'text',
                        'lang' => false,
                        'label' => $this->l('Password'),
                        'name' => 'HELPDESK_PASSWORD',
                        'desc' => 'Email account password.'
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save')
                )
            ),
        );
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->module = $this;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitHd';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'uri' => $this->getPathUri(),
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );
        return $helper->generateForm(array($fields_form, $fields_form_i, $fields_form_ii, $fields_form_iii, $fields_form_iv));
    }
    
    public function getConfigFieldsValues()
    {
        $languages = Language::getLanguages(false);
        $fields = array();
        foreach ($languages as $lang) {
            $fields['HELPDESK_PAGE_TITLE'][$lang['id_lang']] = Tools::getValue('HELPDESK_PAGE_TITLE_'.$lang['id_lang'], Configuration::get('HELPDESK_PAGE_TITLE', $lang['id_lang']));
        }
        $fields['HELPDESK_ENABLE_DISABLE'] = (int)Configuration::get('HELPDESK_ENABLE_DISABLE');
        $fields['HELPDESK_LEFT_ENABLE_DISABLE'] = (int)Configuration::get('HELPDESK_LEFT_ENABLE_DISABLE');
        $fields['HELPDESK_DEFAULT_PRIORITY'] = (int)Configuration::get('HELPDESK_DEFAULT_PRIORITY');
        $fields['HELPDESK_PRIORITIES'] = (int)Configuration::get('HELPDESK_PRIORITIES');
        $fields['HELPDESK_SHOW_DEPARTMENTS'] = (int)Configuration::get('HELPDESK_SHOW_DEPARTMENTS');
        $fields['HELPDESK_DEFAULT_DEPARTMENT'] = (int)Configuration::get('HELPDESK_DEFAULT_DEPARTMENT');
        $fields['HELPDESK_DEFAULT_NEW_STATUS'] = (int)Configuration::get('HELPDESK_DEFAULT_NEW_STATUS');
        $fields['HELPDESK_DEFAULT_CLOSE_STATUS'] = (int)Configuration::get('HELPDESK_DEFAULT_CLOSE_STATUS');
        $fields['HELPDESK_FILE_UPLOADS'] = (int)Configuration::get('HELPDESK_FILE_UPLOADS');
        $fields['HELPDESK_MAX_FILE_SIZE'] = (int)Configuration::get('HELPDESK_MAX_FILE_SIZE');
        $fields['HELPDESK_NEW_TICKET_ALERT'] = (int)Configuration::get('HELPDESK_NEW_TICKET_ALERT');
        $fields['HELPDESK_NEW_MESSAGE_ALERT'] = (int)Configuration::get('HELPDESK_NEW_MESSAGE_ALERT');
        $fields['HELPDESK_DEFAULT_ALERT_NAME'] = Configuration::get('HELPDESK_DEFAULT_ALERT_NAME');
        $fields['HELPDESK_ACCEPTED_FILE_TYPES'] = Configuration::get('HELPDESK_ACCEPTED_FILE_TYPES');
        $fields['HELPDESK_EMAIL_COPY'] = Configuration::get('HELPDESK_EMAIL_COPY');
        $fields['HELPDESK_DEFAULT_ALERT_EMAIL'] = Configuration::get('HELPDESK_DEFAULT_ALERT_EMAIL');
        $fields['HELPDESK_NEW_TICKET_RESPOND'] = (int)Configuration::get('HELPDESK_NEW_TICKET_RESPOND');
        $fields['HELPDESK_NEW_MESSAGE_RESPOND'] = (int)Configuration::get('HELPDESK_NEW_MESSAGE_RESPOND');
        $fields['HELPDESK_CLOSE_TICKET_NOTICE'] = (int)Configuration::get('HELPDESK_CLOSE_TICKET_NOTICE');
        $fields['HELPDESK_ALLOW_CUSTOMERS_CLOSE'] = (int)Configuration::get('HELPDESK_ALLOW_CUSTOMERS_CLOSE');
        $fields['HELPDESK_ENABLE_GOOGLE_CAPTCHA'] = Configuration::get('HELPDESK_ENABLE_GOOGLE_CAPTCHA');
        $fields['HELPDESK_SITEKEY_CAPTCHA'] = Configuration::get('HELPDESK_SITEKEY_CAPTCHA');
        $fields['HELPDESK_DEFAULT_EMAIL_SERVER'] = Configuration::get('HELPDESK_DEFAULT_EMAIL_SERVER');
        $fields['HELPDESK_USER_NAME'] = Configuration::get('HELPDESK_USER_NAME');
        $fields['HELPDESK_PASSWORD'] = Configuration::get('HELPDESK_PASSWORD');
        return $fields;
    }
    
    public function hookDisplayBackOfficeHeader()
    {
        $this->context->controller->addCSS($this->_path.'views/css/admin.css');
    }

    public function cronTask()
    {
        $mbox = imap_open(Configuration::get('HELPDESK_DEFAULT_EMAIL_SERVER'), Configuration::get('HELPDESK_USER_NAME'), Configuration::get('HELPDESK_PASSWORD'));
        $flag_m = 0;
        $unread_emails = imap_search($mbox, 'UNSEEN');
        if ($unread_emails) {
            foreach ($unread_emails as $unread) {
                $header = imap_header($mbox, $unread);
                $fromInfo = $header->from[0];
                $replyInfo = $header->reply_to[0];

                $details = array(
                    'fromAddr' => (isset($fromInfo->mailbox) && isset($fromInfo->host))? $fromInfo->mailbox.'@'.$fromInfo->host : '',
                    'fromName' => (isset($fromInfo->personal))? $fromInfo->personal : '',
                    'replyAddr' => (isset($replyInfo->mailbox) && isset($replyInfo->host))? $replyInfo->mailbox.'@'.$replyInfo->host : '',
                    //'replyName' => (isset($replyTo->personal))? $replyto->personal : '',
                    'subject' => (isset($header->subject))? $header->subject : '',
                    'udate' => (isset($header->udate))? $header->udate : ''
                    );

                $reg_cus = Tickets::getRegcus();

                foreach ($reg_cus as $cus) {
                    if ($cus['email'] == $details['fromAddr']) {
                        $sub = explode('#', $details['subject']);
                        $count = count($sub);
                        if ($count > 1) {
                            $id_ticket = end($sub);
                            $flag_m = 1;
                            $unread_body = imap_fetch_overview($mbox, $unread, 0);
                            $unread_content = $this->getBody($unread_body[0]->uid, $mbox);
                            $attachment = '';
                            if (Tickets::insmailresp($id_ticket, $unread_content, $attachment, $cus['id_customer'], $details['udate'])) {
                                Tickets::sendAdminEmail($id_ticket, 1, $unread_content, 1);
                                echo 'All unread Emails related to tickets are Posted successfully!';
                            }
                        }
                    }
                }
                if ($flag_m == 0) {
                    echo $this->l('There is no unread mail related to Helpdesk Tickets!');
                }
            }
                imap_close($mbox);
        } else {
            echo $this->l('There is no unread mail related to Helpdesk Tickets!');
        }
    }

    public function getBody($uid, $imap)
    {
        $body = $this->getPart($imap, $uid, 'TEXT/HTML');
        // if HTML body is empty, try getting text body
        if ($body == '') {
            $body = $this->getPart($imap, $uid, 'TEXT/PLAIN');
        }
        return $body;
    }

    public function getPart($imap, $uid, $mimetype, $structure = false, $partNumber = false)
    {
        if (!$structure) {
            $structure = imap_fetchstructure($imap, $uid, FT_UID);
        }
        if ($structure) {
            if ($mimetype == $this->getMimeType($structure)) {
                if (!$partNumber) {
                    $partNumber = 1;
                }
                $text = imap_fetchbody($imap, $uid, $partNumber, FT_UID);
                switch ($structure->encoding) {
                    case 3:
                        return imap_base64($text);
                    case 4:
                        return imap_qprint($text);
                    default:
                        return $text;
                }
            }
            // multipart
            if ($structure->type == 1) {
                foreach ($structure->parts as $index => $subStruct) {
                    $prefix = '';
                    if ($partNumber) {
                        $prefix = $partNumber.'.';
                    }
                    $data = $this->getPart($imap, $uid, $mimetype, $subStruct, $prefix.($index + 1));
                    if ($data) {
                        return $data;
                    }
                }
            }
        }
        return false;
    }

    public function getMimeType($structure)
    {
        $primaryMimetype = array('TEXT', 'MULTIPART', 'MESSAGE', 'APPLICATION', 'AUDIO', 'IMAGE', 'VIDEO', 'OTHER');
        if ($structure->subtype) {
            return $primaryMimetype[(int)$structure->type].'/'.$structure->subtype;
        }
        return 'TEXT/PLAIN';
    }

    public function setMedia()
    {
        parent::setMedia();
    }

    public function hookDisplayCustomerAccount()
    {
        if (Configuration::get('HELPDESK_ENABLE_DISABLE') && Tools::version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            return $this->display(__FILE__, 'my-account_17.tpl');
        } elseif (Configuration::get('HELPDESK_ENABLE_DISABLE') && Tools::version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
            return $this->display(__FILE__, 'my-account.tpl');
        } else {
            return false;
        }
    }

    public function hookLeftColumn()
    {
        $customer_id = (int)$this->context->cookie->id_customer;
        $left_enable = (int)Configuration::get('HELPDESK_LEFT_ENABLE_DISABLE');
        $mod_enable = (int)Configuration::get('HELPDESK_ENABLE_DISABLE');
        $model = new Tickets();

        if ($customer_id > 0 && $left_enable > 0 && $mod_enable > 0) {
            $latest_tickets = $model->getLatestTickets($customer_id);
            $PS_VERSION = (Tools::version_compare(_PS_VERSION_, '1.7.0.0', '>=') == true) ? 1 : 0;
            if ($PS_VERSION > 0) {
                $force_ssl = (Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE'));
                    $this->context->smarty->assign(array(
                        'base_dir' => _PS_BASE_URL_.__PS_BASE_URI__,
                        'base_dir_ssl' => _PS_BASE_URL_SSL_.__PS_BASE_URI__,
                        'force_ssl' => $force_ssl
                        ));
            }
            $this->context->smarty->assign('latest_tickets', $latest_tickets);
            $this->context->smarty->assign('ps_ver', $PS_VERSION);
            return $this->display(__FILE__, 'leftcolumn.tpl');
        } else {
            return false;
        }
    }

    public function hookModuleRoutes()
    {
        return array('module-helpdesk-helpdesk' => array(
                'controller' => 'helpdesk',
                'rule' => 'helpdesk',
                'keywords' => array(),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'helpdesk',
                ),
            ),
        );
    }


    public function hookActionExportGDPRData($customer)
    {
        if (!Tools::isEmpty($customer['email']) && Validate::isEmail($customer['email'])) {
            $sql = "SELECT ht.*, htr.*
            FROM `"._DB_PREFIX_."fmm_hd_tickets` ht
            LEFT JOIN `"._DB_PREFIX_."fmm_hd_tickets_responses` htr ON (ht.`ticket_id` = htr.`r_ticket_id`)
            WHERE ht.`t_customer_id` = '".pSQL($customer['id'])."'";
            $res = Db::getInstance()->ExecuteS($sql);
            $result = array();
            foreach ($res as $key => $res1) {
                $result[$key][$this->l('First Name')] = $customer['firstname'];
                $result[$key][$this->l('Last Name')] = $customer['lastname'];
                $result[$key][$this->l('Email')] = $customer['email'];
                $result[$key][$this->l('Ticket No.')] = $res1['ticket_id'];
                $result[$key][$this->l('Subject')] = $res1['ticket_subject'];
                $result[$key][$this->l('Ticket Attachment')] = $res1['ticket_attachment'];
                $result[$key][$this->l('Posted Date and Time')] = $res1['t_created_time'];
                $result[$key][$this->l('Ticket Responses')] = $res1['r_message'];
                $result[$key][$this->l('Ticket Response Attachments')] = $res1['r_attachment'];
            }
            if ($result) {
                return json_encode($result);
            } else {
                return json_encode($this->l('HelpDesk Popup : Unable to export customer using email.'));
            }
        }
    }
    public function hookActionDeleteGDPRCustomer($customer)
    {
        if (!empty($customer['email']) && Validate::isEmail($customer['email'])) {
            $sql = "DELETE ht.*, htr.*
            FROM `"._DB_PREFIX_."fmm_hd_tickets` ht
            LEFT JOIN `"._DB_PREFIX_."fmm_hd_tickets_responses` htr ON (ht.`ticket_id` = htr.`r_ticket_id`)
            WHERE ht.`t_customer_id` = '".pSQL($customer['id'])."'";
            if (Db::getInstance()->execute($sql)) {
                return json_encode(true);
            }
            return json_encode($this->l('HelpDesk Popup : Unable to delete customer using email.'));
        }
    }
}
