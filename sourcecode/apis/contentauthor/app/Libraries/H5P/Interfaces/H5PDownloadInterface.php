<?php

declare(strict_types=1);

namespace App\Libraries\H5P\Interfaces;

interface H5PDownloadInterface
{
    /**
     * @return resource
     */
    public function downloadContent(string $filename);
}
