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
class PosState extends State
{

    /**
     * @param int $id_country
     *
     * @return int
     */
    public static function getDefaultState($id_country)
    {
        $default_state = 0;
        $states = self::getStatesByIdCountry($id_country);
        if (!empty($states)) {
            $state = current($states);
            $default_state = (int)$state['id_state'];
        }
        return $default_state;
    }
}
