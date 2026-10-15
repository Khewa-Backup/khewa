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

class WkHelpDeskCreateTicketModuleFrontController extends ModuleFrontController
{
    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Create new ticket', 'createticket'),
            'url' => ''
        );

        return $breadcrumb;
    }

    public function initContent()
    {
        parent::initContent();
        if (Configuration::get('WK_HD_GUEST_TICKET') || $this->context->customer->isLogged()) {
            $smartyVars = array(
                'fileExtensions' => implode(', ', WkHdTicket::getSelectedFileExtension()),
                'bgColor' => Configuration::get('WK_HD_TITLE_BG_COLOR'),
                'textColor' => Configuration::get('WK_HD_TITLE_TEXT_COLOR'),
                'attachmentMaxSize' => Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE')
            );
            if (Configuration::get('WK_HD_ENABLE_CREATE_CAPTCHA')) {
                $smartyVars['wkHdCaptchaSiteKey'] = Configuration::get('WK_HD_CAPTCHA_SITE_KEY');
            }
            $objQueryType = new WkHdQueryType();
            $queryTypes = $objQueryType->getAllQueryType($this->context->language->id, true);
            if ($queryTypes) {
                $smartyVars['queryTypes'] = $queryTypes;
            }

            if ($this->context->customer->id) {
                $smartyVars['firstname'] = $this->context->customer->firstname;
                $smartyVars['lastname'] = $this->context->customer->lastname;
                $smartyVars['email'] = $this->context->customer->email;
            }

            $smartyVars['id_module'] = $this->module->id;
            $this->context->smarty->assign($smartyVars);
            $this->defineJSVars();
            $this->setTemplate('module:'.$this->module->name.'/views/templates/front/createticket.tpl');
        } else {
            Tools::redirect(
                $this->context->link->getPageLink(
                    'authentication',
                    true,
                    $this->context->language->id,
                    array('back' => $this->context->link->getModuleLink($this->module->name, 'createticket'))
                )
            );
        }
    }

    public function defineJSVars()
    {
        $jsVars = array(
            'imgRemove' => $this->module->l('Remove', 'createticket'),
            'err' => $this->module->l('Error:', 'createticket'),
            'choosefile_fileButtonHtml' => $this->module->l('Choose File', 'createticket'),
            'nofileselect_fileDefaultHtml' => $this->module->l('No file selected', 'createticket'),
            'prevImg' => $this->module->l('Please select previous attachment.', 'createticket'),
            'firstNameError' => $this->module->l('First name is required.', 'createticket'),
            'lastNameError' => $this->module->l('Last name is required.', 'createticket'),
            'emailError' => $this->module->l('Email  is required.', 'createticket'),
            'queryTypeError' => $this->module->l('Please select query type.', 'createticket'),
            'subjectError' => $this->module->l('Subject is required.', 'createticket'),
            'msgError' => $this->module->l('Message is required.', 'createticket'),
            'iso' => $this->context->language->iso_code,
            'mp_tinymce_path' => _MODULE_DIR_.'wkhelpdesk/libs',
            'noCaptchaError' => $this->module->l('Please click on the captcha.', 'createticket'),
            'filesizeError' => $this->module->l('File exceeds maximum size.', 'createticket'),
            'maxSizeAllowed' => Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE'),
            'captchaEnabled' => Configuration::get('WK_HD_ENABLE_CREATE_CAPTCHA')
        );

        Media::addJsDef($jsVars);
    }

    public function setMedia()
    {
        parent::setMedia();

        $this->registerJavascript('createticket-js', 'modules/'.$this->module->name.'/views/js/createticket.js');
        $this->registerJavascript('createticket-remove-js', 'modules/'.$this->module->name.'/views/js/wkimageremove.js');
        $this->context->controller->addJqueryPlugin('growl', null, false);
        $this->context->controller->registerStylesheet('growl-css', 'js/jquery/plugins/growl/jquery.growl.css');
        if (Configuration::get('WK_HD_ENABLE_CREATE_CAPTCHA')) {
            $this->registerJavascript(
                'captcha-js',
                'https://www.google.com/recaptcha/api.js',
                array(
                    'server' => 'remote'
                )
            );
        }
        $this->registerStylesheet(
            'helpdesk_global-css',
            'modules/'.$this->module->name.'/views/css/helpdesk_global.css'
        );
        $this->registerStylesheet(
            'helpdesk_createticket-css',
            'modules/'.$this->module->name.'/views/css/createticket.css'
        );
    }

    private function verifyRecaptchaToken($token)
    {
        if (!Configuration::get('WK_HD_ENABLE_CREATE_CAPTCHA')) {
            return true;
        } elseif (!Tools::strlen(trim($token))) {
            $this->errors[] = $this->module->l('Please check the captcha.', 'createticket');
        } else {
            $request = array(
                'secret' => Configuration::get('WK_HD_CAPTCHA_SECRET_KEY'),
                'response' => $token
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
            // Receive server response ...
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $server_output = curl_exec($ch);
            curl_close($ch);
            $response = json_decode($server_output, true);
            if (!$response['success']) {
                $this->errors[] = $this->module->l('Recaptcha could not be verified.', 'createticket');
            }
            return $response['success'];
        }
    }

    public function postProcess()
    {
        if (Tools::isSubmit('createTicket')) {
            $email = Tools::getValue('email');
            $subject = Tools::getValue('subject');
            $message = Tools::getValue('message');
            $lastname = Tools::getValue('lastname');
            $firstname = Tools::getValue('firstname');
            $queryType = Tools::getValue('queryType');
            $reference = Tools::getValue('reference');
            $recaptcha = Tools::getValue('g-recaptcha-response');

            if ($this->verifyRecaptchaToken($recaptcha)) {
                // set attachment variable
                $ticketAttachment = false;
                $ticketOtherAttachment = false;
                if ($_FILES['ticketAttachment']['tmp_name'] != '') {
                    $ticketAttachment = $_FILES['ticketAttachment'];
                }
                if (isset($_FILES['ticketOtherAttachment'])) {
                    $ticketOtherAttachment = $_FILES['ticketOtherAttachment'];
                }
                //Validate data
                if ($firstname != '') {
                    if (!Validate::isName($firstname)) {
                        $this->errors[] = $this->module->l('First name is not valid.', 'createticket');
                    }
                } else {
                    $this->errors[] = $this->module->l('First name is required field.', 'createticket');
                }

                if ($lastname != '') {
                    if (!Validate::isName($lastname)) {
                        $this->errors[] = $this->module->l('Last name is not valid.', 'createticket');
                    }
                } else {
                    $this->errors[] = $this->module->l('Last name is required field.', 'createticket');
                }

                if ($email != '') {
                    if (!Validate::isEmail($email)) {
                        $this->errors[] = $this->module->l('Email is not valid.', 'createticket');
                    }
                } else {
                    $this->errors[] = $this->module->l('Email is required field.', 'createticket');
                }

                if ($queryType == 0) {
                    $this->errors[] = $this->module->l('Select your query type.', 'createticket');
                }

                if ($subject != '') {
                    if (!Validate::isMailSubject($subject)) {
                        $this->errors[] = $this->module->l('Subject is not valid.', 'createticket');
                    } elseif (Tools::strlen($subject) > 255) {
                        $this->errors[] = $this->module->l('Subject is greater then 255 characters.', 'createticket');
                    }
                } else {
                    $this->errors[] = $this->module->l('Subject is required field.', 'createticket');
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

                if ($message != '') {
                    if (!Validate::isCleanHtml($message)) {
                        $this->errors[] = $this->module->l('Message is not valid.', 'createticket');
                    }
                } else {
                    $this->errors[] = $this->module->l('Message is required field.', 'createticket');
                }

                //validate ticket main file
                if ($ticketAttachment && !empty($ticketAttachment['name'])) {
                    if (!WkHdTicket::validateTicketMainAttachment($ticketAttachment)) {
                        $this->errors[] = $this->module->l('Main attachment file is not valid.', 'createticket');
                    }
                }

                //validate ticket other files
                if ($ticketOtherAttachment && !empty($ticketOtherAttachment['name'])) {
                    if (!WkHdTicket::validateTicketOtherAttachment($ticketOtherAttachment)) {
                        $this->errors[] = $this->module->l('Other attachment file(s) are not valid.', 'createticket');
                    }
                }

                if (!count($this->errors)) {
                    $idCustomer = 0;
                    $objHdCustomer = new WkHdCustomer();
                    $hdCustomer = $objHdCustomer->getCustomerByEmail($email);
                    if ($hdCustomer) {
                        $hdIdCustomer = $hdCustomer['id'];
                        $idCustomer = $hdCustomer['id_ps_customer'];
                        $isCustomer = $idCustomer;
                    } else {
                        $obj_customer = new Customer();
                        $customer_info = $obj_customer->getByEmail($email);
                        if ($customer_info) {
                            $idCustomer = $customer_info->id;
                            $objHdCustomer->id_ps_customer = (int) $customer_info->id;
                            $isCustomer = 1;
                        } else {
                            $objHdCustomer->id_ps_customer = (int) 0;
                            $isCustomer = 0;
                        }
                        $objHdCustomer->first_name = pSQL($firstname);
                        $objHdCustomer->last_name = pSQL($lastname);
                        $objHdCustomer->email = pSQL($email);
                        $objHdCustomer->save();
                        $hdIdCustomer = $objHdCustomer->id;
                    }
                    $objTicket = new WkHdTicket();
                    $objTicket->hd_id_customer = (int) $hdIdCustomer;
                    $objTicket->first_name = pSQL($firstname);
                    $objTicket->last_name = pSQL($lastname);
                    $objTicket->id_query_type = (int) $queryType;
                    $objTicket->assigned_agent_id = (int) 0;
                    $objStatusMapping = new WkHdStatusMapping();
                    $objTicket->id_status = (int) $objStatusMapping->getMappedStatusIdByStatus('open');
                    $objTicket->subject = pSQL($subject);
                    $objTicket->id_order = (int) WkHdTicket::getOrder(trim(Tools::getValue('reference')));
                    $objTicket->save();
                    $ticketId = $objTicket->id;

                    if ($ticketId) {
                        $objTicketMsg = new WkHdTicketMsg();
                        $objTicketMsg->hd_id_ticket = (int) $ticketId;
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
                        $msg_id = $objTicketMsg->id;

                        if ($ticketAttachment && $msg_id) {
                            WkHdTicketAttachment::uploadTicketAttachment($ticketAttachment, $msg_id);
                        }

                        if ($ticketOtherAttachment && $msg_id) {
                            WkHdTicketAttachment::uploadTicketOtherAttachment($ticketOtherAttachment, $msg_id);
                        }

                        $protocolLink = (Configuration::get('PS_SSL_ENABLED') || Tools::usingSecureMode()) ?
                        'https://' : 'http://';
                        $ticketLink = $protocolLink.Tools::getShopDomainSsl().__PS_BASE_URI__.
                        'index.php?fc=module&module=wkhelpdesk&controller=viewticket&id='.$ticketId;

                        if (!$idCustomer) { // generate token if customer is not registered
                            $token = WkHdTicket::getToken();
                            $ticketLink .= '&token='.$token;
                            $objTicketToken = new WkHdTicketToken();
                            $objTicketToken->hd_id_ticket = (int) $ticketId;
                            $objTicketToken->token = pSQL($token);
                            $objTicketToken->save();
                        }

                        //confirmation mail to customer
                        $ticketParams = array(
                            '{ticket_link}' => $ticketLink,
                            '{customer_name}' => $firstname.' '.$lastname,
                            '{subject}' => $subject,
                            '{message}' => $message,
                            '{email}' => $email,
                            '{id_lang}' => $this->context->language->id,
                            '{id_query_type}' => $queryType,
                            '{ticket_id}' => $ticketId,
                            '{isCustomer}' => $isCustomer ? 'customer' : 'visitor',
                        );

                        $objTicketAgent = new WkHdTicketAgent();
                        if (Configuration::get('WK_HD_NEW_TICKET_CUSTOMER_NOTIFICATON')) {
                            $objTicketAgent->createTicketMailToCustomer($ticketParams); //confirmation mail to customer
                        }
                        if (Configuration::get('WK_HD_NEW_TICKET_AGENT_NOTIFICATON')) {
                            $objTicketAgent->customerReplyToAgent($ticketParams); // mail to agents
                        }

                        if ($this->context->customer->isLogged()) {
                            Tools::redirectLink($this->context->link->getModuleLink(
                                'wkhelpdesk',
                                'ticketlist',
                                array('created' => 1)
                            ));
                        } else {
                            Tools::redirectLink($this->context->link->getModuleLink(
                                'wkhelpdesk',
                                'createticket',
                                array('created' => 1)
                            ));
                        }
                    } else {
                        $this->errors[] = $this->module->l(
                            'Your ticket did not create due to some technical error.',
                            'createticket'
                        );
                    }
                }
            }
        }
    }
}
