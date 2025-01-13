<?php

namespace App\Http\Libraries;

use App\Lti\LtiRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class License
{
    public const LICENSE_CC0 = 'CC0';
    public const LICENSE_COPYRIGHT = 'COPYRIGHT';
    public const LICENSE_EDLIB = "EDLL";
    public const LICENSE_PDM = 'PDM';
    public const LICENSE_PRIVATE = 'PRIVATE';

    public const LICENSE_BY_NC = 'BY-NC';
    public const LICENSE_BY_ND = 'BY-ND';
    public const LICENSE_BY_NC_ND = 'BY-NC-ND';
    public const LICENSE_BY_NC_SA = 'BY-NC-SA';
    public const LICENSE_BY_SA = 'BY-SA';

    public const LICENSE_BY = 'BY';
    public const LICENSE_CC = 'CC';
    public const LICENSE_NC = 'NC';
    public const LICENSE_ND = 'ND';
    public const LICENSE_SA = 'SA';

    public const LICENSE_CC_BY = 'CC-BY';
    public const LICENSE_CC_BY_SA = 'CC-BY-SA';
    public const LICENSE_CC_BY_ND = 'CC-BY-ND';
    public const LICENSE_CC_BY_NC = 'CC-BY-NC';
    public const LICENSE_CC_BY_NC_SA = 'CC-BY-NC-SA';
    public const LICENSE_CC_BY_NC_ND = 'CC-BY-NC-ND';

    public const ALLOWED_LICENSES = 'PRIVATE,CC0,PDM,BY,BY-SA,BY-NC,BY-ND,BY-NC-SA,BY-NC-ND,EDLL';

    public const VALID_LICENSES = [
        self::LICENSE_BY,
        self::LICENSE_BY_SA,
        self::LICENSE_BY_ND,
        self::LICENSE_BY_NC,
        self::LICENSE_BY_NC_SA,
        self::LICENSE_BY_NC_ND,
        self::LICENSE_CC0,
        self::LICENSE_PRIVATE,
        self::LICENSE_PDM,
        self::LICENSE_EDLIB,
    ];

    public const THROW_AWAY_LICENSE_PARTS = [
        'CREATIVE COMMONS',
        'CREATIVE-COMMONS',
        'LICENSE',
        'CC-',
        'CC ',
        '-1.0',
        '1.0',
        '-2.0',
        '2.0',
        '-2.5',
        '2.5',
        '-3.0',
        '3.0',
        '-4.0',
        '4.0',
        'INTERNATIONAL',
    ];

    // This must be in sync with LICENSE_LONG_FORM_PARTS for replacement to happen correctly
    public const LICENSE_SHORT_FORM_PARTS = [
        self::LICENSE_BY,
        self::LICENSE_SA,
        self::LICENSE_ND,
        self::LICENSE_NC,
        'C',
        'C',
        self::LICENSE_CC0,
        self::LICENSE_PDM,
        self::LICENSE_EDLIB,
    ];

    // This must be in sync with LICENSE_SHORT_FORM_PARTS for replacement to happen correctly
    public const LICENSE_LONG_FORM_PARTS = [
        'en' => [
            'ATTRIBUTION',
            'SHAREALIKE',
            'NODERIVATIVES',
            'NONCOMMERCIAL',
            'PRIVATE',
            'COPYRIGHT',
            'ZERO',
            'PUBLIC DOMAIN MARK',
            'EDLIB LICENSE',
        ],
        'no' => [
            'NAVNGIVELSE',
            'DEL PA SAMME VILKAR', // Note: Norwegian characters are replaced in toEdLibLicenseString
            'INGEN BEARBEIDELSE',
            'IKKEKOMMERSIELL',
            'PRIVATE',
            'COPYRIGHT',
            'ZERO',
            'PUBLIC DOMAIN MARK',
            'EDLIB LISENS',
        ],
    ];

    /**
     * Return the OER Licenses.
     */
    public static function getLicenses(LtiRequest $ltiRequest = null): array
    {
        $allowedLicenses = explode(',', self::getAllowedLicenses($ltiRequest));
        return self::translateLicensesName($allowedLicenses)->all();
    }

    protected static function translateLicensesName(array $licenses = []): Collection
    {
        return collect($licenses)
            ->map(function ($license) {
                return (object) [
                    'id' => $license,
                    'name' => trans('licenses.' . $license),
                ];
            });
    }

    protected static function getAllowedLicenses(LtiRequest $ltiRequest = null)
    {
        if (empty($ltiRequest)) {
            return Session::get('allowedLicenses', self::ALLOWED_LICENSES);
        }

        return $ltiRequest->getAllowedLicenses(self::ALLOWED_LICENSES);
    }

    public static function getDefaultLicense(LtiRequest $ltiRequest = null)
    {
        $defaultLicense = config('license.default-license');
        if (empty($ltiRequest)) {
            return Session::get('defaultLicense', $defaultLicense);
        }

        return $ltiRequest->getDefaultLicense($defaultLicense);
    }

    public static function isContentCopyable(string $licenseName): bool
    {
        return !(
            mb_stristr($licenseName, '-ND') ||
            mb_stristr($licenseName, self::LICENSE_PRIVATE) ||
            mb_stristr($licenseName, self::LICENSE_EDLIB)
        );
    }

    public static function isLicenseSupported(string $checkLicense): bool
    {
        return collect(explode(',', self::getAllowedLicenses()))
            ->filter(function ($license) use ($checkLicense) {
                return strtolower($license) === strtolower($checkLicense);
            })
            ->isNotEmpty();
    }

    /**
     * Normalizes a license string and returns the H5P equivalent license string. If EdLib does not support the resulting license null is returned.
     *
     * @param string $licenseString The string to get H5P equivalent license for
     * @return string|null The H5P licens string, or null if license is unsupported in EdLib.
     */
    public static function toH5PLicenseString($licenseString)
    {
        if (!$normalizedString = self::toEdLibLicenseString($licenseString)) {
            return null;
        }

        $normalizedLicenseToH5pLicenseMap = [
            self::LICENSE_CC0 => 'CC0 1.0',
            self::LICENSE_BY => 'CC BY',
            self::LICENSE_BY_SA => 'CC BY-SA',
            self::LICENSE_BY_ND => 'CC BY-ND',
            self::LICENSE_BY_NC => 'CC BY-NC',
            self::LICENSE_BY_NC_SA => 'CC BY-NC-SA',
            self::LICENSE_BY_NC_ND => 'CC BY-NC-ND',
            self::LICENSE_PRIVATE => 'C',
            self::LICENSE_PDM => 'CC PDM',
        ];

        return $normalizedLicenseToH5pLicenseMap[$normalizedString];
    }

    /**
     * Take almost any half way sensible Creative Commons or EdLib license string and normalize it to the license format used by EdLib.
     *
     * @param string $licenseString The string you want normalized.
     * @return string|null The normalized license string. Null if the resulting license is not supported by EdLib or makes no sense
     */
    public static function toEdLibLicenseString(string $licenseString): ?string
    {
        $normalizingCopyrightString = strtoupper(trim($licenseString));

        $normalizingCopyrightString = str_replace(['Æ', 'Ø', 'Å'], ['A', 'O', 'A'], $normalizingCopyrightString);
        $normalizingCopyrightString = str_replace(['æ', 'ø', 'å'], ['A', 'O', 'A'], $normalizingCopyrightString);

        // Replace long form parts of a license with short form. Ex Attribution -> BY
        // Supports English and Norwegian longform parts
        foreach (self::LICENSE_LONG_FORM_PARTS as $licenseLongFormPart) {
            $normalizingCopyrightString = str_replace($licenseLongFormPart, self::LICENSE_SHORT_FORM_PARTS, $normalizingCopyrightString);
        }

        // Some massaging of the string to get it to a point where we can
        $normalizingCopyrightString = str_replace(self::THROW_AWAY_LICENSE_PARTS, '', $normalizingCopyrightString);
        $normalizingCopyrightString = preg_replace("/(\s)\\1+/", '-', $normalizingCopyrightString);
        $normalizingCopyrightString = str_replace(' ', '-', $normalizingCopyrightString);

        $licenseParts = explode('-', $normalizingCopyrightString);
        $licenseParts = array_unique($licenseParts);
        $licenseParts = array_values(array_filter($licenseParts, 'strlen')); // Remove empty array fields

        if (sizeof($licenseParts) === 1) {
            if ($licenseParts[0] === 'C') {
                $licenseParts[0] = self::LICENSE_PRIVATE;
            } elseif ($licenseParts[0] === 'PD') {
                $licenseParts[0] = self::LICENSE_PDM;
            }
        }

        $licenseParts = self::rearrangeLicenseTerms($licenseParts);

        $normalizedCopyrightString = implode('-', $licenseParts);

        if (!in_array($normalizedCopyrightString, self::VALID_LICENSES)) {
            return null;
        }

        return $normalizedCopyrightString;
    }

    /**
     * Make sure the order of the cc license terms is correct.
     *
     * @param array $licenseParts The array to check.
     * @return array The license with the correct order of the license parts.
     */
    protected static function rearrangeLicenseTerms(array $licenseParts = []): array
    {
        if (empty($licenseParts)) {
            return [];
        }

        $rearrangedLicenseParts = [];

        $orderedLicenseParts = [
            self::LICENSE_BY,
            self::LICENSE_NC,
            self::LICENSE_SA,
            self::LICENSE_ND,
            self::LICENSE_CC0,
            self::LICENSE_PRIVATE,
            self::LICENSE_PDM,
            self::LICENSE_EDLIB,
        ];

        // Create a new array containing the license parts in the correct order
        foreach ($orderedLicenseParts as $licensePart) {
            if (in_array($licensePart, $licenseParts)) {
                $rearrangedLicenseParts[] = $licensePart;
            }
        }

        if (sizeof($rearrangedLicenseParts) !== sizeof($licenseParts)) {
            return [];
        }

        return $rearrangedLicenseParts;
    }
}
