<?php

use App\Http\Controllers\API\ContentTypeController;
use App\Http\Controllers\H5PReportController;
use Illuminate\Support\Facades\Route;

Route::post('/v1/contenttypes/questionsets', [ContentTypeController::class, 'storeH5PQuestionset']);

Route::post('/v1/questionsandanswers', [H5PReportController::class, "questionAndAnswer"]);
