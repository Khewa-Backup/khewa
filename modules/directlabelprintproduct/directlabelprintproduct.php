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

/*if (!defined('_PS_VERSION_')) {
    exit;
}*/

class DirectLabelPrintProduct extends Module
{
    private $myError;
    private $mySuc;

    public function __construct()
    {
        $this->name = 'directlabelprintproduct';
        $this->tab = 'shipping_logistics';
        $this->version = '2.1.1';
        $this->author = 'Leoné MusicReader B.V.';
        $this->module_key = 'a06117e97ebeb3c978a78e7118573972';

        $this->bootstrap=true;

        parent::__construct();

        $this->displayName = $this->l('Direct Label Print - Product / Barcode Edition');
        $this->description =
            $this->l('Add label print button on products list for names and barcodes. Works with Dymo label printers.');

        $this->prefix="dlp_pb_";
    }

    public function install()
    {
        if (!parent::install()
            || !$this->registerHook('displayBackOfficeHeader')
            /*|| !$this->registerHook('displayhome')*/
            || !$this->registerHook('displayAdminOrder')
            /*|| !$this->registerHook('displayBackOfficeFooter')*/
        ) {
            return false;
        }

        return true;
    }

    public function hookbackofficeheader($params)
    {
        return $this->hookDisplayBackOfficeHeader($params);
    }

    public function hookDisplayBackOfficeHeader($params)
    {
        //Ensure right permission for folder and public PHP files.
            chmod(($this->local_path), 0755);
            chmod(($this->local_path)."getproductids.php", 0755);
            chmod(($this->local_path)."getproductinfo.php", 0755);

        $c_ctrl=$this->context->controller;

        $this->context->controller->addJquery();

        if (strpos($_SERVER['QUERY_STRING'], "directlabelprintproduct")>-1) {
            //Only on configuration page
            if (method_exists($c_ctrl, "addJS")) {
                //include riot.js -->
                $c_ctrl->addJS(($this->_path) ."views/js/riot+compiler.min.js", 'all');
                $c_ctrl->addJS(($this->_path) ."views/js/riot.min.js", 'all');
            }
        }
        $third_party_module=false;
        if (Tools::getValue('configure')=="ec_scan_ean13" && strpos($_SERVER['QUERY_STRING'], "increment")>0) {
            $c_ctrl->addJS(($this->_path) . 'views/js/module_integration.js', 'all');
            $third_party_module=true;
        }
        if (Tools::getValue('controller')=="AdminDmuAdminRecherche") {
            $c_ctrl->addJS(($this->_path) . 'views/js/module_integration.js', 'all');
            $third_party_module=true;
        }

        //Only on product, stock and order pages
        $isStockPage=strpos(Tools::strtolower($_SERVER["REQUEST_URI"]), "stock")>-1;
        $isProductPage=strpos(Tools::strtolower($_SERVER["REQUEST_URI"]), "product")>-1;
        $isOrderPage=strpos(Tools::strtolower($_SERVER["REQUEST_URI"]), "order")>-1;
        $isModule=strpos(Tools::strtolower($_SERVER["REQUEST_URI"]), $this->name)>-1;

        if ($isStockPage || $isProductPage || $isOrderPage || $isModule || $third_party_module) {
            $printerset=Configuration::get('label_printertypeset');
            if (method_exists($c_ctrl, "addJS")) {
                $c_ctrl->addJS(($this->_path) . 'views/js/directlabelprint.js', 'all');
                $printerset=Configuration::get('label_printertypeset');
                $isDymo1=(Configuration::get('label_printertype') && !Tools::isSubmit('printertype_submit'));
                $isDymo2=(Tools::isSubmit('printertype_submit') && Tools::getValue('printertype'));
                $isDymo=$isDymo1 || $isDymo2;
                if ($isDymo && $printerset) {//Dymo
                    $c_ctrl->addJS(($this->_path) . 'views/js/DYMO.Label.Framework.latest.js', 'all');
                } else { //Generic Printer
                    $c_ctrl->addJS(($this->_path) . 'views/js/genericprintersupport.js', 'all');
                    $c_ctrl->addJS(($this->_path) . 'views/js/html2canvas.js', 'all');
                    $c_ctrl->addJS(($this->_path) . 'views/js/JsBarcode.all.min.js', 'all');
                    $c_ctrl->addJS(($this->_path) . 'views/js/qrcode.js', 'all');
                    $c_ctrl->addJS(($this->_path) . 'views/js/canvas2svg.js', 'all');
                }
                if ($printerset || Tools::isSubmit('printertype_submit')) {
                    if (strpos($_SERVER['QUERY_STRING'], "directlabelprintproduct")>-1) { //Only on configuration page
                        $c_ctrl->addJS(($this->_path) . 'views/js/summernote.js', 'all');
                        $c_ctrl->addCSS(($this->_path) . 'views/css/summernote.css', 'all');
                        //$c_ctrl->addJS(($this->_path) . 'views/js/bootstrap.js', 'all');
                        //$c_ctrl->addCSS(($this->_path) . 'views/css/bootstrap.css', 'all');
                    }
                }

                $c_ctrl->addCSS(($this->_path) . 'views/css/directlabelprint.css', 'all');
            }

            $w=Configuration::get($this->prefix.'width_input');
            if (!$w) {
                $w = 100;
            }
            $h=Configuration::get($this->prefix.'height_input');
            if (!$h) {
                $h = 50;
            }
            $r=Configuration::get($this->prefix.'rotate_image');
            if (!$r) {
                $r = 0;
            }
            $printertypeset="true";
            if (!Configuration::get('label_printertypeset')) {
                $printertypeset = "false";
            }

            $printer_type_isDymo="false";
            $printer_type_isGeneric="false";
            if (Configuration::get('label_printertype')) {
                $printer_type_isDymo="true";
            } else {
                $printer_type_isGeneric="true";
            }

            $url = Tools::getShopDomainSsl(true, true)
                . __PS_BASE_URI__
                . 'modules/directlabelprintproduct/MyText.label';
            $dlppb_module_folder = Tools::getShopDomainSsl(true, true)
                . __PS_BASE_URI__
                . 'modules/directlabelprintproduct/';

            //SDI selected printer
            $selectedDymoIndex=Configuration::get('selectedDymoIndex_dlpp', null, null, null, 0);
            if (!$selectedDymoIndex) {
                $selectedDymoIndex = 0;
            }

            //Selected Tray DUO
            $dymoPrinterIndex=Configuration::get('dymoPrinterIndex_dlpp');
            if ($dymoPrinterIndex!=1 && $dymoPrinterIndex!="1") {
                $dymoPrinterIndex = 0;
            }

            $this->smarty->assign(array(
                'token' => $this->getSecurityToken(),
                'printertypeset' => $printertypeset,
                'generic_label_width' => $w,
                'generic_label_height' => $h,
                'generic_label_rotate' => $r,
                'generic_label_content' => preg_replace("/\r|\n/", "", $this->getLabelTemplate()),
                'product_label_template' => $url,
                'dlppb_module_folder' => $dlppb_module_folder,
                'dlppb_printer_type_isGeneric'=>$printer_type_isGeneric,
                'dlppb_printer_type_isDymo'=>$printer_type_isDymo,
                'selectedDymoIndex'=>$selectedDymoIndex,
                'dymoPrinterIndex'=>$dymoPrinterIndex
            ));

            return $this->display(__FILE__, 'views/templates/admin/header.tpl');
        } else {
            return "";
        }
    }


