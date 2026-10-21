<?php
/**
 * RockPOS VeriFacturacion
 *
 * @author    Hamsa Technologies
 * @copyright Hamsa Technologies
 * @license   One license is granted for only one website.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class PosVeriFacturacionModule extends PosVeriFactuAbstract
{

    public static $temp_qr_files = [];
    public function __construct()
    {
        $this->module_name = 'verifacturacion';
    }

    /**
     * Get QR code url, see more it in the function hookDisplayPDFInvoice of module VeriFacturacion Module
     * @param array $params
     * @return string
     */
    public function getQRCodeUrl($params)
    {
        $order_invoice = $params['order_invoice'];
        if (!Validate::isLoadedObject($order_invoice)) {
            return '';
        }
        $order = new Order($order_invoice->id_order);
        if (!Validate::isLoadedObject($order)) {
            return '';
        }
        return Hook::exec('ActionGetVeriFactuQR', ['id_invoice' => $order_invoice->id]);
    }

    public function getTextConfigurations($id_shop)
    {
        return Configuration::get('VERIFACTURACION_QR_TEXT', null, null, $id_shop);
    }
}
