<?php
/**
* 2022 Anvanto
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
*
*  @author    Anvanto <anvantoco@gmail.com>
*  @copyright 2022 Anvanto
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
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
