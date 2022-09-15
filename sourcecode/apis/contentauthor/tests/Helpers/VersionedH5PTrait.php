<?php

namespace Tests\Helpers;

use Illuminate\Support\Facades\Storage;

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
                Storage::disk()->makeDirectory($directory);
            });
    }

    public function deleteDirectoriesAfterTest()
    {
        $this->beforeApplicationDestroyed(function () {
            Storage::disk()->deleteDirectory('content');
        });
    }
}
