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
class AdminWkDeletedAttributeGroupsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->lang = false;
        $this->table = 'wk_deleted_attribute_group';
        $this->className = 'WkDeletedAttributeGroup';
        $this->identifier = 'id_wk_deleted_attribute_group';
        $this->list_no_link = true;
        parent::__construct();

        $this->_select = '`id_wk_deleted_attribute_group` as temp_deleted_attribute_group_id';
        $this->fields_list = [
            'id_wk_deleted_attribute_group' => [
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'id_attribute_group' => [
                'title' => $this->l('Attribute group ID'),
                'align' => 'center',
                'class' => 'fixed-width-xl',
            ],
            'attribute_group_name' => [
                'title' => $this->l('Attribute group name'),
                'align' => 'center',
                'class' => 'fixed-width-xxl',
            ],
            'date_add' => [
                'title' => $this->l('Date'),
                'align' => 'center',
                'class' => 'fixed-width-xxl',
            ],
            'temp_deleted_attribute_group_id' => [
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
     * To display restore button on deleted attribute group list
     *
     * @param [int] $idDeletedAttributeGroup
     *
     * @return html
     */
    public function getRestoreButton($idDeletedAttributeGroup)
    {
        if ($idDeletedAttributeGroup) {
            $this->context->smarty->assign([
                'idDeletedEntity' => $idDeletedAttributeGroup,
                'entityTable' => $this->table,
            ]);

            return $this->context->smarty->fetch(
                _PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/restore-button.tpl'
            );
        }

        return false;
    }

    /**
     * To render list for deleted attribute group
     *
     * @return void
     */
    public function renderList()
    {
        $this->addRowAction('delete');

        return parent::renderList();
    }

    /**
     * To hide add new button from deleted attribute group list
     *
     * @return void
     */
    public function initToolbar()
    {
        parent::initToolbar();
        unset($this->toolbar_btn['new']);
    }

    /**
     * To restore the data of attribute groups.
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
                $idDeletedAttributeGroup = Tools::getValue('restoreButton' . $this->table);
                if ($idDeletedAttributeGroup) {
                    $this->restoreAttributeGroupAfterDeletion($idDeletedAttributeGroup);
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
            foreach ($this->boxes as $idDeletedAttributeGroup) {
                if (!empty($idDeletedAttributeGroup) && $idDeletedAttributeGroup) {
                    $this->restoreAttributeGroupAfterDeletion($idDeletedAttributeGroup);
                }
            }
            if (empty($this->context->controller->errors)) {
                $index = count($this->_conf);
                Tools::redirectAdmin(
                    AdminController::$currentIndex . '&token=' . $this->context->controller->token . '&conf=' . $index
                );
            }
        } else {
            $error = $this->l('You must have select at least one attribute group to restore.');
            $this->context->controller->errors[] = $error;
        }
    }

    public function restoreAttributeGroupAfterDeletion($idDeletedAttributeGroup)
    {
        if (!empty($idDeletedAttributeGroup) && $idDeletedAttributeGroup) {
            $objDeletdAttributeGroup = new WkDeletedAttributeGroup($idDeletedAttributeGroup);
            if (Validate::isLoadedObject($objDeletdAttributeGroup)) {
                $attributeGroupInfo = $objDeletdAttributeGroup->getDeletedAttributeGroupDetail(
                    $idDeletedAttributeGroup
                );
                if (!empty($attributeGroupInfo) && $attributeGroupInfo) {
                    $idNewAttributeGroup = $this->restoreDeletedAttributeGroup($attributeGroupInfo);
                    if (!empty($idNewAttributeGroup) && $idNewAttributeGroup) {
                        $objEntityHistory = new WkEntityRestoreHistory();
                        $historyId = $objEntityHistory->getIdByOldEntityId(
                            $attributeGroupInfo['id_attribute_group'],
                            6
                        );
                        if ($historyId) {
                            $objEntityHistory->updateEntityHistory($historyId, $idNewAttributeGroup);
                        }
                        $objDeletdAttributeGroup->delete();
                    }
                }
            }
        }
    }

    public function restoreDeletedAttributeGroup($attributeGroupInfo)
    {
        if (!empty($attributeGroupInfo) && $attributeGroupInfo) {
            $attributeGroup = new AttributeGroup(); // Restore AttributeGroup with new ID
            if (!Configuration::get('WK_RESTORE_ENTITY_NEW_ID')) {
                // Restore AttributeGroup with Old ID
                $wkResult = WkDeletedAttributeGroup::insertDataInPrimaryTable($attributeGroupInfo);
                if ($wkResult) {
                    $attributeGroup = new AttributeGroup($attributeGroupInfo['id_attribute_group']);
                }
            }

            $attributeGroup->name = [];
            $attributeGroup->public_name = [];
            // Decode all feature info
            $attributeGroupInfo['shop'] = json_decode($attributeGroupInfo['shop'], true);
            $attributeGroupInfo['lang'] = json_decode($attributeGroupInfo['lang'], true);
            $attributeGroupInfo['attribute_value'] = json_decode($attributeGroupInfo['attribute_value'], true);

            $attributeGroup->is_color_group = $attributeGroupInfo['is_color_group'];
            $attributeGroup->group_type = $attributeGroupInfo['group_type'];
            $attributeGroup->position = AttributeGroup::getHigherPosition() + 1;

            foreach (Language::getLanguages() as $lang) {
                $attributeGroup->name[$lang['id_lang']] = $attributeGroupInfo['lang'][$lang['id_lang']]['name'];
                $attributeGroup->public_name[$lang['id_lang']] =
                $attributeGroupInfo['lang'][$lang['id_lang']]['public_name'];
            }
            $attributeGroup->save();

            if ($attributeGroup->id) {
                if (!empty($attributeGroupInfo['attribute_value']) && $attributeGroupInfo['attribute_value']) {
                    foreach ($attributeGroupInfo['attribute_value'] as $attributeValue) {
                        if (!empty($attributeValue) && $attributeValue) {
                            WkDeletedAttribute::insertDataWithOldId(
                                $attributeValue['id_attribute'],
                                $attributeGroup->id
                            );

                            if (version_compare(_PS_VERSION_, '8.0.0', '>=')) {
                                $objAttribute = new ProductAttribute($attributeValue['id_attribute']);
                                $objAttribute->id_attribute_group = $attributeGroup->id;
                                $objAttribute->color = $attributeValue['color'];
                                $objAttribute->position = ProductAttribute::getHigherPosition($attributeGroup->id) + 1;
                            } else {
                                $objAttribute = new Attribute($attributeValue['id_attribute']);
                                $objAttribute->id_attribute_group = $attributeGroup->id;
                                $objAttribute->color = $attributeValue['color'];
                                $objAttribute->position = Attribute::getHigherPosition($attributeGroup->id) + 1;
                            }
                            if ($attributeValue['lang']) {
                                foreach (Language::getLanguages() as $keyLang => $lang) {
                                    $objAttribute->name[$lang['id_lang']] = $attributeValue['lang'][$keyLang]['name'];
                                }
                            }
                            $objAttribute->save();
                            if ($objAttribute->id) {
                                if ((bool) Configuration::get('WK_ATTRIBUTE_APPLY_ON_PRODUCT')) {
                                    if (!empty($attributeValue['combination']) && $attributeValue['combination']) {
                                        $objDeletdAttributeGroup = new WkDeletedAttributeGroup();
                                        foreach ($attributeValue['combination'] as $combination) {
                                            if (!empty($combination && $combination)) {
                                                $objCombination = new Combination($combination['id_product_attribute']);
                                                if (Validate::isLoadedObject($objCombination)) {
                                                    WkDeletedAttributeGroup::setAttributeCombination(
                                                        $objAttribute->id,
                                                        $combination['id_product_attribute']
                                                    );
                                                } else {
                                                    $objDeletdAttributeGroup->setCombinationByAttributeId(
                                                        $objAttribute->id,
                                                        $combination
                                                    );
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                return $attributeGroup->id;
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