    public function getProductInfo($product_id = null)
    {
        /*https://github.com/pal/prestashop/blob/master/classes/Product.php*/

        if ($product_id == null) {
            return array();
        } else {
            $product = new Product($product_id, true);
            $fields=$product->getFields();
            $fields["language_id"]=$this->getLanguageID();
            $fields["product_name"]=$product->name[$this->getLanguageID()];
            if ($fields["product_name"]==null) {
                $fields["product_name"]=$product->name[$this->context->language->id];
            }
            $languages=Language::getLanguages(true);
            foreach ($languages as $lang) {
                if (isset($lang["iso_code"]) && isset($lang["id_lang"]) && isset($product->name[$lang["id_lang"]])) {
                    $fields["product_name_" . $lang["iso_code"]] = $product->name[$lang["id_lang"]];
                }
            }
            $fields["product_name_xx"]="Replace XX with language code (product_name_en)";

            $fields["description"]=$product->description[$this->getLanguageID()];
            if ($fields["description"]==null) {
                $fields["description"]=$product->description[$this->context->language->id];
            }
            $fields["description"]=htmlspecialchars(strip_tags($fields["description"]));
            $languages=Language::getLanguages(true);
            foreach ($languages as $lang) {
                if (isset($lang["iso_code"]) && isset($lang["id_lang"])) {
                    $id_lang=$lang["id_lang"];
                    $iso=$lang["iso_code"];
                    if (isset($product->description[$id_lang])) {
                        $fields["description_" . $iso] = htmlspecialchars(strip_tags($product->description[$id_lang]));
                    }
                }
            }
            $fields["description_xx"]="Replace XX with language code (description_en)";

            $fields["description_short"]=$product->description_short[$this->getLanguageID()];
            if ($fields["description_short"]==null) {
                $fields["description_short"]=$product->description_short[$this->context->language->id];
            }
            $fields["description_short"]=htmlspecialchars(strip_tags($fields["description_short"]));
            $languages=Language::getLanguages(true);
            foreach ($languages as $lang) {
                if (isset($lang["iso_code"]) && isset($lang["id_lang"])) {
                    if (isset($product->description_short[$lang["id_lang"]])) {
                        $value=$product->description_short[$lang["id_lang"]];
                        $fields["description_short_" . $lang["iso_code"]] = htmlspecialchars(strip_tags($value));
                    }
                }
            }
            $fields["description_short_xx"]="Replace XX with language code (description_short_en)";

            $fields["manufacturer_name"]=$product->manufacturer_name;
            $fields["supplier_name"]=$product->supplier_name;
            $fields["all_attributes"]="";
            $fields["all_attributes_multiple_lines"]="";
            $fields["all_attributes_values_only"]="";

            $link = new Link();
            $fields["product_website_url"] = $link->getProductLink($product);

            if ($product->reference) {
                $fields["reference"] = $product->reference;
            } elseif (Configuration::get($this->prefix.'auto_generate_reference')) {
                //Create new reference
                $new_reference=$this->getNewReference();
                $product->reference=$new_reference;
                $product->save();
                $fields["reference"]=$new_reference;
            }

            if ($product->ean13) {
                $fields["ean13"] = $product->ean13;
            } elseif (Configuration::get($this->prefix.'auto_generate_ean')) {
                //Create new reference
                $new_ean=$this->getNewEAN();
                if ($new_ean!=0) {
                    $product->ean13=$new_ean;
                    $product->save();
                    $fields["ean13"]=$new_ean;
                }
            }

            if ($product->upc) {
                $fields["upc"] = $product->upc;
            } elseif (Configuration::get($this->prefix.'auto_generate_UPC')) {
                //Create new reference
                $new_upc=$this->getNewUPC();
                if ($new_upc!=0) {
                    $product->upc=$new_upc;
                    $product->save();
                    $fields["upc"]=$new_upc;
                }
            }

            //Retrieve Supplier Reference
            if (version_compare(_PS_VERSION_, "1.6.0.0") >= 0 && $product->id_supplier>0) {
                $id_s=$product->id_supplier;
                $fields["supplier_reference"]=ProductSupplier::getProductSupplierReference($product_id, 0, $id_s);
            }

            foreach ($fields as $key => $value) {
                if (is_string($value)) {
                    $fields[$key]=htmlspecialchars($value);
                    $fields[$key]=str_replace("'", " ", $fields[$key]);
                }
            }

            $price_incl_tax=$this->getPriceInclTax($product_id, null);
            $fields["price_incl_tax"] = "".$price_incl_tax;

            $discount_price_incl_tax=$this->getDiscountPriceInclTax($product_id, null);
            $fields["discount_price_incl_tax"] = "".$discount_price_incl_tax;

            $discount_incl_tax=($price_incl_tax-$discount_price_incl_tax);
            $fields["discount_incl_tax"] = "".$discount_incl_tax;

            if ($fields["unit_price_ratio"]>0) {
                $fields["unit_price_incl_tax"]=$this->convertDoubleToComma($price_incl_tax/$fields["unit_price_ratio"]);
                $unit_price_excl_tax=$fields["price"]/$fields["unit_price_ratio"];
                $fields["unit_price_excl_tax"]=$this->convertDoubleToComma($unit_price_excl_tax);
            }

            if ($price_incl_tax>0) {
                $discount_percentage = round($discount_incl_tax / $price_incl_tax);
            } else {
                $discount_percentage=0;
            }
            $fields["discount_percentage"] = $discount_percentage."%";

            $features=$product->getFeatures();
            $language_id=$this->getLanguageID();
            foreach ($features as $k => $v) {
                    $id_feature=$v["id_feature"];
                    $id_feature_value=$v["id_feature_value"];
                    $feature = new Feature($id_feature);
                    $feature_name_string="feature_".str_replace(" ","_",trim($feature->name[$language_id]));
                    $feature_value=new FeatureValue($id_feature_value);
                    $feature_value_string=$feature_value->value[$language_id];
                    $fields[$feature_name_string]=$feature_value_string;
                    $fields[strtolower($feature_name_string)]=$feature_value_string;
            }

            $expiration_field_name="feature_days_to_expiration";
            if(isset($fields[$expiration_field_name]) && "".intval($fields[$expiration_field_name])==$fields[$expiration_field_name]){
                $fields["expiration_date"]=date("Y-m-d",time()+intval($fields[$expiration_field_name])*24*60*60);
            }

            $fields["current_date"]=date("Y-m-d",time());

            return $fields;
        }
    }

