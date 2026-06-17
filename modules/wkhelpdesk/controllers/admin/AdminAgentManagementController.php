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

class AdminAgentManagementController extends ModuleAdminController
{
    public function __construct()
    {
        $this->context = Context::getContext();
        $this->table = 'wk_hd_ticket_agent';
        $this->className = 'WkHdTicketAgent';
        $this->bootstrap = true;
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->identifier = 'id';
        parent::__construct();
        $this->toolbar_title = $this->l('Agents');
        if (Shop::getContext() == Shop::CONTEXT_SHOP) {
            Shop::addTableAssociation('wk_hd_ticket_agent', array('type' => 'shop', 'primary' => 'id'));
        }
        $this->_join .= WkHdGroup::addSqlAssociationCustom('wk_hd_ticket_agent', 'a', false);
        $this->_group = ' GROUP BY a.id';
        $this->_where .= ' AND a.`is_super_admin` != 1';
        $this->_select = "a.`id` as `temp_id`";

        $objHdEmployee = new WkHdTicketAgent();
        if ($objHdEmployee->validateEmployee($this->context->employee)) {
            $this->fields_list = array(
                'id' => array(
                    'title' => $this->l('ID'),
                    'align' => 'center',
                    'class' => 'fixed-width-xs',
                ),
                'employee_id' => array(
                    'title' => $this->l('Employee Id'),
                    'align' => 'center',
                    'hint' => $this->l('Prestashop Employee ID'),
                ),
                'name' => array(
                    'title' => $this->l('Agent name'),
                    'align' => 'center',
                ),
                'email' => array(
                    'title' => $this->l('Agent email'),
                    'align' => 'center',
                ),
                'active' => array(
                    'title' => $this->l('Status'),
                    'active' => 'status',
                    'type' => 'bool',
                    'orderby' => false,
                    'align' => 'center',
                ),
                'temp_id' => array(
                    'title' => $this->l('View access rights'),
                    'align' =>'center',
                    'search' => false,
                    'callback' => 'viewAccessRights',
                ),
                'date_add' => array(
                    'title' => $this->l('Add date'),
                    'align' => 'text-right',
                    'type' => 'datetime',
                    'filter_key' => 'a!date_add'
                )
            );
        } else {
            $this->errors[] = $this->l('You do not have access right to view this page.');
        }

        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected items?'),
            ),
        );
    }

    public function viewAccessRights($id)
    {
        $this->context->smarty->assign(
            'access_link',
            self::$currentIndex.'&token='.$this->token.'&viewwk_hd_ticket_agent&id='.$id
        );

        return $this->module->display(_PS_MODULE_DIR_.'wkhelpdesk', 'viewaccessright.tpl');
    }

    public function initToolbar()
    {
        $objHdEmployee = new WkHdTicketAgent();
        if ($objHdEmployee->validateEmployee($this->context->employee)) {
            parent::initToolbar();
            $this->page_header_toolbar_btn['new'] = array(
                'href' => self::$currentIndex.'&add'.$this->table.'&token='.$this->token,
                'desc' => $this->l('Add new agent'),
            );
        }
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

    public function renderForm()
    {
        if (($this->display == 'edit') && (Shop::getContext() != Shop::CONTEXT_SHOP)) {
            return $this->context->smarty->fetch(
                _PS_MODULE_DIR_.$this->module->name.'/views/templates/admin/_partials/shop_warning.tpl'
            );
        } else {
            $objHdEmployee = new WkHdTicketAgent();
            if ($objHdEmployee->validateEmployee($this->context->employee)) {
                // get all access rights
                $allAccessRight = WkHdAccessRight::getAllAccessRights();
                $this->context->smarty->assign('allAccessRight', $allAccessRight);

                // get all employees who are not agent
                if ($this->display == 'add') {
                    $employees = $objHdEmployee->getEmployeeWhoAreNotAgent();
                    if ($employees) {
                        $this->context->smarty->assign('employees', $employees);
                    }
                } elseif ($this->display == 'edit') {
                    $idAgent = Tools::getValue('id');
                    $objTicketAgent = new WkHdTicketAgent((int) $idAgent);
                    if ($objTicketAgent->is_super_admin) {
                        $this->errors[] = $this->l('Super admin access right can not change.');
                    }

                    // get agent information
                    $agentInfo = $objTicketAgent->getAgentInfoById($idAgent);
                    if ($agentInfo) {
                        $objAccessRightMapping = new WkHdAccessRightMapping();
                        $mappedAccessRights = $objAccessRightMapping->getAccessRightByIdAgent($idAgent);

                        $this->context->smarty->assign('agentInfo', $agentInfo);
                        $this->context->smarty->assign('mappedAccessRights', $mappedAccessRights);
                    } else {
                        $this->errors[] = $this->l('Agent information not found.');
                    }
                }

                $this->fields_form = array(
                    'submit' => array(
                        'title' => $this->l('Save'),
                    ),
                );

                return parent::renderForm();
            } else {
                $this->errors[] = $this->l('You do not have access right to view this page.');
            }
        }
    }

    public function renderView()
    {
        $objHdEmployee = new WkHdTicketAgent();
        if ($objHdEmployee->validateEmployee($this->context->employee)) {
            $idAgent = Tools::getValue('id');
            if ($idAgent) {
                //get agent information
                $objTicketAgent = new WkHdTicketAgent();
                $agentInfo = $objTicketAgent->getAgentInfoById($idAgent);
                if ($agentInfo) {
                    //get agent access rights
                    $objAccessRightMapping = new WkHdAccessRightMapping();
                    $mappedAccessRights = $objAccessRightMapping->getAccessRightByIdAgent($idAgent);

                    $this->context->smarty->assign('mappedAccessRights', $mappedAccessRights);
                    $this->context->smarty->assign('agentInfo', $agentInfo);
                } else {
                    $this->errors[] = $this->l('Agent information not found.');
                }
            } else {
                $this->errors[] = $this->l('Agent information not found.');
            }

            return parent::renderView();
        } else {
            $this->errors[] = $this->l('You do not have access right to view this page.');
        }
    }

    public function processSave()
    {
        $objHdEmployee = new WkHdTicketAgent();
        if ($objHdEmployee->validateEmployee($this->context->employee)) {
            $idAgent = Tools::getValue('id');
            $idEmployee = Tools::getValue('idEmployee');
            $selectedAccessRight = Tools::getValue('accessRight');

            if ($idAgent) {
                $objTicketAgent = new WkHdTicketAgent((int) $idAgent);
                if ($objTicketAgent->is_super_admin) {
                    $this->errors[] = $this->l('Super admin access right can not change.');
                }
            }

            if (empty($selectedAccessRight)) {
                $this->errors[] = $this->l('Please select at least one access right.');
            }

            if (empty($this->errors)) {
                // first delete all access rights mapping
                if ($idAgent) {
                    $objAccessRightMapping = new WkHdAccessRightMapping();
                    $objAccessRightMapping->deleteAccessRightByIdAgent($idAgent);
                // add new agent
                } else {
                    $objEmployee = new Employee($idEmployee);
                    $objTicketAgent = new WkHdTicketAgent();
                    $objTicketAgent->employee_id = (int) $idEmployee;
                    $objTicketAgent->name = pSQL($objEmployee->firstname).' '.pSQL($objEmployee->lastname);
                    $objTicketAgent->email = pSQL($objEmployee->email);
                    $objTicketAgent->is_super_admin = (int) 0;
                    $objTicketAgent->active = (int) 1;
                    $objTicketAgent->save();
                    $idAgent = $objTicketAgent->id;
                }

                // save access right mapping
                foreach ($selectedAccessRight as $accessRight) {
                    $objAccessRightMapping = new WkHdAccessRightMapping();
                    $objAccessRightMapping->id_agent = (int) $idAgent;
                    $objAccessRightMapping->id_access_right = (int) $accessRight;
                    $objAccessRightMapping->save();
                }

                if (Tools::isSubmit('submitAdd'.$this->table.'AndStay')) {
                    if ($idEmployee) {
                        Tools::redirectAdmin(
                            self::$currentIndex.'&id='.(int) $idAgent.'&update'.
                            $this->table.'&conf=3&token='.$this->token
                        );
                    } else {
                        Tools::redirectAdmin(
                            self::$currentIndex.'&id='.(int) $idAgent.'&update'.
                            $this->table.'&conf=4&token='.$this->token
                        );
                    }
                } else {
                    if ($idEmployee) {
                        Tools::redirectAdmin(self::$currentIndex.'&conf=3&token='.$this->token);
                    } else {
                        Tools::redirectAdmin(self::$currentIndex.'&conf=4&token='.$this->token);
                    }
                }
            } else {
                if ($idAgent) {
                    $this->display = 'edit';
                } else {
                    $this->display = 'add';
                }
            }
        } else {
            $this->errors[] = $this->l('You do not have access right to view this page.');
        }
    }
}
