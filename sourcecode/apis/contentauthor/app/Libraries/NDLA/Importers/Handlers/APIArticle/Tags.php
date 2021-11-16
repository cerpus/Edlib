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

        return $this->article;
    }
}
