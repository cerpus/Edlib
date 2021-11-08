<?php

Route::post('/v1/contenttypes/questionsets', 'API\ContentTypeController@storeH5PQuestionset');
Route::get('/v1/content-types/{contentType}', 'API\ContentInfoController@getContentTypeInfo');
