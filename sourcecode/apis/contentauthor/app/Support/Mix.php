<?php

declare(strict_types=1);

namespace App\Support;

class Mix extends \Illuminate\Foundation\Mix
{
    public function __invoke($path, $manifestDirectory = ''): string
    {
        // Using a manifest directory is broken, see webpack.mix.js
        return (string) parent::__invoke('build/' . $path, $manifestDirectory);
    }
}
