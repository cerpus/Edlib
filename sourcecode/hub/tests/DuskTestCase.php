<?php

declare(strict_types=1);

namespace Tests;

use App\Jobs\RebuildContentIndex;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Laravel\Dusk\ElementResolver;
use Laravel\Dusk\TestCase as BaseTestCase;

use function assert;
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
        $url = $_ENV['DUSK_DRIVER_URL'] ?? 'http://host.docker.internal:9515';
        assert(is_string($url));

        $options = new ChromeOptions();
        $options->addArguments([
            '--headless',
            '--disable-gpu',
            '--window-size=1920,1080',
            '--disable-search-engine-choice-screen',
            '--start-maximized',
            '--disable-infobars',
            '--disable-dev-shm-usage',
        ]);

        $capabilities = DesiredCapabilities::chrome()
            ->setCapability(ChromeOptions::CAPABILITY, $options);

        return RemoteWebDriver::create($url, $capabilities);
    }

    public function setUp(): void
    {
        parent::setUp();
        RebuildContentIndex::dispatchSync();
    }

    public function newBrowser($driver): Browser
    {
        // Use 'html' as root element for selectors instead of default 'body'
        return new Browser($driver, new ElementResolver($driver, 'html'));
    }
}
