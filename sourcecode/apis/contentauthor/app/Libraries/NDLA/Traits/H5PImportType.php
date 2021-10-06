<?php

namespace App\Libraries\NDLA\Traits;

use App\Libraries\NDLA\Importers\Handlers\H5P\DownloadFiles;
use App\Libraries\NDLA\Importers\Handlers\H5P\DownloadExport;

trait H5PImportType
{
    private $postFilters = [
        DownloadFiles::class,
        DownloadExport::class
    ];

    protected $h5pData;
}
