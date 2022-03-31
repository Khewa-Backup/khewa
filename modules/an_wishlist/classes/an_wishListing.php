<?php
/**
 * 2020 Anvanto
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses. 
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 *  @author Anvanto <anvantoco@gmail.com>
 *  @copyright  2020 Anvanto
 *  @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of Anvanto
 */

class an_wishListing extends ProductListingFrontControllerCore
{
    public function prepare(array $products)
    {
        return $this->prepareMultipleProductsForTemplate($products);
    }

    public function getListingLabel()
    {
    }

    protected function getProductSearchQuery()
    {
    }

    protected function getDefaultProductSearchProvider()
    {
    }
    
    public function getContainer()
    {
        $this->container = $this->buildContainer();
        return $this->container;
    }
}
