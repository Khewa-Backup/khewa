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
class AdminWkDeletedFeaturesController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->lang = false;
        $this->table = 'wk_deleted_feature';
        $this->className = 'WkDeletedFeature';
        $this->identifier = 'id_wk_deleted_feature';
        $this->list_no_link = true;
        parent::__construct();

        $this->_select = '`id_wk_deleted_feature` as temp_deleted_feature_id';
        $this->fields_list = [
            'id_wk_deleted_feature' => [
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'id_feature' => [
                'title' => $this->l('Feature ID'),
                'align' => 'center',
                'class' => 'fixed-width-xl',
            ],
            'feature_name' => [
                'title' => $this->l('Feature name'),
                'align' => 'center',
                'class' => 'fixed-width-xxl',
            ],
            'date_add' => [
                'title' => $this->l('Date'),
                'align' => 'center',
                'class' => 'fixed-width-xxl',
            ],
            'temp_deleted_feature_id' => [
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
     * To display restore button on deleted feature list
     *
     * @param [int] $idDeletedFeature
     *
     * @return html
     */
    public function getRestoreButton($idDeletedFeature)
    {
        if ($idDeletedFeature) {
            $this->context->smarty->assign([
                'idDeletedEntity' => $idDeletedFeature,
                'entityTable' => $this->table,
            ]);

            return $this->context->smarty->fetch(
                _PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/restore-button.tpl'
            );
        }

        return false;
    }

    /**
     * To render list for deleted feature
     *
     * @return void
     */
    public function renderList()
    {
        $this->addRowAction('delete');

        return parent::renderList();
    }

    /**
     * To hide add new button from deleted feature list
     *
     * @return void
     */
    public function initToolbar()
    {
        parent::initToolbar();
        unset($this->toolbar_btn['new']);
    }

    /**
     * To restore the data of features.
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
                $idDeletedFeature = Tools::getValue('restoreButton' . $this->table);
                if ($idDeletedFeature) {
                    $this->restoreFeatureAfterDeletion($idDeletedFeature);
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
            foreach ($this->boxes as $idDeletedFeature) {
                if (!empty($idDeletedFeature) && $idDeletedFeature) {
                    $this->restoreFeatureAfterDeletion($idDeletedFeature);
                }
            }
            if (empty($this->context->controller->errors)) {
                $index = count($this->_conf);
                Tools::redirectAdmin(
                    AdminController::$currentIndex . '&token=' . $this->context->controller->token . '&conf=' . $index
                );
            }
        } else {
            $this->context->controller->errors[] = $this->l('You must have select at least one feature to restore.');
        }
    }

    public function restoreFeatureAfterDeletion($idDeletedFeature)
    {
        if (!empty($idDeletedFeature) && $idDeletedFeature) {
            $objDeletdFeature = new WkDeletedFeature($idDeletedFeature);
            if (Validate::isLoadedObject($objDeletdFeature)) {
                $featureInfo = $objDeletdFeature->getDeletedFeatureDetail($idDeletedFeature);
                if (!empty($featureInfo) && $featureInfo) {
                    $idNewFeature = $this->restoreDeletedFeature($featureInfo);
                    if (!empty($idNewFeature) && $idNewFeature) {
                        $objEntityHistory = new WkEntityRestoreHistory();
                        $historyId = $objEntityHistory->getIdByOldEntityId($featureInfo['id_feature'], 8);
                        if ($historyId) {
                            $objEntityHistory->updateEntityHistory($historyId, $idNewFeature);
                        }
                        $objDeletdFeature->delete();
                    }
                }
            }
        }
    }

    public function restoreDeletedFeature($featureInfo)
    {
        if (!empty($featureInfo) && $featureInfo) {
            $objDeletdFeature = new WkDeletedFeature();
            $feature = new Feature(); // Restore Feature with new ID
            if (!Configuration::get('WK_RESTORE_ENTITY_NEW_ID')) {
                // Restore Feature with Old ID
                $wkResult = WkDeletedFeature::insertDataInPrimaryTable($featureInfo);
                if ($wkResult) {
                    $feature = new Feature($featureInfo['id_feature']);
                }
            }

            $feature->name = [];
            // Decode all feature info
            $featureInfo['shop'] = json_decode($featureInfo['shop'], true);
            $featureInfo['lang'] = json_decode($featureInfo['lang'], true);
            $featureInfo['product_feature'] = json_decode($featureInfo['product_feature'], true);
            $featureInfo['feature_value'] = json_decode($featureInfo['feature_value'], true);

            foreach (Language::getLanguages() as $lang) {
                $feature->name[$lang['id_lang']] = $featureInfo['lang'][$lang['id_lang']]['name'];
            }
            $feature->position = Feature::getHigherPosition() + 1;
            $feature->save();

            if ($feature->id) {
                $featureValueIds = [];
                if (!empty($featureInfo['feature_value']) && $featureInfo['feature_value']) {
                    foreach ($featureInfo['feature_value'] as $featureValue) {
                        if (!empty($featureValue) && $featureValue) {
                            $isRestoreCustomValue = true;
                            if ($featureValue['custom']) {
                                if (!(bool) Configuration::get('WK_FEATURE_APPLY_ON_PRODUCT')) {
                                    $isRestoreCustomValue = false;
                                }
                            }
                            if ($isRestoreCustomValue) {
                                // To created value same as existing one
                                WkDeletedFeature::insertDataWithOldId($featureValue['id_feature_value'], $feature->id);
                                $objFeatureValue = new FeatureValue($featureValue['id_feature_value']);
                                $objFeatureValue->id_feature = $feature->id;
                                $objFeatureValue->custom = $featureValue['custom'];
                                if (!empty($featureValue['lang']) && $featureValue['lang']) {
                                    foreach (Language::getLanguages() as $keyLang => $lang) {
                                        $fatureValueLang = $featureValue['lang'][$keyLang]['value'];
                                        $objFeatureValue->value[$lang['id_lang']] = $fatureValueLang;
                                    }
                                }
                                $objFeatureValue->save();
                                $featureValueIds[] = $objFeatureValue->id;
                            }
                        }
                    }
                }

                if ((bool) Configuration::get('WK_FEATURE_APPLY_ON_PRODUCT')) {
                    if (!empty($featureInfo['product_feature']) && $featureInfo['product_feature']) {
                        foreach ($featureInfo['product_feature'] as $featureProduct) {
                            if ($featureProduct && !empty($featureProduct)) {
                                $objProduct = new Product($featureProduct['id_product']);
                                if (Validate::isLoadedObject($objProduct)) {
                                    $objDeletdFeature->setFeatureProduct(
                                        $feature->id,
                                        $featureProduct['id_feature_value'],
                                        $featureProduct['id_product']
                                    );
                                }
                            }
                        }
                    }
                }

                return $feature->id;
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
