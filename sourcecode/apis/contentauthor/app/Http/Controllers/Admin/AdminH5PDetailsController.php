<?php

namespace App\Http\Controllers\Admin;

use App\Apis\LtiApiService;
use App\Apis\ResourceApiService;
use App\ContentLock;
use App\ContentVersion;
use App\H5PContent;
use App\H5PContentLibrary;
use App\H5PLibrary;
use App\H5PLibraryLanguage;
use App\H5PLibraryLibrary;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminTranslationUpdateRequest;
use App\Libraries\ContentAuthorStorage;
use App\Libraries\H5P\Dataobjects\H5PAlterParametersSettingsDataObject;
use App\Libraries\H5P\h5p;
use App\Libraries\H5P\H5PExport;
use App\Libraries\H5P\H5PViewConfig;
use App\Libraries\H5P\Storage\H5PCerpusStorage;
use Exception;
use H5PCore;
use H5PValidator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use JsonException;
use MatthiasMullie\Minify\CSS;
use Ramsey\Uuid\Uuid;

class AdminH5PDetailsController extends Controller
{
    public function __construct(
        private readonly H5PCore $core,
        private readonly ContentAuthorStorage $contentAuthorStorage,
    ) {
        $this->middleware('auth');
    }

    public function checkLibrary(H5PLibrary $library): View
    {
        $h5pDataFolderName = $library->getFolderName();
        $tmpLibrariesRelative = 'libraries';
        $tmpLibraryRelative = 'libraries/' . $h5pDataFolderName;
        // Download files from bucket to tmp folder
        $this->contentAuthorStorage->copyFolder(
            Storage::disk(),
            $this->contentAuthorStorage->getH5pTmpDisk(),
            $tmpLibraryRelative,
            $tmpLibraryRelative
        );
        $tmpLibraries = $this->core->h5pF->getH5pPath($tmpLibrariesRelative);
        $tmpLibraryFolder = $this->core->h5pF->getH5pPath($tmpLibraryRelative);

        $libraryData = [];
        /** @var H5PValidator $validator */
        $validator = resolve(H5PValidator::class);

        // The Validator does not check if the library folder exists before accessing files
        try {
            $libraryData = $validator->getLibraryData($h5pDataFolderName, $tmpLibraryFolder, $tmpLibraries);
        } catch (Exception $e) {
            $validator->h5pF->setErrorMessage($e->getMessage());
        }

        $editorDep = $library->libraries()
            ->where('dependency_type', 'editor')
            ->with('requiredLibrary')
            ->get();
        $preloadedDep = $library->libraries()
            ->where('dependency_type', 'preloaded')
            ->with('requiredLibrary')
            ->get();

        if ($libraryData !== false) {
            foreach (['editorDependencies' => $editorDep, 'preloadedDependencies' => $preloadedDep] as $depType => $deps) {
                if (array_key_exists($depType, $libraryData)) {
                    foreach ($libraryData[$depType] as $key => $row) {
                        $depLibKey = $deps->search(function ($value) use ($row) {
                            return $value->requiredLibrary->name === $row['machineName'] &&
                                $value->requiredLibrary->major_version === $row['majorVersion'] &&
                                $value->requiredLibrary->minor_version === $row['minorVersion'];
                        });
                        if ($depLibKey !== false) {
                            $depLib = $deps->pull($depLibKey)->requiredLibrary;
                        } else {
                            $depLib = H5PLibrary::fromMachineName($row['machineName'])
                                ->version($row['majorVersion'], $row['minorVersion'])
                                ->select(['id', 'name', 'major_version', 'minor_version', 'patch_version', 'runnable'])
                                ->first();
                        }

                        if ($depLib !== null) {
                            $libraryData[$depType][$key]['library'] = $depLib;
                            $libraryData[$depType][$key]['dependencySet'] = ($depLibKey !== false);
                        } else {
                            $libraryData[$depType][$key]['library'] = null;
                            $libraryData[$depType][$key]['dependencySet'] = false;
                        }
                    }
                }
            }
        }

        return view('admin.library-upgrade.library-check', [
            'library' => $library,
            'libData' => $libraryData,
            'preloadDeps' => $preloadedDep,
            'editorDeps' => $editorDep,
            'usedBy' => H5PLibraryLibrary::where('required_library_id', $library->id)->get(),
            'info' => $validator->h5pF->getMessages('info'),
            'error' => $validator->h5pF->getMessages('error'),
            'languages' => H5PLibraryLanguage::select('language_code')->where('library_id', $library->id)->pluck('language_code'),
            'subContentCount' => H5PContentLibrary::where('library_id', $library->id)->count(),
        ]);
    }

