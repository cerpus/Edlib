<?php

namespace App\Http\Controllers\Admin;

use App\ContentLock;
use App\H5PContent;
use App\H5PLibrary;
use App\Http\Controllers\Controller;
use App\Libraries\H5P\AdminConfig;
use App\Libraries\H5P\AjaxRequest;
use App\Libraries\H5P\H5PLibraryAdmin;
use App\Libraries\H5P\Interfaces\CerpusStorageInterface;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct(
        private H5PLibraryAdmin $h5pLibraryAdmin,
    ) {
        $this->middleware('auth');
    }

    public function index()
    {
        $editLockCount = ContentLock::active()->get()->count();
        return view('admin.index')->with(compact('editLockCount'));
    }

    public function contentUpgrade(Request $request)
    {
        return response()->json($this->h5pLibraryAdmin->upgradeProgress($request));
    }

    public function viewMaxScoreOverview()
    {
        $libraries = H5PLibrary::withCount([
            'contents' => function ($query) {
                H5PContent::noMaxScoreScope($query);
            },
        ])
            ->groupBy('id')
            ->having('contents_count', ">", 0)
            ->orderBy('name')
            ->get()
            ->filter(fn(H5PLibrary $library) => $library->supportsMaxScore());

        $config = resolve(AdminConfig::class);
        $config->addPresaveScripts();
        $scoreConfig = json_encode([
            'endpoint' => route('admin.maxscore.update'),
            'token' => csrf_token(),
        ]);

        return view('admin.maxscore-overview', [
            'libraries' => $libraries,
            'scripts' => $config->getScriptAssets(),
            'scoreConfig' => $scoreConfig,
            'settings' => json_encode($config->getMaxScoreSettings()),
            'numFailed' => H5PContent::where('bulk_calculated', H5PLibraryAdmin::BULK_FAILED)->count(),
            'libraryPath' => app(CerpusStorageInterface::class)->getLibrariesPath(),
        ]);
    }

    public function updateMaxScore(Request $request)
    {
        return response()->json($this->h5pLibraryAdmin->upgradeMaxscore($request->get('libraries'), $request->get('scores')));
    }

    public function viewFailedCalculations()
    {
        $resources = H5PContent::with('library')
            ->where('bulk_calculated', H5PLibraryAdmin::BULK_FAILED)
            ->get();

        return view('admin.maxscore-failed-overview', compact('resources'));
    }

    public function ajaxLoading(Request $request, AjaxRequest $ajaxRequest): object|array|string|null
    {
        $returnValue = $ajaxRequest->handleAjaxRequest($request);
        return match ($ajaxRequest->getReturnType()) {
            "json" => response()->json($returnValue),
            default => $returnValue,
        };
    }
}
