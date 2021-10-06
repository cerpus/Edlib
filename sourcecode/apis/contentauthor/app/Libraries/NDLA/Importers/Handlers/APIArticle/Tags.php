<?php

namespace App\Libraries\NDLA\Importers\Handlers\APIArticle;

use App\Article;

class Tags extends BaseHandler
{
    public function process(Article $article, $jsonArticle): Article
    {
        $this->article = $article;
        $this->jsonArticle = $jsonArticle;

        $this->debug("Processing Tags");

        $tags = $this->jsonArticle->tags->tags ?? [];

        // Make sure we have at least the NDLA tag. Add if required.
        if (!in_array('ndla', $tags)) {
            $tags[] = 'ndla';
        }

        if (!empty($tags) && is_array($tags)) {
            $this->article->updateMetaTags($tags);
            $this->debug('Tags: Set to: ' . $this->article->getMetaTagsAsString() . '.');
        } else {
            $this->debug('Tags: No tags, skipping.');
        }

        return $this->article;
    }
}
