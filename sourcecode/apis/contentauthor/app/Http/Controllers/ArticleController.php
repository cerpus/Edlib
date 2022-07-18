<?php

namespace App\Http\Controllers;

use App\ACL\ArticleAccess;
use App\Article;
use App\Content;
use App\Events\ArticleWasSaved;
use App\Events\ContentCreated;
use App\Events\ContentCreating;
use App\Events\ContentUpdated;
use App\Events\ContentUpdating;
use App\Exceptions\UnhandledVersionReasonException;
use App\H5pLti;
use App\Http\Libraries\License;
use App\Http\Libraries\LtiTrait;
use App\Http\Requests\ArticleRequest;
use App\Libraries\DataObjects\ArticleStateDataObject;
use App\Libraries\DataObjects\EditorConfigObject;
use App\Libraries\DataObjects\LockedDataObject;
use App\Libraries\DataObjects\ResourceInfoDataObject;
use App\Libraries\H5P\Adapters\CerpusH5PAdapter;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use App\Libraries\HTMLPurify\Config\MathMLConfig;
use App\SessionKeys;
use App\Traits\ReturnToCore;
use Cerpus\VersionClient\VersionData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\View\View;
use function Cerpus\Helper\Helpers\profile as config;

class ArticleController extends Controller
{
    use ArticleAccess;
    use LtiTrait;
    use ReturnToCore;

    protected H5pLti $lti;

