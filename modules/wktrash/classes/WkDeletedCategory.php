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
class WkDeletedCategory extends ObjectModel
{
    public $id_category;
    public $id_parent;
    public $id_shop_default;
    public $category_name;
    public $level_depth;
    public $nleft;
    public $nright;
    public $active;
    public $position;
    public $is_root_category;
    public $shop;
    public $lang;
    public $category_product;
    public $category_group;
    public $group_reduction;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table' => 'wk_deleted_category',
        'primary' => 'id_wk_deleted_category',
        'fields' => [
            'id_category' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'id_parent' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'id_shop_default' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'category_name' => ['type' => self::TYPE_STRING, 'validate' => 'isCatalogName', 'size' => 128],
            'level_depth' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'nleft' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'nright' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'position' => ['type' => self::TYPE_INT],
            'is_root_category' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'shop' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'lang' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'category_product' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'category_group' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'group_reduction' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false],
        ],
    ];

    public function getCategoryDetailBeforeDelete($idCategory)
    {
        if ($idCategory) {
            $categoryInfo = $this->getCategory($idCategory);
            if ($categoryInfo) {
                $objCategory = new Category($idCategory);
                if (Validate::isLoadedObject($objCategory)) {
                    $categoryInfo['category_name'] = $objCategory->getName();
                    $shop = $this->getCategory($idCategory, true);
                    if ($shop) {
                        $categoryInfo['shop'] = json_encode($shop);
                    }

                    $lang = $this->getCategory($idCategory, false, true);
                    if ($lang) {
                        $categoryInfo['lang'] = json_encode($lang);
                    }
                    $categoryProduct = $this->getCategoryProductByCategoryId($idCategory);
                    if ($categoryProduct) {
                        $categoryInfo['category_product'] = json_encode($categoryProduct);
                    } else {
                        $categoryInfo['category_product'] = null;
                    }
                    $categoryGroup = $objCategory->getGroups();
                    if ($categoryGroup) {
                        $categoryInfo['category_group'] = json_encode($categoryGroup);
                    } else {
                        $categoryInfo['category_group'] = null;
                    }
                    $groupReduction = GroupReduction::getGroupsByCategoryId($idCategory);
                    if ($groupReduction) {
                        $categoryInfo['group_reduction'] = json_encode($groupReduction);
                    } else {
                        $categoryInfo['group_reduction'] = null;
                    }
                    $source = _PS_CAT_IMG_DIR_ . $idCategory . '.jpg';
                    $destination = _PS_MODULE_DIR_ . 'wktrash/views/img/category/' . $idCategory . '.jpg';
                    if (file_exists($source)) {
                        ImageManager::resize($source, $destination);
                    }
                }

                $this->saveDeletedCategory($categoryInfo);
            }
            $objEntityRestoreHistory = new WkEntityRestoreHistory();
            $objEntityRestoreHistory->addEntityHistory(2, $idCategory);
        }
    }

    public function getCategory($idCategory, $isShop = false, $isLang = false)
    {
        if ($idCategory) {
            if ($isShop) {
                return Db::getInstance()->executeS(
                    'SELECT * FROM `' . _DB_PREFIX_ . 'category_shop`
                    WHERE `id_category` = ' . (int) $idCategory
                );
            } elseif ($isLang) {
                $allLang = [];
                foreach (Language::getLanguages() as $lang) {
                    $allLang[$lang['id_lang']] = Db::getInstance()->getRow(
                        'SELECT * FROM `' . _DB_PREFIX_ . 'category_lang`
                        WHERE `id_category` = ' . (int) $idCategory .
                            ' AND `id_lang` = ' . (int) $lang['id_lang']
                    );
                }

                return $allLang;
            } else {
                return Db::getInstance()->getRow(
                    'SELECT * FROM `' . _DB_PREFIX_ . 'category`
                    WHERE `id_category` = ' . (int) $idCategory
                );
            }
        }

        return false;
    }

    public function getCategoryProductByCategoryId($idCategory)
    {
        if ($idCategory) {
            return Db::getInstance()->executeS(
                'SELECT * FROM `' . _DB_PREFIX_ . 'category_product`
                WHERE `id_category` = ' . (int) $idCategory
            );
        }

        return false;
    }

    public function saveDeletedCategory($categoryInfo)
    {
        if (!empty($categoryInfo) && $categoryInfo) {
            $objDeletedCategory = new WkDeletedCategory();
            $objDeletedCategory->id_category = $categoryInfo['id_category'];
            $objDeletedCategory->id_parent = $categoryInfo['id_parent'];
            $objDeletedCategory->id_shop_default = $categoryInfo['id_shop_default'];
            $objDeletedCategory->category_name = $categoryInfo['category_name'];
            $objDeletedCategory->level_depth = $categoryInfo['level_depth'];
            $objDeletedCategory->nleft = $categoryInfo['nleft'];
            $objDeletedCategory->nright = $categoryInfo['nright'];
            $objDeletedCategory->active = $categoryInfo['active'];
            $objDeletedCategory->position = $categoryInfo['position'];
            $objDeletedCategory->is_root_category = $categoryInfo['is_root_category'];
            $objDeletedCategory->shop = isset($categoryInfo['shop']) ? $categoryInfo['shop'] : '';
            $objDeletedCategory->lang = $categoryInfo['lang'];
            $objDeletedCategory->category_product = $categoryInfo['category_product'];
            $objDeletedCategory->category_group = $categoryInfo['category_group'];
            $objDeletedCategory->group_reduction = $categoryInfo['group_reduction'];
            $objDeletedCategory->save();
        }
    }

    public function getDeletedCategoryDetail($idDeletedCategory)
    {
        if ($idDeletedCategory) {
            return Db::getInstance()->getRow(
                'SELECT * FROM `' . _DB_PREFIX_ . 'wk_deleted_category`
                WHERE `id_wk_deleted_category` = ' . (int) $idDeletedCategory
            );
        }

        return false;
    }

    public static function categoryExistsAfterRestore($idCategory)
    {
        if ($idCategory) {
            if (Category::categoryExists($idCategory)) {
                return $idCategory;
            } else {
                $categoryNewId = Db::getInstance()->getValue(
                    'SELECT `id_new_entity` FROM `' . _DB_PREFIX_ . 'wk_entity_restore_history`
                    WHERE `id_old_entity` = ' . (int) $idCategory . ' AND `type` = 2'
                );
                if (!empty($categoryNewId) && $categoryNewId) {
                    return self::categoryExistsAfterRestore($categoryNewId);
                }
            }
        }

        return false;
    }

    public function getDeletedChildren($idCategory)
    {
        if ($idCategory) {
            return Db::getInstance()->executeS(
                'SELECT `id_wk_deleted_category` FROM `' . _DB_PREFIX_ . 'wk_deleted_category`
                WHERE `id_parent` = ' . (int) $idCategory
            );
        }

        return false;
    }

    public static function getCategoryNleftById($deletedCategoryIds)
    {
        if (is_array($deletedCategoryIds) && !empty($deletedCategoryIds)) {
            $categoryIds = [];
            foreach ($deletedCategoryIds as $idDeletedCategory) {
                if (!empty($idDeletedCategory) && $idDeletedCategory) {
                    $categoryIds[] = Db::getInstance()->getRow(
                        'SELECT `id_wk_deleted_category`, `nleft` FROM `' . _DB_PREFIX_ . 'wk_deleted_category`
                        WHERE `id_wk_deleted_category` = ' . (int) $idDeletedCategory
                    );
                }
            }

            return $categoryIds;
        }

        return false;
    }

    public function updateDefaultCategoryId($oldIdCategory, $newIdCategory)
    {
        if ($oldIdCategory && $newIdCategory) {
            return Db::getInstance()->update(
                'product_shop',
                [
                    'id_category_default' => (int) $newIdCategory,
                ],
                'id_category_default = ' . (int) $oldIdCategory
            );
        }

        return false;
    }

    public function updateDefaultCategoryIdProductShop($idProduct)
    {
        return Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . 'product_shop`
            SET `id_category_default` = 0 WHERE `id_product`=' . (int) $idProduct
        );
    }

    public function updateDefaultCategoryIdOfProduct($idProduct)
    {
        return Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . 'product`
            SET `id_category_default` = 0 WHERE `id_product`=' . (int) $idProduct
        );
    }

    public function setCategoryProduct($idCategory, $categoryProduct)
    {
        if ($idCategory && $categoryProduct) {
            $idProduct = WkDeletedProduct::productExistsAfterRestore(
                $categoryProduct['id_product']
            );
            if ($idProduct) {
                return Db::getInstance()->insert('category_product', [
                    'id_category' => (int) $idCategory,
                    'id_product' => (int) $idProduct,
                    'position' => (int) $categoryProduct['position'],
                ]);
            }
        }

        return false;
    }

    public static function insertDataInPrimaryTable($categoryInfo)
    {
        if ($categoryInfo) {
            return Db::getInstance()->execute(
                'INSERT INTO `' . _DB_PREFIX_ . 'category` (`id_category`, `id_parent`,
                `id_shop_default`, `level_depth`, `nleft`, `nright`, `active`, `date_add`, `date_upd`, `position`,
                `is_root_category`) VALUES (' . (int) $categoryInfo['id_category'] . ',
                ' . (int) $categoryInfo['id_parent'] . ', ' . (int) $categoryInfo['id_shop_default'] . ',
                ' . (int) $categoryInfo['level_depth'] . ', ' . (int) $categoryInfo['nleft'] . ',
                ' . (int) $categoryInfo['nright'] . ', ' . (int) $categoryInfo['active'] . ", '
                " . pSQL($categoryInfo['date_add']) . "', '" . pSQL($categoryInfo['date_upd']) . "',
                " . (int) $categoryInfo['position'] . ', ' . (int) $categoryInfo['is_root_category'] . ')'
            );
        }

        return false;
    }
}
