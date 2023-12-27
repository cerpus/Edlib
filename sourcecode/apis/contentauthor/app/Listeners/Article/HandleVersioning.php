<?php

namespace App\Listeners\Article;

use App\Article;
use App\Content;
use App\ContentVersions;
use App\Events\ArticleWasCopied;
use App\Events\ArticleWasSaved;
use App\Listeners\AbstractHandleVersioning;

class HandleVersioning extends AbstractHandleVersioning
{
    protected Article $article;

    /**
     * Handle the event.
     */
    public function handle(ArticleWasSaved|ArticleWasCopied $event): void
    {
        $this->article = $event->article->fresh();

        $this->handleSave($this->article, $event->reason);
    }

    public function getParentVersionId()
    {
        if (is_null($this->article->parent_id)) {
            return null;
        }

        if (!is_null($this->article->parent_version_id)) {
            return $this->article->parent_version_id;
        }

        $parentArticle = Article::find($this->article->parent_id);

        if (is_null($parentArticle->version_id)) { // Create origin version
            /** @var ?ContentVersions $versionData */
            $versionData = ContentVersions::create([
                'user_id' => $parentArticle->owner_id,
                'content_id' => $parentArticle->id,
                'content_type' => Content::TYPE_ARTICLE,
                'version_purpose' => ContentVersions::PURPOSE_CREATE,
            ]);

            if ($versionData) {
                $parentArticle->version_id = $versionData->id;
                $parentArticle->save();
            }
        }

        return $parentArticle->version_id;
    }
}
