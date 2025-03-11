<?php

namespace App\Http\Controllers\Admin;

use App\ContentLock;
use App\ContentVersion;
use App\H5PContent;
use App\H5PLibrary;
use App\H5PLibraryLanguage;
use App\H5PLibraryLibrary;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminTranslationUpdateRequest;
use App\Libraries\ContentAuthorStorage;
use App\Libraries\H5P\AdminConfig;
use App\Libraries\H5P\H5PLibraryAdmin;
use DB;
use ErrorException;
use H5PCore;
use H5PValidator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

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
            Storage::disk('h5pTmp'),
            $tmpLibraryRelative,
            $tmpLibraryRelative,
        );
        $tmpLibraries = $this->core->h5pF->getH5pPath($tmpLibrariesRelative);
        $tmpLibraryFolder = $this->core->h5pF->getH5pPath($tmpLibraryRelative);

        $libraryData = [];
        /** @var H5PValidator $validator */
        $validator = resolve(H5PValidator::class);

        // The Validator does not check if the library folder exists before accessing files
        try {
            $libraryData = $validator->getLibraryData($h5pDataFolderName, $tmpLibraryFolder, $tmpLibraries);
        } catch (ErrorException $e) {
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
            'languages' => H5PLibraryLanguage::select('language_code')
                ->where('library_id', $library->id)
                ->pluck('language_code')
                ->push('en') // 'en' translation is rarely in a file,
                ->unique() // but just in case it is.
                ->sort()
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
            ->offset($pageSize * ($page - 1))
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
                ->withPath('/admin/libraries/' . $library->id . '/content')
                ->appends(['listAll' => $listAll]),
        ]);
    }

    public function contentHistory(H5PContent $content, ContentVersion $version = null): View
    {
        $versions = collect();
        $history = [];
        if ($version !== null && $content->id === $version->content_id) {
            $history = $this->getVersions($version, $versions);
        } elseif ($content->version_id) {
            $data = $content->getVersion();
            $history = $data ? $this->getVersions($data, $versions) : [];
        }

        return view('admin.library-upgrade.content-details', [
            'content' => $content,
            'requestedVersion' => $version,
            'history' => $history,
            'hasLock' => ContentLock::notExpiredById($content->id)?->updated_at,
        ]);
    }

    public function libraryTranslation(H5PLibrary $library, string $locale): View
    {
        return view('admin.library-upgrade.translation', $this->libraryTranslationData($library, $locale));
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
            } catch (\JsonException $e) {
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

        $data = $this->libraryTranslationData($library, $locale);
        $data['messages'] = $messages;

        return view('admin.library-upgrade.translation', $data);
    }

    private function libraryTranslationData(H5PLibrary $library, string $locale): array
    {
        $libLang = H5PLibraryLanguage::where('library_id', $library->id)
            ->where('language_code', $locale)
            ->first();

        $updatableCount = DB::table('content_versions')
            ->select(DB::raw('count(distinct(h5p_contents.id)) as total'))
            ->leftJoin(DB::raw('content_versions as cv'), 'cv.parent_id', '=', 'content_versions.id')
            ->where(function ($query) {
                $query
                    ->whereNull('cv.content_id')
                    ->orWhereNotIn('cv.version_purpose', [ContentVersion::PURPOSE_UPGRADE, ContentVersion::PURPOSE_UPDATE]);
            })
            ->join('h5p_contents', 'h5p_contents.id', '=', 'content_versions.content_id')
            ->join('h5p_contents_metadata', 'h5p_contents.id', '=', 'h5p_contents_metadata.content_id')
            ->where('h5p_contents.library_id', $library->id)
            ->where('h5p_contents_metadata.default_language', $locale);

        $totalCount = DB::table('h5p_contents')
            ->join('h5p_contents_metadata', 'h5p_contents.id', '=', 'h5p_contents_metadata.content_id')
            ->where('h5p_contents.library_id', $library->id)
            ->where('h5p_contents_metadata.default_language', $locale)
            ->count();

        $filename = sprintf('libraries/%s/language/%s.json', $library->getFolderName(), $locale);
        if (Storage::exists($filename)) {
            $fileTranslation = Storage::disk()->get($filename);
            $fileModified = Carbon::createFromTimestamp(Storage::disk()->lastModified($filename));
        }

        return [
            'library' => $library,
            'languageCode' => $locale,
            'translationDb' => $libLang,
            'translationFile' => $fileTranslation ?? null,
            'fileModified' => $fileModified ?? null,
            'totalCount' => $totalCount,
            'updatableCount' => $updatableCount->first()->total,
        ];
    }

    public function contentTranslationUpdate(H5PLibrary $library, string $locale): View
    {
        $adminConfig = app(AdminConfig::class);
        $adminConfig->getConfig();
        $adminConfig->addContentLanguageScripts();

        $contentCount = DB::table('content_versions')
            ->select(DB::raw('count(distinct(h5p_contents.id)) as total'))
            ->leftJoin(DB::raw('content_versions as cv'), 'cv.parent_id', '=', 'content_versions.id')
            ->where(function ($query) {
                $query
                    ->whereNull('cv.content_id')
                    ->orWhereNotIn('cv.version_purpose', [ContentVersion::PURPOSE_UPGRADE, ContentVersion::PURPOSE_UPDATE]);
            })
            ->join('h5p_contents', 'h5p_contents.id', '=', 'content_versions.content_id')
            ->join('h5p_contents_metadata', 'h5p_contents.id', '=', 'h5p_contents_metadata.content_id')
            ->where('h5p_contents.library_id', $library->id)
            ->where('h5p_contents_metadata.default_language', $locale);

        $jsConfig = [
            'ajaxPath' => $adminConfig->config->ajaxPath,
            'endpoint' => route('admin.library-transation-content-update', [$library, $locale]),
            'libraryId' => $library->id,
            'library' => $library->getLibraryString(false),
            'locale' => $locale,
        ];

        return view('admin.content-language-update', [
            'library' => $library,
            'languageCode' => $locale,
            'contentCount' => $contentCount->first()->total,
            'jsConfig' => $jsConfig,
            'scripts' => $adminConfig->getScriptAssets(),
            'styles' => $adminConfig->getStyleAssets(),
        ]);
    }

    public function updateContentTranslation(Request $request): JsonResponse
    {
        return response()->json(app(H5PLibraryAdmin::class)->updateContentTranslation($request));
    }

    private function getVersions(ContentVersion $versionData, Collection $stack, $getChildren = true): Collection
    {
        $versionArray = $versionData->toArray();
        $versionArray['versionDate'] = $versionData->created_at;
        $versionArray['content'] = $this->getContentInfo($versionData);
        $parent = $versionData->previousVersion;
        if (!empty($parent)) {
            $this->getVersions($parent, $stack, false);
            $versionArray['parent'] = $parent->id;
        }
        $children = $versionData->nextVersions;
        if ($children->isNotEmpty()) {
            $versionArray['children'] = [];
            foreach ($children as $child) {
                if ($getChildren) {
                    $this->getVersions($child, $stack, false);
                }
                $versionArray['children'][] = [
                    'id' => $child->id,
                    'content_id' => $child->content_id,
                    'versionDate' => $child->created_at,
                    'version_purpose' => $child->version_purpose,
                    'content' => $this->getContentInfo($versionData),
                ];
            }
        }
        if (!$stack->has($versionData->id)) {
            $stack->put($versionData->id, $versionArray);
        }

        return $stack;
    }

    private function getContentInfo(ContentVersion $version): array
    {
        $content = $version->getContent();

        if (!empty($content)) {
            $library = $content->library;
            return [
                'title' => $content->title,
                'license' => $content->license,
                'language' => $content->language_iso_639_3,
                'library_id' => $library->id,
                'library' => sprintf('%s %d.%d.%d', $library->name, $library->major_version, $library->minor_version, $library->patch_version),
            ];
        }

        return [];
    }
}
