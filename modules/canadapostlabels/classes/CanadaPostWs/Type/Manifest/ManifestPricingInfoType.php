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

class ManifestPricingInfoType
{
    protected $baseCost;

    protected $automationDiscount;

    protected $optionsAndSurcharges;

    protected $gst;

    protected $pst;

    protected $hst;

    protected $totalDueCpc;

    /**
     * @return mixed
     */
    public function getBaseCost()
    {
        return $this->baseCost;
    }

    /**
     * @param mixed $baseCost
     */
    public function setBaseCost($baseCost)
    {
        $this->baseCost = $baseCost;
    }

    /**
     * @return mixed
     */
    public function getAutomationDiscount()
    {
        return $this->automationDiscount;
    }

    /**
     * @param mixed $automationDiscount
     */
    public function setAutomationDiscount($automationDiscount)
    {
        $this->automationDiscount = $automationDiscount;
    }

    /**
     * @return mixed
     */
    public function getOptionsAndSurcharges()
    {
        return $this->optionsAndSurcharges;
    }

    /**
     * @param mixed $optionsAndSurcharges
     */
    public function setOptionsAndSurcharges($optionsAndSurcharges)
    {
        $this->optionsAndSurcharges = $optionsAndSurcharges;
    }

    /**
     * @return mixed
     */
    public function getGst()
    {
        return $this->gst;
    }

    /**
     * @param mixed $gst
     */
    public function setGst($gst)
    {
        $this->gst = $gst;
    }

    /**
     * @return mixed
     */
    public function getPst()
    {
        return $this->pst;
    }

    /**
     * @param mixed $pst
     */
    public function setPst($pst)
    {
        $this->pst = $pst;
    }

    /**
     * @return mixed
     */
    public function getHst()
    {
        return $this->hst;
    }

    /**
     * @param mixed $hst
     */
    public function setHst($hst)
    {
        $this->hst = $hst;
    }

    /**
     * @return mixed
     */
    public function getTotalDueCpc()
    {
        return $this->totalDueCpc;
    }

    /**
     * @param mixed $totalDueCpc
     */
    public function setTotalDueCpc($totalDueCpc)
    {
        $this->totalDueCpc = $totalDueCpc;
    }
}
