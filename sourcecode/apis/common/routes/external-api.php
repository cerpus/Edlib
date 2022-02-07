<?php

use App\Http\Controllers\AuthMigrationController;
use App\Http\Controllers\AccessTokenController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\MaintenanceModeController;
use Illuminate\Support\Facades\Route;

Route::get('/applications', [ApplicationController::class, 'list']);
Route::get('/applications/{application}', [ApplicationController::class, 'get']);
Route::post('/applications', [ApplicationController::class, 'create']);

Route::get('/applications/{application}/access_tokens', [AccessTokenController::class, 'listByApplication']);
Route::post('/applications/{application}/access_tokens', [AccessTokenController::class, 'create']);
Route::delete('/applications/{application}/access_tokens/{accessToken}', [AccessTokenController::class, 'delete']);

Route::get('/maintenance_mode', [MaintenanceModeController::class, 'status']);
Route::put('/maintenance_mode', [MaintenanceModeController::class, 'toggle']);

Route::get('/auth-migrations/{id}', [AuthMigrationController::class, 'get']);
Route::post('/auth-migrations/{id}/execute', [AuthMigrationController::class, 'execute']);
Route::post('/auth-migrations', [AuthMigrationController::class, 'create']);
