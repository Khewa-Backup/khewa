<?php
/**
 * @author    ELEGANTAL <info@elegantal.com>
 * @copyright (c) 2023, ELEGANTAL <www.elegantal.com>
 * @license   Proprietary License - It is forbidden to resell or redistribute copies of the module or modified copies of the module.
 */

/**
 * This is a helper class which provides some functions used all over the module
 */
class ElegantalSeoEssentialsTools
{

    /**
     * Serializes array to store in database
     * @param array $array
     * @return string
     */
    public static function serialize($array)
    {
        // return json_encode($array);
        // return serialize($array);
        // return base64_encode(serialize($array));
        return call_user_func('base64_encode', serialize($array));
    }

    /**
     * Un-serializes serialized string
     * @param string $string
     * @return array
     */
    public static function unserialize($string)
    {
        // $array = json_decode($string, true);
        // $array = @unserialize($string);
        // $array = @unserialize(base64_decode($string));
        $array = @unserialize(call_user_func('base64_decode', $string));
        return empty($array) ? array() : $array;
    }

    /**
     * Checks if given module is installed
     * @param string $module_name
     * @return bool
     * @throws PrestaShopException
     */
    public static function isModuleInstalled($module_name)
    {
        return (bool) Module::getModuleIdByName($module_name);
    }

    /**
     * Returns formatted price with currency sign
     * @param float $price
     * @return string
     */
    public static function displayPrice($price)
    {
        if (method_exists('Tools', 'displayPrice')) {
            $price = call_user_func(array('Tools', 'displayPrice'), $price);
        } elseif (method_exists('Tools', 'getContextLocale')) {
            $context = Context::getContext();
            $currency = $context->currency;
            if (is_int($currency)) {
                $currency = Currency::getCurrencyInstance($currency);
            }
            $locale = call_user_func(array('Tools', 'getContextLocale'), $context);
            $currency_code = is_array($currency) ? $currency['iso_code'] : $currency->iso_code;
            $price = $locale->formatPrice($price, $currency_code);
        }
        return $price;
    }

    /**
     * Returns formatted file size in GB, MB, KB or bytes
     * @param int $size
     * @return string
     */
    public static function displaySize($size)
    {
        $size = (int) $size;

        if ($size < 1024) {
            $size .= " bytes";
        } elseif ($size < 1048576) {
            $size = round($size / 1024) . " KB";
        } elseif ($size < 1073741824) {
            $size = round($size / 1048576, 1) . " MB";
        } else {
            $size = round($size / 1073741824, 1) . " GB";
        }

        return $size;
    }

    /**
     * Identifies delimiter usde in the csv file
     * @param string $file
     * @return string
     * @throws Exception
     */
    public static function identifyCsvDelimiter($file)
    {
        $delimiters = array(
            ';' => 0,
            ',' => 0,
            "\t" => 0,
            "|" => 0,
        );

        $handle = false;

        if (is_file($file) && is_readable($file) && filesize($file)) {
            $handle = fopen($file, 'r');
        }

        if (!$handle) {
            throw new Exception('Cannot read the CSV file.');
        }

        // First line is column titles, so we assume it would have normal values (no line breaks).
        $first_line = fgets($handle);

        fclose($handle);

        foreach ($delimiters as $delimiter => &$count) {
            $count = count(str_getcsv($first_line, $delimiter));
        }

        return array_search(max($delimiters), $delimiters);
    }

    /**
     * Returns URL adding query parameters provided
     * @param string $url
     * @param mixed string|array $params
     * @return string
     */
    public static function addGetParamsToUrl($url, $params)
    {
        if (empty($params)) {
            return $url;
        }

        $parsed_url = parse_url($url);
        $url .= empty($parsed_url['path']) ? '/' : '';
        $url .= empty($parsed_url['query']) ? '?' : '&';

        if (!is_array($params)) {
            // Remove ? from beginning if exists
            $params = explode('&', ltrim($params, '?'));
            foreach ($params as $key => $param) {
                $subparam = explode('=', $param);
                $params[$subparam[0]] = isset($subparam[1]) ? $subparam[1] : '';
                unset($params[$key]);
            }
        }

        $query = "";
        foreach ($params as $key => $value) {
            $query .= !empty($query) ? '&' : '';
            $query .= urlencode($key);
            $query .= !empty($value) ? '=' . urlencode($value) : '';
        }

        $url .= $query;

        return $url;
    }

    /**
     * Returns mime type of given file
     * @param string $file
     * @param string $extension
     * @return string|boolean
     */
    public static function getMimeType($file, $extension = null)
    {
        if (!is_file($file)) {
            return false;
        }
        $mime = null;
        if (function_exists('finfo_file') && function_exists('finfo_open') && defined('FILEINFO_MIME_TYPE')) {
            // Use the Fileinfo PECL extension (PHP 5.3+)
            $mime = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file);
        } elseif (function_exists('mime_content_type')) {
            // Deprecated in PHP 5.3
            $mime = mime_content_type($file);
        } elseif (function_exists('exif_imagetype')) {
            $exif_imagetype = call_user_func('exif_imagetype', $file);
            if ($exif_imagetype == IMAGETYPE_PNG) {
                $mime = 'image/png';
            } elseif ($exif_imagetype == IMAGETYPE_JPEG) {
                $mime = 'image/jpeg';
            }
        }
        $mime = $mime ? Tools::strtolower($mime) : null;

        if ($mime == 'text/plain' || $mime == 'application/octet-stream') {
            $handle = fopen($file, 'r');
            $line = fread($handle, 10);
            $first_char = Tools::substr($line, 0, 1);
            fclose($handle);
            if ($first_char == '{' || $first_char == '[') {
                $mime = 'application/json';
            } elseif (Tools::strtolower(Tools::substr($line, 0, 5)) == '<?xml') {
                $mime = 'application/xml';
            }
        } elseif ($mime == 'application/cdfv2' && $extension == 'xls') {
            $mime = 'application/vnd.ms-excel';
        } elseif ($mime == 'application/zip' && $extension == 'xls') {
            $mime = 'application/vnd.ms-excel';
        } elseif ($mime == 'application/zip' && $extension == 'xlsx') {
            $mime = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        } elseif ($mime == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheetapplication/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
            $mime = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        } elseif ($mime == 'text/x-algol68') {
            $mime = 'text/csv';
        } elseif ($mime == 'text/x-fortran') {
            $mime = 'text/csv';
        }

        return $mime ? $mime : false;
    }

    /**
     * Returns temporary directory name
     * @return string
     */
    public static function getTempDir()
    {
        $filename = dirname(__FILE__) . '/../tmp';

        if (!is_dir($filename)) {
            mkdir($filename);
            chmod($filename, 0777);
        }

        if (is_dir($filename) && is_writable($filename)) {
            return $filename;
        } elseif (function_exists('sys_get_temp_dir')) {
            return sys_get_temp_dir();
        }

        return dirname(__FILE__);
    }
}
