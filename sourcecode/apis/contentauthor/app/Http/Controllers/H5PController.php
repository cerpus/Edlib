<?php

namespace App\Http\Controllers;

use App\Events\ContentCreated;
use App\Events\ContentCreating;
use App\Events\ContentUpdated;
use App\Events\ContentUpdating;
use App\H5PFile;
use App\Jobs\H5PFilesUpload;
use App\Libraries\DataObjects\LockedDataObject;
use App\Libraries\H5P\Dataobjects\H5PAlterParametersSettingsDataObject;
use App\Libraries\DataObjects\H5PEditorConfigObject;
use App\Libraries\DataObjects\H5PStateDataObject;
use App\Libraries\H5P\H5PExport;
use DB;
use Cache;
use H5PCore;
use H5peditor;
use Exception;
use App\H5pLti;
use Carbon\Carbon;
use App\H5PContent;
use App\H5PLibrary;
use App\SessionKeys;
use App\H5PCollaborator;
use App\Libraries\H5P\h5p;
use App\Events\H5PWasSaved;
use Illuminate\Http\Request;
use App\Traits\ReturnToCore;
use App\Events\ResourceSaved;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Iso639p3;
use MatthiasMullie\Minify\CSS;
use App\Libraries\H5P\H5Plugin;
use App\Http\Libraries\License;
use App\Http\Libraries\LtiTrait;
use App\Libraries\H5P\ViewConfig;
use App\Libraries\H5P\AdminConfig;
use App\Libraries\H5P\AjaxRequest;
use App\Libraries\H5P\H5PProgress;
use App\Libraries\H5P\EditorConfig;
use App\Libraries\H5P\H5PCopyright;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Cerpus\VersionClient\VersionData;
use App\Libraries\H5P\H5PLibraryAdmin;
use Illuminate\Support\Facades\Session;
use App\Libraries\DataObjects\ResourceDataObject;
use App\Libraries\H5P\Interfaces\H5PAudioInterface;
use App\Libraries\H5P\Interfaces\H5PVideoInterface;
use App\Libraries\DataObjects\ResourceInfoDataObject;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use App\Libraries\H5P\Interfaces\H5PImageAdapterInterface;
use stdClass;
use function Cerpus\Helper\Helpers\profile as config;

class H5PController extends Controller
{
    use LtiTrait;
    use ReturnToCore;

    private $viewDataCacheName = 'viewData-';
    private $errorMessage;

    protected $h5pPlugin;
    protected $lti;
    private $sendEmail = true;

    /** @var h5p */
    private $h5p;

    /**
     * Constructor.
     *
     * @param H5pLti $h5pLti
     */
    public function __construct(H5pLti $h5pLti)
    {
        $this->h5pPlugin = H5Plugin::get_instance(DB::connection()->getPdo());
        $this->middleware('adaptermode', ['only' => ['show', 'edit', 'update', 'store', 'create']]);
        $this->middleware('draftaction', ['only' => ['edit', 'update', 'store', 'create']]);
        $this->middleware('core.return', ['only' => ['create', 'edit']]);
        $this->middleware('core.auth', ['only' => ['create', 'edit', 'store', 'update']]);
        $this->middleware('core.ownership', ['only' => ['edit', 'update']]);
        $this->middleware('core.locale', ['only' => ['create', 'edit', 'store']]);
        $this->middleware('core.embed-url', ['only' => ['show', 'ltiShow']]);
        $this->lti = $h5pLti;
    }

    private function initH5P()
    {
        if (is_null($this->h5p)) {
            $this->h5p = resolve(h5p::class);
        }
    }

    /**
     * Handle index route.
     *
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function index()
    {
        $title = "Viewing H5P content";
        return view('h5p.index', ['title' => $title, 'message' => trans('h5p-editor.need-id')]);
    }

    /**
     * Compare 2 hashes.
     *
     * @param mixed $a
     * @param mixed $b
     * @return bool
     */
    private function hash_compare($a, $b): bool
    {
        if (!is_string($a) || !is_string($b)) {
            return false;
        }

        $len = strlen($a);
        if ($len !== strlen($b)) {
            return false;
        }

        $status = 0;
        for ($i = 0; $i < $len; $i++) {
            $status |= ord($a[$i]) ^ ord($b[$i]);
        }
        return $status === 0;
    }

