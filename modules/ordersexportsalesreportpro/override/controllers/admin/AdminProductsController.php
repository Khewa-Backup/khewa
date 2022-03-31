<?php
/**
 *
 * NOTICE OF LICENSE
 *
 *  @author    IntelliPresta <tehran.alishov@gmail.com>
 *  @copyright 2020 IntelliPresta
 *  @license   Commercial License
 */

class AdminProductsController extends AdminProductsControllerCore
{

    public function ajaxProcessGetCategoryTree()
    {
        $category = Tools::getValue('category', Category::getRootCategory()->id);
        $full_tree = Tools::getValue('fullTree', 0);
        $use_check_box = Tools::getValue('useCheckBox', 1);
        $selected = Tools::getValue('selected', array());
        $id_tree = Tools::getValue('type');
        $input_name = str_replace(array('[', ']'), '', Tools::getValue('inputName', null));

        $tree = new HelperTreeCategories('subtree_associated_categories');
        $tree->setTemplate('subtree_associated_categories.tpl')
            ->setUseShopRestriction(false)
            ->setUseCheckBox($use_check_box)
            ->setUseSearch(true)
            ->setIdTree($id_tree)
            ->setSelectedCategories($selected)
            ->setFullTree($full_tree)
            ->setChildrenOnly(true)
            ->setNoJS(true)
            ->setRootCategory($category);

        if ($input_name) {
            $tree->setInputName($input_name);
        }

        die($tree->render());
    }
}
