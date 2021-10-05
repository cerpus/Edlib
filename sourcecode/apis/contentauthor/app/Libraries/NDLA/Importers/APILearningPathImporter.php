<?php

namespace App\Libraries\NDLA\Importers;

use App\Article;
use Carbon\Carbon;
use App\NdlaIdMapper;
use Ramsey\Uuid\Uuid;
use App\Libraries\NDLA\API\ArticleApiClient;
use App\Libraries\NDLA\API\LearningPathApiClient;
use App\Libraries\NDLA\Importers\Handlers\Helpers\LicenseHelper;
use App\Libraries\NDLA\Importers\Handlers\Helpers\ArticleHash;

class APILearningPathImporter extends ImporterBase implements ImporterInterface
{
    protected $handlers = [
    ];

    use LicenseHelper, ArticleHash;

    protected $apiArticle;
    /** @var ArticleApiClient */
    protected $learningPathApiClient;
    /** @var LearningPathApiClient */
    protected $imageApiClient;

    /** @var Article $article */
    protected $article = null;

    public function __construct(ArticleApiClient $articleApiClient = null, LearningPathApiClient $imageApiClient = null)
    {
        parent::__construct(app(NdlaIdMapper::class), app(ImportStatus::class));

        $this->learningPathApiClient = $articleApiClient;
        if (!$this->learningPathApiClient) {
            $this->learningPathApiClient = resolve(ArticleApiClient::class);
        }

        $this->imageApiClient = $imageApiClient;
        if (!$this->imageApiClient) {
            $this->imageApiClient = resolve(LearningPathApiClient::class);
        }
    }

    public function import($jsonArticle, $importTranslations = false, $translatedFromArticle = null)
    {
        $this->apiArticle = $jsonArticle;
        if ($this->canImport($this->apiArticle->copyright)) {
            $checksum = $this->generateChecksumHash($this->apiArticle);

            $existingIdMapper = NdlaIdMapper::articleByNdlaIdAndLanguage($this->apiArticle->id, ($this->apiArticle->title->language ?? 'nb'));

            if (!$existingIdMapper) {
                $this->importArticle();
                $this->idMapper->ndla_id = $this->apiArticle->id;
                $this->idMapper->type = 'article';
                $this->idMapper->language_code = $this->article->getLanguage();
                $this->idMapper->save();
            } else {
                $this->idMapper = $existingIdMapper;
                switch ($this->getDuplicateAction()) {
                    case self::DUPLICATE_UPDATE:
                        $this->article = Article::find($this->idMapper->ca_id);
                        $this->importArticle($this->article);
                        break;
                    case self::DUPLICATE_INSERT:
                        $this->importArticle();
                        break;
                    case self::DUPLICATE_SKIP:
                        break;
                    default:
                        break;
                }
            }

            if ($this->article && $this->idMapper) {
                if ($translatedFromArticle) {
                    $this->article->setAsTranslationOf($translatedFromArticle);
                } else {
                    $this->article->setAsMasterTranslation();
                }

                $this->idMapper->ca_id = $this->article->id;
                $this->idMapper->ndla_checksum = $this->generateChecksumHash($this->apiArticle);
                $this->idMapper->save();
                $currentLanguage = $this->apiArticle->content->language;

                if ($importTranslations) {
                    $availableTranslations = $this->apiArticle->supportedLanguages ?? [];
                    $languagesToImport = array_diff($availableTranslations, [$currentLanguage]);

                    foreach ($languagesToImport as $language) {
                        $translation = $this->learningPathApiClient->getArticle($this->apiArticle->id, $language);
                        $importer = (new APILearningPathImporter())->updateOnDuplicate();
                        $translatedArticle = $importer->import($translation, false, $this->article);
                        $translatedArticle->setAsTranslationOf($this->article);
                    }
                }
            }

            return $this->article;
        } else {
            return false;
        }
    }

    private function importArticle(Article $article = null)
    {
        $this->article = $article;
        if (!$article) {
            $article = new Article();
            $article->id = Uuid::uuid4()->toString();
            $article->owner_id = config('ndla.userId');
            $article->is_private = false;

            $article->save();

            $this->article = $article;
        }

        $this->article->title = trim($this->apiArticle->title->title);

        $content = trim($this->apiArticle->content->content);
        if ($this->apiArticle->introduction->introduction ?? '') {
            $content = '<section>' . trim($this->apiArticle->introduction->introduction) . '</section>' . $content;
        }
        $this->article->content = $content;

        $this->article->created_at = Carbon::parse($this->apiArticle->created);
        $this->article->updated_at = Carbon::parse($this->apiArticle->updated);
        $this->article->node_id = $this->apiArticle->id;
        $this->article->original_id = $this->article->id;

        $this->article->save();

        foreach ($this->handlers as $handler) {
            $this->article = (new $handler)->process($this->article, $this->apiArticle);
        }
    }
}
