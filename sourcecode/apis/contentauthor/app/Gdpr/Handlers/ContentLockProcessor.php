<?php

namespace App\Gdpr\Handlers;


use App\ContentLock;
use Cerpus\Gdpr\Models\GdprDeletionRequest;

class ContentLockProcessor implements Processor
{
    // Remove all content locks
    //
    public function handle(GdprDeletionRequest $deletionRequest)
    {
        $deletionRequest->log('processing', "Handling Content Locks.");

        $authId = $deletionRequest->payload->userId;
        $emails = $deletionRequest->payload->emails ?? false;

        $deletedCount = ContentLock::where('auth_id', $authId)->delete();
        $contentLockCount = ContentLock::where('auth_id', $authId)->count();
        $deletionRequest->log('processing', "Deleted $deletedCount content locks by AuthId. $contentLockCount locks left.");

        if ($emails) {
            $deletedCount = ContentLock::whereIn('email', $emails)->delete();
            $contentLockCount = ContentLock::whereIn('email', $emails)->count();
            $deletionRequest->log('processing', "Deleted $deletedCount content locks by email. $contentLockCount locks left.");
        }

        $deletionRequest->log('processing', "Handled Content Locks.");
    }
}
