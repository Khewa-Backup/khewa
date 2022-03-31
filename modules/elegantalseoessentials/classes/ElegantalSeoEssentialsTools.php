<?php
/**
 * @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
 * @copyright (c) 2022, Jamoliddin Nasriddinov
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
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
        // return Tools::jsonEncode($array);
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
        // $array = Tools::jsonDecode($string, true);
        // $array = @unserialize($string);
        // $array = @unserialize(base64_decode($string));
        $array = @unserialize(call_user_func('base64_decode', $string));
        return empty($array) ? array() : $array;
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
            "|" => 0
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
     * Returns temporary directory name
     * @return string
     */
    public static function getTempDir()
    {
        $filename = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'tmp';

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
