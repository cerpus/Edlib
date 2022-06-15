<?php

namespace Tests\Helpers;

use App\Libraries\ContentAuthorStorage;

trait VersionedH5PTrait
{
    protected bool $cleanupInitiated = false;

    public function setupContentDirectories($contentId)
    {
        if ($this->cleanupInitiated !== true) {
            $this->deleteDirectoriesAfterTest();
            $this->cleanupInitiated = true;
        }

        collect(['audios', 'files', 'images', 'videos'])
            ->each(function ($dirName) use ($contentId) {
                $directory = "/content/$contentId/$dirName";
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
