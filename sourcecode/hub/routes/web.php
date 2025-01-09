<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ContextController;
use App\Http\Controllers\Admin\LtiPlatformController;
use App\Http\Controllers\Admin\LtiToolController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\CookieController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LtiController;
use App\Http\Controllers\LtiSample\DeepLinkController;
use App\Http\Controllers\SocialController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\EnsureFrameCookies;
use App\Http\Middleware\LtiSessionRequired;
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
    ->name('log_out')
    ->can('logout');

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

    Route::patch('/content/{content}/status')
        ->uses([ContentController::class, 'updateStatus'])
        ->name('content.update-status')
        ->whereUlid('content')
        ->can('edit', 'content');

    Route::get('/c/{content}')
        ->uses([ContentController::class, 'share'])
        ->name('content.share')
        ->whereUlid('content')
        ->can('view', 'content');

    Route::get('/content/{content}/history')
        ->name('content.history')
        ->uses([ContentController::class, 'history'])
        ->whereUlid(['content'])
        ->can('edit', ['content']);

    Route::get('/content/{content}/roles')
        ->uses([ContentController::class, 'roles'])
        ->name('content.roles')
        ->whereUlid(['content'])
        ->can('edit', ['content']);

    Route::post('/content/{content}/roles/add-context')
        ->uses([ContentController::class, 'addContext'])
        ->name('content.add-context')
        ->whereUlid(['content'])
        ->can('manage-roles', ['content']);

    Route::delete('/content/{content}/roles/{role}')
        ->uses([ContentController::class, 'removeContext'])
        ->name('content.remove-context')
        ->can('manage-roles', ['content'])
        ->scopeBindings();

    Route::get('/content/{content}/statistics')
        ->name('content.statistics')
        ->uses([ContentController::class, 'statistics'])
        ->whereUlid(['content'])
        ->can('view', ['content']);

    Route::get('/content/{content}/version/{version}/preview')
        ->uses([ContentController::class, 'preview'])
        ->name('content.preview')
        ->whereUlid(['content', 'version'])
        ->can('view', ['content', 'version'])
        ->scopeBindings();

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

    Route::get('/content/{content}/version/{version}/embed')
        ->uses([ContentController::class, 'embed'])
        ->name('content.embed-version')
        ->can('view', ['content', 'version'])
        ->whereUlid(['content', 'version'])
        ->scopeBindings();

    Route::get('/content/create', 'create')
        ->middleware('auth')
        ->can('create', \App\Models\Content::class)
        ->name('content.create');

    Route::post('/content/{content}/copy')
        ->uses([ContentController::class, 'copy'])
        ->can('copy', 'content')
        ->name('content.copy');

    Route::get('/content/{content}/version/{version}/edit')
        ->uses([ContentController::class, 'edit'])
        ->name('content.edit')
        ->can('edit', ['content', 'version'])
        ->whereUlid(['content', 'version'])
        ->scopeBindings();

    Route::post('/content/{content}/version/{version}/use')
        ->uses([ContentController::class, 'use'])
        ->name('content.use')
        ->can('use', ['content', 'version'])
        ->whereUlid(['content', 'version'])
        ->scopeBindings();

    Route::delete('/content/{content}')
        ->uses([ContentController::class, 'delete'])
        ->name('content.delete')
        ->can('delete', 'content')
        ->whereUlid('content');

    Route::get('/content/create/{tool:slug}/{extra:slug?}', 'launchCreator')
        ->uses([ContentController::class, 'launchCreator'])
        ->name('content.launch-creator')
        ->can('create', \App\Models\Content::class)
        ->can('launchCreator', ['tool', 'extra'])
        ->scopeBindings();
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

    Route::post('/tool/{tool}/content/{content}/version/{version}/update')
        ->uses([ContentController::class, 'ltiUpdate'])
        ->name('content.lti-update')
        ->can('edit', ['content', 'version'])
        ->whereUlid(['tool', 'content', 'version']);
});

Route::prefix('/lti')->middleware([
    EnsureFrameCookies::class,
    LtiValidatedRequest::class . ':platform',
    StartScopedLtiSession::class,
])->group(function () {
    Route::post('/content/{content}')
        ->uses([LtiController::class, 'content'])
        ->name('lti.content')
        ->whereUlid('content');

    Route::post('/content/{content}/version/{version}')
        ->uses([LtiController::class, 'content'])
        ->name('lti.content-version')
        ->whereUlid(['content', 'version'])
        ->scopeBindings();

    Route::post('/content/by-edlib2-usage/{edlib2UsageContent}')
        ->uses([LtiController::class, 'content']);

    Route::post('/dl')
        ->uses([LtiController::class, 'select'])
        ->name('lti.select');

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
    });

    Route::middleware(['auth:web', 'can:update-account'])->group(function () {
        Route::get('/my-account', 'myAccount')->name('user.my-account');
        Route::post('/update-account', 'updateAccount')->name('user.update-account');
        Route::post('/disconnect-social-accounts', 'disconnectSocialAccounts')->name('user.disconnect-social-accounts');
    });
});

