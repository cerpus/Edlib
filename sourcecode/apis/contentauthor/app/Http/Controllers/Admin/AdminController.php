<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use Exception;
use H5PCore;
use H5peditor;
use App\H5PContent;
use App\H5PLibrary;
use App\ContentLock;
use Illuminate\Http\Request;
use App\Libraries\SystemInfo;
use Illuminate\Http\Response;
use App\Libraries\LaravelLog;
use App\Libraries\H5P\H5Plugin;
use Illuminate\Http\JsonResponse;
use App\Libraries\H5P\AjaxRequest;
use App\Libraries\H5P\AdminConfig;
use App\Http\Controllers\Controller;
use App\Libraries\H5P\H5PLibraryAdmin;
use App\Libraries\DataObjects\ResourceUserDataObject;

class AdminController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $editLockCount = ContentLock::active()->get()->count();
        return view('admin.index')->with(compact('editLockCount'));
    }

    public function contentUpgrade(Request $request)
    {
        try {
            return response()->json((new H5PLibraryAdmin)->upgradeProgress($request));
        } catch (\Exception $exception) {
            return response($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
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
        $config->h5plugin = H5Plugin::get_instance(DB::connection()->getPdo());
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
        try {
            return response()->json((new H5PLibraryAdmin)->upgradeMaxscore($request->get('libraries'), $request->get('scores')));
        } catch (\Exception $exception) {
            return response($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
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

    public function logs(Request $request)
    {
        $lines = $request->input('lines', 2000);

        $log = (new LaravelLog)->read($lines);

        return view('admin.logs')->with(compact('log'));;
    }

    public function systemInfo()
    {
        $sysinfo = new SystemInfo();

        $loadAvg = $sysinfo->getLoadAverage();
        $memoryUsage = $sysinfo->getMemoryUsage();
        $availMem = $sysinfo->getAvailableMemory();
        $phpVersion = $sysinfo->getPhpVersion();
        $cpuInfo = $sysinfo->getCpuInfo();
        $uptime = $sysinfo->getUptime();
        $memoryLimit = '???';
        if (function_exists('ini_get')) {
            $memoryLimit = ini_get('memory_limit');
        }

        $env = collect($_ENV)
            ->map(function ($value, $key) {
                if (in_array(strtoupper($key), [
                    'APP_KEY',
                    'PASSWORD',
                    'OERPASS',
                    'SECRET',
                    'PUBKEY',
                ], true)) {
                    return '<a href="https://youtu.be/iThtELZvfPs" target="_blank">Keep it secret, keep it safe</a>';
                };
                return $value;
            })
            ->all();

        $extensions = array_sort(get_loaded_extensions());

        return view('admin.system-info')->with(compact('loadAvg', 'availMem', 'memoryUsage', 'phpVersion', 'uptime',
            'cpuInfo', 'memoryLimit', 'env', 'extensions'));
    }

    /**
     * @param Request $request
     * @param H5PCore $core
     * @param H5peditor $editor
     * @return array|JsonResponse|void
     * @throws Exception
     */
    public function ajaxLoading(Request $request, H5PCore $core, H5peditor $editor)
    {
        $h5pPlugin = H5Plugin::get_instance(DB::connection()->getPdo());

        $ajaxRequest = new AjaxRequest($h5pPlugin, $core, $editor);
        $returnValue = $ajaxRequest->handleAjaxRequest($request);
        switch ($ajaxRequest->getReturnType()) {
            case "json":
                return response()->json($returnValue);
                break;
            default:
                return $returnValue;
        }
    }
}
