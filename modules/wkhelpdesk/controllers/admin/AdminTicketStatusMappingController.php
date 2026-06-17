<?php
/**
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License version 3.0
* that is bundled with this package in the file LICENSE.txt
* It is also available through the world-wide-web at this URL:
* https://opensource.org/licenses/AFL-3.0
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to a newer
* versions in the future. If you wish to customize this module for your
* needs please refer to CustomizationPolicy.txt file inside our module for more information.
*
* @author Webkul IN
* @copyright Since 2010 Webkul
* @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*/

class AdminTicketStatusMappingController extends ModuleAdminController
{
    public function __construct()
    {
        $this->context = Context::getContext();
        $this->table = 'wk_hd_status_mapping';
        $this->className = 'WkHdStatusMapping';
        $this->bootstrap = true;
        $this->addRowAction('edit');
        $this->identifier = 'id';
        parent::__construct();
        $this->toolbar_title = $this->l('Ticket status mapping');
        $this->_select = 'wk_hd_status_mapping_shop.*';
        Shop::addTableAssociation('wk_hd_status_mapping', array('type' => 'shop', 'primary' => 'id'));
        $this->_join .= WkHdGroup::addSqlAssociationCustom('wk_hd_status_mapping', 'a', false);
        $this->_group = ' GROUP BY a.id';

        $objHdEmployee = new WkHdTicketAgent();
        if ($objHdEmployee->validateEmployee($this->context->employee)) {
            $this->fields_list = array(
                'id' => array(
                    'title' => $this->l('ID'),
                    'align' => 'center',
                    'class' => 'fixed-width-xs',
                    'filter_key' => 'wk_hd_status_mapping_shop!id'
                ),
                'id_status' => array(
                    'title' => $this->l('Ticket status'),
                    'align' => 'center',
                    'search' => false,
                    'callback' => 'displayStatusText',
                    'filter_key' => 'wk_hd_status_mapping_shop!id_status'
                ),
                'id_status_selected' => array(
                    'title' => $this->l('Selected ticket status'),
                    'align' => 'center',
                    'search' => false,
                    'callback' => 'displayStatusText',
                    'filter_key' => 'wk_hd_status_mapping_shop!id_status_selected'
                )
            );
        } else {
            $this->errors[] = $this->l('You do not have access right to view this page.');
        }
    }

    public function initContent()
    {
        if (($this->display == 'edit') && (Shop::getContext() == Shop::CONTEXT_SHOP)) {
            if (!$this->loadObject(true)) {
                Tools::redirectAdmin(self::$currentIndex.'&token='.$this->token);
            }
        }
        return parent::initContent();
    }

    public function displayStatusText($id)
    {
        $statusText = WkHdStatusMapping::getStatusById($id);
        if ($statusText) {
            return $statusText;
        } else {
            return "--";
        }
    }

    public function initToolbar()
    {
        $objHdEmployee = new WkHdTicketAgent();
        if ($objHdEmployee->validateEmployee($this->context->employee)) {
            parent::initToolbar();
            $url = explode('index.php?', $this->context->link->getAdminLink('AdminTicketStatus'))[1];
            $this->page_header_toolbar_btn['new'] = array(
                'href' => 'index.php?'.$url.'&addwk_hd_status_code',
                'desc' => $this->l('Add new status'),
            );
        }
    }

    public function renderForm()
    {
        if (Shop::getContext() != Shop::CONTEXT_SHOP) {
            return $this->context->smarty->fetch(
                _PS_MODULE_DIR_.$this->module->name.'/views/templates/admin/_partials/shop_warning.tpl'
            );
        } else {
            $objHdEmployee = new WkHdTicketAgent();
            if ($objHdEmployee->validateEmployee($this->context->employee)) {
                $id = Tools::getValue('id');
                $allStatus = WkHdStatusMapping::getAllStatusCode();
                $objStatusMapping = new WkHdStatusMapping();
                $mappedStatus = $objStatusMapping->getMappingInfoById($id);

                // get mapped status
                if ($allStatus && $mappedStatus) {
                    $statusText = WkHdStatusMapping::getStatusById($mappedStatus['id_status']);

                    $this->context->smarty->assign(
                        array(
                            'allStatus' => $allStatus,
                            'statusText' => $statusText,
                            'mappedStatus' => $mappedStatus,
                        )
                    );
                } else {
                    $this->errors[] = $this->l('Status mapping information not found.');
                }

                $this->fields_form = array(
                    'submit' => array(
                        'title' => $this->l('Save'),
                    ),
                );

                return parent::renderForm();
            } else {
                $this->errors[] = $this->l('You do not have access right to view this page.');
            }
        }
    }

    public function processSave()
    {
        $objHdEmployee = new WkHdTicketAgent();
        if ($objHdEmployee->validateEmployee($this->context->employee)) {
            $id = Tools::getValue('id');
            $idStatus = Tools::getValue('idStatus');
            $idStatusSelected = Tools::getValue('idStatusSelected');
            if ($id && empty($this->errors)) {
                // save status mapping
                $objStatusMapping = new WkHdStatusMapping((int) $id);
                $objStatusMapping->id_status = (int) $idStatus;
                $objStatusMapping->id_status_selected = (int) $idStatusSelected;
                $objStatusMapping->save();
                Tools::redirectAdmin(self::$currentIndex.'&conf=4&token='.$this->token);
            } else {
                $this->errors[] = $this->l('There is error in status mapping');
            }
        } else {
            $this->errors[] = $this->l('You do not have access right to view this page.');
        }
    }
}
