<?php
/**
 *  @author    Amazzing <mail@mirindevo.com>
 *  @copyright Amazzing
 *  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class ExtendedTools extends Tools
{
    public static function prepareRangeOptions(&$options, $implode = false)
    {
        foreach ($options as &$opt) {
            if (!is_array($opt)) {
                $opt = self::explodeRangeValue($opt); // normalize even if $implode == true
            }
            if ($implode) {
                $opt = self::implodeRangeValue($opt);
            }
        }

        return $options;
    }

    public static function explodeRangeValue($value)
    {
        $value = explode('-', $value);
        $from = (float) $value[0];
        $to = isset($value[1]) ? (float) $value[1] : $from;

        return [$from, $to];
    }

    public static function implodeRangeValue($value)
    {
        return implode('-', $value);
    }

    public static function isBrightColor($color)
    {
        $hex_code = str_split(trim($color, '#'));
        if (count($hex_code) != 6) {
            $is_bright = false;
        } else {
            $r = hexdec($hex_code[0] . $hex_code[1]);
            $g = hexdec($hex_code[2] . $hex_code[3]);
            $b = hexdec($hex_code[4] . $hex_code[5]);
            $is_bright = $r + $g + $b > 700;
        }

        return $is_bright;
    }
}
