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

class ShipmentsType
{
    /**
     * @var LinkType[]
     * ref="link" minOccurs="0" maxOccurs="unbounded"
     */
    protected $links = array();

    /**
     * @return LinkType[]
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * @param LinkType[] $links
     * @return ShipmentsType
     */
    public function setLinks($links)
    {
        $this->links = $links;

        return $this;
    }

    /**
     * @param LinkType $link
     * @return ShipmentsType
     */
    public function addLink($link)
    {
        $this->links[] = $link;

        return $this;
    }
}