    public function doShow($id, $context, $preview = false)
    {
        try {
            $styles = [];
            if (!empty($this->lti->getLtiRequest()) && !is_null($this->lti->getLtiRequest()->getLaunchPresentationCssUrl())) {
                $styles[] = $this->lti->getLtiRequest()->getLaunchPresentationCssUrl();
                \Session::flash(SessionKeys::EXT_CSS_URL, $this->lti->getLtiRequest()->getLaunchPresentationCssUrl());
            }
            /** @var H5PContent $h5pContent */
            $h5pContent = H5PContent::findOrFail($id);
            if (!$h5pContent->canShow($preview)) {
                return view('layouts.draft-resource', compact('styles'));
            }
            $h5p = new h5p(DB::connection()->getPdo());
            if (Session::get('userId', false)) {
                $h5p->setUserId(Session::get('userId', false));
            }
            $viewConfig = (resolve(ViewConfig::class))
                ->setId($id)
                ->setUserId(Session::get('userId', false))
                ->setUserName(Session::get('name', false))
                ->setEmail(Session::get('email', false))
                ->setPreview($preview)
                ->setContext($context);
            $viewConfig->setAlterParametersSettings(H5PAlterParametersSettingsDataObject::create(['useImageWidth' => $h5pContent->library->includeImageWidth()]));

            $h5p->init($viewConfig);
            $content = $h5p->getContents($id);
            $settings = $h5p->getSettings();
            $styles = array_merge($h5p->getStyles(), $styles);

            $viewData = [
                'id' => $id,
                'title' => $content['title'],
                'embed' => '<div class="h5p-content" data-content-id="' . $content['id'] . '"></div>',
                'config' => $settings,
                'jsScripts' => $h5p->getScripts(),
                'styles' => $styles,
                'inlineStyle' => (new CSS())->add($viewConfig->getCss(true))->minify(),
                'inDraftState' => $h5pContent->inDraftState(),
                'preview' => $preview,
                'resourceType' => sprintf($h5pContent::RESOURCE_TYPE_CSS, $h5pContent->getContentType()),
            ];
        } catch (\Throwable $t) {
            Log::error('[' . app('requestId') . '] ' . __METHOD__ . '(' . $t->getLine() . '): ' . $t->getMessage(), [
                'user' => Session::get('userId', 'not-logged-in-user'),
                'url' => request()->url(),
                'request' => request()->all(),
            ]);
            abort(404, 'Resource not found.');
        }

        return view('h5p.show', $viewData);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return View
     * @throws Exception
     */
    public function show($id, Request $request): View
    {
        Log::info('[' . app('requestId') . '] ' . "Show H5P: $id, user: " . Session::get('userId', 'not-logged-in-user'));
        if ($request->get('hash') != null) {
            $url = $request->url();
            $hash = $request->except('hash');
            $url = $url . '?' . http_build_query($hash, '', '&');
            $hash_expected = hash_hmac('sha256', $url, 'secret2');
            $hash_recieved = $request->get('hash');
            if (!$this->hash_compare($hash_recieved, $hash_expected)) {
                Log::error('[' . app('requestId') . '] ' . 'Access denied. Invalid hash when showing: ' . $id . " Expected: $hash_expected, actual: $hash_recieved.");
                throw new Exception("Access denied. Invalid hash", 403);
            }
        } else {
            Log::error('[' . app('requestId') . '] ' . __METHOD__ . "Invalid or no hash showing H5P $id.", [
                'user' => Session::get('userId', 'not-logged-in-user'),
                'url' => request()->url(),
                'request' => request()->all()
            ]);
            if (!config("app.debug")) {
                abort(403, "Access denied. No hash");
            }
        }
        return $this->doShow($id, null);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(Request $request, H5PCore $core, $contenttype = null): View
    {
        Log::info('[' . app('requestId') . '] ' . "Create H5P, user: " . Session::get('authId', 'not-logged-in-user'));
        $redirectToken = $request->input('redirectToken');
        $h5p = new h5p(DB::connection()->getPdo());

        $language = $this->getTargetLanguage(Session::get('locale') ?? config("h5p.default-resource-language"));
        try {
            $language = Iso639p3::code($language);
        } catch (Exception $e) {
        }

        $editorConfig = (resolve(EditorConfig::class))
            ->setUserId(Session::get('authId', false))
            ->setUserName(Session::get('userName', false))
            ->setEmail(Session::get('email', false))
            ->setName(Session::get('name', false))
            ->setRedirectToken($redirectToken)
            ->setDisplayHub(empty($contenttype))
            ->setLanguage(Iso639p3::code2letters($language))
            ->hideH5pJS();

        $h5p->init($editorConfig);

        $licenseLib = new License(config('license'), config('cerpus-auth.key'), config('cerpus-auth.secret'));

        $jwtTokenInfo = Session::get('jwtToken', null);
        $jwtToken = $jwtTokenInfo && isset($jwtTokenInfo['raw']) ? $jwtTokenInfo['raw'] : null;

        $displayOptions = $core->getDisplayOptionsForEdit();
        $core->getStorableDisplayOptions($displayOptions, null);

        $adapter = app(H5PAdapterInterface::class);

        if (!is_null($contenttype) && !H5PCore::libraryFromString($contenttype)) {
            /** @var H5PLibrary $library */
            $library = H5PLibrary::fromLibraryName($contenttype)
                ->latestVersion()
                ->first();
            if (!empty($library)) {
                $contenttype = $library->getLibraryString(false);
            } else {
                $contenttype = false;
            }
        }

        $editorSetup = H5PEditorConfigObject::create([
            'useDraft' => $adapter->enableDraftLogic(),
            'canPublish' => true,
            'canList' => true,
            'showDisplayOptions' => config('h5p.showDisplayOptions'),
            'autoTranslateTo' => $adapter->autoTranslateTo(),
            'useLicense' => config('feature.licensing') === true || config('feature.licensing') === '1',
            'hideNewVariant' => true,
            'adapterName' => config('feature.allow-mode-switch') === true ? $adapter->getAdapterName() : null,
            'adapterList' => $adapter::getAllAdapters(),
            'h5pLanguage' => Iso639p3::code2letters($language),
            'creatorName' => Session::get("name"),
        ]);

        $state = H5PStateDataObject::create($displayOptions + [
                'library' => $contenttype,
                'license' => $licenseLib->getDefaultLicense(),
                'isPublished' => false,
                'share' => config('h5p.defaultShareSetting'),
                'language_iso_639_3' => $language,
                'redirectToken' => $request->get('redirectToken'),
                'route' => route('h5p.store'),
                '_method' => "POST",
            ])->toJson();

        return view('h5p.create',
            [
                'jwtToken' => $jwtToken,
                'config' => $h5p->getSettings(),
                'jsScript' => $h5p->getScripts(false),
                'styles' => $h5p->getStyles(false),
                'emails' => '',
                'libName' => $contenttype,
                'editorSetup' => $editorSetup->toJson(),
                'state' => $state,
                'configJs' => $adapter->getConfigJs(),
            ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Request $request
     * @param int $id
     * @return View
     */
    public function edit(Request $request, $id): View
    {
        Log::info('[' . app('requestId') . '] ' . "Edit H5P: $id, user: " . Session::get('authId', 'not-logged-in-user'));

        $h5pCore = resolve(H5PCore::class);

        /** @var H5PContent $h5pContent */
        $h5pContent = H5PContent::with(['library', 'ndlaMapper', 'metadata'])->find($id);
        $ownerName = $h5pContent->getOwnerName($h5pContent->user_id);

        /** @var H5PAdapterInterface $adapter */
        $adapter = app(H5PAdapterInterface::class);
        $contentLanguage = $this->getTargetLanguage($h5pContent->language_iso_639_3);
        $isNewLanguageVariant = $adapter->autoTranslateTo() !== null && $contentLanguage === $adapter->autoTranslateTo() && $h5pContent->language_iso_639_3 !== $adapter->autoTranslateTo();
        $h5pLanguage = $this->getTargetLanguage($h5pContent->metadata->default_language ?? null);
        if (!is_null($h5pLanguage)){
            $h5pLanguage = Iso639p3::code2letters($h5pLanguage);
        }

        $h5p = new h5p(DB::connection()->getPdo());
        $editorConfig = (resolve(EditorConfig::class))
            ->setId($id)
            ->setUserId(Session::get('authId', false))
            ->setUserName(Session::get('userName', false))
            ->setEmail(Session::get('email', false))
            ->setName(Session::get('name', false))
            ->setRedirectToken($request->get('redirectToken'))
            ->setLanguage($h5pLanguage)
            ->hideH5pJS();

        $h5p->init($editorConfig);
        $content = $h5p->getContents($id);
        if (empty($content)) {
            Log::error('[' . app('requestId') . '] ' . __METHOD__ . ": H5P $id is empty. UserId: " . Session::get('authId', 'not-logged-in-user'), [
                'user' => Session::get('authId', 'not-logged-in-user'),
                'url' => request()->url(),
                'request' => request()->all()
            ]);
            abort(404, 'Resource not found');
        }
        if (!empty($content['params'])) {
            $content['params'] = str_replace("[[]]", "[{}]", $content['params']); //remove
        }
        $params = json_encode([
            'params' => json_decode($h5pCore->filterParameters($content)),
            'metadata' => $content['metadata'],
        ]);

        $params = $adapter->alterParameters($params, H5PAlterParametersSettingsDataObject::create(['useImageWidth' => false]));

        $licenseLib = new License(config('license'), config('cerpus-auth.key'), config('cerpus-auth.secret'));

        $jwtTokenInfo = Session::get('jwtToken', null);
        $jwtToken = $jwtTokenInfo && isset($jwtTokenInfo['raw']) ? $jwtTokenInfo['raw'] : null;

        /** @var H5PLibrary $library */
        $library = $h5pContent->library;
        $settings = [];
        $scripts = $h5p->getScripts(false);

        $editorSetup = H5PEditorConfigObject::create([
            'useDraft' => $adapter->enableDraftLogic(),
            'canPublish' => $h5pContent->canPublish($request),
            'canList' => $h5pContent->canList($request),
            'showDisplayOptions' => config('h5p.showDisplayOptions'),
            'autoTranslateTo' => $adapter->autoTranslateTo(),
            'useLicense' => config('feature.licensing') === true || config('feature.licensing') === '1',
            'h5pLanguage' => $h5pLanguage,
            'pulseUrl' => config('feature.content-locking') ? route('lock.status', ['id' => $id]) : null,
            ]);

        if ($h5pContent->canList($request)) {
            $config = (resolve(AdminConfig::class))
                ->setId($id);
            $config->h5plugin = $this->h5pPlugin;
            $config->getConfig();
            $config->addUpdateScripts(true);
            $settings = $config->getSettings($library);
            $scripts = array_merge($scripts, $config->getScriptAssets());
            $editorSetup->libraryUpgradeList = $library->getUpgrades(false);
        }

        $displayOptions = $h5pCore->getDisplayOptionsForEdit($h5pContent->disable);
        $h5pCore->getStorableDisplayOptions($displayOptions, $h5pContent->disable);
        $displayOptions['download'] = $displayOptions['export'];

        $editorSetup->setContentProperties(ResourceInfoDataObject::create([
            'id' => (string)$content['id'],
            'createdAt' => $h5pContent->created_at->toIso8601String(),
            'type' => $library->getTitleAndVersionString(),
            'maxScore' => $library->supportsMaxScore() ? $h5pContent->max_score : null,
            'ownerName' => !empty($ownerName) ? $ownerName : null,
        ]));

        if (!$h5pContent->shouldCreateFork(Session::get('authId', false))) {
            if (($locked = $h5pContent->hasLock())){
                $editUrl = $h5pContent->getEditUrl();
                $pollUrl = route('lock.status', $id);
                $editorSetup->setLockedProperties(LockedDataObject::create([
                    'pollUrl' => $pollUrl,
                    'editor' => $locked->getEditor(),
                    'editUrl' => $editUrl,
                ]));
            } else {
                $h5pContent->lock();
            }
        }

        $state = H5PStateDataObject::create($displayOptions + [
            'id' => $h5pContent->id,
            'library' => $library->getLibraryString(),
            'libraryid' => $h5pContent->library_id,
            'parameters' => $params,
            'language_iso_639_3' => $contentLanguage,
            'isNewLanguageVariant' => $isNewLanguageVariant,
            'title' => $h5pContent->title,
            'license' => $licenseLib->getLicense($content["id"]) ?: $licenseLib->getDefaultLicense(),
            'isPublished' => !$h5pContent->inDraftState(),
            'share' => !$h5pContent->isPublished() ? 'private' : 'share',
            'redirectToken' => $request->get('redirectToken'),
            'route' => route('h5p.update', ['h5p' => $id]),
            'max_score' => $h5pContent->max_score,
            '_method' => "PUT",
        ])->toJson();

        return view('h5p.edit',
            [
                'jwtToken' => $jwtToken,
                'id' => $id,
                'h5p' => $h5pContent,
                'config' => $h5p->getSettings(),
                'adminConfig' => "<script>H5PAdminIntegration = " . json_encode($settings) . "</script>",
                'jsScript' => $scripts,
                'styles' => $h5p->getStyles(false),
                'libName' => $h5pCore->libraryToString($content['library']),
                'emails' => $this->get_content_shares($id),
                'hasUserProgress' => $this->hasUserProgress($h5pContent),
                'editorSetup' => $editorSetup->toJson(),
                'state' => $state,
                'configJs' => $adapter->getConfigJs(),
            ]);
    }

    private function getVersionPurpose(Request $request, H5PContent $h5p, $authId): string
    {
        if ($request->get("isNewLanguageVariant", false)) {
            return VersionData::TRANSLATION;
        }

        if ($h5p->shouldCreateFork($authId)) {
            return VersionData::COPY;
        }

        return VersionData::UPDATE;
    }

    private function getTargetLanguage(?string $language)
    {
        $contentLanguage = $language;
        if (($ltiRequest = $this->lti->getLtiRequest()) !== null) {
            $ltiLanguage = $ltiRequest->getExtTranslationLanguage();
            if( !empty($ltiLanguage) ){
                $contentLanguage = $ltiLanguage;
            }
        }
        return $contentLanguage;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param H5PContent $h5p
     * @return \Illuminate\Http\JsonResponse|Response
     * @throws Exception
     */
    public function update(Request $request, H5PContent $h5p, H5PCore $core)
    {
        $this->initH5P();
        if ($this->h5p->validateStoreInput($request, $h5p) !== true) {
            return response($this->h5p->getErrorMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        event(new ContentUpdating($h5p, $request));

        $authId = Session::get('authId', false);
        $versionPurpose = $this->getVersionPurpose($request, $h5p, $authId);
        [$oldContent, $content, $newH5pContent] = $this->performUpdate($request, $h5p, $authId, $versionPurpose);

        //
        $this->sendCollaboratorInviteEmails($newH5pContent, $h5p);
        Cache::forget($this->viewDataCacheName . $content['id']);
        if ($oldContent['library']['name'] !== $content['library']['machineName']) {
            // Remove old progresses
            $progress = new H5PProgress(DB::connection()->getPdo(), Session::get('authId'));
            $progress->deleteProgressForId($content['id']);
        }

        $core->fs->deleteExport(sprintf("%s-%d.h5p", $h5p->slug, $h5p->id));

        $scoring = $this->getScoringForContent($newH5pContent);
        $h5p->unlock();

        $newContent = H5PContent::find($newH5pContent["id"]);
        $oldContent = H5PContent::find($oldContent["id"]);

        event(new ResourceSaved(
            new ResourceDataObject($content['id'], $content['title'], ResourceSaved::UPDATE, ResourceDataObject::H5P),
            $newContent->getEdlibDataObject()
        ));

        event(new ContentUpdated($newContent, $oldContent));

        $urlToCore = $this->getRedirectToCoreUrl(
            $content['id'],
            $content['title'],
            $content['library']['machineName'],
            $scoring,
            $request->get('redirectToken')
        ); // Will not return if we have a returnURL

        $responseValues = [
            'url' => !is_null($urlToCore) ? $urlToCore : route("h5p.show", $content['id'])
        ];
        /** @var Collection $filesToProcess */
        $filesToProcess = H5PFile::ofFileUploadFromContent($content['id'])->get()
            ->filter(function ($file) {
                return $file->state === H5PFile::FILE_CLONEFILE;
            });
        if ($filesToProcess->isNotEmpty()) {
            H5PFilesUpload::dispatch($content['id'])->onQueue("ca-multimedia");
            $responseValues['statuspath'] = route('api.get.filestatus', ['requestId' => app('requestId')]);
        }

        return response()->json($responseValues, Response::HTTP_OK);
    }

    private function sendCollaboratorInviteEmails($newContent, $oldContent)
    {
        if ($this->sendEmail === true && $newContent->id !== $oldContent->id) {
            $oldCollaborators = $oldContent->collaborators ? $oldContent->collaborators->pluck('email')->toArray() : [];
            $newContent->collaborators
                ->pluck('email')// All emails in new article
                ->filter(function ($newCollaborator) use ($oldCollaborators) {
                    //Remove emails that exist as collaborators in the old article
                    return !in_array($newCollaborator, $oldCollaborators) && Session::get("email") !== $newCollaborator;
                })->each(function ($collaborator) use ($newContent) {
                    if ($collaborator) {// Send mails to the new additions
                        $mailData = new \stdClass();
                        $mailData->emailTo = $collaborator;
                        $mailData->inviterName = Session::get('name');
                        $mailData->contentTitle = $newContent->title;
                        $mailData->originSystemName = Session::get('originalSystem', 'edLib');
                        $mailData->emailTitle = trans('emails/collaboration-invite.email-title',
                            ['originSystemName' => $mailData->originSystemName]);

                        $loginUrl = 'https://edstep.com/';
                        $emailFrom = 'no-reply@edlib.com';
                        switch (mb_strtolower(Session::get('originalSystem'))) {
                            case 'edstep':
                                $loginUrl = 'https://edstep.com/';
                                $emailFrom = 'no-reply@edstep.com';
                                break;
                            case 'learnplayground':
                                $loginUrl = 'https://learnplayground.com/';
                                $emailFrom = 'no-reply@learnplayground.com';
                                break;
                        }
                        $mailData->loginUrl = $loginUrl;
                        $mailData->emailFrom = $emailFrom;

                        Mail::send('emails.collaboration-invite', ['mailData' => $mailData],
                            function ($m) use ($mailData) {
                                $m->from($mailData->emailFrom, $mailData->originSystemName);
                                $m->to($mailData->emailTo)->subject($mailData->emailTitle);
                            });
                    }
                });

        }

    }

    public static function addAuthorToParameters($paramsString)
    {
        $sessionName = Session::get("name");
        if ($paramsString == null || empty($sessionName)) {
            return $paramsString;
        }

        $params = json_decode($paramsString);

        if (!isset($params->metadata)) {
            $params->metadata = new stdClass();
        }

        if (!isset($params->metadata->authors)) {
            $params->metadata->authors = [];
        }

        if (count($params->metadata->authors) == 0) {
            $author = new stdClass();
            $author->name = $sessionName;
            $author->role = "Author";
            $params->metadata->authors[] = $author;
        }

        return json_encode($params);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $request->merge([
            "parameters" => self::addAuthorToParameters($request->get("parameters"))
        ]);

        event(new ContentCreating($request));

        try {
            $content = $this->persistContent($request, Session::get('authId'));
        } catch (Exception $e) {
            return response($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $scoring = $this->getScoringForContent($content);

        Cache::forget($this->viewDataCacheName . $content->id);

        event(new ResourceSaved(
            new ResourceDataObject($content['id'], $content['title'], ResourceSaved::CREATE, ResourceDataObject::H5P),
            $content->getEdlibDataObject()
        ));

        event(new ContentCreated($content));

        $urlToCore = $this->getRedirectToCoreUrl(
            $content->id,
            $content->title,
            $content->library()->first()->name,
            $scoring,
            $request->get('redirectToken')
        ); // Will not return if we have a returnURL
        $responseValues = [
            'url' => !is_null($urlToCore) ? $urlToCore : route("h5p.show", $content['id']),
        ];

        /** @var Collection $filesToProcess */
        $filesToProcess = H5PFile::ofFileUploadFromContent($content->id)->get();
        if ($filesToProcess->isNotEmpty()) {
            H5PFilesUpload::dispatch($content['id'])->onQueue("ca-multimedia");
            $responseValues['statuspath'] = route('api.get.filestatus', ['requestId' => app('requestId')]);
        }
        return response()->json($responseValues, Response::HTTP_CREATED);
    }

    /**
     * @param $request
     * @param $authId
     * @return H5PContent
     * @throws Exception
     */
    public function persistContent(Request $request, $authId): H5PContent
    {
        $this->initH5P();

        if ($this->h5p->validateStoreInput($request, app(H5PContent::class)) !== true) {
            throw new Exception($this->h5p->getErrorMessage());
        }

        $this->h5p->setUserId($authId);
        $content = $this->h5p->storeContent($request);

        /** @var H5PContent $newH5pContent */
        $newH5pContent = H5PContent::find($content['id']);

        $this->store_content_shares($content['id'],
            $request->filled("col-emails") ? $request->request->get("col-emails") : "");

        $this->storeContentLicense($request, $content['id']);
        $this->store_content_is_private($newH5pContent, $request);
        $this->handleLicense($request, (object)$content);

        $theOldContent = $this->getEmptyOldContent();

        event(new H5PWasSaved($newH5pContent, $request, VersionData::CREATE));

        $this->sendCollaboratorInviteEmails($newH5pContent, $theOldContent);

        return $newH5pContent;
    }

    /**
     * Store whenever or not content is private.
     *
     * @param int $id
     * @param string $share
     */
    private function store_content_is_private(H5PContent $content, $request)
    {
        $isPrivate = mb_strtoupper($request->get("share", "private")) === 'PRIVATE';

        $content->is_private = $isPrivate;
        $content->save();
    }

    /**
     * Store who has access to handled content.
     *
     * @param int $id
     */
    private function store_content_shares($id, $emailsList)
    {
        $emails = !empty($emailsList) ? explode(",", $emailsList) : [];
        $validEmails = [];

        foreach ($emails as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
                array_push($validEmails, $email);
            }
        }

        H5PCollaborator::where("h5p_id", $id)->delete();

        foreach ($validEmails as $validEmail) {
            $collaborator = new H5PCollaborator();
            $collaborator->h5p_id = $id;
            $collaborator->email = $validEmail;
            if ($collaborator->save() !== true) {
                throw new Exception("Could not store collaborator");
            }
        }
    }

    /**
     * Set license for h5p resource.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function storeContentLicense(Request $request, $id): void
    {
        $db = DB::connection()->getPdo();
        $sql = 'UPDATE h5p_contents SET license=:license WHERE id=:id';
        $params = [
            ':id' => $id,
            ':license' => $request->get('license')
        ];
        $statement = $db->prepare($sql);
        $statement->execute($params);
    }

    protected function handleLicense(Request $request, $content)
    {
        $license = $request->input('license', false);
        if ($license) {
            $licenseLib = app(License::class);
            $licenseContent = $licenseLib->getOrAddContent($content);
            if ($licenseContent) {
                $licenseLib->setLicense($license, $content->id);
            }
        }

        return;
    }

    /**
     * Get license for h5p resource.
     *
     * @param int $id
     * @return Response
     */
    public function getContentLicense($id)
    {
        $db = DB::connection()->getPdo();
        $sql = 'SELECT license FROM h5p_contents WHERE id=:id';
        $params = [':id' => $id];
        $statement = $db->prepare($sql);
        $statement->execute($params);
        $result = $statement->fetchColumn();
        if (isset($result)) {
            return $result;
        }
        return config('license.default-license');
    }

    /**
     * @return \stdClass
     */
    protected function getEmptyOldContent()
    {
        $theOldContent = new \stdClass();
        $theOldContent->id = null;
        $theOldContent->collaborators = collect([]);
        return $theOldContent;
    }

    /**
     * Get content privacy status for h5p resource.
     *
     * @param int $id
     * @return Response
     */
    private function get_content_privacy($id)
    {
        $db = DB::connection()->getPdo();
        $sql = 'SELECT is_private FROM h5p_contents WHERE id=:id';
        $params = [':id' => $id];
        $statement = $db->prepare($sql);
        $statement->execute($params);
        $result = $statement->fetch();
        return $result['is_private'];
    }

    /**
     * Get content shares for h5p resource
     *
     * @param int $id
     * @return Response
     */
    private function get_content_shares($id)
    {
        $db = DB::connection()->getPdo();
        $sql = 'SELECT email FROM cerpus_contents_shares WHERE h5p_id=:id';
        $params = [':id' => $id];
        $statement = $db->prepare($sql);
        $statement->execute($params);
        $result = $statement->fetchAll($db::FETCH_COLUMN, 0);
        $emails = array();
        foreach ($result as $email_raw) {
            array_push($emails, $email_raw);
        }
        $emails_str = implode(',', $emails);
        if (isset($emails)) {
            return $emails_str;
        } else {
            return '';
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return none
     */
    public function destroy($id)
    {
        //
    }

    /**
     * @param Request $request
     * @param H5PCore $core
     * @param H5peditor $editor
     * @return array|\Illuminate\Http\JsonResponse|void
     * @throws Exception
     */
    public function ajaxLoading(Request $request, H5PCore $core, H5peditor $editor)
    {
        $ajaxRequest = new AjaxRequest($this->h5pPlugin, $core, $editor);
        $returnValue = $ajaxRequest->handleAjaxRequest($request);
        switch ($ajaxRequest->getReturnType()) {
            case "json":
                return response()->json($returnValue);
                break;
            default:
                return $returnValue;
        }
    }

    /**
     * Check if any users has progress for this resource.
     *
     * @param int $id
     * @return bool
     */
    public function hasUserProgress(H5PContent $h5p)
    {
        if (config('feature.versioning') !== true){
            return false;
        }
        return $h5p->contentUserData()->get()->isNotEmpty();
    }

    /**
     * @param H5PContent $content
     */
    protected function getScoringForContent(H5PContent $content): int
    {
        return $content->max_score > 0 ? 1 : 0;
    }

    public function contentUpgradeLibrary(Request $request, H5PCore $core)
    {
        try {
            return response()->json((new H5PLibraryAdmin)->upgradeLibrary($core, $request->get('library')));
        } catch (Exception $exception) {
            return response($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @param H5PContent $h5pContent
     * @param $authId
     * @param $versionPurpose
     * @return array
     * @throws Exception
     */
    private function performUpdate(Request $request, H5PContent $h5pContent, $authId, $versionPurpose): array
    {
        $core = resolve(H5PCore::class);
        $oldContent = $core->loadContent($h5pContent->id);
        $this->h5p->setUserId($authId);

        if ($versionPurpose === VersionData::COPY || $versionPurpose === VersionData::TRANSLATION) {
            $oldContent['user_id'] = null;
            if (!$request->input("license", false)) {
                $request->merge(["license" => $h5pContent->getContentLicense()]);
            }
        }
        // Do some final checking, add missing request params
        if (!$request->filled('license')) {
            $request->request->add(['license' => $h5pContent->getContentLicense()]);
        }
        if (!$request->filled('col-emails')) {
            $request->request->add([
                'col-emails' => implode(',', $h5pContent->collaborators->pluck('email')->toArray())
            ]);
        }

        $makeNewVersion = $h5pContent->requestShouldBecomeNewVersion($request);
        $oldContent['useVersioning'] = $makeNewVersion;
        $content = $this->h5p->storeContent($request, $oldContent);

        if ($makeNewVersion !== true && $h5pContent->useVersioning() === false) {
            /** @var \H5PExport $export */
            $export = resolve(\H5PExport::class);
            $export->deleteExport($oldContent);
        }

        /** @var H5PContent $newH5pContent */
        $newH5pContent = H5PContent::find($content['id']);

        event(new H5PWasSaved($newH5pContent, $request, $versionPurpose, $h5pContent));

        // If user is the original owner of the resource
        if ($newH5pContent->isOwner($authId)) {
            if (in_array($versionPurpose, [VersionData::UPDATE, VersionData::UPGRADE])) {
                $this->store_content_shares($content['id'], $request->filled("col-emails") ? $request->request->get("col-emails") : "");
            }

            $this->store_content_is_private($newH5pContent, $request);
            $this->storeContentLicense($request, $content['id']);
            $this->handleLicense($request, (object)$content);
        } elseif ($versionPurpose === VersionData::UPDATE) { // Transfer the old collaborators to the new version, even if the user saving is not the owner
            $emails = $h5pContent->collaborators->pluck('email')->toArray();
            $currentUserEmail = Session::get('email', "noemail");
            if ($currentUserEmail !== "noemail" && !in_array($currentUserEmail, $emails)) {
                $emails[] = $currentUserEmail;
            }
            $collaborators = implode(',', $emails);

            // TODO Update license and privacy based on the old h5p

            $this->store_content_is_private($newH5pContent, $request);
            $this->storeContentLicense($request, $content['id']);
            $this->store_content_shares($content['id'], $collaborators);
            $this->handleLicense($request, (object)$content);
        }
        return array($oldContent, $content, $newH5pContent);
    }

    public function downloadContent(H5PContent $h5p)
    {
        $core = resolve(H5PCore::class);
        $displayOptions = $core->getDisplayOptionsForView($h5p->disable, $h5p->id);
        if (!array_key_exists('export', $displayOptions) || $displayOptions['export'] !== true) {
            return trans('h5p-editor.download-not-available');
        }

        $fileName = sprintf("%s-%d.h5p", $h5p->slug, $h5p->id);
        $export = resolve(H5PExport::class, ['content' => $h5p]);
        if ($core->fs->hasExport($fileName) || $export->generateExport(config('feature.export_h5p_with_local_files'))) {
            return $core->fs->downloadContent($fileName, $h5p->title);
        }

        return response(trans('h5p-editor.could-not-find-content'));
    }

    public function browseImages(Request $request)
    {
        $imageAdapter = app(H5PImageAdapterInterface::class);
        return $imageAdapter->findImages([
            'page' => $request->get('page'),
            'searchString' => $request->get('searchstring'),
        ]);
    }

    public function getImage($imageId)
    {
        $imageAdapter = app(H5PImageAdapterInterface::class);
        return $imageAdapter->getImage($imageId);
    }

    public function browseVideos(Request $request)
    {
        $videodapter = app(H5PVideoInterface::class);
        return $videodapter->findVideos([
            'source' => $request->get('source'),
            'query' => $request->get('query'),
        ]);
    }

    public function getVideo($videoId)
    {
        $videoAdapter = app(H5PVideoInterface::class);
        return $videoAdapter->getVideo($videoId);
    }

    public function getCopyright(H5PContent $h5p)
    {
        $copyrights = (resolve(H5PCopyright::class))->getCopyrights($h5p);
        if (empty($copyrights)) {
            return response('No copyright found', Response::HTTP_NOT_FOUND);
        }
        return response()->json($copyrights);
    }

    public function browseAudios(Request $request)
    {
        $audioAdapter = app(H5PAudioInterface::class);
        return $audioAdapter->findAudio([
            'query' => $request->get('query'),
        ]);
    }

    public function getAudio($audioId)
    {
        $audioAdapter = app(H5PAudioInterface::class);
        return $audioAdapter->getAudio($audioId);
    }
}
