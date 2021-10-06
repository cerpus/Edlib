<?php
/**
 * Created by PhpStorm.
 * User: oddaj
 * Date: 11/5/15
 * Time: 11:30 AM
 */

namespace App\Libraries\H5P\Helper;

class UrlHelper
{
    public static function getCurrentBaseUrl()
    {
        $url = request()->url();
        $path = request()->path();
        $theBaseUrl = str_replace($path, '', $url);
        if (substr($theBaseUrl, -1) === '/') {
            $theBaseUrl = substr($theBaseUrl, 0, strlen($theBaseUrl) - 1);
        }
        return $theBaseUrl;
    }

    public static function getCurrentFullUrl()
    {
        return request()->url();
    }
}
