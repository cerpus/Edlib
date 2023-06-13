<?php

declare(strict_types=1);

namespace App\Libraries\H5P\Video;

use App\Libraries\H5P\Interfaces\H5PVideoInterface;
use BadMethodCallException;

/**
 * Do no transcoding or nothing.
 */
final class NullVideoAdapter implements H5PVideoInterface
{
    public function upload($file, $fileHash): object
    {
        return (object) [
            // value doesn't matter, it will only be used to check transcoding
            // status or get details that we don't have
            'videoId' => base64_encode(random_bytes(24)),
        ];
    }

    public function getVideoDetails($videoId)
    {
        throw new BadMethodCallException('not implemented');
    }

    public function edit()
    {
        throw new BadMethodCallException('not implemented');
    }

    public function isVideoReadyForStreaming($videoId): bool
    {
        return true;
    }

    public function getStreamingUrl($videoId)
    {
        throw new BadMethodCallException('not implemented');
    }

    public function getAdapterMimeType()
    {
        throw new BadMethodCallException('not implemented');
    }

    public function findVideos($filterParameters)
    {
        throw new BadMethodCallException('not implemented');
    }

    public function getVideo($videoId)
    {
        throw new BadMethodCallException('not implemented');
    }

    public function downloadVideo($videoId)
    {
        throw new BadMethodCallException('not implemented');
    }
}
