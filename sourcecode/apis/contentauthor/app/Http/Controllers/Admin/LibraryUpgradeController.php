<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InvalidH5pPackageException;
use App\H5PLibrariesHubCache;
use App\H5PLibrary;
use App\H5POption;
use App\Http\Controllers\Controller;
use App\Http\Requests\H5pUpgradeRequest;
use App\Libraries\H5P\AdminConfig;
use App\Libraries\H5P\H5PLibraryAdmin;
use Exception;
use H5PCore;
use H5PFrameworkInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
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

        $hubCacheLibraries = H5PLibrariesHubCache::all();

        $isPatchUpdate = function ($library) {
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
        $contentTypes = collect();
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
                    'canDelete' => H5PLibrary::canBeDeleted($library->id, $usage['libraries']),
                    'hubUpgradeIsPatch' => null,
                    'hubUpgradeError' => '',
                    'hubUpgradeMessage' => '',
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
                        $item['hubUpgradeIsPatch'] = false;
                        $newVersion = $this->core->getUpgrades($library, [$hasHubCache]);
                        if (empty($newVersion)) {
                            $item['hubUpgradeIsPatch'] = true;
                            $newVersion = $isPatchUpdate($hasHubCache);
                        }
                        if (!empty($newVersion)) {
                            $item['hubUpgrade'] = array_shift($newVersion);
                            $item['hubUpgradeMessage'] = $this->libraryUpdateMessage($item['hubUpgrade'], $item['hubUpgradeIsPatch']);
                            $item['hubUpgradeError'] = $this->libraryUpdateErrorMessage($hasHubCache->h5p_major_version, $hasHubCache->h5p_minor_version, $item['hubUpgrade']);
                        }
                    }
                    $contentTypes->push($item);
                } else {
                    $libraries->push($item);
                }
            }
        }

        $available = collect();
        if (config('h5p.isHubEnabled')) {
            $hubCacheLibraries
                ->each(function ($hubCache) use ($contentTypes, $available) {
                    $hasLast = $contentTypes->where('machineName', $hubCache->name)->firstWhere('isLast', true);
                    if (empty($hasLast)) {
                        $available->push([
                            'machineName' => $hubCache->name,
                            'majorVersion' => $hubCache->major_version,
                            'minorVersion' => $hubCache->minor_version,
                            'title' => sprintf('%s (%d.%d.%d)', $hubCache->title, $hubCache->major_version, $hubCache->minor_version, $hubCache->patch_version),
                            'summary' => $hubCache->summary,
                            'external_link' => $hubCache->example,
                            'numContent' => 0,
                            'numLibraryDependencies' => 0,
                            'hubUpgrade' => sprintf('%s.%s.%s', $hubCache->major_version, $hubCache->minor_version, $hubCache->patch_version),
                            'isLast' => true,
                            'hubUpgradeIsPatch' => null,
                            'hubUpgradeError' => $this->libraryUpdateErrorMessage($hubCache->h5p_major_version, $hubCache->h5p_minor_version),
                            'hubUpgradeMessage' => $this->libraryUpdateMessage(sprintf('%s.%s.%s', $hubCache->major_version, $hubCache->minor_version, $hubCache->patch_version), null),
                        ]);
                    }
                });
        }

        return view('admin.library-upgrade.index', [
            'installedLibraries' => $libraries->sortBy('machineName', SORT_STRING | SORT_FLAG_CASE)->toArray(),
            'installedContentTypes' => $contentTypes->sortBy('machineName', SORT_STRING | SORT_FLAG_CASE)->toArray(),
            'available' => $available->sortBy('machineName', SORT_STRING | SORT_FLAG_CASE)->toArray(),
            'contentTypeCacheUpdateAt' => H5POption::select('option_value')->where('option_name', 'content_type_cache_updated_at')->first(),
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
            ->redirectToRoute('admin.update-libraries', ['activetab' => $request->input('activetab')])
            ->withErrors($errors);
    }

    public function checkForUpdates(Request $request): RedirectResponse
    {
        $this->core->updateContentTypeCache();

        return response()->redirectToRoute('admin.update-libraries', ['activetab' => $request->input('activetab')]);
    }

    public function deleteLibrary(H5PLibrary $library): Response
    {
        if (!H5PLibrary::canBeDeleted($library->id)) {
            throw new BadRequestHttpException('Cannot delete library used by content or other libraries');
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

    private function libraryUpdateErrorMessage(int $coreMajor, int $coreMinor, string|null $libraryVersion = null): string|null
    {
        $message = null;
        $installedCoreMajor = H5PCore::$coreApi['majorVersion'];
        $installedCoreMinor = H5PCore::$coreApi['minorVersion'];

        if ($installedCoreMajor < $coreMajor || ($installedCoreMajor === $coreMajor && $installedCoreMinor < $coreMinor)) {
            $message = 'H5P Core version ' . $coreMajor . '.' . $coreMinor . ' is required.';
            if ($libraryVersion !== null) {
                $message = 'New version ' . $libraryVersion . " cannot be installed,\r\n" . $message;
            }
        }

        return $message;
    }

    private function libraryUpdateMessage(string $newVersion, bool|null $isPatch): string
    {
        $msg = 'Download and install version ' . $newVersion;
        return $msg . match ($isPatch) {
            true => "\r\nNew version will replace installed version.",
            false => "\r\nNew version will be installed in addition to existing versions.",
            default => '',
        };
    }
}
