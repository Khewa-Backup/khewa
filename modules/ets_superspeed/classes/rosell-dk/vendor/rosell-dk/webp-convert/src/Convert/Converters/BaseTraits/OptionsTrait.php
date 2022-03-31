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

use WebPConvert\Convert\Converters\Stack;

/**
 * Trait for handling options
 *
 * This trait is currently only used in the AbstractConverter class. It has been extracted into a
 * trait in order to bundle the methods concerning options.
 *
 * @package    WebPConvert
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since Release 2.0.0
 */
trait OptionsTrait
{
    abstract protected function getMimeTypeOfSource();

    /** @var array  Provided conversion options */
    public $providedOptions;

    /** @var array  Calculated conversion options (merge of default options and provided options)*/
    protected $options;

    /** @var Options  */
    protected $options2;

    /**
     * Set "provided options" (options provided by the user when calling convert().
     *
     * This also calculates the protected options array, by merging in the default options, merging
     * jpeg and png options and merging prefixed options (such as 'vips-quality').
     * The resulting options array are set in the protected property $this->options and can be
     * retrieved using the public ::getOptions() function.
     *
     * @param   array $providedOptions (optional)
     * @return  void
     */
    public function setProvidedOptions($providedOptions = [])
    {
        $isPng = ($this->getMimeTypeOfSource() == 'image/png');
        $defaultOptions = array(
            'alpha-quality' => 50,
            'auto-filter' => null,
            'default-quality' => $isPng ? 85:75,
            'encoding' => 'auto',
            'low-memory' =>null, 
            'log-call-arguments' =>null, 
            'max-quality' => 85,
            'metadata' => 'none',
            'method' => 6,
            'near-lossless' => 60,
            'preset' => 'none',
            'quality' => $isPng ? 85 : 'auto',
            'size-in-percentage' =>null,
            'skip' =>null, 
            'use-nice' => null,
            'jpeg' => array(),
            'png' => array(), 
            'converters' => array('cwebp','vips','imagick','gmagick','imagemagick','graphicsmagick','gd'),
            'converter-options' => array(),
            'shuffle' => null,
            'preferred-converters' => array(),
            'extra-converters' => array(),
        );
        $this->options = array_merge($defaultOptions,$providedOptions);
        
    }

    /**
     * Change an option specifically.
     *
     * This method is probably rarely neeeded. We are using it to change the "encoding" option temporarily
     * in the EncodingAutoTrait.
     *
     * @param  string  $id      Id of option (ie "metadata")
     * @param  mixed   $value   The new value.
     * @return void
     */
    protected function setOption($id, $value)
    {
        $this->options[$id] = $value;
        $this->options2->setOrCreateOption($id, $value);
    }
}
