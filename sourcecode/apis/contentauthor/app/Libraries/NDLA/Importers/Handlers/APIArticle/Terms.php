<?php

namespace App\Libraries\NDLA\Importers\Handlers\APIArticle;

use App\Article;
use Illuminate\Support\Str;

class Terms extends BaseHandler
{
    public function process(Article $article, $jsonArticle): Article
    {
        $this->article = $article;
        $this->jsonArticle = $jsonArticle;

        $this->debug("Processing Terms");

        $document = $this->getDom();

        $embedNodes = $document->getElementsByTagName('embed');
        $processedTermsCount = 0;
        foreach ($embedNodes as $node) {
            if ($this->isValidTermNode($node)) {
                $termNode = $document->createTextNode($node->getAttribute('data-link-text'));
                $node->parentNode->insertBefore($termNode, $node);
                $processedTermsCount++;
            }
        }

        if ($processedTermsCount) {
            $this->saveContent($document);
        }

        $this->debug("Terms: Handled $processedTermsCount " . Str::plural('term', $processedTermsCount) . '.');

        return $this->article;
    }

    protected function isValidTermNode($node)
    {
        return $node->hasAttribute('data-resource')
            && (mb_strtolower($node->getAttribute('data-resource')) === 'concept')
            && $node->hasAttribute('data-link-text');
    }
}
