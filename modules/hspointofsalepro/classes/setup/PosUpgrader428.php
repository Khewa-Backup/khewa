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
class PosUpgrader428 extends PosUpgrader
{
    protected $hooks_to_register = array(
           'actionOrderGridQueryBuilderModifier',
       );
}
