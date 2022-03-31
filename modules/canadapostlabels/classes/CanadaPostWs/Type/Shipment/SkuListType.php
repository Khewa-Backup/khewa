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

class SkuListType
{
    /**
     * @var SkuType[]
     * name="item" type="SkuType" maxOccurs="500"
     */
    protected $items;

    /**
     * @return SkuType[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param SkuType[] $items
     * @return SkuListType
     */
    public function setItems($items)
    {
        $this->items = $items;

        return $this;
    }

    /**
     * @param SkuType $item
     * @return SkuListType
     */
    public function addItem($item)
    {
        $this->items[] = $item;

        return $this;
    }
}
