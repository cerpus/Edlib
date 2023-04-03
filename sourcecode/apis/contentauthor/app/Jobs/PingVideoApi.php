<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;
use Event;
use App\H5PContent;
use App\H5PContentsVideo;
use Illuminate\Bus\Queueable;
use App\Events\VideoSourceChanged;
use App\Exceptions\NoFilesException;
use Cerpus\VersionClient\VersionData;
use Illuminate\Queue\SerializesModels;
use Cerpus\VersionClient\VersionClient;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Exceptions\UnknownH5PPackageException;
use App\Libraries\H5P\Helper\H5PPackageProvider;
use App\Libraries\H5P\Interfaces\H5PVideoInterface;

class PingVideoApi implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $contentVideo;
    protected $versionClient;
    /** @var H5PVideoInterface */
    private $adapter;
    public $processedChildren = 0;
    public $tries = 20;
    public $timeout = 1800;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(H5PContentsVideo $contentVideo, VersionClient $versionClient)
    {
        $this->contentVideo = $contentVideo;
        $this->versionClient = $versionClient;
    }

    private function handleChildren($children)
    {
        if (!empty($children)) {
            /** @var VersionData $child */
            foreach ($children as $child) {
                /** @var H5PContent $content */
                $content = H5PContent::find($child->getExternalReference());
                if (!is_null($content)) {
                    if ($content->contentVideos()->first() === null) {
                        $this->updateContent($content);
                        $this->processedChildren++;
                        if ($child->getChildren() !== []) {
                            $this->handleChildren($child->getChildren());
                        }
                    }
                }
            }
        }
    }

    /**
     * @return bool
     * @throws NoFilesException
     */
    private function updateContent(H5PContent $h5pContent)
    {
        try {
            $library = H5PPackageProvider::make($h5pContent->library()->first()->name, $h5pContent->parameters);
            $success = $library->alterSource($this->contentVideo->source_file, [
                $this->adapter->getStreamingUrl($this->contentVideo->video_id),
                $this->adapter->getAdapterMimeType(),
            ]);
            if ($success !== true) {
                throw new NoFilesException();
            }

            $h5pContent->parameters = json_encode($library->getPackageStructure());
            $h5pContent->filtered = '';
            if ($h5pContent->save()) {
                Event::dispatch(new VideoSourceChanged($h5pContent, $this->contentVideo->source_file));
            }
        } catch (UnknownH5PPackageException $exception) {
        }
        return true;
    }

    /**
     * Execute the job.
     *
     * @return bool
     */
    public function handle(H5PVideoInterface $adapter)
    {
        if ($adapter->isVideoReadyForStreaming($this->contentVideo->video_id)) {
            /** @var H5PContent $h5pcontent */
            $h5pcontent = H5PContent::find($this->contentVideo->h5p_content_id);
            if (empty($h5pcontent)) {
                return false;
            }
            $this->adapter = $adapter;
            try {
                $this->updateContent($h5pcontent);
                if (!empty($h5pcontent->version_id)) {
                    $versions = $this->versionClient->getVersion($h5pcontent->version_id);
                    $this->handleChildren($versions->getChildren());
                }
                return true;
            } catch (NoFilesException $exception) {
                Log::error(sprintf("No files found for content id '%s'.", $h5pcontent->id));
                return false;
            }
        }

        // Back off.
        // Max delay should be $tries * pingDelay => 20 * 10 = 200 seconds using the default values.
        $timeOut = (int)config('h5p.video.pingDelay');
        $attempts = $this->attempts() + 1;
        if ($attempts > 0) {
            $timeOut = $timeOut * $attempts;
        }
        Log::debug(__METHOD__ . ": Will ping Streamps Video API again in $timeOut seconds. This is attempt #$attempts.");

        $this->release($timeOut);

        return false;
    }
}
