<?php

namespace App\Http\Controllers\API;

use App\H5PLibrary;
use App\Http\Controllers\Controller;
use H5PCore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class H5PLibraryController extends Controller
{
    public function getLibraryById($id)
    {
        $library = H5PCore::libraryFromString($id);

        if (!$library) {
            throw ValidationException::withMessages([
                "id" => "invalid id",
            ]);
        }

        $h5pLibrary = H5PLibrary::where([
            "name" => $library["machineName"],
            "major_version" => $library["majorVersion"],
            "minor_version" => $library["minorVersion"],
        ])->firstOrFail();

        $h5pLibrary->semantics = json_decode($h5pLibrary->semantics);

        return response($h5pLibrary);
    }

    public function getLibraryTitleByMachineName(Request $request): Response|JsonResponse
    {
        $request->validate([
            "machineNames" => "required:array",
            "machineNames.*" => "string",
        ]);
        $machineNames = $request->get('machineNames');
        // Get the titles, we get 'title' for all versions, but the latest is first for each 'name' (machine-name)
        $info = H5PLibrary::select(DB::raw('LOWER(name) as name'), 'title', 'major_version', 'minor_version')
            ->whereIn('name', $machineNames)
            ->orderBy('name', 'ASC')
            ->orderBy('major_version', 'DESC')
            ->orderBy('minor_version', 'DESC')
            ->get()
            ->toArray();

        $result = array_reduce($info, function ($result, $item) {
            $key = $item['name'];
            if (!isset($result[$key])) {
                $result[$key] = $item['title'];
            }
            return $result;
        });

        return new JsonResponse($result, 200);
    }
}
