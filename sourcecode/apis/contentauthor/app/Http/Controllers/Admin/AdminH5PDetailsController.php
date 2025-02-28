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
use ErrorException;
use H5PCore;
use H5PValidator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
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
            'languages' => H5PLibraryLanguage::select('language_code')->where('library_id', $library->id)->pluck('language_code'),
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
        $libLang = H5PLibraryLanguage::where('library_id', $library->id)
            ->where('language_code', $locale)
            ->first();

        return view('admin.library-upgrade.translation', [
            'library' => $library,
            'languageCode' => $locale,
            'haveTranslation' => $libLang !== null,
            'translationDb' => $libLang?->translation,
            'translationFile' => Storage::disk()->get(
                sprintf('libraries/%s/language/%s.json', $library->getFolderName(), $locale),
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

        $libLang = H5PLibraryLanguage::where('library_id', $library->id)
            ->where('language_code', $locale)
            ->first();

        return view('admin.library-upgrade.translation', [
            'library' => $library,
            'languageCode' => $locale,
            'haveTranslation' => $libLang !== null,
            'translationDb' => $libLang?->translation,
            'translationFile' => Storage::disk()->get(
                sprintf('libraries/%s/language/%s.json', $library->getFolderName(), $locale),
            ),
            'messages' => $messages,
        ]);
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
