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
class PosUpgrader423 extends PosUpgrader
{
    public function installConfigs()
    {
        Configuration::updateValue('POS_AUTOMATIC_PRINT_QUANTITY', 1);
    }
}
