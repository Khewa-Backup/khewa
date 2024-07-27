<?php

class WkHdTicketManager
{
    private $context;
    private $module;

    public function __construct($module, $context = false)
    {
        $this->module = $module;
        $this->context = $context !== false ? $context : Context::getContext();
    }

    public function handleTicketData()
    {
        $extension = ['.txt', '.rtf', '.doc', '.docx', '.pdf', '.zip', '.png', '.jpeg', '.gif', '.jpg'];
        $file_attachment = Tools::fileAttachment('fileUpload');
        $message = trim(Tools::getValue('message'));
        $url = Tools::getValue('url');
        $clientToken = Tools::getValue('token');
        $serverToken = $this->context->cookie->contactFormToken;
        $clientTokenTTL = $this->context->cookie->contactFormTokenTTL;
        $customer = $this->context->customer;
        $valid = true;
        if (!($from = trim(Tools::getValue('from'))) || !Validate::isEmail($from)) {
            $valid = false;
        } elseif (empty($message)) {
            $valid = false;
        } elseif (!Validate::isCleanHtml($message)) {
            $valid = false;
        } elseif (!($id_contact = (int) Tools::getValue('id_contact'))
            || !Validate::isLoadedObject($contact = new Contact($id_contact, $this->context->language->id))
        ) {
            $valid = false;
        } elseif (!empty($file_attachment['name']) && $file_attachment['error'] != 0) {
            $valid = false;
        } elseif (!empty($file_attachment['name'])
            && !in_array(Tools::strtolower(Tools::substr($file_attachment['name'], -4)), $extension)
            && !in_array(Tools::strtolower(Tools::substr($file_attachment['name'], -5)), $extension)
        ) {
            $valid = false;
        } elseif ($url !== ''
            || empty($serverToken)
            || $clientToken !== $serverToken
            || $clientTokenTTL < time()
        ) {
            $valid = false;
        } elseif (!$customer->id) {
            $customer->getByEmail($from);
            if (!Validate::isLoadedObject($customer)) {
                $customer->email = $from;
                $customer->id = 0;
                $customer->firstname = 'Guest';
                $customer->lastname = '';
            }
        }
        if ($valid) {
            /**
             * Check that the order belongs to the customer.
             */
            $id_order = (int) Tools::getValue('id_order', 0);
            if ($id_order > 0) {
                $order = new Order($id_order);
                $id_order = (int) $order->id_customer === (int) $customer->id ? $id_order : 0;
            } else {
                $id_order = 0;
            }
            $this->createTicketForCustomer($customer, $message, $id_order, $file_attachment);
        }
    }

