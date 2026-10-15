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

class WkHelpDeskTicketListModuleFrontController extends ModuleFrontController
{
    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Ticket list', 'ticketlist'),
            'url' => ''
        );

        return $breadcrumb;
    }

    public function initContent()
    {
        parent::initContent();
        if ($this->context->customer->isLogged()) {
            $smarty_vars = array(
                'hd_bg_color' => Configuration::get('WK_HD_TITLE_BG_COLOR'),
                'hd_text_color' => Configuration::get('WK_HD_TITLE_TEXT_COLOR'),
            );

            $objTicket = new WkHdTicket();
            $ticketList = $objTicket->getAllTicketByCustomerMailAndIdLang(
                $this->context->customer->email,
                $this->context->language->id
            );
            if ($ticketList) {
                foreach ($ticketList as &$ticket) {
                    $ticket['status'] = $this->module->getStatusTextById((int) $ticket['id_status']);
                }
                $smarty_vars['ticketList'] = $ticketList;
            }
            $smarty_vars['statusColors'] = array(
                'lightseagreen',
                'green',
                'deepskyblue',
                'orange',
                'lightgreen',
                'red'
            );
            $this->context->smarty->assign($smarty_vars);
            $this->defineJSVars();
            $this->setTemplate('module:'.$this->module->name.'/views/templates/front/ticketlist.tpl');
        } else {
            Tools::redirect($this->context->link->getPageLink('my-account'));
        }
    }

    public function defineJSVars()
    {
        $jsVars = array(
            'display_name' => $this->module->l('Display', 'ticketlist'),
            'records_name' => $this->module->l('records per page', 'ticketlist'),
            'no_product' => $this->module->l('No ticket found', 'ticketlist'),
            'show_page' => $this->module->l('Showing page', 'ticketlist'),
            'show_of' => $this->module->l('of', 'ticketlist'),
            'no_record' => $this->module->l('No records available', 'ticketlist'),
            'filter_from' => $this->module->l('filtered from', 'ticketlist'),
            't_record' => $this->module->l('total records', 'ticketlist'),
            'search_item' => $this->module->l('Search', 'ticketlist'),
            'p_page' => $this->module->l('Previous', 'ticketlist'),
            'n_page' => $this->module->l('Next', 'ticketlist'),
        );

        Media::addJsDef($jsVars);
    }

    public function setMedia()
    {
        parent::setMedia();
        $this->registerJavascript('ticketlist-js', 'modules/'.$this->module->name.'/views/js/ticketlist3.js');
        $this->registerStylesheet(
            'helpdesk_global-css',
            'modules/'.$this->module->name.'/views/css/helpdesk_global.css'
        );

        //data table file included
        $this->registerStylesheet(
            'datatable_bootstrap',
            'modules/'.$this->module->name.'/views/css/datatable_bootstrap.css'
        );
        $this->registerJavascript(
            'jquery.dataTables.min',
            'modules/'.$this->module->name.'/views/js/jquery.dataTables.min.js'
        );
        $this->registerJavascript(
            'dataTables.bootstrap',
            'modules/'.$this->module->name.'/views/js/dataTables.bootstrap.js'
        );
    }
}
