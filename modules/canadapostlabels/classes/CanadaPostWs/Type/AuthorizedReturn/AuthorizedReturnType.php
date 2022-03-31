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

namespace CanadaPostWs\Type\AuthorizedReturn;

use CanadaPostWs\Type\Shipment\PrintPreferencesType;
use CanadaPostWs\Type\Shipment\SettlementInfoType;
use CanadaPostWs\Type\Shipment\ReferencesType;
use CanadaPostWs\Type\Shipment\NotificationType;
use CanadaPostWs\Type\Shipment\NotificationsType;
use CanadaPostWs\Type\Shipment\ParcelCharacteristicsType;
use CanadaPostWs\Type\Shipment\ReturnRecipientType;

class AuthorizedReturnType
{

    /**
     * @var string
     */
    protected $serviceCode;

    /**
     * @var ReturnRecipientType
     */
    protected $returner;

    /**
     * @var ReturnRecipientType
     */
    protected $receiver;

    /**
     * @var ParcelCharacteristicsType
     */
    protected $parcelCharacteristics;

    /**
     * @var PrintPreferencesType
     */
    protected $printPreferences;

    /**
     * @var SettlementInfoType
     */
    protected $settlementInfo;

    /**
     * @var ReferencesType
     */
    protected $references;

    /**
     * @var NotificationsType
     */
    protected $notifications = array();


    /**
     * @return string
     */
    public function getServiceCode()
    {
        return $this->serviceCode;
    }

    /**
     * @param string $serviceCode
     */
    public function setServiceCode($serviceCode)
    {
        $this->serviceCode = $serviceCode;
    }

    /**
     * @return ReturnRecipientType
     */
    public function getReturner()
    {
        return $this->returner;
    }

    /**
     * @param ReturnRecipientType $returner
     */
    public function setReturner($returner)
    {
        $this->returner = $returner;
    }

    /**
     * @return ReturnRecipientType
     */
    public function getReceiver()
    {
        return $this->receiver;
    }

    /**
     * @param ReturnRecipientType $receiver
     */
    public function setReceiver($receiver)
    {
        $this->receiver = $receiver;
    }

    /**
     * @return ParcelCharacteristicsType
     */
    public function getParcelCharacteristics()
    {
        return $this->parcelCharacteristics;
    }

    /**
     * @param ParcelCharacteristicsType $parcelCharacteristics
     */
    public function setParcelCharacteristics($parcelCharacteristics)
    {
        $this->parcelCharacteristics = $parcelCharacteristics;
    }

    /**
     * @return PrintPreferencesType
     */
    public function getPrintPreferences()
    {
        return $this->printPreferences;
    }

    /**
     * @param PrintPreferencesType $printPreferences
     */
    public function setPrintPreferences($printPreferences)
    {
        $this->printPreferences = $printPreferences;
    }

    /**
     * @return SettlementInfoType
     */
    public function getSettlementInfo()
    {
        return $this->settlementInfo;
    }

    /**
     * @param SettlementInfoType $settlementInfo
     */
    public function setSettlementInfo($settlementInfo)
    {
        $this->settlementInfo = $settlementInfo;
    }

    /**
     * @return ReferencesType
     */
    public function getReferences()
    {
        return $this->references;
    }

    /**
     * @param ReferencesType $references
     */
    public function setReferences($references)
    {
        $this->references = $references;
    }

    /**
     * @return NotificationsType
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * @param NotificationsType $notification
     */
    public function setNotifications($notifications)
    {
        $this->notifications = $notifications;
    }

    /**
     * @param NotificationType $notification
     */
    public function addNotification($notification)
    {
        $this->notifications[] = $notification;
    }
}