    public function __construct(H5pLti $h5pLti)
    {
        $this->middleware('core.return', ['only' => ['create', 'edit']]);
        $this->middleware('core.auth', ['only' => ['create', 'edit', 'store', 'update']]);
        $this->middleware('core.locale', ['only' => ['create', 'edit', 'store', 'update']]);
        $this->middleware('core.behavior-settings:view', ['only' => ['show']]);

        $this->lti = $h5pLti;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        abort(404);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        if (!$this->canCreate()) {
            abort(403);
        }

        $ltiRequest = $this->lti->getValidatedLtiRequest();

        $license = License::getDefaultLicense($ltiRequest);
        $emails = '';

        $jwtTokenInfo = Session::get('jwtToken', null);
        $jwtToken = $jwtTokenInfo && isset($jwtTokenInfo['raw']) ? $jwtTokenInfo['raw'] : null;

        $config = json_encode([
            'editor' => [
                'extraAllowedContent' => implode(" ", CerpusH5PAdapter::getCoreExtraTags()),
                'editorBodyClass' => 'edlib-article',
            ],
        ]);

        /** @var H5PAdapterInterface $adapter */
        $adapter = app(H5PAdapterInterface::class);

        $editorSetup = EditorConfigObject::create([
                'userPublishEnabled' => $adapter->isUserPublishEnabled(),
                'canPublish' => true,
                'canList' => true,
                'useLicense' => config('feature.licensing') === true || config('feature.licensing') === '1',
            ]
        )->toJson();

        $state = ArticleStateDataObject::create([
            'license' => $license,
            'isPublished' => false,
            'share' => config('h5p.defaultShareSetting'),
            'redirectToken' => $request->get('redirectToken'),
            'route' => route('article.store'),
            '_method' => "POST",
        ])->toJson();

        return view('article.create')->with(compact([
            'jwtToken', 'emails', 'config', 'editorSetup', 'state'
        ]));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ArticleRequest $request): JsonResponse
    {
        if (!$this->canCreate()) {
            abort(403);
        }

        event(new ContentCreating($request));

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

        $article->is_published = $article::isUserPublishEnabled() ? $request->input('isPublished', 1) : 1;
        $article->is_draft = $request->input('isDraft', 0);

        $article->save();

        $this->moveTempFiles($article);

        $emailCollaborators = collect();
        if ($request->filled('col-emails')) {
            $emailCollaborators = collect(explode(",", $request->get('col-emails')));
        }

        // Handles privacy, collaborators, and registering a new version
        event(new ArticleWasSaved($article, $request, $emailCollaborators, Session::get('authId'), VersionData::CREATE, Session::all()));

        // A more Laravelly event system
        event(new ContentCreated($article));

        $urlToCore = $this->getRedirectToCoreUrl(
            $article->id,
            $article->title,
            'Article',
            $article->givesScore(),
            $request->get('redirectToken')
        ); // Will not return if we have a returnURL

        $responseValues = [
            'url' => !is_null($urlToCore) ? $urlToCore : route('article.edit', $article->id),
        ];

        return response()->json($responseValues, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return View
     */
    public function doShow($id, $context, $preview = false)
    {
        /** @var Article $article */
        $article = Article::findOrFail($id);
        $customCSS = !empty($this->lti->getValidatedLtiRequest()) ? $this->lti->getValidatedLtiRequest()->getLaunchPresentationCssUrl() : null;
        if (!$article->canShow($preview)) {
            return view('layouts.draft-resource', [
                'styles' => !is_null($customCSS) ? [$customCSS] : [],
            ]);
        }

        if (!is_null($customCSS)) {
            Session::flash(SessionKeys::EXT_CSS_URL, $customCSS);
        }

        $article->convertToCloudPaths();
        $ndlaArticle = $article->isImported();
        $inDraftState = !$article->isActuallyPublished();
        $resourceType = sprintf($article::RESOURCE_TYPE_CSS, $article->getContentType());

        return view('article.show')->with(compact('article', 'customCSS', 'preview', 'ndlaArticle', 'inDraftState', 'resourceType'));
    }

    public function show($id)
    {
        return $this->doShow($id, null);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param string $id
     * @return View
     */
    public function edit(Request $request, $id)
    {
        /** @var Article $article */
        $article = Article::findOrFail($id);

        if (!$this->canUpdateArticle($article)) {
            abort(403);
        }

        $origin = $article->getAttribution()->getOrigin();
        $originators = $article->getAttribution()->getOriginators();

        $ownerName = $article->getOwnerName($article->owner_id);

        $article->convertToCloudPaths();

        $emails = $this->getCollaboratorsEmails($article);

        $jwtTokenInfo = Session::get('jwtToken', null);
        $jwtToken = $jwtTokenInfo && isset($jwtTokenInfo['raw']) ? $jwtTokenInfo['raw'] : null;

        $config = json_encode([
            'editor' => [
                'extraAllowedContent' => implode(" ", CerpusH5PAdapter::getCoreExtraTags()),
                'editorBodyClass' => !$article->isImported() ? 'edlib-article' : 'ndla-article',
            ],
            'article' => $article->toArray(),
        ]);

        $editorSetup = EditorConfigObject::create([
                'userPublishEnabled' => Content::isUserPublishEnabled(),
                'canPublish' => $article->canPublish($request),
                'canList' => $article->canList($request),
                'useLicense' => config('feature.licensing') === true || config('feature.licensing') === '1',
                'pulseUrl' => config('feature.content-locking') ? route('lock.status', ['id' => $id]) : null,
            ]
        );

        $editorSetup->setContentProperties(ResourceInfoDataObject::create([
            'id' => $article['id'],
            'createdAt' => $article->created_at->toIso8601String(),
            'maxScore' => $article->max_score,
            'ownerName' => !empty($ownerName) ? $ownerName : null,
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
            'content' => $article->content,
            'license' => $article->license,
            'isPublished' => $article->isPublished(),
            'isDraft' => $article->isDraft(),
            'share' => !$article->isListed() ? 'private' : 'share',
            'redirectToken' => $request->get('redirectToken'),
            'route' => route('article.update', ['article' => $id]),
            '_method' => "PUT",
        ])->toJson();

        return view('article.edit')
            ->with(compact('jwtToken', 'article', 'emails', 'id', 'config', 'origin', 'originators', 'state', 'editorSetup'));
    }

    private function getCollaboratorsEmails(Article $article)
    {
        return implode(',', $article->collaborators->pluck('email')->toArray());
    }

    public function update(ArticleRequest $request, Article $article)
    {
        $oldArticle = clone $article;
        if (!$this->canUpdateArticle($oldArticle)) {
            abort(403);
        }

        event(new ContentUpdating($article, $request));

        $oldLicense = $oldArticle->getContentLicense();
        $reason = $oldArticle->shouldCreateFork(Session::get('authId', false)) ? VersionData::COPY : VersionData::UPDATE;

        if ($reason === VersionData::COPY && !$request->input("license", false)) {
            $request->merge(["license" => $oldLicense]);
        }

        // If you are a collaborator, use the old license
        if ($oldArticle->isCollaborator()) {
            $request->merge(["license" => $oldLicense]);
        }

        if ($oldArticle->requestShouldBecomeNewVersion($request)) {
            switch ($reason) {
                case VersionData::UPDATE:
                    $article = $oldArticle->makeCopy();
                    break;
                case VersionData::COPY:
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
        $article->is_published = $article::isUserPublishEnabled() ? $request->input('isPublished', 1) : 1;
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

        event(new ContentUpdated($article, $oldArticle));

        $urlToCore = $this->getRedirectToCoreUrl(
            $article->id,
            $article->title,
            'Article',
            $article->givesScore(),
            $request->get('redirectToken')
        ); // Will not return if we have a returnURL

        $responseValues = [
            'url' => !is_null($urlToCore) ? $urlToCore : route('article.edit', $article->id),
        ];

        return response()->json($responseValues, Response::HTTP_CREATED);
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
            case VersionData::UPDATE:
                $collaborators = "";
                if (!$newArticle->isOwner(Session::get('authId'))) { // Collaborators cannot update collaborators
                    $collaborators = $this->getCollaboratorsEmails($oldArticle);
                } else {
                    if ($request->filled('col-emails')) {
                        $collaborators = $request->get('col-emails');
                    }
                }
                return collect(explode(",", $collaborators));

            case VersionData::COPY:
            default:
                return collect();
        }
    }
}
