<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\ContentVersionController;
use App\Http\Controllers\Api\ContentViewController;
use App\Http\Controllers\Api\LtiToolController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', ApiController::class);

Route::whereUlid('content')->name('api.contents.')->group(function () {
    Route::get('/contents')
        ->uses([ContentController::class, 'index'])
        ->can('admin');

    Route::get('/contents/by_tag/{tag}')
        ->uses([ContentController::class, 'indexByTag'])
        ->can('admin');

    Route::get('/contents/{apiContent}')
        ->uses([ContentController::class, 'show'])
        ->can('view', 'apiContent')
        ->name('show');

    Route::post('/contents')
        ->uses([ContentController::class, 'store'])
        ->can('create', \App\Models\Content::class);

    Route::delete('/contents/{apiContent}')
        ->uses([ContentController::class, 'destroy'])
        ->can('delete', 'apiContent');
});

Route::whereUlid('content')
    ->whereUlid('version')
    ->name('api.contents.versions.')
    ->group(function () {
        Route::get('/contents/{apiContent}/versions/{version}')
            ->uses([ContentVersionController::class, 'show'])
            ->can('view', ['apiContent', 'version'])
            ->name('show');

        Route::post('/contents/{apiContent}/versions')
            ->uses([ContentVersionController::class, 'store'])
            ->can('edit', 'apiContent');

        Route::delete('/contents/{apiContent}/versions/{version}')
            ->uses([ContentVersionController::class, 'destroy'])
            ->can('edit', ['apiContent', 'version']);
    });

Route::whereUlid('content')->whereUlid('view')->name('api.contents.views.')->group(function () {
    Route::get('/contents/{apiContent}/views')
        ->uses([ContentViewController::class, 'index'])
        ->name('index')
        ->can('edit', 'apiContent');

    Route::put('/contents/{apiContent}/views_accumulated')
        ->uses([ContentViewController::class, 'storeAccumulatedViews'])
        ->name('store-views-accumulated')
        ->can('edit', 'apiContent');

    Route::put('/contents/{apiContent}/multiple_views_accumulated')
        ->uses([ContentViewController::class, 'storeMultipleAccumulatedViews'])
        ->name('store-multiple-views-accumulated')
        ->can('edit', 'apiContent');
});

Route::whereUlid('tool')->name('api.lti-tools.')->middleware(['can:admin'])->group(function () {
    Route::get('/lti-tools')
        ->uses([LtiToolController::class, 'index']);

    Route::get('/lti-tools/{tool}')
        ->uses([LtiToolController::class, 'show'])
        ->name('show');
});

Route::name('api.users.')->middleware(['can:admin'])->group(function () {
    Route::get('/users/{user}')
        ->uses([UserController::class, 'show'])
        ->name('show');

    Route::get('/users/by-email/{user:email}')
        ->uses([UserController::class, 'show'])
        ->name('show-by-email')
        ->where('user', '^[^\s@]+@[^\s@]+$');

    Route::post('/users')
        ->uses([UserController::class, 'create'])
        ->name('create');
});
