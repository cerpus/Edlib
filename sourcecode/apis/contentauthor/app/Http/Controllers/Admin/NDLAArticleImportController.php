<?php

namespace App\Http\Controllers\Admin;

use App;
use File;
use Cache;
use Storage;
use App\Article;
use App\NdlaIdMapper;
use Illuminate\Http\Request;
use App\NdlaArticleImportStatus;
use App\Http\Controllers\Controller;
use App\Libraries\NDLA\API\ArticleApiClient;
use App\Libraries\NDLA\Traits\ImportOwnerTrait;
use App\Libraries\NDLA\Importers\APIArticleImporter;

class NDLAArticleImportController extends Controller
{
    use ImportOwnerTrait;

    protected $articlesPerPage = 15;
    protected $statusesPerPage = 50;

    public function index(Request $request)
    {
        $owner = $this->getImportOwner();

        if (!$owner) {
            return view('admin.ndla-article-import.configure');
        }

        $articles = App\NdlaArticleId::paginate($this->articlesPerPage);

        $articles->each(function ($article) {
            $article->ca_id = null;
            $article->translations = [];
            $language = $article->json->content->language ?? 'nb';
            if ($imported = NdlaIdMapper::articleByNdlaId($article->id)) {
                $articleDb = Article::find($imported->ca_id);
                if ($articleDb) {
                    $article->ca_id = $articleDb->id;
                    $article->translations = $articleDb->getTranslations();
                }
            }

            return $article;
        });


        /** @var ArticleApiClient $articleApiClient */
        $articleApiClient = resolve(ArticleApiClient::class);
        $response = $articleApiClient->setPage(1)->getArticles();
        $articleApiCount = $articleApiClient->getTotalArticleCount();

        return view('admin.ndla-article-import.index')
            ->with(compact('articles', 'articleCount', 'articleApiCount', 'owner'));
    }

    public function store(Request $request)
    {
        /** @var ArticleApiClient $articleApiClient */
        $articleApiClient = resolve(ArticleApiClient::class);
        $start = microtime(true);
        foreach ($request->import as $import) {
            NdlaArticleImportStatus::addStatus($import, 'Starting import');
            $start1 = microtime(true);
            $article = $articleApiClient->getArticle($import);
            $importer = (new APIArticleImporter())->updateOnDuplicate();
            $importer->import($article, true);
            $importTime1 = sprintf("%0.2f", (microtime(true) - $start1) * 1000);
            NdlaArticleImportStatus::addStatus($import, "{$article->title->title} imported in $importTime1 ms.");
        }
        $importTime = sprintf("%0.2f", (microtime(true) - $start) * 1000);

        $request->session()->flash('message', "Imported in $importTime ms.");

        return redirect(route('admin.ndla.index', ['page' => $request->input('page', 1)]));
    }

    public function show($id)
    {
        $storedArticle = App\NdlaArticleId::findOrFail($id);

        $article = $storedArticle->json;

        $articleRaw = json_encode($article);

        return view('admin.ndla-article-import.show')
            ->with(compact('article', 'articleRaw'));
    }

    public function import(Request $request, $id)
    {
        if (resolve(App\Libraries\H5P\Interfaces\H5PAdapterInterface::class)->adapterIs('cerpus')) {
            /** @var ArticleApiClient $articleApi */
            $articleApi = resolve(ArticleApiClient::class);

            $ndlaApiArticle = $articleApi->getArticle($id);

            $importer = (new APIArticleImporter())->updateOnDuplicate();
            NdlaArticleImportStatus::addStatus($ndlaApiArticle->id, 'Starting import');
            $start = microtime(true);
            $importer->import($ndlaApiArticle, true);
            $importTime = sprintf("%0.2f", (microtime(true) - $start) * 1000);
            NdlaArticleImportStatus::addStatus($ndlaApiArticle->id, "{$ndlaApiArticle->title->title} imported in $importTime ms.");
            $request->session()->flash('message', "{$ndlaApiArticle->title->title} imported in $importTime ms.");
        } else {
            $request->session()->flash('message', "Article import is disabled outside the Cerpus environment");

            return redirect(route('admin.ndla.index', ['page' => $request->input('page', 1)]));
        }
        return redirect(route('admin.ndla.index', ['page' => $request->input('page', 1)]));
    }

    public function destroy(Request $request, $id)
    {
        if (App::environment('local')) {
            $idMappers = NdlaIdMapper::articlesByNdlaId($id);
            $idMappers->each(function ($idMapper) {
                $article = Article::find($idMapper->ca_id);
                if ($article) {
                    $article->files()->delete();
                    File::deleteDirectory(public_path('h5pstorage/article-uploads/' . $article->id));
                    $article->forceDelete();
                }
                $idMapper->delete();
            });

        }

        return redirect(route('admin.ndla.index', ['page' => $request->input('page', 1)]));
    }

