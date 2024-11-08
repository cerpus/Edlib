<?php

use App\Http\Controllers\API\H5PLibraryController;
use App\Http\Controllers\API\QuestionsetController;
use App\Http\Controllers\API\TranslationController;
use App\Http\Controllers\API\H5PFileUploadController;

Route::get('/v1/questionsets/', [QuestionsetController::class, 'getQuestionsets'])->name('api.get.questionsets');
Route::get('/v1/questionsets/search/answers', [QuestionsetController::class, 'searchAnswers'])->name('api.search.answers');
Route::get('/v1/questionsets/search/questions', [QuestionsetController::class, 'searchQuestions'])->name('api.search.questions');
Route::get('/v1/questionsets/{questionsetId}', [QuestionsetController::class, 'getQuestionset'])->name('api.get.questionset');
Route::get('/v1/questionsets/{questionsetId}/questions', [QuestionsetController::class, 'getQuestions'])->name('api.get.questions');
Route::get('/v1/h5p-libraries/{id}', [H5PLibraryController::class, 'getLibraryById']);

Route::post('/api/translate', TranslationController::class)->name('translate');

Route::get('/v1/status/{requestId}', H5PFileUploadController::class)->name("api.get.filestatus");
