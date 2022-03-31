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

class QuoteType
{
    /**
     * @var string
     * name="service-code" type="xsd:normalizedString"
     */
    protected $serviceCode;

    /**
     * @var string
     * name="base" type="xsd:normalizedString"
     */
    protected $priceTaxExcl;

    /**
     * @var string
     * name="due" type="xsd:normalizedString"
     */
    protected $priceTaxIncl;

    /**
     * @var string
     * name="expected-transit-time" type="xsd:normalizedString"
     */
    protected $transitTime;

    /**
     * @var string
     * name="expected-delivery-date" type="xsd:normalizedString"
     */
    protected $deliveryDate;

    /**
     * @return string
     */
    public function getServiceCode()
    {
        return $this->serviceCode;
    }

    /**
     * @param string $code
     * @return QuoteType
     */
    public function setServiceCode($code)
    {
        $this->serviceCode = $code;

        return $this;
    }

    /**
     * @return string
     */
    public function getPriceTaxExcl()
    {
        return $this->priceTaxExcl;
    }

    /**
     * @return string
     */
    public function getDeliveryDate()
    {
        return $this->deliveryDate;
    }

    /**
     * @return string
     */
    public function getTransitTime()
    {
        return $this->transitTime;
    }

    /**
     * @param string $priceTaxExcl
     * @return QuoteType
     */
    public function setPriceTaxExcl($priceTaxExcl)
    {
        $this->priceTaxExcl = $priceTaxExcl;

        return $this;
    }

    /**
     * @return string
     */
    public function getPriceTaxIncl()
    {
        return $this->priceTaxIncl;
    }

    /**
     * @param string $priceTaxIncl
     * @return QuoteType
     */
    public function setPriceTaxIncl($priceTaxIncl)
    {
        $this->priceTaxIncl = $priceTaxIncl;

        return $this;
    }

    /**
     * @param string $deliveryDate
     * @return QuoteType
     */
    public function setDeliveryDate($deliveryDate)
    {
        $this->deliveryDate = $deliveryDate;

        return $this;
    }

    /**
     * @param string $transitTime
     * @return QuoteType
     */
    public function setTransitTime($transitTime)
    {
        $this->transitTime = $transitTime;

        return $this;
    }
}
