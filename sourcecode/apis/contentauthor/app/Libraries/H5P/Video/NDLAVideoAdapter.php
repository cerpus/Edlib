<?php

namespace App\Libraries\H5P\Video;

use App\Libraries\DataObjects\ContentStorageSettings;
use App\Libraries\H5P\Interfaces\CerpusStorageInterface;
use App\Libraries\H5P\Interfaces\H5PExternalProviderInterface;
use App\Libraries\H5P\Interfaces\H5PVideoInterface;
use BadMethodCallException;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Utils as GuzzleUtils;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;

use function asset;
use function mix;

class NDLAVideoAdapter implements H5PVideoInterface, H5PExternalProviderInterface
{
    public const GET_VIDEOS = '/v1/accounts/%s/videos';
    public const GET_VIDEO = '/v1/accounts/%s/videos/%s';
    public const GET_VIDEO_SOURCES = self::GET_VIDEO . '/sources';

    public const MIME_TYPE = 'video/Brightcove';

    public const VIDEO_URL = 'https://bc/%s';

    public function __construct(
        private readonly Client $client,
        private readonly string $accountId,
    ) {
        if ($accountId === '') {
            throw new InvalidArgumentException('$accountId cannot be an empty string');
        }
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
            'sink' => $tempFile,
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

    public function getVideoIdFromPath($path): ?string
    {
        preg_match('!^https://bc/(?:0/|360/|ref:|)([a-z0-9]+)$!', $path, $matches);
        return $matches[1] ?? null;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function storeContent($source, $content, $setVideo = null)
    {
        $videoId = $this->getVideoIdFromPath($source['path'])
            ?? throw new Exception("No video id found");
        $localFile = $this->downloadVideo($videoId);
        $file = new File($localFile);
        $extension = $file->guessExtension();
        $mimeType = $file->getMimeType();
        $fileName = md5($source['path']);
        $filePath = sprintf(ContentStorageSettings::CONTENT_FULL_PATH, $content['id'], $this->getType(), $fileName, $extension);

        $storage = app()->make(CerpusStorageInterface::class);
        if (!$storage->storeContentOnDisk($filePath, fopen($localFile, "r"))) {
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

    public function getViewCss(): array
    {
        return [];
    }

    public function getViewScripts(): array
    {
        return [
            asset('js/videos/brightcove.js'),
        ];
    }

    public function getEditorCss(): array
    {
        $css = [
            (string) mix('css/ndlah5p-editor.css'),
        ];

        $isAdmin = Session::get('isAdmin');
        if (!$isAdmin) {
            $css[] = asset('css/ndlah5p-youtube.css');
        }

        return $css;
    }

    public function getEditorScripts(): array
    {
        return [
            (string) mix("js/ndla-video.js"),
            asset('js/videos/brightcove.js'),
        ];
    }

    public function getConfigJs(): array
    {
        return [];
    }
}
