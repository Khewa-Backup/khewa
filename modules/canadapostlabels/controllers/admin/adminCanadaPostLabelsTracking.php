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

class AdminCanadaPostLabelsTrackingController extends ModuleAdminController
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
        $this->toolbar_title[] = $this->module->l('Track Parcel');
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

        $Forms = new \CanadaPostPs\Forms();

        $formArr = array(
            $Forms->renderTrackingForm(
                $this->context->link->getAdminLink($this->controller_name),
                Tools::getAdminTokenLite($this->controller_name)
            )
        );

        if (Tools::isSubmit('submitTracking')) {
            $API      = new \CanadaPostPs\API();
            $TrackingDetailsType = $API->processTracking(Tools::getValue('tracking-pin'));
            if ($TrackingDetailsType instanceof \CanadaPostWs\Type\Tracking\TrackingDetailsType) {
                $formArr[] = $this->renderTrackingList($TrackingDetailsType);
            }
        }

        $this->context->smarty->assign(array(
            'forms' => $formArr,
            'logo' => $this->module->logo,
        ));

        $this->context->smarty->assign(array(
            'content' => $this->context->smarty->fetch(sprintf(_PS_MODULE_DIR_.'%s/views/templates/hook/forms.tpl', $this->module->name))
        ));
    }

    public function postProcess()
    {
        parent::postProcess();
    }

    public function renderForm()
    {
        return parent::renderForm();
    }

    /**
     * @var \CanadaPostWs\Type\Tracking\TrackingDetailsType $TrackingDetailsType
     * */
    public function renderTrackingList($TrackingDetailsType)
    {
        $fields_list = array(
            'eventDate'           => array(
                'title' => $this->module->l('Date'),
                'type'  => 'date',
            ),
            'eventTime'           => array(
                'title' => $this->module->l('Time'),
                'type'  => 'text',
            ),
            'eventSite'           => array(
                'title' => $this->module->l('Location'),
                'type'  => 'text',
            ),
            'eventDescription'    => array(
                'title' => $this->module->l('Description'),
                'type'  => 'text',
            ),
            'eventRetailLocation' => array(
                'title' => $this->module->l('Retail Location'),
                'type'  => 'text',
            ),
        );

        $events = array();
        /* @var $significantEvent \CanadaPostWs\Type\Tracking\SignificantEventItemType */
        foreach ($TrackingDetailsType->getSignificantEvents() as $key => $significantEvent) {
            $events[$key]['eventId']           =   $significantEvent->getEventIdentifier();
            $events[$key]['eventDate']           = $significantEvent->getEventDate();
            $events[$key]['eventTime']           = $significantEvent->getEventTime();
            $eventSite = $significantEvent->getEventSite();
            if (!empty($eventSite)) {
                $events[$key]['eventSite'] = sprintf(
                    '%s, %s',
                    $eventSite,
                    $significantEvent->getEventProvince()
                );
            } else {
                $events[$key]['eventSite'] = '';
            }
            $events[$key]['eventDescription']    = $significantEvent->getEventDescription();
            $events[$key]['eventRetailLocation'] = $significantEvent->getEventRetailName();
        }

        $helper                = new HelperList();
        $helper->shopLinkType  = '';
        $helper->simple_header = true;
        $helper->show_toolbar  = false;
        $helper->title         = $this->module->l('Tracking Events');
        $helper->table         = false;
        $helper->no_link         = true;
        $helper->identifier    = 'eventId';
        $helper->token         = Tools::getAdminTokenLite($this->controller_name);

        return $helper->generateList($events, $fields_list);
    }
}
