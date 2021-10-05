<?php

namespace App\Libraries\H5P\Storage;

use App\H5PContentsVideo;
use App\H5PLibrary;
use App\Jobs\PingVideoApi;
use App\Libraries\H5P\H5Plugin;
use App\Libraries\H5P\Helper\UrlHelper;
use App\Libraries\H5P\Interfaces\CerpusStorageInterface;
use App\Libraries\H5P\Interfaces\H5PDownloadInterface;
use App\Libraries\H5P\Interfaces\H5PVideoInterface;
use Cerpus\VersionClient\VersionClient;

class H5PStorage extends \H5PDefaultStorage implements H5PDownloadInterface, CerpusStorageInterface
{

    private $path, $altEditorPath;
    private $publicRoot;

    public function __construct($path, $alteditorpath = NULL)
    {
        parent::__construct($path, $alteditorpath);
        // Set H5P storage path
        $this->path = $path;
        $this->altEditorPath = $alteditorpath;
        $this->publicRoot = env('TEST_FS_ROOT', $_SERVER['DOCUMENT_ROOT']);
    }

    public function cloneContentFile($file, $fromId, $toId)
    {
        $path = explode('/', $file);
        $type = $path[0];

        if (config('h5p.video.enable') === true && strtolower($type) === "videos") {
            if ($fromId === 'editor') {
                $sourcepath = $this->getEditorPath();
            } else {
                $sourcepath = "{$this->path}/content/{$fromId}";
            }
            $sourcepath .= '/' . $file;

            /** @var H5PVideoInterface $adapter */
            $adapter = app(H5PVideoInterface::class);
            $uploadJsonData = $adapter->upload($sourcepath, md5_file($sourcepath));

            $h5pContentsVideo = H5PContentsVideo::firstOrCreate([
                'h5p_content_id' => $toId,
                'video_id' => $uploadJsonData->videoId,
                'source_file' => $file,
            ]);

            PingVideoApi::dispatch($h5pContentsVideo, app(VersionClient::class))->onQueue('streamps_messages');

        }

        parent::cloneContentFile($file, $fromId, $toId);
    }

    private function getEditorPath()
    {
        return ($this->altEditorPath !== NULL ? $this->altEditorPath : "{$this->path}/editor");
    }

    public function getPath($relative = true)
    {
        return ($relative ? $this->publicRoot : "") . config('h5p.storage.publicPath');
    }

    public function downloadContent($filename, $title)
    {
        $path = $this->getPath() . DIRECTORY_SEPARATOR . "exports" . DIRECTORY_SEPARATOR . $filename;
        return response()->download($path, $filename);
    }

    public function getDisplayPath(bool $fullUrl = true)
    {
        return ($fullUrl ? UrlHelper::getCurrentBaseUrl() : "") . $this->getPath(false);
    }

    public function getEditorDisplayPath()
    {
        return $this->getPath(false) . '/editor';
    }

    public function getLibrariesPath()
    {
        return $this->getPath(false) . '/libraries/';
    }

    public function getContentPath($id, $file)
    {
        return url(sprintf($this->getPath(false) . '/content/%s/%s', $id, $file));
    }

    public function getAjaxPath()
    {
        return $this->getDisplayPath(false);
    }

    public function alterLibraryFiles($files)
    {
        return $files;
    }

    public function deleteLibrary(H5PLibrary $library)
    {
        return \H5PCore::deleteFileTree($this->getPath() . '/libraries/' . $library->getLibraryString(true));
    }

    public function storeContentOnDisk(string $filePath, $resource)
    {
        $fileInfo = pathinfo($this->getPath() . "/" . $filePath);
        return $this->saveFileFromZip($fileInfo['dirname'],  $fileInfo['basename'], $resource);
    }
}
