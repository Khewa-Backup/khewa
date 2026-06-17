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
class AdminWkDeletedAttributesController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->lang = false;
        $this->table = 'wk_deleted_attribute';
        $this->className = 'WkDeletedAttribute';
        $this->identifier = 'id_wk_deleted_attribute';
        $this->list_no_link = true;
        parent::__construct();

        $this->_select = '`id_wk_deleted_attribute` as temp_deleted_attribute_id';
        $this->fields_list = [
            'id_wk_deleted_attribute' => [
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'id_attribute' => [
                'title' => $this->l('Attribute ID'),
                'align' => 'center',
                'class' => 'fixed-width-xl',
            ],
            'attribute_name' => [
                'title' => $this->l('Attribute name'),
                'align' => 'center',
                'class' => 'fixed-width-xxl',
            ],
            'date_add' => [
                'title' => $this->l('Date'),
                'align' => 'center',
                'class' => 'fixed-width-xxl',
            ],
            'temp_deleted_attribute_id' => [
                'title' => $this->l('Restore'),
                'align' => 'center',
                'class' => 'fixed-width-xl',
                'search' => false,
                'callback' => 'getRestoreButton',
            ],
        ];

        $this->bulk_actions = [
            'delete' => [
                'text' => $this->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected items permanently?'),
            ],
            'restore' => [
                'text' => $this->l('Restore selected'),
                'icon' => 'icon-undo',
                'confirm' => $this->l('Restore selected items?'),
            ],
        ];
        $index = count($this->_conf);
        $this->_conf[$index] = $this->l('Successful restore.');
    }

    /**
     * To display restore button on deleted attribute list
     *
     * @param [int] $idDeletedAttribute
     *
     * @return html
     */
    public function getRestoreButton($idDeletedAttribute)
    {
        if ($idDeletedAttribute) {
            $this->context->smarty->assign([
                'idDeletedEntity' => $idDeletedAttribute,
                'entityTable' => $this->table,
            ]);

            return $this->context->smarty->fetch(
                _PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/restore-button.tpl'
            );
        }

        return false;
    }

    /**
     * To render list for deleted attribute
     *
     * @return void
     */
    public function renderList()
    {
        $this->addRowAction('delete');

        return parent::renderList();
    }

    /**
     * To hide add new button from deleted attribute list
     *
     * @return void
     */
    public function initToolbar()
    {
        parent::initToolbar();
        unset($this->toolbar_btn['new']);
    }

    /**
     * To restore the data of attributes.
     *
     * @return void
     */
    public function postProcess()
    {
        if (!$this->loadObject(true)) {
            return;
        }

        if (Tools::issubmit('restoreButton' . $this->table)) {
            if (Tools::getValue('restoreButton' . $this->table)) {
                $idDeletedAttribute = Tools::getValue('restoreButton' . $this->table);
                if ($idDeletedAttribute) {
                    $this->restoreAttributeAfterDeletion($idDeletedAttribute);
                }
                if (empty($this->context->controller->errors)) {
                    $index = count($this->_conf);
                    Tools::redirectAdmin(
                        AdminController::$currentIndex . '&token=' . $this->context->controller->token . '&conf=' . $index
                    );
                }
            }
        }
        parent::postProcess();
    }

    public function processBulkRestore()
    {
        if (is_array($this->boxes) && !empty($this->boxes)) {
            foreach ($this->boxes as $idDeletedAttribute) {
                if (!empty($idDeletedAttribute) && $idDeletedAttribute) {
                    $this->restoreAttributeAfterDeletion($idDeletedAttribute);
                }
            }
            if (empty($this->context->controller->errors)) {
                $index = count($this->_conf);
                Tools::redirectAdmin(
                    AdminController::$currentIndex . '&token=' . $this->context->controller->token . '&conf=' . $index
                );
            }
        } else {
            $this->context->controller->errors[] = $this->l('You must have select at least one attribute to restore.');
        }
    }

    public function restoreAttributeAfterDeletion($idDeletedAttribute)
    {
        if (!empty($idDeletedAttribute) && $idDeletedAttribute) {
            $objDeletdAttribute = new WkDeletedAttribute($idDeletedAttribute);
            if (Validate::isLoadedObject($objDeletdAttribute)) {
                $attributeInfo = $objDeletdAttribute->getDeletedAttributeDetail($idDeletedAttribute);
                if (!empty($attributeInfo) && $attributeInfo) {
                    $groupExist = WkDeletedAttributeGroup::attributeGroupExistsAfterRestore(
                        $attributeInfo['id_attribute_group']
                    );
                    if ($groupExist) {
                        $groupId = $groupExist;
                    } else {
                        $groupId = false;
                    }

                    $idNewAttribute = $this->restoreDeletedAttribute($attributeInfo, $groupId);
                    if (!empty($idNewAttribute) && $idNewAttribute) {
                        $objEntityHistory = new WkEntityRestoreHistory();
                        $historyId = $objEntityHistory->getIdByOldEntityId($attributeInfo['id_attribute'], 7);
                        if ($historyId) {
                            $objEntityHistory->updateEntityHistory($historyId, $idNewAttribute);
                        }
                        $objDeletdAttribute->delete();
                    }
                }
            }
        }
    }

    public function restoreDeletedAttribute($attributeInfo, $groupId)
    {
        if (!$groupId) {
            $error = $this->l('You can\'t restore attribute value without it\'s attribute group.');
            $this->context->controller->errors = $error;

            return false;
        }

        if (!empty($attributeInfo) && $attributeInfo) {
            $attribute = new Attribute(); // Restore Attribute with new ID
            if (!Configuration::get('WK_RESTORE_ENTITY_NEW_ID')) {
                // Restore Attribute with Old ID
                $wkResult = WkDeletedAttribute::insertDataInPrimaryTable($attributeInfo);
                if ($wkResult) {
                    $attribute = new Attribute($attributeInfo['id_attribute']);
                }
            }

            $attribute->name = [];
            // Decode all feature info
            $attributeInfo['shop'] = json_decode($attributeInfo['shop'], true);
            $attributeInfo['lang'] = json_decode($attributeInfo['lang'], true);
            $attributeInfo['product_attribute'] = json_decode($attributeInfo['product_attribute'], true);

            $attribute->id_attribute_group = $groupId;
            $attribute->color = $attributeInfo['color'];
            $attribute->position = Attribute::getHigherPosition($groupId) + 1;

            foreach (Language::getLanguages() as $lang) {
                $attribute->name[$lang['id_lang']] = $attributeInfo['lang'][$lang['id_lang']]['name'];
            }
            $attribute->save();
            if ($attribute->id) {
                if ((bool) Configuration::get('WK_ATTRIBUTE_APPLY_ON_PRODUCT')) {
                    if (!empty($attributeInfo['product_attribute']) && $attributeInfo['product_attribute']) {
                        $objDeletdAttributeGroup = new WkDeletedAttributeGroup();
                        foreach ($attributeInfo['product_attribute'] as $combination) {
                            $objDeletdAttributeGroup->setCombinationByAttributeId($attribute->id, $combination);
                        }
                    }
                }

                return $attribute->id;
            }
        }
    }

    /**
     * To set JS & CSS for controller
     *
     * @return void
     */
    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        Media::addJsDef([
            'restore_selected_item' => $this->l('Restore selected item?'),
        ]);
        $this->context->controller->addJs(_PS_MODULE_DIR_ . $this->module->name . '/views/js/wk-restore-entity.js');
    }
}
