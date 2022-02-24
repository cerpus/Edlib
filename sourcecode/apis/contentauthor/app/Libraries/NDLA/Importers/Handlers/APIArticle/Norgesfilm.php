<?php

namespace App\Libraries\NDLA\Importers\Handlers\APIArticle;

use App\Article;
use App\File;
use App\Libraries\NDLA\Traits\NdlaUrlHelper;
use GuzzleHttp\Psr7\Query;
use Illuminate\Support\Facades\Storage;

class Norgesfilm extends BaseHandler
{
    use NdlaUrlHelper;

    protected $originalNdlaUrl;

    public function process(Article $article, $jsonArticle): Article
    {
        $this->article = $article;

        $this->debug("Processing norgesfilm iframes");

        $this->fetchNdlaUrlForOriginalArticle();

        $document = $this->getDom();

        /** @var \DOMNodeList $iframes */
        $iframes = $document->getElementsByTagName('iframe');
        $processedIframeCount = 0;
        $deleteIframes = [];

        /** @var \DOMElement $iframe */
        foreach ($iframes as $iframe) {
            $iframeSrc = $this->getNodeAttribute($iframe, 'src');
            $this->debug("Processing " . $iframeSrc ?? 'error getting iframe src');
            if ($iframeSrc) {
                $matches = [];
                preg_match('/ndla\.filmiundervisning\.no\/film\/ndlafilm\.aspx/', $iframeSrc, $matches);
                if ($matches) {
                    $this->replaceNorgesfilmInodeWithPlaceholder($iframe);
                    $deleteIframes[] = $iframe;
                    $processedIframeCount++;
                } else {
                    $this->debug("Skipping iframe $iframeSrc");
                }
            } else {
                $this->debug("Skipping iframe. Missing src.");
            }
        }

        foreach ($deleteIframes as $iframe) {
            $iframe->parentNode->removeChild($iframe);
        }

        if ($processedIframeCount > 0) {
            $this->saveContent($document);
        }

        $this->debug("Norgesfilm: replaced $processedIframeCount iframes.");

        return $this->article;
    }

    public function replaceNorgesfilmInodeWithPlaceholder(\DOMElement $iframe)
    {
        $width = 725;
        $height = floor($width / (16 / 9));
        $text = urlencode("Ikke tilgjengelig");
        $generateImageUrlTemplate = "https://dummyimage.com/%dx%d/333/ddd.png&text=$text";
        $generatedImageUrl = sprintf($generateImageUrlTemplate, $width, $height);

        $imageNameTemplate = 'norgesfilm-placeholder-%d-%dx%d.png';
        $norgesfilmId = $this->getNorgesFilmIdFromUrl($this->getNodeAttribute($iframe, 'src'));
        $imageName = sprintf($imageNameTemplate, $norgesfilmId, $width, $height);

        $imgPath = "{$this->article->id}/$imageName";

        $image = fopen($generatedImageUrl, 'r');

        Storage::disk('article-uploads')->put($imgPath, $image);
        $imageSize = Storage::disk('article-uploads')->size($imgPath);

        unset($image);

        $imgNode = $iframe->ownerDocument->createElement('img');

        $localUrl = Storage::disk('article-uploads')->url($imgPath);
        $imgNode->setAttribute('src', $localUrl);
        $imgNode->setAttribute('alt', 'Norgesfilm video');


        $sourceNode = null;
        if ($this->originalNdlaUrl) {
            $sourceNode = $iframe->ownerDocument->createElement('a');
            $sourceNode->setAttribute('target', '_blank');
            $sourceNode->setAttribute('href', $this->originalNdlaUrl);
            $sourceNode->textContent = 'Se originalen her.';
        }

        $captionedImage = $this->addCaptionTextWithSource($imgNode, "Videoen kan ikke avspilles på grunn av mulige kopirettsbegrensninger. ", $sourceNode);
        $captionedImage->setAttribute('class', implode(' ', array_merge([$captionedImage->getAttribute('class')], $this->iframeClassNames($iframe))));

        $this->debug("Replacing " . $iframe->ownerDocument->saveHTML($iframe) . ' with placeholder.');

        $iframe->parentNode->insertBefore($captionedImage, $iframe);

        $file = new File();
        $file->name = $imageName;
        $file->original_name = $imageName;
        $file->size = $imageSize;
        $file->mime = "image/png";

        $this->article->files()->save($file);

    }

    protected function getNorgesFilmIdFromUrl($url)
    {
        $norgesfilmId = null;

        $queryPart = parse_url($url, PHP_URL_QUERY);
        if ($queryPart) {
            $parsedQuery = Query::parse($queryPart);
            if (array_key_exists('filmId', $parsedQuery)) {
                $norgesfilmId = $parsedQuery['filmId'];
            }
        }

        return $norgesfilmId;
    }

    protected function fetchNdlaUrlForOriginalArticle()
    {
       $originalArticle = Article::find($this->article->original_id);


       $this->originalNdlaUrl = $this->fetchNewNdlaUrl($originalArticle);
    }

    protected function iframeClassNames(\DOMElement $embedNode)
    {
        $classNames = ['edlib_resource'];
        $size = str_replace('fullbredde', 'full', $embedNode->getAttribute('data-size'));
        $align = $embedNode->getAttribute('data-align');
        if (!empty($align)) {
            if (!empty($size)) {
                $classNames[] = sprintf('u-float-%s-%s', $size, $align);
            } else {
                $classNames[] = sprintf('u-float-%s', $align);
            }
        }

        return $classNames;
    }
}
