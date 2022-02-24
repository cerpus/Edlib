<?php

namespace App\Http\Controllers\Admin;

use App\Article;
use App\Norgesfilm;
use App\Traits\HTMLHelper;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use App\Jobs\PopulateNorgesfilm;
use App\Http\Controllers\Controller;
use Cerpus\VersionClient\VersionClient;
use Cerpus\VersionClient\interfaces\VersionDataInterface;
use App\Libraries\NDLA\Importers\Handlers\APIArticle\Norgesfilm as NorgesfilmProcessor;

class NorgesfilmController extends Controller
{
    use HTMLHelper;

    protected $messages = [];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = $request->input('search', null);

        $articles = Norgesfilm::select(['id', 'article_title', 'article_url', 'ndla_url'])
            ->when($search, function ($q) use ($request) {
                return $q->where('article_title', 'like', "%{$request->input('search', '')}%");
            })
            ->orderBy('article_title')
            ->paginate(25);

        return view('admin.norgesfilm.index')->with(compact('articles', 'search'));
    }

    public function compare(Request $request, Norgesfilm $norgesfilm)
    {
        $norgesfilm->load('article');

        if (!$norgesfilm->article) {
            return "Local article not found";
        }

        $versionClient = app(VersionClient::class);
        /** @var VersionDataInterface $remoteVersions */
        $remoteVersions = $versionClient->getVersion($norgesfilm->article->version_id);
        $versions = (object)[
            'id' => $norgesfilm->article->id,
            'title' => $norgesfilm->article->title,
            'created_at' => $norgesfilm->article->created_at ?? null,
            'url' => route('article.show', $norgesfilm->article),
            'children' => $this->children($remoteVersions->getChildren()),
        ];

        return view('admin.norgesfilm.compare')->with(compact('norgesfilm', 'versions'));
    }

    private function children($nodes)
    {
        $children = [];

        /** @var VersionDataInterface $node */
        foreach ($nodes as $node) {
            if ($article = Article::find($node->getExternalReference())) {
                $children[] = (object)[
                    'id' => $article->id ?? null,
                    'title' => $article->title ?? 'Failed!',
                    'created_at' => $article->created_at ?? null,
                    'url' => route('article.show', $node->getExternalReference()),
                    'children' => empty($node->getChildren()) ? [] : $this->children($node->getChildren()),
                ];
            }
        }

        return $children;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function replace(Request $request, $id)
    {
        $article = null;

        if (Uuid::isValid($id)) { // article
            if (!$article = Article::find($id)) {
                $request->session()->flash('message', 'ID error. Unable to find article.');
                return redirect(route('admin.norgesfilm.index'));
            }
        } elseif (is_int((int)$id)) {
            if (!$norgesfilm = Norgesfilm::find($id)) {
                $request->session()->flash('message', 'ID error. Unable to find norgesfilm article.');
                return redirect(route('admin.norgesfilm.index'));
            }
            $article = $norgesfilm->article;
        }

        app(NorgesfilmProcessor::class)->process($article, null);

        $request->session()->flash('message', 'Inserted placeholders');

        return redirect(route('admin.norgesfilm.index'));
    }


    public function populate()
    {
        PopulateNorgesfilm::dispatch()->onQueue('norgesfilm');
        request()->session()->flash('message', 'Updating list of Norgesfilm usage.');

        return redirect(route('admin.norgesfilm.index'));
    }

    public function ndlaUrlNotFound()
    {
        return view('admin.norgesfilm.ndla-url-not-found');
    }
}
