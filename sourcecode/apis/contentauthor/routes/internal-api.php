<?php

use App\Http\Controllers\API\ContentInfoController;
use App\Http\Controllers\API\ContentTypeController;
use App\Http\Controllers\H5PReportController;
use Illuminate\Support\Facades\Route;

Route::post('/v1/contenttypes/questionsets', [ContentTypeController::class, 'storeH5PQuestionset']);
Route::get('/v1/content-types/{contentType}', [ContentInfoController::class, 'getContentTypeInfo']);
Route::get('/v1/content-version/{id}', [ContentInfoController::class, 'getVersion']);
Route::get('/v1/content-version/{version}/history', [ContentInfoController::class, 'getPreviousVersions']);

Route::post('/v1/questionsandanswers', [H5PReportController::class, "questionAndAnswer"]);
