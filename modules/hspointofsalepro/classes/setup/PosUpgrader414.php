<?php
/**
 * RockPOS - Point of Sale for PrestaShop.
 *
 * @author    Hamsa Technologies
 * @copyright Hamsa Technologies
 * @license   One license is granted for only one website.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}
class PosUpgrader414 extends PosUpgrader
{

    /**
     * @see parent::$configurations_to_install
     */
    protected $configurations_to_install = array(
        'POS_RECEIPT_LOGO_MAX_WIDTH' => PosConstants::DEFAULT_RECEIPT_LOGO_MAX_WIDTH,
        'POS_RECEIPT_PAGE_SIZE' => PosConstants::DEFAULT_RECEIPT_PAGE_SIZE,
        'POS_RECEIPT_PRINTER_DPI' => PosConstants::DEFAULT_PRINTER_DPI,
    );
}
