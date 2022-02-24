<?php

namespace App\Libraries\NDLA\Importers\Handlers\APIArticle;

use App\Traits\HTMLHelper;
use Illuminate\Support\Facades\Log;
use DOMElement;
use App\Article;
use Masterminds\HTML5;
use App\NdlaArticleImportStatus;
use App\Libraries\NDLA\API\AudioApiClient;
use App\Libraries\NDLA\API\ImageApiClient;
use App\Libraries\NDLA\API\ArticleApiClient;

abstract class BaseHandler
{
    use HTMLHelper;

    /** @var Article */
    protected $article;

    protected $logMessage;
    protected $jsonArticle;

    /** @var HTML5 */
    protected $html5;

    /** @var ImageApiClient */
    protected $imageApiClient;

    /** @var AudioApiClient */
    protected $audioApiClient;

    /** @var ArticleApiClient */
    protected $articleApiClient;

    protected $dom;

    /** @var string|null */
    protected $importId = null;

    public function __construct(ArticleApiClient $articleApiClient = null, ImageApiClient $imageApiClient = null, AudioApiClient $audioApiClient = null)
    {
        $this->articleApiClient = $articleApiClient;
        if (!$this->articleApiClient) {
            $this->articleApiClient = resolve(ArticleApiClient::class);
        }

        $this->imageApiClient = $imageApiClient;
        if (!$this->imageApiClient) {
            $this->imageApiClient = resolve(ImageApiClient::class);
        }

        $this->audioApiClient = $audioApiClient;
        if (!$this->audioApiClient) {
            $this->audioApiClient = resolve(AudioApiClient::class);
        }
    }

    abstract public function process(Article $article, $jsonArticle): Article;

    protected function debug($message)
    {
        $jsonArticleId = $this->jsonArticle->id ?? '- ndlaId -';
        $edLibTitle = $this->article->title ?? '- EdLib title -';
        $ndlaTitle = $this->jsonArticle->title->title ?? '- NDLA title -';

        $edLibLink = "#";
        if ($articleLink = route('article.show', ['article' => $this->article->id], false)) {
            $edLibLink = "EdLib: <a href=\"$articleLink\" target=\"_blank\">$edLibTitle</a>";
        }

        $oldNdlaUrl = $this->jsonArticle->oldNdlaUrl ?? '#';
        $oldNdlaUrl = str_replace('//', 'http://', $oldNdlaUrl);
        $oldNdlaUrl = str_replace('red.', '', $oldNdlaUrl);

        $ndlaLink = "NDLA: <a href=\"$oldNdlaUrl\" target=\"_blank\">$ndlaTitle</a>";

        $statusMessage = "[$jsonArticleId] [$ndlaLink] [$edLibLink] $message";

        NdlaArticleImportStatus::logDebug($jsonArticleId, $statusMessage, $this->importId);

        Log::debug($statusMessage);
    }

    protected function error($message)
    {
        $jsonArticleId = $this->jsonArticle->id ?? '- ndlaId -';
        $edLibTitle = $this->article->title ?? '- title -';

        $edLibLink = "#";
        if ($articleLink = route('article.show', ['article' => $this->article->id], false)) {
            $edLibLink = "EdLib: <a href=\"$articleLink\" target=\"_blank\">$edLibTitle</a>";
        }

        $oldNdlaUrl = $this->jsonArticle->oldNdlaUrl ?? '#';
        $oldNdlaUrl = str_replace('//', 'http://', $oldNdlaUrl);
        $oldNdlaUrl = str_replace('red.', '', $oldNdlaUrl);

        $ndlaLink = "NDLA: <a href=\"$oldNdlaUrl\" target=\"_blank\">$edLibTitle</a>";

        $statusMessage = "[$jsonArticleId] [$ndlaLink] [$edLibLink] $message";

        NdlaArticleImportStatus::logError($jsonArticleId, $statusMessage, $this->importId);

        Log::error($statusMessage);
    }

    public function setImportId($importId)
    {
        $this->importId = $importId;

        return $this;
    }

    protected function getDom()
    {
        $content = $this->addHtml5($this->article->content);
        $this->html5 = new HTML5();
        $this->dom = $this->html5->loadHTML($content);

        return $this->dom;
    }

    protected function saveContent($document)
    {
        $this->article->content = $this->getBody($this->html5->saveHTML($document));
        $this->article->save();
    }

    protected function addCaption($node, DOMElement $embedNode)
    {
        if (!$caption = $embedNode->getAttribute('data-caption')) {
            return $node;
        }

        if (is_null($this->dom)) {
            $this->getDom();
        }

        $wrapper = $this->dom->createElement('div');
        $wrapper->setAttribute('class', 'ndla-caption-container');
        $wrapper->appendChild($node);

        $captionWrapper = $this->dom->createElement('div');
        $captionWrapper->setAttribute('class', 'ndla-caption');
        $captionWrapper->textContent = $caption;
        $wrapper->appendChild($captionWrapper);

        return $wrapper;
    }

    protected function addCaptionTextWithSource(\DOMNode $node, string $text = '', \DOMNode $sourceNode = null): \DOMNode
    {
        $text = trim($text);

        if (!$text) {
            return $node;
        }

        if (!$this->dom) {
            $this->getDom();
        }

        $wrapper = $this->dom->createElement('div');
        $wrapper->setAttribute('class', 'ndla-caption-container');
        $wrapper->appendChild($node);

        $captionWrapper = $this->dom->createElement('div');
        $captionWrapper->setAttribute('class', 'ndla-caption');
        $captionWrapper->textContent = $text;

        if ($sourceNode) {
            $captionWrapper->appendChild($sourceNode);
        }

        $wrapper->appendChild($captionWrapper);


        return $wrapper;
    }
}
