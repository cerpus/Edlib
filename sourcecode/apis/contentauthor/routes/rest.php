<?php

use App\Http\Controllers\API\H5PLibraryController;

// Get the H5P library (content type) title for library machine-names
Route::post('v1/h5p/library/title', [H5PLibraryController::class, 'getLibraryTitleByMachineName'])
    ->middleware(['auth.psk:X-PSK,app.consumer-key']);
