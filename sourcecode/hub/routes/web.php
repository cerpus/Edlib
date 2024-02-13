<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\LtiPlatformController;
use App\Http\Controllers\Admin\LtiToolController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\CookieController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LtiController;
use App\Http\Controllers\SocialController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\EnsureFrameCookies;
use App\Http\Middleware\LtiValidatedRequest;
use App\Http\Middleware\StartScopedLtiSession;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/')
    ->uses(HomeController::class)
    ->name('home');

Route::middleware('can:login')->group(function () {
    Route::get('/login')
        ->uses([LoginController::class, 'login'])
        ->name('login');

    Route::post('/login')
        ->uses([LoginController::class, 'check'])
        ->name('login_check');
});

Route::post('/log-out')
    ->uses([LoginController::class, 'logout'])
    ->name('log_out');

Route::controller(ContentController::class)->group(function () {
    Route::get('/content', 'index')->name('content.index');

    Route::get('/content/mine', 'mine')
        ->middleware('auth')
        ->name('content.mine');

    Route::post('/content/toggle', 'layoutSwitch')
        ->name('content.layout');

    Route::get('/content/{content}', 'details')
        ->name('content.details')
        ->whereUlid('content')
        ->can('view', 'content');

    Route::get('/c/{content}', 'share')
        ->uses([ContentController::class, 'share'])
        ->name('content.share')
        ->whereUlid('content')
        ->can('view', 'content');

    Route::get('/content/{content}/version/{version}')
        ->uses([ContentController::class, 'version'])
        ->name('content.version-details')
        ->can('view', ['content', 'version'])
        ->whereUlid(['content', 'version'])
        ->scopeBindings();

    Route::get('/content/{content}/embed')
        ->uses([ContentController::class, 'embed'])
        ->name('content.embed')
        ->can('view', 'content')
        ->whereUlid('content');

    Route::get('/content/create', 'create')
        ->can('create', \App\Models\Content::class)
        ->name('content.create');

    Route::post('/content/{content}/copy', 'copy')
        ->can('copy', 'content')
        ->name('content.copy');

    Route::get('/content/{content}/edit', 'edit')
        ->name('content.edit')
        ->can('edit', 'content')
        ->whereUlid('content');

    Route::post('/content/{content}/use')
        ->uses([ContentController::class, 'use'])
        ->name('content.use')
        ->can('use', 'content')
        ->whereUlid('content');

    Route::get('/content/create/{tool}', 'launchCreator')
        ->name('content.launch-creator')
        ->can('create', \App\Models\Content::class)
        ->whereUlid('tool');
});

Route::prefix('/lti/dl')->middleware([
    LtiValidatedRequest::class . ':tool',
    'lti.launch-type:ContentItemSelection',
])->group(function () {
    Route::post('/tool/{tool}/content/create')
        ->uses([ContentController::class, 'ltiStore'])
        ->name('content.lti-store')
        ->can('create', \App\Models\Content::class)
        ->whereUlid('tool');

    Route::post('/tool/{tool}/content/{content}/update')
        ->uses([ContentController::class, 'ltiUpdate'])
        ->name('content.lti-update')
        ->can('edit', 'content')
        ->whereUlid(['tool', 'content']);
});

Route::prefix('/lti')->middleware([
    EnsureFrameCookies::class,
    LtiValidatedRequest::class . ':platform',
    StartScopedLtiSession::class,
])->group(function () {
    Route::post('/content/{content}')
        ->uses([LtiController::class, 'content'])
        ->name('lti.content')
        ->can('view', 'content')
        ->whereUlid('content')
        ->middleware('lti.launch-type:basic-lti-launch-request');

    Route::post('/dl')
        ->uses([LtiController::class, 'select'])
        ->name('lti.select')
        ->middleware('lti.launch-type:ContentItemSelectionRequest');

    // Deprecated: use /lti/dl instead.
    Route::post('/1.1/select')
        ->uses([LtiController::class, 'select'])
        ->middleware('lti.launch-type:ContentItemSelectionRequest');
});

Route::controller(UserController::class)->group(function () {
    Route::middleware('can:register')->group(function () {
        Route::get('/register', 'register')->name('register');
        Route::post('/register', 'store');
    });

    Route::middleware('can:reset-password')->group(function () {
        Route::get('/forgot-password', 'showForgotPasswordForm')->name('forgot-password');
        Route::post('/forgot-password', 'sendResetLink')->name('forgot-password-send');

        Route::get('/reset-password/{user:password_reset_token}')
            ->uses([UserController::class, 'showResetPasswordForm'])
            ->name('reset-password');

        Route::post('/reset-password/{user:password_reset_token}')
            ->uses([UserController::class, 'resetPassword'])
            ->name('reset-password-update');
    });

    Route::middleware('auth:web')->group(function () {
        Route::get('/preferences', 'preferences')->name('user.preferences');
        Route::post('/preferences', 'savePreferences')->name('user.save-preferences');

        Route::get('/my-account', 'myAccount')->name('user.my-account');
        Route::post('/update-account', 'updateAccount')->name('user.update-account');

        Route::post('/disconnect-social-accounts', 'disconnectSocialAccounts')->name('user.disconnect-social-accounts');
    });
});

Route::middleware('can:admin')->prefix('/admin')->group(function () {
    Route::get('', [AdminController::class, 'index'])->name('admin.index');

    Route::post('/rebuild-content-index', [AdminController::class, 'rebuildContentIndex'])
        ->name('admin.rebuild-content-index');

    Route::prefix('/lti-platforms')->controller(LtiPlatformController::class)->group(function () {
        Route::get('', 'index')->name('admin.lti-platforms.index');
        Route::post('', 'store')->name('admin.lti-platforms.store');
    });

    Route::prefix('/lti-tools')->controller(LtiToolController::class)->group(function () {
        Route::get('', 'index')->name('admin.lti-tools.index');
        Route::get('/add', 'add')->name('admin.lti-tools.add');
        Route::post('', 'store')->name('admin.lti-tools.store');
        Route::delete('/{tool}', 'destroy')
            ->name('admin.lti-tools.remove')
            ->whereUlid('tool');
    });
});

Route::prefix('/{provider}')
    ->name('social.')
    ->whereIn('provider', User::SOCIAL_PROVIDERS)
    ->group(function () {
        Route::get('/login')
            ->uses([SocialController::class, 'login'])
            ->name('login');

        Route::any('/callback')
            ->uses([SocialController::class, 'callback'])
            ->name('callback');
    });

Route::get('/cookie-popup', [CookieController::class, 'popup'])->name('cookie.popup');
