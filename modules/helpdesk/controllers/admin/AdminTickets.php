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

class AdminTicketsController extends ModuleAdminController
{
    public function __construct()
    {
        //Get Seprator
        $os = PHP_OS;
        switch ($os) {
            case 'Linux':
                define('SEPARATOR', '/');
                break;
            case 'Windows':
                define('SEPARATOR', '\\');
                break;
            default:
                define('SEPARATOR', '/');
                break;
        }
        
        $this->table = 'fmm_hd_tickets';
        $this->className = 'Tickets';
        $this->identifier = 'ticket_id';
        $this->lang = false;
        $this->deleted = false;
        $this->colorOnBackground = false;
        $this->bootstrap = true;
        parent::__construct();
        
        $this->context = Context::getContext();

        $this->fields_list = array(
            'ticket_id' => array(
                'title'     => $this->l('ID'),
                'width' => 25
            ),
            'ticket_subject' => array(
                'title'     => $this->l('Ticket Subject'),
            ),
            't_department_id' => array(
                'title'     => $this->l('Department'),
                'callback'  => 'getDepartmentName'
            ),
            't_status_id' => array(
                'title'     => $this->l('Ticket Status'),
                'callback'  => 'getStatusName'
            ),
            't_priority_id' => array(
                'title'     => $this->l('Priority'),
                'align'     => 'center',
                'callback'  => 'getPriorityName',
            ),
            't_customer_id' => array(
                'title'     => $this->l('Customer'),
                'callback'  => 'getCustomerName'
            ),
            'last_response_client' => array(
                'title'     => $this->l('Last User Response')
            ),
        );

        if (Shop::isFeatureActive()) {
            $this->fields_list['id_shop'] = array(
                'title'     => $this->l('Shop'),
                'width'     => 25,
                'align'     => 'center',
                'callback'  => 'getShopName',
            );
        }
            
        $this->bulk_actions = array('delete' => array('text' => $this->l('Delete selected'), 'confirm' => $this->l('Delete selected items?')));
    }
    
    public function getShopName($id_shop)
    {
        $shop = new Shop($id_shop);
        return $shop->name;
    }

    public function renderList()
    {
        if (Shop::getContext() != Shop::CONTEXT_ALL) {
            $this->_where = 'AND a.`id_shop` = '.(int)Context::getContext()->shop->id;
        }
        // $this->toolbar_btn = array();
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        return parent::renderList();
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->context->controller->addJS(_MODULE_DIR_.$this->module->name.'/views/js/helpdesk.js');
        $this->context->controller->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/helpdesk.css');
    }

