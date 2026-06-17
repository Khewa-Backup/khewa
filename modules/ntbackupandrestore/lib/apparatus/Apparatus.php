<?php
/**
 * 2013-2024 2N Technologies
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@2n-tech.com so we can send you a copy immediately.
 *
 * @author    2N Technologies <contact@2n-tech.com>
 * @copyright 2013-2024 2N Technologies
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Apparatus
{
    const CIPHER_CRYPTAGE = 'aes-256-cbc'; // The cipher
    const CLE_CRYPTAGE = 'D_T+rW*H`0b84ra.YIen(X|>_Ot&|va;9odG:Gkk3meU=y5kBf3}Yuim'; // The cryptage key

    public function __construct()
    {
    }

    /**
     * Encodes data with MIME base64
     *
     * @param string $data the data to encode
     *
     * @return string the encoded data, as a string
     */
    public static function b64_encode($data)
    {
        return base64_encode($data);
    }

    /**
     * Decodes data encoded with MIME base64
     *
     * @param string $data The encoded data
     * @param bool $strict If the strict parameter is set to TRUE then the base64_decode() function will return FALSE if the input contains character from outside the base64 alphabet. Otherwise invalid characters will be silently discarded.
     *
     * @return string The decoded data or FALSE on failure. The returned data may be binary.
     */
    public static function b64_decode($data, $strict = false)
    {
        return base64_decode($data, $strict);
    }

    /**
     * Generate a keyed hash value using the HMAC method
     *
     * @param string $algo Name of selected hashing algorithm (i.e. "md5", "sha256", "haval160,4", etc..) See hash_hmac_algos() for a list of supported algorithms.
     * @param string $data message to be hashed
     * @param string $key shared secret key used for generating the HMAC variant of the message digest
     * @param bool $raw_output When set to TRUE, outputs raw binary data. FALSE outputs lowercase hexits.
     *
     * @return string The String decoded
     */
    public static function hash_hm($algo, $data, $key, $raw_output = false)
    {
        return hash_hmac($algo, $data, $key, $raw_output);
    }

    /**
     *  Add two arbitrary precision numbers
     *
     * @param string $left_operand
     * @param string $right_operand
     * @param int $scale
     *
     * @return string the sum of the two operands, as a string
     */
    public static function bc_add($left_operand, $right_operand, $scale = 0)
    {
        return bcadd($left_operand, $right_operand, $scale);
    }

    /**
     *  Subtract one arbitrary precision number from another
     *
     * @param string $left_operand
     * @param string $right_operand
     * @param int $scale
     *
     * @return string the result of the subtraction, as a string
     */
    public static function bc_sub($left_operand, $right_operand, $scale = 0)
    {
        return bcsub($left_operand, $right_operand, $scale);
    }

    /**
     * Get all values from $_POST/$_GET.
     *
     * @return mixed
     */
    public static function getAllValues()
    {
        return $_POST + $_GET;
    }

    /**
     * Decodes a hexadecimally encoded binary string
     *
     * @param string $hexa
     *
     * @return binary or false
     */
    public static function hex2bin($hexa)
    {
        if (!function_exists('hex2bin')) {
            $out = '';
            for ($c = 0; $c < strlen($hexa); $c += 2) {
                $out .= chr(hexdec($hexa[$c] . $hexa[$c + 1]));
            }

            return (string) $out;
        } else {
            return hex2bin($hexa);
        }
    }

    /**
     * Timing attack safe string comparison
     *
     * @param string $known_string The string of known length to compare against
     * @param string $user_string The user-supplied string
     *
     * @return bool
     */
    public static function hash_equals($known_string, $user_string)
    {
        if (!function_exists('hash_equals')) {
            if (strlen($known_string) != strlen($user_string)) {
                return false;
            } else {
                $res = $known_string ^ $user_string;
                $ret = 0;
                for ($i = strlen($res) - 1; $i >= 0; --$i) {
                    $ret |= ord($res[$i]);
                }

                return !$ret;
            }
        } else {
            return hash_equals($known_string, $user_string);
        }
    }

    /**
     * Return part of a string
     *
     * @param string    The input string
     * @param int       Start position
     * @param int       Length characters
     *
     * @return string extracted part of string; or FALSE on failure, or an empty string
     */
    public static function substr($string, $start, $length = 0)
    {
        if (!$length) {
            return substr($string, $start);
        }

        return substr($string, $start, $length);
    }

    /**
     * Get size of a String
     *
     * @param string    The input string
     *
     * @return int The size of the String
     */
    public static function strlen($string)
    {
        return strlen($string);
    }

    /**
     * Get absolute path
     *
     * @param string    The path
     *
     * @return string The absolute path
     */
    public static function getRealPath($path)
    {
        if (function_exists('stream_resolve_include_path')) {
            return stream_resolve_include_path($path);
        }

        return realpath($path);
    }

    /**
     * Check if file exists
     *
     * @param string    The path of the file
     *
     * @return bool True if the file exists false otherwise
     */
    public static function checkFileExists($path)
    {
        // stream_resolve_include_path and file_exists does not accept null as param
        if (is_null($path)) {
            $path = '';
        }

        if (function_exists('stream_resolve_include_path')) {
            return (stream_resolve_include_path($path) !== false) ? true : false;
        }

        return file_exists($path);
    }

    /**
     * Second to Hours:minutes:seconds
     *
     * @param float     The seconds to convert
     *
     * @return string True if the file exists false otherwise
     */
    public static function secondsToReadableHours($time_seconds)
    {
        $hours = (int) ($time_seconds / 3600);
        $minutes = (int) (((int) $time_seconds % 3600) / 60);
        $seconds = (int) (((int) $time_seconds % 3600) % 60);

        return (($hours < 10) ? '0' : '') . $hours . ':' . (($minutes < 10) ? '0' : '') . $minutes . ':' . (($seconds < 10) ? '0' : '') . $seconds;
    }

    public static function encrypt($pure_string)
    {
        if (!extension_loaded('openssl')) {
            return false;
        }

        $iv_size = openssl_cipher_iv_length(self::CIPHER_CRYPTAGE);
        $iv = openssl_random_pseudo_bytes($iv_size);

        $encrypted_string = openssl_encrypt(
            $pure_string,
            self::CIPHER_CRYPTAGE,
            self::CLE_CRYPTAGE,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($encrypted_string === false) {
            $errors = [];

            while ($msg = openssl_error_string()) {
                $errors[] = $msg;
            }

            return $errors;
        }

        $hmac = self::hash_hm('sha256', $encrypted_string, self::CLE_CRYPTAGE, true);

        return self::b64_encode($iv . $hmac . $encrypted_string);
    }

    public static function decrypt($encrypted_string)
    {
        if (!extension_loaded('openssl')) {
            return false;
        }

        if ($encrypted_string == '') {
            return $encrypted_string;
        }

        $decode_string = self::b64_decode($encrypted_string);
        $iv_size = openssl_cipher_iv_length(self::CIPHER_CRYPTAGE);
        $iv = Tools::substr($decode_string, 0, $iv_size, '8bit');
        $hmac = Tools::substr($decode_string, $iv_size, 32, '8bit');
        $ciphertext_raw = str_replace($iv . $hmac, '', $decode_string);

        $decrypted_string = openssl_decrypt(
            $ciphertext_raw,
            self::CIPHER_CRYPTAGE,
            self::CLE_CRYPTAGE,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($decrypted_string === false) {
            $errors = [];

            while ($msg = openssl_error_string()) {
                $errors[] = $msg;
            }

            return $errors;
        }

        $calcmac = self::hash_hm('sha256', $ciphertext_raw, self::CLE_CRYPTAGE, true);

        if (self::hash_equals($hmac, $calcmac)) {// timing attack safe comparison
            return $decrypted_string;
        }

        return false;
    }

    public static function endsWith($haystack, $needle)
    {
        return substr_compare($haystack, $needle, -strlen($needle)) === 0;
    }

    public static function is_cli()
    {
        if (defined('STDIN')) {
            return true;
        }

        if (php_sapi_name() === 'cli') {
            return true;
        }

        if (array_key_exists('SHELL', $_ENV)) {
            return true;
        }

        if (empty($_SERVER['REMOTE_ADDR']) && !isset($_SERVER['HTTP_USER_AGENT']) && count($_SERVER['argv']) > 0) {
            return true;
        }

        if (!array_key_exists('REQUEST_METHOD', $_SERVER)) {
            return true;
        }

        return false;
    }
}
