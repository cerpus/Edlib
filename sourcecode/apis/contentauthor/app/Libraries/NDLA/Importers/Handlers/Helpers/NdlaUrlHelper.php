<?php
/**
 * Created by PhpStorm.
 * User: oddaj
 * Date: 7/14/16
 * Time: 9:39 AM
 */

namespace App\Libraries\NDLA\Importers\Handlers\Helpers;


class NdlaUrlHelper
{
    public static function getFullLinkUrl($url, $ndlaParts = null)
    {
        if (is_null($ndlaParts)) {
            $ndlaParts = config('ndla.linkBaseUrl');
        }

        $ndlaParts = parse_url($ndlaParts);

        $urlParts = parse_url($url);
        if (array_key_exists('host', $urlParts)) {
            if (stripos($urlParts['host'], 'ndla') === false) { // External link...
                return $url; // Just return original URL
            }
        }

        if (array_key_exists('scheme', $ndlaParts)) {
            $finalUrl['scheme'] = $ndlaParts['scheme'];
        }

        if (array_key_exists('user', $ndlaParts)) {
            $finalUrl['user'] = $ndlaParts['user'];
        }

        if (array_key_exists('pass', $ndlaParts)) {
            $finalUrl['pass'] = $ndlaParts['pass'];
        }

        if (array_key_exists('host', $ndlaParts)) {
            $finalUrl['host'] = $ndlaParts['host'];
        }

        if (array_key_exists('path', $urlParts)) {
            $finalUrl['path'] = $urlParts['path'];
        }

        if (array_key_exists('port', $urlParts)) {
            $finalUrl['port'] = $urlParts['port'];
        }

        if (array_key_exists('query', $urlParts)) {
            $finalUrl['query'] = $urlParts['query'];
        }

        if (array_key_exists('fragment', $urlParts)) {
            $finalUrl['fragment'] = $urlParts['fragment'];
        }

        $theUrl = NdlaUrlHelper::urlStringFromArray($finalUrl);

        return $theUrl;
    }

    public static function urlStringFromArray($urlParts)
    {
        $theUrl = '';
        if (array_key_exists('scheme', $urlParts)) {
            $theUrl = $urlParts['scheme'];
        }

        if (array_key_exists('user', $urlParts) && array_key_exists('pass', $urlParts)) {
            if (empty($theUrl)) {
                $theUrl .= '//';
            } else {
                $theUrl .= '://';
            }
            $theUrl .= $urlParts['user'] . ':' . $urlParts['pass'] . '@';
        }

        if (array_key_exists('host', $urlParts)) {
            if (empty($theUrl)) {
                $theUrl .= '//';
            } else {
                $theUrl .= '://';
            }
            $theUrl .= $urlParts['host'];
        }


        if (array_key_exists('port', $urlParts)) {
            $theUrl .= ':' . $urlParts['port'];
        }

        if (array_key_exists('path', $urlParts)) {
            $theUrl .= $urlParts['path'];
        }

        if (array_key_exists('query', $urlParts)) {
            $theUrl .= '?' . $urlParts['query'];
        }

        if (array_key_exists('fragment', $urlParts)) {
            $theUrl .= '#' . $urlParts['fragment'];
        }

        return $theUrl;
    }

    public static function ndlaUrlForId($id)
    {
        $baseUrl = config('ndla.linkBaseUrl');
        $finalUrl = $baseUrl . '/node/' . $id;
        return $finalUrl;
    }

    public static function findNodeFromUrl($url)
    {
        $match = preg_match('/^.+\/node\/([\d]+)/', $url, $matches);
        return !empty($match) ? $matches[1] : false;
    }

}