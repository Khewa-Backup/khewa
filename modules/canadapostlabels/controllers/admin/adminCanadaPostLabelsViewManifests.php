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

class AdminCanadaPostLabelsViewManifestsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->identifier = 'id_manifest';
        $this->table = 'cpl_manifest';
        $this->className = '\CanadaPostPs\Manifest';

        parent::__construct();

        $Forms = new \CanadaPostPs\Forms();
        $this->fields_list = $Forms->getManifestFieldsList();
        $this->_orderBy = 'id_manifest';
        $this->_orderWay = 'DESC';
        $this->list_no_link = true;

        $this->actions = array('printmanifest');
        $this->bulk_actions = array(
            'print' => array(
                'text'    => $this->l('Print selected'),
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
        $this->toolbar_title[] = $this->module->l('View Manifests');
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

        if (Tools::getIsset('submitSyncManifests')) {
            if (Validate::isDateFormat(Tools::getValue('from')) && Validate::isDateFormat(Tools::getValue('to'))) {
                $from = new DateTime(Tools::getValue('from'));
                $to = new DateTime(Tools::getValue('to'));
                $API->syncManifests($from, $to);
            } else {
                $this->errors[] = $this->module->l('Invalid date format');
            }
        }

        // Print single manifest
        if (Tools::isSubmit('id_manifest') && Tools::isSubmit('print_manifest')) {
            $Manifest = new \CanadaPostPs\Manifest(Tools::getValue('id_manifest'));
            $API->processSubmitPrint(
                $Manifest,
                $this->module->getManifestsPathLocal(),
                $this->module->getManifestsPathUri(),
                $Manifest->poNumber
            );
        }

        // Bulk print
        if (Tools::isSubmit('submitBulkprint'.\CanadaPostPs\Manifest::$definition['table']) &&
            Tools::getIsset(\CanadaPostPs\Manifest::$definition['table'].'Box')
        ) {
            $API->processSubmitBulkPrint('\CanadaPostPs\Manifest', $this->module->getManifestsPathLocal(), 'poNumber', $this->module->getManifestsPathuri());
        }
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

        $module = $this->module;
        if (!$this->module->isContract() || !$module::getConfig('CONTRACT')) {
            return $this->module->displayError($this->module->l(\CanadaPostPs\Tools::$error_messages['CONTRACT_ONLY']));
        }
        $Forms = new \CanadaPostPs\Forms();

        $syncForm = $Forms->renderViewManifestsForm(
            $this->context->link->getAdminLink($this->controller_name),
            Tools::getAdminTokenLite($this->controller_name)
        );

        return  $syncForm . parent::renderList() . $this->module->logo;
    }

    public function displayPrintManifestLink($token, $id, $name)
    {
        return $this->module->displayPrintManifestLink($token, $id, $name);
    }
}
