<?php

declare(strict_types=1);

namespace App\Libraries\H5P\Audio;

use App\Libraries\H5P\Interfaces\H5PAudioInterface;

final readonly class NullAudioAdapter implements H5PAudioInterface
{
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
