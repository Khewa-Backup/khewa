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
class WkDeletedSupplier extends ObjectModel
{
    public $id_supplier;
    public $name;
    public $active;
    public $shop;
    public $lang;
    public $address;
    public $product_supplier;
    public $supplier_order;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table' => 'wk_deleted_supplier',
        'primary' => 'id_wk_deleted_supplier',
        'fields' => [
            'id_supplier' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'name' => ['type' => self::TYPE_STRING, 'validate' => 'isCatalogName', 'size' => 64],
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'shop' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'lang' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'address' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'product_supplier' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'supplier_order' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false],
        ],
    ];

    public function getSupplierDetailBeforeDelete($idSupplier)
    {
        if ($idSupplier) {
            $supplierInfo = $this->getSupplier($idSupplier);
            if ($supplierInfo) {
                $shop = $this->getSupplier($idSupplier, true);
                if ($shop) {
                    $supplierInfo['shop'] = json_encode($shop);
                }
                $lang = $this->getSupplier($idSupplier, false, true);
                if ($lang) {
                    $supplierInfo['lang'] = json_encode($lang);
                }
                $address = Address::getAddressIdBySupplierId($idSupplier);
                if ($address) {
                    $supplierInfo['address'] = json_encode($address);
                } else {
                    $supplierInfo['address'] = null;
                }
                $productSupplier = $this->getSupplierProductBySupplierId($idSupplier);
                if ($productSupplier) {
                    $supplierInfo['product_supplier'] = json_encode($productSupplier);
                } else {
                    $supplierInfo['product_supplier'] = null;
                }
                // $supplierOrder = $this->getSupplierOrders($idSupplier);
                // if ($supplierOrder && !empty($supplierOrder)) {
                //     $supplierInfo['supplier_order'] = json_encode($supplierOrder);
                // } else {
                //     $supplierInfo['supplier_order'] = null;
                // }
                $source = _PS_SUPP_IMG_DIR_ . $idSupplier . '.jpg';
                $destination = _PS_MODULE_DIR_ . 'wktrash/views/img/supplier/' . $idSupplier . '.jpg';
                if (file_exists($source)) {
                    ImageManager::resize($source, $destination);
                }
                $this->saveDeletedSupplier($supplierInfo);
            }
            $objEntityRestoreHistory = new WkEntityRestoreHistory();
            $objEntityRestoreHistory->addEntityHistory(4, $idSupplier);
        }
    }

    public function getSupplier($idSupplier, $isShop = false, $isLang = false)
    {
        if ($idSupplier) {
            if ($isShop) {
                return Db::getInstance()->executeS(
                    'SELECT * FROM `' . _DB_PREFIX_ . 'supplier_shop`
                    WHERE `id_supplier` = ' . (int) $idSupplier
                );
            } elseif ($isLang) {
                $allLang = [];
                foreach (Language::getLanguages() as $lang) {
                    $allLang[$lang['id_lang']] = Db::getInstance()->getRow(
                        'SELECT * FROM `' . _DB_PREFIX_ . 'supplier_lang`
                        WHERE `id_supplier` = ' . (int) $idSupplier .
                        ' AND `id_lang` = ' . (int) $lang['id_lang']
                    );
                }

                return $allLang;
            } else {
                return Db::getInstance()->getRow(
                    'SELECT * FROM `' . _DB_PREFIX_ . 'supplier`
                    WHERE `id_supplier` = ' . (int) $idSupplier
                );
            }
        }

        return false;
    }

    public function getSupplierProductBySupplierId($idSupplier)
    {
        if ($idSupplier) {
            return Db::getInstance()->executeS(
                'SELECT * FROM `' . _DB_PREFIX_ . 'product_supplier`
                WHERE `id_supplier` = ' . (int) $idSupplier
            );
        }

        return false;
    }

    public function saveDeletedsupplier($supplierInfo)
    {
        if ($supplierInfo) {
            $objDeletedSupplier = new WkDeletedSupplier();
            $objDeletedSupplier->id_supplier = $supplierInfo['id_supplier'];
            $objDeletedSupplier->name = $supplierInfo['name'];
            $objDeletedSupplier->active = $supplierInfo['active'];
            $objDeletedSupplier->shop = $supplierInfo['shop'];
            $objDeletedSupplier->lang = $supplierInfo['lang'];
            $objDeletedSupplier->address = $supplierInfo['address'];
            $objDeletedSupplier->product_supplier = $supplierInfo['product_supplier'];
            $objDeletedSupplier->supplier_order = isset($supplierInfo['supplier_order']) ? $supplierInfo['supplier_order'] : '';
            $objDeletedSupplier->save();
        }
    }

    public function getDeletedSupplierDetail($idDeletedSupplier)
    {
        if ($idDeletedSupplier) {
            return Db::getInstance()->getRow(
                'SELECT * FROM `' . _DB_PREFIX_ . 'wk_deleted_supplier`
                WHERE `id_wk_deleted_supplier` = ' . (int) $idDeletedSupplier
            );
        }

        return false;
    }

    public function updateDefaultSupplierId($oldIdSupplier, $newIdSupplier)
    {
        if ($oldIdSupplier && $newIdSupplier) {
            return Db::getInstance()->update(
                'product',
                [
                    'id_supplier' => (int) $newIdSupplier,
                ],
                'id_supplier = ' . (int) $oldIdSupplier
            );
        }

        return false;
    }

    public function updateAddressSupplierId($idSupplier, $idAddress)
    {
        if ($idSupplier && $idAddress) {
            return Db::getInstance()->update(
                'address',
                [
                    'id_supplier' => (int) $idSupplier,
                    'deleted' => 0,
                ],
                'id_address = ' . (int) $idAddress
            );
        }

        return false;
    }

    public function getIdProductSupplierBySupplierId($idSupplier)
    {
        if ($idSupplier) {
            return Db::getInstance()->getValue(
                'SELECT `id_product_supplier` FROM `' . _DB_PREFIX_ . 'product_supplier`
                WHERE `id_supplier` = ' . (int) $idSupplier
            );
        }

        return false;
    }

    public static function insertDataInPrimaryTable($supplierInfo)
    {
        if ($supplierInfo) {
            return Db::getInstance()->execute(
                'INSERT INTO `' . _DB_PREFIX_ . 'supplier` (`id_supplier`, `name`, `date_add`, `date_upd`, `active`)
                VALUES (' . (int) $supplierInfo['id_supplier'] . ", '', '" . $supplierInfo['date_add'] . "',
                '" . pSQL($supplierInfo['date_upd']) . "', " . (int) $supplierInfo['active'] . ')'
            );
        }

        return false;
    }
}
