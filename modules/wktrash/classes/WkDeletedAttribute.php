<?php
/**
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License version 3.0
* that is bundled with this package in the file LICENSE.txt
* It is also available through the world-wide-web at this URL:
* https://opensource.org/licenses/AFL-3.0
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to a newer
* versions in the future. If you wish to customize this module for your
* needs please refer to CustomizationPolicy.txt file inside our module for more information.
*
* @author Webkul IN
* @copyright Since 2010 Webkul
* @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
class WkDeletedAttribute extends ObjectModel
{
    public $id_attribute;
    public $id_attribute_group;
    public $attribute_name;
    public $color;
    public $position;
    public $shop;
    public $lang;
    public $product_attribute;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table' => 'wk_deleted_attribute',
        'primary' => 'id_wk_deleted_attribute',
        'fields' => [
            'id_attribute' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'id_attribute_group' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'attribute_name' => [
                'type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true,
                'size' => 128,
            ],
            'color' => ['type' => self::TYPE_STRING, 'validate' => 'isColor'],
            'position' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'shop' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'lang' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'product_attribute' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false],
        ],
    ];

    public function getAttributeDetailBeforeDelete($idAttribute)
    {
        if ($idAttribute) {
            $attributeInfo = $this->getAttribute($idAttribute);
            if ($attributeInfo) {
                $attributeInfo['attribute_name'] = $this->getAttributeName(
                    $idAttribute,
                    Configuration::get('PS_LANG_DEFAULT')
                );
                $shop = $this->getAttribute($idAttribute, true);
                if ($shop) {
                    $attributeInfo['shop'] = json_encode($shop);
                }
                $lang = $this->getAttribute($idAttribute, false, true);
                if ($lang) {
                    $attributeInfo['lang'] = json_encode($lang);
                }
                $productAttribute = $this->getProductAttributeByAttributeId($idAttribute);
                if ($productAttribute) {
                    $attributeInfo['product_attribute'] = json_encode($productAttribute);
                } else {
                    $attributeInfo['product_attribute'] = null;
                }
                $this->saveDeletedAttribute($attributeInfo);
            }
            $objEntityRestoreHistory = new WkEntityRestoreHistory();
            $objEntityRestoreHistory->addEntityHistory(7, $idAttribute);
        }
    }

    public function getAttribute($idAttribute, $isShop = false, $isLang = false)
    {
        if ($idAttribute) {
            if ($isShop) {
                return Db::getInstance()->executeS(
                    'SELECT * FROM `' . _DB_PREFIX_ . 'attribute_shop`
                    WHERE `id_attribute` = ' . (int) $idAttribute
                );
            } elseif ($isLang) {
                $allLang = [];
                foreach (Language::getLanguages() as $lang) {
                    $allLang[$lang['id_lang']] = Db::getInstance()->getRow(
                        'SELECT * FROM `' . _DB_PREFIX_ . 'attribute_lang`
                        WHERE `id_attribute` = ' . (int) $idAttribute .
                            ' AND `id_lang` = ' . (int) $lang['id_lang']
                    );
                }

                return $allLang;
            } else {
                return Db::getInstance()->getRow(
                    'SELECT * FROM `' . _DB_PREFIX_ . 'attribute`
                    WHERE `id_attribute` = ' . (int) $idAttribute
                );
            }
        }

        return false;
    }

    public function getAttributeName($idAttribute, $idLang)
    {
        if ($idAttribute) {
            return Db::getInstance()->getValue(
                'SELECT `name` FROM `' . _DB_PREFIX_ . 'attribute_lang`
                WHERE `id_attribute` = ' . (int) $idAttribute .
                    ' AND `id_lang` = ' . (int) $idLang
            );
        }
    }

    public function getProductAttributeByAttributeId($idAttribute)
    {
        if ($idAttribute) {
            $combinations = Db::getInstance()->executeS(
                'SELECT * FROM `' . _DB_PREFIX_ . 'product_attribute_combination`
                WHERE `id_attribute` = ' . (int) $idAttribute
            );
            if ($combinations) {
                $allCombination = [];
                foreach ($combinations as $combination) {
                    if ($combination['id_product_attribute']) {
                        if (version_compare(_PS_VERSION_, '8.0.0', '>=')) {
                            $combination['product_attribute'] = Db::getInstance()->getRow(
                                'SELECT pa.`id_product_attribute`, pa.`id_product`, pa.`reference`,
                                pa.`supplier_reference`, pa.`ean13`, pa.`isbn`, pa.`upc`,
                                sa.`quantity`, pa.`default_on` FROM `' . _DB_PREFIX_ . 'product_attribute` pa
                                LEFT JOIN `' . _DB_PREFIX_ . 'stock_available` sa ON sa.`id_product` = pa.`id_product`
                                WHERE pa.`id_product_attribute` = ' . (int) $combination['id_product_attribute']
                            );
                        } else {
                            $combination['product_attribute'] = Db::getInstance()->getRow(
                                'SELECT pa.`id_product_attribute`, pa.`id_product`, pa.`reference`,
                                pa.`supplier_reference`, pa.`location`, pa.`ean13`, pa.`isbn`, pa.`upc`,
                                sa.`quantity`, pa.`default_on` FROM `' . _DB_PREFIX_ . 'product_attribute` pa
                                LEFT JOIN `' . _DB_PREFIX_ . 'stock_available` sa ON sa.`id_product` = pa.`id_product`
                                WHERE pa.`id_product_attribute` = ' . (int) $combination['id_product_attribute']
                            );
                        }
                        $combination['product_attribute']['shop'] = Db::getInstance()->executeS(
                            'SELECT * FROM `' . _DB_PREFIX_ . 'product_attribute_shop`
                            WHERE `id_product_attribute` = ' . (int) $combination['id_product_attribute']
                        );
                        $combination['product_attribute']['image'] = Db::getInstance()->executeS(
                            'SELECT * FROM `' . _DB_PREFIX_ . 'product_attribute_image`
                            WHERE `id_product_attribute` = ' . (int) $combination['id_product_attribute']
                        );
                        $combination['product_attribute']['product_combination'] = Db::getInstance()->executeS(
                            'SELECT pac.*, sa.`quantity` FROM `' . _DB_PREFIX_ . 'product_attribute_combination` pac
                            LEFT JOIN `' . _DB_PREFIX_ . 'stock_available` sa ON sa.`id_product_attribute` =
                            pac.`id_product_attribute`
                            WHERE pac.`id_product_attribute` = ' . (int) $combination['id_product_attribute']
                        );
                    }
                    array_push($allCombination, $combination);
                }

                return $allCombination;
            }
        }

        return false;
    }

    public function saveDeletedAttribute($attributeInfo)
    {
        if (!empty($attributeInfo) && $attributeInfo) {
            $objDeletedAttribute = new WkDeletedAttribute();
            $objDeletedAttribute->id_attribute = $attributeInfo['id_attribute'];
            $objDeletedAttribute->id_attribute_group = $attributeInfo['id_attribute_group'];
            $objDeletedAttribute->attribute_name = $attributeInfo['attribute_name'];
            $objDeletedAttribute->color = $attributeInfo['color'];
            $objDeletedAttribute->position = $attributeInfo['position'];
            $objDeletedAttribute->shop = $attributeInfo['shop'];
            $objDeletedAttribute->lang = $attributeInfo['lang'];
            $objDeletedAttribute->product_attribute = $attributeInfo['product_attribute'];
            $objDeletedAttribute->save();
        }
    }

    public function getDeletedAttributeDetail($idDeletedAttribute)
    {
        if ($idDeletedAttribute) {
            return Db::getInstance()->getRow(
                'SELECT * FROM `' . _DB_PREFIX_ . 'wk_deleted_attribute`
                WHERE `id_wk_deleted_attribute` = ' . (int) $idDeletedAttribute
            );
        }

        return false;
    }

    public function isAttributeGroupExists($idDeletedAttribute)
    {
        if ($idDeletedAttribute) {
            $idAttributeGroup = Db::getInstance()->getValue(
                'SELECT ag.`id_attribute_group` FROM `' . _DB_PREFIX_ . 'wk_deleted_attribute` da
                LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group` ag ON da.`id_attribute_group` = ag.`id_attribute_group`
                WHERE `id_wk_deleted_attribute` = ' . (int) $idDeletedAttribute
            );
            if ($idAttributeGroup) {
                return true;
            }
        }

        return false;
    }

    public static function insertDataInPrimaryTable($attributeInfo)
    {
        if ($attributeInfo) {
            return Db::getInstance()->execute(
                'INSERT INTO `' . _DB_PREFIX_ . 'attribute` (`id_attribute`, `id_attribute_group`, `color`, `position`)
                VALUES (' . (int) $attributeInfo['id_attribute'] . ', ' . (int) $attributeInfo['id_attribute_group'] . ",
                '" . pSQL($attributeInfo['color']) . "', " . (int) $attributeInfo['position'] . ')'
            );
        }

        return false;
    }

    public static function insertDataWithOldId($idAttribute, $idAttributeGroup)
    {
        return Db::getInstance()->execute(
            'INSERT INTO `' . _DB_PREFIX_ . 'attribute` (`id_attribute`, `id_attribute_group`)
            VALUES (' . (int) $idAttribute . ', ' . (int) $idAttributeGroup . ')'
        );
    }
}
