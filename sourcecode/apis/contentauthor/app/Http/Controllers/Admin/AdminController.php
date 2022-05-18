<?php

namespace App\Http\Controllers\Admin;

use App\Libraries\ContentAuthorStorage;
use Illuminate\Support\Facades\DB;
use Exception;
use H5PCore;
use H5peditor;
use App\H5PContent;
use App\H5PLibrary;
use App\ContentLock;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Libraries\H5P\AjaxRequest;
use App\Libraries\H5P\AdminConfig;
use App\Http\Controllers\Controller;
use App\Libraries\H5P\H5PLibraryAdmin;
use App\Libraries\DataObjects\ResourceUserDataObject;

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
            ->having('contents_count', ">", 0)
            ->orderBy('name')
            ->get()
            ->reduce(function ($old, $new) {
                if (array_key_exists($new->name, $old)) {
                    $old[$new->name]->contents_count += $new->contents_count;
                } else {
                    $old[$new->name] = $new;
                }
                return $old;
            }, []);

        $numFailed = H5PContent::with('library')
            ->where('bulk_calculated', H5PLibraryAdmin::BULK_FAILED)
            ->count();
        $config = resolve(AdminConfig::class);
        $config->addPresaveScripts();
        $scripts = $config->getScriptAssets();
        $settings = json_encode($config->getMaxScoreSettings());

        $scoreConfig = json_encode([
            'endpoint' => route('admin.maxscore.update'),
            'token' => csrf_token(),
            'done' => '<p>Calculations are done!</p>',
        ]);

        return view('admin.maxscore-overview', compact('libraries', 'scripts', 'scoreConfig', 'settings', 'numFailed'));
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
}
