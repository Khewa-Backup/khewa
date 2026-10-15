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

namespace CanadaPostWs\Type\Tracking;

class PinSummaryType
{
    protected $pin;
    protected $originPostalId;
    protected $destinationPostalId;
    protected $destinationProvince;
    protected $serviceName;
    protected $mailedOnDate;
    protected $expectedDeliveryDate;
    protected $actualDeliveryDate;
    protected $deliveryOptionCompletedInd;
    protected $eventDateTime;
    protected $eventDescription;
    protected $attemptedDate;
    protected $customerRef1;
    protected $customerRef2;
    protected $returnPin;
    protected $eventType;
    protected $eventLocation;
    protected $signatoryName;

    /**
     * @return mixed
     */
    public function getPin()
    {
        return $this->pin;
    }

    /**
     * @param mixed $pin
     */
    public function setPin($pin)
    {
        $this->pin = $pin;
    }

    /**
     * @return mixed
     */
    public function getOriginPostalId()
    {
        return $this->originPostalId;
    }

    /**
     * @param mixed $originPostalId
     */
    public function setOriginPostalId($originPostalId)
    {
        $this->originPostalId = $originPostalId;
    }

    /**
     * @return mixed
     */
    public function getDestinationPostalId()
    {
        return $this->destinationPostalId;
    }

    /**
     * @param mixed $destinationPostalId
     */
    public function setDestinationPostalId($destinationPostalId)
    {
        $this->destinationPostalId = $destinationPostalId;
    }

    /**
     * @return mixed
     */
    public function getDestinationProvince()
    {
        return $this->destinationProvince;
    }

    /**
     * @param mixed $destinationProvince
     */
    public function setDestinationProvince($destinationProvince)
    {
        $this->destinationProvince = $destinationProvince;
    }

    /**
     * @return mixed
     */
    public function getServiceName()
    {
        return $this->serviceName;
    }

    /**
     * @param mixed $serviceName
     */
    public function setServiceName($serviceName)
    {
        $this->serviceName = $serviceName;
    }

    /**
     * @return mixed
     */
    public function getMailedOnDate()
    {
        return $this->mailedOnDate;
    }

    /**
     * @param mixed $mailedOnDate
     */
    public function setMailedOnDate($mailedOnDate)
    {
        $this->mailedOnDate = $mailedOnDate;
    }

    /**
     * @return mixed
     */
    public function getExpectedDeliveryDate()
    {
        return $this->expectedDeliveryDate;
    }

    /**
     * @param mixed $expectedDeliveryDate
     */
    public function setExpectedDeliveryDate($expectedDeliveryDate)
    {
        $this->expectedDeliveryDate = $expectedDeliveryDate;
    }

    /**
     * @return mixed
     */
    public function getActualDeliveryDate()
    {
        return $this->actualDeliveryDate;
    }

    /**
     * @param mixed $actualDeliveryDate
     */
    public function setActualDeliveryDate($actualDeliveryDate)
    {
        $this->actualDeliveryDate = $actualDeliveryDate;
    }

    /**
     * @return mixed
     */
    public function getDeliveryOptionCompletedInd()
    {
        return $this->deliveryOptionCompletedInd;
    }

    /**
     * @param mixed $deliveryOptionCompletedInd
     */
    public function setDeliveryOptionCompletedInd($deliveryOptionCompletedInd)
    {
        $this->deliveryOptionCompletedInd = $deliveryOptionCompletedInd;
    }

    /**
     * @return mixed
     */
    public function getEventDateTime()
    {
        return $this->eventDateTime;
    }

    /**
     * @param mixed $eventDateTime
     */
    public function setEventDateTime($eventDateTime)
    {
        $this->eventDateTime = $eventDateTime;
    }

    /**
     * @return mixed
     */
    public function getEventDescription()
    {
        return $this->eventDescription;
    }

    /**
     * @param mixed $eventDescription
     */
    public function setEventDescription($eventDescription)
    {
        $this->eventDescription = $eventDescription;
    }

    /**
     * @return mixed
     */
    public function getAttemptedDate()
    {
        return $this->attemptedDate;
    }

    /**
     * @param mixed $attemptedDate
     */
    public function setAttemptedDate($attemptedDate)
    {
        $this->attemptedDate = $attemptedDate;
    }

    /**
     * @return mixed
     */
    public function getCustomerRef1()
    {
        return $this->customerRef1;
    }

    /**
     * @param mixed $customerRef1
     */
    public function setCustomerRef1($customerRef1)
    {
        $this->customerRef1 = $customerRef1;
    }

    /**
     * @return mixed
     */
    public function getCustomerRef2()
    {
        return $this->customerRef2;
    }

    /**
     * @param mixed $customerRef2
     */
    public function setCustomerRef2($customerRef2)
    {
        $this->customerRef2 = $customerRef2;
    }

    /**
     * @return mixed
     */
    public function getReturnPin()
    {
        return $this->returnPin;
    }

    /**
     * @param mixed $returnPin
     */
    public function setReturnPin($returnPin)
    {
        $this->returnPin = $returnPin;
    }

    /**
     * @return mixed
     */
    public function getEventType()
    {
        return $this->eventType;
    }

    /**
     * @param mixed $eventType
     */
    public function setEventType($eventType)
    {
        $this->eventType = $eventType;
    }

    /**
     * @return mixed
     */
    public function getEventLocation()
    {
        return $this->eventLocation;
    }

    /**
     * @param mixed $eventLocation
     */
    public function setEventLocation($eventLocation)
    {
        $this->eventLocation = $eventLocation;
    }

    /**
     * @return mixed
     */
    public function getSignatoryName()
    {
        return $this->signatoryName;
    }

    /**
     * @param mixed $signatoryName
     */
    public function setSignatoryName($signatoryName)
    {
        $this->signatoryName = $signatoryName;
    }
}
