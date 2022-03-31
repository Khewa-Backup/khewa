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

use CanadaPostWs\Type\Common\LinkType;

class ShipmentInfoType
{
    /**
     * @var string
     * name="shipment-id" type="ShipmentIDType"
     */
    protected $shipmentId;

    /**
     * @var string
     * name="shipment-status" type="ShipmentStatusType"
     */
    protected $shipmentStatus;

    /**
     * @var string
     * name="tracking-pin" type="TrackingPINType" minOccurs="0"
     */
    protected $trackingPin;

    /**
     * @var string
     * name="return-tracking-pin" type="TrackingPINType" minOccurs="0"
     */
    protected $returnTrackingPin;

    /**
     * @var string
     * name="final-shipping-point" minOccurs="0"
     */
    protected $finalShippingPoint;

    /**
     * @var DeliverySpecType
     * name="delivery-spec" type="DeliverySpecType"
     */
    protected $deliverySpec;

    /**
     * @var string
     * name="po-number" type="PoNumberType" minOccurs="0"
     */
    protected $poNumber;

    /*
     * @var LinkType[]
     * */
    protected $selfLink;

    /*
     * @var LinkType[]
     * */
    protected $receiptLink;

    /*
     * @var LinkType[]
     * */
    protected $labelLink;

    /*
     * @var LinkType[]
     * */
    protected $returnLabelLink;

    /*
     * @var LinkType[]
     * */
    protected $commercialInvoiceLink;

    /*
     * @var LinkType[]
     * */
    protected $detailsLink;

    /*
     * @var LinkType[]
     * */
    protected $refundLink;
    /*
     * @var LinkType[]
     * */
    protected $groupLink;

    /**
     * @var LinkType[]
     * ref="links"
     */
    protected $links = array();

    /**
     * @return DeliverySpecType
     */
    public function getDeliverySpec()
    {
        return $this->deliverySpec;
    }

    /**
     * @param DeliverySpecType $deliverySpec
     *
     * @return NonContractShipmentType
     */
    public function setDeliverySpec($deliverySpec)
    {
        $this->deliverySpec = $deliverySpec;

        return $this;
    }

    /**
     * @return string
     */
    public function getShipmentId()
    {
        return $this->shipmentId;
    }

    /**
     * @param string $shipmentId
     * @return ShipmentInfoType
     */
    public function setShipmentId($shipmentId)
    {
        $this->shipmentId = $shipmentId;

        return $this;
    }

    /**
     * @return string
     */
    public function getShipmentStatus()
    {
        return $this->shipmentStatus;
    }

    /**
     * @param string $shipmentStatus
     * @return ShipmentInfoType
     */
    public function setShipmentStatus($shipmentStatus)
    {
        $this->shipmentStatus = $shipmentStatus;

        return $this;
    }

    /**
     * @return string
     */
    public function getTrackingPin()
    {
        return $this->trackingPin;
    }

    /**
     * @param string $trackingPin
     * @return ShipmentInfoType
     */
    public function setTrackingPin($trackingPin)
    {
        $this->trackingPin = $trackingPin;

        return $this;
    }

    /**
     * @return string
     */
    public function getReturnTrackingPin()
    {
        return $this->returnTrackingPin;
    }

    /**
     * @param string $returnTrackingPin
     * @return ShipmentInfoType
     */
    public function setReturnTrackingPin($returnTrackingPin)
    {
        $this->returnTrackingPin = $returnTrackingPin;

        return $this;
    }

    /**
     * @return string
     */
    public function getFinalShippingPoint()
    {
        return $this->finalShippingPoint;
    }

    /**
     * @param string $finalShippingPoint
     *
     * @return ShipmentInfoType
     */
    public function setFinalShippingPoint($finalShippingPoint)
    {
        $this->finalShippingPoint = $finalShippingPoint;

        return $this;
    }

    /**
     * @return string
     */
    public function getPoNumber()
    {
        return $this->poNumber;
    }

    /**
     * @param string $poNumber
     * @return ShipmentInfoType
     */
    public function setPoNumber($poNumber)
    {
        $this->poNumber = $poNumber;

        return $this;
    }

    /**
     * @return LinkType[]
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * @param LinkType[] $links
     * @return ShipmentInfoType
     */
    public function setLinks($links)
    {
        $this->links = $links;

        return $this;
    }

    /**
     * @param LinkType $link
     * @return ShipmentInfoType
     */
    public function addLink($link)
    {
        $this->links[] = $link;

        return $this;
    }

    public function setSelfLink($link)
    {
        $this->selfLink = $link;

        return $this;
    }

    /**
     * @return LinkType
     */
    public function getSelfLink()
    {
        return $this->selfLink;
    }

    public function setLabelLink($link)
    {
        $this->labelLink = $link;

        return $this;
    }

    /**
     * @return LinkType
     */
    public function getLabelLink()
    {
        return $this->labelLink;
    }

    public function setReceiptLink($link)
    {
        $this->receiptLink = $link;

        return $this;
    }

    /**
     * @return LinkType
     */
    public function getReceiptLink()
    {
        return $this->receiptLink;
    }

    public function setDetailsLink($link)
    {
        $this->detailsLink = $link;

        return $this;
    }

    /**
     * @return LinkType
     */
    public function getDetailsLink()
    {
        return $this->detailsLink;
    }

    public function setRefundLink($link)
    {
        $this->refundLink = $link;

        return $this;
    }

    /**
     * @return LinkType
     */
    public function getRefundLink()
    {
        return $this->refundLink;
    }

    public function setGroupLink($link)
    {
        $this->groupLink = $link;

        return $this;
    }

    /**
     * @return LinkType
     */
    public function getGroupLink()
    {
        return $this->groupLink;
    }

    /**
     * @return LinkType
     */
    public function getReturnLabelLink()
    {
        return $this->returnLabelLink;
    }

    /**
     * @param mixed $returnLabelLink
     */
    public function setReturnLabelLink($returnLabelLink)
    {
        $this->returnLabelLink = $returnLabelLink;
    }

    /**
     * @return LinkType
     */
    public function getCommercialInvoiceLink()
    {
        return $this->commercialInvoiceLink;
    }

    /**
     * @param mixed $commercialInvoiceLink
     */
    public function setCommercialInvoiceLink($commercialInvoiceLink)
    {
        $this->commercialInvoiceLink = $commercialInvoiceLink;
    }
}
