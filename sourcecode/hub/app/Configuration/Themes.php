<?php

declare(strict_types=1);

namespace App\Configuration;

final readonly class Themes
{
    /**
     * @return array<string>
     */
    public function all(): array
    {
        return [
            'edlib',
            'light',
            'dark',
        ];
    }

    public function getName(string $theme, string $locale): string|null
    {
        return match ($theme) {
            'edlib' => 'Edlib',
            'light' => match ($locale) {
                'nb', 'no' => 'Bootstrap lys',
                default => 'Bootstrap Light',
            },
            'dark' => match ($locale) {
                'nb', 'no' => 'Bootstrap mørk',
                default => 'Bootstrap Dark',
            },
            default => null,
        };
    }

    public function getDefault(): string
    {
        return 'edlib';
    }

    /**
     * @return array<string, string>
     */
    public function getTranslatedMap(string $locale): array
    {
        return array_combine($this->all(), array_map(
            fn(string $key) => $this->getName($key, $locale) ?? $key,
            $this->all(),
        ));
    }
}
