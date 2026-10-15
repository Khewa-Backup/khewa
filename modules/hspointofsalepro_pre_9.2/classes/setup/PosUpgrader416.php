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
class PosUpgrader416 extends PosUpgrader
{
    /**
     * @return bool
     */
    protected function installOthers()
    {
        $success = array();
        $context = Context::getContext();
        $profiles = $this->module->getPosProfiles();
        $permissions = PosProfile::getDefaultPermissions($profiles);
        $success[] = Configuration::updateValue(
            'POS_ORDER_FIELDS',
            Tools::jsonEncode(array($context->employee->id => PosOrder::getDefaultOrderFields()))
        );
        $success[] = Configuration::updateValue('POS_PERMISSIONS', Tools::jsonEncode($permissions));
        return array_sum($success) >= count($success);
    }
}
