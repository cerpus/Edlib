<?php

namespace App\Gdpr\Handlers;

use App\Collaborator;
use App\H5PCollaborator;
use App\ArticleCollaborator;
use App\Messaging\Messages\EdlibGdprDeleteMessage;

class ShareProcessor implements Processor
{
    public function handle(EdlibGdprDeleteMessage $edlibGdprDeleteMessage)
    {
        $emails = $edlibGdprDeleteMessage->emails ?? false;
        $articleShareDeleted = 0;
        $h5pSharesDeleted = 0;
        $collaborateTypeDeleted = 0;

        if ($emails) {
            $articleShareDeleted = $this->handleArticleShares($emails);
            $h5pSharesDeleted = $this->handleH5PShares($emails);
            $collaborateTypeDeleted = $this->handleCollaboratableTypes($emails);
        }

        $edlibGdprDeleteMessage->stepCompleted('ShareProcessor', "Removed $articleShareDeleted emails from Article shares. Removed $h5pSharesDeleted emails from H5P shares. Removed $collaborateTypeDeleted emails from other shareable content.");
    }

    protected function handleArticleShares($emails): int
    {
        return ArticleCollaborator::whereIn('email', $emails)->delete();
    }

    protected function handleH5PShares($emails): int
    {
        return H5PCollaborator::whereIn('email', $emails)->delete();
    }

    protected function handleCollaboratableTypes($emails): int
    {
        return Collaborator::whereIn('email', $emails)->delete();
    }
}
