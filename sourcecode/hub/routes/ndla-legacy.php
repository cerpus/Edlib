<?php

declare(strict_types=1);

use App\Http\Controllers\NdlaLegacy\CopyController;
use App\Http\Controllers\NdlaLegacy\OembedController;
use App\Http\Controllers\NdlaLegacy\PublishController;
use App\Http\Controllers\NdlaLegacy\ResourceCopyrightController;
use App\Http\Controllers\NdlaLegacy\ResourceInformationController;
use App\Http\Controllers\NdlaLegacy\SelectController;
use App\Http\Controllers\NdlaLegacy\SwaggerController;
use App\Http\Controllers\NdlaLegacy\ViewResourceController;
use Illuminate\Support\Facades\Route;

Route::get('/resource/{edlib2UsageContent}')
    ->uses(ViewResourceController::class)
    ->name('ndla-legacy.resource')
    ->whereUuid('edlib2UsageContent');

Route::get('/v1/resource/{edlib2UsageContent}/copyright')
    ->uses(ResourceCopyrightController::class)
    ->whereUuid('edlib2UsageContent');

Route::get('/v2/resource/{edlib2UsageContent}/copyright')
    ->uses(ResourceCopyrightController::class)
    ->whereUuid('edlib2UsageContent');

Route::get('/v1/resource/{edlib2UsageContent}/info')
    ->uses(ResourceInformationController::class)
    ->whereUuid('edlib2UsageContent');

Route::put('/v1/resource/{id}/publish')
    ->middleware(['auth:ndla-legacy'])
    ->uses(PublishController::class);

Route::put('/v1/resource/publish')
    ->middleware(['auth:ndla-legacy'])
    ->uses(PublishController::class);

Route::get('/oembed')
    ->uses([OembedController::class, 'content'])
    ->name('ndla-legacy.oembed');

Route::get('/oembed/preview')
    ->uses([OembedController::class, 'content']);

Route::post('/select')
    ->middleware(['auth:ndla-legacy'])
    ->uses([SelectController::class, 'select']);

Route::post('/select/edit/byurl')
    ->middleware(['auth:ndla-legacy'])
    ->uses([SelectController::class, 'selectByUrl'])
    ->name('ndla-legacy.select-by-url');

Route::post('/copy')
    ->uses(CopyController::class)
    ->middleware(['auth:ndla-legacy'])
    ->name('ndla-legacy.copy');

Route::middleware([\Illuminate\Session\Middleware\StartSession::class])->group(function () {
    // internal, not part of the API
    Route::get('/select')
        ->middleware(['signed'])
        ->uses([SelectController::class, 'selectIframe'])
        ->name('ndla-legacy.select-iframe');

    // internal, not part of the API
    Route::post('/select/return')
        ->middleware([\App\Http\Middleware\LtiValidatedRequest::class . ':platform'])
        ->uses([SelectController::class, 'return'])
        ->name('ndla-legacy.select-return');
});

Route::get('/swagger')
    ->uses([SwaggerController::class, 'swagger'])
    ->withoutMiddleware(\App\Http\Middleware\ContentSecurityPolicy::class)
    ->name('ndla-legacy.swagger');

Route::get('/swagger-ui.html')
    ->uses([SwaggerController::class, 'redirect']);

Route::get('/openapi.json')
    ->uses([SwaggerController::class, 'schema'])
    ->name('ndla-legacy.openapi-schema');
