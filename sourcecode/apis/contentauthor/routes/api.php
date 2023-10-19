<?php

use App\Http\Controllers\API\H5PLibraryController;
use App\Http\Controllers\API\TranslationController;
use App\Http\Controllers\API\H5PFileUploadController;

Route::get('h5p-libraries/{id}', [H5PLibraryController::class, 'getLibraryById']);

Route::post('translate', TranslationController::class)->name('translate');

Route::get('status/{requestId}', H5PFileUploadController::class)->name("api.get.filestatus");
