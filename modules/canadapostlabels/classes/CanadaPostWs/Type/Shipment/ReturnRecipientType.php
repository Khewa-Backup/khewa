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

class ReturnRecipientType
{
    /**
     * @var string
     * name="name" type="ContactNameType" minOccurs="0"
     */
    protected $name;

    /**
     * @var string
     * name="company" type="CompanyNameType" minOccurs="0"
     */
    protected $company;

    /**
     * @var string
     * name="email" type="EmailType" minOccurs="0"
     */
    protected $email;

    /**
     * @var string
     * name="receiver-voice-number" type="ReceiverVoiceNumberType" minOccurs="0"
     */
    protected $receiverVoiceNumber;

    /**
     * @var DomesticAddressDetailsType
     * name="address-details" type="DomesticAddressDetailsType"
     */
    protected $addressDetails;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return ReturnRecipientType
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getReceiverVoiceNumber()
    {
        return $this->receiverVoiceNumber;
    }

    /**
     * @param string $receiverVoiceNumber
     */
    public function setReceiverVoiceNumber($receiverVoiceNumber)
    {
        $this->receiverVoiceNumber = $receiverVoiceNumber;

        return $this;
    }

    /**
     * @param string $company
     * @return ReturnRecipientType
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return DomesticAddressDetailsType
     */
    public function getAddressDetails()
    {
        return $this->addressDetails;
    }

    /**
     * @param DomesticAddressDetailsType $addressDetails
     * @return ReturnRecipientType
     */
    public function setAddressDetails($addressDetails)
    {
        $this->addressDetails = $addressDetails;

        return $this;
    }
}
