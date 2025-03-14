<?php

namespace App\Http\Controllers;

use App\Article;
use App\ContentVersion;
use App\Events\ArticleWasSaved;
use App\Exceptions\UnhandledVersionReasonException;
use App\Http\Libraries\License;
use App\Http\Requests\ArticleRequest;
use App\Libraries\DataObjects\ArticleStateDataObject;
use App\Libraries\DataObjects\EditorConfigObject;
use App\Libraries\DataObjects\LockedDataObject;
use App\Libraries\DataObjects\ResourceInfoDataObject;
use App\Libraries\H5P\Adapters\CerpusH5PAdapter;
use App\Libraries\HTMLPurify\Config\MathMLConfig;
use App\Lti\Lti;
use App\SessionKeys;
use App\Traits\ReturnToCore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\View\View;

use function config;

class ArticleController extends Controller
{
    use ReturnToCore;

    public function __construct(private readonly Lti $lti)
    {
        $this->middleware('core.return', ['only' => ['create', 'edit']]);
        $this->middleware('core.locale', ['only' => ['create', 'edit', 'store', 'update']]);
        $this->middleware('core.behavior-settings:view', ['only' => ['show']]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $ltiRequest = $this->lti->getRequest($request);

        $license = License::getDefaultLicense($ltiRequest);
        $emails = '';

        $config = json_encode([
            'editor' => [
                'extraAllowedContent' => implode(" ", CerpusH5PAdapter::getCoreExtraTags()),
                'editorBodyClass' => 'edlib-article',
            ],
        ]);

        $editorSetup = EditorConfigObject::create(
            [
                'canList' => true,
                'useLicense' => config('feature.licensing') === true || config('feature.licensing') === '1',
            ],
        )->toJson();

        $state = ArticleStateDataObject::create([
            'license' => $license,
            'isPublished' => $ltiRequest?->getPublished() ?? false,
            'isShared' => $ltiRequest?->getShared() ?? false,
            'redirectToken' => $request->get('redirectToken'),
            'route' => route('article.store'),
            '_method' => "POST",
        ])->toJson();

        return view('article.create')->with(compact([
            'emails', 'config', 'editorSetup', 'state',
        ]));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ArticleRequest $request): JsonResponse
    {
        $inputs = $request->all();
        if (!empty($inputs['content'])) {
            $inputs['content'] = $this->cleanContent($inputs['content']);
        }
        $article = new Article($inputs);
        $article->id = Str::uuid()->toString();
        $article->original_id = $article->id;
        $article->owner_id = Session::get('authId');
        $article->max_score = $article->getMaxScoreHelper($inputs['content']);
        $article->license = $request->input('license');

        // next line commented out in anticipation of permanently deciding if attribution for Articles is no longer maintained
        //$article->updateAttribution($inputs['origin'] ?? null, $inputs['originators'] ?? []);

        $article->is_draft = $request->input('isDraft', 0);

        $article->save();

        $this->moveTempFiles($article);

        $emailCollaborators = collect();
        if ($request->filled('col-emails')) {
            $emailCollaborators = collect(explode(",", $request->get('col-emails')));
        }

        // Handles collaborators, and registering a new version
        event(new ArticleWasSaved($article, $request, $emailCollaborators, Session::get('authId'), ContentVersion::PURPOSE_CREATE, Session::all()));

        $url = $this->getRedirectToCoreUrl($article->toLtiContent(
            published: $request->validated('isPublished'),
            shared: $request->validated('isShared'),
        ), $request->get('redirectToken'));

        return response()->json(['url' => $url], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return View
     */
    public function show($id)
    {
        /** @var Article $article */
        $article = Article::findOrFail($id);
        $customCSS = $this->lti->getRequest(request())?->getLaunchPresentationCssUrl();

        if (!is_null($customCSS)) {
            Session::flash(SessionKeys::EXT_CSS_URL, $customCSS);
        }

        $ndlaArticle = $article->isImported();
        $resourceType = sprintf($article::RESOURCE_TYPE_CSS, $article->getContentType());

        return view('article.show')->with(compact('article', 'customCSS', 'ndlaArticle', 'resourceType'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param string $id
     * @return View
     */
    public function edit(Request $request, $id)
    {
        $ltiRequest = $this->lti->getRequest($request);
        $article = Article::findOrFail($id);

        $origin = $article->getAttribution()->getOrigin();
        $originators = $article->getAttribution()->getOriginators();

        $emails = $this->getCollaboratorsEmails($article);

        $config = json_encode([
            'editor' => [
                'extraAllowedContent' => implode(" ", CerpusH5PAdapter::getCoreExtraTags()),
                'editorBodyClass' => !$article->isImported() ? 'edlib-article' : 'ndla-article',
            ],
            'article' => $article->toArray(),
        ]);

        $editorSetup = EditorConfigObject::create(
            [
                'canList' => $article->canList($request),
                'useLicense' => config('feature.licensing') === true || config('feature.licensing') === '1',
                'pulseUrl' => config('feature.content-locking') ? route('lock.pulse', ['id' => $id]) : null,
            ],
        );

        $editorSetup->setContentProperties(ResourceInfoDataObject::create([
            'id' => $article['id'],
            'createdAt' => $article->created_at->toIso8601String(),
            'maxScore' => $article->max_score,
            'ownerName' => null,
        ]));

        if (!$article->shouldCreateFork(Session::get('authId', false))) {
            if (($locked = $article->hasLock())) {
                $editUrl = $article->getEditUrl();
                $pollUrl = route('lock.status', $id);
                $editorSetup->setLockedProperties(LockedDataObject::create([
                    'pollUrl' => $pollUrl,
                    'editor' => $locked->getEditor(),
                    'editUrl' => $editUrl,
                ]));
            } else {
                $article->lock();
            }
        }

        $editorSetup = $editorSetup->toJson();

        $state = ArticleStateDataObject::create([
            'id' => $article->id,
            'title' => $article->title,
            'content' => $article->render(),
            'license' => $article->license,
            'isPublished' => $ltiRequest?->getPublished() ?? false,
            'isDraft' => $article->isDraft(),
            'isShared' => $ltiRequest?->getShared() ?? false,
            'redirectToken' => $request->get('redirectToken'),
            'route' => route('article.update', ['article' => $id]),
            '_method' => "PUT",
        ])->toJson();

        return view('article.edit')
            ->with(compact('article', 'emails', 'id', 'config', 'origin', 'originators', 'state', 'editorSetup'));
    }

    private function getCollaboratorsEmails(Article $article)
    {
        return implode(',', $article->collaborators->pluck('email')->toArray());
    }

    public function update(ArticleRequest $request, Article $article)
    {
        $oldArticle = clone $article;

        $oldLicense = $oldArticle->getContentLicense();
        $reason = $oldArticle->shouldCreateFork(Session::get('authId', false)) ? ContentVersion::PURPOSE_COPY : ContentVersion::PURPOSE_UPDATE;

        if ($reason === ContentVersion::PURPOSE_COPY && !$request->input("license", false)) {
            $request->merge(["license" => $oldLicense]);
        }

        // If you are a collaborator, use the old license
        if ($oldArticle->isCollaborator()) {
            $request->merge(["license" => $oldLicense]);
        }

        if ($oldArticle->requestShouldBecomeNewVersion($request)) {
            switch ($reason) {
                case ContentVersion::PURPOSE_UPDATE:
                    $article = $oldArticle->makeCopy();
                    break;
                case ContentVersion::PURPOSE_COPY:
                    $article = $oldArticle->makeCopy(Session::get('authId'));
                    break;
                default:
                    throw new UnhandledVersionReasonException("Unhandled Version Reason: $reason");
            }
        }

        $article->title = $request->get("title");
        $content = $request->get("content");
        if (!is_null($content)) {
            $article->content = $this->cleanContent($content);
        }
        $article->max_score = $article->getMaxScoreHelper($article->content);
        $article->license = $request->input('license', $oldLicense);
        $article->is_draft = $request->input('isDraft', false);

        //$article->updateAttribution($request->input('origin'), $request->input('originators', []));
        $article->save();
        $oldArticle->unlock();

        $collaborators = $this->handleCollaborators($request, $oldArticle, $article, $reason);

        // Do some final checking
        if (!$request->filled('license')) {
            $request->request->add(['license' => $oldLicense]);
        }

        event(new ArticleWasSaved($article, $request, $collaborators, Session::get('authId'), $reason, Session::all()));

        $url = $this->getRedirectToCoreUrl(
            $article->toLtiContent(
                published: $request->validated('isPublished'),
                shared: $request->validated('isShared'),
            ),
            $request->get('redirectToken'),
        );

        return response()->json([
            'url' => $url,
        ], Response::HTTP_CREATED);
    }


    private function cleanContent($content)
    {
        $config = app(MathMLConfig::class, [
            'HTML.MathML' => true,
            'HTML.SafeIframe' => true,
            'HTML.IframeAllowFullscreen' => true,
            'URI.SafeIframeRegexp' => '/^\/lti\/launch\?url=|players\.brightcove\.net\/|youtu\.be|youtube\.com|ndla\.filmiundervisning\.no/',
            'Attr.AllowedFrameTargets' => ['_blank', '_top', '_self', '_parent'],
        ]);

        if (getenv('RUN_MODE') === 'phpunit' || App::environment('testing')) {
            $config->set('Cache.DefinitionImpl', null);
        }

        $purifier = app(\HTMLPurifier::class, [$config]);
        return $purifier->purify($content);
    }

    protected function moveTempFiles(Article $article)
    {
        $files = collect(Session::get(Article::TMP_UPLOAD_SESSION_KEY), []);

        $files->each(function ($file) use ($article) {
            if ($file->moveTempToArticle($article)) {
                $originalPath = $file->generateTempPath();
                $newPath = $file->generatePath();
                $article->rewriteUrls($originalPath, $newPath);
                $article->save();
            }
        });

        Session::forget(Article::TMP_UPLOAD_SESSION_KEY);
    }

    protected function handleCollaborators(Request $request, Article $oldArticle, Article $newArticle, $reason): Collection
    {
        switch ($reason) {
            case ContentVersion::PURPOSE_UPDATE:
                $collaborators = "";
                if (!$newArticle->isOwner(Session::get('authId'))) { // Collaborators cannot update collaborators
                    $collaborators = $this->getCollaboratorsEmails($oldArticle);
                } else {
                    if ($request->filled('col-emails')) {
                        $collaborators = $request->get('col-emails');
                    }
                }
                return collect(explode(",", $collaborators));

            case ContentVersion::PURPOSE_COPY:
            default:
                return collect();
        }
    }
}
