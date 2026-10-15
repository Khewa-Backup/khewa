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

class WkHdTicketToken extends ObjectModel
{
    public $id;
    public $hd_id_ticket;
    public $token;
    public $date_add;

    public static $definition = array(
        'table' => 'wk_hd_ticket_token',
        'primary' => 'id',
        'fields' => array(
            'hd_id_ticket' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'token' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false)
        )
    );

    public function __construct($id = null, $idLang = null, $idShop = null)
    {
        parent::__construct($id, $idLang, $idShop);
        Shop::addTableAssociation('wk_hd_ticket_token', array('type' => 'shop', 'primary' => 'id'));
    }

    public function getTokenByIdTicket($idTicket)
    {
        return Db::getInstance()->getValue(
            'SELECT wtt.`token` FROM `'._DB_PREFIX_.'wk_hd_ticket_token` wtt '
            .WkHdGroup::addSqlAssociationCustom('wk_hd_ticket_attachment', 'wtt').'
            WHERE wtt.`hd_id_ticket` = '.(int) $idTicket.' GROUP BY wtt.`id`'
        );
    }

    // Customization By Ram Chandra
    public static function syncImap()
    {
        $modObj = Module::getInstanceByName('wkhelpdesk');
        $context = Context::getContext();
        if (!($url = Configuration::get('PS_SAV_IMAP_URL'))
            || !($port = Configuration::get('PS_SAV_IMAP_PORT'))
            || !($user = Configuration::get('PS_SAV_IMAP_USER'))
            || !($password = Configuration::get('PS_SAV_IMAP_PWD'))) {
            return array(
                'hasError' => true,
                'errors' => $modObj->l('IMAP configuration is not correct', 'WkHdTicketToken')
            );
        }

        $conf = Configuration::getMultiple(array(
            'PS_SAV_IMAP_OPT_NORSH', 'PS_SAV_IMAP_OPT_SSL',
            'PS_SAV_IMAP_OPT_VALIDATE-CERT', 'PS_SAV_IMAP_OPT_NOVALIDATE-CERT',
            'PS_SAV_IMAP_OPT_TLS', 'PS_SAV_IMAP_OPT_NOTLS'));

        $conf_str = '';
        if ($conf['PS_SAV_IMAP_OPT_NORSH']) {
            $conf_str .= '/norsh';
        }
        if ($conf['PS_SAV_IMAP_OPT_SSL']) {
            $conf_str .= '/ssl';
        }
        if ($conf['PS_SAV_IMAP_OPT_VALIDATE-CERT']) {
            $conf_str .= '/validate-cert';
        }
        if ($conf['PS_SAV_IMAP_OPT_NOVALIDATE-CERT']) {
            $conf_str .= '/novalidate-cert';
        }
        if ($conf['PS_SAV_IMAP_OPT_TLS']) {
            $conf_str .= '/tls';
        }
        if ($conf['PS_SAV_IMAP_OPT_NOTLS']) {
            $conf_str .= '/notls';
        }

        if (!function_exists('imap_open')) {
            return array(
                'hasError' => true,
                'errors' => $modObj->l('imap is not installed on this server', 'WkHdTicketToken')
            );
        }

        $mbox = @imap_open('{'.$url.':'.$port.$conf_str.'}INBOX', $user, $password);
        //checks if there is no error when connecting imap server
        $errors = imap_errors();
        if (is_array($errors)) {
            $errors = array_unique($errors);
        }
        $str_errors = '';
        $str_error_delete = '';

        if (is_array($errors) && count($errors)) {
            $str_errors = '';
            foreach ($errors as $error) {
                $str_errors .= $error.', ';
            }
            $str_errors = rtrim(trim($str_errors), ',');
        }

        //checks if imap connexion is active
        if (!$mbox) {
            return array(
                'hasError' => true,
                'errors' => $modObj->l('Cannot connect to the mailbox', 'WkHdTicketToken').
                ':<br />'.($str_errors)
            );
        }

        //Returns information about the current mailbox. Returns FALSE on failure.
        $check = imap_check($mbox);
        if (!$check) {
            return array(
                'hasError' => true,
                'errors' => $modObj->l('Fail to get information about the current mailbox', 'WkHdTicketToken')
            );
        }
        if ($check->Nmsgs == 0) {
            return array('hasError' => true, 'errors' => $modObj->l('No message to sync', 'WkHdTicketToken'));
        }

        $result = imap_fetch_overview($mbox, "1:{$check->Nmsgs}", 0);

        foreach ($result as $overview) {
            //check if message exist in database
            if (isset($overview->subject)) {
                $subject = trim($overview->subject);
            } else {
                $subject = '';
            }

            //Creating an md5 to check if message has been allready processed
            $md5 = md5($overview->date.$overview->from.$subject.$overview->msgno);
            $exist = Db::getInstance()->getValue(
                'SELECT `md5_header`
						 FROM `'._DB_PREFIX_.'wk_customer_message_sync_imap`
						 WHERE `md5_header` = \''.pSQL($md5).'\''
            );
            if ($exist) {
                // if (Configuration::get('PS_SAV_IMAP_DELETE_MSG')) {
                //     if (!imap_delete($mbox, $overview->msgno)) {
                //         $str_error_delete = ', Fail to delete message';
                //     }
                // }
            } else {
                if (strpos($overview->from, '<')) {
                    $email = explode('>', explode('<', $overview->from)[1])[0];
                } else {
                    $email = $overview->from;
                }
                $from = $email;
                if (!preg_match(
                    '/('.Tools::cleanNonUnicodeSupport(
                        '[a-z\p{L}0-9!#$%&\'*+\/=?^`{}|~_-]+[.a-z\p{L}0-9!#$%&\'*+\/=?^`{}|~_-]'.
                        '*@[a-z\p{L}0-9]+[._a-z\p{L}0-9-]*\.[a-z0-9]+'
                    ).')/',
                    $from,
                    $result
                ) || !Validate::isEmail($from)
                ) {
                    continue;
                }

                error_log(
                    json_encode((array) $overview).PHP_EOL.PHP_EOL,
                    3,
                    _PS_MODULE_DIR_.'wkhelpdesk/imapcron.log'
                );

                // on reply code
                $isReply = 0;
                if (!isset($overview->in_reply_to) && !isset($overview->references)) {
                    $wkReply = new WkCustomerReplySyncImap();
                    $wkReply->message_id = pSQL(Tools::substr(trim($overview->message_id), 1, -1));
                    $wkReply->reply_id = '';
                    $wkReply->save();
                    $idReplyImap = $wkReply->id;
                } else {
                    $wkReply = new WkCustomerReplySyncImap();
                    $wkMessageId = $wkReply->getMessageId(Tools::substr(trim($overview->in_reply_to), 1, -1));

                    $tktIdInSubject = 0;
                    preg_match('/Tkt#[0-9]*/', $subject, $matches1);
                    if (!empty($matches1)) {
                        $tktIdInSubject = (int) str_replace('Tkt#', '', $matches1[0]);
                    }
                    if (($tktIdInSubject > 0) && !$wkMessageId) {
                        $wkMessageId = array('id_ticket' => $tktIdInSubject);
                    }

                    if ($wkMessageId) {
                        $wkReply->id_ticket = (int) $wkMessageId['id_ticket'];
                        $wkReply->message_id = pSQL(Tools::substr(trim($overview->message_id), 1, -1));
                        $wkReply->reply_id = pSQL(Tools::substr(trim($overview->in_reply_to), 1, -1));
                        $wkReply->save();
                        $isReply = 1;
                    } else {
                        $wkReply = new WkCustomerReplySyncImap();
                        $wkReply->message_id = pSQL(Tools::substr(trim($overview->message_id), 1, -1));
                        $wkReply->reply_id = '';
                        $wkReply->save();
                        $idReplyImap = $wkReply->id;
                    }
                }

                // reply code end
                $idCustomer = 0;
                $objHdCustomer = new WkHdCustomer();
                $hdCustomer = $objHdCustomer->getCustomerByEmail($from);
                $firstname = 'Guest';
                $lastname = 'user';
                if ($hdCustomer) {
                    $hdIdCustomer = $hdCustomer['id'];
                    $idCustomer = $hdCustomer['id_ps_customer'];
                    $firstname = $hdCustomer['first_name'];
                    $lastname = $hdCustomer['last_name'];
                } else {
                    $obj_customer = new Customer();
                    $customer_info = $obj_customer->getByEmail($from);
                    if ($customer_info) {
                        $idCustomer = $customer_info->id;
                        $objHdCustomer->id_ps_customer = (int) $customer_info->id;
                        $firstname = $customer_info->firstname;
                        $lastname = $customer_info->lastname;
                    } else {
                        $objHdCustomer->id_ps_customer = (int) 0;
                    }
                    $objHdCustomer->first_name = pSQL($firstname);
                    $objHdCustomer->last_name = pSQL($lastname);
                    $objHdCustomer->email = pSQL($from);
                    $objHdCustomer->save();
                    $hdIdCustomer = $objHdCustomer->id;
                }

                // reply code
                if ($isReply) {
                    $ticketId = $wkMessageId['id_ticket'];
                    $objStatusMapping = new WkHdStatusMapping();
                    $objTicket = new WkHdTicket((int) $ticketId);
                    $prevStatus = (int) $objTicket->id_status;
                    $objTicket->id_status = (int) 1;
                    $objTicket->save();

                    $closedIdStatus = $objStatusMapping->getMappedStatusIdByStatus('closed');
                    if ($closedIdStatus == $prevStatus) { // if ticket is closed add system note to open ticket
                        $objTicket_msg = new WkHdTicketMsg();
                        $objTicket_msg->hd_id_ticket = (int) $ticketId;
                        $objTicket_msg->message = '';
                        $objTicket_msg->id_customer = (int) 0;
                        $objTicket_msg->id_agent = (int) 0;
                        $objTicket_msg->is_internal_note = (int) 0;
                        $objTicket_msg->is_status_update = (int) 1;
                        $objTicket_msg->status_from = (int) $prevStatus;
                        $objTicket_msg->status_to = (int) 1;
                        $objTicket_msg->is_agent_assign = (int) 0;
                        $objTicket_msg->agent_from = (int) 0;
                        $objTicket_msg->agent_to = (int) 0;
                        $objTicket_msg->save();
                    }

                    $message = imap_fetchbody($mbox, $overview->msgno, 1.1);
                    if ($message == '') {
                        $message = imap_fetchbody($mbox, $overview->msgno, 1);
                    }
                    $message = self::cleanBody($message);
                    $message = quoted_printable_decode($message);
                    // $message = utf8_encode($message);
                    // $message = quoted_printable_decode($message);

                    $message = nl2br($message);

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
                    $objTicket_msg->id;
                } else {
                    // new ticket
                    $objTicket = new WkHdTicket();
                    $objTicket->hd_id_customer = (int) $hdIdCustomer;
                    $queryType = 0;
                    $objQueryType = new WkHdQueryType();
                    $queryTypes = $objQueryType->getAllQueryType();
                    if (!empty($queryTypes)) {
                        $queryType = (int) $queryTypes[0]['id'];
                    }
                    $objTicket->first_name = pSQL($firstname);
                    $objTicket->last_name = pSQL($lastname);
                    $objTicket->id_query_type = (int) $queryType;
                    $objTicket->assigned_agent_id = (int) 0;
                    $objStatusMapping = new WkHdStatusMapping();
                    $objTicket->id_status = (int) 1;
                    $objTicket->subject = pSQL($subject);
                    $objTicket->id_order = (int) 0;
                    $objTicket->save();
                    $ticketId = $objTicket->id;

                    if ($ticketId) {
                        $message = imap_fetchbody($mbox, $overview->msgno, 1.1);
                        if (empty($message)) {
                            $message = imap_fetchbody($mbox, $overview->msgno, 1);
                        }
                        $message = self::cleanBody($message);
                        $message = quoted_printable_decode($message);
                        // $message = utf8_encode($message);
                        // $message = quoted_printable_decode($message);
                        $message = nl2br($message);
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

                        //update ticket id in Imap reply table
                        $wkReplyIdObj = new WkCustomerReplySyncImap((int) $idReplyImap);
                        $wkReplyIdObj->id_ticket = (int) $ticketId;
                        $wkReplyIdObj->save();

                        //confirmation mail to customer
                        $ticketParams = array(
                            '{ticket_link}' => $ticketLink,
                            '{customer_name}' => $firstname.' '.$lastname,
                            '{subject}' => $subject,
                            '{message}' => $message,
                            '{email}' => $from,
                            '{id_lang}' => $context->language->id,
                            '{id_query_type}' => $queryType,
                            '{ticket_id}' => $ticketId,
                        );

                        $objTicketAgent = new WkHdTicketAgent();
                        if (Configuration::get('WK_HD_NEW_TICKET_CUSTOMER_NOTIFICATON')) {
                            $objTicketAgent->createTicketMailToCustomer($ticketParams); //confirmation mail to customer
                        }
                        if (Configuration::get('WK_HD_NEW_TICKET_AGENT_NOTIFICATON')) {
                            $objTicketAgent->customerReplyToAgent($ticketParams); // mail to agents
                        }
                    }
                }
                Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'wk_customer_message_sync_imap` (`md5_header`)
                VALUES (\''.pSQL($md5).'\')');
            }
        }
        imap_expunge($mbox);
        imap_close($mbox);
        if ($str_errors.$str_error_delete) {
            return array('hasError' => true, 'errors' => $str_errors.$str_error_delete);
        } else {
            return array('hasError' => false, 'errors' => '');
        }
        return array('hasError' => false, 'errors' => '');
    }
    // END


    public static function is_base64Encoded($s){
        if (!preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $s)) {
            return false;
        }
        // $decoded = base64_decode($s, true);
        // if (false === $decoded) {
        //     return false;
        // }

        // // Encode the string again
        // if (base64_encode($decoded) != $s) {
        //     return false;
        // }

        return true;
    }

    public static function cleanBody($message)
    {
        if (self::is_base64Encoded($message)) {
            $message = base64_decode($message);
        }
        $doc = new DOMDocument();
        $doc->loadHTML(mb_convert_encoding($message, 'HTML-ENTITIES', 'UTF-8'));

        // we retrieve the chapter and remove it from the book
        $chapter = $doc->documentElement->getElementsByTagName('blockquote');
        $flag = false;
        for ($i = $chapter->length; --$i >= 0;) {
            $el = $chapter->item($i);
            if (!$flag) {
                $flag = true;
                $prevNode = $el->previousSibling;
                if (strlen(trim($prevNode->textContent)) == 0) {
                    $prevNode = $prevNode->previousSibling;
                }
                if ($prevNode->nodeName == 'div') {
                    $el->parentNode->removeChild($prevNode);
                }
            }
            switch ($el->getAttribute('type')) {
                case 'cite':
                    $el->parentNode->removeChild($el);
                    break;
                    //break;
            }
        }
        return $doc->textContent;
    }

}
