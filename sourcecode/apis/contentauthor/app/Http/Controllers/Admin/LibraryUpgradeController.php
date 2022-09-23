<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InvalidH5pPackageException;
use App\H5PLibrariesHubCache;
use App\H5PLibrary;
use App\H5PLibraryLibrary;
use App\Http\Controllers\Controller;
use App\Http\Requests\H5pUpgradeRequest;
use App\Libraries\H5P\AdminConfig;
use App\Libraries\H5P\H5PLibraryAdmin;
use Exception;
use H5PCore;
use H5PFrameworkInterface;
use H5PValidator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class LibraryUpgradeController extends Controller
{
    public function __construct(
        private H5PCore $core,
        private H5PLibraryAdmin $h5pLibraryAdmin,
        private H5PFrameworkInterface $h5pFramework,
    ) {
        $this->middleware('auth');
    }

    public function index(): View
    {
        (new Capability())->refresh();

        $storedLibraries = $this->h5pFramework->loadLibraries();

        $config = resolve(AdminConfig::class);
        $config->getConfig();

        /** @var H5PLibrariesHubCache $hubCacheLibraries */
        $hubCacheLibraries = H5PLibrariesHubCache::all();

        $isPatchUpdate = function ($library) {
            /** @noinspection PhpParamsInspection */
            if ($this->h5pFramework->isPatchedLibrary([
                'machineName' => $library->name,
                'majorVersion' => $library->major_version,
                'minorVersion' => $library->minor_version,
                'patchVersion' => $library->patch_version,
            ])) {
                return [H5PCore::libraryVersion($library)];
            }
            return [];
        };

        // Add settings for each library
        $libraries = collect();
        foreach ($storedLibraries as $versions) {
            $lastVersion = end($versions);
            reset($versions);
            foreach ($versions as $library) {
                $usage = $this->h5pFramework->getLibraryUsage($library->id);
                $item = [
                    'machineName' => $library->name,
                    'majorVersion' => $library->major_version,
                    'minorVersion' => $library->minor_version,
                    'title' => sprintf('%s (%d.%d.%d)', $library->title, $library->major_version, $library->minor_version, $library->patch_version),
                    'numContent' => $usage['content'],
                    'numLibraryDependencies' => $usage['libraries'],
                    'hubUpgrade' => null,
                    'isLast' => $library->id === $lastVersion->id,
                    'libraryId' => $library->id,
                ];

                if ($library->runnable) {
                    $upgrades = $this->core->getUpgrades($library, $versions);

                    $item['upgradeUrl'] = empty($upgrades) || empty($usage['content']) ? false : route('admin.library', [
                        'task' => 'upgrade',
                        'destination' => route('admin.update-libraries'),
                        'library' => $library->id,
                    ]);

                    $hasHubCache = $hubCacheLibraries->firstWhere('machineName', $library->name);
                    if (!empty($hasHubCache) && $lastVersion->id === $library->id) {
                        $newVersion = $this->core->getUpgrades($library, [$hasHubCache]);
                        if (empty($newVersion)) {
                            $newVersion = $isPatchUpdate($hasHubCache);
                        }
                        if (!empty($newVersion)) {
                            $item['hubUpgrade'] = array_shift($newVersion);
                        }
                    }
                }

                $libraries->push($item);
            }
        }

        $available = collect();
        $hubCacheLibraries
            ->each(function ($hubCache) use ($libraries, $hubCacheLibraries, $available) {
                $hasLast = $libraries->where('machineName', $hubCache->name)->firstWhere('isLast', true);
                if (empty($hasLast)) {
                    $available->push([
                        'machineName' => $hubCache->name,
                        'majorVersion' => $hubCache->major_version,
                        'minorVersion' => $hubCache->minor_version,
                        'title' => $hubCache->title,
                        'summary' => $hubCache->summary,
                        'external_link' => $hubCache->example,
                        'numContent' => 0,
                        'numLibraryDependencies' => 0,
                        'hubUpgrade' => sprintf('%s.%s.%s', $hubCache->major_version, $hubCache->minor_version, $hubCache->patch_version),
                        'isLast' => true,
                    ]);
                }
            });

        return view('admin.library-upgrade.index', [
            'installed' => $libraries->sortBy('machineName', SORT_STRING | SORT_FLAG_CASE)->toArray(),
            'available' => $available->sortBy('machineName', SORT_STRING | SORT_FLAG_CASE)->toArray(),
        ]);
    }

    /**
     * Handle an uploaded .h5p file.
     */
    public function upgrade(H5pUpgradeRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $file = $data['h5p_file'];
        assert($file instanceof UploadedFile);

        $errors = [];

        try {
            $this->h5pLibraryAdmin->handleUpload(
                $file->getPathname(),
                !empty($data['h5p_upgrade_only']),
                !empty($data['h5p_disable_file_check']),
            );
        } catch (InvalidH5pPackageException $e) {
            $errors = $e->errors;
        }

        return response()
            ->redirectToRoute('admin.update-libraries')
            ->withErrors($errors);
    }

    public function checkForUpdates(): RedirectResponse
    {
        $this->core->updateContentTypeCache();

        return response()->redirectToRoute('admin.update-libraries');
    }

    public function deleteLibrary(H5PLibrary $library): Response
    {
        if ($library->contents()->exists()) {
            throw new BadRequestHttpException('Cannot delete libraries with existing content');
        }

        $this->core->deleteLibrary((object) [
            'id' => $library->id,
            'name' => $library->name,
            'major_version' => $library->major_version,
            'minor_version' => $library->minor_version,
        ]);

        if ($library->fresh()) {
            throw new Exception("Library not deleted.");
        }

        return response()->noContent();
    }

    public function checkLibrary(H5PLibrary $library): View
    {
        $h5pDataFolderName = $library->getLibraryString(true);
        $disk = Storage::disk();
        $libsFolder = $disk->path('libraries');
        $libFolder = $disk->path('libraries/' . $h5pDataFolderName);

        /** @var H5PValidator $validator */
        $validator = resolve(H5PValidator::class);
        $libraryData = $validator->getLibraryData($h5pDataFolderName, $libFolder, $libsFolder);

        $editorDep = $library->libraries()
            ->where('dependency_type', 'editor')
            ->select('required_library_id')
            ->pluck('required_library_id');
        $preloadedDep = $library->libraries()
            ->where('dependency_type', 'preloaded')
            ->select('required_library_id')
            ->pluck('required_library_id');

        if ($libraryData !== false) {
            foreach(['editorDependencies' => $editorDep, 'preloadedDependencies' => $preloadedDep] as $depType => $deps) {
                if (array_key_exists($depType, $libraryData)) {
                    foreach($libraryData[$depType] as $key => $row) {
                        $depLib = H5PLibrary::fromMachineName($row['machineName'])
                            ->version($row['majorVersion'], $row['minorVersion'])
                            ->select(['id', 'name', 'major_version', 'minor_version', 'patch_version'])
                            ->first();

                        if ($depLib !== null) {
                            $libraryData[$depType][$key]['library'] = $depLib;
                            $isSet = $deps->search($depLib->id);
                            $libraryData[$depType][$key]['dependencySet'] = ($isSet !== false);
                            if ($isSet !== false) {
                                $deps->forget($isSet);
                            }
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
            'preloadDeps' => count($preloadedDep) > 0 ? H5PLibrary::whereIn('id', $preloadedDep)->get() : [],
            'editorDeps' => count($editorDep) > 0 ? H5PLibrary::whereIn('id', $editorDep)->get() : [],
            'usedBy' => H5PLibraryLibrary::where('required_library_id', $library->id)->get(),
            'info' => $validator->h5pF->getMessages('info'),
            'error' => $validator->h5pF->getMessages('error'),
        ]);
    }
}
