<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Filesystem\Cloud;
use Illuminate\Http\RedirectResponse;

final readonly class ContentAssetController
{
    public function __construct(private Cloud $fs) {}

    public function __invoke(string $path): RedirectResponse
    {
        return new RedirectResponse($this->fs->url($path));
    }
}
