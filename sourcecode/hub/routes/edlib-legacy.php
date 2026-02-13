<?php

declare(strict_types=1);

use App\Http\Controllers\EdlibLegacyController;
use App\Http\Middleware\LtiValidatedRequest;
use Illuminate\Support\Facades\Route;

Route::domain('www.edlib.com')
    ->get('/s/resources/{content:edlib2_id}')
    ->uses([EdlibLegacyController::class, 'redirectFromEdlib2Id'])
    ->whereUuid('content');

Route::domain('www.h5p.ndla.no')
    ->get('/s/resources/{content:edlib2_id}')
    ->uses([EdlibLegacyController::class, 'redirectFromEdlib2Id'])
    ->whereUuid('content');

Route::middleware([LtiValidatedRequest::class . ':platform'])->group(function () {
    Route::domain('core.cerpus-course.com')
        ->post('/lti/launch/{edlib2UsageContent}')
        ->uses([EdlibLegacyController::class, 'redirectLtiLaunch'])
        ->whereUuid('edlib2UsageContent');

    // Doesn't actually match what used to be on that endpoint, but Gamilab will
    // only send LTI requests here now.
    Route::domain('api.edlib.com')
        ->post('/lti/v2/lti-links/{edlib2UsageContent}')
        ->uses([EdlibLegacyController::class, 'redirectLtiLaunch'])
        ->whereUuid('edlib2UsageContent');
});
