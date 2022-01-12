<?php

namespace App\Libraries\NDLA\Importers\Handlers\H5P;

use App\Libraries\ContentAuthorStorage;
use Illuminate\Filesystem\FilesystemAdapter;
use GuzzleHttp\Client;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;

class DownloadExport
{
    protected $h5p;
    private $payload;

    private FilesystemAdapter $tmpDisk;
    private ContentAuthorStorage $contentAuthorStorage;

    /** @var ZipArchiveAdapter */
    private $h5pFile;

    const H5PSource = '/sites/default/files/h5p/exports/%s-%s.h5p';
    const tmpFile = 'export/%s.h5p';
    const tmpFolder = 'export/%s';
    const contentFolder = 'content/%s';

    public function __construct(ContentAuthorStorage $contentAuthorStorage)
    {
        $this->contentAuthorStorage = $contentAuthorStorage;
        $this->tmpDisk = $contentAuthorStorage->getH5pTmpDisk();
    }

    public function handle($params, $h5p, $jsonPayload)
    {
        $this->h5p = (object)$h5p;
        $this->payload = $jsonPayload;

        if( !$this->isIframeEmbed()){
            return $params;
        }

        $this->getExportFromNDLA();
        $this->h5pFile = new ZipArchiveAdapter($this->tmpDisk->path(sprintf(self::tmpFile, $this->payload->nodeId)));
        $this->extractFiles();
        $this->moveFiles();

        $this->deleteArchive();
        return $params;
    }

    private function isIframeEmbed()
    {
        return $this->h5p->library['machineName'] === "H5P.IFrameEmbed";
    }

    /**
     * @throws \Exception
     */
    private function getExportFromNDLA()
    {
        $client = new Client([
            'base_uri' => config('ndla.baseUrl'),
        ]);
        $exportFile = $client->get($this->getComputedH5PUrl(), ['sink' => tmpfile()]);
        if( $this->tmpDisk->put($this->getComputedTmpFile(), $exportFile->getBody()->getContents()) !== true){
            throw new \Exception("Could not write to the tmp folder");
        }
    }

    private function getComputedH5PUrl()
    {
        return sprintf(self::H5PSource, $this->h5p->slug, $this->payload->nodeId);
    }

    private function getComputedTmpFolder()
    {
        return sprintf(self::tmpFolder, $this->payload->nodeId);
    }

    private function getComputedTmpFile()
    {
        return sprintf(self::tmpFile, $this->payload->nodeId);
    }

    private function getComputedTmpContentFolder()
    {
        return sprintf(self::contentFolder, $this->h5p->id);
    }

    private function extractFiles()
    {
        $path = $this->getComputedTmpFolder();
        if( !$this->tmpDisk->exists($path)){
            $this->tmpDisk->makeDirectory($path);
        }
        $this->h5pFile->getArchive()->extractTo($this->tmpDisk->path($path));
    }

    private function deleteArchive(){
        $this->tmpDisk->deleteDirectory($this->getComputedTmpFolder());
        $this->tmpDisk->delete($this->getComputedTmpFile());
    }

    private function moveFiles()
    {
        $core = resolve(\H5PCore::class);
        if( !$this->contentAuthorStorage->getBucketDisk()->exists($this->getComputedTmpContentFolder()) ){
            $this->contentAuthorStorage->getBucketDisk()->makeDirectory($this->getComputedTmpContentFolder());
        }
        $core->fs->moveContentDirectory(
            $this->tmpDisk->path($this->getComputedTmpFolder()),
            $this->h5p->id
        );

    }
}

