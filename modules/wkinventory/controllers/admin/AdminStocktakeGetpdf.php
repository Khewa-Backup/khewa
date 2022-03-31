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
 
if (!class_exists('mPDF')) {
    include_once(_PS_ROOT_DIR_.'/modules/wkinventory/libraries/mpdf_lib/mpdf.php');
}
if (!class_exists('fpdi_pdf_parser')) {
    include_once(_PS_ROOT_DIR_.'/modules/wkinventory/libraries/mpdf_lib/vendor/setasign/fpdi/fpdi_pdf_parser.php');
}

class AdminStocktakegetpdfController extends ModuleAdminController
{
    private $template_dir;
    private $pdf_list_dir;
    private $products2page;

    public function __construct()
    {
        include dirname(__FILE__).'/../../classes/StockTake.php';
        include dirname(__FILE__).'/../../classes/StockTakeProduct.php';
        include dirname(__FILE__).'/../../classes/StockTakeLog.php';
        include dirname(__FILE__).'/../../classes/Workshop.php';

        $this->bootstrap = true;
        $this->display = 'view';
        $this->toolbar_title = $this->l('PDF Report Generation');
        parent::__construct();

        $this->initParams();
    }

    private function initParams()
    {
        $template_name = 'Blue_lines';
        $module_dir = _PS_ROOT_DIR_.'/modules/'.$this->module->name.'/';

        $this->pdf_list_dir = $module_dir.'pdf_list/';
        $this->template_dir = $module_dir.'libraries/templates/'.$template_name.'/';
        $this->products2page = 10;
    }

    public function renderView()
    {
        if (Configuration::get('WKINVENTORY_PDFREPORT_MODE') == 'normal') {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminStocktake'));
            exit();
        }
        if (Tools::getValue('createpdf')) {
            $page_num = $this->createPDF(
                Tools::getValue('createpdf'),
                explode(',', Tools::getValue('inventories_products_ids')),
                Tools::getValue('createpdf'),
                Tools::getValue('page_num'),
                Tools::getValue('pdf_length'),
                Tools::jsonDecode(htmlspecialchars_decode(Tools::getValue('post_data')))
            );
            echo $page_num;
            exit();
        }
        if (Tools::getValue('getpdf')) {
            if (Tools::getValue('pdfList')) {
                $this->concatePDF(
                    Tools::jsonDecode(htmlspecialchars_decode(Tools::getValue('post_data'))),
                    Tools::getValue('pdfList'),
                    Tools::getValue('key') + 1
                );
            } else {
                $this->concatePDF(Tools::jsonDecode(htmlspecialchars_decode(Tools::getValue('post_data'))));
            }
            exit();
        }

        $id_inventory = (int)Tools::getValue('id_inventory');
        if (empty($id_inventory)) {
            $this->errors[] = $this->l('Please select an inventory before!');
        }

        $products2pdf = (floor(100 / $this->products2page)) * $this->products2page;
        StockTakeLog::addLog(
            WorkshopInv::filterMessageException('Products2PDF = '.$products2pdf),
            1, // severity
            null,
            'GetPDF',
            $id_inventory,
            true
        );

        $inventories_products_ids = array();

        /*if (Tools::getValue('inventories_products_ids')) {
            $inventories_products_ids = explode(',', Tools::getValue('inventories_products_ids'));
        } else*/
        if (!empty($id_inventory)) {
            $inventory = new StockTake($id_inventory);
            if (Validate::isLoadedObject($inventory)) {
                $results = $inventory->getInventoryProducts(true, false);

                foreach ($results as $result) {
                    $inventories_products_ids[] = (int)$result['id_inventory_product'];
                }
            } else {
                $this->errors[] = $this->l('Error occured, please select a valid inventory before!');
            }
        } else {
            $this->errors[] = $this->l('Error occured. No product has been found within the selected inventory!');
        }

        if (count($this->errors) == 0) {
            $this->tpl_view_vars['inventories_products_ids'] = Tools::jsonEncode($inventories_products_ids);
            $this->tpl_view_vars['parts'] = count(array_chunk($inventories_products_ids, $products2pdf));
            $post_data = $_POST;
            $post_data['parts'] = $this->tpl_view_vars['parts'];
            $post_data['products2pdf'] = $products2pdf;
            $post_data['id_inventory'] = $id_inventory;
            $this->tpl_view_vars['post_data'] = Tools::jsonEncode($post_data);
            $this->tpl_view_vars['url'] = $this->context->link->getAdminLink('AdminStocktakegetpdf', true);
            $this->tpl_view_vars['getpdflink'] = $this->context->link->getAdminLink('AdminStocktakegetpdf', true).'&getpdf=1';

            // Clean folder where the generated files will be stored (to be merged)
            if (file_exists(dirname(__file__) . '/../../pdf_list')) {
                foreach (glob(dirname(__file__) . '/../../pdf_list/*') as $file) {
                    unlink($file);
                }
            }
            $this->base_tpl_view = 'view.tpl';
            return parent::renderView();
        }
    }

