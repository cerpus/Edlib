<?php

namespace App\Libraries\NDLA\Importers\Handlers\APIArticle;

use App\Article;

class Iframe extends BaseHandler
{
    public function process(Article $article, $jsonArticle): Article
    {
        $this->article = $article;
        $this->jsonArticle = $jsonArticle;

        $this->debug("Processing iframes");

        $document = $this->getDom();

        $embedNodes = $document->getElementsByTagName('embed');
        $processedIframeCount = 0;
        foreach ($embedNodes as $node) {
            if ($this->isIframeNode($node)) {
                if ($iFrameSrc = $this->getNodeAttribute($node, 'data-url')) {
                    $fWidth = $fHeight = null;
                    if ($node->hasAttribute('data-width') && $node->hasAttribute('data-height') && ($node->getAttribute('data-width') !== 0)) {
                        $height = filter_var($node->getAttribute('data-height'), FILTER_SANITIZE_NUMBER_INT);
                        if (is_numeric($height)) {
                            $fHeight = $height;
                        }

                        $width = filter_var($node->getAttribute('data-width'), FILTER_SANITIZE_NUMBER_INT);
                        if (is_numeric($width)) {
                            $fWidth = $width;
                        }
                    }

                    $iFrameNode = $document->createElement('iframe');

                    if ($fWidth) {
                        $iFrameNode->setAttribute('width', $fWidth . 'px');
                    }
                    if ($fHeight) {
                        $iFrameNode->setAttribute('height', $fHeight . 'px');
                    }

                    $iFrameNode->setAttribute('src', $iFrameSrc);
                    $iFrameNode->setAttribute('allow', 'fullscreen');
                    $iFrameNode->setAttribute('allowfullscreen', 'allowfullscreen');

                    $node->parentNode->insertBefore($iFrameNode, $node);
                    $processedIframeCount++;
                }
            }
        }
        if ($processedIframeCount > 0) {
            $this->saveContent($document);
        }

        $this->debug("Iframe: embedded $processedIframeCount iframes.");

        return $this->article;
    }

    protected function isIframeNode($node)
    {
        return $node->hasAttribute('data-resource')
            && (mb_strtolower($node->getAttribute('data-resource')) === 'iframe');
    }
}
