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

class DeliveryOptionsType
{
    /**
     * @var DeliveryOptionsType[]
     * name="option" type="DeliveryOptionsType" maxOccurs="20"
     */
    protected $deliveryOptions = array();

    /**
     * @return DeliveryOptionsType[]
     */
    public function getDeliveryOptions()
    {
        return $this->deliveryOptions;
    }

    /**
     * @param DeliveryOptionsType[] $deliveryOptions
     */
    public function setDeliveryOptions($deliveryOptions)
    {
        $this->deliveryOptions = $deliveryOptions;
    }



    /**
     * @param DeliveryOptionsType $option
     * @return DeliveryOptionsType
     */
    public function addDeliveryOption($option)
    {
        $this->deliveryOptions[] = $option;

        return $this;
    }
}
