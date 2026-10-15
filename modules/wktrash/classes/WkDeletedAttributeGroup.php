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
class WkDeletedAttributeGroup extends ObjectModel
{
    public $id_attribute_group;
    public $attribute_group_name;
    public $is_color_group;
    public $group_type;
    public $position;
    public $shop;
    public $lang;
    public $attribute_value;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table' => 'wk_deleted_attribute_group',
        'primary' => 'id_wk_deleted_attribute_group',
        'fields' => [
            'id_attribute_group' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'attribute_group_name' => [
                'type' => self::TYPE_STRING, 'validate' => 'isGenericName',
                'required' => true, 'size' => 128,
            ],
            'is_color_group' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'group_type' => ['type' => self::TYPE_STRING, 'required' => true],
            'position' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'shop' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'lang' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'attribute_value' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false],
        ],
    ];

    public function getAttributeGroupDetailBeforeDelete($idAttributeGroup)
    {
        if ($idAttributeGroup) {
            $attributeGroupInfo = $this->getAttributeGroup($idAttributeGroup);
            if ($attributeGroupInfo) {
                $attributeGroupInfo['attribute_group_name'] = $this->getAttributeGroupName(
                    $idAttributeGroup,
                    Configuration::get('PS_LANG_DEFAULT')
                );
                $shop = $this->getAttributeGroup($idAttributeGroup, true);
                if ($shop) {
                    $attributeGroupInfo['shop'] = json_encode($shop);
                }
                $lang = $this->getAttributeGroup($idAttributeGroup, false, true);
                if ($lang) {
                    $attributeGroupInfo['lang'] = json_encode($lang);
                }
                $attributeValue = $this->getAttributeByAttributeGroupId($idAttributeGroup);
                if ($attributeValue) {
                    $attributeGroupInfo['attribute_value'] = json_encode($attributeValue);
                } else {
                    $attributeGroupInfo['attribute_value'] = null;
                }
                $this->saveDeletedAttributeGroup($attributeGroupInfo);
            }
            $objEntityRestoreHistory = new WkEntityRestoreHistory();
            $objEntityRestoreHistory->addEntityHistory(6, $idAttributeGroup);
        }
    }

    public function getAttributeGroup($idAttributeGroup, $isShop = false, $isLang = false)
    {
        if ($idAttributeGroup) {
            if ($isShop) {
                return Db::getInstance()->executeS(
                    'SELECT * FROM `' . _DB_PREFIX_ . 'attribute_group_shop`
                    WHERE `id_attribute_group` = ' . (int) $idAttributeGroup
                );
            } elseif ($isLang) {
                $allLang = [];
                foreach (Language::getLanguages() as $lang) {
                    $allLang[$lang['id_lang']] = Db::getInstance()->getRow(
                        'SELECT * FROM `' . _DB_PREFIX_ . 'attribute_group_lang`
                        WHERE `id_attribute_group` = ' . (int) $idAttributeGroup .
                            ' AND `id_lang` = ' . (int) $lang['id_lang']
                    );
                }

                return $allLang;
            } else {
                return Db::getInstance()->getRow(
                    'SELECT * FROM `' . _DB_PREFIX_ . 'attribute_group`
                    WHERE `id_attribute_group` = ' . (int) $idAttributeGroup
                );
            }
        }

        return false;
    }

    public function getAttributeGroupName($idAttributeGroup, $idLang)
    {
        if ($idAttributeGroup) {
            return Db::getInstance()->getValue(
                'SELECT `name` FROM `' . _DB_PREFIX_ . 'attribute_group_lang`
                WHERE `id_attribute_group` = ' . (int) $idAttributeGroup .
                    ' AND `id_lang` = ' . (int) $idLang
            );
        }
    }

    public function getAttributeByAttributeGroupId($idAttributeGroup)
    {
        if ($idAttributeGroup) {
            $attributes = Db::getInstance()->executeS(
                'SELECT * FROM `' . _DB_PREFIX_ . 'attribute`
                WHERE `id_attribute_group` = ' . (int) $idAttributeGroup
            );
            if ($attributes) {
                $objDeletedAttribute = new WkDeletedAttribute();
                $allAttribute = [];
                foreach ($attributes as $attribute) {
                    if ($attribute['id_attribute']) {
                        $attribute['lang'] = Db::getInstance()->executeS(
                            'SELECT * FROM `' . _DB_PREFIX_ . 'attribute_lang`
                            WHERE `id_attribute` = ' . (int) $attribute['id_attribute']
                        );
                        $attribute['shop'] = Db::getInstance()->executeS(
                            'SELECT * FROM `' . _DB_PREFIX_ . 'attribute_shop`
                            WHERE `id_attribute` = ' . (int) $attribute['id_attribute']
                        );
                        $attribute['combination'] = $objDeletedAttribute->getProductAttributeByAttributeId(
                            $attribute['id_attribute']
                        );
                        /* $attribute['combination'] = Db::getInstance()->executeS(
                            'SELECT * FROM `'._DB_PREFIX_.'product_attribute_combination`
                            WHERE `id_attribute` = ' .(int)$attribute['id_attribute']
                        ); */
                    }
                    array_push($allAttribute, $attribute);
                }

                return $allAttribute;
            }
        }

        return false;
    }

    public static function attributeGroupExistsAfterRestore($idAttributeGroup)
    {
        if ($idAttributeGroup) {
            $row = Db::getInstance()->getRow(
                'SELECT `id_attribute_group` FROM ' . _DB_PREFIX_ . 'attribute_group
                WHERE `id_attribute_group` = ' . (int) $idAttributeGroup
            );
            $groupExist = isset($row['id_attribute_group']);
            if ($groupExist) {
                return $idAttributeGroup;
            } else {
                $groupNewId = Db::getInstance()->getValue(
                    'SELECT `id_new_entity` FROM `' . _DB_PREFIX_ . 'wk_entity_restore_history`
                    WHERE `id_old_entity` = ' . (int) $idAttributeGroup . ' AND `type` = 6'
                );
                if (!empty($groupNewId) && $groupNewId) {
                    return self::attributeGroupExistsAfterRestore($groupNewId);
                }
            }
        }

        return false;
    }

    public function saveDeletedAttributeGroup($attributeGroupInfo)
    {
        if (!empty($attributeGroupInfo) && $attributeGroupInfo) {
            $objDeletedAttributeGroup = new WkDeletedAttributeGroup();
            $objDeletedAttributeGroup->id_attribute_group = $attributeGroupInfo['id_attribute_group'];
            $objDeletedAttributeGroup->attribute_group_name = $attributeGroupInfo['attribute_group_name'];
            $objDeletedAttributeGroup->is_color_group = $attributeGroupInfo['is_color_group'];
            $objDeletedAttributeGroup->group_type = $attributeGroupInfo['group_type'];
            $objDeletedAttributeGroup->position = $attributeGroupInfo['position'];
            $objDeletedAttributeGroup->shop = $attributeGroupInfo['shop'];
            $objDeletedAttributeGroup->lang = $attributeGroupInfo['lang'];
            $objDeletedAttributeGroup->attribute_value = $attributeGroupInfo['attribute_value'];
            $objDeletedAttributeGroup->save();
        }
    }

    public function getDeletedAttributeGroupDetail($idDeletedAttributeGroup)
    {
        if ($idDeletedAttributeGroup) {
            return Db::getInstance()->getRow(
                'SELECT * FROM `' . _DB_PREFIX_ . 'wk_deleted_attribute_group`
                WHERE `id_wk_deleted_attribute_group` = ' . (int) $idDeletedAttributeGroup
            );
        }

        return false;
    }

    public function setCombinationByAttributeId($idAttribute, $combination)
    {
        $objCombination = new Combination();
        $objCombination->id_product = (int) $combination['product_attribute']['id_product'];
        $objCombination->reference = $combination['product_attribute']['reference'];
        $objCombination->supplier_reference = $combination['product_attribute']['supplier_reference'];
        if (version_compare(_PS_VERSION_, '8.0.0', '>=')) {
        } else {
            $objCombination->location = $combination['product_attribute']['location'];
        }
        $objCombination->ean13 = $combination['product_attribute']['ean13'];
        $objCombination->isbn = $combination['product_attribute']['isbn'];
        $objCombination->upc = $combination['product_attribute']['upc'];
        if (version_compare(_PS_VERSION_, '8.0.0', '>=')) {
        } else {
            $objCombination->quantity = (int) $combination['product_attribute']['quantity'];
        }
        $default_on = 0;
        if ($combination['product_attribute']['default_on']) {
            $default_on = $combination['product_attribute']['default_on'];
        }
        if ($combination['product_attribute']['shop']
            && !empty($combination['product_attribute']['shop'])) {
            foreach ($combination['product_attribute']['shop'] as $comboShop) {
                if (!empty($comboShop) && $comboShop) {
                    if ((int) $comboShop['id_shop'] === Context::getContext()->shop->id) {
                        $objCombination->wholesale_price = $comboShop['wholesale_price'];
                        $objCombination->price = $comboShop['price'];
                        $objCombination->ecotax = $comboShop['ecotax'];
                        $objCombination->weight = $comboShop['weight'];
                        $objCombination->unit_price_impact = $comboShop['unit_price_impact'];
                        $objCombination->default_on = $default_on/* $comboShop['default_on'] */;
                        $objCombination->minimal_quantity = $comboShop['minimal_quantity'];
                        $objCombination->low_stock_threshold = $comboShop['low_stock_threshold'];
                        $objCombination->low_stock_alert = $comboShop['low_stock_alert'];
                        $objCombination->available_date = $comboShop['available_date'];
                    }
                }
            }
        }
        $objCombination->save();
        if ($objCombination->id) {
            self::setAttributeCombination(
                $idAttribute,
                $objCombination->id
            );
            if ($combination['product_attribute']['product_combination']) {
                foreach ($combination['product_attribute']['product_combination'] as $allCombination) {
                    $idAttr = $allCombination['id_attribute'];
                    if (version_compare(_PS_VERSION_, '8.0.0', '>=')) {
                        $objAttributeCheck = new ProductAttribute($idAttr);
                    } else {
                        $objAttributeCheck = new Attribute($idAttr);
                    }
                    if (Validate::isLoadedObject($objAttributeCheck)) {
                        $isAlready = self::getAttributeCombination($idAttr, $objCombination->id);
                        if (!$isAlready) {
                            self::setAttributeCombination(
                                $idAttr,
                                $objCombination->id
                            );
                        }
                    }
                    unset($objAttributeCheck);
                    StockAvailable::setQuantity(
                        $combination['product_attribute']['id_product'],
                        $objCombination->id,
                        $allCombination['quantity'],
                        Context::getContext()->shop->id
                    );
                }

                return;
            }
            StockAvailable::updateQuantity(
                $combination['product_attribute']['id_product'],
                $objCombination->id,
                $combination['product_attribute']['quantity'],
                Context::getContext()->shop->id
            );
        }
    }

    public static function setAttributeCombination($idAttribute, $idProductAttribute)
    {
        if ($idAttribute && $idProductAttribute) {
            return Db::getInstance()->insert('product_attribute_combination', [
                'id_attribute' => (int) $idAttribute,
                'id_product_attribute' => (int) $idProductAttribute,
            ]);
        }

        return false;
    }

    public static function getAttributeCombination($idAttribute, $idProductAttribute)
    {
        if ($idAttribute && $idProductAttribute) {
            return Db::getInstance()->getRow(
                'SELECT * FROM `' . _DB_PREFIX_ . 'product_attribute_combination`
                WHERE `id_attribute` = ' . (int) $idAttribute .
                    ' AND `id_product_attribute` = ' . (int) $idProductAttribute
            );
        }

        return false;
    }

    public static function insertDataInPrimaryTable($attributeGroupInfo)
    {
        if ($attributeGroupInfo) {
            return Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'attribute_group` (`id_attribute_group`,
            `is_color_group`, `group_type`, `position`) VALUES (' . (int) $attributeGroupInfo['id_attribute_group'] . ",
            '', '" . pSQL($attributeGroupInfo['group_type']) . "', '')");
        }

        return false;
    }
}
