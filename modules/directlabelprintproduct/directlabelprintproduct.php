<?php
/**
 * 2016-2021 Leone MusicReader B.V.
 *
 * NOTICE OF LICENSE
 *
 * Source file is copyrighted by Leone MusicReader B.V.
 * Only licensed users may install, use and alter it.
 * Original and altered files may not be (re)distributed without permission.
 *
 * @author    Leone MusicReader B.V.
 *
 * @copyright 2016-2021 Leone MusicReader B.V.
 *
 * @license   custom see above
 */

class DirectLabelPrintProduct extends Module
{
    private $myError;
    private $mySuc;

    private $languages;

    public function __construct()
    {
        $this->name = 'directlabelprintproduct';
        $this->tab = 'shipping_logistics';
        $this->version = '3.6.3';
        /* Fix for EAN13/UPC barcodes on other printers*/
        $this->author = 'Leoné MusicReader B.V.';
        $this->module_key = 'a06117e97ebeb3c978a78e7118573972';

        $this->bootstrap=true;

        parent::__construct();

        $this->displayName = $this->l('Direct Label Print - Product / Barcode Edition');
        $this->description =
            $this->l('Add label print button on products list for names and barcodes. Works with Dymo label printers.');

        $this->prefix="dlp_pb_";

        $this->languages=Language::getLanguages(true);
    }

    public function install()
    {
        if (!parent::install()
            || !$this->registerHook('displayBackOfficeHeader')
            || !$this->registerHook('displayAdminOrder')
            || !$this->installTabs()
            || !$this->createDBTable()
        ) {
            return false;
        }

        return true;
    }

    public function installTabs()
    {
        $tab = new Tab();
        $tab->class_name = "AdminDirectLabelPrintProduct";
        $tab->active = true;
        $tab->name = array();
        foreach (Language::getLanguages() as $lang) {
            //$domain='Modules.DirectLabelPrintProduct.Admin';
            $tab->name[$lang['id_lang']] = $this->l($tab->class_name);
        }
        $tab->module=$this->name;
        $tab->id_parent = -1;

        return $tab->save();
    }

    public function createDBTable()
    {
        Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'dlpp_templates` (
				`id_dlpp_templates` varchar(40) NOT NULL UNIQUE,
				`template` MEDIUMTEXT NOT NULL,
				`template_name` VARCHAR(255) NOT NULL DEFAULT \'Default Template\',
                `template2` MEDIUMTEXT NULL DEFAULT NULL,
                `template2_name` VARCHAR(255) NULL DEFAULT NULL,
                `template3` MEDIUMTEXT NULL DEFAULT NULL,
                `template3_name` VARCHAR(255) NULL DEFAULT NULL,
			PRIMARY KEY (`id_dlpp_templates`)
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 ;');
        return true;
    }

    public function updateDBTable360(){
        Db::getInstance()->execute('
          ALTER TABLE `' . _DB_PREFIX_ . 'dlpp_templates`
            ADD `template_name` VARCHAR(255) NOT NULL DEFAULT \'Default Template\'  AFTER `template`,
            ADD `template2` MEDIUMTEXT NULL DEFAULT NULL  AFTER `template_name`,
            ADD `template2_name` VARCHAR(255) NULL DEFAULT NULL  AFTER `template2`,
            ADD `template3` MEDIUMTEXT NULL DEFAULT NULL  AFTER `template2_name`,
            ADD `template3_name` VARCHAR(255) NULL DEFAULT NULL  AFTER `template3`;');
        return true;
    }

    public function hookbackofficeheader($params)
    {
        return $this->hookDisplayBackOfficeHeader($params);
    }

    public function hookDisplayBackOfficeHeader($params)
    {
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
                    if (!Module::getInstanceByName('directlabelprint')) {
                        $c_ctrl->addJS(($this->_path) . 'views/js/dymo.connect.framework.js', 'all');
                        $c_ctrl->addJS(($this->_path) . 'views/js/dymo_fix.js', 'all');
                    }
                } else { //Generic Printer
                    $c_ctrl->addJS(($this->_path) . 'views/js/genericprintersupport.js', 'all');
                    $c_ctrl->addJS(($this->_path) . 'views/js/html2canvas.js', 'all');
                    $c_ctrl->addJS(($this->_path) . 'views/js/JsBarcode.all.min.js', 'all');
                    $c_ctrl->addJS(($this->_path) . 'views/js/qrcode.js', 'all');
                    $c_ctrl->addJS(($this->_path) . 'views/js/canvas2svg.js', 'all');
                }
                if ($printerset || Tools::isSubmit('printertype_submit')) {
                    if (strpos($_SERVER['QUERY_STRING'], "directlabelprintproduct")>-1) { //Only on configuration page
                        $c_ctrl->addJS(($this->_path) . 'views/js/summernote-lite.js', 'all');
                        $c_ctrl->addCSS(($this->_path) . 'views/css/summernote-lite.css', 'all');
                    }
                }
                if ($isModule) {
                    $c_ctrl->addCSS(($this->_path) . 'views/css/settings.css', 'all');
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
                'dymoPrinterIndex'=>$dymoPrinterIndex,
                'lang_id'=>$this->context->employee->id_lang,
                'dlpp_controller_url'=>$this->getControllerURL()
            ));

