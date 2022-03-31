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

namespace CanadaPostWs\Type\Manifest;

use CanadaPostWs\Type\Common\LinkType;

class ManifestType
{
    /**
     * @var string
     * name="po-number" type="PoNumberType"
     */
    protected $poNumber;

    /**
     * @var LinkType[]
     * ref="links"
     */
    protected $links = array();

    /*
     * @var LinkType[]
     * */
    protected $selfLink;

    /*
     * @var LinkType[]
     * */
    protected $artifactLink;

    /*
     * @var LinkType[]
     * */
    protected $detailsLink;

    /*
     * @var LinkType[]
     * */
    protected $shipmentsLink;

    /**
     * @return LinkType[]
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * @param LinkType[] $links
     * @return ManifestType
     */
    public function setLinks($links)
    {
        $this->links = $links;

        return $this;
    }

    /**
     * @param LinkType $link
     * @return ManifestType
     */
    public function addLink($link)
    {
        $this->links[] = $link;

        return $this;
    }

    /**
     * @return string
     */
    public function getPoNumber()
    {
        return $this->poNumber;
    }

    /**
     * @param string $poNumber
     * @return ManifestType
     */
    public function setPoNumber($poNumber)
    {
        $this->poNumber = $poNumber;

        return $this;
    }

    public function setSelfLink($link)
    {
        $this->selfLink = $link;

        return $this;
    }

    /**
     * @return LinkType
     */
    public function getSelfLink()
    {
        return $this->selfLink;
    }

    public function setArtifactLink($link)
    {
        $this->artifactLink = $link;

        return $this;
    }

    /**
     * @return LinkType
     */
    public function getArtifactLink()
    {
        return $this->artifactLink;
    }

    public function setDetailsLink($link)
    {
        $this->detailsLink = $link;

        return $this;
    }

    /**
     * @return LinkType
     */
    public function getDetailsLink()
    {
        return $this->detailsLink;
    }

    public function setShipmentsLink($link)
    {
        $this->shipmentsLink = $link;

        return $this;
    }

    /**
     * @return LinkType
     */
    public function getShipmentsLink()
    {
        return $this->shipmentsLink;
    }
}
