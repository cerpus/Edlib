<?php

namespace App\Http\Controllers\Admin;

use App\Content;
use App\H5PContent;
use App\H5PLibrary;
use App\H5PLibraryLibrary;
use App\Http\Controllers\Controller;
use App\Libraries\ContentAuthorStorage;
use Cerpus\VersionClient\VersionData;
use Exception;
use H5PCore;
use H5PValidator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
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
        $h5pDataFolderName = $library->getLibraryString(true);
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
        ]);
    }

    public function contentForLibrary(H5PLibrary $library, Request $request): View
    {
        /** @var \Cerpus\VersionClient\VersionClient $versionClient */
        $versionClient = app('Cerpus\VersionClient\VersionClient');
        $listAll = (bool) $request->get('listAll', true);
        $contents = [];
        $failed = [];

        $library->contents()
            ->orderBy('updated_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->get()
            ->filter(function ($value) use ($versionClient, $listAll, &$contents, &$failed) {
                try {
                    $latest = null;
                    if ($listAll && $value->version_id) {
                        $latest = $versionClient->latest($value->version_id);
                    }
                    if (!$listAll || (!empty($latest) && $value->version_id === $latest->getId())) {
                        $contents[] = $value;
                    }
                } catch (Exception $e) {
                    $failed[$e->getMessage()][] = $value;
                }
            });

        return view('admin.library-upgrade.library-content', [
            'library' => $library,
            'contents' => $contents,
            'failed' => $failed,
            'listAll' => $listAll,
        ]);
    }

    public function contentHistory(H5PContent $content): View
    {
        /** @var \App\Apis\ResourceApiService $resourceService */
        $resourceService = app('\App\Apis\ResourceApiService');
        /** @var \Cerpus\VersionClient\VersionClient $versionClient */
        $versionClient = app('Cerpus\VersionClient\VersionClient');
        $versions = collect();
        $history = [];

        try {
            $foliumId = $resourceService->getResourceFromExternalReference('contentauthor', $content->id)->id;
        } catch (Exception $e) {
            Log::warning($e);
            $foliumId = '';
        }

        if ($content->version_id) {
            $data = $versionClient->getVersion($content->version_id);
            if ($data === false) {
                Log::error(__METHOD__, [$versionClient->getErrorCode(), $versionClient->getError()]);
            }
            $history = $data ? $this->getVersions($data, $versions) : [];
        }

        return view('admin.library-upgrade.content-details', [
            'content' => $content,
            'latestVersion' => !isset($history[$content->id]['children']),
            'foliumId' => $foliumId,
            'history' => $history,
        ]);
    }

    private function getVersions(VersionData $versionData, Collection $stack, $getChildren = true): Collection
    {
        $versionArray = $versionData->toArray();
        $versionArray['versionDate'] = Carbon::createFromTimestampMs($versionData->getCreatedAt())->format('Y-m-d H:i:s e');
        $content = Content::findContentById($versionData->getExternalReference());
        if (!empty($content)) {
            $library = $content->library;
            $versionArray['content'] = [
                'title' => $content->title,
                'created' => $content->created_at->format('Y-m-d H:i:s e'),
                'update' => $content->updated_at->format('Y-m-d H:i:s e'),
                'version_id' => $content->version_id,
                'license' => $content->license,
                'library_id' => $library->id,
                'library' => sprintf('%s %d.%d.%d', $library->name, $library->major_version, $library->minor_version, $library->patch_version),
            ];
        }
        $parent = $versionData->getParent();
        if ($parent) {
            $this->getVersions($parent, $stack);
            $versionArray['parent'] = $parent->getExternalReference();
        }
        $children = $versionData->getChildren();
        if ($children) {
            $versionArray['children'] = [];
            foreach ($children as $child) {
                if ($getChildren) {
                    $this->getVersions($child, $stack, false);
                }
                $versionArray['children'][] = $child->getExternalReference();
            }
        }
        if (!$stack->has($versionData->getExternalReference())) {
            $stack->put($versionData->getExternalReference(), $versionArray);
        }

        return $stack;
    }
}
