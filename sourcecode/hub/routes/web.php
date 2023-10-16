<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\LtiPlatformController;
use App\Http\Controllers\Admin\LtiToolController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\CookieController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LtiController;
use App\Http\Controllers\SocialController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\EnsureFrameCookies;
use App\Http\Middleware\LtiValidatedRequest;
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

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'login')->name('login');
    Route::post('/login', 'check')->name('login_check');
    Route::post('/log-out', 'logout')->name('log_out');
});

Route::controller(ContentController::class)->group(function () {
    Route::get('/content', 'index')->name('content.index');

    Route::get('/content/mine', 'mine')
        ->middleware('auth')
        ->name('content.mine');

    Route::get('/content/{content}', 'show')
        ->name('content.preview')
        ->whereUlid('content')
        ->can('view', 'content');

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

    Route::get('/content/create/{tool}', 'launchCreator')
        ->name('content.launch-creator')
        ->can('create', \App\Models\Content::class)
        ->whereUlid('tool');
});

Route::prefix('/lti/deep-linking-return')
    ->middleware([
        LtiValidatedRequest::class . ':tool',
        'lti.launch-type:ContentItemSelection',
    ])
    ->group(function () {
        Route::post('/store-content')
            ->uses([ContentController::class, 'ltiStore'])
            ->can('create', \App\Models\Content::class)
            ->name('content.lti-store');

        Route::post('/update-content/{content}')
            ->uses([ContentController::class, 'ltiUpdate'])
            ->can('edit', 'content')
            ->name('content.lti-update')
            ->whereUlid('content');
    });

Route::prefix('/lti/1.1')->group(function () {
    Route::post('/select', [LtiController::class, 'select'])
        ->middleware([
            EnsureFrameCookies::class,
            LtiValidatedRequest::class . ':platform',
            'lti.launch-type:ContentItemSelectionRequest',
        ])
        ->name('lti.select');
});

Route::controller(UserController::class)->group(function () {
    Route::get('/register', 'register')->name('register');
    Route::post('/register', 'store');

    Route::get('/forgot-password', 'showForgotPasswordForm')->name('forgot-password');
    Route::post('/forgot-password', 'sendResetLink')->name('forgot-password-send');

    Route::get('/reset-password/{token}', 'showResetPasswordForm')->name('reset-password');
    Route::post('/reset-password/{token}', 'resetPassword')->name('reset-password-update');

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
