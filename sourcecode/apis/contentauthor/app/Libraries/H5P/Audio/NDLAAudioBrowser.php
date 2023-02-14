<?php

namespace App\Libraries\H5P\Audio;

use App\Libraries\DataObjects\ContentStorageSettings;
use App\Libraries\H5P\Interfaces\CerpusStorageInterface;
use App\Libraries\H5P\Interfaces\H5PAudioInterface;
use App\Libraries\H5P\Interfaces\H5PExternalProviderInterface;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\File;

class NDLAAudioBrowser implements H5PAudioInterface, H5PExternalProviderInterface
{
    private $client;
    /** @var CerpusStorageInterface */
    private $storage;

    public const FIND_AUDIOS_URL = '/audio-api/v1/audio';
    public const GET_AUDIO_URL = '/audio-api/v1/audio/%s';

    public function __construct(Client $client)
    {
        $this->client = $client;
    }


    public function findAudio($filterParameters)
    {
        $searchString = !empty($filterParameters['query']) ? $this->buildSearchQuery($filterParameters['query']) : null;
        if (!empty($filterParameters['fallback'])) {
            $searchString['fallback'] = $filterParameters['fallback'];
        }

        $request = $this->client->get(self::FIND_AUDIOS_URL, [
            'query' => $searchString
        ]);
        $audios = $request->getBody()->getContents();

        return \response()->json(json_decode($audios));
    }

    public function getAudio($audioId, array $params = [])
    {
        $language = !empty($params['language']) ? $params['language'] : null;
        $request = $this->client->get(sprintf(self::GET_AUDIO_URL, $audioId), [
            'query' => [
                'language' => $language,
            ],
        ]);
        $audio = $request->getBody()->getContents();

        return \response()->json(json_decode($audio));
    }

    private function buildSearchQuery($queryObject): ?array
    {
        if (empty($queryObject)) {
            return null;
        }
        $queryObject = json_decode($queryObject, true);
        if (empty($queryObject['query'])) {
            unset($queryObject['query']);
        }
        if (!empty($queryObject['pageSize'])) {
            $queryObject['page-size'] = $queryObject['pageSize'];
            unset($queryObject['pageSize']);
        }
        if (empty($queryObject['language'])) {
            unset($queryObject['language']);
        }

        return $queryObject;
    }

    public function isTargetType($mimeType, $pathToFile): bool
    {
        return $this->isAudioMime($mimeType) && $this->isSameDomain($pathToFile);
    }

    private function isSameDomain($pathToFile)
    {
        $url = config('h5p.audio.url') ?: config('h5p.image.url');
        return strpos($pathToFile, $url) === 0;
    }

    private function isAudioMime($mime)
    {
        return !empty($mime) && strpos($mime, $this->getType() .'/') === 0;
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

    public function setStorage(CerpusStorageInterface $storage)
    {
        $this->storage = $storage;
    }
}
