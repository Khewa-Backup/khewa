<?php
/**
 * RockPOS - Point of Sale for PrestaShop.
 * @author    Hamsa Technologies
 * @copyright Hamsa Technologies
 * @license   One license is granted for only one website.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class PosUpgrader411 extends PosUpgrader
{
    protected $configuration_keys_to_rename = array(
        'POS_DEFAULT_ID_CUSTOMER' => 'POS_DEFAULT_ID_CUSTOMERS'
    );

    /**
     * @return bool
     */
    protected function installOthers()
    {
        $success = array();
        $current_pos_default_id_customer = (int)Configuration::get('POS_DEFAULT_ID_CUSTOMER');
        if ($current_pos_default_id_customer > 0) {
            $success[] = Configuration::updateValue(
                'POS_DEFAULT_ID_CUSTOMER',
                '{"-1":"' . $current_pos_default_id_customer . '"}'
            );
        }
        if (!Configuration::get('POS_PRODUCT_OUT_OF_STOCK')) {
            $configuration_keys_to_rename = array(
                'POS_ORDER_OUT_OF_STOCK' => 'POS_PRODUCT_OUT_OF_STOCK',
            );
            $success[] = PosConfiguration::renameMultiple(
                array_keys($configuration_keys_to_rename),
                array_values($configuration_keys_to_rename)
            );
        }
        return array_sum($success) >= count($success);
    }
}
