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
class PosUpgrader412 extends PosUpgrader
{

    /**
     * @see parent::$configurations_to_install
     */
    protected $configurations_to_install = array(
        'POS_PRODUCT_ACTIVE' => 1,
        'POS_PRODUCT_INSTOCK' => 1
    );
}
