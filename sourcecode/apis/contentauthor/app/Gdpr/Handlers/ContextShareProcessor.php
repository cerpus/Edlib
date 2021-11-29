<?php

namespace App\Gdpr\Handlers;


use App\CollaboratorContext;
use App\Messaging\Messages\EdlibGdprDeleteMessage;

class ContextShareProcessor implements Processor
{
    public function handle(EdlibGdprDeleteMessage $edlibGdprDeleteMessage)
    {
        CollaboratorContext::where('collaborator_id', $edlibGdprDeleteMessage->userId)->delete();
        CollaboratorContext::where('collaborator_id', $edlibGdprDeleteMessage->userId)->count();
    }
}