    public function createTicketForCustomer($customer, $message, $idOrder, $ticketAttachment)
    {
        $idCustomer = 0;
        $objHdCustomer = new WkHdCustomer();
        $hdCustomer = $objHdCustomer->getCustomerByEmail($customer->email);
        if ($hdCustomer) {
            $hdIdCustomer = $hdCustomer['id'];
            $idCustomer = $hdCustomer['id_ps_customer'];
            $isCustomer = $idCustomer;
        } else {
            $idCustomer = $customer->id;
            $objHdCustomer->id_ps_customer = (int) $customer->id;
            $isCustomer = $customer->id;

            $objHdCustomer->first_name = pSQL($customer->firstname);
            $objHdCustomer->last_name = pSQL($customer->lastname);
            $objHdCustomer->email = pSQL($customer->email);
            $objHdCustomer->save();
            $hdIdCustomer = $objHdCustomer->id;
        }
        $idQueryType = Db::getInstance()->getValue('SELECT `id` FROM `'._DB_PREFIX_.'wk_hd_query_type` ORDER BY `active` DESC');
        $objTicket = new WkHdTicket();
        $objTicket->hd_id_customer = (int) $hdIdCustomer;
        $objTicket->first_name = pSQL($customer->firstname);
        $objTicket->last_name = pSQL($customer->lastname);
        $objTicket->id_query_type = (int) $idQueryType;
        $objTicket->assigned_agent_id = (int) 0;
        $objStatusMapping = new WkHdStatusMapping();

        $context = Context::getContext();
        if(isset($context->employee) && Validate::isLoadedObject($context->employee) && in_array($context->employee->id_profile, [1,6,7])){
            $objTicket->id_status = (int) $objStatusMapping->getMappedStatusIdByStatus('closed');
        }else{
            $objTicket->id_status = (int) $objStatusMapping->getMappedStatusIdByStatus('open');
        }
//        $objTicket->id_status = (int) $objStatusMapping->getMappedStatusIdByStatus('open');
//        $objTicket->id_status = (int) $objStatusMapping->getMappedStatusIdByStatus('closed');
        $objTicket->subject = pSQL($this->module->l('New message from', 'WkHdTicketManager').' '.$customer->firstname.' '.$customer->lastname);
        $objTicket->id_order = (int) $idOrder;
        $objTicket->save();
        $ticketId = $objTicket->id;

        if ($ticketId) {
            $objTicketMsg = new WkHdTicketMsg();
            $objTicketMsg->hd_id_ticket = (int) $ticketId;
            $objTicketMsg->message = nl2br($message);
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
            $msg_id = $objTicketMsg->id;

            if (!empty($ticketAttachment) && $msg_id) {
                $this->attachFileToReply($ticketAttachment, $msg_id);
            }

            $protocolLink = (Configuration::get('PS_SSL_ENABLED') || Tools::usingSecureMode()) ?
            'https://' : 'http://';
            $ticketLink = $protocolLink . Tools::getShopDomainSsl() . __PS_BASE_URI__ .
            'index.php?fc=module&module=wkhelpdesk&controller=viewticket&id=' . $ticketId;

            if (!$idCustomer) { // generate token if customer is not registered
                $token = WkHdTicket::getToken();
                $ticketLink .= '&token=' . $token;
                $objTicketToken = new WkHdTicketToken();
                $objTicketToken->hd_id_ticket = (int) $ticketId;
                $objTicketToken->token = pSQL($token);
                $objTicketToken->save();
            }

            // confirmation mail to customer
            $ticketParams = [
                '{ticket_link}' => $ticketLink,
                '{customer_name}' => $customer->firstname . ' ' . $customer->lastname,
                '{subject}' => $objTicket->subject,
                '{message}' => $message,
                '{email}' => $customer->email,
                '{id_lang}' => $this->context->language->id,
                '{id_query_type}' => $idQueryType,
                '{ticket_id}' => $ticketId,
                '{isCustomer}' => $isCustomer ? 'customer' : 'visitor',
            ];

            $objTicketAgent = new WkHdTicketAgent();
            if (Configuration::get('WK_HD_NEW_TICKET_CUSTOMER_NOTIFICATON')) {
                $objTicketAgent->createTicketMailToCustomer($ticketParams); // confirmation mail to customer
            }
            if (Configuration::get('WK_HD_NEW_TICKET_AGENT_NOTIFICATON')) {
                $objTicketAgent->customerReplyToAgent($ticketParams); // mail to agents
            }

            // if ($this->context->customer->isLogged()) {
            //     Tools::redirectLink($this->context->link->getModuleLink(
            //         'wkhelpdesk',
            //         'ticketlist',
            //         ['created' => 1]
            //     ));
            // } else {
            //     Tools::redirectLink($this->context->link->getModuleLink(
            //         'wkhelpdesk',
            //         'createticket',
            //         ['created' => 1]
            //     ));
            // }
        } else {
            $this->context->controller->errors[] = $this->module->l('Your ticket did not create due to some technical error.', 'WkHdTicketManager');
        }
        return (int) $ticketId;
    }

