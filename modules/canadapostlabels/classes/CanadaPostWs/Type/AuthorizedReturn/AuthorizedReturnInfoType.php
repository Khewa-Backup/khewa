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

namespace CanadaPostWs\Type\AuthorizedReturn;

use CanadaPostWs\Type\Common\LinkType;

class AuthorizedReturnInfoType
{
    protected $trackingPin;

    protected $returnLabelLink;

    protected $links = array();

    /**
     * @return mixed
     */
    public function getTrackingPin()
    {
        return $this->trackingPin;
    }

    /**
     * @param mixed $trackingPin
     */
    public function setTrackingPin($trackingPin)
    {
        $this->trackingPin = $trackingPin;
    }

    /**
     * @return array
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * @param array $links
     */
    public function setLinks($links)
    {
        $this->links = $links;
    }

    /**
     * @param LinkType $link
     * @return AuthorizedReturnInfoType
     */
    public function addLink($link)
    {
        $this->links[] = $link;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getReturnLabelLink()
    {
        return $this->returnLabelLink;
    }

    /**
     * @param mixed $returnLink
     */
    public function setReturnLabelLink($returnLink)
    {
        $this->returnLabelLink = $returnLink;
    }
}
