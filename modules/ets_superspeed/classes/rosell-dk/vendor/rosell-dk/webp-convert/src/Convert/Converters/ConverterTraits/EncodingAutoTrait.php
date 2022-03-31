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

/**
 * Trait for converters that supports lossless encoding and thus the "lossless:auto" option.
 *
 * @package    WebPConvert
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since Release 2.0.0
 */
trait EncodingAutoTrait
{

    abstract protected function doActualConvert();
    abstract public function getSource();
    abstract public function getDestination();
    abstract public function setDestination($destination);
    abstract protected function setOption($optionName, $optionValue);
    abstract protected function logReduction($source, $destination);

    public function supportsLossless()
    {
        return true;
    }

    /** Default is to not pass "lossless:auto" on, but implement it.
     *
     *  The Stack converter passes it on (it does not even use this trait)
     *  WPC currently implements it, but this might be configurable in the future.
     *
     */
    public function passOnEncodingAuto()
    {
        return false;
    }

    private function convertTwoAndSelectSmallest()
    {
        $destination = $this->getDestination();
        $destinationLossless =  $destination . '.lossless.webp';
        $destinationLossy =  $destination . '.lossy.webp';
        $this->setDestination($destinationLossy);
        $this->setOption('encoding', 'lossy');
        $this->doActualConvert();
        $this->logReduction($this->getSource(), $destinationLossy);
        $this->setDestination($destinationLossless);
        $this->setOption('encoding', 'lossless');
        $this->doActualConvert();
        $this->logReduction($this->getSource(), $destinationLossless);
        if (filesize($destinationLossless) > filesize($destinationLossy)) {
            unlink($destinationLossless);
            rename($destinationLossy, $destination);
        } else {
            unlink($destinationLossy);
            rename($destinationLossless, $destination);
        }
        $this->setDestination($destination);
        $this->setOption('encoding', 'auto');
    }

    protected function runActualConvert()
    {
        $this->doActualConvert();
    }
}
