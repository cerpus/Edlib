<?php

namespace App\Gdpr\Handlers;


use App\ContentLock;
use App\Messaging\Messages\EdlibGdprDeleteMessage;

class ContentLockProcessor implements Processor
{
    public function handle(EdlibGdprDeleteMessage $edlibGdprDeleteMessage)
    {
        $authId = $edlibGdprDeleteMessage->userId;
        $emails = $edlibGdprDeleteMessage->emails;

        $deletedCount = ContentLock::where('auth_id', $authId)->delete();
        $emailDeletedCount = 0;

        if ($emails) {
            $emailDeletedCount = ContentLock::whereIn('email', $emails)->delete();
        }

        $edlibGdprDeleteMessage->stepCompleted('ContentLockProcessor', "Deleted $deletedCount content locks by AuthId and $emailDeletedCount by email");
    }
}
