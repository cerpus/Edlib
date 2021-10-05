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
    public static function convert($ltiLanguage = 'en-gb', $defaultLanguage = 'en-gb')
    {
        $convertMatrix = [
            'en-gb' => 'en',
            'nb-no' => 'nb',
            'nn-no' => 'nn',
        ];

        if (array_key_exists($ltiLanguage, $convertMatrix)) {
            return $convertMatrix[$ltiLanguage];
        }

        return $convertMatrix[$defaultLanguage];
    }
}