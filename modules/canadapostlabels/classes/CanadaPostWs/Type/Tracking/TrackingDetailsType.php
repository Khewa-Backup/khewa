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

use CanadaPostWs\Type\Shipment\OptionsType;
use CanadaPostWs\Type\Shipment\OptionType;

class TrackingDetailsType
{
    protected $pin;
    protected $activeExists;
    protected $archiveExists;
    protected $changedExpectedDate;
    protected $destinationPostalId;
    protected $duplicateFlagInd;
    protected $expectedDeliveryDate;
    protected $changedExpectedDeliveryReason;
    protected $mailedByCustomerNumber;
    protected $mailedOnBehalfOfCustomerNumber;
    protected $originalPin;
    protected $serviceName;
    protected $serviceName2;
    protected $customerRef1;
    protected $customerRef2;
    protected $returnPin;
    protected $signatureImageExists;
    protected $suppressSignature;

    protected $deliveryOptions = array();

    protected $significantEvents = array();

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
    public function getActiveExists()
    {
        return $this->activeExists;
    }

    /**
     * @param mixed $activeExists
     */
    public function setActiveExists($activeExists)
    {
        $this->activeExists = $activeExists;
    }

    /**
     * @return mixed
     */
    public function getArchiveExists()
    {
        return $this->archiveExists;
    }

    /**
     * @param mixed $archiveExists
     */
    public function setArchiveExists($archiveExists)
    {
        $this->archiveExists = $archiveExists;
    }

    /**
     * @return mixed
     */
    public function getChangedExpectedDate()
    {
        return $this->changedExpectedDate;
    }

    /**
     * @param mixed $changedExpectedDate
     */
    public function setChangedExpectedDate($changedExpectedDate)
    {
        $this->changedExpectedDate = $changedExpectedDate;
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
    public function getDuplicateFlagInd()
    {
        return $this->duplicateFlagInd;
    }

    /**
     * @param mixed $duplicateFlagInd
     */
    public function setDuplicateFlagInd($duplicateFlagInd)
    {
        $this->duplicateFlagInd = $duplicateFlagInd;
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
    public function getChangedExpectedDeliveryReason()
    {
        return $this->changedExpectedDeliveryReason;
    }

    /**
     * @param mixed $changedExpectedDeliveryReason
     */
    public function setChangedExpectedDeliveryReason($changedExpectedDeliveryReason)
    {
        $this->changedExpectedDeliveryReason = $changedExpectedDeliveryReason;
    }

    /**
     * @return mixed
     */
    public function getMailedByCustomerNumber()
    {
        return $this->mailedByCustomerNumber;
    }

    /**
     * @param mixed $mailedByCustomerNumber
     */
    public function setMailedByCustomerNumber($mailedByCustomerNumber)
    {
        $this->mailedByCustomerNumber = $mailedByCustomerNumber;
    }

    /**
     * @return mixed
     */
    public function getMailedOnBehalfOfCustomerNumber()
    {
        return $this->mailedOnBehalfOfCustomerNumber;
    }

    /**
     * @param mixed $mailedOnBehalfOfCustomerNumber
     */
    public function setMailedOnBehalfOfCustomerNumber($mailedOnBehalfOfCustomerNumber)
    {
        $this->mailedOnBehalfOfCustomerNumber = $mailedOnBehalfOfCustomerNumber;
    }

    /**
     * @return mixed
     */
    public function getOriginalPin()
    {
        return $this->originalPin;
    }

    /**
     * @param mixed $originalPin
     */
    public function setOriginalPin($originalPin)
    {
        $this->originalPin = $originalPin;
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
    public function getServiceName2()
    {
        return $this->serviceName2;
    }

    /**
     * @param mixed $serviceName2
     */
    public function setServiceName2($serviceName2)
    {
        $this->serviceName2 = $serviceName2;
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
    public function getSignatureImageExists()
    {
        return $this->signatureImageExists;
    }

    /**
     * @param mixed $signatureImageExists
     */
    public function setSignatureImageExists($signatureImageExists)
    {
        $this->signatureImageExists = $signatureImageExists;
    }

    /**
     * @return mixed
     */
    public function getSuppressSignature()
    {
        return $this->suppressSignature;
    }

    /**
     * @param mixed $suppressSignature
     */
    public function setSuppressSignature($suppressSignature)
    {
        $this->suppressSignature = $suppressSignature;
    }

    /**
     * @return array
     */
    public function getDeliveryOptions()
    {
        return $this->deliveryOptions;
    }

    /**
     * @param array $deliveryOptions
     */
    public function setDeliveryOptions($deliveryOptions)
    {
        $this->deliveryOptions = $deliveryOptions;
    }

    /**
     * @param DeliveryOptionItemType $option
     * @return TrackingDetailsType
     */
    public function addDeliveryOption($option)
    {
        $this->deliveryOptions[] = $option;

        return $this;
    }

    /**
     * @return array
     */
    public function getSignificantEvents()
    {
        return $this->significantEvents;
    }

    /**
     * @param array $significantEvents
     */
    public function setSignificantEvents($significantEvents)
    {
        $this->significantEvents = $significantEvents;
    }

    /**
     * @param SignificantEventItemType $event
     * @return TrackingDetailsType
     */
    public function addEvent($event)
    {
        $this->significantEvents[] = $event;

        return $this;
    }
}
