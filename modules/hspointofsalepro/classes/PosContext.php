<?php
/**
 * RockPOS - Point of Sale for PrestaShop.
 *
 * @author    Hamsa Technologies
 * @copyright Hamsa Technologies
 * @license   One license is granted for only one website.
 */

/**
 * Extended Context for RockPOS.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class PosContext extends Context
{
    /**
     * Reset element(s) of Context to reflect RockPOS classes instead of PrestaShop classes<br/>
     * This method should be called right after "Cart::save()".
     */
    public static function resetContext()
    {
        if (!empty(self::getContext()->cart) && !self::getContext()->cart instanceof PosCart) {
            self::getContext()->cart = new PosCart(self::getContext()->cart->id);
        }
    }
}
