<?php

namespace App\Libraries\H5P\Interfaces;

interface H5PExternalProviderInterface
{
    public function isTargetType($mimeType, $pathToFile): bool;

    public function storeContent($source, $content);

    public function getType(): string;

    public function setStorage(CerpusStorageInterface $storage);
}