    private function createPDF(
        $pdf_current,
        $inventories_products_ids,
        $pdf_num = 1,
        $page_num = 1,
        $pdf_length = 1,
        $post_data = array()
    ) {
        //$this->setEnvironmentParameters();
        $store_name = Configuration::get('PS_SHOP_NAME');

        $post_data = (array)$post_data;
        $post_data['pdf_num'] = $pdf_num;

        $sql = 'SELECT a.`id_product`, a.`real_quantity`, a.`unit_price`,
                IF(a.`id_product_attribute` > 0, pa.`reference`, p.`reference`) as reference,
                IF(a.`id_product_attribute` > 0, pa.`ean13`, p.`ean13`) as ean13, pl.`name`
                FROM `'._DB_PREFIX_.StockTakeProduct::$definition['table'].'` a 
                LEFT JOIN `'._DB_PREFIX_.'product` p ON (p.`id_product` = a.`id_product`) 
                '.Shop::addSqlAssociation('product', 'p').'
                LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (
                    p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.(int)$this->context->language->id
                    .Shop::addSqlRestrictionOnLang('pl').'
                )
                LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (
                    a.`id_product_attribute` = pa.`id_product_attribute`
                )
                WHERE a.`id_inventory` = '.(int)$post_data['id_inventory'].' AND 
                a.`'.StockTakeProduct::$definition['primary'].'` IN (%inventories_products_ids%)
                ORDER BY a.`'.StockTakeProduct::$definition['primary'].'` DESC
                LIMIT %offset%, %limit%';

        $products = $this->getProducts($sql, $inventories_products_ids, $post_data);
        $create_result = array();

        if (is_array($products) && (count($products) > 0)) {
            $mode = 'utf-8';
            $format = 'A4';
            $default_font_size = 0;
            $default_font = '';
            $margin_left = 0;
            $margin_right = 0;
            $margin_top = 0;
            $margin_bottom = 0;
            $margin_header = 0;
            $margin_footer = 0;
            $orientation = 'P';
            $margin_header = 0;
            $margin_footer = 0;

            $inventory = new StockTake((int)$post_data['id_inventory']);

            // Create PDF header
            $header = '';
            if (file_exists($this->template_dir.'header.tpl')) {
                $header = $this->context->smarty->fetch($this->template_dir.'header.tpl');
            }
            // Create PDF footer
            $footer = '';
            if (file_exists($this->template_dir.'footer.tpl')) {
                $footer = $this->context->smarty->fetch($this->template_dir.'footer.tpl');
            }
            // Generate PDF body contents
            $html = '';
            if (file_exists($this->template_dir.'template.tpl')) {
                $margin_top = 28.5;
                $margin_right = 0;
                $margin_bottom = 5.1;
                $margin_left = 0;
                $this->context->smarty->assign(array(
                    'inventory_products' => $products,
                    'count_of_products' => count($products),
                    'products_per_page' => $this->products2page,
                ));
                $html = $this->context->smarty->fetch($this->template_dir.'template.tpl');
            }
            // Create PDF css
            $stylesheet = '';
            if (file_exists($this->template_dir.'stylesheet.css')) {
                $stylesheet = Tools::file_get_contents($this->template_dir.'stylesheet.css');
            } else {
                if (file_exists($this->template_dir.'stylesheet.php')) {
                    ob_start();
                        include_once $this->template_dir.'stylesheet.php';
                        $stylesheet = ob_get_contents();
                    ob_end_clean();
                }
            }

            if (!empty($orientation)) {
                $orientation = '-' . $orientation;
            }

            $template_config_fonts = $this->template_dir.'fonts/template_config_fonts.php';
            if (file_exists($template_config_fonts)) {
                define('_MPDF_SYSTEM_TTFONTS_CONFIG', $template_config_fonts);
            }

            $mpdf = new mPDF(
                $mode,
                $format,
                $default_font_size,
                $default_font,
                $margin_left,
                $margin_right,
                $margin_top,
                $margin_bottom,
                $margin_header,
                $margin_footer,
                $orientation
            );

            if (Tools::strlen($stylesheet) > 0) {
                $mpdf->WriteHTML($stylesheet, 1);
            }

            $template_has_cover = false;
            if ($page_num == 1) {//first part
                $page_html = '';
                if (file_exists($this->template_dir.'cover_page.tpl')) {
                    $this->context->smarty->assign(array(
                        'store_name' => $store_name,
                        'server_name' => $_SERVER['SERVER_NAME'],
                        'inventory' => $inventory,
                        'begin_inventory' => Tools::displayDate($inventory->date_add),
                        'end_inventory' => Tools::displayDate($inventory->date_upd),
                        'employee_name' => WorkshopInv::getShopEmployeeName((int)$inventory->id_employee),
                        'logo' => $this->getLogo(),
                    ));
                    $page_html = $this->context->smarty->fetch($this->template_dir.'cover_page.tpl');
                }

                $template_has_cover = Tools::strlen($page_html) > 0;
                if ($template_has_cover === true) {
                    $mpdf->OVO_SetMargins(0, 0, 0, 0, 0, 0);
                    $mpdf->WriteHTML($page_html);
                    $mpdf->OVO_SetMargins(
                        $margin_left,
                        $margin_right,
                        $margin_top,
                        $margin_bottom,
                        $margin_header,
                        $margin_footer
                    );

                    $type = 'E';       //$type = E|O|even|odd|next-odd|next-even
                    $resetpagenum = 1; //$resetpagenum = 1 - ∞
                    $pagenumstyle = 1; //$pagenumstyle = 1|A|a|I|i
                    $suppress = false; //$suppress = on|off|1|0
                    $mpdf->AddPage(
                        $orientation,
                        $type,
                        $resetpagenum,
                        $pagenumstyle,
                        $suppress,
                        $margin_left,
                        $margin_right,
                        $margin_top,
                        $margin_bottom,
                        $margin_header,
                        $margin_footer
                    );
                    $mpdf->PageNumSubstitutions[] = array(
                        'from' => (1),
                        'reset' => $resetpagenum,
                        'type' => $pagenumstyle,
                        'suppress' => $suppress
                    );
                }
            }
            if ($template_has_cover === false) {
                $mpdf->PageNumSubstitutions[] = array(
                    'from' => (1),
                    'reset' => $page_num,
                    'type' => $page_num,
                    'suppress' => false
                );
            }
            $mpdf->SetDisplayMode('fullpage');

            if (Tools::strlen($header) > 0) {
                $mpdf->SetHTMLHeader($header);
            }
            if (Tools::strlen($footer) > 0) {
                $mpdf->SetHTMLFooter($footer);
            }
            $mpdf->WriteHTML($html);

            if ($pdf_current == $pdf_length) {//last part
                $page_html = '';
                if (file_exists($this->template_dir.'back_page.tpl')) {
                    $this->context->smarty->assign(array(
                        'server_name' => $_SERVER['SERVER_NAME'],
                        'stock_valuation' => $inventory->getStockValue($inventories_products_ids),
                        'inventory_count' => count($inventories_products_ids),
                    ));
                    $page_html = $this->context->smarty->fetch($this->template_dir.'back_page.tpl');
                }
                if (Tools::strlen($page_html) > 0) {
                    $mpdf->OVO_SetMargins(0, 0, 0, 0, 0, 0); // Left, Right, Top, Bottom, MH MF
                    $mpdf->WriteHTML($page_html);
                }
            }

            $create_result['page_num'] = $mpdf->PageNo();

            $file_name_full = $this->pdf_list_dir.'page'.sprintf('%04s', $pdf_num).'.pdf';
            $mpdf->Output($file_name_full, 'F');
        } else {
            StockTakeLog::addLog(
                WorkshopInv::filterMessageException('SQL-statement return empty result - '.$sql),
                2, // severity
                null,
                'GetPDF',
                $inventory->id,
                true
            );
            $create_result['error'] = 'SQL-statement return empty result';
        }
        StockTakeLog::addLog(
            WorkshopInv::filterMessageException('Create_result - '.json_encode($create_result)),
            1, // severity
            null,
            'GetPDF',
            $inventory->id,
            true
        );
        return Tools::jsonEncode($create_result);
    }

