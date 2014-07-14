<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 14.07.2014
 * Time: 09:19
 */

namespace Nokaut\ApiKit\Converter\Product;


use Nokaut\ApiKit\Converter\ConverterInterace;
use Nokaut\ApiKit\Entity\Product\OfferWithBestPrice;

class OfferWithBestPriceConverter implements ConverterInterace
{
    public function convert(\stdClass $object)
    {
        $offerWithBestPrice = new OfferWithBestPrice();

        foreach ($object as $field => $value) {
            $offerWithBestPrice->set($field, $value);
        }
        return $offerWithBestPrice;
    }

} 