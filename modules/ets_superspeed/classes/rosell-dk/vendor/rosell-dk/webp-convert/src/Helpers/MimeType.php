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

namespace WebPConvert\Helpers;

use ImageMimeTypeGuesser\ImageMimeTypeGuesser;

/**
 * Get MimeType, results cached by path.
 *
 * @package    WebPConvert
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since Release 2.0.6
 */
class MimeType
{
    private static $cachedDetections = [];

    /**
     * Get mime type for image (best guess).
     *
     * It falls back to using file extension. If that fails too, false is returned
     *
     * @return  string|false|null mimetype (if it is an image, and type could be determined / guessed),
     *    false (if it is not an image type that the server knowns about)
     *    or null (if nothing can be determined)
     */
    public static function getMimeTypeDetectionResult($absFilePath)
    {
        PathChecker::checkAbsolutePathAndExists($absFilePath);

        if (isset(self::$cachedDetections[$absFilePath])) {
            return self::$cachedDetections[$absFilePath];
        }
        $cachedDetections[$absFilePath] = ImageMimeTypeGuesser::lenientGuess($absFilePath);
        return $cachedDetections[$absFilePath];
    }
}
