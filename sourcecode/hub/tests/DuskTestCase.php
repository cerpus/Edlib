<?php

declare(strict_types=1);

namespace Tests;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\TestCase as BaseTestCase;

use function assert;
use function env;
use function is_string;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;
    use DatabaseTruncation;

    /**
     * Create the RemoteWebDriver instance.
     */
    protected function driver(): RemoteWebDriver
    {
        $url = env('DUSK_DRIVER_URL') ?? 'http://host.docker.internal:9515';
        assert(is_string($url));

        return RemoteWebDriver::create($url, DesiredCapabilities::chrome());
    }
}
