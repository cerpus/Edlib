<?php

namespace App\Gdpr\Handlers;

use App\H5PResult;
use App\Messaging\Messages\EdlibGdprDeleteMessage;

class H5PResultProcessor implements Processor
{
    public function handle(EdlibGdprDeleteMessage $edlibGdprDeleteMessage)
    {
        $deleteCount = H5PResult::where('user_id', $edlibGdprDeleteMessage->userId)->delete();
        $edlibGdprDeleteMessage->stepCompleted('H5PResultProcessor', "Deleted $deleteCount H5P results");
    }
}
