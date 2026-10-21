<?php
/**
 * RockPOS - Point of Sale for PrestaShop.
 *
 * @author    Hamsa Technologies
 * @copyright Hamsa Technologies
 * @license   One license is granted for only one website.
 */

/**
 * @since 4.6.0
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class PosValidate extends Validate
{
    public static function isPasswd($passwd, $size = 5)
    {
        if (version_compare(_PS_VERSION_, '9', '>=')) {
            return parent::isAcceptablePasswordLength($passwd);
        } elseif (version_compare(_PS_VERSION_, '8', '>=')) {
            $size = Validate::PASSWORD_LENGTH;
            return parent::isPlaintextPassword($passwd, $size);
        } else {
            $size = Validate::PASSWORD_LENGTH;
            return parent::isPasswd($passwd, $size);
        }
    }
}
