<?php

use App\Http\Controllers\DokuController;
use Illuminate\Support\Facades\Route;

Route::get('/v1/dokus', [DokuController::class, 'getPaginated']);
Route::get('/v1/dokus/{doku}', [DokuController::class, 'get'])
    ->whereUuid('doku');
Route::post('/v1/dokus', [DokuController::class, 'create']);
Route::patch('/v1/dokus/{doku}', [DokuController::class, 'update']);
Route::post('/v1/dokus/{doku}/publish', [DokuController::class, 'publish']);
Route::post('/v1/dokus/{doku}/unpublish', [DokuController::class, 'unpublish']);

// TODO: needed for something? (see resourceapi)
//Route::get('/v1/content');
