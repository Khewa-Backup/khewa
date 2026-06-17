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
class AdminWkDeletedSuppliersController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->lang = false;
        $this->table = 'wk_deleted_supplier';
        $this->className = 'WkDeletedSupplier';
        $this->identifier = 'id_wk_deleted_supplier';
        $this->list_no_link = true;
        parent::__construct();

        $this->_select = '`id_wk_deleted_supplier` as temp_deleted_supplier_id';
        $this->fields_list = [
            'id_wk_deleted_supplier' => [
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'id_supplier' => [
                'title' => $this->l('Supplier ID'),
                'align' => 'center',
                'class' => 'fixed-width-xl',
            ],
            'name' => [
                'title' => $this->l('Supplier name'),
                'align' => 'center',
                'class' => 'fixed-width-xxl',
            ],
            'date_add' => [
                'title' => $this->l('Date'),
                'align' => 'center',
                'class' => 'fixed-width-xxl',
            ],
            'temp_deleted_supplier_id' => [
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
     * To display restore button on deleted supplier list
     *
     * @param [int] $idDeletedSupplier
     *
     * @return html
     */
    public function getRestoreButton($idDeletedSupplier)
    {
        if ($idDeletedSupplier) {
            $this->context->smarty->assign([
                'idDeletedEntity' => $idDeletedSupplier,
                'entityTable' => $this->table,
            ]);

            return $this->context->smarty->fetch(
                _PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/restore-button.tpl'
            );
        }

        return false;
    }

    /**
     * To render list for deleted supplier
     *
     * @return void
     */
    public function renderList()
    {
        $this->addRowAction('delete');

        return parent::renderList();
    }

    /**
     * To hide add new button from deleted supplier list
     *
     * @return void
     */
    public function initToolbar()
    {
        parent::initToolbar();
        unset($this->toolbar_btn['new']);
    }

    /**
     * To restore the data of suppliers.
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
                $idDeletedSupplier = Tools::getValue('restoreButton' . $this->table);
                if (!empty($idDeletedSupplier) && $idDeletedSupplier) {
                    $this->restoreSupplierAfterDeletion($idDeletedSupplier);
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
            foreach ($this->boxes as $idDeletedSupplier) {
                if (!empty($idDeletedSupplier) && $idDeletedSupplier) {
                    $this->restoreSupplierAfterDeletion($idDeletedSupplier);
                }
            }
            if (empty($this->context->controller->errors)) {
                $index = count($this->_conf);
                Tools::redirectAdmin(
                    AdminController::$currentIndex . '&token=' . $this->context->controller->token . '&conf=' . $index
                );
            }
        } else {
            $this->context->controller->errors[] = $this->l('You must have select at least one supplier to restore.');
        }
    }

    public function restoreSupplierAfterDeletion($idDeletedSupplier)
    {
        if (!empty($idDeletedSupplier) && $idDeletedSupplier) {
            $objDeletdSupplier = new WkDeletedSupplier($idDeletedSupplier);
            if (Validate::isLoadedObject($objDeletdSupplier)) {
                $supplierInfo = $objDeletdSupplier->getDeletedSupplierDetail($idDeletedSupplier);
                if (!empty($supplierInfo) && $supplierInfo) {
                    $idNewSupplier = $this->restoreDeletedSupplier($supplierInfo);
                    if (!empty($idNewSupplier) && $idNewSupplier) {
                        $objEntityHistory = new WkEntityRestoreHistory();
                        $historyId = $objEntityHistory->getIdByOldEntityId($supplierInfo['id_supplier'], 4);
                        if ($historyId) {
                            $objEntityHistory->updateEntityHistory($historyId, $idNewSupplier);
                        }
                        $objDeletdSupplier->delete();
                    }
                }
            }
        }
    }

    public function restoreDeletedSupplier($supplierInfo)
    {
        if (!empty($supplierInfo) && $supplierInfo) {
            $supplier = new Supplier(); // Restore Supplier with new ID
            if (!Configuration::get('WK_RESTORE_ENTITY_NEW_ID')) {
                // Restore Supplier with Old ID
                $wkResult = WkDeletedSupplier::insertDataInPrimaryTable($supplierInfo);
                if ($wkResult) {
                    $supplier = new Supplier($supplierInfo['id_supplier']);
                }
            }

            $supplier->description = [];
            $supplier->meta_title = [];
            $supplier->meta_description = [];
            $supplier->meta_keywords = [];

            // Decode all supplier info
            $supplierInfo['shop'] = json_decode($supplierInfo['shop'], true);
            $supplierInfo['lang'] = json_decode($supplierInfo['lang'], true);
            $supplierInfo['address'] = json_decode($supplierInfo['address'], true);
            $supplierInfo['product_supplier'] = json_decode($supplierInfo['product_supplier'], true);
            // $supplierInfo['supplier_order'] = json_decode($supplierInfo['supplier_order'], true);
            foreach (Language::getLanguages() as $lang) {
                $supplier->description[$lang['id_lang']] = $supplierInfo['lang'][$lang['id_lang']]['description'];
                $supplier->meta_title[$lang['id_lang']] = $supplierInfo['lang'][$lang['id_lang']]['meta_title'];
                $supplier->meta_description[$lang['id_lang']] = $supplierInfo['lang'][$lang['id_lang']]['meta_description'];
                $supplier->meta_keywords[$lang['id_lang']] = $supplierInfo['lang'][$lang['id_lang']]['meta_keywords'];
            }
            $supplier->name = $supplierInfo['name'];
            $supplier->active = $supplierInfo['active'];
            $supplier->save();

            if ($supplier->id) {
                $source = _PS_MODULE_DIR_ . $this->module->name . '/views/img/supplier/' .
                $supplierInfo['id_supplier'] . '.jpg';
                $destination = _PS_SUPP_IMG_DIR_ . $supplier->id;
                if (file_exists($source)) {
                    if ($imageTypes = ImageType::getImagesTypes('suppliers')) {
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

                $objDeletdSupplier = new WkDeletedSupplier();
                $objDeletdSupplier->updateDefaultSupplierId($supplierInfo['id_supplier'], $supplier->id);

                if (!empty($supplierInfo['address']) && $supplierInfo['address']) {
                    $objDeletdSupplier->updateAddressSupplierId($supplier->id, $supplierInfo['address']);
                }

                if ((bool) Configuration::get('WK_SUPPLIER_APPLY_ON_PRODUCT')) {
                    if (!empty($supplierInfo['product_supplier']) && $supplierInfo['product_supplier']) {
                        foreach ($supplierInfo['product_supplier'] as $productSupplier) {
                            if ($productSupplier && !empty($productSupplier)) {
                                $idProduct = WkDeletedProduct::productExistsAfterRestore(
                                    $productSupplier['id_product']
                                );
                                $objProductSupplier = new ProductSupplier();
                                $objProductSupplier->id_product = $idProduct;
                                $objProductSupplier->id_product_attribute = $productSupplier['id_product_attribute'];
                                $objProductSupplier->id_supplier = $supplier->id;
                                $objProductSupplier->product_supplier_reference = $productSupplier['product_supplier_reference'];
                                $objProductSupplier->product_supplier_price_te = $productSupplier['product_supplier_price_te'];
                                $objProductSupplier->id_currency = $productSupplier['id_currency'];
                                $objProductSupplier->save();
                            }
                        }
                    } elseif ($idProductSupplier = $objDeletdSupplier->getIdProductSupplierBySupplierId(
                        $supplierInfo['id_supplier']
                    )) {
                        $objProductSupplier = new ProductSupplier($idProductSupplier);
                        if (Validate::isLoadedObject($objProductSupplier)) {
                            $objProductSupplier->id_supplier = $supplier->id;
                            $objProductSupplier->save();
                        }
                    }
                }

                return $supplier->id;
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
