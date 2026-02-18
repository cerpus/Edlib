<?php

declare(strict_types=1);

// Routes that should not start sessions

use App\Http\Controllers\ContentAuthorController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\LtiController;
use App\Http\Controllers\LtiSample\PresentationController;
use App\Http\Controllers\LtiSample\ResizeController;
use App\Http\Controllers\OembedController;
use App\Http\Middleware\LtiValidatedRequest;
use Illuminate\Support\Facades\Route;

Route::middleware('signed')
    ->get('/lti-launch')
    ->uses([LtiController::class, 'launch'])
    ->name('lti.launch');

Route::post('/lti/samples/resize')
    ->uses(ResizeController::class)
    ->middleware(LtiValidatedRequest::class . ':platform')
    ->name('lti.samples.resize');

Route::post('/lti/samples/presentation')
    ->uses(PresentationController::class)
    ->middleware([
        LtiValidatedRequest::class . ':platform',
        'lti.launch-type:basic-lti-launch-request',
    ]);

Route::get('/oembed')
    ->uses(OembedController::class)
    ->name('oembed');

Route::get('/sitemap.xml')
    ->uses([ContentController::class, 'sitemap'])
    ->name('sitemap');

Route::name('author.content.')
    ->prefix('/author')
    ->middleware([LtiValidatedRequest::class . ':tool'])
    ->group(function () {
        Route::post('/tool/{tool}/content/info')
            ->uses([ContentAuthorController::class, 'info'])
            ->name('info');

        Route::post('/tool/{tool}/content-versions/leaves')
            ->uses([ContentAuthorController::class, 'leaves'])
            ->name('leaves');

        Route::post('/tool/{tool}/content/{content}/version/{version}/update')
            ->uses([ContentAuthorController::class, 'update'])
            ->whereUlid(['tool', 'content', 'version'])
            ->name('update');

        Route::post('/tool/{tool}/content-exclusions/list')
            ->uses([ContentAuthorController::class, 'listExclusions'])
            ->name('exclusions.list');

        Route::post('/tool/{tool}/content-exclusions/add')
            ->uses([ContentAuthorController::class, 'addExclusions'])
            ->name('exclusions.add');

        Route::post('/tool/{tool}/content-exclusions/delete')
            ->uses([ContentAuthorController::class, 'deleteExclusions'])
            ->name('exclusions.delete');
    });
