<?php

use App\Http\Controllers\API\ContentInfoController;
use App\Http\Controllers\API\H5PImportController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\EmbedController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\H5PController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\LtiContentController;
use App\Http\Controllers\QuestionSetController;
use Illuminate\Support\Facades\Route;

Route::post('h5p/adapter', function () {
    return ["url" => route('create')];
})->name('h5p.adapter')->middleware('adaptermode');
Route::get('h5p/{h5p}/copyright', 'H5PController@getCopyright');
Route::resource('/h5p', "H5PController", ['except' => ['destroy']]);

Route::get('images/browse', 'H5PController@browseImages');
Route::get('images/browse/{imageId}', 'H5PController@getImage');

Route::get('videos/browse', 'H5PController@browseVideos');
Route::get('videos/browse/{videoId}', 'H5PController@getVideo');

Route::get('audios/browse', 'H5PController@browseAudios');
Route::get('audios/browse/{audioId}', 'H5PController@getAudio');

Route::get('h5p/{h5p}/download', 'H5PController@downloadContent')->name('content-download')->middleware(['adaptermode']);
Route::get('content/upgrade/library', 'H5PController@contentUpgradeLibrary')->name('content-upgrade-library');

Route::group(['middleware' => ['internal.handle-jwt']], function () {
    Route::get('/view', 'InternalController@view');
});

Route::group(['middleware' => ['core.return', 'core.ltiauth', 'core.locale', 'adaptermode']], function () {
    Route::post('lti-content/create', [LtiContentController::class, 'create']);
    Route::post('lti-content/create/{type}', [LtiContentController::class, 'create']);
    Route::post('lti-content/{id}', [LtiContentController::class, 'show'])->middleware(['core.behavior-settings:view']);
    Route::post('lti-content/{id}/edit', [LtiContentController::class, 'edit'])->middleware(['core.ownership','core.behavior-settings:editor','draftaction']);

    Route::post('/h5p/{id}', [H5PController::class, 'ltiShow'])->middleware(['core.behavior-settings:view'])->name('h5p.ltishow');
    Route::post('/game/{id}', [GameController::class, 'ltiShow']);

    Route::post('/link/create', [LinkController::class, 'ltiCreate']);
    Route::post('/link/{id}', [LinkController::class, 'ltiShow']);

    Route::post('/embed/create', [EmbedController::class, 'ltiCreate']);
    Route::post('/embed/{id}', [EmbedController::class, 'ltiShow']);

    Route::post('questionset/create', [QuestionSetController::class, 'ltiCreate']);
    Route::post('questionset/{id}', [QuestionSetController::class,'ltiShow']);
    Route::post('questionsets/image', [QuestionSetController::class, 'setQuestionImage'])->name('set.questionImage');

    Route::post('/article/create', [ArticleController::class, 'ltiCreate'])->middleware(['core.behavior-settings:editor', 'draftaction']);
    Route::post('/article/{id}', [ArticleController::class, 'ltiShow'])->middleware('core.behavior-settings:view');
    Route::post('/article/{id}/edit', [ArticleController::class, 'ltiEdit'])->middleware(['core.behavior-settings:editor','draftaction']);

    Route::get("/h5p/create/{contenttype}", [H5PController::class, 'create'])->name("create.h5pContenttype");

    Route::match(['GET', 'POST'], '/create/{contenttype?}', [ContentController::class, 'index'])->middleware(["core.auth", "lti.question-set", 'core.behavior-settings:editor', 'draftaction'])->name('create');

    Route::resource('questionset', 'QuestionSetController', ['except' => ['destroy']]);
    Route::post('questionset/{id}/edit', [QuestionSetController::class, 'ltiEdit']);

    Route::resource('game', 'GameController', ['except' => ['destroy']]);
    Route::post('game/{id}/edit', [GameController::class, 'ltiEdit']);

    Route::group(['middleware' => ['core.ownership']], function () {
        Route::post('h5p/{id}/edit', [H5PController::class, 'ltiEdit'])->middleware(['core.behavior-settings:editor','draftaction'])->name('h5p.ltiedit');
        Route::post('link/{id}/edit', [LinkController::class, 'ltiEdit']);
    });

});

