<?php
/**
 * 2019 ZH Media
 *
 * NOTICE OF LICENSE
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 * Do not resell or redistribute this file, either fully or partially.
 * Do not remove this comment containing author information and copyright.
 *
 * @author    Zack Hussain <me@zackhussain.ca>
 * @copyright 2019 ZH Media - All Rights Reserved
 */

namespace CanadaPostWs\Type\NcShipment;

class OptionsType
{
    /**
     * @var OptionType[]
     * name="option" type="OptionType" maxOccurs="20"
     */
    protected $options;

    /**
     * @return OptionType[]
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param OptionType[] $options
     * @return OptionsType
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @param OptionType $option
     * @return OptionsType
     */
    public function addOption($option)
    {
        $this->options[] = $option;

        return $this;
    }
}
