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

/**
 * Functions for sanitizing.
 *
 * @package    WebPConvert
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since Release 2.0.6
 */
class PathChecker
{

     /**
      * Check absolute file path to prevent attacks.
      *
      * - Prevents non printable characters
      * - Prevents stream wrappers
      * - Prevents directory traversal
      *
      * Preventing non printable characters is especially done to prevent the NUL character, which can be used
      * to bypass other tests. See https://st-g.de/2011/04/doing-filename-checks-securely-in-PHP.
      *
      * Preventeng stream wrappers is especially done to protect against Phar Deserialization.
      * See https://blog.ripstech.com/2018/new-php-exploitation-technique/
      *
      * @param  string  $absFilePath
      * @return string  sanitized file path
      */
    public static function checkAbsolutePath($absFilePath, $text = 'file')
    {
        if (empty($absFilePath)) {
            throw new InvalidInputException('Empty filepath for ' . $text);
        }

        // Prevent non printable characters
        /*
        if (!ctype_print($absFilePath)) {
            throw new InvalidInputException('Non-printable characters are not allowed in ' . $text);
        }*/

        // Prevent control characters (at least the first 32 (#0 - #1f)
        if (preg_match('#[\x{0}-\x{1f}]#', $absFilePath)) {
            throw new InvalidInputException('Non-printable characters are not allowed');
        }

        // Prevent directory traversal
        if (preg_match('#\.\.\/#', $absFilePath)) {
            throw new InvalidInputException('Directory traversal is not allowed in ' . $text . ' path');
        }

        // Prevent stream wrappers ("phar://", "php://" and the like)
        // https://www.php.net/manual/en/wrappers.phar.php
        if (preg_match('#^\\w+://#', $absFilePath)) {
            throw new InvalidInputException('Stream wrappers are not allowed in ' . $text . ' path');
        }
    }

    public static function checkAbsolutePathAndExists($absFilePath, $text = 'file')
    {
        if (empty($absFilePath)) {
            throw new TargetNotFoundException($text . ' argument missing');
        }
        self::checkAbsolutePath($absFilePath, $text);
        if (@!file_exists($absFilePath)) {
            throw new TargetNotFoundException($text . ' file was not found');
        }
        if (@is_dir($absFilePath)) {
            throw new InvalidInputException($text . ' is a directory');
        }
    }

    /**
     *  Checks that source path is secure, file exists and it is not a dir.
     *
     *  To also check mime type, use InputValidator::checkSource
     */
    public static function checkSourcePath($source)
    {
        self::checkAbsolutePathAndExists($source, 'source');
    }

    public static function checkDestinationPath($destination)
    {
        if (empty($destination)) {
            throw new InvalidInputException('Destination argument missing');
        }
        self::checkAbsolutePath($destination, 'destination');
        if (@is_dir($destination)) {
            throw new InvalidInputException('Destination is a directory');
        }
    }

    public static function checkSourceAndDestinationPaths($source, $destination)
    {
        self::checkSourcePath($source);
        self::checkDestinationPath($destination);
    }
}
