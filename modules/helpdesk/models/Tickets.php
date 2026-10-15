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

class Tickets extends ObjectModel
{
    public $id;
    public $ticket_id;
    public $t_department_id;
    public $t_priority_id;
    public $t_customer_id;
    public $ticket_subject;
    public $t_status_id;
    public $ticket_attachment;
    public $t_created_time;
    public $t_update_time;
    public $last_response_client;
    public $last_response_staff;
    public $id_shop;

    public static $definition = array(
        'table' => 'fmm_hd_tickets',
        'primary' => 'ticket_id',
        'multilang' => false,
        'fields' => array(
                'ticket_subject'            =>      array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 64),
                't_department_id'           =>      array('type' => self::TYPE_INT),
                't_priority_id'             =>      array('type' => self::TYPE_INT),
                't_customer_id'             =>      array('type' => self::TYPE_INT),
                't_status_id'               =>      array('type' => self::TYPE_INT),
                'ticket_attachment'         =>      array('type' => self::TYPE_STRING),
                't_created_time'            =>      array('type' => self::TYPE_DATE),
                't_update_time'             =>      array('type' => self::TYPE_DATE),
                'last_response_client'      =>      array('type' => self::TYPE_DATE),
                'last_response_staff'       =>      array('type' => self::TYPE_DATE),
                'id_shop'           =>      array('type' => self::TYPE_INT),
        ),
    );

    public function __construct($id = null, $id_lang = null)
    {
        parent::__construct($id, $id_lang);
    }

    public function getsearchedtickets($keywords, $user_id)
    {
            $id_lang = (int)Configuration::get('PS_LANG_DEFAULT');

            $sql = 'SELECT t.*, t1.`department_title`, t2.`priorities_title`, t3.`ticketstatus_title`, count(t4.`r_ticket_id`)-1 AS `total_replies`
            FROM '._DB_PREFIX_.'fmm_hd_tickets t
            LEFT OUTER JOIN '._DB_PREFIX_.'fmm_hd_departments_lang t1
                ON (t1.departments_id = t.t_department_id AND t1.id_lang = '.(int)$id_lang.')
            LEFT OUTER JOIN '._DB_PREFIX_.'fmm_hd_priorities_lang t2
                ON (t2.priorities_id = t.t_priority_id AND t2.id_lang = '.(int)$id_lang.')
            LEFT OUTER JOIN '._DB_PREFIX_.'fmm_hd_ticketstatus_lang t3
                ON (t3.ticketstatus_id = t.t_status_id AND t3.id_lang = '.(int)$id_lang.')
            LEFT OUTER JOIN '._DB_PREFIX_.'fmm_hd_tickets_responses t4
                ON (t4.r_ticket_id = t.ticket_id)
            WHERE (t.`ticket_subject` like (\'%'.$keywords.'%\') AND t.`t_customer_id` = '.$user_id.')';

        $posts = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        if ($posts === false) {
            return false;
        }
        return $posts;
    }

    public static function getLatestTickets($customer_id)
    {
        $sql = 'SELECT `ticket_subject`, `ticket_id`
            FROM '._DB_PREFIX_.'fmm_hd_tickets
            WHERE `t_customer_id` = '.(int)$customer_id.' ORDER BY `ticket_id` DESC';
        $posts = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        return $posts;
    }

    public function delete()
    {
        $res = Db::getInstance()->execute('
            DELETE FROM `'._DB_PREFIX_.'fmm_hd_tickets`
            WHERE `ticket_id` = '.(int)$this->ticket_id);
        $res &= parent::delete();
        return $res;
    }

    public function deleteSelection($selection)
    {
        if (!is_array($selection)) {
            die(Tools::displayError());
        }

        $result = true;
        foreach ($selection as $id) {
            $this->id = (int)$id;
            $this->ticket_id = Tickets::getTickets();
            $result = $result && $this->delete();
        }
        return $result;
    }

    public function getTickets()
    {
        if (!(int)$this->id) {
            return false;
        }

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `ticket_id` FROM '._DB_PREFIX_.'fmm_hd_tickets WHERE `ticket_id` = '.(int)$this->id);
    }

    public static function getDepartments()
    {
        $id_lang = Context::getContext()->language->id;
        $id_shop = (int)Context::getContext()->shop->id;
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
            SELECT t.departments_id, tl.department_title
            FROM '._DB_PREFIX_.'fmm_hd_departments t
            LEFT JOIN '._DB_PREFIX_.'fmm_hd_departments_lang tl
                ON (t.departments_id = tl.departments_id AND tl.id_lang = '.(int)$id_lang.')
            LEFT JOIN '._DB_PREFIX_.'fmm_hd_departments_shop ts
                ON (t.departments_id = ts.departments_id)
            WHERE t.`department_status` = 1
            AND ts.`id_shop` = '.(int)$id_shop.'
            ORDER BY t.departments_id');
    }

    public static function getPriorities()
    {
        $id_lang = Context::getContext()->language->id;
        
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
            SELECT t.priorities_id, t.priority_color, tl.priorities_title
            FROM '._DB_PREFIX_.'fmm_hd_priorities t
            LEFT JOIN '._DB_PREFIX_.'fmm_hd_priorities_lang tl
                ON (t.priorities_id = tl.priorities_id AND tl.id_lang = '.(int)$id_lang.')
            WHERE t.`priorities_status` = 1
            ORDER BY t.priorities_id');
    }

    public static function getStatuses()
    {
        $id_lang = Context::getContext()->language->id;
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
            SELECT t.ticketstatus_id, tl.ticketstatus_title
            FROM '._DB_PREFIX_.'fmm_hd_ticketstatus t
            LEFT JOIN '._DB_PREFIX_.'fmm_hd_ticketstatus_lang tl
                ON (t.ticketstatus_id = tl.ticketstatus_id AND tl.id_lang = '.(int)$id_lang.')
            WHERE t.`ticketstatus_status` = 1
            ORDER BY t.ticketstatus_id');
    }

    public static function getPremades()
    {
        $id_lang = Context::getContext()->language->id;
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
            SELECT t.premade_id, tl.premade_title
            FROM '._DB_PREFIX_.'fmm_hd_premade t
            LEFT JOIN '._DB_PREFIX_.'fmm_hd_premade_lang tl
                ON (t.premade_id = tl.premade_id AND tl.id_lang = '.(int)$id_lang.')
            WHERE t.`premade_status` = 1
            ORDER BY t.premade_id');
    }

    public static function getEmailTemps()
    {
        $id_lang = Context::getContext()->language->id;
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
            SELECT t.emailtemp_id, t.emailtemp_title
            FROM '._DB_PREFIX_.'fmm_hd_emailtemp t
            LEFT JOIN '._DB_PREFIX_.'fmm_hd_emailtemp_lang tl
                ON (t.emailtemp_id = tl.emailtemp_id AND tl.id_lang = '.(int)$id_lang.')
            WHERE t.`emailtemp_status` = 1
            ORDER BY t.emailtemp_id');
    }

    public static function getUserTickets($user_id)
    {
        $id_lang = Context::getContext()->language->id;
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
            SELECT t.*, t1.department_title, t2.priorities_title, t3.ticketstatus_title, count(t4.r_ticket_id)-1 AS `total_replies`
            FROM '._DB_PREFIX_.'fmm_hd_tickets t
            LEFT OUTER JOIN '._DB_PREFIX_.'fmm_hd_departments_lang t1
                ON (t1.departments_id = t.t_department_id AND t1.id_lang = '.(int)$id_lang.')
            LEFT OUTER JOIN '._DB_PREFIX_.'fmm_hd_priorities_lang t2
                ON (t2.priorities_id = t.t_priority_id AND t2.id_lang = '.(int)$id_lang.')
            LEFT OUTER JOIN '._DB_PREFIX_.'fmm_hd_ticketstatus_lang t3
                ON (t3.ticketstatus_id = t.t_status_id AND t3.id_lang = '.(int)$id_lang.')
            LEFT OUTER JOIN '._DB_PREFIX_.'fmm_hd_tickets_responses t4
                ON (t4.r_ticket_id = t.ticket_id)
            WHERE t.`t_customer_id` = '.(int)$user_id.'
            GROUP BY t.`ticket_id`
            ORDER BY t.ticket_id DESC');
    }

    public function getUserTicketDetail($ticket_id, $user_id)
    {
        $id_lang = Context::getContext()->language->id;
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
            SELECT t1.ticket_id, t1.t_status_id, t1.ticket_subject, t2.department_title, t3.ticketstatus_title, t1.last_response_staff
            FROM '._DB_PREFIX_.'fmm_hd_tickets t1
            LEFT OUTER JOIN '._DB_PREFIX_.'fmm_hd_departments_lang t2
                ON (t1.t_department_id = t2.departments_id AND t2.id_lang = '.(int)$id_lang.')
            LEFT OUTER JOIN '._DB_PREFIX_.'fmm_hd_ticketstatus_lang t3
                ON (t1.t_status_id = t3.ticketstatus_id AND t3.id_lang = '.(int)$id_lang.')
            WHERE t1.`t_customer_id` = '.(int)$user_id.' and t1.`ticket_id` = '.(int)$ticket_id.'
            GROUP BY t1.`ticket_id`');
    }

    public function getTicketDetails($ticket_id)
    {
        $id_lang = Context::getContext()->language->id;
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
            SELECT t1.*, t2.department_title, t3.ticketstatus_title, t4.priorities_title, CONCAT(`firstname`, \' \', c.`lastname`) AS `customer`, c.email
            FROM '._DB_PREFIX_.'fmm_hd_tickets t1
            LEFT JOIN '._DB_PREFIX_.'fmm_hd_departments_lang t2
                ON (t1.t_department_id = t2.departments_id AND t2.id_lang = '.(int)$id_lang.')
            LEFT JOIN '._DB_PREFIX_.'fmm_hd_ticketstatus_lang t3
                ON (t1.t_status_id = t3.ticketstatus_id AND t3.id_lang = '.(int)$id_lang.')
            LEFT JOIN '._DB_PREFIX_.'fmm_hd_priorities_lang t4
                ON (t1.t_priority_id = t4.priorities_id AND t4.id_lang = '.(int)$id_lang.')
            LEFT JOIN `'._DB_PREFIX_.'customer` c 
                ON (c.`id_customer` = t1.`t_customer_id`)
            WHERE t1.`ticket_id` = '.(int)$ticket_id.'
            GROUP BY t1.`ticket_id`');
    }

    public function getUserTicketResponses($ticket_id)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
            SELECT *
            FROM '._DB_PREFIX_.'fmm_hd_tickets_responses t1
            WHERE t1.`r_ticket_id` = '.(int)$ticket_id.'
            ORDER BY t1.`r_created_time` ASC');
    }

    public function getUserTicketNotes($ticket_id)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
            SELECT *
            FROM '._DB_PREFIX_.'fmm_hd_notes t1
            WHERE t1.`note_ticket_id` = '.(int)$ticket_id.'
            ORDER BY t1.`note_created` ASC');
    }

    public function getDepInfo($dep_id)
    {
        $id_lang = Context::getContext()->language->id;

        return Db::getInstance()->getRow('
            SELECT t1.*, t2.`department_title`
            FROM '._DB_PREFIX_.'fmm_hd_departments t1
            LEFT JOIN '._DB_PREFIX_.'fmm_hd_departments_lang t2
                ON (t1.departments_id = t2.departments_id AND t2.id_lang = '.(int)$id_lang.')
            WHERE t1.`departments_id` = '.(int)$dep_id);
    }

    public function getCustomerInfo($custId)
    {
        return Db::getInstance()->getRow('
            SELECT CONCAT(`firstname`, \' \', c.`lastname`) AS `customer_name`, c.email
            FROM '._DB_PREFIX_.'customer c
            WHERE c.`id_customer` = '.(int)$custId);
    }

    public static function getTicketInfo($ticketId)
    {
        return Db::getInstance()->getRow('
            SELECT *
            FROM '._DB_PREFIX_.'fmm_hd_tickets t
            WHERE t.`ticket_id` = '.(int)$ticketId);
    }

    public static function getRegcus()
    {
        $sql = 'SELECT email, id_customer FROM '._DB_PREFIX_.'customer';
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    }

    public static function insmailresp($ticket_id, $r_message, $ticketPath, $customer_id)
    {
        $sql = 'INSERT INTO '._DB_PREFIX_.'fmm_hd_tickets_responses
        ( 
            r_ticket_id, r_message, r_attachment, r_client_id, r_created_time 
        ) VALUES ( 
            "'.(int)$ticket_id.'",
            "'.pSQL($r_message).'",
            "'.pSQL($ticketPath).'",
            "'.(int)$customer_id.'",
            "'.date('Y-m-d H:i:s').'"
            )';
        return Db::getInstance()->execute($sql);
    }

    public static function sendAdminEmail($ticket, $isReply, $content, $userName, $userEmail)
    {
        $email_templates = Configuration::get('HELPDESK_DEFAULT_EMAIL_TEMPLATE');
        $loadTemplate = Ticketemailtemps::getEmailContent($email_templates);
        if ($userName =="" && $userEmail =="") {
            $userName = Context::getContext()->customer->firstname.' '.Context::getContext()->customer->lastname;
            $userEmail = Context::getContext()->customer->email;
        }
        $search_arr = array('[user]', '[id]', '[email]');
        $replace_arr = array($userName, $ticket, $userEmail);

        if ($isReply == 1) {
            $staff_subject = str_replace($search_arr, $replace_arr, $loadTemplate['new_message_staff_subject']);
            $staff_message = str_replace($search_arr, $replace_arr, $loadTemplate['new_message_staff_message']);
        } else {
            $staff_subject = str_replace($search_arr, $replace_arr, $loadTemplate['new_ticket_staff_subject']);
            $staff_message = str_replace($search_arr, $replace_arr, $loadTemplate['new_ticket_staff_message']);
        }

        $mailSubject = $staff_subject;
        $mailMessage = $staff_message;

        // check if there is need to send email copy
        $email_copies = Configuration::get('HELPDESK_EMAIL_COPY');
        
        // get the default alert email
        $default_alert_email = Configuration::get('HELPDESK_DEFAULT_ALERT_EMAIL');
        
        // get the default alert name
        $default_alert_name = Configuration::get('HELPDESK_DEFAULT_ALERT_NAME');
        if ($default_alert_name == '') {
            $default_alert_name = 'Help Desk';
        }

        $bccEmails = '';
        if (!empty($email_copies)) {
            $bccEmails = $email_copies;
        }

        // To send HTML mail, the Content-type header must be set
        $headers  = 'MIME-Version: 1.0'."\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1'."\r\n";
        $id_lang = Context::getContext()->language->id;
        // Additional headers
        $headers .= 'From: '.$userName.' <'.$userEmail.'>'."\r\n";
        if ($bccEmails != '') {
            $headers .= 'Bcc: '.$bccEmails."\r\n";
        }
        // Mail it
        //mail($default_alert_email, $mailSubject, $mailMessage, $headers);
        $user = array(
        '{userName}' => $userName,
        '{message}' => $content,
        '{ticket_id}' => $ticket
        );
        $mailSubject .= '#'.$ticket;
        $res=Mail::Send((int)$id_lang, 'sendtoadmin', $mailSubject, $user, $default_alert_email, null, $headers, null, $mailMessage, null, _PS_MODULE_DIR_.'helpdesk/mails/', false, 1);
    }

    public function sendUserEmail($ticket, $isReply, $content, $dep_id, $userName, $userEmail)
    {
        // load the email template
        $email_templates = Configuration::get('HELPDESK_DEFAULT_EMAIL_TEMPLATE');
        $loadTemplate = Ticketemailtemps::getEmailContent($email_templates);

        // get user name and user email
        if ($userName=="") {
            $userName = Context::getContext()->customer->firstname.' '.Context::getContext()->customer->lastname;
            $userEmail = Context::getContext()->customer->email;
        }
        // search and replaces array
        $search_arr = array('[user]', '[id]', '[email]');
        $replace_arr = array($userName, $ticket, $userEmail);

        // replacing the tmeplate with search
        if ($isReply == 1) {
            $user_subject = str_replace($search_arr, $replace_arr, $loadTemplate['new_ticket_user_subject']);
            $user_message = str_replace($search_arr, $replace_arr, $loadTemplate['new_ticket_user_message']);
        } else {
            $user_subject = str_replace($search_arr, $replace_arr, $loadTemplate['new_message_user_subject']);
            $user_message = str_replace($search_arr, $replace_arr, $loadTemplate['new_message_user_message']);
        }
        $mailSubject = $user_subject;
        $mailMessage = $user_message;
        //var_dump($mailMessage);exit;
        // Department to send email
        $department = $this->getDepInfo($dep_id);
        $fromName = $department['department_title'];
        $fromEmail = $department['department_email'];
        
        // To send HTML mail, the Content-type header must be set
        $headers  = 'MIME-Version: 1.0'."\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1'."\r\n";
        
        // Additional headers
        $headers .= 'From: '.$fromName.' <'.$fromEmail.'>'."\r\n";
        $id_lang = Context::getContext()->language->id;
        $user = array(
        '{userName}' => $userName,
        '{message}' => $content,
        '{ticket_id}' => $ticket
        );
        // Mail it
        //@mail($userEmail, $mailSubject, $mailMessage, $headers);
        $mailSubject .= '#'.$ticket;
        Mail::Send((int)$id_lang, 'sendtouser', $mailSubject, $user, $userEmail, null, $headers, null, $mailMessage, null, _PS_MODULE_DIR_.'helpdesk/mails/', false, 1);
    }

    public function sendtoDepartmentmail($ticket, $content, $dep_id)
    {
                $userName = Context::getContext()->customer->firstname.' '.Context::getContext()->customer->lastname;
                $ticketdep = new Ticketdepartments();
                $ticketdepinfo=$ticketdep->getDepartmentTitle($dep_id);
                $dep_em=$ticketdepinfo['department_email'];
                $helpdesk = new helpdesk();
                $subject =$helpdesk->l('Ticket generated from '.$userName.' to '.$ticketdepinfo['department_title'].' department');
                $res = Mail::Send(
                    (int) (Configuration::get('PS_LANG_DEFAULT')), // defaut language id
                    'sendtodepartment', // email template file to be use
                    $subject, // email subject
                    array(
                    '{userName}' => $userName,
                    '{message}' => $content,
                    '{ticket_id}' => $ticket
                    ),
                    $dep_em,
                    null,
                    null,
                    null,
                    null,
                    null,
                    _PS_MODULE_DIR_.'helpdesk/mails/',
                    false //from name
                );
    }

    public function sendCloseTicketMail($ticket)
    {
        // load the email template
        $email_templates = Configuration::get('HELPDESK_DEFAULT_EMAIL_TEMPLATE');
        $loadTemplate = Ticketemailtemps::getEmailContent($email_templates);

        // get ticket info
        $ticket_info = $this->getTicketInfo($ticket);

        // get user name and user email
        $customer_data = $this->getCustomerInfo($ticket_info['t_customer_id']);

        $userName = $customer_data['customer_name'];
        $userEmail = $customer_data['email'];

        // search and replaces array
        $search_arr = array('[user]', '[id]', '[email]');
        $replace_arr = array($userName, $ticket, $userEmail);

        // replacing the tmeplate with search
        $user_subject = str_replace($search_arr, $replace_arr, $loadTemplate['close_ticket_user_subject']);
        $user_message = str_replace($search_arr, $replace_arr, $loadTemplate['close_ticket_user_message']);

        $mailSubject = $user_subject;
        $sendToEmail = $userEmail;
        $mailMessage = $user_message;
        //var_dump($mailMessage);exit;
        // Department to send email
        $department = $this->getDepInfo($ticket_info['t_department_id']);
        $fromName = $department['department_title'];
        $fromEmail = $department['department_email'];
        // To send HTML mail, the Content-type header must be set
        $headers  = 'MIME-Version: 1.0'."\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1'."\r\n";

        // Additional headers
        $headers .= 'From: '.$fromName.' <'.$fromEmail.'>'."\r\n";
        $id_lang = Context::getContext()->language->id;
        $user = array(
        '{userName}' => $userName,
        );
        // Mail it
        //@mail($sendToEmail, $mailSubject, $mailMessage, $headers);
        $mailSubject .= '#'.$ticket;
        Mail::Send((int)$id_lang, 'sendtouser', $mailSubject, $user, $sendToEmail, null, $headers, null, $mailMessage, null, _PS_MODULE_DIR_.'helpdesk/mails/', false, 1);
    }

    public function sendNewReplyMail($ticket, $reply_message)
    {
        // load the email template
        $email_templates = Configuration::get('HELPDESK_DEFAULT_EMAIL_TEMPLATE');
        $loadTemplate = Ticketemailtemps::getEmailContent($email_templates);

        // get ticket info
        $ticket_info = $this->getTicketInfo($ticket);
        $attach_link = $this->getAttachmentUrl($ticket);
        $attach_link = empty($attach_link) ? '' : _PS_BASE_URL_.__PS_BASE_URI__.'img/'.$attach_link;
        // get user name and user email
        $customer_data = $this->getCustomerInfo($ticket_info['t_customer_id']);
        $userName = $customer_data['customer_name'];
        $userEmail = $customer_data['email'];

        // search and replaces array
        $search_arr = array('[user]', '[id]', '[email]');
        $replace_arr = array($userName, $ticket, $userEmail);

        // replacing the tmeplate with search
        $user_subject = str_replace($search_arr, $replace_arr, $loadTemplate['new_reply_user_subject']);
        $user_message = str_replace($search_arr, $replace_arr, $loadTemplate['new_reply_user_message']);

        $mailSubject = $user_subject;

        $sendToEmail = $userEmail;
        $mailMessage = $user_message;
        // Department to send email
        $department = $this->getDepInfo($ticket_info['t_department_id']);
        $fromName = $department['department_title'];
        $fromEmail = $department['department_email'];

        // To send HTML mail, the Content-type header must be set
        $headers  = 'MIME-Version: 1.0'."\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1'."\r\n";

        // Additional headers
        $headers .= 'From: '.$fromName.' <'.$fromEmail.'>'."\r\n";
        $id_lang = Context::getContext()->language->id;
        $user = array(
        '{userName}' => $userName,
        '{message}' => $reply_message,
        '{ticket_id}' => $ticket,
        '{attach}' => $attach_link
        );
        $mailSubject .= '#'.$ticket;
        Mail::Send((int)$id_lang, 'sendtouser', $mailSubject, $user, $sendToEmail, null, $headers, null, $mailMessage, null, _PS_MODULE_DIR_.'helpdesk/mails/', false, 1);
    }

    public static function getAttachmentUrl($id)
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
        SELECT *
        FROM `'._DB_PREFIX_.'fmm_hd_tickets_responses`
        WHERE `r_ticket_id` = '.(int)$id.'
        ORDER BY `response_id` DESC');
        return $result['r_attachment'];
    }
}