    public function getProducts(&$sql, $inventories_products_ids, $options)
    {
        $offset = ($options['pdf_num'] - 1) * $options['products2pdf'];
        unset($options['inventories_products_ids']);
        $healthy = array(
            '%inventories_products_ids%',
            "%limit%",
            "%offset%"
        );

        $yummy = array(
            join(',', array_map(array($this, 'castInt'), $inventories_products_ids)),
            (int)$options['products2pdf'],
            $offset
        );
        foreach ($options as $key => $option) {
            $healthy[] = '%'.$key.'%';
            if (is_array($option)) {
                if (count($option) > 0) {
                    $yummy[] = join(', ', $option);
                } else {
                    $yummy[] = '-1';
                }
            } else {
                $yummy[] = $option;
            }
        }

        $sql = str_replace($healthy, $yummy, $sql);
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);

        if (!is_array($result) || $result < 0) {
            StockTakeLog::addLog(
                WorkshopInv::filterMessageException('EMPTY RESULT SQL = '.$sql),
                2, // severity
                null,
                'GetPDF',
                null,
                true
            );
        }
        foreach ($result as &$product) {
            $product['combination'] = (
                !empty($product['id_product_attribute']) ? WorkshopInv::getAttributesCombinationNames($product['id_product_attribute']) : ''
            );
        }
        return $result;
    }

    private function concatePDF()
    {
        //$this->setEnvironmentParameters();
        $file_name = 'Inventory-report.pdf';
        $file_name_full = _PS_IMG_DIR_.$file_name;
        $file_version = '?v='.time();
        $file_url_full = _PS_IMG_.$file_name;

        if (file_exists($file_name_full)) {
            unlink($file_name_full);
        }

        $pdf_files = scandir($this->pdf_list_dir);
        unset($pdf_files[0], $pdf_files[1]);
        $filesize = 0;
        foreach ($pdf_files as $pdf_file) {
            $filesize += filesize($this->pdf_list_dir.$pdf_file);
        }
        $json = array();
        if (count($pdf_files) == 0) {
            $json['error'] = $this->l('Folder pdf_list is empty');
        } elseif (count($pdf_files) == 1) {
            $first = reset($pdf_files);
            rename($this->pdf_list_dir.$first, $file_name_full);
        } else {
            $mpdf = new mPDF('utf-8');
            $mpdf->SetImportUse();

            foreach ($pdf_files as $fk => $f) {
                for ($i = 1; $i <= $mpdf->SetSourceFile($this->pdf_list_dir.$f); $i++) {
                    if ($fk == 1) {
                        $tpl_id = $mpdf->ImportPage(1);
                    }
                    $tpl_id = $mpdf->ImportPage($i);
                    $pgw = $mpdf->tpls[$tpl_id]['w'];
                    $pgh = $mpdf->tpls[$tpl_id]['h'];

                    $orientation = $pgw > $pgh ? 'L' : 'P';

                    $mpdf->AddPage($orientation);
                    $mpdf->UseTemplate($tpl_id);

                    if ($fk == 1) {
                        break;
                    }
                }
            }

            $mpdf->Output($file_name_full, 'F');
        }
        $json['link'] = $file_url_full.$file_version;

        echo Tools::jsonEncode($json);
    }

    /**
     * Returns the invoice logo
     */
    protected function getLogo()
    {
        $logo = '';
        $id_shop = (int)$this->context->shop->id;

        if (Configuration::get('PS_LOGO_INVOICE', null, null, $id_shop) != false &&
            file_exists(_PS_IMG_DIR_.Configuration::get('PS_LOGO_INVOICE', null, null, $id_shop))) {
            $logo = _PS_IMG_DIR_.Configuration::get('PS_LOGO_INVOICE', null, null, $id_shop);
        } elseif (Configuration::get('PS_LOGO', null, null, $id_shop) != false &&
            file_exists(_PS_IMG_DIR_.Configuration::get('PS_LOGO', null, null, $id_shop))) {
            $logo = _PS_IMG_DIR_.Configuration::get('PS_LOGO', null, null, $id_shop);
        }
        return $logo;
    }

    private function getCatalogImagePath($id_image, $type = null)
    {
        $result = _PS_IMG_DIR_.'404.gif';
        if (!empty($id_image)) {
            $image_full_name = _PS_PROD_IMG_DIR_.Image::getImgFolderStatic($id_image).$id_image.($type ? '-'.$type : '').'.jpg';
            if (file_exists($image_full_name)) {
                $result = $image_full_name;
            }
        }
        return $result;
    }

    private function getStoreURL()
    {
        return _PS_BASE_URL_.__PS_BASE_URI__;
    }

    public function setEnvironmentParameters()
    {
        ini_set("memory_limit", "4096M");
        ini_set("max_execution_time", "3600");
    }

    private function castInt($int)
    {
        return (int)$int;
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $this->addJqueryUI('ui.progressbar');
        $this->addJs(_MODULE_DIR_.$this->module->name.'/views/js/getpdf.min.js');

        $this->addCSS(array(_MODULE_DIR_.$this->module->name.'/views/css/getpdf.css'));
    }

    public function initPageHeaderToolbar()
    {
        $this->page_header_toolbar_btn['back_to_dashboard'] = array(
            'href' => $this->context->link->getAdminLink('AdminStocktakedash'),
            'desc' => $this->l('Dashboard', null, null, false),
            'icon' => 'process-icon-back'
        );
        parent::initPageHeaderToolbar();
        unset($this->page_header_toolbar_btn['back']);
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
