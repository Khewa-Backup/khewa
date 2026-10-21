<?php
/**
 * RockPOS - Point of Sale for PrestaShop
 *
 * @author    Hamsa Technologies
 * @copyright Hamsa Technologies
 * @license   One license is granted for only one website.
 */

/**
 * Rendering invoices in PDF
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class PosPDFGeneratorSalesSummary extends PosPDFGenerator
{
    protected function setPdfMargins()
    {
        $this->SetHeaderMargin(15);
        $this->SetFooterMargin(15);
        $this->setMargins(40, 30);
    }
}
