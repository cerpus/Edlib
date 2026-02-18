<?php

use App\Http\Controllers\API\LinkInfoController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ArticleCopyrightController;
use App\Http\Controllers\ArticleUploadController;
use App\Http\Controllers\ContentAssetController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\H5PController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\NdlaContentController;
use App\Http\Controllers\Progress;
use App\Http\Controllers\QuestionSetController;
use App\Http\Controllers\ReturnToCoreController;
use App\Http\Controllers\SingleLogoutController;
use Illuminate\Support\Facades\Route;
use Laravel\Nightwatch\Http\Middleware\Sample;

Route::get('/lti-return', ReturnToCoreController::class)
    ->middleware('signed')
    ->name('lti-return');

Route::post('h5p/adapter', function () {
    return response()->noContent();
})->name('h5p.adapter')->middleware('adaptermode');
Route::get('h5p/{h5p}/copyright', [H5PController::class, 'getCopyright']);
Route::get('h5p/{h5p}/info', [H5PController::class, 'getInfo']);

Route::get('images/browse/{imageId}', [NdlaContentController::class, 'getImage']);

Route::get('videos/browse', [H5PController::class, 'browseVideos']);
Route::get('videos/browse/{videoId}', [H5PController::class, 'getVideo']);

Route::get('audios/browse/{audioId}', [NdlaContentController::class, 'getAudio']);

Route::get('h5p/{h5p}/download', [H5PController::class, 'downloadContent'])->name('content-download')->middleware(['adaptermode']);
Route::get('content/upgrade/library', [H5PController::class, 'contentUpgradeLibrary'])->name('content-upgrade-library');

Route::middleware(['core.return', 'lti.add-to-session', 'lti.signed-launch', 'core.locale', 'adaptermode'])->group(function () {
    Route::resource('/h5p', H5PController::class, ['except' => ['index', 'destroy']]);
    Route::post("/h5p/create/{contenttype?}", [H5PController::class, 'create']);
    Route::post('/h5p/{id}', [H5PController::class, 'show'])->middleware(['core.behavior-settings:view', 'lti.redirect-to-editor'])->name('h5p.ltishow');
    Route::post('/h5p/{id}/edit', [H5PController::class, 'edit'])->middleware(['core.behavior-settings:editor'])->name('h5p.ltiedit');

    Route::resource('/link', LinkController::class, ['only' => ['show']]);
    Route::post('/link/{id}', [LinkController::class, 'show'])->middleware(['lti.redirect-to-editor']);

    Route::resource('/article', ArticleController::class, ['except' => ['index', 'destroy']]);
    Route::post('/article/create', [ArticleController::class, 'create'])->middleware(['core.behavior-settings:editor']);
    Route::post('/article/{id}', [ArticleController::class, 'show'])->middleware(['core.behavior-settings:view', 'lti.redirect-to-editor']);
    Route::post('/article/{id}/edit', [ArticleController::class, 'edit'])->middleware(['core.behavior-settings:editor']);

    Route::resource('/questionset', QuestionSetController::class, ['except' => ['index', 'destroy']]);
    Route::post('/questionset/create', [QuestionSetController::class, 'create']);
    Route::post('/questionsets/image', [QuestionSetController::class, 'setQuestionImage'])->name('set.questionImage');
    Route::post('/questionset/{id}', [QuestionSetController::class, 'show'])->middleware(['lti.redirect-to-editor']);
    Route::post('/questionset/{id}/edit', [QuestionSetController::class, 'edit']);

    Route::resource('/game', GameController::class, ['except' => ['index', 'destroy']]);
    Route::post('/game/create/{type}', [GameController::class, 'create']);
    Route::post('/game/{id}', [GameController::class, 'show'])->middleware(['lti.redirect-to-editor']);
    Route::post('/game/{id}/edit', [GameController::class, 'edit']);

    // deprecated routes, do not add more of these.
    // references to these exist in external systems, so these cannot be removed.
    Route::post('/lti-content/create/article', [ArticleController::class, 'create']);
    Route::post('/lti-content/create/game', [GameController::class, 'create']);
    Route::post('/lti-content/create/questionset', [QuestionSetController::class, 'create']);
    Route::post('/lti-content/create/h5p', [H5PController::class, 'create']);
    Route::post('/lti-content/create', [H5PController::class, 'create']);
});

Route::get('/slo', [SingleLogoutController::class, 'index'])->name('slo'); // Single logout route

Route::post('/article/create/upload', [ArticleUploadController::class, 'uploadToNewArticle'])->name('article-upload.new');
Route::post('/article/{id}/upload', [ArticleUploadController::class, 'uploadToExistingArticle'])->name('article-upload.existing');


// *************************
// API Endpoints     TODO: clean up!
// *************************
Route::get('v1/link/embeddata', [LinkInfoController::class, 'embed']);

// AJAX and REST(ish) routes
Route::post('api/progress', [Progress::class, 'storeProgress'])->name("setProgress");
Route::get('api/progress', [Progress::class, 'getProgress'])->name("getProgress");

Route::match(['GET', 'POST'], '/ajax', [H5PController::class, 'ajaxLoading'])->middleware("adaptermode"); // TODO: Refactor into its own controller

Route::get('article/{article}/copyright', [ArticleCopyrightController::class, 'copyright'])->name('article.copyright');

Route::get('/health', [HealthController::class, 'index'])->middleware(Sample::never());

// New code should not generate URLs to this, but this endpoint needs to exist
// for backward compatibility.
Route::get('content/assets/{path?}', ContentAssetController::class)
    ->where('path', '.*')
    ->name('content.asset');
