<?php

namespace App\Gdpr\Handlers;

use App\Collaborator;
use App\H5PCollaborator;
use App\ArticleCollaborator;
use App\Messaging\Messages\EdlibGdprDeleteMessage;

class ShareProcessor implements Processor
{
    // Remove all shares to any email in $deletionRequest->payload->emails
    //
    public function handle(EdlibGdprDeleteMessage $edlibGdprDeleteMessage)
    {
        $emails = $this->deletionRequest->payload->emails ?? false;

        if ($emails) {
            $this->handleArticleShares($emails);
            $this->handleH5PShares($emails);
            $this->handleCollaboratableTypes($emails);
        }
    }

    protected function handleArticleShares($emails)
    {
        ArticleCollaborator::whereIn('email', $emails)->delete();
    }

    protected function handleH5PShares($emails)
    {
        H5PCollaborator::whereIn('email', $emails)->delete();
    }

    protected function handleCollaboratableTypes($emails)
    {
        Collaborator::whereIn('email', $emails)->delete();
    }
}
