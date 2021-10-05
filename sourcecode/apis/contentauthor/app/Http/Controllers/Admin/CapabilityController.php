<?php

namespace App\Http\Controllers\Admin;

use Lang;
use Auth;
use App\LibraryDescription;
use App\H5PLibraryCapability;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Capability;
use App\Http\Requests\CapabilityScoreRequest;
use App\Http\Requests\CapabilityEnableRequest;
use App\Http\Requests\GetTranslatedDescriptionRequest;
use App\Http\Requests\CapabilityDescriptionPostRequest;

class CapabilityController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $capabilities = H5PLibraryCapability::all()->sortBy('name');
        $locale = Lang::getLocale();

        return view('admin.capability.index')->with(compact('capabilities', 'locale'));
    }

    public function refresh()
    {
        if (!Auth::check()) {
            abort(401, 'Not authenticated');
        }

        (new Capability())->refresh();

        return redirect(route('admin.capability'));
    }

    public function enable(CapabilityEnableRequest $request, H5PLibraryCapability $capability)
    {
        $capability->enabled = $request->input('enabled');
        $capability->save();

        return $capability;
    }

    public function score(CapabilityScoreRequest $request, H5PLibraryCapability $capability)
    {
        $capability->score = $request->input('score');
        $capability->save();

        return $capability;
    }

    public function description(CapabilityDescriptionPostRequest $request, H5PLibraryCapability $capability)
    {
        $libraryDescription = LibraryDescription::where('locale', $request->input('locale'))->where('library_id', $capability->library_id)->first();
        if (is_null($libraryDescription)) {
            // Create new translation
            $libraryDescription = new LibraryDescription();
            $libraryDescription->locale = $request->input('locale');
            $libraryDescription->library_id = $capability->library_id;
        }
        $libraryDescription->title = $request->input('title');
        $libraryDescription->description = $request->input('description');
        $libraryDescription->save();

        return $libraryDescription;
    }

    public function translation(GetTranslatedDescriptionRequest $request, H5PLibraryCapability $capability)
    {
        $libraryDescription = LibraryDescription::where('locale', $request->input('locale'))->where('library_id', $capability->library_id)->first();
        if (is_null($libraryDescription)) {
            $libraryDescription = LibraryDescription::where('locale', config('app.locale'))->where('library_id', $capability->library_id)->first();
        }

        return $libraryDescription;
    }
}
