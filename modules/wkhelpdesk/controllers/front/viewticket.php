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

class WkHelpDeskViewTicketModuleFrontController extends ModuleFrontController
{
    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Ticket list', 'viewticket'),
            'url' => $this->context->link->getModuleLink('wkhelpdesk', 'ticketlist')
        );

        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Ticket conversation', 'viewticket'),
            'url' => ''
        );

        return $breadcrumb;
    }

    public function initContent()
    {
        parent::initContent();
        $ticketId = Tools::getValue('id');
        if ($ticketId) {
            $objTicket = new WkHdTicket();
            $objTicketAttachment = new WkHdTicketAttachment();

            $smartyVars = array(
                'objTicketAttachment' => $objTicketAttachment,
                'bgColor' => Configuration::get('WK_HD_TITLE_BG_COLOR'),
                'textColor' => Configuration::get('WK_HD_TITLE_TEXT_COLOR'),
                'attachmentMaxSize' => Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE'),
                'fileExtensions' => implode(', ', WkHdTicket::getSelectedFileExtension()),
                'ticketDetails' => $objTicket->getTicketDetailsByIdAndIdLang($ticketId, $this->context->language->id),
                'formUrl' => $this->context->link->getModuleLink('wkhelpdesk', 'viewticket', array('id' => $ticketId)),
            );
            if (Configuration::get('WK_HD_ENABLE_VIEW_CAPTCHA')) {
                $smartyVars['wkHdCaptchaSiteKey'] = Configuration::get('WK_HD_CAPTCHA_SITE_KEY');
            }

            if ($this->context->customer->isLogged()) {
                $ticketConversation = $objTicket->getTicketConversationByIdTicketAndIdCustomer(
                    $ticketId,
                    $this->context->customer->id
                );

                if ($ticketConversation) {
                    $smartyVars['ticketConversation'] = $ticketConversation;
                } else {
                    $smartyVars['error'] = 1;
                }
            } elseif (Tools::getValue('token') != '') {
                $token = Tools::getValue('token');
                $ticketConversation = $objTicket->getTicketConversationByIdTicketAndToken($ticketId, $token);
                if ($ticketConversation) {
                    $smartyVars['ticketConversation'] = $ticketConversation;
                } else {
                    $smartyVars['error'] = 1;
                }
                $smartyVars['formUrl'] = $this->context->link->getModuleLink(
                    'wkhelpdesk',
                    'viewticket',
                    array('id' => $ticketId, 'token' => $token)
                );
            } else {
                Tools::redirect(
                    $this->context->link->getPageLink(
                        'authentication',
                        true,
                        $this->context->language->id,
                        array('back' => $smartyVars['formUrl'])
                    )
                );
            }
            $ticketValue = $objTicket->getTicketDetailsByIdAndIdLang($ticketId, $this->context->language->id);
            $statusNameWithId = array(
                $this->module->getStatusTextById($ticketValue['id_status'])
            );
            $smartyVars['statusNameWithId'] = $statusNameWithId;
            $smartyVars['statusColors'] = array(
                'lightseagreen',
                'green',
                'deepskyblue',
                'orange',
                'lightgreen',
                'red'
            );
            $this->context->smarty->assign($smartyVars);
            $this->defineJSVars();
            $this->setTemplate('module:'.$this->module->name.'/views/templates/front/viewticket.tpl');
        } else {
            Tools::redirect($this->context->link->getPageLink('my-account'));
        }
    }

    public function defineJSVars()
    {
        $jsVars = array(
            'img_remove' => $this->module->l('Remove', 'viewticket'),
            'err' => $this->module->l('Error:', 'viewticket'),
            'msgError' => $this->module->l('Message is required.', 'viewticket'),
            'choosefile_fileButtonHtml' => $this->module->l('Choose File', 'viewticket'),
            'nofileselect_fileDefaultHtml' => $this->module->l('No file selected', 'viewticket'),
            'prev_img' => $this->module->l('First select main attachment.', 'viewticket'),
            'iso' => $this->context->language->iso_code,
            'mp_tinymce_path' => _MODULE_DIR_.'wkhelpdesk/libs',
            'noCaptchaError' => $this->module->l('Please click on the captcha.', 'viewticket'),
            'filesizeError' => $this->module->l('File exceeds maximum size.', 'viewticket'),
            'maxSizeAllowed' => Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE'),
            'captchaEnabled' => Configuration::get('WK_HD_ENABLE_VIEW_CAPTCHA')
        );

        Media::addJsDef($jsVars);
    }

    public function setMedia()
    {
        parent::setMedia();

        $this->registerStylesheet('viewticket-css', 'modules/'.$this->module->name.'/views/css/viewticket.css');
        $this->registerJavascript('viewticket-js', 'modules/'.$this->module->name.'/views/js/viewticket.js');
        $this->registerJavascript('viewticket-remove-js', 'modules/'.$this->module->name.'/views/js/wkimageremove.js');
        $this->context->controller->addJqueryPlugin('growl', null, false);
        $this->context->controller->registerStylesheet('growl-css', 'js/jquery/plugins/growl/jquery.growl.css');
        if (Configuration::get('WK_HD_ENABLE_VIEW_CAPTCHA')) {
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
    }

    private function verifyRecaptchaToken($token)
    {
        if (!Configuration::get('WK_HD_ENABLE_VIEW_CAPTCHA')) {
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
        if (Tools::isSubmit('replyTicket')) {
            $recaptcha = Tools::getValue('g-recaptcha-response');
            if ($this->verifyRecaptchaToken($recaptcha)) {
                $token = Tools::getValue('token');
                $ticketId = Tools::getValue('id');
                $message = Tools::getValue('hd_message');
                $hdIdCustomer = Tools::getValue('hdIdCustomer');

                $ticketAttachment = false;
                $ticketOtherAttachment = false;
                if ($_FILES['ticketAttachment']['tmp_name'] != '') {
                    $ticketAttachment = $_FILES['ticketAttachment'];
                }
                if (isset($_FILES['ticketOtherAttachment'])) {
                    $ticketOtherAttachment = $_FILES['ticketOtherAttachment'];
                }

                if ($message != '') {
                    if (!Validate::isCleanHtml($message)) {
                        $this->errors[] = $this->module->l('Message is not valid.', 'viewticket');
                    }
                } else {
                    $this->errors[] = $this->module->l('Message is required field.', 'viewticket');
                }

                //validate ticket main file
                if ($ticketAttachment && !empty($ticketAttachment['name'])) {
                    if (!WkHdTicket::validateTicketMainAttachment($ticketAttachment)) {
                        $this->errors[] = $this->module->l('Main attachment file is not valid.', 'viewticket');
                    }
                }

                //validate ticket other files
                if ($ticketOtherAttachment && !empty($ticketOtherAttachment['name'])) {
                    if (!WkHdTicket::validateTicketOtherAttachment($ticketOtherAttachment)) {
                        $this->errors[] = $this->module->l('Other attachment file(s) are not valid.', 'viewticket');
                    }
                }

                if (!count($this->errors)) {
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
                    $objTicket_msg->message = pSQL(trim(preg_replace('/\s+/', ' ', $message)), true);
                    $objTicket_msg->id_customer = (int) $hdIdCustomer;
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

                    if ($ticketAttachment) {
                        WkHdTicketAttachment::uploadTicketAttachment($ticketAttachment, $idMsg);
                    }

                    if ($ticketOtherAttachment) {
                        WkHdTicketAttachment::uploadTicketOtherAttachment($ticketOtherAttachment, $idMsg);
                    }

                    if (Configuration::get('WK_HD_CUSTOMER_REPLY_MAIL')) {
                        $objTicket_agent = new WkHdTicketAgent();
                        $objTicket_agent->customerReplyToAgent(
                            array('{ticket_id}' => $ticketId, '{message}' => $message),
                            true
                        );
                    }

                    $paramsArray = array('sent' => 1, 'id' => $ticketId);
                    if ($token) {
                        $paramsArray['token'] = $token;
                    }
                    Tools::redirectLink(
                        $this->context->link->getModuleLink(
                            'wkhelpdesk',
                            'viewticket',
                            $paramsArray
                        )
                    );
                }
            }
        }
    }
}
