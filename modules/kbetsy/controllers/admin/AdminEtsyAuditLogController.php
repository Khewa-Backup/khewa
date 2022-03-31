<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 * We offer the best and most useful modules PrestaShop and modifications for your online store.
 *
 * @author    knowband.com <support@knowband.com>
 * @copyright 2017 Knowband
 * @license   see file: LICENSE.txt
 * @category  PrestaShop Module
 */

require_once(_PS_MODULE_DIR_ . 'kbetsy/classes/EtsyModule.php');

class AdminEtsyAuditLogController extends ModuleAdminController
{

    public function __construct()
    {
        $this->name = 'EtsyAuditLog';
        $this->context = Context::getContext();
        $this->bootstrap = true;
        $this->table = 'etsy_audit_log';

        parent::__construct();
        
        $this->fields_list = array(
            'id_etsy_audit_log' => array(
                'title' => $this->module->l('ID', 'AdminEtsyAuditLogController'),
                'align' => 'center',
                'remove_onclick' => true
            ),
            'log_entry' => array(
                'title' => $this->module->l('Description', 'AdminEtsyAuditLogController'),
                'float' => true,
                'remove_onclick' => true
            ),
            'log_class_method' => array(
                'title' => $this->module->l('Action Called', 'AdminEtsyAuditLogController'),
                'remove_onclick' => true
            ),
            'log_time' => array(
                'title' => $this->module->l('Date of Action', 'AdminEtsyAuditLogController'),
                'type' => 'datetime',
                'class' => 'fixed-width-lg',
                'remove_onclick' => true
            )
        );

        $this->_orderBy = 'id_etsy_audit_log';
        $this->_orderWay = 'DESC';

        $this->module->list_no_link = true;
    }

    public function renderList()
    {
        return parent::renderList();
    }

    public function initToolbar()
    {
        parent::initToolbar();
        unset($this->toolbar_btn['new']);
    }
}
