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
class AdminWkDeletedCategoriesController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->lang = false;
        $this->table = 'wk_deleted_category';
        $this->className = 'WkDeletedCategory';
        $this->identifier = 'id_wk_deleted_category';
        $this->list_no_link = true;
        parent::__construct();

        $this->_select = '`id_wk_deleted_category` as temp_deleted_category_id';
        $this->fields_list = [
            'id_wk_deleted_category' => [
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'id_category' => [
                'title' => $this->l('Category ID'),
                'align' => 'center',
                'class' => 'fixed-width-xl',
            ],
            'category_name' => [
                'title' => $this->l('Category name'),
                'align' => 'center',
                'class' => 'fixed-width-xxl',
            ],
            'date_add' => [
                'title' => $this->l('Date'),
                'align' => 'center',
                'class' => 'fixed-width-xxl',
            ],
            'temp_deleted_category_id' => [
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
     * To display restore button on deleted category list
     *
     * @param [int] $idDeletedCategory
     *
     * @return html
     */
    public function getRestoreButton($idDeletedCategory)
    {
        if ($idDeletedCategory) {
            $this->context->smarty->assign([
                'idDeletedEntity' => $idDeletedCategory,
                'entityTable' => $this->table,
            ]);

            return $this->context->smarty->fetch(
                _PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/restore-button.tpl'
            );
        }

        return false;
    }

    /**
     * To render list for deleted category
     *
     * @return void
     */
    public function renderList()
    {
        $this->addRowAction('delete');

        return parent::renderList();
    }

    /**
     * To hide add new button from deleted category list
     *
     * @return void
     */
    public function initToolbar()
    {
        parent::initToolbar();
        unset($this->toolbar_btn['new']);
    }

    /**
     * To restore the data of categories.
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
                $idDeletedCategory = Tools::getValue('restoreButton' . $this->table);
                if ($idDeletedCategory) {
                    $this->restoreCategoryAfterDeletion($idDeletedCategory);
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
        $categoryIds = WkDeletedCategory::getCategoryNleftById($this->boxes);

        if (is_array($categoryIds) && !empty($categoryIds)) {
            $id = $nleft = [];
            foreach ($categoryIds as $catIndex => $row) {
                $id[$catIndex] = $row['id_wk_deleted_category'];
                $nleft[$catIndex] = $row['nleft'];
            }
            $id = array_column($categoryIds, 'id_wk_deleted_category');
            $nleft = array_column($categoryIds, 'nleft');
            array_multisort($nleft, SORT_ASC, $categoryIds);

            foreach ($categoryIds as $idCategory) {
                if (isset($idCategory['id_wk_deleted_category']) && $idCategory['id_wk_deleted_category']) {
                    $this->restoreCategoryAfterDeletion($idCategory['id_wk_deleted_category']);
                }
            }
            if (empty($this->context->controller->errors)) {
                $index = count($this->_conf);
                Tools::redirectAdmin(
                    AdminController::$currentIndex . '&token=' . $this->context->controller->token . '&conf=' . $index
                );
            }
        } else {
            $this->context->controller->errors[] = $this->l('You must have select at least one category to restore.');
        }
    }

    public function restoreCategoryAfterDeletion($idDeletedCategory)
    {
        if (!empty($idDeletedCategory) && $idDeletedCategory) {
            $objDeletdCategory = new WkDeletedCategory($idDeletedCategory);
            if (Validate::isLoadedObject($objDeletdCategory)) {
                $categoryInfo = $objDeletdCategory->getDeletedCategoryDetail($idDeletedCategory);
                if (!empty($categoryInfo) && $categoryInfo) {
                    $parentExist = WkDeletedCategory::categoryExistsAfterRestore($categoryInfo['id_parent']);
                    if ($parentExist) {
                        $parentId = $parentExist;
                        $levelDepth = $categoryInfo['level_depth'];
                    } else {
                        if ((bool) Configuration::get('WK_RESTORE_IN_ROOT_CATEGORY')) {
                            $parentId = Category::getRootCategory()->id;
                            $levelDepth = 2;
                        } else {
                            $parentId = false;
                            $levelDepth = false;
                        }
                    }
                    $idNewCategory = $this->restoreDeletedCategory($categoryInfo, $parentId, $levelDepth);
                    if (!empty($idNewCategory) && $idNewCategory) {
                        $objEntityHistory = new WkEntityRestoreHistory();
                        $historyId = $objEntityHistory->getIdByOldEntityId($categoryInfo['id_category'], 2);
                        if ($historyId) {
                            $objEntityHistory->updateEntityHistory($historyId, $idNewCategory);
                        }
                        $objDeletdCategory->delete();
                        if ((bool) Configuration::get('WK_RESTORE_CHILD_CATEGORY')) {
                            $this->restoreChildCategory($categoryInfo['id_category'], $idNewCategory);
                        }
                    }
                }
            }
        }
    }

    public function restoreDeletedCategory($categoryInfo, $idParent, $depthLevel = false)
    {
        if (!$idParent) {
            $this->context->controller->errors = $this->l('You can\'t restore category without it\'s parent category.');

            return false;
        }
        if (!empty($categoryInfo) && $categoryInfo) {
            $category = new Category(); // Restore Category with new ID
            if (!Configuration::get('WK_RESTORE_ENTITY_NEW_ID')) {
                // Restore Category with Old ID
                $wkResult = WkDeletedCategory::insertDataInPrimaryTable($categoryInfo);
                if ($wkResult) {
                    $category = new Category($categoryInfo['id_category']);
                }
            }

            $category->name = [];
            $category->description = [];
            $category->link_rewrite = [];
            $category->meta_title = [];
            $category->meta_description = [];
            $category->meta_keywords = [];
            // Decode all category info
            $categoryInfo['shop'] = json_decode($categoryInfo['shop'], true);
            $categoryInfo['lang'] = json_decode($categoryInfo['lang'], true);
            $categoryInfo['category_product'] = json_decode($categoryInfo['category_product'], true);
            $categoryInfo['category_group'] = json_decode($categoryInfo['category_group'], true);
            $categoryInfo['group_reduction'] = json_decode($categoryInfo['group_reduction'], true);

            foreach (Language::getLanguages() as $lang) {
                $category->name[$lang['id_lang']] = $categoryInfo['lang'][$lang['id_lang']]['name'];
                $category->description[$lang['id_lang']] = $categoryInfo['lang'][$lang['id_lang']]['description'];

                $category->meta_title[$lang['id_lang']] = $categoryInfo['lang'][$lang['id_lang']]['meta_title'];
                $category->meta_description[$lang['id_lang']] =
                $categoryInfo['lang'][$lang['id_lang']]['meta_description'];
                $category->meta_keywords[$lang['id_lang']] = $categoryInfo['lang'][$lang['id_lang']]['meta_keywords'];
                $category->link_rewrite[$lang['id_lang']] = $categoryInfo['lang'][$lang['id_lang']]['link_rewrite'];
            }
            $category->id_shop_default = Context::getContext()->shop->id;
            $category->id_parent = $idParent;
            $category->level_depth = ($depthLevel) ? $depthLevel : $categoryInfo['level_depth'];
            // $category->nleft = $categoryInfo['nleft'];
            // $category->nright = $categoryInfo['nright'];
            $category->position = Category::getLastPosition($idParent, Context::getContext()->shop->id);

            $category->active = $categoryInfo['active'];
            $category->is_root_category = $categoryInfo['is_root_category'];

            $category->save();

            if ($category->id) {
                $source = _PS_MODULE_DIR_ . $this->module->name . '/views/img/category/' .
                    $categoryInfo['id_category'] . '.jpg';
                $destination = _PS_CAT_IMG_DIR_ . $category->id;
                if (file_exists($source)) {
                    if ($imageTypes = ImageType::getImagesTypes('categories')) {
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
                $objDeletdCategory = new WkDeletedCategory();
                $objDeletdCategory->updateDefaultCategoryId($categoryInfo['id_category'], $category->id);
                if ((bool) Configuration::get('WK_CATEGORY_APPLY_ON_PRODUCT')) {
                    if (!empty($categoryInfo['category_product']) && $categoryInfo['category_product']) {
                        foreach ($categoryInfo['category_product'] as $categoryProduct) {
                            if (!empty(new Product($categoryProduct['id_product']))) {
                                $objDeletdCategory->setCategoryProduct($category->id, $categoryProduct);
                            }
                        }
                    }
                } else {
                    if (!empty($categoryInfo['category_product']) && $categoryInfo['category_product']) {
                        foreach ($categoryInfo['category_product'] as $categoryProduct) {
                            if (!empty(new Product($categoryProduct['id_product']))) {
                                $objDeletdCategory->updateDefaultCategoryIdOfProduct($categoryProduct['id_product']);
                                $objDeletdCategory->updateDefaultCategoryIdProductShop($categoryProduct['id_product']);
                            }
                        }
                    }
                }
                if (!empty($categoryInfo['group_reduction']) && $categoryInfo['group_reduction']) {
                    foreach ($categoryInfo['group_reduction'] as $groupReduction) {
                        if (!empty($groupReduction) && $groupReduction) {
                            $objGroupReduction = new GroupReduction();
                            $objGroupReduction->id_group = $groupReduction['id_group'];
                            $objGroupReduction->id_category = $category->id;
                            $objGroupReduction->reduction = $groupReduction['reduction'];
                            $objGroupReduction->save();
                        }
                    }
                }

                return $category->id;
            }
        }
    }

    public function restoreChildCategory($idCategory, $idParent)
    {
        $objDeletdCategory = new WkDeletedCategory();
        $children = $objDeletdCategory->getDeletedChildren($idCategory);
        if ($children) {
            foreach ($children as $child) {
                $objDeletedCategory = new WkDeletedCategory($child['id_wk_deleted_category']);
                if (Validate::isLoadedObject($objDeletedCategory)) {
                    $childCategoryInfo = $objDeletedCategory->getDeletedCategoryDetail(
                        $child['id_wk_deleted_category']
                    );
                    if ($childCategoryInfo) {
                        $idNewChildCategory = $this->restoreDeletedCategory(
                            $childCategoryInfo,
                            $idParent
                        );
                        if ($idNewChildCategory) {
                            $objEntityHistory = new WkEntityRestoreHistory();
                            $historyId = $objEntityHistory->getIdByOldEntityId(
                                $childCategoryInfo['id_category'],
                                2
                            );
                            if ($historyId) {
                                $objEntityHistory->updateEntityHistory(
                                    $historyId,
                                    $idNewChildCategory
                                );
                            }
                            $objDeletedCategory->delete();
                            $this->restoreChildCategory($childCategoryInfo['id_category'], $idNewChildCategory);
                        }
                    }
                }
            }
        }

        return false;
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
