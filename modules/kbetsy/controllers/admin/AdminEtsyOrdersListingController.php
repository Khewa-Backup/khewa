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

class AdminEtsyOrdersListingController extends ModuleAdminController
{

    protected $statuses_array = array();

    public function __construct()
    {
        $this->name = 'EtsyOrdersListing';
        $this->context = Context::getContext();
        $this->bootstrap = true;
        $this->table = 'order';

        $statuses = OrderState::getOrderStates((int) $this->context->language->id);
        foreach ($statuses as $status) {
            $this->statuses_array[$status['id_order_state']] = $status['name'];
        }

        parent::__construct();
        $this->fields_list = array(
            'id_order' => array(
                'title' => $this->module->l('Order ID', 'AdminEtsyOrdersListingController'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
                'remove_onclick' => true
            ),
            'id_etsy_order' => array(
                'title' => $this->module->l('Etsy Order ID', 'AdminEtsyOrdersListingController'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
                'remove_onclick' => true
            ),
            'reference' => array(
                'title' => $this->module->l('Reference', 'AdminEtsyOrdersListingController'),
                'remove_onclick' => true
            ),
            'customer' => array(
                'title' => $this->module->l('Customer', 'AdminEtsyOrdersListingController'),
                'havingFilter' => true,
                'remove_onclick' => true
            ),
            'total_paid_tax_incl' => array(
                'title' => $this->module->l('Total', 'AdminEtsyOrdersListingController'),
                'type' => 'price',
                'currency' => true,
                'badge_success' => true,
                'remove_onclick' => true
            ),
            'payment' => array(
                'title' => $this->module->l('Payment', 'AdminEtsyOrdersListingController'),
                'remove_onclick' => true
            ),
            'osname' => array(
                'title' => $this->module->l('Status', 'AdminEtsyOrdersListingController'),
                'type' => 'select',
                'color' => 'color',
                'list' => $this->statuses_array,
                'filter_key' => 'os!id_order_state',
                'filter_type' => 'int',
                'order_key' => 'osname',
                'remove_onclick' => true
            ),
            'date_add' => array(
                'title' => $this->module->l('Date', 'AdminEtsyOrdersListingController'),
                'type' => 'datetime',
                'filter_key' => 'a!date_added',
                'remove_onclick' => true
            )
        );

//        $this->_select = 'CONCAT(LEFT(c.`firstname`, 1), \'. \', c.`lastname`) AS `customer`, osl.`name` AS `osname`,';
        $this->_select = 'CONCAT(LEFT(c.`firstname`, 1), \'. \', c.`lastname`) AS `customer`, osl.`name` AS `osname`,b.id_etsy_order';
        $this->_join = '
                INNER JOIN `' . _DB_PREFIX_ . 'etsy_orders_list` b ON (b.`id_order` = a.`id_order`)
		LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON (c.`id_customer` = a.`id_customer`)
		LEFT JOIN `' . _DB_PREFIX_ . 'order_state` os ON (os.`id_order_state` = a.`current_state`)
		LEFT JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = ' . (int) $this->context->language->id . ')';
        $this->_orderBy = 'id_order';
        $this->_orderWay = 'DESC';
        $this->module->list_no_link = true;
    }

    public function renderList()
    {
        $this->addRowAction('view');
        return parent::renderList();
    }

    public static function setOrderCurrency($echo, $tr)
    {
        $order = new Order($tr['id_order']);
        return Tools::displayPrice($echo, (int) $order->id_currency);
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
            self::$cache_lang['View'] = $this->l('View', 'Helper');
        }

        $this->context->smarty->assign(array(
            'href' => $this->context->link->getAdminlink('AdminOrders') . '&' . $this->identifier . '=' . $id . '&vieworder',
            'action' => $this->l('View'),
            'icon' => 'search-plus'
        ));

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbetsy/views/templates/admin/list/list_action.tpl');
    }

    public function initPageHeaderToolbar()
    {
        $secure_key = Configuration::get('KBETSY_SECURE_KEY');
        $this->page_header_toolbar_btn['kb_sync_order_list'] = array(
            'href' => $this->context->link->getModuleLink('kbetsy', 'cron', array('action' => 'syncOrdersListing', 'secure_key' => $secure_key)),
            'target' => '_blank',
            'desc' => $this->l('Sync Orders from Etsy'),
            'icon' => 'process-icon-update'
        );
        $this->page_header_toolbar_btn['kb_sync_order_status'] = array(
            'href' => $this->context->link->getModuleLink('kbetsy', 'cron', array('action' => 'syncOrdersStatus', 'secure_key' => $secure_key)),
            'target' => '_blank',
            'desc' => $this->l('Update Order Status On Etsy'),
            'icon' => 'process-icon-update'
        );

        parent::initPageHeaderToolbar();
    }
}
