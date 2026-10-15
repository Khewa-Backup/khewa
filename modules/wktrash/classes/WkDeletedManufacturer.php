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
class WkDeletedManufacturer extends ObjectModel
{
    public $id_manufacturer;
    public $name;
    public $active;
    public $shop;
    public $lang;
    public $address;
    public $product_manufacturer;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table' => 'wk_deleted_manufacturer',
        'primary' => 'id_wk_deleted_manufacturer',
        'fields' => [
            'id_manufacturer' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'name' => ['type' => self::TYPE_STRING, 'validate' => 'isCatalogName', 'size' => 64],
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'shop' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'lang' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'address' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'product_manufacturer' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false],
        ],
    ];

    public function getManufacturerDetailBeforeDelete($idManufacturer)
    {
        if ($idManufacturer) {
            $manufacturerInfo = $this->getManufacturer($idManufacturer);
            if ($manufacturerInfo) {
                $shop = $this->getManufacturer($idManufacturer, true);
                if ($shop) {
                    $manufacturerInfo['shop'] = json_encode($shop);
                }
                $lang = $this->getManufacturer($idManufacturer, false, true);
                if ($lang) {
                    $manufacturerInfo['lang'] = json_encode($lang);
                }
                $address = $this->getManufacturerAddressId($idManufacturer);
                if ($address) {
                    $manufacturerInfo['address'] = json_encode($address);
                } else {
                    $manufacturerInfo['address'] = null;
                }
                $productManufacturer = $this->getProductIdByManufacturerId($idManufacturer);
                if ($productManufacturer) {
                    $manufacturerInfo['product_manufacturer'] = json_encode($productManufacturer);
                } else {
                    $manufacturerInfo['product_manufacturer'] = null;
                }
                $source = _PS_MANU_IMG_DIR_ . $idManufacturer . '.jpg';
                $destination = _PS_MODULE_DIR_ . 'wktrash/views/img/manufacturer/' . $idManufacturer . '.jpg';
                if (file_exists($source)) {
                    ImageManager::resize($source, $destination);
                }
                $this->saveDeletedManufacturer($manufacturerInfo);
            }
            $objEntityRestoreHistory = new WkEntityRestoreHistory();
            $objEntityRestoreHistory->addEntityHistory(3, $idManufacturer);
        }
    }

    public function getManufacturer($idManufacturer, $isShop = false, $isLang = false)
    {
        if ($idManufacturer) {
            if ($isShop) {
                return Db::getInstance()->executeS(
                    'SELECT * FROM `' . _DB_PREFIX_ . 'manufacturer_shop`
                    WHERE `id_manufacturer` = ' . (int) $idManufacturer
                );
            } elseif ($isLang) {
                $allLang = [];
                foreach (Language::getLanguages() as $lang) {
                    $allLang[$lang['id_lang']] = Db::getInstance()->getRow(
                        'SELECT * FROM `' . _DB_PREFIX_ . 'manufacturer_lang`
                        WHERE `id_manufacturer` = ' . (int) $idManufacturer .
                        ' AND `id_lang` = ' . (int) $lang['id_lang']
                    );
                }

                return $allLang;
            } else {
                return Db::getInstance()->getRow(
                    'SELECT * FROM `' . _DB_PREFIX_ . 'manufacturer`
                    WHERE `id_manufacturer` = ' . (int) $idManufacturer
                );
            }
        }

        return false;
    }

    public function getManufacturerAddressId($idManufacturer)
    {
        if ($idManufacturer) {
            return Db::getInstance()->executeS(
                'SELECT `id_address` FROM `' . _DB_PREFIX_ . 'address`
                WHERE `id_manufacturer` = ' . (int) $idManufacturer
            );
        }

        return false;
    }

    public function getProductIdByManufacturerId($idManufacturer)
    {
        if ($idManufacturer) {
            return Db::getInstance()->executeS(
                'SELECT `id_product` FROM `' . _DB_PREFIX_ . 'product`
                WHERE `id_manufacturer` = ' . (int) $idManufacturer
            );
        }

        return false;
    }

    public function saveDeletedManufacturer($manufacturerInfo)
    {
        if (!empty($manufacturerInfo) && $manufacturerInfo) {
            $objDeletedManufacturer = new WkDeletedManufacturer();
            $objDeletedManufacturer->id_manufacturer = $manufacturerInfo['id_manufacturer'];
            $objDeletedManufacturer->name = $manufacturerInfo['name'];
            $objDeletedManufacturer->active = $manufacturerInfo['active'];
            $objDeletedManufacturer->shop = $manufacturerInfo['shop'];
            $objDeletedManufacturer->lang = $manufacturerInfo['lang'];
            $objDeletedManufacturer->address = $manufacturerInfo['address'];
            $objDeletedManufacturer->product_manufacturer = $manufacturerInfo['product_manufacturer'];
            $objDeletedManufacturer->save();
        }
    }

    public function getDeletedManufacturerDetail($idDeletedManufacturer)
    {
        if ($idDeletedManufacturer) {
            return Db::getInstance()->getRow(
                'SELECT * FROM `' . _DB_PREFIX_ . 'wk_deleted_manufacturer`
                WHERE `id_wk_deleted_manufacturer` = ' . (int) $idDeletedManufacturer
            );
        }

        return false;
    }

    public function updateAddressManufacturerId($idManufacturer, $address)
    {
        if ($idManufacturer && $address) {
            return Db::getInstance()->update(
                'address',
                [
                    'id_manufacturer' => (int) $idManufacturer,
                    'deleted' => 0,
                ],
                'id_address = ' . (int) $address['id_address']
            );
        }

        return false;
    }

    public function updateProductManufacturerId($idManufacturer, $productManufacturer)
    {
        if ($idManufacturer && $productManufacturer) {
            $idProduct = WkDeletedProduct::productExistsAfterRestore(
                $productManufacturer['id_product']
            );
            if ($idProduct) {
                return Db::getInstance()->update(
                    'product',
                    [
                        'id_manufacturer' => (int) $idManufacturer,
                    ],
                    'id_product = ' . (int) $idProduct
                );
            }
        }

        return false;
    }

    public function updateProductManufacturerIdZero($idProduct)
    {
        if ($idProduct) {
            return Db::getInstance()->update(
                'product',
                [
                    'id_manufacturer' => 0,
                ],
                'id_product = ' . (int) $idProduct
            );
        }

        return false;
    }

    public static function insertDataInPrimaryTable($manufacturerInfo)
    {
        if ($manufacturerInfo) {
            return Db::getInstance()->execute(
                'INSERT INTO `' . _DB_PREFIX_ . 'manufacturer` (`id_manufacturer`, `name`, `date_add`, `date_upd`, `active`)
                VALUES (' . (int) $manufacturerInfo['id_manufacturer'] . ", '', '" . pSQL($manufacturerInfo['date_add']) . "',
                '" . pSQL($manufacturerInfo['date_upd']) . "', " . (int) $manufacturerInfo['active'] . ')'
            );
        }

        return false;
    }
}
