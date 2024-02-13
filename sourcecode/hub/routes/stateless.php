<?php

declare(strict_types=1);

// Routes that should not start sessions

use App\Http\Controllers\ContentController;
use App\Http\Controllers\LtiController;
use App\Http\Controllers\OembedController;
use App\Http\Middleware\LtiValidatedRequest;
use Illuminate\Support\Facades\Route;

Route::middleware('signed')
    ->get('/lti-launch')
    ->uses([LtiController::class, 'launch'])
    ->name('lti.launch');

Route::post('/lti/resize-test')
    ->uses([LtiController::class, 'resizeTest'])
    ->middleware(LtiValidatedRequest::class . ':platform')
    ->name('lti.resize-test');

Route::get('/oembed')
    ->uses(OembedController::class)
    ->name('oembed');

Route::get('/sitemap.xml')
    ->uses([ContentController::class, 'sitemap'])
    ->name('sitemap');
