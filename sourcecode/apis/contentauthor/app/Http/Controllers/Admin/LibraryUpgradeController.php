<?php

namespace App\Http\Controllers\Admin;

use App\H5PLibrariesHubCache;
use App\Http\Controllers\Controller;
use App\Libraries\H5P\AdminConfig;
use App\Libraries\H5P\H5PLibraryAdmin;
use H5PCore;
use Illuminate\Http\Request;

class LibraryUpgradeController extends Controller
{
    public function __construct(
        private H5PCore $core,
        private H5PLibraryAdmin $h5pLibraryAdmin,
    ) {
        $this->middleware('auth');
    }

    public function index(Request $request, \H5PFrameworkInterface $framework)
    {
        $this->h5pLibraryAdmin->process_libraries(); // Upgrades the libraries if we have a .h5p file
        if ($request->method() === 'POST' && isset($_FILES['h5p_file']) && $_FILES['h5p_file']['error'] === 0) { //
            (new Capability())->refresh();
        }

        $storedLibraries = $framework->loadLibraries();

        $config = resolve(AdminConfig::class);
        $config->getConfig();

        /** @var H5PLibrariesHubCache $hubCacheLibraries */
        $hubCacheLibraries = H5PLibrariesHubCache::all();

        $isPatchUpdate = function ($library) use ($framework) {
            if ($framework->isPatchedLibrary([
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
                $usage = $framework->getLibraryUsage($library->id, false);
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

    public function checkForUpdates()
    {
        $this->core->updateContentTypeCache();

        return response()->redirectToRoute('admin.update-libraries');
    }
}
