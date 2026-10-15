<?php
/**
 * 2021 Leone MusicReader B.V.
 *
 * NOTICE OF LICENSE
 *
 * Source file is copyrighted by Leone MusicReader B.V.
 * Only licensed users may install, use and alter it.
 * Original and altered files may not be (re)distributed without permission.
 *
 * @author    Leone MusicReader B.V.
 *
 * @copyright 2021 Leone MusicReader B.V.
 *
 * @license   custom see above
 */

class AdminDirectLabelPrintProductController extends ModuleAdminController
{

    public function ajaxProcessGetProductIds()
    {
        $sql="SELECT DISTINCT id_product, id_product_attribute FROM `"._DB_PREFIX_."product_attribute`";
        $results = Db::getInstance()->ExecuteS($sql);

        $sql2="SELECT DISTINCT id_product FROM `"._DB_PREFIX_."product`".
            " WHERE id_product NOT IN ".
            "(SELECT DISTINCT id_product FROM `"._DB_PREFIX_."product_attribute` WHERE id_product_attribute=0)";
        $results2 = Db::getInstance()->ExecuteS($sql2);

        foreach ($results2 as $r) {
                $r["id_product_attribute"]="0";
                $results[]=$r;
        }

        $this->ajaxDie(json_encode($results));
    }

    public function ajaxProcessGetProductInfo()
    {

        $barcode=Tools::getValue("barcode");

        $id=(int)Tools::getValue("id");
        $comb_id=(int)Tools::getValue("combination_id");
        $module=Module::getInstanceByName('directlabelprintproduct');
        if (mb_strlen($barcode)>0) {
            $product_info = $module->getProductCombinationInfoBarcode($barcode);
            $product_info=$module->convertDoublePricing($product_info);
        } elseif ($comb_id>0) {
            $product_info = $module->getProductCombinationInfo($id, $comb_id);
            $product_info=$module->convertDoublePricing($product_info);
            $product_info["id_combination"]=$comb_id;
        } else {
            $product_info = $module->getProductInfo($id);
            $product_info=$module->convertDoublePricing($product_info);
        }

        $prefix="dlp_pb_";
        if (Configuration::get($prefix."multipleLabelsPerProduct")) {
            $countPerProduct_count=Configuration::get($prefix."multipleLabelsPerProduct_count");
            if (is_numeric($countPerProduct_count)) {
                $product_info["label_per_product_count"]=(int)$countPerProduct_count;
            } elseif (isset($product_info[$countPerProduct_count])
                && is_numeric($product_info[$countPerProduct_count])) {
                    $product_info["label_per_product_count"]=(int)$product_info[$countPerProduct_count];
            } else {
                $product_info["label_per_product_count"]=1;
            }
        } else {
            $product_info["label_per_product_count"]=1;
        }

        $orderid=(int)Tools::getValue("orderid");
        //Add Order Information
        if (Module::getInstanceByName('directlabelprint')!=false && $orderid>0) {
            $order_info=Module::getInstanceByName('directlabelprint')->getOrderInfo($orderid);
            $product_info=array_merge($product_info,$order_info);
        }
        if (Module::getInstanceByName('an_productfields')!=false && $orderid>0) {
            $order=new Order($orderid);
            $cart_id=$order->id_cart;
            $sql='SELECT field_name, value FROM `'. _DB_PREFIX_ . 'an_productfields_cart` WHERE id_cart='.$cart_id.' AND id_product='.$id;

            $sql_result = Db::getInstance()->ExecuteS($sql);

            for ($k = 0; $k < count($sql_result); $k++) {
                $fieldname=$sql_result[$k]["field_name"];
                $field_id="an_productfields_".str_replace(" ","_",$fieldname);
                $product_info[$field_id] = $sql_result[$k]["value"];
                $product_info[strtolower($field_id)] = $sql_result[$k]["value"];
            }
        }

        //Support for serial numbers module.
        $serialnumbers=Module::getInstanceByName("serialnumbers");
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
        //end support

        $product_info_json = Tools::jsonEncode($product_info);
        $this->ajaxDie($product_info_json);
    }

