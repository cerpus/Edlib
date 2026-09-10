<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Console\Commands\H5PClearCachedAssets;
use App\H5PLibrariesCachedAssets;
use App\Libraries\DataObjects\ContentStorageSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

final class CachedFilesController
{
    public function index(): View
    {
        $disk = Storage::disk();
        $cachedFiles = $disk->files(ContentStorageSettings::CACHEDASSETS_DIR);
        $cachedAssetRowsCount = H5PLibrariesCachedAssets::count();
        $isJobRunning = Cache::has(H5PClearCachedAssets::CACHE_KEY_RUNNING);

        return view('admin.cached-files.index', [
            'cachedFilesCount' => count($cachedFiles),
            'cachedAssetRowsCount' => $cachedAssetRowsCount,
            'isJobRunning' => $isJobRunning,
        ]);
    }

    public function delete(): RedirectResponse
    {
        Cache::put(H5PClearCachedAssets::CACHE_KEY_RUNNING, true, now()->addMinutes(H5PClearCachedAssets::CACHE_KEY_RUNNING_TTL));

        Artisan::queue('h5p:clear-cached-assets');

        return redirect(route('admin.cached-files.index'))
            ->with('message', 'Cached files deletion has been queued.');
    }

    public function status(): JsonResponse
    {
        return response()->json([
            'isRunning' => Cache::has(H5PClearCachedAssets::CACHE_KEY_RUNNING),
        ]);
    }

    public function unlock(): RedirectResponse
    {
        H5PClearCachedAssets::clearLock();

        return redirect(route('admin.cached-files.index'))
            ->with('message', 'The deletion lock has been removed.');
    }
}
