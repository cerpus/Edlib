<?php

namespace App\Libraries\NDLA\Importers\Handlers\APIArticle;

use App\Article;

class AddTagClasses extends BaseHandler
{
    public function process(Article $article, $jsonArticle): Article
    {
        $this->article = $article;
        $this->jsonArticle = $jsonArticle;

        $this->debug("Processing HTML tag classes for NDLA");

        $document = $this->getDom();

        $nodes = $document->getElementsByTagName('*');
        $processedNodeCount = 0;
        foreach ($nodes as $node) {
            $classes = $node->getAttribute('class');
            if ($classes !== 'edlib_resource') {
                $className = ' ndla-' . $node->tagName;
                $node->setAttribute('class', $classes . $className);
            }
            $processedNodeCount++;
        }

        $this->saveContent($document);

        $this->debug('Added ndla classes to ' . $processedNodeCount . ' html nodes.');

        return $this->article;
    }
}
