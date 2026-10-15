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

class TrackingSummaryType
{
    protected $pinSummary;

    /**
     * @return PinSummaryType
     */
    public function getPinSummary()
    {
        return $this->pinSummary;
    }

    /**
     * @param PinSummaryType $pinSummary
     */
    public function setPinSummary($pinSummary)
    {
        $this->pinSummary = $pinSummary;
    }
}
