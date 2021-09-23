<?php

use App\Http\Controllers\AccessTokenController;
use App\Http\Controllers\ApplicationController;
use Illuminate\Support\Facades\Route;

Route::get('/applications', [ApplicationController::class, 'list']);
Route::post('/applications', [ApplicationController::class, 'create']);

Route::get('/applications/{application}/access_tokens', [AccessTokenController::class, 'listByApplication']);
Route::post('/applications/{application}/access_tokens', [AccessTokenController::class, 'create']);
Route::delete('/applications/{application}/access_tokens/{accessToken}', [AccessTokenController::class, 'delete']);