    public function addReplyToTicket($ticketId, $message, $ticketAttachment, $idEmployee)
    {
        if ((int) $idEmployee > 0) {
            return $this->addAdminReplyToTicket($ticketId, $message, $ticketAttachment, $idEmployee);
        } else {
            $objStatusMapping = new WkHdStatusMapping();
            $objTicket = new WkHdTicket((int) $ticketId);
            $prevStatus = (int) $objTicket->id_status;
            $openIdStatus = (int) $objStatusMapping->getMappedStatusIdByStatus('open');
            $objTicket->id_status = (int) $openIdStatus;
            $objTicket->save();
    
            $closedIdStatus = (int) $objStatusMapping->getMappedStatusIdByStatus('closed');
            if ($closedIdStatus == $prevStatus) { // if ticket is closed add system note to open ticket
                $objTicket_msg = new WkHdTicketMsg();
                $objTicket_msg->hd_id_ticket = (int) $ticketId;
                $objTicket_msg->message = '';
                $objTicket_msg->id_customer = (int) 0;
                $objTicket_msg->id_agent = (int) 0;
                $objTicket_msg->is_internal_note = (int) 0;
                $objTicket_msg->is_status_update = (int) 1;
                $objTicket_msg->status_from = (int) $prevStatus;
                $objTicket_msg->status_to = (int) $openIdStatus;
                $objTicket_msg->is_agent_assign = (int) 0;
                $objTicket_msg->agent_from = (int) 0;
                $objTicket_msg->agent_to = (int) 0;
                $objTicket_msg->save();
            }
    
            $objTicket_msg = new WkHdTicketMsg();
            $objTicket_msg->hd_id_ticket = (int) $ticketId;
            $objTicket_msg->message = nl2br($message);
            $objTicket_msg->id_customer = (int) $objTicket->hd_id_customer;
            $objTicket_msg->id_agent = (int) 0;
            $objTicket_msg->is_internal_note = (int) 0;
            $objTicket_msg->is_status_update = (int) 0;
            $objTicket_msg->status_from = (int) 0;
            $objTicket_msg->status_to = (int) 0;
            $objTicket_msg->is_agent_assign = (int) 0;
            $objTicket_msg->agent_from = (int) 0;
            $objTicket_msg->agent_to = (int) 0;
            $objTicket_msg->save();
            $idMsg = $objTicket_msg->id;
    
            if (!empty($ticketAttachment)) {
                $this->attachFileToReply($ticketAttachment, $idMsg);
            }
            if (Configuration::get('WK_HD_CUSTOMER_REPLY_MAIL')) {
                $objTicket_agent = new WkHdTicketAgent();
                $objTicket_agent->customerReplyToAgent(
                    array('{ticket_id}' => $ticketId, '{message}' => $message),
                    true
                );
            }
        }
    }

