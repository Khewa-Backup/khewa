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

class ShipmentRefundInfoType
{
    /**
     * @var string
     * name="serviceTicketDate" type="$serviceTicketDateType"
     */
    protected $serviceTicketDate;
    /**
     * @var string
     * name="$serviceTicketId" type="$serviceTicketIdType"
     */
    protected $serviceTicketId;

    /**
     * @return string
     */
    public function getServiceTicketDate()
    {
        return $this->serviceTicketDate;
    }

    /**
     * @param string $email
     * @return ShipmentRefundInfoType
     */
    public function setServiceTicketDate($date)
    {
        $this->serviceTicketDate = $date;

        return $this;
    }
    /**
     * @return string
     */
    public function getServiceTicketId()
    {
        return $this->serviceTicketId;
    }

    /**
     * @param string $email
     * @return ShipmentRefundInfoType
     */
    public function setServiceTicketId($date)
    {
        $this->serviceTicketId = $date;

        return $this;
    }
}
