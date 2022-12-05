<?php

use App\Http\Controllers\API\ArticleInfoController;
use App\Http\Controllers\API\ContentInfoController;
use App\Http\Controllers\API\ContentTypeController;
use App\Http\Controllers\API\EmbedlyController;
use App\Http\Controllers\API\GameInfoController;
use App\Http\Controllers\API\GdprSubjectDataController;
use App\Http\Controllers\API\H5PImportController;
use App\Http\Controllers\API\H5PInfoController;
use App\Http\Controllers\API\H5PTypeApi;
use App\Http\Controllers\API\LinkInfoController;
use App\Http\Controllers\API\LockStatusController;
use App\Http\Controllers\API\PublishResourceController;
use App\Http\Controllers\API\QuestionSetInfoController;
use App\Http\Controllers\API\UnlockController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ArticleCopyrightController;
use App\Http\Controllers\ArticleUploadController;
use App\Http\Controllers\ContentAssetController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\EmbedController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\H5PController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\InternalController;
use App\Http\Controllers\JWTUpdateController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\LtiContentController;
use App\Http\Controllers\Progress;
use App\Http\Controllers\QuestionSetController;
use App\Http\Controllers\SingleLogoutController;
use Illuminate\Support\Facades\Route;

Route::post('h5p/adapter', function () {
    return ["url" => route('create')];
})->name('h5p.adapter')->middleware('adaptermode');
Route::get('h5p/{h5p}/copyright', [H5PController::class, 'getCopyright']);
Route::resource('/h5p', H5PController::class, ['except' => ['destroy']]);

Route::get('images/browse', [H5PController::class, 'browseImages']);
Route::get('images/browse/{imageId}', [H5PController::class, 'getImage']);

Route::get('videos/browse', [H5PController::class, 'browseVideos']);
Route::get('videos/browse/{videoId}', [H5PController::class, 'getVideo']);

Route::get('audios/browse', [H5PController::class, 'browseAudios']);
Route::get('audios/browse/{audioId}', [H5PController::class, 'getAudio']);

Route::get('h5p/{h5p}/download', [H5PController::class, 'downloadContent'])->name('content-download')->middleware(['adaptermode']);
Route::get('content/upgrade/library', [H5PController::class, 'contentUpgradeLibrary'])->name('content-upgrade-library');

Route::group(['middleware' => ['internal.handle-jwt']], function () {
    Route::get('/view', [InternalController::class, 'view']);
});

Route::group(['middleware' => ['core.return', 'core.ltiauth', 'core.locale', 'adaptermode']], function () {
    Route::post('lti-content/create', [LtiContentController::class, 'create']);
    Route::post('lti-content/create/{type}', [LtiContentController::class, 'create']);
    Route::post('lti-content/{id}', [LtiContentController::class, 'show'])->middleware(['core.behavior-settings:view']);
    Route::post('lti-content/{id}/edit', [LtiContentController::class, 'edit'])->middleware(['core.ownership','core.behavior-settings:editor']);

    Route::post('/h5p/{id}', [H5PController::class, 'ltiShow'])->middleware(['core.behavior-settings:view'])->name('h5p.ltishow');
    Route::post('/game/{id}', [GameController::class, 'ltiShow']);

    Route::post('/link/create', [LinkController::class, 'ltiCreate']);
    Route::post('/link/{id}', [LinkController::class, 'ltiShow']);

    Route::post('/embed/create', [EmbedController::class, 'ltiCreate']);
    Route::post('/embed/{id}', [EmbedController::class, 'ltiShow']);

    Route::post('questionset/create', [QuestionSetController::class, 'ltiCreate']);
    Route::post('questionset/{id}', [QuestionSetController::class,'ltiShow']);
    Route::post('questionsets/image', [QuestionSetController::class, 'setQuestionImage'])->name('set.questionImage');

    Route::post('/article/create', [ArticleController::class, 'ltiCreate'])->middleware(['core.behavior-settings:editor']);
    Route::post('/article/{id}', [ArticleController::class, 'ltiShow'])->middleware('core.behavior-settings:view');
    Route::post('/article/{id}/edit', [ArticleController::class, 'ltiEdit'])->middleware(['core.behavior-settings:editor']);

    Route::get("/h5p/create/{contenttype}", [H5PController::class, 'create'])->name("create.h5pContenttype");

    Route::match(['GET', 'POST'], '/create/{contenttype?}', [ContentController::class, 'index'])->middleware(["core.auth", "lti.question-set", 'core.behavior-settings:editor'])->name('create');

    Route::resource('questionset', QuestionSetController::class, ['except' => ['destroy']]);
    Route::post('questionset/{id}/edit', [QuestionSetController::class, 'ltiEdit']);

    Route::resource('game', GameController::class, ['except' => ['destroy']]);
    Route::post('game/{id}/edit', [GameController::class, 'ltiEdit']);

    Route::group(['middleware' => ['core.ownership']], function () {
        Route::post('h5p/{id}/edit', [H5PController::class, 'ltiEdit'])->middleware(['core.behavior-settings:editor'])->name('h5p.ltiedit');
        Route::post('link/{id}/edit', [LinkController::class, 'ltiEdit']);
    });
});

