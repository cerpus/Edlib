<?php

declare(strict_types=1);

namespace App\Libraries\H5P;

use Illuminate\Contracts\Filesystem\Cloud;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use InvalidArgumentException;

/**
 * Manager class for H5P presave scripts.
 *
 * This should be the preferred option over accessing the filesystem directly.
 */
class H5pPresave
{
    public function __construct(private readonly Cloud $fs)
    {
    }

    public function hasScript(string $library): bool
    {
        return $this->fs->exists("$library/presave.js");
    }

    /**
     * @throws InvalidArgumentException if script does not exist
     */
    public function getScriptUrl(string $library): string
    {
        if (!$this->hasScript($library)) {
            throw new InvalidArgumentException(
                "The library $library does not have a presave script",
            );
        }

        return $this->fs->url("$library/presave.js");
    }

    /**
     * @throws InvalidArgumentException if script does not exist
     */
    public function getScriptContents(string $library): string
    {
        try {
            return $this->fs->get("$library/presave.js");
        } catch (FileNotFoundException $e) {
            throw new InvalidArgumentException(
                "The library $library does not have a presave script",
                previous: $e,
            );
        }
    }

    /**
     * @return list<string>
     */
    public function getAllLibrariesWithScripts(): array
    {
        return collect($this->fs->directories())
            ->filter(fn(string $dir): bool => $this->fs->exists("$dir/presave.js"))
            ->values()
            ->toArray();
    }
}
