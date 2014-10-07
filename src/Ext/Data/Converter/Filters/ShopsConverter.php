<?php


namespace Nokaut\ApiKit\Ext\Data\Converter\Filters;

use Nokaut\ApiKit\Collection\Products;
use Nokaut\ApiKit\Ext\Data\Converter\ConverterInterface;
use Nokaut\ApiKit\Ext\Data\Converter\Filters\Callback\ShopsCallbackInterface;
use Nokaut\ApiKit\Ext\Data\Collection\Filters\Shops;
use Nokaut\ApiKit\Ext\Data\Entity\Filter\Shop;

class ShopsConverter implements ConverterInterface
{
    /**
     * @param Products $products
     * @param ShopsCallbackInterface[] $callbacks
     * @return Shops
     */
    public function convert(Products $products, $callbacks = array())
    {
        $shops = $this->initialConvert($products);

        foreach ($callbacks as $callback) {
            $callback($shops, $products);
        }

        return $shops;
    }

    /**
     * @param Products $products
     * @return Shops
     */
    public function initialConvert(Products $products)
    {
        $facetShops = $products->getShops();
        $shops = array();

        foreach ($facetShops as $facetShop) {
            $shop = new Shop();
            $shop->setId($facetShop->getId());
            $shop->setName($facetShop->getName());
            $shop->setUrl($facetShop->getUrl());
            $shop->setUrlBase($facetShop->getUrlBase());
            $shop->setIsFilter($facetShop->getIsFilter());
            $shop->setTotal($facetShop->getTotal());

            $shops[] = $shop;
        }

        return new Shops($shops);
    }
}