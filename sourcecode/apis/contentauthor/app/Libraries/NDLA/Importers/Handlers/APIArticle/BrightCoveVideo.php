<?php

namespace App\Libraries\NDLA\Importers\Handlers\APIArticle;

use Illuminate\Support\Facades\Cache;
use App\Article;
use App\H5PContent;
use App\NdlaIdMapper;
use Illuminate\Support\Str;
use Cerpus\Helper\Clients\Oauth2Client;
use Cerpus\Helper\DataObjects\OauthSetup;
use App\Libraries\DataObjects\Attribution;
use App\Libraries\H5P\ContentType\CerpusVideo;
use App\Libraries\NDLA\Importers\ImporterInterface;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use App\Libraries\NDLA\Importers\Handlers\Helpers\LicenseHelper;

class BrightCoveVideo extends BaseHandler
{
    use LicenseHelper;

    public function process(Article $article, $jsonArticle): Article
    {
        $this->article = $article;
        $this->jsonArticle = $jsonArticle;

        $this->debug("Processing Brightcove videos");

        $document = $this->getDom();

        $failedEmbedCount = 0;
        $processedVideosCount = 0;

        $embedNodes = $document->getElementsByTagName('embed');

        foreach ($embedNodes as $embedNode) {
            if (mb_strtolower($embedNode->getAttribute('data-resource')) === "brightcove") {
                $account = $embedNode->hasAttribute('data-account') ? $embedNode->getAttribute('data-account') : null;
                $player = $embedNode->hasAttribute('data-player') ? $embedNode->getAttribute('data-player') : null;
                $videoId = $embedNode->hasAttribute('data-videoid') ? $embedNode->getAttribute('data-videoid') : null;
                $caption = $embedNode->hasAttribute('data-caption') ? $embedNode->getAttribute('data-caption') : null;

                if (!$caption) {
                    $caption = "Brightcove imported video at " . now()->toIso8601ZuluString();
                }

                if ($account && $player && $videoId) {
                    $videoInfo = $this->fetchVideoInfo($account, $videoId);

                    $videoUrl = "https://bc/$videoId";
                    $h5pVideo = new CerpusVideo();
                    $h5pVideo->setId($videoId);

                    $title = $videoInfo->name ?? "Video imported from Brightcove at " . now()->toIso8601ZuluString();
                    $h5pVideo->setTitle($title);

                    $h5pVideo->setBrightcoveVideoUrl($videoUrl);

                    $license = $this->toEdLibLicenseString($videoInfo->custom_fields->license ?? 'COPYRIGHT');
                    $h5pVideo->setLicense($license);

                    $h5pVideo->addMetaComment("Brightcove video referenced by EdLib import at " . now()->toIso8601ZuluString() . ".");

                    // 5. Persist CerpusImage H5P
                    $importJson = $h5pVideo->getImportJson();

                    /** @var ImporterInterface $h5pImporter */
                    $h5pImporter = app(H5PAdapterInterface::class)->getImporter();
                    $h5pImportReport = $h5pImporter->import($importJson);
                    $this->debug("Imported BrightCove {$h5pVideo->getId()}: $h5pImportReport->report");

                    // 6. Get embed URL for CerpusVideo
                    /** @var NdlaIdMapper $idMapper */
                    if ($idMapper = NdlaIdMapper::h5pByNdlaId($h5pVideo->getId())) {
                        $embedUrl = $idMapper->getOerLink();

                        /** @var H5PContent $h5p */
                        $h5p = $idMapper->h5pContents()->first();
                        /** @var  AttributionHandler $attribution */
                        $attribution = app(Attribution::class);
                        $origin = $videoInfo->custom_fields->licenseinfo ?? null;
                        if ($origin) {
                            $attribution->setOrigin(trim(str_replace('Leverandør:', '', $origin)));
                        }
                        $h5p->setAttribution($attribution); // Attribution is not handled in standard H5P import, so do it now.

                        $iFrameNode = $document->createElement('iframe');
                        $iFrameNode->setAttribute('src', $embedUrl);
                        $iFrameNode->setAttribute('allow', "fullscreen");
                        $iFrameNode->setAttribute('allowfullscreen', 'allowfullscreen');
                        $iFrameNode->setAttribute('frameborder', '0');
                        $iFrameNode->setAttribute('class', 'edlib_resource');

                        $embedNode->parentNode->insertBefore($this->addCaption($iFrameNode, $embedNode), $embedNode);

                        $processedVideosCount++;
                    } else {
                        $failedEmbedCount++;
                        $this->error("BrightCove: Failed to embed {$h5pVideo->getId()}. No mapping found.");

                    }
                }
            }
        }

        if ($processedVideosCount > 0) {
            $this->saveContent($document);
        }

        $message = "Brightcove: Embedded $processedVideosCount BrightCove " . Str::plural('video', $processedVideosCount) . ". Failed to embed $failedEmbedCount " . Str::plural('video',
                $failedEmbedCount);

        if ($failedEmbedCount) {
            $this->error($message);
        } else {
            $this->debug($message);
        }

        return $this->article;
    }

    public function fetchVideoInfo($account, $videoId)
    {
        $videoInfo = null;

        $cacheKey = "BrightcoveVideoInfo|$account|$videoId";

        if (!$videoInfo = Cache::get($cacheKey, null)) {
            try {
                // Copy paste from NDLAVideoAdapter as it returns a html response object not a useable object
                $client = Oauth2Client::getClient(OauthSetup::create([
                    'authUrl' => config('ndla-import.video.authUrl'),
                    'coreUrl' => config('ndla-import.video.url'),
                    'key' => config('ndla-import.video.key'),
                    'secret' => config('ndla-import.video.secret'),
                ]));

                $response = $client->get(sprintf('/v1/accounts/%s/videos/%s', $account, $videoId));

                $video = json_decode($response->getBody()->getContents());
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception(json_last_error_msg());
                }

                Cache::put($cacheKey, $video, now()->addHour());
                $videoInfo = $video;

            } catch (\Throwable $t) {
                $message = __METHOD__ . '(' . $t->getLine() . '): Brightcove (' . $t->getCode() . ') ' . $t->getMessage();
                $this->debug($message);
            }
        }

        return $videoInfo;
    }
}
