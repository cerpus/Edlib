<?php

declare(strict_types=1);

namespace App\Libraries\H5P\Dataobjects;

class H5PView
{
    public function __construct(
        private array $scripts,
        private array $styles,
        private object $settings,
    ) {}

    public function getScripts($objectsToArray = true): array
    {
        return $objectsToArray ? self::objectsToArray($this->scripts) : $this->scripts;
    }

    /**
     * Get an HTML script tag defining the settings.
     */
    public function getSettings(string $settingsName = 'H5PIntegration'): string
    {
        $settings = json_encode($this->settings, JSON_THROW_ON_ERROR);

        return "<script>$settingsName = $settings</script>";
    }

    public function getStyles($objectsToArray = true): array
    {
        return $objectsToArray ? self::objectsToArray($this->styles) : $this->styles;
    }

    private static function objectsToArray(iterable $objects): array
    {
        $res = [];
        foreach ($objects as $a) {
            $res[] = is_object($a) ? $a->path : $a;
        }
        return array_values($res);
    }
}
