<?php
/**
* NOTICE OF LICENSE
*
* This file is part of the 'WK Inventory' module feature.
* Developped by Khoufi Wissem (2017).
* You are not allowed to use it on several site
* You are not allowed to sell or redistribute this module
* This header must not be removed
*
*  @author    KHOUFI Wissem - K.W
*  @copyright Khoufi Wissem
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class AdminStocktakeLogsController extends ModuleAdminController
{
    public function __construct()
    {
        include_once(dirname(__FILE__).'/../../classes/StockTakeLog.php');

        $this->bootstrap = true;
        $this->table = 'wkinventory_log';
        $this->className = 'StockTakeLog';
        $this->lang = false;
        $this->noLink = true;
        $this->list_no_link = true;
        $this->context = Context::getContext();
        $this->addRowAction('delete');

        $this->_use_found_rows = false;
        $this->_select .= 'CONCAT(LEFT(e.firstname, 1), \'. \', e.lastname) employee';
        $this->_join .= ' LEFT JOIN '._DB_PREFIX_.'employee e ON (a.id_employee = e.id_employee)';
        $this->_orderBy = 'id_wkinventory_log';
        $this->_orderWay = 'DESC';

        $this->fields_list = array(
            'id_wkinventory_log' => array(
                'title' => 'ID',
                'align' => 'text-center',
                'class' => 'fixed-width-xs'
            ),
            'employee' => array(
                'title' => $this->l('Employee'),
                'havingFilter' => true,
                'callback' => 'displayEmployee',
                'callback_object' => $this
            ),
            'severity' => array(
                'title' => $this->l('Severity (1-4)'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
                'badge_success' => true,
                'badge_danger' => true,
                'badge_warning' => true,
                'color' => 'color',
            ),
            'message' => array(
                'title' => $this->l('Message'),
                'callback' => 'displayMessage',
            ),
            'object_type' => array(
                'title' => $this->l('Object type'),
                'align' => 'center',
                'class' => 'fixed-width-sm'
            ),
            'object_id' => array(
                'title' => $this->l('Object ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs'
            ),
            'error_code' => array(
                'title' => $this->l('Error code'),
                'align' => 'center',
                'prefix' => '0x',
                'class' => 'fixed-width-xs'
            ),
            'date_add' => array(
                'title' => $this->l('Date'),
                'align' => 'right',
                'type' => 'datetime'
            )
        );
        parent::__construct();
    }

    public function displayEmployee($value, $tr)
    {
        $template = $this->createTemplate('helpers/list/employee_field.tpl');
        $employee = new Employee((int)$tr['id_employee']);
        $template->assign(array(
            'employee_image' => $this->getImage($employee),
            'employee_name' => $value
        ));
        return $template->fetch();
    }

    public function processDelete()
    {
        if (StockTakeLog::eraseAllLogs()) {
            Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminStocktakeLogs').'&conf=1');
        }
    }

    public function getImage($employee)
    {
        if (method_exists('Employee', 'getImage')) {
            return $employee->getImage();
        } else {
            if (!Validate::isLoadedObject($employee)) {
                return Tools::getAdminImageUrl('prestashop-avatar.png');
            }
            return Tools::getShopProtocol().'profile.prestashop.com/'.urlencode($employee->email).'.jpg';
        }
    }

    public function displayMessage($value, $tr)
    {
        return Tools::stripslashes($value);
    }

    /**
     * AdminController::getList() override.
     *
     * @see AdminController::getList()
     */
    public function getList(
        $id_lang,
        $order_by = null,
        $order_way = null,
        $start = 0,
        $limit = null,
        $id_lang_shop = false
    ) {
        parent::getList($id_lang, $order_by, $order_way, $start, $limit, $id_lang_shop);

        foreach ($this->_list as &$item) {
            switch ($item['severity']) {
                case 1:
                    $item['badge_success'] = true;
                    break;
                case 2:
                    $item['badge_warning'] = true;
                    break;
                case 3:
                    $item['badge_danger'] = true;
                    break;
                case 4:
                    $item['color'] = '#cf535c';
                    break;
            }
        }
    }

    public function initToolbar()
    {
        parent::initToolbar();

        if ($this->module->is_before_16 && empty($this->display)) {
            $this->toolbar_btn['back'] = array(
                'href' => $this->context->link->getAdminLink('AdminStocktakedash'),
                'desc' => $this->l('Dashboard')
            );
        }
        $this->toolbar_btn['delete'] = array(
            'short' => 'Erase',
            'desc' => $this->l('Erase all'),
            'js' => 'if (confirm(\''.$this->l('Are you sure you want to empty table logs?').'\')) document.location = \''.Tools::safeOutput($this->context->link->getAdminLink('AdminStocktakeLogs')).'&token='.$this->token.'&deletewkinventory_log=1\';'
        );
        unset($this->toolbar_btn['new']);
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();

        if (empty($this->display)) {
            $this->page_header_toolbar_btn['back_to_dashboard'] = array(
                'href' => $this->context->link->getAdminLink('AdminStocktakedash'),
                'desc' => $this->l('Dashboard', null, null, false),
                'icon' => 'process-icon-back'
            );
        }
    }

    /*
    * Method Translation Override For PS 1.7
    */
    protected function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        if (method_exists('Context', 'getTranslator')) {
            $this->translator = Context::getContext()->getTranslator();
            $translated = $this->translator->trans($string);
            if ($translated !== $string) {
                return $translated;
            }
        }
        if ($class === null || $class == 'AdminTab') {
            $class = Tools::substr(get_class($this), 0, -10);
        } elseif (Tools::strtolower(Tools::substr($class, -10)) == 'controller') {
            $class = Tools::substr($class, 0, -10);
        }
        return Translate::getAdminTranslation($string, $class, $addslashes, $htmlentities);
    }
}
