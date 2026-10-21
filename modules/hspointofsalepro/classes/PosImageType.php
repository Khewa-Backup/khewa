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
class PosImageType extends ImageType
{
    public static function getFormattedName($name)
    {
        return method_exists(
            'ImageType',
            'getFormattedName'
        ) ? parent::getFormattedName($name) : parent::getFormatedName($name);
    }
}
