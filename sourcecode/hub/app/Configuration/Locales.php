<?php

declare(strict_types=1);

namespace App\Configuration;

use Illuminate\Contracts\Foundation\Application;

use function array_combine;
use function array_map;
use function locale_get_display_name;
use function locale_lookup;

final class Locales
{
    /**
     * @param string[] $locales
     */
    public function __construct(
        private readonly Application $application,
        private readonly array $locales,
    ) {}

    public function getBestAvailable(string $locale): string
    {
        return locale_lookup(
            $this->locales,
            $locale,
            defaultLocale: $this->application->getLocale(),
        ) ?? $this->application->getLocale();
    }

    /**
     * @return string[]
     */
    public function all(): array
    {
        return $this->locales;
    }

    /**
     * Get a map of locale identifiers and their corresponding display names in
     * the given locale.
     * @return array<string, string>
     */
    public function getTranslatedMap(string $displayLocale): array
    {
        $displayNames = array_map(
            fn($locale) => locale_get_display_name($locale, $displayLocale)
                ?: $locale,
            $this->locales,
        );

        return array_combine($this->locales, $displayNames);
    }
}
