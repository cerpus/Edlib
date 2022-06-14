<?php

namespace App\Libraries\H5P\Image;

use App\Libraries\DataObjects\ContentStorageSettings;
use App\Libraries\H5P\Interfaces\CerpusStorageInterface;
use App\Libraries\H5P\Interfaces\H5PExternalProviderInterface;
use App\Libraries\H5P\Interfaces\H5PImageAdapterInterface;
use Exception;
use GuzzleHttp\ClientInterface;
use Illuminate\Http\File;

class NDLAContentBrowser implements H5PImageAdapterInterface, H5PExternalProviderInterface
{
    private $mappings = [
        'startX' => 'cropStartX',
        'startY' => 'cropStartY',
        'endX' => 'cropEndX',
        'endY' => 'cropEndY',
        'width' => 'width',
        'height' => 'height',
    ];

    const FIND_IMAGES_URL = '/image-api/v2/images';
    const GET_IMAGE_URL = '/image-api/v2/images/%s';
    const GET_IMAGE_ID = '/image-api/raw/id/%s';
    const GET_IMAGE_NAME = '/image-api/raw/%s';

    public function __construct(
        private readonly ClientInterface $client,
        private readonly CerpusStorageInterface $storage,
    ) {
    }

    public function findImages($filterParameters)
    {
        $page = !empty($filterParameters['page']) ? $filterParameters['page'] : 1;
        $searchString = !empty($filterParameters['searchString']) ? $filterParameters['searchString'] : null;

        $request = $this->client->get(self::FIND_IMAGES_URL, [
            'query' => [
                'page' => $page,
                'query' => $searchString,
            ]
        ]);
        $images = $request->getBody()->getContents();

        return \response()->json(json_decode($images));

    }

    public function getImage($imageId)
    {
        $request = $this->client->get(sprintf(self::GET_IMAGE_URL, $imageId));
        $image = $request->getBody()->getContents();

        return \response()->json(json_decode($image));
    }

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
        return $this->getImageUrl(sprintf(self::GET_IMAGE_ID, $imageId), $imageParams);
    }

    public function getImageUrlFromName($imageName, array $parameters, bool $useOriginalKeys): string
    {
        $imageParams = $this->mapParams($parameters, $useOriginalKeys);
        return $this->getImageUrl(sprintf(self::GET_IMAGE_NAME, $imageName), $imageParams);
    }

    private function getImageUrl($path, $requestParameters)
    {
        return config('h5p.image.url') . $path . "?" . http_build_query($requestParameters);
    }

    public function isTargetType($mimeType, $pathToFile): bool
    {
        return $this->isImageMime($mimeType) && $this->isSameDomain($pathToFile);
    }

    private function isSameDomain($pathToFile)
    {
        return strpos($pathToFile, config('h5p.image.url')) === 0;
    }

    private function isImageMime($mime)
    {
        return !empty($mime) && strpos($mime, 'image/') === 0;
    }

    /**
     * @param $values
     * @param $content
     * @return array
     * @throws Exception
     */
    public function storeContent($values, $content)
    {
        $source = $values['path'];
        $tempFile = tempnam(sys_get_temp_dir(), 'h5p-');
        $this->client->get($source, [
            'sink' => $tempFile
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

    public function alterImageProperties($imageProperties, bool $includeWidthQuery): object
    {
        if (empty($imageProperties->path) ){
            return $imageProperties;
        }

        $imageProperties->path = html_entity_decode($imageProperties->path);
        $url = parse_url($imageProperties->path);
        $query = [
            'width' => config('h5p.image.properties.width'),
        ];
        if(!empty($url['query'])){
            parse_str($url['query'], $existingQuery);
            $query = array_merge($query, $existingQuery);
        }
        if (!$includeWidthQuery){
            unset($query['width']);
        }
        if (!empty($imageProperties->externalId) && strpos($imageProperties->path, "/" . $imageProperties->externalId . "?") !== false){
            $imageProperties->path = $this->getImageUrlFromId($imageProperties->externalId, $query, true);
        } else {
            $imageProperties->path = $this->getImageUrlFromName(basename($url['path']), $query, true);
        }
        return $imageProperties;
    }
}
