<?php

namespace App\Libraries\H5P\Interfaces;

use Symfony\Component\HttpFoundation\Response;

interface H5PDownloadInterface
{
    public function downloadContent(string $filename, string $title): Response;
}
