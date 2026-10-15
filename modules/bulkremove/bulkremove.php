<?php

if (!defined('_PS_VERSION_')) {
    exit;
}
 
 
class bulkremove extends Module
{
 

    public function __construct()
    {
        $this->name = "bulkremove";
        $this->tab = 'administration';
        $this->version = 1.0;
        $this->author = 'tom';
        $this->need_instance = 0;
        
        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true; 
         
       
        $this->displayName = $this->l('bulkremove');
        $this->description = $this->l('bulkremove');

        $this->confirmUninstall = $this->l('Uninstall the module?'); 
         parent::__construct();
     }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    
    
    public function install()
    { 
        return (parent::install() && $this->registerHook('displayBackOfficeHeader')
               && $this->registerHook('displayHeader')
               );  
    }
     
     public function hookDisplayHeader()
     {


         $controller = Tools::getValue('controller');

       //  if($controller =='order'){
             $sql = "SELECT * FROM `"._DB_PREFIX_."product_lang` WHERE `description_short` LIKE '%South West%' OR `description_short` LIKE '%Southwest%'";
//         $sql = "SELECT * FROM `"._DB_PREFIX_."product_lang` WHERE `description_short` LIKE '%120 sheets%' OR `description_short` LIKE '%Southwest%'";
             $products = DB::getInstance()->executeS($sql);

//var_dump($products);die();


             $product_arr = array();
             foreach($products as $product){
                 $sql = 'SELECT * FROM `'._DB_PREFIX_.'stock_available` WHERE `quantity` = 0 AND id_product= '. $product['id_product'];
                 $id_product =  DB::getInstance()->executeS($sql);

                 if(count($id_product)>0){
                     $product_arr[] = $id_product[0];
                 }
             }
             foreach($product_arr as $product){
                 Db::getInstance()->update(
                     'product_shop',
                     array(
                         'active' => 0,
                     ),
                     'id_product ='.(int) $product['id_product']
                 );
             }
       //  }

     }

    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */



        if (((bool) Tools::isSubmit('submit4')) == true) {
            $category_id_availability = Tools::getValue('category_id_availability');
            $product_avail = Tools::getValue('product_avail');

 
            $id_category = $category_id_availability;
            $sql = 'SELECT * FROM `'._DB_PREFIX_.'category_product` WHERE id_category = '.$id_category;
            $products = DB::getInstance()->executeS($sql);
            foreach ($products as $item) {
                $id_product = $item['id_product'];

                    Db::getInstance()->update(
                        'stock_available',
                        array(
                            'out_of_stock' =>$product_avail,
                        ),
                        'id_product ='.(int) $id_product
                    );

            }




        }





        if (((bool) Tools::isSubmit('submit3')) == true) {
            $carriers = Tools::getValue('carrier_box');
            $category_id_carrier = Tools::getValue('category_id_carrier');


            $sql = 'SELECT * FROM `'._DB_PREFIX_.'category_product` WHERE id_category = '.$category_id_carrier;

            $products = DB::getInstance()->executeS($sql);

            $id_shop = (int)Context::getContext()->shop->id;



            foreach ($products as $product) {
                $id_product = $product['id_product'];
                Db::getInstance()->delete(
                    'product_carrier',
                    'id_product ='.(int) $id_product
                );
            }

            foreach ($products as $product) {
                $id_product = $product['id_product'];

                foreach ($carriers as $carrier_id) {


                    Db::getInstance()->insert(
                        'product_carrier',
                        array(
                            'id_shop' => (int) $id_shop,
                            'id_product' => $id_product,
                            'id_carrier_reference' => $carrier_id,
                        )
                    );
                }
            }
        }
        if (((bool) Tools::isSubmit('submit2')) == true) {
            $category_id_barcode = Tools::getValue('category_id_barcode');


            $id_category = $category_id_barcode;
            $sql = 'SELECT * FROM `'._DB_PREFIX_.'category_product` WHERE id_category = '.$id_category;
            $products = DB::getInstance()->executeS($sql);

            foreach ($products as $item) {
                $id_product = $item['id_product'];
                Db::getInstance()->update(
                    'product',
                    array(
                        'upc' => '',
                    ),
                    'id_product ='.(int) $id_product
                );

                $sql = 'SELECT * FROM `'._DB_PREFIX_.'product_attribute` WHERE id_product = '.$id_product;
                $product_attributes = DB::getInstance()->executeS($sql);

                foreach ($product_attributes as $item) {
                    $id_product_attribute = $item['id_product_attribute'];
                    Db::getInstance()->update(
                        'product_attribute',
                        array(
                            'upc' => '',
                        ),
                        'id_product_attribute ='.(int) $id_product_attribute
                    );
                }

            }
        }

        if (((bool) Tools::isSubmit('submit1')) == true) {
            $category_id_for_weights = Tools::getValue('category_id_for_weights');
            $weight = Tools::getValue('category_id_weight');
            $depth = Tools::getValue('category_id_depth');
            $width = Tools::getValue('category_id_width');
            $height =Tools::getValue('category_id_height');

            $id_category = $category_id_for_weights;

            $sql = 'SELECT * FROM `'._DB_PREFIX_.'category_product` WHERE id_category = '.$id_category;
            $products = DB::getInstance()->executeS($sql);

            foreach ($products as $item) {
                $id_product = $item['id_product'];
                Db::getInstance()->update(
                    'product',
                    array(
                        'weight' => $weight,
                        'depth' => $depth,
                        'width' => $width,
                        'height' => $height,
                    ),
                    'id_product ='.(int) $id_product
                );
            }
        }

