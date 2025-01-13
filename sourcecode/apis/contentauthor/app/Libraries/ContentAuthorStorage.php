<?php

namespace App\Libraries;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Str;
use League\Flysystem\StorageAttributes;

/**
 * @deprecated Please don't add more stuff, we want to migrate to using
 *     Laravel's filesystem abstraction directly.
 */
class ContentAuthorStorage
{
    public function copyFolder(
        FilesystemAdapter $sourceDisk,
        FilesystemAdapter $destinationDisk,
        string $sourceFolder,
        string $destinationFolder,
        array $ignoredFiles = [],
    ) {
        collect($sourceDisk->listContents($sourceFolder, true))
            ->filter(function (StorageAttributes $fileProperties) use ($ignoredFiles) {
                $file = basename($fileProperties->path());
                return !in_array($file, $ignoredFiles);
            })
            ->each(function (StorageAttributes $fileProperties) use ($destinationDisk, $destinationFolder, $sourceDisk, $sourceFolder) {
                if (!$fileProperties->isDir()) {
                    $file = Str::after($fileProperties->path(), Str::after($sourceFolder, '/'));
                    $destinationDisk->put("{$destinationFolder}/{$file}", $sourceDisk->readStream("{$sourceFolder}/{$file}"));
                }
            });
    }
}
