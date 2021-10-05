<?php

namespace App\Http\Controllers\Admin;

use App\Article;
use App\Content;
use App\H5PContent;
use App\Http\Controllers\Controller;
use App\Jobs\REBulkIndexAllContent;
use App\Jobs\REBulkIndexAllNDLAArticlesContent;
use Cerpus\REContentClient\ContentClient;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;

class RecommendationEngineController extends Controller
{
    public function index()
    {
        if (!config("feature.enable-recommendation-engine")) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $indexNum = Redis::get("re-indexing-jobs");

        return view("admin.recommendation-engine.index")->with(compact("indexNum"));
    }

    public function doIndex(Request $request)
    {
        if (!config("feature.enable-recommendation-engine")) {
            abort(Response::HTTP_NOT_FOUND);
        }

        REBulkIndexAllContent::dispatch();

        $request->session()->flash("message", "Indexing is starting");

        return redirect(route("admin.recommendation-engine.index"));
    }

    public function indexNdlaArticles(Request $request)
    {
        if (!config("feature.enable-recommendation-engine")) {
            abort(Response::HTTP_NOT_FOUND);
        }

        REBulkIndexAllNDLAArticlesContent::dispatch();

        $request->session()->flash("message", "Indexing of NDLA articles is starting");

        return redirect(route("admin.recommendation-engine.index"));
    }

    public function search(Request $request)
    {
        if (!config("feature.enable-recommendation-engine")) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $query = $request->input("query");
        $request->flash("query");
        $results = collect();
        if ($query) {
            $h5ps = H5PContent::select(["id", "title", "version_id", "library_id", "created_at"])->where("title", "like", "$query%")->get();
            $results = $results->merge($h5ps);
            $articles = Article::select(["id", "title", "version_id", "created_at"])->where("title", "like", "$query%")->get();
            $results = $results->merge($articles);

            $results = $this->addExtraInfo($results)
            ->sortByDesc("in_re");

        }

        return view("admin.recommendation-engine.search", compact("results"));
    }

    protected function addExtraInfo(Collection $results): Collection
    {
        $reCClient = app(ContentClient::class);

        $results = $results->each(function (Content $result) use ($reCClient) {
            $reId = $result->getPublicId();
            $result->in_re = $reCClient->exists($reId);
            $result->type = $result->getContentType();
        });

        return $results;
    }

    public function remove(Request $request, $id, $query)
    {
        $request->flash(["query"]);

        $content = Content::findContentById($id);

        $reCClient = app(ContentClient::class);

        $reCClient->removeIfExists($content->toREContent());

        return redirect(route("admin.recommendation-engine.search", ["query" => $query]));
    }
}