    public function refresh(Request $request)
    {
        set_time_limit(0);
        $startTime = microtime(true);
        /** @var ArticleApiClient $articleApiClient */
        $articleApiClient = app()->makeWith(\App\Libraries\NDLA\API\ArticleApiClient::class, ['client' => null, 'pageSize' => 10]);

        $articleApiClient->setPage(1)->getArticles();

        $articleId = \App\NdlaArticleId::max('id') + 1;
        if (!$articleId) {
            $articleId = 1;
        } else {
            $articleId++;
        }

        $importedArticles = \App\NdlaArticleId::count();
        if (!$importedArticles) {
            $importedArticles = 0;
        }
        $currentImportCount = 0;
        $notFoundCount = 0;
        do {
            try {
                $article = $articleApiClient->getArticle($articleId);
                \App\NdlaArticleId::updateOrCreate(
                    [
                        'id' => $article->id,
                    ],
                    [
                        'title' => ($article->title->title ?? 'No title'),
                        'language' => ($article->content->language ?? 'nb'),
                        'type' => ($article->articleType ?? 'unknown'),
                        'json' => $article,
                    ]
                );
                $importedArticles++;
                $currentImportCount++;
                $notFoundCount = 0;
                unset($article);
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                $notFoundCount++;
            }
            $articleId++;
        } while ($notFoundCount < 250); // Allow for holes in the ids, but stop import before it gets ridiculous

        $importTime = sprintf("%0.2f", microtime(true) - $startTime);

        $request->session()->flash('message', "Imported $currentImportCount articles in $importTime seconds.");

        return redirect(route('admin.ndla.index', ['page' => $request->input('page', 1)]));
    }

    public function search(Request $request)
    {
        $owner = $this->getImportOwner();

        $searchFor = $request->input('query', '');

        $isId = !!filter_var($searchFor, FILTER_VALIDATE_INT);

        $articles = App\NdlaArticleId::when(!$isId, function ($q) use ($searchFor) {
            return $q->where('title', 'LIKE', $searchFor . '%');
        })
            ->when($isId, function ($q) use ($searchFor) {
                return $q->orWhere('id', $searchFor);
            })
            ->paginate($this->articlesPerPage);

        $articles->each(function ($article) {
            $article->ca_id = null;
            $article->translations = [];
            $language = $article->json->content->language ?? 'nb';
            if ($imported = NdlaIdMapper::articleByNdlaId($article->id)) {
                $articleDb = Article::find($imported->ca_id);
                if ($articleDb) {
                    $article->ca_id = $articleDb->id;
                    $article->translations = $articleDb->getTranslations();
                }
            }

            return $article;
        });

        /** @var ArticleApiClient $articleApiClient */
        $articleApiClient = resolve(ArticleApiClient::class);
        $articleApiCount = $articleApiClient->fetchTotalArticleCount();

        $request->flashOnly('query', 'page');

        return view('admin.ndla-article-import.index')
            ->with(compact('articles', 'articleApiCount', 'owner'));
    }

    public function all(Request $request)
    {
        // $this->dispatch(new App\Jobs\ImportAllArticles());

        //echo "Peak memory use: " . sprintf("%0.3f", memory_get_peak_usage()/(1024*1024)) . "MB<br>";
        //echo "PHP memory use: " . sprintf("%0.3f", memory_get_usage()/(1024*1024)) . "MB<br>";

        return redirect()->back()->with('message', "I don't do that anymore. Use the Course Export tool to import multiple articles.");
    }

    public function status(Request $request)
    {
        $statuses = NdlaArticleImportStatus::select(['ndla_id', 'message', 'updated_at', 'import_id', 'log_level'])
            ->orderBy('id', 'desc')
            ->paginate($this->statusesPerPage);

        if (!$request->filled('log_level')) {
            $request->request->add(['log_level' => NdlaArticleImportStatus::LOG_LEVEL_DEBUG]);
        }

        $request->flashOnly('query', 'page', 'log_level');

        return view('admin.ndla-article-import.status')->with(compact('statuses'));
    }

    public function searchImportStatus(Request $request)
    {
        $searchFor = $request->input('query', '');
        $logLevel = (int)$request->input('log_level', NdlaArticleImportStatus::LOG_LEVEL_DEBUG);

        $request->flashOnly('query', 'page', 'log_level');

        if (empty($searchFor) && $logLevel === NdlaArticleImportStatus::LOG_LEVEL_DEBUG) {
            return redirect(route('admin.ndla.status'));
        }

        $statuses = NdlaArticleImportStatus::select(['ndla_id', 'message', 'updated_at', 'import_id', 'log_level'])
            ->when($searchFor, function ($query) use ($searchFor) {
                return $query->where('ndla_id', $searchFor)
                    ->where('ndla_id', '<>', 0)
                    ->orWhere('import_id', $searchFor);

            })
            ->where('log_level', '>=', $logLevel)
            ->orderBy('id', 'desc')
            ->paginate($this->statusesPerPage);


        return view('admin.ndla-article-import.status')
            ->with(compact('statuses'));
    }
}
