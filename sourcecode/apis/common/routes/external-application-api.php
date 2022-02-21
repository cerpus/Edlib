<?php

use App\Http\Controllers\ContentAuthorController;
use App\Http\Controllers\GDPRController;
use App\Http\Controllers\H5PController;
use App\Http\Controllers\ResourceCollaboratorController;
use Illuminate\Support\Facades\Route;

Route::post('/context-resource-collaborators', [ResourceCollaboratorController::class, 'set']);
Route::post('/h5p/generate-from-qa', [H5PController::class, 'generateFromQA']);
Route::post('/contentauthor/question-and-answers', [ContentAuthorController::class, 'questionAndAnswers']);
Route::delete('/gdpr', [GDPRController::class, 'deleteUser']);
