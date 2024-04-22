<?php

declare(strict_types=1);

namespace Tests;

use App\Jobs\RebuildContentIndex;
use Facebook\WebDriver\Chrome\ChromeOptions;
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

        $headlessDisabled = env('DUSK_HEADLESS_DISABLED', true);
        $options = new ChromeOptions();

        if (!$headlessDisabled) {
            $options->addArguments([
                '--disable-gpu',
                '--headless',
                '--no-sandbox',
                '--window-size=1920,1080',
            ]);
        }

        return RemoteWebDriver::create(
            $url,
            DesiredCapabilities::chrome()
                ->setCapability(ChromeOptions::CAPABILITY, $options)
                ->setCapability('acceptInsecureCerts', true)
        );
    }

    public function setUp(): void
    {
        parent::setUp();
        RebuildContentIndex::dispatchSync();
    }
}
