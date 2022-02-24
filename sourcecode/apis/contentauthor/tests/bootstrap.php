<?php

namespace Tests;

use Illuminate\Support\Facades\Artisan;

$root = realpath(dirname(__DIR__));

require $root . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";
unset($root);

class bootstrap
{
    use CreatesApplication;
}

// Make sure config and routes are not cached
$bs = new bootstrap();
$bs->createApplication();

Artisan::call('config:clear');
Artisan::call('route:clear');
