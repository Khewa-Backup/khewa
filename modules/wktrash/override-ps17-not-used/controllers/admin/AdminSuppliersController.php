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
class AdminSuppliersController extends AdminSuppliersControllerCore
{
    public function postProcess()
    {
        if (Module::isEnabled('wktrash')) {
            if (Tools::isSubmit('delete' . $this->table)) {
                if (!($obj = $this->loadObject(true))) {
                    return;
                } elseif (SupplyOrder::supplierHasPendingOrders($obj->id)) {
                    $this->errors[] = $this->trans(
                        'It is not possible to delete a supplier if there are pending supplier orders.',
                        [],
                        'Admin.Catalog.Notification'
                    );
                } else {
                    // Override start
                    require_once _PS_MODULE_DIR_ . '/wktrash/classes/WkTrashRequiredClasses.php';
                    $objDeletedSupplier = new WkDeletedSupplier();
                    $objDeletedSupplier->getSupplierDetailBeforeDelete($obj->id);
                    // override end

                    // delete all product_supplier linked to this supplier
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

    public function processBulkDelete()
    {
        if (Module::isEnabled('wktrash')) {
            // Override start
            require_once _PS_MODULE_DIR_ . '/wktrash/classes/WkTrashRequiredClasses.php';
            $objDeletedSupplier = new WkDeletedSupplier();
            if (is_array($this->boxes) && !empty($this->boxes)) {
                foreach ($this->boxes as $idSupplier) {
                    if (!empty($idSupplier) && $idSupplier) {
                        $objDeletedSupplier->getSupplierDetailBeforeDelete($idSupplier);
                    }
                }
            }
            // override end
        }
        parent::processBulkDelete();
    }
}
