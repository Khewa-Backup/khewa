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

class DimensionalRestrictionType
{
    protected $length;

    protected $width;

    protected $height;

    protected $lengthPlusGirthMax;

    protected $lengthHeightWidthSumMax;

    protected $oversizeLimit;

    /**
     * @return mixed
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param mixed $length
     */
    public function setLength($length)
    {
        $this->length = $length;
    }

    /**
     * @return mixed
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param mixed $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param mixed $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * @return mixed
     */
    public function getLengthPlusGirthMax()
    {
        return $this->lengthPlusGirthMax;
    }

    /**
     * @param mixed $lengthPlusGirthMax
     */
    public function setLengthPlusGirthMax($lengthPlusGirthMax)
    {
        $this->lengthPlusGirthMax = $lengthPlusGirthMax;
    }

    /**
     * @return mixed
     */
    public function getLengthHeightWidthSumMax()
    {
        return $this->lengthHeightWidthSumMax;
    }

    /**
     * @param mixed $lengthHeightWidthSumMax
     */
    public function setLengthHeightWidthSumMax($lengthHeightWidthSumMax)
    {
        $this->lengthHeightWidthSumMax = $lengthHeightWidthSumMax;
    }

    /**
     * @return mixed
     */
    public function getOversizeLimit()
    {
        return $this->oversizeLimit;
    }

    /**
     * @param mixed $oversizeLimit
     */
    public function setOversizeLimit($oversizeLimit)
    {
        $this->oversizeLimit = $oversizeLimit;
    }
}