    public function addAdminReplyToTicket($ticketId, $message, $ticketAttachment, $idEmployee)
    {
        $objTicketAgent = new WkHdTicketAgent();
        $agentInfo = $objTicketAgent->getAgentInfoByIdEmployee($idEmployee);

        $objTicketMsg = new WkHdTicketMsg();
        $objTicketMsg->hd_id_ticket = (int) $ticketId;
        $objTicketMsg->message = nl2br($message);
        $objTicketMsg->id_customer = (int) 0;
        $objTicketMsg->id_agent = 0;
        $objTicket = new WkHdTicket($ticketId);
        if (!empty($agentInfo)) {
            $objTicketMsg->id_agent = (int) $agentInfo['id'];
        } else {
            if ((int) $objTicket->assigned_agent_id > 0) {
                $objTicketMsg->id_agent = (int) $objTicket->assigned_agent_id;
            } else {
                // find any agent and assign
                $agent = Db::getInstance()->getRow(
                    'SELECT * FROM `'._DB_PREFIX_.'wk_hd_ticket_agent` g'
                    .WkHdGroup::addSqlAssociationCustom('wk_hd_ticket_agent', 'g')
                    .' WHERE GROUP BY g.`id` ORDER BY g.`is_super_admin` DESC'
                );
                if (!empty($agent)) {
                    $objTicketMsg->id_agent = (int) $agent['id'];
                }
            }
        }
        $objTicketMsg->is_status_update = (int) 0;
        $objTicketMsg->status_from = (int) 0;
        $objTicketMsg->status_to = (int) 0;
        $objTicketMsg->is_internal_note = (int) 0;
        $objTicketMsg->is_agent_assign = (int) 0;
        $objTicketMsg->agent_from = (int) 0;
        $objTicketMsg->agent_to = (int) 0;

        $objTicketMsg->save();
        $idMsg = $objTicketMsg->id;
        if ($idMsg) {
            if (!empty($ticketAttachment) && $idMsg) {
                $this->attachFileToReply($ticketAttachment, $idMsg);
            }
            $hdIdCustomer = $objTicket->hd_id_customer;
            // get mapped status of answer status
            $objStatusMapping = new WkHdStatusMapping();
            $objTicket->id_status = (int) $objStatusMapping->getMappedStatusIdByStatus('Answered');
            $objTicket->save();
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
    }

    public function attachFileToReply($filename, $idMsg)
    {
        // file_name
        $filePath = _PS_UPLOAD_DIR_.$filename;
        if (file_exists($filePath)) {
            $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
            $uploadPath = _PS_MODULE_DIR_.'wkhelpdesk/ticketattachments/';
            $fileName = $idMsg.'_1.'.$fileExtension; // _1 because this is msg mail attachment
            if (Tools::copy($filePath, $uploadPath.$fileName)) {
                $obj_msg_attachment = new WkHdTicketAttachment();
                $obj_msg_attachment->hd_id_msg = (int) $idMsg;
                $obj_msg_attachment->attachment_name = pSQL($fileName);
                $obj_msg_attachment->attachment_token = pSQL(WkHdTicket::getToken());
                $obj_msg_attachment->save();
            }
        }
    }
    public function addCustomerThreadMessage($idThread, $ticketMsg)
    {
        $ct = new CustomerThread($idThread);
        if (Validate::isLoadedObject($ct)) {
            $customer = new Customer($ct->id_customer);
            $idEmployee = 0;
            $contact = new Contact((int) $ct->id_contact, (int) $ct->id_lang);
            if (Validate::isLoadedObject($contact)) {
                $from_name = $contact->name;
                $from_email = $contact->email;
            } else {
                $from_name = null;
                $from_email = null;
            }
            if ($ticketMsg->id_agent > 0) {
                $objAgent = new WkHdTicketAgent();
                $agentInfo = $objAgent->getAgentInfoById($ticketMsg->id_agent);
                if (!empty($agentInfo)) {
                    $idEmployee = $agentInfo['employee_id'];
                } else {
                    $this->context->controller->errors[] = $this->module->l('Agent not found!', 'WkHdTicketManager');
                    return;
                }
                $cm = new CustomerMessage();
                $cm->id_employee = (int) $idEmployee;
                $cm->id_customer_thread = $ct->id;
                $cm->ip_address = (int) ip2long(Tools::getRemoteAddr());
                $cm->message = pSQL(Tools::purifyHTML($ticketMsg->message));
                $cm->add();
                // Send mail notification

                // $params = [
                //     '{reply}' => Tools::nl2br(Tools::htmlentitiesUTF8(pSQL(Tools::purifyHTML($ticketMsg->message)))),
                //     '{link}' => Tools::url(
                //         $this->context->link->getPageLink('contact', true, null, null, false, $ct->id_shop),
                //         'id_customer_thread=' . (int) $ct->id . '&token=' . $ct->token
                //     ),
                //     '{firstname}' => $customer->firstname,
                //     '{lastname}' => $customer->lastname,
                // ];
                // //#ct == id_customer_thread    #tc == token of thread   <== used in the synchronization imap

                // if (Mail::Send(
                //     (int) $ct->id_lang,
                //     'reply_msg',
                //     sprintf(
                //         $this->module->l('An answer to your message is available #ct%s #tc%s', 'WkHdTicketManager'),
                //         $ct->id,
                //         $ct->token
                //     ),
                //     $params,
                //     Tools::strlen(trim($customer->email)) ? $customer->email : $ct->email,
                //     null,
                //     $from_email,
                //     $from_name,
                //     null,
                //     null,
                //     _PS_MAIL_DIR_,
                //     true,
                //     $ct->id_shop
                // )) {
                    $ct->status = 'closed';
                    $ct->update();
                // }
            } else {
                $cm = new CustomerMessage();
                $cm->id_customer_thread = $ct->id;
                $cm->message = pSQL(Tools::purifyHTML($ticketMsg->message));
                $objAttachment = new WkHdTicketAttachment();
                $attachments = $objAttachment->getAttachmentByIdMsg($ticketMsg->id);
                if (!empty($attachments)) {
                    $attachment = $attachments[0];
                    $origPath = _PS_MODULE_DIR_.'wkhelpdesk/ticketattachments/'.$attachment['attachment_name'];
                    if (file_exists($origPath)) {
                        $renamed = uniqid() . Tools::strtolower(substr($attachment['attachment_name'], -5));
                        if (copy($origPath, _PS_UPLOAD_DIR_ . basename($renamed))) {
                            $cm->file_name = $renamed;
                            @chmod(_PS_UPLOAD_DIR_ . basename($renamed), 0664);
                        }
                    }
                }
                $cm->ip_address = (int)ip2long(Tools::getRemoteAddr());
                $cm->add();

                // $sendConfirmationEmail = Configuration::get('CONTACTFORM_SEND_CONFIRMATION_EMAIL');
                // $sendNotificationEmail = Configuration::get('CONTACTFORM_SEND_NOTIFICATION_EMAIL');

                // if (!count($this->context->controller->errors)
                //     && ($sendConfirmationEmail || $sendNotificationEmail)
                // ) {
                //     $var_list = [
                //         '{firstname}' => '',
                //         '{lastname}' => '',
                //         '{order_name}' => '-',
                //         '{attached_file}' => '-',
                //         '{message}' => Tools::nl2br(Tools::htmlentitiesUTF8(Tools::stripslashes(pSQL(Tools::purifyHTML($ticketMsg->message))))),
                //         '{email}' =>  Tools::strlen(trim($customer->email)) ? $customer->email : $ct->email,
                //         '{product_name}' => '',
                //     ];

                //     if (isset($customer->id)) {
                //         $var_list['{firstname}'] = $customer->firstname;
                //         $var_list['{lastname}'] = $customer->lastname;
                //     }
                //     $id_product = (int)Tools::getValue('id_product');

                //     if ($ct->id_order) {
                //         $order = new Order((int)$ct->id_order);
                //         $var_list['{order_name}'] = $order->getUniqReference();
                //         $var_list['{id_order}'] = (int)$order->id;
                //     }

                //     if ($ct->id_product) {
                //         $product = new Product((int)$id_product);

                //         if (Validate::isLoadedObject($product) &&
                //             isset($product->name[Context::getContext()->language->id])
                //         ) {
                //             $var_list['{product_name}'] = $product->name[Context::getContext()->language->id];
                //         }
                //     }

                //     if ($sendNotificationEmail) {
                //         Mail::Send(
                //             $this->context->language->id,
                //             'contact',
                //             $this->module->l('Message from contact form', 'WkHdTicketManager').' [no_sync]',
                //             $var_list,
                //             $from_email,
                //             $from_name,
                //             null,
                //             null,
                //             null,
                //             null,
                //             _PS_MAIL_DIR_,
                //             false,
                //             null,
                //             null,
                //             Tools::strlen(trim($customer->email)) ? $customer->email : $ct->email
                //         );
                //     }

                //     if ($sendConfirmationEmail) {
                //         $var_list['{message}'] = '(hidden)';

                //         Mail::Send(
                //             $this->context->language->id,
                //             'contact_form',
                //             ((isset($ct) && Validate::isLoadedObject($ct)) ? sprintf(
                //                 $this->module->l('Your message has been correctly sent #ct%s #tc%s', 'WkHdTicketManager'),
                //                 $ct->id,
                //                 $ct->token
                //             ) : $this->module->l('Your message has been correctly sent', 'WkHdTicketManager')),
                //             $var_list,
                //             Tools::strlen(trim($customer->email)) ? $customer->email : $ct->email,
                //             null,
                //             null,
                //             null,
                //             null,
                //             null,
                //             _PS_MAIL_DIR_,
                //             false,
                //             null,
                //             null,
                //             $from_email
                //             );
                //     }
                // }
            }
        }
    }

    public function mapTicketThread($idTicket, $idThread)
    {
        if (((int) $idTicket > 0) && (int) $idThread > 0) {
            return Db::getInstance()->insert('wk_hd_ticket_thread', ['id_ticket' => $idTicket, 'id_customer_thread' => $idThread]);
        }
        return false;
    }
    public function getThreadByTicket($idTicket)
    {
        return Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'wk_hd_ticket_thread` WHERE id_ticket ='.(int) $idTicket);
    }
    public function getTicketByThread($idThread)
    {
        return Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'wk_hd_ticket_thread` WHERE id_customer_thread ='.(int) $idThread);
    }
}
