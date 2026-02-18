<?php

namespace App\Jobs;

use App\H5PFile;
use App\Libraries\H5P\Traits\FileUploadTrait;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class H5PFileUpload implements ShouldQueue
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
    public function __construct($contentId, $h5pFileId)
    {
        $this->contentId = $contentId;
        $this->h5pFileId = $h5pFileId;
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        return ['H5PFileUpload', 'h5pContentId:' . $this->contentId, 'h5pFileId:' . $this->h5pFileId];
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        /** @var H5PFile $file */
        $file = H5PFile::ofFileUploadFromContent($this->contentId)
            ->where('id', $this->h5pFileId)
            ->where('state', H5PFile::FILE_CLONEFILE)
            ->first();
        if (!is_null($file)) {
            $this->processFile($file);
        }
    }
}
