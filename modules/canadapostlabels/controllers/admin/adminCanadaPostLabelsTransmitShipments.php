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

class AdminCanadaPostLabelsTransmitShipmentsController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();

        $this->bootstrap = true;

        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminDashboard'));
        }
    }

    public function initToolBarTitle()
    {
        $this->toolbar_title[] = $this->module->l('End of Day / Transmit Shipments');
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

        if (!$this->module->isConnected() || !$this->module->isVerified()) {
            $this->context->controller->errors[] = $this->module->l(\CanadaPostPs\Tools::$error_messages['CONNECT_ACCOUNT']);
            return false;
        }

        $module = $this->module;
        if (!$this->module->isContract() || !$module::getConfig('CONTRACT')) {
            $this->context->controller->errors[] = $this->module->l(\CanadaPostPs\Tools::$error_messages['CONTRACT_ONLY']);
            return false;
        }

        $Forms = new \CanadaPostPs\Forms();

        $logo = $this->context->smarty->fetch(sprintf(_PS_MODULE_DIR_.'%s/views/templates/admin/logo.tpl', $this->module->name));

        // modal.tpl template vars
        $this->modals[] = array(
            'modal_id' => 'labelModal',
            'modal_title' => \CanadaPostPs\Tools::renderHtmlTag('h4', $this->module->l('Print Manifest')),
            'modal_content' => \CanadaPostPs\Tools::renderHtmlTag(
                    'div', null, array('class' => 'modal-body')
                ) . $this->module->logo,
            'modal_actions' => true,
            'modal_class' => 'zhmedia-modal'
        );

        Media::addJsDef(array(
            'canadaPostCreateLabelControllerUrl' => $this->context->link->getAdminLink($this->controller_name, true),
        ));

        $this->context->smarty->assign(array(
            'forms' => array(
                $Forms->renderTransmitShipmentForm(
                    $this->context->link->getAdminLink($this->controller_name),
                    Tools::getAdminTokenLite($this->controller_name)
                ),
            ),
            'modal' => $this->renderModal(),
            'logo' => $this->module->logo,
        ));

        $this->context->smarty->assign(array(
            'content' => $this->context->smarty->fetch(sprintf(_PS_MODULE_DIR_.'%s/views/templates/hook/forms.tpl', $this->module->name))
        ));
    }

    public function postProcess()
    {
        parent::postProcess();

        if (Tools::isSubmit('submitTransmitShipments')) {
            $API      = new \CanadaPostPs\API();
            $API->processTransmitShipments();
        }
    }

    public function renderForm()
    {
        return parent::renderForm();
    }

    public function displayPrintManifestLink($token, $id, $name)
    {
        return $this->module->displayPrintManifestLink($token, $id, $name);
    }
}
