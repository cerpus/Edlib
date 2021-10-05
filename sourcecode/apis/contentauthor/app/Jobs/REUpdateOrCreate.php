<?php

namespace App\Jobs;

use App\Content;
use Cerpus\REContentClient\ContentClient;
use Cerpus\REContentClient\Exceptions\MissingDataException;
use Cerpus\REContentClient\REContent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class REUpdateOrCreate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $content;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Content $content)
    {
        $this->content = $content;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (config("feature.enable-recommendation-engine")) {
            $this->content->refresh();
            /** @var ContentClient $reCClient */
            $reCClient = app(ContentClient::class);
            $action = $this->content->determineREAction();
            switch ($action) {
                case Content::RE_ACTION_UPDATE_OR_CREATE:
                    $reContent = $this->content->toREContent();

                    try {
                        // Add this content to RE
                        $reCClient->updateOrCreate($reContent);

                        // Remove old version(s) from RE
                        $parentIds = $this->content->getPublicParentsIds();
                        $parents = $reCClient->multiExists($parentIds);
                        foreach ($parents as $parentId) {
                            $oldContent = app(REContent::class);
                            $oldContent->setId($parentId);
                            $reCClient->removeIfExists($oldContent);
                        }
                    } catch (MissingDataException $e) {
                        Log::error(__METHOD__."({$e->getCode()}) {$e->getMessage()} ", $e->getTrace());
                    }

                    break;

                case Content::RE_ACTION_REMOVE:
                    try {
                        // 1. Remove content that is cleared for removal.
                        $reContent = app(REContent::class);
                        $reContent->setId($this->content->getPublicId());
                        $reCClient->removeIfExists($reContent);

                        // 2. Activate previous public version
                        // Commenting out. People do not have a realtionship with versions
                        // It will just seem like something is impossible to remove from the RE.
                        /*
                        if (!empty($parentIds = $this->content->getParentIds())) {
                            $content = Content::findContentById($parentIds[0]);
                            if ($content) {
                                $reContent = $content->toREContent();
                                $reCClient->updateOrCreate($reContent);
                            }
                        }
                        */

                    } catch (MissingDataException $e) {
                        Log::error(__METHOD__."({$e->getCode()}) {$e->getMessage()} ", $e->getTrace());
                    }
                    break;

                default:
                    Log::debug(__METHOD__.": Action was: '$action'. Don't know what to do, bailing out. ContentID: {$this->content->id}");

                    break;
            }
        } else {
            Log::debug("Recommendation engine is not enabled.");
        }
    }
}
