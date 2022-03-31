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

namespace WebPConvert\Convert\Converters;

use WebPConvert\Convert\Converters\AbstractConverter;
use WebPConvert\Convert\Converters\ConverterTraits\EncodingAutoTrait;

/**
 * Convert images to webp using Imagick extension.
 *
 * @package    WebPConvert
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since Release 2.0.0
 */
class Imagick extends AbstractConverter
{
    use EncodingAutoTrait;

    /**
     * Check operationality of Imagick converter.
     *
     * Note:
     * It may be that Gd has been compiled without jpeg support or png support.
     * We do not check for this here, as the converter could still be used for the other.
     *
     * @throws SystemRequirementsNotMetException  if system requirements are not met
     * @return void
     */
    public function checkOperationality()
    {
        if (!extension_loaded('imagick')) {
            return false;
        }

        if (!class_exists('\\Imagick')) {
            return false;
        }

        $im = new \Imagick();
        if (!in_array('WEBP', $im->queryFormats('WEBP'))) {
            return false;
        }
        return true;
    }

    /**
     * Check if specific file is convertable with current converter / converter settings.
     *
     * @throws SystemRequirementsNotMetException  if Imagick does not support image type
     */
    public function checkConvertability()
    {
        $im = new \Imagick();
        $mimeType = $this->getMimeTypeOfSource();
        switch ($mimeType) {
            case 'image/png':
                if (!in_array('PNG', $im->queryFormats('PNG'))) {
                    return false;
                }
                break;
            case 'image/jpeg':
                if (!in_array('JPEG', $im->queryFormats('JPEG'))) {
                    return false;
                }
                break;
        }
        return true;
    }

    /**
     *
     * It may also throw an ImagickException if imagick throws an exception
     * @throws CreateDestinationFileException if imageblob could not be saved to file
     */
    protected function doActualConvert()
    {
        /*
         * More about iMagick's WebP options:
         * - Inspect source code: https://github.com/ImageMagick/ImageMagick/blob/master/coders/webp.c#L559
         *      (search for "webp:")
         * - http://www.imagemagick.org/script/webp.php
         * - https://developers.google.com/speed/webp/docs/cwebp
         * - https://stackoverflow.com/questions/37711492/imagemagick-specific-webp-calls-in-php
         */

        $options = $this->options;

        // This might throw - we let it!
        $im = new \Imagick($this->source);

        $im->setImageFormat('WEBP');

        $im->setOption('webp:method', $options['method']);
        $im->setOption('webp:lossless', $options['encoding'] == 'lossless' ? 'true' : 'false');
        $im->setOption('webp:low-memory', $options['low-memory'] ? 'true' : 'false');
        $im->setOption('webp:alpha-quality', $options['alpha-quality']);

        if ($options['auto-filter'] === true) {
            $im->setOption('webp:auto-filter', 'true');
        }

        if ($options['metadata'] == 'none') {
            // Strip metadata and profiles
            $im->stripImage();
        }

        if ($this->isQualityDetectionRequiredButFailing()) {
        } else {
            $im->setImageCompressionQuality($this->getCalculatedQuality());
        }
        $imageBlob = $im->getImageBlob();

        $success = file_put_contents($this->destination, $imageBlob);

        if (!$success) {
            return false;
        }
        return true;
    }
}
