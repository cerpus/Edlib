<?php

use App\Http\Controllers\AccessTokenController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\OembedController;
use Illuminate\Support\Facades\Route;

Route::get('/applications', [ApplicationController::class, 'list']);
Route::get('/applications/{application}', [ApplicationController::class, 'get']);
Route::post('/applications', [ApplicationController::class, 'create']);

Route::get('/applications/{application}/access_tokens', [AccessTokenController::class, 'listByApplication']);
Route::post('/applications/{application}/access_tokens', [AccessTokenController::class, 'create']);
Route::delete('/applications/{application}/access_tokens/{accessToken}', [AccessTokenController::class, 'delete']);

Route::post('/oembed/select', [OembedController::class, 'preStartContentExplorer'])->middleware('auth');
Route::get('/oembed/select/{oembedContext}', [OembedController::class, 'startContentExplorer']);
