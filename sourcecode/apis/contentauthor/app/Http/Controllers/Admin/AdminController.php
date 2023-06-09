<?php

namespace App\Http\Controllers\Admin;

use App\ContentLock;
use App\H5PContent;
use App\H5PLibrary;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminPreSaveScriptRequest;
use App\Libraries\DataObjects\ContentStorageSettings;
use App\Libraries\DataObjects\ResourceUserDataObject;
use App\Libraries\H5P\AdminConfig;
use App\Libraries\H5P\AjaxRequest;
use App\Libraries\H5P\H5PLibraryAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            }
        ])
            ->groupBy('id')
            ->having('contents_count', ">", 0)
            ->orderBy('name')
            ->get()
            ->filter(fn (H5PLibrary $library) => $library->supportsMaxScore());

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
            ->get()
            ->each(function ($resource) {
                /** @var ResourceUserDataObject $ownerData */
                $ownerData = $resource->getOwnerData();
                $resource->ownerName = $ownerData->getNameAndEmail();
                return $resource;
            });
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

    public function getPresaveScript(AdminPreSaveScriptRequest $request)
    {
        /** @var H5PLibrary $library */
        $library = H5PLibrary::fromLibrary($request->validated())->first();

        if ($library) {
            $libraryLocation = sprintf(ContentStorageSettings::PRESAVE_SCRIPT_PATH, $library->getLibraryString(true));
            if (Storage::disk()->exists($libraryLocation)) {
                return response()->stream(function () use ($libraryLocation) {
                    $handle = Storage::disk()->readStream($libraryLocation);
                    while (!feof($handle)) {
                        if (connection_aborted() === 1) {
                            break;
                        }
                        echo fread($handle, 2048);
                    }
                    fclose($handle);
                }, 200);
            }
        }

        return response('Script not found', 404);
    }
}
