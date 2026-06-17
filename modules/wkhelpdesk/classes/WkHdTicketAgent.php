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

class WkHdTicketAgent extends ObjectModel
{
    public $id;
    public $employee_id;
    public $name;
    public $email;
    public $is_super_admin;
    public $active;
    public $date_add;

    public static $definition = array(
        'table' => 'wk_hd_ticket_agent',
        'primary' => 'id',
        'fields' => array(
            'employee_id' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'name' => array('type' => self::TYPE_STRING, 'validate' => 'isName', 'required' => true, 'size' => 256),
            'email' => array('type' => self::TYPE_STRING, 'validate' => 'isEmail', 'required' => true, 'size' => 128),
            'is_super_admin' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false)
        )
    );

    public function __construct($id = null, $idLang = null, $idShop = null)
    {
        parent::__construct($id, $idLang, $idShop);
        Shop::addTableAssociation('wk_hd_ticket_agent', array('type' => 'shop', 'primary' => 'id'));
    }

    public function delete()
    {
        if ($this->id) {
            $objAccessRightMapping = new WkHdAccessRightMapping();
            $deleted = $objAccessRightMapping->deleteAccessRightByIdAgent($this->id);
            if ($deleted && parent::delete()) {
                return true;
            }
        }

        return false;
    }

    public function validateEmployee($employee)
    {
        return Db::getInstance()->getRow(
            'SELECT g.`employee_id` FROM `'._DB_PREFIX_.'wk_hd_ticket_agent` g'
            . WkHdGroup::addSqlAssociationCustom('wk_hd_ticket_agent', 'g') . ' WHERE `is_super_admin` = 1
            AND g.`employee_id` = '.(int) $employee->id.' GROUP BY g.`id`'
        );
    }

    public function getSuperAdminInfo()
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'wk_hd_ticket_agent` g'
            . WkHdGroup::addSqlAssociationCustom('wk_hd_ticket_agent', 'g') . '
            WHERE g.`is_super_admin` = 1 GROUP BY g.`id`'
        );
    }

    public function getEmployeeWhoAreNotAgent()
    {
        return Db::getInstance()->executeS(
            'SELECT eg.* FROM `'._DB_PREFIX_.'employee` eg
            WHERE eg.`id_employee` NOT IN
            (SELECT g.`employee_id` FROM `'._DB_PREFIX_.'wk_hd_ticket_agent` g'
            . WkHdGroup::addSqlAssociationCustom('wk_hd_ticket_agent', 'g') . ' GROUP BY g.`id`) AND eg.active = 1'
        );
    }

    public function getAgentInfoById($id)
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'wk_hd_ticket_agent` g'
            .WkHdGroup::addSqlAssociationCustom('wk_hd_ticket_agent', 'g')
            .' WHERE g.`id` = '.(int) $id.' GROUP BY g.`id`'
        );
    }

    public function getAgentInfoByIdEmployee($employee_id)
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'wk_hd_ticket_agent` g'
            .WkHdGroup::addSqlAssociationCustom('wk_hd_ticket_agent', 'g')
            .' WHERE g.`employee_id` = '.(int) $employee_id.' GROUP BY g.`id`'
        );
    }

    public function getAllAgent($superAdmin = false)
    {
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'wk_hd_ticket_agent` g'
        . WkHdGroup::addSqlAssociationCustom('wk_hd_ticket_agent', 'g');
        if (!$superAdmin) {
            $sql .= ' WHERE g.`is_super_admin` = 0';
        }
        $sql .= ' GROUP BY g.`id`';

        return Db::getInstance()->executeS($sql);
    }

    public function getAgentListExceptId($id)
    {
        return Db::getInstance()->executeS(
            'SELECT * FROM `'._DB_PREFIX_.'wk_hd_ticket_agent` g'
            . WkHdGroup::addSqlAssociationCustom('wk_hd_ticket_agent', 'g') . '
            WHERE g.`id` != '.(int) $id.'
            AND g.`active` = 1 GROUP BY g.`id`'
        );
    }
    // public function getAgentListExceptId_bkp($id)
    // {
    //     return Db::getInstance()->executeS(
    //         'SELECT * FROM `'._DB_PREFIX_.'wk_hd_ticket_agent` g'
    //         . WkHdGroup::addSqlAssociationCustom('wk_hd_ticket_agent', 'g') . '
    //         WHERE g.`id` != '.(int) $id.'
    //         AND g.`is_super_admin` = 0
    //         AND g.`active` = 1 GROUP BY g.`id`'
    //     );
    // }

    public function getAgentNameById($id)
    {
        $name = Db::getInstance()->getValue(
            'SELECT g.`name` FROM `'._DB_PREFIX_.'wk_hd_ticket_agent` g'
            .WkHdGroup::addSqlAssociationCustom('wk_hd_ticket_agent', 'g')
            .' WHERE g.`id` = '.(int) $id.' GROUP BY g.`id`'
        );

        /* return name if found else return 'not_found'. Do not remove return
        'not_found' because there is a case to test 'not_found'*/
        if ($name) {
            return $name;
        } else {
            return 'not_found';
        }
    }

    public function assignMailToAgent($mailVars, $idLang)
    {
        $temp_path = _PS_MODULE_DIR_.'wkhelpdesk/mails/';
        Mail::Send(
            (int) $idLang,
            'ticket_assign_mail_to_agent',
            Mail::l('New ticket assigned to you', (int) $idLang),
            $mailVars,
            $mailVars['{email}'],
            $mailVars['{name}'],
            null,
            null,
            null,
            null,
            $temp_path,
            false,
            null,
            null
        );
    }

    public function customerReplyToAgent($ticketParams, $isNewMsgAlert = false)
    {
        $obj_ticket = new WkHdTicket();
        $ticketInfo = $obj_ticket->getInfoById($ticketParams['{ticket_id}']);
        if ($ticketInfo) {
            $objGroupQueryMapping = new WkHdGroupQueryTypeMapping();
            $mappingInfo = $objGroupQueryMapping->getInfoByIdQueryType($ticketInfo['id_query_type']);
            $objTicketAgent = new WkHdTicketAgent();
            if ($mappingInfo) {
                $objGroupAgentMapping = new WkHdGroupAgentMapping();
                $agentsInfo = $objGroupAgentMapping->getMappedAgentInfoByIdGroup($mappingInfo['id_group']);
                if ($ticketInfo['assigned_agent_id'] && $isNewMsgAlert) {
                    $assignedAgentInfo = $objTicketAgent->getAgentInfoById($ticketInfo['assigned_agent_id']);
                    $count = count($agentsInfo);
                    if (!$count) {
                        $count = 0;
                    }
                    $agentsInfo[$count] = $assignedAgentInfo;
                }

                if ($agentsInfo) {
                    //send mail to all mapped agents
                    foreach ($agentsInfo as $agent) {
                        $ticketParams['{agent_name}'] = $agent['name'];
                        $ticketParams['{agent_email}'] = $agent['email'];
                        $agentObj = new Employee();
                        $agentEmployee = $agentObj->getByEmail($agent['email']);
                        if ($isNewMsgAlert) {
                            $objTicketAgent->newMessageAlertMail($ticketParams, $agentEmployee->id_lang);
                        } else {
                            $objTicketAgent->newTicketAlertMail($ticketParams, $agentEmployee->id_lang);
                        }
                    }
                } else { // else send mail to super admin
                    $agent = $objTicketAgent->getSuperAdminInfo();
                    $ticketParams['{agent_name}'] = $agent['name'];
                    $ticketParams['{agent_email}'] = $agent['email'];
                    if ($isNewMsgAlert) {
                        $objTicketAgent->newMessageAlertMail($ticketParams);
                    } else {
                        $objTicketAgent->newTicketAlertMail($ticketParams);
                    }
                }
            } elseif ($ticketInfo['assigned_agent_id'] && $isNewMsgAlert) {
                // else check assigned agent
                $agent_info = $objTicketAgent->getAgentInfoById($ticketInfo['assigned_agent_id']);
                if ($agent_info) {
                    $agentObj = new Employee((int) $agent_info['employee_id']);
                    $ticketParams['{agent_name}'] = $agent_info['name'];
                    $ticketParams['{agent_email}'] = $agent_info['email'];
                    $objTicketAgent->newMessageAlertMail($ticketParams, $agentObj->id_lang);
                }
            } else { // else send mail to super admin
                $agent = $objTicketAgent->getSuperAdminInfo();
                $ticketParams['{agent_name}'] = $agent['name'];
                $ticketParams['{agent_email}'] = $agent['email'];
                if ($isNewMsgAlert) {
                    $objTicketAgent->newMessageAlertMail($ticketParams);
                } else {
                    $objTicketAgent->newTicketAlertMail($ticketParams);
                }
            }
        }
    }

    public function newTicketAlertMail($mailVars, $idLang = false)
    {
        $objQueryType = new WkHdQueryType();
        $queryTypeInfo = $objQueryType->getQueryInfoById(
            $mailVars['{id_query_type}'],
            $mailVars['{id_lang}']
        );
        $mailVars['{query_type}'] = $queryTypeInfo['query_name'];
        if (!$idLang) {
            $idLang = Configuration::get('PS_LANG_DEFAULT'); // mail in default lang for admin
        }
        $mailVars['{isCustomer}'] = 'Admin';

        $templatePath = _PS_MODULE_DIR_.'wkhelpdesk/mails/';
        Mail::Send(
            (int) $idLang,
            'new_ticket_alert_to_agent',
            Mail::l('New ticket alert', (int) $idLang),
            $mailVars,
            $mailVars['{agent_email}'],
            $mailVars['{agent_name}'],
            null,
            null,
            null,
            null,
            $templatePath,
            false,
            null,
            null
        );
    }

    public function newMessageAlertMail($mailVars, $idLang = false)
    {
        if (!$idLang) {
            $idLang = Configuration::get('PS_LANG_DEFAULT'); // mail in default lang for admin
        }
        $templatePath = _PS_MODULE_DIR_.'wkhelpdesk/mails/';
        Mail::Send(
            (int) $idLang,
            'new_message_alert_to_agent',
            Mail::l('New message alert', (int) $idLang),
            $mailVars,
            $mailVars['{agent_email}'],
            $mailVars['{agent_name}'],
            null,
            null,
            null,
            null,
            $templatePath,
            false,
            null,
            null
        );
    }

    public function createTicketMailToCustomer($mailVars)
    {
        $objQueryType = new WkHdQueryType();
        $queryTypeInfo = $objQueryType->getQueryInfoById(
            $mailVars['{id_query_type}'],
            $mailVars['{id_lang}']
        );
        $templatePath = _PS_MODULE_DIR_.'wkhelpdesk/mails/';
        $mailVars['{query_type}'] = $queryTypeInfo['query_name'];

        Mail::Send(
            (int) $mailVars['{id_lang}'],
            'create_ticket_mail_to_customer',
            Mail::l('New Ticket', (int) $mailVars['{id_lang}'].' Tkt#'.$mailVars['{ticket_id}']),
            $mailVars,
            $mailVars['{email}'],
            $mailVars['{customer_name}'],
            null,
            null,
            null,
            null,
            $templatePath,
            false,
            null,
            null
        );
    }
}
