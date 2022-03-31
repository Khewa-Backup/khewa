<?php
class AdminProductsController extends AdminProductsControllerCore
{
    /*
    * module: directlabelprintproduct
    * date: 2021-04-06 05:19:14
    * version: 2.1.1
    */
    public function __construct()
    {
        $this->addRowAction('label');
        return parent::__construct();
    }
    /*
    * module: directlabelprintproduct
    * date: 2021-04-06 05:19:14
    * version: 2.1.1
    */
    public function displayLabelLink($token, $id, $name)
    {
        $combination= Tools::strlen(Tools::getValue("id_product"))>0;
        $product_info="";
        if (!$combination) {
            $product_info = Module::getInstanceByName('directlabelprintproduct')->getProductInfo($id);
        } else {
            $pid=(int)Tools::getValue("id_product");
            $product_info = Module::getInstanceByName('directlabelprintproduct')->getProductCombinationInfo($pid, $id);
        }
        $product_info=Module::getInstanceByName('directlabelprintproduct')->convertDoublePricing($product_info);
        $product_info_json = Tools::jsonEncode($product_info);
        $tpl_file="../../../../modules/directlabelprintproduct/views/templates/admin/list_action_label.tpl";
        $tpl = $this->createTemplate($tpl_file);
        if (!array_key_exists('Label', self::$cache_lang)) {
            self::$cache_lang['Label'] = $this->l('Label', 'Helper');
        }
        $tpl->assign(array(
        'href' => "javascript:void(0);",
        'js' => "printProductLabel(product_label_template,".$product_info_json.");",
        'js_data' => "product_label_template,".$product_info_json,
        'action' => self::$cache_lang['Label'],
        'id' => $id
        ));
        return $tpl->fetch();
    }
    /*
    * module: ordersexportsalesreportpro
    * date: 2021-10-04 05:21:26
    * version: 4.1.7
    */
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
