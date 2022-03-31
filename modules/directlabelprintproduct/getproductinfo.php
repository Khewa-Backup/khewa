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
//define('_PS_MODE_DEV_', true);
//error_reporting(0);
require_once("../../config/config.inc.php");

require_once(dirname(__FILE__).'/../../init.php');

$token=Tools::getValue("token");
$token1=Module::getInstanceByName('directlabelprintproduct')->getSecurityToken();

if ($token!=$token1) {
    return;
}

header("Access-Control-Allow-Origin: *");

$barcode=Tools::getValue("barcode");

$id=(int)Tools::getValue("id");
$comb_id=(int)Tools::getValue("combination_id");
if (mb_strlen($barcode)>0) {
    $product_info = Module::getInstanceByName('directlabelprintproduct')->getProductCombinationInfoBarcode($barcode);
    $product_info=Module::getInstanceByName('directlabelprintproduct')->convertDoublePricing($product_info);
} elseif ($comb_id>0) {
    $product_info = Module::getInstanceByName('directlabelprintproduct')->getProductCombinationInfo($id, $comb_id);
    $product_info=Module::getInstanceByName('directlabelprintproduct')->convertDoublePricing($product_info);
    $product_info["id_combination"]=$comb_id;
} else {
    $product_info = Module::getInstanceByName('directlabelprintproduct')->getProductInfo($id);
    $product_info=Module::getInstanceByName('directlabelprintproduct')->convertDoublePricing($product_info);
}


$orderid=(int)Tools::getValue("orderid");
$serialnumbers=Module::getInstanceByName('serialnumbers');
if ($serialnumbers!=false && $orderid>0) {
    $products = Module::getInstanceByName('directlabelprint')->getOrderedProducts($orderid);
    //$product_info_json = Tools::jsonEncode($products);
    //print($product_info_json."\n\r");
    foreach ($products as $product) {
        $same_p_id=$product["product_id"] == $product_info["id_product"];
        if ($same_p_id  && ($comb_id==0 || $product["product_attribute_id"] == $comb_id)) {
            $product_info["serial_test"]="yes";
            $id_order_detail=$product["id_order_detail"];
           // print($orderid."-".$id_order_detail."\n");
            $dlpproduct_module=Module::getInstanceByName('directlabelprintproduct');
            $serials=$dlpproduct_module->getOrderedSerials($orderid, $id_order_detail);
            if (count($serials)>0) {
                $product_info["serial_no"] = $serials;
            }
        }
    }
}

$product_info_json = Tools::jsonEncode($product_info);
print($product_info_json);
