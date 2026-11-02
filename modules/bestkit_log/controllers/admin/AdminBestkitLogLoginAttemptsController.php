<?php
/**
 * Login attempt administration controller.
 *
 * @author Best-Kit
 * @copyright 2026 Best-Kit
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'bestkit_log/includer.php';

class AdminBestkitLogLoginAttemptsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'bestkit_log_login_attempt';
        $this->className = 'BestkitLogLoginAttempt';
        $this->identifier = 'id_bestkit_log_login_attempt';
        $this->_defaultOrderBy = 'id_bestkit_log_login_attempt';
        $this->_defaultOrderWay = 'DESC';
        $this->lang = false;
        $this->bootstrap = true;

        parent::__construct();

        //$this->addRowAction('view');
        $this->bulk_actions = array();

        $this->fields_list = array(
            'id_bestkit_log_login_attempt' => array('title' => $this->module->l('ID'), 'align' => 'center', 'width' => 25),
            'id_employee' => array('title' => $this->module->l('Employee ID')),
            'email' => array('title' => $this->module->l('Email address')),
            'name' => array('title' => $this->module->l('Name')),
            'status' => array('title' => $this->module->l('Status'), 'type' => 'bool'),
            'ip' => array('title' => $this->module->l('IP')),
            'user_agent' => array('title' => $this->module->l('User agent')),
            'date_add' => array('title' => $this->module->l('Date add')),
            'date_upd' => array('title' => $this->module->l('Date upd')),
        );
    }

    public function initToolbar()
    {
        parent::initToolbar();

        if (isset($this->toolbar_btn['new']))
            unset($this->toolbar_btn['new']);
    }

    public function renderForm()
    {
        $id = (int) Tools::getValue('id_bestkit_log_login_attempt');
        $obj = new BestkitLogLoginAttempt($id);

        if (Validate::isLoadedObject($obj) && (int) $obj->id_employee > 0) {
            Tools::redirectAdmin($this->context->link->getAdminLink(
                'AdminEmployees',
                true,
                array(),
                array(
                    'id_employee' => (int) $obj->id_employee,
                    'updateemployee' => 1,
                )
            ));
        }

        return parent::renderForm();
    }
}
