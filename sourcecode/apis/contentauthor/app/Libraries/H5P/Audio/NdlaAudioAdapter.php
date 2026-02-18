<?php

namespace App\Libraries\H5P\Audio;

use App\Libraries\DataObjects\ContentStorageSettings;
use App\Libraries\H5P\Interfaces\CerpusStorageInterface;
use App\Libraries\H5P\Interfaces\H5PAudioInterface;
use App\Libraries\H5P\Interfaces\H5PExternalProviderInterface;
use Exception;
use Illuminate\Http\File;

final readonly class NdlaAudioAdapter implements H5PAudioInterface, H5PExternalProviderInterface
{
    public function __construct(
        private NdlaAudioClient $client,
        private CerpusStorageInterface $storage,
        private string $url,
    ) {}

    public function isTargetType($mimeType, $pathToFile): bool
    {
        return $this->isAudioMime($mimeType) && $this->isSameDomain($pathToFile);
    }

    private function isSameDomain($pathToFile): bool
    {
        return str_starts_with($pathToFile, $this->url);
    }

    private function isAudioMime($mime): bool
    {
        return !empty($mime) && str_starts_with($mime, $this->getType() . '/');
    }

    public function storeContent($values, $content): array
    {
        $source = $values['path'];
        $tempFile = tempnam(sys_get_temp_dir(), 'h5p-');
        $this->client->get($source, [
            'sink' => $tempFile,
        ]);
        $file = new File($tempFile);
        $extension = $file->guessExtension();
        $fileName = md5($source);
        $filePath = sprintf(ContentStorageSettings::CONTENT_FULL_PATH, $content['id'], $this->getType(), $fileName, $extension);

        if (!$this->storage->storeContentOnDisk($filePath, fopen($tempFile, "r"))) {
            throw new Exception("Could not store file on disk");
        }
        unlink($tempFile);

        return [
            'path' => sprintf(ContentStorageSettings::CONTENT_LOCAL_PATH, $this->getType(), $fileName, $extension),
            'mime' => $values['mime'],
        ];
    }

    public function getType(): string
    {
        return "audio";
    }

    public function getClientDetailsUrl(): string
    {
        return rtrim($this->url, '/') . '/audio-api/v1/audio';
    }

    public function getViewCss(): array
    {
        return [];
    }

    public function getViewScripts(): array
    {
        return [];
    }

    public function getEditorCss(): array
    {
        return [];
    }

    public function getEditorScripts(): array
    {
        return [
            (string) mix('js/ndla-audio.js'),
        ];
    }

    public function getConfigJs(): array
    {
        return [];
    }

    public function getBrowserConfig(): array
    {
        return [
            'searchUrl' => rtrim($this->url, '/') . '/audio-api/v1/audio',
            'detailsUrl' => rtrim($this->url, '/') . '/audio-api/v1/audio',
            'searchParams' => [
                'fallback' => config('ndla.audio.searchparams.fallback'),
                'license' => config('ndla.audio.searchparams.license'),
                'page-size' => config('ndla.audio.searchparams.pagesize'),
            ],
        ];
    }
}
