<?php

namespace App\Libraries\NDLA\Importers\Handlers\APIArticle;

use App\Article;
use Illuminate\Support\Str;

class ExternalLink extends BaseHandler
{
    public function process(Article $article, $jsonArticle): Article
    {
        $this->article = $article;
        $this->jsonArticle = $jsonArticle;

        $this->debug("Processing external links");

        $language = $this->jsonArticle->content->language ?? 'nb';

        $document = $this->getDom();

        $embedNodes = $document->getElementsByTagName('embed');
        $processedNdlaLinksCount = 0;
        $processedOtherLinksCount = 0;

        foreach ($embedNodes as $node) {
            // Example: <embed data-content-id="9631" data-link-text="Armeringskutter" data-resource="content-link">
            if ($this->isExternalLinkToNdla($node)) {
                $linkNode = $document->createElement('a');

                $linkUrl = "https://ndla.no/$language/article/{$node->getAttribute('data-content-id')}";
                $linkNode->setAttribute('href', $linkUrl);

                $linkTextNode = $node->ownerDocument->createTextNode($node->getAttribute('data-link-text'));
                $linkNode->appendChild($linkTextNode);

                $linkNode->setAttribute('target', '_blank');

                $node->parentNode->insertBefore($linkNode, $node);

                $processedNdlaLinksCount++;
            } elseif ($this->isExternalLink($node)) {
                // I don't know how the embed tag looks if there is a link to something other than NDLA.
                // Fill in the blanks if/when needed.
                $processedOtherLinksCount++;
            }
        }
        if ($processedNdlaLinksCount > 0) {
            $this->saveContent($document);
        }

        $this->debug("External Links: created $processedNdlaLinksCount " . Str::plural('link', $processedNdlaLinksCount) . " to NDLA. Skipped $processedOtherLinksCount " . Str::plural('link',
                $processedOtherLinksCount) . " to other sources.");

        return $this->article;
    }

    protected function isExternalLinkToNdla($node)
    {
        $isContentLink = $this->isExternalLink($node);

        $isNdlaReference = false;
        if ($contentId = $node->hasAttribute('data-content-id') ? $node->getAttribute('data-content-id') : null) {
            $isNdlaReference = filter_var($contentId, FILTER_VALIDATE_INT);
        }

        return $isContentLink && $isNdlaReference;
    }

    protected function isExternalLink($node)
    {
        $isContentLink = $node->hasAttribute('data-resource') && (mb_strtolower($node->getAttribute('data-resource')) === 'content-link');

        return $isContentLink;
    }
}
