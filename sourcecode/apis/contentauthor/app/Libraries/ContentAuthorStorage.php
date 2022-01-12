<?php


namespace App\Libraries;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ContentAuthorStorage
{
    private string $assetsBaseUrl;

    public function __construct(string $cdnPrefix)
    {
        $this->assetsBaseUrl = !empty($cdnPrefix) ? $cdnPrefix : route('content.asset', null, true);
    }

    public function getAssetUrl(string $path, bool $private = false): string
    {
        if ($private) {
            return route('content.asset', ['path' => $path], true);
        }

        return rtrim($this->assetsBaseUrl, '/') . '/' . ltrim($path, '/');
    }

    public function getAssetsBaseUrl(): string
    {
        return $this->assetsBaseUrl;
    }

    public function getBucketDiskName(): string
    {
        return Storage::getDefaultCloudDriver();
    }

    public function getH5pTmpDiskName(): string
    {
        return 'h5pTmp';
    }

    public function getBucketDisk(): FilesystemAdapter
    {
        return Storage::disk($this->getBucketDiskName());
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
    )
    {
        collect($sourceDisk->listContents($sourceFolder, true))
            ->filter(function ($fileProperties) use ($ignoredFiles) {
                $file = $fileProperties['basename'];
                return !in_array($file, $ignoredFiles);
            })
            ->each(function ($fileProperties) use ($destinationDisk, $destinationFolder, $sourceDisk, $sourceFolder) {
                if ($fileProperties['type'] !== 'dir') {
                    $file = Str::after($fileProperties['path'], Str::after($sourceFolder, '/'));
                    $destinationDisk->putStream("{$destinationFolder}/{$file}", $sourceDisk->readStream("{$sourceFolder}/{$file}"));
                }
            });
    }
}
