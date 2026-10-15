<?php
/**
*  2019 Zack Hussain
*
*  @author 		Zack Hussain <me@zackhussain.ca>
*  @copyright  	2019 Zack Hussain
*
*  DISCLAIMER
*
*  Do not redistribute without my permission. Feel free to modify the code as needed.
*  Modifying the code may break future PrestaShop updates.
*  Do not remove this comment containing author information and copyright.
*
*/

class AdminCanadaPostLabelsViewShipmentsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->identifier = 'id_shipment';
        $this->table = 'cpl_shipment';
        $this->className = '\CanadaPostPs\Shipment';

        parent::__construct();

        $Forms = new \CanadaPostPs\Forms();
        $this->fields_list = $Forms->getShipmentFieldsList();
        $this->_orderBy = 'id_shipment';
        $this->_orderWay = 'DESC';
        $this->list_no_link = true;

        $this->actions = $Forms->getShipmentsActions();
        $this->bulk_actions = $Forms->getShipmentsBulkActions();

        $this->bootstrap = true;

        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminDashboard'));
        }
    }

    public function initToolBarTitle()
    {
        $this->toolbar_title[] = $this->module->l('View Shipments');
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();
        unset($this->toolbar_btn['new']);
    }

    public function init()
    {
        parent::init();
    }

    public function initContent()
    {
        parent::initContent();
    }

    public function postProcess()
    {
        parent::postProcess();

        $API = new \CanadaPostPs\API();

        // Submit either the id_group or date range depending on Canada Post account type
        if (Tools::getIsset('submitSyncShipments')) {
            if (Tools::getIsset('group-id')) {
                $API->syncShipments(Tools::getValue('group-id'));
            } elseif (Tools::getIsset('from') && Tools::getIsset('to')) {
                if (Validate::isDateFormat(Tools::getValue('from')) && Validate::isDateFormat(Tools::getValue('to'))) {
                    $from = new DateTime(Tools::getValue('from'));
                    $to = new DateTime(Tools::getValue('to'));
                    $API->syncShipments(false, $from, $to);
                }
            }
        }

        $Forms = new \CanadaPostPs\Forms();
        $Forms->postProcessShipments(
            $this->context->link->getAdminLink($this->controller_name)
        );
    }

    public function renderForm()
    {
        return parent::renderForm();
    }

    public function renderList()
    {
        if (!$this->module->isConnected() || !$this->module->isVerified()) {
            return $this->module->displayError($this->module->l(\CanadaPostPs\Tools::$error_messages['CONNECT_ACCOUNT']));
        }
        $Forms = new \CanadaPostPs\Forms();

        $syncForm = $Forms->renderViewShipmentsForm(
            $this->context->link->getAdminLink($this->controller_name),
            Tools::getAdminTokenLite($this->controller_name)
        );

        return  $syncForm . parent::renderList() . $this->module->logo;
    }

    public function displayPrintLink($token, $id, $name)
    {
        return $this->module->displayPrintLink($token, $id, $name);
    }

    public function displayPrintCommercialInvoiceLink($token, $id, $name)
    {
        return $this->module->displayPrintCommercialInvoiceLink($token, $id, $name);
    }

    public function displayPrintReturnLink($token, $id, $name)
    {
        return $this->module->displayPrintReturnLink($token, $id, $name);
    }

    public function displayVoidLink($token, $id, $name)
    {
        return $this->module->displayVoidLink($token, $id, $name);
    }

    public function displayRefundLink($token, $id, $name)
    {
        return $this->module->displayRefundLink($token, $id, $name);
    }
}
