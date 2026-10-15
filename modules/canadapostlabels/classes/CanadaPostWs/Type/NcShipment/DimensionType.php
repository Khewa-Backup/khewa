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

class DimensionType
{
    /**
     * @var float
     * name="length" type="DimensionMeasurementType"
     */
    protected $length;

    /**
     * @var float
     * name="width" type="DimensionMeasurementType"
     */
    protected $width;

    /**
     * @var float
     * name="height" type="DimensionMeasurementType"
     */
    protected $height;

    /**
     * @return float
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param float $length
     * @return DimensionType
     */
    public function setLength($length)
    {
        $this->length = $length;

        return $this;
    }

    /**
     * @return float
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param float $width
     * @return DimensionType
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * @return float
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param float $height
     * @return DimensionType
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }
}
