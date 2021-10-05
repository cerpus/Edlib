<?php

namespace App\Libraries\NDLA\Importers;

use App\Article;
use Carbon\Carbon;
use App\NdlaIdMapper;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Str;
use App\NdlaArticleImportStatus;
use App\Libraries\NDLA\API\ImageApiClient;
use App\Libraries\NDLA\API\ArticleApiClient;
use App\Libraries\NDLA\Importers\Handlers\APIArticle\H5P;
use App\Libraries\NDLA\Importers\Handlers\APIArticle\Tags;
use App\Libraries\NDLA\Importers\Handlers\APIArticle\Files;
use App\Libraries\NDLA\Importers\Handlers\APIArticle\Audio;
use App\Libraries\NDLA\Importers\Handlers\APIArticle\Terms;
use App\Libraries\NDLA\Importers\Handlers\APIArticle\Image;
use App\Libraries\NDLA\Importers\Handlers\APIArticle\Iframe;
use App\Libraries\NDLA\Importers\Handlers\Helpers\HTMLHelper;
use App\Libraries\NDLA\Importers\Handlers\Helpers\ArticleHash;
use App\Libraries\NDLA\Importers\Handlers\APIArticle\Language;
use App\Libraries\NDLA\Importers\Handlers\APIArticle\NrkVideo;
use App\Libraries\NDLA\Importers\Handlers\APIArticle\Licensing;
use App\Libraries\NDLA\Importers\Handlers\APIArticle\VimeoVideo;
use App\Libraries\NDLA\Importers\Handlers\Helpers\LicenseHelper;
use App\Libraries\NDLA\Importers\Handlers\APIArticle\NotifyCore;
use App\Libraries\NDLA\Importers\Handlers\APIArticle\Versioning;
use App\Libraries\NDLA\Importers\Handlers\APIArticle\Norgesfilm;
use App\Libraries\NDLA\Importers\Handlers\APIArticle\ExternalLink;
use App\Libraries\NDLA\Importers\Handlers\APIArticle\YouTubeVideo;
use App\Libraries\NDLA\Importers\Handlers\APIArticle\TedTalksVideo;
use App\Libraries\NDLA\Importers\Handlers\APIArticle\AddTagClasses;
use App\Libraries\NDLA\Importers\Handlers\APIArticle\BrightCoveVideo;
use App\Libraries\NDLA\Importers\Handlers\APIArticle\AttributionHandler;

class APIArticleImporter extends ImporterBase implements ImporterInterface
{
    use LicenseHelper, ArticleHash, HTMLHelper;

    protected $handlers = [
        Image::class,
        Files::class,
        H5P::class,
        Iframe::class,
        Norgesfilm::class,
        Terms::class,
        BrightCoveVideo::class,
        YouTubeVideo::class,
        VimeoVideo::class,
        NrkVideo::class,
        TedTalksVideo::class,
        Audio::class,
        ExternalLink::class,
        Language::class,
        AttributionHandler::class,
        AddTagClasses::class,
        Tags::class,
        Licensing::class,
        Versioning::class,
        NotifyCore::class,
    ];

    /** @var ArticleApiClient */
    protected $articleApiClient;
    /** @var ImageApiClient */
    protected $imageApiClient;

    /** @var Article|null $article */
    protected $article = null;

    /** @var NdlaIdMapper $idMapper */
    protected $idMapper = null;

    protected $jsonArticle;

    /** @var Article|null */
    protected $oldArticle = null;

    /** @var string|null */
    protected $importId = null;


    public function __construct(ArticleApiClient $articleApiClient = null, ImageApiClient $imageApiClient = null)
    {
        parent::__construct(app(NdlaIdMapper::class), app(ImportStatus::class));

        $this->articleApiClient = $articleApiClient;
        if (!$this->articleApiClient) {
            $this->articleApiClient = resolve(ArticleApiClient::class);
        }

        $this->imageApiClient = $imageApiClient;
        if (!$this->imageApiClient) {
            $this->imageApiClient = resolve(ImageApiClient::class);
        }
    }

    public function setImportId($importId): ImporterInterface
    {
        $this->importId = $importId;

        return $this;
    }

    public function import($jsonArticle, $importTranslations = false, $translatedFromArticle = null)
    {
        if ($this->adapter->adapterIs('cerpus')) {
            $this->jsonArticle = $jsonArticle;
            if ($this->canImport($this->jsonArticle->copyright)) {
                $existingIdMapper = NdlaIdMapper::articleByNdlaIdAndLanguage($this->jsonArticle->id, ($this->jsonArticle->title->language ?? 'nb'));

                if (!$existingIdMapper) { // This article has not been imported before
                    $this->importArticle();
                    $existingIdMapper = NdlaIdMapper::articleByNdlaIdAndLanguage($this->jsonArticle->id, ($this->jsonArticle->title->language ?? 'nb'));
                    if (!$existingIdMapper) {
                        $this->idMapper = new NdlaIdMapper();
                        $this->idMapper->ndla_id = $this->jsonArticle->id;
                        $this->idMapper->type = 'article';
                        $this->idMapper->language_code = $this->article->getLanguage();
                        $this->idMapper->ndla_checksum = $this->generateChecksumHash($this->jsonArticle);
                        $this->idMapper->save();
                    } else {
                        $this->idMapper = $existingIdMapper;
                    }
                } else { // We have already imported this article
                    $this->idMapper = $existingIdMapper;
                    $this->article = Article::find($this->idMapper->ca_id);
                    $this->importArticle($this->article);
                }

                if ($this->article && $this->idMapper) {
                    if ($translatedFromArticle) {
                        $this->article->setAsTranslationOf($translatedFromArticle);
                    } else {
                        $this->article->setAsMasterTranslation();
                    }

                    $this->idMapper->ca_id = $this->article->id;
                    $this->idMapper->ndla_checksum = $this->generateChecksumHash($this->jsonArticle);
                    $this->idMapper->save();
                    $currentLanguage = $this->jsonArticle->content->language;

                    if ($importTranslations) {
                        $availableTranslations = $this->jsonArticle->supportedLanguages ?? [];
                        $languagesToImport = array_diff($availableTranslations, [$currentLanguage]);

                        foreach ($languagesToImport as $language) {
                            $translation = $this->articleApiClient->getArticle($this->jsonArticle->id, $language);

                            $importer = (new APIArticleImporter())->updateOnDuplicate();
                            $importer->setImportId($this->importId);
                            $translatedArticle = $importer->import($translation, false, $this->article);
                            $translatedArticle->setAsTranslationOf($this->article);
                            unset($importer);
                        }
                    }
                }

                return $this->article;
            } else {
                NdlaArticleImportStatus::logDebug($this->jsonArticle->id, "{$this->jsonArticle->title->title} not imported because of copyrighted content.", $this->importId);

                return false;
            }
        } else {
            return false; // No article import except in cerpus env.
        }
    }

