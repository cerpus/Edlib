<?php

namespace App\Libraries\NDLA\Importers\Handlers\APIArticle;

use App\Article;

class Language extends BaseHandler
{
    public function process(Article $article, $jsonArticle): Article
    {
        $this->article = $article;
        $this->jsonArticle = $jsonArticle;

        $this->debug("Processing Language");

        $currentLanguage = $this->jsonArticle->content->language ?? 'nb';
        $this->article->setLanguage($currentLanguage);

        $this->debug('Language set to: ' . $this->article->getLanguage() . '.');

        return $this->article;
    }
}
