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

namespace CanadaPostWs\Type\Rating;

class RatingType
{

    /**
     * @var DeliverySpecType
     * name="delivery-spec" type="DeliverySpecType"
     */
    protected $deliverySpec;

    /**
     * @return DeliverySpecType
     */
    public function getDeliverySpec()
    {
        return $this->deliverySpec;
    }

    /**
     * @param DeliverySpecType $deliverySpec
     * @return RatingType
     */
    public function setDeliverySpec($deliverySpec)
    {
        $this->deliverySpec = $deliverySpec;

        return $this;
    }
}
