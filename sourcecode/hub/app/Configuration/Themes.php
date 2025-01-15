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
            'dark',
        ];
    }

    public function getName(string $theme): string|null
    {
        $name = match ($theme) {
            'edlib' => trans('messages.theme-edlib'),
            'dark' => trans('messages.theme-dark'),
            default => null,
        };

        assert(!is_array($name));

        return $name;
    }

    public function getDefault(): string
    {
        return 'edlib';
    }

    /**
     * @return array<string, string>
     */
    public function getTranslatedMap(): array
    {
        return array_combine($this->all(), array_map(
            fn(string $key) => $this->getName($key) ?? $key,
            $this->all(),
        ));
    }
}
