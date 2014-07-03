<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 04.06.2014
 * Time: 13:56
 */

namespace Nokaut\ApiKit\Helper;

use Nokaut\ApiKit\Text\Text;

class PhotoUrl
{

    public static function prepare($photoId, $size = '90x90', $additionalUrlPart = '')
    {
        return 'p-' . substr($photoId, 0, 2) . '-' . substr($photoId, 2, 2) . '-' . $photoId . $size . '/' . Text::urlize($additionalUrlPart) . '.jpg';
    }
} 