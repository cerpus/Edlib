<?php

namespace App\Gdpr\Handlers;


use App\CollaboratorContext;
use Cerpus\Gdpr\Models\GdprDeletionRequest;

class ContextShareProcessor implements Processor
{
    public function handle(GdprDeletionRequest $deletionRequest)
    {
        $deletionRequest->log('processing', "Handling Context Shares.");

        $deleteCount = CollaboratorContext::where('collaborator_id', $deletionRequest->payload->userId)->delete();
        $contextShareCount = CollaboratorContext::where('collaborator_id', $deletionRequest->payload->userId)->count();
        $deletionRequest->log('processing', "Removed $deleteCount Context Shares. $contextShareCount context shares left.");

        $deletionRequest->log('processing', "Handled Context Shares.");
    }
}