    private function importArticle(Article $oldArticle = null, $prefix = null)
    {
        $this->oldArticle = $oldArticle;
        if (!$oldArticle) {
            $oldArticle = new Article();
            $oldArticle->id = Uuid::uuid4()->toString();
            $oldArticle->owner_id = config('ndla.userId');
            $oldArticle->is_private = false;

            $oldArticle->save();

            $this->article = $oldArticle;
        } else {
            $this->article = $this->article->makeCopy(config('ndla.userId'));
        }

        NdlaArticleImportStatus::logDebug($this->jsonArticle->id, '[' . $this->article->id . '] Owner is: ' . $this->article->owner_id, $this->importId);

        $title = trim($this->jsonArticle->modifiedTitle ?? $this->jsonArticle->title->title);

        $this->article->title = $title;

        $content = trim($this->jsonArticle->content->content);
        if ($this->jsonArticle->introduction->introduction ?? '') {
            $content = '<section class="ndla-introduction">' . trim($this->jsonArticle->introduction->introduction) . '</section>' . $content;
        }

        $content = '<section><header><h1>' . $title . '</h1></header></section>' . $content;

        $this->article->content = $content;

        $this->article->created_at = Carbon::parse($this->jsonArticle->created);
        $this->article->updated_at = Carbon::parse($this->jsonArticle->updated);
        $this->article->node_id = $this->jsonArticle->id;
        $this->article->original_id = $this->article->id;
        $this->article->max_score = null;
        $this->article->is_published = 1;

        $this->article->save();

        NdlaArticleImportStatus::logDebug($this->jsonArticle->id, "[{$this->article->id}] Starting additional processing.", $this->importId);

        foreach ($this->handlers as $handler) {
            $this->article = (new $handler)->setImportId($this->importId)
                ->process($this->article, $this->jsonArticle);
        }
    }

    public function importArticles($articleIds = [], $titlePrefixes = [])
    {
        /** @var ArticleApiClient $articleApiClient */
        $articleApiClient = resolve(ArticleApiClient::class);
        $start = microtime(true);

        NdlaArticleImportStatus::logDebug(0, "Starting multi import of " . count($articleIds) . " articles.", $this->importId);

        $totalArticles = count($articleIds);

        $articleCount = 0;
        $failedCount = 0;
        foreach ($articleIds as $apiArticleId) {
            try {
                $jsonArticle = $articleApiClient->getArticle($apiArticleId);

                $originalTitle = $jsonArticle->title->title ?? 'an jsonArticle';
                $jsonArticle->modifiedTitle = $this->getModifiedTitle($jsonArticle, $titlePrefixes);

                $importer = (new APIArticleImporter())->updateOnDuplicate();
                $currentCount = $articleCount + $failedCount + 1;

                NdlaArticleImportStatus::logDebug($apiArticleId, "Starting import of '$originalTitle' as '{$jsonArticle->modifiedTitle}' [$currentCount/$totalArticles].",
                    $this->importId);

                $start1 = microtime(true);
                $importer->setImportId($this->importId);
                $importer->import($jsonArticle, false)                                         ;
                $importTime1 = sprintf("%0.2f", (microtime(true) - $start1));
                NdlaArticleImportStatus::logDebug($apiArticleId, "{$jsonArticle->title->title} imported as '{$jsonArticle->modifiedTitle}' [$currentCount/$totalArticles] in $importTime1 seconds.", $this->importId);
                unset($importer);
                $articleCount++;
            } catch (\Throwable $t) {
                $failedCount++;
                NdlaArticleImportStatus::logError($apiArticleId, "Failed import of jsonArticle $apiArticleId. {$t->getFile()}({$t->getLine()}):{$t->getMessage()}", $this->importId);
                NdlaArticleImportStatus::logError($apiArticleId, "Backtrace: {$t->getTraceAsString()}", $this->importId);
            }
        }

        $importTime = sprintf("%0.2f", (microtime(true) - $start));

        $message = "$articleCount " . Str::plural('jsonArticle', $articleCount) . " imported in $importTime seconds. $failedCount failed " . Str::plural('import', $failedCount) . ".";

        if ($failedCount > 0) {
            NdlaArticleImportStatus::logError(0, $message, $this->importId);
        } else {
            NdlaArticleImportStatus::logDebug(0, $message, $this->importId);
        }

        return $articleCount;
    }

    public function getModifiedTitle(&$article, &$titlePrefixes)
    {
        $title = $article->title->title ?? 'an article';

        if (array_key_exists($article->id, $titlePrefixes)) {
            $title = $titlePrefixes[$article->id] . ' - ' . $title;
        }

        return $title;
    }
}
