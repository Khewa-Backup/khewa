<?php
/**
 * Action audit administration controller.
 *
 * @author Best-Kit
 * @copyright 2026 Best-Kit
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'bestkit_log/includer.php';

class AdminBestkitLogController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'bestkit_log';
        $this->className = 'BestkitLog';
        $this->identifier = 'id_bestkit_log';
        $this->_defaultOrderBy = 'id_bestkit_log';
        $this->_defaultOrderWay = 'DESC';
        $this->lang = false;
        $this->bootstrap = true;

        parent::__construct();

        $this->addRowAction('view');
        $this->bulk_actions = array();

        $this->_select = 'CONCAT(LEFT(e.`firstname`, 1), \'. \', e.`lastname`) AS `employee`,';
        $this->_join = '
            LEFT JOIN `' . _DB_PREFIX_ . 'employee` e ON (e.id_employee = a.id_employee)';
        $this->_group = 'GROUP BY a.id_bestkit_log';

        $this->fields_list = array(
            'id_bestkit_log' => array('title' => $this->module->l('ID'), 'align' => 'center', 'width' => 25),
            'email' => array('title' => $this->module->l('Email address'), 'filter_key' => 'a!email'),
            'employee' => array('title' => $this->module->l('Employee'), 'havingFilter' => true),
            'action_type' => array('title' => $this->module->l('Action type'), 'callback' => 'setStatus'),
            'id_object' => array('title' => $this->module->l('ID object')),
            'object' => array('title' => $this->module->l('Object type')),
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

    public function setStatus($echo, $tr)
    {
        $status = (string) $tr['action_type'];
        $this->context->smarty->assign(array(
            'bestkit_points' => array(
                'status' => array(
                    'name' => $status,
                ),
            ),
        ));

        return $this->module->display(
            _PS_MODULE_DIR_ . 'bestkit_log/bestkit_log.php',
            'views/templates/admin/status_renderer.tpl'
        );
    }

    public function renderView()
    {
        $diffObj = new BestkitLog((int) Tools::getValue('id_bestkit_log'));

        if (!Validate::isLoadedObject($diffObj)) {
            return $this->module->displayError($this->module->l('The requested log entry could not be found.'));
        }

        $this->context->smarty->assign([
            'bestkit_log' => [
                'diffObj' => $diffObj,
                'details' => $diffObj->getLogDetails()
            ]
        ]);

        return $this->module->display(
            _PS_MODULE_DIR_ . 'bestkit_log/bestkit_log.php',
            'views/templates/admin/log_details.tpl'
        );
    }
}
