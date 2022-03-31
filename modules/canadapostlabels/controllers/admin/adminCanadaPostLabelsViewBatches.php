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

class AdminCanadaPostLabelsViewBatchesController extends ModuleAdminController
{
    public function __construct()
    {
        $this->identifier = 'id_batch';
        $this->table = 'cpl_batch';
        $this->className = '\CanadaPostPs\Batch';

        parent::__construct();

        $this->_select = '
		a.id_batch,
		COUNT(shipment.id_batch) AS totalShipments,
		IF((SELECT oe.id_order_error FROM `' . _DB_PREFIX_ . \CanadaPostPs\OrderError::$definition['table'] . '` oe WHERE oe.id_batch = a.id_batch LIMIT 1) > 0, 1, 0) as haserror,
		(SELECT oerror.id_batch FROM `' . _DB_PREFIX_ . \CanadaPostPs\OrderError::$definition['table'] . '` oerror WHERE oerror.id_batch = a.id_batch LIMIT 1) as ordererror
		';

        $this->_join = '
		LEFT JOIN `' . _DB_PREFIX_ . \CanadaPostPs\Shipment::$definition['table'].'` shipment ON (shipment.`id_batch` = a.`id_batch`)
		';

        $this->_group = '
        GROUP BY shipment.id_batch
        ';

        $Forms = new \CanadaPostPs\Forms();

        $this->fields_list = array(
            'id_batch' => array(
                'title'   => $this->module->l('Batch ID'),
                'type'    => 'text',
                'orderby' => true,
                'search' => true
            ),
            'totalShipments' => array(
                'title'   => $this->module->l('Total Shipments'),
                'type'    => 'text',
                'orderby' => true,
                'search' => true,
                'havingFilter' => true,
            ),
            'ordererror' => array(
                'title'   => $this->module->l('Errors'),
                'type' => 'bool',
                'class' => 'fixed-width-xs',
                'callback' => 'getBatchErrorMessage',
                'callback_object' => $Forms,
                'havingFilter' => true,
                'filter_key' => 'haserror',
                'filter_type' => 'int',
                'hint' => $this->module->l('Errors that occurred during label creation for a batch. Click the red "Error" link to view the error messages.'),
            ),
            'date_add' => array(
                'title'   => $this->module->l('Date'),
                'type' => 'datetime',
                'filter_key' => 'a!date_add',
            ),
        );
        $this->_orderBy = 'id_batch';
        $this->_orderWay = 'DESC';
        $this->list_no_link = true;

        $this->actions = array('printbatch');
        $this->bulk_actions = array(
            'printbatch' => array(
                'text'    => $this->module->l('Print selected'),
                'icon'    => 'icon-print'
            ),
        );

        $this->informations[] = $this->module->l('Batches are groups of labels that you have previously printed on the "Bulk Order Labels" page.');

        $this->bootstrap = true;

        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminDashboard'));
        }
    }

    public function initToolBarTitle()
    {
        $this->toolbar_title[] = $this->module->l('View Batches');
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

        // modal.tpl template vars
        $this->modals[] = array(
            'modal_id' => 'errorModal',
            'modal_title' => \CanadaPostPs\Tools::renderHtmlTag('h4', $this->module->l('Error Messages')),
            'modal_content' => \CanadaPostPs\Tools::renderHtmlTag(
                    'div', null, array('class' => 'modal-body')
                ) . $this->module->logo,
            'modal_actions' => true,
            'modal_class' => 'zhmedia-modal'
        );
    }

    public function postProcess()
    {
        parent::postProcess();

        $API = new \CanadaPostPs\API();
        $response = array();

        // Process error messages
        if (Tools::isSubmit('id_batch') && Tools::isSubmit('viewbatcherrors')) {
            $orderErrors = \CanadaPostPs\OrderError::getOrderErrors(array('id_batch' => Tools::getValue('id_batch')));

            if (!empty($orderErrors)) {
                $errors = array();
                foreach ($orderErrors as $orderError) {
                    $OrderError = new \CanadaPostPs\OrderError($orderError['id_order_error']);
                    $errors[] = sprintf(
                        '%s %s: "%s" %s',
                        $this->module->l('Order ID'),
                        $OrderError->id_order,
                        $OrderError->errorMessage,
                        \CanadaPostPs\Tools::renderHtmlTag('br')
                    );
                }
                if (Tools::getIsset('ajax')) {
                    $response['response'] = $this->module->displayError(
                        implode('', $errors)
                    );
                    die(json_encode($response));
                }

                $this->errors[] = $OrderError->errorMessage;
            } else {
                if (Tools::getIsset('ajax')) {
                    $response['error'] = $this->module->l('Unable to retrieve error messages.');
                    die(json_encode($response));
                }
            }
        }

        // Print single batch
        if (Tools::isSubmit('id_batch') && Tools::isSubmit('printbatch')) {
            $Batch = new \CanadaPostPs\Batch(Tools::getValue('id_batch'));
            $API->processSubmitPrint(
                $Batch,
                $this->module->getBatchPathLocal(),
                $this->module->getBatchPathUri(),
                $Batch->id
            );
        }

        if (
            Tools::isSubmit('submitBulkprintbatch'.\CanadaPostPs\Batch::$definition['table']) &&
            Tools::getIsset(\CanadaPostPs\Batch::$definition['table'].'Box')
        ) {
            $API->processSubmitBulkPrint('\CanadaPostPs\Batch', $this->module->getBatchPathLocal(), 'id', $this->module->getBatchPathUri());
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

        return  parent::renderList() . $this->module->logo;
    }

    public function displayPrintBatchLink($token, $id, $name)
    {
        return $this->module->displayPrintBatchLink($token, $id, $name);
    }
}
