<?php

namespace App\Libraries\H5P\Interfaces;

interface H5PDownloadInterface
{
    public function downloadContent($filename, $title);
}
