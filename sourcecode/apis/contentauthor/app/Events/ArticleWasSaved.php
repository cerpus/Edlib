<?php

namespace App\Events;

use App\Article;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

class ArticleWasSaved extends Event
{
    use SerializesModels;

    public $article;
    public $request;
    public $authId;
    public $reason;
    public $theSession;

    public function __construct(Article $article, Request $request, $authId, $reason, $theSession)
    {
        $this->article = $article;
        $this->request = $request;
        $this->authId = $authId;
        $this->reason = $reason;
        $this->theSession = $theSession;
    }
}
