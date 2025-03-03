<?php

namespace App\Http\Controllers\API;

use App\H5PLibrary;
use App\Http\Controllers\Controller;
use H5PCore;
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
}
