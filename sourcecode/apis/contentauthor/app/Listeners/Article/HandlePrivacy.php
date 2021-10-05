<?php

namespace App\Listeners\Article;

use App\ACL\ArticleAccess;
use App\Events\ArticleWasSaved;

class HandlePrivacy
{
    use ArticleAccess;

    public function handle(ArticleWasSaved $event)
    {
        /** @var \App\Article $article */
        $article = $event->article->fresh();
        $request = $event->request;
        $private = $request->get('share', "PRIVATE");
        $isPrivate = (mb_strtoupper($private) === 'PRIVATE');
        $article->is_private = $isPrivate; // Yey it made a comeback...keeping it was worth it! :)
        $article->save();
    }
}
