<?php
/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 *         DISCLAIMER   *
 * *************************************** */
/* Do not edit or add to this file if you wish to upgrade Prestashop to newer
* versions in the future.
* ****************************************************
*
*  @author     BEST-KIT
*  @copyright  best-kit
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

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
        $this->lang = FALSE;
        $this->context = Context::getContext();
        $this->bootstrap = TRUE;

        parent::__construct();

        $this->addRowAction('view');
        $this->bulk_actions = array();

        $this->_select = 'CONCAT(LEFT(e.`firstname`, 1), \'. \', e.`lastname`) AS `employee`,';
        $this->_join = '
            LEFT JOIN `' . _DB_PREFIX_ . 'employee` e ON (e.id_employee = a.id_employee)';
        $this->_group = 'GROUP BY a.id_bestkit_log';

        $this->fields_list = array(
            'id_bestkit_log' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
            'email' => array('title' => $this->l('Email address'), 'filter_key' => 'a!email'),
            'employee' => array('title' => $this->l('Employee'), 'havingFilter' => true),
            'action_type' => array('title' => $this->l('Action type'), 'callback' => 'setStatus'),
            'id_object' => array('title' => $this->l('ID object')),
            'object' => array('title' => $this->l('Object type')),
            'date_add' => array('title' => $this->l('Date add')),
            'date_upd' => array('title' => $this->l('Date upd')),
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
        return $this->module->setStatusCallback($tr['action_type']);
    }

    public function renderView()
    {
        $diffObj = new BestkitLog(Tools::getValue('id_bestkit_log'));

        $this->context->smarty->assign([
            'bestkit_log' => [
                'diffObj' => $diffObj,
                'details' => $diffObj->getLogDetails()
            ]
        ]);

        $file = _PS_MODULE_DIR_ . 'bestkit_log/bestkit_log.php';
        return $this->module->display($file, 'views/templates/admin/log_details.tpl');
    }
}