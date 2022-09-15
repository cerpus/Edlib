<?php

namespace App\Libraries\H5P\Interfaces;

interface H5PVideoInterface
{
    public function upload($file, $fileHash);

    public function getVideoDetails($videoId);

    public function edit();

    public function isVideoReadyForStreaming($videoId);

    public function getStreamingUrl($videoId);

    public function getAdapterMimeType();

    public function findVideos($filterParameters);

    public function getVideo($videoId);

    public function downloadVideo($videoId);
}