    public function renderForm()
    {
        $back = Tools::safeOutput(Tools::getValue('back', ''));
        if (empty($back)) {
            $back = self::$currentIndex.'&token='.$this->token;
        }

        $this->toolbar_btn['save-and-stay'] = array(
            'href' => '#',
            'desc' => $this->l('Save and Stay'),
            'class' => 'button'
        );

        $this->fields_form['submit'] = array(
            'title' => $this->l('Save'),
            'class' => 'button btn btn-default pull-right'
        );
        
        $this->context->smarty->assign('mode', $this->display);
        $Obj = new Tickets();
        
        if (!($this->loadObject(true))) {
            return;
        }

        $id = (int)Tools::getValue('ticket_id');
        
        if ($id) {
            $this->context->smarty->assign('ticket_id', $id);
            $this->context->smarty->assign('ticket_details', $Obj->getTicketDetails($id));
            $this->context->smarty->assign('ticketResponsesData', $Obj->getUserTicketResponses($id));
            $this->context->smarty->assign('ticketNotesData', $Obj->getUserTicketNotes($id));
        }
            $this->context->smarty->assign('ticketStatuses', Tickets::getStatuses());
            $this->context->smarty->assign('ticketDepartments', Tickets::getDepartments());
            $this->context->smarty->assign('ticketPriorities', Tickets::getPriorities());
            $this->context->smarty->assign('ticketPremades', Tickets::getPremades());
            $this->context->smarty->assign('path', __PS_BASE_URI__);
        $this->context->smarty->assign('path', __PS_BASE_URI__);
        $notes              = _PS_MODULE_DIR_.'helpdesk/views/templates/admin/tickets/notes.tpl';
        $informations       = _PS_MODULE_DIR_.'helpdesk/views/templates/admin/tickets/informations.tpl';
        $cust_orders       = _PS_MODULE_DIR_.'helpdesk/views/templates/admin/tickets/cust_orders.tpl';
        $cust_orders_msgs       = _PS_MODULE_DIR_.'helpdesk/views/templates/admin/tickets/cust_orders_msgs.tpl';
        $add_ticket       = _PS_MODULE_DIR_.'helpdesk/views/templates/admin/tickets/add_ticket.tpl';
                
        $libpath            = _MODULE_DIR_;
        $current_object = $this->loadObject(true);
        
        $this->context->smarty->assign('seprator', SEPARATOR);
        ////updating version working v.1.9.0
        $cust_info = Tickets::getTicketInfo($id);
        $cust_total_orders = Order::getCustomerOrders($cust_info['t_customer_id']);
        $custinfo =new Customer($cust_info['t_customer_id']);
        $custinfo =$custinfo->firstname.'.'.$custinfo->lastname;
        $getCust_order_msgs = array();
        foreach ($cust_total_orders as $key => $value) {
                $currency = new CurrencyCore($value['id_currency']);
                $my_currency_iso_code = $currency->sign;
                $cust_total_orders[$key]['id_currency'] = $my_currency_iso_code;
                 $getCust_order_msg = CustomerMessage::getMessagesByOrderId($value['id_order']);
            if ($getCust_order_msg && !empty($getCust_order_msg)) {
                 $getCust_order_msgs[$key] = $getCust_order_msg;
                 $getCust_order_msgs[$key]['id_order'] = $value['id_order'];
                 $getCust_order_msgs[$key]['reference'] = $value['reference'];
            }
        }
        $this->context->smarty->assign(
            array(
                'libpath' => $libpath,
                'show_toolbar' => true,
                'toolbar_btn' => $this->toolbar_btn,
                'toolbar_scroll' => $this->toolbar_scroll,
                'title' => array($this->l('Tickets')),
                'id_lang_default' => Configuration::get('PS_LANG_DEFAULT'),
                'currentToken' => $this->token,
                'currentIndex' => self::$currentIndex,
                'currentObject' => $current_object,
                'currentTab' => $this,
                'notes' => $notes,
                'informations' => $informations,
                'cust_orders_temp'=>$cust_orders,
                'cust_orders_msgs_temp'=>$cust_orders_msgs,
                'cust_total_orders'=>$cust_total_orders,
                'cust_orders_msgs'=>$getCust_order_msgs,
                'cust_info'=>$custinfo
            )
        );
        ///for adding ticket backoffice

        $meta_title = Configuration::get('HELPDESK_PAGE_TITLE'.$this->context->language->id);
        $this->context->smarty->assign('meta_title', $meta_title);
        $this->context->smarty->assign(array('id_module' => $this->module->id));
        $this->context->smarty->assign('ticketDepartments', Tickets::getDepartments());
        $this->context->smarty->assign('ticketPriorities', Tickets::getPriorities());
        $this->context->smarty->assign('addticket', $add_ticket);
        $this->assignAll();
        $check_version = _PS_VERSION_;
        $c_index = $this->context->link->getAdminLink('AdminTickets');
        $this->context->smarty->assign('url', $c_index);
        $this->context->smarty->assign('check_version', $check_version);
        $this->context->smarty->assign('snd_user_email', '1');
        $this->context->smarty->assign('snd_admin_email', '1');
        return parent::renderForm();
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
    
    public function postProcess()
    {
      ////for ajax search customer name
        if (Tools::isSubmit('name')) {
            $vals = Tools::getValue('name');
            if ($vals) {
                $this->getuserinfo($vals);
            }
        }
        ////for adding ticket from adimin side
        if (Tools::isSubmit('add_ticket')) {
            $this->submitTicket();
        }
        if (Tools::isSubmit('submitAdd'.$this->table)) {
            $id = (int)Tools::getValue('ticket_id');
            $this->saveTicketInfo($id);
        } else if (trim(Tools::getValue('action')) == 'get_premade_reply') {
            $premade_id = (int)Tools::getValue('premade_id');
            $id_lang = Context::getContext()->language->id;
            if ((Tools::getValue('premade_id')) && (Tools::getValue('premade_id') != 0 )) {
                $sql = 'SELECT tl.premade_content
                        FROM '._DB_PREFIX_.'fmm_hd_premade t
                        LEFT JOIN '._DB_PREFIX_.'fmm_hd_premade_lang tl
                            ON (t.premade_id = tl.premade_id AND tl.id_lang = '.(int)$id_lang.')
                        WHERE t.`premade_status` = 1
                        AND t.`premade_id` = '.(int)$premade_id;
                $row = Db::getInstance()->getRow($sql);
                echo $row['premade_content'];
                exit;
            }
        }
        $this->loadObject(true);
        parent::postProcess();
    }

    public function submitTicket()
    {
        $ticket_subject     = Tools::getValue('ticket_subject');
        $content            = Tools::getValue('content');
        $t_department_id    = Tools::getValue('t_department_id');
        $t_priority_id      = Tools::getValue('t_priority_id');
        $t_status_id = (int)Tools::getValue('t_status_id');
        $customer_email     = Tools::getValue('cust_email');
        $snd_admin_email     = Tools::getValue('snd_admin_email');
        $snd_user_email     = Tools::getValue('snd_user_email');
        $cust = new Customer();
        $cust->getByEmail($customer_email);
        $customer_id        = $cust->id;
        $cust_name          =$cust->firstname.' '.$cust->lastname;
        $id_lang = Context::getContext()->language->id;
        if (!$customer_id) {
                $this->errors[] = Tools::displayError('Customer email invalid');
        }
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
                $_ticket->t_status_id = $t_status_id;
                $_ticket->t_customer_id = $customer_id;
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
                if ($snd_admin_email == 1) {
                    $ticket_id = (int)$_ticket->id;
                    $_ticket->sendAdminEmail($ticket_id, 0, $content, $cust_name, $customer_email);
                }

                // autorepsond to user
                if ($snd_user_email == 1) {
                    $ticket_id = (int)$_ticket->id;
                    $_ticket->sendUserEmail($ticket_id, 0, Tools::getValue('reply_msg'), $t_department_id, $cust_name, $customer_email);
                }
                $this->context->smarty->assign('confirmation', 1);
                $this->saveTicketInfo($_ticket->id);
            }
        }
    }

    public function getuserinfo($data)
    {
        $sql = 'SELECT firstname,id_customer,lastname,email FROM '
        . _DB_PREFIX_ . 'customer WHERE firstname LIKE ' . "'%$data%'";
        $results = Db::getInstance()->ExecuteS($sql);
        if ($results) {
            echo json_encode($results);
            die();
        } else {
            echo ("Result not found");
            die();
        }
    }

    public function getDepartmentName($echo, $row)
    {
        $id_lang = Context::getContext()->language->id;
        $t_department_id = $row['t_department_id'];
        
        if (!(int)$t_department_id) {
            return '--';
        } else {
            $sql = 'SELECT t2.department_title
                FROM `'._DB_PREFIX_.'fmm_hd_departments` t1
                LEFT JOIN `'._DB_PREFIX_.'fmm_hd_departments_lang` t2 ON (
                t1.departments_id = t2.departments_id and t2.id_lang = '.(int)$id_lang.') where t1.departments_id = '.(int)$t_department_id;
            
            return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
        }
    }
    
    public function getStatusName($echo, $row)
    {
        $id_lang = Context::getContext()->language->id;
        $t_status_id = $row['t_status_id'];
        if (!(int)$t_status_id) {
            return '--';
        } else {
            $sql = 'SELECT t2.ticketstatus_title
                FROM `'._DB_PREFIX_.'fmm_hd_ticketstatus` t1
                LEFT JOIN `'._DB_PREFIX_.'fmm_hd_ticketstatus_lang` t2 ON (
                t1.ticketstatus_id = t2.ticketstatus_id and t2.id_lang = '.(int)$id_lang.')
                where t1.ticketstatus_id = '.(int)$t_status_id;
            return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
        }
    }

    public function getPriorityName($echo, $row)
    {
        $id_lang = Context::getContext()->language->id;
        $t_priority_id = $row['t_priority_id'];
        if (!(int)$t_priority_id) {
            return '--';
        } else {
            $sql = 'SELECT t2.priorities_title, t1.priority_color
                FROM `'._DB_PREFIX_.'fmm_hd_priorities` t1
                LEFT JOIN `'._DB_PREFIX_.'fmm_hd_priorities_lang` t2 ON (
                t1.priorities_id = t2.priorities_id and t2.id_lang = '.(int)$id_lang.') where t1.priorities_id = '.(int)$t_priority_id;
            
            $priority_values = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
            //print_r($priority_values);
            return "<span style='background-color:".$priority_values[0]['priority_color'].";color:white' class='badge color_field'>".$priority_values[0]['priorities_title'].'</span>';
        }
    }

    public function getCustomerName($echo, $row)
    {
        $t_customer_id = $row['t_customer_id'];
        $customer = new Customer((int)$t_customer_id);
        if (!(int)$t_customer_id) {
            return '--';
        } else {
            $firstName = $customer->firstname;
            $lastName = $customer->lastname;
            return $firstName.' '.$lastName;
        }
    }

    public function saveTicketInfo($ticketId)
    {
        $model = new Tickets();
        $dep_model = new Ticketdepartments();
        $status_model = new Ticketstatus();
        $priority_model = new Ticketpriorities();
        
        $ticket_detail = $model->getTicketDetails($ticketId);
        $t_status_id = (int)Tools::getValue('t_status_id');
        $t_priority_id = (int)Tools::getValue('t_priority_id');
        $t_department_id = (int)Tools::getValue('t_department_id');
        $department_title = $dep_model->getDepartmentTitle($t_department_id);
        //Get Ticket Status Title
        $status_title = $status_model->getStatusTitle($t_status_id);
        //Get Priority Title
        $priority_title = $priority_model->getPrioritiesTitle($t_priority_id);
        // dump($status_title);
        // die();
        //Check if there was the same department for the ticket
        if ($ticket_detail[0]['t_department_id'] != $t_department_id) {
            $note_title = $this->l('Department Transferred');
            $note_content = sprintf($this->l('Department Transferred to %s by Admin'), $department_title['department_title']);
            $note_created = date('Y-m-d H:i:s');
            Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'fmm_hd_notes`
                (note_ticket_id, note_title, note_content, note_created)
                VALUES ( 
                        "'.(int)$ticketId.'",
                        "'.pSQL($note_title).'",
                        "'.pSQL($note_content).'",
                        "'.pSQL($note_created).'")
                    ');
        }
        
        //Check if there was the same priority for the ticket
        if ($ticket_detail[0]['t_priority_id'] != $t_priority_id) {
            $note_title = $this->l('Priority Changed');
            $note_content = sprintf($this->l('Ticket priority set to %s by Admin'), $priority_title['priorities_title']);
            $note_created = date('Y-m-d H:i:s');
            Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'fmm_hd_notes`
                (note_ticket_id, note_title, note_content, note_created)
                VALUES ( 
                        "'.(int)$ticketId.'",
                        "'.pSQL($note_title).'",
                        "'.pSQL($note_content).'",
                        "'.pSQL($note_created).'")
                    ');
        }
        
        //Check if there was the same status for the ticket
        if ($ticket_detail[0]['t_status_id'] != $t_status_id) {
            $note_title = $this->l('Ticket Status Changed');
            $note_content = sprintf($this->l('Ticket status changed to %s by Admin'), $status_title['ticketstatus_title']);
            $note_created = date('Y-m-d H:i:s');
            Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'fmm_hd_notes`
                ( note_ticket_id, note_title, note_content, note_created)
                VALUES ( 
                        "'.(int)$ticketId.'",
                        "'.pSQL($note_title).'",
                        "'.pSQL($note_content).'",
                        "'.pSQL($note_created).'")
                    ');
            
            // autorepsond to user if ticket is close
            $status_close = (int)Configuration::get('HELPDESK_DEFAULT_CLOSE_STATUS');
            if ($t_status_id == $status_close) {
                $closeTicketRespond = (int)Configuration::get('HELPDESK_CLOSE_TICKET_NOTICE');
                if ($closeTicketRespond == 1) {
                    $model->sendCloseTicketMail($ticketId);
                }
            }
        }
        $notes_title = Tools::getValue('notes_title');
        $notes_content = Tools::getValue('notes_content');
        
        //Check if there was the note posted
        if ($notes_title != '' && $notes_content != '') {
            $note_created = date('Y-m-d H:i:s');
            Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'fmm_hd_notes`
                (note_ticket_id, note_title, note_content, note_created)
                VALUES ( 
                        "'.(int)$ticketId.'",
                        "'.pSQL($notes_title).'",
                        "'.pSQL($notes_content).'",
                        "'.pSQL($note_created).'")
                    ');
        }
        
        //Checking if there is some text to reply
        
        $reply_msg = Tools::getValue('reply_msg');
        $close_on_reply = Tools::getValue('close_on_reply');
        if ($reply_msg != '') {
            $this->doreplyAction($ticketId);
            // reply to user
            $replyTicketRespond = (int)Configuration::get('HELPDESK_NEW_MESSAGE_RESPOND');
            if ($replyTicketRespond == 1) {
                $model->sendNewReplyMail($ticketId, $reply_msg);
            }
            
            if ($close_on_reply != '' && $close_on_reply == 1) {
                $t_status_id = (int)Configuration::get('HELPDESK_DEFAULT_CLOSE_STATUS');
                // Updating the log for this ticket if checkbox close is on
                $note_title = $this->l('Ticket Status Changed');
                $note_content = $this->l('Ticket status changed to Closed by Admin');
                $note_created = date('Y-m-d H:i:s');
                
                Db::getInstance()->Execute(' INSERT INTO `'._DB_PREFIX_.'fmm_hd_notes`
                    (note_ticket_id, note_title, note_content, note_created)
                    VALUES ( 
                            "'.(int)$ticketId.'",
                            "'.pSQL($note_title).'",
                            "'.pSQL($note_content).'",
                            "'.pSQL($note_created).'")
                        ');
                
                // autorepsond to user if ticket is close
                $closeTicketRespond = (int)Configuration::get('HELPDESK_CLOSE_TICKET_NOTICE');
                if ($closeTicketRespond == 1) {
                    $model->sendCloseTicketMail($ticketId);
                }
            }
            $last_response_staff = date('Y-m-d H:i:s');
            Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'fmm_hd_tickets`
            SET `last_response_staff` = "'.$last_response_staff.'"
            WHERE `ticket_id` = '.(int)$ticketId);
        }
    }

    public function doreplyAction($ticketId)
    {
        $dep_model = new Ticketdepartments();
        $reply_msg = Tools::getValue('reply_msg');
        $t_department_id = Tools::getValue('t_department_id');
        $append_signature = Tools::getValue('append_signature');
        $r_created_time = date('Y-m-d H:i:s');
        $r_client_id = 0;
        $append_signagure = '';

        $image = $_FILES['admin_attachment']['tmp_name'];
        $img_name = $_FILES['admin_attachment']['name'];
        $path = _PS_IMG_DIR_.'helpdesk/'.$ticketId.'/';
        $dir = $path.$img_name;
        if ($img_name) {
            $ticketPath = 'helpdesk/'.$ticketId.'/'.$img_name;
        } else {
            $ticketPath = '';
        }
        move_uploaded_file($image, $dir);
        
        if ($append_signature == 1) {
            $dept = $dep_model->getDepartmentSignature($t_department_id);
            $append_signagure = nl2br($dept['department_signature']);
        }
        $r_message = nl2br($reply_msg).'<br/><br/>'.$append_signagure;
        Db::getInstance()->Execute(' INSERT INTO `'._DB_PREFIX_.'fmm_hd_tickets_responses`
            (r_ticket_id, r_message, r_attachment, r_client_id, r_created_time)
            VALUES (
                "'.(int)$ticketId.'",
                "'.pSQL($r_message).'",
                "'.pSQL($ticketPath).'",
                "'.pSQL($r_client_id).'",
                "'.pSQL($r_created_time).'")
            ');
    }
}
