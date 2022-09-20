<?php

namespace App\Events;

use App\Article;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ArticleWasSaved extends Event
{
    use SerializesModels;

    public $article;
    public $request;
    public $originalCollaborators;
    public $authId;
    public $reason;
    public $theSession;

    public function __construct(Article $article, Request $request, Collection $originalCollaborators, $authId, $reason, $theSession)
    {
        $this->article = $article;
        $this->request = $request;
        $this->originalCollaborators = $originalCollaborators;
        $this->authId = $authId;
        $this->reason = $reason;
        $this->theSession = $theSession;
    }
}
