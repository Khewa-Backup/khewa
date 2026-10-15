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
        return Module::getInstanceByName('directlabelprintproduct')->displayLabelLink($token, $id, $name);
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
}
