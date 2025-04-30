<?php

namespace App\Http\Controllers;

use App\ContentVersion;
use App\Events\H5PWasSaved;
use App\H5PCollaborator;
use App\H5PContent;
use App\H5PFile;
use App\H5PLibrary;
use App\Http\Libraries\License;
use App\Http\Requests\H5PStorageRequest;
use App\Jobs\H5PFilesUpload;
use App\Libraries\DataObjects\H5PEditorConfigObject;
use App\Libraries\DataObjects\H5PStateDataObject;
use App\Libraries\DataObjects\ResourceInfoDataObject;
use App\Libraries\H5P\AdminConfig;
use App\Libraries\H5P\AjaxRequest;
use App\Libraries\H5P\Dataobjects\H5PAlterParametersSettingsDataObject;
use App\Libraries\H5P\h5p;
use App\Libraries\H5P\H5PCopyright;
use App\Libraries\H5P\H5PCreateConfig;
use App\Libraries\H5P\H5PEditConfig;
use App\Libraries\H5P\H5PExport;
use App\Libraries\H5P\H5PInfo;
use App\Libraries\H5P\H5PLibraryAdmin;
use App\Libraries\H5P\H5PProgress;
use App\Libraries\H5P\H5PViewConfig;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use App\Libraries\H5P\Interfaces\H5PVideoInterface;
use App\Libraries\H5P\Interfaces\TranslationServiceInterface;
use App\Libraries\H5P\LtiToH5PLanguage;
use App\Libraries\H5P\Storage\H5PCerpusStorage;
use App\Lti\Lti;
use App\Lti\LtiRequest;
use App\SessionKeys;
use App\Traits\ReturnToCore;
use Exception;
use H5PCore;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Iso639p3;
use MatthiasMullie\Minify\CSS;
use stdClass;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use function app;
use function config;
use function request;

class H5PController extends Controller
{
    use ReturnToCore;

    private string $viewDataCacheName = 'viewData-';

    public function __construct(
        private Lti $lti,
        private h5p $h5p,
        private H5PLibraryAdmin $h5pLibraryAdmin,
    ) {
        $this->middleware('adaptermode', ['only' => ['show', 'edit', 'update', 'store', 'create']]);
        $this->middleware('core.return', ['only' => ['create', 'edit']]);
        $this->middleware('core.locale', ['only' => ['create', 'edit', 'store']]);
    }