    public function ajaxProcessSearchProductOrCategory()
    {
        $query=Tools::getValue("query");
        $printertype=Module::getInstanceByName('directlabelprintproduct')->getPrinterTypeName();

        $id_lang=Context::getContext()->language->id;

        $products=Product::searchByName($id_lang, $query);
        if (!$products) {
            $products=[];
        }
        for ($i=0; $i<count($products); $i++) {
            $products[$i]=new Product($products[$i]["id_product"]);
        }
        $categories=Category::searchByName($id_lang, $query);
        if (!$categories) {
            $categories=[];
        }
        for ($i=0; $i<count($categories); $i++) {
            $categories[$i]=new Category($categories[$i]["id_category"]);
        }

        if (is_numeric($query)) {
            $found_ids=$this->getExistingIdsFromIdsOrRefs([(int)$query]);
            if (count($found_ids)==1) {
                array_unshift($products, new Product($found_ids[0]));
            }
            if (Category::categoryExists((int)$query)) {
                array_unshift($categories, new Category((int)$query));
            }
        }

        $result=[];

        foreach ($categories as $category) {
            $result["category-".$category->id."-".$printertype]=$category->getName($id_lang);
        }

        foreach ($products as $product) {
            $result["product-".$product->id."-".$printertype]=$product->name[$id_lang];
        }

        $this->ajaxDie(Tools::jsonEncode($result));
    }



    public function ajaxProcessListTemplates()
    {

        $id_lang=Context::getContext()->language->id;
        $printertype=Module::getInstanceByName('directlabelprintproduct')->getPrinterTypeName();

        $results_array = array();
        $results_array["DEFAULT-".$printertype]="Default Template";

        $sql="SELECT id_dlpp_templates FROM " . _DB_PREFIX_ . "dlpp_templates;";

        $result = Db::getInstance()->executeS($sql);


        if (!$result) {
            $this->ajaxDie(Tools::jsonEncode($results_array));
        }

        foreach ($result as $row) {
            $id_string=$row["id_dlpp_templates"];
            $id_parts=explode("-", $id_string);
            $type=$id_parts[0];
            if ($type=="DEFAULT") {
                continue;
            }
            $id=$id_parts[1];
            $printer=$id_parts[2];

            if ($printer==$printertype) {
                if ($type=="product") {
                    $found_ids=$this->getExistingIdsFromIdsOrRefs([(int)$id]);
                    if (count($found_ids)==1) {
                        $product=new Product($found_ids[0]);
                        $results_array[$id_string]=$product->name[$id_lang];
                    }
                } elseif ($type=="category") {
                    if (Category::categoryExists((int)$id)) {
                        $category=new Category((int)$id);
                        $results_array[$id_string]=$category->getName($id_lang);
                    }
                }
            }
        }
        $this->ajaxDie(Tools::jsonEncode($results_array));
    }

