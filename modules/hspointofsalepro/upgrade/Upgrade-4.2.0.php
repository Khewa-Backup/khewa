<?php
/**
 * RockPOS - Point of Sale for PrestaShop
 *
 * @author    Hamsa Technologies
 * @copyright Hamsa Technologies
 * @license   One license is granted for only one website.
 */

/**
 *
 * @param HsPointOfSalePro $module
 * @return bool
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
function upgrade_module_4_2_0($module)
{
    $sub_versions = array();
    foreach (explode('_', __FUNCTION__) as $element) {
        if (Validate::isInt($element)) {
            $sub_versions[] = $element;
        }
    }
    copy(_ROCKPOS_DIR_ . '/override/classes/Cart.php', _PS_ROOT_DIR_ . '/override/classes/Cart.php');
    return $module->upgrade(implode('.', $sub_versions));
}
