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

class NonContractShipmentType
{
    /**
     * @var string
     * name="requested-shipping-point" type="PostalCodeType" minOccurs="0"
     */
    protected $requestedShippingPoint;

    /**
     * @var string
     * name="shipment-id" type="ShipmentIDType"
     */
    protected $shipmentId;

    /**
     * @var DeliverySpecType
     * name="delivery-spec" type="DeliverySpecType"
     */
    protected $deliverySpec;

    /**
     * @var string
     * name="tracking-pin" type="TrackingPINType" minOccurs="0"
     */
    protected $trackingPin;

    /**
     * @var LinkType[]
     * ref="links"
     */
    protected $links = array();

    /**
     * @var string
     * name="final-shipping-point" minOccurs="0"
     */
    protected $finalShippingPoint;

    /**
     * @return string
     */
    public function getRequestedShippingPoint()
    {
        return $this->requestedShippingPoint;
    }

    /**
     * @param string $requestedShippingPoint
     *
     * @return NonContractShipmentType
     */
    public function setRequestedShippingPoint($requestedShippingPoint)
    {
        $this->requestedShippingPoint = $requestedShippingPoint;

        return $this;
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
     *
     * @return NonContractShipmentType
     */
    public function setDeliverySpec($deliverySpec)
    {
        $this->deliverySpec = $deliverySpec;

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
     *
     * @return NonContractShipmentType
     */
    public function setLinks($links)
    {
        $this->links = $links;

        return $this;
    }

    /**
     * @param LinkType $link
     *
     * @return NonContractShipmentType
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
     *
     * @return NonContractShipmentType
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
     *
     * @return NonContractShipmentType
     */
    public function setTrackingPin($trackingPin)
    {
        $this->trackingPin = $trackingPin;

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
     * @return NonContractShipmentType
     */
    public function setFinalShippingPoint($finalShippingPoint)
    {
        $this->finalShippingPoint = $finalShippingPoint;

        return $this;
    }
}