    public static function getExistingIdsFromIdsOrRefs($ids_or_refs)
    {
        // separate IDs and Refs
        $ids = array();
        $refs = array();
        $whereStatements = array();
        foreach ((is_array($ids_or_refs) ? $ids_or_refs : array($ids_or_refs)) as $id_or_ref) {
            if (is_numeric($id_or_ref)) {
                $ids[] = (int) $id_or_ref;
            } elseif (is_string($id_or_ref)) {
                $refs[] = '\'' . pSQL($id_or_ref) . '\'';
            }
        }

        // construct WHERE statement with OR combination
        if (count($ids) > 0) {
            $whereStatements[] = ' p.id_product IN (' . implode(',', $ids) . ') ';
        }
        if (count($refs) > 0) {
            $whereStatements[] = ' p.reference IN (' . implode(',', $refs) . ') ';
        }
        if (!count($whereStatements)) {
            return false;
        }

        $results = Db::getInstance()->executeS('
        SELECT DISTINCT `id_product`
        FROM `' . _DB_PREFIX_ . 'product` p
        WHERE ' . implode(' OR ', $whereStatements));

        // simplify array since there is 1 useless dimension.
        // FIXME : find a better way to avoid this, directly in SQL?
        foreach ($results as $k => $v) {
            $results[$k] = (int) $v['id_product'];
        }

        return $results;
    }

    public function ajaxProcessGetTemplateOfId()
    {
        $template_id=Tools::getValue("template_id");
        $data=Module::getInstanceByName('directlabelprintproduct')->getTemplateForId($template_id);
        $printertype=Module::getInstanceByName('directlabelprintproduct')->getPrinterTypeName();

        if ($data==null) {
            $data=Module::getInstanceByName('directlabelprintproduct')->getTemplateOldStorage($printertype);
        }

        if ($data==null) {
            $this->ajaxDie("No Template Found");
        }


        if ($printertype=="dymo") {
            $template_file="labeltemplate.label";
            if (strpos($data, "<DesktopLabel ")>0) {
                $template_file="labeltemplate.dymo";
            }

            header('Content-Type: application/xml');
            header('Content-Disposition: attachment; filename="'.$template_file.'"');
        }

        $this->ajaxDie($data);
    }

    public function ajaxProcessHasTemplateVariants()
    {
        $product_id=Tools::getValue("id_product");
        $module=Module::getInstanceByName('directlabelprintproduct');
        $printertype=$module->getPrinterTypeName();
        if($product_id=="DEFAULT"){
            $default_id="DEFAULT-".$printertype;
            $this->ajaxDie("".$module->hasTemplateVariants($default_id));
        }
        $product_template_id="product-".$product_id."-".$printertype;
        if($module->getTemplateForId($product_template_id)!=null){
            $this->ajaxDie("".$module->hasTemplateVariants($product_template_id));
        }else{
            $product=new Product($product_id);
            $category_template_id="category-".$product->id_category_default."-".$printertype;
            if($module->getTemplateForId($category_template_id)!=null){
                $this->ajaxDie("".$module->hasTemplateVariants($category_template_id));
            }else {
                $default_id="DEFAULT-".$printertype;
                $this->ajaxDie("".$module->hasTemplateVariants($default_id));
            }
        }


    }

    public function ajaxProcessGetTemplate()
    {
        $product_id=Tools::getValue("id_product");
        $variant=(int)Tools::getValue("variant");
        $module=Module::getInstanceByName('directlabelprintproduct');
        $printertype=$module->getPrinterTypeName();

        //Check for product
        $data=$module->getTemplateForId("product-".$product_id."-".$printertype,$variant);

        if ($data==null) {
            //Check categories
            $product=new Product($product_id);
            $data=$this->getTemplateForCategory($product->id_category_default, $printertype,$variant);
            if ($data==null) {
                $categories=Product::getProductCategories($product_id);
                while ($data==null && count($categories)>0) {
                    $list_parents=[];
                    for ($i=0; $i<count($categories)&&$data==null; $i++) {
                        $data=$this->getTemplateForCategory($categories[$i], $printertype,$variant);

                        $category=new Category($categories[$i]);
                        if ($category->id_parent>0) {
                            $list_parents[]=$category->id_parent;
                        }
                    }
                    $categories=$list_parents;
                }
            }
        }

        if ($data==null) {
            //Get Default
            $default_id="DEFAULT-".$printertype;
            $data=Module::getInstanceByName('directlabelprintproduct')->getTemplateForId($default_id,$variant);
        }

        if ($data==null) {
            $data=Module::getInstanceByName('directlabelprintproduct')->getTemplateOldStorage($printertype);
        }

        if ($data==null) {
            $this->ajaxDie("No Template Found");
        } else {
            if ($printertype=="dymo") {
                $template_file="labeltemplate.label";
                if (strpos($data, "<DesktopLabel ")>0) {
                    $template_file="labeltemplate.dymo";
                }

                header('Content-Type: application/xml');
                header('Content-Disposition: attachment; filename="'.$template_file.'"');
            }

            $this->ajaxDie($data);
        }
    }

    private function getTemplateForCategory($id_category, $printertype,$variant=1)
    {
        $template_id="category-".$id_category."-".$printertype;
        return Module::getInstanceByName('directlabelprintproduct')->getTemplateForId($template_id,$variant);
    }
}
