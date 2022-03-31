<?php
/**
 * 2016-2017 Leone MusicReader B.V.
 *
 * NOTICE OF LICENSE
 *
 * Source file is copyrighted by Leone MusicReader B.V.
 * Only licensed users may install, use and alter it.
 * Original and altered files may not be (re)distributed without permission.
 *
 * @author    Leone MusicReader B.V.
 *
 * @copyright 2016-2017 Leone MusicReader B.V.
 *
 * @license   custom see above
 */

class AdminProductsController extends AdminProductsControllerCore
{

    public function __construct()
    {
        $this->addRowAction('label');
        return parent::__construct();
    }

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

        //$url = Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/directlabelprintproduct/MyText.label';

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
