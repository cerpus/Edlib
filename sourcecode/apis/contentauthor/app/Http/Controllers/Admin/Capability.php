<?php

namespace App\Http\Controllers\Admin;

use Lang;
use App\H5PLibrary;
use App\LibraryDescription;
use App\H5PLibraryCapability;

class Capability
{
    public function refresh($libraryId = null)
    {
        $librariesQuery = H5PLibrary::runnable()->with(['capability', 'description']);
        if (!empty($libraryId)) {
            $librariesQuery->whereIn('id', explode(",", $libraryId));
        }
        $libraries = $librariesQuery->get();

        foreach ($libraries as $library) {
            if (empty($library->capability)) {
                $cap = new H5PLibraryCapability();
                $cap->name = $library->title . ' ' . $library->major_version . '.' . $library->minor_version;
                $cap->enabled = 1;
                $cap->score = 0;
                $library->capability()->save($cap);
            }

            if (empty($library->description)) {
                $desc = new LibraryDescription();
                $desc->title = $library->title;
                $desc->description = '';
                $desc->locale = Lang::getLocale();
                $library->description()->save($desc);
            }
        }
    }
}
