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

namespace WebPConvert\Convert\Converters\ConverterTraits;
use WebPConvert\Convert\Converters\AbstractConverter;

/**
 * Trait for converters that works by uploading to a cloud service.
 *
 * The trait adds a method for checking against upload limits.
 *
 * @package    WebPConvert
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since Release 2.0.0
 */
trait CurlTrait
{

    /**
     * Check basis operationality for converters relying on curl.
     *
     * Performs the same as ::checkOperationality(). It is here so converters that overrides the
     * ::checkOperationality() still has a chance to do the checks.
     *
     * @throws  SystemRequirementsNotMetException
     * @return  void
     */
    public function checkOperationalityForCurlTrait()
    {
        if (!extension_loaded('curl')) {
            throw new SystemRequirementsNotMetException('Required cURL extension is not available.');
        }

        if (!function_exists('curl_init')) {
            throw new SystemRequirementsNotMetException('Required url_init() function is not available.');
        }

        if (!function_exists('curl_file_create')) {
            throw new SystemRequirementsNotMetException(
                'Required curl_file_create() function is not available (requires PHP > 5.5).'
            );
        }
    }

    /**
     * Check basis operationality for converters relying on curl
     *
     * @throws  SystemRequirementsNotMetException
     * @return  void
     */
    public function checkOperationality()
    {
        $this->checkOperationalityForCurlTrait();
    }

    /**
     * Init curl.
     *
     * @throws  SystemRequirementsNotMetException  if curl could not be initialized
     * @return  resource  curl handle
     */
    protected static function initCurl()
    {
        // Get curl handle
        $ch = curl_init();
        if ($ch === false) {
            throw new SystemRequirementsNotMetException('Could not initialise cURL.');
        }
        return $ch;
    }
}
