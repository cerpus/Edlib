<?php

namespace App\Libraries;

use Illuminate\Contracts\Filesystem\Cloud;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Flysystem\StorageAttributes;

/**
 * @deprecated Please don't add more stuff, we want to migrate to using
 *     Laravel's filesystem abstraction directly.
 */
class ContentAuthorStorage
{
    public function __construct(private readonly Cloud $fs)
    {
    }

    public function getAssetUrl(string $path): string
    {
        return $this->fs->url($path);
    }

    public function getAssetsBaseUrl(): string
    {
        return rtrim($this->fs->url(''), '/');
    }

    public function getH5pTmpDiskName(): string
    {
        return 'h5pTmp';
    }

    public function getH5pTmpDisk(): FilesystemAdapter
    {
        return Storage::disk($this->getH5pTmpDiskName());
    }

    public function copyFolder(
        FilesystemAdapter $sourceDisk,
        FilesystemAdapter $destinationDisk,
        string $sourceFolder,
        string $destinationFolder,
        array $ignoredFiles = []
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
