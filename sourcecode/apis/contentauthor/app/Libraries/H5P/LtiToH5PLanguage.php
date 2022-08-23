<?php

namespace App\Libraries\H5P;

class LtiToH5PLanguage
{
    public static function convert($ltiLanguage = 'en-gb', $defaultLanguage = 'en-gb'): string
    {
        return self::extractCode($ltiLanguage) ?: self::extractCode($defaultLanguage) ?: 'en';
    }

    private static function extractCode(?string $language): ?string
    {
        $code = str_replace('_', '-', strtolower($language));

        if (strlen($code) === 2 && !strpos($code, '-')) {
            return $code;
        }

        if (strlen($code) > 2 && strpos($code, '-')) {
            $pieces = explode('-', $code);
            return strlen($pieces[0]) === 2 ? $pieces[0] : null;
        }

        return null;
    }
}
