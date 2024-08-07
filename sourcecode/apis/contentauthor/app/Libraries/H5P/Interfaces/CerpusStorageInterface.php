<?php

namespace App\Libraries\H5P\Interfaces;

interface CerpusStorageInterface
{
    public function getDisplayPath(bool $fullUrl = true);

    public function getEditorDisplayPath();

    public function getLibrariesPath();

    public function getContentPath($id, $file);

    public function getAjaxPath();

    public function alterLibraryFiles($files);

    public function getFileUrl(string $path);

    /**
     * @param resource $resource
     */
    public function storeContentOnDisk(string $filePath, $resource);
}
