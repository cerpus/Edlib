<?php

namespace Tests;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\TestCase as BaseTestCase;

use function assert;
use function env;
use function is_string;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;

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
