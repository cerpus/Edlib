<?php

namespace App\Libraries\H5P\Interfaces;

use App\H5PLibrary;

interface CerpusStorageInterface
{
    public function getDisplayPath(bool $fullUrl = true);

    public function getEditorDisplayPath();

    public function getContentPath($id, $file);

    public function getAjaxPath();

    public function alterLibraryFiles($files);

    public function deleteLibrary(H5PLibrary $library);

    public function getFileUrl(string $path);

    /**
     * @param resource $resource
     */
    public function storeContentOnDisk(string $filePath, $resource);
}
