<?php
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
    Route::post('lti-content/create', 'LtiContentController@create');
    Route::post('lti-content/create/{type}', 'LtiContentController@create');
    Route::post('lti-content/{id}', 'LtiContentController@show')->middleware(['core.behavior-settings:view']);
    Route::post('lti-content/{id}/edit', 'LtiContentController@edit')->middleware(['core.ownership','core.behavior-settings:editor','draftaction']);

    Route::post('/h5p/{id}', 'H5PController@ltiShow')->middleware(['core.behavior-settings:view'])->name('h5p.ltishow');
    Route::post('/game/{id}', 'GameController@ltiShow');

    Route::post('/link/create', 'LinkController@ltiCreate');
    Route::post('/link/{id}', 'LinkController@ltiShow');

    Route::post('/embed/create', 'EmbedController@ltiCreate');
    Route::post('/embed/{id}', 'EmbedController@ltiShow');

    Route::post('questionset/create', 'QuestionSetController@ltiCreate');
    Route::post('questionset/{id}', 'QuestionSetController@ltiShow');
    Route::post('questionsets/image', 'QuestionSetController@setQuestionImage')->name('set.questionImage');

    Route::post('/article/create', 'ArticleController@ltiCreate')->middleware(['core.behavior-settings:editor','draftaction']);
    Route::post('/article/{id}', 'ArticleController@ltiShow')->middleware('core.behavior-settings:view');
    Route::post('/article/{id}/edit', 'ArticleController@ltiEdit')->middleware(['core.behavior-settings:editor','draftaction']);

    Route::get("/h5p/create/{contenttype}", 'H5PController@create')->name("create.h5pContenttype");

    Route::match(['GET', 'POST'], '/create/{contenttype?}', 'ContentController@index')->middleware(["core.auth", "lti.question-set", 'core.behavior-settings:editor','draftaction'])->name('create');

    Route::resource('questionset', 'QuestionSetController', ['except' => ['destroy']]);
    Route::post('questionset/{id}/edit', 'QuestionSetController@ltiEdit');

    Route::resource('game', 'GameController', ['except' => ['destroy']]);
    Route::post('game/{id}/edit', 'GameController@ltiEdit');

    Route::group(['middleware' => ['core.ownership']], function () {
        Route::post('h5p/{id}/edit', 'H5PController@ltiEdit')->middleware(['core.behavior-settings:editor','draftaction'])->name('h5p.ltiedit');
        Route::post('link/{id}/edit', 'LinkController@ltiEdit');
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

Route::get('v1/content/{id}', 'API\ContentInfoController@index');
Route::get('v1/content', 'API\ContentInfoController@list');
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

// Faked Import API
Route::match(['GET', 'POST'], 'api/v1/import', 'ImportController@import');

// AJAX and REST(ish) routes
Route::post('api/progress', 'Progress@storeProgress')->name("setProgress");
Route::get('api/progress', 'Progress@getProgress')->name("getProgress");

Route::match(['GET', 'POST'], '/ajax', 'H5PController@ajaxLoading')->middleware("adaptermode"); // TODO: Refactor into its own controller

Route::post('v1/sessiontest/{id}', 'API\SessionTestController@setValue');
Route::get('v1/sessiontest/{id}', 'API\SessionTestController@getValue');

Route::group(['prefix' => 'api', 'middleware' => ['oauth']], function () {
    Route::post('v1/questionsandanswers', 'API\H5PReportController@questionAndAnswer');
    Route::get('v1/resourcelicense/{id}', 'API\H5PReportController@resourceLicense');
});

Route::group(['prefix' => 'api', 'middleware' => ['signed.oauth10-request']], function () {
    Route::post('v1/contenttypes/questionsets', 'API\ContentTypeController@storeH5PQuestionset');
    Route::put('v1/resources/{resourceId}/publish', 'API\PublishResourceController@publishResource')->name('api.resource.publish');
    Route::post('v1/h5p/import', 'API\H5PImportController@importH5P')->name('api.import.h5p');
});

Route::post('v1/copy', 'API\ContentCopyController@index')->name('content.copy')->middleware('signed.oauth10-request');

Route::get('h5p/{h5p}/tags', 'ContentTagController@fetchH5PTags')->name('h5p.tags');
Route::get('article/{article}/tags', 'ContentTagController@fetchArticleTags')->name('article.tags');
Route::get('game/{game}/tags', 'ContentTagController@fetchGameTags')->name('game.tags');
Route::get('link/{link}/tags', 'ContentTagController@fetchLinkTags')->name('link.tags');

Route::get('article/{article}/copyright', 'ArticleCopyrightController@copyright')->name('article.copyright');

Route::get('/health', 'HealthController@index');

Route::get('content/assets/{path?}', 'ContentAssetController')->where('path', '.*')->name('content.asset')->middleware('adaptermode');
