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
class PosTools extends ToolsCore
{

    /**
     * @param array array
     * <pre>
     * array (
     *  array(
     *      key1 => A
     *      key2 => int
     *  ),
     *  array(
     *      key1 => B
     *      key2 => int
     *  ),
     *  array(
     *      key1 => A, C
     *      key2 => int
     *  )
     * )
     * @param string key
     * @return array
     * <pre>
     * array(
     *  array(
     *      key1 => A
     *      key2 => int
     *  ),
     *  array(
     *      key1 => B
     *      key2 => int
     *  ),
     *
     *),
     */
    public static function uniqueMultidimensionalArray(array $array, $key)
    {
        $temp_array = array();
        $unique_value = array();

        $i = 0;
        $j = 0;
        if (!empty($array)) {
            foreach ($array as $value) {
                if (isset($value[$key])) {
                    $key_values = explode(',', $value[$key]);
                    if (!array_intersect($key_values, $unique_value)) {
                        foreach ($key_values as $key_value) {
                            $unique_value[$i] = $key_value;
                            $i++;
                        }
                        $temp_array[$j] = $value;
                        $j++;
                    }
                }
            }
        }
        return $temp_array;
    }

    /**
     * @param null $original_email
     * @param null $suffix
     * @return string
     */
    public static function generateFakeEmail($original_email = null, $suffix = null)
    {
        if ($original_email && $suffix) {
            $suffix = trim(preg_replace('/\s+/', '', $suffix));
            $email = $suffix . '.' . self::generateRandomString(6) . $original_email;
        } else {
            $email = self::generateRandomString(10) . Configuration::get('PS_SHOP_EMAIL');
        }

        if (PosCustomer::getCustomersByEmail($email)) {
            return self::generateFakeEmail($original_email, $suffix . '.' . self::generateRandomString(2));
        }

        return $email;
    }

    /**
     * @param int $length
     * @return string
     */
    public static function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = Tools::strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * @return mixed
     */
    public static function getRealIpAddr()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        //check ip from share internet
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        return $ip;
    }
}
