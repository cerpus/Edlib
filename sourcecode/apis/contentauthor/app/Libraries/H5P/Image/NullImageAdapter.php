<?php

declare(strict_types=1);

namespace App\Libraries\H5P\Image;

use App\Libraries\H5P\Dataobjects\H5PAlterParametersSettingsDataObject;
use App\Libraries\H5P\Interfaces\H5PImageInterface;

final class NullImageAdapter implements H5PImageInterface
{
    public function alterImageProperties($imageProperties, H5PAlterParametersSettingsDataObject $settings): object
    {
        return $imageProperties;
    }

    public function getViewCss(): array
    {
        return [];
    }

    public function getViewScripts(): array
    {
        return [];
    }

    public function getEditorCss(): array
    {
        return [];
    }

    public function getEditorScripts(): array
    {
        return [];
    }

    public function getConfigJs(): array
    {
        return [];
    }
}
