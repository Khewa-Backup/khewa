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
               && $this->registerHook('actionCronJob') // Khewa PS 9.2: was displayHeader
               );  
    }
     
    // Khewa PS 9.2: the "South West" rule used to run on every shop page view (hookDisplayHeader).
    // It now runs once an hour from the Cron tasks module (cronjobs) through actionCronJob.
    public function hookActionCronJob()
    {
        $this->deactivateSouthWestOutOfStock();
    }

    // Khewa PS 9.2: tells the cronjobs module to run hookActionCronJob every hour
    public function getCronFrequency()
    {
        return array(
            'hour' => -1,
            'day' => -1,
            'month' => -1,
            'day_of_week' => -1,
        );
    }

    // Switches off products whose short description says "South West"/"Southwest" and that have a stock row at 0
    // (same rule as before, done in one query instead of one query per product)
    public function deactivateSouthWestOutOfStock()
    {
        return Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . 'product_shop` ps SET ps.`active` = 0
             WHERE ps.`active` = 1
             AND ps.`id_product` IN (
                 SELECT pl.`id_product` FROM `' . _DB_PREFIX_ . 'product_lang` pl
                 WHERE pl.`description_short` LIKE \'%South West%\' OR pl.`description_short` LIKE \'%Southwest%\'
             )
             AND ps.`id_product` IN (
                 SELECT sa.`id_product` FROM `' . _DB_PREFIX_ . 'stock_available` sa WHERE sa.`quantity` = 0
             )'
        );
    }

    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */



        if (((bool) Tools::isSubmit('submit4')) == true) {
            $category_id_availability = Tools::getValue('category_id_availability');
            $product_avail = Tools::getValue('product_avail');

 
            $id_category = (int) $category_id_availability; // Khewa PS 9.2: number only
            $sql = 'SELECT * FROM `'._DB_PREFIX_.'category_product` WHERE id_category = '.$id_category;
            $products = DB::getInstance()->executeS($sql);
            foreach ($products as $item) {
                $id_product = $item['id_product'];

                    Db::getInstance()->update(
                        'stock_available',
                        array(
                            'out_of_stock' => (int) $product_avail,
                        ),
                        'id_product ='.(int) $id_product
                    );

            }




        }





        if (((bool) Tools::isSubmit('submit3')) == true) {
            $carriers = Tools::getValue('carrier_box');
            $category_id_carrier = Tools::getValue('category_id_carrier');


            $sql = 'SELECT * FROM `'._DB_PREFIX_.'category_product` WHERE id_category = '.(int) $category_id_carrier; // Khewa PS 9.2: number only

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

                foreach ((array) $carriers as $carrier_id) { // Khewa PS 9.2: no warning when none ticked


                    Db::getInstance()->insert(
                        'product_carrier',
                        array(
                            'id_shop' => (int) $id_shop,
                            'id_product' => (int) $id_product,
                            'id_carrier_reference' => (int) $carrier_id,
                        )
                    );
                }
            }
        }
        if (((bool) Tools::isSubmit('submit2')) == true) {
            $category_id_barcode = Tools::getValue('category_id_barcode');


            $id_category = (int) $category_id_barcode; // Khewa PS 9.2: number only
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

                $sql = 'SELECT * FROM `'._DB_PREFIX_.'product_attribute` WHERE id_product = '.(int) $id_product;
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

            $id_category = (int) $category_id_for_weights; // Khewa PS 9.2: number only

            $sql = 'SELECT * FROM `'._DB_PREFIX_.'category_product` WHERE id_category = '.$id_category;
            $products = DB::getInstance()->executeS($sql);

            foreach ($products as $item) {
                $id_product = $item['id_product'];
                Db::getInstance()->update(
                    'product',
                    array(
                        'weight' => (float) $weight,
                        'depth' => (float) $depth,
                        'width' => (float) $width,
                        'height' => (float) $height,
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
     

    // Khewa PS 9.2: the registered hook is displayBackOfficeHeader, and 9.2's product page has no ?controller= in the URL,
    // so the old code never ran. Same rule: opening a product named "copy of ..." clears UPC and ISBN on its combinations.
    public function hookDisplayBackOfficeHeader()
    {
        $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
        if (!preg_match('#/sell/catalog/products(?:-v2)?/(\d+)/edit#', $uri, $m)
            && !(Tools::getValue('controller') == 'AdminProducts' && ($m = array(1 => (int) Tools::getValue('id_product'))) && $m[1])) {
            return '';
        }
        $id_product = (int) $m[1];
        $product_name = (string) Db::getInstance()->getValue(
            'SELECT `name` FROM `' . _DB_PREFIX_ . 'product_lang` WHERE id_product = ' . $id_product . ' AND id_lang = ' . (int) $this->context->language->id
        );
        if ($product_name !== '' && stripos($product_name, 'copy of') !== false) {
            Db::getInstance()->update(
                'product_attribute',
                array(
                    'upc' => '',
                    'isbn' => '',
                ),
                'id_product =' . $id_product
            );
        }
        return '';
    }

    public function hookBackOfficeHeader()
    {
        return $this->hookDisplayBackOfficeHeader();
    }

   
    
 

   
  
} 

