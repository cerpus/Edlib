<?php

use Illuminate\Support\Facades\Artisan;
use Tests\CreatesApplication;

$root = realpath(dirname(__DIR__));

/** @var \Composer\Autoload\ClassLoader $autoloading */
$autoloading = require $root . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";
unset($root);
class BootstrapTests
{
    use CreatesApplication;
}

// Make sure config and routes are not cached
$bs = new BootstrapTests();
$bs->createApplication();
Artisan::call('config:clear');
Artisan::call('route:clear');
