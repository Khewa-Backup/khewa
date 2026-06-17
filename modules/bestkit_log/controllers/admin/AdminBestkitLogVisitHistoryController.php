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

class AdminBestkitLogVisitHistoryController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'bestkit_log_visit';
        $this->className = 'BestkitLogVisit';
        $this->identifier = 'id_bestkit_log_visit';
        $this->_defaultOrderBy = 'id_bestkit_log_visit';
        $this->_defaultOrderWay = 'DESC';
        $this->lang = FALSE;
        $this->context = Context::getContext();
        $this->bootstrap = TRUE;

        parent::__construct();

        //$this->addRowAction('view');
        $this->bulk_actions = array();

        $this->_select = 'ld.`path`, ld.`url`';
        $this->_join = '
            LEFT JOIN `' . _DB_PREFIX_ . 'bestkit_log_visit_detail` ld ON (a.id_bestkit_log_visit = ld.id_bestkit_log_visit)';
        //$this->_group = 'GROUP BY a.id_bestkit_log_visit_detail';

        $this->fields_list = array(
            'id_bestkit_log_visit' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
            'id_employee' => array('title' => $this->l('Employee ID')),
            'email' => array('title' => $this->l('Email address')),
            'name' => array('title' => $this->l('Name')),
            'ip' => array('title' => $this->l('IP')),
            'path' => array('title' => $this->l('Path')),
            'session_start' => array('title' => $this->l('Session start')),
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
        $id = Tools::getValue('id_bestkit_log_visit');
        $obj = new BestkitLogVisit($id);

        $link = Context::getContext()->link->getAdminLink('AdminEmployees') . '&id_employee='.(int)$obj->id_employee.'&updateemployee';
        Tools::redirectAdmin($link);
    }
}