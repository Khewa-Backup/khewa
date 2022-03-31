<?php
/**
* NOTICE OF LICENSE
*
* This file is part of the 'WK Inventory' module feature.
* Developped by Khoufi Wissem (2017).
* You are not allowed to use it on several site
* You are not allowed to sell or redistribute this module
* This header must not be removed
*
*  @author    KHOUFI Wissem - K.W
*  @copyright Khoufi Wissem
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class AdminBarcodesgenController extends ModuleAdminController
{
    public function __construct()
    {
        require_once(dirname(__FILE__).'/../../classes/Workshop.php');

        $this->bootstrap = true;
        $this->display = 'view';
        $this->toolbar_title = $this->l('EAN / UPC Barcode Generator');
        parent::__construct();

        $this->tmp_storage_file = _PS_MODULE_DIR_.$this->module->name.'/tmp-storage-data.json';
    }

    /*
     * Render general view
    */
    public function renderView()
    {
        $id_lang = (int)$this->context->language->id;

        $this->tpl_view_vars = array(
            'suppliers' => Supplier::getSuppliers(false, (int)$id_lang),
            'manufacturers' => Manufacturer::getManufacturers(),
            'this_path' => _MODULE_DIR_.$this->module->name,
            'is_before_16' => $this->module->is_before_16,
            'module_name' => $this->module->displayName,
            'isModuleFolderWritable' => is_writable(dirname(__FILE__).'/../../../'.$this->module->name.'/'),
            //'token_security' => WorkshopStock::getToken()
        );
        if ($this->module->is_before_16) {
            $this->tpl_view_vars['title_page'] = $this->toolbar_title;
        }
        $this->base_tpl_view = 'view.tpl';

        return parent::renderView();
    }

    public function getDataFromPreviousIteration($key = false)
    {
        $data = array();
        $stored_data = Tools::jsonDecode(Tools::file_get_contents($this->tmp_storage_file), true);
        if (!empty($stored_data) && !empty($stored_data['identifier'][0]) && ($stored_data['identifier'][0] == $this->identifier || $key == 'identifier')) {
            $data = $stored_data;
            if ($key) {
                $data = isset($stored_data[$key]) ? $stored_data[$key] : '';
            }
        } else {
            $this->clearTmpStorage();
        }
        return $data;
    }

    public function clearTmpStorage()
    {
        if (file_exists($this->tmp_storage_file)) {
            unlink($this->tmp_storage_file);
        }
    }

    public function checkIdentifier()
    {
        $this->identifier = Tools::getValue('identifier');
        $saved_identifier = $this->getDataFromPreviousIteration('identifier');
        if ($saved_identifier && $this->identifier != $saved_identifier[0] && microtime(true) - $saved_identifier[1] < 120) {
            $this->throwError($this->l('Please wait, somebody else is currently generating codes'), false);
        }
        if (!$this->identifier) {
            $this->identifier = microtime(true);
        }
        $this->saveDataForNextIteration(array('identifier' => array($this->identifier, microtime(true))));
    }

    public function saveDataForNextIteration($data)
    {
        $data_to_save = $this->getDataFromPreviousIteration();
        foreach ($data as $k => $d) {
            $data_to_save[$k] = $d;
        }
        file_put_contents($this->tmp_storage_file, Tools::jsonEncode($data_to_save));
    }

    public function ajaxProcessProcessEanUpcGeneration()
    {
        $prefix = configuration::get('WKINVENTORY_PREFIX_CODE');
        $isAllowed = true;
        $error = '';

        if (empty($prefix) || (!configuration::get('WKINVENTORY_GEN_EAN') && !configuration::get('WKINVENTORY_GEN_UPC'))) {
            $isAllowed = false;
            if (empty($prefix)) {
                $error = $this->l('The prefix is required for codes generation (See config. page).');
            } else {
                $error = $this->l('Generation options for EAN13 and UPC codes are disabled, you must enable at least one option from the configuration page to continue.');
            }
        }
        if (!$isAllowed) {
            die(Tools::jsonEncode(array(
                'responseText' => $error,
                'isAllowed' => $isAllowed,
            )));
        }
        // For Ajax Handler
        $this->checkIdentifier();
        // For Ajax Handler
        $data_from_previous_iteration = $data_for_next_iteration = $this->getDataFromPreviousIteration();

        // For Ajax Handler
        $ret = array (
            'hasError' => false,
            'isAllowed' => $isAllowed,
            'identifier' => $this->identifier,
            'data_from_previous_iteration' => $data_from_previous_iteration,
        );

        $force_generation = false;
        if (Tools::getIsset('task') && Tools::getValue('task') == 'genCodeForceProducts') {
            $force_generation = true;
        }

        // For Ajax Handler
        if (!isset($data_from_previous_iteration['product_ids'])) {
            // prepare Filters
            $brands_ids = Tools::getValue('brands_ids');
            $suppliers_ids = Tools::getValue('suppliers_ids');
            $where = array();

            // prepare products
            $query = new DbQuery();
            $query->select('DISTINCT p.`id_product`')
            ->from('product', 'p');

            if (!$force_generation) {// Get only products with empty EAN13, UPC
                $query->leftJoin('product_attribute', 'pa', 'p.`id_product` = pa.`id_product`');
                if (Configuration::get('WKINVENTORY_GEN_EAN')) {
                    $where[] = '(((p.ean13 IS NULL OR p.ean13 = \'\') AND pa.id_product_attribute IS NULL) OR ((pa.ean13 IS NULL OR pa.ean13 = \'\') AND pa.id_product_attribute IS NOT NULL))';
                }
                if (Configuration::get('WKINVENTORY_GEN_UPC')) {
                    $where[] = '(((p.upc IS NULL OR p.upc = \'\') AND pa.id_product_attribute IS NULL) OR ((pa.upc IS NULL OR pa.upc = \'\') AND pa.id_product_attribute IS NOT NULL))';
                }
                $query->where(implode(' OR ', $where));
            }
            // Search by brands
            if (!empty($brands_ids)) {
                $query->where('p.`id_manufacturer` IN ('.pSQL($brands_ids).')');
            }
            // Search by suppliers
            if (!empty($suppliers_ids)) {
                $query->innerJoin(
                    'product_supplier',
                    'ps',
                    'ps.`id_product` = p.`id_product` AND ps.id_supplier IN ('.pSQL($suppliers_ids).')'
                );
            }
            //echo $query->build();
            //exit();
            // Array map get the current element (ID) for each product array
            $product_ids = array_map('current', Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($query));
            if (!$product_ids) {
                $ret['complete'] = true;
                $ret['responseText'] = '0/0; '.$this->l('End process').'.';
            }

            $data_for_next_iteration = array(
                'total' => count($product_ids),
                'product_ids' => $product_ids,
                'processed_product_ids' => array(),
            );
            // Save data (products id) for the next iterations
            $this->saveDataForNextIteration($data_for_next_iteration);
            $ret['complete'] = false;
            $ret['responseText'] = $this->getResponseText(0, count($product_ids));
            die(Tools::jsonEncode($ret));
        }

        // For Ajax Handler
        $product_ids = $data_from_previous_iteration['product_ids'];
        if (!empty($data_from_previous_iteration['processed_product_ids'])) {
            $data_for_next_iteration['processed_product_ids'] = array();
        }

        foreach ($product_ids as $k => $id) {
            $product = new Product($id, false);
            if (!Validate::isLoadedObject($product)) {
                continue;
            }
            // Update EAN13/UPC for the current product
            $completed = $this->module->saveBarcodes($product, $force_generation);

            if ($completed) {
                $this->clearTmpStorage();
                $data_for_next_iteration['processed_product_ids'][] = $id;
                unset($product_ids[$k]);

                // For Ajax Handler
                $data_for_next_iteration['product_ids'] = $product_ids;
                $this->saveDataForNextIteration($data_for_next_iteration);

                // For Ajax Handler
                $total = $data_from_previous_iteration['total'];
                $ret['complete'] = false;
                $ret['responseText'] = $this->getResponseText($total - count($product_ids), $total);
                die(Tools::jsonEncode($ret));
            } else {
                break;
            }
        }

        // For Ajax Handler
        // Ajax Process Completed
        if (!$product_ids && !$data_for_next_iteration['processed_product_ids']) {
            $this->clearTmpStorage();
            $ret['complete'] = true;
            $ret['responseText'] = $this->l('The generation process is finished').'.';
            die(Tools::jsonEncode($ret));
        }
    }

    public function getResponseText($processed, $total)
    {
        $ret = sprintf($this->l('Products processed: %d/%d'), (int)$processed, (int)$total);
        return $ret;
    }

    public function throwError($error_text, $clear_tmp_storage = true)
    {
        $ret = array (
            'hasError' => true,
            'responseText' => $error_text
        );
        if ($clear_tmp_storage) {
            $this->clearTmpStorage();
        }
        die(Tools::jsonEncode($ret));
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->addJqueryUI('ui.dialog');
        $this->addJqueryPlugin('chosen');
    }

    public function initToolbar()
    {
        parent::initToolbar();
        $this->toolbar_btn['back'] = array(
            'href' => $this->context->link->getAdminLink('AdminStocktakedash'),
            'desc' => $this->l('Dashboard')
        );
    }

    public function initPageHeaderToolbar()
    {
        $this->page_header_toolbar_btn['back_to_dashboard'] = array(
            'href' => $this->context->link->getAdminLink('AdminStocktakedash'),
            'desc' => $this->l('Dashboard', null, null, false),
            'icon' => 'process-icon-back'
        );
        parent::initPageHeaderToolbar();
    }

    /*
    * Method Translation Override For PS 1.7
    */
    protected function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        if (method_exists('Context', 'getTranslator')) {
            $this->translator = Context::getContext()->getTranslator();
            $translated = $this->translator->trans($string);
            if ($translated !== $string) {
                return $translated;
            }
        }
        if ($class === null || $class == 'AdminTab') {
            $class = Tools::substr(get_class($this), 0, -10);
        } elseif (Tools::strtolower(Tools::substr($class, -10)) == 'controller') {
            $class = Tools::substr($class, 0, -10);
        }
        return Translate::getAdminTranslation($string, $class, $addslashes, $htmlentities);
    }
}