    public function getProductCombinationInfo($product_id, $id)
    {
        $fields=$this->getProductInfo($product_id);
        $combination = new Combination($id);

        $product = new Product($product_id, true);

        if ($combination->id_product) {
            $fields["id_product"] = $combination->id_product;
        }
        if ($combination->reference) {
            $fields["reference"] = $combination->reference;
        } elseif (Configuration::get($this->prefix.'auto_generate_reference')) {
            //Create new reference
                $new_reference=$this->getNewReference();
                $combination->reference=$new_reference;
                $combination->save();
                $fields["reference"]=$new_reference;
        }
        if ($combination->ean13) {
            $fields["ean13"] = $combination->ean13;
        } elseif (Configuration::get($this->prefix.'auto_generate_ean')) {
            //Create new reference
            $new_ean=$this->getNewEAN();
            if ($new_ean!=0) {
                $combination->ean13=$new_ean;
                $combination->save();
                $fields["ean13"]=$new_ean;
            }
        }
        if ($combination->supplier_reference) {
            $fields["supplier_reference"] = $combination->supplier_reference;
        }
        if ($combination->location) {
            $fields["location"] = $combination->location;
        }
        if ($combination->ean13) {
            $fields["ean13"] = $combination->ean13;
        }
        if (isset($combination->isbn) && $combination->isbn) {
            $fields["isbn"] = $combination->isbn;
        }
        if ($combination->upc) { /*property_exists */
            $fields["upc"] = $combination->upc;
        }elseif (Configuration::get($this->prefix.'auto_generate_UPC')) {
            //Create new reference
            $new_UPC=$this->getNewUPC();
            if ($new_UPC!=0) {
                $combination->upc=$new_UPC;
                $combination->save();
                $fields["upc"]=$new_UPC;
            }
        }
        if ($combination->wholesale_price) {
            $fields["wholesale_price"] = $combination->wholesale_price;
        }
        if ($combination->unit_price_impact) {
            $fields["unit_price_impact"] = $combination->unit_price_impact;
            $price_incl_tax_normal=$this->getPriceInclTax($product_id, null);
            $price_excl_tax_normal=$fields["price"];
            $unit_price_excl_tax=0;
            if ($fields["unit_price_ratio"]>0) {
                $unit_price_excl_tax = ($price_excl_tax_normal / $fields["unit_price_ratio"]);
                $unit_price_excl_tax = $unit_price_excl_tax + $combination->unit_price_impact;
            }
            $unit_price_incl_tax=($unit_price_excl_tax*$price_incl_tax_normal)/$price_excl_tax_normal;
            $fields["unit_price_incl_tax"]=$this->convertDoubleToComma($unit_price_incl_tax);
            $fields["unit_price_excl_tax"]=$this->convertDoubleToComma($unit_price_excl_tax);
        }
        if ($combination->price && $combination->price>0) {
            $fields["price"] = $combination->price;
        }
        if ($combination->ecotax) {
            $fields["ecotax"] = $combination->ecotax;
        }
        if ($combination->minimal_quantity) {
            $fields["minimal_quantity"] = $combination->minimal_quantity;
        }
        if ($combination->quantity) {
            $fields["quantity"] = $combination->quantity;
        }
        if ($combination->weight) {
            $fields["weight"] = (int)$fields["weight"]+(int)$combination->weight;
        }
        if ($combination->default_on) {
            $fields["default_on"] = $combination->default_on;
        }
        if ($combination->available_date) {
            $fields["available_date"] = $combination->available_date;
        }

        //Retrieve Supplier Reference
        $version_compare=version_compare(_PS_VERSION_, "1.6.0.0") >= 0;
        if ($version_compare && $product->id_supplier>0 && $id>0) {
            $id_s=$product->id_supplier;
            $fields["supplier_reference"]=ProductSupplier::getProductSupplierReference($product_id, $id, $id_s);
        }

        $price_incl_tax=$this->getPriceInclTax($product_id, $id);
        $fields["price_incl_tax"] = "".$price_incl_tax;
        $fields["price"] = "".$this->getPriceExclTax($product_id, $id);

        $fields["discount_price_incl_tax"] = "".$this->getDiscountPriceInclTax($product_id, $id);

        $fields["all_attributes"]=htmlspecialchars($this->combinationName($product_id, $id));
        $fields["all_attributes_multiple_lines"]=str_replace(",", "||", $fields["all_attributes"]);
        $attr_lines=explode(",", $fields["all_attributes"]);
        $fields["all_attributes_values_only"]="";
        foreach ($attr_lines as $k => $v) {
            $attr_parts=explode("-", $v);
            if (count($attr_parts)>1) {
                $fields["all_attributes_values_only"] .= trim($attr_parts[1])." ";
                $field_name=str_replace(" ","_",trim($attr_parts[0]));
                $fields["attribute_".$field_name]=trim($attr_parts[1]);
                $field_lower_case=strtolower($field_name);
                $value_field=trim($attr_parts[1]);
                $fields["attribute_".$field_lower_case]=$value_field;
            }
        }

        $expiration_field_name="attribute_days_to_expiration";
        if(isset($fields[$expiration_field_name]) && "".intval($fields[$expiration_field_name])==$fields[$expiration_field_name]){
            $fields[$fields["expiration_date"]]=date("Y-m-d",time()+intval($fields[$expiration_field_name])*24*60*60);
        }

        return $fields;
    }

