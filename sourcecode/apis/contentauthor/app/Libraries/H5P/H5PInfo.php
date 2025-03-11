<?php

namespace App\Libraries\H5P;

use App\H5PContent;
use App\H5PLibrary;

class H5PInfo
{
    public function getInformation(H5PContent $content): array
    {
        $libraryInfo = H5PLibrary::where('name', $content->library->name)->latestVersion()->first();
        return [
            'title' =>  $content['title'],
            'h5pLibrary' => [
                'name' => $content->library->name,
                'majorVersion' => $content->library->major_version,
                'minorVersion' => $content->library->minor_version,
                'latestMajorVersion' => $libraryInfo->major_version,
                'latestMinorVersion' => $libraryInfo->minor_version,
                'upgradable' => $content->library->isUpgradable(),
            ],
        ];
    }
}
