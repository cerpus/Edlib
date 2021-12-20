<?php

namespace Tests\Traits;

use App\H5PContent;
use App\H5PContentLibrary;
use App\Libraries\ContentAuthorStorage;

trait VersionedH5PTrait
{
    protected $cleanupInitiated = false;

    protected $originalH5P;

    public function setUpOriginalH5P($params = [], $license = 'PRIVATE', $copyable = false) {
        //Create a h5p with a file attached and file structure
        $createParams = array_merge(['parameters' => '{}', 'license' => $license], $params);
        $this->originalH5P = H5PContent::factory()->create($createParams);
        H5PContentLibrary::factory()->create(['content_id' => $this->originalH5P->id]);

        $this->setupContentDirectories($this->originalH5P->id);

        collect(['audios', 'files', 'images', 'videos'])
            ->each(function ($dirName) {
                $directory = DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR . $this->originalH5P->id . DIRECTORY_SEPARATOR . $dirName;
                $fromFile = base_path() . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'tree.jpg';
                app(ContentAuthorStorage::class)->getBucketDisk()->put($directory . '/tree.jpg', file_get_contents($fromFile));
            });

        $this->setUpLicensing($license, $copyable);

        return $this->originalH5P;
    }

    public function setupContentDirectories($contentId)
    {
        if ($this->cleanupInitiated !== true) {
            $this->deleteDirectoriesAfterTest();
            $this->cleanupInitiated = true;
        }

        collect(['audios', 'files', 'images', 'videos'])
            ->each(function ($dirName) use ($contentId) {
                $directory = DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR . $contentId . DIRECTORY_SEPARATOR . $dirName;
                app(ContentAuthorStorage::class)->getBucketDisk()->makeDirectory($directory);
            });

    }

    public function deleteDirectoriesAfterTest()
    {
        $this->beforeApplicationDestroyed(function () {
            app(ContentAuthorStorage::class)->getBucketDisk()->deleteDirectory('content');
        });
    }
}
