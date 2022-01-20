<?php

namespace App\Libraries\H5P\Video;


use App\Libraries\H5P\Interfaces\H5PVideoInterface;
use GuzzleHttp\ClientInterface;
use InvalidArgumentException;

class StreampsAdapter implements H5PVideoInterface
{

    const UPLOAD_VIDEO = '/v1/video/put/';
    const VIDEO_DETAILS = '/video/%s/%s.json';
    const SEE_VIDEO = '/video/%s/%s';
    const MIME_TYPE = 'video/Streamps';

    private ClientInterface $client;
    private string $appId;
    private string $appKey;

    public function __construct(ClientInterface $client, string $appId, string $appKey)
    {
        if ($appId === '') {
            throw new InvalidArgumentException('$appId cannot be an empty string');
        }

        if ($appKey === '') {
            throw new InvalidArgumentException('$appKey cannot be an empty string');
        }

        $this->client = $client;
        $this->appId = $appId;
        $this->appKey = $appKey;
    }

    public function upload($file, $fileHash)
    {
        $signedParams = $this->signUrl(self::UPLOAD_VIDEO . '?md5=' . urlencode($fileHash));
        $options = [
            'body' => is_resource($file) ? $file : fopen($file, 'r'),
        ];
        $response = $this->client->request("PUT", $signedParams, $options);
        return json_decode($response->getBody()->getContents());
    }

    private function signUrl($path, $expiry = null)
    {
        // Add the appId GET parameter
        if (strpos($path, '?') !== false) {
            $path .= '&';
        } else {
            $path .= '?';
        }
        $path .= 'appId=' . urlencode($this->appId);

        // If expiry is not provided, expire the URL after 24 hours
        if (!$expiry) {
            $expiry = time() + (3600 * 24);
        }
        $path .= '&signExpiry=' . $expiry;

        // Sign the path
        $signature = hash_hmac('sha256', $path, $this->appKey);
        // Concat path to endpoint
        return $path . '&signature=' . urlencode($signature);
    }

    public function edit()
    {
        // TODO: Implement edit() method.
    }

    public function isVideoReadyForStreaming($videoId)
    {
        $data = $this->getVideoDetails($videoId);
        return in_array($data->state, ["playable", "finished"]);
    }

    public function getVideoDetails($videoId)
    {
        $signedUrl = $this->signUrl(sprintf(self::VIDEO_DETAILS, $this->appId, $videoId));
        $response = $this->client->request("GET", $signedUrl);
        return json_decode($response->getBody());
    }

    public function getStreamingUrl($videoId)
    {
        return $this->client->getConfig('base_uri') . $this->signUrl(sprintf(self::SEE_VIDEO, $this->appId, $videoId));
    }

    public function getAdapterMimeType()
    {
        return self::MIME_TYPE;
    }

    public function findVideos($filterParameters)
    {
        // TODO: Implement findVideos() method.
    }

    public function getVideo($videoId)
    {
        // TODO: Implement getVideo() method.
    }

    public function downloadVideo($videoId)
    {
        throw new \Exception("Not supported at the moment");
    }
}