    public function getLanguageID()
    {
        $orderid=(int)Tools::getValue("orderid", "0");
        if ($orderid>0) {
            $order = new Order($orderid);
            return $order->id_lang;
        } else {
            return $this->context->language->id;
        }
    }

    public function getNewReference()
    {
        $id_field_name=$this->prefix."auto_reference_id";
        $last_id=0;
        if (Configuration::get($id_field_name)) {
            $last_id=(int)(Configuration::get($id_field_name));
        }
        $last_id++;
        Configuration::updateValue($id_field_name, "".$last_id);
        return str_pad($last_id, 7, '0', STR_PAD_LEFT);
    }

    public function getNewEAN()
    {
        $id_field_name=$this->prefix."auto_ean_id";
        $last_id=0;
        if (Configuration::get($id_field_name)) {
            $last_id=(int)(Configuration::get($id_field_name));
        }
        $last_id++;
        Configuration::updateValue($id_field_name, "".$last_id);

        //$code = '247' . str_pad($last_id, 9, '0');
        $code_int = $this->getEANStartValue() + $last_id;

        if ($code_int>=$this->getEANEndValue()) {
                return 0;
        }

        $code = (string)($code_int);
        $weightflag = true;
        $sum = 0;
        // Weight for a digit in the checksum is 3, 1, 3.. starting from the last digit.
        // loop backwards to make the loop length-agnostic. The same basic functionality
        // will work for codes of different lengths.
        for ($i = Tools::strlen($code) - 1; $i >= 0; $i--) {
            $sum += (int)$code[$i] * ($weightflag?3:1);
            $weightflag = !$weightflag;
        }
        $code .= (10 - ($sum % 10)) % 10;
        return $code;
    }

