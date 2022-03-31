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

namespace CanadaPostWs\Type\NcShipment;

use CanadaPostWs\Type\Common\LinkType;

class NonContractShipmentInfoType
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
     * @var DeliverySpecType
     * name="delivery-spec" type="DeliverySpecType"
     */
    protected $deliverySpec;


    /**
     * @var string
     * name="final-shipping-point" minOccurs="0"
     */
    protected $finalShippingPoint;

    /**
     * @var LinkType[]
     * ref="link" minOccurs="0" maxOccurs="unbounded"
     */
    protected $links = array();

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
    protected $commercialInvoiceLink;

    /*
     * @var LinkType[]
     * */
    protected $detailsLink;

    /*
     * @var LinkType[]
     * */
    protected $refundLink;

    /**
     * @return LinkType[]
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * @param LinkType[] $links
     * @return NonContractShipmentInfoType
     */
    public function setLinks($links)
    {
        $this->links = $links;

        return $this;
    }

    /**
     * @param LinkType $link
     * @return NonContractShipmentInfoType
     */
    public function addLink($link)
    {
        $this->links[] = $link;

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
     * @return NonContractShipmentInfoType
     */
    public function setShipmentId($shipmentId)
    {
        $this->shipmentId = $shipmentId;

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
     * @return NonContractShipmentInfoType
     */
    public function setTrackingPin($trackingPin)
    {
        $this->trackingPin = $trackingPin;

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
     */
    public function setShipmentStatus($shipmentStatus)
    {
        $this->shipmentStatus = $shipmentStatus;
    }

    /**
     * @return DeliverySpecType
     */
    public function getDeliverySpec()
    {
        return $this->deliverySpec;
    }

    /**
     * @param DeliverySpecType $deliverySpec
     */
    public function setDeliverySpec($deliverySpec)
    {
        $this->deliverySpec = $deliverySpec;
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
     * @return NonContractShipmentType
     */
    public function setFinalShippingPoint($finalShippingPoint)
    {
        $this->finalShippingPoint = $finalShippingPoint;

        return $this;
    }

    public function setSelfLink($link)
    {
        $this->selfLink = $link;

        return $this;
    }

    public function getSelfLink()
    {
        return $this->selfLink;
    }

    public function setLabelLink($link)
    {
        $this->labelLink = $link;

        return $this;
    }

    public function getLabelLink()
    {
        return $this->labelLink;
    }

    public function setReceiptLink($link)
    {
        $this->receiptLink = $link;

        return $this;
    }

    public function getReceiptLink()
    {
        return $this->receiptLink;
    }

    public function setDetailsLink($link)
    {
        $this->detailsLink = $link;

        return $this;
    }

    public function getDetailsLink()
    {
        return $this->detailsLink;
    }

    public function setRefundLink($link)
    {
        $this->refundLink = $link;

        return $this;
    }

    public function getRefundLink()
    {
        return $this->refundLink;
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
