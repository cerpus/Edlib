<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use App\Configuration\Locales;
use Generator;
use Illuminate\Contracts\Foundation\Application;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

final class LocalesTest extends TestCase
{
    /**
     * @param string[] $allowedLocales
     * @throws Exception
     */
    #[DataProvider('provideBestLocalesData')]
    public function testGetsBestAvailableLocale(string $expected, string $attempted, array $allowedLocales): void
    {
        $application = $this->createMock(Application::class);
        $application->method('getLocale')->willReturn('und');

        $locales = new Locales($application, $allowedLocales);

        $this->assertSame($expected, $locales->getBestAvailable($attempted));
    }

    /**
     * @return Generator<int|string, array{string, string, array<string>}, null, null>
     */
    public static function provideBestLocalesData(): Generator
    {
        yield ['en', 'en', ['en', 'nb'], 'en'];
        yield ['en', 'en_GB', ['en']];
        yield ['und', 'de', ['en', 'nb']];
        yield ['und', 'de', ['en', 'nb']];
    }
}
