<?php


namespace App\Jobs;


use App\H5PContent;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use App\Libraries\H5P\Interfaces\H5PVideoInterface;
use App\Libraries\H5P\Video\NDLAVideoAdapter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ReplaceVideoRefId implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $h5p;
    public $videoAdapter;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(H5PContent $h5p)
    {
        $this->h5p = $h5p;
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        return ['H5PVideoReplaceRef', 'h5pContentId:'.$this->h5p->id];
    }

    /**
     * Execute the job.
     *
     * @param H5PAdapterInterface $adapter
     * @param H5PVideoInterface $video
     * @return void
     * @throws \Exception
     */
    public function handle(H5PAdapterInterface $adapter, H5PVideoInterface $video)
    {
        if ($adapter->getAdapterName() === 'ndla'){
            $this->videoAdapter = $video;
            $content = json_decode($this->h5p->parameters);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception(json_last_error_msg());
            }
            $parameters = $this->traverseParameters(collect($content));
            $this->h5p->parameters = $parameters->toJson();
            $this->h5p->filtered = '';
            $this->h5p->save();
        }
    }

    /**
     * @param Collection $parameters
     * @return Collection
     */
    private function traverseParameters(Collection $parameters)
    {
        /** @var Collection $parameters */
        $processedParams = $parameters->map(function ($value) {
            if (!empty($value->mime) && $this->videoAdapter->isTargetType($value->mime, $value->path)) {
                $value = $this->replaceRef($value);
            }

            if (!!(array)$value && (is_array($value) || is_object($value))) {
                $value = $this->traverseParameters(collect($value));
            }
            return $value;
        });
        return $processedParams;
    }

    private function replaceRef($values)
    {
        $data = $this->videoAdapter->getVideoDetails($this->videoAdapter->getVideoIdFromPath($values->path));
        if (!empty($data)) {
            $values->path = sprintf(NDLAVideoAdapter::VIDEO_URL, $data->id);
        }
        return $values;
    }
}
