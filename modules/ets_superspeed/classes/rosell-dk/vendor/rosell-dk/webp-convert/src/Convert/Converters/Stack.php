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

use WebPConvert\Convert\ConverterFactory;
use WebPConvert\Convert\Converters\AbstractConverter;

/**
 * Convert images to webp by trying a stack of converters until success.
 *
 * @package    WebPConvert
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since Release 2.0.0
 */
class Stack extends AbstractConverter
{
    /**
     * Get available converters (ids) - ordered by awesomeness.
     *
     * @return  array  An array of ids of converters that comes with this library
     */
    public static function getAvailableConverters()
    {
        return [
            'cwebp', 'vips', 'imagick', 'gmagick', 'imagemagick', 'graphicsmagick', 'wpc', 'ewww', 'gd'
        ];
    }

    /**
     * Check (general) operationality of imagack converter executable
     *
     * @throws SystemRequirementsNotMetException  if system requirements are not met
     */
    public function checkOperationality()
    {
        if (count($this->options['converters']) == 0) {
            return false;
        }else
            return true;
        
    }

    protected function doActualConvert()
    {
        $options = $this->options;
        $converters = $options['converters'];
        $defaultConverterOptions = array(
            'alpha-quality' => 50,
            '_skip_input_check' => 1,
            '_skip_input_check' => 1
        );
        foreach ($converters as $converter) {
            if (is_array($converter)) {
                $converterId = $converter['converter'];
                $converterOptions = isset($converter['options']) ? $converter['options'] : [];
            } else {
                $converterId = $converter;
                $converterOptions = [];
                if (isset($options['converter-options'][$converterId])) {
                    // Note: right now, converter-options are not meant to be used,
                    //       when you have several converters of the same type
                    $converterOptions = $options['converter-options'][$converterId];
                }
            }
            $converterOptions = array_merge($defaultConverterOptions, $converterOptions);
            $beginTime = microtime(true);
            $converter = ConverterFactory::makeConverter(
                $converterId,
                $this->source,
                $this->destination,
                $converterOptions
            );
            if($converter->doConvert())
                return true;
        }
        return false;
    }
}
