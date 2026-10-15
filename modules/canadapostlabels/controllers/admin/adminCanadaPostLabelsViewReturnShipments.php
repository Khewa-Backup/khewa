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

class AdminCanadaPostLabelsViewReturnShipmentsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->identifier = 'id_return_shipment';
        $this->table = 'cpl_return_shipment';
        $this->className = '\CanadaPostPs\ReturnShipment';

        parent::__construct();

        $Forms = new \CanadaPostPs\Forms();
        $this->fields_list = $Forms->getReturnShipmentFieldsList();
        $this->_orderBy = 'id_return_shipment';
        $this->_orderWay = 'DESC';
        $this->list_no_link = true;

        $this->actions = array('printreturn');
        $this->bulk_actions = array(
            'printreturn' => array(
                'text'    => $this->module->l('Print selected'),
                'icon'    => 'icon-print'
            )
        );

        $this->bootstrap = true;

        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminDashboard'));
        }
    }

    public function initToolBarTitle()
    {
        $this->toolbar_title[] = $this->module->l('View Return Shipments');
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
//
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
        return parent::renderList() . $this->module->logo;
    }

    public function displayPrintReturnLink($token, $id, $name)
    {
        return $this->module->displayPrintReturnLink($token, $id, $name);
    }
}
