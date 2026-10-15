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
 * @package   FmmHelpdesk
*/

class HelpdeskHelpdeskModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    protected $ticket;
    public function init()
    {
        if (Configuration::get('HELPDESK_ENABLE_DISABLE') != 1) {
            header('HTTP/1.1 404 Not Found');
            header('Status: 404 Not Found');
        }

        parent::init();

        if ($ticket_id = Tools::getValue('ticket_id')) {
            $this->ticket = new Tickets((int)$ticket_id);
            if (!Validate::isLoadedObject($this->ticket) || $this->ticket->t_customer_id != $this->context->customer->id) {
                header('HTTP/1.1 404 Not Found');
                header('Status: 404 Not Found');
                $this->errors[] = Tools::displayError('Ticket does not exist.');
            }
        }
    }

    public function initContent()
    {
        parent::initContent();
        $this->assignAll();

        if (!Context::getContext()->customer->isLogged()) {
            Tools::redirect('index.php?controller=helpdesk&redirect=module&module=helpdesk&action=account');
        }
        if (Context::getContext()->customer->id) {
            $seo_url = Configuration::get('PS_REWRITING_SETTINGS');
            $this->context->smarty->assign('seo_url', $seo_url);
            $sitekey = Configuration::get('HELPDESK_SITEKEY_CAPTCHA');
            $this->context->smarty->assign('sitekey', $sitekey);

            if (trim(Tools::getValue('detail')) == 1 && Tools::getValue('ticket_id') && $this->ticket->t_customer_id == $this->context->customer->id) {
                $ticket_id = (int)Tools::getValue('ticket_id');
                if (trim(Tools::getValue('action')) == 'sendmessage') {
                    $this->replyTicket($ticket_id);
                }
                
                $this->ticketDetail($ticket_id, (int)Context::getContext()->customer->id);
                if (Tools::version_compare(_PS_VERSION_, '1.7.0.0', '>=') == true) {
                    $this->context->smarty->assign(array(
                        'its_ps17' => true
                        ));
                    $this->setTemplate('module:helpdesk/views/templates/front/ticket-detail_17.tpl');
                } elseif (_PS_VERSION_ >= 1.6 && Tools::version_compare(_PS_VERSION_, '1.7.0.0', '<') == true) {
                    $this->setTemplate('ticket-detail.tpl');
                } else {
                    $this->setTemplate('previous-ticket-detail.tpl');
                }
            } else {
                if (trim(Tools::getValue('action')) == 'ticketsubmit') {
                    $this->submitTicket();
                }

                $meta_title = Configuration::get('HELPDESK_PAGE_TITLE'.$this->context->language->id);
                $this->context->smarty->assign('meta_title', $meta_title);

                $this->context->smarty->assign('ticketDepartments', Tickets::getDepartments());
                $this->context->smarty->assign('ticketPriorities', Tickets::getPriorities());
                $this->context->smarty->assign('userPostedTickets', Tickets::getUserTickets((int)Context::getContext()->customer->id));
                $this->context->smarty->assign(array('id_module' => $this->module->id));
                if (Tools::version_compare(_PS_VERSION_, '1.7.0.0', '>=') == true) {
                    $force_ssl = (Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE'));
                    $this->context->smarty->assign(array(
                        'base_dir' => _PS_BASE_URL_.__PS_BASE_URI__,
                        'base_dir_ssl' => _PS_BASE_URL_SSL_.__PS_BASE_URI__,
                        'force_ssl' => $force_ssl,
                        'its_ps17' => true
                        ));
                    $this->setTemplate('module:helpdesk/views/templates/front/helpdesk-tickets_17.tpl');
                } elseif (_PS_VERSION_ >= 1.6 && Tools::version_compare(_PS_VERSION_, '1.7.0.0', '<') == true) {
                    $this->setTemplate('helpdesk-tickets.tpl');
                } else {
                    $this->setTemplate('previous-helpdesk-tickets.tpl');
                }
            }
        }

        $action = Tools::getValue('action');
        $searchTxt = Tools::getValue('searchTxt');
        if ($action == 'search' && $searchTxt != '') {
            $obj = new Tickets();
            $searchTxt = Tools::getValue('searchTxt');
            $result = $obj->getsearchedtickets($searchTxt, (int)Context::getContext()->customer->id);
            $this->context->smarty->assign('result', $result);
            if (Tools::version_compare(_PS_VERSION_, '1.7.0.0', '>=') == true) {
                $this->setTemplate('module:helpdesk/views/templates/front/searched_tickets_17.tpl');
            } else {
                $this->setTemplate('searched_tickets.tpl');
            }
        }
    }

    public function submitTicket()
    {
        $ticket_subject     = Tools::getValue('ticket_subject');
        $content            = Tools::getValue('content');
        $t_department_id    = Tools::getValue('t_department_id');
        $t_priority_id      = Tools::getValue('t_priority_id');
        $customer_id        = Context::getContext()->customer->id;
        $id_lang = Context::getContext()->language->id;
        if (trim(Tools::getValue('action')) == 'ticketsubmit') {
            if (!$ticket_subject) {
                $this->errors[] = Tools::displayError('Please enter the subejct');
            } else if (!Validate::isCleanHtml($ticket_subject)) {
                $this->errors[] = Tools::displayError('Invalid Subject');
            }

            if (!$content) {
                $this->errors[] = Tools::displayError('Please enter content');
            } else if (!Validate::isCleanHtml($content)) {
                $this->errors[] = Tools::displayError('Invalid Content');
            }
            
            if (Configuration::get('HELPDESK_SHOW_DEPARTMENTS') == 1) {
                if (!$t_department_id) {
                    $this->errors[] = Tools::displayError('Please select department');
                }
            } else {
                $t_department_id = (int)Configuration::get('HELPDESK_DEFAULT_DEPARTMENT');
            }

            if (Configuration::get('HELPDESK_PRIORITIES') == 1) {
                if (!$t_priority_id) {
                    $this->errors[] = Tools::displayError('Please select priority');
                }
            } else {
                $t_priority_id = (int)Configuration::get('HELPDESK_DEFAULT_PRIORITY');
            }

            // file upload process starts
            if ($_FILES['helpdesk_attachment']['name']) {
                $helpdesk_file_types = Configuration::get('HELPDESK_ACCEPTED_FILE_TYPES');
                $file_types_array = explode(',', $helpdesk_file_types);
                $ext = Tools::strtolower(pathinfo($_FILES['helpdesk_attachment']['name'], PATHINFO_EXTENSION));
                
                if (in_array($ext, $file_types_array)) {
                    //if no errors...
                    if (!$_FILES['helpdesk_attachment']['error']) {
                        $filesize = 2097152;
                        $filesizemb = 2;
                        if (Configuration::get('HELPDESK_MAX_FILE_SIZE') != '') {
                            $filesizemb = Configuration::get('HELPDESK_MAX_FILE_SIZE');
                            $filesize = (int)$filesizemb * 1024 * 1024;
                        }
                        if ($_FILES['helpdesk_attachment']['size'] > ($filesize)) {
                            $this->errors[] = sprintf(Tools::displayError('Your file\'s must be less than %s MB.'), $filesizemb);
                        }
                    } else {
                        $this->errors[] = sprintf(Tools::displayError('Ooops!  Your upload triggered the following error:  %s'), $_FILES['helpdesk_attachment']['error']);
                    }
                } else {
                    $this->errors[] = Tools::displayError('File type not supported');
                }
            } if (empty($this->errors)) {
                $_ticket = new Tickets(null, $id_lang);
                $_ticket->ticket_subject = $ticket_subject;
                $_ticket->content = $content;
                $_ticket->t_department_id = $t_department_id;
                $_ticket->t_priority_id = $t_priority_id;
                $_ticket->t_customer_id = $customer_id;
                $_ticket->t_status_id = (int)Configuration::get('HELPDESK_DEFAULT_NEW_STATUS');
                $_ticket->t_created_time = date('Y-m-d H:i:s');
                $_ticket->t_update_time = date('Y-m-d H:i:s');
                $_ticket->id_shop = (int)Context::getContext()->shop->id;
                $_ticket->add();

                $ticket_id = (int)$_ticket->id;

                if (isset($_FILES['helpdesk_attachment'])) {
                    $image = $_FILES['helpdesk_attachment']['tmp_name'];
                    $img_name = $_FILES['helpdesk_attachment']['name'];
                    $path = _PS_IMG_DIR_.'helpdesk/'.$ticket_id.'/';
                    $dir = $path.$img_name;

                    if ($img_name) {
                        $ticketPath = 'helpdesk/'.$ticket_id.'/'.$img_name;
                    } else {
                        $ticketPath = '';
                    }
                    $new_dir = _PS_IMG_DIR_.'helpdesk/'.$ticket_id;
                    if (!file_exists($new_dir)) {
                        @mkdir($new_dir, 0777);
                    }
                    if (move_uploaded_file($image, $dir)) {
                        Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'fmm_hd_tickets`
                            SET `ticket_attachment` = "'.$ticketPath.'"
                            WHERE `ticket_id` = '.(int)$ticket_id);
                    }
                }
                
                    //Insert data into tickets responses table
                    Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'fmm_hd_tickets_responses`
                        (r_ticket_id, r_message, r_attachment, r_client_id, r_created_time)
                        VALUES ( 
                                "'.(int)$_ticket->id.'",
                                "'.pSQL($content).'",
                                "'.pSQL($ticketPath).'",
                                "'.(int)$customer_id.'",
                                "'.date('Y-m-d H:i:s').'"
                                )
                            ');
                
                // sending new ticket alerts
                $newTicketAlert = Configuration::get('HELPDESK_NEW_TICKET_ALERT');
                $username="";
                $useremail="";
                ////this parameter is passed in adminticket controller
                if ($newTicketAlert == 1) {
                    $ticket_id = (int)$_ticket->id;
                    $_ticket->sendAdminEmail($ticket_id, 0, $content, $username, $useremail);
                }

                // autorepsond to user
                $newTicketRespond = Configuration::get('HELPDESK_NEW_TICKET_RESPOND');
                if ($newTicketRespond == 1) {
                    $ticket_id = (int)$_ticket->id;
                    $_ticket->sendUserEmail($ticket_id, 0, $content, $t_department_id, $username, $useremail);
                }
                ///send to department email
                    $ticket_id = (int)$_ticket->id;
                    $_ticket->sendtoDepartmentmail($ticket_id, $content, $t_department_id);
                $this->context->smarty->assign('confirmation', 1);
            }
        }
    }

    public function ticketDetail($ticket_id, $user_id)
    {
        $Obj = new Tickets();
        $this->context->smarty->assign('Obj', $Obj);
        
        if (Tools::getValue('ticket_id')) {
            $ticket_id = (int)Tools::getValue('ticket_id');
            $ticketData = $Obj->getUserTicketDetail($ticket_id, $user_id);
            $this->context->smarty->assign('meta_title', $ticketData[0]['ticket_subject']);
            $ticketResponsesData = $Obj->getUserTicketResponses($ticket_id);
            $this->context->smarty->assign('customername', Context::getContext()->customer->firstname.' '.Context::getContext()->customer->lastname);
            $this->context->smarty->assign('ticketData', $ticketData);
            $this->context->smarty->assign('ticketResponsesData', $ticketResponsesData);
            $this->context->smarty->assign('path', __PS_BASE_URI__);
        }
    }

    public function replyTicket($ticket_id)
    {
        $Obj = new Tickets();
        $r_message          = Tools::getValue('r_message');
        $close_on_reply     = Tools::getValue('close_on_reply');
        $customer_id        = Context::getContext()->customer->id;

        $image = $_FILES['user_attachment']['tmp_name'];
        $img_name = $_FILES['user_attachment']['name'];
        $path = _PS_IMG_DIR_.'helpdesk/'.$ticket_id.'/';
        $dir = $path.$img_name;
        if ($img_name) {
            $ticketPath = 'helpdesk/'.$ticket_id.'/'.$img_name;
        } else {
            $ticketPath = '';
        }
        move_uploaded_file($image, $dir);

        $another_update = '';
        if (trim(Tools::getValue('action')) == 'sendmessage') {
            if (!$r_message) {
                $this->errors[] = Tools::displayError('Please enter the message');
            } else if (!Validate::isCleanHtml($r_message)) {
                $this->errors[] = Tools::displayError('Invalid Message');
            }

            if (empty($this->errors)) {
                $department = Db::getInstance()->getRow('
                    SELECT t1.t_department_id
                    FROM '._DB_PREFIX_.'fmm_hd_tickets t1
                    WHERE t1.`ticket_id` = '.(int)$ticket_id);
                //Insert data into tickets responses table
                Db::getInstance()->execute('
                INSERT INTO `'._DB_PREFIX_.'fmm_hd_tickets_responses` ( 
                    r_ticket_id, r_message, r_attachment, r_client_id, r_created_time 
                ) VALUES ( 
                    "'.(int)$ticket_id.'",
                    "'.pSQL($r_message).'",
                    "'.pSQL($ticketPath).'",
                    "'.(int)$customer_id.'",
                    "'.date('Y-m-d H:i:s').'")
                ');
                if ($close_on_reply != '' && $close_on_reply == 1) {
                    $t_status_id = (int)Configuration::get('HELPDESK_DEFAULT_CLOSE_STATUS');
                    $another_update = ' , `t_status_id` = '.$t_status_id.' ';
                }
                // update the record
                Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'fmm_hd_tickets`
                SET `last_response_client` = "'.date('Y-m-d H:i:s').'"
                '.$another_update.'
                WHERE `ticket_id` = '.(int)$ticket_id);
                $this->context->smarty->assign('confirmation', 1);
                $username="";
                $useremail="";
                ///this parameter is passed with in adminTicket controllere
                // Sending New message alert
                $newMsgAlert = Configuration::get('HELPDESK_NEW_MESSAGE_ALERT');
                if ($newMsgAlert == 1) {
                    $Obj->sendAdminEmail((int)$ticket_id, 1, $r_message, $username, $useremail);
                }
                // autorepsond to user for a message
                $newTicketRespond = Configuration::get('HELPDESK_NEW_MESSAGE_ALERT');
                if ($newTicketRespond == 1) {
                    $Obj->sendUserEmail((int)$ticket_id, 1, $r_message, $department['t_department_id'], $username, $useremail);
                }
            }
        }
    }

    public function assignAll()
    {
        $this->context->smarty->assign('HELPDESK_PAGE_TITLE', Configuration::get('HELPDESK_PAGE_TITLE'.$this->context->language->id));
        $this->context->smarty->assign('HELPDESK_ENABLE_DISABLE', Configuration::get('HELPDESK_ENABLE_DISABLE'));
        $this->context->smarty->assign('HELPDESK_PRIORITIES', Configuration::get('HELPDESK_PRIORITIES'));
        $this->context->smarty->assign('HELPDESK_SHOW_DEPARTMENTS', Configuration::get('HELPDESK_SHOW_DEPARTMENTS'));
        $this->context->smarty->assign('HELPDESK_FILE_UPLOADS', Configuration::get('HELPDESK_FILE_UPLOADS'));
        $this->context->smarty->assign('HELPDESK_MAX_FILE_SIZE', Configuration::get('HELPDESK_MAX_FILE_SIZE'));
        $this->context->smarty->assign('HELPDESK_ACCEPTED_FILE_TYPES', Configuration::get('HELPDESK_ACCEPTED_FILE_TYPES'));
        $this->context->smarty->assign('HELPDESK_NEW_TICKET_ALERT', Configuration::get('HELPDESK_NEW_TICKET_ALERT'));
        $this->context->smarty->assign('HELPDESK_NEW_MESSAGE_ALERT', Configuration::get('HELPDESK_NEW_MESSAGE_ALERT'));
        $this->context->smarty->assign('HELPDESK_EMAIL_COPY', Configuration::get('HELPDESK_EMAIL_COPY'));
        $this->context->smarty->assign('HELPDESK_DEFAULT_ALERT_NAME', Configuration::get('HELPDESK_DEFAULT_ALERT_NAME'));
        $this->context->smarty->assign('HELPDESK_DEFAULT_ALERT_EMAIL', Configuration::get('HELPDESK_DEFAULT_ALERT_EMAIL'));
        $this->context->smarty->assign('HELPDESK_NEW_TICKET_RESPOND', Configuration::get('HELPDESK_NEW_TICKET_RESPOND'));
        $this->context->smarty->assign('HELPDESK_CLOSE_TICKET_NOTICE', Configuration::get('HELPDESK_CLOSE_TICKET_NOTICE'));
        $this->context->smarty->assign('HELPDESK_ALLOW_CUSTOMERS_CLOSE', Configuration::get('HELPDESK_ALLOW_CUSTOMERS_CLOSE'));
        $this->context->smarty->assign('HELPDESK_ALLOW_CUSTOMERS_REOPEN', Configuration::get('HELPDESK_ALLOW_CUSTOMERS_REOPEN'));
        $this->context->smarty->assign('HELPDESK_ENABLE_EDITOR_MESSAGE', Configuration::get('HELPDESK_ENABLE_EDITOR_MESSAGE'));
        $this->context->smarty->assign('HELPDESK_ENABLE_GOOGLE_CAPTCHA', Configuration::get('HELPDESK_ENABLE_GOOGLE_CAPTCHA'));
        $this->context->smarty->assign('HELPDESK_DEFAULT_PRIORITY', Configuration::get('HELPDESK_DEFAULT_PRIORITY'));
        $this->context->smarty->assign('HELPDESK_DEFAULT_DEPARTMENT', Configuration::get('HELPDESK_DEFAULT_DEPARTMENT'));
        $this->context->smarty->assign('HELPDESK_DEFAULT_NEW_STATUS', Configuration::get('HELPDESK_DEFAULT_NEW_STATUS'));
        $this->context->smarty->assign('HELPDESK_DEFAULT_EMAIL_TEMPLATE', Configuration::get('HELPDESK_DEFAULT_EMAIL_TEMPLATE'));
        $this->context->smarty->assign('HELPDESK_DEFAULT_CLOSE_STATUS', Configuration::get('HELPDESK_DEFAULT_CLOSE_STATUS'));
    }
    
    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->addCss(__PS_BASE_URI__ . 'modules/helpdesk/views/css/helpfront.css');
    }
}
