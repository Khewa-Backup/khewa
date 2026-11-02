<?php
/**
 * Visit history administration controller.
 *
 * @author Best-Kit
 * @copyright 2026 Best-Kit
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'bestkit_log/includer.php';

class AdminBestkitLogVisitHistoryController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'bestkit_log_visit';
        $this->className = 'BestkitLogVisit';
        $this->identifier = 'id_bestkit_log_visit';
        $this->_defaultOrderBy = 'id_bestkit_log_visit';
        $this->_defaultOrderWay = 'DESC';
        $this->lang = false;
        $this->bootstrap = true;

        parent::__construct();

        //$this->addRowAction('view');
        $this->bulk_actions = array();

        $this->_select = 'ld.`path`, ld.`url`';
        $this->_join = '
            LEFT JOIN `' . _DB_PREFIX_ . 'bestkit_log_visit_detail` ld ON (a.id_bestkit_log_visit = ld.id_bestkit_log_visit)';
        //$this->_group = 'GROUP BY a.id_bestkit_log_visit_detail';

        $this->fields_list = array(
            'id_bestkit_log_visit' => array('title' => $this->module->l('ID'), 'align' => 'center', 'width' => 25),
            'id_employee' => array('title' => $this->module->l('Employee ID')),
            'email' => array('title' => $this->module->l('Email address')),
            'name' => array('title' => $this->module->l('Name')),
            'ip' => array('title' => $this->module->l('IP')),
            'path' => array('title' => $this->module->l('Path')),
            'session_start' => array('title' => $this->module->l('Session start')),
            //'url' => array('title' => $this->l('url')),
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
        $id = (int) Tools::getValue('id_bestkit_log_visit');
        $obj = new BestkitLogVisit($id);

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