            return $this->display(__FILE__, 'views/templates/admin/header.tpl');
        } else {
            return "";
        }
    }

    public function getControllerURL()
    {
        return Context::getContext()->link->getAdminLink('AdminDirectLabelPrintProduct', true)."&ajax=true";
    }


    public function getProductInfo($product_id = null)
    {

        $languages=$this->languages;

        /*https://github.com/pal/prestashop/blob/master/classes/Product.php*/

        if ($product_id == null) {
            return array();
        } else {
            $product = new Product($product_id, true);
            if ($product->price==null) {
                $product->price=0;
            }
            $fields=$product->getFields();
            $fields["language_id"]=$this->getLanguageID();
            $fields["product_name"]=$product->name[$fields["language_id"]];
            if ($fields["product_name"]==null) {
                $fields["product_name"]=$product->name[$this->context->language->id];
            }
            foreach ($languages as $lang) {
                if (isset($lang["iso_code"]) && isset($lang["id_lang"]) && isset($product->name[$lang["id_lang"]])) {
                    $fields["product_name_" . $lang["iso_code"]] = $product->name[$lang["id_lang"]];
                }
            }
            $fields["product_name_xx"]=$this->l("Replace XX with language code.");
            $fields["description_xx"]=$this->l("Replace XX with language code.");
            $fields["description_short_xx"]=$this->l("Replace XX with language code.");

            $fields["description"]=$product->description[$fields["language_id"]];
            if ($fields["description"]==null) {
                $fields["description"]=$product->description[$this->context->language->id];
            }
            if (Tools::strlen($fields["description"])<25000) {
                $fields["description_html"] = urlencode($fields["description"]);
                $fields["description"] = htmlspecialchars(strip_tags($fields["description"]));
            } else {
                $fields["description"]=$this->l("Description too long...");
                $fields["description_html"]=$this->l("Description too long...");
            }

            foreach ($languages as $lang) {
                if (isset($lang["iso_code"]) && isset($lang["id_lang"])) {
                    $id_lang=$lang["id_lang"];
                    $iso=$lang["iso_code"];
                    if (isset($product->description[$id_lang])) {
                        $fields["description_" . $iso]=$product->description[$id_lang];
                        if (Tools::strlen($fields["description_" . $iso])<50000/count($languages)) {
                            $fields["description_".$iso."_html"] = urlencode($fields["description_" . $iso]);
                            $fields["description_".$iso] = htmlspecialchars(strip_tags($fields["description_" . $iso]));
                        } else {
                            $fields["description_".$iso]=$this->l("Description too long...");
                            $fields["description_".$iso."_html"]=$this->l("Description too long...");
                        }
                    }
                }
            }

            $fields["description_short"]=$product->description_short[$fields["language_id"]];
            if ($fields["description_short"]==null) {
                $fields["description_short"]=$product->description_short[$this->context->language->id];
            }
            $fields["description_short"]=htmlspecialchars(strip_tags($fields["description_short"]));
            foreach ($languages as $lang) {
                if (isset($lang["iso_code"]) && isset($lang["id_lang"])) {
                    if (isset($product->description_short[$lang["id_lang"]])) {
                        $value=$product->description_short[$lang["id_lang"]];
                        $fields["description_short_" . $lang["iso_code"]] = htmlspecialchars(strip_tags($value));
                    }
                }
            }

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
                $product2 = new Product($product_id, false);
                $product2->reference=$new_reference;
                $product2->save();
                $fields["reference"]=$new_reference;
            }

            if ($product->ean13) {
                $fields["ean13"] = $product->ean13;
            } elseif (Configuration::get($this->prefix.'auto_generate_ean')) {
                //Create new reference
                $new_ean=$this->getNewEAN();
                if ($new_ean!=0) {
                    $product2 = new Product($product_id, false);
                    $product2->ean13=$new_ean;
                    $product2->save();
                    $fields["ean13"]=$new_ean;
                }
            }

            if ($product->upc) {
                $fields["upc"] = $product->upc;
            } elseif (Configuration::get($this->prefix.'auto_generate_UPC')) {
                //Create new reference
                $new_upc=$this->getNewUPC();
                if ($new_upc!=0) {
                    $product2 = new Product($product_id, false);
                    $product2->upc=$new_upc;
                    $product2->save();
                    $fields["upc"]=$new_upc;
                }
            }

            $fields["supplier_reference"]="";

            //Retrieve Supplier Reference
            if (version_compare(_PS_VERSION_, "1.6.0.0") >= 0 && $product->id_supplier>0) {
                $id_s=$product->id_supplier;
                $fields["supplier_reference"]=ProductSupplier::getProductSupplierReference($product_id, 0, $id_s);
            }

            $fields["warehouse_location"]="";
            $isPS16=version_compare(_PS_VERSION_, "1.6.0.0") >= 0 && version_compare(_PS_VERSION_, "1.7.0.0") < 0;
            if ($isPS16 && Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') && isset($this->advanced_stock_management) && $this->advanced_stock_management) {
                $service_name='\\PrestaShop\\PrestaShop\\Core\\Foundation\\Database\\EntityManager';

                /* REMARK FOR REVIEW */
                $service= ServiceLocator::get($service_name);
                /* Special feature for warehouse location in Prestashop 1.6.x only.*/
                /* In 1.6.x this method does exist.*/
                /* Only way this can be achieved. */
                $rep_name='WarehouseProductLocation';
                $warehouse_product_locations = $service->getRepository($rep_name)->findByIdProduct($this->id);
                if (count($warehouse_product_locations)>0) {
                    $fields["warehouse_location"] = $warehouse_product_locations[0]->location;
                }
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
                $discount_percentage=round((100*$discount_incl_tax)/$price_incl_tax);
            } else {
                $discount_percentage=0;
            }
            $fields["discount_percentage"] = $discount_percentage."%";

            $all_features=[];
            $all_features_values_only=[];

            $features=$product->getFeatures();
            $language_id=$fields["language_id"];
            foreach ($features as $v) {
                $id_feature=$v["id_feature"];
                $id_feature_value=$v["id_feature_value"];
                $feature = new Feature($id_feature);
                $feature_name_string="feature_".str_replace(" ", "_", trim($feature->name[$language_id]));
                $feature_value=new FeatureValue($id_feature_value);
                $feature_value_string=$feature_value->value[$language_id];
                if (Tools::strlen($feature_value_string)==0) {
                    $feature_value_string=$feature_value->value[(int)Configuration::get('PS_LANG_DEFAULT')];
                }
                $fields[$feature_name_string]=$feature_value_string;
                $fields[Tools::strtolower($feature_name_string)]=$feature_value_string;

                $all_features[]=$feature->name[$language_id].": ".$feature_value_string;
                $all_features_values_only[]=$feature_value_string;
            }

            $fields["all_features"]=join(", ", $all_features);
            $fields["all_features_multiple_lines"]=join("||", $all_features);
            $fields["all_features_values_only"]=join(", ", $all_features_values_only);

            $expiration_field_name="feature_days_to_expiration";
            if (isset($fields[$expiration_field_name]) &&
                "".((int)$fields[$expiration_field_name])==$fields[$expiration_field_name]) {
                    $fields["expiration_date"]=date("Y-m-d", time()+((int)$fields[$expiration_field_name])*24*60*60);
            }

            $fields["current_date"]=date("Y-m-d", time());

            $link_rewrite=$product->link_rewrite;
            if(is_array($link_rewrite)){
                $link_rewrite=$link_rewrite[$fields["language_id"]];
            }

            $images = Product::getCover($product_id);
            if ($images['id_image'] && $images['id_image']>0) {
                $form=ImageType::getFormatedName('home');
                $image_url = $this->context->link->getImageLink($link_rewrite, $images['id_image'], $form);
                $fields["cover_image_url"] = $image_url;
            }

            $all_images = $product->getWsImages();

            for ($i=0; $i<count($all_images); $i++) {
                $form=ImageType::getFormatedName('home');
                $image_url = $this->context->link->getImageLink($link_rewrite, $all_images[$i]['id'], $form);
                $fields["image_".($i+1)."_url"] = $image_url;
            }

            if (Module::isEnabled('imaximprimepedidosservidor')) {
                $id_product = (int)$product_id;
                $id_product_attribute = (int)0;

                $sql='SELECT ubicacion FROM `'._DB_PREFIX_."imaximppedser_extraProducto` ".
                    " WHERE id_product = '$id_product' AND id_product_attribute = '$id_product_attribute'";
                $fields['ubicacion'] = Db::getInstance()->getValue($sql);
            }

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
                $fields["reference_generated"]="yes";
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
                $fields["ean13_generated"]="yes";
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
        } elseif (Configuration::get($this->prefix.'auto_generate_UPC')) {
            //Create new reference
            $new_UPC=$this->getNewUPC();
            if ($new_UPC!=0) {
                $combination->upc=$new_UPC;
                $combination->save();
                $fields["upc"]=$new_UPC;
                $fields["upc_generated"]="yes";
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
            $unit_price_incl_tax=0;
            if ($price_excl_tax_normal>0) {
                $unit_price_incl_tax = ($unit_price_excl_tax * $price_incl_tax_normal) / $price_excl_tax_normal;
            }
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
        if($price_incl_tax>0) {
            $fields["price_incl_tax"] = "" . $price_incl_tax;
            $fields["price"] = "" . $this->getPriceExclTax($product_id, $id);

            $fields["discount_price_incl_tax"] = "".$this->getDiscountPriceInclTax($product_id, $id);
        }
        $fields["all_attributes"]=htmlspecialchars($this->combinationName($product_id, $id));
        $fields["all_attributes_multiple_lines"]=str_replace(",", "||", $fields["all_attributes"]);
        $fields["all_attributes_values_only"]="";
        $comb_value=$this->combinationValues($product_id, $id);
        foreach ($comb_value as $k => $v) {
                $fields["all_attributes_values_only"] .= trim($v)." ";
                $field_name=str_replace(" ", "_", trim($k));
                $fields["attribute_".$field_name]=trim($v);
                $field_lower_case=Tools::strtolower($field_name);
                $value_field=trim($v);
                $fields["attribute_".$field_lower_case]=$value_field;
        }

        $expiration_field_name="attribute_days_to_expiration";
        if (isset($fields[$expiration_field_name])) {
            $expire=$fields[$expiration_field_name];
            if ("".((int)$expire)==$expire) {
                $fields[$fields["expiration_date"]] = date("Y-m-d", time() + ((int)$expire) * 24 * 60 * 60);
            }
        }

        $images = $combination->getWsImages();
        if (count($images)>0) {
            $form=ImageType::getFormatedName('home');
            $image_url = $this->context->link->getImageLink($product->link_rewrite, $images[0]['id'], $form);
            $fields["cover_image_url"] = $image_url;
        }

        for ($i=0; $i<count($images); $i++) {
            $form=ImageType::getFormatedName('home');
            $image_url = $this->context->link->getImageLink($product->link_rewrite, $images[$i]['id'], $form);
            $fields["image_".($i+1)."_url"] = $image_url;
        }

        if (Module::isEnabled('imaximprimepedidosservidor')) {
            $id_product = (int)$product_id;
            $id_product_attribute = (int)$id;

            $sql='SELECT ubicacion FROM `'._DB_PREFIX_."imaximppedser_extraProducto` ".
                "WHERE id_product = '$id_product' AND id_product_attribute = '$id_product_attribute'";
            $fields['ubicacion'] = Db::getInstance()->getValue($sql);
        }

        return $fields;
    }

    private $language_id_detected=null;

    public function getLanguageID()
    {
        if ($this->language_id_detected==null) {
            $orderid=(int)Tools::getValue("orderid", "0");
            $lang_id=(int)Tools::getValue("langid", "0");
            if ($orderid>0) {
                $order = new Order($orderid);
                $this->language_id_detected=$order->id_lang;
            } elseif ($lang_id>0) {
                $this->language_id_detected=$lang_id;
            } else {
                $this->language_id_detected=(int)Configuration::get('PS_LANG_DEFAULT');
            }
        }
        return $this->language_id_detected;
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

        if ($this->getUPCStartValue()==0) {
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
        if ("".$id_product!==trim($barcode)) {
            $id_product="9328747235943675361";
        }

        //CHECK Combinations
        $sql='SELECT `id_product`,`id_product_attribute` '.
            ' FROM `'._DB_PREFIX_.'product_attribute` '.
            ' WHERE `ean13` = \''.$barcode.'\' OR `upc` = \''.$barcode.'\'  OR `reference` = \''.$barcode.'\' '.
            'OR  `id_product` = \''.$id_product.'\' OR  `supplier_reference` = \''.$barcode.'\'';
        $result = Db::getInstance()->executeS($sql);
        $total = count($result);

        //print("count1:".$total);

        if ($total<1 || !isset($result[0]['id_product_attribute'])) {
            $sql='SELECT `id_product` '.
                'FROM `'._DB_PREFIX_.'product` '.
                'WHERE `ean13` = \''.$barcode.'\' OR `upc` = \''.$barcode.'\'  OR `reference` = \''.$barcode.'\' '.
                'OR  `id_product` = \''.$id_product.'\' OR  `supplier_reference` = \''.$barcode.'\'';
            //print($sql);
            $result = Db::getInstance()->executeS($sql);
            $total = count($result);
            //print("count2:".$total);
            //print_r($result);

            /*for ($i=0;$i<$total;$i++) {
                print("id:".$result[$i]['id_product']);
            }*/
        }

        if ($total<1 || (!isset($result[0]['id_product_attribute'])&&!isset($result[0]['id_product']))) {
            $sql='SELECT `id_product`,`id_product_attribute` '.
            'FROM `'._DB_PREFIX_.'product_supplier` '.
            'WHERE `product_supplier_reference` = \''.$barcode.'\' ';
            $result = Db::getInstance()->executeS($sql);
            $total = count($result);
            //print("count3:".$total);
        }
        if ($total>0 && (isset($result[0]['id_product_attribute'])||isset($result[0]['id_product']))) {
            $idpa=0;
            if (isset($result[0]['id_product_attribute'])) {
                $idpa=(int)$result[0]['id_product_attribute'];
            }
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
        if (is_array($fields) && array_key_exists("price_incl_tax", $fields)) {
            $fields["price_incl_tax"] = $this->convertDoubleToComma($fields["price_incl_tax"]);
        }
        if (is_array($fields) && array_key_exists("price", $fields)) {
            $fields["price"] = $this->convertDoubleToComma($fields["price"]);
        }
        if (is_array($fields) && array_key_exists("discount_price_incl_tax", $fields)) {
            $fields["discount_price_incl_tax"]=$this->convertDoubleToComma($fields["discount_price_incl_tax"]);
        }
        if (is_array($fields) && array_key_exists("wholesale_price", $fields)) {
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

    private function combinationValues($product_id, $id)
    {
        $langid=$this->getLanguageID();
        $product = new Product($product_id, true);
        $combinations = $product->getAttributeCombinations($langid);
        $comb_array = array();

        if (is_array($combinations)) {
            foreach ($combinations as $combination) {
                $comb_array[$combination['id_product_attribute']]['attributes'][] = array(
                    $this->getAttributeGroupPublicName($combination['id_attribute_group'], $langid),
                    $combination['attribute_name'],
                    $combination['id_attribute']
                );
            }

            foreach ($comb_array as $id_product_attribute => $product_attribute) {
                if ($id_product_attribute==$id) {
                    $comb_values=[];

                    /* In order to keep the same attributes order */
                    //asort($product_attribute['attributes']);

                    foreach ($product_attribute['attributes'] as $attribute) {
                        $comb_values[$attribute[0]]= $attribute[1];
                    }

                    return $comb_values;
                }
            }
        }
        return [];
    }

    private function combinationName($product_id, $id)
    {
        $langid=$this->getLanguageID();
        $product = new Product($product_id, true);
        $combinations = $product->getAttributeCombinations($langid);
        $comb_array = array();
        if (is_array($combinations)) {
            foreach ($combinations as $combination) {
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
                    //asort($product_attribute['attributes']);

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
        if ($extension == '.label' || $extension == '.dlabel' || $extension == '.labelx' || $extension == '.dymo') {

            $choosenTemplate=Tools::getValue('choosenTemplate');
            $choosenVariantIndex=Tools::getValue('choosenVariantIndex');
            $choosenTemplateName=Tools::getValue('choosenTemplateName');
            $templateString=Tools::file_get_contents($file_path);

            $sql="INSERT INTO `" . _DB_PREFIX_ . "dlpp_templates` (id_dlpp_templates,template,template_name) ".
                " VALUES('".$choosenTemplate."','".$templateString."','".$choosenTemplateName."') ".
                " ON DUPLICATE KEY UPDATE template='".$templateString."',template_name='".$choosenTemplateName."';";

            if($choosenVariantIndex=="2" || $choosenVariantIndex=="3"){
                $sql="UPDATE `" . _DB_PREFIX_ . "dlpp_templates` SET template".$choosenVariantIndex."='".$templateString."',template".$choosenVariantIndex."_name='".$choosenTemplateName."' WHERE id_dlpp_templates='".$choosenTemplate."';";
            }

            Db::getInstance()->execute($sql);

            $this->mySuc = $this->l('Label updated successfully.');

            return true;
        } else {
            $extensions=" .label / .dymo / .labelx / .dlabel.";
            $this->myError = $this->l('File type is invalid. Dymo templates have extension').$extensions;
        }
        if (empty($this->myError)) {
            $this->myError = $this->l('There was problem while uploading file.');
        }
        return false;
    }

    private function removeTemplate($template_id)
    {
        $sql="DELETE FROM " . _DB_PREFIX_ . "dlpp_templates WHERE id_dlpp_templates='".$template_id."';";

        Db::getInstance()->executeS($sql);
    }

    private function removeVariant($choosenVariantIndex)
    {
        $choosenTemplate=Tools::getValue('choosenTemplate');
        $sql="UPDATE `" . _DB_PREFIX_ . "dlpp_templates` SET template".$choosenVariantIndex."=NULL,template".$choosenVariantIndex."_name=NULL WHERE id_dlpp_templates='".$choosenTemplate."';";

        //die($sql);
        Db::getInstance()->execute($sql);

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

    /*
    get template from OLD storage
    */
    public function getLabelTemplate()
    {
        $label_content=urldecode(Configuration::get($this->prefix.'label_content'));
        if (!Configuration::get($this->prefix.'label_content')) {
            $label_content = '<p align="center"><b><span style="font-size: 30px;">[[product_name]]</span></b><br>'.
            '<img class="barcode" fieldname="ean13" name="[[ean13]]" src="'.($this->_path).
            'views/img/barcode.svg" style="width:381px; height:202px;"><br>'.
            '<b><span style="font-size: 16px;">[[ean13]]</span></b>'.
            '</p><p align="center"><b><span style="font-size: 24px;">&euro; [[price_incl_tax]]</span>'.
            '<span style="font-size: 18px;"><br></span></b></p>';
        }
        return $label_content;
    }

    public function displayForm()
    {
        $sampletemplateurl_label = Tools::getShopDomainSsl(true, true)
            . __PS_BASE_URI__
            . 'modules/directlabelprintproduct/MyText_sample.label';
        $sampletemplateurl_connect = Tools::getShopDomainSsl(true, true)
            . __PS_BASE_URI__
            . 'modules/directlabelprintproduct/DymoConnect_sample.dymo';

        $printertype="false"; //Generic Printer
        if (Configuration::get('label_printertype')) {
            $printertype = "true"; //Dymo
        }
        $printertypeerror=$this->l("Please select printer type and press SAVE");
        if (Configuration::get('label_printertypeset')) {
            $printertypeerror=null;
        }

        $dymosoftwaretype="false";
        if (Configuration::get('label_dymosoftwaretype')) {
            $dymosoftwaretype = "true";
        }

        $label_content="";
        $height_input="";
        $width_input="";
        $rotate_image="";

        if ($this->getPrinterTypeName()=="other") {
            //Retrieve new template
            $data=$this->getChoosenTemplateData(true);
            $label_content=urldecode($data->label_content);
            $height_input=$data->height;
            $width_input=$data->width;
            $rotate_image=$data->rotate_image;
        }

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

        $countPerProduct="false";
        if (Configuration::get($this->prefix."multipleLabelsPerProduct")) {
            $countPerProduct="true";
        }

        $countPerProduct_count=Configuration::get($this->prefix."multipleLabelsPerProduct_count");

        $variant1_status="";
        $variant2_status="";
        if(!$this->choosenTemplateHasVariant(1)){
            $variant1_status=$this->l("(CREATE VARIANT)");
        }
        if(!$this->choosenTemplateHasVariant(2)){
            $variant2_status=$this->l("(CREATE VARIANT)");
        }

        $this->smarty->assign(array(
            'sampletemplateurl_connect' => $sampletemplateurl_connect,
            'sampletemplateurl_label' => $sampletemplateurl_label,
            'formactionurl' => $_SERVER['REQUEST_URI'],
            'error' => $this->myError,
            'success' => $this->mySuc,
            'iconurl' => ($this->_path) . 'views/img/icon-print-16.png',
            'printicon' => ($this->_path) . 'views/img/icon-print.png',
            'imgfolder' => ($this->_path) . 'views/img/',
            'printertype' => $printertype,
            'printertypeerror' => $printertypeerror,
            'dymosoftwaretype' => $dymosoftwaretype,
            'width_input' => $width_input,
            'height_input' => $height_input,
            'rotate_image' => $rotate_image,
            'label_content' => $label_content,
            'barcode_sample_url' => ($this->_path) . 'views/img/barcode.svg',
            'image_sample_url' => ($this->_path) . 'views/img/image.svg',
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
            'autoGenerateUPC_EndValue' => $autoUPCEnd,
            'multipleLabelsPerProduct' => $countPerProduct,
            'multipleLabelsPerProduct_count' => $countPerProduct_count,
            'openSettingsArea'=> Tools::getValue('settingsArea'),
            'choosenTemplate' => Tools::getValue('choosenTemplate'),
            'choosenTemplateName' => Tools::getValue('choosenTemplateName'),
            'choosenVariantName' => $this->getChoosenVariantName(),
            'choosenVariantIndex' => Tools::getValue('choosenVariantIndex'),
            'statusVariant1' => $variant1_status,
            'statusVariant2' => $variant2_status
        ));

        $html=$this->display(__FILE__, 'views/templates/admin/prestui/ps-tags.tpl');
        return $html.$this->display(__FILE__, 'views/templates/admin/settings.tpl');
    }

    public function hasTemplateVariants($template_id)
    {
        $sql="SELECT template FROM " . _DB_PREFIX_ . "dlpp_templates WHERE id_dlpp_templates='".$template_id."' AND (template2 IS NOT NULL OR template3 IS NOT NULL);";

        $result = Db::getInstance()->executeS($sql);

        if ($result && count($result)>0) {
            return true;
        } else {
            return false;
        }
    }

    public function getTemplateForId($template_id,$variant=1)
    {
        $variant=(int)$variant;
        $sql="SELECT template,template2,template3 FROM " . _DB_PREFIX_ . "dlpp_templates WHERE id_dlpp_templates='".$template_id."';";
        
        $result = Db::getInstance()->executeS($sql);

        if ($result && count($result)>0) {
            if($variant==2 && $result[0]["template2"]!=null){
                return $result[0]["template2"];
            }
            if($variant==3 && $result[0]["template3"]!=null){
                return $result[0]["template3"];
            }
            return $result[0]["template"];
        } else {
            return null;
        }
    }

    public function getChoosenVariantName(){
        $choosenVariantIndex=Tools::getValue('choosenVariantIndex');
        $choosenTemplate=Tools::getValue('choosenTemplate');

        if($choosenVariantIndex!="2" && $choosenVariantIndex!="3"){
            $choosenVariantIndex="";
        }

        $field="template".$choosenVariantIndex."_name";

        $sql="SELECT ".$field." FROM " . _DB_PREFIX_ . "dlpp_templates WHERE id_dlpp_templates='".$choosenTemplate."';";

        $result = Db::getInstance()->executeS($sql);
        $returndata="";

        if ($result && count($result)>0) {
            $returndata=$result[0][$field];
            if($returndata==null || strlen(trim($returndata))==0){
                $returndata = "Template Variant ".$choosenVariantIndex;
            }
            return $returndata;
        } else {
            return "Template Variant ".$choosenVariantIndex;
        }
    }

    public function choosenTemplateHasVariant($variantIndex){
        $choosenTemplate=Tools::getValue('choosenTemplate');
        $template_field="template".($variantIndex+1);

        $sql="SELECT ".$template_field." FROM " . _DB_PREFIX_ . "dlpp_templates WHERE id_dlpp_templates='".$choosenTemplate."';";

        $result = Db::getInstance()->executeS($sql);

        if ($result && count($result)>0) {
            $returndata = $result[0][$template_field];
            return ($returndata != null && strlen(trim($returndata)) > 0);
        }
        return false;
    }

    public function getChoosenTemplateData($parseJson)
    {
        $choosenTemplate=Tools::getValue('choosenTemplate');
        $choosenVariantIndex=Tools::getValue('choosenVariantIndex');

        if($choosenVariantIndex!="2" && $choosenVariantIndex!="3"){
            $choosenVariantIndex="";
        }

        $printertype=$this->getPrinterTypeName();

        $template_field="template".$choosenVariantIndex;

        $sql="SELECT ".$template_field.",template FROM " . _DB_PREFIX_ . "dlpp_templates WHERE id_dlpp_templates='".$choosenTemplate."';";

        $result = Db::getInstance()->executeS($sql);

        $returndata="";

        if ($result && count($result)>0) {
            $returndata=$result[0][$template_field];
            if($returndata==null || strlen(trim($returndata))==0){
                $returndata=$result[0]["template"];
            }
        } else {
            //GET DEFAULT FROM PRINTER TYPE
            $default_id="DEFAULT"."-".$printertype;
            if ($choosenTemplate!=$default_id) {
                $sql="SELECT ".$template_field.",template FROM "._DB_PREFIX_."dlpp_templates WHERE id_dlpp_templates='".$default_id."';";

                $result2 = Db::getInstance()->executeS($sql);
                if ($result2 && count($result2)>0) {
                    $returndata=$result2[0][$template_field];
                    if($returndata==null || strlen(trim($returndata))==0){
                        $returndata=$result2[0]["template"];
                    }
                }
            }
            //DEFAULT FROM OLDER PRINTER
            if (Tools::strlen($returndata)==0) {
                //RETURN DEFAULT OF OLD RELEASE
                $returndata=$this->getTemplateOldStorage($printertype);
            }
        }

        if (Tools::strlen($returndata)==0) {
            return null;
        }

        if ($parseJson) {
            $returndata=json_decode($returndata, false);
        }

        return $returndata;
    }

    public function getTemplateOldStorage($printertype)
    {
        $returndata="";
        if ($printertype=="dymo") {
            $returndata=Tools::file_get_contents(dirname(__FILE__)."/MyText.label");
        } else {
            $data=[];
            $data["width"]="100";
            if (Configuration::get($this->prefix.'width_input')) {
                $data["width"]=Configuration::get($this->prefix.'width_input');
            }
            $data["height"]="50";
            if (Configuration::get($this->prefix.'height_input')) {
                $data["height"]=Configuration::get($this->prefix.'height_input');
            }
            $data["rotate_image"]="false"; //Generic Printer
            if (Configuration::get($this->prefix.'rotate_image')) {
                $data["rotate_image"] = "true"; //Dymo
            }
            $data["label_content"]=urlencode($this->getLabelTemplate());
            $returndata=json_encode($data);
        }
        return $returndata;
    }

    public function getPrinterTypeName()
    {
        $type_printer="other";
        if (Configuration::get('label_printertype')) {
            $type_printer="dymo";
        }
        return $type_printer;
    }

    public function getContent()
    {
        if (Tools::isSubmit('upload')) {
            $this->uploadlabel() ;
        }
        if (Tools::isSubmit('printertype_submit')) {
            Configuration::updateValue('label_printertype', Tools::getValue('printertype'));
            Configuration::updateValue('label_printertypeset', "set");
            Configuration::updateValue('label_dymosoftwaretype', Tools::getValue('dymosoftwaretype'));
            $this->mySuc=$this->l("Printer settings updated successfully.");
        }
        if (Tools::getValue('removeTemplateAction')=="yes") {
            $this->removeTemplate(Tools::getValue('removeTemplate'));
            $this->mySuc=$this->l("Template removed successfully.");
        }else if(Tools::getValue('removeVariantAction')=="yes"){
            $this->removeVariant(Tools::getValue('removeVariantIndex'));
            $this->mySuc=$this->l("Variant removed successfully.");
        }
        if (Tools::isSubmit('generic_label_submit')) {
            $choosenTemplate=Tools::getValue('choosenTemplate');

            $choosenVariantIndex=Tools::getValue('choosenVariantIndex');
            $choosenVariantName=Tools::getValue('choosenVariantName');

            $data=[];
            $data["width"]=Tools::getValue('width_input');
            $data["height"]=Tools::getValue('height_input');
            if (Tools::getValue('rotate_image')=="1") {
                $data["rotate_image"] = "true";
            } else {
                $data["rotate_image"]="false";
            }
            $data["label_content"]=urlencode(Tools::getValue('label_content'));

            $templateString=json_encode($data);


            $sql="INSERT INTO `" . _DB_PREFIX_ . "dlpp_templates` (id_dlpp_templates,template,template_name) ".
                " VALUES('".$choosenTemplate."','".$templateString."','".$choosenVariantName."') ".
                " ON DUPLICATE KEY UPDATE template='".$templateString."',template_name='".$choosenVariantName."';";

            if($choosenVariantIndex=="2" || $choosenVariantIndex=="3"){
                $sql="UPDATE `" . _DB_PREFIX_ . "dlpp_templates` SET template".$choosenVariantIndex."='".$templateString."',template".$choosenVariantIndex."_name='".$choosenVariantName."' WHERE id_dlpp_templates='".$choosenTemplate."';";
            }

            //die($sql);
            Db::getInstance()->execute($sql);

            /*Configuration::updateValue($this->prefix.'width_input', Tools::getValue('width_input'));
            Configuration::updateValue($this->prefix.'height_input', Tools::getValue('height_input'));
            Configuration::updateValue($this->prefix.'rotate_image', Tools::getValue('rotate_image'));
            Configuration::updateValue($this->prefix.'label_content', urlencode(Tools::getValue('label_content')));*/
            $this->mySuc=$this->l("Template saved successfully.");
        }
        if (Tools::isSubmit('dymoSettings')) {
            Configuration::updateValue('dymoPrinterIndex_dlpp', Tools::getValue('dymoPrinterIndex'));
            Configuration::updateValue('selectedDymoIndex_dlpp', Tools::getValue('selectedDymoIndex')); //SDI
            $this->mySuc=$this->l("Printer settings updated successfully.");
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


            $countPerProduct=trim(Tools::getValue('multipleLabelsPerProduct'));
            Configuration::updateValue($this->prefix."multipleLabelsPerProduct", $countPerProduct);

            $countPerProduct_count=trim(Tools::getValue('multipleLabelsPerProduct_count'));
            Configuration::updateValue($this->prefix."multipleLabelsPerProduct_count", $countPerProduct_count);

            $this->mySuc=$this->l("Settings updated successfully.");
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

    public function displayLabelLink($token, $id, $name)
    {
        $combination= Tools::strlen(Tools::getValue("id_product"))>0;
        $product_info=$token.$name;

        if (!$combination) {
            $product_info = $this->getProductInfo($id);
        } else {
            $pid=(int)Tools::getValue("id_product");
            $product_info = $this->getProductCombinationInfo($pid, $id);
        }
        $product_info=$this->convertDoublePricing($product_info);

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

        $product_info_json = Tools::jsonEncode($product_info);



        $tpl_file=dirname(__FILE__)."/views/templates/admin/list_action_label.tpl";
        $tpl =  $this->context->smarty->createTemplate($tpl_file);

        $tpl->assign(array(
            'href' => "javascript:void(0);",
            'js' => "window.scrollTo(0, 0);var product_label_template = getProductLabelTemplateURL(".$id.");".
                "printProductLabel(product_label_template,".$product_info_json.");",
            'js_data' => "product_label_template,".$product_info_json,
            'action' => $this->l('Label', 'Helper'),
            'id' => $id
        ));

        return $tpl->fetch();
    }

    public function upgradeOverride()
    {
        $override_path=_PS_OVERRIDE_DIR_."controllers/admin/AdminProductsController.php";

        if (file_exists($override_path)) {
            $replace="\$combination= Tools::strlen(Tools::getValue(\"id_product\"))>0;";
            $replaceby="return Module::getInstanceByName('directlabelprintproduct')->displayLabelLink(\$token, \$id, \$name);";
            $file_contents=Tools::file_get_contents($override_path);
            $file_contents=str_replace($replace, $replaceby, $file_contents);
            file_put_contents($override_path, $file_contents);
            return true;
        } else {
            return true;
        }
    }
}
