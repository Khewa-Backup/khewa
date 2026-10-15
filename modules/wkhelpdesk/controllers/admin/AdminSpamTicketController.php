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

class AdminSpamTicketController extends ModuleAdminController
{
    public function __construct()
    {
        $this->context = Context::getContext();
        $this->table = 'wk_hd_ticket';
        $this->className = 'WkHdTicket';
        $this->bootstrap = true;
        $this->identifier = 'id';
        parent::__construct();
        $this->toolbar_title = $this->l('Spam user tickets');
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
                    $allQueryType = $objQueryType->getAllQueryTypeForAgent(
                        $idEmployee,
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
                    $selectedStatus = 1;
                }
                if (!$selectedQueryType) {
                    $selectedQueryType = 0;
                }
                if (!$selectedTicketCustomer) {
                    $selectedTicketCustomer = 0;
                }

                // get all tickets of agent
                if ($agentInfo['is_super_admin']) {
                    $agentTickets = $objTicket->getSpamTicketsForSuperAdmin($idLang, $idTicket);
                } else {
                    $agentTickets = $objTicket->getSpamTicketsByIdAgent($agentInfo['id'], $idLang, $idTicket);
                }
                $allStatus = WkHdStatusMapping::getAllStatusCode();
                $ticketList = array();


                $ticketStatusCount = 0;
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
                        $ticketStatusCount++; // increment for all status counter

                        $ticket['order_ref'] = '--';
                        if ((int) $ticket['id_order'] > 0) {
                            $ticket['order_ref'] = Order::getUniqReferenceOf($ticket['id_order']);
                        }

                        $ticketList[] = $ticket;
                    }
                }
                if (Shop::getContext() == Shop::CONTEXT_ALL) {
                    $smartyVars['allShopContext'] = 1;
                }
                $smartyVars['allStatus'] = $allStatus;
                $smartyVars['ticketList'] = $ticketList;
                $smartyVars['customerList'] = $customerList;
                $smartyVars['selectedStatus'] = $selectedStatus;
                $smartyVars['ticketStatusCount'] = $ticketStatusCount;
                $smartyVars['selectedQueryType'] = $selectedQueryType;
                $smartyVars['selectedTicketCustomer'] = $selectedTicketCustomer;
                $smartyVars['adminUri'] = $this->context->link->getAdminLink('AdminSpamTicket');
                $smartyVars['adminAllticketUri'] = $this->context->link->getAdminLink('AdminAllTicket');
            } else {
                $smartyVars['isAgent'] = 0;
            }
        } else {
            $smartyVars['isAgent'] = 0;
        }

        $this->context->smarty->assign($smartyVars);
        return parent::renderList();
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->addJqueryPlugin('chosen');
        $this->addCSS(_MODULE_DIR_.'wkhelpdesk/views/css/adminallticket.css');
        //data table file included
        $this->addCSS(_MODULE_DIR_.'wkhelpdesk/views/css/datatable_bootstrap.css');
        $this->addJS(_MODULE_DIR_.'wkhelpdesk/views/js/jquery.dataTables.min.js');
        $this->addJS(_MODULE_DIR_.'wkhelpdesk/views/js/dataTables.bootstrap.js');
        $this->addJS(_MODULE_DIR_.'wkhelpdesk/views/js/wkimageremove.js');

        //for tiny mce field
        Media::addJsDef(
            array(
                'iso' => $this->context->language->iso_code,
                'mp_tinymce_path' => _MODULE_DIR_.'wkhelpdesk/libs',
                'filesizeError' => $this->l('File exceeds maximum size.'),
                'maxSizeAllowed' => Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE'),
            )
        );
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
            if ($tickets = $objTicket->getSpamTicketsForSuperAdminByIdTicket($ticketNumber)) {
                $param['status'] = 'success';
                $param['info'] = $tickets;
            }
        } else {
            if ($tickets = $objTicket->getSpamTicketsByIdTicketAndIdAgent($ticketNumber, $idAgent)) {
                $param['status'] = 'success';
                $param['info'] = $tickets;
            }
        }

        die(Tools::jsonEncode($param));
    }
}
