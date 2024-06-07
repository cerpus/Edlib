<?php

namespace App\Console\Libraries;

use App;
use App\Libraries\ContentAuthorStorage;
use App\Libraries\H5P\Framework;
use GuzzleHttp\Client;
use H5PPermission;
use Illuminate\Support\Facades\DB;

/**
 * Override permission functions to enable CLI use
 */
class CliH5pFramework extends Framework
{
    public function __construct()
    {
        $contentAuthorStorage = app(ContentAuthorStorage::class);

        parent::__construct(
            new Client(),
            DB::connection()->getPdo(),
            $contentAuthorStorage->getH5pTmpDisk()
        );
    }

    public function hasPermission($permission, $id = null): bool
    {
        return match ($permission) {
            H5PPermission::INSTALL_RECOMMENDED, H5PPermission::UPDATE_LIBRARIES => App::runningInConsole(),
            default => false,
        };
    }

    public function mayUpdateLibraries(): bool
    {
        return App::runningInConsole();
    }
}
