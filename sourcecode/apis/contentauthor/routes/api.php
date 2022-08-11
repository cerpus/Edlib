<?php

use App\Http\Controllers\API\QuestionsetController;
use App\Http\Controllers\API\TranslationController;
use App\Http\Controllers\API\H5PFileUploadController;

Route::get('questionsets/', [QuestionsetController::class, 'getQuestionsets'])->name('api.get.questionsets');
Route::get('questionsets/search/answers', [QuestionsetController::class, 'searchAnswers'])->name('api.search.answers');
Route::get('questionsets/search/questions', [QuestionsetController::class, 'searchQuestions'])->name('api.search.questions');
Route::get('questionsets/{questionsetId}', [QuestionsetController::class, 'getQuestionset'])->name('api.get.questionset');
Route::get('questionsets/{questionsetId}/questions', [QuestionsetController::class, 'getQuestions'])->name('api.get.questions');
Route::get('h5p-libraries/{id}', [QuestionsetController::class, 'getLibraryById']);

Route::post('translate', TranslationController::class)->name('translate');

Route::get('status/{requestId}', H5PFileUploadController::class)->name("api.get.filestatus");
