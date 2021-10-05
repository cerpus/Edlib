<?php

namespace App\Libraries\NDLA\Importers\Handlers\APIArticle;

use Cache;
use App\Article;
use Illuminate\Support\Str;

class VimeoVideo extends BaseHandler
{
    protected $client;
    protected $standardLicense = 'C';

    public function process(Article $article, $jsonArticle): Article
    {
        $this->article = $article;
        $this->jsonArticle = $jsonArticle;

        $this->debug("Processing Vimeo Videos");

        $document = $this->getDom();

        $embedNodes = $document->getElementsByTagName('embed');
        $processedVimeoVideosCount = 0;
        foreach ($embedNodes as $embedNode) {
            // Example: <embed data-resource="external" data-url="https://player.vimeo.com/video/62283990"/>
            if ($this->isVimeoEmbeddedNode($embedNode)) {
                $fWidth = $fHeight = null;
                if ($embedNode->hasAttribute('data-width') && $embedNode->hasAttribute('data-height') && ($embedNode->getAttribute('data-width') !== 0)) {
                    $height = filter_var($embedNode->getAttribute('data-height'), FILTER_SANITIZE_NUMBER_INT);
                    if (is_numeric($height)) {
                        $fHeight = $height;
                    }

                    $width = filter_var($embedNode->getAttribute('data-width'), FILTER_SANITIZE_NUMBER_INT);
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

                $iFrameSrc = $embedNode->getAttribute('data-url');
                $iFrameNode->setAttribute('src', $iFrameSrc);
                $iFrameNode->setAttribute('allow', 'fullscreen');
                $iFrameNode->setAttribute('allowfullscreen', 'allowfullscreen');

                $embedNode->parentNode->insertBefore($this->addCaption($iFrameNode, $embedNode), $embedNode);
                $processedVimeoVideosCount++;
            }
        }


        if ($processedVimeoVideosCount > 0) {
            $this->saveContent($document);
        }

        $this->debug("Vimeo: Embedded $processedVimeoVideosCount Vimeo " . Str::plural('video', $processedVimeoVideosCount) . ".");

        return $this->article;
    }

    protected function isVimeoEmbeddedNode($node): bool
    {
        $isExternal = $this->isExternal($node);
        $linksToVimeo = $this->linksToVimeo($node);

        return $isExternal && $linksToVimeo;
    }

    protected function linksToVimeo($node): bool
    {
        $linksToVimeo = false;
        $nodeUri = $node->getAttribute('data-url');
        $host = strtolower(parse_url($nodeUri, PHP_URL_HOST));

        if ($host) {
            $linksToVimeo = in_array($host, ['player.vimeo.com']);
        }

        return $linksToVimeo;
    }

    protected function isExternal($node): bool
    {
        return (mb_strtolower($node->getAttribute('data-resource')) === "external");
    }
}
