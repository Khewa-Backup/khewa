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

namespace CanadaPostWs\Type\Rating;

class OptionInfoType
{
    /**
     * @var string
     * name="option-code"
     */
    protected $optionCode;

    /**
     * @var float
     * name="option-amount" type="CostTypeNonZero" minOccurs="0"
     */
    protected $optionAmount;

    protected $mandatory;

    protected $qualifierRequired;

    protected $qualifierMax;

    /**
     * @return string
     */
    public function getOptionCode()
    {
        return $this->optionCode;
    }

    /**
     * @param string $optionCode
     */
    public function setOptionCode($optionCode)
    {
        $this->optionCode = $optionCode;
    }

    /**
     * @return float
     */
    public function getOptionAmount()
    {
        return $this->optionAmount;
    }

    /**
     * @param float $optionAmount
     */
    public function setOptionAmount($optionAmount)
    {
        $this->optionAmount = $optionAmount;
    }

    /**
     * @return mixed
     */
    public function getMandatory()
    {
        return $this->mandatory;
    }

    /**
     * @param mixed $mandatory
     */
    public function setMandatory($mandatory)
    {
        $this->mandatory = $mandatory;
    }

    /**
     * @return mixed
     */
    public function getQualifierRequired()
    {
        return $this->qualifierRequired;
    }

    /**
     * @param mixed $qualifierRequired
     */
    public function setQualifierRequired($qualifierRequired)
    {
        $this->qualifierRequired = $qualifierRequired;
    }

    /**
     * @return mixed
     */
    public function getQualifierMax()
    {
        return $this->qualifierMax;
    }

    /**
     * @param mixed $qualifierMax
     */
    public function setQualifierMax($qualifierMax)
    {
        $this->qualifierMax = $qualifierMax;
    }
}
