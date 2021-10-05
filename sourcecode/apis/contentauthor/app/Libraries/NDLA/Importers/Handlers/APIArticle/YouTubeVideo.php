<?php

namespace App\Libraries\NDLA\Importers\Handlers\APIArticle;

use Cache;
use App\Article;
use App\H5PContent;
use App\NdlaIdMapper;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use App\Libraries\DataObjects\Attribution;
use App\Libraries\H5P\ContentType\CerpusVideo;
use App\Libraries\NDLA\Importers\ImporterInterface;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;

class YouTubeVideo extends BaseHandler
{
    protected $client;
    protected $standardLicense = 'BY-NC-ND';

    public function process(Article $article, $jsonArticle): Article
    {
        $this->article = $article;
        $this->jsonArticle = $jsonArticle;

        $this->debug("Processing YouTube videos");

        $document = $this->getDom();

        $embedNodes = $document->getElementsByTagName('embed');
        $failedEmbedCount = 0;
        $processedYouTubeVideosCount = 0;
        foreach ($embedNodes as $embedNode) {
            // Example: <embed data-resource=\"external\" data-url=\"https://youtu.be/RAbVTreF3lA?rel=0&amp;start=2\">
            if ($this->isYouTubeEmbeddedNode($embedNode)) {
                $h5pVideo = new CerpusVideo();
                $h5pVideo->setId($embedNode->getAttribute('data-url'));
                $h5pVideo->setTitle('Imported YouTube video: ' . $embedNode->getAttribute('data-url'));
                $h5pVideo->setMetaLicense($this->standardLicense);
                $h5pVideo->setYouTubeVideoUrl($embedNode->getAttribute('data-url'));
                $h5pVideo->addMetaComment("YouTube video referenced by EdLib import at " . now()->toIso8601ZuluString() . ".");

                // 5. Persist CerpusImage H5P
                $importJson = $h5pVideo->getImportJson();

                /** @var ImporterInterface $h5pImporter */
                $h5pImporter = app(H5PAdapterInterface::class)->getImporter();
                $h5pImportReport = $h5pImporter->import($importJson);

                if ($h5pImportReport->status === Response::HTTP_CREATED) {
                    $message = "YouTube: Imported YouTube {$h5pVideo->getId()}: {$h5pImportReport->report}";
                    $this->debug($message);
                } else {
                    $message = "YouTube: Failed YouTube import {$h5pVideo->getId()}: {$h5pImportReport->report}";
                    $this->error($message);
                }


                // 6. Get embed URL for CerpusVideo
                /** @var NdlaIdMapper $idMapper */
                if ($idMapper = NdlaIdMapper::h5pByNdlaId($h5pVideo->getId())) {
                    $embedUrl = $idMapper->getOerLink();

                    /** @var H5PContent $h5p */
                    $h5p = $idMapper->h5pContents()->first();
                    /** @var  AttributionHandler $attribution */
                    $attribution = app(Attribution::class);
                    $attribution->setOrigin($embedNode->getAttribute('data-url'));

                    $h5p->setAttribution($attribution);

                    $iFrameNode = $document->createElement('iframe');
                    $iFrameNode->setAttribute('src', $embedUrl);
                    $iFrameNode->setAttribute('allow', "fullscreen; accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture");
                    $iFrameNode->setAttribute('class', 'edlib_resource');

                    $embedNode->parentNode->insertBefore($this->addCaption($iFrameNode, $embedNode), $embedNode);

                    $this->debug("YouTube: Embedded video: {$h5pVideo->getTitle()} ({$h5pVideo->getId()})");
                    $processedYouTubeVideosCount++;
                } else {
                    $failedEmbedCount++;
                    $this->error("YouTube: Failed to embed {$h5pVideo->getId()} ({$h5pVideo->getId()}). No mapping found.");
                }
            }
        }

        if ($processedYouTubeVideosCount > 0) {
            $this->saveContent($document);
        }

        $message = "Embedded $processedYouTubeVideosCount YouTube " . Str::plural('video', $processedYouTubeVideosCount) . ". Failed to embed $failedEmbedCount " . Str::plural('video',
                $failedEmbedCount);
        if ($failedEmbedCount) {
            $this->error($message);
        } else {
            $this->debug($message);
        }

        return $this->article;
    }

    protected function isYouTubeEmbeddedNode($node): bool
    {
        $isExternal = $this->isExternal($node);
        $linksToYouTube = $this->linksToYouTube($node);

        return $isExternal && $linksToYouTube;
    }

    protected function linksToYouTube($node): bool
    {
        $linksToYouTube = false;
        $nodeUri = $node->getAttribute('data-url');
        $host = strtolower(parse_url($nodeUri, PHP_URL_HOST));

        if ($host) {
            $linksToYouTube = in_array($host, ['youtu.be', 'www.youtu.be', 'youtube.com', 'www.youtube.com']);
        }

        return $linksToYouTube;
    }

    protected function isExternal($node): bool
    {
        return (mb_strtolower($node->getAttribute('data-resource')) === "external");
    }

    protected function makeYouTubeEmbedUrl($node)
    {
        $path = parse_url($node->getAttribute('data-url'), PHP_URL_PATH);

        return "https://www.youtube.com/embed$path";
    }
}
