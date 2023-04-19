<?php

namespace App\Libraries\H5P\Video;

use App\Libraries\DataObjects\ContentStorageSettings;
use App\Libraries\H5P\Interfaces\CerpusStorageInterface;
use App\Libraries\H5P\Interfaces\H5PExternalProviderInterface;
use App\Libraries\H5P\Interfaces\H5PVideoInterface;
use BadMethodCallException;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Utils as GuzzleUtils;
use Illuminate\Http\File;
use InvalidArgumentException;

class NDLAVideoAdapter implements H5PVideoInterface, H5PExternalProviderInterface
{
    public const GET_VIDEOS = '/v1/accounts/%s/videos';
    public const GET_VIDEO = '/v1/accounts/%s/videos/%s';
    public const GET_VIDEO_SOURCES = self::GET_VIDEO . '/sources';

    public const MIME_TYPE = 'video/Brightcove';

    public const VIDEO_URL = 'https://bc/%s';

    private ClientInterface $client;
    private string $accountId;
    private CerpusStorageInterface $storage;

    public function __construct(Client $client, string $accountId)
    {
        if ($accountId === '') {
            throw new InvalidArgumentException('$accountId cannot be an empty string');
        }

        $this->client = $client;
        $this->accountId = $accountId;
    }

    public function upload($file, $fileHash)
    {
        throw new BadMethodCallException('not implemented');
    }

    public function getVideoDetails($videoId)
    {
        $request = $this->client->get(sprintf(self::GET_VIDEO, $this->accountId, urlencode($videoId)));
        $details = $request->getBody()->getContents();

        return json_decode($details);
    }

    public function edit()
    {
        // TODO: Implement edit() method.
    }

    public function isVideoReadyForStreaming($videoId)
    {
        // TODO: Implement isVideoReadyForStreaming() method.
    }

    public function getStreamingUrl($videoId)
    {
        // TODO: Implement getStreamingUrl() method.
    }

    public function getAdapterMimeType()
    {
        return self::MIME_TYPE;
    }

    public function findVideos($filterParameters)
    {
        $searchString = !empty($filterParameters['query']) ? $this->buildSearchQuery($filterParameters['query']) : null;

        $request = $this->client->get(sprintf(self::GET_VIDEOS, $this->accountId), [
            'query' => $searchString,
        ]);

        $videos = $request->getBody()->getContents();

        return \response()->json(json_decode($videos));
    }

    private function buildSearchQuery($queryObject)
    {
        if (empty($queryObject)) {
            return null;
        }

        $query = [];
        if (!empty($queryObject['query'])) {
            $query['query'] = sprintf('text:%s', $queryObject['query']);
        }
        if (!empty($queryObject['limit']) && isset($queryObject['offset'])) {
            $query['limit'] = $queryObject['limit'];
            $query['offset'] = $queryObject['offset'];
        }
        return $query;
    }

    public function getVideo($videoId)
    {
        return \response()->json($this->getVideoDetails($videoId));
    }

    private function getVideoSources($videoId)
    {
        $request = $this->client->get(sprintf(self::GET_VIDEO_SOURCES, $this->accountId, urlencode($videoId)));
        $response = $request->getBody()->getContents();

        return GuzzleUtils::jsonDecode($response);
    }

    public function downloadVideo($videoId)
    {
        $videoSourceList = $this->getVideoSources($videoId);
        $videoSource = collect($videoSourceList)
            ->filter(function ($source) {
                return !empty($source->container) && $source->container === "MP4";
            })
            ->sortBy('encoding_rate')
            ->pluck('src')
            ->first();

        $client = resolve(Client::class);
        $tempFile = tempnam(sys_get_temp_dir(), 'h5p-');
        $client->get($videoSource, [
            'sink' => $tempFile
        ]);

        return $tempFile;
    }

    public function isTargetType($mimeType, $pathToFile): bool
    {
        return $this->isVideoMime($mimeType) && $this->isBrightCove($pathToFile);
    }

    private function isVideoMime($mime)
    {
        return !empty($mime) && $mime === self::MIME_TYPE;
    }

    private function isBrightCove($path)
    {
        return !is_null($this->getVideoIdFromPath($path));
    }

    public function getVideoIdFromPath($path)
    {
        !preg_match('/https:\/\/bc\/(ref:[a-z0-9]+|\d+)/', $path, $matches);
        return $matches[1] ?? null;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function storeContent($source, $content, $setVideo = null)
    {
        if (!preg_match('/https:\/\/bc\/(ref:[a-z0-9]+|\d+)/', $source['path'], $matches)) {
            throw new Exception("No video id found");
        }
        $localFile = $this->downloadVideo($matches[1]);
        $file = new File($localFile);
        $extension = $file->guessExtension();
        $mimeType = $file->getMimeType();
        $fileName = md5($source['path']);
        $filePath = sprintf(ContentStorageSettings::CONTENT_FULL_PATH, $content['id'], $this->getType(), $fileName, $extension);

        if (!$this->storage->storeContentOnDisk($filePath, fopen($localFile, "r"))) {
            throw new Exception("Could not store file on disk");
        }
        unlink($localFile);

        return [
            'path' => sprintf(ContentStorageSettings::CONTENT_LOCAL_PATH, $this->getType(), $fileName, $extension),
            'mime' => $mimeType,
        ];
    }

    public function getType(): string
    {
        return "video";
    }

    public function setStorage(CerpusStorageInterface $storage)
    {
        $this->storage = $storage;
    }
}
