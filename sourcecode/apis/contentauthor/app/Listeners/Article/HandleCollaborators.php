<?php

namespace App\Listeners\Article;

use App\ACL\ArticleAccess;
use App\ArticleCollaborator;
use App\Events\ArticleWasSaved;

class HandleCollaborators
{
    use ArticleAccess;

    public function handle(ArticleWasSaved $event)
    {
        $article = $event->article->fresh();

        ArticleCollaborator::where('article_id', $article->id)->delete();
        $theCollaborators = $event->originalCollaborators;
        $theCollaborators->each(function ($newCollaborator) use ($article) {
            $collab = new ArticleCollaborator();
            $collab->article_id = $article->id;
            $collab->email = $newCollaborator;
            $collab->save();
        });
    }
}