Route::post('/jwt/update', 'JWTUpdateController@updateJwtEndpoint');

Route::get('/hack/safari', 'SafariHackController@index');
Route::get('/modern/safari/safari', 'SafariHackController@jailBreakDialog');
Route::get('/modern/safari/sessiontest', 'SessionTestHostController@sessionTestPage');

Route::get('/slo', 'SingleLogoutController@index')->name('slo'); // Single logout route

Route::resource('/article', 'ArticleController', ['except' => ['destroy']]);
Route::resource('/link', 'LinkController', ['except' => ['destroy']]);
Route::resource('/embed', 'EmbedController', ['except' => ['destroy']]);

Route::post('/article/create/upload', 'ArticleUploadController@uploadToNewArticle')->name('article-upload.new');
Route::post('/article/{id}/upload', 'ArticleUploadController@uploadToExistingArticle')->name('article-upload.existing');

Route::get('/lti/insert-resource', 'ContentExplorerController@insertResource')->name('lti.insert-resource');
Route::get('/lti/container', 'ContentExplorerController@container')->name('lti.container');
Route::get('/lti/return/{resourceId}', 'ContentExplorerController@returnUrl')->name('lti.return');
Route::get('/lti/launch', 'ContentExplorerController@launch')->name('lti.launch');


// *************************
// API Endpoints     TODO: clean up!
// *************************
Route::get('api/h5p-type/{ids}', 'API\H5PTypeApi@getTypes');

Route::get('v1/content/{id}', [ContentInfoController::class, 'index']);
Route::get('v1/content', [ContentInfoController::class, 'list']);
Route::get('v1/h5p/{id}/info', 'API\H5PInfoController@index');
Route::get('v1/article/{id}/info', 'API\ArticleInfoController@index');
Route::get('v1/link/{id}/info', 'API\LinkInfoController@index');
Route::get('v1/questionset/{id}/info', 'API\QuestionSetInfoController@index');
Route::get('v1/game/{id}/info', 'API\GameInfoController@index');
Route::get('v1/link/embeddata', 'API\LinkInfoController@embed');
Route::get('v1/embed/embedly', 'API\EmbedlyController@get');


Route::get('v1/content/{id}/lock-status', 'API\LockStatusController@index')->name('lock.status');
Route::post('v1/content/{id}/lock-status', 'API\LockStatusController@pulse')->name('lock.status');
Route::match(['GET', 'POST'], 'v1/content/{id}/unlock', 'API\UnlockController@index')->name('lock.unlock');

Route::group(['middleware' => ['core.auth']], function () {
    Route::get('v1/gdpr/user/byemail', 'API\GdprSubjectDataController@getUserDataByEmail')->name('gdpr.user.data.byemail');
    Route::get('v1/gdpr/user/{userId}', 'API\GdprSubjectDataController@getUserData')->name('gdpr.user.data');
});

// AJAX and REST(ish) routes
Route::post('api/progress', 'Progress@storeProgress')->name("setProgress");
Route::get('api/progress', 'Progress@getProgress')->name("getProgress");

Route::match(['GET', 'POST'], '/ajax', [H5PController::class, 'ajaxLoading'])->middleware("adaptermode"); // TODO: Refactor into its own controller

Route::post('v1/sessiontest/{id}', 'API\SessionTestController@setValue');
Route::get('v1/sessiontest/{id}', 'API\SessionTestController@getValue');

Route::group(['prefix' => 'api', 'middleware' => ['signed.oauth10-request']], function () {
    Route::post('v1/contenttypes/questionsets', 'API\ContentTypeController@storeH5PQuestionset');
    Route::put('v1/resources/{resourceId}/publish', 'API\PublishResourceController@publishResource')->name('api.resource.publish');
    Route::post('v1/h5p/import', [H5PImportController::class, 'importH5P'])->name('api.import.h5p');
});

Route::get('article/{article}/copyright', 'ArticleCopyrightController@copyright')->name('article.copyright');

Route::get('/health', 'HealthController@index');

Route::get('content/assets/{path?}', 'ContentAssetController')->where('path', '.*')->name('content.asset')->middleware('adaptermode');
