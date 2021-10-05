<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;

class ImportExportSettingsController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.import-export-settings.index');
    }

    public function resetTracking(Request $request)
    {
        /** @var H5PAdapterInterface $adapter */
        $adapter = resolve(H5PAdapterInterface::class);

        $adapter->resetNdlaIdTracking();

        return redirect(route('admin.importexport.index'));
    }

    public function emptyArticleImportLog(Request $request)
    {
        /** @var H5PAdapterInterface $adapter */
        $adapter = resolve(H5PAdapterInterface::class);

        $adapter->emptyArticleImportLog();

        return redirect(route('admin.importexport.index'));
    }

    public function runPresave()
    {
        /** @var H5PAdapterInterface $adapter */
        $adapter = resolve(H5PAdapterInterface::class);

        $adapter->runPresaveCommand();

        return redirect(route('admin.importexport.index'));
    }
}
