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
        $contentLockCount = ContentLock::where('auth_id', $authId)->count();

        if ($emails) {
            $deletedCount = ContentLock::whereIn('email', $emails)->delete();
            $contentLockCount = ContentLock::whereIn('email', $emails)->count();
        }
    }
}
