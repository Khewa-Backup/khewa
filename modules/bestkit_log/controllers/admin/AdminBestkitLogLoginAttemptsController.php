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

class AdminBestkitLogLoginAttemptsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'bestkit_log_login_attempt';
        $this->className = 'BestkitLogLoginAttempt';
        $this->identifier = 'id_bestkit_log_login_attempt';
        $this->_defaultOrderBy = 'id_bestkit_log_login_attempt';
        $this->_defaultOrderWay = 'DESC';
        $this->lang = FALSE;
        $this->context = Context::getContext();
        $this->bootstrap = TRUE;

        parent::__construct();

        //$this->addRowAction('view');
        $this->bulk_actions = array();

        $this->fields_list = array(
            'id_bestkit_log_login_attempt' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
            'id_employee' => array('title' => $this->l('Employee ID')),
            'email' => array('title' => $this->l('Email address')),
            'name' => array('title' => $this->l('Name')),
            'status' => array('title' => $this->l('Status'), 'active' => 'status'),
            'ip' => array('title' => $this->l('IP')),
            'user_agent' => array('title' => $this->l('User agent')),
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

    public function renderForm()
    {
        $id = Tools::getValue('id_bestkit_log_login_attempt');
        $obj = new BestkitLogLoginAttempt($id);

        $link = Context::getContext()->link->getAdminLink('AdminEmployees') . '&id_employee='.(int)$obj->id_employee.'&updateemployee';
        Tools::redirectAdmin($link);
    }
}