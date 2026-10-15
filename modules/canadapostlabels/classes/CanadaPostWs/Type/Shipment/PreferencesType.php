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

namespace CanadaPostWs\Type\Shipment;

class PreferencesType
{
    /**
     * @var bool
     * name="show-packing-instructions" type="xsd:boolean"
     */
    protected $showPackingInstructions;

    /**
     * @var bool
     * name="show-postage-rate" type="xsd:boolean" minOccurs="0"
     */
    protected $showPostageRate;

    /**
     * @var bool
     * name="show-insured-value" type="xsd:boolean" minOccurs="0"
     */
    protected $showInsuredValue;

    /**
     * @return boolean
     */
    public function isShowPackingInstructions()
    {
        return $this->showPackingInstructions;
    }

    /**
     * @param boolean $showPackingInstructions
     * @return PreferencesType
     */
    public function setShowPackingInstructions($showPackingInstructions)
    {
        $this->showPackingInstructions = $showPackingInstructions;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isShowPostageRate()
    {
        return $this->showPostageRate;
    }

    /**
     * @param boolean $showPostageRate
     * @return PreferencesType
     */
    public function setShowPostageRate($showPostageRate)
    {
        $this->showPostageRate = $showPostageRate;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isShowInsuredValue()
    {
        return $this->showInsuredValue;
    }

    /**
     * @param boolean $showInsuredValue
     * @return PreferencesType
     */
    public function setShowInsuredValue($showInsuredValue)
    {
        $this->showInsuredValue = $showInsuredValue;

        return $this;
    }
}