    public function getEANStartValue()
    {
        $start=(float)247000000000;
        $id_field_name=$this->prefix."auto_ean_start";
        if (Configuration::get($id_field_name)) {
            $start=(float)(Tools::substr(Configuration::get($id_field_name), 0, 12));
        }
        return $start;
    }

    public function getEANEndValue()
    {
        $end=(float)999999999999;
        $id_field_name=$this->prefix."auto_ean_end";
        if (Configuration::get($id_field_name)) {
            $end=(float)(Tools::substr(Configuration::get($id_field_name), 0, 12));
        }
        return $end;
    }

    public function getNewUPC()
    {
        $id_field_name=$this->prefix."auto_UPC_id";
        $last_id=0;
        if (Configuration::get($id_field_name)) {
            $last_id=(int)(Configuration::get($id_field_name));
        }
        $last_id++;
        Configuration::updateValue($id_field_name, "".$last_id);

        //$code = '247' . str_pad($last_id, 9, '0');
        $code_int = $this->getUPCStartValue() + $last_id;

        if($this->getUPCStartValue()==0){
            return 0;
        }

        if ($code_int>=$this->getUPCEndValue()) {
            return 0;
        }

        $code = (string)($code_int);
        $weightflag = true;
        $sum = 0;
        // Weight for a digit in the checksum is 3, 1, 3.. starting from the last digit.
        // loop backwards to make the loop length-agnostic. The same basic functionality
        // will work for codes of different lengths.
        for ($i = Tools::strlen($code) - 1; $i >= 0; $i--) {
            $sum += (int)$code[$i] * ($weightflag?3:1);
            $weightflag = !$weightflag;
        }
        $code .= (10 - ($sum % 10)) % 10;
        return $code;
    }


    public function getUPCStartValue()
    {
        $start=(float)0;
        $id_field_name=$this->prefix."auto_UPC_start";
        if (Configuration::get($id_field_name)) {
            $start=(float)(Tools::substr(Configuration::get($id_field_name), 0, 11));
        }
        return $start;
    }

    public function getUPCEndValue()
    {
        $end=(float)0;
        $id_field_name=$this->prefix."auto_UPC_end";
        if (Configuration::get($id_field_name)) {
            $end=(float)(Tools::substr(Configuration::get($id_field_name), 0, 11));
        }
        return $end;
    }

