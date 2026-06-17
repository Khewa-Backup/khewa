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
class AdminWkDeletedManufacturersController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->lang = false;
        $this->table = 'wk_deleted_manufacturer';
        $this->className = 'WkDeletedManufacturer';
        $this->identifier = 'id_wk_deleted_manufacturer';
        $this->list_no_link = true;
        parent::__construct();

        $this->_select = '`id_wk_deleted_manufacturer` as temp_deleted_manufacturer_id';
        $this->fields_list = [
            'id_wk_deleted_manufacturer' => [
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'id_manufacturer' => [
                'title' => $this->l('Brand ID'),
                'align' => 'center',
                'class' => 'fixed-width-xl',
            ],
            'name' => [
                'title' => $this->l('Brand name'),
                'align' => 'center',
                'class' => 'fixed-width-xxl',
            ],
            'date_add' => [
                'title' => $this->l('Date'),
                'align' => 'center',
                'class' => 'fixed-width-xxl',
            ],
            'temp_deleted_manufacturer_id' => [
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
     * To display restore button on deleted manufacturer list
     *
     * @param [int] $idDeletedManufacturer
     *
     * @return html
     */
    public function getRestoreButton($idDeletedManufacturer)
    {
        if ($idDeletedManufacturer) {
            $this->context->smarty->assign([
                'idDeletedEntity' => $idDeletedManufacturer,
                'entityTable' => $this->table,
            ]);

            return $this->context->smarty->fetch(
                _PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/restore-button.tpl'
            );
        }

        return false;
    }

    /**
     * To render list for deleted manufacturer
     *
     * @return void
     */
    public function renderList()
    {
        $this->addRowAction('delete');

        return parent::renderList();
    }

    /**
     * To hide add new button from deleted manufacturer list
     *
     * @return void
     */
    public function initToolbar()
    {
        parent::initToolbar();
        unset($this->toolbar_btn['new']);
    }

    /**
     * To restore the data of manufacturers.
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
                $idDeletedManufacturer = Tools::getValue('restoreButton' . $this->table);
                if ($idDeletedManufacturer) {
                    $this->restoreManufacturerAfterDeletion($idDeletedManufacturer);
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
            foreach ($this->boxes as $idDeletedManufacturer) {
                if (!empty($idDeletedManufacturer) && $idDeletedManufacturer) {
                    $this->restoreManufacturerAfterDeletion($idDeletedManufacturer);
                }
            }
            if (empty($this->context->controller->errors)) {
                $index = count($this->_conf);
                Tools::redirectAdmin(
                    AdminController::$currentIndex . '&token=' . $this->context->controller->token . '&conf=' . $index
                );
            }
        } else {
            $this->context->controller->errors[] = $this->l('You must have select at least one brand to restore.');
        }
    }

    public function restoreManufacturerAfterDeletion($idDeletedManufacturer)
    {
        if (!empty($idDeletedManufacturer) && $idDeletedManufacturer) {
            $objDeletdManufacturer = new WkDeletedManufacturer($idDeletedManufacturer);
            if (Validate::isLoadedObject($objDeletdManufacturer)) {
                $manufacturerInfo = $objDeletdManufacturer->getDeletedManufacturerDetail($idDeletedManufacturer);
                if (!empty($manufacturerInfo) && $manufacturerInfo) {
                    $idNewManufacturer = $this->restoreDeletedManufacturer($manufacturerInfo);
                    if (!empty($idNewManufacturer) && $idNewManufacturer) {
                        $objEntityHistory = new WkEntityRestoreHistory();
                        $historyId = $objEntityHistory->getIdByOldEntityId($manufacturerInfo['id_manufacturer'], 3);
                        if ($historyId) {
                            $objEntityHistory->updateEntityHistory($historyId, $idNewManufacturer);
                        }
                        $objDeletdManufacturer->delete();
                    }
                }
            }
        }
    }

    public function restoreDeletedManufacturer($manufacturerInfo)
    {
        if (!empty($manufacturerInfo) && $manufacturerInfo) {
            $manufacturer = new Manufacturer(); // Restore manufacturer with new ID
            if (!Configuration::get('WK_RESTORE_ENTITY_NEW_ID')) {
                // Restore manufacturer with Old ID
                $wkResult = WkDeletedManufacturer::insertDataInPrimaryTable($manufacturerInfo);
                if ($wkResult) {
                    $manufacturer = new Manufacturer($manufacturerInfo['id_manufacturer']);
                }
            }

            $manufacturer->description = [];
            $manufacturer->short_description = [];
            $manufacturer->meta_title = [];
            $manufacturer->meta_description = [];
            $manufacturer->meta_keywords = [];

            // Decode all manufacturer info
            $manufacturerInfo['shop'] = json_decode($manufacturerInfo['shop'], true);
            $manufacturerInfo['lang'] = json_decode($manufacturerInfo['lang'], true);
            $manufacturerInfo['address'] = json_decode($manufacturerInfo['address'], true);
            $manufacturerInfo['product_manufacturer'] = json_decode($manufacturerInfo['product_manufacturer'], true);

            foreach (Language::getLanguages() as $lang) {
                $manufacturer->description[$lang['id_lang']] = $manufacturerInfo['lang'][$lang['id_lang']]['description'];
                $manufacturer->short_description[$lang['id_lang']] = $manufacturerInfo['lang'][$lang['id_lang']]['short_description'];
                $manufacturer->meta_title[$lang['id_lang']] = $manufacturerInfo['lang'][$lang['id_lang']]['meta_title'];
                $manufacturer->meta_description[$lang['id_lang']] = $manufacturerInfo['lang'][$lang['id_lang']]['meta_description'];
                $manufacturer->meta_keywords[$lang['id_lang']] = $manufacturerInfo['lang'][$lang['id_lang']]['meta_keywords'];
            }

            $manufacturer->name = $manufacturerInfo['name'];
            $manufacturer->active = $manufacturerInfo['active'];
            $manufacturer->save();

            if ($manufacturer->id) {
                $source = _PS_MODULE_DIR_ . $this->module->name . '/views/img/manufacturer/' .
                $manufacturerInfo['id_manufacturer'] . '.jpg';
                $destination = _PS_MANU_IMG_DIR_ . $manufacturer->id;
                if (file_exists($source)) {
                    if ($imageTypes = ImageType::getImagesTypes('manufacturers')) {
                        foreach ($imageTypes as $imageType) {
                            ImageManager::resize(
                                $source,
                                $destination . '-' . Tools::stripslashes($imageType['name']) . '.jpg',
                                $imageType['width'],
                                $imageType['height']
                            );
                        }
                        ImageManager::resize($source, $destination . '.jpg');
                    }
                }

                $objDeletdManufacturer = new WkDeletedManufacturer();
                if ($manufacturerInfo['address']) {
                    foreach ($manufacturerInfo['address'] as $address) {
                        $objDeletdManufacturer->updateAddressManufacturerId($manufacturer->id, $address);
                    }
                }
                if ((bool) Configuration::get('WK_BRAND_APPLY_ON_PRODUCT')) {
                    if (!empty($manufacturerInfo['product_manufacturer'])
                        && $manufacturerInfo['product_manufacturer']) {
                        foreach ($manufacturerInfo['product_manufacturer'] as $productManufacturer) {
                            if ($productManufacturer) {
                                $objDeletdManufacturer->updateProductManufacturerId(
                                    $manufacturer->id,
                                    $productManufacturer
                                );
                            }
                        }
                    }
                } else {
                    if (!empty($manufacturerInfo['product_manufacturer'])) {
                        foreach ($manufacturerInfo['product_manufacturer'] as $productManufacturer) {
                            if ($productManufacturer) {
                                $idProduct = $productManufacturer['id_product'];
                                $objDeletdManufacturer->updateProductManufacturerIdZero($idProduct);
                            }
                        }
                    }
                }

                return $manufacturer->id;
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
