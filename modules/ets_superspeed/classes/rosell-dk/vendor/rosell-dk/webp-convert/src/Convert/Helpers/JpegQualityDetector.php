<?php
/**
 * 2007-2021 ETS-Soft
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses. 
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 * 
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please contact us for extra customization service at an affordable price
 *
 *  @author ETS-Soft <etssoft.jsc@gmail.com>
 *  @copyright  2007-2021 ETS-Soft
 *  @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

namespace WebPConvert\Convert\Helpers;

/**
 * Try to detect quality of a jpeg image using various tools.
 *
 * @package    WebPConvert
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since Release 2.0.0
 */
class JpegQualityDetector
{

    /**
     * Try to detect quality of jpeg using imagick extension
     *
     * @param  string  $filename  A complete file path to file to be examined
     * @return int|null  Quality, or null if it was not possible to detect quality
     */
    private static function detectQualityOfJpgUsingImagick($filename)
    {
        if (extension_loaded('imagick') && class_exists('\\Imagick')) {
            try {
                $img = new \Imagick($filename);

                // The required function is available as from PECL imagick v2.2.2
                if (method_exists($img, 'getImageCompressionQuality')) {
                    return $img->getImageCompressionQuality();
                }
            } catch (\Exception $e) {
                // Well well, it just didn't work out.
                // - But perhaps next method will work...
            }
        }
        return null;
    }


    /**
     * Try to detect quality of jpeg using imagick binary
     *
     * @param  string  $filename  A complete file path to file to be examined
     * @return int|null  Quality, or null if it was not possible to detect quality
     */
    private static function detectQualityOfJpgUsingImageMagick($filename)
    {
        if (function_exists('exec')) {
            // Try Imagick using exec, and routing stderr to stdout (the "2>$1" magic)
            exec("identify -format '%Q' " . escapeshellarg($filename) . " 2>&1", $output, $returnCode);
            //echo 'out:' . print_r($output, true);
            if ((intval($returnCode) == 0) && (is_array($output)) && (count($output) == 1)) {
                return intval($output[0]);
            }
        }
        return null;
    }


    /**
     * Try to detect quality of jpeg using gmagick binary
     *
     * @param  string  $filename  A complete file path to file to be examined
     * @return int|null  Quality, or null if it was not possible to detect quality
     */
    private static function detectQualityOfJpgUsingGraphicsMagick($filename)
    {
        if (function_exists('exec')) {
            // Try GraphicsMagick
            exec("gm identify -format '%Q' " . escapeshellarg($filename) . " 2>&1", $output, $returnCode);
            if ((intval($returnCode) == 0) && (is_array($output)) && (count($output) == 1)) {
                return intval($output[0]);
            }
        }
        return null;
    }


    /**
     * Try to detect quality of jpeg.
     *
     * Note: This method does not throw errors, but might dispatch warnings.
     * You can use the WarningsIntoExceptions class if it is critical to you that nothing gets "printed"
     *
     * @param  string  $filename  A complete file path to file to be examined
     * @return int|null  Quality, or null if it was not possible to detect quality
     */
    public static function detectQualityOfJpg($filename)
    {

        //trigger_error('warning test', E_USER_WARNING);

        // Test that file exists in order not to break things.
        if (!file_exists($filename)) {
            // One could argue that it would be better to throw an Exception...?
            return null;
        }

        // Try Imagick extension, if available
        $quality = self::detectQualityOfJpgUsingImagick($filename);

        if (is_null($quality)) {
            $quality = self::detectQualityOfJpgUsingImageMagick($filename);
        }

        if (is_null($quality)) {
            $quality = self::detectQualityOfJpgUsingGraphicsMagick($filename);
        }

        return $quality;
    }
}
