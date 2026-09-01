<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\H5PLibrariesCachedAssets;
use App\Libraries\DataObjects\ContentStorageSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

final class CachedFilesController
{
    public function index(): View
    {
        $disk = Storage::disk();
        $cachedFiles = $disk->files(ContentStorageSettings::CACHEDASSETS_DIR);
        $cachedAssetRowsCount = H5PLibrariesCachedAssets::count();

        return view('admin.cached-files.index', [
            'cachedFilesCount' => count($cachedFiles),
            'cachedAssetRowsCount' => $cachedAssetRowsCount,
        ]);
    }

    public function delete(): RedirectResponse
    {
        Artisan::call('h5p:clear-cached-assets');
        $output = trim(Artisan::output());

        return redirect(route('admin.cached-files.index'))
            ->with('message', $output !== '' ? $output : 'Cached files deleted.');
    }
}
