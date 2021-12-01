<?php

namespace App\Gdpr\Handlers;


use App\CollaboratorContext;
use App\Messaging\Messages\EdlibGdprDeleteMessage;

class ContextShareProcessor implements Processor
{
    public function handle(EdlibGdprDeleteMessage $edlibGdprDeleteMessage)
    {
        $deleteCount = CollaboratorContext::where('collaborator_id', $edlibGdprDeleteMessage->userId)->delete();
        $contextShareCount = CollaboratorContext::where('collaborator_id', $edlibGdprDeleteMessage->userId)->count();

        $edlibGdprDeleteMessage->stepCompleted('ContextShareProcessor', "Removed $deleteCount Context Shares. $contextShareCount context shares left.");
    }
}
