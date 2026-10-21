<?php
/**
 * RockPOS stkverifactu
 *
 * @author    Hamsa Technologies
 * @copyright Hamsa Technologies
 * @license   One license is granted for only one website.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class PosStkverifactuModule extends PosVeriFactuAbstract
{

    public static $temp_qr_files = [];
    const FIELD_ID_TYPE_DOC = 'id_stkverifactutypedoc';

    public function __construct()
    {
        $this->module_name = 'stkverifactu';
    }

    public static function isAddressFieldAvailable()
    {
        $module_name = 'stkverifactu';
        if (!Module::isInstalled($module_name) || !Module::isEnabled($module_name)) {
            return false;
        }

        $address = new Address();
        return property_exists($address, self::FIELD_ID_TYPE_DOC)
            && isset(Address::$definition['fields'][self::FIELD_ID_TYPE_DOC]);
    }

    public static function getAddressFieldOptions()
    {
        if (!self::isAddressFieldAvailable()) {
            return array();
        }

        $module_name = 'stkverifactu';
        if (!class_exists('StkVerifactuTypeDoc')) {
            $type_doc_file = _PS_MODULE_DIR_ . $module_name . '/StkVerifactuTypeDoc/StkVerifactuTypeDoc.php';
            if (file_exists($type_doc_file)) {
                require_once $type_doc_file;
            }
        }

        if (!class_exists('StkVerifactuTypeDoc')) {
            return array();
        }

        $module = Module::getInstanceByName($module_name);
        $options = array(
            array(
                'value' => '0',
                'label' => $module ? $module->l('Select a document type', $module_name) : 'Select a document type',
            ),
        );

        $doc_types = StkVerifactuTypeDoc::getAll();
        if (!is_array($doc_types)) {
            return array(self::FIELD_ID_TYPE_DOC => $options);
        }

        foreach ($doc_types as $doc_type) {
            if (!isset($doc_type[self::FIELD_ID_TYPE_DOC])) {
                continue;
            }
            $label = isset($doc_type['name']) ? $doc_type['name'] : '';
            $options[] = array(
                'value' => (string) $doc_type[self::FIELD_ID_TYPE_DOC],
                'label' => $module ? $module->l($label, $module_name) : $label,
            );
        }

        return array(self::FIELD_ID_TYPE_DOC => $options);
    }

    /**
     * Get QR code url, see more it in the function hookDisplayPDFInvoice of module VeriFacturacion Module
     * @param array $params
     * @return string
     */
    public function getQRCodeUrl($params)
    {
        $qr_code_url_full = '';
        $order_invoice = $params['order_invoice'];
        if (!Validate::isLoadedObject($order_invoice)) {
            return '';
        }
        $order = new Order($order_invoice->id_order);
        if (!Validate::isLoadedObject($order)) {
            return '';
        }
        if (Module::isInstalled($this->module_name) && Module::isEnabled($this->module_name)) {
            $id_order_invoice = $order_invoice->id;
            $stk_verifactu_url_format = Configuration::get('STKVERIFACTU_ENTORNO') == 'prod' ? '%/www%' : '%prewww%';
            $qr_code_url = $this->getStkVerifactuUrlByIdOrderInvoice($id_order_invoice, $stk_verifactu_url_format);
            if ($qr_code_url) {
                $qr_folder = _PS_MODULE_DIR_ . $this->module_name . '/qrcodes/';
                if (!file_exists($qr_folder)) {
                    mkdir($qr_folder, 0755, true);
                }
                $qr_file_name = 'invoice_' . $id_order_invoice . '.png';
                $qr_file_path = $qr_folder . $qr_file_name;
                $module = Module::getInstanceByName($this->module_name);
                $module->generateQrCode($qr_code_url, $qr_file_path);
                $qr_code_url_full = _MODULE_DIR_ . $this->module_name . '/qrcodes/' . $qr_file_name;
            }
        }
        return $qr_code_url_full;
    }

    public static function getStkVerifactuUrlByIdOrderInvoice($id_order_invoice, $stk_verifactu_url_format)
    {
        $query = new DbQuery();
        $query->select('`stk_verifactu_url`');
        $query->from('order_invoice');
        $query->where('`stk_verifactu_url` LIKE \'%' . pSQL($stk_verifactu_url_format) . '%\'');
        $query->where('`id_order_invoice` = ' . (int) $id_order_invoice);
        return Db::getInstance()->getValue($query);
    }

    public function getTextConfigurations($id_shop)
    {
        return '';
        //return Configuration::get('VERIFACTURACION_QR_TEXT', null, null, $id_shop);
    }
}