        return  $this->renderForm();
    }
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitsmartdhvcformModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => array(
                //  'smartdhvcform_LIVE_MODE' => Configuration::get('smartdhvcform_LIVE_MODE', true),
                //default value for general settings
                'category_id_weight' => Configuration::get('category_id_weight'),
                'category_id_depth' => Configuration::get('category_id_depth'),
                'category_id_width' => Configuration::get('category_id_width'),
                'category_id_height' => Configuration::get('category_id_height'),
                'category_id_barcode' => Configuration::get('category_id_barcode'),
                'category_id_carrier' => Configuration::get('category_id_carrier'),
                'category_id_availability' => Configuration::get('category_id_availability'),
                'product_avail' => Configuration::get('product_avail'),
                'category_id_for_weights' => Configuration::get('category_id_for_weights'),
            ), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm(), $this->getConfigForm2(), $this->getConfigForm3(), $this->getConfigForm4()));
    }

    protected function getConfigForm2()
    {


        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Remove Barcodes'),
                    'icon' => '',
                ),
                'input' => array(
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l(''),
                        'name' => 'category_id_barcode',
                        'label' => $this->l('Enter your Category id'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'submit2',
                ),
            ),
        );
    }
    protected function getConfigForm3()
    {


//        $id_lang = (int)Context::getContext()->language->id;
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'carrier` as c,`'._DB_PREFIX_.'carrier_lang` as cl WHERE cl.id_carrier = c.id_carrier  AND active = 1 AND deleted = 0';
        $carriers = DB::getInstance()->executeS($sql);

        $query = array();

$exist = array();
        $count = 0;
        foreach($carriers as $carrier){
 
            if (!in_array($carrier['id_carrier'], $exist))
              {
                        $query[$count]['id'] = $carrier['id_carrier'];
                        $query[$count]['name'] = $carrier['name'];
                        $query[$count]['val'] = $carrier['id_reference'];
              }
            
            
            $exist[] = $carrier['id_carrier'];
            $count++;
        }
//        var_dump($query);
//        die();

        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Add carriers'),
                    'icon' => '',
                ),
                'input' => array(
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l(''),
                        'name' => 'category_id_carrier',
                        'label' => $this->l('Enter your Category id'),
                    ),
                    array(
                        'type' => 'checkbox',
                        'name' => 'carrier_box[]',
                        'values' => array(
                            'query' => $query,
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                ),

                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'submit3',
                ),
            ),
        );
    }
    protected function getConfigForm4()
    {



//        var_dump($query);
//        die();

        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Set Availability'),
                    'icon' => '',
                ),
                'input' => array(
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l(''),
                        'name' => 'category_id_availability',
                        'label' => $this->l('Enter your Category id'),
                    ),
                    array(
                        'type' => 'radio',
                        'label' => $this->l('Availability'),
                        'name' => 'product_avail',
                        'class' => 't',
                        'required'  => true,
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'deny',
                                'value' => 0,
                                'label' => $this->l('Deny orders')
                            ),
                            array(
                                'id' => 'allow',
                                'value' => 1,
                                'label' => $this->l('Allow orders')
                            ),
                            array(
                                'id' => 'default',
                                'value' => 2,
                                'label' => $this->l('Use default behavior')
                            )
                        ),
                    ),
                ),

                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'submit4',
                ),
            ),
        );
    }
    protected function getConfigForm()
    {

        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Set Shipping Inputs'),
                    'icon' => '',
                ),
                'input' => array(
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l(''),
                        'name' => 'category_id_for_weights',
                        'label' => $this->l('Enter your Category id'),
                    ),array(
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l(''),
                        'name' => 'category_id_width',
                        'label' => $this->l('Enter width'),
                    ),array(
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l(''),
                        'name' => 'category_id_height',
                        'label' => $this->l('Enter height'),
                    ),array(
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l(''),
                        'name' => 'category_id_depth',
                        'label' => $this->l('Enter depth'),
                    ),array(
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l(''),
                        'name' => 'category_id_weight',
                        'label' => $this->l('Enter weight'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'submit1',
                ),
            ),
        );
    }
    /**
     * Load the configuration form
     */
    

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
     

    public function hookBackOfficeHeader()
    {
        $controller = Tools::getValue('controller');
        if($controller == 'AdminProducts'){
            $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
            $link_array = explode('products/',$actual_link);
            $link_array = explode('/edit',$link_array[1]);
            $id_product = (int)$link_array[0];


            $sql = 'SELECT * FROM `'._DB_PREFIX_.'product_lang` WHERE id_product = '.$id_product.' AND id_lang = '.$this->context->language->id;
            $products = DB::getInstance()->executeS($sql);
            $product_name = $products[0]['name'];

                if(strpos($product_name, "copy of") !== false){
                    Db::getInstance()->update(
                        'product_attribute',
                        array(
                            'upc' => '',
                            'isbn' => '',
                        ),
                        'id_product ='.(int) $id_product
                    );
                } else{
                    //do nothing
                }
        }
        return '';
    }

   
    
 

   
  
} 

