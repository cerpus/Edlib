<?php

namespace App\Libraries\H5P\Image;

use App\Libraries\DataObjects\ContentStorageSettings;
use App\Libraries\H5P\Dataobjects\H5PAlterParametersSettingsDataObject;
use App\Libraries\H5P\Interfaces\CerpusStorageInterface;
use App\Libraries\H5P\Interfaces\H5PExternalProviderInterface;
use App\Libraries\H5P\Interfaces\H5PImageInterface;
use Exception;
use Illuminate\Http\File;

final class NdlaImageAdapter implements H5PImageInterface, H5PExternalProviderInterface
{
    private $mappings = [
        'startX' => 'cropStartX',
        'startY' => 'cropStartY',
        'endX' => 'cropEndX',
        'endY' => 'cropEndY',
        'width' => 'width',
        'height' => 'height',
    ];

    public function __construct(
        private readonly NdlaImageClient $client,
        private readonly CerpusStorageInterface $storage,
        private readonly string $url,
    ) {}

    public function mapParams($params, $originalKeys = false)
    {
        $mappings = !$originalKeys ? $this->mappings : array_flip($this->mappings);
        return collect($params)
            ->filter(function ($value, $index) use ($mappings) {
                return array_key_exists($index, $mappings);
            })
            ->mapWithKeys(function ($value, $index) use ($mappings, $originalKeys) {
                $key = !$originalKeys ? $mappings[$index] : $index;
                return [$key => $value];
            })
            ->toArray();
    }

    public function getImageUrlFromId($imageId, array $parameters, bool $useOriginalKeys): string
    {
        $imageParams = $this->mapParams($parameters, $useOriginalKeys);
        return $this->getImageUrl('/image-api/raw/id/' . $imageId, $imageParams);
    }

    private function getImageUrlFromName($imageName, array $parameters, bool $useOriginalKeys): string
    {
        $imageParams = $this->mapParams($parameters, $useOriginalKeys);
        return $this->getImageUrl('/image-api/raw/' . $imageName, $imageParams);
    }

    private function getImageUrl($path, $requestParameters)
    {
        return $this->url . $path . "?" . http_build_query($requestParameters);
    }

    public function isTargetType($mimeType, $pathToFile): bool
    {
        return $this->isImageMime($mimeType) && $this->isSameDomain($pathToFile);
    }

    private function isSameDomain($pathToFile): bool
    {
        return str_starts_with($pathToFile, $this->url);
    }

    private function isImageMime($mime): bool
    {
        return !empty($mime) && str_starts_with($mime, 'image/');
    }

    /**
     * @return array
     * @throws Exception
     */
    public function storeContent($values, $content)
    {
        $source = $values['path'];
        $tempFile = tempnam(sys_get_temp_dir(), 'h5p-');
        $this->client->get($source, [
            'sink' => $tempFile,
        ]);
        $file = new File($tempFile);
        $extension = $file->guessExtension();
        $fileName = md5($values['path']);
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
        return "image";
    }

    public function alterImageProperties($imageProperties, H5PAlterParametersSettingsDataObject $settings): object
    {
        if (empty($imageProperties->path)) {
            return $imageProperties;
        }

        $imageProperties->path = html_entity_decode($imageProperties->path);
        $url = parse_url($imageProperties->path);
        $query = [
            'width' => config('ndla.image.properties.width'),
        ];
        if (!empty($url['query'])) {
            parse_str($url['query'], $existingQuery);
            $query = array_merge($query, $existingQuery);
        }
        if (!$settings->useImageWidth) {
            unset($query['width']);
        }
        if (!empty($imageProperties->externalId) && str_contains($imageProperties->path, "/" . $imageProperties->externalId . "?")) {
            $imageProperties->path = $this->getImageUrlFromId($imageProperties->externalId, $query, true);
        } else {
            $imageProperties->path = $this->getImageUrlFromName(basename($url['path']), $query, true);
        }
        return $imageProperties;
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
            (string) mix('js/ndla-image.js'),
        ];
    }

    public function getConfigJs(): array
    {
        return [];
    }

    public function getBrowserConfig(): array
    {
        return [
            'searchUrl' => rtrim($this->url, '/') . '/image-api/v3/images',
            'detailsUrl' => rtrim($this->url, '/') . '/image-api/v3/images',
            'searchParams' => [
                'fallback' => config('ndla.image.searchparams.fallback'),
                'license' => config('ndla.image.searchparams.license'),
                'page-size' => config('ndla.image.searchparams.pagesize'),
            ],
        ];
    }
}
