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

class ReturnSpecType
{
    /**
     * @var string
     * name="service-code" type="ServiceCodeType"
     */
    protected $serviceCode;

    /**
     * @var ReturnRecipientType
     * name="return-recipient" type="ReturnRecipientType"
     */
    protected $returnRecipient;

    /**
     * @var string
     * name="return-notification" type="EmailType" minOccurs="0"
     */
    protected $returnNotification;

    /**
     * @return string
     */
    public function getServiceCode()
    {
        return $this->serviceCode;
    }

    /**
     * @param string $serviceCode
     * @return ReturnSpecType
     */
    public function setServiceCode($serviceCode)
    {
        $this->serviceCode = $serviceCode;

        return $this;
    }

    /**
     * @return ReturnRecipientType
     */
    public function getReturnRecipient()
    {
        return $this->returnRecipient;
    }

    /**
     * @param ReturnRecipientType $returnRecipient
     * @return ReturnSpecType
     */
    public function setReturnRecipient($returnRecipient)
    {
        $this->returnRecipient = $returnRecipient;

        return $this;
    }

    /**
     * @return string
     */
    public function getReturnNotification()
    {
        return $this->returnNotification;
    }

    /**
     * @param string $returnNotification
     * @return ReturnSpecType
     */
    public function setReturnNotification($returnNotification)
    {
        $this->returnNotification = $returnNotification;

        return $this;
    }
}
