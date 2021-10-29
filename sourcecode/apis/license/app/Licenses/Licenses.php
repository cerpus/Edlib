<?php

namespace App\Licenses;

class Licenses
{
    public static function getLicenses()
    {
        static $licenses;
        if ($licenses === null) {
            $licenses = [
                new License('PRIVATE', 'Private', []),
                new License('CC0', 'Creative Commons', [License::RETAIN, License::REUSE, License::REVISE, License::REMIX, License::REDISTRIBUTE, License::COMERCIAL, License::SUBLICENSE]),
                new License('BY', 'CC Attribution', [License::RETAIN, License::REUSE, License::REVISE, License::REMIX, License::REDISTRIBUTE, License::COMERCIAL, License::SUBLICENSE]),
                new License('BY-SA', 'CC Attribution, sharealike', [License::RETAIN, License::REUSE, License::REVISE, License::REMIX, License::REDISTRIBUTE, License::COMERCIAL]),
                new License('BY-NC', 'CC Attribution, noncommercial', [License::RETAIN, License::REUSE, License::REVISE, License::REMIX, License::REDISTRIBUTE, License::SUBLICENSE]),
                new License('BY-ND', 'CC Attribution, no derrivatives', [License::RETAIN, License::REUSE, License::REDISTRIBUTE, License::COMERCIAL]),
                new License('BY-NC-SA', 'CC Attribution, noncommercial, sharealike', [License::RETAIN, License::REUSE, License::REVISE, License::REMIX, License::REDISTRIBUTE]),
                new License('BY-NC-ND', 'CC Attribution, noncommercial, no derrivatives', [License::RETAIN, License::REUSE, License::REDISTRIBUTE]),

                // The PDM is used for works that has expired copyright as opposed to the
                // CC0 where a copyright holder relinquishes his/her rights to the work.
                // https://wiki.creativecommons.org/wiki/PDM_FAQ#What_is_the_difference_between_the_PDM_and_CC0.3F
                new License('PDM', 'Public Domain Mark', [License::RETAIN, License::REUSE, License::REVISE, License::REMIX, License::REDISTRIBUTE, License::COMERCIAL, License::SUBLICENSE]),

                // This is the EdLib license
                new License('EDLL', 'EdLib license', []),
            ];
        }
        return $licenses;
    }
}
