<?php

namespace App\Gdpr\Handlers;


use App\H5PResult;
use Cerpus\Gdpr\Models\GdprDeletionRequest;

class H5PResultProcessor implements Processor
{
    public function handle(GdprDeletionRequest $deletionRequest)
    {
        $deletionRequest->log('processing', "Handling H5P Results.");

        $deleteCount = H5PResult::where('user_id', $deletionRequest->payload->userId)->delete();
        $resultsCount = H5PResult::where('user_id', $deletionRequest->payload->userId)->count();
        $deletionRequest->log('processing', "Deleted $deleteCount H5P results. There is $resultsCount results left.");

        $deletionRequest->log('processing', "Handled H5P Results.");
    }
}