Route::middleware('can:admin')->prefix('/admin')->group(function () {
    Route::get('', [AdminController::class, 'index'])->name('admin.index');

    Route::post('/rebuild-content-index', [AdminController::class, 'rebuildContentIndex'])
        ->name('admin.rebuild-content-index');

    Route::get('/contexts')
        ->uses([ContextController::class, 'index'])
        ->name('admin.contexts.index');

    Route::post('/contexts')
        ->uses([ContextController::class, 'add'])
        ->name('admin.contexts.add');

    Route::prefix('/lti-platforms')->group(function () {
        Route::get('')
            ->uses([LtiPlatformController::class, 'index'])
            ->name('admin.lti-platforms.index');

        Route::post('')
            ->uses([LtiPlatformController::class, 'store'])
            ->name('admin.lti-platforms.store');

        Route::get('/{platform}/edit')
            ->uses([LtiPlatformController::class, 'edit'])
            ->name('admin.lti-platforms.edit')
            ->can('edit', ['platform']);

        Route::get('/{platform}/contexts')
            ->uses([LtiPlatformController::class, 'contexts'])
            ->name('admin.lti-platforms.contexts')
            ->can('edit', ['platform']);

        Route::put('/{platform}/contexts')
            ->uses([LtiPlatformController::class, 'addContext'])
            ->name('admin.lti-platforms.add-context')
            ->can('edit', ['platform']);

        Route::patch('/{platform}')
            ->uses([LtiPlatformController::class, 'update'])
            ->name('admin.lti-platforms.update')
            ->can('edit', ['platform']);

        Route::delete('/{platform}')
            ->uses([LtiPlatformController::class, 'destroy'])
            ->name('admin.lti-platforms.remove')
            ->can('delete', 'platform')
            ->whereUlid('platform');
    });

    Route::prefix('/lti-tools')->group(function () {
        Route::get('')
            ->uses([LtiToolController::class, 'index'])
            ->name('admin.lti-tools.index');

        Route::get('/add')
            ->uses([LtiToolController::class, 'add'])
            ->name('admin.lti-tools.add');

        Route::post('')
            ->uses([LtiToolController::class, 'store'])
            ->name('admin.lti-tools.store');

        Route::get('/{tool}/edit')
            ->uses([LtiToolController::class, 'edit'])
            ->name('admin.lti-tools.edit')
            ->whereUlid(['tool']);

        Route::patch('/{tool}')
            ->uses([LtiToolController::class, 'update'])
            ->name('admin.lti-tools.update')
            ->whereUlid(['tool']);

        Route::get('/{tool}/extras/add')
            ->uses([LtiToolController::class, 'addExtra'])
            ->name('admin.lti-tools.add-extra')
            ->can('add-extra', ['tool'])
            ->whereUlid(['tool']);

        Route::post('/{tool}/extras/add')
            ->uses([LtiToolController::class, 'storeExtra'])
            ->name('admin.lti-tools.store-extra')
            ->can('add-extra', ['tool'])
            ->whereUlid(['tool']);

        Route::delete('/{tool}/extras/{extra}')
            ->uses([LtiToolController::class, 'removeExtra'])
            ->name('admin.lti-tools.remove-extra')
            ->can('remove-extra', ['tool', 'extra'])
            ->whereUlid(['tool', 'extra'])
            ->scopeBindings();

        Route::delete('/{tool}')
            ->uses([LtiToolController::class, 'destroy'])
            ->name('admin.lti-tools.remove')
            ->can('remove', ['tool'])
            ->whereUlid(['tool']);
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

Route::match(['GET', 'POST'], '/lti/playground')
    ->middleware('auth')
    ->uses([LtiController::class, 'playground'])
    ->name('lti.playground');

Route::post('/lti/samples/deep-link')
    ->uses([DeepLinkController::class, 'launch'])
    ->middleware([
        LtiValidatedRequest::class . ':platform',
        'lti.launch-type:ContentItemSelectionRequest',
        StartScopedLtiSession::class,
    ])
    ->name('lti.samples.presentation');

Route::get('/lti/samples/deep-link')
    ->uses([DeepLinkController::class, 'form'])
    ->middleware([LtiSessionRequired::class])
    ->name('lti.samples.deep-link.form');

Route::post('/lti/samples/deep-link/return')
    ->uses([DeepLinkController::class, 'return'])
    ->middleware([
        LtiSessionRequired::class,
    ])
    ->name('lti.samples.deep-link.return');

Route::get('/cookie-popup', [CookieController::class, 'popup'])->name('cookie.popup');
