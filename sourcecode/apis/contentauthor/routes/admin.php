<?php
/**
 * Admin routes
 */

use App\Http\Controllers\Admin\AdminArticleController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\CapabilityController;
use App\Http\Controllers\Admin\ContentUpgradeController;
use App\Http\Controllers\Admin\GamesAdminController;
use App\Http\Controllers\Admin\ImportExportSettingsController;
use App\Http\Controllers\Admin\LibraryUpgradeController;
use App\Http\Controllers\Admin\LocksController;
use App\Http\Controllers\Admin\NDLAReplaceRefController;
use App\Http\Controllers\Admin\VersioningController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('auth/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::get('sso-edlib-admin', [LoginController::class, 'ssoFromEdlibAdmin'])->middleware('edlib.parse-jwt', 'edlib.auth:superadmin');
Route::post('auth/login', [LoginController::class, 'login']);
Route::post('auth/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('edlib.auth:superadmin')->namespace('Admin')->prefix('admin')->group(
    function () {
        Route::get('/', [AdminController::class, 'index'])->name('admin');

        /*
         * Update H5P libraries
         */
        Route::get('/update-libraries', [LibraryUpgradeController::class, 'index'])
            ->name('admin.update-libraries');
        Route::post('/update-libraries', [LibraryUpgradeController::class, 'upgrade']);
        Route::post('/check-for-updates', [LibraryUpgradeController::class, 'checkForUpdates'])
            ->name('admin.check-for-updates');
        Route::post('/update-library', [LibraryUpgradeController::class, 'upgradeLibrary'])
            ->name('admin.upgrade-library');
        Route::delete('libraries/{library}', [LibraryUpgradeController::class, 'deleteLibrary'])
            ->name('admin.delete-library');

        Route::get('libraries/{libraryId}', [ContentUpgradeController::class, 'upgrade'])->name('admin.library');

        Route::post('content/upgrade', [AdminController::class, 'contentUpgrade'])->name('admin.content-upgrade');

        Route::match(['GET', 'POST'], 'ajax', [AdminController::class, 'ajaxLoading'])->name('admin.ajax');

        /*
         * Capabilities
         */
        Route::get('/capability', [CapabilityController::class, 'index'])->name('admin.capability');
        Route::get('/capability/refresh', [CapabilityController::class, 'refresh'])->name('admin.capability.refresh');
        Route::post('/capability/{capability}/enable', [CapabilityController::class, 'enable'])->name('admin.capability.enabled');
        Route::post('/capability/{capability}/score', [CapabilityController::class, 'score'])->name('admin.capability.score');
        Route::post('/capability/{capability}/description',
            [CapabilityController::class, 'description'])->name('admin.capability.description');
        Route::get('/capability/{capability}/translation',
            [CapabilityController::class, 'translation'])->name('admin.capability.translation');

        Route::get('games', [GamesAdminController::class, 'index'])->name('admin.games');
        Route::post('games', [GamesAdminController::class, 'store'])->name('admin.games.store');

        Route::get('maxscore', [AdminController::class, 'viewMaxScoreOverview'])->name('admin.maxscore.list');
        Route::post('maxscore', [AdminController::class, 'updateMaxScore'])->name('admin.maxscore.update');
        Route::get('maxscore/failed', [AdminController::class, 'viewFailedCalculations'])->name('admin.maxscore.failed');

        Route::get('article/maxscore', [AdminArticleController::class, 'index'])->name('admin.article.maxscore.list');
        Route::post('article/maxscore', [AdminArticleController::class, 'updateMaxScore'])->name('admin.article.maxscore.update');
        Route::get('article/maxscore/log', [AdminArticleController::class, 'download'])->name('admin.article.maxscore.download');
        Route::get('article/maxscore/failed', [AdminArticleController::class, 'viewFailedCalculations'])->name('admin.article.maxscore.failed');

        // Settings for import export
        Route::get('ndla-import-export/settings', [ImportExportSettingsController::class, 'index'])->name('admin.importexport.index');
        Route::post('ndla-import-export/settings/reset-tracking', [ImportExportSettingsController::class, 'resetTracking'])->name('admin.importexport.reset-tracking');
        Route::post('ndla-import-export/settings/empty-article-import-log', [ImportExportSettingsController::class, 'emptyArticleImportLog'])->name('admin.importexport.empty-article-import-log');
        Route::post('ndla-import-export/settings/run-presave', [ImportExportSettingsController::class, 'runPresave'])->name('admin.importexport.run-presave');

        // More general Admin Backend routes
        Route::resource('admin-users', 'AdminUserController')->only(['index', 'store', 'destroy']);

        Route::get('support/versioning', [VersioningController::class, 'index'])->name('admin.support.versioning');

        // Locks admin
        Route::get("locks", [LocksController::class, 'index'])->name("admin.locks");
        Route::delete("locks", [LocksController::class, 'destroy'])->name("admin.locks.delete");

        // Refs
        Route::get("video/ndla/replace", [NDLAReplaceRefController::class, 'index'])->name("admin.video.ndla.replaceref");
        Route::get("video/ndla/doreplaceref", [NDLAReplaceRefController::class, 'doReplaceRef'])->name("admin.video.ndla.doreplaceref");
        Route::get("video/ndla/populatetargets", [NDLAReplaceRefController::class, 'populateTable'])->name("admin.video.ndla.populatetargets");
        Route::get("video/ndla/reindexrefs", [NDLAReplaceRefController::class, 'reindex'])->name("admin.video.ndla.reindexrefs");
    }
);
