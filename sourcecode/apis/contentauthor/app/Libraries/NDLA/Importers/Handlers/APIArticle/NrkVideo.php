<?php

namespace App\Libraries\NDLA\Importers\Handlers\APIArticle;

use Cache;
use App\Article;
use Illuminate\Support\Str;

class NrkVideo extends BaseHandler
{
    protected $client;
    protected $standardLicense = 'C';

    public function process(Article $article, $jsonArticle): Article
    {
        $this->article = $article;
        $this->jsonArticle = $jsonArticle;

        $this->debug("Processing NRK videos");

        $document = $this->getDom();

        $embedNodes = $document->getElementsByTagName('embed');
        $failedEmbedCount = 0;
        $processedNrkVideosCount = 0;
        foreach ($embedNodes as $embedNode) {
            // Example: <embed data-nrk-video-id="6053" data-resource="nrk" data-url="http://www.nrk.no/skole/klippdetalj?topic=nrk:klipp/319950">
            if ($this->isNrkEmbeddedNode($embedNode)) {
                $iFrameSrc = $this->makeNrkEmbedUrl($embedNode);
                if ($iFrameSrc) {
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

                    $iFrameNode->setAttribute('src', $iFrameSrc);
                    $iFrameNode->setAttribute('allowfullscreen', 'allowfullscreen');

                    $embedNode->parentNode->insertBefore($this->addCaption($iFrameNode, $embedNode), $embedNode);
                    $processedNrkVideosCount++;
                }
            } else {
                $failedEmbedCount++;
            }
        }

        if ($processedNrkVideosCount > 0) {
            $this->saveContent($document);
        }

        $this->debug("NRK: Embedded $processedNrkVideosCount NRK " . Str::plural('video', $processedNrkVideosCount) . ".");

        return $this->article;
    }

    protected function isNrkEmbeddedNode($node): bool
    {
        $isNrk = $this->isNrk($node);
        $linksToNrk = $this->linksToNrk($node);

        return $isNrk && $linksToNrk;
    }

    protected function linksToNrk($node): bool
    {
        $linksToNrk = false;
        $nodeUri = $node->getAttribute('data-url');
        $host = strtolower(parse_url($nodeUri, PHP_URL_HOST));

        if ($host) {
            $linksToNrk = in_array($host, ['www.nrk.no', 'nrk.no', 'static.nrk.no']);
        }

        return $linksToNrk;
    }

    protected function isNrk($node): bool
    {
        return (mb_strtolower($node->getAttribute('data-resource')) === "nrk");
    }

    public function makeNrkEmbedUrl($node)
    {
        $urlTemplate = "https://static.nrk.no/ludo/latest/video-embed.html#id=%s";
        $url = sprintf($urlTemplate, $node->getAttribute('data-nrk-video-id'));

        return $url;
    }
}
