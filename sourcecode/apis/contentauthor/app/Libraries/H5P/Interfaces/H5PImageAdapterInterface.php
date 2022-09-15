<?php

namespace App\Libraries\H5P\Interfaces;

interface H5PImageAdapterInterface
{
    public function findImages($filterParameters);

    public function getImage($imageId);

    public function getImageUrlFromId($imageId, array $parameters, bool $useOriginalKeys): string;

    public function getImageUrlFromName($imageName, array $parameters, bool $useOriginalKeys): string;

    public function alterImageProperties($imageProperties, bool $includeWidthQuery): object;
}
