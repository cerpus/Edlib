<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;
use App\H5PFile;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use App\Libraries\H5P\Traits\FileUploadTrait;

class H5PFilesUpload implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use FileUploadTrait;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($contentId)
    {
        $this->contentId = $contentId;
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        return ['H5PFileUpload', 'h5pContentId:' . $this->contentId];
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        /** @var Collection $files */
        $files = H5PFile::ofFileUploadFromContent($this->contentId)->get();
        if ($files->isNotEmpty()) {
            $this->filesystem = resolve('H5PFilesystem');
            $files
                ->filter(function ($file) {
                    return $file->state === H5PFile::FILE_CLONEFILE;
                })
                ->each(function (H5PFile $file) {
                    $this->processFile($file);
                });

            $unprocessed = H5PFile::ofFileUploadFromContent($this->contentId)
                ->get()
                ->filter(function ($file) {
                    return $file->state === H5PFile::FILE_CLONEFILE;
                });
            if ($unprocessed->isNotEmpty()) {
                $attempts = $this->attempts() + 1;
                Log::debug(__METHOD__ . ": There are still unprocessed files. Retrying. This is attempt #$attempts.");

                $this->release(config('h5p.upload-media-files-timeout'));
            }
        }
    }
}
