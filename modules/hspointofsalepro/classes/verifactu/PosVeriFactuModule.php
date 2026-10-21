<?php
/**
 * RockPOS VeriFactu
 *
 * @author    Hamsa Technologies
 * @copyright Hamsa Technologies
 * @license   One license is granted for only one website.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class PosVeriFactuModule extends PosVeriFactuAbstract
{

    public static $temp_qr_files = [];
    public function __construct()
    {
        $this->module_name = 'verifactu';
    }
    /**
     * Get QR code url, see more it in the function hookDisplayPDFInvoice of module verifactu
     * @param array $params
     * @return string
     */
    public function getQRCodeUrl($params)
    {
        $qr_code_url = '';
        $order_invoice = $params['order_invoice'];
        if (!Validate::isLoadedObject($order_invoice)) {
            return $qr_code_url;
        }
        $order = new Order($order_invoice->id_order);
        if (!Validate::isLoadedObject($order)) {
            return $qr_code_url;
        }
        if (Module::isInstalled($this->module_name) && Module::isEnabled($this->module_name)) {
            $module = Module::getInstanceByName($this->module_name);
            $qr_code_url = $module->getQRCodeUrl($params);
        }
        return $qr_code_url;
    }
    
    public function getTextConfigurations($id_shop)
    {
        $tex_configuration = '';
        if (Module::isInstalled($this->module_name) && Module::isEnabled($this->module_name)) {
            $module = Module::getInstanceByName($this->module_name);
            $tex_configuration = $module->getVeriFactuQrText($id_shop);
        }
        return $tex_configuration;
    }
}