    public function show($id): View
    {
        $ltiRequest = $this->lti->getRequest(request());
        $styles = [];
        $style = $ltiRequest?->getLaunchPresentationCssUrl();
        if ($style) {
            $styles[] = $style;
            Session::flash(SessionKeys::EXT_CSS_URL, $style);
        }
        $h5pContent = H5PContent::findOrFail($id);

        $viewConfig = (app(H5PViewConfig::class))
            ->setUserId(Session::get('authId', false))
            ->setUserUsername(Session::get('userName', false))
            ->setUserEmail(Session::get('email', false))
            ->setUserName(Session::get('name', false))
            ->setPreview($ltiRequest?->isPreview())
            ->setContext($ltiRequest?->generateContextKey() ?? '')
            ->setEmbedCode($ltiRequest?->getEmbedCode() ?? '')
            ->setEmbedResizeCode($ltiRequest?->getEmbedResizeCode() ?? '')
            ->loadContent($id)
            ->setAlterParameterSettings(new H5PAlterParametersSettingsDataObject(useImageWidth: $h5pContent->library->includeImageWidth()));

        $h5pView = $this->h5p->createView($viewConfig);
        $content = $viewConfig->getContent();
        $settings = $h5pView->getSettings();
        $styles = array_merge($h5pView->getStyles(), $styles);

        return view('h5p.show', [
            'id' => $id,
            'title' => $content['title'],
            'language' => $content['language'],
            'config' => $settings,
            'jsScripts' => $h5pView->getScripts(),
            'styles' => $styles,
            'inlineStyle' => (new CSS())->add($viewConfig->getCss(true))->minify(),
            'preview' => $ltiRequest?->isPreview(),
            'resourceType' => sprintf($h5pContent::RESOURCE_TYPE_CSS, $h5pContent->getContentType()),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request, H5PCore $core, $contenttype = null): View
    {
        Log::info("Create H5P, user: " . Session::get('authId', 'not-logged-in-user'));

        $language = Session::get('locale') ?? config("h5p.default-resource-language");
        try {
            $language = Iso639p3::code($language);
        } catch (Exception) {
        }

        $editorConfig = (app(H5PCreateConfig::class))
            ->setUserId(Session::get('authId', false))
            ->setUserUsername(Session::get('userName', false))
            ->setUserEmail(Session::get('email', false))
            ->setUserName(Session::get('name', false))
            ->setDisplayHub(empty($contenttype))
            ->setRedirectToken($request->input('redirectToken'))
            ->setLanguage(Iso639p3::code2letters($language));

        $h5pView = $this->h5p->createView($editorConfig);

        $displayOptions = $core->getDisplayOptionsForEdit();
        $core->getStorableDisplayOptions($displayOptions, null);

        /** @var H5PAdapterInterface $adapter */
        $adapter = app(H5PAdapterInterface::class);

        if (!is_null($contenttype) && !H5PCore::libraryFromString($contenttype)) {
            /** @var H5PLibrary $library */
            $library = H5PLibrary::fromMachineName($contenttype)
                ->latestVersion()
                ->first();
            if (!empty($library)) {
                $contenttype = $library->getLibraryString(false);
            } else {
                $contenttype = false;
            }
        }

        $ltiRequest = $this->lti->getRequest(request());

        $editorSetup = H5PEditorConfigObject::create([
            'canList' => true,
            'showDisplayOptions' => config('h5p.showDisplayOptions'),
            'useLicense' => config('feature.licensing') === true || config('feature.licensing') === '1',
            'adapterName' => config('feature.allow-mode-switch') === true ? $adapter->getAdapterName() : null,
            'adapterList' => $adapter::getAllAdapters(),
            'h5pLanguage' => Iso639p3::code2letters($language),
            'creatorName' => Session::get("name"),
            'editorLanguage' => Session::get('locale', config('app.fallback_locale')),
            'enableUnsavedWarning' => $ltiRequest?->getEnableUnsavedWarning() ?? config('feature.enable-unsaved-warning'),
        ]);

        $state = H5PStateDataObject::create($displayOptions + [
            'library' => $contenttype,
            'license' => License::getDefaultLicense(),
            'isPublished' => $ltiRequest?->getPublished() ?? false,
            'isShared' => $ltiRequest?->getShared() ?? false,
            'language_iso_639_3' => $language,
            'redirectToken' => $request->get('redirectToken'),
            'route' => route('h5p.store'),
            '_method' => "POST",
        ])->toJson();

        return view(
            'h5p.create',
            [
                'config' => $h5pView->getSettings(),
                'jsScript' => $h5pView->getScripts(false),
                'styles' => $h5pView->getStyles(false),
                'emails' => '',
                'libName' => $contenttype,
                'editorSetup' => $editorSetup->toJson(),
                'state' => $state,
                'configJs' => $adapter->getConfigJs(),
            ],
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, int $id): View
    {
        Log::info("Edit H5P: $id, user: " . Session::get('authId', 'not-logged-in-user'));

        $h5pCore = resolve(H5PCore::class);

        /** @var H5PContent $h5pContent */
        $h5pContent = H5PContent::with(['library', 'ndlaMapper', 'metadata'])->find($id);

        /** @var H5PAdapterInterface $adapter */
        $adapter = app(H5PAdapterInterface::class);
        $contentLanguage = $h5pContent->language_iso_639_3;
        $h5pLanguage = $h5pContent->metadata->default_language ?? null;
        if (!is_null($h5pLanguage)) {
            $h5pLanguage = Iso639p3::code2letters($h5pLanguage);
        }
        $redirectToken = $request->input('redirectToken');

        $editorConfig = (app(H5PEditConfig::class))
            ->setUserId(Session::get('authId', false))
            ->setUserUsername(Session::get('userName', false))
            ->setUserEmail(Session::get('email', false))
            ->setUserName(Session::get('name', false))
            ->setRedirectToken($redirectToken)
            ->setLanguage(LtiToH5PLanguage::convert(Session::get('locale')))
            ->loadContent($id);

        $h5pView = $this->h5p->createView($editorConfig);
        $content = $editorConfig->getContent();

        if (empty($content)) {
            Log::error(__METHOD__ . ": H5P $id is empty. UserId: " . Session::get('authId', 'not-logged-in-user'), [
                'user' => Session::get('authId', 'not-logged-in-user'),
                'url' => request()->url(),
                'request' => request()->all(),
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

        $params = $adapter->alterParameters($params, new H5PAlterParametersSettingsDataObject(useImageWidth: false));

        $library = $h5pContent->library;
        $settings = [];
        $scripts = $h5pView->getScripts(false);

        /** @var LtiRequest|null $ltiRequest */
        $ltiRequest = Session::get('lti_requests.' . $redirectToken);

        $editorSetup = H5PEditorConfigObject::create([
            'canList' => $h5pContent->canList($request),
            'showDisplayOptions' => config('h5p.showDisplayOptions'),
            'useLicense' => config('feature.licensing') === true || config('feature.licensing') === '1',
            'h5pLanguage' => $h5pLanguage,
            'editorLanguage' => Session::get('locale', config('app.fallback_locale')),
            'enableUnsavedWarning' => $ltiRequest?->getEnableUnsavedWarning() ?? config('feature.enable-unsaved-warning'),
            'supportedTranslations' => app()->make(TranslationServiceInterface::class)->getSupportedLanguages(),
        ]);

        if ($h5pContent->canList($request)) {
            $config = (resolve(AdminConfig::class))
                ->setId($id);
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
            'id' => (string) $content['id'],
            'createdAt' => $h5pContent->created_at->toIso8601String(),
            'type' => $library->getTitleAndVersionString(),
            'maxScore' => $library->supportsMaxScore() ? $h5pContent->max_score : null,
            'ownerName' => null,
        ]));

        $state = H5PStateDataObject::create($displayOptions + [
            'id' => $h5pContent->id,
            'library' => $library->getLibraryString(false),
            'libraryid' => $h5pContent->library_id,
            'parameters' => $params,
            'language_iso_639_3' => $contentLanguage,
            'title' => $h5pContent->title,
            'license' => $h5pContent->license ?: License::getDefaultLicense(),
            'isPublished' => $ltiRequest?->getPublished() ?? false,
            'isDraft' => $h5pContent->isDraft(),
            'isShared' => $ltiRequest?->getShared() ?? false,
            'redirectToken' => $request->get('redirectToken'),
            'route' => route('h5p.update', ['h5p' => $id]),
            'max_score' => $h5pContent->max_score,
            '_method' => "PUT",
        ])->toJson();

        return view(
            'h5p.edit',
            [
                'id' => $id,
                'h5p' => $h5pContent,
                'config' => $h5pView->getSettings(),
                'adminConfig' => "<script>H5PAdminIntegration = " . json_encode($settings) . "</script>",
                'jsScript' => $scripts,
                'styles' => $h5pView->getStyles(false),
                'libName' => $h5pCore->libraryToString($content['library']),
                'emails' => $this->get_content_shares($id),
                'hasUserProgress' => $this->hasUserProgress($h5pContent),
                'editorSetup' => $editorSetup->toJson(),
                'state' => $state,
                'configJs' => $adapter->getConfigJs(),
            ],
        );
    }

    private function getVersionPurpose(Request $request, H5PContent $h5p, $authId): string
    {
        if ($request->get("isNewLanguageVariant", false)) {
            return ContentVersion::PURPOSE_TRANSLATION;
        }

        if (!$h5p->canUpdateOriginalResource($authId)) {
            if (!$h5p->isCopyable()) {
                throw new AccessDeniedHttpException('Cannot copy this resource');
            }

            return ContentVersion::PURPOSE_COPY;
        }

        return ContentVersion::PURPOSE_UPDATE;
    }

    /**
     * Update the specified resource in storage.
     *
     * @throws Exception
     */
    public function update(H5PStorageRequest $request, H5PContent $h5p, H5PCore $core): Response|JsonResponse
    {
        $authId = Session::get('authId', false);
        $versionPurpose = $this->getVersionPurpose($request, $h5p, $authId);
        [$oldContent, $content, $newH5pContent] = $this->performUpdate($request, $h5p, $authId, $versionPurpose);

        Cache::forget($this->viewDataCacheName . $content['id']);
        if ($oldContent['library']['name'] !== $content['library']['machineName']) {
            // Remove old progresses
            $progress = new H5PProgress(DB::connection()->getPdo(), Session::get('authId'));
            $progress->deleteProgressForId($content['id']);
        }

        $core->fs->deleteExport(sprintf("%s-%d.h5p", $h5p->slug, $h5p->id));

        $responseValues = [
            'url' => $this->getRedirectToCoreUrl(
                $newH5pContent->toLtiContent(
                    published: $request->validated('isPublished'),
                    shared: $request->validated('isShared'),
                ),
                $request->input('redirectToken'),
            ),
        ];
        /** @var Collection $filesToProcess */
        $filesToProcess = H5PFile::ofFileUploadFromContent($content['id'])->get()
            ->filter(function ($file) {
                return $file->state === H5PFile::FILE_CLONEFILE;
            });
        if ($filesToProcess->isNotEmpty()) {
            H5PFilesUpload::dispatch($content['id']);
            $responseValues['statuspath'] = route('api.get.filestatus', ['requestId' => $request->header('X-Request-Id')]);
        }

        return response()->json($responseValues, Response::HTTP_OK);
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
     */
    public function store(H5PStorageRequest $request): Response|JsonResponse
    {
        $request->merge([
            "parameters" => self::addAuthorToParameters($request->get("parameters")),
        ]);

        $content = $this->persistContent($request, Session::get('authId'));

        Cache::forget($this->viewDataCacheName . $content->id);

        $responseValues = [
            'url' => $this->getRedirectToCoreUrl(
                $content->toLtiContent(
                    published: $request->validated('isPublished'),
                    shared: $request->validated('isShared'),
                ),
                $request->input('redirectToken'),
            ),
        ];

        /** @var Collection $filesToProcess */
        $filesToProcess = H5PFile::ofFileUploadFromContent($content->id)->get();
        if ($filesToProcess->isNotEmpty()) {
            H5PFilesUpload::dispatch($content['id']);
            $responseValues['statuspath'] = route('api.get.filestatus', ['requestId' => $request->header('X-Request-Id')]);
        }
        return response()->json($responseValues, Response::HTTP_CREATED);
    }

    public function persistContent(Request $request, $authId): H5PContent
    {
        $content = $this->h5p->storeContent($request, null, $authId);
        $this->storeContentLicense($request, $content['id']);

        /** @var H5PContent $newH5pContent */
        $newH5pContent = H5PContent::find($content['id']);

        $this->store_content_shares(
            $content['id'],
            $request->filled("col-emails") ? $request->request->get("col-emails") : "",
        );

        event(new H5PWasSaved($newH5pContent, $request, ContentVersion::PURPOSE_CREATE));

        return $newH5pContent;
    }

    /**
     * Store who has access to handled content.
     */
    private function store_content_shares(int $id, $emailsList)
    {
        $emails = !empty($emailsList) ? explode(",", $emailsList) : [];
        $validEmails = [];

        foreach ($emails as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
                $validEmails[] = $email;
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
     */
    public function storeContentLicense(Request $request, int $id): void
    {
        $db = DB::connection()->getPdo();
        $sql = 'UPDATE h5p_contents SET license=:license WHERE id=:id';
        $params = [
            ':id' => $id,
            ':license' => $request->get('license'),
        ];
        $statement = $db->prepare($sql);
        $statement->execute($params);
    }

    /**
     * Get license for h5p resource.
     */
    public function getContentLicense(int $id): Response
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

        return License::getDefaultLicense();
    }

    /**
     * Get content shares for h5p resource
     */
    private function get_content_shares(int $id): string
    {
        $db = DB::connection()->getPdo();
        $sql = 'SELECT email FROM cerpus_contents_shares WHERE h5p_id=:id';
        $params = [':id' => $id];
        $statement = $db->prepare($sql);
        $statement->execute($params);
        $result = $statement->fetchAll($db::FETCH_COLUMN, 0);
        $emails = [];
        foreach ($result as $email_raw) {
            $emails[] = $email_raw;
        }
        $emails_str = implode(',', $emails);
        if (isset($emails)) {
            return $emails_str;
        } else {
            return '';
        }
    }

    public function ajaxLoading(Request $request, AjaxRequest $ajaxRequest): object|array|string|null
    {
        $returnValue = $ajaxRequest->handleAjaxRequest($request);
        return match ($ajaxRequest->getReturnType()) {
            "json" => response()->json($returnValue),
            default => $returnValue,
        };
    }

    /**
     * Check if any users has progress for this resource.
     */
    public function hasUserProgress(H5PContent $h5p): bool
    {
        return $h5p->contentUserData()->get()->isNotEmpty();
    }

    public function contentUpgradeLibrary(Request $request, H5PCore $core)
    {
        return response()->json($this->h5pLibraryAdmin->upgradeLibrary($core, $request->get('library')));
    }

    /**
     * @return array{array, array, H5PContent}
     * @throws Exception
     */
    private function performUpdate(Request $request, H5PContent $h5pContent, $authId, $versionPurpose): array
    {
        /** @var H5PCore $core */
        $core = resolve(H5PCore::class);
        $oldContent = $core->loadContent($h5pContent->id);

        if ($versionPurpose === ContentVersion::PURPOSE_COPY || $versionPurpose === ContentVersion::PURPOSE_TRANSLATION) {
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
                'col-emails' => implode(',', $h5pContent->collaborators->pluck('email')->toArray()),
            ]);
        }

        $makeNewVersion = $h5pContent->requestShouldBecomeNewVersion($request);
        $oldContent['useVersioning'] = $makeNewVersion;
        $content = $this->h5p->storeContent($request, $oldContent, $authId);

        $newH5pContent = H5PContent::find($content['id']);

        event(new H5PWasSaved($newH5pContent, $request, $versionPurpose, $h5pContent));

        // If user is the original owner of the resource
        if ($newH5pContent->isOwner($authId)) {
            if (in_array($versionPurpose, [ContentVersion::PURPOSE_UPDATE, ContentVersion::PURPOSE_UPGRADE])) {
                $this->store_content_shares($content['id'], $request->filled("col-emails") ? $request->request->get("col-emails") : "");
            }

            $this->storeContentLicense($request, $content['id']);
        } elseif ($versionPurpose === ContentVersion::PURPOSE_UPDATE) { // Transfer the old collaborators to the new version, even if the user saving is not the owner
            $emails = $h5pContent->collaborators->pluck('email')->toArray();
            $currentUserEmail = Session::get('email', "noemail");
            if ($currentUserEmail !== "noemail" && !in_array($currentUserEmail, $emails)) {
                $emails[] = $currentUserEmail;
            }
            $collaborators = implode(',', $emails);

            // TODO Update license based on the old h5p

            $this->storeContentLicense($request, $content['id']);
            $this->store_content_shares($content['id'], $collaborators);
        }
        return [$oldContent, $content, $newH5pContent->fresh()];
    }

    public function downloadContent(H5PContent $h5p, H5PExport $export, H5PCore $core, H5PCerpusStorage $storage)
    {
        $options = $core->getDisplayOptionsForView($h5p->disable, $h5p->id);
        $canExport = $options[H5PCore::DISPLAY_OPTION_DOWNLOAD] ?? false;

        if (!$canExport) {
            return response(trans('h5p-editor.download-not-available'), 403);
        }

        $fileName = sprintf("%s-%d.h5p", $h5p->slug, $h5p->id);
        if ($storage->hasExport($fileName) || $export->generateExport($h5p)) {
            return $storage->downloadContent($fileName, $h5p->title);
        }

        return response(trans('h5p-editor.could-not-find-content'), 404);
    }

    public function browseVideos(Request $request)
    {
        /** @var H5PVideoInterface $videodapter */
        $videodapter = app(H5PVideoInterface::class);
        return $videodapter->findVideos([
            'source' => $request->get('source'),
            'query' => $request->get('query'),
        ]);
    }

    public function getVideo($videoId)
    {
        /** @var H5PVideoInterface $videoAdapter */
        $videoAdapter = app(H5PVideoInterface::class);
        return $videoAdapter->getVideo($videoId);
    }

    public function getCopyright(H5PContent $h5p, H5PCopyright $copyright, CacheRepository $cache)
    {
        $copyrights = $cache->rememberForever($h5p->getCopyrightCacheKey(), fn() => $copyright->getCopyrights($h5p));

        if (empty($copyrights)) {
            return response('No copyright found', Response::HTTP_NOT_FOUND);
        }

        return response()->json($copyrights);
    }

    public function getInfo(H5PContent $h5p, H5PInfo $h5pInfo, CacheRepository $cache)
    {
        $information = $cache->rememberForever($h5p->getInfoCacheKey(), fn() => $h5pInfo->getInformation($h5p));

        return response()->json($information);
    }
}
