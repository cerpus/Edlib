<?php

namespace App\Gdpr\Handlers;

use App\Collaborator;
use App\H5PCollaborator;
use App\ArticleCollaborator;
use Cerpus\Gdpr\Models\GdprDeletionRequest;

class ShareProcessor implements Processor
{
    protected $deletionRequest;

    // Remove all shares to any email in $deletionRequest->payload->emails
    //
    public function handle(GdprDeletionRequest $deletionRequest)
    {
        $this->deletionRequest = $deletionRequest;
        $deletionRequest->log('processing', "Handling Shares.");

        $emails = $this->deletionRequest->payload->emails ?? false;

        if ($emails) {
            $this->handleArticleShares($emails);
            $this->handleH5PShares($emails);
            $this->handleCollaboratableTypes($emails);
        }

        $deletionRequest->log('processing', "Handled Shares.");
    }

    protected function handleArticleShares($emails)
    {
        $deletedCount = ArticleCollaborator::whereIn('email', $emails)->delete();
        $articleSharesCount = ArticleCollaborator::whereIn('email', $emails)->count();
        $this->deletionRequest->log('processing', "Removed emails from $deletedCount Article Shares. $articleSharesCount Article Shares left.");
    }

    protected function handleH5PShares($emails)
    {
        $deletedCount = H5PCollaborator::whereIn('email', $emails)->delete();
        $h5pSharesCount = H5PCollaborator::whereIn('email', $emails)->count();
        $this->deletionRequest->log('processing', "Removed emails from $deletedCount H5P Shares. $h5pSharesCount H5P Shares left.");
    }

    protected function handleCollaboratableTypes($emails)
    {
        $deletedCount = Collaborator::whereIn('email', $emails)->delete();
        $theRestSharesCount = Collaborator::whereIn('email', $emails)->count();
        $this->deletionRequest->log('processing', "Removed email from $deletedCount other shareable content. $theRestSharesCount shares left.");
    }
}
