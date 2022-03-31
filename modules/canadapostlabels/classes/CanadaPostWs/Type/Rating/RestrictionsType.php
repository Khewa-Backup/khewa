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

class RestrictionsType
{
    /* @var */
    protected $weightRestriction;

    protected $dimensionalRestrictions;

    protected $densityFactor;

    protected $canShipInMailingTube;

    protected $canShipUnpackaged;

    protected $allowedAsReturnService;

    /**
     * @return mixed
     */
    public function getWeightRestriction()
    {
        return $this->weightRestriction;
    }

    /**
     * @param mixed $weightRestriction
     */
    public function setWeightRestriction($weightRestriction)
    {
        $this->weightRestriction = $weightRestriction;
    }

    /**
     * @return mixed
     */
    public function getDimensionalRestrictions()
    {
        return $this->dimensionalRestrictions;
    }

    /**
     * @param $dimensionalRestrictions
     */
    public function setDimensionalRestrictions($dimensionalRestrictions)
    {
        $this->dimensionalRestrictions = $dimensionalRestrictions;
    }

    /**
     * @param array $dimensionalRestrictions
     */
    public function addDimensionalRestriction($dimensionalRestriction)
    {
        $this->dimensionalRestrictions[] = $dimensionalRestriction;
    }

    /**
     * @return mixed
     */
    public function getDensityFactor()
    {
        return $this->densityFactor;
    }

    /**
     * @param mixed $densityFactor
     */
    public function setDensityFactor($densityFactor)
    {
        $this->densityFactor = $densityFactor;
    }

    /**
     * @return mixed
     */
    public function getCanShipInMailingTube()
    {
        return $this->canShipInMailingTube;
    }

    /**
     * @param mixed $canShipInMailingTube
     */
    public function setCanShipInMailingTube($canShipInMailingTube)
    {
        $this->canShipInMailingTube = $canShipInMailingTube;
    }

    /**
     * @return mixed
     */
    public function getCanShipUnpackaged()
    {
        return $this->canShipUnpackaged;
    }

    /**
     * @param mixed $canShipUnpackaged
     */
    public function setCanShipUnpackaged($canShipUnpackaged)
    {
        $this->canShipUnpackaged = $canShipUnpackaged;
    }

    /**
     * @return mixed
     */
    public function getAllowedAsReturnService()
    {
        return $this->allowedAsReturnService;
    }

    /**
     * @param mixed $allowedAsReturnService
     */
    public function setAllowedAsReturnService($allowedAsReturnService)
    {
        $this->allowedAsReturnService = $allowedAsReturnService;
    }
}
