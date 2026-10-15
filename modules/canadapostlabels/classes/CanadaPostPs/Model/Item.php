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

namespace CanadaPostPs;

use \Context;
use \Product;
use \PrestaShopException;

class Item extends Product implements \CanadaPost\BoxPacker\Item
{

    /**
     * @var int
     */
    private $keepFlat = false;

    /**
     * @var int
     */
    private $volume;

    /**
     * Item constructor.
     *
     * @param string $description
     * @param int    $width
     * @param int    $length
     * @param int    $depth
     * @param int    $weight
     * @param int    $keepFlat
     */
//    public function __construct()
//    {
//        parent::__construct();
//    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->name[\Context::getContext()->language->id];
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return Tools::toMm($this->width);
    }

    /**
     * PrestaShop uses "Depth" as "Length" and BoxPacker uses "Depth" as "Height"
     * @return int
     */
    public function getLength()
    {
        return Tools::toMm($this->depth);
    }

    /**
     * PrestaShop uses "Depth" as "Length" and BoxPacker uses "Depth" as "Height"
     * @return int
     */
    public function getDepth()
    {
        return Tools::toMm($this->height);
    }

    /**
     * @return int
     */
    public function getWeight()
    {
        return Tools::toG($this->weight);
    }

    /**
     * @return int
     */
    public function getVolume()
    {
        return Tools::toMm($this->width) *  Tools::toMm($this->depth) *  Tools::toMm($this->height);
    }

    /**
     * @return int
     */
    public function getKeepFlat()
    {
        return $this->keepFlat;
    }
}
