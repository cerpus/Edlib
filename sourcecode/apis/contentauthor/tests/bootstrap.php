<?php

namespace Tests;

use Illuminate\Support\Facades\Artisan;

require __DIR__.'/../vendor/autoload.php';

class bootstrap
{
    use CreatesApplication;
}

// Make sure config and routes are not cached
$bs = new bootstrap();
$bs->createApplication();

Artisan::call('config:clear');
Artisan::call('route:clear');
