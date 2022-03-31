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

class ManifestAddressType
{
    /**
     * @var string
     * name="manifest-company"
     */
    protected $manifestCompany;

    /**
     * @var string
     * name="manifest-name" minOccurs="0"
     */
    protected $manifestName;

    /**
     * @var string
     * name="phone-number" type="PhoneNumberType"
     */
    protected $phoneNumber;

    /**
     * @var AddressDetailsType
     * name="address-details" type="AddressDetailsType"
     */
    protected $addressDetails;

    /**
     * @return string
     */
    public function getManifestCompany()
    {
        return $this->manifestCompany;
    }

    /**
     * @param string $manifestCompany
     * @return ManifestAddressType
     */
    public function setManifestCompany($manifestCompany)
    {
        $this->manifestCompany = $manifestCompany;

        return $this;
    }

    /**
     * @return string
     */
    public function getManifestName()
    {
        return $this->manifestName;
    }

    /**
     * @param string $manifestName
     * @return ManifestAddressType
     */
    public function setManifestName($manifestName)
    {
        $this->manifestName = $manifestName;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param string $phoneNumber
     * @return ManifestAddressType
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * @return AddressDetailsType
     */
    public function getAddressDetails()
    {
        return $this->addressDetails;
    }

    /**
     * @param AddressDetailsType $addressDetails
     * @return ManifestAddressType
     */
    public function setAddressDetails($addressDetails)
    {
        $this->addressDetails = $addressDetails;

        return $this;
    }
}