    public function contentForLibrary(H5PLibrary $library, Request $request): View
    {
        $pageSize = 100;
        $page = (int) $request->get('page', 1);
        $listAll = (bool) $request->get('listAll', false);
        $latestCount = 0;
        $total = $library->contents()->count();

        $contents = $library
            ->contents()
            ->orderBy('updated_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->limit($pageSize)
            ->offset($pageSize * ($page-1))
            ->get()
            ->map(function (H5PContent $row) use (&$latestCount) {
                try {
                    $latest = null;
                    if ($row->version_id) {
                        $latest = ContentVersion::latestLeaf($row->version_id);
                    }
                    $isLatest = (empty($latest) || $row->version_id === $latest->id);
                    $latestCount += $isLatest ? 1 : 0;

                    return [
                        'item' => $row,
                        'isLatest' => $isLatest,
                    ];
                } catch (ModelNotFoundException) {
                    return [
                        'item' => $row,
                        'isLatest' => null,
                    ];
                }
            });

        return view('admin.library-upgrade.library-content', [
            'library' => $library,
            'listAll' => $listAll,
            'latestCount' => $latestCount,
            'paginator' => (new LengthAwarePaginator($contents, $total, $pageSize))
                ->withPath('/admin/libraries/'.$library->id.'/content')
                ->appends(['listAll' => $listAll]),
        ]);
    }

    public function contentHistory(H5PContent $content): View
    {
        $resourceService = app(ResourceApiService::class);
        $versions = collect();
        $history = [];

        try {
            $resource = $resourceService->getResourceFromExternalReference('contentauthor', $content->id);
        } catch (Exception $e) {
            Log::warning($e);
            $resource = null;
        }

        if ($content->version_id) {
            $data = $content->getVersion();
            $history = $data ? $this->getVersions($data, $versions) : [];
        }

        return view('admin.library-upgrade.content-details', [
            'content' => $content,
            'latestVersion' => !isset($history[$content->id]['children']),
            'resource' => $resource,
            'history' => $history,
            'hasLock' => ContentLock::notExpiredById($content->id)?->updated_at,
        ]);
    }

    public function libraryTranslation(H5PLibrary $library, string $locale): View
    {
        $libLang = H5PLibraryLanguage::where('library_id', $library->id)
            ->where('language_code', $locale)
            ->first();

        return view('admin.library-upgrade.translation', [
            'library' => $library,
            'languageCode' => $locale,
            'haveTranslation' => $libLang !== null,
            'translationDb' => $libLang?->translation,
            'translationFile' => Storage::disk()->get(
                sprintf('libraries/%s/language/%s.json', $library->getFolderName(), $locale)
            ),
        ]);
    }

    public function libraryTranslationUpdate(AdminTranslationUpdateRequest $request, H5PLibrary $library, string $locale): View
    {
        $messages = collect();
        $input = $request->validated();

        if (array_key_exists('translationFile', $input) && $request->file('translationFile')->isValid()) {
            $translation = $request->file('translationFile')->getContent();
        } else {
            $translation = $input['translation'];
        }

        if (empty($translation)) {
            $messages->add('Content was empty');
        } else {
            try {
                json_decode($translation, flags: JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                $messages->add($e->getMessage());
            }

            if ($messages->isEmpty()) {
                $count = $library->languages()
                    ->where('language_code', $locale)
                    ->limit(1)
                    ->update(['translation' => $translation]);

                if ($count === 0) {
                    $messages->add('No rows was updated');
                }
            }
        }

        $libLang = H5PLibraryLanguage::where('library_id', $library->id)
            ->where('language_code', $locale)
            ->first();

        return view('admin.library-upgrade.translation', [
            'library' => $library,
            'languageCode' => $locale,
            'haveTranslation' => $libLang !== null,
            'translationDb' => $libLang?->translation,
            'translationFile' => Storage::disk()->get(
                sprintf('libraries/%s/language/%s.json', $library->getFolderName(), $locale)
            ),
            'messages' => $messages,
        ]);
    }

    private function getVersions(ContentVersion $versionData, Collection $stack, $getChildren = true): Collection
    {
        $versionArray = $versionData->toArray();
        $versionArray['versionDate'] = $versionData->created_at;
        $content = $versionData->getContent();
        if (!empty($content)) {
            $versionArray['content'] = [
                'title' => $content->title,
                'created' => $content->created_at->format('Y-m-d H:i:s e'),
                'update' => $content->updated_at->format('Y-m-d H:i:s e'),
                'version_id' => $content->version_id,
                'license' => $content->license,
                'language' => $content->language_iso_639_3,
                'library_id' => '',
                'library' => '',
            ];
            $library = $content->library;
            if ($library !== null) {
                $versionArray['content']['library_id'] = $library->id;
                $versionArray['content']['library'] = $library->getLibraryString(true);
            }
        }
        $parent = $versionData->previousVersion;
        if (!empty($parent)) {
            $this->getVersions($parent, $stack, false);
            $versionArray['parent'] = $parent->content_id;
        }
        $children = $versionData->nextVersions;
        if ($children->isNotEmpty()) {
            $versionArray['children'] = [];
            foreach ($children as $child) {
                if ($getChildren) {
                    $this->getVersions($child, $stack, false);
                }
                $versionArray['children'][] = $child->content_id;
            }
        }
        if (!$stack->has($versionData->content_id)) {
            $stack->put($versionData->content_id, $versionArray);
        }

        return $stack;
    }

    public function h5pContentInfo(Request $request): View|RedirectResponse
    {
        $valueType = $request->get('valueType');
        $value = $request->get('value');
        $content = null;
        $error = null;

        try {
            switch ($valueType) {
                case 'content':
                    if (ctype_digit($value)) {
                        $content = H5PContent::findOrFail($value);
                    } else {
                        $error = "Value is not an integer";
                    }
                    break;
                case 'resource':
                    if (Uuid::isValid($value)) {
                        $resourceService = app(ResourceApiService::class);
                        $data = $resourceService->getResourceById($value);
                        if (str_starts_with($data['contentType'], 'h5p.')) {
                            $content = H5PContent::find($data['externalSystemId']);
                        } else {
                            $error = "Found resource of type '" . $data['contentType'] . "', only H5P types are valid";
                        }
                    } else {
                        $error = "Value is not a valid uuid";
                    }
                    break;
                case 'version':
                    if (Uuid::isValid($value)) {
                        $version = ContentVersion::findorFail($value);
                        $content = $version->getContent();
                    } else {
                        $error = "Value is not a valid uuid";
                    }
                    break;
                case 'usage':
                    if (Uuid::isValid($value)) {
                        $service = app(LtiApiService::class);
                        $data = $service->getResourceFromUsageId($value);
                        $resourceService = app(ResourceApiService::class);
                        $data = $resourceService->getResourceByIdAndVersion($data['resourceId'], $data['resourceVersionId']);
                        if (str_starts_with($data['contentType'], 'h5p.')) {
                            $content = H5PContent::find($data['externalSystemId']);
                        } else {
                            $error = "Found resource of type '" . $data['contentType'] . "', only H5P types are valid";
                        }
                    } else {
                        $error = "Value is not a valid uuid";
                    }
                    break;
                case null:
                    $error = 'Select type of id and paste/enter the value';
                    break;
                default:
                    $error = "Unknown content type";
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        if ($content !== null) {
            return redirect()->route('admin.content-details', [$content]);
        }

        return view('admin/content-details', [
            'error' => $error,
            'valueType' => $valueType,
            'value' => $value,
        ]);
    }

    public function contentPreview(H5PContent $h5pContent): View
    {
        try {
            $resourceService = app(ResourceApiService::class);
            $resource = $resourceService->getResourceFromExternalReference('contentauthor', $h5pContent->id);
        } catch (Exception) {
            $resource = null;
        }

        $viewConfig = (app(H5PViewConfig::class))
            ->setUserId(Session::get('authId', false))
            ->setUserUsername(Session::get('userName', false))
            ->setUserEmail(Session::get('email', false))
            ->setUserName(Session::get('name', false))
            ->setPreview(true)
            ->setEmbedId($resource?->id)
            ->loadContent($h5pContent->id)
            ->setAlterParameterSettings(H5PAlterParametersSettingsDataObject::create(['useImageWidth' => $h5pContent->library->includeImageWidth()]));

        $h5p = app(h5p::class);
        $h5pView = $h5p->createView($viewConfig);
        $content = $viewConfig->getContent();
        $settings = $h5pView->getSettings();
        $styles = array_merge($h5pView->getStyles(), [
            mix('css/admin-preview.css')
        ]);

        return view('admin.h5p-preview', [
            'id' => $h5pContent->id,
            'title' => $content['title'],
            'language' => $content['language'],
            'embed' => '<div class="h5p-content" data-content-id="' . $content['id'] . '"></div>',
            'config' => $settings,
            'jsScripts' => $h5pView->getScripts(),
            'styles' => $styles,
            'inlineStyle' => (new CSS())->add($viewConfig->getCss(true))->minify(),
            'inDraftState' => !$h5pContent->isActuallyPublished(),
            'preview' => true,
            'resourceType' => sprintf($h5pContent::RESOURCE_TYPE_CSS, $h5pContent->getContentType()),
        ]);
    }

    public function contentExport(H5PContent $h5pContent): RedirectResponse|Response
    {
        $export = app(H5PExport::class);
        $storage = app(H5PCerpusStorage::class);
        $fileName = sprintf("%s-%d.h5p", $h5pContent->slug, $h5pContent->id);

        if ($storage->hasExport($fileName) || $export->generateExport($h5pContent)) {
            return $storage->downloadContent($fileName, $h5pContent->title);
        }

        return response(trans('h5p-editor.could-not-find-content'), 404);
    }
}
