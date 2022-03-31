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

class SignificantEventsType
{
    /**
     * @var SignificantEventsType[]
     * name="item" type="SignificantEventsType" maxOccurs="20"
     */
    protected $significantEvents = array();

    /**
     * @return SignificantEventsType[]
     */
    public function getSignificantEvents()
    {
        return $this->significantEvents;
    }

    /**
     * @param SignificantEventsType[] $significantEvents
     */
    public function setSignificantEvents($significantEvents)
    {
        $this->significantEvents = $significantEvents;
    }
}
