<?php

use App\Libraries\H5P\Dataobjects\H5PTranslationDataObject;
use App\Libraries\H5P\Interfaces\TranslationServiceInterface;

Route::get('questionsets/', 'API\QuestionsetController@getQuestionsets')->name('api.get.questionsets');
Route::get('questionsets/tags', 'API\TagController@searchTags')->name('api.search.tags');
Route::get('questionsets/search/answers', 'API\QuestionsetController@searchAnswers')->name('api.search.answers');
Route::get('questionsets/search/questions', 'API\QuestionsetController@searchQuestions')->name('api.search.questions');
Route::get('questionsets/{questionsetId}', 'API\QuestionsetController@getQuestionset')->name('api.get.questionset');
Route::get('questionsets/{questionsetId}/questions', 'API\QuestionsetController@getQuestions')->name('api.get.questions');
Route::get('h5p-libraries/{id}', 'API\H5PLibraryController@getLibraryById');

Route::post('translate', 'API\TranslationController')->name('translate');

Route::get('status/{requestId}', 'API\H5PFileUploadController')->name("api.get.filestatus");
