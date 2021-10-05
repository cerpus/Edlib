<?php

namespace App\Libraries\NDLA\Importers\Handlers\Helpers;


trait LicenseHelper
{
    use \Cerpus\LicenseClient\Traits\LicenseHelper;

    public function canImport($copyright)
    {
        $license = $this->toEdLibLicenseString($copyright->license->license ?? '');

        $result = mb_strstr($license, 'BY') || in_array($license, ['PDM', 'CC0']);

        if ($result === false) {
            return false;
        }

        return true;
    }

    /**
     * Normalizes a license string and returns the H5P equivalent license string. If EdLib does not support the resulting license null is returned.
     *
     * @param $licenseString The string to get H5P equivalent license for
     * @return string|null The H5P licens string, or null if license is unsupported in EdLib.
     */
    public function toH5PLicenseString($licenseString)
    {
        if (!$normalizedString = $this->toEdLibLicenseString($licenseString)) {
            return null;
        }

        $normalizedLicenseToH5PLicenseMap = [
            'CC0' => 'CC0 1.0',
            'BY' => 'CC BY',
            'BY-SA' => 'CC BY-SA',
            'BY-ND' => 'CC BY-ND',
            'BY-NC' => 'CC BY-NC',
            'BY-NC-SA' => 'CC BY-NC-SA',
            'BY-NC-ND' => 'CC BY-NC-ND',
            'PRIVATE' => 'C',
            'PDM' => 'CC PDM',
        ];

        $h5pLicense = $normalizedLicenseToH5PLicenseMap[$normalizedString];

        return $h5pLicense;
    }
}
