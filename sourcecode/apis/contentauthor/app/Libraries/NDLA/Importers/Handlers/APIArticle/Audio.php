<?php

namespace App\Libraries\NDLA\Importers\Handlers\APIArticle;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\File;
use DOMElement;
use App\Article;
use App\H5PContent;
use App\NdlaIdMapper;
use Illuminate\Support\Str;
use App\Libraries\ImportOwner;
use App\Libraries\DataObjects\Attribution;
use App\Libraries\H5P\ContentType\H5PAudio;
use App\Libraries\NDLA\Importers\ImporterInterface;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use App\Libraries\NDLA\Importers\Handlers\Helpers\ClassNames;
use App\Libraries\NDLA\Importers\Handlers\Helpers\LicenseHelper;

class Audio extends BaseHandler
{
    use LicenseHelper, ClassNames;

    public function process(Article $article, $jsonArticle): Article
    {
        $this->article = $article;
        $this->jsonArticle = $jsonArticle;

        $this->debug("Processing Audio");

        $document = $this->getDom();

        $failedEmbedCount = 0;
        $failedLicenseCount = 0;
        $processedAudioCount = 0;

        $embedNodes = $document->getElementsByTagName('embed');

        foreach ($embedNodes as $embedNode) {
            // <embed data-caption=\"Siri Knudsen i  NRK P3 gir deg gode råd om stemmebruk i radio.\" data-resource=\"audio\" data-resource_id=\"6\" data-type=\"standard\" data-url=\"https://api.ndla.no/audio-api/v1/audio/6\">
            /** @var DOMElement $embedNode */
            if (mb_strtolower($embedNode->getAttribute('data-resource')) === "audio") {
                if ($audioId = $embedNode->getAttribute('data-resource_id')) {
                    $audioMeta = $this->audioApiClient->fetchMetaData($audioId);
                    if ($this->canImport($audioMeta->copyright) && ($audioMeta->audioFile->url ?? false)) {
                        // 3. Create a new H5PAudio H5P
                        $audioH5P = new H5PAudio();
                        $audioH5P->setId($audioId);
                        $audioH5P->setTitle($audioMeta->title->title ?? 'NDLA audio #{$audioId}');
                        $audioH5P->setAudioFileUrl($audioMeta->audioFile->url);
                        $audioH5P->setMimeType($audioMeta->audioFile->mimeType) ?? 'audio/mp3';

                        $audioH5P->setLicense($audioMeta->copyright->license->license ?? 'U');

                        $importOwner = app(ImportOwner::class);

                        $audioH5P->addMetaComment("Audio referenced by EdLib import at " . now()->toIso8601ZuluString() . ".");

                        // Mapping from NDLA collaborator types to H5P types is not perfect.
                        // Some info is lost. For example; the Photographer role is represented in H5P as Author
                        // This is the current best effort mapping.

                        $originators = [];
                        $origin = '';

                        foreach ($audioMeta->copyright->creators ?? [] as $creator) {
                            $audioH5P->addMetaAuthor($creator->name);
                            $originators[] = (object)[
                                'name' => $creator->name,
                                'role' => $creator->type,
                            ];
                        }
                        foreach ($audioMeta->copyright->processors ?? [] as $processor) {
                            $audioH5P->addMetaEditor($processor->name);
                            $originators[] = (object)[
                                'name' => $processor->name,
                                'role' => $processor->type,
                            ];
                        }
                        foreach ($audioMeta->copyright->rightsholders ?? [] as $rightsholder) {
                            $audioH5P->addMetaAuthor($rightsholder->name);
                            $originators[] = (object)[
                                'name' => $rightsholder->name,
                                'role' => $rightsholder->type,
                            ];
                        }
                        if ($audioMeta->copyright->origin ?? null) {
                            $audioH5P->addMetaOriginator($audioMeta->copyright->origin);
                            $origin = $audioMeta->copyright->origin;
                        }

                        // 5. Persist H5PAudio H5P
                        $importJson = $audioH5P->getImportJson();

                        /** @var ImporterInterface $h5pImporter */
                        $h5pImporter = app(H5PAdapterInterface::class)->getImporter();
                        $h5pImportReport = $h5pImporter->import($importJson);
                        if ($h5pImportReport->status === \Illuminate\Http\Response::HTTP_CREATED) {
                            $this->debug("Imported Audio {$audioH5P->getId()}: $h5pImportReport->report");
                        } else {
                            $this->error("Audio: '{$audioH5P->getTitle()}' failed. {$h5pImportReport->report}'");
                        }


                        // 6. Get embed URL for CerpusImage
                        /** @var NdlaIdMapper $idMapper */
                        if ($idMapper = NdlaIdMapper::h5pByNdlaId($audioH5P->getId())) {
                            $embedUrl = $idMapper->getOerLink();

                            /** @var H5PContent $h5p */
                            $h5p = $idMapper->h5pContents()->first();
                            /** @var  Attribution $attribution */
                            $attribution = app(Attribution::class);
                            $attribution->setOrigin($origin);
                            foreach ($originators as $originator) {
                                $attribution->addOriginator($originator->name, $originator->role);
                            }
                            $h5p->setAttribution($attribution);


                            // 7. Create html to embed the new CerpusImage in the article.
                            $iFrameNode = $document->createElement('iframe');
                            $iFrameNode->setAttribute('src', $embedUrl);
                            $iFrameNode->setAttribute('allow', 'fullscreen');
                            $iFrameNode->setAttribute('class', 'edlib_resource');

                            $embedNode->parentNode->insertBefore($this->addCaption($iFrameNode, $embedNode), $embedNode);

                            $processedAudioCount++;
                        } else {
                            $failedEmbedCount++;
                            $this->error("Audio: Embed failed for '{$audioH5P->getTitle()}' {$audioH5P->getId()}. No mapping found.");
                        }
                    } else {
                        // Add placeholder image

                        $width = 725;
                        $height = 40;
                        $text = urlencode("Ikke tilgjengelig");
                        $generateImageUrlTemplate = "https://dummyimage.com/%dx%d/333/ddd.png&text=$text";
                        $generatedImageUrl = sprintf($generateImageUrlTemplate, $width, $height);

                        $imageNameTemplate = 'audio-placeholder-%d-%dx%d.png';
                        $imageName = sprintf($imageNameTemplate, $audioId, $width, $height);

                        $imgPath = "{$this->article->id}/$imageName";

                        $image = fopen($generatedImageUrl, 'r');

                        Storage::disk('article-uploads')->put($imgPath, $image);
                        $imageSize = Storage::disk('article-uploads')->size($imgPath);

                        unset($image);

                        $imgNode = $document->createElement('img');

                        $localUrl = Storage::disk('article-uploads')->url($imgPath);
                        $imgNode->setAttribute('src', $localUrl);
                        $imgNode->setAttribute('alt', $audioMeta->title->title ?? '');

                        $sourceNode = $document->createElement('a');
                        $sourceNode->setAttribute('target', '_blank');
                        $sourceNode->setAttribute('href', $audioMeta->audioFile->url);
                        $sourceNode->textContent = $audioMeta->title->title ?? '';

                        $captionedImage = $this->addCaptionTextWithSource($imgNode, "Lydfilen kan ikke avspilles på grunn av mulige kopirettsbegrensninger. Lytt til originalen her: ", $sourceNode);
                        $captionedImage->setAttribute('class', implode(' ', array_merge([$captionedImage->getAttribute('class')], $this->iframeClassNames($embedNode))));

//                            $embedNode->parentNode->replaceChild($iFrameNode, $embedNode);
                        $embedNode->parentNode->insertBefore($captionedImage, $embedNode);

                        $file = new File();
                        $file->name = $imageName;
                        $file->original_name = $imageName;
                        $file->size = $imageSize;
                        $file->mime = "image/png";

                        $this->article->files()->save($file);

                        $failedLicenseCount++;
                    }
                }
            }
        }

        if ($processedAudioCount > 0) {
            $this->saveContent($document);
        }

        $message = "Audio: Inserted $processedAudioCount audio " . Str::plural('file', $processedAudioCount) . " . $failedLicenseCount " . Str::plural('file',
                $failedLicenseCount) . " not imported due to licensing restrictions. Failed to embed $failedEmbedCount " . Str::plural('file', $failedEmbedCount) . ".";

        if ($failedLicenseCount || $failedEmbedCount) {
            $this->error($message);
        } else {
            $this->debug($message);
        }

        return $this->article;
    }
}
