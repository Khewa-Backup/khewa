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
class PosCountry extends Country
{

    /**
     * @param int $id_country
     *
     * @return string
     */
    public static function generateDefaultZipCode($id_country)
    {
        $zipcode = PosConstants::DEFAULT_POSTCODE;
        $zip_code_format = self::getZipCodeFormat($id_country);
        if ($zip_code_format) {
            $zipcode = str_replace(array('L', 'N'), array('A', '0'), $zip_code_format);
        }

        return $zipcode;
    }
}
