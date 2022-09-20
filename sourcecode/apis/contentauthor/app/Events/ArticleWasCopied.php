<?php

namespace App\Events;

use App\Article;
use Illuminate\Queue\SerializesModels;

class ArticleWasCopied extends Event
{
    use SerializesModels;

    public function __construct(Article $article, $reason)
    {
        $this->article = $article;
        $this->reason = $reason;
    }
}
