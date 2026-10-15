<?php
 
class AdminSuppliersController extends AdminSuppliersControllerCore
{
    /*
    * module: wktrash
    * date: 2022-07-31 22:10:09
    * version: 4.0.1
    */
    public function postProcess()
    {
        if (Module::isEnabled('wktrash')) {
            if (Tools::isSubmit('delete' . $this->table)) {
                if (!($obj = $this->loadObject(true))) {
                    return;
                } elseif (SupplyOrder::supplierHasPendingOrders($obj->id)) {
                    $this->errors[] = $this->trans(
                        'It is not possible to delete a supplier if there are pending supplier orders.',
                        array(),
                        'Admin.Catalog.Notification'
                    );
                } else {
                    require_once _PS_MODULE_DIR_.'/wktrash/classes/WkTrashRequiredClasses.php';
                    $objDeletedSupplier = new WkDeletedSupplier();
                    $objDeletedSupplier->getSupplierDetailBeforeDelete($obj->id);
                    Db::getInstance()->execute(
                        'DELETE FROM `' . _DB_PREFIX_ . 'product_supplier` WHERE `id_supplier`=' . (int) $obj->id
                    );
                    $id_address = Address::getAddressIdBySupplierId($obj->id);
                    $address = new Address($id_address);
                    if (Validate::isLoadedObject($address)) {
                        $address->deleted = 1;
                        $address->save();
                    }
                    return parent::postProcess();
                }
            } else {
                return parent::postProcess();
            }
        }
    }
    /*
    * module: wktrash
    * date: 2022-07-31 22:10:09
    * version: 4.0.1
    */
    public function processBulkDelete()
    {
        if (Module::isEnabled('wktrash')) {
            require_once _PS_MODULE_DIR_.'/wktrash/classes/WkTrashRequiredClasses.php';
            $objDeletedSupplier = new WkDeletedSupplier();
            if (is_array($this->boxes) && !empty($this->boxes)) {
                foreach ($this->boxes as $idSupplier) {
                    if (!empty($idSupplier) && $idSupplier) {
                        $objDeletedSupplier->getSupplierDetailBeforeDelete($idSupplier);
                    }
                }
            }
        }
        parent::processBulkDelete();
    }
    /*
    * module: ets_superspeed
    * date: 2026-01-17 12:14:10
    * version: 2.1.2
    */
    protected function afterImageUpload()
    {
        parent::afterImageUpload();
        if(Module::isInstalled('ets_superspeed') && Module::isEnabled('ets_superspeed'))
        {
            $id_supplier = (int)Tools::getValue('id_supplier');
            Ets_superspeed_compressor_image::optimizeImageSupplier($id_supplier);
        }
    }
}