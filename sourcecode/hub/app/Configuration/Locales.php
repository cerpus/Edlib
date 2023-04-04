<?php

declare(strict_types=1);

namespace App\Configuration;

use function array_combine;
use function array_map;
use function locale_get_display_name;

final class Locales
{
    /**
     * @param string[] $locales
     */
    public function __construct(private readonly array $locales)
    {
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
            fn ($locale) => locale_get_display_name($locale, $displayLocale)
                ?: $locale,
            $this->locales,
        );

        return array_combine($this->locales, $displayNames);
    }
}
