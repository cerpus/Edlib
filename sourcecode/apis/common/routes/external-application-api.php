<?php

use App\Http\Controllers\H5PController;
use App\Http\Controllers\ResourceCollaboratorController;
use Illuminate\Support\Facades\Route;

Route::post('/context-resource-collaborators', [ResourceCollaboratorController::class, 'set']);
Route::post('/h5p/generate-from-qa', [H5PController::class, 'generateFromQA']);
