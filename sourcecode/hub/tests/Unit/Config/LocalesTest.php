<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use App\Configuration\Locales;
use Illuminate\Contracts\Foundation\Application;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

final class LocalesTest extends TestCase
{
    /**
     * @param string[] $allowedLocales
     * @throws Exception
     */
    #[TestWith(['en', 'en', ['en', 'nb'], 'en'], 'gets "en" when requesting "en" and "en", "nb" are available')]
    #[TestWith(['en', 'en_GB', ['en']], 'gets "en" when requesting "en_GB" and only "en" is available')]
    #[TestWith(['und', 'de', ['en', 'nb']], 'gets "und" when language is not available')]
    public function testGetsBestAvailableLocale(string $expected, string $attempted, array $allowedLocales): void
    {
        $application = $this->createMock(Application::class);
        $application->method('getLocale')->willReturn('und');

        $locales = new Locales($application, $allowedLocales);

        $this->assertSame($expected, $locales->getBestAvailable($attempted));
    }
}
