<?php

/**
 * Admin routes
 */

use App\Http\Controllers\Admin\AdminArticleController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminH5PDetailsController;
use App\Http\Controllers\Admin\CapabilityController;
use App\Http\Controllers\Admin\ContentUpgradeController;
use App\Http\Controllers\Admin\GamesAdminController;
use App\Http\Controllers\Admin\LibraryUpgradeController;
use App\Http\Controllers\Admin\LocksController;
use App\Http\Controllers\Admin\LtiAdminAccess;
use App\Http\Controllers\Admin\PresaveController;
use App\Http\Controllers\Admin\VersioningController;
use App\Http\Controllers\Auth\LogoutController;
use Illuminate\Support\Facades\Route;

Route::post('auth/logout', LogoutController::class)->name('logout');

Route::post('/lti/admin', LtiAdminAccess::class)
    ->middleware(['lti.add-to-session', 'lti.signed-launch']);

Route::middleware(['auth:sso', 'can:superadmin'])->prefix('admin')->group(
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

        /*
         * Admin library and content details
         */
        Route::get('libraries/{library}/check', [AdminH5PDetailsController::class, 'checkLibrary'])
            ->name('admin.check-library');
        Route::get('libraries/{library}/content', [AdminH5PDetailsController::class, 'contentForLibrary'])
            ->name('admin.content-library');
        Route::get('content/{content}/details/{version?}', [AdminH5PDetailsController::class, 'contentHistory'])
            ->name('admin.content-details');
        Route::get('libraries/{library}/translation/{locale}', [AdminH5PDetailsController::class, 'libraryTranslation'])
            ->name('admin.library-translation');
        Route::post('libraries/{library}/translation/{locale}', [AdminH5PDetailsController::class, 'libraryTranslationUpdate']);

        Route::get('libraries/{library}', [ContentUpgradeController::class, 'upgrade'])->name('admin.library');

        Route::post('content/upgrade', [AdminController::class, 'contentUpgrade'])->name('admin.content-upgrade');

        Route::match(['GET', 'POST'], 'ajax', [AdminController::class, 'ajaxLoading'])->name('admin.ajax');

        /*
         * Capabilities
         */
        Route::get('/capability', [CapabilityController::class, 'index'])->name('admin.capability');
        Route::get('/capability/refresh', [CapabilityController::class, 'refresh'])->name('admin.capability.refresh');
        Route::post('/capability/{capability}/enable', [CapabilityController::class, 'enable'])->name('admin.capability.enabled');
        Route::post('/capability/{capability}/score', [CapabilityController::class, 'score'])->name('admin.capability.score');
        Route::post(
            '/capability/{capability}/description',
            [CapabilityController::class, 'description'],
        )->name('admin.capability.description');
        Route::get(
            '/capability/{capability}/translation',
            [CapabilityController::class, 'translation'],
        )->name('admin.capability.translation');

        Route::get('games', [GamesAdminController::class, 'index'])->name('admin.games');
        Route::post('games', [GamesAdminController::class, 'store'])->name('admin.games.store');

        Route::get('maxscore', [AdminController::class, 'viewMaxScoreOverview'])->name('admin.maxscore.list');
        Route::post('maxscore', [AdminController::class, 'updateMaxScore'])->name('admin.maxscore.update');
        Route::get('maxscore/failed', [AdminController::class, 'viewFailedCalculations'])->name('admin.maxscore.failed');

        Route::get('article/maxscore', [AdminArticleController::class, 'index'])->name('admin.article.maxscore.list');
        Route::post('article/maxscore', [AdminArticleController::class, 'updateMaxScore'])->name('admin.article.maxscore.update');
        Route::get('article/maxscore/log', [AdminArticleController::class, 'download'])->name('admin.article.maxscore.download');
        Route::get('article/maxscore/failed', [AdminArticleController::class, 'viewFailedCalculations'])->name('admin.article.maxscore.failed');

        Route::get('presave', [PresaveController::class, 'index'])->name('admin.presave.index');
        Route::post('presave/run-presave', [PresaveController::class, 'runPresave'])->name('admin.presave.run-presave');

        // More general Admin Backend routes
        Route::get('support/versioning', [VersioningController::class, 'index'])->name('admin.support.versioning');

        // Locks admin
        Route::get("locks", [LocksController::class, 'index'])->name("admin.locks");
        Route::delete("locks", [LocksController::class, 'destroy'])->name("admin.locks.delete");
    },
);