    public function getProductCombinationInfoBarcode($barcode)
    {
        $id_product=(int)$barcode;
        if ("".$id_product!=trim($barcode)) {
            $id_product="9328747235943675361";
        }

        //CHECK Combinations
        $sql='SELECT `id_product`,`id_product_attribute`
            FROM `'._DB_PREFIX_.'product_attribute`
            WHERE `ean13` = \''.$barcode.'\' OR `upc` = \''.$barcode.'\'  OR `reference` = \''.$barcode.'\'
             OR  `id_product` = \''.$id_product.'\' OR  `supplier_reference` = \''.$barcode.'\'';
        $result = Db::getInstance()->executeS($sql);
        $total = count($result);

        //print("count1:".$total);

        if (!$result || $total<1) {
            $result = Db::getInstance()->executeS('
            SELECT `id_product`
            FROM `'._DB_PREFIX_.'product`
            WHERE `ean13` = \''.$barcode.'\' OR `upc` = \''.$barcode.'\'  OR `reference` = \''.$barcode.'\'
                 OR  `id_product` = \''.$id_product.'\' OR  `supplier_reference` = \''.$barcode.'\'
            ');
            $total = count($result);
            //print("count2:".$total);

            /*for($i=0;$i<$total;$i++){
                print("id:".$result[$i]['id_product']);
            }*/
        }

        if (!$result || $total<1) {
            $result = Db::getInstance()->executeS('
            SELECT `id_product`,`id_product_attribute`
            FROM `'._DB_PREFIX_.'product_supplier`
            WHERE `product_supplier_reference` = \''.$barcode.'\'
            ');
            $total = count($result);
            //print("count3:".$total);
        }
        if ($result && $total>0) {
            $idpa=(int)$result[0]['id_product_attribute'];
            if ($idpa>0) {
                return $this->getProductCombinationInfo((int)$result[0]['id_product'], $idpa);
            } else {
                return $this->getProductInfo((int)$result[0]['id_product']);
            }
            //print("count4:".$total);
        }
        return "not found";
    }


    public function convertDoublePricing($fields)
    {
        if (array_key_exists("price_incl_tax", $fields)) {
            $fields["price_incl_tax"] = $this->convertDoubleToComma($fields["price_incl_tax"]);
        }
        if (array_key_exists("price", $fields)) {
            $fields["price"] = $this->convertDoubleToComma($fields["price"]);
        }
        if (array_key_exists("discount_price_incl_tax", $fields)) {
            $fields["discount_price_incl_tax"]=$this->convertDoubleToComma($fields["discount_price_incl_tax"]);
        }
        if (array_key_exists("wholesale_price", $fields)) {
            $fields["wholesale_price"]=$this->convertDoubleToComma($fields["wholesale_price"]);
        }
        return $fields;
    }

    private function convertDoubleToComma($value)
    {
        $fval=0.00;
        if ($value!=null && Tools::strlen($value)>0) {
            $fval = (float)$value;
        }
        return  number_format($fval, 2, ",", ".");
    }

    private function combinationName($product_id, $id)
    {
        $langid=$this->getLanguageID();
        $product = new Product($product_id, true);
        $combinations = $product->getAttributeCombinations($langid);
        $comb_array = array();
        if (is_array($combinations)) {
            foreach ($combinations as $k => $combination) {
                $comb_array[$combination['id_product_attribute']]['attributes'][] = array(
                    $this->getAttributeGroupPublicName($combination['id_attribute_group'], $langid),
                    $combination['attribute_name'],
                    $combination['id_attribute']
                );
            }

            foreach ($comb_array as $id_product_attribute => $product_attribute) {
                if ($id_product_attribute==$id) {
                    $list = '';

                    /* In order to keep the same attributes order */
                    asort($product_attribute['attributes']);

                    foreach ($product_attribute['attributes'] as $attribute) {
                        $list .= $attribute[0] . ' - ' . $attribute[1] . ', ';
                    }

                    $list = rtrim($list, ', ');
                    return $list;
                }
            }
        }
        return "";
    }

    private function getAttributeGroupPublicName($id, $id_lang)
    {
        $group = new AttributeGroup($id, $id_lang);
        return $group->public_name;
    }

    private function getPriceInclTax($id_product, $id_product_attribute)
    {
        $specific_price_output = null;
        return Product::getPriceStatic(
            $id_product,
            true,
            $id_product_attribute,
            2,
            null,
            false,
            false,
            1,
            false,
            null,
            null,
            null,
            $specific_price_output,
            true,
            true,
            null,
            true,
            null
        );
    }

    private function getPriceExclTax($id_product, $id_product_attribute)
    {
        $specific_price_output = null;
        return Product::getPriceStatic(
            $id_product,
            false,
            $id_product_attribute,
            2,
            null,
            false,
            false,
            1,
            false,
            null,
            null,
            null,
            $specific_price_output,
            true,
            true,
            null,
            true,
            null
        );
    }

    private function getDiscountPriceInclTax($id_product, $id_product_attribute)
    {
        $specific_price_output = null;
        return Product::getPriceStatic(
            $id_product,
            true,
            $id_product_attribute,
            2,
            null,
            false,
            true,
            1,
            false,
            null,
            null,
            null,
            $specific_price_output,
            true,
            true,
            null,
            true,
            null
        );
    }

    private function uploadlabel()
    {
        $file_path = $_FILES["filelabel"]["tmp_name"];
        $file_name = $_FILES["filelabel"]["name"];
        $extension = $this->getExtension($file_name);
        if ($extension == '.label') {
            $labelfile = 'MyText.label';
            $new_path = dirname(__FILE__) . '/' . $labelfile;

            if (move_uploaded_file($file_path, $new_path)) {
                $this->mySuc = 'Label updated';
                return true;
            };
        } else {
            $this->myError = 'File type is not valid';
        }
        if (empty($this->myError)) {
            $this->myError = 'There is problem while uploading the label';
        }
        return false;
    }

    public function getExtension($str)
    {
        $i = strrpos($str, ".");
        if (!$i) {
            return "";
        }
        $ext = Tools::substr($str, $i);
        return Tools::strtolower($ext);
    }

    public function getLabelTemplate()
    {
        $label_content=urldecode(Configuration::get($this->prefix.'label_content'));
        if (!Configuration::get($this->prefix.'label_content')) {
            $label_content = '<p align="center"><b><span style="font-size: 30px;">[[product_name]]</span></b><br>'.
            '<img class="barcode" name="[[ean13]]" src="'.($this->_path).
            'views/img/barcode.svg" style="width:381px; height:202px;"><br>'.
            '<b><span style="font-size: 16px;">[[ean13]]</span></b>'.
            '</p><p align="center"><b><span style="font-size: 24px;">&euro; [[price_incl_tax]]</span>'.
            '<span style="font-size: 18px;"><br></span></b></p>';
        }
        //$imgurl=($this->_path) . 'views/img/qrcode.png',$label_content;
        //$label_content=str_replace('{$qrcode_sample_url|escape:\'html\':\'UTF-8\'}',$imgurl);
        return $label_content;
    }

    public function displayForm()
    {
        $url = Tools::getShopDomainSsl(true, true)
            . __PS_BASE_URI__
            . 'modules/directlabelprintproduct/MyText_sample.label';

        $printertype="false"; //Generic Printer
        if (Configuration::get('label_printertype')) {
            $printertype = "true"; //Dymo
        }
        $printertypeerror="Please select printer type and press SAVE";
        if (Configuration::get('label_printertypeset')) {
            $printertypeerror=null;
        }

        $width_input="100";
        if (Configuration::get($this->prefix.'width_input')) {
            $width_input=Configuration::get($this->prefix.'width_input');
        }
        $height_input="50";
        if (Configuration::get($this->prefix.'height_input')) {
            $height_input=Configuration::get($this->prefix.'height_input');
        }
        $rotate_image="false"; //Generic Printer
        if (Configuration::get($this->prefix.'rotate_image')) {
            $rotate_image = "true"; //Dymo
        }

        $label_content=$this->getLabelTemplate();

        //SDI selected printer
        $selectedDymoIndex=Configuration::get('selectedDymoIndex_dlpp', null, null, null, 0);
        if (!$selectedDymoIndex) {
            $selectedDymoIndex = 0;
        }

        //Selected DUO side
        $dymoPrinterIndexActive="false";
        $dymoPrinterIndex=Configuration::get('dymoPrinterIndex_dlpp');
        if ($dymoPrinterIndex!=1 && $dymoPrinterIndex!="1") {
            $dymoPrinterIndex = 0;
        } else {
            $dymoPrinterIndexActive="true";
        }

        $autoFirstReferenceCounter=(int)Configuration::get($this->prefix.'auto_reference_id')+1;

        $autoGenerateReference="false";
        if (Configuration::get($this->prefix.'auto_generate_reference')) {
            $autoGenerateReference = "true";
        }

        $autoGenerateEAN="false";
        if (Configuration::get($this->prefix.'auto_generate_ean')) {
            $autoGenerateEAN = "true";
        }

        $autoEANStart="".$this->getEANStartValue();
        if (Configuration::get($this->prefix."auto_ean_start")) {
            $autoEANStart=Configuration::get($this->prefix."auto_ean_start");
        }

        $autoEANEnd="";
        if (Configuration::get($this->prefix."auto_ean_end")) {
            $autoEANEnd=Configuration::get($this->prefix."auto_ean_end");
        }

        $autoGenerateUPC="false";
        if (Configuration::get($this->prefix.'auto_generate_UPC')) {
            $autoGenerateUPC = "true";
        }

        $autoUPCStart="".$this->getUPCStartValue();
        if (Configuration::get($this->prefix."auto_UPC_start")) {
            $autoUPCStart=Configuration::get($this->prefix."auto_UPC_start");
        }

        $autoUPCEnd="";
        if (Configuration::get($this->prefix."auto_UPC_end")) {
            $autoUPCEnd=Configuration::get($this->prefix."auto_UPC_end");
        }




        $this->smarty->assign(array(
            'templateurl' => $url,
            'formactionurl' => $_SERVER['REQUEST_URI'],
            'error' => $this->myError,
            'success' => $this->mySuc,
            'iconurl' => ($this->_path) . 'views/img/icon-print-16.png',
            'imgfolder' => ($this->_path) . 'views/img/',
            'printertype' => $printertype,
            'printertypeerror' => $printertypeerror,
            'width_input' => $width_input,
            'height_input' => $height_input,
            'rotate_image' => $rotate_image,
            'label_content' => $label_content,
            'barcode_sample_url' => ($this->_path) . 'views/img/barcode.svg',
            'qrcode_sample_url' => ($this->_path) . 'views/img/qrcode.svg',
            'selectedDymoIndex' => $selectedDymoIndex,
            'dymoPrinterIndex'=>$dymoPrinterIndex,
            'dymoPrinterIndexActive'=>$dymoPrinterIndexActive,
            'autoGenerateReference'=>$autoGenerateReference,
            'autoFirstReferenceCounter'=>$autoFirstReferenceCounter,
            'autoGenerateEAN' => $autoGenerateEAN,
            'autoGenerateEAN_StartValue' => $autoEANStart,
            'autoGenerateEAN_EndValue' => $autoEANEnd,
            'autoGenerateUPC' => $autoGenerateUPC,
            'autoGenerateUPC_StartValue' => $autoUPCStart,
            'autoGenerateUPC_EndValue' => $autoUPCEnd
        ));

        $html=$this->display(__FILE__, 'views/templates/admin/prestui/ps-tags.tpl');
        return $html.$this->display(__FILE__, 'views/templates/admin/settings.tpl');
    }

    public function getContent()
    {
        if (Tools::isSubmit('upload')) {
            $this->uploadlabel() ;
        }
        if (Tools::isSubmit('printertype_submit')) {
            Configuration::updateValue('label_printertype', Tools::getValue('printertype'));
            Configuration::updateValue('label_printertypeset', "set");
        }
        if (Tools::isSubmit('generic_label_submit')) {
            Configuration::updateValue($this->prefix.'width_input', Tools::getValue('width_input'));
            Configuration::updateValue($this->prefix.'height_input', Tools::getValue('height_input'));
            Configuration::updateValue($this->prefix.'rotate_image', Tools::getValue('rotate_image'));
            Configuration::updateValue($this->prefix.'label_content', urlencode(Tools::getValue('label_content')));
        }
        if (Tools::isSubmit('dymoSettings')) {
            Configuration::updateValue('dymoPrinterIndex_dlpp', Tools::getValue('dymoPrinterIndex'));
            Configuration::updateValue('selectedDymoIndex_dlpp', Tools::getValue('selectedDymoIndex')); //SDI
        }
        if (Tools::isSubmit('otherSettings')) {
            $pf=$this->prefix;
            Configuration::updateValue($pf.'auto_generate_reference', Tools::getValue('autoGenerateReference'));
            Configuration::updateValue($pf.'auto_generate_ean', Tools::getValue('autoGenerateEAN'));

            Configuration::updateValue($pf.'auto_reference_id', (int)Tools::getValue('autoFirstReferenceCounter')-1);

            $start_value=trim(Tools::getValue('autoGenerateEAN_StartValue'));
            $start_value_len=Tools::strlen($start_value);
            if ($start_value_len==12 || $start_value_len==13) {
                $start_value_float12=(float)(Tools::substr($start_value, 0, 12));
                if ($start_value_float12!=$this->getEANStartValue()) {
                    Configuration::updateValue($this->prefix."auto_ean_start", $start_value);
                    Configuration::updateValue($this->prefix."auto_ean_id", "0"); //reset counter
                }
            }

            $end_value=trim(Tools::getValue('autoGenerateEAN_EndValue'));
            $end_value_len=Tools::strlen($end_value);
            if ($end_value_len==12 || $end_value_len==13) {
                $end_value_int12=(int)(Tools::substr($end_value, 0, 12));
                if ($end_value_int12!=$this->getEANEndValue()) {
                    Configuration::updateValue($this->prefix."auto_ean_end", $end_value);
                }
            }

            Configuration::updateValue($pf.'auto_generate_UPC', Tools::getValue('autoGenerateUPC'));

            $start_value=trim(Tools::getValue('autoGenerateUPC_StartValue'));
            $start_value_len=Tools::strlen($start_value);
            if ($start_value_len==11 || $start_value_len==12) {
                $start_value_float11=(float)(Tools::substr($start_value, 0, 11));
                if ($start_value_float11!=$this->getUPCStartValue()) {
                    Configuration::updateValue($this->prefix."auto_UPC_start", $start_value);
                    Configuration::updateValue($this->prefix."auto_UPC_id", "0"); //reset counter
                }
            }

            $end_value=trim(Tools::getValue('autoGenerateUPC_EndValue'));
            $end_value_len=Tools::strlen($end_value);
            if ($end_value_len==11 || $end_value_len==12) {
                $end_value_int11=(int)(Tools::substr($end_value, 0, 11));
                if ($end_value_int11!=$this->getUPCEndValue()) {
                    Configuration::updateValue($this->prefix."auto_UPC_end", $end_value);
                }
            }
        }

        return $this->displayForm();
    }

    //For use with Serials Module
    public function getOrderedSerials($id_order, $id_order_detail)
    {
        $serialnumbers=Module::getInstanceByName('serialnumbers');
        if ($serialnumbers!=false) {
            $ordered_serials = $serialnumbers->getOrderProductsKey($id_order);
            for ($j = 0; $j < count($ordered_serials); $j++) {
                $serial = $ordered_serials[$j];
                if ($serial["id_order_detail"] == $id_order_detail) {
                    $keys = Db::getInstance()->ExecuteS(
                        'SELECT k.*, ' . $serialnumbers->sql_key_value . ' AS `key_val_decode`
                                    FROM `' . _DB_PREFIX_ . 'keymanager` k
                                    WHERE k.`id_order_detail` = ' . (int)$serial['id_order_detail'] . '
                                    AND k.`active` = 1
                                    AND k.`deleted` = 0
                                    ORDER BY k.`id_keymanager` ASC
                                    LIMIT ' . (int)$serial['quantity']
                    );

                    $key_str = [];
                    for ($k = 0; $k < count($keys); $k++) {
                        $key_str[] = $keys[$k]["key_val_decode"];
                    }
                    return $key_str;
                }
            }
        }
        return [];
    }

    public function getSecurityToken()
    {
        $passwd="DLP9876DirectLabelPrint";
        return Tools::encrypt($passwd);
    }
}
