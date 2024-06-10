<?php

namespace App\Console\Libraries;

use App\Libraries\H5P\Framework;

/**
 * Override permission functions to enable CLI use
 */
class CliH5pFramework extends Framework
{
    public function hasPermission($permission, $id = null): bool
    {
        return true;
    }

    public function mayUpdateLibraries(): bool
    {
        return true;
    }
}
