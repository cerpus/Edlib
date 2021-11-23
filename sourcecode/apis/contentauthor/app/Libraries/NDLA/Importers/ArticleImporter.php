<?php
namespace App\Libraries\NDLA\Importers;

use Cerpus\LicenseClient\Contracts\LicenseContract;
use Illuminate\Support\Facades\Log;
use App\Article;
use App\NdlaIdMapper;
use Ramsey\Uuid\Uuid;
use App\Libraries\NDLA\Notice\Core;
use Cerpus\LicenseClient\LicenseClient;


class ArticleImporter extends ImporterBase implements ImporterInterface
{

    protected $article;

    protected $contentType = "fagstoff";

    protected $filters = [
        Handlers\Article\CorrectImageLinks::class,
        Handlers\Article\DownloadImages::class,
        Handlers\Article\RelinkH5PIframes::class,
        Handlers\Article\RemoveProtocolFromAssetLinks::class,
    ];

    public function __construct()
    {
        parent::__construct(app(NdlaIdMapper::class), app(ImportStatus::class));
    }

    public function import($json)
    {
        $this->checkImportContentType($json);

        $this->initStatus([
            'report' => 'Article data was successfully imported',
            'status' => 201,
            "checksum" => $this->generateChecksumHash($json)
        ]);
        if ($this->importContent($json) === true) {
            $this->importTranslations($json,"article", $this->generateChecksumHash($json));
            $this->createInCore();
            $this->registerKeywords($json);
            $this->addLicense($json);
        }

        return $this->importStatus;
    }

    private function importContent($json)
    {
        try {
            $this->idMapper = $this->idMapper->firstOrNew([
                'ndla_id' => $json->nodeId,
                'ndla_checksum' => $this->generateChecksumHash($json),
                'language_code' => $json->title->language,
            ]);

            if (!empty($this->idMapper->id) && $this->getForceInsert() !== true) {
                $this->coreId = $this->idMapper->core_id;
                $this->article = app(Article::class)->find($this->idMapper->ca_id);
                $this->importStatus->id = $this->article->id;
                $this->importStatus->report = "Article already imported with no detected changes";
                return true;
            }

            $article = app(Article::class);
            $article->id = Uuid::uuid4()->toString();
            $article->owner_id = config('ndla.userId');
            $article->node_id = $json->nodeId;
            $article->ndla_url = str_replace('ndlatest', config('ndla.baseUrl'), $json->url);
            $article->title = $json->title;

            $content = $json->content;
            $article->content = '';
            foreach ($content->paragraphs as $paragraph) {
                if (property_exists($paragraph, 'left')) {
                    $article->content .= $paragraph->left;
                }
                if (property_exists($paragraph, 'right')) {
                    $article->content .= $paragraph->right;
                }
            }
            foreach ($this->filters as $filter) {
                $article->content = (new $filter)->handle($article);
            }

            $article->save();
            $this->article = $article;
            $this->importStatus->id = $article->id;

            $this->idMapper->ca_id = $article->id;
            $this->idMapper->type = 'article';
            $this->idMapper->save();

            return true;

        } catch (\Exception $e) {
            Log::error(__METHOD__ . ': ' . $e->getMessage());
            $this->importStatus->id = '';
            $this->importStatus->report = 'Data was not imported';
            $this->importStatus->status = 500;
        }

        return false;
    }

    private function createInCore()
    {
        if ($this->isResourceInCore() !== true) {
            $coreReporter = app(Core::class);
            $coreData = $coreReporter->notify($this->article->id, $this->article->node_id, $this->article->title,
                'Article');
            if ($coreData !== false) {
                $this->idMapper->core_id = $coreData->id;
                $this->idMapper->launch_url = $coreData->launch;
                $this->idMapper->save();
                $this->importStatus->report .= PHP_EOL . "Registered in Core";
                $this->coreId = $coreData->id;
            }
        }
    }

    private function generateChecksumHash($json)
    {
        try {
            $hashElements = [
                $json->nodeId,
                $json->title,
                $json->content,
                $json->keywords,
                $json->authors,
                $json->license,
                $json->used_content
            ];

            return sha1(json_encode($hashElements));
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function addLicense($json)
    {
        try {
            $license = resolve(LicenseContract::class);
            $contentLicense = $this->getLicense($json);

            $art = $this->article;
            $id = $art->id;
            $title = $art->title;
            $resp = $license->addContent($id, $title);

            $lic = $license->addLicense($art->id, $contentLicense);
            $this->importStatus->report .= PHP_EOL . "License added";
        }
        catch (\Exception $e){
            return false;
        }

        return true;
    }

}
