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
use App\Libraries\H5P\ContentType\CerpusImage;
use App\Libraries\NDLA\Importers\ImporterInterface;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use App\Libraries\NDLA\Importers\Handlers\Helpers\ClassNames;
use App\Libraries\NDLA\Importers\Handlers\Helpers\LicenseHelper;

class Image extends BaseHandler
{
    use LicenseHelper, ClassNames;

    public function process(Article $article, $jsonArticle): Article
    {
        $this->article = $article;
        $this->jsonArticle = $jsonArticle;

        $this->debug("Processing Images");

        $document = $this->getDom();

        $failedEmbedCount = 0;
        $failedLicenseCount = 0;
        $processedImagesCount = 0;

        $embedNodes = $document->getElementsByTagName('embed');

        foreach ($embedNodes as $embedNode) {
            /** @var DOMElement $embedNode */
            if (mb_strtolower($embedNode->getAttribute('data-resource')) === "image") {
                if ($imageId = $embedNode->getAttribute('data-resource_id')) {
                    $imageMeta = $this->imageApiClient->fetchMetaData($imageId);

                    if ($this->canImport($imageMeta->copyright)) {
                        // 1. Get image width and height
                        $imageDimensions = $this->fetchImageDimensions($imageMeta->imageUrl);

                        // 3. Create a new CerpusImage H5P
                        $imageH5P = new CerpusImage();
                        $imageH5P->setId($imageId);
                        $imageH5P->setTitle($imageMeta->title->title ?? 'NDLA image #{$imageMeta->id}');
                        $imageH5P->setAltText($imageMeta->alttext->alttext ?? '');
                        $imageH5P->setHoverText($imageMeta->alttext->alttext ?? '');
                        $imageH5P->setImageUrl($imageMeta->imageUrl);
                        $imageH5P->setImageWidth($imageDimensions->width);
                        $imageH5P->setImageHeight($imageDimensions->height);
                        $imageH5P->setMimeType($imageMeta->contentType);
                        $imageH5P->setImageLicense($imageMeta->copyright->license->license);

                        $importOwner = app(ImportOwner::class);

                        $imageH5P->addMetaComment("Image referenced by EdLib import at " . now()->toIso8601ZuluString() . ".");

                        // Mapping from NDLA collaborator types to H5P types is not perfect.
                        // Some info is lost. For example; the Photographer role is represented in H5P as Author
                        // This is the current best effort mapping.

                        $originators = [];
                        $origin = '';

                        foreach ($imageMeta->copyright->creators ?? [] as $creator) {
                            $imageH5P->addMetaAuthor($creator->name);
                            $originators[] = (object)[
                                'name' => $creator->name,
                                'role' => $creator->type,
                            ];
                        }
                        foreach ($imageMeta->copyright->processors ?? [] as $processor) {
                            $imageH5P->addMetaEditor($processor->name);
                            $originators[] = (object)[
                                'name' => $processor->name,
                                'role' => $processor->type,
                            ];
                        }
                        foreach ($imageMeta->copyright->rightsholders ?? [] as $rightsholder) {
                            $imageH5P->addMetaAuthor($rightsholder->name);
                            $originators[] = (object)[
                                'name' => $rightsholder->name,
                                'role' => $rightsholder->type,
                            ];
                        }
                        if ($imageMeta->copyright->origin ?? null) {
                            $imageH5P->addMetaOriginator($imageMeta->copyright->origin);
                            $origin = $imageMeta->copyright->origin;
                        }

                        // 5. Persist CerpusImage H5P
                        $importJson = $imageH5P->getImportJson();

                        /** @var ImporterInterface $h5pImporter */
                        $h5pImporter = app(H5PAdapterInterface::class)->getImporter();
                        $this->debug("Import of Image {$imageH5P->getId()} START");
                        $h5pImportReport = $h5pImporter->setImportId($this->importId)->import($importJson);
                        $this->debug("Imported Image {$imageH5P->getId()}: $h5pImportReport->report");

                        // 6. Get embed URL for CerpusImage
                        /** @var NdlaIdMapper $idMapper */
                        if ($idMapper = NdlaIdMapper::h5pByNdlaId($imageH5P->getId())) {
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
                            $iFrameNode->setAttribute('frameborder', '0');
                            $iFrameNode->setAttribute('allowfullscreen', 'allowfullscreen');

                            $wrapperNode = $this->addCaption($iFrameNode, $embedNode);
                            $wrapperNode->setAttribute('class', implode(' ', array_merge([$wrapperNode->getAttribute('class')], $this->iframeClassNames($embedNode))));

//                            $embedNode->parentNode->replaceChild($iFrameNode, $embedNode);
                            $embedNode->parentNode->insertBefore($wrapperNode, $embedNode);

                            $processedImagesCount++;
                        } else {
                            $failedEmbedCount++;
                            $this->error("Image: Failed to embed {$imageH5P->getId()}. No mapping found.");
                        }
                    } else { // Copyright issue; create an image placeholder instead
                        $imageDimensions = $this->fetchImageDimensions($imageMeta->imageUrl);
                        $text = urlencode("Ikke tilgjengelig");
                        $generateImageUrlTemplate = "https://dummyimage.com/%dx%d/444/ddd.png&text=$text";
                        $generatedImageUrl = sprintf($generateImageUrlTemplate, $imageDimensions->width, $imageDimensions->height);

                        $imageNameTemplate = 'placeholder-%d-%dx%d.png';
                        $imageName = sprintf($imageNameTemplate, $imageId, $imageDimensions->width, $imageDimensions->height);

                        $imgPath = "{$this->article->id}/$imageName";

                        $image = fopen($generatedImageUrl, 'r');

                        Storage::disk('article-uploads')->put($imgPath, $image);
                        $imageSize = Storage::disk('article-uploads')->size($imgPath);

                        unset($image);

                        $imgNode = $document->createElement('img');

                        $localUrl = Storage::disk('article-uploads')->url($imgPath);
                        $imgNode->setAttribute('src', $localUrl);
//                        $imgNode->setAttribute('width', $imageDimensions->width);
//                        $imgNode->setAttribute('height', $imageDimensions->height);
                        $imgNode->setAttribute('alt', $imageMeta->alttext->alttext ?? '');

                        $sourceNode = $document->createElement('a');
                        $sourceNode->setAttribute('target', '_blank');
                        $sourceNode->setAttribute('href', $imageMeta->imageUrl);
                        $sourceNode->textContent = $imageMeta->imageUrl;

                        $captionedImage = $this->addCaptionTextWithSource($imgNode, "Bildet vises ikke på grunn av mulige kopirettsbegrensninger. Se originalbildet her: ", $sourceNode);
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


        $this->saveContent($document);


        $message = "Image: Inserted $processedImagesCount " . Str::plural('image', $processedImagesCount) . " . $failedLicenseCount " . Str::plural('image',
                $failedLicenseCount) . " not imported due to licensing restrictions. Failed to embed $failedEmbedCount " . Str::plural('image', $failedEmbedCount) . ".";

        if ($failedEmbedCount || $failedLicenseCount) {
            $this->error($message);
        } else {
            $this->debug($message);
        }

        return $this->article;
    }

    /**
     * Get the image dimensions
     *
     * @param string $path The path of the image. Can be a filesystem path or an URL.
     * @param bool $cacheResult Indicate if you want to cache the result. Defaults to caching.
     * @return \stdClass|null Null or an object containing width and height properties
     */
    protected function fetchImageDimensions($path, $cacheResult = true)
    {
        $cacheName = "ImageDimension | $path";
        $cacheTime = now()->addHours(3);

        $imageDimensions = null;

        if ($cacheResult) {
            $imageDimensions = Cache::get($cacheName, null);
        }

        if ($imageDimensions) {
            return $imageDimensions;
        }

        if (list($width, $height) = getimagesize($path)) {
            $imageDimensions = new \stdClass();
            $imageDimensions->width = $width;
            $imageDimensions->height = $height;
        } elseif (self::isSVG($path)) {
            try {
                $previous = libxml_use_internal_errors(true);

                $doc = new \DOMDocument();
                $doc->load($path);
                $root = $doc->documentElement;

                [$x0, $y0, $x, $y] = array_pad(explode(' ', $root->getAttribute('viewBox'), 4), 4, null);

                if ($root->hasAttribute('width')) {
                    $width = $root->getAttribute('width');
                } elseif ($x) {
                    $width = $x - max($x0, 0);
                }

                if ($root->hasAttribute('height')) {
                    $height = $root->getAttribute('height');
                } elseif ($y) {
                    $height = $y - max($y0, 0);
                }
            } finally {
                libxml_use_internal_errors($previous);
            }

            if ($width > 0 && $height > 0) {
                $imageDimensions = new \stdClass();
                $imageDimensions->width = +$width;
                $imageDimensions->height = +$height;
            }
        }

        if (isset($imageDimensions)) {
            Cache::put($cacheName, $imageDimensions, $cacheTime);

            return $imageDimensions;
        }

        $this->error("Image: Unable to download $path . ");

        return null;
    }

    private static function isSVG(string $path): bool
    {
        $previous = libxml_use_internal_errors(true);

        try {
            $doc = new \DOMDocument();

            return $doc->load($path) && $doc->documentElement->nodeName === 'svg';
        } finally {
            libxml_use_internal_errors($previous);
        }
    }
}
