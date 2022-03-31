<?php
/**
 * 2007-2021 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@buy-addons.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    Buy-Addons <contact@buy-addons.com>
 *  @copyright 2007-2021 PrestaShop SA
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 * @since 1.6
 */

class AdminReportSaleController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }
    public function display()
    {
        parent::display();
    }
    public function initContent()
    {
        parent::initContent();
        $tokenad = Tools::getAdminTokenLite('AdminModules');
        $adminProducts='index.php?controller=AdminModules';
        $action = Tools::getValue('action');
        if (empty($action)) {
            Tools::redirectAdmin($adminProducts.'&token='.$tokenad.'&configure=reportsale');
            return true;
        }
        $action = Tools::strtolower($action);
        switch ($action) {
            case 'viewcustomer':
                return $this->viewCustomer();
            case 'vieworder':
                return $this->viewOrder();
        }
    }
    public function viewCustomer()
    {
        $db = Db::getInstance();
        $id_report = (int) Tools::getValue('id_report');
        if (empty($id_report)) {
            die;
        }
        $sql ='SELECT customers_data FROM '._DB_PREFIX_.'ba_report_products';
        $sql .=' WHERE id_report = '.$id_report;
        $customers_data = $db->getValue($sql, false);
        $arr = Tools::jsonDecode($customers_data, true);
        $c_controller = 'index.php?controller=AdminCustomers&viewcustomer';
        $c_controller .='&token='.Tools::getAdminTokenLite('AdminCustomers');
        $this->context->smarty->assign('c_controller', $c_controller);
        $this->context->smarty->assign('customer', $arr);
        $tpl = _PS_MODULE_DIR_."reportsale/views/templates/admin/customer_data_modal.tpl";
        echo $this->context->smarty->fetch($tpl);
        die;
    }
    public function viewOrder()
    {
        $db = Db::getInstance();
        $id_report = (int) Tools::getValue('id_report');
        if (empty($id_report)) {
            die;
        }
        $sql ='SELECT orders_data FROM '._DB_PREFIX_.'ba_report_products';
        $sql .=' WHERE id_report = '.$id_report;
        $orders_data = $db->getValue($sql, false);
        $arr = Tools::jsonDecode($orders_data, true);
        $c_controller = 'index.php?controller=AdminOrders&vieworder';
        $c_controller .='&token='.Tools::getAdminTokenLite('AdminOrders');
        $this->context->smarty->assign('c_controller', $c_controller);
        $this->context->smarty->assign('orders', $arr);
        $tpl = _PS_MODULE_DIR_."reportsale/views/templates/admin/order_data_modal.tpl";
        echo $this->context->smarty->fetch($tpl);
        die;
    }
}
