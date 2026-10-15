<?php
/**
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License version 3.0
* that is bundled with this package in the file LICENSE.txt
* It is also available through the world-wide-web at this URL:
* https://opensource.org/licenses/AFL-3.0
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to a newer
* versions in the future. If you wish to customize this module for your
* needs please refer to CustomizationPolicy.txt file inside our module for more information.
*
* @author Webkul IN
* @copyright Since 2010 Webkul
* @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*/

require_once dirname(__FILE__).'/classes/HelpDeskClassInclude.php';

class WkHelpDesk extends Module
{
    private $postErrors = array();

    public function __construct()
    {
        $this->name = 'wkhelpdesk';
        $this->tab = 'front_office_features';
        $this->version = '5.0.0';
        $this->module_key = '73ddfe405c708dea7962bd45723057a1';
        $this->author = 'Webkul';
        if (_PS_VERSION_ >= '1.7') {
            $this->secure_key = Tools::hash($this->name);
        } else {
            $this->secure_key = Tools::encrypt($this->name);
        }
        $this->bootstrap = true;
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => '1.7');
        parent::__construct();

        $this->displayName = $this->l('Help Desk');
        $this->description = $this->l('Customer can create tickets.');
    }

    public function enable($force_all = false)
    {
        if (Shop::isFeatureActive()) {
            $this->uninstallOverrides();
        }
        return parent::enable($force_all);
    }

    public function disable($force_all = false)
    {
        if (parent::disable($force_all)) {
            if (Shop::isFeatureActive()) {
                $sql = 'SELECT `id_module` FROM `' . _DB_PREFIX_ . 'module_shop`
                WHERE `id_module` = ' . (int) $this->id;
                $ifExists = Db::getInstance()->getValue($sql);
                if ($ifExists) {
                    $this->installOverrides();
                }
            }
            return true;
        }
        return false;
    }


    public function getStatusTextById($idStatus)
    {
        $wkStsCode = new WkHdStatusCode();
        $data = $wkStsCode->getStatusInfoById($idStatus, true);
        if ($data) {
            return $data['ticket_status'];
        }
    }

    public function getMappedStatusIdByStatus($idStatus)
    {
        $wkStsMap = new WkHdStatusMapping();
        $data = $wkStsMap->getMappedStatusIdByStatuss($idStatus);
        if ($data) {
            return $data;
        } else {
            return false;
        }
    }

    public function getContent()
    {
        $objHdEmployee = new WkHdTicketAgent();
        if ($objHdEmployee->validateEmployee($this->context->employee)) {
            $this->context->smarty->assign('wkhdsubmit', 'general');
            if (Tools::isSubmit('submitGeneral') || Tools::isSubmit('wksyncimap')) {
                if (Tools::isSubmit('submitGeneral')) {
                    $this->validateAndSaveGeneralConfig();
                } else {
                    $data = WkHdTicketToken::syncImap();
                    if ($data['hasError'] == true) {
                        $this->postErrors[] = $data['errors'];
                        $this->context->smarty->assign('errors', $this->postErrors);
                    } else {
                        $this->context->smarty->assign('successes', $this->l('IMAP server message imported successfully.'));
                    }
                }
            } elseif (Tools::isSubmit('submitMail')) {
                Media::addJsDef(array('wkhdsubmit' => 'mail'));
                $this->context->smarty->assign('wkhdsubmit', 'mail');
                Configuration::updateValue(
                    'WK_HD_NEW_TICKET_CUSTOMER_NOTIFICATON',
                    Tools::getValue('customerNotification')
                );
                Configuration::updateValue(
                    'WK_HD_NEW_TICKET_AGENT_NOTIFICATON',
                    Tools::getValue('agentNotification')
                );
                $this->context->smarty->assign('successes', $this->l('Saved successfully.'));
                Configuration::updateValue('WK_HD_STATUS_UPDATE_MAIL', Tools::getValue('statusUpdateMail'));
                Configuration::updateValue('WK_HD_CUSTOMER_REPLY_MAIL', Tools::getValue('customerReplyMail'));
            } elseif (Tools::isSubmit('submitSeo')) {
                $this->context->smarty->assign('wkhdsubmit', 'seo');
                $this->validateAndSaveSeoConfig();
            } elseif (Tools::isSubmit('submitCaptcha')) {
                $this->context->smarty->assign('wkhdsubmit', 'captcha');
                $this->validateAndSaveCaptchaConfig();
            }
            $link = $this->context->link->getModuleLink('wkhelpdesk', 'cronticket');
            $this->context->smarty->assign(
                array(
                    'fileType' => WkHdTicket::getAllFileExtension(),
                    'bgColor' => Configuration::get('WK_HD_TITLE_BG_COLOR'),
                    'textColor' => Configuration::get('WK_HD_TITLE_TEXT_COLOR'),
                    'newTicketUrl' => Configuration::get('WK_HD_NEW_TICKET_URL'),
                    'viewTicketUrl' => Configuration::get('WK_HD_VIEW_TICKET_URL'),
                    'helpdeskUrlRewrite' => Configuration::get('WK_HD_URL_REWRITING'),
                    'moduleLink' => $this->context->link->getAdminLink('AdminModules'),
                    'statusUpdateMail' => Configuration::get('WK_HD_STATUS_UPDATE_MAIL'),
                    'customerReplyMail' => Configuration::get('WK_HD_CUSTOMER_REPLY_MAIL'),
                    'agentNotification' => Configuration::get('WK_HD_NEW_TICKET_AGENT_NOTIFICATON'),
                    'customerNotification' => Configuration::get('WK_HD_NEW_TICKET_CUSTOMER_NOTIFICATON'),
                    'enableCaptcha' => Configuration::get('WK_HD_ENABLE_CREATE_CAPTCHA'),
                    'enableCaptchaViewTicket' => Configuration::get('WK_HD_ENABLE_VIEW_CAPTCHA'),
                    'captchaSiteKey' => Configuration::get('WK_HD_CAPTCHA_SITE_KEY'),
                    'captchaSecretKey' => Configuration::get('WK_HD_CAPTCHA_SECRET_KEY'),
                    'enabledGuestTicket' => Configuration::get('WK_HD_GUEST_TICKET'),
                    'version' => $this->version,
                    'docLink' => _MODULE_DIR_.$this->name.'/doc_en.pdf',
                    'cron_url' => '5 0 * * * curl '.$link.'?token='.$this->secure_key,
                )
            );
            $this->context->controller->addJqueryPlugin('colorpicker');
        } else {
            $this->context->smarty->assign('permissionError', 1);
        }
        Media::addJsDef(array(
            'wkModuleAddonKey' => $this->module_key,
            'wkModuleAddonsId' => 22848,
            'wkModuleTechName' => $this->name,
            'wkModuleDoc' => file_exists(_PS_MODULE_DIR_.$this->name.'/doc_en.pdf'),
            //'wkAddonsDemo' => Configuration::get('WK_DEMO_PS_ADDON')// ONLY add in wkrepo for docker demo
        ));
        $this->context->controller->addJs('https://prestashop.webkul.com/crossselling/wkcrossselling.min.js?t='.time());

        return $this->display(__FILE__, '/views/templates/admin/config.tpl');
    }

    private function validateAndSaveGeneralConfig()
    {
        $bgColor = Tools::getValue('bgColor');
        $fileType = Tools::getValue('fileType');
        $textColor = Tools::getValue('textColor');
        $enabledGuestTicket = Tools::getValue('enabledGuestTicket');
        if ($bgColor == '') {
            $this->postErrors[] = $this->l('Page title background color is required field.');
        } elseif (!Validate::isColor($bgColor)) {
            $this->postErrors[] = $this->l('Page title background color value is not valid.');
        }

        if ($textColor == '') {
            $this->postErrors[] = $this->l('Page title text color is required field.');
        } elseif (!Validate::isColor($textColor)) {
            $this->postErrors[] = $this->l('Page title text color value is not valid.');
        }

        if (!$fileType) {
            $this->postErrors[] = $this->l('You must select atleast one file type extension.');
        }
        if (empty($this->postErrors)) {
            $this->context->smarty->assign('successes', $this->l('Saved successfully.'));
            Media::addJsDef(array('wkhdsubmit' => 'general'));
            Configuration::updateValue('WK_HD_TITLE_BG_COLOR', $bgColor);
            Configuration::updateValue('WK_HD_GUEST_TICKET', $enabledGuestTicket);
            Configuration::updateValue('WK_HD_TITLE_TEXT_COLOR', $textColor);
            WkHdTicket::setSelectedFileExtension($fileType);
        } else {
            $this->context->smarty->assign('errors', $this->postErrors);
        }
    }

    private function validateAndSaveSeoConfig()
    {
        $newTicketUrl = Tools::getValue('newTicketUrl');
        $viewTicketUrl = Tools::getValue('viewTicketUrl');
        $helpdeskUrlRewrite = Tools::getValue('helpdeskUrlRewrite');

        if ($helpdeskUrlRewrite) {
            if ($newTicketUrl == '') {
                $this->postErrors[] = $this->l('New ticket URL is required field.');
            } elseif (Tools::link_rewrite($newTicketUrl) != str_replace('/', '-', $newTicketUrl)) {
                $this->postErrors[] = $this->l('New ticket URL is invalid.');
            }

            if ($viewTicketUrl == '') {
                $this->postErrors[] = $this->l('View ticket URL is required field.');
            } elseif (Tools::link_rewrite($viewTicketUrl) != str_replace('/', '-', $viewTicketUrl)) {
                $this->postErrors[] = $this->l('View ticket URL is invalid.');
            }
        }
        if (empty($this->postErrors)) {
            $this->context->smarty->assign('successes', $this->l('Saved successfully.'));
            Media::addJsDef(array('wkhdsubmit' => 'seo'));
            Configuration::updateValue('WK_HD_URL_REWRITING', $helpdeskUrlRewrite);
            Configuration::updateValue('WK_HD_NEW_TICKET_URL', $newTicketUrl);
            Configuration::updateValue('WK_HD_VIEW_TICKET_URL', $viewTicketUrl);
        } else {
            $this->context->smarty->assign('errors', $this->postErrors);
        }
    }

    private function validateAndSaveCaptchaConfig()
    {
        $captchaEnabled = Tools::getValue('enableCaptcha');
        $captchaSiteKey = Tools::getValue('captchaSiteKey');
        $captchaSecretKey = Tools::getValue('captchaSecretKey');
        $enableCaptchaViewTicket = Tools::getValue('enableCaptchaViewTicket');
        if ($captchaEnabled || $enableCaptchaViewTicket) {
            if (!Tools::strlen(trim($captchaSiteKey))) {
                $this->postErrors[] = $this->l('Please enter captcha site key');
            }
            if (!Tools::strlen(trim($captchaSecretKey))) {
                $this->postErrors[] = $this->l('Please enter captcha secret key');
            }
        }
        if (empty($this->postErrors)) {
            $this->context->smarty->assign('successes', $this->l('Saved successfully.'));
            Media::addJsDef(array('wkhdsubmit' => 'captcha'));
            Configuration::updateValue('WK_HD_ENABLE_CREATE_CAPTCHA', $captchaEnabled);
            Configuration::updateValue('WK_HD_ENABLE_VIEW_CAPTCHA', $enableCaptchaViewTicket);
            Configuration::updateValue('WK_HD_CAPTCHA_SITE_KEY', $captchaSiteKey);
            Configuration::updateValue('WK_HD_CAPTCHA_SECRET_KEY', $captchaSecretKey);
        } else {
            $this->context->smarty->assign('errors', $this->postErrors);
        }
    }

    public function hookdisplayNav1()
    {
        if (Configuration::get('WK_HD_GUEST_TICKET')) {
            return $this->fetch('module:wkhelpdesk/views/templates/hook/helpdeskcontactus.tpl');
        } elseif ($this->context->customer->id) {
            return $this->fetch('module:wkhelpdesk/views/templates/hook/helpdeskcontactus.tpl');
        }
    }

    public function hookActionFrontControllerSetMedia($params)
    {
        $this->context->controller->registerStylesheet('wknavstyle-css', 'modules/wkhelpdesk/views/css/wknavstyle.css');
    }

    public function hookDisplayCustomerAccount()
    {
        return $this->fetch('module:wkhelpdesk/views/templates/hook/helpdeskmyaccountmenu.tpl');
    }

    public function insertPreData()
    {
        Shop::setContext(Shop::CONTEXT_ALL);
        $defaultIdGroup = 0;
        $languages = Language::getLanguages(true);
        $accessRights = WkHdAccessRight::getAllAccessRights();
        $allStatusCode = WkHdStatusMapping::getAllStatusCode(); //get all status code
        $status = array('Open', 'Closed', 'Answered', 'Pending', 'Resolved', 'Spam');
        foreach ($status as $sts) {
            $objStatus = new WkHdStatusCode();
            foreach ($languages as $language) {
                $objStatus->ticket_status[$language['id_lang']] = pSQL($sts);
            }
            $objStatus->save();
            $savedIdQuery = $objStatus->id;
            $mapObj = new WkHdStatusMapping();
            $mapObj->id_status = (int) $savedIdQuery;
            $mapObj->id_status_selected = (int) $savedIdQuery;
            $mapObj->save();
        }

        $employees = Employee::getEmployees();
        foreach ($employees as $employee) {
            $objEmployee = new Employee((int) $employee['id_employee']);
            if ($objEmployee->isSuperAdmin()) { // check is super admin
                $objTicketAgent = new WkHdTicketAgent();
                $objTicketAgent->employee_id = (int) $employee['id_employee'];
                $objTicketAgent->name = pSQL($objEmployee->firstname).' '.pSQL($objEmployee->lastname);
                $objTicketAgent->email = pSQL($objEmployee->email);
                $objTicketAgent->is_super_admin = (int) 1; // set super admin
                $objTicketAgent->active = (int) 1; // set super admin as active ticket agent
                $objTicketAgent->save();

                foreach ($accessRights as $access_rights) {
                    $objAccessRightMapping = new WkHdAccessRightMapping();
                    $objAccessRightMapping->id_agent = (int) $objTicketAgent->id;
                    $objAccessRightMapping->id_access_right = (int) $access_rights['id'];
                    $objAccessRightMapping->active = (int) 1;
                    $objAccessRightMapping->save();
                }
            }
        }

        $objGroup = new WkHdGroup(); // insert default group
        $objGroup->is_default_group = (int) 1;
        $objGroup->active = (int) 1;
        foreach ($languages as $language) {
            $objGroup->group_name[$language['id_lang']] = pSQL('Default group');
        }
        $objGroup->save();
        $defaultIdGroup = $objGroup->id;

        $queryTypes = array('Support', 'Pre sale query');
        foreach ($queryTypes as $queryType) {
            $objQueryType = new WkHdQueryType();
            $objQueryType->active = 1;
            foreach ($languages as $language) {
                $objQueryType->query_name[$language['id_lang']] = pSQL($queryType);
            }
            $objQueryType->save();
        }

        if ($defaultIdGroup) {
            $objQueryType = new WkHdQueryType();
            $queryTypes = $objQueryType->getAllQueryType(false, true);
            if ($queryTypes) {
                foreach ($queryTypes as $queryType) {
                    $objGroupQtMapping = new WkHdGroupQueryTypeMapping();
                    $objGroupQtMapping->id_group = (int) $defaultIdGroup;
                    $objGroupQtMapping->id_query_type = (int) $queryType['id'];
                    $objGroupQtMapping->save();
                }
            }
        }

        return true;
    }

    public function callInstallTab()
    {
        $this->installTab('AdminHelpDesk', 'Help desk');
        $this->installTab('AdminHelpDeskManagement', 'Help desk', 'AdminHelpDesk');
        $this->installTab('AdminAllTicket', 'All tickets', 'AdminHelpDeskManagement');
        $this->installTab('AdminSpamTicket', 'Spam user tickets', 'AdminHelpDeskManagement');
        $this->installTab('AdminAgentManagement', 'Agents', 'AdminHelpDeskManagement');
        $this->installTab('AdminGroupManagement', 'Groups', 'AdminHelpDeskManagement');
        $this->installTab('AdminTicketStatus', 'Ticket status', 'AdminHelpDeskManagement');
        $this->installTab('AdminTicketStatusMapping', 'Ticket status mapping', 'AdminHelpDeskManagement');
        $this->installTab('AdminQueryTypeManagement', 'Query types', 'AdminHelpDeskManagement');

        return true;
    }

    public function installTab($className, $tabName, $tabParentName = false)
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = $className;
        $tab->name = array();

        if ($className == 'AdminHelpDeskManagement') {
            $tab->icon = 'chat';
        }

        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $tabName;
        }

        if ($tabParentName) {
            $tab->id_parent = (int) Tab::getIdFromClassName($tabParentName);
        } else {
            $tab->id_parent = 0;
        }

        $tab->module = $this->name;
        return $tab->add();
    }

    public function hookActionCustomerAccountAdd($params)
    {
        $email = $params['newCustomer']->email;
        if ($email) {
            $objHdCustomer = new WkHdCustomer();
            $objHdCustomer->updateCustomerInfoByEmail(
                $email,
                $params['newCustomer']->firstname,
                $params['newCustomer']->lastname,
                $params['newCustomer']->id
            );
        }
    }

    public function hookActionObjectCustomerUpdateBefore($params)
    {
        if (is_array($params)) {
            $firstName = $params['object']->firstname;
            $lastName = $params['object']->lastname;
            $idCustomer = $params['object']->id;
            $email = $params['object']->email;
            if ($firstName && $lastName && $idCustomer && $email) {
                $objHdCustomer = new WkHdCustomer();
                $objHdCustomer->updateCustomerInfoByIdCustomer($idCustomer, $firstName, $lastName, $email);
            }
        }
    }

    public function hookModuleRoutes()
    {
        if (Configuration::get('WK_HD_URL_REWRITING')) {
            $newTicketUrl = Configuration::get('WK_HD_NEW_TICKET_URL');
            $viewTicketUrl = Configuration::get('WK_HD_VIEW_TICKET_URL');

            return array(
                'module-wkhelpdesk-createticket' => array(
                    'controller' => 'createticket',
                    'rule' => "$newTicketUrl",
                    'keywords' => array(),
                    'params' => array(
                        'fc' => 'module',
                        'module' => 'wkhelpdesk',
                        'controller' => 'createticket',
                    ),
                ),
                'module-wkhelpdesk-viewticket' => array(
                    'controller' => 'viewticket',
                    'rule' => "$viewTicketUrl/{:id}",
                    'keywords' => array(
                        'id' => array(
                            'regexp' => '[_0-9_-]+',
                            'param' => 'id',
                        ),
                    ),
                    'params' => array(
                        'fc' => 'module',
                        'module' => 'wkhelpdesk',
                        'controller' => 'viewticket',
                    ),
                ),
            );
        }
    }

    public function hookActionObjectLanguageAddAfter($params)
    {
        if ($params['object']->id) {
            //Assign all lang's main table in an ARRAY
            $langTables = array('wk_hd_group', 'wk_hd_query_type');

            //If Admin create any new language when we do entry in module all lang tables.
            WkHdTicket::insertLangIdinAllTables($params['object']->id, $langTables);
        }
    }

    public function hookActionObjectEmployeeAddAfter($params)
    {
        if ($params['object']->id && $params['object']->id_profile == 1) {
            $objTicketAgent = new WkHdTicketAgent();
            $objTicketAgent->employee_id = (int) $params['object']->id;
            $objTicketAgent->name = pSQL($params['object']->firstname).' '.pSQL($params['object']->lastname);
            $objTicketAgent->email = pSQL($params['object']->email);
            $objTicketAgent->is_super_admin = (int) 1; // set super admin
            $objTicketAgent->active = (int) 1; // set super admin as active ticket agent
            $objTicketAgent->save();

            if ($objTicketAgent->id) { // check is super admin created as ticket agent
                $accessRights = WkHdAccessRight::getAllAccessRights();
                foreach ($accessRights as $accessRight) {
                    $objAccessRightMapping = new WkHdAccessRightMapping();
                    $objAccessRightMapping->id_agent = (int) $objTicketAgent->id;
                    $objAccessRightMapping->id_access_right = (int) $accessRight['id'];
                    $objAccessRightMapping->active = (int) 1;
                    $objAccessRightMapping->save();
                }
            }
        }
    }

    public function hookActionObjectEmployeeUpdateAfter($params)
    {
        if ($params['object']->id) {
            $objTicketAgent = new WkHdTicketAgent();
            $agentInfo = $objTicketAgent->getAgentInfoByIdEmployee($params['object']->id);
            if ($params['object']->id_profile > 1) {
                if ($agentInfo && $agentInfo['is_super_admin']) {
                    $objTicketAgent = new WkHdTicketAgent((int) $agentInfo['id']);
                    $objTicketAgent->delete();
                    $objAccessRightMapping = new WkHdAccessRightMapping();
                    $objAccessRightMapping->deleteAccessRightByIdAgent($agentInfo['id']);
                }
                if ($agentInfo) {
                    $objTicketAgent = new WkHdTicketAgent((int) $agentInfo['id']);
                    if ($agentInfo['is_super_admin']) {
                        $objTicketAgent->delete();
                        $objAccessRightMapping = new WkHdAccessRightMapping();
                        $objAccessRightMapping->deleteAccessRightByIdAgent($agentInfo['id']);
                    } else {
                        $objTicketAgent->employee_id = (int) $params['object']->id;
                        $objTicketAgent->name = pSQL($params['object']->firstname).' '.pSQL($params['object']->lastname);
                        $objTicketAgent->email = pSQL($params['object']->email);
                        $objTicketAgent->is_super_admin = (int) 0; // set super admin
                        $objTicketAgent->active = (int) 1; // set super admin as active ticket agent
                        $objTicketAgent->save();
                    }
                }
            } elseif ($params['object']->id_profile == 1) {
                if ($agentInfo) {
                    $objTicketAgent = new WkHdTicketAgent((int) $agentInfo['id']);
                } else {
                    $objTicketAgent = new WkHdTicketAgent();
                }

                $objTicketAgent->employee_id = (int) $params['object']->id;
                $objTicketAgent->name = pSQL($params['object']->firstname).' '.pSQL($params['object']->lastname);
                $objTicketAgent->email = pSQL($params['object']->email);
                $objTicketAgent->is_super_admin = (int) 1; // set super admin
                $objTicketAgent->active = (int) 1; // set super admin as active ticket agent
                $objTicketAgent->save();

                if ($objTicketAgent->id) { // check is super admin created as ticket agent
                    $objAccessRightMapping = new WkHdAccessRightMapping();
                    $objAccessRightMapping->deleteAccessRightByIdAgent((int) $objTicketAgent->id);
                    $accessRights = WkHdAccessRight::getAllAccessRights();
                    foreach ($accessRights as $access_rights) {
                        $objAccessRightMapping = new WkHdAccessRightMapping();
                        $objAccessRightMapping->id_agent = (int) $objTicketAgent->id;
                        $objAccessRightMapping->id_access_right = (int) $access_rights['id'];
                        $objAccessRightMapping->active = (int) 1;
                        $objAccessRightMapping->save();
                    }
                }
            }
        }
    }

    public function hookActionObjectCustomerDeleteAfter($params)
    {
        $objHdCustomer = new WkHdCustomer();
        // set ps id customer to 0 when customer deleted from ps
        $objHdCustomer->updateCustomerInfoByEmail(
            $params['object']->email,
            $params['object']->firstname,
            $params['object']->lastname,
            0
        );
    }

    /**
     * Module Installation Process.
     */
    public function createTable()
    {
        if (!file_exists(dirname(__FILE__).'/'.self::INSTALL_SQL_FILE)) {
            return false;
        } elseif (!$sql = Tools::file_get_contents(dirname(__FILE__).'/'.self::INSTALL_SQL_FILE)) {
            return false;
        }

        $sql = str_replace(array('PREFIX_',  'ENGINE_TYPE'), array(_DB_PREFIX_, _MYSQL_ENGINE_), $sql);
        $sql = preg_split("/;\s*[\r\n]+/", $sql);

        foreach ($sql as $query) {
            if ($query) {
                if (!Db::getInstance()->execute(trim($query))) {
                    return false;
                }
            }
        }

        return true;
    }

    public function hookActionDeleteGDPRCustomer($customer)
    {
        if (!empty($customer['email']) && Validate::isEmail($customer['email'])) {
            $objCustomerHd = new WkHdCustomer();
            $delete = $objCustomerHd->deleteHdCustomerByEmail($customer['email']);
            if ($delete) {
                return json_encode(true);
            }
            return json_encode($this->l('Help Desk : Unable to delete customer using email.'));
        }
    }

    public function hookActionExportGDPRData($customer)
    {
        if (!Tools::isEmpty($customer['email']) && Validate::isEmail($customer['email'])) {
            $objHdCustomer = new WkHdCustomer();
            $res = $objHdCustomer->getCustomerByEmail($customer['email']);
            if ($res && !empty($res)) {
                return json_encode($res);
            }
            return json_encode($this->l('Help Desk : Unable to export customer using email.'));
        }
    }

    public function registerPsHook()
    {
        return $this->registerHook(
            array(
                'displayNav1',
                'moduleRoutes',
                'displayHeader',
                'displayCustomerAccount',
                'displayBackOfficeHeader',
                'actionCustomerAccountAdd',
                'actionObjectLanguageAddAfter',
                'actionObjectEmployeeAddAfter',
                'ActionFrontControllerSetMedia',
                'actionObjectCustomerDeleteAfter',
                'actionObjectEmployeeUpdateAfter',
                'actionObjectCustomerUpdateBefore',
                'registerGDPRConsent',
                'actionDeleteGDPRCustomer',
                'actionExportGDPRData',
                'actionOrderGridDefinitionModifier',
                'actionAdminOrdersListingFieldsModifier',
                // Customization By Ram Chandra
                'actionObjectCustomerMessageAddAfter',
                'actionObjectWkHdTicketMsgAddAfter'
                // END
            )
        );
    }

    // Customization By Ram Chandra
    public function hookActionObjectCustomerMessageAddAfter($params)
    {
        if (Tools::getValue('wkhelpdesk_addinghookmessage')) {
            return;
        }
        if (isset($params['object'])
            && $params['object'] instanceof CustomerMessage
            && Validate::isLoadedObject($params['object'])
            && isset($params['object']->id)
            && isset($params['object']->id_customer_thread)
            && $params['object']->id
            && $params['object']->id_customer_thread
        ) {
            $_GET['wkhelpdesk_addinghookmessage'] = 1;
            $customerThread = new CustomerThread($params['object']->id_customer_thread);
            $idEmployee = $params['object']->id_employee;
            if (Validate::isLoadedObject($customerThread)) {
                $objCustomer = new Customer();
                if ($customerThread->id_customer) {
                    $objCustomer = new Customer($customerThread->id_customer);
                } elseif (Tools::strlen(trim($customerThread->email)) > 0) {
                    $objCustomer = $objCustomer->getByEmail($customerThread->email);
                }
                if (!Validate::isLoadedObject($objCustomer) && (Tools::strlen(trim($customerThread->email)) > 0)) {
                    $objCustomer = new Customer();
                    $objCustomer->email = $customerThread->email;
                    $objCustomer->id = 0;
                    $objCustomer->firstname = 'Guest';
                    $objCustomer->lastname = 'User ';
                }
                $objTicketManager = new WkHdTicketManager($this, $this->context);
                $mapping = $objTicketManager->getTicketByThread($customerThread->id);
                if (!empty($mapping)) {
                    $idTicket = $mapping['id_ticket'];
                    if ((int) $idTicket > 0) {
                        // Create reply for customer message
                        $objTicketManager->addReplyToTicket($idTicket, $params['object']->message, $params['object']->file_name, $idEmployee);
                    }
                } else {
                    $idTicket = $objTicketManager->createTicketForCustomer($objCustomer, $params['object']->message, (int) $customerThread->id_order, $params['object']->file_name);
                    if ($idTicket) {
                        // Create mapping for thread and ticket
                        $res = $objTicketManager->mapTicketThread($idTicket, $customerThread->id);
                        if ($res) {
                            $this->context->controller->warnings[] = $this->l('Ticket could not be created');
                            return;
                        }
                    }
                }
            }
            unset($_GET['wkhelpdesk_addinghookmessage']);
        }
    }

    public function hookActionObjectWkHdTicketMsgAddAfter($params)
    {
        if (Tools::getValue('wkhelpdesk_addinghookmessage')) {
            return;
        }
        if (isset($params['object'])
            && $params['object'] instanceof WkHdTicketMsg
            && Validate::isLoadedObject($params['object'])
            && isset($params['object']->id)
            && $params['object']->id
            && $params['object']->hd_id_ticket
            && !$params['object']->is_status_update
            && !$params['object']->is_internal_note
            && !$params['object']->is_agent_assign
            && ($params['object']->id_customer || $params['object']->id_agent)
        ) {
            $_GET['wkhelpdesk_addinghookmessage'] = 1;
            // new reply added on ticket, add to customer service thread
            $objTicket = new WkHdTicket($params['object']->hd_id_ticket);
            if (Validate::isLoadedObject($objTicket)) {
                $objHdCustomer = new WkHdCustomer($objTicket->hd_id_customer);
                $objTicketManager = new WkHdTicketManager($this, $this->context);
                $mapping = $objTicketManager->getThreadByTicket($objTicket->id);
                if ($objTicket->id == 1009) {
                    // dump($mapping);die;
                }
                if (!empty($mapping)) {
                    $idThread = $mapping['id_customer_thread'];
                    if ((int) $idThread > 0) {
                        // Create reply for customer message
                        $objTicketManager->addCustomerThreadMessage($idThread, $params['object']);
                    }
                } else {
                    $customer_thread = new CustomerThread();
                    $customer_thread->id_contact = 0;
                    $customer_thread->id_customer = (int) $objHdCustomer->id_ps_customer;
                    $customer_thread->id_shop = (int) $this->context->shop->id;
                    $customer_thread->id_order = (int) $objTicket->id_order;
                    $customer_thread->id_lang = (int) $this->context->language->id;
                    $customer_thread->email = $objHdCustomer->email;
                    $customer_thread->status = 'open';
                    $customer_thread->token = Tools::passwdGen(12);
                    $customer_thread->add();

                    $customer_message = new CustomerMessage();
                    $customer_message->id_customer_thread = $customer_thread->id;
                    $customer_message->id_employee = 0;
                    $customer_message->message = pSQL(Tools::purifyHTML($params['object']->message));
                    $customer_message->private = false;
                    $customer_message->add();
                    $objTicketManager->mapTicketThread($params['object']->hd_id_ticket, $customer_thread->id);
                }
            }
            unset($_GET['wkhelpdesk_addinghookmessage']);
        }
    }
    // END

    public function hookActionOrderGridDefinitionModifier(array $params)
    {

        /** @var GridDefinitionInterface $definition */
        $definition = $params['definition'];

        $definition
            ->getColumns()
            ->addAfter(
                'osname',
                (new WkHelpDesk\Grid\Column\HtmlTypeColumn('wk_button'))
                    ->setName($this->l('Ticket'))
                    ->setOptions([
                        'ModuleClass' => new WkHelpDesk(),
                        'custom_text' => $this->l('Click here')
                    ])
            )
        ;
    }

    public function hookActionAdminOrdersListingFieldsModifier($params)
    {
        if (isset($params['select'])) {
            $params['select'] .= ', a.`id_order` AS ticket';
        }
        $newColumn = array();
        $newColumn['ticket'] = array(
            'title' => $this->l('Ticket'),
            'type' => 'text',
            'align' => 'text-center',
            'class' => 'fixed-width-xl',
            'callback' => 'getsTicketForOrder',
            'havingFilter' => false,
            'orderby' => false,
            'callback_object' => $this
        );
        //show after status in grid
        $params['fields'] = array_merge(
            array_slice($params['fields'], 0, 8),
            $newColumn,
            array_slice($params['fields'], 8)
        );
    }

    public function getsTicketForOrder($id, $data)
    {
        $ticketObj = new WkHdTicket();
        $ticketData = $ticketObj->getTicketByOrderId($data['id_order']);
        if ($ticketData) {
            $this->context->smarty->assign(array(
                'url' => $this->context->link->getAdminLink('AdminAllTicket')
                .'&id='.$ticketData[0]['id'].'&updatewk_hd_ticket'
            ));
            return $this->context->smarty->fetch(
                _PS_MODULE_DIR_.$this->name.'/views/templates/admin/ticketButton.tpl'
            );
        } else {
            return '--';
        }
    }


    public function getTicketForOrder($id)
    {
        $ticketObj = new WkHdTicket();
        $ticketData = $ticketObj->getTicketByOrderId($id);
        if ($ticketData) {
            return $this->context->link->getAdminLink('AdminAllTicket')
            .'&id='.$ticketData[0]['id'].'&updatewk_hd_ticket';
        }
        return false;
    }

    public function insertConfigData()
    {
        Configuration::updateValue(
            'WK_HD_ATTACHMENT_TYPE',
            Tools::jsonEncode(
                array(
                    0 => array('ext_name' => 'txt', 'is_availble' => 1),
                    1 => array('ext_name' => 'rtf', 'is_availble' => 1),
                    2 => array('ext_name' => 'doc', 'is_availble' => 1),
                    3 => array('ext_name' => 'pdf', 'is_availble' => 1),
                    4 => array('ext_name' => 'zip', 'is_availble' => 1),
                    5 => array('ext_name' => 'png', 'is_availble' => 1),
                    6 => array('ext_name' => 'gif', 'is_availble' => 0),
                    7 => array('ext_name' => 'jpg', 'is_availble' => 1),
                    8 => array('ext_name' => 'jpeg', 'is_availble' => 1),
                    9 => array('ext_name' => 'docx', 'is_availble' => 1),
                    10 => array('ext_name' => 'rar', 'is_availble' => 1),
                    11 => array('ext_name' => 'mp4', 'is_availble' => 0),
                    12 => array('ext_name' => 'avi', 'is_availble' => 0),
                    13 => array('ext_name' => 'mkv', 'is_availble' => 0),
                    14 => array('ext_name' => 'flv', 'is_availble' => 0),
                    15 => array('ext_name' => 'mov', 'is_availble' => 0),
                    16 => array('ext_name' => 'wmv', 'is_availble' => 0),
                )
            )
        );
        Configuration::updateValue('WK_HD_GUEST_TICKET', '1');
        Configuration::updateValue('WK_HD_ENABLE_CREATE_CAPTCHA', 0);
        Configuration::updateValue('WK_HD_ENABLE_VIEW_CAPTCHA', 0);
        Configuration::updateValue('WK_HD_CAPTCHA_SITE_KEY', '');
        Configuration::updateValue('WK_HD_CAPTCHA_SECRET_KEY', '');
        Configuration::updateValue('WK_HD_URL_REWRITING', '1');
        Configuration::updateValue('WK_HD_STATUS_UPDATE_MAIL', 1);
        Configuration::updateValue('WK_HD_CUSTOMER_REPLY_MAIL', 1);
        Configuration::updateValue('WK_HD_TITLE_BG_COLOR', '#333333');
        Configuration::updateValue('WK_HD_TITLE_TEXT_COLOR', '#FFFFFF');
        Configuration::updateValue('WK_HD_NEW_TICKET_AGENT_NOTIFICATON', 1);
        Configuration::updateValue('WK_HD_NEW_TICKET_CUSTOMER_NOTIFICATON', 1);
        Configuration::updateValue('WK_HD_NEW_TICKET_URL', 'support/newticket');
        Configuration::updateValue('WK_HD_VIEW_TICKET_URL', 'support/viewticket');

        return true;
    }

    public function install()
    {
        if (!parent::install()
            || !WkHdGroup::createTable()
            || !$this->registerPsHook()
            || !$this->callInstallTab()
            || !$this->insertPreData()
            || !$this->insertConfigData()
        ) {
            return false;
        }

        return true;
    }

    public function dropTables()
    {
        return Db::getInstance()->execute(
            'DROP TABLE IF EXISTS
            `'._DB_PREFIX_.'wk_hd_status_code`,
            `'._DB_PREFIX_.'wk_hd_status_code_lang`,
            `'._DB_PREFIX_.'wk_hd_status_code_shop`,
            `'._DB_PREFIX_.'wk_hd_status_mapping`,
            `'._DB_PREFIX_.'wk_hd_status_mapping_shop`,
            `'._DB_PREFIX_.'wk_hd_access_right`,
            `'._DB_PREFIX_.'wk_hd_ticket_agent`,
            `'._DB_PREFIX_.'wk_hd_ticket_agent_shop`,
            `'._DB_PREFIX_.'wk_hd_access_right_agent_mapping`,
            `'._DB_PREFIX_.'wk_hd_access_right_agent_mapping_shop`,
            `'._DB_PREFIX_.'wk_hd_group`,
            `'._DB_PREFIX_.'wk_hd_group_shop`,
            `'._DB_PREFIX_.'wk_hd_group_lang`,
            `'._DB_PREFIX_.'wk_hd_group_agent_mapping`,
            `'._DB_PREFIX_.'wk_hd_group_agent_mapping_shop`,
            `'._DB_PREFIX_.'wk_hd_query_type`,
            `'._DB_PREFIX_.'wk_hd_query_type_shop`,
            `'._DB_PREFIX_.'wk_hd_query_type_lang`,
            `'._DB_PREFIX_.'wk_hd_group_query_type_mapping`,
            `'._DB_PREFIX_.'wk_hd_group_query_type_mapping_shop`,
            `'._DB_PREFIX_.'wk_hd_ticket`,
            `'._DB_PREFIX_.'wk_hd_ticket_shop`,
            `'._DB_PREFIX_.'wk_hd_ticket_msg`,
            `'._DB_PREFIX_.'wk_hd_ticket_msg_shop`,
            `'._DB_PREFIX_.'wk_hd_ticket_token`,
            `'._DB_PREFIX_.'wk_hd_ticket_token_shop`,
            `'._DB_PREFIX_.'wk_hd_ticket_attachment`,
            `'._DB_PREFIX_.'wk_hd_ticket_attachment_shop`,
            `'._DB_PREFIX_.'wk_hd_customer`,
            `'._DB_PREFIX_.'wk_hd_customer_shop`,
            `'._DB_PREFIX_.'wk_customer_reply_sync_imap`,
            `'._DB_PREFIX_.'wk_customer_reply_sync_imap_shop`,
            `'._DB_PREFIX_.'wk_customer_mark_spam`,
            `'._DB_PREFIX_.'wk_customer_message_sync_imap`,
            `'._DB_PREFIX_.'wk_customer_mark_spam_shop`'
        );
    }

    public function deleteConfiguration()
    {
        $configVars = array(
            'WK_HD_GUEST_TICKET',
            'WK_HD_URL_REWRITING',
            'WK_HD_TITLE_BG_COLOR',
            'WK_HD_NEW_TICKET_URL',
            'WK_HD_VIEW_TICKET_URL',
            'WK_HD_ATTACHMENT_TYPE',
            'WK_HD_TITLE_TEXT_COLOR',
            'WK_HD_STATUS_UPDATE_MAIL',
            'WK_HD_CUSTOMER_REPLY_MAIL',
            'WK_HD_NEW_TICKET_AGENT_NOTIFICATON',
            'WK_HD_NEW_TICKET_CUSTOMER_NOTIFICATON',
            'WK_HD_ENABLE_CREATE_CAPTCHA',
            'WK_HD_ENABLE_VIEW_CAPTCHA',
            'WK_HD_CAPTCHA_SITE_KEY',
            'WK_HD_CAPTCHA_SECRET_KEY',
        );

        foreach ($configVars as $key) {
            if (!Configuration::deleteByName($key)) {
                return false;
            }
        }

        return true;
    }

    public function callUninstallTab()
    {
        $moduleTabs = Tab::getCollectionFromModule($this->name);
        if (!empty($moduleTabs)) {
            foreach ($moduleTabs as $moduleTab) {
                $moduleTab->delete();
            }
        }

        return true;
    }

    public function reset()
    {
        if (!$this->uninstall(false)) {
            return false;
        }
        if (!$this->install(false)) {
            return false;
        }

        return true;
    }

    public function uninstall($keep = true)
    {
        if (!parent::uninstall()
            || ($keep && !$this->dropTables())
            || !$this->callUninstallTab()
            || !$this->deleteConfiguration()) {
            return false;
        }

        return true;
    }
}
