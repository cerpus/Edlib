<?php


namespace App\Libraries;

class ContentAuthorStorage
{
    private string $assetsBaseUrl;

    public function __construct(string $cdnPrefix)
    {
        $this->assetsBaseUrl = !empty($cdnPrefix) ? $cdnPrefix : route('content.asset', null, true);
    }

    public function getAssetUrl(string $path): string
    {
        return rtrim($this->assetsBaseUrl, '/') . '/' . ltrim($path, '/');
    }

    public function getAssetsBaseUrl(): string
    {
        return $this->assetsBaseUrl;
    }
}