Route::post('/jwt/update', [JWTUpdateController::class, 'updateJwtEndpoint']);

Route::get('/slo', [SingleLogoutController::class, 'index'])->name('slo'); // Single logout route

Route::resource('/article', ArticleController::class, ['except' => ['destroy']]);
Route::resource('/link', LinkController::class, ['except' => ['destroy']]);
Route::resource('/embed', EmbedController::class, ['except' => ['destroy']]);

Route::post('/article/create/upload', [ArticleUploadController::class, 'uploadToNewArticle'])->name('article-upload.new');
Route::post('/article/{id}/upload', [ArticleUploadController::class, 'uploadToExistingArticle'])->name('article-upload.existing');


// *************************
// API Endpoints     TODO: clean up!
// *************************
Route::get('api/h5p-type/{ids}', [H5PTypeApi::class, 'getTypes']);

Route::get('v1/content/{id}', [ContentInfoController::class, 'index']);
Route::get('v1/content', [ContentInfoController::class, 'list']);
Route::get('v1/h5p/{id}/info', [H5PInfoController::class, 'index']);
Route::get('v1/article/{id}/info', [ArticleInfoController::class, 'index']);
Route::get('v1/link/{id}/info', [LinkInfoController::class, 'index']);
Route::get('v1/questionset/{id}/info', [QuestionSetInfoController::class, 'index']);
Route::get('v1/game/{id}/info', [GameInfoController::class, 'index']);
Route::get('v1/link/embeddata', [LinkInfoController::class, 'embed']);
Route::get('v1/embed/embedly', [EmbedlyController::class, 'get']);


Route::get('v1/content/{id}/lock-status', [LockStatusController::class, 'index'])->name('lock.status');
Route::post('v1/content/{id}/lock-status', [LockStatusController::class, 'pulse'])->name('lock.status');
Route::match(['GET', 'POST'], 'v1/content/{id}/unlock', [UnlockController::class, 'index'])->name('lock.unlock');

Route::group(['middleware' => ['core.auth']], function () {
    Route::get('v1/gdpr/user/byemail', [GdprSubjectDataController::class, 'getUserDataByEmail'])->name('gdpr.user.data.byemail');
    Route::get('v1/gdpr/user/{userId}', [GdprSubjectDataController::class, 'getUserData'])->name('gdpr.user.data');
});

// AJAX and REST(ish) routes
Route::post('api/progress', [Progress::class, 'storeProgress'])->name("setProgress");
Route::get('api/progress', [Progress::class, 'getProgress'])->name("getProgress");

Route::match(['GET', 'POST'], '/ajax', [H5PController::class, 'ajaxLoading'])->middleware("adaptermode"); // TODO: Refactor into its own controller

Route::group(['prefix' => 'api', 'middleware' => ['signed.oauth10-request']], function () {
    Route::post('v1/contenttypes/questionsets', [ContentTypeController::class, 'storeH5PQuestionset']);
    Route::put('v1/resources/{resourceId}/publish', [PublishResourceController::class, 'publishResource'])->name('api.resource.publish');
    Route::post('v1/h5p/import', [H5PImportController::class, 'importH5P'])->name('api.import.h5p');
});

Route::get('article/{article}/copyright', [ArticleCopyrightController::class, 'copyright'])->name('article.copyright');

Route::get('/health', [HealthController::class, 'index']);

Route::get('content/assets/{path?}', ContentAssetController::class)->where('path', '.*')->name('content.asset')->middleware('adaptermode');
