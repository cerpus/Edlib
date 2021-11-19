<?php
/**
 * Admin routes
 */

use App\Http\Controllers\Admin\LocksController;
use App\Http\Controllers\Admin\RecommendationEngineController;

Route::get('auth/login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('auth/login', 'Auth\LoginController@login');
Route::post('auth/logout', 'Auth\LoginController@logout')->name('logout');

Route::middleware('auth')->namespace('Admin')->prefix('admin')->group(
    function () {
        Route::get('/', 'AdminController@index')->name('admin');

        /*
         * Update H5P libraries
         */
        Route::match(['get', 'post'], '/update-libraries',
            'LibraryUpgradeController@index')->name('admin.update-libraries');
        Route::post('/check-for-updates', 'LibraryUpgradeController@checkForUpdates')
            ->name('admin.check-for-updates');
        Route::post('/update-library', 'LibraryUpgradeController@upgradeLibrary')
            ->name('admin.upgrade-library');
        Route::get('libraries/{libraryId}', 'ContentUpgradeController@upgrade')->name('admin.library');

        Route::post('content/upgrade', 'AdminController@contentUpgrade')->name('admin.content-upgrade');

        Route::match(['GET', 'POST'], 'ajax', 'AdminController@ajaxLoading')->name('admin.ajax');

        /*
         * Capabilities
         */
        Route::get('/capability', 'CapabilityController@index')->name('admin.capability');
        Route::get('/capability/refresh', 'CapabilityController@refresh')->name('admin.capability.refresh');
        Route::post('/capability/{capability}/enable', 'CapabilityController@enable')->name('admin.capability.enabled');
        Route::post('/capability/{capability}/score', 'CapabilityController@score')->name('admin.capability.score');
        Route::post('/capability/{capability}/description',
            'CapabilityController@description')->name('admin.capability.description');
        Route::get('/capability/{capability}/translation',
            'CapabilityController@translation')->name('admin.capability.translation');

        Route::get('games', 'GamesAdminController@index')->name('admin.games');
        Route::post('games', 'GamesAdminController@store')->name('admin.games.store');

        Route::get('maxscore', 'AdminController@viewMaxScoreOverview')->name('admin.maxscore.list');
        Route::post('maxscore', 'AdminController@updateMaxScore')->name('admin.maxscore.update');
        Route::get('maxscore/presave/{library}', function ($library) {
            if (\App\H5PLibrary::where('name', urlencode($library))->exists()) {
                $disk = Storage::disk('h5p');
                $location = sprintf('Presave/%s/presave.js', $library);
                if ($disk->exists($location)) {
                    return $disk->read($location);
                }
            }
        })->name('admin.maxscore.presave');
        Route::get('maxscore/failed', 'AdminController@viewFailedCalculations')->name('admin.maxscore.failed');

        Route::get('article/maxscore', 'AdminArticleController@index')->name('admin.article.maxscore.list');
        Route::post('article/maxscore', 'AdminArticleController@updateMaxScore')->name('admin.article.maxscore.update');
        Route::get('article/maxscore/log', 'AdminArticleController@download')->name('admin.article.maxscore.download');
        Route::get('article/maxscore/failed', 'AdminArticleController@viewFailedCalculations')->name('admin.article.maxscore.failed');

        // Article import
        Route::get('ndla-article-import', 'NDLAArticleImportController@index')->name('admin.ndla.index');
        Route::get('ndla-article-import/status', 'NDLAArticleImportController@status')->name('admin.ndla.status');
        Route::match(['get', 'post'], 'ndla-article-import/status/search', 'NDLAArticleImportController@searchImportStatus')->name('admin.ndla.status.search');
        Route::post('ndla-article-import/all', 'NDLAArticleImportController@all')->name('admin.ndla.all');
        Route::get('ndla-article-import/refresh', 'NDLAArticleImportController@refresh')->name('admin.ndla.refresh');
        Route::match(['get', 'post'], 'ndla-article-import/search', 'NDLAArticleImportController@search')->name('admin.ndla.search');
        Route::post('ndla-article-import', 'NDLAArticleImportController@store')->name('admin.ndla.multi-import');
        Route::get('ndla-article-import/{id}', 'NDLAArticleImportController@show')->name('admin.ndla.show');
        Route::get('ndla-article-import/{id}/import', 'NDLAArticleImportController@import')->name('admin.ndla.import');
        Route::get('ndla-article-import/{id}/delete', 'NDLAArticleImportController@destroy')->name('admin.ndla.delete');

        // Learning path import
        Route::get('ndla-learning-path-import', 'NDLALearningPathImportController@index')->name('admin.learningpath.index');
        Route::get('ndla-learning-path-import/sync', 'NDLALearningPathImportController@sync')->name('admin.learningpath.sync');
        Route::get('ndla-learning-path-import/{id}', 'NDLALearningPathImportController@show')->name('admin.learningpath.show');

        // Course import / export
        Route::get('ndla-course-import', 'NDLACourseImportController@index')->name('admin.courseimport.index');
        Route::get('ndla-course-import/{subjectId}/preview', 'NDLACourseImportController@subjectPreview')->name('admin.courseimport.subject-preview');
        Route::get('ndla-course-import/{subjectId}/{topicId}/export', 'NDLACourseImportController@export')->name('admin.courseimport.export');
        Route::get('ndla-course-import/{subjectId}/{topicId}/article-import', 'NDLACourseImportController@articleImport')->name('admin.courseimport.article.import');


        Route::get('/metadata/authors', 'NDLAMetadataImportController@index')->name('admin.metadata.index');
        Route::post('/metadata/authors', 'NDLAMetadataImportController@migrate')->name('admin.metadata.migrate');
        Route::get('/metadata/log', 'NDLAMetadataImportController@download')->name('admin.metadata.download');

        // Settings for import export
        Route::get('ndla-import-export/settings', "ImportExportSettingsController@index")->name('admin.importexport.index');
        Route::post('ndla-import-export/settings/reset-tracking', "ImportExportSettingsController@resetTracking")->name('admin.importexport.reset-tracking');
        Route::post('ndla-import-export/settings/empty-article-import-log', "ImportExportSettingsController@emptyArticleImportLog")->name('admin.importexport.empty-article-import-log');
        Route::post('ndla-import-export/settings/run-presave', "ImportExportSettingsController@runPresave")->name('admin.importexport.run-presave');

        Route::get('norgesfilm', 'NorgesfilmController@index')->name('admin.norgesfilm.index');
        Route::get('norgesfilm/populate', 'NorgesfilmController@populate')->name('admin.norgesfilm.populate');
        Route::get('norgesfilm/{norgesfilm}/compare', 'NorgesfilmController@compare')->name('admin.norgesfilm.compare');
        Route::get('norgesfilm/{id}/replace', 'NorgesfilmController@replace')->name('admin.norgesfilm.replace');
        Route::get('norgesfilm/ndla-url-not-found', 'NorgesfilmController@ndlaUrlNotFound')->name('admin.norgesfilm.ndla-url-not-found');

        // More general Admin Backend routes
        Route::get('logs', "AdminController@logs")->name('admin.logs');
        Route::get('system-info', "AdminController@systemInfo")->name('admin.system-info');

        Route::resource('admin-users', 'AdminUserController')->only(['index', 'store', 'destroy']);

        Route::get('support/versioning', 'VersioningController@index')->name('admin.support.versioning');

        // Locks admin
        Route::get("locks", [LocksController::class, 'index'])->name("admin.locks");
        Route::delete("locks", [LocksController::class, 'destroy'])->name("admin.locks.delete");

        // Refs
        Route::get("video/ndla/replace", "NDLAReplaceRefController@index")->name("admin.video.ndla.replaceref");
        Route::get("video/ndla/doreplaceref", "NDLAReplaceRefController@doReplaceRef")->name("admin.video.ndla.doreplaceref");
        Route::get("video/ndla/populatetargets", "NDLAReplaceRefController@populateTable")->name("admin.video.ndla.populatetargets");
        Route::get("video/ndla/reindexrefs", "NDLAReplaceRefController@reindex")->name("admin.video.ndla.reindexrefs");

        // Recommendation engine
        Route::get("recommendation-engine", [RecommendationEngineController::class, "index"])->name("admin.recommendation-engine.index");
        Route::get("recommendation-engine/doIndex", [RecommendationEngineController::class, "doIndex"])->name("admin.recommendation-engine.doIndex");
        Route::get("recommendation-engine/index-ndla-articles", [RecommendationEngineController::class, "indexNdlaArticles"])->name("admin.recommendation-engine.index-ndla-articles");
        Route::get("recommendation-engine/search" , [RecommendationEngineController::class, "search"])->name("admin.recommendation-engine.search");
        Route::get("recommendation-engine/{id}/remove/{query}" , [RecommendationEngineController::class, "remove"])->name("admin.recommendation-engine.remove");
    }
);
