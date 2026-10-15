<?php
/**
 * 2010-2021 Webkul.
 *
 * NOTICE OF LICENSE
 *
 * All right is reserved,
 * Please go through LICENSE.txt file inside our module
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please refer to CustomizationPolicy.txt file inside our module for more information.
 *
 * @author Webkul IN
 * @copyright 2010-2021 Webkul IN
 * @license LICENSE.txt
*/
class AdminWkEntityRestoreHistoryController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->lang = false;
        $this->table = 'wk_entity_restore_history';
        $this->className = 'WkEntityRestoreHistory';
        $this->identifier = 'id_wk_entity_restore_history';
        $this->list_no_link = true;
        parent::__construct();
        $entityType = [1 => $this->l('Product'), 2 => $this->l('Category'), 3 => $this->l('Brand'), 4 => $this->l('Supplier'), 5 => $this->l('Customer'), 6 => $this->l('Attribute group'), 7 => $this->l('Attribute'), 8 => $this->l('Feature')];
        $this->_where = ' AND a.`id_new_entity` != 0';
        $this->fields_list = [
            'id_wk_entity_restore_history' => [
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'type' => [
                'title' => $this->l('Type'),
                'align' => 'center',
                'class' => 'fixed-width-xxl',
                'type' => 'select',
                'list' => $entityType,
                'filter_key' => 'a!type',
                'callback' => 'getEntityType',
            ],
            'id_old_entity' => [
                'title' => $this->l('Entity old ID'),
                'align' => 'center',
                'class' => 'fixed-width-xxl',
            ],
            'id_new_entity' => [
                'title' => $this->l('Entity new ID'),
                'align' => 'center',
                'class' => 'fixed-width-xxl',
            ],
            'date_del' => [
                'title' => $this->l('Deletion date/time'),
                'align' => 'center',
                'class' => 'fixed-width-xl',
            ],
            'date_res' => [
                'title' => $this->l('Restoration date/time'),
                'align' => 'center',
                'class' => 'fixed-width-xl',
            ],
        ];
    }

    /**
     * To get entity type
     *
     * @param [type] $type
     *
     * @return string
     */
    public function getEntityType($type)
    {
        if (!empty($type) && $type) {
            if ($type == 1) {
                return $this->l('Product');
            } elseif ($type == 2) {
                return $this->l('Category');
            } elseif ($type == 3) {
                return $this->l('Brand');
            } elseif ($type == 4) {
                return $this->l('Supplier');
            } elseif ($type == 5) {
                return $this->l('Customer');
            } elseif ($type == 6) {
                return $this->l('Attribute group');
            } elseif ($type == 7) {
                return $this->l('Attribute');
            } elseif ($type == 8) {
                return $this->l('Feature');
            }
        }

        return false;
    }

    /**
     * To hide add new button from history
     *
     * @return void
     */
    public function initToolbar()
    {
        parent::initToolbar();
        unset($this->toolbar_btn['new']);
    }
}
