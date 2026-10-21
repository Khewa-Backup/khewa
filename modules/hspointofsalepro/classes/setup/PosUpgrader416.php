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
            json_encode(array($context->employee->id => PosOrder::getDefaultOrderFields()))
        );
        $success[] = Configuration::updateValue('POS_PERMISSIONS', json_encode($permissions));
        return array_sum($success) >= count($success);
    }
}
