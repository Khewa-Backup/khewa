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

class SignificantEventItemType
{
    
    /**
     * @var string
     */
    protected $eventIdentifier;
    
    /**
     * @var string
     */
    protected $eventDate;
    
    /**
     * @var string
     */
    protected $eventTime;
    
    /**
     * @var string
     */
    protected $eventTimeZone;
    
    /**
     * @var string
     */
    protected $eventDescription;
    
    /**
     * @var string
     */
    protected $signatoryName;
    
    /**
     * @var string
     */
    protected $eventSite;
    
    /**
     * @var string
     */
    protected $eventProvince;
    
    /**
     * @var string
     */
    protected $eventRetailLocationId;
    
    /**
     * @var string
     */
    protected $eventRetailName;

    /**
     * @return string
     */
    public function getEventIdentifier()
    {
        return $this->eventIdentifier;
    }

    /**
     * @param string $eventIdentifier
     */
    public function setEventIdentifier($eventIdentifier)
    {
        $this->eventIdentifier = $eventIdentifier;
    }

    /**
     * @return string
     */
    public function getEventDate()
    {
        return $this->eventDate;
    }

    /**
     * @param string $eventDate
     */
    public function setEventDate($eventDate)
    {
        $this->eventDate = $eventDate;
    }

    /**
     * @return string
     */
    public function getEventTime()
    {
        return $this->eventTime;
    }

    /**
     * @param string $eventTime
     */
    public function setEventTime($eventTime)
    {
        $this->eventTime = $eventTime;
    }

    /**
     * @return string
     */
    public function getEventTimeZone()
    {
        return $this->eventTimeZone;
    }

    /**
     * @param string $eventTimeZone
     */
    public function setEventTimeZone($eventTimeZone)
    {
        $this->eventTimeZone = $eventTimeZone;
    }

    /**
     * @return string
     */
    public function getEventDescription()
    {
        return $this->eventDescription;
    }

    /**
     * @param string $eventDescription
     */
    public function setEventDescription($eventDescription)
    {
        $this->eventDescription = $eventDescription;
    }

    /**
     * @return string
     */
    public function getSignatoryName()
    {
        return $this->signatoryName;
    }

    /**
     * @param string $signatoryName
     */
    public function setSignatoryName($signatoryName)
    {
        $this->signatoryName = $signatoryName;
    }

    /**
     * @return string
     */
    public function getEventSite()
    {
        return $this->eventSite;
    }

    /**
     * @param string $eventSite
     */
    public function setEventSite($eventSite)
    {
        $this->eventSite = $eventSite;
    }

    /**
     * @return string
     */
    public function getEventProvince()
    {
        return $this->eventProvince;
    }

    /**
     * @param string $eventProvince
     */
    public function setEventProvince($eventProvince)
    {
        $this->eventProvince = $eventProvince;
    }

    /**
     * @return string
     */
    public function getEventRetailLocationId()
    {
        return $this->eventRetailLocationId;
    }

    /**
     * @param string $eventRetailLocationId
     */
    public function setEventRetailLocationId($eventRetailLocationId)
    {
        $this->eventRetailLocationId = $eventRetailLocationId;
    }

    /**
     * @return string
     */
    public function getEventRetailName()
    {
        return $this->eventRetailName;
    }

    /**
     * @param string $eventRetailName
     */
    public function setEventRetailName($eventRetailName)
    {
        $this->eventRetailName = $eventRetailName;
    }
}
