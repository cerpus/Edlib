<?php
/**
 * Created by PhpStorm.
 * User: janespen
 * Date: 25.07.16
 * Time: 10:13
 */

namespace App\Http\Controllers;

use App\Licenses\Licenses;

class LicenseInformationController
{
    public function getLicenses(): array
    {
        $licenses = Licenses::getLicenses();
        $list = [];
        foreach ($licenses as $license) {
            $list[] = [
                'id' => $license->getId(),
                'name' => $license->getName(),
            ];
        }

        return $list;
    }

    public function copyable($licenseName): array
    {
        $copyable = true;
        if (mb_strstr(mb_strtoupper($licenseName), '-ND')) {
            $copyable = false;
        }

        if (mb_strstr(mb_strtoupper($licenseName), 'PRIVATE')) {
            $copyable = false;
        }

        if (mb_strstr(mb_strtoupper($licenseName), 'EDLL')) {
            $copyable = false;
        }

        return ['copyable' => (boolean)$copyable];

    }
}
