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

class AdminAllTicketController extends ModuleAdminController
{
    public function __construct()
    {
        $this->context = Context::getContext();
        $this->table = 'wk_hd_ticket';
        $this->className = 'WkHdTicket';
        $this->bootstrap = true;
        $this->identifier = 'id';
        parent::__construct();
        $this->toolbar_title = $this->l('All tickets');
        if (Shop::getContext() == Shop::CONTEXT_SHOP) {
            Shop::addTableAssociation('wk_hd_ticket', array('type' => 'shop', 'primary' => 'id'));
        }
        $this->_join .= WkHdGroup::addSqlAssociationCustom('wk_hd_ticket', 'a', false);
        $this->_group = ' GROUP BY a.id';

        $this->fields_list = array(
            'id' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs'
            ),
        );
    }

    public function initContent()
    {
        if (($this->display == 'edit') && (Shop::getContext() == Shop::CONTEXT_SHOP)) {
            if (!$this->loadObject(true)) {
                Tools::redirectAdmin(self::$currentIndex.'&token='.$this->token);
            }
        }
        return parent::initContent();
    }

    public function renderList()
    {
        $idLang = $this->context->language->id;
        $idEmployee = $this->context->employee->id;
        $smartyVars = array();

        if ($idEmployee) {
            $objTicketAgent = new WkHdTicketAgent();
            $agentInfo = $objTicketAgent->getAgentInfoByIdEmployee($idEmployee);
            if ($agentInfo && $agentInfo['active']) { //check is agent have access right to view tickets
                $smartyVars['isAgent'] = 1;
                $smartyVars['agentInfo'] = $agentInfo;
                $smartyVars['ajaxLoader'] = _MODULE_DIR_.'wkhelpdesk/views/img/ajax-loader.gif';

                // get all query type
                $objQueryType = new WkHdQueryType();
                if (!$objQueryType->isSuperAdmin($idEmployee)) {
                    $idAgent = $objQueryType->getTicketAgentId($idEmployee);
                    $allQueryType = $objQueryType->getAllQueryTypeForAgent(
                        $idAgent,
                        $idLang
                    );
                } else {
                    $allQueryType = $objQueryType->getAllQueryType($idLang);
                }
                if ($allQueryType) {
                    $smartyVars['allQueryType'] = $allQueryType;
                }

                $objTicket = new WkHdTicket();
                $selectedStatus = Tools::getValue('status');
                $selectedQueryType = Tools::getValue('queryType');
                $idTicket = Tools::getValue('id_ticket');
                $selectedTicketCustomer = Tools::getValue('ticket_customer');
                if (!$selectedStatus) {
                    // Customization By Ram Chandra
                    $selectedStatus = 0;
                    // $selectedStatus = 1;
                    // END
                }
                if (!$selectedQueryType) {
                    $selectedQueryType = 0;
                }
                if (!$selectedTicketCustomer) {
                    $selectedTicketCustomer = 0;
                }

                // get all tickets of agent
                if ($agentInfo['is_super_admin']) {
                    $agentTickets = $objTicket->getTicketsForSuperAdmin($idLang, $idTicket);
                } else {
                    $agentTickets = $objTicket->getTicketsByIdAgent($agentInfo['id'], $idLang, $idTicket);
                }
                // Customization By Ram Chandra
                // $allStatus = WkHdStatusMapping::getAllStatusCode();
                $statusListArr = WkHdStatusMapping::getAllStatusCode();
                $ticketStatusCount[0] = 0;
                $allStatus = [];
                $statusColorCodes = ['#25b9d7', 'mediumseagreen', 'blueviolet', '#fbbb22', '#72c279', '#e08f95'];
                // END

                $ticketList = array();
                foreach ($statusListArr as $key => $sts) {
                    // Customization By Ram Chandra
                    $sts['color_code'] = isset($statusColorCodes[$sts['id'] - 1]) ? $statusColorCodes[$sts['id'] - 1] : '';
                    $allStatus[$sts['id']] = $sts;
                    // END
                    $ticketStatusCount[$sts['id']] = 0;
                }
                // filter ticket on the basis  of status, customer and query type
                $customerList = array();
                if ($agentTickets) {
                    foreach ($agentTickets as $ticket) {
                        if (Shop::getContext() == Shop::CONTEXT_ALL) {
                            $shopObj = new Shop($ticket['id_shop']);
                            $ticket['shop_name'] = $shopObj->name;
                        }
                        // ==== do not change order of following code  ======
                        // get customer details while filter applied or not
                        // get customer email
                        $customerList[$ticket['hd_customer_id']]['email'] = $ticket['email'];
                        // get customer name
                        $customerList[$ticket['hd_customer_id']]['name'] = $ticket['customer_name'];

                        // filter ticket by customer
                        if ($selectedTicketCustomer) {
                            if ($selectedTicketCustomer != $ticket['hd_customer_id']) {
                                continue;
                            }
                        }

                        // filter ticket by query type
                        if ($selectedQueryType) {
                            if ($ticket['id_query_type'] != $selectedQueryType) {
                                continue;
                            }
                        }
                        $ticketStatusCount[$ticket['id_status']]++; // increment status counter
                        // Customization By Ram Chandra
                        $ticketStatusCount[0]++; // increment status counter
                        // END

                        // display only selected status tickets
                        // Customization By Ram Chandra
                        if ($selectedStatus && ($ticket['id_status'] != $selectedStatus)) {
                        // if ($ticket['id_status'] != $selectedStatus) {
                        // END
                            continue;
                        }
                        $ticket['order_ref'] = '--';
                        if ((int) $ticket['id_order'] > 0) {
                            $ticket['order_ref'] = Order::getUniqReferenceOf($ticket['id_order']);
                        }
                        $ticketList[] = $ticket;
                    }
                }
                $objHelpDesk = new WkHelpDesk();

                // Customization #1012187
                $objAccessRightMapping = new WkHdAccessRightMapping();
                $agentAccessRight = $objAccessRightMapping->getAccessRightByIdAgent($agentInfo['id']);
                $smartyVars['assignAccessRight'] = 0;
                $smartyVars['deleteAccessRight'] = 0;
                $smartyVars['updateAccessRight'] = 0;
                $smartyVars['removeAccessRight'] = 0;
                if ($agentAccessRight) {
                    $smartyVars['agentAccessRight'] = $agentAccessRight;
                    foreach ($agentAccessRight as $access_right) {
                        if ($access_right['access_right_text'] == 'assign') {
                            $smartyVars['assignAccessRight'] = 1;
                        } elseif ($access_right['access_right_text'] == 'delete') {
                            $smartyVars['deleteAccessRight'] = 1;
                        } elseif ($access_right['access_right_text'] == 'update') {
                            $smartyVars['updateAccessRight'] = 1;
                        } elseif ($access_right['access_right_text'] == 'remove') {
                            $smartyVars['removeAccessRight'] = 1;
                        }
                    }
                    if ($smartyVars['updateAccessRight']) {
                        $smartyVars['statusList'] = WkHdStatusMapping::getAllStatusCode();
                    }
                }
                // Customization end #1012187
                if (Shop::getContext() == Shop::CONTEXT_ALL) {
                    $smartyVars['allShopContext'] = 1;
                }
                $smartyVars['objHelpDesk'] = $objHelpDesk;
                $smartyVars['allStatus'] = $allStatus;
                $smartyVars['ticketList'] = $ticketList;
                $smartyVars['customerList'] = $customerList;
                $smartyVars['selectedStatus'] = $selectedStatus;
                $smartyVars['ticketStatusCount'] = $ticketStatusCount;
                $smartyVars['selectedQueryType'] = $selectedQueryType;
                $smartyVars['selectedTicketCustomer'] = $selectedTicketCustomer;
                $smartyVars['adminUri'] = $this->context->link->getAdminLink('AdminAllTicket');
                // Customization By Ravindra Gautam 
                //get agent access rights
                $objAccessRightMapping = new WkHdAccessRightMapping();
                $agentAccessRight = $objAccessRightMapping->getAccessRightByIdAgent($agentInfo['id']);
                $smartyVars['updateAccessRight'] = 0;
                if ($agentAccessRight) {
                    foreach ($agentAccessRight as $access_right) {
                        if ($access_right['access_right_text'] == 'update') {
                            $smartyVars['updateAccessRight'] = 1;
                        }
                    }

                    if ($smartyVars['updateAccessRight']) {
                        $smartyVars['statusList'] = WkHdStatusMapping::getAllStatusCode();
                    }
                }
                $smartyVars['agentInfo'] = $agentInfo;
                // END
            } else {
                $smartyVars['isAgent'] = 0;
            }
        } else {
            $smartyVars['isAgent'] = 0;
        }
        $smartyVars['openedStatus'] = Tools::getValue('status') ? Tools::getValue('status') : 0;
        $this->context->smarty->assign($smartyVars);
        return parent::renderList();
    }

    public function initToolbar()
    {
        $idEmployee = $this->context->employee->id;
        if ($idEmployee) {
            $objTicketAgent = new WkHdTicketAgent();
            $agentInfo = $objTicketAgent->getAgentInfoByIdEmployee($idEmployee);
            if ($agentInfo && $agentInfo['active']) { //check is active agent have access right to create new ticket
                $objMapping = new WkHdAccessRightMapping();
                if ($objMapping->checkTicketCreateAccessRightByIdAgent($agentInfo['id']) || $agentInfo['is_super_admin']) {
                    parent::initToolbar();
                    $this->page_header_toolbar_btn['new'] = array(
                        'href' => self::$currentIndex.'&add'.$this->table.'&token='.$this->token,
                        'desc' => $this->l('Create new ticket'),
                    );
                }
            }
        }
    }

    public function renderForm()
    {
        if (($this->display == 'edit') && (Shop::getContext() != Shop::CONTEXT_SHOP)) {
            return $this->context->smarty->fetch(
                _PS_MODULE_DIR_.$this->module->name.'/views/templates/admin/_partials/shop_warning.tpl'
            );
        } else {
            $idEmployee = $this->context->employee->id;
            $idLang = $this->context->language->id;
            $objHelpDesk = new WkHelpDesk();
            $objQueryType = new WkHdQueryType();

            if (!$objQueryType->isSuperAdmin($idEmployee)) {
                $allQueryType = $objQueryType->getAllQueryTypeForAgent(
                    $idEmployee,
                    $idLang
                );
            } else {
                $allQueryType = $objQueryType->getAllQueryType($idLang);
            }


            $smartyVars = array(
                'objHelpDesk' => $objHelpDesk,
                'ajax_loader' => _MODULE_DIR_.'wkhelpdesk/views/img/ajax-loader.gif',
                'attachmentMaxSize' => Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE'),
                'fileExtensions' => implode(', ', WkHdTicket::getSelectedFileExtension()),
                'tinymceJsLink' => _MODULE_DIR_.'wkhelpdesk/views/js/tinymce/tinymce.min.js',
                'allQueryType' => $allQueryType,
                'tinymceJsSetupLink' => _MODULE_DIR_.'wkhelpdesk/views/js/tinymce/tinymce_wk_setup.js',
            );

            $smartyVars['add'] = 1;
            if ($this->display == 'edit') {//if edit form
                $smartyVars['add'] = 0;
                $idTicket = Tools::getValue('id');
                $smartyVars['ticketViewAccess'] = 0;
                $idEmployee = $this->context->employee->id;

                $objTicketAgent = new WkHdTicketAgent();
                $objTicketAttachment = new WkHdTicketAttachment();
                $smartyVars['objTicketAgent'] = $objTicketAgent;
                $smartyVars['objTicketAttachment'] = $objTicketAttachment;
                $agentInfo = $objTicketAgent->getAgentInfoByIdEmployee($idEmployee);

                if ($agentInfo && $agentInfo['active']) { //check is agent have access right to edit ticket
                    $objTicket = new WkHdTicket();

                    // if agent is super admin
                    if ($agentInfo['is_super_admin']) {
                        $smartyVars['ticketViewAccess'] = 1;
                        $ticketDetails = $objTicket->getTicketDetailsByIdAndIdLang(
                            $idTicket,
                            $this->context->language->id
                        );
                        $smartyVars['ticketDetails'] = $ticketDetails;
                    } elseif ($objTicket->checkAgentTicketAccessRight($agentInfo['id'], $idTicket)) {
                        $ticketDetails = $objTicket->getTicketDetailsByIdAndIdLang(
                            $idTicket,
                            $this->context->language->id
                        );
                        $smartyVars['ticketDetails'] = $ticketDetails;
                        $smartyVars['ticketViewAccess'] = 1;
                    } else {
                        $this->errors[] = $this->l('You do not have access right to view this page.');
                    }

                    // get ticket conversation
                    if (array_key_exists('ticketDetails', $smartyVars)) {
                        $ticketConversation = $objTicket->getTicketConversationByIdTicket($idTicket);
                        foreach ($ticketConversation as $key => $conversation) {
                            $employObj = new Employee();
                            if ($conversation['email']) {
                                $employee = $employObj->getByEmail($conversation['email']);
                                $profile = new Profile($employee->id_profile);
                                $ticketConversation[$key]['profile'] = $profile->name[$this->context->language->id];
                            }
                        }
                        // $msgTemp = $ticketConversation[4]['message'];
                        // dump($msgTemp);
                        // dump(Tools::getDescriptionClean($msgTemp));
                        // die;
                        if ($ticketConversation) {
                            $smartyVars['ticketConversation'] = $ticketConversation;
                        }
                    }

                    //get agent access rights
                    $objAccessRightMapping = new WkHdAccessRightMapping();
                    $agentAccessRight = $objAccessRightMapping->getAccessRightByIdAgent($agentInfo['id']);
                    $smartyVars['assignAccessRight'] = 0;
                    $smartyVars['deleteAccessRight'] = 0;
                    $smartyVars['updateAccessRight'] = 0;
                    $smartyVars['removeAccessRight'] = 0;
                    if ($agentAccessRight) {
                        $smartyVars['agentAccessRight'] = $agentAccessRight;
                        foreach ($agentAccessRight as $access_right) {
                            if ($access_right['access_right_text'] == 'assign') {
                                $smartyVars['assignAccessRight'] = 1;
                            } elseif ($access_right['access_right_text'] == 'delete') {
                                $smartyVars['deleteAccessRight'] = 1;
                            } elseif ($access_right['access_right_text'] == 'update') {
                                $smartyVars['updateAccessRight'] = 1;
                            } elseif ($access_right['access_right_text'] == 'remove') {
                                $smartyVars['removeAccessRight'] = 1;
                            }
                        }

                        if ($smartyVars['assignAccessRight']) {
                            $smartyVars['agentList'] = $objTicketAgent->getAgentListExceptId($agentInfo['id']);
                            if (!$smartyVars['agentList']) {
                                $smartyVars['assignAccessRight'] = 0;
                            }
                        }

                        if ($smartyVars['updateAccessRight']) {
                            $smartyVars['statusList'] = WkHdStatusMapping::getAllStatusCode();
                        }
                    }
                    $smartyVars['agentInfo'] = $agentInfo;
                } else {
                    $this->errors[] = $this->l('You do not have access right to view this page.');
                }
            }
            $this->context->smarty->assign($smartyVars);
            $this->fields_form = array(
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            );

            return parent::renderForm();
        }
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->addJqueryPlugin('chosen');
        $this->addCSS(_MODULE_DIR_.'wkhelpdesk/views/css/adminallticket.css');
        //data table file included
        $this->addCSS(_MODULE_DIR_.'wkhelpdesk/views/css/datatable_bootstrap.css');
        $this->addJS(_MODULE_DIR_.'wkhelpdesk/views/js/jquery.dataTables.min.js');
        $this->addJS(_MODULE_DIR_.'wkhelpdesk/views/js/dataTables.bootstrap.js?v='.time());
        $this->addJS(_MODULE_DIR_.'wkhelpdesk/views/js/wkimageremove.js?v='.time());
        $this->addJS('growl', null, false);
        $this->addCSS('growl-css', 'js/jquery/plugins/growl/jquery.growl.css');
        //for tiny mce field
        Media::addJsDef(
            array(
                'iso' => $this->context->language->iso_code,
                'mp_tinymce_path' => _MODULE_DIR_.'wkhelpdesk/libs',
                'filesizeError' => $this->l('File exceeds maximum size.'),
                'maxSizeAllowed' => Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE'),
                'err' => $this->l('Error:'),
                // Customization #1012187
                'atleastoneprd' => $this->l('Select at least one ticket.'),
                'wk_admin_url' => $this->context->link->getAdminLink('AdminAllTicket')
                // Customization end #1012187
            )
        );
    }

    public function processSave()
    {
        $idTicket = Tools::getValue('id'); //if edit
        $message = Tools::getValue('message');

        $ticketAttachment = false;
        $ticketOtherAttachment = false;
        if (isset($_FILES['ticketAttachment'])) { // check ticket main attachment
            $ticketAttachment = $_FILES['ticketAttachment'];
        }
        if (isset($_FILES['ticketOtherAttachment'])) { // check ticket other attachments
            $ticketOtherAttachment = $_FILES['ticketOtherAttachment'];
        }

        if ($idTicket) { // if ticket reply
            $replyType = Tools::getValue('replyType');

            $objTicket = new WkHdTicket();
            $ticketInfo = $objTicket->getInfoById($idTicket);

            // check is ticket assigned to agent
            if ($replyType == 'forward') {
                $idAgent = Tools::getValue('assignedAgent');

                // ticket already assigned to selected agent
                if ($ticketInfo['assigned_agent_id'] == $idAgent) {
                    $this->errors[] = $this->l('This ticket already assigned to selected agent.');
                }
                // check is ticket reply or add internal note then message is required field.
            // Customization #1012187
            } elseif ($replyType == 'internal' || $replyType == 'internal_close'  || $replyType == 'reply') {
            // Customization end
                if ($message == '') {
                    $this->errors[] = $this->l('Please enter message.');
                }
            }
        } else {
            $email = Tools::getValue('email');
            $subject = Tools::getValue('subject');
            $lastname = Tools::getValue('lastname');
            $firstname = Tools::getValue('firstname');
            $queryType = Tools::getValue('queryType');
            $reference = Tools::getValue('reference');

            //Validate data
            if ($firstname != '') {
                if (!Validate::isName($firstname)) {
                    $this->errors[] = $this->l('First name is not valid.');
                }
            } else {
                $this->errors[] = $this->l('First name is required field.');
            }

            if ($lastname != '') {
                if (!Validate::isName($lastname)) {
                    $this->errors[] = $this->l('Last name is not valid.');
                }
            } else {
                $this->errors[] = $this->l('Last name is required field.');
            }

            if ($email != '') {
                if (!Validate::isEmail($email)) {
                    $this->errors[] = $this->l('Email is not valid.');
                }
            } else {
                $this->errors[] = $this->l('Email is required field.');
            }

            if ($queryType == 0) {
                $this->errors[] = $this->l('Select your query type.');
            }

            if ($subject != '') {
                if (!Validate::isMailSubject($subject)) {
                    $this->errors[] = $this->l('Subject is not valid.');
                } elseif (Tools::strlen($subject) > 255) {
                    $this->errors[] = $this->l('Subject is greater then 255 characters.');
                }
            } else {
                $this->errors[] = $this->l('Subject is required field.');
            }

            if ($reference!= '') {
                if (!Validate::isGenericName($reference)) {
                    $this->errors[] = $this->module->l('Reference number is not valid.', 'createticket');
                }
                $orderObj = Order::getByReferenceAndEmail($reference, $email);
                if ($orderObj->id_customer == null) {
                    $this->errors[] = $this->module->l('Reference number is not valid with customer email.', 'createticket');
                }
            }
            if ($message == '') {
                $this->errors[] = $this->l('Please enter message.');
            }
        }

        if ($message) {
            if (!Validate::isCleanHtml($message)) {
                $this->errors[] = $this->l('Message is not valid.');
            }
        }

        //validate ticket main attachment
        if ($ticketAttachment && !empty($ticketAttachment['name'])) {
            if (!WkHdTicket::validateTicketMainAttachment($ticketAttachment)) {
                $this->errors[] = $this->l('Main attachment file is not valid.');
            }
        }

        //validate ticket other attachment
        if ($ticketOtherAttachment && !empty($ticketOtherAttachment['name'])) {
            if (!WkHdTicket::validateTicketOtherAttachment($ticketOtherAttachment)) {
                $this->errors[] = $this->l('Other attachment file(s) are not valid.');
            }
        }


        if (empty($this->errors)) {
            $objTicketAgent = new WkHdTicketAgent();
            if ($idTicket) { //if ticket reply
                $objTicket = new WkHdTicket((int) $idTicket);
                // add data in ticket msg table
                $objTicketMsg = new WkHdTicketMsg();
                $objTicketMsg->hd_id_ticket = (int) $idTicket;
                $objTicketMsg->message = pSQL(trim(preg_replace('/\s+/', ' ', $message)), true);
                $objTicketMsg->id_customer = (int) 0;
                $objTicketMsg->id_agent = (int) Tools::getValue('idAgent');
                $objTicketMsg->is_status_update = (int) 0;
                $objTicketMsg->status_from = (int) 0;
                $objTicketMsg->status_to = (int) 0;

                // Customization #1012187
                if ($replyType == 'internal' || $replyType == 'internal_close') { // check if internal note
                    // Customization end #1012187
                    $objTicketMsg->is_internal_note = (int) 1;
                } else {
                    $objTicketMsg->is_internal_note = (int) 0;
                }

                if ($replyType == 'forward') { // check is ticket assigned to other agent
                    $objTicketMsg->is_agent_assign = (int) 1;
                    $objTicketMsg->agent_from = (int) $objTicket->assigned_agent_id;
                    $objTicketMsg->agent_to = (int) Tools::getValue('assignedAgent');
                } else {
                    $objTicketMsg->is_agent_assign = (int) 0;
                    $objTicketMsg->agent_from = (int) 0;
                    $objTicketMsg->agent_to = (int) 0;
                }

                $objTicketMsg->save();
                $idMsg = $objTicketMsg->id;

                if ($idMsg) {
                    // if ticket assign to any agent then mail to that agent
                    if ($replyType == 'forward') {
                        $idAgent = Tools::getValue('assignedAgent');
                        $objTicket->assigned_agent_id = (int) $idAgent;

                        $agentInfo = $objTicketAgent->getAgentInfoById($idAgent);
                        $templateVars = array(
                            '{name}' => $agentInfo['name'],
                            '{email}' => $agentInfo['email'],
                            '{ticket_id}' => $objTicket->id,
                            '{message}' => $message
                        );
                        $agentObj = new Employee((int) $agentInfo['employee_id']);
                        $objTicketAgent->assignMailToAgent($templateVars, (int) $agentObj->id_lang);
                    // if ticket reply then mail to customer
                    // Customization #1012187
                    } elseif ($replyType == 'reply' || $replyType == 'internal_close') {
                    // Customization end #1012187
                        $hdIdCustomer = $objTicket->hd_id_customer;
                        // get mapped status of answer status
                        $objStatusMapping = new WkHdStatusMapping();
                        // Customization #1012187
                        if ($replyType == 'internal_close') {
                            $objTicket->id_status = (int) 2;
                        } else {
                        // Customization end #1012187
                            $objTicket->id_status = (int) $objStatusMapping->getMappedStatusIdByStatus('Answered');
                        // Customization #1012187
                        }
                        // Customization end #1012187

                        $objHdCustomer = new WkHdCustomer((int) $hdIdCustomer);
                        if ($objHdCustomer->email) {
                            $protocol_link = (Configuration::get('PS_SSL_ENABLED') || Tools::usingSecureMode()) ?
                            'https://' : 'http://';
                            $ticketLink = $protocol_link.Tools::getShopDomainSsl().__PS_BASE_URI__.
                            'index.php?fc=module&module=wkhelpdesk&controller=viewticket&id='.$objTicket->id;

                            // if cutomer is not sign up in prestashop
                            if (!$objHdCustomer->id_ps_customer) {
                                $objTicketToken = new WkHdTicketToken();
                                if ($token = $objTicketToken->getTokenByIdTicket($objTicket->id)) {
                                    $ticketLink .= '&token='.$token;
                                }
                            }

                            $templateVars = array(
                                '{first_name}' => $objHdCustomer->first_name,
                                '{last_name}' => $objHdCustomer->last_name,
                                '{email}' => $objHdCustomer->email,
                                '{ticket_id}' => $objTicket->id,
                                '{message}' => $message,
                                '{ticket_link}' => $ticketLink,
                                '{id_lang}' => $this->context->language->id
                            );
                            $objHdCustomer->replyMailToCustomer($templateVars);
                        }
                    }
                    $objTicket->save();
                    $wkIdTicket = $objTicket->id;

                    // upload ticket attachment
                    if ($ticketAttachment) {
                        WkHdTicketAttachment::uploadTicketAttachment($ticketAttachment, $idMsg);
                    }

                    // upload ticket other attachment
                    if ($ticketOtherAttachment) {
                        WkHdTicketAttachment::uploadTicketOtherAttachment($ticketOtherAttachment, $idMsg);
                    }
                } else {
                    $this->errors[] = $this->l('There is some technical error. Please try again later.');
                }
            } else { // add new ticket
                $firstname = Tools::getValue('firstname');
                $lastname = Tools::getValue('lastname');
                $email = Tools::getValue('email');
                $queryType = Tools::getValue('queryType');
                $subject = Tools::getValue('subject');

                // check customer by email id
                $idCustomer = 0;
                $objHdCustomer = new WkHdCustomer();
                $hdCustomer = $objHdCustomer->getCustomerByEmail(pSQL($email));
                // if customer available of this email id
                if ($hdCustomer) {
                    $hdIdCustomer = $hdCustomer['id'];
                    $idCustomer = $hdCustomer['id_ps_customer'];
                // else create new help desk customer
                } else {
                    $objCustomer = new Customer();
                    $customerInfo = $objCustomer->getByEmail($email);
                    if ($customerInfo) {
                        $idCustomer = $customerInfo->id;
                    }
                    $objHdCustomer->id_ps_customer = (int) $idCustomer;
                    $objHdCustomer->first_name = pSQL($firstname);
                    $objHdCustomer->last_name = pSQL($lastname);
                    $objHdCustomer->email = pSQL($email);
                    $objHdCustomer->save();
                    $hdIdCustomer = $objHdCustomer->id;
                }

                // create new ticket
                $objTicket = new WkHdTicket();
                $objTicket->first_name = pSQL($firstname);
                $objTicket->last_name = pSQL($lastname);
                $objTicket->hd_id_customer = (int) $hdIdCustomer;
                $objTicket->id_query_type = (int) $queryType;
                $objTicket->assigned_agent_id = (int) 0;
                $objStatusMapping = new WkHdStatusMapping();
                $data = $objStatusMapping->getAllStatusCode();

                //open status id
                $objTicket->id_status = (int) $data[0]['id'];
                $objTicket->subject = pSQL($subject);
                $objTicket->id_order = (int) WkHdTicket::getOrder(trim(Tools::getValue('reference')));
                $objTicket->save();
                $wkIdTicket = $objTicket->id;

                if ($wkIdTicket) {
                    // save ticket message
                    $objTicketMsg = new WkHdTicketMsg();
                    $objTicketMsg->hd_id_ticket = (int) $wkIdTicket;
                    $objTicketMsg->message = pSQL(trim(preg_replace('/\s+/', ' ', $message)), true);
                    $objTicketMsg->id_customer = (int) $hdIdCustomer;
                    $objTicketMsg->id_agent = (int) 0;
                    $objTicketMsg->is_internal_note = (int) 0;
                    $objTicketMsg->is_status_update = (int) 0;
                    $objTicketMsg->status_from = (int) 0;
                    $objTicketMsg->status_to = (int) 0;
                    $objTicketMsg->is_agent_assign = (int) 0;
                    $objTicketMsg->agent_from = (int) 0;
                    $objTicketMsg->agent_to = (int) 0;
                    $objTicketMsg->save();
                    $idMsg = $objTicketMsg->id;

                    // upload ticket main attachment
                    if ($ticketAttachment && $idMsg) {
                        WkHdTicketAttachment::uploadTicketAttachment($ticketAttachment, $idMsg);
                    }

                    // upload ticket other attachment
                    if ($ticketOtherAttachment && $idMsg) {
                        WkHdTicketAttachment::uploadTicketOtherAttachment($ticketOtherAttachment, $idMsg);
                    }

                    // create token if customer is not registered with entered email id
                    $protocol_link = (Configuration::get('PS_SSL_ENABLED') || Tools::usingSecureMode()) ?
                    'https://' : 'http://';
                    $ticketLink = $protocol_link.Tools::getShopDomainSsl().__PS_BASE_URI__.
                    'index.php?fc=module&module=wkhelpdesk&controller=viewticket&id='.$wkIdTicket;

                    if (!$idCustomer) {
                        $token = WkHdTicket::getToken();
                        $obj_ticket_token = new WkHdTicketToken();
                        $obj_ticket_token->hd_id_ticket = (int) $wkIdTicket;
                        $obj_ticket_token->token = pSQL($token);
                        $obj_ticket_token->save();
                        $ticketLink .= '&token='.$token;
                    }

                    // prepare mail template vars
                    $ticket_param = array(
                        '{ticket_link}' => $ticketLink,
                        '{customer_name}' => $firstname.' '.$lastname,
                        '{subject}' => $subject,
                        '{message}' => $message,
                        '{email}' => $email,
                        '{id_lang}' => $this->context->language->id,
                        '{id_query_type}' => $queryType,
                        '{ticket_id}' => $wkIdTicket
                    );

                    //confirmation mail to customer
                    if (Configuration::get('WK_HD_NEW_TICKET_CUSTOMER_NOTIFICATON')) {
                        $objTicketAgent->createTicketMailToCustomer($ticket_param);
                    }

                    // mail to agents
                    if (Configuration::get('WK_HD_NEW_TICKET_AGENT_NOTIFICATON')) {
                        $objTicketAgent->customerReplyToAgent($ticket_param);
                    }
                }
            }

            $viewAccessRight = false;
            if ($idTicket) {
                $idAgent = Tools::getValue('idAgent');
                $agentInfo = $objTicketAgent->getAgentInfoById($idAgent);
            } else {
                $idEmployee = $this->context->employee->id;
                $objTicketAgent = new WkHdTicketAgent();
                $agentInfo = $objTicketAgent->getAgentInfoByIdEmployee($idEmployee);
            }
            if ($agentInfo) {
                if ($agentInfo['is_super_admin']) {
                    $viewAccessRight = true;
                } else {
                    // on click create and stay first check if view access of this ticket else redirect to list
                    $viewAccessRight = $objTicket->checkAgentTicketAccessRight(Tools::getValue('idAgent'), $wkIdTicket);
                }
            }
            if (Tools::isSubmit('submitAdd'.$this->table.'AndStay') && $viewAccessRight) {
                if ($idTicket) {
                    Tools::redirectAdmin(
                        self::$currentIndex.'&id='.(int) $wkIdTicket.'&update'.$this->table.
                        '&conf=4&token='.$this->token
                    );
                } else {
                    Tools::redirectAdmin(
                        self::$currentIndex.'&id='.(int) $wkIdTicket.'&update'.$this->table.
                        '&conf=3&token='.$this->token
                    );
                }
            } else {
                if ($idTicket) {
                    Tools::redirectAdmin(self::$currentIndex.'&conf=4&token='.$this->token);
                } else {
                    Tools::redirectAdmin(self::$currentIndex.'&conf=3&token='.$this->token);
                }
            }
        } else {
            if ($idTicket) {
                $this->display = 'edit';
            } else {
                $this->display = 'add';
            }
        }
    }

    // remove agent from ticket
    public function ajaxProcessRemoveAgent()
    {
        $param = array('status' => 'fail');
        $idAgent = Tools::getValue('idAgent');
        $idTicket = Tools::getValue('idTicket');

        $objTicket = new WkHdTicket((int) $idTicket);
        $prev_agent_id = $objTicket->assigned_agent_id;
        $objTicket->assigned_agent_id = (int) 0;
        $objTicket->save();

        if ($idAgent && $idTicket && $prev_agent_id) {
            $objTicketMsg = new WkHdTicketMsg();
            $objTicketMsg->hd_id_ticket = (int) $idTicket;
            $objTicketMsg->message = '';
            $objTicketMsg->id_customer = (int) 0;
            $objTicketMsg->id_agent = (int) $idAgent;
            $objTicketMsg->is_internal_note = (int) 0;
            $objTicketMsg->is_status_update = (int) 0;
            $objTicketMsg->status_from = (int) 0;
            $objTicketMsg->status_to = (int) 0;
            $objTicketMsg->is_agent_assign = (int) 2;
            $objTicketMsg->agent_from = (int) $prev_agent_id;
            $objTicketMsg->agent_to = (int) 0;
            $objTicketMsg->save();
            if ($objTicketMsg->id) {
                $param['status'] = 'success';
            }
        }

        die(Tools::jsonEncode($param));
    }

    // search ticket by ticket id
    public function ajaxProcessSearchTicket()
    {
        $param = array('status' => 'fail');
        $idAgent = Tools::getValue('idAgent');
        $ticketNumber = Tools::getValue('ticketNumber');
        $isSuperAdmin = Tools::getValue('isSuperAdmin');

        // if super admin then serach ticket without any restriction
        $objTicket = new WkHdTicket();
        if ($isSuperAdmin) {
            if ($tickets = $objTicket->getTicketsForSuperAdminByIdTicket($ticketNumber)) {
                $param['status'] = 'success';
                $param['info'] = $tickets;
            }
        } else {
            if ($tickets = $objTicket->getTicketsByIdTicketAndIdAgent($ticketNumber, $idAgent)) {
                $param['status'] = 'success';
                $param['info'] = $tickets;
            }
        }

        die(Tools::jsonEncode($param));
    }

    // Customization start #1012187
    // change selected ticket status
    public function ajaxProcessChangeSelectedTicketStatus()
    {
        $status = Tools::getValue('status');
        $idAgent = Tools::getValue('idAgent');
        $idTickets = Tools::getValue('idTicket');
        $param = [
            'status' => 'fail',
            'msg' => $this->l('This is some technical error.'),
        ];

        if (!empty($idTickets) && $status && $idAgent) {
            foreach ($idTickets as $idTicket) {
                $objTicket = new WkHdTicket((int) $idTicket);
                $objStatusMapping = new WkHdStatusMapping();
                $idStatus = $objStatusMapping->getMappedStatusIdByStatus($status);
                if ($idStatus) {
                    if ($objTicket->id_status == $idStatus) {
                        $objHelpDesk = new WkHelpDesk();
                        $param['msg'] = $this->l('This ticket already in ') . $objHelpDesk->getStatusTextById($idStatus) .
                        $this->l(' status');
                    } else {
                        $objTicketMsg = new WkHdTicketMsg();
                        $objTicketMsg->hd_id_ticket = (int) $idTicket;
                        $objTicketMsg->message = '';
                        $objTicketMsg->id_customer = (int) 0;
                        $objTicketMsg->id_agent = (int) $idAgent;
                        $objTicketMsg->is_internal_note = (int) 0;
                        $objTicketMsg->is_status_update = (int) 1;
                        $objTicketMsg->status_from = (int) $objTicket->id_status;
                        $objTicketMsg->status_to = (int) $idStatus;
                        $objTicketMsg->is_agent_assign = (int) 0;
                        $objTicketMsg->agent_from = (int) 0;
                        $objTicketMsg->agent_to = (int) 0;
                        $objTicketMsg->save();
        
                        $objTicket->id_status = (int) $idStatus;
                        $objTicket->save();
                        $param['status'] = 'success';
                        $param['msg'] = $this->l('Status change successfully');
                        // $ticketStatus = WkHdStatusMapping::getStatusById($idStatus);
        
                        // mail to customer when ticket status will changed to resolved or closed.
                        if ($idStatus == 2 || $idStatus == 5) {
                            if ($idStatus == 5) {
                                $status = $this->l('Resolved');
                            } else {
                                $status = $this->l('Closed');
                            }
        
                            if ($objTicket->hd_id_customer && Configuration::get('WK_HD_STATUS_UPDATE_MAIL')) {
                                $objHdCustomer = new WkHdCustomer((int) $objTicket->hd_id_customer);
                                $mailParams = [
                                    '{name}' => $objHdCustomer->first_name . ' ' . $objHdCustomer->last_name,
                                    '{email}' => $objHdCustomer->email,
                                    '{ticket_id}' => $idTicket,
                                    '{status}' => $status,
                                    '{id_lang}' => $this->context->language->id,
                                ];
                                $objHdCustomer->mailToCustomerForStatusUpdate($mailParams);
                            }
                        }
                    }
                } else {
                    $param['msg'] = $this->l('Status not found.');
                }
            }
        }

        exit(json_encode($param));
    }
    // Customization end #1012187

    // change ticket status
    public function ajaxProcessChangeTicketStatus()
    {
        $status = Tools::getValue('status');
        $idAgent = Tools::getValue('idAgent');
        $idTicket = Tools::getValue('idTicket');
        $param = array(
            'status' => 'fail',
            'msg' => $this->l('This is some technical error.')
        );

        if ($idTicket && $status && $idAgent) {
            $objTicket = new WkHdTicket((int) $idTicket);
            $objStatusMapping = new WkHdStatusMapping();
            $idStatus = $objStatusMapping->getMappedStatusIdByStatus($status);
            if ($idStatus) {
                if ($objTicket->id_status == $idStatus) {
                    $objHelpDesk = new WkHelpDesk();
                    $param['msg'] = $this->l('This ticket already in ').$objHelpDesk->getStatusTextById($idStatus).
                    $this->l(' status');
                } else {
                    $objTicketMsg = new WkHdTicketMsg();
                    $objTicketMsg->hd_id_ticket = (int) $idTicket;
                    $objTicketMsg->message = '';
                    $objTicketMsg->id_customer = (int) 0;
                    $objTicketMsg->id_agent = (int) $idAgent;
                    $objTicketMsg->is_internal_note = (int) 0;
                    $objTicketMsg->is_status_update = (int) 1;
                    $objTicketMsg->status_from = (int) $objTicket->id_status;
                    $objTicketMsg->status_to = (int) $idStatus;
                    $objTicketMsg->is_agent_assign = (int) 0;
                    $objTicketMsg->agent_from = (int) 0;
                    $objTicketMsg->agent_to = (int) 0;
                    $objTicketMsg->save();

                    $objTicket->id_status = (int) $idStatus;
                    $objTicket->save();
                    $param['status'] = 'success';
                    // Customization By Ravindra Gautam
                    $objHelpDesk = new WkHelpDesk();
                    $param['msg'] = sprintf(
                        $this->l('This ticket status updated to %s status.'),
                        $objHelpDesk->getStatusTextById($idStatus)
                    );
                    // End
                    //$ticketStatus = WkHdStatusMapping::getStatusById($idStatus);

                    // mail to customer when ticket status will changed to resolved or closed.
                    if ($idStatus == 2 || $idStatus == 5) {
                        if ($idStatus == 5) {
                            $status = $this->l('Resolved');
                        } else {
                            $status = $this->l('Closed');
                        }

                        if ($objTicket->hd_id_customer && Configuration::get('WK_HD_STATUS_UPDATE_MAIL')) {
                            $objHdCustomer = new WkHdCustomer((int) $objTicket->hd_id_customer);
                            $mailParams = array(
                                '{name}' => $objHdCustomer->first_name.' '.$objHdCustomer->last_name,
                                '{email}' => $objHdCustomer->email,
                                '{ticket_id}' => $idTicket,
                                '{status}' => $status,
                                '{id_lang}' => $this->context->language->id,
                            );
                            $objHdCustomer->mailToCustomerForStatusUpdate($mailParams);
                        }
                    }
                }
            } else {
                $param['msg'] = $this->l('Status not found.');
            }
        }

        die(Tools::jsonEncode($param));
    }

    // change ticket status
    public function ajaxProcessMarkCustomerAsSpam()
    {
        $status = Tools::getValue('status');
        $idAgent = Tools::getValue('idAgent');
        $idTicket = Tools::getValue('idTicket');
        $param = array(
            'status' => 'fail',
            'msg' => $this->l('This is some technical error.')
        );

        if ($idTicket && $idAgent) {
            $objTicket = new WkHdTicket((int) $idTicket);
            $objCustomer = new WkHdCustomer((int) $objTicket->hd_id_customer);
            $objCustomer->is_spam = (int) $status;
            $objCustomer->save();
            if ($objCustomer->id) {
                if ($status) {
                    $param['msg'] = $this->l('Ticket marked as not spam.');
                } else {
                    $param['msg'] = $this->l('Ticket marked as spam.');
                }
                $param['status'] = 'success';
            }
        }

        die(Tools::jsonEncode($param));
    }
}
