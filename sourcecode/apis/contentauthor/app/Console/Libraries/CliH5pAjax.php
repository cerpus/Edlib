<?php

namespace App\Console\Libraries;

use App;
use App\Libraries\ContentAuthorStorage;
use App\Libraries\H5P\EditorAjax;
use App\Libraries\H5P\EditorStorage;
use H5PCore;
use H5peditor;
use H5PEditorEndpoints;
use H5PFileStorage;
use RuntimeException;

class CliH5pAjax
{
    public static function installLibrary(string $machineName): string
    {
        if (!App::runningInConsole()) {
            throw new RuntimeException('Interface not supported');
        }

        // H5P Editor preforms permission checks when installing/updating libraries, so we override
        // the permission functions in a CLI version of the Framework
        $cliFramework = app(CliH5pFramework::class);

        $fileStorage = app(H5PFileStorage::class);
        $contentAuthorStorage = app(ContentAuthorStorage::class);
        $core = new H5PCore($cliFramework, $fileStorage, $contentAuthorStorage->getAssetsBaseUrl());
        $core->aggregateAssets = true; // @phpstan-ignore property.notFound
        $editor = new H5peditor($core, app(EditorStorage::class), app(EditorAjax::class));

        $ajax = $editor->ajax;

        ob_start();
        $_SERVER['REQUEST_METHOD'] = 'POST'; // Install and update are only allowed for POST requests
        $ajax->action(H5PEditorEndpoints::LIBRARY_INSTALL, '', $machineName);

        return ob_get_clean();
    }
}
