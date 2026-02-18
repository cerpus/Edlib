<?php

namespace App\Libraries\H5P\File;

use App\Libraries\DataObjects\ContentStorageSettings;
use App\Libraries\H5P\Interfaces\CerpusStorageInterface;
use App\Libraries\H5P\Interfaces\H5PExternalProviderInterface;
use App\Libraries\H5P\Video\NDLAVideoAdapter;
use Exception;
use GuzzleHttp\Client;

class NDLATextTrack implements H5PExternalProviderInterface
{
    public function __construct(
        private readonly Client $client,
        private readonly CerpusStorageInterface $storage,
        private readonly NDLAVideoAdapter $video,
    ) {}

    public function isTargetType($mimeType, $pathToFile): bool
    {
        return parse_url($pathToFile, PHP_URL_HOST) === 'bc' ||
            $this->isVttMime($mimeType) && $this->isSameDomain($pathToFile);
    }

    private function isSameDomain($pathToFile)
    {
        return preg_match('/https?:\/\/[0-9a-z-]*\.?brightcove(cdn)?\.com/', $pathToFile);
    }

    private function isVttMime($mime)
    {
        return $mime === 'text/webvtt';
    }

    public function storeContent($values, $content)
    {
        $source = $values['path'];
        if (parse_url($source, PHP_URL_HOST) === 'bc') {
            parse_str(parse_url($source, PHP_URL_QUERY), $result);
            ['id' => $id, 'track' => $track] = $result;
            $source = collect($this->video->getVideoDetails($id)->text_tracks ?? [])
                ->firstOrFail(fn($item) => $item->id === $track)
                ->sources[0]
                ->src;
        }
        $tempFile = tempnam(sys_get_temp_dir(), 'h5p-');
        $this->client->get($source, [
            'sink' => $tempFile,
        ]);
        $extension = 'vtt';
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
        return 'file';
    }
}
