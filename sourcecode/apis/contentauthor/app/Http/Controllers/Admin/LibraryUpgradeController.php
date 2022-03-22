<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InvalidH5pPackageException;
use App\H5PLibrariesHubCache;
use App\Http\Controllers\Controller;
use App\Http\Requests\H5pUpgradeRequest;
use App\Libraries\H5P\AdminConfig;
use App\Libraries\H5P\H5PLibraryAdmin;
use H5PCore;
use H5PFrameworkInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;

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
                        'libraryId' => $library->id,
                    ]);

                    $hasHubCache = $hubCacheLibraries->firstWhere('machineName', $library->name);
                    if (!empty($hasHubCache) && $lastVersion->id === $library->id) {
                        $newVersion = $this->core->getUpgrades($library, [$hasHubCache]);
                        if( empty($newVersion)){
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

        $hubCacheLibraries
            ->each(function ($hubCache) use ($libraries, $hubCacheLibraries) {
                $hasLast = $libraries->where('machineName', $hubCache->name)->firstWhere('isLast', true);
                if (empty($hasLast)) {
                    $libraries->push([
                        'machineName' => $hubCache->name,
                        'majorVersion' => $hubCache->major_version,
                        'minorVersion' => $hubCache->minor_version,
                        'title' => $hubCache->title,
                        'numContent' => 0,
                        'numLibraryDependencies' => 0,
                        'hubUpgrade' => sprintf('%s.%s.%s', $hubCache->major_version, $hubCache->minor_version, $hubCache->patch_version),
                        'isLast' => true,
                    ]);
                }
            });

        return view('admin.library-upgrade.index', [
            'libraries' => $libraries->toArray(),
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
}
