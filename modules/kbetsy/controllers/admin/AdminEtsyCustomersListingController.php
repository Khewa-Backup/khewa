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
//First condition to check if PS Version defined
if (!defined('_PS_VERSION_')) {
    exit;
}
require_once(_PS_MODULE_DIR_ . 'kbetsy/classes/EtsyModule.php');

class AdminEtsyCustomersListingController extends ModuleAdminController
{

    public function __construct()
    {
        $this->name = 'EtsyCustomersListing';
        $this->context = Context::getContext();
        $this->bootstrap = true;
        $this->table = 'customer';

        parent::__construct();
        $this->fields_list = array(
            'id_customer' => array(
                'title' => $this->module->l('Customer ID', 'AdminEtsyCustomersListingController'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
                'remove_onclick' => true
            ),
            'firstname' => array(
                'title' => $this->module->l('First Name', 'AdminEtsyCustomersListingController'),
                'havingFilter' => true,
                'remove_onclick' => true
            ),
            'lastname' => array(
                'title' => $this->module->l('Last Name', 'AdminEtsyCustomersListingController'),
                'havingFilter' => true,
                'remove_onclick' => true
            ),
            'email' => array(
                'title' => $this->module->l('Email Address', 'AdminEtsyCustomersListingController'),
                'havingFilter' => true,
                'remove_onclick' => true
            ),
        );

        $this->_join = '
                INNER JOIN (SELECT `id_customer`			
				FROM `' . _DB_PREFIX_ . 'orders` o
                INNER JOIN  `' . _DB_PREFIX_ . 'etsy_orders_list` b ON (b.`id_order` = o.`id_order`)                                               
				GROUP BY id_customer) cu ON (cu.`id_customer` = a.`id_customer`)';

        $this->_orderBy = 'id_customer';
        $this->_orderWay = 'DESC';
        $this->module->list_no_link = true;
    }

    public function renderList()
    {
        $this->addRowAction('view');
        return parent::renderList();
    }

    public function initToolbar()
    {
        parent::initToolbar();
        unset($this->toolbar_btn['new']);
    }

    /** Display view action link */
    public function displayViewLink($token = null, $id = null, $name = null)
    {
        if (!array_key_exists('View', self::$cache_lang)) {
            self::$cache_lang['View'] = $this->module->l('View', 'Helper');
        }

        $view_link = $this->context->link->getAdminlink('AdminCustomers');

        if (version_compare(_PS_VERSION_, '1.7.6.0', '>=')) {
            $view_link = str_replace('customers/', 'customers/' . $id . '/view', $view_link);
        } else {
            $view_link = $view_link . '&' . $this->identifier . '=' . $id . '&viewcustomer';
        }

        $this->context->smarty->assign(array(
            'href' => $view_link,
            'action' => $this->module->l('View'),
            'icon' => 'search-plus'
        ));

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbetsy/views/templates/admin/list/list_action.tpl');
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();
    }
}
