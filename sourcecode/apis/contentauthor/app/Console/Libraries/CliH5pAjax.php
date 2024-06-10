<?php

namespace App\Console\Libraries;

use App;
use H5peditor;
use H5PEditorEndpoints;
use RuntimeException;

class CliH5pAjax
{
    public static function installLibrary(string $machineName): string
    {
        if (!App::runningInConsole()) {
            throw new RuntimeException('Interface not supported');
        }

        $editor = app(H5peditor::class);
        $ajax = $editor->ajax;

        ob_start();
        $_SERVER['REQUEST_METHOD'] = 'POST'; // Install and update are only allowed for POST requests
        $ajax->action(H5PEditorEndpoints::LIBRARY_INSTALL, '', $machineName);

        return ob_get_clean();
    }
}
