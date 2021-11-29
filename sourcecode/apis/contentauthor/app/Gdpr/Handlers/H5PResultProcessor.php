<?php

namespace App\Gdpr\Handlers;


use App\H5PResult;
use App\Messaging\Messages\EdlibGdprDeleteMessage;
use Cerpus\Gdpr\Models\GdprDeletionRequest;

class H5PResultProcessor implements Processor
{
    public function handle(EdlibGdprDeleteMessage $edlibGdprDeleteMessage)
    {
        $deleteCount = H5PResult::where('user_id', $edlibGdprDeleteMessage->userId)->delete();
        $resultsCount = H5PResult::where('user_id', $edlibGdprDeleteMessage->userId)->count();
    }
}
