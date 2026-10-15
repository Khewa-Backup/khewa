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

namespace CanadaPostWs\Type\Manifest;

use CanadaPostWs\Type\Common\LinkType;

class ManifestDetailsType
{
    /**
     * @var string
     * name="po-number" type="PoNumberType"
     */
    protected $poNumber;

    /**
     * @var ManifestAddressType
     * name="address-details" type="AddressDetailsType"
     */
    protected $manifestAddressType;

    /**
     * @var ManifestPricingInfoType
     * name="manifest-pricing-info" type="ManifestPricingInfoType"
     */
    protected $manifestPricingInfoType;

    protected $finalShippingPoint;

    protected $shippingPointName;

    protected $shippingPointId;

    protected $mailedByCustomer;

    protected $mailedOnBehalfOf;

    protected $paidByCustomer;

    protected $manifestDate;

    protected $manifestTime;

    protected $contractId;

    protected $methodOfPayment;

    /**
     * @return ManifestAddressType
     */
    public function getManifestAddressType()
    {
        return $this->manifestAddressType;
    }

    /**
     * @param ManifestAddressType $manifestAddressType
     */
    public function setManifestAddressType($manifestAddressType)
    {
        $this->manifestAddressType = $manifestAddressType;
    }

    /**
     * @return ManifestPricingInfoType
     */
    public function getManifestPricingInfoType()
    {
        return $this->manifestPricingInfoType;
    }

    /**
     * @param ManifestPricingInfoType $manifestPricingInfoType
     */
    public function setManifestPricingInfoType($manifestPricingInfoType)
    {
        $this->manifestPricingInfoType = $manifestPricingInfoType;
    }


    /**
     * @return mixed
     */
    public function getFinalShippingPoint()
    {
        return $this->finalShippingPoint;
    }

    /**
     * @param mixed $finalShippingPoint
     */
    public function setFinalShippingPoint($finalShippingPoint)
    {
        $this->finalShippingPoint = $finalShippingPoint;
    }

    /**
     * @return mixed
     */
    public function getShippingPointName()
    {
        return $this->shippingPointName;
    }

    /**
     * @param mixed $shippingPointName
     */
    public function setShippingPointName($shippingPointName)
    {
        $this->shippingPointName = $shippingPointName;
    }

    /**
     * @return mixed
     */
    public function getShippingPointId()
    {
        return $this->shippingPointId;
    }

    /**
     * @param mixed $shippingPointId
     */
    public function setShippingPointId($shippingPointId)
    {
        $this->shippingPointId = $shippingPointId;
    }

    /**
     * @return mixed
     */
    public function getMailedByCustomer()
    {
        return $this->mailedByCustomer;
    }

    /**
     * @param mixed $mailedByCustomer
     */
    public function setMailedByCustomer($mailedByCustomer)
    {
        $this->mailedByCustomer = $mailedByCustomer;
    }

    /**
     * @return mixed
     */
    public function getMailedOnBehalfOf()
    {
        return $this->mailedOnBehalfOf;
    }

    /**
     * @param mixed $mailedOnBehalfOf
     */
    public function setMailedOnBehalfOf($mailedOnBehalfOf)
    {
        $this->mailedOnBehalfOf = $mailedOnBehalfOf;
    }

    /**
     * @return mixed
     */
    public function getPaidByCustomer()
    {
        return $this->paidByCustomer;
    }

    /**
     * @param mixed $paidByCustomer
     */
    public function setPaidByCustomer($paidByCustomer)
    {
        $this->paidByCustomer = $paidByCustomer;
    }

    /**
     * @return mixed
     */
    public function getManifestDate()
    {
        return $this->manifestDate;
    }

    /**
     * @param mixed $manifestDate
     */
    public function setManifestDate($manifestDate)
    {
        $this->manifestDate = $manifestDate;
    }

    /**
     * @return mixed
     */
    public function getManifestTime()
    {
        return $this->manifestTime;
    }

    /**
     * @param mixed $manifestTime
     */
    public function setManifestTime($manifestTime)
    {
        $this->manifestTime = $manifestTime;
    }

    /**
     * @return mixed
     */
    public function getContractId()
    {
        return $this->contractId;
    }

    /**
     * @param mixed $contractId
     */
    public function setContractId($contractId)
    {
        $this->contractId = $contractId;
    }

    /**
     * @return mixed
     */
    public function getMethodOfPayment()
    {
        return $this->methodOfPayment;
    }

    /**
     * @param mixed $methodOfPayment
     */
    public function setMethodOfPayment($methodOfPayment)
    {
        $this->methodOfPayment = $methodOfPayment;
    }

    /**
     * @return string
     */
    public function getPoNumber()
    {
        return $this->poNumber;
    }

    /**
     * @param string $poNumber
     * @return ManifestType
     */
    public function setPoNumber($poNumber)
    {
        $this->poNumber = $poNumber;

        return $this;
    }
}
