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

class DeliveryOptionItemType
{
    protected $deliveryOption;

    protected $deliveryOptionDescription;

    /**
     * @return mixed
     */
    public function getDeliveryOption()
    {
        return $this->deliveryOption;
    }

    /**
     * @param mixed $deliveryOption
     */
    public function setDeliveryOption($deliveryOption)
    {
        $this->deliveryOption = $deliveryOption;
    }

    /**
     * @return mixed
     */
    public function getDeliveryOptionDescription()
    {
        return $this->deliveryOptionDescription;
    }

    /**
     * @param mixed $deliveryOptionDescription
     */
    public function setDeliveryOptionDescription($deliveryOptionDescription)
    {
        $this->deliveryOptionDescription = $deliveryOptionDescription;
    }
}
