<?php
/**
 * RockPOS - Point of Sale for PrestaShop.
 *
 * @author    Hamsa Technologies
 * @copyright Hamsa Technologies
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 *
 */
class PosUpgrader423 extends PosUpgrader
{
    public function installConfigs()
    {
        Configuration::updateValue('POS_AUTOMATIC_PRINT_QUANTITY', 1);
    }
}
