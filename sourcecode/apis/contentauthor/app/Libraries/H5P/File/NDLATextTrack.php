<?php


namespace App\Libraries\H5P\File;


use App\Libraries\DataObjects\ContentStorageSettings;
use App\Libraries\H5P\Interfaces\CerpusStorageInterface;
use App\Libraries\H5P\Interfaces\H5PExternalProviderInterface;
use Exception;
use GuzzleHttp\Client;

class NDLATextTrack implements H5PExternalProviderInterface
{

    private $client;
    /** @var CerpusStorageInterface */
    private $storage;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function isTargetType($mimeType, $pathToFile): bool
    {
        return $this->isVttMime($mimeType) && $this->isSameDomain($pathToFile);
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
        $tempFile = tempnam(sys_get_temp_dir(), 'h5p-');
        $this->client->get($source, [
            'sink' => $tempFile
        ]);
        $extension = 'vtt';
        $fileName = md5($source);
        $filePath = sprintf(ContentStorageSettings::CONTENT_FULL_PATH, $content['id'], $this->getType(), $fileName, $extension);

        if( !$this->storage->storeContentOnDisk($filePath, fopen($tempFile, "r"))){
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

    public function setStorage(CerpusStorageInterface $storage)
    {
        $this->storage = $storage;
    }
}
