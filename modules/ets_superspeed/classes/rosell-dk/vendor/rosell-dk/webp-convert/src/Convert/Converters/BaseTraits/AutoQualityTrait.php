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

namespace WebPConvert\Convert\Converters\BaseTraits;

use WebPConvert\Convert\Helpers\JpegQualityDetector;

/**
 * Trait for handling the "quality:auto" option.
 *
 * This trait is only used in the AbstractConverter class. It has been extracted into a
 * trait in order to bundle the methods concerning auto quality.
 *
 * @package    WebPConvert
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since Release 2.0.0
 */
trait AutoQualityTrait
{
    abstract public function getMimeTypeOfSource();

    /** @var boolean  Whether the quality option has been processed or not */
    private $processed = false;

    /** @var boolean  Whether the quality of the source could be detected or not (set upon processing) */
    private $qualityCouldNotBeDetected = false;

    /** @var integer  The calculated quality (set upon processing - on successful detection) */
    private $calculatedQuality;


    /**
     *  Determine if quality detection is required but failing.
     *
     *  It is considered "required" when:
     *  - Mime type is "image/jpeg"
     *  - Quality is set to "auto"
     *
     *  If quality option hasn't been proccessed yet, it is triggered.
     *
     *  @return  boolean
     */
    public function isQualityDetectionRequiredButFailing()
    {
        $this->processQualityOptionIfNotAlready();
        return $this->qualityCouldNotBeDetected;
    }

    /**
     * Get calculated quality.
     *
     * If the "quality" option is a number, that number is returned.
     * If mime type of source is something else than "image/jpeg", the "default-quality" option is returned
     * If quality is "auto" and source is a jpeg image, it will be attempted to detect jpeg quality.
     * In case of failure, the value of the "default-quality" option is returned.
     * In case of success, the detected quality is returned, or the value of the "max-quality" if that is lower.
     *
     *  @return  int
     */
    public function getCalculatedQuality()
    {
        $this->processQualityOptionIfNotAlready();
        return $this->calculatedQuality;
    }

    /**
     * Process the quality option if it is not already processed.
     *
     * @return void
     */
    private function processQualityOptionIfNotAlready()
    {
        if (!$this->processed) {
            $this->processed = true;
            $this->processQualityOption();
        }
    }

    /**
     * Process the quality option.
     *
     * Sets the private property "calculatedQuality" according to the description for the getCalculatedQuality
     * function.
     * In case quality detection was attempted and failed, the private property "qualityCouldNotBeDetected" is set
     * to true. This is used by the "isQualityDetectionRequiredButFailing" (and documented there too).
     *
     * @return void
     */
    private function processQualityOption()
    {
        $options = $this->options;
        $source = $this->source;

        $q = $options['quality'];
        if ($q == 'auto') {
            if (($this->getMimeTypeOfSource() == 'image/jpeg')) {
                $q = JpegQualityDetector::detectQualityOfJpg($source);
                if (is_null($q)) {
                    $q = $options['default-quality'];
                    $this->qualityCouldNotBeDetected = true;
                }
                $q = min($q, $options['max-quality']);
            }
        }
        $this->calculatedQuality = $q;
    }
}
