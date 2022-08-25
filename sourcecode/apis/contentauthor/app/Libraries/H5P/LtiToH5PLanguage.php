<?php

declare(strict_types=1);

namespace App\Libraries\H5P;

class LtiToH5PLanguage
{
    public static function convert(?string $ltiLanguage = 'en-gb', ?string $defaultLanguage = 'en-gb'): string
    {
        return self::extractCode($ltiLanguage) ?: self::extractCode($defaultLanguage) ?: 'en';
    }

    private static function extractCode(?string $language): ?string
    {
        if ($language !== null) {
            $code = str_replace('_', '-', strtolower($language));

            if (strlen($code) === 2 && !strpos($code, '-')) {
                return $code;
            }

            if (strlen($code) > 2 && strpos($code, '-')) {
                $pieces = explode('-', $code);
                return strlen($pieces[0]) === 2 ? $pieces[0] : null;
            }
        }

        return null;
    }
}
