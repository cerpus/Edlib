<?php

use App\Http\Controllers\H5PReportController;

Route::post('/v1/contenttypes/questionsets', 'API\ContentTypeController@storeH5PQuestionset');
Route::get('/v1/content-types/{contentType}', 'API\ContentInfoController@getContentTypeInfo');

Route::post('/v1/questionsandanswers', [H5PReportController::class, "questionAndAnswer"]);
