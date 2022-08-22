<?php

/**
 * Created by PhpStorm.
 * User: oddaj
 * Date: 12/5/16
 * Time: 8:15 AM
 */

namespace App\Libraries\H5P;

class LtiToH5PLanguage
{
    public static function convert($ltiLanguage = 'en-gb', $defaultLanguage = 'en-gb'): string
    {
        if (strlen($ltiLanguage) === 2) {
            return $ltiLanguage;
        } elseif (strlen($ltiLanguage) > 2) {
            $split = explode('-', $ltiLanguage);
            return $split[0];
        }

        if (strlen($defaultLanguage) === 2) {
            return $defaultLanguage;
        } elseif (strlen($defaultLanguage) > 2) {
            $split = explode('-', $defaultLanguage);
            return $split[0];
        }

        return 'en';
    }
}
