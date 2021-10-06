<?php

namespace App\Libraries\NDLA\Importers\Handlers\APIArticle;

use Cache;
use App\Article;
use Illuminate\Support\Str;

class TedTalksVideo extends BaseHandler
{
    protected $client;
    protected $standardLicense = 'C';

    public function process(Article $article, $jsonArticle): Article
    {
        $this->article = $article;
        $this->jsonArticle = $jsonArticle;

        $this->debug("Processing TedTalk videos");

        $document = $this->getDom();

        $embedNodes = $document->getElementsByTagName('embed');
        $failedEmbedCount = 0;
        $processedTedTalksVideosCount = 0;
        foreach ($embedNodes as $embedNode) {
            // Example: <embed data-resource="external" data-url="http://www.ted.com/talks/edward_snowden_here_s_how_we_take_back_the_internet">
            if ($this->isTedTalk($embedNode)) {
                $iFrameSrc = $this->makeTedTalkEmbedUrl($embedNode);
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
                    $processedTedTalksVideosCount++;
                }
            } else {
                $failedEmbedCount++;
            }
        }

        if ($processedTedTalksVideosCount > 0) {
            $this->saveContent($document);
        }
        $message = "TedTalks: Embedded $processedTedTalksVideosCount TedTalks " . Str::plural('video', $processedTedTalksVideosCount) . ".";

        $this->debug($message);

        return $this->article;
    }

    protected function isTedTalk($node): bool
    {
        $host = parse_url($node->getAttribute('data-url'), PHP_URL_HOST);

        return in_array($host, ['www.ted.com', 'ted.com']);
    }

    public function makeTedTalkEmbedUrl($node)
    {
        $path = parse_url($node->getAttribute('data-url'), PHP_URL_PATH);
        $talkTitle = last(explode('/', $path));

        $urlTemplate = "https://embed.ted.com/talks/%s";

        $url = sprintf($urlTemplate, $talkTitle);

        return $url;
    }
}
